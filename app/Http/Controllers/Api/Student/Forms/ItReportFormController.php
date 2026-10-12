<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseItSupportTicket;
use App\Models\StudentItReport;
use App\Models\StudentItReportDocument;
use App\Models\Venue;
use App\Services\ItSupportTicket;
use App\Services\OperationsServiceDeskClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Report an IT issue on campus, for the student app.
 *
 * The one form where the student picks the issue type. The options are read
 * live from Operations' own Service Desk settings — whatever IT & Monitoring
 * has opened to students — and the same list is what the submission is checked
 * against, so a type the desk has not opened cannot be posted whatever the app
 * sends.
 */
class ItReportFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 15;
    }

    private function departmentId(): int
    {
        return (int) config('services.operations.service_desk.it_support.department_id');
    }

    public function show(Request $request, OperationsServiceDeskClient $operations)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $issueTypes = $operations->studentIssueTypes($this->departmentId());

        return $this->ok([
            'student' => $this->studentBlock($student),
            'issue_types' => $issueTypes,
            /* False when the service desk cannot be reached: the app should say
               so rather than offer an empty picker. */
            'issue_types_available' => count($issueTypes) > 0,
            'campuses' => Venue::where('active', 1)->orderBy('name')->get(['id', 'name'])
                ->map(fn ($venue) => ['id' => $venue->id, 'name' => $venue->name])
                ->all(),
            'limits' => [
                'description_min' => 20,
                'location_max' => 191,
                'documents_max' => 5,
                'document_size_kb' => 5120,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            ],
        ]);
    }

    public function store(Request $request, ItSupportTicket $tickets, OperationsServiceDeskClient $operations)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $issueTypes = collect($operations->studentIssueTypes($this->departmentId()));

        $validator = Validator::make($request->all(), [
            'issue_type_id' => [
                'required',
                function ($attribute, $value, $fail) use ($issueTypes) {
                    if ($issueTypes->isEmpty()) {
                        $fail('We cannot reach the IT service desk at the moment. Please try again shortly.');

                        return;
                    }

                    if (! $issueTypes->contains('id', (int) $value)) {
                        $fail('Choose an issue type from the list.');
                    }
                },
            ],
            'venue_id' => 'required|exists:venues,id',
            'location' => 'nullable|string|max:191',
            'description' => 'required|string|min:20',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'issue_type_id.required' => 'Select the type of issue.',
            'venue_id.required' => 'Select the campus the issue is on.',
            'description.required' => 'Describe the issue.',
            'description.min' => 'Please give us a little more detail - at least 20 characters.',
            'documents.max' => 'You can attach up to 5 files.',
            'documents.*.mimes' => 'Attachments must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each attachment must be 5MB or smaller.',
        ]);

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $venue = Venue::find($request->venue_id);
        $issueType = $issueTypes->firstWhere('id', (int) $request->issue_type_id);

        $report = StudentItReport::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            'issue_type_id' => $issueType['id'] ?? null,
            /* Kept as well as the id: the id is what Operations was told, the
               name is what the student saw, and types get renamed. */
            'issue_type_name' => $issueType['name'] ?? null,
            'venue_id' => $venue->id ?? null,
            'campus' => $venue->name ?? null,
            'location' => $request->location,
            'description' => $request->description,
            'device_type' => $request->input('device_type'),
            'device_os' => $request->input('device_os'),
            'device_browser' => $request->input('device_browser'),
            'device_screen' => $request->input('device_screen'),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'status' => 'Pending',
            'created_by' => $request->user()->id,
        ]);

        $uploaded = $this->uploadedContents($request);

        $this->storeDocuments($request, $student, $report->id, function (array $file) use ($report, $request) {
            StudentItReportDocument::create($file + [
                'request_id' => $report->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($report, $uploaded);

        if (! $raised) {
            RaiseItSupportTicket::dispatch($report->id)->delay(now()->addMinute());
        }

        return $this->submitted($report->fresh(), $raised);
    }
}
