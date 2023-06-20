<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CountryRequest extends FormRequest
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
            'name' => 'required',
            'iso_code' => 'required',
            'is_hesa' => 'sometimes',
            'hesa_code' => 'required_if:is_hesa,1',
            'is_df' => 'sometimes',
            'df_code' => 'required_if:is_df,1',
        ];
    }
}
