@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="spf-page-head spf-page-head--baseline">
        <h1 class="spf-h1">Student performance</h1>
        <span class="spf-eyebrow">By attendance term, newest first</span>
    </div>


    @if(isset($termSet) && $termSet->count() > 0)
        <div class="spf-terms">
            @foreach($termSet as $term)
                @php
                    /* Same weighting the college publishes on the staff side:
                       an attendance band score plus the academic points earned,
                       against the best either could have scored. */
                    $averageAttendance = isset($termAttendanceCount[$term->id]['avg'])
                        ? round($termAttendanceCount[$term->id]['avg']) : 0;

                    $attendanceCriteria = \App\Models\AttendanceCriteria::where('range_from', '<=', $averageAttendance)
                        ->where('range_to', '>=', $averageAttendance)
                        ->first();

                    $attendancePoint = isset($attendanceCriteria->id) ? round($attendanceCriteria->point) : 0;

                    if (isset($perTermModuleCriteria[$term->id])) {
                        $achievedResult = $perTermModuleCriteria[$term->id];
                        $expectedResult = $perTermTopSet[$term->id];
                    } else {
                        $achievedResult = 0;
                        $expectedResult = 0;
                    }

                    $achievedTotal = $attendancePoint + $achievedResult;
                    $expectedTotal = $TopAttendanceCriteria + $expectedResult;
                    $percent = $expectedTotal > 0 ? round(($achievedTotal / $expectedTotal) * 100, 2) : 0;

                    $criteria = \App\Models\TermPerformanceCriteria::where('range_from', '<=', $percent)
                        ->where('range_to', '>=', $percent)
                        ->first();

                    $tone = \App\Support\StudentTermPerformance::tone($percent);
                    $chipClass = $tone === 'green' ? 'spf-chip--green' : ($tone === 'cream' ? 'spf-chip--cream' : 'spf-chip--rust');
                    $barClass = $tone === 'green' ? 'spf-bar__fill--green' : '';

                    $attendancePercentOfTop = $TopAttendanceCriteria > 0
                        ? round(($attendancePoint / $TopAttendanceCriteria) * 100, 2) : 0;
                    $academicPercentOfTop = $expectedResult > 0
                        ? round(($achievedResult / $expectedResult) * 100, 2) : 0;
                @endphp

                <div class="spf-term">
                    <div class="spf-term__head">
                        <h3 class="spf-h2">{{ $term->name }}</h3>
                        <span class="spf-chip {{ $chipClass }} spf-chip--lg">
                            {{ optional($criteria)->label ?? 'Not available' }} &middot; {{ round($percent) }}%
                        </span>
                        <div class="spf-spacer"></div>
                        <span class="spf-term__score">Term performance <strong>{{ $achievedTotal }} of {{ $expectedTotal }}</strong></span>
                    </div>

                    <div class="spf-bar">
                        <div class="spf-bar__fill {{ $barClass }}" style="width:{{ min(100, $percent) }}%"></div>
                    </div>

                    <div class="spf-split">
                        <div>
                            <div class="spf-split__label">Attendance &middot; {{ $attendancePoint }} of {{ $TopAttendanceCriteria }} ({{ $averageAttendance }}% present)</div>
                            <div class="spf-bar spf-bar--thin">
                                <div class="spf-bar__fill" style="width:{{ min(100, $attendancePercentOfTop) }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="spf-split__label">Academic results &middot; {{ $achievedResult }} of {{ $expectedResult }}</div>
                            <div class="spf-bar spf-bar--thin">
                                <div class="spf-bar__fill" style="width:{{ min(100, $academicPercentOfTop) }}%"></div>
                            </div>
                        </div>
                    </div>

                    @if(isset($results[$term->id]) && count($results[$term->id]) > 0)
                        <div class="spf-modlist">
                            @foreach($results[$term->id] as $moduleName => $result)
                                <div class="spf-modlist__row">
                                    <span class="spf-modlist__dot {{ $result['grade'] === '' ? 'spf-modlist__dot--pending' : '' }}"></span>
                                    <span class="spf-modlist__name">{{ $result['module'] }}</span>
                                    <span class="spf-modlist__grade">
                                        {{ $result['grade'] !== '' ? $result['grade'] : '—' }}
                                        @if($result['academic_criteria'] !== '')
                                            &middot; {{ $result['academic_criteria'] }}/{{ $TopAcademicCriteria }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="spf-empty">No term performance has been recorded yet.</div>
    @endif
@endsection
