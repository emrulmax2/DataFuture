@extends('../layout/policy-exam')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('styles')
    @vite('resources/css/policy-assessment-staff.css')
@endsection

@section('subcontent')
    @php
        $passed = (bool) $result['passed'];
        $total = (int) $result['total_questions'];
        $attemptText = 'Attempt '.$result['attempt_no'].($result['max_attempts'] !== null ? ' of '.$result['max_attempts'] : '');
        /* The score ring: an arc of a circle of radius 52 (circumference 326.73). */
        $ringLength = 326.73;
        $ringOffset = number_format($ringLength * (1 - $result['score_ring'] / 100), 2, '.', '');
        $attemptsLeft = $result['attempts_left'];
        $exits = (int) $activity['exits'];
    @endphp

    {{--
        Step 4 of a test: the Result. Everything here is read from what is
        stored for the attempt, so the page is the same after a reload.

        Deliberately not here: which answers were right or wrong, the correct
        answers, the questions themselves. Questions come up again on retakes
        and at other levels, so staff see their score and pass or fail only.
    --}}
    <section id="pexamResult" class="pexam-page pexam-result" data-screen-label="Policy Test Result"
        data-attempt="{{ $result['attempt_id'] }}"
        data-outcome="{{ $passed ? 'passed' : 'failed' }}"
        data-next="{{ $result['next'] }}">

        <div class="pexam-verdict pexam-verdict--{{ $passed ? 'pass' : 'fail' }}">
            <div class="pexam-ring pexam-ring--{{ $passed ? 'pass' : 'fail' }}">
                <svg viewBox="0 0 120 120" aria-hidden="true" focusable="false">
                    <circle class="pexam-ring__track" cx="60" cy="60" r="52"/>
                    {{-- no arc at all for a score of nought (a rounded line end would still leave a dot) --}}
                    @if($result['score_ring'] > 0)
                        <circle class="pexam-ring__value" cx="60" cy="60" r="52" stroke-dasharray="{{ $ringLength }}" stroke-dashoffset="{{ $ringOffset }}" transform="rotate(-90 60 60)"/>
                    @endif
                </svg>
                <div class="pexam-ring__n">
                    <span><span class="pexam-sr">Your score: </span>{{ $result['score_text'] }}<small>%</small></span>
                </div>
            </div>

            <div class="pexam-verdict__text">
                <p class="pexam-eyebrow">
                    {{ $attemptText }}
                    <span class="pexam-pill {{ $passed ? 'pexam-pill--ok' : 'pexam-pill--warn' }}">{{ $passed ? 'Target met' : 'Target not met' }}</span>
                </p>
                <h1 id="pexamTitle" tabindex="-1">{{ $passed ? 'You met the target for the '.$policy['title'].' test' : 'You did not reach the target score' }}</h1>
                <p class="pexam-verdict__policy">
                    @if(!$passed)
                        <span>{{ $policy['title'] }}</span>
                    @endif
                    @include('pages.hr.policy-assessment.partials.level-pill', ['level' => $result['level'], 'text' => $result['level_label'].' exam'])
                </p>
                <p class="pexam-line">
                    {{ $result['correct_count'] }} of {{ $total }} correct
                    &middot; target score {{ $result['pass_mark'] }}% ({{ $result['pass_needed'] }} of {{ $total }})
                    @if($result['timed_out'])
                        &middot; time ran out
                    @elseif($result['duration'] !== null)
                        &middot; finished in {{ $result['duration'] }}
                    @endif
                </p>
                @if($result['timed_out'])
                    <p class="pexam-note">
                        <i data-lucide="alarm-clock"></i>
                        <span>Time ran out &mdash; {{ $result['answered_count'] }} of {{ $total }} {{ $total == 1 ? 'question' : 'questions' }} answered; unanswered questions are marked wrong.</span>
                    </p>
                @elseif($result['unanswered'] > 0)
                    <p class="pexam-note">
                        <i data-lucide="info"></i>
                        <span>You submitted with {{ $result['unanswered'] }} {{ $result['unanswered'] == 1 ? 'question' : 'questions' }} unanswered; {{ $result['unanswered'] == 1 ? 'it is' : 'they are' }} marked wrong.</span>
                    </p>
                @endif
            </div>
        </div>

        <div class="pexam-duo">
            @if($result['next'] == 'passed' && $result['badge'])
                <div class="pexam-panel pexam-panel--badge pexam-level pexam-level--{{ $result['badge']['level'] }}">
                    <h2>Badge earned</h2>
                    <div class="pexam-badge">
                        <span class="pexam-badge__medal" aria-hidden="true">
                            @include('pages.hr.policy-assessment.partials.level-badge', ['level' => $result['badge']['level'], 'size' => 'lg', 'title' => null, 'date' => null, 'revoked' => false])
                        </span>
                        <div class="pexam-badge__text">
                            <strong>{{ $result['badge']['policy_title'] }} &middot; {{ $result['badge']['level_label'] }}</strong>
                            <span>{{ $result['badge']['badge_name'].' badge'.(filled($result['badge']['awarded_at']) ? ', awarded '.$result['badge']['awarded_at'] : '') }}. It is on your badge shelf in My HR.</span>
                            <a href="{{ $urls['badges'] }}">See my badges</a>
                        </div>
                    </div>
                </div>
            @elseif($result['next'] == 'passed')
                <div class="pexam-panel">
                    <h2>Test complete</h2>
                    <p class="pexam-sub">You met the target for this test. There is nothing more to do.</p>
                </div>
            @elseif($result['next'] == 'passed_since')
                <div class="pexam-panel">
                    <h2>Target met since</h2>
                    <p class="pexam-sub">You have met the target for this test in a later attempt. There is nothing more to do.</p>
                </div>
            @elseif($result['next'] == 'resume')
                <div class="pexam-panel">
                    <h2>Test in progress</h2>
                    <p class="pexam-sub">You have another attempt at this test in progress. Go back to it to finish.</p>
                </div>
            @elseif($result['next'] == 'retake')
                <div class="pexam-panel">
                    <h2>Next step</h2>
                    <p class="pexam-sub">
                        Read the policy again, then retake the test. You will get a new set of questions.
                        @if($attemptsLeft === null)
                            There is no limit on attempts.
                        @elseif($attemptsLeft == 1)
                            You have 1 attempt left before this assessment is locked.
                        @else
                            You have {{ $attemptsLeft }} attempts left before this assessment is locked.
                        @endif
                    </p>
                </div>
            @elseif($result['next'] == 'locked')
                <div class="pexam-panel pexam-panel--alert">
                    <h2>Referred to HR</h2>
                    <p>{{ $result['max_attempts'] == 1 ? 'You have used your only attempt.' : 'You have used all '.$result['max_attempts'].' attempts.' }} This assessment is locked until HR allows another attempt.</p>
                </div>
            @else
                <div class="pexam-panel">
                    <h2>Not available to retake</h2>
                    <p class="pexam-sub">This test cannot be retaken at the moment. Please contact HR.</p>
                </div>
            @endif

            <div class="pexam-panel" id="pexamActivity">
                @if($exits < 1 && empty($activity['events']))
                    <h2>Tab activity <span class="pexam-pill pexam-pill--ok">No exits</span></h2>
                    <p class="pexam-sub">The test tab stayed open and in focus for the whole attempt.</p>
                @else
                    <h2>Tab activity <span class="pexam-pill pexam-pill--bad">{{ $exits }} tab {{ $exits == 1 ? 'exit' : 'exits' }} &middot; {{ $activity['away'] }} away</span></h2>
                    <ol class="pexam-leaves">
                        @foreach($activity['events'] as $event)
                            <li>
                                <time>{{ $event['at'] }}</time>
                                <span>{{ $event['where'] }} &middot; {{ $event['what'] }}</span>
                                <b>{{ $event['away'] !== null ? $event['away'] : 'still away' }}</b>
                            </li>
                        @endforeach
                    </ol>
                    <p class="pexam-fine pexam-fine--left">Each time the test tab or window was left is logged. HR sees the same record with your result.</p>
                @endif
            </div>
        </div>

        <div class="pexam-act pexam-act--result">
            <a href="{{ $urls['index'] }}" class="pexam-btn {{ in_array($result['next'], ['retake', 'resume'], true) ? '' : 'pexam-btn--primary' }}">
                <i data-lucide="arrow-left"></i>
                Back to my policies
            </a>
            @if($policy['has_pdf'])
                <a href="{{ $urls['read'] }}" target="_blank" rel="noopener" class="pexam-btn">
                    <i data-lucide="book-open"></i>
                    Read policy again<span class="pexam-sr"> (opens in a new tab)</span>
                </a>
            @endif
            @if($result['next'] == 'retake')
                <a href="{{ $urls['take'] }}" class="pexam-btn pexam-btn--primary">
                    <i data-lucide="rotate-ccw"></i>
                    Retake test
                </a>
            @elseif($result['next'] == 'resume')
                <a href="{{ $urls['take'] }}" class="pexam-btn pexam-btn--primary">
                    <i data-lucide="play-circle"></i>
                    Resume test
                </a>
            @endif
        </div>
    </section>
@endsection

@section('script')
    @vite('resources/js/user-policy-assessment.js')
@endsection
