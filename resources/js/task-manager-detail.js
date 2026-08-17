/*
    Task Manager — student list (detail) screen.

    Replaces the old jQuery/Tabulator controller. The whole screen runs on:

      * one `fetch` helper,
      * one delegated click listener dispatching through the ACTIONS map,
      * one delegated submit listener dispatching through the FORMS map,
      * one open/close engine for every dialog.

    Adding a row action means adding one entry to ACTIONS and one `data-tkm-act`
    attribute — not a new binding, a new modal instance and a new teardown
    listener. Heavy dependencies (CKEditor, html2canvas) load on demand so a
    plain upload-only task never downloads them.

    Nothing here touches jQuery, Tabulator, Dropzone, TomSelect or the theme's
    modal/dropdown JS.
*/

const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

const esc = (value) =>
    String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");

/* ------------------------------------------------------------------ */
/* Transport                                                           */
/* ------------------------------------------------------------------ */

function toFormData(input) {
    if (input instanceof FormData) return input;
    const fd = new FormData();
    Object.entries(input || {}).forEach(([key, value]) => {
        if (Array.isArray(value)) value.forEach((v) => fd.append(`${key}[]`, v));
        else fd.append(key, value);
    });
    return fd;
}

/**
 * POST and resolve to { ok, status, data }. Never rejects on an HTTP error —
 * callers branch on `ok` / `status` instead of wrapping everything in a catch.
 */
