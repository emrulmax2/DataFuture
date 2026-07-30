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
                        <span class="cm-stephead__sub">Select modules</span>
                    </div>
                    <h2 class="cm-stephead__title cm-serif">Choose course modules</h2>
                </div>

                <form method="POST" action="#" id="termModuleCreationFormStp1">
                    <div class="cm-picker">
                        {{-- Chosen modules. Rows are built by JS from whichever
                             options are on, and each carries the `moduleid[]`
                             input the store endpoint reads. --}}
                        <div class="cm-picker__col">
                            <div class="cm-picker__head">
                                <span class="cm-picker__label">Selected Modules</span>
                                <span class="cm-picker__count" data-cm-picked-count>0 selected</span>
                            </div>

                            <div class="cm-picker__empty" data-cm-picked-empty>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                                Modules not selected yet
                            </div>

                            <div class="cm-picker__list" data-cm-picked-list></div>
                        </div>

                        {{-- Available modules. Every module stays listed; the ones
                             already chosen simply read as selected, which is what
                             makes a second click deselect them. --}}
                        <div class="cm-picker__col">
                            <div class="cm-picker__head">
                                <span class="cm-picker__label">Available Course Modules</span>
                            </div>

                            <div class="cm-picker__list" data-cm-option-list>
                                @if(!empty($modules))
                                    @foreach($modules as $mod)
                                        <button type="button"
                                                class="cm-option"
                                                data-cm-option
                                                data-modid="{{ $mod->id }}"
                                                data-modname="{{ $mod->name }}"
                                                aria-pressed="false">
                                            <span class="cm-option__name">{{ $mod->name }}</span>
                                            <span class="cm-option__mark">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                                            </span>
                                        </button>
                                    @endforeach
                                @endif
                            </div>

                            <div class="cm-picker__empty" data-cm-option-empty @if(!empty($modules) && count($modules)) hidden @endif style="margin-top:9px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                                No modules available for this course
                            </div>
                        </div>
                    </div>

                    <div class="cm-modal__foot">
                        <a href="{{ route('term.module.creation') }}" class="cm-btn cm-btn--keep">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 19l-7-7 7-7"></path></svg>
                            Cancel &amp; Back
                        </a>
                        <button type="submit" id="saveandcontinue" class="cm-btn cm-btn--save" disabled>
                            @include('pages.course-management.partials.save-glyphs')
                            Save &amp; Continue
                        </button>
                        <input type="hidden" name="instanceTermId" value="{{ $instanceTermId }}">
                        <input type="hidden" name="courseId" value="{{ $courseId }}">
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-term-module-add.js')
@endsection
