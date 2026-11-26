import { createIcons, icons } from "lucide";
import Dropzone from "dropzone";
import html2canvas from "html2canvas";
import { saveAs } from 'file-saver';
import { initDocumentVault } from "./document-vault";
import { esc, fmtDate, plural, avatar as avatarOf, hl, memory, makeToast, printTable, downloadSheet, pagerHtml, bindMenu } from "./student-record-kit";

("use strict");

/*
 * Student documents page (/student/uploads/{id}).
 *
 * Documents: every document for the chosen status (Active / Archived) comes
 * down in one request — the busiest student has under fifty — and the
 * original-seen filter, uploader filter, search, sorting and paging all happen
 * here, so the counts always describe the list being shown.
 *
 * Access log: paged on the server, because unlike the documents it only grows.
 *
 * Upload (Dropzone dialog), Print ID Card and the encrypted-document PIN
 * prompt are the page's existing pieces, wired to these two lists.
 */

const sv = (w, d, sw) =>
    `<svg width="${w}" height="${w}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${sw || 2}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
const I = {
    doc: sv(18, '<path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5M9 13h6M9 17h4"/>'),
    lockDoc: sv(18, '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>'),
    lock: sv(11, '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>', 2.4),
    key: sv(16, '<circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 7.7-7.7M16 7l2 2M14 9l1.5 1.5"/>'),
    check: sv(12, '<path d="m5 12 5 5 9-10"/>', 3),
    down: sv(16, '<path d="M12 4v11m-4-4 4 4 4-4M5 20h14"/>'),
    trash: sv(16, '<path d="M3 6h18M8 6V4h8v2M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14M10 11v6M14 11v6"/>'),
    restore: sv(16, '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>'),
    clock: sv(18, '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>'),
    sort: sv(12, '<path d="m7 15 5 5 5-5M7 9l5-5 5 5"/>', 2.4),
    up: sv(12, '<path d="m7 14 5-5 5 5"/>', 2.6),
    downArrow: sv(12, '<path d="m7 10 5 5 5-5"/>', 2.6),
    user: sv(12, '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>', 2.2),
};
const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
const toast = makeToast(document.getElementById("sdcToast"));
const avatar = (n) => avatarOf(n, "sdc-av");

/* ------------------------------------------------------- access log */

const logApp = (function () {
    const root = document.getElementById("studentDocLogApp");
    if (!root) return { reload() {} };
    const $ = (sel) => root.querySelector(sel);
    const studentId = root.dataset.student || "0";
    const remember = memory("studentDocLog");
    const state = { event: "", page: 1, size: ["10", "25", "50", "0"].includes(remember("size")) ? +remember("size") : 10 };
    let requestNo = 0;

    const fetchPage = (all) => axios.get(route("student.uploads.access.log.list"), {
        params: { studentId, event: state.event, page: all ? 1 : state.page, size: all || state.size === 0 ? "true" : state.size },
    });

    function row(r) {
        return `<div class="sdc-row" role="row">
            <div class="sdc-date" role="cell">${esc(fmtDate(r.date_iso) || r.date)}<small>${esc(r.time)}</small></div>
            <div role="cell"><span class="sdc-pill sdc-pill--${esc(r.tone || "info")}">${esc(r.event)}</span></div>
            <div class="sdc-doc" role="cell"><div><strong>${esc(r.document || "—")}</strong>${+r.is_encrypted === 1 ? `<span class="sdc-chip" title="Stored encrypted. Opens with a document PIN.">${I.lock}Encrypted</span>` : ""}</div></div>
            <div class="sdc-by" role="cell">${avatar(r.user)}<div>${esc(r.user)}${r.signed_in_as ? `<div class="sdc-sub">${I.user}Through impersonation, signed in as ${esc(r.signed_in_as)}</div>` : ""}</div></div>
            <div class="sdc-ip" role="cell">${esc(r.ip_address || "—")}</div>
        </div>`;
    }

    function reload() {
        const mine = ++requestNo;
        $("#sdcLogRows").setAttribute("aria-busy", "true");
        return fetchPage(false).then((res) => {
            if (mine !== requestNo) return;
            const data = res.data || {};
            const rows = Array.isArray(data.data) ? data.data : [];
            const total = +data.total_rows || 0;
            const pages = state.size > 0 ? Math.max(1, +data.last_page || 1) : 1;
            if (state.page > pages && total) { state.page = pages; reload(); return; }

            $("#sdcLogRows").innerHTML = rows.length
                ? rows.map(row).join("")
                : `<div class="sdc-empty"><span class="sdc-ficon">${I.clock}</span><strong>${state.event ? "Nothing of this kind has been recorded" : "Nothing has been recorded yet"}</strong>Entries will be listed here, newest first.</div>`;
            $("#sdcLogRows").setAttribute("aria-busy", "false");
            const from = state.size > 0 ? (state.page - 1) * state.size : 0;
            $("#sdcLogFoot").hidden = !total;
            $("#sdcLogRange").textContent = total ? `${from + 1}–${from + rows.length} of ${total}` : "";
            $("#sdcLogPager").innerHTML = pagerHtml(state.page, pages, "sdc-gap");
            $("#sdcLogSize").value = String(state.size);
        }).catch(() => {
            if (mine !== requestNo) return;
            $("#sdcLogRows").setAttribute("aria-busy", "false");
            $("#sdcLogFoot").hidden = true;
            $("#sdcLogRows").innerHTML = `<div class="sdc-empty"><strong>The log could not be loaded</strong>Check your connection and try again.<br><button type="button" class="sdc-link" data-log-retry>Try again</button></div>`;
        });
    }

    const eventLabel = () => $("#sdcLogEvent").selectedOptions[0]?.textContent.trim() || "Everything";

    function withAll(fn) {
        fetchPage(true).then((res) => {
            const rows = res.data && Array.isArray(res.data.data) ? res.data.data : [];
            if (!rows.length) { toast("There is nothing in the log to output.", true); return; }
            fn(rows);
        }).catch(() => toast("The log could not be loaded. Please try again.", true));
    }

    const exportMenu = bindMenu($("#sdcLogExportBtn"), $("#sdcLogExportMenu"));
    const docsRoot = document.getElementById("studentDocsApp");
    const studentName = docsRoot?.dataset.studentName || "";
    const studentRef = docsRoot?.dataset.studentRef || studentId;

    root.addEventListener("click", (e) => {
        const b = e.target.closest("button");
        if (!b || !root.contains(b)) return;
        const d = b.dataset;
        if (d.page) {
            state.page = +d.page;
            reload();
        } else if ("logRetry" in d) {
            reload();
        } else if (d.logExport) {
            exportMenu.close();
            withAll((rows) => downloadSheet({
                rows: rows.map((r) => ({
                    "Date": r.date, "Time": r.time, "Event": r.event, "Document": r.document,
                    "Encrypted": +r.is_encrypted === 1 ? "Yes" : "No", "By": r.user,
                    "Signed In As (Impersonated)": r.signed_in_as, "IP Address": r.ip_address,
                })),
                sheet: "Document Access Log",
                base: `student-document-access-log-${String(studentRef).replace(/[^\w-]+/g, "")}`,
                kind: d.logExport,
            }));
        } else if (b.id === "sdcLogPrint") {
            withAll((rows) => printTable({
                title: `Document access log — ${studentName}${studentRef ? " (" + studentRef + ")" : ""}`,
                subtitle: `${eventLabel()} · ${rows.length === 1 ? "1 entry" : rows.length + " entries"}`,
                headers: ["When", "Event", "Document", "By", "IP address"],
                rows: rows.map((r) => [
                    `${esc(fmtDate(r.date_iso) || r.date)}<div class="m">${esc(r.time)}</div>`, esc(r.event),
                    esc(r.document) + (+r.is_encrypted === 1 ? '<div class="m">Encrypted</div>' : ""),
                    esc(r.user) + (r.signed_in_as ? `<div class="m">Through impersonation, signed in as ${esc(r.signed_in_as)}</div>` : ""),
                    esc(r.ip_address),
                ]),
            }));
        }
    });
    $("#sdcLogEvent").addEventListener("change", (e) => { state.event = e.target.value; state.page = 1; reload(); });
    $("#sdcLogSize").addEventListener("change", (e) => {
        state.size = +e.target.value || 0;
        remember("size", String(state.size));
        state.page = 1;
        reload();
    });

    reload();
    return { reload };
})();

/* ------------------------------------------------------- documents */

const docsApp = (function () {
    const root = document.getElementById("studentDocsApp");
    if (!root) return { reload() {} };
    const $ = (sel) => root.querySelector(sel);
    const studentId = root.dataset.student || "0";
    const studentName = root.dataset.studentName || "";
    const studentRef = root.dataset.studentRef || "";
    const canAdd = root.dataset.canAdd === "1";
    const remember = memory("studentDocs");

    const state = {
        status: "1",
        type: "all",
        staff: "",
        q: "",
        sort: { key: "date", dir: "desc" },
        page: 1,
        size: ["10", "25", "50", "0"].includes(remember("size")) ? +remember("size") : 10,
        confirm: null, // { id, kind: 'del' | 'restore' }
        busy: false,
    };
    let docs = [];
    let loading = true;
    let failed = false;
    let requestNo = 0;

    const types = [
        { key: "all", label: "All", test: () => true },
        { key: "ok", label: "Original seen", test: (d) => d.seen },
        { key: "no", label: "Original not seen", test: (d) => !d.seen },
    ];
    const columns = [
        { key: "name", label: "Document" },
        { key: "by", label: "Uploaded by" },
        { key: "date", label: "Uploaded" },
        { key: "seen", label: "Original" },
    ];

    const norm = (r) => {
        const d = {
            raw: r,
            id: +r.id,
            name: r.display_file_name || "Unknown",
            type: String(r.doc_type || "").toUpperCase(),
            seen: +r.hard_copy_check === 1,
            by: r.created_by || "Unknown",
            date: r.created_iso || "",
            encrypted: +r.is_encrypted === 1,
            viewable: +r.viewable === 1,
            canDelete: +r.can_delete === 1,
            deleted: !!r.deleted_at,
        };
        d.hay = [d.name, d.by, d.id, d.type, d.encrypted ? "encrypted" : ""].join(" ").toLowerCase();
        return d;
    };

    function filtered() {
        const q = state.q.trim().toLowerCase();
        const type = types.find((t) => t.key === state.type) || types[0];
        const dir = state.sort.dir === "asc" ? 1 : -1;
        const cmp = {
            name: (a, b) => a.name.localeCompare(b.name, "en", { sensitivity: "base" }),
            by: (a, b) => a.by.localeCompare(b.by, "en", { sensitivity: "base" }),
            date: (a, b) => (a.date < b.date ? -1 : a.date > b.date ? 1 : 0),
            seen: (a, b) => (a.seen === b.seen ? 0 : a.seen ? 1 : -1),
        }[state.sort.key];
        return docs
            .filter((d) => type.test(d) && (!state.staff || d.by === state.staff) && (!q || d.hay.includes(q)))
            .sort((a, b) => (cmp(a, b) || a.id - b.id) * dir);
    }

    /* ------------------------------------------------------------- pieces */

    function actions(d) {
        if (state.confirm && state.confirm.id === d.id) {
            const words = state.confirm.kind === "del"
                ? ["Delete this document?", "Delete", "Deleting…", ""]
                : ["Restore this document?", "Restore", "Restoring…", " sdc-yes--ok"];
            return `<div class="sdc-confirm" role="group" aria-label="${words[0]}">${words[0]}`
                + `<button type="button" data-cancel ${state.busy ? "disabled" : ""}>Cancel</button>`
                + `<button type="button" class="sdc-yes${words[3]}" data-yes="${d.id}" ${state.busy ? "disabled" : ""}>${state.busy ? words[2] : words[1]}</button></div>`;
        }
        let html = `<div class="sdc-acts">`;
        html += d.encrypted
            // No storage link exists for an encrypted document: it is asked
            // for with a PIN and comes back through the app.
            ? `<button type="button" class="sdc-ib sdc-ib--lock openEncryptedDoc" data-id="${d.id}" data-name="${esc(d.name)}" data-viewable="${d.viewable ? 1 : 0}" title="Open with document PIN" aria-label="Open ${esc(d.name)} with document PIN">${I.key}</button>`
            : `<button type="button" class="sdc-ib downloadDoc" data-id="${d.id}" title="Download" aria-label="Download ${esc(d.name)}">${I.down}</button>`;
        if (d.canDelete && !d.deleted)
            html += `<button type="button" class="sdc-ib sdc-ib--del" data-del="${d.id}" title="Delete" aria-label="Delete ${esc(d.name)}">${I.trash}</button>`;
        else if (d.canDelete && d.deleted)
            html += `<button type="button" class="sdc-ib" data-restore="${d.id}" title="Restore" aria-label="Restore ${esc(d.name)}">${I.restore}</button>`;
        return html + `</div>`;
    }

    function row(d, q) {
        const confirming = state.confirm && state.confirm.id === d.id;
        return `<div class="sdc-row${d.deleted ? " sdc-row--archived" : ""}${confirming ? " is-confirming" : ""}" role="row">
            <div class="sdc-doc" role="cell"><span class="sdc-ficon${d.encrypted ? " sdc-ficon--locked" : ""}">${d.encrypted ? I.lockDoc : I.doc}</span>
                <div><strong>${hl(d.name, q)}</strong>${d.encrypted ? `<span class="sdc-chip" title="Stored encrypted. Opens with a document PIN.">${I.lock}Encrypted</span>` : ""}
                <small>#${hl(d.id, q)}${d.type ? ` · ${hl(d.type, q)}` : ""}</small></div></div>
            <div class="sdc-by" role="cell">${avatar(d.by)}<span>${hl(d.by, q)}</span></div>
            <div class="sdc-date" role="cell">${fmtDate(d.date) || "—"}</div>
            <div class="sdc-st" role="cell">${d.seen ? `<span class="sdc-pill sdc-pill--ok">${I.check}Original seen</span>` : `<span class="sdc-none" aria-label="Original not seen" title="Original not seen">&ndash;</span>`}</div>
            <div class="sdc-a" role="cell">${actions(d)}</div>
        </div>`;
    }

    function head() {
        return columns.map((c) => {
            const on = state.sort.key === c.key;
            const sortAttr = on ? (state.sort.dir === "asc" ? "ascending" : "descending") : "none";
            return `<span role="columnheader" aria-sort="${sortAttr}"><button type="button" data-sort="${c.key}" title="Sort by ${c.label.toLowerCase()}">${c.label}${on ? (state.sort.dir === "asc" ? I.up : I.downArrow) : I.sort}</button></span>`;
        }).join("") + `<span role="columnheader">Actions</span>`;
    }

    /* ------------------------------------------------------------- render */

    function render() {
        const a = document.activeElement;
        let refocus = null;
        if (a && root.contains(a) && a.dataset) {
            for (const k of ["type", "sort", "page", "cancel", "yes", "del", "restore"]) {
                if (k in a.dataset) { refocus = `[data-${k}="${CSS.escape(a.dataset[k])}"]`; break; }
            }
        }

        const archived = state.status === "2";
        const seen = docs.filter((d) => d.seen).length;
        const encrypted = docs.filter((d) => d.encrypted).length;

        $("#sdcSeg").innerHTML = types.map((t) =>
            `<button type="button" data-type="${t.key}" aria-pressed="${state.type === t.key}">${t.label}<span class="sdc-n">${docs.filter(t.test).length}</span></button>`).join("");

        const staff = [...new Set(docs.map((d) => d.by))].sort((x, y) => x.localeCompare(y));
        if (state.staff && !staff.includes(state.staff)) state.staff = "";
        $("#sdcStaff").innerHTML = `<option value="">Uploaded by anyone</option>`
            + staff.map((s) => `<option value="${esc(s)}"${s === state.staff ? " selected" : ""}>${esc(s)}</option>`).join("");
        $("#sdcStatus").value = state.status;
        $("#sdcHead").innerHTML = head();

        const q = state.q.trim().toLowerCase();
        const list = filtered();
        const isFiltered = !!q || !!state.staff || state.type !== "all";
        const pages = state.size > 0 ? Math.max(1, Math.ceil(list.length / state.size)) : 1;
        state.page = Math.min(Math.max(1, state.page), pages);
        const from = state.size > 0 ? (state.page - 1) * state.size : 0;
        const pageList = state.size > 0 ? list.slice(from, from + state.size) : list;

        const rows = $("#sdcRows");
        rows.setAttribute("aria-busy", String(loading));
        if (loading && !docs.length) {
            rows.innerHTML = `<div class="sdc-empty">Loading documents…</div>`;
        } else if (failed) {
            rows.innerHTML = `<div class="sdc-empty"><strong>Documents could not be loaded</strong>Check your connection and try again.<br><button type="button" class="sdc-link" data-retry>Try again</button></div>`;
        } else if (!docs.length) {
            rows.innerHTML = archived
                ? `<div class="sdc-empty"><span class="sdc-ficon">${I.doc}</span><strong>No archived documents</strong>Deleted documents for this student would appear here.</div>`
                : `<div class="sdc-empty"><span class="sdc-ficon">${I.doc}</span><strong>No documents yet</strong>${canAdd ? "Use Add Document to upload the first one." : "Nothing has been uploaded for this student."}</div>`;
        } else if (!list.length) {
            rows.innerHTML = `<div class="sdc-empty"><strong>No documents match these filters</strong>Try another filter or clear the search.<br><button type="button" class="sdc-link" data-clear>Clear filters</button></div>`;
        } else {
            rows.innerHTML = pageList.map((d) => row(d, q)).join("");
        }

        $("#sdcReset").hidden = !(isFiltered || archived);
        $("#sdcRange").textContent = list.length ? `${from + 1}–${from + pageList.length} of ${list.length}` : "0 of 0";
        $("#sdcPager").innerHTML = pagerHtml(state.page, pages, "sdc-gap");
        $("#sdcSize").value = String(state.size);

        let summary;
        if (loading && !docs.length) summary = "Loading documents…";
        else if (failed) summary = "Documents could not be loaded";
        else if (archived) summary = docs.length ? plural(docs.length, "archived document") : "No archived documents";
        else if (docs.length) summary = [plural(docs.length, "document"), `${seen} original seen`, `${docs.length - seen} original not seen`, encrypted ? `${encrypted} encrypted` : ""].filter(Boolean).join(" · ");
        else summary = "No documents yet";
        if (isFiltered && docs.length && !loading) summary += ` — showing ${list.length}`;
        $("#sdcSummary").textContent = summary;

        if (refocus) root.querySelector(refocus)?.focus({ preventScroll: true });
    }

    /* --------------------------------------------------------------- data */

    function reload() {
        const mine = ++requestNo;
        loading = true;
        failed = false;
        render();
        return axios.get(route("student.uploads.list"), { params: { studentId, status: state.status, size: "true" } })
            .then((res) => {
                if (mine !== requestNo) return;
                docs = (res.data && Array.isArray(res.data.data) ? res.data.data : []).map(norm);
                loading = false;
                render();
            })
            .catch(() => {
                if (mine !== requestNo) return;
                docs = [];
                loading = false;
                failed = true;
                render();
            });
    }

    function runConfirmed() {
        const c = state.confirm;
        if (!c || state.busy) return;
        state.busy = true;
        render();
        const headers = { "X-CSRF-TOKEN": csrf() };
        const req = c.kind === "del"
            ? axios({ method: "delete", url: route("student.destory.uploads"), data: { student: studentId, recordid: c.id }, headers })
            : axios({ method: "post", url: route("student.resotore.uploads"), data: { student: studentId, recordid: c.id }, headers });
        req.then(() => {
            state.confirm = null;
            state.busy = false;
            toast(c.kind === "del" ? "Document deleted" : "Document restored");
            return reload();
        }).catch(() => {
            state.confirm = null;
            state.busy = false;
            render();
            toast("That did not go through. Please try again.", true);
        });
    }

    /* ------------------------------------------------------ print / export */

    function filterLabel() {
        const bits = [state.status === "2" ? "Archived documents" : "Active documents"];
        if (state.type !== "all") bits.push(types.find((t) => t.key === state.type).label);
        if (state.staff) bits.push(`uploaded by ${state.staff}`);
        if (state.q.trim()) bits.push(`matching “${state.q.trim()}”`);
        return bits.join(" · ");
    }

    function printDocs() {
        const list = filtered();
        if (!list.length) { toast("There are no documents to print with these filters.", true); return; }
        printTable({
            title: `Documents — ${studentName}${studentRef ? " (" + studentRef + ")" : ""}`,
            subtitle: `${filterLabel()} · ${plural(list.length, "document")}`,
            headers: ["ID", "Document", "Type", "Uploaded by", "Uploaded", "Original seen"],
            rows: list.map((d) => [`#${d.id}`, esc(d.name) + (d.encrypted ? '<div class="m">Encrypted</div>' : ""), esc(d.type), esc(d.by), esc(fmtDate(d.date)), d.seen ? "Yes" : "No"]),
        });
    }

    function exportDocs(kind) {
        const list = filtered();
        if (!list.length) { toast("There are no documents to export with these filters.", true); return; }
        downloadSheet({
            rows: list.map((d) => ({
                "ID": d.id,
                "Name": d.name,
                "Type": d.type,
                "Original Seen": d.seen ? "Yes" : "No",
                "Encrypted": d.encrypted ? "Yes" : "No",
                "Uploaded By": d.by,
                "Uploaded": fmtDate(d.date),
                "Status": d.deleted ? "Archived" : "Active",
            })),
            sheet: "Student Upload Details",
            base: `student-documents-${(studentRef || studentId).replace(/[^\w-]+/g, "")}`,
            kind,
        });
    }

    /* ------------------------------------------------------------- events */

    const exportMenu = bindMenu($("#sdcExportBtn"), $("#sdcExportMenu"));
    const addMenu = $("#sdcAddBtn") ? bindMenu($("#sdcAddBtn"), $("#sdcAddMenu")) : null;

    root.addEventListener("click", (e) => {
        const b = e.target.closest("button");
        if (!b || !root.contains(b)) return;
        const d = b.dataset;

        if (d.export) { exportMenu.close(); exportDocs(d.export); return; }
        if (d.docSetting) { addMenu?.close(); openUpload(d.docSetting, d.label || ""); return; }
        if (b.id === "sdcPrint") { printDocs(); return; }

        if (d.type) { state.type = d.type; state.page = 1; }
        else if (d.sort) {
            state.sort = state.sort.key === d.sort
                ? { key: d.sort, dir: state.sort.dir === "asc" ? "desc" : "asc" }
                : { key: d.sort, dir: d.sort === "date" || d.sort === "seen" ? "desc" : "asc" };
            state.page = 1;
        }
        else if (d.page) {
            state.page = +d.page;
            render();
            if (root.getBoundingClientRect().top < 0) root.scrollIntoView({ block: "start" });
            return;
        }
        else if (d.del) { state.confirm = { id: +d.del, kind: "del" }; }
        else if (d.restore) { state.confirm = { id: +d.restore, kind: "restore" }; }
        else if ("cancel" in d) { if (state.busy) return; state.confirm = null; }
        else if (d.yes) { runConfirmed(); return; }
        else if ("retry" in d) { reload(); return; }
        else if (b.id === "sdcReset" || "clear" in d) {
            const wasArchived = state.status === "2";
            state.type = "all"; state.staff = ""; state.q = ""; state.page = 1; state.status = "1";
            $("#sdcQuery").value = "";
            if (wasArchived) { reload(); return; }
        }
        else return; // downloads, PIN opens and Print ID Card are handled below

        render();
        if (d.del || d.restore) root.querySelector("[data-cancel]")?.focus();
    });

    root.addEventListener("keydown", (e) => {
        if (e.key !== "Escape" || !state.confirm || state.busy) return;
        const id = state.confirm.id;
        state.confirm = null;
        render();
        root.querySelector(`[data-del="${id}"],[data-restore="${id}"]`)?.focus();
    });

    let qTimer;
    $("#sdcQuery").addEventListener("input", (e) => {
        clearTimeout(qTimer);
        qTimer = setTimeout(() => { state.q = e.target.value; state.page = 1; render(); }, 120);
    });
    $("#sdcStaff").addEventListener("change", (e) => { state.staff = e.target.value; state.page = 1; render(); });
    $("#sdcStatus").addEventListener("change", (e) => {
        state.status = e.target.value === "2" ? "2" : "1";
        state.confirm = null;
        state.page = 1;
        reload();
    });
    $("#sdcSize").addEventListener("change", (e) => {
        state.size = +e.target.value || 0;
        remember("size", String(state.size));
        state.page = 1;
        render();
    });

    // Set by the upload dialog below.
    let openUpload = () => {};
    reload();
    return { reload, toast, setOpenUpload: (fn) => { openUpload = fn; } };
})();

