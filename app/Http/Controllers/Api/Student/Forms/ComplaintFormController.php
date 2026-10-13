<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseComplaintTicket;
use App\Models\StudentComplaint;
use App\Models\StudentComplaintDocument;
use App\Services\ComplaintTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Complaint Form Stage 1, for the student app. */
class ComplaintFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 7;
    }

    public function show(Request $request)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        return $this->ok([
            'student' => $this->studentBlock($student),
            /* The paper form asks because staff and visitors use it too; from
               the app it can only be a student, but it is recorded, not
               assumed. */
            'relationship' => 'Student',
            'policy_url' => 'https://lcc.ac.uk/policies/complaints-policy-and-procedure/',
            'registrar_emails' => ['registry.Support@lcc.ac.uk', 'registrar@londonchurchillcollege.ac.uk'],
            'rules' => [
                'spoken_to_details_required_when' => ['spoken_to_anyone' => 'yes'],
            ],
            'limits' => [
                'text_min' => 20,
                'spoken_to_details_max' => 191,
                'documents_max' => 5,
                'document_size_kb' => 5120,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            ],
        ]);
    }

    public function store(Request $request, ComplaintTicket $tickets)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $validator = Validator::make($request->all(), [
            'complaint_details' => 'required|string|min:20',
            'spoken_to_anyone' => 'nullable|in:yes,no',
            'spoken_to_details' => 'nullable|string|max:191|required_if:spoken_to_anyone,yes',
            'attempts' => 'required|string|min:20',
            'desired_outcome' => 'required|string|min:20',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'complaint_details.required' => 'Explain your complaint.',
            'complaint_details.min' => 'Please give us a little more detail - at least 20 characters.',
            'spoken_to_details.required_if' => 'Tell us who you spoke to.',
            'attempts.required' => 'Explain how you have tried to resolve your complaint and why you remain dissatisfied.',
            'attempts.min' => 'Please give us a little more detail - at least 20 characters.',
            'desired_outcome.required' => 'Explain what you would like to happen.',
            'desired_outcome.min' => 'Please give us a little more detail - at least 20 characters.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.max' => 'You can attach up to 5 documents.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each document must be 5MB or smaller.',
        ]);

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $spoken = $request->spoken_to_anyone;

        $complaint = StudentComplaint::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            'relationship' => 'Student',
            'complaint_details' => $request->complaint_details,
            'spoken_to_anyone' => $spoken,
            'spoken_to_details' => $spoken === 'yes' ? $request->spoken_to_details : null,
            'attempts' => $request->attempts,
            'desired_outcome' => $request->desired_outcome,
            'declaration_accepted' => 1,
            'declaration_name' => trim($student->first_name.' '.$student->last_name),
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

        $this->storeDocuments($request, $student, $complaint->id, function (array $file) use ($complaint, $request) {
            StudentComplaintDocument::create($file + [
                'request_id' => $complaint->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($complaint, $uploaded);

        if (! $raised) {
            RaiseComplaintTicket::dispatch($complaint->id)->delay(now()->addMinute());
        }

        return $this->submitted($complaint->fresh(), $raised);
    }
}
