@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $tmDate = fn ($v) => (!empty($v) && $v != '0000-00-00') ? date('jS F, Y', strtotime($v)) : '';
        $tmRange = function ($from, $to) use ($tmDate) {
            $a = $tmDate($from);
            $b = $tmDate($to);

            return $a && $b ? $a.' – '.$b : ($a ?: $b);
        };
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @include('pages.course-management.partials.detail-header', [
                'detailBadge' => 'Term Modules',
                'detailSubtitle' => $term->course_name ?? '',
                'detailTitle' => $term->term_name ?? '',
                'detailMeta' => [
                    ['label' => 'Start Date', 'value' => $tmDate($term->start_date ?? null), 'icon' => 'calendar'],
                    ['label' => 'End Date', 'value' => $tmDate($term->end_date ?? null), 'icon' => 'calendar'],
                    ['label' => 'Teaching Weeks', 'value' => $term->total_teaching_weeks ?? '', 'icon' => 'layers'],
                    ['label' => 'Revision', 'value' => $tmRange($term->revision_start_date ?? null, $term->revision_end_date ?? null), 'icon' => 'shield'],
                ],
            ])

            <div class="cm-card cm-tablecard">
                {{-- The endpoint filters on the search term only, so this screen
                     carries a lone search box in the header rather than the full
                     toolbar — `wireFilters` binds on `data-cm-query` either way. --}}
                <div class="cm-tablecard__head cm-tablecard__head--divided">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Modules</h2>
                        <span class="cm-tablecard__count" data-cm-count="module"></span>
                    </div>
                    <div class="cm-tablecard__actions">
                        <form class="cm-headsearch" data-cm-filterform autocomplete="off">
                            <label class="cm-headsearch__box">
                                <span class="cm-sr-only">Search modules</span>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                                <input name="query" type="search" data-cm-query placeholder="Search modules">
                            </label>
                        </form>
                        <button data-tw-toggle="modal" data-tw-target="#addModuleCreationModal" type="button" class="cm-btn cm-btn--pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                            Add Module
                        </button>
                    </div>
                </div>

                <div class="cm-tabulator-wrap">
                    <div id="termModuleListTable" data-terminstanceid="{{ $term->id }}" class="cm-tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Add module creation                                               --}}
    {{-- ---------------------------------------------------------------- --}}
    <div id="addModuleCreationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
            <form method="POST" action="#" id="addModuleCreationForm" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-content cm-modal">
                    <div class="cm-modal__head">
                        <div>
                            <div class="cm-modal__eyebrow"><span>New record</span></div>
                            <h2 class="cm-modal__title cm-serif">Add Module Creation</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="cm-modal__body">
                        <div class="cm-field">
                            <label for="creation_module_id">Module <span>*</span></label>
                            {{-- Modules already on this term are disabled rather
                                 than removed, so it is obvious they exist. --}}
                            <select id="creation_module_id" name="course_module_id" class="cm-select course_module_id">
                                <option value="">Please Select</option>
                                @if(!empty($modules))
                                    @foreach($modules as $mod)
                                        <option value="{{ $mod->id }}" @if(!empty($existing_modules) && in_array($mod->id, $existing_modules)) disabled @endif>{{ $mod->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-course_module_id"></div>
                        </div>

                        {{-- Filled from `term.module.get.base.assessment` once a
                             module is chosen. --}}
                        <div class="cm-field" data-cm-indv-wrap hidden>
                            <label>Course Module Base Assesments</label>
                            <div data-cm-indv-list></div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button type="submit" id="saveModuleCreation" class="cm-btn cm-btn--save">
                            @include('pages.course-management.partials.save-glyphs')
                            Save
                        </button>
                        {{-- Both are read by `storeIndividually`. --}}
                        <input type="hidden" name="instance_term_id" value="{{ $term->id }}">
                        <input type="hidden" name="course_id" value="{{ $course->id }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Assessments — view (has some) / add (has none)                    --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach([
        ['id' => 'viewModuleAssessmentModal', 'form' => 'viewModuleAssessmentForm', 'btn' => 'updateAssessments', 'label' => 'Update', 'eyebrow' => 'Edit record'],
        ['id' => 'addModuleAssessmentModal', 'form' => 'addModuleAssessmentForm', 'btn' => 'addMAssessments', 'label' => 'Save', 'eyebrow' => 'New record'],
    ] as $assModal)
        <div id="{{ $assModal['id'] }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
                <form method="POST" action="#" id="{{ $assModal['form'] }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $assModal['eyebrow'] }}</span></div>
                                <h2 class="cm-modal__title cm-serif">Assessments &mdash; <span class="moduleName">Module</span></h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body">
                            <div class="theLoader" style="padding:32px; text-align:center; color:#a8a49a; font-size:13px; font-weight:600;">Loading assesments&hellip;</div>
                            <div class="theContent" style="display:none;"></div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $assModal['btn'] }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $assModal['label'] }}
                            </button>
                            <input type="hidden" name="module_creation_id" value="0">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ---------------------------------------------------------------- --}}
    {{-- Edit module creation                                              --}}
    {{-- ---------------------------------------------------------------- --}}
    <div id="editModuleCreationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
            <form method="POST" action="#" id="editModuleCreationForm" enctype="multipart/form-data" autocomplete="off">
                <div class="modal-content cm-modal">
                    <div class="cm-modal__head">
                        <div>
                            <div class="cm-modal__eyebrow"><span>Edit record</span></div>
                            <h2 class="cm-modal__title cm-serif">Edit Module Creation</h2>
                        </div>
                        <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="cm-modal__body cm-modal__body--grid2">
                        <div class="cm-field cm-field--span2">
                            <label for="mc_module_name">Module Name <span>*</span></label>
                            <input id="mc_module_name" type="text" name="module_name" class="cm-input module_name">
                            <div class="acc__input-error error-module_name"></div>
                        </div>

                        <div class="cm-field">
                            <label for="mc_code">Code <span>*</span></label>
                            <input id="mc_code" type="text" name="code" class="cm-input code">
                            <div class="acc__input-error error-code"></div>
                        </div>

                        <div class="cm-field">
                            <label for="mc_status">Status</label>
                            <select id="mc_status" name="status" class="cm-select status">
                                <option value="">Please Select</option>
                                <option value="core">Core</option>
                                <option value="specialist">Specialist</option>
                                <option value="optional">Optional</option>
                            </select>
                            <div class="acc__input-error error-status"></div>
                        </div>

                        <div class="cm-field">
                            <label for="mc_credit_value">Credit Value <span>*</span></label>
                            <input id="mc_credit_value" type="text" name="credit_value" class="cm-input credit_value">
                            <div class="acc__input-error error-credit_value"></div>
                        </div>

                        <div class="cm-field">
                            <label for="mc_unit_value">Unit Value <span>*</span></label>
                            <input id="mc_unit_value" type="text" name="unit_value" class="cm-input unit_value">
                            <div class="acc__input-error error-unit_value"></div>
                        </div>

                        <div class="cm-field">
                            <label for="mc_moodle_enrollment_key">Enrollment Key</label>
                            <input id="mc_moodle_enrollment_key" type="text" name="moodle_enrollment_key" class="cm-input moodle_enrollment_key">
                            <div class="acc__input-error error-moodle_enrollment_key"></div>
                        </div>

                        <div class="cm-field">
                            <label for="mc_class_type">Class Type</label>
                            <select id="mc_class_type" name="class_type" class="cm-select class_type">
                                <option value="">Please Select</option>
                                <option value="Theory">Theory</option>
                                <option value="Practical">Practical</option>
                                <option value="Tutorial">Tutorial</option>
                                <option value="Seminar">Seminar</option>
                            </select>
                            <div class="acc__input-error error-class_type"></div>
                        </div>

                        <div class="cm-field cm-field--span2">
                            <label for="mc_submission_date">Submission Date</label>
                            <input id="mc_submission_date" type="text" name="submission_date"
                                   class="cm-input datepicker submission_date"
                                   data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                            <div class="acc__input-error error-submission_date"></div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            Cancel
                        </button>
                        <button type="submit" id="updateModuleCreation" class="cm-btn cm-btn--save">
                            @include('pages.course-management.partials.save-glyphs')
                            Update
                        </button>
                        <input type="hidden" name="id" value="0">
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-term-module-show.js')
@endsection
