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
                        <div class="intro-y p-5 mt-5">
                            <form action="#" method="post" id="classStatusForm">
                                @csrf
                                <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="attendance_semester" class="form-label">Attendance Term <span class="text-danger">*</span></label>
                                        <select id="attendance_semester" class="w-full tom-selects" multiple name="attendance_semester">
                                            <option value="">Please Select</option>
                                            @if($terms->count() > 0)
                                                @foreach($terms as $trm)
                                                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-attendance_semester text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-9 ml-auto mt-auto text-right">
                                        <button type="button" id="classStatusFormBtn" class="btn btn-success text-white ml-auto w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i><i data-loading-icon="oval" data-color="white" class="w-4 h-4 mr-2 hidden loadingClass"></i> Search</button>
                                    </div>
                                </div>
                            </form>
                    
                            <div id="statusListTableWrap" class="overflow-x-auto scrollbar-hidden pt-5 statusReportListTableWrap" style="display: none;">
                                
                                <div id="statusListTable" class="mt-5 table-report table-report--tabulator"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <div id="termPerformanceReportAccordion-6" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#termPerformanceReportAccordion-collapse-6" aria-expanded="false" aria-controls="termPerformanceReportAccordion-collapse-6">
                        Submission Performance
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="termPerformanceReportAccordion-collapse-6" class="accordion-collapse collapse" aria-labelledby="termPerformanceReportAccordion-6" data-tw-parent="#termPerformanceReportAccordion">
                    <div class="accordion-body">
                        <form method="post" action="#" id="submissionPerformanceReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="sub_perf_term_id" class="form-label semesterLabel inline-flex items-center">Attendance Term <span class="text-danger">*</span></label>
                                    <select name="sub_perf_term_id" class="tom-selects w-full" id="sub_perf_term_id">
                                        <option value="">Please Select</option>
                                        @if($terms->count() > 0)
                                            @foreach($terms as $trm)
                                                <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-sub_perf_term_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="submissionPerformanceReportBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                        <a href="javascript:void(0);" style="display: none;" id="printSubmissionPerformanceReportBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                        <a href="javascript:void(0);" style="display: none;" id="exportSubmissionPerformanceReportBtn" class="btn btn-facebook text-white ml-2"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="submissionPerformanceReportWrap" style="display: none;"></div>
                    </div>
                </div>
            </div>
            <!--<div class="accordion-item">
                <div id="termPerformanceReportAccordion-7" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#termPerformanceReportAccordion-collapse-7" aria-expanded="false" aria-controls="termPerformanceReportAccordion-collapse-7">
                        Progression Report
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="termPerformanceReportAccordion-collapse-7" class="accordion-collapse collapse" aria-labelledby="termPerformanceReportAccordion-7" data-tw-parent="#termPerformanceReportAccordion">
                    <div class="accordion-body">
                        <form method="post" action="#" id="progressionReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="progression_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="progression_semester_id" class="tom-selects w-full" id="progression_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-progression_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="progressionReportBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                        <a href="javascript:void(0);" style="display: none;" id="printProgressionReportBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                        <a href="javascript:void(0);" style="display: none;" id="exportProgressionReportBtn" class="btn btn-facebook text-white ml-2"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="progressionReportWrap" style="display: none;"></div>
                    </div>
                </div>
            </div>-->
            <div class="accordion-item">
                <div id="termPerformanceReportAccordion-8" class="accordion-header">
                    <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#termPerformanceReportAccordion-collapse-8" aria-expanded="false" aria-controls="termPerformanceReportAccordion-collapse-8">
                        Retention Report
                        <span class="accordionCollaps"></span>
                    </button>
                </div>
                <div id="termPerformanceReportAccordion-collapse-8" class="accordion-collapse collapse" aria-labelledby="termPerformanceReportAccordion-8" data-tw-parent="#termPerformanceReportAccordion">
                    <div class="accordion-body">
                        <form method="post" action="#" id="termRetentionReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="retention_term_id" class="form-label semesterLabel inline-flex items-center">Attendance Term <span class="text-danger">*</span></label>
                                    <select name="retention_term_id[]" multiple class="tom-selects w-full" id="retention_term_id">
                                        <option value="">Please Select</option>
                                        @if($terms->count() > 0)
                                            @foreach($terms as $trm)
                                                <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-retention_term_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="termRetentionReportBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                        <a href="javascript:void(0);" style="display: none;" id="printtermRetentionReportBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                        <a href="javascript:void(0);" style="display: none;" id="exporttermRetentionReportBtn" class="btn btn-facebook text-white ml-2"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="termRetentionReportWrap" style="display: none;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div id="viewUnknownEntryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Student List</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="unknownEntryApplicantList" class="table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <div id="submissionPerformanceSTDListModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Student List</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="submissionPerformanceSTDListTable" class="table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
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

    @vite('resources/js/student-class-status-reports.js')
    @vite('resources/js/term-submission-performance-reports.js')
    @vite('resources/js/term-progression-reports.js')
    @vite('resources/js/term-retention-reports.js')
@endsection