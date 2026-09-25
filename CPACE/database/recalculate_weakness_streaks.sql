-- ============================================================================
-- One-time repair: performance_records.consecutive_wrong  (CPAce)
--
-- WHY: before the streak fix this column was a running TOTAL of wrong answers
-- (it only reset on a perfect sitting), so an ~90% topic could read
-- "3 wrong in a row". The real streak is the run of wrong answers at the very
-- END of a student's answer history for a topic. This rebuilds it from
-- quiz_answers (same logic as `php artisan performance:recalculate-streaks`).
--
-- SAFE TO RE-RUN (idempotent). Touches only performance_records (streak +
-- weak flag) and weakness_reports (open/resolve) for rows that HAVE answer
-- history; rows with no history are left exactly as they are.
-- No DROP / CREATE DATABASE / USE statements. Uses one TEMPORARY table that
-- vanishes when the session closes.
--
-- REQUIRES: MySQL 8.0+ or MariaDB 10.2+ (window functions).
--   Check first:  SELECT VERSION();
--
-- HOW TO RUN (phpMyAdmin): select the production database, open the SQL tab,
-- run STEP 0 and STEP 1 first and look at the result. If it looks right, run
-- STEP 2 onward (all together). Take an Export of `performance_records` and
-- `weakness_reports` first if you want an undo.
-- ============================================================================


-- ---------------------------------------------------------------------------
-- STEP 0 - build the correct streak per (student, topic)
-- ---------------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_streaks;

CREATE TEMPORARY TABLE tmp_streaks AS
SELECT
    a.student_id,
    a.topic_id,
    -- rn = 1 is the most recent answer. The streak is (position of the most
    -- recent CORRECT answer) - 1; if there is no correct answer, every answer
    -- is wrong and the streak is the whole history.
    COALESCE(MIN(CASE WHEN a.is_correct = 1 THEN a.rn END) - 1, COUNT(*)) AS streak
FROM (
    SELECT
        qs.student_id,
        q.topic_id,
        qa.is_correct,
        ROW_NUMBER() OVER (
            PARTITION BY qs.student_id, q.topic_id
            ORDER BY qa.answered_at DESC, qa.id DESC
        ) AS rn
    FROM quiz_answers qa
    JOIN quiz_sessions qs ON qs.id = qa.session_id
    JOIN questions q      ON q.id  = qa.question_id
    WHERE qs.session_type <> 'training'
      AND qs.is_practice_room = 0
      AND qa.is_correct IS NOT NULL
) a
GROUP BY a.student_id, a.topic_id;


-- ---------------------------------------------------------------------------
-- STEP 1 - PREVIEW (read-only). Every row shown WILL change in STEP 2.
--   Weak rule: streak >= 3, OR (>= 5 attempts AND accuracy < 60%).
-- ---------------------------------------------------------------------------
SELECT
    pr.student_id,
    pr.topic_id,
    pr.correct_count,
    pr.total_attempts,
    pr.consecutive_wrong                                AS streak_now,
    t.streak                                            AS streak_correct,
    pr.is_weak_area                                     AS weak_now,
    (t.streak >= 3
     OR (pr.total_attempts >= 5 AND pr.correct_count / pr.total_attempts < 0.60)) AS weak_correct
FROM performance_records pr
JOIN tmp_streaks t ON t.student_id = pr.student_id AND t.topic_id = pr.topic_id
WHERE pr.total_attempts > 0
  AND (
        pr.consecutive_wrong <> t.streak
     OR pr.is_weak_area <> (t.streak >= 3
            OR (pr.total_attempts >= 5 AND pr.correct_count / pr.total_attempts < 0.60))
  )
ORDER BY pr.student_id, pr.topic_id;


-- ---------------------------------------------------------------------------
-- STEP 2 - apply: correct the streak and the weak flag
-- ---------------------------------------------------------------------------
UPDATE performance_records pr
JOIN tmp_streaks t ON t.student_id = pr.student_id AND t.topic_id = pr.topic_id
SET pr.consecutive_wrong = t.streak,
    pr.is_weak_area = (t.streak >= 3
        OR (pr.total_attempts >= 5 AND pr.correct_count / pr.total_attempts < 0.60))
WHERE pr.total_attempts > 0;


-- ---------------------------------------------------------------------------
-- STEP 3 - keep weakness_reports (feeds the Spaced Repetition Calendar) in step
-- ---------------------------------------------------------------------------
-- 3a. Close reports for topics that are no longer weak.
UPDATE weakness_reports wr
JOIN performance_records pr ON pr.student_id = wr.student_id AND pr.topic_id = wr.topic_id
JOIN tmp_streaks t          ON t.student_id  = wr.student_id AND t.topic_id  = wr.topic_id
SET wr.resolved_at = NOW()
WHERE wr.resolved_at IS NULL
  AND pr.is_weak_area = 0;

-- 3b. Open a report for topics that are now weak and have none open.
INSERT INTO weakness_reports (student_id, topic_id, flagged_at, trigger_reason, accuracy_at_flag)
SELECT pr.student_id,
       pr.topic_id,
       NOW(),
       CASE WHEN pr.consecutive_wrong >= 3 THEN 'consecutive_wrong' ELSE 'low_accuracy' END,
       ROUND(pr.correct_count * 100 / pr.total_attempts, 2)
FROM performance_records pr
JOIN tmp_streaks t ON t.student_id = pr.student_id AND t.topic_id = pr.topic_id
WHERE pr.is_weak_area = 1
  AND pr.total_attempts > 0
  AND NOT EXISTS (
        SELECT 1 FROM weakness_reports wr
        WHERE wr.student_id = pr.student_id
          AND wr.topic_id   = pr.topic_id
          AND wr.resolved_at IS NULL
  );


-- ---------------------------------------------------------------------------
-- STEP 4 - verify: re-run STEP 1. It should now return ZERO rows.
-- (Then clean up:)
-- ---------------------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_streaks;
