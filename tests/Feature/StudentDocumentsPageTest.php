<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Student documents page redesign (25 Nov 2026): the Documents card draws from
 * student.uploads.list with size=true and the access log from
 * student.uploads.access.log.list page by page. The list must still return
 * every field it did before (plus a sortable date), the browser-supplied
 * sorters must not reach ORDER BY unchecked, and Restore now answers with JSON.
 * Runs against the dev database inside a transaction that is rolled back;
 * nothing it creates survives.
 */
class StudentDocumentsPageTest extends TestCase
{
    use DatabaseTransactions;

    private Student $student;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = Student::query()->orderByDesc('id')->firstOrFail();
        $this->staff = User::find(7) ?? User::where('active', 1)->firstOrFail();
        $this->actingAs($this->staff);
    }

    private function document(array $attrs = []): StudentDocument
    {
        return StudentDocument::create(array_merge([
            'student_id' => $this->student->id,
            'document_setting_id' => 0,
            'hard_copy_check' => 1,
            'doc_type' => 'pdf',
            'path' => 'https://example.test/x.pdf',
            'display_file_name' => 'Proof of ID - Passport',
            'current_file_name' => '1792684338_passport.pdf',
            'is_encrypted' => 0,
            'created_by' => $this->staff->id,
        ], $attrs));
    }

    private function list(array $params = []): array
    {
        return $this->getJson(route('student.uploads.list', array_merge(['studentId' => $this->student->id, 'size' => 'true'], $params)))
            ->assertOk()->json();
    }

    public function test_list_keeps_the_old_fields_and_adds_a_sortable_date(): void
    {
        $doc = $this->document(['is_encrypted' => 1, 'doc_type' => 'docx']);

        $json = $this->list();
        $this->assertSame(count($json['data']), $json['total_rows']);
        $row = collect($json['data'])->firstWhere('id', $doc->id);
        $this->assertNotNull($row);

        foreach (['id', 'sl', 'display_file_name', 'hard_copy_check', 'doc_type', 'current_file_name', 'created_by', 'created_at',
                  'deleted_at', 'can_delete', 'is_encrypted', 'viewable'] as $key) {
            $this->assertArrayHasKey($key, $row, $key);
        }
        $this->assertSame('Proof of ID - Passport', $row['display_file_name']);
        $this->assertSame('DOCX', $row['doc_type']);
        $this->assertSame(1, $row['is_encrypted']);
        $this->assertSame(0, $row['viewable']); // a Word file cannot be shown in the browser
        $this->assertSame($this->staff->name, $row['created_by']);
        $this->assertSame($doc->created_at->format('Y-m-d'), $row['created_iso']);
    }

    public function test_archived_status_returns_only_deleted_documents_and_restore_answers(): void
    {
        $kept = $this->document();
        $gone = $this->document();
        $gone->delete();

        $active = collect($this->list(['status' => 1])['data'])->pluck('id');
        $archived = collect($this->list(['status' => 2])['data'])->pluck('id');
        $this->assertTrue($active->contains($kept->id));
        $this->assertFalse($active->contains($gone->id));
        $this->assertTrue($archived->contains($gone->id));

        $this->postJson(route('student.resotore.uploads'), ['student' => $this->student->id, 'recordid' => $gone->id])->assertOk();
        $this->assertTrue(collect($this->list(['status' => 1])['data'])->pluck('id')->contains($gone->id));
    }

    public function test_sorters_are_whitelisted(): void
    {
        $a = $this->document();
        $b = $this->document();

        $this->assertNotEmpty($this->list(['sorters' => [['field' => 'id) OR SLEEP(5', 'dir' => 'ASC']]])['data']);

        $asc = collect($this->list(['sorters' => [['field' => 'id', 'dir' => 'asc']]])['data'])->pluck('id')->all();
        $this->assertLessThan(array_search($b->id, $asc), array_search($a->id, $asc));
        $junk = collect($this->list(['sorters' => [['field' => 'id', 'dir' => 'asc; DROP']]])['data'])->pluck('id')->all();
        $this->assertLessThan(array_search($a->id, $junk), array_search($b->id, $junk));
    }

    public function test_access_log_pages_on_the_server_and_carries_an_iso_date(): void
    {
        $doc = $this->document();
        foreach (range(1, 12) as $i) {
            DB::table('student_document_access_logs')->insert([
                'student_document_id' => $doc->id, 'student_id' => $this->student->id, 'user_id' => $this->staff->id,
                'event' => $i === 12 ? 'pin_failed' : 'download', 'is_encrypted' => 0, 'document_name' => $doc->display_file_name,
                'ip_address' => '127.0.0.1', 'created_at' => '2026-11-0'.($i % 9 + 1).' 10:00:00', 'updated_at' => now(),
            ]);
        }
        $url = fn (array $p) => route('student.uploads.access.log.list', array_merge(['studentId' => $this->student->id], $p));

        $page1 = $this->getJson($url(['page' => 1, 'size' => 10]))->assertOk()->json();
        $total = $page1['total_rows'];
        $this->assertGreaterThanOrEqual(12, $total);
        $this->assertCount(10, $page1['data']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $page1['data'][0]['date_iso']);
        foreach (['event', 'tone', 'document', 'is_encrypted', 'user', 'signed_in_as', 'ip_address', 'date', 'time'] as $key) {
            $this->assertArrayHasKey($key, $page1['data'][0], $key);
        }

        $all = $this->getJson($url(['size' => 'true']))->assertOk()->json();
        $this->assertCount($total, $all['data']);

        $blocked = collect($this->getJson($url(['event' => 'blocked', 'size' => 'true']))->assertOk()->json('data'));
        $this->assertNotEmpty($blocked);
        $this->assertTrue($blocked->every(fn ($r) => $r['tone'] === 'danger'));
    }

    public function test_page_renders_the_new_cards_and_keeps_its_dialogs(): void
    {
        $response = $this->get(route('student.uploads', $this->student->id))->assertOk()
            ->assertSee('id="studentDocsApp"', false)
            ->assertSee('id="studentDocLogApp"', false)
            ->assertSee('data-vault-pin-url=', false)
            ->assertSee('id="downloadIDCardBtn"', false)
            ->assertSee('id="uploadDocumentModal"', false)
            ->assertSee('id="documentPinModal"', false)
            ->assertSee('id="downloadIDCard"', false)
            ->assertSee('id="warningModal"', false)
            ->assertDontSee('id="studentUploadListTable"', false);

        // Add Document lists the live document types for staff who may add.
        if (($this->staff->priv()['document_add'] ?? 0) == 1) {
            $response->assertSee('id="sdcAddMenu"', false);
            foreach (DB::table('document_settings')->where('live', '1')->whereNull('deleted_at')->pluck('id') as $id) {
                $response->assertSee('data-doc-setting="'.$id.'"', false);
            }
        }
    }
}
