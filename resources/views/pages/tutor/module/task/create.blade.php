@extends('../layout/' . $layout)

@section('subhead')
    <title>Add New Activity - London Churchill College</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Add A Module Document</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('tutor-dashboard.plan.module.show',$plan->id) }}" class="transition duration-200 border shadow-md inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-secondary/70 border-secondary/70 text-slate-500 dark:border-darkmode-400 dark:bg-darkmode-400 dark:text-slate-300 [&amp;:hover:not(:disabled)]:bg-slate-100 [&amp;:hover:not(:disabled)]:border-slate-100 [&amp;:hover:not(:disabled)]:dark:border-darkmode-300/80 [&amp;:hover:not(:disabled)]:dark:bg-darkmode-300/80  mb-2 mr-2  w-24">
                <i class="w-4 h-4 ml-2" data-lucide="arrow-left"></i> Back 
            </a>
            <button id="moduleCreateSave" data-tw-merge class="transition duration-200 border shadow-md inline-flex items-center justify-center py-2 px-3 rounded-md font-medium cursor-pointer focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus-visible:outline-none dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&amp;:hover:not(:disabled)]:bg-opacity-90 [&amp;:hover:not(:disabled)]:border-opacity-90 [&amp;:not(button)]:text-center disabled:opacity-70 disabled:cursor-not-allowed bg-primary border-primary text-white dark:border-primary mb-2 mr-1 ">Save
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
        </div>
    </div>
    <div class="pos intro-y grid grid-cols-12 gap-5 mt-5">
        <!-- BEGIN: Post Content -->
        <div class="intro-y col-span-12 lg:col-span-8">
            <input name="title" type="text" class="intro-y form-control py-3 px-4 box pr-10" placeholder="Title">
            <div class="acc__input-error error-title text-danger mt-2"></div>
            <div class="post intro-y overflow-hidden box mt-5">
                <ul class="post__tabs nav nav-tabs flex-col sm:flex-row bg-slate-200 dark:bg-darkmode-800" role="tablist">
                    <li class="nav-item">
                        <button title="Fill in the article content" data-tw-toggle="tab" data-tw-target="#content" class="nav-link tooltip w-full sm:w-40 py-4 active" id="content-tab" role="tab" aria-controls="content" aria-selected="true">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Content
                        </button>
                    </li>
                </ul>
                <div class="post__content tab-content">
                    <div id="content" class="tab-pane p-5 active" role="tabpanel" aria-labelledby="content-tab">
                        <div class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-5">
                            
                            <div class="mt-5">
                                <textarea id="addEditor" name="remarks"></textarea>
                            </div>
                        </div>
                        <div class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-5 mt-5 bg-slate-300">
                            <div class="font-medium flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5">
                                 Uploads
                            </div>
                            <div class="mt-5">
                                <div class="mt-3">
                                    
                                    <form method="post"  action="{{ route('plan-taskupload.store') }}" class="dropzone bg-slate-100" id="uploadDocumentForm" enctype="multipart/form-data">
                                        @csrf
                                        <div class="fallback">
                                            <input type="hidden" name="documents" type="file" />
                                        </div>
                                        <div class="dz-message" data-dz-message>
                                            <div class="text-lg font-medium">Drop file here or click to upload.</div>
                                            <div class="text-slate-500">
                                                 Max file size should be 10MB.
                                            </div>
                                        </div>
                                        <input type="hidden" name="plans_date_list_id" value=""/>
                                        <input type="hidden" name="plan_id" value="{{ $plan->id }}"/>
                                        <input type="hidden" name="activity_settings_id" value="{{ $EActivitySettings->id }}"/>
                                        <input type="hidden" name="name" />
                                        <input type="hidden" name="description" />
                                        <input type="hidden" name="availibility_at" />
                                        <input type="hidden" name="plan_task_id" />
                                    </form> 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- END: Post Content -->
        <!-- BEGIN: Post Info -->
        <div class="col-span-12 lg:col-span-4">
            <div class="intro-y box p-5">
                <div> 
                    @php

                    $employeeUser = cache()->get('employeeCache') ?? Auth::user()->load('employee'); 

                    @endphp
                    <label class="form-label">Created By</label>
                    <div class="dropdown">
                        <div class="dropdown-toggle btn w-full btn-outline-secondary dark:bg-darkmode-800 dark:border-darkmode-800 flex items-center justify-start" role="button" aria-expanded="false" data-tw-toggle="dropdown">
                            <div class="w-6 h-6 image-fit mr-3">
                                <img class="rounded" alt="{{ $employeeUser->employee->title->name.' '.$employeeUser->employee->first_name.' '.$employeeUser->employee->last_name }}"  src="{{ (isset($employeeUser->employee->photo) && !empty($employeeUser->employee->photo) && Storage::disk('local')->exists('public/employees/'.$employeeUser->employee->id.'/'.$employeeUser->employee->photo) ? Storage::disk('local')->url('public/employees/'.$employeeUser->employee->id.'/'.$employeeUser->employee->photo) : asset('build/assets/images/avater.png')) }}">
                            </div>
                            <div class="truncate">{{ $employeeUser->employee->title->name.' '.$employeeUser->employee->first_name.' '.$employeeUser->employee->last_name }}</div>
                            
                        </div>
                        
                    </div>
                </div>
                <div class="mt-3">
                    <label for="post-form-2" class="form-label inline-flex"><i data-lucide="calendar" class="w-4 h-4 mr-1 mt-1"></i> Published At</label>
                    <input type="text" data-show-current="true" value="{{ date("d-m-Y") }}" class="form-control" readonly name="start_date"  data-single-mode="true">
                </div>
            </div>
        </div>
        <!-- END: Post Info -->
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
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/create-module-activity.js')
@endsection
