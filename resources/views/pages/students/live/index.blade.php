@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Live Students</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
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
                        <button id="studentIDSearchBtn" type="button" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
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
                                                <label for="application_no" class="form-label">Application Ref. No.</label>
                                                <input type="text" value="" id="application_no" class="form-control" name="student[application_no]">
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
                                                <button id="studentSearchSubmitBtn" type="button" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                                            </div>
                                        </div>
                                        <input type="hidden" value="0" id="studentSearchStatus" class="form-control" name="student[stataus]">
                                    </div>
                                </div>
                            </div>
                            <div class="accordion-item">
                                <div id="studentSearchAccordion-1" class="accordion-header">
                                    <button  id="studentGroupSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-1" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-1">
                                        Group Search
                                        <span class="accordionCollaps"></span>
                                    </button>
                                </div>
                                <div id="studentSearchAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-1" data-tw-parent="#studentSearchAccordion">
                                    <div class="accordion-body">
                                        <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                                                <select id="academic_year" class="w-full tom-selects" multiple name="group[academic_year][]">
                                                    <option value="">Please Select</option>
                                                    @if(!empty($academicYear))
                                                        @foreach($academicYear as $acy)
                                                            <option value="{{ $acy->id }}">{{ $acy->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <div class="acc__input-error error-academic_year text-danger mt-2"></div>
                                            </div>
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="intake_semester" class="form-label">Intake Semester </label>
                                                <select id="intake_semester" class="w-full tom-selects" multiple name="group[intake_semester][]">
                                                    <option value="">Please Select</option>
                                                    @if(!empty($semesters))
                                                        @foreach($semesters as $sem)
                                                            <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <div class="acc__input-error error-intake_semester text-danger mt-2"></div>
                                            </div>
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="attendance_semester" class="form-label">Attendance Semester <span class="text-danger">*</span></label>
                                                <select id="attendance_semester" class="w-full tom-selects" multiple name="group[attendance_semester][]">
                                                    <option value="">Please Select</option>
                                                    
                                                </select>
                                                <div class="acc__input-error error-attendance_semester text-danger mt-2"></div>
                                            </div>
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="course" class="form-label">Course </label>
                                                <select id="course" class="w-full tom-selects" multiple name="group[course][]">
                                                    <option value="">Please Select</option>
                                                    @if(!empty($courses))
                                                        @foreach($courses as $crs)
                                                            <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                                <div class="acc__input-error error-course text-danger mt-2"></div>
                                            </div>
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="group" class="form-label">Master Group</label>
                                                <select id="group" class="w-full tom-selects" multiple name="group[group][]">
                                                    <option value="">Please Select</option>
                                                </select>
                                            </div>
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="evening_weekend" class="form-label">Evening / Weekend</label>
                                                <select id="evening_weekend" class="w-full tom-selects" name="group[evening_weekend]">
                                                    <option value="">Please Select</option>
                                                    <option value="1">Yes</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="student_type" class="form-label">Student Type</label>
                                                <select id="student_type" class="w-full tom-selects" multiple name="group[student_type][]">
                                                    <option value="">Please Select</option>
                                                    <option value="UK">UK</option>
                                                    <option value="BOTH">BOTH</option>
                                                    <option value="OVERSEAS">OVERSEAS</option></option>
                                                </select>
                                            </div>
                                            <div class="col-span-12 sm:col-span-3">
                                                <label for="term_status" class="form-label">Student Term Status</label>
                                                <select id="term_status" class="w-full tom-selects" multiple name="group[term_status][]" multiple>
                                                    <option value="">Please Select</option>
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
                                            <div class="col-span-12 sm:col-span-9 ml-auto mt-auto">
                                                <button id="studentGroupSearchSubmitBtn" type="button" class="btn btn-success text-white ml-auto w-96"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                                            </div>
                                            <input type="hidden" id="groupSearchStatus" value="0" class="form-control" name="group[stataus]">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="studentListFound" class="flex items-center  ">
            <div id="unsignedResultCount" class="font-bold text-base text-right ml-auto p-5 border-b border-slate-200/60 dark:border-darkmode-400" data-total="0"></div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="liveStudentsListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/students.js')
    @vite('resources/js/student-global.js')
@endsection