@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Interview List</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
            <a id="assignedPageLoad" href="{{ route('staff.dashboard') }}" type="button" class="btn btn-secondary w-auto  mt-2 sm:mt-0 sm:ml-1  mr-2" ><i data-lucide="arrow-left"  class="w-4 h-4 mr-2"></i> Back</a>
        </div>
    </div>
                                    
    @if($unfinishedInterviewCount)
        <div class="intro-y flex flex-col sm:flex-row justify-center items-center mt-5 w-full">
            <div role="alert" class="alert relative alert-primary show mb-2 px-5 py-4" >
                <div class="flex items-center">
                    <div class="text-lg font-medium uppercase">
                        <a href="{{ route('applicant.interview.session.list',\Auth::id()) }}">Unfinish interview{{ $unfinishedInterviewCount>1 ? 's are': ' is' }} waiting. ({{ $unfinishedInterviewCount }}) </a>
                    </div>
                    <div class="text-xs bg-white px-1 rounded-md text-slate-700 ml-auto">
                        New
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('applicant.interview.session.list',\Auth::id()) }}"> Some unfinished interview{{ $unfinishedInterviewCount>1 ? 's are': ' is' }} still waiting. <b class=" font-medium"> Click here </b> to view waiting session. </a>
                </div>
            </div>
        </div>
    @endif 
    <!-- BEGIN: HTML Table Data -->
    {{-- <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                    <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                </div>
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Search By</label>
                    <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                        <option value="">Please Select</option>
                        <option value="applicantName">Applicant Name</option>
                        <option value="applicantNumber">Applicant Number</option>
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>              
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                <a id="assignedPageLoad" href="{{ route('applicant.interview.session.list',\Auth::id()) }}" type="button" class="btn btn-warning w-auto sm:w-56 mt-2 sm:mt-0 sm:ml-1  mr-2" >View Sessions</a>
                --}}
                {{-- <button data-tw-toggle="modal" data-tw-target="#selectInterviewModal" type="button" class="btn btn-primary shadow-md mr-2 interviewer">Take Interview</button> --}}               
            {{-- </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="interviewTableId" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div> --}}
    <!-- END: HTML Table Data -->
    <!-- Tabs -->
    <div class="intro-y box p-5 mt-5">
    <div class="intro-y pt-2">
        <ul class="nav nav-link-tabs border-b border-slate-200/60" role="tablist">
            <li id="process-1-tab" class="nav-item mr-10 flex" role="presentation">
                <button class="nav-link font-medium text-slate-500 py-2 px-0 active" data-tw-toggle="pill" data-tw-target="#process-tab-1" type="button" role="tab" aria-controls="process-tab-1" aria-selected="true">
                    Pending
                </button>
            </li>
            <li id="process-2-tab" class="nav-item flex" role="presentation">
                <button class="nav-link font-medium text-slate-500 py-2  px-0" data-tw-toggle="pill" data-tw-target="#process-tab-2" type="button" role="tab" aria-controls="process-tab-2" aria-selected="false">
                    In Progress
                </button>
            </li>
            <li id="process-3-tab" class="nav-item ml-10 flex" role="presentation">
                <button class="nav-link font-medium text-slate-500 py-2  px-0" data-tw-toggle="pill" data-tw-target="#process-tab-3" type="button" role="tab" aria-controls="process-tab-3" aria-selected="false">
                    Archived
                </button>
            </li>
        </ul>
        <div class="tab-content mt-5">
            <div id="process-tab-1" class="tab-pane leading-relaxed active" role="tabpanel" aria-labelledby="process-1-tab">
                <div class="intro-y box p-5 mt-5">
                    <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                        <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                                <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                            </div>
                            <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                                <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Search By</label>
                                <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                                    <option value="">Please Select</option>
                                    <option value="applicantName">Applicant Name</option>
                                    <option value="applicantNumber">Applicant Number</option>
                                </select>
                            </div>
                            <div class="mt-2 xl:mt-0">
                                <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                                <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>              
                            </div>
                        </form>
                        <div class="flex mt-5 sm:mt-0">
                            <a id="assignedPageLoad" href="{{ route('applicant.interview.session.list',\Auth::id()) }}" type="button" class="btn btn-warning w-auto sm:w-56 mt-2 sm:mt-0 sm:ml-1  mr-2" >View Sessions</a>
                            
                            {{-- <button data-tw-toggle="modal" data-tw-target="#selectInterviewModal" type="button" class="btn btn-primary shadow-md mr-2 interviewer">Take Interview</button> --}}               
                        </div>
                    </div>
                    <div class="overflow-x-auto scrollbar-hidden">
                        <div id="interviewTableId" class="mt-5 table-report table-report--tabulator"></div>
                    </div>
                </div>
            </div>
            <div id="process-tab-2" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-2-tab">
            </div>
            <div id="process-tab-3" class="tab-pane leading-relaxed" role="tabpanel" aria-labelledby="process-3-tab">
            </div>
        </div>
    </div>
    </div>
    <!-- BEGIN: Add Interviewer Modal -->
    <div id="selectInterviewModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="interviewerSelectForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Do you want to confirm interview session for ?</h2>
                    </div>
                    <div class="modal-body">
                        <input id="ids" name="ids" value="" type="hidden" />
                        <div class="flex mt-5 pb-10 sm:mt-0 overflow-x-auto">
                                <table class="w-full p-6 text-xs text-left whitespace-nowrap">
                                        <thead class="bg-cyan-700 text-white">
                                            
                                            <tr class="text-center">
                                                <th class="p-2 border border-cyan-800">Serial</th>
                                                <th class="p-2 border border-cyan-800">Applicant Name</th>
                                                <th class="p-2 border border-cyan-800 ">Applicant Number</th>
                                            </tr>

                                        </thead>
                                        <tbody id="intervieweelist">

                                            <tr  class="text-center">
                                                <td class="p-2 border border-cyan-800">1</th>
                                                <th class="p-2 border border-cyan-800">Marry</th>
                                                <th class="p-2 border border-cyan-800">1000021</th>
                                            </tr>
                                        </tbody>
                                </table>

                        </div>
                        <input type="hidden" id="user" name="user" value = "{{ \Auth::id() }}" />
                        {{-- <div>
                            <label for="user" class="form-label">Interviewer <span class="text-danger">*</span></label>
                            <select id="user" name="user" class="form-control w-full user__input">
                                <option value="">Please Select</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach    
                            </select>
                            <div id="error-user" class="user__input-error error-user text-danger mt-2"></div>
                        </div> --}}
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">No</button>
                        <button type="submit" id="assign" class="btn btn-primary w-auto">
                            Yes 
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
            </form>
        </div>
    </div>
    <!-- END: Add Interviewer Modal -->

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
                        <button type="button" data-tw-dismiss="modal" class="btn btn-primary w-24">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Success Modal Content -->
    <!-- BEGIN: Student Profile Lock Modal -->
    <div id="callLockModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="callLockModalForm" enctype="multipart/form-data">
                <div class="modal-content">
    
                    <div class="modal-body">
                        <div>
                            <label for="dob" class="form-label">Please provide applicant date of birth to unlock profile <span class="text-danger">*</span></label>
                            <input id="dob" type="text" name="dob" class="datepicker form-control w-full" placeholder="DD-MM-YYYY" data-format="DD-MM-YYYY"  data-single-mode="true" >
                            <div class="dob__input-error error-name text-danger mt-2"></div>
                            <input type="hidden" id="applicantId" name="applicantId" value="">
                            <input type="hidden" id="taskListId" name="taskListId" value="">
                        </div>    
                        
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="unlock" class="btn btn-primary w-auto">     
                            <i data-lucide="unlock" class="stroke-1.5 h-5 w-5 mr-1"></i> Unlock                      
                            <svg class="loading" style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
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
            </form>
        </div>
    </div>
    <!-- END: Student Profile Lock Modal -->
@endsection
@section('script')
    @vite('resources/js/interviewlist.js')
@endsection