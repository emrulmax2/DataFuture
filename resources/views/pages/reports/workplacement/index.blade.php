@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Student Workplacement Report</h2>
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
    <div class=" intro-y box p-5 mt-10 mb-10 hidden searchResultBox">
        <div class="grid grid-cols-12 items-center" id="reportRowCountWrap">
            <div id="reportTotalRowCount" class="col-span-12 sm:col-span-6 items-center text-left font-medium ">Total Student(s) Found: <div id="totalCount" class="inline-block ml-2"></div></div>
            <div class="col-span-12 sm:col-span-6 text-right">
                <button type="button" id="studentDataReportExcelBtn" class="btn btn-primary w-auto" disabled>
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export Excel 
                    <svg width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4 ml-2 hidden loadingCall" style="display: none;">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="4">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                </path>
                            </g>
                        </g>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <form method="POST" id="studentExcelForm">
        @csrf
        <input type="hidden" id="studentExcelFormInput" name="studentExcelFormInput" value="1">
    </form>
@endsection

@section('script')
    @vite('resources/js/student-workplacement-form.js')
@endsection