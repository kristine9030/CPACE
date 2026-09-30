@extends('emails.layouts.cpace')

@section('title', $title)
@section('preheader', \Illuminate\Support\Str::limit($body, 110))
@section('icon', $priority === 'urgent' ? '🚨' : '📣')

@section('content')
    @if ($priority !== 'normal')
        <div style="display:inline-block; background:{{ $priority === 'urgent' ? '#fee2e2' : '#fef3c7' }}; color:{{ $priority === 'urgent' ? '#b91c1c' : '#92400e' }}; font-size:11px; font-weight:700; letter-spacing:0.8px; text-transform:uppercase; padding:5px 12px; border-radius:999px; margin-bottom:14px;">
            {{ $priority }}
        </div>
    @endif
    <h1 style="margin:0 0 14px; font-size:24px; line-height:1.3; font-weight:700; color:#1f1414;">{{ $title }}</h1>
    <p style="margin:0 0 10px; font-size:15px; line-height:1.65; color:#4a3f3f;">Hi {{ $recipientName }},</p>
    <p style="margin:0 0 24px; font-size:15px; line-height:1.7; color:#4a3f3f; white-space:pre-line;">{{ $body }}</p>

    @if ($ctaUrl)
        @include('emails.partials.button', ['url' => $ctaUrl, 'label' => 'View in CPAce'])
    @endif
@endsection

@section('note')
    Sent by <strong style="color:#5c4f4f;">{{ $senderName }}</strong> via the CPAce Program Chair communications tool.
@endsection
