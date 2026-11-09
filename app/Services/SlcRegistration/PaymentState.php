<?php

namespace App\Services\SlcRegistration;

use App\Models\SlcPaymentUpload;
use App\Models\SlcPaymentUploadRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * /slc-registration › Bulk payment upload (user, 2 Nov 2026): the page's `payments` payload — the
 * latest remittance upload with each payment line matched, the earlier uploads, and what is still
 * waiting in Reports › Accounts › Payments Upload.
 *
 * Each line, worked out afresh on every load (nothing is guessed at upload):
 * Reports' checks (user, 2 Nov 2026: "check for both SSN & DOB… bring all other checks too"):
 *   student     the SSN (case-insensitive) AND the date of birth, as Reports; an SSN whose holder has
 *               another date of birth is "Student not found" naming the holder (staff can confirm them);
 *   course      SLC's course code must be on the student's active course (Reports' "Course does not
 *               match"); choosing an agreement records it as a forced entry (Reports' Force Insert);
 *   agreement   the year's agreement on the student's active course (the line's code first, newest);
 *   instalment  none on the agreement = Reports' "Installment Not Found" (blocks, unless forced);
 * and on top:
 *   instalment  an unpaid instalment of that agreement: the same amount first, then SLC's term, then
 *               the one due nearest the remittance date. A term that differs from the instalment's on
 *               the Accounts tab (its term declaration's term type) is flagged (user, 2 Nov 2026) —
 *               SLC's term often doesn't follow ours (21 Oct 2026: SLC "SPR" lines paid our
 *               "2026 September" instalments). Reports' upload took the agreement's newest
 *               instalment, paid or not, and overwrote its receipt;
 *   recorded    a money receipt already on the student's account for the remittance date, amount and
 *               type (refund / course fee) that no other line here accounts for — so a file uploaded
 *               twice, here or in Reports, is not recorded twice.
 *
 * Status: ready (all found, all agree) · look (recordable, something to check: a date of birth staff
 * accepted, the agreement's course code, every instalment already paid, several agreements, the
 * instalment amount, a forced entry) · error (Reports' checks: student not found, course does not
 * match, no agreement for the year on the active course, no instalments — can't be recorded until
 * fixed or an agreement is chosen) · done (already on the account) · recorded (here) · skipped.
 */
class PaymentState
{
    /** Session key: the upload this person has open in Check & record. */
    public const OPEN_KEY = 'slc_payment_upload_open';

    public function build(): array
    {
        // the upload this person opened in Uploads (kept in their session), else the newest
        $newest = SlcPaymentUpload::latest('id')->first();
        $open = session(self::OPEN_KEY);
        $upload = $open && $newest && (int) $open !== $newest->id ? (SlcPaymentUpload::find($open) ?? $newest) : $newest;
        $uploads = $this->uploads();
        $waiting = self::legacyWaiting();
        $base = ['uploads' => $uploads, 'legacy' => ['waiting' => $waiting->count(), 'total' => round((float) $waiting->sum('amount'), 2), 'url' => route('reports.accounts').'#accountsReportsAccordion-collapse-3']];
        if (!$upload) {
            return $base + ['upload' => null, 'rows' => [], 'counts' => self::counts([])];
        }
        $rows = SlcPaymentUploadRow::where('slc_payment_upload_id', $upload->id)->orderBy('line_no')->get();
        $out = self::match($rows, (string) $upload->remittance_date?->toDateString());
        $names = RegistrationState::names(array_merge([$upload->created_by], $rows->pluck('linked_by')->all(), $rows->pluck('agreement_by')->all(), $rows->pluck('skipped_by')->all(), $rows->pluck('recorded_by')->all()));
        foreach ($out as &$i) {
            foreach (['link', 'choice', 'skip', 'receipt'] as $k) {
                if (!empty($i[$k]['byId'])) {
                    $i[$k]['by'] = $names[$i[$k]['byId']] ?? '';
                }
            }
        }
        unset($i);

        return $base + [
            'upload' => [
                'id' => $upload->id, 'fileName' => $upload->file_name, 'hei' => $upload->hei, 'heiName' => $upload->hei_name, 'date' => $upload->remittance_date?->toDateString(),
                'headerCount' => $upload->header_count, 'headerTotal' => (float) $upload->header_total, 'lineCount' => $upload->line_count, 'lineTotal' => (float) $upload->line_total,
                'totalsOk' => (int) $upload->header_count === (int) $upload->line_count && abs((float) $upload->header_total - (float) $upload->line_total) < 0.005,
                'errors' => $upload->error_lines ?? [], 'at' => $upload->created_at?->format('d/m/Y H:i'), 'by' => $names[$upload->created_by] ?? '',
                'newest' => $upload->id === $newest->id, 'fromReports' => $rows->whereNotNull('slc_payment_history_id')->isNotEmpty(),
            ],
            'rows' => $out,
            'counts' => self::counts($out),
        ];
    }

    /** Payments still waiting in Reports › Accounts › Payments Upload (not recorded, not yet brought into this tab). */
    public static function legacyWaiting(): Collection
    {
        return DB::table('slc_payment_histories as h')->where(fn ($q) => $q->whereNull('h.status')->orWhere('h.status', '!=', 1))
            ->whereNotExists(fn ($q) => $q->from('slc_payment_upload_rows as r')->whereColumn('r.slc_payment_history_id', 'h.id'))
            ->orderBy('h.transaction_date')->orderBy('h.id')->get();
    }

    public static function counts(array $out): array
    {
        $c = ['all' => count($out), 'ready' => 0, 'look' => 0, 'error' => 0, 'done' => 0, 'recorded' => 0, 'skipped' => 0, 'matched' => 0, 'refunds' => 0];
        $sum = array_fill_keys(['all', 'ready', 'look', 'error', 'done', 'recorded', 'skipped'], 0.0);
        foreach ($out as $i) {
            $c[$i['status']]++;
            $sum[$i['status']] += $i['r']['amount'];
            $sum['all'] += $i['r']['amount'];
            $c['matched'] += $i['s'] ? 1 : 0;
            $c['refunds'] += $i['r']['amount'] < 0 ? 1 : 0;
        }
        $c['sum'] = array_map(fn ($v) => round($v, 2), $sum);

        return $c;
    }

    /** Every line matched (see the class comment). Rows need id, line_no, data, amount, student_id, match_method, chosen_agreement_id, skip and receipt columns. */
    public static function match(Collection $rows, string $date): array
    {
        $sids = $rows->pluck('student_id')->filter()->unique()->values()->all();
        $students = $sids ? DB::table('students as s')->leftJoin('titles as t', 't.id', '=', 's.title_id')->whereIn('s.id', $sids)
            ->get(['s.id', 's.registration_no', 's.first_name', 's.last_name', 's.date_of_birth', 's.ssn_no', 't.name as title'])->keyBy('id') : collect();
        $statuses = StudentStatus::forIds($sids);
        $agreements = $sids ? DB::table('slc_agreements')->whereIn('student_id', $sids)->whereNull('deleted_at')
            ->get(['id', 'student_id', 'student_course_relation_id', 'course_creation_instance_id', 'year', 'slc_coursecode', 'date', 'fees', 'total'])->groupBy('student_id') : collect();
        $chosenIds = $rows->pluck('chosen_agreement_id')->filter()->unique()->all();
        $chosen = $chosenIds ? DB::table('slc_agreements')->whereIn('id', $chosenIds)->get(['id', 'student_id', 'student_course_relation_id', 'course_creation_instance_id', 'year', 'slc_coursecode', 'date', 'fees', 'total', 'deleted_at'])->keyBy('id') : collect();
        // Reports' course check: SLC's course code must be on the student's active course
        $active = $sids ? DB::table('student_course_relations as r')->join('course_creations as c', 'c.id', '=', 'r.course_creation_id')->leftJoin('courses as co', 'co.id', '=', 'c.course_id')
            ->whereIn('r.student_id', $sids)->where('r.active', 1)->whereNull('r.deleted_at')->orderByDesc('r.id')
            ->get(['r.student_id', 'r.id', 'c.course_id', 'co.name'])->unique('student_id')->keyBy('student_id') : collect();
        $codes = $rows->map(fn ($row) => strtoupper((string) ($row->data['slcCode'] ?? '')))->filter()->unique()->values()->all();
        $codeCourses = $codes ? DB::table('course_creation_venue as v')->join('course_creations as c', 'c.id', '=', 'v.course_creation_id')->leftJoin('courses as co', 'co.id', '=', 'c.course_id')
            ->whereIn('v.slc_code', $codes)->get(['v.slc_code', 'c.course_id', 'co.name'])->groupBy(fn ($v) => strtoupper(trim((string) $v->slc_code))) : collect();
        $agrIds = $agreements->flatten(1)->pluck('id')->merge($chosen->keys())->unique()->values()->all();
        $insts = $agrIds ? DB::table('slc_installments as i')->leftJoin('term_declarations as d', 'd.id', '=', 'i.term_declaration_id')
            ->leftJoin('term_types as tt', 'tt.id', '=', DB::raw('COALESCE(d.term_type_id, i.term_type_id)')) // as the Accounts tab shows it: the term declaration's term
            ->leftJoin('slc_money_receipts as m', fn ($j) => $j->on('m.id', '=', 'i.slc_money_receipt_id')->whereNull('m.deleted_at'))
            ->whereIn('i.slc_agreement_id', $agrIds)->whereNull('i.deleted_at')->orderBy('i.installment_date')->orderBy('i.id')
            ->get(['i.id', 'i.slc_agreement_id', 'i.installment_date', 'i.amount', 'i.session_term', 'i.term_declaration_id', 'tt.code', 'd.name as decl', 'm.id as receipt_id', 'm.invoice_no as receipt_invoice', 'm.payment_date as receipt_date'])->groupBy('slc_agreement_id') : collect();
        // receipts already on the accounts for the remittance date (any source), minus those made from lines of this upload;
        // a line brought in from Reports' waiting list carries its own remittance date
        $dateOf = fn ($row) => (string) ($row->data['date'] ?? '') ?: $date;
        $dates = $rows->map($dateOf)->filter()->unique()->values()->all();
        $mine = $rows->pluck('slc_money_receipt_id')->filter()->all();
        $existing = $sids && $dates ? DB::table('slc_money_receipts')->whereIn('student_id', $sids)->whereIn('payment_date', $dates)->whereNull('deleted_at')
            ->whereIn('payment_type', ['Course Fee', 'Refund'])->get(['id', 'student_id', 'payment_date', 'invoice_no', 'amount', 'payment_type', 'slc_agreement_id', 'created_by', 'created_at', 'remarks']) : collect();
        $receiptById = $mine ? DB::table('slc_money_receipts')->whereIn('id', $mine)->get(['id', 'invoice_no', 'amount', 'deleted_at'])->keyBy('id') : collect();
        $pool = [];
        foreach ($existing as $m) {
            if (!in_array($m->id, $mine)) {
                $pool[substr((string) $m->payment_date, 0, 10).'|'.$m->student_id.'|'.number_format(abs((float) $m->amount), 2, '.', '').'|'.$m->payment_type][] = $m;
            }
        }
        $userNames = RegistrationState::names($existing->pluck('created_by')->all());
        $usedInst = [];
        foreach ($rows as $row) {
            if ($row->slc_installment_id && $row->slc_money_receipt_id) {
                $usedInst[$row->slc_installment_id] = true;
            }
        }

        $out = [];
        foreach ($rows as $row) {
            $r = $row->data;
            $amount = (float) $row->amount;
            $type = $amount < 0 ? 'Refund' : 'Course Fee';
            $rowDate = $dateOf($row);
            $st = $row->student_id ? $students->get($row->student_id) : null;
            // Reports' student check: the SSN AND the date of birth (unless staff confirmed the student here)
            $holder = null;
            if ($st && $row->match_method !== 'linked' && ($r['dob'] === '' || substr((string) $st->date_of_birth, 0, 10) !== $r['dob'])) {
                $holder = $st;
                $st = null;
            }
            $s = $st ? [
                'sid' => (int) $st->id, 'id' => $st->registration_no, 'title' => (string) $st->title, 'first' => trim((string) $st->first_name), 'last' => trim((string) $st->last_name),
                'dob' => substr((string) $st->date_of_birth, 0, 10), 'ssn' => (string) $st->ssn_no,
                'url' => route('student.show', $st->id), 'accountsUrl' => route('student.accounts', $st->id), 'status' => $statuses[$st->id] ?? null,
            ] : null;
            $issues = [];
            $agr = null;
            $choices = [];
            $inst = null;
            $done = null;
            $status = 'ready';
            $error = '';
            $errorKey = null;
            $fail = function (string $key, string $text) use (&$status, &$error, &$errorKey) {
                if ($status !== 'error') {
                    [$status, $errorKey, $error] = ['error', $key, $text];
                }
            };

            if (!$s) {
                $holder
                    ? $fail('dob', 'Student not found: SSN '.$r['ssn'].' is on '.$holder->registration_no.' '.trim($holder->first_name.' '.$holder->last_name).', but the date of birth differs (SLC '.($r['dob'] ? Text::fmtIso($r['dob']) : 'none').', ours '.Text::fmtIso(substr((string) $holder->date_of_birth, 0, 10)).'). Correct it on the student record, or confirm the student.')
                    : $fail('nostudent', 'Student not found: no student has SSN '.$r['ssn'].'. Check the SSN, or find the student.');
            } else {
                if ($r['dob'] !== $s['dob']) {
                    $issues[] = ['key' => 'dob', 'text' => 'Date of birth differs: SLC '.($r['dob'] ? Text::fmtIso($r['dob']) : 'none').', ours '.Text::fmtIso($s['dob']).' – student confirmed by staff'];
                }
                $act = $active->get($s['sid']);
                $forCode = collect($codeCourses->get($r['slcCode'], []));
                $courseOk = $act && $forCode->contains(fn ($v) => (int) $v->course_id === (int) $act->course_id);
                $courseText = !$forCode->count() ? 'SLC course code '.$r['slcCode'].' is not on any of our courses (Bulk registration › Course mapping)'
                    : (!$act ? 'the student has no active course' : 'SLC course code '.$r['slcCode'].' ('.($forCode->first()->name ?? '–').') is not the student’s active course ('.($act->name ?: '–').')');
                $list = collect($agreements->get($s['sid'], []));
                $choices = $list->sortBy([['year', 'asc'], ['id', 'asc']])->values()->map(fn ($a) => ['id' => (int) $a->id, 'year' => (int) $a->year, 'code' => (string) $a->slc_coursecode, 'date' => substr((string) $a->date, 0, 10), 'total' => (float) $a->total])->all();
                if ($row->chosen_agreement_id) {
                    // staff chose the agreement: recorded as a forced entry, as Reports' Force Insert
                    $a = $chosen->get($row->chosen_agreement_id);
                    $agr = $a && !$a->deleted_at ? $a : null;
                    if (!$agr) {
                        $fail('agreement', 'The chosen agreement has been deleted – choose another');
                    } elseif (!$courseOk) {
                        $issues[] = ['key' => 'course', 'text' => 'Course does not match: '.$courseText.' – recorded as a forced entry on the chosen agreement'];
                    }
                } elseif (!$courseOk) {
                    $fail('course', 'Course does not match: '.$courseText.'. Choose the agreement to record it as a forced entry.');
                } else {
                    // Reports' agreement: the year's agreement on the student's active course (the line's course code first, then the newest)
                    $cands = $list->filter(fn ($a) => (int) $a->year === (int) $r['year'] && (int) $a->student_course_relation_id === (int) $act->id)
                        ->sortBy([fn ($x, $y) => (strtoupper((string) $y->slc_coursecode) === $r['slcCode']) <=> (strtoupper((string) $x->slc_coursecode) === $r['slcCode']), fn ($x, $y) => $y->id <=> $x->id])->values();
                    $agr = $cands->first();
                    if (!$agr) {
                        $elsewhere = $list->first(fn ($a) => (int) $a->year === (int) $r['year']);
                        $fail('agreement', 'No SLC agreement for year '.$r['year'].' on the student’s active course'.($elsewhere ? ' (agreement #'.$elsewhere->id.' for that year is on another course)' : '').'. Add it on the Accounts tab, or choose an agreement.');
                    } else {
                        if ($cands->count() > 1) {
                            $issues[] = ['key' => 'multi', 'text' => $cands->count().' agreements for year '.$r['year'].' – agreement #'.$agr->id.' used'];
                        }
                        if (strtoupper((string) $agr->slc_coursecode) !== $r['slcCode']) {
                            $issues[] = ['key' => 'code', 'text' => 'Agreement course code differs: SLC '.$r['slcCode'].', agreement '.($agr->slc_coursecode ?: '–')];
                        }
                    }
                }
                if ($agr && $amount > 0) {
                    $list = collect($insts->get($agr->id, []));
                    $due = fn ($x) => abs(strtotime((string) $x->installment_date) - strtotime($rowDate ?: 'today'));
                    $sameTerm = fn ($x) => strtoupper((string) $x->code) === $r['term'];
                    $open = $list->filter(fn ($x) => !$x->receipt_id && empty($usedInst[$x->id]))
                        ->sortBy([fn ($x, $y) => (abs((float) $y->amount - $amount) < 0.005) <=> (abs((float) $x->amount - $amount) < 0.005), fn ($x, $y) => $sameTerm($y) <=> $sameTerm($x), fn ($x, $y) => $due($x) <=> $due($y)]);
                    $termLabel = fn ($x) => trim(($x->decl ?: 'No term declaration').($x->code ? ' ('.$x->code.')' : ''));
                    $slcTerm = PaymentFile::termName($r['term']).' ('.$r['term'].')';
                    $pick = $row->slc_installment_id && $row->slc_money_receipt_id ? $list->firstWhere('id', $row->slc_installment_id) : null;
                    $pick ??= $open->first();
                    $short = (float) $agr->total - (float) $list->sum('amount');
                    if ($pick) {
                        $inst = ['id' => (int) $pick->id, 'date' => substr((string) $pick->installment_date, 0, 10), 'amount' => (float) $pick->amount, 'term' => (string) $pick->code, 'decl' => (string) $pick->decl, 'sessionTerm' => $pick->session_term, 'termDeclarationId' => $pick->term_declaration_id];
                        if (!$row->slc_money_receipt_id) {
                            $usedInst[$pick->id] = true;
                        }
                        if (abs((float) $pick->amount - $amount) >= 0.005) {
                            $issues[] = ['key' => 'amount', 'text' => 'Instalment is '.Text::money($pick->amount).', SLC paid '.Text::money($amount)];
                        }
                        // the instalment's term on the Accounts tab against SLC's (user, 2 Nov 2026: "you have to flag it")
                        if (!$sameTerm($pick)) {
                            $issues[] = ['key' => 'term', 'text' => 'Term differs: SLC paid for '.$slcTerm.', the instalment is '.$termLabel($pick)];
                        }
                    } elseif (!$row->slc_money_receipt_id) {
                        if ($list->isEmpty()) {
                            // Reports' "Installment Not Found"
                            $row->chosen_agreement_id
                                ? $issues[] = ['key' => 'inst', 'text' => 'Agreement #'.$agr->id.' has no instalments – the receipt is linked to the agreement only']
                                : $fail('inst', 'Instalment not found: agreement #'.$agr->id.' has no instalments. Add them on the Accounts tab, or choose the agreement to record it as a forced entry.');
                        } else {
                            if (!$list->contains($sameTerm)) {
                                $issues[] = ['key' => 'term', 'text' => 'Term differs: SLC paid for '.$slcTerm.'; agreement #'.$agr->id.' has no '.PaymentFile::termName($r['term']).' instalment (it has '.$list->map($termLabel)->unique()->implode(', ').')'];
                            }
                            $last = $list->filter(fn ($x) => $x->receipt_id)->sortBy('receipt_date')->last();
                            $issues[] = ['key' => 'paid', 'text' => 'Every instalment of agreement #'.$agr->id.' is already paid'.($last ? ' (the last by receipt '.$last->receipt_invoice.', remittance '.Text::fmtIso(substr((string) $last->receipt_date, 0, 10)).')' : '')
                                .($short >= 0.005 ? '. Its instalments add up to '.Text::money($list->sum('amount')).' of the '.Text::money($agr->total).' agreement – some may be missing' : ' – check it isn’t a second payment')];
                        }
                    }
                }
            }

            // already on the account? (not for lines recorded or skipped here)
            if ($s && !$row->slc_money_receipt_id && !$row->skipped_at) {
                $key = $rowDate.'|'.$s['sid'].'|'.number_format(abs($amount), 2, '.', '').'|'.$type;
                if (!empty($pool[$key])) {
                    $m = array_shift($pool[$key]);
                    $done = ['id' => (int) $m->id, 'invoice' => $m->invoice_no, 'by' => $userNames[$m->created_by] ?? '', 'at' => $m->created_at ? \Carbon\Carbon::parse($m->created_at)->format('d/m/Y') : '', 'bulk' => str_contains((string) $m->remarks, 'Bulk Upload')];
                }
            }

            if ($row->slc_money_receipt_id) {
                $rc = $receiptById->get($row->slc_money_receipt_id);
                $status = 'recorded';
            } elseif ($row->skipped_at) {
                $status = 'skipped';
            } elseif ($done) {
                $status = 'done';
            } elseif ($status !== 'error' && $issues) {
                $status = 'look';
            }
            $holderOut = $holder ? ['sid' => (int) $holder->id, 'id' => $holder->registration_no, 'name' => trim($holder->first_name.' '.$holder->last_name), 'dob' => substr((string) $holder->date_of_birth, 0, 10), 'url' => route('student.show', $holder->id), 'status' => $statuses[$holder->id] ?? null] : null;

            $out[] = [
                'id' => $row->id, 'line' => $row->line_no, 'r' => ['date' => $rowDate] + $r + ['amount' => $amount, 'type' => $type, 'termName' => PaymentFile::termName($r['term'])],
                'fromReports' => (bool) $row->slc_payment_history_id,
                's' => $s, 'link' => ['method' => $row->match_method, 'byId' => $row->linked_by],
                'agreement' => $agr ? ['id' => (int) $agr->id, 'year' => (int) $agr->year, 'code' => (string) $agr->slc_coursecode, 'date' => substr((string) $agr->date, 0, 10), 'total' => (float) $agr->total,
                    'crel' => (int) $agr->student_course_relation_id, 'instance' => $agr->course_creation_instance_id ? (int) $agr->course_creation_instance_id : null] : null,
                'choice' => $row->chosen_agreement_id ? ['byId' => $row->agreement_by, 'at' => $row->agreement_at?->format('d/m H:i')] : null,
                'choices' => in_array($status, ['error', 'look'], true) || $row->chosen_agreement_id ? $choices : [],
                'inst' => $inst, 'issues' => $issues, 'status' => $status, 'error' => $error, 'errorKey' => $status === 'error' ? $errorKey : null, 'holder' => $holderOut, 'done' => $done,
                'receipt' => $row->slc_money_receipt_id ? ['id' => (int) $row->slc_money_receipt_id, 'invoice' => $rc->invoice_no ?? '', 'deleted' => (bool) ($rc->deleted_at ?? false), 'byId' => $row->recorded_by, 'at' => $row->recorded_at?->format('d/m/Y H:i'), 'forced' => (bool) $row->chosen_agreement_id] : null,
                'skip' => $row->skipped_at ? ['reason' => $row->skip_reason, 'byId' => $row->skipped_by, 'at' => $row->skipped_at->format('d/m H:i')] : null,
            ];
        }

        return $out;
    }

    /**
     * Every remittance recorded in bulk, by remittance date — here or in Reports › Accounts ›
     * Payments Upload, which kept neither the file nor the upload, so it is rebuilt from the money
     * receipts it made (remark "… Bulk Upload", payment date = remittance date). Loaded only when
     * Uploads is opened (user, 2 Nov 2026: "show old data here too").
     */
    public static function history(): array
    {
        $bulk = fn () => DB::table('slc_money_receipts')->whereNull('deleted_at')->where('remarks', 'like', '%Bulk Upload%');
        $g = $bulk()->selectRaw("payment_date, COUNT(*) as n, SUM(CASE WHEN payment_type = 'Refund' THEN -amount ELSE amount END) as total, SUM(payment_type = 'Refund') as refunds,
                SUM(force_entry = 1) as forced, GROUP_CONCAT(DISTINCT created_by) as users, MIN(created_at) as first, MAX(created_at) as last")
            ->groupBy('payment_date')->orderByDesc('payment_date')->get();
        // the same student, amount and type more than once on a remittance date
        $doubles = DB::query()->fromSub($bulk()->selectRaw('payment_date, student_id, amount, payment_type, COUNT(*) as c')->groupBy('payment_date', 'student_id', 'amount', 'payment_type')->havingRaw('COUNT(*) > 1'), 'd')
            ->selectRaw('payment_date, SUM(c - 1) as extra')->groupBy('payment_date')->pluck('extra', 'payment_date');
        $waiting = DB::table('slc_payment_histories')->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 1))->selectRaw('transaction_date, COUNT(*) as n')->groupBy('transaction_date')->pluck('n', 'transaction_date');
        $here = DB::table('slc_payment_uploads')->selectRaw('remittance_date, COUNT(*) as n')->groupBy('remittance_date')->pluck('n', 'remittance_date');
        $names = RegistrationState::names($g->flatMap(fn ($r) => explode(',', (string) $r->users))->filter()->unique()->all());
        $when = fn ($t) => $t ? \Carbon\Carbon::parse($t)->format('d/m/Y H:i') : '';
        $dates = $g->pluck('payment_date')->merge($waiting->keys())->unique();

        return $dates->sortDesc()->values()->map(function ($d) use ($g, $doubles, $waiting, $here, $names, $when) {
            $r = $g->firstWhere('payment_date', $d);

            return [
                'date' => substr((string) $d, 0, 10), 'n' => (int) ($r->n ?? 0), 'total' => round((float) ($r->total ?? 0), 2), 'refunds' => (int) ($r->refunds ?? 0), 'forced' => (int) ($r->forced ?? 0),
                'by' => $r ? collect(explode(',', (string) $r->users))->filter()->map(fn ($u) => $names[$u] ?? 'User '.$u)->values()->all() : [],
                'first' => $when($r->first ?? null), 'last' => $when($r->last ?? null),
                'doubles' => (int) ($doubles[$d] ?? 0), 'waiting' => (int) ($waiting[$d] ?? 0), 'uploadsHere' => (int) ($here[$d] ?? 0),
            ];
        })->all();
    }

    /** The bulk receipts of one remittance date, and the payments still waiting in Reports for it. */
    public static function historyDate(string $date): array
    {
        $rows = DB::table('slc_money_receipts as m')->leftJoin('students as s', 's.id', '=', 'm.student_id')->leftJoin('titles as t', 't.id', '=', 's.title_id')
            ->leftJoin('slc_agreements as a', 'a.id', '=', 'm.slc_agreement_id')
            ->whereNull('m.deleted_at')->where('m.remarks', 'like', '%Bulk Upload%')->where('m.payment_date', $date)->orderBy('s.last_name')->orderBy('s.first_name')->orderBy('m.id')
            ->get(['m.id', 'm.student_id', 'm.invoice_no', 'm.amount', 'm.payment_type', 'm.slc_agreement_id', 'm.slc_coursecode', 'm.force_entry', 'm.created_by', 'm.created_at',
                's.registration_no', 's.first_name', 's.last_name', 's.ssn_no', 't.name as title', 'a.year as agr_year', 'a.slc_coursecode as agr_code']);
        $inst = $rows->isEmpty() ? collect() : DB::table('slc_installments')->whereIn('slc_money_receipt_id', $rows->pluck('id'))->whereNull('deleted_at')
            ->get(['slc_money_receipt_id', 'installment_date', 'amount'])->keyBy('slc_money_receipt_id');
        $here = $rows->isEmpty() ? collect() : SlcPaymentUploadRow::whereIn('slc_money_receipt_id', $rows->pluck('id'))->pluck('slc_payment_upload_id', 'slc_money_receipt_id');
        $dupKey = fn ($m) => $m->student_id.'|'.$m->amount.'|'.$m->payment_type;
        $dup = $rows->groupBy($dupKey)->map->count();
        $names = RegistrationState::names($rows->pluck('created_by')->all());
        $statuses = StudentStatus::forIds($rows->pluck('student_id')->all());
        $waiting = DB::table('slc_payment_histories')->where('transaction_date', $date)->where(fn ($q) => $q->whereNull('status')->orWhere('status', '!=', 1))
            ->get(['id', 'student_id', 'ssn', 'first_name', 'last_name', 'course_code', 'year', 'amount', 'errors']);

        return [
            'date' => $date,
            'receipts' => $rows->map(fn ($m) => [
                'id' => (int) $m->id, 'invoice' => $m->invoice_no, 'amount' => (float) $m->amount, 'refund' => $m->payment_type === 'Refund',
                's' => $m->student_id ? ['sid' => (int) $m->student_id, 'id' => $m->registration_no, 'name' => implode(' ', array_filter(array_map(fn ($v) => trim((string) $v), [$m->title, $m->first_name, $m->last_name]))), 'ssn' => (string) $m->ssn_no, 'accountsUrl' => route('student.accounts', $m->student_id), 'status' => $statuses[$m->student_id] ?? null] : null,
                'agreement' => $m->slc_agreement_id ? ['id' => (int) $m->slc_agreement_id, 'year' => $m->agr_year, 'code' => $m->agr_code ?: $m->slc_coursecode] : null,
                'inst' => isset($inst[$m->id]) ? ['date' => substr((string) $inst[$m->id]->installment_date, 0, 10), 'amount' => (float) $inst[$m->id]->amount] : null,
                'forced' => (bool) $m->force_entry, 'double' => ($dup[$dupKey($m)] ?? 1) > 1 ? $dup[$dupKey($m)] : 0,
                'by' => $names[$m->created_by] ?? '', 'at' => $m->created_at ? \Carbon\Carbon::parse($m->created_at)->format('d/m/Y H:i') : '',
                'source' => isset($here[$m->id]) ? 'here' : 'reports',
            ])->all(),
            'waiting' => $waiting->map(fn ($w) => ['id' => (int) $w->id, 'name' => Text::titleCase(trim($w->last_name.' '.$w->first_name)), 'ssn' => $w->ssn, 'code' => $w->course_code, 'year' => $w->year, 'amount' => (float) $w->amount, 'error' => $w->errors])->all(),
        ];
    }

    private function uploads(): array
    {
        $ups = SlcPaymentUpload::orderByDesc('id')->limit(30)->get();
        if ($ups->isEmpty()) {
            return [];
        }
        $rec = SlcPaymentUploadRow::whereIn('slc_payment_upload_id', $ups->pluck('id'))->whereNotNull('slc_money_receipt_id')
            ->groupBy('slc_payment_upload_id')->selectRaw('slc_payment_upload_id, COUNT(*) as n, SUM(amount) as s')->get()->keyBy('slc_payment_upload_id');
        $names = RegistrationState::names($ups->pluck('created_by')->all());

        return $ups->map(fn ($u) => [
            'id' => $u->id, 'fileName' => $u->file_name, 'date' => $u->remittance_date?->toDateString(), 'lines' => $u->line_count, 'total' => (float) $u->line_total,
            'recorded' => (int) ($rec[$u->id]->n ?? 0), 'recordedTotal' => round((float) ($rec[$u->id]->s ?? 0), 2),
            'at' => $u->created_at?->format('d/m/Y H:i'), 'by' => $names[$u->created_by] ?? '', 'fileUrl' => route('slc.registration.pay.file', ['id' => $u->id]),
        ])->all();
    }
}
