@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Term Module Details</h2>
    </div>
    <!-- BEGIN: Profile Info -->
    <div class="intro-y box px-5 pt-5 mt-5">
        <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
            <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                <div class="ml-auto mr-auto">
                    <div class="w-auto sm:w-full truncate text-primary sm:whitespace-normal font-bold text-3xl">{{ $term->name }}</div>
                    <div class="text-slate-500 font-medium">{{ $term->course_name }} <u>({{ $term->semester_name }})</u></div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                <div class="font-medium text-center lg:text-left lg:mt-3">Term Details</div>
                <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                    <div class="truncate sm:whitespace-normal flex items-center">
                        <i data-lucide="shield" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Term:</span> <span class="font-medium ml-2">{{ $term->term }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="zap" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Session:</span> <span class="font-medium ml-2">{{ $term->session_term > 0 ? 'Term '.$term->session_term : '---' }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Start Date:</span> <span class="font-medium ml-2">{{ isset($term->start_date) && !empty($term->start_date) ? date('jS F, Y', strtotime($term->start_date)) : '' }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">End Date:</span> <span class="font-medium ml-2">{{ isset($term->end_date) && !empty($term->end_date) ? date('jS F, Y', strtotime($term->end_date)) : '' }}</span>
                    </div>
                </div>
            </div>
            <div class="mt-6 lg:mt-0 flex-1 px-5 pt-5 lg:pt-0">
                <div class="flex flex-col justify-center items-center lg:items-start mt-5 pt-6">
                    <div class="truncate sm:whitespace-normal flex items-center">
                        <i data-lucide="shield" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Teaching Weeks:</span> <span class="font-medium ml-2">{{ $term->total_teaching_weeks }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Start Date:</span> <span class="font-medium ml-2">{{ isset($term->teaching_start_date) && !empty($term->teaching_start_date) ? date('jS F, Y', strtotime($term->teaching_start_date)) : '' }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">End Date:</span> <span class="font-medium ml-2">{{ isset($term->teaching_end_date) && !empty($term->teaching_end_date) ? date('jS F, Y', strtotime($term->teaching_end_date)) : '' }}</span>
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Revision:</span> <span class="font-medium ml-2">{{ isset($term->revision_start_date) && !empty($term->revision_start_date) ? date('jS F, Y', strtotime($term->revision_start_date)) : '' }} {{ isset($term->revision_end_date) && !empty($term->revision_end_date) ? ' - '.date('jS F, Y', strtotime($term->revision_end_date)) : '' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
            <li id="modulesCreations-tab" class="nav-item mr-5" role="presentation">
                <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#modulesCreations" aria-controls="modulesCreations" aria-selected="true" role="tab" >
                    <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Modules
                </a>
            </li>
        </ul>
    </div>
    <div class="intro-y tab-content mt-5">
        <div id="modulesCreations" class="tab-pane active" role="tabpanel" aria-labelledby="modulesCreations-tab">
            @include('pages.module-creations.details.module-creations')
        </div>
    </div>

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection