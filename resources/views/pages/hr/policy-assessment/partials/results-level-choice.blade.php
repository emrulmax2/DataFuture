{{--
    Exam level picker for the Assign modals (Assignments page and the employee profile tab):
    three radio cards posting `level`, Beginner ticked. Each card shows the exam's question
    pattern (an exam always mixes all three question levels). The page JS re-labels the
    policy picker when the level changes.

    Needs $levelMix — PolicyLevel::MIX as passed by the controller
    (PolicyAssignmentController::assignOptions()['levelMix']): [exam level => [question level => %]].

    @include('pages.hr.policy-assessment.partials.results-level-choice', ['levelChoiceId' => 'pa_assign_level'])
--}}
@php
    $paLevelChoiceId = (isset($levelChoiceId) && is_string($levelChoiceId) && $levelChoiceId !== '' ? $levelChoiceId : 'pa_level');
    $paLevelMix = (isset($levelMix) && is_array($levelMix) ? $levelMix : []);
    $paLevelHelp = [
        \App\Support\PolicyLevel::BEGINNER => 'Mostly the core rules everyone must know.',
        \App\Support\PolicyLevel::INTERMEDIATE => 'Core rules and day-to-day application in equal measure.',
        \App\Support\PolicyLevel::EXPERT => 'Mostly detail, exceptions and harder cases.',
    ];
@endphp
<fieldset class="pa-assign-level" aria-describedby="{{ $paLevelChoiceId }}_hint">
    <legend class="pa-assign-level__legend">Exam level <span>*</span></legend>
    <small id="{{ $paLevelChoiceId }}_hint" class="pa-assign-hint">Every exam mixes Beginner, Intermediate and Expert questions in the pattern shown. Meeting the target earns the level's badge.</small>
    <div class="pa-assign-level__grid">
        @foreach(\App\Support\PolicyLevel::labels() as $paLevelKey => $paLevelLabel)
            @php
                $paCardMix = (isset($paLevelMix[$paLevelKey]) && is_array($paLevelMix[$paLevelKey]) ? $paLevelMix[$paLevelKey] : []);
                $paCardMixParts = [];
                foreach($paCardMix as $paMixLevel => $paMixPercent):
                    $paCardMixParts[] = (int) $paMixPercent.'% '.\App\Support\PolicyLevel::label($paMixLevel);
                endforeach;
                $paCardMixText = (!empty($paCardMixParts) ? implode(' · ', $paCardMixParts).' questions' : '');
            @endphp
            <label class="pa-assign-level-card pa-assign-level-card--{{ $paLevelKey }}" for="{{ $paLevelChoiceId }}_{{ $paLevelKey }}">
                <input id="{{ $paLevelChoiceId }}_{{ $paLevelKey }}" type="radio" name="level" value="{{ $paLevelKey }}" class="level"{{ $paLevelKey == \App\Support\PolicyLevel::BEGINNER ? ' checked' : '' }} autocomplete="off">
                <span class="pa-assign-level-card__medal">@include('pages.hr.policy-assessment.partials.level-badge', ['level' => $paLevelKey, 'size' => 'sm', 'title' => null, 'date' => null, 'revoked' => false])</span>
                <span class="pa-assign-level-card__copy">
                    <strong>{{ $paLevelLabel }} exam</strong>
                    <small>{{ $paLevelHelp[$paLevelKey] }}</small>
                    @if($paCardMixText !== '')
                        <span class="pa-assign-level-card__mix" data-level-mix="{{ $paLevelKey }}">
                            <span class="pa-mix-bar" aria-hidden="true">@foreach($paCardMix as $paMixLevel => $paMixPercent)<span class="pa-mix-bar__part pa-mix-bar__part--{{ $paMixLevel }}" style="width: {{ (int) $paMixPercent }}%;"></span>@endforeach</span>
                            <span class="pa-assign-level-card__mix-text">{{ $paCardMixText }}</span>
                        </span>
                    @endif
                    <em>{{ \App\Support\PolicyLevel::badgeName($paLevelKey) }} badge</em>
                </span>
                <span class="pa-assign-level-card__radio" aria-hidden="true"></span>
            </label>
        @endforeach
    </div>
    <div class="acc__input-error error-level"></div>
</fieldset>
