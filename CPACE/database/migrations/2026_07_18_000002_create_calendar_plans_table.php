<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Student-created study blocks for the Spaced Repetition Calendar.
 * A plan is either tied to a topic (so it can be started as a topic exam)
 * or a free-form titled block ("Review formulas", "Mock exam drill", ...).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_plans', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('student_id');
            $table->unsignedSmallInteger('topic_id')->nullable();
            $table->string('title', 120)->nullable();
            $table->date('plan_date');
            $table->unsignedSmallInteger('start_min');          // minutes from midnight
            $table->unsignedSmallInteger('duration_min')->default(60);
            $table->timestamps();

            $table->index(['student_id', 'plan_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_plans');
    }
};
