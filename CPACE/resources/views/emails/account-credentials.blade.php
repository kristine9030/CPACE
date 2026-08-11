<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $isReissue ? 'Your CPACE one-time password was reset' : 'Your CPACE account is ready' }}</title>
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
                            <div style="width:52px; height:52px; border-radius:50%; background:#f5e8e8; display:flex; align-items:center; justify-content:center; margin-bottom:20px; font-size:22px;">🔑</div>
                            <h1 style="margin:0 0 12px; font-size:22px; color:#1a1a1a;">
                                {{ $isReissue ? 'Your one-time password was reset' : 'Welcome to CPACE' }}
                            </h1>
                            <p style="margin:0 0 8px; font-size:14px; line-height:1.6; color:#444;">
                                Hi {{ $user->first_name }},
                            </p>
                            <p style="margin:0 0 20px; font-size:14px; line-height:1.6; color:#444;">
                                {{ $isReissue
                                    ? 'The Program Chair issued a new one-time password for your CPACE account. Any password you were given earlier no longer works.'
                                    : "Your {$roleLabel} account has been created. Use the credentials below to sign in — you'll be asked to set your own password on first login." }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 8px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf7f7; border:1px solid #eee; border-radius:8px;">
                                <tr>
                                    <td style="padding:16px 18px;">
                                        <div style="font-size:11px; color:#999; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">Email / Username</div>
                                        <div style="font-size:14px; color:#1a1a1a; font-weight:600; margin-bottom:14px;">{{ $user->email }}</div>
                                        <div style="font-size:11px; color:#999; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;">One-Time Password</div>
                                        <div style="font-family: 'Courier New', monospace; font-size:18px; color:#7B1D1D; font-weight:700; letter-spacing:1px;">{{ $tempPassword }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px 32px 28px;" align="center">
                            <a href="{{ route('login') }}"
                               style="display:inline-block; background:linear-gradient(135deg, #7B1D1D 0%, #8B2525 100%); color:#ffffff; text-decoration:none; font-weight:600; font-size:15px; padding:14px 32px; border-radius:6px;">
                                Sign In to CPACE
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:0 32px 28px;">
                            <p style="margin:0; font-size:12px; line-height:1.6; color:#888;">
                                This one-time password is for your eyes only — the Program Chair cannot see it. You will be required to choose a new password the first time you sign in.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px; background:#faf7f7; border-top:1px solid #eee;">
                            <p style="margin:0; font-size:12px; color:#999; line-height:1.6;">
                                Didn't expect this email? Your Program Chair provisioned this account on your institution's behalf — reach out to them if something looks wrong.
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
