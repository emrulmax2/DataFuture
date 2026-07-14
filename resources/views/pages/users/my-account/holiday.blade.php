@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
    @include('pages.users.my-account.show-info')

    <div class="ep-holiday-page intro-y mt-5">
        <div class="grid grid-cols-12 gap-6">
            <div class="col-span-12 xl:col-span-8">
                <div class="ep-holiday-main">
                    @if(!empty($holidayDetails))
                        <div id="employeeHolidayAccordion" class="ep-holiday-year-list">
                            @foreach($holidayDetails as $year => $yearDetails)
                                @php
                                    $yearKey = 'holiday-year-'.$loop->index;
                                    $isCurrentYear = (isset($yearDetails['is_active']) && $yearDetails['is_active'] == 1);
                                @endphp
                                <div class="ep-holiday-year-block">
                                    <div id="{{ $yearKey }}-header" class="ep-holiday-year-block__header">
                                        <button class="ep-holiday-year-toggle {{ $isCurrentYear ? '' : 'collapsed' }}" type="button" data-holiday-toggle="collapse" data-holiday-target="#{{ $yearKey }}-body" data-holiday-parent="#employeeHolidayAccordion" aria-expanded="{{ $isCurrentYear ? 'true' : 'false' }}" aria-controls="{{ $yearKey }}-body">
                                            <span class="ep-holiday-year-toggle__eyebrow">Holiday Year</span>
                                            <span class="ep-holiday-year-toggle__title">{{ date('Y', strtotime($yearDetails['start'])) }} - {{ date('Y', strtotime($yearDetails['end'])) }}</span>
                                            <span class="ep-holiday-year-toggle__badge{{ $isCurrentYear ? ' is-current' : '' }}">{{ $isCurrentYear ? 'Current' : 'Archived' }}</span>
                                            <span class="ep-holiday-year-toggle__arrow">
                                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                            </span>
                                        </button>
                                    </div>

                                    <div id="{{ $yearKey }}-body" class="ep-holiday-collapse{{ $isCurrentYear ? ' is-open' : '' }}" aria-labelledby="{{ $yearKey }}-header">
                                        <div class="ep-holiday-year-block__body">
                                            <div id="{{ $yearKey }}-patterns" class="ep-holiday-pattern-list">
                                                @foreach($yearDetails['patterns'] as $pattern)
                                                    @php
                                                        $patternKey = $yearKey.'-pattern-'.$pattern->id;
                                                        $openPattern = ($loop->first && $isCurrentYear);
                                                        $totalEntitlementMinutes = max(0, (int) ($pattern->totalHolidayEntitlementMinutes ?? 0));
                                                        $bankMinutes = max(0, (int) ($pattern->autoBookedBankHolidayMinutes ?? 0));
                                                        $usedMinutes = max(0, (int) ($pattern->usedMinutes ?? 0));
                                                        $balanceMinutes = (int) ($pattern->existingLeaveHours['balance'] ?? 0);
                                                        $remainingMinutes = max(0, $balanceMinutes);
                                                        $scale = max(1, $totalEntitlementMinutes > 0 ? $totalEntitlementMinutes : ($bankMinutes + $usedMinutes + $remainingMinutes));
                                                        $bankPercent = min(100, round(($bankMinutes / $scale) * 100, 2));
                                                        $usedPercent = min(100, round(($usedMinutes / $scale) * 100, 2));
                                                        $remainingPercent = min(100, round(($remainingMinutes / $scale) * 100, 2));
                                                        $activityRows = collect($pattern->activityRows ?? []);
                                                        $activityCounts = $pattern->activityCounts ?? ['all' => 0, 'bank' => 0, 'approved' => 0, 'pending' => 0, 'taken' => 0, 'rejected' => 0];
                                                        $holidayBase = optional($employee->payment)->holiday_base ?: '5.60';
                                                    @endphp

                                                    <div class="ep-holiday-pattern-card">
                                                        <div id="{{ $patternKey }}-header" class="ep-holiday-pattern-card__header">
                                                            <button class="ep-holiday-pattern-toggle {{ $openPattern ? '' : 'collapsed' }}" type="button" data-holiday-toggle="collapse" data-holiday-target="#{{ $patternKey }}-body" data-holiday-parent="#{{ $yearKey }}-patterns" aria-expanded="{{ $openPattern ? 'true' : 'false' }}" aria-controls="{{ $patternKey }}-body">
                                                                <span class="ep-holiday-pattern-toggle__icon">
                                                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                                                </span>
                                                                <span class="ep-holiday-pattern-toggle__title">Pattern ID: {{ $pattern->id }}</span>
                                                                @if(isset($pattern->patterns) && $pattern->patterns->count() > 0)
                                                                    <span class="ep-holiday-pattern-toggle__chips">
                                                                        @foreach($pattern->patterns as $pt)
                                                                            <span class="ep-holiday-day-chip">{{ $pt->day_name }} {{ $pt->total }}</span>
                                                                        @endforeach
                                                                    </span>
                                                                @endif
                                                                <span class="ep-holiday-pattern-toggle__arrow">
                                                                    <i data-lucide="chevron-down" class="w-4 h-4"></i>
                                                                </span>
                                                            </button>
                                                        </div>

                                                        <div id="{{ $patternKey }}-body" class="ep-holiday-collapse{{ $openPattern ? ' is-open' : '' }}" aria-labelledby="{{ $patternKey }}-header">
                                                            <div class="ep-holiday-pattern-card__body">
                                                                <div class="ep-holiday-balance-panel">
                                                                    <div class="ep-holiday-balance-panel__hero">
                                                                        <div class="ep-holiday-balance-panel__eyebrow">{{ $balanceMinutes < 0 ? 'Overtaken' : 'Balance Remaining' }}</div>
                                                                        <div class="ep-holiday-balance-panel__value{{ $balanceMinutes < 0 ? ' is-negative' : '' }}">
                                                                            {{ $balanceMinutes < 0 ? '-' : '' }}{{ $pattern->existingLeaveHours['balance_html'] ?? '00:00' }}
                                                                        </div>
                                                                        <div class="ep-holiday-balance-panel__subvalue">of {{ $pattern->totalHolidayEntitlement ?? '00:00' }}</div>
                                                                        <div class="ep-holiday-balance-panel__meter">
                                                                            <span class="is-bank" style="width: {{ $bankPercent }}%"></span>
                                                                            <span class="is-used" style="width: {{ $usedPercent }}%"></span>
                                                                            <span class="is-left" style="width: {{ $remainingPercent }}%"></span>
                                                                        </div>
                                                                        <div class="ep-holiday-balance-panel__legend">
                                                                            <span><i class="is-bank"></i>Bank {{ $pattern->autoBookedBankHoliday ?? '00:00' }}</span>
                                                                            <span><i class="is-used"></i>Used {{ $pattern->existingLeaveHours['total_taken'] ?? '00:00' }}</span>
                                                                            <span><i class="is-left"></i>Left {{ $pattern->existingLeaveHours['balance_html'] ?? '00:00' }}</span>
                                                                        </div>
                                                                    </div>

                                                                    <div class="ep-holiday-balance-panel__summary">
                                                                        <div class="ep-holiday-balance-panel__entitlement">
                                                                            <span class="ep-holiday-summary__eyebrow">Entitlement</span>
                                                                            <div class="ep-holiday-balance-panel__entitlement-values">
                                                                                <span>{{ $pattern->holidayEntitlement ?? '00:00' }}</span>
                                                                                <span class="ep-holiday-adjust-btn" aria-hidden="true">
                                                                                    <i data-lucide="repeat-1" class="w-3.5 h-3.5"></i>
                                                                                </span>
                                                                                <span class="is-adjustment">{{ $pattern->adjustmentHtml ?? '+00:00' }}</span>
                                                                                <span class="is-equals">=</span>
                                                                                <strong>{{ $pattern->totalHolidayEntitlement ?? '00:00' }}</strong>
                                                                            </div>
                                                                        </div>
                                                                        <div class="ep-holiday-balance-panel__stats">
                                                                            <div>
                                                                                <span>Bank Holiday</span>
                                                                                <strong>{{ $pattern->autoBookedBankHoliday ?? '00:00' }}</strong>
                                                                            </div>
                                                                            <div>
                                                                                <span>Taken / Booked</span>
                                                                                <strong>{{ $pattern->existingLeaveHours['taken'] ?? '00:00' }}</strong>
                                                                            </div>
                                                                            <div>
                                                                                <span>Holiday Base</span>
                                                                                <strong>{{ $holidayBase }}</strong>
                                                                            </div>
                                                                        </div>
                                                                        <div class="ep-holiday-balance-panel__dates">
                                                                            <i data-lucide="calendar" class="w-4 h-4"></i>
                                                                            {{ date('j M Y', strtotime($pattern->pattern_start)) }} - {{ date('j M Y', strtotime($pattern->pattern_end)) }} &middot; {{ $pattern->workingDays ?? 0 }} working days / year
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="ep-holiday-filter-bar">
                                                                    <span class="ep-holiday-filter-bar__label">Filter</span>
                                                                    <button type="button" class="holiday-filter-chip is-active" data-filter="all">All <span>{{ $activityCounts['all'] ?? 0 }}</span></button>
                                                                    <button type="button" class="holiday-filter-chip" data-filter="bank">Bank Holidays <span>{{ $activityCounts['bank'] ?? 0 }}</span></button>
                                                                    <button type="button" class="holiday-filter-chip" data-filter="approved">Approved <span>{{ $activityCounts['approved'] ?? 0 }}</span></button>
                                                                    <button type="button" class="holiday-filter-chip" data-filter="pending">Pending <span>{{ $activityCounts['pending'] ?? 0 }}</span></button>
                                                                    <button type="button" class="holiday-filter-chip" data-filter="taken">Taken <span>{{ $activityCounts['taken'] ?? 0 }}</span></button>
                                                                    @if(($activityCounts['rejected'] ?? 0) > 0)
                                                                        <button type="button" class="holiday-filter-chip" data-filter="rejected">Rejected <span>{{ $activityCounts['rejected'] ?? 0 }}</span></button>
                                                                    @endif
                                                                </div>

                                                                <div class="ep-holiday-record-table">
                                                                    <div class="ep-holiday-record-table__head">
                                                                        <div>Status</div>
                                                                        <div>Title &amp; Dates</div>
                                                                        <div>Hours</div>
                                                                    </div>
                                                                    <div class="ep-holiday-record-table__body">
                                                                        @forelse($activityRows as $row)
                                                                            @php
                                                                                $clickClass = match($row['action']) {
                                                                                    'approved-day' => 'approvedDayRow',
                                                                                    'taken-day' => 'takenDayRow',
                                                                                    'new-request' => 'newRequestRow',
                                                                                    'rejected-day' => 'rejectedDayRow',
                                                                                    default => ''
                                                                                };
                                                                            @endphp
                                                                            <div
                                                                                class="ep-holiday-record ep-holiday-record--{{ $row['status'] }} {{ $clickClass }}"
                                                                                data-status="{{ $row['status'] }}"
                                                                                @if($row['action'] === 'approved-day' || $row['action'] === 'taken-day' || $row['action'] === 'rejected-day')
                                                                                    data-leavedayid="{{ $row['data_id'] }}"
                                                                                @elseif($row['action'] === 'new-request')
                                                                                    data-id="{{ $row['data_id'] }}"
                                                                                @endif
                                                                            >
                                                                                <div class="ep-holiday-record__status">
                                                                                    <span class="ep-holiday-record__dot"></span>
                                                                                    <span class="ep-holiday-record__labels">
                                                                                        <span>{{ $row['status_label'] }}</span>
                                                                                        <small>{{ $row['status_meta'] }}</small>
                                                                                    </span>
                                                                                </div>
                                                                                <div class="ep-holiday-record__details">
                                                                                    <div class="ep-holiday-record__title">
                                                                                        @if(!empty($row['has_supervised_days']))
                                                                                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                                                                                        @endif
                                                                                        <span>{{ $row['title'] }}</span>
                                                                                    </div>
                                                                                    <div class="ep-holiday-record__date">{{ $row['date_range'] }}</div>
                                                                                </div>
                                                                                <div class="ep-holiday-record__hour">{{ $row['hour'] }}</div>
                                                                            </div>
                                                                        @empty
                                                                            <div class="ep-holiday-record-empty">
                                                                                <i data-lucide="inbox" class="w-5 h-5"></i>
                                                                                No holiday activity found for this pattern.
                                                                            </div>
                                                                        @endforelse
                                                                        @if($activityRows->count() > 0)
                                                                            <div class="ep-holiday-record-empty js-holiday-record-empty" style="display:none;">
                                                                                <i data-lucide="inbox" class="w-5 h-5"></i>
                                                                                No records match this filter.
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                            <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Valid holiday data not found!
                        </div>
                    @endif
                </div>
            </div>

            <div class="col-span-12 xl:col-span-4">
                <div class="ep-holiday-sidebar">
                    <section class="ep-holiday-card ep-holiday-card--accent">
                        <div class="ep-holiday-card__header">
                            <div class="ep-holiday-card__icon is-accent">
                                <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                            </div>
                            <div class="ep-holiday-card__copy">
                                <h2>My Leave Allowance</h2>
                            </div>
                        </div>
                        <div class="ep-holiday-card__body">
                            <div class="relative holidayStatistics">
                                {!! $holidayStatistics !!}
                            </div>
                        </div>
                    </section>

                    <section class="ep-holiday-card ep-holiday-card--primary">
                        <div class="ep-holiday-card__header">
                            <div class="ep-holiday-card__icon">
                                <i data-lucide="file-check" class="w-5 h-5"></i>
                            </div>
                            <div class="ep-holiday-card__copy">
                                <h2>Submit Leave Request</h2>
                            </div>
                        </div>
                        <div class="ep-holiday-card__body">
                            @if($holidayYears->count() > 0 && $empPatterns->count() > 0)
                                <form method="post" action="#" id="employeeLeaveForm" class="ep-leave-form">
                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>

                                    <div class="ep-form-field">
                                        <label>Holiday Year</label>
                                        <select class="form-control w-full" name="leave_holiday_years">
                                            <option value="">Please Select</option>
                                            @foreach($holidayYears as $hy)
                                                @php
                                                    $today = date('Y-m-d');
                                                    $startDate = (isset($hy->start_date) && !empty($hy->start_date) ? date('Y-m-d', strtotime($hy->start_date)) : '');
                                                    $endDate = (isset($hy->end_date) && !empty($hy->end_date) ? date('Y-m-d', strtotime($hy->end_date)) : '');
                                                    $selected = ($today >= $startDate && $today <= $endDate ? 'selected' : '');
                                                @endphp
                                                <option {{ $selected }} data-notice="{{ $hy->notice_period }}" value="{{ $hy->id }}">
                                                    {{ date('Y', strtotime($hy->start_date)) }} - {{ date('Y', strtotime($hy->end_date)) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="ep-form-grid ep-form-grid--two">
                                        <div class="ep-form-field">
                                            <label>Work Pattern</label>
                                            <select class="form-control w-full" name="leave_pattern">
                                                <option value="">Please Select</option>
                                                @foreach($empPatterns as $pt)
                                                    <option {{ $activePattern == $pt->id ? 'Selected' : '' }} value="{{ $pt->id }}">{{ $pt->id }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="ep-form-field">
                                            <label>Type</label>
                                            <select class="form-control w-full" name="leave_type">
                                                {!! $leaveOptionTypes !!}
                                            </select>
                                        </div>
                                    </div>

                                    <div class="ep-leave-calendar-wrap relative">
                                        <div
                                            class="leaveCalendar"
                                            id="leaveCalendar"
                                            data-start="{{ (isset($calendarOptions['startDate']) ? $calendarOptions['startDate'] : '') }}"
                                            data-end="{{ (isset($calendarOptions['endDate']) ? $calendarOptions['endDate'] : '') }}"
                                            data-disable-dates="{{ (isset($calendarOptions['disableDates']) ? $calendarOptions['disableDates'] : '') }}"
                                            data-disable-days="{{ (isset($calendarOptions['disableDays']) ? $calendarOptions['disableDays'] : '') }}"
                                        ></div>
                                    </div>

                                    <div class="leaveFormStep2" style="display: none;"></div>

                                    <div class="ep-leave-submit relative">
                                        <button type="submit" id="confirmRequest" disabled class="btn btn-primary save ep-holiday-submit-btn">
                                            <i data-lucide="calendar-check" class="w-4 h-4 mr-2"></i>
                                            Confirm Request
                                            <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2 loaderSvg">
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
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-danger-soft show flex items-center mb-2" role="alert">
                                    <i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Holiday year of Employee working pattern not found!
                                </div>
                            @endif
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-warning mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="warningCloser btn w-24 btn-primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalTitle"></div>
                        <div class="text-slate-500 mt-2 successModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="NONE" class="btn w-24 btn-primary successCloser">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/user-holiday.js')
@endsection
