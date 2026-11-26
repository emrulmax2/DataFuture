@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Page title and report cards as Intake / Term Performance (user, 25 Nov 2026). --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/slc-reports.css')
@endsection

@section('subcontent')
    <div class="intro-y ipr-pagehead">
        <div class="ipr-pagehead__lead">
            <span class="ipr-pagehead__icon" aria-hidden="true"><i data-lucide="landmark"></i></span>
            <div class="ipr-pagehead__text">
                <h2 class="ipr-pagehead__title">SLC Reports</h2>
                <p class="ipr-pagehead__sub">Student Loans Company records: attendance confirmations, registrations, changes of circumstances and each intake's SLC position. Open a report, set the filters and export it.</p>
            </div>
        </div>
        <a href="{{ route('reports') }}" class="ipr-back"><i data-lucide="arrow-left"></i> Back to Reports</a>
    </div>
    <!-- BEGIN: HTML Table Data -->
    {{-- One card per report, no wrapper box. The ids stay: student-slc-reports.js opens the card in the URL hash. --}}
    <div class="intro-y mt-5">
        <div id="intakePerformanceReportAccordion" class="accordion ipr-list">
            <div class="accordion-item ipr-item ipr-tone-teal">
                @include('pages.reports.slc.partials.report-head', ['n' => 1, 'title' => 'SLC Attendance History', 'desc' => 'Attendance confirmations sent to SLC, by date, attendance code, year and term', 'icon' => 'calendar-check-2', 'formats' => ['Excel']])
                <div id="intakePerformanceReportAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-1" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        {{-- New version beside the old Excel-only form until the old one is retired (user, 25 Nov 2026). --}}
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="SLC Attendance History version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="sahPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="sahPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="sahPaneNew" data-version-pane="new">
                            @include('pages.reports.slc.attendance.new')
                        </div>
                        <div id="sahPaneOld" data-version-pane="old" hidden>
                        <form method="post" action="{{ route('reports.slc.attendance.excel.export') }}" id="attendanceSLCReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <div class="w-auto">
                                        <label for="date_range" class="form-label">Date Range <span class="text-danger">*</span></label>
                                        <div class="relative w-full mx-auto mr-2">
                                            <div class="absolute rounded-l w-10 h-full flex items-center justify-center bg-slate-100 border text-slate-500">
                                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" id="date_range" name="date_range" class="datepicker form-control pl-12" data-format="DD-MM-YYYY" data-daterange="true">
                                        </div>
                                        <div class="acc__input-error error-date_range text-danger mt-2">{{ ($errors->has('date_range') ? $errors->first('date_range') : '')}}</div>
                                    </div>
                                </div>
                                <div class="col-span-3">
                                    <label for="attendance_code_id" class="form-label semesterLabel inline-flex items-center">Attendance Code </label>
                                    <select name="attendance_code_id" multiple class="tom-selects w-full" id="attendance_code_id">
                                        <option value="">Please Select</option>
                                        @if($attendanceCodes->count() > 0)
                                            @foreach($attendanceCodes as $attendanceCode)
                                                <option value="{{ $attendanceCode->id }}">{{ $attendanceCode->code }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label for="attendance_year" class="form-label semesterLabel inline-flex items-center">Attendance Year</label>
                                    <select name="attendance_year" multiple class="tom-selects w-full" id="attendance_year">
                                        <option value="">Please Select</option>
                                        <option value="1">Year 1</option>
                                        <option value="2">Year 2</option>
                                        <option value="3">Year 3</option>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label for="term_declaration_id" class="form-label  inline-flex items-center">Attendance Term</label>
                                    <select name="session_term[]" multiple class="tom-selects w-full" id="term_declaration_id">
                                        <option value="">Please Select</option>
                                        <option value="1">Term 1</option>
                                        <option value="2">Term 2</option>
                                        <option value="3">Term 3</option>
                                    </select>
                                </div>
                                <div class="col-span-12 ml-auto text-right py-2" >
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="excelSubmitBtn" class="btn btn-success text-white w-auto ml-2">
                                            Excel Export 
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
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="continuationRateWrap" style="display: none;"></div>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="accordion-item ipr-item ipr-tone-blue">
                @include('pages.reports.slc.partials.report-head', ['n' => 2, 'title' => 'SLC Registration History', 'desc' => 'SLC registrations by date, registration status and academic year', 'icon' => 'badge-check', 'formats' => ['Excel']])
                <div id="intakePerformanceReportAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-2" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        {{-- New version beside the old Excel-only form until the old one is retired (user, 25 Nov 2026). --}}
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="SLC Registration History version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="srhPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="srhPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="srhPaneNew" data-version-pane="new">
                            @include('pages.reports.slc.registration.new')
                        </div>
                        <div id="srhPaneOld" data-version-pane="old" hidden>
                    <form method="post" action="#" id="registrationSLCForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <div class="w-auto">
                                        <label for="date_range" class="form-label">Date Range <span class="text-danger">*</span></label>
                                        <div class="relative w-full mx-auto">
                                            <div class="absolute rounded-l w-10 h-full flex items-center justify-center bg-slate-100 border text-slate-500">
                                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" id="date_range1" name="date_range" class="datepicker form-control pl-12" data-format="DD-MM-YYYY" data-daterange="true">
                                        </div>
                                        <div class="acc__input-error error-date_range text-danger mt-2">{{ ($errors->has('date_range') ? $errors->first('date_range') : '')}}</div>
                                    </div>
                                </div>
                                
                                <div class="col-span-3">
                                    <label for="slc_registration_status_id" class="form-label semesterLabel inline-flex items-center">Registration Status</label>
                                    <select name="slc_registration_status_id" multiple class="tom-selects w-full" id="slc_registration_status_id">
                                        <option value="">Please Select</option>
                                        @if($slcRegistrationStatuses->count() > 0)
                                            @foreach($slcRegistrationStatuses as $slcRegistrationStatus)
                                                <option value="{{ $slcRegistrationStatus->id }}">{{ $slcRegistrationStatus->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label for="registration_year" class="form-label semesterLabel inline-flex items-center">Registration Year</label>
                                    <select name="registration_year" multiple class="tom-selects w-full" id="registration_year">
                                        <option value="">Please Select</option>
                                        <option value="1">Year 1</option>
                                        <option value="2">Year 2</option>
                                        <option value="3">Year 3</option>
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <label for="academic_year_id" class="form-label  inline-flex items-center">Academic Year</label>
                                    <select name="academic_year_id" multiple class="tom-selects w-full" id="academic_year_id">
                                        <option value="">Please Select</option>
                                        @if($academicYears->count() > 0)
                                            @foreach($academicYears as $academicYear)
                                                <option value="{{ $academicYear->id }}">{{ $academicYear->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                                <div class="col-span-12 ml-auto text-right py-2">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="excelSubmitBtn1" class="btn btn-success text-white w-auto ml-2">
                                            Excel Export 
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
                @include('pages.reports.slc.partials.report-head', ['n' => 3, 'title' => 'SLC COC History', 'desc' => 'Changes of circumstances reported to SLC, by date, type and whether actioned', 'icon' => 'file-diff', 'formats' => ['Excel']])
                <div id="intakePerformanceReportAccordion-collapse-3" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-3" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        {{-- New version beside the old Excel-only form until the old one is retired (user, 25 Nov 2026). --}}
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="SLC COC History version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="schPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="schPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="schPaneNew" data-version-pane="new">
                            @include('pages.reports.slc.coc.new')
                        </div>
                        <div id="schPaneOld" data-version-pane="old" hidden>
                        <form method="post" action="#" id="SLCcocReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-4">
                                    <div class="relative w-full">
                                        <label for="date_range" class="form-label">Date Range <span class="text-danger">*</span></label>
                                        <div class="relative w-full mx-auto">
                                            <div class="absolute rounded-l w-10 h-full flex items-center justify-center bg-slate-100 border text-slate-500">
                                                <i data-lucide="calendar" class="w-4 h-4"></i>
                                            </div>
                                            <input type="text" id="date_range2" name="date_range" class="datepicker form-control pl-12" data-format="DD-MM-YYYY" data-daterange="true">
                                        </div>
                                        <div class="acc__input-error error-date_range text-danger mt-2">{{ ($errors->has('date_range') ? $errors->first('date_range') : '')}}</div>
                                    </div>
                                    
                                </div>
                                <div class="col-span-4">
                                    <label for="coc_type" class="form-label semesterLabel inline-flex items-center">COC TYPE</label>
                                    <select name="coc_type" multiple class="tom-selects w-full" id="coc_type">
                                        <option value="">Please Select</option>
                                        <option value="Withdrawal">Withdrawal</option>
                                        <option value="Suspension">Suspension</option>
                                        <option value="Resumption">Resumption</option>
                                        <option value="Repetition">Repetition</option>
                                        <option value="Transfer">Transfer</option>
                                        <option value="Fee">Fee</option>
                                    </select>
                                </div>
                                <div class="col-span-4">
                                    <label for="actioned" class="form-label semesterLabel inline-flex items-center">Actioned</label>
                                    <select name="actioned" multiple class="tom-selects w-full" id="actioned">
                                        <option value="">Please Select</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </div>
                                <div class="col-span-12 ml-auto text-right py-2">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="excelSubmitBtn3" class="btn btn-primary text-white w-auto ml-2">
                                            Excel Export 
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
                @include('pages.reports.slc.partials.report-head', ['n' => 4, 'title' => 'SLC Record Report', 'desc' => 'Each intake from first registration to SLC registration, attendance confirmed, withdrawn and self funded', 'icon' => 'table-properties', 'formats' => ['PDF', 'Excel']])
                <div id="intakePerformanceReportAccordion-collapse-4" class="accordion-collapse collapse" aria-labelledby="intakePerformanceReportAccordion-4" data-tw-parent="#intakePerformanceReportAccordion">
                    <div class="accordion-body">
                        {{-- New version beside the old one until the old report is retired (user, 25 Nov 2026). --}}
                        <div data-version-tabs>
                        <div class="crn-tabs" aria-label="SLC Record Report version">
                            <button type="button" class="crn-tab is-active" aria-pressed="true" aria-controls="srrPaneNew" data-version="new">New version</button>
                            <button type="button" class="crn-tab" aria-pressed="false" aria-controls="srrPaneOld" data-version="old">Old version</button>
                        </div>
                        <div id="srrPaneNew" data-version-pane="new">
                            @include('pages.reports.slc.record.new')
                        </div>
                        <div id="srrPaneOld" data-version-pane="old" hidden>
                        <form method="post" action="#" id="slcRecoredReportForm">
                            @csrf
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-3">
                                    <label for="srr_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                                    <select name="srr_semester_id[]" multiple class="tom-selects w-full" id="srr_semester_id">
                                        <option value="">Please Select</option>
                                        @if($semesters->count() > 0)
                                            @foreach($semesters as $sem)
                                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-srr_semester_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-9 text-right" style="padding-top: 31px;">
                                    <div class="flex justify-end items-center">
                                        <button type="submit" id="slcRecoredReportBtn" class="btn btn-primary text-white w-auto ml-2">
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
                                        <a href="javascript:void(0);" style="display: none;" id="printPdfslcRecoredReportBtn" class="btn btn-linkedin text-white ml-2"><i data-lucide="printer" class="w-4 h-4 mr-2"></i> Download PDF</a>
                                        <a href="javascript:void(0);" style="display: none;" id="exportXlslcRecoredReportBtn" class="btn btn-facebook text-white ml-2"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export Excel</a>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="overflow-x-auto scrollbar-hidden mt-5" id="slcRecoredReportWrap" style="display: none;"></div>
                        </div>
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
    @vite('resources/js/slc-attendance-history.js')
    @vite('resources/js/slc-registration-history.js')
    @vite('resources/js/slc-coc-history.js')
    @vite('resources/js/slc-record.js')
    @vite('resources/js/student-slc-reports.js')
    @vite('resources/js/student-slc-recored-reports.js')
@endsection