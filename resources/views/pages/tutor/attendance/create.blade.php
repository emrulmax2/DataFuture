@extends('../layout/' . $layout)

@php
    $attendanceInfo = $data['attendanceInformation'];
    $classEnded = isset($attendanceInfo->end_time) && $attendanceInfo->end_time != null;
    $canEditAttendance = !$classEnded || ($data['feed_given'] != 1 && $data['feed_count'] == 0) || (isset(auth()->user()->priv()['edit_attendance']) && auth()->user()->priv()['edit_attendance'] == 1);
    $studentTotal = $data['assignStudentList']->count();
    $backUrl = $type == 1 ? route('pt.dashboard') : ($type == 2 ? route('tutor-dashboard.plan.module.show', $data['plan_id']) : ($type == 3 ? route('dashboard') : route('tutor-dashboard.show.new')));
    $teacherLabel = $data['tutor_id'] > 0 ? 'Tutor' : ($data['personal_tutor_id'] > 0 ? 'Personal Tutor' : 'Tutor');
    $teacherName = $data['tutor_id'] > 0 ? $data['tutor'] : ($data['personal_tutor_id'] > 0 ? $data['personal_tutor'] : 'Unknown');
    $startedAt = isset($attendanceInfo->start_time) ? date('h:i A', strtotime($attendanceInfo->start_time)) : $data['start_time'];
    $finishedAt = $classEnded ? date('h:i A', strtotime($attendanceInfo->end_time)) : null;
    $timerHour = $data['classTakenTimeHour'] < 10 ? '0'.$data['classTakenTimeHour'] : $data['classTakenTimeHour'];
    $timerMinute = $data['classTakenTimeMin'] < 10 ? '0'.$data['classTakenTimeMin'] : $data['classTakenTimeMin'];
    $timerSecond = $data['classTakenTimeSeconds'] < 10 ? '0'.$data['classTakenTimeSeconds'] : $data['classTakenTimeSeconds'];
    $presentInitial = 0;
    $absentInitial = 0;
    foreach ($data['assignStudentList'] as $assignedStudent) {
        $initialStatus = $data['attendanceFeed'][$assignedStudent->student->id] ?? 4;
        if (in_array((int) $initialStatus, [1, 2, 3, 5])) {
            $presentInitial++;
        }
        if ((int) $initialStatus === 4) {
            $absentInitial++;
        }
    }
    $presentPercent = $studentTotal > 0 ? round(($presentInitial / $studentTotal) * 100) : 0;
@endphp

@section('body_class', 'tutor-attendance-body')

