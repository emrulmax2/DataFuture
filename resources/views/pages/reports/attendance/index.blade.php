@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Application Analysis Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form action="{{ route('report.application.analysis') }}" method="post" id="studentGroupSearchForm">
            @csrf
            <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                <div class="col-span-12 sm:col-span-3">
                    <label for="academic_year" class="form-label">Academic Year <span class="text-danger">*</span></label>
                    <select id="academic_year" class="w-full tom-selects" multiple name="params[academic_year][]">
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
                    <label for="evening_weekend" class="form-label">Evening / Weekend</label>
                    <select id="evening_weekend" class="w-full form-control" name="params[evening_weekend][]">
                        <option value="">Please Select</option>
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="student_type" class="form-label">Student Type</label>
                    <select id="student_type" class="w-full tom-selects" multiple name="params[student_type][]">
                        <option value="">Please Select</option>
                        <option value="UK">UK</option>
                        <option value="BOTH">BOTH</option>
                        <option value="OVERSEAS">OVERSEAS</option></option>
                    </select>
                </div>
                <div class="col-span-12 sm:col-span-3">
                    <label for="term_status" class="form-label">Student Term Status</label>
                    <select id="term_status" class="w-full tom-selects" multiple name="params[term_status][]" multiple>
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
                <div class="col-span-12 sm:col-span-9 ml-auto mt-auto text-right">
                    <button type="submit" class="btn btn-success text-white ml-auto w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                </div>
            </div>
        </form>

        <div class="overflow-x-auto scrollbar-hidden pt-5">
            
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-group-search-form.js')
    @vite('resources/js/student-attendance-reports.js')
@endsection