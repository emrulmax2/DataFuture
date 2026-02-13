<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentDocumentRequestFormRequest extends FormRequest
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
            "student_id" => "required|exists:students,id",
            "term_declaration_id" => "required|exists:term_declarations,id",
            "letter_set_id" => "required|exists:letter_sets,id",
            "description" => "required|string|max:255",
            "service_type" => "required|in:Same Day (cost £10.00),3 Working Days (Free),3 Working Days (cost £10.00),Printer Top Up (cost £5.00)",
            "status" => "nullable|in:Pending,In Progress,Approved,Rejected",
            "student_consent" => "required",
        ];
    }
}
