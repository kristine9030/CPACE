<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Gate first-login: NULL means the student still has to run Account Setup.
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'setup_completed_at')) {
                $table->timestamp('setup_completed_at')->nullable()->after('last_login_at');
            }
        });

        // Study-plan personalization captured during onboarding.
        Schema::table('student_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('student_profiles', 'mobile')) {
                $table->string('mobile', 30)->nullable();
            }
            if (! Schema::hasColumn('student_profiles', 'study_days')) {
                $table->string('study_days')->nullable();          // e.g. "Mon,Tue,Wed"
            }
            if (! Schema::hasColumn('student_profiles', 'study_time')) {
                $table->string('study_time', 30)->nullable();       // Morning / Afternoon / Evening / Late night
            }
            if (! Schema::hasColumn('student_profiles', 'study_intensity')) {
                $table->string('study_intensity', 20)->nullable();  // Light / Steady / Intense
            }
            if (! Schema::hasColumn('student_profiles', 'focus_subjects')) {
                $table->string('focus_subjects')->nullable();       // e.g. "FAR,AUD,TAX"
            }
        });

        // Everyone who already exists has a real password and shouldn't be
        // pushed into onboarding — mark them complete.
        DB::table('users')->whereNull('setup_completed_at')->update([
            'setup_completed_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'setup_completed_at')) {
                $table->dropColumn('setup_completed_at');
            }
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            foreach (['mobile', 'study_days', 'study_time', 'study_intensity', 'focus_subjects'] as $col) {
                if (Schema::hasColumn('student_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
