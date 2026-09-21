<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Mock Exam workflow: a faculty member assembles a per-subject exam from
 * the Test Bank, the Program Chair reviews and publishes it, and students
 * redeem a day code to sit it.
 *
 * Two deliberate shapes here:
 *
 *  - The redeem code lives on mock_exam_events (one row per exam DAY), not on
 *    the exam itself, so every subject sitting on 2026-09-21 shares one code.
 *    Registration is therefore per-event too, which means an exam published
 *    later that same day is automatically visible to already-registered
 *    students with no backfill.
 *
 *  - mock_exam_items COPIES the question text and choices out of the Test Bank
 *    (same reasoning as faculty_quiz_items) so editing a question months later
 *    can never retroactively change an exam students have already sat.
 *
 * Column types match the real schema in database/cpace_database.sql:
 * users.id is INT UNSIGNED, subjects.id TINYINT UNSIGNED, topics.id
 * SMALLINT UNSIGNED, questions.id INT UNSIGNED.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mock_exam_events')) {
            Schema::create('mock_exam_events', function (Blueprint $table) {
                $table->id();
                // One exam day = one code. Created the first time the Chair
                // publishes an exam scheduled for this date.
                $table->date('exam_date')->unique();
                $table->string('access_code', 32)->unique();
                $table->unsignedInteger('created_by')->nullable();
                $table->timestamps();
                $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('mock_exams')) {
            Schema::create('mock_exams', function (Blueprint $table) {
                $table->id();
                // Null until the Chair publishes it and the day's event is resolved.
                $table->unsignedBigInteger('event_id')->nullable();
                $table->unsignedTinyInteger('subject_id');
                $table->unsignedInteger('created_by');
                $table->string('title', 150);
                // draft = faculty is building it; for_review = handed to the
                // Chair; published = locked and visible to students who
                // redeemed the code; closed = window over.
                $table->string('status', 20)->default('draft');
                // Null while the faculty is still on the early wizard steps -
                // the sitting is chosen last, and a draft has to exist before
                // then so topics/items and the audit trail have somewhere to live.
                $table->dateTime('scheduled_at')->nullable();
                // Real CPALE allots three hours per subject.
                $table->unsignedSmallInteger('duration_minutes')->default(180);
                $table->unsignedSmallInteger('total_items')->default(0);
                // How the exam was assembled - kept for the audit trail so a
                // reviewer can see whether items were hand-picked or generated.
                $table->string('topic_mode', 10)->default('manual');
                $table->string('question_mode', 10)->default('manual');
                $table->text('review_note')->nullable();
                $table->dateTime('submitted_for_review_at')->nullable();
                $table->unsignedInteger('reviewed_by')->nullable();
                $table->unsignedInteger('published_by')->nullable();
                $table->dateTime('published_at')->nullable();
                $table->dateTime('closed_at')->nullable();
                // Optimistic lock: two faculty assigned to the same subject can
                // both open an exam, so a save built on a stale copy is
                // rejected instead of silently clobbering the other's work.
                $table->unsignedInteger('version')->default(1);
                $table->timestamps();
                // "One subject, one exam per sitting" is enforced in the
                // controller rather than by a unique index: drafts legitimately
                // share a null schedule, and only live (non-closed) exams
                // should collide.
                $table->index(['subject_id', 'scheduled_at']);
                $table->index(['status', 'scheduled_at']);
                $table->foreign('event_id')->references('id')->on('mock_exam_events')->nullOnDelete();
                $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
                $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('mock_exam_topics')) {
            Schema::create('mock_exam_topics', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('exam_id');
                $table->unsignedSmallInteger('topic_id');
                $table->unique(['exam_id', 'topic_id']);
                $table->foreign('exam_id')->references('id')->on('mock_exams')->cascadeOnDelete();
                $table->foreign('topic_id')->references('id')->on('topics')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('mock_exam_items')) {
            Schema::create('mock_exam_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('exam_id');
                // Kept so item analysis can point back at the Test Bank, but
                // the exam never reads through it - the snapshot below is the
                // authoritative copy.
                $table->unsignedInteger('source_question_id')->nullable();
                $table->unsignedSmallInteger('topic_id')->nullable();
                $table->text('question_text');
                $table->string('question_type', 20)->default('mcq');
                $table->string('difficulty', 20)->nullable();
                // [{"label":"A","text":"...","is_correct":true}, ...]
                $table->json('choices');
                $table->text('explanation')->nullable();
                $table->unsignedSmallInteger('points')->default(1);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->index(['exam_id', 'sort_order']);
                $table->foreign('exam_id')->references('id')->on('mock_exams')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('mock_exam_audits')) {
            Schema::create('mock_exam_audits', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('exam_id');
                // Null only if the acting user is later deleted; the action
                // itself is never removed.
                $table->unsignedInteger('user_id')->nullable();
                $table->string('action', 40);
                // Short human-readable summary, e.g. "Removed 3 questions, added 5".
                $table->text('details')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->index(['exam_id', 'created_at']);
                $table->foreign('exam_id')->references('id')->on('mock_exams')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('mock_exam_registrations')) {
            Schema::create('mock_exam_registrations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('event_id');
                $table->unsignedInteger('student_id');
                $table->dateTime('redeemed_at');
                $table->unique(['event_id', 'student_id']);
                $table->foreign('event_id')->references('id')->on('mock_exam_events')->cascadeOnDelete();
                $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('mock_exam_attempts')) {
            Schema::create('mock_exam_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('exam_id');
                $table->unsignedInteger('student_id');
                $table->dateTime('started_at');
                $table->dateTime('submitted_at')->nullable();
                // {"<item_id>": "B", ...} - autosaved during the sitting.
                $table->json('answers')->nullable();
                $table->unsignedSmallInteger('score')->default(0);
                $table->unsignedSmallInteger('total_points')->default(0);
                $table->decimal('percent', 5, 2)->nullable();
                // Same server-side reasoning as quiz_sessions.is_late: derived
                // from started_at, never from anything the client reports.
                $table->boolean('is_late')->default(false);
                $table->unsignedSmallInteger('flag_count')->default(0);
                $table->string('status', 20)->default('in_progress');
                $table->timestamps();
                $table->unique(['exam_id', 'student_id']);
                $table->foreign('exam_id')->references('id')->on('mock_exams')->cascadeOnDelete();
                $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_exam_attempts');
        Schema::dropIfExists('mock_exam_registrations');
        Schema::dropIfExists('mock_exam_audits');
        Schema::dropIfExists('mock_exam_items');
        Schema::dropIfExists('mock_exam_topics');
        Schema::dropIfExists('mock_exams');
        Schema::dropIfExists('mock_exam_events');
    }
};
