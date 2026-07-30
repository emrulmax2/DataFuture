{{--
    Course Management sidebar (redesigned).

    Data-driven so the remaining screens can adopt it as they are converted:
    each group declares the privilege that reveals it and each link declares
    the route names that should light it up. Privilege gating is a like-for-
    like copy of the legacy `pages/course-management/sidebar.blade.php`, which
    stays in place for the pages still on the old layout.
--}}
@php
    $cmPriv = Auth::check() ? (Auth::user()->priv() ?? []) : [];
    $cmCan = fn ($key) => isset($cmPriv[$key]) && $cmPriv[$key] == 1;
    $cmRoute = Route::currentRouteName() ?? '';

    $cmNav = [
        [
            'label' => 'Courses & Semesters',
            'priv' => 'course_and_semesters',
            'links' => [
                ['label' => 'Semesters', 'route' => 'semester', 'match' => ['semester']],
                ['label' => 'Module Levels', 'route' => 'modulelevels', 'match' => ['modulelevels']],
                ['label' => 'Courses', 'route' => 'courses', 'match' => ['courses', 'courses.show', 'course.module.show']],
            ],
        ],
        [
            'label' => 'Course and Term Creation',
            'priv' => 'terms_and_modules',
            'links' => [
                ['label' => 'Term Declarations', 'route' => 'term-declaration.index', 'match' => ['term-declaration.index']],
                ['label' => 'Course Creations', 'route' => 'course.creation', 'match' => ['course.creation', 'course.creation.show']],
                ['label' => 'Term Module Creations', 'route' => 'term.module.creation', 'match' => ['term.module.creation', 'term.module.creation.add', 'term.module.creation.show', 'term.module.creation.module.details']],
            ],
        ],
        [
            'label' => 'Plans',
            'priv' => 'plans',
            'links' => [
                ['label' => 'Plans', 'route' => 'class.plan', 'priv' => 'plans_list', 'match' => ['class.plan', 'class.plan.add', 'class.plan.builder', 'plan.dates']],
                ['label' => 'Plan Tree View', 'route' => 'plans.tree', 'priv' => 'plans_tree', 'match' => ['plans.tree', 'bulk.communication']],
                ['label' => 'Groups', 'route' => 'groups', 'match' => ['groups']],
            ],
        ],
    ];

    // Resolve visibility and active state once, so the markup below stays flat.
    $cmGroups = [];
    foreach ($cmNav as $cmGroup) {
        if (!$cmCan($cmGroup['priv'])) {
            continue;
        }

        $cmLinks = [];
        foreach ($cmGroup['links'] as $cmLink) {
            if (isset($cmLink['priv']) && !$cmCan($cmLink['priv'])) {
                continue;
            }

            $cmLinks[] = [
                'label' => $cmLink['label'],
                'href' => Route::has($cmLink['route']) ? route($cmLink['route']) : 'javascript:void(0);',
                'active' => in_array($cmRoute, $cmLink['match'], true),
            ];
        }

        if (empty($cmLinks)) {
            continue;
        }

        $cmGroups[] = [
            'label' => $cmGroup['label'],
            'key' => $cmGroup['priv'],
            'links' => $cmLinks,
            'active' => collect($cmLinks)->contains('active', true),
        ];
    }
@endphp

<aside class="cm-layout__side">
    <nav class="cm-card cm-sidenav" aria-label="Course management sections">
        @forelse($cmGroups as $cmIndex => $cmGroup)
            {{-- Ships expanded; the script collapses it again only when this
                 user explicitly closed it before (see course-management-redesign.js). --}}
            <div class="cm-sidenav__group is-open"
                 data-cm-sidenav-group
                 data-cm-sidenav-key="cm.sidenav.{{ $cmGroup['key'] }}"
                 @if($cmGroup['active']) data-cm-sidenav-active @endif>
                <button type="button" class="cm-sidenav__head {{ $cmGroup['active'] ? 'cm-sidenav__head--active' : '' }}" data-cm-sidenav-toggle aria-expanded="true">
                    <span class="cm-sidenav__num">{{ str_pad($cmIndex + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    <span class="cm-sidenav__label">{{ $cmGroup['label'] }}</span>
                    <svg class="cm-sidenav__caret" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
                </button>

                <div class="cm-sidenav__list">
                    @foreach($cmGroup['links'] as $cmLink)
                        <a href="{{ $cmLink['href'] }}" class="cm-sidenav__link {{ $cmLink['active'] ? 'cm-sidenav__link--active' : '' }}" @if($cmLink['active']) aria-current="page" @endif>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8l4 4-4 4M2 12h20"></path></svg>
                            <span>{{ $cmLink['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="cm-alert cm-alert--danger" role="alert">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                <span><strong>Oops!</strong> You do not have permission to access this menu.</span>
            </div>
        @endforelse
    </nav>
</aside>
