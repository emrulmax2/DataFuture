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

    $P = $O = $L = $E = $M = $H = $TOTAL = 0;
    $uploadsTotal = 0;
    $moduleRows = [];

    foreach($plans as $pln):
        $attendances = $pln->attendances;

        /* A Theory parent is on the page because its results are this tutor's
           responsibility, but the class is taught by somebody else. Its
           attendance is shown on the row and in the Theory total, and kept out
           of the headline, which answers "how are this tutor's own groups
           attending". */
        $ownClass = !($pln->parent_theory ?? false);

        $attended = 0;
        $attended += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
        $attended += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
        $attended += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
        $attended += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
        $attended += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
        $attended += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);

        $planTotal = (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;

        if($ownClass):
            $P += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
            $O += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
            $L += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
            $E += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
            $M += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
            $H += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);
            $TOTAL += $planTotal;
        endif;

        $uploads = (isset($pln->undecidedUploads) ? $pln->undecidedUploads : 0);
        $uploadsTotal += $uploads;

        $moduleRows[] = [
            'id' => $pln->id,
            'module' => (isset($pln->creations->module->name) ? $pln->creations->module->name : 'Unknown'),
            'type' => (isset($pln->class_type) ? strtoupper($pln->class_type) : ''),
            'group' => (isset($pln->group->name) ? $pln->group->name : ''),
            'people' => (isset($pln->people) ? $pln->people : []),
            'rate' => ($attended > 0 && $planTotal > 0 ? $attended / $planTotal * 100 : 0),
            'uploads' => $uploads,
            /* Kept alongside the rate so a group total can be summed from the
               marks themselves. Averaging the per-module percentages would let
               a two-student class weigh the same as a forty-student one. */
            'attended' => $attended,
            'total' => $planTotal,
            'submission' => ($submissionByPlan[$pln->id] ?? ['counted' => 0, 'expected' => 0, 'rate' => null]),
            'pass' => ($passByPlan[$pln->id] ?? ['counted' => 0, 'expected' => 0, 'rate' => null]),
        ];
    endforeach;

    $overallAttended = $P + $O + $L + $E + $M + $H;
    $overallRate = ($overallAttended > 0 && $TOTAL > 0 ? $overallAttended / $TOTAL * 100 : 0);

    /* One accordion per class type. Tutorial, Seminar and Theory are three
       different jobs with three different attendance expectations, so they are
       totalled apart rather than averaged into one meaningless figure.

       Order is fixed rather than first-seen: the section a tutor is looking
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
                'rows' => [], 'attended' => 0, 'total' => 0, 'uploads' => 0,
                'submitted' => 0, 'passed' => 0, 'expected' => 0,
            ];
        endif;

        $typeGroups[$key]['rows'][] = $row;
        $typeGroups[$key]['attended'] += $row['attended'];
        $typeGroups[$key]['total'] += $row['total'];
        $typeGroups[$key]['uploads'] += $row['uploads'];
        /* Totalled from the marks, not averaged from the module percentages —
           a class of six must not weigh the same as a class of sixty. One
           denominator for both, because they are the same cohort. */
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
    $load = ($contractedMinutes > 0 && $assignedStudents > 0 ? $assignedStudents / ($contractedMinutes / 60) : 0);

    $termMeta = collect($termColours)->first() ?? ['name' => ($termDeclaration->name ?? 'Term'), 'dot' => '#0E5A61'];
    $tutorName = (isset($tutor->employee->full_name) ? $tutor->employee->full_name : 'Unknown Employee');
    // Designation only — the employment type (Employee / Contractor) is
    // deliberately left off this label.
    $tutorRole = trim($tutor->employee->employment->employeeJobTitle->name ?? '');

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
                    <span class="pgd-detailhead__role">{{ $tutorRole !== '' ? $tutorRole : 'Personal Tutor' }}</span>
                    <span class="pgd-detailhead__sum">{{ count($moduleRows) }} {{ count($moduleRows) == 1 ? 'module' : 'modules' }} · {{ number_format($assignedStudents) }} students · {{ $termDeclaration->name ?? '' }}</span>
                </div>
            </div>
            <div class="pgd-detailhead__actions">
                <a href="{{ route('programme.dashboard.personal.tutors', $termDeclaration->id) }}" class="pgd-btn pgd-btn--ghost">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>
                    All personal tutors
                </a>
            </div>
        </div>

        <div class="pgd-kpis">
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $pgdToneDot[$pgdTone($overallRate)] }};"></span>Attendance</div>
                <div class="pgd-kpi__value"><strong>{{ number_format($overallRate, 1) }}%</strong><span>across groups</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ min(100, round($overallRate)) }}%; background: {{ $pgdToneDot[$pgdTone($overallRate)] }};"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: #2AA9C4;"></span>Outstanding calls</div>
                <div class="pgd-kpi__value"><strong>{{ number_format($outstandingCalls) }}</strong><span>to follow up</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ $outstandingCalls > 0 ? min(100, $outstandingCalls) : 2 }}%; background: #2AA9C4;"></span></div>
            </div>
            <div class="pgd-kpi">
                @php $subAll = $submissionOverall['rate']; @endphp
                <div class="pgd-kpi__label"><span style="background: {{ $subAll === null ? '#8A9299' : $pgdToneDot[$pgdTone($subAll)] }};"></span>Submission</div>
                <div class="pgd-kpi__value">
                    <strong>{{ $subAll === null ? '—' : number_format($subAll, 1).'%' }}</strong>
                    <span>{{ number_format($submissionOverall['counted']) }} of {{ number_format($submissionOverall['expected']) }} expected</span>
                </div>
                <div class="pgd-kpi__bar"><span style="width: {{ $subAll === null ? 2 : max(2, min(100, round($subAll))) }}%; background: {{ $subAll === null ? '#8A9299' : $pgdToneDot[$pgdTone($subAll)] }};"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $uploadsTotal > 0 ? '#B3261E' : '#0E5A61' }};"></span>Uploads due</div>
                <div class="pgd-kpi__value"><strong>{{ $uploadsTotal }}</strong><span>outstanding</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ min(100, $uploadsTotal * 12) }}%; background: {{ $uploadsTotal > 0 ? '#B3261E' : '#0E5A61' }};"></span></div>
            </div>
        </div>

        <section class="pgd-table">
            @forelse($typeGroups as $key => $g)
                @php
                    /* Submission and achievement are only ever recorded against
                       taught Theory classes. On Tutorial and Seminar they were
                       two columns of 0.0% wide enough to push the real figures
                       off to the right, so those sections drop them and get the
                       width back. */
                    $isTheory = ($key === 'THEORY');
                    $cols = $isTheory ? 'pgd-cols-pdetail' : 'pgd-cols-pdetail--slim';
                @endphp

                {{-- One section per class type, each carrying its own column
                     headings and its own totals: a Seminar rate and a Tutorial
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
                            <span class="pgd-acc__uploads {{ $g['uploads'] > 0 ? 'pgd-acc__uploads--due' : '' }}">{{ $g['uploads'] }} uploads</span>
                        </span>
                    </button>

                    <div class="pgd-acc__body">
                        <div class="pgd-table__head {{ $cols }}">
                            <span></span>
                            <span>Tutor &middot; module name</span>
                            <span>Group</span>
                            <span class="pgd-t-right pgd-t-nowrap">Attendance</span>
                            @if($isTheory)
                                <span class="pgd-t-right pgd-t-nowrap">Submission</span>
                                <span class="pgd-t-right pgd-t-nowrap">Pass rate</span>
                            @endif
                            <span class="pgd-t-center pgd-t-nowrap">Uploads</span>
                            <span></span>
                        </div>

                        <div class="pgd-table__row pgd-table__row--total {{ $cols }}">
                            <span></span>
                            <span>Overall</span>
                            <span></span>
                            <span class="pgd-t-right" style="color: #0E5A61; font-variant-numeric: tabular-nums;">{{ number_format($g['rate'], 2) }}%</span>
                            @if($isTheory)
                                <span class="pgd-t-right" style="font-variant-numeric: tabular-nums; {{ $g['subRate'] === null ? 'color: var(--pgd-ink-faint);' : 'color: #0E5A61;' }}"
                                      title="{{ $g['submitted'] }} of {{ $g['expected'] }} expected submissions">
                                    {{ $g['subRate'] === null ? '—' : number_format($g['subRate'], 2).'%' }}
                                </span>
                                <span class="pgd-t-right" style="font-variant-numeric: tabular-nums; {{ $g['passRate'] === null ? 'color: var(--pgd-ink-faint);' : 'color: #0E5A61;' }}"
                                      title="{{ $g['passed'] }} of {{ $g['expected'] }} passed">
                                    {{ $g['passRate'] === null ? '—' : number_format($g['passRate'], 2).'%' }}
                                </span>
                            @endif
                            <span class="pgd-t-center" style="font-variant-numeric: tabular-nums;">{{ $g['uploads'] }}</span>
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
                                    @php $sub = $row['submission']; $pass = $row['pass']; @endphp
                                    <span class="pgd-t-right">
                                        @if($sub['rate'] === null)
                                            <span class="pgd-num pgd-num--muted">&mdash;</span>
                                        @else
                                            <span class="pgd-rate pgd-rate--{{ $pgdTone($sub['rate']) }}"
                                                  title="{{ $sub['counted'] }} of {{ $sub['expected'] }} expected submissions"><span></span>{{ number_format($sub['rate'], 2) }}%</span>
                                        @endif
                                    </span>
                                    <span class="pgd-t-right">
                                        @if($pass['rate'] === null)
                                            <span class="pgd-num pgd-num--muted">&mdash;</span>
                                        @else
                                            <span class="pgd-rate pgd-rate--{{ $pgdTone($pass['rate']) }}"
                                                  title="{{ $pass['counted'] }} of {{ $pass['expected'] }} passed"><span></span>{{ number_format($pass['rate'], 2) }}%</span>
                                        @endif
                                    </span>
                                @endif
                                <span class="pgd-t-center">
                                    <button type="button"
                                            data-tutor="{{ $tutor->id }}" data-plan="{{ $row['id'] }}" data-term="{{ $termDeclaration->id }}"
                                            {!! $row['uploads'] > 0 ? 'data-tw-toggle="modal" data-tw-target="#viewElearnincTrackingModal"' : '' !!}
                                            class="{{ $row['uploads'] > 0 ? 'showUndeciededModulesBtn' : '' }} pgd-count pgd-count--pill {{ $row['uploads'] > 0 ? 'pgd-count--due' : 'pgd-count--zero' }}">
                                        {{ $row['uploads'] }}
                                    </button>
                                </span>
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
                <div class="pgd-table__empty">No modules for this personal tutor in the selected term.</div>
            @endforelse
        </section>
    </div>

    <aside style="position: sticky; top: 126px;">
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
                <div class="pgd-profilecard__role">{{ $tutorRole !== '' ? $tutorRole : 'Personal Tutor' }}</div>
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
                <div class="pgd-contact">
                    <span class="pgd-contact__icon">◷</span>
                    <span>
                        <span class="pgd-contact__label">Contracted hours</span>
                        <span class="pgd-contact__value">{{ $contractedHour }}</span>
                    </span>
                </div>
                <div class="pgd-contact">
                    <span class="pgd-contact__icon">◎</span>
                    <span>
                        <span class="pgd-contact__label">Assigned students</span>
                        <span class="pgd-contact__value">{{ number_format($assignedStudents) }} · load {{ number_format($load, 2) }}</span>
                    </span>
                </div>
            </div>
        </section>
    </aside>
</main>

<!-- BEGIN: E-learning tracking (theme modal, driven by manager-tutor-tracking.js) -->
<div id="viewElearnincTrackingModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="viewElearnincTrackingForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">E-learning Tracking</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    {{-- id kept: manager-tutor-tracking.js fills this tbody --}}
                    <table class="table table-report" id="dailyClassInfoTable">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap uppercase">Schedule</th>
                                <th class="whitespace-nowrap uppercase">Module</th>
                                <th class="text-left whitespace-nowrap uppercase">Tutor</th>
                                <th class="text-left whitespace-nowrap uppercase">Room</th>
                                <th class="text-left whitespace-nowrap uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Close</button>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: E-learning tracking -->
@endsection

@section('script')
    @vite('resources/js/manager-tutor-tracking.js')
@endsection
