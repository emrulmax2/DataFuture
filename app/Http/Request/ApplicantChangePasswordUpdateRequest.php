<?php

namespace App\Http\Request;

use App\Models\ApplicantUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Validator;

class ApplicantChangePasswordUpdateRequest extends FormRequest
{    
    /**
    * @param  \Illuminate\Validation\Validator  $validator
    * @return void
    */
    public function withValidator(Validator $validator)
    {
        $oldPassword = $validator->getData()['old_password'] ?? '';
        $id = $validator->getData()['id'];

        $validator->after(
            function ($validator) use ($oldPassword,$id) {
                $user = ApplicantUser::findOrFail($id);
                if (!Hash::check($oldPassword, $user->password)) {
                    $validator->errors()->add(
                        'old_password',
                        'Old password didn\'t matched'
                    );
                }
            }
        );                            
    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'old_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ];
    }
}
