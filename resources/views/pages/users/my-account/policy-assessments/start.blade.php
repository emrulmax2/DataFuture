@extends('../layout/policy-exam')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/policy-assessment-staff.css')
@endsection

@section('subcontent')
    @php
        /* "the Adverse Occurrence Policy", but never "the The ..." */
        $declarationTitle = (preg_match('/^the\s/i', $policy['title']) ? $policy['title'] : 'the '.$policy['title']);
        $isTimed = ($test['time_limit'] !== null);
        $questionCount = (int) $test['question_count'];
        $attemptsLeft = $test['attempts_left'];
    @endphp

    {{--
        Step 1 of a test: the Briefing. Nothing has been drawn and no clock is
        running — opening or reloading this page costs nothing. Posting the
        form to user.account.policy.begin draws the questions (and, on a timed
        test, starts the clock) and lands on the questions.

        Start stays disabled until the declaration is ticked. The script
        enables it, so a browser without the script cannot start a test it
        could not then sit. The server checks the declaration as well.
    --}}
    <section id="pexamBriefing" class="pexam-brief" data-screen-label="Policy Test Briefing"
        data-assignment="{{ $test['assignment_id'] }}"
        data-level="{{ $test['level'] }}">

        <div class="pexam-brief__main">
            <a href="{{ $urls['index'] }}" class="pexam-back">
                <i data-lucide="arrow-left"></i>
                My policy assessments
            </a>

            <p class="pexam-eyebrow">
                Policy assessment
                @include('pages.hr.policy-assessment.partials.level-pill', ['level' => $test['level'], 'text' => $test['level_label'].' exam'])
            </p>
            <h1 id="pexamTitle" class="pexam-brief__title" tabindex="-1">{{ $policy['title'] }}</h1>
            <p class="pexam-lede">Show that you have read and understood the policy. Score {{ $test['pass_mark'] }}% or more to earn your {{ $test['level_label'] }} badge.</p>

            <ul class="pexam-chips" aria-label="About this test">
                @if(filled($policy['version']))
                    <li>Version {{ $policy['version'] }}</li>
                @endif
                <li>Level: {{ $test['level_label'] }}</li>
                <li>Attempt {{ $test['attempt_no'] }}{{ $test['max_attempts'] !== null ? ' of '.$test['max_attempts'] : '' }}</li>
                @if($policy['due_date'])
                    <li class="{{ $policy['is_overdue'] ? 'is-overdue' : '' }}">Due {{ $policy['due_date'] }}@if($policy['is_overdue']) (overdue)@endif</li>
                @endif
            </ul>

            <div class="pexam-rules">
                <h2>How the test works</h2>
                <ul>
                    <li>One question per screen. Move back and forward freely until you submit.</li>
                    <li>Flag any question you want to come back to. A question you skip is flagged automatically until you answer it.</li>
                    <li>
                        Questions are drawn at random and the answer order is shuffled on every attempt.
                        @if($test['pattern_text'] !== '')
                            This test draws <strong>{{ $test['pattern_text'] }}</strong> {{ $questionCount == 1 ? 'question' : 'questions' }}.
                        @endif
                    </li>
                    @if($isTimed)
                        <li>You have <strong>{{ $test['time_limit_words'] }}</strong>. The clock cannot be paused and keeps running if you close the page. The test submits itself when the timer reaches zero, and unanswered questions count as wrong.</li>
                    @else
                        <li>There is no time limit. Any question left unanswered when you submit counts as wrong.</li>
                    @endif
                    <li><strong>Stay on this tab.</strong> Leaving the test tab or window is logged and shown with your result &mdash; HR can see it.</li>
                    @if($test['max_attempts'] === null)
                        <li>There is no limit on attempts. If you do not pass, read the policy again and retake the test with a new set of questions.</li>
                    @elseif($attemptsLeft === 1)
                        <li>This is your last attempt. If you do not pass, this assessment is locked until HR allows another attempt.</li>
                    @else
                        <li>You have {{ $test['max_attempts'] }} attempts in all. If you use them without passing, this assessment is locked until HR allows another attempt.</li>
                    @endif
                    <li>Afterwards you see your score and whether you passed, not the answers.</li>
                </ul>
            </div>
        </div>

        <div class="pexam-sheet">
            <div class="pexam-sheet__level pexam-level pexam-level--{{ $test['level'] }}">
                <span class="pexam-sheet__medal" aria-hidden="true">
                    @include('pages.hr.policy-assessment.partials.level-badge', ['level' => $test['level'], 'size' => 'md', 'title' => null, 'date' => null, 'revoked' => false])
                </span>
                <div class="pexam-sheet__level-text">
                    <strong>{{ $test['level_label'] }} exam</strong>
                    <span>Pass to earn the {{ $test['level_label'] }} badge ({{ strtolower($test['badge_name']) }}).</span>
                </div>
            </div>

            <dl class="pexam-facts">
                <div>
                    <dt>{{ $questionCount == 1 ? 'question' : 'questions' }}</dt>
                    <dd>{{ $questionCount }}</dd>
                </div>
                <div>
                    @if($isTimed)
                        <dt>time limit</dt>
                        <dd>{{ $test['time_limit_label'] }}</dd>
                    @else
                        <dt>take your time</dt>
                        <dd class="is-words">No time limit</dd>
                    @endif
                </div>
                <div>
                    <dt>to pass ({{ $test['pass_needed'] }} of {{ $questionCount }})</dt>
                    <dd>{{ $test['pass_mark'] }}%</dd>
                </div>
                <div>
                    @if($attemptsLeft === null)
                        <dt>attempts</dt>
                        <dd class="is-words">Unlimited</dd>
                    @else
                        <dt>{{ $attemptsLeft == 1 ? 'attempt left' : 'attempts left' }}</dt>
                        <dd>{{ $attemptsLeft }}</dd>
                    @endif
                </div>
            </dl>

            @if($policy['has_pdf'])
                <a href="{{ $urls['read'] }}" target="_blank" rel="noopener" class="pexam-btn pexam-btn--block">
                    <i data-lucide="book-open"></i>
                    Read policy<span class="pexam-sr"> (opens in a new tab)</span>
                </a>
            @else
                <p class="pexam-fine">The document is not online yet &mdash; ask HR for a copy before you start.</p>
            @endif

            <form id="pexamStartForm" class="pexam-gate" method="POST" action="{{ $urls['begin'] }}">
                @csrf

                @if($errors->has('acknowledge'))
                    <div class="pexam-alert" role="alert">
                        <i data-lucide="alert-triangle"></i>
                        <span>{{ $errors->first('acknowledge') }}</span>
                    </div>
                @endif

                <label class="pexam-check" for="pexamReadGate">
                    <input type="checkbox" id="pexamReadGate" name="acknowledge" value="1" required>
                    <span>I confirm I have read and understood {{ $declarationTitle }}.</span>
                </label>

                <button type="submit" id="pexamStartBtn" class="pexam-btn pexam-btn--primary pexam-btn--lg" disabled>
                    <span class="pexam-start__label">Start test</span>
                    <i data-lucide="arrow-right"></i>
                </button>

                <p class="pexam-fine">
                    @if($isTimed)
                        The timer starts as soon as you press Start.
                    @else
                        Nothing starts until you press Start.
                    @endif
                </p>

                <noscript>
                    <p class="pexam-alert">This test needs JavaScript. Please switch it on in your browser, then reload this page.</p>
                </noscript>
            </form>
        </div>
    </section>
@endsection

@section('script')
    @vite('resources/js/user-policy-assessment.js')
@endsection
