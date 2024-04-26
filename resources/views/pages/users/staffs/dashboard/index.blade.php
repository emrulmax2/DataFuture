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
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['applicant']) && auth()->user()->priv()['applicant'] == 1)
                        <a href="{{ route('admission') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y relative">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/APPLICANT-logos.jpeg') }}">
                            <span style="margin-top: -55px;" class="absolute bg-white rounded-full l-0 r-0 mr-auto ml-auto w-10 text-center font-bold py-2 text-base text-slate-500">{{ $applicant }}</span>
                        </a>
                        @endif
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['live']) && auth()->user()->priv()['live'] == 1)
                        <a href="{{ route('student') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/STUDENTS-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['tutor_2']) && auth()->user()->priv()['tutor_2'] == 1)
                        <a href="{{ route('tutor-dashboard.show.new', 32) }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/Tutor-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['personal_tutor']) && auth()->user()->priv()['personal_tutor'] == 1)
                        <a href="{{ route('pt.dashboard',32) }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/personal_tutor-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
                        <a href="{{ route('hr.portal') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/Human-Resources-logos.jpeg') }}">
                        </a>
                        @endif
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['programme_dashboard']) && auth()->user()->priv()['programme_dashboard'] == 1)
                        <a href="{{ route('programme.dashboard') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/MANAGER-logos.jpeg') }}">
                        </a>
                        @endif

                        
                        
                        @if(auth()->user()->remote_access && isset(auth()->user()->priv()['access_account']) && auth()->user()->priv()['access_account'] == 1)
                        <a href="{{ route('accounts') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">  
                            <img class="block w-full h-auto shadow-md zoom-in rounded" src="{{ asset('build/assets/images/dash_icons/ACCOUNT-logos.jpg') }}">
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
                        @if(Auth::user() && Route::currentRouteName() == 'dashboard' && !empty($home_work_history_btns) && ((!in_array(auth()->user()->last_login_ip, $venue_ips) && isset($home_work) && $home_work) || (in_array(auth()->user()->last_login_ip, $venue_ips) && isset($desktop_login) && $desktop_login)))
                        <div class="intro-x mt-6 mb-6">
                            <div class="grid grid-cols-12 gap-5 logBtns">
                                {!! $home_work_history_btns !!}
                            </div>
                        </div>
                        @endif
                        <div class="intro-x mt-6 mb-6">
                            <div class="grid grid-cols-12 gap-5">
                                {!! $internal_link_buttons !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        <div class="col-span-12 2xl:col-span-3">
             <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-6">
                        <div class="grid grid-cols-12 gap-5">
                            @if(!empty($myPendingTask))
                                @foreach($myPendingTask as $process_id => $process)
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
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
             </div>
            
        </div>
        {{--<div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-6">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Pending Tasks</h2>
                            <a href="{{ route('task.manager.all') }}" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5">
                            @if(!empty($myPendingTask))
                                <div id="userPendingTaskAccordion" class="accordion mb-8">
                                    @foreach($myPendingTask as $process_id => $process)
                                        @if($process['outstanding_tasks'] > 0)
                                            <div class="accordion-item bg-white mb-3 border-0 rounded">
                                                <div id="userPendingTaskAccordion-{{ $loop->index }}" class="accordion-header">
                                                    <button class="accordion-button collapsed relative w-full text-sm font-semibold px-5 flex items-center" type="button" data-tw-toggle="collapse" data-tw-target="#userPendingTaskAccordion-collapse-{{ $loop->index }}" aria-expanded="false" aria-controls="userPendingTaskAccordion-collapse-{{ $loop->index }}">
                                                        {{ $process['name'] }} 
                                                        <span class="w-10 h-10 justify-center items-center inline-flex rounded-full bg-warning text-sm font-semibold text-white ml-auto relative">{{ $process['outstanding_tasks'] }}</span>
                                                    </button>
                                                </div>
                                                <div id="userPendingTaskAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse" aria-labelledby="userPendingTaskAccordion-{{ $loop->index }}" data-tw-parent="#userPendingTaskAccordion">
                                                    <div class="accordion-body px-5 border-t pt-5">
                                                        @if(isset($process['tasks']) && !empty($process['tasks']))
                                                            @foreach($process['tasks'] as $task_id => $pts)
                                                                <a href="{{ route('task.manager.show', $task_id) }}" class="intro-x block">
                                                                    <div class="box px-5 py-3 mb-3 flex items-center zoom-in bg-success-soft-1">
                                                                        <div class="mr-auto">
                                                                            <div class="font-medium">{{ $pts->name }}</div>
                                                                            @if(!empty($pts->short_description))
                                                                                <div class="text-slate-500 text-xs mt-0.5">{{ $pts->short_description }}</div>
                                                                            @endif
                                                                        </div>
                                                                        <div class="w-10 h-10 rounded-full bg-primary text-white text-danger inline-flex justify-center items-center font-medium">{{ $pts->pending_task }}</div>
                                                                    </div>
                                                                </a>
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach 
                                </div>
                                <a href="{{ route('task.manager') }}" class="intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">My Tasks</a>
                            @else 
                                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> There are no pending task found.
                                </div>
                            @endif
                            {{--@if(isset($myPendingTask['outstanding_tasks']) && $myPendingTask['outstanding_tasks'] > 0)
                                <div class="intro-x">
                                    <div class="box px-5 py-3 mb-3 bg-warning-soft flex items-center zoom-in">
                                        <div class="mr-auto">
                                            <div class="font-medium">Total Outstanding Tasks</div>
                                        </div>
                                        <div class="w-10 h-10 rounded-full bg-warning text-white inline-flex justify-center items-center font-medium">{{ $myPendingTask['outstanding_tasks'] }}</div>
                                    </div>
                                </div>
                            @endif
                            @if(isset($myPendingTask['tasks']) && !empty($myPendingTask['tasks']))
                                @foreach($myPendingTask['tasks'] as $task_id => $pts)
                                    @if($loop->index > 5)
                                        @break
                                    @endif
                                    <a href="{{ route('task.manager.show', $task_id) }}" class="intro-x block">
                                        <div class="box px-5 py-3 mb-3 flex items-center zoom-in">
                                            <div class="mr-auto">
                                                <div class="font-medium">{{ $pts->name }}</div>
                                                @if(!empty($pts->short_description))
                                                    <div class="text-slate-500 text-xs mt-0.5">{{ $pts->short_description }}</div>
                                                @endif
                                            </div>
                                            <div class="w-10 h-10 rounded-full bg-primary text-white text-danger inline-flex justify-center items-center font-medium">{{ $pts->pending_task }}</div>
                                        </div>
                                    </a>
                                @endforeach
                            @else 
                                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> There are no pending task found.
                                </div>
                            @endif--}}
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}
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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    
@endsection

@section('script')
    @vite('resources/js/jquery-stopwatch.js')
    @vite('resources/js/staff-dashboard.js')
@endsection