    
    <!-- BEGIN: Add Modal -->
    <div id="addVisitModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addVisitForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add New Visits</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="visitTypeId" class="form-label inline-flex">Visit Type <span class="text-danger"> *</span></label>
                            <select id="visitTypeId" name="visit_type" class="tom-selects w-full lccToms tomRequire">
                                <option  value="">Please Select</option>   
                                <option  value="academic">Academic</option>   
                                <option  value="non-academic">Non-Academic</option>
                            </select>
                            <div class="acc__input-error error-visit_type text-danger mt-2"></div>
                        </div>
                        <div class="visit-student-info hidden">
                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="terms" class="form-label inline-flex">Terms <span class="text-danger"> *</span></label>
                                <select id="terms" name="term_declaration_id" class="tom-selects w-full lccToms tomRequire">
                                    <option  value="">Please Select</option>   
                                    @foreach($termDeclarations as $termDeclaration)
                                        <option value="{{ $termDeclaration->id }}">{{ $termDeclaration->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-visit_type text-danger mt-2"></div>
                            </div>
                            <div id="modulesContainer" class="intro-y col-span-12 sm:col-span-4">
                                <label for="modules" class="form-label inline-flex">Modules <span class="text-danger"> *</span>
                                <i data-loading-icon='oval' class="loading-icon hidden w-4 h-4 ml-1"></i>
                                </label>
                                <select id="modules" name="plan_id" class="tom-selects w-full lccToms tomRequire">
                                    <option  value="">Please Select</option>  
                                </select>
                                <div class="acc__input-error error-visit_type text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="input-wizard-2" class="form-label inline-flex">Visit Date <span class="text-danger"> *</span></label>
                            <input id="input-wizard-2" name="visit_date" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="form-control datepicker require"  data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-visit_date text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="visitDurationId" class="form-label inline-flex">Visit Duration <span class="text-danger"> *</span></label>
                            <select id="visitDurationId" name="visit_duration" class="tom-selects w-full lccToms tomRequire">
                                <option  value="">Please Select</option>   
                                <option value="30 minutes">30 minutes</option>
                                <option value="60 minutes">60 minutes</option>
                                <option value="90 minutes">90 minutes</option>
                                <option value="120 minutes">120 minutes</option>
                            </select>
                            <div class="acc__input-error error-visit_duration text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="visit_notes" class="form-label inline-flex">Notes</label>
                            <textarea id="visit_notes" class="form-control w-full" rows="4" name="visit_notes"></textarea>
                        </div>

                        <input type="hidden" name="student_id" value="{{ $student->id }}" />
                    </div>
                    <div class="modal-footer">
                        
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveVisit" class="btn btn-primary w-auto">     
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
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->
    <!-- BEGIN: Edit Modal -->
    <div id="editVisitModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editVisitForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Visit</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="modal-body">
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="visitTypeIdEdit" class="form-label inline-flex">Visit Type <span class="text-danger"> *</span></label>
                            <select id="visitTypeIdEdit" name="visit_type" class="tom-selects w-full lccToms tomRequire">
                                <option  value="">Please Select</option>   
                                <option  value="academic">Academic</option>   
                                <option  value="non-academic">Non-Academic</option>
                            </select>
                            <div class="acc__input-error error-visit_type text-danger mt-2"></div>
                        </div>
                        <div class="visit-student-info-edit hidden">
                            <div class="intro-y col-span-12 sm:col-span-4">
                                <label for="termsEdit" class="form-label inline-flex">Terms <span class="text-danger"> *</span></label>
                                <select id="termsEdit" name="term_declaration_id" class="tom-selects w-full lccToms tomRequire">
                                    <option  value="">Please Select</option>   
                                    @foreach($termDeclarations as $termDeclaration)
                                        <option value="{{ $termDeclaration->id }}">{{ $termDeclaration->name }}</option>
                                    @endforeach
                                </select>
                                <div class="acc__input-error error-visit_type text-danger mt-2"></div>
                            </div>
                            <div id="modulesContainerEdit" class="intro-y col-span-12 sm:col-span-4">
                                <label for="modulesEdit" class="form-label inline-flex">Modules <span class="text-danger"> *</span>
                                
                                <i data-loading-icon='oval' class="loading-icon hidden w-4 h-4 ml-1"></i>
                                </label>
                                <select id="modulesEdit" name="plan_id" class="tom-selects w-full lccToms tomRequire">
                                    <option  value="">Please Select</option>   
                                </select>
                                <div class="acc__input-error error-visit_type text-danger mt-2"></div>
                            </div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="visitDateEdit" class="form-label inline-flex">Visit Date <span class="text-danger"> *</span></label>
                            <input id="visitDateEdit" name="visit_date" type="text" placeholder="DD-MM-YYYY" autocomplete="off" class="form-control datepicker require"  data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-visit_date text-danger mt-2"></div>
                        </div>
                        <div class="intro-y col-span-12 sm:col-span-4">
                            <label for="visitDurationIdEdit" class="form-label inline-flex">Visit Duration <span class="text-danger"> *</span></label>
                            <select id="visitDurationIdEdit" name="visit_duration" class="tom-selects w-full lccToms tomRequire">
                                <option  value="">Please Select</option>   
                                <option value="30 minutes">30 minutes</option>
                                <option value="60 minutes">60 minutes</option>
                                <option value="90 minutes">90 minutes</option>
                                <option value="120 minutes">120 minutes</option>
                            </select>
                            <div class="acc__input-error error-visit_duration text-danger mt-2"></div>
                        </div>
                        <div class="col-span-12">
                            <label for="visit_notes_edit" class="form-label inline-flex">Notes</label>
                            <textarea id="visit_notes_edit" class="form-control w-full" rows="4" name="visit_notes"></textarea>
                        </div>

                        <input type="hidden" name="student_id" value="{{ $student->id }}" />
                    </div>
                    </div>
                    <div class="modal-footer">
                        
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateVisit" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="id" value="0" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->
    
    <!-- BEGIN: Show Visit Modal -->
    <div id="showVisitModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editVisiForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Show Visit Information</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <table id="showHtml" class="table table-bordered table-striped">
                            <tbody>
                                <tr>
                                    <td class="w-1/3 font-medium">Visit Type</td>
                                    <td class="w-2/3"><span id="visitTypeShow"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1/3 font-medium">Terms</td>
                                    <td class="w-2/3"><span id="termsShow"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1/3 font-medium">Modules</td>
                                    <td class="w-2/3"><span id="modulesShow"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1/3 font-medium">Visit Date</td>
                                    <td class="w-2/3"><span id="visitDateShow"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1/3 font-medium">Visit Duration</td>
                                    <td class="w-2/3"><span id="visitDurationShow"></span></td>
                                </tr>
                                <tr>
                                    <td class="w-1/3 font-medium">Notes</td>
                                    <td class="w-2/3"><span id="visitNotesShow"></span></td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="font-medium">Created By</td>
                                    <td><span id="createdByShow"></span></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Updated By</td>
                                    <td><span id="updatedByShow"></span></td>
                                </tr>
                                <tr>
                                    <td class="font-medium">Attendance Deleted By</td>
                                    <td><span id="attendanceDeletedByShow"></span></td>
                                </tr>
                            </tbody>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Show Visit Modal -->

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


    <!-- BEGIN: Error Modal Content -->
    <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-orange-400 mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 errorModalTitle">Error Found</div>
                        <div class="text-slate-500 mt-2 errorModalDesc"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Error Modal Content -->

    
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