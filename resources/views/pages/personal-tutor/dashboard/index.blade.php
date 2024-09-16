@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div id="personalTutorDashboard" class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8 relative z-20">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Welcome {{ $employee->full_name }}</h2>
                    </div>
                    <div class="report-box-2 intro-y mt-5">
                        <div class="box grid grid-cols-12">
                            <div class="col-span-12 lg:col-span-4 px-8 py-12 flex flex-col justify-center">
                                <div class="pr-8 pt-12 flex flex-col justify-center flex-1">
                                    <div class="w-30 h-30 flex-none image-fit rounded-full overflow-hidden">
                                        <img alt="{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}" class="rounded-full" src="{{ (isset($employee->photo) && !empty($employee->photo) && Storage::disk('local')->exists('public/employees/'.$employee->id.'/'.$employee->photo) ? Storage::disk('local')->url('public/employees/'.$employee->id.'/'.$employee->photo) : asset('build/assets/images/avater.png')) }}">
                                    </div>
                                    <div class="relative text-3xl font-medium mt-5">
                                        {{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}
                                    </div>
                                    <div class="text-slate-500">
                                        {{ $employee->user->email }}<br/>
                                        {{-- $employee->email --}}
                                    </div>
                                </div>
                                
                                <form class="relative justify-start flex mt-12" method="post" action="#">
                                    <div class="autoCompleteField w-full h-full rounded-full" data-table="students">
                                        <input type="text" autocomplete="off" id="registration_no" name="student_id" class="form-control rounded-full registration_no" value="" placeholder="Search Student By ID"/>
                                        <ul class="autoFillDropdown"></ul>
                                        <input type="hidden" id="profileUrl" name="profile_url"/>
                                    </div>
                                    <button disabled id="viewStudentBtn" type="button" class="w-8 h-8 disabled:cursor-not-allowed disabled:opacity-70 absolute flex justify-center items-center bg-primary text-white rounded-full right-0 top-0 bottom-0 my-auto ml-auto mr-0.5">
                                        <i data-lucide="search" class="w-4 h-4 svgSearch"></i><!--arrow-right-->
                                        <svg style="opacity: 0;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                            stroke="white" class="w-4 h-4 ml-2 svgLoader absolute l-0 t-0 b-0 r-0 m-auto">
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
                                </form>
                                <!--<div class="text-right pt-2">
                                    <a href="{{ route('student') }}" class="text-primary text-small">Advance Search</a>
                                </div>-->
                            </div>
                            <div class="col-span-12 lg:col-span-8 p-8 border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-darkmode-300 border-dashed">
                                @if(isset($current_term->id) && $current_term->id > 0)
                                <ul class="nav nav-pills w-60 border border-slate-300 dark:border-darkmode-300 border-dashed rounded-md mx-auto p-1 mb-8" role="tablist">
                                    <li id="selectedTermButton" class="nav-item flex-1" role="presentation">
                                        <button class="nav-link w-full py-1.5 px-2 active" data-tw-toggle="pill" data-tw-target="#weekly-report" type="button" role="tab" aria-controls="weekly-report" aria-selected="true" >
                                            {{ $current_term->name }}
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content px-5 pb-5">
                                    <div class="tab-pane active grid grid-cols-12 gap-y-8 gap-x-10" id="weekly-report" role="tabpanel" aria-labelledby="weekly-report-tab">
                                        <div class="col-span-6 sm:col-span-6">
                                            <div class="text-slate-500">No of Module</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div id="totalModule" class="text-base">{{ $modules->count() }}</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">No of Student</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">{{ $no_of_assigned }}</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">Expected Assignments</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">0</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">Average Attendance</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">0%</div>
                                            </div>
                                        </div>

                                        <div class="col-span-12 sm:col-span-6"></div>

                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">Attendance Bellow 60%</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">0</div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                @else 
                                    <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Assigned terms not found.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: General Report -->
                
                <!-- BEGIN: Weekly Top Products
                <div class="col-span-12 mt-6 relative z-10">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Students Work List</h2>
                        <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                            <button class="btn box flex items-center text-slate-600 dark:text-slate-300">
                                <i data-lucide="file-text" class="hidden sm:block w-4 h-4 mr-2"></i> {{ date('jS M, Y')}}
                            </button>
                        </div>
                    </div>
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        <table class="table table-report sm:mt-2">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap">IMAGES</th>
                                    <th class="whitespace-nowrap">NAME</th>
                                    <th class="text-center whitespace-nowrap">Attendance %</th>
                                    <th class="text-center whitespace-nowrap text-left">Missed Module</th>
                                    <th class="text-center whitespace-nowrap">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($fakers, 0, 4) as $faker)
                                    <tr class="intro-x">
                                        <td class="w-40">
                                            <div class="flex">
                                                <div class="w-10 h-10 image-fit zoom-in">
                                                    <img alt="London Churchill College" class="tooltip rounded-full" src="{{ asset('build/assets/images/' . $faker['images'][0]) }}" title="Uploaded at {{ $faker['dates'][0] }}">
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="" class="font-medium whitespace-nowrap">{{ $faker['products'][0]['name'] }}</a>
                                            <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $faker['products'][0]['category'] }}</div>
                                        </td>
                                        <td class="text-center">{{ $faker['stocks'][0] }}</td>
                                        <td class="w-60 text-left">
                                            <div class="flex items-start justify-start text-success">
                                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Business Environment -B
                                            </div>
                                            <div class="flex items-start justify-start text-success mt-1">
                                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Accounting - M
                                            </div>
                                        </td>
                                        <td class="table-report__action w-56">
                                            <div class="flex justify-center items-center">
                                                <a class="flex items-center mr-3" href="">
                                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> Note
                                                </a>
                                                <a class="flex items-center text-danger" href="">
                                                    <i data-lucide="tablet-smartphone" class="w-4 h-4 mr-1"></i> Send SMS
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="intro-y flex flex-wrap sm:flex-row sm:flex-nowrap items-center mt-3">
                        <nav class="w-full sm:w-auto sm:mr-auto">
                            <ul class="pagination">
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevrons-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevron-left"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">...</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">3</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">...</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevron-right"></i>
                                    </a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">
                                        <i class="w-4 h-4" data-lucide="chevrons-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                        <select class="w-20 form-select box mt-3 sm:mt-0">
                            <option>10</option>
                            <option>25</option>
                            <option>35</option>
                            <option>50</option>
                        </select>
                    </div>
                </div>
                END: Weekly Top Products -->
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <!-- BEGIN: Important Notes -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-8">
                        <div class="intro-y flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">My Modules</h2>
                        </div>
                        
                        <div id="personalTutormoduleList" class="mt-5">
                            @if($modules->count() > 0)
                                @php $i = 1; @endphp
                                @foreach($modules as $mod)
                                    <a class="{{ $i > 4 ? 'more hidden' : 'block' }}" href="{{ route('tutor-dashboard.plan.module.show', $mod->id) }}" target="_blank">
                                        <div id="moduleset-{{ $mod->id }}" class="intro-y module-details_{{ $mod->id }}">
                                            <div class="box px-4 py-4 mb-3 zoom-in {{ (isset($mod->tutor_id) && $mod->tutor_id > 0 ? 'pl-5' : '') }}">
                                                @if(isset($mod->tutor_id) && $mod->tutor_id > 0)
                                                <div class="w-10 h-10 image-fit -ml-5 rounded-full absolute t-0 b-0 my-auto" style="margin-left: -35px;">
                                                    <img src="{{ (isset($mod->tutor->employee->photo_url) && !empty($mod->tutor->employee->photo_url) ? $mod->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}" title="{{ (isset($mod->tutor->employee->full_name) && !empty($mod->tutor->employee->full_name) ? $mod->tutor->employee->full_name : '') }}" class="tooltip rounded-full" alt="{{ (isset($mod->tutor->employee->full_name) && !empty($mod->tutor->employee->full_name) ? $mod->tutor->employee->full_name : '') }}"/>
                                                </div>
                                                @endif
                                                <div class="flex justify-start items-center mb-2 pl-4">
                                                    <div class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center min-w-10 px-3 py-0.5">{{ $mod->group->name }}</div>
                                                    <button class="rounded bg-info text-white cursor-pointer font-medium inline-flex justify-center items-center w-auto ml-1 px-3 py-0.5">
                                                        {{ (!empty($mod->class_type) ? $mod->class_type : (isset($mod->creations->class_type) && !empty($mod->creations->class_type) ? $mod->creations->class_type : 'Unknown')) }}
                                                    </button>
                                                    <button class="rounded bg-primary text-white cursor-pointer font-medium inline-flex justify-center items-center w-auto ml-1 px-3 py-0.5">
                                                        {{ $mod->activeAssign->count() }}
                                                    </button>
                                                </div>
                                                <div class="ml-4 mr-auto">
                                                    <div class="font-medium">{{ $mod->creations->module_name }}</div>
                                                    <div class="text-slate-500 text-xs mt-0.5">{{ $mod->course->name }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                    @php $i += 1; @endphp
                                @endforeach
                                @if($modules->count() > 4)
                                    <a href="javascript:void(0);" id="load-more" class="intro-y w-full block text-center rounded-md py-4 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                                @endif
                            @else 
                                <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Modules not found!
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- END: Important Notes -->

                    <!-- BEGIN: Recent Activities -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-auto">Todays Classes</h2>
                            <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                                <i class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0" data-lucide="calendar-days"></i>
                                <input data-pt="{{ $user->id }}" id="personalTutorCalendar" value="{{ date('d-m-Y') }}" type="text" class="form-control sm:w-56 box pl-10 " placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                        </div>  
                        <div id="todays-classlist">
                            <div id="todaysClassListWrap" class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                                @if($todays_classes->count() > 0)
                                    @foreach($todays_classes as $class)
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
                                        <div class="intro-x relative flex items-center mb-3">
                                            <div class="before:block before:absolute before:w-20 before:h-px before:bg-slate-200 before:dark:bg-darkmode-400 before:mt-5 before:ml-5">
                                                <div class="w-10 h-10 flex-none image-fit rounded-full overflow-hidden">
                                                    <img alt="{{ (isset($class->plan->tutor->employee->full_name) && !empty($class->plan->tutor->employee->full_name) ? $class->plan->tutor->employee->full_name : 'London Churchill College') }}" src="{{ (isset($class->plan->tutor->employee->photo_url) && !empty($class->plan->tutor->employee->photo_url) ? $class->plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg')) }}">
                                                </div>
                                            </div>
                                            <div class="box px-5 py-3 ml-4 flex-1 bg-warning-soft zoom-in">
                                                <div class="flex items-center mb-3">
                                                    <div class="font-medium">
                                                        {{ $class->plan->creations->module_name }} 
                                                        ({{ $class->plan->group->name }})
                                                        {{ (isset($class->plan->class_type) && !empty($class->plan->class_type) ? ' - '.$class->plan->class_type : '') }}
                                                    </div>
                                                    <div class="text-xs text-slate-500 ml-auto text-right" style="flex: 0 0 70px">{{ (isset($class->plan->start_time) && !empty($class->plan->start_time) ? date('h:i A', strtotime($class->plan->start_time)) : '') }}</div>
                                                </div>
                                                @if($class->plan->class_type == 'Tutorial')
                                                    @if(isset($class->attendanceInformation->id) && $class->attendanceInformation->id > 0)
                                                        @if($class->feed_given == 1)
                                                            <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" href="{{ route('tutor-dashboard.attendance', [$class->plan->personal_tutor_id, $class->id, 1]) }}" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Feed Attendance</a>
                                                        @else
                                                            <a href="{{ route('tutor-dashboard.attendance', [$class->plan->personal_tutor_id, $class->id, 1]) }}"  data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" class="start-punch transition duration-200 btn btn-sm btn-success text-white py-2 px-3 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>View Feed</a>
                                                            @if($class->feed_given == 1 && $class->attendanceInformation->end_time == null)
                                                                <a data-attendanceinfo="{{ $class->attendanceInformation->id }}" data-id="{{ $class->id }}" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch transition duration-200 btn btn-sm btn-danger text-white py-2 px-3 ml-1"><i data-lucide="x-circle" class="stroke-1.5 mr-2 h-4 w-4"></i>End Class</a>
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if($showClass == 1)
                                                            <a data-tw-toggle="modal" data-id="{{ $class['id'] }}" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Start Class</a>
                                                        @elseif($showClass == 2)
                                                            <div class="alert alert-danger-soft show flex items-start" role="alert">
                                                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Class Start Button appears 15 minutes before the scheduled time.
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endif
                                                {{--<div class="text-slate-500 mt-1">{{ (isset($class->plan->course->name) ? $class->plan->course->name : '') }}</div>--}}
                                            </div>
                                        </div>
                                    @endforeach
                                @else 
                                    <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                        <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> No Class found for the day.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <!-- END: Recent Activities -->
                </div>
            </div>
        </div>
    </div>
    @include('pages.personal-tutor.dashboard.modals')
@endsection

@section('script')
    @vite('resources/js/tutor-personal.js')
@endsection
