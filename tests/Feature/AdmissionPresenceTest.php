<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantViewSession;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Staff time on an applicant's record: start / heartbeat / end write
 * applicant_view_sessions rows with capped active seconds.
 */
class AdmissionPresenceTest extends TestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function staff(): User
    {
        return User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
    }

    public function test_the_record_pages_carry_the_presence_hook(): void
    {
        $applicant = Applicant::whereNotNull('submission_date')->orderBy('id', 'desc')->first();

        $this->actingAs($this->staff())->get(route('admission.show', $applicant->id))->assertOk()
            ->assertSee('data-adp-presence', false)
            ->assertSee('data-section="details"', false)
            ->assertSee(route('admission.presence.start'), false);
        $this->actingAs($this->staff())->get(route('admission.notes', $applicant->id))->assertOk()
            ->assertSee('data-section="notes"', false);
    }

    public function test_start_heartbeat_and_end_record_a_capped_active_time(): void
    {
        $applicant = Applicant::whereNotNull('submission_date')->orderBy('id', 'desc')->first();
        $staff = $this->staff();
        Carbon::setTestNow('2026-10-23 10:00:00');

        $start = $this->actingAs($staff)->postJson(route('admission.presence.start'), ['applicant_id' => $applicant->id, 'route' => 'admission.process', 'url' => 'https://lcc_datafuture.test/admission/process/'.$applicant->id]);
        $start->assertStatus(201);
        $id = (int) $start->json('id');
        $session = ApplicantViewSession::find($id);
        $this->assertSame('process', $session->section);
        $this->assertSame($staff->id, (int) $session->user_id);
        $this->assertSame(0, (int) $session->active_seconds);

        // Two regular heartbeats, 15 s apart.
        Carbon::setTestNow('2026-10-23 10:00:15');
        $this->actingAs($staff)->postJson(route('admission.presence.beat'), ['id' => $id])->assertOk();
        Carbon::setTestNow('2026-10-23 10:00:30');
        $this->actingAs($staff)->postJson(route('admission.presence.beat'), ['id' => $id])->assertOk();
        $this->assertSame(30, (int) $session->fresh()->active_seconds);

        // The tab was hidden for 10 minutes: the resume beat restarts the clock without crediting the gap.
        Carbon::setTestNow('2026-10-23 10:10:30');
        $this->actingAs($staff)->postJson(route('admission.presence.beat'), ['id' => $id, 'resume' => 1])->assertOk();
        $this->assertSame(30, (int) $session->fresh()->active_seconds);

        // A silent gap (no beats) is capped when the page is finally left.
        Carbon::setTestNow('2026-10-23 10:12:00');
        $this->actingAs($staff)->postJson(route('admission.presence.end'), ['id' => $id])->assertOk();
        $session->refresh();
        $this->assertSame(30 + ApplicantViewSession::MAX_BEAT_GAP, (int) $session->active_seconds);
        $this->assertNotNull($session->ended_at);
        $this->assertSame(3, (int) $session->heartbeats);

        // Nothing more is accepted once ended, and another user cannot touch it.
        $this->actingAs($staff)->postJson(route('admission.presence.beat'), ['id' => $id])->assertStatus(410);
        $other = User::where('active', 1)->where('id', '!=', $staff->id)->first();
        if ($other) {
            $this->actingAs($other)->postJson(route('admission.presence.end'), ['id' => $id])->assertStatus(410);
        }
    }

    public function test_presence_requires_a_signed_in_staff_member(): void
    {
        $applicant = Applicant::whereNotNull('submission_date')->orderBy('id', 'desc')->first();

        $before = ApplicantViewSession::count();
        // The app's auth middleware redirects to the login page rather than answering 401.
        $response = $this->postJson(route('admission.presence.start'), ['applicant_id' => $applicant->id, 'route' => 'admission.show']);
        $this->assertContains($response->getStatusCode(), [401, 302]);
        $this->assertSame($before, ApplicantViewSession::count());
    }
}
