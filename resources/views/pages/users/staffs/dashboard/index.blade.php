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
                        <a href="{{ route('tutor-dashboard.show.new') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/Tutor-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['personal_tutor']) && auth()->user()->priv()['personal_tutor'] == 1)
                        <a href="{{ route('pt.dashboard') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
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
                        @if(!$work_history_lock && auth()->user()->remote_access && isset(auth()->user()->priv()['programme_dashboard']) && auth()->user()->priv()['programme_dashboard'] == 1)
                        <a href="{{ route('library.management.index') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/library.png') }}">
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
        @if(!empty($myPendingTask) || $proxyClasses->count() > 0 || $myfollowups > 0)
        <div class="col-span-12 2xl:col-span-3">
             <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-6">
                        <div class="grid grid-cols-12 gap-5 gap-y-0">
                            @if($proxyClasses->count() > 0)
                                <div class="col-span-12 mb-5">
                                    <div class="grid grid-cols-12 gap-5">
                                        @foreach($proxyClasses as $class)
                                            @php 
                                                $showClass = 0;
                                                if(in_array(auth()->user()->last_login_ip, $venue_ips)):
                                                    $listStart = date('Y-m-d').' '.$class->plan->start_time;
                                                    $listEnd = date('Y-m-d').' '.$class->plan->end_time;
                                                    $classStart = date('Y-m-d H:i:s', strtotime('-15 minutes', strtotime($listStart)));
                                                    $classEnd = date('Y-m-d H:i:s', strtotime($listEnd));
                                                    $currentTime = date('Y-m-d H:i:s');
                                                    if($currentTime >= $classStart && $currentTime <= $classEnd):
                                                        $showClass = 1;
                                                    elseif($currentTime < $classStart):
                                                        $showClass = 2;
                                                    endif;
                                                endif;
                                            @endphp
                                            <div class="col-span-12">
                                                <div class="box zoom-in bg-success-soft px-5 py-3">
                                                    <div class="flex items-start">
                                                        <div class="font-medium">
                                                            {{ $class->plan->creations->module_name }} 
                                                            ({{ $class->plan->group->name }})
                                                            {{ (isset($class->plan->class_type) && !empty($class->plan->class_type) ? ' - '.$class->plan->class_type : '') }}
                                                        </div>
                                                        <div class="text-xs text-slate-500 ml-auto text-right" style="flex: 0 0 70px">{{ (isset($class->plan->start_time) && !empty($class->plan->start_time) ? date('h:i A', strtotime($class->plan->start_time)) : '') }}</div>
                                                    </div>
                                                    <div class="flex justify-start items-center mt-3">
                                                        @if(isset($class->attendanceInformation->id) && $class->attendanceInformation->id > 0)
                                                            @if($class->feed_given == 1)
                                                                <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" href="{{ route('tutor-dashboard.attendance', [$class->proxy_tutor_id, $class->id, 3]) }}" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-1.5 px-2"><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>View Attendance</a>
                                                            @else
                                                                <a href="{{ route('tutor-dashboard.attendance', [$class->proxy_tutor_id, $class->id, 3]) }}"  data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" class="start-punch transition duration-200 btn btn-sm btn-success text-white py-1.5 px-2 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>Feed Attendance</a>
                                                            @endif
                                                            @if($class->feed_given == 1 && $class->attendanceInformation->end_time == null && $class->status == 'Ongoing')
                                                                <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" data-tw-toggle="modal" data-tw-target="#endClassModal" class="endClassBtn transition duration-200 btn btn-sm btn-danger text-white py-1.5 px-2 ml-1"><i data-lucide="x-circle" class="stroke-1.5 mr-2 h-4 w-4"></i>End Class</a>
                                                            @endif
                                                        @else
                                                            @if($showClass == 1)
                                                                <a data-tw-toggle="modal" data-id="{{ $class['id'] }}" data-tw-target="#startProxyClassModal" class="startClassBtn transition duration-200 btn btn-sm btn-primary text-white py-1.5 px-2">Start Class</a>
                                                            @elseif($showClass == 2)
                                                                <div class="alert alert-danger-soft show flex items-start px-2 py-2" role="alert">
                                                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Class Start Button appears 15 minutes before the scheduled time.
                                                                </div>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if(!$work_history_lock)
                                @foreach($myPendingTask as $process_id => $process)
                                    <div class="col-span-12 {{ !$loop->first && $process['outstanding_tasks'] > 0 ? 'border-t pt-5 mt-3' : '' }}">
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
                            
                            @if($myfollowups > 0)
                                <div class="col-span-12 mt-5">
                                    <div class="grid grid-cols-12 gap-5">
                                        <a href="{{ route('followups') }}" class="intro-y block relative col-span-6 2xl:col-span-4 mb-3">
                                            <h6 class="absolute text-sm w-full text-center mt-3 uppercase text-white font-medium z-10 px-2">Pending Followups</h6>
                                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/blan_logo.png') }}" alt="Pending Followups" />
                                            <span style="margin-top: -38px; left: {{ $myunreadcomments > 0 ? '-32px' : '0px' }};" class="absolute bg-warning rounded-full l-0 r-0 mr-auto ml-auto w-7 h-7 flex items-center justify-center text-sm font-medium text-white">{{ $myfollowups }}</span>
                                            @if($myunreadcomments > 0)
                                            <span style="margin-top: -38px; right: -32px;" class="absolute bg-danger rounded-full l-0 r-0 mr-auto ml-auto w-7 h-7 flex items-center justify-center text-sm font-medium text-white">{{ $myunreadcomments }}</span>
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- BEGIN: Class Start Modal Start -->
    <div id="startProxyClassModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="startProxyClassForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="p-5 pt-0 text-center">
                            <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                            <div class="text-3xl font-medium mt-3 px-2 confModTitle">Do you want to start the class now ?</div>
                        </div>
                        <div class="mt-2">
                            <textarea name="proxy_class_tutor_note" class="form-control w-full" placeholder="Note (Optional)" rows="3"></textarea>
                        </div>
                        <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-danger text-white w-auto mr-1">Not Now</button>
                        <button type="submit" id="startProxyBtn" class="btn btn-success text-white w-auto save">     
                            Start Class                    
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
                        <input type="hidden" value="{{ $user->employee->id }}" name="employee_id"/>
                        <input type="hidden" name="user_id" value="{{ $user->id }}" />
                        <input type="hidden" name="type" value="3" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Class Start Modal End -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="endClassModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="endClassModalForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-body p-0">
                        <div class="p-5 text-center">
                            <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                            <div class="text-3xl mt-5 confModTitle">End Now?</div>
                            <div class="text-slate-500 mt-2 mb-2 confModDesc">Do you want to end this class?</div>
                        </div>
                        <div class="px-5 pb-8 text-center">
                            <input class="plan_date_list_id" type="hidden" name="plan_date_list_id" value="0">
                            <input class="attendance_information_id" type="hidden" name="attendance_information_id" value="0">

                            <button type="submit" id="endClassBtn" class="btn btn-danger w-auto">
                                Yes, I do
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
                </div>
            </form>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

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
    <!-- END: Warning Modal Content Data-->
    @if(!auth()->user()->isImpersonated())
        @if($work_history_lock && $work_history_lock_no > 0 && (Session::has('work_history_lock_first_time') == null || Session::get('work_history_lock_first_time') != 1) && ((!in_array(auth()->user()->last_login_ip, $venue_ips) && isset($home_work) && $home_work) || (in_array(auth()->user()->last_login_ip, $venue_ips) && isset($desktop_login) && $desktop_login)))
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
                            <button type="button" class="disagreeWith actionBtn btn btn-danger text-white w-40 mr-1">No</button>
                            <button type="button" data-value="{{$work_history_lock_no}}" class="agreeWith actionBtn btn btn-success text-white w-48 h-20">Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Confirm Modal Content -->
        @endif
    @endif
    @if (session('verifySuccessMessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Success!</div>
                <div class="text-slate-500 mt-1">{{ session('verifySuccessMessage') }}</div>
            </div>
        </div>
        <!-- END: Notification Content -->
        <!-- BEGIN: Notification Toggle -->
        <button id="success-notification-toggle" class="btn hidden btn-primary">Show Notification</button>
        <!-- END: Notification Toggle -->
    @endif

    
@endsection

@section('script')
    @vite('resources/js/jquery-stopwatch.js')
    @vite('resources/js/staff-dashboard.js')
@endsection