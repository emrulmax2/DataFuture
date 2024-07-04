@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Holiday Hour Reports</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Report</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form method="POST" action="{{ route('hr.portal.reports.holiday.hour') }}">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">From <span class="text-danger">*</span></label>
                    <input readonly required type="text" id="from_date" name="from_date" placeholder="DD-MM-YYYY" value="{{ (!empty($from_date) ? date('d-m-Y', strtotime($from_date)) : '') }}" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">To</label>
                    <input readonly required type="text" id="to_date" name="to_date" placeholder="DD-MM-YYYY" value="{{ (!empty($to_date) ? date('d-m-Y', strtotime($to_date)) : '') }}" class="w-full form-control"/>                    
                </div>
                <div class="col-span-12 sm:col-span-6 xl:col-span-8 text-right mt-7">
                    <input type="hidden" name="action" value="search"/>
                    <button onclick="this.form.action.value = this.value" value="search" type="submit" class="btn btn-primary text-white w-auto mt-2 sm:mt-0 sm:ml-1" >Search</button>
                    @if($searched)
                        <a href="{{ route('hr.portal.reports.holiday.hour.export', [(!empty($from_date) ? strtotime($from_date) : strtotime(date('Y-m-d'))), (!empty($to_date) ? strtotime($to_date) : '')]) }}" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" >Download Excel</a>
                    @endif
                </div>
            </div>
        </form>
        
        <div class="overflow-x-auto scrollbar-hidden mt-5" style="display: none;">
            <div id="employeeHOurCalcTable" class="table-report table-report--tabulator"></div>
        </div>

        @if($searched && !empty($search_result))
        <div class="overflow-x-auto mt-5">
            {!! $search_result !!}
        </div>
        @endif
    </div>
@endsection

@section('script')
    @vite('resources/js/holiday-hour-report.js')
@endsection