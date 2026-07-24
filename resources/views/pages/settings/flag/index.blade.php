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
    <div id="siteSettingsPage" class="ss-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Student Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Student Flags</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="flag"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage the flags staff use to mark student records for attention, follow up and clearance.</p>
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
                    @php($settingsSidebarIcon = 'flag')
                    @php($settingsSidebarSubtitle = 'Student alerts')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @if(isset(auth()->user()->priv()['student_flags']) && auth()->user()->priv()['student_flags'] == 1)
                        <div class="ss-table-card ss-flags-card">
                            <div class="ss-table-card__header">
                                <h2>Student Flag List</h2>
                                <button data-tw-toggle="modal" data-tw-target="#addSettingsModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                    <i data-lucide="plus"></i>
                                    Add New Flag
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
                                <div id="settingsListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                            </div>
                        </div>
                    @else
                        <div class="ss-empty-state" role="alert">
                            <span><i data-lucide="alert-triangle"></i></span>
                            <div>
                                <h2>Permission Required</h2>
                                <p>You do not have enough permission to view this page's content. Please navigate to the menus on the left.</p>
                            </div>
                        </div>
                    @endif
                </section>
            </div>
        </main>

        <div id="addSettingsModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog">
                <form method="POST" action="#" id="addSettingsForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add New Flag</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="add_name">Name <span>*</span></label>
                                <input id="add_name" type="text" name="name" class="ss-modal-input name" placeholder="Flag name">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="add_color">Color <span>*</span></label>
                                <label class="ss-modal-select" for="add_color">
                                    <select id="add_color" name="color" class="color">
                                        <option value="">Please Select</option>
                                        <option value="Success">Green</option>
                                        <option value="Warning">Yellow</option>
                                        <option value="Danger">Red</option>
                                    </select>
                                    <i data-lucide="chevron-down"></i>
                                </label>
                                <div class="acc__input-error error-color"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="user_ids">Clearer</label>
                                <select id="user_ids" placeholder="Select Users" name="user_ids[]" multiple class="w-full tom-selects">
                                    <option value="">Please Select Raiser</option>
                                    @if(!empty($users))
                                        @foreach($users as $usr)
                                            <option value="{{ $usr->id }}">{{ (isset($usr->employee->full_name) && !empty($usr->employee->full_name) ? $usr->employee->full_name : $usr->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveSettings" class="ss-btn ss-btn--primary">
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

        <div id="editSettingsModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog">
                <form method="POST" action="#" id="editSettingsForm" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Flag</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="edit_name">Name <span>*</span></label>
                                <input id="edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Flag name">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="edit_color">Color <span>*</span></label>
                                <label class="ss-modal-select" for="edit_color">
                                    <select id="edit_color" name="color" class="color">
                                        <option value="">Please Select</option>
                                        <option value="Success">Green</option>
                                        <option value="Warning">Yellow</option>
                                        <option value="Danger">Red</option>
                                    </select>
                                    <i data-lucide="chevron-down"></i>
                                </label>
                                <div class="acc__input-error error-color"></div>
                            </div>

                            <div class="ss-modal-field">
                                <label for="edit_user_ids">Clearer</label>
                                <select id="edit_user_ids" placeholder="Select Users" name="user_ids[]" multiple class="w-full tom-selects">
                                    <option value="">Please Select Raiser</option>
                                    @if(!empty($users))
                                        @foreach($users as $usr)
                                            <option value="{{ $usr->id }}">{{ (isset($usr->employee->full_name) && !empty($usr->employee->full_name) ? $usr->employee->full_name : $usr->name) }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="updateSettings" class="ss-btn ss-btn--primary">
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
                            <input type="hidden" name="id" value="0">
                        </div>
                    </div>
                </form>
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
    @vite('resources/js/student-flag.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
