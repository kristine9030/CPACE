<?php

namespace Tests\Unit;

use App\Support\BatchYear;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class BatchYearTest extends TestCase
{
    public function test_the_school_year_turns_over_in_june(): void
    {
        $this->assertSame('2025-2026', BatchYear::forDate(Carbon::create(2026, 5, 31)));
        $this->assertSame('2026-2027', BatchYear::forDate(Carbon::create(2026, 6, 1)));
        $this->assertSame('2026-2027', BatchYear::forDate(Carbon::create(2026, 12, 31)));
        $this->assertSame('2026-2027', BatchYear::forDate(Carbon::create(2027, 1, 1)));
    }

    public function test_only_consecutive_year_pairs_are_valid(): void
    {
        $this->assertTrue(BatchYear::isValid('2026-2027'));
        $this->assertFalse(BatchYear::isValid('2026-2028'));
        $this->assertFalse(BatchYear::isValid('2026'));
        $this->assertFalse(BatchYear::isValid('26-27'));
        $this->assertFalse(BatchYear::isValid(null));
    }

    public function test_the_previous_batch_is_one_school_year_earlier(): void
    {
        $this->assertSame('2026-2027', BatchYear::previous('2027-2028'));
        $this->assertNull(BatchYear::previous('not a batch'));
    }

    public function test_the_next_batch_is_one_school_year_later(): void
    {
        $this->assertSame('2028-2029', BatchYear::next('2027-2028'));
        $this->assertNull(BatchYear::next('not a batch'));
    }
}
