<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A third, optional axis on top of audience_years/audience_sections: which
 * enrollment batch ("2026-2027") may take a mock exam. Needed because year
 * level + section alone can't tell apart two batches sharing a section name
 * (an irregular student sitting in a section from a different intake).
 * NULL/empty means "every batch of the chosen years", same as sections today.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mock_exams') || Schema::hasColumn('mock_exams', 'audience_batch_years')) {
            return;
        }

        Schema::table('mock_exams', function (Blueprint $t) {
            $t->json('audience_batch_years')->nullable()->after('audience_sections');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('mock_exams', 'audience_batch_years')) {
            Schema::table('mock_exams', fn (Blueprint $t) => $t->dropColumn('audience_batch_years'));
        }
    }
};
