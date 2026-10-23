<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Scenarios (case problems): one shared situation with any number of questions
 * under it. The Test Bank keeps the live scenario; quizzes and mock exams get a
 * snapshot copy, like they do for questions, so editing the bank later never
 * changes something students have already sat.
 *
 * Every step is guarded because this project's real schema is dump-driven and
 * can already hold some of these columns.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('question_scenarios')) {
            Schema::create('question_scenarios', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('topic_id')->index();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('title', 150)->nullable();
                $table->mediumText('body');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('questions')) {
            Schema::table('questions', function (Blueprint $table) {
                if (! Schema::hasColumn('questions', 'scenario_id')) {
                    $table->unsignedBigInteger('scenario_id')->nullable()->index();
                }
                if (! Schema::hasColumn('questions', 'scenario_position')) {
                    $table->unsignedSmallInteger('scenario_position')->default(0);
                }
            });
        }

        foreach (['faculty_quiz_items', 'mock_exam_items'] as $items) {
            if (! Schema::hasTable($items)) {
                continue;
            }
            Schema::table($items, function (Blueprint $table) use ($items) {
                // Items sharing a group are one scenario; the text is copied onto each of them.
                if (! Schema::hasColumn($items, 'scenario_group')) {
                    $table->string('scenario_group', 40)->nullable();
                }
                if (! Schema::hasColumn($items, 'scenario_text')) {
                    $table->mediumText('scenario_text')->nullable();
                }
                if (! Schema::hasColumn($items, 'scenario_id')) {
                    $table->unsignedBigInteger('scenario_id')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['faculty_quiz_items', 'mock_exam_items'] as $items) {
            if (Schema::hasTable($items)) {
                Schema::table($items, function (Blueprint $table) use ($items) {
                    foreach (['scenario_group', 'scenario_text', 'scenario_id'] as $col) {
                        if (Schema::hasColumn($items, $col)) {
                            $table->dropColumn($col);
                        }
                    }
                });
            }
        }
        if (Schema::hasTable('questions')) {
            if (Schema::hasColumn('questions', 'scenario_id') && Schema::hasIndex('questions', 'questions_scenario_id_index')) {
                Schema::table('questions', fn (Blueprint $table) => $table->dropIndex('questions_scenario_id_index'));
            }
            Schema::table('questions', function (Blueprint $table) {
                foreach (['scenario_id', 'scenario_position'] as $col) {
                    if (Schema::hasColumn('questions', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
        Schema::dropIfExists('question_scenarios');
    }
};
