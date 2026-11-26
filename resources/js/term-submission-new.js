import TomSelect from "tom-select";
import { createIcons, icons } from "lucide";

/*
 * Reports › Term Performance › Submission Performance, "New version" (user's
 * submission-performance-report design, 25 Nov 2026). Generate asks once
 * (SubmissionPerformanceController::generate); Download PDF and Export Excel show after it.
 * As the other new reports, the old version follows: the same term, and Generate runs both.
 */
(function () {
    const root = document.querySelector("[data-tsp]");
    if (!root) return;

    const $ = (sel, el = root) => el.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const n = (v) => Number(v || 0).toLocaleString("en-GB");
    const pc = (a, b, dp = 1) => (b ? ((100 * a) / b).toFixed(dp) + "%" : "–");
    const pts = (a, b) => {
        const d = a - b;
        return (d >= 0 ? "+" : "−") + Math.abs(d).toFixed(1) + " pts";
    };
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });
    // Outcomes in the design's order: key, name, colour, dark text on the light ones.
    const CATS = [
        ["D", "Distinction", "var(--tsp-d)", false],
        ["M", "Merit", "var(--tsp-m)", false],
        ["P", "Pass", "var(--tsp-p)", true],
        ["R", "Referred", "var(--tsp-r)", true],
        ["C", "Plagiarised", "var(--tsp-c)", false],
        ["A", "Not submitted", "var(--tsp-a)", true],
    ];
    const pass = (o) => o.P + o.M + o.D;
    const gap = (o) => Math.max(0, o.expected - CATS.reduce((t, c) => t + o[c[0]], 0));

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

    /* ── Form ── */
    const form = $("[data-tsp-form]");
    const select = $("[data-tsp-term]");
    const error = $("[data-tsp-error]");
    const button = $("[data-tsp-generate]");
    const pdf = $("[data-tsp-pdf]");
    const excel = $("[data-tsp-excel]");
    const result = $("[data-tsp-result]");
    const picker = new TomSelect(select, {
        plugins: { dropdown_input: {} },
        placeholder: "Search here...",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown tar-ts-dropdown",
        maxOptions: null,
        create: false,
    });

    let data = null;
    let seq = 0;
    const busy = (on) => {
        button.classList.toggle("is-busy", on);
        button.disabled = on;
    };
    const downloads = () => {
        const ready = !!data && data.courses.length > 0;
        pdf.hidden = excel.hidden = !ready;
        if (ready) {
            pdf.setAttribute("href", route(root.dataset.pdf, { term: data.term.id }));
            excel.setAttribute("href", route(root.dataset.excel, { term: data.term.id }));
        }
    };

    select.addEventListener("change", () => {
        error.textContent = "";
        result.innerHTML = "";
        data = null;
        downloads();
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const term = picker.getValue();
        error.textContent = "";
        if (!term) {
            error.textContent = "Please select an attendance term.";
            return;
        }
        const mine = ++seq;
        busy(true);
        result.innerHTML = "";
        data = null;
        downloads();
        axios
            .get(route(root.dataset.generate, { term }))
            .then(({ data: d }) => {
                if (mine !== seq) return;
                data = d;
                render();
                downloads();
            })
            .catch((err) => {
                if (mine !== seq) return;
                error.textContent = (err.response && err.response.data && err.response.data.message) || "The report could not be generated. Please try again.";
            })
            .finally(() => mine === seq && busy(false));
    });

    /* ── The report ── */
    function stack(o, thin) {
        const g = gap(o);
        const exp = o.expected || 1;
        const label = CATS.map((c) => `${n(o[c[0]])} ${c[1].toLowerCase()}`).join(", ") + (g ? `, ${n(g)} not accounted for` : "");
        let h = `<div class="tsp-stack${thin ? " thin" : ""}" role="img" aria-label="${label}">`;
        CATS.forEach(([k, , colour, dark]) => {
            const v = o[k];
            if (v) h += `<i style="flex:${v} 1 0;background:${colour}${dark ? ";color:#16242b" : ""}">${!thin && v / exp > 0.045 ? n(v) : ""}</i>`;
        });
        if (g) h += `<i class="tsp-gap" style="flex:${g} 1 0"></i>`;
        return h + "</div>";
    }

    const was = (now, then, label) => `<span class="tsp-was">${label} ${then.toFixed(1)}% · ${pts(now, then)}</span>`;
    const gradeList = (other) =>
        Object.entries(other || {})
            .sort((a, b) => b[1] - a[1])
            .map(([g, c]) => `${n(c)} ${data.grades[g] ? data.grades[g] : "with no grade name"}`)
            .join(", ");

    // A count of the Full figures table that opens its students (user, 25 Nov 2026). Zero stays text.
    const link = (text, c, which, key, v, merged) =>
        v > 0 ? `<button type="button" class="tsp-num" data-tsp-list="${key}" data-course="${c.main ? "all" : c.id}" data-which="${which}"${merged ? " data-merged" : ""}>${text}</button>` : text;

    function matrix() {
        const T = data.totals;
        const cols = [{ name: "All courses", students: T.students, first: T.first, final: T.final, main: true }].concat(data.courses);
        const sw = (c) => `<span class="tsp-sw" style="background:${c}"></span>`;
        const pair = (fn, better, key) =>
            cols
                .map((c) => {
                    const f = fn(c.first, c);
                    const a = fn(c.final, c);
                    let cls = "";
                    if (better && a.v !== f.v) cls = (a.v - f.v) * better > 0 ? " up" : " down";
                    const main = c.main ? " mainc" : "";
                    const ft = key ? link(f.t, c, "first", key, f.v) : f.t;
                    const at = key ? link(a.t, c, "final", key, a.v) : a.t;
                    return `<td class="f${main}">${ft}${f.s ? "<small>" + f.s + "</small>" : ""}</td><td class="l${cls}${main}">${at}${a.s ? "<small>" + a.s + "</small>" : ""}</td>`;
                })
                .join("");
        // One figure for first and final (students; expected when it does not change).
        const both = (fn, key) =>
            cols
                .map((c) => {
                    const v = fn(c);
                    return `<td colspan="2" class="span${c.main ? " mainc" : ""}">${link(n(v), c, "final", key, v, true)}</td>`;
                })
                .join("");
        const count = (k) => (o) => ({ v: o[k], t: n(o[k]), s: pc(o[k], o.sub) });
        const band = (t) => `<tr class="band"><th scope="rowgroup" colspan="${1 + cols.length * 2}">${t}</th></tr>`;
        const row = (label, cells, cls) => `<tr${cls ? ` class="${cls}"` : ""}><th scope="row">${label}</th>${cells}</tr>`;
        const sameExpected = cols.every((c) => c.first.expected === c.final.expected);

        let h = `<table class="tsp-mx"><thead><tr><th rowspan="2" scope="col"></th>${cols.map((c) => `<th colspan="2" scope="colgroup" class="cg${c.main ? " mainc" : ""}">${esc(c.name)}</th>`).join("")}</tr><tr>`;
        h += cols.map((c) => `<th scope="col" class="f${c.main ? " mainc" : ""}">First</th><th scope="col" class="l${c.main ? " mainc" : ""}">Final</th>`).join("") + "</tr></thead><tbody>";
        h += band("Assignments") + row("Students", both((c) => c.students, "students"));
        h += sameExpected ? row("Expected", both((c) => c.final.expected, "expected")) : row("Expected", pair((o) => ({ v: o.expected, t: n(o.expected) }), 0, "expected"));
        h += row("Submitted", pair((o) => ({ v: o.sub, t: n(o.sub) }), 1, "sub"));
        h += row("Submission rate", pair((o) => ({ v: o.expected ? o.sub / o.expected : 0, t: pc(o.sub, o.expected, 2) }), 1), "rate");
        h += band("Passed") + row(sw("var(--tsp-d)") + "Distinction", pair(count("D"), 1, "D")) + row(sw("var(--tsp-m)") + "Merit", pair(count("M"), 1, "M")) + row(sw("var(--tsp-p)") + "Pass", pair(count("P"), 1, "P"));
        h += row("Total passed", pair((o) => ({ v: pass(o), t: n(pass(o)) }), 1, "passed"));
        h += row("Pass rate", pair((o) => ({ v: o.sub ? pass(o) / o.sub : 0, t: pc(pass(o), o.sub, 2) }), 1), "rate");
        h += band("Not passed") + row(sw("var(--tsp-r)") + "Referred", pair(count("R"), -1, "R")) + row(sw("var(--tsp-c)") + "Plagiarised", pair(count("C"), -1, "C"));
        h += row(sw("var(--tsp-a)") + "Not submitted", pair((o) => ({ v: o.A, t: n(o.A), s: pc(o.A, o.expected) }), -1, "A"));
        if (gap(T.first) || gap(T.final)) h += row("Not accounted for", pair((o) => ({ v: gap(o), t: n(gap(o)) }), -1, "other"));
        return h + '</tbody></table><div class="tsp-hint" style="padding-top:8px">Small figures are the share of submitted work (for Not submitted, the share of expected). Final figures in green improved on the first submission; orange got worse. Select a count to see its students.</div>';
    }

    function render() {
        const T = data.totals;
        const F = T.first;
        const A = T.final;
        if (!data.courses.length || !F.expected) {
            result.innerHTML = `<div class="tsp-report"><div><h1>${esc(data.term.name)} term</h1></div><div class="tsp-empty">No results are recorded for this term yet.</div></div>`;
            return;
        }
        const subNow = (100 * A.sub) / A.expected;
        const subThen = (100 * F.sub) / F.expected;
        const passNow = A.sub ? (100 * pass(A)) / A.sub : 0;
        const passThen = F.sub ? (100 * pass(F)) / F.sub : 0;
        const hasGap = gap(F) || gap(A);

        let h = `<div><h1>${esc(data.term.name)} term</h1><div class="tsp-lede">${n(T.students)} students · ${n(A.expected)} assignments expected</div></div>`;
        h += '<div class="tsp-hero">';
        h += `<div class="tsp-kpi"><div class="k">Submission rate</div><div class="line"><div class="v">${subNow.toFixed(1)}%</div>${was(subNow, subThen, "First submission")}</div><div class="s">${n(A.sub)} of ${n(A.expected)} expected assignments handed in</div></div>`;
        h += `<div class="tsp-kpi"><div class="k">Pass rate</div><div class="line"><div class="v">${passNow.toFixed(1)}%</div>${was(passNow, passThen, "First submission")}</div><div class="s">${n(pass(A))} of ${n(A.sub)} submitted assignments passed</div></div></div>`;

        // First submission to final result
        h += `<section class="tsp-sec"><div class="tsp-head"><h2>From first submission to final result</h2><span class="tsp-hint">Each bar is all ${n(A.expected)} expected assignments</span></div><div class="tsp-two"><div class="tsp-sec"><div class="tsp-pair">`;
        h += `<div class="lab">First submission<small>Before any resubmission</small></div>${stack(F)}<div class="lab">Final result<small>After resubmission</small></div>${stack(A)}</div>`;
        h += '<div class="tsp-key">' + CATS.map((c) => `<span><i class="tsp-sw" style="background:${c[2]}"></i>${c[1]}</span>`).join("") + (hasGap ? '<span><i class="tsp-sw tsp-gap"></i>Not accounted for</span>' : "") + "</div></div>";
        h += '<table class="tsp-table"><thead><tr><th scope="col">Outcome</th><th scope="col">First</th><th scope="col">Final</th><th scope="col">Change</th></tr></thead><tbody>';
        CATS.forEach(([k, label, colour]) => {
            const d = A[k] - F[k];
            const good = ["R", "C", "A"].includes(k) ? d < 0 : d > 0;
            h += `<tr><td><span class="tsp-sw" style="background:${colour}"></span>${label}</td><td>${n(F[k])}</td><td class="final">${n(A[k])}</td><td class="${d === 0 ? "same" : good ? "up" : "down"}">${d === 0 ? "No change" : (d > 0 ? "+" : "−") + n(Math.abs(d))}</td></tr>`;
        });
        if (hasGap) {
            const d = gap(A) - gap(F);
            h += `<tr><td><span class="tsp-sw tsp-gap"></span>Not accounted for</td><td>${n(gap(F))}</td><td class="final">${n(gap(A))}</td><td class="${d === 0 ? "same" : d < 0 ? "up" : "down"}">${d === 0 ? "No change" : (d > 0 ? "+" : "−") + n(Math.abs(d))}</td></tr>`;
        }
        h += "</tbody></table></div></section>";

        // By course
        h += '<section class="tsp-sec"><div class="tsp-head"><h2>By course</h2><span class="tsp-hint">Top bar = first submission · Bottom bar = final result</span></div><div class="tsp-courses">';
        data.courses.forEach((c) => {
            const f = c.first;
            const a = c.final;
            const md = a.M + a.D;
            h += `<div class="tsp-course"><div><div class="name">${esc(c.name)}</div><div class="meta">${n(c.students)} students · ${n(a.expected)} expected${a.expected < 30 ? " · small cohort" : ""}</div></div>`;
            h += `<div class="bars"><span>First</span>${stack(f, true)}<span>Final</span>${stack(a, true)}</div>`;
            h += `<div class="tsp-trio"><div><div class="k">Submitted</div><div class="v">${pc(a.sub, a.expected)}</div><div class="w">first ${pc(f.sub, f.expected)}</div></div>`;
            h += `<div><div class="k">Passed</div><div class="v">${pc(pass(a), a.sub)}</div><div class="w">first ${pc(pass(f), f.sub)}</div></div>`;
            h += `<div><div class="k">Higher grades</div><div class="v">${pc(md, a.sub)}</div><div class="w">${n(md)} Merit or Distinction</div></div></div></div>`;
        });
        h += "</div></section>";

        h += `<details open><summary>Full figures: first submission and final result</summary><div class="tsp-scroll">${matrix()}</div></details>`;

        h += '<ul class="tsp-notes"><li>Submission rate = submitted ÷ expected. Pass rate = Pass, Merit and Distinction ÷ submitted.</li>';
        if (A.M === F.M && A.D === F.D && A.P > F.P) h += "<li>Merit and Distinction counts are the same at first submission and after resubmission; every resubmitted assignment that passed did so at Pass grade.</li>";
        if (gap(A)) h += `<li>${n(gap(A))} expected assignments in the final figures have a grade outside these outcomes: ${esc(gradeList(A.other))}.</li>`;
        if (gap(F)) h += `<li>At first submission, ${n(gap(F))} had a grade outside these outcomes: ${esc(gradeList(F.other))}.</li>`;
        h += "</ul>";

        result.innerHTML = `<div class="tsp-report">${h}</div>`;
    }

    /* ── Students behind a count (SubmissionPerformanceController::students, 50 a page) ── */
    const FIGURE = { students: "Students", expected: "Expected assignments", sub: "Submitted", passed: "Total passed", D: "Distinction", M: "Merit", P: "Pass", R: "Referred", C: "Plagiarised", A: "Not submitted", other: "Not accounted for" };
    // Figures made of more than one grade show each student's grades.
    const MIXED = ["students", "expected", "sub", "passed", "other"];
    const DUE = { 2: ["No SLC payment due", "ok"], 3: ["SLC payment due", "warn"], 4: ["SLC payment overdue by more than 30 days", "bad"] };
    const modalEl = document.getElementById("tspStudentsModal");
    const modal = modalEl && window.tailwind ? tailwind.Modal.getOrCreateInstance(modalEl) : null;
    const mq = (sel) => modalEl.querySelector(sel);
    let list = null;
    let listSeq = 0;

    const icon = (name, cls) => `<i data-lucide="${name}" class="${cls}" aria-hidden="true"></i>`;
    // An SVG ignores a title attribute, so each mark is a titled span around its icon.
    const mark = (name, tone, title) => `<span class="tsp-mark${tone ? " tsp-mark--" + esc(tone) : ""}" title="${esc(title)}" role="img" aria-label="${esc(title)}">${icon(name, "")}</span>`;
    const marks = (r) => {
        let h = r.flags.map((f) => mark("flag", f.color, f.name)).join("");
        if (DUE[r.due]) h += mark("badge-pound-sterling", DUE[r.due][1], DUE[r.due][0]);
        h += r.full_time ? mark("sunset", "", "Evening and weekend") : mark("sun", "day", "Weekday");
        if (r.disability) h += mark("accessibility", "bad", "Disability recorded");
        return h;
    };
    const sortHead = (label, key, cls) => {
        const on = list.sort === key;
        const arrow = on ? (list.dir === "asc" ? "arrow-up" : "arrow-down") : "arrow-up-down";
        return `<th scope="col"${cls ? ` class="${cls}"` : ""} aria-sort="${on ? (list.dir === "asc" ? "ascending" : "descending") : "none"}"><button type="button" class="tsp-sort${on ? " is-on" : ""}" data-tsp-sort="${key}">${label}${icon(arrow, "tsp-sort__icon")}</button></th>`;
    };

    function drawList(d) {
        const one = list.key === "students" ? "student" : "assignment";
        let lede =
            list.key === "students"
                ? `${n(d.total)} students · ${n(d.assignments)} expected assignments`
                : `${n(d.assignments)} ${one}${d.assignments === 1 ? "" : "s"} by ${n(d.total)} student${d.total === 1 ? "" : "s"}`;
        if (list.key === "students" && list.figure > d.total) lede += ` (${n(list.figure - d.total)} counted on two courses)`;
        let h = `<p class="tsp-mlede">${lede}</p>`;
        if (!d.rows.length) {
            h += '<div class="tsp-mempty">No students to show.</div>';
        } else {
            h += `<div class="tsp-mscroll"><table class="tsp-mt"><thead><tr>${sortHead("Reg. no", "registration_no")}${sortHead("Student", "name")}<th scope="col"><span class="tsp-sr">Flags</span></th><th scope="col">Course</th><th scope="col">Status</th>${sortHead(list.key === "students" ? "Expected" : "Assignments", "count", "num")}</tr></thead><tbody>`;
            d.rows.forEach((r) => {
                const grades = MIXED.includes(list.key) && r.grades.length ? `<small>${r.grades.map((g) => `${esc(g.name)} ${n(g.count)}`).join(" · ")}</small>` : "";
                h += `<tr data-href="${esc(r.url)}"><td class="reg">${esc(r.registration_no)}</td>`;
                h += `<td><div class="who"><img src="${esc(r.photo_url)}" alt="" loading="lazy"><a href="${esc(r.url)}" target="_blank" rel="noopener">${esc(r.name)}</a></div></td>`;
                h += `<td><div class="marks">${marks(r)}</div></td>`;
                h += `<td><div class="course">${esc(r.course) || "–"}</div>${r.semester ? `<small>${esc(r.semester)}</small>` : ""}</td>`;
                h += `<td>${esc(r.status) || "–"}</td><td class="num"><strong>${n(r.count)}</strong>${grades}</td></tr>`;
            });
            h += "</tbody></table></div>";
        }
        mq("[data-tsp-m-body]").innerHTML = h;
        const from = d.total ? (d.page - 1) * d.per_page + 1 : 0;
        mq("[data-tsp-m-range]").textContent = d.total ? `Showing ${n(from)}–${n(from + d.rows.length - 1)} of ${n(d.total)}` : "";
        mq("[data-tsp-m-pager]").hidden = d.last_page < 2;
        mq("[data-tsp-m-page]").textContent = `Page ${n(d.page)} of ${n(d.last_page)}`;
        mq("[data-tsp-m-prev]").disabled = d.page <= 1;
        mq("[data-tsp-m-next]").disabled = d.page >= d.last_page;
        list.page = d.page;
        list.last = d.last_page;
        paintIcons();
    }

    function loadList() {
        const mine = ++listSeq;
        modalEl.classList.add("is-loading");
        if (!mq(".tsp-mt")) mq("[data-tsp-m-body]").innerHTML = '<div class="tsp-mempty">Loading students…</div>';
        axios
            .get(route(root.dataset.students, { term: list.term }), { params: { course: list.course, which: list.which, key: list.key, page: list.page, sort: list.sort, dir: list.dir } })
            .then(({ data: d }) => mine === listSeq && drawList(d))
            .catch(() => {
                if (mine !== listSeq) return;
                mq("[data-tsp-m-body]").innerHTML = '<div class="tsp-mempty tsp-mempty--error">The student list could not be loaded. Please try again.</div>';
                mq("[data-tsp-m-range]").textContent = "";
                mq("[data-tsp-m-pager]").hidden = true;
            })
            .finally(() => mine === listSeq && modalEl.classList.remove("is-loading"));
    }

    if (modal) {
        result.addEventListener("click", (e) => {
            const b = e.target.closest("[data-tsp-list]");
            if (!b || !data) return;
            const key = b.dataset.tspList;
            const course = b.dataset.course === "all" ? { name: "All courses" } : data.courses.find((c) => String(c.id) === b.dataset.course) || { name: "" };
            list = { term: data.term.id, course: b.dataset.course, which: b.dataset.which, key, page: 1, sort: "count", dir: "desc", figure: Number(b.textContent.replace(/[^0-9]/g, "")) };
            const side = "merged" in b.dataset ? "" : b.dataset.which === "first" ? " · first submission" : " · final result";
            mq("[data-tsp-m-title]").textContent = FIGURE[key] + side;
            mq("[data-tsp-m-sub]").textContent = `${course.name} · ${data.term.name} term`;
            mq("[data-tsp-m-body]").innerHTML = "";
            mq("[data-tsp-m-range]").textContent = "";
            mq("[data-tsp-m-pager]").hidden = true;
            modal.show();
            loadList();
        });
        modalEl.addEventListener("click", (e) => {
            if (!list) return;
            const sort = e.target.closest("[data-tsp-sort]");
            if (sort) {
                const key = sort.dataset.tspSort;
                list.dir = list.sort === key ? (list.dir === "asc" ? "desc" : "asc") : key === "count" ? "desc" : "asc";
                list.sort = key;
                list.page = 1;
                return loadList();
            }
            if (e.target.closest("[data-tsp-m-prev]") && list.page > 1) {
                list.page--;
                return loadList();
            }
            if (e.target.closest("[data-tsp-m-next]") && list.page < list.last) {
                list.page++;
                return loadList();
            }
            // As the old list, a row opens the student's profile in a new tab.
            const tr = e.target.closest("tr[data-href]");
            if (tr && !e.target.closest("a, button")) window.open(tr.dataset.href, "_blank", "noopener");
        });
        modalEl.addEventListener("hidden.tw.modal", () => {
            listSeq++;
            list = null;
            mq("[data-tsp-m-body]").innerHTML = "";
        });
    }

    /* ── New and old versions together ── */
    const oldSelect = document.querySelector("#sub_perf_term_id");
    const oldForm = document.querySelector("#submissionPerformanceReportForm");
    if (oldSelect && oldForm) {
        let mirroring = false;
        const oldPicker = () => oldSelect.tomselect;
        const copy = (from, to) => {
            const v = from.getValue() || "";
            if ((to.getValue() || "") === v) return; // also stops the two echoing each other
            if (v && !to.options[v] && from.options[v]) to.addOption({ value: v, text: from.options[v].text });
            to.setValue(v);
        };
        select.addEventListener("change", () => oldPicker() && copy(picker, oldPicker()));
        oldSelect.addEventListener("change", () => oldPicker() && copy(oldPicker(), picker));
        const fire = (target) => {
            mirroring = true;
            try {
                target.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
            } finally {
                mirroring = false;
            }
        };
        form.addEventListener("submit", () => {
            if (mirroring || !picker.getValue() || !oldPicker()) return;
            copy(picker, oldPicker());
            fire(oldForm);
        });
        oldForm.addEventListener("submit", () => {
            if (mirroring || !oldPicker() || !oldPicker().getValue()) return;
            copy(oldPicker(), picker);
            fire(form);
        });
    }

    paintIcons();
})();
