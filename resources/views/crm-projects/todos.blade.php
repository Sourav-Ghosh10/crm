<x-app-layout>
    <div class=" mx-auto py-8 px-4 sm:px-6 lg:px-8" x-data="todosPage()">
        
        <!-- Header & Breadcrumb Section -->
        <div class="mb-8">
            <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mb-3">
                <a href="{{ route('crm-projects.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Projects</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <a href="{{ route('crm-projects.show', $project->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $project->project_name }}</a>
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="font-medium text-gray-900 dark:text-white">To-Do List</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-200 dark:border-slate-700/80">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        {{ $project->project_name }}
                    </h1>
                    <h2 class="text-xl font-bold text-[#F78166] tracking-tight mt-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#F78166]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        To-Do List & Tasks
                    </h2>
                </div>

                <div class="flex items-center gap-2.5">
                    <a href="{{ route('crm-projects.show', $project->id) }}" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/70 rounded-xl transition-colors shadow-xs">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Project
                    </a>
                    <a href="{{ route('crm-projects.edit', $project->id) }}" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/70 rounded-xl transition-colors shadow-xs">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        Settings
                    </a>
                </div>
            </div>
        </div>

        <!-- Toast / Feedback Message -->
        <template x-if="toastMessage">
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-2xl text-sm font-semibold text-emerald-400 flex items-center justify-between transition-all"
                 x-transition>
                <div class="flex items-center gap-2.5">
                    <svg class="w-5 h-5 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span x-text="toastMessage"></span>
                </div>
                <button @click="toastMessage = ''" class="text-emerald-400 hover:text-emerald-300 font-bold px-2">&times;</button>
            </div>
        </template>

        <!-- Stats Overview Cards -->
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <!-- Total Tasks -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-100 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Tasks</span>
                    <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1" x-text="todosList.length">
                        {{ $todos->count() }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>

            <!-- Pending Tasks -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-100 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pending</span>
                    <h3 class="text-3xl font-extrabold text-amber-600 dark:text-amber-400 mt-1" x-text="pendingCount">
                        {{ $todos->where('status', '!=', 'completed')->count() }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Completed Tasks -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-5 border border-gray-100 dark:border-slate-700 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Completed</span>
                    <h3 class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1" x-text="completedCount">
                        {{ $todos->where('status', 'completed')->count() }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left Column: Add New Task Form -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 p-6 shadow-sm sticky top-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Add New Task
                    </h3>

                    <form @submit.prevent="submitNewTodo" class="space-y-4">
                        <!-- Task Description -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                                Task Description <span class="text-rose-500">*</span>
                            </label>
                            <textarea x-model="formDescription" rows="3" required placeholder="What needs to be done?"
                                class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors resize-none px-3.5 py-2.5"></textarea>
                        </div>

                        <!-- Duration -->
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Duration Value</label>
                                <input type="number" min="1" x-model="formDurationValue"
                                    class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors px-3.5 py-2">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Duration Type</label>
                                <select x-model="formDurationType"
                                    class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors px-3.5 py-2">
                                    <option value="hours">Hours</option>
                                    <option value="days">Days</option>
                                    <option value="weeks">Weeks</option>
                                    <option value="months">Months</option>
                                </select>
                            </div>
                        </div>

                        <!-- Divider -->
                        <div class="border-t border-gray-200 dark:border-slate-700 pt-4">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                </svg>
                                Recurring Type
                            </label>
                            <select x-model="formRecurrenceType"
                                class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors px-3.5 py-2">
                                <option value="none">⛔ None (One-time task)</option>
                                <option value="daily">📅 Daily</option>
                                <option value="weekly">📆 Weekly (choose days)</option>
                                <option value="monthly">🗓️ Monthly (choose dates)</option>
                                <option value="yearly">🔁 Yearly</option>
                            </select>
                        </div>

                        <!-- Weekly Day Picker -->
                        <div x-show="formRecurrenceType === 'weekly'" x-transition style="display:none;">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Select Days of the Week</label>
                            <div class="grid grid-cols-4 gap-1.5">
                                <template x-for="day in weekDays" :key="day.value">
                                    <button type="button"
                                        @click="toggleDay(day.value)"
                                        :class="formRecurrenceDays.includes(day.value) 
                                            ? 'bg-indigo-600 text-white border-indigo-600 font-bold' 
                                            : 'bg-gray-50 dark:bg-slate-900 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-slate-600 hover:border-indigo-400'"
                                        class="py-1.5 text-xs rounded-lg border transition-all cursor-pointer text-center"
                                        x-text="day.label">
                                    </button>
                                </template>
                            </div>
                            <p class="mt-1.5 text-[11px] text-gray-400 dark:text-gray-500" x-show="formRecurrenceDays.length === 0">Select at least one day.</p>
                        </div>

                        <!-- Monthly Date Picker -->
                        <div x-show="formRecurrenceType === 'monthly'" x-transition style="display:none;">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Select Dates of the Month</label>
                            <div style="display:grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 4px;">
                                <template x-for="d in 31" :key="d">
                                    <button type="button"
                                        @click="toggleDate(d)"
                                        :class="formRecurrenceDates.includes(d)
                                            ? 'bg-indigo-600 text-white border-indigo-600 font-bold'
                                            : 'bg-gray-50 dark:bg-slate-900 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-slate-600 hover:border-indigo-400'"
                                        class="py-1 text-xs rounded-lg border transition-all cursor-pointer text-center"
                                        x-text="d">
                                    </button>
                                </template>
                            </div>
                            <p class="mt-1.5 text-[11px] text-gray-400 dark:text-gray-500" x-show="formRecurrenceDates.length === 0">Select at least one date.</p>
                        </div>

                        <!-- Yearly Date Picker -->
                        <div x-show="formRecurrenceType === 'yearly'" x-transition style="display:none;">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Choose Date(s) That Repeat Each Year</label>

                            <!-- Month + Day Row -->
                            <div class="flex gap-2 mb-2">
                                <select x-model="yearlyPickMonth"
                                    class="flex-1 text-xs rounded-lg border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-2.5 py-1.5">
                                    <template x-for="(m, i) in monthNames" :key="i">
                                        <option :value="String(i+1).padStart(2,'0')" x-text="m"></option>
                                    </template>
                                </select>
                                <select x-model="yearlyPickDay"
                                    class="w-20 text-xs rounded-lg border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-2.5 py-1.5">
                                    <template x-for="d in 31" :key="d">
                                        <option :value="String(d).padStart(2,'0')" x-text="d"></option>
                                    </template>
                                </select>
                                <button type="button" @click="addYearlyDate()"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer whitespace-nowrap">+ Add</button>
                            </div>

                            <!-- Chips of selected dates -->
                            <div class="flex flex-wrap gap-1.5 min-h-[28px]">
                                <template x-if="formRecurrenceYearlyDates.length === 0">
                                    <span class="text-[11px] text-gray-400 dark:text-gray-500 italic">No dates added yet. Select a month & day then click + Add.</span>
                                </template>
                                <template x-for="yd in formRecurrenceYearlyDates" :key="yd">
                                    <span class="inline-flex items-center gap-1 bg-indigo-600 text-white text-[11px] font-semibold px-2 py-0.5 rounded-full">
                                        <span x-text="formatYearlyDate(yd)"></span>
                                        <button type="button" @click="removeYearlyDate(yd)" class="ml-0.5 hover:text-red-200 cursor-pointer">&times;</button>
                                    </span>
                                </template>
                            </div>
                            <p class="mt-1.5 text-[11px] text-gray-400 dark:text-gray-500" x-show="formRecurrenceYearlyDates.length === 0">Add at least one date.</p>
                        </div>

                        <!-- Recurrence summary badge -->
                        <div x-show="formRecurrenceType && formRecurrenceType !== 'none'" style="display:none;"
                            class="px-3 py-2 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs text-indigo-700 dark:text-indigo-300 font-semibold flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span x-text="recurrenceSummary()"></span>
                        </div>

                        <button type="submit" :disabled="isSubmitting || !formDescription.trim() || (formRecurrenceType === 'weekly' && formRecurrenceDays.length === 0) || (formRecurrenceType === 'monthly' && formRecurrenceDates.length === 0) || (formRecurrenceType === 'yearly' && formRecurrenceYearlyDates.length === 0)"
                            class="w-full mt-2 inline-flex justify-center items-center gap-2 px-5 py-2.5 bg-[#238636] hover:bg-[#2ea043] disabled:opacity-50 text-white text-sm font-bold rounded-xl shadow-xs transition-colors cursor-pointer">
                            <template x-if="isSubmitting">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <template x-if="!isSubmitting">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </template>
                            <span>Add To-Do Item</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right Column: Task Filter & List Items -->
            <div class="lg:col-span-2 space-y-4">
                
                <!-- Filter Tabs -->
                <div class="flex items-center justify-between flex-wrap gap-3 bg-white dark:bg-slate-800 p-2 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center gap-1.5">
                        <button @click="activeTab = 'all'" 
                            :class="activeTab === 'all' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700/50'"
                            class="px-4 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            All (<span x-text="todosList.length"></span>)
                        </button>
                        <button @click="activeTab = 'pending'" 
                            :class="activeTab === 'pending' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700/50'"
                            class="px-4 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            Pending (<span x-text="pendingCount"></span>)
                        </button>
                        <button @click="activeTab = 'completed'" 
                            :class="activeTab === 'completed' ? 'bg-indigo-600 text-white shadow-xs' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-slate-700/50'"
                            class="px-4 py-1.5 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            Completed (<span x-text="completedCount"></span>)
                        </button>
                    </div>

                    <div class="text-xs text-gray-500 dark:text-gray-400 px-3">
                        <span x-text="filteredTodos.length"></span> tasks shown
                    </div>
                </div>

                <!-- Tasks Items List -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden divide-y divide-gray-100 dark:divide-slate-700/60">
                    <template x-for="todo in filteredTodos" :key="todo.id">
                        <div class="p-4 sm:p-5 hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors flex items-start justify-between gap-4 group">
                            
                            <!-- Left: Checkbox & Task Body -->
                            <div class="flex items-start gap-3.5 flex-1 min-w-0">
                                
                                <!-- Checkbox Status Toggle -->
                                <button type="button" @click="toggleStatus(todo)"
                                    class="mt-0.5 shrink-0 w-6 h-6 rounded-lg border transition-all flex items-center justify-center cursor-pointer"
                                    :class="todo.status === 'completed' ? 'bg-emerald-500 border-emerald-500 text-white shadow-xs' : 'border-gray-300 dark:border-slate-600 hover:border-indigo-500 text-transparent bg-white dark:bg-slate-900'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </button>

                                <div class="flex-1 min-w-0">
                                    <div class="text-base font-semibold text-gray-900 dark:text-white leading-relaxed break-words transition-all prose prose-sm dark:prose-invert max-w-none"
                                        :class="{ 'line-through text-gray-400 dark:text-gray-500 font-normal': todo.status === 'completed' }"
                                        x-html="todo.description"></div>
                                    
                                    <!-- Meta Badges -->
                                    <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                        
                                        <!-- Duration Estimate Badge -->
                                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 rounded-lg border border-indigo-100 dark:border-indigo-900/40">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span x-text="(todo.duration_value || 1) + ' ' + capitalize(todo.duration_type || 'days')"></span>
                                        </span>

                                        <!-- Status Badge -->
                                        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-lg"
                                            :class="todo.status === 'completed' ? 'bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40' : 'bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-900/40'"
                                            x-text="todo.status === 'completed' ? 'Done' : 'Pending'"></span>

                                        <!-- Recurrence Badge -->
                                        <template x-if="todo.recurrence_type && todo.recurrence_type !== 'none'">
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-1 rounded-lg border border-indigo-100 dark:border-indigo-900/40">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                                </svg>
                                                <span x-text="getRecurrenceLabel(todo)"></span>
                                            </span>
                                        </template>

                                        <!-- Timestamp -->
                                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400" x-text="formatDate(todo.created_at)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Creator Tag & Delete Action -->
                            <div class="flex items-center gap-2.5 shrink-0">
                                <template x-if="todo.user">
                                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-slate-700/50 py-1 px-2.5 rounded-xl border border-gray-200 dark:border-slate-600">
                                        <div class="w-6 h-6 rounded-full bg-indigo-100 dark:bg-indigo-900/80 text-indigo-700 dark:text-indigo-300 flex items-center justify-center text-[10px] font-bold"
                                            x-text="getUserInitials(todo.user.name)">
                                        </div>
                                        <span class="text-xs text-gray-700 dark:text-gray-200 font-medium hidden sm:inline-block max-w-[120px] truncate" x-text="todo.user.name"></span>
                                    </div>
                                </template>

                                <!-- Action Buttons (Edit & Delete) -->
                                <div class="flex items-center gap-1">
                                    <button type="button" @click="openEditModal(todo)"
                                        class="p-2 text-gray-400 hover:text-indigo-600 dark:text-slate-500 dark:hover:text-indigo-400 rounded-xl hover:bg-indigo-50 dark:hover:bg-slate-700 transition-colors opacity-80 group-hover:opacity-100 cursor-pointer"
                                        title="Edit Task">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                    </button>

                                    <!-- Delete Button -->
                                    <button type="button" @click="deleteItem(todo.id)"
                                        class="p-2 text-gray-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 rounded-xl hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors opacity-80 group-hover:opacity-100 cursor-pointer"
                                        title="Delete Task">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Empty State -->
                    <template x-if="filteredTodos.length === 0">
                        <div class="p-12 text-center">
                            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/50 text-indigo-500 flex items-center justify-center">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                </svg>
                            </div>
                            <h3 class="text-base font-bold text-gray-800 dark:text-gray-200">No tasks found</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-sm mx-auto">
                                <span x-show="activeTab === 'all'">Add your first task in the left form to start organizing this project.</span>
                                <span x-show="activeTab === 'pending'">There are no pending tasks right now.</span>
                                <span x-show="activeTab === 'completed'">No completed tasks yet. Check off items as you finish them.</span>
                            </p>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Edit Task Modal -->
        <div x-show="editModalOpen"
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/60 backdrop-blur-xs flex items-center justify-center p-4"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0">
            
            <div @click.away="closeEditModal()"
                class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700 w-full max-w-xl overflow-hidden transform transition-all my-8"
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
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Edit Task</h3>
                    </div>
                    <button type="button" @click="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 font-bold p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitUpdateTodo" class="p-6 space-y-4">
                    <!-- Task Description -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">
                            Task Description <span class="text-rose-500">*</span>
                        </label>
                        <textarea x-model="editDescription" rows="3" required placeholder="What needs to be done?"
                            class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white placeholder-gray-400 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors resize-none px-3.5 py-2.5"></textarea>
                    </div>

                    <!-- Duration -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Duration Value</label>
                            <input type="number" min="1" x-model="editDurationValue"
                                class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors px-3.5 py-2">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Duration Type</label>
                            <select x-model="editDurationType"
                                class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors px-3.5 py-2">
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                            </select>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-200 dark:border-slate-700 pt-4">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            Recurring Type
                        </label>
                        <select x-model="editRecurrenceType"
                            class="w-full text-sm rounded-xl border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors px-3.5 py-2">
                            <option value="none">⛔ None (One-time task)</option>
                            <option value="daily">📅 Daily</option>
                            <option value="weekly">📆 Weekly (choose days)</option>
                            <option value="monthly">🗓️ Monthly (choose dates)</option>
                            <option value="yearly">🔁 Yearly</option>
                        </select>
                    </div>

                    <!-- Weekly Day Picker -->
                    <div x-show="editRecurrenceType === 'weekly'" x-transition style="display:none;">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Select Days of the Week</label>
                        <div class="grid grid-cols-4 gap-1.5">
                            <template x-for="day in weekDays" :key="'edit-day-'+day.value">
                                <button type="button"
                                    @click="editToggleDay(day.value)"
                                    :class="editRecurrenceDays.includes(day.value) 
                                        ? 'bg-indigo-600 text-white border-indigo-600 font-bold' 
                                        : 'bg-gray-50 dark:bg-slate-900 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-slate-600 hover:border-indigo-400'"
                                    class="py-1.5 text-xs rounded-lg border transition-all cursor-pointer text-center"
                                    x-text="day.label">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Monthly Date Picker -->
                    <div x-show="editRecurrenceType === 'monthly'" x-transition style="display:none;">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Select Dates of the Month</label>
                        <div style="display:grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 4px;">
                            <template x-for="d in 31" :key="'edit-date-'+d">
                                <button type="button"
                                    @click="editToggleDate(d)"
                                    :class="editRecurrenceDates.includes(d)
                                        ? 'bg-indigo-600 text-white border-indigo-600 font-bold'
                                        : 'bg-gray-50 dark:bg-slate-900 text-gray-600 dark:text-gray-400 border-gray-300 dark:border-slate-600 hover:border-indigo-400'"
                                    class="py-1 text-xs rounded-lg border transition-all cursor-pointer text-center"
                                    x-text="d">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Yearly Date Picker -->
                    <div x-show="editRecurrenceType === 'yearly'" x-transition style="display:none;">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Choose Date(s) That Repeat Each Year</label>
                        <div class="flex gap-2 mb-2">
                            <select x-model="editYearlyPickMonth"
                                class="flex-1 text-xs rounded-lg border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-2.5 py-1.5">
                                <template x-for="(m, i) in monthNames" :key="'edit-month-'+i">
                                    <option :value="String(i+1).padStart(2,'0')" x-text="m"></option>
                                </template>
                            </select>
                            <select x-model="editYearlyPickDay"
                                class="w-20 text-xs rounded-lg border border-gray-300 dark:border-slate-600 bg-gray-50 dark:bg-slate-900 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 px-2.5 py-1.5">
                                <template x-for="d in 31" :key="'edit-day-val-'+d">
                                    <option :value="String(d).padStart(2,'0')" x-text="d"></option>
                                </template>
                            </select>
                            <button type="button" @click="editAddYearlyDate()"
                                class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg transition-colors cursor-pointer whitespace-nowrap">+ Add</button>
                        </div>

                        <div class="flex flex-wrap gap-1.5 min-h-[28px]">
                            <template x-for="yd in editRecurrenceYearlyDates" :key="'edit-yd-'+yd">
                                <span class="inline-flex items-center gap-1 bg-indigo-600 text-white text-[11px] font-semibold px-2 py-0.5 rounded-full">
                                    <span x-text="formatYearlyDate(yd)"></span>
                                    <button type="button" @click="editRemoveYearlyDate(yd)" class="ml-0.5 hover:text-red-200 cursor-pointer">&times;</button>
                                </span>
                            </template>
                        </div>
                    </div>

                    <!-- Recurrence summary badge -->
                    <div x-show="editRecurrenceType && editRecurrenceType !== 'none'" style="display:none;"
                        class="px-3 py-2 bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 rounded-xl text-xs text-indigo-700 dark:text-indigo-300 font-semibold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span x-text="editRecurrenceSummary()"></span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-slate-700/80">
                        <button type="button" @click="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg transition-colors">Cancel</button>
                        <button type="submit" :disabled="isUpdating || !editDescription.trim()"
                            class="inline-flex items-center gap-1.5 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-sm font-bold rounded-lg shadow-sm transition-colors">
                            <template x-if="isUpdating">
                                <svg class="animate-spin w-4 h-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </template>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function todosPage() {
            return {
                activeTab: 'all',
                todosList: @json($todos),
                projectId: {{ $project->id }},
                formDescription: '',
                formDurationValue: 1,
                formDurationType: 'days',
                formRecurrenceType: 'none',
                formRecurrenceDays: [],
                formRecurrenceDates: [],
                formRecurrenceYearlyDates: [],
                yearlyPickMonth: '01',
                yearlyPickDay: '01',
                isSubmitting: false,
                toastMessage: '',

                // Edit Modal state
                editModalOpen: false,
                editingTodo: null,
                editDescription: '',
                editDurationValue: 1,
                editDurationType: 'days',
                editRecurrenceType: 'none',
                editRecurrenceDays: [],
                editRecurrenceDates: [],
                editRecurrenceYearlyDates: [],
                editYearlyPickMonth: '01',
                editYearlyPickDay: '01',
                isUpdating: false,

                monthNames: ['January','February','March','April','May','June','July','August','September','October','November','December'],

                weekDays: [
                    { label: 'Mon', value: 'monday' },
                    { label: 'Tue', value: 'tuesday' },
                    { label: 'Wed', value: 'wednesday' },
                    { label: 'Thu', value: 'thursday' },
                    { label: 'Fri', value: 'friday' },
                    { label: 'Sat', value: 'saturday' },
                    { label: 'Sun', value: 'sunday' },
                ],

                get pendingCount() {
                    return this.todosList.filter(t => t.status !== 'completed').length;
                },

                get completedCount() {
                    return this.todosList.filter(t => t.status === 'completed').length;
                },

                get filteredTodos() {
                    if (this.activeTab === 'pending') {
                        return this.todosList.filter(t => t.status !== 'completed');
                    } else if (this.activeTab === 'completed') {
                        return this.todosList.filter(t => t.status === 'completed');
                    }
                    return this.todosList;
                },

                toggleDay(day) {
                    if (this.formRecurrenceDays.includes(day)) {
                        this.formRecurrenceDays = this.formRecurrenceDays.filter(d => d !== day);
                    } else {
                        this.formRecurrenceDays.push(day);
                    }
                },

                toggleDate(date) {
                    if (this.formRecurrenceDates.includes(date)) {
                        this.formRecurrenceDates = this.formRecurrenceDates.filter(d => d !== date);
                    } else {
                        this.formRecurrenceDates.push(date);
                    }
                },

                recurrenceSummary() {
                    const type = this.formRecurrenceType;
                    if (type === 'daily') return 'Repeats every day';
                    if (type === 'yearly') {
                        if (this.formRecurrenceYearlyDates.length === 0) return 'Yearly — add dates';
                        return 'Every year on: ' + this.formRecurrenceYearlyDates.map(d => this.formatYearlyDate(d)).join(', ');
                    }
                    if (type === 'weekly') {
                        if (this.formRecurrenceDays.length === 0) return 'Weekly — select days';
                        const labels = this.formRecurrenceDays.map(d => d.charAt(0).toUpperCase() + d.slice(1, 3));
                        return 'Every week on: ' + labels.join(', ');
                    }
                    if (type === 'monthly') {
                        if (this.formRecurrenceDates.length === 0) return 'Monthly — select dates';
                        const sorted = [...this.formRecurrenceDates].sort((a, b) => a - b);
                        return 'Every month on: ' + sorted.map(d => this.ordinal(d)).join(', ');
                    }
                    return '';
                },

                ordinal(n) {
                    const s = ['th','st','nd','rd'];
                    const v = n % 100;
                    return n + (s[(v - 20) % 10] || s[v] || s[0]);
                },

                getRecurrenceLabel(todo) {
                    const type = todo.recurrence_type;
                    if (type === 'daily') return 'Daily';
                    if (type === 'yearly') {
                        const yd = todo.recurrence_yearly_dates || [];
                        if (!yd.length) return 'Yearly';
                        return 'Yearly: ' + yd.map(d => this.formatYearlyDate(d)).join(', ');
                    }
                    if (type === 'weekly') {
                        const days = todo.recurrence_days || [];
                        if (!days.length) return 'Weekly';
                        return 'Weekly: ' + days.map(d => d.charAt(0).toUpperCase() + d.slice(1, 3)).join(', ');
                    }
                    if (type === 'monthly') {
                        const dates = todo.recurrence_dates || [];
                        if (!dates.length) return 'Monthly';
                        return 'Monthly: ' + [...dates].sort((a,b)=>a-b).map(d => this.ordinal(d)).join(', ');
                    }
                    return type;
                },

                addYearlyDate() {
                    const val = this.yearlyPickMonth + '-' + this.yearlyPickDay;
                    if (!this.formRecurrenceYearlyDates.includes(val)) {
                        this.formRecurrenceYearlyDates.push(val);
                        this.formRecurrenceYearlyDates.sort();
                    }
                },

                removeYearlyDate(val) {
                    this.formRecurrenceYearlyDates = this.formRecurrenceYearlyDates.filter(d => d !== val);
                },

                formatYearlyDate(mmdd) {
                    if (!mmdd) return '';
                    const [mm, dd] = mmdd.split('-');
                    const mn = this.monthNames[parseInt(mm, 10) - 1] || mm;
                    return mn.slice(0, 3) + ' ' + parseInt(dd, 10);
                },

                async submitNewTodo() {
                    if (!this.formDescription.trim()) return;
                    if (this.formRecurrenceType === 'weekly' && this.formRecurrenceDays.length === 0) return;
                    if (this.formRecurrenceType === 'monthly' && this.formRecurrenceDates.length === 0) return;
                    if (this.formRecurrenceType === 'yearly' && this.formRecurrenceYearlyDates.length === 0) return;
                    this.isSubmitting = true;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const payload = {
                            description: this.formDescription,
                            duration_value: this.formDurationValue,
                            duration_type: this.formDurationType,
                            recurrence_type: this.formRecurrenceType,
                            recurrence_days: this.formRecurrenceType === 'weekly' ? this.formRecurrenceDays : null,
                            recurrence_dates: this.formRecurrenceType === 'monthly' ? this.formRecurrenceDates : null,
                            recurrence_yearly_dates: this.formRecurrenceType === 'yearly' ? this.formRecurrenceYearlyDates : null,
                        };

                        const response = await fetch('{{ route('crm-projects.todos.store', $project->id) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            this.todosList.unshift(data.todo);
                            // Reset form
                            this.formDescription = '';
                            this.formDurationValue = 1;
                            this.formDurationType = 'days';
                            this.formRecurrenceType = 'none';
                            this.formRecurrenceDays = [];
                            this.formRecurrenceDates = [];
                            this.formRecurrenceYearlyDates = [];
                            this.showToast('To-Do task added successfully!');
                        } else {
                            alert(data.message || 'Failed to create task.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('An error occurred while creating the task.');
                    } finally {
                        this.isSubmitting = false;
                    }
                },

                openEditModal(todo) {
                    this.editingTodo = todo;
                    this.editDescription = todo.description || '';
                    this.editDurationValue = todo.duration_value || 1;
                    this.editDurationType = todo.duration_type || 'days';
                    this.editRecurrenceType = todo.recurrence_type || 'none';
                    this.editRecurrenceDays = Array.isArray(todo.recurrence_days) ? [...todo.recurrence_days] : [];
                    this.editRecurrenceDates = Array.isArray(todo.recurrence_dates) ? [...todo.recurrence_dates] : [];
                    this.editRecurrenceYearlyDates = Array.isArray(todo.recurrence_yearly_dates) ? [...todo.recurrence_yearly_dates] : [];
                    this.editYearlyPickMonth = '01';
                    this.editYearlyPickDay = '01';
                    this.editModalOpen = true;
                },

                closeEditModal() {
                    this.editModalOpen = false;
                    this.editingTodo = null;
                },

                editToggleDay(day) {
                    if (this.editRecurrenceDays.includes(day)) {
                        this.editRecurrenceDays = this.editRecurrenceDays.filter(d => d !== day);
                    } else {
                        this.editRecurrenceDays.push(day);
                    }
                },

                editToggleDate(date) {
                    if (this.editRecurrenceDates.includes(date)) {
                        this.editRecurrenceDates = this.editRecurrenceDates.filter(d => d !== date);
                    } else {
                        this.editRecurrenceDates.push(date);
                    }
                },

                editAddYearlyDate() {
                    const val = this.editYearlyPickMonth + '-' + this.editYearlyPickDay;
                    if (!this.editRecurrenceYearlyDates.includes(val)) {
                        this.editRecurrenceYearlyDates.push(val);
                        this.editRecurrenceYearlyDates.sort();
                    }
                },

                editRemoveYearlyDate(val) {
                    this.editRecurrenceYearlyDates = this.editRecurrenceYearlyDates.filter(d => d !== val);
                },

                editRecurrenceSummary() {
                    const type = this.editRecurrenceType;
                    if (type === 'daily') return 'Repeats every day';
                    if (type === 'yearly') {
                        if (this.editRecurrenceYearlyDates.length === 0) return 'Yearly — add dates';
                        return 'Every year on: ' + this.editRecurrenceYearlyDates.map(d => this.formatYearlyDate(d)).join(', ');
                    }
                    if (type === 'weekly') {
                        if (this.editRecurrenceDays.length === 0) return 'Weekly — select days';
                        const labels = this.editRecurrenceDays.map(d => d.charAt(0).toUpperCase() + d.slice(1, 3));
                        return 'Every week on: ' + labels.join(', ');
                    }
                    if (type === 'monthly') {
                        if (this.editRecurrenceDates.length === 0) return 'Monthly — select dates';
                        const sorted = [...this.editRecurrenceDates].sort((a, b) => a - b);
                        return 'Every month on: ' + sorted.map(d => this.ordinal(d)).join(', ');
                    }
                    return '';
                },

                async submitUpdateTodo() {
                    if (!this.editingTodo || !this.editDescription.trim()) return;
                    if (this.editRecurrenceType === 'weekly' && this.editRecurrenceDays.length === 0) return;
                    if (this.editRecurrenceType === 'monthly' && this.editRecurrenceDates.length === 0) return;
                    if (this.editRecurrenceType === 'yearly' && this.editRecurrenceYearlyDates.length === 0) return;
                    this.isUpdating = true;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const payload = {
                            description: this.editDescription,
                            duration_value: this.editDurationValue,
                            duration_type: this.editDurationType,
                            recurrence_type: this.editRecurrenceType,
                            recurrence_days: this.editRecurrenceType === 'weekly' ? this.editRecurrenceDays : null,
                            recurrence_dates: this.editRecurrenceType === 'monthly' ? this.editRecurrenceDates : null,
                            recurrence_yearly_dates: this.editRecurrenceType === 'yearly' ? this.editRecurrenceYearlyDates : null,
                        };

                        const updateUrl = '{{ route('crm-projects.todos.update', ['project' => $project->id, 'todo' => '__ID__']) }}'.replace('__ID__', this.editingTodo.id);
                        const response = await fetch(updateUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            const idx = this.todosList.findIndex(t => t.id === this.editingTodo.id);
                            if (idx !== -1) {
                                this.todosList[idx] = data.todo;
                            }
                            this.showToast('To-Do task updated successfully!');
                            this.closeEditModal();
                        } else {
                            alert(data.message || 'Failed to update task.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('An error occurred while updating the task.');
                    } finally {
                        this.isUpdating = false;
                    }
                },

                async toggleStatus(todo) {
                    const originalStatus = todo.status;
                    const nextStatus = originalStatus === 'completed' ? 'pending' : 'completed';
                    
                    // Optimistic update
                    todo.status = nextStatus;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const toggleUrl = '{{ route('crm-projects.todos.toggle', ['project' => $project->id, 'todo' => '__ID__']) }}'.replace('__ID__', todo.id);
                        const response = await fetch(toggleUrl, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ status: nextStatus })
                        });

                        if (!response.ok) {
                            todo.status = originalStatus;
                        }
                    } catch (e) {
                        console.error(e);
                        todo.status = originalStatus;
                    }
                },

                async deleteItem(todoId) {
                    if (!confirm('Are you sure you want to delete this task?')) return;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const deleteUrl = '{{ route('crm-projects.todos.destroy', ['project' => $project->id, 'todo' => '__ID__']) }}'.replace('__ID__', todoId);
                        const response = await fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        if (response.ok) {
                            this.todosList = this.todosList.filter(t => t.id !== todoId);
                            this.showToast('Task deleted successfully.');
                        } else {
                            alert('Could not delete task.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Error deleting task.');
                    }
                },

                showToast(msg) {
                    this.toastMessage = msg;
                    setTimeout(() => {
                        this.toastMessage = '';
                    }, 3500);
                },

                formatDate(isoString) {
                    if (!isoString) return '';
                    const d = new Date(isoString);
                    return d.toLocaleString(undefined, {
                        month: 'short',
                        day: 'numeric',
                        year: 'numeric',
                        hour: 'numeric',
                        minute: '2-digit',
                        hour12: true
                    });
                },

                getUserInitials(name) {
                    if (!name) return 'U';
                    return name.split(' ').filter(n => n.length > 0).map(n => n[0]).slice(0, 2).join('').toUpperCase();
                },

                capitalize(str) {
                    if (!str) return '';
                    return str.charAt(0).toUpperCase() + str.slice(1);
                }
            };
        }

    </script>
</x-app-layout>
