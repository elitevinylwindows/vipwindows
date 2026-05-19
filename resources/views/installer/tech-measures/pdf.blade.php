<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tech Measure — {{ $measure->customer_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, Arial, sans-serif; color: #222; font-size: 11pt; }

        .page { width: 8.5in; min-height: 11in; margin: 0 auto; padding: .6in .7in; }

        /* ── Header ── */
        .header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: .4in; padding-bottom: .15in; border-bottom: 3px solid #111; }
        .header-left { flex: 0 0 auto; }
        .header-left img { height: 70px; }
        .header-center { flex: 1; text-align: center; padding: 0 20px; }
        .header-center .service-title { font-size: 16pt; font-weight: 700; color: #111; text-transform: uppercase; letter-spacing: 1px; margin-top: 6px; }
        .header-center .doc-label { font-size: 9pt; color: #888; text-transform: uppercase; letter-spacing: 2px; }
        .header-right { flex: 0 0 220px; text-align: right; font-size: 9pt; line-height: 1.6; }
        .header-right .cust-name { font-size: 11pt; font-weight: 700; color: #111; }

        /* ── Info strip ── */
        .info-strip { display: flex; gap: 20px; margin-bottom: .3in; padding: 8px 12px; background: #f5f5f5; border-radius: 4px; font-size: 9pt; }
        .info-strip .info-item { }
        .info-strip .info-label { color: #888; text-transform: uppercase; font-size: 7pt; letter-spacing: .5px; }
        .info-strip .info-value { font-weight: 600; }

        /* ── Table ── */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: .3in; }
        .items-table th {
            background: #111; color: #fff; font-size: 7.5pt; text-transform: uppercase;
            letter-spacing: .5px; padding: 6px 8px; text-align: left;
        }
        .items-table td { padding: 6px 8px; font-size: 9.5pt; border-bottom: 1px solid #e0e0e0; vertical-align: top; }
        .items-table tr:nth-child(even) td { background: #fafafa; }
        .items-table .num { text-align: center; color: #999; }
        .items-table .center { text-align: center; }

        /* ── Notes ── */
        .section-heading { font-size: 10pt; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #111; margin: .2in 0 .1in; padding-bottom: 4px; border-bottom: 1px solid #ddd; }
        .notes-block { font-size: 9.5pt; line-height: 1.6; color: #444; white-space: pre-wrap; }

        /* ── Footer ── */
        .footer { margin-top: .4in; padding-top: .1in; border-top: 2px solid #111; display: flex; justify-content: space-between; font-size: 7.5pt; color: #aaa; }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .page { padding: .5in .6in; }
            .no-print { display: none !important; }
        }

        @media screen {
            body { background: #e0e0e0; padding: 20px 0; }
            .page { background: #fff; box-shadow: 0 2px 12px rgba(0,0,0,.15); }
            .print-bar {
                position: fixed; top: 0; left: 0; right: 0; z-index: 100;
                background: #111; color: #fff; padding: 10px 24px;
                display: flex; align-items: center; justify-content: space-between;
            }
            .print-bar button {
                background: #c9a84c; color: #fff; border: none; padding: 8px 24px;
                border-radius: 4px; font-weight: 600; cursor: pointer; font-size: 10pt;
            }
            .print-bar button:hover { background: #b8973f; }
        }
    </style>
</head>
<body>
    <div class="print-bar no-print">
        <span>Tech Measure — {{ $measure->customer_name }}</span>
        <button onclick="window.print()"><i class="bi bi-printer me-1"></i> Print / Save as PDF</button>
    </div>

    <div class="page">
        {{-- Header: Logo left, Service title center, Customer info right --}}
        <div class="header">
            <div class="header-left">
                <img src="{{ public_path('images/logo.png') }}" alt="VIP Windows" onerror="this.style.display='none'">
            </div>
            <div class="header-center">
                <div class="doc-label">Tech Measure Report</div>
                <div class="service-title">{{ $serviceTitle }}</div>
            </div>
            <div class="header-right">
                <div class="cust-name">{{ $measure->customer_name ?: '—' }}</div>
                @if($measure->customer_phone)
                    <div>{{ $measure->customer_phone }}</div>
                @endif
                @if($measure->customer_email)
                    <div>{{ $measure->customer_email }}</div>
                @endif
                @if($measure->address)
                    <div>{{ $measure->address }}</div>
                @endif
                @if($measure->city || $measure->state || $measure->zip)
                    <div>{{ implode(', ', array_filter([$measure->city, $measure->state])) }} {{ $measure->zip }}</div>
                @endif
            </div>
        </div>

        {{-- Info strip --}}
        <div class="info-strip">
            <div class="info-item">
                <div class="info-label">Status</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $measure->status)) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Date</div>
                <div class="info-value">{{ $measure->created_at?->format('M d, Y') ?? '—' }}</div>
            </div>
            @if($measure->frame_type)
            <div class="info-item">
                <div class="info-label">Frame Type</div>
                <div class="info-value">{{ $measure->frame_type }}</div>
            </div>
            @endif
            @if($measure->has_grids)
            <div class="info-item">
                <div class="info-label">Grids</div>
                <div class="info-value">{{ $measure->grid_list ?: '—' }} / {{ $measure->grid_pattern ?: '—' }}</div>
            </div>
            @endif
            <div class="info-item">
                <div class="info-label">Total Openings</div>
                <div class="info-value">{{ $items->count() }}</div>
            </div>
        </div>

        {{-- Openings Table --}}
        @if($items->count())
        <div class="section-heading">Openings</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:30px;" class="center">#</th>
                    <th class="center">Qty</th>
                    <th>Width</th>
                    <th>Height</th>
                    <th>Unit (Configuration)</th>
                    <th>Reference</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $idx => $item)
                <tr>
                    <td class="num">{{ $idx + 1 }}</td>
                    <td class="center">{{ $item->qty ?: 1 }}</td>
                    <td>{{ $item->width ?: '—' }}</td>
                    <td>{{ $item->height ?: '—' }}</td>
                    <td>{{ $item->description ?: '—' }}</td>
                    <td>{{ $item->room_label ?: '—' }}</td>
                    <td style="font-size:8.5pt;">{{ $item->notes ?: '' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <p style="color:#999; font-style:italic; margin: .2in 0;">No openings recorded.</p>
        @endif

        {{-- Notes --}}
        @if($measure->notes)
        <div class="section-heading">General Notes</div>
        <div class="notes-block">{{ $measure->notes }}</div>
        @endif

        {{-- Footer --}}
        <div class="footer">
            <div>VIP Windows &mdash; Tech Measure Report</div>
            <div>Generated {{ now()->format('M d, Y \a\t g:i A') }}</div>
        </div>
    </div>
</body>
</html>
