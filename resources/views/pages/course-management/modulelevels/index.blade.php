@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @include('pages.course-management.partials.simple-list', [
                'listTitle' => 'Module Levels',
                'listNoun' => 'Module Level',
                'listTableId' => 'modulelevelTableId',
                'listFieldLabel' => 'Module Level Name',
                'listPlaceholder' => 'e.g. Level 5',
                'listPrivilege' => 'course_and_semesters',
            ])
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/course-modulelevel.js')
@endsection
