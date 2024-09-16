@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    
    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Course Details</div>
            </div>
            <div class="col-span-6 text-right">

                <button data-tw-toggle="modal" data-tw-target="#editStudentCourseChangeModal" class="btn btn-outline-secondary mr-2 w-auto"><i data-lucide="book-copy" class="stroke-1.5 h-4 w-4"></i></button>
                <button data-tw-toggle="modal" data-tw-target="#editStudentCourseDetailsModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                    <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Course Informations
                </button>
            </div>
        </div>
        <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="grid grid-cols-12 gap-4"> 
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Course & Semester</div>
                    <div class="col-span-8 font-medium">
                            <span>{{ $student->crel->creation->course->name.' - '.$student->crel->propose->semester->name }}</span>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Venue</div>
                    <div class="col-span-8 font-medium">
                            <span>{{ $venue }}</span>
                    </div>
                </div>
            </div>
            @if($studentCourseAvailability->count()>0)
                @foreach ($studentCourseAvailability as $availability)
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Course Start</div>
                            <div class="col-span-8 font-medium">{{ ($CourseRelation->course_start_date!=null) ?  $CourseRelation->course_start_date : $availability->course_start_date }}</div>
                        </div>
                    </div>
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Course End</div>
                            <div class="col-span-8 font-medium">{{ ($CourseRelation->course_end_date!=null) ?  $CourseRelation->course_end_date : $availability->course_end_date }}</div>
                        </div>
                    </div>
                    
                    <div class="col-span-12 sm:col-span-12">
                        <div class="grid grid-cols-12 gap-0">
                            <div class="col-span-4 text-slate-500 font-medium">Student Type</div>
                            <div class="col-span-8 font-medium">{{ $availability->type }}</div>
                        </div>
                    </div>
                    
                @endforeach
            @endif
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Qualification Achivement after completion</div>
                    <div class="col-span-8 font-medium">{{ isset($courseQualification->name) ? $courseQualification->name : '' }}</div>
                </div>
            </div>

            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Awarding Body</div>
                    <div class="col-span-8 font-medium">{{ (isset($student->crel->creation->course->body->name) ? $student->crel->creation->course->body->name : 'Unknown')}}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Duration</div>
                    <div class="col-span-8 font-medium">
                        {{ (isset($student->crel->creation->duration) ? $student->crel->creation->duration : '0')}} 
                        {{ (isset($student->crel->creation->unit_length) ? $student->crel->creation->unit_length : '')}} 
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">SLC Course Code</div>
                    <div class="col-span-8 font-medium">{{ $slcCode }} </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Evening & Weekend Indicator</div>
                    <div class="col-span-8 font-medium">{!! (isset($student->crel->propose->full_time) && $student->crel->propose->full_time == 1 ? '<span class="btn btn-success px-2 py-0 text-white rounded-0">Yes</span>' : '<span class="btn btn-danger px-2 py-0 text-white rounded-0">No</span>') !!}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Fee Eligibility</div>
                    <div class="col-span-8 font-medium">{!! (isset($student->crel->feeeligibility->elegibility->name) && isset($student->crel->feeeligibility->fee_eligibility_id) && $student->crel->feeeligibility->fee_eligibility_id > 0 ? $student->crel->feeeligibility->elegibility->name : '---') !!}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="intro-y box p-5 mt-5">
        <div class="grid grid-cols-12 gap-0 items-center">
            <div class="col-span-6">
                <div class="font-medium text-base">Awarding Body</div>
            </div>
            <div class="col-span-6 text-right">
                <button data-tw-toggle="modal" data-tw-target="#editStudentAWBModal" type="button" class="btn btn-primary w-auto mr-0 mb-0">
                    <i data-lucide="Pencil" class="w-4 h-4 mr-2"></i> Edit Awarding Body
                </button>
            </div>
        </div>
        <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
        <div class="grid grid-cols-12 gap-4"> 
            <div class="col-span-12 sm:col-span-12">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Registration Document Verified</div>
                    <div class="col-span-8 font-medium">
                        <div class="flex flex-col sm:flex-row mt-0">
                            <div class="form-check mr-5">
                                <input id="registration_document_verified_yes" class="form-check-input registration_document_verified" type="radio" name="registration_document_verified" {{ (isset($student->crel->abody->registration_document_verified) && $student->crel->abody->registration_document_verified == 'Yes' ? 'Checked' : '' ) }} value="Yes">
                                <label class="form-check-label" for="registration_document_verified_yes">Yes</label>
                            </div>
                            <div class="form-check mr-5 mt-2 sm:mt-0">
                                <input id="registration_document_verified_no" class="form-check-input registration_document_verified" type="radio" name="registration_document_verified" {{ (isset($student->crel->abody->registration_document_verified) && $student->crel->abody->registration_document_verified == 'No' ? 'Checked' : '' ) }} value="No">
                                <label class="form-check-label" for="registration_document_verified_no">No</label>
                            </div>
                            @if(isset($student->crel->abody->registration_document_verified) && !empty($student->crel->abody->registration_document_verified))
                            <button type="button" id="reset_regDocVerify" class="btn btn-success ml-3 rounded-0 px-3 py-1 text-white w-auto">     
                                Reset                      
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
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Awarding Body Ref</div>
                    <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->reference) ? $student->crel->abody->reference : '') }}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Awarding body course code</div>
                    <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->course_code) ? $student->crel->abody->course_code : '') }}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Awarding Body Reg. Date</div>
                    <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->registration_date) ? $student->crel->abody->registration_date : '') }}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Awarding Body Reg. Expire Date</div>
                    <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->registration_expire_date) ? $student->crel->abody->registration_expire_date : '') }}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Registered By</div>
                    <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->user->name) && !empty($student->crel->abody->user->name) ? $student->crel->abody->user->name : '') }}</div>
                </div>
            </div>
            <div class="col-span-12 sm:col-span-4">
                <div class="grid grid-cols-12 gap-0">
                    <div class="col-span-4 text-slate-500 font-medium">Registered At</div>
                    <div class="col-span-8 font-medium">{{ (isset($student->crel->abody->created_at) && !empty($student->crel->abody->created_at) ? date('jS F, Y', strtotime($student->crel->abody->created_at)) : '') }}</div>
                </div>
            </div>
        </div>
    </div>


    <!-- BEGIN: Course Change Modal -->
    <div id="editStudentCourseChangeModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <form method="POST" action="#" id="editStudentCourseChangeForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Change Course</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="academic_year_id" class="form-label sm:pt-2 col-span-12 sm:col-span-5 flex justify-start">Academic year <span class="text-danger">*</span><i data-loading-icon="oval" class="w-4 h-4 ml-2 relative t-1 loading hidden"></i></label>
                                    <div class="col-span-12 sm:col-span-7">
                                        <select id="academic_year_id" class="lcc-tom-select w-full" name="academic_year_id">
                                            <option value="">Please Select</option>
                                            @if($academicYears->count() > 0)
                                                @foreach($academicYears as $data)
                                                    <option value="{{ $data->id }}">{{ $data->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-academic_year_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 semesterWrap" style="display: none;">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="semester_id" class="form-label sm:pt-2 col-span-12 sm:col-span-5 flex justify-start">Semester <span class="text-danger">*</span><i data-loading-icon="oval" class="w-4 h-4 ml-2 relative t-1 loading hidden"></i></label>
                                    <div class="col-span-12 sm:col-span-7">
                                        <select id="semester_id" class="lcc-tom-select w-full" name="semester_id">
                                            <option value="">Please Select</option>
                                            {{-- @if($semesters->count() > 0)
                                                @foreach($semesters as $data)
                                                    <option value="{{ $data->id }}">{{ $data->name }}</option>
                                                @endforeach
                                            @endif --}}
                                        </select>
                                        <div class="acc__input-error error-semester_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 courseWrap" style="display: none;">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="course_id" class="form-label sm:pt-2 col-span-12 sm:col-span-5 inline-flex">Course <span class="text-danger">*</span> <i data-loading-icon="oval" class="w-4 h-4 ml-2 relative t-1 courseLoading hidden"></i></label>
                                    <div class="col-span-12 sm:col-span-7">
                                        <select id="course_id" class="lcc-tom-select w-full" name="course_id">
                                            <option value="">Please Select</option>
                                            {{-- @if($courses->count() > 0)
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                @endforeach
                                            @endif --}}
                                        </select>
                                        <div class="acc__input-error error-course_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12 venueWrap" style="display: none;">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="venue_id" class="form-label sm:pt-2 col-span-12 sm:col-span-5">Venue <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-7">
                                        <select id="venue_id" class="lcc-tom-select w-full" name="venue_id">
                                            <option value="">Please Select</option>
                                            {{-- @if($courses->count() > 0)
                                                @foreach($courses as $course)
                                                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                @endforeach
                                            @endif --}}
                                        </select>
                                        <div class="acc__input-error error-venue_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveSCR" class="btn btn-success text-white w-auto">     
                            Change Course                      
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
                        <input type="hidden" value="{{ $student->id }}" name="student_id"/>
                        <input type="hidden" value="{{ $student->crel->id }}" name="student_course_relation_id"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Course Change Modal -->

    <!-- BEGIN: Edit Modal -->
    <div id="editStudentCourseDetailsModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editStudentCourseDetailsForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Course Informations</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="full_time" class="form-label col-span-12 sm:col-span-6">Are you applying for evening and weekend classes (Full Time) <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <div class="form-check form-switch">
                                            <input {{ isset($student->crel->propose->full_time) && $student->crel->propose->full_time == 1 ? 'checked' : '' }} id="full_time" class="form-check-input" name="full_time" value="1" type="checkbox">
                                            <label class="form-check-label" for="full_time">&nbsp;</label>
                                        </div>
                                        <div class="acc__input-error error-full_time text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="fee_eligibility_id" class="form-label sm:pt-2 col-span-12 sm:col-span-6">Fee Eligibility <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <select id="fee_eligibility_id" class="lcc-tom-select lccTom w-full" name="fee_eligibility_id">
                                            <option value="">Please Select</option>
                                            @if($feeelegibility->count() > 0)
                                                @foreach($feeelegibility as $fl)
                                                    <option {{ isset($student->crel->feeeligibility->fee_eligibility_id) && $student->crel->feeeligibility->fee_eligibility_id == $fl->id ? 'Selected' : '' }} value="{{ $fl->id }}">{{ $fl->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-fee_eligibility_id text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="course_start_date" class="form-label sm:pt-2 col-span-12 sm:col-span-6">Course Start Date <span class="text-danger">*</span></label>
                                    <div class="col-span-12 sm:col-span-6">
                                    <input id="course_start_date" value="{{ ($CourseRelation->course_start_date!=null) ?  $CourseRelation->course_start_date : $availability->course_start_date }}" name="course_start_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                    <div class="acc__input-error error-course_start_date text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                <label for="course_end_date" class="form-label sm:pt-2 col-span-12 sm:col-span-6">Course End Date <span class="text-danger">*</span></label>
                                <div class="col-span-12 sm:col-span-6">
                                    <input id="course_end_date" value="{{ ($CourseRelation->course_end_date!=null) ?  $CourseRelation->course_end_date : $availability->course_end_date }}" name="course_end_date" type="text" class="form-control datepicker" data-format="DD-MM-YYYY" placeholder="DD-MM-YYYY" data-single-mode="true">    
                                    <div class="acc__input-error error-course_end_date text-danger mt-2"></div>
                                </div>
                                </div>
                            </div>
                            <div class="col-span-12">
                                <div class="grid grid-cols-12 gap-x-4">
                                    <label for="student_type" class="form-label sm:pt-2 col-span-12 sm:col-span-6">Type</label>
                                    <div class="col-span-12 sm:col-span-6">
                                        <select id="student_type" class="lcc-tom-select lccTom w-full" name="student_type">
                                            <option value="">Please Select</option>
                                            <option {{ ($CourseRelation->type!=null && $CourseRelation->type=="UK")  ?  'selected' : ($availability->type=="UK" ? 'selected' : "") }} value="UK">UK</option>
                                            <option {{ ($CourseRelation->type!=null && $CourseRelation->type=="OVERSEAS")  ?  'selected' : ($availability->type=="OVERSEAS" ? 'selected' : "") }} value="OVERSEAS">OVERSEAS</option>
                                            <option {{ ($CourseRelation->type!=null && $CourseRelation->type=="BOTH")  ?  'selected' : ($availability->type=="BOTH" ? 'selected' : "") }} value="BOTH">BOTH</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="savePCP" class="btn btn-primary w-auto">     
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
                        <input type="hidden" value="{{ $student->id }}" name="student_id"/>
                        <input type="hidden" value="{{ $student->crel->id }}" name="student_course_relation_id"/>
                        <input type="hidden" value="{{ (isset($student->crel->propose->id) ? $student->crel->propose->id : 0) }}" name="id"/>
                        <input type="hidden" value="{{ (isset($student->crel->feeeligibility->id) && $student->crel->feeeligibility->id > 0 ? $student->crel->feeeligibility->id : 0) }}" name="student_fee_eligibility_id"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Update Modal -->

    <!-- BEGIN: Edit Modal -->
    <div id="editStudentAWBModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="editStudentAWBForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Update Awarding Body Details</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-12 gap-4">
                            <div class="col-span-12 sm:col-span-6">
                                <label for="reference" class="form-label">Awarding Body Ref</label>
                                <input type="text" id="reference" class="form-control w-full" name="reference" value="{{ isset($student->crel->abody->reference) ? $student->crel->abody->reference : '' }}" placeholder="Awarding Body Ref."/>
                                <div class="acc__input-error error-reference text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="course_code" class="form-label">Awarding body course code</label>
                                <input type="text" id="course_code" class="form-control w-full" name="course_code" value="{{ isset($student->crel->abody->course_code) ? $student->crel->abody->course_code : '' }}" placeholder="Course Code"/>
                                <div class="acc__input-error error-course_code text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="registration_date" class="form-label">Awarding Body Reg. Date</label>
                                <input type="text" id="registration_date" class="form-control w-full datepicker" name="registration_date" value="{{ isset($student->crel->abody->registration_date) ? $student->crel->abody->registration_date : '' }}" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true"/>
                                <div class="acc__input-error error-registration_date text-danger mt-2"></div>
                            </div>
                            <div class="col-span-12 sm:col-span-6">
                                <label for="registration_expire_date" class="form-label">Awarding Body Reg. Expiry Date</label>
                                <input type="text" id="registration_expire_date" class="form-control w-full datepicker" name="registration_expire_date" value="{{ isset($student->crel->abody->registration_expire_date) ? $student->crel->abody->registration_expire_date : '' }}" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true"/>
                                <div class="acc__input-error error-registration_expire_date text-danger mt-2"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveSABD" class="btn btn-primary w-auto">     
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
                        <input type="hidden" value="{{ $student->id }}" name="student_id"/>
                        <input type="hidden" value="{{ $student->crel->id }}" name="student_course_relation_id"/>
                        <input type="hidden" value="{{ (isset($student->crel->abody->id) ? $student->crel->abody->id : 0) }}" name="id"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Update Modal -->

    <!-- BEGIN: Error Modal Content -->
    <div id="errorModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 errorModalTitle"></div>
                        <div class="text-slate-500 mt-2 errorModalDesc"></div>
                    </div>
                </div>
            </div>
        </div>
    <!-- END: Error Modal Content -->


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
                        <button type="button" data-action="DISMISS" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->

    <!-- BEGIN: Warning Modal Content -->
    <div id="warningModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="alert-octagon" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 warningModalTitle"></div>
                        <div class="text-slate-500 mt-2 warningModalDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-action="DISMISS" class="warningCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Warning Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmRDVModal" class="modal" tabindex="-1" aria-hidden="true" data-tw-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="x-circle" class="w-16 h-16 text-danger mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle">Are you sure?</div>
                        <div class="text-slate-500 mt-2 confModDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" class="disAgreeWith btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="{{ (isset($student->crel->abody->id) ? $student->crel->abody->id : 0) }}" data-status="" data-scrid="{{ $student->crel->id }}" data-student="{{ $student->id }}" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-course.js')
    @vite('resources/js/student-awarding-body.js')
@endsection