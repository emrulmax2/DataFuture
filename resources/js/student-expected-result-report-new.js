/*
 * Reports › Student Expected Result, new version (user's student-expected-result-report design,
 * 25 Nov 2026). Attendance semester is required. Search loads every found student per chosen
 * attendance semester with Yes / No per unit once (GET .../data); the KPIs, course and group lines,
 * checks, student tables, filters and paging all work from that in the browser. The form, version
 * switch and Old-tab link are the shared report-filters.js.
 */
import { esc, n, pc, plural, OFF, filterForm } from "./report-filters";

(function () {
    "use strict";

    const root = document.querySelector("[data-ser]");
    if (!root) return;

    const OPT = JSON.parse(root.dataset.options || "{}");
    const URLS = { data: root.dataset.dataUrl, excel: root.dataset.excelUrl, student: root.dataset.studentUrl };
    const out = root.querySelector("[data-srp-out]");
    const PER = 20;
    const START = '<div class="srp-start">Choose your filters, then select Search. Attendance semester is required.</div>';

    let D = null;
    let PARAMS = "";
    let CRIT = "";
    let S = {};
    let request = 0;

    const filters = filterForm({
        root,
        form: root.querySelector("[data-srp-form]"),
        options: OPT,
        required: ["attend"],
        onSearch: search,
        onReset: () => {
            D = null;
            request++;
            filters.busy(false);
            render();
        },
    });

    async function search(params) {
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
            S = { key: biggest(D.rows), view: "all", q: "", page: 0 };
            render();
        } catch (e) {
            if (mine !== request) return;
            D = null;
            out.innerHTML = `<div class="srp-start" role="alert"><b>${esc(e.message || "The report could not be loaded.")}</b><br>Please try again.</div>`;
        } finally {
            if (mine === request) filters.busy(false);
        }
    }

    /* Row: [id, reg, name, status, course, term, group, [[unit, Y|N]], awarding body ref, intake]. */
    const exp = (r) => r[7].some((u) => u[1] === "Y");
    const status = (r) => D.statuses[r[3]] || "";
    const key = (r) => `${r[4]}|${r[5]}`;
    const CHECKS = [
        ["c1", "Discarded, but a result is expected", (r) => /discard/i.test(status(r)) && exp(r)],
        ["c2", "Suspended, but a result is expected", (r) => /suspend/i.test(status(r)) && exp(r)],
        ["c5", "Left or withdrawn, but a result is expected", (r) => /left|withdraw|terminat/i.test(status(r)) && exp(r)],
        ["c3", "Enrolled, but no result is expected", (r) => /enrolled/i.test(status(r)) && !exp(r)],
        ["c4", "Result expected, but no awarding body reference", (r) => exp(r) && !r[8]],
    ];
    const worth = (r) => CHECKS.some((c) => c[2](r));
    function biggest(rows, test = () => true) {
        const count = {};
        rows.forEach((r) => test(r) && (count[key(r)] = (count[key(r)] || 0) + 1));
        return Object.keys(count).sort((a, b) => count[b] - count[a])[0] || "";
    }

    /* ---------- results ---------- */
    const short = (c) => String(c).replace(/^HND in /i, "").replace(/ \(.*?\)/g, "");
    const meter = (a, e) => `<div class="srp-meter"><i style="width:${e ? Math.min(100, (100 * a) / e) : 0}%"></i></div>`;
    const multi = () => D.terms.length > 1;
    const unitsOf = (rows) => [...new Set(rows.flatMap((r) => r[7].map((u) => u[0])))].sort((a, b) => String(D.units[a]).localeCompare(String(D.units[b])));
    const keyName = (k, full) => {
        const [c, t] = k.split("|").map(Number);
        const course = full ? D.courses[c] : short(D.courses[c]);
        return multi() ? `${course} · ${D.terms[t]}` : course;
    };

    function render() {
        if (!D) {
            out.innerHTML = START;
            return;
        }
        const R = D.rows;
        const unit = multi() ? "student semester" : "student";
        if (!R.length) {
            out.innerHTML = `<div class="srp-start"><b>No students found.</b><br>${esc(CRIT)}</div>`;
            return;
        }
        let yes = 0, results = 0;
        const C = {}, G = {};
        R.forEach((r) => {
            const e = exp(r);
            if (e) yes++;
            r[7].forEach((u) => u[1] === "Y" && results++);
            const k = key(r);
            const c = C[k] || (C[k] = { s: 0, y: 0, rows: [] });
            c.s++;
            c.rows.push(r);
            if (e) c.y++;
            const gk = `${k}|${r[6]}`;
            const g = G[gk] || (G[gk] = { s: 0, y: 0, k, g: r[6] });
            g.s++;
            if (e) g.y++;
        });
        const students = new Set(R.map((r) => r[0])).size;
        const checks = CHECKS.map((c) => R.filter(c[2]).length);
        const anyCheck = R.filter(worth).length;

        let h = `<div class="srp-panel srp-res"><div class="srp-top"><div><div class="srp-found"><b>${n(students)}</b> student${students === 1 ? "" : "s"} found${multi() ? ` <span class="srp-muted">· ${plural(R.length, "student semester")}</span>` : ""}</div><div class="srp-crit">${esc(CRIT)}</div></div>`;
        h += `<a class="srp-btn" href="${esc(URLS.excel)}?${esc(PARAMS)}" download data-srp-excel><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>Export Excel</a></div>`;
        h += `<div class="srp-kpis"><div class="srp-kpi is-good"><div class="k">Result expected</div><div class="v">${n(yes)}</div><div class="s">${pc(yes, R.length)} of ${unit}s</div></div>`;
        h += `<div class="srp-kpi"><div class="k">No result expected</div><div class="v">${n(R.length - yes)}</div><div class="s">${pc(R.length - yes, R.length)} of ${unit}s</div></div>`;
        h += `<div class="srp-kpi"><div class="k">Unit results expected</div><div class="v">${n(results)}</div><div class="s">Across all units ${multi() ? "in these semesters" : "this semester"}</div></div>`;
        h += `<div class="srp-kpi${anyCheck ? " is-alert" : ""}"><div class="k">Worth checking</div><div class="v">${n(anyCheck)}</div><div class="s">${multi() ? "Student semesters" : "Students"} whose status and answer do not fit</div></div></div>`;

        const row = (cls, name, o) =>
            `<div class="srp-cgr ${cls}"><span>${name}</span><span class="r">${n(o.s)}</span>${meter(o.y, o.s)}<span class="r">${n(o.y)} <span class="srp-muted" style="font-weight:400">· ${pc(o.y, o.s)}</span></span><span class="r${o.s - o.y ? "" : " zero"}">${n(o.s - o.y)}</span></div>`;
        h += '<section class="srp-sec"><div class="srp-sechead"><h2>By course and group</h2><span class="srp-hint">Students with a result expected</span></div><div class="srp-cg"><div class="srp-cgh"><span>Course and group</span><span class="r">Students</span><span></span><span class="r">Result expected</span><span class="r">Not expected</span></div>';
        Object.keys(C)
            .sort((a, b) => C[b].s - C[a].s)
            .forEach((k) => {
                const units = unitsOf(C[k].rows);
                h += row("course", `${esc(keyName(k, true))}<small>${units.length ? "Units: " + units.map((u) => esc(D.units[u])).join(" · ") : "No unit classes"}</small>`, C[k]);
                Object.values(G)
                    .filter((g) => g.k === k)
                    .sort((a, b) => String(D.groups[a.g]).localeCompare(String(D.groups[b.g]), "en", { numeric: true }))
                    .forEach((g) => (h += row("grp", esc(D.groups[g.g] || "No group"), g)));
            });
        h += "</div></section>";

        if (anyCheck) {
            h += `<section class="srp-sec"><div class="srp-sechead"><h2>Worth checking</h2><span class="srp-hint">Select one to list those ${unit}s</span></div><div class="srp-chk">${CHECKS.map((c, k) => (checks[k] ? `<button type="button" data-check="${c[0]}"><b>${n(checks[k])}</b><span>${esc(c[1])}</span></button>` : "")).join("")}</div></section>`;
        }

        const keys = Object.keys(C).sort((a, b) => keyName(a).localeCompare(keyName(b)));
        if (!C[S.key]) S.key = biggest(R);
        const cur = C[S.key];
        h += `<section class="srp-sec" data-srp-students><div class="srp-coursebar"><div><div class="t">Students · ${esc(keyName(S.key, false))}</div><div class="srp-hint">${plural(cur.s, "student")} · ${plural(unitsOf(cur.rows).length, "unit")} ${multi() ? "in " + esc(D.terms[S.key.split("|")[1]]) : "this semester"}</div></div>`;
        if (keys.length > 4) {
            h += `<label class="srp-field srp-coursepick"><span>Course</span><select class="srp-ctl" data-srp-key>${keys.map((k) => `<option value="${k}"${k === S.key ? " selected" : ""}>${esc(keyName(k, true))} (${n(C[k].s)})</option>`).join("")}</select></label>`;
        } else if (keys.length > 1) {
            h += `<div class="srp-seg" role="group" aria-label="Course">${keys.map((k) => `<button type="button" data-key="${k}" aria-pressed="${k === S.key}" title="${esc(keyName(k, true))}">${esc(keyName(k))}<small>${n(C[k].s)}</small></button>`).join("")}</div>`;
        }
        h += "</div>";
        h += `<div class="srp-tools"><label class="srp-field"><span>Find a student</span><input type="search" data-srp-q placeholder="Name or student ID" value="${esc(S.q)}"></label><div class="srp-field is-wide"><span id="ser-vl">Show</span><div class="srp-seg" role="group" aria-labelledby="ser-vl" data-srp-views></div></div></div><div data-srp-tbl></div></section>`;

        const notes = [
            "Yes: the student is active in that unit's class, so a result is expected. No: they were made inactive in the class. As in the old export.",
            "Units are the Theory classes (and classes with no type, other than Group Tutorial) on the student's own course in the semester.",
        ];
        if (multi()) notes.push("Each student appears once per chosen attendance semester.");
        if (D.unplaced) notes.push(`${plural(D.unplaced, "student")} found by the search ${D.unplaced === 1 ? "has" : "have"} no class on their course in ${multi() ? "these semesters" : "this semester"} (for example after a course change), so ${D.unplaced === 1 ? "is" : "are"} not listed, as in the old export.`);
        h += `<ul class="srp-notes">${notes.map((x) => `<li>${esc(x)}</li>`).join("")}</ul></div>`;

        out.innerHTML = h;
        draw();
    }

    function draw() {
        const rows = D.rows.filter((r) => key(r) === S.key);
        const units = unitsOf(rows);
        const q = S.q.trim().toLowerCase();
        const test = { all: () => true, yes: exp, no: (r) => !exp(r), chk: worth };
        CHECKS.forEach((c) => (test[c[0]] = c[2]));
        if (!test[S.view]) S.view = "all";
        const extra = CHECKS.find((c) => c[0] === S.view);
        out.querySelector("[data-srp-views]").innerHTML = [
            ["all", "All"],
            ["yes", "Result expected"],
            ["no", "Not expected"],
            ["chk", "Worth checking"],
        ]
            .map((v) => `<button type="button" data-view="${v[0]}" aria-pressed="${S.view === v[0] || (v[0] === "chk" && !!extra)}">${v[1]}<small>${n(rows.filter(test[v[0]]).length)}</small></button>`)
            .join("");
        const list = rows
            .filter((r) => test[S.view](r) && (!q || String(r[1]).toLowerCase().includes(q) || String(r[2]).toLowerCase().includes(q)))
            .sort((a, b) => String(a[1]).localeCompare(String(b[1]), "en", { numeric: true }));
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);
        const cols = units.length + 2;
        let h = extra ? `<p class="srp-hint" style="margin:0 0 8px"><b style="color:var(--srp-warn)">${esc(extra[1])}</b> · ${n(list.length)} in this course</p>` : "";
        h += `<div class="srp-scroll"><table class="srp-table srp-ex"><thead><tr><th scope="col">Student</th>${units.map((u) => `<th scope="col" class="u">${esc(D.units[u])}</th>`).join("")}<th scope="col">Awarding body ref</th></tr></thead><tbody>`;
        if (!part.length) h += `<tr><td colspan="${cols}" class="srp-muted" style="padding:20px;text-align:center">No students match.</td></tr>`;
        part.forEach((r) => {
            const st = status(r);
            const by = Object.fromEntries(r[7].map((u) => [u[0], u[1]]));
            h += `<tr class="s2"><td class="srp-who2"><a class="reg" href="${esc(URLS.student.replace("__ID__", r[0]))}" target="_blank" rel="noopener" title="Open ${esc(r[2])}'s record">${esc(r[1] || "–")}</a><small>${esc(r[2])}</small><small>${esc(D.groups[r[6]] || "No group")}</small><small class="st${OFF.test(st) ? " is-off" : ""}">${esc(st || "No status")}</small></td>`;
            units.forEach((u) => {
                const g = by[u];
                h += `<td class="u">${g ? `<span class="srp-yn ${g}">${g === "Y" ? "Yes" : "No"}</span>` : '<span class="srp-yn x" aria-label="No class">·</span>'}</td>`;
            });
            h += `<td>${r[8] ? esc(r[8]) : `<span class="srp-miss">${exp(r) ? "Missing" : "–"}</span>`}</td></tr>`;
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
        if ((b = e.target.closest("[data-check]"))) {
            const c = CHECKS.find((x) => x[0] === b.dataset.check);
            const here = D.rows.some((r) => key(r) === S.key && c[2](r));
            S.key = here ? S.key : biggest(D.rows, c[2]);
            S.view = c[0];
            S.q = "";
            S.page = 0;
            render();
            const el = out.querySelector("[data-srp-students]");
            if (el) el.scrollIntoView({ block: "start" });
        } else if ((b = e.target.closest("[data-key]"))) {
            S.key = b.dataset.key;
            S.view = "all";
            S.q = "";
            S.page = 0;
            render();
            const f = out.querySelector(`[data-key="${S.key}"]`);
            if (f) f.focus();
        } else if ((b = e.target.closest("[data-view]"))) {
            S.view = b.dataset.view;
            S.page = 0;
            draw();
            out.querySelector(`[data-view="${S.view}"]`).focus();
        } else if ((b = e.target.closest("[data-page]"))) {
            S.page += +b.dataset.page;
            draw();
        }
    });
    out.addEventListener("change", (e) => {
        if (!e.target.matches("[data-srp-key]")) return;
        S.key = e.target.value;
        S.view = "all";
        S.q = "";
        S.page = 0;
        render();
        out.querySelector("[data-srp-key]").focus();
    });
    out.addEventListener("input", (e) => {
        if (!e.target.matches("[data-srp-q]")) return;
        S.q = e.target.value;
        S.page = 0;
        draw();
    });

    render();
})();
