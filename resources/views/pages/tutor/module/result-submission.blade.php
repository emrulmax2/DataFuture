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
            @if($plan->personal_tutor_id > 0)
                <div class="flex items-center mt-4">
                    <div class="w-10 h-10 intro-x image-fit mr-5 inline-block">
                        <img alt="{{ (isset($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : '') }}" class="rounded-full shadow" src="{{ (isset($plan->personalTutor->employee->photo_url) ? $plan->personalTutor->employee->photo_url : asset('build/assets/images/placeholders/200x200.jpg'))}}">
                    </div>
                    <div class="inline-block relative">
                        <div class="font-medium whitespace-nowrap uppercase">{{ (isset($plan->personalTutor->employee->full_name) ? $plan->personalTutor->employee->full_name : '') }}</div>
                        <div class="text-slate-500 text-xs whitespace-nowrap">Personal Tutor</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
        <li id="submission-tab" class="nav-item mr-5 " role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#submission" aria-controls="submission" aria-selected="true" role="tab" >
                <i data-lucide="files" class="w-4 h-4 mr-2  mt-1"></i> Result Submission
            </a>
        </li>
        @if($submissionAssessment->count()>0)
        <li id="comparison-tab" class="nav-item mr-5 " role="presentation">
            <a href="{{ route('result.comparison',$plan->id) }}" class="nav-link py-4 inline-flex px-0" >
                <i data-lucide="plus-circle" class="w-4 h-4 mr-2 mt-1"></i> Result Comparison
            </a>
        </li>
        @endif
    </ul>
</div>

<div class="intro-y tab-content mt-5">

    <div id="submission"  class="tab-pane active" role="tabpanel"  aria-labelledby="submission-tab">
        <!-- BEGIN: HTML Table Data -->
        @include('pages.tutor.module.includes.submission-result')
        <!-- END: HTML Table Data -->
    </div>
</div>
@include('pages.tutor.module.component.modal')
@endsection

@section('script')

    @vite('resources/js/results-staff-submission.js')
@endsection