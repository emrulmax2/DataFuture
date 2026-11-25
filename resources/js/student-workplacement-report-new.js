/*
 * Reports › Student Work Placement, new version (user's student-work-placement-report design,
 * 25 Nov 2026). Search loads every found student with required and confirmed hours by level and
 * learning hours once (GET .../data); the KPIs, split, course and intake lines, student table,
 * module hours, filters and paging all work from that in the browser. The form, version switch and
 * Old-tab link are the shared report-filters.js.
 */
import { esc, n, pc, plural, OFF, filterForm } from "./report-filters";

(function () {
    "use strict";

    const root = document.querySelector("[data-swp]");
    if (!root) return;

    const OPT = JSON.parse(root.dataset.options || "{}");
    const URLS = { data: root.dataset.dataUrl, excel: root.dataset.excelUrl, student: root.dataset.studentUrl };
    const out = root.querySelector("[data-srp-out]");
    const PER = 30;
    const CHEV = '<svg class="srp-chev" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';
    const STATE_COL = ["#fbe3d0;color:#7a3409", "#9fd0c8;color:#04302c", "var(--srp-gM);color:#04302c", "var(--srp-teal);color:#fff"];

    let D = null;
    let R = [];
    let PARAMS = "";
    let CRIT = "";
    let S = {};
    let STATE = [];
    let request = 0;

    const filters = filterForm({
        root,
        form: root.querySelector("[data-srp-form]"),
        options: OPT,
        onSearch: search,
        onReset: () => {
            D = null;
            request++;
            filters.busy(false);
            render();
        },
    });

    async function search(params) {
        if (!params) {
            D = null;
            out.innerHTML = '<div class="srp-start"><b>Choose at least one filter</b>, then select Search.</div>';
            return;
        }
        const mine = ++request;
        filters.busy(true);
        out.innerHTML = '<div class="srp-start" role="status"><span class="srp-spin" aria-hidden="true"></span>Loading results…</div>';
        try {
            const res = await fetch(`${URLS.data}?${params}`, { headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" }, credentials: "same-origin" });
            const body = await res.json().catch(() => ({}));
            if (mine !== request) return;
            if (!res.ok) throw new Error(body.message || "The report could not be loaded.");
            D = body;
            PARAMS = params;
            CRIT = filters.criteria();
            const first = (D.levels[0] || ["Level 4"])[0];
            STATE = ["Not started", `${first} in progress`, `${first} hours met`, "All hours met"];
            R = D.rows.map(summarise);
            S = { view: "all", q: "", sort: "reg", page: 0, open: {} };
            render();
        } catch (e) {
            if (mine !== request) return;
            D = null;
            out.innerHTML = `<div class="srp-start" role="alert"><b>${esc(e.message || "The report could not be loaded.")}</b><br>Please try again.</div>`;
        } finally {
            if (mine === request) filters.busy(false);
        }
    }

    /* Row: [id, reg, name, status, course, intake, need by level, done by level, required, confirmed total, pending, modules, has records]. */
    function summarise(r) {
        const need = r[6];
        const got = r[7];
        const total = r[9];
        const required = r[8];
        const met = need.map((l, i) => l[0] > 0 && got[i][0] >= l[0]);
        let state = 0;
        if (total > 0) {
            if (!need.length) state = required && total >= required ? 3 : 1;
            else if (!met[0]) state = 1;
            else state = met.every(Boolean) ? 3 : 2;
        }
        return { r, total, required, counted: Math.min(total, required), state, met, later: got.slice(1).some((l) => l[0] > 0) };
    }

    /* ---------- results ---------- */
    const hr = (v) => (Math.round(v * 100) / 100).toLocaleString("en-GB");
    const short = (c) => String(c).replace(/^HND in /i, "").replace(/ \(.*?\)/g, "");
    const meter = (a, e, cls) => `<div class="srp-meter${cls || ""}"><i style="width:${e ? Math.min(100, (100 * a) / e) : 0}%"></i></div>`;
    function line(name, s, done, need, title) {
        return `<div class="srp-tl"><span${title ? ` title="${esc(title)}"` : ""}>${esc(name)}</span><span class="r">${n(s)}</span>${meter(done, need)}<span class="r">${hr(done / s)} <span class="srp-muted">of ${hr(need / s)} h</span></span><span class="srp-pctv">${pc(done, need)}</span></div>`;
    }

    function render() {
        if (!D) {
            out.innerHTML = '<div class="srp-start">Choose your filters, then select Search.</div>';
            return;
        }
        if (!R.length) {
            out.innerHTML = `<div class="srp-start"><b>No students found.</b><br>${esc(CRIT)}<br>Only students with work placement records, or on a course with a work placement requirement, are listed.</div>`;
            return;
        }
        let done = 0, need = 0, later = 0, norec = 0;
        const st = [0, 0, 0, 0];
        const C = {}, I = {}, M = {};
        R.forEach((x) => {
            done += x.counted;
            need += x.required;
            st[x.state]++;
            if (x.later) later++;
            if (!x.r[12]) norec++;
            [[C, x.r[4]], [I, x.r[5]]].forEach(([map, k]) => {
                const o = map[k] || (map[k] = { s: 0, d: 0, n: 0 });
                o.s++;
                o.d += x.counted;
                o.n += x.required;
            });
            x.r[11].forEach(([m, h]) => {
                const o = M[m] || (M[m] = { s: 0, h: 0 });
                o.s++;
                o.h += h;
            });
        });
        const metFirst = st[2] + st[3];
        const firstName = (D.levels[0] || ["Level 4"])[0];
        const laterName = (D.levels[1] || ["Level 5"])[0];

        let h = `<div class="srp-panel srp-res"><div class="srp-top"><div><div class="srp-found"><b>${n(R.length)}</b> student${R.length === 1 ? "" : "s"} found</div><div class="srp-crit">${esc(CRIT)}</div></div>`;
        h += `<a class="srp-btn" href="${esc(URLS.excel)}?${esc(PARAMS)}" download data-srp-excel><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>Export Excel</a></div>`;
        h += `<div class="srp-kpis"><div class="srp-kpi is-good"><div class="k">Required hours completed</div><div class="v">${pc(done, need)}</div><div class="s">${hr(done)} of ${hr(need)} hours, counted up to each student's requirement</div></div>`;
        h += `<div class="srp-kpi"><div class="k">${esc(firstName)} hours met</div><div class="v">${n(metFirst)}</div><div class="s">${pc(metFirst, R.length)} of students have their ${esc(firstName)} hours</div></div>`;
        h += `<div class="srp-kpi${st[0] ? " is-alert" : ""}"><div class="k">Not started</div><div class="v">${n(st[0])}</div><div class="s">No confirmed hours${norec ? ` · ${n(norec)} with no placement record yet` : ""}</div></div>`;
        h += `<div class="srp-kpi"><div class="k">${esc(laterName)} started</div><div class="v">${n(later)}</div><div class="s">Students with any ${esc(laterName)} hours</div></div></div>`;

        const shown = [0, 1, 2, 3].filter((k) => k < 3 || st[k]);
        h += `<section class="srp-sec"><div class="srp-sechead"><h2>Where students are</h2><span class="srp-legend">${shown
            .map((k) => `<span><i class="srp-sw" style="background:${STATE_COL[k].split(";")[0]}"></i>${esc(STATE[k])} <b>${n(st[k])}</b></span>`)
            .join("")}</span></div><div class="srp-split" role="img" aria-label="${esc(shown.map((k) => `${STATE[k]} ${st[k]}`).join(", "))}">${shown
            .map((k) => (st[k] ? `<i style="flex:${st[k]};background:${STATE_COL[k]}">${n(st[k])}</i>` : ""))
            .join("")}</div></section>`;

        h += '<div class="srp-two is-even"><section class="srp-sec"><div class="srp-sechead"><h2>By course</h2><span class="srp-hint">Average hours per student</span></div><div><div class="srp-tlh"><span>Course</span><span class="r">Students</span><span></span><span class="r">Average</span><span class="r">Share</span></div>';
        h += Object.keys(C).map((k) => line(short(D.courses[k]), C[k].s, C[k].d, C[k].n, D.courses[k])).join("");
        h += '</div></section><section class="srp-sec"><div class="srp-sechead"><h2>By intake</h2><span class="srp-hint">Average hours per student</span></div><div><div class="srp-tlh"><span>Intake</span><span class="r">Students</span><span></span><span class="r">Average</span><span class="r">Share</span></div>';
        h += Object.keys(I)
            .sort((a, b) => String(D.intakes[b]).localeCompare(String(D.intakes[a]), "en", { numeric: true }))
            .map((k) => line(D.intakes[k] || "No intake", I[k].s, I[k].d, I[k].n))
            .join("");
        h += "</div></section></div>";

        h += `<section class="srp-sec"><div class="srp-sechead"><h2>Students</h2><span class="srp-legend"><span><b style="color:var(--srp-teal)">Teal</b> = requirement met</span><span><b style="color:var(--srp-warn)">Orange</b> = short, although the level total is met</span></span></div>`;
        h += `<div class="srp-tools"><label class="srp-field"><span>Find a student</span><input type="search" data-srp-q placeholder="Name or student ID" value="${esc(S.q)}"></label>`;
        h += '<div class="srp-field is-wide"><span id="swp-vl">Show</span><div class="srp-seg" role="group" aria-labelledby="swp-vl" data-srp-views></div></div>';
        h += '<label class="srp-field" style="flex-basis:220px"><span>Sort by</span><select class="srp-ctl" data-srp-sort><option value="reg">Student ID</option><option value="low">Fewest hours first</option><option value="high">Most hours first</option></select></label></div><div data-srp-tbl></div></section>';

        const mk = Object.keys(M).sort((a, b) => M[b].h - M[a].h);
        if (mk.length) {
            const mmax = M[mk[0]].h || 1;
            h += '<section class="srp-sec"><div class="srp-sechead"><h2>Hours logged by module</h2><span class="srp-hint">Confirmed hours, all students in this result</span></div><div><div class="srp-tlh"><span>Module</span><span class="r">Students</span><span></span><span class="r">Hours</span><span class="r">Average</span></div>';
            h += mk
                .map((k) => `<div class="srp-tl"><span${+k ? "" : ' class="low"'}>${esc(+k ? D.modules[k] : "No module recorded")}</span><span class="r">${n(M[k].s)}</span>${meter(M[k].h, mmax)}<span class="r">${hr(M[k].h)} h</span><span class="srp-pctv">${hr(M[k].h / M[k].s)}</span></div>`)
                .join("");
            h += "</div></section>";
        }

        const notes = [
            "Required hours come from each student's work placement set-up; students with no records use their course's set-up for their start date. Completed = confirmed hours; pending hours are shown but not counted.",
            "Level hours are those logged against the level; guided and non-guided are the parts logged against each.",
            "The headline share counts each student only up to the hours required, so extra hours do not hide another student's gap.",
        ];
        if (norec) notes.push(`${plural(norec, "student")} on a course with a work placement requirement ${norec === 1 ? "has" : "have"} no placement record yet. The old report left them out; Export Excel includes them.`);
        notes.push("Module hours are confirmed hours by the module they were logged against.");
        h += `<ul class="srp-notes">${notes.map((x) => `<li>${esc(x)}</li>`).join("")}</ul></div>`;

        out.innerHTML = h;
        out.querySelector("[data-srp-sort]").value = S.sort;
        draw();
    }

    function fig(v, q, warn, cls) {
        const k = v >= q && q ? " ok" : warn ? " short" : v ? "" : " nil";
        return `<td class="srp-fg${k}${cls ? " " + cls : ""}"><b>${hr(v)}</b><span> / ${hr(q)}</span></td>`;
    }

    function draw() {
        const q = S.q.trim().toLowerCase();
        const views = [["all", "All", () => true], ["s0", STATE[0], (x) => x.state === 0], ["s1", "In progress", (x) => x.state === 1], ["s2", STATE[2], (x) => x.state === 2]];
        if (R.some((x) => x.state === 3)) views.push(["s3", STATE[3], (x) => x.state === 3]);
        const test = Object.fromEntries(views.map((v) => [v[0], v[2]]));
        if (!test[S.view]) S.view = "all";
        out.querySelector("[data-srp-views]").innerHTML = views
            .map((v) => `<button type="button" data-view="${v[0]}" aria-pressed="${S.view === v[0]}">${esc(v[1])}<small>${n(R.filter(v[2]).length)}</small></button>`)
            .join("");
        const list = R.filter((x) => test[S.view](x) && (!q || String(x.r[1]).toLowerCase().includes(q) || String(x.r[2]).toLowerCase().includes(q)));
        const byReg = (a, b) => String(a.r[1]).localeCompare(String(b.r[1]), "en", { numeric: true });
        if (S.sort === "reg") list.sort(byReg);
        if (S.sort === "low") list.sort((a, b) => a.total - b.total || byReg(a, b));
        if (S.sort === "high") list.sort((a, b) => b.total - a.total || byReg(a, b));
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);

        const reqs = [...new Set(R.map((x) => x.required))];
        const totalHead = reqs.length === 1 && reqs[0] ? `Total of ${hr(reqs[0])}` : "Total";
        const cols = 2 + D.levels.reduce((s, l) => s + l[1].length + 1, 0);
        let h = '<div class="srp-scroll"><table class="srp-table srp-wp"><thead><tr class="grp"><th rowspan="2" scope="col">Student</th>';
        h += D.levels.map((l) => `<th colspan="${l[1].length + 1}" class="gl" scope="colgroup">${esc(l[0])} hours</th>`).join("");
        h += `<th rowspan="2" class="tt" scope="col">${esc(totalHead)}</th></tr><tr>`;
        h += D.levels.map((l) => l[1].map((name, i) => `<th scope="col" class="r${i ? "" : " gl"}">${esc(name.replace(/ Learning$/i, ""))}</th>`).join("") + `<th scope="col" class="r${l[1].length ? "" : " gl"}">${esc(l[0])}</th>`).join("");
        h += "</tr></thead><tbody>";
        if (!part.length) h += `<tr><td colspan="${cols}" class="srp-muted" style="padding:20px;text-align:center">No students match.</td></tr>`;
        part.forEach((x, k) => {
            const r = x.r;
            const id = r[0];
            const mods = r[11];
            const op = !!S.open[id] && mods.length;
            const status = D.statuses[r[3]] || "";
            const intake = String(D.intakes[r[5]] || "").split(" ");
            const intakeShort = intake.length > 1 ? `${intake[1].slice(0, 3)} ${intake[0]}` : intake[0];
            h += `<tr class="s${k % 2 ? "" : " alt"}"><td><button type="button" class="srp-who3" data-open="${id}" aria-expanded="${!!op}"${mods.length ? "" : ' disabled style="cursor:default"'}>${mods.length ? CHEV : CHEV.replace("<svg", '<svg style="visibility:hidden"')}<span><b>${esc(r[1] || "–")}</b><small>${esc(r[2])}</small><small title="${esc(D.courses[r[4]] || "")}">${esc(short(D.courses[r[4]] || ""))} · ${esc(intakeShort)}</small></span></button>`;
            h += `<span class="srp-st-label${OFF.test(status) ? " is-off" : ""}">${esc(status || "No status")}</span>`;
            if (!r[12]) h += '<span class="srp-flag">No placement record yet</span>';
            else if (r[10] > 0) h += `<span class="srp-flag is-quiet">${hr(r[10])} h pending</span>`;
            h += "</td>";
            D.levels.forEach((l, li) => {
                const nd = r[6][li];
                const gt = r[7][li];
                l[1].forEach((_, i) => (h += fig(gt[1][i], nd[1][i], x.met[li], i ? "" : "gl")));
                h += fig(gt[0], nd[0], false, l[1].length ? "" : "gl");
            });
            const pct = x.required ? Math.round((100 * x.counted) / x.required) : 0;
            h += `<td class="tt"><div class="srp-tbar">${meter(x.total, x.required)}<b>${x.required ? pct + "%" : "–"}</b></div>${x.total > x.required && x.required ? `<small class="srp-over">${hr(x.total)} h, ${hr(x.total - x.required)} over</small>` : `<small class="srp-tsub">${hr(x.total)} of ${hr(x.required)} h</small>`}</td></tr>`;
            if (op) {
                const mx = Math.max(...mods.map((m) => m[1]), 1);
                h += `<tr class="d"><td colspan="${cols}"><div class="srp-mods"><div class="dh">Hours by module</div><div class="srp-ml">${mods
                    .slice()
                    .sort((a, b) => b[1] - a[1])
                    .map((m) => `<span${m[0] ? "" : ' class="low"'}>${esc(m[0] ? D.modules[m[0]] : "No module recorded")}</span>${meter(m[1], mx, " is-thin")}<span class="r">${hr(m[1])} h</span>`)
                    .join("")}</div><a class="srp-open" href="${esc(URLS.student.replace("__ID__", id))}" target="_blank" rel="noopener">Open student record</a></div></td></tr>`;
            }
        });
        h += `</tbody></table></div><div class="srp-foot"><span>${list.length ? `Showing ${n(S.page * PER + 1)} to ${n(S.page * PER + part.length)} of ${n(list.length)}` : "0 students"}</span>${
            pages > 1
                ? `<span class="srp-pager"><button type="button" class="srp-btn" data-page="-1"${S.page ? "" : " disabled"}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="srp-btn" data-page="1"${S.page < pages - 1 ? "" : " disabled"}>Next</button></span>`
                : ""
        }</div>`;
        out.querySelector("[data-srp-tbl]").innerHTML = h;
    }

    /* ---------- events (results) ---------- */
    out.addEventListener("click", (e) => {
        let b;
        if ((b = e.target.closest("[data-view]"))) {
            S.view = b.dataset.view;
            S.page = 0;
            draw();
            out.querySelector(`[data-view="${S.view}"]`).focus();
        } else if ((b = e.target.closest("[data-open]"))) {
            if (b.disabled) return;
            const i = b.dataset.open;
            S.open[i] = !S.open[i];
            draw();
            const w = out.querySelector(`[data-open="${i}"]`);
            if (w) w.focus();
        } else if ((b = e.target.closest("[data-page]"))) {
            S.page += +b.dataset.page;
            draw();
        }
    });
    out.addEventListener("change", (e) => {
        if (!e.target.matches("[data-srp-sort]")) return;
        S.sort = e.target.value;
        S.page = 0;
        draw();
    });
    out.addEventListener("input", (e) => {
        if (!e.target.matches("[data-srp-q]")) return;
        S.q = e.target.value;
        S.page = 0;
        draw();
    });

    render();
})();
