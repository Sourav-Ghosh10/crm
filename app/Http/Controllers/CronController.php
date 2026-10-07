<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTodo;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CronController extends Controller
{
    /**
     * Daily Cron endpoint to process recurring todos or create todos on demand.
     * Accessible via GET or POST:
     *   GET /cron/daily-todos
     *   GET /cron/daily-todos?key=YOUR_SECRET_KEY
     *   GET /cron/daily-todos?dry_run=1
     *   GET /cron/daily-todos?date=2026-09-08
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateDailyTodos(Request $request): JsonResponse
    {
        // 1. Security / Token Validation
        $configuredKey = config('app.cron_key') ?? env('CRON_KEY') ?? env('CRON_SECRET');
        $providedKey = $request->query('key') 
            ?? $request->query('token') 
            ?? $request->bearerToken() 
            ?? $request->header('X-Cron-Key');

        if ($configuredKey && $providedKey !== $configuredKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Invalid or missing cron key/token.',
            ], 403);
        }

        // 2. Mode 1: Direct Single Todo Creation (if description and project_id are provided)
        if ($request->filled('description') && $request->filled('project_id')) {
            return $this->handleDirectTodoCreation($request);
        }

        // 3. Mode 2: Process Daily Recurring To-Dos
        return $this->processRecurringTodos($request, $configuredKey ? false : true);
    }

    /**
     * Process all recurring to-dos across projects and generate daily instances.
     */
    protected function processRecurringTodos(Request $request, bool $isUnsecured = false): JsonResponse
    {
        $isDryRun = $request->boolean('dry_run', false);
        $targetDateInput = $request->input('date');
        
        try {
            $targetDate = $targetDateInput ? Carbon::parse($targetDateInput) : Carbon::now();
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid date format provided. Please use YYYY-MM-DD.',
            ], 422);
        }

        $dayOfWeek = strtolower($targetDate->format('l')); // e.g. monday
        $dayOfMonth = (int) $targetDate->format('j');     // e.g. 7
        $yearlyDate = $targetDate->format('m-d');          // e.g. 09-07
        $isLastDayOfMonth = $targetDate->isLastOfMonth();

        $hasParentColumn = Schema::hasColumn('project_todos', 'parent_id');

        // Query active recurring master todos
        $query = ProjectTodo::query()
            ->whereNotNull('recurrence_type')
            ->where('recurrence_type', '!=', 'none')
            ->whereHas('project'); // Only active/existing projects

        if ($hasParentColumn) {
            // Only templates/master rules have parent_id as null
            $query->whereNull('parent_id');
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->input('project_id'));
        }

        $templates = $query->with('project')->get();

        $processedCount = 0;
        $createdCount = 0;
        $skippedCount = 0;
        $details = [];

        foreach ($templates as $template) {
            $processedCount++;
            $recurrenceType = strtolower($template->recurrence_type ?? 'none');
            $matchesSchedule = false;
            $matchReason = '';

            // Check schedule match
            switch ($recurrenceType) {
                case 'daily':
                    $matchesSchedule = true;
                    $matchReason = 'Daily recurrence matches every day';
                    break;

                case 'weekly':
                    $days = is_array($template->recurrence_days)
                        ? $template->recurrence_days
                        : (json_decode($template->recurrence_days, true) ?: []);
                    $normalizedDays = array_map('strtolower', $days);

                    if (in_array($dayOfWeek, $normalizedDays, true)) {
                        $matchesSchedule = true;
                        $matchReason = "Weekly match for {$dayOfWeek}";
                    }
                    break;

                case 'monthly':
                    $dates = is_array($template->recurrence_dates)
                        ? $template->recurrence_dates
                        : (json_decode($template->recurrence_dates, true) ?: []);
                    $intDates = array_map('intval', $dates);

                    if (in_array($dayOfMonth, $intDates, true)) {
                        $matchesSchedule = true;
                        $matchReason = "Monthly match for day {$dayOfMonth}";
                    } elseif ($isLastDayOfMonth) {
                        // If month has fewer days than scheduled date (e.g. 31 in Feb or 30-day months)
                        foreach ($intDates as $d) {
                            if ($d > $dayOfMonth) {
                                $matchesSchedule = true;
                                $matchReason = "Monthly month-end catchup for day {$d}";
                                break;
                            }
                        }
                    }
                    break;

                case 'yearly':
                    $yearlyDates = is_array($template->recurrence_yearly_dates)
                        ? $template->recurrence_yearly_dates
                        : (json_decode($template->recurrence_yearly_dates, true) ?: []);

                    if (in_array($yearlyDate, $yearlyDates, true)) {
                        $matchesSchedule = true;
                        $matchReason = "Yearly match for date {$yearlyDate}";
                    }
                    break;

                default:
                    $matchesSchedule = false;
                    break;
            }

            if (!$matchesSchedule) {
                $skippedCount++;
                $details[] = [
                    'template_id' => $template->id,
                    'project' => $template->project ? $template->project->project_name : ('#' . $template->project_id),
                    'description' => $template->description,
                    'status' => 'skipped',
                    'reason' => 'Schedule does not match today',
                ];
                continue;
            }

            // Check if template itself was created on target date
            if ($template->created_at && $template->created_at->toDateString() === $targetDate->toDateString()) {
                $skippedCount++;
                $details[] = [
                    'template_id' => $template->id,
                    'project' => $template->project ? $template->project->project_name : ('#' . $template->project_id),
                    'description' => $template->description,
                    'status' => 'skipped',
                    'reason' => 'Master task was created on this date already',
                ];
                continue;
            }

            // Check if an instance already exists for target date (Idempotency)
            $alreadyExists = false;
            if ($hasParentColumn) {
                $alreadyExists = ProjectTodo::where('parent_id', $template->id)
                    ->whereDate('created_at', $targetDate->toDateString())
                    ->exists();
            } else {
                $alreadyExists = ProjectTodo::where('project_id', $template->project_id)
                    ->where('id', '!=', $template->id)
                    ->where('description', $template->description)
                    ->whereDate('created_at', $targetDate->toDateString())
                    ->exists();
            }

            if ($alreadyExists) {
                $skippedCount++;
                $details[] = [
                    'template_id' => $template->id,
                    'project' => $template->project ? $template->project->project_name : ('#' . $template->project_id),
                    'description' => $template->description,
                    'status' => 'skipped',
                    'reason' => 'Already generated for this date',
                ];
                continue;
            }

            // Generate new todo instance
            if (!$isDryRun) {
                $attributes = [
                    'project_id' => $template->project_id,
                    'user_id' => $template->user_id,
                    'description' => $template->description,
                    'duration_value' => $template->duration_value ?? 1,
                    'duration_type' => $template->duration_type ?? 'days',
                    'status' => 'pending',
                    'recurrence_type' => $hasParentColumn ? $template->recurrence_type : 'none',
                    'recurrence_days' => $hasParentColumn ? $template->recurrence_days : null,
                    'recurrence_dates' => $hasParentColumn ? $template->recurrence_dates : null,
                    'recurrence_yearly_dates' => $hasParentColumn ? $template->recurrence_yearly_dates : null,
                    'created_at' => $targetDate,
                    'updated_at' => $targetDate,
                ];

                if ($hasParentColumn) {
                    $attributes['parent_id'] = $template->id;
                }

                $newTodo = ProjectTodo::create($attributes);
                $createdId = $newTodo->id;
            } else {
                $createdId = 'dry-run-preview';
            }

            $createdCount++;
            $details[] = [
                'template_id' => $template->id,
                'created_todo_id' => $createdId,
                'project' => $template->project ? $template->project->project_name : ('#' . $template->project_id),
                'description' => $template->description,
                'status' => $isDryRun ? 'would_create' : 'created',
                'reason' => $matchReason,
            ];
        }

        return response()->json([
            'success' => true,
            'message' => $isDryRun 
                ? 'Dry run completed. No database changes were made.' 
                : 'Daily to-do cron executed successfully.',
            'date' => $targetDate->toDateString(),
            'day_of_week' => ucfirst($dayOfWeek),
            'is_dry_run' => $isDryRun,
            'summary' => [
                'total_templates_scanned' => $processedCount,
                'todos_created' => $createdCount,
                'todos_skipped' => $skippedCount,
            ],
            'details' => $details,
            'warning' => $isUnsecured 
                ? 'CRON_KEY / CRON_SECRET is not set in .env. It is recommended to configure a secret key for production security.' 
                : null,
        ]);
    }

    /**
     * Directly create a single todo via URL parameters or POST body.
     */
    protected function handleDirectTodoCreation(Request $request): JsonResponse
    {
        $project = Project::find($request->input('project_id'));
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid project_id provided.',
            ], 404);
        }

        $todo = ProjectTodo::create([
            'project_id' => $project->id,
            'user_id' => $request->input('user_id'),
            'description' => $request->input('description'),
            'duration_value' => $request->input('duration_value', 1),
            'duration_type' => $request->input('duration_type', 'days'),
            'status' => $request->input('status', 'pending'),
            'recurrence_type' => $request->input('recurrence_type', 'none'),
            'recurrence_days' => $request->input('recurrence_days'),
            'recurrence_dates' => $request->input('recurrence_dates'),
            'recurrence_yearly_dates' => $request->input('recurrence_yearly_dates'),
        ]);

        return response()->json([
            'success' => true,
            'action' => 'created_single_todo',
            'message' => 'To-do created successfully.',
            'todo' => $todo->load('user'),
        ]);
    }
}
