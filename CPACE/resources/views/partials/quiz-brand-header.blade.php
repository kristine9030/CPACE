{{--
    Branded header for every quiz-taking screen (adaptive quizzes, class
    quizzes, mock exams): a maroon gradient with the campus photo faded into
    its right side, and the BSU logo on the left.

    In <head>:      @include('partials.quiz-brand-header')
    On the header:  class="quiz-brand"  (it must be positioned — relative or
                    sticky — so the photo layer stays inside it)
    Logo, first in the header row:  @include('partials.quiz-brand-header', ['logo' => true])
--}}
@if(! empty($logo))
    <img src="{{ asset('images/logo-bsu.png') }}" alt="Batangas State University" class="quiz-brand-logo">
@else
<style>
    .quiz-brand {
        background:linear-gradient(115deg, #4a1010 0%, #7B1D1D 42%, #a8322a 100%);
        overflow:hidden;
        isolation:isolate;
    }
    /* The campus photo is grayscale with a transparent left edge, so it
       fades in from the right; "screen" lifts it into the maroon as a soft
       texture instead of a grey block over the header text. */
    .quiz-brand::before {
        content:''; position:absolute; inset:0; z-index:-1; pointer-events:none;
        background:url('{{ asset('images/overaly.png') }}') right 42% / cover no-repeat;
        opacity:.2; mix-blend-mode:screen;
    }
    .quiz-brand-logo {
        width:40px; height:40px; flex-shrink:0; border-radius:50%;
        background:#fff; padding:2px; object-fit:contain;
        box-shadow:0 2px 8px rgba(0,0,0,.25);
    }
    @media (max-width:560px) { .quiz-brand-logo { width:32px; height:32px; } }
</style>
@endif
