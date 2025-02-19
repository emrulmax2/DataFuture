@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
<div class="intro-y flex justify-between items-center mt-8">
    <h2 class="text-lg font-medium mr-auto min-w-max">Module Details</h2>
    <div class="w-auto flex mt-0 sm:mt-4">
        <a href="{{ route('students.dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
        
    </div>
</div>
<!-- BEGIN: Profile Info -->
<div class="intro-y box px-5 pt-5 mt-5">
    <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 sm:pb-5 -mx-5">
        <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
            <div class="ml-auto mr-auto">
                <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold sm:text-3xl">{{ $data->module }}</div>
                <div class="text-slate-500 font-medium">{{ $data->course }} - {{ $data->term_name }}</div>
            </div>
        </div>
        <div class="my-6 sm:mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
            <div class="font-medium text-left lg:mt-3">Module Details</div>
            <div class="flex flex-col justify-center items-start mt-4">
                <div class="truncate sm:whitespace-normal flex items-center">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Group:</span> <span class="font-medium ml-2">{{ $data->group }}</span>
                </div>
                
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Class Type</span> <span class="font-medium ml-2">{{ $data->classType }}</span>
                </div>
            </div>
        </div>
        <div class="flex flex-1 px-5 items-center justify-center lg:justify-start border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0">
            <div class="w-full sm:w-auto grid grid-cols-12 gap-6 sm:mt-2 sm:mt-0">
                @if(isset($data->tutor) && $data->tutor!=null)
                <div class="relative flex items-center w-full col-span-12 py-3 sm:py-0">
                    <div class="w-12 h-12 flex-none image-fit">
                        <img alt="{{ $data->tutor->title->name.' '.$data->tutor->first_name.' '.$data->tutor->last_name }}" class="rounded-full" src="{{ (isset($data->tutor->photo) && !empty($data->tutor->photo) && Storage::disk('local')->exists('public/employees/'.$data->tutor->id.'/'.$data->tutor->photo) ? Storage::disk('local')->url('public/employees/'.$data->tutor->id.'/'.$data->tutor->photo) : asset('build/assets/images/avater.png')) }}">
                    </div>
                    <div class="ml-4 mr-auto">
                        <a href="" class="font-medium">{{ $data->tutor->full_name }}</a>
                        <div class="text-slate-500 mr-5 sm:mr-5">Tutor</div>
                    </div>
                    {{-- <div class="font-medium text-slate-600 dark:text-slate-500">+5</div> --}}
                </div>
                @endif
                @if(isset($data->personalTutor) && $data->personalTutor!=null)
                <div class="relative flex items-center sm:mt-2 w-full col-span-12">
                    <div class="w-12 h-12 flex-none image-fit">
                        <img alt="{{ $data->personalTutor->name.' '.$data->personalTutor->first_name.' '.$data->personalTutor->last_name }}" class="rounded-full" src="{{ (isset($data->personalTutor->photo) && !empty($data->personalTutor->photo) && Storage::disk('local')->exists('public/employees/'.$data->personalTutor->id.'/'.$data->personalTutor->photo) ? Storage::disk('local')->url('public/employees/'.$data->personalTutor->id.'/'.$data->personalTutor->photo) : asset('build/assets/images/avater.png')) }}">
                    </div>
                    <div class="ml-4 mr-auto">
                        <a href="" class="font-medium">{{ $data->personalTutor->full_name }}</a>
                        <div class="text-slate-500 mr-5 sm:mr-5">Personal Tutor</div>
                    </div>
                    {{-- <div class="font-medium text-slate-600 dark:text-slate-500">+2</div> --}}
                </div>
                @endif
            </div>
        </div>
    </div>
    <div>
        <button id="studentPlanMenu" class="sm:hidden w-full flex items-center justify-end text-gray-700 py-4 sm:px-0">
            <div class="bg-primary text-white font-semibold py-3 px-4 border border-gray-400 rounded flex items-center gap-2">
                <span>Menu</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="bar-chart2" class="lucide lucide-bar-chart2 w-4 h-4 -rotate-90"><line x1="18" x2="18" y1="20" y2="10"></line><line x1="12" x2="12" y1="20" y2="4"></line><line x1="6" x2="6" y1="20" y2="14"></line></svg>
            </div>
        </button>
    </div>
    <ul class="hidden sm:flex nav nav-link-tabs flex-col sm:flex-row justify-start sm:text-center studentPlanMenuList" role="tablist">
        <li id="availabilty-tab" class="nav-item sm:mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 w-full sm:w-auto active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab" >
                <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Course Content
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="https://teams.microsoft.com/v2/"  class="nav-link py-4 inline-flex px-0">
                <div class="flex items-center justify-center">
                    <div class="flex items-center justify-between  rounded-lg mr-2">
                        <img class="h-6 pr-1 py-1" src="{{ asset('build/assets/images/mircrosoft-team-logo.png') }}"></img>
                        <div class="flex flex-col px-2">
                            Microsoft Teams
                        </div>
                    </div>
                </div>
            </a>
        </li>
        <li id="class-dates-tab" class="nav-item sm:mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 w-full sm:w-auto " data-tw-target="#class-dates" aria-controls="class-dates" aria-selected="true" role="tab" >
                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Class Dates
            </a>
        </li>
        {{-- <li id="participants-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#participants" aria-controls="participants" aria-selected="true" role="tab" >
                <i data-lucide="users" class="w-4 h-4 mr-2"></i> Participants
            </a>
        </li>


        <li id="assessment-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#assessment" aria-controls="assessment" aria-selected="true" role="tab" >
                <i data-lucide="utility-pole" class="w-4 h-4 mr-2"></i> Assessment
            </a>
        </li>
        <li id="analytics-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#analytics" aria-controls="analytics" aria-selected="true" role="tab" >
                <i data-lucide="scatter-chart" class="w-4 h-4 mr-2"></i> Analytics
            </a>
        </li> --}}
    </ul>
