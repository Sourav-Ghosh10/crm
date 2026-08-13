@props(['labels' => [], 'created' => [], 'completed' => [], 'active' => []])

<div x-data="{
    filter: 'month',
    labels: {{ json_encode($labels) }},
    created: {{ json_encode($created) }},
    completed: {{ json_encode($completed) }},
    active: {{ json_encode($active) }},
    loading: false,

    // Calendar State
    currentYear: new Date().getFullYear(),
    currentMonth: new Date().getMonth(),
    calendarMonthName: '',
    calendarDays: [],

    init() {
        this.updateCalendar();
    },

    updateCalendar() {
        this.calendarMonthName = new Date(this.currentYear, this.currentMonth).toLocaleString('default', { month: 'long' });
        let firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
        let daysInMonth = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
        let days = [];
        for (let i = 0; i < firstDay; i++) days.push({ day: null });
        for (let i = 1; i <= daysInMonth; i++) {
            days.push({ 
                day: i, 
                full: `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}` 
            });
        }
        this.calendarDays = days;
    },

    changeMonth(diff) {
        this.currentMonth += diff;
        if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        } else if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        }
        this.updateCalendar();
        this.setFilter('date');
    },

    get maxValue() {
        return Math.max(...this.created, ...this.completed, ...this.active, 5);
    },

    get subtitle() {
        if (this.filter === 'date') return `Project volume for ${this.calendarMonthName} ${this.currentYear}`;
        return {
            'week': 'Weekly project volume',
            'month': 'Monthly project volume',
            'year': 'Yearly project volume'
        }[this.filter];
    },

    async setFilter(f, date = null) {
        if (this.loading) return;
        this.filter = f;
        this.loading = true;

        let targetDate = date;
        if (f === 'date' && !targetDate) {
            targetDate = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(new Date().getDate()).padStart(2, '0')}`;
        }

        try {
            let url = `{{ route('dashboard.project-progress') }}?filter=${f}`;
            if (targetDate) url += `&date=${targetDate}`;
            const res = await fetch(url);
            const data = await res.json();
            this.labels = data.labels;
            this.created = data.created;
            this.completed = data.completed;
            this.active = data.active;
        } catch (e) {
            console.error('Error fetching project progress chart data', e);
        } finally {
            this.loading = false;
        }
    },

    getDataForDay(full) {
        if (!full) return { created: 0, active: 0, completed: 0 };
        let index = this.labels.indexOf(full);
        if (index === -1) return { created: 0, active: 0, completed: 0 };
        return {
            created: this.created[index] || 0,
            active: this.active[index] || 0,
            completed: this.completed[index] || 0
        };
    }
}" class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Project Progress</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="subtitle"></p>
        </div>
        <div class="flex items-center gap-2.5">
            <template x-for="f in ['date', 'week', 'month', 'year']">
                <button @click="setFilter(f)"
                    :class="(filter === f)
                        ? 'bg-indigo-600/10 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 border-indigo-600/30 font-bold' 
                        : 'bg-white/40 dark:bg-slate-800/40 text-gray-400 dark:text-gray-500 border-gray-100 dark:border-slate-700/50 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800' "
                    class="px-4 py-1.5 text-[11px] rounded-xl border transition-all capitalize">
                    <span x-text="f === 'date' ? 'day' : f"></span>
                </button>
            </template>

            <!-- Month navigation for Calendar View -->
            <div x-show="filter === 'date'"
                class="flex items-center gap-2 ml-4">
                <button @click="changeMonth(-1)"
                    class="p-1 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <span class="text-xs font-bold text-gray-700 dark:text-gray-300 w-24 text-center"
                    x-text="`${calendarMonthName} ${currentYear}`"></span>
                <button @click="changeMonth(1)"
                    class="p-1 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Content Container -->
    <div class="relative min-h-[14rem] flex flex-col justify-end">
        <!-- Loading Spinner -->
        <div x-show="loading"
            class="absolute inset-0 z-10 bg-white/50 dark:bg-slate-800/50 flex items-center justify-center">
            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>

        <!-- Calendar Grid (Only visible when filter === 'date') -->
        <div x-show="filter === 'date'" class="w-full animate-in fade-in duration-500 overflow-hidden mb-4">
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px;"
                class="bg-gray-100 dark:bg-slate-700/50 rounded-lg overflow-hidden border border-gray-100 dark:border-slate-700/50">
                <!-- Headers -->
                <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                    <div class="bg-gray-50 dark:bg-slate-800/80 py-2 text-center border-b border-gray-100 dark:border-slate-700/50">
                        <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest" x-text="day"></span>
                    </div>
                </template>

                <!-- Day Cells -->
                <template x-for="(item, idx) in calendarDays" :key="idx">
                    <div class="h-[75px] bg-white dark:bg-slate-800 transition-colors hover:bg-gray-50 dark:hover:bg-slate-700/30 p-1.5 relative overflow-hidden border-b border-r border-gray-100 dark:border-slate-700/50">
                        <div class="flex items-center justify-between mb-0.5" x-show="item.day">
                            <span :class="item.full === new Date().toISOString().split('T')[0] 
                                    ? 'bg-indigo-600 text-white font-bold w-4 h-4 rounded-full flex items-center justify-center text-[10px]' 
                                    : 'text-gray-400 dark:text-slate-500 text-[10px] font-medium'"
                                x-text="item.day"></span>
                        </div>

                        <!-- Activity Indicators -->
                        <div x-show="item.day" class="space-y-1 mt-0.5 flex flex-col items-start overflow-hidden">
                            <!-- Created -->
                            <template x-if="getDataForDay(item.full).created > 0">
                                <div class="w-full bg-indigo-500/5 border-l-2 border-indigo-400 rounded-sm px-1 py-0 truncate">
                                    <span class="text-[9px] font-bold text-indigo-600 dark:text-indigo-400 leading-none">
                                        <span x-text="getDataForDay(item.full).created"></span> Created
                                    </span>
                                </div>
                            </template>
                            <!-- Active -->
                            <template x-if="getDataForDay(item.full).active > 0">
                                <div class="w-full bg-amber-500/5 border-l-2 border-amber-400 rounded-sm px-1 py-0 truncate">
                                    <span class="text-[9px] font-bold text-amber-600 dark:text-amber-400 leading-none">
                                        <span x-text="getDataForDay(item.full).active"></span> Active
                                    </span>
                                </div>
                            </template>
                            <!-- Completed -->
                            <template x-if="getDataForDay(item.full).completed > 0">
                                <div class="w-full bg-emerald-500/5 border-l-2 border-emerald-400 rounded-sm px-1 py-0 truncate">
                                    <span class="text-[9px] font-bold text-emerald-600 dark:text-emerald-400 leading-none">
                                        <span x-text="getDataForDay(item.full).completed"></span> Completed
                                    </span>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Progress Bar Chart (Only visible when filter !== 'date') -->
        <div x-show="filter !== 'date'" class="flex items-end justify-between h-48 gap-4 px-2 w-full">
            <template x-for="(label, index) in labels" :key="index">
                <div class="flex flex-col items-center flex-1 group relative h-40 justify-end">
                    <!-- Outer Bar (Created Projects) -->
                    <div class="w-full bg-indigo-100/80 dark:bg-indigo-900/20 rounded-t-lg transition-all duration-300 relative flex items-end group cursor-pointer hover:bg-indigo-200/50 dark:hover:bg-indigo-900/40 h-40 overflow-hidden"
                        :style="`height: ${Math.max((created[index] / maxValue) * 100, 5)}%; min-height: 10px;` ">
                        
                        <!-- Hover Animation (slides up from bottom) -->
                        <div class="absolute bottom-0 left-0 right-0 bg-indigo-600 rounded-t-lg transition-all duration-300 h-0 group-hover:h-full z-20"></div>

                        <!-- Completed Projects Bar (Inner Fill) -->
                        <div class="w-full bg-indigo-600/70 dark:bg-indigo-500/30 rounded-t-lg transition-all duration-500 z-10"
                            :style="`height: ${created[index] > 0 ? (completed[index] / created[index]) * 100 : 0}%;` ">
                        </div>

                        <!-- Tooltip inside the relative outer bar -->
                        <div class="absolute -top-20 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-all duration-200 pointer-events-none bg-gray-900 dark:bg-slate-900 text-white text-[10px] rounded-lg px-3 py-2 z-30 shadow-xl border border-white/10 whitespace-nowrap scale-95 group-hover:scale-100">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-gray-400 font-medium">Created:</span>
                                    <span class="font-bold text-indigo-400" x-text="created[index]"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-gray-400 font-medium">Active:</span>
                                    <span class="font-bold text-amber-400" x-text="active[index]"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-gray-400 font-medium">Completed:</span>
                                    <span class="font-bold text-emerald-400" x-text="completed[index]"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-text="label"></span>
                </div>
            </template>
        </div>
        
        <!-- Legend -->
        <div class="flex items-center justify-center gap-6 mt-6 pt-4 border-t border-gray-100 dark:border-slate-700/50 w-full">
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-sm"></div>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Created Projects</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-amber-500 rounded-sm"></div>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Active Projects</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-3 h-3 bg-indigo-600 dark:bg-indigo-500 rounded-sm"></div>
                <span class="text-xs font-semibold text-gray-600 dark:text-gray-400">Completed Projects</span>
            </div>
        </div>
    </div>
</div>
