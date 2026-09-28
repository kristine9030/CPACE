<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * AI substitute questions: when a topic stays short of its TOS item count past
 * the grace period, the curriculum:fill-gaps command drafts the missing items
 * with AI. Those land inactive (never in a student quiz) with review_status
 * "pending" until the assigned faculty or the chair approves them.
 *
 * questions.created_by becomes nullable: an AI question has no faculty author,
 * so it's never counted as anyone's contribution.
 *
 * topics.gap_flagged_at / gap_warned_at drive the reminder cadence (first
 * notice, final warning) and reset once the topic is back at quota.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('questions', 'source')) {
            Schema::table('questions', function (Blueprint $t) {
                $t->string('source', 20)->default('faculty')->after('created_by'); // faculty | ai_substitute
                $t->string('review_status', 10)->nullable()->after('is_active');   // null | pending | approved | rejected
                $t->unsignedInteger('reviewed_by')->nullable()->after('review_status');
                $t->dateTime('reviewed_at')->nullable()->after('reviewed_by');
                $t->index(['source', 'review_status']);
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE questions MODIFY created_by INT UNSIGNED NULL');
        }

        if (! Schema::hasColumn('topics', 'gap_flagged_at')) {
            Schema::table('topics', function (Blueprint $t) {
                $t->dateTime('gap_flagged_at')->nullable()->after('tos_items');
                $t->dateTime('gap_warned_at')->nullable()->after('gap_flagged_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('topics', 'gap_flagged_at')) {
            Schema::table('topics', fn (Blueprint $t) => $t->dropColumn(['gap_flagged_at', 'gap_warned_at']));
        }
        if (Schema::hasColumn('questions', 'source')) {
            Schema::table('questions', function (Blueprint $t) {
                $t->dropIndex(['source', 'review_status']);
                $t->dropColumn(['source', 'review_status', 'reviewed_by', 'reviewed_at']);
            });
        }
    }
};
