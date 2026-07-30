@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Assign / Deassign Students.

        Three stacked cards and one transfer grid:

          1. Unassigned Students — a remote Tabulator over `assign.unsignned.list`,
             whose ticked rows can be pushed into the Potential column.
          2. Assigned To — the fixed year / term / course / group this page acts
             on, plus the module ticks that scope every add and remove.
          3. Existing ⟷ Potential — the two lists and the three transfer actions,
             with the search that fills the Potential side beside them.

        No module sidebar here: the design runs the four transfer tracks edge to
        edge and they do not fit beside a 316px nav.

        Every hook is a `cm-` class or a `data-cm-*` attribute. The legacy names
        this screen used (`addRemoveBtns`, `assignStudentsList`, `existThere`,
        `headingItem`, `termModuleBox`, …) are deliberately gone: `datafuture.css`
        still styles them app-wide — it absolutely positions `.addRemoveBtns` and
        repaints `.assignStudentsList li` — and those two-class selectors outrank
        anything single-class we could write here, whatever the load order.
    --}}
    <div class="cm-asg">

        {{-- ---------------------------------------------------------- --}}
        {{-- 1. Unassigned students                                      --}}
        {{-- ---------------------------------------------------------- --}}
        <section class="cm-card">
            <div class="cm-asg__head">
                <div class="cm-asg__heading">
                    <span class="cm-asg__dot" aria-hidden="true"></span>
                    <h2 class="cm-asg__title cm-serif">Unassigned Students</h2>
                </div>
                <div class="cm-asg__count" data-cm-unsigned-count data-total="0"></div>
            </div>

            <div class="cm-asgfilters">
                <div class="cm-field cm-asgfilters__term">
                    <label for="unsigned_term">Term Declaration <span>*</span></label>
                    <select id="unsigned_term" name="unsigned_term" class="cm-select">
                        <option value="">Please Select</option>
                        @foreach($termDeclarations as $term)
                            <option value="{{ $term->id }}">{{ $term->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- `cm-select--chips` is what turns the picked statuses into the
                     design's navy pills; the module's default multi-select chip
                     is a pale grey one. --}}
                <div class="cm-field cm-asgfilters__statuses">
                    <label for="unsigned_statuses">Student Statuses <span>*</span></label>
                    <select id="unsigned_statuses" name="unsigned_statuses[]" class="cm-select cm-select--chips" multiple>
                        @foreach($statuses as $sts)
                            <option {{ (in_array($sts->id, [18, 23, 24, 28, 29]) ? 'selected' : '') }} value="{{ $sts->id }}">{{ $sts->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="cm-asgfilters__actions">
                    <input type="hidden" name="unsigned_course_id" value="{{ $theCourse->id }}" id="unsigned_course_id"/>

                    <button type="button" class="cm-btn cm-btn--asggo" data-cm-unsigned-go>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
                        Go
                    </button>
                    <button type="button" class="cm-btn cm-btn--asgreset" data-cm-unsigned-reset>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"></path></svg>
                        Reset
                    </button>
                    {{-- Revealed by the table once rows are ticked. --}}
                    <button type="button" class="cm-btn cm-btn--asgmove" data-cm-move hidden>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"></path></svg>
                        Move to Potential
                    </button>
                </div>
            </div>

            <div class="cm-tabulator-wrap" data-cm-unsigned-wrap hidden>
                <div id="unsignedStudentList" class="cm-tabulator"></div>
            </div>

            <div class="cm-asgempty" data-cm-unsigned-empty>
                <span class="cm-asgempty__icon">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                </span>
                <div class="cm-asgempty__title">Choose a term and statuses, then press Go</div>
                <div class="cm-asgempty__text">Students with no class plan for that term will be listed here.</div>
            </div>
        </section>

        {{-- ---------------------------------------------------------- --}}
        {{-- 2. Assigned to                                              --}}
        {{-- ---------------------------------------------------------- --}}
        <section class="cm-card">
            <div class="cm-asg__head">
                <div class="cm-asg__heading">
                    <span class="cm-asg__dot" aria-hidden="true"></span>
                    <h2 class="cm-asg__title cm-serif">Assigned To</h2>
                </div>
            </div>

            <div class="cm-asgtarget">
                {{-- Three tracks, in the design's reading order: year, term and
                     course across the top, group and evening below. --}}
                <div class="cm-asgtarget__meta">
                    <div class="cm-meta">
                        <span class="cm-meta__icon" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect></svg>
                        </span>
                        <div class="cm-meta__copy">
                            <div class="cm-meta__label">Academic Year</div>
                            <div class="cm-meta__value">{{ $theAcademicYear->name ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="cm-meta">
                        <span class="cm-meta__icon" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path></svg>
                        </span>
                        <div class="cm-meta__copy">
                            <div class="cm-meta__label">Attendance Term</div>
                            <div class="cm-meta__value">{{ $theTermDeclaration->name ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="cm-meta">
                        <span class="cm-meta__icon" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                        </span>
                        <div class="cm-meta__copy">
                            <div class="cm-meta__label">Course</div>
                            <div class="cm-meta__value">{{ $theCourse->name ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="cm-meta">
                        <span class="cm-meta__icon" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path></svg>
                        </span>
                        <div class="cm-meta__copy">
                            <div class="cm-meta__label">Group</div>
                            <div class="cm-meta__value">{{ $theGroup->name ?? '—' }}</div>
                        </div>
                    </div>

                    <div class="cm-meta">
                        <span class="cm-meta__icon" aria-hidden="true">
                            @if($theGroup->evening_and_weekend == 1)
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 10V2M4.93 10.93l1.41 1.41M2 18h2M20 18h2M19.07 10.93l-1.41 1.41M22 22H2M16 6l-4 4-4-4M16 18a4 4 0 0 0-8 0"></path></svg>
                            @else
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"></path></svg>
                            @endif
                        </span>
                        <div class="cm-meta__copy">
                            <div class="cm-meta__label">Evening &amp; Weekend</div>
                            <div class="cm-meta__value">{{ $theGroup->evening_and_weekend == 1 ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>

                    {{-- Read by every request this page makes. --}}
                    <input type="hidden" id="assignToAcademicYearId" value="{{ $theAcademicYear->id }}"/>
                    <input type="hidden" id="assignToTermDeclarationId" value="{{ $theTermDeclaration->id }}"/>
                    <input type="hidden" id="assignToCourseId" value="{{ $theCourse->id }}"/>
                    <input type="hidden" id="assignToGroupId" value="{{ $theGroup->id }}"/>
                </div>

                {{-- The ticked modules scope every add and remove, so they sit
                     beside the target rather than in a panel of their own. --}}
                <div class="cm-asgmods">
                    <div class="cm-asgmods__head">
                        <span>Modules</span>
                        <button type="button" class="cm-linkbtn" data-cm-mods-toggle>Clear all modules</button>
                    </div>

                    <div class="cm-asgmods__list">
                        {{-- `is-on` mirrors the checkbox onto the row so the
                             whole bar can be tinted; a bare `:checked` cannot
                             reach its own label. The JS keeps it in step. --}}
                        @forelse($selectedModules as $smd)
                            <label class="cm-modrow is-on" for="assignToModuleIds_{{ $smd->id }}">
                                <input checked id="assignToModuleIds_{{ $smd->id }}" class="cm-modrow__input" data-cm-module name="assignToModuleIds[]" type="checkbox" value="{{ $smd->id }}">
                                <span class="cm-modrow__box">
                                    <svg data-cm-on width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                    <svg data-cm-off width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                                </span>
                                <span class="cm-modrow__label">
                                    <span class="cm-modrow__id">{{ $smd->id }}</span> &mdash;
                                    {{ $smd->creations->module_name ?? 'Unknown Module' }}@if(!empty($smd->class_type)) - {{ $smd->class_type }}@endif
                                </span>
                                <span class="cm-modrow__count">({{ isset($smd->assign) ? $smd->assign->count() : 0 }})</span>
                            </label>
                        @empty
                            <div class="cm-asgmods__empty">No class plans exist for this group yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </section>

        {{-- Add / remove results land here and clear themselves. --}}
        <div data-cm-result-wrap hidden></div>

        {{-- ---------------------------------------------------------- --}}
        {{-- 3. Existing ⟷ Potential                                     --}}
        {{-- ---------------------------------------------------------- --}}
        <div class="cm-asggrid" data-cm-transfer>

            {{-- Both columns are a fixed height so they line up whatever they
                 hold; the scroller between head and foot takes the slack. --}}
            <section class="cm-card cm-stulist">
                <div class="cm-stulist__head">
                    <div class="cm-stulist__titles">
                        <h3 class="cm-serif">Existing</h3>
                        <span data-cm-existing-count>{{ $existingStudents['count'] ?? 0 }}</span>
                    </div>
                    <span class="cm-stulist__sel" data-cm-existing-sel></span>
                </div>

                <div class="cm-stulist__scroll">
                    <ul class="cm-stulist__body" data-cm-list="existing">
                        {!! $existingStudents['htm'] ?? '' !!}
                    </ul>
                    {{-- Same empty state the unassigned card uses, centred in
                         the column rather than sat in a card of its own. --}}
                    <div class="cm-asgempty cm-asgempty--inlist" data-cm-existing-empty hidden>
                        <span class="cm-asgempty__icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path></svg>
                        </span>
                        <div class="cm-asgempty__title">No students assigned</div>
                        <div class="cm-asgempty__text">Students on the ticked modules will be listed here.</div>
                    </div>
                </div>

                <div class="cm-stulist__foot">
                    <div class="cm-stufilter">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
                        <input type="search" placeholder="Filter students" data-cm-existing-filter>
                    </div>
                </div>
            </section>

            <div class="cm-asgacts">
                <button type="button" disabled class="cm-asgact cm-asgact--add" data-cm-act="add">
                    <svg class="cm-spinner" style="display:none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>
                    <svg data-cm-btn-icon width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>
                    <span>Add</span>
                </button>
                <button type="button" disabled class="cm-asgact cm-asgact--remove" data-cm-act="remove">
                    <span>Remove</span>
                    <svg class="cm-spinner" style="display:none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>
                    <svg data-cm-btn-icon width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>
                </button>
                <button type="button" disabled class="cm-asgact cm-asgact--move" data-cm-act="reassign">
                    <span>Re-Assign</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"></path></svg>
                </button>
            </div>

            <section class="cm-card cm-stulist">
                <div class="cm-stulist__head">
                    <div class="cm-stulist__titles">
                        <h3 class="cm-serif">Potential</h3>
                        <span data-cm-potential-count>0</span>
                    </div>
                    <button type="button" class="cm-linkbtn" data-cm-select-all hidden>Select all</button>
                </div>

                <div class="cm-stulist__scroll">
                    <ul class="cm-stulist__body" data-cm-list="potential"></ul>
                    <div class="cm-asgempty cm-asgempty--inlist" data-cm-potential-empty>
                        <span class="cm-asgempty__icon">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"></circle><path d="M21 21l-4.3-4.3"></path></svg>
                        </span>
                        <div class="cm-asgempty__title">No students to add yet</div>
                        <div class="cm-asgempty__text">Search for a student, pick a term and group, or move rows down from the unassigned list.</div>
                    </div>
                </div>
            </section>

            <div class="cm-asgside">
                <section class="cm-card">
                    <div class="cm-asgside__head">
                        <h3 class="cm-serif">Search</h3>
                    </div>

                    <div class="cm-asgside__body">
                        <div class="cm-field">
                            <label for="potentialStudentSearch">Student Search</label>
                            <input type="search" id="potentialStudentSearch" class="cm-input" placeholder="Registration no.">
                        </div>

                        <div class="cm-field">
                            <label for="potentialTermDeclaration">
                                Term Declaration
                                @include('pages.course-management.partials.field-spinner')
                            </label>
                            <select id="potentialTermDeclaration" class="cm-select" name="potentialTermDeclaration">
                                <option value="">Please Select</option>
                                @foreach($termDeclarations as $trm)
                                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field" data-cm-field="groups" hidden>
                            <label for="potentialGroups">
                                Groups
                                @include('pages.course-management.partials.field-spinner')
                            </label>
                            <select id="potentialGroups" class="cm-select" name="potentialGroups">
                                <option value="">Please Select</option>
                            </select>
                        </div>

                        <div class="cm-field" data-cm-field="modules" hidden>
                            <label for="potentialModules">
                                Modules
                                @include('pages.course-management.partials.field-spinner')
                            </label>
                            <select id="potentialModules" class="cm-select" name="potentialModules">
                                <option value="">Please Select</option>
                            </select>
                        </div>
                    </div>
                </section>

                {{-- Filled by `assign.get.module.student.list` — the class plans
                     behind whichever group the search landed on. --}}
                <section class="cm-card" data-cm-sidemods hidden>
                    <div class="cm-asgside__head cm-asgside__head--split">
                        <div class="cm-asg__heading">
                            <span class="cm-asg__dot" aria-hidden="true"></span>
                            <h3 class="cm-serif">Modules</h3>
                        </div>
                        <span class="cm-badge" data-cm-sidemod-count>0</span>
                    </div>
                    <div class="cm-asgside__mods" data-cm-sidemods-body></div>
                </section>
            </div>
        </div>
    </div>

    @include('pages.course-management.assign.modals')

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-assign.js')
@endsection
