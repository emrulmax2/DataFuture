@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Uploaded files on the admission portal shell: type chips, the document
        list (Tabulator, admission.uploads.list), a Deleted files accordion
        (same list with status 2, restorable), the type picker and the upload
        dialog (Dropzone → admission.upload.documents). Behaviour lives in
        resources/js/admission-uploads.js, whose only markup is this page; the
        record header's actions keep going through admission-global.js,
        admission-show.js and admission-vue.js. Ids kept from the previous
        page: #applicantUploadListTable, #query-UP, #tabulator-html-filter-go-UP,
        #tabulator-html-filter-reset-UP, #tabulator-print-UP,
        #tabulator-export-csv-UP, #tabulator-export-xlsx-UP, #uploadDocumentForm
        (document_setting_id / hard_copy_check / display_file_name),
        .displayNameInput, #documentNameDisplay, #uploadDocBtn, .delete_btn,
        .restore_btn, .downloadDoc, #confirmModal (.agreeWith data-recordid /
        data-status / data-applicant).
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'uploads';

        $adpPaths = [
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/>',
            'flag' => '<path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/>',
            'sign' => '<path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/><circle cx="11" cy="11" r="2"/>',
            'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
            'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
            'ok' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'print' => '<path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>',
            'export' => '<path d="M12 3v12"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/>',
        ];
        $adpIcon = fn ($name, $size = 16) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$adpPaths[$name].'</svg>';
        $adpCheck = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>';
        $adpX = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>';
        $adpSpin = '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
        $adpClose = '<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm adp-modal__close" data-tw-dismiss="modal" aria-label="Close">✕</button>';
        $adpCaret = '<span class="adp-acc-caret" aria-hidden="true">▾</span>';

        // Icon and hint per document type, as the design names them.
        $adpDocMeta = [
            'Proof of ID' => ['user', 'Passport, BRP or national ID'],
            'VISA/ Residence Permit' => ['flag', 'Visa vignette, BRP or share-code print'],
            'Other' => ['file', 'Anything that does not fit the other types'],
            'Offer Acceptance / Audit Trail' => ['sign', 'Signed acceptance form and e-sign audit'],
            'Student Finance Letter' => ['mail', 'SFE entitlement or payment schedule'],
        ];
        $adpMetaOf = fn ($name) => $adpDocMeta[trim((string) $name)] ?? ['file', ''];
        $adpCounts = $docCounts ?? [];
        $adpDeleted = (int) ($deletedCount ?? 0);
    @endphp

    @include('pages.students.admission.partials.portal-record')

    <section class="adp-panel adp-uploads">
        <div class="adp-panel__head adp-panel__head--top">
            <div class="adp-proc__copy">
                <h3 class="adp-h3">Uploaded files</h3>
                <p class="adp-proc__desc">Types come from Document settings where <span class="adp-mono">admission = 1</span>.</p>
            </div>
            <button type="button" class="adp-btn adp-btn--primary adp-btn--sm" data-tw-toggle="modal" data-tw-target="#docTypeModal">{!! $adpIcon('upload', 14) !!}Add document</button>
        </div>

        {{-- One chip per document type: click to upload that type straight away. --}}
        <div class="adp-doc-summary">
            @foreach($docSettings as $adpDs)
                @php [$adpDsIcon, $adpDsHint] = $adpMetaOf($adpDs->name); $adpN = (int) ($adpCounts[$adpDs->id] ?? 0); @endphp
                <button type="button" class="adp-doc-chip{{ $adpN > 0 ? ' has' : '' }}" data-adp-doc-type="{{ $adpDs->id }}" data-adp-doc-name="{{ $adpDs->name }}" data-adp-doc-hint="{{ $adpDsHint }}" data-adp-doc-icon="{{ $adpDsIcon }}" title="{{ $adpN > 0 ? $adpN.' uploaded — add another' : 'Not uploaded yet — click to upload' }}">
                    <span class="adp-doc-chip__ico" data-adp-chip-ico>{!! $adpIcon($adpN > 0 ? 'ok' : $adpDsIcon, 14) !!}</span>
                    <span>{{ $adpDs->name }}</span>
                    <b data-adp-chip-count {{ $adpN > 0 ? '' : 'hidden' }}>{{ $adpN }}</b>
                </button>
            @endforeach
            <span class="adp-doc-chip adp-doc-chip--static{{ ($taskDocCount ?? 0) > 0 ? ' has' : '' }}" title="Files uploaded against process tasks" {{ ($taskDocCount ?? 0) > 0 ? '' : 'hidden' }} data-adp-task-chip>
                {!! $adpIcon('list', 14) !!}<span>Task documents</span><b data-adp-task-count>{{ (int) ($taskDocCount ?? 0) }}</b>
            </span>
        </div>

        <div class="adp-tabletools">
            <form id="tabulatorFilterForm-UP" class="adp-tabletools__filters">
                <input id="query-UP" name="query" type="text" class="adp-ctl" placeholder="Search documents…" autocomplete="off">
                <button id="tabulator-html-filter-go-UP" type="button" class="adp-btn adp-btn--primary adp-btn--sm">{!! $adpIcon('search', 13) !!}Go</button>
                <button id="tabulator-html-filter-reset-UP" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Reset</button>
            </form>
            <div class="adp-tabletools__actions">
                <button type="button" id="tabulator-print-UP" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('print', 13) !!}Print</button>
                <button type="button" id="tabulator-export-csv-UP" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export CSV</button>
                <button type="button" id="tabulator-export-xlsx-UP" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export XLSX</button>
            </div>
        </div>

        <div class="adp-dtable">
            <div id="applicantUploadListTable" data-applicant="{{ $applicant->id }}" class="adp-table"></div>
        </div>

        {{-- Soft-deleted files (admission.uploads.list with status 2); the list is built when opened. --}}
        <details class="adp-accordion adp-accordion--deleted" data-adp-deleted {{ $adpDeleted > 0 ? '' : 'hidden' }}>
            <summary><span>Deleted files <span class="adp-cnt adp-cnt--muted" data-adp-deleted-count>{{ $adpDeleted }}</span></span>{!! $adpCaret !!}</summary>
            <div class="adp-acc-body">
                <div class="adp-dtable">
                    <div id="applicantDeletedListTable" data-applicant="{{ $applicant->id }}" class="adp-table"></div>
                </div>
            </div>
        </details>
    </section>

    {{-- ============================ Step 1: document type ============================ --}}
    <div id="docTypeModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h3>What are you uploading?</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-doc-grid">
                        @foreach($docSettings as $adpDs)
                            @php [$adpDsIcon, $adpDsHint] = $adpMetaOf($adpDs->name); $adpN = (int) ($adpCounts[$adpDs->id] ?? 0); @endphp
                            <button type="button" class="adp-doc-card{{ $adpN > 0 ? ' has' : '' }}" data-adp-doc-type="{{ $adpDs->id }}" data-adp-doc-name="{{ $adpDs->name }}" data-adp-doc-hint="{{ $adpDsHint }}" data-adp-doc-icon="{{ $adpDsIcon }}">
                                <span class="adp-doc-ico">{!! $adpIcon($adpDsIcon, 20) !!}</span>
                                <span class="adp-doc-txt"><b>{{ $adpDs->name }}</b>@if($adpDsHint !== '')<small>{{ $adpDsHint }}</small>@endif</span>
                                <span class="adp-doc-state{{ $adpN > 0 ? ' ok' : '' }}" data-adp-doc-state>@if($adpN > 0){!! $adpIcon('ok', 13) !!}{{ $adpN }} on file @else Not uploaded @endif</span>
                            </button>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================ Step 2: files + name + hard copy ============================ --}}
    <div id="uploadDocumentModal" class="modal adp-modal" data-tw-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header"><h3>Upload documents</h3>{!! $adpClose !!}</div>
                <div class="modal-body">
                    <div class="adp-doc-picked">
                        <span class="adp-doc-picked__ico" data-adp-upload-ico>{!! $adpIcon('file', 18) !!}</span>
                        <span class="adp-doc-picked__t"><b data-adp-upload-type>Document</b><small data-adp-upload-hint></small></span>
                        <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-change-type>Change type</button>
                    </div>
                    <form method="post" action="{{ route('admission.upload.documents') }}" class="dropzone adp-dropzone" id="uploadDocumentForm" enctype="multipart/form-data">
                        @csrf
                        <div class="fallback"><input name="documents[]" multiple type="file" /></div>
                        <div class="dz-message" data-dz-message>
                            <span class="adp-dz-ico">{!! $adpIcon('upload', 22) !!}</span>
                            <b>Drop files here or click to browse</b>
                            <small>PDF, Office, image or text · up to 5 MB each · 10 files per upload</small>
                        </div>
                        <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
                        <input type="hidden" name="document_setting_id" value="0"/>
                        <input type="hidden" name="hard_copy_check" value="0"/>
                        <input type="hidden" name="display_file_name" value=""/>
                    </form>
                    <ul class="adp-dz-list" data-adp-dz-list></ul>
                    <div class="adp-notice adp-notice--bad" data-adp-upload-error hidden></div>
                    <div class="adp-grid-2">
                        <div class="adp-field">
                            <label for="display_name">Document name</label>
                            <input type="text" id="display_name" name="display_name" value="" class="displayNameInput adp-ctl" autocomplete="off">
                            <span class="adp-opt__hint">Shown in the list instead of the file name: <span id="documentNameDisplay" class="adp-mono"></span></span>
                        </div>
                        <div class="adp-field">
                            <label for="hard_copy_check_status">Hard copy checked?</label>
                            <div>
                                <label class="adp-sw" for="hard_copy_check_status"><input id="hard_copy_check_status" class="adp-sw__input" name="hard_copy_check_status" value="1" type="checkbox"><span class="adp-sw__dot"><span class="adp-sw__on">{!! $adpCheck !!}</span><span class="adp-sw__off">{!! $adpX !!}</span></span><span class="adp-sw__yes">Yes</span><span class="adp-sw__no">No</span></label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" data-tw-dismiss="modal" class="adp-btn adp-btn--ghost">Cancel</button>
                    <button type="button" id="uploadDocBtn" class="adp-btn adp-btn--primary">{!! $adpIcon('upload', 14) !!}<span data-adp-upload-label>Upload</span> {!! $adpSpin !!}</button>
                </div>
            </div>
        </div>
    </div>

    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs', ['adpConfirmAttrs' => 'data-recordid="0" data-status="none" data-applicant="'.$applicant->id.'"'])
@endsection

@section('script')
    @vite('resources/js/admission-uploads.js')
    {{-- The record header's Change status dialog (admission-show.js guards its other blocks). --}}
    @vite('resources/js/admission-show.js')
    @vite('resources/js/admission-vue.js')
@endsection
