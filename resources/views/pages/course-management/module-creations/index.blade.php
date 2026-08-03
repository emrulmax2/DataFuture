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
                <div class="cm-card cm-finder">
                    <div class="cm-finder__head">
                        <h2 class="cm-tablecard__title cm-serif">Term Modules</h2>
                        <p class="cm-finder__hint">Pick an academic year, term and course to find or create its module set.</p>
                    </div>

                    {{-- Cascading: each select is revealed and populated by the one
                         before it, so an invalid combination cannot be chosen.
                         Wired in js/course-term-module.js. --}}
                    <div class="cm-finder__grid">
                        <div class="cm-field">
                            <label for="academic-year">Academic Year <span>*</span></label>
                            <select id="academic-year" name="academic_year_id" class="cm-select">
                                <option value="">Please Select</option>
                                @foreach($academic_years as $year)
                                    <option value="{{ $year->id }}">{{ $year->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="cm-field" data-cm-step="term" hidden>
                            <label for="termDeclarationId">Term Name <span>*</span></label>
                            <select id="termDeclarationId" name="term_declaration_id" class="cm-select">
                                <option value="">Please Select</option>
                            </select>
                        </div>

                        <div class="cm-field" data-cm-step="course" hidden>
                            <label for="course_creation_id">Course <span>*</span></label>
                            <select id="course_creation_id" name="course_creation_id" class="cm-select">
                                <option value="">Please Select</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="cm-card cm-tablecard" data-cm-result hidden>
                    <div class="cm-tablecard__head">
                        <div class="cm-tablecard__titles">
                            <h2 class="cm-tablecard__title cm-serif">Matching term</h2>
                            <span class="cm-tablecard__count" data-cm-count></span>
                        </div>
                        <span class="cm-badge cm-badge--ok" data-cm-assigned hidden>Modules assigned</span>
                    </div>

                    <div class="cm-tabulator-wrap">
                        <div id="termModuleCreationsListTable" class="cm-tabulator"></div>
                    </div>
                </div>
            @else
                <div class="cm-card">
                    <div class="cm-empty">
                        <span class="cm-empty__icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                        </span>
                        <div class="cm-empty__title cm-serif">Permission required</div>
                        <div class="cm-empty__text">You do not have permission to view term module creations. Use the menu on the left to reach a section you can access.</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-term-module.js')
@endsection
