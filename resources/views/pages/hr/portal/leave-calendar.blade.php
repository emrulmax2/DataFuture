@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('body_class', 'hr-leave-calendar-body')

@section('subcontent')
    @php
        $leaveLegend = [
            ['class' => 'holidayVacationBG', 'code' => 'H', 'label' => 'Holiday / Vacation'],
            ['class' => 'meetingTrainingBG', 'code' => 'A', 'label' => 'Unauthorised Absent'],
            ['class' => 'sickLeaveBG', 'code' => 'S', 'label' => 'Sick Leave'],
            ['class' => 'authoriseUnpaidBG', 'code' => 'U', 'label' => 'Authorise Unpaid'],
            ['class' => 'authorisedPaidBG', 'code' => 'P', 'label' => 'Authorise Paid'],
            ['class' => 'bankHolidayBG', 'code' => 'B', 'label' => 'Bank Holiday'],
        ];
    @endphp

    <div class="hr-leave-page">
        <div class="hr-leave-page__inner">
            <div class="hr-leave-title-card">
                <div>
                    <h1>Leave Calendar</h1>
                    <p>Monthly leave overview &middot; London Churchill College</p>
                </div>
                <a href="{{ route('hr.portal') }}" class="hr-leave-back-btn">
                    <i data-lucide="chevron-left"></i>
                    <span>Back to Portal</span>
                </a>
            </div>

            <form method="post" action="#" id="leaveCalendarFilterForm" class="hr-leave-filter-card">
                <label class="hr-leave-field hr-leave-field--department">
                    <span>Department</span>
                    <span class="hr-leave-select-wrap">
                        <select name="department" id="department" class="hr-leave-select">
                            <option value="">All Departments</option>
                            @if($department->count() > 0)
                                @foreach($department as $dpt)
                                    <option value="{{ $dpt->id }}">{{ $dpt->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <i data-lucide="chevron-down"></i>
                    </span>
                </label>

                <label class="hr-leave-field hr-leave-field--employee">
                    <span>Employee</span>
                    <span class="hr-leave-employee-select">
                        <select name="employee[]" multiple id="employee" class="tom-selects">
                            <option value="">Please Select</option>
                            @if($employees->count() > 0)
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </span>
                </label>

                <label class="hr-leave-field hr-leave-field--month">
                    <span>Month</span>
                    <span class="hr-leave-select-wrap">
                        <select name="month" id="month" class="hr-leave-select">
                            @php
                                for($i = 1; $i <= 12; $i++):
                                    $y = date('Y');
                                    $cm = date('m');
                                    $m = date('F', strtotime($y.'-'.$i.'-1'));
                                    if($cm == $i):
                                        echo '<option selected value="'.$i.'">'.$m.'</option>';
                                    else:
                                        echo '<option value="'.$i.'">'.$m.'</option>';
                                    endif;
                                endfor;
                            @endphp
                        </select>
                        <i data-lucide="chevron-down"></i>
                    </span>
                </label>

                <label class="hr-leave-field hr-leave-field--year">
                    <span>Year</span>
                    <span class="hr-leave-select-wrap">
                        <select name="year" id="year" class="hr-leave-select">
                            @php
                                for($i = 2015; $i <= date('Y'); $i++):
                                    $y = date('Y');
                                    if($y == $i):
                                        echo '<option selected value="'.$i.'">'.$i.'</option>';
                                    else:
                                        echo '<option value="'.$i.'">'.$i.'</option>';
                                    endif;
                                endfor;
                            @endphp
                        </select>
                        <i data-lucide="chevron-down"></i>
                    </span>
                </label>

                <div class="hr-leave-filter-actions pt-4">
                    <button type="button" id="leave-calendar-prev" data-value="prev" data-date="{{ date('Y-m-d') }}" class="leaveCalendarActionBtn hr-leave-month-btn hr-leave-month-btn--ghost">
                        <i data-lucide="chevron-left"></i>
                        <span>Prev Month</span>
                    </button>
                    <button type="button" id="leave-calendar-next" data-value="next" data-date="{{ date('Y-m-d') }}" class="leaveCalendarActionBtn hr-leave-month-btn hr-leave-month-btn--primary">
                        <span>Next Month</span>
                        <i data-lucide="chevron-right"></i>
                    </button>
                </div>
            </form>

            <div class="hr-leave-calendar-card">
                <div class="hr-leave-calendar-card__head">
                    <div class="hr-leave-calendar-summary">
                        <span class="hr-leave-calendar-summary__icon">
                            <i data-lucide="calendar-days"></i>
                        </span>
                        <div>
                            <h2 id="leaveCalendarMonthLabel">{{ $calendarMeta['monthLabel'] ?? date('F Y') }}</h2>
                            <p>
                                <span id="leaveCalendarVisibleCount">{{ $calendarMeta['visibleCount'] ?? $employees->count() }}</span> staff shown
                                &middot;
                                <strong><span id="leaveCalendarOnLeaveToday">{{ $calendarMeta['onLeaveToday'] ?? 0 }}</span> on leave today</strong>
                            </p>
                        </div>
                    </div>

                    <div class="hr-leave-legend">
                        @foreach($leaveLegend as $item)
                            <span class="hr-leave-legend__item">
                                <span class="hr-leave-legend__code {{ $item['class'] }}">{{ $item['code'] }}</span>
                                {{ $item['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="leaveCalendarWrap hr-leave-calendar-scroll"
                    data-calendar-date="{{ date('Y-m-d') }}"
                    data-calendar-next-offset="{{ $calendarMeta['nextOffset'] ?? 30 }}"
                    data-calendar-has-more="{{ !empty($calendarMeta['hasMore']) ? '1' : '0' }}">
                    <div class="leaveTableLoader">
                        <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="rgb(255, 255, 255)" class="w-10 h-10 text-danger">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </div>
                    <table class="leaveCalendarTable">
                        {!! $calendarHtml !!}
                    </table>
                    <div class="hr-leave-more-loader" data-leave-more-loader aria-hidden="true">
                        <span class="hr-leave-more-loader__spinner"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Edit New Request Modal -->
    <div id="viewLeaveModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto modal-titles uppercase">Leave Details</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="leaveDetailsModalLoader">
                        <div class=" flex justify-center items-center px-10 py-10">
                            <i data-loading-icon="oval" class="w-10 h-10"></i>
                        </div>
                    </div>
                    <div class="leaveDetailsModalContent" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Edit New Request Modal -->
@endsection

@section('script')
    @vite('resources/js/leave-calendar.js')
@endsection
