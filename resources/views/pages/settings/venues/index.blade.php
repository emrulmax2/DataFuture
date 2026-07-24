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
            <span>Campus Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Venues</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="map-pin"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage campuses, venues, rooms and venue datafuture settings.</p>
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
                    @if(isset(auth()->user()->priv()['campus_settings']) && auth()->user()->priv()['campus_settings'] == 1)
                        <div class="ss-table-card ss-venues-card">
                            <div class="ss-table-card__header">
                                <h2>Venues List</h2>
                                <button data-tw-toggle="modal" data-tw-target="#addVenueModal" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                    <i data-lucide="plus"></i>
                                    Add New Venue
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
                                <div id="venuesTableId" class="ss-tabulator table-report table-report--tabulator"></div>
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

        <div id="addVenueModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="addForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Add New Venue</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="add_name">Venue Name <span>*</span></label>
                                    <input id="add_name" type="text" name="name" class="ss-modal-input name" placeholder="Campus A, LCHC">
                                    <div class="acc__input-error error-name"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="add_idnumber">ID Number <span>*</span></label>
                                    <input id="add_idnumber" type="text" name="idnumber" class="ss-modal-input idnumber" placeholder="A">
                                    <div class="acc__input-error error-idnumber"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="add_ukprn">UKPRN <span>*</span></label>
                                    <input id="add_ukprn" type="text" name="ukprn" class="ss-modal-input ukprn" placeholder="10030391">
                                    <div class="acc__input-error error-ukprn"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="add_postcode">Postal Code</label>
                                    <input id="add_postcode" type="text" name="postcode" class="ss-modal-input postcode" placeholder="E1 2JA">
                                    <div class="acc__input-error error-postcode"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="add_address">Full Address</label>
                                    <textarea id="add_address" name="address" rows="3" class="ss-modal-input ss-modal-textarea address" placeholder="Full campus address"></textarea>
                                    <div class="acc__input-error error-address"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="add_ip_addresses">IP Address(es)</label>
                                    <textarea id="add_ip_addresses" name="ip_addresses" rows="3" class="ss-modal-input ss-modal-textarea ip_addresses" placeholder="62.31.206.211 | 140.228.78.66"></textarea>
                                    <small class="ss-modal-help">Use a pipe (|) to separate multiple IP addresses.</small>
                                    <div class="acc__input-error error-ip_addresses"></div>
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

        <div id="editVenueModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide">
                <form method="POST" action="#" id="editForm" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2>Edit Venue</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="ss-settings-modal__body">
                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="edit_name">Venue Name <span>*</span></label>
                                    <input id="edit_name" type="text" name="name" class="ss-modal-input name" placeholder="Campus A, LCHC">
                                    <div class="acc__input-error error-name"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="edit_idnumber">ID Number <span>*</span></label>
                                    <input id="edit_idnumber" type="text" name="idnumber" class="ss-modal-input idnumber" placeholder="A">
                                    <div class="acc__input-error error-idnumber"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="edit_ukprn">UKPRN <span>*</span></label>
                                    <input id="edit_ukprn" type="text" name="ukprn" class="ss-modal-input ukprn" placeholder="10030391">
                                    <div class="acc__input-error error-ukprn"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="edit_postcode">Postal Code</label>
                                    <input id="edit_postcode" type="text" name="postcode" class="ss-modal-input postcode" placeholder="E1 2JA">
                                    <div class="acc__input-error error-postcode"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="edit_address">Full Address</label>
                                    <textarea id="edit_address" name="address" rows="3" class="ss-modal-input ss-modal-textarea address" placeholder="Full campus address"></textarea>
                                    <div class="acc__input-error error-address"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="edit_ip_addresses">IP Address(es)</label>
                                    <textarea id="edit_ip_addresses" name="ip_addresses" rows="3" class="ss-modal-input ss-modal-textarea ip_addresses" placeholder="62.31.206.211 | 140.228.78.66"></textarea>
                                    <small class="ss-modal-help">Use a pipe (|) to separate multiple IP addresses.</small>
                                    <div class="acc__input-error error-ip_addresses"></div>
                                </div>
                                <div class="ss-modal-field ss-modal-field--full">
                                    <label for="edit_active">Active Status</label>
                                    <label class="ss-status-toggle" for="edit_active">
                                        <input type="hidden" name="active" value="0">
                                        <input id="edit_active" name="active" value="1" type="checkbox" autocomplete="off">
                                        <span class="ss-status-toggle__control">
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                            <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                        </span>
                                        <span class="ss-status-toggle__copy">
                                            <strong>Active</strong>
                                            <small>Available for campus setup</small>
                                        </span>
                                    </label>
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
    @vite('resources/js/venues.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
