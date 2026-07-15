{{--
    One attendance. Each row is rendered exactly once and tagged with every bucket
    it belongs to (a leave row that also carries issues is in two), so the tabs
    filter the same DOM instead of the page emitting a second copy of the form.
--}}
@php
    $r = $row;
    // Follows the timeline bar, not the recommendation - see edge_tone in the controller.
    $edge = $r['edge_tone'];
    // Nothing was clocked and there is leave on the row, so the leave hours are the
    // figure that matters - the work total is 0 and would be misleading. Covers an
    // approved holiday (status 1) as well as an absence (2-5).
    $isAbsence = $r['leave_status'] > 0 && !$r['has_punch'];
@endphp

<div class="att-row {{ $r['is_reviewed'] ? 'is-reviewed' : '' }} {{ $r['is_adjusted'] ? 'is-adjusted' : '' }}"
     id="attRow_{{ $r['id'] }}"
     data-id="{{ $r['id'] }}"
     data-employee="{{ $r['employee_id'] }}"
     data-date="{{ $r['date'] }}"
     data-name="{{ $r['name'] }}"
     data-buckets="{{ implode(' ', $r['buckets']) }}"
     data-flags="{{ implode(' ', $r['flags']) }}"
     data-leave-status="{{ $r['leave_status'] }}"
     data-overtime="{{ in_array('overtime', $r['buckets']) ? 1 : 0 }}"
     data-rostered="{{ $r['rostered_min'] !== null ? $r['rostered_min'] : '' }}"
     data-punch-in="{{ $r['punch_in'] }}"
     data-punch-out="{{ $r['punch_out'] }}">

    <span class="att-row__edge att-edge--{{ $edge }}"></span>

    {{-- who, and the one line that says whether HR needs to care --}}
    <div class="att-row__who">
        <span class="att-avatar att-avatar--{{ $edge }}">
            @if($r['photo_url'])
                <img class="att-avatar__image" src="{{ $r['photo_url'] }}" alt="{{ $r['name'] }}">
            @else
                {{ $r['initials'] }}
            @endif
        </span>
        <div class="att-row__ident">
            <div class="att-row__name">{{ $r['name'] }}</div>
            <div class="att-row__title">{{ $r['job_title'] }}</div>

            {{-- A leave-only absence has no punch to recommend on, so instead of a flat
                 grey "No clocking recorded" the pill wears the leave type's own colour,
                 matching the timeline's noclock block. Rows that DID punch keep their
                 tone so a red/amber hours warning is never masked. --}}
            <span class="att-reco att-tone--{{ $r['reco']['tone'] }} {{ $isAbsence ? 'att-reco--leave att-reco--leave-'.$r['leave_status'] : '' }}">
                <span class="att-reco__dot"></span>
                {{ $r['reco']['label'] }}
            </span>

            @if($r['has_punch'])
                <div class="att-row__facts">{{ $r['fact_line'] }}</div>
            @endif

            @if($r['leave_status'] > 0)
                <div class="att-row__leave">
                    <i data-lucide="palmtree" class="w-3 h-3"></i>
                    <span>{{ $r['leave_name'] }}@if($r['leave_note']) — {{ $r['leave_note'] }}@endif</span>
                </div>
            @endif
        </div>
    </div>

    {{-- rostered shift against what was actually recorded --}}
    <div class="att-row__mid">
        @if($r['timeline'])
            <div class="att-timeline">
                @if($r['timeline']['clock'])
                    <span class="att-timeline__stamp js-stamp-in" style="left: {{ $r['timeline']['clock']['left'] }}%">{{ $r['system_in'] ?: $r['punch_in'] }}</span>
                    <span class="att-timeline__stamp js-stamp-out" style="left: {{ min(100, $r['timeline']['clock']['left'] + $r['timeline']['clock']['width']) }}%">{{ $r['system_out'] ?: $r['punch_out'] }}</span>
                @endif
                <div class="att-timeline__track">
                    @if($r['timeline']['sched'])
                        <span class="att-timeline__sched"
                              style="left: {{ $r['timeline']['sched']['left'] }}%; width: {{ $r['timeline']['sched']['width'] }}%"></span>
                    @endif
                    @if($r['timeline']['clock'])
                        <span class="att-timeline__clock att-bar--{{ $r['bar_tone'] }} js-clock-bar"
                              style="left: {{ $r['timeline']['clock']['left'] }}%; width: {{ $r['timeline']['clock']['width'] }}%"></span>
                    @endif
                </div>
            </div>
        @else
            @php
                $hasLeaveHours = $r['leave_day_hour'] && $r['leave_day_hour'] !== '00:00';
                $noClockTitle = $r['leave_status'] > 0 ? $r['leave_name'] : 'No clocking recorded';

                if ($r['leave_status'] > 0) {
                    // The detail line reads: [approved-holiday hours] · [leave's own comment] · [HR's note].
                    // hintBase is everything the server already knows; the note is split out so the
                    // editor can refresh this line the moment HR saves one, without a reload.
                    $hintParts = [];
                    if ($r['leave_status'] === 1 && $hasLeaveHours) {
                        $hintParts[] = 'Approved holiday · '.$r['leave_day_hour'].' hours';
                    }
                    if (trim((string) $r['leave_note']) !== '') {
                        $hintParts[] = trim((string) $r['leave_note']);
                    }
                    $hintBase = implode(' · ', $hintParts);

                    if (trim((string) $r['note']) !== '') {
                        $hintParts[] = trim((string) $r['note']);
                    }
                    $noClockHint = !empty($hintParts) ? implode(' · ', $hintParts) : 'Recorded absence';
                } else {
                    $hintBase = '';
                    $noClockHint = 'No in/out punch available';
                }
            @endphp
            <div class="att-noclock {{ $r['leave_status'] > 0 ? 'att-noclock--leave att-noclock--leave-'.$r['leave_status'] : '' }}"
                 data-hint-base="{{ $hintBase }}">
                <span class="att-noclock__icon">
                    <i data-lucide="{{ $r['leave_status'] > 0 ? 'calendar-x' : 'minus-circle' }}" class="w-4 h-4"></i>
                </span>
                <span class="att-noclock__copy">
                    <span class="att-noclock__title">{{ $noClockTitle }}</span>
                    <span class="att-noclock__hint">{{ $noClockHint }}</span>
                </span>
            </div>
        @endif
    </div>

    {{-- hours, then the two things HR can do without leaving the list --}}
    <div class="att-row__end">
        <div class="att-hours">
            @if($isAbsence)
                <div class="att-hours__value js-row-leave-hours">{{ $r['leave_hour_text'] }}<small>hrs</small></div>
                <div class="att-hours__delta">Leave hours</div>
            @else
                <div class="att-hours__value js-row-hours">{{ $r['work_hour'] }}<small>hrs</small></div>
                <div class="att-hours__delta js-row-delta">{{ $r['hour_delta_plain'] }}</div>
            @endif
        </div>

        <div class="att-actions">
            <button type="button" class="att-btn att-btn--solid att-btn--sm js-approve" data-id="{{ $r['id'] }}"
                    title="Save this row as recorded and mark it reviewed">
                <i data-lucide="check" class="w-3.5 h-3.5"></i> Approve
            </button>

            <span class="att-status js-status">
                <i data-lucide="check" class="w-3 h-3"></i> Approved
            </span>

            <button type="button" class="att-btn att-btn--outline att-btn--sm js-edit" data-id="{{ $r['id'] }}"
                    title="Open the editor">
                <i data-lucide="pencil" class="w-3.5 h-3.5"></i> Edit
            </button>
        </div>
    </div>

    @include('pages.hr.attendance.partials.editor', ['row' => $r])
</div>
