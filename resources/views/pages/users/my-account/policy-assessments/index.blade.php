@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('styles')
    @vite('resources/css/policy-assessment-staff.css')
@endsection

@section('body_class', 'my-account-policy-body')

@section('subcontent')
    @include('pages.users.my-account.show-info')

    <section id="myhrPolicyIndex" class="myhr-policy" data-screen-label="Policy Assessments">
        <header class="myhr-policy__header">
            <span class="myhr-policy__header-icon">
                <i data-lucide="clipboard-check"></i>
            </span>
            <div class="myhr-policy__header-text">
                <h2>Policy Assessments</h2>
                <p>Read each college policy, confirm you have understood it, then meet the target score in a short test.</p>
            </div>
        </header>

        @if(session('policyMessage'))
            <div class="myhr-policy-notice" role="status">
                <i data-lucide="info"></i>
                <div>{{ session('policyMessage') }}</div>
            </div>
        @endif

        {{-- Badges outlive the assignment that earned them, so the shelf can show with nothing left to do. --}}
        @if(!empty($sections) || $shelf['total'] > 0)
            @include('pages.users.my-account.policy-assessments.partials.badges', ['shelf' => $shelf])
        @endif

        @if(empty($sections))
            <div class="myhr-policy-empty">
                <span class="myhr-policy-empty__icon">
                    <i data-lucide="clipboard-check"></i>
                </span>
                @if($shelf['total'] > 0)
                    <h3>No policy tests to do right now</h3>
                    <p>When HR asks you to take another policy test, it will appear here.</p>
                @else
                    <h3>No policy assessments yet</h3>
                    <p>When HR asks you to read a college policy and take its test, it will appear here.</p>
                @endif
            </div>
        @else
            <div class="myhr-policy-overview">
                <div class="myhr-policy-summary">
                    <span class="myhr-policy-summary__label">Your progress</span>
                    <p class="myhr-policy-summary__figure">
                        <strong>{{ $summary['passed'] }}</strong>
                        <span>of {{ $summary['assigned'] }} {{ $summary['assigned'] == 1 ? 'test' : 'tests' }} with the target met</span>
                    </p>
                    <div class="myhr-policy-progress" role="progressbar" aria-label="Tests with the target met" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $summary['percent'] }}">
                        <span style="width: {{ $summary['percent'] }}%;"></span>
                    </div>
                    <ul class="myhr-policy-summary__stats">
                        <li>
                            <strong>{{ $summary['to_do'] }}</strong>
                            <span>To complete</span>
                        </li>
                        <li class="{{ $summary['in_progress'] > 0 ? 'is-info' : '' }}">
                            <strong>{{ $summary['in_progress'] }}</strong>
                            <span>In progress</span>
                        </li>
                        <li class="{{ $summary['overdue'] > 0 ? 'is-alert' : '' }}">
                            <strong>{{ $summary['overdue'] }}</strong>
                            <span>Overdue</span>
                        </li>
                    </ul>
                </div>

                <div class="myhr-policy-howto">
                    <span class="myhr-policy-summary__label">How it works</span>
                    <ol class="myhr-policy-steps">
                        <li><span>1</span>Read the policy</li>
                        <li><span>2</span>Confirm you have understood it</li>
                        <li><span>3</span>Meet the target score in the short test to earn a badge</li>
                    </ol>
                    <p class="myhr-policy-howto__note">
                        <i data-lucide="eye-off"></i>
                        After a test you see your score and whether you met the target &mdash; not the answers. If you do not meet the target, read the policy again and retake the test.
                    </p>
                    @if($summary['timed'] > 0)
                        <p class="myhr-policy-howto__note">
                            <i data-lucide="timer"></i>
                            Some tests are timed. Opening one shows its rules first: the clock starts only when you click Start, and cannot be paused.
                        </p>
                    @endif
                </div>
            </div>

            <div class="myhr-policy-sections">
                @foreach($sections as $section)
                    <details class="myhr-policy-section" @if($section['open']) open @endif>
                        <summary class="myhr-policy-section__head">
                            <span class="myhr-policy-section__num">{{ $section['number'] }}</span>
                            <span class="myhr-policy-section__title">{{ $section['name'] }}</span>
                            <span class="myhr-policy-section__count {{ $section['passed'] == $section['total'] ? 'is-done' : '' }}">
                                @if($section['passed'] == $section['total'])
                                    <i data-lucide="check"></i>
                                @endif
                                {{ $section['passed'] }} of {{ $section['total'] }} target met
                            </span>
                            <i data-lucide="chevron-down" class="myhr-policy-section__chevron"></i>
                        </summary>

                        <div class="myhr-policy-grid">
                            @foreach($section['cards'] as $card)
                                @include('pages.users.my-account.policy-assessments.partials.card', ['card' => $card])
                            @endforeach
                        </div>
                    </details>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@section('script')
    @vite('resources/js/user-policy-assessment.js')
@endsection
