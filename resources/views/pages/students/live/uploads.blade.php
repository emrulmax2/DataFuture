@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
    @vite('resources/css/student-documents.css')
@endsection

@section('subcontent')

    <!-- BEGIN: Profile Info -->
    @include('pages.students.live.show-info')
    <!-- END: Profile Info -->

    @php
        $canAddDocument = isset(auth()->user()->priv()['document_add']) && auth()->user()->priv()['document_add'] == 1;
    @endphp

    {{-- Documents: uniform records, so one table, newest first. Each row says
         whether the original was seen at upload (green) or only a copy is held
         (neutral). The list is drawn by resources/js/student-upload.js from
         student.uploads.list; this is the frame it draws into. The data-vault-*
         flags only pick which message to show before asking for a PIN for an
         encrypted file; the server decides. --}}
    <section id="studentDocsApp" class="sdc intro-y mt-5" aria-labelledby="sdcTitle"
             data-student="{{ $student->id }}"
             data-student-name="{{ $student->full_name }}"
             data-student-ref="{{ $student->registration_no }}"
             data-can-add="{{ $canAddDocument ? 1 : 0 }}"
             data-vault-can-use="{{ $vault['can_use'] ? 1 : 0 }}"
             data-vault-has-pin="{{ $vault['has_pin'] ? 1 : 0 }}"
             data-vault-impersonating="{{ $vault['impersonating'] ? 1 : 0 }}"
             data-vault-pin-url="{{ route('user.account.document.pin') }}">
        <header class="sdc-head">
            <div>
                <h2 id="sdcTitle" class="sdc-title">Documents</h2>
                <p class="sdc-summary" id="sdcSummary">Loading documents…</p>
            </div>
            <div class="sdc-head-acts">
                <button type="button" class="sdc-btn sdc-btn--ghost" id="sdcPrint">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                    Print
                </button>
                <div class="sdc-menu">
                    <button type="button" class="sdc-btn sdc-btn--ghost" id="sdcExportBtn" aria-haspopup="true" aria-expanded="false" aria-controls="sdcExportMenu">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M12 11v6m-3-3 3 3 3-3"/></svg>
                        Export
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="sdc-menu-list" id="sdcExportMenu" hidden>
                        <button type="button" data-export="csv">Export CSV</button>
                        <button type="button" data-export="xlsx">Export XLSX</button>
                    </div>
                </div>
                <button type="button" class="sdc-btn" id="downloadIDCardBtn" data-studentid="{{ $student->id }}">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="5" y="3" width="14" height="18" rx="2"/><circle cx="12" cy="10" r="2.5"/><path d="M8.5 17a3.5 3.5 0 0 1 7 0"/></svg>
                    Print ID Card
                </button>
                @if($canAddDocument)
                    <div class="sdc-menu">
                        <button type="button" class="sdc-btn sdc-btn--primary" id="sdcAddBtn" aria-haspopup="true" aria-expanded="false" aria-controls="sdcAddMenu">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                            Add Document
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="sdc-menu-list sdc-menu-list--wide" id="sdcAddMenu" hidden>
                            <p class="sdc-menu-head">Choose the document type</p>
                            @forelse($docSettings as $ds)
                                <button type="button" data-doc-setting="{{ $ds->id }}" data-label="{{ $ds->name }}">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M12 12v6M9 15h6"/></svg>
                                    {{ $ds->name }}
                                </button>
                            @empty
                                <p class="sdc-menu-empty">No document types are set up yet. Add them in Site Settings.</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </div>
        </header>

        <div class="sdc-bar">
            <div class="sdc-seg" role="group" aria-label="Filter by original seen" id="sdcSeg"></div>
            <label class="sdc-field">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input id="sdcQuery" type="search" placeholder="Search document, staff or ID" aria-label="Search documents" autocomplete="off">
            </label>
            <select id="sdcStaff" class="sdc-select" aria-label="Uploaded by">
                <option value="">Uploaded by anyone</option>
            </select>
            <select id="sdcStatus" class="sdc-select" aria-label="Document status">
                <option value="1" selected>Active</option>
                <option value="2">Archived</option>
            </select>
            <button type="button" class="sdc-link" id="sdcReset" hidden>Clear filters</button>
        </div>

        <div class="sdc-grid" role="table" aria-label="Documents" aria-describedby="sdcSummary">
            <div class="sdc-hrow" role="row" id="sdcHead"></div>
            <div id="sdcRows" role="rowgroup" aria-busy="true">
                <div class="sdc-empty">Loading documents…</div>
            </div>
        </div>

        <footer class="sdc-foot">
            <div class="sdc-size">
                <label for="sdcSize">Rows per page</label>
                <select id="sdcSize" class="sdc-select sdc-select--sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="0">All</option>
                </select>
                <span id="sdcRange"></span>
            </div>
            <nav class="sdc-pager" id="sdcPager" aria-label="Document pages"></nav>
        </footer>
    </section>

    {{-- Every open of this student's documents and every attempt that was
         turned away. Read-only, newest first, paged on the server: unlike the
         documents themselves this only ever grows. --}}
    <section id="studentDocLogApp" class="sdc sdc--log intro-y mt-5" aria-labelledby="sdcLogTitle" data-student="{{ $student->id }}">
        <header class="sdc-head">
            <div>
                <h2 id="sdcLogTitle" class="sdc-title sdc-title--sub">Document access log</h2>
                <p class="sdc-summary">Who opened this student's documents, when, and from which session.</p>
            </div>
            <div class="sdc-head-acts">
                <select id="sdcLogEvent" class="sdc-select" aria-label="Show log entries">
                    <option value="" selected>Everything</option>
                    <option value="opened">Opened (viewed or downloaded)</option>
                    <option value="blocked">Blocked attempts</option>
                    <option value="impersonated">While impersonating</option>
                </select>
                <button type="button" class="sdc-btn sdc-btn--ghost" id="sdcLogPrint">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/></svg>
                    Print
                </button>
                <div class="sdc-menu">
                    <button type="button" class="sdc-btn sdc-btn--ghost" id="sdcLogExportBtn" aria-haspopup="true" aria-expanded="false" aria-controls="sdcLogExportMenu">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M12 11v6m-3-3 3 3 3-3"/></svg>
                        Export
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="sdc-menu-list" id="sdcLogExportMenu" hidden>
                        <button type="button" data-log-export="csv">Export CSV</button>
                        <button type="button" data-log-export="xlsx">Export XLSX</button>
                    </div>
                </div>
            </div>
        </header>

        <div class="sdc-grid" role="table" aria-label="Document access log">
            <div class="sdc-hrow" role="row">
                <span role="columnheader">When</span>
                <span role="columnheader">Event</span>
                <span role="columnheader">Document</span>
                <span role="columnheader">By</span>
                <span role="columnheader">IP address</span>
            </div>
            <div id="sdcLogRows" role="rowgroup" aria-busy="true">
                <div class="sdc-empty">Loading the log…</div>
            </div>
        </div>

        <footer class="sdc-foot" id="sdcLogFoot" hidden>
            <div class="sdc-size">
                <label for="sdcLogSize">Rows per page</label>
                <select id="sdcLogSize" class="sdc-select sdc-select--sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="0">All</option>
                </select>
                <span id="sdcLogRange"></span>
            </div>
            <nav class="sdc-pager" id="sdcLogPager" aria-label="Access log pages"></nav>
        </footer>
    </section>
    <div class="sdc-toast" id="sdcToast" role="status" aria-live="polite"></div>


    <!-- BEGIN: Import Modal -->
    <div id="uploadDocumentModal" class="modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="font-medium text-base mr-auto">Upload Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;">
                        <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post"  action="{{ route('student.upload.documents') }}" class="dropzone" id="uploadDocumentForm" style="padding: 5px;" enctype="multipart/form-data">
                        @csrf    
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <div class="text-lg font-medium">Drop files here or click to upload.</div>
                            <div class="text-slate-500">
                                Max file size 5MB & max file limit 10.
                            </div>
                        </div>
                        <input type="hidden" name="student_id" value="{{ $student->id }}"/>
                        <input type="hidden" name="document_setting_id" value="0"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="is_encrypted" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>
                    <div class="mt-3">
                        <label class="form-label">Document Name</label>
                        <span id="documentNameDisplay" class="block mb-1"></span>
                        <input type="text" name="display_name" value="" class="displayNameInput form-control w-full"/>
                    </div>
                    {{-- Yes / No as a switch: off is No. The word beside it is drawn by CSS from the switch's state. --}}
                    <div class="mt-4">
                        <div class="form-check form-switch m-0 flex items-center student-profile-vault-toggle">
                            <label class="form-check-label mr-auto ml-0" for="uploadHardCopyToggle">Hard Copy Checked?</label>
                            <input id="uploadHardCopyToggle" name="hard_copy_check_status" class="form-check-input" value="1" type="checkbox">
                            <span class="student-profile-vault-toggle-state" aria-hidden="true"></span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="form-check form-switch m-0 flex items-center student-profile-vault-toggle">
                            <label class="form-check-label mr-auto ml-0" for="uploadEncryptToggle">Encrypt Document?</label>
                            <input id="uploadEncryptToggle" name="is_encrypted_status" class="form-check-input" value="1" type="checkbox" aria-describedby="uploadEncryptHint">
                            <span class="student-profile-vault-toggle-state" aria-hidden="true"></span>
                        </div>
                        <p id="uploadEncryptHint" class="student-profile-vault-hint">An encrypted document can only be opened with a document PIN, by staff who have PIN Enabled in their privileges. This cannot be changed after upload.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                    <button type="button" id="uploadDocBtn" class="btn btn-primary w-auto">     
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

    <!-- BEGIN: Encrypted Document PIN Modal -->
    <div id="documentPinModal" class="modal student-profile-vault-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="post" action="#" id="documentPinForm" autocomplete="off">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="font-medium text-base mr-auto">Encrypted Document</h2>
                        <a data-tw-dismiss="modal" href="javascript:;">
                            <i data-lucide="x" class="w-5 h-5 text-slate-400"></i>
                        </a>
                    </div>
                    <div class="modal-body">
                        <div class="student-profile-vault-docname">
                            <i data-lucide="lock-keyhole" class="w-4 h-4"></i>
                            <span id="documentPinDocName"></span>
                        </div>
                        <div class="mt-4">
                            <label for="documentPinInput" class="form-label">{{ $vault['impersonating'] ? 'Enter your own document PIN to open this file' : 'Enter your document PIN to open this file' }}</label>
                            <input type="password" id="documentPinInput" name="pin" class="form-control w-full student-profile-vault-pin" inputmode="numeric" pattern="[0-9]*" maxlength="{{ $vault['pin_max_length'] }}" autocomplete="one-time-code"/>
                            <div id="documentPinError" class="student-profile-vault-error" role="alert"></div>
                        </div>

                        {{-- Shown for a file a browser cannot display (Word, Excel...): it can only be saved. --}}
                        <p id="documentPinSaveNote" class="student-profile-vault-note" hidden>
                            <i data-lucide="file-down" class="w-4 h-4"></i>
                            <span>This type of file cannot be shown in the browser, so it will be saved to your computer.</span>
                        </p>

                        @if($vault['impersonating'])
                            {{-- Signed in as somebody else: it is the impersonator's own PIN that is wanted, and their name that goes in the log. --}}
                            <p class="student-profile-vault-note student-profile-vault-note--impersonating">
                                <i data-lucide="user-cog" class="w-4 h-4"></i>
                                <span>You are signed in as <strong>{{ $vault['signed_in_as'] }}</strong>. Use your own PIN, not theirs. This will be recorded as opened by <strong>{{ $vault['holder_name'] }}</strong> through impersonation.</span>
                            </p>
                        @else
                            <p class="student-profile-vault-note">
                                <i data-lucide="scan-eye" class="w-4 h-4"></i>
                                <span>Opening this document is recorded against your name.</span>
                            </p>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary w-20 mr-1">Cancel</button>
                        {{-- The one way in: the file opens in the browser, and a copy is saved from there if one is wanted. --}}
                        <button type="button" id="documentPinViewBtn" data-mode="view" class="documentPinSubmit btn btn-primary w-auto">View</button>
                        <input type="hidden" name="row_id" value="0"/>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Encrypted Document PIN Modal -->



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


    <div id="downloadIDCard" data-tw-backdrop="static" class="modal" tabindex="-1" aria-hidden="true">
        <a data-tw-dismiss="modal" class="hideIDCardModalBtn btn btn-linkedin text-white btn-rounded m-0 p-0 w-9 h-9" href="javascript:;"><i data-lucide="x" class="w-5 h-5 text-white"></i></a>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="idLoader flex justify-center items-center p-10"><i data-loading-icon="rings" class="w-20 h-20"></i></div>
                    <div class="idContent" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @vite('resources/js/student-global.js')
    @vite('resources/js/student-upload.js')
@endsection
