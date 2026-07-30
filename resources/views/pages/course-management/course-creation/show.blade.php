@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        // Venues are a repeater on the record, so each contributes its own pair
        // of meta tiles rather than being squeezed into one.
        $ccMeta = [
            ['label' => 'Duration', 'value' => trim($creation->duration.' '.$creation->unit_length), 'icon' => 'calendar'],
        ];

        if (!empty($creation->venues)) {
            $ccVenueIndex = 1;
            foreach ($creation->venues as $ccVenue) {
                $ccMeta[] = ['label' => 'Venue '.$ccVenueIndex, 'value' => $ccVenue->name ?? '', 'icon' => 'pin'];
                $ccMeta[] = ['label' => 'SLC Code '.$ccVenueIndex, 'value' => $ccVenue->pivot->slc_code ?? '', 'icon' => 'shield'];
                $ccVenueIndex++;
            }
        }

        $ccMeta[] = ['label' => 'Fees', 'value' => !empty($creation->fees) ? '£'.number_format($creation->fees, 2) : '', 'icon' => 'pound'];
        $ccMeta[] = ['label' => 'Reg. Fees', 'value' => !empty($creation->reg_fees) ? '£'.number_format($creation->reg_fees, 2) : '', 'icon' => 'pound'];
        $ccMeta[] = ['label' => 'Evening / Weekend', 'value' => $creation->has_evening_and_weekend == 1 ? 'Yes' : 'No', 'icon' => 'check'];
        $ccMeta[] = ['label' => 'Workplacement', 'value' => $creation->is_workplacement == 1 ? 'Yes' : 'No', 'icon' => 'layers'];

        if ($creation->is_workplacement == 1) {
            $ccMeta[] = ['label' => 'Required Hours', 'value' => $creation->required_hours, 'icon' => 'grid'];
        }
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @include('pages.course-management.partials.detail-header', [
                'detailBadge' => 'Course Creation',
                'detailSubtitle' => $creation->semester->name ?? '',
                'detailTitle' => $creation->course->name ?? '',
                'detailMeta' => $ccMeta,
                'detailTabs' => [
                    ['key' => 'availability', 'label' => 'Availabilty', 'icon' => 'calendar'],
                    ['key' => 'instance', 'label' => 'Instance', 'icon' => 'layers'],
                    ['key' => 'datafuture', 'label' => 'Datafuture', 'icon' => 'database'],
                ],
            ])

            {{-- ---------------------------------------------------------- --}}
            {{-- Availabilty                                                 --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="cm-card cm-tablecard" data-cm-tabpanel="availability">
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Availabilty</h2>
                        <span class="cm-tablecard__count" data-cm-count></span>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#cretionAvailabilityAddModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Availabilty
                    </button>
                </div>

                {{-- The availabilty endpoint filters by course creation only —
                     it reads neither `querystr` nor `status` — so this toolbar
                     is print/export only rather than showing inert controls. --}}
                @include('pages.course-management.partials.list-toolbar', [
                    'toolbarChips' => [],
                ])

                <div class="cm-tabulator-wrap">
                    <div id="courseCreationAvailibilityTableId" data-coursecreationid="{{ $creation->id }}" class="cm-tabulator"></div>
                </div>
            </div>

            {{-- ---------------------------------------------------------- --}}
            {{-- Instance — hand-rendered accordion, see the CSS note         --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="cm-card cm-tablecard" data-cm-tabpanel="instance" hidden>
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Instances</h2>
                        <span class="cm-tablecard__count" data-cm-count></span>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#addCourseCreationInstModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Instance
                    </button>
                </div>

                <div class="cm-inst__head">
                    <span></span>
                    <span>ID</span>
                    <span>Academic Year</span>
                    <span>Start Date</span>
                    <span>End Date</span>
                    <span>T. Weeks</span>
                    <span>Fees</span>
                    <span>Reg. Fees</span>
                    <span style="text-align:right;">Actions</span>
                </div>

                <div id="courseCreationInstList" data-creationid="{{ $creation->id }}"></div>
            </div>

            {{-- ---------------------------------------------------------- --}}
            {{-- Datafuture                                                  --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="cm-card cm-tablecard" data-cm-tabpanel="datafuture" hidden>
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Datafuture Fields</h2>
                        <span class="cm-tablecard__count" data-cm-count></span>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#addCourseCreationDataFutureModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Datafuture Field
                    </button>
                </div>

                @include('pages.course-management.partials.list-toolbar', [
                    'toolbarSearchLabel' => 'Search datafuture fields',
                ])

                <div class="cm-tabulator-wrap">
                    <div id="courseCreationDataFutureTableId" data-coursecreationid="{{ $creation->id }}" class="cm-tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Availabilty add / edit                                            --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $avMode)
        @php
            $avIsAdd = $avMode === 'add';
            $avPrefix = $avIsAdd ? 'av_add' : 'av_edit';
            $avDates = [
                'admission_date' => 'Admission Start Date',
                'admission_end_date' => 'Admission End Date',
                'course_start_date' => 'Course Start Date',
                'course_end_date' => 'Course End Date',
                'last_joinning_date' => 'Last Joinning Date',
            ];
        @endphp

        <div id="{{ $avIsAdd ? 'cretionAvailabilityAddModal' : 'cretionAvailabilityEditModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
                <form method="POST" action="#" id="{{ $avIsAdd ? 'cretionAvailabilityAddForm' : 'cretionAvailabilityEditForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $avIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $avIsAdd ? 'Add Availabilty' : 'Edit Availabilty' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid2">
                            @foreach($avDates as $avKey => $avLabel)
                                <div class="cm-field">
                                    <label for="{{ $avPrefix }}_{{ $avKey }}">{{ $avLabel }} <span>*</span></label>
                                    <input id="{{ $avPrefix }}_{{ $avKey }}" name="{{ $avKey }}" type="text"
                                           class="cm-input datepicker {{ $avKey }}"
                                           data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                                    <div class="acc__input-error error-{{ $avKey }}"></div>
                                </div>
                            @endforeach

                            <div class="cm-field">
                                <label for="{{ $avPrefix }}_type">Type <span>*</span></label>
                                <select id="{{ $avPrefix }}_type" name="type" class="cm-select type">
                                    <option value="">Please Select</option>
                                    <option value="UK">UK</option>
                                    <option value="OVERSEAS">OVERSEAS</option>
                                    <option value="BOTH">BOTH</option>
                                </select>
                                <div class="acc__input-error error-type"></div>
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $avIsAdd ? 'crationAvailabilitySave' : 'crationAvailabilityUpdate' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $avIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_creation_id" value="{{ $creation->id }}">
                            @unless($avIsAdd)
                                <input type="hidden" name="id" value="0">
                            @endunless
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ---------------------------------------------------------------- --}}
    {{-- Instance add / edit                                               --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $inMode)
        @php
            $inIsAdd = $inMode === 'add';
            $inPrefix = $inIsAdd ? 'in_add' : 'in_edit';
        @endphp

        <div id="{{ $inIsAdd ? 'addCourseCreationInstModal' : 'editCourseCreationInstModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
                <form method="POST" action="#" id="{{ $inIsAdd ? 'addCourseCreationInstForm' : 'editCourseCreationInstForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $inIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $inIsAdd ? 'Add Instance' : 'Edit Instance' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid2">
                            <div class="cm-field cm-field--span2">
                                <label for="{{ $inPrefix }}_academic_year_id">Academic Year <span>*</span></label>
                                <select id="{{ $inPrefix }}_academic_year_id" name="academic_year_id" class="cm-select academic_year_id">
                                    <option value="">Please Select</option>
                                    @foreach($academic as $ay)
                                        <option value="{{ $ay->id }}">{{ $ay->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-academic_year_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $inPrefix }}_start_date">Start Date <span>*</span></label>
                                <input id="{{ $inPrefix }}_start_date" name="start_date" type="text" class="cm-input datepicker start_date" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                                <div class="acc__input-error error-start_date"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $inPrefix }}_end_date">End Date <span>*</span></label>
                                <input id="{{ $inPrefix }}_end_date" name="end_date" type="text" class="cm-input datepicker end_date" data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                                <div class="acc__input-error error-end_date"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $inPrefix }}_total_teaching_week">Total Teaching Week <span>*</span></label>
                                <input id="{{ $inPrefix }}_total_teaching_week" name="total_teaching_week" type="number" min="0" class="cm-input total_teaching_week">
                                <div class="acc__input-error error-total_teaching_week"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $inPrefix }}_fees">Fees (UK)</label>
                                <input id="{{ $inPrefix }}_fees" name="fees" type="number" step="any" min="0" class="cm-input fees">
                                <div class="acc__input-error error-fees"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $inPrefix }}_reg_fees">Reg. Fees (UK)</label>
                                <input id="{{ $inPrefix }}_reg_fees" name="reg_fees" type="number" step="any" min="0" class="cm-input reg_fees">
                                <div class="acc__input-error error-reg_fees"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $inPrefix }}_university_commission">University Commission</label>
                                <input id="{{ $inPrefix }}_university_commission" name="university_commission" type="number" step="any" min="0" class="cm-input university_commission">
                                <div class="acc__input-error error-university_commission"></div>
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $inIsAdd ? 'saveCCIN' : 'updateCCIN' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $inIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_creation_id" value="{{ $creation->id }}">
                            @unless($inIsAdd)
                                <input type="hidden" name="id" value="0">
                            @endunless
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ---------------------------------------------------------------- --}}
    {{-- Instance term add / edit                                          --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $itMode)
        @php
            $itIsAdd = $itMode === 'add';
            $itPrefix = $itIsAdd ? 'it_add' : 'it_edit';
            $itDates = [
                'start_date' => 'Start Date',
                'end_date' => 'End Date',
                'teaching_start_date' => 'Teaching Start Date',
                'teaching_end_date' => 'Teaching End Date',
                'revision_start_date' => 'Revision Start Date',
                'revision_end_date' => 'Revision End Date',
            ];
        @endphp

        <div id="{{ $itIsAdd ? 'instancetermAddModal' : 'instancetermEditModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
                <form method="POST" action="#" id="{{ $itIsAdd ? 'instancetermAddForm' : 'instancetermEditForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $itIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $itIsAdd ? 'Add Instance Term' : 'Edit Instance Term' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid2">
                            <div class="cm-field cm-field--span2">
                                <label for="{{ $itPrefix }}_term_declaration_id">Term Name <span>*</span></label>
                                <select id="{{ $itPrefix }}_term_declaration_id" name="term_declaration_id" class="cm-select term_declaration_id">
                                    <option value="">Please Select</option>
                                    @foreach($termDeclarations as $td)
                                        <option value="{{ $td->id }}">{{ $td->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-term_declaration_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $itPrefix }}_session_term">Session Term <span>*</span></label>
                                <select id="{{ $itPrefix }}_session_term" name="session_term" class="cm-select session_term">
                                    <option value="">Please Select</option>
                                    <option value="Term 1">Term 1</option>
                                    <option value="Term 2">Term 2</option>
                                    <option value="Term 3">Term 3</option>
                                    <option value="Term 4">Term 4</option>
                                </select>
                                <div class="acc__input-error error-session_term"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $itPrefix }}_total_teaching_weeks">Total Teaching Weeks <span>*</span></label>
                                <input id="{{ $itPrefix }}_total_teaching_weeks" name="total_teaching_weeks" type="number" min="0" class="cm-input total_teaching_weeks">
                                <div class="acc__input-error error-total_teaching_weeks"></div>
                            </div>

                            @foreach($itDates as $itKey => $itLabel)
                                <div class="cm-field">
                                    <label for="{{ $itPrefix }}_{{ $itKey }}">{{ $itLabel }} <span>*</span></label>
                                    <input id="{{ $itPrefix }}_{{ $itKey }}" name="{{ $itKey }}" type="text"
                                           class="cm-input datepicker {{ $itKey }}"
                                           data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                                    <div class="acc__input-error error-{{ $itKey }}"></div>
                                </div>
                            @endforeach
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $itIsAdd ? 'saveInstanceTerm' : 'updateInstanceTerm' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $itIsAdd ? 'Save' : 'Update' }}
                            </button>
                            {{-- Set by JS from the instance whose "Add Term" was clicked. --}}
                            <input type="hidden" name="course_creation_instance_id" value="0">
                            @unless($itIsAdd)
                                <input type="hidden" name="id" value="0">
                            @endunless
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ---------------------------------------------------------------- --}}
    {{-- Datafuture add / edit                                             --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $dfMode)
        @php
            $dfIsAdd = $dfMode === 'add';
            $dfPrefix = $dfIsAdd ? 'ccdf_add' : 'ccdf_edit';
        @endphp

        <div id="{{ $dfIsAdd ? 'addCourseCreationDataFutureModal' : 'editCourseCreationDataFutureModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog">
                <form method="POST" action="#" id="{{ $dfIsAdd ? 'addCourseCreationDataFutureForm' : 'editCourseCreationDataFutureForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $dfIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $dfIsAdd ? 'Add Datafuture Field' : 'Edit Datafuture Field' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body">
                            <div class="cm-field">
                                <label for="{{ $dfPrefix }}_field_name">Field Name <span>*</span></label>
                                <input id="{{ $dfPrefix }}_field_name" name="field_name" type="text" class="cm-input field_name">
                                <div class="acc__input-error error-field_name"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $dfPrefix }}_field_type">Field Type <span>*</span></label>
                                <select id="{{ $dfPrefix }}_field_type" name="field_type" class="cm-select field_type">
                                    <option value="">Please Select</option>
                                    <option value="text">text</option>
                                    <option value="number">number</option>
                                    <option value="date">date</option>
                                </select>
                                <div class="acc__input-error error-field_type"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $dfPrefix }}_field_value">Field Value <span>*</span></label>
                                <input id="{{ $dfPrefix }}_field_value" name="field_value" type="text" class="cm-input field_value">
                                <div class="acc__input-error error-field_value"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $dfPrefix }}_field_desc">Field Description</label>
                                <textarea id="{{ $dfPrefix }}_field_desc" name="field_desc" class="cm-input cm-textarea field_desc" rows="3"></textarea>
                                <div class="acc__input-error error-field_desc"></div>
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $dfIsAdd ? 'saveCCDF' : 'updateCCDF' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $dfIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_creation_id" value="{{ $creation->id }}">
                            @unless($dfIsAdd)
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
    @vite('resources/js/course-creation-detail.js')
@endsection
