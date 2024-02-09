<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SlcMoneyReceiptRequest extends FormRequest
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
            'invoice_no' => 'required',
            'payment_date' => 'required',
            'slc_payment_method_id' => 'required',
            'term_declaration_id' => 'required',
            'session_term' => 'required',
            'amount' => 'required',
            'payment_type' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'invoice_no.required' => 'This field is required.',
            'payment_date.required' => 'This field is required.',
            'slc_payment_method_id.required' => 'This field is required.',
            'term_declaration_id.required' => 'This field is required.',
            'session_term.required' => 'This field is required.',
            'amount.required' => 'This field is required.',
            'payment_type.required' => 'This field is required.',
        ];
    }
}
