@extends('emails.layouts.cpace')

@section('title', $isReissue ? 'Your CPAce one-time password was reset' : 'Your CPAce account is ready')
@section('preheader', $isReissue ? 'A new one-time password was issued for your CPAce account.' : 'Your sign-in details for CPAce are inside.')
@section('icon', '🔑')

@section('content')
    <h1 style="margin:0 0 14px; font-size:24px; line-height:1.3; font-weight:700; color:#1f1414;">
        {{ $isReissue ? 'Your one-time password was reset' : 'Welcome to CPAce' }}
    </h1>
    <p style="margin:0 0 10px; font-size:15px; line-height:1.65; color:#4a3f3f;">Hi {{ $user->first_name }},</p>
    <p style="margin:0 0 22px; font-size:15px; line-height:1.65; color:#4a3f3f;">
        {{ $isReissue
            ? 'The Program Chair issued a new one-time password for your CPAce account. Any password you were given earlier no longer works.'
            : "Your {$roleLabel} account has been created. Use the credentials below to sign in — you'll be asked to set your own password on first login." }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#faf6f5; border:1px solid #f0e3e2; border-left:4px solid #7B1D1D; border-radius:10px; margin-bottom:22px;">
        <tr>
            <td style="padding:18px 20px;">
                <div style="font-size:11px; font-weight:600; color:#9a8a89; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:4px;">Email / Username</div>
                <div style="font-size:15px; color:#1f1414; font-weight:600; margin-bottom:16px; word-break:break-all;">{{ $user->email }}</div>
                <div style="font-size:11px; font-weight:600; color:#9a8a89; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:6px;">One-Time Password</div>
                <div style="display:inline-block; font-family:'Courier New', Consolas, monospace; font-size:20px; color:#7B1D1D; font-weight:700; letter-spacing:2px; background:#ffffff; border:1px dashed #d9b9b7; border-radius:8px; padding:8px 14px;">{{ $tempPassword }}</div>
            </td>
        </tr>
    </table>

    @include('emails.partials.button', ['url' => route('login'), 'label' => 'Sign in to CPAce'])

    <p style="margin:0 0 8px; font-size:13px; line-height:1.6; color:#8a7c7b;">
        This one-time password is for your eyes only — the Program Chair cannot see it. You'll be required to choose a new password the first time you sign in.
    </p>
@endsection

@section('note')
    Didn't expect this email? Your Program Chair provisioned this account on your institution's behalf — reach out to them if something looks wrong.
@endsection
