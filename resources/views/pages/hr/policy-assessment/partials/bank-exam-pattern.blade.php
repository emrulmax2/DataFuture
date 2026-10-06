{{--
    Policy Assessments › questions page: the "Exam pattern" box — the share of each level every
    exam draws, and the number of questions that gives for this policy's questions per test.

    Needs $pattern: counts['pattern'] from PolicyQuestionController
    (PolicyDocumentController::examPattern()): per_test and, per exam, mix (%) and quota
    (questions). The percentages are PolicyLevel::MIX — never written out here. The rows use
    the same markup as patternRows() in resources/js/policy-assessment-questions.js.
--}}
@php
    $paPatternPerTest = (int) $pattern['per_test'];
@endphp
<div class="pa-pattern" id="examPattern">
    <div class="pa-pattern__head">
        <h3><i data-lucide="pie-chart"></i>Exam pattern</h3>
        <p>
            No exam draws from one level alone: each takes a set share of Beginner, Intermediate and Expert questions.
            With <strong><span data-pattern-per-test>{{ $paPatternPerTest }}</span> <span data-pattern-per-test-word>{{ $paPatternPerTest == 1 ? 'question' : 'questions' }}</span> per test</strong> that gives:
        </p>
    </div>
    <div class="pa-pattern__scroll">
        <table class="pa-pattern__table">
            <caption class="pa-visually-hidden">Questions drawn from each level, and their share of the test, for each exam</caption>
            <thead>
                <tr>
                    <th scope="col">Exam</th>
                    @foreach(\App\Support\PolicyLevel::labels() as $paPatternLevel => $paPatternLabel)
                        <th scope="col">@include('pages.hr.policy-assessment.partials.level-pill', ['level' => $paPatternLevel, 'text' => $paPatternLabel.' questions'])</th>
                    @endforeach
                </tr>
            </thead>
            <tbody id="examPatternRows">
                @foreach($pattern['exams'] as $paPatternExam => $paPatternRow)
                    <tr data-exam="{{ $paPatternExam }}">
                        <th scope="row">{{ $paPatternRow['name'] }}</th>
                        @foreach($paPatternRow['mix'] as $paPatternLevel => $paPatternPercent)
                            @php
                                $paPatternQuota = (int) (isset($paPatternRow['quota'][$paPatternLevel]) ? $paPatternRow['quota'][$paPatternLevel] : 0);
                            @endphp
                            <td class="{{ $paPatternQuota == 0 ? 'is-none' : '' }}" data-level="{{ $paPatternLevel }}" data-percent="{{ (int) $paPatternPercent }}" data-quota="{{ $paPatternQuota }}"><strong>{{ $paPatternQuota }}</strong> <span>{{ $paPatternQuota == 1 ? 'question' : 'questions' }} · {{ (int) $paPatternPercent }}%</span></td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
