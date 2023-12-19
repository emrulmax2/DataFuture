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
                        @if(isset(auth()->user()->priv()['tutor']) && auth()->user()->priv()['tutor'] == 1)
                        <a href="{{ route('tutor-dashboard.show',8) }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
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
                                        <div class="text-base text-slate-500 mt-1">Tutor Dashboard 2</div>
                                    </div>
                                </div>
                            </div>
                        </a>
                        @endif
                        @if(isset(auth()->user()->priv()['personal_tutor']) && auth()->user()->priv()['personal_tutor'] == 1)
                        <a href="{{ route('pt.dashboard') }}" class="col-span-12 sm:col-span-6 xl:col-span-3 intro-y">
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
                            <h2 class="text-lg font-medium truncate mr-5">Recent Activities</h2>
                            <a href="" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                    <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                        <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[9]['photos'][0]) }}">
                                    </div>
                                </div>
                                <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $fakers[9]['users'][0]['name'] }}</div>
                                        <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">Has joined the team</div>
                                </div>
                            </div>
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                    <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                        <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[8]['photos'][0]) }}">
                                    </div>
                                </div>
                                <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $fakers[8]['users'][0]['name'] }}</div>
                                        <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                                    </div>
                                    <div class="text-slate-500">
                                        <div class="mt-1">Added 3 new photos</div>
                                        <div class="flex mt-2">
                                            <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in" title="{{ $fakers[0]['products'][0]['name'] }}">
                                                <img alt="London Churchill College" class="rounded-md border border-white" src="{{ asset('build/assets/images/' . $fakers[8]['images'][0]) }}">
                                            </div>
                                            <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in" title="{{ $fakers[1]['products'][0]['name'] }}">
                                                <img alt="London Churchill College" class="rounded-md border border-white" src="{{ asset('build/assets/images/' . $fakers[8]['images'][1]) }}">
                                            </div>
                                            <div class="tooltip w-8 h-8 image-fit mr-1 zoom-in" title="{{ $fakers[2]['products'][0]['name'] }}">
                                                <img alt="London Churchill College" class="rounded-md border border-white" src="{{ asset('build/assets/images/' . $fakers[8]['images'][2]) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="intro-x text-slate-500 text-xs text-center my-4">12 November</div>
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                    <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                        <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[7]['photos'][0]) }}">
                                    </div>
                                </div>
                                <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $fakers[7]['users'][0]['name'] }}</div>
                                        <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">Has changed <a class="text-primary" href="">{{ $fakers[7]['products'][0]['name'] }}</a> price and description</div>
                                </div>
                            </div>
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                    <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                        <img alt="London Churchill College" src="{{ asset('build/assets/images/' . $fakers[6]['photos'][0]) }}">
                                    </div>
                                </div>
                                <div class="box px-5 py-3 ml-4 flex-1 zoom-in">
                                    <div class="flex items-center">
                                        <div class="font-medium">{{ $fakers[6]['users'][0]['name'] }}</div>
                                        <div class="text-xs text-slate-500 ml-auto">07:00 PM</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">Has changed <a class="text-primary" href="">{{ $fakers[6]['products'][0]['name'] }}</a> description</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div> 
    </div>
@endsection
