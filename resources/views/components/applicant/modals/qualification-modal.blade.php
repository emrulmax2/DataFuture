
    <!-- BEGIN: Add Qualification Modal -->
    <div id="addQualificationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="addQualificationForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Education Qualification</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="highest_academic" class="form-label">Highest Academic Qualification <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Qualification" id="highest_academic" class="form-control w-full" name="highest_academic">
                            <div class="acc__input-error error-highest_academic text-danger mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="awarding_body" class="form-label">Awarding Body <span class="text-danger"></span></label>
                            <input type="text" placeholder="Awarding Body" id="awarding_body" class="form-control w-full" name="awarding_body">
                            <div class="acc__input-error error-awarding_body text-danger mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="subjects" class="form-label">Subjects <span class="text-danger"></span></label>
                            <input type="text" placeholder="Subjects" id="subjects" class="form-control" name="subjects">
                            <div class="acc__input-error error-subjects text-danger mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="result" class="form-label">Result <span class="text-danger"></span></label>
                            <input type="text" placeholder="Result" id="result" class="form-control" name="result">
                            <div class="acc__input-error error-result text-danger mt-2"></div>
                        </div>
                        <div>
                            <label for="degree_award_date" class="form-label">Date Of Award <span class="text-danger"></span></label>
                            <input type="text" placeholder="DD-MM-YYYY" id="degree_award_date" class="form-control datepicker" name="degree_award_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-degree_award_date text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveEducationQualification" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Qualification Modal -->
    

    <!-- BEGIN: Edit Qualification Modal -->
    <div id="editQualificationModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editQualificationForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Education Qualification</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_highest_academic" class="form-label">Highest Academic Qualification <span class="text-danger">*</span></label>
                            <input type="text" placeholder="Qualification" id="edit_highest_academic" class="form-control w-full" name="highest_academic">
                            <div class="acc__input-error error-highest_academic text-danger mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_awarding_body" class="form-label">Awarding Body <span class="text-danger"></span></label>
                            <input type="text" placeholder="Awarding Body" id="edit_awarding_body" class="form-control w-full" name="awarding_body">
                            <div class="acc__input-error error-awarding_body text-danger mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_subjects" class="form-label">Subjects <span class="text-danger"></span></label>
                            <input type="text" placeholder="Subjects" id="edit_subjects" class="form-control" name="subjects">
                            <div class="acc__input-error error-subjects text-danger mt-2"></div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_result" class="form-label">Result <span class="text-danger"></span></label>
                            <input type="text" placeholder="Result" id="edit_result" class="form-control" name="result">
                            <div class="acc__input-error error-result text-danger mt-2"></div>
                        </div>
                        <div>
                            <label for="edit_degree_award_date" class="form-label">Date Of Award <span class="text-danger"></span></label>
                            <input type="text" placeholder="DD-MM-YYYY" id="edit_degree_award_date" class="form-control datepicker" name="degree_award_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-degree_award_date text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateEducationQualification" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="applicant_id" value="{{ isset($apply->id) && $apply->id > 0 ? $apply->id : 0 }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Qualification Modal -->