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
            <h1>Personal tutors</h1>
            <p>{{ count($tutors) }} {{ count($tutors) == 1 ? 'personal tutor' : 'personal tutors' }} · {{ $termDeclaration->name ?? '' }}</p>
        </div>
        <div class="pgd-listhead__actions">
            <select class="pgd-select" name="course_id" id="personalTutorCourseFilter" style="width: 260px;">
                <option value="{{ route('programme.dashboard.personal.tutors', $termDeclaration->id) }}">All courses</option>
                @foreach($courses as $cr)
                    <option {{ $selected_course == $cr->id ? 'selected' : '' }} value="{{ route('programme.dashboard.personal.tutors', [$termDeclaration->id, $cr->id]) }}">{{ $cr->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <section class="pgd-table">
        <div class="pgd-table__head pgd-cols-ptutors">
            <span>Personal tutor</span>
            <span class="pgd-t-right pgd-t-nowrap">Contracted</span>
            <span class="pgd-t-right pgd-t-nowrap">Students</span>
            <span class="pgd-t-right">Load</span>
            <span class="pgd-t-center pgd-t-nowrap">Modules / groups</span>
            <span class="pgd-t-right pgd-t-nowrap">Attendance</span>
            <span class="pgd-t-right pgd-t-nowrap">Outstanding call</span>
            <span class="pgd-t-center pgd-t-nowrap">Uploads due</span>
            <span class="pgd-t-right pgd-t-nowrap">Submission</span>
        </div>

        @forelse($tutors as $tut)
            @php
                $contracted = (isset($tut->contracted_hour) && !empty($tut->contracted_hour) ? $tut->contracted_hour : '00:00');
                $hours = explode(':', $contracted);
                $hour = (isset($hours[0]) ? (int) $hours[0] : 0) + (isset($hours[1]) ? (int) $hours[1] / 60 : 0);
                $assigned = (isset($tut->no_of_assigned) && $tut->no_of_assigned > 0 ? $tut->no_of_assigned : 0);
                $load = ($hour > 0 && $assigned > 0 ? $assigned / $hour : 0);
                $rate = $pgdRate($tut->attendances);
                $uploads = (isset($tut->undecidedUploads) ? $tut->undecidedUploads : 0);
                $name = (isset($tut->employee->full_name) ? $tut->employee->full_name : 'Unknown Employee');
                $role = trim((isset($tut->employee->employment->employeeWorkType->name) ? $tut->employee->employment->employeeWorkType->name : '')
                    .(isset($tut->employee->employment->employeeJobTitle->name) ? ' - '.$tut->employee->employment->employeeJobTitle->name : ''));
            @endphp
            <div class="pgd-table__row pgd-cols-ptutors">
                <a href="{{ route('programme.dashboard.personal.tutors.details', [$termDeclaration->id, $tut->id]) }}" class="pgd-who">
                    <span class="pgd-avatar" style="background: {{ \App\Support\Avatar::soft($name) }};">
                        @if(!\App\Support\Avatar::isGenerated($tut->employee->photo_url ?? null))
                            <img src="{{ $tut->employee->photo_url }}" alt="{{ $name }}">
                        @else
                            {{ $tut->initials ?? '' }}
                        @endif
                    </span>
                    <span class="pgd-who__copy">
                        <span class="pgd-who__name">{{ $name }}</span>
                        <span class="pgd-who__role">{{ $role !== '' ? $role : 'Personal Tutor' }}</span>
                    </span>
                </a>
                <span class="pgd-num pgd-t-right">{{ $contracted }}</span>
                <span class="pgd-num pgd-t-right">{{ $assigned }}</span>
                <span class="pgd-num pgd-num--muted pgd-t-right">{{ number_format($load, 2) }}</span>
                <span class="pgd-t-center" style="display: flex; justify-content: center; gap: 6px;">
                    <span class="pgd-count" title="Modules">{{ (isset($tut->no_of_module) && $tut->no_of_module > 0 ? $tut->no_of_module : 0) }}</span>
                    <span class="pgd-count pgd-count--alt" title="Groups">{{ (isset($tut->no_of_group) && $tut->no_of_group > 0 ? $tut->no_of_group : 0) }}</span>
                </span>
                <span class="pgd-t-right">
                    <span class="pgd-rate pgd-rate--{{ $pgdTone($rate) }}"><span></span>{{ number_format($rate, 2) }}%</span>
                </span>
                <span class="pgd-num pgd-t-right">{{ number_format($tut->outstanding_calls) }}</span>
                <span class="pgd-t-center">
                    <button type="button"
                            data-plan="0" data-tutor="{{ $tut->id }}" data-term="{{ $termDeclaration->id }}"
                            {!! $uploads > 0 ? 'data-tw-toggle="modal" data-tw-target="#viewElearnincTrackingModal"' : '' !!}
                            class="{{ $uploads > 0 ? 'showUndeciededModulesBtn' : '' }} pgd-count pgd-count--pill {{ $uploads > 0 ? 'pgd-count--due' : 'pgd-count--zero' }}">
                        {{ $uploads }}
                    </button>
                </span>
                <span class="pgd-num pgd-num--muted pgd-t-right">0.0%</span>
            </div>
        @empty
            <div class="pgd-table__empty">No personal tutors found for the selected terms.</div>
        @endforelse
    </section>
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
