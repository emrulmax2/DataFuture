@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <!-- BEGIN: Archives -->
    <div class="intro-y box mt-5 student-profile-archives">
        <div class="student-profile-secthead">
            <div class="student-profile-secthead-title">
                <div class="font-medium text-base">Archives</div>
            </div>
        </div>
        <div class="intro-y">
            <div class="student-profile-tablefilter">
                <form id="tabulatorFilterForm-ARCV" class="student-profile-tablefilter-form" >
                    <input id="query-ARCV" name="query" type="text" class="form-control student-profile-tablefilter-search" placeholder="Search archives...">
                    <button id="tabulator-html-filter-go-ARCV" type="button" class="btn btn-primary" >Go</button>
                    <button id="tabulator-html-filter-reset-ARCV" type="button" class="btn btn-outline-secondary student-profile-tablefilter-reset" >Reset</button>
                </form>
            </div>
            <div class="student-profile-tablebody">
                <div id="studentArchiveListTable" data-student="{{ $student->id }}" class="table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>
    <!-- END: Archives -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-archives.js')
@endsection