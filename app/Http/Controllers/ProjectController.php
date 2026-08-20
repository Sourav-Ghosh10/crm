<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Project;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\ProjectAmount;
use App\Models\Setting;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects.
     */
    public function index(Request $request)
    {
        $statusFilter = $request->get('status');
        $search = $request->get('search');
        $user = auth()->user();

        $projectsQuery = Project::query()
            ->withSum('projectAmounts as calculated_base_amount', 'project_amount')
            ->withSum('projectAmounts as calculated_total_amount', 'total_amount')
            ->withSum('payments as calculated_paid_amount', 'amount')
            ->orderBy('id', 'desc');

        if (!$user->isAdmin() && !$user->isManager()) {
            $projectsQuery->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($statusFilter && $statusFilter !== 'all') {
            $projectsQuery->where('status', $statusFilter);
        }

        if ($search) {
            $projectsQuery->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                    ->orWhere('client_name', 'like', "%{$search}%")
                    ->orWhere('customer_company', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        $projects = $projectsQuery->get();

        // Calculate real stats with currency conversion
        $settings = Setting::getConfig();
        $defaultCurrency = '₹'; // Revenue box is specifically requested in INR

        $statsBaseQuery = Project::query();
        if (!$user->isAdmin() && !$user->isManager()) {
            $statsBaseQuery->whereHas('assignees', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        $allowedProjectIds = $statsBaseQuery->pluck('id');

        $paymentsQuery = Payment::join('codec_projects', 'codec_payments.project_id', '=', 'codec_projects.id')
            ->select('codec_payments.amount', 'codec_projects.project_currency');

        if (!$user->isAdmin() && !$user->isManager()) {
            $paymentsQuery->whereIn('codec_projects.id', $allowedProjectIds);
        }

        $allPayments = $paymentsQuery->get();

        $totalRevenueINR = 0;
        $rates = collect($settings->currency_conversion_rates ?? []);

        foreach ($allPayments as $payment) {
            $currency = $payment->project_currency;
            $amount = $payment->amount;

            if ($currency === '₹' || strtoupper($currency) === 'INR') {
                $totalRevenueINR += $amount;
            } else {
                // Find matching rate
                $rateMatch = $rates->first(function ($r) use ($currency) {
                    // Try to match by name or symbol
                    return (isset($r['currency']) && ($r['currency'] === $currency)) ||
                        (isset($r['symbol']) && ($r['symbol'] === $currency));
                });

                // If no direct match by symbol, try matching by name in available_currencies
                if (!$rateMatch) {
                    $currInfo = collect($settings->available_currencies)->firstWhere('symbol', $currency);
                    if ($currInfo) {
                        $rateMatch = $rates->firstWhere('currency', $currInfo['name']);
                    }
                }

                $conversionRate = $rateMatch ? $rateMatch['rate'] : 1;
                $totalRevenueINR += ($amount * $conversionRate);
            }
        }

        $stats = [
            'total' => (clone $statsBaseQuery)->count(),
            'active' => (clone $statsBaseQuery)->where('status', 'Active')->count(),
            'completed' => (clone $statsBaseQuery)->where('status', 'Completed')->count(),
            'revenue' => $defaultCurrency . number_format($totalRevenueINR, 0),
        ];

        return view('projects.index', compact('projects', 'stats', 'statusFilter', 'search'));
    }

    /**
     * Show project settings (reads from codec_settings table).
     */
    public function settings()
    {
        $config = Setting::getConfig();
        return view('projects.settings', compact('config'));
    }

    /**
     * Save / update project settings into codec_settings table.
     */
    public function saveSettings(Request $request)
    {
        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'company_gst' => 'nullable|string|max:100',
            'company_mobile' => 'nullable|string|max:50',
            'company_email' => 'nullable|email|max:255',
            'company_website' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:100',
            'bank_ifsc' => 'nullable|string|max:50',
            'bank_branch' => 'nullable|string|max:255',
            'currencies' => 'nullable|array',
            'currencies.*.symbol' => 'nullable|string|max:10',
            'currencies.*.name' => 'nullable|string|max:50',
            'taxes' => 'nullable|array',
            'taxes.*.name' => 'nullable|string|max:50',
            'taxes.*.rate' => 'nullable|numeric',
            'default_notes' => 'nullable|string',
            'default_terms' => 'nullable|string',
            'rates' => 'nullable|array',
            'rates.*.currency' => 'nullable|string|max:50',
            'rates.*.rate' => 'nullable|numeric',
        ]);

        // Map form field names to model field names
        $settingsData = [
            'company_name' => $data['company_name'] ?? null,
            'company_address' => $data['company_address'] ?? null,
            'company_gst' => $data['company_gst'] ?? null,
            'company_mobile' => $data['company_mobile'] ?? null,
            'company_email' => $data['company_email'] ?? null,
            'company_website' => $data['company_website'] ?? null,
            'upi_id' => $data['upi_id'] ?? null,
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account' => $data['bank_account'] ?? null,
            'bank_ifsc' => $data['bank_ifsc'] ?? null,
            'bank_branch' => $data['bank_branch'] ?? null,
            'available_currencies' => $data['currencies'] ?? [],
            'available_taxes' => $data['taxes'] ?? [],
            'currency_conversion_rates' => $data['rates'] ?? [],
            'default_notes' => $data['default_notes'] ?? null,
            'default_terms' => $data['default_terms'] ?? null,
        ];

        // updateOrCreate: always keep a single row (id = 1)
        Setting::updateOrCreate(['id' => 1], $settingsData);

        return redirect()->route('projects.settings')
            ->with('success', 'Settings saved successfully!');
    }

    /**
     * Display a listing of invoices.
     */
    public function invoices()
    {
        $invoices = Invoice::with('payments.project')->orderBy('created_at', 'desc')->get();
        return view('projects.invoices', compact('invoices'));
    }

    /**
     * Generate and download invoice PDF.
     */
    public function generateInvoice(Request $request)
    {
        // Allow optional fields for demo/listing convenience
        $data = $request->validate([
            'invoice_type_label' => 'nullable|string',
            'display_invoice_number' => 'required|string',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'place_of_supply' => 'nullable|string',
            'customer_name' => 'required|string',
            'customer_company' => 'nullable|string',
            'customer_gst' => 'nullable|string',
            'customer_address' => 'required|string',
            'customer_phone' => 'nullable|string',
            'customer_email' => 'nullable|email',
            'items' => 'required|array',
            'items.*.desc' => 'required|string',
            'items.*.price' => 'required|numeric',
            'items.*.qty' => 'required|integer',
            'tax_rate' => 'required|numeric',
            'tax_name' => 'required|string',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
        ]);

        // Default company data matching the Python script / Settings
        $company = [
            'name' => 'NRNST TECH PRIVATE LIMITED',
            'gst' => '19AAGCN5427M1ZY',
            'address' => "Mani Casadona, Plot No. IIF/04, Newtown, Kolkata, West Bengal 700156",
            'mobile' => '+91 98765 43210',
            'email' => 'info@nrnsttech.com',
            'website' => 'www.nrnsttech.com',
            'upi' => 'nrnsttech@hdfc',
            'bank_name' => 'HDFC Bank',
            'bank_account' => '12345678901234',
            'bank_ifsc' => 'HDFC0001234',
            'bank_branch' => 'Salt Lake, Kolkata',
        ];

        // Process items and totals
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += $item['price'] * $item['qty'];
        }

        $tax_rate = (float) $data['tax_rate'];
        $tax_amount = ($subtotal * $tax_rate) / 100;
        $grand_total = $subtotal + $tax_amount;

        $selected_taxes = [];
        if (strtoupper($data['tax_name']) === 'GST') {
            $half_rate = $tax_rate / 2;
            $half_amount = $tax_amount / 2;
            $selected_taxes[] = ['name' => 'CGST', 'rate' => $half_rate, 'amount' => $half_amount];
            $selected_taxes[] = ['name' => 'SGST', 'rate' => $half_rate, 'amount' => $half_amount];
        } else {
            $selected_taxes[] = ['name' => $data['tax_name'], 'rate' => $tax_rate, 'amount' => $tax_amount];
        }

        // Prepare PDF variables
        $pdfData = [
            'invoice_type_label' => $data['invoice_type_label'] ?? 'TAX INVOICE',
            'display_invoice_number' => $data['display_invoice_number'],
            'status_label' => 'Paid in Full',
            'company_name' => $company['name'],
            'company_gst' => $company['gst'],
            'company_address' => $company['address'],
            'company_mobile' => $company['mobile'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_upi' => $company['upi'],

            'customer_name' => $data['customer_name'],
            'customer_company' => $data['customer_company'] ?? 'ABC Corporation Pvt Ltd',
            'customer_gst' => $data['customer_gst'] ?? '19ABCDE1234F1Z6',
            'customer_address' => $data['customer_address'],

            'invoice_date' => $data['invoice_date'] ?? '2026-04-16',
            'due_date' => $data['due_date'] ?? '2026-04-30',
            'place_of_supply' => $data['place_of_supply'] ?? 'West Bengal',

            'subtotal' => $subtotal,
            'selected_taxes' => $selected_taxes,
            'grand_total' => $grand_total,
            'grand_total_words' => $this->numberToWordsIndia($grand_total),
            'currency_symbol' => '₹',
            'notes' => $data['notes'] ?? 'Thank you for your business! We appreciate your trust in our services.',
            'terms' => $data['terms'] ?? "1. Payment is due within 14 days of invoice date.\n2. Please include invoice number with payment.\n3. Late payments may incur additional charges.\n4. All services are subject to our standard terms and conditions.",

            'bank_name' => $company['bank_name'],
            'bank_account' => $company['bank_account'],
            'bank_ifsc' => $company['bank_ifsc'],
            'bank_branch' => $company['bank_branch'],
            'items' => $data['items'],
        ];

        $pdf = Pdf::loadView('projects.invoice_pdf', $pdfData);
        return $pdf->download('Invoice-' . str_replace(['/', '\\'], '-', $data['display_invoice_number']) . '.pdf');
    }

    private function numberToWordsIndia($number)
    {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '',
            '1' => 'One',
            '2' => 'Two',
            '3' => 'Three',
            '4' => 'Four',
            '5' => 'Five',
            '6' => 'Six',
            '7' => 'Seven',
            '8' => 'Eight',
            '9' => 'Nine',
            '10' => 'Ten',
            '11' => 'Eleven',
            '12' => 'Twelve',
            '13' => 'Thirteen',
            '14' => 'Fourteen',
            '15' => 'Fifteen',
            '16' => 'Sixteen',
            '17' => 'Seventeen',
            '18' => 'Eighteen',
            '19' => 'Nineteen',
            '20' => 'Twenty',
            '30' => 'Thirty',
            '40' => 'Forty',
            '50' => 'Fifty',
            '60' => 'Sixty',
            '70' => 'Seventy',
            '80' => 'Eighty',
            '90' => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred
                    :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
            } else
                $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ?
            " and " . $words[$point / 10] . " " .
            $words[$point = $point % 10] : '';
        return $result . "Only";
    }

    /**
     * Show the form for creating a new project.
     */
    public function create()
    {
        $settings = Setting::getConfig();
        return view('projects.create', compact('settings'));
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:255',
            'customer_alt_phone' => 'nullable|string|max:255',
            'customer_company' => 'required|string|max:255',
            'client_gst' => 'required|string|max:255',
            'client_address' => 'required|string',
            'base_amount' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'project_currency' => 'required|string|max:10',
            'status' => 'required|string',
            'supplementary_charges' => 'nullable|array',
            'supplementary_charges.*.description' => 'required|string',
            'supplementary_charges.*.base_amount' => 'required|numeric|min:0',
            'supplementary_charges.*.gst_amount' => 'required|numeric|min:0',
            'supplementary_charges.*.total_amount' => 'required|numeric|min:0',
        ]);

        $data['total_amount'] = $data['base_amount'] * (1 + ($data['tax_rate'] / 100));

        $project = Project::create($data);

        // Calculate GST for the main project base
        $gstAmount = $data['total_amount'] - $data['base_amount'];

        // Record the Main Project details in codec_project_amount
        ProjectAmount::create([
            'project_id' => $project->id,
            'project_amount' => $data['base_amount'],
            'project_gst' => $gstAmount,
            'total_amount' => $data['total_amount'],
            'description' => $data['project_name'] . ' (Base Amount)',
        ]);

        // Record supplementary charges if any
        if (!empty($data['supplementary_charges'])) {
            foreach ($data['supplementary_charges'] as $charge) {
                ProjectAmount::create([
                    'project_id' => $project->id,
                    'project_amount' => $charge['base_amount'],
                    'project_gst' => $charge['gst_amount'],
                    'total_amount' => $charge['total_amount'],
                    'description' => $charge['description'],
                ]);
            }
        }

        return redirect()->route('projects.index')
            ->with('success', 'Project created and initialized successfully!');
    }

    /**
     * Display a single project.
     */
    public function show($id)
    {
        $user = auth()->user();
        $projectModel = Project::findOrFail($id);

        if (!$user->isAdmin() && !$user->isManager()) {
            if (!$projectModel->assignees->contains('id', $user->id)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $project = Project::withSum('projectAmounts as calculated_base_amount', 'project_amount')
            ->withSum('projectAmounts as calculated_total_amount', 'total_amount')
            ->withSum('payments as calculated_paid_amount', 'amount')
            ->findOrFail($id);

        $payments = $project->payments()->with('invoice')->orderBy('date', 'desc')->get();

        // Use ProjectAmount model for installments
        $installments = ProjectAmount::where('project_id', $id)->orderBy('payment_id', 'asc')->get();

        // Handle both 'Paid' and legacy 'completed' status values
        $paidTotal = $payments->whereIn('status', ['Paid', 'completed'])->sum('amount');
        $pendingTotal = $project->calculated_total_amount - $paidTotal;

        return view('projects.show', compact('project', 'payments', 'installments', 'paidTotal', 'pendingTotal'));
    }

    public function edit($id)
    {
        $project = Project::with('projectAmounts')
            ->withSum('projectAmounts as calculated_base_amount', 'project_amount')
            ->withSum('projectAmounts as calculated_total_amount', 'total_amount')
            ->withSum('payments as calculated_paid_amount', 'amount')
            ->findOrFail($id);
        $settings = Setting::getConfig();

        // Sum of total_amount in codec_project_amount for this project
        $totalProjectCost = ProjectAmount::where('project_id', $id)->sum('total_amount');

        return view('projects.edit', compact('project', 'settings', 'totalProjectCost'));
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $data = $request->validate([
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'required|string|max:255',
            'customer_alt_phone' => 'nullable|string|max:255',
            'customer_company' => 'required|string|max:255',
            'client_gst' => 'required|string|max:255',
            'client_address' => 'required|string',
            'base_amount' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'project_currency' => 'required|string|max:10',
            'status' => 'required|string',
            'supplementary_charges' => 'nullable|array',
            'supplementary_charges.*.description' => 'required|string',
            'supplementary_charges.*.base_amount' => 'required|numeric|min:0',
            'supplementary_charges.*.gst_amount' => 'required|numeric|min:0',
            'supplementary_charges.*.total_amount' => 'required|numeric|min:0',
        ]);

        // Calculate NEW Grand Total (Current Total + New Supplementary Charges)
        // Note: Base amount and tax rate are readonly in UI to preserve history consistency
        $newChargesTotal = 0;
        if (!empty($data['supplementary_charges'])) {
            foreach ($data['supplementary_charges'] as $charge) {
                $newChargesTotal += (float)$charge['total_amount'];
            }
        }
        
        $data['total_amount'] = (float)$project->total_amount + $newChargesTotal;

        // 1. Update project core info (Name, Status, Client info, etc.)
        $project->update($data);

        // 2. Add NEW supplementary charges to ledger
        if (!empty($data['supplementary_charges'])) {
            foreach ($data['supplementary_charges'] as $charge) {
                ProjectAmount::create([
                    'project_id' => $project->id,
                    'project_amount' => (float)$charge['base_amount'],
                    'project_gst' => (float)$charge['gst_amount'],
                    'total_amount' => (float)$charge['total_amount'],
                    'description' => $charge['description'],
                ]);
            }
        }

        return redirect()->route('projects.show', $id)
            ->with('success', 'Project updated successfully!');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Store a payment for a project (Real implementation).
     */
    public function storePayment(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:codec_projects,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
        ]);

        $project = Project::findOrFail($data['project_id']);

        // Financial breakdown based on project tax rate
        $tax_rate = $project->tax_rate;
        $base = $data['amount'] / (1 + ($tax_rate / 100));
        $gst = $data['amount'] - $base;

        // 1. Save to codec_payments
        $payment = Payment::create([
            'project_id' => $data['project_id'],
            'amount' => $data['amount'],
            'date' => $data['payment_date'],
            'mode' => $data['payment_mode'],
            'base_amount' => $base,
            'gst' => $gst,
            'status' => 'Paid'
        ]);

        return redirect()->back()
            ->with('success', 'Payment recorded successfully!');
    }

    /**
     * Generate invoice PDF from selected payment IDs for a project.
     */
    public function generatePaymentInvoice(Request $request, $projectId)
    {
        $project = Project::findOrFail($projectId);
        $paymentIds = $request->input('payment_ids', []);

        // Load selected payments (or all if none specified)
        $payments = $project->payments()
            ->when(!empty($paymentIds), fn($q) => $q->whereIn('id', $paymentIds))
            ->orderBy('date', 'asc')
            ->get();

        if ($payments->isEmpty()) {
            return back()->with('error', 'No payments selected for invoice generation.');
        }

        // Check if an invoice already exists for this exact selection of payments
        $firstPay = $payments->first();
        if ($firstPay->invoice_id) {
            $existingInvoice = Invoice::find($firstPay->invoice_id);
            if ($existingInvoice) {
                // Verify if the payments in that invoice match our current selection exactly
                $existingInvoicePaymentIds = Payment::where('invoice_id', $existingInvoice->id)->pluck('id')->toArray();
                $currentSelectionIds = $payments->pluck('id')->toArray();

                sort($existingInvoicePaymentIds);
                sort($currentSelectionIds);

                if ($existingInvoicePaymentIds === $currentSelectionIds) {
                    return $this->downloadInvoice($existingInvoice->id);
                }
            }
        }

        $settings = Setting::getConfig();

        // Build items array from payments
        $items = [];
        foreach ($payments as $pay) {
            $items[] = [
                'desc' => 'Payment — ' . $project->project_name . ' (' . $pay->mode . ')',
                'price' => $pay->base_amount,
                'qty' => 1,
                'subtext' => 'Ref: ' . $pay->ref . ' | Date: ' . $pay->date->format('d M Y'),
            ];
        }

        $subtotal = $payments->sum('base_amount');
        $tax_amount = $payments->sum('gst');
        $grand_total = $payments->sum('amount');
        $tax_rate = $project->tax_rate;

        // Determine tax breakdown
        $taxTypeMatch = collect($settings->available_taxes)->firstWhere('rate', $tax_rate);
        $taxName = $taxTypeMatch ? $taxTypeMatch['name'] : 'Tax';

        $selected_taxes = [];
        if (strtoupper($taxName) === 'GST') {
            $half_rate = $tax_rate / 2;
            $half_amount = $tax_amount / 2;
            $selected_taxes[] = ['name' => 'CGST', 'rate' => $half_rate, 'amount' => $half_amount];
            $selected_taxes[] = ['name' => 'SGST', 'rate' => $half_rate, 'amount' => $half_amount];
        } else {
            $selected_taxes[] = ['name' => $taxName, 'rate' => $tax_rate, 'amount' => $tax_amount];
        }

        // Global sequential invoice number: CI-MMDDYY-XXX
        $maxSeq = Invoice::max('invoice_seq') ?? 0;
        $nextNum = $maxSeq + 1;

        // Generate the new invoice number
        $invoiceNumber = 'CI-' . date('mdy') . '-' . str_pad($nextNum, 3, '0', STR_PAD_LEFT);

        // Record the invoice in the database to ensure the sequence persists
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_seq' => $nextNum,
            'invoice_date' => now(),
            'client_name' => $project->client_name,
            'total_amount' => $grand_total,
            'tax_amount' => $tax_amount,
            'base_amount' => $subtotal,
            'invoice_type' => 'TAX INVOICE',
            'status' => 'Paid',
            'data' => [
                'project_id' => $project->id,
                'payment_ids' => $paymentIds
            ],
        ]);

        // Link payments to this invoice
        Payment::whereIn('id', $paymentIds)->update(['invoice_id' => $invoice->id]);

        $company = [
            'name' => $settings->company_name ?? 'NRNST TECH PRIVATE LIMITED',
            'gst' => $settings->company_gst ?? '19AAGCN5427M1ZY',
            'address' => $settings->company_address ?? 'Mani Casadona, Plot No, IIF/04, Newtown, Kolkata, West Bengal 700156',
            'mobile' => $settings->company_mobile ?? '+91 022-46635616',
            'email' => $settings->company_email ?? 'info@codecit.com',
            'website' => $settings->company_website ?? 'www.codecit.com',
            'bank_name' => $settings->bank_name ?? 'HDFC Bank',
            'bank_account' => $settings->bank_account ?? '12345678901234',
            'bank_ifsc' => $settings->bank_ifsc ?? 'HDFC0001234',
            'bank_branch' => $settings->bank_branch ?? 'Salt Lake, Kolkata',
        ];

        $pdfData = [
            'invoice_type_label' => 'TAX INVOICE',
            'display_invoice_number' => $invoiceNumber,
            'invoice_date' => now()->format('Y-m-d'),
            'due_date' => null,
            'place_of_supply' => 'West Bengal',

            'company_name' => $company['name'],
            'company_gst' => $company['gst'],
            'company_address' => $company['address'],
            'company_mobile' => $company['mobile'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_upi' => $settings->upi_id ?? '',
            'bank_name' => $company['bank_name'],
            'bank_account' => $company['bank_account'],
            'bank_ifsc' => $company['bank_ifsc'],
            'bank_branch' => $company['bank_branch'],

            'customer_name' => $project->client_name,
            'customer_company' => $project->customer_company ?? '',
            'customer_gst' => $project->client_gst ?? '',
            'customer_address' => $project->client_address ?? '',

            'items' => $items,
            'subtotal' => $subtotal,
            'selected_taxes' => $selected_taxes,
            'grand_total' => $grand_total,
            'grand_total_words' => $this->numberToWordsIndia($grand_total),
            'currency_symbol' => $project->project_currency ?? '₹',
            'notes' => 'Thank you for your business! We appreciate your trust in our services.',
            'terms' => "1. Payment is due within 14 days of invoice date.\n2. Please include invoice number with payment.\n3. Late payments may incur additional charges.\n4. All services are subject to our standard terms and conditions.",
        ];

        $pdf = Pdf::loadView('projects.invoice_pdf', $pdfData)->setPaper('a4', 'portrait');
        return $pdf->download(str_replace(['/', '\\'], '-', $invoiceNumber) . '.pdf');
    }

    public function downloadLedger($id)
    {
        $project = Project::findOrFail($id);
        $settings = Setting::getConfig();
        // Handle both 'Paid' and legacy 'completed' status values
        $payments = $project->payments()->whereIn('status', ['Paid', 'completed'])->get();

        $paidTotal = $payments->sum('amount');

        // Get all project segments (installments/base/supplementary)
        $installments = ProjectAmount::where('project_id', $id)->orderBy('payment_id', 'asc')->get();

        $items = [];
        // 1. Add Installments (Charges)
        foreach ($installments as $inst) {
            $items[] = [
                'type' => 'installment',
                'desc' => $inst->description,
                'price' => $inst->project_amount,
                'qty' => 1,
            ];
        }

        // 2. Add Payments (Credits) - Only if they are paid
        foreach ($payments as $pay) {
            $items[] = [
                'type' => 'payment',
                'desc' => 'Payment Received',
                'subtext' => 'Details: ' . $pay->mode . ' | Date: ' . $pay->date->format('d M Y'),
                'price' => $pay->amount,
                'qty' => 1,
            ];
        }

        $subtotal = $installments->sum('project_amount');
        $tax_amount = $installments->sum('project_gst');
        $grand_total = $installments->sum('total_amount');
        $remainingBalance = $grand_total - $paidTotal;
        $tax_rate = $project->tax_rate;

        // Determine tax breakdown
        $taxTypeMatch = collect($settings->available_taxes)->firstWhere('rate', $tax_rate);
        $taxName = $taxTypeMatch ? $taxTypeMatch['name'] : 'Tax';

        $selected_taxes = [];
        if (strtoupper($taxName) === 'GST') {
            $half_rate = $tax_rate / 2;
            $half_amount = $tax_amount / 2;
            $selected_taxes[] = ['name' => 'CGST', 'rate' => $half_rate, 'amount' => $half_amount];
            $selected_taxes[] = ['name' => 'SGST', 'rate' => $half_rate, 'amount' => $half_amount];
        } else {
            $selected_taxes[] = ['name' => $taxName, 'rate' => $tax_rate, 'amount' => $tax_amount];
        }

        $company = [
            'name' => $settings->company_name ?? 'NRNST TECH PRIVATE LIMITED',
            'gst' => $settings->company_gst ?? '19AAGCN5427M1ZY',
            'address' => $settings->company_address ?? 'Mani Casadona, Plot No, IIF/04, Newtown, Kolkata, West Bengal 700156',
            'mobile' => $settings->company_mobile ?? '+91 022-46635616',
            'email' => $settings->company_email ?? 'info@codecit.com',
            'website' => $settings->company_website ?? 'www.codecit.com',
            'bank_name' => $settings->bank_name ?? 'HDFC Bank',
            'bank_account' => $settings->bank_account ?? '12345678901234',
            'bank_ifsc' => $settings->bank_ifsc ?? 'HDFC0001234',
            'bank_branch' => $settings->bank_branch ?? 'Salt Lake, Kolkata',
        ];

        $pdfData = [
            'invoice_type_label' => 'LEDGER BALANCE',
            'display_invoice_number' => null, // No invoice number
            'invoice_date' => null, // No invoice date
            'due_date' => now()->format('Y-m-d'), // Must be a due date
            'place_of_supply' => 'West Bengal', // Place of supply

            'company_name' => $company['name'],
            'company_gst' => $company['gst'],
            'company_address' => $company['address'],
            'company_mobile' => $company['mobile'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_upi' => $settings->upi_id ?? '',
            'bank_name' => $company['bank_name'],
            'bank_account' => $company['bank_account'],
            'bank_ifsc' => $company['bank_ifsc'],
            'bank_branch' => $company['bank_branch'],

            'customer_name' => $project->client_name,
            'customer_company' => $project->customer_company ?? '',
            'customer_gst' => $project->client_gst ?? '',
            'customer_address' => $project->client_address ?? '',

            'items' => $items,
            'subtotal' => $subtotal,
            'selected_taxes' => $selected_taxes,
            'grand_total' => $grand_total,
            'grand_total_words' => $this->numberToWordsIndia($grand_total),
            'currency_symbol' => $project->project_currency ?? '₹',
            'notes' => 'Thank you for your business! We appreciate your trust in our services.',
            'terms' => "1. Payment is due within 14 days of invoice date.\n2. Please include invoice number with payment.\n3. Late payments may incur additional charges.\n4. All services are subject to our standard terms and conditions.",

            'paidTotal' => $paidTotal,
            'remainingBalance' => $remainingBalance,
            'is_ledger' => true,
        ];

        $pdf = Pdf::loadView('projects.ledger_pdf', $pdfData)->setPaper('a4', 'portrait');
        return $pdf->download('Ledger-' . str_replace([' ', '/', '\\'], '_', $project->project_name) . '.pdf');
    }

    public function downloadInvoice($id)
    {
        $invoice = Invoice::with('payments.project')->findOrFail($id);
        $settings = Setting::getConfig();

        // Get project from data or payments
        $project = $invoice->payments->first()?->project;
        if (!$project && isset($invoice->data['project_id'])) {
            $project = Project::find($invoice->data['project_id']);
        }

        if (!$project) {
            return back()->with('error', 'Project not found for this invoice.');
        }

        // Reconstruct items from payments
        $items = [];
        foreach ($invoice->payments as $pay) {
            $items[] = [
                'desc' => 'Payment — ' . $project->project_name . ' (' . $pay->mode . ')',
                'price' => $pay->base_amount,
                'qty' => 1,
                'subtext' => 'Ref: ' . $pay->ref . ' | Date: ' . $pay->date->format('d M Y'),
            ];
        }

        // If no payments linked yet (older record), use generic label
        if (empty($items)) {
            $items[] = [
                'desc' => 'Invoice Payment — ' . $invoice->invoice_number,
                'price' => $invoice->base_amount,
                'qty' => 1,
            ];
        }

        $tax_rate = $project->tax_rate;
        $taxTypeMatch = collect($settings->available_taxes)->firstWhere('rate', $tax_rate);
        $taxName = $taxTypeMatch ? $taxTypeMatch['name'] : 'Tax';

        $selected_taxes = [];
        if (strtoupper($taxName) === 'GST') {
            $half_rate = $tax_rate / 2;
            $half_amount = $invoice->tax_amount / 2;
            $selected_taxes[] = ['name' => 'CGST', 'rate' => $half_rate, 'amount' => $half_amount];
            $selected_taxes[] = ['name' => 'SGST', 'rate' => $half_rate, 'amount' => $half_amount];
        } else {
            $selected_taxes[] = ['name' => $taxName, 'rate' => $tax_rate, 'amount' => $invoice->tax_amount];
        }

        $company = [
            'name' => $settings->company_name ?? 'NRNST TECH PRIVATE LIMITED',
            'gst' => $settings->company_gst ?? '19AAGCN5427M1ZY',
            'address' => $settings->company_address ?? 'Mani Casadona, Plot No, IIF/04, Newtown, Kolkata, West Bengal 700156',
            'mobile' => $settings->company_mobile ?? '+91 022-46635616',
            'email' => $settings->company_email ?? 'info@codecit.com',
            'website' => $settings->company_website ?? 'www.codecit.com',
            'bank_name' => $settings->bank_name ?? 'HDFC Bank',
            'bank_account' => $settings->bank_account ?? '12345678901234',
            'bank_ifsc' => $settings->bank_ifsc ?? 'HDFC0001234',
            'bank_branch' => $settings->bank_branch ?? 'Salt Lake, Kolkata',
        ];

        $pdfData = [
            'invoice_type_label' => 'TAX INVOICE',
            'display_invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : now()->format('Y-m-d'),
            'due_date' => null,
            'place_of_supply' => 'West Bengal',

            'company_name' => $company['name'],
            'company_gst' => $company['gst'],
            'company_address' => $company['address'],
            'company_mobile' => $company['mobile'],
            'company_email' => $company['email'],
            'company_website' => $company['website'],
            'company_upi' => $settings->upi_id ?? '',
            'bank_name' => $company['bank_name'],
            'bank_account' => $company['bank_account'],
            'bank_ifsc' => $company['bank_ifsc'],
            'bank_branch' => $company['bank_branch'],

            'customer_name' => $invoice->client_name,
            'customer_company' => $project->customer_company ?? '',
            'customer_gst' => $project->client_gst ?? '',
            'customer_address' => $project->client_address ?? '',

            'items' => $items,
            'subtotal' => $invoice->base_amount,
            'selected_taxes' => $selected_taxes,
            'grand_total' => $invoice->total_amount,
            'grand_total_words' => $this->numberToWordsIndia($invoice->total_amount),
            'currency_symbol' => $project->project_currency ?? '₹',
            'notes' => 'Thank you for your business! We appreciate your trust in our services.',
            'terms' => "1. Payment is due within 14 days of invoice date.\n2. Please include invoice number with payment.\n3. Late payments may incur additional charges.\n4. All services are subject to our standard terms and conditions.",
        ];

        $pdf = Pdf::loadView('projects.invoice_pdf', $pdfData)->setPaper('a4', 'portrait');
        return $pdf->download(str_replace(['/', '\\'], '-', $invoice->invoice_number) . '.pdf');
    }
}
