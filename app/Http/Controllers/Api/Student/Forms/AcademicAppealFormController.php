<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseAcademicAppealTicket;
use App\Models\StudentAcademicAppeal;
use App\Models\StudentAcademicAppealDocument;
use App\Services\AcademicAppealTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/** Academic Appeal Form Stage 1, for the student app. */
class AcademicAppealFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 4;
    }

    public function show(Request $request)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        return $this->ok([
            'student' => $this->studentBlock($student),
            'policy_url' => 'https://lcc.ac.uk/policies/academic-appeals-policy-and-procedure/',
            'registrar_email' => 'registry.Support@lcc.ac.uk',
            /* The only grounds the College accepts. */
            'grounds' => collect(StudentAcademicAppeal::GROUNDS)
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
            /* The email route was removed from this form: documents are either
               uploaded with it or there are none. */
            'evidence_methods' => [
                ['value' => 'upload', 'label' => 'I will upload my documents with this form'],
                ['value' => 'none', 'label' => 'I do not have any documents to send'],
            ],
            'rules' => [
                /* An appeal can only be made once results have been notified. */
                'notified_must_be_yes' => true,
                'documents_required_when' => ['evidence_method' => 'upload'],
                'evidence_statement_required_when' => ['evidence_method' => 'none'],
            ],
            'limits' => [
                'text_min' => 20,
                'documents_max' => 5,
                'document_size_kb' => 5120,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            ],
        ]);
    }

    public function store(Request $request, AcademicAppealTicket $tickets)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $validator = Validator::make($request->all(), [
            'notified' => 'required|in:yes,no',
            'grounds' => 'required|in:mitigating_circumstances,procedural_irregularity',
            'early_resolution' => 'required|string|min:20',
            'decision' => 'required|string|min:20',
            'outcome' => 'required|string|min:20',
            'evidence_method' => 'required|in:upload,none',
            'evidence_statement' => 'nullable|string|min:20|required_if:evidence_method,none',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5|required_if:evidence_method,upload',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'notified.required' => 'Tell us whether you have received formal notification of your results.',
            'grounds.required' => 'Select the grounds for your appeal.',
            'early_resolution.required' => 'Tell us what was done to try to resolve this informally.',
            'early_resolution.min' => 'Please give us a little more detail - at least 20 characters.',
            'decision.required' => 'Explain the decision you wish to appeal against.',
            'decision.min' => 'Please give us a little more detail - at least 20 characters.',
            'outcome.required' => 'Tell us the outcome you are seeking.',
            'outcome.min' => 'Please give us a little more detail - at least 20 characters.',
            'evidence_method.required' => 'Select how you will provide your documents.',
            'evidence_statement.required_if' => 'Describe your supporting information, or explain why you cannot provide any.',
            'evidence_statement.min' => 'Please give us a little more detail - at least 20 characters.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.required_if' => 'Attach at least one document, or choose another option.',
            'documents.max' => 'You can attach up to 5 documents.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each document must be 5MB or smaller.',
        ]);

        /* The College only considers appeals against a decision of the
           Assessment and Progress Board, so an appeal made before the results
           were notified cannot be accepted. */
        $validator->after(function ($validator) use ($request) {
            if ($request->input('notified') === 'no') {
                $validator->errors()->add(
                    'notified',
                    'You can only appeal once you have received formal notification of your results.'
                );
            }
        });

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $appeal = StudentAcademicAppeal::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            'notified' => $request->notified,
            'grounds' => $request->grounds,
            'early_resolution' => $request->early_resolution,
            'decision' => $request->decision,
            'outcome' => $request->outcome,
            'evidence_method' => $request->evidence_method,
            'evidence_statement' => $request->evidence_statement,
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

        $this->storeDocuments($request, $student, $appeal->id, function (array $file) use ($appeal, $request) {
            StudentAcademicAppealDocument::create($file + [
                'request_id' => $appeal->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($appeal, $uploaded);

        if (! $raised) {
            RaiseAcademicAppealTicket::dispatch($appeal->id)->delay(now()->addMinute());
        }

        return $this->submitted($appeal->fresh(), $raised);
    }
}
