<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which year levels (and optionally sections) may take a mock exam. The redeem
 * code is shared by everyone who hears it, so it cannot be the only gate.
 * NULL means open to everyone, which keeps exams published before this working.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mock_exams')) {
            return;
        }

        foreach (['audience_years', 'audience_sections'] as $column) {
            if (! Schema::hasColumn('mock_exams', $column)) {
                Schema::table('mock_exams', function (Blueprint $t) use ($column) {
                    $t->json($column)->nullable()->after('version');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['audience_years', 'audience_sections'] as $column) {
            if (Schema::hasColumn('mock_exams', $column)) {
                Schema::table('mock_exams', fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
    }
};
