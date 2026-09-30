<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin · Evaluations · CPACE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @include('partials.superadmin-sidebar', ['active' => 'evaluations'])
    <style>
        .confusion-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; margin:16px 0; }
        .confusion-cell { background:#f9fafb; border-radius:10px; padding:14px; text-align:center; }
        .confusion-cell .v { font-size:22px; font-weight:700; color:#1a1a1a; }
        .confusion-cell .k { font-size:10.5px; color:#999; margin-top:2px; }
        .metric-row-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:18px; }
        .metric-box { background:#fff; border:1px solid #eee; border-radius:14px; padding:18px 20px; text-align:center; }
        .metric-box .v { font-size:26px; font-weight:700; color:var(--primary); }
        .metric-box .k { font-size:11px; color:#999; margin-top:4px; }
        .sm2-case { border-top:1px solid #f5f5f5; padding:14px 6px; display:flex; align-items:center; gap:14px; }
        .sm2-case:first-child { border-top:none; }
        .sm2-verdict { width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:12px; color:#fff; flex-shrink:0; }
        .sm2-verdict.pass { background:#10b981; }
        .sm2-verdict.fail { background:#c0392b; }
        .sm2-label { font-size:13px; font-weight:600; color:#1a1a1a; }
        .sm2-detail { font-size:11.5px; color:#888; margin-top:3px; font-family:'IBM Plex Mono', monospace, monospace; }
        .methodology-note { font-size:11.5px; color:#999; line-height:1.6; }
    </style>
</head>
<body>
    <main class="main">
        <div class="topbar">
            <div class="topbar-left">
                <div>
                    <div class="page-title">Evaluations</div>
                    <div class="page-sub">Weakness Detection accuracy and Spaced Repetition Scheduler (SM-2) correctness.</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-magnifying-glass-chart" style="color:var(--primary);"></i> 2.1 — Weakness Detection Module</div>
            <p class="methodology-note" style="margin-top:8px;">
                No independently labeled "this topic really is weak" dataset exists in production, so this is <b>not</b> validation against outside ground truth. It compares the module's actual call —
                <span class="cell-mono">is_weak</span> (a &lt;60% accuracy floor gated behind a minimum of {{ \App\Services\WeaknessDetector::MIN_ATTEMPTS }} attempts, OR 3 wrong answers in a row) — against a naive accuracy-only baseline (accuracy &lt; 60%, no gate, no streak rule), across every real performance record with at least one attempt.
                It measures what the module's refinements are doing relative to the naive rule, not whether either is objectively "correct".
            </p>

            @if ($weakness['sample_size'] === 0)
                <div class="empty"><i class="fas fa-inbox"></i><div>No performance records with attempts yet — nothing to evaluate.</div></div>
            @else
                <div class="metric-row-3">
                    <div class="metric-box"><div class="v">{{ $weakness['precision'] !== null ? number_format($weakness['precision'] * 100, 1) . '%' : '—' }}</div><div class="k">Precision</div></div>
                    <div class="metric-box"><div class="v">{{ $weakness['recall'] !== null ? number_format($weakness['recall'] * 100, 1) . '%' : '—' }}</div><div class="k">Recall</div></div>
                    <div class="metric-box"><div class="v">{{ $weakness['f1'] !== null ? number_format($weakness['f1'] * 100, 1) . '%' : '—' }}</div><div class="k">F1-Score</div></div>
                </div>

                <div class="confusion-grid">
                    <div class="confusion-cell"><div class="v">{{ $weakness['tp'] }}</div><div class="k">True Positive<br>(both flag weak)</div></div>
                    <div class="confusion-cell"><div class="v">{{ $weakness['fp'] }}</div><div class="k">False Positive<br>(module only)</div></div>
                    <div class="confusion-cell"><div class="v">{{ $weakness['fn'] }}</div><div class="k">False Negative<br>(baseline only)</div></div>
                    <div class="confusion-cell"><div class="v">{{ $weakness['tn'] }}</div><div class="k">True Negative<br>(neither flags)</div></div>
                </div>
                <div class="hint">Evaluated across {{ number_format($weakness['sample_size']) }} performance record(s).</div>
            @endif
        </div>

        <div class="card">
            <div class="card-title"><i class="fas fa-calendar-check" style="color:var(--primary);"></i> 2.2 — Spaced Repetition Scheduler (SM-2) Correctness</div>
            <p class="methodology-note" style="margin-top:8px;">
                Runs the real <span class="cell-mono">SpacedRepetitionScheduler::next()</span> through fixed recall-quality sequences and compares its output against expected values derived by hand from the spec —
                I(2)=6, I(n)=I(n-1)&times;EF, EF'=EF+(0.1&minus;(5&minus;q)&times;(0.08+(5&minus;q)&times;0.02)), floored at 1.30 — independently of the implementation, so an actual bug can fail a case here.
            </p>

            <div class="metric-row-3" style="grid-template-columns:1fr;">
                <div class="metric-box">
                    <div class="v" style="color:{{ $sm2['passed'] === $sm2['total'] ? '#10b981' : '#c0392b' }};">{{ $sm2['passed'] }} / {{ $sm2['total'] }}</div>
                    <div class="k">Test Cases Passing</div>
                </div>
            </div>

            <div>
                @foreach ($sm2['cases'] as $case)
                    <div class="sm2-case">
                        <div class="sm2-verdict {{ $case['pass'] ? 'pass' : 'fail' }}"><i class="fas {{ $case['pass'] ? 'fa-check' : 'fa-xmark' }}"></i></div>
                        <div>
                            <div class="sm2-label">{{ $case['label'] }}</div>
                            <div class="sm2-detail">
                                expected: rep={{ $case['expected']['repetition_num'] }}, EF={{ number_format($case['expected']['ease_factor'], 2) }}, I={{ $case['expected']['interval_days'] }}d
                                &nbsp;·&nbsp;
                                actual: rep={{ $case['actual']['repetition_num'] }}, EF={{ number_format($case['actual']['ease_factor'], 2) }}, I={{ $case['actual']['interval_days'] }}d
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </main>
</body>
</html>
