@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('body_class', 'sdr-page')

@section('styles')
    @vite('resources/css/student-due-report.css')
@endsection

@section('subcontent')
    <div class="sdr">
        <div class="sdr-head">
            <div>
                <h1 class="sdr-head__title">Student Due Report</h1>
                <div class="sdr-head__sub">Outstanding balances across intakes &middot; generated {{ now()->format('H:i, d M Y') }}</div>
            </div>
            <div class="sdr-head__actions">
                <a href="{{ route('dashboard') }}" class="sdr-btn sdr-btn--ghost">
                    <i data-lucide="chevron-left" class="w-4 h-4"></i> Back to Dashboard
                </a>
                <button type="button" id="downloadXl" class="sdr-btn sdr-btn--solid">
                    <i data-lucide="file-text" class="w-4 h-4"></i> XL Export
                    <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                        stroke="white" class="w-4 h-4 theLoader">
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

        {{-- Totals for the whole filtered set; populated from the list response. --}}
        <div class="sdr-summary">
            <div class="sdr-stat">
                <div class="sdr-stat__label">Students with dues</div>
                <div class="sdr-stat__value is-loading" data-sdr-stat="students">&mdash;</div>
            </div>
            <div class="sdr-stat">
                <div class="sdr-stat__label">Claim total</div>
                <div class="sdr-stat__value is-loading" data-sdr-stat="claim">&mdash;</div>
            </div>
            <div class="sdr-stat sdr-stat--received">
                <div class="sdr-stat__label">Received</div>
                <div class="sdr-stat__value is-loading" data-sdr-stat="received">&mdash;</div>
            </div>
            <div class="sdr-stat sdr-stat--due">
                <div class="sdr-stat__label">Outstanding</div>
                <div class="sdr-stat__value is-loading" data-sdr-stat="due">&mdash;</div>
            </div>
        </div>

        <div class="sdr-panel sdr-panel--filters">
            <form method="post" action="#" id="studentDueReportForm">
                <div class="sdr-filters">
                    <div class="sdr-field">
                        <label for="due_semester_id" class="sdr-field__label semesterLabel">Intake Semester <span class="sdr-field__req">*</span></label>
                        <select name="due_semester_id[]" multiple class="tom-selects sdr-select w-full" id="due_semester_id">
                            <option value="">Please Select</option>
                            @if($semester->count() > 0)
                                @foreach($semester as $sem)
                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="sdr-field">
                        <label for="due_course_id" class="sdr-field__label courseLabel">Course</label>
                        <select name="due_course_id[]" multiple class="tom-selects sdr-select w-full" id="due_course_id">
                            <option value="">Please Select</option>
                            @if($courses->count() > 0)
                                @foreach($courses as $crs)
                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="sdr-field">
                        <label for="due_status_id" class="sdr-field__label">Status</label>
                        <select name="due_status_id[]" multiple class="tom-selects sdr-select w-full" id="due_status_id">
                            <option value="">Please Select</option>
                            @if($status->count() > 0)
                                @foreach($status as $sts)
                                    <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="sdr-filters__actions">
                        <button type="button" id="accDueSubmitBtn" class="sdr-btn sdr-btn--gold">
                            <i data-lucide="search" class="w-4 h-4"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="sdr-panel">
            <div class="sdr-panel__head">
                <h2 class="sdr-panel__title">Results</h2>
                <span class="sdr-panel__meta" data-sdr-result-meta>All intakes &middot; all courses</span>
                <span class="sdr-panel__count" data-sdr-result-count></span>
            </div>
            <div class="sdr-tablewrap scrollbar-hidden">
                <div id="studentDueReportList" class="table-report table-report--tabulator"></div>
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
    @vite('resources/js/student-due-reports.js')
@endsection
