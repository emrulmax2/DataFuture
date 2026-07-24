@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
@endsection

@section('content')
    @php
        $studentOptionGroups = [
            ['title' => 'Care Leaver', 'button' => 'Add New Care Leaver', 'modal' => 'addCareLeaverModal', 'table' => 'careLeaverListTable', 'partial' => 'pages.settings.studentoption.careLeaver.index'],
            ['title' => 'Countries', 'button' => 'Add New Country', 'modal' => 'addCountryModal', 'table' => 'countryListTable', 'partial' => 'pages.settings.studentoption.country.index'],
            ['title' => 'Country of permanent address', 'button' => 'Add New', 'modal' => 'addPermaddcountryModal', 'table' => 'PermaddcountryListTable', 'partial' => 'pages.settings.studentoption.permaddcountry.index'],
            ['title' => 'Disabilities', 'button' => 'Add New Disability', 'modal' => 'addDisabilityModal', 'table' => 'disabilityListTable', 'partial' => 'pages.settings.studentoption.disability.index'],
            ['title' => 'Disable Allowance', 'button' => 'Add New', 'modal' => 'addDisableAllowanceModal', 'table' => 'DisableAllowanceListTable', 'partial' => 'pages.settings.studentoption.disableAllowance.index'],
            ['title' => 'Equivalent or Lower qualification', 'button' => 'Add New', 'modal' => 'addEqvOrLwQfModal', 'table' => 'EqvOrLwQfListTable', 'partial' => 'pages.settings.studentoption.eqvorlwqf.index'],
            ['title' => 'Ethnicities', 'button' => 'Add New Ethnicity', 'modal' => 'addEthnicityModal', 'table' => 'ethnicityListTable', 'partial' => 'pages.settings.studentoption.ethnicity.index'],
            ['title' => 'Exchange Programme', 'button' => 'Add New', 'modal' => 'addExchangeProgrammeModal', 'table' => 'ExchangeProgrammeListTable', 'partial' => 'pages.settings.studentoption.exchangeProgramme.index'],
            ['title' => 'Fee Eligibilities', 'button' => 'Add New', 'modal' => 'addFeeEligibilityModal', 'table' => 'feeEligibilitiesListTable', 'partial' => 'pages.settings.studentoption.feeeligibility.index'],
            ['title' => 'Funding Completion', 'button' => 'Add New', 'modal' => 'addFundingCompletionModal', 'table' => 'FundingCompletionListTable', 'partial' => 'pages.settings.studentoption.fundingcompletion.index'],
            ['title' => 'Funding Length', 'button' => 'Add New', 'modal' => 'addFundingLengthModal', 'table' => 'FundingLengthListTable', 'partial' => 'pages.settings.studentoption.fundinglength.index'],
            ['title' => 'Grades', 'button' => 'Add New Relation', 'modal' => 'addResGradeModal', 'table' => 'resultGradeListTable', 'partial' => 'pages.settings.studentoption.grades.index'],
            ['title' => 'Genders', 'button' => 'Add New Gender', 'modal' => 'addHgenModal', 'table' => 'hgenListTable', 'partial' => 'pages.settings.studentoption.hesagender.index'],
            ['title' => 'Heapes Population', 'button' => 'Add New', 'modal' => 'addHeapesPopulationModal', 'table' => 'HeapesPopulationListTable', 'partial' => 'pages.settings.studentoption.heapesPopulation.index'],
            ['title' => 'Hesa Qualification Award', 'button' => 'Add New', 'modal' => 'addHesaQualificationAwardModal', 'table' => 'HesaQualificationAwardListTable', 'partial' => 'pages.settings.studentoption.hesaQualificationAward.index'],
            ['title' => 'Hesa Qualification Subject', 'button' => 'Add New', 'modal' => 'addHesaQualSubModal', 'table' => 'HesaQualSubListTable', 'partial' => 'pages.settings.studentoption.hesaqualsub.index'],
            ['title' => 'Highest qualification on entry', 'button' => 'Add New', 'modal' => 'addHighestqoeModal', 'table' => 'HighestqoeListTable', 'partial' => 'pages.settings.studentoption.highestqoe.index'],
            ['title' => 'Kins Relation', 'button' => 'Add New Relation', 'modal' => 'addKinsModal', 'table' => 'kinsListTable', 'partial' => 'pages.settings.studentoption.kins-relation.index'],
            ['title' => 'Location Of Study', 'button' => 'Add New', 'modal' => 'addLocationOfStudyModal', 'table' => 'LocationOfStudyListTable', 'partial' => 'pages.settings.studentoption.locationOfStudy.index'],
            ['title' => 'Major Source Of Tuition Fee', 'button' => 'Add New', 'modal' => 'addMajorSourceOfTuitionFeeModal', 'table' => 'MajorSourceOfTuitionFeeListTable', 'partial' => 'pages.settings.studentoption.majorSourceOfTuitionFee.index'],
            ['title' => 'Module Outcome', 'button' => 'Add New', 'modal' => 'addModuleOutcomeModal', 'table' => 'ModuleOutcomeTable', 'partial' => 'pages.settings.studentoption.moduleOutcome.index'],
            ['title' => 'Module Result', 'button' => 'Add New', 'modal' => 'addModuleResultModal', 'table' => 'ModuleResultTable', 'partial' => 'pages.settings.studentoption.moduleResult.index'],
            ['title' => 'Non Regulated Fee Flag', 'button' => 'Add New', 'modal' => 'addNonRegFFlgModal', 'table' => 'NonRegFFlgListTable', 'partial' => 'pages.settings.studentoption.nonregfflg.index'],
            ['title' => 'Other Academic Qualifications', 'button' => 'Add New', 'modal' => 'addOtherAcademicQualificationModal', 'table' => 'otherAcademicQualificationsListTable', 'partial' => 'pages.settings.studentoption.otherAcademicQualifications.index'],
            ['title' => 'Previous provider', 'button' => 'Add New', 'modal' => 'addPreviousproviderModal', 'table' => 'PreviousproviderListTable', 'partial' => 'pages.settings.studentoption.previousprovider.index'],
            ['title' => 'Qualification Award Results', 'button' => 'Add New', 'modal' => 'addQaualAwardResultModal', 'table' => 'QaualAwardResultListTable', 'partial' => 'pages.settings.studentoption.qualawardresult.index'],
            ['title' => 'Qualification Grades', 'button' => 'Add New', 'modal' => 'addQaualGradeModal', 'table' => 'QaualGradeListTable', 'partial' => 'pages.settings.studentoption.qaualgrade.index'],
            ['title' => 'Qualification type identifier', 'button' => 'Add New', 'modal' => 'addQaualtypeidModal', 'table' => 'QaualtypeidListTable', 'partial' => 'pages.settings.studentoption.qaualtypeid.index'],
            ['title' => 'Reason for Ending course session', 'button' => 'Add New', 'modal' => 'addRsForEndCrsSModal', 'table' => 'RsForEndCrsSListTable', 'partial' => 'pages.settings.studentoption.rsfendcrss.index'],
            ['title' => 'Reason for Engagement ending', 'button' => 'Add New', 'modal' => 'addRsnengendModal', 'table' => 'RsnengendListTable', 'partial' => 'pages.settings.studentoption.rsnengend.index'],
            ['title' => 'Session Status', 'button' => 'Add Session Status', 'modal' => 'addSessionStatusModal', 'table' => 'sessionStatusListTable', 'partial' => 'pages.settings.studentoption.sessionstatus.index'],
            ['title' => 'Religions', 'button' => 'Add New Religion', 'modal' => 'addRelgnModal', 'table' => 'relgnListTable', 'partial' => 'pages.settings.studentoption.religion.index'],
            ['title' => 'Sexual Orientation', 'button' => 'Add New Orientation', 'modal' => 'addSexoModal', 'table' => 'sexoListTable', 'partial' => 'pages.settings.studentoption.sexual-orientation.index'],
            ['title' => 'Sex Identifier', 'button' => 'Add New Student Identifier', 'modal' => 'addStudentidentifierModal', 'table' => 'studentidentifierListTable', 'partial' => 'pages.settings.studentoption.sexidentifier.index'],
            ['title' => 'Student Support Eligibility', 'button' => 'Add New', 'modal' => 'addStudentSupportEligibilityModal', 'table' => 'StudentSupportEligibilityListTable', 'partial' => 'pages.settings.studentoption.studentSupportEligibility.index'],
            ['title' => 'Study Modes', 'button' => 'Add New', 'modal' => 'addStudyModeModal', 'table' => 'studyModeListTable', 'partial' => 'pages.settings.studentoption.studymode.index'],
            ['title' => 'Suspension Of Active Study', 'button' => 'Add New', 'modal' => 'addSuspensionOfActiveStudyModal', 'table' => 'SuspensionOfActiveStudyListTable', 'partial' => 'pages.settings.studentoption.suspensionOfActiveStudy.index'],
            ['title' => 'Term Time Accommodation Type', 'button' => 'Add New Term Time Accommodation Type', 'modal' => 'addTTACCOMModal', 'table' => 'termtimeaccommodationtypeListTable', 'partial' => 'pages.settings.studentoption.termtimeaccommodationtype.index'],
            ['title' => 'Titles', 'button' => 'Add New Title', 'modal' => 'addTitleModal', 'table' => 'titleListTable', 'partial' => 'pages.settings.studentoption.title.index'],
            ['title' => 'Work Placement Companies', 'button' => 'Add Company', 'modal' => 'addWPCompanyModal', 'table' => 'wpCompanyListTable', 'partial' => 'pages.settings.studentoption.workplacement-company.index'],
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page ss-student-options-page ss-legacy-modal-scope">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <span>Student Settings</span>
            <i data-lucide="chevron-right"></i>
            <span>Student Option Values</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="sliders-horizontal"></i>
                    </span>
                    <div>
                        <h1>{{ $subtitle }}</h1>
                        <p>Manage the dropdown values used throughout student records, HESA returns and reporting.</p>
                    </div>
                </div>
                <a href="{{ route('dashboard') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Dashboard
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php($settingsSidebarIcon = 'settings-2')
                    @php($settingsSidebarSubtitle = 'Global configuration')
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    <div class="ss-student-options-card">
                        <div class="ss-student-options-card__header">
                            <div>
                                <h2>Student Option Groups</h2>
                                <span>{{ count($studentOptionGroups) }} option groups</span>
                            </div>
                            <span class="ss-student-options-card__badge">
                                <i data-lucide="database"></i>
                                Values
                            </span>
                        </div>

                        <div class="ss-option-accordion-list">
                            @foreach($studentOptionGroups as $option)
                                <div class="optionBox ss-option-accordion">
                                    <div class="optionBoxHeader ss-option-accordion__header">
                                        <h2 class="optionBoxTitle ss-option-accordion__title" role="button" tabindex="0" aria-expanded="false" aria-controls="student-option-body-{{ $loop->index }}">
                                            <span class="ss-option-accordion__icon">
                                                <i data-lucide="list-filter"></i>
                                            </span>
                                            <span>{{ $option['title'] }}</span>
                                        </h2>
                                        <div class="ss-option-accordion__actions">
                                            <button data-tw-toggle="modal" data-tw-target="#{{ $option['modal'] }}" type="button" class="add_btn ss-option-add ss-btn ss-btn--primary ss-btn--compact">
                                                <i data-lucide="plus"></i>
                                                {{ $option['button'] }}
                                            </button>
                                            <button type="button" class="ss-option-accordion__chevron" aria-label="Toggle {{ $option['title'] }}">
                                                <i data-lucide="chevron-down" class="arrowNavigation"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="student-option-body-{{ $loop->index }}" class="optionBoxBody ss-option-accordion__body" data-tableid="{{ $option['table'] }}">
                                        @include($option['partial'])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content ss-success-modal">
                    <div class="modal-body p-0">
                        <div class="ss-success-modal__body">
                            <i data-lucide="check-circle" class="ss-success-modal__icon"></i>
                            <div class="successModalTitle">Success</div>
                            <p class="successModalDesc"></p>
                        </div>
                        <div class="ss-success-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--primary">Ok</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-confirm-modal__dialog">
                <div class="modal-content ss-confirm-modal">
                    <div class="ss-confirm-modal__hero">
                        <span><i data-lucide="alert-triangle"></i></span>
                        <h2 class="confModTitle">Are you sure?</h2>
                    </div>
                    <div class="ss-confirm-modal__body">
                        <p class="confModDesc"></p>
                    </div>
                    <div class="ss-confirm-modal__footer">
                        <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--light">
                            <i data-lucide="x"></i>
                            No, Cancel
                        </button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith ss-btn ss-btn--danger">
                            <i data-lucide="check"></i>
                            Yes, I agree
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/student-option.js')
    @vite('resources/js/title.js')
    @vite('resources/js/ethnicity.js')
    @vite('resources/js/kins-relation.js')
    @vite('resources/js/sexual-rientation.js')
    @vite('resources/js/religion.js')
    @vite('resources/js/hesagender.js')
    @vite('resources/js/country.js')
    @vite('resources/js/disabilities.js')
    @vite('resources/js/feeeligibilities.js')
    @vite('resources/js/funding-completion.js')
    @vite('resources/js/funding-length.js')
    @vite('resources/js/module-outcome.js')
    @vite('resources/js/module-result.js')
    @vite('resources/js/apelcredit.js')
    @vite('resources/js/highest-qualification-on-entry.js')
    @vite('resources/js/hesa-qualification-subject.js')
    @vite('resources/js/country-fo-permanent-address.js')
    @vite('resources/js/previous-provider.js')
    @vite('resources/js/qualification-type-identifier.js')
    @vite('resources/js/reason-for-ending-course-session.js')
    @vite('resources/js/equivalent-or-lower-qualification.js')
    @vite('resources/js/non-regulated-fee-flag.js')
    @vite('resources/js/reason-for-engagement-ending.js')
    @vite('resources/js/termtimeaccommodationtype.js')
    @vite('resources/js/sexidentifier.js')
    @vite('resources/js/wp-company.js')
    @vite('resources/js/disable-allowance.js')
    @vite('resources/js/exchange-programme.js')
    @vite('resources/js/heapes-population.js')
    @vite('resources/js/hesa-qualification-award.js')
    @vite('resources/js/location-of-study.js')
    @vite('resources/js/major-source-of-tuition-fee.js')
    @vite('resources/js/student-support-eligibility.js')
    @vite('resources/js/suspension-of-active-study.js')
    @vite('resources/js/qualification-grades.js')
    @vite('resources/js/other-academic-qualifications.js')
    @vite('resources/js/session-status.js')
    @vite('resources/js/study-modes.js')
    @vite('resources/js/qual-award-results.js')
    @vite('resources/js/grades.js')
    @vite('resources/js/care-leaver.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
