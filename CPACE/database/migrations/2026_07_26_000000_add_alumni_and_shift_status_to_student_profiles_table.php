<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->boolean('is_alumni')->default(false)->after('focus_subjects');
            $table->dateTime('alumni_marked_at')->nullable()->after('is_alumni');
            $table->boolean('is_shifted')->default(false)->after('alumni_marked_at');
            $table->string('shift_reason', 500)->nullable()->after('is_shifted');
            $table->dateTime('shifted_at')->nullable()->after('shift_reason');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['is_alumni', 'alumni_marked_at', 'is_shifted', 'shift_reason', 'shifted_at']);
        });
    }
};
