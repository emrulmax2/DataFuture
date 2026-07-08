@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    @include('pages.students.live.index-info')

    <div class="intro-y student-live-shell">
        <div class="student-live-search-card" data-screen-label="Student Search">
            <form id="studentSearchForm" method="post" action="#">
                <div class="student-live-search-main">
                    <label class="student-live-search-label studentIdSearchWrap">Student Search</label>
                    <div class="student-live-id-field studentIdSearchWrap">
                        <div class="autoCompleteField" data-table="students">
                            <i data-lucide="search" class="student-live-input-icon w-4 h-4"></i>
                            <input type="text" autocomplete="off" id="registration_no" name="student_id" class="form-control registration_no student-live-id-input" value="" placeholder="LCC000001"/>
                            <ul class="autoFillDropdown"></ul>
                        </div>
                    </div>

                    <div class="student-live-search-actions">
                        <button id="studentIDSearchBtn" type="button" class="btn btn-success text-white student-live-btn student-live-btn-primary">
                            <i class="w-4 h-4" data-lucide="search"></i>
                            <span>Search</span>
                        </button>
                        <button id="resetStudentSearch" type="button" class="btn btn-danger student-live-btn student-live-btn-reset">
                            <i class="w-4 h-4" data-lucide="rotate-cw"></i>
                            <span>Reset</span>
                        </button>
                        <button id="advanceSearchToggle" type="button" class="btn btn-facebook student-live-btn student-live-btn-advance">
                            <span>Advance Search</span>
                            <i class="w-4 h-4" data-lucide="chevron-down"></i>
                        </button>
                    </div>
                </div>

                <div id="studentSearchAccordionWrap" class="student-live-advanced" style="display: none;">
                    <div id="studentSearchAccordion" class="accordion accordion-boxed student-live-accordion">
                        <div class="accordion-item student-live-accordion-item">
                            <div id="studentSearchAccordion-1" class="accordion-header">
                                <button id="studentSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold student-live-accordion-trigger" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-1" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-1">
                                    <span class="student-live-panel-icon student-live-panel-icon-student">
                                        <i data-lucide="user" class="w-4 h-4"></i>
                                    </span>
                                    <span class="student-live-panel-copy">
                                        <span class="student-live-panel-title">Search By Student</span>
                                        <span class="student-live-panel-subtitle">ID, name, contact or reference</span>
                                    </span>
                                    <span class="accordionCollaps student-live-panel-toggle"></span>
                                </button>
                            </div>
                            <div id="studentSearchAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-1" data-tw-parent="#studentSearchAccordion">
                                <div class="accordion-body student-live-accordion-body">
                                    <div class="grid grid-cols-12 gap-0 gap-y-4 gap-x-4">
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
                                            <input type="text" value="" autocomplete="off" placeholder="DD-MM-YYYY" id="student_dob" class="form-control datepickerMask" name="student[student_dob]">
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
                                        <div class="col-span-12 sm:col-span-6 student-live-form-submit">
                                            <button id="studentSearchSubmitBtn" type="button" class="btn btn-success text-white student-live-btn student-live-btn-primary">
                                                <i class="w-4 h-4" data-lucide="search"></i>
                                                <span>Search</span>
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" value="0" id="studentSearchStatus" class="form-control" name="student[stataus]">
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item student-live-accordion-item">
                            <div id="studentSearchAccordion-2" class="accordion-header">
                                <button id="studentGroupSearchBtn" class="accordion-button collapsed relative w-full text-lg font-semibold student-live-accordion-trigger" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-2" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-2">
                                    <span class="student-live-panel-icon student-live-panel-icon-group">
                                        <i data-lucide="users" class="w-4 h-4"></i>
                                    </span>
                                    <span class="student-live-panel-copy">
                                        <span class="student-live-panel-title">Group Search</span>
                                        <span class="student-live-panel-subtitle">By semester, course or group</span>
                                    </span>
                                    <span class="accordionCollaps student-live-panel-toggle"></span>
                                </button>
                            </div>
                            <div id="studentSearchAccordion-collapse-2" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-2" data-tw-parent="#studentSearchAccordion">
                                <div class="accordion-body student-live-accordion-body">
                                    <div class="grid grid-cols-12 gap-0 gap-y-4 gap-x-4">
                                        <div class="col-span-12 sm:col-span-3">
                                            <label for="intake_semester" class="form-label">Intake Semester</label>
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
                                            <label for="attendance_semester" class="form-label">Attendance Semester</label>
                                            <select id="attendance_semester" class="w-full tom-selects" multiple name="group[attendance_semester][]">
                                                <option value="">Please Select</option>
                                                @if(!empty($terms))
                                                    @foreach($terms as $term)
                                                        <option value="{{ $term->id }}">{{ $term->name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            <div class="acc__input-error error-attendance_semester text-danger mt-2"></div>
                                        </div>
                                        <div class="col-span-12 sm:col-span-3">
                                            <label for="course" class="form-label">Course</label>
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
                                                <option value="OVERSEAS">OVERSEAS</option>
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
                                        <div class="col-span-12 sm:col-span-3 student-live-form-submit">
                                            <button id="studentGroupSearchSubmitBtn" type="button" class="btn btn-success text-white student-live-btn student-live-btn-primary">
                                                <i class="w-4 h-4" data-lucide="search"></i>
                                                <span>Search</span>
                                            </button>
                                        </div>
                                        <input type="hidden" id="groupSearchStatus" value="0" class="form-control" name="group[stataus]">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="student-live-results-card" data-screen-label="Results List">
            <div id="studentListFound" class="student-live-results-head">
                <div class="student-live-results-title">Results</div>
                <div id="unsignedResultCount" class="student-live-result-count hidden" data-total="0"></div>
                <div id="studentSelectedCount" class="student-live-selected-count hidden" data-count="0"></div>

                <div id="communicationBtnsArea" class="student-live-bulk-actions" style="display: none;">
                    @if(isset(auth()->user()->priv()['send_sms']) && auth()->user()->priv()['send_sms'] == 1)
                        <button type="button" class="sendBulkSmsBtn btn btn-pending shadow-md text-white student-live-bulk-btn student-live-bulk-btn-sms">
                            <i data-lucide="smartphone" class="w-4 h-4"></i>
                            <span>Send SMS</span>
                        </button>
                    @endif
                    @if(isset(auth()->user()->priv()['send_email']) && auth()->user()->priv()['send_email'] == 1)
                        <button type="button" class="sendBulkMailBtn btn btn-success shadow-md text-white student-live-bulk-btn student-live-bulk-btn-email">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                            <span>Send Email</span>
                        </button>
                    @endif
                    @if(isset(auth()->user()->priv()['generage_latter']) && auth()->user()->priv()['generage_latter'] == 1)
                        <button type="button" class="generateBulkLetterBtn btn btn-primary shadow-md text-white student-live-bulk-btn student-live-bulk-btn-letter">
                            <i data-lucide="mailbox" class="w-4 h-4"></i>
                            <span>Generate Letter</span>
                        </button>
                    @endif
                </div>

                <div class="dropdown hidden">
                    <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto px-5" aria-expanded="false" data-tw-toggle="dropdown">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                    </button>
                    <div class="dropdown-menu w-40">
                        <ul class="dropdown-content">
                            <li>
                                <a id="tabulator-export-xlsx-LSD" href="javascript:;" class="dropdown-item">
                                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="student-live-table-wrap overflow-x-auto scrollbar-hidden">
                <div id="liveStudentsListTable" data-coummunication="{{ ((isset(auth()->user()->priv()['generage_latter']) && auth()->user()->priv()['generage_latter'] == 1) || (isset(auth()->user()->priv()['send_email']) && auth()->user()->priv()['send_email'] == 1) || (isset(auth()->user()->priv()['send_sms']) && auth()->user()->priv()['send_sms'] == 1) ? 1 : 0) }}" class="table-report table-report--tabulator student-live-table"></div>
            </div>
        </div>
    </div>

    @include('pages.students.live.index-modal')
@endsection

@section('script')
    @vite('resources/js/students.js')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-list-communication.js')
@endsection
