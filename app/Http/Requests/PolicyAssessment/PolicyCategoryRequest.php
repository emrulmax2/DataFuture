<?php

namespace App\Http\Requests\PolicyAssessment;

use App\Services\PolicyAssessmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add / edit a policy category (Corporate, Academic, Student Policies ...).
 * The name must be unique among live categories.
 */
class PolicyCategoryRequest extends FormRequest
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
        $category = $this->route('category');
        $unique = Rule::unique('policy_categories', 'name')->whereNull('deleted_at');
        if(isset($category->id)):
            $unique->ignore($category->id);
        endif;

        return [
            'name' => ['required', 'string', 'max:191', $unique],
            'description' => 'nullable|string|max:2000',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a category name.',
            'name.unique' => 'A category with this name already exists.',
            'sort_order.integer' => 'Sort order must be a whole number.',
        ];
    }
}
