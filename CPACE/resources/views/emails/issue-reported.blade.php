@extends('emails.layouts.cpace')

@section('title', 'New issue report #' . $report->id)
@section('preheader', $report->categoryLabel() . ' from ' . $report->name)
@section('icon', '🐞')

@section('content')
    <div style="display:inline-block; background:#f8eceb; color:#7B1D1D; font-size:11px; font-weight:700; letter-spacing:0.8px; text-transform:uppercase; padding:5px 12px; border-radius:999px; margin-bottom:14px;">
        {{ $report->categoryLabel() }} &middot; #{{ $report->id }}
    </div>
    <h1 style="margin:0 0 18px; font-size:24px; line-height:1.3; font-weight:700; color:#1f1414;">New issue report</h1>

    @php
        $rows = [
            'From' => $report->name . ' (' . $report->email . ')',
            'Account' => $report->user_id ? 'Signed in — user #' . $report->user_id : 'Not signed in',
            'Submitted' => $report->created_at->format('d M Y, g:i A'),
        ];
        if ($report->page_url) {
            $rows['Page'] = $report->page_url;
        }
    @endphp
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border:1px solid #f0e3e2; border-radius:10px; margin-bottom:22px;">
        @foreach ($rows as $label => $value)
            <tr>
                <td style="padding:11px 16px; width:90px; font-size:12px; font-weight:600; color:#9a8a89; text-transform:uppercase; letter-spacing:0.6px; vertical-align:top; {{ $loop->last ? '' : 'border-bottom:1px solid #f4ecea;' }}">{{ $label }}</td>
                <td style="padding:11px 16px; font-size:14px; color:#1f1414; word-break:break-all; {{ $loop->last ? '' : 'border-bottom:1px solid #f4ecea;' }}">{{ $value }}</td>
            </tr>
        @endforeach
    </table>

    <div style="font-size:11px; font-weight:600; color:#9a8a89; text-transform:uppercase; letter-spacing:0.8px; margin-bottom:8px;">What they reported</div>
    <div style="background:#faf6f5; border-left:4px solid #7B1D1D; border-radius:8px; padding:16px 18px; margin-bottom:24px; font-size:15px; line-height:1.7; color:#4a3f3f; white-space:pre-line;">{{ $report->message }}</div>

    @include('emails.partials.button', [
        'url' => 'mailto:' . $report->email . '?subject=' . rawurlencode('Re: CPAce issue report #' . $report->id),
        'label' => 'Reply to ' . $report->name,
    ])
@endsection

@section('note')
    Replying to this email also reaches {{ $report->email }} directly.
    @if ($report->user_agent)
        <br>Browser: {{ $report->user_agent }}
    @endif
@endsection
