@extends('../layout/' . $layout)

@section('subhead')
    <title>Dashboard - Enigma - Tailwind HTML Admin Template</title>
@endsection

@section('subcontent')
    <div id="personalTutorDashboard" class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: General Report -->
                <div class="col-span-12 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Welcome Mr Personal Tutor</h2>
                    </div>
                    <div class="report-box-2 intro-y mt-5">
                        <div class="box grid grid-cols-12">
                            <div class="col-span-12 lg:col-span-4 px-8 py-12 flex flex-col justify-center">
                                <i data-lucide="pie-chart" class="w-10 h-10 text-pending"></i>
                                <div class="justify-start flex items-center text-slate-600 dark:text-slate-300 mt-12">
                                    Current Term
                                    <i data-lucide="alert-circle" class="tooltip w-4 h-4 ml-1.5" title="Total value of your sales: $158.409.416"></i>
                                </div>
                                <div class="flex items-center justify-start mt-4">
                                    <div id="term-dropdown" class="dropdown w-1/2 sm:w-auto mr-auto">
                                        <button id="selected-term" class="dropdown-toggle btn btn-primary text-white w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> <i data-loading-icon="oval" class="w-4 h-4 mr-2 hidden"  data-color="white"></i> <span>{{ $termList[$currenTerm]->name }}</span> <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                        </button>
                                        <div class="dropdown-menu w-40">
                                            <ul class="dropdown-content">
                                                
                                                @foreach($termList as $term)
                                                <li>
                                                    <a  id="term-{{ $term->id }}" data-tutor_id="{{ $employee->user_id }}"  data-instance_term_id="{{ $term->id }}" data-instance_term="{{ $term->name }}" href="javascript:;" class="dropdown-item term-select {{ ($termList[$currenTerm]->name==$term->name) ? " dropdown-active " : ""}}">
                                                        <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>  {{ $term->name }}
                                                    </a>
                                                </li>
                                                @endforeach
                                                
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4 text-slate-500 text-xs">Last updated 1 hour ago</div>
                                <button class="btn btn-outline-secondary relative justify-start rounded-full mt-12">
                                    Search Student By ID
                                    <span class="w-8 h-8 absolute flex justify-center items-center bg-primary text-white rounded-full right-0 top-0 bottom-0 my-auto ml-auto mr-0.5">
                                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                    </span>
                                </button>
                                <div class="text-right pt-2">
                                    <a href="{{ route('student') }}" class="text-primary text-small">Advance Search</a>
                                </div>
                            </div>
                            <div class="col-span-12 lg:col-span-8 p-8 border-t lg:border-t-0 lg:border-l border-slate-200 dark:border-darkmode-300 border-dashed">
                                <ul
                                    class="
                                        nav
                                        nav-pills
                                        w-60
                                        border
                                        border-slate-300
                                        dark:border-darkmode-300
                                        border-dashed
                                        rounded-md
                                        mx-auto
                                        p-1
                                        mb-8
                                    "
                                    role="tablist"
                                >
                                    <li id="selectedTermButton" class="nav-item flex-1" role="presentation">
                                        <button 
                                            class="nav-link w-full py-1.5 px-2 active"
                                            data-tw-toggle="pill"
                                            data-tw-target="#weekly-report"
                                            type="button"
                                            role="tab"
                                            aria-controls="weekly-report"
                                            aria-selected="true"
                                        >
                                        {{ $termList[$currenTerm]->name }}
                                        </button>
                                    </li>
                                </ul>
                                <div class="tab-content px-5 pb-5">
                                    <div class="tab-pane active grid grid-cols-12 gap-y-8 gap-x-10" id="weekly-report" role="tabpanel" aria-labelledby="weekly-report-tab">
                                        <div class="col-span-6 sm:col-span-6">
                                            <div class="text-slate-500">No of Module</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div id="totalModule" class="text-base">{{ $termList[$currenTerm]->total_modules }}</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">No of Student</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">120</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">Expected Assignments</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">640</div>
                                            </div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">Average Attendance</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">62.92%</div>
                                            </div>
                                        </div>

                                        <div class="col-span-12 sm:col-span-6"></div>

                                        <div class="col-span-12 sm:col-span-6">
                                            <div class="text-slate-500">Attendance Bellow 60%</div>
                                            <div class="mt-1.5 flex items-center">
                                                <div class="text-base">25</div>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END: General Report -->
                
                <!-- BEGIN: Weekly Top Products -->
                <div class="col-span-12 mt-6">
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
                <!-- END: Weekly Top Products -->
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
                            @foreach($data as $termId => $termModuleList)
                                @if($termList[$currenTerm]->id == $termId)
                                    @php $i=0 @endphp
                                    @foreach($termModuleList as $termData)
                                     @php $i++ @endphp
                                        <a @if($i>4) @class(['more','hidden']) @endif href="{{ route('tutor-dashboard.plan.module.show',$termData->id) }}" target="_blank" style="inline-block">
                                            <div id="moduleset-{{ $termData->id }}" class="intro-y module-details_{{ $termId }}  @php if($termList[$currenTerm]->id != $termId) echo "hidden " @endphp ">
                                                <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                                                    <div class="ml-4 mr-auto">
                                                        <div class="font-medium">{{ $termData->module }}</div>
                                                        <div class="text-slate-500 text-xs mt-0.5">{{ $termData->course }}</div>
                                                    </div>
                                                    <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">{{ $termData->group }}</div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @endif
                            @endforeach
                            <a href="#" id="load-more" class="intro-y w-full block text-center rounded-md py-4 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                        </div>
                    </div>
                    <!-- END: Important Notes -->
                    <!-- BEGIN: Recent Activities -->
                    {{-- <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-5">Todays Classes</h2>
                            <a href="" class="ml-auto text-primary truncate">Show More</a>
                        </div>
                        <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="box px-5 py-3 ml-0 flex-1 zoom-in bg-danger-soft">
                                    <div class="flex items-center">
                                        <div class="font-medium">Business Environment (B)</div>
                                        <div class="text-xs text-slate-500 ml-auto">10:00 AM</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">Mahin Talukder</div>
                                </div>
                            </div>
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="box px-5 py-3 ml-0 flex-1 zoom-in bg-warning-soft">
                                    <div class="flex items-center">
                                        <div class="font-medium">Business Environment (B)</div>
                                        <div class="text-xs text-slate-500 ml-auto">10:00 AM</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">Mahin Talukder</div>
                                </div>
                            </div>
                            <div class="intro-x relative flex items-center mb-3">
                                <div class="box px-5 py-3 ml-0 flex-1 zoom-in bg-success-soft">
                                    <div class="flex items-center">
                                        <div class="font-medium">Business Environment (B)</div>
                                        <div class="text-xs text-slate-500 ml-auto">10:00 AM</div>
                                    </div>
                                    <div class="text-slate-500 mt-1">Mahin Talukder</div>
                                </div>
                            </div>
                        </div>
                    </dCTRL + SHIFT + FCTRL + SHIFT + Fiv> --}}
                    <!-- END: Recent Activities -->
                    <!-- BEGIN: Recent Activities -->
                    <div class="col-span-12 md:col-span-6 xl:col-span-4 2xl:col-span-12 mt-3">
                        <div class="intro-x flex items-center h-10">
                            <h2 class="text-lg font-medium truncate mr-auto">Todays Classes</h2>
                            <div class="sm:ml-auto mt-3 sm:mt-0 relative text-slate-500">
                                <i class="w-4 h-4 z-10 absolute my-auto inset-y-0 ml-3 left-0" data-lucide="calendar-days"></i>
                                <input id="tutor-calendar-date" value="{{ date('d-m-Y') }}" type="text" class="form-control sm:w-56 box pl-10 " placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                <input name="tutor_id" value="{{ $user->id }}" type="hidden" />
                            </div>
                            
                        </div>  
                        <div id="todays-classlist">
                            <div class="mt-5 relative before:block before:absolute before:w-px before:h-[85%] before:bg-slate-200 before:dark:bg-darkmode-400 before:ml-5 before:mt-5">
                                
                                @foreach($todaysClassList as $list)
                                        <div class="intro-x relative flex items-center mb-3">
                                            <div class="box px-5 py-3 ml-0 flex-1 zoom-in bg-warning-soft">
                                                <div class="flex items-center">
                                                    <div class="font-medium">{{ $list["module"] }} ({{ $list["group"] }})</div>
                                                    <div class="text-xs text-slate-500 ml-auto">{{ $list["start_time"] }}</div>
                                                </div>
                                                <div class="text-slate-500 mt-1">{{ $list["course"] }}</div>
                                            </div>
                                        </div>
                                    
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <!-- END: Recent Activities -->
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/tutor-personal.js')
@endsection
