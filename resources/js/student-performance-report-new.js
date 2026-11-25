/*
 * Reports › Result Grade Performance, new version (user's result-grade-performance-report design,
 * 25 Nov 2026). Search loads every found student with expected and achieved points per attendance
 * semester once (GET .../data); the KPIs, semester and course lines, bands, student grid, filters
 * and paging all work from that in the browser. The form, version switch and Old-tab link are the
 * shared report-filters.js.
 */
import { esc, n, pc, plural, OFF, filterForm } from "./report-filters";

(function () {
    "use strict";

    const root = document.querySelector("[data-spr]");
    if (!root) return;

    const OPT = JSON.parse(root.dataset.options || "{}");
    const URLS = { data: root.dataset.dataUrl, excel: root.dataset.excelUrl, student: root.dataset.studentUrl };
    const out = root.querySelector("[data-srp-out]");
    const PER = 20;
    const BANDS = ["0%", "1–24%", "25–49%", "50–74%", "75–99%", "100%"];

    let D = null;
    let R = [];
    let PARAMS = "";
    let CRIT = "";
    let S = {};
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
            R = D.rows.map((r) => {
                let e = 0, a = 0;
                r[5].forEach((c) => {
                    e += c[1];
                    a += c[2];
                });
                return { r, e, a };
            });
            S = { view: "all", q: "", sort: "reg", page: 0 };
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
    const num = (v) => n(Math.round(v * 100) / 100);
    const short = (c) => String(c).replace(/^HND in /i, "").replace(/ \(.*?\)/g, "");
    const tshort = (t) => {
        const m = String(t).match(/^(\d{4}) (\w{3})\w*(.*)$/);
        return m ? `${m[2]} ${m[1].slice(2)}${m[3] ? " " + m[3].replace(/[()]/g, "") : ""}` : t;
    };
    const meter = (a, e, low) => `<div class="srp-meter${low ? " is-low" : ""}"><i style="width:${e ? Math.min(100, (100 * a) / e) : 0}%"></i></div>`;
    function line(name, students, a, e, avg, title) {
        const low = e && avg != null && a / e < avg;
        return `<div class="srp-tl"><span${title ? ` title="${esc(title)}"` : ""}>${esc(name)}</span><span class="r">${n(students)}</span>${meter(a, e, low)}<span class="r">${num(a)} <span class="srp-muted">of ${num(e)}</span></span><span class="srp-pctv${low ? " low" : ""}">${pc(a, e)}</span></div>`;
    }
    const share = (x) => (x.e ? x.a / x.e : -1);
    const TESTS = {
        all: () => true,
        zero: (x) => x.e && !x.a,
        half: (x) => x.e && x.a / x.e < 0.5,
        full: (x) => x.e && x.a >= x.e,
    };

    function render() {
        if (!D) {
            out.innerHTML = '<div class="srp-start">Choose your filters, then select Search.</div>';
            return;
        }
        if (!R.length) {
            out.innerHTML = `<div class="srp-start"><b>No students found.</b><br>${esc(CRIT)}</div>`;
            return;
        }
        let E = 0, A = 0, full = 0, zero = 0, norec = 0, inactive = 0;
        const bands = [0, 0, 0, 0, 0, 0];
        const T = {};
        const C = {};
        R.forEach((x) => {
            E += x.e;
            A += x.a;
            x.r[5].forEach((c) => c[3] && inactive++);
            if (!x.e) {
                norec++;
                return;
            }
            const p = x.a / x.e;
            if (p >= 1) full++;
            if (!x.a) zero++;
            bands[!x.a ? 0 : p >= 1 ? 5 : p < 0.25 ? 1 : p < 0.5 ? 2 : p < 0.75 ? 3 : 4]++;
            x.r[5].forEach((c) => {
                const o = T[c[0]] || (T[c[0]] = { s: 0, e: 0, a: 0 });
                o.s++;
                o.e += c[1];
                o.a += c[2];
            });
            const o = C[x.r[3]] || (C[x.r[3]] = { s: 0, e: 0, a: 0 });
            o.s++;
            o.e += x.e;
            o.a += x.a;
        });
        const avg = E ? A / E : null;

        let h = `<div class="srp-panel srp-res"><div class="srp-top"><div><div class="srp-found"><b>${n(R.length)}</b> student${R.length === 1 ? "" : "s"} found</div><div class="srp-crit">${esc(CRIT)}</div></div>`;
        h += `<a class="srp-btn" href="${esc(URLS.excel)}?${esc(PARAMS)}" download data-srp-excel><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>Export Excel</a></div>`;
        h += `<div class="srp-kpis"><div class="srp-kpi is-good"><div class="k">Achieved of expected</div><div class="v">${pc(A, E)}</div><div class="s">${num(A)} achieved of ${num(E)} expected</div></div>`;
        h += `<div class="srp-kpi"><div class="k">Students at 100%</div><div class="v">${n(full)}</div><div class="s">Achieved everything expected · ${pc(full, R.length - norec)}</div></div>`;
        h += `<div class="srp-kpi${zero ? " is-alert" : ""}"><div class="k">Nothing achieved yet</div><div class="v">${n(zero)}</div><div class="s">Students with expected points but none achieved</div></div>`;
        h += `<div class="srp-kpi"><div class="k">No term records</div><div class="v">${n(norec)}</div><div class="s">Students with no attendance semester, so nothing expected</div></div></div>`;

        const bmax = Math.max(1, ...bands);
        const terms = Object.keys(T).map(Number).sort((x, y) => y - x);
        h += `<div class="srp-two"><section class="srp-sec"><div class="srp-sechead"><h2>By attendance semester</h2><span class="srp-hint">Orange = below the overall ${pc(A, E)}</span></div><div><div class="srp-tlh"><span>Semester</span><span class="r">Students</span><span></span><span class="r">Achieved</span><span class="r">Share</span></div>`;
        h += terms.length ? terms.map((k) => line(D.terms[k][1], T[k].s, T[k].a, T[k].e, avg)).join("") : '<p class="srp-hint" style="padding:8px">No semesters.</p>';
        h += "</div></section>";
        h += `<section class="srp-sec"><div class="srp-sechead"><h2>Students by share achieved</h2><span class="srp-hint">Achieved ÷ expected, per student</span></div><div><div class="srp-hist" role="img" aria-label="${esc(BANDS.map((b, k) => `${b}: ${bands[k]} students`).join(", "))}">`;
        h += bands.map((c, k) => `<div${k === 5 ? ' class="is-top"' : ""}><b>${c}</b><i style="height:${c ? Math.max(2, (100 * c) / bmax) : 0}%${k === 0 ? ";background:var(--srp-orange)" : ""}"></i></div>`).join("");
        h += `</div><div class="srp-hx">${BANDS.map((b) => `<span>${b}</span>`).join("")}</div></div></section></div>`;

        const courses = Object.keys(C).map(Number).sort((x, y) => C[x].a / C[x].e - C[y].a / C[y].e || D.courses[x].localeCompare(D.courses[y]));
        h += `<section class="srp-sec"><div class="srp-sechead"><h2>By course</h2><span class="srp-hint">Lowest share first</span></div><div><div class="srp-tlh"><span>Course</span><span class="r">Students</span><span></span><span class="r">Achieved</span><span class="r">Share</span></div>`;
        h += courses.length ? courses.map((k) => line(D.courses[k] || "No course", C[k].s, C[k].a, C[k].e, avg, D.courses[k])).join("") : '<p class="srp-hint" style="padding:8px">No courses with expected points.</p>';
        h += "</div></section>";

        h += '<section class="srp-sec"><div class="srp-sechead"><h2>By student</h2><span class="srp-legend"><span><i class="srp-sw" style="background:#fbe3d0"></i>Nothing achieved</span><span><i class="srp-sw" style="background:#dcefec"></i>Under half</span><span><i class="srp-sw" style="background:#9fd0c8"></i>Half or more</span><span><i class="srp-sw" style="background:var(--srp-teal)"></i>All achieved</span>';
        if (inactive) h += '<span><i class="srp-sw is-inactive"></i>Term status No</span>';
        h += "</span></div>";
        h += `<div class="srp-tools"><label class="srp-field"><span>Find a student</span><input type="search" data-srp-q placeholder="Name or student ID" value="${esc(S.q)}"></label>`;
        h += '<div class="srp-field is-wide"><span id="spr-vl">Show</span><div class="srp-seg" role="group" aria-labelledby="spr-vl" data-srp-views></div></div>';
        h += '<label class="srp-field" style="flex-basis:220px"><span>Sort by</span><select class="srp-ctl" data-srp-sort><option value="reg">Student ID</option><option value="name">Name</option><option value="low">Lowest share first</option><option value="high">Highest share first</option></select></label></div><div data-srp-tbl></div></section>';

        const P = D.points || {};
        const g = P.grades || {};
        const notes = [
            `Share = achieved ÷ expected points, as in the old export. Each attendance semester expects ${P.attendance ?? 4} attendance points plus ${P.academic ?? 3} for each Pass, Merit or Distinction result; achieved = the attendance band's points (0–${P.attendance ?? 4}) plus ${g.P ?? 1}, ${g.M ?? 2} or ${g.D ?? 3} per Pass, Merit or Distinction.`,
            "Results count when published, on a class the student is assigned to; retakes count again.",
        ];
        if (new URLSearchParams(PARAMS).has("attendance[]")) notes.push("Only the chosen attendance semesters are counted.");
        if (inactive) notes.push(`${plural(inactive, "semester")} ${inactive === 1 ? "has" : "have"} term status No (dashed outline): the student was made inactive in that semester's classes.`);
        if (norec) notes.push(`${plural(norec, "student")} ${norec === 1 ? "has" : "have"} no attendance semester (no classes assigned), so nothing is expected of them.`);
        h += `<ul class="srp-notes">${notes.map((x) => `<li>${esc(x)}</li>`).join("")}</ul></div>`;

        out.innerHTML = h;
        out.querySelector("[data-srp-sort]").value = S.sort;
        draw();
    }

    function draw() {
        const q = S.q.trim().toLowerCase();
        out.querySelector("[data-srp-views]").innerHTML = [
            ["all", "All"],
            ["zero", "Nothing achieved"],
            ["half", "Under half"],
            ["full", "At 100%"],
        ]
            .map((v) => `<button type="button" data-view="${v[0]}" aria-pressed="${S.view === v[0]}">${v[1]}<small>${n(R.filter(TESTS[v[0]]).length)}</small></button>`)
            .join("");
        const list = R.filter((x) => TESTS[S.view](x) && (!q || String(x.r[1]).toLowerCase().includes(q) || String(x.r[2]).toLowerCase().includes(q)));
        const byReg = (a, b) => String(a.r[1]).localeCompare(String(b.r[1]), "en", { numeric: true });
        const bare = (s) => String(s).replace(/^(mr|mrs|miss|ms|dr)\.?\s+/i, "");
        if (S.sort === "reg") list.sort(byReg);
        if (S.sort === "name") list.sort((a, b) => bare(a.r[2]).localeCompare(bare(b.r[2]), "en", { sensitivity: "base" }) || byReg(a, b));
        if (S.sort === "low") list.sort((a, b) => share(a) - share(b) || byReg(a, b));
        if (S.sort === "high") list.sort((a, b) => share(b) - share(a) || byReg(a, b));
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);

        const used = {};
        R.forEach((x) => x.r[5].forEach((c) => (used[c[0]] = 1)));
        const cols = Object.keys(used).map(Number).sort((a, b) => a - b);
        let h = `<div class="srp-scroll"><table class="srp-table srp-mx"><thead><tr><th scope="col">Student</th>${cols
            .map((k, i) => `<th scope="col" class="c t${i % 8}" title="${esc(D.terms[k][1])}">${esc(tshort(D.terms[k][1]))}</th>`)
            .join("")}<th scope="col" class="tot">Overall</th></tr></thead><tbody>`;
        if (!part.length) h += `<tr><td colspan="${cols.length + 2}" class="srp-muted" style="padding:20px;text-align:center">No students match.</td></tr>`;
        part.forEach((x) => {
            const r = x.r;
            const status = D.statuses[r[4]] || "";
            const course = D.courses[r[3]] || "";
            const by = {};
            r[5].forEach((c) => (by[c[0]] = c));
            h += `<tr class="stu2"><td class="srp-who2"><a class="reg" href="${esc(URLS.student.replace("__ID__", r[0]))}" target="_blank" rel="noopener" title="Open ${esc(r[2])}'s record">${esc(r[1] || "–")}</a><small title="${esc(r[2])}">${esc(r[2])}</small><small title="${esc(course)}">${esc(short(course))}</small><small class="st${OFF.test(status) ? " is-off" : ""}" title="Student status">${esc(status || "No status")}</small></td>`;
            h += cols
                .map((k, i) => {
                    const c = by[k];
                    const td = `<td class="c t${i % 8}">`;
                    if (!c) return `${td}<span class="srp-cell none" aria-label="No record">·</span></td>`;
                    const p = c[1] ? c[2] / c[1] : 0;
                    const cls = !c[2] ? "z" : p >= 1 ? "f" : p >= 0.5 ? "h" : "l";
                    const title = `${D.terms[k][1]}: ${num(c[2])} achieved of ${num(c[1])} expected${c[3] ? " · term status No (made inactive)" : ""}`;
                    return `${td}<span class="srp-cell ${cls}${c[3] ? " is-inactive" : ""}" title="${esc(title)}">${num(c[2])}/${num(c[1])}</span></td>`;
                })
                .join("");
            h += `<td class="tot">${
                x.e
                    ? `<div class="srp-sharew"><span class="srp-frac"><b>${num(x.a)}</b> of ${num(x.e)}</span>${meter(x.a, x.e, !x.a)}<b class="${x.a ? "" : "low"}">${pc(x.a, x.e)}</b></div>`
                    : '<span class="zero">–</span>'
            }</td></tr>`;
        });
        h += `</tbody></table></div><div class="srp-foot"><span>${list.length ? `Showing ${n(S.page * PER + 1)} to ${n(S.page * PER + part.length)} of ${n(list.length)}` : "0 students"}</span><span class="srp-pager"><button type="button" class="srp-btn" data-page="-1"${S.page ? "" : " disabled"}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="srp-btn" data-page="1"${S.page < pages - 1 ? "" : " disabled"}>Next</button></span></div>`;
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
