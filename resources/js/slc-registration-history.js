import { createIcons, icons } from "lucide";

/*
 * Reports › SLC Reports › SLC Registration History, "New version" (user's slc-registration-history
 * design, 25 Nov 2026). Works as SLC Attendance History (slc-attendance-history.js): nothing shows
 * until Search; then every registration confirmation of the date range comes at once
 * (SlcRegistrationHistoryController::data) and the status, registration year, academic year, course
 * and day filters work in the browser. Each search is copied into the Old version's form. Export
 * Excel (beside Search, once searched) downloads the selection with the old export's columns.
 */
(function () {
    const root = document.querySelector("[data-srh]");
    if (!root) return;

    const $ = (sel) => root.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const n = (v) => Math.round(v).toLocaleString("en-GB");
    const pc = (a, b) => (b ? ((100 * a) / b).toFixed(1) + "%" : "–");
    const MON = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const DAY = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    const any = (o) => Object.keys(o).some((k) => o[k]);
    const on = (o) => Object.keys(o).filter((k) => o[k]);
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });

    const toDay = (s) => Math.round(Date.parse(s + "T00:00:00Z") / 864e5);
    const fromDay = (d) => new Date(d * 864e5).toISOString().slice(0, 10);
    const nice = (s) => {
        const d = new Date(s + "T00:00:00Z");
        return d.getUTCDate() + " " + MON[d.getUTCMonth()] + " " + d.getUTCFullYear();
    };
    const minus = (s, days) => fromDay(toDay(s) - days);
    const yearBack = (s) => {
        const d = new Date(s + "T00:00:00Z");
        d.setUTCFullYear(d.getUTCFullYear() - 1);
        d.setUTCDate(d.getUTCDate() + 1);
        return d.toISOString().slice(0, 10);
    };

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

    // rows: [id, date, status id, registration year, academic year id, course id, intake id,
    //        student status id, has class dates 1/0, student id]
    let D = null;
    let L = null;
    let F = { from: "", to: "", reg: {}, year: {}, ay: {}, course: "" };
    const T = { page: 0, per: 15, dir: -1, q: "" };
    let seq = 0;
    let searched = false;
    let ayOpen = false;
    let shortCourse = {};
    const OPT = {
        statuses: JSON.parse(root.dataset.statuses || "[]"),
        years: JSON.parse(root.dataset.years || "[]"),
        courses: JSON.parse(root.dataset.courses || "[]"),
    };
    const statusOf = {};
    OPT.statuses.forEach((s) => (statusOf[s.id] = s));
    const yearName = {};
    OPT.years.forEach(([id, name]) => (yearName[id] = name));
    const DEFAULT = { from: root.dataset.from, to: root.dataset.to };
    F.from = DEFAULT.from;
    F.to = DEFAULT.to;
    const YES = (OPT.statuses.find((s) => s.code === "Yes") || {}).id;
    const NO = (OPT.statuses.find((s) => s.code === "No") || {}).id;

    const today = () => (D ? D.today : DEFAULT.to);
    const PRESETS = [
        ["7", "Last 7 days", () => minus(today(), 6)],
        ["30", "Last 30 days", () => minus(today(), 29)],
        ["90", "Last 90 days", () => minus(today(), 89)],
        ["365", "Last 12 months", () => yearBack(today())],
    ];

    const state = $("[data-srh-state]");
    const body = $("[data-srh-body]");
    const tip = $("[data-srh-tip]");
    document.body.appendChild(tip);

    function load(from, to) {
        const mine = ++seq;
        state.hidden = false;
        state.classList.remove("is-error");
        state.textContent = `Loading registrations from ${nice(from)} to ${nice(to)}…`;
        root.classList.add("is-loading");
        axios
            .get(route(root.dataset.data), { params: { from, to } })
            .then(({ data }) => {
                if (mine !== seq) return;
                D = data;
                L = { from: data.from, to: data.to };
                F.from = L.from;
                F.to = L.to;
                Object.entries(D.years).forEach(([id, name]) => (yearName[id] = name));
                shortCourse = {};
                const shorts = {};
                Object.entries(D.courses).forEach(([id, name]) => {
                    const s = String(name).replace(/\s*\(.*\)\s*$/, "");
                    shortCourse[id] = s;
                    shorts[s] = (shorts[s] || 0) + 1;
                });
                Object.keys(shortCourse).forEach((id) => shorts[shortCourse[id]] > 1 && (shortCourse[id] = D.courses[id]));
                searched = true;
                state.hidden = true;
                body.hidden = false;
                refresh();
            })
            .catch((err) => {
                if (mine !== seq) return;
                state.hidden = false;
                state.classList.add("is-error");
                state.textContent = (err.response && err.response.data && err.response.data.message) || "The registrations could not be loaded. Please try again.";
            })
            .finally(() => mine === seq && root.classList.remove("is-loading"));
    }

    // skip: "date" or "matrix" leaves that filter out (as the design)
    const pass = (r, skip) =>
        (skip === "date" || (r[1] >= F.from && r[1] <= F.to)) &&
        (!any(F.reg) || F.reg[r[2]]) &&
        (skip === "matrix" || ((!any(F.year) || F.year[r[3]]) && (!any(F.ay) || F.ay[r[4]]))) &&
        (F.course === "" || String(r[5]) === F.course);

    const present = (i) => [...new Set(D.rows.map((r) => r[i]).filter((v) => v !== null))].sort((a, b) => a - b);
    const union = (a, b) => [...new Set([...a, ...b])].sort((x, y) => x - y);
    const chip = (group, key, label, small, lead) =>
        `<button type="button" class="sah-chip" data-f="${group}" data-k="${key}" aria-pressed="${!!F[group][key]}">${lead || ""}${label}${small != null ? ` <small>${small}</small>` : ""}</button>`;
    const letter = (s) => `<span class="sah-letter${s.code.length > 1 ? " sah-letter--wide" : ""}" style="background:${s.colour}">${esc(s.code)}</span>`;
    const ayCounts = () => {
        const c = {};
        if (D) D.rows.forEach((r) => r[4] !== null && (c[r[4]] = (c[r[4]] || 0) + 1));
        return c;
    };
    const aySummary = () => {
        const picked = OPT.years.filter(([id]) => F.ay[id]).map(([, name]) => name);
        return picked.length === 0 ? "All academic years" : picked.length <= 2 ? picked.join(", ") : picked.length + " years selected";
    };

    function drawFilters() {
        const count = {};
        if (D) D.rows.forEach((r) => (count[r[2]] = (count[r[2]] || 0) + 1));
        let h = `<div class="sah-f sah-f--date"><span id="srhDl">Confirmation date</span><div class="sah-dates" role="group" aria-labelledby="srhDl"><input type="date" id="srhFrom" aria-label="From" value="${F.from}"><span class="sah-hint">to</span><input type="date" id="srhTo" aria-label="To" value="${F.to}"></div><div class="sah-chips">`;
        h += PRESETS.map(([key, label, start]) => `<button type="button" class="sah-chip sah-chip--quick" data-preset="${key}" aria-pressed="${F.to === today() && F.from === start()}">${label}</button>`).join("");
        h += "</div></div>";
        h += `<div class="sah-f sah-f--code"><span>Registration status</span><div class="sah-chips">${OPT.statuses.map((s) => chip("reg", s.id, esc(s.name), searched ? n(count[s.id] || 0) : null, letter(s))).join("")}</div></div>`;
        h += `<div class="sah-f sah-f--year"><span>Registration year</span><div class="sah-chips">${union([1, 2, 3], D ? present(3) : []).map((y) => chip("year", y, "Year " + y)).join("")}</div></div>`;

        // Academic year: a searchable multi-select (the design's), counts once searched
        const ayc = ayCounts();
        h += `<div class="sah-f sah-f--term"><span id="srhAyl">Academic year</span><div class="sah-ms" data-srh-ms><button type="button" class="sah-msbtn" id="srhAyBtn" aria-haspopup="true" aria-expanded="${ayOpen}" aria-labelledby="srhAyl srhAyBtn"><span data-srh-aysum>${esc(aySummary())}</span><i data-lucide="chevron-down"></i></button>`;
        h += `<div class="sah-mspanel" data-srh-aypanel${ayOpen ? "" : " hidden"}><input type="search" id="srhAyQ" placeholder="Find a year, e.g. 2025" aria-label="Find an academic year" autocomplete="off"><div class="sah-mslist" role="group" aria-labelledby="srhAyl">`;
        h += OPT.years.map(([id, name]) => `<label class="sah-msopt${searched && !ayc[id] ? " none" : ""}" data-label="${esc(name)}"><input type="checkbox" data-ay-opt="${id}"${F.ay[id] ? " checked" : ""}><span>${esc(name)}</span><small>${searched ? n(ayc[id] || 0) : ""}</small></label>`).join("");
        h += '<div class="sah-msempty" hidden>No academic year matches.</div></div><div class="sah-msfoot"><button type="button" class="sah-linkbtn" data-ay-clear>Clear</button><button type="button" class="sah-btn" data-ay-done>Done</button></div></div></div></div>';

        const courses = OPT.courses.map(([id, name]) => [String(id), name]);
        if (D) present(5).forEach((id) => courses.some((c) => c[0] === String(id)) || courses.push([String(id), D.courses[id]]));
        h += `<div class="sah-f sah-f--course"><label for="srhCourse">Course</label><select id="srhCourse"><option value="">All courses</option>${courses.map(([id, name]) => `<option value="${id}"${F.course === id ? " selected" : ""}>${esc(name)}</option>`).join("")}</select></div>`;
        const base = searched ? L : DEFAULT;
        const active = (F.from !== base.from || F.to !== base.to) + any(F.reg) + any(F.year) + any(F.ay) + (F.course !== "");
        h += `<div class="sah-fbar"><span>${searched ? '<span class="sah-count" data-srh-fcount></span>' : '<span class="sah-hint">Nothing selected in a group means all.</span>'}</span><span class="sah-fbar__actions">`;
        if (active) h += `<button type="button" class="sah-linkbtn" data-reset>Clear ${active} filter${active > 1 ? "s" : ""}</button>`;
        h += `<button type="button" class="sah-btn sah-btn--primary" data-srh-search><i data-lucide="search"></i> Search</button>`;
        if (searched) h += `<a href="javascript:void(0);" class="sah-btn sah-btn--excel" data-srh-export aria-disabled="false"><i data-lucide="file-spreadsheet"></i> Export Excel</a>`;
        h += "</span></div>";
        $("[data-srh-filters]").innerHTML = h;
        paintIcons();
    }

    // The Old version's form follows the search (it has no course field).
    function syncOld() {
        const dmy = (s) => s.split("-").reverse().join("-");
        const range = document.getElementById("date_range1");
        if (range) {
            range.value = `${dmy(F.from)} - ${dmy(F.to)}`;
            const err = document.querySelector("#registrationSLCForm .error-date_range");
            if (err) err.textContent = "";
        }
        [
            ["slc_registration_status_id", on(F.reg)],
            ["registration_year", on(F.year)],
            ["academic_year_id", on(F.ay)],
        ].forEach(([id, values]) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.tomselect) el.tomselect.setValue(values, true);
            else Array.from(el.options).forEach((o) => (o.selected = values.includes(o.value)));
        });
    }

    const shade = (v, max) => {
        if (!v) return "var(--sah-track)";
        const t = 0.18 + 0.82 * Math.sqrt(v / max);
        return "rgb(" + [14, 90, 84].map((x) => Math.round(255 + (x - 255) * t)).join(",") + ")";
    };

    function draw() {
        const sel = D.rows.filter((r) => pass(r));
        const per = {};
        let yes = 0;
        sel.forEach((r) => {
            per[r[9]] = (per[r[9]] || 0) + 1;
            if (r[2] === YES) yes++;
        });
        const ns = Object.keys(per).length;
        const k = (cls, a, b, c) => `<div class="sah-kpi ${cls}"><div class="k">${a}</div><div class="v">${b}</div><div class="s">${c}</div></div>`;
        $("[data-srh-kpis]").innerHTML =
            k("lead", "Registrations confirmed", n(sel.length), F.from === F.to ? "On " + nice(F.from) : nice(F.from) + " to " + nice(F.to)) +
            k("", "Students", n(ns), sel.length > ns ? n(sel.length - ns) + " confirmed more than once" : ns ? "One confirmation each" : "No students") +
            k("", "Registered", pc(yes, sel.length), n(yes) + " confirmed as registered") +
            k("", "Not registered", n(sel.length - yes), sel.length ? pc(sel.length - yes, sel.length) + " marked No, N/R or other" : "None");
        root.querySelector("[data-srh-fcount]").textContent = `Showing ${n(sel.length)} of ${n(D.rows.length)} registrations`;
        const exportBtn = root.querySelector("[data-srh-export]");
        if (exportBtn) exportBtn.setAttribute("aria-disabled", sel.length ? "false" : "true");

        // Calendar
        const perDay = {};
        let max = 0;
        D.rows.forEach((r) => pass(r, "date") && (perDay[r[1]] = (perDay[r[1]] || 0) + 1));
        Object.values(perDay).forEach((v) => (max = Math.max(max, v)));
        const first = toDay(L.from);
        const last = toDay(L.to);
        const start = first - ((new Date(first * 864e5).getUTCDay() + 6) % 7);
        const weeks = Math.ceil((last - start + 1) / 7);
        const marks = [];
        for (let w = 0, lastM = -1; w < weeks; w++) {
            const monday = new Date((start + w * 7) * 864e5);
            const m = monday.getUTCMonth();
            if (m !== lastM && (monday.getUTCDate() <= 7 || w === 0)) marks.push({ w, m, y: monday.getUTCFullYear() });
            lastM = m;
        }
        if (marks.length > 1 && marks[1].w - marks[0].w < 4) marks.shift();
        const labels = {};
        marks.forEach((x, i) => (labels[x.w] = MON[x.m] + (i === 0 || x.m === 0 ? " " + x.y : "")));
        let c = '<span class="m"></span>' + DAY.map((d, i) => `<span class="wd">${i % 2 === 0 ? d : ""}</span>`).join("");
        let best = null;
        for (let w = 0; w < weeks; w++) {
            c += `<span class="m">${labels[w] || ""}</span>`;
            for (let d = 0; d < 7; d++) {
                const day = start + w * 7 + d;
                if (day < first || day > last) {
                    c += '<span class="blank"></span>';
                    continue;
                }
                const s = fromDay(day);
                const v = perDay[s] || 0;
                if (v && (best === null || v > perDay[best])) best = s;
                c += `<button type="button" data-day="${s}" data-v="${v}" class="${s >= F.from && s <= F.to ? "" : "out"}${F.from === s && F.to === s ? " sel" : ""}" style="background:${shade(v, max)}" aria-label="${nice(s)}: ${v} registrations"></button>`;
            }
        }
        $("[data-srh-cal]").innerHTML = c;
        $("[data-srh-calfoot]").innerHTML =
            "<span>" +
            (best ? `Busiest day: <b>${nice(best)}</b> with ${n(perDay[best])} registrations · confirmed on ${n(Object.keys(perDay).length)} days in total` : "No registrations match these filters") +
            '</span><span class="sah-scale">Fewer ' +
            [0, 0.05, 0.25, 0.6, 1].map((t) => `<i style="background:${shade(t * 100, 100)}"></i>`).join("") +
            " More</span>";

        // By status (share) and by course (share confirmed as registered)
        const row = (label, v, mx, right) => `<div class="sah-brow"><div class="nm">${label}</div><div class="sah-track"><i style="width:${mx ? (100 * v) / mx : 0}%"></i></div><div class="n">${n(v)}</div><div class="a">${right}</div></div>`;
        const shownStatuses = OPT.statuses.filter((s) => D.rows.some((r) => r[2] === s.id));
        const bs = shownStatuses.map((s) => sel.filter((r) => r[2] === s.id).length);
        const bmx = Math.max(0, ...bs);
        $("[data-srh-bystatus]").innerHTML = shownStatuses.map((s, i) => row(`${letter(s)}<span>${esc(s.name)}</span>`, bs[i], bmx, pc(bs[i], sel.length))).join("") || '<p class="sah-hint">No registrations in this range.</p>';
        const courseIds = present(5).sort((a, b) => String(D.courses[a]).localeCompare(String(D.courses[b])));
        if (D.rows.some((r) => r[5] === null)) courseIds.push(null);
        const bc = courseIds.map((id) => {
            const a = sel.filter((r) => r[5] === id);
            return [a.length, a.filter((r) => r[2] === YES).length];
        });
        const cmx = Math.max(0, ...bc.map((x) => x[0]));
        $("[data-srh-bycourse]").innerHTML = courseIds
            .map((id, i) => row(id === null ? "<span>No course recorded</span>" : `<span title="${esc(D.courses[id])}">${esc(shortCourse[id] || D.courses[id])}</span>`, bc[i][0], cmx, bc[i][0] ? pc(bc[i][1], bc[i][0]) : "–"))
            .join("");

        // Academic year × registration year: its own two filters left out (as the design)
        const ays = present(4).sort((a, b) => String(yearName[b] || "").localeCompare(String(yearName[a] || "")));
        const regYears = union([1, 2, 3], present(3));
        const mx = {};
        let mmax = 0;
        let mtot = 0;
        D.rows.forEach((r) => {
            if (r[4] !== null && r[3] !== null && pass(r, "matrix")) {
                const key = r[4] + "-" + r[3];
                mx[key] = (mx[key] || 0) + 1;
                mmax = Math.max(mmax, mx[key]);
                mtot++;
            }
        });
        const matrix = $("[data-srh-matrix]");
        matrix.style.gridTemplateColumns = `86px repeat(${regYears.length}, minmax(0, 1fr))`;
        let mh = '<span class="h"></span>' + regYears.map((y) => `<span class="h">Year ${y}</span>`).join("");
        ays.forEach((a) => {
            mh += `<span class="h r">${esc(yearName[a] || "Year #" + a)}</span>`;
            regYears.forEach((y) => {
                const v = mx[a + "-" + y] || 0;
                const isOn = F.ay[a] && F.year[y];
                const p = mtot ? (100 * v) / mtot : 0;
                mh += `<button type="button" data-ay="${a}" data-y="${y}" class="${isOn ? "on" : ""}" style="background:${shade(v, mmax || 1)};color:${v / (mmax || 1) > 0.35 ? "#fff" : "var(--sah-ink)"}" aria-pressed="${!!isOn}" aria-label="${esc(yearName[a] || "")} Year ${y}: ${v} registrations">${n(v)}<small>${v ? (p < 1 ? "<1%" : p.toFixed(0) + "%") : ""}</small></button>`;
            });
        });
        matrix.innerHTML = ays.length ? mh : '<p class="sah-hint">No academic year recorded in this range.</p>';

        // Worth checking
        const count = (fn) => sel.filter(fn).length;
        const items = [
            [count((r) => r[2] === YES && r[7] === D.checks.left), "confirmed as <b>registered</b> for students whose status is SLC Left"],
            [count((r) => r[2] === NO && (r[7] === D.checks.enrolled || r[7] === D.checks.registered)), "marked <b>not registered</b> for students whose status is SLC Enrolled or SLC Registered"],
            [count((r) => r[2] === YES && !r[8]), "confirmed as <b>registered</b> with no class start or end date"],
            [Object.values(per).filter((x) => x > 1).length, "students confirmed <b>more than once</b> in this selection"],
        ].filter((x) => x[0]);
        const checks = $("[data-srh-checks]");
        checks.className = "sah-checks" + (items.length ? "" : " ok");
        checks.innerHTML = items.length ? items.map((x) => `<li><b class="num">${n(x[0])}</b><span>${x[1]}</span></li>`).join("") : "<li><span>Nothing unusual in this selection.</span></li>";

        drawTable(sel);
    }

    function drawTable(sel) {
        sel = sel || D.rows.filter((r) => pass(r));
        const q = T.q.trim().toLowerCase();
        if (q) sel = sel.filter((r) => (D.students[r[9]] || ["", ""]).join(" ").toLowerCase().includes(q));
        sel = sel.slice().sort((a, b) => (a[1] < b[1] ? -1 : a[1] > b[1] ? 1 : a[0] - b[0]) * T.dir);
        const pages = Math.max(1, Math.ceil(sel.length / T.per));
        T.page = Math.max(0, Math.min(pages - 1, T.page));
        let h = `<thead><tr><th scope="col">Student</th><th scope="col">Course</th><th scope="col">Intake</th><th scope="col">Academic year</th><th scope="col">Registration year</th><th scope="col">Registration</th><th scope="col" aria-sort="${T.dir < 0 ? "descending" : "ascending"}"><button type="button" data-sort>Confirmed on${T.dir < 0 ? " ↓" : " ↑"}</button></th><th scope="col">Status</th></tr></thead><tbody>`;
        sel.slice(T.page * T.per, (T.page + 1) * T.per).forEach((r) => {
            const st = D.students[r[9]] || ["", "Student #" + r[9]];
            const s = statusOf[r[2]] || { code: "?", name: "Unknown status", colour: "#4a5b6b" };
            h += `<tr><td><a href="${esc(route("student.show", r[9]))}" target="_blank" rel="noopener">${esc(st[1])}</a><small>${esc(st[0])}</small></td>`;
            h += `<td class="course"${r[5] !== null ? ` title="${esc(D.courses[r[5]])}"` : ""}>${r[5] !== null ? esc(shortCourse[r[5]] || D.courses[r[5]]) : "–"}</td><td>${r[6] !== null ? esc(D.intakes[r[6]]) : "–"}</td>`;
            h += `<td>${r[4] !== null ? esc(yearName[r[4]] || "") : "–"}</td><td>${r[3] !== null ? "Year " + r[3] : "–"}</td>`;
            h += `<td><span class="sah-pill">${letter(s)}${s.name.startsWith("Status ") ? "" : esc(s.name)}</span></td><td>${nice(r[1])}</td><td>${r[7] !== null ? esc(D.studentStatuses[r[7]]) : "–"}</td></tr>`;
        });
        if (!sel.length) h += `<tr><td colspan="8" class="none">${q ? "No student in this selection matches that search." : "No registrations match these filters."}</td></tr>`;
        $("[data-srh-table]").innerHTML = h + "</tbody>";
        $("[data-srh-tcount]").textContent = sel.length ? `Showing ${n(T.page * T.per + 1)} to ${n(Math.min(sel.length, (T.page + 1) * T.per))} of ${n(sel.length)}` : "0 registrations";
        $("[data-srh-tpage]").textContent = `Page ${n(T.page + 1)} of ${n(pages)}`;
        $('[data-srh-page="-1"]').disabled = T.page === 0;
        $('[data-srh-page="1"]').disabled = T.page >= pages - 1;
    }

    function refresh(keepFocus) {
        const id = keepFocus && document.activeElement && root.contains(document.activeElement) && document.activeElement.id;
        T.page = 0;
        drawFilters();
        if (searched) {
            draw();
            syncOld();
        }
        if (id && document.getElementById(id)) document.getElementById(id).focus();
    }

    function setRange(from, to) {
        if (!from || !to) return;
        if (from > to) [from, to] = [to, from];
        if (!searched || (L && from >= L.from && to <= L.to)) {
            F.from = from;
            F.to = to;
            refresh(true);
        } else {
            load(from, to);
        }
    }

    // The academic-year list stays open while ticking: only the results and its summary change.
    function ayChanged() {
        root.querySelector("[data-srh-aysum]").textContent = aySummary();
        if (searched) {
            T.page = 0;
            draw();
            syncOld();
        }
    }
    function ayClose(focus) {
        if (!ayOpen) return;
        ayOpen = false;
        refresh();
        if (focus) document.getElementById("srhAyBtn").focus();
    }

    /* ── Events ── */
    root.addEventListener("click", (e) => {
        const t = e.target;
        let b;
        if (t.closest("#srhAyBtn")) {
            if (ayOpen) return ayClose(true);
            ayOpen = true;
            root.querySelector("[data-srh-aypanel]").hidden = false;
            t.closest("#srhAyBtn").setAttribute("aria-expanded", "true");
            document.getElementById("srhAyQ").focus();
            return;
        }
        if (t.closest("[data-ay-done]")) return ayClose(true);
        if (t.closest("[data-ay-clear]")) {
            F.ay = {};
            root.querySelectorAll("[data-ay-opt]").forEach((x) => (x.checked = false));
            return ayChanged();
        }
        if (ayOpen && !t.closest("[data-srh-ms]")) ayClose(false);
        if ((b = t.closest("[data-srh-search]"))) {
            if (!F.from || !F.to) {
                state.hidden = false;
                state.classList.add("is-error");
                state.textContent = "Please choose both dates.";
                return;
            }
            if (D && F.from >= L.from && F.to <= L.to) {
                searched = true;
                state.hidden = true;
                body.hidden = false;
                refresh();
            } else {
                load(F.from, F.to);
            }
            return;
        }
        if ((b = t.closest("[data-f]"))) {
            F[b.dataset.f][b.dataset.k] = !F[b.dataset.f][b.dataset.k];
            refresh();
        } else if ((b = t.closest("[data-preset]"))) {
            const p = PRESETS.find((x) => x[0] === b.dataset.preset);
            setRange(p[2](), today());
        } else if (t.closest("[data-reset]")) {
            const base = searched ? L : DEFAULT;
            F = { from: base.from, to: base.to, reg: {}, year: {}, ay: {}, course: "" };
            refresh();
        } else if (!searched) {
            return;
        } else if ((b = t.closest("[data-day]"))) {
            const s = b.dataset.day;
            if (F.from === s && F.to === s) {
                F.from = L.from;
                F.to = L.to;
            } else if (Number(b.dataset.v)) {
                F.from = s;
                F.to = s;
            } else return;
            refresh();
        } else if ((b = t.closest("[data-ay]"))) {
            const a = b.dataset.ay;
            const y = b.dataset.y;
            const isOn = F.ay[a] && F.year[y] && on(F.ay).length === 1 && on(F.year).length === 1;
            F.ay = {};
            F.year = {};
            if (!isOn) {
                F.ay[a] = true;
                F.year[y] = true;
            }
            refresh();
        } else if (t.closest("[data-sort]")) {
            T.dir = -T.dir;
            T.page = 0;
            drawTable();
        } else if ((b = t.closest("[data-srh-page]"))) {
            T.page += Number(b.dataset.srhPage);
            drawTable();
        } else if ((b = t.closest("[data-srh-export]"))) {
            e.preventDefault();
            if (b.getAttribute("aria-disabled") === "true") return;
            const p = new URLSearchParams({ from: F.from, to: F.to });
            on(F.reg).forEach((x) => p.append("statuses[]", x));
            on(F.year).forEach((x) => p.append("years[]", x));
            on(F.ay).forEach((x) => p.append("academic[]", x));
            if (F.course !== "") p.append("course", F.course);
            window.location.href = route(root.dataset.excel) + "?" + p.toString();
        }
    });
    document.addEventListener("click", (e) => ayOpen && !root.contains(e.target) && ayClose(false));
    root.addEventListener("keydown", (e) => e.key === "Escape" && ayOpen && ayClose(true));
    root.addEventListener("input", (e) => {
        if (e.target.id !== "srhAyQ") return;
        const q = e.target.value.trim().toLowerCase();
        let shown = 0;
        root.querySelectorAll(".sah-msopt").forEach((o) => {
            const hit = o.dataset.label.toLowerCase().includes(q);
            o.hidden = !hit;
            if (hit) shown++;
        });
        root.querySelector(".sah-msempty").hidden = shown > 0;
    });
    root.addEventListener("change", (e) => {
        if (e.target.hasAttribute("data-ay-opt")) {
            F.ay[e.target.dataset.ayOpt] = e.target.checked;
            return ayChanged();
        }
        if (e.target.id === "srhFrom" || e.target.id === "srhTo") {
            setRange(e.target.id === "srhFrom" ? e.target.value : F.from, e.target.id === "srhTo" ? e.target.value : F.to);
        } else if (e.target.id === "srhCourse") {
            F.course = e.target.value;
            refresh(true);
        }
    });
    $("[data-srh-q]").addEventListener("input", (e) => {
        T.q = e.target.value;
        T.page = 0;
        if (D) drawTable();
    });
    const cal = $("[data-srh-cal]");
    cal.addEventListener("mousemove", (e) => {
        const b = e.target.closest("[data-day]");
        if (!b) {
            tip.hidden = true;
            return;
        }
        tip.textContent = `${nice(b.dataset.day)}: ${n(Number(b.dataset.v))} registrations`;
        tip.hidden = false;
        tip.style.left = Math.min(window.innerWidth - 240, e.clientX + 14) + "px";
        tip.style.top = e.clientY - 38 + "px";
    });
    cal.addEventListener("mouseleave", () => (tip.hidden = true));

    // Nothing loads until Search (user): the form only.
    drawFilters();
    paintIcons();
})();
