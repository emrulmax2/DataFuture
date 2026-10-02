@extends('../layout/site-settings')

@section('body_class', 'site-settings-isolated')

@section('subhead')
    <title>{{ $title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Spectral:wght@600;700&display=swap" rel="stylesheet">
@endsection

@section('styles')
    @vite('resources/css/site-settings-redesign.css')
    @vite('resources/css/policy-assessment-admin.css')
@endsection

@section('content')
    @php
        $paCategoryName = (isset($policy->category->name) ? $policy->category->name : 'No category');
        $paPdfUrl = (is_string($policy->pdf_url) && preg_match('#^https?://#i', $policy->pdf_url) ? $policy->pdf_url : '');
        $paPageUrl = (is_string($policy->page_url) && preg_match('#^https?://#i', $policy->page_url) ? $policy->page_url : '');
        $paStatusFilter = in_array(request()->query('status'), ['all', 'active', 'drafts', 'archived'], true) ? request()->query('status') : 'all';
        $paLevelFilter = (is_string(request()->query('level')) && \App\Support\PolicyLevel::isValid(request()->query('level')) ? request()->query('level') : '');
        $paPerTest = (int) $policy->questions_per_attempt;
        $paTimeText = ($counts['time_limit_minutes'] !== null ? $counts['time_limit_label'].' time limit' : $counts['time_limit_label']);
        $paExamTotal = count($counts['exams']);
        $paExamsReady = (int) $counts['exams_ready'];
        $paLevelIcons = ['ready' => 'check-circle', 'short' => 'alert-triangle', 'blocked' => 'x-circle'];
        $paBankTotal = (int) $counts['total'];
        $paBankTarget = (int) $counts['bank_target'];
        $paLevelDrafts = ($paLevelFilter !== '' ? (int) $counts['levels'][$paLevelFilter]['drafts'] : (int) $counts['drafts']);
        /* One line under each level in the question modal. */
        $paLevelHints = [
            \App\Support\PolicyLevel::BEGINNER => 'Key facts everyone should know',
            \App\Support\PolicyLevel::INTERMEDIATE => 'Applying the policy day to day',
            \App\Support\PolicyLevel::EXPERT => 'Detail, exceptions and judgement',
        ];
    @endphp

    <div id="siteSettingsPage" class="ss-page pa-bank-page pa-questions-page">
        @include('pages.settings.partials.isolated-header')

        <nav class="ss-breadcrumb" aria-label="Breadcrumb">
            <a href="{{ route('dashboard') }}">
                <i data-lucide="home"></i>
                Dashboard
            </a>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('hr.portal') }}">HR Portal</a>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('policy.assessment') }}">Policy Assessments</a>
            <i data-lucide="chevron-right"></i>
            <a href="{{ route('policy.assessment.policy') }}">Question Bank</a>
            <i data-lucide="chevron-right"></i>
            <span class="pa-breadcrumb-current">{{ $policy->title }}</span>
        </nav>

        <main class="ss-main">
            <section class="ss-title-card">
                <div class="ss-title-card__content">
                    <button type="button" class="ss-icon-btn ss-sidebar-toggle" data-ss-sidebar-toggle aria-label="Open settings menu">
                        <i data-lucide="panel-left"></i>
                    </button>
                    <span class="ss-title-card__icon">
                        <i data-lucide="clipboard-check"></i>
                    </span>
                    <div>
                        <h1>Questions</h1>
                        <p>Read each question, check the marked answer against the policy, then switch it on.</p>
                    </div>
                </div>
                <a href="{{ route('policy.assessment.policy') }}" class="ss-back-btn">
                    <i data-lucide="arrow-left"></i>
                    Back to Question Bank
                </a>
            </section>

            <div class="ss-workspace">
                <button type="button" class="ss-sidebar-backdrop" data-ss-sidebar-close aria-label="Close settings menu"></button>
                <aside class="ss-sidebar">
                    @php
                        $settingsSidebarIcon = 'clipboard-check';
                        $settingsSidebarSubtitle = 'Policy Assessments';
                    @endphp
                    @include('pages.settings.sidebar')
                </aside>

                <section class="ss-content">
                    @include('pages.hr.policy-assessment.partials.nav', ['active' => 'policies'])

                    <section class="pa-policy-head" id="policyHead">
                        <div class="pa-policy-head__top">
                            <div class="pa-policy-head__main">
                                <span class="pa-policy-head__eyebrow">
                                    <i data-lucide="folder-tree"></i>
                                    {{ $paCategoryName }}@if(isset($policy->category) && $policy->category->trashed()) (archived)@endif
                                    @if(!empty($policy->version))
                                        <span class="pa-meta__dot">·</span> {{ $policy->version }}
                                    @endif
                                </span>
                                <h2>{{ $policy->title }}</h2>
                                <div class="pa-policy-head__facts">
                                    <span><i data-lucide="award"></i>Pass mark {{ (int) $policy->pass_mark }}%</span>
                                    <span><i data-lucide="list-checks"></i><span data-fact="per-test">{{ $paPerTest }} {{ $paPerTest == 1 ? 'question' : 'questions' }} per test</span></span>
                                    <span><i data-lucide="rotate-cw"></i>{{ $policy->max_attempts !== null ? $policy->max_attempts.' '.((int) $policy->max_attempts == 1 ? 'attempt' : 'attempts') : 'Unlimited attempts' }}</span>
                                    <span title="{{ $counts['time_limit_minutes'] !== null ? 'The clock starts when the member of staff starts the test; it is submitted automatically when the time runs out.' : 'Staff can take as long as they need.' }}"><i data-lucide="timer"></i><span data-fact="time-limit">{{ $paTimeText }}</span></span>
                                    @if($paPdfUrl !== '')
                                        <a href="{{ $paPdfUrl }}" target="_blank" rel="noopener noreferrer" class="pa-inline-link"><i data-lucide="file-text"></i>Open PDF</a>
                                    @else
                                        <span class="pa-muted-warn"><i data-lucide="file-question"></i>No PDF link</span>
                                    @endif
                                    @if($paPageUrl !== '')
                                        <a href="{{ $paPageUrl }}" target="_blank" rel="noopener noreferrer" class="pa-inline-link"><i data-lucide="external-link"></i>Web page</a>
                                    @endif
                                    @if(!$policy->is_active)
                                        <span class="pa-pill pa-pill--danger">Policy inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="pa-policy-head__stats" aria-live="polite">
                                <div class="pa-stat pa-stat--active">
                                    <strong data-count="active">{{ $counts['active'] }}</strong>
                                    <span>Active</span>
                                </div>
                                <div class="pa-stat pa-stat--draft">
                                    <strong data-count="drafts">{{ $counts['drafts'] }}</strong>
                                    <span>Drafts to review</span>
                                </div>
                                <div class="pa-stat pa-stat--muted">
                                    <strong data-count="archived">{{ $counts['archived'] }}</strong>
                                    <span>Archived</span>
                                </div>
                            </div>
                        </div>
                        {{-- Exam readiness: an exam draws a set share of every level, so it is ready only
                             when each level has enough active questions for its share. --}}
                        <div class="pa-exams">
                            <div class="pa-levels__head">
                                <h3>Exam readiness</h3>
                                <span>An exam can start once every level has enough active questions for its share.</span>
                                <strong id="examsReadyNote" class="pa-exams__tally{{ $paExamsReady >= $paExamTotal ? ' is-ready' : '' }}" aria-live="polite">{{ $paExamsReady }} of {{ $paExamTotal }} exams ready</strong>
                            </div>
                            <div id="examReadiness" class="pa-exams__grid">
                                @include('pages.hr.policy-assessment.partials.bank-exam-readiness', ['exams' => $counts['exams']])
                            </div>
                        </div>
                        {{-- Each level's own counts; a card also filters the list to its level.
                             Same markup as levelCard() in policy-assessment-questions.js. --}}
                        <div class="pa-levels">
                            <div class="pa-levels__head">
                                <h3>Questions by level</h3>
                                <span>Active questions at each level, against the most any exam draws from it. Pick a level to filter the list.</span>
                            </div>
                            <div id="levelReadiness" class="pa-levels__grid" aria-live="polite">
                                @foreach($counts['levels'] as $paLevelKey => $paLevel)
                                    @php
                                        $paLevelSelected = ($paLevelFilter === $paLevelKey);
                                        $paLevelBar = ($paLevel['total'] > 0 ? (int) round($paLevel['active'] / $paLevel['total'] * 100) : 0);
                                    @endphp
                                    <button type="button"
                                            class="pa-level-ready pa-level-ready--{{ $paLevelKey }} is-{{ $paLevel['state'] }}{{ $paLevelSelected ? ' is-selected' : '' }}"
                                            data-level="{{ $paLevelKey }}"
                                            aria-pressed="{{ $paLevelSelected ? 'true' : 'false' }}"
                                            aria-label="{{ $paLevel['label'] }} questions — {{ $paLevel['summary'] }}. {{ $paLevel['note'] }}"
                                            title="{{ $paLevelSelected ? 'Show every level' : 'Show only '.$paLevel['label'].' questions' }}">
                                        <span class="pa-level-ready__top">
                                            @include('pages.hr.policy-assessment.partials.level-pill', ['level' => $paLevelKey, 'text' => null])
                                            <span class="pa-level-state pa-readiness--{{ $paLevel['state'] }}"><i data-lucide="{{ $paLevelIcons[$paLevel['state']] }}"></i>{{ $paLevel['state_label'] }}</span>
                                        </span>
                                        <span class="pa-level-ready__count"><strong>{{ $paLevel['active'] }}</strong> active of {{ $paLevel['total'] }}</span>
                                        <span class="pa-level-ready__bar" aria-hidden="true"><span style="width: {{ $paLevelBar }}%"></span></span>
                                        <span class="pa-level-ready__note">{{ $paLevel['note'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @include('pages.hr.policy-assessment.partials.bank-exam-pattern', ['pattern' => $counts['pattern']])
                        <div class="pa-policy-head__bottom">
                            <p id="bankSizeNote" class="pa-bank-size{{ $paBankTotal < $paBankTarget ? ' is-below' : '' }}">
                                <i data-lucide="{{ $paBankTotal < $paBankTarget ? 'alert-triangle' : 'library' }}"></i>
                                <span>
                                    @if($paBankTotal < $paBankTarget)
                                        {{ $paBankTotal }} {{ $paBankTotal == 1 ? 'question' : 'questions' }} in the bank — below the {{ $paBankTarget }}-question target.
                                    @else
                                        {{ $paBankTotal }} questions in the bank.
                                    @endif
                                </span>
                            </p>
                            <div class="pa-policy-head__actions">
                                {{-- The answer key as a PDF. The script adds the Level / Show filters, so it exports what the list is showing. --}}
                                <a id="downloadPdfBtn" href="{{ route('policy.assessment.question.pdf', $policy->id) }}" data-url="{{ route('policy.assessment.question.pdf', $policy->id) }}" class="ss-btn ss-btn--light ss-btn--compact" title="Download these questions with their answers and reasons as a PDF">
                                    <i data-lucide="file-down"></i>
                                    Download PDF
                                </a>
                                <button id="addQuestionBtn" type="button" class="ss-btn ss-btn--primary ss-btn--compact">
                                    <i data-lucide="plus"></i>
                                    Add question
                                </button>
                                <button id="activateAllBtn" type="button" class="ss-btn ss-btn--success ss-btn--compact" @if($paLevelDrafts <= 0) disabled title="{{ $paLevelFilter !== '' ? 'No '.\App\Support\PolicyLevel::label($paLevelFilter).' drafts to activate' : 'No drafts to activate' }}" @endif>
                                    <i data-lucide="check-check"></i>
                                    <span data-activate-label>{{ $paLevelFilter !== '' ? 'Activate '.\App\Support\PolicyLevel::label($paLevelFilter).' drafts' : 'Activate all drafts' }}</span>
                                </button>
                            </div>
                        </div>
                    </section>

                    <div class="ss-table-card pa-bank-card pa-questions-card">
                        <div class="ss-table-tools">
                            <form id="tabulatorFilterForm" class="ss-table-filter">
                                <div class="ss-filter-field">
                                    <span>Query</span>
                                    <label class="ss-filter-input" for="query">
                                        <i data-lucide="search"></i>
                                        <input id="query" name="query" type="text" placeholder="Search questions..." title="Searches question text, options and explanations">
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Level</span>
                                    <label class="ss-filter-select" for="level">
                                        <select id="level" name="level">
                                            <option value="" @selected($paLevelFilter === '')>All levels</option>
                                            @foreach(\App\Support\PolicyLevel::labels() as $paLevelKey => $paLevelLabel)
                                                <option value="{{ $paLevelKey }}" @selected($paLevelFilter === $paLevelKey)>{{ $paLevelLabel }}</option>
                                            @endforeach
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <div class="ss-filter-field">
                                    <span>Show</span>
                                    <label class="ss-filter-select" for="status">
                                        <select id="status" name="status">
                                            <option value="all" @selected($paStatusFilter === 'all')>All</option>
                                            <option value="active" @selected($paStatusFilter === 'active')>Active</option>
                                            <option value="drafts" @selected($paStatusFilter === 'drafts')>Drafts</option>
                                            <option value="archived" @selected($paStatusFilter === 'archived')>Archived</option>
                                        </select>
                                        <i data-lucide="chevron-down"></i>
                                    </label>
                                </div>
                                <button id="tabulator-html-filter-go" type="button" class="ss-btn ss-btn--primary ss-btn--tool">Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="ss-btn ss-btn--light ss-btn--tool">Reset</button>
                            </form>

                            <div class="ss-table-actions">
                                <button id="expandWhyBtn" type="button" class="ss-btn ss-btn--light ss-btn--tool" aria-pressed="false">
                                    <i data-lucide="lightbulb"></i>
                                    <span>Show all reasons</span>
                                </button>
                                <div class="dropdown ss-export-dropdown">
                                    <button type="button" class="dropdown-toggle ss-btn ss-btn--light ss-btn--tool" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="download"></i>
                                        Export
                                        <i data-lucide="chevron-down"></i>
                                    </button>
                                    <div class="dropdown-menu ss-export-menu">
                                        <ul class="dropdown-content">
                                            <li>
                                                <a id="tabulator-export-csv" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text"></i>
                                                    Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-spreadsheet"></i>
                                                    Export XLSX
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ss-tabulator-wrap">
                            <div id="policyQuestionTable"
                                 data-policy="{{ $policy->id }}"
                                 data-per-test="{{ $paPerTest }}"
                                 data-title="{{ $policy->title }}"
                                 data-counts="{{ json_encode($counts) }}"
                                 class="ss-tabulator pa-question-table table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </section>
            </div>
        </main>

        <div id="questionModal" class="modal ss-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog ss-settings-modal__dialog ss-settings-modal__dialog--wide pa-modal-dialog--question">
                <form method="POST" action="#" id="questionForm" autocomplete="off" novalidate>
                    <div class="modal-content ss-settings-modal ss-compact-settings-modal">
                        <div class="ss-settings-modal__header">
                            <div>
                                <span></span>
                                <h2 id="questionModalTitle">Add question</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="ss-modal-close" aria-label="Close modal">
                                <i data-lucide="x"></i>
                            </button>
                        </div>
                        <div class="modal-body ss-settings-modal__body">
                            <div class="ss-modal-field">
                                <label for="question_text">Question <span>*</span></label>
                                <textarea id="question_text" name="question" rows="3" maxlength="1000" class="ss-modal-input ss-modal-textarea question" placeholder="e.g. Who should you tell first if you have a safeguarding concern?"></textarea>
                                <div class="acc__input-error error-question"></div>
                            </div>

                            {{-- Level: how hard the question is. Every exam draws a set share of each level. --}}
                            <div class="ss-modal-field pa-level-field">
                                <div class="pa-options-field__head">
                                    <label id="question_level_label">Level <span>*</span></label>
                                    <small>How hard the question is. Every exam draws a set share of each level.</small>
                                </div>
                                <div id="questionLevelChoice" class="pa-level-choice" role="radiogroup" aria-labelledby="question_level_label" aria-required="true">
                                    @foreach(\App\Support\PolicyLevel::labels() as $paLevelKey => $paLevelLabel)
                                        <label class="pa-level-choice__item pa-level-choice__item--{{ $paLevelKey }}" for="question_level_{{ $paLevelKey }}">
                                            <input id="question_level_{{ $paLevelKey }}" type="radio" name="level" value="{{ $paLevelKey }}" class="pa-level-choice__input" autocomplete="off">
                                            <span class="pa-level-choice__card">
                                                {{-- Decorative here: the radio is already named by the text beside it. --}}
                                                <span class="pa-level-choice__medal" aria-hidden="true">@include('pages.hr.policy-assessment.partials.level-badge', ['level' => $paLevelKey, 'size' => 'sm', 'title' => null, 'date' => null, 'revoked' => false])</span>
                                                <span class="pa-level-choice__text">
                                                    <strong>{{ $paLevelLabel }}</strong>
                                                    <small>{{ $paLevelHints[$paLevelKey] }}</small>
                                                </span>
                                                <span class="pa-level-choice__tick" aria-hidden="true"><i data-lucide="check"></i></span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <div class="acc__input-error error-level"></div>
                            </div>

                            <div class="ss-modal-field pa-options-field">
                                <div class="pa-options-field__head">
                                    <label>Options <span>*</span></label>
                                    <small>Click a letter to mark the correct answer. 2 to 6 options.</small>
                                </div>
                                <div id="optionList" class="pa-option-list" role="radiogroup" aria-label="Options; choose the correct answer"></div>
                                <div class="acc__input-error error-options"></div>
                                <div class="acc__input-error error-correct"></div>
                                <button id="addOptionBtn" type="button" class="pa-add-option">
                                    <i data-lucide="plus"></i>
                                    Add option
                                </button>
                            </div>

                            <div class="ss-modal-grid">
                                <div class="ss-modal-field">
                                    <label for="question_explanation">Why this answer <small class="pa-label-note">HR only</small></label>
                                    <textarea id="question_explanation" name="explanation" rows="3" maxlength="2000" class="ss-modal-input ss-modal-textarea explanation" placeholder="Optional. Why the marked option is right."></textarea>
                                    <div class="acc__input-error error-explanation"></div>
                                </div>
                                <div class="ss-modal-field">
                                    <label for="question_excerpt">Source excerpt <small class="pa-label-note">HR only</small></label>
                                    <textarea id="question_excerpt" name="source_excerpt" rows="3" maxlength="2000" class="ss-modal-input ss-modal-textarea source_excerpt" placeholder="Optional. The line in the policy that answers it."></textarea>
                                    <div class="acc__input-error error-source_excerpt"></div>
                                </div>
                            </div>

                            <div class="ss-modal-field">
                                <label class="ss-status-toggle pa-active-toggle" for="question_active">
                                    <input id="question_active" name="is_active" value="1" type="checkbox" autocomplete="off">
                                    <span class="ss-status-toggle__control">
                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--on"><i data-lucide="check"></i></span>
                                        <span class="ss-status-toggle__icon ss-status-toggle__icon--off"><i data-lucide="x"></i></span>
                                    </span>
                                    <span class="ss-status-toggle__copy">
                                        <strong data-on="Active" data-off="Draft">Draft</strong>
                                        <small data-on="Can be drawn into staff tests" data-off="Not used in tests until switched on">Not used in tests until switched on</small>
                                    </span>
                                </label>
                                <div class="acc__input-error error-is_active"></div>
                            </div>
                        </div>
                        <div class="modal-footer ss-settings-modal__footer">
                            <button type="button" data-tw-dismiss="modal" class="ss-btn ss-btn--danger-soft">
                                <i data-lucide="x"></i>
                                Cancel
                            </button>
                            <button type="submit" id="saveQuestion" class="ss-btn ss-btn--primary">
                                @include('pages.hr.policy-assessment.partials.bank-spinner')
                                <i data-lucide="check"></i>
                                Save
                            </button>
                            <input type="hidden" name="id" value="0">
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @include('pages.hr.policy-assessment.partials.bank-modals')
    </div>
@endsection

@section('script')
    @vite('resources/js/settings.js')
    @vite('resources/js/policy-assessment-questions.js')
    @vite('resources/js/site-settings-redesign.js')
@endsection
