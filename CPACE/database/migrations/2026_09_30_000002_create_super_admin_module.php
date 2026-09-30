<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->insertOrIgnore(['id' => 5, 'name' => 'super_admin']);

        // Overall audit trail: one row per notable action across every
        // portal (login/logout, account provisioning, question/quiz
        // activity, viewing a student's record, etc.), surfaced in the
        // Super Admin "Activity Log" page.
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_name')->nullable();
            $table->string('actor_role', 20)->nullable();
            $table->string('action', 60);
            $table->text('description')->nullable();
            $table->string('subject_type', 60)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('actor_id');
            $table->index('action');
            $table->index('created_at');
        });

        // One row per allowed AI-backed request (Ask AI, tutor chat, review
        // note generation, ...), so the Super Admin dashboard can report on
        // real AI usage instead of just the live per-user rate-limit cache.
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('feature', 60);
            $table->string('provider', 30)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('user_id');
            $table->index('feature');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('activity_logs');
        DB::table('roles')->where('id', 5)->delete();
    }
};
