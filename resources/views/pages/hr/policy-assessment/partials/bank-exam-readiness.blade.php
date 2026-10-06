{{--
    Policy Assessments › questions page: one card per exam — Ready / Not ready, and what the
    exam needs from each level against the active questions the bank has. An exam draws a set
    share of every level, so one level running short keeps the whole exam from starting.

    Needs $exams: counts['exams'] from PolicyQuestionController (worded by
    PolicyDocumentController::examReadiness()). Same markup as examCard() in
    resources/js/policy-assessment-questions.js, which redraws the cards after every change.
--}}
@php
    $paExamIcons = ['ready' => 'check-circle', 'blocked' => 'x-circle'];
@endphp
@foreach($exams as $paExamKey => $paExam)
    @php
        $paExamState = (isset($paExamIcons[$paExam['state']]) ? $paExam['state'] : 'blocked');
    @endphp
    <div class="pa-exam pa-exam--{{ $paExamKey }} is-{{ $paExamState }}" data-exam="{{ $paExamKey }}" data-ready="{{ $paExam['ready'] ? 1 : 0 }}" role="group" aria-label="{{ $paExam['summary'] }}">
        <div class="pa-exam__top">
            <span class="pa-exam__name">
                {{-- Decorative here: the exam is named by the text beside it. --}}
                <span class="pa-exam__medal" aria-hidden="true">@include('pages.hr.policy-assessment.partials.level-badge', ['level' => $paExamKey, 'size' => 'sm', 'title' => null, 'date' => null, 'revoked' => false])</span>
                <strong>{{ $paExam['name'] }}</strong>
            </span>
            <span class="pa-level-state pa-readiness--{{ $paExamState }}"><i data-lucide="{{ $paExamIcons[$paExamState] }}"></i>{{ $paExam['state_label'] }}</span>
        </div>
        <div class="pa-exam__rows">
            @foreach($paExam['needed'] as $paRowLevel => $paRowNeeded)
                @php
                    $paRowHas = (int) (isset($paExam['available'][$paRowLevel]) ? $paExam['available'][$paRowLevel] : 0);
                    $paRowShort = (int) (isset($paExam['short'][$paRowLevel]) ? $paExam['short'][$paRowLevel] : 0);
                @endphp
                <div class="pa-exam__row{{ $paRowShort > 0 ? ' is-short' : '' }}" data-level="{{ $paRowLevel }}" data-needed="{{ (int) $paRowNeeded }}" data-available="{{ $paRowHas }}" data-short="{{ $paRowShort }}">
                    @include('pages.hr.policy-assessment.partials.level-pill', ['level' => $paRowLevel, 'text' => null])
                    <span class="pa-exam__need">needs <strong>{{ (int) $paRowNeeded }}</strong></span>
                    <span class="pa-exam__have">has <strong>{{ $paRowHas }}</strong></span>
                    <span class="pa-exam__mark" aria-hidden="true"><i data-lucide="{{ $paRowShort > 0 ? 'x' : 'check' }}"></i></span>
                </div>
            @endforeach
        </div>
        <p class="pa-exam__note">{{ $paExam['note'] }}</p>
    </div>
@endforeach
