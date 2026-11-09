<?php

namespace App\Http\Requests;

use App\Models\ProcessList;
use App\Models\TaskAssignmentRule;
use App\Models\TaskList;
use App\Services\TaskAssignment\ApplicationFacts;
use App\Services\TaskAssignment\StudentFacts;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TaskAssignmentRuleRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'task_list_id'           => 'required|integer|exists:task_lists,id',
            'trigger'                => 'required|in:' . implode(',', array_keys(TaskAssignmentRule::TRIGGERS)),
            'exclusive_group'        => 'nullable|string|max:64',
            'priority'               => 'required|integer|min:1|max:9999',
            'active'                 => 'sometimes|boolean',
            'notes'                  => 'nullable|string|max:1000',
            'after_assign_job'       => 'nullable|string|in:' . implode(',', array_keys($this->jobRegistry())),
            'conditions'             => 'sometimes|array',
            'conditions.*.field'     => 'required|string',
            'conditions.*.operator'  => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'task_list_id.required' => 'Choose the task this rule assigns.',
            'task_list_id.exists'   => 'That task no longer exists.',
            'priority.required'     => 'Give the rule a priority.',
            'after_assign_job.in'   => 'Choose a job from the list.',
            'conditions.*.field.required'    => 'Choose what to test.',
            'conditions.*.operator.required' => 'Choose how to compare.',
        ];
    }

    /** applicant or student, from the trigger the rule is for. */
    private function ruleSubject(): string
    {
        return TaskAssignmentRule::subjectOf((string) $this->input('trigger'));
    }

    private function jobRegistry(): array
    {
        // return (array) config($this->ruleSubject() === TaskAssignmentRule::SUBJECT_STUDENT ? 'tasks.student_assignment_jobs' : 'tasks.assignment_jobs', []);
        return $this->ruleSubject() === TaskAssignmentRule::SUBJECT_STUDENT
            ? (array) config('tasks.student_assignment_jobs', [])
            : (array) config('tasks.assignment_jobs', []) + (array) config('tasks.completion_jobs', []);
    }

    /* The task must belong to the subject's phase (Applicant tasks for applicant rules,
       Live tasks for student rules). Each condition must name a field of that subject's
       catalogue, use an operator that field's type allows, and carry a value of the
       right shape. */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($v) {
            $subject = $this->ruleSubject();
            $facts = StudentFacts::forSubject($subject);
            $phase = $subject === TaskAssignmentRule::SUBJECT_STUDENT ? 'Live' : 'Applicant';
            $taskId = (int) $this->input('task_list_id');
            if ($taskId > 0 && array_key_exists((string) $this->input('trigger'), TaskAssignmentRule::TRIGGERS)
                && !TaskList::where('id', $taskId)->whereIn('process_list_id', ProcessList::where('phase', $phase)->pluck('id'))->exists()) {
                $v->errors()->add('task_list_id', $subject === TaskAssignmentRule::SUBJECT_STUDENT ? 'Choose a student (Live) task for a student trigger.' : 'Choose an applicant task for this trigger.');
            }
            $fields = $facts::fields();
            foreach ((array) $this->input('conditions', []) as $i => $c) {
                $key = $c['field'] ?? null;
                if (!$key || !isset($fields[$key])) {
                    $v->errors()->add("conditions.$i.field", 'Choose what to test.');
                    continue;
                }
                $type = $fields[$key]['type'];
                $op = $c['operator'] ?? '';
                if (!isset($facts::OPERATORS[$type][$op])) {
                    $v->errors()->add("conditions.$i.operator", 'That comparison does not fit this field.');
                    continue;
                }
                $val = $c['value'] ?? null;
                $isList = in_array($op, ['in', 'not_in'], true);
                if ($isList && (!is_array($val) || count(array_filter($val, fn($x) => $x !== '' && $x !== null)) === 0)) {
                    $v->errors()->add("conditions.$i.value", 'Pick at least one option.');
                } elseif (!$isList && ($val === null || $val === '' || is_array($val))) {
                    $v->errors()->add("conditions.$i.value", 'Enter a value.');
                } elseif ($type === 'number' && !$isList && !is_numeric($val)) {
                    $v->errors()->add("conditions.$i.value", 'Enter a number.');
                } elseif ($type === 'select' && !empty($fields[$key]['options'])) {
                    $allowed = array_map('strval', array_keys($facts::optionsFor($fields[$key]['options'])));
                    $given = array_map('strval', $isList ? array_filter((array) $val, fn($x) => $x !== '') : [$val]);
                    if (array_diff($given, $allowed)) {
                        $v->errors()->add("conditions.$i.value", 'Choose from the list.');
                    }
                }
            }
        });
    }
}
