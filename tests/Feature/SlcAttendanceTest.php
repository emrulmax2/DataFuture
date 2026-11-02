<?php

namespace Tests\Feature;

use App\Models\SlcAttendanceImportRow;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\User;
use App\Services\SlcRegistration\AttendanceFile;
use App\Services\SlcRegistration\RegistrationState;
use App\Services\SlcRegistration\StudentSnapshots;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * /slc-registration › Bulk attendance (user, 2 Nov 2026): upload the SIS Attendance Confirmation
 * Report, match students by SSN, the Data check, the register in the liability period, codes, CoCs and
 * the batch's SIS file. The report is built from this install's students in the layout of the user's
 * sample; everything runs in a rolled-back transaction.
 */
class SlcAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    private string $text;
    private array $withSsn = [];
    private ?array $noSsn = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->actingAs(User::find(7));
        $year = RegistrationState::academicYear(now()->subMonths(7)->format('y'));
        $ids = DB::table('students as s')->join('student_course_relations as r', fn ($j) => $j->on('r.student_id', '=', 's.id')->where('r.active', 1)->whereNull('r.deleted_at'))
            ->join('course_creation_instances as i', fn ($j) => $j->on('i.course_creation_id', '=', 'r.course_creation_id')->where('i.academic_year_id', $year?->id))
            ->whereNull('s.deleted_at')->whereNotNull('s.ssn_no')->where('s.ssn_no', '!=', '')->orderByDesc('s.id')->limit(200)->pluck('s.id')->all();
        $this->withSsn = array_slice(array_values(array_filter(StudentSnapshots::load($ids, $year?->id), fn ($s) => $s['slc_code'] !== '' && $s['instance'] && $s['dob']
            && DB::table('students')->where('ssn_no', $s['ssn'])->whereNull('deleted_at')->count() === 1)), 0, 5);
        $noSsnIds = DB::table('students')->whereNull('deleted_at')->where(fn ($q) => $q->whereNull('ssn_no')->orWhere('ssn_no', ''))->whereNotNull('date_of_birth')->orderByDesc('id')->limit(30)->pluck('id')->all();
        $this->noSsn = collect(StudentSnapshots::load($noSsnIds, $year?->id))->first(fn ($s) => $s['first'] !== '' && $s['last'] !== '' && $s['dob']);
        if (count($this->withSsn) < 3 || !$this->noSsn) {
            $this->markTestSkipped('Too few students to build an attendance report.');
        }
        $line = fn ($ssn, $s) => implode('~', ['AUT', $ssn, strtoupper($s['last']), strtoupper($s['first']), date('dmY', strtotime($s['dob'])), '', $s['slc_code'] ?: '134138', 'COURSE SEP I...ENG-NI-SCO', (string) ($s['instance']['rank'] ?? 1), '6350.00', '6350.00', '0.00', '0.00', 'F', now()->format('dmY'), 'NEW', 'N', 'N', 'N', 'N', '']);
        $lines = array_map(fn ($s) => $line($s['ssn'], $s), $this->withSsn);
        $lines[] = $line('SFTA26900000X', $this->noSsn);                                                  // a student we hold without an SSN
        $lines[] = 'AUT~SFTA26900001Y~NOBODY~TESTA~01011990~~134138~COURSE~1~6350.00~0.00~0.00~6350.00~F~01092026~NEW~N~N~N~N~'; // nobody
        $this->text = implode("\r\n", array_merge(['LCHC~LONDON CHURCHILL COLLEGE~'.now()->format('dmY').'~'.count($lines).'~0.00~0.00~0.00'], $lines))."\r\n";
    }

    private function upload(?string $text = null)
    {
        $path = sys_get_temp_dir().'/att-'.md5($text ?? $this->text).'.txt';
        file_put_contents($path, $text ?? $this->text);

        return $this->post(route('slc.registration.att.upload'), ['file' => new UploadedFile($path, 'Attendance Confirmation Report.txt', 'text/plain', null, true)], ['Accept' => 'application/json']);
    }

    public function test_the_report_is_read_and_students_are_matched_by_ssn(): void
    {
        $n = count($this->withSsn);
        $res = $this->upload()->assertOk();
        $this->assertSame('Uploaded '.($n + 2).' students. '.$n.' matched by SSN, 2 to match.', $res->json('flash.text'));
        $att = $res->json('state.attendance');
        $this->assertSame(['all' => $n + 2, 'matched' => $n, 'unmatched' => 2, 'ssn' => $n, 'linked' => 0], array_intersect_key($att['counts'], array_flip(['all', 'matched', 'unmatched', 'ssn', 'linked'])));
        $this->assertSame(['LCHC', 'LONDON CHURCHILL COLLEGE', now()->toDateString(), 'AUT', 'Autumn'], [$att['import']['hei'], $att['import']['heiName'], $att['import']['reportDate'], $att['import']['term'], $att['import']['termName']]);
        $this->assertNotNull($att['import']['year']);

        $first = collect($att['rows'])->firstWhere('r.ssn', $this->withSsn[0]['ssn']);
        $this->assertSame([$this->withSsn[0]['id'], 'ssn', true, true], [$first['s']['sid'], $first['link']['method'], $first['s']['courseOk'], $first['s']['yearOk']]);
        $this->assertSame(route('student.show', $this->withSsn[0]['id']), $first['s']['url']);
        // the Attendance column: the register as Pre-confirm shows it (physical + online + other = attended)
        $a = $first['s']['att'];
        $this->assertContains($a['st'], ['ok', 'warn', 'bad']);
        $this->assertSame($a['a']['attended'], $a['a']['physical'] + $a['a']['online'] + $a['a']['other']);

        // the student we hold without an SSN is suggested first; nobody gets no suggestion worth taking
        $mine = collect($att['rows'])->firstWhere('r.ssn', 'SFTA26900000X');
        $this->assertSame($this->noSsn['id'], $mine['cands'][0]['s']['sid']);
        $this->assertGreaterThanOrEqual(85, $mine['cands'][0]['pct']);
        $this->assertNull(collect($att['rows'])->firstWhere('r.ssn', 'SFTA26900001Y')['s']);
    }

    public function test_save_ssn_matches_the_line_and_unmatch_takes_it_back(): void
    {
        $att = $this->upload()->json('state.attendance');
        $row = collect($att['rows'])->firstWhere('r.ssn', 'SFTA26900000X');
        $sid = $this->noSsn['id'];

        $res = $this->postJson(route('slc.registration.att.link'), ['row_id' => $row['id'], 'student_id' => $sid])->assertOk();
        $this->assertSame('SFTA26900000X', Student::find($sid)->ssn_no);
        $this->assertTrue(StudentArchive::where('student_id', $sid)->where('field_name', 'ssn_no')->where('field_new_value', 'SFTA26900000X')->exists());
        $this->assertSame(1, $res->json('state.attendance.counts.linked'));
        $this->postJson(route('slc.registration.att.link'), ['row_id' => $row['id'], 'student_id' => $sid])->assertStatus(422);
        // an SSN already held by someone else is refused
        $other = collect($att['rows'])->firstWhere('r.ssn', 'SFTA26900001Y');
        $this->postJson(route('slc.registration.att.link'), ['row_id' => $other['id'], 'student_id' => $this->withSsn[0]['id']])->assertStatus(422);

        $this->postJson(route('slc.registration.att.unlink'), ['row_id' => $row['id']])->assertOk();
        $this->assertNull(Student::find($sid)->ssn_no);
        $this->assertNull(SlcAttendanceImportRow::find($row['id'])->student_id);
        // a line matched by SSN (not here) cannot be unmatched
        $byssn = collect($att['rows'])->firstWhere('r.ssn', $this->withSsn[0]['ssn']);
        $this->postJson(route('slc.registration.att.unlink'), ['row_id' => $byssn['id']])->assertStatus(422);
    }

    public function test_the_data_check(): void
    {
        [$a, $b] = [$this->withSsn[0], $this->withSsn[1]];
        $yearId = (int) \App\Models\AcademicYear::where('from_date', '<=', now()->toDateString())->where('to_date', '>=', now()->toDateString())->value('id');
        // A: an SLC registration for the year whose agreement says £6,000 (the line says £6,350); B: a different date of birth on the line
        \App\Models\SlcRegistration::where('student_id', $a['id'])->where('academic_year_id', $yearId)->delete();
        $reg = \App\Models\SlcRegistration::create(['student_id' => $a['id'], 'student_course_relation_id' => $a['crel_id'], 'course_creation_instance_id' => $a['instance']['id'], 'ssn' => $a['ssn'], 'confirmation_date' => now()->toDateString(), 'academic_year_id' => $yearId, 'registration_year' => $a['instance']['rank'], 'slc_registration_status_id' => 1, 'note' => '', 'created_by' => 7]);
        \App\Models\SlcAgreement::create(['student_id' => $a['id'], 'student_course_relation_id' => $a['crel_id'], 'course_creation_instance_id' => $a['instance']['id'], 'slc_registration_id' => $reg->id, 'slc_coursecode' => $a['slc_code'], 'is_self_funded' => 0, 'date' => now()->toDateString(), 'year' => $a['instance']['rank'], 'fees' => 6000, 'commission_amount' => 0, 'total' => 6000, 'created_by' => 7]);
        $lines = explode("\r\n", rtrim($this->text));
        $f = explode('~', $lines[2]);
        $f[4] = '01011901';
        $lines[2] = implode('~', $f);
        $att = $this->upload(implode("\r\n", $lines)."\r\n")->assertOk()->json('state.attendance');
        $row = fn ($att, $ssn) => collect($att['rows'])->firstWhere('r.ssn', $ssn);
        $check = fn ($att, $ssn, $key) => collect($row($att, $ssn)['checks'])->firstWhere('key', $key);

        // A: registered — fee and TFL checked against the agreement (CoC suggested), the rest matches
        $this->assertTrue($check($att, $a['ssn'], 'registration')['ok']);
        $this->assertSame([false, 'coc'], [$check($att, $a['ssn'], 'fee')['ok'], $check($att, $a['ssn'], 'fee')['sugg']]);
        $this->assertSame([false, 'coc'], [$check($att, $a['ssn'], 'tfl')['ok'], $check($att, $a['ssn'], 'tfl')['sugg']]);
        foreach (['first', 'last', 'dob', 'ssn', 'course', 'year'] as $k) {
            $this->assertTrue($check($att, $a['ssn'], $k)['ok'], $k);
        }
        // B: no registration recorded (only "No action"), so no fee/TFL check; the date of birth differs (no suggestion)
        $this->assertFalse($check($att, $b['ssn'], 'registration')['ok']);
        $this->assertNull($check($att, $b['ssn'], 'fee'));
        $this->assertSame([false, null], [$check($att, $b['ssn'], 'dob')['ok'], $check($att, $b['ssn'], 'dob')['sugg']]);
        $this->assertSame('data', $row($att, $b['ssn'])['stage']);
        $rb = $row($att, $b['ssn'])['id'];
        $this->postJson(route('slc.registration.att.route'), ['row_id' => $rb, 'key' => 'registration', 'route' => 'coc'])->assertStatus(422);

        // Update our record (date of birth), with the history entry
        $att = $this->postJson(route('slc.registration.att.route'), ['row_id' => $rb, 'key' => 'dob', 'route' => 'update'])->assertOk()->json('state.attendance');
        $this->assertSame('1901-01-01', substr((string) Student::find($b['id'])->getRawOriginal('date_of_birth'), 0, 10));
        $this->assertTrue(StudentArchive::where('student_id', $b['id'])->where('field_name', 'date_of_birth')->exists());
        $this->assertTrue($check($att, $b['ssn'], 'dob')['ok']);

        // Apply suggested for one field (grouped by issue): only the fee is decided
        $att = $this->postJson(route('slc.registration.att.suggested'), ['row_ids' => [$row($att, $a['ssn'])['id']], 'key' => 'fee'])->assertOk()->json('state.attendance');
        $this->assertSame(['coc', null], [$check($att, $a['ssn'], 'fee')['route'], $check($att, $a['ssn'], 'tfl')['route']]);
        $this->postJson(route('slc.registration.att.suggested'), ['row_ids' => [1], 'key' => 'nonsense'])->assertStatus(422);
        // Apply suggested: A's fee and TFL become CoCs (one CoC, linked to the registration); Change removes it
        $att = $this->postJson(route('slc.registration.att.suggested'), ['row_ids' => [$row($att, $a['ssn'])['id']]])->assertOk()->json('state.attendance');
        $this->assertSame(['coc', 'coc'], [$check($att, $a['ssn'], 'fee')['route'], $check($att, $a['ssn'], 'tfl')['route']]);
        $this->assertSame('checked', $row($att, $a['ssn'])['stage']);
        $cocs = \App\Models\SlcCoc::where('student_id', $a['id'])->where('reason', 'like', 'SLC attendance check%')->get();
        $this->assertCount(1, $cocs);
        $this->assertSame([$reg->id, 'Fee', 'no'], [(int) $cocs[0]->slc_registration_id, $cocs[0]->coc_type, $cocs[0]->actioned]);
        $ra = $row($att, $a['ssn'])['id'];
        $this->postJson(route('slc.registration.att.route.clear'), ['row_id' => $ra, 'key' => 'fee'])->assertOk();
        $this->assertNotNull($cocs[0]->fresh()); // TFL still uses it
        $this->postJson(route('slc.registration.att.route.clear'), ['row_id' => $ra, 'key' => 'tfl'])->assertOk();
        $this->assertSoftDeleted('slc_cocs', ['id' => $cocs[0]->id]);
    }

    private function yearId(): int
    {
        return (int) \App\Models\AcademicYear::where('from_date', '<=', now()->toDateString())->where('to_date', '>=', now()->toDateString())->value('id');
    }

    /** An SLC registration for the report's academic year, as the SLC tab records it (unless the student has one). */
    private function register(array $s): void
    {
        if (!\App\Models\SlcRegistration::where('student_id', $s['id'])->where('academic_year_id', $this->yearId())->exists()) {
            \App\Models\SlcRegistration::create(['student_id' => $s['id'], 'student_course_relation_id' => $s['crel_id'], 'course_creation_instance_id' => $s['instance']['id'], 'ssn' => $s['ssn'], 'confirmation_date' => now()->toDateString(), 'academic_year_id' => $this->yearId(), 'registration_year' => $s['instance']['rank'], 'slc_registration_status_id' => 1, 'note' => '', 'created_by' => 7]);
        }
    }

    /** Record the test students' SLC registrations, upload, then sort every Data check difference with "No action" so their codes can be confirmed. */
    private function uploadReady(?string $text = null): array
    {
        foreach (array_slice($this->withSsn, 0, 3) as $st) {
            $this->register($st);
        }
        $att = $this->upload($text)->assertOk()->json('state.attendance');
        foreach ($att['rows'] as $i) {
            foreach ($i['s'] ? $i['checks'] : [] as $c) {
                if (!$c['ok'] && !$c['route'] && $c['key'] !== 'registration') {
                    $att = $this->postJson(route('slc.registration.att.route'), ['row_id' => $i['id'], 'key' => $c['key'], 'route' => 'accept'])->assertOk()->json('state.attendance');
                }
            }
        }

        return $att;
    }

    private function rowOf(array $att, string $ssn): array
    {
        return collect($att['rows'])->firstWhere('r.ssn', $ssn);
    }

    public function test_the_register_in_the_liability_period_and_a_suggested_code(): void
    {
        $att = $this->upload()->assertOk()->json('state.attendance');
        $i = $this->rowOf($att, $this->withSsn[0]['ssn']);
        $l = $i['liab'];
        $this->assertSame(['AUT', 'Autumn'], [$l['term'], $l['termName']]);
        $this->assertSame($l['a']['attended'], $l['a']['physical'] + $l['a']['online'] + $l['a']['other']);
        $this->assertLessThanOrEqual($l['a']['held'], $l['a']['attended']);
        if ($l['start']) {
            $this->assertLessThanOrEqual($l['end'], $l['start']);
        }
        // the suggestion is a known code (or none, with the reason) and the codes list comes from attendance_codes
        $this->assertTrue($i['code']['suggested'] === null || in_array($i['code']['suggested'], ['A', 'C', 'F', 'S', 'X'], true));
        $this->assertNotSame('', $i['code']['reason']);
        $this->assertSame(['A', 'C', 'D', 'F', 'L', 'N', 'S', 'X'], array_column($att['codes'], 'code'));
        $this->assertFalse(collect($att['codes'])->firstWhere('code', 'A')['coc']);
        $this->assertSame('Withdrawal', collect($att['codes'])->firstWhere('code', 'X')['cocType']);

        // the rules, on their own
        $suggest = [\App\Services\SlcRegistration\AttendanceCodes::class, 'suggest'];
        $liab = fn ($held, $attended) => ['start' => '2026-09-07', 'started' => true, 'termName' => 'Autumn', 'last' => '2026-10-30', 'st' => 'ok', 'a' => ['held' => $held, 'attended' => $attended]];
        $this->assertSame('S', $suggest('SLC Suspended', $liab(10, 8), [])['code']);
        $this->assertSame('X', $suggest('SLC Discarded', $liab(10, 8), [])['code']);
        $this->assertSame('X', $suggest('SLC Enrolled', $liab(10, 0), [])['code']);
        $this->assertNull($suggest('SLC Enrolled', $liab(0, 0), [])['code']);
        $this->assertSame('C', $suggest('SLC Enrolled', $liab(10, 8), [['key' => 'course', 'ok' => false]])['code']);
        $this->assertSame('F', $suggest('SLC Enrolled', $liab(10, 8), [['key' => 'tfl', 'ok' => false]])['code']);
        $this->assertSame('A', $suggest('SLC Enrolled', $liab(10, 8), [['key' => 'dob', 'ok' => false]])['code']);
        $this->assertNull($suggest('SLC Enrolled', ['start' => null, 'started' => false, 'termName' => 'Autumn'] + $liab(0, 0), [])['code']);
    }

    public function test_codes_are_confirmed_into_a_batch_with_a_coc_and_downloaded_as_the_sis_file(): void
    {
        [$a, $b] = [$this->withSsn[0], $this->withSsn[1]];
        $att = $this->uploadReady();
        $ra = $this->rowOf($att, $a['ssn'])['id'];
        $rb = $this->rowOf($att, $b['ssn'])['id'];

        // any code can be chosen; an unknown one is refused
        $att = $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra, 'code' => 'X'])->assertOk()->json('state.attendance');
        $this->assertSame(['X', 'X', true, 'ready'], [$this->rowOf($att, $a['ssn'])['code']['chosen'], $this->rowOf($att, $a['ssn'])['code']['final'], $this->rowOf($att, $a['ssn'])['code']['coc'], $this->rowOf($att, $a['ssn'])['codeStage']]);
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $rb, 'code' => 'A'])->assertOk();
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $rb, 'code' => 'Q'])->assertStatus(422);

        // Confirm: both into one awaiting batch; X queues a Withdrawal CoC on the SLC tab
        $res = $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra, $rb]])->assertOk();
        $att = $res->json('state.attendance');
        [$ia, $ib] = [$this->rowOf($att, $a['ssn']), $this->rowOf($att, $b['ssn'])];
        $this->assertSame(['confirmed', 'confirmed', 'X', 'A'], [$ia['codeStage'], $ib['codeStage'], $ia['confirm']['code'], $ib['confirm']['code']]);
        $code = $ia['confirm']['batchId'];
        $this->assertSame($code, $ib['confirm']['batchId']);
        $this->assertStringContainsString($code, $res->json('flash.text'));
        $this->assertSame(['Withdrawal', false], [$ia['coc']['type'], $ia['coc']['actioned']]);
        $this->assertNull($ib['coc']);
        $coc = \App\Models\SlcCoc::find($ia['coc']['id']);
        $this->assertSame([$a['id'], 'Withdrawal', 'no'], [(int) $coc->student_id, $coc->coc_type, $coc->actioned]);
        $this->assertStringStartsWith('SLC attendance code X', $coc->reason);
        $batch = collect($att['batches'])->firstWhere('id', $code);
        $this->assertSame(['awaiting', 'AUT'], [$batch['status'], $batch['term']]);
        $this->assertEqualsCanonicalizing([$ra, $rb], array_column($batch['items'], 'rowId'));
        // a confirmed code can't be changed, and a line already confirmed is not confirmed twice
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra, 'code' => 'A'])->assertStatus(422);
        $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra]])->assertStatus(422);

        // step 6: done in SIS and back
        $this->postJson(route('slc.registration.att.coc'), ['row_id' => $ra, 'done' => true])->assertOk();
        $this->assertSame('Yes', $coc->fresh()->actioned);
        $this->postJson(route('slc.registration.att.coc'), ['row_id' => $ra, 'done' => false])->assertOk();
        $this->assertSame('no', $coc->fresh()->actioned);

        // Remove B while the batch waits
        $att = $this->postJson(route('slc.registration.att.undo'), ['row_id' => $rb])->assertOk()->json('state.attendance');
        $this->assertSame('ready', $this->rowOf($att, $b['ssn'])['codeStage']);
        $this->assertSame([$ra], array_column(collect($att['batches'])->firstWhere('id', $code)['items'], 'rowId'));

        // step 7: the SIS file — the line as sent with field 21 = the code, the header recounted
        $res = $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk();
        $file = $res->json('file');
        $this->assertSame('ATT_LCHC_AUT_'.$code.'.txt', $file['name']);
        $lines = explode("\r\n", rtrim($file['text']));
        $this->assertCount(2, $lines);
        $this->assertSame(['LCHC', 'LONDON CHURCHILL COLLEGE', now()->format('dmY'), '1', '6350.00', '0.00', '0.00'], explode('~', $lines[0]));
        $f = explode('~', $lines[1]);
        $this->assertSame([21, $a['ssn'], 'X'], [count($f), $f[1], $f[20]]);
        $this->assertSame(SlcAttendanceImportRow::find($ra)->raw, implode('~', array_slice($f, 0, 20)).'~');
        $this->assertSame('downloaded', collect($res->json('state.attendance.batches'))->firstWhere('id', $code)['status']);
        $this->getJson(route('slc.registration.att.batch.file', ['code' => $code]))->assertOk()->assertJsonPath('text', $file['text']);
        // once downloaded, a line can't leave the batch
        $this->postJson(route('slc.registration.att.undo'), ['row_id' => $ra])->assertStatus(422);
        $this->postJson(route('slc.registration.att.batch.uploaded'), ['code' => $code])->assertOk();
        $this->assertSame('uploaded', \App\Models\SlcAttendanceBatch::where('code', $code)->value('status'));
        $this->postJson(route('slc.registration.att.batch.uploaded'), ['code' => $code])->assertStatus(422);
    }

    public function test_a_code_cannot_be_confirmed_without_an_slc_registration(): void
    {
        $c = $this->withSsn[2];
        \App\Models\SlcRegistration::where('student_id', $c['id'])->where('academic_year_id', $this->yearId())->delete(); // rolled back with the test
        $att = $this->upload()->assertOk()->json('state.attendance');
        $i = $this->rowOf($att, $c['ssn']);
        $this->assertSame('noreg', $i['codeStage']);
        $this->assertGreaterThanOrEqual(1, $att['counts']['codeNoReg']);
        // a code can still be chosen, but not confirmed
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $i['id'], 'code' => 'A'])->assertOk();
        $res = $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$i['id']]])->assertStatus(422);
        $this->assertStringContainsString('no SLC registration', $res->json('flash.text'));
        $this->assertNull(SlcAttendanceImportRow::find($i['id'])->confirmed_code);

        // once the registration is recorded (and the Data check sorted) it can be
        $att = $this->uploadReady();
        $i = $this->rowOf($att, $c['ssn']);
        $this->assertSame('ready', $i['codeStage']);
        $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$i['id']]])->assertOk();
    }

    public function test_removing_the_last_line_drops_its_coc_and_the_empty_batch(): void
    {
        $att = $this->uploadReady();
        $ra = $this->rowOf($att, $this->withSsn[0]['ssn'])['id'];
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra, 'code' => 'S'])->assertOk();
        $i = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra]])->assertOk()->json('state.attendance'), $this->withSsn[0]['ssn']);
        $this->assertSame('Suspension', $i['coc']['type']);
        $this->postJson(route('slc.registration.att.undo'), ['row_id' => $ra])->assertOk();
        $this->assertSoftDeleted('slc_cocs', ['id' => $i['coc']['id']]);
        $this->assertFalse(\App\Models\SlcAttendanceBatch::where('code', $i['confirm']['batchId'])->exists());
        $this->assertNull(SlcAttendanceImportRow::find($ra)->slc_coc_id);
    }

    public function test_a_newer_upload_keeps_codes_confirmations_and_the_batch(): void
    {
        [$a, $b] = [$this->withSsn[0], $this->withSsn[1]];
        $att = $this->uploadReady();
        $ra = $this->rowOf($att, $a['ssn'])['id'];
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra, 'code' => 'X'])->assertOk();
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $this->rowOf($att, $b['ssn'])['id'], 'code' => 'D'])->assertOk();
        $first = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra]])->assertOk()->json('state.attendance'), $a['ssn']);
        $code = $first['confirm']['batchId'];

        // the same report again: the confirmation (and its CoC) moves to the new line, the chosen code stays
        $res = $this->upload()->assertOk();
        $this->assertStringContainsString('carried over', $res->json('flash.text'));
        $att = $res->json('state.attendance');
        $ia = $this->rowOf($att, $a['ssn']);
        $this->assertNotSame($ra, $ia['id']);
        $this->assertSame(['confirmed', 'X', $code, false, $first['coc']['id']], [$ia['codeStage'], $ia['confirm']['code'], $ia['confirm']['batchId'], $ia['confirm']['earlier'], $ia['coc']['id']]);
        $this->assertSame('D', $this->rowOf($att, $b['ssn'])['code']['chosen']);
        $this->assertSame([$ia['id']], array_column(collect($att['batches'])->firstWhere('id', $code)['items'], 'rowId'));

        // downloaded, then a third upload: the line shows the earlier batch and can't be removed
        $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk();
        $ic = $this->rowOf($this->upload()->assertOk()->json('state.attendance'), $a['ssn']);
        $this->assertSame(['confirmed', true, $code], [$ic['codeStage'], $ic['confirm']['earlier'], $ic['confirm']['batchId']]);
        $this->postJson(route('slc.registration.att.undo'), ['row_id' => $ic['id']])->assertStatus(422);
    }

    public function test_slc_names_without_middle_names_match(): void
    {
        $within = [\App\Services\SlcRegistration\AttendanceChecks::class, 'within'];
        $this->assertTrue($within('DANAIL', 'Danail Angelov'));
        $this->assertTrue($within('LUMINITA', 'Luminita-Mariana'));
        $this->assertTrue($within('CHAVEZ', 'Chavez Quinones'));
        $this->assertFalse($within('IOUNT', 'Ionut-Gabriel'));
        $this->assertFalse($within('NGUEMA OBONO', 'Obono'));
    }

    public function test_a_file_that_is_not_the_report_is_refused_and_access_is_privileged(): void
    {
        $this->upload("not a report\r\n")->assertStatus(422)->assertJsonPath('flash.tone', 'bad');
        $other = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(50)->get()->first(fn ($u) => empty($u->priv()['slc_registration']));
        $this->actingAs($other)->postJson(route('slc.registration.att.upload'))->assertForbidden();
    }

    public function test_the_sample_layout(): void
    {
        $p = AttendanceFile::parse("LCHC~LONDON CHURCHILL COLLEGE~02112026~1~6350.00~0.00~0.00\r\nAUT~SSN1~DOE~JANE~15041985~~134138~HOSPITALITY~1~6350.00~6350.00~0.00~0.00~F~22102026~NEW~N~N~N~N~\r\n");
        $this->assertSame(['LCHC', '02112026', 1, [6350.0, 0.0, 0.0]], [$p['header']['hei'], $p['header']['date'], $p['header']['count'], $p['header']['totals']]);
        $r = $p['rows'][0];
        $this->assertSame(['AUT', 'SSN1', 'DOE', 'JANE', '15041985', '134138', '1', '6350.00', 'NEW', ''], [$r['term'], $r['ssn'], $r['surname'], $r['forename'], $r['dob'], $r['slcCode'], $r['year'], $r['tfl'], $r['newStudent'], $r['attendanceCode']]);
        $this->assertSame('Autumn', AttendanceFile::termName('AUT'));
    }
}
