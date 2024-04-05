@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Add Attendance</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button type="submit" id="saveLiveAttendance" style="display: none;" class="btn text-white btn-success shadow-md  mr-1 w-auto">     
                Save  Attendance                    
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
            <a href="{{ route('hr.portal.live.attedance') }}" class="add_btn btn btn-primary shadow-md mr-0">Back To Live</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="intro-y box mt-5">
        <form method="post" action="#" id="attendanceLiveForm">
            <div class="p-5">
                <div class="grid grid-cols-12 gap-4 mb-7">
                    <div class="col-span-3">
                        <label for="liveAttendanceDate" class="form-label">Date <span class="text-danger">*</span></label>
                        <input id="liveAttendanceDate" name="the_date" value="{{ date('d-m-Y') }}" type="text" class="form-control w-full" placeholder="DD-MM-YYYY">
                    </div>
                    <div class="col-span-9">
                        <label for="employees" class="form-label">Employees <span class="text-danger">*</span></label>
                        <select id="employeeIDS" name="employees[]" class="w-full tom-selects" multiple>
                            @if(!empty($employee))
                                @foreach($employee as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden relative">
                    <table class="table table-bordered table-striped" id="addLiveAttendanceTable">
                        <thead>
                            <tr>
                                <th class="whitespace-nowrap">Name</th>
                                <th class="whitespace-nowrap">Cloc In</th>
                                <th class="whitespace-nowrap">Breaks</th>
                                <th class="whitespace-nowrap">Clock Out</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="noticeRow">
                                <td colspan="4">
                                    <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Please, Select employee to generate attendance data.
                                    </div>
                                </td>
                            </tr>
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
        </form>
    </div>
    <!-- END: Settings Page Content -->
    
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
                        <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    
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
                        <button type="button" data-action="NONE" class="warningCloser btn btn-danger w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

@endsection
@section('script')
    @vite('resources/js/hr-add-live-attendance.js')
@endsection