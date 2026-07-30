{{--
    The spinner + save glyph pair inside a submit button. `setBusy()` in
    `js/course-table-kit.js` swaps one for the other, so both must be present
    and the spinner must start hidden.
--}}
<svg style="display: none;" class="cm-spinner" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white">
    <g fill="none" fill-rule="evenodd">
        <g transform="translate(1 1)" stroke-width="4">
            <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
            <path d="M36 18c0-9.94-8.06-18-18-18">
                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
            </path>
        </g>
    </g>
</svg>
<svg data-cm-btn-icon width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><path d="M17 21v-8H7v8M7 3v5h8"></path></svg>
