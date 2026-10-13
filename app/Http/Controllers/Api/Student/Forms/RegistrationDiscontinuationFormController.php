<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseDiscontinuationTicket;
use App\Models\StudentRegistrationDiscontinuationDocument;
use App\Models\StudentRegistrationDiscontinuationRequest;
use App\Services\RegistrationDiscontinuationTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Registration Discontinuation Form, for the student app. */
class RegistrationDiscontinuationFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 12;
    }

    public function show(Request $request)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        return $this->ok([
            'student' => $this->studentBlock($student),
            'limits' => [
                'reason_min' => 20,
                'documents_max' => 5,
                'document_size_kb' => 5120,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
                'staff_details_max' => 191,
                'institute_name_max' => 191,
            ],
        ]);
    }

    public function store(Request $request, RegistrationDiscontinuationTicket $tickets)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $validator = Validator::make($request->all(), [
            'effective_from' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|min:20',
            'discussed_with_staff' => 'nullable|in:yes,no',
            'staff_details' => 'nullable|string|max:191|required_if:discussed_with_staff,yes',
            'offer_from_other_institute' => 'nullable|in:yes,no',
            'institute_name' => 'nullable|string|max:191|required_if:offer_from_other_institute,yes',
            'institute_start_date' => 'nullable|date_format:Y-m-d',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'effective_from.required' => 'Tell us the date you want this to take effect.',
            'effective_from.date_format' => 'Send the effective date as YYYY-MM-DD.',
            'reason.required' => 'Please tell us why you want to discontinue.',
            'reason.min' => 'Please give us a little more detail - at least 20 characters.',
            'staff_details.required_if' => 'Tell us who you spoke to at the College.',
            'institute_name.required_if' => 'Tell us which institute made you the offer.',
            'institute_start_date.date_format' => 'Send the start date as YYYY-MM-DD.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.max' => 'You can attach up to 5 documents.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each supporting document must be 5MB or smaller.',
        ]);

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $discussed = $request->discussed_with_staff;
        $offer = $request->offer_from_other_institute;

        $discontinuation = StudentRegistrationDiscontinuationRequest::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            /* Snapshotted: a withdrawal is a record of what they were on when
               they asked, and the enrolment can move afterwards. */
            'course_name' => $student->activeCR->course->name ?? null,
            'effective_from' => $this->fromIsoDate($request->effective_from),
            'reason' => $request->reason,
            'discussed_with_staff' => $discussed,
            'staff_details' => $discussed === 'yes' ? $request->staff_details : null,
            'offer_from_other_institute' => $offer,
            'institute_name' => $offer === 'yes' ? $request->institute_name : null,
            'institute_start_date' => $offer === 'yes' ? $this->fromIsoDate($request->institute_start_date) : null,
            'declaration_accepted' => 1,
            'declaration_name' => trim($student->first_name.' '.$student->last_name),
            /* The app knows its own device; the IP comes from the request,
               which the sender cannot edit. */
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

        $this->storeDocuments($request, $student, $discontinuation->id, function (array $file) use ($discontinuation, $request) {
            StudentRegistrationDiscontinuationDocument::create($file + [
                'request_id' => $discontinuation->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($discontinuation, $uploaded);

        if (! $raised) {
            RaiseDiscontinuationTicket::dispatch($discontinuation->id)->delay(now()->addMinute());
        }

        return $this->submitted($discontinuation->fresh(), $raised);
    }
}
