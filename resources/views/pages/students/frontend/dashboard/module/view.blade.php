@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Module Details</h2>
    <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        <a href="{{ route('students.dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
    </div>
</div>
<!-- BEGIN: Profile Info -->
<div class="intro-y box px-5 pt-5 mt-5">
    <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
        <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
            <div class="ml-auto mr-auto">
                <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold text-3xl">{{ $data->module }}</div>
                <div class="text-slate-500 font-medium">{{ $data->course }} - {{ $data->term_name }}</div>
            </div>
        </div>
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
            <div class="font-medium text-center lg:text-left lg:mt-3">Module Details</div>
            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                <div class="truncate sm:whitespace-normal flex items-center">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Group:</span> <span class="font-medium ml-2">{{ $data->group }}</span>
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Student : </span> <span class="font-medium ml-2">{{ $studentCount }}</span>
                </div>
                
                
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Class Type</span> <span class="font-medium ml-2">{{ $data->classType }}</span>
                </div>
            </div>
        </div>
        <div class="flex flex-1 px-5 items-center justify-center lg:justify-start border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0">
            <div class="grid grid-cols-12 gap-6 mt-0">
                <div class="relative flex items-center w-full col-span-12">
                    <div class="w-12 h-12 flex-none image-fit">
                        <img alt="{{ $data->tutor->title->name.' '.$data->tutor->first_name.' '.$data->tutor->last_name }}" class="rounded-full" src="{{ (isset($data->tutor->photo) && !empty($data->tutor->photo) && Storage::disk('local')->exists('public/employees/'.$data->tutor->id.'/'.$data->tutor->photo) ? Storage::disk('local')->url('public/employees/'.$data->tutor->id.'/'.$data->tutor->photo) : asset('build/assets/images/avater.png')) }}">
                    </div>
                    <div class="ml-4 mr-auto">
                        <a href="" class="font-medium">{{ $data->tutor->full_name }}</a>
                        <div class="text-slate-500 mr-5 sm:mr-5">Tutor</div>
                    </div>
                    {{-- <div class="font-medium text-slate-600 dark:text-slate-500">+5</div> --}}
                </div>
                <div class="relative flex items-center mt-2 w-full col-span-12">
                    <div class="w-12 h-12 flex-none image-fit">
                        <img alt="{{ $data->personalTutor->name.' '.$data->personalTutor->first_name.' '.$data->personalTutor->last_name }}" class="rounded-full" src="{{ (isset($data->personalTutor->photo) && !empty($data->personalTutor->photo) && Storage::disk('local')->exists('public/employees/'.$data->personalTutor->id.'/'.$data->personalTutor->photo) ? Storage::disk('local')->url('public/employees/'.$data->personalTutor->id.'/'.$data->personalTutor->photo) : asset('build/assets/images/avater.png')) }}">
                    </div>
                    <div class="ml-4 mr-auto">
                        <a href="" class="font-medium">{{ $data->personalTutor->full_name }}</a>
                        <div class="text-slate-500 mr-5 sm:mr-5">Personal Tutor</div>
                    </div>
                    {{-- <div class="font-medium text-slate-600 dark:text-slate-500">+2</div> --}}
                </div>
            </div>
        </div>
    </div>
    <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
        <li id="availabilty-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab" >
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
        <li id="class-dates-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#class-dates" aria-controls="class-dates" aria-selected="true" role="tab" >
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
        <div class="intro-y box p-5 mt-5">
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
@endsection