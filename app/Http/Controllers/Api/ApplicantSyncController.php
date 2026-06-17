<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ApplicantSyncController extends Controller
{
    public function index(Request $request)
    {
        $perPage = max((int) $request->integer('per_page', 100), 1);
        $name = trim((string) $request->query('name', ''));
        $applicationNo = trim((string) $request->query('application_no', ''));

        $applicants = Applicant::query()
            ->with(['contact', 'users', 'course.creation.course', 'status','allTasks' => function($query){
                //$query->whereIn('status', ['pending', 'in_progress']);
                $query->where('task_list_id', 7);
            }])
            ->whereIn('status_id', [3])
            ->when($name !== '', function (Builder $query) use ($name) {
                $query->where(function (Builder $nameQuery) use ($name) {
                    $nameQuery->where('first_name', 'like', "%{$name}%")
                        ->orWhere('last_name', 'like', "%{$name}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$name}%"]);
                });
            })
            ->when($applicationNo !== '', function (Builder $query) use ($applicationNo) {
                $query->where('application_no', 'like', "%{$applicationNo}%");
            })
            ->orderBy('id')
            ->paginate($perPage);

        // Flatten to a clean sync DTO. proposed_course is the applicant's proposed
        // course name (applicant -> course -> creation -> course -> name).
        $data = collect($applicants->items())->map(function ($a) {
            $courseName = optional(optional(optional($a->course)->creation)->course)->name;
            $candidateName = trim(($a->first_name ?? '').' '.($a->last_name ?? ''));

            // Email comes from the applicant's login account; mobile from their
            // contact details (falling back to the account phone).
            $email  = optional($a->users)->email;
            $mobile = optional($a->contact)->mobile ?: optional($a->users)->phone;

            return [
                'id'              => $a->id,
                'candidate_name'  => $candidateName,
                'first_name'      => $a->first_name,
                'last_name'       => $a->last_name,
                'application_no'  => $a->application_no,
                'proposed_course' => $courseName,
                'email'           => $email,
                'mobile'          => $mobile,
            ];
        })->all();

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $applicants->currentPage(),
                'last_page' => $applicants->lastPage(),
                'per_page' => $applicants->perPage(),
                'total' => $applicants->total(),
            ],
            'filters' => [
                'name' => $name !== '' ? $name : null,
                'application_no' => $applicationNo !== '' ? $applicationNo : null,
                'status_ids' => [2, 3],
            ],
        ]);
    }
}
