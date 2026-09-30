<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The student Mock Exams page explains, once, that subject folders stay muted
 * until one of their exams opens. When the student acknowledges it, this is
 * stamped and the notice never shows again — on any device, since it lives on
 * the account rather than in the browser.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'mock_exam_notice_seen_at')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dateTime('mock_exam_notice_seen_at')->nullable()->after('setup_completed_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'mock_exam_notice_seen_at')) {
            Schema::table('users', function (Blueprint $t) {
                $t->dropColumn('mock_exam_notice_seen_at');
            });
        }
    }
};