async function post(url, payload) {
    let response;
    try {
        response = await fetch(url, {
            method: "POST",
            body: toFormData(payload),
            headers: { "X-CSRF-TOKEN": CSRF, "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
        });
    } catch (e) {
        return { ok: false, status: 0, data: {} };
    }

    let data = {};
    try {
        data = await response.json();
    } catch (e) {
        /* a redirect to the login page, or an empty body */
    }
    return { ok: response.ok, status: response.status, data };
}

async function getJson(url, params) {
    const query = new URLSearchParams(params).toString();
    const response = await fetch(query ? `${url}?${query}` : url, {
        headers: { "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
    });
    if (!response.ok) throw new Error(`Request failed (${response.status})`);
    return response.json();
}

/** GETs a spreadsheet and hands it to the browser as a download. */
async function download(url, params, filename) {
    const query = new URLSearchParams();
    Object.entries(params || {}).forEach(([key, value]) => {
        if (Array.isArray(value)) value.forEach((v) => query.append(`${key}[]`, v));
        else query.append(key, value);
    });

    const response = await fetch(`${url}?${query.toString()}`, {
        headers: { "X-CSRF-TOKEN": CSRF, "X-Requested-With": "XMLHttpRequest" },
    });
    if (!response.ok) throw new Error(`Download failed (${response.status})`);

    const blob = await response.blob();
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    link.remove();
    setTimeout(() => URL.revokeObjectURL(link.href), 4000);
}

/* ------------------------------------------------------------------ */
/* Chrome — row menus and their shared dismiss                        */
/* ------------------------------------------------------------------ */

function closePopovers(except = null) {
    $$(".tkm-pop.is-open").forEach((node) => {
        if (except && node === except) return;
        node.classList.remove("is-open", "tkm-pop--up");
        const trigger = $("[aria-expanded]", node);
        if (trigger) trigger.setAttribute("aria-expanded", "false");
    });
}

function togglePopover(node) {
    const open = node.classList.contains("is-open");
    closePopovers();
    if (open) return;

    node.classList.add("is-open");
    const trigger = $("[aria-expanded]", node);
    if (trigger) trigger.setAttribute("aria-expanded", "true");

    // Row menus near the bottom of the viewport open upwards instead.
    if (node.classList.contains("tkm-pop")) {
        const panel = $(".tkm-pop__panel", node);
        if (panel && node.getBoundingClientRect().bottom + panel.offsetHeight + 24 > window.innerHeight) {
            node.classList.add("tkm-pop--up");
        }
    }
}

/* ------------------------------------------------------------------ */
/* Dialogs                                                             */
/* ------------------------------------------------------------------ */

const modal = (name) => $(`[data-tkm-modal="${name}"]`);

function openModal(name) {
    const el = modal(name);
    if (!el) return null;
    closePopovers();
    el.hidden = false;
    return el;
}

function closeModal(el) {
    if (!el) return;
    el.hidden = true;
    const form = $("[data-tkm-form]", el);
    if (form) clearErrors(form);
}

function closeAllModals() {
    $$("[data-tkm-modal]").forEach(closeModal);
}

/** The single feedback card — success, warning and error all render through it. */
function feedback({ title, copy = "", tone = "ok", pill = "", reload = false, dismissMs = 0 }) {
    const el = openModal("feedback");
    if (!el) return;

    const crest = $("[data-tkm-feedback-crest]", el);
    crest.className = "tkm-modal__crest" + (tone === "warn" ? " tkm-modal__crest--warn" : tone === "bad" ? " tkm-modal__crest--bad" : "");
    crest.innerHTML =
        tone === "ok"
            ? '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.6"></circle><path d="m8.4 12.2 2.5 2.5 4.7-5"></path></svg>'
            : '<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.6"></circle><path d="M12 8v5"></path><path d="M12 16.2h.01"></path></svg>';

    $("[data-tkm-feedback-title]", el).textContent = title;
    $("[data-tkm-feedback-copy]", el).innerHTML = copy;

    const pillEl = $("[data-tkm-feedback-pill]", el);
    pillEl.hidden = !pill;
    if (pill) $("[data-tkm-feedback-pilltext]", el).textContent = pill;

    el.dataset.tkmReload = reload ? "1" : "";
    if (dismissMs > 0) {
        setTimeout(() => {
            if (!el.hidden) {
                closeModal(el);
                if (reload) window.location.reload();
            }
        }, dismissMs);
    }
}

let toastTimer = null;
function toast(message, bad = false) {
    let el = $(".tkm-toast");
    if (!el) {
        el = document.createElement("div");
        el.className = "tkm-toast";
        document.body.appendChild(el);
    }
    el.className = "tkm-toast" + (bad ? " tkm-toast--bad" : "");
    el.innerHTML = `<span class="tkm-toast__ico">${
        bad
            ? '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="8.5"></circle><path d="M12 8v5M12 16h.01"></path></svg>'
            : '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"></circle><path d="m8.5 12.2 2.4 2.4 4.6-5"></path></svg>'
    }</span><span>${esc(message)}</span>`;

    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.remove(), 3200);
}

function spin(button, busy) {
    if (!button) return;
    button.classList.toggle("is-busy", busy);
    button.disabled = busy;
}

function clearErrors(form) {
    $$("[data-tkm-error]", form).forEach((node) => (node.textContent = ""));
    $$(".tkm-field.is-invalid", form).forEach((node) => node.classList.remove("is-invalid"));
}

/** Paints Laravel's 422 payload onto the matching `[data-tkm-error]` slots. */
function showErrors(form, errors) {
    clearErrors(form);
    Object.entries(errors || {}).forEach(([key, messages]) => {
        const slot = $(`[data-tkm-error="${key}"]`, form);
        const text = Array.isArray(messages) ? messages[0] : messages;
        if (slot) {
            slot.textContent = text;
            slot.closest(".tkm-field")?.classList.add("is-invalid");
        } else {
            toast(text, true);
        }
    });
}

/* ------------------------------------------------------------------ */
/* Rich text — loaded only when a dialog that needs it is opened       */
/* ------------------------------------------------------------------ */

const editors = {};
let editorBuild = null;

async function getEditor(name) {
    if (editors[name]) return editors[name];

    const host = $(`[data-tkm-editor="${name}"]`);
    if (!host) return null;

    if (!editorBuild) editorBuild = import("@ckeditor/ckeditor5-build-decoupled-document");
    const { default: DecoupledEditor } = await editorBuild;

    const editor = await DecoupledEditor.create(host);
    host.closest(".tkm-editor")?.querySelector(".tkm-editor__toolbar")?.appendChild(editor.ui.view.toolbar.element);
    editors[name] = editor;
    return editor;
}

/* ------------------------------------------------------------------ */
/* Table                                                               */
/* ------------------------------------------------------------------ */

const configEl = $("[data-tkm-config]");
const config = configEl ? JSON.parse(configEl.textContent) : { routes: {}, flags: {} };
const isApplicant = config.phase === "Applicant";

const state = {
    page: 1,
    size: 25,
    sort: { field: "id", dir: "DESC" },
    rows: [],
    total: 0,
    lastPage: 1,
    /** Map of rowKey → row, so bulk actions can read ids without touching the DOM. */
    selected: new Map(),
    loading: false,
};

const rowKey = (row) => `${row.ids}:${row.student_task_id || 0}`;

function filters() {
    const form = $("[data-tkm-filters]");
    return {
        reg_or_ref: $("[name=reg_or_ref]", form).value.trim(),
        status: $("[name=status]", form).value,
        venue: $("[name=venue]", form).value,
        courses: $("[name=courses]", form).value,
    };
}

const CHIP_TONE = {
    Pending: "pending",
    "In Progress": "progress",
    Completed: "done",
    Canceled: "dead",
    Cancelled: "dead",
    Approved: "done",
    Rejected: "dead",
};

const chip = (label, fallback = "pending") =>
    label ? `<span class="tkm-chip tkm-chip--${CHIP_TONE[label] || fallback}">${esc(label)}</span>` : "";

const ICONS = {
    // Document buttons download the uploaded file, so they read as a download
    // rather than as a note — file/image outline with an arrow coming out.
    fileDown:
        '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M13.5 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8.5L13.5 3z"></path><path d="M13.5 3v5.5H19"></path><path d="M12 11.5v6M9.5 15l2.5 2.5 2.5-2.5"></path></svg>',
    imageDown:
        '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 12.5V6.5a2 2 0 0 0-2-2h-13a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h6"></path><circle cx="9" cy="9.5" r="1.4"></circle><path d="m3.8 15 3.7-3.7 2.8 2.8"></path><path d="M17 14v6.5M14.5 18l2.5 2.5 2.5-2.5"></path></svg>',
    kebab: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 6h.01M12 12h.01M12 18h.01"></path></svg>',
    upload: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V6M8 9.5 12 5.5l4 4"></path><path d="M4.5 15v3a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2v-3"></path></svg>',
    tick: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="8.5"></circle><path d="m8.5 12.2 2.4 2.4 4.6-5"></path></svg>',
    award: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="5"></circle><path d="M8.5 13.5 7 21l5-2.5L17 21l-1.5-7.5"></path></svg>',
    eye: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.8 12 5.8 21.5 12 21.5 12 18 18.2 12 18.2 2.5 12 2.5 12z"></path><circle cx="12" cy="12" r="2.6"></circle></svg>',
    mail: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3.5 6.5 8.5 6 8.5-6"></path></svg>',
    receipt: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h12v17l-3-1.6-3 1.6-3-1.6-3 1.6z"></path><path d="M9 8h6M9 12h6"></path></svg>',
    lock: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4.5" y="10.5" width="15" height="9.5" rx="2"></rect><path d="M8 10.5V7.8a4 4 0 0 1 8 0v2.7"></path></svg>',
    idcard: '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"></rect><circle cx="9" cy="11" r="2.2"></circle><path d="M5.8 16c.4-1.6 1.7-2.4 3.2-2.4s2.8.8 3.2 2.4M15 10h4M15 13.5h3"></path></svg>',
};

const popItem = (act, label, data = {}, tone = "") => {
    const attrs = Object.entries(data)
        .map(([key, value]) => ` data-tkm-${key}="${esc(value)}"`)
        .join("");
    const icon = { upload: ICONS.upload, complete: ICONS.tick, outcome: ICONS.award, docreq: ICONS.award, letter: ICONS.mail, receipt: ICONS.receipt, excuse: ICONS.eye, address: ICONS.eye, profile: ICONS.eye }[act] || ICONS.tick;
    return `<button type="button" class="tkm-pop__item${tone ? ` tkm-pop__item--${tone}` : ""}" data-tkm-act="${act}"${attrs}><span class="tkm-pop__ico">${icon}</span>${esc(label)}</button>`;
};

const rowAttrs = (row) =>
    Object.entries({ taskid: row.task_id, studentid: row.ids, phase: row.phase, studenttaskid: row.student_task_id || 0 })
        .map(([key, value]) => ` data-tkm-${key}="${esc(value)}"`)
        .join("");

/**
 * The kebab entries a row is entitled to.
 *
 * Mirrors the two branches of the old Tabulator formatter exactly: `Pending`
 * rows can be given an outcome and documents, `In Progress` rows cannot, and
 * the document-request entries differ between the two. Any other task status
 * gets no menu at all.
 */
function rowMenu(row) {
    const items = [];
    const base = { taskid: row.task_id, studentid: row.ids, phase: row.phase, studenttaskid: row.student_task_id || 0 };
    const request = row.student_document_request_form_id;
    const pending = row.task_status === "Pending";
    const inProgress = row.task_status === "In Progress";
    if (!pending && !inProgress) return items;

    const receipt = (orderId) =>
        `<a class="tkm-pop__item" target="_blank" rel="noopener" href="${esc(
            (config.routes.receipt || "").replace("__ID__", orderId)
        )}"><span class="tkm-pop__ico">${ICONS.receipt}</span>View receipt</a>`;
    const letterItem = () =>
        popItem("letter", "Generate requested document", { ...base, lettersetid: request.letter_set?.id ?? "" });

    if (pending) {
        if (row.has_task_status === "Yes") items.push(popItem("outcome", "Update outcome", base));
        if (row.has_task_upload === "Yes") items.push(popItem("upload", "Upload documents", base));

        if (request) {
            if (request.status === "Approved" && !request.letter_generated_count) items.push(letterItem());
            if (request.student_order_id) items.push(receipt(request.student_order_id));
            if (request.status !== "Approved") items.push(popItem("docreq", "Update task outcome", base));
            if (request.status === "Approved") items.push(popItem("complete", "Mark as complete", base, "green"));
        }
    } else {
        if (request) {
            items.push(letterItem());
            if (request.student_order_id) items.push(receipt(request.student_order_id));
            if (request.status === "Approved") items.push(popItem("complete", "Mark as complete", base, "green"));
        }
    }

    // Shared tail — identical in both branches of the old formatter.
    if (row.is_completable === 1) {
        if (row.task_address_request === "No" && row.task_excuse === "No" && !request) {
            items.push(popItem("complete", "Mark as complete", base, "green"));
        }
        if (row.task_address_request === "Yes") items.push(popItem("address", "View address update request", base));
        if (row.task_excuse === "Yes") items.push(popItem("excuse", "View excuse", base));
    }

    return items;
}

/**
 * Actions cell: the uploaded-document buttons and the kebab, nothing else.
 * The outer guard is the old formatter's — a row with no outcome, no upload,
 * nothing completable and no documents renders an empty cell.
 */
function rowActions(row) {
    const docs = row.task_documents || [];
    const eligible =
        row.has_task_status === "Yes" || row.has_task_upload === "Yes" || row.is_completable === 1 || docs.length > 0;
    if (!eligible) return "";

    // Each button fetches a signed URL for that document and opens it — the
    // same AJAX download the old `.downloadTaskDoc` link did.
    const chips = docs
        .map(
            (doc) =>
                `<button type="button" class="tkm-doc" title="Download ${esc(doc.name)}" aria-label="Download ${esc(
                    doc.name
                )}" data-tkm-act="download-doc" data-tkm-id="${esc(doc.id)}" data-tkm-phase="${esc(row.phase)}">${
                    doc.type === "image" ? ICONS.imageDown : ICONS.fileDown
                }</button>`
        )
        .join("");

    const items = rowMenu(row);
    const menu = items.length
        ? `<span class="tkm-pop" data-tkm-pop><button type="button" class="tkm-icobtn" title="More" data-tkm-act="pop-toggle" aria-expanded="false">${ICONS.kebab}</button><span class="tkm-pop__panel">${items.join("")}</span></span>`
        : "";

    return chips || menu ? `<span class="tkm-actions">${chips}${menu}</span>` : "";
}

/**
 * The ID-card and interview-unlock buttons live in the task status cell, not
 * the actions cell, and they are mutually exclusive — same as the old table.
 */
function statusButton(row) {
    if (config.flags.idCard) {
        return `<button type="button" class="tkm-icobtn tkm-icobtn--green" title="Download ID card" data-tkm-act="idcard"${rowAttrs(
            row
        )}>${ICONS.idcard}</button>`;
    }
    if (config.flags.interview && row.task_status === "Pending") {
        return `<button type="button" class="tkm-icobtn tkm-icobtn--gold" title="Unlock profile for interview" data-tkm-act="unlock"${rowAttrs(
            row
        )}>${ICONS.lock}</button>`;
    }
    return "";
}

function interviewCell(row) {
    const iv = row.interview || {};
    if (!iv.date && !iv.interviewer && !iv.result) return "<td>—</td>";
    const lines = [];
    if (iv.date) lines.push(esc(iv.date));
    if (iv.time) lines.push(esc(iv.time));
    if (iv.interviewer) lines.push(esc(iv.interviewer));
    if (iv.result) lines.push(chip(iv.result));
    const link =
        iv.interview_id > 0
            ? `<button type="button" class="tkm-pop__item" style="padding:6px 0;font-size:12.5px" data-tkm-act="view-profile" data-tkm-id="${esc(
                  iv.interview_id
              )}"><span class="tkm-pop__ico" style="width:22px;height:22px">${ICONS.eye}</span>View profile</button>`
            : "";
    return `<td class="tkm-td-nowrap">${lines[0] || ""}${lines
        .slice(1)
        .map((line) => `<span class="tkm-sub">${line}</span>`)
        .join("")}${link}</td>`;
}

function renderRows() {
    const tbody = $("[data-tkm-rows]");

    // Interview details only ever carried anything once a task had moved on,
    // so the old table showed that column on those two filters alone. The
    // actions column is dropped outright on interview tasks.
    const showInterview =
        config.flags.interview && ["In Progress", "Completed"].includes(filters().status);
    const showActions = !config.flags.interview;

    const interviewHead = $('[data-tkm-col="interview"]');
    if (interviewHead) interviewHead.hidden = !showInterview;
    const actionsHead = $('[data-tkm-col="actions"]');
    if (actionsHead) actionsHead.hidden = !showActions;

    const columns = 8 + (showInterview ? 1 : 0) + (showActions ? 1 : 0);

    if (!state.rows.length) {
        tbody.innerHTML = `<tr><td colspan="${columns}"><div class="tkm-empty"><div class="tkm-empty__title">No matching records</div><div class="tkm-empty__note">Try a different status, venue or course.</div></div></td></tr>`;
        return;
    }

    tbody.innerHTML = state.rows
        .map((row) => {
            const key = rowKey(row);
            const on = state.selected.has(key);
            const selectable = row.task_id > 0;
            const ref = isApplicant ? row.application_no : row.registration_no;
            const longName = !isApplicant && (String(row.first_name).length > 20 || String(row.last_name).length > 20);
            const request = row.student_document_request_form_id;

            // Only document-request rows carry a task type; every other row
            // leaves the cell empty, exactly as the old table did.
            const taskType = request
                ? `${esc(request.name)}${
                      request.student_order
                          ? `<span class="tkm-sub">${esc(request.student_order.invoice_number)}</span>${
                                request.student_order.payment_status === "Completed"
                                    ? `<span class="tkm-sub">Paid by ${esc(request.student_order.payment_method)}</span>`
                                    : ""
                            }`
                          : ""
                  }`
                : "";

            // Same three lines the old cell carried, in the same order.
            const meta = [
                row.task_created_by ? `By: ${esc(row.task_created_by)}` : "",
                row.task_created ? esc(row.task_created) : "",
                row.canceled_reason ? `Reason: ${esc(row.canceled_reason)}` : "",
            ]
                .filter(Boolean)
                .map((line) => `<span class="tkm-sub">${line}</span>`)
                .join("");

            return `<tr data-tkm-row="${esc(key)}"${on ? ' class="is-selected"' : ""}>
                <td>${
                    selectable
                        ? `<label class="tkm-check"><input type="checkbox" data-tkm-act="select-row" data-tkm-key="${esc(key)}"${
                              on ? " checked" : ""
                          } aria-label="Select ${esc(ref)}"><span class="tkm-check__box"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.4" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7"></path></svg></span></label>`
                        : ""
                }</td>
                <td class="tkm-td-nowrap"><a class="tkm-ref" href="${esc(row.url)}">${esc(ref)}</a>${
                row.outcome ? `<span class="tkm-sub">Outcome: ${esc(row.outcome)}</span>` : ""
            }</td>
                <td class="tkm-td-name${longName ? " tkm-sub--warn" : ""}">${esc(
                `${row.first_name ?? ""} ${row.last_name ?? ""}`.trim()
            )}</td>
                <td class="tkm-td-course">${esc(row.course)}${row.semester ? `<span class="tkm-sub">${esc(row.semester)}</span>` : ""}</td>
                <td class="tkm-td-venue">${esc(row.venue_name)}</td>
                <td class="tkm-td-nowrap">${chip(row.status_id, "neutral")}</td>
                ${showInterview ? interviewCell(row) : ""}
                <td class="tkm-td-nowrap"><span class="tkm-taskstate"><span>${chip(
                    row.task_status
                )}${meta}</span>${statusButton(row)}</span></td>
                <td class="tkm-td-type">${taskType}</td>
                ${showActions ? `<td class="tkm-td-act">${rowActions(row)}</td>` : ""}
            </tr>`;
        })
        .join("");
}

function renderFooter() {
    const from = state.total === 0 ? 0 : (state.page - 1) * state.size + 1;
    const to = Math.min(state.page * state.size, state.total);
    $("[data-tkm-showing]").innerHTML = `Showing <strong>${from}–${to}</strong> of ${state.total}`;
    $("[data-tkm-records]").textContent = state.total;

    const pager = $("[data-tkm-pager]");
    const last = Math.max(1, state.lastPage);
    const first = state.page <= 1;
    const end = state.page >= last;

    const step = (page, label, disabled) =>
        `<button type="button" class="tkm-icobtn" data-tkm-act="page" data-tkm-page="${page}"${disabled ? " disabled" : ""}>${label}</button>`;

    // A window of five around the current page keeps the control the width the
    // design allows, however many pages the filter turns up.
    const windowStart = Math.max(1, Math.min(state.page - 2, last - 4));
    const windowEnd = Math.min(last, windowStart + 4);
    const numbers = [];
    for (let page = windowStart; page <= windowEnd; page += 1) {
        numbers.push(
            `<button type="button" class="tkm-pager__num${page === state.page ? " is-on" : ""}" data-tkm-act="page" data-tkm-page="${page}">${page}</button>`
        );
    }
    if (windowStart > 1) numbers.unshift('<span class="tkm-pager__gap">…</span>');
    if (windowEnd < last) numbers.push('<span class="tkm-pager__gap">…</span>');

    pager.innerHTML = [
        step(1, '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 6l-6 6 6 6M11 6l-6 6 6 6"></path></svg>', first),
        step(state.page - 1, '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 6l-6 6 6 6"></path></svg>', first),
        numbers.join(""),
        step(state.page + 1, '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.5 6l6 6-6 6"></path></svg>', end),
        step(last, '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 6l6 6-6 6M13 6l6 6-6 6"></path></svg>', end),
    ].join("");
}

function renderSelection() {
    const count = state.selected.size;
    const label = count ? `${count} ${count === 1 ? "student" : "students"} selected` : "No students selected";
    $$("[data-tkm-sellabel]").forEach((node) => (node.textContent = label));

    const bar = $("[data-tkm-selbar]");
    if (bar) bar.hidden = count === 0;

    const all = $("[data-tkm-act=select-all]");
    if (all) {
        const selectable = state.rows.filter((row) => row.task_id > 0);
        all.checked = selectable.length > 0 && selectable.every((row) => state.selected.has(rowKey(row)));
        all.indeterminate = !all.checked && count > 0;
    }
}

function renderHeader() {
    const active = filters();
    const venueName = $("[name=venue] option:checked", $("[data-tkm-filters]"))?.textContent?.trim() || "all venues";
    $("[data-tkm-countlabel]").textContent = active.status;
    $("[data-tkm-counttotal]").textContent = state.total;
    $("[data-tkm-scope]").textContent = `${active.status} · ${venueName.toLowerCase()}`;
}

async function load({ resetPage = false } = {}) {
    if (resetPage) state.page = 1;

    const wrap = $("[data-tkm-tablewrap]");
    wrap.classList.add("is-loading");
    state.loading = true;

    try {
        const payload = await getJson(config.routes.list, {
            ...filters(),
            task_id: config.taskId,
            phase: config.phase,
            page: state.page,
            size: state.size,
            "sorters[0][field]": state.sort.field,
            "sorters[0][dir]": state.sort.dir,
        });

        state.rows = payload.data || [];
        state.total = payload.total || 0;
        state.lastPage = Number(payload.last_page) || 1;

        // A row that scrolled out of the result set can no longer be acted on.
        const visible = new Set(state.rows.map(rowKey));
        Array.from(state.selected.keys()).forEach((key) => {
            if (!visible.has(key)) state.selected.delete(key);
        });

        renderRows();
        renderFooter();
        renderSelection();
        renderHeader();
    } catch (e) {
        $("[data-tkm-rows]").innerHTML = `<tr><td colspan="11"><div class="tkm-empty"><div class="tkm-empty__title">Could not load the list</div><div class="tkm-empty__note">${esc(
            e.message
        )}</div></div></td></tr>`;
    } finally {
        wrap.classList.remove("is-loading");
        state.loading = false;
    }
}

const selectedIds = () => Array.from(state.selected.values()).map((row) => row.ids);
const selectedRows = () => Array.from(state.selected.values());

/* ------------------------------------------------------------------ */
/* File pickers (upload + Pearson dialogs share one implementation)    */
/* ------------------------------------------------------------------ */

const MAX_BYTES = 5 * 1024 * 1024;
const MAX_FILES = 10;
const BAD_NAME = /[`!@#$%^&*+=\[\]{};':"\\|,<>\/?~]/;

const picked = new WeakMap();

function renderFileList(form) {
    const list = $("[data-tkm-filelist]", form);
    const files = picked.get(form) || [];
    list.innerHTML = files
        .map(
            (file, index) =>
                `<span class="tkm-file"><span class="tkm-file__name">${esc(file.name)}</span><span class="tkm-file__size">${(
                    file.size / 1024
                ).toFixed(0)} KB</span><button type="button" class="tkm-file__x" data-tkm-act="drop-file" data-tkm-index="${index}" aria-label="Remove ${esc(
                    file.name
                )}"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"></path></svg></button></span>`
        )
        .join("");
}

function addFiles(form, incoming) {
    const multiple = $("[data-tkm-files]", form).multiple;
    const current = multiple ? picked.get(form) || [] : [];

    Array.from(incoming).forEach((file) => {
        if (BAD_NAME.test(file.name)) return toast(`"${file.name}" was skipped — the file name contains special characters.`, true);
        if (file.size > MAX_BYTES) return toast(`"${file.name}" was skipped — over 5 MB.`, true);
        if (current.length >= (multiple ? MAX_FILES : 1)) return toast(`Only ${multiple ? MAX_FILES : 1} file${multiple ? "s" : ""} at a time.`, true);
        current.push(file);
    });

    picked.set(form, current);
    renderFileList(form);
}

function resetForm(form) {
    form.reset();
    picked.set(form, []);
    const list = $("[data-tkm-filelist]", form);
    if (list) list.innerHTML = "";
    clearErrors(form);
}

/* ------------------------------------------------------------------ */
/* Document-request payloads                                           */
/* ------------------------------------------------------------------ */

/** Fills the shared request summary block in the docreq / letter dialogs. */
function paintRequest(scope, request) {
    if (!request) return;
    const set = (sel, value) => {
        const node = $(sel, scope);
        if (node) node.innerHTML = value;
    };
    set("[data-tkm-request-name]", esc(request.name));
    set("[data-tkm-request-desc]", esc(request.description ?? ""));
    set("[data-tkm-request-service]", esc(request.service_type ?? ""));
    set("[data-tkm-request-when]", esc(request.created_at_human ?? ""));

    const status = $("[data-tkm-request-status]", scope);
    if (status) {
        status.className = `tkm-chip tkm-chip--${CHIP_TONE[request.status] || "pending"}`;
        status.textContent = request.status ?? "";
    }
}

const requestFor = (row) => row?.student_document_request_form_id ?? null;
const rowFor = (button) => {
    const key = `${button.dataset.tkmStudentid}:${button.dataset.tkmStudenttaskid || 0}`;
    return state.rows.find((row) => rowKey(row) === key) || state.rows.find((row) => String(row.ids) === String(button.dataset.tkmStudentid));
};

function docRequestTemplate(name, request) {
    return `Dear <b>${esc(name)}</b>,<br/><br/>
