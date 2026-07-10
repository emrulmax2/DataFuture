@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

@include('pages.employee.profile.partials.cover-header')

@include('pages.employee.profile.partials.side-tabs')

<div class="ep-grid ep-tk-page">
    <div class="ep-col">

    <section class="ep-tk-shell" data-screen-label="Time Keeping">
        <div class="ep-tk-shell__head">
            <div class="ep-tk-icon ep-tk-icon--lg">
                <i data-lucide="clock" class="w-5 h-5"></i>
            </div>
            <div class="ep-tk-shell__copy">
                <h2>Time Keeping</h2>
                <p>Clock records, contracted vs worked hours and pay</p>
            </div>
            <button type="button" class="ep-tk-save">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save All</span>
            </button>
        </div>

        <div class="ep-tk-shell__body">
            @if(!empty($empAttendances))
                <div id="employeeTKYear" class="lcc_custom_accordion ep-tk-years">
                    @foreach($empAttendances as $year_id => $year)
                        @php
                            $isFirstYear = $loop->first;
                            $monthCount = isset($year['month']) && !empty($year['month']) ? count($year['month']) : 0;
                        @endphp
                        <div class="lcc_accordion_item ep-tk-year-card">
                            <button class="lcc_accordion_button ep-tk-year-toggle {{ $isFirstYear ? 'active' : '' }}" type="button" data-target="#employeeTKYear_{{ $year_id }}">
                                <span class="ep-tk-icon ep-tk-icon--sm">
                                    <i data-lucide="calendar-days" class="w-4 h-4"></i>
                                </span>
                                <span class="ep-tk-year-toggle__title">Year: {{ date('Y', strtotime($year['start_date'])).' - '.date('y', strtotime($year['end_date'])) }}</span>
                                <span class="ep-tk-year-toggle__meta">{{ $monthCount }} {{ \Illuminate\Support\Str::plural('month', $monthCount) }} recorded</span>
                                <span class="ep-tk-toggle__mark" aria-hidden="true"></span>
                            </button>
                            <div id="employeeTKYear_{{ $year_id }}" class="lcc_accordion_body ep-tk-year-body" style="{{ $isFirstYear ? '' : 'display: none;' }}">
                                @if(!empty($year['month']))
                                    <div id="employeeMonthAttendances_{{ $year_id }}" class="employee_month_attendance_accordion">
                                        @foreach($year['month'] as $key => $month)
                                            @php($isInitialMonth = $isFirstYear && $loop->first)
                                            <div class="lcc_month_accordion_item ep-tk-month-card">
                                                <button data-year="{{ $year_id }}" data-employee="{{ $employee->id }}" data-date="{{ $month['start_date'] }}" class="lcc_month_accordion_button lccEmpTimeKeepingBtn ep-tk-month-toggle {{ $isInitialMonth ? 'active' : '' }}" type="button" data-target="#employeeTKMonth_{{ $year_id }}_{{ $key }}">
                                                    <span class="ep-tk-icon ep-tk-icon--xs">
                                                        <i data-lucide="calendar" class="w-4 h-4"></i>
                                                    </span>
                                                    <span class="ep-tk-month-toggle__title">{{ date('F Y', strtotime($month['start_date'])) }}</span>
                                                    <span class="ep-tk-month-toggle__badge js-month-worked-total">{{ $isInitialMonth ? 'Loading...' : 'Time recorded' }}</span>
                                                    <span class="ep-tk-toggle__mark" aria-hidden="true"></span>
                                                </button>
                                                <div id="employeeTKMonth_{{ $year_id }}_{{ $key }}" class="lcc_month_accordion_body ep-tk-month-body" style="{{ $isInitialMonth ? '' : 'display: none;' }}">
                                                    @if(!empty($month['attendances']))
                                                        <div class="ep-tk-card">
                                                            <div class="ep-tk-card__head">
                                                                <div class="ep-tk-card__title-wrap">
                                                                    <span class="ep-tk-icon ep-tk-icon--lg">
                                                                        <i data-lucide="calendar-days" class="w-5 h-5"></i>
                                                                    </span>
                                                                    <div class="ep-tk-card__titles">
                                                                        <div class="ep-tk-card__title">{{ date('F Y', strtotime($month['start_date'])) }}</div>
                                                                        <div class="ep-tk-card__sub">{{ $employee->full_name }} &middot; Time recorded</div>
                                                                    </div>
                                                                </div>
                                                                <a href="{{ route('employee.time.keeper.download.pdf', [$employee->id, $month['start_date'], $year_id]) }}" class="ep-tk-pdf">
                                                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                                    <span>Download PDF</span>
                                                                </a>
                                                            </div>
                                                            <div class="ep-tk-table-shell">
                                                                <table class="ep-tk-table">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="ep-tk-th">Date</th>
                                                                            <th class="ep-tk-th">Status</th>
                                                                            <th class="ep-tk-th">Clock in &rarr; out</th>
                                                                            <th class="ep-tk-th ep-tk-th--num">Break</th>
                                                                            <th class="ep-tk-th ep-tk-th--num">Contracted</th>
                                                                            <th class="ep-tk-th ep-tk-th--num">Worked</th>
                                                                            <th class="ep-tk-th ep-tk-th--num">Holiday</th>
                                                                            <th class="ep-tk-th ep-tk-th--pay">Pay</th>
                                                                            <th class="ep-tk-th">Notes</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr class="ep-tk-loading">
                                                                            <td class="ep-tk-td"><span class="ep-tk-spinner"></span> Loading timesheet&hellip;</td>
                                                                        </tr>
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr class="ep-tk-foot">
                                                                            <td class="ep-tk-foot__title">{{ date('F Y', strtotime($month['start_date'])) }} totals</td>
                                                                            <td class="ep-tk-foot__stat"><span class="ep-tk-foot__k">Worked</span><span class="ep-tk-foot__v tfootTotalWorkingHour">00:00</span></td>
                                                                            <td class="ep-tk-foot__stat"><span class="ep-tk-foot__k">Holiday</span><span class="ep-tk-foot__v tfootTotalHolidayHour">00:00</span></td>
                                                                            <td class="ep-tk-foot__stat ep-tk-foot__stat--pay"><span class="ep-tk-foot__k">Gross pay</span><span class="ep-tk-foot__v tfootTotalPay">&pound;0.00</span></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else

                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="ep-tk-empty">
                    <div class="ep-tk-icon ep-tk-icon--lg">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3>No time records found</h3>
                        <p>Clock records will appear here once attendance has been recorded.</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
</div>
</div>
@endsection

@section('script')
    @vite('resources/js/employee-time-keeping.js')
@endsection
