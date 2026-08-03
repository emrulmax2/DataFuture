@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            @include('pages.course-management.partials.detail-header', [
                'detailBadge' => 'Module',
                'detailSubtitle' => $module->course->name ?? '',
                'detailTitle' => $module->name,
                'detailMeta' => [
                    ['label' => 'Level', 'value' => $module->level->name ?? '', 'icon' => 'layers'],
                    ['label' => 'Code', 'value' => $module->code, 'icon' => 'shield'],
                    ['label' => 'Status', 'value' => ucfirst($module->status), 'icon' => 'check'],
                    ['label' => 'Credit Value', 'value' => $module->credit_value, 'icon' => 'file'],
                    ['label' => 'Unit Value', 'value' => $module->unit_value, 'icon' => 'grid'],
                    ['label' => 'Active Status', 'value' => $module->active == 1 ? 'Active' : 'Inactive', 'icon' => 'pound'],
                ],
                'detailTabs' => [
                    ['key' => 'assessments', 'label' => 'Assesments', 'icon' => 'layers'],
                    ['key' => 'datafuture', 'label' => 'Datafuture', 'icon' => 'database'],
                ],
            ])

            {{-- ---------------------------------------------------------- --}}
            {{-- Assesments                                                  --}}
            {{-- ---------------------------------------------------------- --}}
            <div class="cm-card cm-tablecard" data-cm-tabpanel="assessments">
                <div class="cm-tablecard__head">
                    <div class="cm-tablecard__titles">
                        <h2 class="cm-tablecard__title cm-serif">Assesments</h2>
                        <span class="cm-tablecard__count" data-cm-count></span>
                    </div>
                    <button data-tw-toggle="modal" data-tw-target="#moduleAssesmentAddModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Assesment
                    </button>
                </div>

                @include('pages.course-management.partials.list-toolbar', [
                    'toolbarSearchLabel' => 'Search assesments',
                ])

                <div class="cm-tabulator-wrap">
                    <div id="moduleAssesmentDataTable" data-moduleid="{{ $module->id }}" class="cm-tabulator"></div>
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
                    <button data-tw-toggle="modal" data-tw-target="#moduleDataFutureAddModal" type="button" class="cm-btn cm-btn--pill">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                        Add Datafuture Field
                    </button>
                </div>

                @include('pages.course-management.partials.list-toolbar', [
                    'toolbarSearchLabel' => 'Search datafuture fields',
                ])

                <div class="cm-tabulator-wrap">
                    <div id="moduleDatafutureDataTable" data-moduleid="{{ $module->id }}" class="cm-tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- ---------------------------------------------------------------- --}}
    {{-- Assesment add / edit                                              --}}
    {{-- ---------------------------------------------------------------- --}}
    @foreach(['add', 'edit'] as $asMode)
        @php
            $asIsAdd = $asMode === 'add';
            $asPrefix = $asIsAdd ? 'as_add' : 'as_edit';
        @endphp

        <div id="{{ $asIsAdd ? 'moduleAssesmentAddModal' : 'moduleAssesmentEditModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog cm-modal__dialog--md">
                <form method="POST" action="#" id="{{ $asIsAdd ? 'moduleAssesmentAddForm' : 'moduleAssesmentEditForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $asIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $asIsAdd ? 'Add Assesment' : 'Update Assesment' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body">
                            <div class="cm-field">
                                <label for="{{ $asPrefix }}_assessment_type_id">Assesment <span>*</span></label>
                                <select id="{{ $asPrefix }}_assessment_type_id" name="assessment_type_id" class="cm-select assessment_type_id">
                                    <option value="">Please Select</option>
                                    @if(!empty($assementTypes))
                                        @foreach($assementTypes as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} - {{ $t->code }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <div class="acc__input-error error-assessment_type_id"></div>
                            </div>

                            {{-- The endpoint expects `is_result_segment` as a flag
                                 alongside `grade[]`; it was a hidden 1 before and
                                 stays one so the payload is unchanged. --}}
                            <input type="hidden" name="is_result_segment" value="1">

                            <div class="cm-checklist" data-cm-checklist>
                                <div class="cm-checklist__head">
                                    <span>Result set from result segment</span>
                                    <button type="button" class="cm-checklist__toggle" data-cm-checklist-toggle>Clear all</button>
                                </div>
                                <div class="cm-checklist__grid">
                                    @if(!empty($gradesList))
                                        @foreach($gradesList as $grade)
                                            <label class="cm-check">
                                                <input class="cm-check__input" type="checkbox" name="grade[]" value="{{ $grade->id }}" @if($asIsAdd) checked @endif>
                                                <span class="cm-check__box">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                                </span>
                                                <span class="cm-check__label">{{ $grade->name }}</span>
                                            </label>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="acc__input-error error-grade"></div>
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $asIsAdd ? 'saveModuleAssesment' : 'updateModuleAssesment' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $asIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_module_id" value="{{ $module->id }}">
                            @unless($asIsAdd)
                                <input type="hidden" name="id" value="0">
                                {{-- Preserved across an edit so the toggle in the
                                     list is not reset by a modal save. --}}
                                <input type="hidden" name="view_in_plan" value="0">
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
    @foreach(['add', 'edit'] as $mdfMode)
        @php
            $mdfIsAdd = $mdfMode === 'add';
            $mdfPrefix = $mdfIsAdd ? 'mdf_add' : 'mdf_edit';
        @endphp

        <div id="{{ $mdfIsAdd ? 'moduleDataFutureAddModal' : 'moduleDataFutureEditModal' }}" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog cm-modal__dialog">
                <form method="POST" action="#" id="{{ $mdfIsAdd ? 'moduleDataFutureAddForm' : 'moduleDataFutureEditForm' }}" enctype="multipart/form-data" autocomplete="off">
                    <div class="modal-content cm-modal">
                        <div class="cm-modal__head">
                            <div>
                                <div class="cm-modal__eyebrow"><span>{{ $mdfIsAdd ? 'New record' : 'Edit record' }}</span></div>
                                <h2 class="cm-modal__title cm-serif">{{ $mdfIsAdd ? 'Add Datafuture Field' : 'Edit Datafuture Field' }}</h2>
                            </div>
                            <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <div class="cm-modal__body">
                            <div class="cm-field">
                                <label for="{{ $mdfPrefix }}_datafuture_field_id">Field <span>*</span></label>
                                <select id="{{ $mdfPrefix }}_datafuture_field_id" name="datafuture_field_id" class="cm-select datafuture_field_id">
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
                                <label for="{{ $mdfPrefix }}_field_value">Field Value</label>
                                <input id="{{ $mdfPrefix }}_field_value" type="text" name="field_value" class="cm-input field_value">
                                <div class="acc__input-error error-field_value"></div>
                            </div>
                        </div>

                        <div class="cm-modal__foot">
                            <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                Cancel
                            </button>
                            <button type="submit" id="{{ $mdfIsAdd ? 'saveModuleDF' : 'updateModuleDF' }}" class="cm-btn cm-btn--save">
                                @include('pages.course-management.partials.save-glyphs')
                                {{ $mdfIsAdd ? 'Save' : 'Update' }}
                            </button>
                            <input type="hidden" name="course_module_id" value="{{ $module->id }}">
                            @unless($mdfIsAdd)
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
    @vite('resources/js/course-module-detail.js')
@endsection
