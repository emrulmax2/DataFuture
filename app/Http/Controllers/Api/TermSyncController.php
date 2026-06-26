<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TermDeclaration;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Exposes the SMS terms (term declarations) for external synchronisation. Each
 * term belongs to an academic year (academic_year_id) so the Operations app can
 * link it to the academic year it mirrored from /academic-years/sync.
 *
 * A short `key` (e.g. "autumn") is derived from the term type for consumers that
 * bucket terms. Dates are emitted as ISO `Y-m-d` (raw, bypassing accessors).
 *
 * Protected by Passport client_credentials with the `sms.terms.read` scope.
 */
class TermSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);

        $terms = TermDeclaration::query()
            ->with('termType:id,name,code')
            ->whereNotNull('academic_year_id')
            ->select(['id', 'name', 'term_type_id', 'academic_year_id', 'start_date', 'end_date', 'is_active', 'updated_at'])
            ->orderBy('academic_year_id')
            ->orderBy('start_date')
            ->paginate($perPage);

        $terms->getCollection()->transform(function (TermDeclaration $term) {
            $type = $term->termType;

            return [
                'id'               => $term->id,
                'name'             => $term->name,
                'key'              => $this->keyFor($type?->code, $type?->name, $term->name),
                'academic_year_id' => $term->academic_year_id,
                'term_type'        => $type?->name,
                'start_date'       => $this->isoDate($term->getRawOriginal('start_date')),
                'end_date'         => $this->isoDate($term->getRawOriginal('end_date')),
                'active'           => (bool) ($term->is_active ?? true),
                'updated_at'       => $this->isoTimestamp($term->updated_at),
            ];
        });

        return response()->json([
            'data' => $terms->items(),
            'meta' => [
                'current_page' => $terms->currentPage(),
                'last_page'    => $terms->lastPage(),
                'per_page'     => $terms->perPage(),
                'total'        => $terms->total(),
            ],
        ]);
    }

    /** Prefer the term-type code, else the first word of the type/term name. */
    private function keyFor(?string $code, ?string $typeName, ?string $termName): string
    {
        $code = trim((string) $code);
        if ($code !== '') {
            return Str::slug($code, '_');
        }
        $source = trim((string) ($typeName ?: $termName));
        return $source !== '' ? Str::slug(strtok($source, ' '), '_') : '';
    }

    private function isoDate($raw): ?string
    {
        if (empty($raw)) {
            return null;
        }
        try {
            $date = Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
        // Don't emit MySQL zero-dates ("0000-00-00" -> year -1) — return null.
        return $date->year < 1970 ? null : $date->toDateString();
    }

    private function isoTimestamp($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            $ts = Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
        return $ts->year < 1970 ? null : $ts->toISOString();
    }
}
