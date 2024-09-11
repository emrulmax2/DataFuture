<div class="intro-y bg-slate-200 box">
    <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
        <h2 class="font-medium text-base mr-auto">Module Documents</h2>
        <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#confirmModalPlanTask" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} class="callModalPlanTask ml-auto transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2"><i data-lucide="activity" class="w-4 h-4 mr-1"></i> Syncronize Documents</button>
    </div>
    <div class="p-5 pt-0">
        <div class="grid grid-cols-12 gap-4">
            @foreach ($planTasks as $task) 
                @php
                    if ($task->task->logo !== null && Storage::disk('s3')->exists('public/activity/'.$task->task->logo)) {
                        $logoUrl = Storage::disk('s3')->temporaryUrl('public/activity/'.$task->task->logo, now()->addMinutes(120));
                    } else {
                        $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
                    }

                    $FullName = isset($task->task->user) ? $task->task->user->employee->full_name : '';
                    $lastUpdate = ($task->task->updated_at) ?? $task->task->created_at;
                    $userProfileImage =$task->task->createdBy->employee->photo_url;
                    $onlyTaskCreatorFound =1;
                    $rand = rand(0,1);

                    $required_date = '';
                    $days_reminder = (isset($task->task->days_reminder) && $task->task->days_reminder > 0 ? $task->task->days_reminder : 0);
                    $class_start = (isset($plan->attenTerm->start_date) && !empty($plan->attenTerm->start_date) ? date('Y-m-d', strtotime($plan->attenTerm->start_date)) : '');
                    if(!empty($class_start)):
                        $required_date = date('jS F, Y', strtotime('+'.$days_reminder.' days', strtotime($class_start)));
                    endif;

                    $document = [];
                @endphp
                <div class="intro-y col-span-12 sm:col-span-3">
                    <div class="box">
                        <div class="p-5">
                            <div class="relative h-40 overflow-hidden rounded-md before:absolute before:left-0 before:top-0 before:z-10 before:block before:h-full before:w-full before:bg-gradient-to-t before:from-black before:to-black/10 2xl:h-56">
                                <img class="rounded-md absolute h-30 w-auto m-auto l-0 r-0 t-0 b-0" src="{{ $logoUrl }}" alt="{{ $task->task->name }}">
                                {{--<span class="absolute top-0 z-10 m-5 rounded bg-pending/80 px-2 py-1 text-xs text-white">
                                    {{ $task->task->category }}
                                </span>--}}
                                <div class="absolute bottom-0 z-10 px-5 pb-6 text-white">
                                    <a class="block text-base font-medium mt-3 " href="">
                                        <span class="text-lg text-white/90">{{ $task->task->name }}</span>
                                    </a>
                                </div>
                            </div>
                            <div class="mt-5 text-slate-600 dark:text-slate-500">
                                <div class="flex items-center">
                                    <i data-lucide="calendar-days" class="w-4 h-4 mr-2"></i>
                                    Upload Required By: {{ (!empty($required_date) ? $required_date : '')}}
                                </div>

                                @if($task->taskUploads->isNotEmpty())
                                    @foreach($task->taskUploads as $upload)
                                        @php
                                            $document['type'] = $upload->doc_type;
                                            $document['url'] = Storage::disk('s3')->temporaryUrl('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name, now()->addMinutes(120))
                                        @endphp
                                        <div class="mt-2 flex items-center">
                                            <i class="w-4 h-4 mr-2" data-lucide="user"></i>
                                            Uploaded By: {{ $upload->createdBy->employee->full_name }}
                                        </div>
                                        <div class="mt-2 flex items-center">
                                            <i class="w-4 h-4 mr-2" data-lucide="clock"></i>
                                            Uploaded At: {{ (isset($upload->created_at) && !empty($upload->created_at) ? date('jS F, Y', strtotime($upload->created_at)) : '') }}
                                        </div>
                                        @php
                                            $userProfileImage = $upload->createdBy->employee->photo_url;
                                        @endphp
                                    @endForeach
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center justify-center border-t border-slate-200/60 p-5 dark:border-darkmode-400 lg:justify-end">
                            @if(!empty($document) && count($document) > 0 && isset($document['url']) && !empty($document['url']))
                                <a target="_blank" href="{{ $document['url'] }}" class="mr-auto flex items-center text-success">
                                    @if($document['type'] !="pdf" && $document['type']!="xls" && $document['type']!="doc" && $document['type']!="docx")
                                        <i data-lucide="file-down" class="w-4 h-4 mr-2"></i>
                                    @else
                                        <i data-lucide="image-down" class="w-4 h-4 mr-2"></i>
                                    @endif   
                                    Download File
                                </a>
                            @else
                                <a class="mr-auto flex items-center text-slate-400" href="javascript:void(0);">
                                    <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> File Not Available
                                </a>
                            @endif
                            <a data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" data-plantaskid="{{ $task->task->id }}" class="task-upload__Button btn btn-sm btn-success text-white" href="javascript:void(0);">
                                <i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i>
                                Upload
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!--<div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
            <table class="table table-report sm:mt-2">
                <thead>
                    <tr>
                        <th class="whitespace-nowrap w-20">#</th>
                        <th class="whitespace-nowrap">NAME</th>
                        <th class="text-center whitespace-nowrap">UPLOADS</th>
                        <th class="text-center whitespace-nowrap">UPLOADED BY</th>
                        <th class="text-center whitespace-nowrap">ACTIONS</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($planTasks as $task) 
                    
                    @php
                    if ($task->task->logo !== null && Storage::disk('s3')->exists('public/activity/'.$task->task->logo)) {
                        $logoUrl = Storage::disk('s3')->temporaryUrl('public/activity/'.$task->task->logo, now()->addMinutes(120));

                    } else {
                        $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
                    }

                    $FullName = isset($task->task->user) ? $task->task->user->employee->full_name : '';
                    $lastUpdate = ($task->task->updated_at) ?? $task->task->created_at;
                    $userProfileImage =$task->task->createdBy->employee->photo_url;
                    $onlyTaskCreatorFound =1;
                    $rand = rand(0,1);
                    @endphp
                        <tr class="intro-x">
                            <td class="w-20">
                                <div class="flex">
                                    <div class="w-10 h-10 image-fit zoom-in">
                                        <img alt="London Churchill College" class="tooltip rounded-full" src="{{ $logoUrl }}" title="Uploaded at {{ date("Y m d") }}">
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="" class="font-medium whitespace-nowrap">{{ $task->task->name }}</a>
                                <div class="text-slate-500 text-xs whitespace-nowrap mt-0.5">{{ $task->task->category }}</div>
                            </td>
                            <td class="w-40">
                                <div class="flex">
                                    
                                    @if($task->taskUploads->isNotEmpty())
                                    
                                        @foreach($task->taskUploads as $upload)
                                        
                                        @php
                                            $tempUrl = Storage::disk('s3')->temporaryUrl('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name, now()->addMinutes(120))
                                        @endphp
                                            <a target="_blank" href="{{ $tempUrl }}" class="w-10 h-10 image-fit zoom-in -ml-5" >
                                                
                                                @if($upload->doc_type!="pdf" && $upload->doc_type!="xls" && $upload->doc_type!="doc" && $upload->doc_type!="docx")
                                                    <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ $tempUrl }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">           
                                                @else
                                                    <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ asset('build/assets/images/placeholders/files2.jpeg') }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">             
                                                @endif   
                                            </a>
                                            @php
                                                $userProfileImage =$upload->createdBy->employee->photo_url;
                                                $FullName = $upload->createdBy->employee->full_name;
                                                $lastUpdate = $upload->created_at;
                                                $onlyTaskCreatorFound = 0;
                                            @endphp
                                        @endForeach
                                    @else
                                        <div class="font-medium text-slate-400">
                                                No Uploaded File Found
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="flex">
                                @if(!$onlyTaskCreatorFound)
                                    <div class="flex items-center px-5 py-5 mx-auto">
                                        <div class="image-fit h-10 w-10 flex-none overflow-hidden rounded-full">
                                            <img src="{{ $userProfileImage }}" alt="{{ $FullName }}">
                                        </div>
                                        <div class="ml-4 mr-auto">
                                            <div class="font-medium">{{ $FullName }}</div>
                                            <div class="mt-0.5 text-xs text-slate-500">
                                                {{ date("F jS, Y",strtotime($lastUpdate)) }}
                                            </div>
                                        </div>
                                        <div class="text-success ml-2">
                                            <i data-lucide="check-square" class="w-4 h-4 mr-1"></i>
                                        </div>
                                    </div>
                                @else
                                <div class="flex items-center px-5 py-5 mx-auto ">
                                    <div class="font-medium text-slate-400">
                                            N/A
                                    </div>
                                </div>
                                @endif
                            </td>
                            <td class="table-report__action w-56">
                                <div class="flex justify-center items-center">
                                    <button data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" data-plantaskid="{{ $task->task->id }}" class="flex items-center mr-3 task-upload__Button" href="">
                                        <i data-lucide="upload-cloud" class="w-4 h-4 mr-1"></i> upload
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>-->
    </div>
