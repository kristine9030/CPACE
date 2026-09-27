<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Curriculum versions: the chair can start a new curriculum (a blank DRAFT
 * topic tree), build or import it while students keep studying the current
 * one, then publish it. The old version is archived, not deleted — its topics
 * stay in `topics` because questions, performance records and quiz history
 * all point at them — and remains viewable as read-only history.
 *
 * Every existing topic is backfilled into one initial ACTIVE version so the
 * app behaves exactly as before until the chair starts a new curriculum.
 *
 * Also adds student_profiles.batch_year ("2026-2027"), the enrollment batch a
 * curriculum's effective range is expressed in.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('curriculum_versions')) {
            Schema::create('curriculum_versions', function (Blueprint $t) {
                $t->increments('id');
                $t->string('label', 80);
                $t->string('effective_from_batch', 9)->nullable();
                $t->string('effective_to_batch', 9)->nullable();
                $t->string('status', 10)->default('draft'); // draft | active | archived
                $t->unsignedInteger('created_by')->nullable();
                $t->dateTime('published_at')->nullable();
                $t->dateTime('created_at')->nullable();
                $t->index('status');
            });
        }

        if (! Schema::hasTable('curriculum_audits')) {
            Schema::create('curriculum_audits', function (Blueprint $t) {
                $t->increments('id');
                $t->unsignedInteger('curriculum_version_id');
                $t->unsignedTinyInteger('subject_id')->nullable();
                $t->unsignedInteger('user_id')->nullable();
                $t->string('action', 30);
                $t->text('details')->nullable();
                $t->dateTime('created_at')->nullable();
                $t->index('curriculum_version_id');
            });
        }

        if (Schema::hasTable('topics') && ! Schema::hasColumn('topics', 'curriculum_version_id')) {
            Schema::table('topics', function (Blueprint $t) {
                $t->unsignedInteger('curriculum_version_id')->nullable()->after('subject_id');
                $t->index('curriculum_version_id');
            });
        }

        if (Schema::hasTable('topics') && DB::table('topics')->whereNull('curriculum_version_id')->exists()) {
            $versionId = DB::table('curriculum_versions')->where('status', 'active')->value('id')
                ?? DB::table('curriculum_versions')->insertGetId([
                    'label' => 'CPALE Curriculum (current)',
                    'status' => 'active',
                    'published_at' => now(),
                    'created_at' => now(),
                ]);

            DB::table('topics')->whereNull('curriculum_version_id')->update(['curriculum_version_id' => $versionId]);
        }

        if (Schema::hasTable('student_profiles') && ! Schema::hasColumn('student_profiles', 'batch_year')) {
            Schema::table('student_profiles', function (Blueprint $t) {
                $t->string('batch_year', 9)->nullable()->after('section');
            });
        }

        // Existing students: derive the batch from when their account was
        // created (the enrollment date), same rule as new enrollments.
        if (Schema::hasColumn('student_profiles', 'batch_year')) {
            DB::table('student_profiles')
                ->join('users', 'users.id', '=', 'student_profiles.user_id')
                ->whereNull('student_profiles.batch_year')
                ->whereNotNull('users.created_at')
                ->select('student_profiles.user_id', 'users.created_at')
                ->orderBy('student_profiles.user_id')
                ->get()
                ->each(fn ($row) => DB::table('student_profiles')
                    ->where('user_id', $row->user_id)
                    ->update(['batch_year' => \App\Support\BatchYear::forDate(\Illuminate\Support\Carbon::parse($row->created_at))]));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('student_profiles', 'batch_year')) {
            Schema::table('student_profiles', fn (Blueprint $t) => $t->dropColumn('batch_year'));
        }
        if (Schema::hasColumn('topics', 'curriculum_version_id')) {
            Schema::table('topics', function (Blueprint $t) {
                $t->dropIndex(['curriculum_version_id']);
                $t->dropColumn('curriculum_version_id');
            });
        }
        Schema::dropIfExists('curriculum_audits');
        Schema::dropIfExists('curriculum_versions');
    }
};
