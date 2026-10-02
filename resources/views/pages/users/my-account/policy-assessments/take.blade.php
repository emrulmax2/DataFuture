@extends('../layout/policy-exam')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/policy-assessment-staff.css')
@endsection

{{--
    The countdown, in the exam header. data-seconds is the server's count of the
    time left as this page was built; the script counts down from it and never
    works out the deadline from the browser's own clock. The state is always in
    words as well as colour.
--}}
@section('exam_timer')
    @if(!empty($timer))
        @php
            $timerWords = ['ok' => 'Time left', 'warn' => 'Running low', 'danger' => 'Almost out', 'done' => 'Time is up'];
        @endphp
        <div id="pexamTimer" class="pexam-timer" role="timer" aria-label="Time remaining" data-state="{{ $timer['state'] }}"
            data-seconds="{{ $timer['seconds'] }}"
            data-limit="{{ $timer['limit'] }}"
            data-warn="{{ $timer['warn'] }}"
            data-danger="{{ $timer['danger'] }}"
            data-announce="{{ implode(',', $timer['announce']) }}">
            <i data-lucide="timer"></i>
            <span class="pexam-timer__text">
                <span id="pexamTimerState" class="pexam-timer__state">{{ $timerWords[$timer['state']] ?? $timerWords['ok'] }}</span>
                <span id="pexamClock" class="pexam-timer__clock">{{ $timer['clock'] }}</span>
            </span>
        </div>
    @endif
@endsection

