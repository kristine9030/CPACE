<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reports submitted through "Report an issue" in the landing page footer.
 *
 * The row is the record of truth: the notification email to the support inbox
 * is sent after the insert and is allowed to fail, so a down mail server or a
 * revoked app password loses the alert but never the report itself.
 *
 * Open to guests, so the controller pairs this with a throttle and a honeypot.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('issue_reports')) {
            return;
        }

        Schema::create('issue_reports', function (Blueprint $table) {
            $table->id();

            // Set when the reporter happened to be signed in; a guest report
            // is still valid, so this stays nullable and survives the account
            // being deleted.
            //
            // unsignedInteger, NOT foreignId(): users.id in this database is
            // int(10) unsigned, while foreignId() would emit a bigint and the
            // constraint would be rejected as incorrectly formed.
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('category', 40)->default('other');
            $table->text('message');

            // Context the reporter should not have to describe by hand.
            $table->string('page_url', 500)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('ip_address', 45)->nullable();

            $table->string('status', 20)->default('new');   // new | in_review | resolved
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // The triage screen reads newest-first within a status.
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_reports');
    }
};
