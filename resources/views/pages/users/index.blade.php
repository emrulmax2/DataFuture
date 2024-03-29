@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">All Users</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            {{-- <button data-tw-toggle="modal" data-tw-target="#addUserModal" type="button" class="add_btn btn btn-primary shadow-md mr-2">Add New User</button> --}}
            <a href="{{ route('employee.create') }}" class="add_btn btn btn-primary shadow-md mr-2">Add New User</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
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
            <div id="userListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    <!-- BEGIN: Add Modal -->
    <div id="addUserModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog  modal-lg">
            <form method="POST" action="#" id="addUserForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add User</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-4">
                                <div class="w-40 h-40 flex-none image-fit relative">
                                    <img alt="User Photo" class="rounded-full userImageAdd" id="userImageAdd" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                    <label for="userPhotoAdd" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-3  cursor-pointer">
                                        <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="absolute w-0 h-0 overflow-hidden opacity-0" id="userPhotoAdd"/>
                                </div>
                            </div>
                            <div class="col-span-8">
                                <div class="grid grid-cols-12 gap-0 gap-x-4">
                                    <div class="col-span-12">
                                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input id="name" type="text" name="name" class="form-control w-full">
                                        <div class="acc__input-error error-name text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input id="email" type="email" name="email" class="form-control w-full">
                                        <div class="acc__input-error error-email text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12">
                                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                        <input id="password" type="password" name="password" class="form-control w-full">
                                        <div class="acc__input-error error-password text-danger mt-2"></div>
                                    </div>
                                    <div  class="col-span-12">
                                        <label for="conf_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input id="conf_password" type="password" name="conf_password" class="form-control w-full">
                                        <div class="acc__input-error error-conf_password text-danger mt-2"></div>
                                    </div>
                                    <div  class="col-span-12">
                                        <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                        <select id="gender" name="gender" class="form-control w-full">
                                            <option value="">Please Select</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="acc__input-error error-gender text-danger mt-2"></div>
                                    </div>
                                    <div  class="col-span-12">
                                        <label for="role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select id="role_id" name="role_id[]" data-placeholder="Please Select" class="tom-selects w-full" multiple>
                                            @if(!empty($roles))
                                                @foreach($roles as $rl)
                                                    <option value="{{ $rl->id }}">{{ $rl->display_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-role_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="float: left;" class="mt-1">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="active">Status</label>
                                <input id="active" class="form-check-input" name="active" value="1" type="checkbox">
                            </div>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveUser" class="btn btn-primary w-auto">     
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
    <div id="editUsersModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editUsersForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit User</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-4">
                                <div class="w-40 h-40 flex-none image-fit relative">
                                    <img alt="User Photo" class="rounded-full userImageEdit" id="userImageEdit" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                    <label for="userPhotoEdit" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-3  cursor-pointer">
                                        <i data-lucide="camera" class="w-4 h-4 text-white"></i>
                                    </label>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="absolute w-0 h-0 overflow-hidden opacity-0" id="userPhotoEdit"/>
                                </div>
                            </div>
                            <div class="col-span-8">
                                <div class="grid grid-cols-12 gap-0 gap-x-4">
                                    <div class="col-span-12">
                                        <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input id="edit_name" type="text" name="name" class="form-control w-full">
                                        <div class="acc__input-error error-name text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12">
                                        <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input id="edit_email" type="email" name="email" class="form-control w-full">
                                        <div class="acc__input-error error-email text-danger mt-2"></div>
                                    </div>
                                    <!--<div class="col-span-12">
                                        <label for="edit_password" class="form-label">New Password</label>
                                        <input id="edit_password" type="password" name="password" class="form-control w-full">
                                        <div class="acc__input-error error-password text-danger mt-2"></div>
                                    </div>
                                    <div  class="col-span-12">
                                        <label for="edit_conf_password" class="form-label">Confirm New Password</label>
                                        <input id="edit_conf_password" type="password" name="password_confirmation" class="form-control w-full">
                                        <div class="acc__input-error error-password_confirmation text-danger mt-2"></div>
                                    </div>-->
                                    <div  class="col-span-12">
                                        <label for="edit_gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                        <select id="edit_gender" name="gender" class="form-control w-full">
                                            <option value="">Please Select</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                            <option value="Other">Other</option>
                                        </select>
                                        <div class="acc__input-error error-gender text-danger mt-2"></div>
                                    </div>
                                    <div  class="col-span-12">
                                        <label for="edit_role_id" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select id="edit_role_id" name="role_id[]" data-placeholder="Please Select" class="tom-selects w-full" multiple>
                                            @if(!empty($roles))
                                                @foreach($roles as $rl)
                                                    <option value="{{ $rl->id }}">{{ $rl->display_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-role_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div style="float: left;" class="mt-1">
                            <div class="form-check form-switch">
                                <label class="form-check-label mr-3 ml-0" for="edit_active">Status</label>
                                <input id="edit_active" class="form-check-input" name="active" value="1" type="checkbox">
                            </div>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateUser" class="btn btn-primary w-auto">
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
    @vite('resources/js/user.js')
@endsection