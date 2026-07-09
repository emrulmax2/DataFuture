@extends('../layout/employee-profile')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
<style type="text/css">
    body{
        background-color: #eff1f2 !important;
    }
</style>

@include('pages.employee.profile.partials.cover-header')
@include('pages.employee.profile.partials.side-tabs')

<div class="ep-grid ep-payment-page">
    <div class="ep-col">
        @php
            $tableBtn = 'btn btn-outline-secondary w-full sm:w-auto ep-table-toolbar__btn';
            $tablePrimaryBtn = 'btn btn-primary w-full sm:w-auto ep-table-toolbar__btn ep-table-toolbar__btn--primary';
            $tableSecondaryBtn = 'btn btn-secondary w-full sm:w-auto ep-table-toolbar__btn';
            $payment = $employee->payment;
            $hasPayment = isset($payment->id) && $payment->id > 0;
            $hasBankTransfer = isset($payment->payment_method) && $payment->payment_method === 'Bank Transfer';
            $hasClockin = isset($payment->subject_to_clockin) && $payment->subject_to_clockin === 'Yes';
            $hasHoliday = isset($payment->holiday_entitled) && $payment->holiday_entitled === 'Yes';
            $hasPension = isset($payment->pension_enrolled) && $payment->pension_enrolled === 'Yes';
            $yesBadge = '<span class="ep-status-badge ep-status-badge--yes"><i data-lucide="check"></i><span>Yes</span></span>';
            $noBadge = '<span class="ep-status-badge ep-status-badge--no"><i data-lucide="x"></i><span>No</span></span>';
            $personName = function ($rel) {
                if (isset($rel->user->employee->full_name) && !empty($rel->user->employee->full_name)) return $rel->user->employee->full_name;
                if (isset($rel->user->name) && !empty($rel->user->name)) return $rel->user->name;
                return 'Unknown';
            };
            $personInitials = function ($name) {
                $name = trim((string) $name);
                if ($name === '') return 'NA';
                $parts = preg_split('/\s+/', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $initials = '';
                foreach (array_slice($parts, 0, 2) as $part) {
                    $initials .= strtoupper(substr($part, 0, 1));
                }
                if ($initials !== '') return $initials;
                return strtoupper(substr($name, 0, 2));
            };
            $personPillTone = function ($name) {
                $tones = [
                    ['bg' => '#1d8b84', 'ring' => '#d7eeeb'],
                    ['bg' => '#3f63c8', 'ring' => '#dde4fb'],
                    ['bg' => '#d15436', 'ring' => '#f9e0da'],
                    ['bg' => '#c68f10', 'ring' => '#f7ebc9'],
                ];
                $name = trim((string) $name);
                $sum = 0;
                for ($i = 0; $i < strlen($name); $i++) {
                    $sum += ord($name[$i]);
                }
                return $tones[$sum % count($tones)];
            };
        @endphp

        <div class="flex flex-col gap-5 mt-5">

            {{-- ============================ Payment Settings ============================ --}}
            <div class="intro-y ep-pcard ep-pcard--teal">
                <div class="ep-pcard__head">
                    <span class="ep-pcard__icon ep-pcard__icon--teal">
                        <i data-lucide="credit-card"></i>
                    </span>
                    <h2 class="ep-pcard__title">Payment Settings</h2>
                    <div class="ep-pcard__actions">
                        @if($hasPayment)
                            <button data-tw-toggle="modal" data-tw-target="#editEmployeePaymentSettingModal" type="button" class="ep-pbtn">
                                <i data-lucide="pencil"></i> Edit Payment Settings
                            </button>
                        @else
                            <button data-tw-toggle="modal" data-tw-target="#addEmployeePaymentSettingModal" type="button" class="ep-pbtn">
                                <i data-lucide="plus"></i> Add Payment Settings
                            </button>
                        @endif
                    </div>
                </div>

                @if($hasPayment)
                    {{-- key facts strip --}}
                    <div class="ep-keyfacts">
                        <div class="ep-keyfacts__cell">
                            <span class="ep-keyfacts__icon"><i data-lucide="calendar-clock"></i></span>
                            <div>
                                <div class="ep-plabel">Pay Frequency</div>
                                <div class="ep-pvalue">{{ $payment->pay_frequency ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="ep-keyfacts__cell">
                            <span class="ep-keyfacts__icon"><i data-lucide="file-text"></i></span>
                            <div>
                                <div class="ep-plabel">Tax Code</div>
                                <div class="ep-pvalue">{{ $payment->tax_code ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="ep-keyfacts__cell">
                            <span class="ep-keyfacts__icon"><i data-lucide="landmark"></i></span>
                            <div>
                                <div class="ep-plabel">Payment Method</div>
                                <div class="ep-pvalue">{{ $payment->payment_method ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- bank accounts --}}
                    @if($hasBankTransfer)
                        <div class="ep-psub">
                            <div class="ep-psub__head">
                                <h3 class="ep-psub__title">Bank Accounts</h3>
                                @if(isset($employee->banks) && $employee->banks->count() == 0)
                                    <button data-tw-toggle="modal" data-tw-target="#addBankModal" type="button" class="ep-pbtn">
                                        <i data-lucide="plus"></i> Add Account
                                    </button>
                                @endif
                            </div>

                            <div class="ep-table-toolbar flex flex-col xl:flex-row xl:items-end gap-4">
                                <form id="tabulatorFilterForm-BNK" class="ep-table-toolbar__form xl:flex xl:flex-wrap xl:items-end gap-3 xl:mr-auto">
                                    <div class="sm:flex items-center gap-2">
                                        <label class="w-12 flex-none text-sm font-medium text-slate-600">Query</label>
                                        <input id="query-BNK" name="query" type="text" class="form-control sm:w-48" placeholder="Search...">
                                    </div>
                                    <div class="sm:flex items-center gap-2">
                                        <label class="w-12 flex-none text-sm font-medium text-slate-600">Status</label>
                                        <select id="status-BNK" name="status" class="form-select sm:w-auto">
                                            <option value="3">All</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                            <option value="2">Archived</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <button id="tabulator-html-filter-go-BNK" type="button" class="{{ $tablePrimaryBtn }}">Go</button>
                                        <button id="tabulator-html-filter-reset-BNK" type="button" class="{{ $tableSecondaryBtn }}">Reset</button>
                                    </div>
                                </form>

                                <div class="ep-table-toolbar__actions flex flex-col sm:flex-row gap-2">
                                    <button id="tabulator-print-BNK" class="{{ $tableBtn }}">
                                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                                    </button>
                                    <div class="dropdown w-full sm:w-auto">
                                        <button class="dropdown-toggle {{ $tableBtn }}" aria-expanded="false" data-tw-toggle="dropdown">
                                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                                        </button>
                                        <div class="dropdown-menu w-40">
                                            <ul class="dropdown-content">
                                                <li>
                                                    <a id="tabulator-export-csv-BNK" href="javascript:;" class="dropdown-item">
                                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                                    </a>
                                                </li>
                                                <li>
                                                    <a id="tabulator-export-xlsx-BNK" href="javascript:;" class="dropdown-item">
                                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="overflow-x-auto scrollbar-hidden">
                                <div id="employeeBankListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
                            </div>
                            <div class="ep-tab-foot">
                                <span id="employeeBankTableCount" class="ep-tab-foot__count"></span>
                                <span class="ep-tab-foot__note">
                                    <i data-lucide="lock"></i> Account details are visible to payroll administrators only
                                </span>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="ep-pempty">
                        <i data-lucide="info" class="w-4 h-4 flex-none"></i> Payment settings have not been configured for this employee yet.
                    </div>
                @endif
            </div>

            @if($hasPayment)
                {{-- ==================== Time, Holiday & Approvals ==================== --}}
                <div class="intro-y ep-pcard ep-pcard--teal">
                    <div class="ep-pcard__head">
                        <span class="ep-pcard__icon ep-pcard__icon--teal">
                            <i data-lucide="clock"></i>
                        </span>
                        <h2 class="ep-pcard__title">Time, Holiday &amp; Approvals</h2>
                    </div>

                    <div class="ep-pbody">
                        <div class="ep-prow ep-prow--4">
                            <div class="min-w-0">
                                <div class="ep-pfield-label">Subject to Clocking</div>
                                {!! $hasClockin ? $yesBadge : $noBadge !!}
                            </div>
                            <div class="min-w-0">
                                <div class="ep-pfield-label">Holiday Entitlement</div>
                                {!! $hasHoliday ? $yesBadge : $noBadge !!}
                            </div>
                            <div class="min-w-0">
                                <div class="ep-pfield-label">Holiday Base</div>
                                <div class="ep-pvalue">{{ ($hasHoliday && isset($payment->holiday_base) && $payment->holiday_base !== null && $payment->holiday_base !== '') ? $payment->holiday_base : 'N/A' }}@if($hasHoliday && isset($payment->holiday_base) && $payment->holiday_base !== null && $payment->holiday_base !== '')<span class="ep-pvalue__unit"> weeks</span>@endif</div>
                            </div>
                            <div class="min-w-0">
                                <div class="ep-pfield-label">Bank Holiday Auto Book</div>
                                {!! (isset($payment->bank_holiday_auto_book) && $payment->bank_holiday_auto_book === 'Yes') ? $yesBadge : $noBadge !!}
                            </div>
                        </div>

                        <div class="ep-prow ep-prow--2">
                            <div class="min-w-0">
                                <div class="ep-pfield-label">Hours Authorised By</div>
                                @if($hasClockin && isset($employee->hourauth) && $employee->hourauth->count() > 0)
                                    <div class="ep-pill-row">
                                        @foreach($employee->hourauth as $ha)
                                            @php($person = $ha->user->name ?? 'Unknown')
                                            @php($tone = $personPillTone($person))
                                            <span class="ep-person-pill" style="--ep-person-accent: {{ $tone['bg'] }}; --ep-person-ring: {{ $tone['ring'] }};">
                                                <span class="ep-person-pill__avatar">{{ $personInitials($person) }}</span>
                                                <span class="ep-person-pill__name">{{ $person }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm font-semibold italic text-slate-400">N/A</span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="ep-pfield-label">Holiday Authorised By</div>
                                @if($hasHoliday && isset($employee->holidayAuth) && $employee->holidayAuth->count() > 0)
                                    <div class="ep-pill-row">
                                        @foreach($employee->holidayAuth as $ha)
                                            @php($person = $personName($ha))
                                            @php($tone = $personPillTone($person))
                                            <span class="ep-person-pill" style="--ep-person-accent: {{ $tone['bg'] }}; --ep-person-ring: {{ $tone['ring'] }};">
                                                <span class="ep-person-pill__avatar">{{ $personInitials($person) }}</span>
                                                <span class="ep-person-pill__name">{{ $person }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm font-semibold italic text-slate-400">N/A</span>
                                @endif
                            </div>
                        </div>

                        <div class="ep-prow ep-prow--2">
                            <div class="min-w-0">
                                <div class="ep-pfield-label">HR Approver</div>
                                @if(isset($employee->approvers) && $employee->approvers->count() > 0)
                                    <div class="ep-pill-row">
                                        @foreach($employee->approvers as $ap)
                                            @php($person = $personName($ap))
                                            @php($tone = $personPillTone($person))
                                            <span class="ep-person-pill" style="--ep-person-accent: {{ $tone['bg'] }}; --ep-person-ring: {{ $tone['ring'] }};">
                                                <span class="ep-person-pill__avatar">{{ $personInitials($person) }}</span>
                                                <span class="ep-person-pill__name">{{ $person }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm font-semibold italic text-slate-400">N/A</span>
                                @endif
                            </div>
                            <div class="min-w-0" style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
                                <div class="min-w-0">
                                    <div class="ep-pfield-label">Line Manager</div>
                                    @if(isset($employee->lineManagers) && $employee->lineManagers->count() > 0)
                                        <div class="ep-pill-row">
                                            @foreach($employee->lineManagers as $lm)
                                                @php($person = $personName($lm))
                                                @php($tone = $personPillTone($person))
                                                <span class="ep-person-pill" style="--ep-person-accent: {{ $tone['bg'] }}; --ep-person-ring: {{ $tone['ring'] }};">
                                                    <span class="ep-person-pill__avatar">{{ $personInitials($person) }}</span>
                                                    <span class="ep-person-pill__name">{{ $person }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-sm font-semibold italic text-slate-400">N/A</span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="ep-pfield-label">Pension Enrolled</div>
                                    {!! $hasPension ? $yesBadge : $noBadge !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================ Pension Schemes ============================ --}}
                <div class="intro-y ep-pcard ep-pcard--gold">
                    <div class="ep-pcard__head">
                        <span class="ep-pcard__icon ep-pcard__icon--gold">
                            <i data-lucide="shield"></i>
                        </span>
                        <h2 class="ep-pcard__title">Pension Schemes</h2>
                        <div class="ep-pcard__actions">
                            @if($hasPension)
                                <button id="tabulator-print-PNS" type="button" class="ep-pbtn ep-pbtn--ghost">
                                    <i data-lucide="printer"></i> Print
                                </button>
                                <div class="dropdown">
                                    <button class="dropdown-toggle ep-pbtn ep-pbtn--ghost" aria-expanded="false" data-tw-toggle="dropdown">
                                        <i data-lucide="download"></i> Export <i data-lucide="chevron-down" style="width:13px;height:13px;"></i>
                                    </button>
                                    <div class="dropdown-menu w-40">
                                        <ul class="dropdown-content">
                                            <li>
                                                <a id="tabulator-export-csv-PNS" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                                </a>
                                            </li>
                                            <li>
                                                <a id="tabulator-export-xlsx-PNS" href="javascript:;" class="dropdown-item">
                                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <button data-tw-toggle="modal" data-tw-target="#addEmpPenssionModal" type="button" class="ep-pbtn">
                                    <i data-lucide="plus"></i> Add New Pension
                                </button>
                            @endif
                        </div>
                    </div>

                    @if($hasPension)
                        <div class="ep-psub">
                            <div class="overflow-x-auto scrollbar-hidden">
                                <div id="employeePenssionListTable" data-employee="{{ $employee->id }}" class="table-report table-report--tabulator"></div>
                            </div>
                            <div class="ep-tab-foot">
                                <span id="employeePenssionTableCount" class="ep-tab-foot__count"></span>
                            </div>
                        </div>
                    @else
                        <div class="ep-pbody">
                            <div class="ep-prow" style="grid-template-columns:1fr;">
                                <div class="min-w-0">
                                    <div class="ep-pfield-label">Pension Enrolled</div>
                                    {!! $noBadge !!}
                                </div>
                            </div>
                            <p class="ep-pnote">This employee is not currently enrolled in a pension scheme.</p>
                        </div>
                    @endif
                </div>

                {{-- ============================ Working Pattern ============================ --}}
                <div class="intro-y ep-pcard ep-pcard--blue">
                    <div class="ep-pcard__head">
                        <span class="ep-pcard__icon ep-pcard__icon--blue">
                            <i data-lucide="calendar-range"></i>
                        </span>
                        <h2 class="ep-pcard__title">Working Pattern</h2>
                        <div class="ep-pcard__actions">
                            <button id="tabulator-print-EWP" type="button" class="ep-pbtn ep-pbtn--ghost">
                                <i data-lucide="printer"></i> Print
                            </button>
                            <div class="dropdown">
                                <button class="dropdown-toggle ep-pbtn ep-pbtn--ghost" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="download"></i> Export <i data-lucide="chevron-down" style="width:13px;height:13px;"></i>
                                </button>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a id="tabulator-export-csv-EWP" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                            </a>
                                        </li>
                                        <li>
                                            <a id="tabulator-export-xlsx-EWP" href="javascript:;" class="dropdown-item">
                                                <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <button data-tw-toggle="modal" data-tw-target="#addEmployeeWorkingPatternModal" type="button" class="ep-pbtn">
                                <i data-lucide="plus"></i> Add Working Pattern
                            </button>
                        </div>
                    </div>

                    <div class="ep-psub">
                        <div class="ep-table-toolbar flex flex-col xl:flex-row xl:items-end gap-4">
                            <form id="tabulatorFilterForm-EWP" class="ep-table-toolbar__form xl:flex xl:flex-wrap xl:items-end gap-3 xl:mr-auto">
                                <div class="sm:flex items-center gap-2">
                                    <label class="w-12 flex-none text-sm font-medium text-slate-600">Status</label>
                                    <select id="status-EWP" name="status" class="form-select sm:w-auto">
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                        <option value="2">Archived</option>
                                    </select>
                                </div>
                                <div class="flex gap-2">
                                    <button id="tabulator-html-filter-go-EWP" type="button" class="{{ $tablePrimaryBtn }}">Go</button>
                                    <button id="tabulator-html-filter-reset-EWP" type="button" class="{{ $tableSecondaryBtn }}">Reset</button>
                                </div>
                            </form>

                        </div>

                        <div class="overflow-x-auto scrollbar-hidden">
                            <div id="employeePatternListTable" data-employee="{{ $employee->id }}" class="mt-5 table-report table-report--tabulator"></div>
                        </div>
                    </div>
                </div>
            @endif

            @include('pages.employee.profile.payment-modal')
        </div>
    </div>
</div>
@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    @vite('resources/js/employee-payment-setting.js')
    @vite('resources/js/employee-banks.js')
    @vite('resources/js/employee-penssion-scheem.js')
    @vite('resources/js/employee-working-pattern.js')
@endsection
