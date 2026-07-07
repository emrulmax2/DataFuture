@php
    $termPct = isset($avarageTotalPercentage[$termId]) ? $avarageTotalPercentage[$termId] : 0;
    $isRed = (isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId] === 0) || $termPct < 50;
    $accent = $isRed ? '#B3392E' : '#0B6B66';
    $headerBg = $isRed ? '#FBEDEB' : '#E9F4F2';
    $headerBorder = $isRed ? '#F0C7C2' : '#CBE3DF';

    // Date range: "19 May – 18 Jul 2025" (omit start year when same as end)
    $tStart = strtotime($term[$termId]["start_date"]);
    $tEnd   = strtotime($term[$termId]["end_date"]);
    $startStr = (date('Y', $tStart) === date('Y', $tEnd)) ? date('d M', $tStart) : date('d M Y', $tStart);
    $rangeStr = $startStr.' – '.date('d M Y', $tEnd);
    $lastStr  = (isset($lastAttendanceDate[$termId]) && !empty($lastAttendanceDate[$termId]) && $lastAttendanceDate[$termId] != "N/A") ? date('d M Y', strtotime($lastAttendanceDate[$termId])) : '—';

    $dist  = $totalFullSetFeedList[$termId] ?? '';
    $days  = (isset($totalClassFullSet[$termId]) && $totalClassFullSet[$termId] != 0) ? $totalClassFullSet[$termId].' days' : 'No class found';
    $statsStr = (strlen($dist) > 0 ? $dist.' — ' : '').$days;
@endphp
<div class="term-block">
    <div class="term-head" style="background:{{ $headerBg }}; border:1px solid {{ $headerBorder }}; border-top:3px solid {{ $accent }};">
        <span class="name">{{ $term[$termId]["name"] }}</span>
        <span class="pct" style="color:{{ $accent }};">{{ number_format((float) $termPct, 2) }}%</span>
        <span class="stats">{{ $statsStr }}</span>
        <span class="range">{{ $rangeStr }} · Last attendance {{ $lastStr }}</span>
    </div>
    <div class="term-body">
        <div class="atn-grid atn-colhead">
            <div>Module</div>
            <div>Group</div>
            <div>Room</div>
            <div>Time</div>
            <div>Tutor</div>
            <div style="text-align:right;">Average</div>
        </div>

        @foreach($dataStartPoint as $planId => $data)
            @if(isset($planDetails[$termId][$planId]) && !empty($planDetails[$termId][$planId]))
                @php
                    // Time: "09:45 – 11:45 AM" (collapse meridiem when equal), else "11:45 AM – 01:45 PM"
                    if(isset($planDetails[$termId][$planId]->start_time) && isset($planDetails[$termId][$planId]->end_time)){
                        $s = strtotime(date("Y-m-d ".$planDetails[$termId][$planId]->start_time));
                        $e = strtotime(date("Y-m-d ".$planDetails[$termId][$planId]->end_time));
                        $startShow = (date('A', $s) === date('A', $e)) ? date('h:i', $s) : date('h:i A', $s);
                        $timeStr = $startShow.' – '.date('h:i A', $e);
                    } else {
                        $timeStr = "N/A";
                    }

                    // Split "Name-CODE" into title + code line "CODE · planId"
                    $rawMod = $moduleNameList[$planId] ?? '';
                    $dashPos = strrpos($rawMod, '-');
                    if($dashPos !== false){
                        $modTitle = trim(substr($rawMod, 0, $dashPos));
                        $modCode  = trim(substr($rawMod, $dashPos + 1));
                    } else {
                        $modTitle = $rawMod;
                        $modCode  = '';
                    }
                    $codeLine = ($modCode !== '' ? $modCode : 'NA').' · '.$planId;

                    $modPct   = $avarageDetails[$termId][$planId] ?? null;
                    $pctColor = (is_numeric($modPct) && $modPct >= 70) ? '#0B6B66' : ((is_numeric($modPct) && $modPct >= 40) ? '#8A6D1F' : '#B3392E');

                    if($ClassType[$planId] != 'Tutorial'){
                        $tutorName = !empty($planDetails[$termId][$planId]->tutor->employee) ? $planDetails[$termId][$planId]->tutor->employee->full_name : 'N/A';
                    } else {
                        $tutorName = !empty($planDetails[$termId][$planId]->personalTutor->employee) ? $planDetails[$termId][$planId]->personalTutor->employee->full_name : 'N/A';
                    }
                @endphp

                <div class="atn-grid atn-mod-row">
                    <div>
                        <span class="atn-mod-title">{{ $modTitle }}</span><br>
                        <span class="atn-mod-code">{{ $codeLine }}</span>
                    </div>
                    <div>{{ $planDetails[$termId][$planId]->group->name ?? 'N/A' }}</div>
                    <div>{{ $planDetails[$termId][$planId]->room->name ?? 'N/A' }}</div>
                    <div style="white-space:nowrap;">{{ $timeStr }}</div>
                    <div>{{ $tutorName }}</div>
                    <div class="atn-mod-avg" style="color:{{ $pctColor }};">{{ is_numeric($modPct) ? number_format((float) $modPct, 2).'%' : 'N/A' }}</div>
                </div>
            @endif
        @endforeach
    </div>
</div>
