@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
<div class="grid grid-cols-12 gap-6">
    <div class="col-span-12 2xl:col-span-9">
        <div class="grid grid-cols-12 gap-6">
            <!-- BEGIN: Profile Info -->
            @include('pages.students.frontend.dashboard.show-info')
            <!-- END: Profile Info -->
            
            <div class="intro-y mt-5 col-span-12">
                <form method="post" action="#" id="studentAttendanceExcuseForm" enctype="multipart/form-data">
                    <div class="intro-y box">
                        <div class="grid grid-cols-12 gap-0 items-center p-5">
                            <div class="col-span-6">
                                <div class="font-medium text-base">Attendance Excuse</div>
                            </div>
                        </div>
                        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
                        <div class="p-5">
                            <div class="grid grid-cols-12 gap-4"> 
                                <div class="col-span-12 sm:col-span-3">
                                    <div id="pastAttenExcuseAccr" class="accordion accordion-boxed excuseAccordions mb-4">
                                        <div class="accordion-item" style="padding-left: 0; padding-right: 0; padding-bottom: 0;">
                                            <div id="pastAttenExcuseAccrHeading" class="accordion-header">
                                                <button class="accordion-button collapsed flex w-full items-center bg-pending text-white active-text-white px-5" type="button" data-tw-toggle="collapse" data-tw-target="#pastAttenExcuseAccrBody" aria-expanded="false" aria-controls="pastAttenExcuseAccrBody">
                                                    <i data-lucide="list" class="w-4 h-4 mr-2"></i> List of Absent Days
                                                </button>
                                            </div>
                                            <div id="pastAttenExcuseAccrBody" class="accordion-collapse collapse" aria-labelledby="faq-accordion-content-5" data-tw-parent="#pastAttenExcuseAccr">
                                                <div class="accordion-body p-5 pb-1 bg-warning-soft">
                                                    @if(!empty($pastDateList) && count($pastDateList) > 0)
                                                        @php 
                                                            $dateCount = 0;
                                                        @endphp
                                                        @foreach($pastDateList as $plan_id => $contents)
                                                            @if(isset($contents['date_lists']) && !empty($contents['date_lists']))
                                                                <div class="PastCheckedList excuseCheckedList mb-4">
                                                                    <label class="font-medium underline inline-flex items-start moduleLabel"><i data-lucide="check-circle" class="w-4 h-4 mr-2 text-success"></i>{{ $contents['module'] }}</label>
                                                                    @foreach($contents['date_lists'] as $planDate)
                                                                        @php 
                                                                            $label = '';
                                                                            $disabled = 0;
                                                                            if($planDate['status'] == 0):
                                                                                $label = '<strong class="text-danger"> Decision Pending!</strong>';
                                                                                $disabled = 1;
                                                                            elseif($planDate['status'] == 1):
                                                                                $label = '<strong class="text-warning"> Rejected! Submit Again.</strong>';
                                                                                $disabled = 0;
                                                                            endif;
                                                                        @endphp
                                                                        <div class="form-check items-start mt-2 pl-5">
                                                                            <input {{ ($disabled == 1 ? 'Disabled' : '') }} name="excuses[{{ $plan_id }}][]" value="{{ $planDate['id'] }}" id="past_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}" class="form-check-input" type="checkbox">
                                                                            <label class="form-check-label" for="past_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}">
                                                                                {{ $planDate['dates'] }}
                                                                                {!! $label !!}
                                                                            </label>
                                                                        </div>
                                                                        @php 
                                                                            $dateCount += 1;
                                                                        @endphp
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                        @if($dateCount == 0)
                                                            <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Dates not found for excuse.
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                                            <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Dates not found for excuse.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-3">
                                    <div id="futureAttenExcuseAccr" class="accordion accordion-boxed excuseAccordions mb-4">
                                        <div class="accordion-item" style="padding-left: 0; padding-right: 0; padding-bottom: 0;">
                                            <div id="futureAttenExcuseAccrHeading" class="accordion-header">
                                                <button class="accordion-button collapsed flex w-full items-center bg-success text-white active-text-white px-5" type="button" data-tw-toggle="collapse" data-tw-target="#futureAttenExcuseAccrBody" aria-expanded="false" aria-controls="futureAttenExcuseAccrBody">
                                                    <i data-lucide="list" class="w-4 h-4 mr-2"></i> List of Future Days
                                                </button>
                                            </div>
                                            <div id="futureAttenExcuseAccrBody" class="accordion-collapse collapse" aria-labelledby="faq-accordion-content-5" data-tw-parent="#futureAttenExcuseAccr">
                                                <div class="accordion-body p-5 pb-1 bg-success-soft-2">
                                                    @if(!empty($futureDateList) && count($futureDateList) > 0)
                                                        @php 
                                                            $dateCount = 0;
                                                        @endphp
                                                        @foreach($futureDateList as $plan_id => $contents)
                                                            @if(isset($contents['date_lists']) && !empty($contents['date_lists']))
                                                                <div class="futureCheckedList excuseCheckedList mb-4">
                                                                    <label class="font-medium underline inline-flex items-start moduleLabel"><i data-lucide="check-circle" class="w-4 h-4 mr-2 text-success"></i>{{ $contents['module'] }}</label>
                                                                    @foreach($contents['date_lists'] as $planDate)
                                                                        @php 
                                                                            $label = '';
                                                                            $disabled = 0;
                                                                            if($planDate['status'] == 0):
                                                                                $label = '<strong class="text-danger"> Decision Pending!</strong>';
                                                                                $disabled = 1;
                                                                            elseif($planDate['status'] == 1):
                                                                                $label = '<strong class="text-warning"> Rejected! Submit Again.</strong>';
                                                                                $disabled = 0;
                                                                            endif;
                                                                        @endphp
                                                                        <div class="form-check items-start mt-2 pl-5">
                                                                            <input {{ ($disabled == 1 ? 'Disabled' : '') }} name="excuses[{{ $plan_id }}][]" value="{{ $planDate['id'] }}" id="past_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}" class="form-check-input" type="checkbox">
                                                                            <label class="form-check-label" for="past_clas_date_{{ $plan_id }}_{{ $planDate['id'] }}">
                                                                                {{ $planDate['dates'] }}
                                                                                {!! $label !!}
                                                                            </label>
                                                                        </div>
                                                                        @php 
                                                                            $dateCount += 1;
                                                                        @endphp
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                        @if($dateCount == 0)
                                                            <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                                                <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Dates not found for excuse.
                                                            </div>
                                                        @endif
                                                    @else
                                                        <div class="alert alert-pending-soft show flex items-center mb-2" role="alert">
                                                            <i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> Dates not found for excuse.
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-span-12 sm:col-span-6">
                                    <div class="upload_document_row">
                                        <label class="form-label">Upload Documents</label>
                                        <div class="flex justify-start items-start relative">
                                            <label for="addEXCUSEDocument" class="inline-flex items-center justify-center btn btn-facebook  cursor-pointer">
                                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Choose Files
                                            </label>
                                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.doc,.docx" name="document[]" multiple class="absolute w-0 h-0 overflow-hidden opacity-0" id="addEXCUSEDocument"/>
                                            <span id="addEXCUSEDocumentName" class="documentEXCUSEName ml-5"></span>
                                        </div>
                                        <div class="form-help">Upload File (max file size will no more than 5MB and file types are docx,doc,pdf,jpg,png)</div>
                                    </div>
                                    <div class="mt-5">
                                        <label class="form-label">Reason <span class="text-danger">*</span></label>
                                        <textarea class="form-control w-full" name="reason" rows="5" placeholder="Reason"></textarea>
                                        <div class="acc__input-error error-reason text-danger mt-2"></div>
                                    </div>
                                    <div class="mt-5">
                                        <button type="submit" id="submitExcuseBtn" class="btn btn-primary w-auto">
                                            Submit Excuse
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
                                        <div class="acc__input-error error-excuses text-danger mt-2"></div>
                                        <input type="hidden" name="student_id" value="{{ $student->id }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div><!--End 2xl:col-span-9-->  
    @include('pages.students.frontend.dashboard.profile.sidebar')
 
</div><!--End GRID-->   

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
                    <button type="button" data-action="none" class="successCloser btn btn-primary w-24">Ok</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-frontend-global.js')
    @vite('resources/js/student-attendance-excuse.js')
@endsection