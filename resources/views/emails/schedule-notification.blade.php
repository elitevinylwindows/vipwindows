<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background:#f4f3f0; font-family:'Segoe UI', Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f3f0; padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%;">

                    {{-- Header with logo --}}
                    <tr>
                        <td style="background:linear-gradient(135deg, #0a0a0a, #1a1a1a); padding:30px 40px; text-align:center; border-radius:12px 12px 0 0;">
                            <img src="https://vipwindowsinc.com/images/logo.png" alt="VIP Windows" style="height:60px; max-width:200px;" />
                            <p style="color:#c9a84c; margin:10px 0 0; font-size:13px; letter-spacing:2px; text-transform:uppercase;">
                                {{ $type === 'job' ? 'Installation Scheduled' : 'Appointment Confirmation' }}
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background:#ffffff; padding:35px 40px;">
                            <p style="font-size:16px; color:#333; margin:0 0 20px;">Hi {{ $customerName }},</p>
                            <p style="font-size:15px; color:#555; line-height:1.6; margin:0 0 25px;">
                                {{ $type === 'job' ? 'Your installation has been scheduled.' : 'Your appointment has been confirmed.' }}
                                Here are the details:
                            </p>

                            {{-- Details card --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#faf9f6; border:1px solid #eee; border-radius:8px; overflow:hidden;">
                                <tr>
                                    <td style="padding:20px 25px;">
                                        {{-- Service badge + Event Title --}}
                                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:15px;">
                                            @if($serviceName)
                                            <tr>
                                                <td style="padding-bottom:8px;">
                                                    <span style="display:inline-block; background:#c9a84c; color:#fff; font-size:11px; font-weight:600; padding:3px 10px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px;">{{ $serviceName }}</span>
                                                </td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="font-size:18px; font-weight:700; color:#111;">{{ $eventTitle }}</td>
                                            </tr>
                                        </table>

                                        {{-- Date --}}
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="35" style="vertical-align:top; padding:6px 0;">
                                                    <span style="font-size:18px;">&#128197;</span>
                                                </td>
                                                <td style="padding:6px 0;">
                                                    <span style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px;">Date</span><br>
                                                    <span style="font-size:15px; font-weight:600; color:#111;">{{ \Carbon\Carbon::parse($eventDate)->format('l, F j, Y') }}</span>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Time --}}
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="35" style="vertical-align:top; padding:6px 0;">
                                                    <span style="font-size:18px;">&#128336;</span>
                                                </td>
                                                <td style="padding:6px 0;">
                                                    <span style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px;">Time</span><br>
                                                    <span style="font-size:15px; font-weight:600; color:#111;">
                                                        @if($startTime)
                                                            {{ $startTime }}@if($endTime) &mdash; {{ $endTime }}@endif
                                                        @else
                                                            To be confirmed
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>

                                        @if($address)
                                        {{-- Location --}}
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="35" style="vertical-align:top; padding:6px 0;">
                                                    <span style="font-size:18px;">&#128205;</span>
                                                </td>
                                                <td style="padding:6px 0;">
                                                    <span style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px;">Location</span><br>
                                                    <span style="font-size:15px; font-weight:600; color:#111;">{{ $address }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            @if($description)
                            <div style="margin-top:20px; padding:15px 20px; background:#f0f0f0; border-radius:6px;">
                                <p style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px; margin:0 0 5px;">Additional Details</p>
                                <p style="font-size:14px; color:#444; margin:0; line-height:1.5;">{{ $description }}</p>
                            </div>
                            @endif

                            {{-- CTA --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
                                <tr>
                                    <td align="center">
                                        <p style="font-size:14px; color:#666; margin:0 0 15px;">Need to reschedule or have questions?</p>
                                        <a href="tel:+15623680313" style="display:inline-block; background:#c9a84c; color:#fff; padding:12px 30px; border-radius:6px; text-decoration:none; font-weight:600; font-size:14px;">
                                            Call (562) 368-0313
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#111; padding:25px 40px; text-align:center; border-radius:0 0 12px 12px;">
                            <p style="color:#c9a84c; font-weight:700; font-size:14px; margin:0 0 5px;">VIP Windows</p>
                            <p style="color:#888; font-size:12px; margin:0 0 3px;">Professional Window & Door Installation</p>
                            <p style="color:#666; font-size:11px; margin:10px 0 0;">
                                (562) 368-0313 &bull; info@vipwindows.net &bull; vipwindows.net
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
