<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restricts a faculty's subject assignment to specific sections. A faculty
 * with a row in faculty_subjects but no rows here for that subject remains
 * unrestricted (sees the whole subject) - this table only narrows, it never
 * grants access on its own.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('faculty_subject_sections')) {
            return;
        }

        Schema::create('faculty_subject_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at')->useCurrent();
            $table->timestamps();
            $table->unique(['faculty_id', 'subject_id', 'section_id'], 'fss_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faculty_subject_sections');
    }
};
