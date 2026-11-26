import { createIcons, icons } from "lucide";

/*
 * Reports › SLC Reports › SLC Attendance History, "New version" (user's slc-attendance-history
 * design, 25 Nov 2026). Nothing shows until Search (user): then every confirmation of the date
 * range comes at once (SlcAttendanceHistoryController::data) and the code, year, term, course and
 * day filters work in the browser; a date outside the loaded range loads that range. Each search
 * is copied into the Old version's form (dates, codes, years, terms; it has no course field).
 * Export Excel downloads the current selection (SlcAttendanceHistoryController::excel).
 */
(function () {
    const root = document.querySelector("[data-sah]");
    if (!root) return;

    const $ = (sel) => root.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const n = (v) => Math.round(v).toLocaleString("en-GB");
    const gbp = (v) => "£" + n(v);
    const money = (v) => "£" + Number(v).toLocaleString("en-GB", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const MON = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const DAY = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    const any = (o) => Object.keys(o).some((k) => o[k]);
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });

    // Dates as "Y-m-d" strings (compare as text); day numbers for the calendar.
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

    /* ── Version tabs of this report (toggle buttons, not role=tab) ── */
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

    // rows: [id, date, code id, year, term, course id, intake id, status id, amount, student id]
    let D = null;
    let L = null; // the loaded range {from, to}
    let F = { from: "", to: "", code: {}, year: {}, term: {}, course: "" };
    const T = { page: 0, per: 15, sort: "date", dir: -1, q: "" };
    let seq = 0;
    let codeOf = {};
    let shortCourse = {};
    let searched = false;
    // The search form's lists, before any data (SlcDataReportController::index).
    const OPT = { codes: JSON.parse(root.dataset.codes || "[]"), courses: JSON.parse(root.dataset.courses || "[]") };
    const DEFAULT = { from: root.dataset.from, to: root.dataset.to };
    F.from = DEFAULT.from;
    F.to = DEFAULT.to;
    OPT.codes.forEach((c) => (codeOf[c.id] = c));

    const today = () => (D ? D.today : DEFAULT.to);
    const PRESETS = [
        ["7", "Last 7 days", () => minus(today(), 6)],
        ["30", "Last 30 days", () => minus(today(), 29)],
        ["90", "Last 90 days", () => minus(today(), 89)],
        ["365", "Last 12 months", () => yearBack(today())],
    ];

    const state = $("[data-sah-state]");
    const body = $("[data-sah-body]");
    const tip = $("[data-sah-tip]");
    document.body.appendChild(tip); // position: fixed; an animated ancestor would move it

    function load(from, to) {
        const mine = ++seq;
        state.hidden = false;
        state.classList.remove("is-error");
        state.textContent = `Loading confirmations from ${nice(from)} to ${nice(to)}…`;
        root.classList.add("is-loading");
        axios
            .get(route(root.dataset.data), { params: { from, to } })
            .then(({ data }) => {
                if (mine !== seq) return;
                D = data;
                L = { from: data.from, to: data.to };
                F.from = L.from;
                F.to = L.to;
                codeOf = {};
                D.codes.forEach((c) => (codeOf[c.id] = c));
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
                state.textContent = (err.response && err.response.data && err.response.data.message) || "The confirmations could not be loaded. Please try again.";
            })
            .finally(() => mine === seq && root.classList.remove("is-loading"));
    }

    const pass = (r, skipDate) =>
        (skipDate || (r[1] >= F.from && r[1] <= F.to)) &&
        (!any(F.code) || F.code[r[2]]) &&
        (!any(F.year) || F.year[r[3]]) &&
        (!any(F.term) || F.term[r[4]]) &&
        (F.course === "" || String(r[5]) === F.course);

    const present = (i) => [...new Set(D.rows.map((r) => r[i]).filter((v) => v !== null))].sort((a, b) => a - b);
    const chip = (group, key, label, small, lead) =>
        `<button type="button" class="sah-chip" data-f="${group}" data-k="${key}" aria-pressed="${!!F[group][key]}">${lead || ""}${label}${small != null ? ` <small>${small}</small>` : ""}</button>`;
    const letter = (c) => `<span class="sah-letter" style="background:${c.colour}">${esc(c.code)}</span>`;

    const union = (a, b) => [...new Set([...a, ...b])].sort((x, y) => x - y);

    // Before the first search the lists are the page's (codes, years and terms 1–3, courses with
    // confirmations); after it, counts and any other year, term or course in the data join them.
    function drawFilters() {
        const count = {};
        if (D) D.rows.forEach((r) => (count[r[2]] = (count[r[2]] || 0) + 1));
        let h = `<div class="sah-f sah-f--date"><span id="sahDl">Confirmation date</span><div class="sah-dates" role="group" aria-labelledby="sahDl"><input type="date" id="sahFrom" aria-label="From" value="${F.from}"><span class="sah-hint">to</span><input type="date" id="sahTo" aria-label="To" value="${F.to}"></div><div class="sah-chips">`;
        h += PRESETS.map(([key, label, start]) => `<button type="button" class="sah-chip sah-chip--quick" data-preset="${key}" aria-pressed="${F.to === today() && F.from === start()}">${label}</button>`).join("");
        h += "</div></div>";
        h += `<div class="sah-f sah-f--code"><span>Attendance code</span><div class="sah-chips">${OPT.codes.map((c) => chip("code", c.id, esc(c.name), searched ? n(count[c.id] || 0) : null, letter(c))).join("")}</div></div>`;
        h += `<div class="sah-f sah-f--year"><span>Attendance year</span><div class="sah-chips">${union([1, 2, 3], D ? present(3) : []).map((y) => chip("year", y, "Year " + y)).join("")}</div></div>`;
        h += `<div class="sah-f sah-f--term"><span>Attendance term</span><div class="sah-chips">${union([1, 2, 3], D ? present(4) : []).map((t) => chip("term", t, "Term " + t)).join("")}</div></div>`;
        const courses = OPT.courses.map(([id, name]) => [String(id), name]);
        if (D) present(5).forEach((id) => courses.some((c) => c[0] === String(id)) || courses.push([String(id), D.courses[id]]));
        h += `<div class="sah-f sah-f--course"><label for="sahCourse">Course</label><select id="sahCourse"><option value="">All courses</option>${courses.map(([id, name]) => `<option value="${id}"${F.course === id ? " selected" : ""}>${esc(name)}</option>`).join("")}</select></div>`;
        const base = searched ? L : DEFAULT;
        const active = (F.from !== base.from || F.to !== base.to) + any(F.code) + any(F.year) + any(F.term) + (F.course !== "");
        h += `<div class="sah-fbar"><span>${searched ? '<span class="sah-count" data-sah-fcount></span>' : '<span class="sah-hint">Nothing selected in a group means all.</span>'}</span><span class="sah-fbar__actions">`;
        if (active) h += `<button type="button" class="sah-linkbtn" data-reset>Clear ${active} filter${active > 1 ? "s" : ""}</button>`;
        h += `<button type="button" class="sah-btn sah-btn--primary" data-sah-search><i data-lucide="search"></i> Search</button>`;
        // Export Excel to the right of Search, once there are results (user).
        if (searched) h += `<a href="javascript:void(0);" class="sah-btn sah-btn--excel" data-sah-export aria-disabled="false"><i data-lucide="file-spreadsheet"></i> Export Excel</a>`;
        h += "</span></div>";
        $("[data-sah-filters]").innerHTML = h;
        paintIcons();
    }

    // The Old version's form follows the search (its own fields; it has no course filter).
    function syncOld() {
        const on = (o) => Object.keys(o).filter((k) => o[k]);
        const dmy = (s) => s.split("-").reverse().join("-");
        const range = document.getElementById("date_range");
        if (range) {
            range.value = `${dmy(F.from)} - ${dmy(F.to)}`;
            const err = document.querySelector("#attendanceSLCReportForm .error-date_range");
            if (err) err.textContent = "";
        }
        [
            ["attendance_code_id", on(F.code)],
            ["attendance_year", on(F.year)],
            ["term_declaration_id", on(F.term)],
        ].forEach(([id, values]) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.tomselect) el.tomselect.setValue(values, true);
            else Array.from(el.options).forEach((o) => (o.selected = values.includes(o.value)));
        });
    }

    // Teal by how many (square root, as the design), grey for none.
    const shade = (v, max) => {
        if (!v) return "var(--sah-track)";
        const t = 0.18 + 0.82 * Math.sqrt(v / max);
        return "rgb(" + [14, 90, 84].map((x) => Math.round(255 + (x - 255) * t)).join(",") + ")";
    };

    function draw() {
        const sel = D.rows.filter((r) => pass(r));

        // Figures
        const studs = new Set();
        let amt = 0;
        let notA = 0;
        sel.forEach((r) => {
            studs.add(r[9]);
            if (r[8] > 0) amt += r[8];
            if (!codeOf[r[2]] || codeOf[r[2]].code !== "A") notA++;
        });
        const k = (cls, a, b, c) => `<div class="sah-kpi ${cls}"><div class="k">${a}</div><div class="v">${b}</div><div class="s">${c}</div></div>`;
        $("[data-sah-kpis]").innerHTML =
            k("lead", "Confirmations sent", n(sel.length), F.from === F.to ? "On " + nice(F.from) : nice(F.from) + " to " + nice(F.to)) +
            k("", "Students", n(studs.size), studs.size ? (sel.length / studs.size).toFixed(1) + " confirmations each on average" : "No students") +
            k("", "Claim amount", gbp(amt), sel.length ? gbp(amt / sel.length) + " per confirmation" : "Nothing claimed") +
            k("", "Not coded A", n(notA), sel.length ? ((100 * notA) / sel.length).toFixed(1) + "% suspended, not attending or other" : "None");
        root.querySelector("[data-sah-fcount]").textContent = `Showing ${n(sel.length)} of ${n(D.rows.length)} confirmations`;
        const exportBtn = root.querySelector("[data-sah-export]");
        if (exportBtn) exportBtn.setAttribute("aria-disabled", sel.length ? "false" : "true");

        // Calendar: the whole loaded range, other filters applied (as the design)
        const perDay = {};
        let max = 0;
        D.rows.forEach((r) => pass(r, true) && (perDay[r[1]] = (perDay[r[1]] || 0) + 1));
        Object.values(perDay).forEach((v) => (max = Math.max(max, v)));
        const first = toDay(L.from);
        const last = toDay(L.to);
        const start = first - ((new Date(first * 864e5).getUTCDay() + 6) % 7); // the Monday on or before
        const weeks = Math.ceil((last - start + 1) / 7);
        // Month labels where a month starts; the range's first label only with room before the next
        // (a range starting late in a month would print "Nov 2025" over "Dec"). Year on the first and on January.
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
                const inRange = s >= F.from && s <= F.to;
                c += `<button type="button" data-day="${s}" data-v="${v}" class="${inRange ? "" : "out"}${F.from === s && F.to === s ? " sel" : ""}" style="background:${shade(v, max)}" aria-label="${nice(s)}: ${v} confirmations"></button>`;
            }
        }
        $("[data-sah-cal]").innerHTML = c;
        $("[data-sah-calfoot]").innerHTML =
            "<span>" +
            (best ? `Busiest day: <b>${nice(best)}</b> with ${n(perDay[best])} confirmations · sent on ${n(Object.keys(perDay).length)} days in total` : "No confirmations match these filters") +
            '</span><span class="sah-scale">Fewer ' +
            [0, 0.05, 0.25, 0.6, 1].map((t) => `<i style="background:${shade(t * 100, 100)}"></i>`).join("") +
            " More</span>";

        // By code and by course
        // Every key of the loaded range (zero included, as the design), then any other met.
        const bars = (keyOf, keys, label) => {
            const a = new Map(keys.map((key) => [String(key), [0, 0]]));
            sel.forEach((r) => {
                const key = String(keyOf(r));
                if (!a.has(key)) a.set(key, [0, 0]);
                const x = a.get(key);
                x[0]++;
                if (r[8] > 0) x[1] += r[8];
            });
            const mx = Math.max(1, ...[...a.values()].map((x) => x[0]));
            return [...a.entries()].map(([key, x]) => `<div class="sah-brow"><div class="nm">${label(key)}</div><div class="sah-track"><i style="width:${(100 * x[0]) / mx}%"></i></div><div class="n">${n(x[0])}</div><div class="a">${gbp(x[1])}</div></div>`).join("") || '<p class="sah-hint">No confirmations in this range.</p>';
        };
        const codesShown = D.codes.filter((cd) => D.rows.some((r) => r[2] === cd.id)).map((cd) => cd.id);
        $("[data-sah-bycode]").innerHTML = bars((r) => r[2], codesShown, (id) => `${letter(codeOf[id])}<span>${esc(codeOf[id].name)}</span>`);
        const coursesShown = present(5).sort((a, b) => String(D.courses[a]).localeCompare(String(D.courses[b])));
        $("[data-sah-bycourse]").innerHTML = bars((r) => (r[5] === null ? "none" : r[5]), coursesShown, (id) =>
            id === "none" ? '<span>No course recorded</span>' : `<span title="${esc(D.courses[id])}">${esc(shortCourse[id] || D.courses[id])}</span>`
        );

        // Year × term: its own two filters left out so every cell stays readable (as the design)
        const years = present(3);
        const terms = present(4);
        const mx = {};
        let mmax = 0;
        let mtotal = 0;
        D.rows.forEach((r) => {
            if (r[1] >= F.from && r[1] <= F.to && (!any(F.code) || F.code[r[2]]) && (F.course === "" || String(r[5]) === F.course) && r[3] !== null && r[4] !== null) {
                const key = r[3] + "-" + r[4];
                mx[key] = (mx[key] || 0) + 1;
                mmax = Math.max(mmax, mx[key]);
                mtotal++;
            }
        });
        const matrix = $("[data-sah-matrix]");
        matrix.style.gridTemplateColumns = `70px repeat(${terms.length}, minmax(0, 1fr))`;
        let mh = '<span class="h"></span>' + terms.map((t) => `<span class="h">Term ${t}</span>`).join("");
        years.forEach((y) => {
            mh += `<span class="h r">Year ${y}</span>`;
            terms.forEach((t) => {
                const v = mx[y + "-" + t] || 0;
                const on = F.year[y] && F.term[t];
                const p = mtotal ? (100 * v) / mtotal : 0;
                mh += `<button type="button" data-y="${y}" data-t="${t}" class="${on ? "on" : ""}" style="background:${shade(v, mmax || 1)};color:${v / (mmax || 1) > 0.35 ? "#fff" : "var(--sah-ink)"}" aria-pressed="${!!on}" aria-label="Year ${y} Term ${t}: ${v} confirmations">${n(v)}<small>${v ? (p < 1 ? "<1%" : p.toFixed(0) + "%") : ""}</small></button>`;
            });
        });
        matrix.innerHTML = years.length && terms.length ? mh : '<p class="sah-hint">No year or term recorded in this range.</p>';

        // Worth checking
        const codeId = (letterCode) => (D.codes.find((cd) => cd.code === letterCode) || {}).id;
        const A = codeId("A");
        const S = codeId("S");
        const count = (fn) => sel.filter(fn).length;
        const items = [
            [count((r) => r[2] === A && r[7] === D.checks.suspended), "coded <b>A (in attendance)</b> for students whose status is SLC Suspended"],
            [count((r) => r[2] === S && r[7] === D.checks.enrolled), "coded <b>S (suspended)</b> for students whose status is SLC Enrolled"],
            [count((r) => r[2] === A && r[8] === null), "coded <b>A</b> with no claim amount recorded"],
            [count((r) => r[2] === A && r[7] === D.checks.left), "coded <b>A</b> for students whose status is SLC Left"],
        ].filter((x) => x[0]);
        const checks = $("[data-sah-checks]");
        checks.className = "sah-checks" + (items.length ? "" : " ok");
        checks.innerHTML = items.length ? items.map((x) => `<li><b class="num">${n(x[0])}</b><span>${x[1]}</span></li>`).join("") : "<li><span>Nothing unusual in this selection.</span></li>";

        drawTable(sel);
    }

    function drawTable(sel) {
        sel = sel || D.rows.filter((r) => pass(r));
        const q = T.q.trim().toLowerCase();
        if (q) sel = sel.filter((r) => ((D.students[r[9]] || ["", ""]).join(" ") + "").toLowerCase().includes(q));
        sel = sel.slice().sort((a, b) => (T.sort === "date" ? (a[1] < b[1] ? -1 : a[1] > b[1] ? 1 : a[0] - b[0]) : (a[8] ?? -1) - (b[8] ?? -1) || a[0] - b[0]) * T.dir);
        const pages = Math.max(1, Math.ceil(sel.length / T.per));
        T.page = Math.max(0, Math.min(pages - 1, T.page));
        const arrow = (s) => (T.sort === s ? (T.dir < 0 ? " ↓" : " ↑") : "");
        const sortAttr = (s) => (T.sort === s ? (T.dir < 0 ? "descending" : "ascending") : "none");
        let h = `<thead><tr><th scope="col">Student</th><th scope="col">Course</th><th scope="col">Intake</th><th scope="col">Year</th><th scope="col">Term</th><th scope="col">Code</th><th scope="col" class="r" aria-sort="${sortAttr("amount")}"><button type="button" data-sort="amount">Claim amount${arrow("amount")}</button></th><th scope="col" aria-sort="${sortAttr("date")}"><button type="button" data-sort="date">Confirmed on${arrow("date")}</button></th><th scope="col">Status</th></tr></thead><tbody>`;
        sel.slice(T.page * T.per, (T.page + 1) * T.per).forEach((r) => {
            const st = D.students[r[9]] || ["", "Student #" + r[9]];
            const cd = codeOf[r[2]] || { code: "?", name: "Unknown code", colour: "#4a5b6b" };
            h += `<tr><td><a href="${esc(route("student.show", r[9]))}" target="_blank" rel="noopener">${esc(st[1])}</a><small>${esc(st[0])}</small></td>`;
            h += `<td class="course"${r[5] !== null ? ` title="${esc(D.courses[r[5]])}"` : ""}>${r[5] !== null ? esc(shortCourse[r[5]] || D.courses[r[5]]) : "–"}</td><td>${r[6] !== null ? esc(D.intakes[r[6]]) : "–"}</td>`;
            h += `<td>${r[3] !== null ? "Year " + r[3] : "–"}</td><td>${r[4] !== null ? "Term " + r[4] : "–"}</td><td><span class="sah-pill">${letter(cd)}${esc(cd.name)}</span></td>`;
            h += `<td class="r">${r[8] === null ? "–" : money(r[8])}</td><td>${nice(r[1])}</td><td>${r[7] !== null ? esc(D.statuses[r[7]]) : "–"}</td></tr>`;
        });
        if (!sel.length) h += `<tr><td colspan="9" class="none">${q ? "No student in this selection matches that search." : "No confirmations match these filters."}</td></tr>`;
        $("[data-sah-table]").innerHTML = h + "</tbody>";
        $("[data-sah-tcount]").textContent = sel.length ? `Showing ${n(T.page * T.per + 1)} to ${n(Math.min(sel.length, (T.page + 1) * T.per))} of ${n(sel.length)}` : "0 confirmations";
        $("[data-sah-tpage]").textContent = `Page ${n(T.page + 1)} of ${n(pages)}`;
        $('[data-sah-page="-1"]').disabled = T.page === 0;
        $('[data-sah-page="1"]').disabled = T.page >= pages - 1;
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

    // A new date range: before the first search it only fills the form; after it, within what is
    // loaded it filters, otherwise it loads that range.
    function setRange(from, to) {
        if (!from || !to) return;
        if (from > to) [from, to] = [to, from];
        if (!searched) {
            F.from = from;
            F.to = to;
            refresh(true);
        } else if (L && from >= L.from && to <= L.to) {
            F.from = from;
            F.to = to;
            refresh(true);
        } else {
            load(from, to);
        }
    }

    /* ── Events ── */
    root.addEventListener("click", (e) => {
        const t = e.target;
        let b;
        if ((b = t.closest("[data-sah-search]"))) {
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
            const g = b.dataset.f;
            const key = b.dataset.k;
            F[g][key] = !F[g][key];
            refresh();
        } else if ((b = t.closest("[data-preset]"))) {
            const p = PRESETS.find((x) => x[0] === b.dataset.preset);
            setRange(p[2](), today());
        } else if (t.closest("[data-reset]")) {
            const base = searched ? L : DEFAULT;
            F = { from: base.from, to: base.to, code: {}, year: {}, term: {}, course: "" };
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
        } else if ((b = t.closest("[data-y]"))) {
            const y = b.dataset.y;
            const tm = b.dataset.t;
            const only = (o) => Object.keys(o).filter((q) => o[q]);
            const on = F.year[y] && F.term[tm] && only(F.year).length === 1 && only(F.term).length === 1;
            F.year = {};
            F.term = {};
            if (!on) {
                F.year[y] = true;
                F.term[tm] = true;
            }
            refresh();
        } else if ((b = t.closest("[data-sort]"))) {
            const s = b.dataset.sort;
            if (T.sort === s) T.dir = -T.dir;
            else {
                T.sort = s;
                T.dir = -1;
            }
            T.page = 0;
            drawTable();
        } else if ((b = t.closest("[data-sah-page]"))) {
            T.page += Number(b.dataset.sahPage);
            drawTable();
        } else if ((b = t.closest("[data-sah-export]"))) {
            e.preventDefault();
            if (b.getAttribute("aria-disabled") === "true") return;
            const p = new URLSearchParams({ from: F.from, to: F.to });
            Object.keys(F.code).filter((x) => F.code[x]).forEach((x) => p.append("codes[]", x));
            Object.keys(F.year).filter((x) => F.year[x]).forEach((x) => p.append("years[]", x));
            Object.keys(F.term).filter((x) => F.term[x]).forEach((x) => p.append("terms[]", x));
            if (F.course !== "") p.append("course", F.course);
            window.location.href = route(root.dataset.excel) + "?" + p.toString();
        }
    });
    root.addEventListener("change", (e) => {
        if (e.target.id === "sahFrom" || e.target.id === "sahTo") {
            const from = e.target.id === "sahFrom" ? e.target.value : F.from;
            const to = e.target.id === "sahTo" ? e.target.value : F.to;
            setRange(from, to);
        } else if (e.target.id === "sahCourse") {
            F.course = e.target.value;
            refresh(true);
        }
    });
    $("[data-sah-q]").addEventListener("input", (e) => {
        T.q = e.target.value;
        T.page = 0;
        if (D) drawTable();
    });
    const cal = $("[data-sah-cal]");
    cal.addEventListener("mousemove", (e) => {
        const b = e.target.closest("[data-day]");
        if (!b) {
            tip.hidden = true;
            return;
        }
        tip.textContent = `${nice(b.dataset.day)}: ${n(Number(b.dataset.v))} confirmations`;
        tip.hidden = false;
        tip.style.left = Math.min(window.innerWidth - 240, e.clientX + 14) + "px";
        tip.style.top = e.clientY - 38 + "px";
    });
    cal.addEventListener("mouseleave", () => (tip.hidden = true));

    // Nothing loads until Search (user): the form only.
    drawFilters();
    paintIcons();
})();
