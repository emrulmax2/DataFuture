<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStudentFirstAddress extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'address_line_1'=> 'required_if:disagree_current_address,=,1',
            'post_code'=> 'required_if:disagree_current_address,=,1',
            'city'=> 'required_if:disagree_current_address,=,1',
            'country'=> 'required_if:disagree_current_address,=,1',

            'permanent_address_line_1'=> 'required_if:disagree_permanent_address,=,1',
            'permanent_post_code'=> 'required_if:disagree_permanent_address,=,1',
            'permanent_city'=> 'required_if:disagree_permanent_address,=,1',
            'permanent_country'=> 'required_if:disagree_permanent_address,=,1',
        ];
    }

    public function messages()
    {
        return [
            'address_line_1.required_if' => 'The address line 1 field is required when their is no term time address set.',
            'post_code.required_if' => 'Postal code field is required if their is no term time address set.',
            'city.required_if' => 'The city field is required when their is no term time address set.',
            'country.required_if' => 'The country field is required when their is no term time address set.',

            'permanent_address_line_1.required_if' => 'The permanent address line 1 field is required when their is different permanent address set.',
            'permanent_post_code.required_if' => 'Permanent postal code field is required if their is different permanent address set.',
            'permanent_city.required_if' => 'The permanent city field is required when their is different permanent address set.',
            'permanent_country.required_if' => 'The permanent country field is required when their is different permanent address set.',
        ];
    }
}
