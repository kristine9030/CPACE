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

        $counts = $this->scope()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts = [
            'open'                         => ($counts[IssueReport::STATUS_NEW] ?? 0) + ($counts[IssueReport::STATUS_IN_REVIEW] ?? 0),
            IssueReport::STATUS_NEW        => $counts[IssueReport::STATUS_NEW] ?? 0,
            IssueReport::STATUS_IN_REVIEW  => $counts[IssueReport::STATUS_IN_REVIEW] ?? 0,
            IssueReport::STATUS_RESOLVED   => $counts[IssueReport::STATUS_RESOLVED] ?? 0,
            'all'                          => $counts->sum(),
        ];

        $tickets = $this->scope()->with('user')
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

        return view('chair.support.index', [
            'tickets' => $tickets, 'counts' => $counts, 'status' => $status, 'search' => $search,
            'indexRoute' => $this->indexRoute(),
            'subheading' => $this->subheading(),
        ]);
    }

    /** Which requests this inbox lists. The Super Admin's inbox narrows it. */
    protected function scope()
    {
        return IssueReport::query();
    }

    protected function indexRoute(): string
    {
        return 'chair.support.index';
    }

    protected function subheading(): string
    {
        return 'Requests from Help & Support and the website’s “Report an issue”.';
    }

    protected function authorizeReport(IssueReport $report): void
    {
    }

    /** Hand a request up to the Super Admin (technical ones already reach them). */
    public function escalate(IssueReport $report)
    {
        if ($report->isForSuperAdmin()) {
            return back()->with('status', 'Request #' . $report->id . ' is already with the Super Admin.');
        }

        $report->update(['escalated_at' => now(), 'escalated_by' => Auth::id(), 'last_activity_at' => now()]);

        $this->notifier->notifySuperAdmins(
            $report,
            'Escalated request #' . $report->id . ': ' . $report->title(),
            Auth::user()->name . ' escalated this — ' . $report->categoryLabel(),
            Auth::id(),
        );

        return back()->with('status', 'Request #' . $report->id . ' escalated to the Super Admin.');
    }

    public function updateStatus(Request $request, IssueReport $report)
    {
        $this->authorizeReport($report);

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
