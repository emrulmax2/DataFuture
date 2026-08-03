@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @include('pages.course-management.partials.simple-list', [
                'listTitle' => 'Semesters',
                'listNoun' => 'Semester',
                'listTableId' => 'semesterTableId',
                'listFieldLabel' => 'Semester Name',
                'listPlaceholder' => 'e.g. 2026 September',
                'listPrivilege' => 'course_and_semesters',
            ])
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/course-semester.js')
@endsection
