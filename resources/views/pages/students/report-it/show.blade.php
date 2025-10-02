@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    
    
    @include('pages.students.report-it.includes.title-info')

    @if(isset($employee))
        @include('pages.students.report-it.includes.employee.show-info')
    @elseif(isset($student))
        @include('pages.students.report-it.includes.student.show-info')
    @endif
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="col-span-12 lg:col-span-8 2xl:col-span-8">
            @include('pages.students.report-it.includes.show-left')
        </div>
        <div class="col-span-12 lg:col-span-4 2xl:col-span-4">
            @include('pages.students.report-it.includes.show-right')
        </div>
    </div>

    @include('pages.students.report-it.modals.add-edit')
    @include('pages.students.report-it.modals.confirmation')
    @include('pages.students.report-it.modals.success')
    @include('pages.students.report-it.modals.error')
@endsection

@section('script')
    @vite('resources/js/report-it-show.js')
@endsection