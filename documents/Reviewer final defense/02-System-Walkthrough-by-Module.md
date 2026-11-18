# System Walkthrough by Module

Organized by role, the way a panelist will picture the system: what can each user do,
screen by screen. Everything below traces to real, working code — nothing here is a
mock-up or a stub. Where something is still a calibration item rather than a missing
feature, it's called out explicitly.

---

## STUDENT

**Dashboard** — day streak, total points, exam countdown, questions attempted
(practice-room/training sessions excluded so they can't inflate the number), study
hours, an overall "Board Readiness" percentage, per-subject mastery, and top-3
weak/strong topics pulled from the shared `WeaknessDetector`. All computed live from
real tables, nothing hardcoded.

**Subjects / Resources** — a grid of subjects with live topic/question counts and
weak-topic counts. Drilling into a subject shows its topic tree (respecting whatever
curriculum version and audience targeting applies to that student), and each topic
shows its materials. The topic's **"About" button** (the feature added this session)
sits right next to the topic name and pops up the topic's description — "No
description has been added for this topic yet" if the chair hasn't written one.

**Practice Quiz** — four modes:
- *Adaptive*: weights question selection toward the student's weak topics.
- *Topic Mode*: student picks a topic (or the system suggests their weakest).
- *Challenge*: skews toward harder questions, 1.5× point multiplier.
- *Timed*: random draw, server-enforced per-question time limit (the client-side
  countdown is cosmetic — the server is the authority).

Every retake re-shuffles answer order, re-labels A/B/C/D, and paraphrases the question
text, so a question can't be memorized by position across retakes. "Training" and
"Practice Room" sessions are deliberately excluded from analytics — only real
"testing" attempts feed weakness detection and spaced repetition.

**Class Quiz** — faculty-authored, shared via a link, deadline and time limit enforced
server-side. If the faculty member turned on monitoring for that quiz, the same
proctoring mechanism used in mock exams applies (see `04-Mock-Exam-Rules-and-Proctoring.md`
— it's one proctoring system reused for both, not two separate builds).

**Mock Exam** — the highest-stakes mode, fully proctored, no exam code needed
(eligibility is automatic by year level/section, set by the chair). Full rules in
`04-Mock-Exam-Rules-and-Proctoring.md`.

**Calendar** — shows real spaced-repetition due dates (from `SpacedRepetitionScheduler`),
color-coded per subject, priority-ranked by weakness. Students can also add their own
manual study blocks alongside the auto-scheduled reviews. A student with prior quiz
history but no schedule yet (e.g. someone who used the app before this feature
existed) gets one bootstrapped automatically from their existing accuracy — nobody
sees an empty calendar.

**Performance** — the same source tables as the Dashboard, with week-over-week deltas
and per-subject charts.

**Other features**: Quiz History, Review Notes (with an AI quiz-generation action and
a floating AI Tutor chat), Achievements (badges/leaderboard, computed live), Settings,
and Community/Messenger (shared with alumni).

---

## FACULTY

**Dashboard** — test-bank size and composition, active-student counts, average score
vs. a 75% board-readiness benchmark, a student-readiness breakdown (Ready / Developing
/ At-risk / Not-yet-measurable), and auto-generated narrative insights (e.g. "Only 12%
of the bank is Hard difficulty — add more challenging items"). Scoped to the subjects
the chair has assigned that faculty member.

**Materials** — upload learning materials per subject/topic (PDF, Office formats,
images, video, zip/rar, up to 20MB each, bulk upload supported), save as draft or
publish. Has the same topic "About" button as the student side.

**Test Bank** — faculty author questions scoped to their assigned subjects (chair/
admin see everything). Two question types: **Multiple Choice** (2–26 choices) and
**True/False**, three difficulty levels (Easy/Medium/Hard), topic-tagged, optional
image/table exhibit, optional explanation. AI-assisted drafting is available (rate-
limited, grounded in the subject/topic and existing questions to avoid duplicates),
plus AI-or-rule-based "variants" — alternate wordings of the same question to
discourage memorization, without changing the stored correct answer. Supports bulk
import from PDF/Word/Excel/CSV/image with a staged-review step before committing, and
export to Excel/JSON/PDF.

**Class Quiz** — assemble a fixed quiz (up to 100 items) from the Test Bank, toggle
`show_results`, `shuffle_questions`, and the **proctoring toggle**. Publish, share the
link, then watch submissions live or review them afterward.

**Mock Exam Building** — pick topics, then pick questions manually or via "auto-pick"
(which flags it if the bank can't supply enough items for a topic), set audience
targeting (year/section), then submit to the chair for review. Once submitted for
review, faculty can no longer edit it; once published, it can **never** be edited
again by anyone.

**Monitoring** — faculty share the same live monitor, flag feed, and answer-similarity
screen the chair uses, scoped to their own subject's sitting.

**Performance Reports** — two screens: a live, filterable, exportable dashboard (with
a real "send reminder" action that creates a notification/email) and a set of
print-ready paper reports (Class Performance Summary, At-Risk, Subject Mastery,
Question Quality).

---

## PROGRAM CHAIR (and Super Admin)

**Dashboard** — program-wide overview, refreshed via AJAX.

**Subject & Curriculum Management** — full subject/topic CRUD, including nested
(parent/child) topics. **Curriculum versioning is real**: draft a new curriculum
version, publish it, or discard it, and carry a subject's questions forward into the
new version. A single enforcement point (`CurriculumScope`) decides what a student
actually sees to quiz on, keyed to that student's `batch_year` — so an already-
enrolled batch keeps the curriculum it started on even after a newer version is
published for incoming batches. There's also a PRC Table-of-Specifications import
(upload → review → commit).

**Topic-level year/section/semester targeting** — this was built and merged this
session and is **fully implemented**, not just planned: a topic can be restricted to
specific year levels, sections, and/or semesters; students outside that audience never
see it in quizzes, while chair/faculty program-wide views always show every topic
regardless (so management views never lose anything). If a dimension is left unset,
or a student's section name doesn't parse cleanly into a semester, the system **fails
open** (shows the topic) rather than silently hiding content — a deliberate safety
choice.

**Student Management** — roster CRUD, bulk import/export, OTP regeneration, bulk
alumni marking, section assignment, and a full per-student detail page showing
subject-by-subject accuracy and the same weak-topic list (via `WeaknessDetector`) the
student sees about themselves.

**Faculty / Accounts page** (`/chair/faculty`) — one merged page, two tabs:
- *Accounts*: faculty roster, assigned subjects/sections, create/edit/toggle/assign,
  bulk actions, reminders, OTP regeneration.
- *Performance Report*: system-wide faculty contribution and question-quality report
  (questions authored, variants, quiz answers graded, accuracy, quality flags, last
  contribution), scoped to the **active curriculum only** by design — so a freshly
  published, still-empty curriculum version doesn't make faculty look like they
  haven't contributed.

**Mock Exam Review** — chair reviews what faculty submit: return for revision (with a
required note), publish (blocked until audience targeting is set), or close early
(in-progress attempts still get to finish). Live monitor during the exam, with a
per-student risk score, flag counts, latest camera snapshot, and heartbeat status; a
separate answer-similarity screen for collusion signals. Full detail in
`04-Mock-Exam-Rules-and-Proctoring.md`.

**Other chair features found in the system**: Section management, AI-substitute
question review (for topics where the bank is short on questions matching the TOS),
Help/Support ticket triage, Announcements, and program-wide analytics.

**Super Admin** is a distinct, higher role above Program Chair, with its own area
including API token management and the evaluation harness referenced in
`01-Weakness-Detection-and-Spaced-Repetition.md`.

---

## 🇵🇭 Sa Taglish

**Student side**: Dashboard shows real-time streak, points, exam countdown, Board
Readiness %, at top-3 weak/strong topics — walang hardcoded data. Subjects/Resources
has the topic tree with the new "About" button (shows description, o "no description
yet" kung wala pang nilagay ang chair). Practice Quiz has 4 modes (Adaptive, Topic
Mode, Challenge, Timed) — shuffle ng choices at paraphrasing per retake para hindi
ito mamemorize lang. Class Quiz is faculty-made, may optional proctoring. Mock Exam
is the most serious/proctored mode, no exam code needed (automatic eligibility by
year/section). Calendar shows real spaced-repetition due dates, weak topics as High
priority. Plus Quiz History, Review Notes w/ AI Tutor, Achievements, Settings,
Community.

**Faculty side**: Dashboard shows test bank stats at student readiness breakdown.
Materials is for uploading learning materials per topic (has the About button too).
Test Bank is where faculty author questions — Multiple Choice at True/False lang,
3 difficulty levels, may AI-assist at "variants" (iba't ibang phrasing ng same
question). Class Quiz building — up to 100 items, may proctoring toggle. Mock Exam
Building — pick topics/questions, set audience targeting, submit for chair review
(hindi na pwede baguhin pagka-published). Monitoring — same live monitor/similarity
screen ng chair pero sa kanilang subject lang. Performance Reports — live dashboard
plus print-ready reports.

**Program Chair / Super Admin side**: Dashboard is program-wide overview. Subject &
Curriculum Management handles curriculum versioning (draft → publish → discard,
totoo at gumagana ito, hindi lang UI) — isang system lang ang nagdedesisyon kung
aling curriculum version makikita ng isang student base sa batch year nila, kaya
hindi maaapektuhan ang mga naka-enroll na kapag mag-publish ng bagong version.

**Important note**: ang topic-level audience targeting (year/section/semester) —
*fully implemented na, hindi lang plano* kahit may naunang plan file na nagsabing
darating pa lang ito. Kung wala namang itinakdang audience o hindi ma-parse ang
section name, ipapakita na lang ng system ang topic (fail open) imbis na itago —
sinadya para walang student na biglang mawalan ng access.

Student Management may per-student detail page na may weak-topic list din (same
WeaknessDetector signal na nakikita ng student mismo). Faculty/Accounts page has 2
tabs — Accounts at Performance Report (scoped sa active curriculum lang, sinadya
para hindi mukhang walang ambag ang faculty sa bagong-publish pa lang na curriculum).
Mock Exam Review — chair reviews what faculty submit (return/publish/close), may
live monitor na may risk score at flag counts, plus similarity-check screen for
possible collusion. Other features: section management, AI-substitute question
review, support tickets, announcements, program-wide analytics. Super Admin is a
separate, higher role with its own API token management area.
