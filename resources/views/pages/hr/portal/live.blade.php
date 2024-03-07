@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Live Attendance of <span class="theDateHolder underline">{{ date('jS M, Y') }}</span></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 mr-2">
                <i data-lucide="tags" class="hidden sm:block w-4 h-4 mr-2"></i>
                <select name="department" class="w-full form-control border-0 liveAttendanceDept" id="liveAttendanceDept">
                    <option value="">All Department</option>
                    @if($departments->count() > 0)
                        @foreach($departments as $dep)
                            <option value="{{ $dep->id }}">{{ $dep->name }}</option>
                        @endforeach
                    @endif
                </select>
            </div>
            <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 mr-2">
                <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i>
                <input type="text" name="class_date" class="w-full form-control border-0 liveAttendanceDate" id="liveAttendanceDate" value="{{ date('d-m-Y') }}" style="max-width: 110px;"/>
            </div>
            <a href="{{ route('hr.portal.leave.calendar') }}" class="add_btn btn btn-success text-white shadow-md mr-2">Planner</a>
            <a href="{{ route('hr.portal') }}" class="add_btn btn btn-primary shadow-md mr-0">Back To Portal</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="intro-y box mt-5">
        <div class="p-5">
            <div class="overflow-x-auto scrollbar-hidden relative">
                <table class="table table-striped" id="liveAttendanceTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap">Name</th>
                            <th class="whitespace-nowrap">&nbsp;</th>
                            <th class="whitespace-nowrap">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {!! $live !!}
                    </tbody>
                </table>

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
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

@endsection
@section('script')
    @vite('resources/js/hr-live-attendance.js')
@endsection