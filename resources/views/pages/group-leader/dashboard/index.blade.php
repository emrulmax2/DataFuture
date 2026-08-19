@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('body_class', 'gl-dashboard-body')

@section('styles')
    @vite('resources/css/group-leader-dashboard.css')
@endsection

@section('subcontent')
    {{-- Level 1: every group this person leads. Picking one opens its own
         page rather than swapping panels, so a group dashboard is a URL that
         can be bookmarked and shared. --}}
    <div class="gl-root" id="glGroups">
        <div class="gl-shell">
            <div class="gl-shell__inner">
                <div class="gl-shell__top">
                    <div>
                        <div class="gl-shell__eyebrow">Group Leader</div>
                        <div class="gl-shell__name">{{ $leaderName }}</div>
                    </div>

                    @php
                        // One label shape for the button and the menu rows, or the
                        // button would read "2026 May" while its own row reads
                        // "2026 May · Summer Term".
                        $glTermLabel = fn ($term) => $term->name
                            .(($term->termType->name ?? '') ? ' · '.$term->termType->name : '');

                        $glSelectedTerm = $selected === 'all' ? null : $terms->firstWhere('id', $selected);
                        $glCurrentTerm = $selected === 'all'
                            ? 'All terms'
                            : ($glSelectedTerm ? $glTermLabel($glSelectedTerm) : null);
                    @endphp

                    <div class="gl-term">
                        <div class="gl-shell__eyebrow">Term</div>

                        {{-- The app's own dropdown component (tw-starter), not a
                             native select: it takes the panel styling below and
                             matches every other term picker in the portal. --}}
                        <div class="dropdown">
                            <button type="button" id="glTermDropdown" class="dropdown-toggle gl-term-button"
                                    data-tw-toggle="dropdown" aria-expanded="false">
                                <i data-lucide="calendar-days" class="w-4 h-4"></i>
                                <span data-gl-term-current>{{ $glCurrentTerm ?: 'Select term' }}</span>
                                <i data-lucide="chevron-down" class="gl-term-chev w-4 h-4"></i>
                            </button>

                            <div class="dropdown-menu">
                                <ul class="dropdown-content gl-dropdown-panel">
                                    <li class="gl-dropdown-heading">Select period</li>

                                    <li>
                                        <a href="javascript:void(0);" data-gl-term-item="all" data-gl-term-name="All terms"
                                           class="dropdown-item {{ $selected === 'all' ? 'is-active' : '' }}">
                                            <span>All terms</span>
                                            <i data-lucide="check" class="gl-dropdown-check w-4 h-4"></i>
                                        </a>
                                    </li>

                                    @foreach($terms as $term)
                                        @php $glLabel = $glTermLabel($term); @endphp
                                        <li>
                                            <a href="javascript:void(0);" data-gl-term-item="{{ $term->id }}" data-gl-term-name="{{ $glLabel }}"
                                               class="dropdown-item {{ (string) $selected === (string) $term->id ? 'is-active' : '' }}">
                                                <span>{{ $glLabel }}</span>
                                                <i data-lucide="check" class="gl-dropdown-check w-4 h-4"></i>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-gl-stats>
                    @include('pages.group-leader.dashboard.partials.head-stats')
                </div>
            </div>
        </div>

        <div class="gl-wrap">
            <div class="gl-section-head" style="margin-bottom:16px;">
                <h2 class="gl-card__title">My groups</h2>
                <span class="gl-card__hint" data-gl-term-label>{{ $glCurrentTerm ?: '' }}</span>
            </div>

            <div data-gl-cards class="gl-async">
                @include('pages.group-leader.dashboard.partials.group-cards')
            </div>

            <div class="gl-foot">Click a group to open its dashboard.</div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/group-leader-dashboard.js')
@endsection
