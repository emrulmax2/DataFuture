@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">File Manager</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            @if(($parent_id > 0 && (isset($theFolder->folder_permission->create) && $theFolder->folder_permission->create == 1)) || auth()->user()->id == 1)
                <button type="button" data-tw-toggle="modal" data-tw-target="#addFolderModal" class="add_btn btn btn-primary shadow-md mr-2">New Folder</button>
            @endif
            @if($parent_id > 0 && (isset($theFolder->folder_permission->create) && $theFolder->folder_permission->create == 1))
                <button type="button" data-tw-toggle="modal" data-tw-target="#addFileModal" class="add_btn btn btn-primary shadow-md mr-2">Upload File</button>
            @endif
            <div class="btnGroup fileManagerViewToggle inline-flex bg-slate-100 border rounded shadow-md">
                <button type="button" class="btn-grid active"><i data-lucide="layout-grid" class="w-5 h-5"></i></button>
                <button type="button" class="btn-list"><i data-lucide="list" class="w-5 h-5"></i></button>
            </div>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <div class="folderGridWrap activeGrid">
            <div class="activeListHeader">
                <div class="font-medium uppercase fileNameCol">Name</div>
                <div class="font-medium uppercase fileUpdateCol">Last Modified</div>
                <div class="font-medium uppercase fileOwnedCol">Owned By</div>
            </div>
            @if(!empty($permission))
                @foreach($permission as $parent_id => $details)
                    @foreach($details as $key => $folder)
                        @if(($key != 'parent_documents' && App\Http\Controllers\Filemanager\FilemanagerController::findKeyValue($folder)) || ($key != 'parent_documents' && $folder['folder_permission'] == 1))
                            @php 
                                $parameters = (!empty($params) ? explode('/', $params) : []);
                                $parameters[] = $folder['folder_slug'];

                                $parameters = implode('/', $parameters);
                                $thePermission = (isset($folder['folder_permission_details']) ? $folder['folder_permission_details'] : []);
                            @endphp
                            <div data-href="{{ route('file.manager', $parameters) }}" class="fileFolderWrap cursor-pointer folderWrap">
                                <div class="folderItem gridItems filesFoldersBox text-center">
                                    <div class="fileFolderImg">
                                        <img src="{{ asset('build/assets/images/file_icons/folder.png') }}" alt="{{ $folder['folder_name'] }}"/>
                                    </div>
                                    <h5>{{ $folder['folder_name'] }}</h5>
                                    <span class="fileFolderUpdated">{{ $folder['last_modified'] }}</span>
                                    @if(isset($folder['folder_admins']) && $folder['folder_admins']->count() > 0)
                                        <div class="ownedBy">
                                            <div class="flex">
                                                @foreach($folder['folder_admins'] as $admin)
                                                    @if(isset($admin->employee->photo_url) && !empty($admin->employee->photo_url))
                                                    <div class="w-8 h-8 image-fit zoom-in {{ (!$loop->first ? '-ml-5' : '') }}">
                                                        <img title="{{ (isset($admin->employee->full_name) ? $admin->employee->full_name : 'Unknown') }}" alt="{{ (isset($admin->employee->full_name) ? $admin->employee->full_name : 'Unknown') }}" class="tooltip rounded-full" src="{{ $admin->employee->photo_url }}">
                                                    </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="ownedBy"></div>
                                    @endif
                                    @if(!empty($thePermission))
                                    <div class="dropdown">
                                        <button class="dropdown-toggle w-5 h-5 block -mr-2" type="button" aria-expanded="false" data-tw-toggle="dropdown">
                                            <i data-lucide="more-vertical" class="dropdownSVG w-5 h-5 text-slate-500"></i>
                                        </button>
                                        <div class="dropdown-menu w-40">
                                            <ul class="dropdown-content">
                                                @if((isset($thePermission->create) && $thePermission->create == 1) && (isset($thePermission->update) && $thePermission->update == 1))
                                                <li>
                                                    <a data-id="{{ $key }}" data-tw-toggle="modal" data-tw-target="#editFolderModal" href="javascript:void(0);" class="editFolder dropdown-item">
                                                        <i data-lucide="pencil-line" class="text-success w-4 h-4 mr-2"></i> Edit Folder
                                                    </a>
                                                </li>
                                                @endif
                                                @if((isset($thePermission->create) && $thePermission->create == 1) && (isset($thePermission->update) && $thePermission->update == 1))
                                                <li>
                                                    <a data-id="{{ $key }}" data-tw-toggle="modal" data-tw-target="#editFolderPermissionModal" href="javascript:void(0);" class="editPermission dropdown-item">
                                                        <i data-lucide="user-cog" class="text-info w-4 h-4 mr-2"></i> Edit Permission
                                                    </a>
                                                </li>
                                                @endif
                                                @if(isset($thePermission->delete) && $thePermission->delete == 1)
                                                <li>
                                                    <a data-name="{{ $folder['folder_name'] }}" data-id="{{ $key }}" href="javascript:void(0);" class="deleteFolder dropdown-item">
                                                        <i data-lucide="trash-2" class="text-danger w-4 h-4 mr-2"></i> Delete Folder
                                                    </a>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @elseif($key == 'parent_documents' && !empty($folder)) 
                            @foreach($folder as $theFile)
                                @php 
                                    $currentFileName = explode('.', $theFile->current_file_name);
                                    $fileExtension = end($currentFileName);
                                    $thePermission = (isset($theFile->permission[0]->role) && !empty($theFile->permission[0]->role) ? $theFile->permission[0]->role : []);
                                @endphp
                                <div data-href="{{ ($theFile->download_url ? $theFile->download_url : '') }}" class="fileFolderWrap cursor-pointer fileWrap">
                                    <div class="fileItem gridItems filesFoldersBox text-center">
                                        <div class="fileFolderImg">
                                            <img src="{{ asset('build/assets/images/file_icons/'.strtolower($fileExtension).'.png') }}" alt="{{ $theFile->display_file_name }}"/>
                                        </div>
                                        <h5>{{ $theFile->display_file_name }}</h5>
                                        <span class="fileFolderUpdated">{{ (!empty($theFile->updated_at) ? date('jS F, Y', strtotime($theFile->updated_at)) : date('jS F, Y', strtotime($theFile->created_at))) }}</span>
                                        @if(isset($theFile->admins) && $theFile->admins->count() > 0)
                                            <div class="ownedBy">
                                                <div class="flex">
                                                    @foreach($theFile->admins as $admin)
                                                        @if(isset($admin->employee->photo_url) && !empty($admin->employee->photo_url))
                                                        <div class="w-8 h-8 image-fit zoom-in {{ (!$loop->first ? '-ml-5' : '') }}">
                                                            <img title="{{ (isset($admin->employee->full_name) ? $admin->employee->full_name : 'Unknown') }}" alt="{{ (isset($admin->employee->full_name) ? $admin->employee->full_name : 'Unknown') }}" class="tooltip rounded-full" src="{{ $admin->employee->photo_url }}">
                                                        </div>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="ownedBy"></div>
                                        @endif
                                        <div class="dropdown">
                                            <button class="dropdown-toggle w-5 h-5 block -mr-2" type="button" aria-expanded="false" data-tw-toggle="dropdown">
                                                <i data-lucide="more-vertical" class="dropdownSVG w-5 h-5 text-slate-500"></i>
                                            </button>
                                            <div class="dropdown-menu w-48" >
                                                <ul class="dropdown-content">
                                                    @if((isset($thePermission->create) && $thePermission->create == 1) || (isset($thePermission->update) && $thePermission->update == 1) || (isset($thePermission->read) && $thePermission->read == 1))
                                                    <li>
                                                        <a {{ ($theFile->download_url ? 'download' : '') }} href="{{ ($theFile->download_url ? $theFile->download_url : 'javascript:void(0);') }}" class="downloadDoc dropdown-item">
                                                            <i data-lucide="download-cloud" class="text-success w-4 h-4 mr-2"></i> Download
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if((isset($thePermission->create) && $thePermission->create == 1) && (isset($thePermission->update) && $thePermission->update == 1))
                                                    <li>
                                                        <a data-id="{{ $theFile->id }}" data-name="{{ $theFile->display_file_name }}" data-tw-toggle="modal" data-tw-target="#editFileModal" href="javascript:void(0);" class="editFile dropdown-item">
                                                            <i data-lucide="pencil-line" class="text-success w-4 h-4 mr-2"></i> Edit File
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if((isset($thePermission->create) && $thePermission->create == 1) && (isset($thePermission->update) && $thePermission->update == 1))
                                                    <li>
                                                        <a data-id="{{ $theFile->id }}" data-name="{{ $theFile->display_file_name }}" data-tw-toggle="modal" data-tw-target="#uploadFileVersionModal" href="javascript:void(0);" class="uploadNewVersion dropdown-item">
                                                            <i data-lucide="upload-cloud" class="text-success w-4 h-4 mr-2"></i> Upload New Version
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if((isset($thePermission->create) && $thePermission->create == 1) && (isset($thePermission->update) && $thePermission->update == 1))
                                                    <li>
                                                        <a data-id="{{ $theFile->id }}" data-name="{{ $theFile->display_file_name }}" data-tw-toggle="modal" data-tw-target="#fileHistoryModal" href="javascript:void(0);" class="versionHistory dropdown-item">
                                                            <i data-lucide="file-clock" class="text-success w-4 h-4 mr-2"></i> Version History
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if((isset($thePermission->create) && $thePermission->create == 1) && (isset($thePermission->update) && $thePermission->update == 1) && (isset($thePermission->read) && $thePermission->read == 1))
                                                    <li>
                                                        <a data-id="{{ $theFile->id }}" data-name="{{ $theFile->display_file_name }}" data-tw-toggle="modal" data-tw-target="#editFilePermissionModal" href="javascript:void(0);" class="editFilePermission dropdown-item">
                                                            <i data-lucide="user-cog" class="text-info w-4 h-4 mr-2"></i> Edit Permission
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if((isset($thePermission->create) && $thePermission->create == 1) && isset($thePermission->update) && $thePermission->update == 1)
                                                    <li>
                                                        <a data-id="{{ $theFile->id }}" data-name="{{ $theFile->display_file_name }}" data-tw-toggle="modal" data-tw-target="#fileReminderModal" href="javascript:void(0);" class="fileReminderBtn dropdown-item">
                                                            <i data-lucide="bell" class="text-info w-4 h-4 mr-2"></i> Reminder
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if(isset($thePermission->delete) && $thePermission->delete == 1)
                                                    <li>
                                                        <a data-name="{{ $theFile->display_file_name }}" data-id="{{ $theFile->id }}" href="javascript:void(0);" class="deleteFile dropdown-item">
                                                            <i data-lucide="trash-2" class="text-danger w-4 h-4 mr-2"></i> Delete File
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    @endforeach
                @endforeach
            @endif
        </div>
    </div>

    <!-- BEGIN: Folder Dropdown Start -->
    <div class="dropdown-menu folderDropdown w-40">
        <ul class="dropdown-content">
            <li class="editFolderLink">
                <a data-id="0" data-tw-toggle="modal" data-tw-target="#editFolderModal" href="javascript:void(0);" class="editFolder dropdown-item">
                    <i data-lucide="pencil-line" class="text-success w-4 h-4 mr-2"></i> Edit Folder
                </a>
            </li>
            <li class="editFolderPermissionLink">
                <a data-id="0" data-tw-toggle="modal" data-tw-target="#editFolderPermissionModal" href="javascript:void(0);" class="editPermission dropdown-item">
                    <i data-lucide="user-cog" class="text-info w-4 h-4 mr-2"></i> Edit Permission
                </a>
            </li>
            <li class="deleteFolderLink">
                <a data-name="" data-id="0" href="javascript:void(0);" class="deleteFolder dropdown-item">
                    <i data-lucide="trash-2" class="text-danger w-4 h-4 mr-2"></i> Delete Folder
                </a>
            </li>
        </ul>
    </div>
    <!-- BEGIN: Folder Dropdown End -->

    <!-- BEGIN: File Dropdown Start -->
    <div class="dropdown-menu fileDropdown w-48" >
        <ul class="dropdown-content">
            <li class="downloadLink">
                <a href="" class="downloadDoc dropdown-item">
                    <i data-lucide="download-cloud" class="text-success w-4 h-4 mr-2"></i> Download
                </a>
            </li>
            <li class="editFileLink">
                <a data-id="0" data-name="" data-tw-toggle="modal" data-tw-target="#editFileModal" href="javascript:void(0);" class="editFile dropdown-item">
                    <i data-lucide="pencil-line" class="text-success w-4 h-4 mr-2"></i> Edit File
                </a>
            </li>
            <li class="uploadVersionLink">
                <a data-id="0" data-name="" data-tw-toggle="modal" data-tw-target="#uploadFileVersionModal" href="javascript:void(0);" class="uploadNewVersion dropdown-item">
                    <i data-lucide="upload-cloud" class="text-success w-4 h-4 mr-2"></i> Upload New Version
                </a>
            </li>
            <li class="versionHistoryLink">
                <a data-id="0" data-name="" data-tw-toggle="modal" data-tw-target="#fileHistoryModal" href="javascript:void(0);" class="versionHistory dropdown-item">
                    <i data-lucide="file-clock" class="text-success w-4 h-4 mr-2"></i> Version History
                </a>
            </li>
            <li class="editPermissionLink">
                <a data-id="0" data-name="" data-tw-toggle="modal" data-tw-target="#editFilePermissionModal" href="javascript:void(0);" class="editFilePermission dropdown-item">
                    <i data-lucide="user-cog" class="text-info w-4 h-4 mr-2"></i> Edit Permission
                </a>
            </li>
            <li class="reminderLink">
                <a data-id="0" data-name="" data-tw-toggle="modal" data-tw-target="#fileReminderModal" href="javascript:void(0);" class="fileReminderBtn dropdown-item">
                    <i data-lucide="bell" class="text-info w-4 h-4 mr-2"></i> Reminder
                </a>
            </li>
            <li class="deleteFileLink">
                <a data-name="" data-id="0" href="javascript:void(0);" class="deleteFile dropdown-item">
                    <i data-lucide="trash-2" class="text-danger w-4 h-4 mr-2"></i> Delete File
                </a>
            </li>
        </ul>
    </div>
    <!-- BEGIN: File Dropdown End -->


    <!-- BEGIN: File Reminder Modal -->
    <div id="fileReminderModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="fileReminderForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">File Reminder <span class="displayName underline font-bold"></span></h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control w-full" id="subject"/>
                        </div>
                        <div class="mt-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea rows="4" name="message" class="form-control w-full" id="message"></textarea>
                        </div>
                        <div class="grid grid-cols-12 gap-x-4 gap-y-0 mt-3">
                            <div class="col-span-3">
                                <div class="form-check" style="padding-top: 38px;">
                                    <input id="is_repeat_reminder" name="is_repeat_reminder" class="form-check-input" type="checkbox" value="1">
                                    <label class="form-check-label" for="is_repeat_reminder">Repeat Reminder</label>
                                </div>
                            </div>
                            <div class="col-span-3">
                                <div class="form-check" style="padding-top: 38px;">
                                    <input id="is_send_email" name="is_send_email" class="form-check-input" type="checkbox" value="1">
                                    <label class="form-check-label" for="is_send_email">Send Email</label>
                                </div>
                            </div>
                            <div class="col-span-6">
                                <label for="reminder_employee_ids" class="form-label">Employees <span class="text-danger">*</span></label>
                                <select name="employee_ids[]" id="reminder_employee_ids" class="w-full tom-selects" multiple>
                                    @if(!empty($employee))
                                        @foreach($employee as $emp)
                                            <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-employee_ids text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="reminderSingleWrap">
                            <div class="grid grid-cols-12 gap-x-4 gap-y-0 mt-3">
                                <div class="col-span-4">
                                    <label for="single_reminder_date" class="form-label">Reminder Date <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ date('d-m-Y') }}" name="single_reminder_date" class="form-control w-full datepicker" id="single_reminder_date" data-format="DD-MM-YYYY" data-single-mode="true"/>
                                </div>
                            </div>
                        </div>
                        <div class="reminderMultiWrap" style="display: none;">
                            <div class="grid grid-cols-12 gap-x-4 gap-y-0 mt-3">
                                <div class="col-span-4">
                                    <label for="frequency" class="form-label">Reminder Date <span class="text-danger">*</span></label>
                                    <select id="frequency" name="frequency" class="form-control w-full">
                                        <option value="">Please Select</option>
                                        <option value="Daily" class="ng-star-inserted">Daily</option>
                                        <option value="Weekly" class="ng-star-inserted">Weekly</option>
                                        <option value="Monthly" class="ng-star-inserted">Monthly</option>
                                        <option value="Quarterly" class="ng-star-inserted">Quarterly</option>
                                        <option value="Half Yearly" class="ng-star-inserted">Half Yearly</option>
                                        <option value="Yearly" class="ng-star-inserted">Yearly</option>
                                    </select>
                                </div>
                                <div class="col-span-4">
                                    <label for="repeat_reminder_start" class="form-label">Reminder Start <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ date('d-m-Y') }}" name="repeat_reminder_start" class="form-control w-full datepicker" id="repeat_reminder_start" data-format="DD-MM-YYYY" data-single-mode="true"/>
                                </div>
                                <div class="col-span-4">
                                    <label for="repeat_reminder_end" class="form-label">Reminder End</label>
                                    <input type="text" value="" name="repeat_reminder_end" class="form-control w-full datepicker" id="repeat_reminder_end" data-format="DD-MM-YYYY" data-single-mode="true"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveReminder" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="document_info_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit File Permission Modal -->

    <!-- BEGIN: Edit File Permission Modal -->
    <div id="editFilePermissionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editFilePermissionForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit File Permission</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_file_employee_ids" class="form-label">Employees <span class="text-danger">*</span></label>
                            <select name="employee_ids[]" id="edit_file_employee_ids" class="w-full tom-selects" multiple>
                                @if(!empty($employee))
                                    @foreach($employee as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employee_ids text-danger mt-2"></div>
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
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateFilePermission" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="document_info_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit File Permission Modal -->


    <!-- BEGIN: File History Modal -->
    <div id="fileHistoryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Version History of <span class="displayName underline font-bold"></span></h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden fileVersionHistoryListTableWrap">
                        <div data-fileinfo="0" id="fileVersionHistoryListTable" class="table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: File History Modal -->

    <!-- BEGIN: File New Version Modal -->
    <div id="uploadFileVersionModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="uploadFileVersionForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Upload New Version</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4 gap-y-1">
                            <div class="col-span-12">
                                <label for="document" class="form-label">Upload Document <span class="text-danger">*</span></label>
                                <label class="uploadWrap form-control relative border flex justify-start items-center cursor-pointer" for="editDocument">
                                    <input accept="image/*,.doc,.docx,.xl,.xlsx,.xls,.ppt,.pptx,.pdf,.txt,.sql" id="editDocument" type="file" name="document" class="w-full" style="position: absolute; width: 0; height: 0; opacity: 0; visibility: hidden;">
                                    <span class="btn btn-secondary w-auto">Choose File</span>
                                    <span id="editDocumentName" class="ml-3"></span>
                                </label>
                                <div class="acc__input-error error-document text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12">
                                <label for="linked_document" class="form-label">Linked Document </label>
                                <input id="linked_document" type="url" name="linked_document" class="form-control w-full">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="uploadNV" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="params" value="{{ $params }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: File New Version Modal -->

    <!-- BEGIN: Edit File Modal -->
    <div id="editFileModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editFileForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit File</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4 gap-y-1">
                            <div class="col-span-6">
                                <label for="edit_name" class="form-label">Document Name <span class="text-danger">*</span></label>
                                <input id="edit_name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>
                            {{--<div class="col-span-6">
                                <label for="edit_expire_at" class="form-label">Exipiry Date</label>
                                <input id="edit_expire_at" type="text" name="expire_at" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>--}}
                            <div class="col-span-12">
                                <label for="edit_description" class="form-label">Description</label>
                                <textarea id="edit_description" name="description" class="form-control w-full" rows="4"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateFile" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="folder_id" value="{{ $parent_id }}"/>
                        <input type="hidden" name="params" value="{{ $params }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit File Modal -->

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
                        <div class="grid grid-cols-12 gap-4 gap-y-1">
                            <div class="col-span-6">
                                <label for="document" class="form-label">Upload Document <span class="text-danger">*</span></label>
                                <label class="uploadWrap form-control relative border flex justify-start items-center cursor-pointer" for="addDocument">
                                    <input accept=".jpg,.jpeg,.png,.doc,.docx,.xl,.xlsx,.xls,.ppt,.pptx,.pdf,.txt,.zip" id="addDocument" type="file" name="document" class="w-full" style="position: absolute; width: 0; height: 0; opacity: 0; visibility: hidden;">
                                    <span class="btn btn-secondary w-auto">Choose File</span>
                                    <span id="addDocumentName" class="ml-3"></span>
                                </label>
                                <div class="acc__input-error error-document text-danger mt-2"></div>
                            </div>
                            {{--<div class="col-span-6">
                                <label for="linked_document" class="form-label">Linked Document </label>
                                <input id="linked_document" type="url" name="linked_document" class="form-control w-full">
                            </div>--}}
                            <div class="col-span-6">
                                <label for="name" class="form-label">Document Name <span class="text-danger">*</span></label>
                                <input id="name" type="text" name="name" class="form-control w-full">
                                <div class="acc__input-error error-name text-danger mt-2"></div>
                            </div>
                            <div class="col-span-6">
                                <label for="expire_at" class="form-label">Exipiry Date</label>
                                <input id="expire_at" type="text" name="expire_at" class="form-control w-full datepicker" data-format="DD-MM-YYYY" data-single-mode="true">
                            </div>
                            <div class="col-span-12">
                                <label for="description" class="form-label">Description</label>
                                <textarea id="description" name="description" class="form-control w-full" rows="4"></textarea>
                            </div>
                        </div>
                        <div class="mt-3 filePermissionSwitchWrap mb-3">
                            <label for="name" class="form-label">Inherit Permission</label>
                            <div class="form-check form-switch">
                                <input checked id="file_permission_inheritence" name="file_permission_inheritence" value="1" class="form-check-input" type="checkbox">
                                <label class="form-check-label file_permission_inheritence_label" for="permission_inheritence">Yes</label>
                            </div>
                        </div>
                        <div class="filePermissionWrap pt-2" style="display: none;">
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
                        <input type="hidden" name="params" value="{{ $params }}"/>
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
                        <input type="hidden" name="params" value="{{ $params }}"/>
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

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="octagon-alert" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 sarningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
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
    @vite('resources/js/file-manager.js')
@endsection
