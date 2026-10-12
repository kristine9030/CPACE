{{--
    "Back to where you were" for student pages on phones.

    Usage:
      @include('partials.student-back')                    - computes $backUrl, defines cpaceBack()
      @include('partials.student-back', ['pill' => true])  - also draws a floating Back pill
                                                             (for pages without the app header)

    Back goes to the page you actually came from (browser history, same site
    only). If there is no such page - you opened the link directly, or the
    previous page is this one (e.g. after saving a form) - it falls back to the
    logical parent below, so the button always leads somewhere sensible.
--}}
@php
    if (!function_exists('cpaceBackUrl')) {
        /** Logical parent of the current student page, or null on the home page. */
        function cpaceBackUrl(): ?string
        {
            $r = request()->route()?->getName();
            try {
                return match (true) {
                    $r === 'dashboard'             => null,
                    $r === 'subjects.topic'        => route('subjects.show', request()->route('subject')),
                    $r === 'subjects.show'         => route('subjects'),
                    $r === 'class-quizzes.subject' => route('class-quizzes'),
                    in_array($r, ['mock-exams.subject', 'mock-exams.show', 'mock-exams.result'], true) => route('mock-exams'),
                    in_array($r, ['quiz.history', 'quiz.results'], true) => route('adaptive-quizzes'),
                    $r === 'help.tickets.show'     => route('help.index'),
                    default                        => route('dashboard'),
                };
            } catch (\Throwable $e) {
                return route('dashboard');
            }
        }
    }
    $backUrl = cpaceBackUrl();
@endphp

@if ($backUrl)
    @if (!empty($pill))
        <style>
            .sb-pill { display: none; }
            @media (max-width: 768px) {
                .sb-pill {
                    display: inline-flex; align-items: center; gap: 8px; position: fixed; z-index: 1060;
                    top: max(10px, env(safe-area-inset-top)); left: 12px; height: 40px; padding: 0 16px 0 13px;
                    border-radius: 20px; background: #fff; color: #7B1D1D; text-decoration: none;
                    font: 600 13px 'Poppins', sans-serif; box-shadow: 0 4px 16px rgba(0,0,0,.16);
                    -webkit-tap-highlight-color: transparent;
                }
                body { padding-top: 58px; }
            }
        </style>
        <a href="{{ $backUrl }}" class="sb-pill" onclick="return cpaceBack(this)" aria-label="Go back">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    @endif

    <script>
        function cpaceBack(el) {
            try {
                const ref = document.referrer ? new URL(document.referrer) : null;
                if (ref && ref.origin === location.origin && ref.pathname !== location.pathname && !/\/(take|result|results)$/.test(ref.pathname) && history.length > 1) {
                    history.back();
                    return false;
                }
            } catch (e) {}
            return true; // follow the link's own href (the logical parent)
        }
    </script>
@endif
