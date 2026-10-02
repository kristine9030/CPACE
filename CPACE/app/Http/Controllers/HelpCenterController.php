<?php

namespace App\Http\Controllers;

use App\Mail\IssueReportedMail;
use App\Models\IssueReport;
use App\Services\SupportNotifier;
use App\Support\HelpCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * Help & Support for every signed-in role: role-specific guides and FAQs, a
 * "Contact support" form, and the user's own request threads.
 *
 * Requests are stored as IssueReport rows so they land in the same Support
 * Inbox as the landing page's "Report an issue".
 */
class HelpCenterController extends Controller
{
    public function __construct(private SupportNotifier $notifier)
    {
    }

    public function index()
    {
        $user = Auth::user();

        $tickets = IssueReport::where('user_id', $user->id)
            ->withCount('replies')
            ->orderByRaw('COALESCE(last_activity_at, created_at) DESC')
            ->limit(20)
            ->get();

        $openInbox = $user->isChair()
            ? IssueReport::where('status', '!=', IssueReport::STATUS_RESOLVED)->count()
            : null;

        return view('help.index', [
            'help'       => HelpCenter::for($user),
            'tickets'    => $tickets,
            'categories' => IssueReport::CATEGORIES,
            'openInbox'  => $openInbox,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject'  => ['required', 'string', 'min:4', 'max:150'],
            'category' => ['required', Rule::in(array_keys(IssueReport::CATEGORIES))],
            'message'  => ['required', 'string', 'min:15', 'max:3000'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ], [
            'message.min' => 'Please describe the problem in a little more detail so we can act on it.',
        ]);

        $user = Auth::user();

        $report = IssueReport::create([
            'user_id'          => $user->id,
            'name'             => $user->name,
            'email'            => $user->email,
            'category'         => $data['category'],
            'subject'          => $data['subject'],
            'message'          => $data['message'],
            'page_url'         => $data['page_url'] ?? null,
            'user_agent'       => substr((string) $request->userAgent(), 0, 500),
            'ip_address'       => $request->ip(),
            'status'           => IssueReport::STATUS_NEW,
            'last_activity_at' => now(),
        ]);

        $this->notifier->notifyStaff(
            $report,
            'New support request: ' . $report->title(),
            $user->name . ' — ' . $report->categoryLabel(),
            $user->id,
        );

        try {
            if ($support = config('mail.from.address')) {
                Mail::to($support)->send(new IssueReportedMail($report));
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return redirect()
            ->route('help.tickets.show', $report)
            ->with('status', 'Request #' . $report->id . ' sent. We’ll reply here and by email.');
    }

    public function show(IssueReport $report)
    {
        $user = Auth::user();
        abort_unless($report->isVisibleTo($user), 404);

        $report->load(['replies.user', 'user']);

        // Opening the thread clears its unread notifications for this viewer.
        DB::table('notifications')
            ->where('recipient_id', $user->id)
            ->where('reference_type', 'issue_report')
            ->where('reference_id', $report->id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return view('help.ticket', [
            'report'   => $report,
            'isChair'  => $user->isChair(),
            'isSuperAdmin' => $user->isSuperAdmin(),
            'isStaff'  => $user->isChair() || $user->isSuperAdmin(),
            'statuses' => IssueReport::STATUSES,
        ]);
    }

    public function reply(Request $request, IssueReport $report)
    {
        $user = Auth::user();
        abort_unless($report->isVisibleTo($user), 404);

        $data = $request->validate(['body' => ['required', 'string', 'min:2', 'max:3000']]);

        $reply = $report->replies()->create(['user_id' => $user->id, 'body' => $data['body']]);

        $fromRequester = $report->user_id !== null && (int) $report->user_id === (int) $user->id;

        // A Chair reply moves a new request to "In progress"; a requester reply
        // on a resolved request reopens it.
        $status = match (true) {
            $fromRequester && $report->isResolved()                  => IssueReport::STATUS_IN_REVIEW,
            ! $fromRequester && $report->status === IssueReport::STATUS_NEW => IssueReport::STATUS_IN_REVIEW,
            default                                                   => $report->status,
        };

        $report->update([
            'status'           => $status,
            'resolved_at'      => $status === IssueReport::STATUS_RESOLVED ? $report->resolved_at : null,
            'last_activity_at' => now(),
        ]);

        if ($fromRequester) {
            $this->notifier->notifyStaff($report, 'Reply on request #' . $report->id . ': ' . $report->title(), $reply->body, $user->id);
        } else {
            $this->notifier->notifyRequesterOfReply($report, $reply, $user->id);
        }

        return redirect()
            ->to(route('help.tickets.show', $report) . '#reply-' . $reply->id)
            ->with('status', 'Reply sent.');
    }
}
