@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
@php
    $pgdTone = function ($pct) {
        return $pct >= 75 ? 'good' : ($pct >= 50 ? 'mid' : 'bad');
    };
    $pgdToneDot = ['good' => '#1B7F5A', 'mid' => '#C9922B', 'bad' => '#B3261E'];

    // Overall figures are accumulated as the module rows are walked, exactly as
    // the previous table did, so the "Overall" line keeps agreeing with them.
    $P = $O = $L = $E = $M = $H = $TOTAL = 0;
    $expectedTotal = 0;
    $moduleRows = [];

    foreach($plans as $pln):
        $attendances = $pln->attendances;

        $attended = 0;
        $attended += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
        $P += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
        $attended += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
        $O += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
        $attended += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
        $L += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
        $attended += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
        $E += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
        $attended += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
        $M += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
        $attended += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);
        $H += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);

        $planTotal = (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;
        $TOTAL += $planTotal;

        $expected = (isset($pln->expected_submission) && $pln->expected_submission > 0 ? $pln->expected_submission : 0);
        $expectedTotal += $expected;

        $moduleRows[] = [
            'id' => $pln->id,
            'module' => (isset($pln->creations->module->name) ? $pln->creations->module->name : 'Unknown'),
            'type' => (isset($pln->class_type) ? strtoupper($pln->class_type) : ''),
            'group' => (isset($pln->group->name) ? $pln->group->name : ''),
            'people' => (isset($pln->people) ? $pln->people : []),
            'rate' => ($attended > 0 && $planTotal > 0 ? $attended / $planTotal * 100 : 0),
            'expected' => $expected,
        ];
    endforeach;

    $overallAttended = $P + $O + $L + $E + $M + $H;
    $overallRate = ($overallAttended > 0 && $TOTAL > 0 ? $overallAttended / $TOTAL * 100 : 0);

    $chours = explode(':', $contractedHour);
    $contractedMinutes = (isset($chours[0]) ? (int) $chours[0] * 60 : 0) + (isset($chours[1]) ? (int) $chours[1] : 0);
    $load = ($contractedMinutes > 0 ? $classMinutes / $contractedMinutes : 0);
    $loadColor = ($load > 0.8 ? '#B3261E' : ($load > 0.5 ? '#C9922B' : '#0E5A61'));
    $loadNote = ($load > 0.8 ? 'at capacity' : ($load > 0.5 ? 'balanced' : 'capacity available'));
    $groupCount = collect($moduleRows)->pluck('group')->filter()->unique()->count();

    $termMeta = collect($termColours)->first() ?? ['name' => ($termDeclaration->name ?? 'Term'), 'dot' => '#0E5A61'];
    $tutorName = (isset($tutor->employee->full_name) ? $tutor->employee->full_name : 'Unknown Employee');
    $tutorRole = trim((isset($tutor->employee->employment->employeeWorkType->name) ? $tutor->employee->employment->employeeWorkType->name : '')
        .(isset($tutor->employee->employment->employeeJobTitle->name) ? ' - '.$tutor->employee->employment->employeeJobTitle->name : ''));

    $address = collect([
        $tutor->employee->address->address_line_1 ?? null,
        $tutor->employee->address->address_line_2 ?? null,
        $tutor->employee->address->city ?? null,
        $tutor->employee->address->state ?? null,
        $tutor->employee->address->post_code ?? null,
        $tutor->employee->address->country ?? null,
    ])->filter()->implode(', ');
@endphp

<main class="pgd-page pgd-page--split">
    <div style="min-width: 0;">
        <div class="pgd-detailhead">
            <div>
                <h1>{{ $tutorName }}</h1>
                <div class="pgd-detailhead__tags">
                    <span class="pgd-detailhead__role">{{ $tutorRole !== '' ? $tutorRole : 'Staff' }}</span>
                    <span class="pgd-detailhead__sum">{{ count($moduleRows) }} {{ count($moduleRows) == 1 ? 'module' : 'modules' }} · {{ $termDeclaration->name ?? '' }}</span>
                </div>
            </div>
            <div class="pgd-detailhead__actions">
                <a href="{{ route('programme.dashboard.tutors', $termDeclaration->id) }}" class="pgd-btn pgd-btn--ghost">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>
                    All tutors
                </a>
            </div>
        </div>

        <div class="pgd-kpis">
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $pgdToneDot[$pgdTone($overallRate)] }};"></span>Attendance</div>
                <div class="pgd-kpi__value"><strong>{{ number_format($overallRate, 1) }}%</strong><span>across modules</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ min(100, round($overallRate)) }}%; background: {{ $pgdToneDot[$pgdTone($overallRate)] }};"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: #B3261E;"></span>Submission</div>
                <div class="pgd-kpi__value"><strong>0.0%</strong><span>of {{ number_format($expectedTotal) }} expected</span></div>
                <div class="pgd-kpi__bar"><span style="width: 2%; background: #B3261E;"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: #B3261E;"></span>Achievement</div>
                <div class="pgd-kpi__value"><strong>0.0%</strong><span>graded work</span></div>
                <div class="pgd-kpi__bar"><span style="width: 2%; background: #B3261E;"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $loadColor }};"></span>Load</div>
                <div class="pgd-kpi__value"><strong>{{ number_format($load, 2) }}</strong><span>of contract</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ min(100, round($load * 100)) }}%; background: {{ $loadColor }};"></span></div>
            </div>
        </div>

        <section class="pgd-table">
            <div class="pgd-table__head pgd-cols-detail">
                <span></span>
                <span>Module name</span>
                <span>Group</span>
                <span class="pgd-t-right pgd-t-nowrap">Attendance</span>
                <span class="pgd-t-right pgd-t-nowrap">Exp. sub.</span>
                <span class="pgd-t-right pgd-t-nowrap">Submission</span>
                <span class="pgd-t-right pgd-t-nowrap">Achievement</span>
                <span></span>
            </div>

            <div class="pgd-table__row pgd-table__row--total pgd-cols-detail">
                <span></span>
                <span>Overall</span>
                <span></span>
                <span class="pgd-t-right" style="color: #0E5A61; font-variant-numeric: tabular-nums;">{{ number_format($overallRate, 2) }}%</span>
                <span class="pgd-t-right" style="font-variant-numeric: tabular-nums;">{{ number_format($expectedTotal) }}</span>
                <span class="pgd-t-right pgd-num--muted" style="font-variant-numeric: tabular-nums;">0.0%</span>
                <span class="pgd-t-right pgd-num--muted" style="font-variant-numeric: tabular-nums;">0.0%</span>
                <span></span>
            </div>

            @forelse($moduleRows as $row)
                <div class="pgd-table__row pgd-cols-detail">
                    {{-- Both faces when the tutor and the personal tutor differ,
                         each captioned on hover. --}}
                    <span class="pgd-people">
                        @forelse($row['people'] as $person)
                            <span class="pgd-people__face" data-pgd-tooltip="{{ $person['role'] }} · {{ $person['name'] }}">
                                <span class="pgd-avatar" style="background: {{ $person['color'] }};">
                                    @if(!empty($person['photo']))
                                        <img src="{{ $person['photo'] }}" alt="{{ $person['name'] }}">
                                    @else
                                        {{ $person['initials'] }}
                                    @endif
                                </span>
                            </span>
                        @empty
                            <span class="pgd-people__face" data-pgd-tooltip="Tutor · {{ $tutorName }}">
                                <span class="pgd-avatar" style="background: {{ \App\Support\Avatar::soft($tutorName) }};">{{ $tutorInitials }}</span>
                            </span>
                        @endforelse
                    </span>
                    <span style="min-width: 0;">
                        <span class="pgd-mod__name">{{ $row['module'] }}</span>
                        <span class="pgd-mod__meta">
                            <span class="pgd-mod__type">{{ $row['type'] }}</span>
                            <span class="pgd-mod__term"><span style="background: {{ $termMeta['dot'] }};"></span>{{ $termMeta['name'] }}</span>
                        </span>
                    </span>
                    <span>@if(!empty($row['group']))<span class="pgd-group">{{ $row['group'] }}</span>@endif</span>
                    <span class="pgd-t-right">
                        <span class="pgd-rate pgd-rate--{{ $pgdTone($row['rate']) }}"><span></span>{{ number_format($row['rate'], 2) }}%</span>
                    </span>
                    <span class="pgd-num pgd-t-right">{{ $row['expected'] }}</span>
                    <span class="pgd-num pgd-num--muted pgd-t-right">0.0%</span>
                    <span class="pgd-num pgd-num--muted pgd-t-right">0.0%</span>
                    <span class="pgd-t-center">
                        <a href="{{ route('tutor-dashboard.plan.module.show', $row['id']) }}" class="pgd-eye" data-pgd-tooltip="Open module details">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5Z"></path><circle cx="12" cy="12" r="1.7"></circle></svg>
                        </a>
                    </span>
                </div>
            @empty
                <div class="pgd-table__empty">No modules for this tutor in the selected term.</div>
            @endforelse
        </section>
    </div>

    <aside style="position: sticky; top: 126px; display: flex; flex-direction: column; gap: 14px;">
        <section class="pgd-profilecard">
            <div class="pgd-profilecard__top">
                <span class="pgd-profilecard__avatar" style="background: {{ \App\Support\Avatar::soft($tutorName) }};">
                    @if(!\App\Support\Avatar::isGenerated($tutor->employee->photo_url ?? null))
                        <img src="{{ $tutor->employee->photo_url }}" alt="{{ $tutorName }}">
                    @else
                        {{ $tutorInitials }}
                    @endif
                </span>
                <div class="pgd-profilecard__name">{{ $tutorName }}</div>
                <div class="pgd-profilecard__role">{{ $tutorRole !== '' ? $tutorRole : 'Staff' }}</div>
            </div>
            <div class="pgd-profilecard__body">
                @if(!empty($address))
                    <div class="pgd-contact">
                        <span class="pgd-contact__icon">⌂</span>
                        <span>
                            <span class="pgd-contact__label">Address</span>
                            <span class="pgd-contact__value">{{ $address }}</span>
                        </span>
                    </div>
                @endif
                <div class="pgd-contact">
                    <span class="pgd-contact__icon">@</span>
                    <span>
                        <span class="pgd-contact__label">Personal email</span>
                        <span class="pgd-contact__value">{{ $tutor->employee->email ?? '—' }}</span>
                    </span>
                </div>
                <div class="pgd-contact">
                    <span class="pgd-contact__icon">@</span>
                    <span>
                        <span class="pgd-contact__label">Work email</span>
                        <span class="pgd-contact__value">{{ $tutor->email ?? '—' }}</span>
                    </span>
                </div>
                <div class="pgd-contact">
                    <span class="pgd-contact__icon">☎</span>
                    <span>
                        <span class="pgd-contact__label">Mobile</span>
                        <span class="pgd-contact__value">{{ $tutor->employee->mobile ?? '—' }}</span>
                    </span>
                </div>
                @if(!empty($tutor->employee->employment->office_telephone))
                    <div class="pgd-contact">
                        <span class="pgd-contact__icon">☏</span>
                        <span>
                            <span class="pgd-contact__label">Office telephone</span>
                            <span class="pgd-contact__value">{{ $tutor->employee->employment->office_telephone }}</span>
                        </span>
                    </div>
                @endif
                @if(!empty($tutor->employee->id))
                    <div class="pgd-contact">
                        <span class="pgd-contact__icon">#</span>
                        <span>
                            <span class="pgd-contact__label">Staff reference</span>
                            <span class="pgd-contact__value">{{ $tutor->employee->id }}</span>
                        </span>
                    </div>
                @endif
            </div>
        </section>


        <section class="pgd-workload">
            <div class="pgd-workload__head">
                <h2>Workload</h2>
                <span>{{ $loadNote }}</span>
            </div>
            <div class="pgd-workload__meter">
                <div><div style="width: {{ min(100, round($load * 100)) }}%; background: {{ $loadColor }};"></div></div>
                <strong>{{ number_format($load, 2) }}</strong>
            </div>
            <div class="pgd-workload__row"><span>Contracted hours</span><strong>{{ $contractedHour }}</strong></div>
            <div class="pgd-workload__row"><span>Timetabled class hours</span><strong>{{ $classHours }}</strong></div>
            <div class="pgd-workload__row"><span>Modules taught</span><strong>{{ count($moduleRows) }}</strong></div>
            <div class="pgd-workload__row"><span>Groups</span><strong>{{ $groupCount }}</strong></div>
            <div class="pgd-workload__row"><span>Expected submissions</span><strong>{{ number_format($expectedTotal) }}</strong></div>
            <div class="pgd-workload__row"><span>Term</span><strong>{{ $termDeclaration->name ?? '—' }}</strong></div>
        </section>
    </aside>
</main>
@endsection
