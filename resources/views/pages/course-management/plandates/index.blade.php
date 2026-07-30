@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            <div class="cm-card cm-tablecard">
                <div class="cm-tablecard__head cm-tablecard__head--divided">
                    <div class="cm-tablecard__titles" style="display:block;">
                        <h2 class="cm-tablecard__title cm-serif">Class Plan Dates</h2>
                        <p class="cm-datehead__sub">{{ $planLabel }}</p>
                    </div>
                    <div class="cm-tablecard__actions">
                        <button type="button" data-tw-toggle="modal" data-tw-target="#addPlanDateModal" class="cm-btn cm-btn--pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                            Add New Date
                        </button>
                        <a href="{{ route('plans.tree') }}" class="cm-btn cm-btn--ghost">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                            Back to tree
                        </a>
                    </div>
                </div>

                {{-- `dates` and `status` are the names `list()` reads. --}}
                <form class="cm-datefilters" id="planDateFilterForm" autocomplete="off">
                    <input type="text" id="pd-date" name="dates" class="cm-input cm-input--sm datepicker" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                    <select id="pd-status" name="status" class="cm-select cm-select--sm">
                        <option value="1" selected>Active</option>
                        <option value="2">Archived</option>
                    </select>
                    <button type="submit" class="cm-btn cm-btn--go">Go</button>
                    <button type="button" id="planDateReset" class="cm-btn cm-btn--ghost">Reset</button>

                    <span class="cm-datefilters__spacer"></span>
                    <span class="cm-tablecard__count" data-cm-count="date"></span>
                </form>

                <div class="cm-tabulator-wrap">
                    <div id="planDateListTable" class="cm-tabulator" data-planid="{{ $planid }}"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Add a date                                                        --}}
    {{-- ---------------------------------------------------------------- --}}
    <div id="addPlanDateModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog cm-modal__dialog cm-modal__dialog--sm">
            <form method="POST" action="#" id="addPlanDateForm" autocomplete="off">
                <div class="modal-content cm-modal">
                    <div class="cm-modal__head">
                        <div>
                            <div class="cm-modal__eyebrow"><span>New record</span></div>
                            <h2 class="cm-modal__title cm-serif">Add New Date</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="cm-modal__body">
                        <div class="cm-field">
                            <label for="pd_name">Name <span>*</span></label>
                            <select id="pd_name" name="name" class="cm-select">
                                <option value="">Please Select</option>
                                <option value="Teaching">Teaching</option>
                                <option value="Revision">Revision</option>
                                <option value="Submission">Submission</option>
                            </select>
                            <div class="acc__input-error error-name"></div>
                        </div>

                        <div class="cm-field">
                            <label for="pd_date">Date <span>*</span></label>
                            <input id="pd_date" type="text" name="date" class="cm-input datepicker" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY" readonly>
                            <div class="acc__input-error error-date"></div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button type="submit" id="savePlanDate" class="cm-btn cm-btn--save">
                            @include('pages.course-management.partials.save-glyphs')
                            Save
                        </button>
                        <input type="hidden" name="plan_id" value="{{ $planid }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-plan-dates.js')
@endsection
