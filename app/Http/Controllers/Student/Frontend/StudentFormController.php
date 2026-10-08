<?php

namespace App\Http\Controllers\Student\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\FormsTable;
use App\Models\Student;
use App\Models\StudentCourseChangeRequest;
use App\Models\StudentCourseChangeRequestDocument;
use App\Models\StudentRegistrationDiscontinuationDocument;
use App\Models\StudentRegistrationDiscontinuationRequest;
use App\Models\StudentRefundRequest;
use App\Models\StudentRefundRequestDocument;
use App\Models\StudentAcademicAppeal;
use App\Models\StudentAcademicAppealDocument;
use App\Models\StudentMitigatingCircumstance;
use App\Models\StudentMitigatingCircumstanceAssignment;
use App\Models\StudentMitigatingCircumstanceAbsence;
use App\Models\StudentComplaint;
use App\Models\StudentComplaintDocument;
use App\Models\StudentItReport;
use App\Models\StudentItReportDocument;
use App\Models\Venue;
use App\Models\StudentMitigatingCircumstanceDocument;
use App\Jobs\RaiseCourseChangeTicket;
use App\Jobs\RaiseDiscontinuationTicket;
use App\Jobs\RaiseRefundTicket;
use App\Jobs\RaiseAcademicAppealTicket;
use App\Jobs\RaiseMitigatingCircumstanceTicket;
use App\Jobs\RaiseMitigatingAttendanceTicket;
use App\Jobs\RaiseComplaintTicket;
use App\Jobs\RaiseItSupportTicket;
use App\Services\CourseChangeTicket;
use App\Services\RegistrationDiscontinuationTicket;
use App\Services\RefundRequestTicket;
use App\Services\AcademicAppealTicket;
use App\Services\MitigatingCircumstanceTicket;
use App\Services\MitigatingAttendanceTicket;
use App\Services\ComplaintTicket;
use App\Services\ItSupportTicket;
use App\Services\OperationsServiceDeskClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/*
 * The "Do it online" forms that live in the portal instead of Google Forms.
 *
 * Everything is reached through one dynamic URL keyed by the forms_table row,
 * so moving another form in-house is a matter of adding it to $internalForms
 * and writing its blade - the "Do it online" list needs no change. A form id
 * that has no page here still works: the student is sent to the external link
 * the row carries, exactly as before.
 */
class StudentFormController extends Controller
{
    /* forms_table.form_name => the blade under pages/students/frontend/forms.
       Matched on the name rather than the id, because the row ids are not the
       same in every environment. */
    public const INTERNAL_FORMS = [
        'Course Change Request Form' => 'course-change',
        'Registration Discontinuation Form' => 'registration-discontinuation',
        'Refund Request Form' => 'refund-request',
        'Academic Appeal Form Stage1' => 'academic-appeal',
        'Mitigating Circumstances Claim Form' => 'mitigating-circumstances',
        'APPLICATION FOR MITIGATING CIRCUMSTANCES ( Attendance)' => 'mitigating-attendance',
        'Complaint Form Stage 1' => 'complaint',
        'Report any IT issues on campus' => 'it-report',
    ];

    /* Used by the "Do it online" list to decide between this page and the
       external link the row carries. */
    public static function internalPage(?string $formName): ?string
    {
        return self::INTERNAL_FORMS[$formName] ?? null;
    }

    public function show(FormsTable $form)
    {
        $page = self::internalPage($form->form_name);

        if (!$page) {
            return redirect()->away($form->form_link);
        }

        $student = $this->currentStudent();

        if (!$student) {
            return redirect()->route('students.dashboard.forms');
        }

        return view('pages.students.frontend.forms.'.$page, [
            'title' => $form->form_name.' - London Churchill College',
            'breadcrumbs' => [],
            'student' => $student,
            'form' => $form,
            'hideRail' => true,
            /* The enrolment the student is actually on - activeCR is the
               relation flagged active, which is what the rest of the portal
               reads for "current course". */
            'currentCourseName' => $student->activeCR->course->name ?? null,
            'currentCourseStart' => $student->activeCR->course_start_date ?? null,
            /* Four relations in five carry no start date, so the intake the
               enrolment belongs to stands in for it. */
            'currentIntake' => $student->activeCR->semester->name ?? null,
            /* Shown read-only so the student can check we hold the right ones
               before staff try to reach them about the request. */
            'studentEmail' => $student->contact?->institutional_email
                ?: ($student->users->email ?? $student->contact?->personal_email),
            'studentMobile' => $student->contact?->mobile,
            ...$this->pageData($page, $student),
        ]);
    }

    /**
     * Anything only one form needs.
     *
     * @return array<string, mixed>
     */
    private function pageData(string $page, Student $student): array
    {
        if ($page === 'course-change') {
            return [
                /* Every live course except the one they are already on -
                   "change course" to the same course is not a request anyone
                   can act on. */
                'courses' => Course::where('active', 1)
                    ->when($student->activeCR->course->id ?? null, fn ($query, $id) => $query->where('id', '!=', $id))
                    ->orderBy('name')
                    ->get(),
                'submitted' => StudentCourseChangeRequest::where('student_id', $student->id)
                    ->orderBy('id', 'DESC')
                    ->first(),
            ];
        }

        if ($page === 'refund-request') {
            return [
                'submitted' => StudentRefundRequest::where('student_id', $student->id)
                    ->orderBy('id', 'DESC')
                    ->first(),
            ];
        }

        if ($page === 'academic-appeal') {
            return [
                'submitted' => StudentAcademicAppeal::where('student_id', $student->id)
                    ->orderBy('id', 'DESC')
                    ->first(),
            ];
        }

        if ($page === 'complaint') {
            return [
                'submitted' => StudentComplaint::where('student_id', $student->id)
                    ->orderBy('id', 'DESC')
                    ->first(),
            ];
        }

        if ($page === 'it-report') {
            $department = (int) config('services.operations.service_desk.it_support.department_id');

            return [
                'submitted' => StudentItReport::where('student_id', $student->id)
                    ->orderBy('id', 'DESC')
                    ->first(),
                /* Straight from Operations: what IT has opened to students
                   there is exactly what the dropdown offers here. */
                'issueTypes' => app(OperationsServiceDeskClient::class)->studentIssueTypes($department),
                'venues' => Venue::where('active', 1)->orderBy('name')->get(),
            ];
        }

        if (in_array($page, ['mitigating-circumstances', 'mitigating-attendance'], true)) {
            return [
                'submitted' => StudentMitigatingCircumstance::where('student_id', $student->id)
                    ->where('claim_type', $page === 'mitigating-attendance' ? 'attendance' : 'assignment')
                    ->orderBy('id', 'DESC')
                    ->first(),
                'correspondenceAddress' => $this->correspondenceAddress($student),
            ];
        }

        return [
            'submitted' => StudentRegistrationDiscontinuationRequest::where('student_id', $student->id)
                ->orderBy('id', 'DESC')
                ->first(),
        ];
    }

    public function storeCourseChange(Request $request, FormsTable $form, CourseChangeTicket $tickets)
    {
        $student = $this->currentStudent();

        if (!$student) {
            return redirect()->route('students.dashboard.forms');
        }

        $currentCourseId = $student->activeCR->course->id ?? null;

        $validator = Validator::make($request->all(), [
            /* The list on the page already leaves out the course they are on;
               this is the same rule where it cannot be edited away. */
            'proposed_course_id' => [
                'required',
                'exists:courses,id',
                function ($attribute, $value, $fail) use ($currentCourseId) {
                    if ($currentCourseId && (int) $value === (int) $currentCourseId) {
                        $fail('Choose a course other than the one you are on.');
                    }
                },
            ],
            'proposed_start_date' => 'required|date_format:d-m-Y',
            'reason' => 'required|string|min:20',
            'declaration' => 'accepted',
            'documents.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'proposed_course_id.required' => 'Choose the course you would like to move to.',
            'proposed_start_date.required' => 'Tell us when you would like the new course to start.',
            'proposed_start_date.date_format' => 'Pick the start date from the calendar.',
            'reason.required' => 'Please explain why you want to change course.',
            'reason.min' => 'Please give us a little more detail - at least 20 characters.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each supporting document must be 10MB or smaller.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $course = Course::find($request->proposed_course_id);

        $courseChange = StudentCourseChangeRequest::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
            'current_course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'current_course_name' => $student->activeCR->course->name ?? null,
            'current_course_start_date' => $this->dateOrNull($student->activeCR->course_start_date ?? null),
            'proposed_course_id' => $course->id ?? null,
            /* Snapshotted so the request still reads correctly if the course is
               renamed or retired later. */
            'proposed_course_name' => $course->name ?? null,
            'proposed_start_date' => Carbon::createFromFormat('d-m-Y', $request->proposed_start_date)->format('Y-m-d'),
            'reason' => $request->reason,
            'declaration_accepted' => 1,
            'declaration_name' => trim($student->first_name.' '.$student->last_name),
            'status' => 'Pending',
            'created_by' => auth('student')->user()->id,
        ]);

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $courseChange->id);

