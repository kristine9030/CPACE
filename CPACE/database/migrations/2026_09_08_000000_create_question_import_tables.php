<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staging area for the "import questions from a file" flow. A faculty member
 * uploads a PDF/Word/Excel/image; it's parsed (rule-based first, AI fallback)
 * into rows here, faculty reviews/edits/approves each one, then commit()
 * copies the approved rows into questions/question_choices. Nothing lands in
 * the real Test Bank until the faculty explicitly approves it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('question_import_batches')) {
            Schema::create('question_import_batches', function (Blueprint $table) {
                $table->id();
                // users.id is INT UNSIGNED in the real schema (see database/cpace_database.sql).
                $table->unsignedInteger('faculty_id');
                $table->unsignedTinyInteger('subject_id')->nullable();
                $table->string('original_filename', 255);
                $table->string('file_type', 20);
                // parsing = extraction/AI still running; ready = items staged for
                // review; committed = faculty saved the approved items; failed = the
                // file couldn't be read at all.
                $table->string('status', 20)->default('parsing');
                $table->string('parse_source', 10)->nullable(); // rule | ai | mixed
                $table->text('error_message')->nullable();
                $table->timestamps();
                $table->foreign('faculty_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('subject_id')->references('id')->on('subjects')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('question_import_items')) {
            Schema::create('question_import_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('batch_id');
                $table->unsignedSmallInteger('topic_id')->nullable();
                $table->text('question_text');
                $table->string('question_type', 20)->default('mcq');
                // [{"label":"A","text":"...","is_correct":true}, ...]
                $table->json('choices')->nullable();
                $table->text('explanation')->nullable();
                $table->string('difficulty', 20)->default('moderate');
                $table->string('source', 10)->default('rule'); // rule | ai
                $table->unsignedTinyInteger('confidence')->default(100); // 0-100, for the review UI's "check this one" flag
                // pending = awaiting the faculty's decision; approved = will be
                // committed; rejected = faculty discarded this row.
                $table->string('status', 20)->default('pending');
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->timestamps();
                $table->foreign('batch_id')->references('id')->on('question_import_batches')->cascadeOnDelete();
                $table->foreign('topic_id')->references('id')->on('topics')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('question_import_items');
        Schema::dropIfExists('question_import_batches');
    }
};
