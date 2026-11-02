<?php

namespace App\Services\SlcRegistration;

use App\Models\SlcBulkBatch;
use App\Models\SlcBulkImport;
use App\Models\SlcBulkImportRow;
use App\Models\SlcBulkRowField;

/**
 * A newer SIS file must not lose the work done on the earlier one (user, 2 Nov 2026: "make sure we
 * don't lose any info during multiple uploads"). Each new line takes over the work of the most
 * recent earlier line with the same SSN:
 *
 *   - Identify: "identified by" stays (the SSN is already on the student);
 *   - batch: a confirmation waiting in the batch moves to the new line (the batch then sends the new
 *     file's line); one already downloaded / uploaded stays "Earlier batch";
 *   - Hold and Not registered stay (a "student not identified" hold ends once the SSN matches);
 *   - the attendance "I've checked" tick stays;
 *   - each field decision (CoC to issue, Contact student, No action) and how far it got stays while
 *     SLC still sends the same value — the same CoC, never a second one. When SLC's value changed
 *     or it now matches ours, the decision goes into the line's history instead (shown on the page)
 *     and the field is decided again if it still differs.
 *
 * History accumulates from upload to upload, so the whole trail stays with the student's line.
 */
class CarryOver
{
    /** @return array{lines:int, decisions:int, fields:int, history:int, moved:int} */
    public function apply(SlcBulkImport $import, RegistrationState $state): array
    {
        $rows = SlcBulkImportRow::with('fields')->where('slc_bulk_import_id', $import->id)->get();
        $prevBySsn = SlcBulkImportRow::with(['fields', 'import'])
            ->whereIn('ssn', $rows->pluck('ssn'))->where('slc_bulk_import_id', '<', $import->id)
            ->orderBy('slc_bulk_import_id')->orderBy('id')->get()
            ->keyBy('ssn'); // the most recent earlier line for each SSN
        $counts = ['lines' => 0, 'decisions' => 0, 'fields' => 0, 'history' => 0, 'moved' => 0];
        if ($prevBySsn->isEmpty()) {
            return $counts;
        }
        $infos = $state->infoFor($rows);
        $batches = SlcBulkBatch::whereIn('id', $prevBySsn->flatMap(fn ($p) => [$p->slc_bulk_batch_id, $p->earlier_batch_id])->filter()->unique())->get()->keyBy('id');

        foreach ($rows as $row) {
            $prev = $prevBySsn->get($row->ssn);
            if (!$prev) {
                continue;
            }
            $before = $counts['decisions'] + $counts['fields'] + $counts['history'];
            $file = (string) ($prev->import->file_name ?? 'the earlier upload');
            $history = $prev->history ?? [];
            $upd = ['carried_from_id' => $prev->id];

            // Identify
            if ($prev->match_method === 'linked' && $row->student_id && (int) $row->student_id === (int) $prev->student_id) {
                $upd += ['match_method' => 'linked', 'linked_by' => $prev->linked_by, 'linked_at' => $prev->linked_at];
            }

            // the decision: batch, hold, not registered
            $batch = $batches->get($prev->slc_bulk_batch_id ?: $prev->earlier_batch_id);
            if ($prev->decision === 'confirm' && $batch && $batch->status === 'awaiting' && $prev->slc_bulk_batch_id) {
                $upd += ['decision' => 'confirm', 'decided_by' => $prev->decided_by, 'decided_at' => $prev->decided_at, 'attendance_checked' => $prev->attendance_checked, 'slc_bulk_batch_id' => $batch->id];
                $prev->update(['slc_bulk_batch_id' => null, 'decision_reason' => 'Moved to '.$import->file_name]);
                if (substr($batch->header, 2, 6) === substr($import->header, 2, 6)) {
                    $batch->update(['header' => $import->header]); // the batch now sends this file's lines
                }
                $counts['moved']++;
                $counts['decisions']++;
            } elseif ($prev->decision === 'confirm' && $batch) {
                $upd += ['decision' => 'confirm', 'decision_reason' => 'Earlier batch', 'earlier_batch_id' => $batch->id];
                $counts['decisions']++;
            } elseif (in_array($prev->decision, ['hold', 'notreg'], true)) {
                if ($prev->decision === 'hold' && $prev->decision_reason === 'student not identified' && $row->student_id) {
                    $history[] = $this->entry($prev, '', 'hold', null, $prev->decided_by, $prev->decided_at, $file, 'released', 'The SSN now matches a student');
                    $counts['history']++;
                } else {
                    $upd += ['decision' => $prev->decision, 'decision_reason' => $prev->decision_reason, 'decided_by' => $prev->decided_by, 'decided_at' => $prev->decided_at];
                    $counts['decisions']++;
                }
            }
            if (!array_key_exists('attendance_checked', $upd) && $prev->attendance_checked) {
                $upd['attendance_checked'] = true;
            }

            // field decisions
            $checks = collect($infos[$row->id]['checks'] ?? [])->keyBy('key');
            foreach ($prev->fields as $f) {
                $ch = $checks->get($f->field);
                $same = self::slcValue($prev->data, $f->field) === self::slcValue($row->data, $f->field);
                if ($ch && !$ch['ok'] && $same) {
                    SlcBulkRowField::create([
                        'slc_bulk_import_row_id' => $row->id, 'field' => $f->field, 'route' => $f->route, 'state' => $f->state,
                        'state_by' => $f->state_by, 'state_at' => $f->state_at, 'slc_coc_id' => $f->slc_coc_id, 'carried_from_id' => $f->id, 'created_by' => $f->created_by,
                    ]);
                    $counts['fields']++;
                    continue;
                }
                $now = !$ch ? 'unchecked' : ($ch['ok'] ? 'matches' : 'changed');
                $history[] = $this->entry($prev, $f->field, $f->route, $f->state, $f->state_by ?: $f->created_by, $f->state_at ?? $f->created_at, $file, $now, $ch && !$ch['ok'] ? $ch['slc'] : null, $f->slc_coc_id);
                $counts['history']++;
            }

            $upd['history'] = $history ?: null;
            $row->update($upd);
            if ($counts['decisions'] + $counts['fields'] + $counts['history'] > $before || isset($upd['match_method']) || !empty($upd['attendance_checked'])) {
                $counts['lines']++; // students that had something to carry
            }
        }

        return $counts;
    }

