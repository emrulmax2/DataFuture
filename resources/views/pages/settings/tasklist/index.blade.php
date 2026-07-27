@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
@endsection

@section('content')
    @php
        $taskToggles = [
            ['name' => 'interview', 'label' => 'Interview', 'required' => true],
            ['name' => 'upload', 'label' => 'Upload', 'required' => true],
            ['name' => 'org_email', 'label' => 'Organization Email'],
            ['name' => 'id_card', 'label' => 'ID Card'],
            ['name' => 'attendance_excuses', 'label' => 'Attendance Excuse'],
            ['name' => 'pearson_reg', 'label' => 'Pearson Registration'],
            ['name' => 'address_request', 'label' => 'Address Update Request'],
            ['name' => 'hesa_status', 'label' => 'Student Hesa Status'],
            ['name' => 'status', 'label' => 'Status', 'required' => true],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Applicant Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Task List</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="clipboard-list"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage task workflows, assigned staff, documents, external links and status requirements.</p>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Dashboard
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-table-card ss-task-list-card">
                        <div class="ss-table-card__header">
                            <h2>Tasks List</h2>
                            <button data-tw-toggle="modal" data-tw-target="#addTaskModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add New Task
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Search...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Process</span>
                                    <label class="ss-filter-select" for="processlists-01">
                                        <select id="processlists-01" name="processlists">
                                            <option value="">Please Select</option>
                                            @if(!empty($processlists))
                                                @foreach($processlists as $pro)
                                                    <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status">
                                        <select id="status" name="status">
                                            <option value="1">Active</option>
                                            <option value="2">Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>

                            <div class="ss-table-actions">
                                <button id="tabulator-print" type="button" class="ss-btn ss-btn--light ss-btn--tool">
                                    <i data-lucide="printer"></i>
                                    Print
                                </button>
                                <div class="dropdown ss-export-dropdown">
                                    <button type="button" class="dropdown-toggle ss-btn ss-btn--light ss-btn--tool" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="download"></i>
                                        Export
                                        <i data-lucide="chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu ss-export-menu">
                                        <ul class="dropdown-content">
                                            <li>
                                                <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-spreadsheet"></i>
                                                    Export XLSX
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ss-tabulator-wrap">
                            <div id="taskTableId" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addTaskModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--task">
                <form method="POST" action="#" id="addTaskForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Task</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-task-form-grid">
                                <div class="ss-modal-field ss-task-image-field">
                                    <label>Task Image</label>
                                    <label for="processImageAdd" class="ss-task-image-picker">
                                        <img alt="Task preview" class="processImageAddShow" id="processImageAddShow" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                        <span><i data-lucide="camera"></i></span>
                                    </label>
                                    <small data-ss-upload-name>No file selected</small>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="ss-task-image-input" id="processImageAdd">
                                </div>

                                <div class="ss-task-fields">
                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="add_process_list_id">Permission <span>*</span></label>
                                            <label class="ss-modal-select" for="add_process_list_id">
                                                <select id="add_process_list_id" name="process_list_id" class="process_list_id">
                                                    <option value="">Please Select</option>
                                                    @if(!empty($processlists))
                                                        @foreach($processlists as $pro)
                                                            <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <i data-lucide="chevron-down"></i>
                                            </label>
                                            <div class="acc__input-error error-process_list_id"></div>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="add_task_name">Name <span>*</span></label>
                                            <input id="add_task_name" type="text" name="name" class="ss-modal-input name" placeholder="Task name">
                                            <div class="acc__input-error error-name"></div>
                                        </div>
                                        <div class="ss-modal-field ss-modal-field--full">
                                            <label for="add_short_description">Short Description</label>
                                            <input id="add_short_description" type="text" name="short_description" class="ss-modal-input short_description" placeholder="Short description">
                                            <div class="acc__input-error error-short_description"></div>
                                        </div>
                                        <div class="ss-modal-field ss-modal-field--full">
                                            <label for="assigned_users">Assigned Users <span>*</span></label>
                                            <select id="assigned_users" name="assigned_users[]" class="ss-modal-input assigned_users tom-selects" multiple>
                                                @if(!empty($employees))
                                                    @foreach($employees as $emp)
                                                        <option value="{{ $emp->user_id }}">{{ $emp->full_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-assigned_users"></div>
                                        </div>
                                    </div>

                                    <div class="ss-task-toggle-grid">
                                        @foreach($taskToggles as $field)
                                            <div class="ss-modal-field {{ $field['name'] === 'status' ? 'ss-task-status-radio' : '' }}">
                                                <label>{{ $field['label'] }} @if(!empty($field['required']))<span>*</span>@endif</label>
                                                <div class="ss-type-options ss-task-yes-no">
                                                    <label class="ss-type-option" for="{{ $field['name'] }}-yes">
                                                        <input id="{{ $field['name'] }}-yes" type="radio" name="{{ $field['name'] }}" value="Yes">
                                                        <span><i data-lucide="check"></i></span>
                                                        <strong>Yes</strong>
                                                    </label>
                                                    <label class="ss-type-option" for="{{ $field['name'] }}-no">
                                                        <input checked id="{{ $field['name'] }}-no" type="radio" name="{{ $field['name'] }}" value="No">
                                                        <span><i data-lucide="x"></i></span>
                                                        <strong>No</strong>
                                                    </label>
                                                </div>
                                                @if($field['name'] === 'status')
                                                    <div class="acc__input-error error-status"></div>
                                                @endif
                                            </div>
                                            @if($field['name'] === 'status')
                                                <div class="ss-modal-field ss-modal-field--full taskStatusesWrap" style="display: none;">
                                                    <label>Task Statuses <span>*</span></label>
                                                    @if($taskStatus->count() > 0)
                                                        <div class="ss-document-toggle-grid ss-task-status-grid">
                                                            @foreach($taskStatus as $ts)
                                                                <label class="ss-status-toggle" for="task-status-{{ $ts->id }}">
                                                                    <input id="task-status-{{ $ts->id }}" type="checkbox" name="task_statuses[]" value="{{ $ts->id }}">
                                                                    <span class="ss-status-toggle__control">
                                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                                    </span>
                                                                    <span class="ss-status-toggle__copy">
                                                                        <strong>{{ $ts->name }}</strong>
                                                                        <small>Available status</small>
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        <div class="acc__input-error error-task_statuses"></div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach

                                        <div class="ss-task-external-row">
                                            <div class="ss-modal-field ss-task-external-toggle">
                                                <label for="external_link">External Link</label>
                                                <label class="ss-status-toggle" for="external_link">
                                                    <input id="external_link" name="external_link" value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>External URL</strong>
                                                        <small>Attach a link to this task</small>
                                                    </span>
                                                </label>
                                            </div>

                                            <div class="ss-modal-field extarnalUrlWrap ss-task-external-url" style="display: none;">
                                                <label for="external_link_ref">External URL <span>*</span></label>
                                                <input id="external_link_ref" type="text" name="external_link_ref" class="ss-modal-input external_link_ref" placeholder="https://">
                                                <div class="acc__input-error error-external_link_ref"></div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="save" class="ss-btn ss-btn--primary">
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                                <i data-lucide="check"></i>
                                Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="editTaskModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--task">
                <form method="POST" action="#" id="editTaskForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Task</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-task-form-grid">
                                <div class="ss-modal-field ss-task-image-field">
                                    <label>Task Image</label>
                                    <label for="processImageEdit" class="ss-task-image-picker">
                                        <img alt="Task preview" class="processImageEditShow" id="processImageEditShow" data-placeholder="{{ asset('build/assets/images/placeholders/200x200.jpg') }}" src="{{ asset('build/assets/images/placeholders/200x200.jpg') }}">
                                        <span><i data-lucide="camera"></i></span>
                                    </label>
                                    <small data-ss-upload-name>No file selected</small>
                                    <input type="file" accept=".jpeg,.jpg,.png,.gif" name="photo" class="ss-task-image-input" id="processImageEdit">
                                </div>

                                <div class="ss-task-fields">
                                    <div class="ss-modal-grid">
                                        <div class="ss-modal-field">
                                            <label for="edit_process_list_id">Permission <span>*</span></label>
                                            <label class="ss-modal-select" for="edit_process_list_id">
                                                <select id="edit_process_list_id" name="process_list_id" class="process_list_id">
                                                    <option value="">Please Select</option>
                                                    @if(!empty($processlists))
                                                        @foreach($processlists as $pro)
                                                            <option value="{{ $pro->id }}">{{ $pro->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <i data-lucide="chevron-down"></i>
                                            </label>
                                            <div class="acc__input-error error-process_list_id"></div>
                                        </div>
                                        <div class="ss-modal-field">
                                            <label for="edit_task_name">Name <span>*</span></label>
                                            <input id="edit_task_name" type="text" name="name" class="ss-modal-input name" placeholder="Task name">
                                            <div class="acc__input-error error-name"></div>
                                        </div>
                                        <div class="ss-modal-field ss-modal-field--full">
                                            <label for="edit_short_description">Short Description</label>
                                            <input id="edit_short_description" type="text" name="short_description" class="ss-modal-input short_description" placeholder="Short description">
                                            <div class="acc__input-error error-short_description"></div>
                                        </div>
                                        <div class="ss-modal-field ss-modal-field--full">
                                            <label for="edit_assigned_users">Assigned Users <span>*</span></label>
                                            <select id="edit_assigned_users" name="assigned_users[]" class="ss-modal-input assigned_users tom-selects" multiple>
                                                @if(!empty($employees))
                                                    @foreach($employees as $emp)
                                                        <option value="{{ $emp->user_id }}">{{ $emp->full_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-assigned_users"></div>
                                        </div>
                                    </div>

                                    <div class="ss-task-toggle-grid">
                                        @foreach($taskToggles as $field)
                                            <div class="ss-modal-field {{ $field['name'] === 'status' ? 'ss-task-status-radio' : '' }}">
                                                <label>{{ $field['label'] }} @if(!empty($field['required']))<span>*</span>@endif</label>
                                                <div class="ss-type-options ss-task-yes-no">
                                                    <label class="ss-type-option" for="edit_{{ $field['name'] }}-yes">
                                                        <input id="edit_{{ $field['name'] }}-yes" type="radio" name="{{ $field['name'] }}" value="Yes">
                                                        <span><i data-lucide="check"></i></span>
                                                        <strong>Yes</strong>
                                                    </label>
                                                    <label class="ss-type-option" for="edit_{{ $field['name'] }}-no">
                                                        <input checked id="edit_{{ $field['name'] }}-no" type="radio" name="{{ $field['name'] }}" value="No">
                                                        <span><i data-lucide="x"></i></span>
                                                        <strong>No</strong>
                                                    </label>
                                                </div>
                                                @if($field['name'] === 'status')
                                                    <div class="acc__input-error error-status"></div>
                                                @endif
                                            </div>
                                            @if($field['name'] === 'status')
                                                <div class="ss-modal-field ss-modal-field--full taskStatusesWrap" style="display: none;">
                                                    <label>Task Statuses <span>*</span></label>
                                                    @if($taskStatus->count() > 0)
                                                        <div class="ss-document-toggle-grid ss-task-status-grid">
                                                            @foreach($taskStatus as $ts)
                                                                <label class="ss-status-toggle" for="edit_task-status-{{ $ts->id }}">
                                                                    <input id="edit_task-status-{{ $ts->id }}" type="checkbox" name="task_statuses[]" value="{{ $ts->id }}">
                                                                    <span class="ss-status-toggle__control">
                                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                                    </span>
                                                                    <span class="ss-status-toggle__copy">
                                                                        <strong>{{ $ts->name }}</strong>
                                                                        <small>Available status</small>
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                        <div class="acc__input-error error-task_statuses"></div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach

                                        <div class="ss-task-external-row">
                                            <div class="ss-modal-field ss-task-external-toggle">
                                                <label for="edit_external_link">External Link</label>
                                                <label class="ss-status-toggle" for="edit_external_link">
                                                    <input id="edit_external_link" name="external_link" value="1" type="checkbox" autocomplete="off">
                                                    <span class="ss-status-toggle__control">
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                    </span>
                                                    <span class="ss-status-toggle__copy">
                                                        <strong>External URL</strong>
                                                        <small>Attach a link to this task</small>
                                                    </span>
                                                </label>
                                            </div>

                                            <div class="ss-modal-field extarnalUrlWrap ss-task-external-url" style="display: none;">
                                                <label for="edit_external_link_ref">External URL <span>*</span></label>
                                                <input id="edit_external_link_ref" type="text" name="external_link_ref" class="ss-modal-input external_link_ref" placeholder="https://">
                                                <div class="acc__input-error error-external_link_ref"></div>
                                            </div>
                                        </div>

                                        <input type="hidden" name="id" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="update" class="ss-btn ss-btn--primary">
                                <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="ss-spinner">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                                <i data-lucide="check"></i>
                                Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div id="taskUserModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <div class="modal-content ss-settings-modal ss-task-user-modal">
                    <div class="ss-settings-modal__header">
                        <div>
                            <span></span>
                            <h2>Task Assigned Users</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                            <i data-lucide="x"></i>
                        </button>
                    </div>
                    <div class="ss-settings-modal__body">
                        <div class="taskUserModalLoader ss-task-user-loader">
                            <i data-loading-icon="rings" class="w-20 h-20"></i>
                        </div>
                        <div class="taskUserModalContent ss-task-user-content" style="display: none;">
                            <div class="ss-task-user-table-wrap">
                                <table class="ss-assigned-users-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Department</th>
                                            <th>Work Type</th>
                                            <th>Work No.</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="ss-settings-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                            <i data-lucide="x"></i>
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                            <div class="successModalTitle"></div>
                            <p class="successModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-confirm-modal__dialog">
                <div class="modal-content ss-confirm-modal">
                    <div class="ss-confirm-modal__hero">
                        <span><i data-lucide="alert-triangle"></i></span>
                        <h2 class="confModTitle">Are you sure?</h2>
                    </div>
                    <div class="ss-confirm-modal__body">
                        <p class="confModDesc"></p>
                    </div>
                    <div class="ss-confirm-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                            <i data-lucide="x"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith ss-btn ss-btn--danger">
                            <i data-lucide="check"></i>
                            Yes, I agree
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/tasklist.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
