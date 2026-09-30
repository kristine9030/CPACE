@extends('emails.layouts.cpace')

@section('title', 'CPAce Support — request #' . $report->id)
@section('preheader', $reply ? \Illuminate\Support\Str::limit($reply->body, 110) : 'Your support request has been marked as resolved.')
@section('icon', $reply ? '💬' : '✅')

@section('content')
    <div style="display:inline-block; background:#f8eceb; color:#7B1D1D; font-size:11px; font-weight:700; letter-spacing:0.8px; text-transform:uppercase; padding:5px 12px; border-radius:999px; margin-bottom:14px;">
        Request #{{ $report->id }} &middot; {{ $report->statusLabel() }}
    </div>
    <h1 style="margin:0 0 14px; font-size:24px; line-height:1.3; font-weight:700; color:#1f1414;">
        {{ $reply ? 'We replied to your request' : 'Your request is resolved' }}
    </h1>
    <p style="margin:0 0 10px; font-size:15px; line-height:1.65; color:#4a3f3f;">Hi {{ $report->name }},</p>
    <p style="margin:0 0 18px; font-size:15px; line-height:1.65; color:#4a3f3f;">
        {{ $reply
            ? 'The CPAce support team answered your request “' . $report->title() . '”.'
            : 'We marked your request “' . $report->title() . '” as resolved. If the problem comes back, reply in the same request and it will reopen.' }}
    </p>

    @if ($reply)
        <div style="background:#faf6f5; border-left:4px solid #7B1D1D; border-radius:8px; padding:16px 18px; margin-bottom:24px; font-size:15px; line-height:1.7; color:#4a3f3f; white-space:pre-line;">{{ $reply->body }}</div>
    @endif

    @if ($report->user_id)
        @include('emails.partials.button', ['url' => route('help.tickets.show', $report), 'label' => 'View request'])
    @endif
@endsection

@section('note')
    @if ($report->user_id)
        To keep everything in one place, please reply from the request page in CPAce rather than to this email.
    @else
        You reported this from the CPAce website without signing in. Reply to this email if you need anything else.
    @endif
@endsection
