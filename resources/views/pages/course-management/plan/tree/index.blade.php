@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{-- Three columns on this screen: the module menu, the plan tree, then the
         detail. The menu is hidden and shown by the rail button on the tree's
         header, so a deep tree can have the width when it needs it. --}}
    <div class="cm-treelayout" data-cm-treelayout>
        <div class="cm-treelayout__nav">
            @include('pages.course-management.partials.sidebar')
        </div>

        {{-- The tree is lazy: each level is fetched when its parent is opened,
             so only the academic years are rendered here. --}}
        <aside class="cm-card cm-treeside">
            <div class="cm-treeside__head">
                <span class="cm-treeside__title">Plan tree</span>
                <button type="button" class="cm-treeside__rail" data-cm-nav-toggle title="Hide the menu">
                    <svg data-cm-rail-icon width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"></path></svg>
                </button>
            </div>

            <div class="cm-treeside__body">
                @if(!empty($acyers) && $acyers->count() > 0)
                    <ul class="cm-tree">
                        @foreach($acyers as $year)
                            @if(isset($year->terms) && $year->terms->count() > 0)
                                <li class="cm-tree__item">
                                    <div class="cm-tree__line">
                                        <button type="button" data-yearid="{{ $year->id }}" class="cm-tree__row academicYear" data-cm-level="0">
                                            <span class="cm-tree__mark" data-cm-mark>+</span>
                                            <span class="cm-tree__label">{{ $year->name }}</span>
                                            <span class="cm-tree__spin" data-cm-tree-spin hidden></span>
                                        </button>
                                    </div>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                @else
                    <div class="cm-tree__empty">No academic years have class plans yet.</div>
                @endif
            </div>
        </aside>

        <div class="cm-treemain">
            {{-- Replaced wholesale by `plans.tree.get.module` when a group is
                 picked; the notice below is what shows until then. --}}
            <div class="classPlanTreeResultWrap" data-cm-tree-result hidden></div>

            <div class="cm-card cm-treeempty" data-cm-tree-notice>
                <span class="cm-treeempty__icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                </span>
                <div>
                    <div class="cm-treeempty__title">Please select a group to view the details</div>
                    <div class="cm-treeempty__text">Expand an academic year, then a term and course to reach its groups.</div>
                </div>
            </div>
        </div>
    </div>

    @include('pages.course-management.plan.tree.modals')

    @include('pages.course-management.partials.list-dialogs')
@endsection

@section('script')
    @vite('resources/js/course-plan-tree.js')
@endsection
