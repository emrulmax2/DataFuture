<!-- BEGIN: Edit Punch Modal -->
{{-- <div id="editPunchNumberDeteilsModal_XXX" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editPunchNumberDeteilsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Punch Number</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5 items-center">
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="employee_punch_number" class="form-label inline-flex font-medium ">Please Swipe or Touch your card </label>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <input id="employee_punch_number" type="password" value="" class="form-control rounded  form-control-lg" name="punch_number" aria-label="default input example">
                            <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto save">     
                        Start Class                      
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
                    <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                    
                    <input type="hidden" name="url" value="{{ route('tutor-attendance.check') }}" />
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                </div>
            </div>
        </form>
    </div>
</div> --}}
<div id="editPunchNumberDeteilsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="editPunchNumberDeteilsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Start Class</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5 items-center">
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="employee_punch_number" class="form-label inline-flex font-medium ">Do you want to start the class now ?</label>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <input id="employee_punch_number" type="hidden" value="" class="form-control rounded  form-control-lg" name="punch_number" aria-label="default input example">
                            <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-auto mr-1">No</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-20 save">     
                        Yes                      
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
                    <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                    <input type="hidden" name="url" value="{{ route('tutor-attendance.startClass') }}" />
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Punch Modal -->
<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="confirmModalForm" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">Different Tutor ?</div>
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">Please Put a note Below, why are you taking this class?</div>
                    
                    <div class="relative w-full min-w-[200px]">
                        <textarea id="note"
                          class="peer h-full focus:ring-0 focus:ring-offset-0 min-h-[100px] w-full resize-none border-b border-0 border-blue-gray-200 bg-transparent pt-4 pb-1.5 font-sans text-sm font-normal text-blue-gray-700 outline outline-0 transition-all placeholder-shown:border-blue-gray-200 focus:border-pink-500 focus:outline-0 disabled:resize-none disabled:border-0 disabled:bg-blue-gray-50"
                          placeholder=" "
                          name = "note"
                        ></textarea>
                        <label id="note" class="after:content[' '] pointer-events-none absolute left-0 -top-1.5 flex h-full w-full select-none text-[14px] font-normal leading-tight text-blue-gray-500 transition-all after:absolute after:-bottom-0 after:block after:w-full after:scale-x-0 after:border-b-2 after:border-pink-500 after:transition-transform after:duration-300 peer-placeholder-shown:text-sm peer-placeholder-shown:leading-[4.25] peer-placeholder-shown:text-blue-gray-500 peer-focus:text-[11px] peer-focus:leading-tight peer-focus:text-pink-500 peer-focus:after:scale-x-100 peer-focus:after:border-pink-500 peer-disabled:text-transparent peer-disabled:peer-placeholder-shown:text-blue-gray-500">
                          Type Here
                        </label>
                        
                    </div>
                    <div class="acc__input-error error-note text-danger mt-2"></div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                    <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                    
                    <input type="hidden" name="url" value="{{ route('tutor-attendance.store') }}" />
                    <input type="hidden" name="start_class" value="1" />
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>

                    <button type="submit" data-id="0" data-action="none" class="save btn btn-danger w-auto">
                        Yes, I confirm
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
                </div>
            </div>
        </div>
        </form>
    </div>
</div>

<div id="startClassConfirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="startClassConfirmModalForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Confirmed Class Teacher</div>
                        <div class="text-slate-500 mt-2 mb-2 confModDesc">Do you want to start this class?</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                        <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                        <input type="hidden" value="Class Started By {{ $employee->full_name }}" name="note"/>
                        
                        <input type="hidden" name="url" value="{{ route('tutor-attendance.store') }}" />
                        <input type="hidden" name="start_class" value="1" />
                        <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>

                        <button type="submit" data-id="0" data-action="none" class="save btn btn-success w-auto text-white">
                            Yes, Start Class
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
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->


<!-- BEGIN: Delete Confirm Modal Content -->
<div id="endClassModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="#" id="endClassModalForm" enctype="multipart/form-data">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">End Now ?</div>
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">Do you want to end this class?</div>
                    
                </div>
                <div class="px-5 pb-8 text-center">
                    <input class="plan-datelist" type="hidden" name="plan_date_list_id" value="">
                    <input type="hidden" value="{{ $employee->id }}" name="employee_id"/>
                    
                    <input type="hidden" name="url" value="{{ route('tutor-attendance.store') }}" />
                    <input type="hidden" name="user_id" value="{{ $employee->user_id }}" />
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>

                    <button type="submit" data-id="0" data-action="none" class="save btn btn-danger w-auto">
                        Yes, I do
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
                </div>
            </div>
        </div>
        </form>
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

<!-- BEGIN: Error Modal Content -->
<div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 errorModalTitle"></div>
                    <div class="text-slate-500 mt-2 errorModalDesc"></div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- END: Error Modal Content -->
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
            </div>
        </div>
    </div>
</div>
<!-- END: Success Modal Content -->