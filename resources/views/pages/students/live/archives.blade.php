@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Archives -->
    <div class="intro-y box p-5 mt-5">
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-ARCV" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-ARCV" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-ARCV" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-ARCV" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="studentArchiveListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    <!-- END: Archives -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-archives.js')
@endsection