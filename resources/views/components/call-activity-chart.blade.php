@props(['labels' => [], 'values' => [], 'calls' => [], 'followUps' => [], 'events' => []])

<div x-data="{
    filter: 'month',
    labels: {{ json_encode($labels) }},
    values: {{ json_encode($values) }},
    calls: {{ json_encode($calls) }},
    followUps: {{ json_encode($followUps) }},
    events: {{ json_encode($events) }},
    loading: false,
    
    // Calendar State
    showCalendar: false,
    selectedDate: null,
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
    
    prevMonth() {
        if (this.currentMonth === 0) {
            this.currentMonth = 11;
            this.currentYear--;
        } else {
            this.currentMonth--;
        }
        this.updateCalendar();
    },
    
    nextMonth() {
        if (this.currentMonth === 11) {
            this.currentMonth = 0;
            this.currentYear++;
        } else {
            this.currentMonth++;
        }
        this.updateCalendar();
    },
    
    selectDate(date) {
        if (!date) return;
        this.selectedDate = date;
        this.showCalendar = false;
        this.setFilter('date', date);
    },
    
    get maxValue() { 
        return Math.max(...this.values, 10); 
    },
    
    get subtitle() {
        if (this.filter === 'date') return `Activity for ${this.calendarMonthName} ${this.currentYear}`;
        return {
            'week': 'Weekly call volume',
            'month': 'Monthly call volume',
            'year': 'Yearly call volume'
        }[this.filter];
    },
    
    async setFilter(f, date = null) {
        if (this.loading) return;
        this.filter = f;
        this.loading = true;
        
        let targetDate = date;
        if (f === 'date' && !targetDate) {
            targetDate = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-01`;
        }

        try {
            let url = `{{ route('dashboard.call-activity') }}?filter=${f}`;
            if (targetDate) url += `&date=${targetDate}`;
            const res = await fetch(url);
            const data = await res.json();
            this.labels = data.labels;
            this.values = data.values;
            this.calls = data.calls || [];
            this.followUps = data.followUps || [];
            this.events = data.events || [];
            
            if (f === 'date' && targetDate) {
                // If we fetched for a specific date, update the current month/year view
                const d = new Date(targetDate);
                this.currentYear = d.getFullYear();
                this.currentMonth = d.getMonth();
                this.updateCalendar();
            }
        } catch (e) {
            console.error('Error fetching chart data', e);
        } finally {
            this.loading = false;
        }
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

    getDataForDay(full) {
        if (!full) return { calls: 0, followUps: 0, events: 0 };
        let index = this.labels.indexOf(full);
        if (index === -1) return { calls: 0, followUps: 0, events: 0 };
        
        return {
            calls: this.calls[index] || 0,
            followUps: this.followUps[index] || 0,
            events: this.events[index] || 0
        };
    },
}" class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Call Activity</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="subtitle"></p>
        </div>
        <div class="flex items-center gap-2.5 relative">
            <template x-for="f in ['date', 'week', 'month', 'year']">
                <button @click="setFilter(f)"
                    :class="(filter === f)
                        ? 'bg-indigo-600/10 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 border-indigo-600/30 font-bold' 
                        : 'bg-white/40 dark:bg-slate-800/40 text-gray-400 dark:text-gray-500 border-gray-100 dark:border-slate-700/50 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800' "
                    class="px-4 py-1.5 text-[11px] rounded-xl border transition-all capitalize flex items-center gap-2 group overflow-hidden relative">
                    <span x-text="f"></span>
                </button>
            </template>

            <!-- Month navigation for Calendar View -->
            <div x-show="filter === 'date'"
                class="flex items-center gap-2 ml-4 animate-in fade-in slide-in-from-right-4">
                <button @click="changeMonth(-1)"
                    class="p-1 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg text-gray-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
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
    <div class="relative min-h-[14rem]">
        <!-- Loading Spinner -->
        <div x-show="loading" x-transition:enter="transition opacity-0 duration-300"
            x-transition:enter-end="opacity-100"
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
        <div x-show="filter === 'date'" class="h-full animate-in fade-in duration-500 overflow-hidden">
            <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px;"
                class="bg-gray-100 dark:bg-slate-700/50 rounded-lg overflow-hidden border border-gray-100 dark:border-slate-700/50">
                <!-- Headers -->
                <template x-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']">
                    <div
                        class="bg-gray-50 dark:bg-slate-800/80 py-2 text-center border-b border-gray-100 dark:border-slate-700/50">
                        <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest"
                            x-text="day"></span>
                    </div>
                </template>

                <!-- Day Cells -->
                <template x-for="(item, idx) in calendarDays" :key="idx">
                    <div
                        class="h-[80px] bg-white dark:bg-slate-800 transition-colors hover:bg-gray-50 dark:hover:bg-slate-700/30 p-1.5 relative overflow-hidden border-b border-r border-gray-100 dark:border-slate-700/50">
                        <div class="flex items-center justify-between mb-0.5" x-show="item.day">
                            <span :class="item.full === new Date().toISOString().split('T')[0] 
                                    ? 'bg-indigo-600 text-white font-bold w-4 h-4 rounded-full flex items-center justify-center text-[10px]' 
                                    : 'text-gray-400 dark:text-slate-500 text-[10px] font-medium'"
                                x-text="item.day"></span>
                        </div>

                        <!-- Activity Indicators -->
                        <div x-show="item.day" class="space-y-1 mt-0.5 flex flex-col items-start">
                            <!-- Calls -->
                            <template x-if="getDataForDay(item.full).calls > 0">
                                <div
                                    class="w-full max-w-full bg-indigo-500/5 border-l-2 border-indigo-500 rounded-sm px-1 py-0 relative group overflow-hidden">
                                    <div class="flex items-center gap-1 truncate">
                                        <div class="w-1 h-1 rounded-full bg-indigo-500 animate-pulse flex-shrink-0">
                                        </div>
                                        <p
                                            class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap truncate leading-none">
                                            <span x-text="getDataForDay(item.full).calls"></span> Calls
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <!-- Follow-ups -->
                            <template x-if="getDataForDay(item.full).followUps > 0">
                                <div
                                    class="w-full max-w-full bg-emerald-500/5 border-l-2 border-emerald-500 rounded-sm px-1 py-0 relative group overflow-hidden">
                                    <div class="flex items-center gap-1 truncate">
                                        <div class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse flex-shrink-0">
                                        </div>
                                        <p
                                            class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400 whitespace-nowrap truncate leading-none">
                                            <span x-text="getDataForDay(item.full).followUps"></span> Follow-ups
                                        </p>
                                    </div>
                                </div>
                            </template>

                            <!-- Events -->
                            <template x-if="getDataForDay(item.full).events > 0">
                                <div
                                    class="w-full max-w-full bg-amber-500/5 border-l-2 border-amber-500 rounded-sm px-1 py-0 relative group overflow-hidden">
                                    <div class="flex items-center gap-1 truncate">
                                        <div class="w-1 h-1 rounded-full bg-amber-500 animate-pulse flex-shrink-0">
                                        </div>
                                        <p
                                            class="text-[11px] font-bold text-amber-600 dark:text-amber-400 whitespace-nowrap truncate leading-none">
                                            <span x-text="getDataForDay(item.full).events"></span> Events
                                        </p>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Bars (Only visible when filter !== 'date') -->
        <div x-show="filter !== 'date'" class="flex items-end justify-between h-48 gap-3">
            <template x-for="(value, index) in values" :key="index">
                <div class="flex flex-col items-center flex-1">
                    <div class="w-full bg-indigo-100 dark:bg-indigo-900/30 rounded-t-lg relative group cursor-pointer transition-all hover:bg-indigo-200 dark:hover:bg-indigo-900/50"
                        :style="`height: ${Math.max((value / maxValue) * 100 * 1.5, 10)}px; min-height: 10px;` ">
                        <!-- Progress Bar (The dark blue part) -->
                        <div
                            class="absolute bottom-0 left-0 right-0 bg-indigo-600 rounded-t-lg transition-all duration-300 h-0 group-hover:h-full">
                        </div>

                        <!-- Tooltip on hover -->
                        <div
                            class="absolute -top-16 left-1/2 -translate-x-1/2 px-3 py-2 bg-gray-900 dark:bg-slate-900 text-white text-[10px] rounded-lg opacity-0 group-hover:opacity-100 transition-all pointer-events-none whitespace-nowrap z-20 shadow-xl border border-white/10 scale-95 group-hover:scale-100">
                            <div class="space-y-1">
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-gray-400 font-medium">Calls:</span>
                                    <span class="font-bold text-indigo-400" x-text="calls[index] || 0"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-gray-400 font-medium">Follow-ups:</span>
                                    <span class="font-bold text-emerald-400" x-text="followUps[index] || 0"></span>
                                </div>
                                <div class="flex items-center justify-between gap-4">
                                    <span class="text-gray-400 font-medium">Events:</span>
                                    <span class="font-bold text-amber-400" x-text="events[index] || 0"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-text="labels[index]"></span>
                </div>
            </template>
        </div>
    </div>
</div>