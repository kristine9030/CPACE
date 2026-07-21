<?php

use App\Models\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Create the single default group chat and drop every existing student
     * and alumnus into it. New accounts auto-join via User::booted() (see
     * app/Models/User.php) — this migration only backfills accounts that
     * already existed before the messaging feature shipped.
     */
    public function up(): void
    {
        $conversationId = DB::table('conversations')->insertGetId([
            'type' => 'group',
            'name' => 'CPACE Community',
            'is_default_group' => true,
            'created_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userIds = DB::table('users')
            ->whereIn('role_id', [Role::STUDENT, Role::ALUMNI])
            ->pluck('id');

        $now = now();
        $rows = $userIds->map(fn ($id) => [
            'conversation_id' => $conversationId,
            'user_id' => $id,
            'joined_at' => $now,
            'last_read_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        if (! empty($rows)) {
            DB::table('conversation_participants')->insert($rows);
        }
    }

    public function down(): void
    {
        $id = DB::table('conversations')->where('is_default_group', true)->value('id');
        if ($id) {
            DB::table('conversations')->where('id', $id)->delete();
        }
    }
};
