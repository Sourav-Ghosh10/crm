<x-app-layout>
    <!-- Dashboard Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Projects Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Welcome back, {{ Auth::user()->name }}!
                @if(Auth::user()->isAdmin())
                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold">(Administrator)</span>
                @elseif(Auth::user()->isManager())
                    <span class="text-purple-600 dark:text-purple-400 font-semibold">(Manager)</span>
                @elseif(Auth::user()->hasRole('project-manager'))
                    <span class="text-blue-600 dark:text-blue-400 font-semibold">(Project Manager)</span>
                @elseif(Auth::user()->isTeamLead())
                    <span class="text-amber-600 dark:text-amber-400 font-semibold">(Team Lead)</span>
                @elseif(Auth::user()->hasRole('UI-UX-desinger'))
                    <span class="text-pink-600 dark:text-pink-400 font-semibold">(UI/UX Designer)</span>
                @elseif(Auth::user()->hasRole('web-desinger'))
                    <span class="text-teal-600 dark:text-teal-400 font-semibold">(Web Designer)</span>
                @else
                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">({{ Auth::user()->role }})</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="date"
                    class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            <a href="{{ route('projects.create') }}"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2 hover:-translate-y-0.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                New Project
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Projects -->
        <a href="{{ route('crm-projects.index', ['status' => 'all']) }}" class="block bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"></path>
                    </svg>
                </div>
                <span class="px-2.5 py-1 {{ $stats['totalGrowth'] >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' }} text-xs font-semibold rounded-full">
                    {{ $stats['totalGrowth'] >= 0 ? '+' : '' }}{{ $stats['totalGrowth'] }}%
                </span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['totalProjects']) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Projects</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 rounded-full transition-all duration-1000 group-hover:w-full" style="width: 70%"></div>
            </div>
        </a>

        <!-- Active Projects -->
        <a href="{{ route('crm-projects.index', ['status' => 'Active']) }}" class="block bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="px-2.5 py-1 {{ $stats['activeGrowth'] >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' }} text-xs font-semibold rounded-full">
                    {{ $stats['activeGrowth'] >= 0 ? '+' : '' }}{{ $stats['activeGrowth'] }}%
                </span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['activeProjects']) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Active Projects</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full transition-all duration-1000 group-hover:w-full" style="width: 55%"></div>
            </div>
        </a>

        <!-- Completed Projects -->
        <a href="{{ route('crm-projects.index', ['status' => 'Completed']) }}" class="block bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <span class="px-2.5 py-1 {{ $stats['completedGrowth'] >= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' }} text-xs font-semibold rounded-full">
                    {{ $stats['completedGrowth'] >= 0 ? '+' : '' }}{{ $stats['completedGrowth'] }}%
                </span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['completedProjects']) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Completed Projects</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-1000 group-hover:w-full" style="width: 80%"></div>
            </div>
        </a>

        <!-- Overdue Projects -->
        <a href="{{ route('crm-projects.index', ['status' => 'overdue']) }}" class="block bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 group cursor-pointer">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/30 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110">
                    <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <span class="px-2.5 py-1 {{ $stats['overdueGrowth'] <= 0 ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' }} text-xs font-semibold rounded-full">
                    {{ $stats['overdueGrowth'] > 0 ? '+' : '' }}{{ $stats['overdueGrowth'] }}%
                </span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['overdueProjects']) }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Overdue Projects</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-rose-500 rounded-full transition-all duration-1000 group-hover:w-full" style="width: 25%"></div>
            </div>
        </a>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Project Progress (Left Column) -->
        <div class="lg:col-span-2">
            <x-project-progress-chart :labels="$stats['chartLabels']" :created="$stats['chartCreated']" :completed="$stats['chartCompleted']" :active="$stats['chartActive']" />
        </div>

        <!-- Upcoming Deadlines (Right Column) -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 flex flex-col h-full justify-between">
            <div>
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Deadlines</h3>
                    <div class="w-2 h-2 rounded-full bg-indigo-600"></div>
                </div>
                <div class="space-y-4">
                    @forelse($upcomingDeadlines as $dl)
                        @php
                            $badgeColor = match($dl['priority']) {
                                'High' => 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400',
                                'Medium' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                default => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
                            };
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl border border-gray-50 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-all duration-200">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $dl['project_name'] }}</h4>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="text-[11px] text-gray-500 dark:text-gray-400">Deadline: {{ $dl['deadline'] }}</span>
                                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-slate-600"></span>
                                    <span class="text-[11px] font-bold {{ $dl['remaining_days'] <= 3 ? 'text-rose-500' : 'text-gray-500 dark:text-gray-400' }}">
                                        {{ $dl['remaining_days'] < 0 ? abs($dl['remaining_days']) . ' Days Overdue' : ($dl['remaining_days'] == 0 ? 'Due Today' : $dl['remaining_days'] . ' Days Left') }}
                                    </span>
                                </div>
                            </div>
                            <span class="ml-3 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $badgeColor }}">
                                {{ $dl['priority'] }}
                            </span>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-500 dark:text-gray-400 italic">
                            No upcoming deadlines.
                        </div>
                    @endforelse
                </div>
            </div>
            <a href="{{ route('crm-projects.index') }}" class="w-full mt-6 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border-t border-gray-100 dark:border-slate-700 uppercase tracking-widest text-center block">
                Manage All Projects
            </a>
        </div>
    </div>

    <!-- Third Row Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Projects Table (Left Column) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Projects</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Current status and development velocity</p>
                </div>
                <a href="{{ route('crm-projects.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Progress</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Team</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @forelse($recentProjects as $proj)
                            @php
                                $statusColor = match($proj['status']) {
                                    'Completed' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400',
                                    'Overdue' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                                    'Pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                    default => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400',
                                };
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $proj['project_name'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    {{ $proj['client_name'] }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">
                                        {{ $proj['status'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex-1 min-w-[70px] h-2 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-500" style="width: {{ $proj['progress'] }}%"></div>
                                        </div>
                                        <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">{{ $proj['progress'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center -space-x-2 overflow-hidden">
                                        @forelse(collect($proj['assignees'])->take(3) as $member)
                                            @php
                                                $initial = substr(explode(' ', trim($member->name))[0], 0, 1);
                                                $colors = ['#3b82f6', '#a855f7', '#ec4899', '#10b981', '#f59e0b', '#f43f5e'];
                                                $color = $colors[$member->id % count($colors)];
                                            @endphp
                                            <div style="background-color: {{ $color }};" class="h-6 w-6 rounded-full ring-2 ring-white dark:ring-slate-800 flex items-center justify-center text-[9px] font-bold text-white uppercase" title="{{ $member->name }}">
                                                {{ strtoupper($initial) }}
                                            </div>
                                        @empty
                                            <span class="text-xs text-gray-400 italic">Unassigned</span>
                                        @endforelse
                                        @if(count($proj['assignees']) > 3)
                                            <div class="inline-block h-6 w-6 rounded-full ring-2 ring-white dark:ring-slate-800 bg-gray-200 dark:bg-slate-700 flex items-center justify-center text-[9px] font-bold text-gray-600 dark:text-gray-400">
                                                +{{ count($proj['assignees']) - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('crm-projects.edit', $proj['id']) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 transition-colors">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                    No recent projects found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Side Widgets (Recent Activities & Quick Actions) -->
        <div class="space-y-8 flex flex-col justify-between h-full">
            <!-- Recent Activities -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 flex-1">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Recent Activities</h3>
                <div class="space-y-4">
                    @forelse($recentActivities as $act)
                        @php
                            $iconColor = match($act['icon']) {
                                'plus' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400',
                                'update' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400',
                                'trash' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400',
                                default => 'bg-amber-500/10 text-amber-600 dark:text-amber-400',
                            };
                        @endphp
                        <div class="flex items-start gap-3.5 p-3 rounded-xl border border-gray-50/50 dark:border-slate-700/30 hover:bg-gray-50 dark:hover:bg-slate-700/20 transition-all duration-200">
                            <!-- Circular Initials Badge -->
                            <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-[11px] uppercase {{ $iconColor }}">
                                {{ $act['avatar'] }}
                            </div>
                            <!-- Details -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200 break-words leading-tight">
                                    {{ $act['description'] }}
                                </p>
                                <span class="text-[10px] font-medium text-gray-400 dark:text-gray-500 mt-1 block">
                                    {{ $act['time'] }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-gray-400 dark:text-gray-500 italic">
                            No recent activity found.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions (Purple Gradient Card) -->
            @if(auth()->user()->isAdmin())
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white overflow-hidden relative group shadow-lg shadow-indigo-600/10">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-150 duration-700"></div>
                <h3 class="text-lg font-semibold mb-6 relative z-10">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <a href="{{ route('projects.create') }}"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 hover:-translate-y-1 active:scale-95 border border-white/5 text-center">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Create Project</span>
                    </a>
                    
                    <a href="{{ route('crm-projects.index') }}"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 hover:-translate-y-1 active:scale-95 border border-white/5 text-center">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Assign Employee</span>
                    </a>

                    <a href="{{ route('tasks.create') }}"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 hover:-translate-y-1 active:scale-95 border border-white/5 text-center">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Daily Update</span>
                    </a>

                    <a href="{{ route('projects.settings') }}"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 hover:-translate-y-1 active:scale-95 border border-white/5 text-center">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider">Upload Files</span>
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
