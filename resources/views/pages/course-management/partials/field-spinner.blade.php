{{--
    The inline "loading" mark that sits beside a cascading field's label while
    its options are being fetched. Toggled by `data-cm-field-spinner`.
--}}
<svg data-cm-field-spinner style="display: none;" class="cm-fieldspin" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
    <g fill="none" fill-rule="evenodd">
        <g transform="translate(1 1)" stroke-width="4">
            <circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle>
            <path d="M36 18c0-9.94-8.06-18-18-18">
                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
            </path>
        </g>
    </g>
</svg>
