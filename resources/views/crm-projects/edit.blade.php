<x-app-layout>
    <!-- Jodit Editor Stylesheets and Scripts -->
    <link class="jodit-assets" rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/jodit/3.24.2/jodit.min.css" />
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

        /* Sidebar compact Jodit styling */
        .sidebar-jodit .jodit-container {
            border-radius: 0.75rem !important;
            min-height: 140px !important;
        }

        .sidebar-jodit .jodit-toolbar__box {
            padding: 2px 4px !important;
        }

        .sidebar-jodit .jodit-toolbar-button__button {
            padding: 2px 4px !important;
            min-width: 24px !important;
            height: 24px !important;
        }

        .sidebar-jodit .jodit-workplace {
            min-height: 100px !important;
        }

        .sidebar-jodit .jodit-wysiwyg {
            padding: 10px 12px !important;
            font-size: 13px !important;
            min-height: 100px !important;
            line-height: 1.5 !important;
        }

        .sidebar-jodit .jodit-placeholder {
            padding: 10px 12px !important;
            font-size: 13px !important;
        }

        .sidebar-jodit .jodit-status-bar {
            display: none !important;
        }
    </style>
    <div x-data="projectSidebarManager()">
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

                    <form id="details-form" action="{{ route('crm-projects.details.store', $project->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div>
                            @php
                                $user = auth()->user();
                                $canEditDetails = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
                                $canManageDates = $user->isAdmin() || $user->isManager() || $user->hasRole('project-manager') || $user->hasRole('business-analytics');
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
                                    @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager') || auth()->user()->hasRole('business-analytics'))
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
                                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-5"
                                            style="margin-bottom: 1.25rem; display: block;">Description</label>

                                        <!-- Jodit uses a standard textarea and binds to it directly -->
                                        <div style="margin-top: 1rem;"
                                            class="bg-gray-50 dark:bg-slate-900 rounded-xl overflow-hidden border border-gray-200 dark:border-slate-600 focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500 transition-shadow">
                                            <textarea name="description" id="description-editor" autocomplete="off"
                                                class="w-full">{{ old('description', $details->description ?? '') }}</textarea>
                                        </div>
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

                                <!-- Project Files & Attachments Section -->
                                <div class="mt-8 mb-8 pt-8 border-t border-gray-200 dark:border-slate-700"
                                    x-data="{
                                        selectedFiles: [],
                                        isDragging: false,
                                        isUploadingNow: false,
                                        syncInput() {
                                            try {
                                                const dt = new DataTransfer();
                                                this.selectedFiles.forEach(f => dt.items.add(f));
                                                const input = document.getElementById('project-attachments-input');
                                                if (input) {
                                                    input.files = dt.files;
                                                }
                                            } catch (e) {
                                                console.warn('DataTransfer sync:', e);
                                            }
                                            window.__projectSelectedFiles = this.selectedFiles;
                                        },
                                        handleFiles(event) {
                                            const incoming = Array.from(
                                                (event.target && event.target.files) || 
                                                (event.dataTransfer && event.dataTransfer.files) || 
                                                []
                                            );
                                            incoming.forEach(file => {
                                                if (!this.selectedFiles.some(f => f.name === file.name && f.size === file.size)) {
                                                    this.selectedFiles.push(file);
                                                }
                                            });
                                            this.syncInput();
                                        },
                                        removeFile(index) {
                                            this.selectedFiles.splice(index, 1);
                                            this.syncInput();
                                        },
                                        formatSize(bytes) {
                                            if (!bytes) return '0 B';
                                            const k = 1024;
                                            const sizes = ['B', 'KB', 'MB', 'GB'];
                                            const i = Math.floor(Math.log(bytes) / Math.log(k));
                                            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
                                        },
                                        async uploadSelectedNow() {
                                            if (this.selectedFiles.length === 0) return;
                                            this.isUploadingNow = true;
                                            const formData = new FormData();
                                            this.selectedFiles.forEach(file => {
                                                formData.append('attachments[]', file);
                                            });
                                            formData.append('_token', '{{ csrf_token() }}');

                                            try {
                                                const res = await fetch('{{ route('crm-projects.attachments.store', $project->id) }}', {
                                                    method: 'POST',
                                                    body: formData,
                                                    headers: {
                                                        'Accept': 'application/json',
                                                        'X-Requested-With': 'XMLHttpRequest'
                                                    }
                                                });
                                                const data = await res.json();
                                                if (data.success) {
                                                    window.dispatchEvent(new CustomEvent('notify', {
                                                        detail: {
                                                            message: data.message || 'Files uploaded successfully to Google Drive!',
                                                            type: 'success'
                                                        }
                                                    }));
                                                    setTimeout(() => window.location.reload(), 500);
                                                } else {
                                                    alert(data.message || 'Error uploading files.');
                                                    this.isUploadingNow = false;
                                                }
                                            } catch (err) {
                                                alert('Network error while uploading files.');
                                                this.isUploadingNow = false;
                                            }
                                        },
                                        async deleteExisting(id) {
                                            if (!confirm('Are you sure you want to delete this file from Google Drive and this project?')) return;
                                            try {
                                                const deleteUrl = '{{ route('crm-projects.attachments.destroy', '__ID__') }}'.replace('__ID__', id);
                                                const res = await fetch(deleteUrl, {
                                                    method: 'DELETE',
                                                    headers: {
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json'
                                                    }
                                                });
                                                const data = await res.json();
                                                if (data.success) {
                                                    const el = document.getElementById('attachment-card-' + id);
                                                    if (el) el.remove();
                                                    const countEl = document.getElementById('existing-attachments-count');
                                                    if (countEl) {
                                                        const cur = parseInt(countEl.textContent) || 1;
                                                        countEl.textContent = Math.max(0, cur - 1);
                                                    }
                                                } else {
                                                    alert(data.message || 'Error deleting file.');
                                                }
                                            } catch (e) {
                                                alert('Network error while deleting file.');
                                            }
                                        }
                                    }">
                                    
                                <style>
                                    .project-file-card {
                                        background-color: #ffffff;
                                        border: 1px solid #e2e8f0;
                                        transition: all 0.2s ease-in-out;
                                    }
                                    .dark .project-file-card {
                                        background-color: #0f172a !important;
                                        border: 1px solid #334155 !important;
                                    }
                                    .project-file-card:hover {
                                        border-color: #6366f1 !important;
                                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
                                        transform: translateY(-2px);
                                    }
                                    .dark .project-file-card:hover {
                                        border-color: #818cf8 !important;
                                        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5), 0 0 15px -3px rgba(99, 102, 241, 0.25);
                                    }
                                    .file-card-title {
                                        color: #0f172a;
                                    }
                                    .dark .file-card-title {
                                        color: #f1f5f9 !important;
                                    }
                                    .file-card-title:hover {
                                        color: #4f46e5 !important;
                                    }
                                    .dark .file-card-title:hover {
                                        color: #a5b4fc !important;
                                    }
                                    .file-card-meta {
                                        color: #64748b;
                                    }
                                    .dark .file-card-meta {
                                        color: #94a3b8 !important;
                                    }
                                    .dark .action-icon-btn {
                                        color: #94a3b8 !important;
                                    }
                                    .dark .action-icon-btn:hover {
                                        background-color: #1e293b !important;
                                    }
                                    .dark .action-btn-view:hover {
                                        color: #818cf8 !important;
                                    }
                                    .dark .action-btn-download:hover {
                                        color: #34d399 !important;
                                    }
                                    .dark .action-btn-delete:hover {
                                        color: #f87171 !important;
                                        background-color: rgba(239, 68, 68, 0.18) !important;
                                    }
                                    .drive-status-badge {
                                        background-color: rgba(16, 185, 129, 0.08);
                                        border: 1px solid rgba(16, 185, 129, 0.25);
                                        color: #059669;
                                    }
                                    .dark .drive-status-badge {
                                        background-color: rgba(16, 185, 129, 0.15) !important;
                                        border: 1px solid rgba(16, 185, 129, 0.35) !important;
                                        color: #34d399 !important;
                                    }
                                    .upload-dropzone-box {
                                        background-color: #f8fafc;
                                        border: 2px dashed #cbd5e1;
                                        transition: all 0.2s ease-in-out;
                                    }
                                    .dark .upload-dropzone-box {
                                        background-color: rgba(15, 23, 42, 0.55) !important;
                                        border: 2px dashed #334155 !important;
                                    }
                                    .upload-dropzone-box:hover, .upload-dropzone-box.drag-active {
                                        border-color: #6366f1 !important;
                                        background-color: #eef2ff !important;
                                    }
                                    .dark .upload-dropzone-box:hover, .dark .upload-dropzone-box.drag-active {
                                        border-color: #818cf8 !important;
                                        background-color: rgba(99, 102, 241, 0.08) !important;
                                    }
                                    .dark .dropzone-title {
                                        color: #f8fafc !important;
                                    }
                                    .dark .dropzone-desc {
                                        color: #94a3b8 !important;
                                    }
                                    .file-type-badge {
                                        font-size: 10px;
                                        font-weight: 700;
                                        letter-spacing: 0.05em;
                                        padding: 2px 6px;
                                        border-radius: 6px;
                                        text-transform: uppercase;
                                    }
                                </style>

                                @php
                                    $getFileMeta = function ($fileName) {
                                        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
                                            return [
                                                'type' => 'IMAGE',
                                                'color' => 'purple',
                                                'icon_bg' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/25',
                                                'badge_bg' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300'
                                            ];
                                        } elseif ($ext === 'pdf') {
                                            return [
                                                'type' => 'PDF',
                                                'color' => 'rose',
                                                'icon_bg' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/25',
                                                'badge_bg' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300'
                                            ];
                                        } elseif (in_array($ext, ['xls', 'xlsx', 'csv'])) {
                                            return [
                                                'type' => 'SHEET',
                                                'color' => 'emerald',
                                                'icon_bg' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/25',
                                                'badge_bg' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300'
                                            ];
                                        } elseif (in_array($ext, ['doc', 'docx', 'txt', 'rtf'])) {
                                            return [
                                                'type' => 'DOC',
                                                'color' => 'blue',
                                                'icon_bg' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/25',
                                                'badge_bg' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300'
                                            ];
                                        } elseif (in_array($ext, ['zip', 'rar', 'tar', '7z', 'gz'])) {
                                            return [
                                                'type' => 'ZIP',
                                                'color' => 'amber',
                                                'icon_bg' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/25',
                                                'badge_bg' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300'
                                            ];
                                        }
                                        return [
                                            'type' => strtoupper($ext ?: 'FILE'),
                                            'color' => 'indigo',
                                            'icon_bg' => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/25',
                                            'badge_bg' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300'
                                        ];
                                    };
                                @endphp

                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center shadow-md shadow-indigo-500/20 shrink-0">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 dark:text-white tracking-tight">
                                                Project Files & Attachments
                                            </h3>
                                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                                Upload specifications, assets, or documents securely synced with Google Drive.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="drive-status-badge inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold self-start sm:self-auto shadow-sm">
                                        <span class="relative flex h-2 w-2">
                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                        </span>
                                        <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                        </svg>
                                        <span>Google Drive 5 TB Active</span>
                                    </div>
                                </div>

                                <!-- Existing Attachments List -->
                                @if($project->attachments && $project->attachments->count() > 0)
                                    <div class="mb-6">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2">
                                                <p class="text-xs font-bold text-gray-600 dark:text-slate-300 uppercase tracking-wider">
                                                    Existing Uploads
                                                </p>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/60">
                                                    <span id="existing-attachments-count">{{ $project->attachments->count() }}</span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5" id="existing-attachments-container">
                                            @foreach($project->attachments as $attachment)
                                                @php $meta = $getFileMeta($attachment->file_name); @endphp
                                                <div id="attachment-card-{{ $attachment->id }}" 
                                                    class="project-file-card rounded-2xl p-4 flex flex-col justify-between gap-3 group">
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="flex items-start gap-3 min-w-0 flex-1">
                                                            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-sm {{ $meta['icon_bg'] }}">
                                                                @if($meta['color'] === 'purple')
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                                    </svg>
                                                                @elseif($meta['color'] === 'rose')
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                                    </svg>
                                                                @elseif($meta['color'] === 'emerald')
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                    </svg>
                                                                @elseif($meta['color'] === 'amber')
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                                                    </svg>
                                                                @else
                                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                                    </svg>
                                                                @endif
                                                            </div>

                                                            <div class="min-w-0 flex-1">
                                                                <a href="{{ route('crm-projects.attachments.show', $attachment->id) }}" 
                                                                    target="_blank" 
                                                                    class="file-card-title text-sm font-bold truncate block transition-colors tracking-tight"
                                                                    title="{{ $attachment->file_name }}">
                                                                    {{ $attachment->file_name }}
                                                                </a>
                                                                <div class="file-card-meta flex items-center flex-wrap gap-2 text-xs mt-1">
                                                                    <span class="file-type-badge {{ $meta['badge_bg'] }}">
                                                                        {{ $meta['type'] }}
                                                                    </span>
                                                                    <span class="font-medium">{{ $attachment->formatted_size ?: 'File' }}</span>
                                                                    <span>&bull;</span>
                                                                    <span>{{ $attachment->created_at->format('M d, Y') }}</span>
                                                                    @if($attachment->user)
                                                                        <span>&bull;</span>
                                                                        <span class="truncate max-w-[120px] font-medium">{{ $attachment->user->name }}</span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="flex items-center gap-1 shrink-0">
                                                            <a href="{{ route('crm-projects.attachments.show', $attachment->id) }}" 
                                                                target="_blank" 
                                                                class="action-icon-btn action-btn-view p-2 text-gray-400 hover:text-indigo-600 rounded-lg transition-all" 
                                                                title="View in new tab">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                                </svg>
                                                            </a>
                                                            <a href="{{ route('crm-projects.attachments.download', $attachment->id) }}" 
                                                                class="action-icon-btn action-btn-download p-2 text-gray-400 hover:text-emerald-600 rounded-lg transition-all" 
                                                                title="Download file">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                                </svg>
                                                            </a>
                                                            @if($canEditDetails || $attachment->user_id === auth()->id())
                                                                <button type="button" 
                                                                    @click="deleteExisting({{ $attachment->id }})" 
                                                                    class="action-icon-btn action-btn-delete p-2 text-gray-400 hover:text-rose-600 rounded-lg transition-all" 
                                                                    title="Delete file">
                                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                                    </svg>
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <div class="flex items-center justify-between pt-2.5 border-t border-gray-100 dark:border-slate-800/80 text-[11px] text-gray-400 dark:text-slate-500">
                                                        <span class="inline-flex items-center gap-1.5 font-medium">
                                                            <svg class="w-3.5 h-3.5 text-indigo-500" viewBox="0 0 24 24" fill="currentColor">
                                                                <path d="M19.35 10.04C18.67 6.59 15.64 4 12 4 9.11 4 6.6 5.64 5.35 8.04 2.34 8.36 0 10.91 0 14c0 3.31 2.69 6 6 6h13c2.76 0 5-2.24 5-5 0-2.64-2.05-4.78-4.65-4.96zM14 13v4h-4v-4H7l5-5 5 5h-3z"/>
                                                            </svg>
                                                            <span>Google Drive</span>
                                                        </span>
                                                        <span class="text-emerald-600 dark:text-emerald-400 font-semibold inline-flex items-center gap-1">
                                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Synced
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Drag & Drop Upload Zone -->
                                <div class="upload-dropzone-box relative rounded-2xl p-7 text-center transition-all cursor-pointer"
                                    :class="isDragging ? 'drag-active' : ''"
                                    @dragover.prevent="isDragging = true"
                                    @dragleave.prevent="isDragging = false"
                                    @drop.prevent="isDragging = false; handleFiles($event)"
                                    @click="$refs.attachmentInput.click()">
                                    
                                    <input type="file" 
                                        name="attachments[]" 
                                        id="project-attachments-input" 
                                        x-ref="attachmentInput" 
                                        multiple 
                                        class="hidden" 
                                        @change="handleFiles($event)">

                                    <div class="flex flex-col items-center justify-center pointer-events-none">
                                        <div class="w-14 h-14 mb-3 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/15 border border-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shadow-inner">
                                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                            </svg>
                                        </div>
                                        <p class="dropzone-title text-sm font-semibold text-gray-900 dark:text-white">
                                            <span class="text-indigo-600 dark:text-indigo-400 hover:underline">Click to browse</span> or drag and drop files here
                                        </p>
                                        <p class="dropzone-desc text-xs text-gray-500 dark:text-slate-400 mt-1.5">
                                            Supported: PDF, DOCX, XLSX, Images, ZIP, code archives • Up to 50MB each
                                        </p>
                                    </div>
                                </div>

                                <!-- Selected Files Chips Preview -->
                                <div x-show="selectedFiles.length > 0" class="mt-4 space-y-3" style="display: none;">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <p class="text-xs font-bold text-gray-600 dark:text-slate-300 uppercase tracking-wider">
                                                Files ready to upload
                                            </p>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-700 dark:bg-indigo-950/80 dark:text-indigo-300 border border-indigo-200/50 dark:border-indigo-800/60" x-text="selectedFiles.length"></span>
                                        </div>
                                        <button type="button" 
                                            @click.stop="uploadSelectedNow()" 
                                            :disabled="isUploadingNow"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-indigo-500/20 disabled:opacity-50">
                                            <svg x-show="!isUploadingNow" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            <svg x-show="isUploadingNow" class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span x-text="isUploadingNow ? 'Uploading to Drive...' : 'Upload Now'"></span>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                        <template x-for="(file, idx) in selectedFiles" :key="idx">
                                            <div class="flex items-center justify-between p-3 rounded-xl border border-indigo-200/80 dark:border-indigo-800/80 bg-indigo-50/60 dark:bg-indigo-950/40 text-xs font-medium">
                                                <div class="flex items-center gap-2.5 min-w-0 flex-1 mr-2">
                                                    <div class="w-8 h-8 rounded-lg bg-indigo-100 dark:bg-indigo-900/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center shrink-0">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                        </svg>
                                                    </div>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="truncate font-semibold text-gray-900 dark:text-slate-200" x-text="file.name"></p>
                                                        <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-medium" x-text="formatSize(file.size)"></p>
                                                    </div>
                                                </div>
                                                <button type="button" 
                                                    @click.stop="removeFile(idx)" 
                                                    class="p-1.5 text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/40 rounded-lg transition-colors"
                                                    title="Remove file">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="text-[11px] text-gray-500 dark:text-slate-400 italic">Click "Upload Now" to upload immediately, or click "Save Details" below to save all changes together.</p>
                                </div>
                                </div>
                            @endif

                            @php
                                $user = auth()->user();
                                $hasRoleToManageAssignments = auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->hasRole('project-manager') || auth()->user()->hasRole('business-analytics') || auth()->user()->hasRole('team-lead');
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
                        x-data="{ open: sessionStorage.getItem('scrollToSection') === 'changes-section' }">
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
                                <div class="mb-3 space-y-2" x-data="{ fileName: '' }">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                            Description <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="sidebar-jodit {{ $isCompleted ? 'cursor-not-allowed opacity-75 pointer-events-none' : '' }}">
                                            <textarea name="change_description" id="change-description-editor"
                                                autocomplete="off">{{ old('change_description') }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Attachment Button & Hidden File Input -->
                                    <div class="flex items-center justify-between">
                                        <input type="file" name="attachment" x-ref="changeAttachment"
                                            @change="fileName = $event.target.files[0]?.name || ''" class="hidden" {{ $isCompleted ? 'disabled' : '' }}>
                                        <button type="button"
                                            @click="{{ $isCompleted ? 'alert(\'The project is completed. Please reopen to attach files.\')' : '$refs.changeAttachment.click()' }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 bg-gray-100 dark:bg-slate-700 hover:bg-indigo-50 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition-colors {{ $isCompleted ? 'opacity-60 cursor-not-allowed' : '' }}"
                                            title="Attach a file">
                                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                            <span x-text="fileName ? 'Change file' : 'Attach file'">Attach file</span>
                                        </button>
                                    </div>

                                    <!-- Attachment file indicator pill -->
                                    <template x-if="fileName">
                                        <div
                                            class="flex items-center justify-between gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs text-indigo-700 dark:text-indigo-300">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <svg class="w-3.5 h-3.5 shrink-0 text-indigo-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg>
                                                <span class="truncate font-medium" x-text="fileName"></span>
                                            </div>
                                            <button type="button"
                                                @click="$refs.changeAttachment.value = ''; fileName = ''"
                                                class="text-indigo-500 hover:text-red-500 font-bold ml-2 transition-colors">&times;</button>
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
                                            <div class="text-sm text-gray-900 dark:text-white mb-1 prose prose-sm dark:prose-invert max-w-none break-words">
                                                {!! $activity->description !!}
                                            </div>
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
                                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-100 dark:border-slate-700/60">
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($activity->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($activity->user)->name }}</span>
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">•</span>
                                                    <span class="text-[11px] text-gray-400 dark:text-gray-500"
                                                        x-data="{ date: new Date('{{ $activity->created_at->toISOString() }}') }"
                                                        x-text="date.toLocaleString(undefined, { month: 'numeric', day: 'numeric', hour: 'numeric', minute: '2-digit', hour12: true })">{{ $activity->created_at->format('n/j, g:i A') }}</span>
                                                </div>

                                                @php
                                                    $canEditThisActivity = !$isCompleted && ($canEditDetails || $user->hasRole('team-lead') || $activity->user_id === $user->id);
                                                    $canDeleteThisActivity = !$isCompleted && ($canEditDetails || $activity->user_id === $user->id);
                                                @endphp

                                                <div class="flex items-center gap-1">
                                                    @if($canEditThisActivity)
                                                        <button type="button"
                                                            @click="openEditActivity(@js([
                                                                'id' => $activity->id,
                                                                'description' => $activity->description,
                                                                'time_estimate' => trim(preg_replace('/(hrs?|hours?)$/i', '', $activity->time_estimate ?? '')),
                                                                'attachment_name' => $activity->attachment_name,
                                                                'attachment_url' => $activity->attachment_path ? route('api.activities.attachment.show', $activity->id) : null,
                                                            ]))"
                                                            class="p-1 text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 rounded-md transition-colors"
                                                            title="Edit Change">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                    @if($canDeleteThisActivity)
                                                        <button type="button"
                                                            @click="deleteActivity({{ $activity->id }})"
                                                            class="p-1 text-gray-400 hover:text-rose-600 dark:text-gray-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-colors"
                                                            title="Delete Change">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
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
                        x-data="{ open: sessionStorage.getItem('scrollToSection') === 'enhancements-section' }">
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
                                <div class="mb-3 space-y-2" x-data="{ fileName: '' }">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                            Description <span class="text-rose-500">*</span>
                                        </label>
                                        <div class="sidebar-jodit {{ $isCompleted ? 'cursor-not-allowed opacity-75 pointer-events-none' : '' }}">
                                            <textarea name="enhancement_description" id="enhancement-description-editor"
                                                autocomplete="off">{{ old('enhancement_description') }}</textarea>
                                        </div>
                                    </div>

                                    <!-- Attachment Button & Hidden File Input -->
                                    <div class="flex items-center justify-between">
                                        <input type="file" name="attachment" x-ref="enhancementAttachment"
                                            @change="fileName = $event.target.files[0]?.name || ''" class="hidden" {{ $isCompleted ? 'disabled' : '' }}>
                                        <button type="button"
                                            @click="{{ $isCompleted ? 'alert(\'The project is completed. Please reopen to attach files.\')' : '$refs.enhancementAttachment.click()' }}"
                                            class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 bg-gray-100 dark:bg-slate-700 hover:bg-indigo-50 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition-colors {{ $isCompleted ? 'opacity-60 cursor-not-allowed' : '' }}"
                                            title="Attach a file">
                                            <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                </path>
                                            </svg>
                                            <span x-text="fileName ? 'Change file' : 'Attach file'">Attach file</span>
                                        </button>
                                    </div>

                                    <!-- Attachment file indicator pill -->
                                    <template x-if="fileName">
                                        <div
                                            class="flex items-center justify-between gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs text-indigo-700 dark:text-indigo-300">
                                            <div class="flex items-center gap-1.5 truncate">
                                                <svg class="w-3.5 h-3.5 shrink-0 text-indigo-500" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                    </path>
                                                </svg>
                                                <span class="truncate font-medium text-indigo-700 dark:text-indigo-300" x-text="fileName"></span>
                                            </div>
                                            <button type="button"
                                                @click="$refs.enhancementAttachment.value = ''; fileName = ''"
                                                class="text-indigo-500 hover:text-red-500 font-bold ml-2 transition-colors">&times;</button>
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
                                            <div class="text-sm text-gray-900 dark:text-white mb-1 prose prose-sm dark:prose-invert max-w-none break-words">
                                                {!! $enhancement->description !!}
                                            </div>
                                            @if($enhancement->attachment_path)
                                                <div class="mb-2">
                                                    <a href="{{ route('api.enhancements.attachment.show', $enhancement->id) }}"
                                                        target="_blank"
                                                        class="inline-flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 hover:underline bg-indigo-50 dark:bg-indigo-900/30 px-2 py-1 rounded border border-indigo-100 dark:border-indigo-800/40">
                                                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13">
                                                            </path>
                                                        </svg>
                                                        <span
                                                            class="truncate max-w-[200px]">{{ $enhancement->attachment_name ?? 'Attachment' }}</span>
                                                    </a>
                                                </div>
                                            @endif
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
                                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-100 dark:border-slate-700/60">
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($enhancement->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($enhancement->user)->name }}</span>
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">•</span>
                                                    <span class="text-[11px] text-gray-400 dark:text-gray-500"
                                                        x-data="{ date: new Date('{{ $enhancement->created_at->toISOString() }}') }"
                                                        x-text="date.toLocaleString(undefined, { month: 'numeric', day: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true })">{{ $enhancement->created_at->format('n/j, g:i A') }}</span>
                                                </div>

                                                @php
                                                    $canEditThisEnhancement = !$isCompleted && ($canEditDetails || $user->hasRole('team-lead') || $enhancement->user_id === $user->id);
                                                    $canDeleteThisEnhancement = !$isCompleted && ($canEditDetails || $enhancement->user_id === $user->id);
                                                @endphp

                                                <div class="flex items-center gap-1">
                                                    @if($canEditThisEnhancement)
                                                        <button type="button"
                                                            @click="openEditEnhancement(@js([
                                                                'id' => $enhancement->id,
                                                                'description' => $enhancement->description,
                                                                'time_estimate' => trim(preg_replace('/(hrs?|hours?)$/i', '', $enhancement->time_estimate ?? '')),
                                                                'attachment_name' => $enhancement->attachment_name,
                                                                'attachment_url' => $enhancement->attachment_path ? route('api.enhancements.attachment.show', $enhancement->id) : null,
                                                            ]))"
                                                            class="p-1 text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 rounded-md transition-colors"
                                                            title="Edit Enhancement">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                    @if($canDeleteThisEnhancement)
                                                        <button type="button"
                                                            @click="deleteEnhancement({{ $enhancement->id }})"
                                                            class="p-1 text-gray-400 hover:text-rose-600 dark:text-gray-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-colors"
                                                            title="Delete Enhancement">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
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
                        x-data="{ open: sessionStorage.getItem('scrollToSection') === 'todos-section' }">
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
                                    <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                        Task Description <span class="text-rose-500">*</span>
                                    </label>
                                    <div class="sidebar-jodit {{ $isCompleted ? 'cursor-not-allowed opacity-75 pointer-events-none' : '' }}">
                                        <textarea name="description" id="todo-description-editor" autocomplete="off">{{ old('description') }}</textarea>
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
                                            <div class="text-sm text-gray-900 dark:text-white mb-1 prose prose-sm dark:prose-invert max-w-none break-words">
                                                {!! $todo->description !!}
                                            </div>
                                            <div
                                                class="flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 font-medium mb-2 bg-indigo-50 dark:bg-indigo-900/30 w-max px-2 py-0.5 rounded">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $todo->duration_value }} {{ ucfirst($todo->duration_type) }}
                                            </div>
                                            <div class="flex justify-between items-center pt-2 mt-2 border-t border-gray-100 dark:border-slate-700/60">
                                                <div class="flex items-center gap-1.5">
                                                    <div
                                                        class="w-4 h-4 rounded-full bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-400 flex items-center justify-center text-[8px] font-bold">
                                                        {{ collect(explode(' ', optional($todo->user)->name ?? 'U'))->map(fn($n) => substr($n, 0, 1))->take(2)->join('') }}
                                                    </div>
                                                    <span
                                                        class="text-xs text-gray-500 dark:text-gray-400 font-medium">{{ optional($todo->user)->name }}</span>
                                                    <span class="text-[10px] text-gray-400 dark:text-gray-500">•</span>
                                                    <span class="text-[11px] text-gray-400 dark:text-gray-500"
                                                        x-data="{ date: new Date('{{ $todo->created_at->toISOString() }}') }"
                                                        x-text="date.toLocaleString(undefined, { month: 'numeric', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit', second: '2-digit', hour12: true })">{{ $todo->created_at->format('n/j, g:i A') }}</span>
                                                </div>

                                                @php
                                                    $canEditThisTodo = !$isCompleted && ($canEditDetails || $user->hasRole('team-lead') || $todo->user_id === $user->id);
                                                    $canDeleteThisTodo = !$isCompleted && ($canEditDetails || $todo->user_id === $user->id);
                                                @endphp

                                                <div class="flex items-center gap-1">
                                                    @if($canEditThisTodo)
                                                        <button type="button"
                                                            @click="openEditTodo(@js([
                                                                'id' => $todo->id,
                                                                'description' => $todo->description,
                                                                'duration_value' => $todo->duration_value ?? 1,
                                                                'duration_type' => $todo->duration_type ?? 'days',
                                                            ]))"
                                                            class="p-1 text-gray-400 hover:text-indigo-600 dark:text-gray-500 dark:hover:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-slate-700 rounded-md transition-colors"
                                                            title="Edit To-Do">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                    @if($canDeleteThisTodo)
                                                        <button type="button"
                                                            @click="deleteTodo({{ $todo->id }})"
                                                            class="p-1 text-gray-400 hover:text-rose-600 dark:text-gray-500 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-md transition-colors"
                                                            title="Delete To-Do">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
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
        <!-- Edit Activity / Change Modal -->
        <div x-show="editActivityModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div @click.away="closeEditActivity()"
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700 w-full max-w-2xl overflow-hidden transform transition-all my-8"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                
                <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Change</h3>
                    </div>
                    <button type="button" @click="closeEditActivity()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 font-bold p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="edit-activity-form"
                    action="{{ route('crm-projects.activities.update', ['project' => $project->id, 'activity' => '0']) }}"
                    :action="'{{ route('crm-projects.activities.update', ['project' => $project->id, 'activity' => '__ID__']) }}'.replace('__ID__', editActivityData.id || '')"
                    method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Description <span class="text-rose-500">*</span>
                        </label>
                        <div class="sidebar-jodit">
                            <textarea name="change_description" id="modal-edit-change-description" autocomplete="off"></textarea>
                        </div>
                    </div>

                    <!-- Existing attachment & Replace / Remove -->
                    <div class="space-y-2">
                        <template x-if="editActivityData.attachment_name && !editActivityData.remove_attachment">
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-slate-900/70 border border-gray-200 dark:border-slate-700 rounded-xl">
                                <div class="flex items-center gap-2 truncate">
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate" x-text="editActivityData.attachment_name"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a :href="editActivityData.attachment_url" target="_blank" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                                    <button type="button" @click="editActivityData.remove_attachment = true" class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:hover:text-rose-400">Remove</button>
                                </div>
                            </div>
                        </template>

                        <input type="hidden" name="remove_attachment" :value="editActivityData.remove_attachment ? '1' : '0'">

                        <div class="flex items-center justify-between pt-1">
                            <input type="file" name="attachment" x-ref="editChangeAttachment" @change="editActivityData.newFileName = $event.target.files[0]?.name || ''" class="hidden">
                            <button type="button" @click="$refs.editChangeAttachment.click()"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 bg-gray-100 dark:bg-slate-700 hover:bg-indigo-50 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span x-text="editActivityData.newFileName ? 'Change selected file' : (editActivityData.attachment_name && !editActivityData.remove_attachment ? 'Replace attachment' : 'Attach file')">Attach file</span>
                            </button>
                        </div>

                        <template x-if="editActivityData.newFileName">
                            <div class="flex items-center justify-between gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs text-indigo-700 dark:text-indigo-300">
                                <span class="truncate font-medium" x-text="editActivityData.newFileName"></span>
                                <button type="button" @click="$refs.editChangeAttachment.value = ''; editActivityData.newFileName = ''" class="text-indigo-500 hover:text-red-500 font-bold ml-2">&times;</button>
                            </div>
                        </template>
                    </div>

                    <!-- Time Estimate -->
                    <div class="relative w-48">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Hours Estimate</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="time_estimate" x-model="editActivityData.time_estimate"
                                class="block w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors"
                                style="padding-left: 2.25rem; padding-right: 3rem;" placeholder="e.g. 2">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-xs text-gray-400 font-medium">hrs</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700/80">
                        <button type="button" @click="closeEditActivity()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Enhancement Modal -->
        <div x-show="editEnhancementModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div @click.away="closeEditEnhancement()"
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700 w-full max-w-2xl overflow-hidden transform transition-all my-8"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                
                <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Enhancement</h3>
                    </div>
                    <button type="button" @click="closeEditEnhancement()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 font-bold p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="edit-enhancement-form"
                    action="{{ route('crm-projects.enhancements.update', ['project' => $project->id, 'enhancement' => '0']) }}"
                    :action="'{{ route('crm-projects.enhancements.update', ['project' => $project->id, 'enhancement' => '__ID__']) }}'.replace('__ID__', editEnhancementData.id || '')"
                    method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Description <span class="text-rose-500">*</span>
                        </label>
                        <div class="sidebar-jodit">
                            <textarea name="enhancement_description" id="modal-edit-enhancement-description" autocomplete="off"></textarea>
                        </div>
                    </div>

                    <!-- Existing attachment & Replace / Remove -->
                    <div class="space-y-2">
                        <template x-if="editEnhancementData.attachment_name && !editEnhancementData.remove_attachment">
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-slate-900/70 border border-gray-200 dark:border-slate-700 rounded-xl">
                                <div class="flex items-center gap-2 truncate">
                                    <svg class="w-4 h-4 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300 truncate" x-text="editEnhancementData.attachment_name"></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a :href="editEnhancementData.attachment_url" target="_blank" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                                    <button type="button" @click="editEnhancementData.remove_attachment = true" class="text-xs font-semibold text-rose-500 hover:text-rose-600 dark:hover:text-rose-400">Remove</button>
                                </div>
                            </div>
                        </template>

                        <input type="hidden" name="remove_attachment" :value="editEnhancementData.remove_attachment ? '1' : '0'">

                        <div class="flex items-center justify-between pt-1">
                            <input type="file" name="attachment" x-ref="editEnhancementAttachment" @change="editEnhancementData.newFileName = $event.target.files[0]?.name || ''" class="hidden">
                            <button type="button" @click="$refs.editEnhancementAttachment.click()"
                                class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 bg-gray-100 dark:bg-slate-700 hover:bg-indigo-50 dark:hover:bg-slate-600 px-3 py-1.5 rounded-lg transition-colors">
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                <span x-text="editEnhancementData.newFileName ? 'Change selected file' : (editEnhancementData.attachment_name && !editEnhancementData.remove_attachment ? 'Replace attachment' : 'Attach file')">Attach file</span>
                            </button>
                        </div>

                        <template x-if="editEnhancementData.newFileName">
                            <div class="flex items-center justify-between gap-2 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-800 rounded-lg text-xs text-indigo-700 dark:text-indigo-300">
                                <span class="truncate font-medium" x-text="editEnhancementData.newFileName"></span>
                                <button type="button" @click="$refs.editEnhancementAttachment.value = ''; editEnhancementData.newFileName = ''" class="text-indigo-500 hover:text-red-500 font-bold ml-2">&times;</button>
                            </div>
                        </template>
                    </div>

                    <!-- Time Estimate -->
                    <div class="relative w-48">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Hours Estimate</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <input type="text" name="time_estimate" x-model="editEnhancementData.time_estimate"
                                class="block w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors"
                                style="padding-left: 2.25rem; padding-right: 3rem;" placeholder="e.g. 2">
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-xs text-gray-400 font-medium">hrs</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700/80">
                        <button type="button" @click="closeEditEnhancement()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit To-Do Modal -->
        <div x-show="editTodoModal"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div @click.away="closeEditTodo()"
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700 w-full max-w-2xl overflow-hidden transform transition-all my-8"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                
                <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit To-Do Task</h3>
                    </div>
                    <button type="button" @click="closeEditTodo()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 font-bold p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form id="edit-todo-form"
                    action="{{ route('crm-projects.todos.update', ['project' => $project->id, 'todo' => '0']) }}"
                    :action="'{{ route('crm-projects.todos.update', ['project' => $project->id, 'todo' => '__ID__']) }}'.replace('__ID__', editTodoData.id || '')"
                    method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Task Description <span class="text-rose-500">*</span>
                        </label>
                        <div class="sidebar-jodit">
                            <textarea name="description" id="modal-edit-todo-description" autocomplete="off"></textarea>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-32">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Duration</label>
                            <input type="number" min="1" name="duration_value" x-model="editTodoData.duration_value"
                                class="block w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Duration Unit</label>
                            <select name="duration_type" x-model="editTodoData.duration_type"
                                class="block w-full rounded-lg border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm shadow-sm transition-colors">
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700/80">
                        <button type="button" @click="closeEditTodo()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            // Alpine Component definition for Sidebar Management
            function projectSidebarManager() {
                return {
                    editActivityModal: false,
                    editActivityData: {
                        id: null,
                        description: '',
                        time_estimate: '',
                        attachment_name: '',
                        attachment_url: '',
                        remove_attachment: false,
                        newFileName: ''
                    },
                    editEnhancementModal: false,
                    editEnhancementData: {
                        id: null,
                        description: '',
                        time_estimate: '',
                        attachment_name: '',
                        attachment_url: '',
                        remove_attachment: false,
                        newFileName: ''
                    },
                    editTodoModal: false,
                    editTodoData: {
                        id: null,
                        description: '',
                        duration_value: 1,
                        duration_type: 'days'
                    },

                    openEditActivity(activity) {
                        this.editActivityData = {
                            id: activity.id,
                            description: activity.description || '',
                            time_estimate: activity.time_estimate || '',
                            attachment_name: activity.attachment_name || '',
                            attachment_url: activity.attachment_url || '',
                            remove_attachment: false,
                            newFileName: ''
                        };
                        const form = document.getElementById('edit-activity-form');
                        if (form && activity.id) {
                            form.action = '{{ route('crm-projects.activities.update', ['project' => $project->id, 'activity' => '__ID__']) }}'.replace('__ID__', activity.id);
                        }
                        this.editActivityModal = true;
                        this.$nextTick(() => {
                            if (window.initModalEditors) window.initModalEditors();
                            if (window.modalChangeEditor) {
                                window.modalChangeEditor.value = activity.description || '';
                            }
                        });
                    },
                    closeEditActivity() {
                        this.editActivityModal = false;
                        if (this.$refs.editChangeAttachment) {
                            this.$refs.editChangeAttachment.value = '';
                        }
                    },

                    openEditEnhancement(enhancement) {
                        this.editEnhancementData = {
                            id: enhancement.id,
                            description: enhancement.description || '',
                            time_estimate: enhancement.time_estimate || '',
                            attachment_name: enhancement.attachment_name || '',
                            attachment_url: enhancement.attachment_url || '',
                            remove_attachment: false,
                            newFileName: ''
                        };
                        const form = document.getElementById('edit-enhancement-form');
                        if (form && enhancement.id) {
                            form.action = '{{ route('crm-projects.enhancements.update', ['project' => $project->id, 'enhancement' => '__ID__']) }}'.replace('__ID__', enhancement.id);
                        }
                        this.editEnhancementModal = true;
                        this.$nextTick(() => {
                            if (window.initModalEditors) window.initModalEditors();
                            if (window.modalEnhancementEditor) {
                                window.modalEnhancementEditor.value = enhancement.description || '';
                            }
                        });
                    },
                    closeEditEnhancement() {
                        this.editEnhancementModal = false;
                        if (this.$refs.editEnhancementAttachment) {
                            this.$refs.editEnhancementAttachment.value = '';
                        }
                    },

                    openEditTodo(todo) {
                        this.editTodoData = {
                            id: todo.id,
                            description: todo.description || '',
                            duration_value: todo.duration_value || 1,
                            duration_type: todo.duration_type || 'days'
                        };
                        const form = document.getElementById('edit-todo-form');
                        if (form && todo.id) {
                            form.action = '{{ route('crm-projects.todos.update', ['project' => $project->id, 'todo' => '__ID__']) }}'.replace('__ID__', todo.id);
                        }
                        this.editTodoModal = true;
                        this.$nextTick(() => {
                            if (window.initModalEditors) window.initModalEditors();
                            if (window.modalTodoEditor) {
                                window.modalTodoEditor.value = todo.description || '';
                            }
                        });
                    },
                    closeEditTodo() {
                        this.editTodoModal = false;
                    },

                    deleteActivity(id) {
                        if (!confirm('Are you sure you want to delete this change?')) return;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('crm-projects.activities.destroy', ['project' => $project->id, 'activity' => '__ID__']) }}'.replace('__ID__', id);
                        form.innerHTML = `@csrf @method('DELETE')`;
                        document.body.appendChild(form);
                        sessionStorage.setItem('scrollToSection', 'changes-section');
                        form.submit();
                    },

                    deleteEnhancement(id) {
                        if (!confirm('Are you sure you want to delete this enhancement?')) return;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('crm-projects.enhancements.destroy', ['project' => $project->id, 'enhancement' => '__ID__']) }}'.replace('__ID__', id);
                        form.innerHTML = `@csrf @method('DELETE')`;
                        document.body.appendChild(form);
                        sessionStorage.setItem('scrollToSection', 'enhancements-section');
                        form.submit();
                    },

                    deleteTodo(id) {
                        if (!confirm('Are you sure you want to delete this to-do task?')) return;
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('crm-projects.todos.destroy', ['project' => $project->id, 'todo' => '__ID__']) }}'.replace('__ID__', id);
                        form.innerHTML = `@csrf @method('DELETE')`;
                        document.body.appendChild(form);
                        sessionStorage.setItem('scrollToSection', 'todos-section');
                        form.submit();
                    }
                };
            }

            document.addEventListener('DOMContentLoaded', function () {
                const isDark = document.documentElement.classList.contains('dark');

                // Standard font list
                const fontList = {
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
                };

                // Initialize Project Details Description Editor (if present)
                let projectDescEditor = null;
                const descEl = document.getElementById('description-editor');
                if (descEl) {
                    projectDescEditor = new Jodit(descEl, {
                        theme: isDark ? 'dark' : 'default',
                        height: 250,
                        placeholder: 'Enter project description...',
                        toolbarButtonSize: 'middle',
                        readonly: {{ $canEditDetails ? 'false' : 'true' }},
                        toolbar: {{ $canEditDetails ? 'true' : 'false' }},
                        autofocus: false,
                        controls: {
                            font: { list: fontList }
                        }
                    });
                }

                // Sidebar compact Jodit editor options
                const sidebarEditorOptions = {
                    theme: isDark ? 'dark' : 'default',
                    height: 160,
                    minHeight: 120,
                    toolbarButtonSize: 'small',
                    toolbarAdaptive: false,
                    showCharsCounter: false,
                    showWordsCounter: false,
                    showXPathInStatusbar: false,
                    buttons: [
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'ul', 'ol', '|',
                        'font', 'fontsize', 'paragraph', '|',
                        'link', 'table', '|',
                        'undo', 'redo'
                    ],
                    buttonsMD: [
                        'bold', 'italic', 'underline', '|',
                        'ul', 'ol', '|',
                        'link', 'table', '|',
                        'undo', 'redo'
                    ],
                    buttonsSM: [
                        'bold', 'italic', 'underline', '|',
                        'ul', 'ol', '|',
                        'link'
                    ],
                    buttonsXS: [
                        'bold', 'italic', 'underline', '|',
                        'ul', 'ol'
                    ],
                    readonly: {{ $isCompleted ? 'true' : 'false' }},
                    toolbar: {{ $isCompleted ? 'false' : 'true' }},
                    autofocus: false,
                    controls: {
                        font: { list: fontList }
                    }
                };

                // Initialize Add Changes Editor
                let changeEditor = null;
                const changeDescEl = document.getElementById('change-description-editor');
                if (changeDescEl) {
                    changeEditor = new Jodit(changeDescEl, Object.assign({}, sidebarEditorOptions, {
                        placeholder: 'What changed?'
                    }));
                }

                // Initialize Add Enhancements Editor
                let enhancementEditor = null;
                const enhancementDescEl = document.getElementById('enhancement-description-editor');
                if (enhancementDescEl) {
                    enhancementEditor = new Jodit(enhancementDescEl, Object.assign({}, sidebarEditorOptions, {
                        placeholder: 'What enhancement?'
                    }));
                }

                // Initialize Add To-Dos Editor
                let todoEditor = null;
                const todoDescEl = document.getElementById('todo-description-editor');
                if (todoDescEl) {
                    todoEditor = new Jodit(todoDescEl, Object.assign({}, sidebarEditorOptions, {
                        placeholder: 'What needs to be done?'
                    }));
                }

                // Initialize Modal Jodit Editors
                window.modalChangeEditor = null;
                window.modalEnhancementEditor = null;
                window.modalTodoEditor = null;

                window.initModalEditors = function () {
                    const changeModalEl = document.getElementById('modal-edit-change-description');
                    if (changeModalEl && !window.modalChangeEditor) {
                        window.modalChangeEditor = new Jodit(changeModalEl, Object.assign({}, sidebarEditorOptions, {
                            placeholder: 'Edit change description...',
                            readonly: false,
                            toolbar: true
                        }));
                    }

                    const enhancementModalEl = document.getElementById('modal-edit-enhancement-description');
                    if (enhancementModalEl && !window.modalEnhancementEditor) {
                        window.modalEnhancementEditor = new Jodit(enhancementModalEl, Object.assign({}, sidebarEditorOptions, {
                            placeholder: 'Edit enhancement description...',
                            readonly: false,
                            toolbar: true
                        }));
                    }

                    const todoModalEl = document.getElementById('modal-edit-todo-description');
                    if (todoModalEl && !window.modalTodoEditor) {
                        window.modalTodoEditor = new Jodit(todoModalEl, Object.assign({}, sidebarEditorOptions, {
                            placeholder: 'Edit task description...',
                            readonly: false,
                            toolbar: true
                        }));
                    }
                };

                const detailsForm = document.getElementById('details-form');
                if (detailsForm) {
                    detailsForm.addEventListener('submit', function(e) {
                        e.preventDefault();

                        if (projectDescEditor && descEl) {
                            descEl.value = projectDescEditor.value;
                        }
                        
                        const btn = e.submitter || detailsForm.querySelector('button[type="submit"]');
                        const originalText = btn ? btn.innerHTML : '';
                        if (btn) {
                            btn.innerHTML = '<svg class="animate-spin h-5 w-5 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...';
                            btn.disabled = true;
                        }

                        const formData = new FormData(detailsForm);
                        if (e.submitter && e.submitter.name) {
                            formData.append(e.submitter.name, e.submitter.value);
                        }

                        if (window.__projectSelectedFiles && window.__projectSelectedFiles.length > 0) {
                            formData.delete('attachments[]');
                            formData.delete('attachments');
                            formData.delete('files[]');
                            formData.delete('files');
                            window.__projectSelectedFiles.forEach(file => {
                                formData.append('attachments[]', file);
                            });
                        }
                        
                        fetch(detailsForm.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => {
                            return response.json().then(data => {
                                if (!response.ok) {
                                    let errorMsg = data.message || 'Error updating details';
                                    if (data.errors) {
                                        const firstKey = Object.keys(data.errors)[0];
                                        if (firstKey && data.errors[firstKey].length > 0) {
                                            errorMsg = data.errors[firstKey][0];
                                        }
                                    }
                                    throw new Error(errorMsg);
                                }
                                return data;
                            });
                        })
                        .then(data => {
                            if(data.success) {
                                window.dispatchEvent(new CustomEvent('notify', {
                                    detail: {
                                        message: data.message || 'Project details updated successfully!',
                                        type: 'success'
                                    }
                                }));
                                
                                const targetUrl = data.redirect || window.location.href;
                                setTimeout(() => window.location.href = targetUrl, 500);
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
                                    message: error.message || 'An unexpected error occurred.',
                                    type: 'error'
                                }
                            }));
                        })
                        .finally(() => {
                            if (btn) {
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            }
                        });
                    });
                }

                // Add forms submit listeners
                const changesForm = document.getElementById('changes-form');
                const enhancementsForm = document.getElementById('enhancements-form');
                const todosForm = document.getElementById('todos-form');

                if (changesForm) {
                    changesForm.addEventListener('submit', function (e) {
                        if (changeEditor && changeDescEl) {
                            changeDescEl.value = changeEditor.value;
                            const textOnly = changeEditor.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                            const fileInput = changesForm.querySelector('input[type="file"][name="attachment"]');
                            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                            if (!textOnly && !hasFile) {
                                e.preventDefault();
                                alert('Please provide a description or attach a file.');
                                return false;
                            }
                        }
                        sessionStorage.setItem('scrollToSection', 'changes-section');
                    });
                }
                if (enhancementsForm) {
                    enhancementsForm.addEventListener('submit', function (e) {
                        if (enhancementEditor && enhancementDescEl) {
                            enhancementDescEl.value = enhancementEditor.value;
                            const textOnly = enhancementEditor.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                            const fileInput = enhancementsForm.querySelector('input[type="file"][name="attachment"]');
                            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                            if (!textOnly && !hasFile) {
                                e.preventDefault();
                                alert('Please provide a description or attach a file.');
                                return false;
                            }
                        }
                        sessionStorage.setItem('scrollToSection', 'enhancements-section');
                    });
                }

                if (todosForm) {
                    todosForm.addEventListener('submit', function (e) {
                        if (todoEditor && todoDescEl) {
                            todoDescEl.value = todoEditor.value;
                            const textOnly = todoEditor.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                            if (!textOnly) {
                                e.preventDefault();
                                alert('Please enter a task description.');
                                return false;
                            }
                        }
                        sessionStorage.setItem('scrollToSection', 'todos-section');
                    });
                }

                // Edit modals submit listeners
                const editActivityForm = document.getElementById('edit-activity-form');
                if (editActivityForm) {
                    editActivityForm.addEventListener('submit', function (e) {
                        const changeModalEl = document.getElementById('modal-edit-change-description');
                        if (window.modalChangeEditor && changeModalEl) {
                            changeModalEl.value = window.modalChangeEditor.value;
                            const textOnly = window.modalChangeEditor.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                            const fileInput = editActivityForm.querySelector('input[type="file"][name="attachment"]');
                            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                            const isRemoving = editActivityForm.querySelector('input[name="remove_attachment"]')?.value === '1';
                            
                            if (!textOnly && !hasFile && isRemoving) {
                                e.preventDefault();
                                alert('Please provide a description or attach a file.');
                                return false;
                            }
                        }
                        sessionStorage.setItem('scrollToSection', 'changes-section');
                    });
                }

                const editEnhancementForm = document.getElementById('edit-enhancement-form');
                if (editEnhancementForm) {
                    editEnhancementForm.addEventListener('submit', function (e) {
                        const enhancementModalEl = document.getElementById('modal-edit-enhancement-description');
                        if (window.modalEnhancementEditor && enhancementModalEl) {
                            enhancementModalEl.value = window.modalEnhancementEditor.value;
                            const textOnly = window.modalEnhancementEditor.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                            const fileInput = editEnhancementForm.querySelector('input[type="file"][name="attachment"]');
                            const hasFile = fileInput && fileInput.files && fileInput.files.length > 0;
                            const isRemoving = editEnhancementForm.querySelector('input[name="remove_attachment"]')?.value === '1';
                            
                            if (!textOnly && !hasFile && isRemoving) {
                                e.preventDefault();
                                alert('Please provide a description or attach a file.');
                                return false;
                            }
                        }
                        sessionStorage.setItem('scrollToSection', 'enhancements-section');
                    });
                }

                const editTodoForm = document.getElementById('edit-todo-form');
                if (editTodoForm) {
                    editTodoForm.addEventListener('submit', function (e) {
                        const todoModalEl = document.getElementById('modal-edit-todo-description');
                        if (window.modalTodoEditor && todoModalEl) {
                            todoModalEl.value = window.modalTodoEditor.value;
                            const textOnly = window.modalTodoEditor.value.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
                            if (!textOnly) {
                                e.preventDefault();
                                alert('Please enter a task description.');
                                return false;
                            }
                        }
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
    </div>
</x-app-layout>