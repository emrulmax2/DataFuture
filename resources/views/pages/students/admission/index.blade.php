@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Redesigned admission list.

        Every id the Tabulator/TomSelect/datepicker wiring in
        resources/js/admission.js reaches for is preserved verbatim:
        #tabulatorFilterForm-ADM, #refno-ADM, #firstname-ADM, #lastname-ADM,
        #dob-ADM, #email-ADM, #phone-ADM, #semesters-ADM, #courses-ADM,
        #statuses-ADM, #agents-ADM, #tabulator-html-filter-go-ADM,
        #tabulator-html-filter-reset-ADM, #tabulator-print-ADM,
        #tabulator-export-xlsx-ADM, #excelExportBtn and #admissionListTable.
        Only the surrounding markup changed.
    --}}
    <div class="adm-pagehead">
        <div>
            <h1 class="adm-pagehead__title">Students Admission</h1>
            <div class="adm-pagehead__sub">Search, review and manage all applications</div>
        </div>
        <a href="{{ route('dashboard') }}" class="add_btn adm-btn adm-btn--primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
            Back to Dashboard
        </a>
    </div>

    <div class="adm-toolbar">
        <button type="button" class="adm-btn" data-adm-filter-toggle="#admissionFilterPanel" data-adm-filter-key="adm.list.filters" aria-expanded="true">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 3H2l8 9.5V19l4 2v-8.5z"></path></svg>
            <span data-adm-filter-label>Hide Search Filters</span>
            <svg class="adm-filter-caret" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#9aa8b0" stroke-width="2.5"><path d="M6 9l6 6 6-6"></path></svg>
        </button>

        <div class="adm-toolbar__actions">
            <button type="button" id="tabulator-print-ADM" class="adm-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-3a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2M6 14h12v8H6z"></path></svg>
                Print
            </button>
            <button type="button" id="tabulator-export-xlsx-ADM" class="adm-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><path d="M14 2v6h6"></path></svg>
                Export Excel
                <svg id="excelExportBtn" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="w-4 h-4 ml-2">
                    <g fill="none" fill-rule="evenodd">
                        <g transform="translate(1 1)" stroke-width="4">
                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                            </path>
                        </g>
                    </g>
                </svg>
            </button>

            @if(isset(auth()->user()->priv()['applicant_analysis']) && auth()->user()->priv()['applicant_analysis'] == 1)
                <a href="{{ route('reports.applicant.analysis') }}" class="adm-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"></path><path d="M7 14l4-4 3 3 5-6"></path></svg>
                    Application Analysis
                </a>
            @endif
        </div>
    </div>

    <div id="admissionFilterPanel" class="adm-card adm-card--pad adm-collapse" style="margin-bottom:20px;">
        <form id="tabulatorFilterForm-ADM">
            <div class="adm-filter-grid">
                <div class="adm-field">
                    <label class="adm-label" for="refno-ADM">Ref. No.</label>
                    <input type="text" id="refno-ADM" name="refno-ADM" placeholder="Ref. No." value="" class="adm-input"/>
                </div>
                <div class="adm-field">
                    <label class="adm-label" for="firstname-ADM">First Name(s)</label>
                    <input type="text" id="firstname-ADM" name="firstname-ADM" placeholder="First Name" value="" class="adm-input"/>
                </div>
                <div class="adm-field">
                    <label class="adm-label" for="lastname-ADM">Last Name</label>
                    <input type="text" id="lastname-ADM" name="lastname-ADM" placeholder="Last Name" value="" class="adm-input"/>
                </div>
                <div class="adm-field">
                    <label class="adm-label" for="dob-ADM">Date of Birth</label>
                    <input type="text" id="dob-ADM" name="dob-ADM" placeholder="DD-MM-YYYY" value="" data-format="DD-MM-YYYY" data-single-mode="true" class="adm-input datepicker"/>
                </div>
                <div class="adm-field">
                    <label class="adm-label" for="email-ADM">Email</label>
                    <input type="text" id="email-ADM" name="email-ADM" placeholder="xyz@zyx.com" value="" class="adm-input"/>
                </div>
                <div class="adm-field">
                    <label class="adm-label" for="phone-ADM">Phone</label>
                    <input type="text" id="phone-ADM" name="phone-ADM" placeholder="07XXXXXXXXX" value="" class="adm-input"/>
                </div>
                <div class="adm-field adm-field--tom">
                    <label class="adm-label" for="semesters-ADM">Semester</label>
                    <select id="semesters-ADM" name="semesters[]" class="w-full tom-selects" multiple>
                        @if(!empty($semesters))
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="adm-field adm-field--tom">
                    <label class="adm-label" for="courses-ADM">Courses</label>
                    {{-- Options are injected by admission.js when a semester is chosen. --}}
                    <select id="courses-ADM" name="courses[]" class="w-full tom-selects" multiple></select>
                </div>
                <div class="adm-field adm-field--tom">
                    <label class="adm-label" for="statuses-ADM">Status</label>
                    <select id="statuses-ADM" name="statuses[]" class="w-full tom-selects" multiple>
                        @if(!empty($allStatuses))
                            @foreach($allStatuses as $sts)
                                <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="adm-field adm-field--tom">
                    <label class="adm-label" for="agents-ADM">Agent</label>
                    <select id="agents-ADM" name="agents[]" class="w-full tom-selects" multiple>
                        @if(!empty($agents))
                            @foreach($agents as $agt)
                                <option value="{{ $agt->agent_user_id }}">{{ (isset($agt->organization) ? $agt->organization : 'Unknown Organization') }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="adm-filter-foot">
                <div class="adm-filter-foot__group">
                    <button id="tabulator-html-filter-go-ADM" type="button" class="adm-btn adm-btn--primary adm-btn--go">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
                        Go
                    </button>
                    <button id="tabulator-html-filter-reset-ADM" type="button" class="adm-btn adm-btn--soft">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 019-9 9 9 0 016.7 3L21 8M21 3v5h-5"></path><path d="M21 12a9 9 0 01-9 9 9 9 0 01-6.7-3L3 16M3 21v-5h5"></path></svg>
                        Reset
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="adm-table-wrap">
        <div id="admissionListTable" class="table-report table-report--tabulator"></div>
    </div>

    @include('pages.students.admission.modals.confirmation')
    @include('pages.students.admission.modals.success')
    @include('pages.students.admission.modals.error')
@endsection

@section('script')
    @vite('resources/js/admission.js')
@endsection
