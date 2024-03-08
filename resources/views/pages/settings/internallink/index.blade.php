@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">{{ $subtitle }}</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class=" btn btn-primary shadow-md mr-2">Back To Dashboard</a>
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
                    <h2 class="font-medium text-base mr-auto">Internal Site List</h2>
                    <button data-tw-toggle="modal" data-tw-target="#uploadEmployeeDocumentModal" type="button" class=" btn btn-primary ml-auto shadow-md"><i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Site Link</button>
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
                                        <li>
                                            <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="awardingbodyTableId" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->

    <!-- BEGIN: Add Modal -->
    <div id="uploadEmployeeDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Site Link</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="mt-3">   
                        <form method="post"  action="{{ route('internal-link.store') }}" class="dropzone bg-slate-100" id="uploadDocumentForm" enctype="multipart/form-data">
                            @csrf
                            <div class="fallback">
                                <input type="hidden" name="documents" type="file" />
                            </div>
                            <div class="dz-message" data-dz-message>
                                <div class="text-lg font-medium">Drop Image here or click to upload.</div>
                                <div class="text-slate-500">
                                     Max file size should be 10MB.
                                </div>
                            </div>
                                <input type="hidden" name="name" id="nameValue" value=""/>
                                <input type="hidden" name="parent_id" id="parentValue" value=""/>
                                <input type="hidden" name="link" id="linkValue" value=""/>

                                <input type="hidden" name="available_staff" id="available_staffValue" value=""/>
                                <input type="hidden" name="available_student" id="available_studentValue" value=""/>
                                
                                <input type="hidden" name="description" id="descriptionValue" value=""/>
    
                                <input type="hidden" name="start_date" id="start_dateValue" value=""/>
                                <input type="hidden" name="end_date" id="end_dateValue" value=""/>
    
                                <input type="hidden" name="active" id="activeValue" value=""/>
                        </form> 
                        </div>
                        <div class="grid grid-cols-12 gap-6 mt-3">   
                            <div class="col-span-6">     
                                <label for="name_status" class="form-label">Name <span class="text-danger">*</span></label>
                                <input id="name_status" type="text" name="name_status" class="form-control w-full">
                                <div id="error-name_status" class="name_status__input-error error-user text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">       
                                <label for="link_status" class="form-label">Link </label>
                                <input id="link_status" type="url" name="link_status" class="form-control w-full" placeholder="http://gmail.com">
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label">Parent Category</label>
                            <select id="course-01" name="parent_category" class="form-select w-full" >
                                <option value="">Please Select</option>
                                @if(!empty($parents))
                                    @foreach($parents as $crs)
                                        <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="grid grid-cols-12 gap-6 mt-3">
                            <div class="col-span-6">
                                <div class="form-check form-switch">
                                    <label class="form-check-label mr-3 ml-0" for="available_staff_status">Available To Staff</label>
                                    <input id="available_staff_status" class="form-check-input" name="available_staff_status" value="1" type="checkbox">
                                </div>
                            </div>
                            <div class="col-span-6">
                                <div class="form-check form-switch">
                                    <label class="form-check-label mr-3 ml-0" for="available_student_status">Available To Student</label>
                                    <input id="available_student_status" class="form-check-input" name="available_student_status" value="1" type="checkbox">
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label" for="description">Description</label>
                            <textarea name="description_status" id="description_status" class="form-control w-full"></textarea>
                       
                        </div>
                        <div class="grid grid-cols-12 gap-6 mt-3">
                            <div class="col-span-6">
                                <label for="start_date_status" class="form-label">Started On</label>
                                <input id="start_date_status" type="text" name="start_date_status" class="datepicker date-picker form-control w-full" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY"  data-single-mode="true" >
                                <div class="start_date_status__input-error error-start_date_status text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="end_date_status" class="form-label">Ended On</label>
                                <input id="end_date_status" type="text" name="end_date_status" class="datepicker date-picker form-control w-full" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY"  data-single-mode="true" >
                                <div class="end_date_status__input-error error-end_date_status text-danger mt-2"></div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="active_status">Active</label>
                                <input id="active_status" class="form-check-input" name="active_status" value="1" type="checkbox">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="button" id="uploadEmpDocBtn" class="btn btn-primary w-auto">
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
        </div>
    </div>
    <!-- END: Add Modal -->
<!-- BEGIN: Add Modal -->
<div id="uploadEmployeeDocumentModalEdit" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Site Link</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="mt-3">   
                    <form method="post"  action="{{ route('internal-link.update') }}" class="dropzone bg-slate-100" id="uploadDocumentFormEdit" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback">
                            <input type="hidden" name="documents" type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop Image here or click to upload.</div>
                            <div class="text-slate-500">
                                 Max file size should be 10MB.
                            </div>
                        </div>
                        <input type="hidden" name="name" id="nameValue" value=""/>
                            <input type="hidden" name="parent_id" id="parentValue" value=""/>
                            <input type="hidden" name="link" id="linkValue" value=""/>
                            <input type="hidden" name="id" id="idValue" value=""/>
                            
                            <input type="hidden" name="available_staff" id="available_staffValue" value=""/>
                            <input type="hidden" name="available_student" id="available_studentValue" value=""/>
                            
                            <input type="hidden" name="description" id="descriptionValue" value=""/>

                            <input type="hidden" name="start_date" id="start_dateValue" value=""/>
                            <input type="hidden" name="end_date" id="end_dateValue" value=""/>

                            <input type="hidden" name="active" id="activeValue" value=""/>
                    </form> 
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-3">   
                        <div class="col-span-6">     
                            <label for="name_status" class="form-label">Name <span class="text-danger">*</span></label>
                            <input id="name_status" type="text" name="name_status" class="form-control w-full">
                            <div id="error-name_status" class="name_status__input-error error-user text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">       
                            <label for="link_status" class="form-label">Link </label>
                            <input id="link_status" type="url" name="link_status" class="form-control w-full" placeholder="http://gmail.com">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Parent Category</label>
                        <select id="course-01" name="parent_category" class="form-select w-full" >
                            <option value="">Please Select</option>
                            @if(!empty($parents))
                                @foreach($parents as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-3">
                        <div class="col-span-6">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="available_staff_status">Available To Staff</label>
                                <input id="available_staff_status" class="form-check-input" name="available_staff_status" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="col-span-6">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="available_student_status">Available To Student</label>
                                <input id="available_student_status" class="form-check-input" name="available_student_status" value="1" type="checkbox">
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label" for="description_status">Description</label>
                        <textarea name="description_status" id="description_status" class="form-control w-full"></textarea>
                    </div>
                    <div class="grid grid-cols-12 gap-6 mt-3">
                        <div class="col-span-6">
                            <label for="start_date_status" class="form-label">Started On</label>
                            <input id="start_date_status" type="text" name="start_date_status" class="datepicker date-picker form-control w-full" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY"  data-single-mode="true" >
                            <div class="start_date_status__input-error error-start_date_status text-danger mt-2"></div>
                        </div>
                        <div class="col-span-6">
                            <label for="end_date_status" class="form-label">Ended On</label>
                            <input id="end_date_status" type="text" name="end_date_status" class="datepicker date-picker form-control w-full" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY"  data-single-mode="true" >
                            <div class="end_date_status__input-error error-end_date text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="form-check form-switch">
                            <label class="form-check-label mr-3 ml-0" for="active_status">Active</label>
                            <input id="active_status" class="form-check-input" name="active_status" value="1" type="checkbox">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadEmpDocBtnEdit" class="btn btn-primary w-auto">
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
    </div>
</div>
<!-- END: Add Modal -->

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

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/internallink.js')
@endsection