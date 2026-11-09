<?php

namespace Tests\Feature;

use App\Http\Controllers\AdmissionController;
use App\Jobs\ProcessDocusealSubmission;
use App\Mail\OfferAcceptanceUpdate;
use App\Models\AdmissionNotification;
use App\Models\ApplicantDocument;
use App\Models\Applicant;
use App\Models\ApplicantArchive;
use App\Models\ApplicantDocusealSubmission;
use App\Models\ApplicantFeeEligibility;
use App\Models\ApplicantProofOfId;
use App\Models\StudentConversionLog;
use App\Models\User;
use App\Services\DocuSeal\OfferAcceptanceDocuments;
use App\Services\DocuSeal\OfferAcceptanceWorkflow;
use Illuminate\Bus\PendingBatch;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * DocuSeal webhooks → OfferAcceptanceWorkflow: signed → uploads + Offer Accepted +
 * conversion + admin email; declined → Offer Rejected + admin email.
 */
class DocusealSigningWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private const API = 'https://api.docuseal.co';
    private const PDF = "%PDF-1.7\n% fake\n";

    protected function setUp(): void
    {
        parent::setUp();
        config([
            'services.docuseal.api_key' => 'test-key',
            'services.docuseal.api_url' => self::API,
            'services.docuseal.notify_email' => 'admission@lcc.ac.uk',
            'services.docuseal.webhook_secret_key' => null,
            'services.docuseal.webhook_secret' => null,
            'services.docuseal.webhook_hmac_secret' => null,
            'services.docuseal.auto_convert' => true,
        ]);
        Storage::fake('local');
        Http::preventStrayRequests();
        Bus::fake();
        Mail::fake();
    }

    /** An applicant at Offer Placed (inside the test transaction). */
    private function offerPlaced(bool $withProof = true): Applicant
    {
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')->whereHas('course')
            ->whereDoesntHave('feeeligibility')->whereDoesntHave('proofs')
            // not one with real DocuSeal forms of its own (e.g. the test applicant 3562)
            ->whereNotExists(fn ($q) => $q->from('applicant_docuseal_submissions')->whereColumn('applicant_docuseal_submissions.applicant_id', 'applicants.id'))
            ->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);
        $applicant->status_id = 6;
        $applicant->save();
        if ($withProof) {
            ApplicantProofOfId::create(['applicant_id' => $applicant->id, 'proof_type' => 'passport', 'proof_id' => 'P1234567', 'proof_expiredate' => '31-12-2030', 'created_by' => 1]);
        }

        return $applicant->fresh(['proof', 'feeeligibility']);
    }

    private function remote(int $id, string $status, array $applicantSide = []): array
    {
        $done = $status === 'completed';

        return [
            'id' => $id,
            'status' => $status,
            'created_at' => '2026-10-23T11:01:15.638Z',
            'completed_at' => $done ? '2026-10-23T11:29:46.708Z' : null,
            'template' => ['id' => 6568085],
            'submitters' => [
                ['role' => 'London Churchill College', 'email' => 'admission@lcc.ac.uk', 'status' => 'completed'],
                array_merge(['role' => 'Applicant', 'email' => 'test@example.com', 'status' => $status === 'declined' ? 'declined' : ($done ? 'completed' : 'opened')], $applicantSide),
            ],
            'documents' => $done ? [['name' => 'Offer acceptance form Local Check', 'url' => 'https://docuseal.com/file/signed-'.$id.'/Offer.pdf']] : [],
            'audit_log_url' => $done ? 'https://docuseal.com/file/audit-'.$id.'/Audit.pdf' : null,
        ];
    }

    private function fakeRemote(array $submission): void
    {
        Http::fake([
            self::API.'/submissions/'.$submission['id'] => Http::response($submission, 200),
            'https://docuseal.com/file/*' => Http::response(self::PDF, 200, ['Content-Type' => 'application/pdf']),
        ]);
    }

    private function row(Applicant $applicant, int $submissionId): ApplicantDocusealSubmission
    {
        return app(OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => $submissionId, 'template_id' => 6568085], null);
    }

    /** The body DocuSeal posted for 3561's form, as pasted by the user (trimmed). */
    private function webhookBody(string $event, int $submissionId, array $data = []): array
    {
        return [
            'event_type' => $event,
            'timestamp' => '2026-10-23T11:31:05Z',
            'data' => array_merge([
                'id' => 16386510,
                'email' => 'limon@churchill.ac',
                'opened_at' => '2026-10-23T11:24:01.683Z',
                'completed_at' => '2026-10-23T11:29:46.708Z',
                'status' => 'completed',
                'decline_reason' => null,
                'role' => 'Applicant',
                'documents' => [['name' => 'Offer acceptance form Local Check', 'url' => 'https://docuseal.com/file/x/Offer.pdf']],
                'template' => ['id' => 6568085, 'name' => 'Offer acceptance form Local Check'],
                'audit_log_url' => 'https://docuseal.com/file/y/Audit.pdf',
                'submission' => ['id' => $submissionId, 'status' => 'completed'],
            ], $data),
        ];
    }

    /* ---------------- webhook endpoint ---------------- */

    public function test_the_webhook_queues_work_only_for_forms_we_sent(): void
    {
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777001);

        $this->postJson(route('docuseal.webhook'), $this->webhookBody('form.completed', 999999))->assertOk()->assertJson(['ignored' => 'unknown submission']);
        Bus::assertNotDispatched(ProcessDocusealSubmission::class);

        $this->postJson(route('docuseal.webhook'), $this->webhookBody('form.completed', 777001))->assertOk()->assertJson(['ok' => true]);
        Bus::assertDispatched(ProcessDocusealSubmission::class, fn ($job) => $job->rowId === $row->id);

        // form.viewed only notes when the applicant opened it.
        $this->postJson(route('docuseal.webhook'), $this->webhookBody('form.viewed', 777001, ['status' => 'opened']))->assertOk();
        $this->assertNotNull($row->fresh()->opened_at);
        Bus::assertDispatchedTimes(ProcessDocusealSubmission::class, 1);
    }

    public function test_the_webhook_checks_the_secret_header_when_configured(): void
    {
        config(['services.docuseal.webhook_secret_key' => 'X-LCC-Secret', 'services.docuseal.webhook_secret' => 's3cret']);
        $applicant = $this->offerPlaced();
        $this->row($applicant, 777002);

        $this->postJson(route('docuseal.webhook'), $this->webhookBody('form.completed', 777002))->assertStatus(401);
        $this->postJson(route('docuseal.webhook'), $this->webhookBody('form.completed', 777002), ['X-LCC-Secret' => 'wrong'])->assertStatus(401);
        Bus::assertNotDispatched(ProcessDocusealSubmission::class);

        $this->postJson(route('docuseal.webhook'), $this->webhookBody('form.completed', 777002), ['X-LCC-Secret' => 's3cret'])->assertOk();
        Bus::assertDispatched(ProcessDocusealSubmission::class);
    }

    public function test_the_webhook_accepts_a_valid_docuseal_hmac_signature(): void
    {
        $hmac = 'whsec_'.base64_encode(random_bytes(24));
        config(['services.docuseal.webhook_hmac_secret' => $hmac, 'services.docuseal.webhook_secret_key' => 'X-LCC-Secret', 'services.docuseal.webhook_secret' => 's3cret']);
        $applicant = $this->offerPlaced();
        $this->row($applicant, 777011);
        $body = json_encode($this->webhookBody('form.completed', 777011));
        $post = fn (array $headers) => $this->call('POST', route('docuseal.webhook'), [], [], [], array_merge(['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'], $headers), $body);
        $sign = fn (int $ts, string $payload) => $ts.'.'.hash_hmac('sha256', $ts.'.'.$payload, $hmac);

        $post([])->assertStatus(401);
        $post(['HTTP_X_DOCUSEAL_SIGNATURE' => $sign(time(), $body.' ')])->assertStatus(401);           // body altered
        $post(['HTTP_X_DOCUSEAL_SIGNATURE' => $sign(time() - 3600, $body)])->assertStatus(401);       // replayed an hour later
        $post(['HTTP_X_DOCUSEAL_SIGNATURE' => $sign(time(), $body)])->assertOk();                      // what DocuSeal sends
        $post(['HTTP_X_LCC_SECRET' => 's3cret'])->assertOk();                                          // the Secret tab works too
        Bus::assertDispatchedTimes(ProcessDocusealSubmission::class, 2);
    }

    public function test_a_forged_completed_webhook_changes_nothing_while_docuseal_says_pending(): void
    {
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777003);
        $this->fakeRemote($this->remote(777003, 'pending'));

        (new ProcessDocusealSubmission($row->id))->handle(app(OfferAcceptanceWorkflow::class));

        $this->assertSame(6, (int) $applicant->fresh()->status_id);
        $this->assertSame('waiting', $row->fresh()->state);
        Bus::assertNothingBatched();
        Mail::assertNothingSent();
    }

    /* ---------------- signed ---------------- */

    public function test_signed_stores_the_documents_accepts_the_offer_and_queues_the_conversion(): void
    {
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777004);
        $this->fakeRemote($this->remote(777004, 'completed'));

        (new ProcessDocusealSubmission($row->id))->handle(app(OfferAcceptanceWorkflow::class));

        $row->refresh();
        // 1. documents
        $this->assertSame('stored', $row->state);
        $this->assertNotNull($row->offer_document_id);
        $this->assertNotNull($row->audit_document_id);
        // 2. status + archive by the System user
        $this->assertSame(7, (int) $applicant->fresh()->status_id);
        $this->assertTrue(ApplicantArchive::where('applicant_id', $applicant->id)->where('field_name', 'status_id')->where('field_value', 6)->where('field_new_value', 7)->where('created_by', 1)->exists());
        // the dialog's fee default when nothing was recorded
        $this->assertSame(3, (int) ApplicantFeeEligibility::where('applicant_id', $applicant->id)->value('fee_eligibility_id'));
        // the conversion: one chained batch of the 21 jobs, logs seeded
        Bus::assertBatched(function (PendingBatch $batch) {
            $chain = $batch->jobs->first();

            return $batch->jobs->count() === 1 && count($chain) === count(StudentConversionLog::JOB_SEQUENCE)
                && get_class($chain[0]) === StudentConversionLog::JOB_SEQUENCE[0]
                // after everything is copied, Task Automation's "Student created" rules run
                && get_class(end($chain)) === \App\Jobs\ProcessStudentTaskRules::class;
        });
        $this->assertSame('converting', $row->outcome);
        $this->assertNotNull($row->batch_id);
        $this->assertSame(count(StudentConversionLog::JOB_SEQUENCE), StudentConversionLog::where('batch_id', $row->batch_id)->where('applicant_id', $applicant->id)->count());
        Mail::assertNothingSent();

        // 3. the batch finished → admin email
        app(OfferAcceptanceWorkflow::class)->conversionFinished($row->id, $row->batch_id, true);
        $this->assertSame('converted', $row->fresh()->outcome);
        Mail::assertSent(OfferAcceptanceUpdate::class, fn ($m) => $m->kind === 'converted' && $m->hasTo('admission@lcc.ac.uk') && $m->applicant->id === $applicant->id);

        // A repeated webhook does nothing more.
        (new ProcessDocusealSubmission($row->id))->handle(app(OfferAcceptanceWorkflow::class));
        Bus::assertBatchCount(1);
        Mail::assertSent(OfferAcceptanceUpdate::class, 1);
        $this->assertSame(1, ApplicantArchive::where('applicant_id', $applicant->id)->where('field_name', 'status_id')->where('field_new_value', 7)->count());
    }

    public function test_with_auto_convert_off_a_signed_form_is_only_stored(): void
    {
        config(['services.docuseal.auto_convert' => false]);
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777010);
        $this->fakeRemote($this->remote(777010, 'completed'));

        (new ProcessDocusealSubmission($row->id))->handle(app(OfferAcceptanceWorkflow::class));
        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();

        $row->refresh();
        $this->assertSame('stored', $row->state);
        $this->assertNotNull($row->offer_document_id);
        $this->assertNotNull($row->audit_document_id);
        $this->assertNull($row->outcome);
        $this->assertSame(6, (int) $applicant->fresh()->status_id);
        $this->assertFalse(ApplicantFeeEligibility::where('applicant_id', $applicant->id)->exists());
        Bus::assertNothingBatched();
        Mail::assertNothingSent();

        // Switched on later: the waiting signed form is picked up.
        config(['services.docuseal.auto_convert' => true]);
        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();
        $this->assertSame(7, (int) $applicant->fresh()->status_id);
        Bus::assertBatchCount(1);
    }

    public function test_a_stopped_conversion_emails_admin_with_the_failed_step(): void
    {
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777005);
        $this->fakeRemote($this->remote(777005, 'completed'));
        app(OfferAcceptanceWorkflow::class)->process($row);
        $row->refresh();
        StudentConversionLog::where('batch_id', $row->batch_id)->where('job_class', StudentConversionLog::JOB_SEQUENCE[1])->update(['status' => 'failed', 'message' => 'Failed: S3 said no']);

        app(OfferAcceptanceWorkflow::class)->conversionFinished($row->id, $row->batch_id, false, 'S3 said no');

        $this->assertSame('conversion_failed', $row->fresh()->outcome);
        Mail::assertSent(OfferAcceptanceUpdate::class, fn ($m) => $m->kind === 'conversion_failed' && $m->details['failed_job'] === StudentConversionLog::jobLabel(StudentConversionLog::JOB_SEQUENCE[1]));
    }

    public function test_signed_without_proof_of_id_is_held_at_offer_placed(): void
    {
        $applicant = $this->offerPlaced(false);
        $row = $this->row($applicant, 777006);
        $this->fakeRemote($this->remote(777006, 'completed'));

        app(OfferAcceptanceWorkflow::class)->process($row);

        $row->refresh();
        $this->assertSame('stored', $row->state);
        $this->assertSame('held_missing_proof', $row->outcome);
        $this->assertSame(6, (int) $applicant->fresh()->status_id);
        Bus::assertNothingBatched();
        Mail::assertSent(OfferAcceptanceUpdate::class, fn ($m) => $m->kind === 'held' && count($m->details['missing']) === 3);
    }

    public function test_signed_after_staff_already_moved_the_application_only_stores_the_documents(): void
    {
        $applicant = $this->offerPlaced();
        $applicant->status_id = 9;
        $applicant->save();
        $row = $this->row($applicant, 777007);
        $this->fakeRemote($this->remote(777007, 'completed'));

        app(OfferAcceptanceWorkflow::class)->process($row);

        $row->refresh();
        $this->assertSame('stored', $row->state);
        $this->assertSame('status_unchanged', $row->outcome);
        $this->assertSame(9, (int) $applicant->fresh()->status_id);
        Bus::assertNothingBatched();
        Mail::assertNothingSent();
    }

    /* ---------------- declined ---------------- */

    public function test_declined_rejects_the_offer_and_emails_admin_with_the_reason(): void
    {
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777008);
        $this->fakeRemote($this->remote(777008, 'declined', ['declined_at' => '2026-10-23T12:00:00Z']));

        $this->postJson(route('docuseal.webhook'), $this->webhookBody('form.declined', 777008, ['status' => 'declined', 'decline_reason' => 'I chose another college']))->assertOk();
        Bus::assertDispatched(ProcessDocusealSubmission::class, function ($job) {
            $job->handle(app(OfferAcceptanceWorkflow::class));

            return true;
        });

        $row->refresh();
        $this->assertSame('declined', $row->state);
        $this->assertSame('rejected', $row->outcome);
        $this->assertSame('I chose another college', $row->decline_reason);
        $this->assertSame(9, (int) $applicant->fresh()->status_id);
        $this->assertTrue(ApplicantArchive::where('applicant_id', $applicant->id)->where('field_name', 'status_id')->where('field_value', 6)->where('field_new_value', 9)->exists());
        Mail::assertSent(OfferAcceptanceUpdate::class, fn ($m) => $m->kind === 'rejected' && $m->details['reason'] === 'I chose another college');
    }

    /* ---------------- the backstop and the manual path ---------------- */

    public function test_the_scheduled_backstop_runs_the_same_workflow(): void
    {
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777009);
        $this->fakeRemote($this->remote(777009, 'completed'));

        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();

        $this->assertSame('converting', $row->fresh()->outcome);
        $this->assertSame(7, (int) $applicant->fresh()->status_id);
        Bus::assertBatchCount(1);
    }

    public function test_the_change_status_dialog_still_converts_with_its_own_proof_values(): void
    {
        $applicant = $this->offerPlaced(false);
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        Auth::guard('web')->setUser($staff);
        $request = Request::create('/admission/update-status', 'POST', ['applicantID' => $applicant->id, 'statusidID' => 7, 'proof_type' => 'nid', 'proof_id' => 'N99', 'proof_expiredate' => '01-01-2031', 'fee_eligibility_id' => 2]);

        $response = app(AdmissionController::class)->admissionStudentUpdateStatus($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(7, (int) $applicant->fresh()->status_id);
        $this->assertSame('N99', ApplicantProofOfId::where('applicant_id', $applicant->id)->latest('id')->value('proof_id'));
        $this->assertSame(2, (int) ApplicantFeeEligibility::where('applicant_id', $applicant->id)->latest('id')->value('fee_eligibility_id'));
        Bus::assertBatched(fn (PendingBatch $batch) => count($batch->jobs->first()) === count(StudentConversionLog::JOB_SEQUENCE));
        $batchId = session('lastBatchId');
        $this->assertNotEmpty($batchId);
        $this->assertSame(count(StudentConversionLog::JOB_SEQUENCE), StudentConversionLog::where('batch_id', $batchId)->where('created_by', $staff->id)->count());
    }

    /* ---------------- notifications kept in the DB ---------------- */

    public function test_a_decline_notification_is_emailed_and_stored_with_the_sender(): void
    {
        $applicant = $this->offerPlaced();
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $row = app(OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 777020], $staff->id);
        $this->fakeRemote($this->remote(777020, 'declined', ['declined_at' => '2026-10-23T12:00:00Z', 'decline_reason' => 'Going elsewhere']));

        app(OfferAcceptanceWorkflow::class)->process($row);

        Mail::assertSent(OfferAcceptanceUpdate::class, fn ($m) => $m->kind === 'rejected');
        // $n = AdmissionNotification::where('applicant_id', $applicant->id)->sole();
        // since 27 Oct 2026 the applicant (and an agent) are emailed too: the admin copy is the one to admissions
        $n = AdmissionNotification::where('applicant_id', $applicant->id)->where('recipients', 'like', 'admission@lcc.ac.uk%')->sole();
        $this->assertSame(AdmissionNotification::TYPE_OFFER_REJECTED, $n->type);
        $this->assertSame($staff->id, (int) $n->user_id);
        $this->assertSame($row->id, (int) $n->docuseal_submission_id);
        // $this->assertSame('admission@lcc.ac.uk', $n->recipients);
        // ... and the staff member who sent the form is copied in
        $this->assertSame('admission@lcc.ac.uk'.((int) $staff->id !== 1 && $staff->email ? ', '.$staff->email : ''), $n->recipients);
        $this->assertTrue(AdmissionNotification::where('applicant_id', $applicant->id)->where('recipients', $applicant->users->email)->where('subject', 'You have declined your offer from London Churchill College')->exists());
        $this->assertSame('sent', $n->status);
        $this->assertNotNull($n->sent_at);
        $this->assertNull($n->read_at);
        $this->assertStringStartsWith('Offer rejected: ', $n->subject);
        $this->assertStringContainsString($applicant->application_no, $n->body);
        $this->assertStringContainsString('Going elsewhere', $n->body);
        // the stored copy carries the college footer and links the logo instead of embedding it
        $this->assertStringContainsString('intended for the above named only', $n->body);
        $this->assertStringContainsString('+44 (0) 207 377 1077', $n->body);
        $this->assertStringNotContainsString('data:image', $n->body);
        $this->assertStringContainsString('/storage/', $n->body);
        $this->assertLessThan(30000, strlen($n->body));
        $this->assertSame('Going elsewhere', $n->data['reason']);
        $this->assertSame(777020, $n->data['submission_id']);
    }

    public function test_the_accepted_notification_is_stored_when_the_conversion_finishes(): void
    {
        $applicant = $this->offerPlaced();
        $row = $this->row($applicant, 777021);
        $this->fakeRemote($this->remote(777021, 'completed'));
        app(OfferAcceptanceWorkflow::class)->process($row);
        $this->assertSame(0, AdmissionNotification::where('applicant_id', $applicant->id)->count());

        app(OfferAcceptanceWorkflow::class)->conversionFinished($row->id, $row->fresh()->batch_id, true);

        $n = AdmissionNotification::where('applicant_id', $applicant->id)->sole();
        $this->assertSame(AdmissionNotification::TYPE_OFFER_ACCEPTED, $n->type);
        $this->assertNull($n->user_id); // a form linked by hand has no sender
        $this->assertSame('sent', $n->status);
        $this->assertSame($row->fresh()->batch_id, $n->data['batch_id']);
    }

    public function test_the_accepted_email_names_the_new_student_and_links_the_record(): void
    {
        $student = \App\Models\Student::whereNotNull('applicant_id')->whereNotNull('registration_no')->orderByDesc('id')->first();
        $this->assertNotNull($student);
        $applicant = Applicant::find($student->applicant_id);
        $row = $this->row($applicant, 777024);
        $row->update(['outcome' => 'converting']);

        // A conversion whose log rows carry no student id falls back to the applicant's student record.
        app(OfferAcceptanceWorkflow::class)->conversionFinished($row->id, null, true);

        Mail::assertSent(OfferAcceptanceUpdate::class, function ($m) use ($student) {
            $html = $m->render();

            return $m->kind === 'converted'
                && $m->details['student_id'] === $student->id
                && $m->details['registration_no'] === $student->registration_no
                && $m->details['student_url'] === route('student.show', $student->id)
                && str_contains($html, 'has accepted the offer from London Churchill College')
                && str_contains($html, 'Please open the student record and check')
                && str_contains($html, $student->registration_no)
                && str_contains($html, 'Open student record');
        });
        $n = AdmissionNotification::where('docuseal_submission_id', $row->id)->sole();
        $this->assertSame($student->registration_no, $n->data['registration_no']);
        $this->assertStringEndsWith('has been moved to Students', $n->subject);
    }

    public function test_a_failed_or_unaddressed_email_is_still_stored(): void
    {
        $applicant = $this->offerPlaced(false);
        $row = $this->row($applicant, 777022);
        $this->fakeRemote($this->remote(777022, 'completed'));
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP is down'));

        app(OfferAcceptanceWorkflow::class)->process($row);

        $n = AdmissionNotification::where('applicant_id', $applicant->id)->sole();
        $this->assertSame(AdmissionNotification::TYPE_PROOF_MISSING, $n->type);
        $this->assertSame('failed', $n->status);
        $this->assertSame('SMTP is down', $n->error);
        $this->assertSame('held_missing_proof', $row->fresh()->outcome);

        config(['services.docuseal.notify_email' => '']);
        $other = $this->row($applicant, 777023);
        $this->fakeRemote($this->remote(777023, 'declined'));
        app(OfferAcceptanceWorkflow::class)->process($other);
        $this->assertSame('not_sent', AdmissionNotification::where('docuseal_submission_id', $other->id)->value('status'));
    }

    /* ---------------- only the latest form moves the status ---------------- */

    public function test_an_older_form_signed_after_a_newer_one_was_sent_does_not_change_the_status(): void
    {
        $applicant = $this->offerPlaced();
        $old = $this->row($applicant, 777030);
        $old->update(['sent_at' => now()->subHour()]);
        $this->row($applicant, 777031); // re-sent since
        $this->fakeRemote($this->remote(777030, 'completed'));

        app(OfferAcceptanceWorkflow::class)->process($old->fresh());

        $old->refresh();
        $this->assertSame('stored', $old->state); // the signed form is still kept
        $this->assertSame('status_unchanged', $old->outcome);
        $this->assertStringContainsString('newer offer acceptance form (777031)', $old->outcome_note);
        $this->assertSame(6, (int) $applicant->fresh()->status_id);
        Bus::assertNothingBatched();
    }

    public function test_a_signed_form_is_stored_again_when_the_earlier_upload_was_deleted(): void
    {
        $applicant = $this->offerPlaced();
        $first = $this->row($applicant, 777040);
        $first->update(['sent_at' => now()->subHour()]);
        $this->fakeRemote($this->remote(777040, 'completed'));
        app(OfferAcceptanceDocuments::class)->check($first->fresh());
        $first->refresh();
        $this->assertSame('stored', $first->state);
        ApplicantDocument::whereIn('id', [$first->offer_document_id, $first->audit_document_id])->get()->each->delete(); // staff removed them (soft delete)

        $second = $this->row($applicant, 777041);
        $this->fakeRemote($this->remote(777041, 'completed'));
        app(OfferAcceptanceDocuments::class)->check($second);

        $this->assertSame('stored', $second->fresh()->state);
        $this->assertNotNull($second->fresh()->offer_document_id);
    }

    public function test_every_admin_email_renders(): void
    {
        $applicant = $this->offerPlaced();
        foreach ([
            'converted' => ['signed_at' => now(), 'student_id' => 20046, 'fee_note' => 'Fee note'],
            'conversion_failed' => ['signed_at' => now(), 'failed_job' => 'Create Student Record', 'error' => 'S3 said no'],
            'held' => ['signed_at' => now(), 'missing' => ['the ID number']],
            'rejected' => ['declined_at' => now(), 'reason' => null],
        ] as $kind => $details) {
            $html = (new OfferAcceptanceUpdate($kind, $applicant, $details))->render();
            $this->assertStringContainsString($applicant->application_no, $html, $kind);
            $this->assertStringContainsString(route('admission.show', $applicant->id), $html, $kind);
        }
        $this->assertStringContainsString('No reason given', (new OfferAcceptanceUpdate('rejected', $applicant, ['reason' => null]))->render());

        // The rejected email lists the applicant's basic details; the others do not.
        $applicant->loadMissing(['users', 'contact', 'course.creation.course', 'course.semester']);
        $rejected = (new OfferAcceptanceUpdate('rejected', $applicant, ['reason' => 'x']))->render();
        $this->assertStringContainsString('Applicant details', $rejected);
        $this->assertStringContainsString('mailto:'.$applicant->users->email, $rejected);
        $this->assertStringContainsString('Application ref no.', $rejected);
        if (!empty($applicant->contact->mobile)) {
            $this->assertStringContainsString($applicant->contact->mobile, $rejected);
        }
        $this->assertStringContainsString(e($applicant->course->creation->course->name), $rejected);
        $this->assertStringContainsString(e($applicant->course->semester->name), $rejected);
        $this->assertStringNotContainsString('Applicant details', (new OfferAcceptanceUpdate('converted', $applicant, []))->render());
    }
}
