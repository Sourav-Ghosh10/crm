<x-app-layout>
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Tasks & Meetings</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your schedule and upcoming interactions</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-medium transition-colors shadow-lg shadow-indigo-600/25 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Create Task
        </a>
    </div>

    <!-- Filters Section -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-gray-100 dark:border-slate-700 mb-6">
        <form method="GET" action="{{ route('tasks.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" onchange="this.form.submit()" placeholder="Search tasks..." class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Date -->
            <div>
                <input type="date" name="date" value="{{ request('date') }}" onchange="this.form.submit()" class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
            </div>

            <!-- Category -->
            <div>
                <select name="category" onchange="this.form.submit()" class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Categories</option>
                    <option value="Task" {{ request('category') == 'Task' ? 'selected' : '' }}>Task</option>
                    <option value="Meeting" {{ request('category') == 'Meeting' ? 'selected' : '' }}>Meeting</option>
                    <option value="Call" {{ request('category') == 'Call' ? 'selected' : '' }}>Call</option>
                </select>
            </div>

            <!-- Status/Priority -->
            <div>
                <select name="status" onchange="this.form.submit()" class="w-full px-4 py-2 bg-gray-50 dark:bg-slate-700 border-0 rounded-xl text-sm text-gray-700 dark:text-gray-300 focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Statuses & Priorities</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="High" {{ request('status') == 'High' ? 'selected' : '' }}>High Priority</option>
                    <option value="Medium" {{ request('status') == 'Medium' ? 'selected' : '' }}>Medium Priority</option>
                    <option value="Low" {{ request('status') == 'Low' ? 'selected' : '' }}>Low Priority</option>
                </select>
            </div>
        </form>
    </div>

    <!-- Tasks List -->
    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-slate-700/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date & Time</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Task / Meeting</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Related Client</th>
                        @if(auth()->user()->isManagement())
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Assigned To</th>
                        @endif
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    @forelse($tasks as $task)
                        @php
                            $colors = [
                                'Meeting' => 'indigo',
                                'Call' => 'emerald',
                                'Task' => 'amber',
                            ];
                            $color = $colors[$task->category] ?? 'indigo';
                            
                            $priorityColors = [
                                'Low' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                                'Medium' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                'High' => 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ $task->due_at->format('M d, Y') }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ $task->due_at->format('h:i A') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $task->title }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate max-w-xs">{{ $task->location ?? 'No location' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-tight bg-{{ $color }}-100 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400">
                                    {{ $task->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($task->client)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 text-[10px] font-bold">
                                            {{ strtoupper(substr($task->client->full_name, 0, 1)) }}
                                        </div>
                                        <a href="{{ route('clients.show', $task->client_id) }}" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 transition-colors">{{ $task->client->full_name }}</a>
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400 italic">General</span>
                                @endif
                            </td>
                            @if(auth()->user()->isManagement())
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($task->user->name) }}&background=6366f1&color=fff&size=32" alt="" class="w-6 h-6 rounded-full">
                                    <span class="text-sm text-gray-600 dark:text-gray-300 font-medium">{{ $task->user->name }}</span>
                                </div>
                            </td>
                            @endif
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-tight {{ $priorityColors[$task->priority] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ $task->priority }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if(!$task->is_completed)
                                    <form method="POST" action="{{ route('tasks.update', $task) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="is_completed" value="1">
                                        <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-900/30 rounded-lg transition-colors" title="Mark as Completed">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </button>
                                    </form>
                                    @else
                                    <button type="button" disabled class="p-2 text-emerald-600/50 dark:text-emerald-500/50 cursor-not-allowed rounded-lg" title="Task Completed">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                    </button>
                                    @endif
                                    <a href="{{ route('tasks.edit', $task) }}" class="p-2 text-gray-400 hover:text-indigo-600 rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-400 hover:text-red-600 rounded-lg transition-colors" onclick="return confirm('Are you sure?')">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ auth()->user()->isManagement() ? 7 : 6 }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div class="w-12 h-12 bg-gray-100 dark:bg-slate-700/50 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400">No tasks or meetings scheduled.</p>
                                    <a href="{{ route('tasks.create') }}" class="mt-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                                        Schedule Your First Task
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($tasks->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-700">
            {{ $tasks->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
