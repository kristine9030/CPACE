# Weakness Detection & Spaced Repetition — Core Feature

This is the heart of CPAce. Everything else in the system (practice quizzes, the
calendar, faculty dashboards, chair reports) is built to feed this engine or display
its output. You need to be able to explain this without notes.

## Two services, one shared rule

Two classes do all the work, and **every other part of the system that mentions
"weak topics" calls these same two classes** — the student dashboard, the faculty
dashboard, the faculty performance/report screens, the chair's student-management
page, the practice-quiz question picker, and the calendar all reuse the identical
logic. There is one rule, not six copies of a similar rule.

### `App\Services\WeaknessDetector`

Decides whether a student is weak in a topic, using `performance_records` (one row per
student per topic: `total_attempts`, `correct_count`, a database-generated
`accuracy_rate` column, and `consecutive_wrong`).

A topic is flagged **weak** when *either* condition is true:

- **Low accuracy**: accuracy rate is below **60%**, but only once the student has at
  least **5 attempts** on that topic (so one unlucky early guess can't flag a topic).
- **Consecutive wrong**: the student has gotten the last **3 answers wrong in a row**
  on that topic — checked first, so a sudden bad streak gets flagged immediately
  without waiting for 5 attempts to accumulate.

A topic is marked **strong** at **75%+ accuracy** over the same 5-attempt floor, and
only if it isn't currently flagged weak.

When a topic flips to weak, the system opens a row in `weakness_reports` recording
*why* (`trigger_reason`: `low_accuracy` or `consecutive_wrong`) and the accuracy at the
moment it was flagged. When the student recovers, that report is auto-resolved
(`resolved_at` is stamped). This is idempotent — re-running it after every quiz never
duplicates or corrupts the history.

**Why 60% / 5 attempts / 3-in-a-row, and not something else?** These are deliberately
simple, explainable thresholds chosen so the rule is transparent to students and
faculty, not a black box. Chapter 5 of the manuscript explicitly flags calibrating
these numbers against real cohort data as future work — be upfront about that if
asked; it's a strength ("we started with a defensible, explainable rule and planned to
tune it with real usage data"), not a weakness to hide.

### `App\Services\SpacedRepetitionScheduler`

This is a genuine implementation of **SM-2** (the SuperMemo-2 algorithm by Piotr
Wozniak), not a simplified approximation. For every answered question it:

1. Converts correct/incorrect (and the question's difficulty) into a **quality score
   from 0–5** — the standard SM-2 input.
2. Updates the **ease factor (EF)** using the real SM-2 formula:
   `EF' = EF + (0.1 − (5 − q) × (0.08 + (5 − q) × 0.02))`, floored at **1.3** so a
   question never becomes "impossibly hard to re-earn trust on."
3. Recomputes the **next review interval** with the canonical SM-2 rule:
   `I(1) = 1 day`, `I(2) = 6 days`, `I(n) = round(I(n−1) × EF)` for every review after
   that.
4. On a **lapse** (quality score below 3 — i.e., the student got it wrong), the
   repetition counter resets to zero and the item is scheduled to come back
   **tomorrow** — this is the direct mechanism that makes weak topics resurface
   sooner rather than drifting further away.

All of this state lives in `spaced_repetition_items` — one row per student per
question, holding `repetition_num`, `ease_factor`, `interval_days`, `quality_score`,
and `next_review_at`.

### The end-to-end flow (say this out loud, it's your elevator pitch)

> Student submits a quiz (practice, class, or any mode) → the server grades it →
> **immediately**, in the same request, it calls `SpacedRepetitionScheduler` to update
> that question's SM-2 state, then `WeaknessDetector` to re-check every topic touched
> by that quiz → `performance_records` and `weakness_reports` are updated → the next
> time the student opens `/calendar`, the newly-due reviews show up, with weak topics
> ranked **High priority** above everything else.

This call pair happens in every quiz-submission path in the app (regular practice
quizzes, class quizzes, and the API quiz endpoint) — it is not something that only
runs in one place and was forgotten elsewhere.

### Where weakness detection shows up for a student, concretely

- **Practice Quiz "Adaptive" mode** actively *weights the question pool* toward topics
  already flagged `is_weak_area` in `performance_records`, filling the rest of the
  quiz from the general pool — this is real SQL filtering at quiz-build time, not a
  cosmetic label.
- **Calendar** colors/ranks spaced-repetition events by weakness (weak = High
  priority, under-75%-but-not-weak = Medium, else Low).
- **Dashboard** surfaces the student's top-3 weak topics and top-3 strong topics.
- **Faculty dashboard / performance / reports** and **chair student management** all
  show the same weak-topic flags for a class or a specific student — a faculty member
  and the program chair are looking at the exact same underlying signal a student
  sees about themselves, just aggregated differently.

### How you validated it (cite this directly, it's strong material)

There is a dedicated internal validation harness,
`App\Http\Controllers\SuperAdmin\EvaluationController`, reachable from the Super Admin
panel, that:

- Runs the real `WeaknessDetector` against a function that only checks accuracy, and
  reports Precision/Recall/F1 between the two. **Be careful how you phrase this to the
  panel**: this is *not* a comparison against independently-labeled ground truth (none
  exists for "is this student actually weak" in real life) — it's a sensitivity
  analysis showing exactly how much the 5-attempt floor and 3-in-a-row rule change the
  call versus plain accuracy alone. Say "we quantified how our refinements change the
  detection decision versus a naive accuracy-only baseline," not "we proved our
  detector is accurate."
- Runs the live `SpacedRepetitionScheduler` through hand-derived SM-2 test cases with
  known correct answers (e.g., three perfect recalls in a row → EF should land at
  2.80 with a 17-day interval; a lapse after two good reviews should reset the
  interval to 1 day; repeated failures should floor the ease factor at 1.30) and checks
  pass/fail against the published SM-2 formula. This is a genuine correctness check of
  your implementation, and you can say so plainly: "we verified our SM-2
  implementation against the published formulas across a battery of test cases, and
  all passed."

These two validations are also what produced the Chapter 4 numbers in
`03-Pilot-Testing-Results.md` (120 simulated scenarios for weakness detection, 204 test
cases for SM-2) — read that file for the exact figures.

---

## 🇵🇭 Sa Taglish

Dalawang services lang ang gumagawa ng core logic dito, at same dalawang class na ito
ang ginagamit ng *lahat* ng ibang part ng system na may "weak topic" — student
dashboard, faculty dashboard, chair's student management, practice quiz question
picker, calendar. So isa lang talaga ang rule, hindi paiba-iba per page.

**WeaknessDetector** — nagdedesisyon kung weak ba ang isang student sa isang topic.
Weak kapag: (1) below 60% accuracy niya pero may at least 5 attempts na siya (para
hindi agad maflag sa isa pang pagkakamali), OR (2) 3 consecutive wrong sa parehong
topic — ito ang unang checked, kaya kahit wala pang 5 attempts, kapag 3 beses
sunod-sunod na mali, flagged na agad. Strong naman siya kapag 75%+ accuracy. Kung
tanungin kung bakit ganito lang ang numbers: sinadya namin itong gawing simple at
explainable muna, tapos plano naming i-calibrate ito gamit ang real cohort data sa
hinaharap (nakasulat sa Chapter 5 recommendations) — strength ito, hindi weakness.

**SpacedRepetitionScheduler** — totoong SM-2 algorithm ito (hindi approximation
lang). Per-question: kinukwenta ang quality score (0–5) base sa tama/mali at
difficulty, then na-update ang ease factor gamit ang actual SM-2 formula (floored
sa 1.3), then kinukwenta ang next review interval (1 day → 6 days → previous
interval × ease factor). Kapag mali (lapse), reset ang repetition count at bukas na
agad ulit ang next review — ito mismo ang nagpapabalik ng weak topics sa harapan ng
student mas madalas.

**Buong flow** (elevator pitch mo ito): Student submits quiz → graded ng server →
sa same request, na-update agad ang SM-2 state tapos na-recheck ang weakness ng
every topic na affected → naka-update na ang records → sa Calendar ng student,
makikita na ang bagong due reviews, with weak topics as High priority. Nangyayari
ito sa *every* quiz-submission path (practice, class quiz, API) — hindi lang isang
lugar lang ito ginawa.

**Saan makikita in practice**: Adaptive mode sa Practice Quiz talagang weighted ang
question pool papunta sa weak topics (actual SQL filter, hindi cosmetic). Calendar
nag-prioritize ng weak topics bilang High. Dashboard shows top-3 weak/strong topics.
Faculty dashboard at chair's student management — same signal, parehong source.

**Validation** (good material to cite): may internal evaluation harness sa Super
Admin panel na (1) nag-compare ng detector vs. naive accuracy-only baseline —
i-frame mo ito bilang "sensitivity analysis kung gaano nag-change ang decision dahil
sa refinements namin," hindi "proof na accurate ang detector" kasi walang real
ground truth na available; (2) nag-verify ng SM-2 implementation laban sa published
formula gamit ang hand-derived test cases — lahat pumasa. Ito mismo ang pinagmulan
ng mga numbers sa `03-Pilot-Testing-Results.md` (120 scenarios para sa weakness
detection, 204 test cases para sa SM-2).
