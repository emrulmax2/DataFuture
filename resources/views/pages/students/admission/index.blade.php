@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Admission list on the admission portal shell (layout/admission-portal).

        Behaviour lives in resources/js/admission-list.js, which binds to the
        same ids the page has always carried: #tabulatorFilterForm-ADM,
        #refno-ADM, #firstname-ADM, #lastname-ADM, #dob-ADM, #email-ADM,
        #phone-ADM, #semesters-ADM, #courses-ADM, #statuses-ADM, #agents-ADM,
        #tabulator-html-filter-go-ADM, #tabulator-html-filter-reset-ADM,
        #tabulator-print-ADM, #tabulator-export-xlsx-ADM, #excelExportBtn,
        #admissionListTable, #addNewAccountConfirm, #student_id,
        #successModal and #errorModal.
    --}}
    @php
        // Hero figures come from AdmissionController::index() as $hero:
        // cells (intake × stage × course counts), the intake tabs, the
        // default intake and its stage counts. admission-list.js takes over
        // from there (intake switch, stage tiles, "by course" breakdown).
        $adpHero = $hero ?? ['cells' => collect(), 'intakes' => collect(), 'default' => null, 'submitted' => 0, 'all' => 0, 'counts' => collect()];
        $adpCounts = collect($adpHero['counts']);
        $adpIntakes = collect($adpHero['intakes']);
        $adpSubmitted = (int) $adpHero['submitted'];
        $adpDefaultIntake = $adpHero['default'];
        $adpDefaultName = $adpDefaultIntake !== null ? ($adpIntakes->firstWhere('id', $adpDefaultIntake)['name'] ?? '') : '';
        $adpQueue = [
            2 => ['New', 'New', 'Not yet looked at'],
            3 => ['In progress', 'In Progress', 'Tasks assigned'],
            4 => ['Awaiting decision', 'Awaiting Decision', 'All tasks complete'],
            5 => ['Accepted', 'Accepted', 'Offer letter sent'],
            6 => ['Offer placed', 'Offer Placed', 'Waiting on applicant'],
            7 => ['Offer accepted', 'Offer Accepted', 'Converted to student'],
        ];
        $adpCanAnalyse = isset(auth()->user()->priv()['applicant_analysis']) && auth()->user()->priv()['applicant_analysis'] == 1;
    @endphp

    <section class="adp-hero" aria-label="Admissions overview" data-adp-hero data-adp-hero-default="{{ $adpDefaultIntake ?? '' }}">
        <div class="adp-ah-top">
            <div class="adp-ah-title">
                <h1 class="adp-h1">Students <em>admission</em></h1>
                <p class="adp-hero__sub"><b class="adp-ah-n" data-adp-hero-submitted>{{ $adpSubmitted }}</b> submitted <span data-adp-hero-scope>@if($adpDefaultName !== '')for <b class="adp-ah-n">{{ $adpDefaultName }}</b>@else across all intakes @endif</span> · <span data-adp-hero-count="2">{{ (int) $adpCounts->get(2, 0) }}</span> untouched · <span data-adp-hero-count="4">{{ (int) $adpCounts->get(4, 0) }}</span> ready for a decision</p>
            </div>
            <div class="adp-ah-actions">
                <div class="adp-ah-intakes" role="group" aria-label="Intake term">
                    @foreach($adpIntakes as $adpIntake)
                        <button type="button" class="{{ $adpIntake['id'] === $adpDefaultIntake ? 'on' : '' }}" aria-pressed="{{ $adpIntake['id'] === $adpDefaultIntake ? 'true' : 'false' }}" data-adp-intake="{{ $adpIntake['id'] }}" data-adp-intake-name="{{ $adpIntake['name'] }}"><span>{{ $adpIntake['name'] }}</span><b>{{ (int) $adpIntake['n'] }}</b></button>
                    @endforeach
                    <button type="button" class="{{ $adpDefaultIntake === null ? 'on' : '' }}" aria-pressed="{{ $adpDefaultIntake === null ? 'true' : 'false' }}" data-adp-intake="" data-adp-intake-name=""><span>All intakes</span><b>{{ (int) $adpHero['all'] }}</b></button>
                </div>
                <button type="button" id="tabulator-export-xlsx-ADM" class="adp-btn adp-btn--light">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/></svg>
                    Export list
                    <span id="excelExportBtn" class="adp-spin" hidden aria-hidden="true"></span>
                </button>
                <button type="button" id="tabulator-print-ADM" class="adp-ah-icon" title="Print the list" aria-label="Print the list">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                </button>
                @if($adpCanAnalyse)
                    <a href="{{ route('reports.applicant.analysis') }}" class="adp-ah-icon" title="Application analysis" aria-label="Application analysis">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3v18h18"/><path d="m7 14 4-4 3 3 5-6"/></svg>
                    </a>
                @endif
            </div>
        </div>

        <div class="adp-ah-pipe" aria-hidden="true">
            @foreach(array_keys($adpQueue) as $adpI => $adpStatusId)
                @php $adpN = (int) $adpCounts->get($adpStatusId, 0); @endphp
                <i data-adp-pipe="{{ $adpStatusId }}" style="flex:{{ $adpN > 0 ? $adpN : '0.001' }};opacity:{{ .45 + $adpI * .11 }}"></i>
            @endforeach
        </div>

        <div class="adp-ah-tiles" role="group" aria-label="Pipeline stage">
            @foreach($adpQueue as $adpStatusId => [$adpLabel, $adpStageName, $adpHint])
                @php
                    $adpN = (int) $adpCounts->get($adpStatusId, 0);
                    $adpShare = $adpSubmitted > 0 ? (int) round($adpN / $adpSubmitted * 100) : 0;
                @endphp
                <button type="button" aria-pressed="false" class="adp-ah-tile{{ $adpN === 0 ? ' zero' : '' }}" data-adp-quick="{{ $adpStatusId }}" data-adp-stage-name="{{ $adpStageName }}" data-tip="{{ $adpHint }}">
                    <small>{{ $adpLabel }}</small>
                    <span class="adp-ah-num"><b data-adp-tile-n>{{ $adpN }}</b><span class="adp-ah-share" data-adp-tile-share>{{ $adpShare }}%</span></span>
                    <i class="adp-ah-track"></i><i class="adp-ah-fill" data-adp-tile-fill style="width:{{ $adpShare }}%"></i>
                </button>
            @endforeach
        </div>

        {{-- "{Stage} by course" breakdown; opens when a stage tile is selected. --}}
        <div class="adp-ah-brk" data-adp-hero-brk hidden>
            <div class="adp-ah-brk__h">
                <h4><span data-adp-brk-stage></span> by course<span data-adp-brk-intake></span></h4>
                <span class="adp-ah-brk__n" data-adp-brk-n></span>
                <button type="button" class="adp-ah-x" aria-label="Close breakdown" data-adp-brk-close>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="adp-ah-brk__empty" data-adp-brk-empty hidden>Nothing in this status for the selected intake.</div>
            <div class="adp-ah-rows" data-adp-brk-rows></div>
        </div>

        <script type="application/json" data-adp-hero-cells>@json($adpHero['cells'])</script>
    </section>

    <section class="adp-panel" style="min-width:0;">
        <div class="adp-panel__head">
            <h3 class="adp-h3">Search applications<span class="adp-cnt adp-cnt--filters" data-adp-filter-count hidden>0</span></h3>
            <div class="adp-panel__tools">
                <span class="adp-note">Only applications with a submission date are listed.</span>
                <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-filter-toggle="#tabulatorFilterForm-ADM" data-adp-filter-key="adm.list.filters" aria-expanded="true" aria-controls="tabulatorFilterForm-ADM">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <span data-adp-filter-label>Hide search</span>
                </button>
            </div>
        </div>

        <form id="tabulatorFilterForm-ADM" class="adp-searchbox" data-adp-filter-panel>
            <div class="adp-grid-4">
                <div class="adp-field">
                    <label for="refno-ADM">Ref. No.</label>
                    <input type="text" id="refno-ADM" name="refno-ADM" placeholder="Ref. No." value="" class="adp-ctl" autocomplete="off">
                </div>
                <div class="adp-field">
                    <label for="firstname-ADM">First Name(s)</label>
                    <input type="text" id="firstname-ADM" name="firstname-ADM" placeholder="First Name" value="" class="adp-ctl" autocomplete="off">
                </div>
                <div class="adp-field">
                    <label for="lastname-ADM">Last Name</label>
                    <input type="text" id="lastname-ADM" name="lastname-ADM" placeholder="Last Name" value="" class="adp-ctl" autocomplete="off">
                </div>
                <div class="adp-field">
                    <label for="dob-ADM">Date of Birth</label>
                    <input type="text" id="dob-ADM" name="dob-ADM" placeholder="DD-MM-YYYY" value="" class="adp-ctl adp-date" autocomplete="off">
                </div>
                <div class="adp-field">
                    <label for="email-ADM">Email</label>
                    <input type="email" id="email-ADM" name="email-ADM" placeholder="xyz@zyx.com" value="" class="adp-ctl" autocomplete="off">
                </div>
                <div class="adp-field">
                    <label for="phone-ADM">Phone</label>
                    <input type="text" id="phone-ADM" name="phone-ADM" placeholder="07XXXXXXXXX" value="" class="adp-ctl" autocomplete="off">
                </div>
                <div class="adp-field">
                    <label for="semesters-ADM">Semester</label>
                    <select id="semesters-ADM" name="semesters[]" class="adp-msel" multiple data-placeholder="All semesters">
                        @if(!empty($semesters))
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="adp-field">
                    <label for="courses-ADM">Courses</label>
                    {{-- Options arrive from course.creation.coursesbysemester once a semester is chosen. --}}
                    <select id="courses-ADM" name="courses[]" class="adp-msel" multiple data-placeholder="All courses"></select>
                </div>
                <div class="adp-field">
                    <label for="statuses-ADM">Status</label>
                    <select id="statuses-ADM" name="statuses[]" class="adp-msel" multiple data-placeholder="All submitted statuses">
                        @if(!empty($allStatuses))
                            @foreach($allStatuses as $sts)
                                <option value="{{ $sts->id }}">{{ $sts->name == 'Awating Decision' ? 'Awaiting Decision' : $sts->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="adp-field">
                    <label for="agents-ADM">Agent</label>
                    <select id="agents-ADM" name="agents[]" class="adp-msel" multiple data-placeholder="Any agent or direct">
                        @if(!empty($agents))
                            @foreach($agents as $agt)
                                <option value="{{ $agt->agent_user_id }}">{{ (isset($agt->organization) ? $agt->organization : 'Unknown Organization') }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
            </div>

            <div class="adp-searchbox__foot">
                <button id="tabulator-html-filter-reset-ADM" type="button" class="adp-btn adp-btn--ghost">Reset</button>
                <button id="tabulator-html-filter-go-ADM" type="button" class="adp-btn adp-btn--primary">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    Search
                </button>
            </div>
        </form>

        <div class="adp-dtable">
            <div id="admissionListTable" class="adp-table"></div>
        </div>
    </section>

    {{-- Create an applicant account for a converted student found by reference. --}}
    <div id="addNewAccountConfirm" class="adp-overlay" hidden>
        <div class="adp-dialog adp-dialog--warn" role="alertdialog" aria-modal="true" aria-labelledby="addNewAccountTitle">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 2h8.2L22 7.9v8.2L16.1 22H7.9L2 16.1V7.9z"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg></span>
            <h3 id="addNewAccountTitle">Create an application account?</h3>
            <p>An applicant password-change email goes to the student's personal email. They set their password from that email.</p>
            <input type="hidden" id="student_id" name="student_id" value="">
            <div class="adp-dialog__actions">
                <button type="button" class="adp-btn adp-btn--ghost disAgreeWith" data-adp-dialog-close>Cancel</button>
                <button type="button" class="adp-btn adp-btn--primary agreeWith" data-id="0" data-action="none">
                    <span class="adp-spin adp-spin--light" hidden aria-hidden="true"></span>
                    Yes, I confirm
                </button>
            </div>
        </div>
    </div>

    <div id="successModal" class="adp-overlay" hidden>
        <div class="adp-dialog adp-dialog--success" role="dialog" aria-modal="true" aria-live="polite">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg></span>
            <h3 class="successModalTitle"></h3>
            <p class="successModalDesc"></p>
            <div class="adp-dialog__actions adp-dialog__actions--single">
                <button type="button" class="adp-btn adp-btn--success successCloser" data-adp-dialog-close>Done</button>
            </div>
        </div>
    </div>

    <div id="errorModal" class="adp-overlay" hidden>
        <div class="adp-dialog adp-dialog--danger" role="alertdialog" aria-modal="true" aria-live="assertive">
            <span class="adp-dialog__ico"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.9 2h8.2L22 7.9v8.2L16.1 22H7.9L2 16.1V7.9z"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg></span>
            <h3 class="errorModalTitle"></h3>
            <p class="errorModalDesc"></p>
            <div class="adp-dialog__actions adp-dialog__actions--single">
                <button type="button" class="adp-btn adp-btn--primary" data-adp-dialog-close>Done</button>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/admission-list.js')
@endsection
