<?php

namespace App\Http\Request;

use Illuminate\Foundation\Http\FormRequest;

class MachineLoginRequest extends FormRequest
{
    
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'username' => 'required|exists:employee_attendance_machines',
            'password' => 'required'
        ];
    }
}
