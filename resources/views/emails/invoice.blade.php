<div style="font-family: 'Segoe UI', Arial, sans-serif; max-width: 650px; margin: 0 auto;">
    <div style="background: #111; padding: 20px; text-align: center;">
        <h1 style="color: #c9a84c; margin: 0; font-size: 24px;">VIP Windows</h1>
        <p style="color: #888; margin: 5px 0 0; font-size: 14px;">Professional Window Installation</p>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #eee;">
        <h2 style="color: #111; margin-top: 0;">Invoice #{{ $invoice->invoice_number }}</h2>

        <table style="width: 100%; margin-bottom: 20px;">
            <tr>
                <td style="vertical-align: top; width: 50%;">
                    <p><strong>Bill To:</strong></p>
                    <p>
                        {{ $invoice->customer_name }}<br>
                        @if($invoice->customer_email){{ $invoice->customer_email }}<br>@endif
                        @if($invoice->customer_phone){{ $invoice->customer_phone }}<br>@endif
                        @if($invoice->billing_address ?: $invoice->customer_address){{ $invoice->billing_address ?: $invoice->customer_address }}@endif
                    </p>
                </td>
                <td style="vertical-align: top; width: 50%; text-align: right;">
                    <p><strong>Date:</strong> {{ $invoice->created_at?->format('F j, Y') }}</p>
                    @if($invoice->due_date)
                        <p><strong>Due Date:</strong> {{ $invoice->due_date->format('F j, Y') }}</p>
                    @endif
                </td>
            </tr>
        </table>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead>
                <tr style="background: #f8f8f8;">
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">#</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: left;">Description</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: center;">Qty</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Unit Price</th>
                    <th style="padding: 10px; border: 1px solid #ddd; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $i => $item)
                    <tr>
                        <td style="padding: 8px; border: 1px solid #ddd;">{{ $i + 1 }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd;">{{ $item->description }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">{{ $item->qty }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${{ number_format($item->unit_price, 2) }}</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="padding: 8px; border: 1px solid #ddd; text-align: right;">Subtotal</td>
                    <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${{ number_format($invoice->subtotal, 2) }}</td>
                </tr>
                @if($invoice->tax_rate > 0)
                    <tr>
                        <td colspan="4" style="padding: 8px; border: 1px solid #ddd; text-align: right;">Tax ({{ number_format($invoice->tax_rate, 2) }}%)</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">${{ number_format($invoice->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr style="background: #f8f8f8; font-weight: bold;">
                    <td colspan="4" style="padding: 10px; border: 1px solid #ddd; text-align: right;">Total</td>
                    <td style="padding: 10px; border: 1px solid #ddd; text-align: right;">${{ number_format($invoice->total, 2) }}</td>
                </tr>
                @if($invoice->amount_paid > 0)
                    <tr>
                        <td colspan="4" style="padding: 8px; border: 1px solid #ddd; text-align: right; color: #28a745;">Amount Paid</td>
                        <td style="padding: 8px; border: 1px solid #ddd; text-align: right; color: #28a745;">${{ number_format($invoice->amount_paid, 2) }}</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td colspan="4" style="padding: 10px; border: 1px solid #ddd; text-align: right; color: #dc3545;">Balance Due</td>
                        <td style="padding: 10px; border: 1px solid #ddd; text-align: right; color: #dc3545;">${{ number_format($invoice->balance_due, 2) }}</td>
                    </tr>
                @endif
            </tfoot>
        </table>

        @if($invoice->notes)
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0; font-size: 14px; color: #666;"><strong>Notes:</strong> {{ $invoice->notes }}</p>
            </div>
        @endif

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <p>If you have any questions about this invoice, please contact us:</p>
            <p><strong>Phone:</strong> (562) 368-0313<br><strong>Email:</strong> info@vipwindows.net</p>
        </div>

        <p style="margin-top: 30px;">Best regards,<br><strong>VIP Windows Team</strong></p>
    </div>
    <div style="background: #111; padding: 15px; text-align: center; color: #888; font-size: 12px;">
        VIP Windows &mdash; Professional Window Installation<br>
        (562) 368-0313 &bull; info@vipwindows.net
    </div>
</div>
