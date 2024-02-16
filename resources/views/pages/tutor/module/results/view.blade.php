@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
    <h2 class="text-lg font-medium mr-auto">Result Details</h2>
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
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Class Type</span> <span class="font-medium ml-2">{{ $data->classType }}</span>
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
        <li id="availabilty-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab" >
                <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Result
            </a>
        </li>
    </ul>
</div>

<form id="resultBulkInsert" method="post" action="{{ route("result.store") }}">
<div class="intro-y tab-content mt-5">
    <div id="availabilty" class="tab-pane active" role="tabpanel" aria-labelledby="availabilty-tab">
        <div class="intro-y box lg:mt-5">
            <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">{{ $assessmentPlan->courseModuleBase->type->name }} - {{ $assessmentPlan->courseModuleBase->type->code }}</h2>
                <a href="{{ route("tutor-dashboard.plan.module.show",$plan->id) }}" data-tw-merge class="mr-2 ml-auto inline-flex transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-primary text-primary dark:border-primary [&:hover:not(:disabled)]:bg-primary/10 "><i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back To assessment</a>
                @if(isset($result) && count($result)>0) 
                <button type="submit" class="update_all_result btn btn-success shadow-md mr-2"><i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> Update Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>
                
                <button type="button"  data-assessmentPlan= {{ $assessmentPlan->id }} class="delete_all_result btn btn-danger shadow-md"><i data-lucide="trash" class="w-4 h-4 mr-2"></i> Delete All <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>

                @else
                <button type="submit" id="insertAllResult" class="insert_all_result btn btn-warning shadow-md"><i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> Insert Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>
                @endif
            </div>
        </div>
        <div class="intro-y box p-5 mt-5">
            @include('pages.tutor.module.includes.result.index')
        </div>
        <div class="intro-y box lg:mt-5">
            <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                <h2 class="font-medium text-base mr-auto">{{ $assessmentPlan->courseModuleBase->type->name }} - {{ $assessmentPlan->courseModuleBase->type->code }}</h2>
                <a href="{{ route("tutor-dashboard.plan.module.show",$plan->id) }}" data-tw-merge class="mr-2 ml-auto inline-flex transition duration-200 border shadow-sm items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&:hover:not(:disabled)]:bg-opacity-90 [&:hover:not(:disabled)]:border-opacity-90 [&:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-primary text-primary dark:border-primary [&:hover:not(:disabled)]:bg-primary/10 "><i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Back To assessment</a>
                @if(isset($result) && count($result)>0) 
                <button type="submit" class="update_all_result btn btn-success shadow-md mr-2"><i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> Update Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>
                
                <button type="button"  data-assessmentPlan= {{ $assessmentPlan->id }} class="delete_all_result btn btn-danger shadow-md"><i data-lucide="trash" class="w-4 h-4 mr-2"></i> Delete All <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>

                @else
                <button type="submit" id="insertAllResult" class="insert_all_result btn btn-warning shadow-md"><i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i> Insert Bulk Results <i data-loading-icon="oval" class="w-4 h-4 ml-1 hidden text-white" ></i></button>
                @endif
            </div>
        </div>
    </div>
</div>
</form>
@include('pages.tutor.module.component.result.modal')
@endsection

@section('script')
    @vite('resources/js/plan-tasks.js')
@endsection