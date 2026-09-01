<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CrmProject;
use App\Models\ProjectDailyUpdate;
use App\Models\ProjectDailyUpdateAttachment;
use Illuminate\Support\Facades\Storage;

class CrmProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $statusFilter = request()->get('status');

        $query = \App\Models\Project::with(['crmDetails', 'assignees', 'dailyUpdates'])->orderBy('id', 'desc');

        // If not Admin, or Manager, restrict to assigned projects (including Project Manager, Team Leads and other employees)
        if (!$user->isAdmin() && !$user->isManager()) {
            $query->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($statusFilter && $statusFilter !== 'all') {
            if (strtolower($statusFilter) === 'completed') {
                $query->whereHas('crmDetails', function ($q) {
                    $q->where('status', 'Completed');
                });
            } elseif (strtolower($statusFilter) === 'active') {
                $query->whereHas('crmDetails', function ($q) {
                    $q->where('status', '!=', 'Completed')
                        ->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->where('end_date', '>=', now()->toDateString());
                });
            } elseif ($statusFilter === 'overdue') {
                $query->whereHas('crmDetails', function ($q) {
                    $q->where('status', '!=', 'Completed')
                        ->whereNotNull('start_date')
                        ->whereNotNull('end_date')
                        ->where('end_date', '<', now()->toDateString());
                });
            } else {
                $query->whereIn('status', [$statusFilter, strtolower($statusFilter), ucfirst($statusFilter)]);
            }
        }

        $projects = $query->get();
        return view('crm-projects.index', compact('projects', 'statusFilter'));
    }

    public function chatIndex(Request $request)
    {
        $user = auth()->user();

        $projectsQuery = \App\Models\Project::with(['crmDetails', 'assignees'])
            ->orderBy('project_name', 'asc');

        // If not Admin, Manager, restrict to assigned projects
        if (!$user->isAdmin() && !$user->isManager()) {
            $projectsQuery->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $projects = $projectsQuery->get();

        $selectedProjectId = $request->get('project_id');
        $selectedProject = null;
        $messages = collect();

        if ($projects->count() > 0) {
            if ($selectedProjectId) {
                $selectedProject = $projects->firstWhere('id', $selectedProjectId);
            }
            // Default to the first project if none selected or if selected project is not found in their allowed projects
            if (!$selectedProject) {
                $selectedProject = $projects->first();
            }

            if ($selectedProject) {
                $messages = $selectedProject->messages()
                    ->with('user')
                    ->orderBy('created_at', 'asc')
                    ->get();
            }
        }

        return view('crm-projects.chat', compact('projects', 'selectedProject', 'messages'));
    }
    public function show($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with(['crmDetails', 'assignees', 'dailyUpdates'])->findOrFail($projectId);

        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $dailyUpdates = ProjectDailyUpdate::with(['user', 'attachments'])
            ->where('project_id', $projectId)
            ->orderBy('log_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $enhancements = \App\Models\ProjectEnhancement::with('user')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();

        $activities = \App\Models\ProjectActivity::with('user')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();

        $todos = \App\Models\ProjectTodo::with('user')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('crm-projects.show', compact('project', 'dailyUpdates', 'enhancements', 'activities', 'todos'));
    }

    public function edit($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with([
            'crmDetails',
            'assignees',
            'activities' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'enhancements' => function ($q) {
                $q->orderBy('created_at', 'desc');
            },
            'todos' => function ($q) {
                $q->orderBy('created_at', 'desc');
            }
        ])->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        $isCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
        if ($isCompleted && !$hasGlobalAccess) {
            $canEdit = false;
        }

        if (!$canEdit) {
            // Other roles are redirected to their daily updates logs
            return redirect()->route('crm-projects.daily-updates', $projectId);
        }

        $users = \App\Models\User::orderBy('name', 'asc')->get();

        // Filter roles and users depending on who is logged in
        if ($user->hasRole('project-manager')) {
            // Project Manager can assign to any roles except super-admin, manager, and project-manager
            $roles = \App\Models\Role::whereNotIn('name', ['super-admin', 'manager', 'project-manager'])->get();
            $allowedRoleNames = $roles->pluck('name')->toArray();
            $users = \App\Models\User::with('roles')->whereHas('roles', function ($q) use ($allowedRoleNames) {
                $q->whereIn('name', $allowedRoleNames);
            })->orderBy('name', 'asc')->get();
        } elseif ($user->hasRole('team-lead')) {
            // Team Lead can assign to any roles except super-admin, manager, and project-manager
            $roles = \App\Models\Role::whereNotIn('name', ['super-admin', 'manager', 'project-manager'])->get();
            $allowedRoleNames = $roles->pluck('name')->toArray();
            $users = \App\Models\User::with('roles')->whereHas('roles', function ($q) use ($allowedRoleNames) {
                $q->whereIn('name', $allowedRoleNames);
            })->orderBy('name', 'asc')->get();
        } else {
            // Admin/Manager can see all roles
            $roles = \App\Models\Role::orderBy('name', 'asc')->get();
            $users = \App\Models\User::with('roles')->orderBy('name', 'asc')->get();
        }

        $details = $project->crmDetails;

        return view('crm-projects.edit', compact('project', 'users', 'roles', 'details'));
    }

    public function storeOrUpdate(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        $isCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
        if ($isCompleted && !$hasGlobalAccess) {
            abort(403, 'Project is completed. Unauthorized access.');
        }

        if (!$canEdit) {
            abort(403, 'Unauthorized access.');
        }

        $canEditDetails = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');

        // Reopen / Complete only flip status — no mandatory field checks
        if ($request->input('reopen_project') === '1' || $request->input('complete_project') === '1') {
            if (!$canEditDetails) {
                abort(403, 'Unauthorized access.');
            }

            $details = CrmProject::firstOrCreate(['project_id' => $projectId]);
            $isReopening = $request->input('reopen_project') === '1';
            $details->update(['status' => $isReopening ? 'Active' : 'Completed']);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true, 
                    'message' => $isReopening ? 'Project reopened successfully.' : 'Project marked as completed successfully.',
                    'redirect' => route('crm-projects.show', $projectId)
                ]);
            }

            return redirect()->route('crm-projects.show', $projectId)
                ->with('success', $isReopening ? 'Project reopened successfully.' : 'Project marked as completed successfully.');
        }

        $request->validate([
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'log_hours' => 'nullable|numeric|min:0',
            'assignee_ids' => 'required|array|min:1',
            'assignee_ids.*' => 'exists:users,id',
        ], [
            'start_date.required' => 'The start date is required.',
            'end_date.required' => 'The end date is required.',
            'assignee_ids.required' => 'You must assign at least one employee to this project.',
            'assignee_ids.min' => 'You must assign at least one employee to this project.'
        ]);

        if ($canEditDetails) {
            $updateData = [
                'description' => $request->description,
                'log_hours' => $request->log_hours,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'assignee_name' => null, // Deprecated, using pivot table now
            ];

            CrmProject::updateOrCreate(
                ['project_id' => $projectId],
                $updateData
            );
        } else {
            // Ensure CrmProject record exists even if team lead is only updating assignments
            CrmProject::firstOrCreate(['project_id' => $projectId]);
        }

        // Only update assignments if the user has permission to manage assignments
        $canManageAssignments = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('team-lead');

        if ($canManageAssignments) {
            $assigneeIds = array_filter($request->input('assignee_ids', []));
            $oldAssigneeIds = $project->assignees->pluck('id')->toArray();

            if ($user->isAdmin() || $user->isManager()) {
                // Admin and Manager can sync all assignments
                $project->assignees()->sync($assigneeIds);
            } else {
                $currentAssignees = $project->assignees;

                if ($user->hasRole('project-manager')) {
                    // Project Manager updates assignments for all employee roles (excluding super-admin, manager, and project-manager)
                    // Keep current assignees who are super-admin, manager, or project-manager
                    $keepIds = $currentAssignees->filter(function ($u) {
                        return $u->hasRole('super-admin') || $u->hasRole('manager') || $u->hasRole('project-manager');
                    })->pluck('id')->toArray();

                    $newSyncIds = array_unique(array_merge($keepIds, $assigneeIds));
                    $project->assignees()->sync($newSyncIds);

                } elseif ($user->hasRole('team-lead')) {
                    // Team Lead updates assignments for all roles except super-admin, manager, and project-manager
                    // Keep anyone who is super-admin, manager, or project-manager
                    $keepIds = $currentAssignees->filter(function ($u) {
                        return $u->hasRole('super-admin') || $u->hasRole('manager') || $u->hasRole('project-manager');
                    })->pluck('id')->toArray();

                    $newSyncIds = array_unique(array_merge($keepIds, $assigneeIds));
                    $project->assignees()->sync($newSyncIds);
                }
            }

            // Dispatch notifications to newly assigned users
            $newAssigneeIds = $project->fresh()->assignees->pluck('id')->toArray();
            $newlyAssignedIds = array_diff($newAssigneeIds, $oldAssigneeIds);
            
            \Illuminate\Support\Facades\Log::info('--- PROJECT ASSIGNMENT TRACE START ---');
            \Illuminate\Support\Facades\Log::info('[PASS] Save Details request reached Laravel storeOrUpdate method.');
            \Illuminate\Support\Facades\Log::info('[PASS] Project assignment saved for Project ID: ' . $project->id . ' / ' . $project->project_name);
            \Illuminate\Support\Facades\Log::info('Old Assignees: ' . json_encode($oldAssigneeIds));
            \Illuminate\Support\Facades\Log::info('New Assignees: ' . json_encode($newAssigneeIds));
            \Illuminate\Support\Facades\Log::info('Newly Assigned (Diff): ' . json_encode($newlyAssignedIds));

            if (!empty($newlyAssignedIds)) {
                \Illuminate\Support\Facades\Log::info('[PASS] Laravel detected NEW assignees.');
                $newUsers = \App\Models\User::whereIn('id', $newlyAssignedIds)->get();
                foreach ($newUsers as $newUser) {
                    \Illuminate\Support\Facades\Log::info('[PASS] Laravel identified correct assigned user: ' . $newUser->name . ' (ID: ' . $newUser->id . ')');
                    
                    // Check FCM Token existence for logging
                    $fcmCount = $newUser->fcmTokens()->count();
                    if ($fcmCount > 0) {
                        \Illuminate\Support\Facades\Log::info('[PASS] FCM token found for user ' . $newUser->id . '. Count: ' . $fcmCount);
                    } else {
                        \Illuminate\Support\Facades\Log::error('[FAIL] No FCM token found in database for user ' . $newUser->id);
                    }

                    // Delete previous assignment notifications for this project for this user
                    $newUser->notifications()
                        ->where('type', \App\Notifications\ProjectAssignmentNotification::class)
                        ->where('data->project_id', $project->id)
                        ->delete();

                    // 1. Send bell-icon notification (database + broadcast)
                    // The FCM logic is inside the FcmChannel, which will log Firebase API success/failure.
                    $newUser->notify(new \App\Notifications\ProjectAssignmentNotification($project, $user));

                    // 2. Broadcast a dedicated event so the assignee's browser adds the project card instantly
                    broadcast(new \App\Events\ProjectAssignedToUserEvent(
                        (int) $project->id,
                        (int) $newUser->id,
                        $project->project_name
                    ));
                }
            } else {
                \Illuminate\Support\Facades\Log::info('[FAIL] No NEW assignees detected. Notification will NOT be sent. Did you actually change the assignees before clicking Save Details?');
            }
            \Illuminate\Support\Facades\Log::info('--- PROJECT ASSIGNMENT TRACE END ---');

            // Broadcast real-time access revocation to each removed user.
            // This fires on their own private channel (App.Models.User.{id})
            // so their browser can redirect or remove the project card immediately.
            $removedIds = array_diff($oldAssigneeIds, $newAssigneeIds);
            foreach ($removedIds as $removedUserId) {
                broadcast(new \App\Events\ProjectAssignedEvent((int) $projectId, (int) $removedUserId));
            }
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Project details updated successfully.']);
        }

        return redirect()->route('crm-projects.show', $projectId)->with('success', 'Project details updated successfully.');
    }

    public function storeActivity(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        if (!$canEdit) {
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('change_description') ?? $request->input('activity_description') ?? $request->input('description');
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'nullable|string|max:65535|required_without:attachment',
            'time_estimate' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->file('attachment');

        \App\Models\ProjectActivity::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'description' => $request->description ?? '',
            'time_estimate' => $request->time_estimate,
            'attachment_path' => $attachment ? $attachment->store('project-attachments', 'public') : null,
            'attachment_name' => $attachment?->getClientOriginalName(),
        ]);

        return redirect()->back()->with('success', 'Activity added successfully.');
    }

    public function storeEnhancement(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        if (!$canEdit) {
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('enhancement_description') ?? $request->input('description');
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'nullable|string|max:65535|required_without:attachment',
            'time_estimate' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->file('attachment');

        \App\Models\ProjectEnhancement::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'description' => $request->description ?? '',
            'time_estimate' => $request->time_estimate,
            'attachment_path' => $attachment ? $attachment->store('project-attachments', 'public') : null,
            'attachment_name' => $attachment?->getClientOriginalName(),
        ]);

        return redirect()->back()->with('success', 'Enhancement added successfully.');
    }

    public function storeTodo(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        // All assigned users can add to-dos
        if (!$hasGlobalAccess && !$isAssigned) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'description' => 'required|string|max:65535',
            'duration_value' => 'required|integer|min:1',
            'duration_type' => 'required|string|in:days,weeks,months',
        ]);

        \App\Models\ProjectTodo::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'description' => $request->description,
            'duration_value' => $request->duration_value,
            'duration_type' => $request->duration_type,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'To-Do item added successfully.');
    }

    public function sendMessage(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canChat = $hasGlobalAccess || $isAssigned;

        if (!$canChat) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = \App\Models\ProjectMessage::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'message' => $request->message,
        ]);

        $message->load('user');

        broadcast(new \App\Events\MessageSent($message))->toOthers();

        return response()->json([
            'status' => 'success',
            'message' => $message
        ]);
    }

    public function dailyUpdatesIndex($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        // Authorization: Admin, Manager, and Project Manager have global access.
        // Team Lead and all other roles can view/post updates ONLY if they are assigned to this project.
        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isAssigned) {
            abort(403, 'Unauthorized access. You are not assigned to this project.');
        }

        $dailyUpdates = ProjectDailyUpdate::with(['user', 'attachments'])
            ->where('project_id', $projectId)
            ->orderBy('log_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('crm-projects.daily-updates', compact('project', 'dailyUpdates'));
    }

    public function storeDailyUpdate(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isAssigned) {
            abort(403, 'Unauthorized access.');
        }

        $hasSuperAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
        if ($isCompleted && !$hasSuperAccess) {
            abort(403, 'Project is completed. You cannot add daily updates.');
        }

        $data = $request->validate([
            'log_date' => 'required|date',
            'log_time' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file', // no max size
        ]);

        $dailyUpdate = ProjectDailyUpdate::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'log_date' => $data['log_date'],
            'log_time' => $data['log_time'] ?? 0,
            'notes' => $data['notes'] ?? '',
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $storedPath = $file->store('daily_updates', 'public');

            ProjectDailyUpdateAttachment::create([
                'project_daily_update_id' => $dailyUpdate->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }

        return redirect()->route('crm-projects.show', $projectId)
            ->with('success', 'Daily update logged successfully.');
    }
    public function updateDailyUpdate(Request $request, $projectId, $updateId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $update = \App\Models\ProjectDailyUpdate::where('project_id', $projectId)->findOrFail($updateId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
        $isOwner = $update->user_id === $user->id;

        if (!$hasGlobalAccess && !$isOwner) {
            abort(403, 'Unauthorized access. You can only edit your own updates.');
        }

        $isCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
        if ($isCompleted && !$hasGlobalAccess) {
            abort(403, 'Project is completed. You cannot edit daily updates.');
        }

        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $update->update([
            'notes' => $data['notes'] ?? '',
        ]);

        return redirect()->route('crm-projects.show', $projectId)
            ->with('success', 'Daily update edited successfully.');
    }

    public function docsIndex($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::findOrFail($projectId);

        // Security check
        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $documents = \App\Models\ProjectDocument::with('user')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('crm-projects.docs.index', compact('project', 'documents'));
    }

    public function docsCreate($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::findOrFail($projectId);

        // Security check
        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return view('crm-projects.docs.create', compact('project'));
    }

    public function docsStore(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::findOrFail($projectId);

        // Security check
        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        \App\Models\ProjectDocument::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return redirect()->route('crm-projects.docs', $projectId)->with('success', 'Document created successfully.');
    }

    public function docsShow($projectId, $documentId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::findOrFail($projectId);
        $document = \App\Models\ProjectDocument::where('project_id', $projectId)->findOrFail($documentId);

        // Security check
        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return view('crm-projects.docs.show', compact('project', 'document'));
    }

    public function attachmentShow($attachmentId)
    {
        $user = auth()->user();
        $attachment = ProjectDailyUpdateAttachment::with(['dailyUpdate.project.assignees'])->findOrFail($attachmentId);
        $project = optional(optional($attachment->dailyUpdate)->project);

        if (!$project) {
            abort(404, 'Attachment project not found.');
        }

        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->response($attachment->file_path, $attachment->file_name);
    }

    public function legacyDailyUpdateAttachmentShow($updateId)
    {
        $user = auth()->user();
        $update = ProjectDailyUpdate::with('project.assignees')->findOrFail($updateId);
        $project = $update->project;

        if (!$project) {
            abort(404, 'Project not found.');
        }

        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (!$update->attachment_path || !Storage::disk('public')->exists($update->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->response($update->attachment_path, $update->attachment_name ?? basename($update->attachment_path));
    }

    public function activityAttachmentShow($activityId)
    {
        $user = auth()->user();
        $activity = \App\Models\ProjectActivity::with('project.assignees')->findOrFail($activityId);
        $project = $activity->project;

        if (!$project) {
            abort(404, 'Project not found.');
        }

        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (!$activity->attachment_path || !Storage::disk('public')->exists($activity->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->response($activity->attachment_path, $activity->attachment_name ?? basename($activity->attachment_path));
    }

    public function enhancementAttachmentShow($enhancementId)
    {
        $user = auth()->user();
        $enhancement = \App\Models\ProjectEnhancement::with('project.assignees')->findOrFail($enhancementId);
        $project = $enhancement->project;

        if (!$project) {
            abort(404, 'Project not found.');
        }

        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (!$enhancement->attachment_path || !Storage::disk('public')->exists($enhancement->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->response($enhancement->attachment_path, $enhancement->attachment_name ?? basename($enhancement->attachment_path));
    }
}
