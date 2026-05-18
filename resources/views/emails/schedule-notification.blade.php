<div style="font-family: 'Segoe UI', Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background: #111; padding: 20px; text-align: center;">
        <h1 style="color: #c9a84c; margin: 0; font-size: 24px;">VIP Windows</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #eee;">
        <h2 style="color: #111; margin-top: 0;">{{ $type === 'job' ? 'Installation Scheduled' : 'Appointment Scheduled' }}</h2>
        <p>Hello {{ $customerName }},</p>
        <p>{{ $type === 'job' ? 'Your installation has been scheduled with VIP Windows.' : 'An appointment has been scheduled for you with VIP Windows.' }} Here are the details:</p>

        <div style="background: #f8f8f8; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #666; width: 120px;">Event:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ $eventTitle }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Date:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ \Carbon\Carbon::parse($eventDate)->format('l, F j, Y') }}</td>
                </tr>
                @if($startTime)
                <tr>
                    <td style="padding: 8px 0; color: #666;">Time:</td>
                    <td style="padding: 8px 0; font-weight: bold;">
                        {{ $startTime }}@if($endTime) &mdash; {{ $endTime }}@endif
                    </td>
                </tr>
                @endif
                @if($address)
                <tr>
                    <td style="padding: 8px 0; color: #666;">Location:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ $address }}</td>
                </tr>
                @endif
            </table>
        </div>

        @if($description)
        <p><strong>Details:</strong></p>
        <p>{{ $description }}</p>
        @endif

        <p>If you need to reschedule or have any questions, please call us at <strong>(562) 368-0313</strong> or reply to this email.</p>

        <p style="margin-top: 30px;">
            Best regards,<br>
            <strong>VIP Windows Team</strong>
        </p>
    </div>
    <div style="background: #111; padding: 15px; text-align: center; color: #888; font-size: 12px;">
        VIP Windows &mdash; Professional Window Installation<br>
        (562) 368-0313 &bull; info@vipwindows.net
    </div>
</div>
