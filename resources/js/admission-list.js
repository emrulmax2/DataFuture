import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import axios from "axios";
import Litepicker from "litepicker";

/*
 * Admission list (pages/students/admission/index on the admission portal
 * shell). Same endpoints and parameters as before:
 *
 *  - admission.list                 remote-paged, remote-sorted Tabulator
 *  - admission.export               filtered Excel download
 *
 * Both take the search form's fields plus `intake` (the hero's intake tab,
 * one semester id). The hero itself works from the intake × stage × course
 * counts the page embeds (data-adp-hero-cells): switching the intake, the
 * stage tiles and the "by course" breakdown never hit the server.
 *  - course.creation.coursesbysemester
 *  - admission.create.account.from.student / admission.applicant.password.change
 *    (only reachable when a reference matches a converted student)
 */
(function () {
    "use strict";

    const root = document.getElementById("admissionListTable");
    if (!root) return;

    const csrf = { "X-CSRF-TOKEN": (document.querySelector('meta[name="csrf-token"]') || {}).content || "" };
    const esc = (v) => String(v === null || v === undefined ? "" : v)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    const byId = (id) => document.getElementById(id);

    /* status → tone, by id first (applicant statuses), then by name (student rows) */
    const TONE_BY_ID = { 1: "muted", 2: "muted", 3: "amber", 4: "blue", 5: "green", 6: "blue", 7: "green", 8: "red", 9: "red", 46: "red" };
    const TONE_BY_NAME = { "in progress": "amber", "awating decision": "blue", "awaiting decision": "blue", "offer placed": "blue", "accepted": "green", "offer accepted": "green", "rejected": "red", "offer rejected": "red", "canceled": "red", "cancelled": "red" };
    const statusLabel = (name) => (String(name || "") === "Awating Decision" ? "Awaiting Decision" : String(name || ""));
    const tone = (id, name) => TONE_BY_ID[Number(id)] || TONE_BY_NAME[String(name || "").trim().toLowerCase()] || "muted";
    const pill = (id, name) => `<span class="adp-pill adp-pill--${tone(id, name)}"><i></i>${esc(statusLabel(name) || "—")}</span>`;

    /* dates arrive as d-m-Y (model accessors); Y-m-d is accepted too */
    const fmt = (d) => {
        if (!d) return "—";
        const s = String(d);
        const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/) || s.match(/^(\d{2})-(\d{2})-(\d{4})/);
        if (!m) return esc(s);
        const dt = m[1].length === 4 ? new Date(+m[1], +m[2] - 1, +m[3]) : new Date(+m[3], +m[2] - 1, +m[1]);
        return dt.toLocaleDateString("en-GB", { day: "2-digit", month: "short", year: "numeric" });
    };

    const ICON = {
        eye: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
        list: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 6h13M8 12h13M8 18h13"/><path d="M3 6h.01M3 12h.01M3 18h.01"/></svg>',
        login: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="m10 17 5-5-5-5"/><path d="M15 12H3"/></svg>',
        send: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m22 2-7 20-4-9-9-4zM22 2 11 13"/></svg>',
        plus: '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
        chev: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>',
    };

    /* ---------- hero state ------------------------------------------------- */
    const hero = document.querySelector("[data-adp-hero]");
    const heroCells = (() => {
        try { return JSON.parse((document.querySelector("[data-adp-hero-cells]") || {}).textContent || "[]") || []; } catch (err) { return []; }
    })();
    const INTAKE_KEY = "adm.list.intake";
    const intakeTabs = Array.from(document.querySelectorAll("[data-adp-intake]"));
    const tiles = Array.from(document.querySelectorAll("[data-adp-quick]"));
    const intakeTab = (id) => intakeTabs.find((b) => b.getAttribute("data-adp-intake") === String(id)) || null;
    /* "" = all intakes; otherwise a semester id. Remembered across reloads. */
    let intake = hero ? (hero.getAttribute("data-adp-hero-default") || "") : "";
    try { const stored = window.localStorage.getItem(INTAKE_KEY); if (stored !== null && intakeTab(stored)) intake = stored; } catch (err) { /* private mode */ }
    /* the selected stage tile (a status id), or null */
    let brk = null;

    /* ---------- dialogs ---------------------------------------------------- */
    const dialog = (id) => byId(id);
    const openDialog = (id) => { const el = dialog(id); if (el) el.hidden = false; };
    const closeDialog = (id) => { const el = dialog(id); if (el) el.hidden = true; };
    const showSuccess = (title, text, autoHide) => {
        const el = dialog("successModal");
        if (!el) return;
        el.querySelector(".successModalTitle").textContent = title;
        el.querySelector(".successModalDesc").textContent = text || "";
        openDialog("successModal");
        if (autoHide) window.setTimeout(() => closeDialog("successModal"), autoHide);
    };
    const showError = (title, text) => {
        const el = dialog("errorModal");
        if (!el) return;
        el.querySelector(".errorModalTitle").textContent = title;
        el.querySelector(".errorModalDesc").textContent = text || "";
        openDialog("errorModal");
    };
    document.querySelectorAll(".adp-overlay").forEach((overlay) => {
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay || e.target.closest("[data-adp-dialog-close]")) {
                overlay.hidden = true;
                if (overlay.id === "addNewAccountConfirm") { const s = byId("student_id"); if (s) s.value = ""; }
            }
        });
    });
    document.addEventListener("keydown", (e) => {
        if (e.key !== "Escape") return;
        document.querySelectorAll(".adp-overlay:not([hidden])").forEach((o) => { o.hidden = true; });
    });

    /* ---------- filters ---------------------------------------------------- */
    const text = (id) => ((byId(id) || {}).value || "").trim();
    const tsOf = (id) => { const el = byId(id); return el && el.tomselect ? el.tomselect : null; };
    const tsVal = (id) => {
        const ts = tsOf(id);
        const v = ts ? ts.getValue() : Array.from((byId(id) || { selectedOptions: [] }).selectedOptions).map((o) => o.value);
        return (Array.isArray(v) ? v : (v ? [v] : [])).filter(Boolean);
    };
    const params = () => ({
        semesters: tsVal("semesters-ADM"),
        courses: tsVal("courses-ADM"),
        statuses: tsVal("statuses-ADM"),
        agents: tsVal("agents-ADM"),
        intake,
        refno: text("refno-ADM"),
        firstname: text("firstname-ADM"),
        lastname: text("lastname-ADM"),
        dob: text("dob-ADM"),
        email: text("email-ADM"),
        phone: text("phone-ADM"),
    });
    let applied = params();
    /* the badge counts the search form's filters; the intake tab is not one of them */
    const activeCount = (p) => Object.entries(p).filter(([k, v]) => k !== "intake" && (Array.isArray(v) ? v.length : v)).length;
    const paintFilterState = () => {
        const n = activeCount(applied);
        const badge = document.querySelector("[data-adp-filter-count]");
        if (badge) { badge.textContent = String(n); badge.hidden = n === 0; }
    };

    /* ---------- hero: intake tabs, stage tiles, "by course" breakdown ------ */
    const inIntake = (c) => intake === "" || String(c.semester_id) === intake;
    const stageCount = (s) => heroCells.reduce((t, c) => (inIntake(c) && Number(c.status_id) === s ? t + Number(c.n) : t), 0);
    const submittedCount = () => heroCells.reduce((t, c) => (inIntake(c) ? t + Number(c.n) : t), 0);
    const byCourse = (s) => {
        const m = new Map();
        heroCells.forEach((c) => {
            if (!inIntake(c) || Number(c.status_id) !== s) return;
            const key = c.course_id === null || c.course_id === undefined ? "" : String(c.course_id);
            const cur = m.get(key) || { id: key, name: c.course || "Unknown course", n: 0 };
            cur.n += Number(c.n);
            m.set(key, cur);
        });
        return Array.from(m.values()).sort((a, b) => b.n - a.n || a.name.localeCompare(b.name));
    };
    const intakeName = () => { const tab = intakeTab(intake); return tab ? (tab.getAttribute("data-adp-intake-name") || "") : ""; };

    const reduceMotion = !!(window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches);
    /* numbers roll from their previous value (420 ms, ease-out) */
    const countUp = (el, to) => {
        if (!el) return;
        /* start from what is on screen, so an interrupted roll-up carries on from where it was */
        const shown = Number(String(el.textContent).replace(/[^\d.-]/g, ""));
        const from = (Number.isFinite(shown) && el.textContent.trim() !== "" ? shown : Number(el.getAttribute("data-n"))) || 0;
        el.setAttribute("data-n", String(to));
        if (reduceMotion || from === to || !window.requestAnimationFrame) { el.textContent = String(to); return; }
        if (el._adpRaf) window.cancelAnimationFrame(el._adpRaf);
        /* timed from the first frame's own timestamp, so the easing can never run backwards */
        let t0 = null;
        const d = 420;
        const step = (t) => {
            if (t0 === null) t0 = t;
            const k = Math.max(0, Math.min(1, (t - t0) / d)), e = 1 - Math.pow(1 - k, 3);
            el.textContent = String(Math.round(from + (to - from) * e));
            if (k < 1) el._adpRaf = window.requestAnimationFrame(step); else el._adpRaf = null;
        };
        el._adpRaf = window.requestAnimationFrame(step);
    };

    const brkEl = hero ? hero.querySelector("[data-adp-hero-brk]") : null;
    let brkKey = "";
    const paintBreakdown = () => {
        if (!brkEl) return;
        if (brk === null) { brkEl.hidden = true; brkKey = ""; return; }
        const tile = tiles.find((t) => Number(t.getAttribute("data-adp-quick")) === brk);
        const rows = byCourse(brk);
        const total = rows.reduce((t, r) => t + r.n, 0), max = rows.length ? rows[0].n : 0;
        const name = intakeName();
        brkEl.querySelector("[data-adp-brk-stage]").textContent = tile ? (tile.getAttribute("data-adp-stage-name") || "") : "";
        brkEl.querySelector("[data-adp-brk-intake]").textContent = name ? ` · ${name}` : "";
        brkEl.querySelector("[data-adp-brk-n]").textContent = `${total} application${total === 1 ? "" : "s"}`;
        brkEl.querySelector("[data-adp-brk-empty]").hidden = rows.length > 0;
        /* a row is "on" while the list is filtered to its course */
        const picked = applied.courses.length === 1 && applied.statuses.length === 1 && Number(applied.statuses[0]) === brk ? String(applied.courses[0]) : null;
        brkEl.querySelector("[data-adp-brk-rows]").innerHTML = rows.map((r, i) => `<button type="button" class="adp-ah-row${picked !== null && picked === r.id ? " on" : ""}" data-adp-brk-course="${esc(r.id)}" data-adp-brk-name="${esc(r.name)}"${r.id === "" ? ' aria-disabled="true"' : ""} style="animation-delay:${i * 45}ms"><span class="adp-ah-rank">${String(i + 1).padStart(2, "0")}</span><span class="adp-ah-course"><span class="adp-ah-name">${esc(r.name)}</span><span class="adp-ah-bar"><i style="width:${max ? (r.n / max) * 100 : 0}%"></i></span></span><b>${r.n}</b><span class="adp-ah-pct">${total ? Math.round((r.n / total) * 100) : 0}%</span><span class="adp-ah-go">${ICON.chev}</span></button>`).join("");
        /* replay the slide-in when the stage or the intake changes */
        const key = `${brk}|${intake}`;
        if (key !== brkKey && !brkEl.hidden) { brkEl.style.animation = "none"; void brkEl.offsetWidth; brkEl.style.animation = ""; }
        brkKey = key;
        brkEl.hidden = false;
    };

    const paintHero = () => {
        if (!hero) return;
        const submitted = submittedCount();
        intakeTabs.forEach((b) => {
            const on = (b.getAttribute("data-adp-intake") || "") === intake;
            b.classList.toggle("on", on);
            b.setAttribute("aria-pressed", on ? "true" : "false");
        });
        const name = intakeName();
        const scope = hero.querySelector("[data-adp-hero-scope]");
        if (scope) scope.innerHTML = name ? `for <b class="adp-ah-n">${esc(name)}</b>` : "across all intakes";
        countUp(hero.querySelector("[data-adp-hero-submitted]"), submitted);
        hero.querySelectorAll("[data-adp-hero-count]").forEach((el) => countUp(el, stageCount(Number(el.getAttribute("data-adp-hero-count")))));
        tiles.forEach((tile, i) => {
            const s = Number(tile.getAttribute("data-adp-quick"));
            const n = stageCount(s), share = submitted ? Math.round((n / submitted) * 100) : 0, on = brk === s;
            tile.classList.toggle("on", on);
            tile.classList.toggle("zero", n === 0);
            tile.setAttribute("aria-pressed", on ? "true" : "false");
            countUp(tile.querySelector("[data-adp-tile-n]"), n);
            const share_ = tile.querySelector("[data-adp-tile-share]"); if (share_) share_.textContent = `${share}%`;
            const fill = tile.querySelector("[data-adp-tile-fill]"); if (fill) fill.style.width = `${share}%`;
            const seg = hero.querySelector(`[data-adp-pipe="${s}"]`);
            if (seg) {
                seg.style.flex = String(n || 0.001);
                seg.style.opacity = String(brk !== null && brk !== s ? 0.35 : 0.45 + i * 0.11);
                seg.classList.toggle("on", on);
            }
        });
        paintBreakdown();
    };

    /* date of birth: Litepicker, DD-MM-YYYY, as every other date field in the app */
    let dobPicker = null;
    const dob = byId("dob-ADM");
    if (dob && !dob.dataset.litepickerReady) {
        dob.dataset.litepickerReady = "1";
        dobPicker = new Litepicker({
            element: dob,
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            showWeekNumbers: true,
            format: "DD-MM-YYYY",
            maxDate: new Date(),
            dropdowns: { minYear: 1900, maxYear: null, months: true, years: true },
        });
    }

    /* tag selectors */
    const tsOptions = (el) => ({
        plugins: { remove_button: { title: "Remove this item" }, clear_button: { title: "Clear all" } },
        placeholder: el.getAttribute("data-placeholder") || "Select…",
        hidePlaceholder: true,
        dropdownParent: "body",
        dropdownClass: "ts-dropdown adp-dd",
        hideSelected: true,
        closeAfterSelect: false,
        /* persist keeps an option in the list after its tag is removed */
        persist: true,
        create: false,
        allowEmptyOption: false,
    });
    const selects = {};
    ["semesters-ADM", "courses-ADM", "statuses-ADM", "agents-ADM"].forEach((id) => {
        const el = byId(id);
        if (!el) return;
        selects[id] = el.tomselect || new TomSelect(el, tsOptions(el));
    });

    /* the course list follows the chosen semesters (courses already picked stay selected) */
    if (selects["semesters-ADM"] && selects["courses-ADM"]) {
        selects["semesters-ADM"].on("change", (value) => {
            const courses = selects["courses-ADM"];
            courses.clearOptions();
            const chosen = Array.isArray(value) ? value.filter(Boolean) : (value ? [value] : []);
            if (!chosen.length) return;
            axios.get(route("course.creation.coursesbysemester"), { params: { semesters: chosen }, headers: csrf })
                .then((res) => { (res.data || []).forEach((c) => courses.addOption({ value: c.id, text: c.name })); courses.refreshOptions(false); })
                .catch(() => {});
        });
    }

    /* ---------- the table -------------------------------------------------- */
    let table = null, total = 0;
    const counter = () => {
        const foot = table && table.element.querySelector(".tabulator-footer");
        if (!foot) return;
        let c = foot.querySelector(".tabulator-page-counter");
        if (!c) { c = document.createElement("span"); c.className = "tabulator-page-counter"; foot.insertBefore(c, foot.firstChild); }
        const size = table.getPageSize(), page = table.getPage() || 1;
        if (!total) { c.textContent = "0 applications"; return; }
        const from = size === true ? 1 : (page - 1) * size + 1;
        const to = size === true ? total : Math.min(page * size, total);
        c.textContent = `${from}–${to} of ${total}`;
    };

    const avatar = (d) => {
        if (d.has_photo && d.photo_url) return `<span class="adp-avatar"><img src="${esc(d.photo_url)}" alt=""></span>`;
        return `<span class="adp-avatar">${esc(d.initials || "LC")}</span>`;
    };

    const columns = [
        { title: "Ref", field: "application_no", width: 110, cssClass: "adp-cell--ref", formatter: (c) => esc(c.getValue() || "—") },
        { title: "Applicant", field: "first_name", minWidth: 260, widthGrow: 2, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => {
            const d = c.getData();
            const name = [d.title, d.full_name || `${d.first_name || ""} ${d.last_name || ""}`].filter(Boolean).join(" ").trim();
            const sub = [d.date_of_birth ? fmt(d.date_of_birth) : "", d.gender, d.email].filter(Boolean).map(esc).join(" · ");
            return `<div class="adp-namecell">${avatar(d)}<div class="adp-cell"><b>${esc(name || "—")}</b>${sub ? `<span class="adp-sub-line">${sub}</span>` : ""}</div></div>`;
        } },
        { title: "Course", field: "course", minWidth: 220, widthGrow: 2, headerSort: false, variableHeight: true, cssClass: "adp-cell--wrap", formatter: (c) => {
            const d = c.getData();
            if (!d.course) return '<span class="adp-none">—</span>';
            const sub = [d.semester, d.full_time === "Yes" ? "Full time" : "Weekday"].filter(Boolean).map(esc).join(" · ");
            return `<div class="adp-cell"><span>${esc(d.course)}</span><span class="adp-sub-line">${sub}</span></div>`;
        } },
        { title: "Submitted", field: "submission_date", width: 120, formatter: (c) => fmt(c.getValue()) },
        { title: "Status", field: "status_id", width: 170, formatter: (c) => {
            const d = c.getData();
            if (d.create_account === true) {
                /* a converted student found by reference: offer the account actions the list always had */
                if (d.apply_ready !== false) {
                    const url = route("impersonate", { id: d.apply_ready, guardName: "applicant" });
                    return `<span class="adp-stack"><a target="_blank" rel="noopener" href="${esc(url)}" class="adp-iconbtn adp-iconbtn--warn" title="Login as applicant" aria-label="Login as applicant">${ICON.login}</a><button type="button" class="adp-iconbtn sendMail" data-id="${esc(d.apply_ready)}" title="Send password email" aria-label="Send password email">${ICON.send}</button></span>`;
                }
                return `<button type="button" class="adp-btn adp-btn--primary adp-btn--sm create_new_account" data-id="${esc(d.id)}">${ICON.plus}Create account</button>`;
            }
            const reason = Number(d.status) === 8 && d.reason ? `<span class="adp-sub-line">${esc(d.reason)}</span>` : "";
            return `<div class="adp-cell--status">${pill(d.status, d.status_id)}${reason}</div>`;
        } },
        { title: "Tasks", field: "tasks_done", width: 118, headerSort: false, formatter: (c) => {
            const d = c.getData();
            const n = Number(d.tasks_total) || 0, done = Number(d.tasks_done) || 0;
            if (!n) return '<span class="adp-none">none</span>';
            const pct = Math.round((done / n) * 100);
            return `<span class="adp-tprog" title="${done} of ${n} tasks complete"><span class="adp-tprog__bar"><i style="width:${pct}%"></i></span><small>${done}/${n}</small></span>`;
        } },
        { title: "", field: "id", width: 92, headerSort: false, hozAlign: "right", cssClass: "adp-cell--right", formatter: (c) => {
            const d = c.getData();
            if (d.create_account === true) return "";
            let html = `<div class="adp-acts"><a href="${esc(d.url)}" target="_blank" rel="noopener" class="adp-iconbtn" title="View details" aria-label="View ${esc(d.application_no)}">${ICON.eye}</a>`;
            if (d.process_url) html += `<a href="${esc(d.process_url)}" target="_blank" rel="noopener" class="adp-iconbtn" title="Process tasks" aria-label="Process ${esc(d.application_no)}">${ICON.list}</a>`;
            return html + "</div>";
        } },
    ];

    const build = () => {
        table = new Tabulator("#admissionListTable", {
            ajaxURL: route("admission.list"),
            /* a copy: Tabulator writes page/size into the object it is handed */
            ajaxParams: { ...applied },
            ajaxFiltering: true,
            ajaxSorting: true,
            ajaxResponse: (url, p, res) => { total = Number(res && res.total_rows) || 0; return res; },
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 200, 500, true],
            langs: { default: { pagination: { page_size: "Rows", all: "All", prev: "Previous", next: "Next", prev_title: "Previous page", next_title: "Next page" } } },
            layout: "fitColumns",
            placeholder: "<b>No applications match.</b><span>Change the filters or reset the search. A reference that matches a converted student is looked up in the student table too.</span>",
            columns,
            dataLoaded: () => { counter(); },
            renderComplete: () => { counter(); },
            rowClick: (e, row) => {
                const d = row.getData();
                const studentId = byId("student_id");
                if (studentId) studentId.value = d.id;
                if (e.target.closest("a, button")) return;
                if (d.create_account === false) window.open(d.url, "_blank");
            },
        });
    };
    build();

    window.addEventListener("resize", () => { if (table) table.redraw(); });

    const search = () => {
        applied = params();
        /* the breakdown follows its stage filter; any other filter set closes it */
        if (brk !== null && !(applied.statuses.length === 1 && Number(applied.statuses[0]) === brk)) brk = null;
        paintFilterState();
        paintHero();
        table.setData(route("admission.list"), { ...applied });
    };

    byId("tabulator-html-filter-go-ADM").addEventListener("click", (e) => { e.preventDefault(); search(); });
    byId("tabulatorFilterForm-ADM").addEventListener("keydown", (e) => {
        if (e.key === "Enter" && !(e.target && e.target.closest(".ts-wrapper"))) { e.preventDefault(); search(); }
    });
    byId("tabulatorFilterForm-ADM").addEventListener("submit", (e) => { e.preventDefault(); search(); });

    /* keep.statuses: status ids; keep.course: { value, text } (added to the course list if missing) */
    const resetFilters = (keep = {}) => {
        Object.values(selects).forEach((ts) => ts.clear(true));
        ["refno-ADM", "firstname-ADM", "lastname-ADM", "dob-ADM", "email-ADM", "phone-ADM"].forEach((id) => { const el = byId(id); if (el) el.value = ""; });
        if (dobPicker) dobPicker.clearSelection();
        if (keep.statuses && selects["statuses-ADM"]) selects["statuses-ADM"].setValue(keep.statuses, true);
        if (keep.course && selects["courses-ADM"]) {
            const courses = selects["courses-ADM"];
            if (!courses.options[keep.course.value]) courses.addOption({ value: keep.course.value, text: keep.course.text });
            courses.setValue([keep.course.value], true);
        }
    };
    byId("tabulator-html-filter-reset-ADM").addEventListener("click", (e) => { e.preventDefault(); brk = null; resetFilters(); search(); });

    /* stage tiles: one-click status filter + "by course" breakdown; click again to clear */
    const quick = (s) => {
        brk = s;
        resetFilters(s === null ? {} : { statuses: [String(s)] });
        search();
    };
    tiles.forEach((tile) => tile.addEventListener("click", () => {
        const s = Number(tile.getAttribute("data-adp-quick"));
        quick(brk === s ? null : s);
    }));

    /* breakdown rows narrow the list to that course; click again for the whole stage */
    if (brkEl) {
        brkEl.addEventListener("click", (e) => {
            if (e.target.closest("[data-adp-brk-close]")) { quick(null); return; }
            const row = e.target.closest("[data-adp-brk-course]");
            if (!row || brk === null) return;
            const id = row.getAttribute("data-adp-brk-course") || "";
            if (!id) return;
            if (row.classList.contains("on")) { quick(brk); return; }
            resetFilters({ statuses: [String(brk)], course: { value: id, text: row.getAttribute("data-adp-brk-name") || id } });
            search();
        });
    }

    /* intake tabs: the figures and the list follow the chosen intake; the search starts afresh */
    intakeTabs.forEach((tab) => tab.addEventListener("click", () => {
        const id = tab.getAttribute("data-adp-intake") || "";
        if (id === intake) return;
        intake = id;
        try { window.localStorage.setItem(INTAKE_KEY, intake); } catch (err) { /* private mode */ }
        quick(null);
    }));

    /* show / hide the search box, remembered across reloads */
    document.querySelectorAll("[data-adp-filter-toggle]").forEach((button) => {
        const panel = document.querySelector(button.getAttribute("data-adp-filter-toggle"));
        if (!panel) return;
        const label = button.querySelector("[data-adp-filter-label]");
        const key = button.getAttribute("data-adp-filter-key");
        const paint = (open) => {
            panel.hidden = !open;
            if (label) label.textContent = open ? "Hide search" : "Show search";
            button.setAttribute("aria-expanded", open ? "true" : "false");
        };
        let open = true;
        try { const stored = key && window.localStorage.getItem(key); if (stored !== null && stored !== undefined) open = stored === "1"; } catch (err) { /* private mode */ }
        paint(open);
        button.addEventListener("click", (e) => {
            e.preventDefault();
            open = panel.hidden;
            paint(open);
            try { if (key) window.localStorage.setItem(key, open ? "1" : "0"); } catch (err) { /* private mode */ }
        });
    });

    paintFilterState();
    paintHero();

    /* ---------- export + print -------------------------------------------- */
    const exportBtn = byId("tabulator-export-xlsx-ADM");
    const exportSpin = byId("excelExportBtn");
    if (exportBtn) {
        exportBtn.addEventListener("click", (e) => {
            e.preventDefault();
            exportBtn.setAttribute("disabled", "disabled");
            if (exportSpin) exportSpin.hidden = false;
            axios.get(route("admission.export"), { params: params(), headers: csrf, responseType: "blob" })
                .then((res) => {
                    const url = window.URL.createObjectURL(new Blob([res.data]));
                    const link = document.createElement("a");
                    link.href = url;
                    link.setAttribute("download", "admission_download.xlsx");
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    window.URL.revokeObjectURL(url);
                })
                .catch(() => showError("Export failed", "The list could not be exported. Try again, or narrow the filters."))
                .finally(() => { exportBtn.removeAttribute("disabled"); if (exportSpin) exportSpin.hidden = true; });
        });
    }
    const printBtn = byId("tabulator-print-ADM");
    if (printBtn) printBtn.addEventListener("click", (e) => { e.preventDefault(); if (table) table.print(); });

    /* ---------- converted-student rows: create account / resend password --- */
    root.addEventListener("click", (e) => {
        const create = e.target.closest(".create_new_account");
        if (create) {
            e.preventDefault();
            const s = byId("student_id");
            if (s) s.value = create.getAttribute("data-id") || "";
            openDialog("addNewAccountConfirm");
            return;
        }
        const send = e.target.closest(".sendMail");
        if (send) {
            e.preventDefault();
            if (send.hasAttribute("disabled")) return;
            send.setAttribute("disabled", "disabled");
            axios.get(route("admission.applicant.password.change", send.getAttribute("data-id")), { headers: csrf })
                .then(() => showSuccess("Email sent", "A password change email has been sent to the applicant's personal email.", 3000))
                .catch(() => showError("Email not sent", "The password change email could not be sent."))
                .finally(() => send.removeAttribute("disabled"));
        }
    });

    const confirmBtn = document.querySelector("#addNewAccountConfirm .agreeWith");
    if (confirmBtn) {
        confirmBtn.addEventListener("click", (e) => {
            e.preventDefault();
            const studentId = (byId("student_id") || {}).value;
            if (!studentId) return;
            const spin = confirmBtn.querySelector(".adp-spin");
            const cancel = document.querySelector("#addNewAccountConfirm .disAgreeWith");
            confirmBtn.setAttribute("disabled", "disabled");
            if (cancel) cancel.setAttribute("disabled", "disabled");
            if (spin) spin.hidden = false;
            axios.post(route("admission.create.account.from.student"), { student_id: studentId }, { headers: csrf })
                .then(() => {
                    closeDialog("addNewAccountConfirm");
                    showSuccess("Account created", "Please check the applicant's personal email for the password link.");
                    window.setTimeout(() => window.location.reload(), 2000);
                })
                .catch(() => showError("Account not created", "The application account could not be created."))
                .finally(() => {
                    confirmBtn.removeAttribute("disabled");
                    if (cancel) cancel.removeAttribute("disabled");
                    if (spin) spin.hidden = true;
                });
        });
    }
})();
