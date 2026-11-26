<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\StudentNote;
use App\Models\TermDeclaration;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Student notes page redesign (25 Nov 2026): the timeline/table card draws from
 * student.note.list with size=true, so the list must still return every field
 * it did before plus the readable text, ISO dates, term start and follower
 * names the new card uses — and the browser-supplied sorters must not reach
 * ORDER BY unchecked. Runs against the dev database inside a transaction that
 * is rolled back; nothing it creates survives.
 */
class StudentNotesPageTest extends TestCase
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

    private function note(array $attrs = []): StudentNote
    {
        return StudentNote::create(array_merge([
            'student_id' => $this->student->id,
            'term_declaration_id' => TermDeclaration::query()->orderByDesc('id')->value('id'),
            'opening_date' => '2026-10-06',
            'note' => '<p>First line</p><p>Second &amp; last</p>',
            'phase' => 'Live',
            'followed_up' => 'no',
            'is_flaged' => 'No',
            'student_flag_id' => 0,
            'created_by' => $this->staff->id,
        ], $attrs));
    }

    private function list(array $params = []): array
    {
        return $this->getJson(route('student.note.list', array_merge(['studentId' => $this->student->id, 'size' => 'true'], $params)))
            ->assertOk()->json();
    }

    public function test_list_keeps_the_old_fields_and_adds_what_the_card_needs(): void
    {
        $note = $this->note([
            'followed_up' => 'yes',
            'followed_up_status' => 'Pending',
            'is_flaged' => 'Yes',
            'student_flag_id' => DB::table('student_flags')->value('id') ?? 1,
            'flaged_status' => 'Active',
        ]);
        DB::table('student_note_followed_bies')->insert(['student_note_id' => $note->id, 'user_id' => $this->staff->id, 'created_by' => $this->staff->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('student_note_followup_comment_reads')->insert([
            ['student_note_id' => $note->id, 'student_note_followup_comment_id' => 0, 'user_id' => $this->staff->id, 'read' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['student_note_id' => $note->id, 'student_note_followup_comment_id' => 0, 'user_id' => $this->staff->id, 'read' => 0, 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('student_notes_documents')->insert(['student_id' => $this->student->id, 'student_note_id' => $note->id, 'hard_copy_check' => 0, 'doc_type' => 'pdf', 'path' => 'x', 'display_file_name' => '1792684338_letter.pdf', 'current_file_name' => '1792684338_letter.pdf', 'created_by' => $this->staff->id, 'created_at' => now(), 'updated_at' => now()]);

        $json = $this->list();
        $row = collect($json['data'])->firstWhere('id', $note->id);
        $this->assertNotNull($row);
        $this->assertSame(count($json['data']), $json['total_rows']);

        // every key the old Tabulator list read is still there
        foreach (['id', 'sl', 'term', 'opening_date', 'note', 'note_document_id', 'followed_up', 'followed_up_status', 'followed', 'completed_by', 'completed_at',
                  'am_i_followed', 'unread_comment', 'is_flaged', 'flaged_status', 'student_flag_id', 'flag_name', 'flag_color', 'created_by', 'created_at', 'deleted_at', 'is_ownere'] as $key) {
            $this->assertArrayHasKey($key, $row, $key);
        }
        $this->assertSame(1, $row['am_i_followed']);
        $this->assertSame(2, $row['unread_comment']);
        $this->assertSame(1, $row['is_ownere']);
        $this->assertGreaterThan(0, (int) $row['note_document_id']);

        // the new ones
        $this->assertSame("First line\nSecond & last", $row['note_text']);
        $this->assertFalse($row['note_rich']);
        $this->assertSame('2026-10-06', $row['date_iso']);
        $this->assertSame('letter.pdf', $row['document_name']);
        $this->assertCount(1, $row['followed_names']);
        $this->assertStringContainsString(e($row['followed_names'][0]), $row['followed']);
        $term = TermDeclaration::find($note->term_declaration_id);
        $this->assertSame($term->name, $row['term']);
        $this->assertSame(date('Y-m-d', strtotime($term->getRawOriginal('start_date'))), $row['term_start']);
    }

    public function test_lists_and_tables_survive_as_text_and_are_marked_rich(): void
    {
        $note = $this->note(['note' => '<p>Done:</p><ul><li>Form uploaded</li><li>Status changed</li></ul><table><tr><td>A</td><td>B</td></tr></table>']);
        $row = collect($this->list()['data'])->firstWhere('id', $note->id);
        $this->assertTrue($row['note_rich']);
        $this->assertStringContainsString("Done:\n• Form uploaded\n• Status changed", $row['note_text']);
        $this->assertStringContainsString('A  B', $row['note_text']);
    }

    public function test_archived_status_returns_only_deleted_notes(): void
    {
        $kept = $this->note();
        $gone = $this->note();
        $gone->delete();

        $active = collect($this->list(['status' => 1])['data'])->pluck('id');
        $archived = collect($this->list(['status' => 2])['data']);
        $this->assertTrue($active->contains($kept->id));
        $this->assertFalse($active->contains($gone->id));
        $this->assertTrue($archived->pluck('id')->contains($gone->id));
        $this->assertNotNull($archived->firstWhere('id', $gone->id)['deleted_at']);
    }

    public function test_sorters_are_whitelisted(): void
    {
        $a = $this->note();
        $b = $this->note();

        // an expression where a column belongs: ignored, not run
        $rows = $this->list(['sorters' => [['field' => 'id) OR SLEEP(5', 'dir' => 'ASC']]])['data'];
        $this->assertNotEmpty($rows);

        $asc = collect($this->list(['sorters' => [['field' => 'id', 'dir' => 'asc']]])['data'])->pluck('id')->all();
        $this->assertLessThan(array_search($b->id, $asc), array_search($a->id, $asc));

        // anything but ASC is DESC, so a tacked-on statement goes nowhere
        $junk = collect($this->list(['sorters' => [['field' => 'id', 'dir' => 'asc; DROP']]])['data'])->pluck('id')->all();
        $this->assertLessThan(array_search($a->id, $junk), array_search($b->id, $junk));
        $desc = collect($this->list()['data'])->pluck('id')->all();
        $this->assertLessThan(array_search($a->id, $desc), array_search($b->id, $desc));
    }

    public function test_page_renders_the_new_card(): void
    {
        Http::fake(['*' => Http::response(['data' => []], 200)]);

        $this->get(route('student.notes', $this->student->id))
            ->assertOk()
            ->assertSee('id="studentNotesApp"', false)
            ->assertSee('data-student="'.$this->student->id.'"', false)
            ->assertSee('id="addNoteModal"', false)
            ->assertSee('id="editNoteModal"', false)
            ->assertSee('id="followUpCommentModal"', false)
            ->assertSee('id="viewNoteModal"', false)
            ->assertDontSee('id="studentNotesListTable"', false);
    }
}
