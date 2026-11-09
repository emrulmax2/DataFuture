<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantViewSession;
use App\Models\User;
use App\Services\Admission\ApplicantTimeline;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * The Time line tab: every action on an application, oldest first, read back
 * from the tables the other admission screens write (ApplicantTimeline).
 */
class AdmissionTimelineTest extends TestCase
{
    use DatabaseTransactions;

    /** A submitted applicant with no status archive rows yet, so the chain is ours. */
    private function applicant(): Applicant
    {
        $id = DB::table('applicants')->whereNull('deleted_at')->where('status_id', '>', 1)->whereNotNull('submission_date')->whereNotNull('applicant_user_id')
            ->whereNotExists(fn ($q) => $q->from('applicant_archives')->whereColumn('applicant_archives.applicant_id', 'applicants.id'))
            ->orderByDesc('id')->value('id');
        $this->assertNotNull($id, 'needs a submitted applicant without archive rows');

        return Applicant::find($id);
    }

    private function events(Applicant $a): array
    {
        return ApplicantTimeline::for($a->fresh())['events'];
    }

    private function titled(array $events, string $title): ?array
    {
        foreach ($events as $e) {
            if ($e['title'] === $title) return $e;
        }
        return null;
    }

    public function test_the_tab_renders_with_the_submission_and_the_closing_status(): void
    {
        $a = $this->applicant();
        $html = $this->actingAs(User::find(7))->get(route('admission.timeline', $a->id))->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('admission.timeline', $a->id).'" class="on" aria-current="page"', $html);
        $this->assertStringContainsString('data-adp-atl ', $html);
        $this->assertStringContainsString('Application submitted', $html);
        $this->assertStringContainsString('data-adp-atl-now', $html);
        // every other record page links to the tab too
        $this->actingAs(User::find(7))->get(route('admission.show', $a->id))->assertOk()->assertSee(route('admission.timeline', $a->id), false);
        $this->assertSame('timeline', ApplicantViewSession::sectionFor('admission.timeline'));
    }

    public function test_status_changes_follow_the_chain_and_show_the_rejection_reason(): void
    {
        $a = $this->applicant();
        $reason = DB::table('application_rejected_reasons')->first();
        $rows = [
            ['2030-01-01 09:00:00', 'status_id', 2, 3],
            // several writers store the new status in field_value too: "4 -> 4" means 3 -> 4
            ['2030-01-02 09:00:00', 'status_id', 4, 4],
            ['2030-01-03 09:00:00', 'status_id', 4, 4],
            ['2030-01-04 09:00:00', 'status_id', 8, 8],
            ['2030-01-04 09:00:01', 'application_rejected_reason_id', null, $reason->id],
        ];
        foreach ($rows as [$at, $field, $old, $new]) {
            DB::table('applicant_archives')->insert(['applicant_id' => $a->id, 'table' => 'applicants', 'field_name' => $field, 'field_value' => $old, 'field_new_value' => $new, 'created_by' => 7, 'created_at' => $at, 'updated_at' => $at]);
        }

        $status = array_values(array_filter($this->events($a), fn ($e) => $e['cat'] === 'status' && str_starts_with($e['at'], '2030-')));
        $this->assertSame(['New → In Progress', 'In Progress → Awaiting Decision', 'Status set to Awaiting Decision again', 'Awaiting Decision → Rejected'], array_column($status, 'title'));
        $this->assertSame('Reason: '.$reason->name, $status[3]['detail']);
        $this->assertTrue($status[3]['milestone']);
        $this->assertSame('bad', $status[3]['tone']);
        $this->assertSame('u:7', $status[0]['who']);
    }

    public function test_a_burst_of_uploads_is_one_event_with_its_files(): void
    {
        $a = $this->applicant();
        foreach (['2030-02-01 10:00:00', '2030-02-01 10:01:30', '2030-02-01 10:03:00', '2030-02-01 10:40:00'] as $i => $at) {
            DB::table('applicant_documents')->insert(['applicant_id' => $a->id, 'path' => 'x', 'display_file_name' => 'Doc '.$i, 'current_file_name' => 'doc'.$i.'.pdf', 'hard_copy_check' => $i === 0 ? 1 : 0, 'created_by' => 7, 'created_at' => $at, 'updated_at' => $at]);
        }

        $docs = array_values(array_filter($this->events($a), fn ($e) => $e['cat'] === 'doc' && str_starts_with($e['at'], '2030-02-01')));
        $this->assertCount(2, $docs, 'three uploads within minutes fold together; one 37 minutes later does not');
        $this->assertSame('3 files added', $docs[0]['title']);
        $this->assertSame(['Doc 0 · hard copy seen', 'Doc 1', 'Doc 2'], array_column($docs[0]['items'], 'detail'));
        $this->assertSame('10:03', $docs[0]['end']);
        $this->assertSame('File uploaded', $docs[1]['title']);
    }

    public function test_actors_are_the_applicant_the_system_account_or_staff(): void
    {
        $a = $this->applicant();
        $email = DB::table('applicant_users')->where('id', $a->applicant_user_id)->value('email');
        DB::table('applicant_e_signature_events')->insert(['applicant_id' => $a->id, 'user_type' => 'applicant', 'event_type' => 'finalized', 'event_description' => $email.' finalized the sign request for the document', 'created_at' => '2030-03-01 12:00:00', 'updated_at' => '2030-03-01 12:00:00']);
        DB::table('applicant_notes')->insert(['applicant_id' => $a->id, 'note' => '<p>Called the <b>applicant</b></p>', 'created_by' => 1, 'created_at' => '2030-03-01 12:05:00', 'updated_at' => '2030-03-01 12:05:00', 'deleted_at' => '2030-03-02 08:00:00', 'updated_by' => 7]);

        $t = ApplicantTimeline::for($a->fresh());
        $sign = $this->titled($t['events'], 'Finalised');
        $this->assertSame('applicant', $sign['who']);
        $note = $this->titled($t['events'], 'Note added');
        $this->assertSame('system', $note['who'], 'user 1 is the System account');
        $this->assertSame('Called the applicant', $note['detail']);
        $this->assertSame('u:7', $this->titled($t['events'], 'Note deleted')['who']);
        $this->assertSame('Applicant', $t['actors']['applicant']['role']);
        $this->assertStringContainsString('called the applicant', $note['search']);
    }

    public function test_only_categories_with_events_get_a_filter_chip(): void
    {
        $a = $this->applicant();
        $t = ApplicantTimeline::for($a);
        $html = $this->actingAs(User::find(7))->get(route('admission.timeline', $a->id))->assertOk()->getContent();
        foreach ($t['counts'] as $cat => $n) {
            $chip = 'data-adp-atl-cat="'.$cat.'"';
            $n > 0 ? $this->assertStringContainsString($chip, $html) : $this->assertStringNotContainsString($chip, $html);
        }
        $this->assertSame(count($t['events']), substr_count($html, 'data-adp-atl-ev '));
    }
}
