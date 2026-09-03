<x-app-layout>
    <div>
        <!-- Header Section -->
        <div class="mb-8" style="margin-bottom: 4rem;">
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

                <a href="{{ route('crm-projects.show', $project->id) }}"
                    class="flex items-center shrink-0 gap-2 px-3 py-1.5 text-sm font-medium text-gray-500 hover:text-indigo-600 bg-gray-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-gray-400 dark:hover:text-indigo-400 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Project
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2">
                <div style="max-width: 1000px;"
                    class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6 sm:p-8 mb-8 mx-auto overflow-visible">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-8 flex items-center gap-3">
                        <svg class="w-5 h-5 text-indigo-500 shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                            </path>
                        </svg>
                        <span>{{ $details ? 'Update Project Details' : 'Add Project Details' }}</span>
                    </h2>

                    <form id="details-form" action="{{ route('crm-projects.details.store', $project->id) }}" method="POST">
                        @csrf
                        <div>
                            @php
                                $user = auth()->user();
                                $canEditDetails = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
                                $canManageDates = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager');
                                $crmStatus = $details ? ($details->status ?? 'Active') : 'Active';
                            @endphp

                            <!-- Project Status Banner / Complete Button -->
                            <div
                                class="mb-8 p-4 rounded-xl border flex items-center justify-between {{ $crmStatus === 'Completed' ? 'bg-emerald-500/10 border-emerald-500/25 dark:bg-emerald-950/20 dark:border-emerald-800/60' : 'bg-amber-500/10 border-amber-500/25 dark:bg-amber-950/20 dark:border-amber-800/60' }}">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-3 w-3 relative">
                                        <span
                                            class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75 {{ $crmStatus === 'Completed' ? 'bg-emerald-400' : 'bg-amber-400' }}"></span>
                                        <span
                                            class="relative inline-flex rounded-full h-3 w-3 {{ $crmStatus === 'Completed' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                                    </span>
                                    <div>
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white">Project
                                            Status: </span>
                                        <span
                                            class="text-sm font-bold uppercase tracking-wider {{ $crmStatus === 'Completed' ? 'text-emerald-600 dark:text-emerald-400' : 'text-amber-600 dark:text-amber-400' }}">
                                            {{ $crmStatus }}
                                        </span>
                                    </div>
                                </div>
                                <div>
                                    @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager'))
                                        @if($crmStatus !== 'Completed')
                                            <button type="submit" name="complete_project" value="1" formnovalidate
                                                class="inline-flex justify-center items-center gap-1.5 rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none transition-all duration-150 active:scale-95">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7"></path>
                                                </svg>
                                                Mark as Completed
                                            </button>
                                        @else
                                            <button type="submit" name="reopen_project" value="1" formnovalidate
                                                class="inline-flex justify-center items-center gap-1.5 rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none transition-all duration-150 active:scale-95">
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
                                        <link class="jodit-assets" rel="stylesheet"
                                            href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.css" />
                                        <script
                                            src="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.js"></script>
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

                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-5"
                                            style="margin-bottom: 1.25rem; display: block;">Description</label>

                                        <!-- Jodit uses a standard textarea and binds to it directly -->
                                        <div style="margin-top: 1rem;"
                                            class="bg-gray-50 dark:bg-slate-900 rounded-xl overflow-hidden border border-gray-200 dark:border-slate-600 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-shadow">
                                            <textarea name="description" id="description-editor" autocomplete="off"
                                                class="w-full">{{ old('description', $details->description ?? '') }}</textarea>
                                        </div>

                                        <script>
                                            document.addEventListener('DOMContentLoaded', function () {
                                                const isDark = document.documentElement.classList.contains('dark');
                                                const editor = new Jodit('#description-editor', {
                                                    theme: isDark ? 'dark' : 'default',
                                                    height: 250,
                                                    placeholder: 'Enter project description...',
                                                    toolbarButtonSize: 'middle',
                                                    readonly: {{ $canEditDetails ? 'false' : 'true' }},
                                                    toolbar: {{ $canEditDetails ? 'true' : 'false' }},
                                                    autofocus: false,
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
                                        <label
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Start
                                            Date <span class="text-red-500">*</span></label>
                                        <input type="date" name="start_date" id="start_date"
                                            value="{{ old('start_date', $details->start_date ?? '') }}"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block px-5 py-4 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-gray-400 dark:text-white transition-colors {{ !$canManageDates ? 'opacity-65 cursor-not-allowed bg-gray-100 dark:bg-slate-800/40' : '' }}"
                                            required {{ !$canManageDates ? 'disabled' : '' }}>
                                        @error('start_date') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">End
                                            Date <span class="text-red-500">*</span></label>
                                        <input type="date" name="end_date" id="end_date"
                                            value="{{ old('end_date', $details->end_date ?? '') }}"
                                            class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block px-5 py-4 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-gray-400 dark:text-white transition-colors {{ !$canManageDates ? 'opacity-65 cursor-not-allowed bg-gray-100 dark:bg-slate-800/40' : '' }}"
                                            required {{ !$canManageDates ? 'disabled' : '' }}>
                                        @error('end_date') <span
                                        class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
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
                                        <label
                                            class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Total
                                            Hours</label>
                                        <div class="relative">
                                            <input type="number" step="0.5" name="log_hours"
                                                value="{{ old('log_hours', $details->log_hours ?? '') }}"
                                                class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block px-5 py-4 dark:bg-slate-900 dark:border-slate-600 dark:placeholder-gray-400 dark:text-white pr-14 transition-colors {{ !$canEditDetails ? 'opacity-65 cursor-not-allowed bg-gray-100 dark:bg-slate-800/40' : '' }}"
                                                placeholder="0.00" {{ !$canEditDetails ? 'disabled' : '' }}>
                                            <div
                                                class="absolute inset-y-0 right-0 flex items-center pr-5 pointer-events-none">
                                                <span class="text-gray-500 dark:text-gray-400 text-base">hrs</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @php
                                $user = auth()->user();
                                $hasRoleToManageAssignments = auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager') || auth()->user()->hasRole('team-lead');
                                $isCompleted = $crmStatus === 'Completed';
                                $canManageAssignments = $hasRoleToManageAssignments && !$isCompleted;
                            @endphp
                            @if(true)
                                @php
                                    $usersJson = $users->map(function ($u) {
                                        $dbRole = $u->roles->first();
                                        $roleName = $dbRole ? $dbRole->name : $u->role;
                                        $normalizedRole = strtolower(str_replace(' ', '-', $roleName ?? ''));
                                        if ($normalizedRole === 'admin' || $normalizedRole === 'administrator') {
                                            $normalizedRole = 'super-admin';
                                        }
                                        return ['id' => (string) $u->id, 'name' => $u->name, 'role' => $normalizedRole];
                                    })->toJson();

                                    $groupedAssignees = $project->assignees->groupBy(function ($user) {
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
                                    foreach ($groupedAssignees as $role => $usersInRole) {
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
                                            'user_ids' => $usersInRole->pluck('id')->map(fn($id) => (string) $id)->toArray()
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
                                            <label
                                                class="block text-sm font-medium text-gray-700 dark:text-gray-300">Assignees
                                                <span class="text-red-500">*</span></label>
                                            @error('assignee_ids') <span
                                            class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                        @if($hasRoleToManageAssignments)
                                            @if($isCompleted)
                                                <button type="button"
                                                    onclick="alert('The project is completed. Please reopen the project to add assignees.')"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-400 dark:hover:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Add Assignee
                                                </button>
                                            @else
                                                <button type="button" @click="addAssignee"
                                                    class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-900/50 dark:text-indigo-400 dark:hover:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                                    <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Add Assignee
                                                </button>
                                            @endif
                                        @endif
                                    </div>

                                    @if(!empty($readonlyAssignees))
                                        <div class="mb-4 space-y-2 hidden">
                                            @foreach($readonlyAssignees as $roAssignee)
                                                <div
                                                    class="flex items-center gap-4 px-4 py-3 bg-gray-50 dark:bg-slate-800/40 border border-gray-200 dark:border-slate-700 rounded-lg">
                                                    <div class="w-1/2 flex items-center">
                                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Role:
                                                        </span>
                                                        <span
                                                            class="text-sm text-gray-900 dark:text-gray-300 ml-2 font-semibold">{{ $roAssignee['role'] }}</span>
                                                    </div>
                                                    <div class="w-1/2 flex items-center">
                                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Name:
                                                        </span>
                                                        <span
                                                            class="text-sm text-gray-900 dark:text-gray-300 ml-2 font-semibold">{{ $roAssignee['name'] }}</span>
                                                    </div>
                                                    <span class="text-xs text-gray-400 ml-auto italic whitespace-nowrap"
                                                        title="You do not have permission to modify higher-level roles.">Read-only</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <template x-for="(assignee, index) in assignees" :key="index">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 relative"
                                            :style="{{ $canManageAssignments ? 'true' : 'false' }} ? 'padding-right: 56px;' : ''">
                                            <div>
                                                <label
                                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4"
                                                    x-show="index === 0">Role</label>
                                                <div class="relative">
                                                    <select x-model="assignee.role" @change="assignee.user_ids = []"
                                                        class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-xl focus:ring-indigo-500 focus:border-indigo-500 block px-5 py-4 dark:bg-slate-900 dark:border-slate-600 dark:text-white transition-colors appearance-none {{ !$canManageAssignments ? 'opacity-75 cursor-not-allowed bg-gray-100 dark:bg-slate-800/40' : '' }}"
                                                        :class="assignee.role === '' ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white'"
                                                        {{ !$canManageAssignments ? 'disabled' : '' }}>
                                                        <option value="" class="text-gray-500 dark:text-gray-400">Select
                                                            Role...</option>
                                                        @foreach($roles as $role)
                                                            <option value="{{ strtolower($role->name) }}"
                                                                class="text-gray-900 dark:text-white">{{ $role->display_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div x-data="{ open: false, search: '' }"
                                                @click.away="open = false; search = ''" class="relative">
                                                <label
                                                    class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4"
                                                    x-show="index === 0">Employee</label>

                                                <!-- Hidden inputs for form submission -->
                                                <template x-for="id in assignee.user_ids" :key="id">
                                                    <input type="hidden" name="assignee_ids[]" :value="id">
                                                </template>
                                                <!-- Custom Dropdown Trigger -->
                                                <div @click="if({{ $canManageAssignments ? 'true' : 'false' }} && assignee.role) open = !open"
                                                    class="w-full relative bg-gray-50 border border-gray-300 text-gray-900 text-base rounded-xl focus:border-indigo-500 focus:ring-indigo-500 block py-4 cursor-pointer transition-colors dark:bg-slate-900 dark:border-slate-600 dark:text-white"
                                                    style="padding-left: 1.25rem; padding-right: 2.5rem;"
                                                    :class="!assignee.role ? 'opacity-60 cursor-not-allowed' : (assignee.user_ids.length === 0 ? 'text-gray-500 dark:text-gray-400' : 'text-gray-900 dark:text-white') + ({{ $canManageAssignments ? 'false' : 'true' }} ? ' cursor-not-allowed opacity-75' : '')"
                                                    tabindex="0">

                                                    <span class="block truncate"
                                                        x-text="!assignee.role ? 'Select Role First...' : (assignee.user_ids.length === 0 ? 'Select Employees...' : assignee.user_ids.map(id => getFilteredUsers(assignee.role).find(u => u.id == id)?.name).filter(Boolean).join(', '))"></span>

                                                    <span
                                                        class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
                                                        :class="!assignee.role && 'opacity-50'">
                                                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20"
                                                            fill="currentColor">
                                                            <path fill-rule="evenodd"
                                                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </span>
                                                </div>

                                                <!-- Custom Dropdown Menu -->
                                                <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                                    x-transition:enter-start="transform opacity-0 scale-95"
                                                    x-transition:enter-end="transform opacity-100 scale-100"
                                                    x-transition:leave="transition ease-in duration-75"
                                                    x-transition:leave-start="transform opacity-100 scale-100"
                                                    x-transition:leave-end="transform opacity-0 scale-95"
                                                    class="z-50 mt-1 w-full bg-white dark:bg-slate-700 shadow-xl rounded-md text-base border border-gray-200 dark:border-slate-600 overflow-hidden sm:text-sm"
                                                    style="display: none;">

                                                    <!-- Sticky Search Bar -->
                                                    <div
                                                        class="p-2 border-b border-gray-100 dark:border-slate-600 bg-gray-50 dark:bg-slate-700/50">
                                                        <div class="relative">
                                                            <div
                                                                class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                                                                <svg class="h-4 w-4 text-gray-400" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                                                </svg>
                                                            </div>
                                                            <input type="text" x-model="search" @click.stop
                                                                class="w-full border border-gray-300 dark:border-slate-500 bg-white dark:bg-slate-800 text-gray-900 dark:text-white rounded-md shadow-sm sm:text-sm pl-9 pr-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 outline-none"
                                                                placeholder="Search team members...">
                                                        </div>
                                                    </div>

                                                    <!-- Scrollable List -->
                                                    <div class="overflow-y-auto"
                                                        style="max-height: 180px; scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent;">
                                                        <style>
                                                            /* Custom scrollbar styling for WebKit browsers */
                                                            .overflow-y-auto::-webkit-scrollbar {
                                                                width: 6px;
                                                            }

                                                            .overflow-y-auto::-webkit-scrollbar-track {
                                                                background: transparent;
                                                            }

                                                            .overflow-y-auto::-webkit-scrollbar-thumb {
                                                                background-color: #cbd5e1;
                                                                border-radius: 20px;
                                                            }

                                                            .dark .overflow-y-auto::-webkit-scrollbar-thumb {
                                                                background-color: #475569;
                                                            }
                                                        </style>
                                                        <template
                                                            x-for="user in getFilteredUsers(assignee.role).filter(u => u.name.toLowerCase().includes(search.toLowerCase()))"
                                                            :key="user.id">
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
                                                                        <svg x-show="assignee.user_ids.includes(user.id)"
                                                                            class="h-3 w-3 text-white" fill="none"
                                                                            viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round" stroke-width="3"
                                                                                d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                    </div>

                                                                    <!-- Employee Name -->
                                                                    <span class="block break-words"
                                                                        :class="assignee.user_ids.includes(user.id) ? 'font-medium' : 'font-normal'"
                                                                        x-text="user.name"></span>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <div x-show="getFilteredUsers(assignee.role).filter(u => u.name.toLowerCase().includes(search.toLowerCase())).length === 0"
                                                            class="py-3 px-4 text-gray-500 dark:text-gray-400 text-center">
                                                            No team members found.
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($canManageAssignments)
                                                <div class="absolute right-0 flex items-center justify-center"
                                                    :style="index === 0 ? 'top: 30px;' : 'top: 4px;'">
                                                    <button type="button" @click="removeAssignee(index)"
                                                        x-show="assignees.length > 1"
                                                        class="p-2.5 text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-lg transition-colors border border-transparent hover:border-red-200 dark:hover:border-red-900"
                                                        title="Remove Role Group">
                                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                            </path>
                                                        </svg>
                                                    </button>
                                                    <div class="w-[44px]" x-show="assignees.length <= 1"></div>
                                                </div>
                                            @endif
                                        </div>
                                    </template>
                                </div>
                            @endif

                        </div>

                        <div class="flex items-center justify-end gap-4 pt-8" style="margin-top: 2rem;">
                            <a href="{{ route('crm-projects.show', $project->id) }}"
                                class="inline-flex justify-center rounded-lg border border-gray-300 dark:border-slate-600 shadow-sm px-6 py-2.5 bg-white dark:bg-slate-800 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none transition-colors">
                                Cancel
                            </a>
                            <button type="submit"
                                class="inline-flex justify-center rounded-lg border border-transparent shadow-sm px-6 py-2.5 bg-indigo-600 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none transition-colors">
                                {{ $details ? 'Update Details' : 'Save Details' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:col-span-1">
                <div class="sticky top-6 space-y-6">
                    <div id="changes-section"
                        class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6"
                        x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between mb-4 focus:outline-none group">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Changes</h3>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300 transition-transform duration-200"
                                    :class="{'rotate-180': !open}" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300">
                                Activity
                            </span>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms>
                            <!-- Add Change Form -->
                            <form id="changes-form" action="{{ route('crm-projects.activities.store', $project->id) }}"
                                method="POST" enctype="multipart/form-data"
                                class="mb-6 pb-6 border-b border-gray-100 dark:border-slate-700"
                                style="padding-bottom: 1.5rem;">
                                @csrf
                                <div class="mb-3" x-data="{ fileName: '' }">
                                    <div
                                        class="relative flex items-center bg-slate-900/50 dark:bg-slate-900/70 border border-slate-700/80 focus-within:border-indigo-500 rounded-lg overflow-hidden transition-colors">
                                        <input type="file" name="attachment" x-ref="changeAttachment"
                                            @change="fileName = $event.target.files[0]?.name || ''" class="hidden" {{ $isCompleted ? 'disabled' : '' }}>
                                        <button type="button"
                                            @click="{{ $isCompleted ? 'alert(\'The project is completed. Please reopen to attach files.\')' : '$refs.changeAttachment.click()' }}"
                                            class="ml-3 shrink-0 text-slate-400 hover:text-white transition-colors"
                                            :class="{'text-indigo-400': fileName}" title="Attach a file">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                        </button>
                                        <textarea name="change_description" id="change-description-input"
                                            autocomplete="off" rows="1"
                                            @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
                                            style="background-color: transparent !important; color: #e2e8f0 !important; border: 0 !important; box-shadow: none !important; resize: none; max-height: 140px; min-height: 44px; outline: none !important;"
                                            class="w-full min-w-0 bg-transparent border-0 text-slate-200 placeholder-slate-400 focus:ring-0 text-sm py-3 px-3 focus:outline-none custom-scrollbar {{ $isCompleted ? 'cursor-not-allowed opacity-75' : '' }}"
                                            placeholder="What changed?" :required="!fileName" {{ $isCompleted ? 'disabled' : '' }}>{{ old('change_description') }}</textarea>
                                    </div>

                                    <!-- Attachment file indicator pill -->
                                    <template x-if="fileName">
                                        <div
                                            class="mt-1.5 flex items-center justify-between gap-2 px-2.5 py-1 bg-indigo-900/40 border border-indigo-700/50 rounded-md text-xs text-indigo-300">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg>
                                                <span class="truncate" x-text="fileName"></span>
                                            </div>
                                            <button type="button"
                                                @click="$refs.changeAttachment.value = ''; fileName = ''"
                                                class="text-indigo-400 hover:text-white font-bold ml-2">&times;</button>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="relative w-1/2">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" name="time_estimate"
                                            class="block w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors {{ $isCompleted ? 'opacity-75 cursor-not-allowed bg-gray-100 dark:bg-slate-800/40' : '' }}"
                                            style="padding-left: 2.25rem; padding-right: 3rem;" placeholder="e.g. 2" {{ $isCompleted ? 'disabled' : '' }}>
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span
                                                class="text-xs text-gray-400 dark:text-gray-400 font-medium">hrs</span>
                                        </div>
                                    </div>
                                    @if($isCompleted)
                                        <button type="button"
                                            onclick="alert('The project is completed. Please reopen the project to add changes.')"
                                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Change
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Change
                                        </button>
                                    @endif
                                </div>
                            </form>

                            <!-- Changes List -->
                            @if($project->activities->count() > 0)
                                <style>
                                    .custom-scrollbar::-webkit-scrollbar {
                                        width: 6px;
                                    }

                                    .custom-scrollbar::-webkit-scrollbar-track {
                                        background: transparent;
                                    }

                                    .custom-scrollbar::-webkit-scrollbar-thumb {
                                        background-color: #6366f1;
                                        border-radius: 10px;
                                    }

                                    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                                        background-color: #4f46e5;
                                    }
                                </style>
                                <div class="space-y-4 mt-4 overflow-y-auto pr-2 custom-scrollbar"
                                    style="max-height: 260px; scrollbar-width: thin; scrollbar-color: #6366f1 transparent;">
                                    @foreach($project->activities as $activity)
                                        <div
                                            class="py-3 border-b border-gray-100 dark:border-slate-700/60 last:border-0 text-left">
                                            <p class="text-sm text-gray-900 dark:text-white mb-1">{{ $activity->description }}
                                            </p>
                                            @if($activity->attachment_path)
                                                <div class="mb-2">
                                                    <a href="{{ route('api.activities.attachment.show', $activity->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800/40">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                            </path>
                                                        </svg>
                                                        <span
                                                            class="truncate max-w-[200px]">{{ $activity->attachment_name ?? 'Attachment' }}</span>
                                                    </a>
                                                </div>
                                            @endif
                                            @if($activity->time_estimate)
                                                <div
                                                    class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2 bg-indigo-50 dark:bg-indigo-900/30 w-max px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ trim(preg_replace('/(hrs?|hours?)$/i', '', $activity->time_estimate)) }} hrs
                                                </div>
                                            @else
                                                <div class="mb-2"></div>
                                            @endif
                                            <div class="flex justify-between items-center">
                                                <div class="flex-1 min-w-0"
                                                    x-data="{ date: new Date('{{ $activity->created_at->toISOString() }}') }">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400"
                                                        x-text="date.toLocaleString(undefined, { month: 'numeric', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true })">{{ $activity->created_at->format('n/j/Y, g:i:s A') }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($activity->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($activity->user)->name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center w-full min-h-[11rem] px-6 py-8 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 dark:bg-slate-900 dark:border-slate-600 transition-colors mt-2">
                                    <svg class="w-8 h-8 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium text-center">No recent
                                        changes recorded.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Enhancements Box -->
                    <div id="enhancements-section"
                        class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6"
                        x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between mb-4 focus:outline-none group">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Enhancements</h3>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300 transition-transform duration-200"
                                    :class="{'rotate-180': !open}" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300">
                                Activity
                            </span>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms>
                            <!-- Add Enhancement Form -->
                            <form id="enhancements-form"
                                action="{{ route('crm-projects.enhancements.store', $project->id) }}" method="POST"
                                enctype="multipart/form-data"
                                class="mb-6 pb-6 border-b border-gray-100 dark:border-slate-700"
                                style="padding-bottom: 1.5rem;">
                                @csrf
                                <div class="mb-3" x-data="{ fileName: '' }">
                                    <div
                                        class="relative flex items-center bg-slate-900/50 dark:bg-slate-900/70 border border-slate-700/80 focus-within:border-indigo-500 rounded-lg overflow-hidden transition-colors">
                                        <input type="file" name="attachment" x-ref="enhancementAttachment"
                                            @change="fileName = $event.target.files[0]?.name || ''" class="hidden" {{ $isCompleted ? 'disabled' : '' }}>
                                        <button type="button"
                                            @click="{{ $isCompleted ? 'alert(\'The project is completed. Please reopen to attach files.\')' : '$refs.enhancementAttachment.click()' }}"
                                            class="ml-3 shrink-0 text-slate-400 hover:text-white transition-colors"
                                            :class="{'text-indigo-400': fileName}" title="Attach a file">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                        </button>
                                        <textarea name="enhancement_description" id="enhancement-description-input"
                                            autocomplete="off" rows="1"
                                            @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
                                            style="background-color: transparent !important; color: #e2e8f0 !important; border: 0 !important; box-shadow: none !important; resize: none; max-height: 140px; min-height: 44px; outline: none !important;"
                                            class="w-full min-w-0 bg-transparent border-0 text-slate-200 placeholder-slate-400 focus:ring-0 text-sm py-3 px-3 focus:outline-none custom-scrollbar {{ $isCompleted ? 'cursor-not-allowed opacity-75' : '' }}"
                                            placeholder="What enhancement?" :required="!fileName" {{ $isCompleted ? 'disabled' : '' }}>{{ old('enhancement_description') }}</textarea>
                                    </div>

                                    <!-- Attachment file indicator pill -->
                                    <template x-if="fileName">
                                        <div
                                            class="mt-1.5 flex items-center justify-between gap-2 px-2.5 py-1 bg-indigo-900/40 border border-indigo-700/50 rounded-md text-xs text-indigo-300">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg>
                                                <span class="truncate" x-text="fileName"></span>
                                            </div>
                                            <button type="button"
                                                @click="$refs.enhancementAttachment.value = ''; fileName = ''"
                                                class="text-indigo-400 hover:text-white font-bold ml-2">&times;</button>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div class="relative w-1/2">
                                        <div
                                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" name="time_estimate"
                                            class="block w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors {{ $isCompleted ? 'opacity-75 cursor-not-allowed bg-gray-100 dark:bg-slate-800/40' : '' }}"
                                            style="padding-left: 2.25rem; padding-right: 3rem;" placeholder="e.g. 2" {{ $isCompleted ? 'disabled' : '' }}>
                                        <div
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <span
                                                class="text-xs text-gray-400 dark:text-gray-400 font-medium">hrs</span>
                                        </div>
                                    </div>
                                    @if($isCompleted)
                                        <button type="button"
                                            onclick="alert('The project is completed. Please reopen the project to add enhancements.')"
                                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Enhancement
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add Enhancement
                                        </button>
                                    @endif
                                </div>
                            </form>

                            <!-- Enhancements List -->
                            @if($project->enhancements && $project->enhancements->count() > 0)
                                <div class="space-y-4 mt-4 overflow-y-auto pr-2 custom-scrollbar"
                                    style="max-height: 260px; scrollbar-width: thin; scrollbar-color: #6366f1 transparent;">
                                    @foreach($project->enhancements as $enhancement)
                                        <div
                                            class="py-3 border-b border-gray-100 dark:border-slate-700/60 last:border-0 text-left">
                                            <p class="text-sm text-gray-900 dark:text-white mb-1">
                                                {{ $enhancement->description }}
                                            </p>
                                            @if($enhancement->time_estimate)
                                                <div
                                                    class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2 bg-indigo-50 dark:bg-indigo-900/30 w-max px-2 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                    {{ trim(preg_replace('/(hrs?|hours?)$/i', '', $enhancement->time_estimate)) }}
                                                    hrs
                                                </div>
                                            @else
                                                <div class="mb-2"></div>
                                            @endif
                                            <div class="flex justify-between items-center">
                                                <div class="flex-1 min-w-0"
                                                    x-data="{ date: new Date('{{ $enhancement->created_at->toISOString() }}') }">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400"
                                                        x-text="date.toLocaleString(undefined, { month: 'numeric', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true })">{{ $enhancement->created_at->format('n/j/Y, g:i:s A') }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($enhancement->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($enhancement->user)->name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div
                                    class="flex flex-col items-center justify-center w-full min-h-[11rem] px-6 py-8 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 dark:bg-slate-900 dark:border-slate-600 transition-colors mt-2">
                                    <svg class="w-8 h-8 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium text-center">No recent
                                        enhancements recorded.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- To-Do List Box -->
                    <div id="todos-section"
                        class="bg-white dark:bg-slate-800 rounded-2xl shadow-sm border border-gray-200 dark:border-slate-700 p-6"
                        x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                            class="w-full flex items-center justify-between mb-4 focus:outline-none group">
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">To-Do List</h3>
                                <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300 transition-transform duration-200"
                                    :class="{'rotate-180': !open}" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-gray-300">
                                Activity
                            </span>
                        </button>

                        <div x-show="open" x-transition.opacity.duration.200ms>
                            <!-- Add To-Do Form -->
                            <form id="todos-form" action="{{ route('crm-projects.todos.store', $project->id) }}"
                                method="POST" class="mb-6">
                                @csrf
                                <div class="mb-3">
                                    <div
                                        class="relative flex items-center bg-slate-900/50 dark:bg-slate-900/70 border border-slate-700/80 focus-within:border-indigo-500 rounded-lg overflow-hidden transition-colors">
                                        <textarea name="description" id="todo-description-input" autocomplete="off"
                                            rows="1"
                                            @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 140) + 'px'"
                                            style="background-color: transparent !important; color: #e2e8f0 !important; border: 0 !important; box-shadow: none !important; resize: none; max-height: 140px; min-height: 44px; outline: none !important;"
                                            class="w-full min-w-0 bg-transparent border-0 text-slate-200 placeholder-slate-400 focus:ring-0 text-sm py-3 px-3 focus:outline-none custom-scrollbar {{ $isCompleted ? 'cursor-not-allowed opacity-75' : '' }}"
                                            placeholder="What needs to be done?" required {{ $isCompleted ? 'disabled' : '' }}>{{ old('description') }}</textarea>
                                    </div>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2 flex-1">
                                        <select name="duration_type"
                                            class="block rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors {{ $isCompleted ? 'opacity-75 cursor-not-allowed' : '' }}"
                                            {{ $isCompleted ? 'disabled' : '' }}>
                                            <option value="days" {{ old('duration_type') === 'days' ? 'selected' : '' }}>
                                                Days</option>
                                            <option value="weeks" {{ old('duration_type') === 'weeks' ? 'selected' : '' }}>Weeks</option>
                                            <option value="months" {{ old('duration_type') === 'months' ? 'selected' : '' }}>Months</option>
                                        </select>
                                        <input type="hidden" name="duration_value" value="1">
                                    </div>
                                    @if($isCompleted)
                                        <button type="button"
                                            onclick="alert('The project is completed. Please reopen the project to add to-dos.')"
                                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add To-Do
                                        </button>
                                    @else
                                        <button type="submit"
                                            class="inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-sm font-bold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            Add To-Do
                                        </button>
                                    @endif
                                </div>
                            </form>

                            <!-- To-Do List -->
                            @if($project->todos && $project->todos->count() > 0)
                                <div class="space-y-4 mt-4 overflow-y-auto pr-2 custom-scrollbar"
                                    style="max-height: 260px; scrollbar-width: thin; scrollbar-color: #6366f1 transparent;">
                                    @foreach($project->todos as $todo)
                                        <div
                                            class="py-3 border-b border-gray-100 dark:border-slate-700/60 last:border-0 text-left">
                                            <p class="text-sm text-gray-900 dark:text-white mb-1">
                                                {{ $todo->description }}
                                            </p>
                                            <div
                                                class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2 bg-indigo-50 dark:bg-indigo-900/30 w-max px-2 py-0.5 rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $todo->duration_value }} {{ ucfirst($todo->duration_type) }}
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <div class="flex-1 min-w-0"
                                                    x-data="{ date: new Date('{{ $todo->created_at->toISOString() }}') }">
                                                    <span class="text-xs text-gray-500 dark:text-gray-400"
                                                        x-text="date.toLocaleString(undefined, { month: 'numeric', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true })">{{ $todo->created_at->format('n/j/Y, g:i:s A') }}</span>
                                                </div>
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
                                    class="flex flex-col items-center justify-center w-full min-h-[11rem] px-6 py-8 border-2 border-gray-300 border-dashed rounded-xl bg-gray-50 dark:bg-slate-900 dark:border-slate-600 transition-colors mt-2">
                                    <svg class="w-8 h-8 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                                        </path>
                                    </svg>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 font-medium text-center">No
                                        to-do items added yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const detailsForm = document.getElementById('details-form');
                if (detailsForm) {
                    detailsForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const btn = e.submitter || detailsForm.querySelector('button[type="submit"]');
                        const originalText = btn.innerHTML;
                        btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
                        btn.disabled = true;

                        // Include the button's value if it has one (e.g., complete_project)
                        const formData = new FormData(detailsForm);
                        if (e.submitter && e.submitter.name) {
                            formData.append(e.submitter.name, e.submitter.value);
                        }
                        
                        fetch(detailsForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                window.dispatchEvent(new CustomEvent('notify', {
                                    detail: {
                                        message: data.message || 'Project details updated successfully!',
                                        type: 'success'
                                    }
                                }));
                                
                                if (data.redirect) {
                                    setTimeout(() => window.location.href = data.redirect, 500);
                                } else if (e.submitter && (e.submitter.name === 'reopen_project' || e.submitter.name === 'complete_project')) {
                                    setTimeout(() => window.location.reload(), 500);
                                }
                            } else {
                                window.dispatchEvent(new CustomEvent('notify', {
                                    detail: {
                                        message: data.message || 'Error updating details',
                                        type: 'error'
                                    }
                                }));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            window.dispatchEvent(new CustomEvent('notify', {
                                detail: {
                                    message: 'An unexpected error occurred.',
                                    type: 'error'
                                }
                            }));
                        })
                        .finally(() => {
                            btn.innerHTML = originalText;
                            btn.disabled = false;
                        });
                    });
                }

                const changesForm = document.getElementById('changes-form');
                const enhancementsForm = document.getElementById('enhancements-form');

                if (changesForm) {
                    changesForm.addEventListener('submit', function () {
                        sessionStorage.setItem('scrollToSection', 'changes-section');
                    });
                }
                if (enhancementsForm) {
                    enhancementsForm.addEventListener('submit', function () {
                        sessionStorage.setItem('scrollToSection', 'enhancements-section');
                    });
                }

                const todosForm = document.getElementById('todos-form');
                if (todosForm) {
                    todosForm.addEventListener('submit', function () {
                        sessionStorage.setItem('scrollToSection', 'todos-section');
                    });
                }

                const target = sessionStorage.getItem('scrollToSection');
                if (target) {
                    sessionStorage.removeItem('scrollToSection');
                    const el = document.getElementById(target);
                    if (el) {
                        setTimeout(() => el.scrollIntoView({ behavior: 'auto', block: 'start' }), 50);
                    }
                }
            });
        </script>

</x-app-layout>