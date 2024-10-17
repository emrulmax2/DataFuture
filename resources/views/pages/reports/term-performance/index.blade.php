@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Term Performance Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('reports') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Reports</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div id="termPerformanceReportAccordion" class="accordion accordion-boxed pt-2">
            <div class="accordion-item">
                <div id="termPerformanceReportAccordion-1" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#termPerformanceReportAccordion-collapse-1" aria-expanded="false" aria-controls="termPerformanceReportAccordion-collapse-1">
                        Attendance Rates
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="termPerformanceReportAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="termPerformanceReportAccordion-1" data-tw-parent="#termPerformanceReportAccordion">
                    <div class="accordion-body">
                        <form method="post" action="#" id="termAttendanceRateSearchForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="term_declaration_id" class="form-label semesterLabel inline-flex items-center">Attendance Semester <span class="text-danger">*</span></label>
                                    <select name="term_declaration_id" class="tom-selects w-full" id="term_declaration_id">
                                        <option value="">Please Select</option>
                                        @if($terms->count() > 0)
                                            @foreach($terms as $term)
                                                <option value="{{ $term->id }}">{{ $term->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="termAttendanceRateSearchBtn" class="btn btn-success text-white w-auto ml-2">
                                            Generate Report
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
                                        <a href="javascript:void(0);" style="display: none;" id="viewTermAttendanceTrendBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="eye-off" class="w-4 h-4 mr-2"></i> View Trend</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="termAttendanceRateWrap" style="display: none;"></div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="termPerformanceReportAccordion-5" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#termPerformanceReportAccordion-collapse-5" aria-expanded="false" aria-controls="termPerformanceReportAccordion-collapse-5">
                        Class Status
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="termPerformanceReportAccordion-collapse-5" class="accordion-collapse collapse" aria-labelledby="termPerformanceReportAccordion-5" data-tw-parent="#termPerformanceReportAccordion">
                    <div class="accordion-body">
                        Comming Soon...
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
    @vite('resources/js/term-performance-reports.js')
    @vite('resources/js/term-attendance-performance-reports.js')
@endsection