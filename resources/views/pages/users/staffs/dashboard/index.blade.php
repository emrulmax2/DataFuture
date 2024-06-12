@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - London Churchill College</title>
@endsection
@if(Auth::guard('applicant')->check())
  
@elseif(Auth::guard('student')->check())

@elseif(Auth::guard('agent')->check())

@else
    @php $employeeUser = cache()->get('employeeCache'.Auth::id()) ?? Auth::user()->load('employee'); @endphp
@endif

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 2xl:col-span-6"> 
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8">

                    <div class="grid grid-cols-12 gap-6">
                        
                        <a href="{{ route('user.account') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/MY-HR-logos.jpeg') }}">
                        </a>
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['applicant']) && auth()->user()->priv()['applicant'] == 1)
                        <a href="{{ route('admission') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y relative">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/APPLICANT-logos.jpeg') }}">
                            <span style="margin-top: -55px;border-radius: 0.25rem 0 0.25rem 0;padding: 2px 10px 0;" class="absolute bg-white b-0 r-0 text-center font-medium py-0 px-2 text-slate-500 w-auto">{{ $applicant }}</span>
                        </a>
                        @endif
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['live']) && auth()->user()->priv()['live'] == 1)
                        <a href="{{ route('student') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/STUDENTS-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['tutor_2']) && auth()->user()->priv()['tutor_2'] == 1)
                        <a href="{{ route('tutor-dashboard.show.new', 32) }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/Tutor-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['personal_tutor']) && auth()->user()->priv()['personal_tutor'] == 1)
                        <a href="{{ route('pt.dashboard',32) }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/personal_tutor-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
                        <a href="{{ route('hr.portal') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/Human-Resources-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['programme_dashboard']) && auth()->user()->priv()['programme_dashboard'] == 1)
                        <a href="{{ route('programme.dashboard') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/MANAGER-logos.jpeg') }}">
                        </a>
                        @endif

                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['access_account']) && auth()->user()->priv()['access_account'] == 1)
                        <a href="{{ route('accounts') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/ACCOUNT-logos.png') }}">
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-2">
                        @if(Auth::user() && (Route::currentRouteName() == 'dashboard' || Route::currentRouteName() == 'staff.dashboard') && (isset($home_work_history_btns) && !empty($home_work_history_btns)) && ((!in_array(auth()->user()->last_login_ip, $venue_ips) && isset($home_work) && $home_work) || (in_array(auth()->user()->last_login_ip, $venue_ips) && isset($desktop_login) && $desktop_login)))
                        <div class="intro-x mt-6 mb-6">
                            <div class="grid grid-cols-12 gap-5 logBtns">
                                {!! $home_work_history_btns !!}
                            </div>
                        </div>
                        @endif
                        @if(!$work_history_lock)
                        <div class="intro-x mt-6 mb-6">
                            <div class="grid grid-cols-12 gap-5">
                                {!! $internal_link_buttons !!}
                                @if(isset(auth()->user()->priv()['group_email']) && auth()->user()->priv()['group_email'] == 1)
                                <a href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#senGroupMailModal" class="block relative col-span-6 2xl:col-span-4 mb-3">
                                    <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/group_email.png') }}">
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div> 
        @if(!empty($myPendingTask))
        <div class="col-span-12 2xl:col-span-3">
             <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-6">
                        <div class="grid grid-cols-12 gap-5 gap-y-0">
                            @if(!$work_history_lock)
                                @foreach($myPendingTask as $process_id => $process)
                                    <div class="col-span-12 {{ !$loop->first ? 'border-t pt-5 mt-3' : '' }}">
                                        <div class="grid grid-cols-12 gap-5">
                                            @if($process['outstanding_tasks'] > 0)
                                                <a href="javascript:void(0);" class="block relative col-span-6 2xl:col-span-4 mb-3 processParents process_{{$process_id}}" data-process="{{$process_id}}">
                                                    @if(empty($process['image']))
                                                        <h6 class="absolute text-sm w-full text-center mt-3 uppercase text-white font-medium z-10 px-2">{{ $process['name'] }} </h6>
                                                    @endif
                                                    <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ (!empty($process['image']) ? $process['image_url'] : asset('build/assets/images/blan_logo.png')) }}" alt="{{ $process['name'] }}" />
                                                    <span style="margin-top: -38px;" class="absolute bg-warning rounded-full l-0 r-0 mr-auto ml-auto w-7 h-7 flex items-center justify-center text-sm font-medium text-white">{{ $process['outstanding_tasks'] }}</span>
                                                </a>
                                                @if(isset($process['tasks']) && !empty($process['tasks']))
                                                    @foreach($process['tasks'] as $task_id => $pts)
                                                        <a href="{{ route('task.manager.show', $task_id) }}" class="intro-y block relative col-span-6 2xl:col-span-4 mb-3 processTask process_{{$process_id}}_task" style="display: none;">
                                                            @if(empty($pts->image))
                                                                <h6 class="absolute text-sm w-full text-center mt-3 uppercase text-white font-medium z-10 px-2">{{ $pts->name }} </h6>
                                                            @endif
                                                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ (!empty($pts->image) ? $pts->image_url : asset('build/assets/images/blan_logo.png')) }}" alt="{{ $pts->name }}" />
                                                            <span style="margin-top: -38px;" class="absolute bg-warning rounded-full l-0 r-0 mr-auto ml-auto w-7 h-7 flex items-center justify-center text-sm font-medium text-white">{{ $pts->pending_task }}</span>
                                                        </a>
                                                    @endforeach
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
             </div>
            
        </div>
        @endif
    </div>

    <!-- BEGIN: Send Group Mail Modal -->
    <div id="senGroupMailModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="senGroupMailForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Send Email</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-x-4">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="department_ids" class="form-label">Department</label>
                                <select id="department_ids" name="department_ids[]" class="w-full tom-selects" multiple>
                                    @if($departments->count() > 0)
                                        @foreach($departments as $dpt)
                                            <option value="{{ $dpt->id }}">{{ $dpt->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-department_ids text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="groups_ids" class="form-label">Groups</label>
                                <select id="groups_ids" name="groups_ids[]" class="w-full tom-selects" multiple>
                                    @if($groups->count() > 0)
                                        @foreach($groups as $gr)
                                            <option value="{{ $gr->id }}">{{ $gr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-groups_ids text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label for="employee_ids" class="form-label">Members <span class="text-danger">*</span></label>
                            <select id="employee_ids" name="employee_ids[]" class="w-full tom-selects" multiple>
                                @if($employees->count() > 0)
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employee_ids text-danger mt-2"></div>
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
                        <div id="sendMailsDocumentNames" class="sendMailsDocumentNames mt-3" style="display: none;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="sentMailBtn" class="btn btn-primary w-auto">     
                            Send Mail                      
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
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Send Group Mail Modal -->

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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
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
                        <i data-lucide="octagon-alert" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    @if($work_history_lock && $work_history_lock_no > 0 && (Session::has('work_history_lock_first_time') == null || Session::get('work_history_lock_first_time') != 1))
    <!-- BEGIN: Confirm Modal Content -->
    <div id="attendanceHistoryLocModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5">{{ ($work_history_lock_no == 1 ? 'Oops!' : 'Hi '.Auth::user()->load('employee')->full_name) }}</div>
                        <div class="text-slate-500 mt-2">{{ ($work_history_lock_no == 1 ? 'Looks like you are not clocked in. Would you like to clock in now?' : 'It seems you\'re on break. Are you returning to work now?') }}</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="disagreeWith actionBtn btn btn-danger text-white w-20 mr-1">No</button>
                        <button type="button" data-value="{{$work_history_lock_no}}" class="agreeWith actionBtn btn btn-success text-white w-20">Yes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Confirm Modal Content -->
    @endif

    
@endsection

@section('script')
    @vite('resources/js/jquery-stopwatch.js')
    @vite('resources/js/staff-dashboard.js')
@endsection