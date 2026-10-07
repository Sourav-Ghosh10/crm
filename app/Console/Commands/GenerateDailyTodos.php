<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\ProjectTodo;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class GenerateDailyTodos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'todos:generate-daily
                            {--date= : Custom target date (YYYY-MM-DD) to simulate or run for}
                            {--project_id= : Run only for a specific project ID}
                            {--dry-run : Simulate execution without inserting database records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process recurring to-dos (daily, weekly, monthly, yearly) and create daily instances';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $targetDateInput = $this->option('date');
        $isDryRun = (bool) $this->option('dry-run');
        $projectId = $this->option('project_id');

        try {
            $targetDate = $targetDateInput ? Carbon::parse($targetDateInput) : Carbon::now();
        } catch (\Exception $e) {
            $this->error("Invalid date format provided: {$targetDateInput}. Please use YYYY-MM-DD.");
            return Command::FAILURE;
        }

        $this->info("--------------------------------------------------");
        $this->info("Running Daily To-Do Generator");
        $this->info("Target Date : " . $targetDate->toDateString() . " (" . $targetDate->format('l') . ")");
        $this->info("Dry Run     : " . ($isDryRun ? "YES (No records will be created)" : "NO (Live execution)"));
        if ($projectId) {
            $this->info("Project ID  : {$projectId}");
        }
        $this->info("--------------------------------------------------");

        $dayOfWeek = strtolower($targetDate->format('l'));
        $dayOfMonth = (int) $targetDate->format('j');
        $yearlyDate = $targetDate->format('m-d');
        $isLastDayOfMonth = $targetDate->isLastOfMonth();

        $hasParentColumn = Schema::hasColumn('project_todos', 'parent_id');

        $query = ProjectTodo::query()
            ->whereNotNull('recurrence_type')
            ->where('recurrence_type', '!=', 'none')
            ->whereHas('project');

        if ($hasParentColumn) {
            $query->whereNull('parent_id');
        }

        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        $templates = $query->with('project')->get();

        if ($templates->isEmpty()) {
            $this->comment("No recurring to-do templates found matching criteria.");
            return Command::SUCCESS;
        }

        $processedCount = 0;
        $createdCount = 0;
        $skippedCount = 0;
        $tableRows = [];

        foreach ($templates as $template) {
            $processedCount++;
            $recurrenceType = strtolower($template->recurrence_type ?? 'none');
            $matchesSchedule = false;
            $matchReason = '';

            switch ($recurrenceType) {
                case 'daily':
                    $matchesSchedule = true;
                    $matchReason = 'Daily (Every day)';
                    break;

                case 'weekly':
                    $days = is_array($template->recurrence_days)
                        ? $template->recurrence_days
                        : (json_decode($template->recurrence_days, true) ?: []);
                    $normalizedDays = array_map('strtolower', $days);

                    if (in_array($dayOfWeek, $normalizedDays, true)) {
                        $matchesSchedule = true;
                        $matchReason = "Weekly match ({$dayOfWeek})";
                    }
                    break;

                case 'monthly':
                    $dates = is_array($template->recurrence_dates)
                        ? $template->recurrence_dates
                        : (json_decode($template->recurrence_dates, true) ?: []);
                    $intDates = array_map('intval', $dates);

                    if (in_array($dayOfMonth, $intDates, true)) {
                        $matchesSchedule = true;
                        $matchReason = "Monthly match (day {$dayOfMonth})";
                    } elseif ($isLastDayOfMonth) {
                        foreach ($intDates as $d) {
                            if ($d > $dayOfMonth) {
                                $matchesSchedule = true;
                                $matchReason = "Monthly month-end catchup ({$d})";
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
                        $matchReason = "Yearly match ({$yearlyDate})";
                    }
                    break;

                default:
                    $matchesSchedule = false;
                    break;
            }

            $projectName = $template->project ? $template->project->project_name : ('#' . $template->project_id);
            $shortDesc = \Illuminate\Support\Str::limit($template->description, 35);

            if (!$matchesSchedule) {
                $skippedCount++;
                $tableRows[] = [
                    $template->id,
                    $projectName,
                    $shortDesc,
                    ucfirst($recurrenceType),
                    'SKIPPED',
                    'Schedule does not match today'
                ];
                continue;
            }

            // Skip if master was created on this target date
            if ($template->created_at && $template->created_at->toDateString() === $targetDate->toDateString()) {
                $skippedCount++;
                $tableRows[] = [
                    $template->id,
                    $projectName,
                    $shortDesc,
                    ucfirst($recurrenceType),
                    'SKIPPED',
                    'Master task was created on this date'
                ];
                continue;
            }

            // Check if already exists for target date
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
                $tableRows[] = [
                    $template->id,
                    $projectName,
                    $shortDesc,
                    ucfirst($recurrenceType),
                    'SKIPPED',
                    'Already generated for this date'
                ];
                continue;
            }

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
                $statusLabel = 'CREATED (#' . $newTodo->id . ')';
            } else {
                $statusLabel = 'WOULD CREATE';
            }

            $createdCount++;
            $tableRows[] = [
                $template->id,
                $projectName,
                $shortDesc,
                ucfirst($recurrenceType),
                $statusLabel,
                $matchReason
            ];
        }

        $this->table(
            ['Template ID', 'Project', 'Description', 'Recurrence', 'Action', 'Reason / Note'],
            $tableRows
        );

        $this->info("--------------------------------------------------");
        $this->info("Completed: {$createdCount} created, {$skippedCount} skipped, {$processedCount} total checked.");
        $this->info("--------------------------------------------------");

        return Command::SUCCESS;
    }
}
