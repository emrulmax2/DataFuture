import xlsx from "xlsx";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";
import axios from "axios";

/*
 * Uploaded files (pages/students/admission/uploads on the admission portal
 * shell). Same endpoints as before:
 *
 *  - admission.uploads.list        remote-paged list; status 1 = live, 2 = deleted
 *  - admission.upload.documents    Dropzone on #uploadDocumentForm (type, name, hard copy)
 *  - admission.destory.uploads / admission.resotore.uploads   via #confirmModal
 *  - admission.document.download   .downloadDoc
 *
 * Delete and restore refresh the lists and the counts in place; an upload
 * still reloads the page once its success dialog has shown.
 */
(function () {
    "use strict";

    const listEl = document.getElementById("applicantUploadListTable");
    if (!listEl) return;

    const csrf = { "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "" };
    const byId = (id) => document.getElementById(id);
    const esc = (v) => String(v === null || v === undefined ? "" : v)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    const applicantId = listEl.getAttribute("data-applicant") || (document.querySelector('input[name="applicant_id"]') || {}).value || "";
    const modalOf = (id) => {
        const el = byId(id);
        return el && window.tailwind && window.tailwind.Modal ? window.tailwind.Modal.getOrCreateInstance(el) : null;
    };
    const successModal = modalOf("successModal"), warningModal = modalOf("warningModal"), confirmModal = modalOf("confirmModal");
    const typeModal = modalOf("docTypeModal"), uploadModal = modalOf("uploadDocumentModal");

    const svg = (paths, size) => `<svg width="${size || 16}" height="${size || 16}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;
    const PATHS = {
        file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        user: '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/>',
        flag: '<path d="M4 22V4a1 1 0 0 1 1-1h11l-1 4 1 4H5"/>',
        sign: '<path d="m12 19 7-7 3 3-7 7-3-3z"/><path d="m18 13-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="m2 2 7.6 7.6"/><circle cx="11" cy="11" r="2"/>',
        mail: '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        list: '<path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/>',
        ok: '<circle cx="12" cy="12" r="9"/><path d="m8.5 12.5 2.5 2.5 4.5-5"/>',
        no: '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6M15 9l-6 6"/>',
        download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        trash: '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        undo: '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>',
        x: '<path d="M18 6 6 18M6 6l12 12"/>',
        alert: '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>',
    };
    const ICON = Object.fromEntries(Object.entries(PATHS).map(([k, v]) => [k, svg(v, 15)]));
    /* icon per document type, as the type chips and picker name them */
    const typeIcon = (name) => {
        const n = String(name || "").trim();
        const el = document.querySelector(`[data-adp-doc-name="${CSS.escape(n)}"]`);
        const key = el ? el.getAttribute("data-adp-doc-icon") : (n === "" ? "list" : "file");
        return svg(PATHS[key] || PATHS.file, 14);
    };
    const busy = (btn, on) => {
        if (!btn) return;
        btn.toggleAttribute("disabled", on);
        btn.querySelectorAll(".adm-btn-loader").forEach((l) => { l.style.display = on ? "inline-block" : "none"; });
    };
    const setText = (root, sel, text) => { const el = root ? root.querySelector(sel) : null; if (el) el.textContent = text; };
    const inlineError = (el, text) => { if (!el) return; el.textContent = text || ""; el.hidden = !text; };
    const RELOAD_AFTER = 1600;

    /* ---------- outcome dialogs -------------------------------------------- */
    const successEl = byId("successModal"), warningEl = byId("warningModal");
    const showSuccess = (title, text, reload) => {
        setText(successEl, ".successModalTitle", title);
        setText(successEl, ".successModalDesc", text || "");
        const closer = successEl && successEl.querySelector(".successCloser");
        if (closer) closer.setAttribute("data-action", reload ? "RELOAD" : "DISMISS");
        if (successModal) successModal.show();
        if (reload) window.setTimeout(() => window.location.reload(), RELOAD_AFTER);
        else window.setTimeout(() => { if (successModal) successModal.hide(); }, 2200);
    };
    const showWarning = (title, text, reload) => {
        setText(warningEl, ".warningModalTitle", title);
        setText(warningEl, ".warningModalDesc", text || "");
        const closer = warningEl && warningEl.querySelector(".warningCloser");
        if (closer) closer.setAttribute("data-action", reload ? "RELOAD" : "NONE");
        if (warningModal) warningModal.show();
        if (reload) window.setTimeout(() => window.location.reload(), RELOAD_AFTER);
    };
    if (successEl) successEl.addEventListener("click", (e) => {
        const closer = e.target.closest(".successCloser");
        if (!closer) return;
        e.preventDefault();
        if (closer.getAttribute("data-action") === "RELOAD") window.location.reload(); else if (successModal) successModal.hide();
    });
    if (warningEl) warningEl.addEventListener("click", (e) => {
        const closer = e.target.closest(".warningCloser");
        if (!closer) return;
        e.preventDefault();
        if (closer.getAttribute("data-action") === "RELOAD") window.location.reload(); else if (warningModal) warningModal.hide();
    });

    /* ---------- counts (chips, picker cards, deleted accordion) ------------- */
    const deletedBox = document.querySelector("[data-adp-deleted]");
    const deletedCountEl = document.querySelector("[data-adp-deleted-count]");
    let deletedCount = Number(deletedCountEl ? deletedCountEl.textContent : 0) || 0;
    const paintDeleted = () => {
        if (deletedCountEl) deletedCountEl.textContent = String(deletedCount);
        if (deletedBox) { deletedBox.hidden = deletedCount === 0; if (deletedCount === 0) deletedBox.open = false; }
    };
    /* +1 / -1 on a type's chip and picker card; task documents (no type) use the static chip */
    const bumpType = (settingId, delta) => {
        const id = String(settingId || "");
        if (id === "" || id === "0") {
            const chip = document.querySelector("[data-adp-task-chip]"), n = chip && chip.querySelector("[data-adp-task-count]");
            if (!chip || !n) return;
            const v = Math.max(0, (Number(n.textContent) || 0) + delta);
            n.textContent = String(v);
            chip.hidden = v === 0;
            chip.classList.toggle("has", v > 0);
            return;
        }
        const chip = document.querySelector(`.adp-doc-chip[data-adp-doc-type="${id}"]`);
        if (chip) {
            const n = chip.querySelector("[data-adp-chip-count]");
            const v = Math.max(0, (Number(n ? n.textContent : 0) || 0) + delta);
            if (n) { n.textContent = String(v); n.hidden = v === 0; }
            chip.classList.toggle("has", v > 0);
            const ico = chip.querySelector("[data-adp-chip-ico]");
            if (ico) ico.innerHTML = svg(v > 0 ? PATHS.ok : (PATHS[chip.getAttribute("data-adp-doc-icon")] || PATHS.file), 14);
            chip.setAttribute("title", v > 0 ? `${v} uploaded — add another` : "Not uploaded yet — click to upload");
        }
        const card = document.querySelector(`.adp-doc-card[data-adp-doc-type="${id}"]`);
        if (card) {
            const state = card.querySelector("[data-adp-doc-state]");
            const current = state ? (Number((state.textContent.match(/\d+/) || [0])[0]) || 0) : 0;
            const v = Math.max(0, current + delta);
            card.classList.toggle("has", v > 0);
            if (state) { state.classList.toggle("ok", v > 0); state.innerHTML = v > 0 ? `${svg(PATHS.ok, 13)}${v} on file` : "Not uploaded"; }
        }
    };

    /* ---------- tables ------------------------------------------------------ */
    const params = (status) => ({ applicantId, queryStr: ((byId("query-UP") || {}).value || "").trim(), status });
    const tableOpts = (extra) => ({
        ajaxURL: route("admission.uploads.list"),
        ajaxFiltering: true,
        ajaxSorting: true,
        printAsHtml: true,
        printStyled: true,
        pagination: "remote",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, true],
        langs: { default: { pagination: { page_size: "Rows", all: "All", prev: "Previous", next: "Next", prev_title: "Previous page", next_title: "Next page" } } },
        layout: "fitColumns",
        ...extra,
    });
    const typeOf = (d) => d.document_type || "Task document";
    const mainColumns = [
        { title: "Type", field: "document_type", width: 170, headerSort: false, formatter: (c) => {
            const name = typeOf(c.getData());
            return `<span class="adp-doc-type-cell" title="${esc(name)}">${typeIcon(c.getData().document_type)}${esc(name)}</span>`;
        } },
        { title: "Document name", field: "display_file_name", minWidth: 200, widthGrow: 2, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => esc(c.getValue() || "—") },
        { title: "File", field: "current_file_name", minWidth: 170, widthGrow: 1, headerSort: false, formatter: (c) => `<span class="adp-mono" style="font-size:12.5px" title="${esc(c.getValue() || "")}">${esc(c.getValue() || "—")}</span>` },
        { title: "Uploaded by", field: "created_by", width: 130, headerSort: false, formatter: (c) => `<span title="${esc(c.getValue() || "")}">${esc(c.getValue() || "—")}</span>` },
        { title: "When", field: "created_at", width: 170, formatter: (c) => `<span class="adp-mono">${esc(c.getValue() || "—")}</span>` },
        { title: "Hard copy", field: "hard_copy_check", width: 130, headerSort: false, formatter: (c) => (Number(c.getValue()) === 1 ? `<span class="adp-chip on">${ICON.ok}Checked</span>` : `<span class="adp-chip">${ICON.no}Not checked</span>`) },
        { title: "", field: "id", width: 150, headerSort: false, hozAlign: "right", cssClass: "adp-cell--right", download: false, formatter: (c) => {
            const d = c.getData();
            return `<span class="adp-row-links"><button type="button" class="adp-btn-link downloadDoc" data-id="${esc(d.id)}">Download</button><span class="adp-row-links__dot">·</span><button type="button" class="adp-btn-link adp-btn-link--danger delete_btn" data-id="${esc(d.id)}" data-adp-type="${esc(typeOf(d))}" data-adp-file="${esc(d.current_file_name || "")}" data-adp-setting="${esc(d.document_setting_id || 0)}">Delete</button></span>`;
        } },
    ];
    const deletedColumns = [
        { title: "File", field: "display_file_name", minWidth: 260, widthGrow: 2, headerSort: false, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => {
            const d = c.getData();
            return `<div class="adp-cell"><b>${esc(d.display_file_name || "—")}</b><span class="adp-sub-line">${esc(typeOf(d))} · <span class="adp-mono">${esc(d.current_file_name || "")}</span></span></div>`;
        } },
        { title: "Deleted", field: "deleted_at", width: 170, headerSort: false, formatter: (c) => `<span class="adp-mono">${esc(c.getValue() || "—")}</span>` },
        { title: "", field: "id", width: 150, headerSort: false, hozAlign: "right", cssClass: "adp-cell--right", formatter: (c) => {
            const d = c.getData();
            return `<span class="adp-row-links"><button type="button" class="adp-btn-link downloadDoc" data-id="${esc(d.id)}">Download</button><span class="adp-row-links__dot">·</span><button type="button" class="adp-btn-link restore_btn" data-id="${esc(d.id)}" data-adp-type="${esc(typeOf(d))}" data-adp-file="${esc(d.current_file_name || "")}" data-adp-setting="${esc(d.document_setting_id || 0)}">Restore</button></span>`;
        } },
    ];

    const mainTable = new Tabulator(listEl, tableOpts({
        ajaxParams: { ...params(1) },
        placeholder: "<b>Nothing uploaded.</b><span>Use <b>Add document</b>, choose the type, then drop the files in.</span>",
        columns: mainColumns,
    }));
    let deletedTable = null;
    const deletedEl = byId("applicantDeletedListTable");
    const buildDeleted = () => {
        if (deletedTable || !deletedEl) return;
        deletedTable = new Tabulator(deletedEl, tableOpts({
            ajaxParams: { ...params(2) },
            placeholder: "<b>Nothing deleted.</b><span>Files you delete from the list above appear here and can be restored.</span>",
            columns: deletedColumns,
        }));
    };
    if (deletedBox) deletedBox.addEventListener("toggle", () => {
        if (!deletedBox.open) return;
        if (!deletedTable) buildDeleted(); else deletedTable.redraw(true);
    });
    const refresh = () => {
        mainTable.setData(route("admission.uploads.list"), { ...params(1) });
        if (deletedTable) deletedTable.setData(route("admission.uploads.list"), { ...params(2) });
    };
    window.addEventListener("resize", () => { mainTable.redraw(); if (deletedTable) deletedTable.redraw(); });

    /* search / reset / print / export */
    const goBtn = byId("tabulator-html-filter-go-UP"), resetBtn = byId("tabulator-html-filter-reset-UP"), form = byId("tabulatorFilterForm-UP");
    if (goBtn) goBtn.addEventListener("click", (e) => { e.preventDefault(); refresh(); });
    if (form) form.addEventListener("submit", (e) => { e.preventDefault(); refresh(); });
    if (resetBtn) resetBtn.addEventListener("click", (e) => { e.preventDefault(); const q = byId("query-UP"); if (q) q.value = ""; refresh(); });
    const printBtn = byId("tabulator-print-UP");
    if (printBtn) printBtn.addEventListener("click", (e) => { e.preventDefault(); mainTable.print(); });
    const csvBtn = byId("tabulator-export-csv-UP");
    if (csvBtn) csvBtn.addEventListener("click", (e) => { e.preventDefault(); mainTable.download("csv", "applicant_documents.csv"); });
    const xlsxBtn = byId("tabulator-export-xlsx-UP");
    if (xlsxBtn) xlsxBtn.addEventListener("click", (e) => { e.preventDefault(); window.XLSX = xlsx; mainTable.download("xlsx", "applicant_documents.xlsx", { sheetName: "Admission Uploads" }); });

    /* ---------- confirm dialog (delete / restore) --------------------------- */
    const confirmEl = byId("confirmModal");
    let pending = null;
    const confirmAction = ({ tone, icon, title, text, item, action, recordId, label, meta }) => {
        if (!confirmEl || !confirmModal) return;
        const content = confirmEl.querySelector(".modal-content");
        if (content) content.className = `modal-content adp-dialog adp-dialog--${tone}`;
        const ico = confirmEl.querySelector(".adp-dialog__ico");
        if (ico) ico.innerHTML = svg(icon, 30);
        setText(confirmEl, ".confModTitle", title);
        setText(confirmEl, ".confModDesc", text || "");
        const itemEl = confirmEl.querySelector("[data-adp-confirm-item]");
        if (itemEl) { itemEl.textContent = item || ""; itemEl.hidden = !item; }
        const agree = confirmEl.querySelector(".agreeWith");
        if (agree) {
            agree.setAttribute("data-recordid", recordId);
            agree.setAttribute("data-status", action);
            agree.className = `agreeWith adp-btn ${tone === "danger" ? "adp-btn--danger" : "adp-btn--primary"}`;
            agree.textContent = label || "Yes, I agree";
            agree.removeAttribute("disabled");
        }
        pending = meta || null;
        confirmModal.show();
    };
    document.addEventListener("click", (e) => {
        const del = e.target.closest(".delete_btn");
        if (del) {
            e.preventDefault();
            confirmAction({ tone: "danger", icon: PATHS.trash, title: "Delete this file?", item: `${del.getAttribute("data-adp-type")} · ${del.getAttribute("data-adp-file")}`, text: "Soft-deleted; you can restore it from the deleted list.", action: "DELETEDOC", recordId: del.getAttribute("data-id"), label: "Delete", meta: { setting: del.getAttribute("data-adp-setting") } });
            return;
        }
        const res = e.target.closest(".restore_btn");
        if (res) {
            e.preventDefault();
            confirmAction({ tone: "warn", icon: PATHS.undo, title: "Restore this file?", item: `${res.getAttribute("data-adp-type")} · ${res.getAttribute("data-adp-file")}`, text: "It goes back to the uploaded files list.", action: "RESTOREDOC", recordId: res.getAttribute("data-id"), label: "Restore", meta: { setting: res.getAttribute("data-adp-setting") } });
        }
    });
    if (confirmEl) {
        confirmEl.addEventListener("click", (e) => {
            const agree = e.target.closest(".agreeWith");
            if (!agree) return;
            e.preventDefault();
            const action = agree.getAttribute("data-status");
            const recordid = agree.getAttribute("data-recordid");
            const applicant = agree.getAttribute("data-applicant") || applicantId;
            const meta = pending || {};
            const spec = action === "DELETEDOC" ? { method: "delete", name: "admission.destory.uploads" } : (action === "RESTOREDOC" ? { method: "post", name: "admission.resotore.uploads" } : null);
            if (!spec) { confirmModal.hide(); return; }
            confirmEl.querySelectorAll("button").forEach((b) => b.setAttribute("disabled", "disabled"));
            axios({ method: spec.method, url: route(spec.name), data: { applicant, recordid }, headers: csrf })
                .then(() => {
                    confirmModal.hide();
                    if (action === "DELETEDOC") { deletedCount += 1; bumpType(meta.setting, -1); } else { deletedCount = Math.max(0, deletedCount - 1); bumpType(meta.setting, 1); }
                    paintDeleted();
                    refresh();
                    showSuccess(action === "DELETEDOC" ? "File deleted" : "File restored", action === "DELETEDOC" ? "It is in the deleted list and can be restored." : "It is back in the uploaded files list.", false);
                })
                .catch(() => {
                    confirmEl.querySelectorAll("button").forEach((b) => b.removeAttribute("disabled"));
                    confirmModal.hide();
                    showWarning("Something went wrong", "The change could not be saved. Try again, or contact the administrator.", false);
                });
        });
        confirmEl.addEventListener("hide.tw.modal", () => {
            setText(confirmEl, ".confModDesc", "");
            const agree = confirmEl.querySelector(".agreeWith");
            if (agree) { agree.setAttribute("data-recordid", "0"); agree.setAttribute("data-status", "none"); }
            confirmEl.querySelectorAll("button").forEach((b) => b.removeAttribute("disabled"));
            pending = null;
        });
    }

    /* ---------- downloads --------------------------------------------------- */
    document.addEventListener("click", (e) => {
        const link = e.target.closest(".downloadDoc");
        if (!link) return;
        e.preventDefault();
        if (link.hasAttribute("disabled")) return;
        link.setAttribute("disabled", "disabled");
        axios.post(route("admission.document.download"), { row_id: link.getAttribute("data-id") }, { headers: csrf })
            .then((res) => { link.removeAttribute("disabled"); const url = res.data && res.data.res; if (url) window.open(url, "_blank"); })
            .catch(() => { link.removeAttribute("disabled"); });
    });

    /* ---------- upload: type, then files ------------------------------------ */
    const uploadEl = byId("uploadDocumentModal");
    const dzForm = byId("uploadDocumentForm");
    const nameInput = uploadEl && uploadEl.querySelector(".displayNameInput");
    const namePreview = byId("documentNameDisplay");
    let picked = { id: "0", name: "", hint: "", icon: "file" };
    const uploadError = (text) => inlineError(uploadEl && uploadEl.querySelector("[data-adp-upload-error]"), text);
    const paintName = () => {
        if (!namePreview) return;
        const typed = nameInput ? nameInput.value.trim() : "";
        namePreview.textContent = picked.name + (typed ? ` - ${typed}` : "");
        const hidden = uploadEl.querySelector('input[name="display_file_name"]');
        if (hidden) hidden.value = typed;
    };
    const pickType = (el) => {
        picked = { id: el.getAttribute("data-adp-doc-type") || "0", name: el.getAttribute("data-adp-doc-name") || "", hint: el.getAttribute("data-adp-doc-hint") || "", icon: el.getAttribute("data-adp-doc-icon") || "file" };
        if (!uploadEl) return;
        const setting = uploadEl.querySelector('input[name="document_setting_id"]');
        if (setting) setting.value = picked.id;
        setText(uploadEl, "[data-adp-upload-type]", picked.name);
        setText(uploadEl, "[data-adp-upload-hint]", picked.hint || "Choose the files, name them if you like, and say whether the hard copy was checked.");
        const ico = uploadEl.querySelector("[data-adp-upload-ico]");
        if (ico) ico.innerHTML = svg(PATHS[picked.icon] || PATHS.file, 18);
        paintName();
        if (typeModal) typeModal.hide();
        window.setTimeout(() => { if (uploadModal) uploadModal.show(); }, typeModal ? 220 : 0);
    };
    document.addEventListener("click", (e) => {
        const card = e.target.closest(".adp-doc-card[data-adp-doc-type], .adp-doc-chip[data-adp-doc-type]");
        if (!card) return;
        e.preventDefault();
        pickType(card);
    });
    if (uploadEl) {
        const change = uploadEl.querySelector("[data-adp-change-type]");
        if (change) change.addEventListener("click", (e) => {
            e.preventDefault();
            if (uploadModal) uploadModal.hide();
            window.setTimeout(() => { if (typeModal) typeModal.show(); }, 220);
        });
        if (nameInput) nameInput.addEventListener("input", paintName);
    }

    let dz = null, dzError = false, uploading = false;
    if (dzForm && uploadEl) {
        Dropzone.autoDiscover = false;
        dz = new Dropzone("#uploadDocumentForm", {
            url: dzForm.getAttribute("action"),
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 5,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: false,
            previewsContainer: "[data-adp-dz-list]",
            previewTemplate: `<li class="dz-preview"><span class="adp-dz-fico">${ICON.file}</span><span class="adp-dz-name" data-dz-name></span><span class="adp-dz-size" data-dz-size></span><small class="adp-dz-err" data-dz-errormessage></small><button type="button" class="adp-iconbtn adp-iconbtn--xs" data-dz-remove aria-label="Remove file">${svg(PATHS.x, 14)}</button></li>`,
            headers: csrf,
            accept: (file, done) => { done(); },
        });
        const label = uploadEl.querySelector("[data-adp-upload-label]");
        const paintLabel = () => { if (label) { const n = dz.getAcceptedFiles().length; label.textContent = n > 1 ? `Upload ${n} files` : "Upload"; } };
        dz.on("addedfile", (file) => {
            uploadError("");
            if (file.name.match(/[`!@#$%^&*+\=\[\]{};':"\\|,<>\/?~]/)) {
                uploadError("One file name contains characters that are not allowed, so that file was removed.");
                dz.removeFile(file);
                window.setTimeout(() => uploadError(""), 5000);
            }
            window.setTimeout(paintLabel, 0);
        });
        dz.on("removedfile", paintLabel);
        dz.on("maxfilesexceeded", (file) => {
            uploadError("Only 10 files per upload.");
            dz.removeFile(file);
            window.setTimeout(() => uploadError(""), 2500);
        });
        dz.on("error", () => { if (uploading) dzError = true; });
        dz.on("success", (file) => { if (file.previewElement) file.previewElement.classList.add("dz-success"); });
        dz.on("queuecomplete", () => {
            if (!uploading) return;
            uploading = false;
            busy(byId("uploadDocBtn"), false);
            if (uploadModal) uploadModal.hide();
            if (!dzError) showSuccess("Documents uploaded", `Filed under ${picked.name || "the chosen type"}.`, true);
            else showWarning("Upload failed", "Some files could not be uploaded. Try again, or contact the administrator.", false);
        });
        const uploadBtn = byId("uploadDocBtn");
        if (uploadBtn) uploadBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (!dz.getAcceptedFiles().length) { uploadError("Add at least one file."); return; }
            const setting = uploadEl.querySelector('input[name="document_setting_id"]');
            if (!setting || !(Number(setting.value) > 0)) { uploadError("Choose the document type first."); return; }
            const hard = uploadEl.querySelector('input[name="hard_copy_check_status"]'), hardHidden = uploadEl.querySelector('input[name="hard_copy_check"]');
            if (hardHidden) hardHidden.value = hard && hard.checked ? "1" : "0";
            paintName();
            dzError = false;
            uploading = true;
            busy(uploadBtn, true);
            dz.processQueue();
        });
        uploadEl.addEventListener("hide.tw.modal", () => {
            ["document_setting_id", "hard_copy_check", "display_file_name"].forEach((n) => { const el = uploadEl.querySelector(`input[name="${n}"]`); if (el) el.value = n === "display_file_name" ? "" : "0"; });
            if (nameInput) nameInput.value = "";
            if (namePreview) namePreview.textContent = "";
            const hard = uploadEl.querySelector('input[name="hard_copy_check_status"]');
            if (hard) hard.checked = false;
            uploadError("");
            busy(byId("uploadDocBtn"), false);
            uploading = false;
            dz.removeAllFiles(true);
            paintLabel();
        });
    }
})();
