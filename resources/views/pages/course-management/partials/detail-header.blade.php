{{--
    Record summary + tab strip for the Course Management detail screens.

    Required:
      $detailBadge     Eyebrow pill, e.g. "Course"
      $detailTitle     Serif heading, e.g. the course name
      $detailMeta      [['label' => …, 'value' => …, 'icon' => 'shield'], …]
      $detailTabs      [['key' => 'modules', 'label' => 'Course Modules', 'icon' => 'layers'], …]
                       The first tab is the one shown on load. Omit it entirely
                       on single-panel screens — a lone tab is just noise.

    Optional:
      $detailSubtitle  Muted line beside the badge
--}}
@php
    $detailSubtitle = $detailSubtitle ?? '';
    $detailTabs = $detailTabs ?? [];

    // Icon bodies are static markup owned by this file — nothing here comes
    // from user input, so echoing them raw is safe.
    $detailIcons = [
        'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>',
        'layers' => '<path d="M12 2l9 5-9 5-9-5z"></path><path d="M3 12l9 5 9-5M3 17l9 5 9-5"></path>',
        'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path>',
        'grid' => '<rect x="3" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="3" width="7" height="7" rx="1"></rect><rect x="14" y="14" width="7" height="7" rx="1"></rect><rect x="3" y="14" width="7" height="7" rx="1"></rect>',
        'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"></rect><path d="m22 7-10 6L2 7"></path>',
        'database' => '<ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M3 5v14c0 1.7 4 3 9 3s9-1.3 9-3V5"></path><path d="M3 12c0 1.7 4 3 9 3s9-1.3 9-3"></path>',
        'pound' => '<path d="M18 7c0-2.2-1.8-4-4-4s-4 1.8-4 4v10M6 12h9M6 20h12"></path>',
        'check' => '<path d="M20 6L9 17l-5-5"></path>',
        'calendar' => '<path d="M8 2v4M16 2v4M3 10h18"></path><rect x="3" y="4" width="18" height="18" rx="2"></rect>',
        'book' => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"></path><circle cx="12" cy="10" r="3"></circle>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>',
        'clock' => '<circle cx="12" cy="12" r="10"></circle><path d="M12 6v6l4 2"></path>',
    ];
    // Falls back rather than breaking, but every name used in this module is
    // in the map above — `calendar`, `book`, `pin` and `users` were being
    // requested before they existed and silently rendering as `grid`.
    $detailIcon = fn ($name) => $detailIcons[$name] ?? $detailIcons['grid'];
@endphp

<div class="cm-card cm-detail">
    <div class="cm-detail__top">
        <div class="cm-detail__eyebrow">
            <span class="cm-detail__badge">{{ $detailBadge }}</span>
            @if($detailSubtitle !== '')
                <span class="cm-detail__sub">{{ $detailSubtitle }}</span>
            @endif
        </div>

        <h2 class="cm-detail__title cm-serif">{{ $detailTitle }}</h2>

        @if(!empty($detailMeta))
            <div class="cm-detail__meta">
                @foreach($detailMeta as $detailItem)
                    <div class="cm-meta">
                        <span class="cm-meta__icon" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $detailIcon($detailItem['icon'] ?? 'grid') !!}</svg>
                        </span>
                        <div style="min-width:0;">
                            <div class="cm-meta__label">{{ $detailItem['label'] }}</div>
                            <div class="cm-meta__value">{{ $detailItem['value'] !== '' && $detailItem['value'] !== null ? $detailItem['value'] : '—' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Panels are matched by `data-cm-tabpanel`; switching is handled in JS
         so Tabulator can be built (and redrawn) only once its panel is visible. --}}
    @if(count($detailTabs) > 1)
    <div class="cm-tabs" role="tablist">
        @foreach($detailTabs as $detailTab)
            <button type="button"
                    class="cm-tab {{ $loop->first ? 'is-active' : '' }}"
                    role="tab"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    data-cm-tab="{{ $detailTab['key'] }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">{!! $detailIcon($detailTab['icon'] ?? 'grid') !!}</svg>
                {{ $detailTab['label'] }}
            </button>
        @endforeach
    </div>
    @endif
</div>
