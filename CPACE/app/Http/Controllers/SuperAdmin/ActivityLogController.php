<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response as ResponseFacade;

class ActivityLogController extends Controller
{
    private const PER_PAGE = 30;

    public function index(Request $request)
    {
        $logs = $this->filtered($request)->paginate(self::PER_PAGE)->withQueryString();

        $actions = ActivityLog::select('action')->distinct()->orderBy('action')->pluck('action');

        return view('superadmin.activity-log', [
            'logs' => $logs,
            'actions' => $actions,
            'filters' => $request->only('search', 'action', 'from', 'to'),
        ]);
    }

    public function export(Request $request)
    {
        $rows = $this->filtered($request)->limit(5000)->get();

        $filename = 'activity-log-' . now()->format('Y-m-d_His') . '.csv';

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date/Time', 'Actor', 'Role', 'Action', 'Description', 'IP Address']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    optional($row->created_at)->format('Y-m-d H:i:s'),
                    $row->actor_name ?? 'System',
                    $row->actor_role ?? '—',
                    $row->action,
                    $row->description,
                    $row->ip_address,
                ]);
            }

            fclose($handle);
        };

        return ResponseFacade::stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function filtered(Request $request)
    {
        $query = ActivityLog::query()->orderByDesc('created_at');

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }
        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }
}
