@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Role Details</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('roles') }}" class="add_btn btn btn-primary shadow-md mr-2">Back To List</a>
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
                    <h2 class="font-medium text-base mr-auto">Role: <u class="font-bold">{{ $role->type }}</u> Privileges</h2>
                    <div class="dropdown" id="permissionCategoryDropdown">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown"><i data-lucide="tags" class="w-4 h-4 mr-2"></i>  Assign Category <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i></button>
                        <div class="dropdown-menu w-72">
                            <form method="post" action="#" id="assignPermissionCategoryForm">
                                <ul class="dropdown-content">
                                    <li><h6 class="dropdown-header">Category List</h6></li>
                                    <li><hr class="dropdown-divider mt-0"></li>
                                    @if(!empty($permissioncategory))
                                        @foreach($permissioncategory as $pc)
                                            <li>
                                                <div class="form-check dropdown-item">
                                                    <label class="inline-flex items-center cursor-pointer" for="permission_category_{{ $pc->id }}"><i data-lucide="tag" class="w-4 h-4 mr-2"></i> {{ $pc->name }}</label>
                                                    <input {{ (in_array($pc->id, $savedCategoryIds) ? 'Checked' : '') }} id="permission_category_{{ $pc->id }}" name="permission_category_id[]" class="form-check-input permission_category_id ml-auto" type="checkbox" value="{{ $pc->id }}">
                                                </div>
                                            </li>
                                        @endforeach
                                    @endif
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <div class="flex p-1">
                                            <button type="submit" id="addPermissionCat" class="btn btn-primary py-1 px-2 w-auto">     
                                                <i data-lucide="plus-circle" class="w-3 h-3 mr-2"></i> Assign                      
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
                                            <button type="button" id="closePCDropdown" class="btn btn-secondary py-1 px-2 ml-auto">Close</button>
                                            <input type="hidden" name="role_id" value="{{ $role->id }}"/>
                                        </div>
                                    </li>
                                </ul>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="px-5 py-7">
                    <div id="permissionTemplateAccr" class="accordion  accordion-boxed">
                        @if(!empty($permissionTemplate))
                            @foreach($permissionTemplate as $pt)
                                <div class="accordion-item">
                                    <div id="permissionTemplateAccr-{{ $pt->id }}" class="accordion-header">
                                        <div class="form-check form-switch m-0">
                                            <input id="permission_category_id_{{ $pt->id }}" name="is_inserted" class="form-check-input is_inserted" {{ (empty($pt->deleted_at) ? 'Checked' : '') }} value="{{ $pt->id }}" type="checkbox">
                                        </div>
                                        <button id="permission_category_btn_{{ $pt->id }}" {{ (empty($pt->deleted_at) ? '' : 'disabled') }} class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#permissionTemplateAccr-collapse-{{ $pt->id }}" aria-expanded="false" aria-controls="permissionTemplateAccr-collapse-{{ $pt->id }}">
                                            {{ $pt->category->name }}
                                            <span class="accordionCollaps"></span>
                                        </button>
                                    </div>
                                    <div id="permissionTemplateAccr-collapse-{{ $pt->id }}" class="accordion-collapse collapse" aria-labelledby="permissionTemplateAccr-{{ $pt->id }}" data-tw-parent="#permissionTemplateAccr">
                                        <div class="accordion-body">
                                            <div class="pt-0 pb-5 text-right">
                                                <button data-tw-toggle="modal" data-template="{{ $pt->id }}" data-tw-target="#addPermissionGroupModal" type="button" class="add_btn btn btn-primary addPermissionGroupBtn shadow-md mr-0">Add Permission Group</button>
                                            </div>
                                            @if(isset($pt->groups) && $pt->groups->count() > 0)
                                                @foreach($pt->groups as $ptg)
                                                    <div class="permissionGroup">
                                                        <div class="grid grid-cols-12 gap-4">
                                                            <div class="col-span-3">
                                                                <div class="form-check form-switch m-0">
                                                                    <input id="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_name" name="permission[{{ $ptg->permission_template_id }}][{{ $ptg->id }}][name]" value="1" class="form-check-input" type="checkbox">
                                                                    <label class="form-check-label" for="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_name">{{ $ptg->name }}</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-span-3">
                                                                <div class="form-check form-switch m-0">
                                                                    <input {{ isset($ptg->R) && $ptg->R == 1 ? 'Checked' : '' }} id="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_R" name="permission[{{ $ptg->permission_template_id }}][{{ $ptg->id }}][R]" value="1" class="form-check-input" type="checkbox">
                                                                    <label class="form-check-label" for="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_R">{{ $ptg->name }}</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-span-3">
                                                                <div class="form-check form-switch m-0">
                                                                    <input {{ isset($ptg->W) && $ptg->W == 1 ? 'Checked' : '' }} id="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_W" name="permission[{{ $ptg->permission_template_id }}][{{ $ptg->id }}][W]" value="1" class="form-check-input" type="checkbox">
                                                                    <label class="form-check-label" for="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_W">{{ $ptg->name }}</label>
                                                                </div>
                                                            </div>
                                                            <div class="col-span-3">
                                                                <div class="form-check form-switch m-0">
                                                                    <input {{ isset($ptg->W) && $ptg->D == 1 ? 'Checked' : '' }} id="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_D" name="permission[{{ $ptg->permission_template_id }}][{{ $ptg->id }}][D]" value="1" class="form-check-input" type="checkbox">
                                                                    <label class="form-check-label" for="permission_{{$ptg->permission_template_id}}_{{$ptg->id}}_D">{{ $ptg->name }}</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else 
                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Permission category not foudn. Please insert some category first.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Settings Page Content -->
    
    <!-- BEGIN: Add Modal -->
    <div id="addPermissionGroupModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addPermissionGroupForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Permission Group</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="mt-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="savePermissionGroup" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="permission_template_id" value="0"/>
                    </div>
                </div>
            </form>
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
                        <button type="button" data-action="NONE" class="btn btn-primary w-24 successCloser">Ok</button>
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
                        <button type="button" data-role="{{ $role->id }}" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
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
                        <button type="button" data-tw-dismiss="modal" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/roles.js')
    @vite('resources/js/permissiontemplate.js')
@endsection