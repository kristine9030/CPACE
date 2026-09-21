<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Flags a Timed-mode session that was submitted after its computed time
 * limit (question count * TIMED_SECONDS_PER_QUESTION) had already elapsed,
 * server-side. The client-side countdown can be frozen/tampered with, but
 * duration_secs is always computed from started_at on the server, so this
 * flag is derived from that same trustworthy value at submit time.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('quiz_sessions', 'is_late')) {
                $table->boolean('is_late')->default(false)->after('duration_secs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_sessions', 'is_late')) {
                $table->dropColumn('is_late');
            }
        });
    }
};
