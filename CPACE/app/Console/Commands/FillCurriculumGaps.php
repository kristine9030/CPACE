<?php

namespace App\Console\Commands;

use App\Services\CurriculumGapFiller;
use Illuminate\Console\Command;

/**
 * Enforces each topic's TOS item count as its faculty's quota: notices and a
 * final warning first, then AI-drafted substitutes (pending review, hidden
 * from students) once the grace period has passed. See CurriculumGapFiller.
 */
class FillCurriculumGaps extends Command
{
    protected $signature = 'curriculum:fill-gaps';

    protected $description = 'Remind faculty about topics short of their TOS item count, and draft AI substitutes after the grace period';

    public function handle(CurriculumGapFiller $filler): int
    {
        $s = $filler->run();

        $this->info("Checked {$s['checked']} topic(s) with a TOS target; {$s['short']} short. "
            . "First notices: {$s['noticed']}, final warnings: {$s['warned']}, AI drafts: {$s['drafted']}"
            . ($s['failed'] ? " ({$s['failed']} could not be drafted, will retry next run)" : '') . '.');

        return self::SUCCESS;
    }
}
