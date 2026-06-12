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
            ->with(['contact', 'course', 'status','allTasks' => function($query){
                $query->whereIn('status', ['pending', 'in_progress']);
                $query->where('task_list_id', 7);
            }])
            ->whereIn('status_id', 3)
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

        return response()->json([
            'data' => $applicants->items(),
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
