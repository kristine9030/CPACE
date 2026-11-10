# Pilot Testing / Evaluation Results — Chapter 4-5

Source: `CPAce_Chapter-4-5_DRAFT AS OF OCT 5.docx` (most current draft at research
time). All numbers below are taken directly from that document.

## ⚠️ Important framing correction before you present this

Your Scope explicitly states: **"no surveys, questionnaires, or opinion-based
instruments were used."** This evaluation is **100% objective/automated** — ground-
truth datasets, load testing, and scripted pass/fail test cases. If a panelist asks
"what did your respondents say in the survey," the correct answer is: *we deliberately
did not use a survey/questionnaire instrument; our evaluation is based on objective,
scripted test cases and automated algorithmic validation, which removes the subjectivity
and small-sample bias that opinion surveys carry.* Don't call any part of this a
"survey" or describe results in terms of respondent agreement — there were none.

## Three evaluation tracks

### 1. Functional/algorithmic validation

- **Weakness Detection Module**: tested against a ground-truth dataset of **120
  simulated student-response scenarios** (20 per CPALE subject × 6 subjects: FAR,
  AFAR, MS, TAX, AUD, RFBT).
  - **Precision 1.00, Recall 1.00, F1 1.00** — overall and identical for every one of
    the 6 subjects (9 true positives, 0 false positives, 0 false negatives per
    subject table row).
- **Spaced Repetition Scheduler (SM-2)**: tested against **204 test cases** spanning
  recall-quality scores 0–5 and varied ease-factor histories, checked against the
  published SM-2 formulas.
  - **100.00% exact-match rate** across all 204 cases. Breakdown: I(1)=1 day — 30/30;
    I(2)=6 days — 30/30; I(n)=I(n−1)×EF — 60/60; ease-factor update — 30/30;
    ease-factor floor at 1.30 — 18/18; lapse (quality < 3) resets interval — 36/36.
- Run via automated PHP evaluation test suites and Laravel Artisan commands — this is
  the same harness described in `01-Weakness-Detection-and-Spaced-Repetition.md`.

### 2. Technical benchmarking (Apache JMeter)

Simulated 10 / 50 / 100 / 200 concurrent users against the quiz engine, scheduler, and
dashboard. 5-minute duration per run, 30-second ramp-up, each level repeated at least
3 times, 2.0-second student "think time" between requests. Server: Apache 2.4.58, PHP
8.2.12 with OPcache, MariaDB, on an unconstrained dedicated server.

| Users | Avg response | p90 | Max | Throughput | Error rate |
|---|---|---|---|---|---|
| 10  | 81 ms   | 106 ms  | 276 ms  | 8.92 req/s  | 0.00% (0/2,667) |
| 50  | 103 ms  | 133 ms  | 483 ms  | 43.48 req/s | 0.00% (0/12,998) |
| 100 | 677 ms  | 1,207 ms | 2,141 ms | 57.20 req/s | 0.01% (1/17,100) |
| 200 | 1,273 ms | 2,235 ms | 3,431 ms | 59.48 req/s | 0.00% (0/17,722) |

All response times stayed under the 2,000 ms threshold on average; error rate stayed
far under the 5% acceptance threshold (and under the stricter internal 1% standard) at
every load level. Degradation from the 10-user baseline (81 ms): +27.2% at 50 users,
+735.8% at 100, +1,471.6% at 200 — a steep-looking percentage, but note it's measured
against a very fast 81 ms baseline, and the system never failed or errored out even at
200 concurrent users. Say it this way to the panel: "response time scaled predictably
under load, with no catastrophic failure, and error rates stayed near zero throughout."

### 3. User Acceptance Testing (scripted, not opinion-based)

Selected Accountancy students and BSA faculty evaluators executed **24 scripted test
cases across 15 core modules** — each test case is a specific action checked against
an expected, specified result (pass/fail), not a rating.

- **100.00% pass rate, 0 failures.**
- Modules covered include: Weakness Detection (2/2), SM-2 Scheduler (3/3), Adaptive
  Quiz Engine (2/2), Mock Board Exam (2/2), Performance Dashboard (1/1), Question Bank
  (2/2), Role-Based Access Control (2/2), Score Reporting (1/1), AI Tutor (1/1), AI
  Substitute Review (1/1), Proctoring (2/2), Curriculum/TOS (1/1), Messaging/Community
  (1/1), Notification (1/1), Review Notification (1/1).

