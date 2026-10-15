<x-app-layout>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <div class="text-xs text-gray-400 dark:text-slate-500 mb-1.5 flex items-center gap-1.5">
                <a href="{{ route('projects.index') }}"
                    class="hover:text-indigo-600 dark:hover:text-indigo-400">Projects</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
                <span>Create New Project</span>
            </div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Project</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Fill in client and project financial details</p>
        </div>
        <a href="{{ route('projects.index') }}"
            class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cancel
        </a>
    </div>

    @php
        $firstTax = $settings->available_taxes[0] ?? ['name' => 'GST', 'rate' => 18];
        $firstSymbol = $settings->available_currencies[0]['symbol'] ?? '₹';
        $defaultTaxRate = old('tax_rate', $firstTax['rate']);
        $defaultTaxName = old('tax_name', $firstTax['name']);
    @endphp
    <form action="{{ route('projects.store') }}" method="POST" novalidate x-data="{
        baseAmount: '{{ old('base_amount', '') }}',
        taxRate: {{ $defaultTaxRate }},
        taxType: '{{ $defaultTaxName }}',
        currencySymbol: '{{ old('project_currency', $firstSymbol) }}',
        get totalAmount() {
            let base = parseFloat(this.baseAmount) || 0;
            let tax  = parseFloat(this.taxRate) || 0;
            return (base + base * tax / 100).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }">
        @csrf

        <div class="space-y-8">
            {{-- Global Validation Errors --}}
            @if ($errors->any())
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-2xl flex items-center gap-3">
                    <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm font-bold text-red-600 dark:text-red-400">Please correct the highlighted errors before saving.</span>
                </div>
            @endif

            {{-- ── Section 1: Client Information ── --}}
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-7">

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">
                        Client Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="client_name" value="{{ old('client_name') }}" placeholder="e.g. Sourav Das" required
                        oninvalid="this.setCustomValidity('This field is mandatory.')"
                        oninput="this.setCustomValidity('')"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                    @error('client_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">
                        Client Email <span class="text-red-500">*</span>
                    </label>
                    <input type="email" name="client_email" value="{{ old('client_email') }}"
                        placeholder="e.g. client@company.com" required
                        oninvalid="this.setCustomValidity('This field is mandatory.')"
                        oninput="this.setCustomValidity('')"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                    @error('client_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Phone
                        Number <span class="text-red-500">*</span></label>
                    <input type="text" name="client_phone" value="{{ old('client_phone') }}"
                        placeholder="+91 98765 43210" required
                        oninvalid="this.setCustomValidity('This field is mandatory.')"
                        oninput="this.setCustomValidity('')"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                    @error('client_phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Alternate
                        Phone</label>
                    <input type="text" name="customer_alt_phone" value="{{ old('customer_alt_phone') }}"
                        placeholder="+91 …"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Company
                        Name <span class="text-red-500">*</span></label>
                    <input type="text" name="customer_company" value="{{ old('customer_company') }}"
                        placeholder="e.g. Basak Textiles Ltd" required
                        oninvalid="this.setCustomValidity('This field is mandatory.')"
                        oninput="this.setCustomValidity('')"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                    @error('customer_company') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">GST
                        Number <span class="text-red-500">*</span></label>
                    <input type="text" name="client_gst" value="{{ old('client_gst') }}"
                        placeholder="e.g. 19ABCDE1234F1Z5" required
                        oninvalid="this.setCustomValidity('This field is mandatory.')"
                        oninput="this.setCustomValidity('')"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm font-mono text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                    @error('client_gst') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5">Client
                        Address <span class="text-red-500">*</span></label>
                    <textarea name="client_address" rows="2" placeholder="Full address including city, state and PIN…" required
                        oninvalid="this.setCustomValidity('This field is mandatory.')"
                        oninput="this.setCustomValidity('')"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow resize-none">{{ old('client_address') }}</textarea>
                    @error('client_address') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- ── Section 2: Project Details ── --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2.5">
                <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Project Details
                </h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-7">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase">
                        Project Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="project_name" value="{{ old('project_name') }}"
                        placeholder="e.g. Basak Bedding SEO"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                    @error('project_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label
                        class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase">Currency</label>
                    <select name="project_currency" @change="currencySymbol = $event.target.value"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @foreach($settings->available_currencies as $currency)
                            @php $val = $currency['symbol'];
                            $label = $currency['name'] . ' (' . $currency['symbol'] . ')'; @endphp
                            <option value="{{ $val }}" {{ old('project_currency') === $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                        class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        @foreach(['Draft', 'Active', 'On Hold', 'Completed'] as $statusOption)
                            <option value="{{ $statusOption }}" {{ old('status') === $statusOption ? 'selected' : '' }}>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- ── Section 2.5: Tax Calculation Method ── --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div
                class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2.5 text-indigo-600 dark:text-indigo-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h2 class="text-xs font-bold uppercase tracking-wider">Tax Calculation Method</h2>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Dynamic taxes from codec_settings --}}
                    @foreach($settings->available_taxes as $tax)
                        <button type="button" @click="taxRate = {{ $tax['rate'] }}; taxType = '{{ $tax['name'] }}'"
                            :class="taxRate == {{ $tax['rate'] }} && taxType == '{{ $tax['name'] }}' ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300' : 'border-gray-100 dark:border-slate-700 text-gray-500 dark:text-slate-400 hover:border-indigo-200 dark:hover:border-indigo-800'"
                            class="relative group p-6 border-2 rounded-2xl transition-all duration-300 flex flex-col items-center gap-2 hover:shadow-lg active:scale-95 overflow-hidden">
                            <div x-show="taxRate == {{ $tax['rate'] }} && taxType == '{{ $tax['name'] }}'"
                                class="absolute top-0 right-0 w-8 h-8 bg-indigo-600 dark:bg-indigo-500 rounded-bl-2xl flex items-center justify-center text-white"
                                x-transition>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <span class="text-sm font-black uppercase tracking-widest">{{ $tax['name'] }}
                                ({{ $tax['rate'] }}%)</span>
                            <span class="text-[10px] font-bold opacity-60 uppercase">Calculated Tax</span>
                        </button>
                    @endforeach
                    {{-- Always show No Tax option --}}
                    <button type="button" @click="taxRate = 0; taxType = 'No Tax'"
                        :class="taxRate == 0 ? 'border-indigo-600 dark:border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300' : 'border-gray-100 dark:border-slate-700 text-gray-500 dark:text-slate-400 hover:border-indigo-200 dark:hover:border-indigo-800'"
                        class="relative group p-6 border-2 rounded-2xl transition-all duration-300 flex flex-col items-center gap-2 hover:shadow-lg active:scale-95 overflow-hidden">
                        <div x-show="taxRate == 0"
                            class="absolute top-0 right-0 w-8 h-8 bg-indigo-600 dark:bg-indigo-500 rounded-bl-2xl flex items-center justify-center text-white"
                            x-transition>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="text-sm font-black uppercase tracking-widest">No Tax (0%)</span>
                        <span class="text-[10px] font-bold opacity-60 uppercase">Tax Exempt</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Section 3: Financial Details ── --}}
        <div
            class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2.5">
                <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                <h2 class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Financial
                    Details</h2>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-x-8 gap-y-7">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase">
                        Base Amount (excl. tax) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 font-semibold text-sm"
                            x-text="currencySymbol"></span>
                        <input type="number" name="base_amount" x-model="baseAmount" step="0.01" min="0"
                            placeholder="0.00"
                            class="w-full pl-8 pr-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                    </div>
                    @error('base_amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase">
                        <span x-text="taxType"></span> Rate (%) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="hidden" name="tax_name" :value="taxType">
                        <input type="number" name="tax_rate" x-model="taxRate" step="0.01" min="0" max="100"
                            @input="let match = {{ json_encode($settings->available_taxes) }}.find(t => parseFloat(t.rate) === parseFloat(taxRate)); if (match) { taxType = match.name; } else if (parseFloat(taxRate) === 0) { taxType = 'No Tax'; }"
                            class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-700 border border-gray-200 dark:border-slate-600 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-shadow">
                        <span
                            class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 dark:text-slate-500 text-sm font-semibold">%</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1.5 uppercase">
                        Total Amount
                        <span
                            class="text-emerald-600 dark:text-emerald-400 font-normal normal-case">(auto-calculated)</span>
                    </label>
                    <div
                        class="w-full px-4 py-2.5 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl text-sm font-bold text-indigo-700 dark:text-indigo-300 flex items-center gap-1">
                        <span x-text="currencySymbol"></span>
                        <span x-text="totalAmount"></span>
                    </div>
                </div>
            </div>
        </div>

        <div x-data="{ 
                showSupplementary: false, 
                supplementaryCharges: [], 
                suppDesc: '', 
                suppBase: '',
                get suppTotal() {
                    let base = parseFloat(this.suppBase) || 0;
                    let taxRate = parseFloat($data.taxRate) || 0;
                    return (base * (1 + (taxRate / 100))).toFixed(2);
                },
                addCharge() {
                    if (this.suppDesc && this.suppBase) {
                        let base = parseFloat(this.suppBase) || 0;
                        let taxRate = parseFloat($data.taxRate) || 0;
                        let total = base * (1 + (taxRate / 100));
                        let gst = total - base;

                        this.supplementaryCharges.push({
                            description: this.suppDesc,
                            base_amount: base.toFixed(2),
                            gst_amount: gst.toFixed(2),
                            total_amount: total.toFixed(2)
                        });
                        this.suppDesc = '';
                        this.suppBase = '';
                        this.showSupplementary = false;
                    }
                },
                removeCharge(index) {
                    this.supplementaryCharges.splice(index, 1);
                }
            }">
            {{-- List of Added Charges --}}
            <div class="space-y-3 mb-6">
                <template x-for="(charge, index) in supplementaryCharges" :key="index">
                    <div
                        class="flex items-center justify-between p-4 bg-gray-50/50 dark:bg-slate-700 rounded-2xl border border-gray-200/50 dark:border-slate-600 group hover:shadow-md transition-all">
                        <div class="flex items-center gap-4">
                            <div
                                class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black text-lg">
                                <span x-text="currencySymbol"></span>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-bold text-gray-900 dark:text-white"
                                    x-text="charge.description"></div>
                                <div class="text-[10px] text-gray-500 dark:text-slate-400 uppercase tracking-wider">
                                    Base: <span x-text="currencySymbol"></span><span x-text="charge.base_amount"></span>
                                    + <span x-text="$data.taxType"></span>: <span x-text="currencySymbol"></span><span
                                        x-text="charge.gst_amount"></span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-6">
                            <div class="text-right">
                                <div class="text-sm font-black text-indigo-600 dark:text-indigo-400"><span
                                        x-text="currencySymbol"></span><span x-text="charge.total_amount"></span></div>
                                <div class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">Total Amount
                                </div>
                            </div>
                            <button type="button" @click="removeCharge(index)"
                                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/10 rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                        {{-- Important: Hidden inputs for form submission --}}
                        <input type="hidden" :name="'supplementary_charges['+index+'][description]'"
                            :value="charge.description">
                        <input type="hidden" :name="'supplementary_charges['+index+'][base_amount]'"
                            :value="charge.base_amount">
                        <input type="hidden" :name="'supplementary_charges['+index+'][gst_amount]'"
                            :value="charge.gst_amount">
                        <input type="hidden" :name="'supplementary_charges['+index+'][total_amount]'"
                            :value="charge.total_amount">
                    </div>
                </template>
            </div>

            {{-- Toggle Button (Matches Edit design) --}}
            <div class="mb-6">
                <button type="button" x-show="!showSupplementary" @click="showSupplementary = true"
                    class="inline-flex items-center gap-2 px-6 py-2 border-2 border-indigo-600 dark:border-indigo-500 rounded-full text-indigo-600 dark:text-indigo-400 text-xs font-black uppercase tracking-widest hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all active:scale-95 shadow-sm">
                    <span class="text-lg leading-none">+</span>
                    <span>Add Supplementary Charge</span>
                </button>
            </div>

            {{-- ── Section 4: Supplementary Charge Details ── --}}
            <div x-show="showSupplementary" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform -translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                class="bg-white dark:bg-slate-800 rounded-2xl border-2 border-dashed border-indigo-200 dark:border-indigo-900/50 overflow-hidden mb-8">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-slate-700 flex items-center gap-2.5">
                    <div class="w-2 h-2 rounded-full shadow-sm" style="background-color: #3b82f6;"></div>
                    <h2 class="text-[13px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-widest">
                        Supplementary Charge Details</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-6">
                        <div class="md:col-span-1">
                            <label
                                class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Description:</label>
                            <input type="text" x-model="suppDesc" placeholder="e.g. Server Hosting, Domain Renewal"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-shadow outline-none">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Base
                                Amount (<span x-text="currencySymbol"></span>):</label>
                            <input type="number" x-model="suppBase" step="0.01" placeholder="0.00"
                                class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-xl text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 transition-shadow outline-none">
                        </div>
                        <div>
                            <label
                                class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Total
                                (Incl. <span x-text="$data.taxType"></span>):</label>
                            <div
                                class="w-full px-4 py-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl text-sm font-bold text-blue-700 dark:text-blue-300 flex items-center gap-1">
                                <span x-text="currencySymbol"></span>
                                <span x-text="suppTotal"></span>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="addCharge()" style="background-color: #0a4a6b;"
                            class="px-6 py-2.5 hover:opacity-90 text-white text-[11px] font-bold rounded-lg transition-all shadow-sm active:scale-95">
                            Confirm & Add
                        </button>
                        <button type="button" @click="showSupplementary = false; suppDesc = ''; suppBase = '';"
                            class="px-6 py-2.5 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-[11px] font-bold rounded-lg border border-gray-200 dark:border-slate-600 transition-all active:scale-95">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Section 5: Service Renewal & Subscription (Disabled for now) ──
        <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden"
            x-data="{ hasRenewal: false }">
            ...
        </div>
        --}}

        <div class="flex items-center justify-end gap-3 mt-12 pb-12">
            <a href="{{ route('projects.index') }}"
                class="px-5 py-2.5 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                Cancel
            </a>
            <button type="submit" name="action" value="draft"
                class="px-5 py-2.5 bg-gray-100 dark:bg-slate-700 hover:bg-gray-200 dark:hover:bg-slate-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-semibold transition-colors">
                Save as Draft
            </button>
            <button type="submit" name="action" value="save"
                class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-xl shadow-indigo-600/25 flex items-center gap-2 active:scale-95 group">
                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
                Create Project Details
            </button>
        </div>
    </form>

    <script>
        // Custom script to scroll to the first invalid field when clicking submit
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('invalid', function(e) {
                    e.preventDefault();
                    const firstInvalid = form.querySelector(':invalid');
                    if (firstInvalid) {
                        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        setTimeout(() => firstInvalid.focus(), 500);
                    }
                }, true);
            });
        });
    </script>
</x-app-layout>