/* ------------------------------------------- upload, ID card, PIN opens */

(function(){
    if (!document.getElementById("studentDocsApp")) return;

    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const uploadDocumentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadDocumentModal"));
    const downloadIDCard = tailwind.Modal.getOrCreateInstance(document.querySelector("#downloadIDCard"));

    const showWarning = (title, html) => {
        $('#warningModal .warningModalTitle').html(title);
        $('#warningModal .warningModalDesc').html(html);
        $('#warningModal .warningCloser').attr('data-action', 'DISMISS');
        warningModal.show();
    };

    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        warningModal.hide();
    });

    const downloadIDCardEl = document.getElementById('downloadIDCard')
    downloadIDCardEl.addEventListener('hide.tw.modal', function(event) {
        $('#downloadIDCard .idContent').html('').fadeOut('fast');
        $('#downloadIDCard .idLoader').fadeIn('fast');
    });

    /* Start Dropzone */
    let drzn1 = null;
    let documentLabelText = '';
    if($("#uploadDocumentForm").length > 0){
        let dzError = false;
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
        };

        let options = {
            accept: (file, done) => {
                done();
            },
        };

        drzn1 = new Dropzone('#uploadDocumentForm', options);

        drzn1.on('addedfile', function(file){
            if(file.name.match(/[`!@#$%^&*+\=\[\]{};':"\\|,<>\/?~]/)){
                $('#uploadDocumentModal .modal-content .uploadError').remove();
                $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! One of your selected file name contain validation error & that file has been removed.</div>');
                createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
                drzn1.removeFile(file);

                setTimeout(function(){
                    $('#uploadDocumentModal .modal-content .uploadError').remove();
                }, 5000)
            }
        });

        drzn1.on("maxfilesexceeded", (file) => {
            $('#uploadDocumentModal .modal-content .uploadError').remove();
            $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            setTimeout(function(){
                $('#uploadDocumentModal .modal-content .uploadError').remove();
            }, 2000)
        });

        drzn1.on("error", function(file, response){
            dzError = true;
        });

        drzn1.on("success", function(file, response){
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on('queuecomplete', function(){
            $('#uploadDocBtn').removeAttr('disabled');
            document.querySelector("#uploadDocBtn svg").style.cssText ="display: none;";

            if(!dzError){
                const count = drzn1.getAcceptedFiles().length;
                uploadDocumentModal.hide();
                docsApp.toast(count > 1 ? count + ' documents uploaded' : 'Document uploaded');
                docsApp.reload();
            }else{
                // The files that failed stay in the dialog, so they can be tried again.
                uploadDocumentModal.hide();
                showWarning("Error Found!", 'Something went wrong. Please try later or contact administrator.');
                docsApp.reload();
            }
        })

        $('#uploadDocumentModal [name="display_name"]').on('input', function(){
            var displayName = $(this).val();
            $('#uploadDocumentModal [name="display_file_name"]').val(displayName);
            $('#documentNameDisplay').text(documentLabelText + (displayName.length > 0 ? ' - ' : ' ') + displayName);
        });

        $('#uploadDocBtn').on('click', function(e){
            e.preventDefault();
            document.querySelector('#uploadDocBtn').setAttribute('disabled', 'disabled');
            document.querySelector("#uploadDocBtn svg").style.cssText ="display: inline-block;";

            if(drzn1.files.length > 0){
                dzError = false;
                // Both are switches, so each always has an answer: on is Yes, off is No.
                $('#uploadDocumentModal input[name="hard_copy_check"]').val($('#uploadHardCopyToggle').is(':checked') ? '1' : '0');
                $('#uploadDocumentModal input[name="is_encrypted"]').val($('#uploadEncryptToggle').is(':checked') ? '1' : '0');
                drzn1.processQueue();
            }else{
                $('#uploadDocumentModal .modal-content .uploadError').remove();
                $('#uploadDocumentModal .modal-content').prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select at least one file.</div>');

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function(){
                    $('#uploadDocumentModal .modal-content .uploadError').remove();
                    document.querySelector('#uploadDocBtn').removeAttribute('disabled', 'disabled');
                    document.querySelector("#uploadDocBtn svg").style.cssText ="display: none;";
                }, 2000)
            }
        });
    }
    /* End Dropzone */

    const uploadDocumentModalEl = document.getElementById('uploadDocumentModal')
    uploadDocumentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#uploadDocumentModal input[name="document_setting_id"]').val('0');
        $('#uploadDocumentModal input[name="hard_copy_check"]').val('0');
        $('#uploadDocumentModal input[name="is_encrypted"]').val('0');
        $('#uploadHardCopyToggle, #uploadEncryptToggle').prop('checked', false);
        $('#uploadDocumentModal input[name="display_file_name"]').val('');
        $('#uploadDocumentModal input[name="display_name"]').val('');
        document.querySelector('#uploadDocBtn').removeAttribute('disabled', 'disabled');
        document.querySelector("#uploadDocBtn svg").style.cssText ="display: none;";
        // Uploaded files leave the dialog; ones that failed stay to be retried.
        if(drzn1){
            drzn1.files.filter((f) => f.status === Dropzone.SUCCESS).forEach((f) => drzn1.removeFile(f));
        }
    });

    // Add Document › a document type: the upload dialog, for that type.
    docsApp.setOpenUpload(function(documentSettingId, label){
        documentLabelText = String(label).trim();
        $('#uploadDocumentModal input[name="document_setting_id"]').val(documentSettingId);
        $('#documentNameDisplay').text(documentLabelText);
        uploadDocumentModal.show();
    });

    // Encrypted documents: the PIN prompt and the open that follows it are
    // shared with the staff Documents tab (document-vault.js).
    const documentVault = initDocumentVault({
        table: '#studentDocsApp',
        openRoute: 'student.uploads.open.encrypted',
        showWarning: showWarning,
        onActivity: function(){
            logApp.reload();
        },
    });

    $('#studentDocsApp').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');
        if($theLink.hasClass('is-busy')) return;

        // Opened now, while this is still the click: a tab opened after the
        // response would be blocked as a pop-up.
        const viewer = window.open('', '_blank');
        $theLink.addClass('is-busy');

        axios({
            method: "post",
            url: route('student.document.download'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $theLink.removeClass('is-busy');
            let res = response.data.res;
            if(res != '' && viewer){
                viewer.location.href = res;
            }else if(res != ''){
                window.open(res, '_blank');
            }else if(viewer){
                viewer.close();
            }
            logApp.reload();
        }).catch(error => {
            $theLink.removeClass('is-busy');
            if(viewer) viewer.close();
            documentVault.showRefused(error);
        });
    });

    $(document).on('click', '#downloadIDCardBtn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var student_id = $btn.attr('data-studentid');

        axios({
            method: "post",
            url: route('student.download.id.card'),
            data: {student_id : student_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                downloadIDCard.show();
                document.getElementById('downloadIDCard').addEventListener('shown.tw.modal', function(event){
                    $('#downloadIDCard .idLoader').fadeOut('fast');
                    $('#downloadIDCard .idContent').fadeIn('fast').html(response.data.res);
                    createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
                }, { once: true });
            }
        }).catch(error => {
            docsApp.toast('The ID card could not be prepared. Please try again.', true);
        });
    })

    $('#downloadIDCard').on('click', '.thePrintBtn', function(){
        var $currentBtn = $(this);
        var currentIdAttr = $currentBtn.attr('data-id');
        var currentId = '#theIDCard_'+currentIdAttr;

        html2canvas(document.querySelector(currentId), { useCORS: true, allowTaint : true }).then(canvas => {
            canvas.toBlob(function(blob) {
                saveAs(blob, currentIdAttr+'.jpg');

                setTimeout(function(){
                    downloadIDCard.hide();
                }, 2000);
            });
        });
    });

})()
