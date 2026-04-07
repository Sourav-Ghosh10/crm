<?php

namespace App\Http\Controllers;

use App\Models\CallLog;
use App\Models\Client;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with role-based data.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get role-specific data
        $stats = $this->getDashboardStats($user);

        // Fetch recent clients for the table (Follow-up scenario)
        $isAgent = !$user->isAdmin() && !$user->isManager();
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

        $isAgent = !$user->isAdmin() && !$user->isManager();
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
            $stats['pendingTasks'] = CallLog::where('staff_member_id', $user->id)
                ->whereDate('next_follow_up_date', '>=', today())->count();
        } else {
            $stats['callsToday'] = CallLog::whereDate('call_start_time', today())->count();
            $stats['pendingTasks'] = CallLog::whereDate('next_follow_up_date', '>=', today())->count();
        }

        // Generate initial chart data
        $chartData = $this->calculateChartData($user, 'month');
        $stats['chartMonths'] = $chartData['labels'];
        $stats['chartValues'] = $chartData['values'];

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
        $values = [];
        $isAgent = !$user->isAdmin() && !$user->isManager();

        if ($filter === 'date' && $dateStr) {
            // Selected Date: Show last 7 days ending at the selected date
            try {
                $endDate = \Carbon\Carbon::parse($dateStr);
            } catch (\Exception $e) {
                $endDate = now();
            }

            for ($i = 6; $i >= 0; $i--) {
                $date = (clone $endDate)->subDays($i);
                $labels[] = $date->format('D, M d');

                $query = CallLog::whereDate('call_start_time', $date->toDateString());
                if ($isAgent) {
                    $query->where('staff_member_id', $user->id);
                }
                $values[] = $query->count();
            }
        } elseif ($filter === 'week') {
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $labels[] = $date->format('D');

                $query = CallLog::whereDate('call_start_time', $date->toDateString());
                if ($isAgent) {
                    $query->where('staff_member_id', $user->id);
                }
                $values[] = $query->count();
            }
        } elseif ($filter === 'year') {
            // Last 12 months
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M');

                $query = CallLog::whereMonth('call_start_time', $date->month)
                    ->whereYear('call_start_time', $date->year);
                if ($isAgent) {
                    $query->where('staff_member_id', $user->id);
                }
                $values[] = $query->count();
            }
        } else {
            // Default: last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M');

                $query = CallLog::whereMonth('call_start_time', $date->month)
                    ->whereYear('call_start_time', $date->year);
                if ($isAgent) {
                    $query->where('staff_member_id', $user->id);
                }
                $values[] = $query->count();
            }
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
