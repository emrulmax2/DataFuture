import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";
import tippy, { roundArrow } from "tippy.js";
import { esc, fmtDate as fmt, plural, avatar as avatarOf, hl, memory, makeToast, printTable, downloadSheet, pagerHtml, bindMenu } from "./student-record-kit";

("use strict");

/*
 * Student notes page (/student/notes/{id}).
 *
 * The list: every note for the chosen status (Active / Archived) comes down in
 * one request — the busiest student has a little over a hundred — and the
 * rail, search, term grouping and paging all happen here, so the counts in the
 * rail always describe the same set the list is drawn from.
 *
 * The modals (add, edit, follow-up comments, formatted view) are the page's
 * existing ones; their handlers below are unchanged apart from refreshing this
 * list and reporting through the toast.
 */
const notesApp = (function () {
    const root = document.getElementById("studentNotesApp");
    if (!root) return { reload() {}, toast() {} };

    const studentId = root.dataset.student || "0";
    const studentName = root.dataset.studentName || "";
    const studentRef = root.dataset.studentRef || "";
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute("content");
    const $ = (sel) => root.querySelector(sel);

    /* ------------------------------------------------------------ helpers */

    const sv = (w, d, sw) =>
        `<svg width="${w}" height="${w}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${sw || 2}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${d}</svg>`;
    const I = {
        flag: sv(12, '<path d="M4 22V4s1-1 4-1 5 2 8 2 4-1 4-1v11s-1 1-4 1-5-2-8-2-4 1-4 1"/>', 2.4),
        check: sv(12, '<path d="m5 12 5 5 9-10"/>', 3),
        clock: sv(12, '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>', 2.4),
        edit: sv(16, '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>'),
        reply: sv(16, '<path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/><path d="M12 7v6M9 10h6"/>'),
        done: sv(16, '<circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/>'),
        trash: sv(16, '<path d="M3 6h18M8 6V4h8v2M6 6l1 14a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-14M10 11v6M14 11v6"/>'),
        restore: sv(16, '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>'),
        clip: sv(14, '<path d="m21.4 11.1-9.2 9.2a6 6 0 0 1-8.5-8.5l9.2-9.2a4 4 0 0 1 5.7 5.7l-9.2 9.2a2 2 0 0 1-2.8-2.8l8.5-8.5"/>'),
        open: sv(14, '<path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>'),
    };

    const avatar = (n) => avatarOf(n, "snt-av");
    const toneOf = (c) => {
        c = String(c || "").toLowerCase().replace(/^bg-/, "");
        if (c === "warning" || c === "success" || c === "danger") return c;
        if (c === "primary" || c === "info") return "info";
        return "danger";
    };
    const remember = memory("studentNotes");

    /* -------------------------------------------------------------- state */

    const state = {
        status: "1",
        type: "all",
        term: "",
        q: "",
        view: remember("view") === "table" ? "table" : "feed",
        sort: "desc",
        page: 1,
        size: ["10", "25", "50", "0"].includes(remember("size")) ? +remember("size") : 10,
        open: new Set(),
        confirm: null, // { id, kind: 'del' | 'restore' | 'complete' }
        busy: false,
    };
    let notes = [];
    let loading = true;
    let failed = false;
    let requestNo = 0;

    const types = [
        { key: "all", label: "All notes", dot: "", test: () => true },
        { key: "flag", label: "Flagged", dot: "flag", test: (n) => n.flagOn },
        { key: "follow", label: "Follow-up", dot: "follow", test: (n) => n.follow },
    ];

    const norm = (n) => {
        const names = Array.isArray(n.followed_names) ? n.followed_names : [];
        const flagged = n.is_flaged === "Yes" && +n.student_flag_id > 0;
        const out = {
            raw: n,
            id: +n.id,
            text: n.note_text ?? n.note ?? "",
            rich: !!n.note_rich,
            by: n.created_by || "Unknown",
            date: n.date_iso || "",
            // Keyed by name: the same term name can exist under two ids
            // (two "2026 September" declarations), and staff only see names.
            termKey: n.term ? String(n.term) : "0",
            termName: n.term || "No term",
            termStart: n.term_start || "",
            flagOn: flagged && n.flaged_status !== "Cleared",
            flagCleared: flagged && n.flaged_status === "Cleared",
            flagName: n.flag_name || "Flag",
            tone: toneOf(n.flag_color),
            follow: n.followed_up === "yes",
            followStatus: n.followed_up_status || "",
            names,
            completedBy: n.completed_by || "",
            completedDate: n.completed_iso || "",
            mine: +n.am_i_followed === 1,
            unread: +n.unread_comment || 0,
            owner: +n.is_ownere === 1,
            docId: +n.note_document_id || 0,
            docName: n.document_name || "",
            deleted: !!n.deleted_at,
        };
        out.hay = [out.text, out.by, out.id, out.termName, flagged ? out.flagName : "", names.join(" "), out.completedBy, out.docName]
            .join(" ")
            .toLowerCase();
        return out;
    };

    function filtered() {
        const q = state.q.trim().toLowerCase();
        const type = types.find((t) => t.key === state.type) || types[0];
        const dir = state.sort === "asc" ? 1 : -1;
        return notes
            .filter((n) => type.test(n) && (!state.term || n.termKey === state.term) && (!q || n.hay.includes(q)))
            .sort((a, b) => (a.date < b.date ? -1 : a.date > b.date ? 1 : a.id - b.id) * dir);
    }

    // Term groups in the order they first appear in the (sorted) list.
    const groups = (list) => {
        const m = new Map();
        list.forEach((n) => {
            if (!m.has(n.termKey)) m.set(n.termKey, { name: n.termName, items: [] });
            m.get(n.termKey).items.push(n);
        });
        return [...m.values()];
    };

    /* ------------------------------------------------------------- pieces */

    function actions(n) {
        const c = state.confirm;
        if (c && c.id === n.id) {
            const words = {
                del: ["Delete this note?", "Delete", "Deleting…", ""],
                restore: ["Restore this note?", "Restore", "Restoring…", " snt-yes--ok"],
                complete: ["Mark the follow-up complete?", "Complete", "Saving…", " snt-yes--ok"],
            }[c.kind];
            return `<div class="snt-confirm" role="group" aria-label="${esc(words[0])}">${esc(words[0])}`
                + `<button type="button" data-cancel ${state.busy ? "disabled" : ""}>Cancel</button>`
                + `<button type="button" class="snt-yes${words[3]}" data-yes="${n.id}" ${state.busy ? "disabled" : ""}>${state.busy ? words[2] : words[1]}</button></div>`;
        }
        let html = `<div class="snt-acts">`;
        if (n.follow) {
            html += `<button type="button" class="snt-ib viewCommentBtn" data-id="${n.id}" data-tw-toggle="modal" data-tw-target="#followUpCommentModal" title="Follow-up comments" aria-label="Follow-up comments for note ${n.id}${n.unread ? `, ${n.unread} unread` : ""}">${I.reply}${n.unread ? `<span class="snt-badge" aria-hidden="true">${n.unread}</span>` : ""}</button>`;
            if (n.mine && n.followStatus === "Pending" && !n.deleted)
                html += `<button type="button" class="snt-ib snt-ib--done" data-complete="${n.id}" title="Mark follow-up complete" aria-label="Mark follow-up complete for note ${n.id}">${I.done}</button>`;
        }
        if (!n.deleted) {
            if (n.owner)
                html += `<button type="button" class="snt-ib edit_btn" data-id="${n.id}" data-tw-toggle="modal" data-tw-target="#editNoteModal" title="Edit note" aria-label="Edit note ${n.id}">${I.edit}</button>`;
            html += `<button type="button" class="snt-ib snt-ib--del" data-del="${n.id}" title="Delete note" aria-label="Delete note ${n.id}">${I.trash}</button>`;
        } else {
            html += `<button type="button" class="snt-ib" data-restore="${n.id}" title="Restore note" aria-label="Restore note ${n.id}">${I.restore}</button>`;
        }
        return html + `</div>`;
    }

    function noteText(n, q, limit) {
        const long = n.text.length > limit || (n.text.match(/\n/g) || []).length > 2;
        const open = state.open.has(n.id) || !!q;
        return `<p class="snt-text${long && !open ? " snt-text--clamp" : ""}">${hl(n.text, q) || '<span class="snt-none">No text</span>'}</p>`
            + (long && !q ? `<button type="button" class="snt-link snt-more" data-more="${n.id}" aria-expanded="${open}">${open ? "Show less" : "Show more"}</button>` : "");
    }

    function extras(n, q) {
        let html = "";
        if (n.docId > 0)
            html += `<button type="button" class="snt-attach downloadDoc" data-id="${n.docId}" title="Download ${esc(n.docName || "attachment")}">${I.clip}<span>${hl(n.docName || "Attachment", q)}</span></button>`;
        if (n.rich)
            html += `<button type="button" class="snt-link view_btn" data-id="${n.id}">${I.open}Open formatted note</button>`;
        return html ? `<div class="snt-extras">${html}</div>` : "";
    }

    const flagPill = (n) => (n.flagOn ? `<span class="snt-pill snt-pill--flag snt-tone-${n.tone}">${I.flag}${esc(n.flagName)}</span>` : "");

    // Follow-up / cleared flag line for the timeline.
    function statusLine(n, q) {
        const parts = [];
        if (n.follow) {
            if (n.followStatus === "Completed") {
                parts.push(`<span class="snt-pill snt-pill--done">${I.check}Follow-up completed</span>`);
                if (n.completedBy || n.completedDate)
                    parts.push(`<span>${hl(n.completedBy, q)}${n.completedDate ? ` <time datetime="${n.completedDate}">&middot; ${fmt(n.completedDate)}</time>` : ""}</span>`);
            } else {
                parts.push(n.followStatus === "Pending"
                    ? `<span class="snt-pill snt-pill--pending">${I.clock}Follow-up pending</span>`
                    : `<span class="snt-pill snt-pill--plain">Follow-up</span>`);
                if (n.names.length) parts.push(`<span>Assigned to ${hl(n.names.join(", "), q)}</span>`);
            }
        }
        if (n.flagCleared) parts.push(`<span class="snt-pill snt-pill--cleared">${I.flag}Flag cleared &middot; ${esc(n.flagName)}</span>`);
        return parts.length ? `<div class="snt-status">${parts.join("")}</div>` : "";
    }

    function entryClass(n) {
        if (n.flagOn) return ` snt-entry--flag snt-tone-${n.tone}`;
        if (n.follow && n.followStatus === "Pending") return " snt-entry--pending";
        if (n.follow && n.followStatus === "Completed") return " snt-entry--done";
        return "";
    }

    function feed(pageList, q, counts) {
        return `<div class="snt-feed">` + groups(pageList).map((g) =>
            `<section><h3 class="snt-term">${esc(g.name)}<span>${plural(counts.get(g.items[0].termKey) || g.items.length, "note")}</span></h3>`
            + g.items.map((n) =>
                `<article class="snt-note" aria-label="Note ${n.id}"><div class="snt-tl">${avatar(n.by)}<i></i></div>
                    <div class="snt-entry${entryClass(n)}${n.deleted ? " snt-entry--archived" : ""}">
                        <div class="snt-meta"><strong>${hl(n.by, q)}</strong>${n.date ? `<time datetime="${n.date}">${fmt(n.date)}</time>` : ""}<span class="snt-id">#${hl(n.id, q)}</span>${flagPill(n)}${actions(n)}</div>
                        ${noteText(n, q, 240)}${extras(n, q)}${statusLine(n, q)}
                    </div></article>`).join("")
            + `</section>`).join("") + `</div>`;
    }

    function tableStatus(n, q) {
        const out = [];
        if (n.flagOn) out.push(flagPill(n));
        if (n.flagCleared) out.push(`<span class="snt-pill snt-pill--cleared">${I.flag}Cleared &middot; ${esc(n.flagName)}</span>`);
        if (n.follow) {
            if (n.followStatus === "Completed") {
                out.push(`<span class="snt-pill snt-pill--done">${I.check}Completed</span>`);
                if (n.completedBy || n.completedDate)
                    out.push(`<div class="snt-sub">${hl(n.completedBy, q)}${n.completedDate ? ` &middot; ${fmt(n.completedDate)}` : ""}</div>`);
            } else {
                out.push(n.followStatus === "Pending"
                    ? `<span class="snt-pill snt-pill--pending">${I.clock}Pending</span>`
                    : `<span class="snt-pill snt-pill--plain">Follow-up</span>`);
                if (n.names.length) out.push(`<div class="snt-sub">${hl(n.names.join(", "), q)}</div>`);
            }
        }
        return out.length ? `<div class="snt-cell-stack">${out.join("")}</div>` : `<span class="snt-none" aria-label="No flag or follow-up">&mdash;</span>`;
    }

    function table(pageList, q, counts) {
        return `<div class="snt-table-wrap"><table class="snt-table">
            <colgroup><col class="snt-c-date"><col><col class="snt-c-status"><col class="snt-c-by"><col class="snt-c-act"></colgroup>
            <thead><tr><th scope="col">Date</th><th scope="col">Note</th><th scope="col">Flag &amp; follow-up</th><th scope="col">Added by</th><th scope="col">Actions</th></tr></thead><tbody>`
            + groups(pageList).map((g) =>
                `<tr class="snt-group"><td colspan="5">${esc(g.name)}<span>${plural(counts.get(g.items[0].termKey) || g.items.length, "note")}</span></td></tr>`
                + g.items.map((n) =>
                    `<tr${n.flagOn ? ` class="snt-row--flag snt-tone-${n.tone}"` : ""}>
                        <td><span class="snt-date">${fmt(n.date) || "&mdash;"}</span><span class="snt-id">#${hl(n.id, q)}</span></td>
                        <td>${noteText(n, q, 150)}${extras(n, q)}</td>
                        <td>${tableStatus(n, q)}</td>
                        <td><div class="snt-by">${avatar(n.by)}<span>${hl(n.by, q)}</span></div></td>
                        <td>${actions(n)}</td></tr>`).join("")).join("")
            + `</tbody></table></div>`;
    }

    const pager = (pages) => pagerHtml(state.page, pages, "snt-gap");

    /* ------------------------------------------------------------- render */

    function render() {
        // Keep keyboard focus on the same control across a redraw.
        const a = document.activeElement;
        let refocus = null;
        if (a && root.contains(a) && a.dataset) {
            for (const k of ["type", "term", "view", "page", "more", "cancel", "yes", "del", "restore", "complete"]) {
                if (k in a.dataset) { refocus = `[data-${k}="${CSS.escape(a.dataset[k])}"]`; break; }
            }
        }

        const archived = state.status === "2";
        const termMap = new Map();
        notes.forEach((n) => {
            if (!termMap.has(n.termKey)) termMap.set(n.termKey, { name: n.termName, count: 0, start: "" });
            const t = termMap.get(n.termKey);
            t.count++;
            if (n.termStart > t.start) t.start = n.termStart;
        });
        if (state.term && !termMap.has(state.term)) state.term = "";
        // Most recent term first (by the term's start date), notes without a term last.
        const terms = [...termMap.entries()].sort((x, y) =>
            x[0] === "0" ? 1 : y[0] === "0" ? -1 : y[1].start < x[1].start ? -1 : y[1].start > x[1].start ? 1 : x[1].name.localeCompare(y[1].name));

        // Rail
        $("#sntRail").innerHTML = loading && !notes.length
            ? `<div><h3>Show</h3>${'<div class="snt-rail-skel"></div>'.repeat(3)}</div>`
            : `<div><h3 id="sntRailShow">Show</h3><ul aria-labelledby="sntRailShow">` + types.map((t) =>
                `<li><button type="button" data-type="${t.key}" aria-pressed="${state.type === t.key}"><span class="snt-dot${t.dot ? " snt-dot--" + t.dot : ""}" aria-hidden="true"></span><span class="snt-rail-label">${t.label}</span><span class="snt-n">${notes.filter(t.test).length}</span></button></li>`).join("")
            + `</ul></div><div><h3 id="sntRailTerm">Term</h3><ul aria-labelledby="sntRailTerm"><li><button type="button" data-term="" aria-pressed="${!state.term}"><span class="snt-rail-label">All terms</span><span class="snt-n">${notes.length}</span></button></li>`
            + terms.map(([key, t]) => `<li><button type="button" data-term="${esc(key)}" aria-pressed="${state.term === key}" title="${esc(t.name)}"><span class="snt-rail-label">${esc(t.name)}</span><span class="snt-n">${t.count}</span></button></li>`).join("")
            + `</ul></div>`;

        // View switch + sort
        root.querySelectorAll("#sntViews [data-view]").forEach((b) => b.setAttribute("aria-pressed", String(b.dataset.view === state.view)));
        $("#sntSort span").textContent = state.sort === "desc" ? "Newest first" : "Oldest first";
        $("#sntSort").setAttribute("aria-label", `Sort order: ${state.sort === "desc" ? "newest" : "oldest"} first. Change to ${state.sort === "desc" ? "oldest" : "newest"} first.`);

        const q = state.q.trim().toLowerCase();
        const list = filtered();
        const isFiltered = !!q || !!state.term || state.type !== "all";
        const pages = state.size > 0 ? Math.max(1, Math.ceil(list.length / state.size)) : 1;
        if (state.page > pages) state.page = pages;
        if (state.page < 1) state.page = 1;
        const from = state.size > 0 ? (state.page - 1) * state.size : 0;
        const pageList = state.size > 0 ? list.slice(from, from + state.size) : list;
        const counts = new Map();
        list.forEach((n) => counts.set(n.termKey, (counts.get(n.termKey) || 0) + 1));

        const view = $("#sntView");
        view.setAttribute("aria-busy", String(loading));
        if (loading && !notes.length) {
            view.innerHTML = `<div class="snt-empty">Loading notes…</div>`;
        } else if (failed) {
            view.innerHTML = `<div class="snt-empty"><strong>Notes could not be loaded</strong>Check your connection and try again.<br><button type="button" class="snt-link" data-retry>Try again</button></div>`;
        } else if (!notes.length) {
            view.innerHTML = archived
                ? `<div class="snt-empty"><strong>No archived notes</strong>Deleted notes for this student would appear here.</div>`
                : `<div class="snt-empty"><strong>No notes yet</strong>Use Add Note to record the first one.</div>`;
        } else if (!list.length) {
            view.innerHTML = `<div class="snt-empty"><strong>No notes match these filters</strong>Try another term or clear the search.<br><button type="button" class="snt-link" data-clear>Clear filters</button></div>`;
        } else {
            view.innerHTML = state.view === "table" ? table(pageList, q, counts) : feed(pageList, q, counts);
        }

        $("#sntResult").textContent = !loading && notes.length && isFiltered ? `Showing ${list.length} of ${plural(notes.length, archived ? "archived note" : "note")}` : "";
        $("#sntSort").hidden = !notes.length || failed;
        $("#sntReset").hidden = !(isFiltered || archived);
        $("#sntRange").textContent = list.length ? `${from + 1}–${from + pageList.length} of ${list.length}` : "0 of 0";
        $("#sntPager").innerHTML = pager(pages);
        $("#sntSize").value = String(state.size);
        $("#sntStatus").value = state.status;

        // Summary under the title
        if (loading && !notes.length) {
            $("#sntSummary").textContent = "Loading notes…";
        } else if (failed) {
            $("#sntSummary").textContent = "Notes could not be loaded";
        } else if (archived) {
            $("#sntSummary").textContent = notes.length ? `${plural(notes.length, "archived note")}` : "No archived notes";
        } else if (notes.length) {
            const latest = notes.map((n) => n.date).filter(Boolean).sort().pop();
            const pending = notes.filter((n) => n.follow && n.followStatus === "Pending").length;
            $("#sntSummary").textContent = [
                plural(notes.length, "note"),
                `${notes.filter((n) => n.flagOn).length} flagged`,
                plural(notes.filter((n) => n.follow).length, "follow-up") + (pending ? ` (${pending} pending)` : ""),
                latest ? `latest ${fmt(latest)}` : "",
            ].filter(Boolean).join(" · ");
        } else {
            $("#sntSummary").textContent = "No notes yet";
        }

        if (refocus) {
            const el = root.querySelector(refocus);
            if (el) el.focus({ preventScroll: true });
        }
    }

    /* --------------------------------------------------------------- data */

    function reload() {
        const mine = ++requestNo;
        loading = true;
        failed = false;
        render();
        return axios
            .get(route("student.note.list"), { params: { studentId, status: state.status, size: "true" } })
            .then((res) => {
                if (mine !== requestNo) return;
                notes = (res.data && Array.isArray(res.data.data) ? res.data.data : []).map(norm);
                const ids = new Set(notes.map((n) => n.id));
                state.open.forEach((id) => { if (!ids.has(id)) state.open.delete(id); });
                loading = false;
                render();
            })
            .catch(() => {
                if (mine !== requestNo) return;
                loading = false;
                failed = true;
                notes = [];
                render();
            });
    }

    const toast = makeToast(document.getElementById("sntToast"));

    function runConfirmed() {
        const c = state.confirm;
        if (!c || state.busy) return;
        state.busy = true;
        render();
        const headers = { "X-CSRF-TOKEN": csrf() };
        const req = {
            del: () => axios({ method: "delete", url: route("student.destory.note"), data: { student: studentId, recordid: c.id }, headers }),
            restore: () => axios({ method: "post", url: route("student.resotore.note"), data: { student: studentId, recordid: c.id }, headers }),
            complete: () => axios({ method: "post", url: route("followups.completed"), data: { recordid: c.id }, headers }),
        }[c.kind];
        const done = { del: "Note deleted", restore: "Note restored", complete: "Follow-up marked complete" }[c.kind];
        req()
            .then(() => {
                state.confirm = null;
                state.busy = false;
                toast(done);
                return reload();
            })
            .catch(() => {
                state.confirm = null;
                state.busy = false;
                render();
                toast("That did not go through. Please try again.", true);
            });
    }

    /* ------------------------------------------------------ print / export */

    function statusText(n) {
        const parts = [];
        if (n.flagOn) parts.push(`Flag: ${n.flagName}`);
        if (n.flagCleared) parts.push(`Flag cleared: ${n.flagName}`);
        if (n.follow) {
            let s = `Follow-up${n.followStatus ? " " + n.followStatus.toLowerCase() : ""}`;
            if (n.followStatus === "Completed" && (n.completedBy || n.completedDate)) s += ` (${[n.completedBy, fmt(n.completedDate)].filter(Boolean).join(", ")})`;
            else if (n.names.length) s += ` (assigned to ${n.names.join(", ")})`;
            parts.push(s);
        }
        return parts.join("; ");
    }

    function filterLabel() {
        const bits = [state.status === "2" ? "Archived notes" : "Active notes"];
        const type = types.find((t) => t.key === state.type);
        if (type && type.key !== "all") bits.push(type.label);
        if (state.term) bits.push((notes.find((n) => n.termKey === state.term) || {}).termName || "");
        if (state.q.trim()) bits.push(`matching “${state.q.trim()}”`);
        return bits.filter(Boolean).join(" · ");
    }

    function printNotes() {
        const list = filtered();
        if (!list.length) { toast("There are no notes to print with these filters.", true); return; }
        printTable({
            title: `Notes — ${studentName}${studentRef ? " (" + studentRef + ")" : ""}`,
            subtitle: `${filterLabel()} · ${plural(list.length, "note")}`,
            headers: ["ID", "Term", "Date", "Note", "Flag & follow-up", "Added by"],
            rows: list.map((n) => [`#${n.id}`, esc(n.termName), esc(fmt(n.date)),
                esc(n.text) + (n.docName ? `<div class="m">Attachment: ${esc(n.docName)}</div>` : ""), esc(statusText(n)), esc(n.by)]),
            wrapCols: [3],
        });
    }

    function exportNotes(kind) {
        const list = filtered();
        if (!list.length) { toast("There are no notes to export with these filters.", true); return; }
        const rows = list.map((n) => ({
            "ID": n.id,
            "Term": n.termName,
            "Date": fmt(n.date),
            "Note": n.text,
            "Flag": n.flagOn || n.flagCleared ? n.flagName : "",
            "Flag Status": n.flagOn ? "Active" : n.flagCleared ? "Cleared" : "",
            "Follow-up": n.follow ? n.followStatus || "Yes" : "",
            "Assigned To": n.names.join(", "),
            "Completed By": n.completedBy,
            "Completed On": fmt(n.completedDate),
            "Added By": n.by,
            "Attachment": n.docName,
            "Status": n.deleted ? "Archived" : "Active",
        }));
        downloadSheet({ rows, sheet: "Student Note Details", base: `student-notes-${(studentRef || studentId).replace(/[^\w-]+/g, "")}`, kind });
    }

    /* ------------------------------------------------------------- events */

    const exportMenu = bindMenu($("#sntExportBtn"), $("#sntExportMenu"));

    root.addEventListener("click", (e) => {
        const b = e.target.closest("button");
        if (!b || !root.contains(b)) return;
        const d = b.dataset;

        if (d.export) { exportMenu.close(); exportNotes(d.export); return; }
        if (b.id === "sntPrint") { printNotes(); return; }

        if (d.type) { state.type = d.type; state.page = 1; }
        else if ("term" in d) { state.term = d.term; state.page = 1; }
        else if (d.view) { state.view = d.view; remember("view", d.view); }
        else if (d.page) {
            state.page = +d.page;
            render();
            // Back to the top of the list when the pager sits below the fold.
            if (root.getBoundingClientRect().top < 0) root.scrollIntoView({ block: "start" });
            return;
        }
        else if (d.more) { const id = +d.more; state.open.has(id) ? state.open.delete(id) : state.open.add(id); }
        else if (d.del) { state.confirm = { id: +d.del, kind: "del" }; }
        else if (d.restore) { state.confirm = { id: +d.restore, kind: "restore" }; }
        else if (d.complete) { state.confirm = { id: +d.complete, kind: "complete" }; }
        else if ("cancel" in d) { if (state.busy) return; state.confirm = null; }
        else if (d.yes) { runConfirmed(); return; }
        else if ("retry" in d) { reload(); return; }
        else if (b.id === "sntSort") { state.sort = state.sort === "desc" ? "asc" : "desc"; state.page = 1; }
        else if (b.id === "sntReset" || "clear" in d) {
            const wasArchived = state.status === "2";
            state.type = "all"; state.term = ""; state.q = ""; state.page = 1; state.status = "1";
            $("#sntQuery").value = "";
            if (wasArchived) { reload(); return; }
        }
        else return; // modal triggers, downloads etc. are handled further down

        render();
        if (d.del || d.restore || d.complete) root.querySelector("[data-cancel]")?.focus();
    });

    root.addEventListener("keydown", (e) => {
        if (e.key !== "Escape") return;
        if (state.confirm && !state.busy) {
            const id = state.confirm.id;
            state.confirm = null;
            render();
            root.querySelector(`[data-del="${id}"],[data-restore="${id}"],[data-complete="${id}"]`)?.focus();
        }
    });

    let qTimer;
    $("#sntQuery").addEventListener("input", (e) => {
        clearTimeout(qTimer);
        qTimer = setTimeout(() => { state.q = e.target.value; state.page = 1; render(); }, 120);
    });
    $("#sntStatus").addEventListener("change", (e) => {
        state.status = e.target.value === "2" ? "2" : "1";
        state.confirm = null;
        state.page = 1;
        reload();
    });
    $("#sntSize").addEventListener("change", (e) => {
        state.size = +e.target.value || 0;
        remember("size", String(state.size));
        state.page = 1;
        render();
    });

    reload();
    return { reload, toast };
})();

