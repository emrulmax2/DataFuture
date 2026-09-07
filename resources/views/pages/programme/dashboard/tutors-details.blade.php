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

        /* Expected comes from the rate itself rather than being counted again,
           so the "Exp. sub." column is literally the denominator of the two
           percentages beside it. */
        $submission = ($submissionByPlan[$pln->id] ?? ['counted' => 0, 'expected' => 0, 'rate' => null]);
        $pass = ($passByPlan[$pln->id] ?? ['counted' => 0, 'expected' => 0, 'rate' => null]);

        $expected = $submission['expected'];
        $expectedTotal += $expected;

        $moduleRows[] = [
            'submission' => $submission,
            'pass' => $pass,
            'id' => $pln->id,
            'module' => (isset($pln->creations->module->name) ? $pln->creations->module->name : 'Unknown'),
            'type' => (isset($pln->class_type) ? strtoupper($pln->class_type) : ''),
            'group' => (isset($pln->group->name) ? $pln->group->name : ''),
            'people' => (isset($pln->people) ? $pln->people : []),
            'rate' => ($attended > 0 && $planTotal > 0 ? $attended / $planTotal * 100 : 0),
            'expected' => $expected,
            /* Kept beside the rate so a section total can be summed from the
               marks themselves. Averaging the per-module percentages would let
               a two-student class weigh the same as a forty-student one. */
            'attended' => $attended,
            'total' => $planTotal,
        ];
    endforeach;

    $overallAttended = $P + $O + $L + $E + $M + $H;
    $overallRate = ($overallAttended > 0 && $TOTAL > 0 ? $overallAttended / $TOTAL * 100 : 0);

    /* One accordion per class type, exactly as the personal-tutor page does.
       A Theory rate and a Seminar rate answer different questions and should
       never be read off the same running total.

       Order is fixed rather than first-seen: the section a reader is looking
       for should not move because a plan was created in a different order. */
    $pgdTypeOrder = ['SEMINAR', 'THEORY', 'TUTORIAL', 'PRACTICAL', 'WORKSHOP', 'LAB'];
    $pgdTypeDots = [
        'TUTORIAL' => '#0E5A61', 'SEMINAR' => '#C9922B', 'THEORY' => '#4B4FA6',
        'PRACTICAL' => '#1B7F5A', 'WORKSHOP' => '#8A5CB8', 'LAB' => '#2AA9C4',
    ];

    /* The header tint is the dot colour at low alpha, so the two can never
       drift apart. Emitted as an "r, g, b" triple rather than a finished rgba
       so the stylesheet owns the alpha for the resting and hover states. */
    $pgdRgb = function ($hex) {
        $hex = ltrim($hex, '#');

        return implode(', ', [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))]);
    };

    $typeGroups = [];
    foreach($moduleRows as $row):
        $key = $row['type'] !== '' ? $row['type'] : 'UNSPECIFIED';

        if(!isset($typeGroups[$key])):
            $dot = ($pgdTypeDots[$key] ?? '#7A8791');
            $typeGroups[$key] = [
                'label' => $key,
                'dot' => $dot,
                'rgb' => $pgdRgb($dot),
                'rows' => [], 'attended' => 0, 'total' => 0,
                'submitted' => 0, 'passed' => 0, 'expected' => 0,
            ];
        endif;

        $typeGroups[$key]['rows'][] = $row;
        $typeGroups[$key]['attended'] += $row['attended'];
        $typeGroups[$key]['total'] += $row['total'];
        /* Totalled from the marks, not averaged from the module percentages.
           One denominator for both, because they are the same cohort. */
        $typeGroups[$key]['submitted'] += $row['submission']['counted'];
        $typeGroups[$key]['passed'] += $row['pass']['counted'];
        $typeGroups[$key]['expected'] += $row['submission']['expected'];
    endforeach;

    uksort($typeGroups, function($a, $b) use($pgdTypeOrder) {
        $ai = array_search($a, $pgdTypeOrder, true);
        $bi = array_search($b, $pgdTypeOrder, true);
        /* Anything not in the list sorts alphabetically after everything that
           is, so a new class type appears rather than disappearing. */
        $ai = ($ai === false ? count($pgdTypeOrder) : $ai);
        $bi = ($bi === false ? count($pgdTypeOrder) : $bi);

        return $ai === $bi ? strcmp($a, $b) : $ai - $bi;
    });

    foreach($typeGroups as $key => $g):
        $typeGroups[$key]['rate'] = ($g['attended'] > 0 && $g['total'] > 0 ? $g['attended'] / $g['total'] * 100 : 0);
        /* Null, not zero, when nothing is expected: no cohort means no rate. */
        $typeGroups[$key]['subRate'] = ($g['expected'] > 0 ? $g['submitted'] / $g['expected'] * 100 : null);
        $typeGroups[$key]['passRate'] = ($g['expected'] > 0 ? $g['passed'] / $g['expected'] * 100 : null);
    endforeach;

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
            @php
                /* Null when no cohort is enrolled at all — a grey bar and a dash
                   rather than a red 0.0%, which would read as a failure. */
                $kpi = function ($figures) use ($pgdTone, $pgdToneDot) {
                    $rate = $figures['rate'];

                    return [
                        'text' => $rate === null ? '—' : number_format($rate, 1).'%',
                        'dot' => $rate === null ? '#8A9299' : $pgdToneDot[$pgdTone($rate)],
                        'width' => $rate === null ? 2 : max(2, min(100, round($rate))),
                    ];
                };
                $subKpi = $kpi($submissionOverall);
                $passKpi = $kpi($passOverall);
            @endphp
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $subKpi['dot'] }};"></span>Submission</div>
                <div class="pgd-kpi__value"><strong>{{ $subKpi['text'] }}</strong><span>{{ number_format($submissionOverall['counted']) }} of {{ number_format($submissionOverall['expected']) }} expected</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ $subKpi['width'] }}%; background: {{ $subKpi['dot'] }};"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $passKpi['dot'] }};"></span>Pass rate</div>
                <div class="pgd-kpi__value"><strong>{{ $passKpi['text'] }}</strong><span>{{ number_format($passOverall['counted']) }} of {{ number_format($passOverall['expected']) }} passed</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ $passKpi['width'] }}%; background: {{ $passKpi['dot'] }};"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $loadColor }};"></span>Load</div>
                <div class="pgd-kpi__value"><strong>{{ number_format($load, 2) }}</strong><span>of contract</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ min(100, round($load * 100)) }}%; background: {{ $loadColor }};"></span></div>
            </div>
        </div>

        <section class="pgd-table">
            @forelse($typeGroups as $key => $g)
                @php
                    /* Submission and achievement are only ever recorded against
                       taught Theory classes. On a Seminar they were three
                       columns of nothing wide enough to push the real figures
                       off to the right, so those sections drop them and get the
                       width back. */
                    $isTheory = \App\Services\SubmissionRate::isSubmitting($key);
                    $cols = $isTheory ? 'pgd-cols-detail' : 'pgd-cols-detail--slim';
                @endphp

                {{-- One section per class type, each carrying its own column
                     headings and its own totals: a Seminar rate and a Theory
                     rate answer different questions and should never be read
                     off the same running total. --}}
                <div class="pgd-acc">
                    <button type="button" class="pgd-acc__head" data-pgd-acc aria-expanded="true"
                            style="--pgd-acc-rgb: {{ $g['rgb'] }};">
                        <svg class="pgd-acc__chev" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"></path></svg>
                        <span class="pgd-acc__dot" style="background: {{ $g['dot'] }};"></span>
                        <span class="pgd-acc__title">{{ $g['label'] }}</span>
                        <span class="pgd-acc__count">{{ count($g['rows']) }} {{ count($g['rows']) == 1 ? 'module' : 'modules' }}</span>

                        {{-- Repeated on the header so a collapsed section still
                             reports itself. --}}
                        <span class="pgd-acc__sum">
                            <span class="pgd-rate pgd-rate--{{ $pgdTone($g['rate']) }}"><span></span>{{ number_format($g['rate'], 2) }}%</span>
                            @if($isTheory)
                                <span class="pgd-acc__note">{{ number_format($g['expected']) }} expected</span>
                            @endif
                        </span>
                    </button>

                    <div class="pgd-acc__body">
                        <div class="pgd-table__head {{ $cols }}">
                            <span></span>
                            <span>Module name</span>
                            <span>Group</span>
                            <span class="pgd-t-right pgd-t-nowrap">Attendance</span>
                            @if($isTheory)
                                <span class="pgd-t-right pgd-t-nowrap">Exp. sub.</span>
                                <span class="pgd-t-right pgd-t-nowrap">Submission</span>
                                <span class="pgd-t-right pgd-t-nowrap">Pass rate</span>
                            @endif
                            <span></span>
                        </div>

                        <div class="pgd-table__row pgd-table__row--total {{ $cols }}">
                            <span></span>
                            <span>Overall</span>
                            <span></span>
                            <span class="pgd-t-right" style="color: #0E5A61; font-variant-numeric: tabular-nums;">{{ number_format($g['rate'], 2) }}%</span>
                            @if($isTheory)
                                <span class="pgd-t-right" style="font-variant-numeric: tabular-nums;">{{ number_format($g['expected']) }}</span>
                                <span class="pgd-t-right" style="font-variant-numeric: tabular-nums; {{ $g['subRate'] === null ? 'color: var(--pgd-ink-faint);' : 'color: #0E5A61;' }}"
                                      title="{{ $g['submitted'] }} of {{ $g['expected'] }} expected submissions">
                                    {{ $g['subRate'] === null ? '—' : number_format($g['subRate'], 2).'%' }}
                                </span>
                                <span class="pgd-t-right" style="font-variant-numeric: tabular-nums; {{ $g['passRate'] === null ? 'color: var(--pgd-ink-faint);' : 'color: #0E5A61;' }}"
                                      title="{{ $g['passed'] }} of {{ $g['expected'] }} passed">
                                    {{ $g['passRate'] === null ? '—' : number_format($g['passRate'], 2).'%' }}
                                </span>
                            @endif
                            <span></span>
                        </div>

                        @foreach($g['rows'] as $row)
                            <div class="pgd-table__row {{ $cols }}">
                                {{-- Both faces when the tutor and the personal tutor differ,
                                     each captioned on hover. --}}
                                <span class="pgd-people">
                                    @forelse($row['people'] as $person)
                                        <span class="pgd-people__face" data-pgd-tooltip="{{ $person['role'] }} &middot; {{ $person['name'] }}">
                                            <span class="pgd-avatar" style="background: {{ $person['color'] }};">
                                                @if(!empty($person['photo']))
                                                    <img src="{{ $person['photo'] }}" alt="{{ $person['name'] }}">
                                                @else
                                                    {{ $person['initials'] }}
                                                @endif
                                            </span>
                                        </span>
                                    @empty
                                        <span class="pgd-people__face" data-pgd-tooltip="Tutor &middot; {{ $tutorName }}">
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
                                @if($isTheory)
                                    <span class="pgd-num pgd-t-right">{{ $row['expected'] }}</span>
                                    <span class="pgd-t-right">
                                        @if($row['submission']['rate'] === null)
                                            <span class="pgd-num pgd-num--muted">&mdash;</span>
                                        @else
                                            <span class="pgd-rate pgd-rate--{{ $pgdTone($row['submission']['rate']) }}"
                                                  title="{{ $row['submission']['counted'] }} of {{ $row['submission']['expected'] }} expected submissions"><span></span>{{ number_format($row['submission']['rate'], 2) }}%</span>
                                        @endif
                                    </span>
                                    <span class="pgd-t-right">
                                        @if($row['pass']['rate'] === null)
                                            <span class="pgd-num pgd-num--muted">&mdash;</span>
                                        @else
                                            <span class="pgd-rate pgd-rate--{{ $pgdTone($row['pass']['rate']) }}"
                                                  title="{{ $row['pass']['counted'] }} of {{ $row['pass']['expected'] }} passed"><span></span>{{ number_format($row['pass']['rate'], 2) }}%</span>
                                        @endif
                                    </span>
                                @endif
                                <span class="pgd-t-center">
                                    <a href="{{ route('tutor-dashboard.plan.module.show', $row['id']) }}" class="pgd-eye" data-pgd-tooltip="Open module details">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12s2.5-5 7-5 7 5 7 5-2.5 5-7 5-7-5-7-5Z"></path><circle cx="12" cy="12" r="1.7"></circle></svg>
                                    </a>
                                </span>
                            </div>
                        @endforeach
                    </div>
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
