@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Live Attendance of <span class="theDateHolder underline">{{ date('jS M, Y') }}</span></h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 mr-2">
                <i data-lucide="users" class="hidden sm:block w-4 h-4 mr-2"></i>
                <input type="text" placeholder="Search..." name="employee_name" class="w-full form-control border-0 liveAttendanceEmp" id="liveAttendanceEmp" value="" style="max-width: 150px;"/>
            </div>
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
            @if(isset(auth()->user()->priv()['add_attendance']) && auth()->user()->priv()['add_attendance'] == 1)
            <a href="{{ route('hr.portal.live.attedance.add') }}" class="btn btn-primary shadow-md mr-0">Add Attendance</a>
            @endif
            {{--<a href="{{ route('hr.portal') }}" class="add_btn btn btn-primary shadow-md mr-0">Back To Portal</a>--}}
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
                            <th class="whitespace-nowrap">&nbsp;</th>
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

    <!-- BEGIN: Send Mail Modal Modal -->
    <div id="senMailModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="senMailForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Send Email</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="to_email" class="form-label">To <span class="text-danger">*</span></label>
                            <input id="to_email" type="text" name="to_email" class="form-control w-full">
                            <div class="acc__input-error error-to_email text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="cc_email" class="form-label">CC</label>
                            <select id="cc_email" name="cc_email[]" class="w-full tom-selects" multiple>
                                @if($employees->count() > 0)
                                    @foreach($employees as $emp)
                                        @if(isset($emp->employment->email) && !empty($emp->employment->email)):
                                            <option value="{{ $emp->employment->email }}">{{ $emp->employment->email }}</option>
                                        @endif;
                                    @endforeach
                                @endif;
                            </select>
                        </div>
                        <div class="mt-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input id="subject" type="text" name="subject" class="form-control w-full">
                            <div class="acc__input-error error-subject text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 pt-2 pb-1">
                            <textarea name="mail_body" id="mailEditor"></textarea>
                            <div class="acc__input-error error-mail_body text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 flex justify-start items-center relative">
                            <label for="sendMailsDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Attachments
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" multiple name="documents[]" class="absolute w-0 h-0 overflow-hidden opacity-0" id="sendMailsDocument"/>
                        </div>
                        <div id="sendMailsDocumentNames" class="sendMailsDocumentNames mt-3" style="display: none"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="sentMailBtn" class="btn btn-primary w-auto">     
                            Sent Mail                      
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
                        <input type="hidden" name="employee_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add File Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
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

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="octagon-alert" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 sarningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
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
    @vite('resources/js/hr-live-attendance.js')
@endsection