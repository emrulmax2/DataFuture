{{--
    One venue row for the Course Creation repeater.

    Rendered twice per form: once as the starting row and once inside a
    <template> that `course-creation-page.js` clones for "Add more venue" and
    for each saved venue on edit. Sharing the markup is deliberate — the legacy
    code had three separate copies of this row and they had drifted apart.

    The five inputs post parallel arrays that CoursCreationController::store
    walks by index, so every row must contribute exactly one entry to each. The
    evening/weekend checkbox therefore carries no name: an unchecked box submits
    nothing, which would shorten `evening_and_weekend[]` and shift the flag onto
    the wrong venue. The adjacent hidden field is the one that posts.
--}}
<div class="cm-venue-row">
    <select name="venue_id[]" class="cm-select cm-select--sm venue_id">
        <option value="">Please Select</option>
        @if(!empty($venues))
            @foreach($venues as $vn)
                <option value="{{ $vn->id }}">{{ $vn->name }}</option>
            @endforeach
        @endif
    </select>

    <input type="text" name="slc_code[]" class="cm-input cm-input--sm slc_code" placeholder="SLC code">

    <span class="cm-venues__switch">
        <label class="cm-switchmini" title="Evening &amp; weekend">
            <input type="checkbox" class="cm-switchmini__input eveningAndWeekend" value="1">
            <span class="cm-switchmini__track">
                <span class="cm-switchmini__knob">
                    <svg data-cm-switch-on width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                    <svg data-cm-switch-off width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                </span>
            </span>
        </label>
        <input type="hidden" class="evening_and_weekend" name="evening_and_weekend[]" value="0">
    </span>

    <input type="number" step="1" min="0" name="weekdays[]" class="cm-input cm-input--sm weekdays">

    {{-- Only editable once evening/weekend is on; the controller discards it otherwise. --}}
    <input type="number" step="1" min="0" name="weekends[]" class="cm-input cm-input--sm weekends" readonly>

    <span class="cm-venues__switch">
        <button type="button" class="cm-venues__remove" data-cm-venue-remove title="Remove venue">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path></svg>
        </button>
    </span>
</div>
