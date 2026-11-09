import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";
import axios from "axios";

/*
 * Applicant processes (pages/students/admission/process on the admission
 * portal shell). Same endpoints as before:
 *
 *  - admission.process.store.task.list       add / remove tasks (#studentProcessListForm)
 *  - admission.completed.task, admission.pending.task,
 *    admission.destory.task, admission.resotore.task   via #confirmModal
 *  - admission.show.task.outmoce.statuses (portal=1) + admission.process.task.result.update
 *  - admission.upload.task.documents         Dropzone on #uploadTaskDocumentForm
 *  - admission.process.log.list / admission.applicant.interview.log   #processTaskLogTable
 *  - admission.archived.process.list         .processTaskArchiveListTable, one per process
 *  - admission.process.task.users (portal=1) #taskUserModal
 *  - admission.document.download             .downloadDoc
 *
 * Every change still reloads the page once its success dialog has shown.
 */
(function () {
    "use strict";

    if (!document.querySelector("[data-adp-proc]") && !document.getElementById("addTasksModal")) return;

    const csrf = { "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "" };
    const byId = (id) => document.getElementById(id);
    const esc = (v) => String(v === null || v === undefined ? "" : v)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    const applicantId = (document.querySelector('input[name="applicant_id"]') || {}).value || "";
    const modalOf = (id) => {
        const el = byId(id);
        return el && window.tailwind && window.tailwind.Modal ? window.tailwind.Modal.getOrCreateInstance(el) : null;
    };
    const successModal = modalOf("successModal");
    const warningModal = modalOf("warningModal");
    const confirmModal = modalOf("confirmModal");
    const outcomeModal = modalOf("updateTaskOutcomeModal");
    const uploadModal = modalOf("uploadTaskDocumentModal");
    const usersModal = modalOf("taskUserModal");
    const logModal = modalOf("viewTaskLogModal");
    const addTasksModal = modalOf("addTasksModal");

    const svg = (paths, size) => `<svg width="${size || 16}" height="${size || 16}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;
    const ICON = {
        trash: svg('<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>', 30),
        alert: svg('<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/>', 30),
        check: svg('<circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>', 30),
        undoBig: svg('<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>', 30),
        undo: svg('<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>', 13),
        file: svg('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>', 15),
        download: svg('<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>', 13),
        x: svg('<path d="M18 6 6 18M6 6l12 12"/>', 14),
    };
    const busy = (btn, on) => {
        if (!btn) return;
        btn.toggleAttribute("disabled", on);
        btn.querySelectorAll(".adm-btn-loader").forEach((l) => { l.style.display = on ? "inline-block" : "none"; });
    };
    const setText = (root, sel, text) => { const el = root ? root.querySelector(sel) : null; if (el) el.textContent = text; };
    const inlineError = (el, text) => { if (!el) return; el.textContent = text || ""; el.hidden = !text; };
    const PILL_TONE = { Completed: "green", "In Progress": "amber", Pending: "muted" };
    const pill = (name) => `<span class="adp-pill adp-pill--${PILL_TONE[name] || "muted"}"><i></i>${esc(name || "—")}</span>`;
    /* "22-10-2026 14:05:33" → "22 Oct 2026, 14:05" */
    const fmtStamp = (s) => {
        const m = String(s || "").match(/^(\d{2})-(\d{2})-(\d{4})(?:\s+(\d{2}):(\d{2}))?/);
        if (!m) return esc(s || "—");
        const d = new Date(+m[3], +m[2] - 1, +m[1]);
        const date = d.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
        return esc(m[4] ? `${date}, ${m[4]}:${m[5]}` : date);
    };

    /* ---------- outcome dialogs -------------------------------------------- */
    const RELOAD_AFTER = 1600;
    const successEl = byId("successModal"), warningEl = byId("warningModal");
    const showSuccess = (title, text, reload) => {
        setText(successEl, ".successModalTitle", title);
        setText(successEl, ".successModalDesc", text || "");
        const closer = successEl && successEl.querySelector(".successCloser");
        if (closer) closer.setAttribute("data-action", reload ? "RELOAD" : "DISMISS");
        if (successModal) successModal.show();
        if (reload) window.setTimeout(() => window.location.reload(), RELOAD_AFTER);
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

    /* ---------- confirm dialog --------------------------------------------- */
    const confirmEl = byId("confirmModal");
    const confirmAction = ({ tone, icon, title, text, item, action, recordId, label }) => {
        if (!confirmEl || !confirmModal) return;
        const content = confirmEl.querySelector(".modal-content");
        if (content) content.className = `modal-content adp-dialog adp-dialog--${tone}`;
        const ico = confirmEl.querySelector(".adp-dialog__ico");
        if (ico) ico.innerHTML = icon;
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
    const ACTIONS = {
        DELETETASK: { method: "delete", name: "admission.destory.task", done: ["Task archived", "It is in the Archived list of this process and can be restored."] },
        COMPLETEDTASK: { method: "post", name: "admission.completed.task", done: ["Task completed", "It is now in the Completed lane."] },
        PENDINGTASK: { method: "post", name: "admission.pending.task", done: ["Task reopened", "It is back on the board."] },
        RESTORETASK: { method: "post", name: "admission.resotore.task", done: ["Task restored", "It is back on the board."] },
    };
    if (confirmEl) {
        confirmEl.addEventListener("click", (e) => {
            const agree = e.target.closest(".agreeWith");
            if (!agree) return;
            e.preventDefault();
            const spec = ACTIONS[agree.getAttribute("data-status")];
            if (!spec) { confirmModal.hide(); return; }
            const recordid = agree.getAttribute("data-recordid");
            const applicant = agree.getAttribute("data-applicant") || applicantId;
            confirmEl.querySelectorAll("button").forEach((b) => b.setAttribute("disabled", "disabled"));
            axios({ method: spec.method, url: route(spec.name), data: { applicant, recordid }, headers: csrf })
                .then((res) => {
                    confirmModal.hide();
                    /* reopen / restore say which lane the task landed in */
                    const lane = res.data && res.data.status ? `It is back on the board, in ${res.data.status}.` : spec.done[1];
                    /* completing / archiving can move the application (first completed → In Progress, none left → Awaiting Decision) */
                    const moved = res.data && res.data.applicant_status ? ` The application moved to ${res.data.applicant_status}.` : "";
                    /* Task Automation actions run on completion, e.g. "The applicant photo was updated …" */
                    const notes = res.data && Array.isArray(res.data.action_notes) && res.data.action_notes.length ? " " + res.data.action_notes.join(" ") : "";
                    showSuccess(spec.done[0], (ACTIONS.PENDINGTASK === spec || ACTIONS.RESTORETASK === spec ? lane : spec.done[1]) + moved + notes, true);
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
        });
    }

    /* ---------- popovers (assigned staff / uploads) ------------------------ */
    const closePops = () => {
        document.querySelectorAll(".adp-pop:not([hidden])").forEach((pop) => {
            pop.hidden = true;
            const btn = pop.parentElement && pop.parentElement.querySelector("[data-adp-pop]");
            if (btn) { btn.classList.remove("on"); btn.setAttribute("aria-expanded", "false"); }
        });
    };
    document.addEventListener("click", (e) => {
        const trigger = e.target.closest("[data-adp-pop]");
        if (trigger) {
            const pop = trigger.parentElement.querySelector(".adp-pop");
            const wasClosed = pop && pop.hidden;
            closePops();
            if (pop && wasClosed) { pop.hidden = false; trigger.classList.add("on"); trigger.setAttribute("aria-expanded", "true"); }
            return;
        }
        if (!e.target.closest(".adp-pop")) closePops();
    });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") closePops(); });

    /* ---------- tables ------------------------------------------------------ */
    const tableOpts = (extra) => ({
        ajaxFiltering: true,
        ajaxSorting: true,
        pagination: "remote",
        paginationSize: 10,
        paginationSizeSelector: [10, 20, 50, true],
        langs: { default: { pagination: { page_size: "Rows", all: "All", prev: "Previous", next: "Next", prev_title: "Previous page", next_title: "Next page" } } },
        layout: "fitColumns",
        ...extra,
    });

    /* board / archived segment, one per process */
    document.querySelectorAll("[data-adp-proc]").forEach((proc) => {
        const board = proc.querySelector("[data-adp-board]");
        const archive = proc.querySelector("[data-adp-archive]");
        const seg = Array.from(proc.querySelectorAll("[data-adp-view]"));
        if (!board || !archive) return;
        let archiveTable = null;
        const buildArchive = () => {
            const holder = archive.querySelector(".processTaskArchiveListTable");
            const loading = archive.querySelector("[data-adp-archive-loading]");
            const empty = archive.querySelector("[data-adp-archive-empty]");
            const wrap = archive.querySelector("[data-adp-archive-table]");
            if (!holder) return null;
            /* laid out (for widths) but invisible until the rows are in */
            wrap.hidden = false;
            wrap.classList.add("adp-arch-pending");
            const table = new Tabulator(holder, tableOpts({
                ajaxURL: route("admission.archived.process.list"),
                ajaxParams: { applicantId: holder.getAttribute("data-applicant"), processId: holder.getAttribute("data-process") },
                placeholder: "<b>Nothing archived.</b><span>Tasks you archive from the board appear here and can be restored.</span>",
                columns: [
                    { title: "Task", field: "name", minWidth: 220, widthGrow: 2, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => {
                        const d = c.getData();
                        return `<div class="adp-cell"><b>${esc(d.name)}</b>${d.desc && d.desc !== d.name ? `<span class="adp-sub-line">${esc(d.desc)}</span>` : ""}</div>`;
                    } },
                    { title: "Was", field: "status", width: 130, headerSort: false, formatter: (c) => pill(c.getValue()) },
                    { title: "Archived", field: "deleted_at", width: 170, formatter: (c) => esc(c.getValue() || "—") },
                    { title: "By", field: "archived_by", width: 170, headerSort: false, formatter: (c) => esc(c.getValue() || "—") },
                    { title: "", field: "id", width: 120, headerSort: false, hozAlign: "right", cssClass: "adp-cell--right", formatter: (c) => `<button type="button" class="adp-btn adp-btn--ghost adp-btn--sm restore_btn" data-id="${esc(c.getValue())}" data-adp-task-name="${esc(c.getData().name)}">${ICON.undo}Restore</button>` },
                ],
                dataLoaded: (data) => {
                    const none = !data || data.length === 0;
                    if (loading) loading.hidden = true;
                    if (empty) empty.hidden = !none;
                    wrap.classList.remove("adp-arch-pending");
                    wrap.hidden = none;
                    if (!none) window.setTimeout(() => table.redraw(true), 0);
                },
            }));
            return table;
        };
        const show = (view) => {
            seg.forEach((b) => {
                const on = b.getAttribute("data-adp-view") === view;
                b.classList.toggle("on", on);
                b.setAttribute("aria-pressed", on ? "true" : "false");
            });
            board.hidden = view !== "board";
            archive.hidden = view !== "archive";
            if (view === "archive") {
                if (!archiveTable) archiveTable = buildArchive(); else archiveTable.redraw(true);
            }
        };
        seg.forEach((b) => b.addEventListener("click", () => show(b.getAttribute("data-adp-view"))));
    });

    /* restore from the archived list */
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".processTaskArchiveListTable .restore_btn");
        if (!btn) return;
        e.preventDefault();
        confirmAction({ tone: "warn", icon: ICON.undoBig, title: "Restore this task?", item: btn.getAttribute("data-adp-task-name") || "", text: "It goes back to the board of this process with the status it had.", action: "RESTORETASK", recordId: btn.getAttribute("data-id"), label: "Restore" });
    });

    /* ---------- card actions ---------------------------------------------- */
    document.addEventListener("click", (e) => {
        const complete = e.target.closest(".markAsCompleted");
        if (complete) {
            e.preventDefault();
            confirmAction({ tone: "success", icon: ICON.check, title: "Mark this task complete?", item: complete.getAttribute("data-adp-task-name") || "", text: "It moves to Completed. You can reopen it later if needed.", action: "COMPLETEDTASK", recordId: complete.getAttribute("data-recordid"), label: "Mark complete" });
            return;
        }
        const reopen = e.target.closest(".markAsPending");
        if (reopen) {
            e.preventDefault();
            confirmAction({ tone: "warn", icon: ICON.alert, title: "Reopen this task?", item: reopen.getAttribute("data-adp-task-name") || "", text: "It goes back to the board: In Progress if its outcome and upload are still in, otherwise Pending. If the application is Awaiting Decision it returns to In Progress.", action: "PENDINGTASK", recordId: reopen.getAttribute("data-recordid"), label: "Reopen" });
            return;
        }
        const archiveBtn = e.target.closest(".deleteApplicantTask");
        if (archiveBtn) {
            e.preventDefault();
            confirmAction({ tone: "danger", icon: ICON.trash, title: "Archive this task?", item: archiveBtn.getAttribute("data-adp-task-name") || "", text: "It leaves the board and is kept in the archived list for this process, where it can be restored.", action: "DELETETASK", recordId: archiveBtn.getAttribute("data-taskid"), label: "Archive" });
        }
    });

    /* ---------- add tasks --------------------------------------------------- */
    const pickForm = byId("studentProcessListForm");
    if (pickForm) {
        const pickError = pickForm.querySelector("[data-adp-pick-error]");
        pickForm.addEventListener("change", () => inlineError(pickError, ""));
        pickForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const ids = Array.from(pickForm.querySelectorAll(".task_list_id:checked")).map((c) => c.value);
            if (!ids.length) { inlineError(pickError, "Tick at least one task."); return; }
            const btn = byId("addProcessItemsAdd");
            busy(btn, true);
            axios.post(route("admission.process.store.task.list"), { task_list_ids: ids, applicant_id: (pickForm.querySelector('input[name="applicant_id"]') || {}).value || applicantId }, { headers: csrf })
                .then((res) => {
                    busy(btn, false);
                    if (addTasksModal) addTasksModal.hide();
                    showSuccess("Tasks saved", (res.data && res.data.message) || "The board has been updated.", true);
                })
                .catch(() => {
                    busy(btn, false);
                    if (addTasksModal) addTasksModal.hide();
                    showWarning("Something went wrong", "The tasks could not be saved. Try again, or contact the administrator.", true);
                });
        });
    }

    /* ---------- update outcome --------------------------------------------- */
    const outcomeEl = byId("updateTaskOutcomeModal");
    const outcomeForm = byId("updateTaskOutcomeForm");
    const outcomeBody = outcomeEl && outcomeEl.querySelector("[data-adp-outcome-body]");
    const outcomeLoading = '<div class="adp-arch-loading"><span class="adp-spin"></span>Loading outcomes…</div>';
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".updateTaskOutcome");
        if (!btn || !outcomeEl) return;
        e.preventDefault();
        closePops();
        const taskId = btn.getAttribute("data-applicanttaskid");
        setText(outcomeEl, "[data-adp-outcome-task]", btn.getAttribute("data-adp-task-name") || "");
        const idInput = outcomeEl.querySelector('input[name="applicant_task_id"]');
        if (idInput) idInput.value = taskId;
        if (outcomeBody) outcomeBody.innerHTML = outcomeLoading;
        const save = byId("updateOutcomeBtn");
        if (save) save.removeAttribute("disabled");
        if (outcomeModal) outcomeModal.show();
        axios.post(route("admission.show.task.outmoce.statuses"), { taskId, portal: 1 }, { headers: csrf })
            .then((res) => {
                const msg = (res.data && res.data.message) || {};
                if (outcomeBody) outcomeBody.innerHTML = msg.res || "";
                if (save && Number(msg.suc) !== 1) save.setAttribute("disabled", "disabled");
            })
            .catch(() => {
                if (outcomeBody) outcomeBody.innerHTML = '<div class="adp-notice adp-notice--bad"><b>Could not load the outcomes</b><div>Close this dialog and try again.</div></div>';
                if (save) save.setAttribute("disabled", "disabled");
            });
    });
    if (outcomeForm) {
        outcomeForm.addEventListener("change", () => {
            inlineError(outcomeForm.querySelector("[data-adp-outcome-error]"), "");
            inlineError(outcomeForm.querySelector("[data-adp-note-error]"), "");
        });
        outcomeForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const checked = outcomeForm.querySelector(".resultStatus:checked");
            if (!checked) { inlineError(outcomeForm.querySelector("[data-adp-outcome-error]"), "Choose an outcome."); return; }
            const note = outcomeForm.querySelector('textarea[name="note"]');
            const label = checked.closest("label");
            if (label && label.hasAttribute("data-requires-note") && note && !note.value.trim()) {
                inlineError(outcomeForm.querySelector("[data-adp-note-error]"), "Add a note explaining the reason.");
                note.focus();
                return;
            }
            const btn = byId("updateOutcomeBtn");
            busy(btn, true);
            axios.post(route("admission.process.task.result.update"), new FormData(outcomeForm), { headers: csrf })
                .then((res) => {
                    busy(btn, false);
                    if (outcomeModal) outcomeModal.hide();
                    showSuccess("Outcome recorded", (res.data && res.data.hint) || "The outcome is saved and noted in the task log.", true);
                })
                .catch(() => {
                    busy(btn, false);
                    if (outcomeModal) outcomeModal.hide();
                    showWarning("Something went wrong", "The outcome could not be saved. Try again, or contact the administrator.", true);
                });
        });
    }
    if (outcomeEl) outcomeEl.addEventListener("hide.tw.modal", () => {
        if (outcomeBody) outcomeBody.innerHTML = outcomeLoading;
        const idInput = outcomeEl.querySelector('input[name="applicant_task_id"]');
        if (idInput) idInput.value = "0";
        busy(byId("updateOutcomeBtn"), false);
    });

    /* ---------- upload documents (Dropzone) --------------------------------- */
    const uploadEl = byId("uploadTaskDocumentModal");
    const dzForm = byId("uploadTaskDocumentForm");
    let dz = null, dzError = false, uploading = false;
    const uploadError = (text) => inlineError(uploadEl && uploadEl.querySelector("[data-adp-upload-error]"), text);
    if (dzForm && uploadEl) {
        Dropzone.autoDiscover = false;
        dz = new Dropzone("#uploadTaskDocumentForm", {
            url: dzForm.getAttribute("action"),
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 5,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: false,
            previewsContainer: "[data-adp-dz-list]",
            previewTemplate: `<li class="dz-preview"><span class="adp-dz-fico">${ICON.file}</span><span class="adp-dz-name" data-dz-name></span><span class="adp-dz-size" data-dz-size></span><small class="adp-dz-err" data-dz-errormessage></small><button type="button" class="adp-iconbtn adp-iconbtn--xs" data-dz-remove aria-label="Remove file">${ICON.x}</button></li>`,
            headers: csrf,
            accept: (file, done) => { done(); },
        });
        dz.on("addedfile", (file) => {
            uploadError("");
            if (file.name.match(/[`!@#$%^&*+\=\[\]{};':"\\|,<>\/?~]/)) {
                uploadError("One file name contains characters that are not allowed, so that file was removed.");
                dz.removeFile(file);
                window.setTimeout(() => uploadError(""), 5000);
            }
        });
        dz.on("maxfilesexceeded", (file) => {
            uploadError("Only 10 files per upload.");
            dz.removeFile(file);
            window.setTimeout(() => uploadError(""), 2500);
        });
        dz.on("error", () => { if (uploading) dzError = true; });
        let lastUpload = null;
        dz.on("success", (file, response) => { lastUpload = response || null; if (file.previewElement) file.previewElement.classList.add("dz-success"); });
        dz.on("queuecomplete", () => {
            if (!uploading) return;
            uploading = false;
            busy(byId("uploadProcessDoc"), false);
            if (uploadModal) uploadModal.hide();
            if (!dzError) showSuccess("Documents uploaded", (lastUpload && lastUpload.hint) ? `The files are attached to the task. ${lastUpload.hint}` : "The files are attached to the task.", true);
            else showWarning("Upload failed", "Some files could not be uploaded. Try again, or contact the administrator.", true);
        });
        const uploadBtn = byId("uploadProcessDoc");
        if (uploadBtn) uploadBtn.addEventListener("click", (e) => {
            e.preventDefault();
            if (!dz.getAcceptedFiles().length) { uploadError("Add at least one file."); return; }
            const hard = uploadEl.querySelector('input[name="hard_copy_check_status"]');
            const hardHidden = uploadEl.querySelector('input[name="hard_copy_check"]');
            if (hardHidden) hardHidden.value = hard && hard.checked ? "1" : "0";
            const nameInput = byId("process_doc_name"), nameHidden = uploadEl.querySelector('input[name="display_file_name"]');
            if (nameHidden && nameInput) nameHidden.value = nameInput.value;
            dzError = false;
            uploading = true;
            busy(uploadBtn, true);
            dz.processQueue();
        });
        const nameInput = byId("process_doc_name");
        if (nameInput) nameInput.addEventListener("input", () => {
            const hidden = uploadEl.querySelector('input[name="display_file_name"]');
            if (hidden) hidden.value = nameInput.value;
        });
        uploadEl.addEventListener("hide.tw.modal", () => {
            ["applicant_task_id", "display_file_name", "hard_copy_check"].forEach((n) => {
                const el = uploadEl.querySelector(`input[name="${n}"]`);
                if (el) el.value = n === "applicant_task_id" ? "0" : (n === "hard_copy_check" ? "0" : "");
            });
            if (nameInput) nameInput.value = "";
            const hard = uploadEl.querySelector('input[name="hard_copy_check_status"]');
            if (hard) hard.checked = false;
            uploadError("");
            busy(byId("uploadProcessDoc"), false);
            uploading = false;
            dz.removeAllFiles(true);
        });
    }
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".uploadTaskDoc");
        if (!btn || !uploadEl) return;
        e.preventDefault();
        closePops();
        const idInput = uploadEl.querySelector('input[name="applicant_task_id"]');
        if (idInput) idInput.value = btn.getAttribute("data-applicanttaskid");
        setText(uploadEl, "[data-adp-upload-task]", btn.getAttribute("data-adp-task-name") || "Task");
        const n = Number(btn.getAttribute("data-adp-count")) || 0;
        setText(uploadEl, "[data-adp-upload-sub]", n ? `Evidence for this task. ${n} file${n > 1 ? "s" : ""} already attached.` : "Evidence for this task.");
        if (uploadModal) uploadModal.show();
    });

    /* ---------- assigned staff dialog ---------------------------------------- */
    const usersEl = byId("taskUserModal");
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".taskUserLoader");
        if (!btn || !usersEl) return;
        e.preventDefault();
        closePops();
        const loader = usersEl.querySelector(".taskUserModalLoader"), content = usersEl.querySelector(".taskUserModalContent"), tbody = usersEl.querySelector("tbody");
        setText(usersEl, "[data-adp-users-task]", btn.getAttribute("data-adp-task-name") || "");
        if (loader) loader.style.display = "";
        if (content) content.style.display = "none";
        if (usersModal) usersModal.show();
        axios.post(route("admission.process.task.users"), { task_id: btn.getAttribute("data-taskid"), portal: 1 }, { headers: csrf })
            .then((res) => {
                if (tbody) tbody.innerHTML = (res.data && res.data.res) || "";
                if (loader) loader.style.display = "none";
                if (content) content.style.display = "";
            })
            .catch(() => {
                if (tbody) tbody.innerHTML = '<tr><td colspan="5"><div class="adp-notice adp-notice--bad"><b>Could not load the staff list</b><div>Close this dialog and try again.</div></div></td></tr>';
                if (loader) loader.style.display = "none";
                if (content) content.style.display = "";
            });
    });
    if (usersEl) usersEl.addEventListener("hidden.tw.modal", () => {
        const tbody = usersEl.querySelector("tbody");
        if (tbody) tbody.innerHTML = "";
        const loader = usersEl.querySelector(".taskUserModalLoader"), content = usersEl.querySelector(".taskUserModalContent");
        if (loader) loader.style.display = "";
        if (content) content.style.display = "none";
    });

    /* ---------- task change log -------------------------------------------- */
    const logEl = byId("viewTaskLogModal");
    let logTable = null, interviewTable = null, pendingLog = null;
    const destroyLog = () => {
        [logTable, interviewTable].forEach((t) => { if (t) { try { t.destroy(); } catch (err) { /* already gone */ } } });
        logTable = null;
        interviewTable = null;
    };
    const logColumns = [
        { title: "#", field: "sl", width: 60, headerSort: false, cssClass: "adp-cell--ref" },
        { title: "When", field: "created_at", width: 170, formatter: (c) => fmtStamp(c.getValue()) },
        { title: "Change", field: "actions", minWidth: 150, widthGrow: 1, headerSort: false, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => {
            const d = c.getData();
            return `<div class="adp-log-change"><b>${esc(d.actions || "—")}</b>${d.field_name ? `<span>${esc(d.field_name)}</span>` : ""}</div>`;
        } },
        { title: "Previous", field: "prev_field_value", minWidth: 110, widthGrow: 1, headerSort: false, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => { const v = c.getValue(); return v === null || v === undefined || v === "" ? "—" : esc(v); } },
        /* the server renders the document link itself */
        { title: "New", field: "current_field_value", minWidth: 160, widthGrow: 2, headerSort: false, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => { const v = c.getValue(); return v === null || v === undefined || v === "" ? "—" : String(v); } },
        { title: "By", field: "created_by", width: 170, headerSort: false, formatter: (c) => esc(c.getValue() || "—") },
    ];
    /* interview sessions: who did what, when, with what outcome, and the document */
    const OUTCOME_TONE = { pass: "green", fail: "red" };
    const interviewColumns = [
        { title: "#", field: "sl", width: 60, headerSort: false, cssClass: "adp-cell--ref" },
        { title: "When", field: "date", width: 170, headerSort: false, formatter: (c) => {
            const d = c.getData();
            const time = String(d.time || "").replace(/^\s*-\s*$/, "").trim();
            return fmtStamp(d.date) + (time ? `<span class="adp-sub-line">${esc(time)}</span>` : "");
        } },
        { title: "Who", field: "interviewer", width: 170, formatter: (c) => esc(c.getValue() || "—") },
        { title: "What", field: "current_file_name", minWidth: 200, widthGrow: 2, headerSort: false, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => {
            const d = c.getData();
            return `<div class="adp-log-change"><b>Interview</b>${d.current_file_name ? `<span>${esc(d.current_file_name)}</span>` : ""}</div>`;
        } },
        { title: "Outcome", field: "result", width: 150, formatter: (c) => {
            const d = c.getData();
            const result = String(d.result || "").trim();
            if (result && result.toUpperCase() !== "N/A") return `<span class="adp-pill adp-pill--${OUTCOME_TONE[result.toLowerCase()] || "muted"}"><i></i>${esc(result)}</span>`;
            return `<span class="adp-pill adp-pill--muted"><i></i>${esc(d.status || "Pending")}</span>`;
        } },
        { title: "", field: "file", width: 70, headerSort: false, hozAlign: "right", cssClass: "adp-cell--right", formatter: (c) => {
            const d = c.getData();
            return d.file ? `<button type="button" class="adp-iconbtn adp-iconbtn--xs downloadDoc" data-id="${esc(d.doc_id)}" title="Download ${esc(d.current_file_name || "file")}" aria-label="Download">${ICON.download}</button>` : "";
        } },
    ];
    /* the change log always; interview tasks get their interview sessions underneath */
    const buildLog = () => {
        if (!pendingLog) return;
        destroyLog();
        const holder = byId("processTaskLogTable");
        if (!holder) return;
        const { interview, taskId, applicant } = pendingLog;
        pendingLog = null;
        holder.innerHTML = "";
        logTable = new Tabulator(holder, tableOpts({ ajaxURL: route("admission.process.log.list"), ajaxParams: { applicantTaskId: taskId }, placeholder: "<b>No changes logged yet.</b><span>Outcomes, uploads, notes and status changes are recorded here.</span>", columns: logColumns }));
        const block = logEl.querySelector("[data-adp-log-interviews]");
        const iHolder = byId("processInterviewLogTable");
        if (block) block.hidden = !interview;
        if (interview && iHolder) {
            iHolder.innerHTML = "";
            if (block) block.hidden = true;
            /* every upload to an interview task creates a placeholder row (no result, no time);
               those are already in the change log, so only real sessions are listed here */
            const isSession = (r) => (String(r.result || "").trim() !== "" && String(r.result).trim().toUpperCase() !== "N/A") || String(r.time || "").replace(/[\s-]/g, "") !== "";
            interviewTable = new Tabulator(iHolder, tableOpts({
                ajaxURL: route("admission.applicant.interview.log"),
                ajaxParams: { applicantTaskId: taskId, applicantId: applicant },
                ajaxFiltering: false,
                ajaxSorting: false,
                ajaxResponse: (url, params, res) => {
                    const rows = Array.isArray(res && res.data) ? res.data.filter(isSession) : [];
                    if (block) block.hidden = rows.length === 0;
                    if (rows.length) window.setTimeout(() => { try { interviewTable.redraw(true); } catch (err) { /* gone */ } }, 0);
                    return { last_page: res && res.last_page ? res.last_page : 1, data: rows };
                },
                placeholder: "<b>No interview recorded yet.</b>",
                columns: interviewColumns,
            }));
        }
    };
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".viewTaskLogBtn");
        if (!btn || !logEl) return;
        e.preventDefault();
        closePops();
        const holder = byId("processTaskLogTable");
        if (holder) {
            holder.setAttribute("data-applicanttaskid", btn.getAttribute("data-applicanttaskid"));
            holder.setAttribute("data-interview", btn.getAttribute("data-interview"));
        }
        setText(logEl, "[data-adp-log-task]", btn.getAttribute("data-adp-task-name") || "Task");
        setText(logEl, "[data-adp-log-status]", btn.getAttribute("data-adp-task-status") || "");
        pendingLog = { interview: btn.getAttribute("data-interview") === "1", taskId: btn.getAttribute("data-applicanttaskid"), applicant: btn.getAttribute("data-applicantid") || applicantId };
        if (logModal) logModal.show();
        /* if the modal is already visible there is no shown event to wait for */
        if (logEl.classList.contains("show") && logEl.getAttribute("aria-hidden") === "false") buildLog();
    });
    if (logEl) {
        logEl.addEventListener("shown.tw.modal", buildLog);
        logEl.addEventListener("hidden.tw.modal", () => { destroyLog(); pendingLog = null; });
    }

    /* ---------- downloads --------------------------------------------------- */
    document.addEventListener("click", (e) => {
        const link = e.target.closest(".downloadDoc");
        if (!link) return;
        e.preventDefault();
        if (link.hasAttribute("disabled")) return;
        link.setAttribute("disabled", "disabled");
        axios.post(route("admission.document.download"), { row_id: link.getAttribute("data-id") }, { headers: csrf })
            .then((res) => {
                link.removeAttribute("disabled");
                const url = res.data && res.data.res;
                if (url) window.open(url, "_blank");
            })
            .catch(() => { link.removeAttribute("disabled"); });
    });
})();
