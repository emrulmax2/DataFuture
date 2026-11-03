<?php

namespace App\Services\SlcRegistration;

use App\Models\SlcBulkActivity;
use App\Models\SlcInstallment;
use App\Models\SlcMoneyReceipt;
use App\Models\SlcPaymentUpload;
use App\Models\SlcPaymentUploadRow;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Services\Slc\InvoiceNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * /slc-registration › Bulk payment upload (user, 2 Nov 2026): Reports › Accounts › Payments Upload
 * rebuilt on this page. Upload the SLC remittance file (kept), match each payment (PaymentState),
 * sort what can't be matched (find the student, choose the agreement, skip), and record the ticked
 * payments as money receipts — as Payments Upload does: Bank, payment date = remittance date,
 * "Course Fee" or "Refund" (R- invoice), remark "… Bulk Upload", the instalment pointed at its
 * receipt. Every action goes to the shared Activity log.
 */
class PaymentActions
{
    private function user(): int
    {
        return (int) auth()->id();
    }

    private function log(string $text): void
    {
        SlcBulkActivity::create(['slc_bulk_import_id' => null, 'user_id' => $this->user(), 'text' => 'Bulk payment – '.$text]);
    }

    private function fail(string $message): never
    {
        throw new SlcRegistrationException($message);
    }

    public function import(string $path, string $fileName): string
    {
        if (!in_array(strtolower(pathinfo($fileName, PATHINFO_EXTENSION)), \App\Services\Slc\RemittanceFile::EXTENSIONS, true)) {
            $this->fail('Upload the SLC remittance file (.dat, or a .csv / .txt saved from it).');
        }
        $p = PaymentFile::parse($path);
        if (!$p['header']) {
            $this->fail('That file has no remittance header (HEI ~ name ~ count ~ date ~ type ~ total). Upload the remittance file SLC sent.');
        }
        if (!$p['lines']) {
            $this->fail('No payment lines were found in that file.');
        }
        $h = $p['header'];
        $text = (string) file_get_contents($path);
        $disk = FileDisk::name();
        $stored = 'slc-registration/payments/'.now()->format('Ymd_His').'_'.Str::slug(pathinfo($fileName, PATHINFO_FILENAME)).'.'.strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        Storage::disk($disk)->put($stored, $text);
        $match = self::matchStudents($p['lines']);
        $total = round(array_sum(array_column($p['lines'], 'amount')), 2);

        $up = DB::transaction(function () use ($p, $h, $fileName, $disk, $stored, $match, $total) {
            $up = SlcPaymentUpload::create([
                'file_name' => $fileName, 'disk' => $disk, 'path' => $stored, 'header' => $h['raw'], 'hei' => $h['hei'], 'hei_name' => $h['heiName'], 'remittance_date' => $h['date'],
                'header_count' => $h['count'], 'header_total' => $h['total'], 'line_count' => count($p['lines']), 'line_total' => $total, 'error_lines' => $p['errors'], 'created_by' => $this->user(),
            ]);
            foreach (array_chunk($p['lines'], 200) as $chunk) {
                $now = now();
                SlcPaymentUploadRow::insert(array_map(fn ($l) => [
                    'slc_payment_upload_id' => $up->id, 'line_no' => $l['line'], 'raw' => $l['raw'], 'data' => json_encode(array_diff_key($l, ['raw' => 1, 'line' => 1])),
                    'ssn' => $l['ssn'], 'amount' => $l['amount'], 'student_id' => $match[$l['line']] ?? null, 'match_method' => isset($match[$l['line']]) ? 'ssn' : null,
                    'created_at' => $now, 'updated_at' => $now,
                ], $chunk));
            }

            return $up;
        });
        session([PaymentState::OPEN_KEY => $up->id]); // the new upload is the one open

        $n = count($p['lines']);
        $matched = count(array_filter($match));
        $warn = $h['count'] !== $n || abs($h['total'] - $total) >= 0.005
            ? ' The header says '.$h['count'].' payments, '.Text::money($h['total']).'; the file has '.$n.', '.Text::money($total).'.' : '';
        $this->log('uploaded '.$fileName.' (remittance '.Text::fmtIso($h['date']).') – '.$n.' payments, '.Text::money($total).', '.$matched.' with an SSN on our records.'.$warn);

        return 'Uploaded '.$n.' payments ('.Text::money($total).'). Each is checked against our records: SSN and date of birth, course, agreement and instalment.'.$warn;
    }

