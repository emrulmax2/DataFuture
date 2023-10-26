<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeePaymentSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'pay_frequency' => 'required',
            'tax_code' => 'required',
            'payment_method' => 'required',

            'beneficiary' => 'required_if:payment_method,"Bank Transfer"',
            'sort_code' => 'required_if:payment_method,"Bank Transfer"',
            'ac_no' => 'required_if:payment_method,"Bank Transfer"',

            'subject_to_clockin' => 'sometimes',
            'hour_authorised_by' => 'required_if:subject_to_clockin,1',

            'holiday_entitled' => 'sometimes',
            'holiday_base' => 'required_if:holiday_entitled,1',
            'bank_holiday_auto_book' => 'required_if:holiday_entitled,1',
            'holiday_authorised_by' => 'required_if:holiday_entitled,1',

            'pension_enrolled' => 'sometimes',
            'employee_info_penssion_scheme_id' => 'required_if:pension_enrolled,1',
            'joining_date' => 'required_if:pension_enrolled,1'
        ];
    }

    public function messages()
    {
        return [
            'pay_frequency.required' => 'This field is required',
            'tax_code.required' => 'This field is required',
            'payment_method.required' => 'This field is required',

            'beneficiary.required_if' => 'This field is required',
            'sort_code.required_if' => 'This field is required',
            'ac_no.required_if' => 'This field is required',

            'holiday_base.required_if' => 'This field is required',
            'bank_holiday_auto_book.required_if' => 'This field is required',
            'holiday_authorised_by.required_if' => 'This field is required',

            'employee_info_penssion_scheme_id.required_if' => 'This field is required',
            'joining_date.required_if' => 'This field is required'
        ];
    }
}
