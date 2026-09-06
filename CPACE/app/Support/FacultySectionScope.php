<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Query\Builder;

/**
 * Restricts a query already joined to student_profiles to the sections a
 * faculty member is assigned to, per subject. A faculty with no section
 * rows for a given subject is unrestricted for it (sees the whole subject) -
 * this preserves every faculty_subjects assignment made before this feature
 * existed.
 */
class FacultySectionScope
{
    /**
     * Add an OR-grouped subject/section restriction covering every subject
     * id in scope, so a faculty member restricted on one assigned subject
     * but not another still sees the right mix.
     */
    public static function apply(Builder $query, User $faculty, array $subjectIds, string $subjectColumn, string $sectionColumn): Builder
    {
        return $query->where(function (Builder $outer) use ($faculty, $subjectIds, $subjectColumn, $sectionColumn) {
            foreach ($subjectIds as $subjectId) {
                $names = $faculty->sectionNamesForSubject((int) $subjectId);

                $outer->orWhere(function (Builder $inner) use ($subjectId, $names, $subjectColumn, $sectionColumn) {
                    $inner->where($subjectColumn, $subjectId);
                    if ($names !== null) {
                        $inner->whereIn($sectionColumn, $names);
                    }
                });
            }
        });
    }
}
