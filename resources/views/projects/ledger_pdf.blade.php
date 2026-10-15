<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style>
        @page {
            size: A4;
            margin: 20px 24px 20px 24px;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #333;
            margin: 0;
            padding: 0;
            font-size: 9px;
            line-height: 1.2;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo-img {
            width: 95px;
            height: 45px;
        }

        .invoice-title-cell {
            text-align: right;
        }

        .tax-invoice-label {
            font-size: 14px;
            font-weight: bold;
            color: #1a4a7c;
            text-transform: uppercase;
            margin: 1;
        }

        .invoice-number-label {
            color: #666;
            font-size: 10px;
            margin-top: 2px;
        }

        .company-details {
            margin-top: 4px;
            margin-bottom: 6px;
        }

        .company-details div {
            margin: 0;
            line-height: 1.3;
            text-align: left;
        }

        .company-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 2px;
            letter-spacing: 0.2px;
        }

        .company-contact-line {
            margin-bottom: 0 !important;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            margin-top: 4px;
        }

        .info-grid td {
            vertical-align: top;
            font-size: 9px;
            line-height: 1.1;
            text-align: left;
        }

        .info-grid div {
            margin: 0;
            line-height: 1.25;
        }

        .section-label {
            font-weight: bold;
            color: #000;
            margin-bottom: 2px;
            display: block;
            text-align: left;
        }

        .details-table {
            width: 215px;
            margin-left: auto;
            margin-right: 0;
            border-collapse: collapse;
            margin-top: 0;
        }

        .invoice-details-cell {
            text-align: right;
            padding-right: 0;
        }

        .details-table td {
            padding: 2px 0;
            font-size: 9px;
            vertical-align: middle;
        }

        .details-label {
            font-weight: normal;
            color: #666;
            text-align: left;
            width: 56%;
            white-space: nowrap;
        }

        .details-value {
            font-weight: bold;
            text-align: right;
            color: #000;
            width: 44%;
            white-space: nowrap;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8px;
        }

        .items-table th {
            text-align: left;
            border-top: 2px solid #1a4a7c;
            padding: 5px 4px;
            font-weight: bold;
            background-color: #fff;
            color: #000;
            font-size: 9px;
            vertical-align: middle;
        }

        .items-table td {
            padding: 5px 4px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
            font-size: 9px;
        }

        .items-table th:first-child,
        .items-table td:first-child {
            padding-left: 0;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            padding-right: 0;
        }

        .items-header-center {
            text-align: center !important;
        }

        .items-header-right {
            text-align: right !important;
        }

        .items-header-left {
            text-align: left !important;
        }

        .item-desc {
            font-weight: bold;
            color: #333;
            line-height: 1.15;
        }

        .item-value {
            font-weight: bold;
            color: #333;
        }

        .item-subtext {
            display: block;
            margin-top: 1px;
            font-size: 8px;
            color: #666;
            font-weight: bold;
            line-height: 1.15;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .footer-grid {
            width: 100%;
            margin-top: 6px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 15px;
        }

        .footer-grid td {
            vertical-align: top;
        }

        .bank-details {
            width: 35%;
            font-size: 9px;
            line-height: 1.2;
        }

        .bank-label {
            display: inline-block;
            width: 62px;
            color: #444;
            font-weight: normal;
        }

        .bank-value {
            font-weight: bold;
            color: #000;
        }

        .totals-section {
            width: 45%;
        }

        .totals-table {
            float: right;
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 3px 2px;
            font-size: 9px;
        }

        .total-row {
            background-color: #f0f7ff;
            border: 1px solid #b0c4de;
        }

        .total-row td {
            font-size: 10px;
            font-weight: bold;
            color: #1a4a7c;
        }

        .amount-in-words {
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 9px;
            clear: both;
        }

        .notes-section {
            margin-top: 8px;
            clear: both;
        }

        .notes-title {
            font-weight: bold;
            margin-bottom: 2px;
        }

        .terms-text {
            font-size: 8px;
            color: #666;
            margin-top: 3px;
            line-height: 1.2;
        }

        .signature-section {
            margin-top: 10px;
            text-align: right;
            font-style: italic;
            color: #666;
            font-size: 9px;
            line-height: 1.3;
        }

        .bank-details-title {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .currency-symbol {
            font-family: 'DejaVu Sans', sans-serif;
        }
    </style>
</head>

<body>
    @php
        $logoPath = public_path('assets/img/nrnst_logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }
    @endphp

    <!-- Header with Logo and Invoice Title -->
    <table class="header-table">
        <tr>
            <td style="width: 60%;">
                @if($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" class="logo-img" alt="Company Logo">
                @endif
                <div class="company-details">
                    <div class="company-name">{{ $company_name }}</div>
                    <div>{!! nl2br(e($company_address)) !!}</div>
                    <div><strong>GSTIN {{ $company_gst }}</strong></div>
                    <div class="company-contact-line">Mobile: {{ $company_mobile }} | Email: {{ $company_email }}</div>
                    <div>Website: {{ $company_website }}</div>
                </div>
            </td>
            <td class="invoice-title-cell" style="width: 40%; vertical-align: top;">
                <div class="tax-invoice-label">{{ $invoice_type_label ?? 'TAX INVOICE' }}</div>
                @if(isset($display_invoice_number) && $display_invoice_number)
                    <div class="invoice-number-label">Invoice #: {{ $display_invoice_number }}</div>
                @endif
            </td>
        </tr>
    </table>

    <!-- Bill To and Invoice Details -->
    <table class="info-grid">
        <tr>
            <td style="width: 65%;">
                <span class="section-label">Bill To:</span>
                <div><strong>{{ $customer_name }}</strong></div>
                @if($customer_company)
                <div>{{ $customer_company }}</div> @endif
                <div>{!! nl2br(e($customer_address)) !!}</div>
                @if($customer_gst)
                <div>GSTIN: {{ $customer_gst }}</div> @endif
            </td>
            <td class="invoice-details-cell" style="width: 35%; vertical-align: top; text-align: right;">
                <table class="details-table">
                    @if(isset($invoice_date) && $invoice_date)
                        <tr>
                            <td class="details-label">Invoice Date:</td>
                            <td class="details-value">{{ \Carbon\Carbon::parse($invoice_date)->format('d M Y') }}</td>
                        </tr>
                    @endif
                    @if(isset($due_date) && $due_date)
                        <tr>
                            <td class="details-label">Due Date:</td>
                            <td class="details-value">{{ \Carbon\Carbon::parse($due_date)->format('d M Y') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="details-label">Place of Supply:</td>
                        <td class="details-value">{{ $place_of_supply ?? 'West Bengal' }}</td>
                    </tr>
                    @if(isset($is_ledger) && $is_ledger)
                        <tr>
                            <td colspan="2" style="padding-top: 5px;">
                                <div style="border: 1.5px solid #d32f2f; padding: 3px 6px; background-color: #fff;">
                                    <table style="width: 100%; border-collapse: collapse; margin: 0;">
                                        <tr>
                                            <td
                                                style="text-align: left; font-weight: bold; color: #d32f2f; font-size: 11px; width: 55%; padding: 0; white-space: nowrap;">
                                                Remaining Balance:</td>
                                            <td
                                                style="text-align: right; font-weight: bold; color: #d32f2f; font-size: 11px; width: 45%; padding: 0; white-space: nowrap;">
                                                <span class="currency-symbol">{{ $currency_symbol }}</span>
                                                {{ number_format($remainingBalance, 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <colgroup>
            <col style="width: 5%;">
            <col style="width: 50%;">
            <col style="width: 15%;">
            <col style="width: 10%;">
            <col style="width: 20%;">
        </colgroup>
        <thead>
            <tr>
                <th class="items-header-center" style="text-align: center;">#</th>
                <th class="items-header-left" style="text-align: left;">Item Description</th>
                <th class="items-header-right" style="text-align: right;">Rate</th>
                <th class="items-header-center" style="text-align: center;">Qty</th>
                <th class="items-header-right" style="text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $paymentRowNum = 1; @endphp
            @foreach($items as $item)
                @if(isset($item['type']) && $item['type'] === 'installment')
                    {{-- Skip installment rows entirely in Ledger PDF --}}
                    @continue
                @endif
                <tr>
                    <td class="text-center">{{ $paymentRowNum++ }}</td>
                    <td class="item-desc">
                        {{ $item['desc'] }}<br>
                        @if(isset($item['subtext']))
                            <span class="item-subtext">{{ $item['subtext'] }}</span>
                        @endif
                    </td>
                    <td class="text-right item-value">
                        @if(isset($item['price']) && $item['price'] !== null)
                            <span class="currency-symbol">{{ $currency_symbol }}</span> {{ number_format($item['price'], 2) }}
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $item['qty'] ?? '' }}
                    </td>
                    <td class="text-right item-value">
                        @if(isset($item['price']) && $item['price'] !== null)
                            <span class="currency-symbol">{{ $currency_symbol }}</span>
                            {{ number_format($item['price'] * ($item['qty'] ?? 1), 2) }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Full-width separator line between items and footer -->
    <table style="width: 100%; border-collapse: collapse; margin: 0; padding: 0;">
        <tr>
            <td style="border-bottom: 1px solid #ccc; padding: 0; font-size: 0; line-height: 0;">&nbsp;</td>
        </tr>
    </table>

    <!-- Footer Grid (Bank, Spacer, Totals) -->
    <table class="footer-grid">
        <tr>
            <!-- Bank Details -->
            <td class="bank-details" style="width: 35%;">
                <!-- <div class="bank-details-title">Bank Details:</div>
                <div><span class="bank-label">Bank:</span> <span class="bank-value">{{ $bank_name }}</span></div>
                <div><span class="bank-label">Account #:</span> <span class="bank-value">{{ $bank_account }}</span>
                </div>
                <div><span class="bank-label">IFSC:</span> <span class="bank-value">{{ $bank_ifsc }}</span></div>
                <div><span class="bank-label">Branch:</span> <span class="bank-value">{{ $bank_branch }}</span></div> -->
            </td>

            <!-- Middle Empty Spacer -->
            <td style="width: 20%;">
                &nbsp;
            </td>

            <!-- Totals Table -->
            <td class="totals-section" style="width: 45%;">
                <table class="totals-table">
                    <tr>
                        <td class="details-label" style="text-align: left;">Subtotal</td>
                        <td class="text-right"><span class="currency-symbol">{{ $currency_symbol }}</span>
                            {{ number_format($subtotal, 2) }}</td>
                    </tr>
                    @foreach($selected_taxes as $tax)
                        <tr>
                            <td class="details-label" style="text-align: left;">{{ $tax['name'] }} ({{ $tax['rate'] }}%)</td>
                            <td class="text-right"><span class="currency-symbol">{{ $currency_symbol }}</span>
                                {{ number_format($tax['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td style="text-align: left;">Total (Inc. Tax)</td>
                        <td class="text-right"><span class="currency-symbol">{{ $currency_symbol }}</span>
                            {{ number_format($grand_total, 2) }}</td>
                    </tr>
                    @if(isset($is_ledger) && $is_ledger)
                        <tr>
                            <td class="details-label" style="text-align: left; padding-top: 8px; padding-bottom: 8px;">Total
                                Paid Amount</td>
                            <td class="text-right"
                                style="padding-top: 8px; padding-bottom: 8px; font-weight: bold; color: #2e7d32;"><span
                                    class="currency-symbol">{{ $currency_symbol }}</span> {{ number_format($paidTotal, 2) }}
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <!-- Amount in Words -->
    <div class="amount-in-words">
        Total amount (in words): <strong><span class="currency-symbol">{{ $currency_symbol }}</span>
            {{ $grand_total_words }}</strong>
    </div>

    <!-- Notes & Terms -->
    <div class="notes-section">
        <div class="notes-title">Notes:</div>
        <div>
            {{ $notes ?? 'Thank you for your business! We appreciate your trust in our services.' }}
        </div>

        <div class="terms-text">
            <strong>Terms and Conditions:</strong><br>
            @if(isset($terms) && !empty($terms))
                {!! nl2br(e($terms)) !!}
            @else
                <div>1. Payment is due within 14 days of invoice date.</div>
                <div>2. Please include invoice number with payment.</div>
                <div>3. Late payments may incur additional charges.</div>
                <div>4. All services are subject to our standard terms and conditions.</div>
            @endif
        </div>
    </div>

    <!-- Signature -->
    <div class="signature-section">
        For {{ $company_name }}<br><br>
        Authorized Signatory
    </div>
</body>

</html>