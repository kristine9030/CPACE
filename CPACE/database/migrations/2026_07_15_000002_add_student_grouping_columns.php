<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('student_profiles', 'student_number')) {
            Schema::table('student_profiles', fn (Blueprint $table) => $table->string('student_number', 30)->nullable()->unique());
        }
        if (! Schema::hasColumn('student_profiles', 'year_level')) {
            Schema::table('student_profiles', fn (Blueprint $table) => $table->unsignedTinyInteger('year_level')->nullable());
        }
        if (! Schema::hasColumn('student_profiles', 'section')) {
            Schema::table('student_profiles', fn (Blueprint $table) => $table->string('section', 30)->nullable());
        }
    }

    public function down(): void
    {
        // Compatibility columns are preserved to avoid discarding enrollment data.
    }
};
