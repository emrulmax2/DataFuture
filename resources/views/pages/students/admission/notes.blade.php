@extends('../layout/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    {{--
        Notes on the admission portal shell: an inline composer (rich text +
        one attachment → admission.store.note), the notes timeline rendered
        from admission.note.list, an Edit dialog (admission.get.note /
        admission.update.note, attachment replaceable), delete and restore
        (admission.destory.note / admission.resotore.note) and a Deleted notes
        accordion. Behaviour lives in resources/js/admission-notes.js, whose
        only markup is this page. Ids kept from the previous page:
        #addNoteForm, #saveNote, input[name=id], #query-AN, #tabulator-html-filter-go-AN,
        #tabulator-html-filter-reset-AN, #tabulator-print-AN,
        #tabulator-export-csv-AN, #tabulator-export-xlsx-AN, .edit_btn,
        .delete_btn, .restore_btn, .downloadDoc, #confirmModal (.agreeWith
        data-recordid / data-status / data-applicant).
    --}}
    <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>

    @php
        $adpStatusId = (int) ($applicant->status_id ?? 0);
        $adpPriv = auth()->user()->priv();
        $adpNext = \App\Support\AdmissionStatusFlow::next($applicant, $allStatuses, $adpPriv);
        $adpStatusHelp = \App\Support\AdmissionStatusFlow::HELP;
        $adpTab = 'notes';

        $adpPaths = [
            'upload' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'list' => '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
            'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>',
            'print' => '<path d="M6 9V3h12v6"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v7H6z"/>',
            'export' => '<path d="M12 3v12"/><path d="m7 8 5-5 5 5"/><path d="M5 21h14"/>',
            'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
            'pencil' => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
            'download' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        ];
        $adpIcon = fn ($name, $size = 16) => '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$adpPaths[$name].'</svg>';
        $adpSpin = '<svg class="adm-btn-loader adp-btn-spin" style="display: none;" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" aria-hidden="true"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".4" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg>';
        $adpClose = '<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm adp-modal__close" data-tw-dismiss="modal" aria-label="Close">✕</button>';
        $adpCaret = '<span class="adp-acc-caret" aria-hidden="true">▾</span>';
        $adpAccept = '.jpeg,.jpg,.png,.gif,.txt,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx';
        // The design's small rich-text editor (bold / italic / underline / lists / clear).
        $adpEditor = fn ($id, $placeholder) => '<div class="adp-editor" data-adp-editor><div class="adp-ed-bar" role="toolbar" aria-label="Formatting">'
            .'<button type="button" data-cmd="bold" title="Bold" aria-label="Bold"><b>B</b></button>'
            .'<button type="button" data-cmd="italic" title="Italic" aria-label="Italic"><i>I</i></button>'
            .'<button type="button" data-cmd="underline" title="Underline" aria-label="Underline"><u>U</u></button>'
            .'<span class="adp-ed-sep"></span>'
            .'<button type="button" data-cmd="insertUnorderedList" title="Bulleted list" aria-label="Bulleted list">'.$adpIcon('list', 14).'</button>'
            .'<button type="button" data-cmd="insertOrderedList" title="Numbered list" aria-label="Numbered list">1.</button>'
            .'<span class="adp-ed-sep"></span>'
            .'<button type="button" data-cmd="removeFormat" title="Clear formatting" aria-label="Clear formatting">Tx</button>'
            .'</div><div id="'.$id.'" class="adp-ed-body" contenteditable="true" data-adp-ed-body data-placeholder="'.e($placeholder).'" aria-label="'.e($placeholder).'"></div></div>';
        $adpDeleted = (int) ($deletedCount ?? 0);
    @endphp

    @include('pages.students.admission.partials.portal-record')

    <section class="adp-panel adp-notes">
        <div class="adp-panel__head adp-panel__head--top">
            <div class="adp-proc__copy">
                <h3 class="adp-h3">Notes</h3>
                <p class="adp-proc__desc">Internal notes for the admissions team; the applicant never sees them.</p>
            </div>
            <div class="adp-tabletools__actions">
                <button type="button" id="tabulator-print-AN" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('print', 13) !!}Print</button>
                <button type="button" id="tabulator-export-csv-AN" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export CSV</button>
                <button type="button" id="tabulator-export-xlsx-AN" class="adp-btn adp-btn--ghost adp-btn--sm">{!! $adpIcon('export', 13) !!}Export XLSX</button>
            </div>
        </div>

        {{-- Composer: adds a note, or edits one (Edit on the timeline loads it here). --}}
        <form method="POST" action="#" id="addNoteForm" class="adp-note-composer" enctype="multipart/form-data" data-adp-mode="add">
            <div class="adp-note-editing" data-adp-editing hidden>
                {!! $adpIcon('pencil', 13) !!}<span>Editing the note by <b data-adp-editing-by></b> from <span data-adp-editing-when></span></span>
                <button type="button" class="adp-btn-link" data-adp-edit-cancel>Cancel editing</button>
            </div>
            {!! $adpEditor('addEditor', 'Add a note for the admissions team…') !!}
            <div class="adp-note-file" data-adp-edit-current hidden>
                <span class="adp-dz-fico">{!! $adpIcon('file', 14) !!}</span>
                <span class="adp-note-file__t"><b class="adp-trunc" data-adp-edit-current-name></b><small>Current attachment · a new file replaces it</small></span>
                <button type="button" class="adp-iconbtn adp-iconbtn--xs downloadDoc" data-id="0" data-adp-edit-current-download title="Download" aria-label="Download current attachment">{!! $adpIcon('download', 13) !!}</button>
            </div>
            <div class="adp-note-tools">
                <label for="addNoteDocument" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-attach-pick>{!! $adpIcon('upload', 13) !!}<span data-adp-attach-label>Attach a file</span></label>
                <span class="adp-tag" data-adp-attach-tag hidden>{!! $adpIcon('file', 12) !!}<span data-adp-attach-name></span><small class="adp-mono" data-adp-attach-size></small><button type="button" class="adp-tag-x" data-adp-attach-clear aria-label="Remove attachment">{!! $adpIcon('x', 12) !!}</button></span>
                <input type="file" id="addNoteDocument" name="document" class="adp-sr-only" accept="{{ $adpAccept }}">
                <span class="adp-note-hint">One file per note · up to 5 MB</span>
                <span class="adp-note-spacer"></span>
                <button type="button" class="adp-btn adp-btn--ghost adp-btn--sm" data-adp-edit-cancel hidden>Cancel</button>
                <button type="submit" id="saveNote" class="adp-btn adp-btn--primary adp-btn--sm" disabled><span data-adp-save-label>Add note</span> {!! $adpSpin !!}</button>
            </div>
            <div class="adp-err error-content" data-adp-note-error></div>
            <input type="hidden" name="applicant_id" value="{{ $applicant->id }}"/>
            <input type="hidden" name="id" value="0"/>
        </form>

        <div class="adp-tabletools adp-tabletools--notes">
            <form id="tabulatorFilterForm-AN" class="adp-tabletools__filters">
                <input id="query-AN" name="query" type="text" class="adp-ctl" placeholder="Search notes…" autocomplete="off">
                <button id="tabulator-html-filter-go-AN" type="button" class="adp-btn adp-btn--primary adp-btn--sm">{!! $adpIcon('search', 13) !!}Go</button>
                <button id="tabulator-html-filter-reset-AN" type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Reset</button>
            </form>
            <span class="adp-note-count" data-adp-note-count></span>
        </div>

        <div class="adp-arch-loading" data-adp-notes-loading><span class="adp-spin"></span>Loading notes…</div>
        <div class="adp-empty adp-empty--small" data-adp-notes-empty hidden><span>No notes yet.</span></div>
        <div class="adp-timeline" data-adp-timeline data-applicant="{{ $applicant->id }}"></div>
        <div class="adp-timeline__more" data-adp-notes-more hidden><button type="button" class="adp-btn adp-btn--ghost adp-btn--sm">Load more</button></div>

        {{-- Soft-deleted notes (admission.note.list with status 2); the rows load when opened. --}}
        <details class="adp-accordion adp-accordion--deleted" data-adp-deleted {{ $adpDeleted > 0 ? '' : 'hidden' }}>
            <summary><span>Deleted notes <span class="adp-cnt adp-cnt--muted" data-adp-deleted-count>{{ $adpDeleted }}</span></span>{!! $adpCaret !!}</summary>
            <div class="adp-acc-body">
                <div class="adp-arch-loading" data-adp-deleted-loading hidden><span class="adp-spin"></span>Loading deleted notes…</div>
                <div class="adp-vrows" data-adp-deleted-rows></div>
            </div>
        </details>
    </section>

    @include('pages.students.admission.partials.portal-record-modals')
    @include('pages.students.admission.partials.portal-dialogs', ['adpConfirmAttrs' => 'data-recordid="0" data-status="none" data-applicant="'.$applicant->id.'"'])
@endsection

@section('script')
    @vite('resources/js/admission-notes.js')
    {{-- The record header's Change status dialog (admission-show.js guards its other blocks). --}}
    @vite('resources/js/admission-show.js')
    @vite('resources/js/admission-vue.js')
@endsection
