<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetsRegisterUpdateRequest extends FormRequest
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
            'description' => 'required',
            'acc_asset_type_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'description.required' => 'This field is required.',
            'acc_asset_type_id.required' => 'This field is required.',
        ];
    }
}
