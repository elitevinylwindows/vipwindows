<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Estimate — {{ $job->customer_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; font-size: 10pt; }
        .page { width: 8.5in; min-height: 11in; margin: 0 auto; padding: .5in .6in; }

        /* ── Header ── */
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: .3in; }
        .company-info { font-size: 10pt; line-height: 1.5; }
        .company-info .name { font-weight: bold; font-size: 11pt; }
        .estimate-title { font-size: 22pt; font-weight: bold; text-align: right; }
        .date-box { margin-top: 8px; }
        .date-box table { border-collapse: collapse; margin-left: auto; }
        .date-box th, .date-box td { border: 1px solid #000; padding: 4px 12px; font-size: 9pt; }
        .date-box th { background: #eee; font-weight: bold; }

        /* ── Address blocks ── */
        .address-row { display: flex; gap: 30px; margin-bottom: .25in; }
        .address-block { flex: 1; border: 1px solid #000; padding: 0; }
        .address-block .label { background: #eee; border-bottom: 1px solid #000; padding: 4px 8px; font-weight: bold; font-size: 9pt; }
        .address-block .content { padding: 8px; font-size: 9.5pt; line-height: 1.5; min-height: 80px; }

        /* ── Reference row ── */
        .ref-table { width: 100%; border-collapse: collapse; margin-bottom: .2in; }
        .ref-table th, .ref-table td { border: 1px solid #000; padding: 5px 10px; font-size: 9pt; text-align: center; }
        .ref-table th { background: #eee; font-weight: bold; }

        /* ── Items table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: .15in; }
        .items-table th { background: #333; color: #fff; padding: 5px 8px; font-size: 8.5pt; text-align: left; font-weight: bold; border: 1px solid #333; }
        .items-table td { padding: 4px 8px; font-size: 9pt; border: 1px solid #ccc; vertical-align: top; }
        .items-table tr:nth-child(even) { background: #f9f9f9; }
        .items-table .text-right { text-align: right; }
        .items-table .text-center { text-align: center; }

        /* ── Notice ── */
        .notice-box { border: 1px solid #999; border-radius: 4px; padding: 8px 12px; font-size: 8.5pt; line-height: 1.5; margin: .15in 0; display: inline-block; }

        /* ── Totals ── */
        .totals-row { display: flex; justify-content: space-between; align-items: flex-end; margin-top: .2in; }
        .terms-text { font-size: 8pt; line-height: 1.5; max-width: 55%; color: #333; }
        .totals-box { text-align: right; }
        .totals-box table { border-collapse: collapse; margin-left: auto; }
        .totals-box td { padding: 4px 15px; font-size: 10pt; }
        .totals-box .label { font-weight: bold; text-align: left; }
        .totals-box .total-row td { border-top: 2px solid #000; font-size: 12pt; font-weight: bold; padding-top: 6px; }

        /* ── Signature ── */
        .signature-line { margin-top: .4in; display: flex; align-items: flex-end; gap: 10px; }
        .signature-line .label { font-size: 9pt; font-weight: bold; white-space: nowrap; }
        .signature-line .line { flex: 1; border-bottom: 1px solid #000; min-width: 250px; }

        @media print {
            .page { padding: .4in .5in; }
        }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <div class="header">
        <div class="company-info">
            <div class="name">{{ $settings['company_name'] ?? 'VIP Windows Inc.' }}</div>
            <div>{{ $settings['company_address'] ?? '4231 Liberty Blvd.' }}</div>
            <div>{{ $settings['company_city'] ?? 'South Gate' }}, {{ $settings['company_state'] ?? 'CA' }} {{ $settings['company_zip'] ?? '90280' }}</div>
            <div>Phone: {{ $settings['company_phone'] ?? '(562) 368-0313' }}</div>
        </div>
        <div>
            <div class="estimate-title">Estimate</div>
            <div class="date-box">
                <table>
                    <tr><th>Date</th><th>Estimate #</th></tr>
                    <tr>
                        <td>{{ $estimateDate }}</td>
                        <td>{{ $estimateNumber }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Address blocks --}}
    <div class="address-row">
        <div class="address-block">
            <div class="label">Name / Address</div>
            <div class="content">
                {{ strtoupper($job->customer_name) }}<br>
                @if($job->install_address){{ strtoupper($job->install_address) }},<br>@endif
                @if($job->install_city || $job->install_state){{ strtoupper($job->install_city) }}, {{ strtoupper($job->install_state) }} {{ $job->install_zip }}@endif
                @if($job->customer_phone)<br>{{ $job->customer_phone }}@endif
            </div>
        </div>
        <div class="address-block">
            <div class="label">Job Address</div>
            <div class="content">
                {{ $job->customer_name }}<br>
                @if($job->install_address){{ $job->install_address }},<br>@endif
                @if($job->install_city){{ $job->install_city }}, {{ $job->install_state }}@endif
                @if($job->customer_phone)<br>{{ $job->customer_phone }}@endif
            </div>
        </div>
    </div>

    {{-- Reference row --}}
    <table class="ref-table">
        <tr>
            <th>Reference #</th>
            <th>Customer P.O.</th>
            <th>Terms</th>
            <th>Installation Date</th>
        </tr>
        <tr>
            <td>{{ $job->job_number }}</td>
            <td>{{ $job->customer_name }} {{ $job->install_address ? substr($job->install_address, 0, 20) : '' }}</td>
            <td>{{ $settings['estimate_terms'] ?? 'Due on receipt' }}</td>
            <td>{{ $job->scheduled_date ? $job->scheduled_date->format('n/j/Y') : 'TBD' }}</td>
        </tr>
    </table>

    {{-- Items table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:90px;">Item</th>
                <th>Description</th>
                <th style="width:50px;" class="text-center">Qty</th>
                <th style="width:80px;" class="text-right">Unit Price</th>
                <th style="width:85px;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            {{-- Installation description row --}}
            @if(!empty($installDescription))
            <tr>
                <td>WD</td>
                <td>{{ $installDescription }}</td>
                <td class="text-center"></td>
                <td class="text-right">0.00</td>
                <td class="text-right">0.00</td>
            </tr>
            @endif

            {{-- Measurement items (windows/doors with sizes) --}}
            @foreach($measureItems as $item)
            <tr>
                <td>{{ $item['type'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-right">{{ number_format($item['unit_price'], 2) }}</td>
                <td class="text-right">{{ number_format($item['total'], 2) }}{{ $item['taxable'] ? 'T' : '' }}</td>
            </tr>
            @endforeach

            {{-- Service line items (installation charges) --}}
            @foreach($serviceItems as $item)
            <tr>
                <td>{{ $item['type'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-right">{{ number_format($item['unit_price'], 2) }}</td>
                <td class="text-right">{{ number_format($item['total'], 2) }}</td>
            </tr>
            @endforeach

            {{-- Empty rows for spacing --}}
            <tr><td colspan="5" style="border:none;">&nbsp;</td></tr>
        </tbody>
    </table>

    {{-- Notice --}}
    <div class="notice-box">
        Lic#{{ $settings['license_number'] ?? '' }}<br><br>
        NOTICE: CREDIT CARD FEES<br>
        Visa / MasterCard {{ $settings['cc_fee_visa'] ?? '2' }}%<br>
        American Express / Discover {{ $settings['cc_fee_amex'] ?? '2.5' }}%
    </div>

    {{-- Terms + Totals --}}
    <div class="totals-row">
        <div class="terms-text">
            {{ $settings['estimate_footer'] ?? 'If the above prices, specifications and conditions are satisfactory and hereby accepted, the company requires signatures when orders are placed. By signing, customer has agreed Not to cancel the order or put a stop payment on orders that have been paid by Visa, M/C, check and/or cash. Estimate valid only 30 days.' }}
        </div>
        <div class="totals-box">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td>${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="label">Sales Tax ({{ $taxRate }}%)</td>
                    <td>${{ number_format($taxAmount, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="label">Total</td>
                    <td>${{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Signature --}}
    <div class="signature-line">
        <span class="label">Customer Signature</span>
        <span class="line"></span>
    </div>

</div>
</body>
</html>
