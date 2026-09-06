<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Faculty-authored class quizzes. Unlike the adaptive quiz engine (which draws
 * random questions from the Test Bank per student), a class quiz is a fixed
 * set of questions the faculty assembles, publishes with a deadline, and
 * shares with students through a link. Questions are COPIED into
 * faculty_quiz_items at build time so later Test Bank edits never silently
 * change a quiz students have already taken.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('faculty_quizzes')) {
            Schema::create('faculty_quizzes', function (Blueprint $table) {
                $table->id();
                // Match the real column types: users.id is INT UNSIGNED,
                // subjects.id is TINYINT UNSIGNED (see database/cpace_database.sql).
                $table->unsignedInteger('faculty_id');
                $table->unsignedTinyInteger('subject_id')->nullable();
                $table->string('title', 150);
                $table->text('instructions')->nullable();
                // draft = only the faculty can see it; published = students can take it;
                // closed = manually shut before/after the deadline.
                $table->string('status', 20)->default('draft');
                $table->string('share_token', 40)->unique();
                $table->dateTime('opens_at')->nullable();
                $table->dateTime('due_at')->nullable();
                $table->unsignedSmallInteger('time_limit_minutes')->nullable();
                $table->boolean('shuffle_questions')->default(false);
                $table->boolean('show_results')->default(true);
                $table->dateTime('published_at')->nullable();
                $table->timestamps();
                $table->index(['status', 'due_at']);
                $table->foreign('faculty_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('faculty_quiz_items')) {
            Schema::create('faculty_quiz_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quiz_id');
                $table->unsignedInteger('source_question_id')->nullable();
                $table->text('question_text');
                $table->string('question_type', 20)->default('mcq');
                // [{"label":"A","text":"...","is_correct":true}, ...]
                $table->json('choices');
                $table->text('explanation')->nullable();
                $table->unsignedSmallInteger('points')->default(1);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->foreign('quiz_id')->references('id')->on('faculty_quizzes')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('faculty_quiz_attempts')) {
            Schema::create('faculty_quiz_attempts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quiz_id');
                $table->unsignedInteger('student_id');
                $table->dateTime('started_at');
                $table->dateTime('submitted_at')->nullable();
                // {"<item_id>": "B", ...}
                $table->json('answers')->nullable();
                $table->unsignedSmallInteger('score')->default(0);
                $table->unsignedSmallInteger('total_points')->default(0);
                $table->decimal('percent', 5, 2)->nullable();
                $table->timestamps();
                $table->unique(['quiz_id', 'student_id']);
                $table->foreign('quiz_id')->references('id')->on('faculty_quizzes')->cascadeOnDelete();
                $table->foreign('student_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_quiz_attempts');
        Schema::dropIfExists('faculty_quiz_items');
        Schema::dropIfExists('faculty_quizzes');
    }
};