    /**
     * line no → the record holding the line's SSN (compared without case). An SSN on more than one
     * record (returning students) goes to the one with the line's date of birth, then an agreement for
     * the line's SLC code, then an active course, then the newest. Whether the date of birth matches
     * is checked on every load (PaymentState), so a corrected date of birth matches straight away.
     */
    public static function matchStudents(array $lines): array
    {
        $ssns = array_values(array_unique(array_column($lines, 'ssn')));
        $holders = collect();
        foreach (array_chunk($ssns, 500) as $chunk) {
            $holders = $holders->merge(DB::table('students')->whereIn('ssn_no', $chunk)->whereNull('deleted_at')->orderByDesc('id')->get(['id', 'ssn_no', 'date_of_birth']));
        }
        $bySsn = $holders->groupBy(fn ($s) => strtoupper(trim((string) $s->ssn_no)));
        $multi = $bySsn->filter(fn ($g) => $g->count() > 1)->flatten(1)->pluck('id')->all();
        $codes = $multi ? DB::table('slc_agreements')->whereIn('student_id', $multi)->whereNull('deleted_at')->get(['student_id', 'slc_coursecode'])
            ->groupBy('student_id')->map(fn ($g) => $g->pluck('slc_coursecode')->map(fn ($c) => strtoupper((string) $c))->all()) : collect();
        $active = $multi ? DB::table('student_course_relations')->whereIn('student_id', $multi)->where('active', 1)->whereNull('deleted_at')->pluck('id', 'student_id') : collect();
        $out = [];
        foreach ($lines as $l) {
            $g = $bySsn->get($l['ssn']);
            if (!$g) {
                continue;
            }
            $byDob = $g->filter(fn ($s) => ($l['dob'] ?? '') !== '' && substr((string) $s->date_of_birth, 0, 10) === $l['dob']);
            $g = $byDob->isNotEmpty() ? $byDob->values() : $g;
            $pick = $g->count() === 1 ? $g->first()
                : ($g->first(fn ($s) => in_array($l['slcCode'], $codes[$s->id] ?? [], true)) ?? $g->first(fn ($s) => isset($active[$s->id])) ?? $g->first());
            $out[$l['line']] = (int) $pick->id;
        }

        return $out;
    }

    private function row(int $id): SlcPaymentUploadRow
    {
        $row = SlcPaymentUploadRow::find($id) ?? $this->fail('That payment is no longer in the upload.');
        if ($row->slc_money_receipt_id) {
            $this->fail('That payment is already recorded.');
        }

        return $row;
    }

    private function who(SlcPaymentUploadRow $row): string
    {
        $st = $row->student_id ? Student::find($row->student_id) : null;

        return $st ? trim($st->first_name.' '.$st->last_name) : Text::titleCase(($row->data['forename'] ?? '').' '.($row->data['surname'] ?? ''));
    }

    /** A line whose SSN is on no student: the student staff found. An SSN the student lacks is saved to their record (with history), as Bulk registration does. */
    public function link(int $rowId, int $studentId): string
    {
        $row = $this->row($rowId);
        $st = Student::find($studentId) ?? $this->fail('Student not found.');
        $other = Student::where('ssn_no', $row->ssn)->where('id', '!=', $st->id)->first();
        if ($other) {
            $this->fail('SSN '.$row->ssn.' is already held on '.$other->registration_no.'.');
        }
        if (trim((string) $st->ssn_no) !== '' && strtoupper(trim($st->ssn_no)) !== $row->ssn) {
            $this->fail($st->registration_no.' already has SSN '.$st->ssn_no.'.');
        }
        DB::transaction(function () use ($row, $st) {
            if (trim((string) $st->ssn_no) === '') {
                StudentArchive::create(['student_id' => $st->id, 'table' => 'students', 'field_name' => 'ssn_no', 'field_value' => $st->ssn_no, 'field_new_value' => $row->ssn, 'created_by' => $this->user()]);
                $st->ssn_no = $row->ssn;
                $st->save();
            }
            // every line of the upload with this SSN not yet confirmed (no student, or the SSN holder whose date of birth differs) goes to the student
            SlcPaymentUploadRow::where('slc_payment_upload_id', $row->slc_payment_upload_id)->where('ssn', $row->ssn)->whereNull('slc_money_receipt_id')
                ->where(fn ($q) => $q->whereNull('student_id')->orWhere(fn ($w) => $w->where('student_id', $st->id)->where('match_method', 'ssn')))
                ->update(['student_id' => $st->id, 'match_method' => 'linked', 'linked_by' => $this->user(), 'linked_at' => now()]);
        });
        $this->log('matched SSN '.$row->ssn.' to '.$st->registration_no.' '.$st->first_name.' '.$st->last_name);

        return 'Matched to '.$st->first_name.' '.$st->last_name.'.';
    }

