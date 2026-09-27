<?php

namespace App\Support;

use App\Models\CurriculumVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Which curriculum version's topics a query should see.
 *
 * Old versions' topics stay in the `topics` table (questions, performance
 * records and quiz history point at them), so anything that LISTS or COUNTS
 * topics — or draws questions for a quiz — must be limited to one version:
 *
 *  - restrictForStudent($studentId): what a student studies and draws quiz
 *    questions from — the curriculum covering their enrollment batch.
 *  - restrictToActive(): program-wide views (faculty tools, chair reports,
 *    coverage). Always the published curriculum, never the draft.
 *  - restrictTo($versionId): the Test Bank, where faculty may switch to the
 *    draft to prepare its questions before the chair publishes it.
 *
 * History lookups (performance_records -> topics, a question's own topic) are
 * deliberately NOT restricted, so a student's past work under an older
 * curriculum still resolves.
 *
 * When the versioning tables don't exist (hand-built test schemas that predate
 * this feature) every restriction is a no-op, so the app behaves as before.
 * Results are memoised per request in the container; call flush() after
 * creating, publishing or discarding a version.
 */
class CurriculumScope
{
    private const CACHE_KEY = 'cpace.curriculum_scope';

    /** Session key for the Test Bank's "which curriculum am I editing" switch. */
    public const TEST_BANK_SESSION_KEY = 'test_bank_curriculum';

    public static function activeId(): ?int
    {
        return self::state()['active'];
    }

    public static function draftId(): ?int
    {
        return self::state()['draft'];
    }

    public static function enabled(): bool
    {
        return self::state()['enabled'];
    }

    /**
     * @template T of \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     * @param  T  $query
     * @return T
     */
    public static function restrictToActive($query, string $column = 'topics.curriculum_version_id')
    {
        return self::restrictTo($query, self::activeId(), $column);
    }

    /**
     * @template T of \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     * @param  T  $query
     * @return T
     */
    public static function restrictTo($query, ?int $versionId, string $column = 'topics.curriculum_version_id')
    {
        if (self::enabled() && $versionId !== null) {
            $query->where($column, $versionId);
        }

        return $query;
    }

    /**
     * The curriculum a particular student studies: the one covering their
     * enrollment batch (student_profiles.batch_year), so a batch keeps its
     * curriculum even after a newer one is published for later batches.
     * Falls back to the active curriculum when the student has no batch or
     * no curriculum covers it — never to nothing.
     */
    public static function forStudent(?int $studentId): ?int
    {
        if (! self::enabled() || $studentId === null || ! BatchYear::columnExists()) {
            return self::activeId();
        }

        $key = self::CACHE_KEY . '.student.' . $studentId;
        if (app()->bound($key)) {
            return app($key);
        }

        $batch = DB::table('student_profiles')->where('user_id', $studentId)->value('batch_year');
        $versionId = CurriculumVersion::forBatch($batch)?->id ?? self::activeId();

        app()->instance($key, $versionId);
        self::$studentKeys[] = $key;

        return $versionId;
    }

    /**
     * @template T of \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder
     * @param  T  $query
     * @return T
     */
    public static function restrictForStudent($query, ?int $studentId, string $column = 'topics.curriculum_version_id')
    {
        return self::restrictTo($query, self::forStudent($studentId), $column);
    }

    /**
     * The version the Test Bank is currently showing: the draft when faculty
     * switched to it (and one exists), otherwise the active curriculum.
     */
    public static function testBankVersionId(): ?int
    {
        $draft = self::draftId();
        if ($draft !== null && session(self::TEST_BANK_SESSION_KEY) === 'draft') {
            return $draft;
        }

        return self::activeId();
    }

    public static function testBankShowsDraft(): bool
    {
        return self::draftId() !== null && self::testBankVersionId() === self::draftId();
    }

    public static function flush(): void
    {
        app()->forgetInstance(self::CACHE_KEY);
        foreach (self::$studentKeys as $key) {
            app()->forgetInstance($key);
        }
        self::$studentKeys = [];
    }

    /** @var list<string> per-student cache keys set this request, so flush() can clear them */
    private static array $studentKeys = [];

    /** @return array{enabled: bool, active: ?int, draft: ?int} */
    private static function state(): array
    {
        if (app()->bound(self::CACHE_KEY)) {
            return app(self::CACHE_KEY);
        }

        $state = ['enabled' => false, 'active' => null, 'draft' => null];

        if (Schema::hasTable('curriculum_versions') && Schema::hasColumn('topics', 'curriculum_version_id')) {
            $rows = CurriculumVersion::whereIn('status', [CurriculumVersion::STATUS_ACTIVE, CurriculumVersion::STATUS_DRAFT])
                ->orderByDesc('id')
                ->get(['id', 'status']);

            $state = [
                'enabled' => true,
                'active' => $rows->firstWhere('status', CurriculumVersion::STATUS_ACTIVE)?->id,
                'draft' => $rows->firstWhere('status', CurriculumVersion::STATUS_DRAFT)?->id,
            ];
        }

        app()->instance(self::CACHE_KEY, $state);

        return $state;
    }
}
