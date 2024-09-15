@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Attendance Report</h2>
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
                    <select id="intake_semester" class="w-full tom-selects" multiple name="params[intake_semester][]">
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
                    <select id="attendance_semester" class="w-full tom-selects" multiple name="params[attendance_semester][]">
                        <option value="">Please Select</option>
                        @if($terms->count() > 0)
                            @foreach($terms as $trm)
                                <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="acc__input-error error-attendance_semester text-danger mt-2"></div>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="course" class="form-label">Course </label>
                    <select id="course" class="w-full tom-selects" multiple name="params[course][]">
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
                    <select id="group" class="w-full tom-selects" multiple name="params[group][]">
                        <option value="">Please Select</option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="group_student_status" class="form-label">Student Status</label>
                    <select id="group_student_status" class="w-full tom-selects" name="params[group_student_status][]" multiple>
                        <option value="">Please Select</option>
                        @if(!empty($allStatuses))
                            @foreach($allStatuses as $sts)
                                <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="attendance_percentage" class="form-label">Percentage</label>
                    <select class="form-control w-full" name="params[attendance_percentage]" id="attendance_percentage">
                        <option value="">Please Select</option>
                        <option value="0.00">less than or equal 0%</option>
                        <option value="10.00">less than 10%</option>
                        <option value="20.00">less than 20%</option>
                        <option value="30.00">less than 30%</option>
                        <option value="40.00">less than 40%</option>
                        <option value="50.00">less than 50%</option>
                        <option value="60.00">less than 60%</option>
                        <option value="70.00">less than 70%</option>
                        <option value="80.00">less than 80%</option>
                        <option value="90.00">less than 90%</option>
                        <option value="100.00">less than 100%</option>       
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-6 ml-auto mt-auto text-right">
                    <button type="button" id="studentGroupSearchBtn" class="btn btn-success text-white ml-auto w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto scrollbar-hidden pt-5 attendanceReportListTableWrap" style="display: none;">
            <div class="grid grid-cols-12 items-center" id="reportRowCountWrap">
                <div class="col-span-12 sm:col-span-6 items-center text-left reportTotalRowCount font-medium"></div>
                <div class="col-span-12 sm:col-span-6 text-right">
                    <button type="button" id="attendanceReportExcelBtn" class="btn btn-primary w-auto">
                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>Export Excel 
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 loading">
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
            <div id="attendanceReportListTable" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-group-search-form.js')
    @vite('resources/js/student-attendance-reports.js')
@endsection