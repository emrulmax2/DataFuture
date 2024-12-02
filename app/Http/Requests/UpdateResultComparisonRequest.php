<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateResultComparisonRequest extends FormRequest
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
            "student_id"=> 'required|array',
            "assessment_plan_id"=> 'required|array',
            'grade_id.*' => 'required|integer',
        ];
    }

    public function messages()
    {
        $messages = [];

        // Loop through the possible indices and create custom messages
        foreach (range(0, 100) as $index) {
            $textIndex = 'this';//$this->numberToText($index);
            $messages["grade_id.$index.required"] = "The Grade at $textIndex row position is required.";
            $messages["grade_id.$index.integer"] = "The Grade at $textIndex row position must be an integer.";
        }

        return $messages;
    }

    private function numberToText($number): string
    {
        $textNumbers = [
            'top', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
            'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen', 'twenty',
            'twenty-one', 'twenty-two', 'twenty-three', 'twenty-four', 'twenty-five', 'twenty-six', 'twenty-seven', 'twenty-eight', 'twenty-nine', 'thirty',
            'thirty-one', 'thirty-two', 'thirty-three', 'thirty-four', 'thirty-five', 'thirty-six', 'thirty-seven', 'thirty-eight', 'thirty-nine', 'forty',
            'forty-one', 'forty-two', 'forty-three', 'forty-four', 'forty-five', 'forty-six', 'forty-seven', 'forty-eight', 'forty-nine', 'fifty',
            // Add more as needed
        ];

        return $textNumbers[$number] ?? $number;
    }
}
