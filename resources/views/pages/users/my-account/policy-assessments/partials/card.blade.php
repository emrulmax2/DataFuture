{{--
    One assigned test on My HR › Policy Assessments. $card comes from
    MyPolicyAssessmentController::card(). A policy held at more than one level
    has one card per level; the level pill tells them apart.

    A test still to be sat states its rules ($card['rules']): questions, pass
    mark, the time limit when there is one, and the mix of question levels as
    counts — taken from PolicyLevel::quota() in the controller.

    Start / Resume / Retake open the test page (the exam wizard: its Briefing
    first, or the questions when an attempt is in progress). Once an attempt
    has been marked the card also links to its Result page.
--}}
<article class="myhr-policy-card myhr-policy-card--{{ $card['status'] }} myhr-policy-card--level-{{ $card['level'] }}" data-policy-card data-level="{{ $card['level'] }}">
    <div class="myhr-policy-cover myhr-policy-cover--tone-{{ $card['tone'] }} {{ $card['thumbnail'] ? 'has-image' : '' }}" aria-hidden="true">
        <span class="myhr-policy-cover__crest">LCC</span>
        <span class="myhr-policy-cover__initials">{{ $card['initials'] }}</span>
        <span class="myhr-policy-cover__rule"></span>
        <span class="myhr-policy-cover__label">Policy</span>
        @if($card['thumbnail'])
            <img src="{{ $card['thumbnail'] }}" alt="" loading="lazy" decoding="async" referrerpolicy="no-referrer">
        @endif
    </div>

    <div class="myhr-policy-card__body">
        <div class="myhr-policy-card__tags">
            <span class="myhr-policy-pill myhr-policy-pill--{{ $card['status'] }}">{{ $card['status_label'] }}</span>
            @include('pages.hr.policy-assessment.partials.level-pill', ['level' => $card['level'], 'text' => null])
        </div>

        <h3 class="myhr-policy-card__title">{{ $card['title'] }}<span class="myhr-policy-sr"> ({{ $card['level_label'] }} exam)</span></h3>
        @if(filled($card['version']))
            <span class="myhr-policy-card__version">Version {{ $card['version'] }}</span>
        @endif

        @if($card['status'] == 'passed')
            <p class="myhr-policy-card__passed">
                <i data-lucide="badge-check"></i>
                <span>Passed on {{ $card['passed_at'] ?? '—' }}@if($card['best_score'] !== null) &middot; {{ $card['best_score'] }}%@endif</span>
            </p>
            @if($card['badge'])
                <p class="myhr-policy-card__award">
                    <span class="myhr-policy-card__award-medal" aria-hidden="true">
                        @include('pages.hr.policy-assessment.partials.level-badge', ['level' => $card['badge']['level'], 'size' => 'sm', 'title' => null, 'date' => null, 'revoked' => false])
                    </span>
                    <span>{{ $card['badge']['badge_name'] }} badge earned</span>
                </p>
            @endif
        @else
            <dl class="myhr-policy-card__meta">
                <div>
                    <dt>Due</dt>
                    <dd class="{{ $card['is_overdue'] ? 'is-overdue' : '' }}">
                        @if($card['due_date'])
                            {{ $card['due_date'] }}@if($card['is_overdue'])<span class="myhr-policy-sr"> (overdue)</span>@endif
                        @else
                            No due date
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Best score</dt>
                    <dd>{{ $card['best_score'] !== null ? $card['best_score'].'%' : '—' }}</dd>
                </div>
                <div>
                    <dt>Attempts</dt>
                    <dd>{{ $card['attempts_count'] }}{{ $card['max_attempts'] !== null ? ' / '.$card['max_attempts'] : '' }}</dd>
                </div>
            </dl>
        @endif

        @if($card['rules'])
            <div class="myhr-policy-card__rules">
                <ul class="myhr-policy-card__facts" aria-label="Test rules">
                    <li>
                        <i data-lucide="list-checks"></i>
                        <span>{{ $card['rules']['question_count'] }} {{ $card['rules']['question_count'] == 1 ? 'question' : 'questions' }}</span>
                    </li>
                    <li>
                        <i data-lucide="target"></i>
                        <span>Pass mark {{ $card['rules']['pass_mark'] }}%</span>
                    </li>
                    @if($card['rules']['time_limit'] !== null)
                        <li class="is-timed">
                            <i data-lucide="timer"></i>
                            <span><span class="myhr-policy-sr">Time limit </span>{{ $card['rules']['time_limit_label'] }}</span>
                        </li>
                    @endif
                </ul>
                @if($card['rules']['pattern_text'] !== '')
                    <p class="myhr-policy-card__mix"><span class="myhr-policy-sr">Question mix: </span>{{ $card['rules']['pattern_text'] }}</p>
                @endif
            </div>
        @endif

        @if($card['clock'])
            <p class="myhr-policy-card__clock">
                <i data-lucide="alarm-clock"></i>
                <span>{{ $card['clock'] }}</span>
            </p>
        @elseif($card['ran_out'])
            <p class="myhr-policy-card__clock is-past">
                <i data-lucide="alarm-clock"></i>
                <span>Your last attempt ran out of time</span>
            </p>
        @endif

        <p class="myhr-policy-card__opened" data-policy-opened>
            <i data-lucide="{{ $card['opened_at'] ? 'book-open-check' : 'book' }}"></i>
            <span>{{ $card['opened_at'] ? 'Policy opened '.$card['opened_at'] : 'Policy not opened yet' }}</span>
        </p>

        {{-- The Result page of the last attempt that was marked: score, pass or fail and the tab activity logged. --}}
        @if($card['result_url'])
            <a href="{{ $card['result_url'] }}" class="myhr-policy-card__result">
                <i data-lucide="bar-chart-3"></i>
                <span>View last result<span class="myhr-policy-sr">: {{ $card['title'] }}, {{ $card['level_label'] }}</span></span>
            </a>
        @endif

        <div class="myhr-policy-card__actions">
            @if($card['has_pdf'])
                <a href="{{ $card['read_url'] }}" target="_blank" rel="noopener" class="myhr-policy-btn myhr-policy-btn--outline" data-policy-read>
                    <i data-lucide="book-open"></i>
                    Read policy<span class="myhr-policy-sr"> (opens in a new tab)</span>
                </a>
            @else
                <span class="myhr-policy-btn myhr-policy-btn--outline is-disabled" aria-disabled="true" title="HR has not linked the document yet">
                    <i data-lucide="book-open"></i>
                    Read policy
                </span>
            @endif

            @if($card['action'])
                <a href="{{ $card['take_url'] }}" class="myhr-policy-btn myhr-policy-btn--primary">
                    <i data-lucide="{{ $card['action']['icon'] }}"></i>
                    {{ $card['action']['label'] }}<span class="myhr-policy-sr">: {{ $card['title'] }}, {{ $card['level_label'] }}</span>
                </a>
            @elseif($card['reason'])
                <span class="myhr-policy-btn myhr-policy-btn--primary is-disabled" aria-disabled="true">
                    <i data-lucide="lock"></i>
                    Take test
                </span>
            @endif
        </div>

        @if($card['reason'])
            <p class="myhr-policy-card__reason">
                <i data-lucide="info"></i>
                <span>{{ $card['reason'] }}</span>
            </p>
        @elseif(!$card['has_pdf'] && $card['status'] != 'passed')
            <p class="myhr-policy-card__reason">
                <i data-lucide="info"></i>
                <span>The document is not online yet — ask HR for a copy.</span>
            </p>
        @endif
    </div>
</article>
