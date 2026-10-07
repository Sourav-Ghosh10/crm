<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CrmProject;
use App\Models\ProjectDailyUpdate;
use App\Models\ProjectDailyUpdateAttachment;
use App\Models\ProjectAttachment;
use Illuminate\Support\Facades\Storage;
use App\Services\GoogleDriveService;

class CrmProjectController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $statusFilter = request()->get('status');

        $query = \App\Models\Project::with(['crmDetails', 'assignees', 'dailyUpdates', 'todos'])->orderBy('id', 'desc');

        // If not Admin, or Manager, restrict to assigned projects (including Project Manager, Team Leads and other employees)
        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
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
        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
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
        $project = \App\Models\Project::with(['crmDetails', 'assignees', 'dailyUpdates', 'attachments.user'])->findOrFail($projectId);

        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
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

        return view('crm-projects.show', compact('project', 'dailyUpdates', 'enhancements', 'activities'));
    }

    public function edit($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with([
            'crmDetails',
            'assignees',
            'attachments.user',
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

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
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
        if ($user->hasRole('project-manager') || $user->hasRole('team-lead')) {
            // Project Manager & Team Lead can assign to any roles except super-admin, manager, and project-manager, plus any development team users
            $roles = \App\Models\Role::whereNotIn('name', ['super-admin', 'manager', 'project-manager'])->get();
            $allowedRoleNames = $roles->pluck('name')->toArray();
            $users = \App\Models\User::with('roles')->where(function ($q) use ($allowedRoleNames) {
                $q->whereHas('roles', function ($sub) use ($allowedRoleNames) {
                    $sub->whereIn('name', $allowedRoleNames);
                })->orWhere('is_development_team', true);
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

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        $isCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
        if ($isCompleted && !$hasGlobalAccess) {
            abort(403, 'Project is completed. Unauthorized access.');
        }

        if (!$canEdit) {
            abort(403, 'Unauthorized access.');
        }

        $canEditDetails = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');

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
            'attachment' => 'nullable|file|max:51200',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:51200',
            'file' => 'nullable|file|max:51200',
            'files' => 'nullable|array',
            'files.*' => 'nullable|file|max:51200',
        ], [
            'start_date.required' => 'The start date is required.',
            'end_date.required' => 'The end date is required.',
            'assignee_ids.required' => 'You must assign at least one employee to this project.',
            'assignee_ids.min' => 'You must assign at least one employee to this project.'
        ]);

        // Process file upload(s) to Google Drive
        $uploadedFiles = [];
        if ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $uploadedFiles = is_array($files) ? $files : [$files];
        } elseif ($request->hasFile('files')) {
            $files = $request->file('files');
            $uploadedFiles = is_array($files) ? $files : [$files];
        } elseif ($request->hasFile('attachment')) {
            $uploadedFiles = [$request->file('attachment')];
        } elseif ($request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
        }

        $latestAttachmentPath = null;
        $latestAttachmentName = null;

        foreach ($uploadedFiles as $file) {
            if (!$file) continue;

            $storedPath = null;
            $gdrive = GoogleDriveService::uploadFile($file, 'Projects/Files');
            if ($gdrive && !empty($gdrive['file_id'])) {
                $storedPath = 'google:' . $gdrive['file_id'];
            } else {
                $storedPath = $file->store('project-files', 'public');
            }

            ProjectAttachment::create([
                'project_id' => $projectId,
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            $latestAttachmentPath = $storedPath;
            $latestAttachmentName = $file->getClientOriginalName();
        }

        if ($canEditDetails) {
            $updateData = [
                'description' => $request->description,
                'log_hours' => $request->log_hours,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'assignee_name' => null, // Deprecated, using pivot table now
            ];

            if ($latestAttachmentPath) {
                $updateData['attachment_path'] = $latestAttachmentPath;
                $updateData['attachment_name'] = $latestAttachmentName;
            }

            CrmProject::updateOrCreate(
                ['project_id' => $projectId],
                $updateData
            );
        } else {
            // Ensure CrmProject record exists even if team lead is only updating assignments
            CrmProject::firstOrCreate(['project_id' => $projectId]);
        }

        // Only update assignments if the user has permission to manage assignments
        $canManageAssignments = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics') || $user->hasRole('team-lead');

        if ($canManageAssignments) {
            $assigneeIds = array_filter($request->input('assignee_ids', []));
            $oldAssigneeIds = $project->assignees->pluck('id')->toArray();

            if ($user->isAdmin() || $user->isManager() || $user->hasRole('business-analytics')) {
                // Admin, Manager, and Business Analytics can sync all assignments
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
            return response()->json([
                'success' => true,
                'message' => 'Project details updated successfully.',
                'redirect' => route('crm-projects.edit', $projectId)
            ]);
        }

        return redirect()->route('crm-projects.show', $projectId)->with('success', 'Project details updated successfully.');
    }

    public function storeActivity(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        if (!$canEdit) {
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('change_description') ?? $request->input('activity_description') ?? $request->input('description');
        if ($inputDesc !== null && trim(strip_tags(html_entity_decode($inputDesc, ENT_QUOTES, 'UTF-8'))) === '') {
            $inputDesc = null;
        }
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'nullable|string|max:65535|required_without:attachment',
            'time_estimate' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->file('attachment');
        $attachmentPath = null;
        if ($attachment) {
            $gdrive = GoogleDriveService::uploadFile($attachment, 'Projects/Activities');
            if ($gdrive && !empty($gdrive['file_id'])) {
                $attachmentPath = 'google:' . $gdrive['file_id'];
            } else {
                $attachmentPath = $attachment->store('project-attachments', 'public');
            }
        }

        \App\Models\ProjectActivity::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'description' => $request->description ?? '',
            'time_estimate' => $request->time_estimate,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachment?->getClientOriginalName(),
        ]);

        return redirect()->back()->with('success', 'Activity added successfully.');
    }

    public function updateActivity(Request $request, $projectId, $activityId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);
        $activity = \App\Models\ProjectActivity::where('project_id', $projectId)->findOrFail($activityId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isOwner = $activity->user_id === $user->id;
        $isLead = $user->hasRole('team-lead') && $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isOwner && !$isLead) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('change_description') ?? $request->input('activity_description') ?? $request->input('description');
        if ($inputDesc !== null && trim(strip_tags(html_entity_decode($inputDesc, ENT_QUOTES, 'UTF-8'))) === '') {
            $inputDesc = null;
        }
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'nullable|string|max:65535|required_without:attachment',
            'time_estimate' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->file('attachment');
        $attachmentPath = $activity->attachment_path;
        $attachmentName = $activity->attachment_name;

        if ($request->boolean('remove_attachment')) {
            $attachmentPath = null;
            $attachmentName = null;
        }

        if ($attachment) {
            $gdrive = GoogleDriveService::uploadFile($attachment, 'Projects/Activities');
            if ($gdrive && !empty($gdrive['file_id'])) {
                $attachmentPath = 'google:' . $gdrive['file_id'];
            } else {
                $attachmentPath = $attachment->store('project-attachments', 'public');
            }
            $attachmentName = $attachment->getClientOriginalName();
        }

        $activity->update([
            'description' => $request->description ?? $activity->description,
            'time_estimate' => $request->time_estimate,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity updated successfully.',
                'activity' => $activity
            ]);
        }

        return redirect()->back()->with('success', 'Activity updated successfully.');
    }

    public function destroyActivity(Request $request, $projectId, $activityId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);
        $activity = \App\Models\ProjectActivity::where('project_id', $projectId)->findOrFail($activityId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isOwner = $activity->user_id === $user->id;

        if (!$hasGlobalAccess && !$isOwner) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $activity->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Activity deleted successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Activity deleted successfully.');
    }

    public function storeEnhancement(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        if (!$canEdit) {
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('enhancement_description') ?? $request->input('description');
        if ($inputDesc !== null && trim(strip_tags(html_entity_decode($inputDesc, ENT_QUOTES, 'UTF-8'))) === '') {
            $inputDesc = null;
        }
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'nullable|string|max:65535|required_without:attachment',
            'time_estimate' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->file('attachment');
        $attachmentPath = null;
        if ($attachment) {
            $gdrive = GoogleDriveService::uploadFile($attachment, 'Projects/Enhancements');
            if ($gdrive && !empty($gdrive['file_id'])) {
                $attachmentPath = 'google:' . $gdrive['file_id'];
            } else {
                $attachmentPath = $attachment->store('project-attachments', 'public');
            }
        }

        \App\Models\ProjectEnhancement::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'description' => $request->description ?? '',
            'time_estimate' => $request->time_estimate,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachment?->getClientOriginalName(),
        ]);

        return redirect()->back()->with('success', 'Enhancement added successfully.');
    }

    public function updateEnhancement(Request $request, $projectId, $enhancementId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);
        $enhancement = \App\Models\ProjectEnhancement::where('project_id', $projectId)->findOrFail($enhancementId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isOwner = $enhancement->user_id === $user->id;
        $isLead = $user->hasRole('team-lead') && $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isOwner && !$isLead) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('enhancement_description') ?? $request->input('description');
        if ($inputDesc !== null && trim(strip_tags(html_entity_decode($inputDesc, ENT_QUOTES, 'UTF-8'))) === '') {
            $inputDesc = null;
        }
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'nullable|string|max:65535|required_without:attachment',
            'time_estimate' => 'nullable|string|max:100',
            'attachment' => 'nullable|file|max:10240',
        ]);

        $attachment = $request->file('attachment');
        $attachmentPath = $enhancement->attachment_path;
        $attachmentName = $enhancement->attachment_name;

        if ($request->boolean('remove_attachment')) {
            $attachmentPath = null;
            $attachmentName = null;
        }

        if ($attachment) {
            $gdrive = GoogleDriveService::uploadFile($attachment, 'Projects/Enhancements');
            if ($gdrive && !empty($gdrive['file_id'])) {
                $attachmentPath = 'google:' . $gdrive['file_id'];
            } else {
                $attachmentPath = $attachment->store('project-attachments', 'public');
            }
            $attachmentName = $attachment->getClientOriginalName();
        }

        $enhancement->update([
            'description' => $request->description ?? $enhancement->description,
            'time_estimate' => $request->time_estimate,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Enhancement updated successfully.',
                'enhancement' => $enhancement
            ]);
        }

        return redirect()->back()->with('success', 'Enhancement updated successfully.');
    }

    public function destroyEnhancement(Request $request, $projectId, $enhancementId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);
        $enhancement = \App\Models\ProjectEnhancement::where('project_id', $projectId)->findOrFail($enhancementId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isOwner = $enhancement->user_id === $user->id;

        if (!$hasGlobalAccess && !$isOwner) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $enhancement->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Enhancement deleted successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Enhancement deleted successfully.');
    }

    public function todosIndex($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with(['crmDetails', 'assignees'])->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isAssigned) {
            abort(403, 'Unauthorized access.');
        }

        $todos = \App\Models\ProjectTodo::with('user')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('crm-projects.todos', compact('project', 'todos'));
    }

    public function getTodos($projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isAssigned) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $todos = \App\Models\ProjectTodo::with('user')
            ->where('project_id', $projectId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'project_name' => $project->project_name,
            'todos' => $todos
        ]);
    }

    public function storeTodo(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        // All assigned users can add to-dos
        if (!$hasGlobalAccess && !$isAssigned) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('description');
        if ($inputDesc !== null && trim(strip_tags(html_entity_decode($inputDesc, ENT_QUOTES, 'UTF-8'))) === '') {
            $inputDesc = null;
        }
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'required|string|max:65535',
            'duration_value' => 'nullable|integer|min:1',
            'duration_type' => 'nullable|string|in:hours,days,weeks,months',
            'recurrence_type' => 'nullable|string|in:none,daily,weekly,monthly,yearly',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'nullable|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'recurrence_dates' => 'nullable|array',
            'recurrence_dates.*' => 'nullable|integer|between:1,31',
            'recurrence_yearly_dates' => 'nullable|array',
            'recurrence_yearly_dates.*' => 'nullable|string|regex:/^\d{2}-\d{2}$/',
        ]);

        $todo = \App\Models\ProjectTodo::create([
            'project_id' => $projectId,
            'user_id' => $user->id,
            'description' => $request->description,
            'duration_value' => $request->duration_value ?? 1,
            'duration_type' => $request->duration_type ?? 'days',
            'status' => 'pending',
            'recurrence_type' => $request->recurrence_type ?? 'none',
            'recurrence_days' => $request->recurrence_type === 'weekly' ? ($request->recurrence_days ?? []) : null,
            'recurrence_dates' => $request->recurrence_type === 'monthly' ? ($request->recurrence_dates ?? []) : null,
            'recurrence_yearly_dates' => $request->recurrence_type === 'yearly' ? ($request->recurrence_yearly_dates ?? []) : null,
        ]);


        $todo->load('user');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'To-Do item added successfully.',
                'todo' => $todo
            ]);
        }

        return redirect()->back()->with('success', 'To-Do item added successfully.');
    }

    public function updateTodo(Request $request, $projectId, $todoId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);
        $todo = \App\Models\ProjectTodo::where('project_id', $projectId)->findOrFail($todoId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isOwner = $todo->user_id === $user->id;
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isOwner && !$isAssigned) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized access.'], 403);
            }
            abort(403, 'Unauthorized access.');
        }

        $inputDesc = $request->input('description');
        if ($inputDesc !== null && trim(strip_tags(html_entity_decode($inputDesc, ENT_QUOTES, 'UTF-8'))) === '') {
            $inputDesc = null;
        }
        $request->merge(['description' => $inputDesc]);

        $request->validate([
            'description' => 'required|string|max:65535',
            'duration_value' => 'nullable|integer|min:1',
            'duration_type' => 'nullable|string|in:hours,days,weeks,months',
            'recurrence_type' => 'nullable|string|in:none,daily,weekly,monthly,yearly',
            'recurrence_days' => 'nullable|array',
            'recurrence_days.*' => 'nullable|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
            'recurrence_dates' => 'nullable|array',
            'recurrence_dates.*' => 'nullable|integer|between:1,31',
            'recurrence_yearly_dates' => 'nullable|array',
            'recurrence_yearly_dates.*' => 'nullable|string|regex:/^\d{2}-\d{2}$/',
        ]);

        $todo->update([
            'description' => $request->description,
            'duration_value' => $request->duration_value ?? $todo->duration_value ?? 1,
            'duration_type' => $request->duration_type ?? $todo->duration_type ?? 'days',
            'recurrence_type' => $request->recurrence_type ?? $todo->recurrence_type ?? 'none',
            'recurrence_days' => ($request->recurrence_type ?? $todo->recurrence_type) === 'weekly' ? ($request->recurrence_days ?? $todo->recurrence_days) : null,
            'recurrence_dates' => ($request->recurrence_type ?? $todo->recurrence_type) === 'monthly' ? ($request->recurrence_dates ?? $todo->recurrence_dates) : null,
            'recurrence_yearly_dates' => ($request->recurrence_type ?? $todo->recurrence_type) === 'yearly' ? ($request->recurrence_yearly_dates ?? $todo->recurrence_yearly_dates) : null,
        ]);

        $todo->load('user');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'To-Do item updated successfully.',
                'todo' => $todo
            ]);
        }

        return redirect()->back()->with('success', 'To-Do item updated successfully.');
    }

    public function toggleTodoStatus(Request $request, $projectId, $todoId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isAssigned) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $todo = \App\Models\ProjectTodo::where('project_id', $projectId)->findOrFail($todoId);
        $newStatus = $request->input('status', ($todo->status === 'completed' ? 'pending' : 'completed'));
        $todo->status = $newStatus;
        $todo->save();
        $todo->load('user');

        return response()->json([
            'success' => true,
            'status' => $todo->status,
            'todo' => $todo
        ]);
    }

    public function destroyTodo(Request $request, $projectId, $todoId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isAssigned) {
            return response()->json(['error' => 'Unauthorized access.'], 403);
        }

        $todo = \App\Models\ProjectTodo::where('project_id', $projectId)->findOrFail($todoId);

        if (!$hasGlobalAccess && $todo->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized to delete this to-do item.'], 403);
        }

        $todo->delete();

        return response()->json([
            'success' => true,
            'message' => 'To-Do item deleted successfully.'
        ]);
    }

    public function sendMessage(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
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
        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
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

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);

        if (!$hasGlobalAccess && !$isAssigned) {
            abort(403, 'Unauthorized access.');
        }

        $hasSuperAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
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
            $storedPath = null;
            $gdrive = GoogleDriveService::uploadFile($file, 'Projects/DailyUpdates');
            if ($gdrive && !empty($gdrive['file_id'])) {
                $storedPath = 'google:' . $gdrive['file_id'];
            } else {
                $storedPath = $file->store('daily_updates', 'public');
            }

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

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
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
        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
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
        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
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
        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
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
        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
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

        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (GoogleDriveService::isGoogleDrivePath($attachment->file_path)) {
            $fileId = GoogleDriveService::getFileIdFromPath($attachment->file_path);
            return GoogleDriveService::streamResponse($fileId, $attachment->file_name, $attachment->mime_type, 'inline');
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

        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (GoogleDriveService::isGoogleDrivePath($update->attachment_path)) {
            $fileId = GoogleDriveService::getFileIdFromPath($update->attachment_path);
            return GoogleDriveService::streamResponse($fileId, $update->attachment_name, null, 'inline');
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

        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (GoogleDriveService::isGoogleDrivePath($activity->attachment_path)) {
            $fileId = GoogleDriveService::getFileIdFromPath($activity->attachment_path);
            return GoogleDriveService::streamResponse($fileId, $activity->attachment_name, null, 'inline');
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

        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        if (GoogleDriveService::isGoogleDrivePath($enhancement->attachment_path)) {
            $fileId = GoogleDriveService::getFileIdFromPath($enhancement->attachment_path);
            return GoogleDriveService::streamResponse($fileId, $enhancement->attachment_name, null, 'inline');
        }

        if (!$enhancement->attachment_path || !Storage::disk('public')->exists($enhancement->attachment_path)) {
            abort(404, 'Attachment file not found.');
        }

        return Storage::disk('public')->response($enhancement->attachment_path, $enhancement->attachment_name ?? basename($enhancement->attachment_path));
    }

    public function storeAttachment(Request $request, $projectId)
    {
        $user = auth()->user();
        $project = \App\Models\Project::with('assignees')->findOrFail($projectId);

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isAssigned = $project->assignees->contains('id', $user->id);
        $canEdit = $hasGlobalAccess || ($user->hasRole('team-lead') && $isAssigned);

        if (!$canEdit) {
            abort(403, 'Unauthorized access.');
        }

        $request->validate([
            'file' => 'nullable|file|max:51200',
            'files' => 'nullable|array',
            'files.*' => 'nullable|file|max:51200',
            'attachment' => 'nullable|file|max:51200',
            'attachments' => 'nullable|array',
            'attachments.*' => 'nullable|file|max:51200',
        ]);

        $uploadedFiles = [];
        if ($request->hasFile('files')) {
            $files = $request->file('files');
            $uploadedFiles = is_array($files) ? $files : [$files];
        } elseif ($request->hasFile('attachments')) {
            $files = $request->file('attachments');
            $uploadedFiles = is_array($files) ? $files : [$files];
        } elseif ($request->hasFile('file')) {
            $uploadedFiles = [$request->file('file')];
        } elseif ($request->hasFile('attachment')) {
            $uploadedFiles = [$request->file('attachment')];
        }

        $createdAttachments = [];
        foreach ($uploadedFiles as $file) {
            if (!$file) continue;

            $gdrive = GoogleDriveService::uploadFile($file, 'Projects/Files');
            $storedPath = ($gdrive && !empty($gdrive['file_id']))
                ? ('google:' . $gdrive['file_id'])
                : $file->store('project-files', 'public');

            $attachment = ProjectAttachment::create([
                'project_id' => $projectId,
                'user_id' => $user->id,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $storedPath,
                'mime_type' => $file->getClientMimeType() ?: $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            $createdAttachments[] = $attachment;
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully.',
                'attachments' => $createdAttachments,
            ]);
        }

        return redirect()->back()->with('success', 'File(s) uploaded successfully.');
    }

    public function destroyAttachment($attachmentId)
    {
        $user = auth()->user();
        $attachment = ProjectAttachment::with('project.assignees')->findOrFail($attachmentId);
        $project = $attachment->project;

        $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
        $isOwner = $attachment->user_id === $user->id;

        if (!$hasGlobalAccess && !$isOwner) {
            abort(403, 'Unauthorized to delete this file.');
        }

        if (GoogleDriveService::isGoogleDrivePath($attachment->file_path)) {
            $fileId = GoogleDriveService::getFileIdFromPath($attachment->file_path);
            GoogleDriveService::deleteFile($fileId);
        } elseif (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        $attachment->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'File deleted successfully.']);
        }

        return redirect()->back()->with('success', 'File deleted successfully.');
    }

    public function projectAttachmentShow($attachmentId)
    {
        $user = auth()->user();
        $attachment = ProjectAttachment::with('project.assignees')->findOrFail($attachmentId);
        $project = $attachment->project;

        if (!$project) {
            abort(404, 'Project not found.');
        }

        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized access.');
            }
        }

        if (GoogleDriveService::isGoogleDrivePath($attachment->file_path)) {
            $fileId = GoogleDriveService::getFileIdFromPath($attachment->file_path);
            return GoogleDriveService::streamResponse($fileId, $attachment->file_name, $attachment->mime_type, 'inline');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->response($attachment->file_path, $attachment->file_name);
    }

    public function projectAttachmentDownload($attachmentId)
    {
        $user = auth()->user();
        $attachment = ProjectAttachment::with('project.assignees')->findOrFail($attachmentId);
        $project = $attachment->project;

        if (!$project) {
            abort(404, 'Project not found.');
        }

        if (!$user->isAdmin() && !$user->isManager() && !$user->hasRole('business-analytics')) {
            if (!$project->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized access.');
            }
        }

        if (GoogleDriveService::isGoogleDrivePath($attachment->file_path)) {
            $fileId = GoogleDriveService::getFileIdFromPath($attachment->file_path);
            return GoogleDriveService::streamResponse($fileId, $attachment->file_name, $attachment->mime_type, 'attachment');
        }

        if (!Storage::disk('public')->exists($attachment->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('public')->download($attachment->file_path, $attachment->file_name);
    }
}
