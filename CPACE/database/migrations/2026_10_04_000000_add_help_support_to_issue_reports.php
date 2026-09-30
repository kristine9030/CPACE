<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Turns "Report an issue" into the Help & Support module.
 *
 * Signed-in users now file requests from /help, which reuse issue_reports as
 * the ticket row (guest reports from the landing page keep working unchanged).
 * Each ticket gets a subject line and a reply thread between the requester and
 * the Program Chair.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('issue_reports', 'subject')) {
            Schema::table('issue_reports', function (Blueprint $table) {
                // Null on landing-page reports, which only ever had a category.
                $table->string('subject', 150)->nullable()->after('category');
                // Bumped on every reply so the inbox can sort by "latest activity".
                $table->timestamp('last_activity_at')->nullable()->after('resolved_at');
            });
        }

        if (Schema::hasTable('issue_report_replies')) {
            return;
        }

        Schema::create('issue_report_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_report_id')->constrained('issue_reports')->cascadeOnDelete();

            // unsignedInteger, NOT foreignId(): users.id is int(10) unsigned.
            // Nullable so the thread survives the author's account being deleted.
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->text('body');
            $table->timestamps();

            $table->index(['issue_report_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_report_replies');

        if (Schema::hasColumn('issue_reports', 'subject')) {
            Schema::table('issue_reports', function (Blueprint $table) {
                $table->dropColumn(['subject', 'last_activity_at']);
            });
        }
    }
};
