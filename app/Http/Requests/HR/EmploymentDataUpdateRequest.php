<?php

namespace App\Http\Requests\HR;

use App\Models\Venue;
use Illuminate\Foundation\Http\FormRequest;

class EmploymentDataUpdateRequest extends FormRequest
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
            'punch_number' => "required",
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
            'employee_work_type_id' => "required",
            'employee_job_title_id' => "required",
            'department_id' => "required",
            'works_number' => 'required_if:employee_work_type_id,==,3|nullable|integer',
            
        ];
    }
}
