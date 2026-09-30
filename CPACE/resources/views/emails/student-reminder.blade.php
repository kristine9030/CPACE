@extends('emails.layouts.cpace')

@section('title', $subjectLine)
@section('preheader', \Illuminate\Support\Str::limit($bodyMessage, 110))
@section('icon', '⏰')

@section('content')
    <h1 style="margin:0 0 14px; font-size:24px; line-height:1.3; font-weight:700; color:#1f1414;">{{ $subjectLine }}</h1>
    <p style="margin:0 0 10px; font-size:15px; line-height:1.65; color:#4a3f3f;">Hi {{ $studentName }},</p>
    <p style="margin:0 0 24px; font-size:15px; line-height:1.7; color:#4a3f3f; white-space:pre-line;">{{ $bodyMessage }}</p>

    @if ($ctaUrl)
        @include('emails.partials.button', ['url' => $ctaUrl, 'label' => $ctaLabel ?? 'Open CPAce'])
    @endif

    <p style="margin:0 0 8px; font-size:15px; line-height:1.6; color:#4a3f3f;">
        Regards,<br>
        <strong style="color:#1f1414;">{{ $facultyName }}</strong><br>
        <span style="font-size:13px; color:#8a7c7b;">CPAce CPA Reviewer</span>
    </p>
@endsection

@section('note')
    You're receiving this because your instructor sent it through the CPAce Student Performance dashboard.
@endsection
