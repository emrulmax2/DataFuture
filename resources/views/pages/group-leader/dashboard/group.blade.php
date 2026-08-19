@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('body_class', 'gl-dashboard-body')

@section('styles')
    @vite('resources/css/group-leader-dashboard.css')
@endsection

@section('subcontent')
    @php
        use App\Support\GroupLeaderPresenter as GL;

        $glTabs = [
            'risk' => 'At risk',
            'subs' => 'Submissions outstanding',
            'uncontacted' => 'Uncontacted absences',
            'all' => 'All students',
        ];
        // The day tab's badge is what a leader has to chase before the day ends.
        $glDayFlags = $day['counts']['late'] + $day['counts']['feedMissing'];
    @endphp

    {{-- Level 2: one group. `data-gl-group` / `data-gl-term` scope every ajax
         call on the page, and the server re-authorises both on each one. --}}
    <div class="gl-root" id="glGroup" data-gl-group="{{ $group->id }}" data-gl-term="{{ $termId }}">
        <div class="gl-shell">
            <div class="gl-context">
                <a class="gl-back" href="{{ route('gl.dashboard', ['term' => $termId]) }}">‹ My groups</a>
                <span class="gl-context__rule"></span>

                <div>
                    <div class="gl-context__label">Group</div>
                    <div class="gl-context__value gl-mono">{{ $group->name }}</div>
                </div>
                <div>
                    <div class="gl-context__label">Course</div>
                    <div class="gl-context__value">{{ $group->course->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="gl-context__label">Term</div>
                    <div class="gl-context__value">
                        {{ $term->name ?? '—' }}{{ ($term->termType->name ?? '') ? ' · '.$term->termType->name : '' }}
                    </div>
                </div>

                @if($progress['week'])
                    <div class="gl-context__progress">
                        <div class="gl-context__label">Term progress</div>
                        <div class="gl-context__value">Week {{ $progress['week'] }} of {{ $progress['total'] }}</div>
                        <div class="gl-context__bar"><span style="width: {{ $progress['percent'] }}%;"></span></div>
                    </div>
                @endif
            </div>
        </div>

        <div class="gl-tabs">
            <div class="gl-tabs__inner">
                <button type="button" class="gl-tab is-active" data-gl-view="overview">Overview</button>
                <button type="button" class="gl-tab" data-gl-view="today">
                    Today's classes
                    @if($glDayFlags > 0)<span class="gl-tab__badge">{{ $glDayFlags }}</span>@endif
                </button>
            </div>
        </div>

        {{-- ------------------------------------------------------ overview --}}
        <div class="gl-wrap" data-gl-panel="overview">
            @php
                $glAttTone = GL::tone($kpis['attendance']);
                $glSubTone = GL::tone($kpis['submission'], 90, 80);
                $glCompTone = GL::tone($kpis['completion'], 90, 80);
            @endphp

            <div class="gl-kpis">
                <div class="gl-kpi">
                    <div class="gl-kpi__head">
                        <span class="gl-kpi__label">Attendance rate</span>
                        <span class="gl-kpi__flag is-{{ $glAttTone }}"><span class="gl-dot is-{{ $glAttTone }}"></span>{{ GL::flag($glAttTone) }}</span>
                    </div>
                    <div class="gl-kpi__value">
                        <span class="gl-kpi__number">{{ $kpis['attendance'] ?? '—' }}</span>
                        @if($kpis['attendance'] !== null)<span class="gl-kpi__unit">%</span>@endif
                        @if($kpis['attendanceTrend'] !== null)
                            <span class="gl-kpi__trend {{ $kpis['attendanceTrend'] >= 0 ? 'is-up' : 'is-down' }}">
                                {{ $kpis['attendanceTrend'] >= 0 ? '▲' : '▼' }} {{ abs($kpis['attendanceTrend']) }}pt
                            </span>
                        @endif
                    </div>
                    <div class="gl-kpi__track"><span class="gl-bar is-{{ $glAttTone }}" style="width: {{ min(100, max(0, (int) $kpis['attendance'])) }}%;"></span></div>
                    <div class="gl-kpi__foot">
                        <span>Target 85%</span>
                        <span>{{ $kpis['belowRisk'] }} below 75%</span>
                    </div>
                </div>

                <div class="gl-kpi">
                    <div class="gl-kpi__head">
                        <span class="gl-kpi__label">Submission rate</span>
                        <span class="gl-kpi__flag is-{{ $glSubTone }}"><span class="gl-dot is-{{ $glSubTone }}"></span>{{ GL::flag($glSubTone) }}</span>
                    </div>
                    <div class="gl-kpi__value">
                        <span class="gl-kpi__number">{{ $kpis['submission'] ?? '—' }}</span>
                        @if($kpis['submission'] !== null)<span class="gl-kpi__unit">%</span>@endif
                    </div>
                    <div class="gl-kpi__track"><span class="gl-bar is-{{ $glSubTone }}" style="width: {{ min(100, max(0, (int) $kpis['submission'])) }}%;"></span></div>
                    <div class="gl-kpi__foot">
                        <span>Target 90%</span>
                        <span>{{ $kpis['submission'] === null ? 'No assessments published' : $kpis['submissionOutstanding'].' outstanding' }}</span>
                    </div>
                </div>

                <div class="gl-kpi">
                    <div class="gl-kpi__head">
                        <span class="gl-kpi__label">Completion (on track)</span>
                        <span class="gl-kpi__flag is-{{ $glCompTone }}"><span class="gl-dot is-{{ $glCompTone }}"></span>{{ GL::flag($glCompTone) }}</span>
                    </div>
                    <div class="gl-kpi__value">
                        <span class="gl-kpi__number">{{ $kpis['completion'] ?? '—' }}</span>
                        @if($kpis['completion'] !== null)<span class="gl-kpi__unit">%</span>@endif
                    </div>
                    <div class="gl-kpi__track"><span class="gl-bar is-{{ $glCompTone }}" style="width: {{ min(100, max(0, (int) $kpis['completion'])) }}%;"></span></div>
                    <div class="gl-kpi__foot">
                        <span>Target 90%</span>
                        <span>att ≥80% &amp; subs ≥75%</span>
                    </div>
                </div>
            </div>

            <div class="gl-grid" style="margin-top:24px;">
                <div class="gl-card">
                    <div class="gl-card__head">
                        <div class="gl-section-head">
                            <h2 class="gl-card__title">Students needing attention</h2>
                            <span class="gl-card__hint">Click a student to contact &amp; log a reason</span>
                        </div>
                        <div class="gl-filters">
                            @foreach($glTabs as $key => $label)
                                <button type="button" class="gl-filter {{ $loop->first ? 'is-active' : '' }}" data-gl-tab="{{ $key }}">
                                    {{ $label }} <span class="gl-filter__count" data-gl-count="{{ $key }}">{{ count($lists[$key]) }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="gl-rows gl-async" data-gl-rows>
                        @include('pages.group-leader.dashboard.partials.worklist-rows', ['rows' => $lists['risk']])
                    </div>
                </div>

                <div class="gl-stack">
                    <div class="gl-card">
                        <div class="gl-card__body">
                            <h2 class="gl-card__title" style="margin-bottom:12px;">Needs action now</h2>
                            <div class="gl-stack" style="gap:8px;">
                                @forelse($alerts as $alert)
                                    <div class="gl-alert is-{{ $alert['tone'] }}">
                                        <div class="gl-alert__title">{{ $alert['title'] }}</div>
                                        <div class="gl-alert__detail">{{ $alert['detail'] }}</div>
                                    </div>
                                @empty
                                    <div class="gl-card__hint">Nothing urgent — this group is on track today.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="gl-card">
                        <div class="gl-card__body">
                            <h2 class="gl-card__title" style="margin-bottom:8px;">Group at a glance</h2>
                            <div class="gl-stat"><span class="gl-stat__label">Students in group</span><span class="gl-stat__value">{{ $glance['students'] }}</span></div>
                            <div class="gl-stat"><span class="gl-stat__label">Contacted this week</span><span class="gl-stat__value">{{ $glance['contacted'] }}</span></div>
                            <div class="gl-stat"><span class="gl-stat__label">Active modules</span><span class="gl-stat__value">{{ $glance['modules'] }}</span></div>
                            <div class="gl-stat"><span class="gl-stat__label">Personal tutors</span><span class="gl-stat__value">{{ $glance['personalTutors'] }}</span></div>
                            <div class="gl-stat">
                                <span class="gl-stat__label">Attendance below 60%</span>
                                <span class="gl-stat__value {{ $glance['below60'] > 0 ? 'is-danger' : '' }}">{{ $glance['below60'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gl-card" style="margin-top:24px;">
                <div class="gl-card__head"><h2 class="gl-card__title">Module status</h2></div>
                @if(!empty($modules))
                    <div class="gl-scroll">
                        <table class="gl-table">
                            <thead>
                                <tr>
                                    <th>Module</th>
                                    <th>Tutor</th>
                                    <th>Tutorial tutor</th>
                                    <th>Sessions</th>
                                    <th>Attendance</th>
                                    <th>Submission</th>
                                    <th>Plan / Attendance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($modules as $module)
                                    @php $tone = GL::tone($module['attendance']); @endphp
                                    <tr>
                                        <td>
                                            <div class="gl-table__title">
                                                @if($module['type'])
                                                    <span class="gl-chip {{ GL::typeClass($module['type']) }}">{{ $module['type'] }}</span>
                                                @endif
                                                {{ $module['module'] ?: '—' }}
                                            </div>
                                            <div class="gl-table__code gl-mono">{{ $module['code'] }}</div>
                                        </td>
                                        <td>{{ $module['tutor'] ?: '—' }}</td>
                                        <td style="color:#64748b;">{{ $module['tutorialTutor'] ?: '—' }}</td>
                                        <td>
                                            <span style="font-weight:500;color:#334155;">{{ $module['delivered'] }}</span><span style="color:#94a3b8;">/{{ $module['planned'] }}</span>
                                        </td>
                                        <td>
                                            <div style="display:flex;align-items:center;gap:8px;">
                                                <span class="gl-dot is-{{ $tone }}"></span>
                                                <span style="font-weight:600;" class="is-{{ $tone }}">{{ $module['attendance'] === null ? '—' : $module['attendance'].'%' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($module['submissionDue'] && $module['submissionPct'] !== null)
                                                <span class="{{ $module['submissionPct'] < 75 ? 'is-red' : '' }}" style="font-weight:{{ $module['submissionPct'] < 75 ? '600' : '400' }};">
                                                    {{ $module['submissionPct'] }}%
                                                </span>
                                            @else
                                                <span style="color:#94a3b8;">Not due</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="gl-stackchips">
                                                <span class="gl-feed {{ $module['planCreated'] ? 'is-ok' : 'is-bad' }}" style="margin-top:0;">
                                                    <span class="gl-dot {{ $module['planCreated'] ? 'is-green' : 'is-amber' }}"></span>
                                                    {{ $module['planCreated'] ? 'Plan set' : 'No plan' }}
                                                </span>
                                                <span class="gl-feed {{ $module['attendanceGaps'] === 0 ? 'is-ok' : 'is-bad' }}" style="margin-top:0;">
                                                    <span class="gl-dot {{ $module['attendanceGaps'] === 0 ? 'is-green' : 'is-amber' }}"></span>
                                                    {{ $module['attendanceGaps'] === 0 ? 'Attendance taken' : $module['attendanceGaps'].' not taken' }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="gl-empty">No class plans have been created for this group yet.</div>
                @endif
            </div>

            <div class="gl-card" style="margin-top:24px;">
                <div class="gl-card__head"><h2 class="gl-card__title">Tutor &amp; personal tutor status</h2></div>
                @if(!empty($staff))
                    <div class="gl-staff">
                        @foreach($staff as $person)
                            @php
                                $tone = ['on-track' => 'green', 'attention' => 'amber', 'behind' => 'red'][$person['status']];
                                $label = ['on-track' => 'On track', 'attention' => 'Needs attention', 'behind' => 'Behind'][$person['status']];
                            @endphp
                            <div class="gl-staff__item">
                                <span class="gl-avatar is-lg">{{ GL::initials($person['name']) }}</span>
                                <div style="min-width:0;">
                                    <div class="gl-staff__name">
                                        {{ $person['name'] }}
                                        <span class="gl-pill is-{{ $tone }}"><span class="gl-dot is-{{ $tone }}"></span>{{ $label }}</span>
                                    </div>
                                    <div class="gl-staff__meta">
                                        Tutor / Personal Tutor · {{ $person['modules'] }} {{ Str::plural('module', $person['modules']) }} · {{ $person['tutees'] }} personal tutees
                                    </div>
                                    <div class="gl-staff__detail">{{ $person['detail'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="gl-empty">No tutors are assigned to this group's plans yet.</div>
                @endif
            </div>

            <div class="gl-foot">Group Leader dashboard for {{ $group->name }}</div>
        </div>

        {{-- --------------------------------------------------------- today --}}
        <div class="gl-wrap" data-gl-panel="today" hidden>
            <div class="gl-section-head" style="margin-bottom:16px;">
                <span class="gl-card__hint">Scheduled against actual — what has run, what has not, and what has no register.</span>
                <label class="gl-datefield">
                    <span>📅</span>
                    <input type="text" id="glDayDate" value="{{ $today }}" autocomplete="off">
                </label>
            </div>

            <div data-gl-day class="gl-async">
                @include('pages.group-leader.dashboard.partials.day')
            </div>

            <div class="gl-foot">Live day view · scoped to {{ $group->name }}</div>
        </div>

        {{-- Filled by `gl.dashboard.student`; kept empty until first opened. --}}
        <div class="gl-drawer" data-gl-drawer>
            <div class="gl-drawer__panel" data-gl-drawer-panel></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/group-leader-dashboard.js')
@endsection
