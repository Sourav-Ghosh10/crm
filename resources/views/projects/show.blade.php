<x-app-layout>
    <div x-data="{ 
        tab: 'payments',
        selectedInvoices: [],
        allUnvoicedIds: [@foreach($payments->whereNull('invoice_id') as $p)'{{ $p->id }}',@endforeach],
        get selectedTotal() {
            let total = 0;
            this.selectedInvoices.forEach(id => {
                let amount = document.querySelector(`tr[data-id='${id}']`)?.dataset.amount || 0;
                total += parseFloat(amount);
            });
            return total;
        },
        submitInvoiceForm() {
            let form = document.getElementById('invoice-gen-form');
            form.querySelectorAll('.dynamic-pay-input').forEach(el => el.remove());
            this.selectedInvoices.forEach(function(id) {
                let inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'payment_ids[]';
                inp.value = id;
                inp.className = 'dynamic-pay-input';
                form.appendChild(inp);
            });
            form.submit();
            // Reload after a short delay to show the generated invoice numbers
            setTimeout(() => { window.location.reload(); }, 1500);
        },
        showPaymentModal: false, 
        selectedProject: {{ json_encode($project) }},
        paymentInput: 0,
        paymentDate: '{{ date('Y-m-d') }}',
        paymentMethod: '',
        paymentError: '',
        openPayment(p) {
            this.selectedProject = p;
            this.paymentInput = 0;
            this.paymentMethod = '';
            this.paymentError = '';
            this.showPaymentModal = true;
        },
        submitPayment(event) {
            if (!this.paymentInput || this.paymentInput <= 0 || !this.paymentMethod.trim()) {
                this.paymentError = 'Both Collection Amount and Payment Method are mandatory.';
                event.preventDefault();
                return false;
            }
            this.paymentError = '';
            return true;
        },
        get gstAmount() {
            let amt = parseFloat(this.paymentInput) || 0;
            let rate = parseFloat(this.selectedProject?.tax_rate) || 0;
            if (rate === 0) return '0.00';
            let gst = amt - (amt / (1 + (rate / 100)));
            return gst.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        get actualAmount() {
            let amt = parseFloat(this.paymentInput) || 0;
            let rate = parseFloat(this.selectedProject?.tax_rate) || 0;
            let base = amt / (1 + (rate / 100));
            return base.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    }">
    {{-- Breadcrumb + Header --}}
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-8">
        <div>
            <div class="text-xs text-gray-400 dark:text-slate-500 mb-1.5 flex items-center gap-1.5">
                <a href="{{ route('projects.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400">Projects</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>{{ $project->project_name }}</span>
            </div>
            <div class="flex items-center gap-3">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $project->project_name }}</h1>
                @php
                    $statusColors = [
                        'Active'    => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                        'Completed' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
                        'On Hold'   => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                        'Draft'     => 'bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-400',
                    ];
                    $sc = $statusColors[$project->status] ?? $statusColors['Draft'];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $sc }}">
                    {{ $project->status }}
                </span>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Created {{ $project->created_at->format('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" @click="openPayment(selectedProject)"
                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold transition-all shadow-lg shadow-indigo-600/25 flex items-center gap-2 active:scale-95">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Manage Payment
            </button>
            <a href="{{ route('projects.edit', $project->id) }}"
               class="px-4 py-2.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-slate-700 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Project
            </a>
        </div>
    </div>

    {{-- Info Cards Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        {{-- Client Info --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-4">👤 Client Information</h3>
            <div class="space-y-3">
                @foreach([
                    ['Client Name',  $project->client_name],
                    ['Company',      $project->customer_company],
                    ['Email',        $project->client_email],
                    ['Phone',        $project->client_phone],
                    ['Alt Phone',    $project->customer_alt_phone],
                    ['GST Number',   $project->client_gst],
                    ['Address',      $project->client_address],
                ] as [$label, $value])
                    <div class="flex justify-between py-2.5 border-b border-gray-50 dark:border-slate-700/50 last:border-0">
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200 text-right max-w-[60%] {{ in_array($label, ['GST Number']) ? 'font-mono text-xs' : '' }}">
                            {{ $value ?: '—' }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="flex flex-col gap-6">
            <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm">
                <h3 class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500 mb-4">💰 Financial Summary</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Currency',    $project->project_currency ?? '₹'],
                        ['Base Amount', ($project->project_currency ?? '₹') . number_format($project->calculated_base_amount, 2)],
                        ['Tax Rate',    $project->tax_name ? ($project->tax_name . ' (' . $project->tax_rate . '%)') : ($project->tax_rate . '% Tax')],
                        ['Tax Amount',  ($project->project_currency ?? '₹') . number_format($project->calculated_total_amount - $project->calculated_base_amount, 2)],
                    ] as [$label, $value])
                        <div class="flex justify-between py-2.5 border-b border-gray-50 dark:border-slate-700/50 last:border-0">
                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Total Amount Highlight --}}
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg shadow-indigo-600/30">
                <div class="text-xs font-semibold uppercase tracking-wider text-indigo-200 mb-2">Total Project Value</div>
                <div class="text-4xl font-black">{{ $project->project_currency ?? '₹' }}{{ number_format($project->calculated_total_amount, 2) }}</div>
                <div class="mt-4 flex items-center gap-4 text-sm">
                    <div>
                        <div class="text-indigo-200 text-xs mb-0.5">Paid</div>
                        <div class="font-bold text-emerald-300">{{ $project->project_currency ?? '₹' }}{{ number_format($paidTotal, 0) }}</div>
                    </div>
                    <div class="w-px h-10 bg-white/20"></div>
                    <div>
                        <div class="text-indigo-200 text-xs mb-0.5">Pending</div>
                        <div class="font-bold text-amber-300">{{ $project->project_currency ?? '₹' }}{{ number_format($pendingTotal, 0) }}</div>
                    </div>
                </div>
                {{-- Progress bar --}}
                <div class="mt-4 h-1.5 bg-white/20 rounded-full overflow-hidden">
                    @php 
                        $totalVal = $project->calculated_total_amount ?: 1; 
                        $pct = round(($paidTotal / $totalVal) * 100); 
                    @endphp
                    <div class="h-full bg-emerald-400 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
                <div class="mt-1.5 text-xs text-indigo-200">{{ $pct }}% collected</div>
            </div>
        </div>
    </div>

    {{-- Hidden form: outside Alpine component to avoid scope issues --}}
    <form id="invoice-gen-form" action="{{ route('projects.generate-invoice', $project->id) }}" method="POST" style="display:none;">
        @csrf
    </form>

    {{-- Tabs: Payments + Installments --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden">

        {{-- Tab Headers + Bulk Actions --}}
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pr-6">
            <div class="flex px-6">
                <button @click="tab = 'payments'"
                    class="px-4 py-4 text-sm font-semibold border-b-2 transition-colors -mb-px"
                    :class="tab === 'payments'
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700'">
                    💳 Payments
                </button>
                <button @click="tab = 'installments'"
                    class="px-4 py-4 text-sm font-semibold border-b-2 transition-colors -mb-px ml-2"
                    :class="tab === 'installments'
                        ? 'border-indigo-600 text-indigo-600 dark:text-indigo-400 dark:border-indigo-400'
                        : 'border-transparent text-gray-500 dark:text-slate-400 hover:text-gray-700'">
                    📊 Installments
                </button>
            </div>

            <div class="flex items-center gap-3">
                {{-- Ledger Download Button --}}
                <a href="{{ route('projects.ledger', $project->id) }}" 
                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-600/20 flex items-center gap-2 transition-all active:scale-95 group">
                    <svg class="w-4 h-4 transition-transform group-hover:-translate-y-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Ledger Download
                </a>
            </div>

            {{-- Bulk Action Bar (Animated) --}}
            <template x-if="selectedInvoices.length > 0">
                <div x-transition class="flex items-center gap-4 bg-indigo-50 dark:bg-indigo-900/30 px-4 py-2 rounded-xl border border-indigo-100 dark:border-indigo-800/50 my-2">
                    <div class="text-xs font-bold text-indigo-700 dark:text-indigo-300">
                        <span x-text="selectedInvoices.length"></span> Selected &bull; {{ $project->project_currency ?? '₹' }}<span x-text="selectedTotal.toLocaleString()"></span>
                    </div>
                    <button type="button" @click="submitInvoiceForm()"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded-lg text-[11px] font-black uppercase tracking-wider shadow-lg flex items-center gap-2 transition-all active:scale-95">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Generate Invoices
                    </button>
                    <button type="button" @click="selectedInvoices = []" class="text-indigo-400 hover:text-indigo-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Payments Tab --}}
        <div x-show="tab === 'payments'">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="w-10 px-6 py-3">
                                <input type="checkbox" 
                                    @change="$event.target.checked ? selectedInvoices = [...allUnvoicedIds] : selectedInvoices = []"
                                    :checked="allUnvoicedIds.length > 0 && selectedInvoices.length === allUnvoicedIds.length"
                                    :disabled="allUnvoicedIds.length === 0"
                                    class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600 disabled:opacity-20 disabled:cursor-not-allowed">
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Base Amt</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">GST</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mode</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Invoice</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @foreach($payments as $pay)
                            @php
                                $ps = [
                                    'Paid'    => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400',
                                    'Pending' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                    'Overdue' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors" 
                                data-id="{{ $pay->id }}" data-amount="{{ $pay->amount }}">
                                <td class="px-6 py-4">
                                    <input type="checkbox" :value="'{{ $pay->id }}'" x-model="selectedInvoices"
                                        @if($pay->invoice_id) disabled @endif
                                        class="w-4 h-4 text-indigo-600 bg-white border-gray-300 rounded focus:ring-indigo-500 dark:bg-slate-700 dark:border-slate-600 {{ $pay->invoice_id ? 'opacity-20 cursor-not-allowed' : '' }}">
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $project->project_currency ?? '₹' }}{{ number_format($pay->base_amount) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">{{ $project->project_currency ?? '₹' }}{{ number_format($pay->gst) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">{{ $project->project_currency ?? '₹' }}{{ number_format($pay->amount) }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">{{ $pay->mode }}</td>
                                <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400">{{ $pay->date->format('d M Y') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase {{ $ps[$pay->status] ?? '' }}">
                                        {{ $pay->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($pay->invoice_id && $pay->invoice)
                                            <a href="{{ route('projects.invoices.download', $pay->invoice_id) }}" 
                                               class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/40 border border-indigo-100 dark:border-indigo-800/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 transition-all group"
                                               title="Download Invoice">
                                                <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ $pay->invoice->invoice_number }}
                                                </span>
                                                <svg class="w-3.5 h-3.5 text-indigo-500 dark:text-indigo-400 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                        @elseif($pay->status === 'Paid')
                                            <button type="button" @click="selectedInvoices = ['{{ $pay->id }}']; submitInvoiceForm()"
                                                class="text-[10px] font-black uppercase tracking-wider text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 underline underline-offset-2 decoration-2 transition-all">
                                                Generate Invoice
                                            </button>
                                        @else
                                            <span class="text-[10px] font-bold text-gray-400 uppercase italic">Not Available</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Installments Tab --}}
        <div x-show="tab === 'installments'" style="display:none;">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-slate-700/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Project Amt</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">GST</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                        @foreach($installments as $i => $inst)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/30 transition-colors">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                    {{ $inst->description }}
                                </td>
                                <td class="px-6 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $project->project_currency ?? '₹' }}{{ number_format($inst->project_amount) }}</td>
                                <td class="px-6 py-4 text-right text-sm text-gray-500">{{ $project->project_currency ?? '₹' }}{{ number_format($inst->project_gst) }}</td>
                                <td class="px-6 py-4 text-right text-sm font-bold text-gray-900 dark:text-white">{{ $project->project_currency ?? '₹' }}{{ number_format($inst->total_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @include('projects.partials.payment-modal')
    </div>
</x-app-layout>
