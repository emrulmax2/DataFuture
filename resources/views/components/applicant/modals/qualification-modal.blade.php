{{--
    Add / Edit qualification.

    The theme moves a modal to <body> when it opens (tw-starter modal.js:35),
    so the design cannot be scoped through `.apd-form` the way the page is -
    the skin rides on the modal element itself, as `.apd-modal-skin`.

    Every hook the JS relies on is kept: the ids, the field names, the
    `error-<field>` holders, the `<field>` class the validator paints
    `border-danger` on, `datepicker` (an IMask DD-MM-YYYY mask, not a calendar)
    and the spinner inside the submit button.
--}}
@php
    $apdQualFields = [
        ['name' => 'highest_academic', 'label' => 'Highest academic qualification', 'required' => true,  'placeholder' => 'e.g. L3 Diploma in Business'],
        ['name' => 'awarding_body',    'label' => 'Awarding body',                  'required' => false, 'placeholder' => 'e.g. PEARSON, NCFE, a university'],
        ['name' => 'subjects',         'label' => 'Subjects',                       'required' => false, 'placeholder' => ''],
    ];
    $apdApplicantId = isset($apply->id) && $apply->id > 0 ? $apply->id : 0;
@endphp

@foreach([
    ['mode' => 'add',  'prefix' => '',      'modal' => 'addQualificationModal',  'form' => 'addQualificationForm',  'title' => 'Add qualification',  'btn' => 'saveEducationQualification',   'cta' => 'Save'],
    ['mode' => 'edit', 'prefix' => 'edit_', 'modal' => 'editQualificationModal', 'form' => 'editQualificationForm', 'title' => 'Edit qualification', 'btn' => 'updateEducationQualification', 'cta' => 'Update'],
] as $apdQ)
    <!-- BEGIN: {{ $apdQ['title'] }} Modal -->
    <div id="{{ $apdQ['modal'] }}" class="modal apd-modal-skin" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="{{ $apdQ['form'] }}" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="apd-modal__title">{{ $apdQ['title'] }}</h3>
                        <button type="button" data-tw-dismiss="modal" class="apd-modal__x" aria-label="Close">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="apd-modal__grid">
                            @foreach($apdQualFields as $apdF)
                                <div class="apd-field">
                                    <label for="{{ $apdQ['prefix'] }}{{ $apdF['name'] }}">
                                        {{ $apdF['label'] }}@if($apdF['required']) <span class="text-danger">*</span>@endif
                                    </label>
                                    <input type="text" id="{{ $apdQ['prefix'] }}{{ $apdF['name'] }}" name="{{ $apdF['name'] }}"
                                           class="form-control apd-ctl {{ $apdF['name'] }}"
                                           placeholder="{{ $apdF['placeholder'] }}">
                                    <div class="acc__input-error error-{{ $apdF['name'] }} apd-err text-danger"></div>
                                </div>
                            @endforeach

                            <div class="apd-grid-2">
                                <div class="apd-field">
                                    <label for="{{ $apdQ['prefix'] }}result">Result</label>
                                    <input type="text" id="{{ $apdQ['prefix'] }}result" name="result"
                                           class="form-control apd-ctl result" placeholder="e.g. Merit, 5 GCSEs A&ndash;C">
                                    <div class="acc__input-error error-result apd-err text-danger"></div>
                                </div>
                                <div class="apd-field">
                                    <label for="{{ $apdQ['prefix'] }}degree_award_date">Date of award <span class="text-danger">*</span></label>
                                    <input type="text" id="{{ $apdQ['prefix'] }}degree_award_date" name="degree_award_date"
                                           class="form-control apd-ctl datepicker degree_award_date"
                                           placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-degree_award_date apd-err text-danger"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="apd-btn apd-btn--ghost">Cancel</button>
                        <button type="submit" id="{{ $apdQ['btn'] }}" class="apd-btn apd-btn--primary">
                            {{ $apdQ['cta'] }}
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <input type="hidden" name="applicant_id" value="{{ $apdApplicantId }}"/>
                        @if($apdQ['mode'] === 'edit')
                            <input type="hidden" name="id" value="0"/>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: {{ $apdQ['title'] }} Modal -->
@endforeach
