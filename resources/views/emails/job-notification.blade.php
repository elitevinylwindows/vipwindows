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

                    {{-- Header --}}
                    <tr>
                        <td style="background:linear-gradient(135deg, #0a0a0a, #1a1a1a); padding:30px 40px; text-align:center; border-radius:12px 12px 0 0;">
                            <img src="https://vipwindowsinc.com/images/logo.png" alt="VIP Windows" style="height:60px; max-width:200px;" />
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background:#ffffff; padding:35px 40px;">
                            <div style="font-size:15px; color:#333; line-height:1.7;">
                                {!! nl2br(e($emailBody)) !!}
                            </div>

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
