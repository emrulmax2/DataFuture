@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-5">
        <h2 class="text-lg font-medium mr-auto">Agent Comission Details</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('agent.management.comission', [$comission->semester_id, $comission->agent_user_id]) }}" class="add_btn btn btn-primary shadow-md">Back to List</a>
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
                        <div class="col-span-4 text-slate-500 font-medium">Name</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($comission->agent->full_name) && !empty($comission->agent->full_name) ? $comission->agent->full_name : '') }}
                            {{ (isset($comission->agent->organization) && !empty($comission->agent->organization) ? ' ('.$comission->agent->organization.')' : '') }}
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Email</div>
                        <div class="col-span-8 font-medium">{{ (isset($comission->agent->email) && !empty($comission->agent->email) ? $comission->agent->email : '') }}</div>
                    </div>
                </div>
                @if(isset($comission->agent->address_id) && $comission->agent->address_id > 0)
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Address</div>
                        <div class="col-span-8 font-medium">{!! (isset($comission->agent->address->full_address) && !empty($comission->agent->address->full_address) ? $comission->agent->address->full_address : '') !!}</div>
                    </div>
                </div>
                @else 
                <div class="col-span-12 sm:col-span-3"></div>
                @endif
                <div class="col-span-12 sm:col-span-3"></div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Remittance Ref</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($comission->remittance_ref) && !empty($comission->remittance_ref) ? $comission->remittance_ref : '') }}
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Generate Date</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($comission->entry_date) && !empty($comission->entry_date) ? date('jS F, Y', strtotime($comission->entry_date)) : '') }}
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Intake Semester</div>
                        <div class="col-span-8 font-medium">
                            {{ (isset($comission->semester->name) && !empty($comission->semester->name) ? $comission->semester->name : '') }}
                        </div>
                    </div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <div class="grid grid-cols-12 gap-0">
                        <div class="col-span-4 text-slate-500 font-medium">Remittance Total</div>
                        <div class="col-span-8 font-bold">
                            {{ Number::currency($comission->comissions->sum('amount'), in: 'GBP') }}
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="agentComissionDetailsListTable" data-comission="{{ $comission->id }}" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    
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
    @vite('resources/js/agent-management-comission-details.js')
@endsection