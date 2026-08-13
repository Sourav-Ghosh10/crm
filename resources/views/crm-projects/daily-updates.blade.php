<x-app-layout>
    <div class="max-w-7xl mx-auto py-8 px-4" x-data="{ showForm: false }">
        
        <!-- Header Section -->
        <div class="mb-12">
            <h1 class="text-3xl font-extrabold text-white tracking-tight mb-6">
                {{ $project->project_name }}
            </h1>
            
            <h2 class="text-xl font-bold text-[#f26d5c] tracking-tight mb-3">
                Daily Updates
            </h2>

            <div class="flex items-start gap-3">
                
                <!-- The Card -->
                <div class="w-[380px] min-h-[300px] border border-gray-600/50 rounded-2xl p-2 bg-[#12151a]">
                    @if($dailyUpdates->count() > 0)
                        <div class="flex flex-col gap-1">
                            @foreach($dailyUpdates as $update)
                                <div class="flex items-start gap-3 p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer group">
                                    <!-- Icon -->
                                    <div class="shrink-0 mt-0.5 w-10 h-10 bg-white flex items-center justify-center rounded-lg shadow-sm">
                                        <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <!-- Content -->
                                    <div class="flex flex-col overflow-hidden leading-tight">
                                        <span class="text-[15px] font-bold text-white group-hover:underline truncate">
                                            {{ \Carbon\Carbon::parse($update->log_date)->format('M j') }} - {{ $update->log_time }} hrs
                                        </span>
                                        <span class="text-[13px] text-gray-400 mt-0.5 truncate">
                                            {{ $update->user->name ?? 'Unknown' }} &bull; {{ Str::limit(strip_tags($update->notes), 30) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex flex-col items-center justify-center text-gray-500 pt-10">
                            <span class="text-sm">No updates yet</span>
                        </div>
                    @endif
                </div>

                <!-- Dashed Plus Button -->
                @php
                    $user = auth()->user();
                    $hasSuperAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
                    $isCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
                @endphp
                @if(!$isCompleted || $hasSuperAccess)
                    <button @click="showForm = !showForm" class="w-12 h-12 shrink-0 rounded-[14px] border border-dashed border-gray-500 flex items-center justify-center text-gray-400 hover:text-white hover:border-gray-400 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                @endif

            </div>
        </div>

        <!-- Form for New Update -->
        <div x-show="showForm" style="display: none;" class="mt-8 bg-[#1a1f26] border border-gray-600/50 rounded-2xl p-6 w-[380px]"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0">
            <h3 class="text-lg font-bold text-white mb-4">Log New Update</h3>
            
            <form action="{{ route('crm-projects.daily-updates.store', $project->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1">Date</label>
                    <input type="date" name="log_date" value="{{ date('Y-m-d') }}" required
                           class="w-full bg-[#12151a] border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500">
                </div>
                
                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1">Hours Logged</label>
                    <input type="number" step="0.1" min="0.1" name="log_time" required placeholder="2.5"
                           class="w-full bg-[#12151a] border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-300 mb-1">Notes</label>
                    <textarea name="notes" rows="3" required placeholder="What did you work on?"
                              class="w-full bg-[#12151a] border border-gray-600 text-white rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showForm = false" class="px-3 py-1.5 text-sm font-medium text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" class="px-4 py-1.5 bg-[#238636] hover:bg-[#2ea043] text-white text-sm font-bold rounded-lg shadow-sm">Save</button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
