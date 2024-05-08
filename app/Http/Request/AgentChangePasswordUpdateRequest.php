<?php

namespace App\Http\Request;

use App\Models\AgentUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

class AgentChangePasswordUpdateRequest extends FormRequest
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
                $user = AgentUser::findOrFail($id);
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
        $value=0;
        return [
            'old_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ];
    }
}
