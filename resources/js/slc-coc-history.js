import { createIcons, icons } from "lucide";

/*
 * Reports › SLC Reports › SLC COC History, "New version" (user's slc-coc-history design, 25 Nov
 * 2026). As the other SLC reports: nothing shows until Search (dates start on the design's "All
 * dates"). Changes of circumstances are few (about 300 a year), so the first Search loads every one
 * (SlcCocHistoryController::data) and every filter, the dates included, works in the browser. Each
 * search is copied into the Old version's form. Export Excel (beside Search, once searched)
 * downloads the selection with the old export's columns.
 */
(function () {
    const root = document.querySelector("[data-sch]");
    if (!root) return;

    const $ = (sel) => root.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const n = (v) => Math.round(v).toLocaleString("en-GB");
    const MON = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const any = (o) => Object.keys(o).some((k) => o[k]);
    const on = (o) => Object.keys(o).filter((k) => o[k]);
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });
    const toDay = (s) => Math.round(Date.parse(s + "T00:00:00Z") / 864e5);
    const fromDay = (d) => new Date(d * 864e5).toISOString().slice(0, 10);
    const nice = (s) => {
        const p = s.split("-");
        return +p[2] + " " + MON[+p[1] - 1] + " " + p[0];
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

    // rows: [id, date, type, actioned 1/0, course id, intake id, student status id, student id]
    let D = null;
    let F = { from: "", to: "", type: {}, act: "all", course: "" };
    const T = { page: 0, per: 12, q: "" };
    let searched = false;
    let loading = false;
    let shortCourse = {};
    const OPT = { types: JSON.parse(root.dataset.types || "[]"), courses: JSON.parse(root.dataset.courses || "[]") };
    const colour = {};
    OPT.types.forEach(([name, c]) => (colour[name] = c));
    const TODAY = root.dataset.today;
    const today = () => (D ? D.today : TODAY);
    const PRESETS = [
        ["all", "All dates", () => ""],
        ["7", "Last 7 days", () => fromDay(toDay(today()) - 6)],
        ["30", "Last 30 days", () => fromDay(toDay(today()) - 29)],
        ["90", "Last 90 days", () => fromDay(toDay(today()) - 89)],
        ["365", "Last 12 months", () => fromDay(toDay(today()) - 364)],
    ];
    const waited = (d) => Math.max(0, toDay(today()) - toDay(d));
    const wait = (d) => {
        const w = waited(d);
        return w === 0 ? "Today" : w === 1 ? "1 day" : n(w) + " days";
    };

    const state = $("[data-sch-state]");
    const body = $("[data-sch-body]");

    function load() {
        loading = true;
        state.hidden = false;
        state.classList.remove("is-error");
        state.textContent = "Loading changes of circumstances…";
        root.classList.add("is-loading");
        axios
            .get(route(root.dataset.data))
            .then(({ data }) => {
                D = data;
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
                state.hidden = false;
                state.classList.add("is-error");
                state.textContent = (err.response && err.response.data && err.response.data.message) || "The changes of circumstances could not be loaded. Please try again.";
            })
            .finally(() => {
                loading = false;
                root.classList.remove("is-loading");
            });
    }

    const pass = (r, skipType) =>
        (!F.from || r[1] >= F.from) &&
        (!F.to || r[1] <= F.to) &&
        (skipType || !any(F.type) || F.type[r[2]]) &&
        (F.act === "all" || (F.act === "yes") === !!r[3]) &&
        (F.course === "" || String(r[4]) === F.course);
    const dot = (name) => `<i class="sah-dot" style="background:${colour[name] || "#4a5b6b"}"></i>`;
    const pill = (name) => `<span class="sah-tpill" style="--c:${colour[name] || "#4a5b6b"}">${esc(name || "No type")}</span>`;

    function drawFilters() {
        const count = {};
        if (D) D.rows.forEach((r) => (count[r[2]] = (count[r[2]] || 0) + 1));
        let h = `<div class="sah-f sah-f--date"><span id="schDl">Confirmation date</span><div class="sah-dates" role="group" aria-labelledby="schDl"><input type="date" id="schFrom" aria-label="From" value="${F.from}"><span class="sah-hint">to</span><input type="date" id="schTo" aria-label="To" value="${F.to}"></div><div class="sah-chips">`;
        h += PRESETS.map(([key, label, start]) => {
            const pressed = key === "all" ? !F.from && !F.to : F.to === today() && F.from === start();
            return `<button type="button" class="sah-chip sah-chip--quick" data-preset="${key}" aria-pressed="${pressed}">${label}</button>`;
        }).join("");
        h += "</div></div>";
        h += `<div class="sah-f sah-f--code"><span>Type of change</span><div class="sah-chips">${OPT.types
            .map(([name]) => `<button type="button" class="sah-chip" data-type="${esc(name)}" aria-pressed="${!!F.type[name]}">${dot(name)}${esc(name)}${searched ? ` <small>${n(count[name] || 0)}</small>` : ""}</button>`)
            .join("")}</div></div>`;
        h += `<div class="sah-f sah-f--act"><span id="schAl">Actioned</span><div class="sah-seg" role="group" aria-labelledby="schAl">${[
            ["all", "All"],
            ["no", "Not actioned"],
            ["yes", "Actioned"],
        ]
            .map(([key, label]) => `<button type="button" data-act="${key}" aria-pressed="${F.act === key}">${label}</button>`)
            .join("")}</div></div>`;
        const courses = OPT.courses.map(([id, name]) => [String(id), name]);
        if (D) Object.entries(D.courses).forEach(([id, name]) => courses.some((c) => c[0] === id) || courses.push([id, name]));
        h += `<div class="sah-f sah-f--course"><label for="schCourse">Course</label><select id="schCourse"><option value="">All courses</option>${courses.map(([id, name]) => `<option value="${id}"${F.course === id ? " selected" : ""}>${esc(name)}</option>`).join("")}</select></div>`;
        const active = (F.from || F.to ? 1 : 0) + any(F.type) + (F.act !== "all") + (F.course !== "");
        h += `<div class="sah-fbar"><span>${searched ? '<span class="sah-count" data-sch-fcount></span>' : '<span class="sah-hint">No type selected means all types.</span>'}</span><span class="sah-fbar__actions">`;
        if (active) h += `<button type="button" class="sah-linkbtn" data-reset>Clear ${active} filter${active > 1 ? "s" : ""}</button>`;
        h += `<button type="button" class="sah-btn sah-btn--primary" data-sch-search><i data-lucide="search"></i> Search</button>`;
        if (searched) h += `<a href="javascript:void(0);" class="sah-btn sah-btn--excel" data-sch-export aria-disabled="false"><i data-lucide="file-spreadsheet"></i> Export Excel</a>`;
        h += "</span></div>";
        $("[data-sch-filters]").innerHTML = h;
        paintIcons();
    }

    // The Old version's form follows the search: its date range is required, so "All dates" is
    // the earliest change to today; it has no course field and lists six types.
    function syncOld() {
        const dmy = (s) => s.split("-").reverse().join("-");
        const range = document.getElementById("date_range2");
        if (range) {
            const from = F.from || (D && D.first) || "";
            const to = F.to || today();
            range.value = from ? `${dmy(from)} - ${dmy(to)}` : "";
            const err = document.querySelector("#SLCcocReportForm .error-date_range");
            if (err) err.textContent = "";
        }
        const set = (id, values) => {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.tomselect) el.tomselect.setValue(values, true);
            else Array.from(el.options).forEach((o) => (o.selected = values.includes(o.value)));
        };
        set("coc_type", on(F.type));
        set("actioned", F.act === "yes" ? ["Yes"] : F.act === "no" ? ["No"] : []);
    }

    function draw() {
        const sel = D.rows.filter((r) => pass(r));
        const open = sel.filter((r) => !r[3]);
        const done = sel.length - open.length;
        const oldest = open.reduce((o, r) => (!o || r[1] < o[1] ? r : o), null);
        const k = (cls, a, b, c) => `<div class="sah-kpi ${cls}"><div class="k">${a}</div><div class="v">${b}</div><div class="s">${c}</div></div>`;
        $("[data-sch-kpis]").innerHTML =
            k("", "Changes reported", n(sel.length), F.from || F.to ? (F.from ? nice(F.from) : "Start") + " to " + (F.to ? nice(F.to) : "today") : "All dates") +
            k(open.length ? "alert" : "", "Not actioned", n(open.length), open.length ? "Still to be actioned" : "Nothing waiting") +
            k("", "Actioned", n(done), sel.length ? Math.round((100 * done) / sel.length) + "% of changes reported" : "None") +
            k("", "Longest waiting", oldest ? wait(oldest[1]) : "–", oldest ? `${esc(oldest[2] || "No type")}, reported ${nice(oldest[1])}` : "No change is waiting");
        root.querySelector("[data-sch-fcount]").textContent = `Showing ${n(sel.length)} of ${n(D.rows.length)} ${D.rows.length === 1 ? "change" : "changes"}`;
        const exportBtn = root.querySelector("[data-sch-export]");
        if (exportBtn) exportBtn.setAttribute("aria-disabled", sel.length ? "false" : "true");

        // By type: the type filter left out so every type stays visible (as the design)
        const base = D.rows.filter((r) => pass(r, true));
        const counts = OPT.types.map(([name]) => base.filter((r) => r[2] === name));
        const mx = Math.max(1, ...counts.map((a) => a.length));
        $("[data-sch-bytype]").innerHTML = OPT.types
            .map(([name], i) => {
                const a = counts[i];
                const o = a.filter((r) => !r[3]).length;
                const isOn = !!F.type[name];
                return `<button type="button" class="sah-trow${isOn ? " on" : ""}${a.length ? "" : " zero"}" data-type="${esc(name)}" aria-pressed="${isOn}"><span class="nm">${dot(name)}${esc(name)}</span><span class="sah-stack2" style="width:${a.length ? Math.max(6, (100 * a.length) / mx) : 0}%">${
                    o ? `<i style="flex:${o} 1 0;background:var(--sah-orange)"></i>` : ""
                }${a.length - o ? `<i style="flex:${a.length - o} 1 0;background:var(--sah-teal)"></i>` : ""}</span><span class="n">${n(a.length)}</span><span class="a">${a.length ? (o ? `<b class="low">${n(o)} not actioned</b>` : "All actioned") : "None"}</span></button>`;
            })
            .join("");

        drawTable(sel);
    }

    // Not actioned first, longest waiting at the top; then actioned, newest first (as the design).
    function drawTable(sel) {
        sel = sel || D.rows.filter((r) => pass(r));
        const q = T.q.trim().toLowerCase();
        if (q) sel = sel.filter((r) => (D.students[r[7]] || ["", ""]).join(" ").toLowerCase().includes(q));
        sel = sel.slice().sort((a, b) => a[3] - b[3] || (a[3] ? (a[1] < b[1] ? 1 : a[1] > b[1] ? -1 : 0) : a[1] < b[1] ? -1 : a[1] > b[1] ? 1 : 0) || b[0] - a[0]);
        const pages = Math.max(1, Math.ceil(sel.length / T.per));
        T.page = Math.max(0, Math.min(pages - 1, T.page));
        let h = '<thead><tr><th scope="col">Student</th><th scope="col">Type of change</th><th scope="col">Course</th><th scope="col">Intake</th><th scope="col">Confirmed on</th><th scope="col">Actioned</th><th scope="col">Waiting</th><th scope="col">Student status</th></tr></thead><tbody>';
        sel.slice(T.page * T.per, (T.page + 1) * T.per).forEach((r) => {
            const st = D.students[r[7]] || ["", "Student #" + r[7]];
            h += `<tr><td><a href="${esc(route("student.show", r[7]))}" target="_blank" rel="noopener">${esc(st[1])}</a><small>${esc(st[0])}</small></td><td>${pill(r[2])}</td>`;
            h += `<td class="course"${r[4] !== null ? ` title="${esc(D.courses[r[4]])}"` : ""}>${r[4] !== null ? esc(shortCourse[r[4]] || D.courses[r[4]]) : "–"}</td><td>${r[5] !== null ? esc(D.intakes[r[5]]) : "–"}</td><td>${nice(r[1])}</td>`;
            h += `<td><span class="sah-act ${r[3] ? "yes" : "no"}">${r[3] ? "Actioned" : "Not actioned"}</span></td><td>${r[3] ? '<span class="sah-hint">–</span>' : "<b>" + wait(r[1]) + "</b>"}</td><td>${r[6] !== null ? esc(D.statuses[r[6]]) : "–"}</td></tr>`;
        });
        if (!sel.length) h += `<tr><td colspan="8" class="none"><b>${q ? "No student in this selection matches that search." : "No changes of circumstances match these filters."}</b>${q ? "" : "Try a wider date range or clear the filters."}</td></tr>`;
        $("[data-sch-table]").innerHTML = h + "</tbody>";
        $("[data-sch-tcount]").textContent = sel.length ? `Showing ${n(T.page * T.per + 1)} to ${n(Math.min(sel.length, (T.page + 1) * T.per))} of ${n(sel.length)}` : "0 changes";
        $("[data-sch-tpage]").textContent = `Page ${n(T.page + 1)} of ${n(pages)}`;
        $('[data-sch-page="-1"]').disabled = T.page === 0;
        $('[data-sch-page="1"]').disabled = T.page >= pages - 1;
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

    /* ── Events ── */
    root.addEventListener("click", (e) => {
        const t = e.target;
        let b;
        if (t.closest("[data-sch-search]")) {
            if (D) {
                searched = true;
                state.hidden = true;
                body.hidden = false;
                refresh();
            } else if (!loading) {
                load();
            }
            return;
        }
        if ((b = t.closest("[data-type]"))) {
            const key = b.dataset.type;
            F.type[key] = !F.type[key];
            refresh();
        } else if ((b = t.closest("[data-act]"))) {
            F.act = b.dataset.act;
            refresh();
        } else if ((b = t.closest("[data-preset]"))) {
            const p = PRESETS.find((x) => x[0] === b.dataset.preset);
            F.from = p[2]();
            F.to = p[0] === "all" ? "" : today();
            refresh();
        } else if (t.closest("[data-reset]")) {
            F = { from: "", to: "", type: {}, act: "all", course: "" };
            refresh();
        } else if (!searched) {
            return;
        } else if ((b = t.closest("[data-sch-page]"))) {
            T.page += Number(b.dataset.schPage);
            drawTable();
        } else if ((b = t.closest("[data-sch-export]"))) {
            e.preventDefault();
            if (b.getAttribute("aria-disabled") === "true") return;
            const p = new URLSearchParams();
            // The export takes a whole range or none: an open end becomes the first change or today.
            if (F.from || F.to) {
                p.append("from", F.from || (D && D.first) || today());
                p.append("to", F.to || today());
            }
            on(F.type).forEach((x) => p.append("types[]", x));
            p.append("actioned", F.act);
            if (F.course !== "") p.append("course", F.course);
            window.location.href = route(root.dataset.excel) + "?" + p.toString();
        }
    });
    root.addEventListener("change", (e) => {
        if (e.target.id === "schFrom") {
            F.from = e.target.value;
            if (F.to && F.from && F.from > F.to) F.to = F.from;
            refresh(true);
        } else if (e.target.id === "schTo") {
            F.to = e.target.value;
            if (F.from && F.to && F.to < F.from) F.from = F.to;
            refresh(true);
        } else if (e.target.id === "schCourse") {
            F.course = e.target.value;
            refresh(true);
        }
    });
    $("[data-sch-q]").addEventListener("input", (e) => {
        T.q = e.target.value;
        T.page = 0;
        if (D) drawTable();
    });

    // Nothing loads until Search (user): the form only.
    drawFilters();
    paintIcons();
})();
