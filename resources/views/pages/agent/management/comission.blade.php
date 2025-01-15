@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-5">
        <h2 class="text-lg font-medium mr-auto">Agent Comission Details</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('agent.management') }}" class="add_btn btn btn-primary shadow-md">Agent Management</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->

    <div class="intro-y box mt-5">
        <div class="grid grid-cols-12 gap-0 items-center p-5">
            <div class="col-span-6">
                <div class="font-medium text-base">Details</div>
            </div>
            <div class="col-span-6 text-right"></div>
        </div>
        <div class="border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="p-5">
            <div class="grid grid-cols-12 gap-4"> 
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Semester</div>
                        <div class="col-span-8 font-medium">{{ (isset($rule->semester->name) && !empty($rule->semester->name) ? $rule->semester->name : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Agent</div>
                        <div class="col-span-8 font-medium">{{ (isset($rule->agentuser->email) && !empty($rule->agentuser->email) ? $rule->agentuser->email : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Code</div>
                        <div class="col-span-8 font-medium">{{ (isset($rule->code) && !empty($rule->code) ? $rule->code : '') }}</div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Comission Mode</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($rule->comission_mode) && $rule->comission_mode > 0 ? ($rule->comission_mode == 1 ? 'Percentage' : 'Fixed Amount') : '') }}
                        </div>
                    </div>
                </div>
                @if(isset($rule->comission_mode) && $rule->comission_mode == 1)
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Percentage</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($rule->percentage) && $rule->percentage > 0 ? $rule->percentage.'%' : '') }}
                        </div>
                    </div>
                </div>
                @elseif(isset($rule->comission_mode) && $rule->comission_mode == 2)
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Amount</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($rule->amount) && $rule->amount > 0 ? number_format($rule->amount, 2) : '') }}
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Period</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($rule->period) && $rule->period > 0 ? ($rule->period == 1 ? 'Full Course' : 'Year 1') : '') }}
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Payment</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($rule->payment_type) && $rule->payment_type > 0 ? ($rule->payment_type == 1 ? 'Single Payment' : 'On Receipt') : '') }}
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">No of Students</div>
                        <div class="col-span-8 font-medium" id="noOfStdCount">
                            0
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                    <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
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
                <button data-comissionruleid="{{ $rule->id }}" data-tw-toggle="modal" style="display: none;" data-tw-target="#comissionGenerateModal" id="generateComissionBtn" class="text-white btn btn-success w-1/2 sm:w-auto">
                    <i data-lucide="pound-sterling" class="w-4 h-4 mr-2"></i> Generate Comission
                </button>
                <a href="{{ route('agent.management.comission.list.export', [$rule->semester_id, $rule->agent_user_id, $rule->code]) }}" class="btn btn-facebook text-white ml-1"><i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export</a>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="agentComissionListTable" data-semester="{{ $rule->semester_id }}" data-agent="{{ $rule->agent_user_id }}" data-code="{{ $rule->code }}" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->

    <!-- BEGIN: Comission Modal -->
    <div id="comissionGenerateModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="agentRulesForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Agent Comission</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <table class="table table-bordered table-sm" id="comissionsPaymentTable">
                            <thead>
                                <tr>
                                    <th>Receipt ID</th>
                                    <th>Date</th>
                                    <th>Year</th>
                                    <th>Receipt Amount</th>
                                    <th>Comission Payable</th>
                                    <th>Paid Date</th>
                                    <th>Paid Amount</th>
                                    <th>Remittance Ref.</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                            </tbody>
                        </table>
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
                        <input type="hidden" name="agent_comission_rule_id" value="{{ $rule->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Comission Modal -->
    
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
    @vite('resources/js/agent-management-comission.js')
@endsection