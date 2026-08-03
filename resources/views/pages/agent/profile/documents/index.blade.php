@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="agm-page agm-profile-page">
        @include('pages.agent.profile.show-info')

        <section class="agm-profile-panel agm-document-panel">
            <div class="agm-profile-panel__header agm-document-panel__header">
                <div class="agm-section-title">
                    <span aria-hidden="true"></span>
                    <h2>Documents</h2>
                </div>

                <div class="dropdown agm-doc-dropdown" id="uploadsDropdown">
                    <button class="dropdown-toggle agm-btn agm-btn--primary agm-doc-dropdown__toggle" aria-expanded="false" data-tw-toggle="dropdown" type="button">
                        <i data-lucide="upload"></i>
                        <span>Add Document List</span>
                        <i data-lucide="chevron-down" class="agm-doc-dropdown__caret"></i>
                    </button>

                    <div class="dropdown-menu agm-doc-dropdown__menu">
                        <div class="dropdown-content agm-doc-dropdown__content">
                            <div class="agm-doc-dropdown__header">Document List</div>

                            @if(isset($docSettings) && !empty($docSettings) && $docSettings->count() > 0)
                                <div class="agm-doc-dropdown__list">
                                    @foreach($docSettings as $ds)
                                        <label class="agm-doc-type" for="employee_doc_{{ $ds->id }}">
                                            <input id="employee_doc_{{ $ds->id }}" name="employee_doc_ids[]" class="employee_doc_ids agm-doc-type__input" type="radio" value="{{ $ds->id }}" data-label="{{ $ds->name }}">
                                            <span class="agm-doc-type__label">
                                                <i data-lucide="file-text"></i>
                                                <span>{{ $ds->name }}</span>
                                            </span>
                                            <span class="agm-doc-type__check">
                                                <i data-lucide="x"></i>
                                                <i data-lucide="check"></i>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="agm-doc-dropdown__empty">
                                    <i data-lucide="alert-triangle"></i>
                                    <span>There are no document settings available right now.</span>
                                </div>
                            @endif

                            <div class="agm-doc-dropdown__footer">
                                <button type="button" id="employeeDocumentUploaders" class="agm-btn agm-btn--primary">
                                    <i data-lucide="upload"></i>
                                    Upload Documents
                                </button>
                                <button type="button" id="closeUploadsDropdown" class="agm-btn agm-btn--muted">
                                    <i data-lucide="x"></i>
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="agm-profile-table-wrap agm-document-table-wrap">
                <div id="employeeDocumentListTable" data-employee="{{ $employee->id }}" class="agm-profile-table agm-agent-table agm-document-table"></div>
            </div>
        </section>
    </div>

    <div id="uploadEmployeeDocumentModal" class="modal agm-agent-modal agm-document-upload-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Upload Documents</h2>
                    <a data-tw-dismiss="modal" href="javascript:;" aria-label="Close">
                        <i data-lucide="x"></i>
                    </a>
                </div>
                <div class="modal-body">
                    <form method="post" action="{{ route('agent-user.documents.upload.documents') }}" class="dropzone agm-document-dropzone" id="uploadDocumentForm" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback">
                            <input name="documents[]" multiple type="file" />
                        </div>
                        <div class="dz-message" data-dz-message>
                            <span class="agm-document-dropzone__icon">
                                <i data-lucide="upload-cloud"></i>
                            </span>
                            <strong>Drop files here or click to upload</strong>
                            <small>Max file size 20MB and up to 10 files per upload.</small>
                        </div>
                        <input type="hidden" name="agent_id" value="{{ $employee->id }}"/>
                        <input type="hidden" name="document_setting_id" value="0"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>

                    <div class="agm-document-upload-meta">
                        <div class="agm-document-upload-name">
                            <span>Document Type</span>
                            <strong id="documentNameDisplay">Selected document type</strong>
                        </div>

                        <div class="agm-agent-form-field">
                            <label for="agent_document_display_name">Document Name</label>
                            <input id="agent_document_display_name" type="text" name="doc_name" class="displayNameInput" placeholder="Document name">
                        </div>

                        <div class="agm-agent-form-field">
                            <label>Hard Copy Checked?</label>
                            <div class="agm-document-choice-group">
                                <label class="agm-document-choice" for="hard_copy_check-1">
                                    <input id="hard_copy_check-1" class="agm-document-choice__input" type="radio" value="1" name="hard_copy_check_status">
                                    <span>
                                        <i data-lucide="check"></i>
                                        Yes
                                    </span>
                                </label>
                                <label class="agm-document-choice" for="hard_copy_check-2">
                                    <input checked id="hard_copy_check-2" class="agm-document-choice__input" type="radio" value="0" name="hard_copy_check_status">
                                    <span>
                                        <i data-lucide="x"></i>
                                        No
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="btn btn-outline-secondary">
                        <i data-lucide="x"></i>
                        Cancel
                    </button>
                    <button type="button" id="uploadEmpDocBtn" class="btn btn-primary">
                        <i data-lucide="upload"></i>
                        Upload
                        <svg style="display: none;" width="25" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="white" class="w-4 h-4">
                            <g fill="none" fill-rule="evenodd">
                                <g transform="translate(1 1)" stroke-width="4">
                                    <circle stroke-opacity=".5" cx="18" cy="18" r="18"></circle>
                                    <path d="M36 18c0-9.94-8.06-18-18-18">
                                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform>
                                    </path>
                                </g>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="successModal" class="modal agm-agent-feedback-modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="check-circle"></i>
                    <h2 class="successModalTitle">Done</h2>
                    <p class="successModalDesc"></p>
                    <div class="agm-agent-feedback-actions">
                        <button type="button" data-action="DISMISS" class="successCloser agm-btn agm-btn--primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="warningModal" class="modal agm-agent-feedback-modal agm-agent-feedback-modal--danger" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="alert-octagon"></i>
                    <h2 class="warningModalTitle">Oops!</h2>
                    <p class="warningModalDesc"></p>
                    <div class="agm-agent-feedback-actions">
                        <button type="button" data-action="DISMISS" class="warningCloser agm-btn agm-btn--primary">Ok</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal agm-agent-feedback-modal agm-agent-feedback-modal--danger" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <i data-lucide="x-circle"></i>
                    <h2 class="confModTitle">Are you sure?</h2>
                    <p class="confModDesc"></p>
                    <div class="agm-agent-feedback-actions">
                        <button type="button" class="disAgreeWith agm-btn agm-btn--muted">No, Cancel</button>
                        <button type="button" data-recordid="0" data-status="none" data-employee="{{ $employee->id }}" class="agreeWith agm-btn agm-btn--danger">Yes, I agree</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php($hideAgentProfileSuccessModal = true)
    @include('pages.agent.profile.show-modals')
@endsection

@section('script')
    @vite('resources/js/agent-global.js')
    @vite('resources/js/agent-upload.js')
@endsection
