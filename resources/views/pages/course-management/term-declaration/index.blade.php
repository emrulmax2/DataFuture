@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $canManageTerms = isset(auth()->user()->priv()['terms_and_modules'])
            && auth()->user()->priv()['terms_and_modules'] == 1;

        // Field layout for the add/edit forms — three columns, in the order the
        // mock lays them out. `type` maps to the control; `req` adds the marker.
        $termFields = [
            ['key' => 'academic_year_id', 'label' => 'Academic Year', 'type' => 'select', 'req' => true, 'options' => 'years'],
            ['key' => 'name', 'label' => 'Term Name', 'type' => 'text', 'req' => true],
            ['key' => 'term_type_id', 'label' => 'Term Type', 'type' => 'select', 'req' => true, 'options' => 'types'],
            ['key' => 'start_date', 'label' => 'Start Date', 'type' => 'date', 'req' => true],
            ['key' => 'end_date', 'label' => 'End Date', 'type' => 'date', 'req' => true],
            ['key' => 'total_teaching_weeks', 'label' => 'Total Teaching Weeks', 'type' => 'number', 'req' => true],
            ['key' => 'teaching_start_date', 'label' => 'Teaching Start Date', 'type' => 'date', 'req' => true],
            ['key' => 'teaching_end_date', 'label' => 'Teaching End Date', 'type' => 'date', 'req' => true],
            ['key' => 'revision_start_date', 'label' => 'Revision Start Date', 'type' => 'date', 'req' => true],
            ['key' => 'revision_end_date', 'label' => 'Revision End Date', 'type' => 'date', 'req' => true],
            ['key' => 'exam_publish_date', 'label' => 'Exam Publish Date', 'type' => 'date'],
            ['key' => 'exam_publish_time', 'label' => 'Exam Publish Time', 'type' => 'time'],
            ['key' => 'exam_resubmission_publish_date', 'label' => 'Resubmission Publish Date', 'type' => 'date'],
            ['key' => 'exam_resubmission_publish_time', 'label' => 'Resubmission Publish Time', 'type' => 'time'],
            ['key' => 'stuload', 'label' => 'Term Stuload', 'type' => 'number', 'max' => 100, 'placeholder' => '33'],
        ];
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @if($canManageTerms)
                <div class="cm-card cm-tablecard">
                    <div class="cm-tablecard__head">
                        <div class="cm-tablecard__titles">
                            <h2 class="cm-tablecard__title cm-serif">Term Declarations</h2>
                            {{-- Filled from the list response so it tracks the active filters. --}}
                            <span class="cm-tablecard__count" data-cm-count></span>
                        </div>
                        <button data-tw-toggle="modal" data-tw-target="#addModal" type="button" class="cm-btn cm-btn--pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                            Add New Term
                        </button>
                    </div>

                    @include('pages.course-management.partials.list-toolbar', [
                        'toolbarSearchLabel' => 'Search term declarations',
                    ])

                    <div class="cm-tabulator-wrap">
                        <div id="termTableId" class="cm-tabulator"></div>
                    </div>
                </div>
            @else
                <div class="cm-card">
                    <div class="cm-empty">
                        <span class="cm-empty__icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                        </span>
                        <div class="cm-empty__title cm-serif">Permission required</div>
                        <div class="cm-empty__text">You do not have permission to view term declarations. Use the menu on the left to reach a section you can access.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @foreach(['add', 'edit'] as $termMode)
        @php
            $termIsAdd = $termMode === 'add';
            $termPrefix = $termIsAdd ? 'td_add' : 'td_edit';
        @endphp

        <div id="{{ $termIsAdd ? 'addModal' : 'editModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
                <form method="POST" action="#" id="{{ $termIsAdd ? 'addForm' : 'editForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $termIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $termIsAdd ? 'Add New Term' : 'Edit Term' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid3">
                            @foreach($termFields as $field)
                                @php $fieldId = $termPrefix.'_'.$field['key']; @endphp
                                <div class="cm-field">
                                    <label for="{{ $fieldId }}">{{ $field['label'] }} @if(!empty($field['req']))<span>*</span>@endif</label>

                                    @if($field['type'] === 'select')
                                        <select id="{{ $fieldId }}" name="{{ $field['key'] }}" class="cm-select {{ $field['key'] }}">
                                            <option value="">Please Select</option>
                                            @if($field['options'] === 'years')
                                                @foreach($academicYears as $year)
                                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                                @endforeach
                                            @else
                                                @foreach($termTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    @elseif($field['type'] === 'date')
                                        {{-- `datepicker` + the data attributes are what Litepicker
                                             binds to on load (resources/js/datepicker.js). --}}
                                        <input id="{{ $fieldId }}" name="{{ $field['key'] }}" type="text"
                                               class="cm-input datepicker {{ $field['key'] }}"
                                               data-format="DD-MM-YYYY" data-single-mode="true" placeholder="DD-MM-YYYY">
                                    @elseif($field['type'] === 'time')
                                        {{-- `theTimeField` gets an HH:MM IMask in course-term-declaration.js. --}}
                                        <input id="{{ $fieldId }}" name="{{ $field['key'] }}" type="text"
                                               class="cm-input theTimeField {{ $field['key'] }}" placeholder="HH:MM">
                                    @else
                                        <input id="{{ $fieldId }}" name="{{ $field['key'] }}"
                                               type="{{ $field['type'] === 'number' ? 'number' : 'text' }}"
                                               @isset($field['max']) max="{{ $field['max'] }}" @endisset
                                               class="cm-input {{ $field['key'] }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}">
                                    @endif

                                    <div class="acc__input-error error-{{ $field['key'] }}"></div>
                                </div>
                            @endforeach
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $termIsAdd ? 'save' : 'update' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $termIsAdd ? 'Save' : 'Update' }}
                            </button>
                            @unless($termIsAdd)
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
    @vite('resources/js/course-term-declaration.js')
@endsection
