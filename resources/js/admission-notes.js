import xlsx from "xlsx";
import axios from "axios";

/*
 * Notes (pages/students/admission/notes on the admission portal shell).
 * Same endpoints as before:
 *
 *  - admission.note.list           paged list; status 1 = live, 2 = deleted (renders the timeline)
 *  - admission.store.note          composer (#addNoteForm: content + one attachment)
 *  - admission.get.note / admission.update.note   editing, in the same composer
 *  - admission.destory.note / admission.resotore.note   via #confirmModal
 *  - admission.document.download   .downloadDoc
 *
 * Print prints the timeline; Export CSV / XLSX write every live note out.
 */
(function () {
    "use strict";

    const timeline = document.querySelector("[data-adp-timeline]");
    if (!timeline) return;

    const csrf = { "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "" };
    const byId = (id) => document.getElementById(id);
    const esc = (v) => String(v === null || v === undefined ? "" : v)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    const applicantId = timeline.getAttribute("data-applicant") || (document.querySelector('input[name="applicant_id"]') || {}).value || "";
    const modalOf = (id) => {
        const el = byId(id);
        return el && window.tailwind && window.tailwind.Modal ? window.tailwind.Modal.getOrCreateInstance(el) : null;
    };
    const successModal = modalOf("successModal"), warningModal = modalOf("warningModal"), confirmModal = modalOf("confirmModal"), editModal = modalOf("editNoteModal");
    const svg = (paths, size) => `<svg width="${size || 16}" height="${size || 16}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;
    const PATHS = {
        file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        upload: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
        trash: '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        undo: '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>',
    };
    const MAX_BYTES = 5 * 1024 * 1024;
    const fmtSize = (n) => (n > 1024 * 1024 ? (n / 1024 / 1024).toFixed(1) + " MB" : Math.max(1, Math.round(n / 1024)) + " KB");
    const busy = (btn, on) => {
        if (!btn) return;
        btn.toggleAttribute("disabled", on);
        btn.querySelectorAll(".adm-btn-loader").forEach((l) => { l.style.display = on ? "inline-block" : "none"; });
    };
    const setText = (root, sel, text) => { const el = root ? root.querySelector(sel) : null; if (el) el.textContent = text; };
    const inlineError = (el, text) => { if (!el) return; el.textContent = text || ""; el.hidden = !text; };

    /* ---------- outcome dialogs -------------------------------------------- */
    const successEl = byId("successModal"), warningEl = byId("warningModal");
    const showSuccess = (title, text) => {
        setText(successEl, ".successModalTitle", title);
        setText(successEl, ".successModalDesc", text || "");
        const closer = successEl && successEl.querySelector(".successCloser");
        if (closer) closer.setAttribute("data-action", "DISMISS");
        if (successModal) successModal.show();
        window.setTimeout(() => { if (successModal) successModal.hide(); }, 2200);
    };
    const showWarning = (title, text) => {
        setText(warningEl, ".warningModalTitle", title);
        setText(warningEl, ".warningModalDesc", text || "");
        const closer = warningEl && warningEl.querySelector(".warningCloser");
        if (closer) closer.setAttribute("data-action", "NONE");
        if (warningModal) warningModal.show();
    };
    [successEl, warningEl].forEach((el) => { if (el) el.addEventListener("click", (e) => {
        if (!e.target.closest(".successCloser, .warningCloser")) return;
        e.preventDefault();
        const m = el === successEl ? successModal : warningModal;
        if (m) m.hide();
    }); });

    /* ---------- the design's small editor ----------------------------------- */
    const makeEditor = (root) => {
        const body = root.querySelector("[data-adp-ed-body]");
        const bar = root.querySelector(".adp-ed-bar");
        const api = {
            root, body,
            html: () => body.innerHTML,
            text: () => body.textContent.replace(/ /g, " ").trim(),
            isEmpty: () => api.text() === "" && !body.querySelector("img"),
            set: (html) => { body.innerHTML = html || ""; },
            clear: () => { body.innerHTML = ""; },
            focus: () => body.focus(),
            onChange: (fn) => body.addEventListener("input", fn),
        };
        if (bar) bar.addEventListener("mousedown", (e) => {
            const btn = e.target.closest("[data-cmd]");
            if (!btn) return;
            e.preventDefault();
            body.focus();
            document.execCommand(btn.getAttribute("data-cmd"), false, null);
            body.dispatchEvent(new Event("input", { bubbles: true }));
        });
        /* paste as plain text so pasted formatting cannot break the layout */
        body.addEventListener("paste", (e) => {
            if (!e.clipboardData) return;
            e.preventDefault();
            document.execCommand("insertText", false, e.clipboardData.getData("text/plain"));
        });
        return api;
    };
    const editors = Array.from(document.querySelectorAll("[data-adp-editor]")).map(makeEditor);
    const composerEditor = editors.find((ed) => ed.body.id === "addEditor");

    /* ---------- single-attachment control (composer + edit dialog) ---------- */
    const attachControl = (scope, onError) => {
        const input = scope.querySelector('input[type="file"][name="document"]');
        const tag = scope.querySelector("[data-adp-attach-tag]");
        const pick = scope.querySelector("[data-adp-attach-pick]");
        if (!input || !tag) return { clear: () => {}, file: () => null };
        const paint = () => {
            const f = input.files && input.files[0];
            tag.hidden = !f;
            if (pick) pick.hidden = !!f;
            if (f) { setText(tag, "[data-adp-attach-name]", f.name); setText(tag, "[data-adp-attach-size]", fmtSize(f.size)); }
        };
        input.addEventListener("change", () => {
            const f = input.files && input.files[0];
            if (f && f.size > MAX_BYTES) { input.value = ""; if (onError) onError(`${f.name} is over 5 MB.`); }
            else if (onError) onError("");
            paint();
        });
        const clearBtn = tag.querySelector("[data-adp-attach-clear]");
        if (clearBtn) clearBtn.addEventListener("click", () => { input.value = ""; paint(); if (onError) onError(""); });
        return { clear: () => { input.value = ""; paint(); }, file: () => (input.files && input.files[0]) || null };
    };

    /* ---------- timeline ---------------------------------------------------- */
    const state = { page: 1, lastPage: 1, total: 0, query: "", rows: [] };
    const loadingEl = document.querySelector("[data-adp-notes-loading]");
    const emptyEl = document.querySelector("[data-adp-notes-empty]");
    const moreEl = document.querySelector("[data-adp-notes-more]");
    const countEl = document.querySelector("[data-adp-note-count]");
    const rowHtml = (n) => {
        const hasFile = Number(n.applicant_document_id) > 0;
        const when = n.created_at + (n.updated_at ? ` · edited ${n.updated_at}` : "");
        return `<div class="adp-tl" data-adp-note="${esc(n.id)}"><span class="adp-tl__dot"></span><div class="adp-tl__body">
            <div class="adp-tl__h"><b>${esc(n.created_by || "Unknown")}</b><span class="adp-mono">${esc(when)}</span><span class="adp-tl__links"><button type="button" class="adp-btn-link edit_btn" data-id="${esc(n.id)}">Edit</button><button type="button" class="adp-btn-link adp-btn-link--danger delete_btn" data-id="${esc(n.id)}" data-adp-excerpt="${esc(n.excerpt || n.note || "")}">Delete</button></span></div>
            <div class="adp-tl__text">${n.note_html || esc(n.note || "")}</div>
            ${hasFile
                ? `<div class="adp-note-file"><span class="adp-dz-fico">${svg(PATHS.file, 14)}</span><span class="adp-note-file__t"><b class="adp-trunc">${esc(n.document_name || "Attachment")}</b><small>Attachment</small></span><button type="button" class="adp-iconbtn adp-iconbtn--xs downloadDoc" data-id="${esc(n.applicant_document_id)}" title="Download" aria-label="Download attachment">${svg(PATHS.download, 13)}</button></div>`
                : `<button type="button" class="adp-btn-link adp-note-attach-later edit_btn" data-id="${esc(n.id)}" data-adp-focus-file="1">${svg(PATHS.upload, 12)}Attach a file</button>`}
        </div></div>`;
    };
    const paintCount = () => {
        if (!countEl) return;
        countEl.textContent = state.total ? `${state.total} note${state.total === 1 ? "" : "s"}${state.query ? ` matching “${state.query}”` : ""}` : "";
    };
    const fetchNotes = (page, status, size) => axios.get(route("admission.note.list"), { params: { applicantId, queryStr: state.query, status, page, size: size || 10 }, headers: csrf }).then((r) => r.data || {});
    const loadNotes = (reset) => {
        if (reset) { state.page = 1; state.rows = []; timeline.innerHTML = ""; }
        if (loadingEl) loadingEl.hidden = false;
        if (emptyEl) emptyEl.hidden = true;
        if (moreEl) moreEl.hidden = true;
        return fetchNotes(state.page, 1).then((res) => {
            const rows = Array.isArray(res.data) ? res.data : [];
            state.lastPage = Number(res.last_page) || 1;
            state.total = Number(res.total_rows) || rows.length;
            state.rows = state.rows.concat(rows);
            timeline.insertAdjacentHTML("beforeend", rows.map(rowHtml).join(""));
            if (loadingEl) loadingEl.hidden = true;
            if (emptyEl) emptyEl.hidden = state.rows.length > 0;
            if (moreEl) moreEl.hidden = !(state.page < state.lastPage);
            paintCount();
        }).catch(() => {
            if (loadingEl) loadingEl.hidden = true;
            showWarning("Could not load the notes", "Reload the page and try again.");
        });
    };
    if (moreEl) moreEl.addEventListener("click", (e) => { if (!e.target.closest("button")) return; state.page += 1; loadNotes(false); });

    /* search */
    const queryInput = byId("query-AN");
    const search = () => { state.query = queryInput ? queryInput.value.trim() : ""; loadNotes(true); };
    const goBtn = byId("tabulator-html-filter-go-AN"), resetBtn = byId("tabulator-html-filter-reset-AN"), filterForm = byId("tabulatorFilterForm-AN");
    if (goBtn) goBtn.addEventListener("click", (e) => { e.preventDefault(); search(); });
    if (filterForm) filterForm.addEventListener("submit", (e) => { e.preventDefault(); search(); });
    if (resetBtn) resetBtn.addEventListener("click", (e) => { e.preventDefault(); if (queryInput) queryInput.value = ""; search(); });

    /* ---------- deleted notes ------------------------------------------------ */
    const deletedBox = document.querySelector("[data-adp-deleted]");
    const deletedCountEl = document.querySelector("[data-adp-deleted-count]");
    const deletedRows = document.querySelector("[data-adp-deleted-rows]");
    const deletedLoading = document.querySelector("[data-adp-deleted-loading]");
    let deletedCount = Number(deletedCountEl ? deletedCountEl.textContent : 0) || 0, deletedLoaded = false;
    const paintDeleted = () => {
        if (deletedCountEl) deletedCountEl.textContent = String(deletedCount);
        if (deletedBox) { deletedBox.hidden = deletedCount === 0; if (deletedCount === 0) deletedBox.open = false; }
    };
    const loadDeleted = () => {
        if (!deletedRows) return Promise.resolve();
        if (deletedLoading) deletedLoading.hidden = false;
        return fetchNotes(1, 2, true).then((res) => {
            const rows = Array.isArray(res.data) ? res.data : [];
            deletedCount = Number(res.total_rows) || rows.length;
            deletedLoaded = true;
            deletedRows.innerHTML = rows.map((n) => `<div class="adp-vrow adp-vrow--note"><span><span class="adp-vrow__text" title="${esc(n.excerpt || n.note || "")}">${esc(n.excerpt || n.note || "—")}</span><span class="adp-vrow__meta">${esc(n.created_by || "")} · deleted ${esc(n.deleted_at || "")}</span></span><button type="button" class="adp-btn adp-btn--ghost adp-btn--sm restore_btn" data-id="${esc(n.id)}" data-adp-excerpt="${esc(n.excerpt || n.note || "")}">${svg(PATHS.undo, 13)}Restore</button></div>`).join("");
            if (deletedLoading) deletedLoading.hidden = true;
            paintDeleted();
        }).catch(() => { if (deletedLoading) deletedLoading.hidden = true; });
    };
    if (deletedBox) deletedBox.addEventListener("toggle", () => { if (deletedBox.open && !deletedLoaded) loadDeleted(); });

    /* ---------- composer: add, or edit a note loaded into it ----------------- */
    const addForm = byId("addNoteForm");
    const editing = { id: 0 };
    if (addForm && composerEditor) {
        const errEl = addForm.querySelector("[data-adp-note-error]");
        const saveBtn = byId("saveNote");
        const saveLabel = addForm.querySelector("[data-adp-save-label]");
        const banner = addForm.querySelector("[data-adp-editing]");
        const current = addForm.querySelector("[data-adp-edit-current]");
        const cancelBtns = Array.from(addForm.querySelectorAll("[data-adp-edit-cancel]"));
        const idInput = addForm.querySelector('input[name="id"]');
        const attach = attachControl(addForm, (t) => inlineError(errEl, t));
        const paintSave = () => { if (saveBtn) saveBtn.toggleAttribute("disabled", composerEditor.isEmpty()); };
        composerEditor.onChange(() => { inlineError(errEl, ""); paintSave(); });
        const markRow = () => document.querySelectorAll(".adp-tl").forEach((row) => row.classList.toggle("adp-tl--editing", editing.id > 0 && row.getAttribute("data-adp-note") === String(editing.id)));
        const toAddMode = () => {
            editing.id = 0;
            addForm.setAttribute("data-adp-mode", "add");
            if (idInput) idInput.value = "0";
            composerEditor.clear();
            attach.clear();
            if (banner) banner.hidden = true;
            if (current) current.hidden = true;
            setText(addForm, "[data-adp-attach-label]", "Attach a file");
            if (saveLabel) saveLabel.textContent = "Add note";
            cancelBtns.forEach((b) => { b.hidden = true; });
            inlineError(errEl, "");
            paintSave();
            markRow();
        };
        const toEditMode = (id, focusFile) => {
            const row = state.rows.find((n) => String(n.id) === String(id)) || {};
            editing.id = Number(id) || 0;
            addForm.setAttribute("data-adp-mode", "edit");
            if (idInput) idInput.value = String(id);
            composerEditor.set(row.note_html || "");
            attach.clear();
            if (banner) { banner.hidden = false; setText(banner, "[data-adp-editing-by]", row.created_by || "—"); setText(banner, "[data-adp-editing-when]", row.created_at || "—"); }
            const hasFile = Number(row.applicant_document_id) > 0;
            if (current) {
                current.hidden = !hasFile;
                setText(current, "[data-adp-edit-current-name]", row.document_name || "Attachment");
                const dl = current.querySelector("[data-adp-edit-current-download]");
                if (dl) dl.setAttribute("data-id", row.applicant_document_id || 0);
            }
            setText(addForm, "[data-adp-attach-label]", hasFile ? "Replace attachment" : "Attach a file");
            if (saveLabel) saveLabel.textContent = "Save changes";
            cancelBtns.forEach((b) => { b.hidden = false; });
            inlineError(errEl, "");
            paintSave();
            markRow();
            addForm.scrollIntoView({ behavior: "smooth", block: "start" });
            /* the latest copy from the server, in case the timeline is stale */
            axios.post(route("admission.get.note"), { noteId: id }, { headers: csrf })
                .then((res) => {
                    const d = (res.data && res.data.res) || {};
                    if (editing.id !== Number(id)) return;
                    if (typeof d.note === "string" && composerEditor.html() === (row.note_html || "")) composerEditor.set(d.note);
                    if (current && d.document_name) setText(current, "[data-adp-edit-current-name]", d.document_name);
                    paintSave();
                })
                .catch(() => {});
            window.setTimeout(() => {
                const pick = addForm.querySelector("[data-adp-attach-pick]");
                if (focusFile && pick) pick.focus(); else composerEditor.focus();
            }, 300);
        };
        cancelBtns.forEach((b) => b.addEventListener("click", (e) => { e.preventDefault(); toAddMode(); }));
        document.addEventListener("click", (e) => {
            const btn = e.target.closest(".edit_btn");
            if (!btn) return;
            e.preventDefault();
            toEditMode(btn.getAttribute("data-id"), btn.hasAttribute("data-adp-focus-file"));
        });
        document.addEventListener("keydown", (e) => { if (e.key === "Escape" && editing.id > 0 && !document.querySelector(".modal.show")) toAddMode(); });
        addForm.addEventListener("submit", (e) => {
            e.preventDefault();
            if (composerEditor.isEmpty()) { inlineError(errEl, editing.id > 0 ? "The note cannot be empty." : "Write something first."); composerEditor.focus(); return; }
            const isEdit = editing.id > 0;
            busy(saveBtn, true);
            const fd = new FormData(addForm);
            fd.set("content", composerEditor.html());
            if (!isEdit) fd.delete("id");
            axios.post(route(isEdit ? "admission.update.note" : "admission.store.note"), fd, { headers: csrf })
                .then(() => {
                    busy(saveBtn, false);
                    toAddMode();
                    showSuccess(isEdit ? "Note updated" : "Note added", isEdit ? "The changes are saved." : "It is at the top of the timeline.");
                    loadNotes(true);
                })
                .catch((err) => {
                    busy(saveBtn, false);
                    paintSave();
                    const errors = err.response && err.response.status === 422 ? (err.response.data.errors || {}) : null;
                    if (errors) inlineError(errEl, Object.values(errors).flat().join(" "));
                    else showWarning("Something went wrong", "The note could not be saved. Try again, or contact the administrator.");
                });
        });
        paintSave();
    }

    /* ---------- delete / restore --------------------------------------------- */
    const confirmEl = byId("confirmModal");
    const confirmAction = ({ tone, icon, title, text, item, action, recordId, label }) => {
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
        confirmModal.show();
    };
    document.addEventListener("click", (e) => {
        const del = e.target.closest(".delete_btn");
        if (del) {
            e.preventDefault();
            confirmAction({ tone: "danger", icon: PATHS.trash, title: "Delete this note?", item: (del.getAttribute("data-adp-excerpt") || "").slice(0, 80), text: "Soft-deleted; you can restore it from the deleted list.", action: "DELETENOT", recordId: del.getAttribute("data-id"), label: "Delete" });
            return;
        }
        const res = e.target.closest(".restore_btn");
        if (res) {
            e.preventDefault();
            confirmAction({ tone: "warn", icon: PATHS.undo, title: "Restore this note?", item: (res.getAttribute("data-adp-excerpt") || "").slice(0, 80), text: "It goes back to the timeline.", action: "RESTORENOT", recordId: res.getAttribute("data-id"), label: "Restore" });
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
            const spec = action === "DELETENOT" ? { method: "delete", name: "admission.destory.note" } : (action === "RESTORENOT" ? { method: "post", name: "admission.resotore.note" } : null);
            if (!spec) { confirmModal.hide(); return; }
            confirmEl.querySelectorAll("button").forEach((b) => b.setAttribute("disabled", "disabled"));
            axios({ method: spec.method, url: route(spec.name), data: { applicant, recordid }, headers: csrf })
                .then(() => {
                    confirmModal.hide();
                    if (action === "DELETENOT") deletedCount += 1; else deletedCount = Math.max(0, deletedCount - 1);
                    paintDeleted();
                    loadNotes(true);
                    if (deletedLoaded || (deletedBox && deletedBox.open)) loadDeleted();
                    showSuccess(action === "DELETENOT" ? "Note deleted" : "Note restored", action === "DELETENOT" ? "It is in the deleted list and can be restored." : "It is back in the timeline.");
                })
                .catch(() => {
                    confirmEl.querySelectorAll("button").forEach((b) => b.removeAttribute("disabled"));
                    confirmModal.hide();
                    showWarning("Something went wrong", "The change could not be saved. Try again, or contact the administrator.");
                });
        });
        confirmEl.addEventListener("hide.tw.modal", () => {
            setText(confirmEl, ".confModDesc", "");
            const agree = confirmEl.querySelector(".agreeWith");
            if (agree) { agree.setAttribute("data-recordid", "0"); agree.setAttribute("data-status", "none"); }
            confirmEl.querySelectorAll("button").forEach((b) => b.removeAttribute("disabled"));
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

    /* ---------- print / export ---------------------------------------------- */
    const printBtn = byId("tabulator-print-AN");
    if (printBtn) printBtn.addEventListener("click", (e) => { e.preventDefault(); window.print(); });
    const exportRows = () => fetchNotes(1, 1, true).then((res) => {
        const rows = Array.isArray(res.data) ? res.data : [];
        const strip = (h) => { const d = document.createElement("div"); d.innerHTML = h || ""; return d.textContent.replace(/\s+/g, " ").trim(); };
        return [["#", "Note", "By", "When", "Attachment"]].concat(rows.map((n, i) => [i + 1, strip(n.note_html) || n.note || "", n.created_by || "", n.created_at || "", n.document_name || ""]));
    });
    const download = (blob, name) => { const url = URL.createObjectURL(blob); const a = document.createElement("a"); a.href = url; a.download = name; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); };
    const csvBtn = byId("tabulator-export-csv-AN");
    if (csvBtn) csvBtn.addEventListener("click", (e) => {
        e.preventDefault();
        exportRows().then((aoa) => download(new Blob([xlsx.utils.sheet_to_csv(xlsx.utils.aoa_to_sheet(aoa))], { type: "text/csv;charset=utf-8" }), "applicant_notes.csv"));
    });
    const xlsxBtn = byId("tabulator-export-xlsx-AN");
    if (xlsxBtn) xlsxBtn.addEventListener("click", (e) => {
        e.preventDefault();
        exportRows().then((aoa) => { const wb = xlsx.utils.book_new(); xlsx.utils.book_append_sheet(wb, xlsx.utils.aoa_to_sheet(aoa), "Applicant Notes"); xlsx.writeFile(wb, "applicant_notes.xlsx"); });
    });

    loadNotes(true);
    paintDeleted();
})();