    public function unlink(int $rowId): string
    {
        $row = $this->row($rowId);
        if ($row->match_method !== 'linked') {
            $this->fail('Only a student matched here can be unmatched.');
        }
        $who = $this->who($row);
        // back to whoever holds the SSN (their date of birth is checked again), else no student
        $holder = self::matchStudents([['line' => 0, 'ssn' => $row->ssn, 'slcCode' => (string) ($row->data['slcCode'] ?? ''), 'dob' => (string) ($row->data['dob'] ?? '')]])[0] ?? null;
        SlcPaymentUploadRow::where('slc_payment_upload_id', $row->slc_payment_upload_id)->where('ssn', $row->ssn)->where('match_method', 'linked')->whereNull('slc_money_receipt_id')
            ->update(['student_id' => $holder, 'match_method' => $holder ? 'ssn' : null, 'linked_by' => null, 'linked_at' => null, 'chosen_agreement_id' => null, 'agreement_by' => null, 'agreement_at' => null]);
        $this->log('unmatched SSN '.$row->ssn.' from '.$who.' (the SSN stays on the student record)');

        return 'SSN '.$row->ssn.' unmatched from '.$who.'.';
    }

    /** The agreement to record against when none (or the wrong one) was found — recorded as a forced entry, as Reports' Force Insert. */
    public function chooseAgreement(int $rowId, ?int $agreementId): string
    {
        $row = $this->row($rowId);
        if (!$row->student_id) {
            $this->fail('Match the student first.');
        }
        if ($agreementId) {
            $agr = DB::table('slc_agreements')->where('id', $agreementId)->where('student_id', $row->student_id)->whereNull('deleted_at')->first() ?? $this->fail('That agreement is not on the student’s account.');
            $row->update(['chosen_agreement_id' => $agr->id, 'agreement_by' => $this->user(), 'agreement_at' => now()]);
            $this->log('chose agreement #'.$agr->id.' (year '.$agr->year.') for '.$this->who($row).' – line '.$row->line_no);

            return 'Agreement #'.$agr->id.' (year '.$agr->year.') chosen for '.$this->who($row).'.';
        }
        $row->update(['chosen_agreement_id' => null, 'agreement_by' => null, 'agreement_at' => null]);
        $this->log('back to the matched agreement for '.$this->who($row).' – line '.$row->line_no);

        return $this->who($row).' is back on the matched agreement.';
    }

    public function skip(array $rowIds, ?string $reason): string
    {
        $rows = SlcPaymentUploadRow::whereIn('id', $rowIds)->whereNull('slc_money_receipt_id')->whereNull('skipped_at')->get();
        if ($rows->isEmpty()) {
            $this->fail('Nothing to skip.');
        }
        SlcPaymentUploadRow::whereIn('id', $rows->pluck('id'))->update(['skip_reason' => $reason ?: null, 'skipped_by' => $this->user(), 'skipped_at' => now()]);
        $who = $rows->count() === 1 ? $this->who($rows->first()) : Text::plural($rows->count(), 'payment');
        $this->log('skipped '.$who.($reason ? ' – '.$reason : ''));

        return 'Skipped '.$who.'. It won’t be recorded from this upload.';
    }

    public function unskip(int $rowId): string
    {
        $row = $this->row($rowId);
        $row->update(['skip_reason' => null, 'skipped_by' => null, 'skipped_at' => null]);
        $this->log('reopened '.$this->who($row).' – line '.$row->line_no);

        return 'Reopened '.$this->who($row).'.';
    }

