@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            <div class="cm-card">
                <div class="cm-stephead">
                    <div class="cm-stephead__meta">
                        <span class="cm-stephead__pill">Step 1 of 2</span>
                        <span class="cm-stephead__sub">Plan setup</span>
                    </div>
                    <h2 class="cm-stephead__title cm-serif">Choose the term and group</h2>
                </div>

                <form method="POST" action="#" id="classPlanAddForm" autocomplete="off">
                    {{-- Cascading: each select is revealed and populated by the one
                         before it, so an invalid combination cannot be reached.
                         Wired in js/course-plan-add.js. --}}
                    <div class="cm-finder__grid cm-finder__grid--four">
                        <div class="cm-field">
                            <label for="academic-year">
                                Academic Year <span>*</span>
                                @include('pages.course-management.partials.field-spinner')
                            </label>
                            <select id="academic-year" name="academic_year_id" class="cm-select">
                                <option value="">Please Select</option>
                                @foreach($academic_years as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field" data-cm-step="term" hidden>
                            <label for="termDeclarationId">
                                Term Name <span>*</span>
                                @include('pages.course-management.partials.field-spinner')
                            </label>
                            <select id="termDeclarationId" name="term_declaration_id" class="cm-select">
                                <option value="">Please Select</option>
                            </select>
                        </div>

                        <div class="cm-field" data-cm-step="course" hidden>
                            <label for="course_creation_id">
                                Course <span>*</span>
                                @include('pages.course-management.partials.field-spinner')
                            </label>
                            <select id="course_creation_id" name="course_creation_id" class="cm-select">
                                <option value="">Please Select</option>
                            </select>
                        </div>

                        <div class="cm-field" data-cm-step="group" hidden>
                            <label for="group_id">
                                Group <span>*</span>
                                @include('pages.course-management.partials.field-spinner')
                            </label>
                            <select id="group_id" name="group_id" class="cm-select">
                                <option value="">Please Select</option>
                            </select>
                        </div>
                    </div>

                    {{-- These endpoints answer 304 with an empty payload when a
                         combination has nothing behind it, which axios routes to
                         `.catch`. Saying so beats an empty dropdown and silence. --}}
                    <div class="cm-finder__note" data-cm-empty hidden>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                        <span data-cm-empty-text></span>
                    </div>

                    <div class="cm-modal__foot">
                        <a href="{{ route('class.plan') }}" class="cm-btn cm-btn--keep">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                            Cancel
                        </a>
                        <button type="submit" id="submitModulesBtn" class="cm-btn cm-btn--save" disabled>
                            @include('pages.course-management.partials.save-glyphs')
                            Save &amp; Continue
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/course-plan-add.js')
@endsection