@section('subhead')
    <title>{{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600;700&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Serif:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body.tutor-attendance-body {
            background: #eef1f0;
        }

        body.tutor-attendance-body .content--top-nav {
            background: #eef1f0;
            border-radius: 0 !important;
        }

        #tutorAttendanceFeed {
            --af-ink: #12312e;
            --af-deep: #10312e;
            --af-muted: #5a6f6c;
            --af-faint: #93a09d;
            --af-line: #e6e1d3;
            --af-soft-line: #eef0ea;
            --af-cream: #fbfaf6;
            --af-green: #0d7c73;
            --af-green-dark: #0a655d;
            --af-danger: #b3261e;
            color: var(--af-ink);
            box-sizing: border-box;
            font-family: "IBM Plex Sans", system-ui, sans-serif;
            margin: 5px 0 0;
            padding: 0 0 60px;
            width: 100%;
        }

        #tutorAttendanceFeed *,
        #tutorAttendanceFeed *::before,
        #tutorAttendanceFeed *::after {
            box-sizing: border-box;
        }

        #tutorAttendanceFeed a {
            text-decoration: none;
        }

        #tutorAttendanceFeed .af-header {
            align-items: center;
            background: #fff;
            border: 1px solid var(--af-line);
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
            margin: 0 0 22px;
            padding: 18px 24px;
        }

        #tutorAttendanceFeed .af-title-wrap {
            align-items: center;
            display: flex;
            gap: 14px;
            min-width: 0;
        }

        #tutorAttendanceFeed .af-title-icon {
            align-items: center;
            background: linear-gradient(150deg, #0f4e47, #0d7c73);
            border-radius: 13px;
            box-shadow: 0 8px 20px -8px rgba(13, 124, 115, .7);
            color: #e8d59a;
            display: inline-flex;
            flex: 0 0 46px;
            height: 46px;
            justify-content: center;
            width: 46px;
        }

        #tutorAttendanceFeed .af-title-icon svg {
            height: 23px;
            width: 23px;
        }

        #tutorAttendanceFeed .af-kicker {
            align-items: center;
            color: var(--af-green);
            display: flex;
            font-size: 10.5px;
            font-weight: 700;
            gap: 7px;
            letter-spacing: .14em;
            margin-bottom: 3px;
            text-transform: uppercase;
        }

        #tutorAttendanceFeed .af-kicker-dot {
            background: #e0483c;
            border-radius: 999px;
            box-shadow: 0 0 0 3px rgba(224, 72, 60, .18);
            height: 7px;
            width: 7px;
        }

        #tutorAttendanceFeed .af-title {
            color: var(--af-deep);
            font-family: "IBM Plex Serif", Georgia, serif;
            font-size: 26px;
            font-weight: 600;
            letter-spacing: -.01em;
            line-height: 1.1;
            margin: 0;
        }

        #tutorAttendanceFeed .af-header-actions {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
        }

        #tutorAttendanceFeed .af-btn {
            align-items: center;
            border-radius: 11px;
            cursor: pointer;
            display: inline-flex;
            font-size: 13px;
            font-weight: 600;
            gap: 8px;
            justify-content: center;
            min-height: 38px;
            padding: 10px 18px;
            transition: all .12s;
            white-space: nowrap;
        }

        #tutorAttendanceFeed .af-btn svg {
            height: 15px;
            width: 15px;
        }

        #tutorAttendanceFeed .af-btn-back,
        #tutorAttendanceFeed .af-btn-save {
            background: var(--af-green);
            border: 1px solid var(--af-green);
            box-shadow: 0 8px 18px -8px rgba(13, 124, 115, .8);
            color: #fff;
        }

        #tutorAttendanceFeed .af-btn-back:hover,
        #tutorAttendanceFeed .af-btn-save:hover {
            background: var(--af-green-dark);
        }

        #tutorAttendanceFeed .af-ended {
            background: rgba(179, 38, 30, .08);
            border: 1px solid rgba(179, 38, 30, .28);
            color: var(--af-danger);
        }

        #tutorAttendanceFeed .af-ended-dot {
            background: var(--af-danger);
            border-radius: 999px;
            height: 7px;
            width: 7px;
        }

        #tutorAttendanceFeed .af-overview {
            display: grid;
            gap: 16px;
            grid-template-columns: minmax(0, 1.35fr) minmax(210px, .85fr) minmax(0, 1.35fr) minmax(210px, .85fr);
            margin-bottom: 22px;
        }

        #tutorAttendanceFeed .af-card {
            background: #fff;
            border: 1px solid var(--af-line);
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
            display: flex;
            flex-direction: column;
            gap: 15px;
            padding: 20px 22px;
        }

        #tutorAttendanceFeed .af-field {
            display: flex;
            flex-direction: column;
            gap: 3px;
            min-width: 0;
        }

        #tutorAttendanceFeed .af-label {
            color: var(--af-faint);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .1em;
            text-transform: uppercase;
        }

        #tutorAttendanceFeed .af-value {
            color: var(--af-deep);
            font-size: 13.5px;
            font-weight: 600;
        }

        #tutorAttendanceFeed .af-mono {
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 12.5px;
        }

        #tutorAttendanceFeed .af-location-room {
            align-items: center;
            color: var(--af-green);
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            gap: 6px;
            margin-top: 1px;
        }

        #tutorAttendanceFeed .af-location-room svg {
            height: 13px;
            width: 13px;
        }

        #tutorAttendanceFeed .af-timer-card {
            align-items: center;
            background: linear-gradient(155deg, #0f4e47, #0b302c);
            border: 1px solid #0c3b36;
            border-radius: 18px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
            padding: 18px;
            position: relative;
        }

        #tutorAttendanceFeed .af-timer-card::before {
            background: radial-gradient(circle, rgba(198, 164, 78, .24), transparent 70%);
            border-radius: 999px;
            content: "";
            height: 110px;
            position: absolute;
            right: -24px;
            top: -30px;
            width: 110px;
        }

        #tutorAttendanceFeed .af-timer-label {
            color: #8fc9c0;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .16em;
            margin-bottom: 8px;
            position: relative;
            text-transform: uppercase;
        }

        #tutorAttendanceFeed .theClockWrap {
            color: #fff;
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 33px;
            font-weight: 600;
            letter-spacing: .01em;
            line-height: 1;
            position: relative;
            white-space: nowrap;
        }

        #tutorAttendanceFeed .af-timer-sub {
            color: #8fc9c0;
            font-size: 11px;
            margin-top: 7px;
            position: relative;
        }

        #tutorAttendanceFeed .af-count-card {
            background: linear-gradient(155deg, #159183, #0e6a60);
            border-radius: 18px;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
            padding: 18px 20px;
            position: relative;
        }

        #tutorAttendanceFeed .af-count-card::before {
            background: radial-gradient(circle, rgba(255, 255, 255, .14), transparent 70%);
            border-radius: 999px;
            bottom: -30px;
            content: "";
            height: 120px;
            left: -20px;
            position: absolute;
            width: 120px;
        }

        #tutorAttendanceFeed .af-count-label {
            color: #c8ece6;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .14em;
            position: relative;
            text-transform: uppercase;
        }

        #tutorAttendanceFeed .attendanceCountWrap {
            color: #fff;
            font-family: "IBM Plex Serif", Georgia, serif;
            font-size: 38px;
            font-weight: 600;
            line-height: 1;
            margin: 6px 0 12px;
            position: relative;
        }

        #tutorAttendanceFeed .attendanceTotalValue {
            font-size: 22px;
            opacity: .55;
        }

        #tutorAttendanceFeed .af-progress {
            background: rgba(255, 255, 255, .22);
            border-radius: 999px;
            height: 7px;
            overflow: hidden;
            position: relative;
        }

        #tutorAttendanceFeed .attendanceProgressBar {
            background: #e8d59a;
            border-radius: 999px;
            display: block;
            height: 100%;
            transition: width .25s;
            width: {{ $presentPercent }}%;
        }

        #tutorAttendanceFeed .af-count-sub {
            color: #c8ece6;
            font-size: 11px;
            margin-top: 8px;
            position: relative;
        }

        #tutorAttendanceFeed .af-table-panel {
            background: #fff;
            border: 1px solid var(--af-line);
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05), 0 24px 50px -40px rgba(11, 35, 32, .5);
            overflow: hidden;
        }

        #tutorAttendanceFeed .af-table-scroll {
            overflow-x: auto;
        }

        #tutorAttendanceFeed #feedAttendanceTable {
            border: 0;
            border-collapse: collapse;
            margin: 0;
            min-width: 1040px;
            table-layout: fixed;
            width: 100%;
        }

        #tutorAttendanceFeed #feedAttendanceTable col.af-col-sl {
            width: 80px;
        }

        #tutorAttendanceFeed #feedAttendanceTable col.af-col-student {
            width: 31%;
        }

        #tutorAttendanceFeed #feedAttendanceTable col.af-col-status {
            width: 17%;
        }

        #tutorAttendanceFeed #feedAttendanceTable thead tr {
            background: #fafaf7;
            border-bottom: 2px solid var(--af-soft-line);
        }

        #tutorAttendanceFeed #feedAttendanceTable th {
            border: 0 !important;
            color: #9aa8a5;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            padding: 15px 26px;
            text-align: left;
            text-transform: uppercase;
        }

        #tutorAttendanceFeed #feedAttendanceTable th.af-actions-head {
            text-align: right;
        }

        #tutorAttendanceFeed #feedAttendanceTable tbody tr {
            border-bottom: 1px solid #f3f4f0;
        }

        #tutorAttendanceFeed #feedAttendanceTable tbody tr:nth-child(even) {
            background: #fbfbf9;
        }

        #tutorAttendanceFeed #feedAttendanceTable td {
            border: 0 !important;
            padding: 12px 26px;
            vertical-align: middle;
        }

        #tutorAttendanceFeed .af-sl {
            color: var(--af-faint);
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 13px;
        }

        #tutorAttendanceFeed .af-student {
            align-items: center;
            display: flex;
            gap: 12px;
            min-width: 0;
        }

        #tutorAttendanceFeed .af-avatar {
            align-items: center;
            border-radius: 999px;
            color: #fff;
            display: inline-flex;
            flex: 0 0 38px;
            font-size: 12px;
            font-weight: 700;
            height: 38px;
            justify-content: center;
            overflow: hidden;
            width: 38px;
        }

        #tutorAttendanceFeed .af-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        #tutorAttendanceFeed .af-student-meta {
            min-width: 0;
        }

        #tutorAttendanceFeed .af-reg {
            color: var(--af-green);
            display: block;
            font-family: "IBM Plex Mono", ui-monospace, Menlo, monospace;
            font-size: 12px;
            font-weight: 600;
        }

        #tutorAttendanceFeed .af-name {
            color: var(--af-ink);
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #tutorAttendanceFeed .af-student-status {
            color: #c0392b;
            display: block;
            font-size: 11.5px;
            font-weight: 600;
            margin-top: 1px;
        }

        #tutorAttendanceFeed .af-status-pill {
            align-items: center;
            background: #f4f5f2;
            border: 1px solid #e6e8e3;
            border-radius: 8px;
            color: #adbbb9;
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            gap: 6px;
            height: 25px;
            padding: 0 11px;
            white-space: nowrap;
        }

        #tutorAttendanceFeed .af-status-pill::before {
            background: currentColor;
            border-radius: 999px;
            content: "";
            height: 6px;
            width: 6px;
        }

        #tutorAttendanceFeed .af-status-pill.is-marked {
            background: color-mix(in srgb, currentColor 10%, #fff);
        }

        #tutorAttendanceFeed .af-attendance-options {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            justify-content: flex-end;
        }

        #tutorAttendanceFeed .attendanceCheckbox {
            display: inline-flex;
            margin: 0 !important;
        }

        #tutorAttendanceFeed .attendanceRadio {
            height: 1px;
            opacity: 0;
            pointer-events: none;
            position: absolute;
            width: 1px;
        }

        #tutorAttendanceFeed .af-attendance-option {
            align-items: center;
            border-radius: 9px;
            cursor: pointer;
            display: inline-flex;
            font-size: 12px;
            font-weight: 600;
            gap: 6px;
            height: 32px;
            min-height: 32px;
            padding: 0 12px;
            transition: all .12s;
            white-space: nowrap;
        }

        #tutorAttendanceFeed .af-attendance-option svg {
            flex: 0 0 15px;
            height: 15px;
            width: 15px;
        }

        #tutorAttendanceFeed .af-option-1 {
            background: rgba(13, 124, 115, .10);
            border: 1px solid rgba(13, 124, 115, .28);
            color: #0d7c73;
        }

        #tutorAttendanceFeed .attendanceRadio:checked + .af-option-1 {
            background: #0d7c73;
            border-color: #0d7c73;
            box-shadow: 0 4px 10px -4px #0d7c73;
            color: #fff;
        }

        #tutorAttendanceFeed .af-option-2 {
            background: rgba(47, 111, 176, .10);
            border: 1px solid rgba(47, 111, 176, .28);
            color: #2f6fb0;
        }

        #tutorAttendanceFeed .attendanceRadio:checked + .af-option-2 {
            background: #2f6fb0;
            border-color: #2f6fb0;
            box-shadow: 0 4px 10px -4px #2f6fb0;
            color: #fff;
        }

        #tutorAttendanceFeed .af-option-3 {
            background: rgba(198, 164, 78, .14);
            border: 1px solid rgba(198, 164, 78, .42);
            color: #a1802f;
        }

        #tutorAttendanceFeed .attendanceRadio:checked + .af-option-3 {
            background: #a1802f;
            border-color: #a1802f;
            box-shadow: 0 4px 10px -4px #a1802f;
            color: #fff;
        }

        #tutorAttendanceFeed .af-option-4 {
            background: rgba(192, 57, 43, .09);
            border: 1px solid rgba(192, 57, 43, .28);
            color: #c0392b;
        }

        #tutorAttendanceFeed .attendanceRadio:checked + .af-option-4 {
            background: #c0392b;
            border-color: #c0392b;
            box-shadow: 0 4px 10px -4px #c0392b;
            color: #fff;
        }

        #tutorAttendanceFeed .af-option-5 {
            background: rgba(181, 96, 47, .10);
            border: 1px solid rgba(181, 96, 47, .30);
            color: #b5602f;
        }

        #tutorAttendanceFeed .attendanceRadio:checked + .af-option-5 {
            background: #b5602f;
            border-color: #b5602f;
            box-shadow: 0 4px 10px -4px #b5602f;
            color: #fff;
        }

        #tutorAttendanceFeed .af-option-default {
            background: #f4f5f2;
            border: 1px solid #e6e8e3;
            color: var(--af-muted);
        }

        #tutorAttendanceFeed .attendanceRadio:checked + .af-option-default {
            background: var(--af-muted);
            border-color: var(--af-muted);
            color: #fff;
        }

        #tutorAttendanceFeed .af-table-footer {
            align-items: center;
            background: var(--af-cream);
            border-top: 1px solid #f0ede3;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            justify-content: space-between;
            padding: 18px 26px;
        }

        #tutorAttendanceFeed .af-footer-summary {
            color: #7c8b88;
            font-size: 12.5px;
        }

        #tutorAttendanceFeed .af-footer-summary strong {
            color: var(--af-ink);
            font-weight: 700;
        }

        #tutorAttendanceFeed .af-footer-summary .is-present {
            color: var(--af-green);
        }

        #tutorAttendanceFeed .af-footer-summary .is-absent {
            color: #c0392b;
        }

        #tutorAttendanceFeed .af-btn-save {
            font-size: 13.5px;
            min-height: 42px;
            padding: 11px 22px;
        }

        #tutorAttendanceFeed .af-btn-save svg {
            height: 16px;
            width: 16px;
        }

        @media (max-width: 1280px) {
            #tutorAttendanceFeed .af-overview {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

        }

        @media (max-width: 768px) {
            #tutorAttendanceFeed {
                margin-top: 0;
                padding: 0 0 40px;
            }

            #tutorAttendanceFeed .af-header {
                align-items: flex-start;
                flex-direction: column;
            }

            #tutorAttendanceFeed .af-header-actions {
                justify-content: flex-start;
                width: 100%;
            }

            #tutorAttendanceFeed .af-overview {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('subcontent')
    <div id="tutorAttendanceFeed">
        <div class="af-header">
            <div class="af-title-wrap">
                <span class="af-title-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                </span>
                <span>
                    <span class="af-kicker"><span class="af-kicker-dot"></span>{{ $classEnded ? 'Closed Session' : 'Live Session' }} &middot; Attendance</span>
                    <h2 class="af-title">Attendance Tracking</h2>
                </span>
            </div>

            <div class="af-header-actions">
                @if($classEnded)
                    <span class="af-btn af-ended"><span class="af-ended-dot"></span>Class Ended</span>
                @endif
                <a href="{{ $backUrl }}" class="af-btn af-btn-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6"></path>
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>

        <div class="af-overview">
            <div class="af-card">
                <div class="af-field">
                    <span class="af-label">Term</span>
                    <span class="af-value">{{ $data['term_dec_name'] }}</span>
                </div>
                <div class="af-field">
                    <span class="af-label">Module</span>
                    <span class="af-value">{{ $data['module'].(isset($data['group']) && !empty($data['group']) ? ' - '.$data['group'] : '') }}</span>
                </div>
                <div class="af-field">
                    <span class="af-label">{{ $teacherLabel }}</span>
                    <span class="af-value">{{ $teacherName }}</span>
                </div>
            </div>

            <div class="af-timer-card">
                <div class="af-timer-label">Elapsed</div>
                <div class="theClockWrap" id="dataclassend" data-classend="{{ $classEnded ? 1 : 0 }}">
                    <label id="hours">{{ $timerHour }}</label>:<label id="minutes">{{ $timerMinute }}</label>:<label id="seconds">{{ $timerSecond }}</label>
                </div>
                <div class="af-timer-sub">since {{ $startedAt }}</div>
            </div>

            <div class="af-card">
                <div class="af-field">
                    <span class="af-label">Started</span>
                    <span class="af-value af-mono">{{ $startedAt }}{{ $finishedAt ? ' - '.$finishedAt : '' }}</span>
                </div>
                <div class="af-field">
                    <span class="af-label">Date</span>
                    <span class="af-value">{{ $data['date'] }}</span>
                    <span class="af-value af-mono">{{ $data['start_time'] }} - {{ $data['end_time'] }}</span>
                </div>
                <div class="af-field">
                    <span class="af-label">Location</span>
                    <span class="af-value">{{ $data['venue'] }}</span>
                    <span class="af-location-room">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"></path>
                            <circle cx="12" cy="10" r="3"></circle>
                        </svg>
                        {{ $data['room'] }}
                    </span>
                </div>
            </div>

            <div class="af-count-card">
                <div class="af-count-label">Marked Present</div>
                <div data-numofstd="{{ $studentTotal }}" class="attendanceCountWrap">
                    <span class="attendancePresentValue">{{ $presentInitial }}</span><span class="attendanceTotalValue">/{{ $studentTotal }}</span>
                </div>
                <div class="af-progress"><span class="attendanceProgressBar"></span></div>
                <div class="af-count-sub"><span class="attendancePctText">{{ $presentPercent }}%</span> present &middot; <span class="attendanceAbsentValue">{{ $absentInitial }}</span> absent</div>
            </div>
        </div>

        @if($canEditAttendance)
            <form id="attendanceFeed" method="post">
        @endif

        <div class="af-table-panel">
            <div class="af-table-scroll">
                <table id="feedAttendanceTable">
                    <colgroup>
                        <col class="af-col-sl">
                        <col class="af-col-student">
                        <col class="af-col-status">
                        <col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th>#SL</th>
                            <th>Student</th>
                            <th>Status</th>
                            <th class="af-actions-head att_count_area">Mark Attendance</th>
                        </tr>
                    </thead>
                    <tbody class="send-notofication">
                        @php $serial = 1; @endphp
                        @foreach($data['assignStudentList'] as $list)
                            @php
                                $existAttendance = (isset($data['attendanceFeed'][$list->student->id]) && $data['attendanceFeed'][$list->student->id] > 0 ? $data['attendanceFeed'][$list->student->id] : 0);
                                $statusActive = (isset($list->student->status->active) && $list->student->status->active == 1 ? 1 : 0);
                                $nameParts = preg_split('/\s+/', trim($list->student->full_name));
                                $initials = strtoupper(substr($nameParts[0] ?? 'S', 0, 1).substr($nameParts[1] ?? ($nameParts[0] ?? 'T'), 0, 1));
                                $avatarColors = ['#7a4fa3', '#137a70', '#2f8f5b', '#c94f7c', '#b5602f', '#2f5fa1', '#a13f6b', '#4a7a2f', '#b3261e', '#0d7c73'];
                                $avatarColor = $avatarColors[$serial % count($avatarColors)];
                            @endphp
                            @if($existAttendance > 0 || $statusActive == 1 || ($list->student->status->id == 43 || $list->student->status->id == 47))
                                <tr class="theAttendanceRow">
                                    <td class="af-sl">{{ $serial }}</td>
                                    <td>
                                        <div class="af-student">
                                            <span class="af-avatar" style="background: {{ $avatarColor }};">
                                                @if(!empty($list->student->photo_url))
                                                    <img alt="{{ $list->student->full_name }}" src="{{ $list->student->photo_url }}">
                                                @else
                                                    {{ $initials }}
                                                @endif
                                            </span>
                                            <span class="af-student-meta">
                                                <span class="af-reg {{ $list->student->status->id == 43 ? 'text-danger' : '' }}">{{ $list->student->registration_no }}</span>
                                                <span class="af-name">{{ $list->student->full_name }}</span>
                                                @if($list->student->status->id == 43)
                                                    <span class="af-student-status">{{ $list->student->status->name }}</span>
                                                @endif
                                            </span>
                                        </div>
                                        <input type="hidden" name="attendances[{{ $data['id'] }}][{{ $serial }}][student_id]" value="{{ $list->student->id }}">
                                        <input type="hidden" name="attendances[{{ $data['id'] }}][{{ $serial }}][plans_date_list_id]" value="{{ $data['id'] }}">
                                    </td>
                                    <td>
                                        <span class="af-status-pill feedTypeCol">Not marked</span>
                                    </td>
                                    <td class="attendance-column">
                                        <div class="af-attendance-options">
                                            @foreach($data['AttendanceFeedStatus'] as $feedType)
                                                @php
                                                    $color = '#0d7c73';
                                                    $optionClass = 'af-option-default';
                                                    $iconPath = 'M20 6 9 17l-5-5';

                                                    switch ((int) $feedType->id) {
                                                        case 1:
                                                            $color = '#0d7c73';
                                                            $optionClass = 'af-option-1';
                                                            $iconPath = 'M20 6 9 17l-5-5';
                                                            break;
                                                        case 2:
                                                            $color = '#2f6fb0';
                                                            $optionClass = 'af-option-2';
                                                            $iconPath = 'M4 5h16v11H4zM9 20h6M12 16v4';
                                                            break;
                                                        case 3:
                                                            $color = '#a1802f';
                                                            $optionClass = 'af-option-3';
                                                            $iconPath = 'M13 17l5-5-5-5M18 12H6';
                                                            break;
                                                        case 4:
                                                            $color = '#c0392b';
                                                            $optionClass = 'af-option-4';
                                                            $iconPath = 'M6 6l12 12M18 6 6 18';
                                                            break;
                                                        case 5:
                                                            $color = '#b5602f';
                                                            $optionClass = 'af-option-5';
                                                            $iconPath = 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0zM12 7v5l3.5 2';
                                                            break;
                                                    }
                                                @endphp
                                                @if($feedType->tutor_availability == 1)
                                                    <span class="attendanceCheckbox">
                                                        <input class="attendanceRadio" data-type="{{ $feedType->name }}" data-color="{{ $color }}" id="radio-switch-{{ $data['id'] }}-{{ $serial }}-{{ $feedType->id }}" {{ ($existAttendance > 0 && $existAttendance == $feedType->id) ? ' checked ' : ($existAttendance == 0 && $feedType->id == 4 ? 'checked' : '') }} name="attendances[{{ $data['id'] }}][{{ $serial }}][attendance_feed_status_id]" value="{{ $feedType->id }}" type="radio">
                                                        <label class="af-attendance-option {{ $optionClass }}" for="radio-switch-{{ $data['id'] }}-{{ $serial }}-{{ $feedType->id }}">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                                                @foreach(explode('M', $iconPath) as $pathIndex => $pathChunk)
                                                                    @if($pathChunk !== '')
                                                                        <path d="M{{ $pathChunk }}"></path>
                                                                    @endif
                                                                @endforeach
                                                            </svg>
                                                            {{ $feedType->name }}
                                                        </label>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                                @php $serial++; @endphp
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="af-table-footer">
                <span class="af-footer-summary">
                    Showing <strong>{{ $studentTotal }}</strong> students &middot;
                    <strong class="is-present attendanceFooterPresent">{{ $presentInitial }}</strong> present &middot;
                    <strong class="is-absent attendanceFooterAbsent">{{ $absentInitial }}</strong> absent
                </span>
                @if($canEditAttendance)
                    <button type="submit" class="save af-btn af-btn-save">
                        <svg class="af-save-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <path d="M17 21v-8H7v8M7 3v5h8"></path>
                        </svg>
                        Save Attendance
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                    <input type="hidden" name="url" value="{{ route('attendance.store') }}">
                    <input type="hidden" name="plan_date_list_id" value="{{ $data['id'] }}">
                    <input type="hidden" name="plan_id" value="{{ $data['plan_id'] }}">
                    <input type="hidden" name="tutor_id" value="{{ $data['tutor_id'] }}">
                @endif
            </div>
        </div>

        @if($canEditAttendance)
            </form>
        @endif
    </div>

    @include('pages.tutor.attendance.modals')
@endsection

@section('script')
    @vite('resources/js/tutor-attendance-feed.js')
@endsection
