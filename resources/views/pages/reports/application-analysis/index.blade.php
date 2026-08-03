@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Application Analysis Report, on the redesigned admission shell.

        Every hook resources/js/applicant-analysis-report.js reaches for is
        preserved: #applicantAnalysisReportForm, #ap_an_semester_id,
        .error-ap_an_semester_id, #AplicntAnalysisReptBtn,
        #printPdfAplicntAnalysisBtn, #applicantAnalysisReptWrap,
        #viewUnknownEntryModal and #unknownEntryApplicantList.

        The spinner inside #AplicntAnalysisReptBtn is addressed by its
        `.loaders` class, not by `#AplicntAnalysisReptBtn svg` — the button
        also carries a leading refresh icon, which a first-<svg> match would
        have animated instead.
    --}}
    <div class="adm-card adm-rpt-head">
        <div class="adm-rpt-head__id">
            <span class="adm-rpt-head__icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"></path><path d="M7 14l4-4 3 3 5-6"></path></svg>
            </span>
            <div>
                <div class="adm-rpt-head__eyebrow">Reports</div>
                <h1 class="adm-rpt-head__title">Application Analysis Report</h1>
            </div>
        </div>

        <form method="post" action="#" id="applicantAnalysisReportForm" class="adm-rpt-head__form">
            @csrf
            <div class="adm-field adm-field--tom adm-rpt-head__field">
                <label for="ap_an_semester_id" class="adm-label adm-label--req semesterLabel">Intake Semester</label>
                <select name="ap_an_semester_id" class="tom-selects w-full" id="ap_an_semester_id">
                    <option value="">Please Select</option>
                    @if($semester->count() > 0)
                        @foreach($semester as $sem)
                            <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                        @endforeach
                    @endif
                </select>
                <div class="adm-field__error acc__input-error error-ap_an_semester_id"></div>
            </div>

            <div class="adm-rpt-head__actions">
                <button type="submit" id="AplicntAnalysisReptBtn" class="adm-btn adm-btn--primary adm-btn--go">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3.3-6.96"></path><path d="M21 3v6h-6"></path></svg>
                    Generate Report
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                        stroke="currentColor" class="w-4 h-4 ml-2 loaders">
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
                <a href="javascript:void(0);" style="display: none;" id="printPdfAplicntAnalysisBtn" class="adm-btn adm-btn--blue">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="M7 10l5 5 5-5"></path><path d="M12 15V3"></path></svg>
                    Download PDF
                </a>
                <a href="{{ route('admission') }}" class="adm-btn adm-btn--ghost">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                    Back to List
                </a>
            </div>
        </form>
    </div>

    <div id="applicantAnalysisReptWrap" class="adm-report" style="display: none;"></div>

    <!-- BEGIN: Unknown Entry Modal -->
    <div id="viewUnknownEntryModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Unknown Entry Applicants</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="adm-table-wrap">
                        <div id="unknownEntryApplicantList" class="table-report table-report--tabulator"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adm-btn adm-btn--soft">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Unknown Entry Modal -->
@endsection

@section('script')
    @vite('resources/js/applicant-analysis-report.js')
@endsection
