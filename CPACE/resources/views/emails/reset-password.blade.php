<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your CPACE password</title>
</head>
<body style="margin:0; padding:0; background:#f5f0f0; font-family: 'Segoe UI', Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f5f0f0; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:480px; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 6px 24px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:linear-gradient(135deg, #7B1D1D 0%, #8B2525 100%); padding:28px 32px;">
                            <div style="color:#ffffff; font-size:20px; font-weight:700; letter-spacing:0.5px;">CPACE</div>
                            <div style="color:#f5d9d9; font-size:12px; margin-top:2px;">CPA Licensure Exam Reviewer</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 12px;">
                            <div style="width:52px; height:52px; border-radius:50%; background:#f5e8e8; display:flex; align-items:center; justify-content:center; margin-bottom:20px; font-size:22px;">🔒</div>
                            <h1 style="margin:0 0 12px; font-size:22px; color:#1a1a1a;">Reset your password</h1>
                            <p style="margin:0 0 8px; font-size:14px; line-height:1.6; color:#444;">
                                Hi {{ $user->first_name }},
                            </p>
                            <p style="margin:0 0 24px; font-size:14px; line-height:1.6; color:#444;">
                                We received a request to reset the password for your CPACE account
                                (<strong>{{ $user->email }}</strong>). Click the button below to choose a new password.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px;" align="center">
                            <a href="{{ $resetUrl }}"
                               style="display:inline-block; background:linear-gradient(135deg, #7B1D1D 0%, #8B2525 100%); color:#ffffff; text-decoration:none; font-weight:600; font-size:15px; padding:14px 32px; border-radius:6px;">
                                Change Password
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <p style="margin:0 0 8px; font-size:12px; line-height:1.6; color:#888;">
                                This link expires in 60 minutes. If the button doesn't work, copy and paste this URL into your browser:
                            </p>
                            <p style="margin:0; font-size:12px; word-break:break-all; color:#7B1D1D;">
                                {{ $resetUrl }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background:#faf7f7; border-top:1px solid #eee;">
                            <p style="margin:0; font-size:12px; color:#999; line-height:1.6;">
                                If you didn't request a password reset, you can safely ignore this email —
                                your password will not be changed.
                            </p>
                        </td>
                    </tr>
                </table>
                <p style="font-size:11px; color:#aaa; margin-top:20px;">&copy; {{ date('Y') }} CPACE CPA Reviewer. All rights reserved.</p>
            </td>
        </tr>
    </table>
</body>
</html>