We hope this message finds you well.<br/><br/>
This is to inform you that the status of your recent document request has been <b>[status]</b>.<br/><br/>
Request Details:<br/><br/>
Request Type: <b>${esc(request.name)}</b><br/>
Status: <b>[status]</b><br/>
Date of Request: <b>${esc(request.created_at_formatted ?? "")}</b><br/><br/>
If your request has been approved, you will receive further instructions shortly regarding collection or delivery.<br/><br/>
If it has been rejected, please contact the administration office or reply to this email for more information regarding the reason and possible next steps.<br/><br/>
If it is still in progress, we appreciate your patience and will notify you as soon as there is an update.<br/><br/>
Thank you for your cooperation.<br/><br/>
Best regards,<br/>
The Academic Admin Dept.<br/>
London Churchill College`;
}

/* ------------------------------------------------------------------ */
/* Bulk + row operations                                               */
/* ------------------------------------------------------------------ */

async function updateStatus({ ids, status, studentTaskId = 0, button }) {
    if (!ids.length) return;
    spin(button, true);

    const { ok } = await post(config.routes.updateStatus, {
        student_ids: ids,
        task_id: config.taskId,
        status,
        phase: config.phase,
        student_task_id: studentTaskId,
    });

    spin(button, false);
    if (!ok) return toast("The task status could not be updated. Please try again.", true);

    state.selected.clear();
    await load();
    feedback({
        title: "Task status updated",
        copy:
            ids.length > 1
                ? `${ids.length} students have been marked complete for ${esc(config.taskName)}.`
                : `The student has been marked complete for ${esc(config.taskName)}.`,
        pill: "Completed",
        dismissMs: 2600,
    });
}

async function loadSlot(name, url, payload, onLoaded) {
    const el = openModal(name);
    if (!el) return;
    const slot = $("[data-tkm-slot]", el);
    slot.innerHTML = '<div class="tkm-modal__loader"></div>';

    const { ok, data } = await post(url, payload);
    if (!ok) {
        slot.innerHTML = '<div class="tkm-empty"><div class="tkm-empty__title">Could not load this record</div></div>';
        return;
    }
    onLoaded(el, slot, data);
}

/* ------------------------------------------------------------------ */
/* Delegated actions                                                   */
/* ------------------------------------------------------------------ */

const ACTIONS = {
    /* chrome — the page header is the shared global header and brings its own JS */
    "pop-toggle": (button) => togglePopover(button.closest("[data-tkm-pop]")),

    /* dialogs */
    open: (button) => openModal(button.dataset.tkmTarget),
    close: (button) => {
        const el = button.closest("[data-tkm-modal]");
        const reload = el?.dataset.tkmReload === "1";
        closeModal(el);
        if (reload) window.location.reload();
    },

    /* filters + paging */
    "filter-go": () => load({ resetPage: true }),
    "filter-reset": () => {
        const form = $("[data-tkm-filters]");
        $("[name=reg_or_ref]", form).value = "";
        $("[name=status]", form).value = "Pending";
        $("[name=venue]", form).value = "";
        $("[name=courses]", form).value = "";
        state.selected.clear();
        load({ resetPage: true });
    },
    page: (button) => {
        const page = Number(button.dataset.tkmPage);
        if (!page || page === state.page || page < 1 || page > Math.max(1, state.lastPage)) return;
        state.page = page;
        load();
    },
    "page-size": (select) => {
        state.size = Number(select.value) || 25;
        load({ resetPage: true });
    },

    /* selection */
    "select-all": (input) => {
        state.rows
            .filter((row) => row.task_id > 0)
            .forEach((row) => {
                if (input.checked) state.selected.set(rowKey(row), row);
                else state.selected.delete(rowKey(row));
            });
        renderRows();
        renderSelection();
    },
    "select-row": (input) => {
        const row = state.rows.find((candidate) => rowKey(candidate) === input.dataset.tkmKey);
        if (!row) return;
        if (input.checked) state.selected.set(rowKey(row), row);
        else state.selected.delete(rowKey(row));
        input.closest("tr")?.classList.toggle("is-selected", input.checked);
        renderSelection();
    },

    /* bulk */
    "bulk-complete": (button) => updateStatus({ ids: selectedIds(), status: "Completed", button }),
    "bulk-cancel": () => {
        if (!state.selected.size) return;
        const el = openModal("cancel");
        $('[name="ids"]', el).value = selectedIds().join(",");
    },
    "export-list": async (button) => {
        spin(button, true);
        try {
            await download(
                config.routes.exportList,
                { ids: selectedIds(), task_id: config.taskId, phase: config.phase },
                `${config.taskName.replace(/\s+/g, "_")}_Assigned_Student_List.xlsx`
            );
        } catch (e) {
            toast(e.message, true);
        }
        spin(button, false);
    },
    "export-emails": async (button) => {
        spin(button, true);
        try {
            await download(config.routes.exportEmails, { ids: selectedIds() }, "New_Student_Email_Id_Create_Task.xlsx");
        } catch (e) {
            toast(e.message, true);
        }
        spin(button, false);
    },
    "export-pearson": async (button) => {
        spin(button, true);
        try {
            await download(config.routes.exportPearson, { ids: selectedIds() }, "BTECRTypeSA1.xlsx");
        } catch (e) {
            toast(e.message, true);
        }
        spin(button, false);
    },
    "complete-emails": async (button) => {
        const ids = selectedIds();
        if (!ids.length) return;
        spin(button, true);
        const { ok } = await post(config.routes.completeEmails, { ids });
        spin(button, false);
        if (!ok) return toast("The email task could not be completed.", true);

        state.selected.clear();
        await load();
        feedback({
            title: "Email task completed",
            copy: "The new email IDs have been created and the welcome message has been sent.",
            pill: "Completed",
            dismissMs: 2600,
        });
    },

    /* row actions */
    upload: (button) => {
        const el = openModal("upload");
        if (!el) return;
        const form = $("[data-tkm-form]", el);
        resetForm(form);
        $('[name="student_id"]', form).value = button.dataset.tkmStudentid;
        $('[name="task_id"]', form).value = button.dataset.tkmTaskid;
        $('[name="phase"]', form).value = button.dataset.tkmPhase;
    },
    complete: (button) =>
        updateStatus({
            ids: [button.dataset.tkmStudentid],
            status: "Completed",
            studentTaskId: button.dataset.tkmStudenttaskid || 0,
            button,
        }),
    outcome: (button) =>
        loadSlot(
            "outcome",
            config.routes.outcomeStatuses,
            { phase: button.dataset.tkmPhase, taskid: button.dataset.tkmTaskid, studentid: button.dataset.tkmStudentid },
            (el, slot, data) => {
                slot.innerHTML = data.message?.res ?? "";
                $('[name="student_id"]', el).value = button.dataset.tkmStudentid;
                $('[name="task_id"]', el).value = button.dataset.tkmTaskid;
                $('[name="phase"]', el).value = button.dataset.tkmPhase;
            }
        ),
    excuse: (button) =>
        loadSlot("excuse", config.routes.viewExcuse, { student_task_id: button.dataset.tkmStudenttaskid }, (el, slot, data) => {
            slot.innerHTML = data.htm ?? "";
            $('[name="student_id"]', el).value = button.dataset.tkmStudentid;
            $('[name="student_task_id"]', el).value = button.dataset.tkmStudenttaskid;
            $('[name="attendance_excuse_id"]', el).value = data.excuse ?? 0;
        }),
    address: (button) =>
        loadSlot(
            "address",
            config.routes.viewAddress,
            { student_id: button.dataset.tkmStudentid, student_task_id: button.dataset.tkmStudenttaskid },
            (el, slot, data) => {
                slot.innerHTML = data.html ?? "";
                $('[name="student_id"]', el).value = button.dataset.tkmStudentid;
                $('[name="student_task_id"]', el).value = button.dataset.tkmStudenttaskid;
                $('[name="student_address_update_request_id"]', el).value = data.student_address_update_request_id ?? 0;
                const status = $('[name="task_status"]', el);
                status.value = data.task_status ?? "Pending";
                $("[data-tkm-addr-note]", el).hidden = status.value !== "In Progress";
            }
        ),
    "address-status": (select) => {
        const el = select.closest("[data-tkm-modal]");
        $("[data-tkm-addr-note]", el).hidden = select.value !== "In Progress";
    },
    idcard: (button) =>
        loadSlot(
            "idcard",
            config.routes.idCard,
            { student_id: button.dataset.tkmStudentid, task_id: button.dataset.tkmTaskid },
            (el, slot, data) => {
                slot.innerHTML = data.res ?? "";
            }
        ),
    unlock: (button) => {
        const el = openModal("unlock");
        if (!el) return;
        const form = $("[data-tkm-form]", el);
        resetForm(form);
        $('[name="applicantId"]', form).value = button.dataset.tkmStudentid;
        $('[name="taskListId"]', form).value = button.dataset.tkmTaskid;
    },
    "view-profile": async (button) => {
        button.disabled = true;
        const { ok, data, status } = await post(config.routes.unlockProfile, { interviewId: button.dataset.tkmId });
        button.disabled = false;
        if (ok && data.ref) return (window.location.href = data.ref);
        feedback({
            title: "Invalid profile",
            copy: status === 404 ? "The interviewer did not match this record." : "Something went wrong. Please try later.",
            tone: "bad",
        });
    },
    "download-doc": async (button) => {
        button.disabled = true;
        const { ok, data } = await post(config.routes.downloadDoc, { phase: button.dataset.tkmPhase, id: button.dataset.tkmId });
        button.disabled = false;
        if (ok && data.res) window.open(data.res, "_blank", "noopener");
        else toast("That document could not be opened.", true);
    },
    "print-idcard": async (button) => {
        const card = document.getElementById(`theIDCard_${button.dataset.id}`);
        if (!card) return;
        button.disabled = true;
        const { default: html2canvas } = await import("html2canvas");
        const canvas = await html2canvas(card, { useCORS: true, allowTaint: true });
        canvas.toBlob((blob) => {
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = `${button.dataset.id}.jpg`;
            link.click();
            setTimeout(() => URL.revokeObjectURL(link.href), 4000);
            button.disabled = false;
            closeModal(modal("idcard"));
        });
    },

    /* document requests */
    docreq: async (button) => {
        const row = rowFor(button);
        const request = requestFor(row);
        const el = openModal("docreq");
        if (!el || !request) return;

        const form = $("[data-tkm-form]", el);
        resetForm(form);
        paintRequest(el, request);
        $('[name="student_task_id"]', form).value = button.dataset.tkmStudenttaskid;

        const name = row.full_name || `${row.first_name} ${row.last_name}`.trim();
        const editor = await getEditor("docreq");
        editor?.setData(docRequestTemplate(name, request));
    },
    letter: async (button) => {
        const row = rowFor(button);
        const request = requestFor(row);
        const el = openModal("letter");
        if (!el) return;

        const form = $("[data-tkm-form]", el);
        resetForm(form);
        $("[data-tkm-smtp]", form).hidden = true;
        $("[data-tkm-letterbody]", form).hidden = true;
        if (request) paintRequest(el, request);
        $('[name="student_task_id"]', form).value = button.dataset.tkmStudenttaskid;
        $('[name="student_id"]', form).value = request?.student_id ?? button.dataset.tkmStudentid;

        const letterSet = $('[name="letter_set_id"]', form);
        letterSet.value = button.dataset.tkmLettersetid || "";
        if (letterSet.value) await loadLetterBody(form, letterSet.value);
    },
    "letter-set": (select) => loadLetterBody(select.closest("[data-tkm-form]"), select.value),
    "letter-email": (input) => {
        const form = input.closest("[data-tkm-form]");
        const wrap = $("[data-tkm-smtp]", form);
        wrap.hidden = !input.checked;
        if (!input.checked) $("select", wrap).value = "";
    },
    "copy-tag": (button) => {
        navigator.clipboard?.writeText(button.dataset.tkmTag);
        toast("Tag copied");
        closePopovers();
    },

    /* file pickers */
    "drop-file": (button) => {
        const form = button.closest("[data-tkm-form]");
        const files = picked.get(form) || [];
        files.splice(Number(button.dataset.tkmIndex), 1);
        picked.set(form, files);
        renderFileList(form);
    },
};

async function loadLetterBody(form, letterSetId) {
    const wrap = $("[data-tkm-letterbody]", form);
    if (!letterSetId) {
        wrap.hidden = true;
        (await getEditor("letter"))?.setData("");
        return;
    }
    const { ok, data } = await post(config.routes.getLetterSet, { letterSetId });
    wrap.hidden = false;
    const editor = await getEditor("letter");
    editor?.setData(ok ? data.res?.description ?? "" : "");
}

/* ------------------------------------------------------------------ */
/* Delegated submits                                                   */
/* ------------------------------------------------------------------ */

const FORMS = {
    cancel: async (form, button) => {
        const data = new FormData(form);
        if (!String(data.get("canceled_reason")).trim()) {
            return showErrors(form, { canceled_reason: "Please give a reason." });
        }

        spin(button, true);
        const { ok, status, data: body } = await post(config.routes.cancel, data);
        spin(button, false);

        if (status === 422) return showErrors(form, body.errors);
        if (!ok) return toast("The task could not be cancelled.", true);

        closeModal(modal("cancel"));
        resetForm(form);
        state.selected.clear();
        await load();
        feedback({ title: "Task cancelled", copy: "The selected students' task has been cancelled.", pill: "Cancelled", dismissMs: 2600 });
    },

    upload: async (form, button) => {
        const files = picked.get(form) || [];
        if (!files.length) return toast("Choose at least one file to upload.", true);

        spin(button, true);
        // The endpoint takes one file per request, so the queue is sequential.
        let failed = 0;
        for (const file of files) {
            const payload = new FormData();
            payload.append("file", file);
            payload.append("student_id", $('[name="student_id"]', form).value);
            payload.append("task_id", $('[name="task_id"]', form).value);
            payload.append("phase", $('[name="phase"]', form).value);
            payload.append("display_file_name", $('[name="display_file_name"]', form).value);
            payload.append("hard_copy_check", $('[name="hard_copy_check"]:checked', form)?.value ?? "0");

            const { ok } = await post(config.routes.uploadDoc, payload);
            if (!ok) failed += 1;
        }
        spin(button, false);

        closeModal(modal("upload"));
        resetForm(form);
        await load();

        if (failed) {
            feedback({
                title: "Some files did not upload",
                copy: `${failed} of ${files.length} file${files.length === 1 ? "" : "s"} failed. Please try those again.`,
                tone: "warn",
            });
        } else {
            feedback({
                title: "Documents uploaded",
                copy: `${files.length} file${files.length === 1 ? "" : "s"} added to this student's task.`,
                pill: "Uploaded",
                dismissMs: 2600,
            });
        }
    },

    outcome: async (form, button) => {
        const data = new FormData(form);
        if (!data.get("result_statuses")) return toast("Pick a result before updating.", true);

        spin(button, true);
        const { ok } = await post(config.routes.updateOutcome, data);
        spin(button, false);
        if (!ok) return toast("The outcome could not be updated.", true);

        closeModal(modal("outcome"));
        await load();
        feedback({ title: "Outcome updated", copy: "The task result has been recorded.", pill: "Updated", dismissMs: 2600 });
    },

    excuse: async (form, button) => {
        spin(button, true);
        const { ok, status, data } = await post(config.routes.updateExcuse, new FormData(form));
        spin(button, false);
        if (status === 422) return showErrors(form, data.errors);
        if (!ok) return toast("The excuse could not be updated.", true);

        closeModal(modal("excuse"));
        await load();
        feedback({ title: "Excuse updated", copy: "The attendance excuse status has been recorded.", pill: "Updated", dismissMs: 2600 });
    },

    address: async (form, button) => {
        spin(button, true);
        const { ok, status, data } = await post(config.routes.updateAddress, new FormData(form));
        spin(button, false);
        if (status === 422) return showErrors(form, data.errors);
        if (!ok) return toast("The request could not be updated.", true);

        closeModal(modal("address"));
        await load();
        feedback({ title: "Request updated", copy: "The address update request has been actioned.", pill: "Updated", dismissMs: 2600 });
    },

    unlock: async (form, button) => {
        const data = new FormData(form);
        if (!data.get("dob")) return showErrors(form, { dob: "Please enter the applicant's date of birth." });

        spin(button, true);
        const { ok, status, data: body } = await post(config.routes.unlockWithDob, data);
        spin(button, false);

        if (ok && body.ref) return (window.location.href = body.ref);
        if (status === 422) return showErrors(form, body.errors);
        showErrors(form, { dob: "That date of birth does not match. Check with the Admissions Office." });
    },

    pearson: async (form, button) => {
        const files = picked.get(form) || [];
        const data = new FormData(form);
        if (files.length) data.set("document", files[0]);

        spin(button, true);
        const { ok, status, data: body } = await post(config.routes.uploadPearson, data);
        spin(button, false);

        if (status === 422) return showErrors(form, body.errors);
        if (status === 405) return feedback({ title: "Upload rejected", copy: body.msg ?? "", tone: "bad" });
        if (!ok) return toast("The confirmations could not be uploaded.", true);

        closeModal(modal("pearson"));
        resetForm(form);
        await load();
        feedback({ title: "Confirmations uploaded", copy: body.msg ?? "", pill: "Uploaded" });
    },

    docreq: async (form, button) => {
        const data = new FormData(form);
        if (!data.get("status")) return showErrors(form, { status: "Choose approved or rejected." });

        const editor = await getEditor("docreq");
        data.append("description", editor?.getData() ?? "");

        spin(button, true);
        const { ok, status, data: body } = await post(config.routes.docRequestUpdate, data);
        spin(button, false);

        if (status === 422) return showErrors(form, body.errors);
        if (!ok) return toast(body.msg ?? "The request could not be updated.", true);

        closeModal(modal("docreq"));
        await load();
        feedback({ title: "Request updated", copy: "The document request status has been recorded.", pill: "Updated", dismissMs: 2600 });
    },

    letter: async (form, button) => {
        const data = new FormData(form);
        const editor = await getEditor("letter");
        data.append("letter_body", editor?.getData() ?? "");

        spin(button, true);
        const { ok, status, data: body } = await post(config.routes.sendLetter, data);
        if (status === 422) {
            spin(button, false);
            return showErrors(form, body.errors);
        }
        if (!ok) {
            spin(button, false);
            return toast("The letter could not be sent.", true);
        }

        // Flag the request as fulfilled so the row stops offering to generate it.
        await post(config.routes.letterStatus, { student_task_id: data.get("student_task_id") });
        spin(button, false);

        closeModal(modal("letter"));
        resetForm(form);
        await load();
        feedback({ title: "Letter sent", copy: "The requested document has been generated and sent.", pill: "Sent", dismissMs: 2600 });
    },
};

