<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Models\Client;
use App\Models\User;
use App\Models\Task;
use App\Models\Project;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with role-based data.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $isPM = $user->hasRole('project-manager');
        $isTLOrDesigner = $user->hasRole('team-lead') || $user->hasRole('UI-UX-desinger') || $user->hasRole('web-desinger');

        // If Project Manager, Team Lead, UI/UX Designer, or Web Designer, show the projects-focused dashboard
        if ($isPM || $isTLOrDesigner) {
            $restrictToAssigned = $isTLOrDesigner;

            $stats = $this->getProjectDashboardStats($user, $restrictToAssigned);
            $upcomingDeadlines = $this->getUpcomingDeadlines($user, $restrictToAssigned);
            $recentProjects = $this->getRecentProjects($user, $restrictToAssigned);
            $recentActivities = $this->getRecentActivities($user, $restrictToAssigned);

            return view('dashboard-pm', compact('stats', 'upcomingDeadlines', 'recentProjects', 'recentActivities'));
        }

        // Get role-specific data
        $stats = $this->getDashboardStats($user);

        // Fetch recent clients for the table (Follow-up scenario)
        $isAgent = !$user->isAdmin() && !$user->isManager() && !$user->hasRole('project-manager');
        $recentClients = Client::query()
            ->when($isAgent, fn($q) => $q->where('agent_id', $user->id))
            ->with(['callLogs' => fn($q) => $q->latest()])
            ->latest()
            ->take(5)
            ->get();

        // Fetch upcoming tasks (from today onwards)
        $upcomingTasks = CallLog::whereDate('next_follow_up_date', '>=', today())
            ->when($isAgent, fn($q) => $q->where('staff_member_id', $user->id))
            ->with('client')
            ->orderBy('next_follow_up_date', 'asc')
            ->take(5)
            ->get();

        // Fetch upcoming events/meetings (from the new tasks table)
        $upcomingEvents = Task::where('user_id', $user->id)
            ->whereDate('due_at', '>=', today())
            ->where('is_completed', false)
            ->orderBy('due_at', 'asc')
            ->take(5)
            ->get();

        // Check for follow-up popup
        $showPopup = session()->pull('show_follow_up_popup', false);
        $todayFollowUps = [];

        if ($showPopup) {
            $todayFollowUps = CallLog::whereDate('next_follow_up_date', today())
                ->where('staff_member_id', $user->id)
                ->with('client')
                ->get();
        }

        return view('dashboard', compact('stats', 'todayFollowUps', 'recentClients', 'upcomingTasks', 'upcomingEvents'));
    }

    /**
     * Get dashboard statistics based on user role
     */
    private function getDashboardStats($user)
    {
        $stats = [
            'totalClients' => 0,
            'activeLeads' => 0,
            'callsToday' => 0,
            'pendingTasks' => 0,
            'clientsGrowth' => 12.5,
            'leadsGrowth' => 8.2,
            'callsGrowth' => 23.1,
            'tasksGrowth' => -3.0,
            'chartData' => [],
        ];

        $isAgent = !$user->isAdmin() && !$user->isManager() && !$user->hasRole('project-manager');
        $query = Client::query();

        if ($isAgent) {
            $query->where('agent_id', $user->id);
            $stats['clientsGrowth'] = 5.2;
            $stats['leadsGrowth'] = 3.1;
            $stats['callsGrowth'] = 10.5;
        }

        $stats['totalClients'] = (clone $query)->count();
        $stats['activeLeads'] = (clone $query)->whereIn('status', ['New', 'Follow-up', 'In Progress'])->count();

        // Calls Today: Total calls logged today
        if ($isAgent) {
            $stats['callsToday'] = CallLog::where('staff_member_id', $user->id)
                ->whereDate('call_start_time', today())->count();
            $stats['pendingTasks'] = Task::where('user_id', $user->id)
                ->where('is_completed', false)->count();
        } else {
            $stats['callsToday'] = CallLog::whereDate('call_start_time', today())->count();
            $stats['pendingTasks'] = Task::where('is_completed', false)->count();
        }

        // Generate initial chart data
        $chartData = $this->calculateChartData($user, 'month');
        $stats['chartMonths'] = $chartData['labels'];
        $stats['chartCalls'] = $chartData['calls'];
        $stats['chartFollowUps'] = $chartData['followUps'];
        $stats['chartEvents'] = $chartData['events'];
        $stats['chartValues'] = $chartData['values']; // For backward compatibility if needed

        return $stats;
    }

    /**
     * Get call activity data via AJAX
     */
    public function getCallActivityData(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'month');
        $date = $request->query('date');

        return response()->json($this->calculateChartData($user, $filter, $date));
    }

    /**
     * Calculate chart labels and values based on filter
     */
    private function calculateChartData($user, $filter, $dateStr = null)
    {
        $labels = [];
        $calls = [];
        $followUps = [];
        $events = [];
        $isAgent = !$user->isAdmin() && !$user->isManager() && !$user->hasRole('project-manager');

        if ($filter === 'date') {
            // Show full month data for the calendar view
            try {
                $currentDate = $dateStr ? \Carbon\Carbon::parse($dateStr) : now();
            } catch (\Exception $e) {
                $currentDate = now();
            }

            $startOfMonth = (clone $currentDate)->startOfMonth();
            $daysInMonth = $currentDate->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = (clone $startOfMonth)->day($i);
                $dateString = $date->toDateString();
                $labels[] = $dateString;

                // Calls count
                $callsQuery = CallLog::whereDate('call_start_time', $dateString);
                if ($isAgent)
                    $callsQuery->where('staff_member_id', $user->id);
                $calls[] = $callsQuery->count();

                // Follow-ups count
                $followUpsQuery = CallLog::whereDate('next_follow_up_date', $dateString);
                if ($isAgent)
                    $followUpsQuery->where('staff_member_id', $user->id);
                $followUps[] = $followUpsQuery->count();

                // Events count
                $eventsQuery = Task::whereDate('due_at', $dateString);
                if ($isAgent)
                    $eventsQuery->where('user_id', $user->id);
                $events[] = $eventsQuery->count();
            }
        } elseif ($filter === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateString = $date->toDateString();
                $labels[] = $date->format('D');

                // Calls
                $callsQuery = CallLog::whereDate('call_start_time', $dateString);
                if ($isAgent)
                    $callsQuery->where('staff_member_id', $user->id);
                $calls[] = $callsQuery->count();

                // Follow-ups
                $followUpsQuery = CallLog::whereDate('next_follow_up_date', $dateString);
                if ($isAgent)
                    $followUpsQuery->where('staff_member_id', $user->id);
                $followUps[] = $followUpsQuery->count();

                // Events
                $eventsQuery = Task::whereDate('due_at', $dateString);
                if ($isAgent)
                    $eventsQuery->where('user_id', $user->id);
                $events[] = $eventsQuery->count();
            }
        } elseif ($filter === 'year') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M');

                // Calls
                $callsQuery = CallLog::whereMonth('call_start_time', $date->month)
                    ->whereYear('call_start_time', $date->year);
                if ($isAgent)
                    $callsQuery->where('staff_member_id', $user->id);
                $calls[] = $callsQuery->count();

                // Follow-ups
                $followUpsQuery = CallLog::whereMonth('next_follow_up_date', $date->month)
                    ->whereYear('next_follow_up_date', $date->year);
                if ($isAgent)
                    $followUpsQuery->where('staff_member_id', $user->id);
                $followUps[] = $followUpsQuery->count();

                // Events
                $eventsQuery = Task::whereMonth('due_at', $date->month)
                    ->whereYear('due_at', $date->year);
                if ($isAgent)
                    $eventsQuery->where('user_id', $user->id);
                $events[] = $eventsQuery->count();
            }
        } else {
            // Default: last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M');

                // Calls
                $callsQuery = CallLog::whereMonth('call_start_time', $date->month)
                    ->whereYear('call_start_time', $date->year);
                if ($isAgent)
                    $callsQuery->where('staff_member_id', $user->id);
                $calls[] = $callsQuery->count();

                // Follow-ups
                $followUpsQuery = CallLog::whereMonth('next_follow_up_date', $date->month)
                    ->whereYear('next_follow_up_date', $date->year);
                if ($isAgent)
                    $followUpsQuery->where('staff_member_id', $user->id);
                $followUps[] = $followUpsQuery->count();

                // Events
                $eventsQuery = Task::whereMonth('due_at', $date->month)
                    ->whereYear('due_at', $date->year);
                if ($isAgent)
                    $eventsQuery->where('user_id', $user->id);
                $events[] = $eventsQuery->count();
            }
        }

        return [
            'labels' => $labels,
            'calls' => $calls,
            'followUps' => $followUps,
            'events' => $events,
            // Keep 'values' for backward compatibility or the bars view (using calls as primary)
            'values' => $calls,
        ];
    }

    /**
     * Helper to get base project query with optional assignee restriction
     */
    private function getProjectQuery($user, $restrictToAssigned = false)
    {
        $query = Project::query();
        if ($restrictToAssigned) {
            $query->whereHas('assignees', function($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        return $query;
    }

    /**
     * Get project stats for Project Manager, Team Lead, and Designers
     */
    private function getProjectDashboardStats($user, $restrictToAssigned = false)
    {
        $stats = [
            'totalProjects' => 0,
            'activeProjects' => 0,
            'completedProjects' => 0,
            'overdueProjects' => 0,
            'totalGrowth' => 10.5,
            'activeGrowth' => 5.2,
            'completedGrowth' => 15.0,
            'overdueGrowth' => -8.3,
            'chartLabels' => [],
            'chartCreated' => [],
            'chartCompleted' => [],
            'chartActive' => []
        ];

        // Fetch counts using query helper
        $stats['totalProjects'] = $this->getProjectQuery($user, $restrictToAssigned)->count();
        $stats['activeProjects'] = $this->getProjectQuery($user, $restrictToAssigned)
            ->whereHas('crmDetails', function($sub) {
                $sub->where('status', '!=', 'Completed')
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->where('end_date', '>=', now()->toDateString());
            })->count();
        $stats['completedProjects'] = $this->getProjectQuery($user, $restrictToAssigned)
            ->whereHas('crmDetails', function($q) {
                $q->where('status', 'Completed');
            })->count();
        $stats['overdueProjects'] = $this->getProjectQuery($user, $restrictToAssigned)
            ->whereHas('crmDetails', function($sub) {
                $sub->where('status', '!=', 'Completed')
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->where('end_date', '<', now()->toDateString());
            })->count();

        // Dynamically calculate growth: comparing this month to last month
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        $thisMonthTotal = $this->getProjectQuery($user, $restrictToAssigned)->where('created_at', '>=', $thisMonth)->count();
        $lastMonthTotal = $this->getProjectQuery($user, $restrictToAssigned)->where('created_at', '>=', $lastMonth)->where('created_at', '<', $thisMonth)->count();
        $stats['totalGrowth'] = $lastMonthTotal > 0 ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1) : 100.0;

        $thisMonthActive = $this->getProjectQuery($user, $restrictToAssigned)
            ->whereHas('crmDetails', function($sub) {
                $sub->where('status', '!=', 'Completed')
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->where('end_date', '>=', now()->toDateString());
            })->where('created_at', '>=', $thisMonth)->count();
        $lastMonthActive = $this->getProjectQuery($user, $restrictToAssigned)
            ->whereHas('crmDetails', function($sub) {
                $sub->where('status', '!=', 'Completed')
                    ->whereNotNull('start_date')
                    ->whereNotNull('end_date')
                    ->where('end_date', '>=', now()->toDateString());
            })->where('created_at', '>=', $lastMonth)->where('created_at', '<', $thisMonth)->count();
        $stats['activeGrowth'] = $lastMonthActive > 0 ? round((($thisMonthActive - $lastMonthActive) / $lastMonthActive) * 100, 1) : 5.0;

        // Chart Data (last 6 months)
        $chartData = $this->calculateProjectChartData('month', null, $user, $restrictToAssigned);
        $stats['chartLabels'] = $chartData['labels'];
        $stats['chartCreated'] = $chartData['created'];
        $stats['chartCompleted'] = $chartData['completed'];
        $stats['chartActive'] = $chartData['active'];

        return $stats;
    }

    /**
     * Get project progress data via AJAX
     */
    public function getProjectProgressData(Request $request)
    {
        $user = Auth::user();
        $filter = $request->query('filter', 'month');
        $date = $request->query('date');
        
        $restrictToAssigned = $user->hasRole('team-lead') || $user->hasRole('UI-UX-desinger') || $user->hasRole('web-desinger');

        return response()->json($this->calculateProjectChartData($filter, $date, $user, $restrictToAssigned));
    }

    /**
     * Calculate created vs completed projects chart data
     */
    private function calculateProjectChartData($filter, $dateStr = null, $user = null, $restrictToAssigned = false)
    {
        $labels = [];
        $created = [];
        $completed = [];
        $active = [];

        if ($filter === 'date') {
            try {
                $currentDate = $dateStr ? \Carbon\Carbon::parse($dateStr) : now();
            } catch (\Exception $e) {
                $currentDate = now();
            }

            $startOfMonth = (clone $currentDate)->startOfMonth();
            $daysInMonth = $currentDate->daysInMonth;

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = (clone $startOfMonth)->day($i);
                $dateString = $date->toDateString();
                $labels[] = $dateString;

                $created[] = $this->getProjectQuery($user, $restrictToAssigned)->whereDate('created_at', $dateString)->count();
                $active[] = $this->getProjectQuery($user, $restrictToAssigned)->whereIn('status', ['Active', 'active'])->whereDate('created_at', $dateString)->count();

                $completedCount = $this->getProjectQuery($user, $restrictToAssigned)
                    ->whereHas('crmDetails', function($q) use ($dateString) {
                        $q->where('status', 'Completed')->whereDate('end_date', $dateString);
                    })->count();
                $completed[] = $completedCount;
            }
        } elseif ($filter === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateString = $date->toDateString();
                $labels[] = $date->format('D');

                $created[] = $this->getProjectQuery($user, $restrictToAssigned)->whereDate('created_at', $dateString)->count();
                $active[] = $this->getProjectQuery($user, $restrictToAssigned)->whereIn('status', ['Active', 'active'])->whereDate('created_at', $dateString)->count();
                
                // Completed: check AuditTrail or project end_date
                $completedCount = $this->getProjectQuery($user, $restrictToAssigned)
                    ->whereHas('crmDetails', function($q) use ($dateString) {
                        $q->where('status', 'Completed')->whereDate('end_date', $dateString);
                    })->count();
                $completed[] = $completedCount;
            }
        } elseif ($filter === 'year') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M');

                $created[] = $this->getProjectQuery($user, $restrictToAssigned)->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
                $active[] = $this->getProjectQuery($user, $restrictToAssigned)->whereIn('status', ['Active', 'active'])->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();

                $completedCount = $this->getProjectQuery($user, $restrictToAssigned)
                    ->whereHas('crmDetails', function($q) use ($date) {
                        $q->where('status', 'Completed')->whereMonth('end_date', $date->month)->whereYear('end_date', $date->year);
                    })->count();
                $completed[] = $completedCount;
            }
        } else {
            // Default: last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M');

                $created[] = $this->getProjectQuery($user, $restrictToAssigned)->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();
                $active[] = $this->getProjectQuery($user, $restrictToAssigned)->whereIn('status', ['Active', 'active'])->whereMonth('created_at', $date->month)->whereYear('created_at', $date->year)->count();

                $completedCount = $this->getProjectQuery($user, $restrictToAssigned)
                    ->whereHas('crmDetails', function($q) use ($date) {
                        $q->where('status', 'Completed')->whereMonth('end_date', $date->month)->whereYear('end_date', $date->year);
                    })->count();
                $completed[] = $completedCount;
            }
        }

        return [
            'labels' => $labels,
            'created' => $created,
            'completed' => $completed,
            'active' => $active
        ];
    }

    /**
     * Get upcoming deadlines
     */
    private function getUpcomingDeadlines($user, $restrictToAssigned = false)
    {
        return $this->getProjectQuery($user, $restrictToAssigned)
            ->whereHas('crmDetails', function($q) {
                $q->whereNotNull('end_date')
                  ->where('end_date', '<=', now()->addDays(3)->toDateString())
                  ->where('status', '!=', 'Completed');
            })
            ->with('crmDetails')
            ->get()
            ->map(function($project) {
                $endDate = \Carbon\Carbon::parse($project->crmDetails->end_date);
                $remainingDays = now()->startOfDay()->diffInDays($endDate->startOfDay(), false);
                
                if ($remainingDays <= 3) {
                    $priority = 'High';
                } elseif ($remainingDays <= 7) {
                    $priority = 'Medium';
                } else {
                    $priority = 'Low';
                }
                
                return [
                    'project_name' => $project->project_name,
                    'deadline' => $endDate->format('M d, Y'),
                    'remaining_days' => $remainingDays,
                    'priority' => $priority
                ];
            })
            ->sortBy('remaining_days')
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Get recent projects
     */
    private function getRecentProjects($user, $restrictToAssigned = false)
    {
        return $this->getProjectQuery($user, $restrictToAssigned)
            ->with(['crmDetails', 'assignees'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function($project) {
                $isCrmCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
                $status = strtolower($project->status);
                if ($isCrmCompleted || in_array($status, ['completed', 'paid'])) {
                    $progress = 100;
                    $displayStatus = 'Completed';
                } elseif (in_array($status, ['pending', 'draft'])) {
                    $progress = 0;
                    $displayStatus = 'Pending';
                } else {
                    $displayStatus = 'Active';
                    $startDate = $project->crmDetails && $project->crmDetails->start_date ? \Carbon\Carbon::parse($project->crmDetails->start_date) : null;
                    $endDate = $project->crmDetails && $project->crmDetails->end_date ? \Carbon\Carbon::parse($project->crmDetails->end_date) : null;
                    
                    if ($endDate && $endDate->isPast()) {
                        $displayStatus = 'Overdue';
                    }
                    
                    if ($startDate && $endDate) {
                        $totalDays = $startDate->diffInDays($endDate);
                        $elapsedDays = $startDate->diffInDays(now());
                        $progress = $totalDays > 0 ? min(max(round(($elapsedDays / $totalDays) * 100), 15), 90) : 50;
                    } else {
                        $progress = 45;
                    }
                }
                
                return [
                    'id' => $project->id,
                    'project_name' => $project->project_name,
                    'client_name' => $project->client_name,
                    'status' => $displayStatus,
                    'progress' => $progress,
                    'deadline' => $project->crmDetails && $project->crmDetails->end_date ? \Carbon\Carbon::parse($project->crmDetails->end_date)->format('M d, Y') : 'N/A',
                    'assignees' => $project->assignees
                ];
            })
            ->toArray();
    }

    /**
     * Get recent activities from AuditTrail
     */
    private function getRecentActivities($user, $restrictToAssigned = false)
    {
        $query = AuditTrail::with('user');
        
        $canSeeAll = $user->isAdmin() || $user->hasRole('project-manager');
        if (!$canSeeAll) {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function($trail) {
                $userName = $trail->user ? $trail->user->name : 'System';
                $time = $trail->created_at ? $trail->created_at->diffForHumans() : 'Just now';
                $avatar = $trail->user ? collect(explode(' ', $trail->user->name))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') : 'SYS';
                
                $description = '';
                $icon = 'edit';
                
                $modelName = class_basename($trail->model_type);
                if ($modelName === 'Project') {
                    if ($trail->action_type === 'CREATE') {
                        $projectName = $trail->new_values['project_name'] ?? 'a project';
                        $description = "{$userName} created Project: {$projectName}";
                        $icon = 'plus';
                    } elseif ($trail->action_type === 'UPDATE') {
                        $projectName = $trail->new_values['project_name'] ?? ($trail->old_values['project_name'] ?? 'Project');
                        if (isset($trail->new_values['status'])) {
                            $status = $trail->new_values['status'];
                            $description = "{$userName} changed status of {$projectName} to {$status}";
                        } else {
                            $description = "{$userName} updated Project {$projectName}";
                        }
                        $icon = 'update';
                    } else {
                        $description = "{$userName} deleted a project";
                        $icon = 'trash';
                    }
                } elseif ($modelName === 'Task') {
                    $taskTitle = $trail->new_values['title'] ?? ($trail->old_values['title'] ?? 'Task');
                    $description = "{$userName} updated Task: {$taskTitle}";
                    $icon = 'task';
                } elseif ($modelName === 'Client') {
                    $clientName = $trail->new_values['full_name'] ?? ($trail->old_values['full_name'] ?? 'Client');
                    $description = "{$userName} updated Client: {$clientName}";
                    $icon = 'user';
                } else {
                    $description = "{$userName} performed an action on {$modelName}";
                }
                
                return [
                    'avatar' => $avatar,
                    'time' => $time,
                    'description' => $description,
                    'icon' => $icon
                ];
            })
            ->toArray();
    }
}
