@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->
    <!-- BEGIN: Logs -->
    <div class="intro-y box mt-5 student-profile-loginlog">
        <div class="student-profile-secthead">
            <div class="student-profile-secthead-title">
                <div class="font-medium text-base">Logs</div>
            </div>
        </div>
        <div class="intro-y">
            <div class="student-profile-tablefilter">
                <form id="tabulatorFilterForm" class="student-profile-tablefilter-form">
                    <select id="logout_reason" name="logout_reason" class="form-select student-profile-tablefilter-status">
                        <option value="">All Status</option>
                        <option value="active">Active (logged in)</option>
                        <option value="manual_logout">Manual Logout</option>
                        <option value="session_timeout">Session Timeout</option>
                        <option value="session_invalidated">Session Invalidated</option>
                    </select>
                    <label class="student-profile-tablefilter-label" for="date_from">From</label>
                    <input id="date_from" name="date_from" type="date" class="form-control student-profile-tablefilter-date">
                    <label class="student-profile-tablefilter-label" for="date_to">To</label>
                    <input id="date_to" name="date_to" type="date" class="form-control student-profile-tablefilter-date">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary">Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-outline-secondary student-profile-tablefilter-reset">Reset</button>
                </form>
                <div class="student-profile-tablefilter-actions hidden md:flex">
                    <button id="tabulator-print" class="btn btn-outline-secondary">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                    <div class="dropdown">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
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
            <div class="student-profile-tablebody">
                <div id="loginLogTable" class="table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    <!-- END: Logs -->

    <input type="hidden" id="actor_id" value="{{ $student->student_user_id }}"/>
    <input type="hidden" id="actor_type" value="student_user"/>

@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-archives.js')
    @vite('resources/js/login-log_users.js')
@endsection