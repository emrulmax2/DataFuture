    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile Review of <u><strong>{{ $student->title->name.' '.$student->first_name.' '.$student->last_name }}</strong></u></h2>
        <div class="ml-auto flex justify-end">
            @if(isset(auth()->user()->priv()['login_as_student']) && auth()->user()->priv()['login_as_student'] == 1)
                <a target="__blank" href="{{ route('impersonate', ['id' =>$student->student_user_id,'guardName' =>'student']) }}" class="btn btn-success text-white w-auto mr-1 mb-0">
                        Login As Student <i data-lucide="log-in" class="w-4 h-4 ml-2"></i>
                </a>
            @endif
                <button type="button" class="btn btn-success text-white w-auto mr-1 mb-0">
                    {{ $student->status->name }}
                </button>
            
            @if(isset(auth()->user()->priv()['edit_student_status']) && auth()->user()->priv()['edit_student_status'] == 1)
            <button data-tw-toggle="modal" data-tw-target="#changeStudentModal" type="button" class="btn btn-primary w-auto text-white tooltip" title="Change Status">
                <i data-lucide="check-circle" class="w-4 h-4"></i>
            </button>
            @endif
            <!-- <a style="float: right;" href="{{ route('applicantprofile.print',$student->id) }}" data-id="{{ $student->id }}" class="btn btn-success text-white w-auto">Download Pdf</a> -->
            <input type="hidden" name="applicant_id" value="{{ $student->id }}"/>
        </div>
    </div>
    
    <div class="intro-y box px-5 pt-5 mt-5">
        <div class="flex flex-col lg:flex-row border-b border-slate-200/60 dark:border-darkmode-400 pb-5 -mx-5">
            <div class="flex flex-1 px-5 items-center justify-center lg:justify-start">
                <div class="w-20 h-20 sm:w-24 sm:h-24 flex-none lg:w-32 lg:h-32 image-fit relative">
                    <img alt="{{ $student->full_name.' '.$student->last_name }}" class="rounded-full" src="{{ (isset($student->photo_url) && !empty($student->photo_url) ? $student->photo_url : asset('build/assets/images/avater.png')) }}">
                    <button data-tw-toggle="modal" data-tw-target="#addStudentPhotoModal" type="button" class="absolute mb-1 mr-1 flex items-center justify-center bottom-0 right-0 bg-primary rounded-full p-2">
                        <i class="w-4 h-4 text-white" data-lucide="camera"></i>
                    </button>
                </div>
                
                @php
                    if($student->course->full_time==1):
                        $day = 'text-slate-900' ;
                    else:
                        $day = 'text-amber-600';
                    endif;
                    $html = '<div class="inline-flex ml-auto">';
                        $html .= (isset($student->flag_html) && !empty($student->flag_html) ? $student->flag_html : '');
                        if($student->due > 1):
                            $html .= '<div class="mr-2 '.($student->due == 2 ? 'text-success' : ($student->due == 3 ? 'text-warning' : 'text-danger')).'"><i data-lucide="badge-pound-sterling" class="w-6 h-6"></i></div>';
                        endif;
                        $html .= '<div class="w-8 h-8 '.$day.' intro-x inline-flex">';
                            if($student->course->full_time==1):
                                $html .= '<i data-lucide="sunset" class="w-6 h-6"></i>';
                            else:
                                $html .= '<i data-lucide="sun" class="w-6 h-6"></i>';
                            endif;
                        $html .= '</div>';
                        if($student->other->disability_status==1):
                            $html .= '<div class="inline-flex  intro-x  ml-auto" style="color:#9b1313"><i data-lucide="accessibility" class="w-6 h-6"></i></div>';
                        endif;
                    $html .= '</div>';
                @endphp
                <div class="ml-5">
                    <div class="w-full flex truncate sm:whitespace-normal font-medium text-lg">{{ !empty($student->registration_no) ? $student->registration_no : '' }} {!! $html !!} </div>
                    <div class="w-24 sm:w-40 truncate sm:whitespace-normal font-medium text-lg">{{ $student->title->name.' '.$student->first_name }} <span class="font-black">{{ $student->last_name }}</span></div>
                    <div class="text-slate-500">
                        @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0) <span class="bg-danger text-white inline pl-1 pr-1"> @endif
                            {{ isset($student->crel->creation->course->name) ? $student->crel->creation->course->name : '' }} - {{ isset($student->crel->propose->semester->name) ? $student->crel->propose->semester->name : '' }}
                        @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0) </span> @endif
                        @if(Session::has('student_temp_course_relation_'.$student->id) && Session::get('student_temp_course_relation_'.$student->id) > 0)
                            <a href="{{ route('student.set.default.course', $student->id) }}" class="inline ml-1 bg-success px-1 text-white">Reset</a>
                        @endif
                    </div>
                    <div class="text-slate-500">{{ isset($student->crel->creation->available->type) ? $student->crel->creation->available->type : '' }}</div>
                </div>
            </div>
            
            <div class="mt-6 lg:mt-0 flex-1 px-5 border-l border-r border-slate-200/60 dark:border-darkmode-400 border-t lg:border-t-0 pt-5 lg:pt-0">
                <div class="font-medium text-center lg:text-left lg:mt-3">Contact Details</div>
                <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                    <div class="truncate sm:whitespace-normal flex items-center">
                        <i data-lucide="mail" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Email:</span> {{ $student->users->email }}
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="phone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Phone:</span> {{ $student->contact->home }}
                    </div>
                    <div class="truncate sm:whitespace-normal flex items-center mt-3">
                        <i data-lucide="smartphone" class="w-4 h-4 mr-2"></i> <span class="text-slate-500 mr-2">Mobile:</span> {{ $student->contact->mobile }}
                    </div>
                </div>
            </div>
            
            <div class="mt-6 lg:mt-0 flex-1 px-5 border-t lg:border-0 border-slate-200/60 dark:border-darkmode-400 pt-5 lg:pt-0">
                <div class="font-medium text-center lg:text-left lg:mt-5">Address</div>
                <div class="flex flex-col justify-center items-center lg:items-start mt-4">
                    <div class="truncate sm:whitespace-normal flex items-start">
                        <i data-lucide="map-pin" class="w-4 h-4 mr-2" style="padding-top: 3px;"></i> 
                        <span>
                            @if(isset($student->contact->term_time_address_id) && $student->contact->term_time_address_id > 0)
                                @if(isset($student->contact->termaddress->address_line_1) && !empty($student->contact->termaddress->address_line_1))
                                    <span class="font-medium">{{ $student->contact->termaddress->address_line_1 }}</span><br/>
                                @endif
                                @if(isset($student->contact->termaddress->address_line_2) && !empty($student->contact->termaddress->address_line_2))
                                    <span class="font-medium">{{ $student->contact->termaddress->address_line_2 }}</span><br/>
                                @endif
                                @if(isset($student->contact->termaddress->city) && !empty($student->contact->termaddress->city))
                                    <span class="font-medium">{{ $student->contact->termaddress->city }}</span>,
                                @endif
                                @if(isset($student->contact->termaddress->state) && !empty($student->contact->termaddress->state))
                                    <span class="font-medium">{{ $student->contact->termaddress->state }}</span>, <br/>
                                @endif
                                @if(isset($student->contact->termaddress->post_code) && !empty($student->contact->termaddress->post_code))
                                    <span class="font-medium">{{ $student->contact->termaddress->post_code }}</span>,
                                @endif
                                @if(isset($student->contact->termaddress->country) && !empty($student->contact->termaddress->country))
                                    <br/><span class="font-medium">{{ $student->contact->termaddress->country }}</span>
                                @endif
                            @else 
                                <span class="font-medium text-warning">Not Set Yet!</span><br/>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @include('pages.students.live.show-menu')
    </div>

    <!-- BEGIN: Import Modal -->
    <div id="addStudentPhotoModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Profile Photo</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('student.upload.photo') }}" class="dropzone" id="addStudentPhotoForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents" type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop file here or click to upload.</div>
                            <div class="text-slate-500">
                                Select .jpg, .png, or .gif formate image. Max file size should be 5MB.
                            </div>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $student->applicant_id }}"/>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadStudentPhotoBtn" class="btn btn-primary w-auto">     
                        Upload                      
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
                </div>
            </div>
        </div>
    </div>
    <!-- END: Import Modal -->

    <!-- BEGIN: Status Change Modal -->
    <div id="changeStudentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="changeStudentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Change Student Status</h2>
                        <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="change_status_id" class="form-label">Status <span class="text-danger">*</span></label>
                            <select id="change_status_id" name="status_id" class="tom-selects w-full">
                                <option value="">Please Select</option>
                                @if(isset($statuses))
                                    @foreach($statuses as $stst)
                                        <option {{ ($stst->id == $student->status_id ? 'Selected' : '') }} value="{{ $stst->id }}">{{ $stst->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-status_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="term_declaration_id" class="form-label">Term <span class="text-danger">*</span></label>
                            <select id="term_declaration_id" name="term_declaration_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @if($student->assigned_terms && !empty($student->assigned_terms) && $student->assigned_terms->count() > 0)
                                    @foreach($student->assigned_terms as $term)
                                        <option {{ (isset($student->termStatus->term_declaration_id) && $student->termStatus->term_declaration_id == $term->id ? 'Selected' : '') }} value="{{ $term->id }}">{{ $term->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="mt-3">
                            <label for="status_change_reason" class="form-label">Change Reason</label>
                            <textarea name="status_change_reason" id="status_change_reason" class="form-control w-full" rows="3"></textarea>
                        </div>
                        <div class="mt-3">
                            <label for="status_change_date" class="form-label">Change Date <span class="text-danger">*</span></label>
                            <input type="text" name="status_change_date" id="status_change_date" value="<?php echo date('d-m-Y') ?>" class="form-control w-full datepicker" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY" data-single-mode="true"/>
                            <div class="acc__input-error error-status_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="updateStatusBtn" class="btn btn-primary w-auto">
                            Update Status
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Status Change Modal -->

    <!-- BEGIN: Success Modal Content -->
    <div id="successModalInfo" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 successModalInfoTitle"></div>
                        <div class="text-slate-500 mt-2 successModalInfoDesc"></div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="successCloser btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
