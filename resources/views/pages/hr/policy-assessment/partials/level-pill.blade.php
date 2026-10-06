{{--
    Policy Assessments: a level pill — "Beginner" / "Intermediate" / "Expert" in the
    level's colours (bronze / silver / gold). Same markup as levelPill() in
    resources/js/policy-assessment/levels.js; styles in resources/css/policy-assessment/levels.css.

    @include('pages.hr.policy-assessment.partials.level-pill', [
        'level' => $assignment->level,   // beginner | intermediate | expert (anything else reads as beginner)
        'text' => null,                  // optional: replaces the label, e.g. 'B 18/20'
    ])

    @include passes the parent view's variables through: pass 'text' => null unless
    you mean to replace the label.
--}}
@php
    $paPillLevel = (isset($level) && is_string($level) && \App\Support\PolicyLevel::isValid($level) ? $level : \App\Support\PolicyLevel::BEGINNER);
    $paPillText = (isset($text) && is_scalar($text) && trim((string) $text) !== '' ? trim((string) $text) : \App\Support\PolicyLevel::label($paPillLevel));
@endphp
<span class="pa-level-pill pa-level-pill--{{ $paPillLevel }}"><span class="pa-level-pill__dot" aria-hidden="true"></span>{{ $paPillText }}</span>
