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
                <div class="mt-2 xl:mt-0">
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
@endsection

@section('script')
    @vite('resources/js/hr-attedance.js')
@endsection