@section('subcontent')
    @php
        $total = (int) $test['total'];
        $attemptText = 'attempt '.$test['attempt_no'].($test['max_attempts'] !== null ? ' of '.$test['max_attempts'] : '');
    @endphp

    {{--
        Steps 2 and 3 of a test: the questions, one per screen, then the review.

        Every drawn question is on this page; the script shows one at a time
        and builds the navigator, the review list and the tallies from them.
        Only the question text and the option text are here. Options are in the
        shuffled order stored when the test was drawn, and each radio's value is
        its letter in that order, not a database id. Nothing on this page knows
        which answer is right, and no question says which level it is.
    --}}
    <div id="pexamTest" class="pexam" data-screen-label="Policy Test"
        data-assignment="{{ $test['assignment_id'] }}"
        data-attempt="{{ $test['attempt_id'] }}"
        data-total="{{ $total }}"
        data-attempt-text="{{ $attemptText }}"
        data-timed="{{ !empty($timer) ? 1 : 0 }}"
        data-exits="{{ $test['tab_exits'] }}"
        data-submit-url="{{ $urls['submit'] }}"
        data-event-url="{{ $urls['event'] }}"
        data-result-url="{{ $urls['result'] }}"
        data-index-url="{{ $urls['index'] }}">

        <noscript>
            <p class="pexam-alert">This test needs JavaScript. Please switch it on in your browser, then reload this page.</p>
        </noscript>

        {{-- Step 2: questions --}}
        <section id="pexamStepTest" class="pexam-test" aria-label="Questions">
            <form id="pexamForm" class="pexam-qcol" novalidate autocomplete="off">
                @foreach($test['questions'] as $question)
                    @php
                        $optionCount = count($question['options']);
                        $lastLetter = ($optionCount > 0 ? $question['options'][$optionCount - 1]['letter'] : 'A');
                    @endphp
                    <article class="pexam-q" data-question id="pexamQ{{ $question['number'] }}" data-number="{{ $question['number'] }}" data-row="{{ $question['row_id'] }}" @if(!$loop->first) hidden @endif>
                        <div class="pexam-qmeta">
                            <span class="pexam-count">Question {{ $question['number'] }} <i>of {{ $total }}</i></span>
                            <button type="button" class="pexam-flag" data-act="flag" aria-pressed="false">
                                <i data-lucide="flag"></i>
                                <span data-flag-label>Flag for review</span>
                            </button>
                        </div>

                        <h1 id="pexamQText{{ $question['number'] }}" class="pexam-qtext" tabindex="-1">{{ $question['text'] }}</h1>

                        <fieldset class="pexam-opts" aria-labelledby="pexamQText{{ $question['number'] }}">
                            <legend class="pexam-sr">Choose one answer</legend>
                            @foreach($question['options'] as $option)
                                <label class="pexam-opt">
                                    <input type="radio" name="answers[{{ $question['row_id'] }}]" value="{{ $option['value'] }}">
                                    <span class="pexam-key" aria-hidden="true">{{ $option['letter'] }}</span>
                                    <span class="pexam-opt__text">{{ $option['text'] }}</span>
                                    <span class="pexam-tick" aria-hidden="true"></span>
                                </label>
                            @endforeach
                        </fieldset>

                        <div class="pexam-qnav">
                            <button type="button" class="pexam-btn" data-act="prev" @if($loop->first) disabled @endif>
                                <i data-lucide="arrow-left"></i>
                                Previous
                            </button>
                            <button type="button" class="pexam-btn pexam-btn--primary" data-act="next">
                                {{ $loop->last ? 'Review answers' : 'Next' }}
                                <i data-lucide="arrow-right"></i>
                            </button>
                        </div>

                        {{-- F flags the question, unless this question has an option F. --}}
                        <p class="pexam-hint">
                            <span><kbd>A</kbd>@if($optionCount > 1)&ndash;<kbd>{{ $lastLetter }}</kbd>@endif answer</span>
                            @if($optionCount < 6)
                                <span><kbd>F</kbd> flag</span>
                            @endif
                            <span><kbd>Enter</kbd> next</span>
                            <span><kbd>&larr;</kbd> <kbd>&rarr;</kbd> move</span>
                        </p>
                    </article>
                @endforeach
            </form>

            <aside class="pexam-rail" aria-label="Question navigator">
                <div class="pexam-rail__head">
                    <h2>Questions</h2>
                    <span id="pexamAnswered">0/{{ $total }} answered</span>
                </div>
                <ol id="pexamGrid" class="pexam-grid"></ol>
                <ul class="pexam-legend" aria-hidden="true">
                    <li><i class="is-ans"></i>Answered</li>
                    <li><i></i>Not answered</li>
                    <li><i class="is-flg"></i>Flagged</li>
                </ul>
                <p id="pexamExits" class="pexam-pill pexam-pill--bad pexam-exits" @if($test['tab_exits'] < 1) hidden @endif>{{ $test['tab_exits'] }} tab {{ $test['tab_exits'] == 1 ? 'exit' : 'exits' }} logged</p>
                <button type="button" class="pexam-btn pexam-btn--block" data-act="review">Review and submit</button>
            </aside>
        </section>

        {{-- Step 3: review --}}
        <section id="pexamStepReview" class="pexam-page" aria-label="Review" hidden>
            <div class="pexam-page__head">
                <p class="pexam-eyebrow">Before you submit</p>
                <h1 id="pexamReviewTitle" tabindex="-1">Check your answers</h1>
            </div>

            <div id="pexamTally" class="pexam-tally"></div>

            <ol id="pexamRows" class="pexam-rows"></ol>

            <div id="pexamSubmitError" class="pexam-alert" role="alert" hidden>
                <i data-lucide="alert-triangle"></i>
                <span id="pexamSubmitErrorText"></span>
            </div>

            <div id="pexamAct" class="pexam-act">
                <button type="button" class="pexam-btn pexam-btn--ghost" data-act="back">
                    <i data-lucide="arrow-left"></i>
                    Back to questions
                </button>
                <button type="button" id="pexamSubmitBtn" class="pexam-btn pexam-btn--primary pexam-btn--lg" data-act="ask">Submit test</button>
            </div>

            <div id="pexamConfirm" class="pexam-confirm" role="alertdialog" aria-labelledby="pexamConfirmTitle" aria-describedby="pexamConfirmText" hidden>
                <p id="pexamConfirmTitle" class="pexam-confirm__title">Submit {{ $attemptText }}?</p>
                <p id="pexamConfirmText">You cannot change your answers afterwards.</p>
                <div class="pexam-confirm__actions">
                    <button type="button" id="pexamConfirmBtn" class="pexam-btn pexam-btn--primary" data-act="submit">Submit now</button>
                    <button type="button" id="pexamCancelBtn" class="pexam-btn" data-act="cancel">Keep reviewing</button>
                </div>
            </div>
        </section>

        {{-- Takes over the page when the clock of a timed test reaches zero. --}}
        @if(!empty($timer))
            <section id="pexamTimeUp" class="pexam-page pexam-timeup" aria-label="Time is up" hidden>
                <span class="pexam-timeup__icon" aria-hidden="true">
                    <i data-lucide="alarm-clock"></i>
                </span>
                <h1 id="pexamTimeUpTitle" tabindex="-1">Time is up</h1>
                <p id="pexamTimeUpText" role="alert">Sending your answers&hellip;</p>
                <div class="pexam-timeup__actions">
                    <button type="button" id="pexamTimeUpRetry" class="pexam-btn pexam-btn--primary" data-act="timeup-retry" hidden>Try again</button>
                    <a id="pexamTimeUpBack" href="{{ $urls['index'] }}" class="pexam-btn" hidden>Back to my policy assessments</a>
                </div>
            </section>
        @endif
    </div>
@endsection

@section('script')
    @vite('resources/js/user-policy-assessment.js')
@endsection
