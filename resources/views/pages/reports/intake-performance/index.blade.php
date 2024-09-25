@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Intake Performance Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('reports') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Reports</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div id="intakePerformanceReportAccordion" class="accordion accordion-boxed pt-2">
            <div class="accordion-item">
                <div id="intakePerformanceReportAccordion-1" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#intakePerformanceReportAccordion-collapse-1" aria-expanded="false" aria-controls="intakePerformanceReportAccordion-collapse-1">
                        Continuation Rate
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="intakePerformanceReportAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-1" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <form method="post" action="#" id="continuationRateSearchForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="cr_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="cr_semester_id[]" multiple class="tom-selects w-full" id="cr_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-cr_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <button type="submit" id="continuationRateBtn" class="btn btn-success text-white w-auto ml-2">
                                        <i class="w-4 h-4 mr-2" data-lucide="file-text"></i> Export Excel
                                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                            stroke="white" class="w-4 h-4 ml-2 loaders">
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
                        </form>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="intakePerformanceReportAccordion-2" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#intakePerformanceReportAccordion-collapse-2" aria-expanded="false" aria-controls="intakePerformanceReportAccordion-collapse-2">
                        Retention Rate
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="intakePerformanceReportAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-2" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div class="alert alert-secondary-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Details comming soon...
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="intakePerformanceReportAccordion-3" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#intakePerformanceReportAccordion-collapse-3" aria-expanded="false" aria-controls="intakePerformanceReportAccordion-collapse-3">
                        Attendance Rate
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="intakePerformanceReportAccordion-collapse-3" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-3" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div class="alert alert-secondary-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Details comming soon...
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="intakePerformanceReportAccordion-4" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#intakePerformanceReportAccordion-collapse-4" aria-expanded="false" aria-controls="intakePerformanceReportAccordion-collapse-4">
                        Submission & Pass Rate
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="intakePerformanceReportAccordion-collapse-4" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-4" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div class="alert alert-secondary-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Details comming soon...
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="intakePerformanceReportAccordion-5" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#intakePerformanceReportAccordion-collapse-5" aria-expanded="false" aria-controls="intakePerformanceReportAccordion-collapse-5">
                        Award Rate
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="intakePerformanceReportAccordion-collapse-5" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-5" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div class="alert alert-secondary-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Details comming soon...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/intake-performance-reports.js')
@endsection