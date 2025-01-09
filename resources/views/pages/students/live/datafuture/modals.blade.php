<!-- BEGIN: Edit Student Load Modal -->
<div id="editStudentStuloadModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="#" id="editStudentStuloadForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Edit Student Stuload</h2>
                    <a data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                </div>
                <div class="modal-body">
                    <div class="grid grid-cols-12 gap-4">
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">DISALL</label>
                            <select id="SSI_disall_id" name="disall_id" class="w-full tom-selects">
                                <option value="">N/A</option>
                                @if($disalls->count() > 0)
                                    @foreach($disalls as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">EXCHIND</label>
                            <select id="SSI_exchind_id" name="exchind_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($exchinds->count() > 0)
                                    @foreach($exchinds as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">GROSSFEE</label>
                            <input type="text" name="gross_fee" class="form-control w-full" />
                        </div>
                        {{--<div class="col-span-12 sm:col-span-3">
                            <label class="form-label">LOCATION</label>
                            <input type="text" name="LOCATION" class="form-control w-full" />
                        </div>--}}
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">LOCSDY</label>
                            <select id="SSI_locsdy_id" name="locsdy_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($locsdys->count() > 0)
                                    @foreach($locsdys as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">MODE</label>
                            <select id="SSI_mode_id" name="mode_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($modes->count() > 0)
                                    @foreach($modes as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">MSTUFEE</label>
                            <select id="SSI_mstufee_id" name="mstufee_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($mustfees->count() > 0)
                                    @foreach($mustfees as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">NETFEE</label>
                            <input type="text" name="netfee" class="w-full form-control"/>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">NOTACT</label>
                            <select id="SSI_notact_id" name="notact_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($notacts->count() > 0)
                                    @foreach($notacts as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">PERIODSTART</label>
                            <input type="text" name="periodstart" class="w-full form-control df-datepicker" />
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">PERIODEND</label>
                            <input type="text" name="periodend" class="w-full form-control df-datepicker" />
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">PRIPROV</label>
                            <select id="SSI_priprov_id" name="priprov_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($prefprovider->count() > 0)
                                    @foreach($prefprovider as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">SSELIG</label>
                            <select id="SSI_sselig_id" name="sselig_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($sseligs->count() > 0)
                                    @foreach($sseligs as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">YEARPRG</label>
                            <input type="text" name="yearprg" class="w-full form-control" />
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">YEARSTU</label>
                            <input type="text" name="yearstu" class="w-full form-control" />
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">Qualification Achievement After Completion:</label>
                            <select id="SSI_qual_id" name="qual_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($quals->count() > 0)
                                    @foreach($quals as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">HEAPES population (HEAPESPOP):</label>
                            <select id="SSI_heapespop_id" name="heapespop_id" class="w-full tom-selects">
                                <option value="">Please Select</option>
                                @if($heapespops->count() > 0)
                                    @foreach($heapespops as $opt)
                                        <option value="{{ $opt->id }}">{{ $opt->name }} {{ ($opt->is_hesa == 1 && !empty($opt->hesa_code) ? ' ['.$opt->hesa_code.']' : '') }} {{ ($opt->is_df == 1 && !empty($opt->df_code) ? ' ['.$opt->df_code.']' : '') }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">COMDATE</label>
                            <input type="text" name="comdate" class="w-full form-control df-datepicker" />
                        </div>
                        <div class="col-span-12 sm:col-span-3">
                            <label class="form-label">ENDDATE</label>
                            <input type="text" name="enddate" class="w-full form-control df-datepicker" />
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveStuloadBtn" class="btn btn-primary w-auto">     
                        Save                      
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
                    <input type="hidden" value="0" name="id"/>
                    <input type="hidden" value="{{ $student->id }}" name="student_id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- End: Edit Student Load Modal -->

<!-- BEGIN: Edit Personal Details Modal -->
<div id="addHesaInstanceModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="#" id="addHesaInstanceForm" enctype="multipart/form-data">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Add Hesa Instance</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <div>
                        <label for="semester_id" class="form-label">Intake Semester <span class="text-danger">*</span></label>
                        <select id="semester_id" class="tom-selects w-full" name="semester_id">
                            <option value="" selected>Please Select</option>
                            @if($semesters->count() > 0)
                                @foreach($semesters as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                                @endforeach 
                            @endif 
                        </select>
                        <div class="acc__input-error error-semester_id text-danger mt-2"></div>
                    </div>
                    <div class="instanceListWrap mt-4" style="display: none;">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <td>ID</td>
                                    <td>Start Date</td>
                                    <td>End Date</td>
                                    <td>Total Teaching Week</td>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                    <div class="acc__input-error error-course_creation_instance_id text-danger mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="submit" id="saveInstBtn" class="btn btn-primary w-auto">     
                        Save                      
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
                    <input type="hidden" value="{{ $student->id }}" name="id"/>
                    <input type="hidden" value="{{ $course_id }}" name="course_id"/>
                    <input type="hidden" value="{{ $student->crel->id }}" name="student_course_relation_id"/>
                </div>
            </div>
        </form>
    </div>
</div>
<!-- END: Edit Personal Details Modal -->



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
                    <button type="button" data-action="NONE" class="successCloser btn btn-primary w-24">Ok</button>
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
<!-- END: Delete Confirm Modal Content -->