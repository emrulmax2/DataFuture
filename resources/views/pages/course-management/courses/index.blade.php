@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $colorThemes = [
            'lcc_teal'     => 'LCC Teal',
            'burgundy'     => 'Burgundy',
            'oxford_blue'  => 'Oxford Blue',
            'forest_green' => 'Forest Green',
            'aubergine'    => 'Aubergine',
            'terracotta'   => 'Terracotta',
            'slate'        => 'Slate',
            'deep_petrol'  => 'Deep Petrol',
        ];

        $canManageCourses = isset(auth()->user()->priv()['course_and_semesters'])
            && auth()->user()->priv()['course_and_semesters'] == 1;
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @if($canManageCourses)
                <div class="cm-card cm-tablecard">
                    <div class="cm-tablecard__head">
                        <div class="cm-tablecard__titles">
                            <h2 class="cm-tablecard__title cm-serif">Courses</h2>
                            {{-- Filled from the list response so it tracks the active filters. --}}
                            <span class="cm-tablecard__count" data-cm-count></span>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="cm-btn cm-btn--pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                            New Course
                        </button>
                    </div>

                    {{-- Courses carry an `active` flag on top of soft deletes, so
                         this list has three states where the simple lists have two. --}}
                    @include('pages.course-management.partials.list-toolbar', [
                        'toolbarSearchLabel' => 'Search courses',
                        'toolbarChips' => [
                            ['value' => '1', 'label' => 'Active'],
                            ['value' => '0', 'label' => 'Inactive'],
                            ['value' => '2', 'label' => 'Archived'],
                        ],
                    ])

                    <div class="cm-tabulator-wrap">
                        <div id="courseTableId" class="cm-tabulator"></div>
                    </div>
                </div>
            @else
                <div class="cm-card">
                    <div class="cm-empty">
                        <span class="cm-empty__icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                        </span>
                        <div class="cm-empty__title cm-serif">Permission required</div>
                        <div class="cm-empty__text">You do not have permission to view courses. Use the menu on the left to reach a section you can access.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @foreach(['add', 'edit'] as $courseMode)
        @php
            $isAdd = $courseMode === 'add';
            $modalId = $isAdd ? 'addModal' : 'editModal';
            $formId = $isAdd ? 'addForm' : 'editForm';
            $prefix = $isAdd ? 'add' : 'edit';
        @endphp

        <div id="{{ $modalId }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
                <form method="POST" action="#" id="{{ $formId }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $isAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $isAdd ? 'Add Course' : 'Edit Course' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid4">
                            <div class="cm-field cm-field--span2">
                                <label for="{{ $prefix }}_name">Course Name <span>*</span></label>
                                <input id="{{ $prefix }}_name" type="text" name="name" class="cm-input name" placeholder="e.g. HND in Business">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="cm-field cm-field--span2">
                                <label for="{{ $prefix }}_degree_offered">Degree Offered <span>*</span></label>
                                <input id="{{ $prefix }}_degree_offered" type="text" name="degree_offered" class="cm-input degree_offered" placeholder="e.g. Higher National Diploma">
                                <div class="acc__input-error error-degree_offered"></div>
                            </div>

                            <div class="cm-field cm-field--span2">
                                <label for="{{ $prefix }}_pre_qualification">Pre Qualification <span>*</span></label>
                                <input id="{{ $prefix }}_pre_qualification" type="text" name="pre_qualification" class="cm-input pre_qualification" placeholder="e.g. Level 3 or equivalent">
                                <div class="acc__input-error error-pre_qualification"></div>
                            </div>

                            <div class="cm-field cm-field--span2">
                                <label for="{{ $prefix }}_awarding_body">Awarding Body <span>*</span></label>
                                <select id="{{ $prefix }}_awarding_body" name="awarding_body_id" class="cm-select awarding_body_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($bodies))
                                        @foreach($bodies as $bds)
                                            <option value="{{ $bds->id }}">{{ $bds->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-awarding_body_id"></div>
                            </div>

                            <div class="cm-field cm-field--span3">
                                <label for="{{ $prefix }}_source_tution_fee">Source of Tution Fee <span>*</span></label>
                                <select id="{{ $prefix }}_source_tution_fee" name="source_tuition_fee_id" class="cm-select source_tuition_fee_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($fees))
                                        @foreach($fees as $fs)
                                            <option value="{{ $fs->id }}">{{ $fs->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-source_tuition_fee_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $prefix }}_color_theme">Color Theme</label>
                                <select id="{{ $prefix }}_color_theme" name="color_theme" class="cm-select color_theme">
                                    <option value="">Please Select</option>
                                    @foreach($colorThemes as $slug => $themeName)
                                        <option value="{{ $slug }}">{{ $themeName }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-color_theme"></div>
                            </div>

                            {{-- Real checkboxes: the controller reads
                                 `franchise_course` presence and `active` value, so
                                 the payload is unchanged from the legacy form. --}}
                            <label class="cm-switchfield cm-field--span2">
                                <span>Franchise Course</span>
                                <input id="{{ $prefix }}_franchise_course" class="cm-switchcard__input" name="franchise_course" value="Yes" type="checkbox">
                                <span class="cm-switchcard">
                                    <span class="cm-switchcard__tile">
                                        <svg data-cm-switch-on width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        <svg data-cm-switch-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                    </span>
                                    <span class="cm-switchcard__copy">
                                        <span class="cm-switchcard__title" data-on="Franchise course" data-off="Direct course"></span>
                                        <span class="cm-switchcard__desc" data-on="Delivered with a partner provider" data-off="Delivered by the college only"></span>
                                    </span>
                                </span>
                            </label>

                            <label class="cm-switchfield cm-field--span2">
                                <span>Active Status</span>
                                <input id="{{ $prefix }}_active" class="cm-switchcard__input" name="active" value="1" type="checkbox" @if($isAdd) checked @endif>
                                <span class="cm-switchcard">
                                    <span class="cm-switchcard__tile">
                                        <svg data-cm-switch-on width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        <svg data-cm-switch-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                    </span>
                                    <span class="cm-switchcard__copy">
                                        <span class="cm-switchcard__title" data-on="Active" data-off="Inactive"></span>
                                        <span class="cm-switchcard__desc" data-on="Available for new term setup" data-off="Not available for new term setup"></span>
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $isAdd ? 'save' : 'update' }}" class="cm-btn cm-btn--save">
                                <svg style="display: none;" class="cm-spinner" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
                                    <g fill="none" fill-rule="evenodd">
                                        <g transform="translate(1 1)" stroke-width="4">
                                            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                            <path d="M36 18c0-9.94-8.06-18-18-18">
                                                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                            </path>
                                        </g>
                                    </g>
                                </svg>
                                <svg data-cm-btn-icon width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><path d="M17 21v-8H7v8M7 3v5h8"></path></svg>
                                {{ $isAdd ? 'Save' : 'Update' }}
                            </button>
                            @unless($isAdd)
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
    @vite('resources/js/course-courses.js')
@endsection
