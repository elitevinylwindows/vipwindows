<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Estimate — {{ $job->customer_name }}</title>
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #000;
            font-size: 8pt;
            padding: 30px 40px 30px 40px;
        }

        /* ── Master wrapper — hardcoded pixel width ── */
        .page {
            width: 535px;
            overflow: hidden;
        }

        /* ── Header ── */
        .header-table { width: 535px; margin-bottom: 12px; border-collapse: collapse; }
        .company-info { font-size: 8pt; line-height: 1.4; }
        .company-name { font-weight: bold; font-size: 9pt; }
        .estimate-title { font-size: 14pt; font-weight: bold; text-align: right; }
        .date-box { margin-top: 3px; }
        .date-box table { border-collapse: collapse; margin-left: auto; width: auto; }
        .date-box th, .date-box td { border: 1px solid #000; padding: 2px 6px; font-size: 7pt; }
        .date-box th { background: #eee; font-weight: bold; }

        /* ── Address blocks ── */
        .address-table { width: 535px; margin-bottom: 10px; border-collapse: collapse; }
        .address-table td { vertical-align: top; }
        .addr-box { border: 1px solid #000; }
        .addr-label { background: #eee; border-bottom: 1px solid #000; padding: 2px 4px; font-weight: bold; font-size: 7pt; }
        .addr-content { padding: 3px 4px; font-size: 7.5pt; line-height: 1.3; }

        /* ── Reference row ── */
        .ref-table { width: 535px; border-collapse: collapse; margin-bottom: 8px; }
        .ref-table th, .ref-table td { border: 1px solid #000; padding: 2px 4px; font-size: 7pt; text-align: center; }
        .ref-table th { background: #eee; font-weight: bold; }

        /* ── Items table ── */
        .items-table { width: 535px; border-collapse: collapse; margin-bottom: 6px; }
        .items-table th { background: #333; color: #fff; padding: 2px 4px; font-size: 6.5pt; text-align: left; font-weight: bold; border: 1px solid #333; }
        .items-table td { padding: 2px 4px; font-size: 7pt; border: 1px solid #ccc; vertical-align: top; word-wrap: break-word; overflow: hidden; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* ── Notice ── */
        .notice-box { border: 1px solid #999; padding: 4px 6px; font-size: 6.5pt; line-height: 1.3; margin-top: 5px; width: 250px; }

        /* ── Footer ── */
        .footer-table { width: 535px; margin-top: 8px; border-collapse: collapse; }
        .footer-table td { vertical-align: top; }
        .terms-text { font-size: 6pt; line-height: 1.3; color: #333; padding-right: 8px; }
        .totals-box table { border-collapse: collapse; margin-left: auto; width: auto; }
        .totals-box td { padding: 2px 8px; font-size: 8pt; }
        .totals-box .label { font-weight: bold; text-align: left; }
        .totals-box .total-row td { border-top: 2px solid #000; font-size: 9pt; font-weight: bold; padding-top: 2px; }

        /* ── Signature ── */
        .signature-line { margin-top: 16px; font-size: 7pt; }
        .sig-table { width: 260px; border-collapse: collapse; }
        .sig-table td.sig-label { font-weight: bold; white-space: nowrap; padding-right: 5px; }
        .sig-table td.sig-line { border-bottom: 1px solid #000; width: 180px; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: top; width: 267px;">
                <div class="company-info">
                    <div class="company-name">{{ $settings['company_name'] ?? 'VIP Windows Inc.' }}</div>
                    <div>{{ $settings['company_address'] ?? '4231 Liberty Blvd.' }}</div>
                    <div>{{ $settings['company_city'] ?? 'South Gate' }}, {{ $settings['company_state'] ?? 'CA' }} {{ $settings['company_zip'] ?? '90280' }}</div>
                    <div>Phone: {{ $settings['company_phone'] ?? '(562) 368-0313' }}</div>
                </div>
            </td>
            <td style="vertical-align: top; text-align: right; width: 268px;">
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

    {{-- Address blocks --}}
    <table class="address-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 255px; vertical-align: top;">
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
            <td style="width: 25px;"></td>
            <td style="width: 255px; vertical-align: top;">
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
            <th style="width: 95px;">Reference #</th>
            <th style="width: 200px;">Customer P.O.</th>
            <th style="width: 105px;">Terms</th>
            <th style="width: 135px;">Installation Date</th>
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
                <th style="width: 65px;">Item</th>
                <th style="width: 270px;">Description</th>
                <th style="width: 30px;" class="text-center">Qty</th>
                <th style="width: 70px;" class="text-right">Unit Price</th>
                <th style="width: 70px;" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($installDescription))
            <tr>
                <td>WD</td>
                <td>{{ $installDescription }}</td>
                <td class="text-center"></td>
                <td class="text-right">0.00</td>
                <td class="text-right">0.00</td>
            </tr>
            @endif

            @foreach($measureItems as $item)
            <tr>
                <td>{{ $item['type'] }}</td>
                <td>{{ $item['description'] }}</td>
                <td class="text-center">{{ $item['qty'] }}</td>
                <td class="text-right">{{ number_format($item['unit_price'], 2) }}</td>
                <td class="text-right">{{ number_format($item['total'], 2) }}{{ $item['taxable'] ? 'T' : '' }}</td>
            </tr>
            @endforeach

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

    {{-- Terms + Totals --}}
    <table class="footer-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 295px; vertical-align: top;">
                <div class="terms-text">
                    {{ $settings['estimate_footer'] ?? 'If the above prices, specifications and conditions are satisfactory and hereby accepted, the company requires signatures when orders are placed. By signing, customer has agreed Not to cancel the order or put a stop payment on orders that have been paid by Visa, M/C, check and/or cash. Estimate valid only 30 days.' }}
                </div>
            </td>
            <td style="width: 240px; vertical-align: top;">
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