No sample size or demographic breakdown is given beyond "selected Accountancy students
and BSA faculty evaluators" — don't invent a number if asked; say the manuscript
doesn't break that figure down further.

## Qualitative findings

None reported — consistent with the no-survey scope. There are no interview themes or
open-ended quotes in Chapter 4-5.

## Statistical tests

No inferential statistics (no t-test, ANOVA, etc.) are used anywhere in Chapter 4-5.
Everything reported is descriptive: precision/recall/F1, exact-match rate, pass rate,
response time, throughput, error rate. If asked why no significance testing was done,
the honest answer is that the evaluation design is scripted/deterministic validation
against known-correct outputs and load benchmarking, not a comparison between groups
or conditions that would call for inferential statistics.

## Chapter 5 — conclusions and recommendations

**Conclusions** affirm: feature completeness; perfect detector/scheduler scores;
performance meeting the ISO/IEC 25010 time-behavior standard; predictable scaling;
high reliability (near-zero error rates under load).

**Five recommendations** (good material for your "future work" slide/answer):

1. A longitudinal study tying actual CPAce usage to real CPALE passing rates, versus
   non-users.
2. Continuous question-bank/TOS expansion, coordinated with faculty and the Program
   Chair as PRC content updates.
3. Calibrate the weakness thresholds (60% accuracy / 5 attempts, 3-consecutive-wrong)
   using real cohort data once available, and explore **FSRS** as a newer alternative
   to SM-2.
4. Offline/PWA support for intermittent connectivity.
5. Extend the weakness-detection/spaced-repetition framework to other BatStateU
   licensure programs (Customs Administration, Criminology, Engineering), and add a
   dedicated system-administrator console.

## Scope and environment (useful if asked about hardware/software)

Minimum client spec: ≥2.40GHz processor, ≥4GB RAM, ≥64GB storage, a webcam (required
for proctoring), ≥5Mbps internet, Chrome/Edge on Windows 10/11 or Android/iOS. Server
stack: Apache 2.4.58, Laravel 12, PHP 8.2.12, Bootstrap 5, MySQL 8.0, Laravel Socialite
(Google sign-in), Google Gemini API with an OpenRouter fallback, and dompdf /
PhpSpreadsheet / PhpWord for generated reports. Study scope is single-institution:
BatStateU TNEU ARASOF-Nasugbu BSA program only.

---

## 🇵🇭 Sa Taglish

**⚠️ Important bago i-present ito**: Walang survey/questionnaire/opinion-based
instrument na ginamit sa evaluation — nasa Scope mismo ng manuscript 'to. 100%
objective/automated ito — ground-truth datasets, load testing, at scripted pass/fail
test cases. Kung tanungin "ano sinabi ng respondents sa survey," sagot: "sinadya
naming hindi gumamit ng survey — objective at scripted test cases, at automated
algorithmic validation ang ginamit namin para maalis ang subjectivity." Iwasan
tawaging "survey" ang evaluation na ito.

**3 evaluation tracks**: (1) Functional/algorithmic validation — Weakness Detection
tested sa 120 simulated scenarios (6 subjects), Precision/Recall/F1 lahat 1.00; SM-2
Scheduler tested sa 204 test cases, 100.00% exact match. (2) Technical benchmarking
via JMeter — simulated 10 to 200 concurrent users; kahit sa 200 users, 1,273ms lang
ang avg response time (below 2,000ms threshold) at 0.00% error rate — "steady at
predictable ang performance kahit tumaas ang load." (3) User Acceptance Testing —
selected Accountancy students at BSA faculty executed 24 scripted test cases across
15 modules, 100.00% pass rate. Walang exact headcount na binanggit sa manuscript —
huwag mag-invent ng number kung tanungin.

Walang qualitative findings o statistical tests (no t-test/ANOVA) — descriptive lang
lahat ng results. Kung tanungin kung bakit, sagot: "scripted/deterministic validation
laban sa known-correct output ang design namin, hindi comparison between groups na
need ng significance testing."

**5 recommendations sa Chapter 5** (future work): (1) longitudinal study na
mag-uugnay sa CPAce usage at actual CPALE passing rates; (2) continuous question
bank/TOS expansion kasabay ng PRC updates; (3) calibrate ang thresholds (60%/5
attempts/3-consecutive) gamit ang real cohort data, at explore FSRS as alternative
sa SM-2; (4) offline/PWA support; (5) extend ang framework sa ibang BatStateU
licensure programs plus dedicated sysadmin console.
