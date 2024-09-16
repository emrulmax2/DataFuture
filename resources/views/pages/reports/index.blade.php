@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">All Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.attendance.reports') }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Attendance Report" src="{{ asset('build/assets/images/report_icons/attendance-reports.png') }}">
                </a>
            </div>
        </div>
    </div>
@endsection