</div>
<!-- <div data-tw-merge class="accordion p-5 mt-5">
    <div data-tw-merge class="bg-slate-200 accordion-item py-4 first:-mt-4 last:-mb-4 [&amp;:not(:last-child)]:border-b [&amp;:not(:last-child)]:border-slate-200/60 [&amp;:not(:last-child)]:dark:border-darkmode-400 p-4 first:mt-0 last:mb-0 border border-slate-200/60 mt-3 dark:border-darkmode-400">
        <div class="accordion-header" id="faq-accordion-5">
            <button data-tw-merge data-tw-toggle="collapse" data-tw-target="#faq-accordion-5-collapse" type="button" aria-expanded="true" aria-controls="faq-accordion-5-collapse" class="accordion-button outline-none inline-flex justify-between py-4 -my-4 font-medium w-full text-left dark:text-slate-400 [&amp;:not(.collapsed)]:text-primary [&amp;:not(.collapsed)]:dark:text-slate-300"><div class="flex-none">Assignment Breief and Important Documents</div> <div class="accordian-lucide flex-none"><i data-lucide="minus" class="w-4 h-4"></i></div></button>
        </div>
        <div id="faq-accordion-5-collapse" aria-labelledby="faq-accordion-5" class="accordion-collapse collapse mt-3 text-slate-700 leading-relaxed dark:text-slate-400 [&.collapse:not(.show)]:hidden [&.collapse.show]:visible show">
            <div data-tw-merge class="accordion-body leading-relaxed text-slate-600 dark:text-slate-500 leading-relaxed text-slate-600 dark:text-slate-500">
                
                <div class="col-span-12 mt-2">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Module Documents</h2>
                        <div class="ml-auto w-full sm:w-auto flex mt-4 sm:mt-0">
                            <button data-tw-merge data-module="Yes" data-tw-toggle="modal" data-tw-target="#confirmModalPlanTask" data-planid={{ $plan->id }} data-moduleCretionId = {{ $plan->module_creation_id }} class="callModalPlanTask transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2"><i data-lucide="activity" class="w-4 h-4 mr-1"></i> Syncronize Documents
                                
                            </button>
                        </div>
                    </div>

                    
                    
                    
                    
                </div>
                
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
                        <button data-tw-merge data-module="No"  data-plandataid={{ $dateList->id }} class="activity-call transition duration-200 border shadow-sm inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-1 mb-2 mr-1"><i data-lucide="activity" class="w-4 h-4 mr-1"></i> Add an activity or resource
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
                                    if ($task->logo !== null && Storage::disk('s3')->exists('public/activity/'.$task->logo)) {
                                        $logoUrl = Storage::disk('s3')->url('public/activity/'.$task->logo);
                                    } else {
                                        $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
                                    }
                                    $rand = rand(0,1);
                                    @endphp
                                        <tr class="intro-x">
                                            <td class="w-20">
                                                <div class="flex">
                                                    <div class="w-10 h-10 image-fit zoom-in">
                                                        <img alt="London Churchill College" class="rounded-full" src="{{ $logoUrl }}">
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
                                                        <a target="_blank" href="{{ Storage::disk('s3')->url('public/plans/plan_date_list/'.$dateList->id.'/'.$upload->current_file_name) }}" class="w-10 h-10 image-fit zoom-in -ml-5" >
                                                      
                                                                @if($upload->doc_type!="pdf" && $upload->doc_type!="xls" && $upload->doc_type!="doc" && $upload->doc_type!="docx")
                                                                    
                                                                        <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ Storage::disk('s3')->url('public/plans/plan_date_list/'.$dateList->id.'/'.$upload->current_file_name) }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">
                                                                    
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
                                
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endforeach
    END: Activity Product List
</div> -->

 <!-- BEGIN: Plan Task  Confirm Modal Content -->
 <div id="confirmModalPlanTask" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="info" class="w-16 h-16 text-success mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 title">Are you sure?</div>
                    <div class="text-slate-500 mt-2 description"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                    <button type="button" data-id="0" data-action="none" class="agreeWithPlanTask btn btn-primary w-auto">Yes, I agree</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Plan Task Confirm Modal Content -->