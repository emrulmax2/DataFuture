{{--
    The Assign modals' policy <option>s, grouped by category. Each carries its plain title,
    how many active questions its bank holds at every level (data-drawable-<level>) and, per
    exam level, whether that exam can be drawn in full and if not why (data-exam, JSON:
    {"beginner":{"ready":false,"short":{"expert":1},"why":"needs 1 more Expert question"},...}).
    An exam needs its share of questions from all three levels, so the label starts out
    flagged for a Beginner exam (the level ticked by default) and the page JS re-labels it
    when the level changes. Needs $policyGroups (PolicyAssignmentController::assignOptions()).
--}}
@foreach($policyGroups as $paOptionGroup)
    <optgroup label="{{ $paOptionGroup['name'] }}">
        @foreach($paOptionGroup['policies'] as $paOptionPolicy)
            @php
                $paOptionLevels = (isset($paOptionPolicy['drawable_levels']) && is_array($paOptionPolicy['drawable_levels']) ? $paOptionPolicy['drawable_levels'] : []);
                $paOptionExam = (isset($paOptionPolicy['exam']) && is_array($paOptionPolicy['exam']) ? $paOptionPolicy['exam'] : []);
                $paOptionFirst = (isset($paOptionExam[\App\Support\PolicyLevel::BEGINNER]) ? $paOptionExam[\App\Support\PolicyLevel::BEGINNER] : ['ready' => true, 'why' => '']);
                $paOptionHint = (empty($paOptionFirst['ready']) ? ' (exam not ready'.(!empty($paOptionFirst['why']) ? ': '.$paOptionFirst['why'] : '').')' : '');
            @endphp
            <option value="{{ $paOptionPolicy['id'] }}" data-policy-title="{{ $paOptionPolicy['title'] }}" data-category-id="{{ $paOptionGroup['id'] }}"@foreach(\App\Support\PolicyLevel::all() as $paOptionLevel) data-drawable-{{ $paOptionLevel }}="{{ isset($paOptionLevels[$paOptionLevel]) ? (int) $paOptionLevels[$paOptionLevel] : 0 }}"@endforeach data-exam="{{ json_encode($paOptionExam, JSON_FORCE_OBJECT) }}">{{ $paOptionPolicy['title'] }}{{ $paOptionHint }}</option>
        @endforeach
    </optgroup>
@endforeach
