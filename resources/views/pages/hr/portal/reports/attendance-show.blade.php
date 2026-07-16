@extends('../layout/' . $layout)

@section('body_class', 'hr-attendance-detail-body')

@section('subhead')
    <title>{{ $title }}</title>
    <style>
        body.hr-attendance-detail-body,
        body.hr-attendance-detail-body .content--top-nav {
            background: #eef1f0;
        }

        #attendanceDetailPage {
            --ar-ink: #12312e;
            --ar-muted: #8b9995;
            --ar-line: #e6e1d3;
            --ar-soft-line: #eef0ea;
            --ar-green: #0d7c73;
            --ar-gold: #c6a44e;
            --ar-gold-dark: #a1802f;
            color: var(--ar-ink);
            font-family: "Public Sans", "IBM Plex Sans", system-ui, sans-serif;
            width: 100%;
        }

        #attendanceDetailPage *,
        #attendanceDetailPage *::before,
        #attendanceDetailPage *::after {
            box-sizing: border-box;
        }

        #attendanceDetailPage a {
            text-decoration: none;
        }

        #attendanceDetailPage .ar-card {
            background: #fff;
            border: 1px solid var(--ar-line);
            border-radius: 18px;
            box-shadow: 0 1px 3px rgba(16, 49, 46, .05);
            overflow: hidden;
        }

        #attendanceDetailPage .ar-detail-hero {
            align-items: center;
            display: flex;
            gap: 24px;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 20px 24px;
        }

        #attendanceDetailPage .ar-person {
            align-items: center;
            display: flex;
            gap: 16px;
            min-width: 0;
        }

        #attendanceDetailPage .ar-avatar {
            align-items: center;
            background: radial-gradient(circle at 50% 35%, #1c4d47, #0c2b28);
            border: 2px solid var(--ar-gold);
            border-radius: 14px;
            color: #e8d59a;
            display: inline-flex;
            flex: 0 0 52px;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 16px;
            font-weight: 600;
            height: 52px;
            justify-content: center;
            width: 52px;
        }

        #attendanceDetailPage .ar-avatar.has-photo {
            background: #fff;
            overflow: hidden;
        }

        #attendanceDetailPage .ar-avatar img {
            display: block;
            height: 100%;
            object-fit: cover;
            width: 100%;
        }

        #attendanceDetailPage .ar-eyebrow {
            color: #a1926b;
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .14em;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        #attendanceDetailPage .ar-title {
            color: #0f2d2a;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 23px;
            font-weight: 600;
            letter-spacing: 0;
            line-height: 1.12;
            margin: 0;
        }

        #attendanceDetailPage .ar-title-mark {
            border-bottom: 2px solid var(--ar-gold);
            color: var(--ar-green);
            display: inline-block;
            padding-bottom: 1px;
        }

        #attendanceDetailPage .ar-subtitle {
            color: var(--ar-muted);
            font-size: 13px;
            margin-top: 5px;
        }

        #attendanceDetailPage .ar-subtitle span {
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
        }

        #attendanceDetailPage .ar-back {
            align-items: center;
            background: var(--ar-green);
            border: 1px solid var(--ar-green);
            border-radius: 11px;
            box-shadow: 0 4px 12px -4px rgba(13, 124, 115, .7);
            color: #fff;
            display: inline-flex;
            flex: 0 0 auto;
            font-size: 13.5px;
            font-weight: 700;
            gap: 8px;
            min-height: 42px;
            padding: 0 18px;
        }

        #attendanceDetailPage .ar-back:hover {
            background: #0a655d;
            color: #fff;
        }

        #attendanceDetailPage .ar-detail-scroll {
            overflow-x: auto;
        }

        #attendanceDetailPage .ar-detail-table {
            min-width: 1120px;
        }

        #attendanceDetailPage .ar-detail-grid {
            align-items: center;
            display: grid;
            column-gap: 16px;
            grid-template-columns: 1.55fr .95fr 1.2fr .8fr .95fr .95fr .9fr 2.45fr;
            row-gap: 12px;
            padding: 0 24px;
        }

        #attendanceDetailPage .ar-detail-grid > :nth-child(8) {
            padding-left: 24px;
        }

        #attendanceDetailPage .ar-detail-head {
            background: #fafaf7;
            border-bottom: 2px solid var(--ar-soft-line);
            color: #9aa8a5;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            min-height: 46px;
            padding-bottom: 14px;
            padding-top: 14px;
            text-transform: uppercase;
        }

        #attendanceDetailPage .ar-detail-row {
            background: #fff;
            border-bottom: 1px solid #f3f4f0;
            border-left: 3px solid transparent;
            color: var(--ar-ink);
            cursor: default;
            min-height: 50px;
            padding-bottom: 11px;
            padding-left: 21px;
            padding-top: 11px;
        }

        #attendanceDetailPage .ar-detail-row:nth-of-type(odd) {
            background: #fbfbf9;
        }

        #attendanceDetailPage .ar-detail-row.expandRow {
            cursor: pointer;
        }

        #attendanceDetailPage .ar-detail-row.expandRow:hover {
            background: #f8faf9;
        }

        #attendanceDetailPage .ar-detail-date {
            color: #12312e;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-detail-mono,
        #attendanceDetailPage .ar-detail-money,
        #attendanceDetailPage .ar-detail-strong,
        #attendanceDetailPage .ar-detail-warn,
        #attendanceDetailPage .ar-detail-muted {
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12.5px;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-detail-money,
        #attendanceDetailPage .ar-detail-strong,
        #attendanceDetailPage .ar-detail-warn,
        #attendanceDetailPage .ar-detail-muted {
            text-align: right;
        }

        #attendanceDetailPage .ar-detail-mono,
        #attendanceDetailPage .ar-detail-money {
            color: #5a6f6c;
            font-weight: 500;
        }

        #attendanceDetailPage .ar-detail-strong {
            color: #12312e;
            font-weight: 700;
        }

        #attendanceDetailPage .ar-detail-warn {
            color: var(--ar-gold-dark);
        }

        #attendanceDetailPage .ar-detail-muted {
            color: #c3ccc9;
        }

        #attendanceDetailPage .ar-detail-note {
            color: #6a7a77;
            display: block;
            font-size: 12px;
            font-style: italic;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-detail-status {
            align-items: center;
            background: #e4f1ee;
            border: 1px solid #c4e2da;
            border-radius: 8px;
            color: var(--ar-green);
            display: inline-flex;
            font-size: 11.5px;
            font-weight: 700;
            gap: 6px;
            padding: 3px 10px;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-detail-status::before {
            background: currentColor;
            border-radius: 999px;
            content: "";
            height: 6px;
            width: 6px;
        }

        #attendanceDetailPage .ar-detail-pill {
            align-items: center;
            background: transparent;
            border: 0;
            border-radius: 0;
            display: inline-flex;
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 12.5px;
            font-weight: 700;
            justify-content: center;
            line-height: 1;
            min-height: auto;
            min-width: 0;
            padding: 0;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-detail-pill--contracted {
            color: #7d6b3b;
        }

        #attendanceDetailPage .ar-detail-pill--rate {
            color: #315f87;
        }

        #attendanceDetailPage .ar-detail-pill--work {
            color: #0d7c73;
        }

        #attendanceDetailPage .ar-detail-pill--holiday {
            color: #a1802f;
        }

        #attendanceDetailPage .ar-detail-pill--pay {
            color: #6f4798;
        }

        #attendanceDetailPage .ar-detail-row.nwRow {
            background: #fafafa;
        }

        #attendanceDetailPage .nwRow .ar-detail-date,
        #attendanceDetailPage .nwRow .ar-detail-mono {
            color: #93a09d;
        }

        #attendanceDetailPage .nwRow .ar-detail-status {
            background: #f4f5f4;
            border-color: #e6e8e3;
            color: #9aa8a5;
        }

        #attendanceDetailPage .ar-detail-row.hvRow {
            background: #e8f1f9;
            border-left-color: #2f6fb0;
        }

        #attendanceDetailPage .hvRow .ar-detail-status {
            background: #e8f1f9;
            border-color: #c5ddf0;
            color: #2f6fb0;
        }

        #attendanceDetailPage .ar-detail-row.mtRow {
            background: #f4e4e2;
            border-left-color: #7a1f14;
        }

        #attendanceDetailPage .mtRow .ar-detail-status {
            background: #f4e4e2;
            border-color: #e4c3bd;
            color: #7a1f14;
        }

        #attendanceDetailPage .ar-detail-row.slRow {
            background: #fbeceb;
            border-left-color: #c0392b;
        }

        #attendanceDetailPage .slRow .ar-detail-status {
            background: #fbeceb;
            border-color: #f2d4d0;
            color: #c0392b;
        }

        #attendanceDetailPage .ar-detail-row.auRow {
            background: #f0e9f7;
            border-left-color: #7a4fa3;
        }

        #attendanceDetailPage .auRow .ar-detail-status {
            background: #f0e9f7;
            border-color: #ddccec;
            color: #7a4fa3;
        }

        #attendanceDetailPage .ar-detail-row.apRow {
            background: #e5f4ea;
            border-left-color: #1f8a4c;
        }

        #attendanceDetailPage .apRow .ar-detail-status {
            background: #e5f4ea;
            border-color: #c4e5d0;
            color: #1f8a4c;
        }

        #attendanceDetailPage .ar-detail-row.bhRow {
            background: #f9efe1;
            border-left-color: #c77a2e;
        }

        #attendanceDetailPage .bhRow .ar-detail-status {
            background: #f9efe1;
            border-color: #eddcc0;
            color: #c77a2e;
        }

        #attendanceDetailPage .ar-detail-row.ovRow {
            background: #fbf0e4;
            border-left-color: #d17a33;
        }

        #attendanceDetailPage .ovRow .ar-detail-status {
            background: #fbf0e4;
            border-color: #f0dabf;
            color: #d17a33;
        }

        #attendanceDetailPage .ar-detail-row.hvRow .ar-detail-date,
        #attendanceDetailPage .ar-detail-row.mtRow .ar-detail-date,
        #attendanceDetailPage .ar-detail-row.slRow .ar-detail-date,
        #attendanceDetailPage .ar-detail-row.auRow .ar-detail-date,
        #attendanceDetailPage .ar-detail-row.apRow .ar-detail-date,
        #attendanceDetailPage .ar-detail-row.bhRow .ar-detail-date,
        #attendanceDetailPage .ar-detail-row.ovRow .ar-detail-date {
            font-weight: 700;
        }

        #attendanceDetailPage .ar-detail-expand {
            display: none;
            padding: 0 24px 12px;
        }

        #attendanceDetailPage .ar-detail-expand-inner {
            background: #fbfbf8;
            border: 1px solid #eee7d9;
            border-radius: 12px;
            padding: 12px;
        }

        #attendanceDetailPage .ar-detail-subgrid,
        #attendanceDetailPage .ar-detail-leave {
            display: grid;
            gap: 10px;
        }

        #attendanceDetailPage .ar-detail-subgrid {
            grid-template-columns: 1.2fr 1.2fr .75fr .85fr .75fr;
        }

        #attendanceDetailPage .ar-detail-leave {
            grid-template-columns: 1fr .35fr;
        }

        #attendanceDetailPage .ar-detail-subgrid > span,
        #attendanceDetailPage .ar-detail-leave > span {
            background: #fff;
            border: 1px solid #eee7d9;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
            padding: 10px 12px;
        }

        #attendanceDetailPage .ar-detail-subgrid strong,
        #attendanceDetailPage .ar-detail-leave strong {
            color: #9aa8a5;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        #attendanceDetailPage .ar-detail-subgrid em,
        #attendanceDetailPage .ar-detail-leave em {
            color: #12312e;
            font-style: normal;
            font-weight: 700;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-detail-subgrid small {
            color: #8b9995;
            font-size: 11px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-detail-total {
            background: #f4f8f6;
            border-top: 2px solid #dcebe5;
            color: #0f2d2a;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .05em;
            min-height: 56px;
            padding-bottom: 16px;
            padding-top: 16px;
            text-transform: uppercase;
        }

        #attendanceDetailPage .ar-detail-total .ar-detail-strong,
        #attendanceDetailPage .ar-detail-total .ar-detail-warn {
            font-size: 13px;
        }

        #attendanceDetailPage .ar-detail-total .ar-detail-gross {
            color: var(--ar-green);
            font-family: "IBM Plex Mono", ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 13.5px;
            text-align: right;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-bottom-grid {
            align-items: start;
            display: grid;
            gap: 20px;
            grid-template-columns: 1.6fr 1fr;
            margin-top: 20px;
        }

        #attendanceDetailPage .ar-section-title {
            border-bottom: 1px solid #f0ede3;
            color: #0f2d2a;
            font-family: "Newsreader", "IBM Plex Serif", Georgia, serif;
            font-size: 16px;
            font-weight: 600;
            padding: 15px 22px;
        }

        #attendanceDetailPage .ar-summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
        }

        #attendanceDetailPage .ar-summary-item {
            align-items: center;
            border-bottom: 1px solid #f3f4f0;
            display: flex;
            gap: 12px;
            justify-content: space-between;
            padding: 14px 22px;
        }

        #attendanceDetailPage .ar-summary-item:nth-child(odd) {
            border-right: 1px solid #f3f4f0;
        }

        #attendanceDetailPage .ar-summary-label {
            align-items: center;
            color: #5a6f6c;
            display: inline-flex;
            font-size: 13px;
            gap: 9px;
        }

        #attendanceDetailPage .ar-dot {
            background: var(--dot-color, var(--ar-green));
            border-radius: 3px;
            flex: 0 0 9px;
            height: 9px;
            width: 9px;
        }

        #attendanceDetailPage .ar-summary-value {
            color: var(--value-color, #c3ccc9);
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
        }

        #attendanceDetailPage .ar-legend-list {
            display: flex;
            flex-wrap: wrap;
            gap: 9px;
            padding: 18px 22px;
        }

        #attendanceDetailPage .ar-legend-chip {
            align-items: center;
            background: var(--chip-bg, #e4f1ee);
            border: 1px solid var(--chip-border, #c4e2da);
            border-radius: 9px;
            color: var(--chip-color, var(--ar-green));
            display: inline-flex;
            font-size: 12px;
            font-weight: 700;
            gap: 7px;
            padding: 6px 12px;
        }

        #attendanceDetailPage .ar-legend-chip::before {
            background: currentColor;
            border-radius: 999px;
            content: "";
            height: 8px;
            width: 8px;
        }

        @media (max-width: 900px) {
            #attendanceDetailPage .ar-detail-hero,
            #attendanceDetailPage .ar-person {
                align-items: stretch;
                flex-direction: column;
            }

            #attendanceDetailPage .ar-back {
                justify-content: center;
                width: 100%;
            }

            #attendanceDetailPage .ar-bottom-grid,
            #attendanceDetailPage .ar-summary-grid {
                grid-template-columns: 1fr;
            }

            #attendanceDetailPage .ar-summary-item:nth-child(odd) {
                border-right: 0;
            }
        }
    </style>
