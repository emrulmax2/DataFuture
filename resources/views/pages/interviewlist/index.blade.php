@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y flex flex-col sm:flex-row items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">Interview List</h2>
        <div class="w-full sm:w-auto flex mt-4 sm:mt-0">
        </div>
    </div>
    <!-- BEGIN: HTML Table Data -->
    <div class="intro-y box p-5 mt-5">
        <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
            <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                    <input id="query" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                </div>
                <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                    <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                    <select id="status" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                        <option value="1">Active</option>
                        <option value="2">Archived</option>
                    </select>
                </div>
                <div class="mt-2 xl:mt-0">
                    <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                    <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                </div>
            </form>
            <div class="flex mt-5 sm:mt-0">
                <button data-tw-toggle="modal" data-tw-target="#selectInterviewModal" type="button" class="btn btn-primary shadow-md mr-2 interviewer">Select Interviewer</button>
            </div>
        </div>
        <div class="overflow-x-auto scrollbar-hidden">
            <div id="interviewTableId" class="mt-5 table-report table-report--tabulator"></div>
        </div>
    </div>
    <!-- END: HTML Table Data -->
    <!-- BEGIN: Add Interviewer Modal -->
    <div id="selectInterviewModal" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" action="#" id="interviewerSelectForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Select Interviewer</h2>
                    </div>
                    <div class="modal-body">
                        <input name="ids" type="hidden" />
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
                        <div>
                            <label for="user_id" class="form-label">Interviewer <span class="text-danger">*</span></label>
                            <select id="user_id" name="user_id" class="form-control w-full">
                                <option value="">Please Select</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach    
                            </select>
                            <div class="acc__input-error error-user_id text-danger mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal"
                            class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="assign" class="btn btn-primary w-auto">
                            Assign Interviewer 
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
@endsection
@section('script')
    @vite('resources/js/interviewlist.js')
@endsection