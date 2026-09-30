<?php

namespace App\Services;

use App\Mail\SupportReplyMail;
use App\Models\IssueReport;
use App\Models\IssueReportReply;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

/**
 * In-app notifications and emails for the Help & Support module.
 *
 * Every send is best-effort: the ticket and reply rows are already saved by
 * the time this runs, so a mail outage loses the alert, never the request.
 */
class SupportNotifier
{
    /** Tell every active Program Chair about a new request or a requester reply. */
    public function notifyChairs(IssueReport $report, string $title, string $message, ?int $senderId): void
    {
        $chairIds = User::where('role_id', Role::ADMIN)
            ->where('is_active', true)
            ->when($senderId, fn ($q) => $q->where('id', '!=', $senderId))
            ->pluck('id');

        $this->insert($chairIds->all(), $report, $title, $message, $senderId);
    }

    /** Tell the requester the Chair replied (in-app when they have an account, always by email). */
    public function notifyRequesterOfReply(IssueReport $report, IssueReportReply $reply, int $senderId): void
    {
        if ($report->user_id) {
            $this->insert([$report->user_id], $report, 'Support replied: ' . $report->title(), $reply->body, $senderId);
        }

        $this->mail($report, $reply);
    }

    public function notifyRequesterResolved(IssueReport $report, int $senderId): void
    {
        if ($report->user_id) {
            $this->insert(
                [$report->user_id], $report,
                'Request resolved: ' . $report->title(),
                'Your support request #' . $report->id . ' was marked as resolved. Reply in the request if you still need help.',
                $senderId,
            );
        }

        $this->mail($report, null);
    }

    private function insert(array $recipientIds, IssueReport $report, string $title, string $message, ?int $senderId): void
    {
        if ($recipientIds === []) {
            return;
        }

        $now = now();
        $link = route('help.tickets.show', $report, false);

        try {
            DB::table('notifications')->insert(array_map(fn ($id) => [
                'recipient_id'   => $id,
                'sender_id'      => $senderId,
                'type'           => 'normal',
                'title'          => Str::limit($title, 150, ''),
                'message'        => Str::limit($message, 500),
                'link'           => $link,
                'is_read'        => false,
                'reference_type' => 'issue_report',
                'reference_id'   => $report->id,
                'created_at'     => $now,
                'updated_at'     => $now,
            ], $recipientIds));
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function mail(IssueReport $report, ?IssueReportReply $reply): void
    {
        try {
            Mail::to($report->email, $report->name)->send(new SupportReplyMail($report, $reply));
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
