@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}- </title>
@endsection

@section('subcontent')

    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Profile of <u><strong>{{ $employee->first_name.' '.$employee->last_name }}</strong></u></h2>
    </div>

    <!-- BEGIN: Profile Info -->
    @include('pages.agent.profile.show-info')
    <!-- END: Profile Info -->

    <div class="intro-y mt-5">
        <div class="intro-y box p-5">
            <div class="grid grid-cols-12 gap-0 items-center">
                <div class="col-span-6">
                    <div class="font-medium text-base">Applicant/Student Details</div>
                </div>

            </div>
            
            <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400"></div>
            <div id="studentSearchAccordionWrap" class="pt-4 mb-2">
    
                <div id="studentSearchAccordion" class="accordion accordion-boxed pt-2">
                    <div class="accordion-item">
                        <div id="studentSearchAccordion-1" class="accordion-header">
                            <button  id="studentGroupSearchBtn" class="accordion-button collapsed relative w-full text-sm font-semibold" type="button" data-tw-toggle="collapse" data-tw-target="#studentSearchAccordion-collapse-1" aria-expanded="false" aria-controls="studentSearchAccordion-collapse-1">
                                Search
                                <span class="accordionCollaps" style="width: 18px; height: 16px;"></span>
                            </button>
                        </div>
                        <div id="studentSearchAccordion-collapse-1" class="accordion-collapse collapse" aria-labelledby="studentSearchAccordion-1" data-tw-parent="#studentSearchAccordion">
                            <div class="accordion-body">
                                <div class="grid grid-cols-12 gap-0 gap-y-2 gap-x-4">
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="application_no" class="form-label">Referrence No</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="application_no" name="application_no" class="form-control registration_no" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label class="form-label">Search By Name</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input id="query-CNTR" autocomplete="off" name="query" type="text" class="form-control"  placeholder="Search by Name">
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                        
                                    </div>

                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="applicantEmail" class="form-label">Applicant Email</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="applicantEmail" name="applicantEmail" class="form-control email" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="phone" class="form-label">Applicant Phone</label>
                                        <div class="autoCompleteField" data-table="students">
                                            <input type="text" autocomplete="off" id="applicantPhone" name="applicantPhone" class="form-control phone" value="" placeholder=""/>
                                            <ul class="autoFillDropdown"></ul>
                                        </div>
                                    </div>
                                    
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="semesters" class="form-label">Intake Semester</label>
                                        <select id="semesters" class="w-full tom-selects" name="semesters[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($semesters))
                                                @foreach($semesters as $sem)
                                                    <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-semesters text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="courses" class="form-label">Course </label>
                                        <select id="courses" class="w-full tom-selects" name="courses[]" multiple>
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
                                        <label for="statuses" class="form-label">Status</label>
                                        <select id="statuses" class="w-full tom-selects" name="statuses[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($statuses))
                                            @foreach($statuses as $crs)
                                                <option value="{{ $crs->id }}">{{ $crs->name }}</option>
                                            @endforeach
                                        @endif
                                        </select>
                                    </div>
                                    
                                    <div class="col-span-12 sm:col-span-3">
                                        <label for="agents" class="form-label">Agent/SubAgents</label>
                                        <select id="agents" class="w-full tom-selects" name="agents[]" multiple>
                                            <option value="">Please Select</option>
                                            @if(!empty($agents))
                                            @foreach($agents as $crs)
                                                <option value="{{ $crs->agent_user_id }}">{{ $crs->full_name }} [{{ $crs->code }}]</option>
                                            @endforeach
                                        @endif
                                        </select>
                                    </div>

                                    
                                    <div class="col-span-12 sm:col-span-6 pt-7">
                                        <button id="studentGroupSearchSubmitBtn" type="button" class="btn btn-success text-white ml-2 w-auto"><i class="w-4 h-4 mr-2" data-lucide="search"></i> Search <i  data-loading-icon="oval" data-color="white" class="w-4 h-4 ml-2 searchLoading hidden" ></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="applicantApplicantionList" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>



    @include('pages.agent.profile.show-modals')

@endsection

@section('script')
    @vite('resources/js/agent-profile.js')
@endsection