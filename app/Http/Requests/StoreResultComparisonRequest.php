<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreResultComparisonRequest extends FormRequest
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
        $rules = [];

        // Loop through the inputs to add conditional rules
        foreach ($this->input('id', []) as $index => $value) {
            if ($this->has("id.$index")) {
                
                $rules["grade_id.$index"] = 'required|integer|exists:grades,id';
                $rules["publish_at.$index"] = 'required|date';

            }
        }
        return $rules;
        
    }

    public function messages()
    {
        return [
            'grade_id.*.required' => 'The grade ID is required when either the corresponding Serial or ID is selected.',
            'publish_at.*.required' => 'The Publish date field is required.',
        ];
    }

}
