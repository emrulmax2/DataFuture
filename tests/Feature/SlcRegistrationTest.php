<?php

namespace Tests\Feature;

use App\Models\SlcAgreement;
use App\Models\SlcBulkActivity;
use App\Models\SlcBulkImportRow;
use App\Models\SlcCoc;
use App\Models\SlcRegistration;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\User;
use App\Services\SlcRegistration\RegistrationActions;
use App\Services\SlcRegistration\SisFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * /slc-registration (user, 2 Nov 2026): upload the SIS Registration Confirmation Report, identify,
 * data check, actions, pre-confirm, batches. The upload is the test file built from this install's
 * students (slc-registration:test-file); everything runs in a rolled-back transaction.
 */
class SlcRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    private string $file;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->file = sys_get_temp_dir().'/slc-test-export-'.getmypid().'.txt';
        if (Artisan::call('slc-registration:test-file', ['--out' => $this->file]) !== 0) {
            $this->markTestSkipped('This database has too few students to build a test SLC file.');
        }
        $this->actingAs(User::find(7));
    }

    protected function tearDown(): void
    {
        @unlink($this->file);
        parent::tearDown();
    }

    private function upload(?string $text = null, string $name = 'export.txt')
    {
        $path = $this->file;
        if ($text !== null) {
            $path = $this->file.'.'.md5($text);
            file_put_contents($path, $text);
        }

        return $this->post(route('slc.registration.upload'), ['file' => new UploadedFile($path, $name, 'text/plain', null, true)], ['Accept' => 'application/json']);
    }

    private function rowAt(array $state, int $line): array
    {
        return collect($state['rows'])->firstWhere('line', $line);
    }

    public function test_the_page_has_the_logo_the_signed_in_user_and_back_to_dashboard_and_is_privileged(): void
    {
        $html = $this->get(route('slc.registration'))->assertOk()->getContent();
        $this->assertStringContainsString('id="slr-boot"', $html);
        $boot = json_decode(html_entity_decode(preg_replace('/^.*<script type="application\/json" id="slr-boot">(.*?)<\/script>.*$/s', '$1', $html)), true);
        $this->assertNotEmpty($boot['logo']);
        $this->assertSame(route('staff.dashboard'), $boot['user']['dashboard']);
        $this->assertNotSame('', $boot['user']['name']);

        // someone without the "SLC Registration" privilege
        $other = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(50)->get()
            ->first(fn ($u) => empty($u->priv()['slc_registration']));
        $this->assertNotNull($other, 'needs a staff user without the privilege');
        $this->actingAs($other)->get(route('slc.registration'))->assertForbidden();
        $this->actingAs($other)->getJson(route('slc.registration.state'))->assertForbidden();
        $this->actingAs($other)->postJson(route('slc.registration.upload'))->assertForbidden();
    }

    public function test_upload_matches_by_ssn_and_suggests_the_student_without_one(): void
    {
        $res = $this->upload()->assertOk();
        $this->assertStringStartsWith('Uploaded 18 students. 16 matched by SSN, 2 need identifying.', $res->json('flash.text'));
        $state = $res->json('state');
        $this->assertSame(['all' => 18, 'identify' => 2, 'ssn' => 16], array_intersect_key($state['counts'], array_flip(['all', 'identify', 'ssn'])));
        $this->assertSame('HH26LCHC00001CR', $state['import']['header']['raw']);

        // line 18 is a student we hold without an SSN: Identify puts them first
        $r18 = $this->rowAt($state, 18);
        $this->assertSame('identify', $r18['stage']);
        $this->assertGreaterThanOrEqual(85, $r18['cands'][0]['pct']);
        $this->assertSame(route('student.show', $r18['cands'][0]['s']['sid']), $r18['cands'][0]['s']['url']); // the name opens the profile
        $this->assertSame([], $this->rowAt($state, 19)['cands']); // an SSN nobody holds, a made-up person
        // a partial or failed chip carries what SLC sent and what we hold, for its hover card
        foreach ($r18['cands'] as $cand) {
            foreach ($cand['chips'] as $chip) {
                if ($chip['st'] !== 'ok') {
                    $this->assertArrayHasKey('slc', $chip);
                    $this->assertArrayHasKey('ours', $chip);
                    if ($chip['label'] !== 'Gender') { // Female / Male needs no explanation
                        $this->assertNotSame('', $chip['note'] ?? '', $chip['label'].' needs a reason');
                    }
                }
            }
        }

        // the planted differences and what is suggested for each
        $errs = fn ($line) => collect($this->rowAt($state, $line)['checks'])->where('ok', false)->mapWithKeys(fn ($c) => [$c['key'] => $c['sugg']])->all();
        $this->assertSame(['first' => 'update'], $errs(3));
        $this->assertSame(['tfl' => 'contact'], $errs(4));
        $this->assertSame(['dob' => null], $errs(5)); // swapped day/month waits for an ID check
        $this->assertSame(['postcode' => 'update'], $errs(6));
        $this->assertSame(['start' => 'coc'], $errs(7));
        $this->assertSame(['tfl' => 'coc'], $errs(8));
        $this->assertSame(['title' => 'update'], $errs(9));
        $this->assertSame('precheck', $this->rowAt($state, 2)['stage']);
    }

    public function test_save_ssn_update_record_contact_and_coc(): void
    {
        $state = $this->upload()->json('state');

        // Identify: the SSN goes onto the student, with a history entry
        $r18 = $this->rowAt($state, 18);
        $sid = $r18['cands'][0]['s']['sid'];
        $this->postJson(route('slc.registration.link'), ['row_id' => $r18['id'], 'student_id' => $sid])->assertOk();
        $this->assertSame($r18['r']['ssn'], Student::find($sid)->ssn_no);
        $this->assertTrue(StudentArchive::where('student_id', $sid)->where('field_name', 'ssn_no')->where('field_new_value', $r18['r']['ssn'])->exists());
        $this->postJson(route('slc.registration.link'), ['row_id' => $r18['id'], 'student_id' => $sid])->assertStatus(422)->assertJsonPath('flash.tone', 'bad');

        // Update our record: our first name takes SLC's, recorded twice over
        $r3 = $this->rowAt($state, 3);
        $s3 = Student::find($r3['s']['sid']);
        $res = $this->postJson(route('slc.registration.route'), ['row_id' => $r3['id'], 'key' => 'first', 'route' => 'update'])->assertOk();
        $this->assertSame(\App\Services\SlcRegistration\Text::titleCase($r3['r']['first']), $s3->fresh()->first_name);
        $this->assertTrue(StudentArchive::where('student_id', $s3->id)->where('field_name', 'first_name')->where('field_value', $s3->first_name)->exists());
        $this->assertCount(1, $res->json('state.updates'));
        $this->assertSame('precheck', $this->rowAt($res->json('state'), 3)['stage']);

        // Contact student: contacted → updated with SLC, and they stay on hold
        $r4 = $this->rowAt($state, 4);
        $this->postJson(route('slc.registration.route'), ['row_id' => $r4['id'], 'key' => 'tfl', 'route' => 'contact'])->assertOk();
        $this->postJson(route('slc.registration.action'), ['row_id' => $r4['id'], 'key' => 'tfl', 'state' => 'contacted'])->assertOk();
        $res = $this->postJson(route('slc.registration.action'), ['row_id' => $r4['id'], 'key' => 'tfl', 'state' => 'done'])->assertOk();
        $tfl = collect($this->rowAt($res->json('state'), 4)['checks'])->firstWhere('key', 'tfl');
        $this->assertSame(['contact', 'done'], [$tfl['route'], $tfl['state']['state']]);
        $this->assertSame('action', $this->rowAt($res->json('state'), 4)['stage']);

        // Issue CoC: an slc_cocs row on the student (actioned no), Yes once submitted, gone on Change
        $r7 = $this->rowAt($state, 7);
        $this->postJson(route('slc.registration.route'), ['row_id' => $r7['id'], 'key' => 'start', 'route' => 'coc'])->assertOk();
        $coc = SlcCoc::where('student_id', $r7['s']['sid'])->latest('id')->first();
        $this->assertSame(['Transfer', 'no'], [$coc->coc_type, $coc->actioned]);
        $this->postJson(route('slc.registration.action'), ['row_id' => $r7['id'], 'key' => 'start', 'state' => 'issued'])->assertOk();
        $this->assertSame('Yes', $coc->fresh()->actioned);
        $this->postJson(route('slc.registration.route.clear'), ['row_id' => $r7['id'], 'key' => 'start'])->assertOk();
        $this->assertSoftDeleted('slc_cocs', ['id' => $coc->id]);

        // a route the field does not offer is refused
        $this->postJson(route('slc.registration.route'), ['row_id' => $r7['id'], 'key' => 'start', 'route' => 'accept'])->assertStatus(422);
        $this->assertGreaterThanOrEqual(8, SlcBulkActivity::count());
    }

    public function test_confirm_download_and_mark_uploaded_record_the_registrations(): void
    {
        $state = $this->upload()->json('state');
        $pre = collect($state['rows'])->where('stage', 'precheck')->filter(fn ($i) => $i['ab']['st'] === 'ok' && $i['att']['st'] !== 'bad')->values();
        if ($pre->count() < 2) {
            $this->markTestSkipped('Too few students with an awarding body registration and attendance.');
        }
        $ids = $pre->pluck('id')->all();
        // attended = physical (P) + online (O) + other statuses that count
        foreach ($pre as $i) {
            $a = $i['att']['a'];
            $this->assertSame($a['attended'], $a['physical'] + $a['online'] + $a['other']);
        }
        $warn = $pre->where('att.st', 'warn')->pluck('id')->all();
        if ($warn) {
            // low or stale attendance needs the "I've checked" tick
            $this->postJson(route('slc.registration.confirm'), ['row_ids' => $warn])->assertStatus(422);
        }
        $res = $this->postJson(route('slc.registration.confirm'), ['row_ids' => $ids, 'checked' => $warn])->assertOk();
        $batchOf = fn ($state, $rowIds) => collect($state['batches'])->first(fn ($b) => array_intersect($rowIds, array_column($b['items'], 'rowId')));
        $batch = $batchOf($res->json('state'), $ids);
        $before = count($batch['items']) - count($ids); // students already waiting in this batch on this install
        $this->assertSame(['awaiting', count($ids)], [$batch['status'], count($batch['items']) - $before]);

        $res = $this->postJson(route('slc.registration.batch.download'), ['code' => $batch['id']])->assertOk();
        $text = $res->json('file.text');
        $this->assertSame(SisFile::fileName('HH26LCHC00001CR', $batch['id']), $res->json('file.name'));
        $lines = explode("\r\n", rtrim($text));
        $this->assertSame('HH26LCHC00001CR', $lines[0]);
        $this->assertSame('TT'.str_pad((string) (count($ids) + $before), 6, '0', STR_PAD_LEFT), end($lines));
        $fields = explode('~', $lines[1]);
        $this->assertSame([now()->format('dmY'), 'Y'], [$fields[0], $fields[28]]);
        Storage::disk('local')->assertExists('slc-registration/batches/'.$res->json('file.name'));
        $this->assertSame('downloaded', collect($res->json('state.batches'))->firstWhere('id', $batch['id'])['status']);
        $this->postJson(route('slc.registration.undo'), ['row_id' => $ids[0]])->assertStatus(422)->assertJsonPath('flash.text', 'That batch has already been downloaded.');

        // Bulk SLC Registration needs the batch marked uploaded first
        $this->postJson(route('slc.registration.batch.register'), ['code' => $batch['id'], 'row_ids' => $ids])->assertStatus(422)->assertJsonPath('flash.text', 'Mark the batch uploaded to SIS first.');

        // Mark uploaded only records the upload: no SLC registration yet
        $regsOf = fn () => SlcRegistration::where('note', 'like', '%'.$batch['id'].'%')->whereIn('student_id', $pre->pluck('s.sid'))->get();
        $res = $this->postJson(route('slc.registration.batch.uploaded'), ['code' => $batch['id']])->assertOk();
        $this->assertCount(0, $regsOf());
        $this->assertSame('uploaded', collect($res->json('state.batches'))->firstWhere('id', $batch['id'])['status']);
        $this->postJson(route('slc.registration.batch.uploaded'), ['code' => $batch['id']])->assertStatus(422);

        // the next upload of the same report: those SSNs are already confirmed in that batch
        $res = $this->upload()->assertOk();
        $again = collect($res->json('state.rows'))->whereIn('r.ssn', $pre->pluck('r.ssn'))->values();
        $this->assertSame(['batched'], $again->pluck('stage')->unique()->values()->all());
        $this->assertSame($batch['id'], $again[0]['dec']['batchId']);
    }

    public function test_bulk_slc_registration_creates_the_registration_and_agreement_as_staff_do(): void
    {
        // an uploaded batch
        $state = $this->upload()->json('state');
        $pre = collect($state['rows'])->where('stage', 'precheck')->filter(fn ($i) => $i['ab']['st'] === 'ok' && $i['att']['st'] !== 'bad')->values();
        foreach ($pre as $i) {
            $this->postJson(route('slc.registration.attendance'), ['row_id' => $i['id'], 'checked' => true]);
        }
        $state = $this->postJson(route('slc.registration.confirm'), ['row_ids' => $pre->pluck('id')->all()])->json('state');
        $code = collect($state['batches'])->first(fn ($b) => in_array($pre[0]['id'] ?? 0, array_column($b['items'], 'rowId')))['id'] ?? null;
        if (!$code || $pre->count() < 3) {
            $this->markTestSkipped('Too few students ready to confirm.');
        }
        $this->postJson(route('slc.registration.batch.download'), ['code' => $code])->assertOk();
        $state = $this->postJson(route('slc.registration.batch.uploaded'), ['code' => $code])->assertOk()->json('state');
        $items = collect(collect($state['batches'])->firstWhere('id', $code)['items'])->keyBy('rowId');
        $fresh = $pre->filter(fn ($i) => !$items[$i['id']]['record']['hasReg'] && !$items[$i['id']]['record']['hasAgr'])->values();
        if ($fresh->count() < 3) {
            $this->markTestSkipped('Too few students without an SLC registration for the year.');
        }
        [$a, $b, $c] = [$fresh[0], $fresh[1], $fresh[2]];
        $yearId = \App\Models\SlcBulkImport::latest('id')->value('academic_year_id');
        $snap = \App\Services\SlcRegistration\StudentSnapshots::load([$a['s']['sid'], $b['s']['sid'], $c['s']['sid']], $yearId);
        $year = fn ($i) => (int) $i['r']['year'];

        // B was registered by hand (registration + agreement), C has only an agreement for the year
        $sb = $snap[$b['s']['sid']];
        $manual = SlcRegistration::create(['student_id' => $sb['id'], 'student_course_relation_id' => $sb['crel_id'], 'course_creation_instance_id' => $sb['instance']['id'], 'ssn' => $sb['ssn'], 'confirmation_date' => '2026-10-31', 'academic_year_id' => $yearId, 'registration_year' => $year($b), 'slc_registration_status_id' => 1, 'note' => '', 'created_by' => 7]);
        SlcAgreement::create(['student_id' => $sb['id'], 'student_course_relation_id' => $sb['crel_id'], 'course_creation_instance_id' => $sb['instance']['id'], 'slc_registration_id' => $manual->id, 'slc_coursecode' => $sb['slc_code'], 'is_self_funded' => 0, 'date' => '2026-10-31', 'year' => $year($b), 'fees' => 6350, 'total' => 6350, 'created_by' => 7]);
        $sc = $snap[$c['s']['sid']];
        $cAgreement = SlcAgreement::create(['student_id' => $sc['id'], 'student_course_relation_id' => $sc['crel_id'], 'course_creation_instance_id' => $sc['instance']['id'], 'slc_coursecode' => $sc['slc_code'], 'is_self_funded' => 0, 'date' => '2026-10-30', 'year' => $year($c), 'fees' => 6350, 'total' => 6350, 'created_by' => 7]);
        $attendances = \App\Models\SlcAttendance::count();
        $installments = \App\Models\SlcInstallment::count();

        // B and C already have something for the year: the page asks, student by student (409, the list)
        $res = $this->postJson(route('slc.registration.batch.register'), ['code' => $code, 'row_ids' => [$a['id'], $b['id'], $c['id']]])->assertStatus(409);
        $listed = array_column($res->json('existing'), 'rowId');
        sort($listed);
        $expected = [$b['id'], $c['id']];
        sort($expected);
        $this->assertSame($expected, $listed);
        $this->assertSame(1, $res->json('others'));
        $this->assertSame(0, SlcRegistration::where('student_id', $a['s']['sid'])->where('academic_year_id', $yearId)->count()); // nothing done yet

        // permission for C only
        $res = $this->postJson(route('slc.registration.batch.register'), ['code' => $code, 'row_ids' => [$a['id'], $b['id'], $c['id']], 'confirmed' => true, 'overwrite_ids' => [$c['id']]])->assertOk();
        $items = collect(collect($res->json('state.batches'))->firstWhere('id', $code)['items'])->keyBy('rowId');
        $this->assertSame(['created', null, 'created_anyway'], [$items[$a['id']]['record']['status'], $items[$b['id']]['record']['status'], $items[$c['id']]['record']['status']]);

        // A: registration as the SLC tab makes it…
        $sa = $snap[$a['s']['sid']];
        $uploaded = \App\Models\SlcBulkBatch::where('code', $code)->first()->uploaded_at->toDateString();
        $reg = SlcRegistration::where('student_id', $sa['id'])->where('academic_year_id', $yearId)->sole();
        $this->assertSame([$sa['crel_id'], $sa['instance']['id'], $sa['ssn'], $uploaded, $year($a), 1], [(int) $reg->student_course_relation_id, (int) $reg->course_creation_instance_id, $reg->ssn, $reg->getRawOriginal('confirmation_date'), (int) $reg->registration_year, (int) $reg->slc_registration_status_id]);
        // …and the agreement as the Accounts tab makes it (fee less the university commission)
        $agr = SlcAgreement::where('slc_registration_id', $reg->id)->sole();
        $inst = \App\Models\CourseCreationInstance::with('creation')->find($sa['instance']['id']);
        $pct = ($inst->university_commission ?? 0) > 0 ? (float) $inst->university_commission : (float) ($inst->creation->university_commission ?? 0);
        $commission = round($inst->fees * $pct / 100, 2);
        $this->assertEquals([$sa['crel_id'], $sa['instance']['id'], $sa['slc_code'], 0, $uploaded, $year($a), $commission, $inst->fees - $commission, 0, $inst->fees - $commission],
            [(int) $agr->student_course_relation_id, (int) $agr->course_creation_instance_id, $agr->slc_coursecode, (int) $agr->is_self_funded, $agr->getRawOriginal('date'), (int) $agr->year, (float) $agr->commission_amount, (float) $agr->fees, (float) $agr->discount, (float) $agr->total]);

        // C (permitted): a new registration and a new agreement; their earlier agreement is left as it was
        $cReg = SlcRegistration::where('student_id', $sc['id'])->where('academic_year_id', $yearId)->sole();
        $this->assertSame(1, SlcAgreement::where('slc_registration_id', $cReg->id)->count());
        $this->assertNull($cAgreement->fresh()->slc_registration_id);
        $this->assertSame(2, SlcAgreement::where('student_id', $sc['id'])->where('year', $year($c))->count());

        // B (not permitted): untouched, and can be ticked again — permission now: a second registration and agreement, the first kept
        $this->assertSame(1, SlcRegistration::where('student_id', $sb['id'])->where('academic_year_id', $yearId)->count());
        $this->postJson(route('slc.registration.batch.register'), ['code' => $code, 'row_ids' => [$b['id']]])->assertStatus(409);
        $this->postJson(route('slc.registration.batch.register'), ['code' => $code, 'row_ids' => [$b['id']], 'confirmed' => true, 'overwrite_ids' => [$b['id']]])->assertOk();
        $this->assertSame(2, SlcRegistration::where('student_id', $sb['id'])->where('academic_year_id', $yearId)->count());
        $this->assertNotNull($manual->fresh());

        // no SLC attendance or instalment; and a student done is not done again
        $this->assertSame([$attendances, $installments], [\App\Models\SlcAttendance::count(), \App\Models\SlcInstallment::count()]);
        $this->postJson(route('slc.registration.batch.register'), ['code' => $code, 'row_ids' => [$a['id'], $b['id'], $c['id']]])->assertOk()->assertJsonPath('flash.text', 'No new SLC registrations.');
        $this->assertSame(1, SlcRegistration::where('student_id', $sa['id'])->where('academic_year_id', $yearId)->count());
    }

    public function test_a_newer_upload_keeps_all_the_work_done_on_the_earlier_one(): void
    {
        $state = $this->upload()->json('state');
        $at = fn ($st, $line) => collect($st['rows'])->firstWhere('line', $line);
        $field = fn ($st, $line, $key) => collect($at($st, $line)['checks'])->firstWhere('key', $key);
        $post = fn ($name, $data) => $this->postJson(route($name), $data)->assertOk()->json('state');

        // the work on the first file
        $post('slc.registration.route', ['row_id' => $at($state, 5)['id'], 'key' => 'dob', 'route' => 'contact']);
        $post('slc.registration.action', ['row_id' => $at($state, 5)['id'], 'key' => 'dob', 'state' => 'contacted']); // asked the student to fix their DOB with SLC
        $post('slc.registration.route', ['row_id' => $at($state, 9)['id'], 'key' => 'title', 'route' => 'accept']);
        $post('slc.registration.route', ['row_id' => $at($state, 8)['id'], 'key' => 'tfl', 'route' => 'coc']);        // CoC to issue, not yet in SIS
        $post('slc.registration.route', ['row_id' => $at($state, 7)['id'], 'key' => 'start', 'route' => 'coc']);
        $post('slc.registration.action', ['row_id' => $at($state, 7)['id'], 'key' => 'start', 'state' => 'issued']);   // CoC submitted in SIS
        $post('slc.registration.route', ['row_id' => $at($state, 6)['id'], 'key' => 'postcode', 'route' => 'update']);
        $post('slc.registration.decide', ['row_id' => $at($state, 19)['id'], 'd' => 'hold', 'reason' => 'student not identified']);
        $post('slc.registration.decide', ['row_id' => $at($state, 4)['id'], 'd' => 'notreg']);
        $r18 = $at($state, 18);
        $state = $post('slc.registration.link', ['row_id' => $r18['id'], 'student_id' => $r18['cands'][0]['s']['sid']]);
        $cocTfl = SlcBulkImportRow::find($at($state, 8)['id'])->fields->first()->slc_coc_id;
        $cocStart = SlcBulkImportRow::find($at($state, 7)['id'])->fields->first()->slc_coc_id;
        $cocsBefore = SlcCoc::count();

        $pre = collect($state['rows'])->where('stage', 'precheck')->filter(fn ($i) => $i['ab']['st'] === 'ok' && $i['att']['st'] !== 'bad')->values();
        if ($pre->count() < 2) {
            $this->markTestSkipped('Too few students ready to confirm.');
        }
        [$ticked, $confirmed] = [$pre[0], $pre[1]];
        $post('slc.registration.attendance', ['row_id' => $ticked['id'], 'checked' => true]);
        $post('slc.registration.attendance', ['row_id' => $confirmed['id'], 'checked' => true]);
        $state = $post('slc.registration.confirm', ['row_ids' => [$confirmed['id']]]);
        $batch = collect($state['batches'])->first(fn ($b) => in_array($confirmed['id'], array_column($b['items'], 'rowId')));

        // the same report uploaded again: everything comes across
        $res = $this->upload()->assertOk();
        $this->assertStringContainsString('Carried over from earlier uploads', $res->json('flash.text'));
        $new = $res->json('state');
        $this->assertNotSame($state['import']['id'], $new['import']['id']);
        $this->assertSame(['contact', 'contacted', true], [$field($new, 5, 'dob')['route'], $field($new, 5, 'dob')['state']['state'], $field($new, 5, 'dob')['carried']]);
        $this->assertSame('accept', $field($new, 9, 'title')['route']);
        $this->assertSame(['coc', null], [$field($new, 8, 'tfl')['route'], $field($new, 8, 'tfl')['state']]);
        $this->assertSame(['coc', 'issued'], [$field($new, 7, 'start')['route'], $field($new, 7, 'start')['state']['state']]);
        $this->assertSame($cocTfl, SlcBulkImportRow::find($at($new, 8)['id'])->fields->first()->slc_coc_id); // the same CoC
        $this->assertSame($cocStart, SlcBulkImportRow::find($at($new, 7)['id'])->fields->first()->slc_coc_id);
        $this->assertSame($cocsBefore, SlcCoc::count());
        $this->assertSame(['held', true], [$at($new, 19)['stage'], $at($new, 19)['dec']['carried']]);
        $this->assertSame('notreg', $at($new, 4)['stage']);
        $this->assertSame('linked', $at($new, 18)['link']['method']);
        $this->assertTrue(collect($new['rows'])->firstWhere('r.ssn', $ticked['r']['ssn'])['attChecked']);
        $moved = collect($new['rows'])->firstWhere('r.ssn', $confirmed['r']['ssn']);
        $this->assertSame('batched', $moved['stage']);
        $newBatch = collect($new['batches'])->firstWhere('id', $batch['id']);
        $this->assertContains($moved['id'], array_column($newBatch['items'], 'rowId')); // the batch now sends the new file's line
        $this->assertNotContains($confirmed['id'], array_column($newBatch['items'], 'rowId'));
        $this->assertSame(count($batch['items']), count($newBatch['items']));
        $this->assertNull(SlcBulkImportRow::find($confirmed['id'])->slc_bulk_batch_id);
        $this->assertNotEmpty(collect($new['updates'])->where('field', 'Postcode')->all()); // record changes still listed

        // a later report: the student fixed their DOB with SLC; SLC changed the TFL to another wrong amount
        $lines = explode("\r\n", rtrim(file_get_contents($this->file)));
        $dob = Student::find($at($new, 5)['s']['sid'])->date_of_birth;
        $f5 = explode('~', $lines[4]);
        $f5[7] = date('dmY', strtotime($dob));
        $lines[4] = implode('~', $f5);
        $f8 = explode('~', $lines[7]);
        $f8[18] = '1000.00';
        $lines[7] = implode('~', $f8);
        $later = $this->upload(implode("\r\n", $lines)."\r\n", 'later.txt')->assertOk()->json('state');

        $this->assertTrue($field($later, 5, 'dob')['ok']);
        $h5 = collect($at($later, 5)['history'])->firstWhere('field', 'dob');
        $this->assertSame(['contact', 'contacted', 'matches'], [$h5['route'], $h5['state'], $h5['now']]);
        $this->assertNull($field($later, 8, 'tfl')['route']); // decided again: SLC's value changed
        $h8 = collect($at($later, 8)['history'])->firstWhere('field', 'tfl');
        $this->assertSame(['coc', 'changed', '£1,000.00'], [$h8['route'], $h8['now'], $h8['nowValue']]);
        $this->assertSame(['contact', 'contacted'], [collect($at($later, 5)['history'])->firstWhere('field', 'dob')['route'], $h5['state']]); // the trail is kept

        // raising the CoC again uses the one not yet submitted, not a second one
        $post('slc.registration.route', ['row_id' => $at($later, 8)['id'], 'key' => 'tfl', 'route' => 'coc']);
        $this->assertSame($cocTfl, SlcBulkImportRow::find($at($later, 8)['id'])->fields->first()->slc_coc_id);
        $this->assertSame($cocsBefore, SlcCoc::count());
    }

    public function test_apply_suggested_for_one_field_only(): void
    {
        $state = $this->upload()->assertOk()->json('state');
        // the lines with an undecided suggestion, and a field one of them suggests for
        $open = fn ($state) => collect($state['rows'])->filter(fn ($i) => $i['s'])->flatMap(fn ($i) => collect($i['checks'])->filter(fn ($c) => !$c['ok'] && !$c['route'] && $c['sugg'])->map(fn ($c) => $c['key'].'@'.$i['id']))->values();
        $before = $open($state);
        if ($before->isEmpty()) {
            $this->markTestSkipped('The test file has no suggested decisions.');
        }
        $key = explode('@', $before[0])[0];
        $ids = collect($state['rows'])->filter(fn ($i) => $i['s'])->pluck('id')->all();
        $state = $this->postJson(route('slc.registration.suggested'), ['row_ids' => $ids, 'key' => $key])->assertOk()->json('state');
        $after = $open($state);
        // that field is decided on every line; every other suggestion is still open
        $this->assertSame([], $after->filter(fn ($k) => str_starts_with($k, $key.'@'))->values()->all());
        $this->assertEqualsCanonicalizing($before->reject(fn ($k) => str_starts_with($k, $key.'@'))->values()->all(), $after->all());
        $this->postJson(route('slc.registration.suggested'), ['row_ids' => $ids, 'key' => 'nonsense'])->assertStatus(422);
    }

    public function test_hold_not_registered_and_course_mapping(): void
    {
        $state = $this->upload()->json('state');
        $r19 = $this->rowAt($state, 19);
        $res = $this->postJson(route('slc.registration.decide'), ['row_id' => $r19['id'], 'd' => 'hold', 'reason' => 'student not identified'])->assertOk();
        $this->assertSame('held', $this->rowAt($res->json('state'), 19)['stage']);
        $res = $this->postJson(route('slc.registration.undo'), ['row_id' => $r19['id']])->assertOk();
        $this->assertSame('identify', $this->rowAt($res->json('state'), 19)['stage']);
        $res = $this->postJson(route('slc.registration.decide'), ['row_id' => $r19['id'], 'd' => 'notreg'])->assertOk();
        $this->assertSame(1, $res->json('state.counts.notreg'));

        $venue = DB::table('course_creation_venue')->whereNull('deleted_at')->orderByDesc('id')->value('id');
        $this->postJson(route('slc.registration.course.map'), ['code' => 'zz9999', 'venue_id' => $venue])->assertOk();
        $this->assertSame('ZZ9999', DB::table('course_creation_venue')->where('id', $venue)->value('slc_code'));
    }

    public function test_the_dashboard_box_opens_the_duplicate_agreements_page(): void
    {
        $state = $this->upload()->json('state');
        $this->assertSame($state['counts'], $state['dashboard']['registration']['counts']);
        $this->assertNotNull($state['dashboard']['year']);
        $box = $state['dashboard']['duplicates'];
        $this->assertSame(route('slc.registration.multiple'), $box['url']);
        $this->assertSame(\App\Services\SlcRegistration\RegistrationState::duplicateAgreementGroups()->count(), $box['count']);

        // give a student a second agreement for a year (and course) they already have one for
        $agr = SlcAgreement::whereNull('deleted_at')->whereNotNull('student_course_relation_id')
            ->whereNotIn('student_id', \App\Services\SlcRegistration\RegistrationState::duplicateAgreementGroups()->pluck('student_id'))->latest('id')->first();
        SlcAgreement::create(['student_id' => $agr->student_id, 'student_course_relation_id' => $agr->student_course_relation_id, 'course_creation_instance_id' => $agr->course_creation_instance_id, 'slc_coursecode' => $agr->slc_coursecode, 'is_self_funded' => 0, 'date' => now()->toDateString(), 'year' => $agr->year, 'fees' => 100, 'total' => 100, 'created_by' => 7]);
        $this->assertSame($box['count'] + 1, $this->getJson(route('slc.registration.state'))->json('dashboard.duplicates.count'));

        // the page lists them, newest first; the name opens the Accounts tab
        $html = $this->get(route('slc.registration.multiple'))->assertOk()->getContent();
        $boot = json_decode(html_entity_decode(preg_replace('/^.*<script type="application\/json" id="slr-boot">(.*?)<\/script>.*$/s', '$1', $html)), true);
        $this->assertSame('multiple', $boot['page']);
        $rows = $boot['multiple']['students'];
        $this->assertCount($box['count'] + 1, $rows);
        $this->assertSame([(int) $agr->student_id, (int) $agr->year, 2], [$rows[0]['studentId'], $rows[0]['year'], $rows[0]['count']]);
        $this->assertSame(route('student.accounts', $agr->student_id), $rows[0]['url']);
        $this->assertCount(2, $rows[0]['agreements']);

        $other = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(50)->get()->first(fn ($u) => empty($u->priv()['slc_registration']));
        $this->actingAs($other)->get(route('slc.registration.multiple'))->assertForbidden();
    }

    public function test_reviewed_and_hide_takes_a_duplicate_off_the_list_and_the_count_until_it_changes(): void
    {
        $groups = fn ($hidden = false) => \App\Services\SlcRegistration\RegistrationState::duplicateAgreementGroups($hidden);
        $before = $groups()->count();
        $agr = SlcAgreement::whereNull('deleted_at')->whereNotNull('student_course_relation_id')->whereNotIn('student_id', $groups()->pluck('student_id'))->latest('id')->first();
        $make = fn () => SlcAgreement::create(['student_id' => $agr->student_id, 'student_course_relation_id' => $agr->student_course_relation_id, 'course_creation_instance_id' => $agr->course_creation_instance_id, 'slc_coursecode' => $agr->slc_coursecode, 'is_self_funded' => 0, 'date' => now()->toDateString(), 'year' => $agr->year, 'fees' => 100, 'total' => 100, 'created_by' => 7]);
        $second = $make();
        $row = collect(\App\Services\SlcRegistration\RegistrationState::duplicateAgreements())->first(fn ($r) => $r['studentId'] === (int) $agr->student_id && $r['year'] === (int) $agr->year);
        $this->assertSame([(int) $agr->id, $second->id], $row['agreementIds']);
        $body = ['student_id' => $row['studentId'], 'crel_id' => $row['crelId'], 'year' => $row['year'], 'agreement_ids' => $row['agreementIds']];

        // agreements the page didn't show are refused; the ones it showed are reviewed & hidden
        $this->postJson(route('slc.registration.multiple.review'), ['agreement_ids' => [$agr->id, 999999999]] + $body)->assertStatus(422);
        $res = $this->postJson(route('slc.registration.multiple.review'), $body)->assertOk();
        $this->assertStringStartsWith('Reviewed & hidden', $res->json('flash.text'));
        $this->assertFalse(collect($res->json('multiple.students'))->contains(fn ($r) => $r['studentId'] === (int) $agr->student_id && $r['year'] === (int) $agr->year));
        $hidden = collect($res->json('multiple.hidden'))->first(fn ($r) => $r['studentId'] === (int) $agr->student_id);
        $this->assertTrue($hidden['review']['hidden']);
        $this->assertNotSame('', $hidden['review']['by']);
        $this->assertSame($before, $this->getJson(route('slc.registration.state'))->json('dashboard.duplicates.count'));
        $this->assertSame(1, $groups(true)->where('student_id', $agr->student_id)->count());

        // a third agreement for that year: back on the list, flagged as changed since the review
        $make();
        $back = collect(\App\Services\SlcRegistration\RegistrationState::duplicateAgreements())->first(fn ($r) => $r['studentId'] === (int) $agr->student_id && $r['year'] === (int) $agr->year);
        $this->assertSame([3, true], [$back['count'], $back['review']['changed']]);
        $this->assertSame($before + 1, $groups()->count());

        // reviewed again, then unhidden
        $this->postJson(route('slc.registration.multiple.review'), ['agreement_ids' => $back['agreementIds']] + $body)->assertOk();
        $this->assertSame($before, $groups()->count());
        $res = $this->postJson(route('slc.registration.multiple.unhide'), array_diff_key($body, ['agreement_ids' => 1]))->assertOk();
        $this->assertTrue(collect($res->json('multiple.students'))->contains(fn ($r) => $r['studentId'] === (int) $agr->student_id && $r['year'] === (int) $agr->year));
        $this->assertSame($before + 1, $groups()->count());
    }

    public function test_sis_file_and_slc_address_parts(): void
    {
        $p = SisFile::parse("HH26LCHC00001CR\r\n01012026~26~Mr~JOHN~~SMITH~M~01011990~~~123~X~1~07092026~14 GREEN LANE   LONDON ~N4 1AA~SSN1~6350.00~6350.00\r\nbad~line\r\n");
        $this->assertSame(['26', 'LCHC'], [$p['header']['ay'], $p['header']['hei']]);
        $this->assertCount(1, $p['rows']);
        $this->assertSame(['Line 3 has 2 fields (expected 30)'], $p['errors']);
        $this->assertSame('14 GREEN LANE LONDON', $p['rows'][0]['address']);
        $out = SisFile::build('HH26LCHC00001CR', [$p['rows'][0]['raw']], new \DateTime('2026-11-02'));
        $line = explode("\r\n", $out)[1];
        $this->assertCount(30, explode('~', $line));
        $this->assertStringStartsWith('02112026~26~Mr~JOHN', $line);
        $this->assertStringContainsString('14 GREEN LANE   LONDON ', $line); // SLC's own spacing kept

        $this->assertSame(['line1' => '14', 'line2' => 'GREEN LANE', 'city' => 'LONDON'], RegistrationActions::addressParts('14 GREEN LANE   LONDON '));

        // our first name holding the middle names too ("Genowefa Natalia") matches SLC's first + other names
        $this->assertTrue(\App\Services\SlcRegistration\Checks::firstNameMatch('GENOWEFA', 'NATALIA BRZEZINSKA', 'Genowefa Natalia'));
        $this->assertFalse(\App\Services\SlcRegistration\Checks::firstNameMatch('GENOWEFA', 'NATALIA', 'Genowefa Maria'));
        $this->assertFalse(\App\Services\SlcRegistration\Checks::firstNameMatch('ANASTAZJA', '', 'Genowefa'));
        $chips = collect(\App\Services\SlcRegistration\Checks::score(
            ['first' => 'GENOWEFA', 'other' => 'NATALIA', 'last' => 'BRZEZINSKA', 'gender' => 'F', 'dob' => '08021991', 'postcode' => 'IG3 9RA', 'address' => '47 HAZELDENE ROAD   ILFORD ESSEX', 'slcCode' => '982056', 'courseName' => 'BUSINESS'],
            ['first' => 'Genowefa Natalia', 'last' => 'Brzezinska', 'gender' => 'F', 'dob' => '1991-02-08', 'slc_code' => '982056', 'course_name' => 'HND in Business', 'addresses' => [['type' => 'permanent', 'id' => 1, 'text' => '47, Hazeldene Road, Ilford', 'postcode' => 'IG3 9RA']]],
            true
        )['chips'])->keyBy('label');
        $this->assertSame('ok', $chips['First name']['st']);
        $this->assertSame(['part', 'Only SLC has: Essex'], [$chips['Address']['st'], $chips['Address']['note']]);
        $this->assertSame("47, Hazeldene Road, Ilford, IG3\u{00A0}9RA", $chips['Address']['ours']);
        $this->assertSame(['line1' => 'FLAT 2', 'line2' => '14 GREEN LANE', 'city' => 'LONDON'], RegistrationActions::addressParts('FLAT 2, 14 GREEN LANE   LONDON'));
    }
}
