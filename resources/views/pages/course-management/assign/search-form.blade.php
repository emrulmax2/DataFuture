<form id="studentSearchForm" method="post" action="#">
    <div class="grid grid-cols-12 gap-0 gap-x-4">
        <div class="col-span-12 sm:col-span-4">
            <div class="grid grid-cols-12 gap-0 gap-x-4">
                <label class="col-span-12 sm:col-span-4 form-label pt-2">Student Search</label>
                <div class="col-span-12 sm:col-span-8">
                    <div class="autoCompleteField" data-table="students">
                        <input type="text" autocomplete="off" id="registration_no" name="student_id" class="form-control registration_no" value="" placeholder="LCC000001"/>
                        <ul class="autoFillDropdown"></ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-4 text-right"></div>
        <div class="col-span-12 sm:col-span-4 text-right">
            <div class="flex justify-end items-center">
                <button id="studentIDSearchBtn" type="submit" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                <button id="resetStudentSearch" type="button" class="btn btn-danger w-auto ml-2" ><i class="w-4 h-4 mr-2" data-lucide="rotate-cw"></i> Reset</button>
                <button id="advanceSearchToggle" type="button" class="btn btn-facebook ml-2 w-auto">Advance Search <i class="w-4 h-4 ml-2" data-lucide="chevron-down"></i></button>
            </div>
        </div>
        <div class="col-span-12 sm:col-span-12">
            <div id="studentSearchAccordionWrap" class="pt-4 mb-2" style="display: none;">
                <div id="studentSearchAccordion" class="accordion accordion-boxed pt-2">
                    <div class="accordion-item">
                        <div id="studentSearchAccordion-1" class="accordion-header">
                            <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-1" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-1">
                                Search By Student
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="studentSearchAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-1" data-tw-parent="#studentSearchAccordion">
                            <div class="accordion-body">
                                <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_id" class="form-label">ID</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="student_id" name="student[student_id]" class="form-control registration_no" value="" placeholder="LCC000001"/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_name" class="form-label">Name</label>
                                        <input type="text" value="" id="student_name" class="form-control" name="student[student_name]">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_dob" class="form-label">DOB</label>
                                        <input type="text" value="" placeholder="DD-MM-YYYY" id="student_dob" class="form-control datepicker" name="student[student_dob]" data-format="DD-MM-YYYY" data-single-mode="true">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_post_code" class="form-label">Post Code</label>
                                        <input type="text" value="" id="student_post_code" class="form-control" name="student[student_post_code]">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_email" class="form-label">Email Address</label>
                                        <input type="text" value="" id="student_email" class="form-control" name="student[student_email]">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_mobile" class="form-label">Mobile No</label>
                                        <input type="text" value="" id="student_mobile" class="form-control" name="student[student_mobile]">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_uhn" class="form-label">UHN</label>
                                        <input type="text" value="" id="student_uhn" class="form-control" name="student[student_uhn]">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_ssn" class="form-label">SSN</label>
                                        <input type="text" value="" id="student_ssn" class="form-control" name="student[student_ssn]">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_abr" class="form-label">Awarding Body Ref</label>
                                        <input type="text" value="" id="student_abr" class="form-control" name="student[student_abr]">
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_status" class="form-label">Student Status</label>
                                        <select id="student_status" class="w-full tom-selects" name="student[student_status][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($allStatuses))
                                                @foreach($allStatuses as $sts)
                                                    <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3"></div>
                                    <div class="col-span-12 sm:col-span-3 text-right pt-7">
                                        <button id="studentSearchBtnSubmit" type="submit" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                                    </div>
                                </div>
                                <input type="hidden" value="0" id="studentSearchStatus" class="form-control" name="student[stataus]">
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div id="studentSearchAccordion-1" class="accordion-header">
                            <button  id="studentGroupSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-1" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-1">
                                Multi Student Search
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="studentSearchAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-1" data-tw-parent="#studentSearchAccordion">
                            <div class="accordion-body">
                                <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="academic_year" class="form-label">Academic Year</label>
                                        <select id="academic_year" class="w-full tom-selects" name="group[academic_year][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($academicYear))
                                                @foreach($academicYear as $acy)
                                                    <option value="{{ $acy->id }}">{{ $acy->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="intake_semester" class="form-label">Intake Semester <span class="text-danger">*</span></label>
                                        <select id="intake_semester" class="w-full tom-selects" name="group[intake_semester][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($semesters))
                                                @foreach($semesters as $sem)
                                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="attendance_semester" class="form-label">Attendance Semester</label>
                                        <select id="attendance_semester" class="w-full tom-selects" name="group[attendance_semester][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($terms))
                                                @foreach($terms as $trm)
                                                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="course" class="form-label flex items-center">Course <span class="text-danger">*</span> <i data-loading-icon="three-dots" class="w-6 h-6 ml-4 theLoading hidden"></i></label>
                                        <select id="course" class="w-full tom-selects" name="group[course][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $crs)
                                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="module" class="form-label">Module</label>
                                        <select id="module" class="w-full tom-selects" name="group[module][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($modules))
                                                @foreach($modules as $mod)
                                                    <option value="{{ $mod->id }}">{{ $mod->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="group" class="form-label">Master Group</label>
                                        <select id="group" class="w-full tom-selects" name="group[group][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($groups))
                                                @foreach($groups as $grps)
                                                    <option value="{{ $grps->id }}">{{ $grps->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="evening_weekend" class="form-label">Evening / Weekend</label>
                                        <select id="evening_weekend" class="form-control" name="group[evening_weekend]">
                                            <option value="">Please Select</option>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="term_status" class="form-label">Student Term Status</label>
                                        <select id="term_status" class="w-full tom-selects" name="group[term_status][]" multiple>
                                            <option value="">Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="student_type" class="form-label">Student Type</label>
                                        <select id="student_type" class="w-full tom-selects" name="group[student_type][]" multiple>
                                            <option value="">Please Select</option>
                                            <option value="UK">UK</option>
                                            <option value="OVERSEAS">OVERSEAS</option>
                                            <option value="BOTH">BOTH</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="group_student_status" class="form-label">Student Status</label>
                                        <select id="group_student_status" class="w-full tom-selects" name="group[group_student_status][]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($allStatuses))
                                                @foreach($allStatuses as $sts)
                                                    <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3 text-right pt-7">
                                        <button id="studentGroupSearchBtnSubmit" type="submit" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                                    </div>
                                    <input type="hidden" id="groupSearchStatus" value="0" class="form-control" name="group[stataus]">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <div id="studentSearchAccordion-3" class="accordion-header">
                            <button  id="assignedStudentTermSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-3" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-3">
                                Assigned Student Search
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="studentSearchAccordion-collapse-3" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-3" data-tw-parent="#studentSearchAccordion">
                            <div class="accordion-body">
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="tree_term_declaration" class="form-label flex items-center">Attendance Semester <i data-loading-icon="three-dots" class="w-6 h-6 ml-4 theLoading hidden"></i></label>
                                        <select id="tree_term_declaration" class="w-full tom-selects" name="tree_term_declaration">
                                            <option value="">Please Select</option>
                                            @if(!empty($terms))
                                                @foreach($terms as $trm)
                                                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3 theTreeCourseWrap hidden">
                                        <label for="tree_course" class="form-label flex items-center">Course <i data-loading-icon="three-dots" class="w-6 h-6 ml-4 theLoading hidden"></i></label>
                                        <select id="tree_course" class="w-full tom-selects" name="tree_course">
                                            <option value="">Please Select</option>
                                            @if(!empty($courses))
                                                @foreach($courses as $crs)
                                                    <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3 theTreeGroupWrap hidden">
                                        <label for="tree_group" class="form-label">Group</label>
                                        <select id="tree_group" class="w-full tom-selects" name="tree_group">
                                            <option value="">Please Select</option>
                                            @if(!empty($groups))
                                                @foreach($groups as $grps)
                                                    <option value="{{ $grps->id }}">{{ $grps->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3 theTreeModuleWrap hidden">
                                        <label for="tree_module" class="form-label">Module</label>
                                        <select id="tree_module" class="w-full tom-selects" name="tree_module">
                                            <option value="">Please Select</option>
                                        </select>
                                    </div>
                                    <div class="col-span-12 text-left theTreeSubmitWrap hidden">
                                        <button id="assignedStudentTermSubmitBtn" type="button" class="btn btn-success text-white w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>