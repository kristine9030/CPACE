<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Personal study notes a student writes and revisits while reviewing.
     * Each note belongs to a student and optionally to a subject/topic, keeps a
     * free-form list of topic tags, and tracks how often / when it was reviewed
     * so the Review Notes page figures (streak, top topics, last reviewed) are
     * all computed from real data.
     */
    public function up(): void
    {
        if (Schema::hasTable('review_notes')) {
            return;
        }

        Schema::create('review_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->unsignedTinyInteger('subject_id')->nullable();
            $table->unsignedSmallInteger('topic_id')->nullable();
            $table->string('title', 180);
            $table->longText('content')->nullable();
            $table->string('tags', 255)->nullable();        // comma-separated topic keywords
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('review_count')->default(0);
            $table->dateTime('last_reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'subject_id']);
            $table->index(['student_id', 'is_favorite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('review_notes');
    }
};
