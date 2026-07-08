@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <form id="attendance-update_all" method="post">
        <div id="attendance-editAll" class="student-profile-atn-wrap intro-y mt-5">
            @foreach($dataSet as $termId => $dataStartPoint)
                @php
                    $termPct = isset($avarageTotalPercentage[$termId]) ? $avarageTotalPercentage[$termId] : 0;
                    $termBand = ($termPct >= 70) ? 'atn-good' : (($termPct >= 40) ? 'atn-mid' : 'atn-low');
                    $statsRaw = isset($totalFullSetFeedList[$termId]) ? trim($totalFullSetFeedList[$termId]) : '';
                @endphp
                <div class="atn-term">
                    <div class="atn-edithead">
                        <div class="atn-term-title">{{ $term[$termId]["name"] }}</div>
                        <span class="atn-term-pct {{ $termBand }}">{{ $termPct }}%</span>
                        <span class="atn-term-stats">@if($statsRaw !== '')[ {{ $statsRaw }} ] &middot; @endif{{ isset($totalClassFullSet[$termId]) ? $totalClassFullSet[$termId] : 0 }} days class</span>
                        <div class="atn-term-meta">
                            <span class="atn-term-range">{{ date('j M', strtotime($term[$termId]["start_date"])) }} &ndash; {{ date('j M Y', strtotime($term[$termId]["end_date"])) }}</span>
                            <a href="{{ route('student.attendance',$student->id) }}" class="atn-btn atn-btn-outline atn-btn-sm">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to view
                            </a>
                            <button type="submit" class="update-all atn-btn atn-btn-dark atn-btn-sm">
                                <i data-lucide="save-all" class="w-4 h-4"></i> Update
                                <i data-loading-icon="oval" class="load-update w-4 h-4 hidden"></i>
                            </button>
                        </div>
                    </div>

                    @foreach($dataStartPoint as $planId => $data)
                        @php
                            $start_time = date('h:i A', strtotime(date("Y-m-d ".$planDetails[$termId][$planId]->start_time)));
                            $end_time = date('h:i A', strtotime(date("Y-m-d ".$planDetails[$termId][$planId]->end_time)));
                            $planPct = isset($avarageDetails[$termId][$planId]) ? $avarageDetails[$termId][$planId] : 0;
                            $planBand = ($planPct >= 70) ? 'atn-good' : (($planPct >= 40) ? 'atn-mid' : 'atn-low');
                            if($ClassType[$planId] != "Tutorial") {
                                $tutorName = !empty($planDetails[$termId][$planId]->tutor->employee) ? $planDetails[$termId][$planId]->tutor->employee->full_name : "N/A";
                            } else {
                                $tutorName = !empty($planDetails[$termId][$planId]->personalTutor->employee) ? $planDetails[$termId][$planId]->personalTutor->employee->full_name : "N/A";
                            }
                        @endphp
                        <div class="atn-edit-plan">
                            <div class="atn-edit-planhead">
                                <div id="tablepoint-{{ $termId }}-{{ $planId }}" class="tablepoint-toggle atn-edit-toggle table-collapsed cursor-pointer">
                                    <i data-lucide="minus" class="plusminus w-4 h-4"></i>
                                    <i data-lucide="plus" class="plusminus w-4 h-4 hidden"></i>
                                </div>
                                <div class="toggle-heading atn-edit-headinfo">
                                    <div class="atn-edit-modtitle">
                                        {{ $moduleNameList[$planId] }}
                                        <span class="atn-edit-time"><i data-lucide="clock" class="w-4 h-4"></i> {{ $start_time }} &ndash; {{ $end_time }}</span>
                                    </div>
                                    <div class="atn-edit-tutor"><i data-lucide="user" class="w-4 h-4"></i> {{ $tutorName }}</div>
                                </div>
                                <span class="atn-term-pct {{ $planBand }}">{{ $planPct }}%</span>
                            </div>

                            <div id="tabledata{{ $planDetails[$termId][$planId]->id }}" class="tabledataset atn-edit-tablewrap">
                                <div class="atn-edit-card">
                                    <table class="atn-edit-table">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                @foreach($attendanceFeedStatus as $status)
                                                    <th>{{ $status->code }}</th>
                                                @endforeach
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($data as $planDateList)
                                                @if($planDateList["attendance"]!=null)
                                                    @php $iCountColSpan = 0; @endphp
                                                    <tr>
                                                        <td>
                                                            {{ date('d M, Y', strtotime($planDateList["date"])) }}@if(!empty($planDateList["attendance"]->note)) [ {{ $planDateList["attendance"]->note }} ]@endif
                                                        </td>
                                                        <input name="id[]" value="{{ $planDateList["attendance"]->id }}" type="hidden" />
                                                        @foreach($attendanceFeedStatus as $status)
                                                            @php $iCountColSpan++; @endphp
                                                            @if($planDateList["attendance"]->feed->code == $status->code)
                                                                <td class="atn-edit-current {{ $planDateList["attendance"]->feed->attendance_count ? 'is-present' : 'is-absent' }}">
                                                                    {{ $planDateList["attendance"]->feed->code }} - {{ $planDateList["attendance"]->feed->name }}
                                                                </td>
                                                            @else
                                                                <td>
                                                                    <input {{ (!empty($planDateList["attendance"]->note)) ? 'disabled' : '' }} id="radio-switch-{{ $planDateList["attendance"]->id}}{{ $status->id }}" data-attendanceId="{{ $planDateList["attendance"]->id}}" name="attendance_feed[{{ $planDateList["attendance"]->id}}]" value="{{ $status->id }}" type="radio" />
                                                                    <label for="radio-switch-{{ $planDateList["attendance"]->id}}{{ $status->id }}">{{ $status->name }}</label>
                                                                </td>
                                                            @endif
                                                        @endforeach
                                                        <td class="atn-edit-action">
                                                            <span data-tw-target="#confirmModal" data-tw-toggle="modal" data-id="{{ $planDateList["attendance"]->id}}" class="delete_btn"><i data-lucide="trash-2" class="w-4 h-4"></i>Delete</span>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="1">Total</th>
                                                <th colspan="{{ isset($iCountColSpan) ? $iCountColSpan : 1 }}">{{ isset($totalFeedList[$termId][$planId]) ? $totalFeedList[$termId][$planId] : '' }}</th>
                                                <th>Total</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </form>

    <!-- BEGIN: Error Modal Content -->
    <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 errorModalTitle"></div>
                        <div class="text-slate-500 mt-2 errorModalDesc"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Error Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

@endsection

@section('script')
    @vite('resources/js/attendance-studentstaff.js')
@endsection
