@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - London Churchill College</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 2xl:col-span-9"> 
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">General Report</h2>
                        <a href="" class="ml-auto flex items-center text-primary">
                            <i data-lucide="refresh-ccw" class="w-4 h-4 mr-3"></i> Reload Data
                        </a>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-5">
                        @if(isset(auth()->user()->priv()['applicant']) && auth()->user()->priv()['applicant'] == 1)
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="{{ route('admission') }}" class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="user" class="report-box__icon text-pending"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $applicant }}</div>                                    
                                    <div class="text-base text-slate-500 mt-1">Applicant</div> 
                                </div>
                            </a>
                        </div>
                        @endif
                        @if(isset(auth()->user()->priv()['live']) && auth()->user()->priv()['live'] == 1)
                        <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <a href="{{ route('student') }}" class="report-box zoom-in">
                                <div class="box p-5">
                                    <div class="flex">
                                        <i data-lucide="user-check" class="report-box__icon text-success"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $student }}</div>
                                    <div class="text-base text-slate-500 mt-1">Live Student</div>
                                </div>
                            </a>
                        </div> 
                        @endif
                        @if(isset(auth()->user()->priv()['tutor_2']) && auth()->user()->priv()['tutor_2'] == 1)
                        <a href="{{ route('tutor-dashboard.show.new',32) }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                                <div class="report-box zoom-in">
                                    <div class="box p-5">
                                        <div class="flex">
                                            <i data-lucide="monitor" class="report-box__icon text-warning"></i>
                                            <div class="ml-auto">
                                                <div class="report-box__indicator bg-success tooltip cursor-pointer" title="12% Higher than last month">
                                                    % <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-3xl font-medium leading-8 mt-6">0.00</div>
                                        <div class="text-base text-slate-500 mt-1">Tutor Dashboard</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endif
                        @if(isset(auth()->user()->priv()['personal_tutor']) && auth()->user()->priv()['personal_tutor'] == 1)
                        <a href="{{ route('pt.dashboard',32) }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                            <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                                <div class="report-box zoom-in">
                                    <div class="box p-5">
                                        <div class="flex">
                                            <i data-lucide="monitor" class="report-box__icon text-warning"></i>
                                            <div class="ml-auto">
                                                <div class="report-box__indicator bg-success tooltip cursor-pointer" title="12% Higher than last month">
                                                    % <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-3xl font-medium leading-8 mt-6">0.00</div>
                                        <div class="text-base text-slate-500 mt-1">Personal Tutor Dashboard</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endif
                        @if(isset(auth()->user()->priv()['req_interview']) && auth()->user()->priv()['req_interview'] == 1)
                        <a href="{{ route('interviewlist') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
                        {{-- <div class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">--}}                            
                            <div class="report-box zoom-in">                               
                                <div class="box p-5">                                    
                                    <div class="flex">
                                        <i data-lucide="user" class="report-box__icon text-success"></i>
                                        <div class="ml-auto">
                                            <div class="report-box__indicator bg-success tooltip cursor-pointer" title="22% Higher than last month">
                                                22 % <i data-lucide="chevron-up" class="w-4 h-4 ml-0.5"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6">{{ $interview }}</div>
                                    <div class="text-base text-slate-500 mt-1">Required Interviews</div>                              
                                </div>                               
                            </div>                        
                        {{-- </div> --}}
                        </a>
                        @endif
                        @if(isset(auth()->user()->priv()['hr_porta']) && auth()->user()->priv()['hr_porta'] == 1)
                        <a href="{{ route('hr.portal') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">                           
                            <div class="report-box zoom-in">                               
                                <div class="box p-5">                                    
                                    <div class="flex">
                                        <i data-lucide="contact" class="report-box__icon text-success"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6" style="color: transparent;">04</div>
                                    <div class="text-base text-slate-500 mt-1">HR Portal</div>                              
                                </div>                               
                            </div>        
                        </a>
                        @endif
                        @if(isset(auth()->user()->priv()['programme_dashboard']) && auth()->user()->priv()['programme_dashboard'] == 1)
                        <a href="{{ route('programme.dashboard') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">                           
                            <div class="report-box zoom-in">                               
                                <div class="box p-5">                                    
                                    <div class="flex">
                                        <i data-lucide="calendar-range" class="report-box__icon text-success"></i>
                                    </div>
                                    <div class="text-3xl font-medium leading-8 mt-6" style="color: transparent;">00</div>
                                    <div class="text-base text-slate-500 mt-1">Programme Dashboard</div>                              
                                </div>                               
                            </div>        
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Pending Tasks</h2>
                            @if(isset($myPendingTask['outstanding_tasks']) && $myPendingTask['outstanding_tasks'] > 0)
                                <a href="{{ route('task.manager') }}" class="ml-auto text-primary truncate">Show More</a>
                            @endif
                        </div>
                        <div class="mt-5">
                            @if(isset($myPendingTask['outstanding_tasks']) && $myPendingTask['outstanding_tasks'] > 0)
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div> 
    </div>
@endsection
