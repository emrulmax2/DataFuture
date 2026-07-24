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
        $letterPhases = [
            ['key' => 'admission', 'label' => 'Admission', 'note' => 'Applicant letters'],
            ['key' => 'live', 'label' => 'Live Student', 'note' => 'Student letters'],
            ['key' => 'hr', 'label' => 'Human Resource', 'note' => 'Staff letters'],
            ['key' => 'document_request', 'label' => 'Document Request', 'note' => 'ID replacement'],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page ss-letter-set-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Communication Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Letter Sets</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="scroll-text"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Create and manage reusable letter sets for admission, live student, HR and document request workflows.</p>
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
                    <div class="ss-table-card ss-letter-set-card">
                        <div class="ss-table-card__header">
                            <h2>Letter Set List</h2>
                            <button data-tw-toggle="modal" data-tw-target="#addLetterModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                <i data-lucide="plus"></i>
                                Add New Letter Set
                            </button>
                        </div>

                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm-LS" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query-LS">
                                        <i data-lucide="search"></i>
                                        <input id="query-LS" name="query" type="text" placeholder="Search...">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Phase</span>
                                    <label class="ss-filter-select" for="phase-LS">
                                        <select id="phase-LS" name="phase">
                                            <option value="">All</option>
                                            @foreach($letterPhases as $phase)
                                                <option value="{{ $phase['key'] }}">{{ $phase['label'] }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Status</span>
                                    <label class="ss-filter-select" for="status-LS">
                                        <select id="status-LS" name="status">
                                            <option value="3">All</option>
                                            <option selected value="1">Active</option>
                                            <option value="0">Inactive</option>
                                            <option value="2">Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go-LS" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset-LS" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>

                            <div class="ss-table-actions">
                                <button id="tabulator-print-LS" type="button" class="ss-btn ss-btn--light ss-btn--tool">
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
                                                <a id="tabulator-export-csv-LS" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-LS" href="javascript:;" class="dropdown-item">
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
                            <div id="letterSettingsListTable" class="ss-tabulator table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="addLetterModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--editor">
                <form method="POST" action="#" id="addLetterForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-letter-set-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add Letter Set</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field ss-modal-field--full ss-document-choices">
                                    <div class="ss-document-choices__heading">
                                        <span>Phase <em>*</em></span>
                                        <small>Select where this letter set can be used.</small>
                                    </div>
                                    <div class="ss-document-toggle-grid ss-letter-phase-grid">
                                        @foreach($letterPhases as $phase)
                                            <label class="ss-status-toggle ss-doc-toggle" for="phase_{{ $phase['key'] }}">
                                                <input id="phase_{{ $phase['key'] }}" class="phaseCheckboxs {{ $phase['key'] }}" name="phase[{{ $phase['key'] }}]" type="checkbox" value="1" autocomplete="off">
                                                <span class="ss-status-toggle__control">
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                </span>
                                                <span class="ss-status-toggle__copy">
                                                    <strong>{{ $phase['label'] }}</strong>
                                                    <small>{{ $phase['note'] }}</small>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="acc__input-error error-phase"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="letter_type">Letter Type <span>*</span></label>
                                    <input id="letter_type" type="text" name="letter_type" class="ss-modal-input form-control letter_type" placeholder="Letter type">
                                    <div class="acc__input-error error-letter_type"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="letter_title">Letter Title <span>*</span></label>
                                    <input id="letter_title" type="text" name="letter_title" class="ss-modal-input form-control letter_title" placeholder="Letter title">
                                    <div class="acc__input-error error-letter_title"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full ss-editor-field">
                                    <div class="ss-letter-editor-heading">
                                        <label for="addEditor">Description <span>*</span></label>
                                        <div class="ss-letter-tags-wrap">
                                            @include('pages.settings.letter.letter-tags')
                                        </div>
                                    </div>
                                    <div class="editor document-editor ss-editor">
                                        <div class="document-editor__toolbar"></div>
                                        <div class="document-editor__editable-container">
                                            <div class="document-editor__editable" id="addEditor"></div>
                                        </div>
                                    </div>
                                    <div class="acc__input-error error-description"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--third ss-letter-status-field">
                                    <label for="status">Status</label>
                                    <label class="ss-status-toggle ss-status-toggle--inline" for="status">
                                        <input id="status" class="status" name="status" type="checkbox" value="1" checked autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Letter set is active</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveLetterSet" class="ss-btn ss-btn--primary">
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

        <div id="editLetterModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--editor">
                <form method="POST" action="#" id="editLetterForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal ss-letter-set-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Letter Set</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field ss-modal-field--full ss-document-choices">
                                    <div class="ss-document-choices__heading">
                                        <span>Phase <em>*</em></span>
                                        <small>Select where this letter set can be used.</small>
                                    </div>
                                    <div class="ss-document-toggle-grid ss-letter-phase-grid">
                                        @foreach($letterPhases as $phase)
                                            <label class="ss-status-toggle ss-doc-toggle" for="edit_phase_{{ $phase['key'] }}">
                                                <input id="edit_phase_{{ $phase['key'] }}" class="phaseCheckboxs {{ $phase['key'] }}" name="phase[{{ $phase['key'] }}]" type="checkbox" value="1" autocomplete="off">
                                                <span class="ss-status-toggle__control">
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                                    <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                                </span>
                                                <span class="ss-status-toggle__copy">
                                                    <strong>{{ $phase['label'] }}</strong>
                                                    <small>{{ $phase['note'] }}</small>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="acc__input-error error-phase"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_letter_type">Letter Type <span>*</span></label>
                                    <input id="edit_letter_type" type="text" name="letter_type" class="ss-modal-input form-control letter_type" placeholder="Letter type">
                                    <div class="acc__input-error error-letter_type"></div>
                                </div>

                                <div class="ss-modal-field">
                                    <label for="edit_letter_title">Letter Title <span>*</span></label>
                                    <input id="edit_letter_title" type="text" name="letter_title" class="ss-modal-input form-control letter_title" placeholder="Letter title">
                                    <div class="acc__input-error error-letter_title"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--full ss-editor-field">
                                    <div class="ss-letter-editor-heading">
                                        <label for="editEditor">Description <span>*</span></label>
                                        <div class="ss-letter-tags-wrap">
                                            @include('pages.settings.letter.letter-tags')
                                        </div>
                                    </div>
                                    <div class="editor document-editor ss-editor">
                                        <div class="document-editor__toolbar"></div>
                                        <div class="document-editor__editable-container">
                                            <div class="document-editor__editable" id="editEditor"></div>
                                        </div>
                                    </div>
                                    <div class="acc__input-error error-description"></div>
                                </div>

                                <div class="ss-modal-field ss-modal-field--third ss-letter-status-field">
                                    <label for="edit_status">Status</label>
                                    <label class="ss-status-toggle ss-status-toggle--inline" for="edit_status">
                                        <input id="edit_status" class="status" name="status" type="checkbox" value="1" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Letter set is active</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="editLetterSet" class="ss-btn ss-btn--primary">
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
                        <button type="button" data-id="0" data-phase="" data-action="none" class="agreeWith ss-btn ss-btn--danger">
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
    @vite('resources/js/letter-set.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
