<?php

namespace App\Http\Requests;

use App\Models\Venue;
use Illuminate\Foundation\Http\FormRequest;

class EmploymentDataSaveRequest extends FormRequest
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
            'started_on' => "required|date_format:d-m-Y",
            'punch_number' => "required|integer",
            /* Kept under the `site_location` key rather than `site_location.*`:
               the wizard's 422 handler turns each error key straight into a
               jQuery class selector, and `.site_location.0` is not a valid one. */
            'site_location' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    $ids = array_unique(array_filter(array_map('intval', (array) $value)));

                    if (empty($ids)):
                        $fail('Please choose at least one site location.');
                        return;
                    endif;

                    if (Venue::whereIn('id', $ids)->count() !== count($ids)):
                        $fail('One of the selected site locations is not a valid venue.');
                    endif;
                },
            ],
            'employee_work_type' => "required",
            'job_title' => "required",
            'department' => "required",
            'email' => "required|unique:users,email",
            'works_number' => 'required_if:employee_work_type,==,3|nullable|integer',
            'notice_period' => "required",
            'ssp_term' => "required",
            'employment_period' => "required",
        ];
    }
}
