<?php

namespace Tests\Feature;

use App\Http\Controllers\AdmissionController;
use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantDocusealSubmission;
use App\Models\User;
use App\Services\DocuSeal\OfferAcceptanceDocuments;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Signed DocuSeal offer acceptance forms land in the applicant's uploads as
 * "Offer Acceptance / Audit Trail - Offer Acceptance" and "… - Audit Trail".
 */
class DocusealOfferAcceptanceTest extends TestCase
{
    use DatabaseTransactions;

    private const API = 'https://api.docuseal.co';
    private const PDF = "%PDF-1.7\n% fake signed pdf\n";

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.docuseal.api_key' => 'test-key', 'services.docuseal.offer_acceptance_template_id' => 999, 'services.docuseal.api_url' => self::API, 'services.docuseal.test_email' => 'test@example.com']);
        Storage::fake('local');
        Http::preventStrayRequests();
        // The command now also runs the status workflow: never run a real conversion or send mail here.
        \Illuminate\Support\Facades\Bus::fake();
        \Illuminate\Support\Facades\Mail::fake();
    }

    private function applicant(): Applicant
    {
        $applicant = Applicant::whereNotNull('submission_date')->whereHas('users')
            // not one with real DocuSeal forms of its own (e.g. the test applicant 3562)
            ->whereNotExists(fn ($q) => $q->from('applicant_docuseal_submissions')->whereColumn('applicant_docuseal_submissions.applicant_id', 'applicants.id'))
            ->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);

        return $applicant;
    }

    private function submission(int $id, string $status, array $extra = []): array
    {
        $done = $status === 'completed';

        return array_merge([
            'id' => $id,
            'status' => $status,
            'created_at' => '2026-10-23T10:00:00.000Z',
            'completed_at' => $done ? '2026-10-23T12:30:00.000Z' : null,
            'template' => ['id' => 999],
            'submitters' => [
                ['role' => 'London Churchill College', 'email' => 'admission@lcc.ac.uk', 'status' => 'completed'],
                ['role' => 'Applicant', 'email' => 'test@example.com', 'status' => $done ? 'completed' : 'opened', 'embed_src' => 'https://docuseal.com/s/abc'],
            ],
            'documents' => $done ? [['name' => 'Offer acceptance form', 'url' => 'https://docuseal.com/file/signed-'.$id]] : [],
            'audit_log_url' => $done ? 'https://docuseal.com/file/audit-'.$id : null,
        ], $extra);
    }

    private function fakeDocuseal(array $submissions, string $fileBody = self::PDF): void
    {
        $map = [];
        foreach ($submissions as $sub) {
            $map[self::API.'/submissions/'.$sub['id']] = Http::response($sub, 200);
        }
        $map['https://docuseal.com/file/*'] = Http::response($fileBody, 200, ['Content-Type' => 'application/pdf']);
        Http::fake($map);
    }

    public function test_sending_the_form_records_the_submission(): void
    {
        $applicant = $this->applicant();
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        Auth::guard('web')->setUser($staff);
        Http::fake([self::API.'/submissions' => Http::response([
            ['id' => 1, 'submission_id' => 555001, 'role' => 'London Churchill College', 'email' => 'admission@lcc.ac.uk', 'status' => 'completed'],
            ['id' => 2, 'submission_id' => 555001, 'role' => 'Applicant', 'email' => 'test@example.com', 'status' => 'sent', 'embed_src' => 'https://docuseal.com/s/xyz'],
        ], 200)]);

        $sent = app(AdmissionController::class)->sendDocusealForm($applicant->id);

        $this->assertSame(555001, $sent['submission_id']);
        $row = ApplicantDocusealSubmission::where('submission_id', 555001)->first();
        $this->assertNotNull($row);
        $this->assertSame($applicant->id, (int) $row->applicant_id);
        $this->assertSame('waiting', $row->state);
        $this->assertSame('test@example.com', $row->applicant_email);
        $this->assertSame('https://docuseal.com/s/xyz', $row->signing_url);
        $this->assertSame($staff->id, (int) $row->sent_by);

        // The request DocuSeal received is the one the old Guzzle call made.
        Http::assertSent(function ($request) use ($applicant) {
            $body = json_decode($request->body(), true);

            return $request->method() === 'POST'
                && $request->url() === self::API.'/submissions'
                && $request->header('X-Auth-Token') === ['test-key']
                && str_starts_with($request->header('Content-Type')[0] ?? '', 'application/json')
                && $body['template_id'] === 999
                && $body['send_email'] === true
                && $body['order'] === 'preserved'
                && $body['submitters'][0]['role'] === 'London Churchill College'
                && $body['submitters'][0]['completed'] === true
                && collect($body['submitters'][0]['fields'])->firstWhere('name', 'application_ref_no')['default_value'] === $applicant->application_no
                && $body['submitters'][1]['role'] === 'Applicant';
        });
    }

    public function test_a_docuseal_error_on_send_is_logged_not_thrown(): void
    {
        $applicant = $this->applicant();
        Auth::guard('web')->setUser(User::where('active', 1)->first());
        Http::fake([self::API.'/submissions' => Http::response(['error' => 'Unauthorized'], 401)]);

        $this->assertFalse(app(AdmissionController::class)->sendDocusealForm($applicant->id));
        $this->assertSame(0, ApplicantDocusealSubmission::where('applicant_id', $applicant->id)->count());
    }

    public function test_an_unsigned_form_stays_waiting_and_stores_nothing(): void
    {
        $applicant = $this->applicant();
        $row = app(OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 555002, 'template_id' => 999], null);
        $this->fakeDocuseal([$this->submission(555002, 'pending')]);
        $before = ApplicantDocument::where('applicant_id', $applicant->id)->count();

        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();

        $row->refresh();
        $this->assertSame('waiting', $row->state);
        $this->assertSame('pending', $row->docuseal_status);
        $this->assertSame(1, (int) $row->checks);
        $this->assertSame($before, ApplicantDocument::where('applicant_id', $applicant->id)->count());
    }

    public function test_a_signed_form_is_stored_as_two_uploads_once(): void
    {
        $applicant = $this->applicant();
        $row = app(OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 555003, 'template_id' => 999], null);
        $this->fakeDocuseal([$this->submission(555003, 'completed')]);

        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();

        $row->refresh();
        $this->assertSame('stored', $row->state);
        $this->assertNotNull($row->stored_at);
        $this->assertNotNull($row->completed_at);
        $offer = ApplicantDocument::find($row->offer_document_id);
        $audit = ApplicantDocument::find($row->audit_document_id);
        $type = \App\Models\DocumentSettings::where('name', 'Offer Acceptance / Audit Trail')->first();
        foreach ([[$offer, 'Offer Acceptance / Audit Trail - Offer Acceptance', '_Offer acceptance form.pdf'], [$audit, 'Offer Acceptance / Audit Trail - Audit Trail', '_Audit Log - Offer acceptance form.pdf']] as [$doc, $name, $suffix]) {
            $this->assertNotNull($doc);
            $this->assertSame($applicant->id, (int) $doc->applicant_id);
            $this->assertSame($type->id, (int) $doc->document_setting_id);
            $this->assertSame($name, $doc->display_file_name);
            $this->assertSame(0, (int) $doc->hard_copy_check);
            $this->assertSame('pdf', $doc->doc_type);
            $this->assertSame('local', $doc->disk_type);
            $this->assertSame((int) config('tasks.system_user_id', 1), (int) $doc->created_by);
            $this->assertStringEndsWith($suffix, $doc->current_file_name);
            Storage::disk('local')->assertExists('public/applicants/'.$applicant->id.'/'.$doc->current_file_name);
            $this->assertSame(self::PDF, Storage::disk('local')->get('public/applicants/'.$applicant->id.'/'.$doc->current_file_name));
        }

        // The uploads list shows them under their type.
        $staff = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $list = $this->actingAs($staff)->get(route('admission.uploads.list', ['applicantId' => $applicant->id]))->assertOk()->json('data');
        $names = collect($list)->pluck('display_file_name')->all();
        $this->assertContains('Offer Acceptance / Audit Trail - Offer Acceptance', $names);
        $this->assertContains('Offer Acceptance / Audit Trail - Audit Trail', $names);

        // A second run does nothing more.
        $count = ApplicantDocument::where('applicant_id', $applicant->id)->count();
        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();
        $this->assertSame($count, ApplicantDocument::where('applicant_id', $applicant->id)->count());
    }

    public function test_a_second_signed_form_for_the_same_applicant_is_skipped(): void
    {
        $applicant = $this->applicant();
        $svc = app(OfferAcceptanceDocuments::class);
        $first = $svc->record($applicant->id, ['submission_id' => 555004], null);
        $second = $svc->record($applicant->id, ['submission_id' => 555005], null);
        $this->fakeDocuseal([$this->submission(555004, 'completed'), $this->submission(555005, 'completed')]);

        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();

        $this->assertSame('stored', $first->fresh()->state);
        $this->assertSame('skipped', $second->fresh()->state);
        $this->assertSame(1, ApplicantDocusealSubmission::where('applicant_id', $applicant->id)->where('state', 'stored')->count());
    }

    public function test_a_download_that_is_not_a_pdf_fails_and_stores_nothing(): void
    {
        $applicant = $this->applicant();
        $row = app(OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 555006], null);
        $this->fakeDocuseal([$this->submission(555006, 'completed')], '<html>login</html>');
        $before = ApplicantDocument::where('applicant_id', $applicant->id)->count();

        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();

        $row->refresh();
        $this->assertSame('failed', $row->state);
        $this->assertSame(1, (int) $row->failures);
        $this->assertStringContainsString('did not return a PDF', $row->last_error);
        $this->assertSame($before, ApplicantDocument::where('applicant_id', $applicant->id)->count());
        $this->assertSame([], Storage::disk('local')->allFiles('public/applicants/'.$applicant->id));
        // Still retried next time.
        $this->assertTrue(ApplicantDocusealSubmission::open()->whereKey($row->id)->exists());
    }

    public function test_a_declined_form_stops_being_checked(): void
    {
        $applicant = $this->applicant();
        $row = app(OfferAcceptanceDocuments::class)->record($applicant->id, ['submission_id' => 555007], null);
        $this->fakeDocuseal([$this->submission(555007, 'declined')]);

        $this->artisan('docuseal:collect-signed-offers', ['--applicant' => $applicant->id])->assertSuccessful();

        $this->assertSame('declined', $row->fresh()->state);
        $this->assertFalse(ApplicantDocusealSubmission::open()->whereKey($row->id)->exists());
    }

    public function test_link_registers_an_earlier_form_and_checks_it(): void
    {
        $applicant = $this->applicant();
        $this->fakeDocuseal([$this->submission(555008, 'pending')]);

        $this->artisan('docuseal:collect-signed-offers', ['--link' => true, '--submission' => 555008, '--applicant' => $applicant->id])
            ->expectsOutputToContain('Linked submission 555008')
            ->assertSuccessful();

        $row = ApplicantDocusealSubmission::where('submission_id', 555008)->first();
        $this->assertNotNull($row);
        $this->assertSame($applicant->id, (int) $row->applicant_id);
        $this->assertSame('https://docuseal.com/s/abc', $row->signing_url);
        $this->assertSame('waiting', $row->state);
    }
}
