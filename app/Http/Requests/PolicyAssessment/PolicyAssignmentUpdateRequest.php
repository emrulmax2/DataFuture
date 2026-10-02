<?php

namespace App\Http\Requests\PolicyAssessment;

use App\Services\PolicyAssessmentService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;

/**
 * HR changing the due date and/or note on one assignment. A due date that is
 * already in the past may be kept as it is (so the note can still be
 * edited), but a new date cannot be set in the past. The level is not
 * editable (anything posted as `level` is ignored): its attempts and badge
 * belong to that level, so HR assigns the policy again at another level.
 */
class PolicyAssignmentUpdateRequest extends FormRequest
{
    /**
     * Only policy-assessment managers. Checked here, before the rules run,
     * so the exists/unique rules cannot be used to probe for records.
     */
    public function authorize(): bool
    {
        return PolicyAssessmentService::canManage();
    }

    /** Same 403 message as the controllers give. */
    protected function failedAuthorization()
    {
        throw new AuthorizationException('You are not permitted to manage policy assessments.');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'due_date' => 'nullable|date',
            'note' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'due_date.date' => 'Please enter a valid due date.',
            'note.max' => 'The note may not be longer than 1,000 characters.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $due = $this->input('due_date');
            if($validator->errors()->has('due_date') || $due === null || trim((string) $due) === ''):
                return;
            endif;

            try {
                $newDue = Carbon::parse($due)->startOfDay();
            } catch (\Throwable $e) {
                $validator->errors()->add('due_date', 'Please enter a valid due date.');
                return;
            }

            $assignment = $this->route('assignment');
            $currentDue = (isset($assignment->due_date) && $assignment->due_date ? $assignment->due_date->format('Y-m-d') : null);
            if($newDue->format('Y-m-d') !== $currentDue && $newDue->lt(Carbon::today())):
                $validator->errors()->add('due_date', 'The due date cannot be in the past.');
            endif;
        });
    }
}
