@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('body_class', 'staff-result-submission-body')

@section('subcontent')
<div id="staffResultSubmission" class="staff-result-submission">
    @include('pages.tutor.module.includes.staff-result-header', ['activeResultMenu' => 'submission'])

    <div class="intro-y tab-content sr-tab-content">

        <div id="submission" class="tab-pane active" role="tabpanel" aria-labelledby="submission-tab">
            @include('pages.tutor.module.includes.submission-result')
        </div>
    </div>

    @include('pages.tutor.module.component.modal')
</div>
@endsection

@section('script')

    @vite('resources/js/results-staff-submission.js')
@endsection
