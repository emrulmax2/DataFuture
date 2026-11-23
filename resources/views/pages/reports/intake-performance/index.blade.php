@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter for the new Continuation / Retention / Attendance Rate reports and the Application Analysis report, as in their designs. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/application-analysis-report.css')
@endsection

@section('subcontent')
    <div class="intro-y ipr-pagehead">
        <div class="ipr-pagehead__lead">
            <span class="ipr-pagehead__icon" aria-hidden="true"><i data-lucide="line-chart"></i></span>
            <div class="ipr-pagehead__text">
                <h2 class="ipr-pagehead__title">Intake Performance Reports</h2>
                <p class="ipr-pagehead__sub">Follow an intake from application to award. Open a report, choose the intake semester and generate it.</p>
            </div>
        </div>
        <a href="{{ route('reports') }}" class="ipr-back"><i data-lucide="arrow-left"></i> Back to Reports</a>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y mt-5">
        @include('pages.reports.intake-performance.rate.styles')
        <div id="intakePerformanceReportAccordion" class="accordion ipr-list">
            <div class="accordion-item ipr-item ipr-tone-teal">
                @include('pages.reports.intake-performance.partials.report-head', ['n' => 1, 'title' => 'Continuation Rate', 'desc' => 'Registered students who did not drop out in their first year', 'icon' => 'trending-up', 'formats' => ['PDF']])
                <div id="intakePerformanceReportAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-1" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        {{-- New version beside the old one until the old report is retired. Toggle
                             buttons, not role="tab": the theme's tab script grabs every [role=tab]. --}}
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="Continuation Rate report version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="crnPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="crnPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="crnPaneNew" data-version-pane="new">
                            @include('pages.reports.intake-performance.rate.form', ['copy' => \App\Http\Controllers\Reports\IntakePerformance\ContinuationRateController::copy()])
                        </div>
                        <div id="crnPaneOld" data-version-pane="old" hidden>
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
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="continuationRateBtn" class="btn btn-success text-white w-auto ml-2">
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
                                            {{-- After the loader: the old script toggles the first svg in this button. CSS order puts the icon first. --}}
                                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 ipr-btn-lead"></i>
                                        </button>
                                        <a href="javascript:void(0);" style="display: none;" id="printPdfContinuationRateBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="continuationRateWrap" style="display: none;"></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item ipr-item ipr-tone-blue">
                @include('pages.reports.intake-performance.partials.report-head', ['n' => 2, 'title' => 'Retention Rate', 'desc' => 'Registered students who stayed to the end of their course', 'icon' => 'user-check', 'formats' => ['PDF', 'Excel']])
                <div id="intakePerformanceReportAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-2" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="Retention Rate report version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="rtnPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="rtnPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="rtnPaneNew" data-version-pane="new">
                            @include('pages.reports.intake-performance.rate.form', ['copy' => \App\Http\Controllers\Reports\IntakePerformance\RetentionRateController::copy()])
                        </div>
                        <div id="rtnPaneOld" data-version-pane="old" hidden>
                    <form method="post" action="#" id="retentionRateSearchForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="rtn_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="rtn_semester_id[]" multiple class="tom-selects w-full" id="rtn_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-rtn_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="retentionRateBtn" class="btn btn-success text-white w-auto ml-2">
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
                                            {{-- After the loader: the old script toggles the first svg in this button. CSS order puts the icon first. --}}
                                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 ipr-btn-lead"></i>
                                        </button>
                                        <a href="javascript:void(0);" style="display: none;" id="printPdfRetentionRateBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                        <a href="javascript:void(0);" style="display: none;" id="exportRetentionRateBtn" class="btn btn-twitter text-white ml-2"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="retentionRateWrap" style="display: none;"></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item ipr-item ipr-tone-amber">
                @include('pages.reports.intake-performance.partials.report-head', ['n' => 3, 'title' => 'Attendance Rate', 'desc' => 'Attendance of registered students by course and term', 'icon' => 'calendar-check', 'formats' => ['PDF']])
                <div id="intakePerformanceReportAccordion-collapse-3" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-3" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="Attendance Rate report version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="atnPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="atnPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="atnPaneNew" data-version-pane="new">
                            @include('pages.reports.intake-performance.rate.form', ['copy' => \App\Http\Controllers\Reports\IntakePerformance\AttendanceRateController::copy()])
                        </div>
                        <div id="atnPaneOld" data-version-pane="old" hidden>
                        <form method="post" action="#" id="attendanceRateSearchForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="atn_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="atn_semester_id[]" multiple class="tom-selects w-full" id="atn_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-atn_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="IntakeAttnRateBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                            {{-- After the loader: the old script toggles the first svg in this button. CSS order puts the icon first. --}}
                                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 ipr-btn-lead"></i>
                                        </button>
                                        <a href="javascript:void(0);" style="display: none;" id="printPdfAtnRateBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="attendanceRateWrap" style="display: none;"></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item ipr-item ipr-tone-violet">
                @include('pages.reports.intake-performance.partials.report-head', ['n' => 4, 'title' => 'Submission & Pass Rate', 'desc' => 'Assignments submitted and passed, by course and term', 'icon' => 'file-check-2', 'formats' => ['PDF']])
                <div id="intakePerformanceReportAccordion-collapse-4" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-4" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="Submission and Pass Rate report version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="sprPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="sprPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="sprPaneNew" data-version-pane="new">
                            @include('pages.reports.intake-performance.rate.form', ['copy' => \App\Http\Controllers\Reports\IntakePerformance\SubmissionPassRateController::copy()])
                        </div>
                        <div id="sprPaneOld" data-version-pane="old" hidden>
                        <form method="post" action="#" id="submissionPassRateSearchForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="sub_pass_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="sub_pass_semester_id[]" multiple class="tom-selects w-full" id="sub_pass_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-sub_pass_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="SubPassRateBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                            {{-- After the loader: the old script toggles the first svg in this button. CSS order puts the icon first. --}}
                                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 ipr-btn-lead"></i>
                                        </button>
                                        <a href="javascript:void(0);" style="display: none;" id="printPdfSubPassRateBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="submissionPassRateWrap" style="display: none;"></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item ipr-item ipr-tone-rust">
                @include('pages.reports.intake-performance.partials.report-head', ['n' => 5, 'title' => 'Award Rate', 'desc' => 'Registered students who completed their award', 'icon' => 'award', 'formats' => ['PDF']])
                <div id="intakePerformanceReportAccordion-collapse-5" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-5" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="Award Rate report version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="awdPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="awdPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="awdPaneNew" data-version-pane="new">
                            @include('pages.reports.intake-performance.rate.form', ['copy' => \App\Http\Controllers\Reports\IntakePerformance\AwardRateController::copy()])
                        </div>
                        <div id="awdPaneOld" data-version-pane="old" hidden>
                        <form method="post" action="#" id="awardRateSearchForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="award_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="award_semester_id[]" multiple class="tom-selects w-full" id="award_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-award_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="awardRateBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                            {{-- After the loader: the old script toggles the first svg in this button. CSS order puts the icon first. --}}
                                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 ipr-btn-lead"></i>
                                        </button>
                                        <a href="javascript:void(0);" style="display: none;" id="printPdfAwardRateBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="awardRateWrap" style="display: none;"></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item ipr-item ipr-tone-rose">
                @include('pages.reports.intake-performance.partials.report-head', ['n' => 6, 'title' => 'Application Analysis', 'desc' => 'Applications for an intake by status, entry route and age', 'icon' => 'pie-chart', 'formats' => ['PDF']])
                <div id="intakePerformanceReportAccordion-collapse-6" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-6" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        <form method="post" action="#" id="applicantAnalysisReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="ap_an_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="ap_an_semester_id" class="tom-selects w-full" id="ap_an_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semester->count() > 0)
                                            @foreach($semester as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-ap_an_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="AplicntAnalysisReptBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                            {{-- After the loader: the old script toggles the first svg in this button. CSS order puts the icon first. --}}
                                            <i data-lucide="bar-chart-3" class="w-4 h-4 mr-2 ipr-btn-lead"></i>
                                        </button>
                                        <a href="javascript:void(0);" style="display: none;" id="printPdfAplicntAnalysisBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="applicantAnalysisReptWrap" style="display: none;"></div>
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
    @vite('resources/js/intake-continuation-reports.js')
    @vite('resources/js/intake-rate-reports.js')
    @vite('resources/js/intake-retention-reports.js')
    @vite('resources/js/intake-attendance-rate-reports.js')
    @vite('resources/js/intake-submission-pass-rate-reports.js')
    @vite('resources/js/intake-award-rate-reports.js')
    @vite('resources/js/applicant-analysis-report.js')
@endsection