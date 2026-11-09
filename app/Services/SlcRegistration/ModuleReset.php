<?php

namespace App\Services\SlcRegistration;

use App\Models\SlcBulkActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * /slc-registration › Clear DB (user, 6 Nov 2026; users 1 and 7 only): empties one module's own
 * working tables — its uploads, their lines, the decisions taken on them, its batches and its
 * Activity entries — so it can start again from a fresh file. What the module wrote to the
 * student's real records is never touched: student details and addresses, SSNs, SLC registrations,
 * agreements, CoCs, money receipts and the course mapping stay. Uploaded files stay in storage.
 */
class ModuleReset
{
    /** who may clear (user, 6 Nov 2026) */
    public const USERS = [1, 7];

    public const MODULES = ['registration' => 'Bulk registration', 'attendance' => 'Bulk attendance', 'payment' => 'Bulk payment upload'];

    /** each module's tables, children first, with what they hold */
    private const TABLES = [
        'registration' => [
            'slc_bulk_row_fields' => 'Field decisions',
            'slc_bulk_updates' => '“Our record updated” list',
            'slc_bulk_import_rows' => 'Upload lines',
            'slc_bulk_batches' => 'Batches',
            'slc_bulk_imports' => 'Uploads',
            'slc_bulk_no_actions' => 'Remembered No actions',
        ],
        'attendance' => [
            'slc_attendance_row_fields' => 'Field decisions',
            'slc_attendance_import_rows' => 'Upload lines',
            'slc_attendance_batches' => 'Batches',
            'slc_attendance_imports' => 'Uploads',
        ],
        'payment' => [
            'slc_payment_upload_rows' => 'Upload lines',
            'slc_payment_uploads' => 'Uploads',
        ],
    ];

    /** Activity is one table: attendance and payment entries carry these prefixes, registration's the rest */
    private const LOG_PREFIX = ['attendance' => 'Bulk attendance – ', 'payment' => 'Bulk payment – '];

    public static function allowed($userId): bool
    {
        return in_array((int) $userId, self::USERS, true);
    }

    /** @return list<array{table:string, label:string, count:int}> what Clear DB would delete */
    public function counts(string $module): array
    {
        $out = array_map(fn ($t, $label) => ['table' => $t, 'label' => $label, 'count' => DB::table($t)->count()], array_keys(self::TABLES[$module]), self::TABLES[$module]);
        $out[] = ['table' => 'slc_bulk_activities', 'label' => 'Activity entries', 'count' => $this->activities($module)->count()];

        return $out;
    }

    public function clear(string $module, int $userId): string
    {
        $counts = $this->counts($module);
        DB::transaction(function () use ($module) {
            foreach (array_keys(self::TABLES[$module]) as $t) {
                DB::table($t)->delete();
            }
            $this->activities($module)->delete();
        });
        $done = array_values(array_filter($counts, fn ($c) => $c['count'] > 0));
        $what = $done ? implode(', ', array_map(fn ($c) => $c['label'].' '.$c['count'], $done)) : 'nothing to delete';
        // the trail: Activity (the new first entry) and the application log, since Activity was emptied
        SlcBulkActivity::create(['slc_bulk_import_id' => null, 'user_id' => $userId, 'text' => (self::LOG_PREFIX[$module] ?? '').'Clear DB: '.self::MODULES[$module].' data cleared ('.$what.')']);
        Log::warning('SLC Clear DB', ['module' => $module, 'user_id' => $userId, 'deleted' => array_column($counts, 'count', 'table')]);

        return $done ? 'Cleared '.self::MODULES[$module].' data: '.$what.'. Student records, SLC registrations, agreements, CoCs and money receipts were not touched.' : 'There was no '.self::MODULES[$module].' data to clear.';
    }

    /** the module's Activity entries, except earlier Clear DB entries (the trail of who cleared what stays) */
    private function activities(string $module)
    {
        $q = DB::table('slc_bulk_activities')->where('text', 'not like', '%Clear DB: %');
        if (isset(self::LOG_PREFIX[$module])) {
            return $q->where('text', 'like', self::LOG_PREFIX[$module].'%');
        }
        foreach (self::LOG_PREFIX as $prefix) {
            $q->where('text', 'not like', $prefix.'%');
        }

        return $q;
    }
}
