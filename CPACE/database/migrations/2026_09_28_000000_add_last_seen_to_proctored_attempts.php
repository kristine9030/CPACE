<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a monitored page last reported in (heartbeat, flag or frame). A long
 * silence is written to the flag record as a connection_gap, and the monitor
 * shows "No signal" for anyone who has gone quiet mid-sitting.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['mock_exam_attempts', 'faculty_quiz_attempts'] as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'last_seen_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dateTime('last_seen_at')->nullable()->after('submitted_at');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['mock_exam_attempts', 'faculty_quiz_attempts'] as $table) {
            if (Schema::hasColumn($table, 'last_seen_at')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('last_seen_at'));
            }
        }
    }
};
