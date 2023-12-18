@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Live Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 mr-2">
                <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i>
                <input type="text" name="class_date" class="w-full form-control border-0 liveAttendanceDate" id="liveAttendanceDate" value="{{ date('d-m-Y') }}" style="max-width: 110px;"/>
            </div>
            <a href="{{ route('hr.portal.leave.calendar') }}" class="add_btn btn btn-success text-white shadow-md mr-2">Planner</a>
            <a href="{{ route('hr.portal') }}" class="add_btn btn btn-primary shadow-md mr-0">Back To Portal</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    @if($departments->count() > 0)
        @foreach($departments as $dep)
            <div class="intro-y box mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">{{ $dep->name }}</h2>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="liveAttendanceListTable_{{ $dep->id }}" data-department="{{ $dep->id }}" class="mt-5 liveAttendanceListTable table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        @endforeach
    @else

    @endif
    <!-- END: Settings Page Content -->

@endsection
@section('script')
    @vite('resources/js/hr-live-attendance.js')
@endsection