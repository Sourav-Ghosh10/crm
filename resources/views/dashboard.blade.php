<x-app-layout>
    <!-- Dashboard Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Welcome back, {{ Auth::user()->name }}!
                @if(Auth::user()->isAdmin())
                    <span class="text-indigo-600 dark:text-indigo-400">(Administrator)</span>
                @elseif(Auth::user()->isManager())
                    <span class="text-purple-600 dark:text-purple-400">(Manager)</span>
                @else
                    <span class="text-emerald-600 dark:text-emerald-400">(Agent)</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            <div class="relative">
                <input type="date"
                    class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
            </div>
            @can('create', App\Models\Client::class)
                <button
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-indigo-600/25">
                    <span class="flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        New Client
                    </span>
                </button>
            @endcan
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Clients Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z">
                        </path>
                    </svg>
                </div>
                <span
                    class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full">+{{ $stats['clientsGrowth'] ?? 0 }}%</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">
                {{ number_format($stats['totalClients'] ?? 0) }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Clients</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-indigo-600 rounded-full" style="width: 75%"></div>
            </div>
        </div>

        <!-- Active Leads Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                </div>
                <span
                    class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full">+{{ $stats['leadsGrowth'] ?? 0 }}%</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['activeLeads'] ?? 0) }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Active Leads</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-amber-500 rounded-full" style="width: 60%"></div>
            </div>
        </div>

        <!-- Calls Today Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div
                    class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                        </path>
                    </svg>
                </div>
                <span
                    class="px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-semibold rounded-full">+{{ $stats['callsGrowth'] ?? 0 }}%</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($stats['callsToday'] ?? 0) }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Calls Today</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: 45%"></div>
            </div>
        </div>

        <!-- Pending Tasks Card -->
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/30 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                        </path>
                    </svg>
                </div>
                <span
                    class="px-2.5 py-1 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 text-xs font-semibold rounded-full">{{ $stats['tasksGrowth'] ?? 0 }}</span>
            </div>
            <h3 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['pendingTasks'] ?? 0 }}</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pending Tasks</p>
            <div class="mt-4 h-1.5 bg-gray-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-rose-500 rounded-full" style="width: 35%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column - Charts & Tables -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Call Activity Chart -->
            <x-call-activity-chart :labels="$stats['chartMonths']" :calls="$stats['chartCalls']"
                :followUps="$stats['chartFollowUps']" :events="$stats['chartEvents']" :values="$stats['chartValues']" />

            <!-- Recent Clients Table -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Follow-up Queue</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Latest activity and upcoming calls</p>
                    </div>
                    <a href="{{ route('clients.index') }}"
                        class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">View
                        All</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 dark:bg-slate-700/50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Client</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Last Contact</th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Next Follow-up</th>
                                <th
                                    class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                            @forelse($recentClients as $client)
                                @php
                                    $lastLog = $client->callLogs->first();
                                    $initials = collect(explode(' ', $client->full_name ?? 'C'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-xs font-bold">
                                                {{ $initials }}
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ $client->full_name }}
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ $client->email ?? $client->phone }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-tight {{ $client->status_color }}">
                                            {{ $client->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-400 font-medium">
                                        {{ $lastLog ? $lastLog->call_start_time->format('M d, H:i') : 'Never' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($lastLog && $lastLog->next_follow_up_date)
                                            <span
                                                class="text-xs font-bold {{ $lastLog->next_follow_up_date->isToday() ? 'text-rose-500 animate-pulse' : 'text-indigo-600 dark:text-indigo-400' }}">
                                                {{ $lastLog->next_follow_up_date->format('M d, Y') }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400 dark:text-gray-600 italic">No schedule</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('clients.show', $client->id) }}"
                                            class="inline-flex p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                </path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5"
                                        class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400 italic">
                                        No clients found in your outreach queue.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar Widgets -->
        <div class="space-y-8">
            <!-- Upcoming Follow-ups Widget -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Upcoming Follow-ups</h3>
                    <button
                        class="p-2 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    @forelse($upcomingTasks as $task)
                        <a href="{{ route('clients.show', $task->client_id) }}"
                            class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-shadow cursor-pointer group border border-transparent hover:border-gray-100 dark:hover:border-slate-700">
                            <div class="mt-0.5">
                                <div
                                    class="w-2.5 h-2.5 rounded-full {{ $task->next_follow_up_date->isToday() ? 'bg-rose-500 shadow-sm shadow-rose-500/50' : 'bg-indigo-400' }}">
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p
                                    class="text-sm font-semibold text-gray-900 dark:text-white truncate group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ $task->client->full_name ?? 'Client' }}
                                </p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                    {{ $task->next_follow_up_date->isToday() ? 'Due Today' : 'Scheduled: ' . $task->next_follow_up_date->format('M d') }}
                                </p>
                            </div>
                            @if($task->next_follow_up_date->isToday())
                                <span
                                    class="px-2 py-0.5 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 text-[10px] font-bold rounded-full">Urgent</span>
                            @endif
                        </a>
                    @empty
                        <div class="py-10 text-center">
                            <div
                                class="w-12 h-12 bg-gray-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 italic">No pending follow-ups</p>
                        </div>
                    @endforelse
                </div>

                @if($upcomingTasks->count() > 0)
                    <a href="{{ route('tasks.index') }}"
                        class="w-full mt-6 py-2.5 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors border-t border-gray-100 dark:border-slate-700 uppercase tracking-widest text-center block">
                        View All Tasks
                    </a>
                @endif
            </div>

            <!-- Upcoming Events -->
            <div
                class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Upcoming</h3>
                <div class="space-y-4">
                    @forelse($upcomingEvents as $event)
                        @php
                            $colors = [
                                'Meeting' => 'indigo',
                                'Call' => 'emerald',
                                'Task' => 'amber',
                            ];
                            $color = $colors[$event->category] ?? 'indigo';
                        @endphp
                        <div
                            class="flex items-center gap-4 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors cursor-pointer group">
                            <div
                                class="w-12 h-12 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 rounded-xl flex flex-col items-center justify-center transition-transform group-hover:scale-110">
                                <span
                                    class="text-xs font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ $event->due_at->format('d') }}</span>
                                <span
                                    class="text-[10px] font-medium text-{{ $color }}-500 dark:text-{{ $color }}-400 uppercase">{{ $event->due_at->format('M') }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $event->title }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $event->due_at->format('h:i A') }} • {{ $event->location }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="py-10 text-center">
                            <div
                                class="w-12 h-12 bg-gray-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 italic">No upcoming events</p>
                        </div>
                    @endforelse
                </div>
                <a href="{{ route('tasks.index') }}"
                    class="w-full mt-4 py-2.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded-xl transition-colors text-center block">View
                    All Events</a>
            </div>

            <!-- Quick Actions -->
            <div
                class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white overflow-hidden relative group">
                <div
                    class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl transition-transform group-hover:scale-150 duration-700">
                </div>
                <h3 class="text-lg font-semibold mb-6 relative z-10">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-4 relative z-10">
                    <a href="{{ route('clients.create') }}"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all hover:-translate-y-1 active:scale-95 border border-white/5">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wider">Add Client</span>
                    </a>
                    <a href="{{ route('clients.index') }}"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all hover:-translate-y-1 active:scale-95 border border-white/5">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wider">Log Call</span>
                    </a>
                    <a href="{{ route('tasks.create') }}"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all hover:-translate-y-1 active:scale-95 border border-white/5">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wider">Create Task</span>
                    </a>
                    <a href="mailto:"
                        class="flex flex-col items-center gap-3 p-4 bg-white/10 hover:bg-white/20 rounded-xl transition-all hover:-translate-y-1 active:scale-95 border border-white/5">
                        <div class="w-10 h-10 bg-white/10 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-wider">Send Email</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Follow-up Popup Modal -->
    @if(isset($todayFollowUps) && count($todayFollowUps) > 0)
        <x-modal name="followup-reminder" :show="true" maxWidth="md">
            <div class="bg-white dark:bg-slate-800 shadow-xl border border-gray-100 dark:border-slate-700">
                <!-- Modal Header -->
                <div
                    class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between bg-gray-50/50 dark:bg-slate-800">
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-600 animate-pulse"></div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider">Scheduled
                            Follow-ups</h3>
                    </div>
                    <button x-on:click="$dispatch('close-modal', 'followup-reminder')"
                        class="p-2 text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Modal Content - Following 'Recent Clients' Table Style -->
                <div class="max-h-[400px] overflow-y-auto bg-white dark:bg-slate-800">
                    <div class="divide-y divide-gray-100 dark:divide-slate-700">
                        @foreach($todayFollowUps as $followUp)
                            <div class="px-6 py-4 hover:bg-gray-50/50 dark:hover:bg-slate-700/20 transition-colors">
                                <div class="flex items-center gap-4">
                                    <!-- Avatar Style Initials -->
                                    <div
                                        class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-indigo-500 to-indigo-600 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                        {{ strtoupper(substr($followUp->client->full_name ?? 'C', 0, 1)) }}
                                    </div>

                                    <!-- Main Info Group -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p
                                                class="text-sm font-bold text-gray-900 dark:text-white truncate uppercase tracking-tight">
                                                {{ $followUp->client->full_name ?? 'Client' }}
                                            </p>
                                            <a href="{{ route('clients.show', $followUp->client_id) }}"
                                                class="p-1.5 text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                                </svg>
                                            </a>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <svg class="w-3 h-3 text-emerald-500" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                                </path>
                                            </svg>
                                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                                                {{ $followUp->client->phone ?? 'No phone' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                @if($followUp->notes)
                                    <div class="mt-2 ml-14 pl-3 border-l-2 border-indigo-100 dark:border-indigo-900/40">
                                        <p class="text-[11px] text-gray-500 dark:text-gray-400 italic">
                                            {{ $followUp->notes }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Modal Footer -->
                <div
                    class="px-6 py-4 bg-gray-50 dark:bg-slate-700/50 border-t border-gray-100 dark:border-slate-700 flex items-center justify-end gap-3">
                    <button x-on:click="$dispatch('close-modal', 'followup-reminder')"
                        class="px-4 py-2 text-xs font-bold text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors uppercase tracking-wider">
                        Remind Later
                    </button>
                    <button x-on:click="$dispatch('close-modal', 'followup-reminder')"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-600/20 active:scale-95">
                        Got it
                    </button>
                </div>
            </div>
        </x-modal>
    @endif
</x-app-layout>