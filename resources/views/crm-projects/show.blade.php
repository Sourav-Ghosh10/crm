<x-app-layout>
    <div>

        <!-- Header Section -->
        <div class="mb-20" style="margin-bottom: 2rem;">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-3">
                <a href="{{ route('crm-projects.index') }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400">Projects</a>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span>{{ $project->project_name }}</span>
            </div>

            <div class="flex items-start md:items-center justify-between mb-8 flex-col md:flex-row gap-4">
                <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                    {{ $project->project_name }}
                </h1>

                <a href="{{ route('crm-projects.edit', $project->id) }}"
                    class="flex items-center shrink-0 gap-2 px-3 py-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 bg-gray-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-gray-400 dark:hover:text-indigo-400 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                        </path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Project Settings
                </a>
            </div>

            <!-- Basic activity log / creation info -->
            <div class="flex flex-col gap-2 text-sm text-gray-600 dark:text-gray-400 mt-4">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                        </path>
                    </svg>
                    <span>Project was created on {{ $project->created_at->format('M j, Y') }}</span>
                </div>
            </div>
        </div>

        @php
            $user = auth()->user();
            $hasSuperAccess = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
            $isCompleted = $project->crmDetails && $project->crmDetails->status === 'Completed';
            $isAssigned = $project->assignees->contains('id', $user->id);
            $canLogUpdate = ($hasSuperAccess || $user->hasRole('team-lead') || $isAssigned);
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: New Update Form -->
            <div class="lg:col-span-2">
                @if(!$isCompleted || $hasSuperAccess)
                    @if($canLogUpdate)
                        <div style="max-width: 1000px;"
                            class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 sm:p-8 mb-8 mx-auto">
                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                    </path>
                                </svg>
                                <span>Log Daily Update</span>
                            </h2>

                            <form action="{{ route('crm-projects.daily-updates.store', $project->id) }}" method="POST"
                                enctype="multipart/form-data" id="daily-update-form">
                                @csrf

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                                    <!-- Date Section -->
                                    <div>
                                        <label for="log_date"
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Date</label>
                                        <input type="date" name="log_date" id="log_date" value="{{ date('Y-m-d') }}" required
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block px-5 py-4 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-gray-400 dark:text-white transition-colors">
                                    </div>

                                    <!-- Log Hours Section -->
                                    <div>
                                        <label for="log_time"
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Log Hours
                                            (Optional)</label>
                                        <div class="relative">
                                            <input type="number" step="0.1" min="0" name="log_time" id="log_time"
                                                placeholder="0.0" style="padding-right: 3.5rem;"
                                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block px-5 py-4 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-gray-400 dark:text-white pr-14 transition-colors">
                                            <div class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none"
                                                style="right: 1.25rem;">
                                                <span class="text-gray-500 dark:text-gray-400 text-base">hrs</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Doc Button (Attachment) -->
                                <div class="mb-10">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Attachment
                                        (Optional)</label>
                                    <div class="flex items-center justify-center w-full" x-data="{ isDropping: false }">
                                        <label for="attachment" @dragover.prevent="isDropping = true"
                                            @dragleave.prevent="isDropping = false"
                                            @drop.prevent="isDropping = false; document.getElementById('attachment').files = $event.dataTransfer.files; document.getElementById('file-name').textContent = $event.dataTransfer.files[0]?.name || ''"
                                            :class="isDropping ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 hover:bg-gray-100 dark:hover:bg-slate-800'"
                                            class="flex flex-col items-center justify-center w-full min-h-[11rem] px-6 py-10 border-2 border-dashed rounded-xl cursor-pointer transition-colors">
                                            <div class="flex flex-col items-center justify-center gap-3.5 pointer-events-none">
                                                <svg class="w-9 h-9 text-gray-400"
                                                    :class="{'text-indigo-500 animate-bounce': isDropping}" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                                                    </path>
                                                </svg>
                                                <p class="text-sm text-gray-500 dark:text-gray-400"><span class="font-semibold"
                                                        x-text="isDropping ? 'Drop file to attach' : 'Click to upload document'"></span>
                                                    <span x-show="!isDropping">or drag and drop</span>
                                                </p>
                                                <p class="text-xs text-gray-500 dark:text-gray-400">Any file type supported</p>
                                            </div>
                                            <input id="attachment" name="attachment" type="file" class="hidden"
                                                onchange="document.getElementById('file-name').textContent = this.files[0]?.name || ''" />
                                        </label>
                                    </div>
                                    <p id="file-name" class="mt-3 text-sm text-indigo-600 dark:text-indigo-400 font-medium"></p>
                                </div>

                                <!-- Text Editor -->
                                <div wire:ignore class="mb-12">
                                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-5"
                                        style="margin-bottom: 1.25rem; display: block;">Update Notes</label>
                                    <div style="margin-top: 1rem;"
                                        class="bg-gray-50 dark:bg-slate-900 rounded-xl overflow-hidden border border-gray-200 dark:border-slate-600 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-shadow">
                                        <textarea id="jodit-editor" name="notes"
                                            placeholder="Write your daily update here...">{{ old('notes') }}</textarea>
                                    </div>
                                </div>

                                <div class="flex justify-end pt-8" style="margin-top: 2rem;">
                                    <button type="submit"
                                        class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm transition-colors flex items-center gap-2.5">
                                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>Save Update</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Initialize Jodit -->
                        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.css" />
                        <script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.js"></script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const isDark = document.documentElement.classList.contains('dark');

                                const editorOptions = {
                                    theme: isDark ? 'dark' : 'default',
                                    buttons: "bold,italic,underline,strikethrough,ul,ol,font,fontsize,paragraph,lineHeight,superscript,subscript,classSpan,file,image,video,spellcheck,cut,copy,paste,selectall,copyformat,hr,table,link,symbols,indent,outdent,left,brush,undo,redo,find,source,fullsize,preview,print",
                                    height: 250
                                };

                                const editor = Jodit.make('#jodit-editor', editorOptions);

                                // Make Jodit instances available globally so Alpine can init edit forms
                                window.editorOptions = editorOptions;
                            });
                        </script>
                    @endif
                @endif

                <!-- Daily Updates Timeline -->
                <div style="max-width: 1000px;" class="mb-4 mx-auto bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Update History</h3>

                    @if(isset($dailyUpdates) && $dailyUpdates->count() > 0)
                        <div class="mt-2">
                            @foreach($dailyUpdates as $update)
                                <div class="flex gap-4 md:gap-4 mb-4"
                                    x-data="{ editing: false, initEditor() { if (!this.$refs.editArea.jodit) { Jodit.make(this.$refs.editArea, window.editorOptions); } } }">

                                    <!-- Avatar Column -->
                                    <div class="flex flex-col items-center">
                                        <!-- Avatar -->
                                        <div
                                            class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0 border-2 border-white dark:border-slate-800 shadow-sm">
                                            {{ collect(explode(' ', $update->user->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                        </div>
                                    </div>

                                    <!-- Content Card Column -->
                                    <div class="flex-1">
                                        <div
                                            class="p-5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-2xl shadow-sm relative group transition-all duration-200 hover:shadow-md">
                                            <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
                                                <div>
                                                    <h4 class="text-base font-bold text-gray-900 dark:text-white">
                                                        {{ \Carbon\Carbon::parse($update->log_date)->format('l, F j, Y') }}
                                                    </h4>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                                        Logged by <span
                                                            class="font-semibold text-gray-800 dark:text-gray-300">{{ $update->user->name ?? 'Unknown' }}</span>
                                                    </p>
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    @if((auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager') || auth()->user()->id === $update->user_id))
                                                        <button @click="editing = true; $nextTick(() => initEditor())"
                                                            x-show="!editing"
                                                            class="opacity-0 group-hover:opacity-100 transition-opacity p-2 text-gray-500 hover:text-indigo-600 dark:text-gray-400 dark:hover:text-indigo-400 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-700/50"
                                                            title="Edit Update">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                                </path>
                                                            </svg>
                                                        </button>
                                                    @endif

                                                    @if($update->log_time)
                                                        <span
                                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-100 dark:border-emerald-800/30">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                            </svg>
                                                            {{ $update->log_time }} hrs
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Cleaned Prose Content -->
                                            @if($update->notes)
                                                <div x-show="!editing"
                                                    class="prose dark:prose-invert prose-sm max-w-none text-gray-700 dark:text-gray-300 mb-2 leading-relaxed update-content-clean">
                                                    {!! $update->notes !!}
                                                </div>
                                            @endif

                                            <!-- Edit Form -->
                                            <div x-show="editing" style="display: none;" class="mt-4 mb-2">
                                                <form
                                                    action="{{ route('crm-projects.daily-updates.update', ['project' => $project->id, 'update' => $update->id]) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div wire:ignore>
                                                        <textarea x-ref="editArea" name="notes"
                                                            class="hidden">{!! $update->notes !!}</textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-3 mt-4">
                                                        <button type="button" @click="editing = false"
                                                            class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors bg-gray-100 hover:bg-gray-200 dark:bg-slate-700/50 dark:hover:bg-slate-700 rounded-lg">Cancel</button>
                                                        <button type="submit"
                                                            class="px-5 py-2 text-sm font-bold text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">Save
                                                            Changes</button>
                                                    </div>
                                                </form>
                                            </div>

                                            @if($update->attachments->count() > 0 || $update->attachment_path)
                                                <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-700/60">
                                                    @if($update->attachments->count() > 0)
                                                        @foreach($update->attachments as $attachment)
                                                            <a href="{{ route('api.attachments.show', $attachment->id) }}" target="_blank"
                                                                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:hover:bg-slate-600 transition-colors mr-2 mb-2">
                                                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                                    </path>
                                                                </svg>
                                                                {{ $attachment->file_name ?? 'View Attachment' }}
                                                            </a>
                                                        @endforeach
                                                    @elseif($update->attachment_path)
                                                        <a href="{{ route('api.daily-updates.attachment.show', $update->id) }}"
                                                            target="_blank"
                                                            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 border border-gray-300 rounded-lg hover:bg-gray-100 dark:bg-slate-700 dark:border-slate-600 dark:text-white dark:hover:bg-slate-600 transition-colors">
                                                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                                </path>
                                                            </svg>
                                                            {{ $update->attachment_name ?? 'View Attachment' }}
                                                        </a>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="flex flex-col items-center justify-center w-full min-h-[11rem] px-6 py-10 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 dark:bg-slate-900 dark:border-slate-600 transition-colors">
                            <div class="flex flex-col items-center justify-center gap-3.5">
                                <svg class="w-9 h-9 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">No daily updates logged yet.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Project Info Sidebar -->
            <div class="lg:col-span-1">
                <div class="flex flex-col gap-6 mt-18">
                    <!-- Project Information Box -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 shrink-0">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Project Information</h3>

                        @php
                            $user = auth()->user();
                            $isSuperUser = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');

                            // 1. Total hours (estimated): Only show if Total Hours field in Project Details / Settings was provided
                            $hasSetTotalHours = !empty($project->crmDetails) && !is_null($project->crmDetails->log_hours) && (float) $project->crmDetails->log_hours > 0;
                            $detailsLogHours = (float) (optional($project->crmDetails)->log_hours ?? 0);

                            $changesCollection = $activities ?? $project->activities;
                            $changesHours = (float) $changesCollection->reduce(function ($carry, $act) {
                                return $carry + (float) preg_replace('/[^0-9.]/', '', $act->time_estimate ?? '0');
                            }, 0);
                            $enhancementsCollection = $enhancements ?? $project->enhancements;
                            $enhancementsHours = (float) $enhancementsCollection->reduce(function ($carry, $enh) {
                                return $carry + (float) preg_replace('/[^0-9.]/', '', $enh->time_estimate ?? '0');
                            }, 0);
                            $totalEstimatedHours = $detailsLogHours + $changesHours + $enhancementsHours;

                            // 2. Total logged hours calculation
                            $dailyUpdatesCollection = $dailyUpdates ?? $project->dailyUpdates;
                            $allLoggedHours = (float) $dailyUpdatesCollection->sum('log_time');
                            $userLoggedHours = (float) $dailyUpdatesCollection->where('user_id', $user->id)->sum('log_time');

                            $formatHrs = function ($val) {
                                return ($val == (int) $val) ? (int) $val : number_format($val, 1);
                            };
                        @endphp

                        <div class="space-y-4">
                            @if($project->crmDetails && ($project->crmDetails->start_date || $project->crmDetails->end_date))
                                <div>
                                    <p
                                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                                        Timeline</p>
                                    <p class="text-sm text-gray-900 dark:text-white font-medium">
                                        {{ $project->crmDetails->start_date ? \Carbon\Carbon::parse($project->crmDetails->start_date)->format('M d, Y') : 'N/A' }}
                                        &mdash;
                                        {{ $project->crmDetails->end_date ? \Carbon\Carbon::parse($project->crmDetails->end_date)->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>
                            @endif

                            @if($project->crmDetails && $project->crmDetails->description)
                                <div class="pt-4 border-t border-gray-100 dark:border-slate-700">
                                    <p
                                        class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                                        Description</p>
                                    <div class="prose dark:prose-invert prose-sm max-w-none text-gray-600 dark:text-gray-300 overflow-x-auto custom-scrollbar"
                                        style="max-height: 400px; overflow-y: auto;">
                                        {!! $project->crmDetails->description !!}
                                    </div>
                                </div>
                            @endif

                            <!-- Total hours (estimated): Only visible when Total Hours is set in Project Settings -->
                            @if($hasSetTotalHours)
                                <div
                                    class="pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Total hours
                                            (estimated)</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">From project settings</p>
                                    </div>
                                    <span
                                        class="text-lg font-bold text-gray-900 dark:text-white tracking-tight shrink-0 ml-4">{{ $formatHrs($detailsLogHours) }}
                                        hrs</span>
                                </div>

                                <!-- Changes hours row -->
                                <div
                                    class="pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Changes</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">From changes</p>
                                    </div>
                                    <span
                                        class="text-lg font-bold text-gray-900 dark:text-white tracking-tight shrink-0 ml-4">{{ $formatHrs($changesHours) }}
                                        hrs</span>
                                </div>

                                <!-- Enhancements hours row -->
                                <div
                                    class="pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-start">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Enhancements</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">From enhancements</p>
                                    </div>
                                    <span
                                        class="text-lg font-bold text-gray-900 dark:text-white tracking-tight shrink-0 ml-4">{{ $formatHrs($enhancementsHours) }}
                                        hrs</span>
                                </div>
                            @endif
                            <!-- Total Estimated Hours -->
                            <div
                                class="pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-start">

                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                        Total estimated hours
                                    </p>

                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                        Project + changes + enhancements
                                    </p>
                                </div>

                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white tracking-tight shrink-0 ml-4">
                                    {{ $formatHrs($totalEstimatedHours) }} hrs
                                </span>

                            </div>

                        </div>
                    </div> <!-- End of Project Information Box -->

                    <!-- Logged Hours Box -->
                    <div
                        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden mb-6">
                        <div class="p-5 space-y-4">
                            <!-- Total logged hours (All Assignees) -->
                            <div class="flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Total logged hours
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">All assignees daily
                                        updates</p>
                                </div>
                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white tracking-tight shrink-0 ml-4">{{ $formatHrs($allLoggedHours) }}
                                    hrs</span>
                            </div>

                            <!-- Your logged hours (Current User) -->
                            <div
                                class="pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Your logged hours</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">From your daily updates
                                    </p>
                                </div>
                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white tracking-tight shrink-0 ml-4">{{ $formatHrs($userLoggedHours) }}
                                    hrs</span>
                            </div>

                            <!-- Remaining time -->
                            <div
                                class="pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-start">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Remaining time</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Total estimated hours -
                                        Total logged hours</p>
                                </div>
                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white tracking-tight shrink-0 ml-4">{{ $formatHrs($totalEstimatedHours - $allLoggedHours) }}
                                    hrs</span>
                            </div>
                        </div>
                    </div> <!-- End of Logged Hours Box -->

                    @php
                        $totalActivityHours = $activities->sum(function ($item) {
                            if (!$item->time_estimate)
                                return 0;
                            return (float) preg_replace('/[^0-9.]/', '', $item->time_estimate);
                        });
                        $totalEnhancementHours = $enhancements->sum(function ($item) {
                            if (!$item->time_estimate)
                                return 0;
                            return (float) preg_replace('/[^0-9.]/', '', $item->time_estimate);
                        });
                    @endphp

                    <!-- Changes Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 shrink-0"
                        x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between focus:outline-none group"
                            :class="{'mb-4': open}">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Changes</h3>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:text-gray-400 dark:group-hover:text-white transition-transform duration-200"
                                    :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300 border border-gray-200 dark:border-slate-600">
                                ACTIVITY
                            </span>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms>
                            <div class="flex items-center justify-between text-sm mb-4">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total
                                    Changes</span>
                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">{{ $activities->count() }}</span>
                            </div>

                            @if($activities->count() > 0)
                                <div class="space-y-4 border-t border-gray-100 dark:border-slate-700/60 pt-4">
                                    @foreach($activities as $item)
                                        <div
                                            class="py-3 border-b border-gray-100 dark:border-slate-700/60 last:border-0 text-left">
                                            <p class="text-sm text-gray-900 dark:text-white mb-1">{{ $item->description }}</p>
                                            @if($item->attachment_path)
                                                <div class="mb-2">
                                                    <a href="{{ route('api.activities.attachment.show', $item->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800/40">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                            </path>
                                                        </svg>
                                                        <span
                                                            class="truncate max-w-[200px]">{{ $item->attachment_name ?? 'Attachment' }}</span>
                                                    </a>
                                                </div>
                                            @endif
                                            @if($item->time_estimate)
                                                <div
                                                    class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2 bg-indigo-50 dark:bg-indigo-900/30 w-max px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ trim(preg_replace('/(hrs?|hours?)$/i', '', $item->time_estimate)) }} hrs
                                                </div>
                                            @else
                                                <div class="mb-2"></div>
                                            @endif
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-500 dark:text-gray-400"
                                                    x-data="{ date: new Date('{{ $item->created_at->toISOString() }}') }"
                                                    x-text="date.toLocaleString('en-US')">{{ $item->created_at->format('n/j/Y, g:i:s A') }}</span>
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($item->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($item->user)->name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center w-full min-h-[6rem] px-4 py-4 border border-dashed rounded-xl bg-gray-50 dark:bg-slate-900 border-gray-200 dark:border-slate-700 transition-colors">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium text-center">No recent
                                        changes recorded.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Enhancements Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 shrink-0"
                        x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between focus:outline-none group"
                            :class="{'mb-4': open}">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Enhancements</h3>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:text-gray-400 dark:group-hover:text-white transition-transform duration-200"
                                    :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300 border border-gray-200 dark:border-slate-600">
                                ACTIVITY
                            </span>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms>
                            <div class="flex items-center justify-between text-sm mb-4">
                                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">Total
                                    Enhancements</span>
                                <span
                                    class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">{{ $enhancements->count() }}</span>
                            </div>

                            @if($enhancements->count() > 0)
                                <div class="space-y-4 border-t border-gray-100 dark:border-slate-700/60 pt-4">
                                    @foreach($enhancements as $item)
                                        <div
                                            class="py-3 border-b border-gray-100 dark:border-slate-700/60 last:border-0 text-left">
                                            <p class="text-sm text-gray-900 dark:text-white mb-1">{{ $item->description }}</p>
                                            @if($item->attachment_path)
                                                <div class="mb-2">
                                                    <a href="{{ route('api.enhancements.attachment.show', $item->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800/40">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                            </path>
                                                        </svg>
                                                        <span
                                                            class="truncate max-w-[200px]">{{ $item->attachment_name ?? 'Attachment' }}</span>
                                                    </a>
                                                </div>
                                            @endif
                                            @if($item->time_estimate)
                                                <div
                                                    class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2 bg-indigo-50 dark:bg-indigo-900/30 w-max px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ trim(preg_replace('/(hrs?|hours?)$/i', '', $item->time_estimate)) }} hrs
                                                </div>
                                            @else
                                                <div class="mb-2"></div>
                                            @endif
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-500 dark:text-gray-400"
                                                    x-data="{ date: new Date('{{ $item->created_at->toISOString() }}') }"
                                                    x-text="date.toLocaleString('en-US')">{{ $item->created_at->format('n/j/Y, g:i:s A') }}</span>
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($item->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($item->user)->name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center w-full min-h-[6rem] px-4 py-4 border border-dashed rounded-xl bg-gray-50 dark:bg-slate-900 border-gray-200 dark:border-slate-700 transition-colors">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium text-center">No recent
                                        enhancements recorded.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- To-Do List Card -->
                    <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 shrink-0"
                        x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between focus:outline-none group"
                            :class="{'mb-4': open}">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">To-Do List</h3>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:text-gray-400 dark:group-hover:text-white transition-transform duration-200"
                                    :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300 border border-gray-200 dark:border-slate-600">
                                ACTIVITY
                            </span>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms>
                            <!-- Add To-Do Form -->
                            <form action="{{ route('crm-projects.todos.store', $project->id) }}" method="POST"
                                class="mb-6">
                                @csrf
                                <div class="mb-3">
                                    <div class="relative flex items-center bg-slate-900/50 dark:bg-slate-900/70 border border-slate-700/80 focus-within:border-indigo-500 rounded-lg overflow-hidden transition-colors">
                                        <textarea name="description" autocomplete="off" rows="1"
                                            style="background-color: transparent !important; color: #e2e8f0 !important; border: 0 !important; box-shadow: none !important; resize: none; max-height: 140px; min-height: 44px; outline: none !important;"
                                            class="w-full min-w-0 bg-transparent border-0 text-slate-200 placeholder-slate-400 focus:ring-0 text-sm py-3 px-3 focus:outline-none"
                                            placeholder="What needs to be done?" required></textarea>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 flex-1">
                                        <select name="duration_type"
                                            class="block rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors">
                                            <option value="days">Days</option>
                                            <option value="weeks">Weeks</option>
                                            <option value="months">Months</option>
                                        </select>
                                        <input type="hidden" name="duration_value" value="1">
                                    </div>
                                    <button type="submit"
                                        class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add To-Do
                                    </button>
                                </div>
                            </form>

                            @php $todosCollection = $todos ?? $project->todos; @endphp

                            @if($todosCollection->count() > 0)
                                <div class="space-y-4 border-t border-gray-100 dark:border-slate-700/60 pt-4">
                                    @foreach($todosCollection as $todo)
                                        <div
                                            class="py-3 border-b border-gray-100 dark:border-slate-700/60 last:border-0 text-left">
                                            <p class="text-sm text-gray-900 dark:text-white mb-1">{{ $todo->description }}</p>
                                            <div
                                                class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2 bg-indigo-50 dark:bg-indigo-900/30 w-max px-2 py-0.5 rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $todo->duration_value }} {{ ucfirst($todo->duration_type) }}
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-500 dark:text-gray-400"
                                                    x-data="{ date: new Date('{{ $todo->created_at->toISOString() }}') }"
                                                    x-text="date.toLocaleString('en-US')">{{ $todo->created_at->format('n/j/Y, g:i:s A') }}</span>
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($todo->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($todo->user)->name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center w-full min-h-[6rem] px-4 py-4 border border-dashed rounded-xl bg-gray-50 dark:bg-slate-900 border-gray-200 dark:border-slate-700 transition-colors">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium text-center">No
                                        to-do items added yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div> <!-- End of Sticky Wrapper -->
            </div>
        </div>
    </div>

    <!-- Ensure styling for jodit in dark mode doesn't break input text -->
    <style>
        [x-cloak] {
            display: none !important;
        }

        .jodit-container {
            border: 1px solid #d1d5db !important;
            border-radius: 0.5rem !important;
            overflow: hidden !important;
        }

        .dark .jodit-container {
            border: 1px solid #334155 !important;
            /* slate-700 */
        }

        /* Custom Jodit Dark Theme Overrides to match Tailwind Slate */
        .dark .jodit_theme_dark,
        .dark .jodit-container {
            --jd-color-background-default: #1e293b !important;
            /* slate-800 */
            --jd-color-panel: #0f172a !important;
            /* slate-900 for toolbar/statusbar */
            --jd-color-border: #334155 !important;
            /* slate-700 */
            --jd-color-icon: #94a3b8 !important;
            /* slate-400 */
            --jd-color-text: #f8fafc !important;
            /* slate-50 */
        }

        /* Fallback direct targeting just in case CSS variables aren't picked up */
        .dark .jodit-workplace,
        .dark .jodit-wysiwyg {
            background-color: #ffffff !important;
            color: #111827 !important;
        }

        .dark .jodit-toolbar__box {
            background-color: #0f172a !important;
            border-bottom: 1px solid #334155 !important;
        }

        .dark .jodit-status-bar {
            background-color: #0f172a !important;
            border-top: 1px solid #334155 !important;
            color: #94a3b8 !important;
        }

        .dark .jodit-toolbar-button__button:hover {
            background-color: #1e293b !important;
        }

        .dark .jodit-toolbar-button__button {
            color: #cbd5e1 !important;
        }

        /* Override inline background colors copied from rich text editors in dark mode */
        .dark .prose *:not(pre):not(code) {
            background-color: transparent;
            color: inherit;
        }

        /* Ensure tables are visible and have borders */
        .prose table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            margin-bottom: 1rem;
        }

        .prose table th,
        .prose table td {
            border: 1px solid #d1d5db !important;
            padding: 0.75rem !important;
        }

        .dark .prose table th,
        .dark .prose table td {
            border-color: #475569 !important;
        }

        /* Fix for dark calendar icon in date inputs */
        .dark input[type="date"]::-webkit-calendar-picker-indicator {
            filter: invert(1);
        }
    </style>
</x-app-layout>