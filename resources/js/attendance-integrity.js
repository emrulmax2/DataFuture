import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import Litepicker from "litepicker";
import Chart from "chart.js/auto";

/*
 * Attendance Integrity (/attendance-integrity). One script for every screen; the
 * page is picked by #aimRoot[data-page]. Filters live in the URL (the filter bar
 * is a GET form), so every request here carries the page's own query string.
 */
(function () {
    "use strict";

    // Console frame: mobile sidebar and the UK clock in the top bar.
    document.querySelectorAll("[data-aim-side-open]").forEach((b) => b.addEventListener("click", () => document.body.classList.add("aim-side-open")));
    document.querySelectorAll("[data-aim-side-close]").forEach((b) => b.addEventListener("click", () => document.body.classList.remove("aim-side-open")));
    const clock = document.querySelector("[data-aim-clock]");
    if (clock) {
        const fmt = new Intl.DateTimeFormat("en-GB", { timeZone: "Europe/London", weekday: "short", day: "numeric", month: "short", year: "numeric", hour: "2-digit", minute: "2-digit" });
        const tick = () => { clock.textContent = fmt.format(new Date()).replace(/,/g, "").replace(/ (\d{2}:\d{2})$/, " · $1") + " UK"; };
        tick();
        setInterval(tick, 30000);
    }

    const root = document.getElementById("aimRoot");
    if (!root) return;

    const page = root.dataset.page;
    const can = JSON.parse(root.dataset.can || "{}");
    const csrf = () => (document.querySelector('meta[name="csrf-token"]') || {}).content || "";

    const esc = (value) => String(value === null || value === undefined ? "" : value)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    const num = (value) => (value === null || value === undefined || value === "" ? "—" : Number(value).toLocaleString("en-GB"));
    const link = (template, id, text) => (template && id ? `<a href="${esc(template.replace("__ID__", id))}" class="aim-link">${esc(text)}</a>` : esc(text));
    const points = (v) => `<span class="aim-points ${v >= 25 ? "aim-points--hi" : (v >= 10 ? "aim-points--md" : "")}">${esc(v)}</span>`;
    const badge = (cls, text) => `<span class="aim-badge aim-badge--${esc(cls)}">${esc(text)}</span>`;
    const countBox = document.getElementById("aimCount");
    const setCount = (text) => { if (countBox) countBox.textContent = text; };

    /* ---------------------------------------------------------------- Filter bar */

    const tomBase = { dropdownParent: "body", maxOptions: null, create: false, plugins: {} };
    document.querySelectorAll("select[data-aim-tom]").forEach((el) => {
        const multi = el.dataset.aimTom === "multi";
        // eslint-disable-next-line no-new
        new TomSelect(el, { ...tomBase, plugins: multi ? { remove_button: { title: "Remove" } } : {}, placeholder: multi ? "Choose…" : "Any" });
    });
    document.querySelectorAll("select[data-aim-remote]").forEach((el) => {
        const kind = el.dataset.aimRemote;
        // eslint-disable-next-line no-new
        new TomSelect(el, {
            ...tomBase,
            valueField: "id", labelField: "name", searchField: ["name"], placeholder: kind === "students" ? "Registration no or name" : "Name",
            allowEmptyOption: true,
            load: (query, callback) => {
                if (query.length < 2) return callback();
                axios.get(route("attendance.integrity.lookup", kind), { params: { q: query } })
                    .then((r) => callback(r.data)).catch(() => callback());
            },
        });
    });

    const filters = document.getElementById("aimFilters");
    if (filters) {
        const more = filters.querySelector("[data-aim-more]");
        if (more) more.addEventListener("click", () => {
            filters.classList.toggle("is-open");
            more.setAttribute("aria-expanded", filters.classList.contains("is-open") ? "true" : "false");
        });

        const dateInput = document.getElementById("aimDates");
        if (dateInput) {
            const from = document.getElementById("aimDateFrom");
            const to = document.getElementById("aimDateTo");
            const picker = new Litepicker({
                element: dateInput, singleMode: false, autoApply: true, numberOfColumns: 2, numberOfMonths: 2, format: "DD-MM-YYYY",
                dropdowns: { minYear: 2015, maxYear: new Date().getFullYear() + 1, months: true, years: true },
            });
            picker.on("selected", (start, end) => {
                from.value = start.format("YYYY-MM-DD");
                to.value = end.format("YYYY-MM-DD");
            });
            dateInput.addEventListener("dblclick", () => { picker.clearSelection(); dateInput.value = ""; from.value = ""; to.value = ""; });
        }

        // Groups and modules follow the chosen terms.
        const terms = document.getElementById("aimTerms");
        if (terms && terms.tomselect) {
            terms.tomselect.on("change", () => {
                const ids = terms.tomselect.getValue();
                axios.get(route("attendance.integrity.term.options"), { params: { terms: [].concat(ids).join(",") } }).then((r) => {
                    ["groups", "modules"].forEach((key) => {
                        const el = filters.querySelector(`[data-aim-term-options="${key}"]`);
                        if (!el || !el.tomselect) return;
                        el.tomselect.clear(true);
                        el.tomselect.clearOptions();
                        el.tomselect.addOption({ value: "", text: "Any" });
                        (r.data[key] || []).forEach((o) => el.tomselect.addOption({ value: String(o.id), text: o.name }));
                        el.tomselect.refreshOptions(false);
                    });
                });
            });
        }
    }

    /* ---------------------------------------------------------------- Dialogs */

    function dialog({ title, body, okText = "Save", onOk }) {
        const wrap = document.createElement("div");
        wrap.className = "aim-dialog";
        wrap.innerHTML = `<div class="aim-dialog__box" role="dialog" aria-modal="true">
            <div class="aim-dialog__head">${esc(title)}</div>
            <div class="aim-dialog__body">${body}<div class="aim-error" style="display:none"></div></div>
            <div class="aim-dialog__foot"><button type="button" class="aim-btn" data-cancel>Cancel</button>
            <button type="button" class="aim-btn aim-btn--primary" data-ok>${esc(okText)}</button></div></div>`;
        document.body.appendChild(wrap);
        const close = () => wrap.remove();
        const error = wrap.querySelector(".aim-error");
        wrap.querySelector("[data-cancel]").addEventListener("click", close);
        wrap.addEventListener("keydown", (e) => { if (e.key === "Escape") close(); });
        const ok = wrap.querySelector("[data-ok]");
        ok.addEventListener("click", () => {
            ok.disabled = true;
            error.style.display = "none";
            Promise.resolve(onOk(wrap)).then((result) => {
                if (result === false) { ok.disabled = false; return; }
                close();
            }).catch((e) => {
                ok.disabled = false;
                error.textContent = (e && e.response && e.response.data && (e.response.data.message || Object.values(e.response.data.errors || {})[0])) || "Could not save. Please try again.";
                error.style.display = "";
            });
        });
        const first = wrap.querySelector("textarea, input, select");
        if (first) setTimeout(() => first.focus(), 30);

        return wrap;
    }

    const post = (url, data) => axios.post(url, data, { headers: { "X-CSRF-TOKEN": csrf(), Accept: "application/json" } });

    function openCaseDialog({ findingIds = [], subjectType = "", subjectId = "", subjectLabel = "" }) {
        const about = findingIds.length
            ? `<p>${findingIds.length} selected ${findingIds.length === 1 ? "finding" : "findings"} will be linked to the case.</p>`
            : (subjectLabel ? `<p>About: <strong>${esc(subjectLabel)}</strong></p>` : "");
        dialog({
            title: "Open an investigation case",
            okText: "Open case",
            body: `${about}<label>Summary (what is to be investigated)</label><textarea rows="3" class="aim-input" data-summary maxlength="500"></textarea>
                <label>Evidence or context (optional)</label><textarea rows="3" class="aim-input" data-evidence maxlength="5000"></textarea>
                <p class="aim-small" style="margin-top: 10px;">A case records a concern to investigate; it is not a finding of misconduct.</p>`,
            onOk: (wrap) => {
                const summary = wrap.querySelector("[data-summary]").value.trim();
                if (summary.length < 10) {
                    const err = wrap.querySelector(".aim-error");
                    err.textContent = "Please write a summary of at least 10 characters.";
                    err.style.display = "";
                    return false;
                }
                return post(route("attendance.integrity.cases.store"), {
                    finding_ids: findingIds, subject_type: subjectType || null, subject_id: subjectId || null,
                    summary, evidence: wrap.querySelector("[data-evidence]").value.trim(),
                }).then((r) => { window.location.href = r.data.url; });
            },
        });
    }

    document.querySelectorAll("[data-aim-open-case]").forEach((btn) => btn.addEventListener("click", () => openCaseDialog({
        subjectType: btn.dataset.subjectType, subjectId: btn.dataset.subjectId, subjectLabel: btn.dataset.subjectLabel,
    })));

    /* ---------------------------------------------------------------- Evidence panel */

    const LABELS = {
        first_entered_at: "First entered", delay_minutes: "Delay (minutes)", shortest_delay_minutes: "Shortest delay (minutes)", tier_hours: "Over (hours)",
        entered_by: "Entered by", entered_by_teaches_class: "Entered by the class's own tutor", how_known: "How this is known", first_entered_by: "First entered by",
        changed_by: "Changed by", changed_at: "Changed", changed_by_teaches_class: "Changed by the class's own tutor", after_class: "After the class",
        reason: "Reason given", screen: "Screen", class_dates: "Class dates", student_reason: "Student's reason", reply: "Reply to student",
        decided_by: "Decided by", decided_at: "Decided", marked_present_by: "Marked present by", marked_present_at: "Marked present",
        holds_edit_attendance: "Holds Edit Attendance", top_students: "Students", note: "Note", longest_delay: "Longest delay", shortest_delay: "Shortest delay",
        delay_band: "Delay band",
    };
    const label = (key) => LABELS[key] || key.replace(/_/g, " ").replace(/^./, (c) => c.toUpperCase());
    const value = (v) => {
        if (v === true) return "Yes";
        if (v === false) return "No";
        if (v === null || v === undefined || v === "") return "—";
        if (Array.isArray(v)) {
            if (!v.length) return "—";
            const item = (x) => (x && typeof x === "object"
                ? Object.entries(x).filter(([k, y]) => !k.endsWith("_id") && y !== null && y !== "" && typeof y !== "object").map(([, y]) => y).join(" · ")
                : x);
            return `<ul>${v.slice(0, 25).map((x) => `<li>${esc(item(x))}</li>`).join("")}${v.length > 25 ? `<li>… ${v.length - 25} more</li>` : ""}</ul>`;
        }
        if (typeof v === "object") return esc(Object.entries(v).map(([k, x]) => `${label(k)}: ${x}`).join("; "));
        return esc(v);
    };

    function evidenceHtml(d, tableEl) {
        const skip = ["history_id", "attendance_ids", "session_id", "other_attendance_id", "excuse_id", "claim_id", "delay_minutes"];
        const rows = Object.entries(d.evidence || {}).filter(([k]) => !skip.includes(k))
            .map(([k, v]) => `<dt>${esc(label(k))}</dt><dd>${value(v)}</dd>`).join("");
        const meta = [
            ["Rule", d.rule_label], ["Basis", d.basis_label], ["Term", d.term], ["First detected", d.first_detected],
            d.attendance_id ? ["Attendance record", "#" + d.attendance_id] : null, d.session_id ? ["Class date", "#" + d.session_id] : null,
            d.dismiss_note ? ["Dismissed because", d.dismiss_note] : null,
        ].filter(Boolean).map(([k, v]) => `<dt>${esc(k)}</dt><dd>${esc(v)}</dd>`).join("");
        let actions = "";
        const manage = tableEl && tableEl.dataset.manage === "1";
        if (manage && d.status === "open") actions += `<button type="button" class="aim-btn aim-btn--sm" data-dismiss="${d.id}">Dismiss…</button>`;
        if (manage && d.status === "dismissed") actions += `<button type="button" class="aim-btn aim-btn--sm" data-reopen="${d.id}">Reopen</button>`;
        if (d.case_id && tableEl && tableEl.dataset.caseUrl) actions += `<a class="aim-btn aim-btn--sm" href="${esc(tableEl.dataset.caseUrl.replace("__ID__", d.case_id))}">Open the case</a>`;

        return `<dl>${meta}${rows}</dl>${actions ? `<div class="aim-evidence__actions">${actions}</div>` : ""}`;
    }

    /* ---------------------------------------------------------------- Findings table */

    function findingsColumns(tableEl, selectable) {
        const staffUrl = tableEl.dataset.staffUrl;
        const studentUrl = tableEl.dataset.studentUrl;
        const cols = [];
        if (selectable) {
            // Selection only through this column; a click elsewhere opens the evidence.
            cols.push({ formatter: "rowSelection", titleFormatter: "rowSelection", hozAlign: "center", headerSort: false, width: 40, cssClass: "aim-select-col" });
        }
        return cols.concat([
            { title: "", field: "id", width: 30, headerSort: false, hozAlign: "center", formatter: (c) => `<span class="aim-toggle${c.getRow().getData()._open ? " is-open" : ""}">▶</span>` },
            { title: "When", field: "event_at", minWidth: 96, widthGrow: 1, headerSort: false, formatter: (c) => `<div class="aim-cell">${esc((c.getValue() || "").split(" ")[0])}<div class="aim-cell__sub">${esc((c.getValue() || "").split(" ")[1] || "")}</div></div>` },
            { title: "Finding", field: "type_label", minWidth: 130, widthGrow: 1.5, headerSort: false, formatter: (c) => `<div class="aim-cell">${esc(c.getValue())}<div class="aim-cell__sub">${esc(c.getRow().getData().rule_label)}</div></div>` },
            { title: "Summary", field: "summary", minWidth: 240, widthGrow: 4, headerSort: false, formatter: (c) => `<div class="aim-cell">${esc(c.getValue())}</div>` },
            { title: "Staff", field: "staff", minWidth: 120, widthGrow: 1.4, headerSort: false, formatter: (c) => `<div class="aim-cell">${link(staffUrl, c.getRow().getData().staff_id, c.getValue())}</div>` },
            { title: "Student", field: "student", minWidth: 120, widthGrow: 1.4, headerSort: false, formatter: (c) => `<div class="aim-cell">${link(studentUrl, c.getRow().getData().student_id, c.getValue())}</div>` },
            { title: "Points", field: "points", width: 74, hozAlign: "center", headerHozAlign: "center", headerSort: false, formatter: (c) => points(c.getValue()) },
            { title: "Basis", field: "basis_label", minWidth: 128, widthGrow: 1.1, headerSort: false, formatter: (c) => badge(c.getRow().getData().basis, { fact: "Fact", anomaly: "Anomaly", unavailable: "Not recorded" }[c.getRow().getData().basis] || c.getValue()) },
            { title: "Status", field: "status", minWidth: 104, widthGrow: 0.9, headerSort: false, formatter: (c) => badge(c.getValue(), { open: "Open", dismissed: "Dismissed", case: "In a case" }[c.getValue()] || c.getValue()) },
        ]);
    }

    function attachEvidence(tableEl) {
        return {
            rowFormatter: (row) => {
                const d = row.getData();
                row.getElement().classList.add("is-clickable");
                if (d.points >= 25) row.getElement().classList.add("aim-row--high");
                if (!d._open) return;
                const div = document.createElement("div");
                div.className = "aim-evidence";
                div.innerHTML = evidenceHtml(d, tableEl);
                row.getElement().appendChild(div);
            },
            rowClick: (e, row) => {
                if (e.target.closest("a, button, input, .aim-evidence, .aim-select-col")) return;
                row.update({ _open: !row.getData()._open });
                row.reformat();
            },
        };
    }

    function findingsTable(tableEl, options = {}) {
        if (!tableEl) return null;
        const manage = tableEl.dataset.manage === "1";
        const selectable = manage && !options.local;
        const config = {
            layout: "fitColumns",
            virtualDom: false,
            placeholder: "No findings for these filters.",
            // "highlight": rows are selected only through the checkbox column.
            selectable: selectable ? "highlight" : false,
            columns: findingsColumns(tableEl, selectable),
        };
        Object.assign(config, attachEvidence(tableEl));
        if (options.local) {
            config.data = options.data || [];
            config.pagination = "local";
            config.paginationSize = 50;
        } else {
            config.ajaxURL = tableEl.dataset.url;
            config.ajaxConfig = "GET";
            config.pagination = "remote";
            config.paginationSize = 50;
            config.paginationSizeSelector = [25, 50, 100, 200];
            config.ajaxResponse = (url, params, response) => { setCount(`${num(response.total)} ${response.total === 1 ? "finding" : "findings"}`); return response; };
        }
        if (selectable) config.rowSelectionChanged = (data) => {
            const bar = document.getElementById("aimBulkBar");
            if (!bar) return;
            bar.classList.toggle("is-visible", data.length > 0);
            document.getElementById("aimBulkCount").textContent = data.length;
        };
        const table = new Tabulator(tableEl, config);

        // Dismiss / reopen from the evidence panel.
        tableEl.addEventListener("click", (e) => {
            const dismiss = e.target.closest("[data-dismiss]");
            const reopen = e.target.closest("[data-reopen]");
            if (dismiss) {
                dialog({
                    title: "Dismiss this finding",
                    okText: "Dismiss",
                    body: '<label>Why is it dismissed? (kept with the finding)</label><textarea rows="3" class="aim-input" data-note maxlength="500"></textarea>',
                    onOk: (wrap) => post(tableEl.dataset.dismiss.replace("__ID__", dismiss.dataset.dismiss), { note: wrap.querySelector("[data-note]").value.trim() })
                        .then(() => table.replaceData()),
                });
            } else if (reopen) {
                post(tableEl.dataset.reopen.replace("__ID__", reopen.dataset.reopen), {}).then(() => table.replaceData());
            }
        });

        // Bulk bar.
        const bar = document.getElementById("aimBulkBar");
        if (bar && selectable) {
            const ids = () => table.getSelectedData().map((d) => d.id);
            bar.querySelector("[data-aim-case-from-selection]").addEventListener("click", () => openCaseDialog({ findingIds: ids() }));
            bar.querySelector("[data-aim-clear-selection]").addEventListener("click", () => table.deselectRow());
            bar.querySelector("[data-aim-add-to-case]").addEventListener("click", () => {
                axios.get(route("attendance.integrity.cases.open")).then((r) => {
                    if (!r.data.length) { openCaseDialog({ findingIds: ids() }); return; }
                    dialog({
                        title: "Add to an existing case",
                        okText: "Add",
                        body: `<label>Case</label><select class="aim-input" data-case>${r.data.map((c) => `<option value="${c.id}">${esc(c.name)}</option>`).join("")}</select>`,
                        onOk: (wrap) => post(route("attendance.integrity.cases.findings", wrap.querySelector("[data-case]").value), { finding_ids: ids() })
                            .then((res) => { window.location.href = res.data.url; }),
                    });
                });
            });
        }

        return table;
    }

    /* ---------------------------------------------------------------- Pages */

    Chart.defaults.font.family = '"Public Sans", system-ui, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.color = "#667085";

    if (page === "dashboard") {
        const data = JSON.parse((document.getElementById("aimDashboardData") || {}).textContent || "{}");
        const trendEl = document.getElementById("aimTrendChart");
        if (trendEl && data.trend) {
            // eslint-disable-next-line no-new
            new Chart(trendEl, {
                data: {
                    labels: data.trend.map((r) => r.week),
                    datasets: [
                        { type: "bar", label: "Records", data: data.trend.map((r) => r.records), backgroundColor: "rgba(15, 61, 87, .14)", borderRadius: 4, yAxisID: "y" },
                        { type: "line", label: "Changed", data: data.trend.map((r) => r.changed), borderColor: "#134e6f", backgroundColor: "#134e6f", borderWidth: 2, pointRadius: 2, tension: 0.3, yAxisID: "y" },
                        { type: "line", label: "Entered >24h late", data: data.trend.map((r) => r.late_entered), borderColor: "#f79009", backgroundColor: "#f79009", borderWidth: 2, pointRadius: 2, tension: 0.3, yAxisID: "y1" },
                        { type: "line", label: "Findings", data: data.trend.map((r) => r.findings), borderColor: "#d92d20", backgroundColor: "#d92d20", borderWidth: 2, pointRadius: 2, tension: 0.3, yAxisID: "y1" },
                    ],
                },
                options: {
                    maintainAspectRatio: false, interaction: { mode: "index", intersect: false },
                    plugins: { legend: { position: "bottom", labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true } } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: "#f0f2f5" }, title: { display: true, text: "Records" } },
                        y1: { beginAtZero: true, position: "right", grid: { drawOnChartArea: false }, title: { display: true, text: "Late entries / findings" } },
                    },
                },
            });
        }
        const termEl = document.getElementById("aimTermChart");
        if (termEl && data.by_term) {
            // eslint-disable-next-line no-new
            new Chart(termEl, {
                type: "bar",
                data: { labels: data.by_term.map((r) => r.term), datasets: [{ label: "Attendance %", data: data.by_term.map((r) => r.rate), backgroundColor: "#0f3d57", borderRadius: 6, maxBarThickness: 56 }] },
                options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, max: 100, grid: { color: "#f0f2f5" }, ticks: { callback: (v) => v + "%" } } } },
            });
        }
    }

    if (page === "records") {
        const el = document.getElementById("aimRecordsTable");
        // eslint-disable-next-line no-new
        new Tabulator(el, {
            ajaxURL: el.dataset.url, ajaxConfig: "GET", pagination: "remote", paginationSize: 50, paginationSizeSelector: [50, 100, 200],
            layout: "fitColumns", placeholder: "No records.",
            ajaxResponse: (url, params, r) => { setCount(`${num(r.total)} records`); return r; },
            columns: [
                { title: "Class date", field: "class_date", minWidth: 96, widthGrow: 1, headerSort: false, formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell">${esc(d.class_day)} ${esc(d.class_date)}<div class="aim-cell__sub">${esc(d.class_time)}</div></div>`; } },
                { title: "Class", field: "module", minWidth: 170, widthGrow: 2.6, headerSort: false, formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell">${esc(d.module)}<div class="aim-cell__sub">${esc([d.group, d.class_type, d.teacher].filter(Boolean).join(" · "))}</div></div>`; } },
                { title: "Student", field: "registration_no", minWidth: 120, widthGrow: 1.5, headerSort: false, formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell">${link(can.view_students ? route("attendance.integrity.students.show", d.student_id) : "", d.student_id, d.registration_no)}<div class="aim-cell__sub">${esc(d.student_name)}</div></div>`; } },
                { title: "Status", field: "status", minWidth: 90, widthGrow: 1, headerSort: false },
                { title: "First entered", field: "first_entered_at", minWidth: 120, widthGrow: 1.4, headerSort: false, formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell">${esc(d.first_entered_by)}<div class="aim-cell__sub">${esc(d.first_entered_at)}${d.entered_after ? " · " + esc(d.entered_after) : ""}</div></div>`; } },
                { title: "Last changed", field: "last_changed_at", minWidth: 120, widthGrow: 1.4, headerSort: false, formatter: (c) => { const d = c.getRow().getData(); if (!d.last_changed_by) return '<span class="aim-cell__sub">Not changed</span>'; return `<div class="aim-cell">${esc(d.last_changed_by)}${d.changer_teaches_class === false ? ' <span class="aim-badge aim-badge--medium">not their class</span>' : ""}<div class="aim-cell__sub">${esc(d.last_changed_at)}</div></div>`; } },
                { title: "Deleted", field: "deleted_at", minWidth: 90, widthGrow: 0.9, headerSort: false },
                { title: "Note", field: "note", minWidth: 80, widthGrow: 0.9, headerSort: false },
            ],
        });
    }

    function localTable(el, columns, sortField, rowClick) {
        const table = new Tabulator(el, {
            ajaxURL: el.dataset.url, ajaxConfig: "GET", layout: "fitColumns", pagination: "local", paginationSize: 50, paginationSizeSelector: [25, 50, 100, 250],
            placeholder: "Nothing under these filters.", initialSort: [{ column: sortField, dir: "desc" }],
            ajaxResponse: (url, params, r) => { setCount(`${num(r.data.length)} listed`); return r.data; },
            rowFormatter: (row) => { row.getElement().classList.add("is-clickable"); if (row.getData().band === "high") row.getElement().classList.add("aim-row--high"); },
            rowClick: (e, row) => { if (!e.target.closest("a")) rowClick(row.getData()); },
            columns,
        });
        const search = document.getElementById("aimTableSearch");
        if (search) search.addEventListener("input", () => {
            const q = search.value.trim().toLowerCase();
            if (!q) { table.clearFilter(); return; }
            table.setFilter((d) => Object.values(d).some((v) => typeof v === "string" && v.toLowerCase().includes(q)));
        });

        return table;
    }
    const n = (title, field, extra = {}) => ({ title, field, hozAlign: "right", headerHozAlign: "right", sorter: "number", minWidth: 62, widthGrow: 0.8, formatter: (c) => num(c.getValue()), ...extra });
    const level = { title: "Level", field: "band", minWidth: 78, widthGrow: 0.8, formatter: (c) => (c.getValue() ? badge(c.getValue(), c.getValue().replace(/^./, (x) => x.toUpperCase())) : "") };

    if (page === "staff") {
        const el = document.getElementById("aimStaffTable");
        localTable(el, [
            { title: "Staff", field: "name", minWidth: 170, widthGrow: 2.6, formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell"><span class="aim-link">${esc(d.name)}</span><div class="aim-cell__sub">${esc([d.job_title, d.department].filter(Boolean).join(" · "))}</div></div>`; } },
            n("Entered", "created"), n("Changed", "changed"), n("Late entries", "late_entered"), n("Absent > Present", "positive"), n("Present > Absent", "negative"),
            n("Deleted", "deleted"), n("Students", "students"), n("Classes", "classes"), n("Bulk", "bulk"), n("Not their class", "unauthorised"), n("Pairs", "pairs"),
            n("Changes / 100", "changes_per_100", { formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell">${c.getValue() ?? "—"}<div class="aim-cell__sub">peer ${d.peer_changes_per_100 ?? "—"}</div></div>`; } }),
            n("Score", "score", { formatter: (c) => `<strong>${num(c.getValue())}</strong>` }), level,
        ], "score", (d) => { window.location.href = el.dataset.show.replace("__ID__", d.user_id); });
    }

    if (page === "students") {
        const el = document.getElementById("aimStudentsTable");
        localTable(el, [
            { title: "Student", field: "registration_no", minWidth: 150, widthGrow: 2.2, formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell"><span class="aim-link">${esc(d.registration_no)}</span><div class="aim-cell__sub">${esc(d.name)}</div></div>`; } },
            n("Scheduled", "scheduled"), n("Present", "present"), n("Absent", "absent"), n("Late", "late"), n("Excused", "excused"),
            n("Attendance %", "rate", { formatter: (c) => (c.getValue() === null ? "—" : c.getValue() + "%") }),
            n("Entered late", "late_entered"), n("Absent > Present", "positive"), n("Present > Absent", "negative"),
            n("Staff entering", "staff_entering"), n("Staff correcting", "staff_correcting"), n("Findings", "flagged"), n("Score", "score", { formatter: (c) => `<strong>${num(c.getValue())}</strong>` }), level,
        ], "score", (d) => { window.location.href = el.dataset.show.replace("__ID__", d.student_id); });
    }

    if (["findings", "staff-show", "student-show"].includes(page)) {
        findingsTable(document.getElementById("aimFindingsTable"));
    }

    if (page === "case-show") {
        const el = document.getElementById("aimCaseFindings");
        if (el) findingsTable(el, { local: true, data: JSON.parse(el.dataset.rows || "[]") });
    }

    if (page === "amendments") {
        const el = document.getElementById("aimHistoryTable");
        const noticeWrap = document.getElementById("aimNoticeWrap");
        let noticeTable = null;
        const history = new Tabulator(el, {
            ajaxURL: el.dataset.url, ajaxConfig: "GET", ajaxParams: { kind: "status" }, pagination: "remote", paginationSize: 50, paginationSizeSelector: [50, 100, 200],
            layout: "fitColumns", placeholder: "No changes recorded in the history for these filters yet.",
            ajaxResponse: (url, params, r) => { setCount(`${num(r.total)} recorded`); return r; },
            rowFormatter: (row) => { if (row.getData().late) row.getElement().classList.add("aim-row--high"); },
            columns: [
                { title: "Changed (UK)", field: "changed_at", minWidth: 110, widthGrow: 1.1, headerSort: false, formatter: (c) => { const d = c.getRow().getData(); return `<div class="aim-cell">${esc(d.changed_at)}<div class="aim-cell__sub">${esc(d.after_class)}</div></div>`; } },
                { title: "Change", field: "change", minWidth: 80, widthGrow: 0.8, headerSort: false, formatter: (c) => `<strong>${esc(c.getValue())}</strong>${c.getRow().getData().late ? ' <span class="aim-badge aim-badge--medium">late</span>' : ""}` },
                { title: "Student", field: "student", minWidth: 140, widthGrow: 1.6, headerSort: false, formatter: (c) => `<div class="aim-cell">${link(el.dataset.studentUrl, c.getRow().getData().student_id, c.getValue())}</div>` },
                { title: "Class", field: "class", minWidth: 170, widthGrow: 2.2, headerSort: false, formatter: (c) => `<div class="aim-cell">${esc(c.getValue())}<div class="aim-cell__sub">${esc(c.getRow().getData().class_date)}</div></div>` },
                { title: "Changed by", field: "changed_by", minWidth: 120, widthGrow: 1.3, headerSort: false, formatter: (c) => `<div class="aim-cell">${link(el.dataset.staffUrl, c.getRow().getData().changed_by_id, c.getValue())}<div class="aim-cell__sub">${esc(c.getRow().getData().screen)}</div></div>` },
                { title: "Reason", field: "reason", minWidth: 160, widthGrow: 2, headerSort: false, formatter: (c) => `<div class="aim-cell">${esc(c.getValue())}</div>` },
            ],
        });
        document.querySelectorAll("#aimHistoryKinds [data-kind]").forEach((btn) => btn.addEventListener("click", () => {
            document.querySelectorAll("#aimHistoryKinds [data-kind]").forEach((b) => b.classList.toggle("is-on", b === btn));
            const kind = btn.dataset.kind;
            const notice = kind === "notice";
            el.style.display = notice ? "none" : "";
            noticeWrap.style.display = notice ? "" : "none";
            if (notice) {
                if (!noticeTable) noticeTable = findingsTable(document.getElementById("aimFindingsTable"));
                else noticeTable.replaceData();
            } else {
                history.setData(el.dataset.url, { kind });
            }
        }));
    }

    if (page === "settings") {
        const form = document.querySelector("[data-aim-settings]");
        if (form) {
            const bar = form.querySelector("[data-aim-savebar]");
            let dirty = false;
            let saving = false;
            const markDirty = () => { dirty = true; if (bar) bar.classList.add("is-dirty"); };
            form.addEventListener("input", markDirty);
            form.addEventListener("change", markDirty);
            form.addEventListener("submit", () => { saving = true; });
            // Leaving with unsaved changes asks first.
            window.addEventListener("beforeunload", (e) => { if (dirty && !saving) { e.preventDefault(); e.returnValue = ""; } });

            // A rule switched off dims its settings (they are kept, just not used).
            form.querySelectorAll(".aim-setgroup__head .aim-switch input").forEach((sw) => {
                const group = sw.closest(".aim-setgroup");
                const sync = () => group.classList.toggle("is-off", !sw.checked);
                sw.addEventListener("change", sync);
                sync();
            });

            // Side list follows the group in view.
            const links = Array.from(document.querySelectorAll(".aim-settings__nav a[href^='#set-']"));
            if (links.length && "IntersectionObserver" in window) {
                const byId = new Map(links.map((a) => [a.getAttribute("href").slice(1), a]));
                const io = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (!entry.isIntersecting) return;
                        links.forEach((a) => a.classList.toggle("is-current", a === byId.get(entry.target.id)));
                    });
                }, { rootMargin: "-90px 0px -65% 0px" });
                byId.forEach((a, id) => { const el = document.getElementById(id); if (el) io.observe(el); });
            }

            // After a failed save, go to the first field to fix.
            const firstError = form.querySelector(".aim-setrow.has-error");
            if (firstError) firstError.scrollIntoView({ block: "center" });
        }
    }
})();
