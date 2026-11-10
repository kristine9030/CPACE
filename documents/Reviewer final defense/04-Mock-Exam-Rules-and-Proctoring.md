# Mock Exam Rules & Proctoring — Full Lifecycle

This is the most rule-dense module, exactly as flagged. Read this one slowly and be
ready to explain it as one continuous story: setup → entry gate → during the exam →
what gets captured → how it's reviewed → what happens if something goes wrong.

## 1. Setup (Faculty → Chair)

- Faculty build a mock exam: pick topics, then pick questions **manually**, via
  **auto-pick** (the system drafts a set and flags if the bank can't fill a topic),
  or **"all."** Maximum 100 items. Default duration is **180 minutes** — deliberately
  modeled on the real CPALE's 3-hour-per-subject format, so the exam *feels* like the
  board exam, not just a long quiz.
- Lifecycle: **draft → for review → published → closed.** The chair can return a
  submitted exam to draft with a required note, or publish it, or close it early
  (anyone still mid-attempt gets to finish).
- **Once published, an exam can never be edited again, by anyone** — this is enforced
  server-side, not just hidden in the UI.
- **No exam/redeem codes.** Eligibility is pure audience targeting: year level and
  section, set by whoever builds the exam. If every targeting field is left empty, the
  exam is open to everyone (so older exams built before this feature still work).
  Removing codes entirely also removes the leaked-code problem.
- Every question is **frozen as a snapshot** at publish time. Editing the live
  question bank afterward can never retroactively change anyone's score on an already-
  published exam.
- A mock exam **does not** create a leaderboard entry and awards no gamification
  points — by design, since it's meant to feel like the real exam, not a scoring game
  — but a poor mock-exam performance **does** still feed weakness detection and
  spaced repetition, because that signal is real and worth acting on.

## 2. The entry gate the student must pass before "Start" unlocks

In order, on the exam's landing screen:

1. **Grant camera access.** Refused with an explanation if blocked.
2. **Share the screen** — and specifically the **entire screen, single monitor**.
   Sharing just a window or a browser tab is detected and rejected outright.
3. **Tick a consent checkbox** acknowledging the recording.

"Start" stays disabled until all three are satisfied. The rules are stated plainly on
this screen before the student commits: camera photo roughly once a minute, screen
capture roughly every ten minutes, and **losing either stream mid-exam locks the exam
while the timer keeps running** — a deliberate choice: disconnecting on purpose to
"pause" the clock gains nothing, which removes the incentive to disconnect.

## 3. What's actually captured, and how often

- **Camera snapshot**: every 2 minutes, plus one the moment the exam starts (kept
  specifically as "who actually sat this exam" evidence) and one on any triggering
  event.
- **Screen snapshot**: every 10 minutes, plus on events.
- **Heartbeat**: a ping every 30 seconds. If the page goes silent for more than 150
  seconds, that gap is recorded server-side as a `connection_gap` — the client cannot
  forge or suppress this, since the server is the one noticing the silence. The
  150-second threshold is deliberately generous because browsers throttle timers in a
  backgrounded tab, so a brief, innocent tab switch shouldn't trip it.
- **Face detection** runs **entirely in the browser** (a client-side face-detection
  model) — no video frame leaves the device for this check; only when an actual flag
  fires does a frame get uploaded as evidence. Checked every 3 seconds: flags
  "no face" after about 9 seconds absent, "multiple faces" after about 6 seconds with
  2+ faces, "looking away" after about 12 seconds off-axis. If the detection model
  fails to load, it fails *safe* — it logs an informational note and never counts
  against the student, rather than silently reporting a false "all clear."
- **Other behavioral flags**, all enforced client-side and reported to the server: tab
  switch / window blur, fullscreen exit, copy/paste/cut and right-click all blocked
  outright, camera or screen stream revoked mid-exam, sharing a partial screen or a
  second monitor detected.

**One line worth saying to the panel almost verbatim**: *a browser physically cannot
be silently screenshotted or have its camera turned on without an explicit permission
prompt and a persistent, visible sharing indicator — there is no way around that, and
the whole design is built around that real constraint rather than claiming covert
surveillance it couldn't actually do.* This is an honest system, and saying so
plainly is a strength, not an admission of weakness.

## 4. Server-side enforcement — not just client trust

Every proctoring endpoint re-checks, on the server: the attempt belongs to the caller,
the attempt hasn't already been submitted, and (for class quizzes) that the faculty
actually turned monitoring on for that specific quiz. Captured images are served only
through an authenticated route that re-checks the viewer is the exam's owning faculty
member or the Program Chair — never a public, guessable file URL, because these are
photographs of students' faces.

**Class quizzes reuse this exact same proctoring mechanism**, parameterized by the
class-quiz attempt instead of the mock-exam attempt, gated by a per-quiz toggle
faculty set when creating the quiz. It's one proctoring system with two entry points,
not two separately built systems — a clean architectural point to make if asked about
code reuse/maintainability.

## 5. Retention policy — privacy by design

- **Clean sitting (zero flags)**: on submit, every captured frame is deleted except
  the single opening camera photo (kept as the "who sat this" record).
- **Flagged sitting**: routine interval frames are deleted; only the frames that
  actually back a real flag, plus the opening photo, are kept.
- Whatever remains auto-purges **14 days** after the exam, or sooner if faculty/chair
  manually review and delete specific frames — but only after the student has
  submitted (deletion is refused while the exam is still in progress, so a live sitting
  can never be "blinded" mid-exam).
- The **event log itself** (timestamps and event types, not images) is never purged —
  it's the durable record even after the photos are gone.

This is a genuinely privacy-conscious design and worth stating plainly: *we only keep
evidence when there's actually something flagged, and even then it auto-expires.*

## 6. The risk score shown to faculty/chair on the live monitor

A weighted points system, computed live (not pre-stored, so the weights could be
tuned later without a migration):

| Flag | Points |
|---|---|
| Camera lost / screen lost | 5 each |
| Multiple faces / partial screen | 4 |
| Second monitor | 3 |
| Blur / connection gap / no face | 2 |
| Looking away / fullscreen exit / paste blocked | 1 |

Each flag type caps at 5 counted occurrences, so a student nervously glancing away 50
times can't drown out one serious flag like a lost camera stream. Score ≥12 = **High**
risk, ≥5 = **Medium**, >0 = **Low**. The code itself documents this as *"a signal for
a human, never a verdict"* — no automated rule acts on the score; it only sorts a busy
monitor table (say, 40 students sitting at once) so a human reviewer can triage by
risk level at a glance.

## 7. Answer-similarity / collusion detection — the statistical centerpiece

This is **not** text-similarity or plagiarism matching. It's a genuine statistical
test for collusion: for every pair of students who submitted the exam, it counts how
many questions they **both got wrong, and picked the identical wrong choice** — not
just both-wrong, but *agreeing on which specific wrong answer*, which is far less
likely by pure chance than simply both getting something wrong (since there are
usually several wrong options to choose between).

It computes the number of shared-wrong-answers you'd expect **by pure chance alone**
for that pair (summing 1 ÷ number-of-wrong-options across every question), works out
the variance, and derives a **z-score**. Pairs with **at least 3 shared wrong answers
and a z-score ≥ 2.0 standard deviations above chance** are surfaced to the reviewer.

This is real inferential statistics, not a heuristic — if a statistically literate
panelist asks, you can say precisely: *"we test whether a pair's agreement on
identical wrong answers is statistically improbable under a null model of independent
random guessing, flagging pairs more than two standard deviations above the expected
value."* And just like the risk score, the code documents this honestly as *"a signal
for a human to look at, never proof"* — two students who studied from the same bad
source, or the same bad classmate's notes, would also trigger this without having
cheated on the exam itself.

## 8. Time and submission mechanics

- Autosave every 20 seconds.
- **The server is the sole authority on time remaining** — it's bounded by both the
  student's personal allotted duration and the exam's overall scheduled end, so a
  late starter gets the remainder of the window, not a fresh full clock. The visible
  countdown in the browser is purely cosmetic; freezing or tampering with it client-
  side changes nothing, because the server independently re-derives the real time left
  when the student submits.
- If time runs out before the student submits, the exam is **auto-graded from the
  last autosaved answers** — both when anyone next loads the attempt and on a
  recurring background check, so a timed-out sitting never sits indefinitely showing
  "in progress." This auto-close is stamped with a server-only note that the client
  cannot forge.

## Summary line for the panel

> "Every proctoring rule in this system is something a browser can actually enforce
> honestly — consent-gated interval photo/screen capture, in-browser face checks,
> and behavioral event logging — backed by server-side authority over time and
> submission, a weighted risk score to help a human triage, and a statistical
> collusion check that's independent of the camera entirely. Nothing here claims to
> see more than a web browser truthfully can."

---

## 🇵🇭 Sa Taglish

**Setup**: Faculty builds the mock exam — pick topics, then questions (manual,
auto-pick, or "all"), up to 100 items, default 180 minutes (gaya ng 3-hour format ng
actual CPALE). Draft → for review → published → closed. **Once published, hindi na
pwede baguhin ninuman** — enforced sa server, hindi lang sa UI. Walang exam code —
automatic ang eligibility base sa year/section. Frozen snapshot ang questions sa
publish time, kaya kahit baguhin pa ang Test Bank, hindi na maaapektuhan ang score
ng mga nakasagot na. Walang points/leaderboard ang mock exam (sinadya, para feel na
totoong exam), pero affects pa rin ang weakness detection at spaced repetition.

**Entry gate bago makapagsimula ang student**: (1) grant camera access, (2) share
*entire screen* lang (hindi window/tab — rejected ito), (3) tick ang consent
checkbox. Hindi mag-unlock ang "Start" hangga't incomplete ito. Kung mawala ang
camera/screen share mid-exam, naka-lock ang exam pero tumatakbo pa rin ang timer —
sinadya para walang pakinabang ang pag-disconnect.

**Ano talaga nakukuha**: camera photo every 2 min, screen capture every 10 min,
heartbeat ping every 30s (kung tumahimik >150s, naka-log 'to server-side, hindi
mapeke ng client). Face detection runs entirely client-side sa browser — walang
frame na umu-upload maliban kung may actual flag. Plus behavioral flags: tab
switch, fullscreen exit, copy/paste blocked, camera/screen lost, partial/second
monitor.

**Important point to say sa panel**: hindi talaga pwedeng silently capture ang
screen/camera ng browser nang walang explicit permission at visible indicator —
walang way para itago sa student. So honest system ito, hindi "secret
surveillance" claim — strength ito, hindi weakness.

Server-side enforcement din lahat (not just client trust) — every proctoring
endpoint re-checks ownership, submission status, at monitoring toggle. Class Quiz
reuses the exact same proctoring mechanism (one system, two entry points).

**Retention policy** (privacy-conscious): clean sitting (no flags) — deleted lahat
except opening photo. Flagged sitting — kept lang yung frames na actually may
kinalaman sa flag. Auto-purge after 14 days regardless. Event log mismo (hindi mga
larawan) ang hindi kailanman na-purge.

**Risk score** — weighted points per flag type (camera/screen lost = 5, multiple
faces/partial screen = 4, second monitor = 3, blur/connection gap/no face = 2,
looking away/fullscreen exit/paste = 1), capped sa 5 occurrences per type. ≥12 =
High, ≥5 = Medium. Documented sa code mismo as "signal para sa tao, never a
verdict" — walang automated action based lang dito.

**Similarity/collusion check** — totoong statistical test, hindi text-matching: for
every pair of students, counts questions where both got wrong AND picked the same
wrong choice specifically, computes expected matches by pure chance, derives a
z-score. Flags pairs with ≥3 shared wrong answers AND z-score ≥2.0 SD above chance.
Say it exactly ganito sa panel: "we test whether a pair's agreement on identical
wrong answers is statistically improbable under random guessing." Documented din as
"signal for a human, never proof."

**Time/submission**: autosave every 20s, server ang sole authority sa time
remaining (client countdown is cosmetic lang). Kung na-timeout, auto-graded based
sa last autosaved answers.

**Summary line**: "Every proctoring rule here is something a browser can actually
enforce honestly — consent-gated capture, in-browser face checks, behavioral
logging — backed by server-side time/submission authority, a weighted risk score,
and a statistical collusion check independent of the camera. Nothing claims to see
more than a browser truthfully can."
