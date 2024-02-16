<!-- BEGIN: Add Module Modal -->
<div id="moduleAssesmentAddModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="moduleAssesmentAddForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Assesment</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4 gap-y-2">
                            <div class="col-span-12">
                                <label for="assessment_type_id" class="form-label">Assesment <span class="text-danger">*</span></label>
                                <select id="assessment_type_id" class="assementlccTom lcc-tom-select w-full" name="assessment_type_id">
                                    <option value="" selected>Please Select</option>
                                    @if(!empty($assementTypes))
                                        @foreach($assementTypes as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} - {{ $t->code }}</option>
                                        @endforeach 
                                    @endif 
                                </select>
                                <div class="acc__input-error error-assessment_type_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 border-b">
                                <label for="is_result_segment" class="form-label">Please Select Result Set from Result segment</label>
                                    <input type="hidden"  id="is_result_segment" class="form-check-input" name="is_result_segment" value="1">
                            </div>    
                            <div class="col-span-12">
                                
                                @if(!empty($gradesList))
                                   @foreach($gradesList as $grade)
                                   <div data-tw-merge class="flex items-center mt-2">
                                        <input id="checkbox-switch-{{ $grade->id }}" data-tw-merge type="checkbox" name="grade[]" value="{{ $grade->id }}" checked class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                        <label data-tw-merge for="checkbox-switch-{{ $grade->id }}"  class="cursor-pointer ml-2"> {{ $grade->name }}</label>
                                        
                                    </div>
                                    @endforeach 
                                @endif 
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveModuleAssesment" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="course_module_id" value="{{ $module->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Module Modal -->

    <!-- BEGIN: Edit Module Modal -->
    <div id="moduleAssesmentEditModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="moduleAssesmentEditForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Assesment</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4 gap-y-5">
                            <div class="col-span-12">
                                <label for="assessment_type_id" class="form-label">Assesment <span class="text-danger">*</span></label>
                                <select id="assessment_type_id" class="assementlccTom lcc-tom-select w-full" name="assessment_type_id">
                                    <option value="" selected>Please Select</option>
                                    @if(!empty($assementTypes))
                                        @foreach($assementTypes as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} - {{ $t->code }}</option>
                                        @endforeach 
                                    @endif 
                                </select>
                                <div class="acc__input-error error-assessment_type_id text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 border-b">
                                <label for="is_result_segment" class="form-label">Please Select Result Set from Result segment</label>
                                    <input type="hidden"  id="is_result_segment" class="form-check-input" name="is_result_segment" value="1">
                            </div>    
                            <div class="col-span-12">
                                
                                @if(!empty($gradesList))
                                   @foreach($gradesList as $grade)
                                    <div data-tw-merge class="flex items-center mt-2">
                                            <input id="checkbox-switch-{{ $grade->id }}" data-tw-merge type="checkbox" name="grade[]" value="{{ $grade->id }}" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-{{ $grade->id }}"  class="cursor-pointer ml-2"> {{ $grade->name }}</label>
                                        </div>
                                    @endforeach 
                                @endif 
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateModuleAssesment" class="btn btn-primary w-auto">
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
                        <input type="hidden" name="course_module_id" value="{{ $module->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Module Modal -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalCMA" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitleCMA">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDescCMA"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-id="0" data-action="none" class="agreeWithCMA btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->