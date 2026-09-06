<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The faculty_subjects pivot has existed in production since before this
 * project tracked schema with migrations (see database/cpace_database.sql
 * and database/program_chair_setup.sql) - this migration only exists so
 * local/test environments can build the schema from scratch. It is a no-op
 * wherever the table is already present.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('faculty_subjects')) {
            return;
        }

        Schema::create('faculty_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->useCurrent();
            $table->unique(['faculty_id', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_subjects');
    }
};
