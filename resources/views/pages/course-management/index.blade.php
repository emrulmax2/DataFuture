@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        $cmPriv = Auth::check() ? (Auth::user()->priv() ?? []) : [];
        $cmCan = fn ($key) => isset($cmPriv[$key]) && $cmPriv[$key] == 1;

        // Icon bodies are static markup owned by this file — nothing here comes
        // from user input, so echoing them raw is safe.
        $cmIcons = [
            'calendar' => '<path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect>',
            'book' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path>',
            'layers' => '<path d="M12 2l9 5-9 5-9-5z"></path><path d="M3 12l9 5 9-5M3 17l9 5 9-5"></path>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"></path>',
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>',
        ];

        // Each tile opens the list it counts. The gates mirror the sidebar, so a
        // tile can never reach a screen the menu would have hidden — when the
        // privilege is missing the card still shows the figure but does not link.
        $cmTiles = [
            ['label' => 'Semesters', 'value' => $semesters, 'icon' => 'calendar', 'route' => 'semester', 'can' => $cmCan('course_and_semesters')],
            ['label' => 'Courses', 'value' => $courses, 'icon' => 'book', 'route' => 'courses', 'can' => $cmCan('course_and_semesters')],
            ['label' => 'Term Declarations', 'value' => $termdecs, 'icon' => 'file', 'route' => 'term-declaration.index', 'can' => $cmCan('terms_and_modules')],
            ['label' => 'Term Module Creations', 'value' => $modcreations, 'icon' => 'layers', 'route' => 'term.module.creation', 'can' => $cmCan('terms_and_modules')],
            ['label' => 'Groups', 'value' => $groups, 'icon' => 'users', 'route' => 'groups', 'can' => $cmCan('plans')],
            ['label' => 'Class Plans', 'value' => $plans, 'icon' => 'grid', 'route' => 'class.plan', 'can' => $cmCan('plans') && $cmCan('plans_list')],
        ];
    @endphp

    <div class="cm-layout">
        @include('pages.course-management.partials.sidebar')

        <div class="cm-layout__content">
            <div class="cm-stats">
                @foreach($cmTiles as $cmTile)
                    @php
                        $cmTileLink = $cmTile['can'] && Route::has($cmTile['route']);
                    @endphp

                    <a href="{{ $cmTileLink ? route($cmTile['route']) : 'javascript:void(0);' }}"
                       class="cm-stat {{ $cmTileLink ? 'cm-stat--link' : 'cm-stat--static' }}"
                       @if(!$cmTileLink) aria-disabled="true" tabindex="-1" @endif>
                        <span class="cm-stat__top">
                            <span class="cm-stat__label">{{ $cmTile['label'] }}</span>
                            <span class="cm-stat__icon" aria-hidden="true">
                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $cmIcons[$cmTile['icon']] !!}</svg>
                            </span>
                        </span>

                        <span class="cm-stat__value cm-serif">{{ number_format($cmTile['value']) }}</span>

                        <span class="cm-stat__foot">
                            @if($cmTileLink)
                                View records
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7M7 7h10v10"></path></svg>
                            @else
                                No access
                            @endif
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endsection
