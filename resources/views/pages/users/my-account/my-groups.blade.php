@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('body_class', 'my-account-groups-body')

@section('subcontent')
    @include('pages.users.my-account.show-info')

    <section class="myhr-groups" data-screen-label="My Groups">
        <header class="myhr-groups__header">
            <span class="myhr-groups__header-icon">
                <i data-lucide="users"></i>
            </span>
            <h2>My Groups</h2>
            <button data-tw-toggle="modal" data-tw-target="#addGroupModal" type="button" class="myhr-groups-btn myhr-groups-btn--primary">
                <i data-lucide="plus"></i>
                Add Group
            </button>
        </header>

        <div class="myhr-groups__toolbar">
            <form id="tabulatorFilterForm" class="myhr-groups-filter">
                <span class="myhr-groups-filter__label">Status</span>
                <label class="myhr-groups-select">
                    <select id="status" name="status">
                        <option value="1">Active</option>
                        <option value="2">Archived</option>
                        <option value="3">All</option>
                    </select>
                    <i data-lucide="chevron-down"></i>
                </label>
                <button id="tabulator-html-filter-go" type="button" class="myhr-groups-btn myhr-groups-btn--filter">Go</button>
                <button id="tabulator-html-filter-reset" type="button" class="myhr-groups-btn myhr-groups-btn--muted">Reset</button>
            </form>

            <div class="myhr-groups-actions">
                <button id="tabulator-print" type="button" class="myhr-groups-btn myhr-groups-btn--outline">
                    <i data-lucide="printer"></i>
                    Print
                </button>
                <div class="dropdown">
                    <button class="dropdown-toggle myhr-groups-btn myhr-groups-btn--outline" aria-expanded="false" data-tw-toggle="dropdown" type="button">
                        <i data-lucide="download"></i>
                        Export
                        <i data-lucide="chevron-down" class="myhr-groups-btn__caret"></i>
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
                                    <i data-lucide="file-spreadsheet" class="w-4 h-4 mr-2"></i> Export XLSX
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="myhr-groups__table-wrap">
            <div id="myGroupListTable" class="myhr-groups-table table-report table-report--tabulator"></div>
        </div>
    </section>

    <!-- BEGIN: Edit Modal -->
    <div id="editGroupModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editGroupForm" enctype="multipart/form-data">
                <div class="modal-content myhr-groups-modal">
                    <div class="modal-header myhr-groups-modal__header">
                        <span class="myhr-groups-modal__icon">
                            <i data-lucide="users"></i>
                        </span>
                        <h2>Edit Group</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" class="myhr-groups-modal__close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body myhr-groups-modal__body">
                        <div class="myhr-groups-field">
                            <label for="edit_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input id="edit_name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="myhr-groups-field">
                            <label for="edit_employee_ids" class="form-label">Members <span class="text-danger">*</span></label>
                            <select id="edit_employee_ids" name="employee_ids[]" class="w-full tom-selects" multiple>
                                @if($employees->count() > 0)
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employee_ids text-danger mt-2"></div>
                        </div>
                        <div class="myhr-groups-field">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <div class="myhr-groups-choice">
                                <label class="form-check">
                                    <input checked id="edit_group_type_1" class="form-check-input" type="radio" name="type" value="1">
                                    <span class="form-check-label">Private</span>
                                </label>
                                <label class="form-check">
                                    <input id="edit_group_type_2" class="form-check-input" type="radio" name="type" value="2">
                                    <span class="form-check-label">Public</span>
                                </label>
                            </div>
                            <div class="acc__input-error error-type text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer myhr-groups-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="myhr-groups-modal-btn myhr-groups-modal-btn--cancel">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="submit" id="updateGroup" class="myhr-groups-modal-btn myhr-groups-modal-btn--save">     
                            <i data-lucide="save"></i>
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
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->

    <!-- BEGIN: Add Modal -->
    <div id="addGroupModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="addGroupForm" enctype="multipart/form-data">
                <div class="modal-content myhr-groups-modal">
                    <div class="modal-header myhr-groups-modal__header">
                        <span class="myhr-groups-modal__icon">
                            <i data-lucide="users"></i>
                        </span>
                        <h2>Add Group</h2>
                        <a data-tw-dismiss="modal" href="javascript:;" class="myhr-groups-modal__close">
                            <i data-lucide="x"></i>
                        </a>
                    </div>
                    <div class="modal-body myhr-groups-modal__body">
                        <div class="myhr-groups-field">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input id="name" type="text" name="name" class="form-control w-full">
                            <div class="acc__input-error error-name text-danger mt-2"></div>
                        </div>
                        <div class="myhr-groups-field">
                            <label for="employee_ids" class="form-label">Members <span class="text-danger">*</span></label>
                            <select id="employee_ids" name="employee_ids[]" class="w-full tom-selects" multiple>
                                @if($employees->count() > 0)
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->id }}">{{ $emp->full_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-employee_ids text-danger mt-2"></div>
                        </div>
                        <div class="myhr-groups-field">
                            <label class="form-label">Type <span class="text-danger">*</span></label>
                            <div class="myhr-groups-choice">
                                <label class="form-check">
                                    <input checked id="group_type_1" class="form-check-input" type="radio" name="type" value="1">
                                    <span class="form-check-label">Private</span>
                                </label>
                                <label class="form-check">
                                    <input id="group_type_2" class="form-check-input" type="radio" name="type" value="2">
                                    <span class="form-check-label">Public</span>
                                </label>
                            </div>
                            <div class="acc__input-error error-type text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer myhr-groups-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="myhr-groups-modal-btn myhr-groups-modal-btn--cancel">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                        <button type="submit" id="createGroup" class="myhr-groups-modal-btn myhr-groups-modal-btn--save">     
                            <i data-lucide="save"></i>
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

    <!-- BEGIN: Group Members Modal -->
    <div id="groupMembersModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content myhr-groups-modal myhr-groups-members-modal">
                <div class="modal-header myhr-groups-modal__header">
                    <span class="myhr-groups-modal__icon">
                        <i data-lucide="users"></i>
                    </span>
                    <h2>Group Members</h2>
                    <a data-tw-dismiss="modal" href="javascript:;" class="myhr-groups-modal__close">
                        <i data-lucide="x"></i>
                    </a>
                </div>
                <div class="modal-body myhr-groups-modal__body">
                    <div class="myhr-group-members-loading">
                        <svg width="28" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#0f7b76">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".25" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Group Members Modal -->


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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
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
    @vite('resources/js/user-groups.js')
@endsection
