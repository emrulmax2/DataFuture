@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Attendance Report's Of <u>{{ date('F, Y', strtotime($theDate))}}</u></h2>
        {{--<div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.reports.attendance.generate') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Report</a>
        </div>--}}
    </div>
    <div class="intro-y box p-5 mt-5">
        <form id="attendanceReportForm" method="post" action="#">
            @csrf
            <div class="grid grid-cols-12 gap-4">
                {{--<div class="col-span-3">
                    <label class="form-label">Month <span class="text-danger">*</span></label>
                    <input readonly type="text" id="the_month" name="the_month" placeholder="MM-YYYY" value="{{ date('m-Y') }}" class="w-full form-control"/>                    
                </div>--}}
                <div class="col-span-3">
                    <label class="form-label">Filter by Employee</label>
                    <select id="employee_id" name="employee_id[]" class="w-full tom-selects" multiple>     
                        <option value="">All Employee</option>             
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>             
                        @endforeach
                    </select> 
                </div>
                <div class="col-span-9 text-right mt-7">
                    <input type="hidden" value="{{ date('Y-m-d', strtotime($theDate)) }}" id="the_date" name="the_date"/>
                    <a id="downloadExcel" href="{{ route('hr.portal.reports.attendance.export', date('Y-m-d', strtotime($theDate))) }}" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" >Download Excel</a>
                </div>
            </div>
        </form>
        <div class="overflow-x-auto scrollbar-hidden attendanceReportWrap mt-7" style="display: {{ (isset($reportHtml['html']) && !empty($reportHtml['html']) ? 'block;' : 'none;') }}">
            {!! (isset($reportHtml['html']) && !empty($reportHtml['html']) ? $reportHtml['html'] : '') !!}
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/attendance-report.js')
@endsection