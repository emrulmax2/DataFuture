@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $canManageTerms = isset(auth()->user()->priv()['terms_and_modules'])
            && auth()->user()->priv()['terms_and_modules'] == 1;
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @if($canManageTerms)
                <div class="cm-card cm-tablecard">
                    <div class="cm-tablecard__head">
                        <div class="cm-tablecard__titles">
                            <h2 class="cm-tablecard__title cm-serif">Course Creations</h2>
                            {{-- Filled from the list response so it tracks the active filters. --}}
                            <span class="cm-tablecard__count" data-cm-count></span>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#addCourseCreationModal" type="button" class="cm-btn cm-btn--pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                            Add Course Creation
                        </button>
                    </div>

                    {{-- This list narrows by course and semester on top of the
                         search box, which is what the endpoint's `course` and
                         `semester` parameters expect. --}}
                    @include('pages.course-management.partials.list-toolbar', [
                        'toolbarSearchLabel' => 'Search course creations',
                        'toolbarSelects' => [
                            [
                                'name' => 'course',
                                'label' => 'All courses',
                                'options' => collect($courses)->map(fn ($c) => ['value' => $c->id, 'label' => $c->name])->all(),
                            ],
                            [
                                'name' => 'semester',
                                'label' => 'All semesters',
                                'options' => collect($semesters)->map(fn ($s) => ['value' => $s->id, 'label' => $s->name])->all(),
                            ],
                        ],
                    ])

                    <div class="cm-tabulator-wrap">
                        <div id="courseCreationTableId" class="cm-tabulator"></div>
                    </div>
                </div>
            @else
                <div class="cm-card">
                    <div class="cm-empty">
                        <span class="cm-empty__icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                        </span>
                        <div class="cm-empty__title cm-serif">Permission required</div>
                        <div class="cm-empty__text">You do not have permission to view course creations. Use the menu on the left to reach a section you can access.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @foreach(['add', 'edit'] as $ccMode)
        @php
            $ccIsAdd = $ccMode === 'add';
            $ccPrefix = $ccIsAdd ? 'cc_add' : 'cc_edit';
        @endphp

        <div id="{{ $ccIsAdd ? 'addCourseCreationModal' : 'editCourseCreationModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
                <form method="POST" action="#" id="{{ $ccIsAdd ? 'addCourseCreationForm' : 'editCourseCreationForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $ccIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $ccIsAdd ? 'Add Course Creation' : 'Edit Course Creation' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid3">
                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_course_id">Course <span>*</span></label>
                                <select id="{{ $ccPrefix }}_course_id" name="course_id" class="cm-select course_id">
                                    <option value="">Please Select</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-course_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_semester_id">Semester <span>*</span></label>
                                <select id="{{ $ccPrefix }}_semester_id" name="semester_id" class="cm-select semester_id">
                                    <option value="">Please Select</option>
                                    @foreach($semesters as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-semester_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_course_creation_qualification_id">Qualification <span>*</span></label>
                                <select id="{{ $ccPrefix }}_course_creation_qualification_id" name="course_creation_qualification_id" class="cm-select course_creation_qualification_id">
                                    <option value="">Please Select</option>
                                    @foreach($qualifications as $q)
                                        <option value="{{ $q->id }}">{{ $q->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-course_creation_qualification_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_duration">Duration <span>*</span></label>
                                <input id="{{ $ccPrefix }}_duration" type="number" min="0" name="duration" class="cm-input duration">
                                <div class="acc__input-error error-duration"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_unit_length">Unit Length <span>*</span></label>
                                <select id="{{ $ccPrefix }}_unit_length" name="unit_length" class="cm-select unit_length">
                                    <option value="">Please Select</option>
                                    <option value="Years">Years</option>
                                    <option value="Months">Months</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Days">Days</option>
                                </select>
                                <div class="acc__input-error error-unit_length"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_fees">Fees (UK)</label>
                                <input id="{{ $ccPrefix }}_fees" type="number" step="any" min="0" name="fees" class="cm-input fees">
                                <div class="acc__input-error error-fees"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_reg_fees">Reg. Fees (UK)</label>
                                <input id="{{ $ccPrefix }}_reg_fees" type="number" step="any" min="0" name="reg_fees" class="cm-input reg_fees">
                                <div class="acc__input-error error-reg_fees"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $ccPrefix }}_university_commission">University Commission</label>
                                <input id="{{ $ccPrefix }}_university_commission" type="number" step="any" min="0" name="university_commission" class="cm-input university_commission">
                                <div class="acc__input-error error-university_commission"></div>
                            </div>

                            <label class="cm-switchfield">
                                <span>Has Evening / Weekend</span>
                                <input id="{{ $ccPrefix }}_has_evening_and_weekend" class="cm-switchcard__input has_evening_and_weekend" name="has_evening_and_weekend" value="1" type="checkbox">
                                <span class="cm-switchcard">
                                    <span class="cm-switchcard__tile">
                                        <svg data-cm-switch-on width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        <svg data-cm-switch-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                    </span>
                                    <span class="cm-switchcard__copy">
                                        <span class="cm-switchcard__title" data-on="Yes" data-off="No"></span>
                                        <span class="cm-switchcard__desc" data-on="Evening and weekend delivery" data-off="Weekday delivery only"></span>
                                    </span>
                                </span>
                            </label>

                            <label class="cm-switchfield">
                                <span>Has Workplacement</span>
                                <input id="{{ $ccPrefix }}_is_workplacement" class="cm-switchcard__input is_workplacement" name="is_workplacement" value="1" type="checkbox" data-cm-toggles="is_workplacement">
                                <span class="cm-switchcard">
                                    <span class="cm-switchcard__tile">
                                        <svg data-cm-switch-on width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        <svg data-cm-switch-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                    </span>
                                    <span class="cm-switchcard__copy">
                                        <span class="cm-switchcard__title" data-on="Yes" data-off="No"></span>
                                        <span class="cm-switchcard__desc" data-on="Includes a placement module" data-off="No placement required"></span>
                                    </span>
                                </span>
                            </label>

                            {{-- Shown only when a work placement is set — see
                                 course-creation-page.js. The controller zeroes
                                 `required_hours` unless `is_workplacement` is on. --}}
                            <div class="cm-field" data-cm-showif="is_workplacement" hidden>
                                <label for="{{ $ccPrefix }}_required_hours">Required Hours</label>
                                <input id="{{ $ccPrefix }}_required_hours" type="number" step="any" min="0" name="required_hours" class="cm-input required_hours">
                                <div class="acc__input-error error-required_hours"></div>
                            </div>


                            <div class="cm-field--span3">
                                @include('pages.course-management.partials.venue-repeater', ['venuesSeeded' => $ccIsAdd])
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $ccIsAdd ? 'saveCourseCreation' : 'updateCourseCreation' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $ccIsAdd ? 'Save' : 'Update' }}
                            </button>
                            @unless($ccIsAdd)
                                <input type="hidden" name="id" value="0">
                            @endunless
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-creation-page.js')
@endsection
