<?php

namespace App\Services\SlcRegistration;

use App\Models\SlcBulkImport;
use App\Models\SlcBulkImportRow;
use App\Models\SlcBulkNoAction;
use App\Models\SlcBulkRowField;
use Illuminate\Support\Collection;

/**
 * Data check › "No action" is remembered for later uploads (user, 5 Nov 2026: "if I click No
 * action, remember it for future uploads … you don't have to check it"). Kept per student and field
 * with both sides' values: a later upload where SLC sends the same value and we still hold the same
 * value gets No action by itself, so nobody has to look at it again. A new value on either side is
 * a new difference and is checked as usual. "Change" on the field ends it.
 */
class NoActionMemory
{
    /** [what SLC sent, what we hold] as compared */
    public static function values(array $r, array $ch): array
    {
        return [CarryOver::slcValue($r, $ch['key']), Text::norm($ch['ours'])];
    }

    /** No action was chosen on this line: remembered once per student, field and values. */
    public static function remember(SlcBulkImportRow $row, array $ch, int $userId): void
    {
        if (!$row->student_id || $ch['ok']) {
            return;
        }
        if (self::find(self::forStudents([$row->student_id])->get($row->student_id), $row->data, $ch)) {
            return;
        }
        [$slc, $ours] = self::values($row->data, $ch);
        SlcBulkNoAction::create([
            'student_id' => $row->student_id, 'field' => $ch['key'], 'slc_value' => $slc, 'ours_value' => $ours,
            'slc_display' => $ch['slc'], 'ours_display' => $ch['ours'], 'slc_bulk_import_row_id' => $row->id, 'created_by' => $userId,
        ]);
    }

    /** "Change": the student's field is checked again on later uploads. */
    public static function forget(int $studentId, string $field, int $userId): int
    {
        return SlcBulkNoAction::active()->where('student_id', $studentId)->where('field', $field)->update(['revoked_by' => $userId, 'revoked_at' => now()]);
    }

    /** Has a No action ever been remembered for this student and field? (Decisions before 5 Nov 2026 were not.) */
    public static function known(int $studentId, string $field): bool
    {
        return SlcBulkNoAction::where('student_id', $studentId)->where('field', $field)->exists();
    }

    /** The active ones of these students, grouped by student id, each with the file it was chosen on (source_file). */
    public static function forStudents(array $studentIds): Collection
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $studentIds))));
        if (!$ids) {
            return collect();
        }

        return SlcBulkNoAction::active()->whereIn('slc_bulk_no_actions.student_id', $ids)
            ->leftJoin('slc_bulk_import_rows as r', 'r.id', '=', 'slc_bulk_no_actions.slc_bulk_import_row_id')
            ->leftJoin('slc_bulk_imports as i', 'i.id', '=', 'r.slc_bulk_import_id')
            ->orderBy('slc_bulk_no_actions.id')
            ->get(['slc_bulk_no_actions.*', 'i.file_name as source_file'])
            ->groupBy('student_id');
    }

    /** The one for this difference (same field, same value from SLC, same value of ours), if any. */
    public static function find(?Collection $memories, array $r, array $ch): ?SlcBulkNoAction
    {
        if (!$memories || $ch['ok']) {
            return null;
        }
        [$slc, $ours] = self::values($r, $ch);

        return $memories->first(fn ($m) => $m->field === $ch['key'] && $m->slc_value === $slc && $m->ours_value === $ours);
    }

    /**
     * On upload, after CarryOver: each difference not decided yet that has a remembered No action
     * gets No action (by whoever chose it). Returns how many.
     */
    public function apply(SlcBulkImport $import, RegistrationState $state): int
    {
        $rows = SlcBulkImportRow::with('fields')->where('slc_bulk_import_id', $import->id)->whereNotNull('student_id')->get();
        $memories = self::forStudents($rows->pluck('student_id')->all());
        $rows = $rows->filter(fn ($row) => $memories->has($row->student_id))->values();
        if ($rows->isEmpty()) {
            return 0;
        }
        $infos = $state->infoFor($rows);
        $n = 0;
        foreach ($rows as $row) {
            foreach ($infos[$row->id]['checks'] ?? [] as $ch) {
                if ($ch['ok'] || $ch['route'] || !in_array('accept', Checks::FIELD_ROUTES[$ch['key']] ?? [], true)) {
                    continue;
                }
                $m = self::find($memories->get($row->student_id), $row->data, $ch);
                if ($m) {
                    SlcBulkRowField::create(['slc_bulk_import_row_id' => $row->id, 'field' => $ch['key'], 'route' => 'accept', 'created_by' => $m->created_by]);
                    $n++;
                }
            }
        }

        return $n;
    }
}
