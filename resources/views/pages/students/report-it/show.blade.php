@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('body_class', 'rit-page')

@section('styles')
    @vite('resources/css/report-it.css')
@endsection

@section('subcontent')
    <div class="rit">
        @include('pages.students.report-it.includes.title-info')

        @include('pages.students.report-it.includes.reporter-card')

        <div class="rit-detailgrid">
            @include('pages.students.report-it.includes.show-left')
            <div class="rit-aside">
                @include('pages.students.report-it.includes.show-right')
            </div>
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
