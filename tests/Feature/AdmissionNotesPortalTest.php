<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Models\ApplicantDocument;
use App\Models\ApplicantNote;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * The applicant notes page on the admission portal shell: composer, timeline
 * list, edit, delete and restore, with attachments on the local disk here.
 */
class AdmissionNotesPortalTest extends TestCase
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
        $applicant = Applicant::whereNotNull('submission_date')->orderBy('id', 'desc')->first();
        $this->assertNotNull($applicant);

        return $applicant;
    }

    public function test_the_notes_page_renders_composer_and_timeline(): void
    {
        $applicant = $this->applicant();

        $response = $this->actingAs($this->staff())->get(route('admission.notes', $applicant->id));

        $response->assertOk()
            ->assertSee('id="addNoteForm"', false)
            ->assertSee('data-adp-timeline', false)
            ->assertSee('id="addEditor"', false)
            ->assertSee('data-adp-editing', false)
            ->assertDontSee('editNoteModal')
            ->assertSee('id="changeStatusModal"', false)
            ->assertDontSee('editAdmissionPersonalDetailsModal')
            ->assertDontSee('document-editor__toolbar');
    }

    public function test_a_note_with_an_attachment_is_stored_and_listed_for_the_timeline(): void
    {
        Storage::fake('local');
        $applicant = $this->applicant();
        $staff = $this->staff();
        $html = '<p>Called the applicant about <b>proof of address</b>.</p>';

        $store = $this->actingAs($staff)->post(route('admission.store.note'), [
            'applicant_id' => $applicant->id,
            'content' => $html,
            'document' => UploadedFile::fake()->create('call-log.pdf', 6, 'application/pdf'),
        ]);
        $store->assertOk();
        $noteId = (int) $store->json('id');
        $note = ApplicantNote::find($noteId);
        $this->assertNotNull($note);
        $this->assertSame($html, $note->note);
        $this->assertGreaterThan(0, (int) $note->applicant_document_id);
        $doc = ApplicantDocument::find($note->applicant_document_id);
        $this->assertSame('local', $doc->disk_type);
        Storage::disk('local')->assertExists('public/applicants/'.$applicant->id.'/'.$doc->current_file_name);

        $list = $this->actingAs($staff)->getJson(route('admission.note.list', ['applicantId' => $applicant->id, 'status' => 1, 'size' => 10, 'page' => 1]));
        $list->assertOk();
        $this->assertGreaterThanOrEqual(1, (int) $list->json('total_rows'));
        $row = collect($list->json('data'))->firstWhere('id', $noteId);
        $this->assertNotNull($row);
        $this->assertSame($html, $row['note_html']);
        $this->assertSame((int) $note->applicant_document_id, (int) $row['applicant_document_id']);
        $this->assertStringContainsString('call-log.pdf', $row['document_name']);
        $this->assertStringContainsString('proof of address', $row['excerpt']);

        $get = $this->actingAs($staff)->postJson(route('admission.get.note'), ['noteId' => $noteId]);
        $get->assertOk();
        $this->assertStringContainsString('/storage/applicants/'.$applicant->id.'/', $get->json('res.docURL'));
    }

    public function test_an_empty_note_is_rejected(): void
    {
        $applicant = $this->applicant();

        $this->actingAs($this->staff())->postJson(route('admission.store.note'), ['applicant_id' => $applicant->id, 'content' => ''])
            ->assertStatus(422)->assertJsonValidationErrors(['content']);
    }

    public function test_delete_and_restore_move_a_note_between_the_lists(): void
    {
        $applicant = $this->applicant();
        $staff = $this->staff();
        $note = ApplicantNote::create(['applicant_id' => $applicant->id, 'note' => '<p>Portal test note</p>', 'phase' => 'Admission', 'created_by' => $staff->id]);

        $this->actingAs($staff)->deleteJson(route('admission.destory.note'), ['applicant' => $applicant->id, 'recordid' => $note->id])->assertOk();
        $this->assertNotNull(ApplicantNote::withTrashed()->find($note->id)->deleted_at);

        $deleted = $this->actingAs($staff)->getJson(route('admission.note.list', ['applicantId' => $applicant->id, 'status' => 2, 'size' => 'true', 'page' => 1]));
        $row = collect($deleted->json('data'))->firstWhere('id', $note->id);
        $this->assertNotNull($row, 'The deleted note should be in the deleted list.');
        $this->assertNotEmpty($row['deleted_at']);

        $this->actingAs($staff)->postJson(route('admission.resotore.note'), ['applicant' => $applicant->id, 'recordid' => $note->id])->assertOk();
        $this->assertNull(ApplicantNote::find($note->id)->deleted_at);
    }
}
