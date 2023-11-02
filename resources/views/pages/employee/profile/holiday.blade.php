@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $employee->title->name.' '.$employee->first_name.' '.$employee->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.employee.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5 pb-7">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Employee Holiday</div>
                </div>
                <div class="col-span-6 text-right">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary w-auto mr-0 mb-0">
                        Back to Dashboard
                    </a>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12">
                    @if(!empty($holidayDetails))
                    <div id="employeeHolidayAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                        @foreach($holidayDetails  as $year => $yearDetails)
                            <div class="accordion-item bg-slate-100">
                                <div id="employeeHolidayAccordion-{{ $loop->index }}" class="accordion-header">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#employeeHolidayAccordion-collapse-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="employeeHolidayAccordion-collapse-{{ $loop->index }}">
                                        <span class="font-normal">Holiday Year:</span> {{ date('Y', strtotime($yearDetails['start'])) }} - {{ date('Y', strtotime($yearDetails['end'])) }}
                                        <span class="accordionCollaps"></span>
                                    </button>
                                </div>
                                <div id="employeeHolidayAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="employeeHolidayAccordion-{{ $loop->index }}" data-tw-parent="#employeeHolidayAccordion">
                                    <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                        <div id="employeePatternAccordion" class="accordion accordion-boxed employeeHolidayAccordion">
                                            @foreach($yearDetails['patterns'] as $pattern)
                                                <div class="accordion-item bg-white">
                                                    <div id="employeePatternAccordion-{{ $loop->index }}" class="accordion-header">
                                                        <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }} relative w-full text-lg font-semibold flex" type="button" data-tw-toggle="collapse" data-tw-target="#employeePatternAccordion-collapse-{{ $loop->index }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="employeePatternAccordion-collapse-{{ $loop->index }}">
                                                            <span class="font-normal">Pattern ID:</span> {{ $pattern->id }}
                                                            
                                                            @if(isset($pattern->patterns) && $pattern->patterns->count() > 0)
                                                                <span class="patternHours text-sm ml-auto" style="padding: 7px 49px 0 0;">
                                                                    @foreach($pattern->patterns as $pt)
                                                                        <span>[{{ $pt->day_name }} - {{ $pt->total }}]</span>
                                                                    @endforeach
                                                                </span>
                                                            @endif

                                                            <span class="accordionCollaps"></span>
                                                        </button>
                                                    </div>
                                                    <div id="employeePatternAccordion-collapse-{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" aria-labelledby="employeePatternAccordion-{{ $loop->index }}" data-tw-parent="#employeePatternAccordion">
                                                        <div class="accordion-body text-slate-600 dark:text-slate-500 leading-relaxed">
                                                            <div class="grid grid-cols-12 gap-4">
                                                                <div class="col-span-6 sm:col-span-3">
                                                                    <div class="text-slate-500 font-medium">Start</div>
                                                                    <div class="font-medium">{{ $pattern->effective_from }}</div>
                                                                </div>
                                                                <div class="col-span-6 sm:col-span-3">
                                                                    <div class="text-slate-500 font-medium">End</div>
                                                                    <div class="font-medium">{{ $pattern->end_to }}</div>
                                                                </div>
                                                                <div class="col-span-6 sm:col-span-3">
                                                                    <div class="text-slate-500 font-medium">Entitlement</div>
                                                                    <div class="font-medium flex justify-start items-center">
                                                                        <span style="line-height: 24px;">
                                                                            {{ (isset($pattern->holidayEntitlement) && !empty($pattern->holidayEntitlement) ? $pattern->holidayEntitlement : '00:00') }}
                                                                        </span>
                                                                        <button data-year="{{ $year }}" data-pattern="{{ $pattern->id }}" data-tw-toggle="modal" data-tw-target="#empHolidayAdjustmentModal" class="holidayAdjustmentBtn btn btn-success w-auto px-1 py-1 border-0 text-white ml-2 mr-2">
                                                                            <i data-lucide="repeat-1" class="w-4 h-4"></i>
                                                                        </button>
                                                                        <span class="line-height: 24px;">{{ $pattern->adjustmentHtml }} = {{ $pattern->totalHolidayEntitlement }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-span-6 sm:col-span-3">
                                                                    <div class="text-slate-500 font-medium">Bank Holiday Auto Book</div>
                                                                    <div class="font-medium">{{ (isset($pattern->autoBookedBankHoliday) && !empty($pattern->autoBookedBankHoliday) ? $pattern->autoBookedBankHoliday : '00:00') }}</div>
                                                                </div>
                                                                
                                                                <div class="col-span-12">
                                                                    <table class="table table-bordered table-hover">
                                                                        <thead>
                                                                            <tr>
                                                                                <th class="whitespace-nowrap">Status</th>
                                                                                <th class="whitespace-nowrap">Start Date</th>
                                                                                <th class="whitespace-nowrap">End Date</th>
                                                                                <th class="whitespace-nowrap">Title</th>
                                                                                <th class="whitespace-nowrap">Hour</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @if(isset($pattern->bankHolidays) && !empty($pattern->bankHolidays))
                                                                                @foreach($pattern->bankHolidays as $bhd)
                                                                                    <tr>
                                                                                        <td>Bank Holiday Auto Booked</td>
                                                                                        <td>{{ isset($bhd['start_date']) && !empty($bhd['start_date']) ? date('l jS F, Y', strtotime($bhd['start_date'])) : '' }}</td>
                                                                                        <td>{{ isset($bhd['end_date']) && !empty($bhd['end_date']) ? date('l jS F, Y', strtotime($bhd['end_date'])) : '' }}</td>
                                                                                        <td>{{ isset($bhd['name']) && !empty($bhd['name']) ? $bhd['name'] : '' }}</td>
                                                                                        <td>{{ isset($bhd['hour']) && !empty($bhd['hour']) ? $bhd['hour'] : '00:00' }}</td>
                                                                                    </tr>
                                                                                @endforeach
                                                                            @endif
                                                                        </tbody>
                                                                    </table>
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
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: Edit Calendar Modal -->
    <div id="empHolidayAdjustmentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="empHolidayAdjustmentForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Holiday Hour Adjustment</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="input-group adjustmentInpugGroup">
                            <div class="input-group-text relative">
                                <div class="adjustmentRadioGroup">
                                    <input type="radio" name="adjustmentOpt" value="1" id="adjustmentOpt_1"/>
                                    <label for="adjustmentOpt_1">+</label>
                                </div>
                                <div class="adjustmentRadioGroup argMinus">
                                    <input type="radio" name="adjustmentOpt" value="2" id="adjustmentOpt_2"/>
                                    <label for="adjustmentOpt_2">-</label>
                                </div>
                            </div>
                            <input type="text" disabled class="form-control" placeholder="00:00" name="adjustment">
                        </div>
                        <div class="acc__input-error error-adjustment text-danger mt-2"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateADJ" class="btn btn-primary w-auto">     
                            Update                  
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
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="hr_holiday_year_id" value="0"/>
                        <input type="hidden" name="employee_working_pattern_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Calendar Modal -->


    <!-- BEGIN: Success Modal Content -->
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
                        <button type="button" data-action="NONE" class="btn btn-primary successCloser w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->


@endsection

@section('script')
    @vite('resources/js/employee-global.js')
    @vite('resources/js/employee-holiday.js')
@endsection