    /**
     * The ticked payments that can be recorded (ready, or look — staff ticked them after checking)
     * become money receipts, numbered after the highest invoice number under a lock.
     */
    public function record(array $rowIds): string
    {
        $state = app(PaymentState::class)->build();
        $upload = $state['upload'] ?? $this->fail('Upload the remittance file first.');
        $items = collect($state['rows'])->whereIn('id', $rowIds)->whereIn('status', ['ready', 'look'])->values();
        if ($items->isEmpty()) {
            $this->fail('None of those payments can be recorded.');
        }
        $date = $upload['date'];
        $made = InvoiceNumber::allocate(function ($next) use ($items, $date) {
            return DB::transaction(function () use ($items, $date, $next) {
                $made = 0;
                foreach ($items as $i) {
                    $row = SlcPaymentUploadRow::lockForUpdate()->find($i['id']);
                    if (!$row || $row->slc_money_receipt_id || $row->skipped_at) {
                        continue;
                    }
                    $amount = (float) $i['r']['amount'];
                    $n = $next();
                    $inst = $i['inst'] ? SlcInstallment::where('id', $i['inst']['id'])->whereNull('slc_money_receipt_id')->first() : null;
                    $receipt = new SlcMoneyReceipt([
                        'student_id' => $i['s']['sid'], 'student_course_relation_id' => $i['agreement']['crel'] ?: null,
                        'course_creation_instance_id' => $inst->course_creation_instance_id ?? $i['agreement']['instance'],
                        'slc_agreement_id' => $i['agreement']['id'], 'term_declaration_id' => $inst->term_declaration_id ?? null, 'session_term' => $inst->session_term ?? null,
                        'invoice_no' => $amount > 0 ? (string) $n : 'R-'.$n,
                        'slc_coursecode' => $i['r']['slcCode'], 'slc_payment_method_id' => 2, 'entry_date' => now()->toDateString(), 'payment_date' => $i['r']['date'] ?: $date,
                        'amount' => abs($amount), 'discount' => 0, 'payment_type' => $amount > 0 ? 'Course Fee' : 'Refund',
                        'remarks' => now()->format('Y-m-d H:i:s').' Bulk Upload', 'force_entry' => $i['choice'] ? 1 : 0,
                        'received_by' => $this->user(), 'created_by' => $this->user(),
                    ]);
                    $receipt->refund_invoice_no = $amount > 0 ? null : 'R-'.$n; // not fillable on the model (Reports' upload loses it)
                    $receipt->save();
                    if ($inst) {
                        $inst->update(['slc_money_receipt_id' => $receipt->id, 'updated_by' => $this->user()]);
                    }
                    $row->update(['slc_money_receipt_id' => $receipt->id, 'slc_installment_id' => $inst->id ?? null, 'recorded_by' => $this->user(), 'recorded_at' => now()]);
                    if ($row->slc_payment_history_id) {
                        // brought in from Reports' waiting list: it leaves that list, as Reports does when it inserts one
                        \App\Models\SlcPaymentHistory::where('id', $row->slc_payment_history_id)->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 1))->delete();
                    }
                    $made++;
                }

                return $made;
            });
        });
        $sum = round($items->sum(fn ($i) => $i['r']['amount']), 2);
        $this->log('recorded '.Text::plural($made, 'payment').' ('.Text::money($sum).') as money receipts dated '.Text::fmtIso($date));
        $left = count(array_unique($rowIds)) - $items->count();

        return 'Recorded '.Text::plural($made, 'payment').' ('.Text::money($sum).') as money receipts.'.($left > 0 ? ' '.Text::plural($left, 'payment').' that can’t be recorded yet were left out.' : '');
    }

    /**
     * The payments still waiting in Reports › Accounts › Payments Upload (lines its upload couldn't
     * record) brought in as an upload here, as if their remittance file had just been uploaded
     * (user, 2 Nov 2026). Each line keeps its own remittance date and the waiting entry it came from;
     * recording it here takes it off Reports' list, and one recorded in Reports first shows here as
     * already recorded. A remittance-format file of the lines is kept with the upload.
     */
    public function bringFromReports(): string
    {
        $waiting = PaymentState::legacyWaiting();
        if ($waiting->isEmpty()) {
            $this->fail('Nothing is waiting in Reports › Accounts › Payments Upload.');
        }
        $last = SlcPaymentUpload::whereNotNull('hei')->latest('id')->first();
        [$hei, $heiName] = [$last->hei ?? 'LCHC', $last->hei_name ?? 'LONDON CHURCHILL COLLEGE'];
        $dmy = fn ($d) => $d ? date('dmY', strtotime((string) $d)) : '';
        $lines = [];
        foreach ($waiting->values() as $k => $h) {
            // Reports stores the file's surname as first_name (field 3) and the forename as last_name
            $data = ['term' => strtoupper((string) $h->term_name), 'ssn' => strtoupper(trim((string) $h->ssn)), 'surname' => (string) $h->first_name, 'forename' => (string) $h->last_name,
                'dob' => $h->dob ? substr((string) $h->dob, 0, 10) : '', 'slcCode' => strtoupper((string) $h->course_code), 'courseName' => stripslashes((string) $h->course_name),
                'year' => (int) $h->year, 'amount' => round((float) $h->amount, 2), 'flag' => 'R', 'date' => substr((string) $h->transaction_date, 0, 10)];
            $raw = implode(',', [$data['term'], $data['ssn'], $data['surname'], $data['forename'], $dmy($data['dob']), '', $data['slcCode'], $data['courseName'], $data['year'], '', number_format($data['amount'], 2, '.', ''), '', '', '', '', 'R']);
            $lines[] = ['line' => $k + 2, 'raw' => $raw, 'data' => $data, 'history' => $h];
        }
        $total = round(array_sum(array_map(fn ($l) => $l['data']['amount'], $lines)), 2);
        $latest = $waiting->max('transaction_date');
        $text = implode("\r\n", array_merge([implode(',', [$hei, $heiName, count($lines), $dmy($latest), 'T', number_format($total, 2, '.', ''), '', '', '', '', '', '', '', '', '', ''])], array_column($lines, 'raw')))."\r\n";
        $name = 'Waiting in Reports – '.now()->format('d-m-Y').'.csv';
        $disk = FileDisk::name();
        $stored = 'slc-registration/payments/'.now()->format('Ymd_His').'_waiting-in-reports.csv';
        Storage::disk($disk)->put($stored, $text);
        $match = self::matchStudents(array_map(fn ($l) => ['line' => $l['line'], 'ssn' => $l['data']['ssn'], 'slcCode' => $l['data']['slcCode'], 'dob' => $l['data']['dob']], $lines));

        $up = DB::transaction(function () use ($lines, $hei, $heiName, $latest, $total, $name, $disk, $stored, $match) {
            $up = SlcPaymentUpload::create([
                'file_name' => $name, 'disk' => $disk, 'path' => $stored, 'header' => null, 'hei' => $hei, 'hei_name' => $heiName, 'remittance_date' => $latest,
                'header_count' => count($lines), 'header_total' => $total, 'line_count' => count($lines), 'line_total' => $total, 'error_lines' => [], 'created_by' => $this->user(),
            ]);
            foreach ($lines as $l) {
                $sid = $match[$l['line']] ?? ((int) $l['history']->student_id ?: null);
                SlcPaymentUploadRow::create([
                    'slc_payment_upload_id' => $up->id, 'slc_payment_history_id' => $l['history']->id, 'line_no' => $l['line'], 'raw' => $l['raw'], 'data' => $l['data'],
                    'ssn' => $l['data']['ssn'], 'amount' => $l['data']['amount'], 'student_id' => $sid, 'match_method' => $sid ? 'ssn' : null,
                ]);
            }

            return $up;
        });
        session([PaymentState::OPEN_KEY => $up->id]);
        $n = count($lines);
        $this->log('brought '.Text::plural($n, 'payment').' ('.Text::money($total).') waiting in Reports › Accounts › Payments Upload into this tab');

        return 'Brought in '.Text::plural($n, 'payment').' waiting in Reports ('.Text::money($total).'). They are the current upload: sort and record them in Check & record.';
    }

    /** Uploads › Open: the upload to work on in Check & record (this person only). */
    public function open(int $id): string
    {
        $up = SlcPaymentUpload::find($id) ?? $this->fail('Upload not found.');
        session([PaymentState::OPEN_KEY => $up->id]);

        return 'Opened '.$up->file_name.' (remittance '.Text::fmtIso($up->remittance_date?->toDateString()).').';
    }

    /** The remittance file as it was uploaded. */
    public function file(int $id): array
    {
        $up = SlcPaymentUpload::find($id) ?? $this->fail('Upload not found.');

        return ['name' => $up->file_name, 'disk' => $up->disk, 'path' => $up->path];
    }
}
