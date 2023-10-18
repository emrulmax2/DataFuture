<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentProofOfIdCheckRequest extends FormRequest
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
            'proof_type' => 'required',
            'proof_id' => 'required',
            'proof_expiredate' => 'required|date'
        ];
    }
}
