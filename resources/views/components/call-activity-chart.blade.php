@props(['labels' => [], 'values' => []])

<div x-data="{
    filter: 'month',
    labels: {{ json_encode($labels) }},
    values: {{ json_encode($values) }},
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
        if (this.filter === 'date') return `Activity for 7 days ending ${this.selectedDate}`;
        return {
            'week': 'Weekly call volume',
            'month': 'Monthly call volume',
            'year': 'Yearly call volume'
        }[this.filter];
    },
    
    async setFilter(f, date = null) {
        if ((this.filter === f && !date) || this.loading) return;
        this.filter = f;
        this.loading = true;
        try {
            let url = `{{ route('dashboard.call-activity') }}?filter=${f}`;
            if (date) url += `&date=${date}`;
            const res = await fetch(url);
            const data = await res.json();
            this.labels = data.labels;
            this.values = data.values;
        } catch (e) {
            console.error('Error fetching chart data', e);
        } finally {
            this.loading = false;
        }
    }
}" class="bg-white dark:bg-slate-800 rounded-2xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Call Activity</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="subtitle"></p>
        </div>
        <div class="flex items-center gap-2.5 relative">
            <template x-for="f in ['date', 'week', 'month', 'year']">
                <button @click="f === 'date' ? (showCalendar = !showCalendar) : (selectedDate = null, setFilter(f))"
                    :class="(filter === f || (f === 'date' && filter === 'date'))
                        ? 'bg-white dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border-indigo-600/30' 
                        : 'bg-white/40 dark:bg-slate-800/40 text-gray-400 dark:text-gray-500 border-gray-100 dark:border-slate-700/50 hover:text-gray-900 dark:hover:text-white hover:bg-white dark:hover:bg-slate-800 hover:shadow-md hover:-translate-y-0.5'"
                    class="px-4 py-1.5 text-[11px] font-bold rounded-xl border transition-all capitalize shadow-sm flex items-center gap-2 group overflow-hidden relative">
                    <!-- Background shine for hover -->
                    <div
                        class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity">
                    </div>

                    <span x-show="f !== 'date' || !selectedDate" x-text="f"></span>
                    <template x-if="f === 'date' && selectedDate">
                        <div class="flex items-center gap-1.5 animate-in fade-in slide-in-from-left-1 duration-300">
                            <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 animate-pulse"></div>
                            <span
                                x-text="new Date(selectedDate).toLocaleDateString('en-US', {month: 'short', day: 'numeric'})"></span>
                        </div>
                    </template>
                </button>
            </template>

            <!-- Custom Calendar Modal (Wrike Styled) -->
            <div x-show="showCalendar" @click.away="showCalendar = false"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 top-10 z-50 w-72 bg-white dark:bg-slate-800 rounded-xl shadow-2xl border border-gray-100 dark:border-slate-700 p-4"
                style="display: none;">

                <!-- Calendar Header -->
                <div class="flex items-center justify-between mb-4">
                    <button @click="prevMonth"
                        class="p-1 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                            </path>
                        </svg>
                    </button>
                    <div class="text-sm font-bold text-gray-900 dark:text-white"
                        x-text="`${calendarMonthName} ${currentYear}`"></div>
                    <button @click="nextMonth"
                        class="p-1 hover:bg-gray-100 dark:hover:bg-slate-700 rounded-lg text-gray-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </button>
                </div>

                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-1 mb-2">
                    <template x-for="day in ['S','M','T','W','T','F','S']">
                        <div class="text-[10px] font-bold text-gray-400 dark:text-slate-500 text-center uppercase"
                            x-text="day"></div>
                    </template>
                </div>

                <!-- Days Grid -->
                <div class="grid grid-cols-7 gap-1">
                    <template x-for="item in calendarDays">
                        <div class="aspect-square flex items-center justify-center">
                            <button x-show="item.day" @click="selectDate(item.full)" :class="{
                                    'bg-indigo-600 text-white': selectedDate === item.full,
                                    'hover:bg-indigo-50 dark:hover:bg-indigo-900/30 text-gray-700 dark:text-gray-300': selectedDate !== item.full,
                                    'ring-1 ring-indigo-600 ring-inset': item.full === new Date().toISOString().split('T')[0]
                                }" class="w-8 h-8 text-[11px] font-medium rounded-lg transition-all"
                                x-text="item.day"></button>
                        </div>
                    </template>
                </div>

                <!-- Calendar Footer -->
                <div
                    class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-center px-1">
                    <button @click="selectDate(new Date().toISOString().split('T')[0]); showCalendar = false"
                        class="text-[10px] font-bold text-indigo-600 hover:text-indigo-700 uppercase tracking-wider">Today</button>
                    <button @click="showCalendar = false"
                        class="text-[10px] font-bold text-gray-400 hover:text-gray-600 uppercase tracking-wider">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="relative h-48">
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

        <!-- Bars -->
        <div class="flex items-end justify-between h-full gap-3">
            <template x-for="(value, index) in values" :key="index">
                <div class="flex flex-col items-center flex-1">
                    <div class="w-full bg-indigo-100 dark:bg-indigo-900/30 rounded-t-lg relative group cursor-pointer transition-all hover:bg-indigo-200 dark:hover:bg-indigo-900/50"
                        :style="`height: ${Math.max((value / maxValue) * 100 * 1.5, 10)}px; min-height: 10px;`">
                        <!-- Progress Bar (The dark blue part) -->
                        <div
                            class="absolute bottom-0 left-0 right-0 bg-indigo-600 rounded-t-lg transition-all duration-300 h-0 group-hover:h-full">
                        </div>

                        <!-- Tooltip on hover -->
                        <div
                            class="absolute -top-8 left-1/2 -translate-x-1/2 px-2 py-1 bg-gray-900 dark:bg-slate-700 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap z-20">
                            <span x-text="value.toLocaleString()"></span> Calls
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-2" x-text="labels[index]"></span>
                </div>
            </template>
        </div>
    </div>
</div>