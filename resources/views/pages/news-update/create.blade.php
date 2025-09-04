@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Create Nes & Updates</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('news.updates') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To List</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <form method="post" action="#" id="newsUpdateCreateForm" enctype="multipart/form-data">
        <div class="mt-5 grid grid-cols-12 gap-x-4 gap-y-5">
            <div class="col-span-12 sm:col-span-8">
                <div class="intro-y box">
                    <div class="p-5">
                        <div>
                            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
                            <input id="title" type="text" name="title" class="form-control w-full">
                            <div class="acc__input-title error-name text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="addEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-content text-danger mt-2"></div>
                        </div>
                        <div class="mt-4">
                            <div class="flex justify-start items-center relative">
                                <label for="newsUpdateDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                    <i data-lucide="upload-cloud" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                </label>
                                <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" multiple name="documents[]" class="absolute w-0 h-0 overflow-hidden opacity-0" id="newsUpdateDocument"/>
                            </div>
                            <div id="newsUpdateDocumentNames" class="newsUpdateDocumentNames mt-3" style="display: none"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="intro-y box rounded-bl-none rounded-br-none p-5 border-b border-slate-200/60">
                    <div class="form-check form-switch">
                        <input checked id="for_all_students" class="form-check-input" name="for_all_students" value="1" type="checkbox">
                        <label class="form-check-label ml-3 font-medium text-base" for="for_all_students">For All Students</label>
                    </div> 
                </div>
                <div class="intro-y box rounded-tl-none rounded-tr-none">
                    <div class="groupSearchBodyToggler flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400 cursor-pointer">
                        <h2 class="font-medium text-base mr-auto">Group Search</h2>
                        <i data-lucide="chevron-down" class="w-6 h-6 ml-auto"></i>
                    </div>
                    <div class="groupSearchBody p-5" style="display: none;">
                        <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="intake_semester" class="form-label">Intake Semester </label>
                                <select id="intake_semester" class="w-full tom-selects" multiple name="intake_semester[]">
                                    <option value="">Please Select</option>
                                    @if(!empty($semesters))
                                        @foreach($semesters as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="attendance_semester" class="form-label">Attendance Semester </label>
                                <select id="attendance_semester" class="w-full tom-selects" multiple name="attendance_semester[]">
                                    <option value="">Please Select</option>
                                    @if(!empty($terms))
                                        @foreach($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="course" class="form-label">Course </label>
                                <select id="course" class="w-full tom-selects" multiple name="course[]">
                                    <option value="">Please Select</option>
                                    @if(!empty($courses))
                                        @foreach($courses as $crs)
                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="group" class="form-label">Master Group</label>
                                <select id="group" class="w-full tom-selects" multiple name="group[]">
                                    <option value="">Please Select</option>
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="evening_weekend" class="form-label">Evening / Weekend</label>
                                <select id="evening_weekend" class="w-full tom-selects" name="evening_weekend">
                                    <option value="">Please Select</option>
                                    <option value="1">Yes</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="student_type" class="form-label">Student Type</label>
                                <select id="student_type" class="w-full tom-selects" multiple name="student_type[]">
                                    <option value="">Please Select</option>
                                    <option value="UK">UK</option>
                                    <option value="BOTH">BOTH</option>
                                    <option value="OVERSEAS">OVERSEAS</option></option>
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="group_student_status" class="form-label">Student Status</label>
                                <select id="group_student_status" class="w-full tom-selects" name="group_student_status[]" multiple>
                                    <option value="">Please Select</option>
                                    @if(!empty($allStatuses))
                                        @foreach($allStatuses as $sts)
                                            <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-span-12 sm:col-span-6 pt-7 text-right">
                                <button id="groupStudentSearch" type="button" class="btn btn-success text-white w-auto">
                                    <i class="w-4 h-4 mr-2" data-lucide="search"></i> Search 
                                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                        stroke="white" class="w-4 h-4 ml-2 theLoader">
                                        <g fill="none" fill-rule="evenodd">
                                            <g transform="translate(1 1)" stroke-width="4">
                                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                                </path>
                                            </g>
                                        </g>
                                    </svg>
                                </button>
                            </div>
                            <input type="hidden" id="groupSearchStatus" value="0" class="form-control" name="stataus">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y box mt-5">
            <div class="flex items-center p-5 justify-end">
                <span class="groupSearchCount inline-flex bg-slate-200 text-primary font-medium px-2 py-2 leading-none mr-auto" style="display: none;">0 Students Found</span>
                <a href="{{ route('news.updates') }}" class="btn btn-danger w-auto mr-1">Cancel</a>
                <button type="submit" id="saveNewsUpdts" class="btn btn-primary w-auto">     
                    Submit                      
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                        stroke="white" class="w-4 h-4 ml-2">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="4">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                        to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </g>
                        </g>
                    </svg>
                </button>
            </div>
        </div>

        <div class="intro-y box mt-5 p-5 studentSearchResult" style="display: none;">
            
        </div>
    </form>
    <!-- END: Settings Page Content -->

    
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
                        <button type="button" data-action="none"  data-red="" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/news-updates-create.js')
@endsection