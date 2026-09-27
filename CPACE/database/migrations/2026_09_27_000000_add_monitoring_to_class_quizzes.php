<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optional proctoring for class quizzes, mirroring the mock exam's:
 * faculty switch monitoring on per quiz, and a monitored sitting records
 * behavioural flags and camera/screen frames.
 *
 * Kept in their own tables (rather than reusing mock_exam_proctor_*) because
 * those are foreign-keyed to mock_exam_attempts. The retention rules and the
 * flag weights are shared in code.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('faculty_quizzes', 'monitor_enabled')) {
            Schema::table('faculty_quizzes', function (Blueprint $table) {
                $table->boolean('monitor_enabled')->default(false)->after('show_results');
            });
        }

        if (! Schema::hasColumn('faculty_quiz_attempts', 'flag_count')) {
            Schema::table('faculty_quiz_attempts', function (Blueprint $table) {
                $table->unsignedInteger('flag_count')->default(0)->after('percent');
            });
        }

        if (! Schema::hasTable('quiz_proctor_events')) {
            Schema::create('quiz_proctor_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attempt_id');
                $table->string('type', 30);
                $table->dateTime('occurred_at');
                $table->string('meta', 255)->nullable();
                $table->index(['attempt_id', 'occurred_at']);
                $table->foreign('attempt_id')->references('id')->on('faculty_quiz_attempts')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('quiz_proctor_captures')) {
            Schema::create('quiz_proctor_captures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attempt_id');
                $table->string('kind', 10);
                $table->string('path', 255);
                $table->dateTime('captured_at');
                $table->string('reason', 30)->default('interval');
                $table->index(['attempt_id', 'captured_at']);
                $table->foreign('attempt_id')->references('id')->on('faculty_quiz_attempts')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_proctor_captures');
        Schema::dropIfExists('quiz_proctor_events');

        if (Schema::hasColumn('faculty_quiz_attempts', 'flag_count')) {
            Schema::table('faculty_quiz_attempts', fn (Blueprint $t) => $t->dropColumn('flag_count'));
        }
        if (Schema::hasColumn('faculty_quizzes', 'monitor_enabled')) {
            Schema::table('faculty_quizzes', fn (Blueprint $t) => $t->dropColumn('monitor_enabled'));
        }
    }
};
