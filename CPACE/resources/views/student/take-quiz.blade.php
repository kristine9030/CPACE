<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz - CPACE CPA Reviewer</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary:#7B1D1D; --primary-hover:#6a1818; --primary-light:#f5e8e8; --accent:#c0392b; --green:#10b981; --gray:#999; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Poppins',sans-serif; background:#f4f5f7; color:#333; }
        .main-content { margin-left:220px; padding:30px 40px; min-height:100vh; transition:margin-left .3s; }
        .sidebar.collapsed ~ .main-content { margin-left:70px; }

        .quiz-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:22px; gap:16px; }
        .quiz-title { font-size:24px; font-weight:700; color:#1a1a1a; }
        .quiz-sub { font-size:13px; color:var(--gray); margin-top:2px; }
        .quiz-progress-pill { background:white; border-radius:24px; padding:10px 18px; font-size:13px; font-weight:600; color:var(--primary); box-shadow:0 1px 4px rgba(0,0,0,.06); }

        .progress-track { height:8px; background:#e8e3e3; border-radius:8px; overflow:hidden; margin-bottom:24px; }
        .progress-fill { height:100%; background:var(--primary); width:0; transition:width .3s; }

        .q-card { background:white; border-radius:14px; padding:24px 26px; margin-bottom:18px; }
        .q-top { display:flex; align-items:flex-start; gap:12px; margin-bottom:18px; }
        .q-num { width:30px; height:30px; flex-shrink:0; background:var(--primary-light); color:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; }
        .q-text { font-size:15px; font-weight:500; color:#1a1a1a; line-height:1.55; }
        .q-badge { display:inline-block; font-size:10px; font-weight:700; padding:2px 9px; border-radius:5px; background:#f3f4f6; color:#6b7280; margin-left:8px; vertical-align:middle; }

        .choices { display:flex; flex-direction:column; gap:10px; padding-left:42px; }
        .choice { display:flex; align-items:center; gap:12px; padding:13px 16px; border:1.5px solid #eee; border-radius:10px; cursor:pointer; transition:all .15s; }
        .choice:hover { border-color:var(--primary); background:var(--primary-light); }
        .choice input { accent-color:var(--primary); width:16px; height:16px; flex-shrink:0; }
        .choice.selected { border-color:var(--primary); background:var(--primary-light); }
        .choice-letter { width:26px; height:26px; flex-shrink:0; border-radius:50%; background:#f0f0f0; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:700; color:#555; }
        .choice-text { font-size:14px; color:#333; }

        .submit-bar { display:flex; justify-content:space-between; align-items:center; background:white; border-radius:14px; padding:18px 24px; position:sticky; bottom:16px; box-shadow:0 -2px 12px rgba(0,0,0,.05); }
        .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 26px; border-radius:10px; font-size:14px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; text-decoration:none; }
        .btn-primary { background:var(--primary); color:white; }
        .btn-primary:hover { background:var(--primary-hover); }
        .btn-ghost { background:white; color:#555; border:1px solid #e0e0e0; }
    </style>
</head>
<body>

@include('partials.sidebar', ['active' => 'quizzes'])

<main class="main-content">
    <div class="quiz-header">
        <div>
            <div class="quiz-title">{{ $session->subject->name ?? 'Adaptive Quiz' }}</div>
            <div class="quiz-sub">Answer all {{ $questions->count() }} questions, then submit to see your score.</div>
        </div>
        <div class="quiz-progress-pill"><span id="answeredCount">0</span> / {{ $questions->count() }} answered</div>
    </div>

    <div class="progress-track"><div class="progress-fill" id="progressFill"></div></div>

    <form method="POST" action="{{ route('quiz.submit', $session->id) }}" id="quizForm">
        @csrf

        @php $typeLabel = ['mcq' => 'Multiple Choice', 'true_false' => 'True / False']; @endphp
        @foreach($questions as $i => $question)
            <div class="q-card">
                <div class="q-top">
                    <div class="q-num">{{ $i + 1 }}</div>
                    <div class="q-text">
                        {{ $question->question_text }}
                        <span class="q-badge">{{ $typeLabel[$question->question_type] ?? $question->question_type }}</span>
                    </div>
                </div>
                <div class="choices">
                    @foreach($question->choices as $choice)
                        <label class="choice">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $choice->id }}">
                            <span class="choice-letter">{{ $choice->choice_label }}</span>
                            <span class="choice-text">{{ $choice->choice_text }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <div class="submit-bar">
            <a href="{{ route('adaptive-quizzes') }}" class="btn btn-ghost"><i class="fas fa-times"></i> Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Quiz</button>
        </div>
    </form>
</main>

<script>
    const total = {{ $questions->count() }};
    const form = document.getElementById('quizForm');

    function refresh() {
        const answered = new Set();
        form.querySelectorAll('input[type=radio]:checked').forEach(r => answered.add(r.name));
        document.getElementById('answeredCount').textContent = answered.size;
        document.getElementById('progressFill').style.width = (answered.size / total * 100) + '%';
    }

    form.addEventListener('change', e => {
        if (e.target.type === 'radio') {
            // highlight selected choice within its question group
            document.getElementsByName(e.target.name).forEach(r => r.closest('.choice').classList.remove('selected'));
            e.target.closest('.choice').classList.add('selected');
            refresh();
        }
    });

    form.addEventListener('submit', e => {
        const answeredCount = new Set([...form.querySelectorAll('input[type=radio]:checked')].map(r => r.name)).size;
        if (answeredCount < total && !confirm(`You answered ${answeredCount} of ${total}. Submit anyway?`)) {
            e.preventDefault();
        }
    });
</script>
</body>
</html>
