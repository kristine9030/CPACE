-- =============================================================
-- Curriculum versioning, student batch year, and TOS import
-- (migrations 2026_09_30_000000 and 2026_09_30_000001)
--
-- Run ONCE on the production database in phpMyAdmin (select the database
-- first; this file has no USE / CREATE DATABASE / DROP statements).
-- Only adds tables and columns, then backfills them. Safe to run while the
-- site is live: nothing students see changes until the chair publishes a
-- new curriculum.
-- =============================================================

-- 1. Curriculum versions and their history -------------------------------

CREATE TABLE curriculum_versions (
    id                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label                VARCHAR(80) NOT NULL,
    effective_from_batch VARCHAR(9)  NULL,
    effective_to_batch   VARCHAR(9)  NULL,
    status               VARCHAR(10) NOT NULL DEFAULT 'draft',
    created_by           INT UNSIGNED NULL,
    published_at         DATETIME NULL,
    created_at           DATETIME NULL,
    KEY idx_cv_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE curriculum_audits (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    curriculum_version_id INT UNSIGNED NOT NULL,
    subject_id            TINYINT UNSIGNED NULL,
    user_id               INT UNSIGNED NULL,
    action                VARCHAR(30) NOT NULL,
    details               TEXT NULL,
    created_at            DATETIME NULL,
    KEY idx_ca_version (curriculum_version_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Topics belong to a curriculum; keep the TOS weight / item count -------

ALTER TABLE topics
    ADD COLUMN curriculum_version_id INT UNSIGNED NULL AFTER subject_id,
    ADD COLUMN tos_weight DECIMAL(5,2) NULL AFTER sort_order,
    ADD COLUMN tos_items SMALLINT UNSIGNED NULL AFTER tos_weight,
    ADD KEY idx_topics_version (curriculum_version_id);

-- Every existing topic becomes the starting (active) curriculum.
INSERT INTO curriculum_versions (label, status, published_at, created_at)
VALUES ('CPALE Curriculum (current)', 'active', NOW(), NOW());
UPDATE topics SET curriculum_version_id = LAST_INSERT_ID() WHERE curriculum_version_id IS NULL;

-- 3. TOS import staging -----------------------------------------------------

CREATE TABLE curriculum_import_batches (
    id                    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    curriculum_version_id INT UNSIGNED NOT NULL,
    created_by            INT UNSIGNED NULL,
    original_filename     VARCHAR(255) NOT NULL,
    status                VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at            TIMESTAMP NULL,
    updated_at            TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE curriculum_import_items (
    id             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id       INT UNSIGNED NOT NULL,
    subject_id     TINYINT UNSIGNED NOT NULL,
    parent_item_id INT UNSIGNED NULL,
    ref            VARCHAR(30) NULL,
    name           VARCHAR(150) NOT NULL,
    full_name      TEXT NULL,
    depth          TINYINT UNSIGNED NOT NULL DEFAULT 0,
    weight_percent DECIMAL(5,2) NULL,
    item_count     SMALLINT UNSIGNED NULL,
    sort_order     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    included       TINYINT(1) NOT NULL DEFAULT 1,
    KEY idx_cii_batch (batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Student enrollment batch ----------------------------------------------

ALTER TABLE student_profiles
    ADD COLUMN batch_year VARCHAR(9) NULL AFTER section;

-- Existing students: school year of their account's creation date
-- (June starts a new school year), e.g. Aug 2025 -> '2025-2026'.
UPDATE student_profiles sp
JOIN users u ON u.id = sp.user_id
SET sp.batch_year = CONCAT(
        IF(MONTH(u.created_at) >= 6, YEAR(u.created_at), YEAR(u.created_at) - 1),
        '-',
        IF(MONTH(u.created_at) >= 6, YEAR(u.created_at), YEAR(u.created_at) - 1) + 1)
WHERE sp.batch_year IS NULL AND u.created_at IS NOT NULL;

-- 5. Check -----------------------------------------------------------------
-- Expect: one 'active' curriculum, every topic assigned to it, and no student
-- with an empty batch_year.
SELECT id, label, status FROM curriculum_versions;
SELECT COUNT(*) AS topics_without_curriculum FROM topics WHERE curriculum_version_id IS NULL;
SELECT batch_year, COUNT(*) AS students FROM student_profiles GROUP BY batch_year;
