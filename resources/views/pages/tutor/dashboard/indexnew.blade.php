@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - London Churchill College</title>
@endsection

@section('subcontent')
    <div id="tutorDashboard" class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6 mt-3 2xl:mt-8">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 lg:col-span-8 xl:col-span-8 mt-2">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Welcome <u>{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</u></h2>
                    
                    </div>
                    <div class="report-box-2 intro-y mt-12 sm:mt-5">
                        <div class="box sm:flex">
                            <div class="px-8 py-12 flex flex-col justify-center flex-1">
                                <div class="w-30 h-30 flex-none image-fit rounded-full overflow-hidden">
                                    <img alt="{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}" class="rounded-full" src="{{ (isset($employee->photo) && !empty($employee->photo) && Storage::disk('local')->exists('public/employees/'.$employee->id.'/'.$employee->photo) ? Storage::disk('local')->url('public/employees/'.$employee->id.'/'.$employee->photo) : asset('build/assets/images/avater.png')) }}">
                                </div>
                                <div class="relative text-3xl font-medium mt-5">
                                    {{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}
                                </div>
                                <div class="mt-4 text-slate-500">
                                    @if(isset($employee->address->address_line_1) && $employee->address->address_line_1 > 0)
                                        @if(isset($employee->address->address_line_1) && !empty($employee->address->address_line_1))
                                            <span class="font-medium">{{ $employee->address->address_line_1 }}</span><br/>
                                        @endif
                                        @if(isset($employee->address->address_line_2) && !empty($employee->address->address_line_2))
                                            <span class="font-medium">{{ $employee->address->address_line_2 }}</span><br/>
                                        @endif
                                        @if(isset($employee->address->city) && !empty($employee->address->city))
                                            <span class="font-medium">{{ $employee->address->city }}</span>,
                                        @endif
                                        @if(isset($employee->address->state) && !empty($employee->address->state))
                                            <span class="font-medium">{{ $employee->address->state }}</span>, 
                                        @endif
                                        @if(isset($employee->address->post_code) && !empty($employee->address->post_code))
                                            <span class="font-medium">{{ $employee->address->post_code }}</span>,<br/>
                                        @endif
                                        @if(isset($employee->address->country) && !empty($employee->address->country))
                                            <span class="font-medium">{{ strtoupper($employee->address->country) }}</span><br/>
                                        @endif
                                    @else 
                                        <span class="font-medium text-warning">Not Set Yet!</span><br/>
                                    @endif
                                </div>
                            </div>
                            <div class="px-8 py-12 flex flex-col justify-center flex-1 border-t sm:border-t-0 sm:border-l border-slate-200 dark:border-darkmode-300 border-dashed">
                                <div class="text-slate-500 text-xs">Email</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">
                                        {{ $employee->user->email }}<br/>
                                        {{ $employee->email }}
                                    </div>
                                </div>
                                <div class="text-slate-500 text-xs mt-5">Mobile</div>
                                <div class="mt-1.5 flex items-center">
                                    <div class="text-base">{{ $employee->mobile }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: General Report -->
                {{-- col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-8 --}}
                <!-- BEGIN: Important Notes -->
                <div class="col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-4 mt-2">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-auto">Class Date</h2>
                        <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                            <i class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0" data-lucide="calendar-days"></i>
                            <input id="tutor-calendar-date" value="{{ date('d-m-Y') }}" type="text" class="form-control sm:w-56 box pl-10 " placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                            <input name="tutor_id" value="{{ $user->id }}" type="hidden" />
                        </div>
                        
                    </div>  
                    <div id="todays-classlist">
                        @foreach($todaysClassList as $list)
                            <div class="mt-5 intro-x">
                                <div class="box zoom-in">
                                    <div class="pt-5 px-5"><!-- flex items-center -->
                                        <div class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center min-w-10 px-3 py-0.5 mb-2">{{ $list["group"] }}</div>
                                        <!--<div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">{{ $list["group"] }}</div>-->
                                        <div class="ml-0 mr-auto">
                                            <div class="text-base font-medium truncate w-full relative">{{ $list["module"] }} </div>
                                            <div class="text-slate-400 mt-1">{{ $list["course"] }}</div>
                                            <div class="text-slate-400 mt-1">Schedule - {{ $list["start_time"] }} at {{ $list["venue"] }} - {{ $list["room"] }}</div>
                                        </div>
                                        {{-- @if($list["attendance_information"]!=null)
                                            @if($list["attendance_information"]!=null && $list["end_time"]==null)  
                                                <span class="mr-1 bg-primary absolute right-0 p-1 text-xs text-white">Badge</span>
                                                <a data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-success text-success dark:border-success [&amp;:hover:not(:disabled)]:bg-success/10 mb-2 mr-1  w-24">Class Started</a>
                                                @else
                                                <a data-tw-merge class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-primary text-primary dark:border-primary [&amp;:hover:not(:disabled)]:bg-primary/10 mb-2 mr-1  w-24 ">Class Ended</div>
                                                @endif
                                        @else
                                            <a class="transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-pending text-pending dark:border-pending [&amp;:hover:not(:disabled)]:bg-pending/10 mb-2 mr-1  w-24 ">Pending</a>
                                        @endif --}}
                                    </div>
                                    <div class="mt-5 px-5 pb-5 flex font-medium justify-center">
                                    
                                    @if($list["attendance_information"]!=null)
                                        @if($list["end_time"]==null)
                                            <a data-attendanceinfo="{{ $list["attendance_information"]->id }}" data-id="{{ $list["id"] }}" href="{{ route("tutor-dashboard.attendance",[$list["tutor_id"],$list["id"]]) }}" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Feed Attendance</a>
                                            <a data-attendanceinfo="{{ $list["attendance_information"]->id }}" data-id="{{ $list["id"] }}" data-tw-toggle="modal" data-tw-target="#endClassModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">End Class</a>
                                        @else
                                            <a href="{{ route("tutor-dashboard.attendance",[$list["tutor_id"],$list["id"]]) }}"  data-attendanceinfo="{{ $list['attendance_information']->id }}" data-id="{{ $list['id'] }}" class="start-punch transition duration-200 btn btn-sm btn-success text-white py-2 px-3 "><i data-lucide="view" width="24" height="24" class="stroke-1.5 mr-2 h-4 w-4"></i>View Feed</a>
                                        @endif
                                    @else
                                        <a data-tw-toggle="modal" data-id="{{ $list["id"] }}" data-tw-target="#editPunchNumberDeteilsModal" class="start-punch transition duration-200 btn btn-sm btn-primary text-white py-2 px-3">Start Class</a>
                                    @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <!-- END: Important Notes -->
                
            </div>
        </div>
        <div class="col-span-12 2xl:col-span-3">
            <div class="2xl:border-l -mb-10 pb-10">
                <div class="2xl:pl-6 grid grid-cols-12 gap-x-6 2xl:gap-x-0 gap-y-6">
                    {{-- col-span-12 sm:col-span-6 lg:col-span-4 xl:col-span-4 mt-2 --}}
                    <!-- BEGIN: Visitors -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-12 mt-3 2xl:mt-8">
                        <div class="intro-y flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">My Modules</h2>
                            <div id="term-dropdown" class="dropdown w-1/2 sm:w-auto ml-auto">
                                <button id="selected-term" class="dropdown-toggle btn btn-primary text-white w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i  data-lucide="file-text" class="w-4 h-4 mr-2 "></i> <i data-loading-icon="oval" class="w-4 h-4 mr-2 hidden"  data-color="white"></i> <span>{{ $termList[$currenTerm]->name }}</span> <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        @foreach($termList as $term)
                                        <li>
                                            <a  id="term-{{ $term->id }}" data-tutor_id="{{ $employee->user_id }}"  data-instance_term_id="{{ $term->id }}" data-instance_term="{{ $term->name }}" href="javascript:;" class="dropdown-item term-select {{ ($termList[$currenTerm]->name==$term->name) ? " dropdown-active " : ""}}">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> {{ $term->name }}
                                            </a>
                                        </li>
                                        @endforeach
                                        
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div id="TermBox">
                            @foreach($termList as $term)
                                @if($termList[$currenTerm]->id == $term->id)
                                    <div id="totalmodule-{{ $term->id }}" class="report-box-2 intro-y mt-5 mb-7 @php if($termList[$currenTerm]->id != $term->id) echo "hidden " @endphp">
                                        <div class="box p-5">
                                            <div class="flex items-center">
                                                Total No of Modules
                                            </div>
                                            <div class="text-2xl font-medium mt-2">{{ $term->total_modules }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                            @foreach($data as $termId => $termModuleList)
                                @if($termList[$currenTerm]->id == $termId)
                                    @foreach($termModuleList as $termData)
                                        <a href="{{ route('tutor-dashboard.plan.module.show',$termData->id) }}" target="_blank" style="inline-block">
                                            <div id="moduleset-{{ $termData->id }}" class="intro-y module-details_{{ $termId }}  @php if($termList[$currenTerm]->id != $termId) echo "hidden " @endphp ">
                                                <div class="box px-4 py-4 mb-3 zoom-in">{{-- flex items-center --}}
                                                    <div class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center ml-4 min-w-10 px-3 py-0.5 mb-2">{{ $termData->group }}</div>
                                                    {{--<div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">{{ $termData->group }}</div>--}}
                                                    <div class="ml-4 mr-auto">
                                                        <div class="font-medium">{{ $termData->module }}</div>
                                                        <div class="text-slate-500 text-xs mt-0.5">{{ $termData->course }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <!-- END: Visitors -->
                </div>
            </div>
        </div>
    </div>
    @include('pages.tutor.dashboard.modals')
@endsection

@section('script')
    @vite('resources/js/tutor-dashboard-new.js')
@endsection
