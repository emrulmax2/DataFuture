@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <form method="POST" action="#" id="studentDFForm">
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Datafuture Report</div>
                </div>
                <div class="col-span-6 text-right relative">
                    <input type="hidden" name="student_id" value="{{ $student->id }}" />
                    <input type="hidden" name="course_id" value="{{ $course_id }}" />
                    <input type="hidden" name="student_course_relation_id" value="{{ $student_course_relation_id }}"/>

                    <button data-tw-toggle="modal" data-tw-target="#addHesaInstanceModal" type="button" class="btn btn-facebook w-auto text-white ml-1"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Instance</button>
                    <a href="javascript:void(0);" class="btn btn-success w-auto text-white ml-1 hidden md:inline-flex"><i data-lucide="download" class="w-4 h-4 mr-2"></i>Download XML</a>
                    <button type="submit" id="saveDFBTN" class="btn btn-primary w-auto text-white ml-1 hidden md:inline-flex">
                        <i data-lucide="save-all" class="w-4 h-4 mr-2"></i>Update Data 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                </div>
            </div>
        </div>
        <div class="intro-y box mt-5 p-5 dfReportWrap">                      
            <div id="df-accordion-main" class="accordion accordion-boxed">
                <div class="accordion-item">
                    <div id="df-accr-main-content-1" class="accordion-header">
                        <button class="accordion-button bg_color_1" type="button" data-tw-toggle="collapse" data-tw-target="#df-accr-main-collapse-1" aria-expanded="true" aria-controls="df-accr-main-collapse-1">
                            Course
                            <span class="accordionCollaps"></span>
                        </button>
                    </div>
                    <div id="df-accr-main-collapse-1" class="accordion-collapse collapse show" aria-labelledby="df-accr-main-content-1" data-tw-parent="#df-accordion-main">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                            @include('pages.students.live.datafuture.course')
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div id="df-accr-main-content-2" class="accordion-header">
                        <button class="accordion-button bg_color_1 collapsed" type="button" data-tw-toggle="collapse" data-tw-target="#df-accr-main-collapse-2" aria-expanded="false" aria-controls="df-accr-main-collapse-2">
                            Modules
                            <span class="accordionCollaps"></span>
                        </button>
                    </div>
                    <div id="df-accr-main-collapse-2" class="accordion-collapse collapse" aria-labelledby="df-accr-main-content-2" data-tw-parent="#df-accordion-main">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                        @if($df_modules_fields->count() > 0)
                            @include('pages.students.live.datafuture.modules')
                        @else 
                            <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Module Data not found for the student.
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div id="df-accr-main-content-3" class="accordion-header">
                        <button class="accordion-button bg_color_1 collapsed" type="button" data-tw-toggle="collapse" data-tw-target="#df-accr-main-collapse-3" aria-expanded="false" aria-controls="df-accr-main-collapse-3">
                            Qualifications
                            <span class="accordionCollaps"></span>
                        </button>
                    </div>
                    <div id="df-accr-main-collapse-3" class="accordion-collapse collapse" aria-labelledby="df-accr-main-content-3" data-tw-parent="#df-accordion-main">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                            @include('pages.students.live.datafuture.qualification')
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div id="df-accr-main-content-4" class="accordion-header">
                        <button class="accordion-button bg_color_1 collapsed" type="button" data-tw-toggle="collapse" data-tw-target="#df-accr-main-collapse-4" aria-expanded="false" aria-controls="df-accr-main-collapse-4">
                            Session Years
                            <span class="accordionCollaps"></span>
                        </button>
                    </div>
                    <div id="df-accr-main-collapse-4" class="accordion-collapse collapse" aria-labelledby="df-accr-main-content-4" data-tw-parent="#df-accordion-main">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                            @include('pages.students.live.datafuture.session-years')
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div id="df-accr-main-content-5" class="accordion-header">
                        <button class="accordion-button bg_color_1 collapsed" type="button" data-tw-toggle="collapse" data-tw-target="#df-accr-main-collapse-5" aria-expanded="false" aria-controls="df-accr-main-collapse-5">
                            Student
                            <span class="accordionCollaps"></span>
                        </button>
                    </div>
                    <div id="df-accr-main-collapse-5" class="accordion-collapse collapse" aria-labelledby="df-accr-main-content-5" data-tw-parent="#df-accordion-main">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                            @include('pages.students.live.datafuture.student')
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <div id="df-accr-main-content-6" class="accordion-header">
                        <button class="accordion-button bg_color_1 collapsed" type="button" data-tw-toggle="collapse" data-tw-target="#df-accr-main-collapse-6" aria-expanded="false" aria-controls="df-accr-main-collapse-6">
                            Venue
                            <span class="accordionCollaps"></span>
                        </button>
                    </div>
                    <div id="df-accr-main-collapse-6" class="accordion-collapse collapse" aria-labelledby="df-accr-main-content-5" data-tw-parent="#df-accordion-main">
                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                            @include('pages.students.live.datafuture.venue')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @include('pages.students.live.datafuture.modals')
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-datafuture.js')
@endsection