(function(){
    if (!document.getElementById("studentNotesApp")) return;

    let tomOptionsNote = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',
        //persist: true,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let multiTomOptNote = {
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',

        ...tomOptionsNote,
        plugins: {
            ...tomOptionsNote.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var note_term_declaration_id = new TomSelect('#note_term_declaration_id', tomOptionsNote);
    var edit_note_term_declaration_id = new TomSelect('#edit_note_term_declaration_id', tomOptionsNote);
    var follow_up_by = new TomSelect('#follow_up_by', multiTomOptNote);
    var edit_follow_up_by = new TomSelect('#edit_follow_up_by', multiTomOptNote);

    const addNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addNoteModal"));
    const viewNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewNoteModal"));
    const editNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editNoteModal"));

    let addEditor;
    if($("#addEditor").length > 0){
        const el = document.getElementById('addEditor');
        ClassicEditor.create(el).then((editor) => {
            addEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let editEditor;
    if($("#editEditor").length > 0){
        const el = document.getElementById('editEditor');
        ClassicEditor.create(el).then((editor) => {
            editEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    const addNoteModalEl = document.getElementById('addNoteModal')
    addNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addNoteModal .acc__input-error').html('');
        $('#addNoteModal input[name="document"]').val('');
        $('#addNoteModal #addNoteDocumentName').html('');

        $('#addNoteModal [name="followed_up"]').prop('checked', false);
        $('#addNoteModal .followedUpWrap').fadeOut('fast', function(){
            follow_up_by.clear(true);
        });
        $('#addNoteModal [name="is_flaged"]').prop('checked', false);
        $('#addNoteForm .flagedWrap').fadeOut('fast', function(){
            $('#addNoteForm [name="student_flag_id"]').val('');
            $('#addNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
        });

        addEditor.setData('');
        note_term_declaration_id.clear(true);
    });

    const editNoteModalEl = document.getElementById('editNoteModal')
    editNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editNoteModal .acc__input-error').html('');
        $('#editNoteModal input[name="opening_date"]').val('');
        $('#editNoteModal input[name="document"]').val('');
        $('#editNoteModal #editNoteDocumentName').html('');
        $('#editNoteModal input[name="id"]').val('0');
        $('#editNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();

        $('#editNoteForm .theFollowUpCover').removeClass('active');
        $('#editNoteModal [name="followed_up"]').prop('checked', false);
        $('#editNoteModal .followedUpWrap').fadeOut('fast', function(){
            edit_follow_up_by.clear(true);
        });
        $('#editNoteForm .theFlagCover').removeClass('active');
        $('#editNoteModal [name="is_flaged"]').prop('checked', false);
        $('#editNoteModal .flagedWrap').fadeOut('fast', function(){
            $('#editNoteModal [name="student_flag_id"]').val('');
            $('#editNoteModal .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
        });

        editEditor.setData('');
        edit_note_term_declaration_id.clear(true);
    });

    /* Opening the comments marks them read, so the unread badge in the list
       is refreshed once the conversation is closed again. */
    let commentsOpened = false;
    const followUpCommentModalEl = document.getElementById('followUpCommentModal')
    followUpCommentModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#followUpCommentModal textarea').val('');
        $('#followUpCommentModal [name="student_note_id"]').val('0');
        $('#followUpCommentModal #followUpCommentWrap').html('Loading...');
        if(commentsOpened){
            commentsOpened = false;
            notesApp.reload();
        }
    });

    const viewNoteModalEl = document.getElementById('viewNoteModal')
    viewNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#viewNoteModal .modal-body').html('');
        $('#viewNoteModal .modal-footer .footerBtns').html('');
    });

    $('#addNoteForm').on('change', '#addNoteDocument', function(){
        showFileName('addNoteDocument', 'addNoteDocumentName');
    });

    $('#editNoteForm').on('change', '#editNoteDocument', function(){
        showFileName('editNoteDocument', 'editNoteDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };

    $('#addNoteForm').on('change', '[name="followed_up"]', function(){
        let self_id = $('#addNoteModal #follow_up_by').attr('data-self');
        if($(this).prop('checked')){
            $('#addNoteForm .followedUpWrap').fadeIn('fast', function(){
                follow_up_by.clear(true);
                follow_up_by.addItem(self_id);
            });
        }else{
            $('#addNoteForm .followedUpWrap').fadeOut('fast', function(){
                follow_up_by.clear(true);
            });
        }
    });

    $('#editNoteForm').on('change', '[name="followed_up"]', function(){
        if($(this).prop('checked')){
            $('#editNoteForm .followedUpWrap').fadeIn('fast', function(){
                edit_follow_up_by.clear(true);
            });
        }else{
            $('#editNoteForm .followedUpWrap').fadeOut('fast', function(){
                edit_follow_up_by.clear(true);
            });
        }
    });

    $('#addNoteForm').on('change', '[name="is_flaged"]', function(){
        if($(this).prop('checked')){
            $('#addNoteForm .flagedWrap').fadeIn('fast', function(){
                $('#addNoteForm [name="student_flag_id"]').val('');
                $('#addNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
            });
        }else{
            $('#addNoteForm .flagedWrap').fadeOut('fast', function(){
                $('#addNoteForm [name="student_flag_id"]').val('');
                $('#addNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
            });
        }
    });

    $('#addNoteForm').on('change', '[name="student_flag_id"]', function(){
        if($(this).val() != ''){
            var color = $('option:selected', this).attr('data-color');
            $('#addNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-'+color.toLowerCase());
        }else{
            $('#addNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
        }
    });

    $('#editNoteForm').on('change', '[name="is_flaged"]', function(){
        if($(this).prop('checked')){
            $('#editNoteForm .flagedWrap').fadeIn('fast', function(){
                $('#editNoteForm [name="student_flag_id"]').val('');
                $('#editNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
            });
        }else{
            $('#editNoteForm .flagedWrap').fadeOut('fast', function(){
                $('#editNoteForm [name="student_flag_id"]').val('');
                $('#editNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
            });
        }
    });

    $('#editNoteForm').on('change', '[name="student_flag_id"]', function(){
        if($(this).val() != ''){
            var color = $('option:selected', this).attr('data-color');
            $('#editNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-'+color.toLowerCase());
        }else{
            $('#editNoteForm .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200');
        }
    });

    /* "Open formatted note": the stored HTML in the view modal, for the notes
       whose tables, lists or links do not survive as plain text. */
    $('#studentNotesApp').on('click', '.view_btn', function(e){
        e.preventDefault();
        e.stopPropagation();
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        $('#viewNoteModal .modal-body').html('<div class="text-slate-500 font-medium">Loading note...</div>');
        $('#viewNoteModal .modal-footer .footerBtns').html('');
        viewNoteModal.show();
        axios({
            method: "post",
            url: route('student.show.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $('#viewNoteModal .modal-body').html(response.data.message);
            if(response.data.btns != ''){
                $('#viewNoteModal .modal-footer .footerBtns').html(response.data.btns);
            }
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }).catch(error => {
            $('#viewNoteModal .modal-body').html('<div class="alert alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Unable to load this note. Please try again.</div>');
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });
    })

    $('#addNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addNoteForm');

        document.querySelector('#saveNote').setAttribute('disabled', 'disabled');
        document.querySelector("#saveNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addNoteForm input[name="document"]')[0].files[0]);
        form_data.append("content", addEditor.getData());
        axios({
            method: "post",
            url: route('student.store.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addNoteModal.hide();
                notesApp.toast('Note added');
            }
            notesApp.reload();
        }).catch(error => {
            document.querySelector('#saveNote').removeAttribute('disabled');
            document.querySelector("#saveNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addNoteForm .${key}`).addClass('border-danger');
                        $(`#addNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    notesApp.toast('The note could not be saved. Please try again.', true);
                }
            }
        });
    });

    $('#studentNotesApp').on('click', '.edit_btn', function(e){
        var $btn = $(this);
        var noteId = $btn.attr('data-id');
        axios({
            method: "post",
            url: route('student.get.note'),
            data: {noteId : noteId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            let dataset = response.data.res;
            editEditor.setData(dataset.note ? dataset.note : '');
            $('#editNoteModal [name="opening_date"]').val(dataset.opening_date ? dataset.opening_date : '');
            $('#editNoteModal input[name="id"]').val(noteId);

            if(dataset.term_declaration_id){
                edit_note_term_declaration_id.addItem(dataset.term_declaration_id, true)
            }else{
                edit_note_term_declaration_id.clear(true);
            }
            if(dataset.docURL != ''){
                $('#editNoteModal .downloadExistAttachment').attr('href', dataset.docURL).fadeIn();
            }else{
                $('#editNoteModal .downloadExistAttachment').attr('href', '#').fadeOut();
            }

            if(dataset.followed_up == 'yes'){
                $('#editNoteForm [name="followed_up"]').prop('checked', true);
                $('#editNoteForm .followedUpWrap').fadeIn('fast', function(){
                    $('#editNoteModal select[name="followed_up_status"]').val(dataset.followed_up_status ? dataset.followed_up_status : '');
                    if(dataset.followed_by){
                        edit_follow_up_by.clear(true);

                        $.each(dataset.followed_by, function(index, id) {
                            edit_follow_up_by.addItem(id, true);
                        });
                    }else{
                        edit_follow_up_by.clear(true);
                    }
                });
                if(dataset.edit_followup != 1){
                    $('#editNoteForm .theFollowUpCover').addClass('active');
                }else{
                    $('#editNoteForm .theFollowUpCover').removeClass('active');
                }
            }else{
                $('#editNoteForm [name="followed_up"]').prop('checked', false);
                $('#editNoteForm .followedUpWrap').fadeOut('fast', function(){
                    $('#editNoteModal select[name="followed_up_status"]').val('');
                    edit_follow_up_by.clear(true);
                });
            }
            if(dataset.is_flaged == 'Yes'){
                var color = (dataset.flag_color ? dataset.flag_color : 'bg-slate-200');
                $('#editNoteForm [name="is_flaged"]').prop('checked', true);
                $('#editNoteForm .flagedWrap').fadeIn('fast', function(){
                    $('#editNoteModal [name="student_flag_id"]').val(dataset.student_flag_id);
                    $('#editNoteModal .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-'+color.toLowerCase())
                });
                if(dataset.edit_flag != 1){
                    $('#editNoteForm .theFlagCover').addClass('active');
                }else{
                    $('#editNoteForm .theFlagCover').removeClass('active');
                }

                if(dataset.flaged_status == 'Active'){
                    $('#editNoteForm [name="flaged_status"]').prop('checked', false);
                }else{
                    $('#editNoteForm [name="flaged_status"]').prop('checked', true);
                }
            }else{
                $('#editNoteForm [name="is_flaged"]').prop('checked', false);
                $('#editNoteForm .flagedWrap').fadeOut('fast', function(){
                    $('#editNoteModal [name="student_flag_id"]').val('');
                    $('#editNoteModal .theFlag').removeClass('bg-danger bg-success bg-warning bg-slate-200').addClass('bg-slate-200')
                });
            }
        }).catch(error => {
            editNoteModal.hide();
            notesApp.toast('That note could not be opened for editing.', true);
        });
    });

    $('#editNoteForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editNoteForm');

        document.querySelector('#UpdateNote').setAttribute('disabled', 'disabled');
        document.querySelector("#UpdateNote svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#editNoteForm input[name="document"]')[0].files[0]);
        form_data.append("content", editEditor.getData());
        axios({
            method: "post",
            url: route('student.update.note'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#UpdateNote').removeAttribute('disabled');
            document.querySelector("#UpdateNote svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editNoteModal.hide();
                notesApp.toast('Note updated');
            }
            notesApp.reload();
        }).catch(error => {
            document.querySelector('#UpdateNote').removeAttribute('disabled');
            document.querySelector("#UpdateNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editNoteForm .${key}`).addClass('border-danger');
                        $(`#editNoteForm  .error-${key}`).html(val);
                    }
                } else {
                    notesApp.toast('The note could not be updated. Please try again.', true);
                }
            }
        });
    });

    /* A note's attachment, from the list or from the formatted view. */
    function downloadNoteDocument($theLink){
        var row_id = $theLink.attr('data-id');
        if($theLink.hasClass('is-busy')) return;
        $theLink.addClass('is-busy').css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('student.note.document.download'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            $theLink.removeClass('is-busy').css({'opacity' : '', 'cursor' : ''});
            if (response.status == 200){
                let res = response.data.res;
                if(res != ''){
                    window.open(res, '_blank');
                }
            }
        }).catch(error => {
            $theLink.removeClass('is-busy').css({'opacity' : '', 'cursor' : ''});
            notesApp.toast('The attachment could not be opened.', true);
        });
    }

    $('#studentNotesApp').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        downloadNoteDocument($(this));
    });

    $('#viewNoteModal').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        downloadNoteDocument($(this));
    });

    $('#studentNotesApp').on('click', '.viewCommentBtn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var note_id = $btn.attr('data-id');
        commentsOpened = true;
        axios({
            method: "POST",
            url: route('followups.comment.list'),
            data: {note_id : note_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#followUpCommentWrap').html(response.data.htm);
                $('#followUpCommentModal [name="student_note_id"]').val(note_id);


                setTimeout(() => {
                    $('#followUpCommentModal').find('.tooltip').each(function () {
                        let tippyOption = {
                            dropdownParent: 'body',
                            dropdownClass: 'ts-dropdown lcc-tom-float',

                            content: $(this).attr("alt"),
                        };
                        tippy(this, {
                            arrow: roundArrow,
                            animation: "shift-away",
                            zIndex: '9999999999',
                            ...tippyOption,
                        });
                    });
                }, 10);
            }
        }).catch(error => {
            if (error.response) {
                $('#followUpCommentWrap').html('<div class="text-slate-500">The comments could not be loaded. Please close and try again.</div>');
            }
        });
    });

    $('#followUpCommentForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('followUpCommentForm');

        $('#postCommentBtn').attr('disabled', 'disabled');
        $('#postCommentBtn svg.theIcon').fadeOut();
        $('#postCommentBtn svg.theLoader').fadeIn();

        if($('#the_comment', $form).val() != ''){
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('followups.comment.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#postCommentBtn').removeAttr('disabled');
                $('#postCommentBtn svg.theIcon').fadeIn();
                $('#postCommentBtn svg.theLoader').fadeOut();

                if (response.status == 200) {
                    $('#followUpCommentWrap').html(response.data.htm);
                    $('#the_comment', $form).val('');


                    setTimeout(() => {
                        $('#followUpCommentModal').find('.tooltip').each(function () {
                            let tippyOption = {
                                dropdownParent: 'body',
                                dropdownClass: 'ts-dropdown lcc-tom-float',

                                content: $(this).attr("alt"),
                            };
                            tippy(this, {
                                arrow: roundArrow,
                                animation: "shift-away",
                                zIndex: '9999999999',
                                ...tippyOption,
                            });
                        });
                    }, 10);
                }
            }).catch(error => {
                $('#postCommentBtn').removeAttr('disabled');
                $('#postCommentBtn svg.theIcon').fadeIn();
                $('#postCommentBtn svg.theLoader').fadeOut();
                if (error.response) {
                    notesApp.toast('The comment could not be posted. Please try again.', true);
                }
            });
        }else{
            $('#postCommentBtn').removeAttr('disabled');
            $('#postCommentBtn svg.theIcon').fadeIn();
            $('#postCommentBtn svg.theLoader').fadeOut();
        }
    });
})();
