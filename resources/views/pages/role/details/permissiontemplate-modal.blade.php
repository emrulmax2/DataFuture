<!-- BEGIN: Add Modal -->
<div id="permissiontemplateAddModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="permissiontemplateAddForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Permission</h2>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="permission_category_id" class="form-label">Permission Category <span class="text-danger">*</span></label>
                        <select id="permission_category_id" name="permission_category_id" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if(!empty($permissioncategory))
                                @foreach($permissioncategory as $per)
                                    <option value="{{ $per->id }}">{{ $per->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-permission_category_id text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select id="department_id" name="department_id" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if(!empty($department))
                                @foreach($department as $dpt)
                                    <option value="{{ $dpt->id }}">{{ $dpt->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-department_id text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <input id="type" type="text" name="type" class="form-control w-full">
                        <div class="acc__input-error error-type text-danger mt-2"></div>
                    </div>
                    <div class="grid grid-cols-12 gap-x-4 gap-y-0">
                        <div class="col-span-4">
                            <div class="mt-3 form-check form-switch">
                                <label class="form-check-label mr-3" for="R">Read</label>
                                <input id="R" class="form-check-input" name="R" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="col-span-4">
                            <div class="mt-3 form-check form-switch">
                                <label class="form-check-label mr-3" for="W">Write</label>
                                <input id="W" class="form-check-input" name="W" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="col-span-4">
                            <div class="mt-3 form-check form-switch">
                                <label class="form-check-label mr-3" for="D">Delete</label>
                                <input id="D" class="form-check-input" name="D" value="1" type="checkbox">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="role_id" value="{{ $role->id }}" />
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="save" class="btn btn-primary w-auto">
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
<div id="permissiontemplateEditModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="permissiontemplateEditForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Permission</h2>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="permission_category_id" class="form-label">Permission Category <span class="text-danger">*</span></label>
                        <select id="permission_category_id" name="permission_category_id" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if(!empty($permissioncategory))
                                @foreach($permissioncategory as $per)
                                    <option value="{{ $per->id }}">{{ $per->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-permission_category_id text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="department_id" class="form-label">Department <span class="text-danger">*</span></label>
                        <select id="department_id" name="department_id" class="form-control w-full">
                            <option value="">Please Select</option>
                            @if(!empty($department))
                                @foreach($department as $dpt)
                                    <option value="{{ $dpt->id }}">{{ $dpt->name }}</option>
                                @endforeach
                            @endif
                        </select>
                        <div class="acc__input-error error-permission_category_id text-danger mt-2"></div>
                    </div>
                    <div class="mt-3">
                        <label for="type" class="form-label">Type <span class="text-danger">*</span></label>
                        <input id="type" type="text" name="type" class="form-control w-full">
                        <div class="acc__input-error error-type text-danger mt-2"></div>
                    </div>
                    <div class="grid grid-cols-12 gap-x-4 gap-y-0">
                        <div class="col-span-4">
                            <div class="mt-3 form-check form-switch">
                                <label class="form-check-label mr-3" for="R">Read</label>
                                <input id="R" class="form-check-input" name="R" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="col-span-4">
                            <div class="mt-3 form-check form-switch">
                                <label class="form-check-label mr-3" for="W">Write</label>
                                <input id="W" class="form-check-input" name="W" value="1" type="checkbox">
                            </div>
                        </div>
                        <div class="col-span-4">
                            <div class="mt-3 form-check form-switch">
                                <label class="form-check-label mr-3" for="D">Delete</label>
                                <input id="D" class="form-check-input" name="D" value="1" type="checkbox">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="role_id" value="" />
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal"
                        class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="update" class="btn btn-primary w-auto">
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
@section('script')
    @vite('resources/js/permissiontemplate.js')
@endsection