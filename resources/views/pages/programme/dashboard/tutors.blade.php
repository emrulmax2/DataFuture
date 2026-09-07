@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
@php
    $pgdRate = function ($attendances) {
        $attended = 0;
        $attended += (isset($attendances->P) && $attendances->P > 0 ? $attendances->P : 0);
        $attended += (isset($attendances->O) && $attendances->O > 0 ? $attendances->O : 0);
        $attended += (isset($attendances->L) && $attendances->L > 0 ? $attendances->L : 0);
        $attended += (isset($attendances->E) && $attendances->E > 0 ? $attendances->L : 0);
        $attended += (isset($attendances->M) && $attendances->M > 0 ? $attendances->M : 0);
        $attended += (isset($attendances->H) && $attendances->H > 0 ? $attendances->H : 0);

        $total = (isset($attendances->TOTAL) && $attendances->TOTAL > 0) ? $attendances->TOTAL : 0;

        return ($attended > 0 && $total > 0 ? $attended / $total * 100 : 0);
    };

    $pgdTone = function ($pct) {
        return $pct >= 75 ? 'good' : ($pct >= 50 ? 'mid' : 'bad');
    };
@endphp

<main class="pgd-page">
    <div class="pgd-listhead">
        <div>
            <h1>Programme tutors</h1>
            <p>{{ count($tutors) }} {{ count($tutors) == 1 ? 'staff member' : 'staff' }} teaching · {{ $termDeclaration->name ?? '' }}</p>
        </div>
        <div class="pgd-listhead__actions">
            <select class="pgd-select" name="course_id" id="personalTutorCourseFilter" style="width: 260px;">
                <option value="{{ route('programme.dashboard.tutors', $termDeclaration->id) }}">All courses</option>
                @foreach($courses as $cr)
                    <option {{ $selected_course == $cr->id ? 'selected' : '' }} value="{{ route('programme.dashboard.tutors', [$termDeclaration->id, $cr->id]) }}">{{ $cr->name }}</option>
                @endforeach
            </select>
            <a href="{{ route('programme.dashboard.tutors.export', [$termDeclaration->id, $selected_course]) }}" class="pgd-btn pgd-btn--primary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v5h5"></path><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9l5 5v11a2 2 0 0 1-2 2Z"></path><path d="m9 13 3 4"></path><path d="m12 13-3 4"></path></svg>
                Export XL
            </a>
        </div>
    </div>

    <section class="pgd-table">
        <div class="pgd-table__head pgd-cols-tutors">
            <span>Tutor</span>
            <span class="pgd-t-right pgd-t-nowrap">Contracted hour</span>
            <span class="pgd-t-right pgd-t-nowrap">Class hour</span>
            <span class="pgd-t-right">Load</span>
            <span class="pgd-t-center">Modules</span>
            <span class="pgd-t-right pgd-t-nowrap">Attendance rate</span>
            <span class="pgd-t-right pgd-t-nowrap">Exp. submission</span>
            <span class="pgd-t-right pgd-t-nowrap">Submission rate</span>
            <span class="pgd-t-right pgd-t-nowrap">Pass rate</span>
        </div>

        @forelse($tutors as $tut)
            @php
                $contracted = (isset($tut->contracted_hour) && !empty($tut->contracted_hour) ? $tut->contracted_hour : '00:00');
                $chours = explode(':', $contracted);
                $cHour = (isset($chours[0]) ? (int) $chours[0] : 0) + (isset($chours[1]) ? (int) $chours[1] / 60 : 0);

                $classHour = (isset($tut->class_hours) && !empty($tut->class_hours) ? $tut->class_hours : '00:00');
                $clhours = explode(':', $classHour);
                $clHour = (isset($clhours[0]) ? (int) $clhours[0] : 0) + (isset($clhours[1]) ? (int) $clhours[1] / 60 : 0);

                $load = ($cHour > 0 && $clHour > 0 ? $clHour / $cHour : 0);
                $rate = $pgdRate($tut->attendances);
                $name = (isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee');
                $role = trim((isset($tut->employee->employment->employeeWorkType->name) ? $tut->employee->employment->employeeWorkType->name : '')
                    .(isset($tut->employee->employment->employeeJobTitle->name) ? ' - '.$tut->employee->employment->employeeJobTitle->name : ''));
            @endphp
            <a href="{{ route('programme.dashboard.tutors.details', [$termDeclaration->id, $tut->id]) }}" class="pgd-table__row pgd-cols-tutors">
                <span class="pgd-who">
                    <span class="pgd-avatar" style="background: {{ \App\Support\Avatar::soft($name) }};">
                        @if(!\App\Support\Avatar::isGenerated($tut->employee->photo_url ?? null))
                            <img src="{{ $tut->employee->photo_url }}" alt="{{ $name }}">
                        @else
                            {{ $tut->initials ?? '' }}
                        @endif
                    </span>
                    <span class="pgd-who__copy">
                        <span class="pgd-who__name">{{ $name }}</span>
                        <span class="pgd-who__role">{{ $role !== '' ? $role : 'Staff' }}</span>
                    </span>
                </span>
                <span class="pgd-num pgd-t-right">{{ $contracted }}</span>
                <span class="pgd-num pgd-t-right">{{ $classHour }}</span>
                <span class="pgd-num pgd-num--muted pgd-t-right">{{ number_format($load, 2) }}</span>
                <span class="pgd-t-center"><span class="pgd-count">{{ (isset($tut->no_of_module) && $tut->no_of_module > 0 ? $tut->no_of_module : 0) }}</span></span>
                <span class="pgd-t-right">
                    <span class="pgd-rate pgd-rate--{{ $pgdTone($rate) }}"><span></span>{{ number_format($rate, 2) }}%</span>
                </span>
                <span class="pgd-num pgd-t-right">{{ (isset($tut->expected_submission) && $tut->expected_submission > 0 ? $tut->expected_submission : 0) }}</span>
                {{-- Null, not zero, when nothing is expected: a tutor with no
                     cohort has no submission rate, and a red 0.00% would read
                     as a failure rather than as an empty set. --}}
                <span class="pgd-t-right">
                    @if(($tut->submission['rate'] ?? null) === null)
                        <span class="pgd-num pgd-num--muted">&mdash;</span>
                    @else
                        <span class="pgd-rate pgd-rate--{{ $pgdTone($tut->submission['rate']) }}"
                              title="{{ $tut->submission['counted'] }} of {{ $tut->submission['expected'] }} expected submissions"><span></span>{{ number_format($tut->submission['rate'], 2) }}%</span>
                    @endif
                </span>
                {{-- The same cohort as the column beside it, so the two read
                     as a pair rather than against different denominators. --}}
                <span class="pgd-t-right">
                    @if(($tut->pass['rate'] ?? null) === null)
                        <span class="pgd-num pgd-num--muted">&mdash;</span>
                    @else
                        <span class="pgd-rate pgd-rate--{{ $pgdTone($tut->pass['rate']) }}"
                              title="{{ $tut->pass['counted'] }} of {{ $tut->pass['expected'] }} passed"><span></span>{{ number_format($tut->pass['rate'], 2) }}%</span>
                    @endif
                </span>
            </a>
        @empty
            <div class="pgd-table__empty">No tutors found for the selected terms.</div>
        @endforelse
    </section>
</main>
@endsection
