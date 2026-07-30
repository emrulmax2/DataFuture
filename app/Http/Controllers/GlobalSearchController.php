<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Employee;
use App\Models\Student;
use App\Support\GlobalSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));
        $searchConfig = GlobalSearch::forCurrentUser();
        $canSearchApplicants = $searchConfig['applicants'];
        $canSearchStudents = $searchConfig['students'];
        $canSearchEmployees = $searchConfig['employees'];

        if (Str::length($query) < 2 || !$searchConfig['show']) {
            return response()->json([
                'applicants' => [],
                'students' => [],
                'employees' => [],
                'permissions' => [
                    'applicants' => $canSearchApplicants,
                    'students' => $canSearchStudents,
                    'employees' => $canSearchEmployees,
                ],
            ]);
        }

        $like = '%' . $query . '%';

        $applicants = $canSearchApplicants ? Applicant::with(['status', 'title', 'users'])
            ->where(function ($applicantQuery) use ($like) {
                $applicantQuery->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhere('application_no', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", [$like])
                    ->orWhereHas('users', function ($userQuery) use ($like) {
                        $userQuery->where('email', 'LIKE', $like);
                    });
            })
            ->orderBy('first_name')
            ->limit(6)
            ->get()
            ->map(function (Applicant $applicant) {
                $name = trim((isset($applicant->title->name) ? $applicant->title->name . ' ' : '') . $applicant->first_name . ' ' . $applicant->last_name);
                $reference = $applicant->application_no ?: $applicant->id;

                return [
                    'name' => $name,
                    'meta' => collect([
                        $reference ? '#' . $reference : null,
                        isset($applicant->status->name) ? $applicant->status->name : null,
                    ])->filter()->implode(' / '),
                    'status' => isset($applicant->status->name) ? $applicant->status->name : 'Applicant',
                    'initials' => $this->initials($applicant->first_name, $applicant->last_name),
                    'url' => route('admission.show', $applicant->id),
                ];
            })
            ->values() : collect();

        $students = $canSearchStudents ? Student::with(['status', 'title'])
            ->where(function ($studentQuery) use ($like) {
                $studentQuery->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhere('registration_no', 'LIKE', $like)
                    ->orWhere('application_no', 'LIKE', $like)
                    ->orWhere('df_sid_number', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", [$like]);
            })
            ->orderBy('first_name')
            ->limit(6)
            ->get()
            ->map(function (Student $student) {
                $name = trim((isset($student->title->name) ? $student->title->name . ' ' : '') . $student->first_name . ' ' . $student->last_name);
                $reference = $student->registration_no ?: $student->application_no ?: $student->df_sid_number;

                return [
                    'name' => $name,
                    'meta' => collect([
                        $reference ? '#' . $reference : null,
                        isset($student->status->name) ? $student->status->name : null,
                    ])->filter()->implode(' / '),
                    'status' => isset($student->status->name) ? $student->status->name : 'Student',
                    'initials' => $this->initials($student->first_name, $student->last_name),
                    'url' => route('student.show', $student->id),
                ];
            })
            ->values() : collect();

        $employees = $canSearchEmployees ? Employee::with(['title', 'user', 'employment.employeeJobTitle'])
            ->where(function ($employeeQuery) use ($like) {
                $employeeQuery->where('first_name', 'LIKE', $like)
                    ->orWhere('last_name', 'LIKE', $like)
                    ->orWhere('email', 'LIKE', $like)
                    ->orWhere('telephone', 'LIKE', $like)
                    ->orWhere('mobile', 'LIKE', $like)
                    ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) LIKE ?", [$like])
                    ->orWhereHas('user', function ($userQuery) use ($like) {
                        $userQuery->where('email', 'LIKE', $like);
                    });
            })
            ->orderBy('first_name')
            ->limit(6)
            ->get()
            ->map(function (Employee $employee) {
                $name = trim((isset($employee->title->name) ? $employee->title->name . ' ' : '') . $employee->first_name . ' ' . $employee->last_name);
                $jobTitle = $employee->employment?->employeeJobTitle?->name;
                $email = $employee->employment?->email ?: ($employee->email ?: $employee->user?->email);

                return [
                    'name' => $name,
                    'meta' => collect([$jobTitle, $email])->filter()->implode(' / '),
                    'status' => $employee->status ?: 'Employee',
                    'initials' => $this->initials($employee->first_name, $employee->last_name),
                    'url' => route('profile.employee.view', $employee->id),
                ];
            })
            ->values() : collect();

        return response()->json([
            'applicants' => $applicants,
            'students' => $students,
            'employees' => $employees,
            'permissions' => [
                'applicants' => $canSearchApplicants,
                'students' => $canSearchStudents,
                'employees' => $canSearchEmployees,
            ],
        ]);
    }

    private function initials(?string $firstName, ?string $lastName): string
    {
        $initials = Str::upper(Str::substr((string) $firstName, 0, 1) . Str::substr((string) $lastName, 0, 1));

        return $initials !== '' ? $initials : 'LC';
    }
}
