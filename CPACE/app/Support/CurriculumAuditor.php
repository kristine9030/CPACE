<?php

namespace App\Support;

use App\Models\CurriculumAudit;
use App\Models\CurriculumVersion;
use App\Models\User;

/**
 * Single choke point for a curriculum version's history, so every path that
 * changes a curriculum logs it the same way (mirrors MockExamAuditor).
 */
class CurriculumAuditor
{
    public static function record(CurriculumVersion $version, ?User $user, string $action, ?string $details = null, ?int $subjectId = null): CurriculumAudit
    {
        return CurriculumAudit::create([
            'curriculum_version_id' => $version->id,
            'subject_id' => $subjectId,
            'user_id' => $user?->id,
            'action' => $action,
            'details' => $details,
            'created_at' => now(),
        ]);
    }
}