</div>
<div class="intro-y tab-content mt-5">
    <div id="availabilty" class="tab-pane active" role="tabpanel" aria-labelledby="availabilty-tab">
        <div class="intro-y box sm:p-5 mt-5">
            @include('pages.students.frontend.dashboard.module.includes.activity')
        </div>
    </div>
    <div id="class-dates" class="tab-pane " role="tabpanel" aria-labelledby="classDates-tab">
        
        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            @include('pages.students.frontend.dashboard.module.includes.dates')
        </div>
        <!-- END: HTML Table Data -->
       
    </div>

    {{-- <div id="participants" class="tab-pane " role="tabpanel"  aria-labelledby="participants-tab">
        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            @include('pages.students.frontend.dashboard.module.includes.participants')
        </div>
        <!-- END: HTML Table Data -->

        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            @include('pages.students.frontend.dashboard.module.includes.studentlist')
        </div>
        <!-- END: HTML Table Data -->
    </div>
    <div id="assessment" class="tab-pane " role="tabpanel"  aria-labelledby="assessment-tab">
        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            <h2>Upcoming....</h2>
        </div>
        <!-- END: HTML Table Data -->
    </div>
    <div id="analytics" class="tab-pane " role="tabpanel"  aria-labelledby="analytics-tab">
        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            <h2>Upcoming....</h2>
        </div>
        <!-- END: HTML Table Data -->
    </div> --}}
</div>
@include('pages.students.frontend.dashboard.module.component.modal')
@endsection

@section('script')
    @vite('resources/js/plan-tasks-students.js')
    <script>
        (function(){
            let studentPlanMenu = document.querySelector('#studentPlanMenu');
            let studentPlanMenuList = document.querySelector('.studentPlanMenuList');
            studentPlanMenu.addEventListener('click', function(){
                studentPlanMenuList.classList.toggle('hidden');
            })
        })()
    </script>
@endsection