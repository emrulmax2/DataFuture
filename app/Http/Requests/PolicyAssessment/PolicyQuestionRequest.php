<?php

namespace App\Http\Requests\PolicyAssessment;

use App\Services\PolicyAssessmentService;
use App\Support\PolicyLevel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add / edit a multiple-choice question.
 *
 * options   = [ i => ['id' => existing option id (edit only), 'text' => '...'] ]  (2 to 6 rows)
 * correct   = the key i of the right option
 * level     = beginner | intermediate | expert (PolicyLevel) — how hard the
 *             question is; every exam draws a set share of each level
 *
 * Errors come back keyed options.i.text, which the page maps to row i.
 */
class PolicyQuestionRequest extends FormRequest
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
        $options = $this->input('options');
        $keys = (is_array($options) ? array_map('strval', array_keys($options)) : []);

        return [
            'question' => 'required|string|max:1000',
            'explanation' => 'nullable|string|max:2000',
            'source_excerpt' => 'nullable|string|max:2000',
            'options' => 'required|array|min:2|max:6',
            'options.*' => 'array',
            'options.*.id' => 'nullable|integer',
            'options.*.text' => 'required|string|max:500|distinct:ignore_case',
            'correct' => ['required', 'integer', Rule::in($keys)],
            'level' => ['required', 'string', Rule::in(PolicyLevel::all())],
            'is_active' => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'question.required' => 'Please enter the question.',
            'question.max' => 'Keep the question under 1,000 characters.',
            'options.required' => 'Add at least two options.',
            'options.min' => 'Add at least two options.',
            'options.max' => 'A question can have at most six options.',
            'options.*.text.required' => 'Enter the text for this option, or remove it.',
            'options.*.text.max' => 'Keep each option under 500 characters.',
            'options.*.text.distinct' => 'Each option must be different.',
            'correct.required' => 'Choose which option is the correct answer.',
            'correct.integer' => 'Choose which option is the correct answer.',
            'correct.in' => 'Choose which option is the correct answer.',
            'level.required' => 'Choose the level for this question.',
            'level.string' => 'Choose the level for this question.',
            'level.in' => 'Choose Beginner, Intermediate or Expert.',
        ];
    }

    public function attributes(): array
    {
        return [
            'options.*.text' => 'option',
            'source_excerpt' => 'source excerpt',
        ];
    }
}
