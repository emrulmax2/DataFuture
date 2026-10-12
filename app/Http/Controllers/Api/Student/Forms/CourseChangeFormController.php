<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseCourseChangeTicket;
use App\Models\Course;
use App\Models\StudentCourseChangeRequest;
use App\Models\StudentCourseChangeRequestDocument;
use App\Services\CourseChangeTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Course Change Request Form, for the student app. */
class CourseChangeFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 9;
    }

    /** What the app needs to draw the form. */
    public function show(Request $request)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        return $this->ok([
            'student' => $this->studentBlock($student),
            /* Every live course except the one they are already on — "change
               course" to the same course is not a request anyone can act on. */
            'courses' => Course::where('active', 1)
                ->when($student->activeCR->course->id ?? null, fn ($query, $id) => $query->where('id', '!=', $id))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn ($course) => ['id' => $course->id, 'name' => $course->name])
                ->all(),
            'limits' => [
                'reason_min' => 20,
                'documents_max' => 5,
                'document_size_kb' => 10240,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            ],
        ]);
    }

    public function store(Request $request, CourseChangeTicket $tickets)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $currentCourseId = $student->activeCR->course->id ?? null;

        $validator = Validator::make($request->all(), [
            'proposed_course_id' => [
                'required',
                'exists:courses,id',
                function ($attribute, $value, $fail) use ($currentCourseId) {
                    if ($currentCourseId && (int) $value === (int) $currentCourseId) {
                        $fail('Choose a course other than the one you are on.');
                    }
                },
            ],
            'proposed_start_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|min:20',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'proposed_course_id.required' => 'Choose the course you would like to move to.',
            'proposed_start_date.required' => 'Tell us when you would like the new course to start.',
            'proposed_start_date.date_format' => 'Send the start date as YYYY-MM-DD.',
            'reason.required' => 'Please explain why you want to change course.',
            'reason.min' => 'Please give us a little more detail - at least 20 characters.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.max' => 'You can attach up to 5 documents.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each supporting document must be 10MB or smaller.',
        ]);

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $course = Course::find($request->proposed_course_id);

        $courseChange = StudentCourseChangeRequest::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'current_course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'current_course_name' => $student->activeCR->course->name ?? null,
            'current_course_start_date' => $this->dateOrNull($student->activeCR->course_start_date ?? null),
            'proposed_course_id' => $course->id ?? null,
            /* Snapshotted so the request still reads correctly if the course is
               renamed or retired later. */
            'proposed_course_name' => $course->name ?? null,
            'proposed_start_date' => $this->fromIsoDate($request->proposed_start_date),
            'reason' => $request->reason,
            'declaration_accepted' => 1,
            'declaration_name' => trim($student->first_name.' '.$student->last_name),
            'status' => 'Pending',
            'created_by' => $request->user()->id,
        ]);

        $uploaded = $this->uploadedContents($request);

        $this->storeDocuments($request, $student, $courseChange->id, function (array $file) use ($courseChange, $request) {
            StudentCourseChangeRequestDocument::create($file + [
                'student_course_change_request_id' => $courseChange->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($courseChange, $uploaded);

        if (! $raised) {
            RaiseCourseChangeTicket::dispatch($courseChange->id)->delay(now()->addMinute());
        }

        return $this->submitted($courseChange->fresh(), $raised);
    }
}
