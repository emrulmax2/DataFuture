@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

@include('pages.employee.profile.partials.cover-header')

@include('pages.employee.profile.partials.side-tabs')

<div class="ep-grid">
    <div class="ep-col">

    <!-- BEGIN: Profile Info -->
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Time Keeping</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button type="button" class="btn btn-primary shadow-md mr-2"><i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Save All</button>
            </div>
        </div>
        <div class="intro-y mt-5">
            @if(!empty($empAttendances))
                <div id="employeeTKYear" class="lcc_custom_accordion">
                    @foreach($empAttendances as $year_id => $year)
                        <div class="lcc_accordion_item mb-1">
                            <button class="lcc_accordion_button relative w-full text-lg font-semibold bg-slate-100 p-5 text-left" type="button" data-target="#employeeTKYear_{{ $year_id }}">
                                Year: {{ date('Y', strtotime($year['start_date'])).' - '.date('y', strtotime($year['end_date'])) }}
                                <span class="accordionCollaps"></span>
                            </button>
                            <div id="employeeTKYear_{{ $year_id }}" class="lcc_accordion_body text-slate-600 dark:text-slate-500 leading-relaxed p-5" style="display: none;">
                                @if(!empty($year['month']))
                                    <div id="employeeMonthAttendances_{{ $year_id }}" class="employee_month_attendance_accordion">
                                        @foreach($year['month'] as $key => $month)
                                            <div class="lcc_month_accordion_item mb-1">
                                                <button data-year="{{ $year_id }}" data-employee="{{ $employee->id }}" data-date="{{ $month['start_date'] }}" class="lcc_month_accordion_button lccEmpTimeKeepingBtn relative w-full text-lg font-semibold bg-slate-100 p-5 text-left" type="button" data-target="#employeeTKMonth_{{ $year_id }}_{{ $key }}">
                                                    {{ date('F Y', strtotime($month['start_date'])) }}
                                                    <span class="accordionCollaps"></span>
                                                </button>
                                                <div id="employeeTKMonth_{{ $year_id }}_{{ $key }}" class="lcc_month_accordion_body text-slate-600 dark:text-slate-500 leading-relaxed p-5" style="display: none;">
                                                    @if(!empty($month['attendances']))
                                                        <div class="ep-tk-card">
                                                            <div class="ep-tk-card__head">
                                                                <div class="ep-tk-card__titles">
                                                                    <div class="ep-tk-card__title">{{ date('F Y', strtotime($month['start_date'])) }}</div>
                                                                    <div class="ep-tk-card__sub">{{ $employee->full_name }} &middot; Time recorded</div>
                                                                </div>
                                                                <a href="{{ route('employee.time.keeper.download.pdf', [$employee->id, $month['start_date'], $year_id]) }}" class="ep-tk-pdf">
                                                                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v8H6v-8Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                                    <span>Download PDF</span>
                                                                </a>
                                                            </div>
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
                                                                            <th class="ep-tk-th ep-tk-th--num">Pay</th>
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

            @endif
        </div>
    </div>

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
