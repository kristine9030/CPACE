<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proctoring evidence for a mock exam sitting.
 *
 * Two kinds of evidence, stored separately because they have very different
 * lifetimes:
 *
 *  - mock_exam_proctor_events are tiny behavioural flags (alt-tab, leaving
 *    fullscreen, revoking a permission). They are the durable record and are
 *    never purged.
 *
 *  - mock_exam_proctor_captures are camera/screen JPEG frames. A three-hour
 *    sitting is roughly 8 MB per student, so these are downscaled on the
 *    client and swept by the mock-exam:purge-captures command once an exam has
 *    been closed long enough.
 *
 * Captures are written to the PRIVATE disk and served only through an
 * authorised controller route. They are photographs of students' faces and
 * screens and must never be reachable by guessing a public URL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mock_exam_proctor_events')) {
            Schema::create('mock_exam_proctor_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attempt_id');
                // blur | visibility_hidden | fullscreen_exit | paste_blocked
                // | camera_lost | screen_lost
                $table->string('type', 30);
                $table->dateTime('occurred_at');
                $table->string('meta', 255)->nullable();
                $table->index(['attempt_id', 'occurred_at']);
                $table->foreign('attempt_id')->references('id')->on('mock_exam_attempts')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('mock_exam_proctor_captures')) {
            Schema::create('mock_exam_proctor_captures', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('attempt_id');
                // camera | screen
                $table->string('kind', 10);
                // Path on the private disk, relative to its root.
                $table->string('path', 255);
                $table->dateTime('captured_at');
                // interval | blur | fullscreen_exit | start - why this frame
                // was taken, so the monitor can highlight event-triggered ones.
                $table->string('reason', 30)->default('interval');
                $table->index(['attempt_id', 'captured_at']);
                $table->foreign('attempt_id')->references('id')->on('mock_exam_attempts')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mock_exam_proctor_captures');
        Schema::dropIfExists('mock_exam_proctor_events');
    }
};
