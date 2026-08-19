@php use App\Support\GroupLeaderPresenter as GL; @endphp

@php
    $glTone = GL::tone($student['attendance']);
    $glSubTone = $student['submissionPct'] === null ? 'grey' : GL::tone($student['submissionPct'], 90, 75);
@endphp

<div class="gl-drawer__head">
    <div style="display:flex;align-items:center;gap:12px;min-width:0;">
        <span class="gl-avatar is-brand is-lg">{{ GL::initials($student['name']) }}</span>
        <div style="min-width:0;">
            <div class="gl-drawer__name">{{ $student['name'] }}</div>
            <div class="gl-drawer__meta">
                {{ $student['registration_no'] }}
                @if($student['personalTutor']) · Personal tutor: {{ $student['personalTutor'] }} @endif
            </div>
        </div>
    </div>
    <button type="button" class="gl-drawer__close" data-gl-drawer-close aria-label="Close">&times;</button>
</div>

<div class="gl-drawer__body">
    <div class="gl-ministats">
        <div class="gl-ministat {{ $glTone === 'red' ? 'is-red' : ($glTone === 'amber' ? 'is-amber' : '') }}">
            <div class="gl-ministat__label">Attendance</div>
            <div class="gl-ministat__value">{{ $student['attendance'] === null ? '—' : round($student['attendance']).'%' }}</div>
        </div>
        <div class="gl-ministat">
            <div class="gl-ministat__label">Submissions</div>
            <div class="gl-ministat__value">{{ $student['due'] > 0 ? $student['submitted'].'/'.$student['due'] : '—' }}</div>
        </div>
        <div class="gl-ministat {{ $student['consecutive'] >= 3 ? 'is-red' : '' }}">
            <div class="gl-ministat__label">Consec. abs</div>
            <div class="gl-ministat__value">{{ $student['consecutive'] }}</div>
        </div>
    </div>

    {{-- The one write on this dashboard. Posting needs the student id and the
         group/term the leader is looking at, so all three travel with it. --}}
    <form class="gl-form" id="glContactForm" autocomplete="off">
        <div class="gl-form__title">Log contact &amp; absence reason</div>

        <input type="hidden" name="student_id" value="{{ $student['id'] }}">
        <input type="hidden" name="group_id" value="{{ $groupId }}">
        <input type="hidden" name="term_id" value="{{ $termId }}">

        <div class="gl-field">
            <label class="gl-field__label" for="glMethod">Contact method</label>
            <select id="glMethod" name="method" class="gl-field__select">
                @foreach(\App\Models\GroupLeaderContact::METHODS as $method)
                    <option value="{{ $method }}">{{ $method }}</option>
                @endforeach
            </select>
            <div class="gl-field__error" data-gl-error="method"></div>
        </div>

        <div class="gl-field">
            <label class="gl-field__label" for="glReason">Reason for absence</label>
            <select id="glReason" name="reason" class="gl-field__select">
                <option value="">Select reason…</option>
                @foreach(\App\Models\GroupLeaderContact::REASONS as $reason)
                    <option value="{{ $reason }}">{{ $reason }}</option>
                @endforeach
            </select>
            <div class="gl-field__error" data-gl-error="reason"></div>
        </div>

        <div class="gl-field">
            <label class="gl-field__label" for="glNote">Note</label>
            <textarea id="glNote" name="note" rows="2" class="gl-field__area" placeholder="e.g. Spoke to student, agreed catch-up plan…"></textarea>
            <div class="gl-field__error" data-gl-error="note"></div>
        </div>

        <div class="gl-field">
            <label class="gl-field__label" for="glFollowUp">Follow-up date (optional)</label>
            <input id="glFollowUp" name="follow_up_date" class="gl-field__input" placeholder="DD-MM-YYYY" data-gl-followup>
            <div class="gl-field__error" data-gl-error="follow_up_date"></div>
        </div>

        <button type="submit" class="gl-submit" id="glContactSave">Save to student record</button>
    </form>

    <div class="gl-history">
        <div class="gl-history__title">Contact history</div>

        @forelse($student['log'] as $entry)
            <div class="gl-history__item">
                <div class="gl-history__row">
                    <span class="gl-history__method">
                        {{ $entry['method'] }}@if($entry['reason']) · {{ $entry['reason'] }}@endif
                    </span>
                    <span class="gl-history__date">{{ $entry['date'] }}</span>
                </div>
                @if($entry['note'])
                    <div class="gl-history__note">{{ $entry['note'] }}</div>
                @endif
                <div class="gl-history__row">
                    @if($entry['followUp'])
                        <span class="gl-history__follow">Follow-up: {{ $entry['followUp'] }}</span>
                    @else
                        <span></span>
                    @endif
                    @if($entry['by'])
                        <span class="gl-history__date">by {{ $entry['by'] }}</span>
                    @endif
                </div>
            </div>
        @empty
            <div class="gl-history__empty">
                No contact logged yet. Reach out and record the reason above.
            </div>
        @endforelse
    </div>
</div>
