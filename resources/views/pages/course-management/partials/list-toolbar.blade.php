{{--
    Search + status chips + Reset / Print / Export row shared by the Course
    Management list screens. Wired by `js/course-table-kit.js` (wireFilters /
    wireExports) through data attributes, not ids, so a page can host several
    independent toolbars — the course detail screen has three.

    Required:
      $toolbarSearchLabel  Screen-reader label, e.g. "Search courses"

    Optional:
      $toolbarChips        [['value' => '1', 'label' => 'Active'], …]
                           Defaults to Active / Archived. The first entry is the
                           initial selection and what Reset returns to.
      $toolbarSelects      Extra dropdown filters, each contributing a request
                           parameter named by its key:
                           [['name' => 'course', 'label' => 'All courses',
                             'options' => [['value' => 1, 'label' => '…'], …]], …]

    Pass `$toolbarChips => []` or omit `$toolbarSearchLabel` when the endpoint
    does not support that filter — an inert control is worse than none.
--}}
@php
    $toolbarChips = $toolbarChips ?? [
        ['value' => '1', 'label' => 'Active'],
        ['value' => '2', 'label' => 'Archived'],
    ];
    $toolbarDefault = !empty($toolbarChips) ? $toolbarChips[0]['value'] : '1';
    $toolbarSelects = $toolbarSelects ?? [];
    $toolbarSearchLabel = $toolbarSearchLabel ?? null;
@endphp

<form class="cm-toolbar" data-cm-filterform autocomplete="off">
    @if($toolbarSearchLabel)
        <label class="cm-search-inline">
            <span class="cm-sr-only">{{ $toolbarSearchLabel }}</span>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
            <input name="query" type="search" data-cm-query placeholder="Search records">
        </label>
    @endif

    {{-- The chips write into the hidden field the table reads, so the request
         shape is unchanged from the legacy <select>. --}}
    @if(!empty($toolbarChips))
        <div class="cm-chips" role="group" aria-label="Filter by status">
            @foreach($toolbarChips as $toolbarChip)
                <button type="button"
                        class="cm-chip {{ $loop->first ? 'is-active' : '' }}"
                        data-cm-chip="{{ $toolbarChip['value'] }}"
                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}">{{ $toolbarChip['label'] }}</button>
            @endforeach
        </div>
        <input type="hidden" name="status" data-cm-status value="{{ $toolbarDefault }}">
    @endif

    @foreach($toolbarSelects as $toolbarSelect)
        <label class="cm-filterselect">
            <span class="cm-sr-only">{{ $toolbarSelect['label'] }}</span>
            <select name="{{ $toolbarSelect['name'] }}" data-cm-filter="{{ $toolbarSelect['name'] }}">
                <option value="">{{ $toolbarSelect['label'] }}</option>
                @foreach($toolbarSelect['options'] as $toolbarOption)
                    <option value="{{ $toolbarOption['value'] }}">{{ $toolbarOption['label'] }}</option>
                @endforeach
            </select>
        </label>
    @endforeach

    <span class="cm-toolbar__spacer"></span>

    <button type="button" data-cm-reset class="cm-linkbtn">Reset</button>

    <button type="button" data-cm-print class="cm-pillbtn">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V2h12v7"></path><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Print
    </button>

    <div class="dropdown">
        <button type="button" class="dropdown-toggle cm-pillbtn" aria-expanded="false" data-tw-toggle="dropdown">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
            Export
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"></path></svg>
        </button>
        <div class="dropdown-menu cm-export-menu">
            <ul class="dropdown-content">
                <li>
                    <a href="javascript:;" data-cm-export="csv" class="dropdown-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg>
                        Export CSV
                    </a>
                </li>
                <li>
                    <a href="javascript:;" data-cm-export="xlsx" class="dropdown-item">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6M8 13h8M8 17h5"></path></svg>
                        Export XLSX
                    </a>
                </li>
            </ul>
        </div>
    </div>
</form>
