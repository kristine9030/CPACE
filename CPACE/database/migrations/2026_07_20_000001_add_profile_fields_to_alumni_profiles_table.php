<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The `alumni_profiles` table already exists (seeded via cpace_database.sql
     * with user_id/batch_year/cpa_number/passed_at). This adds the extra,
     * purely additive columns the Alumni Community "about" card needs.
     */
    public function up(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table) {
            $table->string('current_job')->nullable()->after('passed_at');
            $table->string('company')->nullable()->after('current_job');
            $table->string('linkedin_url')->nullable()->after('company');
            $table->text('bio')->nullable()->after('linkedin_url');
        });
    }

    public function down(): void
    {
        Schema::table('alumni_profiles', function (Blueprint $table) {
            $table->dropColumn(['current_job', 'company', 'linkedin_url', 'bio']);
        });
    }
};
