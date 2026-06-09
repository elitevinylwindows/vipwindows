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
                                Team Invitation
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="background:#ffffff; padding:35px 40px;">
                            <p style="font-size:16px; color:#333; margin:0 0 20px;">Hi {{ $memberName }},</p>
                            <p style="font-size:15px; color:#555; line-height:1.6; margin:0 0 25px;">
                                Welcome to the VIP Windows team! {{ $invitedBy }} has created an account for you.
                                You've been added as
                                <strong style="color:#111;">{{ ucfirst($memberRole) }}</strong>.
                            </p>

                            {{-- Credentials card --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#faf9f6; border:1px solid #eee; border-radius:8px; overflow:hidden;">
                                <tr>
                                    <td style="padding:20px 25px;">
                                        <p style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px; margin:0 0 12px; font-weight:600;">Your Login Credentials</p>

                                        {{-- Email --}}
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="35" style="vertical-align:top; padding:6px 0;">
                                                    <span style="font-size:18px;">&#9993;</span>
                                                </td>
                                                <td style="padding:6px 0;">
                                                    <span style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px;">Email</span><br>
                                                    <span style="font-size:15px; font-weight:600; color:#111;">{{ $memberEmail }}</span>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Password --}}
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="35" style="vertical-align:top; padding:6px 0;">
                                                    <span style="font-size:18px;">&#128274;</span>
                                                </td>
                                                <td style="padding:6px 0;">
                                                    <span style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px;">Temporary Password</span><br>
                                                    <span style="font-size:15px; font-weight:600; color:#111; font-family:monospace; background:#f0ede5; padding:2px 8px; border-radius:4px;">{{ $plainPassword }}</span>
                                                </td>
                                            </tr>
                                        </table>

                                        {{-- Role --}}
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td width="35" style="vertical-align:top; padding:6px 0;">
                                                    <span style="font-size:18px;">&#128100;</span>
                                                </td>
                                                <td style="padding:6px 0;">
                                                    <span style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px;">Role</span><br>
                                                    <span style="display:inline-block; background:#c9a84c; color:#fff; font-size:12px; font-weight:600; padding:3px 12px; border-radius:4px; text-transform:uppercase; letter-spacing:0.5px;">{{ ucfirst($memberRole) }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            {{-- Role description --}}
                            <div style="margin-top:20px; padding:15px 20px; background:#f0f0f0; border-radius:6px;">
                                <p style="font-size:12px; color:#999; text-transform:uppercase; letter-spacing:1px; margin:0 0 5px;">What You Can Do</p>
                                <p style="font-size:14px; color:#444; margin:0; line-height:1.5;">
                                    @if($memberRole === 'admin')
                                        As an Admin, you have full access to all features including the dashboard, calendar, jobs, invoices, team management, and system settings.
                                    @elseif($memberRole === 'installer')
                                        As an Installer, you have access to the installer portal where you can view your assigned jobs, manage your calendar, track time, create quotes, and communicate with the admin team.
                                    @elseif($memberRole === 'scheduler')
                                        As a Scheduler, you have access to the calendar to manage appointments, schedule jobs, and coordinate with crews.
                                    @else
                                        You have access to the VIP Windows portal based on your assigned role.
                                    @endif
                                </p>
                            </div>

                            {{-- CTA --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ $loginUrl }}" style="display:inline-block; background:#c9a84c; color:#fff; padding:14px 40px; border-radius:6px; text-decoration:none; font-weight:600; font-size:15px;">
                                            Log In Now
                                        </a>
                                        <p style="font-size:13px; color:#999; margin:12px 0 0;">
                                            We recommend changing your password after your first login.
                                        </p>
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
                                (562) 368-0313 &bull; info@vipwindowsinc.com &bull; vipwindowsinc.com
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
