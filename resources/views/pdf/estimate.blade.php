<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Estimate</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #000;
            font-size: 9pt;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        table { border-collapse: collapse; }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .small { font-size: 8pt; }
        .xsmall { font-size: 7pt; }
    </style>
</head>
<body>

{{-- ═══════ HEADER ═══════ --}}
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="50%" valign="top">
            <span style="font-weight:bold; font-size:11pt;">{{ $settings['company_name'] ?? 'VIP Windows Inc.' }}</span><br>
            {{ $settings['company_address'] ?? '4231 Liberty Blvd.' }}<br>
            {{ $settings['company_city'] ?? 'South Gate' }}, {{ $settings['company_state'] ?? 'CA' }} {{ $settings['company_zip'] ?? '90280' }}<br>
            Phone: {{ $settings['company_phone'] ?? '(562) 368-0313' }}
        </td>
        <td width="50%" valign="top" align="right">
            <span style="font-size:18pt; font-weight:bold;">Estimate</span><br><br>
            <table cellpadding="0" cellspacing="0" align="right" style="border:1px solid #000;">
                <tr>
                    <td style="background:#eee; border:1px solid #000; padding:2pt 8pt; font-weight:bold; font-size:8pt;">Date</td>
                    <td style="background:#eee; border:1px solid #000; padding:2pt 8pt; font-weight:bold; font-size:8pt;">Estimate #</td>
                </tr>
                <tr>
                    <td style="border:1px solid #000; padding:2pt 8pt; font-size:8pt; text-align:center;">{{ $estimateDate }}</td>
                    <td style="border:1px solid #000; padding:2pt 8pt; font-size:8pt; text-align:center;">{{ $estimateNumber }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>

{{-- ═══════ ADDRESS BLOCKS ═══════ --}}
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="48%" valign="top">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #000;">
                <tr>
                    <td style="background:#eee; border-bottom:1px solid #000; padding:2pt 5pt; font-weight:bold; font-size:8pt;">Name / Address</td>
                </tr>
                <tr>
                    <td style="padding:4pt 5pt; font-size:8pt; line-height:1.4;">
                        {{ strtoupper($job->customer_name) }}<br>
                        @if($job->install_address){{ strtoupper($job->install_address) }}<br>@endif
                        @if($job->install_city || $job->install_state){{ strtoupper($job->install_city) }}, {{ strtoupper($job->install_state) }} {{ $job->install_zip }}<br>@endif
                        @if($job->customer_phone){{ $job->customer_phone }}@endif
                    </td>
                </tr>
            </table>
        </td>
        <td width="4%">&nbsp;</td>
        <td width="48%" valign="top">
            <table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #000;">
                <tr>
                    <td style="background:#eee; border-bottom:1px solid #000; padding:2pt 5pt; font-weight:bold; font-size:8pt;">Job Address</td>
                </tr>
                <tr>
                    <td style="padding:4pt 5pt; font-size:8pt; line-height:1.4;">
                        {{ $job->customer_name }}<br>
                        @if($job->install_address){{ $job->install_address }}<br>@endif
                        @if($job->install_city){{ $job->install_city }}, {{ $job->install_state }} {{ $job->install_zip }}<br>@endif
                        @if($job->customer_phone){{ $job->customer_phone }}@endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br>

{{-- ═══════ REFERENCE ROW ═══════ --}}
<table width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #000;">
    <tr>
        <td width="18%" style="background:#eee; border:1px solid #000; padding:2pt 5pt; font-weight:bold; font-size:8pt; text-align:center;">Reference #</td>
        <td width="37%" style="background:#eee; border:1px solid #000; padding:2pt 5pt; font-weight:bold; font-size:8pt; text-align:center;">Customer P.O.</td>
        <td width="20%" style="background:#eee; border:1px solid #000; padding:2pt 5pt; font-weight:bold; font-size:8pt; text-align:center;">Terms</td>
        <td width="25%" style="background:#eee; border:1px solid #000; padding:2pt 5pt; font-weight:bold; font-size:8pt; text-align:center;">Installation Date</td>
    </tr>
    <tr>
        <td style="border:1px solid #000; padding:2pt 5pt; font-size:8pt; text-align:center;">{{ $estimateNumber }}</td>
        <td style="border:1px solid #000; padding:2pt 5pt; font-size:8pt; text-align:center;">{{ $job->customer_name }} {{ $job->install_address }}</td>
        <td style="border:1px solid #000; padding:2pt 5pt; font-size:8pt; text-align:center;">{{ $settings['estimate_terms'] ?? 'Due on receipt' }}</td>
        <td style="border:1px solid #000; padding:2pt 5pt; font-size:8pt; text-align:center;">{{ $job->scheduled_date ? $job->scheduled_date->format('n/j/Y') : 'TBD' }}</td>
    </tr>
</table>

<br>

{{-- ═══════ ITEMS TABLE ═══════ --}}
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="12%" style="background:#333; color:#fff; padding:3pt 5pt; font-size:7pt; font-weight:bold; border:1px solid #333;">Item</td>
        <td width="50%" style="background:#333; color:#fff; padding:3pt 5pt; font-size:7pt; font-weight:bold; border:1px solid #333;">Description</td>
        <td width="8%" style="background:#333; color:#fff; padding:3pt 5pt; font-size:7pt; font-weight:bold; border:1px solid #333; text-align:center;">Qty</td>
        <td width="15%" style="background:#333; color:#fff; padding:3pt 5pt; font-size:7pt; font-weight:bold; border:1px solid #333; text-align:right;">Unit Price</td>
        <td width="15%" style="background:#333; color:#fff; padding:3pt 5pt; font-size:7pt; font-weight:bold; border:1px solid #333; text-align:right;">Total</td>
    </tr>
    @if(!empty($installDescription))
    <tr>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; vertical-align:top;">WD</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; vertical-align:top;">{{ $installDescription }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:center;"></td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:right;">0.00</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:right;">0.00</td>
    </tr>
    @endif
    @foreach($measureItems as $item)
    <tr>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; vertical-align:top;">{{ $item['type'] }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; vertical-align:top;">{{ $item['description'] }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:center;">{{ $item['qty'] }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:right;">{{ number_format($item['unit_price'], 2) }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:right;">{{ number_format($item['total'], 2) }}{{ $item['taxable'] ? 'T' : '' }}</td>
    </tr>
    @endforeach
    @foreach($serviceItems as $item)
    <tr>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; vertical-align:top;">{{ $item['type'] }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; vertical-align:top;">{{ $item['description'] }} charge</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:center;">{{ $item['qty'] }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:right;">{{ number_format($item['unit_price'], 2) }}</td>
        <td style="border:1px solid #ccc; padding:2pt 5pt; font-size:8pt; text-align:right;">{{ number_format($item['total'], 2) }}</td>
    </tr>
    @endforeach
</table>

<br>

{{-- ═══════ NOTICE ═══════ --}}
<table cellpadding="0" cellspacing="0" style="border:1px solid #999;">
    <tr>
        <td style="padding:4pt 6pt; font-size:7pt; line-height:1.4;">
            Lic#{{ $settings['license_number'] ?? '' }}<br><br>
            NOTICE: CREDIT CARD FEES<br>
            Visa / MasterCard {{ $settings['cc_fee_visa'] ?? '2' }}%<br>
            American Express / Discover {{ $settings['cc_fee_amex'] ?? '2.5' }}%
        </td>
    </tr>
</table>

<br>

{{-- ═══════ TERMS + TOTALS ═══════ --}}
<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td width="55%" valign="top" style="font-size:7pt; line-height:1.4; color:#333; padding-right:10pt;">
            {{ $settings['estimate_footer'] ?? 'If the above prices, specifications and conditions are satisfactory and hereby accepted, the company requires signatures when orders are placed. By signing, customer has agreed Not to cancel the order or put a stop payment on orders that have been paid by Visa, M/C, check and/or cash. Estimate valid only 30 days.' }}
        </td>
        <td width="45%" valign="top" align="right">
            <table cellpadding="0" cellspacing="0" align="right">
                <tr>
                    <td style="padding:2pt 8pt; font-size:9pt; font-weight:bold; text-align:left;">Subtotal</td>
                    <td style="padding:2pt 8pt; font-size:9pt; text-align:right;">${{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:2pt 8pt; font-size:9pt; font-weight:bold; text-align:left;">Sales Tax ({{ $taxRate }}%)</td>
                    <td style="padding:2pt 8pt; font-size:9pt; text-align:right;">${{ number_format($taxAmount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:3pt 8pt; font-size:11pt; font-weight:bold; text-align:left; border-top:2pt solid #000;">Total</td>
                    <td style="padding:3pt 8pt; font-size:11pt; font-weight:bold; text-align:right; border-top:2pt solid #000;">${{ number_format($total, 2) }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<br><br>

{{-- ═══════ SIGNATURE ═══════ --}}
<table cellpadding="0" cellspacing="0">
    <tr>
        <td style="font-size:8pt; font-weight:bold; padding-right:5pt; white-space:nowrap;">Customer Signature</td>
        <td style="border-bottom:1px solid #000; width:200pt;">&nbsp;</td>
    </tr>
</table>

</body>
</html>
