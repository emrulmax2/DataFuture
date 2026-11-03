import xlsx from "xlsx";
import Litepicker from "litepicker";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

/*
 * Reports > Non-Tutor Attendance Updates.
 * Attendance now Present, last changed by staff with no link to the class.
 * Search by attendance term and/or a date range (on the class date or the date
 * changed) -> one row per member of staff (red = needs review) -> click a row for that
 * person's changes: "All changes", "By student", "By tutor / personal tutor" and, for
 * red people only, "Needs review" (students whose absence they changed to Present ->
 * every class date of those classes). The Excel files are built here in the browser.
 */
(function () {
    "use strict";

    const page = document.getElementById("ntuReport");
    if (!page) return;

    const form = document.getElementById("ntuSearchForm");
    const termsError = document.getElementById("ntuTermsError");
    const dateInput = document.getElementById("ntuDateRange");
    const dateClear = document.getElementById("ntuDateClear");
    const dateOn = document.getElementById("ntuDateOn");
    const errorBox = document.getElementById("ntuError");
    const results = document.getElementById("ntuResults");
    const countBox = document.getElementById("ntuCount");
    const searchBtn = document.getElementById("ntuSearchBtn");
    const exportBtn = document.getElementById("ntuExportBtn");

    const modalEl = document.getElementById("ntuDetailModal");
    const detailName = document.getElementById("ntuDetailName");
    const detailMeta = document.getElementById("ntuDetailMeta");
    const detailStats = document.getElementById("ntuDetailStats");
    const detailTabs = document.getElementById("ntuDetailTabs");
    const detailSearch = document.getElementById("ntuDetailSearch");
    const detailCount = document.getElementById("ntuDetailCount");
    const detailTableEl = document.getElementById("ntuDetailTable");
    const detailExportBtn = document.getElementById("ntuDetailExportBtn");
    const reviewTab = document.getElementById("ntuReviewTab");
    const reviewBox = document.getElementById("ntuReviewBox");

    const NOTE = "Each attendance row only remembers the last person who changed it. If the class tutor saved the row again afterwards, the earlier change is not shown, so every number is a minimum. "
        + "\"Register saved again\" marks every row of the class as changed, even rows whose status stayed the same. Times are UK time.";

    const termSelect = new TomSelect("#ntuTerms", {
        plugins: { remove_button: { title: "Remove" } },
        placeholder: "Select attendance term(s)",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown lcc-tom-float",
        maxOptions: null,
        create: false,
    });

    const datePicker = new Litepicker({
        element: dateInput,
        autoApply: true,
        singleMode: false,
        numberOfColumns: 2,
        numberOfMonths: 2,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        dropdowns: { minYear: 2015, maxYear: new Date().getFullYear() + 1, months: true, years: true },
    });
    const syncDateClear = () => { dateClear.style.display = dateInput.value ? "" : "none"; };
    datePicker.on("selected", syncDateClear);
    dateClear.addEventListener("click", () => {
        datePicker.clearSelection();
        dateInput.value = "";
        syncDateClear();
    });

    /** "01-10-2026 - 31-10-2026" -> { date_from: "2026-10-01", date_to: "2026-10-31" }, or null. */
    const readDates = () => {
        const found = dateInput.value.match(/(\d{2})-(\d{2})-(\d{4})/g);
        if (!found || !found.length) return null;
        const ymd = (text) => text.split("-").reverse().join("-");
        return { date_from: ymd(found[0]), date_to: ymd(found[found.length - 1]), date_on: dateOn.value };
    };

    let summaryTable = null;
    let detailTable = null;
    let current = null; // { params, meta, rows }
    let detailUser = null;
    let detailView = "rows"; // "rows" | "students"
    let studentRows = []; // the "By student" list as loaded (for its Excel file)
    let tutorRows = []; // the "By tutor" list as loaded
    const tutorState = new Map(); // tutor_id -> "loading" | "error" | class dates[]
    const sessionState = new Map(); // session_id -> "loading" | "error" | rows[]
    const isTutorView = () => detailView === "tutor";
    let buildToken = 0;
    let searchTimer = null;
    const expanded = new Map(); // student_id -> "loading" | "error" | rows[]
    let reviewRows = []; // the "Needs review" students as loaded
    const reviewState = new Map(); // student_id -> "loading" | "error" | class dates[]

    const esc = (value) => String(value === null || value === undefined ? "" : value)
        .replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
    const num = (value) => Number(value || 0).toLocaleString("en-GB");
    const plural = (n, word) => `${num(n)} ${word}${n === 1 ? "" : "s"}`;
    const classCount = (n) => `${num(n)} ${n === 1 ? "class" : "classes"}`;
    const refreshIcons = () => createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    const fileName = (text) => text.replace(/[\\/:*?"<>|]+/g, " ").replace(/\s+/g, " ").trim().slice(0, 120) + ".xlsx";

    const setBusy = (button, busy, label) => {
        button.disabled = busy;
        if (busy) {
            button.dataset.label = button.innerHTML;
            button.textContent = label;
        } else if (button.dataset.label) {
            button.innerHTML = button.dataset.label;
            refreshIcons();
        }
    };

    const showError = (message) => {
        errorBox.textContent = message;
        errorBox.style.display = message ? "" : "none";
    };

    const errorMessage = (error) => (error && error.response && error.response.data && error.response.data.message)
        || "Something went wrong. Please try again.";

    const numberColumn = (title, field, alert) => ({
        title, field, hozAlign: "right", headerHozAlign: "right", sorter: "number", headerSort: true, minWidth: 56, widthGrow: 1,
        formatter: (cell) => {
            const value = cell.getValue();
            return alert && value > 0 ? `<span class="ntu-num--alert">${num(value)}</span>` : num(value);
        },
    });

    const group = (title, columns) => ({ title, columns });
    const dateColumn = (title, field) => ({
        title, field, minWidth: 94, widthGrow: 1.45, headerSort: false,
        formatter: (cell) => {
            const [date, time] = String(cell.getValue() || "").split(" ");
            return `<div class="ntu-cell"><div style="white-space: nowrap;">${esc(date)}</div><div class="ntu-cell__sub">${esc(time || "")}</div></div>`;
        },
    });

    /** "14 after a rejected excuse · 179 lone Presents" (the full reasons are in the tag's tooltip). */
    const reviewShort = (d) => [
        d.after_rejected ? `${num(d.after_rejected)} after a rejected excuse` : "",
        d.review_reasons.some((r) => r.includes("lone")) ? `${num(d.lone)} lone Presents (${Math.round(d.lone_share)}%)` : "",
    ].filter(Boolean).join(" · ");

    /* ---------- Summary ---------- */

    const buildSummary = (rows) => {
        if (summaryTable) summaryTable.destroy();

        summaryTable = new Tabulator("#ntuTable", {
            data: rows,
            layout: "fitColumns",
            placeholder: "No attendance in the selected term(s) was changed to Present by staff outside the class.",
            pagination: "local",
            paginationSize: 50,
            paginationSizeSelector: [25, 50, 100],
            initialSort: [{ column: "rows_updated", dir: "desc" }],
            rowClick: (e, row) => {
                if (e.target.closest("a")) return;
                openDetails(row.getData());
            },
            rowFormatter: (row) => {
                row.getElement().classList.toggle("ntu-row--review", !!row.getData().review);
            },
            columns: [
                {
                    title: "Staff", field: "name", minWidth: 140, widthGrow: 2.6, headerSort: true,
                    formatter: (cell) => {
                        const d = cell.getData();
                        const name = d.profile_url
                            ? `<a href="${esc(d.profile_url)}" target="_blank" class="text-primary">${esc(d.name)}</a>`
                            : esc(d.name);
                        return `<div class="ntu-staff"><div class="ntu-staff__name">${name}</div>`
                            + `<div class="ntu-staff__meta">${esc([d.job_title, d.department].filter(Boolean).join(" · "))}</div>`
                            + (d.review ? `<span class="ntu-flag" title="${esc(d.review_reasons.join(". "))}">Needs review</span>`
                                + `<div class="ntu-flag-why">${esc(reviewShort(d))}</div>` : "")
                            + "</div>";
                    },
                },
                numberColumn("Rows changed", "rows_updated"),
                numberColumn("Absences overturned", "overturned"),
                group("Affected", [
                    numberColumn("Classes", "classes"),
                    numberColumn("Sessions", "sessions"),
                    numberColumn("Students", "students"),
                ]),
                group("How changed", [
                    numberColumn("Single", "single_changes"),
                    numberColumn("Register", "register_saves"),
                ]),
                group("After class end", [
                    numberColumn("≤ 1 h", "within_hour"),
                    numberColumn("1 h – 7 d", "within_week"),
                    numberColumn("> 7 d", "after_week", true),
                ]),
                group("Change dates", [
                    dateColumn("First", "first_update"),
                    dateColumn("Last", "last_update"),
                ]),
                {
                    title: "", field: "user_id", headerSort: false, hozAlign: "right", width: 70,
                    formatter: () => '<span class="btn btn-sm btn-outline-secondary">View</span>',
                },
            ],
        });
    };

    const search = (event) => {
        if (event) event.preventDefault();

        const terms = termSelect.getValue();
        const dates = readDates();
        termsError.style.display = terms.length || dates ? "none" : "";
        if (!terms.length && !dates) return;

        showError("");
        setBusy(searchBtn, true, "Searching…");

        const params = { terms: terms.join(","), ...(dates || {}) };
        axios.get(route("reports.non.tutor.attendance.list"), { params }).then((response) => {
            const rows = response.data.rows || [];
            current = { params, meta: response.data.meta, rows };

            const total = rows.reduce((sum, r) => sum + r.rows_updated, 0);
            const flagged = rows.filter((r) => r.review).length;
            countBox.textContent = `${num(rows.length)} staff · ${num(total)} attendance rows changed to Present`
                + (flagged ? ` · ${num(flagged)} need${flagged === 1 ? "s" : ""} review` : "") + ` · ${response.data.meta.label}`;
            results.style.display = "";
            buildSummary(rows);
        }).catch((error) => {
            showError(errorMessage(error));
        }).finally(() => setBusy(searchBtn, false));
    };

    form.addEventListener("submit", search);

    /** File names: the terms and the date range, with the dates kept file-name safe. */
    const fileScope = () => current.meta.label.replace(/\//g, "-").replace(/ · /g, " - ");

    const headerBlock = (title, extra) => [
        [title],
        ...extra,
        ["Attendance term(s)", current.meta.terms],
        ...(current.meta.dates ? [["Date range", current.meta.dates]] : []),
        ["Rows shown", current.meta.rows_shown],
        ["Generated", current.meta.generated_at],
        ["Generated by", current.meta.generated_by],
        ["Note", NOTE],
        [],
    ];

    const writeWorkbook = (aoa, sheetName, name, widths) => {
        const sheet = xlsx.utils.aoa_to_sheet(aoa);
        sheet["!cols"] = widths.map((wch) => ({ wch }));
        const book = xlsx.utils.book_new();
        xlsx.utils.book_append_sheet(book, sheet, sheetName);
        xlsx.writeFile(book, fileName(name));
    };

    const writeBook = (sheets, name) => {
        const book = xlsx.utils.book_new();
        sheets.forEach(({ title, aoa, widths }) => {
            const sheet = xlsx.utils.aoa_to_sheet(aoa);
            sheet["!cols"] = widths.map((wch) => ({ wch }));
            xlsx.utils.book_append_sheet(book, sheet, title);
        });
        xlsx.writeFile(book, fileName(name));
    };

    exportBtn.addEventListener("click", () => {
        if (!current) return;

        const rows = summaryTable ? summaryTable.getData("active") : current.rows;
        const aoa = headerBlock("Non-Tutor Attendance Updates", [["Needs review", current.meta.review_rule]]);
        aoa.push([
            "Staff", "Job title", "Department", "Needs review", "Why", "Rows changed to Present", "Absences overturned",
            "Lone Presents between absences", "Lone Presents %", "Present after a rejected excuse", "Students with 3+ absences overturned",
            "Classes", "Sessions", "Students", "Single changes", "Register saved again", "Same day as first entry",
            "Within 1 hour of class end", "1 hour - 7 days after", "Over 7 days after", "First change (UK)", "Last change (UK)",
        ]);
        rows.forEach((r) => aoa.push([
            r.name, r.job_title, r.department, r.review ? "Yes" : "", r.review_reasons.join(". "), r.rows_updated, r.overturned,
            r.lone, r.lone_share, r.after_rejected, r.repeat_students,
            r.classes, r.sessions, r.students, r.single_changes, r.register_saves, r.same_day,
            r.within_hour, r.within_week, r.after_week, r.first_update, r.last_update,
        ]));

        writeWorkbook(aoa, "Staff", `Non-tutor attendance updates - ${fileScope()}`,
            [28, 26, 20, 8, 60, 14, 12, 12, 10, 12, 14, 10, 10, 10, 12, 12, 12, 12, 12, 12, 17, 17]);
    });

    /* ---------- Details window ---------- */

    const chip = (label, value, alert) => `<div class="ntu-chip${alert && value > 0 ? " ntu-chip--alert" : ""}">`
        + `<div class="ntu-chip__value">${num(value)}</div><div class="ntu-chip__label">${label}</div></div>`;

    const detailParams = () => ({
        ...current.params,
        user_id: detailUser.user_id,
        search: detailSearch.value.trim(),
    });

    const filterRows = () => [
        ["Staff", detailUser.name],
        ["Job title", [detailUser.job_title, detailUser.department].filter(Boolean).join(" · ")],
        ["Search", detailSearch.value.trim()],
    ];

    const sessionHtml = (d) => `<div class="ntu-cell"><div>${esc(d.session_day)} ${esc(d.session_date)}</div><div class="ntu-cell__sub">${esc(d.class_time)}</div></div>`;
    const classHtml = (d) => `<div class="ntu-cell"><div>${esc(d.module)}</div><div class="ntu-cell__sub">${esc([d.group, d.class_type, d.term].filter(Boolean).join(" · "))}</div></div>`;
    /** Who made the last change, when (UK) and how long after the class ended. */
    const changedHtml = (d) => `<div class="ntu-cell"><div>${esc(d.changed_by)}</div>`
        + `<div class="ntu-cell__sub">${esc([d.updated_at, d.after_class].filter(Boolean).join(" · "))}</div></div>`;
    const firstHtml = (d) => `<div class="ntu-cell"><div>${esc(d.first_entered_by)}</div><div class="ntu-cell__sub">${esc(d.first_entered_at)}</div></div>`;
    const tutorsHtml = (d) => `<div class="ntu-cell">${[["Tutor", d.class_tutor], ["Personal tutor", d.personal_tutor], ["Cover", d.cover_tutor]]
        .filter(([, name]) => name)
        .map(([label, name]) => `<div><span class="ntu-cell__sub">${label}:</span> ${esc(name)}</div>`).join("")}</div>`;
    const studentHtml = (d) => {
        const reg = d.student_url ? `<a href="${esc(d.student_url)}" target="_blank" class="text-primary">${esc(d.registration_no)}</a>` : esc(d.registration_no);
        return `<div class="ntu-cell"><div>${reg}</div><div class="ntu-cell__sub">${esc(d.student_name)}</div></div>`;
    };

    /* "All changes" tab: every row, newest first, paged on the server. */
    const buildRowsTable = () => {
        detailTableEl.classList.remove("is-grouped");
        detailTable = new Tabulator(detailTableEl, {
            ajaxURL: route("reports.non.tutor.attendance.details"),
            ajaxParams: detailParams(),
            ajaxConfig: "GET",
            ajaxResponse: (url, params, response) => {
                detailCount.textContent = plural(response.total || 0, "change");
                return response;
            },
            pagination: "remote",
            paginationSize: 50,
            paginationSizeSelector: [50, 100, 250],
            layout: "fitColumns",
            placeholder: "No changes found",
            columns: [
                { title: "Session", field: "session_date", minWidth: 95, widthGrow: 1.25, headerSort: false, formatter: (cell) => sessionHtml(cell.getData()) },
                { title: "Class", field: "module", minWidth: 150, widthGrow: 2.4, headerSort: false, formatter: (cell) => classHtml(cell.getData()) },
                { title: "Student", field: "registration_no", minWidth: 115, widthGrow: 1.5, headerSort: false, formatter: (cell) => studentHtml(cell.getData()) },
                { title: "Status now", field: "status", minWidth: 78, widthGrow: 1.05, headerSort: false },
                { title: "How", field: "how", minWidth: 88, widthGrow: 1.15, headerSort: false },
                { title: "Changed by (UK)", field: "changed_by", minWidth: 100, widthGrow: 1.3, headerSort: false, formatter: (cell) => changedHtml(cell.getData()) },
                { title: "First entered", field: "first_entered_at", minWidth: 100, widthGrow: 1.3, headerSort: false, formatter: (cell) => firstHtml(cell.getData()) },
                { title: "Class tutors", field: "class_tutor", minWidth: 120, widthGrow: 1.6, headerSort: false, formatter: (cell) => tutorsHtml(cell.getData()) },
                { title: "Note", field: "note", minWidth: 60, widthGrow: 0.8, headerSort: false, formatter: (cell) => `<div class="ntu-cell">${esc(cell.getValue())}</div>` },
            ],
        });
    };

    /* "By student" tab: a student's own changes, opened under their line. */
    const studentChanges = (rows) => '<table class="ntu-mini"><colgroup>'
        + '<col style="width:12%"><col style="width:25%"><col style="width:10%"><col style="width:12%"><col style="width:13%"><col style="width:13%"><col style="width:15%">'
        + "</colgroup><thead><tr><th>Class date</th><th>Class</th><th>Status now</th><th>How</th><th>Changed by (UK)</th><th>First entered</th><th>Class tutors</th></tr></thead>"
        + `<tbody>${rows.map((r) => `<tr><td>${sessionHtml(r)}</td><td>${classHtml(r)}</td><td>${esc(r.status)}</td>`
            + `<td>${esc(r.how)}${r.note ? `<div class="ntu-cell__sub">${esc(r.note)}</div>` : ""}</td><td>${changedHtml(r)}</td><td>${firstHtml(r)}</td><td>${tutorsHtml(r)}</td></tr>`).join("")}</tbody></table>`;

    const toggleStudent = (row) => {
        const id = row.getData().student_id;
        if (expanded.has(id)) {
            expanded.delete(id);
            row.reformat();
            return;
        }

        const token = buildToken;
        expanded.set(id, "loading");
        row.reformat();

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), student_id: id } })
            .then((response) => { if (token === buildToken) { expanded.set(id, response.data.data || []); row.reformat(); } })
            .catch(() => { if (token === buildToken) { expanded.set(id, "error"); row.reformat(); } });
    };

    const buildStudentsTable = () => {
        const token = buildToken;
        detailTableEl.classList.add("is-grouped");
        detailCount.textContent = "Loading…";

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), group: "student" } })
            .then((response) => {
                if (token !== buildToken) return;
                studentRows = response.data.data || [];
                const total = studentRows.reduce((sum, s) => sum + s.rows_changed, 0);
                detailCount.textContent = `${plural(studentRows.length, "student")} · ${plural(total, "change")}`;

                detailTable = new Tabulator(detailTableEl, {
                    data: studentRows,
                    layout: "fitColumns",
                    virtualDom: false, // a student's changes open inside the row and change its height
                    pagination: "local",
                    paginationSize: 50,
                    paginationSizeSelector: [50, 100, 250],
                    placeholder: "No changes found",
                    initialSort: [{ column: "rows_changed", dir: "desc" }],
                    rowClick: (e, row) => {
                        if (e.target.closest("a") || e.target.closest(".ntu-sub-wrap")) return;
                        toggleStudent(row);
                    },
                    rowFormatter: (row) => {
                        const id = row.getData().student_id;
                        if (!expanded.has(id)) return;
                        const state = expanded.get(id);
                        const wrap = document.createElement("div");
                        wrap.className = "ntu-sub-wrap";
                        wrap.innerHTML = state === "loading" ? '<div class="ntu-sub-msg">Loading…</div>'
                            : state === "error" ? '<div class="ntu-sub-msg">Could not load the changes. Please try again.</div>'
                            : studentChanges(state);
                        row.getElement().appendChild(wrap);
                    },
                    columns: [
                        {
                            title: "", field: "student_id", width: 36, headerSort: false, hozAlign: "center",
                            formatter: (cell) => `<span class="ntu-toggle${expanded.has(cell.getValue()) ? " is-open" : ""}">▶</span>`,
                        },
                        { title: "Student", field: "registration_no", minWidth: 150, widthGrow: 2.6, headerSort: true, formatter: (cell) => studentHtml(cell.getData()) },
                        numberColumn("Rows changed", "rows_changed"),
                        group("Affected", [
                            numberColumn("Sessions", "sessions"),
                            numberColumn("Classes", "classes"),
                        ]),
                        group("How changed", [
                            numberColumn("Single", "single_changes"),
                            numberColumn("Register", "register_saves"),
                        ]),
                        group("After class end", [
                            numberColumn("> 7 d", "after_week", true),
                        ]),
                        group("Change dates", [
                            dateColumn("First", "first_update"),
                            dateColumn("Last", "last_update"),
                        ]),
                    ],
                });
            })
            .catch((error) => { if (token === buildToken) detailCount.textContent = errorMessage(error); });
    };


    /* "By tutor" tab: who teaches the class (its tutor, else its personal tutor)
       -> class dates (cards) -> the students whose attendance was changed. */
    const STATUS_CLASS = { Completed: "is-done", Canceled: "is-cancelled", Ongoing: "is-live" };

    const classCard = (tutorId, s) => {
        const open = sessionState.has(s.session_id);
        const teachers = [["Tutor", s.class_tutor], ["Personal tutor", s.personal_tutor], ["Cover", s.cover_tutor]]
            .filter(([, name]) => name).map(([label, name]) => `${label}: ${esc(name)}`).join(" · ");
        const held = s.started_by
            ? `Started ${esc(s.started_at)}${s.ended_at ? "–" + esc(s.ended_at) : ""} by ${esc(s.started_by)}`
            : '<span class="ntu-warn">No class start record</span>';
        const changedWhen = s.last_update !== s.first_update ? `${esc(s.first_update)} → ${esc(s.last_update)}` : esc(s.first_update);

        const head = `<div class="ntu-class__head" data-tutor="${tutorId}" data-session="${s.session_id}" role="button" aria-expanded="${open}">`
            + `<span class="ntu-toggle${open ? " is-open" : ""}">▶</span>`
            + `<div class="ntu-class__date"><strong>${esc(s.session_day)} ${esc(s.session_date)}</strong><span>${esc(s.class_time)}</span></div>`
            + `<div class="ntu-class__what"><strong>${esc(s.module)}</strong>`
            + `<span>${esc([s.group, s.class_type, s.term].filter(Boolean).join(" · "))}</span>`
            + (teachers ? `<span>${teachers}</span>` : "") + "</div>"
            + '<div class="ntu-class__held"><span class="ntu-class__badges">'
            + `<span class="ntu-badge ${STATUS_CLASS[s.session_status] || ""}">${esc(s.session_status || "Unknown")}</span>`
            + `<span class="ntu-badge ntu-badge--mode ${s.class_mode === "Online" ? "is-online" : "is-physical"}">${esc(s.class_mode)}</span>`
            + `</span><span>${held}</span></div>`
            + '<div class="ntu-class__facts">'
            + `<span class="ntu-fact ntu-fact--main"><strong>${num(s.rows_changed)}</strong> of ${num(s.register_size)} rows changed</span>`
            + `<span class="ntu-fact">${[[s.single_changes, "single change"], [s.register_saves, "register saved again"], [s.same_day, "same day as first entry"]]
                .filter(([n]) => n > 0).map(([n, label]) => `${num(n)} ${label}`).join(" · ")}</span>`
            + (s.after_week > 0 ? `<span class="ntu-fact ntu-fact--alert">${num(s.after_week)} over 7 days after</span>` : "")
            + `<span class="ntu-fact ntu-fact--muted">Changed ${changedWhen}</span>`
            + "</div></div>";

        const body = open ? `<div class="ntu-class__body">${stateHtml(sessionState.get(s.session_id), classStudents)}</div>` : "";
        return `<div class="ntu-class${open ? " is-open" : ""}">${head}${body}</div>`;
    };

    const classStudents = (rows) => '<table class="ntu-mini"><colgroup>'
        + '<col style="width:24%"><col style="width:12%"><col style="width:17%"><col style="width:17%"><col style="width:18%"><col style="width:12%">'
        + "</colgroup><thead><tr><th>Student</th><th>Status now</th><th>How</th><th>Changed by (UK)</th><th>First entered</th><th>Note</th></tr></thead>"
        + `<tbody>${rows.map((r) => `<tr><td>${studentHtml(r)}</td><td>${esc(r.status)}</td><td>${esc(r.how)}</td>`
            + `<td>${changedHtml(r)}</td><td>${firstHtml(r)}</td><td>${esc(r.note)}</td></tr>`).join("")}</tbody></table>`;

    const stateHtml = (state, render) => (state === "loading" ? '<div class="ntu-sub-msg">Loading…</div>'
        : state === "error" ? '<div class="ntu-sub-msg">Could not load. Please try again.</div>'
        : render(state));

    const tutorClasses = (tutorId, sessions) => `<div class="ntu-classes">${sessions.map((s) => classCard(tutorId, s)).join("")}</div>`;

    const reformatTutor = (tutorId) => {
        const row = detailTable && detailTable.getRow(tutorId);
        if (row) row.reformat();
    };

    const toggleTutor = (row) => {
        const id = row.getData().tutor_id;
        if (tutorState.has(id)) {
            tutorState.delete(id);
            row.reformat();
            return;
        }

        const token = buildToken;
        tutorState.set(id, "loading");
        row.reformat();

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), group: "sessions", tutor_id: id } })
            .then((response) => { if (token === buildToken) { tutorState.set(id, response.data.data || []); reformatTutor(id); } })
            .catch(() => { if (token === buildToken) { tutorState.set(id, "error"); reformatTutor(id); } });
    };

    const toggleSession = (tutorId, sessionId) => {
        if (sessionState.has(sessionId)) {
            sessionState.delete(sessionId);
            reformatTutor(tutorId);
            return;
        }

        const token = buildToken;
        sessionState.set(sessionId, "loading");
        reformatTutor(tutorId);

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), session_id: sessionId } })
            .then((response) => { if (token === buildToken) { sessionState.set(sessionId, response.data.data || []); reformatTutor(tutorId); } })
            .catch(() => { if (token === buildToken) { sessionState.set(sessionId, "error"); reformatTutor(tutorId); } });
    };

    // A click on a class date card (inside an opened tutor) opens the students changed in it.
    detailTableEl.addEventListener("click", (event) => {
        const head = event.target.closest(".ntu-class__head");
        if (!head || event.target.closest("a") || !isTutorView()) return;
        toggleSession(Number(head.dataset.tutor), Number(head.dataset.session));
    });

    const roleTags = (d) => [
        d.tutor_classes ? `<span class="ntu-role">Tutor · ${classCount(d.tutor_classes)}</span>` : "",
        d.pt_classes ? `<span class="ntu-role ntu-role--pt">Personal tutor · ${classCount(d.pt_classes)}</span>` : "",
    ].join("");

    const buildTutorsTable = () => {
        const token = buildToken;
        detailTableEl.classList.add("is-grouped");
        detailCount.textContent = "Loading…";

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), group: "tutor" } })
            .then((response) => {
                if (token !== buildToken) return;
                tutorRows = response.data.data || [];
                const total = tutorRows.reduce((sum, t) => sum + t.rows_changed, 0);
                detailCount.textContent = `${num(tutorRows.length)} tutors / personal tutors · ${plural(total, "change")} · click a tutor for their class dates, then a class date for the students changed`;

                detailTable = new Tabulator(detailTableEl, {
                    data: tutorRows,
                    index: "tutor_id",
                    layout: "fitColumns",
                    virtualDom: false, // class dates open inside the row and change its height
                    pagination: "local",
                    paginationSize: 50,
                    paginationSizeSelector: [50, 100, 250],
                    placeholder: "No changes found",
                    initialSort: [{ column: "rows_changed", dir: "desc" }],
                    rowClick: (e, row) => {
                        if (e.target.closest("a") || e.target.closest(".ntu-sub-wrap")) return;
                        toggleTutor(row);
                    },
                    rowFormatter: (row) => {
                        const id = row.getData().tutor_id;
                        if (!tutorState.has(id)) return;
                        const wrap = document.createElement("div");
                        wrap.className = "ntu-sub-wrap ntu-sub-wrap--classes";
                        wrap.innerHTML = stateHtml(tutorState.get(id), (sessions) => tutorClasses(id, sessions));
                        row.getElement().appendChild(wrap);
                    },
                    columns: [
                        {
                            title: "", field: "tutor_id", width: 36, headerSort: false, hozAlign: "center",
                            formatter: (cell) => `<span class="ntu-toggle${tutorState.has(cell.getValue()) ? " is-open" : ""}">▶</span>`,
                        },
                        {
                            title: "Tutor / personal tutor", field: "name", minWidth: 180, widthGrow: 3, headerSort: true,
                            formatter: (cell) => {
                                const d = cell.getData();
                                const name = d.profile_url ? `<a href="${esc(d.profile_url)}" target="_blank" class="text-primary">${esc(d.name)}</a>` : esc(d.name);
                                return `<div class="ntu-staff"><div class="ntu-staff__name">${name}</div>`
                                    + `<div class="ntu-staff__meta">${esc([d.job_title, d.department].filter(Boolean).join(" · "))}</div>`
                                    + `<div class="ntu-roles">${roleTags(d)}</div></div>`;
                            },
                        },
                        numberColumn("Rows changed", "rows_changed"),
                        group("Affected", [
                            numberColumn("Class dates", "sessions"),
                            numberColumn("Classes", "classes"),
                            numberColumn("Students", "students"),
                        ]),
                        group("How changed", [
                            numberColumn("Single", "single_changes"),
                            numberColumn("Register", "register_saves"),
                        ]),
                        group("After class end", [
                            numberColumn("> 7 d", "after_week", true),
                        ]),
                        group("Change dates", [
                            dateColumn("First", "first_update"),
                            dateColumn("Last", "last_update"),
                        ]),
                    ],
                });
            })
            .catch((error) => { if (token === buildToken) detailCount.textContent = errorMessage(error); });
    };

    /* "Needs review" tab (red people only): the students whose absence this person changed
       to Present -> every class date of those classes, so a lone Present between absences
       and a rejected excuse show in context. */
    const pct = (value) => (value === null || value === undefined ? "" : `${Number(value).toLocaleString("en-GB", { maximumFractionDigits: 1 })}%`);
    const EXCUSE_TAG = { Rejected: "ntu-tag--amber", Approved: "ntu-tag--green", Pending: "" };

    const statusHtml = (r) => `<span class="${r.attended ? "ntu-st--in" : (r.absent ? "ntu-st--out" : "")}">${esc(r.status)}</span>`;

    const happenedHtml = (r) => {
        const tags = [];
        if (r.overturned_here) tags.push(`<span class="ntu-tag ntu-tag--red">Absent → Present by ${esc(r.changed_by)}</span>`);
        if (r.lone) tags.push('<span class="ntu-tag ntu-tag--red-soft">Lone Present: absent the class before and after</span>');
        if (r.overturned_by_other) tags.push(`<span class="ntu-tag">Absent → Present by ${esc(r.changed_by)}</span>`);
        if (r.excuse) tags.push(`<span class="ntu-tag ${EXCUSE_TAG[r.excuse.status] || ""}">Excuse ${esc(r.excuse.status.toLowerCase())}${r.excuse.type ? ` (${esc(r.excuse.type)})` : ""}</span>`);
        if (r.session_status && r.session_status !== "Completed") tags.push(`<span class="ntu-tag">Class ${esc(r.session_status.toLowerCase())}</span>`);

        const lines = [];
        if (r.excuse && r.excuse.reason) lines.push(`<div class="ntu-quote"><b>Student:</b> “${esc(r.excuse.reason)}”</div>`);
        if (r.excuse && (r.excuse.remarks || r.excuse.actioned_by)) {
            lines.push(`<div class="ntu-quote"><b>${esc(r.excuse.status)} by ${esc(r.excuse.actioned_by || "—")}${r.excuse.actioned_at ? ", " + esc(r.excuse.actioned_at) : ""}:</b> ${r.excuse.remarks ? `“${esc(r.excuse.remarks)}”` : "no reply"}</div>`);
        }
        if (r.note) lines.push(`<div class="ntu-quote"><b>Note:</b> ${esc(r.note)}</div>`);

        return (tags.length ? `<div class="ntu-tags">${tags.join("")}</div>` : "") + lines.join("");
    };

    const reviewTimelineHtml = (rows) => {
        const classes = new Map();
        rows.forEach((r) => {
            if (!classes.has(r.plan_id)) classes.set(r.plan_id, []);
            classes.get(r.plan_id).push(r);
        });

        return `<div class="ntu-tl">${[...classes.values()].map((list) => {
            const f = list[0];
            const total = list.length;
            const attended = list.filter((r) => r.attended).length;
            const here = list.filter((r) => r.overturned_here).length;
            const share = (n) => (total ? `${Math.round((1000 * n) / total) / 10}%` : "");
            const teachers = [["Tutor", f.class_tutor], ["Personal tutor", f.personal_tutor]].filter(([, n]) => n).map(([l, n]) => `${l}: ${esc(n)}`).join(" · ");

            return '<div class="ntu-tl__class"><div class="ntu-tl__head">'
                + `<div><strong>${esc(f.module)}</strong><span>${esc([f.group, f.class_type, f.class_mode, f.term].filter(Boolean).join(" · "))}</span>${teachers ? `<span>${teachers}</span>` : ""}</div>`
                + '<div class="ntu-tl__facts">'
                + `<span class="ntu-fact ntu-fact--alert">${plural(here, "absence")} changed to Present by ${esc(detailUser.name)}</span>`
                + `<span class="ntu-fact">Attended ${num(attended)} of ${num(total)} (${share(attended)})</span>`
                + `<span class="ntu-fact ntu-fact--main">Without these changes ${num(attended - here)} of ${num(total)} (${share(attended - here)})</span>`
                + "</div></div>"
                + '<table class="ntu-mini"><colgroup><col style="width:13%"><col style="width:11%"><col style="width:15%"><col style="width:15%"><col style="width:46%"></colgroup>'
                + "<thead><tr><th>Class date</th><th>Status now</th><th>First entered</th><th>Last changed</th><th>What happened</th></tr></thead><tbody>"
                + list.map((r) => `<tr class="${r.overturned_here ? "is-flagged" : ""}"><td>${sessionHtml(r)}</td><td>${statusHtml(r)}</td>`
                    + `<td>${firstHtml(r)}</td><td>${r.changed_by ? `<div class="ntu-cell"><div>${esc(r.changed_by)}</div><div class="ntu-cell__sub">${esc(r.changed_at)}</div></div>` : '<span class="ntu-cell__sub">Not changed</span>'}</td>`
                    + `<td>${happenedHtml(r)}</td></tr>`).join("")
                + "</tbody></table></div>";
        }).join("")}</div>`;
    };

    const toggleReview = (row) => {
        const id = row.getData().student_id;
        if (reviewState.has(id)) {
            reviewState.delete(id);
            row.reformat();
            return;
        }

        const token = buildToken;
        reviewState.set(id, "loading");
        row.reformat();

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), group: "review_timeline", student_id: id } })
            .then((response) => { if (token === buildToken) { reviewState.set(id, response.data.data || []); row.reformat(); } })
            .catch(() => { if (token === buildToken) { reviewState.set(id, "error"); row.reformat(); } });
    };

    const rateColumn = (title, field, alertBelow) => ({
        title, field, hozAlign: "right", headerHozAlign: "right", sorter: "number", headerSort: true, minWidth: 64, widthGrow: 1,
        formatter: (cell) => {
            const value = cell.getValue();
            return alertBelow && value !== null && value < alertBelow ? `<span class="ntu-num--alert">${pct(value)}</span>` : pct(value);
        },
    });

    const buildReviewTable = () => {
        const token = buildToken;
        detailTableEl.classList.add("is-grouped");
        detailCount.textContent = "Loading…";

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), group: "review" } })
            .then((response) => {
                if (token !== buildToken) return;
                reviewRows = response.data.data || [];
                const sum = (field) => reviewRows.reduce((total, s) => total + s[field], 0);
                detailCount.textContent = `${plural(reviewRows.length, "student")} · ${plural(sum("overturned"), "absence")} changed to Present`
                    + ` · ${num(sum("lone"))} lone · ${num(sum("after_rejected"))} after a rejected excuse · click a student for their attendance in those classes`;

                detailTable = new Tabulator(detailTableEl, {
                    data: reviewRows,
                    layout: "fitColumns",
                    virtualDom: false, // a student's class dates open inside the row and change its height
                    pagination: "local",
                    paginationSize: 50,
                    paginationSizeSelector: [50, 100, 250],
                    placeholder: "No absences changed to Present",
                    rowClick: (e, row) => {
                        if (e.target.closest("a") || e.target.closest(".ntu-sub-wrap")) return;
                        toggleReview(row);
                    },
                    rowFormatter: (row) => {
                        const d = row.getData();
                        row.getElement().classList.toggle("ntu-row--review", d.after_rejected > 0 || d.lone > 0);
                        if (!reviewState.has(d.student_id)) return;
                        const wrap = document.createElement("div");
                        wrap.className = "ntu-sub-wrap";
                        wrap.innerHTML = stateHtml(reviewState.get(d.student_id), reviewTimelineHtml);
                        row.getElement().appendChild(wrap);
                    },
                    columns: [
                        {
                            title: "", field: "student_id", width: 36, headerSort: false, hozAlign: "center",
                            formatter: (cell) => `<span class="ntu-toggle${reviewState.has(cell.getValue()) ? " is-open" : ""}">▶</span>`,
                        },
                        { title: "Student", field: "registration_no", minWidth: 150, widthGrow: 2.4, headerSort: true, formatter: (cell) => studentHtml(cell.getData()) },
                        numberColumn("Absences changed to Present", "overturned"),
                        numberColumn("Lone Presents", "lone", true),
                        numberColumn("After a rejected excuse", "after_rejected", true),
                        numberColumn("Classes", "classes"),
                        group("Attendance in these classes", [
                            rateColumn("Now", "rate_now"),
                            rateColumn("Without these changes", "rate_without", 80),
                        ]),
                        group("Change dates", [
                            dateColumn("First", "first_update"),
                            dateColumn("Last", "last_update"),
                        ]),
                    ],
                });
            })
            .catch((error) => { if (token === buildToken) detailCount.textContent = errorMessage(error); });
    };

    const exportReview = () => {
        const students = detailTable ? detailTable.getData("active") : reviewRows;
        setBusy(detailExportBtn, true, "Preparing…");

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), group: "review_timeline" } })
            .then((response) => {
                const title = "Non-Tutor Attendance Updates - needs review";
                const extra = [...filterRows(), ["Flagged because", detailUser.review_reasons.join(". ")], ["Needs review rule", current.meta.review_rule]];

                const summary = headerBlock(title, extra);
                summary.push(["Registration no", "Student", "Absences changed to Present", "Lone Presents", "Present after a rejected excuse", "Classes",
                    "Class dates in these classes", "Attended now", "Attendance now %", "Attendance without these changes %", "First change (UK)", "Last change (UK)"]);
                students.forEach((s) => summary.push([s.registration_no, s.student_name, s.overturned, s.lone, s.after_rejected, s.classes,
                    s.class_dates, s.attended, s.rate_now, s.rate_without, s.first_update, s.last_update]));

                // Same order as the window: student, then class, then class date.
                const rank = new Map(students.map((s, i) => [s.student_id, i]));
                const rows = (response.data.data || []).filter((r) => rank.has(r.student_id)).sort((a, b) =>
                    rank.get(a.student_id) - rank.get(b.student_id) || a.module.localeCompare(b.module) || a.plan_id - b.plan_id
                    || a.session_iso.localeCompare(b.session_iso) || a.class_time.localeCompare(b.class_time));
                const yes = (v) => (v ? "Yes" : "");
                const attendance = headerBlock(title, extra);
                attendance.push(["Registration no", "Student", "Module", "Group", "Class type", "Online / physical", "Term", "Class tutor", "Personal tutor",
                    "Class date", "Day", "Class time", "Class status", "Status now", "First entered by", "First entered (UK)", "Last changed by", "Last changed (UK)",
                    `Absent to Present by ${detailUser.name}`, "Lone Present (absent before and after)", "Absent to Present by someone else",
                    "Excuse", "Excuse type", "Student's reason", "Excuse decided by", "Excuse decided (UK)", "Reply to student", "Note"]);
                rows.forEach((r) => attendance.push([r.registration_no, r.student_name, r.module, r.group, r.class_type, r.class_mode, r.term, r.class_tutor, r.personal_tutor,
                    r.session_date, r.session_day, r.class_time, r.session_status, r.status, r.first_entered_by, r.first_entered_at, r.changed_by, r.changed_at,
                    yes(r.overturned_here), yes(r.lone), yes(r.overturned_by_other),
                    r.excuse ? r.excuse.status : "", r.excuse ? r.excuse.type : "", r.excuse ? r.excuse.reason : "", r.excuse ? r.excuse.actioned_by : "",
                    r.excuse ? r.excuse.actioned_at : "", r.excuse ? r.excuse.remarks : "", r.note]));

                writeBook([
                    { title: "Students", aoa: summary, widths: [15, 26, 14, 10, 14, 9, 12, 10, 12, 16, 17, 17] },
                    { title: "Attendance", aoa: attendance, widths: [15, 24, 32, 14, 10, 10, 14, 22, 22, 11, 5, 12, 11, 16, 22, 17, 22, 17, 16, 14, 16, 10, 8, 40, 20, 17, 40, 20] },
                ], `Non-tutor attendance updates - ${detailUser.name} - needs review - ${fileScope()}`);
            })
            .catch((error) => alert(errorMessage(error)))
            .finally(() => setBusy(detailExportBtn, false));
    };

    const buildDetails = () => {
        buildToken += 1;
        expanded.clear();
        tutorState.clear();
        sessionState.clear();
        reviewState.clear();
        studentRows = [];
        tutorRows = [];
        reviewRows = [];
        if (detailTable) {
            detailTable.destroy();
            detailTable = null;
        }
        detailCount.textContent = "";

        if (detailView === "students") buildStudentsTable();
        else if (isTutorView()) buildTutorsTable();
        else if (detailView === "review") buildReviewTable();
        else buildRowsTable();
    };

    const setView = (view) => {
        detailView = view;
        detailTabs.querySelectorAll(".ntu-tab").forEach((tab) => {
            const active = tab.dataset.view === view;
            tab.classList.toggle("is-active", active);
            tab.setAttribute("aria-pressed", active ? "true" : "false");
        });
        reviewBox.hidden = view !== "review";
    };

    detailTabs.addEventListener("click", (event) => {
        const tab = event.target.closest(".ntu-tab");
        if (!tab || tab.dataset.view === detailView) return;
        setView(tab.dataset.view);
        if (detailUser) buildDetails();
    });

    const openDetails = (row) => {
        detailUser = row;
        detailSearch.value = "";
        // A red (needs review) person opens on the "Needs review" tab, which only they have.
        reviewTab.hidden = !row.review;
        reviewBox.innerHTML = row.review
            ? "<strong>Why this person is flagged</strong><ul>" + row.review_reasons.map((r) => `<li>${esc(r)}</li>`).join("") + "</ul>"
                + "<p>These are patterns that need an explanation, not proof. Below: every student whose absence this person changed to Present (the register had recorded the absence), "
                + "most serious first. Click a student to see all their class dates in those classes, with any excuse they sent and the reply.</p>"
            : "";
        setView(row.review ? "review" : "rows");
        detailName.textContent = row.name;
        detailMeta.textContent = [row.job_title, row.department, current.meta.label].filter(Boolean).join(" · ");
        detailStats.innerHTML = [
            chip("Rows changed to Present", row.rows_updated),
            chip("Absences overturned", row.overturned),
            ...(row.review ? [chip("Lone Presents", row.lone, true), chip("After a rejected excuse", row.after_rejected, true)] : []),
            chip("Sessions", row.sessions),
            chip("Students", row.students),
            chip("Single changes", row.single_changes),
            chip("Over 7 days after class", row.after_week, true),
        ].join("");

        // The table is built once the modal is visible, so Tabulator can measure it.
        tailwind.Modal.getOrCreateInstance(modalEl).show();
    };

    modalEl.addEventListener("shown.tw.modal", () => {
        if (detailUser) buildDetails();
    });

    modalEl.addEventListener("hidden.tw.modal", () => {
        buildToken += 1;
        if (detailTable) {
            detailTable.destroy();
            detailTable = null;
        }
    });

    detailSearch.addEventListener("input", () => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => { if (detailUser) buildDetails(); }, 400);
    });

    /* Export the tab that is open. */
    const exportStudents = () => {
        const aoa = headerBlock("Non-Tutor Attendance Updates - by student", filterRows());
        aoa.push([
            "Registration no", "Student", "Rows changed to Present", "Sessions", "Classes", "Single changes", "Register saved again",
            "Over 7 days after class", "First change (UK)", "Last change (UK)",
        ]);
        const rows = detailTable ? detailTable.getData("active") : studentRows;
        rows.forEach((s) => aoa.push([
            s.registration_no, s.student_name, s.rows_changed, s.sessions, s.classes, s.single_changes, s.register_saves,
            s.after_week, s.first_update, s.last_update,
        ]));

        writeWorkbook(aoa, "By student", `Non-tutor attendance updates - ${detailUser.name} - by student - ${fileScope()}`,
            [15, 26, 14, 10, 10, 12, 12, 14, 17, 17]);
    };

    const exportChanges = () => {
        setBusy(detailExportBtn, true, "Preparing…");

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), all: 1 } })
            .then((response) => {
                const rows = response.data.data || [];
                const aoa = headerBlock("Non-Tutor Attendance Updates - details", filterRows());
                aoa.push([
                    "Session date", "Day", "Class time", "Term", "Course", "Module", "Group", "Class type", "Online / physical",
                    "Class tutor", "Personal tutor", "Cover tutor",
                    "Registration no", "Student", "Status now", "How",
                    "First entered by", "First entered (UK)", "Changed by", "Changed (UK)", "Hours after class end", "Note",
                ]);
                rows.forEach((r) => aoa.push([
                    r.session_date, r.session_day, r.class_time, r.term, r.course, r.module, r.group, r.class_type, r.class_mode,
                    r.class_tutor, r.personal_tutor, r.cover_tutor,
                    r.registration_no, r.student_name, r.status, r.how,
                    r.first_entered_by, r.first_entered_at, r.changed_by, r.updated_at,
                    r.minutes_after_class === null ? "" : Math.round(r.minutes_after_class / 6) / 10,
                    r.note,
                ]));

                writeWorkbook(aoa, "Changes", `Non-tutor attendance updates - ${detailUser.name} - ${fileScope()}`,
                    [12, 6, 12, 18, 28, 32, 14, 10, 10, 22, 22, 22, 15, 24, 14, 22, 22, 17, 22, 17, 10, 24]);
            })
            .catch((error) => alert(errorMessage(error)))
            .finally(() => setBusy(detailExportBtn, false));
    };

    const exportTutors = () => {
        const none = "No tutor or personal tutor on the class";
        const tutors = detailTable ? detailTable.getData("active") : tutorRows;
        setBusy(detailExportBtn, true, "Preparing…");

        axios.get(route("reports.non.tutor.attendance.details"), { params: { ...detailParams(), all: 1 } })
            .then((response) => {
                const title = "Non-Tutor Attendance Updates - by tutor / personal tutor";
                const roles = (t) => [t.tutor_classes ? `Tutor (${classCount(t.tutor_classes)})` : "", t.pt_classes ? `Personal tutor (${classCount(t.pt_classes)})` : ""].filter(Boolean).join(", ");

                const summary = headerBlock(title, filterRows());
                summary.push(["Tutor / personal tutor", "Role", "Job title", "Rows changed to Present", "Class dates", "Classes", "Students", "Single changes", "Register saved again", "Over 7 days after class", "First change (UK)", "Last change (UK)"]);
                tutors.forEach((t) => summary.push([t.name, roles(t), [t.job_title, t.department].filter(Boolean).join(" · "), t.rows_changed, t.sessions, t.classes, t.students, t.single_changes, t.register_saves, t.after_week, t.first_update, t.last_update]));

                // Every change, in the same order as the window: tutor, then newest class date, then student.
                const rank = new Map(tutors.map((t, i) => [t.name, i]));
                const teacherOf = (r) => r.class_tutor || r.personal_tutor || none;
                const rows = (response.data.data || []).slice().sort((a, b) =>
                    (rank.get(teacherOf(a)) ?? 1e9) - (rank.get(teacherOf(b)) ?? 1e9)
                    || b.session_iso.localeCompare(a.session_iso)
                    || a.class_time.localeCompare(b.class_time)
                    || a.registration_no.localeCompare(b.registration_no));
                const changes = headerBlock(title, filterRows());
                changes.push(["Tutor / personal tutor", "Role", "Class date", "Day", "Class time", "Module", "Group", "Class type", "Online / physical", "Term", "Class tutor", "Personal tutor", "Cover tutor",
                    "Registration no", "Student", "Status now", "How", "Changed by", "Changed (UK)", "Hours after class end", "First entered by", "First entered (UK)", "Note"]);
                rows.forEach((r) => changes.push([teacherOf(r), r.class_tutor ? "Tutor" : (r.personal_tutor ? "Personal tutor" : ""), r.session_date, r.session_day, r.class_time, r.module, r.group, r.class_type, r.class_mode, r.term,
                    r.class_tutor, r.personal_tutor, r.cover_tutor, r.registration_no, r.student_name, r.status, r.how, r.changed_by, r.updated_at,
                    r.minutes_after_class === null ? "" : Math.round(r.minutes_after_class / 6) / 10, r.first_entered_by, r.first_entered_at, r.note]));

                writeBook([
                    { title: "By tutor", aoa: summary, widths: [28, 34, 30, 14, 11, 10, 10, 12, 12, 14, 17, 17] },
                    { title: "Changes", aoa: changes, widths: [26, 14, 12, 6, 12, 32, 14, 10, 10, 16, 22, 22, 22, 15, 24, 14, 22, 22, 17, 10, 22, 17, 24] },
                ], `Non-tutor attendance updates - ${detailUser.name} - by tutor - ${fileScope()}`);
            })
            .catch((error) => alert(errorMessage(error)))
            .finally(() => setBusy(detailExportBtn, false));
    };

    detailExportBtn.addEventListener("click", () => {
        if (!detailUser) return;
        if (isTutorView()) { exportTutors(); return; }
        if (detailView === "review") { exportReview(); return; }
        if (detailView === "students") exportStudents();
        else exportChanges();
    });
})();
