<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Estimate — {{ $job->customer_name }}</title>
    <style>
        @page { margin: 0.4in 0.4in 0.5in 0.4in; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; color: #000; font-size: 9.5pt; }
        .page { width: 100%; }

        /* ── Header ── */
        .header-table { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .company-info { font-size: 9.5pt; line-height: 1.5; }
        .company-name { font-weight: bold; font-size: 10.5pt; }
        .estimate-title { font-size: 22pt; font-weight: bold; text-align: right; }
        .date-box { margin-top: 4px; }
        .date-box table { border-collapse: collapse; margin-left: auto; }
        .date-box th, .date-box td { border: 1px solid #000; padding: 2px 10px; font-size: 8.5pt; }
        .date-box th { background: #eee; font-weight: bold; }

        /* ── Address blocks side-by-side ── */
        .address-table { width: 100%; margin-bottom: 14px; border-collapse: collapse; }
        .address-table td { vertical-align: top; }
        .addr-box { border: 1px solid #000; }
        .addr-label { background: #eee; border-bottom: 1px solid #000; padding: 2px 6px; font-weight: bold; font-size: 8.5pt; }
        .addr-content { padding: 6px; font-size: 9pt; line-height: 1.5; }

        /* ── Reference row ── */
        .ref-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .ref-table th, .ref-table td { border: 1px solid #000; padding: 3px 8px; font-size: 8.5pt; text-align: center; }
        .ref-table th { background: #eee; font-weight: bold; }

        /* ── Items table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .items-table th { background: #333; color: #fff; padding: 4px 6px; font-size: 8pt; text-align: left; font-weight: bold; border: 1px solid #333; }
        .items-table td { padding: 3px 6px; font-size: 8.5pt; border: 1px solid #ccc; vertical-align: top; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── Notice ── */
        .notice-box { border: 1px solid #999; padding: 6px 10px; font-size: 8pt; line-height: 1.5; display: inline-block; margin-top: 8px; }

        /* ── Footer: Terms + Totals ── */
        .footer-table { width: 100%; margin-top: 12px; border-collapse: collapse; }
        .footer-table td { vertical-align: top; }
        .terms-text { font-size: 7.5pt; line-height: 1.5; color: #333; padding-right: 15px; }
        .totals-box table { border-collapse: collapse; margin-left: auto; }
        .totals-box td { padding: 2px 12px; font-size: 9.5pt; }
        .totals-box .label { font-weight: bold; text-align: left; }
        .totals-box .total-row td { border-top: 2px solid #000; font-size: 12pt; font-weight: bold; padding-top: 4px; }

        /* ── Signature ── */
        .signature-line { margin-top: 25px; font-size: 8.5pt; }
        .sig-table { width: 55%; border-collapse: collapse; }
        .sig-table td.sig-label { font-weight: bold; white-space: nowrap; padding-right: 8px; }
        .sig-table td.sig-line { border-bottom: 1px solid #000; width: 100%; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header: company info left, Estimate title + date right --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top; width: 50%;">
                <div class="company-info">
                    <div class="company-name">{{ $settings['company_name'] ?? 'VIP Windows Inc.' }}</div>
                    <div>{{ $settings['company_address'] ?? '4231 Liberty Blvd.' }}</div>
                    <div>{{ $settings['company_city'] ?? 'South Gate' }}, {{ $settings['company_state'] ?? 'CA' }} {{ $settings['company_zip'] ?? '90280' }}</div>
                    <div>Phone: {{ $settings['company_phone'] ?? '(562) 368-0313' }}</div>
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 50%;">
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
            </td>
        </tr>
    </table>

    {{-- Address blocks: Name/Address left, Job Address right — same line --}}
    <table class="address-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 48%; vertical-align: top;">
                <div class="addr-box">
                    <div class="addr-label">Name / Address</div>
                    <div class="addr-content">
                        {{ strtoupper($job->customer_name) }}<br>
                        @if($job->install_address){{ strtoupper($job->install_address) }}<br>@endif
                        @if($job->install_city || $job->install_state){{ strtoupper($job->install_city) }}, {{ strtoupper($job->install_state) }} {{ $job->install_zip }}<br>@endif
                        @if($job->customer_phone){{ $job->customer_phone }}@endif
                    </div>
                </div>
            </td>
            <td style="width: 4%;"></td>
            <td style="width: 48%; vertical-align: top;">
                <div class="addr-box">
                    <div class="addr-label">Job Address</div>
                    <div class="addr-content">
                        {{ $job->customer_name }}<br>
                        @if($job->install_address){{ $job->install_address }}<br>@endif
                        @if($job->install_city){{ $job->install_city }}, {{ $job->install_state }} {{ $job->install_zip }}<br>@endif
                        @if($job->customer_phone){{ $job->customer_phone }}@endif
                    </div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Reference row --}}
    <table class="ref-table" cellpadding="0" cellspacing="0">
        <tr>
            <th style="width: 18%;">Reference #</th>
            <th style="width: 37%;">Customer P.O.</th>
            <th style="width: 20%;">Terms</th>
            <th style="width: 25%;">Installation Date</th>
        </tr>
        <tr>
            <td>{{ $estimateNumber }}</td>
            <td>{{ $job->customer_name }} {{ $job->install_address }}</td>
            <td>{{ $settings['estimate_terms'] ?? 'Due on receipt' }}</td>
            <td>{{ $job->scheduled_date ? $job->scheduled_date->format('n/j/Y') : 'TBD' }}</td>
        </tr>
    </table>

    {{-- Items table --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 85px;">Item</th>
                <th>Description</th>
                <th style="width: 40px;" class="text-center">Qty</th>
                <th style="width: 75px;" class="text-right">Unit Price</th>
                <th style="width: 80px;" class="text-right">Total</th>
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
                <td>{{ $item['description'] }} charge</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-right">{{ number_format($item['unit_price'], 2) }}</td>
                <td class="text-right">{{ number_format($item['total'], 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Notice --}}
    <div class="notice-box">
        Lic#{{ $settings['license_number'] ?? '' }}<br><br>
        NOTICE: CREDIT CARD FEES<br>
        Visa / MasterCard {{ $settings['cc_fee_visa'] ?? '2' }}%<br>
        American Express / Discover {{ $settings['cc_fee_amex'] ?? '2.5' }}%
    </div>

    {{-- Terms + Totals side by side --}}
    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="terms-text">
                    {{ $settings['estimate_footer'] ?? 'If the above prices, specifications and conditions are satisfactory and hereby accepted, the company requires signatures when orders are placed. By signing, customer has agreed Not to cancel the order or put a stop payment on orders that have been paid by Visa, M/C, check and/or cash. Estimate valid only 30 days.' }}
                </div>
            </td>
            <td style="width: 45%; vertical-align: top;">
                <div class="totals-box">
                    <table>
                        <tr>
                            <td class="label">Subtotal</td>
                            <td class="text-right">${{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="label">Sales Tax ({{ $taxRate }}%)</td>
                            <td class="text-right">${{ number_format($taxAmount, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td class="label">Total</td>
                            <td class="text-right">${{ number_format($total, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </td>
        </tr>
    </table>

    {{-- Signature --}}
    <div class="signature-line">
        <table class="sig-table">
            <tr>
                <td class="sig-label">Customer Signature</td>
                <td class="sig-line">&nbsp;</td>
            </tr>
        </table>
    </div>

</div>
</body>
</html>
