@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="grid grid-cols-12 gap-6">       
        <div class="col-span-12 mt-8">
            <div class="intro-y flex items-center h-10">
                <h2 class="text-lg font-medium truncate mr-5">Pending Followups</h2>
                <a href="{{ route('dashboard') }}" class="ml-auto btn btn-primary text-white">
                    Back to Dashboard
                </a>
            </div>
        </div>
        <div class="col-span-12">
            <div class="intro-y box p-5">
                <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                    <form id="tabulatorFilterForm" class="xl:flex sm:mr-auto" >
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Terms</label>
                            <select name="flup_term_declaration_id" id="flup_term_declaration_id" class="form-control sm:w-40 2xl:w-60 mt-2 sm:mt-0">
                                <option value="">All Terms</option>
                                @if($terms->count() > 0)
                                    @foreach($terms as $trm)
                                        <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                            <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                            <select name="status" id="flup_status" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0">
                                <option value="Pending">Pending</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>
                        <div class="mt-2 xl:mt-0">
                            <button id="tabulator-html-filter-go" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                            <button id="tabulator-html-filter-reset" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                        </div>
                    </form>
                    <div class="flex mt-5 sm:mt-0"></div>
                </div>
                <div class="overflow-x-auto scrollbar-hidden">
                    <div id="pendingFollowupsListTable" class="mt-5 table-report table-report--tabulator"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- BEGIN: View Modal -->
    <div id="followUpCommentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="post" action="#" id="followUpCommentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="flex items-center">
                            <div class="image-fit relative h-10 w-10 flex-none sm:h-12 sm:w-12">
                                <img class="rounded-full" src="{{ (isset($user->employee->photo_url) && !empty($user->employee->photo_url) ? $user->employee->photo_url : asset('build/assets/images/avater.png')) }}" alt="{{ (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name) }}">
                            </div>
                            <div class="ml-3 mr-auto">
                                <div class="text-base font-medium">
                                    {{ (isset($user->employee->full_name) && !empty($user->employee->full_name) ? $user->employee->full_name : $user->name) }}
                                </div>
                                <div class="text-xs text-slate-500 sm:text-sm">
                                    Online
                                </div>
                            </div>
                        </div>
                        <a class="ml-auto" data-tw-dismiss="modal" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-slate-400"></i></a>
                    </div>
                    <div class="modal-body">
                        <div class="scrollbar-hidden flex-1 overflow-y-scroll px-0 pt-5" id="followUpCommentWrap">
                            Loading...
                        </div>
                    </div>
                    <div class="modal-footer p-0">
                        <div class="flex items-center py-4">
                            <textarea rows="1" id="the_comment" name="comment" placeholder="Type your comment..." class="py-3 px-5 border-transparent rounded-md resize-none w-full h-[46px] shadow-none text-sm focus:border-transparent focus:ring-0"></textarea>
                            
                            <button type="submit" id="postCommentBtn" class="mr-5 flex h-8 w-8 flex-none items-center justify-center rounded-full bg-primary text-white sm:h-10 sm:w-10 relative">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="send" class="lucide lucide-send stroke-1.5 h-4 w-4 theIcon"><path d="m22 2-7 20-4-9-9-4Z"></path><path d="M22 2 11 13"></path></svg>
                                <svg style="display: none; position: absolute; left: 0; top: 0; right: 0; bottom: 0;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg"
                                    stroke="white" class="w-4 h-4 m-auto theLoader">
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
                    <input type="hidden" name="student_note_id" value="0"/>
                </div>
            </form>
        </div>
    </div>
    <!-- END: View Modal -->

    <!-- BEGIN: View Modal -->
    <div id="viewNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Note</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    
                </div>
                <div class="modal-footer">
                    <div class="footerBtns" style="float: left"></div>
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: View Modal -->

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
                        <button type="button" data-recordid="0" data-status="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->

    <!-- BEGIN: Delete Confirm Modal Content -->
    <div id="confirmModalN" class="modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <div class="p-5 text-center">
                        <i data-lucide="check-circle" class="w-16 h-16 text-success mx-auto mt-3"></i>
                        <div class="text-3xl mt-5 confModTitle font-medium">Are you sure?</div>
                    </div>
                    <div class="px-5 pb-8 text-center">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-24 mr-1">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" class="agreeWith btn btn-danger w-auto">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-note-followups.js')
@endsection
