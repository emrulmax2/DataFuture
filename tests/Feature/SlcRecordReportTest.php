<?php

namespace Tests\Feature;

use App\Exports\ArrayCollectionExport;
use App\Http\Controllers\Reports\SlcReports\SlcRecordReportController;
use App\Models\User;
use App\Services\Reports\SlcRecord;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

/**
 * SLC Reports › SLC Record Report "New version" (user, 25 Nov 2026): the old report's figures
 * (refineResult) for LCC and franchise courses, intake and course rows; the student lists; the
 * Excel, student lists Excel and PDF; and the page's two tabs. Read-only against the dev database.
 */
class SlcRecordReportTest extends TestCase
{
    use DatabaseTransactions;

    /** @var int[] */
    private array $semesters;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::find(7));
        $this->semesters = DB::table('semesters')->whereNull('deleted_at')->orderByDesc('id')->limit(12)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    public function test_figures_match_the_old_report(): void
    {
        $old = ['lcc' => app(SlcRecordReportController::class)->refineResult($this->semesters, false), 'franchise' => app(SlcRecordReportController::class)->refineResult($this->semesters, true)];
        $new = SlcRecord::sets($this->semesters);
        $map = ['lcc' => 'slc_sms_registered', 'awb' => 'slc_awb_registered', 'slc' => 'year_1_registered', 'att' => 'year_1_attendance', 'withdrawn' => 'slc_withdrawn', 'intermittent' => 'slc_interminate', 'self' => 'slc_self_funded'];
        foreach ($old as $set => $rows) {
            foreach ($rows as $semester => $o) {
                foreach ($map as $key => $oldKey) {
                    $this->assertSame((int) $o[$oldKey], count($new[$semester]['sets'][$set]['all'][$key]), "$set {$o['name']} $key");
                }
                foreach ($o['courses'] ?? [] as $creation => $oc) {
                    foreach ($map as $key => $oldKey) {
                        $this->assertSame((int) $oc[$oldKey], count($new[$semester]['sets'][$set]['courses'][$creation]['ids'][$key]), "$set {$o['name']} / {$oc['name']} $key");
                    }
                }
                $all = $new[$semester]['sets'][$set]['all'];
                $this->assertSame(array_values(array_diff($all['lcc'], $all['slc'])), $all['gap'], 'not yet SLC registered = registered at LCC, not SLC registered');
            }
        }
    }

    public function test_a_student_list_adds_up_to_its_count(): void
    {
        $data = SlcRecord::build($this->semesters);
        $intake = collect($data['intakes'])->sortByDesc(fn ($i) => $i['sets']['lcc']['v'][0])->first();
        if (!$intake || !$intake['sets']['lcc']['v'][0]) {
            $this->markTestSkipped('No intake with students.');
        }
        $url = fn ($course, $key) => route('reports.slc.record.new.students', ['semester' => $intake['id'], 'set' => 'lcc', 'course' => $course, 'key' => $key]);
        foreach (array_keys(SlcRecord::KEYS) as $k => $key) {
            $this->assertCount($intake['sets']['lcc']['v'][$k], $this->getJson($url('all', $key))->assertOk()->json('rows'), $key);
        }
        $course = $intake['sets']['lcc']['courses'][0];
        $this->assertCount($course['v'][0], $this->getJson($url($course['id'], 'lcc'))->json('rows'));
        $this->getJson($url('all', 'everyone'))->assertStatus(422);
        $this->getJson($url(99999999, 'lcc'))->assertNotFound();
    }

    public function test_the_screen_json_excel_details_and_pdf(): void
    {
        $ids = implode('_', array_slice($this->semesters, 0, 4));
        $data = $this->getJson(route('reports.slc.record.new.data', $ids))->assertOk()
            ->assertJsonStructure(['keys', 'names', 'sets' => ['lcc', 'franchise'], 'intakes' => [['id', 'name', 'sets' => ['lcc' => ['v', 'courses'], 'franchise' => ['v', 'courses']]]]])
            ->json();
        $this->getJson(route('reports.slc.record.new.data', '99999999'))->assertNotFound();

        Excel::fake();
        $this->get(route('reports.slc.record.new.excel', $ids).'?set=lcc')->assertOk();
        $names = implode('_', array_map(fn ($i) => $i['name'], array_slice($data['intakes'], 0, 3))).(count($data['intakes']) > 3 ? '_and_more' : '');
        $file = 'SLC_Record_'.trim(preg_replace('/[^A-Za-z0-9]+/', '_', $names), '_').'.xlsx';
        Excel::assertDownloaded($file, fn (ArrayCollectionExport $export) => count($export->array()) === 1 + count($data['intakes'])
            + array_sum(array_map(fn ($i) => count($i['sets']['lcc']['courses']), $data['intakes'])) + (count($data['intakes']) > 1 ? 1 : 0));
        $this->get(route('reports.slc.record.new.details', $data['intakes'][0]['id']).'?set=franchise')->assertOk();
        $this->get(route('reports.slc.record.new.excel', $ids).'?set=other')->assertStatus(302);

        $pdf = $this->get(route('reports.slc.record.new.pdf', $ids).'?set=lcc');
        $pdf->assertOk();
        $this->assertSame('application/pdf', $pdf->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
    }

    public function test_the_page_has_both_versions(): void
    {
        $this->get(route('reports.slc.index'))->assertOk()
            ->assertSee('data-srr', false)
            ->assertSee('id="srrPaneNew"', false)
            ->assertSee('id="slcRecoredReportForm"', false);
    }
}
