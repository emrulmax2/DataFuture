@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Login Log</h2>
    </div>

    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start flex-wrap gap-2">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto flex-wrap gap-2">
                {{-- Text search --}}
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Search</label>
                    <input id="querystr" name="querystr" type="text"
                           class="form-control sm:w-44 2xl:w-full mt-2 sm:mt-0"
                           placeholder="Name / Email / IP…">
                </div>
                {{-- Actor type --}}
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-20 flex-none xl:w-auto xl:flex-initial mr-2">Type</label>
                    <select id="actor_type" name="actor_type" class="form-select w-full mt-2 sm:mt-0 sm:w-auto">
                        <option value="">All</option>
                        <option value="user">Staff User</option>
                        <option value="student_user">Student User</option>
                    </select>
                </div>
                {{-- Logout reason --}}
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-20 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                    <select id="logout_reason" name="logout_reason" class="form-select w-full mt-2 sm:mt-0 sm:w-auto">
                        <option value="">All</option>
                        <option value="active">Active (logged in)</option>
                        <option value="manual_logout">Manual Logout</option>
                        <option value="session_timeout">Session Timeout</option>
                        <option value="session_invalidated">Session Invalidated</option>
                    </select>
                </div>
                {{-- Date from --}}
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-20 flex-none xl:w-auto xl:flex-initial mr-2">From</label>
                    <input id="date_from" name="date_from" type="date"
                           class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0">
                </div>
                {{-- Date to --}}
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-20 flex-none xl:w-auto xl:flex-initial mr-2">To</label>
                    <input id="date_to" name="date_to" type="date"
                           class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0">
                </div>
                {{-- Buttons --}}
                <div class="mt-2 xl:mt-0 flex gap-1">
                    <button id="tabulator-html-filter-go" type="button"
                            class="btn btn-primary w-full sm:w-16">Go</button>
                    <button id="tabulator-html-filter-reset" type="button"
                            class="btn btn-secondary w-full sm:w-16 sm:ml-1">Reset</button>
                </div>
            </form>

            {{-- Export --}}
            <div class="flex mt-5 sm:mt-0">
                <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                </button>
                <div class="dropdown w-1/2 sm:w-auto">
                    <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto"
                            aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export
                        <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
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
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto scrollbar-hidden">
            <div id="loginLogTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
@endsection

@section('script')
    @vite('resources/js/login-log.js')
@endsection
