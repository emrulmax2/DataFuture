import xlsx from "xlsx";
import axios from "axios";

/*
 * Communication (pages/students/admission/communication on the admission portal shell).
 * Email / SMS / Letter tabs; each has a compose form and the list of what was sent.
 * Same endpoints as before:
 *
 *  - admission.communication.mail.list / sms.list / letter.list   paged; status 1 = live, 2 = deleted
 *  - admission.communication.send.mail   #sendEmailForm (+ body from the editor, documents[])
 *  - admission.communication.send.sms    #smsSMSForm
 *  - admission.communication.send.letter #addLetterForm (+ letter_body from the editor)
 *  - admission.communication.get.mail.template / get.sms.template / get.letter.set
 *  - admission.communication.mail.show / sms.show                 the View dialog
 *  - admission.communication.{mail,sms,letter}.{destroy,restore}  via #confirmModal
 *  - admission.document.download                                   .downloadDoc (attachments, letter PDFs)
 *
 * Print prints the sent list of the active channel; Export CSV / XLSX write it out.
 */
(function () {
    "use strict";

    const root = document.querySelector("[data-adp-comms]");
    if (!root) return;

    const csrf = { "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "" };
    const byId = (id) => document.getElementById(id);
    const esc = (v) => String(v === null || v === undefined ? "" : v)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    const applicantId = root.getAttribute("data-applicant") || (document.querySelector('input[name="applicant_id"]') || {}).value || "";
    const modalOf = (id) => {
        const el = byId(id);
        return el && window.tailwind && window.tailwind.Modal ? window.tailwind.Modal.getOrCreateInstance(el) : null;
    };
    const successModal = modalOf("successModal"), warningModal = modalOf("warningModal"), confirmModal = modalOf("confirmModal"), viewModal = modalOf("viewCommunicationModal");
    const svg = (paths, size) => `<svg width="${size || 16}" height="${size || 16}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths}</svg>`;
    const PATHS = {
        file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
        download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
        eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        trash: '<path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>',
        undo: '<path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-15-6.7L3 13"/>',
        x: '<path d="M18 6 6 18M6 6l12 12"/>',
        send: '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
    };
    const fmtSize = (n) => (n > 1024 * 1024 ? (n / 1024 / 1024).toFixed(1) + " MB" : Math.max(1, Math.round(n / 1024)) + " KB");
    const busy = (btn, on) => {
        if (!btn) return;
        btn.toggleAttribute("disabled", on);
        btn.querySelectorAll(".adm-btn-loader").forEach((l) => { l.style.display = on ? "inline-block" : "none"; });
    };
    const setText = (scope, sel, text) => { const el = scope ? scope.querySelector(sel) : null; if (el) el.textContent = text; };
    const selectedText = (sel) => (sel && sel.selectedIndex >= 0 ? (sel.options[sel.selectedIndex].text || "").trim() : "");

    /* ---------- outcome dialogs -------------------------------------------- */
    const successEl = byId("successModal"), warningEl = byId("warningModal");
    const showSuccess = (title, text) => {
        setText(successEl, ".successModalTitle", title);
        setText(successEl, ".successModalDesc", text || "");
        const closer = successEl && successEl.querySelector(".successCloser");
        if (closer) closer.setAttribute("data-action", "DISMISS");
        if (successModal) successModal.show();
        window.setTimeout(() => { if (successModal) successModal.hide(); }, 2400);
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

    /* ---------- form errors (server keys map to .error-{key} / .{key}) ------ */
    const clearErrors = (form) => {
        form.querySelectorAll(".acc__input-error").forEach((el) => { el.textContent = ""; });
        form.querySelectorAll(".err").forEach((el) => el.classList.remove("err"));
    };
    const paintErrors = (form, errors) => {
        let first = null;
        Object.keys(errors || {}).forEach((key) => {
            const text = [].concat(errors[key]).join(" ");
            const slot = form.querySelector(`.error-${key}`);
            if (slot) slot.textContent = text;
            const ctl = form.querySelector(`[name="${key}"]`) || form.querySelector(`.${key}`) || (key === "body" || key === "letter_body" ? form.querySelector("[data-adp-editor]") : null);
            if (ctl) { ctl.classList.add("err"); if (!first) first = ctl; }
        });
        if (first && typeof first.focus === "function") first.focus();
    };

    /* ---------- the design's small editor ----------------------------------- */
    const makeEditor = (editorRoot) => {
        const body = editorRoot.querySelector("[data-adp-ed-body]");
        const bar = editorRoot.querySelector(".adp-ed-bar");
        const api = {
            root: editorRoot, body,
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
    const editorOf = (bodyId) => { const body = byId(bodyId); return body ? makeEditor(body.closest("[data-adp-editor]")) : null; };

    /* ---------- channels ---------------------------------------------------- */
    const CH = {
        email: { list: "admission.communication.mail.list", q: "queryStrCME", s: "statusCME", show: "admission.communication.mail.show", destroy: "admission.communication.mail.destroy", restore: "admission.communication.mail.restore", del: "DELETEMAIL", res: "RESTOREMAIL", noun: "Email", plural: "emails", title: "Sent emails", file: "applicant_emails" },
        sms: { list: "admission.communication.sms.list", q: "queryStrCMS", s: "statusCMS", show: "admission.communication.sms.show", destroy: "admission.communication.sms.destroy", restore: "admission.communication.sms.restore", del: "DELETESMS", res: "RESTORESMS", noun: "SMS", plural: "messages", title: "Sent messages", file: "applicant_sms" },
        letter: { list: "admission.communication.letter.list", q: "queryStrCML", s: "statusCML", show: null, destroy: "admission.communication.letter.destroy", restore: "admission.communication.letter.restore", del: "DELETELETTER", res: "RESTORELETTER", noun: "Letter", plural: "letters", title: "Sent letters", file: "applicant_letters" },
    };
    const ACTIONS = {};
    Object.keys(CH).forEach((k) => { ACTIONS[CH[k].del] = { channel: k, method: "delete", name: CH[k].destroy, kind: "delete" }; ACTIONS[CH[k].res] = { channel: k, method: "post", name: CH[k].restore, kind: "restore" }; });
    let active = "email";
    let query = "";
    let deletedCounts = { email: 0, sms: 0, letter: 0 };
    try { deletedCounts = Object.assign(deletedCounts, JSON.parse(root.getAttribute("data-adp-deleted-counts") || "{}")); } catch (e) { /* defaults */ }
    const state = { email: { page: 1, lastPage: 1, total: 0, rows: [] }, sms: { page: 1, lastPage: 1, total: 0, rows: [] }, letter: { page: 1, lastPage: 1, total: 0, rows: [] } };
    const deletedLoaded = { email: false, sms: false, letter: false };
    const subjectOf = (channel, r) => (channel === "letter" ? (r.letter_title || r.letter_type || "Letter") : (r.subject || "—"));

    const fetchList = (channel, page, status, size) => {
        const c = CH[channel];
        const params = { applicantId, page, size: size || 10 };
        params[c.q] = query;
        params[c.s] = status;
        return axios.get(route(c.list), { params, headers: csrf }).then((r) => r.data || {});
    };

    /* ---------- sent list ---------------------------------------------------- */
    const sentRows = root.querySelector("[data-adp-sent-rows]");
    const sentLoading = root.querySelector("[data-adp-sent-loading]");
    const sentEmpty = root.querySelector("[data-adp-sent-empty]");
    const sentMore = root.querySelector("[data-adp-sent-more]");
    const sentTitle = root.querySelector("[data-adp-sent-title]");
    const sentCount = root.querySelector("[data-adp-sent-count]");
    const metaOf = (channel, r) => {
        const bits = [r.created_at, r.created_by];
        if (channel === "email") { if (r.smtp) bits.push(r.smtp); if (Number(r.attachments) > 0) bits.push(`${r.attachments} attachment${Number(r.attachments) > 1 ? "s" : ""}`); }
        if (channel === "sms") { if (r.template) bits.push(r.template); if (Number(r.segments) > 1) bits.push(`${r.segments} segments`); }
        if (channel === "letter") { if (r.issued_date) bits.push(`issued ${r.issued_date}`); if (r.signatory_name) bits.push(`signed by ${r.signatory_name}`); if (r.smtp) bits.push(r.smtp); }
        return bits.filter(Boolean);
    };
    const rowHtml = (channel, r) => {
        const subject = subjectOf(channel, r);
        const acts = [];
        if (channel === "letter" && Number(r.docurl) > 0) acts.push(`<button type="button" class="adp-iconbtn downloadDoc" data-id="${esc(r.docurl)}" title="Download PDF" aria-label="Download PDF">${svg(PATHS.download, 15)}</button>`);
        acts.push(`<button type="button" class="adp-iconbtn view_btn" data-id="${esc(r.id)}" title="View" aria-label="View">${svg(PATHS.eye, 15)}</button>`);
        acts.push(`<button type="button" class="adp-iconbtn adp-iconbtn--danger delete_btn" data-id="${esc(r.id)}" data-adp-subject="${esc(subject)}" title="Delete" aria-label="Delete">${svg(PATHS.trash, 15)}</button>`);
        const sub = channel === "letter" && r.letter_type ? `<small class="adp-vrow__type">${esc(r.letter_type)}</small>` : "";
        return `<div class="adp-vrow adp-vrow--comm" data-adp-comm="${esc(r.id)}"><div class="adp-vrow__main">${sub}<b class="adp-vrow__subject" title="${esc(subject)}">${esc(subject)}</b><span class="adp-vrow__meta">${metaOf(channel, r).map(esc).join(" · ")} · <span class="adp-pill adp-pill--green adp-pill--tight"><i></i>Sent</span></span></div><div class="adp-acts">${acts.join("")}</div></div>`;
    };
    const paintCount = () => {
        if (!sentCount) return;
        const st = state[active];
        sentCount.textContent = st.total ? `${st.total} ${st.total === 1 ? CH[active].plural.replace(/s$/, "").replace(/^message$/, "message") : CH[active].plural}${query ? ` matching “${query}”` : ""}` : "";
    };
    const loadList = (reset) => {
        const channel = active, st = state[channel];
        if (reset) { st.page = 1; st.rows = []; if (sentRows) sentRows.innerHTML = ""; }
        if (sentLoading) sentLoading.hidden = false;
        if (sentEmpty) sentEmpty.hidden = true;
        if (sentMore) sentMore.hidden = true;
        return fetchList(channel, st.page, 1).then((res) => {
            if (channel !== active) return;
            const rows = Array.isArray(res.data) ? res.data : [];
            st.lastPage = Number(res.last_page) || 1;
            st.total = Number(res.total_rows) || rows.length;
            st.rows = st.rows.concat(rows);
            if (sentRows) sentRows.insertAdjacentHTML("beforeend", rows.map((r) => rowHtml(channel, r)).join(""));
            if (sentLoading) sentLoading.hidden = true;
            if (sentEmpty) sentEmpty.hidden = st.rows.length > 0;
            if (sentMore) sentMore.hidden = !(st.page < st.lastPage);
            paintCount();
        }).catch(() => {
            if (channel !== active) return;
            if (sentLoading) sentLoading.hidden = true;
            showWarning(`Could not load the ${CH[channel].plural}`, "Reload the page and try again.");
        });
    };
    if (sentMore) sentMore.addEventListener("click", (e) => { if (!e.target.closest("button")) return; state[active].page += 1; loadList(false); });

    /* search (the old per-table query filters, one box for the active channel) */
    const queryInput = byId("query-CM");
    const resetBtn = byId("tabulator-html-filter-reset-CM");
    const search = () => { query = queryInput ? queryInput.value.trim() : ""; if (resetBtn) resetBtn.hidden = query === ""; loadList(true); };
    const goBtn = byId("tabulator-html-filter-go-CM"), filterForm = byId("tabulatorFilterForm-CM");
    if (goBtn) goBtn.addEventListener("click", (e) => { e.preventDefault(); search(); });
    if (filterForm) filterForm.addEventListener("submit", (e) => { e.preventDefault(); search(); });
    if (resetBtn) resetBtn.addEventListener("click", (e) => { e.preventDefault(); if (queryInput) queryInput.value = ""; search(); });

    /* ---------- deleted ------------------------------------------------------ */
    const deletedBox = root.querySelector("[data-adp-deleted]");
    const deletedCountEl = root.querySelector("[data-adp-deleted-count]");
    const deletedRows = root.querySelector("[data-adp-deleted-rows]");
    const deletedLoading = root.querySelector("[data-adp-deleted-loading]");
    const paintDeleted = () => {
        const n = Number(deletedCounts[active]) || 0;
        if (deletedCountEl) deletedCountEl.textContent = String(n);
        if (deletedBox) { deletedBox.hidden = n === 0; if (n === 0) deletedBox.open = false; }
    };
    const loadDeleted = () => {
        const channel = active;
        if (!deletedRows) return Promise.resolve();
        if (deletedLoading) deletedLoading.hidden = false;
        return fetchList(channel, 1, 2, true).then((res) => {
            if (channel !== active) return;
            const rows = Array.isArray(res.data) ? res.data : [];
            deletedCounts[channel] = Number(res.total_rows) || rows.length;
            deletedLoaded[channel] = true;
            deletedRows.innerHTML = rows.map((r) => {
                const subject = subjectOf(channel, r);
                return `<div class="adp-vrow adp-vrow--note"><span><span class="adp-vrow__text" title="${esc(subject)}">${esc(subject)}</span><span class="adp-vrow__meta">${esc(r.created_by || "")}${r.deleted_at ? ` · deleted ${esc(r.deleted_at)}` : ""}</span></span><button type="button" class="adp-btn adp-btn--ghost adp-btn--sm restore_btn" data-id="${esc(r.id)}" data-adp-subject="${esc(subject)}">${svg(PATHS.undo, 13)}Restore</button></div>`;
            }).join("");
            if (deletedLoading) deletedLoading.hidden = true;
            paintDeleted();
        }).catch(() => { if (deletedLoading) deletedLoading.hidden = true; });
    };
    if (deletedBox) deletedBox.addEventListener("toggle", () => { if (deletedBox.open && !deletedLoaded[active]) loadDeleted(); });

    /* ---------- channel tabs ------------------------------------------------ */
    const tabs = Array.from(root.querySelectorAll("[data-adp-channel]"));
    const forms = { email: byId("sendEmailForm"), sms: byId("smsSMSForm"), letter: byId("addLetterForm") };
    const setChannel = (channel, remember) => {
        if (!CH[channel]) channel = "email";
        active = channel;
        tabs.forEach((b) => { const on = b.getAttribute("data-adp-channel") === channel; b.classList.toggle("on", on); b.setAttribute("aria-pressed", on ? "true" : "false"); });
        Object.keys(forms).forEach((k) => { if (forms[k]) forms[k].hidden = k !== channel; });
        if (sentTitle) sentTitle.textContent = CH[channel].title;
        if (queryInput) queryInput.placeholder = `Search sent ${CH[channel].plural}…`;
        if (deletedRows) deletedRows.innerHTML = "";
        deletedLoaded[channel] = false;
        if (deletedBox) deletedBox.open = false;
        paintDeleted();
        if (typeof applyWide === "function") applyWide();
        loadList(true);
        if (remember && window.history && window.history.replaceState) window.history.replaceState(null, "", "#" + channel);
    };
    tabs.forEach((b) => b.addEventListener("click", (e) => { e.preventDefault(); setChannel(b.getAttribute("data-adp-channel"), true); }));

    /* ---------- letter full width (the form takes the row; remembered per browser) --- */
    const grid = root.querySelector(".adp-comms-grid");
    const wideBtn = root.querySelector("[data-adp-wide-toggle]");
    const WIDE_KEY = "adp.comm.letterWide";
    let wide = false;
    try { wide = window.localStorage.getItem(WIDE_KEY) === "1"; } catch (e) { wide = false; }
    const applyWide = () => {
        const on = wide && active === "letter";
        if (grid) grid.classList.toggle("adp-comms-grid--wide", on);
        if (wideBtn) {
            wideBtn.setAttribute("aria-pressed", wide ? "true" : "false");
            wideBtn.classList.toggle("on", wide);
            setText(wideBtn, "[data-adp-wide-label]", wide ? "Two columns" : "Full width");
            wideBtn.setAttribute("title", wide ? "Put the sent letters back beside the form" : "Give the letter the full width");
        }
    };
    if (wideBtn) wideBtn.addEventListener("click", (e) => {
        e.preventDefault();
        wide = !wide;
        try { window.localStorage.setItem(WIDE_KEY, wide ? "1" : "0"); } catch (err) { /* per-browser convenience only */ }
        applyWide();
    });

    /* ---------- email -------------------------------------------------------- */
    const emailForm = forms.email;
    const resetters = {};
    if (emailForm) {
        const ed = editorOf("mailEditor");
        const subjectInput = byId("subject");
        const smtpSel = byId("comon_smtp_id");
        const templateSel = byId("email_template_id");
        const sendBtn = byId("sendEmailBtn");
        const input = byId("sendMailsDocument");
        const fileList = emailForm.querySelector("[data-adp-files]");
        const files = [];
        const fill = (text) => String(text || "")
            .replace(/\{name\}/g, emailForm.getAttribute("data-adp-fill-name") || "")
            .replace(/\{ref\}/g, emailForm.getAttribute("data-adp-fill-ref") || "")
            .replace(/\{course\}/g, emailForm.getAttribute("data-adp-fill-course") || "");
        const paintFiles = () => {
            if (!fileList) return;
            fileList.innerHTML = files.map((f, i) => `<span class="adp-tag">${svg(PATHS.file, 12)}<span>${esc(f.name)}</span><small class="adp-mono">${esc(fmtSize(f.size))}</small><button type="button" class="adp-tag-x" data-adp-file-remove="${i}" aria-label="Remove ${esc(f.name)}">${svg(PATHS.x, 12)}</button></span>`).join("");
            fileList.hidden = files.length === 0;
        };
        if (input) input.addEventListener("change", () => {
            Array.from(input.files || []).forEach((f) => { if (files.length < 10) files.push(f); });
            input.value = "";
            paintFiles();
        });
        if (fileList) fileList.addEventListener("click", (e) => {
            const b = e.target.closest("[data-adp-file-remove]");
            if (!b) return;
            files.splice(Number(b.getAttribute("data-adp-file-remove")), 1);
            paintFiles();
        });
        const reset = () => {
            clearErrors(emailForm);
            if (subjectInput) subjectInput.value = "";
            if (templateSel) templateSel.value = "";
            if (smtpSel) smtpSel.selectedIndex = 0;
            if (ed) ed.clear();
            files.length = 0;
            paintFiles();
        };
        resetters.email = reset;
        if (templateSel) templateSel.addEventListener("change", () => {
            clearErrors(emailForm);
            const id = templateSel.value;
            if (!id) { if (ed) ed.clear(); return; }
            axios.post(route("admission.communication.get.mail.template"), { emailTemplateID: id }, { headers: csrf })
                .then((res) => {
                    const row = (res.data && res.data.row) || {};
                    if (ed) ed.set(row.description ? fill(row.description) : "");
                    if (subjectInput && !subjectInput.value.trim() && row.email_title) subjectInput.value = fill(row.email_title);
                })
                .catch(() => showWarning("Could not load the template", "Try again, or write the email by hand."));
        });
        if (ed) ed.onChange(() => { const slot = emailForm.querySelector(".error-body"); if (slot) slot.textContent = ""; ed.root.classList.remove("err"); });
        emailForm.addEventListener("submit", (e) => {
            e.preventDefault();
            clearErrors(emailForm);
            const errors = {};
            if (!subjectInput || !subjectInput.value.trim()) errors.subject = "Subject is required.";
            if (!ed || ed.isEmpty()) errors.body = "Write the email.";
            if (Object.keys(errors).length) { paintErrors(emailForm, errors); return; }
            busy(sendBtn, true);
            const fd = new FormData(emailForm);
            fd.delete("documents[]");
            files.forEach((f) => fd.append("documents[]", f, f.name));
            fd.set("body", ed.html());
            const from = selectedText(smtpSel), to = emailForm.getAttribute("data-adp-to") || "the applicant";
            axios.post(route("admission.communication.send.mail"), fd, { headers: csrf })
                .then((res) => {
                    busy(sendBtn, false);
                    const n = Number(res.data && res.data.attachments) || 0;
                    reset();
                    showSuccess("Email sent", `From ${from} to ${to}${n ? ` with ${n} attachment${n > 1 ? "s" : ""}` : ""}.`);
                    if (active === "email") loadList(true);
                })
                .catch((err) => {
                    busy(sendBtn, false);
                    const errors = err.response && err.response.status === 422 ? (err.response.data.errors || {}) : null;
                    if (errors) paintErrors(emailForm, errors);
                    else showWarning("Something went wrong", (err.response && err.response.data && err.response.data.message) || "The email could not be sent. Try again, or contact the administrator.");
                });
        });
    }

    /* ---------- SMS ---------------------------------------------------------- */
    const smsForm = forms.sms;
    if (smsForm) {
        const ta = byId("smsTextArea");
        const subjectInput = byId("sms_subject");
        const templateSel = byId("sms_template_id");
        const sendBtn = byId("sendSMSBtn");
        const counter = smsForm.querySelector("[data-adp-sms-count]");
        const hint = smsForm.querySelector("[data-adp-sms-hint]");
        const segmentsOf = (text) => Math.max(1, Math.ceil(String(text || "").length / 160));
        const paintCounter = () => {
            if (!ta) return;
            const chars = ta.value.length, segs = segmentsOf(ta.value);
            if (counter) { counter.textContent = `${chars} / ${segs}`; counter.classList.toggle("many", chars > 160); }
            if (hint) hint.textContent = `160 characters per segment; longer messages are billed as ${segs > 1 ? segs : "multiple"} segments.`;
            const max = Number(ta.getAttribute("maxlength")) || 0;
            const slot = smsForm.querySelector(".error-sms");
            if (slot) slot.textContent = max > 0 && chars >= max ? "Maximum character limit reached. Make the text shorter or contact the administrator." : "";
        };
        if (ta) { ta.addEventListener("input", paintCounter); ta.addEventListener("keyup", paintCounter); }
        const reset = () => {
            clearErrors(smsForm);
            if (subjectInput) subjectInput.value = "";
            if (templateSel) templateSel.value = "";
            if (ta) ta.value = "";
            paintCounter();
        };
        resetters.sms = reset;
        if (templateSel) templateSel.addEventListener("change", () => {
            clearErrors(smsForm);
            const id = templateSel.value;
            if (!id) { if (ta) ta.value = ""; paintCounter(); return; }
            axios.post(route("admission.communication.get.sms.template"), { smsTemplateId: id }, { headers: csrf })
                .then((res) => {
                    const row = (res.data && res.data.row) || {};
                    if (ta) ta.value = row.description || "";
                    if (subjectInput && !subjectInput.value.trim() && row.sms_title) subjectInput.value = row.sms_title;
                    paintCounter();
                })
                .catch(() => showWarning("Could not load the template", "Try again, or write the message by hand."));
        });
        smsForm.addEventListener("submit", (e) => {
            e.preventDefault();
            clearErrors(smsForm);
            const errors = {};
            if (!subjectInput || !subjectInput.value.trim()) errors.subject = "Subject is required.";
            if (!ta || !ta.value.trim()) errors.sms = "Write the message.";
            if (Object.keys(errors).length) { paintErrors(smsForm, errors); return; }
            busy(sendBtn, true);
            const segs = segmentsOf(ta.value), to = smsForm.getAttribute("data-adp-to");
            axios.post(route("admission.communication.send.sms"), new FormData(smsForm), { headers: csrf })
                .then((res) => {
                    busy(sendBtn, false);
                    const msg = (res.data && res.data.message) || "";
                    reset();
                    if (/not sent/i.test(msg)) showWarning("SMS stored, not sent", msg);
                    else showSuccess("SMS sent", to ? `To ${to} (${segs} segment${segs > 1 ? "s" : ""}).` : msg);
                    if (active === "sms") loadList(true);
                })
                .catch((err) => {
                    busy(sendBtn, false);
                    const errors = err.response && err.response.status === 422 ? (err.response.data.errors || {}) : null;
                    if (errors) paintErrors(smsForm, errors);
                    else showWarning("Something went wrong", (err.response && err.response.data && err.response.data.message) || "The SMS could not be sent. Try again, or contact the administrator.");
                });
        });
        paintCounter();
    }

    /* ---------- letter ------------------------------------------------------- */
    const letterForm = forms.letter;
    if (letterForm) {
        const ed = editorOf("letterEditor");
        const setSel = byId("letter_set_id");
        const smtpSel = byId("letter_comon_smtp_id");
        const signSel = byId("signatory_id");
        const dateInput = byId("issued_date");
        const sendBtn = byId("sendLetterBtn");
        const area = letterForm.querySelector("[data-adp-letter-body]");
        const tagsBtn = letterForm.querySelector("[data-adp-tags-toggle]");
        const tagsPop = letterForm.querySelector("[data-adp-tags-pop]");
        const closeTags = () => { if (tagsPop) tagsPop.hidden = true; if (tagsBtn) tagsBtn.setAttribute("aria-expanded", "false"); };
        const reset = () => {
            clearErrors(letterForm);
            if (setSel) setSel.value = "";
            if (signSel) signSel.value = "";
            if (smtpSel) smtpSel.selectedIndex = 0;
            if (dateInput) dateInput.value = letterForm.getAttribute("data-adp-today") || "";
            if (ed) ed.clear();
            if (area) area.hidden = true;
            closeTags();
        };
        resetters.letter = reset;
        if (setSel) setSel.addEventListener("change", () => {
            clearErrors(letterForm);
            const id = Number(setSel.value) || 0;
            if (!id) { if (area) area.hidden = true; if (ed) ed.clear(); return; }
            axios.post(route("admission.communication.get.letter.set"), { letterSetId: id }, { headers: csrf })
                .then((res) => {
                    const row = (res.data && res.data.res) || {};
                    if (ed) ed.set(row.description || "");
                    if (area) area.hidden = false;
                })
                .catch(() => showWarning("Could not load the letter set", "Try again, or contact the administrator."));
        });
        if (tagsBtn && tagsPop && ed) {
            /* A tag goes in where the caret is. The caret's range is remembered whenever the
               selection sits inside the editor, because opening the popover moves focus. */
            let caret = null;
            document.addEventListener("selectionchange", () => {
                const sel = document.getSelection();
                if (sel && sel.rangeCount && ed.body.contains(sel.anchorNode)) caret = sel.getRangeAt(0).cloneRange();
            });
            const insertTag = (tag) => {
                ed.focus();
                const sel = document.getSelection();
                if (!sel) return;
                sel.removeAllRanges();
                if (caret && ed.body.contains(caret.startContainer)) sel.addRange(caret);
                else { const end = document.createRange(); end.selectNodeContents(ed.body); end.collapse(false); sel.addRange(end); }
                let done = false;
                try { done = document.execCommand("insertText", false, tag); } catch (err) { done = false; }
                if (!done && sel.rangeCount) {
                    const range = sel.getRangeAt(0);
                    range.deleteContents();
                    const node = document.createTextNode(tag);
                    range.insertNode(node);
                    range.setStartAfter(node);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
                caret = sel.rangeCount ? sel.getRangeAt(0).cloneRange() : null;
                ed.body.dispatchEvent(new Event("input", { bubbles: true }));
            };
            /* keep the editor's focus and selection while the popover is used */
            tagsBtn.addEventListener("mousedown", (e) => e.preventDefault());
            tagsPop.addEventListener("mousedown", (e) => { if (e.target.closest("[data-adp-tag]")) e.preventDefault(); });
            tagsBtn.addEventListener("click", (e) => {
                e.preventDefault();
                const open = tagsPop.hidden;
                tagsPop.hidden = !open;
                tagsBtn.setAttribute("aria-expanded", open ? "true" : "false");
            });
            tagsPop.addEventListener("click", (e) => {
                const item = e.target.closest("[data-adp-tag]");
                if (!item) return;
                e.preventDefault();
                insertTag(item.getAttribute("data-adp-tag"));
                const label = tagsBtn.querySelector("[data-adp-tags-label]");
                if (label) { label.textContent = "Inserted"; window.setTimeout(() => { label.textContent = "Tags"; }, 1200); }
            });
            document.addEventListener("mousedown", (e) => { if (!tagsPop.hidden && !e.target.closest("[data-adp-tags-pop], [data-adp-tags-toggle]")) closeTags(); });
            document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeTags(); });
        }
        if (ed) ed.onChange(() => { const slot = letterForm.querySelector(".error-letter_body"); if (slot) slot.textContent = ""; ed.root.classList.remove("err"); });
        letterForm.addEventListener("submit", (e) => {
            e.preventDefault();
            clearErrors(letterForm);
            const errors = {};
            if (!dateInput || !dateInput.value) errors.issued_date = "Issued date is required.";
            if (!setSel || !setSel.value) errors.letter_set_id = "Choose a letter.";
            else if (!ed || ed.isEmpty()) errors.letter_body = "The letter body cannot be empty.";
            if (Object.keys(errors).length) { paintErrors(letterForm, errors); return; }
            busy(sendBtn, true);
            const fd = new FormData(letterForm);
            fd.set("letter_body", ed.html());
            const from = selectedText(smtpSel);
            axios.post(route("admission.communication.send.letter"), fd, { headers: csrf })
                .then(() => {
                    busy(sendBtn, false);
                    reset();
                    showSuccess("Letter sent", `Generated as a PDF and emailed from ${from}.`);
                    if (active === "letter") loadList(true);
                })
                .catch((err) => {
                    busy(sendBtn, false);
                    const errors = err.response && err.response.status === 422 ? (err.response.data.errors || {}) : null;
                    if (errors) paintErrors(letterForm, errors);
                    else showWarning("Something went wrong", (err.response && err.response.data && err.response.data.message) || "The letter could not be generated. Try again, or contact the administrator.");
                });
        });
    }
    root.addEventListener("click", (e) => {
        const btn = e.target.closest("[data-adp-compose-reset]");
        if (!btn) return;
        e.preventDefault();
        const form = btn.closest("form");
        const key = form ? form.getAttribute("data-adp-compose") : "";
        if (resetters[key]) resetters[key]();
    });

    /* ---------- view dialog -------------------------------------------------- */
    const viewEl = byId("viewCommunicationModal");
    const paintView = (channel, row, detail) => {
        if (!viewEl) return;
        const d = Object.assign({}, row || {}, detail || {});
        setText(viewEl, "[data-adp-view-title]", subjectOf(channel, d));
        const meta = [CH[channel].noun.toUpperCase()].concat(metaOf(channel, d));
        if (channel === "email" && d.template) meta.push(`template ${d.template}`);
        setText(viewEl, "[data-adp-view-meta]", meta.join(" · "));
        const filesEl = viewEl.querySelector("[data-adp-view-files]");
        let files = [];
        if (channel === "email" && Array.isArray(d.attachments)) files = d.attachments;
        if (channel === "letter" && Number(d.docurl) > 0) files = [{ id: d.docurl, name: d.document_name || "Letter PDF" }];
        if (filesEl) {
            filesEl.innerHTML = files.map((f) => `<button type="button" class="adp-tag adp-tag--btn downloadDoc" data-id="${esc(f.id)}" title="Download">${svg(PATHS.file, 12)}<span>${esc(f.name)}</span>${svg(PATHS.download, 12)}</button>`).join("");
            filesEl.hidden = files.length === 0;
        }
        const bodyEl = viewEl.querySelector("[data-adp-view-body]");
        if (bodyEl) {
            bodyEl.classList.toggle("adp-view-comm__body--text", channel !== "email");
            if (channel === "email") bodyEl.innerHTML = typeof d.body === "string" ? d.body : (d.excerpt ? esc(d.excerpt) : "");
            else if (channel === "sms") bodyEl.textContent = typeof d.sms === "string" && d.sms.length > 43 ? d.sms : (d.sms || d.excerpt || "");
            else bodyEl.textContent = "Letter PDF generated from the letter set and attached.";
        }
        const actsEl = viewEl.querySelector("[data-adp-view-actions]");
        if (actsEl) actsEl.innerHTML = channel === "letter" && Number(d.docurl) > 0 ? `<button type="button" class="adp-btn adp-btn--primary downloadDoc" data-id="${esc(d.docurl)}">${svg(PATHS.download, 14)}Download PDF</button>` : "";
    };
    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".view_btn");
        if (!btn) return;
        e.preventDefault();
        const channel = active, id = btn.getAttribute("data-id");
        const row = state[channel].rows.find((r) => String(r.id) === String(id)) || { id };
        paintView(channel, row, null);
        if (viewModal) viewModal.show();
        if (!CH[channel].show) return;
        axios.post(route(CH[channel].show), { recordId: id }, { headers: csrf })
            .then((res) => { if (active === channel) paintView(channel, row, (res.data && res.data.row) || null); })
            .catch(() => {});
    });
    if (viewEl) viewEl.addEventListener("hide.tw.modal", () => {
        setText(viewEl, "[data-adp-view-title]", "View communication");
        setText(viewEl, "[data-adp-view-meta]", "");
        const b = viewEl.querySelector("[data-adp-view-body]"); if (b) b.innerHTML = "";
        const f = viewEl.querySelector("[data-adp-view-files]"); if (f) { f.innerHTML = ""; f.hidden = true; }
        const a = viewEl.querySelector("[data-adp-view-actions]"); if (a) a.innerHTML = "";
    });

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
            confirmAction({ tone: "danger", icon: PATHS.trash, title: `Delete this ${CH[active].noun.toLowerCase()}?`, item: (del.getAttribute("data-adp-subject") || "").slice(0, 80), text: "Soft-deleted; you can restore it from the deleted list.", action: CH[active].del, recordId: del.getAttribute("data-id"), label: "Delete" });
            return;
        }
        const res = e.target.closest(".restore_btn");
        if (res) {
            e.preventDefault();
            confirmAction({ tone: "warn", icon: PATHS.undo, title: `Restore this ${CH[active].noun.toLowerCase()}?`, item: (res.getAttribute("data-adp-subject") || "").slice(0, 80), text: `It goes back to the sent ${CH[active].plural}.`, action: CH[active].res, recordId: res.getAttribute("data-id"), label: "Restore" });
        }
    });
    if (confirmEl) {
        confirmEl.addEventListener("click", (e) => {
            const agree = e.target.closest(".agreeWith");
            if (!agree) return;
            e.preventDefault();
            const spec = ACTIONS[agree.getAttribute("data-status")];
            const recordid = agree.getAttribute("data-recordid");
            const applicant = agree.getAttribute("data-applicant") || applicantId;
            if (!spec) { confirmModal.hide(); return; }
            confirmEl.querySelectorAll("button").forEach((b) => b.setAttribute("disabled", "disabled"));
            axios({ method: spec.method, url: route(spec.name), data: { applicant, recordid }, headers: csrf })
                .then(() => {
                    confirmModal.hide();
                    const c = CH[spec.channel];
                    if (spec.kind === "delete") deletedCounts[spec.channel] = (Number(deletedCounts[spec.channel]) || 0) + 1;
                    else deletedCounts[spec.channel] = Math.max(0, (Number(deletedCounts[spec.channel]) || 0) - 1);
                    if (spec.channel === active) {
                        paintDeleted();
                        loadList(true);
                        if (deletedLoaded[active] || (deletedBox && deletedBox.open)) loadDeleted();
                    }
                    showSuccess(spec.kind === "delete" ? `${c.noun} deleted` : `${c.noun} restored`, spec.kind === "delete" ? "It is in the deleted list and can be restored." : `It is back in the sent ${c.plural}.`);
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
    const printBtn = byId("tabulator-print-CM");
    if (printBtn) printBtn.addEventListener("click", (e) => { e.preventDefault(); window.print(); });
    const exportRows = () => fetchList(active, 1, 1, true).then((res) => {
        const rows = Array.isArray(res.data) ? res.data : [];
        if (active === "email") return [["#", "Subject", "From", "Template", "Attachments", "Issued by", "When"]].concat(rows.map((r, i) => [i + 1, r.subject || "", r.smtp || "", r.template || "", r.attachments || 0, r.created_by || "", r.created_at || ""]));
        if (active === "sms") return [["#", "Subject", "Template", "SMS", "Issued by", "When"]].concat(rows.map((r, i) => [i + 1, r.subject || "", r.template || "", r.excerpt || r.sms || "", r.created_by || "", r.created_at || ""]));
        return [["#", "Type", "Subject", "Signatory", "Issued date", "Issued by", "When"]].concat(rows.map((r, i) => [i + 1, r.letter_type || "", r.letter_title || "", r.signatory_name || "", r.issued_date || "", r.created_by || "", r.created_at || ""]));
    });
    const download = (blob, name) => { const url = URL.createObjectURL(blob); const a = document.createElement("a"); a.href = url; a.download = name; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url); };
    const csvBtn = byId("tabulator-export-csv-CM");
    if (csvBtn) csvBtn.addEventListener("click", (e) => {
        e.preventDefault();
        exportRows().then((aoa) => download(new Blob([xlsx.utils.sheet_to_csv(xlsx.utils.aoa_to_sheet(aoa))], { type: "text/csv;charset=utf-8" }), `${CH[active].file}.csv`));
    });
    const xlsxBtn = byId("tabulator-export-xlsx-CM");
    if (xlsxBtn) xlsxBtn.addEventListener("click", (e) => {
        e.preventDefault();
        exportRows().then((aoa) => { const wb = xlsx.utils.book_new(); xlsx.utils.book_append_sheet(wb, xlsx.utils.aoa_to_sheet(aoa), `Applicant ${CH[active].noun}`); xlsx.writeFile(wb, `${CH[active].file}.xlsx`); });
    });

    setChannel((window.location.hash || "").replace("#", "") || "email", false);
})();
