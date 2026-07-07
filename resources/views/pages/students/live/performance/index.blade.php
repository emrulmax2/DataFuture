@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info (navy hero + course sub-tabs) -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    @php
        // Rating label -> [accent, header background tint, header border]
        $ratingTint = function ($label) {
            $l = strtolower($label ?? '');
            if (str_contains($l, 'good'))     return ['#0B6B66', '#E9F4F2', '#CBE3DF'];
            if (str_contains($l, 'standard')) return ['#8A6D1F', '#FAF3E1', '#EAD9A8'];
            return ['#B3392E', '#FBEDEB', '#F0C7C2']; // requires improvement / other
        };
        // Achieved vs expected -> bar colour band
        $bandColor = function ($a, $e) {
            $p = $e > 0 ? $a / $e : 0;
            return $p >= 0.7 ? '#0B6B66' : ($p >= 0.4 ? '#8A6D1F' : '#B3392E');
        };
        // Bar width, min 2% so a sliver always shows
        $barW = function ($a, $e) {
            return ($e > 0 ? max(($a / $e) * 100, 2) : 2) . '%';
        };
        $gradeMeta = [
            'P' => ['#0B6B66', '#E5F2F0'],
            'M' => ['#8A6D1F', '#F4EBD6'],
            'D' => ['#0F252D', '#E2E8E9'],
        ];
    @endphp

    <!-- BEGIN: Term Performance -->
    @if(isset($termSet) && count($termSet))
        <div class="intro-y mt-5 student-performance-page" style="display:flex; flex-direction:column; gap:14px; margin-top:16px;">
            @foreach($termSet as $term)
                @php
                    $avarageAttendance = isset($termAttendanceCount[$term->id]['avg']) ? round($termAttendanceCount[$term->id]['avg']) : 0;

                    $attendanceCriteriaFound = \App\Models\AttendanceCriteria::where('range_from', '<=', $avarageAttendance)
                        ->where('range_to', '>=', $avarageAttendance)
                        ->first();
                    $attendance_criteria = isset($attendanceCriteriaFound->id) ? round($attendanceCriteriaFound->point) : 0;

                    if(isset($perTermModuleCriteria[$term->id])):
                        $achivedResult       = $perTermModuleCriteria[$term->id];
                        $expectedResult      = $perTermTopSet[$term->id];
                        $achivedPerformance  = $attendance_criteria + $perTermModuleCriteria[$term->id];
                        $expectedPerformance = $TopAttendanceCriteria + $perTermTopSet[$term->id];
                    else:
                        $achivedPerformance = 0; $expectedPerformance = 0; $achivedResult = 0; $expectedResult = 0;
                    endif;

                    $avgPerformance = $expectedPerformance != 0 ? number_format(($achivedPerformance / $expectedPerformance) * 100, 2) : 0;

                    $performanceOutput = \App\Models\TermPerformanceCriteria::where('range_from', '<=', $avgPerformance)
                        ->where('range_to', '>=', $avgPerformance)
                        ->first();

                    // ---- derived display values ----
                    [$accent, $hdrBg, $hdrBorder] = $ratingTint($performanceOutput->label ?? '');
                    $rating       = $performanceOutput->label ?? 'N/A';
                    $termPerf     = $achivedPerformance . '/' . $expectedPerformance;
                    $overallWidth = ($expectedPerformance > 0 ? max(($achivedPerformance / $expectedPerformance) * 100, 2) : 2) . '%';
                    $attColor     = $bandColor($attendance_criteria, $TopAttendanceCriteria);
                    $acaColor     = $bandColor($achivedResult, $expectedResult);
                    $attWidth     = $barW($attendance_criteria, $TopAttendanceCriteria);
                    $acaWidth     = $barW($achivedResult, $expectedResult);
                    $moduleCount  = isset($results[$term->id]) ? count($results[$term->id]) : 0;
                @endphp

                <div class="intro-y" style="background:#fff; border-radius:12px; border:1px solid #E5EBEC; overflow:hidden;">

                    <!-- Term header -->
                    <div style="display:flex; align-items:center; gap:12px; padding:13px 24px; background:{{ $hdrBg }}; border-bottom:1px solid {{ $hdrBorder }}; box-shadow:inset 0 3px 0 {{ $accent }}; flex-wrap:wrap;">
                        <div style="font-size:14px; font-weight:700; color:#152528;">{{ $term->name }}</div>
                        <span style="display:inline-flex; align-items:center; gap:6px; font-size:11px; font-weight:700; color:{{ $accent }}; background:#fff; padding:3px 11px; border-radius:999px;">
                            <span style="width:5px; height:5px; border-radius:50%; background:{{ $accent }};"></span>{{ $rating }} &middot; {{ $avgPerformance }}%
                        </span>
                        <span style="font-size:11.5px; color:#5B6E72;">Attendance {{ $avarageAttendance }}%</span>
                        <div style="margin-left:auto; display:flex; align-items:baseline; gap:7px;">
                            <span style="font-size:10px; letter-spacing:0.07em; text-transform:uppercase; color:#7C8D91; font-weight:700;">Term Performance</span>
                            <span style="font-size:16px; font-weight:700; color:{{ $accent }};">{{ $termPerf }}</span>
                        </div>
                    </div>

                    <!-- Metric bars -->
                    <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:0; border-bottom:1px solid #EDF1F2;">

                        <!-- Overall -->
                        <div style="padding:14px 20px 16px 24px; border-right:1px solid #EDF1F2;">
                            <div style="font-size:10px; letter-spacing:0.07em; text-transform:uppercase; color:#7C8D91; font-weight:700; margin-bottom:10px;">Overall</div>
                            <div style="display:flex; flex-direction:column; gap:9px;">
                                <div>
                                    <div style="display:flex; font-size:11.5px; margin-bottom:4px;"><span style="color:#5B6E72;">Expected</span><span style="margin-left:auto; font-weight:700;">{{ $expectedPerformance }}</span></div>
                                    <div style="height:6px; border-radius:999px; background:#EEF2F3;"><div style="height:100%; border-radius:999px; background:#B7C8CC; width:100%;"></div></div>
                                </div>
                                <div>
                                    <div style="display:flex; font-size:11.5px; margin-bottom:4px;"><span style="color:#5B6E72;">Achieved</span><span style="margin-left:auto; font-weight:700; color:{{ $accent }};">{{ $achivedPerformance }}</span></div>
                                    <div style="height:6px; border-radius:999px; background:#EEF2F3;"><div style="height:100%; border-radius:999px; background:{{ $accent }}; width:{{ $overallWidth }};"></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Attendance Performance -->
                        <div style="padding:14px 20px 16px; border-right:1px solid #EDF1F2;">
                            <div style="display:flex; align-items:baseline; gap:8px; margin-bottom:10px;">
                                <div style="font-size:10px; letter-spacing:0.07em; text-transform:uppercase; color:#7C8D91; font-weight:700;">Attendance Performance</div>
                                <span style="font-size:12px; font-weight:700; color:{{ $attColor }};">{{ $attendance_criteria }}/{{ $TopAttendanceCriteria }}</span>
                            </div>
                            <div style="display:flex; flex-direction:column; gap:9px;">
                                <div>
                                    <div style="display:flex; font-size:11.5px; margin-bottom:4px;"><span style="color:#5B6E72;">Expected</span><span style="margin-left:auto; font-weight:700;">{{ $TopAttendanceCriteria }}</span></div>
                                    <div style="height:6px; border-radius:999px; background:#EEF2F3;"><div style="height:100%; border-radius:999px; background:#B7C8CC; width:100%;"></div></div>
                                </div>
                                <div>
                                    <div style="display:flex; font-size:11.5px; margin-bottom:4px;"><span style="color:#5B6E72;">Achieved</span><span style="margin-left:auto; font-weight:700; color:{{ $attColor }};">{{ $attendance_criteria }}</span></div>
                                    <div style="height:6px; border-radius:999px; background:#EEF2F3;"><div style="height:100%; border-radius:999px; background:{{ $attColor }}; width:{{ $attWidth }};"></div></div>
                                </div>
                            </div>
                        </div>

                        <!-- Academic Performance -->
                        <div style="padding:14px 24px 16px 20px;">
                            <div style="display:flex; align-items:baseline; gap:8px; margin-bottom:10px;">
                                <div style="font-size:10px; letter-spacing:0.07em; text-transform:uppercase; color:#7C8D91; font-weight:700;">Academic Performance</div>
                                <span style="font-size:12px; font-weight:700; color:{{ $acaColor }};">{{ $achivedResult }}/{{ $expectedResult }}</span>
                            </div>
                            <div style="display:flex; flex-direction:column; gap:9px;">
                                <div>
                                    <div style="display:flex; font-size:11.5px; margin-bottom:4px;"><span style="color:#5B6E72;">Result expected</span><span style="margin-left:auto; font-weight:700;">{{ $expectedResult }}</span></div>
                                    <div style="height:6px; border-radius:999px; background:#EEF2F3;"><div style="height:100%; border-radius:999px; background:#B7C8CC; width:100%;"></div></div>
                                </div>
                                <div>
                                    <div style="display:flex; font-size:11.5px; margin-bottom:4px;"><span style="color:#5B6E72;">Result achieved</span><span style="margin-left:auto; font-weight:700; color:{{ $acaColor }};">{{ $achivedResult }}</span></div>
                                    <div style="height:6px; border-radius:999px; background:#EEF2F3;"><div style="height:100%; border-radius:999px; background:{{ $acaColor }}; width:{{ $acaWidth }};"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modules -->
                    @if(isset($results[$term->id]))
                        <div style="padding:12px 24px 16px;">
                            <div style="display:flex; align-items:center; gap:8px; padding-bottom:8px;">
                                <div style="font-size:10px; letter-spacing:0.07em; text-transform:uppercase; color:#7C8D91; font-weight:700;">Modules this term</div>
                                <span style="font-size:10.5px; font-weight:700; color:#9AA7AA; background:#EEF2F3; padding:1px 7px; border-radius:999px;">{{ $moduleCount }}</span>
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:4px 24px;">
                                @foreach($results[$term->id] as $moduleName => $result)
                                    @php
                                        $gm = !empty($result['grade']) ? ($gradeMeta[$result['grade']] ?? ['#43585D', '#EEF2F3']) : null;
                                    @endphp
                                    <div style="display:flex; align-items:center; gap:9px; padding:5px 0; font-size:12.5px;">
                                        <span style="width:16px; height:16px; border-radius:50%; background:#E5F2F0; color:#0B6B66; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;">
                                            <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg>
                                        </span>
                                        <span style="color:#33484D;">{{ $result['module'] }}</span>
                                        @if(!empty($result['grade']))
                                            <span style="margin-left:auto; display:inline-flex; align-items:center; gap:6px;">
                                                <span style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:6px; font-size:11px; font-weight:700; color:{{ $gm[0] }}; background:{{ $gm[1] }};">{{ $result['grade'] }}</span>
                                                <span style="font-size:11px; color:#9AA7AA;">{{ $result['academic_criteria'] }} pts</span>
                                            </span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
    <!-- END: Term Performance -->

@endsection

@section('script')

@vite('resources/js/student-global.js')
@endsection
