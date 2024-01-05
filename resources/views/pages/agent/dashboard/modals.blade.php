<!-- BEGIN: Edit Punch Modal -->
<div id="addDeteilsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="addStudentDetailsForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Application Information</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4 gap-y-5 items-center">
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="first_name" class="form-label font-medium ">First Name <span class="text-danger">*</span></label>
                            <input id="first_name" type="text" value="" class="form-control rounded  form-control-lg" name="first_name" aria-label="default input example"> 
                            <div  id="error-first_name"  class="acc__input-error error-first_name text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="last_name" class="form-label font-medium ">Last Name <span class="text-danger">*</span></label>
                            <input id="last_name" type="text" value="" class="form-control rounded  form-control-lg" name="last_name" aria-label="default input example">
                            <div  id="error-last_name"  class="acc__input-error error-last_name text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="mobile" class="form-label inline-flex font-medium ">Mobile <span class="text-danger">*</span></label>
                            <input id="mobile" type="text" value="" class="form-control rounded  form-control-lg" name="mobile" aria-label="default input example">
                            <div  id="error-mobile"  class="acc__input-error error-mobile text-danger mt-2"></div>
                        </div>
                        
                        <div class="intro-y col-span-12 sm:col-span-6">
                            <label for="email" class="form-label inline-flex font-medium ">Email <span class="text-danger">*</span></label>
                            <input id="email" type="text" value="" class="form-control rounded  form-control-lg" name="email" aria-label="default input example">
                            <div  id="error-email"  class="acc__input-error error-email text-danger mt-2"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="savePD" class="btn btn-primary w-auto save">     
                        Start Application                      
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
                    <input type="hidden" value="{{ $user->id }}" name="user_id"/>
                    
                    <input type="hidden" name="url" value="{{ route('agent.apply.check') }}" />
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Punch Modal -->
<!-- BEGIN: Delete Confirm Modal Content -->
<div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content w-full">
            <div class="modal-body p-0">
                <div class="p-5 text-center">
                    <i data-lucide="message-square" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                    <div class="text-3xl mt-5 confModTitle">Verify Mobile & Email</div>
                    <div class="text-slate-500 mt-2 mb-2 confModDesc">Please verify codes in order to apply</div>
                    <div id="modal-emailverified" class="form-inline">
                        <form method="POST" action="#" id="confirmModalForm1" enctype="multipart/form-data">
                        <input class="id" type="hidden" name="id" value="">
                        <input type="hidden" name="url" value="{{ route('agent.apply.email.verify') }}" />
                        <input type="hidden" name="user_id" value="{{ $user->id }}" />
                        <label for="horizontal-form-1" class="form-label w-20 text-left inline-flex"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning"></i> Email </label>
                        <label id="horizontal-email" for="horizontal-email" class="form-label w-40 text-left">emrulmax2@gmail.com</label>
                        <input id="horizontal-form-1" name="email_verify_code" type="text" class="form-control w-20 mr-1" placeholder="XXXX">
                        <input name="type" value="email" type="hidden">
                        <button id="resend-email" type="button" data-id="0" data-action="none" class="resend-email btn btn-primary w-auto ml-auto">
                            Resend
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
                        <button type="submit" data-id="0" data-action="none" class="save btn btn-danger w-auto">
                            Verify
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
                        
                        <div class="acc__input-error error-email_verify_code text-danger mt-2 w-full text-right"></div>
                        </form>
                    </div>
                    <div  id="modal-mobileverified" class="form-inline mt-5">
                        
                        <form method="POST" action="#" id="confirmModalForm2" enctype="multipart/form-data">
                        <input class="id" type="hidden" name="id" value="">
                        <input type="hidden" name="url" value="{{ route('agent.apply.verify') }}" />
                        <input type="hidden" name="user_id" value="{{ $user->id }}" />
                        <label for="horizontal-form-2" class="form-label w-20 text-left inline-flex"><i data-lucide="alert-circle" class="w-4 h-4 mr-2 text-warning"></i> Mobile</label>
                        <label id="horizontal-mobile" for="horizontal-phone" class="form-label w-40 text-left">+8801817718335</label>
                        <input id="horizontal-form-2" name="verify_code" type="text" class="form-control w-20 mr-1" placeholder="XXXX">
                        <input name="type" value="mobile" type="hidden">
                        <button id="resend-mobile" type="button" data-id="0" data-action="none" class="resend-mobile btn btn-primary  w-auto ml-auto">
                            Resend
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
                        <button type="submit" data-id="0" data-action="none" class="save btn btn-danger w-auto">
                            Verify
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
                        <div class="acc__input-error error-verify_code text-danger mt-2 w-full text-right"></div>
                        </form>
                    </div>
                </div>
                <div class="px-5 pb-8 text-center">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">Cancel</button>
                </div>
            </div>
        </div>
        
    </div>
</div>
<!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmDeleteModal" class="modal" tabindex="-1" aria-hidden="true">
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
                        <button type="button" data-id="0" data-action="delete" class="agreeWith btn btn-danger w-auto">Yes, I agree <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
                        </svg></button>
                    </div>
                </div>
            </div>
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