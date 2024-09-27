@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Student Data Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form action="#" method="post" id="studentGroupSearchForm">
            @csrf
            <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
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
                    <label for="attendance_semester" class="form-label">Attendance Semester </label>
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
                <div class="col-span-12 sm:col-span-3 ml-auto mt-auto flex">
                    <button type="button" class="btn btn-danger resetSearch text-white ml-auto w-auto inline-flex mr-2"><i class="w-4 h-4 mr-2" data-lucide="refresh-cw"></i> Reset</button>
                    <button id="studentGroupSearchSubmitBtn" type="submit" class="btn btn-success text-white ml-auto  w-36 xl:w-56 2xl:w-80"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search <i data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 hidden loadingCall"></i></button>
                </div>
                <input type="hidden" id="groupSearchStatus" value="0" class="form-control" name="group[stataus]">
            </div>
        </form>
    </div>
    <div class=" intro-y box p-5 mt-10 mb-10">
        <div class="grid grid-cols-12 items-center" id="reportRowCountWrap">
            <div id="reportTotalRowCount" class="col-span-12 sm:col-span-6 items-center text-left font-medium ">Total Student(s) Found: <div id="totalCount" class="inline-block ml-2"></div></div>
            <div class="col-span-12 sm:col-span-6 text-right">
                <button type="button" id="studentDataReportExcelBtn" class="btn btn-primary w-auto" disabled>
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export Excel 
                    <svg  style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                        stroke="white" class="w-4 h-4 ml-2 loadingCall">
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
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Data Set List</h2>
    </div>
    <form action="#" method="post" id="studentExcelForm">
        <input type="hidden" id="studentFoundedList" value="" />
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 items-center">
                <div class="col-span-12 sm:col-span-12 items-center accordion accordion-boxed">
                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                        <div id="datareportAccordion-1" class="accordion-header">
                            <button class="accordion-button  relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#datareportAccordion-collapse-1" aria-expanded="true" aria-controls="datareportAccordion-collapse-1">
                                <span class="font-normal">Pesonal Details
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="datareportAccordion-collapse-1" class="accordion-collapse " aria-labelledby="datareportAccordion-1" data-tw-parent="#datareportAccordion">
                            <div class="accordion-body px-5 border-t pt-5">
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 intro-y border-b mb-2">
                                        <div data-tw-merge class="flex items-center mt-2 "><input id="checkbox-all-personal" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-all-personal" class="cursor-pointer ml-2 ">Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="Student[full_name]" id="checkbox-switch-1" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-1" class="cursor-pointer ml-2">Name</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="Student[nationality_id][nation]" id="checkbox-switch-2" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-2" class="cursor-pointer ml-2">Nationality</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentOtherDetail[sexual_orientation_id][sexori]" id="checkbox-switch-3" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-3" class="cursor-pointer ml-2">Sexual Orientation</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentOtherDetail[disability_status]" id="checkbox-switch-4" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-4" class="cursor-pointer ml-2"> Disability Status</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="Student[ssn_no]" id="checkbox-switch-5" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-5" class="cursor-pointer ml-2"> SSN</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="Student[date_of_birth]" id="checkbox-switch-6" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-6" class="cursor-pointer ml-2">Date of Birth</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="Student[country_id][country]" id="checkbox-switch-7" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-7" class="cursor-pointer ml-2">Country of Birth</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentOtherDetail[hesa_gender_id][gender]" id="checkbox-switch-8" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-8" class="cursor-pointer ml-2">Gender Identity</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="Student[uhn_no]" id="checkbox-switch-9" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-9" class="cursor-pointer ml-2">UHN Number </label>
                                        </div>
                                        
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="Student[sex_identifier_id][sexid]" id="checkbox-switch-11" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-11" class="cursor-pointer ml-2">Sex Identifier/Gender</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentOtherDetail[ethnicity_id][ethnicity]" id="checkbox-switch-12" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-12" class="cursor-pointer ml-2">Ethnicity</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentOtherDetail[religion_id][religion]" id="checkbox-switch-13" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-13" class="cursor-pointer ml-2">Religion or Belief</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="Student[DF_SID_Number]" id="checkbox-switch-14" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-14" class="cursor-pointer ml-2">DF SID Number</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 items-center">
                <div class="col-span-12 sm:col-span-12 items-center accordion accordion-boxed">
                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                        <div id="datareportAccordion-1" class="accordion-header">
                            <button class="accordion-button  relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#datareportAccordion-collapse-1" aria-expanded="true" aria-controls="datareportAccordion-collapse-1">
                                <span class="font-normal">Course Details
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="datareportAccordion-collapse-1" class="accordion-collapse " aria-labelledby="datareportAccordion-1" data-tw-parent="#datareportAccordion">
                            <div class="accordion-body px-5 border-t pt-5">
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 intro-y border-b mb-2">
                                        <div data-tw-merge class="flex items-center mt-2 "><input id="checkbox-all-course" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-all-course" class="cursor-pointer ml-2 ">Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentCourseRelation[course_relation_id][course]" id="checkbox-switch-15" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-15" class="cursor-pointer ml-2">Course Name</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentCourseRelation[course_start_date]" id="checkbox-switch-16" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-16" class="cursor-pointer ml-2">Start Date</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentCourseRelation[course_end_date]" id="checkbox-switch-17" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-17" class="cursor-pointer ml-2">End Date</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentProposedCourse[semester_id][semester]" id="checkbox-switch-18" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-18" class="cursor-pointer ml-2">Batch Name (Intake Semester)</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentCourseRelation[awarding_body]" id="checkbox-switch-19" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-19" class="cursor-pointer ml-2">Awarding Body</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentAwardingBodyDetails[awarding_body_reference]" id="checkbox-switch-20" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-20" class="cursor-pointer ml-2">Awarding Body ref no</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentProposedCourse[full_time]" id="checkbox-switch-21" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-21" class="cursor-pointer ml-2">Evening and Weekend status</label>
                                        </div>
                                        
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentAttendanceTermStatus[status_change_date]" id="checkbox-switch-23" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-23" class="cursor-pointer ml-2">Status change date</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 items-center">
                <div class="col-span-12 sm:col-span-12 items-center accordion accordion-boxed">
                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                        <div id="datareportAccordion-1" class="accordion-header">
                            <button class="accordion-button  relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#datareportAccordion-collapse-1" aria-expanded="true" aria-controls="datareportAccordion-collapse-1">
                                <span class="font-normal">Proof Of ID Checks
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="datareportAccordion-collapse-1" class="accordion-collapse " aria-labelledby="datareportAccordion-1" data-tw-parent="#datareportAccordion">
                            <div class="accordion-body px-5 border-t pt-5">
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 intro-y border-b mb-2">
                                        <div data-tw-merge class="flex items-center mt-2 "><input id="checkbox-all-proof" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-all-proof" class="cursor-pointer ml-2 ">Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentProofOfId[proof_type]" id="checkbox-switch-24" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-24" class="cursor-pointer ml-2">Proof Type</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentProofOfId[proof_id]" id="checkbox-switch-25" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-25" class="cursor-pointer ml-2">Proof ID</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentProofOfId[proof_expiredate]" id="checkbox-switch-26" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-26" class="cursor-pointer ml-2">Proof Expire date</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 items-center">
                <div class="col-span-12 sm:col-span-12 items-center accordion accordion-boxed">
                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                        <div id="datareportAccordion-1" class="accordion-header">
                            <button class="accordion-button  relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#datareportAccordion-collapse-1" aria-expanded="true" aria-controls="datareportAccordion-collapse-1">
                                <span class="font-normal">Contact Details
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="datareportAccordion-collapse-1" class="accordion-collapse " aria-labelledby="datareportAccordion-1" data-tw-parent="#datareportAccordion">
                            <div class="accordion-body px-5 border-t pt-5">
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 intro-y border-b mb-2">
                                        <div data-tw-merge class="flex items-center mt-2 "><input id="checkbox-all-address" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-all-address" class="cursor-pointer ml-2 ">Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentContact[term_time_address_id][termaddress]" id="checkbox-switch-27" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-27" class="cursor-pointer ml-2">Term Time Address</label>
                                        </div>
                                        {{-- <div data-tw-merge class="flex items-center mt-2"><input name="StudentContact[term_time_address_postcode][termaddress]" id="checkbox-switch-28" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-28" class="cursor-pointer ml-2">Term Time Address Postcode</label>
                                        </div> --}}
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentContact[permanent_address_id][permaddress]" id="checkbox-switch-29" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-29" class="cursor-pointer ml-2">Permanent Address</label>
                                        </div>
                                        {{-- <div data-tw-merge class="flex items-center mt-2"><input name="StudentContact[permanent_address_country_code][permaddress]" id="checkbox-switch-30" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-30" class="cursor-pointer ml-2">Permanent Country code</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentContact[permanent_address_post_code][permaddress]" id="checkbox-switch-31" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-31" class="cursor-pointer ml-2">Permanent Post code</label>
                                        </div> --}}
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentContact[personal_email]" id="checkbox-switch-32" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-32" class="cursor-pointer ml-2">Personal Email</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentContact[institutional_email]" id="checkbox-switch-33" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-33" class="cursor-pointer ml-2">Institutional Email</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentContact[home]" id="checkbox-switch-34" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-34" class="cursor-pointer ml-2">Home Phone</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentContact[mobile]" id="checkbox-switch-35" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-35" class="cursor-pointer ml-2">Mobile</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 items-center">
                <div class="col-span-12 sm:col-span-12 items-center accordion accordion-boxed">
                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                        <div id="datareportAccordion-1" class="accordion-header">
                            <button class="accordion-button relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#datareportAccordion-collapse-1" aria-expanded="true" aria-controls="datareportAccordion-collapse-1">
                                <span class="font-normal">Next of Kin
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="datareportAccordion-collapse-1" class="accordion-collapse " aria-labelledby="datareportAccordion-1" data-tw-parent="#datareportAccordion">
                            <div class="accordion-body px-5 border-t pt-5">
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 intro-y border-b mb-2">
                                        <div data-tw-merge class="flex items-center mt-2 "><input id="checkbox-all-kin" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-all-kin" class="cursor-pointer ml-2 ">Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentKin[name]" id="checkbox-switch-36" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-36" class="cursor-pointer ml-2">Name</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentKin[email]" id="checkbox-switch-37" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-37" class="cursor-pointer ml-2">Email</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentKin[kins_relation_id][relation]" id="checkbox-switch-38" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-38" class="cursor-pointer ml-2">Relation</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentKin[address_id][address]" id="checkbox-switch-39" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-39" class="cursor-pointer ml-2">Address</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentKin[mobile]" id="checkbox-switch-40" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-40" class="cursor-pointer ml-2">Mobile</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 items-center">
                <div class="col-span-12 sm:col-span-12 items-center accordion accordion-boxed">
                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                        <div id="datareportAccordion-1" class="accordion-header">
                            <button class="accordion-button relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#datareportAccordion-collapse-1" aria-expanded="true" aria-controls="datareportAccordion-collapse-1">
                                <span class="font-normal">Entry Qualification
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="datareportAccordion-collapse-1" class="accordion-collapse " aria-labelledby="datareportAccordion-1" data-tw-parent="#datareportAccordion">
                            <div class="accordion-body px-5 border-t pt-5">
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 intro-y border-b mb-2">
                                        <div data-tw-merge class="flex items-center mt-2 "><input id="checkbox-all-qual" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-all-qual" class="cursor-pointer ml-2 ">Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="StudentQualification[highest_qualification_on_Entry][qualHigest]" id="checkbox-switch-41" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-41" class="cursor-pointer ml-2">Highest Qualification on Entry (QUALENT3)</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2"><input name="StudentQualification[qualification_details][qualHigest]" id="checkbox-switch-42" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-42" class="cursor-pointer ml-2">Qualification Details</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="grid grid-cols-12 items-center">
                <div class="col-span-12 sm:col-span-12 items-center accordion accordion-boxed">
                    <div class="accordion-item bg-white mb-3 border-0 rounded">
                        <div id="datareportAccordion-1" class="accordion-header">
                            <button class="accordion-button  relative w-full text-lg font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#datareportAccordion-collapse-1" aria-expanded="true" aria-controls="datareportAccordion-collapse-1">
                                <span class="font-normal">Referral
                                <span class="accordionCollaps"></span>
                            </button>
                        </div>
                        <div id="datareportAccordion-collapse-1" class="accordion-collapse " aria-labelledby="datareportAccordion-1" data-tw-parent="#datareportAccordion">
                            <div class="accordion-body px-5 border-t pt-5">
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 intro-y border-b mb-2">
                                        <div data-tw-merge class="flex items-center mt-2 "><input id="checkbox-all-ref" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-all-ref" class="cursor-pointer ml-2 ">Select All</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-6">
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="Student[application_no]" id="checkbox-switch-43" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-43" class="cursor-pointer ml-2">Application Ref. No</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2 "><input name="Student[submission_date]" id="checkbox-switch-44" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50" />
                                            <label data-tw-merge for="checkbox-switch-44" class="cursor-pointer ml-2">Date of application</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y">
                                        
                                        <div data-tw-merge class="flex items-center mt-2"><input name="Student[referral_code]" id="checkbox-switch-46" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-46" class="cursor-pointer ml-2">Code</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="AgentReferralCode[type]" id="checkbox-switch-47" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-47" class="cursor-pointer ml-2">TYPE</label>
                                        </div>
                                        <div data-tw-merge class="flex items-center mt-2"><input name="AgentReferralCode[referral_name]" id="checkbox-switch-48" value="1"  data-tw-merge type="checkbox" class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 dark:bg-darkmode-800 dark:border-transparent dark:focus:ring-slate-700 dark:focus:ring-opacity-50 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50"  />
                                            <label data-tw-merge for="checkbox-switch-48" class="cursor-pointer ml-2">Name</label>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4 intro-y"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('script')
    @vite('resources/js/student-data-search-form.js')
    @vite('resources/js/student-attendance-reports.js')
@endsection