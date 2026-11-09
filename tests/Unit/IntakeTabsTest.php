<?php

namespace Tests\Unit;

use App\Support\IntakeTabs;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class IntakeTabsTest extends TestCase
{
    private function semesters(array $names): \Illuminate\Support\Collection
    {
        return collect($names)->map(fn ($name, $i) => (object) ['id' => $i + 1, 'name' => $name])->values();
    }

    public function test_it_reads_the_month_from_the_semester_name(): void
    {
        $this->assertSame('2027-01-01', IntakeTabs::dateOf('2027 January')->toDateString());
        $this->assertSame('2011-09-01', IntakeTabs::dateOf('2011 Sept HND')->toDateString());
        $this->assertSame('2011-02-01', IntakeTabs::dateOf('2011 feb HND')->toDateString());
        $this->assertSame('2011-04-01', IntakeTabs::dateOf('2011 April  HND')->toDateString());
        $this->assertSame('2010-02-01', IntakeTabs::dateOf('2010 Feb PGD OLD FEE')->toDateString());
        $this->assertNull(IntakeTabs::dateOf('test'));
        $this->assertNull(IntakeTabs::dateOf(null));
    }

    public function test_it_picks_the_two_semesters_nearest_to_today_newest_first(): void
    {
        $picked = IntakeTabs::pick(
            $this->semesters(['2026 May', '2027 May', '2026 September', 'test', '2027 January', '2026 January']),
            Carbon::create(2026, 10, 22)
        );

        $this->assertSame(['2027 January', '2026 September'], $picked->pluck('name')->all());
    }

    public function test_a_tie_between_past_and_future_goes_to_the_future_semester(): void
    {
        $picked = IntakeTabs::pick(
            $this->semesters(['2026 September', '2027 January', '2026 July']),
            Carbon::create(2026, 11, 3),
            1
        );

        $this->assertSame(['2027 January'], $picked->pluck('name')->all());
    }

    public function test_it_copes_with_fewer_dated_semesters_than_asked_for(): void
    {
        $picked = IntakeTabs::pick($this->semesters(['test', '2026 May']), Carbon::create(2026, 10, 22));

        $this->assertSame(['2026 May'], $picked->pluck('name')->all());
        $this->assertTrue(IntakeTabs::pick(collect(), Carbon::create(2026, 10, 22))->isEmpty());
    }
}
