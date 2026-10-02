<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AiUsageLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('superadmin.dashboard', $this->buildData());
    }

    /** JSON refresh endpoint for the dashboard's auto-updating cards. */
    public function data(Request $request)
    {
        return response()->json($this->buildData());
    }

    private function buildData(): array
    {
        $today = now()->format('Y-m-d');
        $weekAgo = now()->subDays(7);

        $roleCounts = User::select('role_id', DB::raw('COUNT(*) as total'), DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active'))
            ->groupBy('role_id')->get()->keyBy('role_id');

        $roleLabel = fn (int $id) => $roleCounts->get($id)->total ?? 0;

        $userStats = [
            'total'       => User::count(),
            'students'    => $roleLabel(Role::STUDENT),
            'faculty'     => $roleLabel(Role::FACULTY),
            'chairs'      => $roleLabel(Role::ADMIN),
            'alumni'      => $roleLabel(Role::ALUMNI),
            'super_admins' => $roleLabel(Role::SUPER_ADMIN),
            'active'      => User::where('is_active', true)->count(),
            'new_this_week' => User::where('created_at', '>=', $weekAgo)->count(),
        ];

        $aiStats = [
            'today'     => AiUsageLog::whereDate('created_at', $today)->count(),
            'this_week' => AiUsageLog::where('created_at', '>=', $weekAgo)->count(),
            'by_feature' => AiUsageLog::where('created_at', '>=', $weekAgo)
                ->select('feature', DB::raw('COUNT(*) as total'))
                ->groupBy('feature')->orderByDesc('total')->limit(6)->get(),
        ];

        // Samples are [{'route' => ..., 'ms' => ...}, ...] (RecordRequestMetrics
        // tags each one with its route for the Performance page's breakdown) —
        // pull out just the ms values here for the dashboard's own summary.
        $durations = array_map(fn ($s) => (int) (is_array($s) ? $s['ms'] : $s), Cache::get('metrics.durations', []));
        $performance = [
            'requests_today' => (int) Cache::get("metrics.requests.{$today}", 0),
            'errors_today'   => (int) Cache::get("metrics.errors.{$today}", 0),
            'avg_response_ms' => count($durations) > 0 ? (int) round(array_sum($durations) / count($durations)) : null,
            'p95_response_ms' => $this->percentile($durations, 95),
            'sample_size'     => count($durations),
        ];

        $recentActivity = ActivityLog::orderByDesc('created_at')->limit(10)->get();

        return compact('userStats', 'aiStats', 'performance', 'recentActivity');
    }

    private function percentile(array $samples, int $p): ?int
    {
        if (empty($samples)) {
            return null;
        }

        sort($samples);
        $index = (int) ceil($p / 100 * count($samples)) - 1;

        return $samples[max(0, min($index, count($samples) - 1))];
    }
}
