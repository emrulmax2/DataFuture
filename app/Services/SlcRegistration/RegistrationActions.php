<?php

namespace App\Services\SlcRegistration;

use App\Models\Address;
use App\Models\SexIdentifier;
use App\Models\SlcAgreement;
use App\Models\SlcBulkActivity;
use App\Models\SlcBulkBatch;
use App\Models\SlcBulkImport;
use App\Models\SlcBulkImportRow;
use App\Models\SlcBulkRowField;
use App\Models\SlcBulkUpdate;
use App\Models\SlcCoc;
use App\Models\SlcRegistration;
use App\Models\Student;
use App\Models\StudentArchive;
use App\Models\StudentContact;
use App\Models\Title;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * What the /slc-registration buttons do. Each method returns the message the page flashes, or
 * throws SlcRegistrationException with the reason it refused. Every change is written to the
 * Activity log as the signed-in user, and changes to a student also to student_archives (the
 * profile's change history), as the student screens do.
 *
 * Records it writes outside its own tables:
 *   - Save SSN / Unlink: students.ssn_no;
 *   - Update our record: title, names, gender, date of birth (students), address / postcode (a new
 *     addresses row, pointed to from student_contacts — address rows can be shared, never edited);
 *   - Issue CoC: an slc_cocs row (actioned "no" → "Yes" once marked submitted in SIS);
 *   - Mark uploaded: the SLC registration (status Yes) and agreement, as the student's SLC tab
 *     "Add Registration" does.
 */
class RegistrationActions
{
    public function __construct(private RegistrationState $state)
    {
    }

    private function user(): int
    {
        return (int) auth()->id();
    }

    private function log(string $text, ?int $importId = null, bool $system = false): void
    {
        SlcBulkActivity::create(['slc_bulk_import_id' => $importId ?? SlcBulkImport::latest('id')->value('id'), 'user_id' => $system ? null : $this->user(), 'text' => $text]);
    }

    private function fail(string $message): never
    {
        throw new SlcRegistrationException($message);
    }

    /* ---------------------------------------------------------------- upload */

    public function import(string $text, string $fileName): string
    {
        $p = SisFile::parse($text);
        if (!$p['header']) {
            $this->fail('That file has no HH header line. Export the Registration Confirmation Report from SIS and try again.');
        }
        if (!$p['rows']) {
            $this->fail('No student rows were found in that file.');
        }
        $year = RegistrationState::academicYear($p['header']['ay']);
        if (!$year) {
            $this->fail('The file is for academic year '.$p['header']['ay'].', which is not set up under Academic Years.');
        }

        $disk = FileDisk::name();
        $path = 'slc-registration/imports/'.now()->format('Ymd_His').'_'.Str::slug(pathinfo($fileName, PATHINFO_FILENAME)).'.txt';
        Storage::disk($disk)->put($path, $text);

        // SSN → student; an SSN held on two records (returning students) goes to the one on this SLC course, else the newest active one
        $ssns = array_values(array_unique(array_column($p['rows'], 'ssn')));
        $holders = DB::table('students')->whereIn('ssn_no', $ssns)->whereNull('deleted_at')->orderByDesc('id')->get(['id', 'ssn_no'])->groupBy('ssn_no');
        $multi = $holders->filter(fn ($g) => $g->count() > 1)->flatten(1)->pluck('id')->all();
        $snap = $multi ? StudentSnapshots::load($multi, $year->id) : [];
        $c = null;
        $import = DB::transaction(function () use ($p, $fileName, $disk, $path, $year, $holders, $snap, &$c) {
            $import = SlcBulkImport::create([
                'file_name' => $fileName, 'disk' => $disk, 'path' => $path, 'header' => $p['header']['raw'], 'footer' => $p['footer'],
                'hei' => $p['header']['hei'], 'academic_year_id' => $year->id, 'row_count' => count($p['rows']), 'error_lines' => $p['errors'], 'created_by' => $this->user(),
            ]);
            foreach ($p['rows'] as $r) {
                $raw = $r['raw'];
                $line = $r['line'];
                unset($r['raw'], $r['line']);
                $studentId = null;
                $group = $holders->get($r['ssn']);
                if ($group) {
                    $studentId = (int) $group->first()->id;
                    if ($group->count() > 1) {
                        $onCourse = $group->first(fn ($g) => ($snap[$g->id]['slc_code'] ?? '') === strtoupper($r['slcCode']));
                        $active = $group->first(fn ($g) => !empty($snap[$g->id]['crel_id']));
                        $studentId = (int) (($onCourse ?? $active ?? $group->first())->id);
                    }
                }
                SlcBulkImportRow::create([
                    'slc_bulk_import_id' => $import->id, 'line_no' => $line, 'raw' => $raw, 'ssn' => $r['ssn'], 'data' => $r,
                    'student_id' => $studentId, 'match_method' => $studentId ? 'ssn' : null,
                ]);
            }

            // the work done on earlier uploads comes across, line by line (SSN)
            $c = app(CarryOver::class)->apply($import, $this->state);
            // a No action chosen before for the same difference (NoActionMemory)
            $c['remembered'] = app(NoActionMemory::class)->apply($import, $this->state);

            return $import;
        });

        $n = count($p['rows']);
        $matched = SlcBulkImportRow::where('slc_bulk_import_id', $import->id)->whereNotNull('student_id')->count();
        $carried = $c['lines'] ? ' Carried over from earlier uploads for '.Text::plural($c['lines'], 'student').': '.Text::plural($c['decisions'], 'decision').', '.Text::plural($c['fields'], 'field decision').($c['moved'] ? ', '.$c['moved'].' batch '.($c['moved'] === 1 ? 'confirmation' : 'confirmations').' moved to this file' : '').($c['history'] ? ', '.$c['history'].' kept as history (SLC\'s value changed or now matches)' : '').'.' : '';
        $remembered = $c['remembered'] ? ' '.Text::plural($c['remembered'], 'difference').' set to No action as chosen on an earlier upload.' : '';
        $this->log('Uploaded '.$fileName.' – '.$n.' students, '.$matched.' matched by SSN, '.($n - $matched).' without SSN on our system.'.$carried.$remembered, $import->id);

        return 'Uploaded '.$n.' students. '.$matched.' matched by SSN, '.($n - $matched).' need identifying.'.$carried.$remembered;
    }

    /* -------------------------------------------------------------- identify */

    public function link(int $rowId, int $studentId): string
    {
        $row = $this->row($rowId);
        if ($row->student_id) {
            $this->fail('This SLC line is already linked to a student.');
        }
        $other = Student::where('ssn_no', $row->ssn)->where('id', '!=', $studentId)->first();
        if ($other) {
            $this->fail('SSN '.$row->ssn.' is already held on '.$other->registration_no.'.');
        }
        $st = Student::find($studentId);
        if (!$st) {
            $this->fail('Student not found.');
        }
        if (trim((string) $st->ssn_no) !== '' && $st->ssn_no !== $row->ssn) {
            $this->fail($st->registration_no.' already has SSN '.$st->ssn_no.'.');
        }
        DB::transaction(function () use ($row, $st) {
            $this->archive($st->id, 'students', 'ssn_no', $st->ssn_no, $row->ssn);
            $st->ssn_no = $row->ssn;
            $st->save();
            $row->update(['student_id' => $st->id, 'match_method' => 'linked', 'linked_by' => $this->user(), 'linked_at' => now()]);
        });
        $this->log('Identified '.$st->registration_no.' '.$st->first_name.' '.$st->last_name.' and saved SSN '.$row->ssn.' to their record', $row->slc_bulk_import_id);

        return 'SSN saved to '.$st->first_name.' '.$st->last_name.'. They move to the data check.';
    }

    public function unlink(int $rowId): string
    {
        $row = $this->row($rowId);
        if ($row->match_method !== 'linked' || $row->decision) {
            $this->fail('Only a student identified here, with no decision yet, can be unlinked.');
        }
        $st = Student::find($row->student_id);
        DB::transaction(function () use ($row, $st) {
            if ($st && $st->ssn_no === $row->ssn) {
                $this->archive($st->id, 'students', 'ssn_no', $st->ssn_no, null);
                $st->ssn_no = null;
                $st->save();
            }
            foreach ($row->fields as $f) {
                $this->dropCoc($f);
                $f->delete();
            }
            $row->update(['student_id' => null, 'match_method' => null, 'linked_by' => null, 'linked_at' => null]);
        });
        $this->log('Unlinked SSN '.$row->ssn.' from '.($st->registration_no ?? 'the student').' (SSN removed from student record)', $row->slc_bulk_import_id);

        return 'SSN '.$row->ssn.' removed from '.trim(($st->first_name ?? '').' '.($st->last_name ?? '')).'.';
    }

    public function searchStudents(string $q): array
    {
        $q = trim($q);
        if (mb_strlen($q) < 2) {
            return [];
        }
        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $q).'%';
        $date = preg_match('/^\d{4}-\d{2}-\d{2}$/', $q) ? $q : null;
        $pc = str_replace(' ', '', strtoupper($q));
        $ids = DB::table('students as s')
            ->leftJoin('student_contacts as c', 'c.student_id', '=', 's.id')
            ->leftJoin('addresses as a', 'a.id', '=', 'c.permanent_address_id')
            ->whereNull('s.deleted_at')
            ->where(function ($w) use ($like, $date, $pc) {
                $w->where('s.registration_no', 'like', $like)
                  ->orWhere(DB::raw("CONCAT(s.first_name, ' ', s.last_name)"), 'like', $like)
                  ->orWhere('s.last_name', 'like', $like)
                  ->orWhere(DB::raw("REPLACE(a.post_code, ' ', '')"), 'like', '%'.$pc.'%');
                if ($date) {
                    $w->orWhere('s.date_of_birth', $date);
                }
            })
            ->orderByDesc('s.id')->limit(6)->pluck('s.id')->unique()->all();
        $snap = StudentSnapshots::load($ids, null);

        return array_values(array_map(fn ($s) => [
            'id' => $s['reg_no'], 'sid' => $s['id'], 'title' => $s['title'], 'first' => $s['first'], 'last' => $s['last'],
            'dob' => $s['dob'], 'postcode' => $s['addresses'][0]['postcode'] ?? '', 'ssn' => $s['ssn'], 'url' => $s['url'], 'status' => $s['status'] ?? null,
        ], array_filter(array_map(fn ($id) => $snap[$id] ?? null, $ids))));
    }

    /* ------------------------------------------------------------ data check */

    public function setRoute(int $rowId, string $key, string $route, bool $quiet = false): string
    {
        $row = $this->row($rowId);
        $info = $this->state->infoFor(collect([$row]))[$row->id];
        $ch = collect($info['checks'])->firstWhere('key', $key);
        if (!$info['s'] || !$ch || $ch['ok']) {
            $this->fail('That field has nothing to sort.');
        }
        if (!in_array($route, Checks::FIELD_ROUTES[$key] ?? [], true)) {
            $this->fail('That action is not available for '.strtolower(Checks::FIELD_LABEL[$key]).'.');
        }
        if ($row->decision) {
            $this->fail('A decision has already been made for this student.');
        }
        if ($route !== 'accept' && $row->fields->firstWhere('field', $key)?->route === 'accept') {
            NoActionMemory::forget((int) $row->student_id, $key, $this->user());
        }
        if ($route === 'update') {
            return $this->applyUpdate($row, $info, $ch, $quiet);
        }
        $name = $info['s']['first'].' '.$info['s']['last'];
        DB::transaction(function () use ($row, $key, $route, $ch, $info) {
            $f = SlcBulkRowField::firstOrNew(['slc_bulk_import_row_id' => $row->id, 'field' => $key]);
            $this->dropCoc($f);
            $f->fill(['route' => $route, 'state' => null, 'state_by' => null, 'state_at' => null, 'created_by' => $this->user()]);
            if ($route === 'coc') {
                $f->slc_coc_id = ($this->openCoc($info, $ch) ?? $this->createCoc($row, $info, $ch))->id;
            }
            $f->save();
            if ($route === 'accept') {
                NoActionMemory::remember($row, $ch, $this->user()); // later uploads with the same difference get No action
            }
        });
        if (!$quiet) {
            $this->log(($route === 'coc' ? 'CoC to issue ('.Checks::COC_TYPE[$key].')' : ($route === 'contact' ? 'Student to contact about '.strtolower(Checks::FIELD_LABEL[$key]) : 'No action needed on '.strtolower(Checks::FIELD_LABEL[$key]).' (remembered for later uploads)')).' – '.$name, $row->slc_bulk_import_id);
        }

        return $route === 'coc' ? 'CoC to issue added for '.$name.'.' : ($route === 'contact' ? $name.' added to students to contact.' : 'No action needed on '.strtolower(Checks::FIELD_LABEL[$key]).' for '.$name.'. Remembered for later uploads.');
    }

    /** The order a group's fields are decided in: an address update brings in SLC's postcode too, so address goes first. */
    private const GROUP_ORDER = ['title', 'first', 'last', 'gender', 'dob', 'address', 'postcode', 'course', 'start', 'year', 'tfl'];

    /**
     * Data check › a merged row (Name: title, first and last name; Address: address and postcode;
     * user, 5 Nov 2026): one decision for each of those fields still to
     * decide, in GROUP_ORDER, all or none. A field that an earlier one has already put right (the
     * postcode after the address update) is
     * left alone.
     */
    public function setRoutes(int $rowId, array $keys, string $route): string
    {
        $keys = array_values(array_intersect(self::GROUP_ORDER, array_unique($keys)));
        if (!$keys) {
            $this->fail('Nothing to decide.');
        }
        foreach ($keys as $k) {
            if (!in_array($route, Checks::FIELD_ROUTES[$k] ?? [], true)) {
                $this->fail('That action is not available for '.strtolower(Checks::FIELD_LABEL[$k]).'. Decide each field separately.');
            }
        }
        $done = [];
        DB::transaction(function () use ($rowId, $keys, $route, &$done) {
            foreach ($keys as $k) {
                $row = $this->row($rowId);
                $ch = collect($this->state->infoFor(collect([$row]))[$row->id]['checks'])->firstWhere('key', $k);
                if (!$ch || $ch['ok'] || !empty($ch['route'])) {
                    continue;
                }
                $this->setRoute($rowId, $k, $route, true);
                $done[] = strtolower(Checks::FIELD_LABEL[$k]);
            }
        });
        if (!$done) {
            $this->fail('Those fields have nothing left to decide.');
        }
        $row = $this->row($rowId);
        $who = $this->who($row);
        $what = count($done) > 1 ? implode(', ', array_slice($done, 0, -1)).' and '.end($done) : $done[0];
        $this->log(match ($route) {
            'update' => 'Updated our record – '.$who.': '.$what,
            'coc' => 'CoC to issue – '.$who.': '.$what,
            'contact' => 'Student to contact about '.$what.' – '.$who,
            default => 'No action needed on '.$what.' – '.$who.' (remembered for later uploads)',
        }, $row->slc_bulk_import_id);

        return match ($route) {
            'update' => 'Updated '.$who.'’s '.$what.' from SLC.',
            'coc' => 'CoC to issue added for '.$who.' ('.$what.').',
            'contact' => $who.' added to students to contact ('.$what.').',
            default => 'No action needed on '.$what.' for '.$who.'. Remembered for later uploads.',
        };
    }

    /** A merged row's Change: every one of those fields is open to decide again. */
    public function clearRoutes(int $rowId, array $keys): string
    {
        $keys = array_values(array_intersect(self::GROUP_ORDER, array_unique($keys)));
        DB::transaction(function () use ($rowId, $keys) {
            foreach ($keys as $k) {
                $this->clearRoute($rowId, $k);
            }
        });

        return 'Reopened '.implode(', ', array_map(fn ($k) => strtolower(Checks::FIELD_LABEL[$k]), $keys)).'.';
    }

    public function clearRoute(int $rowId, string $key): string
    {
        $row = $this->row($rowId);
        if ($row->decision) {
            $this->fail('A decision has already been made for this student. Reopen it first.');
        }
        $f = $row->fields->firstWhere('field', $key);
        $forgot = 0;
        if ($f) {
            DB::transaction(function () use ($f, $row, $key, &$forgot) {
                $this->dropCoc($f);
                if ($f->route === 'accept' && $row->student_id) {
                    $forgot = NoActionMemory::forget((int) $row->student_id, $key, $this->user()); // checked again on later uploads
                }
                $f->delete();
            });
        }
        $st = Student::find($row->student_id);
        $this->log('Reopened '.strtolower(Checks::FIELD_LABEL[$key] ?? $key).' for '.trim(($st->first_name ?? '').' '.($st->last_name ?? '')).($forgot ? ' – No action no longer remembered' : ''), $row->slc_bulk_import_id);

        return 'Reopened '.strtolower(Checks::FIELD_LABEL[$key] ?? $key).'.'.($forgot ? ' No action is no longer remembered for it.' : '');
    }

    /** Every suggested decision on those lines; with $key, only that field's (Data check grouped by issue). */
    public function applySuggested(array $rowIds, ?string $key = null): string
    {
        $rows = SlcBulkImportRow::with('fields')->whereIn('id', $rowIds)->whereNull('decision')->get();
        $infos = $this->state->infoFor($rows);
        $n = 0;
        $students = 0;
        foreach ($rows as $row) {
            $done = 0;
            foreach ($infos[$row->id]['checks'] as $ch) {
                if (!$ch['ok'] && !$ch['route'] && $ch['sugg'] && ($key === null || $ch['key'] === $key)) {
                    $this->setRoute($row->id, $ch['key'], $ch['sugg'], true);
                    $done++;
                }
            }
            $n += $done;
            $students += $done ? 1 : 0;
        }
        if ($n) {
            $this->log('Applied suggested actions to '.Text::plural($n, 'error').($key ? ' ('.strtolower(Checks::FIELD_LABEL[$key] ?? $key).')' : '').' across '.Text::plural($students, 'student'));
        }

        return $n ? 'Applied '.Text::plural($n, 'suggested action').($key ? '.' : '. Date of birth errors still need a decision after an ID check.') : 'Nothing to apply.';
    }

    /** The update route: our record takes SLC's value. */
    private function applyUpdate(SlcBulkImportRow $row, array $info, array $ch, bool $quiet): string
    {
        $r = $row->data;
        $key = $ch['key'];
        $st = Student::findOrFail($row->student_id);
        $s = $info['snapshot'];

        $which = ''; // the address update: which of our addresses took SLC's
        DB::transaction(function () use ($row, $r, $key, $st, $s, $ch, &$which) {
            switch ($key) {
                case 'title':
                    $title = Title::whereNull('deleted_at')->get()->first(fn ($t) => Text::norm($t->name) === Text::norm($r['title']));
                    if (!$title) {
                        $this->fail('There is no title "'.$r['title'].'" under Titles to set.');
                    }
                    $this->setStudent($st, 'title_id', $title->id);
                    break;
                case 'first':
                    $this->setStudent($st, 'first_name', Text::titleCase($r['first']));
                    break;
                case 'last':
                    $this->setStudent($st, 'last_name', Text::titleCase($r['last']));
                    break;
                case 'gender':
                    $sex = SexIdentifier::where('name', $r['gender'] === 'M' ? 'Male' : ($r['gender'] === 'F' ? 'Female' : '-'))->first();
                    if (!$sex) {
                        $this->fail('SLC\'s gender "'.$r['gender'].'" has no match under Sex identifiers.');
                    }
                    $this->setStudent($st, 'sex_identifier_id', $sex->id);
                    break;
                case 'dob':
                    $this->setStudent($st, 'date_of_birth', Text::slcToIso($r['dob']));
                    break;
                case 'address':
                    $parts = self::addressParts($this->rawField($row, 14));
                    // SLC sets the town apart with a run of spaces; without one there is no town to
                    // save (addresses.city is NOT NULL), so say so instead of failing on the insert
                    if ($parts['city'] === '') {
                        $this->fail('SLC’s address “'.trim($r['address']).'” has no separate town, so it can’t be split into our address fields. Update the address on the student’s profile instead.');
                    }
                    $which = $this->newAddress($st, $s, $parts, strtoupper($r['postcode']), true);
                    break;
                case 'postcode':
                    [$addr] = Checks::bestAddress($r['address'], $s['addresses']);
                    $old = $addr ? Address::find($addr['id']) : null;
                    if (!$old) {
                        $this->fail('There is no address on the student\'s record to correct the postcode of. Update the address instead.');
                    }
                    $this->newAddress($st, $s, ['line1' => $old->address_line_1, 'line2' => $old->address_line_2, 'city' => $old->city, 'state' => $old->state, 'country' => $old->country], strtoupper($r['postcode']), false, $old->id);
                    break;
                default:
                    $this->fail('That field cannot be updated here.');
            }
            SlcBulkUpdate::create(['slc_bulk_import_row_id' => $row->id, 'student_id' => $st->id, 'field' => Checks::FIELD_LABEL[$key].($which ? ' ('.$which.')' : ''), 'from_value' => $ch['ours'], 'to_value' => $ch['slc'], 'created_by' => $this->user()]);
        });

        $name = $s['first'].' '.$s['last'];
        $what = $which ? $which.' address' : strtolower(Checks::FIELD_LABEL[$key]);
        if (!$quiet) {
            $this->log('Updated our record '.$s['reg_no'].' '.$name.': '.$what.' “'.$ch['ours'].'” → “'.$ch['slc'].'”', $row->slc_bulk_import_id);
        }

        return 'Updated '.$name.'’s '.$what.' to '.$ch['slc'].'.'.($which === 'term-time' ? ' Their permanent address is a different address, so it was kept.' : '');
    }

    private function setStudent(Student $st, string $field, $value): void
    {
        $old = $st->getRawOriginal($field);
        $st->{$field} = $value;
        $st->updated_by = $this->user();
        $st->save();
        $this->archive($st->id, 'students', $field, $old, $value);
    }

    /**
     * A new address row for the student (rows can be shared, so none is edited), pointed to from the
     * contact. Address update (user, 5 Nov 2026): becomes the term-time address, and the permanent one
     * too only when the permanent and term-time addresses are the same address — otherwise the
     * permanent address is kept. Postcode fix: replaces the address it corrects wherever the contact
     * uses it. Returns which addresses changed ("term-time and permanent", "term-time", …).
     */
    private function newAddress(Student $st, array $s, array $parts, string $postcode, bool $asTermTime, ?int $replaces = null): string
    {
        $contact = StudentContact::where('student_id', $st->id)->whereNull('deleted_at')->orderByDesc('id')->first();
        if (!$contact) {
            $this->fail('The student has no contact record to hold an address.');
        }
        $basis = Address::find($replaces ?: ($contact->term_time_address_id ?: $contact->permanent_address_id));
        $address = Address::create([
            'address_line_1' => strtoupper((string) $parts['line1']), 'address_line_2' => strtoupper((string) ($parts['line2'] ?? '')) ?: null,
            'city' => strtoupper((string) ($parts['city'] ?? '')) ?: null, 'post_code' => $postcode,
            'state' => $parts['state'] ?? ($basis->state ?? null), 'country' => $parts['country'] ?? ($basis->country ?? 'UNITED KINGDOM'),
            'active' => 1, 'created_by' => $this->user(),
        ]);
        $old = $contact->getAttributes();
        if ($asTermTime) {
            $both = self::sameAddress($contact->permanent_address_id, $contact->term_time_address_id);
            $contact->term_time_address_id = $address->id;
            $contact->term_time_post_code = $postcode;
            if ($both) {
                $contact->permanent_address_id = $address->id;
                $contact->permanent_post_code = $postcode;
            }
        } else {
            if ((int) $contact->permanent_address_id === (int) $replaces) {
                $contact->permanent_address_id = $address->id;
                $contact->permanent_post_code = $postcode;
            }
            if ((int) $contact->term_time_address_id === (int) $replaces) {
                $contact->term_time_address_id = $address->id;
                $contact->term_time_post_code = $postcode;
            }
        }
        $contact->updated_by = $this->user();
        $changes = $contact->getDirty();
        $contact->save();
        foreach ($changes as $field => $value) {
            if ($field !== 'updated_by') {
                $this->archive($st->id, 'student_contacts', $field, $old[$field] ?? null, $value);
            }
        }

        $term = array_key_exists('term_time_address_id', $changes);
        $perm = array_key_exists('permanent_address_id', $changes);

        return $term && $perm ? 'term-time and permanent' : ($term ? 'term-time' : ($perm ? 'permanent' : ''));
    }

    /**
     * Are a student's permanent and term-time addresses the same address? The same row, or two rows
     * with the same lines, town and postcode. Neither held counts as the same (both take SLC's).
     */
    private static function sameAddress($permanentId, $termTimeId): bool
    {
        if (!$permanentId && !$termTimeId) {
            return true;
        }
        if (!$permanentId || !$termTimeId) {
            return false;
        }
        if ((int) $permanentId === (int) $termTimeId) {
            return true;
        }
        $key = fn (?Address $a) => $a ? Text::addrNorm(implode(' ', [$a->address_line_1, $a->address_line_2, $a->city])).'|'.Text::pcNorm($a->post_code) : null;
        $p = $key(Address::find($permanentId));

        return $p !== null && $p === $key(Address::find($termTimeId));
    }

    /** SLC's address field as sent: parts separated by runs of spaces, the last one the town. */
    public static function addressParts(string $raw): array
    {
        $parts = array_values(array_filter(array_map('trim', preg_split('/\s{2,}/', trim($raw))), 'strlen'));
        $city = count($parts) > 1 ? array_pop($parts) : '';
        $street = implode(', ', $parts);
        if (str_contains($street, ',')) {
            [$l1, $l2] = array_map('trim', explode(',', $street, 2));
        } elseif (preg_match('/^(\d+[A-Z]?)\s+(.+)$/i', $street, $m)) {
            [$l1, $l2] = [$m[1], $m[2]];
        } else {
            [$l1, $l2] = [$street, ''];
        }

        return ['line1' => $l1, 'line2' => $l2, 'city' => $city];
    }

    private function rawField(SlcBulkImportRow $row, int $index): string
    {
        return (string) (explode('~', $row->raw)[$index] ?? '');
    }

    /* --------------------------------------------------------------- actions */

    public function markAction(int $rowId, string $key, string $state): string
    {
        $row = $this->row($rowId);
        $f = $row->fields->firstWhere('field', $key);
        $allowed = ['coc' => ['issued'], 'contact' => ['contacted', 'done']];
        if (!$f || !in_array($state, $allowed[$f->route] ?? [], true)) {
            $this->fail('That action does not apply here.');
        }
        $st = Student::find($row->student_id);
        $name = trim(($st->first_name ?? '').' '.($st->last_name ?? ''));
        DB::transaction(function () use ($f, $state) {
            $f->update(['state' => $state, 'state_by' => $this->user(), 'state_at' => now()]);
            if ($state === 'issued' && $f->slc_coc_id) {
                SlcCoc::where('id', $f->slc_coc_id)->update(['actioned' => 'Yes', 'updated_by' => $this->user()]);
            }
        });
        $label = strtolower(Checks::FIELD_LABEL[$key] ?? $key);
        $text = match ($state) {
            'issued' => 'CoC submitted in SIS ('.Checks::COC_TYPE[$key].') – '.$name,
            'contacted' => 'Contacted '.$name.' about '.$label,
            'done' => $name.' confirmed they updated SLC ('.$label.')',
        };
        $this->log($text, $row->slc_bulk_import_id);

        return $text.'.';
    }

    /**
     * Actions › Email / SMS (user, 6 Nov 2026), once the Communication tab's sender has taken the
     * message: it goes into Activity, and when it was sent from a Contact student line (and did go
     * out) that line can be marked contacted in the same step. A failure to mark it doesn't undo
     * the message, so it is said rather than thrown.
     */
    public function messageSent(string $kind, int $studentId, string $subject, bool $delivered, ?int $rowId = null, ?string $key = null, bool $markContacted = false): string
    {
        $st = Student::find($studentId);
        $name = trim(($st->first_name ?? '').' '.($st->last_name ?? ''));
        $row = $rowId ? SlcBulkImportRow::find($rowId) : null;
        if ($row && (int) $row->student_id !== $studentId) {
            $row = null;
        }
        $what = $kind === 'email' ? 'Email' : 'SMS';
        $this->log($delivered ? $what.' sent to '.$name.': '.$subject : $what.' saved for '.$name.' but not sent (no mobile number): '.$subject, $row?->slc_bulk_import_id);
        $text = $delivered ? $what.' sent to '.$name.'. It is on their Communication tab.' : 'The SMS was saved on '.$name.'’s Communication tab but not sent: they have no mobile number.';
        if ($delivered && $markContacted && $row && $key) {
            $f = SlcBulkRowField::where('slc_bulk_import_row_id', $row->id)->where('field', $key)->first();
            if ($f && $f->route === 'contact' && !$f->state) {
                try {
                    $text .= ' '.$this->markAction($row->id, $key, 'contacted');
                } catch (SlcRegistrationException $e) {
                    $text .= ' It was not marked contacted: '.$e->getMessage();
                }
            }
        }

        return $text;
    }

    /** The CoC on the student's SLC tab, waiting to be raised in SIS. */
    private function createCoc(SlcBulkImportRow $row, array $info, array $ch): SlcCoc
    {
        $s = $info['snapshot'];
        if (!$s['crel_id']) {
            $this->fail('The student has no active course to raise a CoC against.');
        }

        return SlcCoc::create([
            'student_id' => $s['id'], 'student_course_relation_id' => $s['crel_id'], 'course_creation_instance_id' => $s['instance']['id'] ?? null,
            'confirmation_date' => now()->toDateString(), 'coc_type' => Checks::COC_ENUM[$ch['key']],
            'reason' => 'SLC registration check – '.Checks::COC_TYPE[$ch['key']].': '.$ch['label'].' SLC '.$ch['slc'].', ours '.$ch['ours'],
            'actioned' => 'no', 'created_by' => $this->user(),
        ]);
    }

    /**
     * A CoC of the same type this screen raised for the student that is not yet submitted in SIS
     * (an earlier upload's): used again rather than raising a second one.
     */
    private function openCoc(array $info, array $ch): ?SlcCoc
    {
        $s = $info['snapshot'];
        $coc = SlcCoc::where('student_id', $s['id'])->where('coc_type', Checks::COC_ENUM[$ch['key']])->where('actioned', 'no')
            ->where('reason', 'like', 'SLC registration check%')->latest('id')->first();
        if ($coc) {
            $coc->update(['reason' => 'SLC registration check – '.Checks::COC_TYPE[$ch['key']].': '.$ch['label'].' SLC '.$ch['slc'].', ours '.$ch['ours'], 'updated_by' => $this->user()]);
        }

        return $coc;
    }

    private function dropCoc(SlcBulkRowField $f): void
    {
        if ($f->slc_coc_id) {
            $shared = SlcBulkRowField::where('slc_coc_id', $f->slc_coc_id)->where('id', '!=', $f->id ?? 0)->exists();
            if (!$shared) {
                SlcCoc::where('id', $f->slc_coc_id)->delete();
            }
            $f->slc_coc_id = null;
        }
    }

    /* ------------------------------------------------------------- decisions */

    public function decide(int $rowId, string $d, ?string $reason): string
    {
        $row = $this->row($rowId);
        if ($row->decision) {
            $this->fail('A decision has already been made for this line.');
        }
        $row->update(['decision' => $d, 'decision_reason' => $reason, 'decided_by' => $this->user(), 'decided_at' => now()]);
        $who = $this->who($row);
        $this->log(($d === 'notreg' ? 'Marked not registered' : 'Put on hold – '.$reason).' – '.$who, $row->slc_bulk_import_id);

        return $d === 'notreg' ? $who.' marked not registered. Mark N for them in SIS.' : $who.' put on hold – '.$reason.'.';
    }

    public function undoDecision(int $rowId): string
    {
        $row = $this->row($rowId);
        if (!$row->decision) {
            return 'Nothing to reopen.';
        }
        if ($row->decision === 'confirm') {
            $batch = SlcBulkBatch::find($row->slc_bulk_batch_id ?: $row->earlier_batch_id);
            if ($batch && $batch->status !== 'awaiting') {
                $this->fail('That batch has already been downloaded.');
            }
        }
        $batchCode = $row->decision === 'confirm' ? SlcBulkBatch::where('id', $row->slc_bulk_batch_id)->value('code') : null;
        DB::transaction(function () use ($row) {
            $batchId = $row->slc_bulk_batch_id;
            $row->update(['decision' => null, 'decision_reason' => null, 'decided_by' => null, 'decided_at' => null, 'slc_bulk_batch_id' => null, 'earlier_batch_id' => null, 'attendance_checked' => false]);
            if ($batchId && !SlcBulkImportRow::where('slc_bulk_batch_id', $batchId)->exists()) {
                SlcBulkBatch::where('id', $batchId)->where('status', 'awaiting')->delete();
            }
        });
        $this->log('Reopened '.$row->ssn.($batchCode ? ' and removed it from batch '.$batchCode : ''), $row->slc_bulk_import_id);

        return 'Reopened '.$this->who($row).'.';
    }

    public function confirmYes(array $rowIds, array $checked = []): string
    {
        $rows = SlcBulkImportRow::with('fields')->whereIn('id', $rowIds)->get();
        $infos = $this->state->infoFor($rows);
        $ok = $rows->filter(function ($row) use ($infos, $checked) {
            $i = $infos[$row->id];
            $attOk = $i['att'] && ($i['att']['st'] === 'ok' || ($i['att']['st'] === 'warn' && (in_array($row->id, $checked) || $row->attendance_checked)));

            return $i['stage'] === 'precheck' && $i['ab'] && $i['ab']['st'] === 'ok' && $attOk;
        })->values();
        if ($ok->isEmpty()) {
            $this->fail('None of those students can be confirmed yet.');
        }

        $batch = DB::transaction(function () use ($ok, $checked) {
            // one waiting batch per academic year + HEI (header characters 3–8); it sends the newest file's header
            $header = SlcBulkImport::find($ok->first()->slc_bulk_import_id)->header;
            $batch = SlcBulkBatch::where('status', 'awaiting')->lockForUpdate()->get()->first(fn ($b) => substr($b->header, 2, 6) === substr($header, 2, 6));
            if (!$batch) {
                $next = (int) SlcBulkBatch::pluck('code')->map(fn ($c) => (int) preg_replace('/\D/', '', $c))->max() + 1;
                $batch = SlcBulkBatch::create(['code' => 'SLC-B'.str_pad((string) $next, 3, '0', STR_PAD_LEFT), 'status' => 'awaiting', 'header' => $header, 'created_by' => $this->user()]);
            } elseif ($batch->header !== $header && $ok->first()->slc_bulk_import_id >= (int) SlcBulkImport::where('header', $batch->header)->max('id')) {
                $batch->update(['header' => $header]);
            }
            foreach ($ok as $row) {
                $row->update(['decision' => 'confirm', 'decision_reason' => null, 'decided_by' => $this->user(), 'decided_at' => now(), 'slc_bulk_batch_id' => $batch->id, 'attendance_checked' => in_array($row->id, $checked) || $row->attendance_checked]);
            }

            return $batch;
        });
        $first = $infos[$ok->first()->id]['s'];
        $who = $ok->count() === 1 ? $first['first'].' '.$first['last'] : Text::plural($ok->count(), 'student');
        $this->log('Confirmed registration Yes for '.$who.' – added to batch '.$batch->code, $ok->first()->slc_bulk_import_id);

        return $who.' confirmed and added to batch '.$batch->code.'.';
    }

    /** Pre-confirm's "I've checked attendance – student is still studying", kept on the line (and carried to a newer upload). */
    public function attendanceChecked(int $rowId, bool $checked): string
    {
        $row = $this->row($rowId);
        if ($row->decision) {
            $this->fail('A decision has already been made for this student.');
        }
        $row->update(['attendance_checked' => $checked]);
        $who = $this->who($row);
        $this->log(($checked ? 'Checked attendance – ' : 'Attendance check removed – ').$who.($checked ? ' is still studying' : ''), $row->slc_bulk_import_id);

        return $checked ? 'Attendance checked for '.$who.'.' : 'Attendance check removed for '.$who.'.';
    }

    /* --------------------------------------------------------------- batches */

    /** Generates the SIS file the first time (batch → downloaded); later calls return the same file. */
    public function download(string $code): array
    {
        $batch = SlcBulkBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status === 'awaiting') {
            $items = SlcBulkImportRow::where('slc_bulk_batch_id', $batch->id)->orderBy('decided_at')->orderBy('id')->get();
            if ($items->isEmpty()) {
                $this->fail('The batch is empty.');
            }
            $text = SisFile::build($batch->header, $items->pluck('raw')->all(), now());
            $name = SisFile::fileName($batch->header, $batch->code);
            $disk = FileDisk::name();
            $path = 'slc-registration/batches/'.$name;
            Storage::disk($disk)->put($path, $text);
            $batch->update(['status' => 'downloaded', 'file_name' => $name, 'disk' => $disk, 'path' => $path, 'file_date' => now()->toDateString(), 'downloaded_by' => $this->user(), 'downloaded_at' => now()]);
            $this->log('Generated SIS file '.$name.' for batch '.$batch->code.' ('.Text::plural($items->count(), 'confirmation').')');

            return ['name' => $name, 'text' => $text, 'message' => 'Saved '.$name.'.'];
        }
        $this->log('Downloaded '.$batch->file_name);

        return ['name' => $batch->file_name, 'text' => (string) Storage::disk($batch->disk)->get($batch->path), 'message' => 'Saved '.$batch->file_name.'.'];
    }

    public function fileText(string $code): array
    {
        $batch = SlcBulkBatch::where('code', $code)->whereNotNull('path')->first() ?? $this->fail('That batch has no file yet.');

        return ['name' => $batch->file_name, 'text' => (string) Storage::disk($batch->disk)->get($batch->path)];
    }

    /**
     * The file is in SIS: the batch is marked uploaded. Recording each student's SLC registration is
     * the next, separate step (Bulk SLC Registration on the batch's student list, user 2 Nov 2026),
     * so a student SIS turned down can simply be left out.
     */
    public function markUploaded(string $code): string
    {
        $batch = SlcBulkBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status !== 'downloaded') {
            $this->fail($batch->status === 'uploaded' ? 'That batch is already marked uploaded.' : 'Download the SIS file first.');
        }
        $batch->update(['status' => 'uploaded', 'uploaded_by' => $this->user(), 'uploaded_at' => now()]);
        $n = SlcBulkImportRow::where('slc_bulk_batch_id', $batch->id)->count();
        $this->log('Marked batch '.$batch->code.' as uploaded to SIS ('.Text::plural($n, 'student').')');

        return 'Batch '.$batch->code.' marked uploaded. Tick the students SIS accepted and use Bulk SLC Registration to record their SLC registration.';
    }

    /**
     * Duplicate agreements › "Reviewed & Hide" (user, 5 Nov 2026): the group (student, course relation,
     * year) is hidden from the list and the Dashboard count while it has the agreements it has now.
     * $seenIds are the agreements the page showed; if they've changed since, nothing is hidden.
     */
    public function reviewDuplicate(int $studentId, ?int $crelId, int $year, array $seenIds): string
    {
        $g = $this->duplicateGroup($studentId, $crelId, $year, false) ?? $this->fail('That student no longer has duplicate agreements for year '.$year.' on that course. Reload the page.');
        sort($seenIds);
        if (array_map('intval', $seenIds) != $g->agreementIds) {
            $this->fail('The agreements changed since the page loaded (now #'.implode(', #', $g->agreementIds).'). Reload and review them again.');
        }
        \App\Models\SlcAgreementDuplicateReview::updateOrCreate(
            ['student_id' => $studentId, 'student_course_relation_id' => $crelId, 'year' => $year],
            ['agreement_ids' => $g->agreementIds, 'hidden' => true, 'reviewed_by' => $this->user(), 'reviewed_at' => now()]
        );
        $who = $this->duplicateWho($studentId);
        $this->log('Duplicate agreements – reviewed & hid '.$who.' year '.$year.' (agreements #'.implode(', #', $g->agreementIds).')');

        return 'Reviewed & hidden: '.$who.', year '.$year.'. It comes back if an agreement is added or removed.';
    }

    public function unhideDuplicate(int $studentId, ?int $crelId, int $year): string
    {
        $review = \App\Models\SlcAgreementDuplicateReview::where('student_id', $studentId)->where('year', $year)
            ->where(fn ($q) => $crelId ? $q->where('student_course_relation_id', $crelId) : $q->whereNull('student_course_relation_id'))->first() ?? $this->fail('That group isn’t hidden.');
        $review->update(['hidden' => false]);
        $who = $this->duplicateWho($studentId);
        $this->log('Duplicate agreements – unhid '.$who.' year '.$year);

        return 'Back on the list: '.$who.', year '.$year.'.';
    }

    private function duplicateGroup(int $studentId, ?int $crelId, int $year, bool $hidden): ?object
    {
        return RegistrationState::duplicateAgreementGroups($hidden)
            ->first(fn ($g) => (int) $g->student_id === $studentId && (int) $g->student_course_relation_id === (int) $crelId && (int) $g->year === $year);
    }

    private function duplicateWho(int $studentId): string
    {
        $st = Student::find($studentId);

        return $st ? trim($st->registration_no.' '.$st->first_name.' '.$st->last_name) : 'student '.$studentId;
    }

    /** What each kind of outcome is called (slc_bulk_import_rows.slc_recorded). */
    public const RECORDED = [
        'created' => 'registration and agreement created',
        'created_anyway' => 'new registration and agreement created (earlier ones kept, with permission)',
    ];

    /**
     * The ticked students who already have an SLC registration or an agreement for the line's academic
     * year and year of study (entered on their SLC / Accounts tab). The page asks, student by student,
     * whether to create new ones anyway (user, 2 Nov 2026: "ask for overwrite permission for each
     * student"; the earlier records are kept).
     */
    public function existingRecords(string $code, array $rowIds): array
    {
        $batch = SlcBulkBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        $out = [];
        foreach ($this->pendingLines($batch, $rowIds) as [$row, $s, $yearId, $regYear]) {
            if (!$s || !$s['crel_id']) {
                continue;
            }
            [$reg, $agr] = $this->slcRecords($s, $yearId, $regYear);
            if ($reg || $agr) {
                $out[] = [
                    'rowId' => $row->id, 'name' => trim($s['first'].' '.$s['last']), 'sid' => $s['reg_no'], 'year' => $regYear, 'status' => $s['status'] ?? null,
                    'regDate' => $reg ? substr((string) $reg->getRawOriginal('confirmation_date'), 0, 10) : null,
                    'regBy' => $reg ? (RegistrationState::names([$reg->created_by])[$reg->created_by] ?? '') : '',
                    'agrDate' => $agr && $agr->getRawOriginal('date') ? substr((string) $agr->getRawOriginal('date'), 0, 10) : null,
                ];
            }
        }

        return $out;
    }

    /**
     * Bulk SLC Registration: for the ticked students of an uploaded batch, a new SLC registration and a
     * new agreement, each filled as staff fill them by hand —
     *
     *   registration (SLC tab › Add Registration): status Yes, the student's SSN, active course
     *     relation, the year's course instance, academic year, year of study (SLC's), confirmation
     *     date = the day the batch was marked uploaded; no SLC attendance, no instalment;
     *   agreement (Accounts tab › Add Agreement): the course's SLC code, not self-funded, date = the
     *     confirmation date, year, fees = instance fee less the university commission (instance's
     *     %, else the course creation's), commission amount, discount 0, total = fees; linked to
     *     the new registration.
     *
     * A student who already has a registration or an agreement for that year is only done when
     * permitted ($overwriteIds — the page asks for each one); the earlier records are left as they
     * are. Without permission the student is skipped and can be ticked again later. Each line done
     * keeps what was done (slc_recorded), so it cannot be ticked again.
     */
    public function bulkRegister(string $code, array $rowIds, array $overwriteIds = []): string
    {
        $batch = SlcBulkBatch::where('code', $code)->first() ?? $this->fail('Batch not found.');
        if ($batch->status !== 'uploaded') {
            $this->fail('Mark the batch uploaded to SIS first.');
        }
        $lines = $this->pendingLines($batch, $rowIds);
        if (!$lines && !SlcBulkImportRow::where('slc_bulk_batch_id', $batch->id)->whereIn('id', $rowIds)->exists()) {
            $this->fail('Tick at least one student of this batch.');
        }
        $date = ($batch->uploaded_at ?? now())->toDateString();
        $did = array_fill_keys(array_keys(self::RECORDED), []);
        $left = [];
        $skipped = [];

        DB::transaction(function () use ($batch, $lines, $date, $overwriteIds, &$did, &$left, &$skipped) {
            foreach ($lines as [$row, $s, $yearId, $regYear]) {
                if (!$s || !$s['crel_id'] || !$s['instance']) {
                    $skipped[] = ($s['reg_no'] ?? $row->ssn).($s && !$s['instance'] ? ' (no course instance for the year)' : ' (no active course)');
                    continue;
                }
                [$reg, $agr] = $this->slcRecords($s, $yearId, $regYear);
                $had = $reg || $agr;
                if ($had && !in_array($row->id, $overwriteIds, true)) {
                    $left[] = $s['reg_no']; // not permitted: left as it is, can be ticked again
                    continue;
                }
                $new = SlcRegistration::create([
                    'student_id' => $s['id'], 'student_course_relation_id' => $s['crel_id'], 'course_creation_instance_id' => $s['instance']['id'],
                    'ssn' => $s['ssn'] ?: $row->ssn, 'confirmation_date' => $date, 'academic_year_id' => $yearId, 'registration_year' => $regYear,
                    'slc_registration_status_id' => 1, 'note' => 'Confirmed in bulk (SLC registration batch '.$batch->code.')', 'created_by' => $this->user(),
                ]);
                $agreement = SlcAgreement::create($this->agreementFields($s, $row, $new, $regYear, $date));
                $outcome = $had ? 'created_anyway' : 'created';
                $row->update(['slc_registration_id' => $new->id, 'slc_agreement_id' => $agreement->id, 'slc_recorded' => $outcome, 'slc_recorded_by' => $this->user(), 'slc_recorded_at' => now()]);
                $did[$outcome][] = $s['reg_no'];
            }
        });

        $parts = [];
        foreach ($did as $k => $list) {
            if ($list) {
                $parts[] = count($list).' '.self::RECORDED[$k].' ('.implode(', ', $list).')';
            }
        }
        $this->log('Bulk SLC Registration for batch '.$batch->code.': '.($parts ? implode('; ', $parts) : 'nothing created')
            .($left ? '; left as they were (already have a registration or agreement, no permission): '.implode(', ', $left) : '').($skipped ? '; not recorded: '.implode(', ', $skipped) : ''));

        $made = count($did['created']) + count($did['created_anyway']);

        return ($made ? 'SLC registration and agreement created for '.Text::plural($made, 'student') : 'No new SLC registrations')
            .($did['created_anyway'] ? ' ('.count($did['created_anyway']).' with permission – their earlier records are kept)' : '')
            .($left ? '. Left as '.(count($left) === 1 ? 'it was' : 'they were').': '.implode(', ', $left) : '')
            .'.'.($skipped ? ' Not recorded: '.implode(', ', $skipped).'.' : '');
    }

    /** The ticked lines of the batch not yet recorded, with the student, the academic year and year of study. */
    private function pendingLines(SlcBulkBatch $batch, array $rowIds): array
    {
        $items = SlcBulkImportRow::with('import')->where('slc_bulk_batch_id', $batch->id)->whereIn('id', $rowIds)->whereNull('slc_recorded')->get();
        $out = [];
        foreach ($items->groupBy('slc_bulk_import_id') as $group) {
            $yearId = (int) $group->first()->import->academic_year_id;
            $snap = StudentSnapshots::load($group->pluck('student_id')->all(), $yearId);
            foreach ($group as $row) {
                $s = $snap[$row->student_id] ?? null;
                $out[] = [$row, $s, $yearId, (int) ($row->data['year'] ?? 0) ?: (int) ($s['instance']['rank'] ?? 1)];
            }
        }

        return $out;
    }

    /**
     * What the student already has for the academic year and year of study: the SLC registration (as
     * the SLC tab's own duplicate check finds it) and an agreement for that year — its own, else any
     * agreement of that year on the course (as the Accounts tab's duplicate check finds it).
     */
    private function slcRecords(array $s, int $yearId, int $regYear): array
    {
        $reg = SlcRegistration::where('student_id', $s['id'])->where('student_course_relation_id', $s['crel_id'])
            ->where('registration_year', $regYear)->where('academic_year_id', $yearId)->latest('id')->first();
        $agr = ($reg ? SlcAgreement::where('slc_registration_id', $reg->id)->latest('id')->first() : null)
            ?? SlcAgreement::where('student_id', $s['id'])->where('student_course_relation_id', $s['crel_id'])->where('year', $regYear)->latest('id')->first();

        return [$reg, $agr];
    }

    /** The agreement as Accounts › Add Agreement fills it (SlcAgreementController@store + getInstanceFees). */
    private function agreementFields(array $s, SlcBulkImportRow $row, SlcRegistration $reg, int $regYear, string $date): array
    {
        $inst = \App\Models\CourseCreationInstance::with('creation')->find($s['instance']['id']);
        $percent = ($inst->university_commission ?? 0) > 0 ? (float) $inst->university_commission : (float) ($inst->creation->university_commission ?? 0);
        $total = (float) ($inst->fees ?? 0) ?: (float) $s['instance']['fee'];
        $commission = round($total * $percent / 100, 2);
        $fees = $total - $commission;

        return [
            'student_id' => $s['id'], 'student_course_relation_id' => $s['crel_id'], 'course_creation_instance_id' => $s['instance']['id'],
            'slc_registration_id' => $reg->id, 'slc_coursecode' => $s['slc_code'] ?: ($row->data['slcCode'] ?? null), 'is_self_funded' => 0,
            'date' => $date, 'year' => $regYear, 'commission_amount' => $commission, 'fees' => $fees, 'discount' => 0, 'total' => $fees,
            'created_by' => $this->user(),
        ];
    }

    /* ---------------------------------------------------------------- course */

    public function mapCourse(string $code, int $venueRowId): string
    {
        $code = strtoupper(trim($code));
        $v = DB::table('course_creation_venue as v')->join('course_creations as cc', 'cc.id', '=', 'v.course_creation_id')
            ->leftJoin('courses as c', 'c.id', '=', 'cc.course_id')->leftJoin('semesters as sm', 'sm.id', '=', 'cc.semester_id')
            ->where('v.id', $venueRowId)->whereNull('v.deleted_at')->first(['v.id', 'v.slc_code', 'c.name as course', 'sm.name as intake']);
        if (!$v || $code === '') {
            $this->fail('Choose a course.');
        }
        DB::table('course_creation_venue')->where('id', $v->id)->update(['slc_code' => $code, 'updated_at' => now()]);
        $this->log('Mapped SLC course '.$code.' to '.trim($v->course).' · '.$v->intake.(StudentSnapshots::code($v->slc_code) ? ' (was '.$v->slc_code.')' : ''));

        return 'Mapped '.$code.' to '.trim($v->course).' · '.$v->intake.'.';
    }

    /* --------------------------------------------------------------- helpers */

    private function row(int $id): SlcBulkImportRow
    {
        return SlcBulkImportRow::with('fields')->find($id) ?? $this->fail('That line is no longer in the upload.');
    }

    private function who(SlcBulkImportRow $row): string
    {
        $st = $row->student_id ? Student::find($row->student_id) : null;

        return $st ? trim($st->first_name.' '.$st->last_name) : Text::titleCase(($row->data['first'] ?? '').' '.($row->data['last'] ?? ''));
    }

    private function archive(int $studentId, string $table, string $field, $old, $new): void
    {
        StudentArchive::create(['student_id' => $studentId, 'table' => $table, 'field_name' => $field, 'field_value' => $old, 'field_new_value' => $new, 'created_by' => $this->user()]);
    }
}
