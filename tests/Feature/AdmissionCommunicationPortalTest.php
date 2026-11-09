<?php

namespace Tests\Feature;

use App\Jobs\UserMailerJob;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantEmail;
use App\Models\ApplicantEmailsAttachment;
use App\Models\ApplicantLetter;
use App\Models\ApplicantSms;
use App\Models\ComonSmtp;
use App\Models\LetterSet;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The applicant communication page on the admission portal shell: compose
 * email / SMS / letter, the per-channel sent lists, view, delete and restore.
 */
class AdmissionCommunicationPortalTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        $user = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->assertNotNull($user, 'A staff user is needed for this test.');

        return $user;
    }

    private function applicant(): Applicant
    {
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);

        return $applicant;
    }

    public function test_the_communication_page_renders_the_three_compose_forms_and_the_sent_list(): void
    {
        $applicant = $this->applicant();

        $response = $this->actingAs($this->staff())->get(route('admission.communication', $applicant->id));

        $response->assertOk()
            ->assertSee('data-adp-comms', false)
            ->assertSee('id="sendEmailForm"', false)
            ->assertSee('id="smsSMSForm"', false)
            ->assertSee('id="addLetterForm"', false)
            ->assertSee('id="mailEditor"', false)
            ->assertSee('id="letterEditor"', false)
            ->assertSee('id="smsTextArea"', false)
            ->assertSee('data-adp-tags-pop', false)
            ->assertSee('[DATA=applicants]application_no[/DATA]', false)
            ->assertSee('id="viewCommunicationModal"', false)
            ->assertSee('id="changeStatusModal"', false)
            ->assertDontSee('document-editor__toolbar')
            ->assertDontSee('role="tab"', false)
            ->assertDontSee('applicantCommLetterListTable');
    }

    public function test_an_email_with_an_attachment_is_stored_queued_and_listed(): void
    {
        Storage::fake('local');
        Bus::fake();
        $applicant = $this->applicant();
        $staff = $this->staff();
        $smtp = ComonSmtp::first();
        $this->assertNotNull($smtp, 'An SMTP account is needed for this test.');

        $send = $this->actingAs($staff)->post(route('admission.communication.send.mail'), [
            'applicant_id' => $applicant->id,
            'comon_smtp_id' => $smtp->id,
            'subject' => 'Portal test: documents needed',
            'body' => '<p>Please upload your <b>proof of address</b>.</p>',
            'documents' => [UploadedFile::fake()->create('checklist.pdf', 4, 'application/pdf')],
        ]);
        $send->assertOk();
        $mailId = (int) $send->json('id');
        $this->assertSame(1, (int) $send->json('attachments'));
        Bus::assertDispatched(UserMailerJob::class);

        $mail = ApplicantEmail::find($mailId);
        $this->assertNotNull($mail);
        $this->assertSame($staff->id, (int) $mail->created_by);
        $pivot = ApplicantEmailsAttachment::where('applicant_email_id', $mailId)->first();
        $this->assertNotNull($pivot);
        $doc = ApplicantDocument::find($pivot->applicant_document_id);
        $this->assertSame('local', $doc->disk_type);
        Storage::disk('local')->assertExists('public/applicants/'.$applicant->id.'/'.$doc->current_file_name);

        $list = $this->actingAs($staff)->getJson(route('admission.communication.mail.list', ['applicantId' => $applicant->id, 'statusCME' => 1, 'size' => 10, 'page' => 1]));
        $list->assertOk();
        $this->assertGreaterThanOrEqual(1, (int) $list->json('total_rows'));
        $row = collect($list->json('data'))->firstWhere('id', $mailId);
        $this->assertNotNull($row);
        $this->assertSame('Portal test: documents needed', $row['subject']);
        $this->assertSame($smtp->smtp_user, $row['smtp']);
        $this->assertSame(1, (int) $row['attachments']);
        $this->assertStringContainsString('proof of address', $row['excerpt']);

        $show = $this->actingAs($staff)->postJson(route('admission.communication.mail.show'), ['recordId' => $mailId]);
        $show->assertOk();
        $this->assertSame('Portal test: documents needed', $show->json('row.subject'));
        $this->assertStringContainsString('checklist.pdf', $show->json('row.attachments.0.name'));
        $this->assertSame((int) $doc->id, (int) $show->json('row.attachments.0.id'));

        $search = $this->actingAs($staff)->getJson(route('admission.communication.mail.list', ['applicantId' => $applicant->id, 'queryStrCME' => 'no-such-subject-xyz', 'statusCME' => 1, 'size' => 10, 'page' => 1]));
        $this->assertSame(0, (int) $search->json('total_rows'));
    }

    public function test_an_email_without_a_subject_or_body_is_rejected(): void
    {
        $applicant = $this->applicant();
        $smtp = ComonSmtp::first();

        $this->actingAs($this->staff())->postJson(route('admission.communication.send.mail'), ['applicant_id' => $applicant->id, 'comon_smtp_id' => $smtp->id, 'subject' => '', 'body' => ''])
            ->assertStatus(422)->assertJsonValidationErrors(['subject', 'body']);
    }

    public function test_an_sms_is_stored_and_listed_with_its_segments(): void
    {
        Http::fake();
        $applicant = $this->applicant();
        $staff = $this->staff();

        $send = $this->actingAs($staff)->postJson(route('admission.communication.send.sms'), [
            'applicant_id' => $applicant->id,
            'subject' => 'Portal test: interview reminder',
            'sms' => str_repeat('Reminder: your interview is tomorrow at 10:00. ', 5),
        ]);
        $send->assertOk();
        $sms = ApplicantSms::where('applicant_id', $applicant->id)->latest('id')->first();
        $this->assertSame('Portal test: interview reminder', $sms->subject);

        $list = $this->actingAs($staff)->getJson(route('admission.communication.sms.list', ['applicantId' => $applicant->id, 'statusCMS' => 1, 'size' => 10, 'page' => 1]));
        $row = collect($list->json('data'))->firstWhere('id', $sms->id);
        $this->assertNotNull($row);
        $this->assertSame(2, (int) $row['segments']);
        $this->assertStringContainsString('Reminder', $row['excerpt']);

        $show = $this->actingAs($staff)->postJson(route('admission.communication.sms.show'), ['recordId' => $sms->id]);
        $show->assertOk();
        $this->assertSame($sms->sms, $show->json('row.sms'));
    }

    public function test_letters_are_listed_with_their_set_signatory_and_document(): void
    {
        $applicant = $this->applicant();
        $staff = $this->staff();
        $set = LetterSet::where('admission', 1)->first();
        $this->assertNotNull($set, 'An admission letter set is needed for this test.');
        $doc = ApplicantDocument::create(['applicant_id' => $applicant->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf', 'path' => '', 'display_file_name' => $set->letter_title, 'current_file_name' => 'portal_test_letter.pdf', 'disk_type' => 'local', 'created_by' => $staff->id]);
        $letter = ApplicantLetter::create(['applicant_id' => $applicant->id, 'letter_set_id' => $set->id, 'comon_smtp_id' => ComonSmtp::first()->id, 'is_email_or_attachment' => 2, 'applicant_document_id' => $doc->id, 'issued_by' => $staff->id, 'issued_date' => '2026-10-20', 'created_by' => $staff->id]);

        $list = $this->actingAs($staff)->getJson(route('admission.communication.letter.list', ['applicantId' => $applicant->id, 'statusCML' => 1, 'size' => 10, 'page' => 1]));
        $list->assertOk();
        $row = collect($list->json('data'))->firstWhere('id', $letter->id);
        $this->assertNotNull($row);
        $this->assertSame($set->letter_title, $row['letter_title']);
        $this->assertSame((int) $doc->id, (int) $row['docurl']);
        $this->assertSame('20 Oct 2026', $row['issued_date']);
        $this->assertSame($set->letter_title, $row['document_name']);

        $download = $this->actingAs($staff)->postJson(route('admission.document.download'), ['row_id' => $doc->id]);
        $this->assertStringContainsString('/storage/applicants/'.$applicant->id.'/portal_test_letter.pdf', $download->json('res'));
    }

    public function test_delete_and_restore_move_an_email_between_the_lists(): void
    {
        $applicant = $this->applicant();
        $staff = $this->staff();
        $mail = ApplicantEmail::create(['applicant_id' => $applicant->id, 'comon_smtp_id' => ComonSmtp::first()->id, 'subject' => 'Portal test: delete me', 'body' => '<p>Bye</p>', 'created_by' => $staff->id]);

        $this->actingAs($staff)->deleteJson(route('admission.communication.mail.destroy'), ['applicant' => $applicant->id, 'recordid' => $mail->id])->assertOk();
        $this->assertNotNull(ApplicantEmail::withTrashed()->find($mail->id)->deleted_at);

        $deleted = $this->actingAs($staff)->getJson(route('admission.communication.mail.list', ['applicantId' => $applicant->id, 'statusCME' => 2, 'size' => 'true', 'page' => 1]));
        $row = collect($deleted->json('data'))->firstWhere('id', $mail->id);
        $this->assertNotNull($row, 'The deleted mail should be in the deleted list.');
        $this->assertNotEmpty($row['deleted_at']);

        $page = $this->actingAs($staff)->get(route('admission.communication', $applicant->id));
        $page->assertOk()->assertSee('data-adp-deleted-counts', false);
        $this->assertStringContainsString('"email":1', $page->getContent());

        $this->actingAs($staff)->postJson(route('admission.communication.mail.restore'), ['applicant' => $applicant->id, 'recordid' => $mail->id])->assertOk();
        $this->assertNull(ApplicantEmail::find($mail->id)->deleted_at);
    }
}
