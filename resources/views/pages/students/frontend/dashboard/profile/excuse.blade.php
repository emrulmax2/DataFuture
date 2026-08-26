@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @php
        /* Status 0 is awaiting a decision (locked), 1 was rejected and can be
           resubmitted. Anything else is simply selectable. */
        $countSessions = function ($list) {
            $count = 0;

            foreach ($list as $contents) {
                $count += isset($contents['date_lists']) ? count($contents['date_lists']) : 0;
            }

            return $count;
        };

        $pastCount = !empty($pastDateList) ? $countSessions($pastDateList) : 0;
        $futureCount = !empty($futureDateList) ? $countSessions($futureDateList) : 0;
    @endphp

    <div class="spf-page-head">
        <div>
            <div class="spf-eyebrow">Do it online &middot; Services</div>
            <h1 class="spf-h1">Attendance excuse</h1>
            <div class="spf-page-head__sub">Report an absence with supporting evidence.</div>
        </div>
        <div class="spf-spacer"></div>
        <a href="{{ route('students.dashboard') }}" class="spf-btn spf-btn--sm">&larr; Back to dashboard</a>
    </div>

    <form method="post" action="#" id="studentAttendanceExcuseForm" enctype="multipart/form-data">
        <div class="spf-split2">
            <section class="spf-panel--flush">
                <div class="spf-panel__bar">
                    <span class="spf-panel__bar-title">List of absent days</span>
                    <div class="spf-spacer"></div>
                    <span class="spf-panel__bar-note">Select the sessions you are excusing</span>
                </div>

                @if($pastCount > 0)
                    <div class="spf-scrollbody" data-excuse-list>
                        @foreach($pastDateList as $plan_id => $contents)
                            @if(!empty($contents['date_lists']))
                                <div class="spf-grouprow">
                                    <span class="spf-grouprow__name">{{ $contents['module'] }}</span>
                                    <div class="spf-spacer"></div>
                                    <span class="spf-grouprow__count">
                                        {{ count($contents['date_lists']) }} {{ count($contents['date_lists']) === 1 ? 'session' : 'sessions' }}
                                    </span>
                                </div>
                                @foreach($contents['date_lists'] as $planDate)
                                    <label class="spf-checkrow" for="past_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}">
                                        <input {{ $planDate['status'] == 0 ? 'disabled' : '' }}
                                               name="excuses[{{ $plan_id }}][]"
                                               value="{{ $planDate['id'] }}"
                                               id="past_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}"
                                               type="checkbox">
                                        <span>
                                            {{ $planDate['dates'] }}
                                            @if($planDate['status'] == 0)
                                                <span class="spf-checkrow__flag spf-checkrow__flag--pending">Decision pending</span>
                                            @elseif($planDate['status'] == 1)
                                                <span class="spf-checkrow__flag spf-checkrow__flag--rejected">Rejected &mdash; submit again</span>
                                            @endif
                                        </span>
                                    </label>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                @else
                    <div style="padding:22px">
                        <div class="spf-notice">Dates not found for excuse.</div>
                    </div>
                @endif
            </section>

            <section class="spf-stack">
                <div class="spf-panel">
                    <div class="spf-railcard__title" style="margin-bottom:12px">List of future days</div>
                    @if($futureCount > 0)
                        <div class="spf-scrollbody" data-excuse-list style="margin:0 -30px -26px">
                            @foreach($futureDateList as $plan_id => $contents)
                                @if(!empty($contents['date_lists']))
                                    <div class="spf-grouprow">
                                        <span class="spf-grouprow__name">{{ $contents['module'] }}</span>
                                        <div class="spf-spacer"></div>
                                        <span class="spf-grouprow__count">
                                            {{ count($contents['date_lists']) }} {{ count($contents['date_lists']) === 1 ? 'session' : 'sessions' }}
                                        </span>
                                    </div>
                                    @foreach($contents['date_lists'] as $planDate)
                                        <label class="spf-checkrow" for="future_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}">
                                            <input {{ $planDate['status'] == 0 ? 'disabled' : '' }}
                                                   name="excuses[{{ $plan_id }}][]"
                                                   value="{{ $planDate['id'] }}"
                                                   id="future_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}"
                                                   type="checkbox">
                                            <span>
                                                {{ $planDate['dates'] }}
                                                @if($planDate['status'] == 0)
                                                    <span class="spf-checkrow__flag spf-checkrow__flag--pending">Decision pending</span>
                                                @elseif($planDate['status'] == 1)
                                                    <span class="spf-checkrow__flag spf-checkrow__flag--rejected">Rejected &mdash; submit again</span>
                                                @endif
                                            </span>
                                        </label>
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="spf-notice">Dates not found for excuse.</div>
                    @endif
                </div>

                <div class="spf-panel">
                    <div class="spf-railcard__title" style="margin-bottom:12px">Upload documents</div>
                    <div class="spf-dropzone">
                        <label for="addEXCUSEDocument" class="spf-btn spf-btn--sm" style="cursor:pointer">
                            <i data-lucide="upload" class="w-4 h-4"></i> Choose files
                        </label>
                        <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.doc,.docx" name="document[]" multiple
                               class="absolute w-0 h-0 overflow-hidden opacity-0" id="addEXCUSEDocument"/>
                        <span class="spf-dropzone__hint">Max 5MB per file &middot; docx, doc, pdf, jpg, png</span>
                    </div>
                    <div id="addEXCUSEDocumentName" class="documentEXCUSEName spf-filenames"></div>

                    <div style="margin-top:18px">
                        <label for="excuseReason" class="spf-modal__label">Reason <span style="color:var(--spf-rust)">*</span></label>
                        <textarea id="excuseReason" class="spf-textarea" name="reason" rows="5"
                                  placeholder="Briefly explain the circumstances of your absence"></textarea>
                        <div class="acc__input-error error-reason spf-modal__note"></div>
                    </div>

                    <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;margin-top:16px">
                        <button type="submit" id="submitExcuseBtn" class="spf-btn spf-btn--dark">
                            Submit excuse
                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                stroke="white" class="w-4 h-4 ml-2">
                                <g fill="none" fill-rule="evenodd">
                                    <g transform="translate(1 1)" stroke-width="4">
                                        <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                        <path d="M36 18c0-9.94-8.06-18-18-18">
                                            <animateTransform attributeName="transform" type="rotate" from="0 18 18"
                                                to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                        </path>
                                    </g>
                                </g>
                            </svg>
                        </button>
                        <span id="excuse-count" class="spf-section__note">No sessions selected yet</span>
                    </div>
                    <div class="acc__input-error error-excuses spf-modal__note"></div>
                    <input type="hidden" name="student_id" value="{{ $student->id }}" />
                </div>
            </section>
        </div>
    </form>

    <!-- BEGIN: Success Modal Content -->
    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog spf-modal spf-modal--status">
            <div class="modal-content">
                <span class="spf-modal__status-icon spf-modal__status-icon--ok">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </span>
                <div class="successModalTitle"></div>
                <div class="successModalDesc"></div>
                <div class="spf-modal__foot">
                    <button type="button" data-action="none" class="successCloser spf-btn spf-btn--dark">Ok</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-attendance-excuse.js')
@endsection
