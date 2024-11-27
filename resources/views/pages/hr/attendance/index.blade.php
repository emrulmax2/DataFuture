@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Monthly Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('hr.portal.leave.calendar') }}" class="btn btn-success text-white shadow-md mr-2">Planner</a>
            <a href="{{ route('hr.portal.live.attedance') }}" class="btn btn-primary shadow-md mr-0">Live Attendance</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="filterMonthAttenForm" class="xl:flex sm:mr-auto">
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                    <input id="queryDate" readonly data-org="{{ date('m-Y') }}" value="{{ date('m-Y') }}" name="queryDate" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="MM-YYYY">
                </div>
                <div class="mt-2 xl:mt-0 mr-auto">
                    <button type="submit" id="filterMonthAtten" class="btn btn-primary text-white w-auto syncroniseAttendance">
                        Go
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2">
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
                    <button type="button" id="generateReport" class="btn btn-success text-white w-auto ml-2">Generage Report</button>
                </div>
            </form>
            <div class="mt-2 flex w-full sm:mt-0 sm:w-auto  ml-2">
                <button id="uploadSync" data-tw-toggle="modal" data-tw-target="#synPaySlipModal" type="button" class="w-48 btn btn-pending text-white ml-auto"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Upload Payslips</button>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden mt-5 " id="attendanceSyncListTable">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap">Date</th>
                        <th class="whitespace-nowrap">Synchronise</th>
                        <th class="whitespace-nowrap">Issues</th>
                        <th class="whitespace-nowrap">Absents</th>
                        <th class="whitespace-nowrap">Overtime</th>
                        <th class="whitespace-nowrap">Pendings</th>
                        <th class="whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {!! $html_table !!}
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

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Add synPaySlipModal Modal -->
        <div id="synPaySlipModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ">
                
                    <div class="modal-content">
                        <div class="modal-body p-0"><a class="absolute right-0 top-0 mr-3 mt-3" data-tw-dismiss="modal" href="#">
                            <i data-tw-merge data-lucide="x" class="stroke-1.5  h-8 w-8 text-slate-400"></i>
                        </a>
                            <div class="p-5 text-center">
                                <i data-lucide="badge-pound-sterling" class="w-16 h-16 text-success mx-auto mt-3"></i>
                                <div class="text-3xl mt-5 ">Upload Payslips</div>
                                <div class="text-slate-500 mt-2 ">Please Upload payslips from below</div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <form method="post"  action="{{ route('hr.attendance.payslip.upload') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                                        @csrf    
                                        <div class="fallback">
                                            <input name="documents[]"  type="file" />
                                        </div>
                                        <div class="dz-message" data-dz-message>
                                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                                            <div class="text-slate-500">
                                                Upload zip files.
                                            </div>
                                        </div>
                                        <input type="hidden" name="dir_name" value=""/>
                                        <input type="hidden" name="type" value=""/>
                                    </form>
                                </div>
                                
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="holiday_year" name="holiday_year_id" class="lccTom lcc-tom-select w-full text-left">
                                        <option value="">Please Select Year</option>
                                        @foreach($holiday_years as $list)
                                            <option value="{{ $list->id }}">{{ date('Y', strtotime($list->start_date)).' - '.date('Y', strtotime($list->end_date)) }}</option>
                                        @endforeach
                                    </select> 
                                    <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="holiday_month" name="holiday_month" class="lccTom lcc-tom-select w-full  text-left">
                                        <option value="">Please Select Month</option>
                                       
                                    </select> 
                                    <div class="acc__input-error error-employee_work_type text-danger mt-2"></div>
                                </div>
                                <div class="intro-y intro-y w-90 mx-auto my-3">
                                    <select id="type" name="type" class="lccTom lcc-tom-select w-full  text-left">
                                        <option value="">Please Select Type</option>
                                        <option value="Payslip">Payslip</option>
                                        <option value="PGT">PGT</option>
                                    </select> 
                                    <div class="acc__input-error error-type text-danger mt-2"></div>
                                </div>
                            </div>
                            <div class="px-5 pb-8 text-center">
                                <button id="uploadEmpDocBtn" type="button" class="btn btn-success w-24 EmpSyncBtn">Save<svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 ml-2">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                    to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg></button>
                            </div>
                        </div>
                    </div>
                
            </div>
        </div>
    <!-- END: synPaySlipModal Modal -->
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-date="" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/hr-attedance.js')
    @vite('resources/js/hr-payslipsync.js')
@endsection