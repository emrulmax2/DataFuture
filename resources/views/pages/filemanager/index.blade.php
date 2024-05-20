@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">File Manager</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            @if($parent_id > 0 && (isset($theFolder->folder_permission->create) && $theFolder->folder_permission->create == 1))
                <button type="button" data-tw-toggle="modal" data-tw-target="#addFolderModal" class="add_btn btn btn-primary shadow-md mr-2">New Folder</button>
                <button type="button" data-tw-toggle="modal" data-tw-target="#addFileModal" class="add_btn btn btn-primary shadow-md mr-2">Upload File</button>
            @endif
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-5">
            @if(!empty($folders))
                @foreach($folders as $folder)
                    @php 
                        $parameters = (!empty($params) ? explode('/', $params) : []);
                        $parameters[] = $folder->slug;

                        $parameters = implode('/', $parameters);
                    @endphp
                    <div data-href="{{ route('file.manager', $parameters) }}" class="intro-y cursor-pointer folderWrap block col-span-2">
                        <div class="folder bg-slate-200 p-2 rounded text-center">
                            <div class="folderHeader flex w-full justify-between py-3 px-2">
                                <h3 class="font-medium inline-flex items-center"><i data-lucide="arrow-big-down-dash" class="w-4 h-4 mr-1"></i>{{ $folder->name }}</h3>
                                <div class="dropdown ml-auto" style="position: relative;">
                                    <button class="dropdown-toggle w-5 h-5 block -mr-2" type="button" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="more-vertical" class="dropdownSVG w-5 h-5 text-slate-500"></i>
                                    </button>
                                    <div class="dropdown-menu w-40" id="_f12z2ubls">
                                        <ul class="dropdown-content">
                                            <li>
                                                <a data-id="{{ $folder->id }}" data-tw-toggle="modal" data-tw-target="#editFolderModal" href="javascript:void(0);" class="editFolder dropdown-item">
                                                    <i data-lucide="pencil-line" class="text-success w-4 h-4 mr-2"></i> Edit Folder
                                                </a>
                                            </li>
                                            <li>
                                                <a data-id="{{ $folder->id }}" data-tw-toggle="modal" data-tw-target="#editFolderPermissionModal" href="javascript:void(0);" class="editPermission dropdown-item">
                                                    <i data-lucide="user-cog" class="text-info w-4 h-4 mr-2"></i> Edit Permission
                                                </a>
                                            </li>
                                            <li>
                                                <a data-id="{{ $folder->id }}" href="javascript:void(0);" class="deleteFolder dropdown-item">
                                                    <i data-lucide="trash-2" class="text-danger w-4 h-4 mr-2"></i> Delete Folder
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="folderIcon w-full text-warning bg-white rounded flex justify-center items-center py-10">
                                <i data-lucide="folder-closed" class="w-24 h-24"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>


    <!-- BEGIN: Add File Modal -->
    <div id="addFileModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addFileForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Upload File</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4 gap-y-2">
                            <div class="col-span-6">
                                <label for="document" class="form-label">Upload Document <span class="text-danger">*</span></label>
                                <input id="document" type="file" name="document" class="w-full">
                                <div class="acc__input-error error-document text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="linked_document" class="form-label">Linked Document <span class="text-danger">*</span></label>
                                <input id="linked_document" type="url" name="linked_document" class="form-control w-full">
                                <div class="acc__input-error error-linked_document text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="name" class="form-label">Document Name <span class="text-danger">*</span></label>
                                <input id="name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="expire_at" class="form-label">Exipiry Date</label>
                                <input id="expire_at" type="text" name="expire_at" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                                <div class="acc__input-error error-expire_at text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea id="description" name="description" class="form-control w-full" rows="4"></textarea>
                                <div class="acc__input-error error-description text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="mt-3 filePermissionSwitchWrap">
                            <label for="name" class="form-label">Inherit Permission</label>
                            <div class="form-check form-switch">
                                <input checked id="file_permission_inheritence" name="file_permission_inheritence" value="1" class="form-check-input" type="checkbox">
                                <label class="form-check-label file_permission_inheritence_label" for="permission_inheritence">Yes</label>
                            </div>
                        </div>
                        <div class="filePermissionWrap" style="display: none;">
                            <div>
                                <label for="file_employee_ids" class="form-label">Employees <span class="text-danger">*</span></label>
                                <select name="employee_ids[]" id="file_employee_ids" class="w-full tom-selects" multiple>
                                    @if(!empty($employee))
                                        @foreach($employee as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-employees text-danger mt-2"></div>
                            </div>
                            <div class="mt-3">
                                <table class="table table-bordered table-sm filePermissionTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Role</th>
                                            <th class="text-center">Create</th>
                                            <th class="text-center">Read</th>
                                            <th class="text-center">Update</th>
                                            <th class="text-center">Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="noticeTr">
                                            <td colspan="6">
                                                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please select employee and assign role.</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="uploadFile" class="btn btn-primary w-auto">     
                            Upload                      
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
                        <input type="hidden" name="folder_id" value="{{ $parent_id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add File Modal -->


    <!-- BEGIN: Edit Folder Permission Modal -->
    <div id="editFolderPermissionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editFolderPermissionForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Folder Permission</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="employee_ids" class="form-label">Employees <span class="text-danger">*</span></label>
                            <select name="employee_ids[]" id="edit_employee_ids" class="w-full tom-selects" multiple>
                                @if(!empty($employee))
                                    @foreach($employee as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employees text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <table class="table table-bordered table-sm folderPermissionTable">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Role</th>
                                        <th class="text-center">Create</th>
                                        <th class="text-center">Read</th>
                                        <th class="text-center">Update</th>
                                        <th class="text-center">Delete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="noticeTr">
                                        <td colspan="6">
                                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please select employee and assign role.</div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateFolderPermission" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="folder_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Folder Permission Modal -->

    <!-- BEGIN: Edit Folder Modal -->
    <div id="editFolderModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editFolderForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Folder</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="name" class="form-label">Folder Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateFolder" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="folder_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Folder Modal -->


    <!-- BEGIN: Add Folder Modal -->
    <div id="addFolderModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addFolderForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add New Folder</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="name" class="form-label">Folder Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 permissionSwitchWrap">
                            <label for="name" class="form-label">Inherit Permission</label>
                            <div class="form-check form-switch">
                                <input {{ $parent_id == 0 ? '' : 'checked' }} id="permission_inheritence" name="permission_inheritence" value="1" class="form-check-input" type="checkbox">
                                <label class="form-check-label permission_inheritence_label" for="permission_inheritence">Yes</label>
                            </div>
                        </div>
                        <div class="permissionWrap" style="display: {{ $parent_id == 0 ? 'block' : 'none' }};">
                            <div class="mt-3">
                                <label for="employee_ids" class="form-label">Employees <span class="text-danger">*</span></label>
                                <select name="employee_ids[]" id="employee_ids" class="w-full tom-selects" multiple>
                                    @if(!empty($employee))
                                        @foreach($employee as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-employees text-danger mt-2"></div>
                            </div>
                            <div class="mt-3">
                                <table class="table table-bordered table-sm folderPermissionTable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Role</th>
                                            <th>Create</th>
                                            <th>Read</th>
                                            <th>Update</th>
                                            <th>Delete</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="noticeTr">
                                            <td colspan="6">
                                                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Please select employee and assign role.</div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="createFolder" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="parent_id" value="{{ $parent_id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Folder Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
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
    @vite('resources/js/file-manager.js')
@endsection
