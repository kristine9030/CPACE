@extends('emails.layouts.cpace')

@section('title', 'Reset your CPAce password')
@section('preheader', 'Use the link inside to choose a new password. It expires in 60 minutes.')
@section('icon', '🔒')

@section('content')
    <h1 style="margin:0 0 14px; font-size:24px; line-height:1.3; font-weight:700; color:#1f1414;">Reset your password</h1>
    <p style="margin:0 0 10px; font-size:15px; line-height:1.65; color:#4a3f3f;">Hi {{ $user->first_name }},</p>
    <p style="margin:0 0 22px; font-size:15px; line-height:1.65; color:#4a3f3f;">
        We received a request to reset the password for your CPAce account
        (<strong style="color:#1f1414;">{{ $user->email }}</strong>). Click the button below to choose a new password.
    </p>

    @include('emails.partials.button', ['url' => $resetUrl, 'label' => 'Change password'])

    <p style="margin:0 0 6px; font-size:13px; line-height:1.6; color:#8a7c7b;">
        This link expires in <strong>60 minutes</strong>. If the button doesn't work, copy and paste this URL into your browser:
    </p>
    <p style="margin:0 0 8px; font-size:12px; line-height:1.5; word-break:break-all; color:#7B1D1D;">{{ $resetUrl }}</p>
@endsection

@section('note')
    If you didn't request a password reset, you can safely ignore this email — your password will not be changed.
@endsection
