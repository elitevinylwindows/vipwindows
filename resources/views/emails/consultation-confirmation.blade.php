<div style="font-family: 'Segoe UI', Arial, sans-serif; max-width: 600px; margin: 0 auto;">
    <div style="background: #111; padding: 20px; text-align: center;">
        <h1 style="color: #c9a84c; margin: 0; font-size: 24px;">VIP Windows</h1>
    </div>
    <div style="background: #fff; padding: 30px; border: 1px solid #eee;">
        <h2 style="color: #111; margin-top: 0;">Your Virtual Consultation is Confirmed</h2>
        <p>Hello {{ $customer_name }},</p>
        <p>Your virtual consultation with VIP Windows has been scheduled. Here are the details:</p>

        <div style="background: #f8f8f8; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #666;">Date & Time:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ $formatted_date }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Duration:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ $duration }} minutes</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #666;">Platform:</td>
                    <td style="padding: 8px 0; font-weight: bold;">{{ ucfirst($platform) }}</td>
                </tr>
                @if(!empty($meeting_link))
                <tr>
                    <td style="padding: 8px 0; color: #666;">Meeting Link:</td>
                    <td style="padding: 8px 0;"><a href="{{ $meeting_link }}" style="color: #c9a84c;">Join Meeting</a></td>
                </tr>
                @endif
            </table>
        </div>

        <p><strong>What to expect:</strong></p>
        <p>During the consultation, our team will review your windows, take virtual measurements, and provide you with a detailed estimate for your project.</p>

        <p>If you need to reschedule, please call us at <strong>(562) 368-0313</strong> or reply to this email.</p>

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
