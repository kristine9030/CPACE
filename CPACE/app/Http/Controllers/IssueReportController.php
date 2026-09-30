<?php

namespace App\Http\Controllers;

use App\Mail\IssueReportedMail;
use App\Models\IssueReport;
use App\Services\SupportNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

/**
 * "Report an issue" from the landing page footer.
 *
 * Open to guests, so it is throttled at the route and carries a honeypot. The
 * row is written first and the support email is sent after, inside its own
 * try/catch: a mail outage should lose the alert, never the report.
 */
class IssueReportController extends Controller
{
    public function store(Request $request, SupportNotifier $notifier)
    {
        // A bot fills every field it finds. A human never sees this one, so
        // anything in it is spam - answered with the success response so the
        // sender learns nothing, but nothing is stored or emailed.
        if (filled($request->input('website'))) {
            return $this->done($request, 'Thanks — your report has been sent.');
        }

        $data = $request->validate([
            'name'     => ['required', 'string', 'max:120'],
            'email'    => ['required', 'email', 'max:160'],
            'category' => ['required', Rule::in(array_keys(IssueReport::CATEGORIES))],
            'message'  => ['required', 'string', 'min:15', 'max:3000'],
            'page_url' => ['nullable', 'string', 'max:500'],
        ], [
            'message.min' => 'Please describe the problem in a little more detail so we can act on it.',
        ]);

        $report = IssueReport::create([
            'user_id'    => Auth::id(),
            'name'       => $data['name'],
            'email'      => $data['email'],
            'category'   => $data['category'],
            'message'    => $data['message'],
            'page_url'   => $data['page_url'] ?? $request->headers->get('referer'),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'ip_address' => $request->ip(),
            'status'     => IssueReport::STATUS_NEW,
            'last_activity_at' => now(),
        ]);

        // Surfaces in the Program Chair's Support Inbox next to Help & Support requests.
        $notifier->notifyChairs(
            $report,
            'New issue report: ' . $report->categoryLabel(),
            $report->name . ' — ' . $report->email,
            Auth::id(),
        );

        $support = config('mail.from.address');

        try {
            if ($support) {
                Mail::to($support)->send(new IssueReportedMail($report));
            }
        } catch (\Throwable $e) {
            // The report is already safe in the table; surface the mail failure
            // to the log and still thank the reporter.
            report($e);
        }

        return $this->done(
            $request,
            'Thanks — your report has been sent. Reference #' . $report->id . '.'
        );
    }

    /** JSON for the footer modal, a flash redirect for a no-JS submit. */
    private function done(Request $request, string $message)
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => $message]);
        }

        return back()->with('report_status', $message);
    }
}
