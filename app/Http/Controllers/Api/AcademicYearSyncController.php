<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Exposes the SMS academic years for external synchronisation (the Operations
 * app mirrors these into its Student Engagement tracker).
 *
 * Dates are emitted as ISO `Y-m-d` (read raw, bypassing the model's d-m-Y
 * accessors). `is_current` is derived from today falling within from/to.
 *
 * Protected by Passport client_credentials with the `sms.academic-years.read` scope.
 */
class AcademicYearSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);
        $today = Carbon::today();

        $years = AcademicYear::query()
            ->select(['id', 'name', 'from_date', 'to_date', 'updated_at'])
            ->orderBy('from_date')
            ->paginate($perPage);

        $years->getCollection()->transform(function (AcademicYear $year) use ($today) {
            $from = $year->getRawOriginal('from_date');
            $to   = $year->getRawOriginal('to_date');

            return [
                'id'         => $year->id,
                'name'       => $year->name,
                'start_date' => $this->isoDate($from),
                'end_date'   => $this->isoDate($to),
                'is_current' => $this->isCurrent($today, $from, $to),
                'updated_at' => optional($year->updated_at)->toISOString(),
            ];
        });

        return response()->json([
            'data' => $years->items(),
            'meta' => [
                'current_page' => $years->currentPage(),
                'last_page'    => $years->lastPage(),
                'per_page'     => $years->perPage(),
                'total'        => $years->total(),
            ],
        ]);
    }

    private function isoDate($raw): ?string
    {
        if (empty($raw)) {
            return null;
        }
        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function isCurrent(Carbon $today, $from, $to): bool
    {
        if (empty($from) || empty($to)) {
            return false;
        }
        try {
            return $today->betweenIncluded(Carbon::parse($from), Carbon::parse($to));
        } catch (\Throwable) {
            return false;
        }
    }
}
