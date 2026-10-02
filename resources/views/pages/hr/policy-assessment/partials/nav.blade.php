{{--
    Policy Assessments section nav (pill tabs), shown at the top of the content
    on every admin page.
    Usage: @include('pages.hr.policy-assessment.partials.nav', ['active' => 'results'])
    active: results | assignments | policies | roles | categories
--}}
@php
    $paNavActive = $active ?? '';
    $paNavItems = [
        ['key' => 'results', 'route' => 'policy.assessment', 'icon' => 'layout-dashboard', 'label' => 'Overview & Results'],
        ['key' => 'assignments', 'route' => 'policy.assessment.assignment', 'icon' => 'user-check', 'label' => 'Assignments'],
        ['key' => 'policies', 'route' => 'policy.assessment.policy', 'icon' => 'library', 'label' => 'Question Bank'],
        ['key' => 'roles', 'route' => 'policy.assessment.role', 'icon' => 'briefcase', 'label' => 'Roles'],
        ['key' => 'categories', 'route' => 'policy.assessment.category', 'icon' => 'folder-tree', 'label' => 'Categories'],
    ];
@endphp
<nav class="pa-section-nav" aria-label="Policy Assessments sections">
    @foreach($paNavItems as $paNavItem)
        <a href="{{ route($paNavItem['route']) }}"
           class="pa-section-nav__link {{ $paNavActive === $paNavItem['key'] ? 'is-active' : '' }}"
           @if($paNavActive === $paNavItem['key']) aria-current="page" @endif>
            <i data-lucide="{{ $paNavItem['icon'] }}"></i>
            <span>{{ $paNavItem['label'] }}</span>
        </a>
    @endforeach
</nav>
