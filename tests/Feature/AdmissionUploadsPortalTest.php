<?php

namespace Tests\Feature;

use App\Models\ApplicantDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * The applicant uploads page on the admission portal shell: type chips and
 * picker, the document list with its type, and the deleted list.
 */
class AdmissionUploadsPortalTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(): User
    {
        $user = User::where('email', 'sakib@lcc.ac.uk')->first() ?? User::where('active', 1)->first();
        $this->assertNotNull($user, 'A staff user is needed for this test.');

        return $user;
    }

    private function document(): ApplicantDocument
    {
        $doc = ApplicantDocument::orderBy('id', 'desc')->first();
        $this->assertNotNull($doc, 'An applicant document is needed for this test.');

        return $doc;
    }

    public function test_the_uploads_page_renders_chips_picker_and_list(): void
    {
        $doc = $this->document();

        $response = $this->actingAs($this->staff())->get(route('admission.uploads', $doc->applicant_id));

        $response->assertOk()
            ->assertSee('id="applicantUploadListTable"', false)
            ->assertSee('class="adp-doc-chip', false)
            ->assertSee('id="docTypeModal"', false)
            ->assertSee('class="adp-doc-card', false)
            ->assertSee('id="uploadDocumentForm"', false)
            ->assertSee('id="applicantDeletedListTable"', false)
            ->assertSee('id="changeStatusModal"', false)
            ->assertDontSee('editAdmissionPersonalDetailsModal');
    }

    public function test_the_list_carries_the_document_type(): void
    {
        $doc = $this->document();

        $response = $this->actingAs($this->staff())->getJson(route('admission.uploads.list', ['applicantId' => $doc->applicant_id, 'status' => 1, 'size' => 50, 'page' => 1]));

        $response->assertOk();
        $row = collect($response->json('data'))->firstWhere('id', $doc->id);
        $this->assertNotNull($row, 'The document should be listed.');
        foreach (['document_setting_id', 'document_type', 'created_by', 'created_at', 'hard_copy_check', 'current_file_name'] as $key) {
            $this->assertArrayHasKey($key, $row);
        }
        $this->assertSame((string) ($doc->documentSetting->name ?? ''), (string) $row['document_type']);
    }

    public function test_delete_and_restore_move_a_document_between_the_lists(): void
    {
        $doc = $this->document();
        $staff = $this->staff();

        $this->actingAs($staff)->deleteJson(route('admission.destory.uploads'), ['applicant' => $doc->applicant_id, 'recordid' => $doc->id])->assertOk();
        $this->assertNotNull(ApplicantDocument::withTrashed()->find($doc->id)->deleted_at);

        $deleted = $this->actingAs($staff)->getJson(route('admission.uploads.list', ['applicantId' => $doc->applicant_id, 'status' => 2, 'size' => 50, 'page' => 1]));
        $row = collect($deleted->json('data'))->firstWhere('id', $doc->id);
        $this->assertNotNull($row, 'The deleted document should be in the deleted list.');
        $this->assertNotEmpty($row['deleted_at']);

        $this->actingAs($staff)->postJson(route('admission.resotore.uploads'), ['applicant' => $doc->applicant_id, 'recordid' => $doc->id])
            ->assertOk()->assertJson(['restored' => true]);
        $this->assertNull(ApplicantDocument::find($doc->id)->deleted_at);
    }
}
