<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorageRequest extends FormRequest
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
            'transaction_date' => 'required',
            'trans_type' => 'required',
            'expense' => 'required_without:income',
            'income' => 'required_without:expense',
            'acc_category_id_in' => 'required_if:trans_type,0',
            'acc_category_id_out' => 'required_if:trans_type,1',
            'acc_bank_id' => 'required_if:trans_type,3',
        ];
    }
}
