<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Program Chair can hand a support request up to the Super Admin.
 * Technical categories (bug, proctoring, privacy) reach the Super Admin
 * without this; escalated_at is for everything else the Chair can't settle.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('issue_reports', 'escalated_at')) {
            return;
        }

        Schema::table('issue_reports', function (Blueprint $table) {
            $table->timestamp('escalated_at')->nullable()->after('resolved_at');
            // unsignedInteger, NOT foreignId(): users.id is int(10) unsigned.
            $table->unsignedInteger('escalated_by')->nullable()->after('escalated_at');
        });
    }

    public function down(): void
    {
        Schema::table('issue_reports', function (Blueprint $table) {
            $table->dropColumn(['escalated_at', 'escalated_by']);
        });
    }
};
