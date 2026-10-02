<?php

namespace App\Support;

use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Request as RequestFacade;

/**
 * Single choke point for the overall audit trail shown on the Super Admin
 * "Activity Log" page (mirrors CurriculumAuditor/MockExamAuditor). Call this
 * from any controller/listener the moment a notable action happens — it
 * never throws, since a logging failure must not break the action itself.
 */
class Auditor
{
    private const ROLE_LABELS = [
        Role::SUPER_ADMIN => 'super_admin',
        Role::ADMIN => 'admin',
        Role::FACULTY => 'faculty',
        Role::STUDENT => 'student',
        Role::ALUMNI => 'alumni',
    ];

    public static function log(?User $actor, string $action, ?string $description = null, ?string $subjectType = null, ?int $subjectId = null): void
    {
        try {
            ActivityLog::create([
                'actor_id' => $actor?->id,
                'actor_name' => $actor?->name,
                // Read straight off role_id rather than the `role` relation
                // (User::roleName()) — this must never depend on the roles
                // table being present/seeded to work.
                'actor_role' => $actor ? (self::ROLE_LABELS[$actor->role_id] ?? null) : null,
                'action' => $action,
                'description' => $description,
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'ip_address' => RequestFacade::ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
