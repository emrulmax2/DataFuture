@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Do it online &middot; Services</div>
            <h1 class="spf-h1">Report an IT issue</h1>
            <div class="spf-page-head__sub">Issues reported by {{ $student->full_name }}.</div>
        </div>
        <div class="spf-spacer"></div>
        <button type="button" data-tw-toggle="modal" data-tw-target="#addModal" class="add_btn spf-btn spf-btn--dark spf-btn--sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Add new
        </button>
        <a href="{{ route('students.dashboard') }}" class="spf-btn spf-btn--sm">&larr; Back to dashboard</a>
    </div>

    {{-- Filters. The ids below are what `report-any-it-student.js` binds to. --}}
    <form id="tabulatorFilterForm" class="spf-toolbar">
        <input type="text" id="query" name="querystr" placeholder="Search by name or ID" value="" class="spf-input--pill"/>
        <select id="status" name="status" class="spf-input--pill" style="flex:0 0 auto;max-width:160px;cursor:pointer">
            <option value="1">Active</option>
            <option value="2">Archived</option>
        </select>
        <button id="tabulator-html-filter-go" type="button" class="spf-btn spf-btn--sm spf-btn--dark">Go</button>
        <button id="tabulator-html-filter-reset" type="button" class="spf-btn spf-btn--sm">Reset</button>
        <div class="spf-spacer"></div>
        <button id="tabulator-print" type="button" class="spf-btn spf-btn--sm">
            <i data-lucide="printer" class="w-4 h-4"></i> Print
        </button>
        <button id="tabulator-export-xlsx" type="button" class="spf-btn spf-btn--sm">
            <i data-lucide="file-text" class="w-4 h-4"></i> Export Excel
            <svg id="excelExportBtn" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                stroke="gray" class="w-4 h-4 ml-2">
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
    </form>

    <div class="spf-tablecard">
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="reportItAllTableId" class="mt-2 table-report table-report--tabulator"></div>
        </div>
    </div>

    @include('pages.students.report-it.student.modals.add-edit')
    @include('pages.students.report-it.student.modals.confirmation')
    @include('pages.students.report-it.student.modals.success')
    @include('pages.students.report-it.student.modals.error')
@endsection

@section('script')
    @vite('resources/js/report-any-it-student.js')
@endsection
