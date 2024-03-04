@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - London Churchill College</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9 pt-5 relative">
            <div class="intro-y block sm:flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">
                    Daily Class Information 
                    {{ (isset($theTerm->attenTerm->name) && !empty($theTerm->attenTerm->name) ? '['.$theTerm->attenTerm->name.']' : '') }}
                </h2>
                <div class="flex items-center sm:ml-auto mt-3 sm:mt-0">
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2">
                        <i data-lucide="sliders-horizontal" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <select class="form-control w-full border-0" name="plan_status" id="planClassStatus" style="max-width: 230px;">
                            <option value="0">All</option>
                            <option value="1">Scheduled</option>
                            <option value="2">Started</option>
                            <option value="3">Finished</option>
                        </select>
                    </div>
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 ml-3">
                        <i data-lucide="sliders-horizontal" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <select class="form-control w-full border-0" name="course_id" id="planCourseId" style="max-width: 230px;">
                            <option value="0">All Course</option>
                            @if(!empty($courses))
                                @foreach($courses as $cr)
                                    <option value="{{ $cr->id }}">{{ $cr->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="btn box flex items-center text-slate-600 dark:text-slate-300 p-0 pl-2 ml-3">
                        <i data-lucide="calendar-days" class="hidden sm:block w-4 h-4 mr-2"></i>
                        <input type="text" name="class_date" class="w-full form-control border-0 classDate" id="theClassDate" value="{{ $theDate }}" style="max-width: 110px;"/>
                    </div>
                    <button class="ml-3 btn box flex items-center text-slate-600 dark:text-slate-300">
                        <i data-lucide="calendar-clock" class="hidden sm:block w-4 h-4 mr-2"></i> <span id="theClock">{{ date('H:i:s') }}</span>
                    </button>
                </div>
            </div>
            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0 relative dailyClassInfoTableWrap">
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
                <table class="table table-report sm:mt-2" id="dailyClassInfoTable">
                    <thead>
                        <tr>
                            <th class="whitespace-nowrap uppercase">Schedule</th>
                            <th class="whitespace-nowrap uppercase">Module</th>
                            <th class="text-left whitespace-nowrap uppercase">Tutor</th>
                            <th class="text-left whitespace-nowrap uppercase">Room</th>
                            <th class="text-left whitespace-nowrap uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        {!! $classInformation !!}
                    </tbody>
                </table>
            </div>


            <div class="grid grid-cols-12 gap-0 gap-x-6 pt-5 mt-5">
                <div class="col-span-12 sm:col-span-6">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Tutors <span class="tutorCount">{{ isset($classTutor['count']) ? ' ('.$classTutor['count'].')' : ' (0)' }}</span></h2>
                        <a href="{{ route('programme.dashboard.tutors', $theTerm->term_declaration_id) }}" class="ml-auto text-primary truncate">Show More</a>
                    </div>
                    <div class="mt-5 tutorWrap relative">
                        <div class="theHolder">{!! $classTutor['html'] !!}</div>
                        <a href="{{ route('programme.dashboard.tutors', $theTerm->term_declaration_id) }}" class="intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-6">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Personal Tutors <span class="personalTutorCount">{{ isset($classPTutor['count']) ? ' ('.$classPTutor['count'].')' : ' (0)' }}</span></h2>
                        <a href="{{ route('programme.dashboard.personal.tutors', $theTerm->term_declaration_id) }}" class="ml-auto text-primary truncate">Show More</a>
                    </div>
                    <div class="mt-5 personalTutorWrap relative">
                        <div class="theHolder">{!! $classPTutor['html'] !!}</div>
                        <a href="{{ route('programme.dashboard.personal.tutors', $theTerm->term_declaration_id) }}" class="intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-5">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Student Attendance</h2>
                            <a href="#" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 intro-x">
                            <div class="report-box-2 before:hidden xl:before:block intro-y mt-5">
                                <div class="box p-5">
                                    <div class="mt-3">
                                        <div class="h-[196px]">
                                            <canvas id="report-donut-chart"></canvas>
                                        </div>
                                    </div>
                                    <div class="w-52 sm:w-auto mx-auto mt-8">
                                        <div class="flex items-center">
                                            <div class="w-2 h-2 bg-primary rounded-full mr-3"></div>
                                            <span class="truncate">May 2023 HND</span>
                                            <span class="font-medium ml-auto">62%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Staff Absence Today</h2>
                            <a href="{{ route('hr.portal.live.attedance') }}" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5">
                            @if(!empty($absentToday))
                                @foreach($absentToday as $employee_id => $absent)
                                    <div class="intro-x">
                                        <div class="flex items-center px-5 py-3 mb-3 box zoom-in">
                                            <div class="flex-none w-10 h-10 overflow-hidden rounded-full image-fit">
                                                <img src="{{ $absent['photo_url'] }}" alt="{{ $absent['full_name'] }}">
                                            </div>
                                            <div class="ml-4 mr-auto">
                                                <div class="font-medium uppercase">{{ $absent['full_name'] }}</div>
                                                <div class="mt-0.5 text-xs text-slate-500">
                                                    {{ $absent['date'] }}
                                                </div>
                                            </div>
                                            <div class="text-danger">
                                                {{ $absent['hourMinute'] }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else 
                                <div class="alert alert-pending-soft show flex items-center mb-2 zoom-in" role="alert">
                                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> There are not absent attendance found for today.
                                </div>
                            @endif
                            <a href="{{ route('hr.portal.live.attedance') }}" class="intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @vite('resources/js/programme-dashboard.js')
@endsection