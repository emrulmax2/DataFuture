{{--
    The editor for one attendance. It is the ONLY place this row's form fields
    exist, so what the drawer shows is exactly what gets posted.

    It ships inside its row and is moved into the drawer host when opened, so the
    inputs (and their IMasks) survive - nothing is cloned or rebuilt.

    Field names are unchanged from the old screen: hr.attendance.update reads a
    serialised attendance[{id}][...] and hr.attendance.update.all reads the same
    shape, so both endpoints keep working untouched.
--}}
@php
    $r = $row;
    // A pure absence has nothing to clock, so it gets the leave editor and its
    // work fields ride along hidden - update() dereferences them unconditionally.
    $showWork = $r['has_punch'] || $r['system_in'] !== '' || $r['system_out'] !== '';
    $showLeave = $r['leave_status'] > 0 || in_array('absents', $r['buckets']);
@endphp

<div class="att-editor" data-editor="{{ $r['id'] }}" hidden>

    <div class="att-editor__head">
        <div class="att-editor__heading">
            <div class="att-editor__eyebrow">Edit attendance</div>
            <div class="att-editor__name">{{ $r['name'] }}</div>
            <div class="att-editor__meta">
                {{ $r['job_title'] }}@if(!empty($r['rate'])) · £{{ $r['rate'] }}/hr @endif
            </div>
        </div>
        <button type="button" class="att-editor__close js-drawer-close" aria-label="Close">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>

    <div class="att-editor__body">

        @if($showWork)
            <div class="att-cards">
                @foreach(['in' => 'Clock in', 'out' => 'Clock out'] as $side => $label)
                    @php
                        $sched = $side === 'in' ? $r['sched_in'] : $r['sched_out'];
                        $punch = $side === 'in' ? $r['punch_in'] : $r['punch_out'];
                        $state = $side === 'in' ? $r['in_state'] : $r['out_state'];
                        $flagged = $side === 'in' ? $r['clockin_issue'] : $r['clockout_issue'];
                    @endphp
                    <div class="att-card {{ $flagged ? 'is-flagged' : '' }}">
                        <div class="att-card__label">{{ $label }}</div>
                        <div class="att-card__line">
                            <span>Scheduled</span><b>{{ $sched ?: '—' }}</b>
                        </div>
                        <div class="att-card__line">
                            <span>Clocked</span><b class="att-text--{{ $state['tone'] }}">{{ $punch ?: '—' }}</b>
                        </div>
                        <span class="att-pill att-tone--{{ $state['tone'] }}">{{ $state['label'] }}</span>
                        @include('pages.hr.attendance.partials.venue', ['loc' => $side === 'in' ? $r['loc_in'] : $r['loc_out']])
                    </div>
                @endforeach
            </div>

            <div class="att-group">
                <div class="att-group__title">Recorded for payroll</div>
                <div class="att-group__row">
                    <label class="att-field">
                        <span>In</span>
                        <input type="text" maxlength="5" placeholder="00:00"
                               class="att-input att-input--time att-time clockin_system"
                               name="attendance[{{ $r['id'] }}][clockin_system]" value="{{ $r['system_in'] }}"/>
                    </label>
                    <label class="att-field">
                        <span>Out</span>
                        <input type="text" maxlength="5" placeholder="00:00"
                               class="att-input att-input--time att-time clockout_system"
                               name="attendance[{{ $r['id'] }}][clockout_system]" value="{{ $r['system_out'] }}"/>
                    </label>
                </div>
            </div>

            <div class="att-group">
                <div class="att-group__title">Breaks</div>
                <div class="att-breaks {{ $r['break_issue'] ? 'is-flagged' : '' }}">
                    <div><span>Paid</span><b>{{ $r['paid_break'] }}</b></div>
                    <div><span>Unpaid</span><b>{{ $r['unpaid_break'] }}</b></div>
                    <div><span>Taken</span><b class="{{ $r['break_over'] ? 'att-text--bad' : '' }}">{{ $r['break_taken'] }}</b></div>
                </div>
                @if($r['break_over'])
                    <p class="att-hint att-text--warn">Break ran over the allowance. The excess is already deducted from the hours below.</p>
                @endif
                <button type="button" class="att-btn att-btn--outline att-btn--sm view_break" data-id="{{ $r['id'] }}">
                    <i data-lucide="coffee" class="w-3.5 h-3.5"></i> Edit break times
                </button>
            </div>

            <div class="att-group">
                <label class="att-field">
                    <span>Manual adjustment (+/-00:00)</span>
                    <input type="text" maxlength="6" placeholder="+/-00:00"
                           class="att-input att-input--time adjustment"
                           name="attendance[{{ $r['id'] }}][adjustment]" value="{{ $r['adjustment'] }}"/>
                </label>
            </div>

            <div class="att-total">
                <span>Recorded hours</span>
                <b class="js-editor-hours">{{ $r['work_hour'] }}</b>
            </div>
            <p class="att-hint js-negative-warning" style="display:none;">
                <i data-lucide="alert-triangle" class="w-3.5 h-3.5"></i>
                Clock out is before clock in, so the hours are negative. Fix the times before saving.
            </p>
        @else
            {{-- No punches. update() still writes these columns, so they must post. --}}
            <input type="hidden" class="clockin_system" name="attendance[{{ $r['id'] }}][clockin_system]" value="{{ $r['system_in'] }}"/>
            <input type="hidden" class="clockout_system" name="attendance[{{ $r['id'] }}][clockout_system]" value="{{ $r['system_out'] }}"/>
            <input type="hidden" class="adjustment" name="attendance[{{ $r['id'] }}][adjustment]" value="{{ $r['adjustment'] }}"/>
        @endif

        @if($showLeave)
            <div class="att-group">
                <div class="att-group__title">Leave / absence</div>

                @if($r['leave_locked'])
                    {{-- Tied to an approved leave day, so the reason is not HR's to change here. --}}
                    <p class="att-leave-note">
                        <strong>{{ $r['leave_name'] }}</strong>
                        @if($r['leave_status'] === 1) — approved holiday of {{ $r['leave_day_hour'] }} hours @endif
                        @if($r['leave_note']) <em>{{ $r['leave_note'] }}</em> @endif
                    </p>
                    {{-- Say WHY there are no reason options here, so a locked row does not read
                         as a broken one next to an absence that can be freely classified. --}}
                    <p class="att-hint">
                        <i data-lucide="lock" class="w-3.5 h-3.5"></i>
                        The reason comes from an approved leave request — change it in the leave module. You can still adjust the hours and note here.
                    </p>
                    <input type="hidden" name="attendance[{{ $r['id'] }}][leave_status]" value="{{ $r['leave_status'] }}"/>

                    <div class="att-group__row">
                        <label class="att-field">
                            <span>Leave adjustment (+/-00:00)</span>
                            <input type="text" maxlength="6" placeholder="+/-00:00"
                                   class="att-input att-input--time leave_adjustment"
                                   name="attendance[{{ $r['id'] }}][leave_adjustment]" value="{{ $r['leave_adjustment'] }}"/>
                        </label>
                        <div class="att-field">
                            <span>Leave hours</span>
                            <b class="att-readout js-editor-leave-hours">{{ $r['leave_hour_text'] }}</b>
                        </div>
                    </div>
                @else
                    {{-- No approved leave, so HR classifies it. Defaults to Authorised Unpaid
                         (the same fallback the sync writes) when nothing is set yet. --}}
                    @php $classified = $r['leave_status'] > 0 ? $r['leave_status'] : 4; @endphp
                    <p class="att-hint">The system found no shift and no approved leave. Classify the absence:</p>
                    <div class="att-radios js-leave-radios">
                        @foreach([2 => 'Unauthorised Absent', 3 => 'Sick Leave', 4 => 'Authorised Unpaid', 5 => 'Authorised Paid'] as $value => $label)
                            <label class="att-radio">
                                <input type="radio" name="attendance[{{ $r['id'] }}][leave_status]" value="{{ $value }}"
                                       {{ $classified === $value ? 'checked' : '' }}/>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>

                    {{-- Authorised Paid and Holiday credit paid hours (stored in leave_hour, which
                         is what the reports pay out); the unpaid reasons credit nothing. Only shown
                         for the paid reasons - the JS toggles it as the reason changes and mirrors
                         the value into the leave_hour field below. --}}
                    <div class="att-group__row js-paid-hours" @if(!in_array($classified, [1, 5])) style="display:none;" @endif>
                        <label class="att-field">
                            <span>Paid hours</span>
                            <input type="text" maxlength="5" placeholder="00:00"
                                   class="att-input att-input--time att-time js-leave-hours-input"
                                   value="{{ $r['leave_hour_text'] }}"/>
                        </label>
                    </div>

                    {{-- Kept so both endpoints read a uniform payload; not adjustable on a
                         self-classified absence. --}}
                    <input type="hidden" class="leave_adjustment" name="attendance[{{ $r['id'] }}][leave_adjustment]" value="{{ $r['leave_adjustment'] }}"/>
                @endif
            </div>
        @else
            {{-- No leave on this row. The endpoints skip these when leave_status is 0,
                 but every row posting every field keeps the contract uniform. --}}
            <input type="hidden" name="attendance[{{ $r['id'] }}][leave_status]" value="0"/>
            <input type="hidden" class="leave_adjustment" name="attendance[{{ $r['id'] }}][leave_adjustment]" value="{{ $r['leave_adjustment'] }}"/>
        @endif

        <div class="att-group">
            <label class="att-field">
                <span>Note</span>
                <textarea rows="3" class="att-input att-textarea rowNote"
                          name="attendance[{{ $r['id'] }}][note]">{{ $r['note'] }}</textarea>
            </label>
        </div>

        {{-- Carried state. The endpoints read every one of these. --}}
        <input type="hidden" name="attendance[{{ $r['id'] }}][attendance_id]" value="{{ $r['id'] }}"/>
        <input type="hidden" name="attendance[{{ $r['id'] }}][user_issues]" value="{{ $r['user_issues'] }}"/>
        <input type="hidden" class="paid_break" name="attendance[{{ $r['id'] }}][paid_break]" value="{{ $r['paid_break'] }}"/>
        <input type="hidden" class="unpadi_break" name="attendance[{{ $r['id'] }}][unpadi_break]" value="{{ $r['unpaid_break'] }}"/>
        <input type="hidden" class="total_break" name="attendance[{{ $r['id'] }}][total_break]" value="{{ $r['taken_break'] }}"/>
        <input type="hidden" class="allowed_br" name="attendance[{{ $r['id'] }}][allowed_br]" value="{{ $r['allowed_break'] }}"/>
        <input type="hidden" class="total_work_hour" name="attendance[{{ $r['id'] }}][total_work_hour]" value="{{ $r['total_work_hour'] }}"/>
        {{-- data-base is the leave figure BEFORE any adjustment, so retyping the
             adjustment recomputes rather than compounding on the running total. --}}
        <input type="hidden" class="leave_hour" name="attendance[{{ $r['id'] }}][leave_hour]"
               data-base="{{ $r['leave_base'] }}" value="{{ $r['leave_hour'] }}"/>
        @if($r['is_only_leave'])
            <input type="hidden" name="attendance[{{ $r['id'] }}][only_leave]" value="1"/>
        @endif
    </div>

    <div class="att-editor__foot">
        <button type="button" class="att-btn att-btn--solid js-save" data-id="{{ $r['id'] }}">
            <i data-lucide="save" class="w-4 h-4"></i> Save &amp; approve
            <svg class="att-spin" style="display:none;" width="16" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
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
        <button type="button" class="att-btn att-btn--outline reSyncRow"
                data-id="{{ $r['employee_id'] }}" data-date="{{ $r['date'] }}"
                title="Discard this row and rebuild it from the raw punches">
            <i data-lucide="rotate-cw" class="w-4 h-4"></i> Re-sync
        </button>
    </div>
</div>
