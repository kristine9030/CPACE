-- =============================================================
-- CPAce: Adaptive Board Exam Review System
-- Normalized MySQL Database Schema (3NF)
-- Based on Chapter 1-3, Context Diagram, and Diagram 0
-- =============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP DATABASE IF EXISTS cpace_db;
CREATE DATABASE cpace_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cpace_db;

-- =============================================================
-- 1. USERS & ROLES
-- Covers: System Administrator, Accountancy Student,
--         BSA Faculty, BatStateU BSA Alumni
-- =============================================================

CREATE TABLE roles (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(30) NOT NULL UNIQUE  -- 'admin','student','faculty','alumni'
);

CREATE TABLE users (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id         TINYINT UNSIGNED NOT NULL,
    first_name      VARCHAR(60)  NOT NULL,
    last_name       VARCHAR(60)  NOT NULL,
    email           VARCHAR(120) NOT NULL UNIQUE,
    password        VARCHAR(255) NOT NULL,             -- bcrypt hash
    remember_token  VARCHAR(100) NULL,                 -- Laravel "remember me"
    profile_photo   VARCHAR(255) NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified  BOOLEAN NOT NULL DEFAULT FALSE,
    last_login_at   DATETIME NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
);

-- Student-specific profile (extends users)
CREATE TABLE student_profiles (
    user_id         INT UNSIGNED PRIMARY KEY,
    student_number  VARCHAR(20)  NULL UNIQUE,
    year_level      TINYINT UNSIGNED NULL,             -- 4 or 5 for BSA
    section         VARCHAR(10)  NULL,
    exam_target_date DATE        NULL,                 -- expected CPALE exam date
    total_points    INT UNSIGNED NOT NULL DEFAULT 0,   -- gamification points
    streak_days     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_sp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Faculty-specific profile
CREATE TABLE faculty_profiles (
    user_id         INT UNSIGNED PRIMARY KEY,
    employee_number VARCHAR(20)  NULL UNIQUE,
    department      VARCHAR(100) NULL,
    CONSTRAINT fk_fp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Faculty <-> Subject assignments (set by the Program Chair / Admin role)
CREATE TABLE faculty_subjects (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    faculty_id  INT UNSIGNED NOT NULL,              -- users.id with role 'faculty'
    subject_id  TINYINT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NULL,                  -- users.id of the chair who assigned
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_faculty_subject (faculty_id, subject_id),
    CONSTRAINT fk_fs_faculty  FOREIGN KEY (faculty_id)  REFERENCES users(id)    ON DELETE CASCADE,
    CONSTRAINT fk_fs_subject  FOREIGN KEY (subject_id)  REFERENCES subjects(id) ON DELETE CASCADE,
    CONSTRAINT fk_fs_assigner FOREIGN KEY (assigned_by) REFERENCES users(id)    ON DELETE SET NULL
);

-- Alumni-specific profile
CREATE TABLE alumni_profiles (
    user_id         INT UNSIGNED PRIMARY KEY,
    batch_year      YEAR        NULL,
    cpa_number      VARCHAR(30) NULL,
    passed_at       DATE        NULL,                  -- date CPA license obtained
    CONSTRAINT fk_ap_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================================
-- 2. CPALE SUBJECTS & TOPICS (Question Bank structure)
-- 6 subjects: FAR, AFAR, MS, TAX, AUD, RFBT
-- =============================================================

CREATE TABLE subjects (
    id          TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(10) NOT NULL UNIQUE,   -- e.g., 'FAR', 'AFAR', 'MS'
    name        VARCHAR(100) NOT NULL,
    description TEXT NULL
);

CREATE TABLE topics (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id  TINYINT UNSIGNED NOT NULL,
    name        VARCHAR(150) NOT NULL,
    description TEXT NULL,
    CONSTRAINT fk_topics_subject FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

-- =============================================================
-- 3. QUESTION BANK
-- Managed by Faculty; used by Adaptive Quiz & Mock Exam
-- =============================================================

CREATE TABLE questions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    topic_id        SMALLINT UNSIGNED NOT NULL,
    created_by      INT UNSIGNED NOT NULL,             -- faculty user_id
    question_text   TEXT NOT NULL,
    question_type   ENUM('mcq','true_false') NOT NULL DEFAULT 'mcq',
    difficulty      ENUM('easy','moderate','difficult') NOT NULL DEFAULT 'moderate',
    explanation     TEXT NULL,                         -- shown after answer in Training Mode
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_q_topic   FOREIGN KEY (topic_id)   REFERENCES topics(id),
    CONSTRAINT fk_q_creator FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE question_choices (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    question_id     INT UNSIGNED NOT NULL,
    choice_label    CHAR(1) NOT NULL,                  -- A, B, C, D
    choice_text     TEXT NOT NULL,
    is_correct      BOOLEAN NOT NULL DEFAULT FALSE,
    CONSTRAINT fk_qc_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

-- =============================================================
-- 4. QUIZ SESSIONS
-- Training Mode  = immediate feedback, no timer
-- Testing Mode   = timed, deferred feedback
-- Mock Exam      = full-length, timed, all 6 subjects
-- =============================================================

CREATE TABLE quiz_sessions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED NOT NULL,
    session_type    ENUM('training','testing','mock_exam','spaced_review') NOT NULL,
    subject_id      TINYINT UNSIGNED NULL,             -- NULL = mock exam (all subjects)
    topic_id        SMALLINT UNSIGNED NULL,            -- NULL = full-subject or mock
    started_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    completed_at    DATETIME NULL,
    total_items     SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    correct_answers SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    score_percent   DECIMAL(5,2) NULL,                 -- computed on completion
    duration_secs   INT UNSIGNED NULL,                 -- time taken
    CONSTRAINT fk_qs_student FOREIGN KEY (student_id) REFERENCES users(id),
    CONSTRAINT fk_qs_subject FOREIGN KEY (subject_id) REFERENCES subjects(id),
    CONSTRAINT fk_qs_topic   FOREIGN KEY (topic_id)   REFERENCES topics(id)
);

CREATE TABLE quiz_answers (
    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id      INT UNSIGNED NOT NULL,
    question_id     INT UNSIGNED NOT NULL,
    selected_choice INT UNSIGNED NULL,                 -- NULL = skipped
    is_correct      BOOLEAN NULL,
    answered_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_qa_session  FOREIGN KEY (session_id)      REFERENCES quiz_sessions(id) ON DELETE CASCADE,
    CONSTRAINT fk_qa_question FOREIGN KEY (question_id)     REFERENCES questions(id),
    CONSTRAINT fk_qa_choice   FOREIGN KEY (selected_choice) REFERENCES question_choices(id)
);

-- =============================================================
-- 5. PERFORMANCE ANALYTICS  (D2 in Diagram 0)
-- Tracks per-topic accuracy for weakness detection & SM-2
-- =============================================================

CREATE TABLE performance_records (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED NOT NULL,
    topic_id        SMALLINT UNSIGNED NOT NULL,
    total_attempts  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    correct_count   SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    accuracy_rate   DECIMAL(5,2) GENERATED ALWAYS AS (
                        CASE WHEN total_attempts = 0 THEN 0
                             ELSE (correct_count / total_attempts) * 100 END
                    ) STORED,
    consecutive_wrong TINYINT UNSIGNED NOT NULL DEFAULT 0,
    is_weak_area    BOOLEAN NOT NULL DEFAULT FALSE,    -- flagged by weakness detection
    last_attempted  DATETIME NULL,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_topic (student_id, topic_id),
    CONSTRAINT fk_pr_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_pr_topic   FOREIGN KEY (topic_id)   REFERENCES topics(id)
);

-- Weakness detection log - records each time the algorithm flags/unflags a topic
CREATE TABLE weakness_reports (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED NOT NULL,
    topic_id        SMALLINT UNSIGNED NOT NULL,
    flagged_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resolved_at     DATETIME NULL,
    trigger_reason  ENUM('low_accuracy','consecutive_wrong') NOT NULL,
    accuracy_at_flag DECIMAL(5,2) NULL,
    CONSTRAINT fk_wr_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_wr_topic   FOREIGN KEY (topic_id)   REFERENCES topics(id)
);

-- =============================================================
-- 6. SPACED REPETITION SCHEDULER (SM-2 Algorithm)
-- One row per student-question pair
-- =============================================================

CREATE TABLE spaced_repetition_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED NOT NULL,
    question_id     INT UNSIGNED NOT NULL,
    repetition_num  SMALLINT UNSIGNED NOT NULL DEFAULT 0,  -- n in SM-2
    ease_factor     DECIMAL(4,2) NOT NULL DEFAULT 2.50,    -- EF, min 1.3
    interval_days   SMALLINT UNSIGNED NOT NULL DEFAULT 1,  -- I(n)
    quality_score   TINYINT UNSIGNED NULL,                 -- q: 0-5
    next_review_at  DATE NOT NULL,
    last_reviewed   DATE NULL,
    UNIQUE KEY uq_student_question (student_id, question_id),
    CONSTRAINT fk_sr_student  FOREIGN KEY (student_id)  REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sr_question FOREIGN KEY (question_id) REFERENCES questions(id)
);

-- =============================================================
-- 7. STUDY PLANS  (output of Generate Study Plan & Spaced Repetition)
-- =============================================================

CREATE TABLE study_plans (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id      INT UNSIGNED NOT NULL,
    generated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    exam_target_date DATE NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE
);

CREATE TABLE study_plan_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    plan_id         INT UNSIGNED NOT NULL,
    topic_id        SMALLINT UNSIGNED NOT NULL,
    scheduled_date  DATE NOT NULL,
    priority        ENUM('high','medium','low') NOT NULL DEFAULT 'medium',
    is_completed    BOOLEAN NOT NULL DEFAULT FALSE,
    completed_at    DATETIME NULL,
    CONSTRAINT fk_spi_plan  FOREIGN KEY (plan_id)  REFERENCES study_plans(id) ON DELETE CASCADE,
    CONSTRAINT fk_spi_topic FOREIGN KEY (topic_id) REFERENCES topics(id)
);

-- =============================================================
-- 8. MOCK EXAM RESULTS (tied back to quiz_sessions for full data)
-- =============================================================

CREATE TABLE mock_exam_results (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    session_id      INT UNSIGNED NOT NULL UNIQUE,
    student_id      INT UNSIGNED NOT NULL,
    -- per-subject breakdown
    far_score       DECIMAL(5,2) NULL,
    afar_score      DECIMAL(5,2) NULL,
    ms_score        DECIMAL(5,2) NULL,
    tax_score       DECIMAL(5,2) NULL,
    aud_score       DECIMAL(5,2) NULL,
    rfbt_score      DECIMAL(5,2) NULL,
    overall_score   DECIMAL(5,2) NULL,
    is_passing      BOOLEAN NULL,
    taken_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_mer_session FOREIGN KEY (session_id)  REFERENCES quiz_sessions(id),
    CONSTRAINT fk_mer_student FOREIGN KEY (student_id)  REFERENCES users(id)
);

-- =============================================================
-- 9. STUDY RESOURCES   (D3 in Diagram 0)
-- Faculty uploads; students download
-- =============================================================

CREATE TABLE resource_categories (
    id      TINYINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name    VARCHAR(80) NOT NULL UNIQUE    -- e.g., 'Lecture Notes', 'Practice Exercises'
);

CREATE TABLE study_resources (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uploaded_by     INT UNSIGNED NOT NULL,
    subject_id      TINYINT UNSIGNED NULL,
    category_id     TINYINT UNSIGNED NULL,
    title           VARCHAR(200) NOT NULL,
    description     TEXT NULL,
    file_path       VARCHAR(255) NOT NULL,
    file_type       VARCHAR(20) NULL,                  -- 'pdf', 'docx', 'pptx', etc.
    file_size_kb    INT UNSIGNED NULL,
    is_active       BOOLEAN NOT NULL DEFAULT TRUE,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sr_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id),
    CONSTRAINT fk_sr_subject  FOREIGN KEY (subject_id)  REFERENCES subjects(id),
    CONSTRAINT fk_sr_category FOREIGN KEY (category_id) REFERENCES resource_categories(id)
);

-- =============================================================
-- 10. COMMUNITY HUB     (D4 in Diagram 0)
-- Posts, replies - students, faculty, alumni
-- =============================================================

CREATE TABLE community_posts (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    author_id       INT UNSIGNED NOT NULL,
    subject_id      TINYINT UNSIGNED NULL,             -- optional subject tag
    title           VARCHAR(200) NULL,
    body            TEXT NOT NULL,
    post_type       ENUM('tip','question','resource','discussion') NOT NULL DEFAULT 'discussion',
    is_pinned       BOOLEAN NOT NULL DEFAULT FALSE,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_cp_author  FOREIGN KEY (author_id)  REFERENCES users(id),
    CONSTRAINT fk_cp_subject FOREIGN KEY (subject_id) REFERENCES subjects(id)
);

CREATE TABLE community_replies (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id     INT UNSIGNED NOT NULL,
    author_id   INT UNSIGNED NOT NULL,
    body        TEXT NOT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cr_post   FOREIGN KEY (post_id)   REFERENCES community_posts(id) ON DELETE CASCADE,
    CONSTRAINT fk_cr_author FOREIGN KEY (author_id) REFERENCES users(id)
);

-- =============================================================
-- 11. GAMIFICATION
-- Points, Badges, Streaks
-- =============================================================

CREATE TABLE badges (
    id          SMALLINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    icon        VARCHAR(100) NULL,
    criteria    VARCHAR(255) NULL                     -- human-readable earn condition
);

CREATE TABLE student_badges (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id  INT UNSIGNED NOT NULL,
    badge_id    SMALLINT UNSIGNED NOT NULL,
    earned_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_student_badge (student_id, badge_id),
    CONSTRAINT fk_sb_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sb_badge   FOREIGN KEY (badge_id)   REFERENCES badges(id)
);

CREATE TABLE points_log (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id  INT UNSIGNED NOT NULL,
    points      SMALLINT NOT NULL,                     -- can be negative (correction)
    reason      VARCHAR(100) NOT NULL,                 -- 'quiz_completed', 'badge_earned', etc.
    reference_id INT UNSIGNED NULL,                    -- session_id or other FK
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pl_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================================
-- 12. NOTIFICATIONS   (Process 9.0 in Diagram 0)
-- =============================================================

CREATE TABLE notifications (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    recipient_id    INT UNSIGNED NOT NULL,
    type            VARCHAR(50) NOT NULL,               -- 'review_reminder','new_post','score_ready'
    title           VARCHAR(150) NOT NULL,
    message         TEXT NULL,
    is_read         BOOLEAN NOT NULL DEFAULT FALSE,
    reference_type  VARCHAR(50) NULL,                  -- 'quiz_session','community_post', etc.
    reference_id    INT UNSIGNED NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_recipient FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- =============================================================
-- 13. SYSTEM LOGS  (Audit trail for Admins)
-- =============================================================

CREATE TABLE system_logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NULL,                     -- NULL = system action
    action      VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NULL,
    target_id   INT UNSIGNED NULL,
    ip_address  VARCHAR(45) NULL,
    details     TEXT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sl_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- =============================================================
-- SEED: Roles
-- =============================================================

INSERT INTO roles (name) VALUES
    ('admin'), ('student'), ('faculty'), ('alumni');

-- =============================================================
-- SEED: CPALE Subjects
-- =============================================================

INSERT INTO subjects (code, name) VALUES
    ('FAR',  'Financial Accounting and Reporting'),
    ('AFAR', 'Advanced Financial Accounting and Reporting'),
    ('MS',   'Management Services'),
    ('TAX',  'Taxation'),
    ('AUD',  'Auditing and Assurance'),
    ('RFBT', 'Regulatory Framework for Business Transactions');

-- =============================================================
-- SEED: Sample Topics per Subject (partial - faculty adds more)
-- =============================================================

INSERT INTO topics (subject_id, name) VALUES
    -- FAR (subject_id = 1)
    (1, 'Cash and Cash Equivalents'),
    (1, 'Receivables'),
    (1, 'Inventories'),
    (1, 'Property, Plant and Equipment'),
    (1, 'Intangible Assets'),
    -- AFAR (subject_id = 2)
    (2, 'Business Combinations'),
    (2, 'Consolidated Financial Statements'),
    (2, 'Foreign Currency Transactions'),
    (2, 'Branch Accounting'),
    -- MS (subject_id = 3)
    (3, 'Cost-Volume-Profit Analysis'),
    (3, 'Budgeting and Budgetary Control'),
    (3, 'Standard Costing'),
    (3, 'Capital Budgeting'),
    -- TAX (subject_id = 4)
    (4, 'Income Tax - Individuals'),
    (4, 'Income Tax - Corporations'),
    (4, 'Value Added Tax'),
    (4, 'Percentage Tax'),
    -- AUD (subject_id = 5)
    (5, 'Philippine Standards on Auditing'),
    (5, 'Audit Planning and Risk Assessment'),
    (5, 'Audit Evidence and Procedures'),
    (5, 'Audit Reports'),
    -- RFBT (subject_id = 6)
    (6, 'Law on Contracts'),
    (6, 'Corporation Code'),
    (6, 'Negotiable Instruments Law'),
    (6, 'Insurance Code');

-- =============================================================
-- SEED: Resource Categories
-- =============================================================

INSERT INTO resource_categories (name) VALUES
    ('Lecture Notes'),
    ('Practice Exercises'),
    ('CPALE Guides'),
    ('Past Board Exam Items'),
    ('Cheat Sheets');

-- =============================================================
-- SEED: Badges
-- =============================================================

INSERT INTO badges (name, description, criteria) VALUES
    ('First Quiz',    'Completed your first quiz',            'Complete 1 quiz session'),
    ('Week Streak',   '7-day study streak',                   'Log in and study for 7 consecutive days'),
    ('Subject Master','Achieved 80%+ accuracy in a subject',  'Accuracy >= 80% across all topics of one subject'),
    ('Mock Ready',    'Completed a full Mock Exam',            'Complete 1 mock_exam session'),
    ('No Weakness',   'Cleared all weak areas in a subject',  'is_weak_area = FALSE for all topics in a subject');

-- =============================================================
-- SEED: Program Chair (Admin role) — default login
-- email: chair@cpace.test   password: ProgramChair123
-- =============================================================

INSERT INTO users (role_id, first_name, last_name, email, password, is_active, email_verified)
VALUES (1, 'Program', 'Chair', 'chair@cpace.test',
        '$2y$10$dVCMqvf.kMCjuzYkvTY5kuGSp5JS24MQtE/lAD6Ix9sELgljL.GdC',
        TRUE, TRUE);

SET FOREIGN_KEY_CHECKS = 1;
