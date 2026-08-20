<x-app-layout>
    <style>
        @keyframes fadeSlideUp {
            0% {
                opacity: 0;
                transform: translateY(30px);
            }
            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .staggered-card {
            opacity: 0;
            animation: fadeSlideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
    <div x-data="{ filter: '{{ request()->get('status') === 'Active' ? 'Ongoing' : (request()->get('status') === 'Completed' ? 'Completed' : 'All') }}' }">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                @if(request()->get('status') === 'Active')
                    Active Projects
                @elseif(request()->get('status') === 'Completed')
                    Completed Projects
                @elseif(request()->get('status') === 'overdue')
                    Overdue Projects
                @else
                    Project Management
                @endif
            </h1>
            <!-- Hide tabs if a specific status filter is active to keep UI clean and focused -->
            @if(!request()->get('status'))
            <div class="flex gap-1.5 bg-gray-100 dark:bg-slate-800/80 p-1.5 rounded-xl border border-gray-200 dark:border-slate-700 overflow-x-auto">
                <button @click="filter = 'All'" :class="{ 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm ring-1 ring-gray-200 dark:ring-slate-600': filter === 'All', 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200/50 dark:hover:bg-slate-700/50': filter !== 'All' }" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200 whitespace-nowrap">All</button>
                <button @click="filter = 'Not Started'" :class="{ 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm ring-1 ring-gray-200 dark:ring-slate-600': filter === 'Not Started', 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200/50 dark:hover:bg-slate-700/50': filter !== 'Not Started' }" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200 whitespace-nowrap">Not Started</button>
                <button @click="filter = 'Ongoing'" :class="{ 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm ring-1 ring-gray-200 dark:ring-slate-600': filter === 'Ongoing', 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200/50 dark:hover:bg-slate-700/50': filter !== 'Ongoing' }" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200 whitespace-nowrap">Ongoing</button>
                <button @click="filter = 'Completed'" :class="{ 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-sm ring-1 ring-gray-200 dark:ring-slate-600': filter === 'Completed', 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200/50 dark:hover:bg-slate-700/50': filter !== 'Completed' }" class="px-4 py-1.5 rounded-lg text-sm font-bold transition-all duration-200 whitespace-nowrap">Completed</button>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($projects as $project)
                @php
                    $details = $project->crmDetails;
                    $user = auth()->user();
                    $hasGlobalAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
                    $isEditor = $hasGlobalAccess || $user->hasRole('team-lead');
                    $isAssigned = $project->assignees->contains('id', $user->id);
                    $isCompleted = $details && $details->status === 'Completed';
                    
                    $projectStatus = 'Not Started';
                    if ($details) {
                        $projectStatus = $isCompleted ? 'Completed' : 'Ongoing';
                    }
                @endphp
                <a href="{{ route('crm-projects.show', $project->id) }}" style="animation-delay: {{ $loop->index * 75 }}ms;" x-show="filter === 'All' || filter === '{{ $projectStatus }}'" x-transition.opacity.duration.300ms class="staggered-card bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 flex flex-col hover:shadow-md hover:border-indigo-300 dark:hover:border-indigo-500 transition-all relative group block">
                    
                    <!-- Card Header -->
                    <div class="p-5 pb-2 flex justify-between items-start">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight pr-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                            {{ Str::limit($project->project_name, 50) }}
                        </h3>
                    </div>

                    <!-- Card Body -->
                    <div class="px-5 py-2 flex-grow flex flex-col">
                        <p class="text-sm text-gray-500 dark:text-gray-400 flex-grow">
                            @if($details && $details->description)
                                {{ Str::limit(strip_tags($details->description), 100) }}
                            @else
                                <span class="italic opacity-75">No description</span>
                            @endif
                        </p>
                        
                        <!-- Timeline / Status Tags -->
                        <div class="mt-4 flex flex-wrap gap-2">
                             @if($details && $details->status === 'Completed')
                                 <span class="inline-flex items-center text-[10px] font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-1 rounded-full">Done</span>
                            @elseif($details && $details->end_date)
                                @php
                                    $endDate = \Carbon\Carbon::parse($details->end_date)->startOfDay();
                                    $today = \Carbon\Carbon::today();
                                    $daysLeft = $today->diffInDays($endDate, false);
                                @endphp
                                @if($daysLeft > 1)
                                    <span class="inline-flex items-center text-[10px] font-semibold text-indigo-700 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded-full">{{ $daysLeft }} days left</span>
                                @elseif($daysLeft == 1)
                                    <span class="inline-flex items-center text-[10px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-full">1 day left</span>
                                @elseif($daysLeft == 0)
                                    <span class="inline-flex items-center text-[10px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-full">Ends today</span>
                                @else
                                    <span class="inline-flex items-center text-[10px] font-semibold text-rose-700 dark:text-rose-400 bg-rose-50 dark:bg-rose-900/30 px-2 py-1 rounded-full">{{ abs($daysLeft) }} {{ abs($daysLeft) == 1 ? 'day' : 'days' }} overdue</span>
                                @endif
                            @endif
                            @if($details && $details->log_hours)
                                <span class="inline-flex items-center text-[10px] font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-2 py-1 rounded-full">{{ $details->log_hours }} hrs</span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Footer: Assignees -->
                    <div class="px-5 py-4 border-t border-gray-50 dark:border-slate-700/50 flex items-center justify-between mt-2">
                        <div class="flex items-center">
                            @if($project->assignees && $project->assignees->count() > 0)
                                @foreach($project->assignees->take(5) as $assignee)
                                    @php
                                        // Take only the very first letter of the first name
                                        $initial = substr(explode(' ', trim($assignee->name))[0], 0, 1);
                                        $colors = ['#3b82f6', '#a855f7', '#ec4899', '#10b981', '#f59e0b', '#f43f5e']; // blue, purple, pink, emerald, amber, rose
                                        $color = $colors[$assignee->id % count($colors)];
                                    @endphp
                                    <div x-data="{ showTooltip: false }" class="relative {{ $loop->first ? '' : '-ml-2' }}" @mouseenter="showTooltip = true" @mouseleave="showTooltip = false">
                                        <div style="background-color: {{ $color }};" class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold ring-2 ring-white dark:ring-slate-800">
                                            {{ strtoupper($initial) }}
                                        </div>
                                        <!-- Tooltip -->
                                        <div x-show="showTooltip" style="display: none;" class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-slate-700 text-white text-xs rounded shadow-lg whitespace-nowrap z-10" x-transition>
                                            {{ $assignee->name }}
                                        </div>
                                    </div>
                                @endforeach
                                @if($project->assignees->count() > 5)
                                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400 text-xs font-bold ring-2 ring-white dark:ring-slate-800 -ml-2">
                                        +{{ $project->assignees->count() - 5 }}
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-gray-400 italic">No assignees</span>
                            @endif
                        </div>

                        <!-- Project Settings Gear Icon -->
                        <button type="button" onclick="event.preventDefault(); window.location.href='{{ route('crm-projects.edit', $project->id) }}'" 
                            class="p-2 text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 transition-colors rounded-full hover:bg-gray-100 dark:hover:bg-slate-700"
                            title="Project Settings">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </button>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-16 px-6 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-slate-800 rounded-xl border border-gray-100 dark:border-slate-700">
                    <svg class="mx-auto h-16 w-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                    <p class="text-base font-medium">No projects found.</p>
                    <p class="text-sm mt-1 opacity-75">Add a project from the <strong>List Projects</strong> section to see it here.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
