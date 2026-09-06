<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sections')) {
            return;
        }

        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed the formal list from the section values already in use so
        // existing enrollment data isn't orphaned by this change.
        if (Schema::hasTable('student_profiles') && Schema::hasColumn('student_profiles', 'section')) {
            $existing = DB::table('student_profiles')
                ->whereNotNull('section')
                ->where('section', '!=', '')
                ->distinct()
                ->pluck('section')
                ->map(fn ($name) => trim($name))
                ->filter()
                ->unique()
                ->values();

            foreach ($existing as $name) {
                DB::table('sections')->insertOrIgnore([
                    'name' => $name,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
