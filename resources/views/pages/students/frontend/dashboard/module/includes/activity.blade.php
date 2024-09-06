<div data-tw-merge class="accordion p-5 mt-5">
    <div data-tw-merge class="bg-slate-200 accordion-item py-4 first:-mt-4 last:-mb-4 [&amp;:not(:last-child)]:border-b [&amp;:not(:last-child)]:border-slate-200/60 [&amp;:not(:last-child)]:dark:border-darkmode-400 p-4 first:mt-0 last:mb-0 border border-slate-200/60 mt-3 dark:border-darkmode-400">
        <div class="accordion-header" id="faq-accordion-5">
            <button data-tw-merge data-tw-toggle="collapse" data-tw-target="#faq-accordion-5-collapse" type="button" aria-expanded="true" aria-controls="faq-accordion-5-collapse" class="accordion-button outline-none inline-flex justify-between py-4 -my-4 font-medium w-full text-left dark:text-slate-400 [&amp;:not(.collapsed)]:text-primary [&amp;:not(.collapsed)]:dark:text-slate-300"><div class="flex-none">Assignment Breief and Important Documents</div> <div class="accordian-lucide flex-none"><i data-lucide="minus" class="w-4 h-4"></i></div></button>
        </div>
        <div id="faq-accordion-5-collapse" aria-labelledby="faq-accordion-5" class="accordion-collapse collapse mt-3 text-slate-700 leading-relaxed dark:text-slate-400 [&.collapse:not(.show)]:hidden [&.collapse.show]:visible show">
            <div data-tw-merge class="accordion-body leading-relaxed text-slate-600 dark:text-slate-500 leading-relaxed text-slate-600 dark:text-slate-500">
                <!-- BEGIN: Module Documents -->
                <div class="col-span-12 mt-6">
                    <div class="intro-y block sm:flex items-center h-10">
                        <h2 class="text-lg font-medium truncate mr-5">Module Documents</h2>
                        <div class="ml-auto w-full sm:w-auto flex mt-4 sm:mt-0"></div>
                    </div>
                    <!-- END: Activity Product List -->
                    <div class="intro-y overflow-auto lg:overflow-visible mt-8 sm:mt-0">
                        <table class="table table-report sm:mt-2">
                            <thead>
                                <tr>
                                    <th class="whitespace-nowrap w-20">#</th>
                                    <th colspan="4" class="whitespace-nowrap">NAME</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($planTasks as $task) 
                                
                                @php
                                if ($task->task->logo !== null && Storage::disk('s3')->exists('public/activity/'.$task->task->logo)) {
                                    $logoUrl = Storage::disk('s3')->url('public/activity/'.$task->task->logo);
                                } else {
                                    $logoUrl = asset('build/assets/images/placeholders/200x200.jpg');
                                }

                                //dd($task->task);

                                $FullName = (isset($task->task->user)) ? $task->task->user->employee->full_name : "";
                                $lastUpdate = ($task->task->updated_at) ?? $task->task->created_at;
                                $rand = rand(0,1);
                                if($task->taskUploads->isNotEmpty())
                                    $upload = $task->taskUploads[0];
                                else
                                    $upload = null;
                                @endphp
                                    @if($upload)
                                    <a target="_blank" href="{{ Storage::disk('s3')->url('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name) }}" class="w-10 h-10 image-fit zoom-in -ml-5" >              
                                    @endif
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
                                            {{-- <div class="flex">
                                                @if(($task->taskUploads))
                                                    @foreach($task->taskUploads as $upload)
                                                        <a target="_blank" href="{{ Storage::disk('s3')->url('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name) }}" class="w-10 h-10 image-fit zoom-in -ml-5" >
                                                            
                                                            @if($upload->doc_type!="pdf" && $upload->doc_type!="xls" && $upload->doc_type!="doc" && $upload->doc_type!="docx")
                                                                <img alt="{{ display_file_name }}" class="tooltip rounded-full" src="{{ Storage::disk('s3')->url('public/plans/plan_task/'.$task->task->id.'/'.$upload->current_file_name) }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">           
                                                            @else
                                                                <img alt="{{ $upload->display_file_name }}" class="tooltip rounded-full" src="{{ asset('build/assets/images/placeholders/files2.jpeg') }}" title="Uploaded at {{ date("F jS, Y",strtotime($upload->created_at)) }}">             
                                                            @endif   
                                                        </a>
                                                        @php
                                                            $FullName = $upload->createdBy->employee->full_name;
                                                            $lastUpdate = $upload->created_at;
                                                        @endphp
                                                    @endForeach
                                                @else
                                                    <div class="font-medium text-slate-400">
                                                            No Upload File Found
                                                    </div>
                                                @endif
                                            </div> --}}
                                        </td>
                                        
                                        <td>
                                            {{-- <div class="flex items-center px-5 py-5">
                                                <div class="image-fit h-10 w-10 flex-none overflow-hidden rounded-full">
                                                    <img src="" alt="">
                                                </div>
                                                <div class="ml-4 mr-auto">
                                                    <div class="font-medium">{{ $FullName }}</div>
                                                    <div class="mt-0.5 text-xs text-slate-500">
                                                        {{ date("F jS, Y",strtotime($lastUpdate)) }}
                                                    </div>
                                                </div>
                                                <div class="text-success">
                                                    <i data-lucide="check-square" class="w-4 h-4 mr-1"></i>
                                                </div>
                                            </div> --}}
                                        </td>
                                        {{-- <td class="table-report__action w-56">
                                            <div class="flex justify-center items-center">
                                                <button data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" data-plantaskid="{{ $task->task->id }}" class="flex items-center mr-3 task-upload__Button" href="">
                                                    <i data-lucide="upload-cloud" class="w-4 h-4 mr-1"></i> upload
                                                </button>
                                            </div>
                                        </td> --}}

                                    </tr>
                                    @if($upload)
                                    </a>             
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- END: Module Documents  -->
            </div>
        </div>
    </div>
    {{-- @foreach($planDateList as $dateList)
    <div data-tw-merge class="accordion-item bg-slate-200 py-4 first:-mt-4 last:-mb-4 [&amp;:not(:last-child)]:border-b [&amp;:not(:last-child)]:border-slate-200/60 [&amp;:not(:last-child)]:dark:border-darkmode-400 p-4 first:mt-0 last:mb-0 border border-slate-200/60 mt-3 dark:border-darkmode-400">
        <div class="accordion-header" id="faq-accordion-7">
            <button data-tw-merge data-tw-toggle="collapse" data-tw-target="#faq-accordion-7-collapse" type="button" aria-expanded="true" aria-controls="faq-accordion-7-collapse" class="accordion-button outline-none inline-flex justify-between py-4 -my-4 font-medium w-full text-left dark:text-slate-400 [&amp;:not(.collapsed)]:text-primary [&amp;:not(.collapsed)]:dark:text-slate-300 collapsed"><div class="flex-none">{{ date("F jS, Y",strtotime($dateList->date)) }} - {{ $dateList->name }}</div> <div class="accordian-lucide flex-none"><i data-lucide="plus" class="w-4 h-4"></i></div></button>
        </div>
        <div id="faq-accordion-7-collapse" aria-labelledby="faq-accordion-7" class="accordion-collapse collapse mt-3 text-slate-700 leading-relaxed dark:text-slate-400 [&.collapse:not(.show)]:hidden [&.collapse.show]:visible">
            <div data-tw-merge class="accordion-body leading-relaxed text-slate-600 dark:text-slate-500 leading-relaxed text-slate-600 dark:text-slate-500">
                <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
                    
                    <!-- <div class="ml-auto w-full sm:w-auto flex mt-4 sm:mt-0">
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
                    </div> -->
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
    @endforeach --}}
</div>