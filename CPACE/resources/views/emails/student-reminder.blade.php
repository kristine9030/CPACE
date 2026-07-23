@component('mail::message')
# {{ $subjectLine }}

Hi {{ $studentName }},

{{ $bodyMessage }}

@isset($ctaUrl)
@component('mail::button', ['url' => $ctaUrl])
{{ $ctaLabel ?? 'Open CPACE' }}
@endcomponent
@endisset

Regards,<br>
{{ $facultyName }}<br>
CPACE CPA Reviewer

@component('mail::subcopy')
You're receiving this because your instructor sent it through the CPACE Student Performance dashboard.
@endcomponent
@endcomponent
