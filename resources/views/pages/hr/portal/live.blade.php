@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Live Attendance of <u>{{ date('jS M, Y') }}</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Portal</a>
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