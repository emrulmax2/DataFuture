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
        // a real batch awaiting download on the dev database would take the test's confirmations: set aside for the test
        // (inside its transaction — rolled back with it; the tables are InnoDB)
        \App\Models\SlcAttendanceBatch::where('status', 'awaiting')->update(['status' => 'downloaded']);
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

    public function test_first_and_last_name_are_decided_together_as_one_name(): void
    {
        $c = $this->withSsn[2];
        $lines = explode("\r\n", rtrim($this->text));
        $f = explode('~', $lines[3]);
        [$f[2], $f[3]] = ['ZZTESTSURNAME', 'IOUNT'];
        $lines[3] = implode('~', $f);
        $att = $this->upload(implode("\r\n", $lines)."\r\n")->assertOk()->json('state.attendance');
        $id = $this->rowOf($att, $c['ssn'])['id'];
        $route = fn ($att, $k) => collect($this->rowOf($att, $c['ssn'])['checks'])->firstWhere('key', $k);
        $this->assertSame([false, false], [$route($att, 'first')['ok'], $route($att, 'last')['ok']]);

        // one action for both
        $res = $this->postJson(route('slc.registration.att.routes'), ['row_id' => $id, 'keys' => ['first', 'last'], 'route' => 'contact'])->assertOk();
        $this->assertStringContainsString('(first name and last name)', $res->json('flash.text'));
        $this->assertSame(['contact', 'contact'], [$route($res->json('state.attendance'), 'first')['route'], $route($res->json('state.attendance'), 'last')['route']]);
        $this->assertTrue(\App\Models\SlcBulkActivity::where('text', 'like', 'Bulk attendance – student to contact about first name and last name – %')->exists());
        $this->postJson(route('slc.registration.att.routes'), ['row_id' => $id, 'keys' => ['first', 'last'], 'route' => 'accept'])->assertStatus(422); // nothing left
        // Change reopens both
        $att = $this->postJson(route('slc.registration.att.routes.clear'), ['row_id' => $id, 'keys' => ['first', 'last']])->assertOk()->json('state.attendance');
        $this->assertSame([null, null], [$route($att, 'first')['route'], $route($att, 'last')['route']]);
        // an action one of them doesn't have: refused, nothing done
        $this->postJson(route('slc.registration.att.routes'), ['row_id' => $id, 'keys' => ['first', 'ssn'], 'route' => 'update'])->assertStatus(422);
        $this->assertSame($c['first'], Student::find($c['id'])->first_name);
        // Update our record: both names from SLC, each archived
        $att = $this->postJson(route('slc.registration.att.routes'), ['row_id' => $id, 'keys' => ['first', 'last'], 'route' => 'update'])->assertOk()->json('state.attendance');
        $st = Student::find($c['id']);
        $this->assertSame(['Iount', 'Zztestsurname'], [$st->first_name, $st->last_name]);
        $this->assertSame(2, StudentArchive::where('student_id', $c['id'])->whereIn('field_name', ['first_name', 'last_name'])->where('created_at', '>=', now()->subMinute())->count());
        $this->assertSame([true, true], [$route($att, 'first')['ok'], $route($att, 'last')['ok']]);
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
        // the period: the earliest session term of the year with no attendance on the SLC tab (SIS applies a code
        // to the earliest outstanding period), its term the course instance's term with that session term
        $this->assertContains($l['period'], [1, 2, 3, null]);
        $own = DB::table('instance_terms as t')->join('term_declarations as d', 'd.id', '=', 't.term_declaration_id')->leftJoin('term_types as y', 'y.id', '=', 'd.term_type_id')
            ->where('t.course_creation_instance_id', $this->withSsn[0]['instance']['id'])->where('t.session_term', $l['period'])->whereNull('t.deleted_at')->first(['y.code', 'd.name']);
        if ($own) {
            $this->assertSame([strtoupper($own->code), $own->name], [$l['term'], $l['decl']]);
            $this->assertSame(\App\Services\SlcRegistration\AttendanceFile::termName($l['term']), $l['termName']);
        } else {
            $this->assertSame(['AUT', 'Autumn'], [$l['term'], $l['termName']]); // no such term: the line's own (AUT)
        }
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
        // spec v9.0 Appendix A: C – a different course or year; F – the course fee; N (or L, checked) – the liability (TFL)
        $this->assertSame('C', $suggest('SLC Enrolled', $liab(10, 8), [['key' => 'year', 'ok' => false]])['code']);
        $this->assertSame('F', $suggest('SLC Enrolled', $liab(10, 8), [['key' => 'fee', 'ok' => false], ['key' => 'tfl', 'ok' => false]])['code']);
        $tflOnly = $suggest('SLC Enrolled', $liab(10, 8), [['key' => 'tfl', 'ok' => false]]);
        $this->assertSame(['N', true], [$tflOnly['code'], $tflOnly['review']]);
        $this->assertStringContainsString('L if we’ll put it right', $tflOnly['reason']);
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
        // D (never attended): SLC refuses it for a student recorded as Registered (spec v9.0 §7.2.3)
        if (isset(\App\Services\SlcRegistration\AttendanceCodes::table()['D'])) {
            $res = $this->postJson(route('slc.registration.att.code'), ['row_id' => $rb, 'code' => 'D'])->assertStatus(422);
            $this->assertStringContainsString('Registered', $res->json('flash.text'));
        }

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
        // the saved copy can't be read (another server's storage, a failed save): built again, the same file
        $batchRow = \App\Models\SlcAttendanceBatch::where('code', $code)->first();
        Storage::disk($batchRow->disk)->delete($batchRow->path);
        $this->getJson(route('slc.registration.att.batch.file', ['code' => $code]))->assertOk()->assertJsonPath('text', $file['text'])->assertJsonPath('rebuilt', true);
        $again = $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk();
        $this->assertSame([$file['text'], true], [$again->json('file.text'), $again->json('file.rebuilt')]);
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

    public function test_never_attended_d_goes_in_the_file_only_without_a_registration(): void
    {
        if (!isset(\App\Services\SlcRegistration\AttendanceCodes::table()['D'])) {
            $this->markTestSkipped('Code D is not in the attendance code list.');
        }
        $c = $this->withSsn[2];
        $att = $this->uploadReady();
        $i = $this->rowOf($att, $c['ssn']);
        // no registration for the year: D (spec v9.0 §7.2.3) is the one code that can still be confirmed
        \App\Models\SlcRegistration::where('student_id', $c['id'])->where('academic_year_id', $this->yearId())->delete(); // rolled back with the test
        $att = $this->postJson(route('slc.registration.att.code'), ['row_id' => $i['id'], 'code' => 'D'])->assertOk()->json('state.attendance');
        $this->assertSame('ready', $this->rowOf($att, $c['ssn'])['codeStage']);
        $code = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$i['id']]])->assertOk()->json('state.attendance'), $c['ssn'])['confirm']['batchId'];
        $text = $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk()->json('file.text');
        $lines = explode("\r\n", rtrim($text, "\r\n"));
        $this->assertSame(['1', 'D'], [explode('~', $lines[0])[3], explode('~', $lines[1])[20]]);
        $this->assertTrue(mb_check_encoding($text, 'UTF-8'));
        // another code still needs the registration
        $j = $this->rowOf($att, $this->withSsn[0]['ssn']);
        \App\Models\SlcRegistration::where('student_id', $this->withSsn[0]['id'])->where('academic_year_id', $this->yearId())->delete();
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $j['id'], 'code' => 'A'])->assertOk();
        $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$j['id']]])->assertStatus(422);
    }

    public function test_bulk_attendance_confirmation_records_the_slc_attendance(): void
    {
        [$a, $b, $c] = [$this->withSsn[0], $this->withSsn[1], $this->withSsn[2]];
        $d = $this->withSsn[3] ?? null;
        if ($d) {
            $this->register($d);
        }
        $att = $this->uploadReady();
        [$ra, $rb, $rc] = [$this->rowOf($att, $a['ssn']), $this->rowOf($att, $b['ssn']), $this->rowOf($att, $c['ssn'])];
        $rd = $d ? $this->rowOf($att, $d['ssn']) : null;
        foreach (array_filter([$ra, $rb, $rc, $rd]) as $r) {
            if (!$r['liab'] || !$r['liab']['termDeclarationId'] || !$r['liab']['sessionTerm']) {
                $this->markTestSkipped('The sample students have no term on their course instance.');
            }
        }
        foreach ([[$ra, 'A'], [$rb, 'X'], [$rc, 'A'], [$rd, 'A']] as [$r, $k]) {
            if ($r) {
                $this->postJson(route('slc.registration.att.code'), ['row_id' => $r['id'], 'code' => $k])->assertOk();
            }
        }
        $code = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => array_column(array_filter([$ra, $rb, $rc, $rd]), 'id')])->assertOk()->json('state.attendance'), $a['ssn'])['confirm']['batchId'];
        $go = fn (array $ids, array $extra = []) => $this->postJson(route('slc.registration.att.batch.attendance'), ['code' => $code, 'row_ids' => $ids] + $extra);

        // not before the batch is uploaded to SIS
        $go([$ra['id']])->assertStatus(422);
        $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk();
        $go([$ra['id']])->assertStatus(422);
        $this->postJson(route('slc.registration.att.batch.uploaded'), ['code' => $code])->assertOk();

        // A needs the registration's agreement for its instalment: without one the student is skipped, nothing saved
        $reg = \App\Models\SlcRegistration::where('student_id', $a['id'])->where('academic_year_id', $this->yearId())->where('slc_registration_status_id', 1)->latest('id')->first();
        \App\Models\SlcAgreement::where('slc_registration_id', $reg->id)->delete(); // rolled back with the test
        $res = $go([$ra['id']])->assertOk();
        $this->assertStringContainsString('no SLC agreement', $res->json('flash.text'));
        $this->assertNull(SlcAttendanceImportRow::find($ra['id'])->slc_recorded);
        \App\Models\SlcAgreement::create(['student_id' => $a['id'], 'student_course_relation_id' => $reg->student_course_relation_id, 'course_creation_instance_id' => $reg->course_creation_instance_id,
            'slc_registration_id' => $reg->id, 'year' => $reg->registration_year, 'fees' => 6350, 'total' => 6350, 'created_by' => 1]);

        // the student list shows each line's attendance and instalment before anything is recorded
        $pv = $this->getJson(route('slc.registration.att.batch.preview', ['code' => $code]))->assertOk()->json();
        $pa = $pv['rows'][$ra['id']];
        $this->assertSame([false, null, $reg->id, (int) $reg->registration_year, now()->toDateString(), $ra['liab']['termDeclarationId'], $ra['liab']['sessionTerm'], 'A'],
            [$pa['recorded'], $pa['problem'], $pa['attendance']['registrationId'], $pa['attendance']['registrationYear'], $pa['attendance']['date'], $pa['attendance']['termDeclarationId'], $pa['attendance']['sessionTerm'], $pa['attendance']['code']]);
        $share = (int) $ra['liab']['sessionTerm'] === 3 ? 0.5 : 0.25;
        $this->assertEqualsWithDelta(round((float) SlcAttendanceImportRow::find($ra['id'])->data['tfl'] * $share, 2), $pa['installment']['amount'], 0.001);
        $this->assertSame(\App\Models\SlcAgreement::where('slc_registration_id', $reg->id)->value('id'), $pa['installment']['agreementId']);
        $pb = $pv['rows'][$rb['id']];
        $this->assertSame([null, 'X releases no tuition fee payment'], [$pb['installment'], $pb['noInstallment']]);

        // (user, 13 Nov 2026) no overwriting: a term already on the SLC tab is passed over for the year's next free one —
        //   B has its Term n attendance (X: no instalment); C its Term n attendance and instalment; D only a Term n instalment
        $existingAtt = fn (array $st, array $r) => \App\Models\SlcAttendance::create(['student_id' => $st['id'], 'student_course_relation_id' => $this->crelOf($st['id']),
            'term_declaration_id' => $r['liab']['termDeclarationId'], 'session_term' => $r['liab']['sessionTerm'], 'attendance_code_id' => 1, 'confirmation_date' => '2026-10-01', 'created_by' => 1]);
        $agreementOf = function (array $st) {
            $g = \App\Models\SlcRegistration::where('student_id', $st['id'])->where('academic_year_id', $this->yearId())->where('slc_registration_status_id', 1)->latest('id')->first();
            return \App\Models\SlcAgreement::where('slc_registration_id', $g->id)->whereNull('deleted_at')->value('id') ?? \App\Models\SlcAgreement::create(['student_id' => $st['id'],
                'student_course_relation_id' => $g->student_course_relation_id, 'course_creation_instance_id' => $g->course_creation_instance_id, 'slc_registration_id' => $g->id,
                'year' => $g->registration_year, 'fees' => 6350, 'total' => 6350, 'created_by' => 1])->id;
        };
        $instAt = fn (array $st, array $r) => \App\Models\SlcInstallment::create(['student_id' => $st['id'], 'student_course_relation_id' => $this->crelOf($st['id']), 'slc_agreement_id' => $agreementOf($st),
            'installment_date' => '2026-10-01', 'amount' => 1.00, 'session_term' => $r['liab']['sessionTerm'], 'term_declaration_id' => $r['liab']['termDeclarationId'], 'created_by' => 1]);
        $termNo = fn (array $st, int $n) => DB::table('instance_terms')->where('course_creation_instance_id', $st['instance']['id'])->where('session_term', $n)->whereNull('deleted_at')->value('term_declaration_id');
        $earlier = $existingAtt($b, $rb);
        $keptC = $existingAtt($c, $rc);
        $instC = $instAt($c, $rc);
        $oldInst = $d ? $instAt($d, $rd) : null;
        $pv = $this->getJson(route('slc.registration.att.batch.preview', ['code' => $code]))->assertOk()->json('rows');
        $this->assertSame([(int) $rc['liab']['sessionTerm'], $keptC->id, $instC->id], [$pv[$rc['id']]['taken'][0]['sessionTerm'], $pv[$rc['id']]['taken'][0]['attendance']['id'], $pv[$rc['id']]['taken'][0]['installment']['id']]);

        // no question asked: everyone recordable gets new rows, and nothing already there changes
        $before = [\App\Models\SlcAttendance::count(), \App\Models\SlcInstallment::count()];
        $res = $go(array_column(array_filter([$ra, $rb, $rc, $rd]), 'id'))->assertOk();
        $this->assertSame(['2026-10-01', '2026-10-01', 1.0], [\App\Models\SlcAttendance::find($earlier->id)->getRawOriginal('confirmation_date'), \App\Models\SlcAttendance::find($keptC->id)->getRawOriginal('confirmation_date'), (float) \App\Models\SlcInstallment::find($instC->id)->amount]);
        $recorded = SlcAttendanceImportRow::whereIn('id', array_column(array_filter([$ra, $rb, $rc, $rd]), 'id'))->whereNotNull('slc_recorded')->get();
        $this->assertSame([$before[0] + $recorded->count(), $before[1] + $recorded->whereNotNull('slc_installment_id')->count()], [\App\Models\SlcAttendance::count(), \App\Models\SlcInstallment::count()]);
        // B and C: recorded as Term n+1 — C's instalment at that term's share (25%, or 50% for Term 3)
        foreach ([[$b, $rb], [$c, $rc]] as [$st, $r]) {
            $n = (int) $r['liab']['sessionTerm'];
            $next = $n < 3 ? $termNo($st, $n + 1) : null;
            $row = SlcAttendanceImportRow::find($r['id']);
            if (!$next) {
                $this->assertNull($row->slc_recorded);
                continue;
            }
            $sa = \App\Models\SlcAttendance::find($row->slc_attendance_id);
            $this->assertSame(['created', $n + 1, (int) $next], [$row->slc_recorded, (int) $sa->session_term, (int) $sa->term_declaration_id]);
            $this->assertNotContains($row->slc_attendance_id, [$earlier->id, $keptC->id]);
            if ($st === $c) {
                $inst = \App\Models\SlcInstallment::find($row->slc_installment_id);
                $this->assertSame([$n + 1, $sa->id], [(int) $inst->session_term, (int) $inst->slc_attendance_id]);
                $this->assertEqualsWithDelta(round((float) $row->data['tfl'] * ($n + 1 === 3 ? 0.5 : 0.25), 2), (float) $inst->amount, 0.001);
            }
            $this->assertStringContainsString('as the next free term', $res->json('flash.text'));
        }
        if ($d) {
            // D: its Term n attendance added; the Term n instalment is already on Accounts, so no second one
            $rowD = SlcAttendanceImportRow::find($rd['id']);
            $this->assertSame([(int) $rd['liab']['sessionTerm'], null, 1.0], [(int) \App\Models\SlcAttendance::find($rowD->slc_attendance_id)->session_term, $rowD->slc_installment_id, (float) $oldInst->fresh()->amount]);
        }

        // A: the attendance as the SLC tab records it, with the instalment (25% / 50% of the instance fee) and the line marked
        $row = SlcAttendanceImportRow::find($ra['id']);
        $sa = \App\Models\SlcAttendance::find($row->slc_attendance_id);
        $this->assertSame(['created', $ra['liab']['termDeclarationId'], $ra['liab']['sessionTerm'], (int) DB::table('attendance_codes')->where('code', 'A')->value('id')],
            [$row->slc_recorded, (int) $sa->term_declaration_id, (int) $sa->session_term, (int) $sa->attendance_code_id]);
        $this->assertSame(now()->toDateString(), $sa->getRawOriginal('confirmation_date'));
        $this->assertNotNull($sa->slc_registration_id);
        // the instalment: the line's Tuition Fee Loan × 25% (term 1, 2) or 50% (term 3), on the agreement
        $inst = \App\Models\SlcInstallment::find($row->slc_installment_id);
        $tfl = (float) $row->data['tfl'];
        $this->assertEqualsWithDelta(round($tfl * ((int) $sa->session_term === 3 ? 0.5 : 0.25), 2), (float) $inst->amount, 0.001);
        $this->assertSame([(int) $sa->term_declaration_id, (int) $sa->session_term], [(int) $inst->term_declaration_id, (int) $inst->session_term]);
        $item = collect(collect($res->json('state.attendance.batches'))->firstWhere('id', $code)['items'])->firstWhere('rowId', $ra['id']);
        $this->assertSame('created', $item['record']['status']);

        // B's queued CoC is linked to its new attendance
        $rowB = SlcAttendanceImportRow::find($rb['id']);
        if ($rowB->slc_recorded && $rowB->slc_coc_id) {
            $this->assertSame($rowB->slc_attendance_id, (int) \App\Models\SlcCoc::find($rowB->slc_coc_id)->slc_attendance_id);
        }
        // a recorded student can't be recorded twice
        $go([$ra['id']])->assertStatus(422);
    }

    private function crelOf(int $studentId): ?int
    {
        return DB::table('student_course_relations')->where('student_id', $studentId)->where('active', 1)->whereNull('deleted_at')->orderByDesc('id')->value('id');
    }

    public function test_a_period_already_on_the_slc_tab_is_asked_about_before_confirming(): void
    {
        [$a, $b] = [$this->withSsn[0], $this->withSsn[1]];
        $att = $this->uploadReady();
        [$ra, $rb] = [$this->rowOf($att, $a['ssn']), $this->rowOf($att, $b['ssn'])];
        if (!$ra['liab'] || !$ra['liab']['termDeclarationId']) {
            $this->markTestSkipped('The sample student has no term on their course instance.');
        }
        // A's attendance for the period entered by hand on the SLC tab after SIS issued the report
        $byHand = \App\Models\SlcAttendance::create(['student_id' => $a['id'], 'student_course_relation_id' => $this->crelOf($a['id']), 'term_declaration_id' => $ra['liab']['termDeclarationId'],
            'session_term' => $ra['liab']['sessionTerm'], 'attendance_code_id' => 1, 'confirmation_date' => now()->toDateString(), 'created_by' => 1]);
        $att = $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra['id'], 'code' => 'A'])->assertOk()->json('state.attendance');
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $rb['id'], 'code' => 'A'])->assertOk();
        $this->assertSame([$byHand->id, false], [$this->rowOf($att, $a['ssn'])['slcTab']['attendance']['id'], $this->rowOf($att, $a['ssn'])['slcTab']['recorded']]);
        $this->assertGreaterThanOrEqual(1, $att['counts']['onSlcTab']);

        // asked first; not ticked: left out of the batch, B confirmed
        $conflict = $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id'], $rb['id']]])->assertStatus(409)->json();
        $this->assertSame([[$ra['id']], 1, $byHand->id], [array_column($conflict['existing'], 'rowId'), $conflict['others'], $conflict['existing'][0]['onTab']['attendance']['id']]);
        $res = $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id'], $rb['id']], 'confirmed' => true])->assertOk();
        $this->assertStringContainsString('left out – already on the SLC tab', $res->json('flash.text'));
        $this->assertSame([null, 'confirmed'], [SlcAttendanceImportRow::find($ra['id'])->confirmed_code, $this->rowOf($res->json('state.attendance'), $b['ssn'])['codeStage']]);
        // ticked to send anyway: confirmed
        $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id']], 'confirmed' => true, 'include_ids' => [$ra['id']]])->assertOk();
        $this->assertSame('A', SlcAttendanceImportRow::find($ra['id'])->confirmed_code);
    }

    public function test_step_4_says_what_would_stop_the_slc_tab_record_and_a_batch_file_can_be_regenerated(): void
    {
        $a = $this->withSsn[0];
        $att = $this->uploadReady();
        $ra = $this->rowOf($att, $a['ssn']);
        // A with a Tuition Fee Loan and no agreement on the registration: the instalment couldn't be recorded later
        $reg = \App\Models\SlcRegistration::where('student_id', $a['id'])->where('academic_year_id', $this->yearId())->where('slc_registration_status_id', 1)->latest('id')->first();
        \App\Models\SlcAgreement::where('slc_registration_id', $reg->id)->delete(); // rolled back with the test
        $att = $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra['id'], 'code' => 'A'])->assertOk()->json('state.attendance');
        $row = $this->rowOf($att, $a['ssn']);
        if ($row['liab'] && $row['liab']['sessionTerm'] && (float) $row['r']['tfl'] > 0) {
            $this->assertStringContainsString('no SLC agreement', (string) $row['recordBlock']);
        }

        // Regenerate: the same lines, dated as before or today; a batch awaiting download can't be
        $code = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id']], 'confirmed' => true, 'include_ids' => [$ra['id']]])->assertOk()->json('state.attendance'), $a['ssn'])['confirm']['batchId'];
        $this->postJson(route('slc.registration.att.batch.regenerate'), ['code' => $code, 'date' => 'today'])->assertStatus(422);
        $first = $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk()->json('file.text');
        \App\Models\SlcAttendanceBatch::where('code', $code)->update(['file_date' => '2026-11-01']);
        $kept = $this->postJson(route('slc.registration.att.batch.regenerate'), ['code' => $code, 'date' => 'original'])->assertOk()->json('file.text');
        $this->assertSame('01112026', explode('~', explode("\r\n", $kept)[0])[2]);
        $this->assertSame(array_slice(explode("\r\n", $first), 1), array_slice(explode("\r\n", $kept), 1)); // the same lines
        $fresh = $this->postJson(route('slc.registration.att.batch.regenerate'), ['code' => $code, 'date' => 'today'])->assertOk();
        $this->assertSame(now()->format('dmY'), explode('~', explode("\r\n", $fresh->json('file.text'))[0])[2]);
        $this->assertSame(now()->toDateString(), collect($fresh->json('state.attendance.batches'))->firstWhere('id', $code)['file']['date']);
        $this->getJson(route('slc.registration.att.batch.file', ['code' => $code]))->assertOk()->assertJsonPath('text', $fresh->json('file.text'));
        // once uploaded, its lines count as still to record on the SLC tab (the Dashboard's last step)
        $up = $this->postJson(route('slc.registration.att.batch.uploaded'), ['code' => $code])->assertOk()->json('state.attendance.counts');
        $this->assertGreaterThanOrEqual(1, $up['toRecord']);
    }

    public function test_the_spec_rules_for_codes_not_registered_students_and_the_report(): void
    {
        [$a, $b, $c] = [$this->withSsn[0], $this->withSsn[1], $this->withSsn[2]];
        // spec v9.0 §7.2.2: a student line must have 21 fields — one that hasn't is left out and listed; WIN is a start period
        $p = AttendanceFile::parse("LCHC~X~02112026~1~0~0~0\r\nAUT~SSN1~DOE~JANE~15041985~~134138~C~1~0~0~0~0~F~22102026~NEW~N~N~N~N\r\n");
        $this->assertSame([[], 1], [$p['rows'], count($p['errors'])]);
        $this->assertSame('Winter', AttendanceFile::termName('WIN'));

        // the report's column 28: SIS already has A for this period (saved or submitted)
        $text = implode("\r\n", array_map(fn ($l) => str_contains($l, '~'.$a['ssn'].'~') ? substr($l, 0, -1).'~A' : $l, explode("\r\n", rtrim($this->text, "\r\n"))))."\r\n";
        $att = $this->uploadReady($text);
        $ra = $this->rowOf($att, $a['ssn']);
        $this->assertSame(['A', 'A'], [$ra['sisCode'], $ra['code']['suggested']]);
        $this->assertStringContainsString('report column 28', $ra['code']['reason']);
        if ($ra['codeStage'] === 'ready') {
            $conflict = $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id']]])->assertStatus(409)->json('existing');
            $this->assertSame(['A', $ra['id']], [$conflict[0]['sisCode'], $conflict[0]['rowId']]);
        }

        // Not Registered (registration No): SIS takes only X or S (spec §7.2.3) — suggested X, an "in attendance" code refused
        \App\Models\SlcRegistration::where('student_id', $b['id'])->where('academic_year_id', $this->yearId())->update(['slc_registration_status_id' => 2]); // rolled back
        $rb = $this->rowOf($this->postJson(route('slc.registration.att.code'), ['row_id' => $ra['id'], 'code' => null])->assertOk()->json('state.attendance'), $b['ssn']);
        $this->assertSame(['X', 'No'], [$rb['code']['final'], $rb['notReg']['status']]);
        $res = $this->postJson(route('slc.registration.att.code'), ['row_id' => $rb['id'], 'code' => 'A'])->assertStatus(422);
        $this->assertStringContainsString('Not Registered', $res->json('flash.text'));
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $rb['id'], 'code' => 'D'])->assertStatus(422);
        $rb = $this->rowOf($this->postJson(route('slc.registration.att.code'), ['row_id' => $rb['id'], 'code' => 'X'])->assertOk()->json('state.attendance'), $b['ssn']);
        if ($rb['codeStage'] === 'ready') {
            $code = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$rb['id']], 'confirmed' => true, 'include_ids' => [$rb['id']]])->assertOk()->json('state.attendance'), $b['ssn'])['confirm']['batchId'];
            $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk();
            $this->postJson(route('slc.registration.att.batch.uploaded'), ['code' => $code])->assertOk();
            $plan = $this->getJson(route('slc.registration.att.batch.preview', ['code' => $code]))->assertOk()->json('rows.'.$rb['id']);
            if (!$plan['problem']) {
                // recorded on the SLC tab against the Not Registered registration, with no instalment
                $notRegId = \App\Models\SlcRegistration::where('student_id', $b['id'])->where('academic_year_id', $this->yearId())->latest('id')->value('id');
                $this->assertSame([$notRegId, null, 'X releases no tuition fee payment'], [$plan['attendance']['registrationId'], $plan['installment'], $plan['noInstallment']]);
            }
        }

        // D after a transfer (spec §7.2.3): an SLC registration for the year on another course
        $rc = $this->rowOf($att, $c['ssn']);
        \App\Models\SlcRegistration::where('student_id', $c['id'])->where('academic_year_id', $this->yearId())->delete(); // rolled back
        \App\Models\SlcRegistration::create(['student_id' => $c['id'], 'student_course_relation_id' => 0, 'academic_year_id' => $this->yearId(), 'registration_year' => 1,
            'slc_registration_status_id' => 3, 'confirmation_date' => '2026-10-01', 'created_by' => 1]);
        $res = $this->postJson(route('slc.registration.att.code'), ['row_id' => $rc['id'], 'code' => 'D'])->assertStatus(422);
        $this->assertStringContainsString('another course', $res->json('flash.text'));
    }

    public function test_a_line_sis_rejected_goes_back_to_step_4_and_can_be_sent_again(): void
    {
        $a = $this->withSsn[0];
        $att = $this->uploadReady();
        $ra = $this->rowOf($att, $a['ssn']);
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra['id'], 'code' => 'A'])->assertOk();
        $code = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id']], 'confirmed' => true, 'include_ids' => [$ra['id']]])->assertOk()->json('state.attendance'), $a['ssn'])['confirm']['batchId'];
        $this->postJson(route('slc.registration.att.batch.rejected'), ['code' => $code, 'row_ids' => [$ra['id']]])->assertStatus(422); // only once uploaded
        $file = $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk()->json('file.text');
        $toRecord = $this->postJson(route('slc.registration.att.batch.uploaded'), ['code' => $code])->assertOk()->json('state.attendance.counts.toRecord');

        // SIS's Import Results rejected it: out of the batch, back on step 4 with its code; the batch lists it, its file unchanged
        $att = $this->postJson(route('slc.registration.att.batch.rejected'), ['code' => $code, 'row_ids' => [$ra['id']], 'reason' => 'Confirmation has already been submitted'])->assertOk()->json('state.attendance');
        $row = $this->rowOf($att, $a['ssn']);
        $this->assertSame([null, 'ready', $code, 'A'], [$row['confirm'], $row['codeStage'], $row['sisRejected']['batchId'], $row['sisRejected']['code']]);
        $item = collect(collect($att['batches'])->firstWhere('id', $code)['items'])->firstWhere('rowId', $ra['id']);
        $this->assertSame(['Confirmation has already been submitted', null, 'A'], [$item['rejected']['reason'], $item['rejected']['resent'], $item['code']]);
        $this->assertSame($toRecord - 1, $att['counts']['toRecord']); // no longer to record on the SLC tab
        $this->getJson(route('slc.registration.att.batch.file', ['code' => $code]))->assertOk()->assertJsonPath('text', $file);
        $this->postJson(route('slc.registration.att.batch.attendance'), ['code' => $code, 'row_ids' => [$ra['id']]])->assertStatus(422); // not recorded on the SLC tab

        // marked by mistake: Undo puts it back in the batch
        $att = $this->postJson(route('slc.registration.att.batch.rejected.undo'), ['row_id' => $ra['id']])->assertOk()->json('state.attendance');
        $this->assertSame(['confirmed', $code], [$this->rowOf($att, $a['ssn'])['codeStage'], $this->rowOf($att, $a['ssn'])['confirm']['batchId']]);

        // rejected again and confirmed into a new batch: the first batch shows where it went; Undo no longer applies
        $this->postJson(route('slc.registration.att.batch.rejected'), ['code' => $code, 'row_ids' => [$ra['id']]])->assertOk();
        $att = $this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id']], 'confirmed' => true, 'include_ids' => [$ra['id']]])->assertOk()->json('state.attendance');
        $again = $this->rowOf($att, $a['ssn'])['confirm']['batchId'];
        $this->assertNotSame($code, $again);
        $this->assertSame($again, collect(collect($att['batches'])->firstWhere('id', $code)['items'])->firstWhere('rowId', $ra['id'])['rejected']['resent']);
        $this->postJson(route('slc.registration.att.batch.rejected.undo'), ['row_id' => $ra['id']])->assertStatus(422);
    }

    public function test_the_slc_tab_session_and_attendance_term_can_be_chosen_on_step_4(): void
    {
        $a = $this->withSsn[0];
        $att = $this->uploadReady();
        $ra = $this->rowOf($att, $a['ssn']);
        $rt = $ra['recordTerm'];
        $terms = collect($rt['options'])->keyBy('sessionTerm');
        if (!$terms->has(3) || isset($rt['taken'][3])) {
            $this->markTestSkipped('The sample student has no free Term 3 on their course instance.');
        }
        $this->assertSame(collect([1, 2, 3])->first(fn ($n) => !isset($rt['taken'][$n])), $rt['suggested']['sessionTerm']);
        $set = fn (array $body) => $this->postJson(route('slc.registration.att.term'), ['row_id' => $ra['id']] + $body);

        // Term 3: its Attendance Term follows (the instance's Term 3)
        $row = $this->rowOf($set(['session_term' => 3])->assertOk()->json('state.attendance'), $a['ssn']);
        $this->assertSame([3, $terms[3]['id'], null], [$row['recordTerm']['final']['sessionTerm'], $row['recordTerm']['final']['termDeclarationId'], $row['recordTerm']['problem']]);
        // another attendance term keeps the chosen Session Term
        $other = DB::table('term_declarations')->whereNotIn('id', $terms->pluck('id'))->whereNull('deleted_at')->value('id');
        $row = $this->rowOf($set(['session_term' => 3, 'term_declaration_id' => $other])->assertOk()->json('state.attendance'), $a['ssn']);
        $this->assertSame([3, (int) $other], [$row['recordTerm']['final']['sessionTerm'], $row['recordTerm']['final']['termDeclarationId']]);
        $set(['session_term' => 5])->assertStatus(422);

        // back to Term 3 of the instance; the final step records it so, with Term 3's share (50% of the TFL)
        $set(['session_term' => 3])->assertOk();
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $ra['id'], 'code' => 'A'])->assertOk();
        $code = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra['id']], 'confirmed' => true, 'include_ids' => [$ra['id']]])->assertOk()->json('state.attendance'), $a['ssn'])['confirm']['batchId'];
        $this->postJson(route('slc.registration.att.batch.download'), ['code' => $code])->assertOk();
        $this->postJson(route('slc.registration.att.batch.uploaded'), ['code' => $code])->assertOk();
        $plan = $this->getJson(route('slc.registration.att.batch.preview', ['code' => $code]))->assertOk()->json('rows.'.$ra['id']);
        $this->assertSame([3, $terms[3]['id'], true], [$plan['attendance']['sessionTerm'], $plan['attendance']['termDeclarationId'], $plan['chosen']]);
        if ($plan['installment']) {
            $this->assertEqualsWithDelta(round((float) SlcAttendanceImportRow::find($ra['id'])->data['tfl'] * 0.5, 2), $plan['installment']['amount'], 0.001);
        }

        // a term already on the SLC tab is never overwritten: chosen, the line can't be recorded
        $first = $terms->first();
        \App\Models\SlcAttendance::create(['student_id' => $a['id'], 'student_course_relation_id' => $this->crelOf($a['id']), 'term_declaration_id' => $first['id'],
            'session_term' => $first['sessionTerm'], 'attendance_code_id' => 1, 'confirmation_date' => '2026-10-01', 'created_by' => 1]);
        $row = $this->rowOf($set(['session_term' => $first['sessionTerm']])->assertOk()->json('state.attendance'), $a['ssn']);
        $this->assertStringContainsString('already on the SLC tab', (string) $row['recordTerm']['problem']);
        $this->assertStringContainsString('already on the SLC tab', $this->getJson(route('slc.registration.att.batch.preview', ['code' => $code]))->json('rows.'.$ra['id'].'.problem'));

        // back to the suggestion; once recorded on the SLC tab it can't change here
        $row = $this->rowOf($set([])->assertOk()->json('state.attendance'), $a['ssn']);
        $this->assertSame([null, null], [$row['recordTerm']['chosen']['sessionTerm'], $row['recordTerm']['chosen']['termDeclarationId']]);
        SlcAttendanceImportRow::where('id', $ra['id'])->update(['slc_recorded' => 'created']);
        $set(['session_term' => 3])->assertStatus(422);
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
        $this->postJson(route('slc.registration.att.code'), ['row_id' => $this->rowOf($att, $b['ssn'])['id'], 'code' => 'L'])->assertOk(); // (D can't be chosen: not in SLC's import)
        $first = $this->rowOf($this->postJson(route('slc.registration.att.confirm'), ['row_ids' => [$ra]])->assertOk()->json('state.attendance'), $a['ssn']);
        $code = $first['confirm']['batchId'];

        // the same report again: the confirmation (and its CoC) moves to the new line, the chosen code stays
        $res = $this->upload()->assertOk();
        $this->assertStringContainsString('carried over', $res->json('flash.text'));
        $att = $res->json('state.attendance');
        $ia = $this->rowOf($att, $a['ssn']);
        $this->assertNotSame($ra, $ia['id']);
        $this->assertSame(['confirmed', 'X', $code, false, $first['coc']['id']], [$ia['codeStage'], $ia['confirm']['code'], $ia['confirm']['batchId'], $ia['confirm']['earlier'], $ia['coc']['id']]);
        $this->assertSame('L', $this->rowOf($att, $b['ssn'])['code']['chosen']);
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
        $other = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(200)->get()->first(fn ($u) => empty($u->priv()['slc_registration']) && empty($u->priv()['slc_records'])); // neither privilege opens the page
        $this->actingAs($other)->postJson(route('slc.registration.att.upload'))->assertForbidden();
    }

    public function test_the_sample_layout(): void
    {
        $p = AttendanceFile::parse("LCHC~LONDON CHURCHILL COLLEGE~02112026~1~6350.00~0.00~0.00\r\nAUT~SSN1~DOE~JANE~15041985~~134138~HOSPITALITY~1~6350.00~6350.00~0.00~0.00~F~22102026~NEW~N~N~N~N~\r\n");
        $this->assertSame(['LCHC', '02112026', 1, [6350.0, 0.0, 0.0]], [$p['header']['hei'], $p['header']['date'], $p['header']['count'], $p['header']['totals']]);
        $r = $p['rows'][0];
        $this->assertSame(['AUT', 'SSN1', 'DOE', 'JANE', '15041985', '134138', '1', '6350.00', '0.00', '0.00', 'F', 'NEW', ''], [$r['term'], $r['ssn'], $r['surname'], $r['forename'], $r['dob'], $r['slcCode'], $r['year'], $r['tfl'], $r['tfg'], $r['tfs'], $r['studentStatus'], $r['newRecord'], $r['attendanceCode']]);
        // the import file (spec v9.0 Appendix B): header count and totals recounted for the lines, code in column 28, no footer
        $text = AttendanceFile::build('LCHC~LONDON CHURCHILL COLLEGE~02112026~55~1~1~1', [
            'AUT~SSN1~DOE~JANE~15041985~~134138~HOSPITALITY~1~6350.10~6350.10~0.00~0.20~F~22102026~NEW~N~N~N~N~' => 'A',
            'AUT~SSN2~ROE~JOHN~01011990~~134138~HOSPITALITY~1~9535.00~9534.20~0.00~0.70~F~22102026~NEW~N~N~N~N~' => 'x',
        ], new \DateTime('2026-11-13'));
        $this->assertSame("LCHC~LONDON CHURCHILL COLLEGE~13112026~2~15884.30~0.00~0.90\r\n"
            ."AUT~SSN1~DOE~JANE~15041985~~134138~HOSPITALITY~1~6350.10~6350.10~0.00~0.20~F~22102026~NEW~N~N~N~N~A\r\n"
            ."AUT~SSN2~ROE~JOHN~01011990~~134138~HOSPITALITY~1~9535.00~9534.20~0.00~0.70~F~22102026~NEW~N~N~N~N~X\r\n", $text);
        $this->assertSame('Autumn', AttendanceFile::termName('AUT'));
        $this->expectException(\InvalidArgumentException::class); // a code outside SLC's list never reaches the file
        AttendanceFile::build('LCHC~X~02112026~1~0~0~0', ['AUT~SSN1~DOE~JANE~~~1~C~1~0~0~0~0~F~~~N~N~N~N~' => 'Q'], new \DateTime());
    }
}