/* ------------------------------------------------------------------ */
/* Wiring — three listeners for the whole screen                       */
/* ------------------------------------------------------------------ */

function boot() {
    if (!document.body.classList.contains("tkm-body")) return;

    // The task list (module index) shares this shell for the header, nav and
    // profile menu, but has no table to fill — everything below the header
    // wiring is skipped there.
    const hasTable = !!$("[data-tkm-tablewrap]");

    // Controls whose meaningful event is `change`, not `click`.
    const isValueControl = (node) =>
        node.tagName === "SELECT" || (node.tagName === "INPUT" && (node.type === "checkbox" || node.type === "radio"));

    const TOGGLES = ["pop-toggle"];

    document.addEventListener("click", (event) => {
        const trigger = event.target.closest("[data-tkm-act]");

        if (!trigger || isValueControl(trigger)) {
            // A click anywhere else dismisses open popovers, and a click on a
            // dialog's backdrop (the overlay itself, not its card) closes it.
            closePopovers();
            const overlay = event.target.closest("[data-tkm-modal]");
            if (overlay && event.target === overlay) {
                const reload = overlay.dataset.tkmReload === "1";
                closeModal(overlay);
                if (reload) window.location.reload();
            }
            return;
        }

        const action = ACTIONS[trigger.dataset.tkmAct];
        if (!action) return;

        if (trigger.tagName !== "A") event.preventDefault();
        // Picking an item out of a menu should also put the menu away.
        if (!TOGGLES.includes(trigger.dataset.tkmAct)) closePopovers();

        action(trigger, event);
    });

    document.addEventListener("change", (event) => {
        const trigger = event.target.closest("[data-tkm-act]");
        if (trigger && isValueControl(trigger)) {
            ACTIONS[trigger.dataset.tkmAct]?.(trigger, event);
            return;
        }

        const filePicker = event.target.closest("[data-tkm-files]");
        if (filePicker) {
            addFiles(filePicker.closest("[data-tkm-form]"), filePicker.files);
            filePicker.value = "";
        }
    });

    document.addEventListener("submit", (event) => {
        const form = event.target.closest("[data-tkm-form]");
        if (!form) return;
        event.preventDefault();
        FORMS[form.dataset.tkmForm]?.(form, $('button[type="submit"]', form));
    });

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") return;
        closePopovers();
        const open = $$("[data-tkm-modal]").find((el) => !el.hidden);
        if (open) closeModal(open);
    });

    // Enter inside the filter bar runs the filter rather than submitting.
    $("[data-tkm-filters]")?.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            load({ resetPage: true });
        }
    });

    // Sortable headers.
    $$("[data-tkm-sort]").forEach((th) => {
        th.addEventListener("click", () => {
            const field = th.dataset.tkmSort;
            state.sort =
                state.sort.field === field
                    ? { field, dir: state.sort.dir === "ASC" ? "DESC" : "ASC" }
                    : { field, dir: "ASC" };
            $$("[data-tkm-sort]").forEach((other) => other.classList.remove("is-sorted", "is-sorted-desc"));
            th.classList.add("is-sorted");
            th.classList.toggle("is-sorted-desc", state.sort.dir === "DESC");
            load({ resetPage: true });
        });
    });

    // Drag-and-drop onto either dropzone.
    $$("[data-tkm-drop]").forEach((zone) => {
        ["dragenter", "dragover"].forEach((type) =>
            zone.addEventListener(type, (event) => {
                event.preventDefault();
                zone.classList.add("is-over");
            })
        );
        ["dragleave", "drop"].forEach((type) =>
            zone.addEventListener(type, (event) => {
                event.preventDefault();
                zone.classList.remove("is-over");
            })
        );
        zone.addEventListener("drop", (event) => {
            if (event.dataTransfer?.files?.length) addFiles(zone.closest("[data-tkm-form]"), event.dataTransfer.files);
        });
    });

    // The ID-card partial ships its own print buttons.
    document.addEventListener("click", (event) => {
        const print = event.target.closest(".thePrintBtn");
        if (print) {
            event.preventDefault();
            ACTIONS["print-idcard"](print);
        }
    });

    if (hasTable) load();
}

if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", boot);
else boot();
