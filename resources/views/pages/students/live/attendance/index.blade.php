@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    @php
        // Overall attendance colour band (>=70 good, 40-69 mid, <40 low)
        $finalBand = ($finalAverage >= 70) ? 'atn-good' : (($finalAverage >= 40) ? 'atn-mid' : 'atn-low');
        // Overall code distribution grouped into Present / Online / Late+Excused / Absent
        $cd = is_array($codeDistribution) ? $codeDistribution : [];
        $cP = (int) ($cd['P'] ?? 0);
        $cO = (int) ($cd['O'] ?? 0);
        $cA = (int) ($cd['A'] ?? 0);
        $cLate = (int) ($cd['L'] ?? 0) + (int) ($cd['E'] ?? 0) + (int) ($cd['LE'] ?? 0);
        $barTotal = $cP + $cO + $cLate + $cA;
        $segP = $barTotal > 0 ? round($cP / $barTotal * 100, 2) : 0;
        $segO = $barTotal > 0 ? round($cO / $barTotal * 100, 2) : 0;
        $segLate = $barTotal > 0 ? round($cLate / $barTotal * 100, 2) : 0;
        $segA = $barTotal > 0 ? round($cA / $barTotal * 100, 2) : 0;
        $totalDays = is_array($totalClassFullSet) ? array_sum($totalClassFullSet) : 0;

        $hasTermAttendance = false;
        if(isset($termAttendanceFound)) {
            if(is_array($termAttendanceFound)) {
                foreach($termAttendanceFound as $v) { if($v === true || $v === 1 || $v === '1') { $hasTermAttendance = true; break; } }
            } else { $hasTermAttendance = (bool) $termAttendanceFound; }
        }
    @endphp

    <div class="student-profile-atn-wrap intro-y mt-5">

        <!-- ===== Overall attendance summary ===== -->
        <div class="atn-summary no-print">
            <div class="atn-summary-inner">
                <div class="atn-summary-head">
                    <div class="atn-summary-label">Overall Attendance</div>
                    <div class="atn-summary-pct {{ $finalBand }}">{{ $finalAverage }}%</div>
                </div>
                <div class="atn-summary-barwrap">
                    <div class="atn-bar-track">
                        @if($segP > 0)<span class="atn-bar-seg atn-seg-present" style="width: {{ $segP }}%" title="Present · {{ $cP }}"></span>@endif
                        @if($segO > 0)<span class="atn-bar-seg atn-seg-online" style="width: {{ $segO }}%" title="Online · {{ $cO }}"></span>@endif
                        @if($segLate > 0)<span class="atn-bar-seg atn-seg-late" style="width: {{ $segLate }}%" title="Late/Excused · {{ $cLate }}"></span>@endif
                        @if($segA > 0)<span class="atn-bar-seg atn-seg-absent" style="width: {{ $segA }}%" title="Absent · {{ $cA }}"></span>@endif
                    </div>
                    <div class="atn-legend">
                        <span class="atn-legend-item"><span class="atn-legend-dot" style="background:#0B6B66"></span>Present {{ $cP }}</span>
                        <span class="atn-legend-item"><span class="atn-legend-dot" style="background:#6FB5AE"></span>Online {{ $cO }}</span>
                        <span class="atn-legend-item"><span class="atn-legend-dot" style="background:#E3B8B3"></span>Absent {{ $cA }}</span>
                        <span class="atn-legend-item"><span class="atn-legend-dot" style="background:#C9992E"></span>Late/Excused {{ $cLate }}</span>
                        <span class="atn-legend-total">Total {{ $totalDays }} days of class</span>
                    </div>
                </div>
                <div class="atn-summary-actions">
                    @if(isset($dataSet) && count($dataSet)>0 && $hasTermAttendance)
                        <a href="{{ route('student.attendance.edit',$student->id) }}" class="atn-btn atn-btn-outline">
                            <i data-lucide="pencil" class="w-4 h-4"></i> Edit
                        </a>
                    @endif
                    <a id="print-all-btn" data-base="{{ route('student.attendance.print', $student->id) }}" href="{{ route('student.attendance.print',$student->id) }}" class="atn-btn atn-btn-dark">
                        <i data-lucide="printer" class="w-4 h-4"></i> Print All
                    </a>
                </div>
            </div>
        </div>

        <!-- ===== Term cards ===== -->
        @foreach($dataSet as $termId => $dataStartPoint)
            @php
                $isInactive = (isset($attendanceIndicator[$termId]) && $attendanceIndicator[$termId]===0);
                $termPct = isset($avarageTotalPercentage[$termId]) ? $avarageTotalPercentage[$termId] : 0;
                $termBand = ($termPct >= 70) ? 'atn-good' : (($termPct >= 40) ? 'atn-mid' : 'atn-low');
                $statsRaw = isset($totalFullSetFeedList[$termId]) ? trim($totalFullSetFeedList[$termId]) : '';
                $statsFmt = $statsRaw !== '' ? str_replace([' : ', ', '], [' ', ' · '], $statsRaw) : '';
            @endphp
            <div class="atn-term">
                <div class="atn-termhead {{ $isInactive ? 'is-inactive' : 'is-active' }}">
                    <div class="atn-term-title">{{ $term[$termId]["name"] }}</div>
                    <span class="atn-term-pct {{ $termBand }}">{{ $termPct }}%</span>
                    <span class="atn-term-stats">{{ $statsFmt }}{{ (isset($totalClassFullSet[$termId]) && $totalClassFullSet[$termId]!=0) ? ' — '.$totalClassFullSet[$termId].' days' : '' }}</span>
                    <div class="atn-term-meta">
                        <span class="atn-term-range">{{ date('j M', strtotime($term[$termId]["start_date"])) }} &ndash; {{ date('j M Y', strtotime($term[$termId]["end_date"])) }} &middot; Last attendance {{ (isset($lastAttendanceDate[$termId]) && !empty($lastAttendanceDate[$termId]) && $lastAttendanceDate[$termId]!="N/A") ? date('j M Y', strtotime($lastAttendanceDate[$termId])) : '---' }}</span>
                        <button data-term="{{ $termId }}" data-student="{{ $student->id }}" data-tw-toggle="modal" data-tw-target="#stdAtnTermStatusHistoryModal" class="sts_history_btn atn-term-icon no-print" title="Status history">
                            <i data-lucide="info" class="w-4 h-4"></i>
                        </button>
                        <a data-term="{{ $termId }}" data-base="{{ route('student.attendance.print', [$student->id, $termId]) }}" href="{{ route('student.attendance.print', [$student->id, $termId]) }}" class="single-print-btn atn-btn atn-btn-outline atn-btn-sm no-print">
                            <i data-lucide="printer" class="w-4 h-4"></i> Print
                        </a>
                    </div>
                </div>

                <div class="atn-cols">
                    <div>Module</div>
                    <div>Schedule</div>
                    <div>Group</div>
                    <div>Tutor</div>
                    <div class="ta-right">Attendance</div>
                </div>

                @php $hasRow = false; @endphp
                @foreach($dataStartPoint as $planId => $data)
                    @if(isset($planDetails[$termId][$planId]) && !empty($planDetails[$termId][$planId]))
                        @php
                            $hasRow = true;
                            if(isset($planDetails[$termId][$planId]->start_time) && isset($planDetails[$termId][$planId]->end_time)) {
                                $start_time = date('h:i A', strtotime(date("Y-m-d ".$planDetails[$termId][$planId]->start_time)));
                                $end_time = date('h:i A', strtotime(date("Y-m-d ".$planDetails[$termId][$planId]->end_time)));
                            } else { $start_time = "N/A"; $end_time = "N/A"; }

                            $planPct = isset($avarageDetails[$termId][$planId]) ? (float) $avarageDetails[$termId][$planId] : 0;
                            $planBand = ($planPct >= 70) ? 'good' : (($planPct >= 40) ? 'mid' : 'low');
                            $roomName = isset($planDetails[$termId][$planId]->room->name) ? $planDetails[$termId][$planId]->room->name : '';
                            $isOnline = (stripos($roomName, 'online') !== false);
                            if($ClassType[$planId] != "Tutorial") {
                                $tutorName = !empty($planDetails[$termId][$planId]->tutor->employee) ? $planDetails[$termId][$planId]->tutor->employee->full_name : "N/A";
                            } else {
                                $tutorName = !empty($planDetails[$termId][$planId]->personalTutor->employee) ? $planDetails[$termId][$planId]->personalTutor->employee->full_name : "N/A";
                            }
                        @endphp

                        <div class="atn-row tablepoint-toggle" id="tablepoint-{{ $termId }}-{{ $planId }}" data-term="{{ $termId }}" data-planid="{{ $planId }}">
                            <div class="atn-cell-module">
                                <span class="atn-toggle-icon">
                                    <i data-lucide="minus" class="plusminus minus-ic w-4 h-4 hidden"></i>
                                    <i data-lucide="plus" class="plusminus plus-ic w-4 h-4"></i>
                                </span>
                                <span>
                                    <span class="atn-mod-title">{{ $moduleNameList[$planId] }}</span>
                                    <span class="atn-mod-sub">
                                        <span class="atn-mod-code">[ {{ $planId }} ]</span>
                                        <span class="atn-mod-dot">&middot;</span>
                                        <span>{{ $ClassType[$planId] }}</span>
                                    </span>
                                </span>
                            </div>
                            <div class="atn-schedule">{{ $start_time }} &ndash; {{ $end_time }}</div>
                            <div class="atn-cell-group">
                                <span class="atn-group-badge">{{ isset($planDetails[$termId][$planId]->group->name) ? $planDetails[$termId][$planId]->group->name : '—' }}</span>
                                <span class="atn-room {{ $isOnline ? 'is-online' : '' }}">{{ $roomName !== '' ? $roomName : '—' }}</span>
                            </div>
                            <div class="atn-tutor">{{ $tutorName }}</div>
                            <div class="atn-att">
                                <span class="atn-att-pct atn-{{ $planBand }}">{{ $planPct }}%</span>
                                <span class="atn-att-bar"><span class="atn-bar-{{ $planBand }}" style="width: {{ max($planPct, 2) }}%"></span></span>
                            </div>
                        </div>

                        <div id="tabledata{{ $planDetails[$termId][$planId]->id }}" class="tabledataset atn-detail" style="display: none;">
                            <div class="atn-detail-card">
                                <div class="atn-detail-cols">
                                    <div>ID</div><div>Date</div><div>Time</div><div>Taken By</div><div>Code</div><div>Status</div>
                                </div>
                                @if(isset($data) && count($data)>0)
                                    @foreach($data as $planDateList)
                                        @if(isset($planDateList["attendance"]) && $planDateList["attendance"]!=null)
                                            @php
                                                $fcode = strtoupper(trim($planDateList["attendance"]->feed->code));
                                                $cc = in_array($fcode, ['P','O','H']) ? 'present' : ($fcode === 'A' ? 'absent' : 'late');
                                            @endphp
                                            <div class="atn-detail-row">
                                                <div class="atn-detail-id">
                                                    {{ $planDateList["attendance"]->id }}
                                                    @if(isset($planDateList["prev_plan_id"]))
                                                        <a href="javascript:;" data-theme="light" data-tooltip-content="#atn-tt-{{ $planDateList["attendance"]->id }}" data-trigger="click" class="tooltip" title="old group"><i data-lucide="info" class="w-3.5 h-3.5"></i></a>
                                                        <div class="tooltip-content">
                                                            <div id="atn-tt-{{ $planDateList["attendance"]->id }}" class="relative flex items-center py-1">
                                                                <span class="rounded btn-primary text-white font-medium inline-flex justify-center items-center px-3 py-0.5 ml-2">{{ $planDateList["prev_plan_id"]->group->name }}</span>
                                                                <span class="rounded text-slate-500 font-medium inline-flex justify-center items-center px-3 py-0.5 ml-2">[ {{ $planDateList["prev_plan_id"]->id }} ]</span>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="atn-detail-date">
                                                    @if(!empty($planDateList["attendance"]->note))
                                                        {{ date('d M, Y', strtotime($planDateList["attendance"]->attendance_date)) }} [ {{ $planDateList["attendance"]->note }} ]
                                                    @else
                                                        {{ date('d M, Y', strtotime($planDateList["date"])) }}
                                                    @endif
                                                </div>
                                                <div class="atn-detail-time">{{ $start_time }} &ndash; {{ $end_time }}</div>
                                                <div class="atn-detail-takenby">{{ !empty($planDateList["attendance_information"]->tutor->employee) ? $planDateList["attendance_information"]->tutor->employee->full_name : (!empty($planDateList["attendance"]->note) ? "N/A" : "Tutor Not Found") }}</div>
                                                <div><span class="atn-code-chip atn-c-{{ $cc }}">{{ $planDateList["attendance"]->feed->code }}</span></div>
                                                <div class="atn-status atn-c-{{ $cc }}">{{ $planDateList["attendance"]->feed->name }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                                <div class="atn-detail-total">
                                    <span class="atn-detail-total-label">Total</span>
                                    <span class="atn-detail-total-val">{{ isset($totalFeedList[$termId][$planId]) ? $totalFeedList[$termId][$planId] : '' }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach

                @if(!$hasRow)
                    <div class="atn-empty">No class schedule found for this term.</div>
                @endif
            </div>
        @endforeach

    </div>

    <!-- BEGIN: Term Status History Modal -->
    <div id="stdAtnTermStatusHistoryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Attendance Term Status History</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="stdAtnTermStatusHistoryTable" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Term Status History Modal -->

@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-attendance-term-status.js')
    <script type="module">
        (function () {
            const expandedGlobal = new Set();
            const expandedByTerm = new Map();
            const buildUrl = (base, params) => {
                const query = params.toString();
                if (!query) { return base; }
                const joiner = base.includes('?') ? '&' : '?';
                return `${base}${joiner}${query}`;
            };
            const updatePrintLinks = () => {
                const globalParams = new URLSearchParams();
                expandedGlobal.forEach(id => globalParams.append('plan_ids[]', id));
                const $printAll = $('#print-all-btn');
                $printAll.attr('href', buildUrl($printAll.data('base'), globalParams));

                $('.single-print-btn').each(function() {
                    const termId = $(this).data('term');
                    const termParams = new URLSearchParams();
                    const termSet = expandedByTerm.get(termId);
                    termSet && termSet.forEach(id => termParams.append('plan_ids[]', id));
                    $(this).attr('href', buildUrl($(this).data('base'), termParams));
                });
            };

            $(document).on('click', '.atn-row', function(e) {
                e.preventDefault();
                const $row = $(this);
                const $detail = $row.next('.tabledataset');
                if (!$detail.length) { return; }

                const planId = $row.data('planid');
                const termId = $row.data('term');
                const isOpening = !$detail.is(':visible');

                $row.find('.plus-ic').toggleClass('hidden', isOpening);
                $row.find('.minus-ic').toggleClass('hidden', !isOpening);

                if (isOpening) {
                    expandedGlobal.add(planId);
                    if (!expandedByTerm.has(termId)) { expandedByTerm.set(termId, new Set()); }
                    expandedByTerm.get(termId).add(planId);
                } else {
                    expandedGlobal.delete(planId);
                    if (expandedByTerm.has(termId)) {
                        expandedByTerm.get(termId).delete(planId);
                        if (expandedByTerm.get(termId).size === 0) { expandedByTerm.delete(termId); }
                    }
                }
                updatePrintLinks();
                $detail.slideToggle(180);
            });

            // Keep the term-header action buttons from triggering a row toggle.
            $(document).on('click', '.atn-termhead a, .atn-termhead button', function(e) {
                e.stopPropagation();
            });
        })();
    </script>
@endsection
