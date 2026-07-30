{{--
    Venue repeater for the Course Creation forms.

    Required:
      $venues        Venue models for the row dropdown
      $venuesSeeded  true to render one blank starting row (the add form); the
                     edit form starts empty and is filled from the record.
--}}
@php $venuesSeeded = $venuesSeeded ?? false; @endphp

<div class="cm-venues" data-cm-venues>
    <div class="cm-venues__head">
        <span>Venues</span>
        <button type="button" class="cm-venues__add" data-cm-venue-add>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
            Add more venue
        </button>
    </div>

    <div class="cm-venues__grid" data-cm-venue-rows>
        <span class="cm-venues__col">Venue</span>
        <span class="cm-venues__col">SLC Code</span>
        <span class="cm-venues__col cm-venues__col--center">Eve/Wknd</span>
        <span class="cm-venues__col">Weekdays</span>
        <span class="cm-venues__col">Weekends</span>
        <span class="cm-venues__col"></span>

        @if($venuesSeeded)
            @include('pages.course-management.partials.venue-row')
        @endif
    </div>

    {{-- Cloned by course-creation-page.js so appended rows can never drift
         from the server-rendered one. --}}
    <template data-cm-venue-template>
        @include('pages.course-management.partials.venue-row')
    </template>
</div>
