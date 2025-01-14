@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Outstanding Holiday Reports</u></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.employment.reports.show') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Report</a>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <form method="POST" action="#" id="outstandingHolidayReportForm">
            @csrf
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">Holiday Year <span class="text-danger">*</span></label>
                    <select name="holiday_year_id" id="holiday_year_id" class="w-full tom-selects tomRequire">
                        <option value="">Please Select</option>
                        @if($holiday_years->count() > 0)
                            @foreach($holiday_years as $year)
                                <option data-start="{{ date('Y-m-d', strtotime($year->start_date)) }}" data-end="{{ date('Y-m-d', strtotime($year->end_date)) }}" value="{{ $year->id }}">{{ date('Y', strtotime($year->start_date)).' - '.date('Y', strtotime($year->end_date)) }}</option>
                            @endforeach
                        @endif
                    </select> 
                    <div class="acc__input-error error-holiday_year_id text-danger mt-2"></div>                
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">From <span class="text-danger">*</span></label>
                    <input readonly type="text" id="from_date" name="from_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control require"/>   
                    <div class="acc__input-error error-from_date text-danger mt-2"></div>                 
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">To <span class="text-danger">*</span></label>
                    <input readonly type="text" id="to_date" name="to_date" placeholder="DD-MM-YYYY" value="" class="w-full form-control require"/>  
                    <div class="acc__input-error error-to_date text-danger mt-2"></div>                  
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-2">
                    <label class="form-label">Status</label>
                    <select name="status" id="status" class="w-full form-control">
                        <option value="2">All</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>                 
                </div>
                <div class="col-span-12 sm:col-span-3 xl:col-span-4 text-right mt-7">
                    <input type="hidden" name="action" value="search"/>
                    <button onclick="this.form.action.value = this.value" value="search" type="submit" id="oshSearchBtn" class="btn btn-primary text-white w-auto mt-2 sm:mt-0 sm:ml-1" >
                        Search 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                    <button onclick="this.form.action.value = this.value" value="export" type="submit" id="oshExportBtn" class="btn btn-success text-white w-auto mt-2 sm:mt-0 sm:ml-1" style="display: none;" >
                        Download Excel 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                            to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                    <button id="oshResetBtn" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </div>
        </form>
        
        <div class="overflow-x-auto scrollbar-hidden mt-5 outStandingHolidayReportWrap" style="display: none;">
            <div id="outStandingHolidayReportTable" class="table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/outstanding-holiday-report.js')
@endsection