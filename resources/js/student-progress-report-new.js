/*
 * Reports › Progress Monitoring, new version (user's progress-monitoring-report design, 25 Nov 2026).
 * Search loads every found student with their modules and certificate once (GET .../data); the
 * KPIs show at once and "View progress" lists the students (as the design), with filters, sorting,
 * paging and a detail panel per student, all in the browser. The form, version switch and Old-tab
 * link are the shared report-filters.js.
 */
import { esc, n, pc, plural, OFF, filterForm } from "./report-filters";

(function () {
    "use strict";

    const root = document.querySelector("[data-spm]");
    if (!root) return;

    const OPT = JSON.parse(root.dataset.options || "{}");
    const URLS = { data: root.dataset.dataUrl, excel: root.dataset.excelUrl, student: root.dataset.studentUrl };
    const out = root.querySelector("[data-srp-out]");
    const PER = 15;
    const LEVEL = 120; // credits in a level (RQF / FHEQ)
    /* Certificate steps in the order they happen in the records (the award date comes first in most). */
    const STEPS = ["Awarded", "Claimed", "Received", "Released"];
    const MOD_STATUS = { C: "Core", S: "Specialist", O: "Optional" };
    const CHEV = '<svg class="srp-chev" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';

    let D = null;
    let R = [];
    let PARAMS = "";
    let CRIT = "";
    let LISTED = false;
    let S = {};
    let request = 0;

    const filters = filterForm({
        root,
        form: root.querySelector("[data-srp-form]"),
        options: OPT,
        fields: ["intake", "attend", "course", "group", "evening", "status", "cert"],
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
            R = D.rows.map(summarise);
            LISTED = false;
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

    /* The old report's counts: a module with results passes on Pass, Merit or Distinction, else fails; awaiting = no result yet. */
    function summarise(r) {
        const x = { r, pass: 0, fail: 0, wait: 0, credits: 0, lv: { 4: [0, 0], 5: [0, 0], 6: [0, 0] }, stage: 0 };
        r[7].forEach((m) => {
            if (!m[8]) x.wait++;
            else if (!m[6]) x.fail++;
            else {
                x.pass++;
                x.credits += m[3];
                if (x.lv[m[2]]) {
                    x.lv[m[2]][0] += m[3];
                    x.lv[m[2]][1]++;
                }
            }
        });
        const c = r[8];
        if (c) x.stage = c[6] ? 4 : c[4] ? 3 : c[1] ? 2 : c[0] ? 1 : 0;
        x.claimed = !!(c && c[1]);
        x.released = !!(c && c[6]);
        return x;
    }

    /* ---------- results ---------- */
    const short = (c) => String(c).replace(/^HND in /i, "").replace(/ \(.*?\)/g, "");
    const meter = (a, e) => `<div class="srp-meter"><i style="width:${e ? Math.min(100, (100 * a) / e) : 0}%"></i></div>`;
    const gname = (g) => String((D.grades || {})[g] || g).split("/")[0];
    const stack = (p, f, w, label) => {
        const t = p + f + w;
        return `<div class="srp-stack" role="img" aria-label="${esc(label)}">${t ? `<i style="width:${(100 * p) / t}%;background:var(--srp-teal)"></i><i style="width:${(100 * f) / t}%;background:var(--srp-orange)"></i>` : ""}</div>`;
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
        let P = 0, Fl = 0, failing = 0, claimed = 0, released = 0, cr = 0;
        R.forEach((x) => {
            P += x.pass;
            Fl += x.fail;
            cr += x.credits;
            if (x.fail) failing++;
            if (x.claimed) claimed++;
            if (x.claimed && x.released) released++;
        });
        const excel = `${URLS.excel}?${PARAMS}&criteria=${encodeURIComponent(CRIT)}`;
        let h = `<div class="srp-panel srp-res"><div class="srp-top"><div><div class="srp-found"><b>${n(R.length)}</b> student${R.length === 1 ? "" : "s"} found</div><div class="srp-crit">${esc(CRIT)}</div></div><div class="srp-btns">`;
        if (!LISTED) h += '<button type="button" class="srp-btn srp-btn--primary" data-list>View progress</button>';
        h += `<a class="srp-btn" href="${esc(excel)}" download data-srp-excel><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v12M7 10l5 5 5-5M5 21h14"/></svg>Export Excel</a></div></div>`;
        h += `<div class="srp-kpis"><div class="srp-kpi is-good"><div class="k">Modules passed</div><div class="v">${pc(P, P + Fl)}</div><div class="s">${n(P)} passed of ${n(P + Fl)} with a result</div></div>`;
        h += `<div class="srp-kpi${failing ? " is-alert" : ""}"><div class="k">Students with a failed module</div><div class="v">${n(failing)}</div><div class="s">${plural(Fl, "failed module")} · ${pc(failing, R.length)} of students</div></div>`;
        h += `<div class="srp-kpi"><div class="k">Average credits achieved</div><div class="v">${n(Math.round(cr / R.length))}</div><div class="s">Per student · each level is ${LEVEL} credits</div></div>`;
        h += `<div class="srp-kpi"><div class="k">Certificates claimed</div><div class="v">${n(claimed)}</div><div class="s">${n(released)} released · ${n(claimed - released)} in progress</div></div></div>`;
        if (!LISTED) {
            h += `<div class="srp-cta"><span>The student list is not loaded yet.</span><button type="button" class="srp-btn srp-btn--primary" data-list>View progress for ${plural(R.length, "student")}</button></div>`;
        } else {
            h += '<section class="srp-sec"><div class="srp-sechead"><h2>Student progress</h2><span class="srp-legend"><span><i class="srp-sw" style="background:var(--srp-teal)"></i>Passed</span><span><i class="srp-sw" style="background:var(--srp-orange)"></i>Failed</span><span><i class="srp-sw" style="background:var(--srp-track)"></i>No result yet</span></span></div>';
            h += `<div class="srp-tools"><label class="srp-field"><span>Find a student</span><input type="search" data-srp-q placeholder="Name or student ID" value="${esc(S.q)}"></label>`;
            h += '<div class="srp-field is-wide"><span id="spm-vl">Show</span><div class="srp-seg" role="group" aria-labelledby="spm-vl" data-srp-views></div></div>';
            h += '<label class="srp-field" style="flex-basis:230px"><span>Sort by</span><select class="srp-ctl" data-srp-sort><option value="reg">Student ID</option><option value="fail">Most failed modules first</option><option value="credit">Fewest credits first</option></select></label></div><div data-srp-tbl></div></section>';
            const notes = [
                "Select a student to see their modules by term and the certificate steps.",
                "Modules come from published results, by term: Pass, Merit or Distinction passes the module, any other result counts as failed, as in the old report.",
                "No result yet: modules the student is assigned to with no published result (Awaiting while the semester runs, No result after it ends). Credit-less tutorials are left out; the old report and Export Excel leave all of these out.",
                `Credit meters are out of ${LEVEL} credits per level.`,
                "Certificate steps follow the award record: awarded, claimed, received, released.",
            ];
            if (new URLSearchParams(PARAMS).has("attendance[]")) notes.push("Only the chosen attendance semesters are counted.");
            h += `<ul class="srp-notes">${notes.map((x) => `<li>${esc(x)}</li>`).join("")}</ul>`;
        }
        out.innerHTML = h + "</div>";
        if (LISTED) {
            out.querySelector("[data-srp-sort]").value = S.sort;
            draw();
        }
    }

    const TESTS = { all: () => true, fail: (x) => x.fail > 0, cert: (x) => x.claimed };
    function draw() {
        const q = S.q.trim().toLowerCase();
        out.querySelector("[data-srp-views]").innerHTML = [
            ["all", "All"],
            ["fail", "With a failed module"],
            ["cert", "Certificate claimed"],
        ]
            .map((v) => `<button type="button" data-view="${v[0]}" aria-pressed="${S.view === v[0]}">${v[1]}<small>${n(R.filter(TESTS[v[0]]).length)}</small></button>`)
            .join("");
        const list = R.filter((x) => TESTS[S.view](x) && (!q || String(x.r[1]).toLowerCase().includes(q) || String(x.r[2]).toLowerCase().includes(q)));
        const byReg = (a, b) => String(a.r[1]).localeCompare(String(b.r[1]), "en", { numeric: true });
        if (S.sort === "reg") list.sort(byReg);
        if (S.sort === "fail") list.sort((a, b) => b.fail - a.fail || byReg(a, b));
        if (S.sort === "credit") list.sort((a, b) => a.credits - b.credits || byReg(a, b));
        const pages = Math.max(1, Math.ceil(list.length / PER));
        S.page = Math.min(Math.max(0, S.page), pages - 1);
        const part = list.slice(S.page * PER, S.page * PER + PER);

        let h = '<div class="srp-scroll"><table class="srp-table srp-pg"><thead><tr><th scope="col">Student</th><th scope="col">Modules</th><th scope="col">Credits achieved</th><th scope="col">Certificate</th></tr></thead><tbody>';
        if (!part.length) h += '<tr><td colspan="4" class="srp-muted" style="padding:20px;text-align:center">No students match.</td></tr>';
        part.forEach((x, k) => {
            const r = x.r;
            const id = r[0];
            const op = !!S.open[id];
            const status = D.statuses[r[3]] || "";
            const course = D.courses[r[4]] || "";
            const group = D.groups[r[6]] || "";
            const tot = x.pass + x.fail + x.wait;
            h += `<tr class="s${k % 2 ? "" : " alt"}"><td><button type="button" class="srp-who3" data-open="${id}" aria-expanded="${op}">${CHEV}<span><b>${esc(r[1] || "–")}</b><small>${esc(r[2])}</small><small title="${esc(course)}">${esc(short(course))}${group ? " · " + esc(group) : ""}</small><small>${esc(D.intakes[r[5]] || "")} intake</small></span></button><span class="srp-st-label${OFF.test(status) ? " is-off" : ""}">${esc(status || "No status")}</span></td>`;
            h += `<td class="srp-mod">${stack(x.pass, x.fail, x.wait, `${x.pass} passed, ${x.fail} failed, ${x.wait} with no result yet`)}<div class="t"><b>${x.pass}</b> passed${x.fail ? ` · <b class="low">${x.fail} failed</b>` : ""}${x.wait ? ` · ${x.wait} no result yet` : ""} · ${plural(tot, "module")}</div></td>`;
            const lv = [4, 5, 6].filter((l) => l !== 6 || x.lv[6][1]);
            h += `<td><div class="srp-cr"><span class="big">${n(x.credits)}</span><div class="srp-lv">${lv
                .map((l) => `<span>L${l}</span>${meter(x.lv[l][0], LEVEL)}<span><b>${n(x.lv[l][0])}</b> · ${plural(x.lv[l][1], "unit")}</span>`)
                .join("")}</div></div></td>`;
            const c = r[8];
            const when = c ? [c[0], c[2], c[5], c[7]][x.stage - 1] : "";
            h += `<td><span class="srp-pill c${x.stage}">${x.stage ? STEPS[x.stage - 1] : "Not claimed"}</span>${when ? `<div class="pd">${esc(when)}</div>` : ""}</td></tr>`;
            if (op) h += `<tr class="d"><td colspan="4">${detail(x)}</td></tr>`;
        });
        h += `</tbody></table></div><div class="srp-foot"><span>${list.length ? `Showing ${n(S.page * PER + 1)} to ${n(S.page * PER + part.length)} of ${n(list.length)}` : "0 students"}</span><span class="srp-pager"><button type="button" class="srp-btn" data-page="-1"${S.page ? "" : " disabled"}>Previous</button><span>Page ${S.page + 1} of ${pages}</span><button type="button" class="srp-btn" data-page="1"${S.page < pages - 1 ? "" : " disabled"}>Next</button></span></div>`;
        out.querySelector("[data-srp-tbl]").innerHTML = h;
    }

    function detail(x) {
        const r = x.r;
        const c = r[8];
        let d = '<div class="srp-dwrap">';
        // Certificate steps
        d += `<div class="srp-cline${c && x.stage ? "" : " is-off"}"><span class="ct">Certificate</span>`;
        if (c && x.stage) {
            const done = [!!c[0], c[1], c[4], c[6]];
            const dates = [c[0], c[2], c[5], c[7]];
            d += `<ol class="srp-steps">${STEPS.map((s, i) => {
                const on = done[i];
                const sub = on ? (dates[i] || "Date not recorded") + (i === 1 && c[3] ? ` · by ${c[3]}` : "") : "Not yet";
                return `<li class="${on ? "on" : ""}${x.stage === i + 1 ? " cur" : ""}"><i>${on ? "✓" : i + 1}</i><span><b>${s}</b><small>${esc(sub)}</small></span></li>`;
            }).join("")}</ol>`;
            if (c[8]) d += `<span class="srp-overall">Overall result <b>${esc(c[8])}</b></span>`;
        } else {
            d += `<span class="srp-muted">${c ? "Not claimed yet" : "No award record yet"}</span>`;
        }
        d += `<a class="srp-open" href="${esc(URLS.student.replace("__ID__", r[0]))}" target="_blank" rel="noopener">Open student record</a></div>`;

        // Terms in date order
        const by = {};
        r[7].forEach((m) => (by[m[0]] = by[m[0]] || []).push(m));
        const terms = Object.keys(by).map(Number).sort((a, b) => a - b);
        if (!terms.length) return d + '<p class="srp-hint">No modules yet.</p></div>';
        d += '<div class="srp-terms">';
        terms.forEach((t) => {
            const ms = by[t];
            let p = 0, f = 0, cr = 0;
            ms.forEach((m) => {
                if (!m[8]) return;
                if (m[6]) {
                    p++;
                    cr += m[3];
                } else f++;
            });
            const ended = !!D.terms[t][2];
            d += `<div class="srp-tcard"><div class="th"><b>${esc(D.terms[t][1])}</b><small>${p + f ? `${p} of ${ms.length} passed · ${n(cr)} credits` : ended ? "No results" : "Awaiting results"}</small></div>${stack(p, f, ms.length - p - f, `${p} passed, ${f} failed`)}<ul>`;
            ms.forEach((m) => {
                const k = !m[8] ? "w" : !m[6] ? "f" : m[6] === "P" ? "p" : m[6] === "M" ? "m" : "dd";
                const label = !m[8] ? (ended ? "No result" : "Awaiting") : m[6] ? gname(m[6]) : m[7] ? gname(m[7]) : "No pass";
                const bits = [m[2] ? `Level ${m[2]}` : "", `${n(m[3])} credits`, D.staff[m[5]] || "", m[8] > 1 ? `${m[8]} attempts` : "", m[4] !== r[6] && D.groups[m[4]] ? `Group ${D.groups[m[4]]}` : "", MOD_STATUS[m[9]] || m[9] || ""].filter(Boolean);
                d += `<li class="${k}"><span class="srp-badge ${k}" title="${esc(label)}">${esc(label)}</span><span class="mn"><b>${esc(D.units[m[1]])}</b><small>${esc(bits.join(" · "))}</small></span></li>`;
            });
            d += "</ul></div>";
        });
        return d + "</div></div>";
    }

    /* ---------- events (results) ---------- */
    out.addEventListener("click", (e) => {
        let b;
        if (e.target.closest("[data-list]")) {
            LISTED = true;
            render();
            const q = out.querySelector("[data-srp-q]");
            if (q) q.focus();
        } else if ((b = e.target.closest("[data-view]"))) {
            S.view = b.dataset.view;
            S.page = 0;
            draw();
            out.querySelector(`[data-view="${S.view}"]`).focus();
        } else if ((b = e.target.closest("[data-open]"))) {
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
