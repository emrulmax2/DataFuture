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
    </div>
</div>


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