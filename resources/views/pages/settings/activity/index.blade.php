@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To Dashboard</a>
        </div>
    </div>

    <!-- BEGIN: Settings Page Content -->
    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-4 2xl:col-span-3 flex lg:block flex-col-reverse">
            <!-- BEGIN: Profile Info -->
            @include('pages.settings.sidebar')
            <!-- END: Profile Info -->
        </div>

        <div class="col-span-12 lg:col-span-8 2xl:col-span-9">
            <!-- BEGIN: Display Information -->
            <div class="intro-y box lg:mt-5">
                <div class="flex items-center p-5 border-b border-slate-200/60 dark:border-darkmode-400">
                    <h2 class="font-medium text-base mr-auto">E-Learning Activity Settings</h2>
                    <button data-tw-toggle="modal" data-tw-target="#addELearningActivityModal" type="button" class="add_btn btn btn-primary shadow-md ml-auto">Add New</button>
                </div>
                <div class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                                <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="1">Active</option>
                                    <option value="0">In Active</option>
                                    <option value="2">Archived</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                                <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                            </button>
                            <div class="dropdown w-1/2 sm:w-auto">
                                <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-json" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export JSON
                                            </a>
                                        </li> --}}
                                        <li>
                                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                        {{-- <li>
                                            <a id="tabulator-export-html" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export HTML
                                            </a>
                                        </li> --}}
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="ELearningActivityList" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    <!-- BEGIN: Add Modal -->
    <div id="addELearningActivityModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addELearningActivityForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Settings</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-4">
                                <div class="w-30 h-30 flex justify-center items-center rounded-full border relative">
                                    <img alt="User Photo" class="rounded-full userImageAdd" id="userImageAdd" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                    <label for="userPhotoAdd" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-3  cursor-pointer">
                                        <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="logo" class="absolute w-0 h-0 overflow-hidden opacity-0" id="userPhotoAdd"/>
                                </div>
                                <div class="acc__input-error error-logo text-danger mt-2"></div>
                            </div>
                            <div class="col-span-8">
                                <div >
                                    <label for="name" class="form-label">Activity Label <span class="text-danger">*</span></label>
                                    <input id="days_name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="short_code" class="form-label">Short Code <span class="text-danger">*</span></label>
                                    <input id="short_code" type="text" name="short_code" class="form-control w-full">
                                    <div class="acc__input-error error-short_code text-danger mt-2"></div>
                                </div>
                                <div  class="mt-3">
                                    <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select id="category" name="category" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        <option value="General">General</option>
                                        <option value="Assignment Brief">Assignment Brief</option>
                                        <option value="Unit Handbook">Unit Handbook</option>
                                        <option value="Harvard Referencing">Harvard Referencing</option>
                                        <option value="Lecture/Topic">Lecture/Topic</option>
                                    </select>
                                    <div class="acc__input-error error-category text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="has_week" class="form-label">Has Week</label>
                                    <div class="form-check form-switch ml-0">
                                        <input id="has_week" class="form-check-input m-0" name="has_week" value="1" type="checkbox">
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <label for="has_week" class="form-label">Is Mandatory</label>
                                    <div class="form-check form-switch ml-0">
                                        <input id="is_mandatory" class="form-check-input m-0" name="is_mandatory" value="1" type="checkbox">
                                    </div>
                                </div>
                                <div class="mt-3" style="">
                                    <label for="days_reminder" class="form-label">Days for reminders</label>
                                    <input id="days_reminder" type="number" name="days_reminder" class="form-control w-full">
                                    <div class="acc__input-error error-days_reminder text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="active">Active</label>
                            <input id="active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveSettings" class="btn btn-primary w-auto">     
                            Save                      
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
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->
    <!-- BEGIN: Edit Modal -->
    <div id="editELearningActivityModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editELearningActivityForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Setting</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-4">
                                <div class="w-30 h-30 flex justify-center items-center rounded-full border relative">
                                    <img alt="User Photo" class="rounded-full userImageEdit" id="userImageEdit" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                    <label for="userPhotoEdit" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-3  cursor-pointer">
                                        <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="logo" class="absolute w-0 h-0 overflow-hidden opacity-0" id="userPhotoEdit"/>
                                </div>
                                <div class="acc__input-error error-logo text-danger mt-2"></div>
                            </div>
                            <div class="col-span-8">
                                <div class="mt-3" >
                                    <label for="name" class="form-label">Activity Label <span class="text-danger">*</span></label>
                                    <input id="days_name" type="text" name="name" class="form-control w-full">
                                    <div class="acc__input-error error-name text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="edit_short_code" class="form-label">Short Code <span class="text-danger">*</span></label>
                                    <input id="edit_short_code" type="text" name="short_code" class="form-control w-full">
                                    <div class="acc__input-error error-short_code text-danger mt-2"></div>
                                </div>
                                <div >
                                    <label for="edit_category" class="form-label">Category <span class="text-danger">*</span></label>
                                    <select id="edit_category" name="category" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        <option value="General">General</option>
                                        <option value="Assignment Brief">Assignment Brief</option>
                                        <option value="Unit Handbook">Unit Handbook</option>
                                        <option value="Harvard Referencing">Harvard Referencing</option>
                                        <option value="Lecture/Topic">Lecture/Topic</option>
                                    </select>
                                    <div class="acc__input-error error-category text-danger mt-2"></div>
                                </div>
                                <div class="mt-3">
                                    <label for="edit_has_week" class="form-label">Repeat Weekly</label>
                                    <div class="form-check form-switch ml-0">
                                        <input id="edit_has_week" class="form-check-input m-0" name="has_week" value="1" type="checkbox">
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <label for="is_mandatory" class="form-label">Is Mandatory</label>
                                    <div class="form-check form-switch ml-0">
                                        <input id="is_mandatory" class="form-check-input m-0" checked name="is_mandatory" value="1" type="checkbox">
                                    </div>
                                </div>
                                <div class="mt-3" >
                                    <label for="days_reminder" class="form-label">Days for reminders</label>
                                    <input id="days_reminder" type="number" name="days_reminder" class="form-control w-full">
                                    <div class="acc__input-error error-days_reminder text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="form-check form-switch" style="float: left; margin: 7px 0 0;">
                            <label class="form-check-label mr-3 ml-0" for="edit_active">Active</label>
                            <input id="edit_active" class="form-check-input m-0" name="active" checked value="1" type="checkbox">
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" disabled id="updateSettings" class="btn btn-primary w-auto">
                            Update
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
                        <input type="hidden" name="id" value="0" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->
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
    @vite('resources/js/settings.js')
    @vite('resources/js/e-learning-activity-settings.js')
@endsection