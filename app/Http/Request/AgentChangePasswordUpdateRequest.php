<?php

namespace App\Http\Request;

use App\Models\AgentUser;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class AgentChangePasswordUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // $user = AgentUser::findOrFail($this->id);
        // if (Hash::check($this->old_password, $user->password)) {

        // }
        return [
            'old_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
            'password_confirmation' => 'required'
        ];
    }
}
