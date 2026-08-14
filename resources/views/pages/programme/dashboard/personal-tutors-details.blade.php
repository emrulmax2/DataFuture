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
        ];
    endforeach;

    $overallAttended = $P + $O + $L + $E + $M + $H;
    $overallRate = ($overallAttended > 0 && $TOTAL > 0 ? $overallAttended / $TOTAL * 100 : 0);

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
                <div class="pgd-kpi__label"><span style="background: #B3261E;"></span>Submission</div>
                <div class="pgd-kpi__value"><strong>0.0%</strong><span>of expected</span></div>
                <div class="pgd-kpi__bar"><span style="width: 2%; background: #B3261E;"></span></div>
            </div>
            <div class="pgd-kpi">
                <div class="pgd-kpi__label"><span style="background: {{ $uploadsTotal > 0 ? '#B3261E' : '#0E5A61' }};"></span>Uploads due</div>
                <div class="pgd-kpi__value"><strong>{{ $uploadsTotal }}</strong><span>outstanding</span></div>
                <div class="pgd-kpi__bar"><span style="width: {{ min(100, $uploadsTotal * 12) }}%; background: {{ $uploadsTotal > 0 ? '#B3261E' : '#0E5A61' }};"></span></div>
            </div>
        </div>

        <section class="pgd-table">
            <div class="pgd-table__head pgd-cols-pdetail">
                <span></span>
                <span>Tutor · module name</span>
                <span>Group</span>
                <span class="pgd-t-right pgd-t-nowrap">Attendance</span>
                <span class="pgd-t-right pgd-t-nowrap">Submission</span>
                <span class="pgd-t-right pgd-t-nowrap">Achievement</span>
                <span class="pgd-t-center pgd-t-nowrap">Uploads</span>
                <span></span>
            </div>

            <div class="pgd-table__row pgd-table__row--total pgd-cols-pdetail">
                <span></span>
                <span>Overall</span>
                <span></span>
                <span class="pgd-t-right" style="color: #0E5A61; font-variant-numeric: tabular-nums;">{{ number_format($overallRate, 2) }}%</span>
                <span class="pgd-t-right pgd-num--muted" style="font-variant-numeric: tabular-nums;">0.0%</span>
                <span class="pgd-t-right pgd-num--muted" style="font-variant-numeric: tabular-nums;">0.0%</span>
                <span class="pgd-t-center" style="font-variant-numeric: tabular-nums;">{{ $uploadsTotal }}</span>
                <span></span>
            </div>

            @forelse($moduleRows as $row)
                <div class="pgd-table__row pgd-cols-pdetail">
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
                    <span class="pgd-num pgd-num--muted pgd-t-right">0.0%</span>
                    <span class="pgd-num pgd-num--muted pgd-t-right">0.0%</span>
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
