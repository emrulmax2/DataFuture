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
                <div class="font-medium text-base">Notes</div>
            </div>
            <div class="col-span-6 text-right relative">
                <button data-tw-toggle="modal" data-tw-target="#addNoteModal" type="button" class="btn btn-primary shadow-md md:mr-2"><i data-lucide="plus-circle" class="w-4 h-4 mr-2"></i>Add Notes</button>
            </div>
        </div>
        <div class="intro-y mt-5">
            <div class="flex flex-col sm:flex-row sm:items-end xl:items-start">
                <form id="tabulatorFilterForm-AN" class="xl:flex sm:mr-auto" >
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Term</label>
                        <select id="term-SN" name="term" class="mt-2 sm:mt-0 sm:w-40 2xl:w-48 tom-selects" >
                            <option selected value="">Please Select</option>
                            @if($terms->count() > 0)
                                @foreach($terms as $trm)
                                    <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Query</label>
                        <input id="query-AN" name="query" type="text" class="form-control sm:w-40 2xl:w-full mt-2 sm:mt-0"  placeholder="Search...">
                    </div>
                    <div class="sm:flex items-center sm:mr-4 mt-2 xl:mt-0">
                        <label class="w-12 flex-none xl:w-auto xl:flex-initial mr-2">Status</label>
                        <select id="status-AN" name="status" class="form-select w-full mt-2 sm:mt-0 sm:w-auto" >
                            <option selected value="1">Active</option>
                            <option value="2">Archived</option>
                        </select>
                    </div>
                    <div class="mt-2 xl:mt-0">
                        <button id="tabulator-html-filter-go-AN" type="button" class="btn btn-primary w-full sm:w-16" >Go</button>
                        <button id="tabulator-html-filter-reset-AN" type="button" class="btn btn-secondary w-full sm:w-16 mt-2 sm:mt-0 sm:ml-1" >Reset</button>
                    </div>
                </form>
                <div class="hidden md:flex mt-5 sm:mt-0">
                    <button id="tabulator-print-AN" class="btn btn-outline-secondary w-1/2 sm:w-auto mr-2">
                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Print
                    </button>
                    <div class="dropdown w-1/2 sm:w-auto">
                        <button class="dropdown-toggle btn btn-outline-secondary w-full sm:w-auto" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-auto sm:ml-2"></i>
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a id="tabulator-export-csv-AN" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export CSV
                                    </a>
                                </li>
                                <li>
                                    <a id="tabulator-export-xlsx-AN" href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Export XLSX
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto scrollbar-hidden">
                <div id="studentNotesListTable" data-student="{{ $student->id }}" class="mt-5 table-report table-report--tabulator"></div>
            </div>
        </div>
    </div>

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

    <!-- BEGIN: Edit Modal -->
    <div id="editNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="editNoteForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Edit Note</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="edit_note_term_declaration_id" class="form-label">Term <span class="text-danger">*</span></label>
                            <select id="edit_note_term_declaration_id" class="w-full tom-selects" name="term_declaration_id">
                                <option value="">Please Select</option>
                                @if($terms->count() > 0)
                                    @foreach($terms as $trm)
                                        <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="edit_opening_date" class="form-label">Opening Date <span class="text-danger">*</span></label>
                            <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="edit_opening_date" class="form-control datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-opening_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="content" class="form-label">Note <span class="text-danger">*</span></label>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="editEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-content text-danger mt-2"></div>
                        </div>
                        <div class="relative theFollowUpCover">
                            <div class="mt-4">
                                <div class="form-check form-switch m-0 flex items-center">
                                    <label class="form-check-label mr-3 ml-0" for="edit_followed_up">Followed Up?</label>
                                    <input id="edit_followed_up" name="followed_up" class="form-check-input" value="yes" type="checkbox">
                                </div>
                            </div>
                            <div class="mt-3 followedUpWrap" style="display: none;">
                                <div class="grid grid-cols-12 gap-4">
                                    {{--<div class="col-span-12 sm:col-span-4">
                                        <label for="edit_follow_up_start" class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input type="text" value="" placeholder="DD-MM-YYYY" id="edit_follow_up_start" class="form-control datepicker" name="follow_up_start" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-follow_up_start text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-12 sm:col-span-4">
                                        <label for="edit_follow_up_end" class="form-label">End Date</label>
                                        <input type="text" value="" placeholder="DD-MM-YYYY" id="edit_follow_up_end" class="form-control datepicker" name="follow_up_end" data-format="DD-MM-YYYY" data-single-mode="true">
                                        <div class="acc__input-error error-follow_up_end text-danger mt-2"></div>
                                    </div>--}}
                                    <div class="col-span-8">
                                        <label for="edit_follow_up_by" class="form-label">Follow Up By <span class="text-danger">*</span></label>
                                        <select id="edit_follow_up_by" class="w-full tom-selects" multiple name="follow_up_by[]">
                                            <option value="">Please Select</option>
                                            @if($users->count() > 0)
                                                @foreach($users as $usr)
                                                    <option value="{{ $usr->id }}">{{ (isset($usr->employee->full_name) ? $usr->employee->full_name : $usr->name) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-follow_up_by text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-4">
                                        <label for="edit_followed_up_status" class="form-label">Followed Up Status <span class="text-danger">*</span></label>
                                        <select id="edit_followed_up_status" class="w-full form-control" name="followed_up_status">
                                            <option value="">Please Select</option>
                                            <option value="Pending">Pending</option>
                                            <option value="Completed">Completed</option>
                                        </select>
                                        <div class="acc__input-error error-followed_up_status text-danger mt-2"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="relative theFlagCover">
                            <div class="mt-4">
                                <div class="form-check form-switch m-0 flex items-center">
                                    <label class="form-check-label mr-3 ml-0" for="edit_is_flaged">Flaged?</label>
                                    <input id="edit_is_flaged" name="is_flaged" class="form-check-input" value="Yes" type="checkbox">
                                </div>
                            </div>
                            <div class="mt-3 flagedWrap" style="display: none;">
                                <div class="grid grid-cols-12 gap-4">
                                    <div class="col-span-6">
                                        <label class="form-label" for="edit_student_flag_id">Available Flags</label>
                                        <select id="edit_student_flag_id" class="w-full form-control" name="student_flag_id">
                                            <option data-color="" value="">Please Select</option>
                                            @if($flags->count() > 0)
                                                @foreach($flags as $flg)
                                                    <option data-color="{{ $flg->color }}" value="{{ $flg->id }}">{{ $flg->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        <div class="acc__input-error error-student_flag_id text-danger mt-2"></div>
                                    </div>
                                    <div class="col-span-6">
                                        <div class="flex justify-start items-center pt-7">
                                            <button class="theFlag w-9 h-9 rounded-full btn bg-slate-200 p-0 text-white"><i data-lucide="flag" class="w-4 h-4"></i></button>
                                            <div class="form-check form-switch m-0 ml-auto flex items-center">
                                                <label class="form-check-label ml-0 mr-5" for="flaged_status">Cleared?</label>
                                                <input id="flaged_status" name="flaged_status" class="form-check-input" value="Cleared" type="checkbox">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex justify-start items-center relative">
                            <a href="#" download class="btn btn-success text-white downloadExistAttachment mr-1 inline-flex" style="display: none;">
                                <i data-lucide="download" class="w-5 h-5"></i>
                            </a>
                            <div class="flex justify-start items-center relative">
                                <label for="editNoteDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                    <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                                </label>
                                <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="editNoteDocument"/>
                                <span id="editNoteDocumentName" class="documentNoteName ml-5"></span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="UpdateNote" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Edit Modal -->

    <!-- BEGIN: Add Modal -->
    <div id="addNoteModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="POST" action="#" id="addNoteForm" enctype="multipart/form-data">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Add Note</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div>
                            <label for="note_term_declaration_id" class="form-label">Term <span class="text-danger">*</span></label>
                            <select id="note_term_declaration_id" class="w-full tom-selects" name="term_declaration_id">
                                <option value="">Please Select</option>
                                @if($terms->count() > 0)
                                    @foreach($terms as $trm)
                                        <option value="{{ $trm->id }}">{{ $trm->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <div class="acc__input-error error-term_declaration_id text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="opening_date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="text" value="{{ date('d-m-Y') }}" placeholder="DD-MM-YYYY" id="opening_date" class="form-control datepicker" name="opening_date" data-format="DD-MM-YYYY" data-single-mode="true">
                            <div class="acc__input-error error-opening_date text-danger mt-2"></div>
                        </div>
                        <div class="mt-3">
                            <label for="content" class="form-label">Note <span class="text-danger">*</span></label>
                            <div class="editor document-editor">
                                <div class="document-editor__toolbar"></div>
                                <div class="document-editor__editable-container">
                                    <div class="document-editor__editable" id="addEditor"></div>
                                </div>
                            </div>
                            <div class="acc__input-error error-content text-danger mt-2"></div>
                        </div>
                        <div class="mt-4">
                            <div class="form-check form-switch m-0 flex items-center">
                                <label class="form-check-label mr-3 ml-0" for="followed_up">Followed Up?</label>
                                <input id="followed_up" name="followed_up" class="form-check-input" value="yes" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 followedUpWrap" style="display: none;">
                            <div class="grid grid-cols-12 gap-4">
                                {{--<div class="col-span-12 sm:col-span-4">
                                    <label for="follow_up_start" class="form-label">Start Date <span class="text-danger">*</span></label>
                                    <input type="text" value="" placeholder="DD-MM-YYYY" id="follow_up_start" class="form-control datepicker" name="follow_up_start" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-follow_up_start text-danger mt-2"></div>
                                </div>
                                <div class="col-span-12 sm:col-span-4">
                                    <label for="follow_up_end" class="form-label">End Date</label>
                                    <input type="text" value="" placeholder="DD-MM-YYYY" id="follow_up_end" class="form-control datepicker" name="follow_up_end" data-format="DD-MM-YYYY" data-single-mode="true">
                                    <div class="acc__input-error error-follow_up_end text-danger mt-2"></div>
                                </div>--}}
                                <div class="col-span-12">
                                    <label for="follow_up_by" class="form-label">Follow Up By <span class="text-danger">*</span></label>
                                    <select data-self="{{ auth()->user()->id }}" id="follow_up_by" class="w-full tom-selects" name="follow_up_by[]" multiple>
                                        <option value="">Please Select</option>
                                        @if($users->count() > 0)
                                            @foreach($users as $usr)
                                                <option value="{{ $usr->id }}">{{ (isset($usr->employee->full_name) ? $usr->employee->full_name : $usr->name) }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-follow_up_by text-danger mt-2"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <div class="form-check form-switch m-0 flex items-center">
                                <label class="form-check-label mr-3 ml-0" for="is_flaged">Flaged?</label>
                                <input id="is_flaged" name="is_flaged" class="form-check-input" value="Yes" type="checkbox">
                            </div>
                        </div>
                        <div class="mt-3 flagedWrap" style="display: none;">
                            <div class="grid grid-cols-12 gap-4">
                                <div class="col-span-6">
                                    <label class="form-label" for="student_flag_id">Available Flags</label>
                                    <select id="student_flag_id" class="w-full form-control" name="student_flag_id">
                                        <option data-color="" value="">Please Select</option>
                                        @if($flags->count() > 0)
                                            @foreach($flags as $flg)
                                                <option data-color="{{ $flg->color }}" value="{{ $flg->id }}">{{ $flg->name }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    <div class="acc__input-error error-student_flag_id text-danger mt-2"></div>
                                </div>
                                <div class="col-span-6">
                                    <button class="theFlag w-9 h-9 rounded-full btn bg-slate-200 p-0 text-white mt-7"><i data-lucide="flag" class="w-4 h-4"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex justify-start items-center relative">
                            <label for="addNoteDocument" class="inline-flex items-center justify-center btn btn-primary  cursor-pointer">
                                <i data-lucide="navigation" class="w-4 h-4 mr-2 text-white"></i> Upload Document
                            </label>
                            <input type="file" accept=".jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx" name="document" class="absolute w-0 h-0 overflow-hidden opacity-0" id="addNoteDocument"/>
                            <span id="addNoteDocumentName" class="documentNoteName ml-5"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        <button type="submit" id="saveNote" class="btn btn-primary w-auto">     
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
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Add Modal -->

    <!-- BEGIN: View Modal -->
    <div id="followUpCommentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <form method="post" action="#" id="followUpCommentForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="flex items-center">
                            <div class="image-fit relative h-10 w-10 flex-none sm:h-12 sm:w-12">
                                <img class="rounded-full" src="{{ (isset($cuser->employee->photo_url) && !empty($cuser->employee->photo_url) ? $cuser->employee->photo_url : asset('build/assets/images/avater.png')) }}" alt="{{ (isset($cuser->employee->full_name) && !empty($cuser->employee->full_name) ? $cuser->employee->full_name : $cuser->name) }}">
                            </div>
                            <div class="ml-3 mr-auto">
                                <div class="text-base font-medium">
                                    {{ (isset($cuser->employee->full_name) && !empty($cuser->employee->full_name) ? $cuser->employee->full_name : $cuser->name) }}
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
    <!-- END: Delete Confirm Modal Content -->
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-note.js')
@endsection