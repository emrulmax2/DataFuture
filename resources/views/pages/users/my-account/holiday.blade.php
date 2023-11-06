@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">My Holidays</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.users.my-account.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5 pb-7">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Employee Holiday</div>
                </div>
                <div class="col-span-6 text-right">
                    <a href="{{ route('user.account') }}" class="btn btn-primary w-auto mr-0 mb-0">
                        Back to Profile
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


@endsection