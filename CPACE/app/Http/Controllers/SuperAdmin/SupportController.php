<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Chair\SupportInboxController;
use App\Models\IssueReport;

/**
 * The Super Admin's view of Help & Support: technical requests (bugs,
 * proctoring, privacy) and anything the Program Chair escalated. It is the
 * Chair's inbox, narrowed; threads are still answered on help.tickets.show.
 */
class SupportController extends SupportInboxController
{
    protected function scope()
    {
        return IssueReport::forSuperAdmin();
    }

    protected function indexRoute(): string
    {
        return 'superadmin.support.index';
    }

    protected function subheading(): string
    {
        return 'Bugs, proctoring and privacy requests, plus anything the Program Chair escalated.';
    }

    protected function authorizeReport(IssueReport $report): void
    {
        abort_unless($report->isForSuperAdmin(), 404);
    }
}
