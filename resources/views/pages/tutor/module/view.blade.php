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
        <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
            <div class="font-medium text-center lg:text-left lg:mt-3">Module Details</div>
            <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                <div class="truncate sm:whitespace-normal flex items-center">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Group:</span> <span class="font-medium ml-2">{{ $data->group }}</span>
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="sliders" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Participant</span> <span class="font-medium ml-2">32</span>
                </div>
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="key" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Student</span> <span class="font-medium ml-2">12</span>
                </div>
                
                
                <div class="truncate sm:whitespace-normal flex items-center mt-3">
                    <i data-lucide="calendar" class="w-4 h-4 mr-2"></i> <span class="text-slate-500">Class Type</span> <span class="font-medium ml-2">{{ $data->classType }}</span>
                </div>
            </div>
        </div>
        <div class="mt-6 lg:mt-0 flex-1 px-5 pt-5 lg:pt-0">
            
        </div>
    </div>
    <ul class="nav nav-link-tabs flex-col sm:flex-row justify-center lg:justify-start text-center" role="tablist">
        <li id="availabilty-tab" class="nav-item mr-5" role="presentation">
            <a href="javascript:void(0);" class="nav-link py-4 inline-flex px-0 active" data-tw-target="#availabilty" aria-controls="availabilty" aria-selected="true" role="tab" >
                <i data-lucide="layers" class="w-4 h-4 mr-2"></i> Course Content
            </a>
        </li>
    </ul>
