/*
 * Reports › Student Result Report, new version (user's student-result-report design, 25 Nov 2026).
 * Search loads every found student with their unit grades once (GET .../data); the KPIs, charts,
 * course switch, unit list, student table, filters and paging all work from that in the browser.
 * The form, version switch and Old-tab link are the shared report-filters.js.
 */
import { esc, n, pc, plural, OFF, filterForm } from "./report-filters";

(function () {
    "use strict";

    const root = document.querySelector("[data-srp]");
    if (!root) return;

    const OPT = JSON.parse(root.dataset.options || "{}");
    const URLS = { data: root.dataset.dataUrl, excel: root.dataset.excelUrl, student: root.dataset.studentUrl };
    const out = root.querySelector("[data-srp-out]");
    const CHEV = '<svg class="srp-chev" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';

    /* Grades: the design's six first, then any other code in the results. Completed = P, M, D (the old export's count). */
    const BASE = ["P", "M", "D", "U", "A", "R"];
    const EXTRA = ["C", "F", "RF", "W", "S", "SF"];
    const PASS = { P: 1, M: 1, D: 1 };
    const GNAMES = OPT.grades || {};
    const gname = (g) => GNAMES[g] || g;
    const gclass = (g) => (BASE.includes(g) || EXTRA.includes(g) ? g : "X");
    const legendName = (g) => (PASS[g] ? `${gname(g)} (${g})` : g);
    const chip = (g, title) => `<span class="srp-g ${gclass(g)}" title="${esc(title)}">${esc(g)}</span>`;
    const NO_CHIP = '<span class="srp-g none" aria-label="No result">·</span>';

    /* ---------- search ---------- */
    let D = null;
    let PARAMS = "";
    let CRIT = "";
    let S = {};
    const PER = 20;
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
            const present = {};
            D.rows.forEach((r) => (present[r[4]] = (present[r[4]] || 0) + 1));
            const first = Object.keys(present).sort((x, y) => present[y] - present[x])[0];
            S = { course: first === undefined ? 0 : +first, view: "all", q: "", sort: "reg", page: 0, open: {} };
            render();
        } catch (e) {
            if (mine !== request) return;
            D = null;
            out.innerHTML = `<div class="srp-start" role="alert"><b>${esc(e.message || "The report could not be loaded.")}</b><br>Please try again.</div>`;
        } finally {
            if (mine === request) filters.busy(false);
        }
    }

    /* ---------- results ---------- */
    const grades = (r) => r[6].filter(Boolean).concat(r[7].map((e) => e[1]));
    const done = (r) => grades(r).filter((g) => PASS[g]).length;
    const notp = (r) => grades(r).filter((g) => !PASS[g]).length;
    function order(t) {
        const extra = Object.keys(t).filter((g) => !BASE.includes(g)).sort((a, b) => {
            const ia = EXTRA.indexOf(a), ib = EXTRA.indexOf(b);
            return (ia < 0 ? 99 : ia) - (ib < 0 ? 99 : ib) || a.localeCompare(b);
        });
        return BASE.concat(extra);
    }
    function tally(rows) {
        const t = { P: 0, M: 0, D: 0, U: 0, A: 0, R: 0 };
        rows.forEach((r) => grades(r).forEach((g) => (t[g] = (t[g] || 0) + 1)));
        return t;
    }
    const colour = (g) => `var(--srp-g${gclass(g)})`;
    function stack(t, total) {
        const gs = order(t).filter((g) => t[g]);
        return `<div class="srp-stack" role="img" aria-label="${esc(gs.map((g) => `${gname(g)} ${t[g]}`).join(", ") || "No results")}">${gs
            .map((g) => `<i style="width:${(100 * t[g]) / total}%;background:${colour(g)}" title="${esc(gname(g))}: ${n(t[g])}"></i>`)
            .join("")}</div>`;
    }
    /* "U, A or R" (plus any other code these results hold). */
    const failWords = (t, and) => {
        const codes = order(t).filter((g) => !PASS[g] && (BASE.includes(g) || t[g]));
        return codes.length > 1 ? `${codes.slice(0, -1).join(", ")} ${and ? "and" : "or"} ${codes[codes.length - 1]}` : codes.join("");
    };

    function render() {
        if (!D) {
            out.innerHTML = '<div class="srp-start">Choose your filters, then select Search.</div>';
            return;
        }
        if (!D.rows.length) {
            out.innerHTML = `<div class="srp-start"><b>No students found.</b><br>${esc(CRIT)}</div>`;
            return;
        }
        const R = D.rows;
        const t = tally(R);
        const all = Object.values(t).reduce((s, v) => s + v, 0);
        const pass = t.P + t.M + t.D;
        let np = 0, none = 0, max = 0;
        const hist = [];
        R.forEach((r) => {
            const d = done(r);
            if (notp(r)) np++;
            if (!d) none++;
            hist[d] = (hist[d] || 0) + 1;
            if (d > max) max = d;
        });
        const noResults = R.filter((r) => !grades(r).length).length;
        let h = `<div class="srp-panel srp-res"><div class="srp-top"><div><div class="srp-found"><b>${n(R.length)}</b> student${R.length === 1 ? "" : "s"} found</div><div class="srp-crit">${esc(CRIT)}</div></div>`;
        h += `<a class="srp-btn" href="${esc(URLS.excel)}?${esc(PARAMS)}" download data-srp-excel><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>Export Excel</a></div>`;
        h += `<div class="srp-kpis"><div class="srp-kpi"><div class="k">Unit results recorded</div><div class="v">${n(all)}</div><div class="s">Across ${plural(R.length, "student")}</div></div>`;
        h += `<div class="srp-kpi is-good"><div class="k">Passed</div><div class="v">${pc(pass, all)}</div><div class="s">${n(pass)} result${pass === 1 ? "" : "s"} at Pass, Merit or Distinction</div></div>`;
        h += `<div class="srp-kpi${np ? " is-alert" : ""}"><div class="k">Students with a unit not passed</div><div class="v">${n(np)}</div><div class="s">At least one ${esc(failWords(t))} · ${pc(np, R.length)} of students</div></div>`;
        h += `<div class="srp-kpi"><div class="k">No completed units</div><div class="v">${n(none)}</div><div class="s">Students with no Pass, Merit or Distinction yet</div></div></div>`;

        const hmax = Math.max(1, ...hist.filter((x) => x));
        let bars = "", xs = "";
        for (let d = 0; d <= max; d++) {
            const c = hist[d] || 0;
            bars += `<div${d === max ? ' class="is-top"' : ""}><b>${c || ""}</b><i style="height:${c ? Math.max(2, (100 * c) / hmax) : 0}%"></i></div>`;
            xs += `<span>${d}</span>`;
        }
        h += `<div class="srp-two"><section class="srp-sec"><div class="srp-sechead"><h2>Grades awarded</h2><span class="srp-hint">All ${plural(all, "unit result")}</span></div>${all ? stack(t, all) : '<div class="srp-stack"></div>'}`;
        h += `<div class="srp-legend">${order(t)
            .filter((g) => BASE.includes(g) || t[g])
            .map((g) => `<span title="${esc(gname(g))}"><i class="srp-sw" style="background:${colour(g)}"></i>${esc(legendName(g))} <b>${n(t[g] || 0)}</b></span>`)
            .join("")}</div>`;
        h += `<p class="srp-hint">Completed units counts Pass, Merit and Distinction. ${esc(failWords(t, true))} are shown as recorded and count as not passed.</p></section>`;
        h += `<section class="srp-sec"><div class="srp-sechead"><h2>Completed units per student</h2><span class="srp-hint">Students by number completed</span></div><div><div class="srp-hist" role="img" aria-label="${n(hist[max] || 0)} students have completed ${max} units, the most in this result">${bars}</div><div class="srp-hx">${xs}</div></div></section></div>`;

        /* course */
        const cs = [];
        D.courses.forEach((c, ci) => {
            const k = R.filter((r) => r[4] === ci);
            if (k.length) cs.push([ci, k]);
        });
        const cur = cs.find((x) => x[0] === S.course) || cs[0];
        const ci = cur[0];
        const rows = cur[1];
        const C = D.courses[ci];
        const shorts = cs.map((x) => D.courses[x[0]].name.replace(/^HND in /i, "").replace(/ \(.*$/, ""));
        const clash = new Set(shorts).size !== shorts.length;
        h += `<section class="srp-sec"><div class="srp-coursebar"><div><div class="t">${esc(C.name)}</div><div class="srp-hint">${plural(rows.length, "student")} · ${plural(C.units.length, "unit")}${C.other ? ` · ${plural(C.other, "other unit")}` : ""}</div></div>`;
        if (cs.length > 4) {
            // Many courses: a list, the buttons would wrap into a block.
            h += `<label class="srp-field srp-coursepick"><span>Course</span><select class="srp-ctl" data-srp-course>${cs
                .map((x) => `<option value="${x[0]}"${x[0] === ci ? " selected" : ""}>${esc(D.courses[x[0]].name)} (${n(x[1].length)})</option>`)
                .join("")}</select></label>`;
        } else if (cs.length > 1) {
            h += `<div class="srp-seg" role="group" aria-label="Course">${cs
                .map((x, i) => `<button type="button" data-course="${x[0]}" aria-pressed="${x[0] === ci}" title="${esc(D.courses[x[0]].name)}">${esc(clash ? D.courses[x[0]].name : shorts[i])}<small>${x[1].length}</small></button>`)
                .join("")}</div>`;
        }
        h += "</div>";
        h += `<div class="srp-sechead"><h2>Results by unit</h2><span class="srp-hint">Bar length = share of the course's ${plural(rows.length, "student")}</span></div>`;
        if (!C.units.length) {
            h += '<p class="srp-hint">No unit results for these students yet.</p>';
        } else {
            h += '<div class="srp-units"><div class="srp-uhead"><span>No.</span><span>Unit</span><span></span><span class="r">Passed</span><span class="r">Not passed</span><span class="r">No result</span></div>';
            C.units.forEach((u, k) => {
                const tt = { P: 0, M: 0, D: 0, U: 0, A: 0, R: 0 };
                let got = 0;
                rows.forEach((r) => {
                    const g = r[6][k];
                    if (g) {
                        tt[g] = (tt[g] || 0) + 1;
                        got++;
                    }
                });
                const p = tt.P + tt.M + tt.D;
                const bad = order(tt).filter((g) => !PASS[g] && tt[g]).map((g) => `${g} ${tt[g]}`).join(" · ");
                h += `<div class="srp-urow"><span class="no">${k + 1}</span><span>${esc(D.units[u])}</span>${stack(tt, rows.length)}<span class="r">${n(p)} <span class="srp-muted">of ${n(got)} · ${pc(p, got)}</span></span><span class="r${bad ? " low" : " zero"}">${bad || "0"}</span><span class="r${rows.length - got ? "" : " zero"}">${n(rows.length - got)}</span></div>`;
            });
            h += "</div>";
        }
        h += "</section>";

        h += `<section class="srp-sec"><div class="srp-sechead"><h2>Results by student</h2><span class="srp-hint">Column numbers match the unit list. Select a student for unit names.</span></div>`;
        h += `<div class="srp-tools"><label class="srp-field"><span>Find a student</span><input type="search" data-srp-q placeholder="Name or student ID" value="${esc(S.q)}"></label>`;
        h += '<div class="srp-field is-wide"><span id="srp-vl">Show</span><div class="srp-seg" role="group" aria-labelledby="srp-vl" data-srp-views></div></div>';
        h += '<label class="srp-field" style="flex-basis:220px"><span>Sort by</span><select class="srp-ctl" data-srp-sort><option value="reg">Student ID</option><option value="name">Name</option><option value="done">Fewest completed first</option><option value="notp">Most not passed first</option></select></label></div><div data-srp-tbl></div></section>';

        const notes = [
            "Each unit shows the student's latest published result, as in the board result export (Export Excel).",
            "Completed counts Pass, Merit and Distinction; any other result counts as not passed.",
        ];
        if (D.courses.some((c) => c.other)) notes.push("Units fewer than 1 in 10 of a course's students have a result for are listed under Other.");
        if (noResults) notes.push(`${plural(noResults, "student")} ${noResults === 1 ? "has" : "have"} no unit results yet; they count under No completed units.`);
        notes.push("No assessment board outcome is recorded, so that column is blank, as in the export.");
        h += `<ul class="srp-notes">${notes.map((x) => `<li>${esc(x)}</li>`).join("")}</ul></div>`;

        out.innerHTML = h;
        out.querySelector("[data-srp-sort]").value = S.sort;
        S.rows = rows;
        S.ci = ci;
        draw();
    }

    const sortKey = (r) => String(r[1] || "");
    function draw() {
        const C = D.courses[S.ci];
        const rows = S.rows;
        const q = S.q.trim().toLowerCase();
        const cnt = { all: rows.length, np: rows.filter((r) => notp(r)).length, none: rows.filter((r) => !done(r)).length };
        out.querySelector("[data-srp-views]").innerHTML = [
            ["all", "All"],
            ["np", "With a unit not passed"],
            ["none", "No completed units"],
        ]
            .map((v) => `<button type="button" data-view="${v[0]}" aria-pressed="${S.view === v[0]}">${v[1]}<small>${n(cnt[v[0]])}</small></button>`)
            .join("");
        const list = rows.filter(
            (r) => (S.view === "all" || (S.view === "np" ? notp(r) : !done(r))) && (!q || String(r[1]).toLowerCase().includes(q) || String(r[2]).toLowerCase().includes(q))
        );
        const byReg = (a, b) => sortKey(a).localeCompare(sortKey(b), "en", { numeric: true });
        if (S.sort === "reg") list.sort(byReg);
        if (S.sort === "name") list.sort((a, b) => String(a[2]).replace(/^(mr|mrs|miss|ms|dr)\.?\s+/i, "").localeCompare(String(b[2]).replace(/^(mr|mrs|miss|ms|dr)\.?\s+/i, ""), "en", { sensitivity: "base" }) || byReg(a, b));
        if (S.sort === "done") list.sort((a, b) => done(a) - done(b) || byReg(a, b));
        if (S.sort === "notp") list.sort((a, b) => notp(b) - notp(a) || byReg(a, b));
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);
        const hasOther = rows.some((r) => r[7].length);
        const cols = C.units.length + (hasOther ? 7 : 6);
        let h = `<div class="srp-scroll"><table class="srp-table"><thead><tr><th scope="col">Student</th><th scope="col">Status</th><th scope="col">Group</th>${C.units
            .map((u, k) => `<th scope="col" class="u" title="${esc(D.units[u])}"><abbr title="${esc(D.units[u])}">${k + 1}</abbr></th>`)
            .join("")}${hasOther ? '<th scope="col" class="u is-other">Other</th>' : ""}<th scope="col">Completed</th><th scope="col" class="r">Not passed</th><th scope="col">Board outcome</th></tr></thead><tbody>`;
        if (!part.length) h += `<tr><td colspan="${cols}" class="srp-muted" style="padding:20px;text-align:center">No students match.</td></tr>`;
        part.forEach((r) => {
            const d = done(r);
            const x = notp(r);
            const op = !!S.open[r[0]];
            const status = D.statuses[r[3]] || "";
            const profile = URLS.student.replace("__ID__", r[0]);
            h += `<tr class="stu"><td><button type="button" class="srp-who" data-open="${r[0]}" aria-expanded="${op}">${CHEV}${esc(r[1] || "–")}</button><a class="srp-name" href="${esc(profile)}" target="_blank" rel="noopener" title="Open ${esc(r[2])}'s record">${esc(r[2])}</a></td>`;
            h += `<td class="srp-st${OFF.test(status) ? " is-off" : ""}">${esc(status)}</td><td class="srp-grp">${r[5].map((g) => esc(D.groups[g])).join("<br>") || '<span class="zero">–</span>'}</td>`;
            r[6].forEach((g, k) => (h += `<td class="u">${g ? chip(g, `${D.units[C.units[k]]}: ${gname(g)}`) : NO_CHIP}</td>`));
            if (hasOther) h += `<td class="u is-other">${r[7].map((e) => chip(e[1], `${D.units[e[0]]}: ${gname(e[1])}`)).join(" ") || NO_CHIP}</td>`;
            h += `<td><div class="srp-prog"><b>${d}</b><span class="bar"><i style="width:${C.units.length ? Math.min(100, (100 * d) / C.units.length) : 0}%"></i></span></div></td><td class="r${x ? " low" : " zero"}">${x}</td><td class="zero">–</td></tr>`;
            h += `<tr class="detail"${op ? "" : " hidden"}><td colspan="${cols}"><div class="srp-dl">${C.units
                .map((u, k) => {
                    const g = r[6][k];
                    return `<div>${g ? chip(g, gname(g)) : NO_CHIP}<span class="nm${g ? "" : " srp-muted"}" title="${esc(D.units[u])}">${k + 1}. ${esc(D.units[u])}</span></div>`;
                })
                .join("")}${r[7]
                .map((e) => `<div>${chip(e[1], gname(e[1]))}<span class="nm" title="${esc(D.units[e[0]])}">${esc(D.units[e[0]])} <span class="ex">(other unit)</span></span></div>`)
                .join("")}</div></td></tr>`;
        });
        h += `</tbody></table></div><div class="srp-foot"><span>${list.length ? `Showing ${n(S.page * PER + 1)} to ${n(S.page * PER + part.length)} of ${n(list.length)}` : "0 students"}</span><span class="srp-pager"><button type="button" class="srp-btn" data-page="-1"${S.page ? "" : " disabled"}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="srp-btn" data-page="1"${S.page < pages - 1 ? "" : " disabled"}>Next</button></span></div>`;
        out.querySelector("[data-srp-tbl]").innerHTML = h;
    }

    /* ---------- events (results) ---------- */
    out.addEventListener("click", (e) => {
        const t = e.target;
        let b;
        if ((b = t.closest("[data-course]"))) {
            S.course = +b.dataset.course;
            S.view = "all";
            S.q = "";
            S.page = 0;
            S.open = {};
            render();
            const f = out.querySelector(`[data-course="${S.course}"]`);
            if (f) f.focus();
        } else if ((b = t.closest("[data-view]"))) {
            S.view = b.dataset.view;
            S.page = 0;
            draw();
            out.querySelector(`[data-view="${S.view}"]`).focus();
        } else if ((b = t.closest("[data-open]"))) {
            const i = b.dataset.open;
            S.open[i] = !S.open[i];
            draw();
            const w = out.querySelector(`[data-open="${i}"]`);
            if (w) w.focus();
        } else if ((b = t.closest("[data-page]"))) {
            S.page += +b.dataset.page;
            draw();
        }
    });
    out.addEventListener("change", (e) => {
        const t = e.target;
        if (t.matches("[data-srp-course]")) {
            S.course = +t.value;
            S.view = "all";
            S.q = "";
            S.page = 0;
            S.open = {};
            render();
            out.querySelector("[data-srp-course]").focus();
        } else if (t.matches("[data-srp-sort]")) {
            S.sort = t.value;
            S.page = 0;
            draw();
        }
    });
    out.addEventListener("input", (e) => {
        if (!e.target.matches("[data-srp-q]")) return;
        S.q = e.target.value;
        S.page = 0;
        draw();
    });

    render();
})();
