<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseMitigatingCircumstanceTicket;
use App\Models\StudentMitigatingCircumstance;
use App\Models\StudentMitigatingCircumstanceAssignment;
use App\Models\StudentMitigatingCircumstanceDocument;
use App\Services\MitigatingCircumstanceTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Mitigating Circumstances (Assignment), for the student app.
 *
 * One claim can cover several assignments, so `assignments` is an array of
 * objects — the panel decides each deadline separately.
 */
class MitigatingCircumstancesFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 8;
    }

    public function show(Request $request)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        return $this->ok([
            'student' => $this->studentBlock($student),
            'policy_url' => 'https://lcc.ac.uk/policies/mitigating-circumstance-policy-and-procedure/',
            'reasons' => collect(StudentMitigatingCircumstance::REASONS)
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            'evidence_methods' => [
                ['value' => 'upload', 'label' => 'I will upload my documents with this form'],
                ['value' => 'none', 'label' => 'I do not have any document to send'],
            ],
            'rules' => [
                'other_reason_required_when' => ['reasons_includes' => 'other'],
                'documents_required_when' => ['evidence_method' => 'upload'],
            ],
            'limits' => [
                'claim_details_min' => 20,
                'assignments_max' => 10,
                'other_reason_max' => 191,
                'documents_max' => 5,
                'document_size_kb' => 5120,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            ],
            'outcome_working_days' => 14,
        ]);
    }

    public function store(Request $request, MitigatingCircumstanceTicket $tickets)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array|min:1|max:10',
            'assignments.*.title' => 'required|string|max:191',
            'assignments.*.submission_date' => 'required|date_format:Y-m-d',
            'reasons' => 'required|array|min:1',
            'reasons.*' => 'in:personal_illness,bereavement,domestic_personal_problems,victim_of_serious_crime,other',
            'other_reason' => 'nullable|string|max:191',
            'claim_details' => 'required|string|min:20',
            'evidence_method' => 'required|in:upload,none',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5|required_if:evidence_method,upload',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'assignments.required' => 'Add at least one assignment.',
            'assignments.*.title.required' => 'Enter the title of each assignment.',
            'assignments.*.submission_date.required' => 'Enter the submission date of each assignment.',
            'assignments.*.submission_date.date_format' => 'Send each submission date as YYYY-MM-DD.',
            'reasons.required' => 'Select at least one reason.',
            'claim_details.required' => 'Provide details of your claim.',
            'claim_details.min' => 'Please give us a little more detail - at least 20 characters.',
            'evidence_method.required' => 'Select how you will provide your supporting documents.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.required_if' => 'Attach at least one document, or choose another option.',
            'documents.max' => 'You can attach up to 5 documents.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each document must be 5MB or smaller.',
        ]);

        /* "Other" means nothing to the panel without the student's own words. */
        $validator->after(function ($validator) use ($request) {
            if (in_array('other', (array) $request->input('reasons', []), true)
                && ! trim((string) $request->input('other_reason'))) {
                $validator->errors()->add('other_reason', 'Describe your other reason.');
            }
        });

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $reasons = (array) $request->input('reasons', []);

        $claim = StudentMitigatingCircumstance::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'claim_type' => 'assignment',
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            /* Snapshotted from the record: a claim is read months later, by
               which time they may have moved. */
            'address' => $this->correspondenceAddress($student),
            'reasons' => $reasons,
            'other_reason' => in_array('other', $reasons, true) ? $request->other_reason : null,
            'claim_details' => $request->claim_details,
            'evidence_method' => $request->evidence_method,
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

        foreach ((array) $request->input('assignments', []) as $assignment) {
            StudentMitigatingCircumstanceAssignment::create([
                'request_id' => $claim->id,
                'title' => $assignment['title'],
                'submission_date' => $this->fromIsoDate($assignment['submission_date']),
            ]);
        }

        $uploaded = $this->uploadedContents($request);

        $this->storeDocuments($request, $student, $claim->id, function (array $file) use ($claim, $request) {
            StudentMitigatingCircumstanceDocument::create($file + [
                'request_id' => $claim->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($claim->load('assignments'), $uploaded);

        if (! $raised) {
            RaiseMitigatingCircumstanceTicket::dispatch($claim->id)->delay(now()->addMinute());
        }

        return $this->submitted($claim->fresh(), $raised);
    }
}
