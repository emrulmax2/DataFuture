@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Employee Attendance Report's Detail</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to List</a>
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
                <table class="table table-bordered attendanceDetailsTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">Date</th>
                            <th class="whitespace-nowrap">Contracted Hour</th>
                            <th class="whitespace-nowrap">Worked Hour</th>
                        </tr>
                    </thead>
                    <tbody>
                        {!! $attendance !!}
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
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance-report.js')
@endsection