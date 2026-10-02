@extends('help.layout', ['active' => 'help'])

@section('title', 'Help & Support')
@section('heading', 'Help & Support')
@section('subheading', $help['intro'])

@push('styles')
<style>
    .hero{position:relative;overflow:hidden;border-radius:20px;padding:30px 32px;margin-bottom:22px;color:#fff;
        background:radial-gradient(circle at 85% 20%,rgba(255,255,255,.12),transparent 40%),linear-gradient(135deg,#3a0f0f 0%,#7B1D1D 55%,#a33a3a 100%)}
    .hero::after{content:'\f1cd';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:28px;bottom:-26px;font-size:150px;color:rgba(255,255,255,.07);pointer-events:none}
    .hero-role{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;background:rgba(255,255,255,.16);padding:5px 12px;border-radius:999px;margin-bottom:12px}
    .hero h2{font-family:'Montserrat',sans-serif;font-size:26px;margin:0 0 6px}
    .hero p{margin:0 0 18px;color:#f3dcdc;font-size:14px}
    .hero-search{position:relative;max-width:560px}
    .hero-search i{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:#9a8a89}
    .hero-search input{width:100%;padding:14px 16px 14px 44px;border:none;border-radius:12px;font:14px 'Poppins',sans-serif;color:var(--ink);box-shadow:0 8px 24px rgba(0,0,0,.18);outline:none}
    .hero-search input:focus-visible{outline:3px solid rgba(255,255,255,.5)}

    .quick-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:14px;margin-bottom:22px}
    .quick{display:flex;gap:12px;align-items:flex-start;padding:16px;text-decoration:none;transition:transform .15s,box-shadow .15s,border-color .15s}
    .quick:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(74,16,16,.08);border-color:#e3cfcd}
    .quick-icon{flex-shrink:0;width:40px;height:40px;border-radius:11px;background:var(--primary-light);color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:16px}
    .quick strong{display:block;font-size:14px;color:var(--ink);margin-bottom:2px}
    .quick span{font-size:12px;color:var(--muted);line-height:1.4}

    .inbox-banner{display:flex;align-items:center;gap:16px;justify-content:space-between;flex-wrap:wrap;padding:18px 22px;margin-bottom:22px;border-left:4px solid var(--primary)}
    .inbox-banner strong{font-size:15px;color:var(--ink)}
    .inbox-banner p{margin:2px 0 0;font-size:13px;color:var(--muted)}

    .help-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr);gap:22px;align-items:start}
    .side-stack{display:flex;flex-direction:column;gap:22px;position:sticky;top:20px}

    .faq-group{margin-bottom:18px}
    .faq-group:last-child{margin-bottom:0}
    .faq-group h3{font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--muted);margin:0 0 8px}
    .faq{border:1px solid var(--line);border-radius:12px;margin-bottom:8px;background:#fff;transition:border-color .15s}
    .faq[open]{border-color:#e3cfcd;box-shadow:0 4px 14px rgba(74,16,16,.05)}
    .faq summary{list-style:none;cursor:pointer;display:flex;justify-content:space-between;align-items:center;gap:12px;padding:14px 16px;font-size:14px;font-weight:600;color:var(--ink)}
    .faq summary::-webkit-details-marker{display:none}
    .faq summary i{color:var(--primary);transition:transform .2s;font-size:12px}
    .faq[open] summary i{transform:rotate(180deg)}
    .faq summary:focus-visible{outline:3px solid rgba(123,29,29,.25);border-radius:12px}
    .faq-a{padding:0 16px 16px;font-size:13.5px;line-height:1.7;color:var(--text)}
    .faq-none{display:none}

    .ticket-list{list-style:none;margin:0;padding:0}
    .ticket-list li+li{border-top:1px solid #f3ebea}
    .ticket-link{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:12px 4px;text-decoration:none}
    .ticket-link:hover .t-subject{color:var(--primary)}
    .t-subject{font-size:13.5px;font-weight:600;color:var(--ink);display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .t-meta{font-size:11.5px;color:var(--muted)}
    .t-body{min-width:0}

    .manual{padding:20px 24px;margin-bottom:22px;background:linear-gradient(135deg,#fff 0%,#fdf7f6 100%)}
    .manual-head{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
    .manual-head > div{flex:1;min-width:220px}
    .manual-icon{flex-shrink:0;width:46px;height:46px;border-radius:13px;display:grid;place-items:center;font-size:19px;color:#fff;background:linear-gradient(135deg,#5c1515,#a33a3a);box-shadow:0 6px 14px rgba(123,29,29,.22)}
    .soon{display:inline-block;vertical-align:middle;margin-left:8px;font:700 10px 'Poppins',sans-serif;letter-spacing:.8px;text-transform:uppercase;color:var(--warn);background:var(--warn-bg);padding:4px 9px;border-radius:999px}
    .manual-dl:disabled{opacity:.55;cursor:not-allowed}
    .manual-list{list-style:none;margin:16px 0 0;padding:0;display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:8px}
    .manual-list li{display:flex;align-items:center;gap:10px;padding:10px 12px;border:1px dashed #e6d6d4;border-radius:10px;font-size:13px;color:var(--muted);background:#fff}
    .ch-no{font:700 11px 'Montserrat',sans-serif;color:var(--primary);background:var(--primary-light);border-radius:6px;padding:3px 7px}
    @media(max-width:1000px){.help-grid{grid-template-columns:minmax(0, 1fr)}.side-stack{position:static}}
    @media(max-width:768px){.hero{padding:24px 20px}.hero h2{font-size:21px}.hero::after{display:none}}
</style>
@endpush

@section('content')
    <section class="hero">
        <span class="hero-role"><i class="fas fa-user-shield"></i> {{ $help['role'] }}</span>
        <h2>Hi {{ Auth::user()->first_name }}, how can we help?</h2>
        <p>Search the answers below, or send a request and the CPAce team will reply here and by email.</p>
        <div class="hero-search">
            <i class="fas fa-search"></i>
            <input type="search" id="faqSearch" placeholder="Search help topics — e.g. password, camera, import" aria-label="Search help topics">
        </div>
    </section>

    @if(!is_null($openInbox))
        <div class="card inbox-banner">
            <div>
                <strong><i class="fas fa-life-ring" style="color:var(--primary)"></i> Support Inbox</strong>
                <p>{{ $openInbox }} open {{ Str::plural('request', $openInbox) }} from students, faculty, alumni and website visitors.</p>
            </div>
            <a class="btn btn-primary" href="{{ route('chair.support.index') }}">Open inbox <i class="fas fa-arrow-right"></i></a>
        </div>
    @endif

    <div class="quick-grid">
        @foreach($help['quickLinks'] as $link)
            <a class="card quick" href="{{ $link['url'] }}">
                <span class="quick-icon"><i class="fas {{ $link['icon'] }}"></i></span>
                <span><strong>{{ $link['label'] }}</strong><span>{{ $link['hint'] }}</span></span>
            </a>
        @endforeach
    </div>

    {{-- Placeholder until the written manual is ready. --}}
    <section class="card manual" id="manual" aria-labelledby="manualTitle">
        <div class="manual-head">
            <span class="manual-icon"><i class="fas fa-book-open-reader"></i></span>
            <div>
                <h2 class="card-title" id="manualTitle" style="margin:0">User Manual <span class="soon">Coming soon</span></h2>
                <p class="card-sub" style="margin:4px 0 0">A step-by-step guide for {{ $help['role'] }} accounts is being prepared. Here’s what it will cover.</p>
            </div>
            <button class="btn btn-ghost manual-dl" type="button" disabled aria-disabled="true" title="Available once the manual is published">
                <i class="fas fa-file-arrow-down"></i> Download PDF
            </button>
        </div>
        <ol class="manual-list">
            @foreach($help['manual'] as $i => $chapter)
                <li><span class="ch-no">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span> {{ $chapter }}</li>
            @endforeach
        </ol>
    </section>

    <div class="help-grid">
        <section class="card card-pad" aria-labelledby="faqTitle">
            <h2 class="card-title" id="faqTitle"><i class="fas fa-circle-question"></i> Frequently asked questions</h2>
            <p class="card-sub">Answers written for your role as {{ $help['role'] }}.</p>

            @foreach($help['faqs'] as $group => $items)
                <div class="faq-group">
                    <h3>{{ $group }}</h3>
                    @foreach($items as $faq)
                        <details class="faq">
                            <summary>{{ $faq['q'] }} <i class="fas fa-chevron-down" aria-hidden="true"></i></summary>
                            <div class="faq-a">{{ $faq['a'] }}</div>
                        </details>
                    @endforeach
                </div>
            @endforeach
            <div class="empty faq-none" id="faqEmpty">
                <i class="far fa-face-meh"></i>
                No answers match that search. Send us a request instead — we’re happy to help.
            </div>
        </section>

        <div class="side-stack">
            <section class="card card-pad" id="contact" aria-labelledby="contactTitle">
                <h2 class="card-title" id="contactTitle"><i class="fas fa-paper-plane"></i> Contact support</h2>
                <p class="card-sub">Replies arrive in your notifications and at {{ Auth::user()->email }}.</p>

                <form method="POST" action="{{ route('help.tickets.store') }}" data-loading="Sending your request...">
                    @csrf
                    <div class="form-row">
                        <label class="label" for="subject">Subject</label>
                        <input class="field" id="subject" name="subject" maxlength="150" required value="{{ old('subject') }}" placeholder="A short summary of the problem">
                        @error('subject')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-row">
                        <label class="label" for="category">Category</label>
                        <select class="field" id="category" name="category" required>
                            @foreach($categories as $key => $label)
                                <option value="{{ $key }}" @selected(old('category', 'bug') === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <label class="label" for="message">What happened?</label>
                        <textarea class="field" id="message" name="message" maxlength="3000" required placeholder="What were you doing, what did you expect, and what happened instead?">{{ old('message') }}</textarea>
                        <div class="hint">Include the subject, quiz or exam name if it’s about content.</div>
                        @error('message')<div class="error-text">{{ $message }}</div>@enderror
                    </div>
                    <input type="hidden" name="page_url" value="{{ url()->previous() !== url()->current() ? url()->previous() : '' }}">
                    <button class="btn btn-primary" type="submit" style="width:100%"><i class="fas fa-paper-plane"></i> Send request</button>
                </form>
            </section>

            <section class="card card-pad" aria-labelledby="myTitle">
                <h2 class="card-title" id="myTitle"><i class="fas fa-ticket"></i> My requests</h2>
                <p class="card-sub">Track the status of what you’ve sent.</p>
                @if($tickets->isEmpty())
                    <div class="empty"><i class="far fa-folder-open"></i>You haven’t sent any requests yet.</div>
                @else
                    <ul class="ticket-list">
                        @foreach($tickets as $t)
                            <li>
                                <a class="ticket-link" href="{{ route('help.tickets.show', $t) }}">
                                    <span class="t-body">
                                        <span class="t-subject">{{ $t->title() }}</span>
                                        <span class="t-meta">#{{ $t->id }} · {{ ($t->last_activity_at ?? $t->created_at)->diffForHumans() }}@if($t->replies_count) · {{ $t->replies_count }} {{ Str::plural('reply', $t->replies_count) }}@endif</span>
                                    </span>
                                    <span class="pill pill-{{ $t->status }}">{{ $t->statusLabel() }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(function () {
    const input = document.getElementById('faqSearch');
    const empty = document.getElementById('faqEmpty');
    if (!input) return;
    input.addEventListener('input', function () {
        const term = input.value.trim().toLowerCase();
        let shown = 0;
        document.querySelectorAll('.faq-group').forEach(function (group) {
            let groupShown = 0;
            group.querySelectorAll('.faq').forEach(function (faq) {
                const hit = !term || faq.textContent.toLowerCase().includes(term);
                faq.style.display = hit ? '' : 'none';
                faq.open = !!term && hit;
                if (hit) groupShown++;
            });
            group.style.display = groupShown ? '' : 'none';
            shown += groupShown;
        });
        empty.classList.toggle('faq-none', shown > 0);
    });
})();
</script>
@endpush
