{{--
    The two dialogs this screen owns. The shared success / confirm pair comes
    from `partials/list-dialogs`.

    Field names inside the re-assign form are exactly what
    `assigns.re.assign.students.new.group` reads, so only the chrome is new.
--}}

{{-- ---------------------------------------------------------------- --}}
{{-- The modules one student sits on                                   --}}
{{-- ---------------------------------------------------------------- --}}
<div id="showAllModulesModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--sm">
        <div class="modal-content cm-modal">
            <div class="cm-modal__head">
                <div>
                    <div class="cm-modal__eyebrow"><span data-cm-modules-eyebrow>Student</span></div>
                    <h2 class="cm-modal__title cm-serif">Assigned Modules</h2>
                </div>
                <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="cm-modal__body"></div>
            <div class="cm-modal__foot">
                <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--save">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- ---------------------------------------------------------------- --}}
{{-- Re-assign one student to another group                            --}}
{{-- ---------------------------------------------------------------- --}}
<div id="studentReAssignModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog cm-modal__dialog cm-modal__dialog--wide">
        <form method="POST" action="#" id="studentReAssignForm" autocomplete="off">
            <div class="modal-content cm-modal">
                <div class="cm-modal__head">
                    <div>
                        <div class="cm-modal__eyebrow"><span data-cm-reassign-eyebrow>{{ $theGroup->name }}</span></div>
                        <h2 class="cm-modal__title cm-serif">Re-Assign Student to a New Group</h2>
                    </div>
                    <button type="button" data-tw-dismiss="modal" class="cm-modal__close" aria-label="Close">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                </div>

                <div class="cm-modal__body">
                    <div class="cm-readonly">
                        <div class="cm-readonly__item">
                            <span class="cm-readonly__label">Student</span>
                            <span class="cm-readonly__value cm-readonly__value--lead" data-cm-reassign-student>&mdash;</span>
                        </div>
                        <div class="cm-readonly__item">
                            <span class="cm-readonly__label">Current Group</span>
                            <span class="cm-readonly__value">{{ $theGroup->name }}</span>
                        </div>
                        <div class="cm-readonly__item">
                            <span class="cm-readonly__label">Term</span>
                            <span class="cm-readonly__value">{{ $theTermDeclaration->name }}</span>
                        </div>
                    </div>

                    <div class="cm-field" style="margin-top:18px;">
                        <label for="new_group_id">Assign To <span>*</span></label>
                        <select name="new_group_id" class="cm-select" id="new_group_id">
                            <option value="">Please Select</option>
                            @foreach(($otherGroup ?? []) as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <div class="acc__input-error error-new_group_id"></div>
                    </div>

                    {{-- Both columns are ticked module by module: the endpoint
                         pairs them by course module and class type, so a theory
                         class can only ever swap for another theory class. --}}
                    <div class="cm-swap" data-cm-swap hidden>
                        <div class="cm-swap__col">
                            <div class="cm-swap__head">
                                <span data-cm-oldgroup></span> Group Modules
                                <em>Tick to remove</em>
                            </div>
                            <div class="cm-swap__body" data-cm-oldmods></div>
                        </div>
                        <div class="cm-swap__arrow" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                        </div>
                        <div class="cm-swap__col">
                            <div class="cm-swap__head">
                                <span data-cm-newgroup></span> Group Modules
                                <em>Tick to add</em>
                            </div>
                            <div class="cm-swap__body" data-cm-newmods></div>
                        </div>
                    </div>
                </div>

                <div class="cm-modal__foot">
                    <button type="button" data-tw-dismiss="modal" class="cm-btn cm-btn--cancel">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                        Cancel
                    </button>
                    <button disabled type="submit" id="reAssignStdBtn" class="cm-btn cm-btn--save">
                        @include('pages.course-management.partials.save-glyphs')
                        Save
                    </button>

                    <input type="hidden" name="student_id" value="0"/>
                    <input type="hidden" name="academic_year_id" value="{{ $theAcademicYear->id }}"/>
                    <input type="hidden" name="term_declaration_id" value="{{ $theTermDeclaration->id }}"/>
                    <input type="hidden" name="course_id" value="{{ $theCourse->id }}"/>
                    <input type="hidden" name="group_id" value="{{ $theGroup->id }}"/>
                    <input type="hidden" name="assigned_module_ids" value="{{ (!empty($selectedModuleIds) ? implode(',', $selectedModuleIds) : '') }}"/>
                </div>
            </div>
        </form>
    </div>
</div>
