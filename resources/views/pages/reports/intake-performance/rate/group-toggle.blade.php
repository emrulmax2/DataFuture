{{--
    Button on an intake row that opens and closes the rows or blocks carrying
    data-crn-group="{{ $key }}" (resources/js/intake-rate-reports.js). Open by default.

    $key    group key, unique on the page (report key + intake index)
    $label  e.g. "January 2025 intake"
--}}
<button type="button" class="crn-grp-toggle" aria-expanded="true" data-crn-toggle="{{ $key }}"><svg aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>{{ $label }}</button>
