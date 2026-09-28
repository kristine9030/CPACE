-- =============================================================
-- AI substitute questions (migration 2026_10_01_000000)
--
-- Run ONCE on the production database in phpMyAdmin (select the database
-- first; this file has no USE / CREATE DATABASE / DROP statements).
-- Only adds columns and relaxes questions.created_by to allow NULL (an AI
-- question has no faculty author). Existing questions keep source =
-- 'faculty' and review_status = NULL, so nothing students see changes.
-- =============================================================

ALTER TABLE questions
    ADD COLUMN source        VARCHAR(20)  NOT NULL DEFAULT 'faculty' AFTER created_by,
    ADD COLUMN review_status VARCHAR(10)  NULL AFTER is_active,
    ADD COLUMN reviewed_by   INT UNSIGNED NULL AFTER review_status,
    ADD COLUMN reviewed_at   DATETIME     NULL AFTER reviewed_by,
    ADD KEY questions_source_review_status_index (source, review_status);

ALTER TABLE questions MODIFY created_by INT UNSIGNED NULL;

ALTER TABLE topics
    ADD COLUMN gap_flagged_at DATETIME NULL AFTER tos_items,
    ADD COLUMN gap_warned_at  DATETIME NULL AFTER gap_flagged_at;

-- Check: expect source/review_status/reviewed_by/reviewed_at, created_by NULL = YES,
-- and every existing question counted under 'faculty'.
SHOW COLUMNS FROM questions WHERE Field IN ('created_by', 'source', 'review_status', 'reviewed_by', 'reviewed_at');
SHOW COLUMNS FROM topics LIKE 'gap%';
SELECT source, COUNT(*) AS questions FROM questions GROUP BY source;
