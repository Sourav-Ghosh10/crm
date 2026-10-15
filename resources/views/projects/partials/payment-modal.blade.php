{{-- Payment Modal Partial --}}
<div x-show="showPaymentModal" 
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display: none;"
     @keydown.escape.window="showPaymentModal = false">
    
    <style>
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
        .payment-card { background: #ffffff; border: 1px solid #f1f5f9; }
        .dark .payment-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.05); }
        .glass-input { background: #f8fafc; border: 1px solid #e2e8f0; }
        .dark .glass-input { background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); }
        .glass-input:focus-within { border-color: #6366f1; background: #ffffff; }
        .dark .glass-input:focus-within { border-color: #6366f1; background: rgba(255, 255, 255, 0.05); }
        input:focus, select:focus, textarea:focus { outline: none !important; box-shadow: none !important; }
    </style>

    <div class="payment-card rounded-[2.5rem] shadow-[0_32px_80px_-16px_rgba(0,0,0,0.1)] dark:shadow-[0_32px_80px_-16px_rgba(0,0,0,0.8)] overflow-hidden transform transition-all relative"
         style="width: 5.5in; min-height: 5in;"
         @click.away="showPaymentModal = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-8"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0">
        
        {{-- Close Button --}}
        <button @click="showPaymentModal = false" class="absolute top-10 right-10 text-slate-400 hover:text-slate-900 dark:text-slate-500 dark:hover:text-white transition-all hover:rotate-90 z-20">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>

        <form action="{{ route('projects.payments.store') }}" method="POST" class="p-12" @submit="submitPayment($event)">
            @csrf
            <input type="hidden" name="project_id" :value="selectedProject?.id">
            
            {{-- Header Section --}}
            <div class="flex items-start justify-between mb-12">
                <div>
                    <label class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-[0.25em] mb-3 block opacity-80 dark:opacity-60">Total Project Value</label>
                    <div class="text-5xl font-black text-slate-900 dark:text-white tracking-tight" 
                         x-text="selectedProject ? (selectedProject.project_currency || '₹') + (parseFloat(selectedProject.calculated_total_amount) || 0).toLocaleString() : ''"></div>
                </div>
                <div class="flex flex-col items-end gap-3 pt-1">
                    <div class="text-right">
                        <label class="text-[10px] font-bold text-emerald-600 dark:text-emerald-500 uppercase tracking-widest mb-1 block">Amount Paid</label>
                        <div class="text-xl font-black text-emerald-600 dark:text-emerald-400" 
                             x-text="selectedProject ? (selectedProject.project_currency || '₹') + (parseFloat(selectedProject.calculated_paid_amount) || 0).toLocaleString() : ''"></div>
                    </div>
                    <div class="text-right">
                        <label class="text-[10px] font-bold text-orange-600 dark:text-orange-500 uppercase tracking-widest mb-1 block">Balance Due</label>
                        <div class="text-xl font-black text-orange-600 dark:text-orange-400" 
                             x-text="selectedProject ? (selectedProject.project_currency || '₹') + ((parseFloat(selectedProject.calculated_total_amount) || 0) - (parseFloat(selectedProject.calculated_paid_amount) || 0)).toLocaleString() : ''"></div>
                    </div>
                </div>
            </div>

            {{-- Form Body: Symmetrical Row --}}
            <div class="space-y-12 mb-12">
                <div class="flex" style="gap: 32px;">
                    <div class="flex-1">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3 block ml-1">Payment Date</label>
                        <div class="glass-input rounded-xl transition-all" 
                             :class="paymentError && (!paymentInput || paymentInput <= 0) ? 'border-red-500 ring-1 ring-red-500' : ''"
                             style="overflow: hidden; height: 62px;">
                            <input type="date" name="payment_date" x-model="paymentDate"
                                style="background: transparent; border: none; width: 100%; height: 100%; padding: 0 18px; outline: none !important;"
                                class="text-sm font-medium text-slate-900 dark:text-white [color-scheme:light] dark:[color-scheme:dark]">
                        </div>
                    </div>
                    <div class="flex-1">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3 block ml-1">Collection Amount</label>
                        <div class="glass-input rounded-xl transition-all" 
                             :class="paymentError && (!paymentInput || paymentInput <= 0) ? 'border-red-500 ring-1 ring-red-500' : ''"
                             style="overflow: hidden; display: flex; align-items: center; height: 62px;">
                            <span class="pl-5 text-lg font-bold text-slate-400 dark:text-slate-500" x-text="selectedProject?.project_currency || '₹'"></span>
                            <input type="number" name="amount" x-model="paymentInput" autofocus placeholder="0.00" required
                                oninvalid="this.setCustomValidity('This field is mandatory.')"
                                oninput="this.setCustomValidity('')"
                                style="background: transparent !important; border: none !important; width: 100%; height: 100%; padding: 0 12px; outline: none !important;"
                                class="text-xl font-bold text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-800">
                        </div>
                    </div>
                </div>

                {{-- Featured Method Box --}}
                <div style="margin-top: 24px;">
                    <div class="w-full">
                        <label class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest mb-3 block ml-1">Method / Transaction ID Details</label>
                        <div class="glass-input rounded-2xl transition-all" 
                             :class="paymentError && !paymentMethod.trim() ? 'border-red-500 ring-1 ring-red-500' : ''"
                             style="overflow: hidden; display: flex; align-items: center; height: 86px;">
                            <input type="text" name="payment_mode" x-model="paymentMethod" placeholder="Type here: UPI, Bank, or Transaction ID..." required
                                oninvalid="this.setCustomValidity('This field is mandatory.')"
                                oninput="this.setCustomValidity('')"
                                style="background: transparent !important; border: none !important; width: 100%; height: 100%; padding: 0 24px; outline: none !important;"
                                class="text-xl font-bold text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-700">
                        </div>
                        <template x-if="paymentError">
                            <div class="mt-4 px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 rounded-xl flex items-center gap-2">
                                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span class="text-xs font-bold text-red-600 dark:text-red-400" x-text="paymentError"></span>
                            </div>
                        </template>
                    </div>
                    
                    <div style="margin-top: 24px;">
                        <button type="submit" 
                            style="width: 100%; height: 72px; background: linear-gradient(to right, #4f46e5, #7c3aed); border: none; border-radius: 18px; color: white; cursor: pointer;"
                            class="shadow-xl dark:shadow-2xl shadow-indigo-600/30 dark:shadow-indigo-600/40 opacity-95 hover:opacity-100 transition-opacity active:scale-[0.98] group relative overflow-hidden">
                            <span class="flex items-center justify-center gap-3 text-xs font-black uppercase tracking-[0.2em]">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Confirm & Add Payment</span>
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Footer Summary --}}
            <div style="margin-top: 60px; padding-top: 32px;" class="grid grid-cols-2 gap-12 border-t border-slate-200 dark:border-white/5">
                <div class="pr-6">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-3 block">
                        <span x-text="selectedProject?.tax_rate || 0"></span>% 
                        <span x-text="selectedProject?.tax_name || (selectedProject?.tax_rate == 18 ? 'GST' : (selectedProject?.tax_rate == 5 ? 'VAT' : 'Tax'))"></span> Breakup
                    </label>
                    <div class="text-4xl font-black text-slate-800 dark:text-white/90">
                        <span class="text-xs text-slate-400 dark:text-slate-600 mr-2 italic font-bold" x-text="selectedProject?.project_currency || '₹'"></span><span x-text="gstAmount"></span>
                    </div>
                </div>
                <div class="pl-12 border-l border-slate-200 dark:border-white/5">
                    <label class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-3 block">Net Base Amount</label>
                    <div class="text-4xl font-black text-slate-800 dark:text-white/90">
                        <span class="text-xs text-slate-400 dark:text-slate-600 mr-2 italic font-bold" x-text="selectedProject?.project_currency || '₹'"></span><span x-text="actualAmount"></span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
