-- =============================================================
-- CPAce — Program Chair feature: incremental setup
-- Run this ONCE against an EXISTING cpace_db (e.g. via phpMyAdmin
-- > cpace_db > SQL tab). It does NOT drop or recreate any data.
--
-- Adds:
--   1. faculty_subjects pivot table (chair assigns faculty -> subjects)
--   2. Default Program Chair (Admin) login
--        email:    chair@cpace.test
--        password: ProgramChair123
-- =============================================================

USE cpace_db;

-- 1. Faculty <-> Subject assignment table
CREATE TABLE IF NOT EXISTS faculty_subjects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faculty_id  INT UNSIGNED NOT NULL,
    subject_id  TINYINT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_faculty_subject (faculty_id, subject_id),
    CONSTRAINT fk_fs_faculty  FOREIGN KEY (faculty_id)  REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_fs_subject  FOREIGN KEY (subject_id)  REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_fs_assigner FOREIGN KEY (assigned_by) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Section catalog + faculty-subject-section restriction (chair assigns
--    faculty -> subject -> specific sections; a subject with no rows here
--    stays unrestricted, i.e. today's "assign by subject" behavior).
CREATE TABLE IF NOT EXISTS sections (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(30) NOT NULL UNIQUE,
    is_active   BOOLEAN NOT NULL DEFAULT TRUE,
    created_at  DATETIME NULL,
    updated_at  DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed the catalog from the section values already on real student accounts.
INSERT IGNORE INTO sections (name, is_active, created_at, updated_at)
SELECT DISTINCT TRIM(section), TRUE, NOW(), NOW()
FROM student_profiles
WHERE section IS NOT NULL AND TRIM(section) != '';

CREATE TABLE IF NOT EXISTS faculty_subject_sections (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faculty_id  INT UNSIGNED NOT NULL,
    subject_id  TINYINT UNSIGNED NOT NULL,
    section_id  INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_at  DATETIME NULL,
    updated_at  DATETIME NULL,
    UNIQUE KEY uq_faculty_subject_section (faculty_id, subject_id, section_id),
    CONSTRAINT fk_fss_faculty  FOREIGN KEY (faculty_id)  REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_fss_subject  FOREIGN KEY (subject_id)  REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_fss_section  FOREIGN KEY (section_id)  REFERENCES sections(id) ON DELETE CASCADE,
    CONSTRAINT fk_fss_assigner FOREIGN KEY (assigned_by) REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2b. Faculty-authored class quizzes (draft / published / closed) shared with
--     students through a link. Questions are copied in, so Test Bank edits
--     never change a quiz that students already took.
CREATE TABLE IF NOT EXISTS faculty_quizzes (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faculty_id          INT UNSIGNED NOT NULL,
    subject_id          TINYINT UNSIGNED NULL,
    title               VARCHAR(150) NOT NULL,
    instructions        TEXT NULL,
    status              VARCHAR(20) NOT NULL DEFAULT 'draft',
    share_token         VARCHAR(40) NOT NULL UNIQUE,
    opens_at            DATETIME NULL,
    due_at              DATETIME NULL,
    time_limit_minutes  SMALLINT UNSIGNED NULL,
    shuffle_questions   BOOLEAN NOT NULL DEFAULT FALSE,
    show_results        BOOLEAN NOT NULL DEFAULT TRUE,
    published_at        DATETIME NULL,
    created_at          DATETIME NULL,
    updated_at          DATETIME NULL,
    INDEX idx_fq_status_due (status, due_at),
    CONSTRAINT fk_fq_faculty FOREIGN KEY (faculty_id) REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_fq_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faculty_quiz_items (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id             BIGINT UNSIGNED NOT NULL,
    source_question_id  INT UNSIGNED NULL,
    question_text       TEXT NOT NULL,
    question_type       VARCHAR(20) NOT NULL DEFAULT 'mcq',
    choices             JSON NOT NULL,
    explanation         TEXT NULL,
    points              SMALLINT UNSIGNED NOT NULL DEFAULT 1,
    sort_order          SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at          DATETIME NULL,
    updated_at          DATETIME NULL,
    CONSTRAINT fk_fqi_quiz FOREIGN KEY (quiz_id) REFERENCES faculty_quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faculty_quiz_attempts (
    id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quiz_id             BIGINT UNSIGNED NOT NULL,
    student_id          INT UNSIGNED NOT NULL,
    started_at          DATETIME NOT NULL,
    submitted_at        DATETIME NULL,
    answers             JSON NULL,
    score               SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    total_points        SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    percent             DECIMAL(5,2) NULL,
    created_at          DATETIME NULL,
    updated_at          DATETIME NULL,
    UNIQUE KEY uq_fqa_quiz_student (quiz_id, student_id),
    CONSTRAINT fk_fqa_quiz    FOREIGN KEY (quiz_id)    REFERENCES faculty_quizzes(id) ON DELETE CASCADE,
    CONSTRAINT fk_fqa_student FOREIGN KEY (student_id) REFERENCES users(id)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Default Program Chair account (Admin role = 1).
--    Safe to re-run: updates the password/role if the email already exists.
INSERT INTO users (role_id, first_name, last_name, email, password, is_active, email_verified)
VALUES (1, 'Program', 'Chair', 'chair@cpace.test',
        '$2y$10$dVCMqvf.kMCjuzYkvTY5kuGSp5JS24MQtE/lAD6Ix9sELgljL.GdC',
        TRUE, TRUE)
ON DUPLICATE KEY UPDATE
    role_id = VALUES(role_id),
    password = VALUES(password),
    is_active = VALUES(is_active),
    email_verified = VALUES(email_verified);
