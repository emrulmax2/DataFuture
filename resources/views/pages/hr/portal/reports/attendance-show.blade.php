@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee Attendance Report's Detail</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.reports.attendance', date('m-Y', strtotime($date))) }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Report</a>
        </div>
    </div>
    <div class="intro-y box mt-5">
        <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
            <h2 class="font-medium text-base mr-auto">
                <strong class="uppercase underline">{{ $employee->full_name."'s" }}</strong> Attendance of <strong class="underline">{{ date('F Y', strtotime($date)) }}</strong>
            </h2>
            {{--<button data-tw-toggle="modal" data-tw-target="#addTaskModal" type="button" class="add_btn btn btn-primary shadow-md ml-auto">Add New Task</button>--}}
        </div>
        <div class="p-5">
            <div class="overflow-x-auto scrollbar-hidden">
                <table class="table table-bordered attendanceDetailsTable" id="employeeAttendanceDetailsTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">Date</th>
                            <th class="whitespace-nowrap">Contracted Hour</th>
                            <th class="whitespace-nowrap">Status</th>
                            <th class="whitespace-nowrap">Rate</th>
                            <th class="whitespace-nowrap">Working Hour</th>
                            <th class="whitespace-nowrap">Holiday Hour</th>
                            <th class="whitespace-nowrap">Pay</th>
                            <th class="whitespace-nowrap">Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        {!! (isset($attendance['html']) ? $attendance['html'] : '') !!}
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4">&nbsp;</th>
                            <th>{{ $attendance['workingHourTotal'] }}</th>
                            <th>{{ $attendance['holidayHourTotal'] }}</th>
                            <th>{{ $attendance['monthTotalPay'] }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
                <table class="table table-bordered table-sm mt-5">
                    <tbody>
                        <tr>
                            <th>Working Days</th>
                            <th>{{ (isset($attendance['dayCount']['wkday']) && $attendance['dayCount']['wkday'] > 0 ? $attendance['dayCount']['wkday'].($attendance['dayCount']['wkday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                            <th>Overtime</th>
                            <th>{{ (isset($attendance['dayCount']['ovday']) && $attendance['dayCount']['ovday'] > 0 ? $attendance['dayCount']['ovday'].($attendance['dayCount']['ovday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                        </tr>
                        <tr>
                            <th>Bank Holidays</th>
                            <th>{{ (isset($attendance['dayCount']['bhday']) && $attendance['dayCount']['bhday'] > 0 ? $attendance['dayCount']['bhday'].($attendance['dayCount']['bhday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                            <th>Holiday / Vacation</th>
                            <th>{{ (isset($attendance['dayCount']['hvday']) && $attendance['dayCount']['hvday'] > 0 ? $attendance['dayCount']['hvday'].($attendance['dayCount']['hvday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                        </tr>
                        <tr>
                            <th>Unauthorised Absent</th>
                            <th>{{ (isset($attendance['dayCount']['uaday']) && $attendance['dayCount']['uaday'] > 0 ? $attendance['dayCount']['uaday'].($attendance['dayCount']['uaday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                            <th>Sick</th>
                            <th>{{ (isset($attendance['dayCount']['skday']) && $attendance['dayCount']['skday'] > 0 ? $attendance['dayCount']['skday'].($attendance['dayCount']['skday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                        </tr>
                        <tr>
                            <th>Authorised Unpaid</th>
                            <th>{{ (isset($attendance['dayCount']['auday']) && $attendance['dayCount']['auday'] > 0 ? $attendance['dayCount']['auday'].($attendance['dayCount']['auday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                            <th>Authorised Paid</th>
                            <th>{{ (isset($attendance['dayCount']['apday']) && $attendance['dayCount']['apday'] > 0 ? $attendance['dayCount']['apday'].($attendance['dayCount']['apday'] > 1 ? ' Days' : ' Day') : '') }}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex justify-start items-center pt-5 labelsBtnsGroup">
                <span class="inline-flex px-3 py-1 font-medium holidayVacationBG mr-1">Holiday / Vacation</span>
                <span class="inline-flex px-3 py-1 font-medium meetingTrainingBG mr-1">Unauthorised Absent</span>
                <span class="inline-flex px-3 py-1 font-medium sickLeaveBG mr-1">Sick Leave</span>
                <span class="inline-flex px-3 py-1 font-medium authoriseUnpaidBG mr-1">Authorise Unpaid</span>
                <span class="inline-flex px-3 py-1 font-medium authorisedPaidBG mr-1">Authorise Paid</span>
                <span class="inline-flex px-3 py-1 font-medium bankHolidayBG mr-1">Bank Holiday</span>
                <span class="inline-flex px-3 py-1 font-medium overTimeBG mr-1">Overtime</span>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance-report-details.js')
@endsection