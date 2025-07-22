@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">All Reports</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a href="{{ route('dashboard') }}" class="add_btn btn btn-primary shadow-md mr-2">Back to Dashboard</a>
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-5">
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.attendance.reports') }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Attendance Report" src="{{ asset('build/assets/images/report_icons/attendance-reports.png') }}">
                </a>
            </div>

            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('reports.intake.performance'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Intake Performance Report" src="{{ asset('build/assets/images/report_icons/Intak_Performance_Report.png') }}">
                </a>
            </div>

            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('reports.term.performance'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Term Performance Report" src="{{ asset('build/assets/images/report_icons/term_performance_report_2.png') }}">
                </a>
            </div>

            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.student.data.view'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Student Data Report" src="{{ asset('build/assets/images/report_icons/student_data_report.png') }}">
                </a>
            </div>

            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('reports.slc.index'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Student SLC Report" src="{{ asset('build/assets/images/report_icons/slc_report.png') }}">
                </a>
            </div>

            
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.student.result.view'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Student Result Report" src="{{ asset('build/assets/images/report_icons/student_result_report_new.png') }}">
                </a>
            </div>

            
            
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.student.performance.view'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Student Performance Report" src="{{ asset('build/assets/images/report_icons/student_performance_report.png') }}">
                </a>
            </div>
            
            
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.student.progress.view'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Student Progress Report" src="{{ asset('build/assets/images/report_icons/student_progress_report.png') }}">
                </a>
            </div>
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.student.workplacement.view'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Student Workplacement Report" src="{{ asset('build/assets/images/report_icons/student-workplacement-reports.png') }}">
                </a>
            </div>
            
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="{{ route('report.student.expected.result.view'); }}" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="Student Expected Result Report" src="{{ asset('build/assets/images/report_icons/student_expected_report.png') }}">
                </a>
            </div>
            <div class="col-span-12 sm:col-span-3 xl:col-span-2 2xl:col-span-1">
                <a href="javascript:void(0);" data-tw-toggle="modal" data-tw-target="#xmlExportModal" class="box introy-y zoom-in bg-primary flex justify-center items-center">
                    <img class="block w-full h-auto shadow-md zoom-in rounded" alt="HESA All Students Report" src="{{ asset('build/assets/images/report_icons/hesa_all_students_report.png') }}">
                </a>
            </div>
        </div>
    </div>


    <!-- BEGIN: XML Export Modal -->
    <div id="xmlExportModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="xmlExportForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Download XML</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="terms_declaration_id" class="form-label">Term Declaration</label>
                            <select id="terms_declaration_id" class="tom-selects w-full" multiple name="term_declaration_id[]">
                                <option value="" selected>Please Select</option>
                                @if($termDeclarations->count() > 0)
                                    @foreach($termDeclarations as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                                    @endforeach 
                                @endif 
                            </select>
                            <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                        </div>
                        <div class="h-[1px] bg-slate-200 relative mt-7 mb-6">
                            <span class="px-2 py-1 bg-white absolute text-xs italic text-slate-500 font-medium w-[32px] l-0 r-0 mx-auto" style="top: -12px;">OR</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label for="from_date" class="form-label">From</label>
                                <input type="text" id="from_date" name="from_date" class="w-full form-control"/>
                                <div class="acc__input-error error-from_date text-danger mt-2"></div>
                            </div>
                            <div>
                                <label for="to_date" class="form-label">To</label>
                                <input type="text" id="to_date" name="to_date" class="w-full form-control"/>
                                <div class="acc__input-error error-to_date text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-danger w-auto mr-1"><i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>Cancel</button>
                        <button type="submit" id="xmlDownBtn" class="btn btn-success w-auto text-white">  
                            <i data-lucide="download" class="w-4 h-4 mr-2"></i>  
                            Download Now                      
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
    </div>
    <!-- END: XML Export Modal -->

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
    @vite('resources/js/hesa-all-students-reports.js')
@endsection