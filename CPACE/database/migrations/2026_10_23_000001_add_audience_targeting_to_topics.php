<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lets the chair restrict a topic to the year level(s), section(s), and/or
 * semester(s) that actually cover it, so a student isn't quizzed on a topic
 * scheduled for a later term. Null/empty on any column means unrestricted
 * along that dimension, same convention as mock_exams' audience_years/
 * audience_sections.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('topics') || Schema::hasColumn('topics', 'audience_years')) {
            return;
        }

        Schema::table('topics', function (Blueprint $table) {
            $table->json('audience_years')->nullable();
            $table->json('audience_sections')->nullable();
            $table->json('audience_semesters')->nullable();
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('topics') && Schema::hasColumn('topics', 'audience_years')) {
            Schema::table('topics', fn (Blueprint $table) => $table->dropColumn([
                'audience_years', 'audience_sections', 'audience_semesters',
            ]));
        }
    }
};
