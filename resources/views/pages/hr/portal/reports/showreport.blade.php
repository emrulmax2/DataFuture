@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To HR Portal</a>
        </div>
    </div>

    <div class="intro-y box p-5 mt-5">
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="employmentReportListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/hr-portal.js')              
    @vite('resources/js/hr-portal-employmentreport.js')
@endsection