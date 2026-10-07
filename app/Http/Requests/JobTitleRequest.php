<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JobTitleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|unique:employee_job_titles,name',
            'department_id' => 'required|exists:departments,id',
        ];
    }

    /** Otherwise the message reads "The department id field is required." */
    public function attributes()
    {
        return [
            'department_id' => 'department',
        ];
    }
}
