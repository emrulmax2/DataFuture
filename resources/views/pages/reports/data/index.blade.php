@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    {{-- Inter, as in the user's student-data-report design. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/student-data-report.css')
@endsection

@php
    /*
     * Export columns (26 Nov 2026, user's student-data-report design). Shown in the design's order;
     * `order` is each checkbox's place on the old page. The server builds the Excel columns in the
     * order the names arrive, so the script posts the ticked ones by `order`: the sheet keeps its
     * old column order. Names are the old checkbox names, unchanged.
     */
    $sdrGroups = [
        ['id' => 'personal', 'title' => 'Personal Details', 'fields' => [
            ['Student[full_name]', 'Name', 1],
            ['Student[date_of_birth]', 'Date of Birth', 4],
            ['Student[sex_identifier_id][sexid]', 'Sex Identifier/Gender', 7],
            ['Student[nationality_id][nation]', 'Nationality', 2],
            ['Student[country_id][country]', 'Country of Birth', 5],
            ['Student[DF_SID_Number]', 'DF SID Number', 8],
            ['Student[ssn_no]', 'SSN', 3],
            ['Student[uhn_no]', 'UHN Number', 6],
            ['Student[hesa_status]', 'HESA Status', 9],
        ]],
    ];
    if (isset(auth()->user()->priv()['student_other_details_report_show']) && auth()->user()->priv()['student_other_details_report_show'] == 1) {
        $sdrGroups[] = ['id' => 'other', 'title' => 'Other Details', 'sensitive' => true, 'fields' => [
            ['StudentOtherDetail[sexual_orientation_id][sexori]', 'Sexual Orientation', 10],
            ['StudentOtherDetail[hesa_gender_id][gender]', 'Gender Identity', 14],
            ['StudentOtherDetail[ethnicity_id][ethnicity]', 'Ethnicity', 15],
            ['StudentOtherDetail[disability_status]', 'Disability Status', 11],
            ['StudentOtherDetail[religion_id][religion]', 'Religion or Belief', 16],
            ['StudentResidency[residency_status][residency]', 'Residency Status', 12],
            ['StudentOtherDetail[care_leaver_id][leaver]', 'Care Leaver', 17],
            ['StudentCriminalConviction[criminal_conviction][criminalConviction]', 'Criminal Conviction Details', 13],
        ]];
    }
    $sdrGroups = array_merge($sdrGroups, [
        ['id' => 'course', 'title' => 'Course Details', 'fields' => [
            ['StudentCourseRelation[course_relation_id][course]', 'Course Name', 18],
            ['StudentCourseRelation[semester]', 'Batch Name (Intake Semester)', 22],
            ['StudentProposedCourse[full_time]', 'Evening and Weekend Status', 25],
            ['StudentCourseRelation[course_start_date]', 'Start Date', 19],
            ['StudentCourseRelation[course_end_date]', 'End Date', 20],
            ['StudentAttendanceTermStatus[status_change_date]', 'Status Change Date', 26],
            ['StudentCourseRelation[awarding_body]', 'Awarding Body', 23],
            ['StudentAwardingBodyDetails[awarding_body_reference]', 'Awarding Body Ref No', 24],
            ['StudentProposedCourse[SLC_course_code][venue]', 'SLC Course Code', 21],
        ]],
        ['id' => 'plan', 'title' => 'Plan Data', 'fields' => [
            ['StudentPlan[group_Id]', 'Group', 27],
            ['StudentPlan[venue_Id]', 'Venue', 28],
        ]],
        ['id' => 'proof', 'title' => 'Proof of ID Checks', 'fields' => [
            ['StudentProofOfId[proof_type]', 'Proof Type', 29],
            ['StudentProofOfId[proof_id]', 'Proof ID', 30],
            ['StudentProofOfId[proof_expiredate]', 'Proof Expiry Date', 31],
        ]],
        ['id' => 'contact', 'title' => 'Contact Details', 'fields' => [
            ['StudentContact[term_time_address_id][termaddress]', 'Term Time Address', 32],
            ['StudentContact[term_polar4_imd_25][termaddress]', 'Term Time POLAR4 Quintile & IMD 25', 33],
            ['StudentContact[permanent_address_id][permaddress]', 'Permanent Address', 34],
            ['StudentContact[perm_polar4_imd_25][permaddress]', 'Permanent POLAR4 Quintile & IMD 25', 35],
            ['StudentContact[personal_email]', 'Personal Email', 36],
            ['StudentContact[institutional_email]', 'Institutional Email', 37],
            ['StudentContact[home]', 'Home Phone', 38],
            ['StudentContact[mobile]', 'Mobile', 39],
        ]],
        ['id' => 'kin', 'title' => 'Next of Kin', 'fields' => [
            ['StudentKin[name]', 'Name', 40],
            ['StudentKin[kins_relation_id][relation]', 'Relation', 42],
            ['StudentKin[mobile]', 'Mobile', 44],
            ['StudentKin[email]', 'Email', 41],
            ['StudentKin[address_id][address]', 'Address', 43],
        ]],
        ['id' => 'entry', 'title' => 'Entry Qualification', 'fields' => [
            ['StudentQualification[highest_qualification_on_Entry][qualHigest]', 'Highest Qualification on Entry (QUALENT3)', 45],
            ['StudentQualification[qualification_details][qualHigest]', 'Qualification Details', 46],
        ]],
        ['id' => 'referral', 'title' => 'Referral', 'fields' => [
            ['Student[application_no]', 'Application Ref. No', 47],
            ['Student[submission_date]', 'Date of Application', 48],
            ['Student[referral_code]', 'Code', 49],
            ['AgentReferralCode[type]', 'Type', 50],
            ['AgentReferralCode[referral_name]', 'Name', 51],
        ]],
        ['id' => 'accounts', 'title' => 'Accounts', 'fields' => [
            ['slcAccount[is_self_funded]', 'Self Funding', 52],
            ['Student[multi_agreement_status]', 'Multi Agreement', 53],
        ]],
    ]);
    $sdrFieldTotal = array_sum(array_map(fn ($g) => count($g['fields']), $sdrGroups));
@endphp

@section('subcontent')
    {{-- Page title as on Term Performance Reports. --}}
    <div class="intro-y ipr-pagehead">
        <div class="ipr-pagehead__lead">
            <span class="ipr-pagehead__icon" aria-hidden="true"><i data-lucide="file-spreadsheet"></i></span>
            <div class="ipr-pagehead__text">
                <h2 class="ipr-pagehead__title">Student Data Report</h2>
                <p class="ipr-pagehead__sub">Filter the students you need, choose the columns, then export to Excel.</p>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="ipr-back"><i data-lucide="arrow-left"></i> Back to Dashboard</a>
    </div>

    <div class="sdr" id="studentDataReport">
        <div class="sdr-layout">
            <div class="sdr-main">
                {{-- 1. Find students: same fields, names and ids as before (student-data-search-form.js drives them). --}}
                <form action="#" method="post" id="studentGroupSearchForm" class="sdr-card" autocomplete="off">
                    @csrf
                    <div class="sdr-card-head">
                        <h2 class="sdr-step"><i>1</i>Find students</h2>
                        <small id="sdrFilterCount">No filters applied</small>
                    </div>
                    <div class="sdr-filters">
                        <div class="sdr-f" data-sdr-filter>
                            <label for="intake_semester">Intake Semester</label>
                            <select id="intake_semester" class="w-full tom-selects" multiple name="group[intake_semester][]" data-placeholder="All intakes">
                                @if(!empty($semesters))
                                    @foreach($semesters as $sem)
                                        <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="sdr-f" data-sdr-filter>
                            <label for="attendance_semester">Attendance Semester</label>
                            <select id="attendance_semester" class="w-full tom-selects" multiple name="group[attendance_semester][]" data-placeholder="All semesters">
                                @if(!empty($terms))
                                    @foreach($terms as $term)
                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="sdr-f" data-sdr-filter>
                            <label for="course">Course</label>
                            <select id="course" class="w-full tom-selects" multiple name="group[course][]" data-placeholder="All courses">
                                @if(!empty($courses))
                                    @foreach($courses as $crs)
                                        <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="sdr-f" data-sdr-filter>
                            <label for="group">Master Group</label>
                            <select id="group" class="w-full tom-selects" multiple name="group[group][]" data-placeholder="All groups"></select>
                            <div class="sdr-hint" id="sdrGroupHint">Choose another filter first</div>
                        </div>
                        <div class="sdr-f" data-sdr-filter>
                            <label for="is_self_funded">Self Funding</label>
                            <select id="is_self_funded" class="w-full tom-selects" name="group[is_self_funded]">
                                <option value="">Any</option>
                                <option value="Yes">Yes</option>
                                <option value="No">No</option>
                            </select>
                        </div>
                        <div class="sdr-f" data-sdr-filter>
                            <label for="evening_weekend">Evening / Weekend</label>
                            <select id="evening_weekend" class="w-full tom-selects" name="group[evening_weekend]">
                                <option value="">Any</option>
                                <option value="1">Yes</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="sdr-f" data-sdr-filter>
                            <label for="student_type">Student Type</label>
                            <select id="student_type" class="w-full tom-selects" multiple name="group[student_type][]" data-placeholder="All types">
                                <option value="UK">UK</option>
                                <option value="BOTH">BOTH</option>
                                <option value="OVERSEAS">OVERSEAS</option>
                            </select>
                        </div>
                        <div class="sdr-f" data-sdr-filter>
                            <label for="group_student_status">Student Status</label>
                            <select id="group_student_status" class="w-full tom-selects" name="group[group_student_status][]" multiple data-placeholder="All statuses">
                                @if(!empty($allStatuses))
                                    @foreach($allStatuses as $sts)
                                        <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <input type="hidden" id="groupSearchStatus" value="0" name="group[stataus]">
                    <div class="sdr-filter-foot">
                        <div class="sdr-result" id="sdrResult" aria-live="polite">
                            <span class="sdr-dot" aria-hidden="true"></span><span id="sdrResultText">Run a search to see how many students match.</span>
                        </div>
                        <div class="sdr-acts">
                            <button type="button" class="sdr-btn sdr-btn--danger resetSearch">Reset</button>
                            <button id="studentGroupSearchSubmitBtn" type="submit" class="sdr-btn sdr-btn--primary">
                                <svg class="sdr-ico" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="7" cy="7" r="4.5"/><path d="m10.5 10.5 3 3"/></svg>
                                <svg class="sdr-spin" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M8 2a6 6 0 1 0 6 6"/></svg>
                                <span>Search</span>
                            </button>
                        </div>
                    </div>
                </form>

                {{-- 2. Choose columns --}}
                <section class="sdr-card sdr-picker" aria-labelledby="sdrPickHead">
                    <div class="sdr-card-head">
                        <h2 class="sdr-step" id="sdrPickHead"><i>2</i>Choose columns</h2>
                        <small>{{ $sdrFieldTotal }} fields in {{ count($sdrGroups) }} data sets</small>
                    </div>
                    <div class="sdr-tools">
                        <div class="sdr-find">
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="7" cy="7" r="4.5"/><path d="m10.5 10.5 3 3"/></svg>
                            <input id="sdrFind" type="search" placeholder="Find a field, e.g. email, address, SLC…" aria-label="Find a field">
                        </div>
                        <div class="sdr-right">
                            <button type="button" class="sdr-link" id="sdrSelectAll">Select all</button>
                            <span class="sdr-sep" aria-hidden="true"></span>
                            <button type="button" class="sdr-link" id="sdrExpandAll">Collapse all</button>
                        </div>
                    </div>
                    <form action="#" method="post" id="studentExcelForm" class="sdr-groups">
                        @foreach($sdrGroups as $g)
                            <div class="sdr-group" data-sdr-group="{{ $g['id'] }}" data-title="{{ $g['title'] }}">
                                <div class="sdr-g-head">
                                    <label class="sdr-g-all" title="Select all in {{ $g['title'] }}">
                                        <input type="checkbox" data-sdr-all><span class="sdr-sr">Select all in {{ $g['title'] }}</span>
                                    </label>
                                    <button type="button" class="sdr-g-toggle" aria-expanded="true" aria-controls="sdrGroupBody-{{ $g['id'] }}">
                                        <h3>{{ $g['title'] }}</h3>
                                        @if(!empty($g['sensitive']))<span class="sdr-tag" title="Special category data — export only when needed">Sensitive</span>@endif
                                        <span class="sdr-count">0/{{ count($g['fields']) }}</span>
                                        <svg class="sdr-chev" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m4 6 4 4 4-4"/></svg>
                                    </button>
                                </div>
                                <div class="sdr-g-body" id="sdrGroupBody-{{ $g['id'] }}">
                                    @foreach($g['fields'] as [$name, $label, $order])
                                        <label class="sdr-field" data-label="{{ strtolower($label) }}">
                                            <input type="checkbox" name="{{ $name }}" value="1" data-order="{{ $order }}"><span>{{ $label }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </form>
                    <div class="sdr-none" id="sdrNone" hidden>No fields match “<span id="sdrNoneQ"></span>”.</div>
                </section>
            </div>

            {{-- 3. Export --}}
            <aside class="sdr-card sdr-panel" aria-labelledby="sdrExportHead">
                <div class="sdr-card-head">
                    <h2 class="sdr-step" id="sdrExportHead"><i>3</i>Export</h2>
                    <button type="button" class="sdr-link" id="sdrClearAll" disabled>Clear all</button>
                </div>
                <div class="sdr-stats">
                    <div><b id="sdrStatStudents">–</b><span>Students found</span></div>
                    <div><b id="sdrStatCols">0</b><span>Columns selected</span></div>
                </div>
                <div class="sdr-sel" id="sdrSelection"></div>
                <div class="sdr-panel-foot" id="sdrPanelFoot">
                    <button type="button" id="studentDataReportExcelBtn" class="sdr-btn sdr-btn--primary" data-sdr-export disabled>
                        <svg class="sdr-ico" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 2v8m0 0 3-3m-3 3L5 7M3 13h10"/></svg>
                        <svg class="sdr-spin" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M8 2a6 6 0 1 0 6 6"/></svg>
                        <span>Export Excel</span>
                    </button>
                    <p class="sdr-why" id="sdrWhy"></p>
                </div>
            </aside>
        </div>
    </div>

    {{-- Fixed bar + toast stay outside .sdr (a size container would pin them to the report). --}}
    <div class="sdr-float sdr-mbar" id="sdrMobileBar" hidden>
        <span><b id="sdrMobileCols">0</b> columns · <b id="sdrMobileStudents">–</b> students</span>
        <button type="button" class="sdr-btn sdr-btn--primary" data-sdr-export disabled>
            <svg class="sdr-spin" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M8 2a6 6 0 1 0 6 6"/></svg>
            <span>Export Excel</span>
        </button>
    </div>
    <div class="sdr-float sdr-toast" id="sdrToast" role="status"></div>
@endsection

@section('script')
    @vite('resources/js/student-data-search-form.js')
@endsection
