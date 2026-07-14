@extends('../layout/my-account')

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')
    @include('pages.users.my-account.show-info')

    @php
        $formatPayslipMonth = function ($monthYear) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->format('F, Y');
            } catch (\Throwable $e) {
                return $monthYear ?: '-';
            }
        };

        $downloadUrl = function ($record) {
            $type = strtolower($record->type ?? '');

            if($type === 'p60'):
                return Storage::disk('s3')->temporaryUrl(
                    'public/employee_payslips/'.$record->type.'_'.$record->holiday_year_id.'/p60/'.$record->file_name,
                    now()->addMinutes(120)
                );
            endif;

            return Storage::disk('s3')->temporaryUrl(
                'public/employee_payslips/'.$record->month_year.(in_array($type, ['p45', 'p60']) ? '/'.$type : '').'/'.$record->file_name,
                now()->addMinutes(120)
            );
        };
    @endphp

    <section class="my-account-card myhr-payslip" data-screen-label="Employee Payslip List">
        <div class="my-account-card__header">
            <div class="my-account-card__icon">
                <i data-lucide="file-text"></i>
            </div>
            <h2>Employee Payslip List</h2>
        </div>

        <div class="myhr-payslip__body">
            @if(!empty($holidayYearIds) && count($holidayYearIds) > 0)
                <div id="myhrPayslipAccordion" class="myhr-payslip-years">
                    @foreach($holidayYearIds as $holidayYearId)
                        @php
                            $holidayYearData = App\Models\HrHolidayYear::find($holidayYearId);
                        @endphp

                        @if($holidayYearData)
                            @php
                                $yearKey = 'myhr-payslip-year-'.$holidayYearData->id;
                                $payslipRecords = $paySlipUploadSync->where('type', 'Payslips')->where('holiday_year_id', $holidayYearData->id)->sortByDesc('month_year');
                                $p45Records = $paySlipUploadSync->where('type', 'P45')->where('holiday_year_id', $holidayYearData->id)->sortByDesc('month_year');
                                $p60Records = $paySlipUploadSync->where('type', 'P60')->where('holiday_year_id', $holidayYearData->id);
                            @endphp

                            <div class="myhr-payslip-year">
                                <button
                                    class="myhr-payslip-year__toggle"
                                    type="button"
                                    data-myhr-payslip-toggle="collapse"
                                    data-myhr-payslip-target="#{{ $yearKey }}"
                                    aria-expanded="true"
                                    aria-controls="{{ $yearKey }}"
                                >
                                    <span><span>Tax Year:</span> {{ $holidayYearData->holiday_year }}</span>
                                    <span class="myhr-payslip-toggle-mark" aria-hidden="true"></span>
                                </button>

                                <div id="{{ $yearKey }}" class="myhr-payslip-collapse is-open">
                                    <div class="myhr-payslip-year__body">
                                        <div class="myhr-payslip-section">
                                            <button
                                                class="myhr-payslip-section__toggle"
                                                type="button"
                                                data-myhr-payslip-toggle="collapse"
                                                data-myhr-payslip-target="#{{ $yearKey }}-payslips"
                                                aria-expanded="true"
                                                aria-controls="{{ $yearKey }}-payslips"
                                            >
                                                <span>Payslips</span>
                                                <span class="myhr-payslip-toggle-mark" aria-hidden="true"></span>
                                            </button>

                                            <div id="{{ $yearKey }}-payslips" class="myhr-payslip-collapse is-open">
                                                <div class="myhr-payslip-table">
                                                    <div class="myhr-payslip-table__head">
                                                        <div>Month</div>
                                                        <div>Action</div>
                                                    </div>
                                                    @forelse($payslipRecords as $record)
                                                        <div class="myhr-payslip-table__row">
                                                            <div>{{ $formatPayslipMonth($record->month_year) }}</div>
                                                            <div>
                                                                <a href="{{ $downloadUrl($record) }}" target="_blank" class="myhr-payslip-download">
                                                                    <i data-lucide="download" class="w-4 h-4"></i>
                                                                    Download
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <div class="myhr-payslip-empty-row">
                                                            <i data-lucide="alert-octagon" class="w-5 h-5"></i>
                                                            Payslip data not found!
                                                        </div>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>

                                        @if($p45Records && $p45Records->count() > 0)
                                            <div class="myhr-payslip-section">
                                                <button
                                                    class="myhr-payslip-section__toggle"
                                                    type="button"
                                                    data-myhr-payslip-toggle="collapse"
                                                    data-myhr-payslip-target="#{{ $yearKey }}-p45"
                                                    aria-expanded="true"
                                                    aria-controls="{{ $yearKey }}-p45"
                                                >
                                                    <span>P45</span>
                                                    <span class="myhr-payslip-toggle-mark" aria-hidden="true"></span>
                                                </button>

                                                <div id="{{ $yearKey }}-p45" class="myhr-payslip-collapse is-open">
                                                    <div class="myhr-payslip-table">
                                                        <div class="myhr-payslip-table__head">
                                                            <div>Month</div>
                                                            <div>Action</div>
                                                        </div>
                                                        @foreach($p45Records as $record)
                                                            <div class="myhr-payslip-table__row">
                                                                <div>{{ $formatPayslipMonth($record->month_year) }}</div>
                                                                <div>
                                                                    <a href="{{ $downloadUrl($record) }}" target="_blank" class="myhr-payslip-download">
                                                                        <i data-lucide="download" class="w-4 h-4"></i>
                                                                        Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif

                                        @if($p60Records && $p60Records->count() > 0)
                                            <div class="myhr-payslip-section">
                                                <button
                                                    class="myhr-payslip-section__toggle"
                                                    type="button"
                                                    data-myhr-payslip-toggle="collapse"
                                                    data-myhr-payslip-target="#{{ $yearKey }}-p60"
                                                    aria-expanded="true"
                                                    aria-controls="{{ $yearKey }}-p60"
                                                >
                                                    <span>P60</span>
                                                    <span class="myhr-payslip-toggle-mark" aria-hidden="true"></span>
                                                </button>

                                                <div id="{{ $yearKey }}-p60" class="myhr-payslip-collapse is-open">
                                                    <div class="myhr-payslip-table">
                                                        <div class="myhr-payslip-table__head">
                                                            <div>Year</div>
                                                            <div>Action</div>
                                                        </div>
                                                        @foreach($p60Records as $record)
                                                            <div class="myhr-payslip-table__row">
                                                                <div>{{ optional($record->holidayYear)->holiday_year ?: $holidayYearData->holiday_year }}</div>
                                                                <div>
                                                                    <a href="{{ $downloadUrl($record) }}" target="_blank" class="myhr-payslip-download">
                                                                        <i data-lucide="download" class="w-4 h-4"></i>
                                                                        Download
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="myhr-payslip-empty-state">
                    <i data-lucide="alert-octagon" class="w-6 h-6"></i>
                    No payslip upload records found!
                </div>
            @endif
        </div>
    </section>
@endsection

@section('script')
    @vite('resources/js/employee-global.js')
@endsection
