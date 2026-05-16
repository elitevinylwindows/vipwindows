<div style="font-family: 'Segoe UI', Arial, sans-serif; max-width: 650px; margin: 0 auto;">
    <div style="background: #111; padding: 20px; text-align: center;">
        <h1 style="color: #c9a84c; margin: 0; font-size: 24px;">VIP Windows</h1>
        <p style="color: #888; margin: 5px 0 0; font-size: 14px;">Professional Window Installation</p>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #eee;">
        <h2 style="color: #111; margin-top: 0;">Quote #{{ $quote->quote_number }}</h2>

        @if($quote->billing_name)
            <p><strong>Customer:</strong> {{ $quote->billing_name }}</p>
        @endif
        <p><strong>Date:</strong> {{ $quote->created_at?->format('F j, Y') }}</p>
        @if($quote->valid_until)
            <p><strong>Valid Until:</strong> {{ $quote->valid_until }}</p>
        @endif

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background: #f8f8f8;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">#</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Description</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Size</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Qty</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items as $i => $item)
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">{{ $i + 1 }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">{{ $item->description }}<br><small style="color:#888;">{{ $item->series_type }}</small></td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">{{ $item->width }}" x {{ $item->height }}"</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">{{ $item->qty }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${{ number_format($item->getRawOriginal('total'), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background: #f8f8f8; font-weight: bold;">
                    <td colspan="4" style="padding: 10px; border: 1px solid #ddd; text-align: right;">Total</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${{ number_format($quote->items->sum(fn($i) => $i->getRawOriginal('total')), 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <p>If you have any questions about this quote, please contact us:</p>
        <p><strong>Phone:</strong> (562) 368-0313<br><strong>Email:</strong> info@vipwindows.net</p>

        <p style="margin-top: 30px;">Best regards,<br><strong>VIP Windows Team</strong></p>
    </div>
    <div style="background: #111; padding: 15px; text-align: center; color: #888; font-size: 12px;">
        VIP Windows &mdash; Professional Window Installation<br>
        (562) 368-0313 &bull; info@vipwindows.net
    </div>
</div>
