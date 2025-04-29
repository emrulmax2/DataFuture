@extends('../layout/' . $layout)

@section('subhead')
    <title>Document / ID Card Replacement request</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Document / ID Card Replacement request</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <button data-tw-toggle="modal" data-tw-target="#agentRulesModal"  class="add_btn btn btn-facebook text-white shadow-md ml-1"><i data-lucide="plus" class="w-4 h-4 mr-2"></i> Create new request</button>
            <a href="{{ route('students.dashboard') }}" class=" btn btn-primary text-white shadow-md ml-1"><i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Attendance Semester</label>
                    <select id="term_declaration_id" name="term_declaration_id" class="tom-selects w-full mt-2 sm:mt-0 sm:w-56" >
                        <option value="">Please Select</option>
                        @foreach($term_declarations as $term_declaration)
                            <option value="{{ $term_declaration->id }}">{{ $term_declaration->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-auto" >
                        Go 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
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
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="requestListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->

    <!-- BEGIN: Agent Rule Modal -->
    <div id="agentRulesModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="agentRulesForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Document Requsts</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="letter_set_id" class="form-label">Select the document you would like to request <span class="text-danger">*</span></label>
                            <select id="letter_set_id" name="letter_set_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @foreach($letter_sets as $letter_set)
                                    <option value="{{ $letter_set->id }}">{{ $letter_set->letter_title }}</option>
                                @endforeach
                            </select>
                            <div class="acc__input-error error-letter_set_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 description">
                            <label for="description" class="form-label">Any Other request or comments <span class="text-danger">*</span></label>
                            <textarea id="description" name="description" class="form-control w-full" rows="3"></textarea>
                            
                            <div class="acc__input-error error-description text-danger mt-2"></div>
                        </div>
                        <!--Implement a disclaimer-->
                        <!--Implement the disclaimer-->
                        <div>
                            <div class="font-medium text-base">Service requested <span class="text-danger">*</span></div>
                            
                                <div class="form-check mr-2  my-3">
                                    <input id="service_type1" class="form-check-input" type="radio" name="service_type" value="Same Day (cost £10.00)">
                                    <label class="form-check-label" for="service_type1">Same Day (cost £10.00)</label>
                                </div>
                                <div class="form-check mr-2 my-3 sm:mt-0">
                                    <input id="service_type2" class="form-check-input" type="radio" name="service_type" value="3 Working Days (Free)">
                                    <label class="form-check-label" for="service_type2">3 Working Days (Free)</label>
                                </div>
                            <div class="acc__input-error error-service_type text-danger mt-2"></div>
                        </div>   
                        
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-12 text-left">
                                <div class="font-medium text-base">Disclaimer</div>
                                <div class="pt-1">Please note that 3 working days’ notice are required to obtain any type of college document. In case of emergency documents (except certificate, ID) may be obtained on the same day provided a fee of £10.oo in paid in advance and request is made before 12pm. Awarding body certificate takes 4 weeks from request date. Also all requests are subject to Accounts / Tuition fees, Class attendance clearance.
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-12">
                            <div class="form-check form-switch my-3 justify-start">
                                <input id="student_consent" name="student_consent" value="1" class="form-check-input " type="checkbox">
                                <label class="form-check-label" for="student_consent">Yes I understand <span class="text-danger">*</span></label>
                                
                                <div class="acc__input-error error-student_consent text-danger mt-2"></div>
                            </div>
                            
                        </div>
                        </div>
                        {{--<div>
                            <label for="payment_type" class="form-label">Payment <span class="text-danger">*</span></label>
                            <select id="payment_type" name="payment_type" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="1">Single Payment</option>
                                <option value="2">On Receipt</option>
                            </select>
                            <div class="acc__input-error error-payment_type text-danger mt-2"></div>
                        </div>--}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveRuleBtn" class="btn btn-primary w-auto">     
                            Save                    
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="term_declaration_id" value="{{ $latestTermInfo->id }}"/>
                        <input type="hidden" name="status" value="Pending"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Agent Rule Modal -->

    
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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

@endsection


@section('script')
 @vite(['resources/js/document-requests.js'])
@endsection
