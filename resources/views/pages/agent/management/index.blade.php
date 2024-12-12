@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Agent Management</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('agent-user.index') }}" class="add_btn btn btn-primary shadow-md">Back to List</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Intek Semester</label>
                    <select id="semister_id" name="semister_id" class="tom-selects w-full mt-2 sm:mt-0 sm:w-56" >
                        <option value="">Please Select</option>
                        @if($semesters->count() > 0)
                            @foreach($semesters as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        @endif
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
            <div class="flex mt-5 sm:mt-0">
                <button id="tabulator-print" class="btn btn-outline-secondary w-1/2 sm:w-auto">
                    <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                </button>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden agentRefListWrap mt-5" style="display: none;"></div>
    </div>
    <!-- END: HTML Table Data -->

    <!-- BEGIN: Agent Rule Modal -->
    <div id="agentRulesModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="agentRulesForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Agent Rule</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="comission_mode" class="form-label">Comission <span class="text-danger">*</span></label>
                            <select id="comission_mode" name="comission_mode" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="1">Percentage</option>
                                <option value="2">Fixed Amount</option>
                            </select>
                            <div class="acc__input-error error-comission_mode text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 percentageWrap" style="display: none;">
                            <label for="percentage" class="form-label">Percentage <span class="text-danger">*</span></label>
                            <input id="percentage" step="1" type="number" name="percentage" class="form-control w-full">
                            <div class="acc__input-error error-percentage text-danger mt-2"></div>
                        </div>
                        <div class="mt-3 fixedAmountWrap" style="display: none;">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <input id="amount" step="any" type="number" name="amount" class="form-control w-full">
                            <div class="acc__input-error error-amount text-danger mt-2"></div>
                        </div>
                        <div>
                            <label for="period" class="form-label">Period <span class="text-danger">*</span></label>
                            <select id="period" name="period" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="1">Full Course</option>
                                <option value="2">Year 1</option>
                            </select>
                            <div class="acc__input-error error-period text-danger mt-2"></div>
                        </div>
                        <div>
                            <label for="payment_type" class="form-label">Payment <span class="text-danger">*</span></label>
                            <select id="payment_type" name="payment_type" class="form-control w-full">
                                <option value="">Please Select</option>
                                <option value="1">Single Payment</option>
                                <option value="2">On Receipt</option>
                            </select>
                            <div class="acc__input-error error-payment_type text-danger mt-2"></div>
                        </div>
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
                        <input type="hidden" name="agent_user_id" value="0"/>
                        <input type="hidden" name="code" value="'"/>
                        <input type="hidden" name="semester_id" value="0"/>
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
    @vite('resources/js/agent-management.js')
@endsection