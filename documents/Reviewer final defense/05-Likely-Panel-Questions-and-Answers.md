# Likely Panel Questions & Answers

Rehearsed answers grounded in what the system actually does — not generic thesis
filler. Practice saying these out loud, not just reading them.

### "Why these specific thresholds — 60% accuracy, 5 attempts, 3 wrong in a row?"
They're deliberately simple and explainable rather than a black-box model, so a
student or faculty member can understand exactly why a topic was flagged. We treat
calibrating these against real cohort usage data as planned future work (Chapter 5,
Recommendation 3) rather than claiming they're already optimally tuned — we started
from a defensible, transparent rule.

### "Why SM-2 and not a newer algorithm like FSRS?"
SM-2 is well-documented, deterministic, and easy to verify formula-by-formula — which
matters for a system students and faculty need to trust and a thesis needs to be able
to prove correct. We explicitly name FSRS as future work once we have real usage data
to tune against (Recommendation 3); SM-2 was the right choice to validate first.

### "How do you know your weakness detector actually works, and not just that it runs?"
We validated its SM-2 implementation against the published formulas across 204 hand-
derived test cases (100% exact match) and ran the weakness-detection logic against 120
simulated scenarios across all 6 CPALE subjects (precision/recall/F1 all 1.00). Be
precise if pressed: the detector comparison is against a naive accuracy-only baseline
to show how much our refinements change the decision — not against independently-
labeled real-world ground truth, because no such ground truth exists for "is this
student actually weak."

### "Your evaluation didn't have a survey — isn't that unusual, and doesn't that weaken your results?"
It was a deliberate choice, stated in our Scope: we evaluate CPAce against objective,
scripted pass/fail test cases and automated algorithmic validation instead of opinion-
based surveys, because that removes subjectivity and small-sample response bias from
the evaluation of a system whose core claims (detection accuracy, scheduling
correctness, response time) are objectively measurable in the first place.

### "How many users did you test with, and is that enough?"
Functional/algorithmic validation used 120 and 204 test cases respectively (not real
users — simulated scenarios and hand-derived cases, which is appropriate for testing
an algorithm's correctness). Load testing simulated 10 to 200 concurrent users via
JMeter. User Acceptance Testing used selected Accountancy students and BSA faculty
evaluators running 24 scripted test cases; the manuscript doesn't report an exact
headcount for that group — say so honestly rather than inventing a number.

### "What happens under heavier load than 200 users?"
We didn't test beyond 200 concurrent users in this study; that's a natural extension
for future load testing, but even at 200 users response time stayed in the low
seconds with a 0% error rate, so there's no sign of an imminent collapse at that
boundary — just an untested range beyond it.

### "Is the proctoring actually secure, or can a student just fake it?"
We built it around what a browser can genuinely, honestly enforce: camera and screen
sharing require an explicit OS/browser permission prompt and a persistent visible
sharing indicator that cannot be hidden from the student — there is no silent capture.
What we add on top is consent-gated interval capture, in-browser face detection,
behavioral event logging (tab switches, fullscreen exits, copy/paste), server-side
time authority, a weighted risk score, and an independent statistical collusion check
on wrong-answer patterns. None of these claim to be unbeatable — they're documented in
the code itself as signals for a human reviewer, not automatic verdicts, which is the
honest and correct way to frame proctoring to a panel.

### "What's the actual mathematical basis for the answer-similarity/collusion check?"
For every pair of students, we count how many questions they both answered wrong with
the *identical* wrong choice, compute the number of such matches expected under pure
random guessing (given how many wrong options each question had), derive the
variance, and compute a z-score. We flag pairs at least 3 shared wrong answers and
2.0+ standard deviations above chance. It's a genuine statistical test, not a
heuristic word-match.

### "Why does a mock exam not award points or appear on the leaderboard?"
It's meant to feel like the real CPALE — serious and consequence-bearing, not a
gamified quiz — while its results still feed the student's weakness profile and
spaced-repetition schedule, because that performance signal is real and worth acting
on even though it isn't "scored" the way a practice quiz is.

### "What stops a faculty member from editing a question after a mock exam is published, to change someone's grade?"
Every published exam stores a frozen snapshot of its questions at publish time;
grading always reads from that snapshot, never from the live, editable question bank.
Editing the bank afterward cannot retroactively change any already-published exam's
scoring, and a published exam cannot be edited again by anyone, including the chair.

### "How do you keep an already-enrolled batch of students on the curriculum they started on, once you publish a newer one?"
Every student has a recorded `batch_year`. A single scoping service resolves what
curriculum version applies to a given student by their batch year, so publishing a
new curriculum version for incoming students never changes what an already-enrolled
batch is tested on.

### "What happens to proctoring recordings afterward — do you just keep everyone's camera photos forever?"
No — a clean sitting (no flags raised) has everything deleted on submission except the
single opening identity photo. A flagged sitting keeps only the frames that actually
back a real flag, plus that opening photo. Anything that remains is auto-purged after
14 days regardless, unless a faculty member or the chair reviews and deletes it
sooner. The event log (not the images) is the one thing that's never purged.

### "Is this specific to BatStateU, or could another school/program use it?"
The current pilot and dataset are scoped to the BatStateU TNEU ARASOF-Nasugbu BSA
program specifically. Extending the same weakness-detection/spaced-repetition
framework to other BatStateU licensure programs (Customs Administration, Criminology,
Engineering) is explicitly named as future work, along with adding a dedicated
system-administrator console for that kind of multi-program rollout.

### "What's the single biggest limitation of this study?"
No longitudinal data yet connecting actual CPAce usage to real CPALE board-passing
outcomes — that's explicitly our first recommendation for future work, and it's the
honest, correct thing to say rather than overclaiming the system already proves it
improves board-exam pass rates.

---

## 🇵🇭 Sa Taglish (quick answers)

**Bakit 60% / 5 attempts / 3-sunod-sunod lang?** Sinadya namin itong gawing simple
at explainable muna, hindi black-box model. Balak pang i-calibrate gamit ang real
data sa hinaharap — strength ito, hindi weakness.

**Bakit SM-2, hindi FSRS?** Well-documented at deterministic ang SM-2, madaling
i-verify formula by formula. Future work na namin ang FSRS once may real usage data
na.

**Paano na-validate ang detector?** 204 test cases para sa SM-2 (100% match), 120
simulated scenarios para sa weakness detection (perfect precision/recall/F1). Pero
importante: ang comparison ng detector ay laban sa naive accuracy-only baseline,
hindi laban sa real-world ground truth — wala talagang ganoong data available.

**Bakit walang survey?** Sinadya — objective/scripted test cases at automated
algorithmic validation ang ginamit, para maalis ang subjectivity/bias ng
opinion-based surveys.

**Secure ba ang proctoring, or can students fake it?** Based ito sa totoong
capability ng browser — kailangan ng explicit permission + visible indicator bago
makuha ang camera/screen, so walang silent capture. May risk score at statistical
collusion check din, pero both are "signals for a human," not automatic verdicts.

**Pang-BatStateU lang ba ito?** Yes, scoped sa BatStateU TNEU ARASOF-Nasugbu BSA
program. Extending sa ibang BatStateU programs ay named as future work.

**Pinakamalaking limitation?** Wala pang longitudinal data na nag-uugnay ng CPAce
usage sa actual CPALE passing rates — ito ang unang recommendation namin para sa
future work, honest answer ito instead of overclaiming.
