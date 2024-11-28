<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayslipSyncUploadUpdateRequest extends FormRequest
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
            'employee_id.*' => 'required|integer|exists:employees,id',
        ];
    }

    
    public function messages(): array
    {
        $messages = [];

        // Loop through the possible indices and create custom messages
        foreach (range(0, 100) as $index) {
            $textIndex = "top"; //$this->numberToText($index);
            $messages["employee_id.$index.required"] = "The employee at $textIndex row position is required.";
            $messages["employee_id.$index.integer"] = "The employee at $textIndex row position must be an integer.";
            $messages["employee_id.$index.exists"] = "The employee at $textIndex row position does not exist.";
        }

        return $messages;
    }

    private function numberToText($number): string
    {
        $textNumbers = [
            'top', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
            'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty',
            // Add more as needed
        ];

        return $textNumbers[$number] ?? $number;
    }
}
