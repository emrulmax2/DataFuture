<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseMitigatingAttendanceTicket;
use App\Models\StudentMitigatingCircumstance;
use App\Models\StudentMitigatingCircumstanceAbsence;
use App\Models\StudentMitigatingCircumstanceDocument;
use App\Services\MitigatingAttendanceTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Mitigating Circumstances (Attendance), for the student app.
 *
 * The same claim as the assignment form, about days missed rather than
 * deadlines. `absent_to` is optional: one day's absence, or one that is still
 * going on, has no end.
 */
class MitigatingAttendanceFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 16;
    }

    public function show(Request $request)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        return $this->ok([
            'student' => $this->studentBlock($student),
            'policy_urls' => [
                'mitigating_circumstances' => 'https://lcc.ac.uk/policies/mitigating-circumstance-policy-and-procedure/',
                'attendance' => 'https://lcc.ac.uk/policies/attendance-policy/',
            ],
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
                'absent_to_not_before_absent_from' => true,
            ],
            'limits' => [
                'claim_details_min' => 20,
                'absences_max' => 20,
                'details_max' => 500,
                'other_reason_max' => 191,
                'documents_max' => 5,
                'document_size_kb' => 5120,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            ],
            'outcome_working_days' => 14,
        ]);
    }

    public function store(Request $request, MitigatingAttendanceTicket $tickets)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        $validator = Validator::make($request->all(), [
            'absences' => 'required|array|min:1|max:20',
            'absences.*.absent_from' => 'required|date_format:Y-m-d',
            'absences.*.absent_to' => 'nullable|date_format:Y-m-d',
            'absences.*.details' => 'required|string|max:500',
            'reasons' => 'required|array|min:1',
            'reasons.*' => 'in:personal_illness,bereavement,domestic_personal_problems,victim_of_serious_crime,other',
            'other_reason' => 'nullable|string|max:191',
            'claim_details' => 'required|string|min:20',
            'evidence_method' => 'required|in:upload,none',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5|required_if:evidence_method,upload',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'absences.required' => 'Add at least one instance of non-attendance.',
            'absences.*.absent_from.required' => 'Enter the first day absent for each instance.',
            'absences.*.absent_from.date_format' => 'Send each first day as YYYY-MM-DD.',
            'absences.*.absent_to.date_format' => 'Send each last day as YYYY-MM-DD.',
            'absences.*.details.required' => 'Enter the details for each instance.',
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

        $validator->after(function ($validator) use ($request) {
            if (in_array('other', (array) $request->input('reasons', []), true)
                && ! trim((string) $request->input('other_reason'))) {
                $validator->errors()->add('other_reason', 'Describe your other reason.');
            }

            /* An absence that ends before it starts is a typo, and would
               otherwise be read as a zero-day absence. */
            foreach ((array) $request->input('absences', []) as $index => $absence) {
                $from = $this->fromIsoDate($absence['absent_from'] ?? null);
                $to = $this->fromIsoDate($absence['absent_to'] ?? null);

                if ($from && $to && Carbon::parse($to)->lt(Carbon::parse($from))) {
                    $validator->errors()->add(
                        'absences.'.$index.'.absent_to',
                        'The last day absent cannot be before the first day.'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return $this->invalid($validator);
        }

        $reasons = (array) $request->input('reasons', []);

        $claim = StudentMitigatingCircumstance::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'claim_type' => 'attendance',
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
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

        foreach ((array) $request->input('absences', []) as $absence) {
            StudentMitigatingCircumstanceAbsence::create([
                'request_id' => $claim->id,
                'absent_from' => $this->fromIsoDate($absence['absent_from']),
                'absent_to' => ! empty($absence['absent_to']) ? $this->fromIsoDate($absence['absent_to']) : null,
                'details' => $absence['details'],
            ]);
        }

        $uploaded = $this->uploadedContents($request);

        $this->storeDocuments($request, $student, $claim->id, function (array $file) use ($claim, $request) {
            StudentMitigatingCircumstanceDocument::create($file + [
                'request_id' => $claim->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($claim->load('absences'), $uploaded);

        if (! $raised) {
            RaiseMitigatingAttendanceTicket::dispatch($claim->id)->delay(now()->addMinute());
        }

        return $this->submitted($claim->fresh(), $raised);
    }
}
