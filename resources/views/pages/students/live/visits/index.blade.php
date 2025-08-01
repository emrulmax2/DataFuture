@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->
    <!-- BEGIN: Visits -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-5 md:col-span-6">
                <div class="font-medium text-base">Visits</div>
            </div>
            <div class="col-span-7 md:col-span-6 text-right relative">
                @if(isset(auth()->user()->priv()['visit_add']) && auth()->user()->priv()['visit_add'] == 1)
                <a href="javascript:void(0)" class="btn btn-success text-white shadow-md mr-2 hidden md:inline-flex" data-tw-toggle="modal" data-tw-target="#addVisitModal">
                    <i data-lucide="plus" class="stroke-1.5 w-4 h-4 mr-2"></i> Add New Visit
                </a>
                @endif
            </div>
        </div>
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query_search" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="studentVisitsListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    <!-- END: Visits -->

    @include('pages.students.live.visits.modal')
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-visits.js')
    <script type="module">
        // (function () {

        // })()
    </script>
@endsection
