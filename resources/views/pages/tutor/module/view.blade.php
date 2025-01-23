@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Module Details</h2>
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
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-l  border-r border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
            <div class="font-medium text-center lg:text-left lg:mt-3">Module Details</div>
            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                <div class="truncate sm:whitespace-normal flex items-center">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Group:</span> <span class="font-medium ml-2">{{ $data->group }}</span>
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="users" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Student : </span> <span class="font-medium ml-2">{{ $studentCount }}</span>
                </div>
                
                
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Class Type</span> <span class="font-medium ml-2">{{ (isset($plan->class_type) && !empty($plan->class_type) ? $plan->class_type : '') }}</span>
                </div>
            </div>
        </div>
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-t lg:border-0 border-slate-200/60 dark:border-darkmode-400 pt-5 lg:pt-0">
            @if($plan->tutor_id > 0)
                <div class="flex items-center lg:mt-3">
                    <div class="w-10 h-10 intro-x image-fit mr-5 inline-block">
                        <img alt="{{ (isset($plan->tutor->employee->full_name) ? $plan->tutor->employee->full_name : '') }}" class="rounded-full shadow" src="{{ (isset($plan->tutor->employee->photo_url) ? $plan->tutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'))}}">
                    </div>
                    <div class="inline-block relative">
                        <div class="font-medium whitespace-nowrap uppercase">{{ (isset($plan->tutor->employee->full_name) ? $plan->tutor->employee->full_name : '') }}</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">Tutor</div>
                    </div>
                </div>
            @endif
            @if(isset($plan->class_type) && $plan->class_type == 'Tutorial' && $plan->personal_tutor_id > 0)
                <div class="flex items-center mt-4">
                    <div class="w-10 h-10 intro-x image-fit mr-5 inline-block">
                        <img alt="{{ (isset($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : '') }}" class="rounded-full shadow" src="{{ (isset($plan->personalTutor->employee->photo_url) ? $plan->personalTutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'))}}">
                    </div>
                    <div class="inline-block relative">
                        <div class="font-medium whitespace-nowrap uppercase">{{ (isset($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : '') }}</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">Personal Tutor</div>
                    </div>
                </div>
            @elseif(isset($plan->class_type) && $plan->class_type != 'Tutorial' && isset($plan->tutorial->personal_tutor_id) && $plan->tutorial->personal_tutor_id > 0)
                <div class="flex items-center mt-4">
                    <div class="w-10 h-10 intro-x image-fit mr-5 inline-block">
                        <img alt="{{ (isset($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : '') }}" class="rounded-full shadow" src="{{ (isset($plan->personalTutor->employee->photo_url) ? $plan->personalTutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'))}}">
                    </div>
                    <div class="inline-block relative">
                        <div class="font-medium whitespace-nowrap uppercase">{{ (isset($plan->tutorial->personalTutor->employee->full_name) ? $plan->tutorial->personalTutor->employee->full_name : '') }}</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">Personal Tutor</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
        @if($plan->class_type != 'Tutorial' && $plan->class_type != 'Seminar')
        <li id="availabilty-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab" >
                <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Course Content
            </a>
        </li>
        @endif
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
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 {{ ($plan->class_type == 'Tutorial' || $plan->class_type == 'Seminar' ? 'active' : '') }}" data-tw-target="#class-dates" aria-controls="class-dates" aria-selected="{{ ($plan->class_type == 'Tutorial' || $plan->class_type == 'Seminar' ? 'true' : 'false') }}" role="tab" >
                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> Class Dates
            </a>
        </li>
        <li id="participants-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#participants" aria-controls="participants" aria-selected="true" role="tab" >
                <i data-lucide="users" class="w-4 h-4 mr-2"></i> Participants
            </a>
        </li>
        @if($plan->class_type != 'Tutorial' && $plan->class_type != 'Seminar')
            @if(isset(auth()->user()->priv()['assessment']) && auth()->user()->priv()['assessment'] == 1)
                <li id="assessment-tab" class="nav-item mr-5" role="presentation">
                    <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#assessment" aria-controls="assessment" aria-selected="true" role="tab" >
                        <i data-lucide="utility-pole" class="w-4 h-4 mr-2"></i> Assessment
                    </a>
                </li>
            @endif
            @if(isset(auth()->user()->priv()['analytics']) && auth()->user()->priv()['analytics'] == 1)
                <li id="analytics-tab" class="nav-item mr-5" role="presentation">
                    <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#analytics" aria-controls="analytics" aria-selected="true" role="tab" >
                        <i data-lucide="scatter-chart" class="w-4 h-4 mr-2"></i> Analytics
                    </a>
                </li>
            @endif
        @endif
        @if(isset(auth()->user()->priv()['result_management_pt']) && auth()->user()->priv()['result_management_pt'] == 1)
        <li id="submission-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 " data-tw-target="#submission" aria-controls="submission" aria-selected="true" role="tab" >
                <i data-lucide="files" class="w-4 h-4 mr-2"></i> Result Submission
            </a>
        </li>
        @endif
    </ul>
</div>
<div class="intro-y tab-content mt-5">
    @if($plan->class_type != 'Tutorial' && $plan->class_type != 'Seminar')
    <div id="availabilty" class="tab-pane active" role="tabpanel" aria-labelledby="availabilty-tab">
        <div class="intro-y box p-5 mt-5">
            @include('pages.tutor.module.includes.activity')
        </div>
    </div>
    @endif
    <div id="class-dates" class="tab-pane {{ ($plan->class_type == 'Tutorial' || $plan->class_type == 'Seminar' ? 'active' : '') }}" role="tabpanel" aria-labelledby="classDates-tab">
        
        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            @include('pages.tutor.module.includes.dates')
        </div>
        <!-- END: HTML Table Data -->
       
    </div>
    <div id="participants" class="tab-pane " role="tabpanel"  aria-labelledby="participants-tab">
        <!-- BEGIN: HTML Table Data -->
        {{--<div class="intro-y box p-5 mt-5">
            @include('pages.tutor.module.includes.participants')
        </div>--}}
        <!-- END: HTML Table Data -->

        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            @include('pages.tutor.module.includes.studentlist')
        </div>
        <!-- END: HTML Table Data -->
    </div>
    @if($plan->class_type != 'Tutorial' && $plan->class_type != 'Seminar')
    <div id="assessment" class="tab-pane " role="tabpanel"  aria-labelledby="assessment-tab">
        <!-- BEGIN: HTML Table Data -->
        <div class="intro-y box p-5 mt-5">
            @include('pages.tutor.module.includes.assessments')
        </div>
        <!-- END: HTML Table Data -->
    </div>
    <div id="analytics" class="tab-pane " role="tabpanel"  aria-labelledby="analytics-tab">
        <!-- BEGIN: HTML Table Data -->
        @include('pages.tutor.module.includes.analytics')
        <!-- END: HTML Table Data -->
    </div>
    @endif
    @if(isset(auth()->user()->priv()['result_management_pt']) && auth()->user()->priv()['result_management_pt'] == 1)
        <div id="submission"  class="tab-pane " role="tabpanel"  aria-labelledby="submission-tab">
            <!-- BEGIN: HTML Table Data -->
            @include('pages.tutor.module.includes.submission')
            <!-- END: HTML Table Data -->
        </div>
    @endif
</div>
@include('pages.tutor.module.component.modal')
@endsection

@section('script')
    @vite('resources/js/plan-tasks.js')
    @vite('resources/js/plan-tasks-analytics.js')
    @if(isset(auth()->user()->priv()['result_management_pt']) && auth()->user()->priv()['result_management_pt'] == 1)
        @vite('resources/js/results-submission.js')
    @endif
@endsection