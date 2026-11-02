<?php

namespace Tests\Feature;

use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\SlcPaymentUploadRow;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\User;
use App\Services\Slc\InvoiceNumber;
use App\Services\SlcRegistration\PaymentFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * /slc-registration › Bulk payment upload (user, 2 Nov 2026): Reports › Accounts › Payments Upload
 * rebuilt. The remittance file is built from this install's students that have an SLC agreement
 * with an unpaid instalment; everything runs in a rolled-back transaction.
 */
class SlcPaymentUploadTest extends TestCase
{
    use DatabaseTransactions;

    private array $st = [];
    private string $date = '2026-11-29';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->actingAs(User::find(7));
        // students with one SSN record and an unpaid instalment on an agreement on their active course, whose code is that course's (Reports' checks)
        $cands = DB::table('slc_installments as i')->join('slc_agreements as a', 'a.id', '=', 'i.slc_agreement_id')->join('students as s', 's.id', '=', 'a.student_id')
            ->join('student_course_relations as r', fn ($j) => $j->on('r.id', '=', 'a.student_course_relation_id')->where('r.active', 1)->whereNull('r.deleted_at'))
            ->join('course_creations as cc', 'cc.id', '=', 'r.course_creation_id')
            ->leftJoin('term_declarations as td', 'td.id', '=', 'i.term_declaration_id')->join('term_types as tt', 'tt.id', '=', DB::raw('COALESCE(td.term_type_id, i.term_type_id)'))
            ->whereExists(fn ($q) => $q->from('course_creation_venue as v')->join('course_creations as c2', 'c2.id', '=', 'v.course_creation_id')->whereColumn('v.slc_code', 'a.slc_coursecode')->whereColumn('c2.course_id', 'cc.course_id'))
            ->whereNull('i.deleted_at')->whereNull('a.deleted_at')->whereNull('s.deleted_at')->where(fn ($q) => $q->whereNull('i.slc_money_receipt_id')->orWhere('i.slc_money_receipt_id', 0))
            ->whereNotNull('s.ssn_no')->where('s.ssn_no', '!=', '')->whereNotNull('s.date_of_birth')->where('a.slc_coursecode', '!=', '')->where('i.amount', '>', 0)
            ->orderByDesc('i.id')->limit(400)->get(['s.id', 's.ssn_no', 's.first_name', 's.last_name', 's.date_of_birth', 'a.id as agr', 'a.year', 'a.slc_coursecode', 'a.student_course_relation_id as crel', 'a.course_creation_instance_id as instance', 'i.id as inst', 'i.amount', 'tt.code as term']);
        foreach ($cands as $c) {
            if (count($this->st) >= 3) {
                break;
            }
            if (isset($this->st[$c->id]) || DB::table('students')->where('ssn_no', $c->ssn_no)->whereNull('deleted_at')->count() !== 1
                || DB::table('slc_agreements')->where('student_id', $c->id)->where('year', $c->year)->whereNull('deleted_at')->count() !== 1
                || DB::table('student_course_relations')->where('student_id', $c->id)->where('active', 1)->whereNull('deleted_at')->count() !== 1) {
                continue;
            }
            $this->st[$c->id] = (array) $c;
        }
        $this->st = array_values($this->st);
        if (count($this->st) < 3) {
            $this->markTestSkipped('Too few students with an agreement and an unpaid instalment.');
        }
    }

    private function line(array $s, float $amount, ?int $year = null, string $ssn = '', string $dob = '', string $code = ''): string
    {
        return implode(',', [$s['term'], $ssn ?: strtoupper($s['ssn_no']), strtoupper($s['last_name']), strtoupper($s['first_name']), $dob ?: date('dmY', strtotime($s['date_of_birth'])), '',
            $code ?: $s['slc_coursecode'], 'COURSE NAME-ENG', $year ?? $s['year'], '', number_format($amount, 2, '.', ''), '', '', '', '', 'R']);
    }

    private function remittance(array $lines): string
    {
        $head = implode(',', ['LCHC', 'LONDON CHURCHILL COLLEGE', count($lines), date('dmY', strtotime($this->date)), 'T', '0.00', '', '', '', '', '', '', '', '', '', '']);

        return implode("\r\n", array_merge([$head], $lines))."\r\n";
    }

    /** A: ready · B: ready · C: refund · unknown SSN · B again with a year that has no agreement */
    private function file(): string
    {
        [$a, $b, $c] = $this->st;
        $lines = [$this->line($a, $a['amount']), $this->line($b, $b['amount']), $this->line($c, -125.50),
            $this->line($a, 999.99, null, 'SFZZ99999999Z'), $this->line($b, 77.77, 9)];
        $total = $a['amount'] + $b['amount'] - 125.50 + 999.99 + 77.77;
        $head = implode(',', ['LCHC', 'LONDON CHURCHILL COLLEGE', count($lines), date('dmY', strtotime($this->date)), 'T', number_format($total, 2, '.', ''), '', '', '', '', '', '', '', '', '', '']);

        return implode("\r\n", array_merge([$head], $lines))."\r\n";
    }

    private function upload(?string $text = null, string $name = 'LCHC_2026_FT_T_29112026_remittance.csv')
    {
        $path = sys_get_temp_dir().'/pay-'.md5(($text ?? $this->file()).$name).'-'.$name;
        file_put_contents($path, $text ?? $this->file());

        return $this->post(route('slc.registration.pay.upload'), ['file' => new UploadedFile($path, $name, 'text/csv', null, true)], ['Accept' => 'application/json']);
    }

    private function rowAt(array $pay, int $line): array
    {
        return collect($pay['rows'])->firstWhere('line', $line);
    }

    public function test_the_file_is_read_and_each_payment_matched(): void
    {
        [$a, $b] = $this->st;
        $res = $this->upload()->assertOk();
        $this->assertStringStartsWith('Uploaded 5 payments', $res->json('flash.text'));
        $pay = $res->json('state.payments');
        $this->assertSame([$this->date, 5, true], [$pay['upload']['date'], $pay['upload']['lineCount'], $pay['upload']['totalsOk']]);
        Storage::disk('local')->assertExists(\App\Models\SlcPaymentUpload::find($pay['upload']['id'])->path);

        $ia = $this->rowAt($pay, 2);
        $this->assertSame(['ready', $a['id'], $a['agr'], $a['inst']], [$ia['status'], $ia['s']['sid'], $ia['agreement']['id'], $ia['inst']['id']]);
        // field 3 is the surname (Reports' upload files it as the first name)
        $this->assertSame([strtoupper($a['last_name']), strtoupper($a['first_name'])], [$ia['r']['surname'], $ia['r']['forename']]);
        $this->assertSame('ready', $this->rowAt($pay, 3)['status']);
        $this->assertSame(['ready', 'Refund'], [$this->rowAt($pay, 4)['status'], $this->rowAt($pay, 4)['r']['type']]);
        $this->assertSame(['error', null], [$this->rowAt($pay, 5)['status'], $this->rowAt($pay, 5)['s']]);
        $this->assertSame(['error', 'agreement'], [$this->rowAt($pay, 6)['status'], $this->rowAt($pay, 6)['errorKey']]);
        $this->assertStringStartsWith('No SLC agreement for year 9 on the student’s active course', $this->rowAt($pay, 6)['error']);
        $this->assertSame('nostudent', $this->rowAt($pay, 5)['errorKey']);
        $this->assertSame(['all' => 5, 'ready' => 3, 'error' => 2, 'matched' => 4, 'refunds' => 1], array_intersect_key($pay['counts'], array_flip(['all', 'ready', 'error', 'matched', 'refunds'])));
    }

    public function test_record_makes_money_receipts_and_a_second_upload_does_not_record_them_again(): void
    {
        [$a, , $c] = $this->st;
        $pay = $this->upload()->json('state.payments');
        $highest = InvoiceNumber::highest();
        $res = $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$this->rowAt($pay, 2)['id'], $this->rowAt($pay, 4)['id'], $this->rowAt($pay, 5)['id']]])->assertOk();
        $this->assertStringContainsString('Recorded 2 payments', $res->json('flash.text'));
        $this->assertStringContainsString('1 payment that can’t be recorded yet', $res->json('flash.text'));

        // A: course fee, the next invoice number, the instalment points at it
        $ra = SlcMoneyReceipt::find(SlcPaymentUploadRow::find($this->rowAt($pay, 2)['id'])->slc_money_receipt_id);
        $this->assertSame([(string) ($highest + 1), $a['id'], $a['agr'], 'Course Fee', 2, '29-11-2026', 0], [$ra->invoice_no, (int) $ra->student_id, (int) $ra->slc_agreement_id, $ra->payment_type, (int) $ra->slc_payment_method_id, $ra->payment_date, (int) $ra->force_entry]);
        $this->assertEquals($a['amount'], (float) $ra->amount);
        $this->assertStringEndsWith('Bulk Upload', $ra->remarks);
        $this->assertSame($ra->id, (int) SlcInstallment::find($a['inst'])->slc_money_receipt_id);
        // C: refund — R- invoice (refund_invoice_no kept too), positive amount, no instalment
        $rc = SlcMoneyReceipt::find(SlcPaymentUploadRow::find($this->rowAt($pay, 4)['id'])->slc_money_receipt_id);
        $this->assertSame(['R-'.($highest + 2), 'R-'.($highest + 2), 'Refund', $c['id']], [$rc->invoice_no, $rc->refund_invoice_no, $rc->payment_type, (int) $rc->student_id]);
        $this->assertEquals(125.50, (float) $rc->amount);

        $pay = $res->json('state.payments');
        $this->assertSame(['recorded', 'recorded'], [$this->rowAt($pay, 2)['status'], $this->rowAt($pay, 4)['status']]);
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$this->rowAt($pay, 2)['id']]])->assertStatus(422);

        // the same file again: A and C are already on the account, B is still to record
        $pay = $this->upload()->assertOk()->json('state.payments');
        $this->assertSame(['done', $ra->invoice_no, true], [$this->rowAt($pay, 2)['status'], $this->rowAt($pay, 2)['done']['invoice'], $this->rowAt($pay, 2)['done']['bulk']]);
        $this->assertSame('done', $this->rowAt($pay, 4)['status']);
        $this->assertSame('ready', $this->rowAt($pay, 3)['status']);
        $this->assertSame(2, SlcMoneyReceipt::where('payment_date', $this->date)->whereIn('student_id', [$a['id'], $c['id']])->count());
    }

    public function test_choose_an_agreement_find_a_student_and_skip(): void
    {
        [$a, $b] = $this->st;
        $pay = $this->upload()->json('state.payments');
        $r6 = $this->rowAt($pay, 6);
        $this->assertContains($b['agr'], array_column($r6['choices'], 'id'));
        // an agreement on someone else's account is refused; the student's own is used, as a forced entry
        $this->postJson(route('slc.registration.pay.agreement'), ['row_id' => $r6['id'], 'agreement_id' => $a['agr']])->assertStatus(422);
        $pay = $this->postJson(route('slc.registration.pay.agreement'), ['row_id' => $r6['id'], 'agreement_id' => $b['agr']])->assertOk()->json('state.payments');
        $r6 = $this->rowAt($pay, 6);
        $this->assertContains($r6['status'], ['ready', 'look']);
        $this->assertSame($b['agr'], $r6['agreement']['id']);
        $this->assertNotNull($r6['choice']);
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$r6['id']]])->assertOk();
        $this->assertSame(1, (int) SlcMoneyReceipt::find(SlcPaymentUploadRow::find($r6['id'])->slc_money_receipt_id)->force_entry);

        // the unknown SSN: matched to a student without an SSN, which saves it to their record
        $r5 = $this->rowAt($pay, 5);
        $nobody = Student::whereNull('deleted_at')->where(fn ($q) => $q->whereNull('ssn_no')->orWhere('ssn_no', ''))->orderByDesc('id')->first();
        $this->postJson(route('slc.registration.pay.link'), ['row_id' => $r5['id'], 'student_id' => $a['id']])->assertStatus(422); // holds another SSN
        $pay = $this->postJson(route('slc.registration.pay.link'), ['row_id' => $r5['id'], 'student_id' => $nobody->id])->assertOk()->json('state.payments');
        $this->assertSame('SFZZ99999999Z', $nobody->fresh()->ssn_no);
        $this->assertTrue(StudentArchive::where('student_id', $nobody->id)->where('field_name', 'ssn_no')->where('field_new_value', 'SFZZ99999999Z')->exists());
        $this->assertSame($nobody->id, $this->rowAt($pay, 5)['s']['sid']);

        // skip and reopen
        $r3 = $this->rowAt($pay, 3);
        $pay = $this->postJson(route('slc.registration.pay.skip'), ['row_ids' => [$r3['id']], 'reason' => 'Paid by card'])->assertOk()->json('state.payments');
        $this->assertSame(['skipped', 'Paid by card'], [$this->rowAt($pay, 3)['status'], $this->rowAt($pay, 3)['skip']['reason']]);
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$r3['id']]])->assertStatus(422);
        $pay = $this->postJson(route('slc.registration.pay.unskip'), ['row_id' => $r3['id']])->assertOk()->json('state.payments');
        $this->assertSame('ready', $this->rowAt($pay, 3)['status']);
    }

    public function test_uploads_lists_every_bulk_remittance_with_its_receipts(): void
    {
        [$a] = $this->st;
        $pay = $this->upload()->json('state.payments');
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$this->rowAt($pay, 2)['id']]])->assertOk();
        $rc = SlcMoneyReceipt::find(SlcPaymentUploadRow::find($this->rowAt($pay, 2)['id'])->slc_money_receipt_id);
        // a receipt from Reports' upload for the same student, amount and date: a possible double
        $old = $rc->replicate();
        $old->invoice_no = (string) (InvoiceNumber::highest() + 1);
        $old->remarks = '2026-11-29 10:00:00 Bulk Upload';
        $old->save();

        $d = collect($this->getJson(route('slc.registration.pay.history'))->assertOk()->json('dates'))->firstWhere('date', $this->date);
        $this->assertSame([2, 1, 1], [$d['n'], $d['doubles'], $d['uploadsHere']]);
        $day = $this->getJson(route('slc.registration.pay.history.date', ['date' => $this->date]))->assertOk()->json();
        $mine = collect($day['receipts'])->firstWhere('id', $rc->id);
        $this->assertSame(['here', 2, $a['id'], $a['inst'] ? true : false], [$mine['source'], $mine['double'], $mine['s']['sid'], (bool) $mine['inst']]);
        $this->assertSame('reports', collect($day['receipts'])->firstWhere('id', $old->id)['source']);
        $this->getJson(route('slc.registration.pay.history.date', ['date' => 'yesterday']))->assertStatus(422);
    }

    public function test_payments_waiting_in_reports_are_brought_in_and_recording_one_clears_it_there(): void
    {
        [$a] = $this->st;
        // Reports' upload stores the surname as first_name; this one couldn't find the installment
        $h = \App\Models\SlcPaymentHistory::create(['student_id' => $a['id'], 'transaction_date' => '2026-11-28', 'term_name' => $a['term'], 'ssn' => $a['ssn_no'], 'first_name' => strtoupper($a['last_name']),
            'last_name' => strtoupper($a['first_name']), 'dob' => substr($a['date_of_birth'], 0, 10), 'course_code' => $a['slc_coursecode'], 'course_name' => 'COURSE', 'year' => $a['year'],
            'amount' => $a['amount'], 'status' => 2, 'error_code' => 2, 'errors' => 'Installment Not Found.', 'created_by' => 7]);
        $before = \App\Services\SlcRegistration\PaymentState::legacyWaiting()->count();
        $this->assertGreaterThanOrEqual(1, $before);

        $res = $this->postJson(route('slc.registration.pay.bring.in'))->assertOk();
        $this->assertStringStartsWith('Brought in '.$before, $res->json('flash.text'));
        $pay = $res->json('state.payments');
        $this->assertSame(0, $pay['legacy']['waiting']);
        $i = collect($pay['rows'])->firstWhere('fromReports', true) ? collect($pay['rows'])->first(fn ($x) => $x['s'] && $x['s']['sid'] === $a['id'] && $x['r']['date'] === '2026-11-28') : null;
        $this->assertNotNull($i);
        $this->assertSame([strtoupper($a['last_name']), strtoupper($a['first_name']), 'ready', $a['inst']], [$i['r']['surname'], $i['r']['forename'], $i['status'], $i['inst']['id']]);
        $this->postJson(route('slc.registration.pay.bring.in'))->assertStatus(422); // nothing left to bring

        // recorded here: dated its own remittance date, and gone from Reports' waiting list
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$i['id']]])->assertOk();
        $receipt = SlcMoneyReceipt::find(SlcPaymentUploadRow::find($i['id'])->slc_money_receipt_id);
        $this->assertSame('28-11-2026', $receipt->payment_date);
        $this->assertNull(\App\Models\SlcPaymentHistory::find($h->id));
    }

    public function test_an_earlier_upload_can_be_opened(): void
    {
        $first = $this->upload()->json('state.payments.upload');
        $second = $this->upload($this->file(), 'again.csv')->json('state.payments.upload');
        $this->assertSame([true, 'again.csv'], [$second['newest'], $second['fileName']]);
        $pay = $this->postJson(route('slc.registration.pay.open'), ['id' => $first['id']])->assertOk()->json('state.payments');
        $this->assertSame([$first['id'], false], [$pay['upload']['id'], $pay['upload']['newest']]);
        // it stays open for the next action; recording works on it
        $pay = $this->getJson(route('slc.registration.state'))->json('payments');
        $this->assertSame($first['id'], $pay['upload']['id']);
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$this->rowAt($pay, 3)['id']]])->assertOk();
        $this->assertSame('recorded', $this->rowAt($this->getJson(route('slc.registration.state'))->json('payments'), 3)['status']);
        $this->postJson(route('slc.registration.pay.open'), ['id' => 999999])->assertStatus(422);
    }

    public function test_reports_checks_ssn_and_date_of_birth_course_and_instalments(): void
    {
        [$a, $b] = $this->st;
        // an agreement for year 8 on A's active course with no instalments
        $bare = \App\Models\SlcAgreement::create(['student_id' => $a['id'], 'student_course_relation_id' => $a['crel'], 'course_creation_instance_id' => $a['instance'], 'slc_coursecode' => $a['slc_coursecode'],
            'is_self_funded' => 0, 'date' => '2026-09-01', 'year' => 8, 'fees' => 1000, 'commission_amount' => 0, 'total' => 1000, 'created_by' => 7]);
        $pay = $this->upload($this->remittance([
            $this->line($a, $a['amount'], null, '', '01011901'),          // line 2: A's SSN, another date of birth
            $this->line($a, $a['amount'], null, '', '', 'ZZ9999'),        // line 3: a course code on none of our courses
            $this->line($a, 500, 8),                                     // line 4: the year 8 agreement has no instalments
            $this->line($b, $b['amount']),                               // line 5: all good
        ]), 'checks.csv')->assertOk()->json('state.payments');

        // SSN + date of birth, as Reports: "Student not found", naming the SSN holder
        $l2 = $this->rowAt($pay, 2);
        $this->assertSame(['error', 'dob', null, $a['id']], [$l2['status'], $l2['errorKey'], $l2['s'], $l2['holder']['sid']]);
        $this->assertStringContainsString('date of birth differs (SLC 01/01/1901', $l2['error']);
        // course: Reports' "Course does not match"
        $this->assertSame(['error', 'course'], [$this->rowAt($pay, 3)['status'], $this->rowAt($pay, 3)['errorKey']]);
        // instalments: Reports' "Installment Not Found"
        $this->assertSame(['error', 'inst', $bare->id], [$this->rowAt($pay, 4)['status'], $this->rowAt($pay, 4)['errorKey'], $this->rowAt($pay, 4)['agreement']['id']]);
        $this->assertSame('ready', $this->rowAt($pay, 5)['status']);
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$l2['id'], $this->rowAt($pay, 3)['id'], $this->rowAt($pay, 4)['id']]])->assertStatus(422);

        // staff confirm the holder: recordable, the difference kept as something to look at; Unmatch puts it back
        $pay = $this->postJson(route('slc.registration.pay.link'), ['row_id' => $l2['id'], 'student_id' => $a['id']])->assertOk()->json('state.payments');
        $l2 = $this->rowAt($pay, 2);
        $this->assertSame(['look', $a['id']], [$l2['status'], $l2['s']['sid']]);
        $this->assertContains('dob', array_column($l2['issues'], 'key'));
        $pay = $this->postJson(route('slc.registration.pay.unlink'), ['row_id' => $l2['id']])->assertOk()->json('state.payments');
        $this->assertSame(['error', 'dob'], [$this->rowAt($pay, 2)['status'], $this->rowAt($pay, 2)['errorKey']]);

        // choosing the agreement = Reports' Force Insert: the course and the missing instalments are kept as things to look at
        $pay = $this->postJson(route('slc.registration.pay.agreement'), ['row_id' => $this->rowAt($pay, 3)['id'], 'agreement_id' => $a['agr']])->assertOk()->json('state.payments');
        $this->assertSame('look', $this->rowAt($pay, 3)['status']);
        $this->assertContains('course', array_column($this->rowAt($pay, 3)['issues'], 'key'));
        $pay = $this->postJson(route('slc.registration.pay.agreement'), ['row_id' => $this->rowAt($pay, 4)['id'], 'agreement_id' => $bare->id])->assertOk()->json('state.payments');
        $this->assertSame('look', $this->rowAt($pay, 4)['status']);
        $this->assertContains('inst', array_column($this->rowAt($pay, 4)['issues'], 'key'));
        $this->postJson(route('slc.registration.pay.record'), ['row_ids' => [$this->rowAt($pay, 3)['id'], $this->rowAt($pay, 4)['id']]])->assertOk();
        $this->assertSame([1, 1], SlcPaymentUploadRow::whereIn('id', [$this->rowAt($pay, 3)['id'], $this->rowAt($pay, 4)['id']])->get()->map(fn ($r) => (int) SlcMoneyReceipt::find($r->slc_money_receipt_id)->force_entry)->all());
    }

    public function test_a_term_that_differs_from_the_instalment_is_flagged(): void
    {
        [$a] = $this->st;
        $other = collect(['AUT', 'SPR', 'SUM', 'WIN'])->first(fn ($t) => $t !== $a['term']);
        $pay = $this->upload($this->remittance([$this->line($a, $a['amount']), str_replace($a['term'].',', $other.',', $this->line($a, $a['amount']))]), 'terms.csv')->json('state.payments');
        // same term: ready; another term: still matched to an instalment, flagged
        $this->assertSame('ready', $this->rowAt($pay, 2)['status']);
        $l3 = $this->rowAt($pay, 3);
        $this->assertSame('look', $l3['status']);
        $term = collect($l3['issues'])->firstWhere('key', 'term');
        $this->assertNotNull($term);
        $this->assertStringStartsWith('Term differs: SLC paid for '.PaymentFile::termName($other).' ('.$other.')', $term['text']);
    }

    public function test_a_wrong_file_is_refused_the_file_can_be_downloaded_and_access_is_privileged(): void
    {
        $this->upload("not a remittance file\r\n", 'notes.csv')->assertStatus(422)->assertJsonPath('flash.tone', 'bad');
        $this->upload($this->file(), 'remittance.pdf')->assertStatus(422);
        $pay = $this->upload()->json('state.payments');
        $this->get(route('slc.registration.pay.file', ['id' => $pay['upload']['id']]))->assertOk()->assertDownload('LCHC_2026_FT_T_29112026_remittance.csv');
        $other = User::whereNotIn('id', User::bypassUserIds())->where('active', 1)->orderBy('id')->limit(50)->get()->first(fn ($u) => empty($u->priv()['slc_registration']));
        $this->actingAs($other)->postJson(route('slc.registration.pay.record'), ['row_ids' => [1]])->assertForbidden();
    }

    public function test_the_remittance_layout_and_invoice_numbers(): void
    {
        $this->assertSame('2026-10-21', PaymentFile::date('21102026'));
        $this->assertSame('2026-01-07', PaymentFile::date('7012026')); // Excel dropped the leading zero
        $this->assertSame('', PaymentFile::date('31022026'));
        // the highest invoice number whatever its prefix (a text MAX returns the highest "R-" refund)
        $numeric = (int) DB::table('slc_money_receipts')->get(['invoice_no'])->map(fn ($r) => (int) str_replace('R-', '', $r->invoice_no))->max();
        $this->assertSame($numeric, InvoiceNumber::highest());
        $this->assertSame([$numeric + 1, $numeric + 2], InvoiceNumber::allocate(fn ($next) => [$next(), $next()]));
    }
}
