@if(($mode ?? 'error') === 'error')
    <div class="ep-holiday-inline-alert is-danger" role="alert">
        <i data-lucide="alert-octagon" class="w-5 h-5"></i>
        <div>
            <strong>Oops.</strong> {{ $message ?? 'Something went wrong.' }}
        </div>
    </div>
@else
    <div class="ep-leave-review-note">
        <i data-lucide="alert-octagon" class="w-4 h-4"></i>
        <div>
            <strong>
                @if(($leaveType ?? 0) == 1)
                    {{ $bookedHoursDisplay }} hours
                @elseif(($leaveType ?? 0) == 5)
                    {{ $bookedHoursDisplay }} hours
                @else
                    00:00 hours
                @endif
            </strong>
            @if(($leaveType ?? 0) == 1)
                will be removed from the staff member's account on confirmation.
            @elseif(($leaveType ?? 0) == 5)
                will be counted as Authorised Paid on confirmation.
            @else
                will be counted as {{ $leaveTypeName ?? 'leave' }} on confirmation.
            @endif
        </div>
    </div>

    <div class="ep-leave-selected-wrap">
        <div class="ep-leave-selected-title">Selected Dates</div>
        <div class="ep-leave-selected-list">
            @foreach(($leaveDays ?? []) as $leaveDay)
                <div class="ep-leave-selected-item leave-request-date-item{{ !empty($leaveDay['is_default_fraction']) ? ' defaultFractionRow' : '' }}">
                    <div class="ep-leave-selected-item__date">
                        <span class="ep-leave-selected-item__icon">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </span>
                        <span>{{ $leaveDay['display_date'] }}</span>
                        <input type="hidden" name="leave[{{ $loop->iteration }}][date]" value="{{ $leaveDay['date_value'] }}"/>
                    </div>
                    <div class="ep-leave-selected-item__controls">
                        <label class="ep-leave-selected-checkbox" for="{{ $leaveDay['fraction_id'] }}" title="Partial leave">
                            <input
                                {{ !empty($leaveDay['fraction_disabled']) ? 'disabled' : '' }}
                                {{ !empty($leaveDay['is_fraction']) ? 'checked' : '' }}
                                name="{{ $leaveDay['fraction_name'] }}"
                                id="{{ $leaveDay['fraction_id'] }}"
                                class="form-check-input fractionIndicator"
                                type="checkbox"
                                value="1"
                            >
                        </label>
                        <input
                            type="text"
                            class="form-control leaveDatesHours timeMask"
                            name="{{ $leaveDay['hours_name'] }}"
                            {!! !empty($leaveDay['hours_readonly']) ? 'readonly' : '' !!}
                            data-daymax="{{ $leaveDay['daymax'] }}"
                            data-maxhour="{{ $leaveDay['maxhour'] }}"
                            value="{{ $leaveDay['hours_value'] }}"
                        />
                        <button type="button" class="ep-leave-selected-remove leave-request-remove-date" data-date="{{ $leaveDay['date_value'] }}">
                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="ep-leave-totals-box">
        <div class="ep-leave-totals-row">
            <span>Days</span>
            <strong>{{ $dayCount ?? 0 }}</strong>
        </div>
        <div class="ep-leave-totals-row">
            <span>Requested</span>
            <strong class="requestedHours">{{ $bookedHoursDisplay ?? '00:00' }}</strong>
        </div>
        <div class="ep-leave-totals-row">
            <span>Allowance Left</span>
            <strong class="balanceLeft">{{ $balanceLeftDisplay ?? '00:00' }}</strong>
        </div>
    </div>

    <div class="ep-leave-note-field">
        <label class="form-label">Note <span class="text-danger">*</span></label>
        <textarea class="form-control w-full" rows="3" name="note" placeholder="Add a reason for this leave request..."></textarea>
    </div>

    <input type="hidden" name="booked_hours" value="{{ $bookedHours ?? 0 }}"/>
    <input type="hidden" name="booked_days" value="{{ $dayCount ?? 0 }}"/>
    <input type="hidden" name="is_fraction_found" value="{{ !empty($fractionFound) ? 1 : 0 }}"/>
    <input type="hidden" name="balance_left" value="{{ $balanceLeft ?? 0 }}"/>
@endif
