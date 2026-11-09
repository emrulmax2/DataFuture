<?php

namespace Tests\Feature;

use App\Jobs\SyncStudentClassCalendar;
use App\Jobs\UserMailerJob;
use App\Models\Student;
use App\Services\ClassRoutine\ClassCalendar;
use App\Services\ClassRoutine\ClassCalendarSync;
use App\Services\ClassRoutine\ClassRoutineSender;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The student's class dates in their Google Calendar (user, 28 Oct 2026), against a fake
 * calendar. Checked on LCC20260894 (SEP26-HM-C, September 2026) as the dev database holds them.
 */
class ClassCalendarSyncTest extends TestCase
{
    use DatabaseTransactions;

    private Student $student;
    private object $calendar;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Http::fake();
        $student = Student::where('registration_no', 'LCC20260894')->first();
        if (!$student) {
            $this->markTestSkipped('LCC20260894 is not in this database.');
        }
        $this->student = $student;
        config(['class_routine.calendar.enabled' => true, 'mail.redirect_to' => 'limon@lcc.ac.uk']);
        $this->travelTo('2026-09-01 09:00:00'); // before the term, so every class date is still to come

        $this->calendar = new class implements ClassCalendar {
            public array $events = [];
            public array $deleted = [];
            public array $existing = [];
            public ?string $owner = null;
            public function configured(): bool { return true; }
            public function upsert(string $calendarUser, array $event): string
            {
                $this->owner = $calendarUser;
                $known = isset($this->events[$event['id']]) || in_array($event['id'], $this->existing, true);
                $this->events[$event['id']] = $event;

                return $known ? 'updated' : 'created';
            }
            public function delete(string $calendarUser, string $eventId): bool
            {
                $this->deleted[] = $eventId;
                $had = isset($this->events[$eventId]) || in_array($eventId, $this->existing, true);
                unset($this->events[$eventId]);

                return $had;
            }
            public function eventIds(string $calendarUser, int $studentId, string $fromIso): array
            {
                return array_values(array_unique(array_merge(array_keys($this->events), $this->existing)));
            }
        };
        $this->app->instance(ClassCalendar::class, $this->calendar);
    }

    private function liveDates(): \Illuminate\Support\Collection
    {
        $plans = DB::table('assigns as a')->join('plans as p', 'p.id', '=', 'a.plan_id')
            ->where('a.student_id', $this->student->id)->whereNull('a.deleted_at')->whereNull('p.deleted_at')
            ->where('p.term_declaration_id', 55)->pluck('p.id');

        return DB::table('plans_date_lists')->whereIn('plan_id', $plans)->whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('status')->orWhereNotIn('status', ['Canceled', 'Cancelled']))->get();
    }

    public function test_every_class_date_becomes_one_event_in_the_test_calendar_off_production(): void
    {
        $result = app(ClassCalendarSync::class)->sync($this->student->id);

        $this->assertSame('done', $result['status']);
        $this->assertSame('limon@lcc.ac.uk', $this->calendar->owner); // never the student's own calendar here
        $this->assertSame($this->liveDates()->count(), $result['created']);
        $this->assertCount($this->liveDates()->count(), $this->calendar->events);

        // Wednesday 14:00 lecture on 9 Sep
        $lecture = collect($this->calendar->events)->first(fn ($e) => $e['start']['dateTime'] === '2026-09-09T14:00:00');
        $this->assertSame('[LCC20260894] Lecture · The Hospitality Business Toolkit', $lecture['summary']);
        $this->assertSame('2026-09-09T16:00:00', $lecture['end']['dateTime']);
        $this->assertSame('Europe/London', $lecture['start']['timeZone']);
        $this->assertStringContainsString('Barking-Red-Room', $lecture['location']);
        $this->assertStringContainsString('Lecturer: Mohammad Mahbubul Haque', $lecture['description']);
        $this->assertSame((string) $this->student->id, $lecture['extendedProperties']['private']['lcc_student']);
        $this->assertMatchesRegularExpression('/^[0-9a-v]{5,1024}$/', $lecture['id']); // Google's id alphabet
    }

    public function test_running_it_again_updates_and_cancelled_or_dropped_classes_are_removed(): void
    {
        $sync = app(ClassCalendarSync::class);
        $sync->sync($this->student->id);

        // a class date gets cancelled, and an old event of a class the student left is still there
        $cancelled = $this->liveDates()->sortBy('date')->first();
        DB::table('plans_date_lists')->where('id', $cancelled->id)->update(['status' => 'Canceled']);
        $this->calendar->existing = ['lcc999999999s'.$this->student->id];

        $again = $sync->sync($this->student->id);

        $this->assertSame(0, $again['created']);
        $this->assertSame($this->liveDates()->count(), $again['updated']);
        $this->assertSame(2, $again['removed']);
        $this->assertContains(ClassCalendarSync::eventId($cancelled->id, $this->student->id), $this->calendar->deleted);
        $this->assertContains('lcc999999999s'.$this->student->id, $this->calendar->deleted);
    }

    public function test_past_classes_are_left_alone(): void
    {
        $this->travelTo('2026-11-17 08:00:00');
        $result = app(ClassCalendarSync::class)->sync($this->student->id);

        $this->assertSame($this->liveDates()->where('date', '>=', '2026-11-17')->count(), $result['created']);
    }

    public function test_on_production_it_is_the_students_own_calendar(): void
    {
        $this->app['env'] = 'production';
        app(ClassCalendarSync::class)->sync($this->student->id);

        $this->assertSame('lcc20260894@lcc.ac.uk', $this->calendar->owner);
        $lecture = collect($this->calendar->events)->first(fn ($e) => str_contains($e['summary'], 'Lecture'));
        $this->assertStringStartsWith('Lecture · ', $lecture['summary']); // no test prefix on the student's own calendar
    }

    public function test_sending_the_routine_queues_the_calendar_and_the_command_runs_it(): void
    {
        Storage::fake('local');
        Bus::fake([UserMailerJob::class, SyncStudentClassCalendar::class]);

        $result = app(ClassRoutineSender::class)->send($this->student, null, 1);

        $this->assertSame('Calendar update queued.', $result['calendar']);
        Bus::assertDispatched(SyncStudentClassCalendar::class, fn ($job) => $job->studentId === (int) $this->student->id);

        $this->artisan('class-calendar:sync', ['student' => 'LCC20260894'])->assertExitCode(0)->run();
        $this->assertNotEmpty($this->calendar->events);
    }

    public function test_switched_off_it_touches_nothing(): void
    {
        config(['class_routine.calendar.enabled' => false]);

        $this->assertSame('off', app(ClassCalendarSync::class)->sync($this->student->id)['status']);
        $this->assertSame([], $this->calendar->events);
    }
}
