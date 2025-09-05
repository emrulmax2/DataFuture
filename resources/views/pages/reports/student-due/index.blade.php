@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Student Due Report</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <form method="post" action="#" id="studentDueReportForm">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-4">
                    <label for="due_semester_id" class="form-label semesterLabel inline-flex items-center">Intake Semester <span class="text-danger">*</span></label>
                    <select name="due_semester_id[]" multiple class="tom-selects w-full" id="due_semester_id">
                        <option value="">Please Select</option>
                        @if($semester->count() > 0)
                            @foreach($semester as $sem)
                                <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-4">
                    <label for="due_course_id" class="form-label courseLabel inline-flex items-center">Course</label>
                    <select name="due_course_id[]" multiple class="tom-selects w-full" id="due_course_id">
                        <option value="">Please Select</option>
                        @if($courses->count() > 0)
                            @foreach($courses as $crs)
                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-span-2">
                    <label for="due_status_id" class="form-label">Status</label>
                    <select name="due_status_id[]" multiple class="tom-selects w-full" id="due_status_id">
                        <option value="">Please Select</option>
                        @if($status->count() > 0)
                            @foreach($status as $sts)
                                <option value="{{ $sts->id }}">{{ $sts->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <!-- <div class="col-span-2">
                    <label for="due_date" class="form-label">Due Until</label>
                    <input type="text" name="due_date" class="form-control w-full datepicker" id="due_date" value="" data-date-format="DD-MM-YYYY" data-single-mode="true"/>
                </div> -->
                <div class="col-span-2 text-right" style="padding-top: 31px;">
                    <button type="button" id="accDueSubmitBtn" class="btn btn-primary text-white w-auto ml-2"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search</button>
                    <button type="button" id="downloadXl" class="btn btn-success text-white w-auto ml-2">
                        <i class="w-4 h-4 mr-2" data-lucide="file-text"></i> XL Export
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                            stroke="white" class="w-4 h-4 ml-2 theLoader">
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
    <div class="intro-y box p-5 mt-5">
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="studentDueReportList" class="table-report table-report--tabulator"></div>
        </div>
    </div>


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

    <!-- BEGIN: Success Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
@endsection


@section('script')
    @vite('resources/js/student-due-reports.js')
@endsection