</div>
<div class="intro-y tab-content mt-5">
    <div id="availabilty" class="tab-pane active" role="tabpanel" aria-labelledby="availabilty-tab">
        <div class="intro-y box p-5 mt-5">
            <div data-tw-merge class="accordion p-5 mt-5">
                <div data-tw-merge class="bg-slate-200 accordion-item py-4 first:-mt-4 last:-mb-4 [&amp;:not(:last-child)]:border-b [&amp;:not(:last-child)]:border-slate-200/60 [&amp;:not(:last-child)]:dark:border-darkmode-400 p-4 first:mt-0 last:mb-0 border border-slate-200/60 mt-3 dark:border-darkmode-400">
                    <div class="accordion-header" id="faq-accordion-5">
                        <button data-tw-merge data-tw-toggle="collapse" data-tw-target="#faq-accordion-5-collapse" type="button" aria-expanded="true" aria-controls="faq-accordion-5-collapse" class="accordion-button outline-none inline-flex justify-between py-4 -my-4 font-medium w-full text-left dark:text-slate-400 [&amp;:not(.collapsed)]:text-primary [&amp;:not(.collapsed)]:dark:text-slate-300"><div class="flex-none">Introduction</div> <div class="accordian-lucide flex-none"><i data-lucide="minus" class="w-4 h-4"></i></div></button>
                    </div>
                    <div id="faq-accordion-5-collapse" aria-labelledby="faq-accordion-5" class="accordion-collapse collapse mt-3 text-slate-700 leading-relaxed dark:text-slate-400 [&.collapse:not(.show)]:hidden [&.collapse.show]:visible show">
                        <div data-tw-merge class="accordion-body leading-relaxed text-slate-600 dark:text-slate-500 leading-relaxed text-slate-600 dark:text-slate-500">
                            Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's
                            standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make
                            a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting,
                            remaining essentially unchanged.
                        </div>
                    </div>
                </div>
                <div data-tw-merge class="accordion-item bg-slate-200 py-4 first:-mt-4 last:-mb-4 [&amp;:not(:last-child)]:border-b [&amp;:not(:last-child)]:border-slate-200/60 [&amp;:not(:last-child)]:dark:border-darkmode-400 p-4 first:mt-0 last:mb-0 border border-slate-200/60 mt-3 dark:border-darkmode-400">
                    <div class="accordion-header" id="faq-accordion-6">
                        <button data-tw-merge data-tw-toggle="collapse" data-tw-target="#faq-accordion-6-collapse" type="button" aria-expanded="true" aria-controls="faq-accordion-6-collapse" class="accordion-button outline-none inline-flex justify-between py-4 -my-4 font-medium w-full text-left dark:text-slate-400 [&amp;:not(.collapsed)]:text-primary [&amp;:not(.collapsed)]:dark:text-slate-300 collapsed"><div class="flex-none">Assignment Breief and Important Documents</div> <div class="accordian-lucide flex-none"><i data-lucide="plus" class="w-4 h-4"></i></div></button>
                    </div>
                    <div id="faq-accordion-6-collapse" aria-labelledby="faq-accordion-6" class="accordion-collapse collapse mt-3 text-slate-700 leading-relaxed dark:text-slate-400 [&.collapse:not(.show)]:hidden [&.collapse.show]:visible">
                        <div data-tw-merge class="accordion-body leading-relaxed text-slate-600 dark:text-slate-500 leading-relaxed text-slate-600 dark:text-slate-500">
                            
                <!-- BEGIN: Weekly Top Products -->
                <div class="col-span-12 mt-6">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Module Documents</h2>
                        
                    </div>
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        <table class="table table-report sm:mt-2">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap w-20">#</th>
                                    <th class="whitespace-nowrap">NAME</th>
                                    <th class="text-center whitespace-nowrap">UPLOADS</th>
                                    <th class="text-center whitespace-nowrap">UPDATED AT</th>
                                    <th class="text-center whitespace-nowrap">ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($planTasks as $task) 
                                
                                @php
                                if ($task->task->logo !== null && Storage::disk('google')->exists('public/activity/'.$task->task->logo)) {
                                    $logoUrl = Storage::disk('google')->url('public/activity/'.$task->task->logo);
                                } else {
                                    $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
                                }
                                
                                $rand = rand(0,1);
                                @endphp
                                    <tr class="intro-x">
                                        <td class="w-20">
                                            <div class="flex">
                                                <div class="w-10 h-10 image-fit zoom-in">
                                                    <img alt="Midone - HTML Admin Template" class="tooltip rounded-full" src="{{ $logoUrl }}" title="Uploaded at {{ date("Y m d") }}">
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="" class="font-medium whitespace-nowrap">{{ $task->task->name }}</a>
                                            <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $task->task->category }}</div>
                                        </td>
                                        <td class="w-40">
                                            <div class="flex">
                                                @if(($task->taskUploads))
                                                    @foreach($task->taskUploads as $upload)
                                                        <a target="_blank" href="{{ Storage::disk('google')->url('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name) }}" class="w-10 h-10 image-fit zoom-in -ml-5" >
                                                            
                                                            @if($upload->doc_type!="pdf" && $upload->doc_type!="xls" && $upload->doc_type!="doc" && $upload->doc_type!="docx")
                                                                  <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ Storage::disk('google')->url('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name) }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">           
                                                            @else
                                                                <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ asset('build/assets/images/placeholders/files2.jpeg') }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">             
                                                            @endif   
                                                        </a>
                                                    @endForeach
                                                @else
                                                    <div class="font-medium text-slate-400">
                                                            No Upload File Found
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        
                                        <td class="w-100">
                                            <div class="flex items-center justify-center">
                                                @if($task->task->updated_at)
                                                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i>{{ date("F jS, Y",strtotime($task->task->updated_at)) }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="table-report__action w-56">
                                            <div class="flex justify-center items-center">
                                                <button data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" data-plantaskid="{{ $task->task->id }}" class="flex items-center mr-3 task-upload__Button" href="">
                                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> upload
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END: Weekly Top Products -->
                        </div>
                    </div>
                </div>
                @foreach($planDateList as $dateList)
                <div data-tw-merge class="accordion-item bg-slate-200 py-4 first:-mt-4 last:-mb-4 [&amp;:not(:last-child)]:border-b [&amp;:not(:last-child)]:border-slate-200/60 [&amp;:not(:last-child)]:dark:border-darkmode-400 p-4 first:mt-0 last:mb-0 border border-slate-200/60 mt-3 dark:border-darkmode-400">
                    <div class="accordion-header" id="faq-accordion-7">
                        <button data-tw-merge data-tw-toggle="collapse" data-tw-target="#faq-accordion-7-collapse" type="button" aria-expanded="true" aria-controls="faq-accordion-7-collapse" class="accordion-button outline-none inline-flex justify-between py-4 -my-4 font-medium w-full text-left dark:text-slate-400 [&amp;:not(.collapsed)]:text-primary [&amp;:not(.collapsed)]:dark:text-slate-300 collapsed"><div class="flex-none">{{ date("F jS, Y",strtotime($dateList->date)) }} - {{ $dateList->name }}</div> <div class="accordian-lucide flex-none"><i data-lucide="plus" class="w-4 h-4"></i></div></button>
                    </div>
                    <div id="faq-accordion-7-collapse" aria-labelledby="faq-accordion-7" class="accordion-collapse collapse mt-3 text-slate-700 leading-relaxed dark:text-slate-400 [&.collapse:not(.show)]:hidden [&.collapse.show]:visible">
                        <div data-tw-merge class="accordion-body leading-relaxed text-slate-600 dark:text-slate-500 leading-relaxed text-slate-600 dark:text-slate-500">
                            <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
                                
                                <div class="ml-auto w-full sm:w-auto flex mt-4 sm:mt-0">
                                    <button data-tw-merge data-plandataid={{ $dateList->id }} class="activity-call transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed border-secondary text-slate-500 dark:border-darkmode-100/40 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-secondary/20 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-100/10 mb-2 mr-1"><i data-lucide="activity" class="w-4 h-4 mr-1"></i> Add an activity or resource
                                        <span class="ml-2 h-4 w-4" style="display: none">
                                            <svg class="w-full h-full" width="25" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="1a202c">
                                                <circle cx="15" cy="15" r="15">
                                                    <animate values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" attributeName="r" from="15" to="15" begin="0s" dur="0.8s" calcMode="linear" repeatCount="indefinite" />
                                                    <animate values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" calcMode="linear" repeatCount="indefinite" />
                                                </circle>
                                                <circle cx="60" cy="15" r="9" fill-opacity="0.3">
                                                    <animate values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" values="9;15;9" attributeName="r" from="9" to="9" begin="0s" dur="0.8s" calcMode="linear" repeatCount="indefinite" />
                                                    <animate values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" values=".5;1;.5" attributeName="fill-opacity" from="0.5" to="0.5" begin="0s" dur="0.8s" calcMode="linear" repeatCount="indefinite" />
                                                </circle>
                                                <circle cx="105" cy="15" r="15">
                                                    <animate values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" values="15;9;15" attributeName="r" from="15" to="15" begin="0s" dur="0.8s" calcMode="linear" repeatCount="indefinite" />
                                                    <animate values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" values="1;.5;1" attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" calcMode="linear" repeatCount="indefinite" />
                                                </circle>
                                            </svg>
                                        </span></button>
                                </div>
                            </div>
                            <!-- END: Activity Product List -->
                            
                            <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                                <table class="table table-report sm:mt-2">
                                    <thead>
                                        <tr>
                                            <th class="whitespace-nowrap" colspan="2">NAME</th>
                                            <th class="text-center whitespace-nowrap">UPLOADS</th>
                                            <th class="text-center whitespace-nowrap">AVAILABLE FROM</th>
                                            <th class="text-center whitespace-nowrap">ACTION</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($planDates[$dateList->id]))
                                        @php
                                            $moduleContent = $planDates[$dateList->id];
                                        @endphp
                                            @foreach ($moduleContent->task  as $task) 
                                            
                                                @php
                                                if ($task->logo !== null && Storage::disk('google')->exists('public/activity/'.$task->logo)) {
                                                    $logoUrl = Storage::disk('google')->url('public/activity/'.$task->logo);
                                                } else {
                                                    $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
                                                }
                                                $rand = rand(0,1);
                                                @endphp
                                                {{-- @if($dateList->id == $keyDate) --}}
                                                    <tr class="intro-x">
                                                        <td class="w-20">
                                                            <div class="flex">
                                                                <div class="w-10 h-10 image-fit zoom-in">
                                                                    <img alt="Midone - HTML Admin Template" class="rounded-full" src="{{ $logoUrl }}">
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a href="" class="font-medium whitespace-nowrap">{{ $task->name }}</a>
                                                            <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{!! $task->description !!}</div>
                                                        </td>
                                                        <td class="w-40">
                                                            <div class="flex">
                                                                
                                                                @if(($moduleContent->taskUploads))
                                                                        
                                                                    @foreach($moduleContent->taskUploads as $upload)
                                                                    <a target="_blank" href="{{ Storage::disk('google')->url('public/plans/plan_date_list/'.$dateList->id.'/'.$upload->current_file_name) }}" class="w-10 h-10 image-fit zoom-in -ml-5" >
                                                                        {{-- <div class="w-10 h-10 image-fit zoom-in"> --}}
                                                                            @if($upload->doc_type!="pdf" && $upload->doc_type!="xls" && $upload->doc_type!="doc" && $upload->doc_type!="docx")
                                                                                
                                                                                    <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ Storage::disk('google')->url('public/plans/plan_date_list/'.$dateList->id.'/'.$upload->current_file_name) }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">
                                                                                
                                                                                @else
                                                                                    <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ asset('build/assets/images/placeholders/files2.jpeg') }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">
                                                                                
                                                                            @endif
                                                                        {{-- </div> --}}
                                                                    </a>
                                                                    @endForeach
                                                                @else
                                                                        <div class="font-medium text-slate-400">
                                                                            No Upload File Found
                                                                        </div>
                                                                @endif
                                                                
                                                            </div>
                                                        </td>
                                                        <td class="w-100">
                                                            <div class="flex items-center justify-center">
                                                                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i>{{ date("F jS, Y",strtotime($task->availibility_at)) }}
                                                            </div>
                                                        </td>
                                                        <td class="table-report__action w-56">
                                                            <div class="flex justify-center items-center">
                                                                <a href="#"   class="flex items-center mr-3" href="">
                                                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i> Edit
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                {{-- @endif --}}
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- BEGIN: Import Modal -->
<div id="addActivityModal" class="modal" size="xl" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class=" modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">SELECT AN ACTIVITY</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <div id="activit-contentlist" class="grid grid-cols-12 gap-5 mt-5 pt-5"></div>
            </div>
        </div>
    </div>