                if (! $stored) {
                    continue;
                }

                StudentCourseChangeRequestDocument::create([
                    'student_course_change_request_id' => $courseChange->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        /* The request is safely stored by now, so raising the ticket cannot
           cost the student their submission. It is attempted here rather than
           on the queue so they normally see their reference straight away; if
           Operations is unreachable the retry job picks it up. */
        $raised = $tickets->raise($courseChange, $uploaded);

        if (! $raised) {
            RaiseCourseChangeTicket::dispatch($courseChange->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $courseChange->id]);
    }

    /**
     * Registration Discontinuation Form.
     *
     * The two follow-up questions are optional, but an answer of "yes" makes
     * its own detail field meaningful, so those are required only then - a
     * "yes" with nothing after it tells Registry nothing.
     */
    public function storeDiscontinuation(Request $request, FormsTable $form, RegistrationDiscontinuationTicket $tickets)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('students.dashboard.forms');
        }

        $validator = Validator::make($request->all(), [
            'effective_from' => 'required|date_format:d-m-Y',
            'reason' => 'required|string|min:20',
            'discussed_with_staff' => 'nullable|in:yes,no',
            'staff_details' => 'nullable|string|max:191|required_if:discussed_with_staff,yes',
            'offer_from_other_institute' => 'nullable|in:yes,no',
            'institute_name' => 'nullable|string|max:191|required_if:offer_from_other_institute,yes',
            'institute_start_date' => 'nullable|date_format:d-m-Y',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'effective_from.required' => 'Tell us the date you want this to take effect.',
            'effective_from.date_format' => 'Pick the effective date from the calendar.',
            'reason.required' => 'Please tell us why you want to discontinue.',
            'reason.min' => 'Please give us a little more detail - at least 20 characters.',
            'staff_details.required_if' => 'Tell us who you spoke to at the College.',
            'institute_name.required_if' => 'Tell us which institute made you the offer.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.max' => 'You can attach up to 5 documents.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each supporting document must be 5MB or smaller.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $discussed = $request->discussed_with_staff;
        $offer = $request->offer_from_other_institute;

        $discontinuation = StudentRegistrationDiscontinuationRequest::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            /* Snapshotted: a withdrawal is a record of what they were on when
               they asked, and the enrolment can move afterwards. */
            'course_name' => $student->activeCR->course->name ?? null,
            'effective_from' => Carbon::createFromFormat('d-m-Y', $request->effective_from)->format('Y-m-d'),
            'reason' => $request->reason,
            'discussed_with_staff' => $discussed,
            'staff_details' => $discussed === 'yes' ? $request->staff_details : null,
            'offer_from_other_institute' => $offer,
            'institute_name' => $offer === 'yes' ? $request->institute_name : null,
            'institute_start_date' => $offer === 'yes' && $request->institute_start_date
                ? Carbon::createFromFormat('d-m-Y', $request->institute_start_date)->format('Y-m-d')
                : null,
            'declaration_accepted' => 1,
            'declaration_name' => trim($student->first_name.' '.$student->last_name),
            /* The browser's own reading of the device, which the sender can
               edit; the IP and user agent come from the request, which they
               cannot. The form tells the student both are recorded. */
            'device_type' => $request->input('device_type'),
            'device_os' => $request->input('device_os'),
            'device_browser' => $request->input('device_browser'),
            'device_screen' => $request->input('device_screen'),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'status' => 'Pending',
            'created_by' => auth('student')->user()->id,
        ]);

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $discontinuation->id);

                if (! $stored) {
                    continue;
                }

                StudentRegistrationDiscontinuationDocument::create([
                    'request_id' => $discontinuation->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        if (! $tickets->raise($discontinuation, $uploaded)) {
            RaiseDiscontinuationTicket::dispatch($discontinuation->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $discontinuation->id]);
    }

    /**
     * Refund Request Form.
     *
     * The bank details are validated to shape here and encrypted by the model
     * on the way in. The account holder is never taken from the request: the
     * College refunds the student, so it is read from their record.
     */
    public function storeRefund(Request $request, FormsTable $form, RefundRequestTicket $tickets)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('students.dashboard.forms');
        }

        /* Typed as 12-34-56 and stored as digits; the amount may arrive with a
           pound sign or thousands separators if the script did not clean it. */
        $request->merge([
            'account_number' => preg_replace('/\D/', '', (string) $request->input('account_number')),
            'sort_code' => preg_replace('/\D/', '', (string) $request->input('sort_code')),
            'refund_amount' => preg_replace('/[^\d.]/', '', (string) $request->input('refund_amount')),
        ]);

        $validator = Validator::make($request->all(), [
            'refund_amount' => 'required|numeric|min:0.01|max:999999.99',
            'reason' => 'required|string|min:20',
            'account_number' => 'required|digits:8',
            'sort_code' => 'required|digits:6',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'refund_amount.required' => 'Enter the refund amount.',
            'refund_amount.numeric' => 'Enter an amount in pounds, for example 250 or 250.50.',
            'refund_amount.min' => 'Enter an amount greater than zero.',
            'reason.required' => 'Please tell us why you are requesting this refund.',
            'reason.min' => 'Please give us a little more detail - at least 20 characters.',
            'account_number.required' => 'Enter the account number.',
            'account_number.digits' => 'Account number must be 8 digits.',
            'sort_code.required' => 'Enter the sort code.',
            'sort_code.digits' => 'Sort code must be 6 digits.',
            'declaration.accepted' => 'You need to confirm the declaration before submitting.',
            'documents.max' => 'You can attach up to 5 documents.',
            'documents.*.mimes' => 'Supporting documents must be a PDF, Word file or an image.',
            'documents.*.max' => 'Each supporting document must be 5MB or smaller.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $accountNumber = $request->input('account_number');

        $refund = StudentRefundRequest::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            'intake_semester' => $student->activeCR->semester->name ?? null,
            'refund_amount' => $request->input('refund_amount'),
            'reason' => $request->reason,
            /* Not posted by the form: refunds are paid to the student, so the
               name on the account is the name on the record. */
            'account_holder_name' => trim($student->first_name.' '.$student->last_name),
            'account_number' => $accountNumber,
            'sort_code' => $request->input('sort_code'),
            /* In the clear on purpose - enough to recognise the account in a
               summary or an email without decrypting anything. */
            'account_number_last4' => substr($accountNumber, -4),
            'declaration_accepted' => 1,
            'declaration_name' => trim($student->first_name.' '.$student->last_name),
            'device_type' => $request->input('device_type'),
            'device_os' => $request->input('device_os'),
            'device_browser' => $request->input('device_browser'),
            'device_screen' => $request->input('device_screen'),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'status' => 'Pending',
            'created_by' => auth('student')->user()->id,
        ]);

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $refund->id);

                if (! $stored) {
                    continue;
                }

                StudentRefundRequestDocument::create([
                    'request_id' => $refund->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        if (! $tickets->raise($refund, $uploaded)) {
            RaiseRefundTicket::dispatch($refund->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $refund->id]);
    }

    /**
     * Academic Appeal Form, Stage 1.
     *
     * Two rules here are the policy rather than the form: an appeal can only be
     * made once formal notification of results has been received, and choosing
     * "I will upload my documents" means a document has to arrive with it.
     */
    public function storeAcademicAppeal(Request $request, FormsTable $form, AcademicAppealTicket $tickets)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('students.dashboard.forms');
        }

        $validator = Validator::make($request->all(), [
            'notified' => 'required|in:yes,no',
            'grounds' => 'required|in:mitigating_circumstances,procedural_irregularity',
            'early_resolution' => 'required|string|min:20',
            'decision' => 'required|string|min:20',
            'outcome' => 'required|string|min:20',
            'evidence_method' => 'required|in:upload,none',
            /* Only asked for when nothing is being sent - otherwise the
               documents themselves are the answer. */
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
           were notified cannot be accepted - the form says so as it is filled
           in, and this is the same rule where it cannot be edited away. */
        $validator->after(function ($validator) use ($request) {
            if ($request->input('notified') === 'no') {
                $validator->errors()->add(
                    'notified',
                    'You can only appeal once you have received formal notification of your results.'
                );
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $appeal = StudentAcademicAppeal::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
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
            'created_by' => auth('student')->user()->id,
        ]);

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $appeal->id);

                if (! $stored) {
                    continue;
                }

                StudentAcademicAppealDocument::create([
                    'request_id' => $appeal->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        if (! $tickets->raise($appeal, $uploaded)) {
            RaiseAcademicAppealTicket::dispatch($appeal->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $appeal->id]);
    }

    /**
     * Mitigating Circumstances Claim Form (Assignment).
     *
     * A claim can name several assignments, each with its own deadline, so they
     * arrive as rows and are stored as rows. "Other" as a reason is only an
     * answer once it has been described.
     */
    public function storeMitigatingCircumstances(Request $request, FormsTable $form, MitigatingCircumstanceTicket $tickets)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('students.dashboard.forms');
        }

        $validator = Validator::make($request->all(), [
            'assignments' => 'required|array|min:1|max:10',
            'assignments.*.title' => 'required|string|max:191',
            'assignments.*.submission_date' => 'required|date_format:d-m-Y',
            'reasons' => 'required|array|min:1',
            'reasons.*' => 'in:personal_illness,bereavement,domestic_personal_problems,victim_of_serious_crime,other',
            'other_reason' => 'nullable|string|max:191|required_if:reasons.*,other',
            'claim_details' => 'required|string|min:20',
            'evidence_method' => 'required|in:upload,none',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5|required_if:evidence_method,upload',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'assignments.required' => 'Add at least one assignment.',
            'assignments.*.title.required' => 'Enter the title of each assignment.',
            'assignments.*.submission_date.required' => 'Enter the submission date of each assignment.',
            'assignments.*.submission_date.date_format' => 'Pick each submission date from the calendar.',
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

        /* "Other" means nothing to the panel without the student's own words,
           and required_if cannot look inside an array of values. */
        $validator->after(function ($validator) use ($request) {
            if (in_array('other', (array) $request->input('reasons', []), true)
                && ! trim((string) $request->input('other_reason'))) {
                $validator->errors()->add('other_reason', 'Describe your other reason.');
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $reasons = (array) $request->input('reasons', []);

        $claim = StudentMitigatingCircumstance::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            /* Snapshotted from the record, as shown on the form - a claim is
               read months later, by which time they may have moved. */
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
            'created_by' => auth('student')->user()->id,
        ]);

        foreach ((array) $request->input('assignments', []) as $assignment) {
            StudentMitigatingCircumstanceAssignment::create([
                'request_id' => $claim->id,
                'title' => $assignment['title'],
                'submission_date' => Carbon::createFromFormat('d-m-Y', $assignment['submission_date'])->format('Y-m-d'),
            ]);
        }

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $claim->id);

                if (! $stored) {
                    continue;
                }

                StudentMitigatingCircumstanceDocument::create([
                    'request_id' => $claim->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        if (! $tickets->raise($claim->load('assignments'), $uploaded)) {
            RaiseMitigatingCircumstanceTicket::dispatch($claim->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $claim->id]);
    }

    /** The term-time address the portal shows, as one line. */
    private function correspondenceAddress(Student $student): ?string
    {
        $address = $student->contact?->term_time_address_id
            ? $student->contact->termaddress
            : null;

        if (! $address) {
            return null;
        }

        return implode(', ', array_filter([
            $address->address_line_1 ?? null,
            $address->address_line_2 ?? null,
            $address->city ?? null,
            $address->state ?? null,
            $address->post_code ?? null,
            $address->country ?? null,
        ])) ?: null;
    }

    /**
     * Mitigating Circumstances Claim Form (Attendance).
     *
     * The same claim as the assignment form, about days missed rather than
     * deadlines. A last day is optional - one day's absence, or one that is
     * still going on, has no end - but when given it cannot precede the first.
     */
    public function storeMitigatingAttendance(Request $request, FormsTable $form, MitigatingAttendanceTicket $tickets)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('students.dashboard.forms');
        }

        $validator = Validator::make($request->all(), [
            'absences' => 'required|array|min:1|max:20',
            'absences.*.from' => 'required|date_format:d-m-Y',
            'absences.*.to' => 'nullable|date_format:d-m-Y',
            'absences.*.details' => 'required|string|max:500',
            'reasons' => 'required|array|min:1',
            'reasons.*' => 'in:personal_illness,bereavement,domestic_personal_problems,victim_of_serious_crime,other',
            'claim_details' => 'required|string|min:20',
            'evidence_method' => 'required|in:upload,none',
            'declaration' => 'accepted',
            'documents' => 'nullable|array|max:5|required_if:evidence_method,upload',
            'documents.*' => 'file|max:5120|mimes:pdf,doc,docx,jpg,jpeg,png',
        ], [
            'absences.required' => 'Add at least one instance of non-attendance.',
            'absences.*.from.required' => 'Enter the first day absent for each instance.',
            'absences.*.from.date_format' => 'Pick each first day from the calendar.',
            'absences.*.to.date_format' => 'Pick each last day from the calendar.',
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

            /* An absence that ends before it starts is a typo, and it would
               otherwise be read as a zero-day absence. */
            foreach ((array) $request->input('absences', []) as $index => $absence) {
                $from = $this->dateFromInput($absence['from'] ?? null);
                $to = $this->dateFromInput($absence['to'] ?? null);

                if ($from && $to && $to->lt($from)) {
                    $validator->errors()->add(
                        'absences.'.$index.'.to',
                        'The last day absent cannot be before the first day.'
                    );
                }
            }
        });

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $reasons = (array) $request->input('reasons', []);

        $claim = StudentMitigatingCircumstance::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
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
            'created_by' => auth('student')->user()->id,
        ]);

        foreach ((array) $request->input('absences', []) as $absence) {
            StudentMitigatingCircumstanceAbsence::create([
                'request_id' => $claim->id,
                'absent_from' => $this->dateFromInput($absence['from'])->format('Y-m-d'),
                'absent_to' => ! empty($absence['to'])
                    ? $this->dateFromInput($absence['to'])->format('Y-m-d')
                    : null,
                'details' => $absence['details'],
            ]);
        }

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $claim->id);

                if (! $stored) {
                    continue;
                }

                StudentMitigatingCircumstanceDocument::create([
                    'request_id' => $claim->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        if (! $tickets->raise($claim->load('absences'), $uploaded)) {
            RaiseMitigatingAttendanceTicket::dispatch($claim->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $claim->id]);
    }

    /** A DD-MM-YYYY value from the form, or null if it is absent or malformed. */
    private function dateFromInput($value): ?Carbon
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d-m-Y', $value)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Complaint Form Stage 1.
     *
     * "Have you spoken to anyone?" is optional, but answering yes makes the
     * details meaningful - a name is what lets the investigator start
     * somewhere, so it is required once that answer is given.
     */
    public function storeComplaint(Request $request, FormsTable $form, ComplaintTicket $tickets)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('students.dashboard.forms');
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
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $spoken = $request->spoken_to_anyone;

        $complaint = StudentComplaint::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            /* Only students can reach this page, so the answer the paper form
               asks for is known - but it is recorded, not assumed. */
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
            'created_by' => auth('student')->user()->id,
        ]);

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $complaint->id);

                if (! $stored) {
                    continue;
                }

                StudentComplaintDocument::create([
                    'request_id' => $complaint->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        if (! $tickets->raise($complaint, $uploaded)) {
            RaiseComplaintTicket::dispatch($complaint->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $complaint->id]);
    }

    /**
     * Report an IT issue on campus.
     *
     * The issue type is the student's choice, and the only one of these forms
     * where it is: IT answers for many kinds of request. The list comes from
     * Operations and so does the check - a type that is not open to students
     * there is not accepted here, however the form was edited.
     */
    public function storeItReport(Request $request, FormsTable $form, ItSupportTicket $tickets, OperationsServiceDeskClient $operations)
    {
        $student = $this->currentStudent();

        if (! $student) {
            return redirect()->route('students.dashboard.forms');
        }

        $department = (int) config('services.operations.service_desk.it_support.department_id');
        $issueTypes = collect($operations->studentIssueTypes($department));

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
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $venue = Venue::find($request->venue_id);
        $issueType = $issueTypes->firstWhere('id', (int) $request->issue_type_id);

        $report = StudentItReport::create([
            'student_id' => $student->id,
            'forms_table_id' => $form->id,
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
            'created_by' => auth('student')->user()->id,
        ]);

        /* Read before storing: storeAs() moves the temp file, and the ticket
           needs the bytes. */
        $uploaded = $this->uploadedContents($request);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $documentName = time().'_'.$document->getClientOriginalName();
                $stored = $this->storeDocument($document, $student->id, $documentName, $report->id);

                if (! $stored) {
                    continue;
                }

                StudentItReportDocument::create([
                    'request_id' => $report->id,
                    'doc_type' => $document->getClientOriginalExtension(),
                    'disk_type' => $stored['disk'],
                    'path' => $stored['url'],
                    'storage_key' => $stored['key'],
                    'display_file_name' => $document->getClientOriginalName(),
                    'current_file_name' => $documentName,
                    'created_by' => auth('student')->user()->id,
                ]);
            }
        }

        if (! $tickets->raise($report, $uploaded)) {
            RaiseItSupportTicket::dispatch($report->id)->delay(now()->addMinute());
        }

        return redirect()->route('students.doitonline.form.submitted', [$form->id, $report->id]);
    }

    /**
     * The confirmation page a student lands on after submitting.
     *
     * Its own URL rather than a flash on the form: a reference is something
     * people re-read, go back to and screenshot, and a message that evaporates
     * on refresh is no use for that. It returns them to their dashboard on its
     * own after a few seconds, so the form is not left sitting in front of
     * somebody who has finished with it.
     */
    public function submitted(FormsTable $form, $requestId)
    {
        $student = $this->currentStudent();
        $page = self::internalPage($form->form_name);

        $row = match ($page) {
            'course-change' => StudentCourseChangeRequest::with('documents')->find($requestId),
            'refund-request' => StudentRefundRequest::with('documents')->find($requestId),
            'academic-appeal' => StudentAcademicAppeal::with('documents')->find($requestId),
            'mitigating-circumstances' => StudentMitigatingCircumstance::with('documents', 'assignments')->find($requestId),
            'mitigating-attendance' => StudentMitigatingCircumstance::with('documents', 'absences')->find($requestId),
            'complaint' => StudentComplaint::with('documents')->find($requestId),
            'it-report' => StudentItReport::with('documents')->find($requestId),
            default => StudentRegistrationDiscontinuationRequest::with('documents')->find($requestId),
        };

        /* Somebody else's request is not theirs to read, reference and all. */
        if (! $student || ! $row || $row->student_id !== $student->id):
            abort(404);
        endif;

        return view('pages.students.frontend.forms.submitted', [
            'title' => 'Request submitted - London Churchill College',
            'breadcrumbs' => [],
            'student' => $student,
            'form' => $form,
            'hideRail' => true,
            'request' => $row,
            'lead' => match ($page) {
                'course-change' => 'Your course change request is with the College Registry.',
                'refund-request' => 'Your refund request is with the College.',
                'academic-appeal' => 'Your appeal has been submitted and will be considered under the Academic Appeals Policy.',
                'mitigating-circumstances', 'mitigating-attendance' => 'Your claim is with the Mitigating Circumstances Panel. You will receive an outcome approximately within 14 working days.',
                'complaint' => 'Your complaint has been submitted and will be handled under the Complaints Policy and Procedure.',
                'it-report' => 'Your report is with IT & Monitoring.',
                default => 'Your registration discontinuation request is with the College Registry.',
            },
            'summary' => $this->summaryFor($page, $row),
            'redirectAfter' => 10,
        ]);
    }

    /**
     * The few lines worth repeating back on the confirmation page.
     *
     * Enough for the student to see their request was read correctly, not the
     * whole form again - the receipt email carries that.
     *
     * @return array<string, string>
     */
    private function summaryFor(string $page, $row): array
    {
        $date = fn ($value) => $value ? date('j F Y', strtotime((string) $value)) : '—';
        $documents = $row->documents->pluck('display_file_name')->filter();

        if ($page === 'course-change') {
            return [
                'Current course' => $row->current_course_name ?: '—',
                'Requested course' => $row->proposed_course_name ?: '—',
                'Requested start date' => $date($row->proposed_start_date),
                'Supporting documents' => $documents->count() ? $documents->implode(', ') : 'None provided',
            ];
        }

        if ($page === 'it-report') {
            return [
                'Issue type' => $row->issue_type_name ?: '—',
                'Campus' => $row->campus ?: '—',
                'Location' => $row->location ?: 'Not given',
                'Attachments' => $documents->count() ? $documents->implode(', ') : 'None attached',
            ];
        }

        if ($page === 'complaint') {
            return [
                'Programme / course' => $row->course_name ?: '—',
                'Spoken to anyone' => $row->spokenToLabel(),
                'Supporting documents' => $documents->count() ? $documents->implode(', ') : 'None attached',
            ];
        }

        if ($page === 'mitigating-attendance') {
            return [
                'Non-attendance' => $row->absences
                    ->map(fn ($a) => $a->period().' — '.$a->details)
                    ->implode('; ') ?: '—',
                'Reasons' => implode('; ', $row->reasonLabels()) ?: '—',
                'Supporting documents' => $row->evidence_method === 'upload'
                    ? ($documents->count() ? $documents->implode(', ') : 'None received')
                    : $row->evidenceMethodLabel(),
            ];
        }

        if ($page === 'mitigating-circumstances') {
            return [
                'Assignments' => $row->assignments
                    ->map(fn ($a) => $a->title.($a->submission_date ? ' (due '.$date($a->submission_date).')' : ''))
                    ->implode('; ') ?: '—',
                'Reasons' => implode('; ', $row->reasonLabels()) ?: '—',
                'Supporting documents' => $row->evidence_method === 'upload'
                    ? ($documents->count() ? $documents->implode(', ') : 'None received')
                    : $row->evidenceMethodLabel(),
            ];
        }

        if ($page === 'academic-appeal') {
            return [
                'Grounds for appeal' => $row->groundsLabel(),
                'Programme / course' => $row->course_name ?: '—',
                'Supporting evidence' => $row->evidence_method === 'upload'
                    ? ($documents->count() ? $documents->implode(', ') : 'None received')
                    : $row->evidenceMethodLabel(),
            ];
        }

        if ($page === 'refund-request') {
            return [
                'Refund amount' => '£'.number_format((float) $row->refund_amount, 2),
                'Course' => $row->course_name ?: '—',
                /* The account is named by its last four digits; the rest is
                   encrypted and has no business on a page or in an email. */
                'Bank account' => $row->maskedAccount(),
                'Supporting documents' => $documents->count() ? $documents->implode(', ') : 'None provided',
            ];
        }

        return [
            'Course' => $row->course_name ?: '—',
            'Effective from' => $date($row->effective_from),
            'Discussed with LCC staff' => $row->discussed_with_staff
                ? ucfirst($row->discussed_with_staff).($row->staff_details ? ' — '.$row->staff_details : '')
                : 'Not answered',
            'Offer from another institute' => $row->offer_from_other_institute
                ? ucfirst($row->offer_from_other_institute).($row->institute_name ? ' — '.$row->institute_name : '')
                : 'Not answered',
            'Supporting documents' => $documents->count() ? $documents->implode(', ') : 'None provided',
        ];
    }

    /**
     * Put one upload away, on S3 or, failing that, locally.
     *
     * S3 is where student documents live, but it can refuse — bad credentials,
     * an outage, a bucket that is not there — and when it does the file must
     * not simply vanish: it is part of a request the student has already made.
     * The local disk keeps it until somebody can move it.
     *
     * @return array{disk: string, key: string, url: ?string}|null
     */
    private function storeDocument($document, int $studentId, string $documentName, int $requestId): ?array
    {
        $folder = 'students/'.$studentId;

        foreach ([['s3', 'public/'.$folder], ['public', $folder]] as [$disk, $directory]) {
            try {
                $key = $document->storeAs($directory, $documentName, $disk);
            } catch (\Throwable $e) {
                $key = false;
                Log::warning('[Course change] Document store failed on '.$disk.'.', [
                    'request' => $requestId, 'error' => $e->getMessage(),
                ]);
            }

            if ($key === false) {
                continue;
            }

            try {
                $url = Storage::disk($disk)->url($key);
            } catch (\Throwable $e) {
                $url = null;
            }

            if ($disk !== 's3') {
                Log::warning('[Course change] Document stored locally; S3 would not take it.', [
                    'request' => $requestId, 'key' => $key,
                ]);
            }

            return ['disk' => $disk, 'key' => $key, 'url' => $url];
        }

        Log::error('[Course change] Document could not be stored anywhere.', [
            'request' => $requestId, 'file' => $document->getClientOriginalName(),
        ]);

        return null;
    }

    /**
     * The files as uploaded, read once for the ticket.
     *
     * Read before they are moved to storage, so the ticket carries exactly what
     * the student attached without a round trip back to the bucket.
     *
     * @return array<int, array{name: string, contents: string}>
     */
    private function uploadedContents(Request $request): array
    {
        return collect($request->file('documents', []))
            ->filter()
            ->map(fn ($document) => [
                'name' => $document->getClientOriginalName(),
                'contents' => file_get_contents($document->getRealPath()),
            ])
            ->values()
            ->all();
    }

    /* course_start_date is an empty string on most relations rather than
       null, which no date column will take. */
    private function dateOrNull($value)
    {
        return $value && !empty((string) $value) ? $value : null;
    }

    /* The portal lets a student user hold more than one student record and
       switch between them, so the session pick wins over the newest record. */
    private function currentStudent()
    {
        $selectedStudentId = session('selected_student_id');

        return $selectedStudentId
            ? Student::with('activeCR.course', 'activeCR.semester', 'contact', 'users')->find($selectedStudentId)
            : Student::with('activeCR.course', 'activeCR.semester', 'contact', 'users')
                ->where('student_user_id', auth('student')->user()->id)
                ->orderBy('id', 'DESC')
                ->first();
    }
}