@endsection

@section('subcontent')
    @php
        $employeeName = trim(($employee->first_name ?? '').' '.($employee->last_name ?? ''));
        $employeeName = $employeeName !== '' ? $employeeName : $employee->full_name;
        $cleanName = preg_replace('/^(Mr|Mrs|Ms|Miss|Dr)\.?\s+/i', '', $employeeName);
        $nameParts = preg_split('/\s+/', $cleanName, -1, PREG_SPLIT_NO_EMPTY);
        $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1).substr($nameParts[count($nameParts) - 1] ?? 'R', 0, 1));
        $jobTitle = $employee->employment->employeeJobTitle->name ?? 'Staff';
        $worksNumber = $employee->employment->works_number ?? ($employee->ni_number ?? $employee->id);
        $monthLabel = date('F Y', strtotime($date));
        $employeePhotoUrl = null;
        if(!empty($employee->photo) && \Illuminate\Support\Facades\Storage::disk('local')->exists('public/employees/'.$employee->id.'/'.$employee->photo)) {
            $employeePhotoUrl = \Illuminate\Support\Facades\Storage::disk('local')->url('public/employees/'.$employee->id.'/'.$employee->photo);
        }
        $formatDayCount = function($count) {
            return $count > 0 ? $count.($count > 1 ? ' Days' : ' Day') : '—';
        };
        $dayCount = $attendance['dayCount'] ?? [];
        $summaryItems = [
            ['label' => 'Working Days', 'value' => $formatDayCount($dayCount['wkday'] ?? 0), 'color' => '#0d7c73'],
            ['label' => 'Overtime', 'value' => $formatDayCount($dayCount['ovday'] ?? 0), 'color' => '#d17a33'],
            ['label' => 'Bank Holidays', 'value' => $formatDayCount($dayCount['bhday'] ?? 0), 'color' => '#c77a2e'],
            ['label' => 'Holiday / Vacation', 'value' => $formatDayCount($dayCount['hvday'] ?? 0), 'color' => '#2f6fb0'],
            ['label' => 'Unauthorised Absent', 'value' => $formatDayCount($dayCount['uaday'] ?? 0), 'color' => '#7a1f14'],
            ['label' => 'Sick Leave', 'value' => $formatDayCount($dayCount['skday'] ?? 0), 'color' => '#c0392b'],
            ['label' => 'Authorised Unpaid', 'value' => $formatDayCount($dayCount['auday'] ?? 0), 'color' => '#7a4fa3'],
            ['label' => 'Authorised Paid', 'value' => $formatDayCount($dayCount['apday'] ?? 0), 'color' => '#1f8a4c'],
        ];
        $legendItems = [
            ['label' => 'Holiday / Vacation', 'color' => '#2f6fb0', 'bg' => '#e8f1f9', 'border' => '#c5ddf0'],
            ['label' => 'Unauthorised Absent', 'color' => '#7a1f14', 'bg' => '#f4e4e2', 'border' => '#e4c3bd'],
            ['label' => 'Sick Leave', 'color' => '#c0392b', 'bg' => '#fbeceb', 'border' => '#f2d4d0'],
            ['label' => 'Authorise Unpaid', 'color' => '#7a4fa3', 'bg' => '#f0e9f7', 'border' => '#ddccec'],
            ['label' => 'Authorise Paid', 'color' => '#1f8a4c', 'bg' => '#e5f4ea', 'border' => '#c4e5d0'],
            ['label' => 'Bank Holiday', 'color' => '#c77a2e', 'bg' => '#f9efe1', 'border' => '#eddcc0'],
            ['label' => 'Overtime', 'color' => '#d17a33', 'bg' => '#fbf0e4', 'border' => '#f0dabf'],
        ];
    @endphp

    <div id="attendanceDetailPage">
        <section class="ar-card ar-detail-hero">
            <div class="ar-person">
                <span class="ar-avatar {{ $employeePhotoUrl ? 'has-photo' : '' }}">
                    @if($employeePhotoUrl)
                        <img src="{{ $employeePhotoUrl }}" alt="{{ $employeeName }}">
                    @else
                        {{ $initials }}
                    @endif
                </span>
                <div class="min-w-0">
                    <span class="ar-eyebrow">Employee Attendance &middot; Detail</span>
                    <h1 class="ar-title">{{ $employeeName }} <span class="text-[#93a09d]">&middot;</span> <span class="ar-title-mark">{{ $monthLabel }}</span></h1>
                    <div class="ar-subtitle">{{ $jobTitle }} &middot; <span>#{{ $worksNumber }}</span></div>
                </div>
            </div>
            <a href="{{ route('hr.portal.reports.attendance', date('m-Y', strtotime($date))) }}" class="ar-back">
                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                <span>Back to Report</span>
            </a>
        </section>

        <section class="ar-card">
            <div class="ar-detail-scroll">
                <div class="ar-detail-table" id="employeeAttendanceDetailsTable">
                    <div class="ar-detail-grid ar-detail-head">
                        <span>Date</span>
                        <span>Contracted</span>
                        <span>Status</span>
                        <span class="text-right">Rate</span>
                        <span class="text-right">Working Hr</span>
                        <span class="text-right">Holiday Hr</span>
                        <span class="text-right">Pay</span>
                        <span>Note</span>
                    </div>
                    {!! $attendance['html'] ?? '' !!}
                    <div class="ar-detail-grid ar-detail-total">
                        <span>Month Total</span>
                        <span></span>
                        <span></span>
                        <span></span>
                        <span class="text-right"><span class="ar-detail-pill ar-detail-pill--work">{{ $attendance['workingHourTotal'] ?? '00:00' }}</span></span>
                        <span class="text-right"><span class="ar-detail-pill ar-detail-pill--holiday">{{ $attendance['holidayHourTotal'] ?? '00:00' }}</span></span>
                        <span class="text-right"><span class="ar-detail-pill ar-detail-pill--pay">{{ $attendance['monthTotalPay'] ?? '£0.00' }}</span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </section>

        <div class="ar-bottom-grid">
            <section class="ar-card">
                <div class="ar-section-title">Month Summary</div>
                <div class="ar-summary-grid">
                    @foreach($summaryItems as $item)
                        <div class="ar-summary-item">
                            <span class="ar-summary-label"><span class="ar-dot" style="--dot-color: {{ $item['color'] }}"></span>{{ $item['label'] }}</span>
                            <span class="ar-summary-value" style="--value-color: {{ $item['value'] === '—' ? '#c3ccc9' : $item['color'] }}">{{ $item['value'] }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="ar-card">
                <div class="ar-section-title">Status Legend</div>
                <div class="ar-legend-list">
                    @foreach($legendItems as $item)
                        <span class="ar-legend-chip" style="--chip-color: {{ $item['color'] }}; --chip-bg: {{ $item['bg'] }}; --chip-border: {{ $item['border'] }}">{{ $item['label'] }}</span>
                    @endforeach
                </div>
            </section>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance-report-details.js')
@endsection
