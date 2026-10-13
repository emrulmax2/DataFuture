<?php

namespace App\Http\Controllers\Api\Student\Forms;

use App\Jobs\RaiseRefundTicket;
use App\Models\StudentRefundRequest;
use App\Models\StudentRefundRequestDocument;
use App\Services\RefundRequestTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Refund Request Form, for the student app.
 *
 * The bank details are stored encrypted by the model and never read back to
 * the app: the response names the account by its last four digits only, as the
 * website and the receipt email do.
 */
class RefundFormController extends BaseFormController
{
    protected function formId(): int
    {
        return 11;
    }

    public function show(Request $request)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        return $this->ok([
            'student' => $this->studentBlock($student),
            /* Not a field: the College refunds the student, so the name on the
               account is the name on the record. Sent so the app can show it. */
            'account_holder_name' => trim($student->first_name.' '.$student->last_name),
            'policy_url' => 'https://lcc.ac.uk/policies/tuition-fee-and-refund-policy',
            'limits' => [
                'reason_min' => 20,
                'amount_max' => 999999.99,
                'account_number_digits' => 8,
                'sort_code_digits' => 6,
                'documents_max' => 5,
                'document_size_kb' => 5120,
                'document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
            ],
        ]);
    }

    public function store(Request $request, RefundRequestTicket $tickets)
    {
        $student = $this->formStudent($request);

        if (! $student) {
            return $this->noStudent();
        }

        /* However the app formats them on screen, what is stored is digits. */
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
            return $this->invalid($validator);
        }

        $accountNumber = $request->input('account_number');

        $refund = StudentRefundRequest::create([
            'student_id' => $student->id,
            'forms_table_id' => $this->formId(),
            'course_creation_id' => $student->activeCR->course_creation_id ?? null,
            'course_name' => $student->activeCR->course->name ?? null,
            'intake_semester' => $student->activeCR->semester->name ?? null,
            'refund_amount' => $request->input('refund_amount'),
            'reason' => $request->reason,
            /* Not taken from the request: refunds are paid to the student. */
            'account_holder_name' => trim($student->first_name.' '.$student->last_name),
            'account_number' => $accountNumber,
            'sort_code' => $request->input('sort_code'),
            /* In the clear on purpose — enough to recognise the account in a
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
            'created_by' => $request->user()->id,
        ]);

        $uploaded = $this->uploadedContents($request);

        $this->storeDocuments($request, $student, $refund->id, function (array $file) use ($refund, $request) {
            StudentRefundRequestDocument::create($file + [
                'request_id' => $refund->id,
                'created_by' => $request->user()->id,
            ]);
        });

        $raised = $tickets->raise($refund, $uploaded);

        if (! $raised) {
            RaiseRefundTicket::dispatch($refund->id)->delay(now()->addMinute());
        }

        $response = $this->submitted($refund->fresh(), $raised);
        $body = $response->getData(true);
        /* The account, as it may safely be shown. */
        $body['data']['bank_account'] = $refund->maskedAccount();

        return response()->json($body, $response->status());
    }
}