</div>
<!-- END: Import Modal -->

<!-- BEGIN: Import Modal -->
<div id="addStudentPhotoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                <a data-tw-dismiss="modal" href="javascript:;">
                    <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                </a>
            </div>
            <div class="modal-body">
                <form method="post" action="{{ route('plan-taskupload.store') }}" class="dropzone" id="addStudentPhotoForm" enctype="multipart/form-data">
                    @csrf
                    <div class="fallback">
                        <input type="hidden" name="documents" type="file" />
                    </div>
                    <div class="dz-message" data-dz-message>
                        <div class="text-lg font-medium">Drop file here or click to upload.</div>
                        <div class="text-slate-500">
                            Select .jpg, .png, or .gif formate image. Max file size should be 5MB.
                        </div>
                    </div>
                    <input type="hidden" name="plan_task_id" value=""/>
                </form>
            </div>
            <div class="modal-footer">
                <button id="uploadStudentPhotoBtn" type="button" class="btn btn-outline-success w-20 mr-1">Upload
                    <span class="ml-2 h-4 w-4" style="display: none">
                        <svg class="w-full h-full" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform type="rotate" attributeName="transform" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite" />
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </span>
                </button>
                <button type="button" data-tw-dismiss="modal" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
            </div>
        </div>
    </div>
</div>
<!-- END: Import Modal -->
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

@section('script')
    @vite('resources/js/plan-tasks.js')
@endsection