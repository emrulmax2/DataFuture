@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('body_class', 'my-account-vacancies-body')

@section('subcontent')
    @include('pages.users.my-account.show-info')

    <section class="myhr-groups myhr-vacancies" data-screen-label="My Vacancies">
        <header class="myhr-groups__header">
            <span class="myhr-groups__header-icon">
                <i data-lucide="briefcase"></i>
            </span>
            <h2>My Vacancies</h2>
        </header>

        <div class="myhr-groups__toolbar">
            <form id="tabulatorFilterForm" class="myhr-groups-filter myhr-vacancies-filter">
                <span class="myhr-groups-filter__label">Query</span>
                <label class="myhr-groups-search">
                    <i data-lucide="search"></i>
                    <input id="query" name="query" type="text" placeholder="Search vacancies...">
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
            <div id="myVacancyListTable" class="myhr-groups-table table-report table-report--tabulator"></div>
        </div>
    </section>


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
    @vite('resources/js/user-vacancy.js')
@endsection
