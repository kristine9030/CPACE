<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Marks a quiz session's Live Room as user-picked practice difficulty
 * (true) vs. the locked, data-derived Assessment/Ranked room (false,
 * default). Practice-room sessions are excluded from every analytics
 * query (dashboard, reports, performance) so the ranked room's numbers
 * stay objective even though students can tune practice difficulty.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('quiz_sessions', 'is_practice_room')) {
                $table->boolean('is_practice_room')->default(false)->after('session_type');
            }
            if (! Schema::hasColumn('quiz_sessions', 'practice_difficulty')) {
                $table->string('practice_difficulty', 20)->nullable()->after('is_practice_room');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quiz_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('quiz_sessions', 'is_practice_room')) {
                $table->dropColumn('is_practice_room');
            }
            if (Schema::hasColumn('quiz_sessions', 'practice_difficulty')) {
                $table->dropColumn('practice_difficulty');
            }
        });
    }
};
