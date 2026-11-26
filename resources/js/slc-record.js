import xlsx from "xlsx";
import { createIcons, icons } from "lucide";

/*
 * Reports › SLC Reports › SLC Record Report, "New version" (user's slc-record-report web design,
 * 25 Nov 2026). Choose intakes and Generate report: SlcRecordController::data returns both record
 * sets (LCC and franchise courses: the old screen's two tables), so the Record set toggle needs no
 * request. Counts open their students (SlcRecordController::students) in a dialog that searches,
 * pages and exports them in the browser. Generating also fills and runs the Old version, and the
 * other way round.
 */
(function () {
    const root = document.querySelector("[data-srr]");
    if (!root) return;

    const $ = (sel) => root.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const n = (v) => Number(v || 0).toLocaleString("en-GB");
    const pc = (a, b) => (b ? ((100 * a) / b).toFixed(1) + "%" : "–");
    const nice = (s) => {
        const m = String(s).match(/^(\d{4})\s+([A-Za-z]+)(.*)$/);
        return m ? m[2] + " " + m[1] + m[3] : String(s);
    };
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });
    // Figures in the order the server sends them.
    const K = { lcc: 0, awb: 1, slc: 2, att: 3, withdrawn: 4, intermittent: 5, self: 6, gap: 7 };
    const NAMES = ["Registered at LCC", "Registered with awarding body", "SLC registered", "SLC attendance confirmed", "SLC withdrawn", "Withdrawn or intermittent", "Self funded", "Not yet SLC registered"];
    const KEYS = Object.keys(K);
    const SETS = { lcc: "LCC courses", franchise: "Franchise courses" };

    /* ── Version tabs of this report ── */
    [root.closest("[data-version-tabs]")].filter(Boolean).forEach((group) => {
        const buttons = group.querySelectorAll(":scope > .crn-tabs [data-version]");
        const panes = group.querySelectorAll(":scope > [data-version-pane]");
        buttons.forEach((button) =>
            button.addEventListener("click", () => {
                buttons.forEach((b) => {
                    b.classList.toggle("is-active", b === button);
                    b.setAttribute("aria-pressed", b === button ? "true" : "false");
                });
                panes.forEach((pane) => (pane.hidden = pane.dataset.versionPane !== button.dataset.version));
            })
        );
    });

    const SEMESTERS = JSON.parse(root.dataset.semesters || "[]"); // [[id, name], ...] newest first
    const nameOf = {};
    SEMESTERS.forEach(([id, name]) => (nameOf[id] = name));
    let pick = {};
    let applied = null; // the intake ids of the report on screen
    let D = null;
    let set = "lcc";
    let seq = 0;
    let open = {};
    const dd = { open: false, q: "" };

    const picked = () => SEMESTERS.filter(([id]) => pick[id]).map(([id]) => String(id));
    const state = $("[data-srr-state]");
    const report = $("[data-srr-report]");
    const dirty = $("[data-srr-dirty]");

    /* ── Bar: intake picker, Generate, then record set and downloads ── */
    function drawBar() {
        const p = picked();
        const sum = p.length === 0 ? "Select intake" : p.length === SEMESTERS.length ? `All intakes (${p.length})` : p.length <= 2 ? p.map((id) => nameOf[id]).join(", ") : `${p.length} of ${SEMESTERS.length} intakes`;
        let h = '<div class="srr-filters"><div class="sah-f"><span id="srrIl">Intake semester</span><div class="srr-dd" data-srr-dd>';
        h += `<button type="button" class="sah-msbtn" id="srrDdBtn" aria-haspopup="true" aria-expanded="${dd.open}" aria-labelledby="srrIl srrDdSum"><span id="srrDdSum">${esc(sum)}</span><i data-lucide="chevron-down"></i></button>`;
        h += `<div class="sah-mspanel" data-srr-ddpanel${dd.open ? "" : " hidden"}><input type="search" id="srrDdQ" placeholder="Search intake" aria-label="Search intake" autocomplete="off" value="${esc(dd.q)}"><div class="srr-ddacts"><button type="button" data-all="1">Select all</button><button type="button" data-all="0">Clear</button></div><div class="sah-mslist" role="group" aria-labelledby="srrIl">`;
        h += SEMESTERS.map(([id, name]) => `<label class="sah-msopt" data-label="${esc(String(name).toLowerCase())}"${dd.q && !String(name).toLowerCase().includes(dd.q.toLowerCase()) ? " hidden" : ""}><input type="checkbox" data-intake="${id}"${pick[id] ? " checked" : ""}><span>${esc(name)}</span><small></small></label>`).join("");
        h += '<div class="sah-msempty" hidden>No intake matches.</div></div></div></div></div>';
        h += `<button type="button" class="sah-btn sah-btn--primary srr-gen" data-srr-gen${p.length ? "" : " disabled"}><i data-lucide="bar-chart-3"></i> Generate report</button>`;
        if (applied) {
            h += `<div class="sah-f"><span id="srrSl">Record set</span><div class="srr-seg" role="group" aria-labelledby="srrSl">${Object.entries(SETS)
                .map(([key, label]) => `<button type="button" data-set="${key}" aria-pressed="${key === set}">${label}</button>`)
                .join("")}</div></div>`;
        }
        h += "</div>";
        if (applied) {
            h += '<div class="srr-tools"><a href="javascript:void(0);" class="sah-btn sah-btn--excel" data-srr-excel><i data-lucide="file-spreadsheet"></i> Export Excel</a>';
            h += '<a href="javascript:void(0);" class="sah-btn srr-btn--pdf" data-srr-pdf><i data-lucide="file-down"></i> Download PDF</a></div>';
        }
        $("[data-srr-bar]").innerHTML = h;
        const isDirty = !!applied && p.join() !== applied.join();
        dirty.hidden = !isDirty;
        dirty.textContent = p.length ? "Selection changed. Select Generate report to update the figures below." : "No intake selected. The figures below still show the last report.";
        paintIcons();
    }

    function generate(ids) {
        if (!ids.length) return;
        const mine = ++seq;
        state.hidden = false;
        state.classList.remove("is-error");
        state.textContent = "Generating the report…";
        root.classList.add("is-loading");
        const btn = $("[data-srr-gen]");
        if (btn) btn.disabled = true;
        axios
            .get(route(root.dataset.data, { semesters: ids.join("_") }))
            .then(({ data }) => {
                if (mine !== seq) return;
                D = data;
                applied = D.intakes.map((i) => String(i.id));
                open = {};
                applied.forEach((id) => (open[id] = true));
                dd.open = false;
                dd.q = "";
                state.hidden = true;
                drawBar();
                render();
            })
            .catch((err) => {
                if (mine !== seq) return;
                state.hidden = false;
                state.classList.add("is-error");
                state.textContent = (err.response && err.response.data && err.response.data.message) || "The report could not be generated. Please try again.";
                drawBar();
            })
            .finally(() => mine === seq && root.classList.remove("is-loading"));
    }

    /* ── The report ── */
    const zero = '<span class="zero">0</span>';
    // A count that opens its students (intake rows and course rows); zero stays text.
    const link = (v, intake, course, key) => (v ? `<button type="button" class="srr-num" data-i="${intake}" data-c="${course}" data-k="${key}">${n(v)}</button>` : zero);
    function cells(v, intake, course, kind) {
        const plain = kind === "total";
        const L = (key) => (plain ? (v[K[key]] ? n(v[K[key]]) : zero) : link(v[K[key]], intake, course, key));
        const rate = v[0] ? (100 * v[2]) / v[0] : null;
        return (
            `<span class="n">${L("lcc")}</span><span class="n">${L("awb")}</span>` +
            `<span class="sah-track"><i style="width:${rate === null ? 0 : Math.min(100, rate)}%;background:var(--sah-${kind === "course" ? "teal" : "ink"})"></i></span>` +
            `<span class="pct">${v[2] || v[0] ? L("slc") : zero}</span><span class="n">${rate === null ? "" : rate.toFixed(1) + "%"}</span>` +
            `<span class="n${v[7] ? " low b" : ""}">${L("gap")}</span><span class="n">${L("att")}</span><span class="n">${L("withdrawn")}</span><span class="n">${L("intermittent")}</span><span class="n">${L("self")}</span>`
        );
    }

    function render() {
        if (!D || !applied) return;
        const intakes = D.intakes;
        const T = [0, 0, 0, 0, 0, 0, 0, 0];
        intakes.forEach((i) => i.sets[set].v.forEach((x, k) => (T[k] += x)));
        const many = intakes.length > 1;
        let h = '<div><h1>SLC Record</h1><div class="srr-lede">How far each intake has got, from registering at the college to SLC confirming attendance</div></div>';

        const step = (i, k, v, s, cls) => `<div class="srr-step ${cls || ""}"><div class="sn">Step ${i}</div><div class="sk">${k}</div><div class="sv">${n(v)}</div><div class="ss">${s}</div></div>`;
        h += `<section class="sah-sec srr-sec"><div class="sah-head"><h2>The journey${many ? ", all selected intakes" : ""}</h2><span class="sah-hint">Students at each step · ${SETS[set]}</span></div><div class="srr-journey">`;
        h += step(1, NAMES[0], T[0], "First registration on the LCC system, excluding discarded");
        h += step(2, NAMES[1], T[1], T[1] === T[0] ? "Same as step 1" : T[1] > T[0] ? n(T[1] - T[0]) + " more than step 1" : `<b>${n(T[0] - T[1])} fewer</b> than step 1`);
        h += step(3, NAMES[2], T[2], (T[0] ? pc(T[2], T[0]) + " of LCC registrations" : "No LCC registrations in this set") + (T[7] ? ` · <b>${n(T[7])} not yet</b>` : ""));
        h += step(4, NAMES[3], T[3], !T[2] && !T[3] ? "No SLC-registered students" : T[3] === T[2] ? "Every SLC-registered student" : pc(T[3], T[2]) + " of SLC registered" + (T[2] > T[3] ? ` · <b>${n(T[2] - T[3])} not yet</b>` : ""), "end");
        h += "</div>";
        const exit = (k, v, s, alert) => `<div class="srr-exit${alert && v ? " alert" : ""}"><div class="k">${k}</div><div class="v">${n(v)}</div><div class="s">${s}</div></div>`;
        h += '<div class="srr-exits">' + exit(NAMES[7], T[7], "Registered at LCC but not with SLC", true) + exit(NAMES[4], T[4], "Withdrawn on the SLC record") + exit(NAMES[5], T[5], "Student status at the college") + exit(NAMES[6], T[6], "Not funded through SLC") + "</div></section>";

        const chev = '<svg class="chev" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';
        h += '<section class="sah-sec srr-sec"><div class="sah-head"><h2>By intake and course</h2><span class="sah-hint">Select a count to list those students</span></div><div class="sah-scroll"><div class="srr-grid">';
        h += '<div class="srr-cols"><span>Intake and course</span><span>Registered at LCC</span><span>With awarding body</span><span>SLC registered</span><span></span><span>% of LCC</span><span>Not yet SLC registered</span><span>Attendance confirmed</span><span>SLC withdrawn</span><span>Withdrawn or intermittent</span><span>Self funded</span><span></span></div>';
        intakes.forEach((i) => {
            const s = i.sets[set];
            const id = String(i.id);
            h += `<div class="srr-row intake"><button type="button" class="name" data-toggle="${id}" aria-expanded="${!!open[id]}">${chev}${esc(nice(i.name))} intake</button>${cells(s.v, id, "all", "intake")}<span class="n"><a href="javascript:void(0);" class="srr-small" data-srr-details="${id}" title="Download this intake's students under every figure">Export</a></span></div>`;
            if (!s.courses.length) {
                h += `<div class="srr-empty" data-group="${id}"${open[id] ? "" : " hidden"}>No ${set === "franchise" ? "franchise" : "LCC"} courses in this intake.</div>`;
                return;
            }
            s.courses.forEach((c) => {
                const m = String(c.name).match(/^(.*?)\s*\((.*)\)\s*$/);
                h += `<div class="srr-row" data-group="${id}"${open[id] ? "" : " hidden"}><span class="name" title="${esc(c.name)}">${m ? esc(m[1]) + "<small>" + esc(m[2]) + "</small>" : esc(c.name)}</span>${cells(c.v, id, c.id, "course")}<span></span></div>`;
            });
        });
        if (many) h += `<div class="srr-row total"><span class="name">All selected intakes</span>${cells(T, "", "", "total")}<span></span></div>`;
        h += "</div></div></section>";

        const odd = intakes.filter((i) => i.sets[set].v[1] > i.sets[set].v[0]).map((i) => `${nice(i.name)} (${n(i.sets[set].v[1])} against ${n(i.sets[set].v[0])})`);
        h += '<ul class="srr-notes"><li>% of LCC = SLC registered ÷ Registered at LCC. Not yet SLC registered = students registered at LCC who are not SLC registered.</li>';
        if (odd.length) h += `<li><b>Check:</b> more students are registered with the awarding body than at LCC for ${odd.join(" and ")}. Registered at LCC excludes discarded students, which may be why.</li>`;
        h += "<li>LCC courses and Franchise courses are the old report's two tables: courses run by the college, and courses run by a franchise partner.</li></ul>";
        report.innerHTML = h;
        report.hidden = false;
    }

    /* ── The Old version: same intakes, Generate runs both ── */
    const oldSelect = document.getElementById("srr_semester_id");
    const oldForm = document.getElementById("slcRecoredReportForm");
    let mirroring = false;
    function runOld(ids) {
        if (!oldSelect || !oldForm || !oldSelect.tomselect) return;
        oldSelect.tomselect.setValue(ids, true);
        mirroring = true;
        try {
            oldForm.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
        } finally {
            mirroring = false;
        }
    }
    if (oldForm) {
        oldForm.addEventListener("submit", () => {
            if (mirroring || !oldSelect || !oldSelect.tomselect) return;
            const ids = [].concat(oldSelect.tomselect.getValue() || []).map(String).filter(Boolean);
            if (!ids.length) return;
            pick = {};
            ids.forEach((id) => (pick[id] = true));
            drawBar();
            generate(picked());
        });
    }

    // The intake list opens, closes and ticks in place: redrawing the bar under the click would
    // detach its target, and the page-wide "click outside" check would then close the list at once.
    function setDd(isOpen, focus) {
        dd.open = isOpen;
        const panel = root.querySelector("[data-srr-ddpanel]");
        const btn = document.getElementById("srrDdBtn");
        if (!panel || !btn) return;
        panel.hidden = !isOpen;
        btn.setAttribute("aria-expanded", isOpen ? "true" : "false");
        if (!isOpen) {
            dd.q = "";
            const q = document.getElementById("srrDdQ");
            if (q) q.value = "";
            root.querySelectorAll("[data-srr-dd] .sah-msopt").forEach((o) => (o.hidden = false));
            const none = root.querySelector("[data-srr-dd] .sah-msempty");
            if (none) none.hidden = true;
            if (focus) btn.focus();
        } else {
            document.getElementById("srrDdQ").focus();
        }
    }
    // After a tick: the summary, the Generate button and the "selection changed" notice.
    function syncPick() {
        const p = picked();
        document.getElementById("srrDdSum").textContent = p.length === 0 ? "Select intake" : p.length === SEMESTERS.length ? `All intakes (${p.length})` : p.length <= 2 ? p.map((x) => nameOf[x]).join(", ") : `${p.length} of ${SEMESTERS.length} intakes`;
        $("[data-srr-gen]").disabled = !p.length;
        const isDirty = !!applied && p.join() !== applied.join();
        dirty.hidden = !isDirty;
        dirty.textContent = p.length ? "Selection changed. Select Generate report to update the figures below." : "No intake selected. The figures below still show the last report.";
    }

    /* ── Events ── */
    root.addEventListener("click", (e) => {
        const t = e.target;
        let b;
        if (t.closest("#srrDdBtn")) {
            setDd(!dd.open);
            return;
        }
        if ((b = t.closest("[data-all]"))) {
            const on = b.dataset.all === "1";
            // Select all takes the intakes the search shows (all of them when it is empty).
            root.querySelectorAll("[data-srr-dd] [data-intake]").forEach((c) => {
                if (on && c.closest(".sah-msopt").hidden) return;
                c.checked = on;
                pick[c.dataset.intake] = on;
            });
            syncPick();
            return;
        }
        if (dd.open && !t.closest("[data-srr-dd]")) setDd(false);
        if (t.closest("[data-srr-gen]")) {
            const ids = picked();
            generate(ids);
            runOld(ids);
        } else if ((b = t.closest("[data-set]"))) {
            set = b.dataset.set;
            drawBar();
            render();
        } else if ((b = t.closest("[data-toggle]"))) {
            const id = b.dataset.toggle;
            open[id] = !open[id];
            b.setAttribute("aria-expanded", open[id] ? "true" : "false");
            report.querySelectorAll(`[data-group="${id}"]`).forEach((row) => (row.hidden = !open[id]));
        } else if (t.closest("[data-srr-excel]")) {
            e.preventDefault();
            window.location.href = route(root.dataset.excel, { semesters: applied.join("_") }) + "?set=" + set;
        } else if (t.closest("[data-srr-pdf]")) {
            e.preventDefault();
            window.location.href = route(root.dataset.pdf, { semesters: applied.join("_") }) + "?set=" + set;
        } else if ((b = t.closest("[data-srr-details]"))) {
            e.preventDefault();
            window.location.href = route(root.dataset.details, { semester: b.dataset.srrDetails }) + "?set=" + set;
        } else if ((b = t.closest(".srr-num"))) {
            openList(b.dataset.i, b.dataset.c, b.dataset.k, b.textContent.trim());
        }
    });
    document.addEventListener("click", (e) => {
        // A target the page has just replaced is not "outside".
        if (dd.open && e.target.isConnected && !root.contains(e.target)) setDd(false);
    });
    root.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && dd.open) setDd(false, true);
    });
    root.addEventListener("change", (e) => {
        const id = e.target.dataset && e.target.dataset.intake;
        if (id === undefined) return;
        pick[id] = e.target.checked;
        syncPick();
    });
    root.addEventListener("input", (e) => {
        if (e.target.id !== "srrDdQ") return;
        dd.q = e.target.value;
        const q = dd.q.trim().toLowerCase();
        let shown = 0;
        root.querySelectorAll("[data-srr-dd] .sah-msopt").forEach((o) => {
            const hit = o.dataset.label.includes(q);
            o.hidden = !hit;
            if (hit) shown++;
        });
        root.querySelector("[data-srr-dd] .sah-msempty").hidden = shown > 0;
    });

    /* ── Students behind a count: every row at once; search, pages of 15 and Excel in the browser ── */
    const PER = 15;
    const modalEl = document.getElementById("srrStudentsModal");
    const modal = modalEl && window.tailwind ? tailwind.Modal.getOrCreateInstance(modalEl) : null;
    const mq = (sel) => modalEl.querySelector(sel);
    const LIST = { rows: [], shown: [], page: 0, title: "", seq: 0 };

    function drawList() {
        const pages = Math.max(1, Math.ceil(LIST.shown.length / PER));
        LIST.page = Math.max(0, Math.min(pages - 1, LIST.page));
        const part = LIST.shown.slice(LIST.page * PER, (LIST.page + 1) * PER);
        let h = '<table class="srr-mt"><thead><tr><th scope="col">Student ID</th><th scope="col">Name</th><th scope="col">Intake</th><th scope="col">Course</th><th scope="col">Status</th></tr></thead><tbody>';
        part.forEach((r) => {
            h += `<tr><td>${esc(r[1])}</td><td><a href="${esc(route("student.show", r[0]))}" target="_blank" rel="noopener">${esc(r[2])}</a></td><td>${esc(r[3]) || "–"}</td><td class="course">${esc(r[4]) || "–"}</td><td>${esc(r[5]) || "–"}</td></tr>`;
        });
        if (!part.length) h += `<tr><td colspan="5" class="none">${LIST.rows.length ? "No students match that search." : "No students to show."}</td></tr>`;
        mq("[data-srr-m-body]").innerHTML = h + "</tbody></table>";
        mq("[data-srr-m-count]").textContent = LIST.shown.length
            ? `Showing ${n(LIST.page * PER + 1)} to ${n(LIST.page * PER + part.length)} of ${n(LIST.shown.length)}${LIST.shown.length < LIST.rows.length ? ` (filtered from ${n(LIST.rows.length)})` : ""}`
            : `0 of ${n(LIST.rows.length)}`;
        mq("[data-srr-m-page-label]").textContent = `Page ${n(LIST.page + 1)} of ${n(pages)}`;
        mq('[data-srr-m-page="-1"]').disabled = LIST.page === 0;
        mq('[data-srr-m-page="1"]').disabled = LIST.page >= pages - 1;
        mq("[data-srr-m-excel]").disabled = !LIST.shown.length;
    }
    function filterList() {
        const q = mq("[data-srr-m-q]").value.trim().toLowerCase();
        LIST.shown = q ? LIST.rows.filter((r) => `${r[1]} ${r[2]}`.toLowerCase().includes(q)) : LIST.rows;
        LIST.page = 0;
        drawList();
    }
    function openList(intake, course, key, count) {
        if (!modal) return;
        const i = D.intakes.find((x) => String(x.id) === String(intake));
        const c = course === "all" ? null : i && i.sets[set].courses.find((x) => String(x.id) === String(course));
        mq("[data-srr-m-kicker]").textContent = `${i ? nice(i.name) : ""} intake · ${c ? c.name : "All courses"} · ${SETS[set]}`;
        LIST.title = NAMES[K[key]];
        mq("[data-srr-m-title]").textContent = `${LIST.title} (${count})`;
        mq("[data-srr-m-q]").value = "";
        LIST.rows = LIST.shown = [];
        mq("[data-srr-m-body]").innerHTML = '<div class="srr-mnote">Loading students…</div>';
        mq("[data-srr-m-count]").textContent = "";
        mq("[data-srr-m-page-label]").textContent = "";
        mq("[data-srr-m-excel]").disabled = true;
        modal.show();
        const mine = ++LIST.seq;
        axios
            .get(route(root.dataset.students, { semester: intake }), { params: { set, course, key } })
            .then(({ data }) => {
                if (mine !== LIST.seq) return;
                LIST.rows = LIST.shown = data.rows;
                mq("[data-srr-m-title]").textContent = `${LIST.title} (${n(data.rows.length)})`;
                filterList();
            })
            .catch(() => {
                if (mine !== LIST.seq) return;
                mq("[data-srr-m-body]").innerHTML = '<div class="srr-mnote srr-mnote--error">The student list could not be loaded. Please try again.</div>';
            });
    }
    if (modalEl) {
        mq("[data-srr-m-q]").addEventListener("input", filterList);
        modalEl.addEventListener("click", (e) => {
            const page = e.target.closest("[data-srr-m-page]");
            if (page && !page.disabled) {
                LIST.page += Number(page.dataset.srrMPage);
                drawList();
            }
            if (e.target.closest("[data-srr-m-excel]") && LIST.shown.length) {
                const rows = [["Registration No", "Name", "Intake", "Course", "Status"]].concat(LIST.shown.map((r) => [r[1], r[2], r[3], r[4], r[5]]));
                const book = xlsx.utils.book_new();
                xlsx.utils.book_append_sheet(book, xlsx.utils.aoa_to_sheet(rows), "Students");
                const name = `${LIST.title} ${mq("[data-srr-m-kicker]").textContent}`.replace(/[^A-Za-z0-9]+/g, "_").replace(/^_|_$/g, "");
                xlsx.writeFile(book, `${name}.xlsx`);
            }
        });
        modalEl.addEventListener("hidden.tw.modal", () => {
            LIST.seq++;
            LIST.rows = LIST.shown = [];
            mq("[data-srr-m-body]").innerHTML = "";
        });
    }

    // Nothing loads until Generate report.
    drawBar();
})();
