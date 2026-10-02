<?php

namespace App\Http\Requests\PolicyAssessment;

use App\Services\PolicyAssessmentService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Add / edit a policy in the question bank: where the PDF lives and the test
 * rules (pass mark, questions per test, attempts, time limit). Links must be
 * http(s) — staff are redirected to pdf_url, so nothing else may be stored
 * there. A blank time limit means the test is untimed.
 */
class PolicyDocumentRequest extends FormRequest
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
        /* A live category, or — when editing — the one the policy is already
           in, even if HR has since archived it, so the policy can still be
           edited without having to move it. */
        $policy = $this->route('policy');
        $currentCategoryId = (isset($policy->policy_category_id) ? (int) $policy->policy_category_id : 0);
        $category = Rule::exists('policy_categories', 'id')->where(function ($query) use ($currentCategoryId) {
            $query->whereNull('deleted_at');
            if($currentCategoryId > 0):
                $query->orWhere('id', $currentCategoryId);
            endif;
        });

        return [
            'policy_category_id' => ['required', 'integer', $category],
            'title' => 'required|string|max:191',
            'version' => 'nullable|string|max:50',
            'pdf_url' => 'nullable|string|max:500|url:http,https',
            'page_url' => 'nullable|string|max:500|url:http,https',
            'description' => 'nullable|string|max:5000',
            'pass_mark' => 'required|integer|between:1,100',
            'questions_per_attempt' => 'required|integer|between:1,50',
            'max_attempts' => 'nullable|integer|between:1,100',
            'time_limit_minutes' => 'nullable|integer|min:1|max:600',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|in:0,1',
        ];
    }

    public function messages(): array
    {
        return [
            'policy_category_id.required' => 'Please choose a category.',
            'policy_category_id.exists' => 'Please choose a category.',
            'title.required' => 'Please enter the policy title.',
            'pdf_url.url' => 'Enter a full link starting with https://',
            'page_url.url' => 'Enter a full link starting with https://',
            'pass_mark.required' => 'Please enter a pass mark.',
            'pass_mark.between' => 'The pass mark must be between 1 and 100.',
            'questions_per_attempt.required' => 'Please enter how many questions each test has.',
            'questions_per_attempt.between' => 'Each test can have between 1 and 50 questions.',
            'max_attempts.between' => 'Max attempts must be between 1 and 100, or blank for unlimited.',
            'time_limit_minutes.integer' => 'Enter the time limit in whole minutes, or leave it blank for an untimed test.',
            'time_limit_minutes.min' => 'The time limit must be between 1 and 600 minutes, or blank for an untimed test.',
            'time_limit_minutes.max' => 'The time limit must be between 1 and 600 minutes, or blank for an untimed test.',
        ];
    }

    public function attributes(): array
    {
        return [
            'policy_category_id' => 'category',
            'pdf_url' => 'PDF link',
            'page_url' => 'page link',
            'questions_per_attempt' => 'questions per test',
            'time_limit_minutes' => 'time limit',
        ];
    }
}