    private function entry(SlcBulkImportRow $prev, string $field, string $route, ?string $state, $by, $at, string $file, string $now, ?string $nowValue = null, ?int $cocId = null): array
    {
        return [
            'field' => $field, 'route' => $route, 'state' => $state, 'by' => $by ? (int) $by : null,
            'at' => $at ? \Carbon\Carbon::parse($at)->toDateTimeString() : null,
            'then' => $field !== '' ? self::slcDisplay($prev->data, $field) : null,
            'now' => $now, 'nowValue' => $nowValue, 'cocId' => $cocId, 'file' => $file,
        ];
    }

    /** What SLC sent for a field, as compared between two files. */
    public static function slcValue(array $r, string $key): string
    {
        $v = match ($key) {
            'first' => trim(($r['first'] ?? '').' '.($r['other'] ?? '')),
            'course' => ($r['slcCode'] ?? '').'|'.($r['courseName'] ?? ''),
            default => (string) ($r[$key] ?? ''),
        };

        return Text::norm($v);
    }

    /** What SLC sent for a field, as the page shows it. */
    public static function slcDisplay(array $r, string $key): string
    {
        return match ($key) {
            'first' => trim(($r['first'] ?? '').' '.($r['other'] ?? '')),
            'dob', 'start' => Text::fmtSlc($r[$key] ?? ''),
            'tfl' => Text::money($r['tfl'] ?? 0),
            'address' => Text::titleCase($r['address'] ?? ''),
            'course' => trim(($r['slcCode'] ?? '').' · '.($r['courseName'] ?? ''), ' ·'),
            default => (string) ($r[$key] ?? ''),
        };
    }
}
