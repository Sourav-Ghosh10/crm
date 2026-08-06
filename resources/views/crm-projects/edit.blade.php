<x-app-layout>
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $details ? 'Update Project Details' : 'Add Project Details' }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage extended details for project: <strong>{{ $project->project_name }}</strong></p>
            </div>
            <a href="{{ route('crm-projects.show', $project->id) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Project
            </a>
        </div>

        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-100 dark:border-slate-700 overflow-visible">
            <form action="{{ route('crm-projects.details.store', $project->id) }}" method="POST">
                @csrf
                <div class="px-6 py-6 sm:p-8">
                    @php
                        $user = auth()->user();
                        $canEditDetails = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
                        $canManageDates = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
                        $crmStatus = $details ? ($details->status ?? 'Active') : 'Active';
                    @endphp

                    <!-- Project Status Banner / Complete Button -->
                    <div class="mb-8 p-4 rounded-xl border flex items-center justify-between {{ $crmStatus === 'Completed' ? 'bg-emerald-500/10 border-emerald-500/25 dark:bg-emerald-950/20 dark:border-emerald-800/60' : 'bg-amber-500/10 border-amber-500/25 dark:bg-amber-950/20 dark:border-amber-800/60' }}">
                        <div class="flex items-center gap-3">
                            <span class="flex h-3 w-3 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $crmStatus === 'Completed' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 {{ $crmStatus === 'Completed' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                            </span>
                            <div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Project Status: </span>
                                <span class="text-sm font-bold uppercase tracking-wider {{ $crmStatus === 'Completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                    {{ $crmStatus }}
                                </span>
                            </div>
                        </div>
                        <div>
                            @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager'))
                                @if($crmStatus !== 'Completed')
                                    <button type="submit" name="complete_project" value="1" formnovalidate class="inline-flex justify-center items-center gap-1.5 rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none transition-all duration-150 active:scale-95">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        Mark as Completed
                                    </button>
                                @else
                                    <button type="submit" name="reopen_project" value="1" formnovalidate class="inline-flex justify-center items-center gap-1.5 rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none transition-all duration-150 active:scale-95">
                                        Reopen Project
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>

                    @if($canEditDetails)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6" style="margin-bottom: 32px;">
                        <div class="md:col-span-2">
                            <!-- Jodit Editor Stylesheets and Scripts -->
                            <link class="jodit-assets" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.css"/>
                            <script src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.js"></script>
                            <style>
                                /* Align Jodit rounded corners and styling */
                                .jodit-container {
                                    border-radius: 0.75rem !important;
                                    border: 1px solid #e2e8f0 !important;
                                    background-color: #ffffff !important;
                                    overflow: hidden !important;
                                }
                                .dark .jodit-container {
                                    border: 1px solid #475569 !important;
                                }
                                /* Toolbar styling */
                                .jodit-container .jodit-toolbar__box {
                                    background-color: #f8fafc !important;
                                    border-bottom: 1px solid #e2e8f0 !important;
                                }
                                .dark .jodit-container .jodit-toolbar__box {
                                    background-color: #334155 !important;
                                    border-bottom: 1px solid #475569 !important;
                                }
                                /* Toolbar button icons */
                                .jodit-container .jodit-toolbar-button__button {
                                    color: #475569 !important;
                                }
                                .dark .jodit-container .jodit-toolbar-button__button {
                                    color: #cbd5e1 !important;
                                }
                                .jodit-container .jodit-toolbar-button__button:hover {
                                    background-color: #e2e8f0 !important;
                                }
                                .dark .jodit-container .jodit-toolbar-button__button:hover {
                                    background-color: #475569 !important;
                                }
                                /* Text editor area styling */
                                .jodit-container .jodit-workplace {
                                    background-color: #ffffff !important;
                                    padding: 0px !important;
                                }
                                .jodit-container .jodit-wysiwyg {
                                    background-color: #ffffff !important;
                                    color: #0f172a !important;
                                    padding: 20px !important;
                                }
                                .jodit-container .jodit-placeholder {
                                    padding: 20px !important;
                                }
                                /* Status bar styling */
                                .jodit-container .jodit-status-bar {
                                    background-color: #f8fafc !important;
                                    border-top: 1px solid #e2e8f0 !important;
                                    color: #64748b !important;
                                    font-size: 11px !important;
                                }
                                .dark .jodit-container .jodit-status-bar {
                                    background-color: #334155 !important;
                                    border-top: 1px solid #475569 !important;
                                    color: #94a3b8 !important;
                                }
                                                            /* Hide number input spinners to prevent overlap with suffix */
                                input[type=number]::-webkit-outer-spin-button,
                                input[type=number]::-webkit-inner-spin-button {
                                    -webkit-appearance: none;
                                    margin: 0;
                                }
                                input[type=number] {
                                    -moz-appearance: textfield;
                                }
                                /* Override Jodit editor table hover and selection highlighting to keep them white */
                                .jodit-container .jodit-wysiwyg table,
                                .jodit-container .jodit-wysiwyg table tr,
                                .jodit-container .jodit-wysiwyg table td {
                                    background-color: #ffffff !important;
                                    color: #0f172a !important;
                                    border: 1px solid #cbd5e1 !important;
                                }
                                .jodit-container .jodit-wysiwyg table tr:hover,
                                .jodit-container .jodit-wysiwyg table td:hover,
                                .jodit-container .jodit-wysiwyg table tr:hover td,
                                .jodit-container .jodit-wysiwyg table td[data-selected],
                                .jodit-container .jodit-wysiwyg table td.jodit_selected_cell {
                                    background-color: #ffffff !important;
                                    color: #0f172a !important;
                                }
                            </style>

                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            
                            <!-- Jodit uses a standard textarea and binds to it directly -->
                            <textarea name="description" id="description-editor" class="w-full">{{ old('description', $details->description ?? '') }}</textarea>

                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    const isDark = document.documentElement.classList.contains('dark');
                                    const editor = new Jodit('#description-editor', {
                                        theme: isDark ? 'dark' : 'default',
                                        height: 250,
                                        placeholder: 'Enter project description...',
                                        toolbarButtonSize: 'middle',
                                        readonly: {{ $canEditDetails ? 'false' : 'true' }},
                                        toolbar: {{ $canEditDetails ? 'true' : 'false' }},
                                        // Define standard font families like Word
                                        controls: {
                                            font: {
                                                list: {
                                                    'sans-serif': 'Sans Serif',
                                                    'serif': 'Serif',
                                                    'monospace': 'Monospace',
                                                    'Arial,Helvetica,sans-serif': 'Arial',
                                                    'Georgia,serif': 'Georgia',
                                                    'Impact,Charcoal,sans-serif': 'Impact',
                                                    '"Courier New",Courier,monospace': 'Courier New',
                                                    '"Comic Sans MS",cursive,sans-serif': 'Comic Sans',
                                                    '"Times New Roman",Times,serif': 'Times New Roman',
                                                    'Verdana,Geneva,sans-serif': 'Verdana'
                                                }
                                            }
                                        }
                                    });
                                });
                            </script>
                        </div>
                    </div>
                    @endif

                    @if($canEditDetails)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6" style="margin-bottom: 32px;">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Start Date <span class="text-red-500">*</span></label>
                                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $details->start_date ?? '') }}" 
                                       class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 {{ !$canManageDates ? 'opacity-65 cursor-not-allowed bg-gray-50 dark:bg-slate-800/40' : '' }}"
                                       required {{ !$canManageDates ? 'disabled' : '' }}>
                                @error('start_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">End Date <span class="text-red-500">*</span></label>
                                <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $details->end_date ?? '') }}" 
                                       class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-4 py-3 {{ !$canManageDates ? 'opacity-65 cursor-not-allowed bg-gray-50 dark:bg-slate-800/40' : '' }}"
                                       required {{ !$canManageDates ? 'disabled' : '' }}>
                                @error('end_date') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const startDateInput = document.getElementById('start_date');
                                    const endDateInput = document.getElementById('end_date');

                                    if (startDateInput && endDateInput) {
                                        function updateMinEndDate() {
                                            if (startDateInput.value) {
                                                endDateInput.min = startDateInput.value;
                                            } else {
                                                endDateInput.removeAttribute('min');
                                            }
                                        }
                                        startDateInput.addEventListener('change', updateMinEndDate);
                                        updateMinEndDate();
                                    }
                                });
                            </script>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Hours</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" step="0.5" name="log_hours" value="{{ old('log_hours', $details->log_hours ?? '') }}" 
                                           class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-4 pr-12 py-3 {{ !$canEditDetails ? 'opacity-65 cursor-not-allowed bg-gray-50 dark:bg-slate-800/40' : '' }}" 
                                           placeholder="0.00"
                                           {{ !$canEditDetails ? 'disabled' : '' }}>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 dark:text-gray-400 sm:text-sm">hrs</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                        @php
                            $user = auth()->user();
                            $canManageAssignments = auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager') || auth()->user()->hasRole('team-lead');
                        @endphp
                        @if(true)
                            @php
                            $usersJson = $users->map(function($u) {
                                $dbRole = $u->roles->first();
                                $roleName = $dbRole ? $dbRole->name : $u->role;
                                $normalizedRole = strtolower(str_replace(' ', '-', $roleName ?? ''));
                                if ($normalizedRole === 'admin' || $normalizedRole === 'administrator') {
                                    $normalizedRole = 'super-admin';
                                }
                                return ['id' => (string) $u->id, 'name' => $u->name, 'role' => $normalizedRole];
                            })->toJson();
                            
                            $groupedAssignees = $project->assignees->groupBy(function($user) {
                                $dbRole = $user->roles->first();
                                $roleName = $dbRole ? $dbRole->name : $user->role;
                                $normalizedRole = strtolower(str_replace(' ', '-', $roleName ?? ''));
                                if ($normalizedRole === 'admin' || $normalizedRole === 'administrator') {
                                    $normalizedRole = 'super-admin';
                                }
                                return $normalizedRole;
                            });
                            
                            $currentAssignees = [];
                            $readonlyAssignees = [];
                            foreach($groupedAssignees as $role => $usersInRole) {
                                $roleName = strtolower($role);
                                $user = auth()->user();
                                
                                $isReadonly = false;
                                if ($user->hasRole('project-manager')) {
                                    if (in_array($roleName, ['super-admin', 'manager', 'project-manager'])) {
                                        $isReadonly = true;
                                    }
                                } elseif ($user->hasRole('team-lead')) {
                                    if (in_array($roleName, ['super-admin', 'manager', 'project-manager', 'team-lead'])) {
                                        $isReadonly = true;
                                    }
                                }
                                
                                if ($isReadonly) {
                                    foreach ($usersInRole as $u) {
                                        $dbRole = $u->roles->first();
                                        $rName = $dbRole ? $dbRole->name : $u->role;
                                        $readonlyAssignees[] = ['name' => $u->name, 'role' => ucwords(str_replace('-', ' ', $rName))];
                                    }
                                    continue;
                                }
                                
                                $currentAssignees[] = [
                                    'role' => $roleName,
                                    'user_ids' => $usersInRole->pluck('id')->map(fn($id) => (string)$id)->toArray()
                                ];
                            }
                            
                            if (empty($currentAssignees)) {
                                $currentAssignees = [['role' => '', 'user_ids' => []]];
                            }
                            
                            $validRoles = $roles->pluck('name')->map(fn($r) => strtolower($r))->toArray();
                        @endphp

                        <div x-data="{
                            assignees: {{ json_encode($currentAssignees) }},
                            users: {{ $usersJson }},
                            addAssignee() {
                                this.assignees.push({role: '', user_ids: []});
                            },
                            removeAssignee(index) {
                                if (this.assignees.length > 1) {
                                    this.assignees.splice(index, 1);
                                }
                            },
                            getFilteredUsers(role) {
                                if (!role) return [];
                                return this.users.filter(u => u.role === role);
                            }
                        }">
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assignees <span class="text-red-500">*</span></label>
                                    @error('assignee_ids') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>
                                @if($canManageAssignments)
                                    <button type="button" @click="addAssignee" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-400 dark:hover:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                        <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Add Assignee
                                    </button>
                                @endif
                            </div>
                            
                            @if(!empty($readonlyAssignees))
                                <div class="mb-4 space-y-2 hidden">
                                    @foreach($readonlyAssignees as $roAssignee)
                                        <div class="flex items-center gap-4 px-4 py-3 bg-gray-50 dark:bg-slate-800/40 border border-gray-200 dark:border-slate-700 rounded-lg">
                                            <div class="w-1/2 flex items-center">
                                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Role: </span>
                                                <span class="text-sm text-gray-900 dark:text-gray-300 ml-2 font-semibold">{{ $roAssignee['role'] }}</span>
                                            </div>
                                            <div class="w-1/2 flex items-center">
                                                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Name: </span>
                                                <span class="text-sm text-gray-900 dark:text-gray-300 ml-2 font-semibold">{{ $roAssignee['name'] }}</span>
                                            </div>
                                            <span class="text-xs text-gray-400 ml-auto italic whitespace-nowrap" title="You do not have permission to modify higher-level roles.">Read-only</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                            
                            <template x-for="(assignee, index) in assignees" :key="index">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 relative" :style="{{ $canManageAssignments ? 'true' : 'false' }} ? 'padding-right: 56px;' : ''">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" x-show="index === 0">Role</label>
                                        <div class="relative rounded-md shadow-sm">
                                            <select x-model="assignee.role" @change="assignee.user_ids = []" class="w-full rounded-lg border-gray-300 dark:border-slate-600 dark:bg-slate-700 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-4 pr-4 py-3 appearance-none {{ !$canManageAssignments ? 'opacity-75 cursor-not-allowed bg-gray-50 dark:bg-slate-800/40' : '' }}" :class="assignee.role === '' ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white'" {{ !$canManageAssignments ? 'disabled' : '' }}>
                                                <option value="" class="text-gray-500 dark:text-gray-400">Select Role...</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ strtolower($role->name) }}" class="text-gray-900 dark:text-white">{{ $role->display_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
        
                                    <div x-data="{ open: false, search: '' }" @click.away="open = false; search = ''" class="relative">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1" x-show="index === 0">Employee</label>
                                        
                                        <!-- Hidden inputs for form submission -->
                                        <template x-for="id in assignee.user_ids" :key="id">
                                            <input type="hidden" name="assignee_ids[]" :value="id">
                                        </template>                                        
                                        <!-- Custom Dropdown Trigger -->
                                         <div @click="if({{ $canManageAssignments ? 'true' : 'false' }} && assignee.role) open = !open" 
                                              class="w-full relative rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-700 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-3 cursor-pointer transition-colors"
                                              style="padding-left: 1.25rem; padding-right: 2.5rem;"
                                              :class="!assignee.role ? 'bg-gray-50 dark:bg-slate-700 text-gray-400 dark:text-slate-500 opacity-60 cursor-not-allowed' : (assignee.user_ids.length === 0 ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white') + ({{ $canManageAssignments ? 'false' : 'true' }} ? ' cursor-not-allowed opacity-75' : '')"
                                              tabindex="0">
                                            
                                            <span class="block truncate" x-text="!assignee.role ? 'Select Role First...' : (assignee.user_ids.length === 0 ? 'Select Employees...' : assignee.user_ids.map(id => getFilteredUsers(assignee.role).find(u => u.id == id)?.name).filter(Boolean).join(', '))"></span>
                                            
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none" :class="!assignee.role && 'opacity-50'">
                                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </span>
                                        </div>
                                        
                                        <!-- Custom Dropdown Menu -->
                                        <div x-show="open" 
                                             x-transition:enter="transition ease-out duration-100"
                                             x-transition:enter-start="transform opacity-0 scale-95"
                                             x-transition:enter-end="transform opacity-100 scale-100"
                                             x-transition:leave="transition ease-in duration-75"
                                             x-transition:leave-start="transform opacity-100 scale-100"
                                             x-transition:leave-end="transform opacity-0 scale-95"
                                             class="z-50 mt-1 w-full bg-white dark:bg-slate-700 shadow-xl rounded-md text-base border border-gray-200 dark:border-slate-600 overflow-hidden sm:text-sm"
                                             style="display: none;">
                                            
                                            <!-- Sticky Search Bar -->
                                            <div class="p-2 border-b border-gray-100 dark:border-slate-600 bg-gray-50 dark:bg-slate-700/50">
                                                <div class="relative">
                                                    <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                        </svg>
                                                    </div>
                                                    <input type="text" x-model="search" @click.stop class="w-full border border-gray-300 dark:border-slate-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white rounded-md shadow-sm sm:text-sm pl-9 pr-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 outline-none" placeholder="Search team members...">
                                                </div>
                                            </div>
 
                                            <!-- Scrollable List -->
                                            <div class="overflow-y-auto" style="max-height: 180px; scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                                                <style>
                                                    /* Custom scrollbar styling for WebKit browsers */
                                                    .overflow-y-auto::-webkit-scrollbar { width: 6px; }
                                                    .overflow-y-auto::-webkit-scrollbar-track { background: transparent; }
                                                    .overflow-y-auto::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
                                                    .dark .overflow-y-auto::-webkit-scrollbar-thumb { background-color: #475569; }
                                                </style>
                                                <template x-for="user in getFilteredUsers(assignee.role).filter(u => u.name.toLowerCase().includes(search.toLowerCase()))" :key="user.id">
                                                    <div @click="
                                                            const idx = assignee.user_ids.indexOf(user.id);
                                                            if (idx > -1) { assignee.user_ids.splice(idx, 1); }
                                                            else { assignee.user_ids.push(user.id); }
                                                         "
                                                         class="cursor-pointer select-none relative py-2.5 pl-4 pr-4 hover:bg-gray-100 dark:hover:bg-slate-600 text-gray-900 dark:text-white transition-colors"
                                                         :class="assignee.user_ids.includes(user.id) ? 'bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-400' : ''">
                                                        <div class="flex items-center">
                                                             <!-- Square Checkbox -->
                                                            <div class="h-4 w-4 rounded-sm border flex items-center justify-center mr-3 flex-shrink-0 transition-colors"
                                                                 :class="assignee.user_ids.includes(user.id) ? 'bg-indigo-600 border-indigo-600' : 'border-gray-300 dark:border-slate-500 bg-white dark:bg-slate-800'">
                                                                <svg x-show="assignee.user_ids.includes(user.id)" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                                                </svg>
                                                            </div>
                                                             
                                                             <!-- Employee Name -->
                                                            <span class="block break-words" :class="assignee.user_ids.includes(user.id) ? 'font-medium' : 'font-normal'" x-text="user.name"></span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <div x-show="getFilteredUsers(assignee.role).filter(u => u.name.toLowerCase().includes(search.toLowerCase())).length === 0" class="py-3 px-4 text-gray-500 dark:text-gray-400 text-center">
                                                    No team members found.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($canManageAssignments)
                                        <div class="absolute right-0 flex items-center justify-center" :style="index === 0 ? 'top: 30px;' : 'top: 4px;'">
                                            <button type="button" @click="removeAssignee(index)" x-show="assignees.length > 1" class="p-2.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors border border-transparent hover:border-red-200 dark:hover:border-red-900" title="Remove Role Group">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                            <div class="w-[44px]" x-show="assignees.length <= 1"></div>
                                        </div>
                                    @endif
                                </div>
                            </template>
                        </div>
                    @endif
                
                <div class="px-6 pb-8 flex items-center justify-end gap-4">
                    <a href="{{ route('crm-projects.show', $project->id) }}" class="inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 shadow-sm px-6 py-2.5 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none transition-colors">
                        {{ $details ? 'Update Details' : 'Save Details' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
