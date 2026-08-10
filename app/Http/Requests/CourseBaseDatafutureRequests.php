<?php

namespace App\Http\Requests;

use App\Models\CourseBaseDatafutures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseBaseDatafutureRequests extends FormRequest
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
        $courseId = (int) $this->input('course_id');
        $recordId = (int) $this->input('id');

        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'datafuture_field_id' => ['required', 'integer', 'exists:datafuture_fields,id'],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('course_base_datafutures', 'id')->where(function ($query) use ($courseId, $recordId) {
                    $query->where('course_id', $courseId)
                        ->whereNull('parent_id')
                        ->whereNull('deleted_at');

                    if ($recordId > 0) {
                        $query->where('id', '!=', $recordId);
                    }
                }),
            ],
            'field_value' => ['nullable', 'string', 'max:191'],
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'parent_id' => $this->filled('parent_id') ? $this->input('parent_id') : null,
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_id');

            if (empty($parentId)) {
                return;
            }

            $recordId = (int) $this->input('id');

            if ($recordId > 0 && CourseBaseDatafutures::where('parent_id', $recordId)->exists()) {
                $validator->errors()->add('parent_id', 'A parent field with child fields cannot be assigned to another parent.');
                return;
            }

            $duplicate = CourseBaseDatafutures::where('course_id', $this->input('course_id'))
                ->where('parent_id', $parentId)
                ->where('datafuture_field_id', $this->input('datafuture_field_id'))
                ->when($recordId > 0, function ($query) use ($recordId) {
                    $query->where('id', '!=', $recordId);
                })
                ->exists();

            if ($duplicate) {
                $validator->errors()->add('parent_id', 'This parent already has that child field.');
            }
        });
    }
}
