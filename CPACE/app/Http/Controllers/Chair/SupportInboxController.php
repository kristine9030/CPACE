<?php

namespace App\Http\Controllers\Chair;

use App\Http\Controllers\Controller;
use App\Models\IssueReport;
use App\Services\SupportNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * The Program Chair's triage view of every Help & Support request, including
 * guest reports from the landing page. Threads themselves are read and
 * answered on the shared help.tickets.show page.
 */
class SupportInboxController extends Controller
{
    public function __construct(private SupportNotifier $notifier)
    {
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $search = trim((string) $request->query('q', ''));

        $counts = IssueReport::selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts = [
            'open'                         => ($counts[IssueReport::STATUS_NEW] ?? 0) + ($counts[IssueReport::STATUS_IN_REVIEW] ?? 0),
            IssueReport::STATUS_NEW        => $counts[IssueReport::STATUS_NEW] ?? 0,
            IssueReport::STATUS_IN_REVIEW  => $counts[IssueReport::STATUS_IN_REVIEW] ?? 0,
            IssueReport::STATUS_RESOLVED   => $counts[IssueReport::STATUS_RESOLVED] ?? 0,
            'all'                          => $counts->sum(),
        ];

        $tickets = IssueReport::with('user')
            ->withCount('replies')
            ->when($status === 'open', fn ($q) => $q->where('status', '!=', IssueReport::STATUS_RESOLVED))
            ->when(array_key_exists($status, IssueReport::STATUSES), fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $like = '%' . $search . '%';
                $id = ltrim($search, '#');
                $q->where(fn ($w) => $w->where('subject', 'like', $like)
                    ->orWhere('message', 'like', $like)
                    ->orWhere('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->when(ctype_digit($id), fn ($w) => $w->orWhere('id', (int) $id)));
            })
            ->orderByRaw('COALESCE(last_activity_at, created_at) DESC')
            ->paginate(20)
            ->withQueryString();

        return view('chair.support.index', compact('tickets', 'counts', 'status', 'search'));
    }

    public function updateStatus(Request $request, IssueReport $report)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(IssueReport::STATUSES))]]);

        if ($data['status'] === $report->status) {
            return back();
        }

        $report->update([
            'status'           => $data['status'],
            'resolved_at'      => $data['status'] === IssueReport::STATUS_RESOLVED ? now() : null,
            'last_activity_at' => now(),
        ]);

        if ($report->isResolved()) {
            $this->notifier->notifyRequesterResolved($report, Auth::id());
        }

        return back()->with('status', 'Request #' . $report->id . ' marked as ' . strtolower($report->statusLabel()) . '.');
    }
}
