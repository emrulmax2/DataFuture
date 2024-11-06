@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection
@section('subcontent')

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 2xl:col-span-9">
            <div class="grid grid-cols-12 gap-6">
                <!-- BEGIN: Show-info Report -->
                @include('pages.students.frontend.dashboard.show-info')
                <!-- END: Show-Info Report -->
                <!-- BEGIN: Sales Report -->

                <div class="col-span-12 lg:col-span-6 mt-8">
                    <div class="intro-y flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">My Modules</h2>
                        @if($termList)
                        <div id="term-dropdown" class="dropdown w-1/2 sm:w-auto ml-auto">
                            <button id="selected-term" class="dropdown-toggle btn btn-primary text-white w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> <i data-loading-icon="oval" class="w-4 h-4 mr-2 hidden"  data-color="white"></i> <span>{{ $termList[$currenTerm]->name }}</span> 
                            </button>
                            {{-- <div class="dropdown-menu w-40">
                                <ul class="dropdown-content">
                                    @foreach($termList as $term)
                                    <li>
                                        <a  id="term-{{ $term->id }}"  data-instance_term_id="{{ $term->id }}" data-instance_term="{{ $term->name }}" href="javascript:;" class="dropdown-item term-select {{ ($termList[$currenTerm]->name==$term->name) ? " dropdown-active " : ""}}">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> {{ $term->name }}
                                        </a>
                                    </li>
                                    @endforeach
                                    
                                </ul>
                            </div> --}}
                        </div>
                        @endif
                    </div>
                    @if($termList)
                    <div class="intro-y  mt-12 sm:mt-5">
                        <div id="TermBox">
                            @foreach($data as $termId => $termModuleList)
                                @if($termList[$currenTerm]->id == $termId)
                                    @foreach($termModuleList as $termData)
                                        @php 
                                            $module_id = (isset($termData->parent_id) && $termData->parent_id > 0 ? $termData->parent_id : $termData->id);
                                        @endphp
                                        <a href="{{ route('students.dashboard.plan.module.show', $module_id) }}" target="_blank" style="inline-block">
                                            <div id="moduleset-{{ $termData->id }}" class="intro-y module-details_{{ $termId }}  @php if($termList[$currenTerm]->id != $termId) echo "hidden " @endphp ">
                                                <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                                                    <div class="ml-4 mr-auto">
                                                        <div class="font-medium">{{ $termData->module }}</div>
                                                        <div class="text-slate-500 text-xs mt-0.5">{{ isset($termData->classType) ? $termData->classType : "No class type found" }}</div>
                                                    </div>
                                                    @if($termData->tutor_photo != "")
                                                        <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden mr-2">
                                                            
                                                            <img alt="#" src="{{ $termData->tutor_photo }}">
                                                        </div>
                                                    @endif
                                                    @if($termData->personal_tutor_photo!="")
                                                        <div class="w-10 h-10 flex-none image-fit rounded-md overflow-hidden mr-2">
                                                            <img alt="#" src="{{ $termData->personal_tutor_photo }}">
                                                        </div>
                                                    @endif
                                                    @if(isset($termData->group) && !empty($termData->group))
                                                        @if(strlen($termData->group) > 2)
                                                            <div class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center ml-4 min-w-10 px-3 py-0.5 mb-2">{{ $termData->group }}</div>
                                                        @else
                                                            <div class="rounded bg-success text-white cursor-pointer font-medium w-auto inline-flex justify-center items-center ml-4 min-w-10 px-3 py-0.5 mb-2">{{ $termData->group }}</div>
                                                        @endif
                                                    @endif
                                                    {{-- <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">{{ $termData->group }}</div> --}}
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div><!--end of intro-->
                    @else
                    <div class="intro-y  mt-12 sm:mt-5">
                        <div id="TermBox">
                            <a href="javascript:void()" target="_blank" style="inline-block">
                                <div id="moduleset-0" class="intro-y module-details_0 ">
                                    <div class="box px-4 py-4 mb-3 flex items-center zoom-in">
                                        <div class="ml-4 mr-auto">
                                            <div class="font-medium">No Module Available</div>
                                            <div class="text-slate-500 text-xs mt-0.5"></div>
                                        </div>
                                        {{-- <div class="rounded-full text-lg bg-success text-white cursor-pointer font-medium w-12 h-10 inline-flex justify-center items-center">N/A</div> --}}
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div><!--end of intro-->
                    @endif
                </div>
                <!-- END: Sales Report -->
                <!-- BEGIN: Transactions -->
                <div class="col-span-12 lg:col-span-6 mt-8">
                    <div class="intro-x flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Do it Online</h2>
                        <a href="" class="ml-auto text-primary truncate">Show More</a>
                    </div>
                    <div class="mt-5">
                        @php $iCountTotal =0 ;  @endphp
                        @foreach ($doItOnline as $onlineWork)
                        @php
                            $endDate = strtotime(date("Y-m-d",strtotime($onlineWork->end_to)));
                            $currentDate = strtotime(date("Y-m-d"));
                        @endphp
                        @if( $endDate > $currentDate || $onlineWork->end_to=="0000-00-00" || $onlineWork->end_to==null)
                            @if($iCountTotal==7) <div id="doitOnlineSecondBox" class="hidden"> @endif
                                <a id="doitOnline{{ $iCountTotal++; }}" href="{{ $onlineWork->form_link }}" class="intro-x inline-block w-full" >
                                    <div class="box px-5 py-3 mb-3 flex items-center zoom-in w-full">
                                        <div class="mr-auto">
                                            <div class="font-medium">{{ $onlineWork->form_name }}</div>
                                            <div class="text-slate-500 text-xs mt-0.5">{{ $onlineWork->form_description }}</div>
                                        </div>
                                    </div>
                                </a>
                        @endif  
                        @endforeach
                        @if($iCountTotal>7) </div> @endif
                        <a href="#"  class="doitOnlineSecondBoxToggle intro-x w-full block text-center rounded-md py-3 border border-dotted border-slate-400 dark:border-darkmode-300 text-slate-500">View More</a>
                    </div>
                </div>
                <!-- END: Transactions -->

            </div>
        </div>
        @include('pages.students.frontend.dashboard.profile.sidebar')
        
    </div>
    @if (session('verifySuccessMessage'))
        <!-- BEGIN: Notification Content -->
        <div id="success-notification-content" class="toastify-content hidden flex">
            <i class="text-success" data-lucide="check-circle"></i>
            <div class="ml-4 mr-4">
                <div class="font-medium">Success !</div>
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
    @vite('resources/js/student-frontend-dashboard.js')
@endsection
