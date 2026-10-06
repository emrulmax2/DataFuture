{{--
    My HR › Policy Assessments: the "My badges" shelf. $shelf comes from
    MyPolicyAssessmentController::badgeShelf(): the signed-in member of staff's
    live badges, hardest level first, newest first within a level.

    The medals here sit next to text that says the same thing, so each one is
    hidden from screen readers (aria-hidden wrapper) rather than announced twice.
    The level-badge partial is always given title/date/revoked explicitly, since
    @include would otherwise pass this page's $title through.
--}}
<section id="myhrPolicyBadges" class="myhr-policy-shelf {{ $shelf['total'] > 0 ? 'has-badges' : 'is-empty' }}" aria-labelledby="myhrPolicyBadgesTitle" data-policy-shelf>
    <div class="myhr-policy-shelf__head">
        <div class="myhr-policy-shelf__intro">
            <h3 id="myhrPolicyBadgesTitle" class="myhr-policy-shelf__label">My badges</h3>
            @if($shelf['total'] > 0)
                <p class="myhr-policy-shelf__figure">
                    <strong>{{ $shelf['total'] }}</strong>
                    <span>{{ $shelf['total'] == 1 ? 'badge' : 'badges' }} earned</span>
                </p>
            @endif
        </div>

        @if($shelf['total'] > 0)
            <ul class="myhr-policy-shelf__tally" aria-label="Badges by level">
                @foreach($shelf['tally'] as $tally)
                    <li class="myhr-policy-shelf__tally-item myhr-policy-shelf__tally-item--{{ $tally['level'] }} {{ $tally['count'] > 0 ? '' : 'is-zero' }}">
                        <span class="myhr-policy-shelf__tally-medal" aria-hidden="true">
                            @include('pages.hr.policy-assessment.partials.level-badge', ['level' => $tally['level'], 'size' => 'sm', 'title' => null, 'date' => null, 'revoked' => false])
                        </span>
                        <strong>{{ $tally['count'] }}</strong>
                        <span>{{ $tally['badge_name'] }}<span class="myhr-policy-sr"> ({{ $tally['level_label'] }})</span></span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if($shelf['total'] > 0)
        <ol id="myhrPolicyShelfGrid" class="myhr-policy-shelf__grid">
            @foreach($shelf['items'] as $index => $item)
                <li class="myhr-policy-award myhr-policy-award--{{ $item['level'] }}" @if($index >= $shelf['visible']) data-shelf-extra @endif>
                    <div class="myhr-policy-award__stage" aria-hidden="true">
                        @include('pages.hr.policy-assessment.partials.level-badge', ['level' => $item['level'], 'size' => 'lg', 'title' => null, 'date' => null, 'revoked' => false])
                    </div>
                    <div class="myhr-policy-award__body">
                        <span class="myhr-policy-award__level">{{ $item['badge_name'] }} &middot; {{ $item['level_label'] }}</span>
                        <h4 class="myhr-policy-award__title">{{ $item['title'] }}</h4>
                        <p class="myhr-policy-award__meta">
                            @if($item['awarded_at'])
                                <span>Awarded <time datetime="{{ $item['awarded_iso'] }}">{{ $item['awarded_at'] }}</time></span>
                            @endif
                            @if($item['score'] !== null)
                                <span>Score {{ $item['score'] }}%</span>
                            @endif
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>

        @if($shelf['total'] > $shelf['visible'])
            {{-- Shown by the page script, which folds away the badges after the first few. Without it every badge stays on show. --}}
            <div class="myhr-policy-shelf__more">
                <button type="button" class="myhr-policy-btn myhr-policy-btn--ghost" data-shelf-toggle aria-controls="myhrPolicyShelfGrid" aria-expanded="false" data-label-more="Show all {{ $shelf['total'] }} badges" data-label-less="Show fewer badges" hidden>
                    <i data-lucide="chevron-down"></i>
                    <span data-shelf-toggle-label>Show all {{ $shelf['total'] }} badges</span>
                </button>
            </div>
        @endif
    @else
        <div class="myhr-policy-shelf__empty">
            <ul class="myhr-policy-shelf__legend" aria-label="Badges you can earn">
                @foreach(array_reverse($shelf['tally']) as $tally)
                    <li class="myhr-policy-shelf__legend-item myhr-policy-shelf__legend-item--{{ $tally['level'] }}">
                        <span aria-hidden="true">
                            @include('pages.hr.policy-assessment.partials.level-badge', ['level' => $tally['level'], 'size' => 'sm', 'title' => null, 'date' => null, 'revoked' => false])
                        </span>
                        <span>{{ $tally['level_label'] }}<span class="myhr-policy-sr">: {{ strtolower($tally['badge_name']) }} badge</span></span>
                    </li>
                @endforeach
            </ul>
            <div class="myhr-policy-shelf__empty-text">
                <p class="myhr-policy-shelf__empty-title">Meet the target in a policy test to earn your first badge</p>
                <p class="myhr-policy-shelf__empty-note">Beginner tests earn a bronze badge, Intermediate tests silver and Expert tests gold.</p>
            </div>
        </div>
    @endif
</section>
