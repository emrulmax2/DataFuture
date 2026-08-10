@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @include('pages.course-management.partials.detail-header', [
                'detailBadge' => 'Course',
                'detailSubtitle' => $course->body->name ?? '',
                'detailTitle' => $course->name,
                'detailMeta' => [
                    ['label' => 'Degree Offered', 'value' => $course->degree_offered, 'icon' => 'shield'],
                    ['label' => 'Pre Qualification', 'value' => $course->pre_qualification, 'icon' => 'layers'],
                    ['label' => 'Tution Source', 'value' => $course->fee->name ?? '', 'icon' => 'file'],
                ],
                'detailTabs' => [
                    ['key' => 'modules', 'label' => 'Course Modules', 'icon' => 'layers'],
                    ['key' => 'datafuture', 'label' => 'Datafuture', 'icon' => 'database'],
                    ['key' => 'monitors', 'label' => 'Monitor Emails', 'icon' => 'mail'],
                ],
            ])

            {{-- ---------------------------------------------------------- --}}
            {{-- Course Modules                                              --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="cm-card cm-tablecard" data-cm-tabpanel="modules">
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Course Modules</h2>
                        <span class="cm-tablecard__count" data-cm-count></span>
                    </div>
                    <div class="cm-tablecard__actions">
                        <a href="{{ route('course.module.export', $course->id) }}" class="cm-pillbtn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><path d="M7 10l5 5 5-5M12 15V3"></path></svg>
                            Download Excel
                        </a>
                        <button data-tw-toggle="modal" data-tw-target="#courseModuleAddModal" type="button" class="cm-btn cm-btn--pill">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                            Add Module
                        </button>
                    </div>
                </div>

                @include('pages.course-management.partials.list-toolbar', [
                    'toolbarSearchLabel' => 'Search course modules',
                    'toolbarChips' => [
                        ['value' => '1', 'label' => 'Active'],
                        ['value' => '0', 'label' => 'Inactive'],
                        ['value' => '2', 'label' => 'Archived'],
                    ],
                ])

                <div class="cm-tabulator-wrap">
                    <div id="courseModuleTableId" data-courseid="{{ $course->id }}" class="cm-tabulator"></div>
                </div>
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
                    <button data-tw-toggle="modal" data-tw-target="#courseDataFutureAddModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Datafuture Field
                    </button>
                </div>

                @include('pages.course-management.partials.list-toolbar', [
                    'toolbarSearchLabel' => 'Search datafuture fields',
                ])

                <div class="cm-tabulator-wrap">
                    <div id="courseDataFutureTableId" data-courseid="{{ $course->id }}" class="cm-tabulator"></div>
                </div>
            </div>

            {{-- ---------------------------------------------------------- --}}
            {{-- Monitor Emails                                              --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="cm-card cm-tablecard" data-cm-tabpanel="monitors" hidden>
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Monitory Accounts</h2>
                        <span class="cm-tablecard__count" data-cm-count></span>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#courseMonitorAddModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Monitory Account
                    </button>
                </div>

                @include('pages.course-management.partials.list-toolbar', [
                    'toolbarSearchLabel' => 'Search monitory accounts',
                ])

                <div class="cm-tabulator-wrap">
                    <div id="courseMonitorTableId" data-courseid="{{ $course->id }}" class="cm-tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Module add / edit                                                 --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $modMode)
        @php
            $modIsAdd = $modMode === 'add';
            $modPrefix = $modIsAdd ? 'md_add' : 'md_edit';
        @endphp

        <div id="{{ $modIsAdd ? 'courseModuleAddModal' : 'courseModuleEditModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
                <form method="POST" action="#" id="{{ $modIsAdd ? 'courseModuleAddForm' : 'courseModuleEditForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $modIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $modIsAdd ? 'Add Module' : 'Update Module' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body cm-modal__body--grid2">
                            <div class="cm-field cm-field--span2">
                                <label for="{{ $modPrefix }}_name">Module Name <span>*</span></label>
                                <input id="{{ $modPrefix }}_name" type="text" name="name" class="cm-input name" placeholder="e.g. Business Strategy">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $modPrefix }}_module_level_id">Module Level</label>
                                <select id="{{ $modPrefix }}_module_level_id" name="module_level_id" class="cm-select module_level_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($levels))
                                        @foreach($levels as $lvl)
                                            <option value="{{ $lvl->id }}">{{ $lvl->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-module_level_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $modPrefix }}_code">Module Code <span>*</span></label>
                                <input id="{{ $modPrefix }}_code" type="text" name="code" class="cm-input code" placeholder="e.g. R/650/2920">
                                <div class="acc__input-error error-code"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $modPrefix }}_credit_value">Credit Value <span>*</span></label>
                                <input id="{{ $modPrefix }}_credit_value" type="text" name="credit_value" class="cm-input credit_value" placeholder="e.g. 15">
                                <div class="acc__input-error error-credit_value"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $modPrefix }}_unit_value">Unit Value <span>*</span></label>
                                <input id="{{ $modPrefix }}_unit_value" type="text" name="unit_value" class="cm-input unit_value" placeholder="e.g. 5">
                                <div class="acc__input-error error-unit_value"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $modPrefix }}_status">Status <span>*</span></label>
                                <select id="{{ $modPrefix }}_status" name="status" class="cm-select status">
                                    <option value="">Please Select</option>
                                    <option value="core">Core</option>
                                    <option value="specialist">Specialist</option>
                                    <option value="optional">Optional</option>
                                </select>
                                <div class="acc__input-error error-status"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $modPrefix }}_class_type">Class Type <span>*</span></label>
                                <select id="{{ $modPrefix }}_class_type" name="class_type" class="cm-select class_type">
                                    <option value="">Please Select</option>
                                    <option value="Theory">Theory</option>
                                    <option value="Practical">Practical</option>
                                    <option value="Tutorial">Tutorial</option>
                                    <option value="Seminar">Seminar</option>
                                </select>
                                <div class="acc__input-error error-class_type"></div>
                            </div>

                            <label class="cm-switchfield cm-field--span2">
                                <span>Active Status</span>
                                <input id="{{ $modPrefix }}_active" class="cm-switchcard__input" name="active" value="1" type="checkbox" @if($modIsAdd) checked @endif>
                                <span class="cm-switchcard">
                                    <span class="cm-switchcard__tile">
                                        <svg data-cm-switch-on width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                        <svg data-cm-switch-off width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                    </span>
                                    <span class="cm-switchcard__copy">
                                        <span class="cm-switchcard__title" data-on="Active" data-off="Inactive"></span>
                                        <span class="cm-switchcard__desc" data-on="Available for term module creation" data-off="Hidden from term module creation"></span>
                                    </span>
                                </span>
                            </label>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $modIsAdd ? 'saveModule' : 'updateModule' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $modIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            @unless($modIsAdd)
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
            $dfPrefix = $dfIsAdd ? 'df_add' : 'df_edit';
        @endphp

        <div id="{{ $dfIsAdd ? 'courseDataFutureAddModal' : 'courseDataFutureEditModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog">
                <form method="POST" action="#" id="{{ $dfIsAdd ? 'courseDataFutureAddForm' : 'courseDataFutureEditForm' }}" enctype="multipart/form-data" autocomplete="off">
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
                                <label for="{{ $dfPrefix }}_datafuture_field_id">Field <span>*</span></label>
                                <select id="{{ $dfPrefix }}_datafuture_field_id" name="datafuture_field_id" class="cm-select cm-tom-select datafuture_field_id" data-placeholder="Search field">
                                    <option value="">Please Select</option>
                                    @if(!empty($df_fields))
                                        @foreach($df_fields as $fld)
                                            <option data-type="{{ $fld->type }}" value="{{ $fld->id }}">{{ $fld->name }} ({{ strtoupper($fld->type) }}){{ (isset($fld->category->name) && !empty($fld->category->name) ? ' - '.$fld->category->name : '') }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-datafuture_field_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $dfPrefix }}_parent_id">Parent Field</label>
                                <select id="{{ $dfPrefix }}_parent_id" name="parent_id" class="cm-select cm-tom-select parent_id" data-df-parent-select data-placeholder="Search parent field">
                                    <option value="">No Parent</option>
                                    @if(!empty($df_parent_fields))
                                        @foreach($df_parent_fields as $parentField)
                                            @php
                                                $parentLabel = '#'.$parentField->id.' '.(isset($parentField->field->name) && !empty($parentField->field->name) ? $parentField->field->name : 'ID: '.$parentField->datafuture_field_id);
                                                $parentLabel .= (!empty($parentField->field_value) ? ' - '.$parentField->field_value : '');
                                                $parentLabel .= (isset($parentField->field->category->name) && !empty($parentField->field->category->name) ? ' ('.$parentField->field->category->name.')' : '');
                                            @endphp
                                            <option value="{{ $parentField->id }}">{{ $parentLabel }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-parent_id"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $dfPrefix }}_field_value">Field Value</label>
                                <input id="{{ $dfPrefix }}_field_value" type="text" name="field_value" class="cm-input field_value">
                                <div class="acc__input-error error-field_value"></div>
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $dfIsAdd ? 'saveBaseDF' : 'updateBaseDF' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $dfIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            @unless($dfIsAdd)
                                <input type="hidden" name="id" value="0">
                            @endunless
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- ---------------------------------------------------------------- --}}
    {{-- Monitory account add / edit                                       --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $mnMode)
        @php
            $mnIsAdd = $mnMode === 'add';
            $mnPrefix = $mnIsAdd ? 'mn_add' : 'mn_edit';
        @endphp

        <div id="{{ $mnIsAdd ? 'courseMonitorAddModal' : 'courseMonitorEditModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog">
                <form method="POST" action="#" id="{{ $mnIsAdd ? 'courseMonitorAddForm' : 'courseMonitorEditForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $mnIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $mnIsAdd ? 'Add Monitory Account' : 'Edit Monitory Account' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body">
                            <div class="cm-field">
                                <label for="{{ $mnPrefix }}_name">Name</label>
                                <input id="{{ $mnPrefix }}_name" type="text" name="name" class="cm-input name">
                                <div class="acc__input-error error-name"></div>
                            </div>

                            <div class="cm-field">
                                <label for="{{ $mnPrefix }}_email">Email <span>*</span></label>
                                <input id="{{ $mnPrefix }}_email" type="email" name="email" class="cm-input email" placeholder="name@lcc.ac.uk">
                                <div class="acc__input-error error-email"></div>
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $mnIsAdd ? 'saveBaseMN' : 'updateBaseMN' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $mnIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_id" value="{{ $course->id }}">
                            @unless($mnIsAdd)
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
    @vite('resources/js/course-course-detail.js')
@endsection
