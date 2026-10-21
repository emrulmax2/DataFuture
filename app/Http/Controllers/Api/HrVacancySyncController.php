<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HrVacancy;
use App\Models\HrVacancyType;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Receives a vacancy published in the Operations recruitment module.
 *
 * An internal vacancy (or one open to both) is listed here under
 * HR Portal > Vacancies, with a link back to the advert in Operations, so staff
 * who look for jobs in this system find it.
 *
 * Authenticated by Passport client-credentials (scope sms.hr-vacancies.write),
 * so there is no logged-in user: created_by/updated_by come from the publisher's
 * email when it matches a user here, and the configured system user
 * (services.sms_sync.created_by) otherwise.
 *
 * Idempotent by `reference`. A retried request, or the vacancy being closed
 * later, updates the row it already wrote. A row somebody has deleted here is
 * left deleted: the push must not bring back what HR removed on purpose.
 */
class HrVacancySyncController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'reference'        => ['required', 'string', 'max:32'],
            'title'            => ['required', 'string', 'max:255'],
            /* Internal | External | Both: the names of hr_vacancy_types. */
            'type'             => ['required', 'string', 'in:Internal,External,Both'],
            'link'             => ['required', 'url', 'max:2000'],
            'date'             => ['nullable', 'date'],
            'active'           => ['required', 'boolean'],
            'created_by_email' => ['nullable', 'email', 'max:255'],
        ]);

        $writerId = $this->writerId($data['created_by_email'] ?? null);

        if ($writerId <= 0) {
            return response()->json([
                'message' => 'SMS sync system user is not configured (set SMS_API_SYSTEM_USER_ID).',
            ], 500);
        }

        $type = HrVacancyType::where('name', $data['type'])->first();

        if (! $type) {
            return response()->json(['message' => "No vacancy type named {$data['type']} here."], 422);
        }

        $existing = HrVacancy::withTrashed()->where('ops_requisition_ref', $data['reference'])->first();

        if ($existing?->trashed()) {
            return response()->json(['id' => $existing->id, 'status' => 'deleted_here'], 200);
        }

        $attributes = [
            'hr_vacancy_type_id' => $type->id,
            'title'              => $data['title'],
            'link'               => $data['link'],
            'active'             => $data['active'] ? 1 : 0,
        ];

        /* The setter turns an empty date into '', which a date column refuses. */
        if (! empty($data['date'])) {
            $attributes['date'] = $data['date'];
        }

        if ($existing) {
            $existing->update($attributes + ['updated_by' => $writerId]);
            $vacancy = $existing;
        } else {
            $vacancy = HrVacancy::create($attributes + [
                'ops_requisition_ref' => $data['reference'],
                'created_by'          => $writerId,
            ]);
        }

        return response()->json([
            'id'     => $vacancy->id,
            'status' => $vacancy->wasRecentlyCreated ? 'created' : 'updated',
        ], 200);
    }

    private function writerId(?string $email): int
    {
        $email = strtolower(trim((string) $email));
        $id    = $email !== '' ? (int) (User::where('email', $email)->value('id') ?? 0) : 0;

        return $id > 0 ? $id : (int) config('services.sms_sync.created_by', 1);
    }
}
