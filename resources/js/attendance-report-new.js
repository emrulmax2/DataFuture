import TomSelect from "tom-select";
import { createIcons, icons } from "lucide";

/*
 * Reports › Attendance Reports, "New version" tab (user's design, 24 Nov 2026).
 *
 * - Filters: the old version's six, as Tom Selects whose options follow each other through the
 *   old endpoint (student.get.all.student.type). Intake or attendance semester is required.
 * - Results (AttendanceReportNewController::list): one page of students, the band counts and
 *   a label. Search asks for fresh rows; bands, sorting, paging reuse the search's rows.
 * - Ticked students (kept across pages) get the old version's Send SMS / Send Email /
 *   Generate Letter: the page's shared modals, sent by the old script's handlers, so this
 *   tab only fills in student_ids. Plus Excel of the search or of the ticked students.
 */
(function () {
    const root = document.querySelector("[data-atr]");
    if (!root) return;

    const $ = (sel, el = root) => el.querySelector(sel);
    const $$ = (sel, el = root) => Array.from(el.querySelectorAll(sel));
    const config = JSON.parse($("script[data-atr-config]").textContent);
    const csrf = () => ({ "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content") });
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const fmt = (n) => Number(n || 0).toLocaleString("en-GB");
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });

    const LOW = config.bands.low;
    const MID = config.bands.mid;
    const HIGH = config.bands.high;
    const band = (p) => (p < LOW ? "low" : p < MID ? "warn" : p < HIGH ? "mid" : "high");
    const BAND_NAMES = { low: "Very low", warn: "Low", mid: "Fair", high: "Good" };
    // "0%" (user, 24 Nov 2026): no attendance counted at all; these students are in "Below 50%" too.
    const CHIPS = [
        ["all", "All students", "var(--atr-primary)"],
        ["zero", "0%", "var(--atr-zero)"],
        ["low", `Below ${LOW}%`, "var(--atr-low)"],
        ["warn", `${LOW}–${MID - 1}%`, "var(--atr-warn)"],
        ["mid", `${MID}–${HIGH - 1}%`, "var(--atr-mid)"],
        ["high", `${HIGH}% and above`, "var(--atr-high)"],
    ];
    // Positions in each row's counts (see StudentAttendanceReport::COUNTS).
    const idx = Object.fromEntries(config.counts.map((c, i) => [c.key, i]));

    const els = {
        form: $("[data-atr-form]"),
        error: $("[data-atr-error]"),
        search: $("[data-atr-search]"),
        percent: $("[data-atr-percent]"),
        summary: $("[data-atr-summary]"),
        exportBtn: $("[data-atr-export]"),
        start: $("[data-atr-start]"),
        report: $("[data-atr-report]"),
        chips: $("[data-atr-chips]"),
        quick: $("[data-atr-quick]"),
        bulk: $("[data-atr-bulk]"),
        bulkCount: $("[data-atr-bulk-count]"),
        all: $("[data-atr-all]"),
        body: $("[data-atr-body]"),
        empty: $("[data-atr-empty]"),
        loading: $("[data-atr-loading]"),
        scroll: $("[data-atr-scroll]"),
        shown: $("[data-atr-shown]"),
        total: $("[data-atr-total]"),
        size: $("[data-atr-size]"),
        pager: $("[data-atr-pager]"),
    };

    /* ── Version switch ── */
    $$("[data-atr-version]").forEach((button) =>
        button.addEventListener("click", () => {
            const name = button.dataset.atrVersion;
            $$("[data-atr-version]").forEach((b) => {
                b.classList.toggle("is-active", b === button);
                b.setAttribute("aria-pressed", b === button ? "true" : "false");
            });
            $$("[data-atr-pane]").forEach((pane) => (pane.hidden = pane.dataset.atrPane !== name));
            $$("[data-atr-new-only]").forEach((el) => (el.hidden = name !== "new"));
            // The line under the title: this tab's search summary, else the page description.
            if (name === "new" && state.bands) renderSummary();
            else els.summary.textContent = els.summary.dataset.default;
            // The old Tabulator measures itself; it redraws on resize.
            if (name === "old") window.dispatchEvent(new Event("resize"));
        })
    );

    /* ── Filters ── */
    const FILTERS = ["intake_semester", "attendance_semester", "course", "group", "group_student_status"];
    const pickers = {};
    const original = {};
    let quiet = false; // programmatic changes (Reset) must not ask for new options

    // The design shows each filter as a plain select: the box reads "All intakes", or the
    // chosen value ("2026 September", "2026 September +2 more"); the list ticks the choices.
    const markSet = (key) => {
        const picker = pickers[key];
        const names = picker.items.map((v) => (picker.options[v] ? picker.options[v].text : v));
        const summary = !names.length ? picker.input.dataset.placeholder || "" : names.length === 1 ? names[0] : `${names[0]} +${names.length - 1} more`;
        picker.control.setAttribute("data-summary", summary);
        picker.control.title = names.join(", ");
        picker.wrapper.classList.toggle("is-set", names.length > 0);
    };
    const markPercent = () => els.percent.classList.toggle("is-set", els.percent.value !== "");

    FILTERS.forEach((key) => {
        const select = $(`[data-atr-filter="${key}"]`);
        original[key] = Array.from(select.options).filter((o) => o.value !== "").map((o) => ({ value: o.value, text: o.text }));
        pickers[key] = new TomSelect(select, {
            plugins: { dropdown_input: {}, checkbox_options: {} },
            dropdownParent: "body",
            dropdownClass: "ts-dropdown atr-ts-dropdown",
            maxOptions: null,
            create: false,
            placeholder: "Search…", // the list's search box; the field itself reads data-summary
            // Ticks follow the choice even when it changed in code (Reset, refreshed options).
            onDropdownOpen(dropdown) {
                dropdown.querySelectorAll(".option").forEach((option) => {
                    const box = option.querySelector("input[type=checkbox]");
                    if (box) box.checked = option.classList.contains("selected");
                });
            },
            onChange() {
                markSet(key);
                els.error.hidden = true;
                if (!quiet) refreshOptions(key);
            },
        });
        markSet(key);
    });
    pickers.group.disable();
    els.percent.addEventListener("change", markPercent);

    const filterValues = () => {
        const values = {};
        FILTERS.forEach((key) => (values[key] = [].concat(pickers[key].getValue() || []).filter(Boolean)));
        values.attendance_percentage = els.percent.value;
        return values;
    };

    const replaceOptions = (key, list) => {
        const picker = pickers[key];
        picker.clearOptions(); // keeps the chosen items
        list.forEach((o) => {
            if (!picker.options[o.value]) picker.addOption(o);
        });
        picker.refreshOptions(false);
    };

    // The other filters offer only what the current choice leaves (old version's endpoint).
    let optionSeq = 0;
    function refreshOptions(changed) {
        const values = filterValues();
        const seq = ++optionSeq;
        if (!FILTERS.some((key) => values[key].length)) {
            FILTERS.forEach((key) => replaceOptions(key, original[key]));
            pickers.group.disable();
            return;
        }
        axios
            .post(
                route(config.routes.options),
                {
                    academic_years: "",
                    term_declaration_ids: values.attendance_semester,
                    intake_semesters: values.intake_semester,
                    courses: values.course,
                    groups: values.group,
                    group_student_statuses: values.group_student_status,
                    student_types: [],
                    evening_weekends: "",
                },
                { headers: csrf() }
            )
            .then(({ data }) => {
                if (seq !== optionSeq) return;
                const res = (data && data.res) || {};
                FILTERS.forEach((key) => {
                    if (key === changed) return;
                    replaceOptions(key, Object.values(res[key] || {}).map((row) => ({ value: String(row.id), text: String(row.name) })));
                });
                pickers.group.enable();
            })
            .catch(() => {});
    }

    els.form.addEventListener("reset", (e) => {
        e.preventDefault();
        quiet = true;
        FILTERS.forEach((key) => {
            pickers[key].clear();
            replaceOptions(key, original[key]);
            markSet(key);
        });
        quiet = false;
        optionSeq++;
        pickers.group.disable();
        els.percent.value = "";
        markPercent();
        els.error.hidden = true;
    });

    /* ── Report state ── */
    // Search loads every student once (all); the band, sort and page are worked out here, no requests.
    const state = {
        filters: null,
        all: [],
        band: "all",
        sort: "name",
        dir: "asc",
        page: 1,
        size: 50,
        rows: [], // the current page
        total: 0, // students in the current band
        bands: null,
        lastPage: 1,
        label: "",
        sel: new Set(),
        seq: 0,
    };

    const inBand = (r, key) => key === "all" || (key === "zero" ? r.pct === 0 : band(r.pct) === key);
    const sortValue = (key) => {
        if (key === "name") return (r) => r.name.toLowerCase();
        if (key === "group") return (r) => (r.group || "").toLowerCase();
        if (key === "pct" || key === "wo") return (r) => r[key];
        const i = idx[key];
        return (r) => r.n[i];
    };

    // Band → sort → page, from the rows the search loaded.
    function view() {
        const value = sortValue(state.sort);
        const sign = state.dir === "desc" ? -1 : 1;
        const rows = state.all.filter((r) => inBand(r, state.band));
        rows.sort((a, b) => {
            const x = value(a);
            const y = value(b);
            return x < y ? -sign : x > y ? sign : a.id - b.id;
        });
        state.total = rows.length;
        state.lastPage = Math.max(1, Math.ceil(rows.length / state.size));
        state.page = Math.min(Math.max(1, state.page), state.lastPage);
        state.rows = rows.slice((state.page - 1) * state.size, state.page * state.size);
        render();
    }

    const busy = (button, on) => {
        if (!button) return;
        button.classList.toggle("is-busy", on);
        button.disabled = on;
    };

    // The only request: every student of the search, with the band counts and the label.
    function load() {
        const seq = ++state.seq;
        els.loading.hidden = false;
        busy(els.search, true);
        axios
            .get(route(config.routes.list), { params: { ...state.filters } })
            .then(({ data }) => {
                if (seq !== state.seq) return;
                Object.assign(state, { all: data.rows || [], bands: data.bands, label: data.label || "" });
                view();
            })
            .catch((err) => {
                if (seq !== state.seq) return;
                const message = (err.response && err.response.data && err.response.data.message) || "The report could not be loaded. Please try again.";
                showError(message);
            })
            .finally(() => {
                if (seq !== state.seq) return;
                els.loading.hidden = true;
                busy(els.search, false);
            });
    }

    function showError(message) {
        els.error.textContent = message;
        els.error.hidden = false;
    }

    els.form.addEventListener("submit", (e) => {
        e.preventDefault();
        const values = filterValues();
        if (!values.intake_semester.length && !values.attendance_semester.length) {
            showError("Choose an Intake Semester or an Attendance Semester, then press Search.");
            return;
        }
        els.error.hidden = true;
        state.filters = values;
        state.band = "all";
        state.page = 1;
        state.sel.clear();
        els.quick.value = "";
        els.start.hidden = true;
        els.report.hidden = false;
        load();
    });

    /* ── Rendering ── */
    const badge = (s) =>
        "atr-badge--" + (/discard/i.test(s) ? "discarded" : /regist/i.test(s) ? "registered" : /extend/i.test(s) ? "extended" : /enrol/i.test(s) ? "enrolled" : "other");

    function visibleRows() {
        const q = els.quick.value.trim().toLowerCase();
        if (!q) return state.rows;
        return state.rows.filter((r) => [r.reg, r.name, r.group, r.course, r.status, r.ssn, r.ab_ref, r.mobile, r.code, r.intake].join(" ").toLowerCase().includes(q));
    }

    function attLine(label, pct, attended, classes) {
        const k = band(pct);
        const tip = `${attended} of ${classes} classes counted — ${BAND_NAMES[k]}`;
        return `<div class="atr-att-row" title="${esc(tip)}"><span>${label}</span><div class="atr-bar" aria-hidden="true"><i class="is-${k}" style="width:${Math.max(Math.min(pct, 100), 1.5)}%"></i></div><b class="is-${k}">${pct.toFixed(2)}%</b></div>`;
    }

    function rowHtml(r) {
        const on = state.sel.has(r.id);
        const n = r.n;
        const tc = n[idx.TC];
        const withExcuse = n[idx.P] + n[idx.O] + n[idx.L] + n[idx.E] + n[idx.M] + n[idx.H];
        const without = n[idx.P] + n[idx.O] + n[idx.L];
        return `<tr class="${on ? "is-sel" : ""}">
            <td><input type="checkbox" data-id="${r.id}" ${on ? "checked" : ""} aria-label="Select ${esc(r.name)}"></td>
            <td><div class="atr-stu">
                <span><a href="${esc(route("student.show", r.id))}" target="_blank" rel="noopener" title="${esc(r.name)}">${esc(r.name)}</a>${r.flag ? '<span class="atr-flag" title="Active flagged note">⚑ Flagged</span>' : ""}</span>
                <span class="atr-sub">${esc(r.reg)} · <span class="atr-badge ${badge(r.status)}">${esc(r.status || "No status")}</span></span>
                <span class="atr-sub">${esc(r.mobile || "—")}</span>
            </div></td>
            <td><div class="atr-course" title="${esc(r.course)}">${esc(r.course || "—")}</div>
                <div class="atr-sub">${esc(r.group || "—")}</div>
                <div class="atr-kv"><span>Intake</span>${esc(r.intake || "—")}</div></td>
            <td><div class="atr-kv"><span>Code</span>${esc(r.code || "—")}</div>
                <div class="atr-kv"><span>A.B. Ref</span>${esc(r.ab_ref || "—")}</div>
                <div class="atr-kv"><span>SSN</span>${esc(r.ssn || "—")}</div></td>
            ${n.map((v) => `<td class="atr-num${v ? "" : " is-zero"}">${v ? fmt(v) : "–"}</td>`).join("")}
            <td class="atr-att">${attLine("With excuse", r.pct, withExcuse, tc)}${attLine("W/O excuse", r.wo, without, tc)}</td>
        </tr>`;
    }

    function renderBody() {
        const rows = visibleRows();
        els.body.innerHTML = rows.map(rowHtml).join("");
        let message = "";
        if (!rows.length) {
            if (!state.bands || !state.bands.all) message = "No students match this search.";
            else if (!state.rows.length) message = "No students in this attendance band.";
            else message = "No students on this page match the filter.";
        }
        els.empty.textContent = message;
        els.empty.hidden = !message;
        els.all.disabled = !rows.length;
        syncTicks(rows);
    }

    // Header box and selection bar only: a tick must not redraw the rows (focus, speed).
    function syncTicks(rows = visibleRows()) {
        const ticked = rows.filter((r) => state.sel.has(r.id)).length;
        els.all.checked = rows.length > 0 && ticked === rows.length;
        els.all.indeterminate = ticked > 0 && ticked < rows.length;
        renderBulk();
    }

    function renderBulk() {
        const count = state.sel.size;
        els.bulk.hidden = count === 0;
        els.bulkCount.textContent = `${fmt(count)} selected`;
        els.bulkCount.title = `${fmt(count)} student${count === 1 ? "" : "s"} ticked, on any page`;
    }

    function renderChips() {
        els.chips.innerHTML = CHIPS.map(([key, label, colour]) => {
            const count = state.bands ? state.bands[key] : 0;
            return `<button type="button" class="atr-chip" data-band="${key}" aria-pressed="${state.band === key}" style="--c:${colour}"><i></i>${esc(label)} <small>${fmt(count)}</small></button>`;
        }).join("");
    }

    function renderSort() {
        $$("thead th", root).forEach((th) => {
            const button = th.querySelector("[data-sort]");
            if (!button) return;
            if (button.dataset.sort === state.sort) th.setAttribute("aria-sort", state.dir === "asc" ? "ascending" : "descending");
            else th.removeAttribute("aria-sort");
        });
    }

    function renderFoot() {
        const from = state.total ? (state.page - 1) * state.size + 1 : 0;
        const to = Math.min(state.page * state.size, state.total);
        els.shown.textContent = state.total ? `${fmt(from)}–${fmt(to)}` : "0";
        els.total.textContent = fmt(state.total);
        els.size.value = String(state.size);

        const last = state.lastPage;
        const cur = state.page;
        let start = Math.max(1, cur - 2);
        const end = Math.min(last, start + 4);
        start = Math.max(1, end - 4);
        const button = (label, page, aria, disabled, current) =>
            `<button type="button" data-page="${page}"${aria ? ` aria-label="${aria}"` : ""}${disabled ? " disabled" : ""}${current ? ' aria-current="page"' : ""}>${label}</button>`;
        let html = button("«", 1, "First page", cur === 1) + button("‹", cur - 1, "Previous page", cur === 1);
        for (let p = start; p <= end; p++) html += button(fmt(p), p, `Page ${p}`, false, p === cur);
        html += button("›", cur + 1, "Next page", cur === last) + button("»", last, "Last page", cur === last);
        els.pager.innerHTML = html;
    }

    function renderSummary() {
        const all = state.bands ? state.bands.all : 0;
        els.summary.innerHTML = `${esc(state.label)} · <b>${fmt(all)}</b> student${all === 1 ? "" : "s"}`;
        els.exportBtn.disabled = all === 0;
    }

    function render() {
        renderSummary();
        renderChips();
        renderSort();
        renderBody();
        renderFoot();
    }

    /* ── Interaction ── */
    root.addEventListener("click", (e) => {
        const chip = e.target.closest("[data-band]");
        if (chip) {
            if (chip.dataset.band === state.band) return;
            state.band = chip.dataset.band;
            state.page = 1;
            view();
            return;
        }
        const sort = e.target.closest("[data-sort]");
        if (sort && state.bands) {
            const key = sort.dataset.sort;
            state.dir = state.sort === key && state.dir === "asc" ? "desc" : "asc";
            state.sort = key;
            state.page = 1;
            view();
            return;
        }
        const page = e.target.closest("[data-page]");
        if (page && !page.disabled) {
            state.page = Number(page.dataset.page);
            view();
            els.report.scrollIntoView({ block: "start", behavior: "smooth" });
            return;
        }
        const act = e.target.closest("[data-atr-act]");
        if (act) runAction(act.dataset.atrAct, act);
    });

    els.size.addEventListener("change", () => {
        state.size = Number(els.size.value);
        state.page = 1;
        view();
    });
    els.quick.addEventListener("input", renderBody);

    root.addEventListener("change", (e) => {
        if (e.target === els.all) {
            const on = els.all.checked;
            visibleRows().forEach((r) => (on ? state.sel.add(r.id) : state.sel.delete(r.id)));
            $$("[data-atr-body] input[data-id]").forEach((box) => {
                box.checked = on;
                box.closest("tr").classList.toggle("is-sel", on);
            });
            syncTicks();
        } else if (e.target.matches("input[type=checkbox][data-id]")) {
            const id = Number(e.target.dataset.id);
            if (e.target.checked) state.sel.add(id);
            else state.sel.delete(id);
            e.target.closest("tr").classList.toggle("is-sel", e.target.checked);
            syncTicks();
        }
    });

    /* ── Ticked students: the page's shared SMS / Email / Letter dialogs ── */
    const MODALS = { sms: "#sendBulkSmsModal", email: "#sendBulkMailModal", letter: "#generateBulkLetterModal" };

    function openModal(selector) {
        const el = document.querySelector(selector);
        if (!el || !window.tailwind) return;
        const ids = Array.from(state.sel).join(",");
        const field = el.querySelector('[name="student_ids"]');
        // The old version's buttons leave "shown" listeners holding their own selection; ours runs after them.
        el.addEventListener("shown.tw.modal", () => (field.value = ids), { once: true });
        field.value = ids;
        tailwind.Modal.getOrCreateInstance(el).show();
    }

    function runAction(action, button) {
        if (MODALS[action]) {
            if (state.sel.size) openModal(MODALS[action]);
        } else if (action === "export-selected") {
            if (state.sel.size) exportExcel(button, Array.from(state.sel));
        } else if (action === "clear") {
            state.sel.clear();
            $$("[data-atr-body] input[data-id]").forEach((box) => {
                box.checked = false;
                box.closest("tr").classList.remove("is-sel");
            });
            syncTicks();
        }
    }

    /* ── Excel ── */
    function exportExcel(button, studentIds) {
        if (!state.filters) return;
        busy(button, true);
        axios
            .post(
                route(config.routes.export),
                // Ticked students go out whatever the band; the search goes out as shown.
                { ...state.filters, band: studentIds.length ? "all" : state.band, sort: state.sort, dir: state.dir, student_ids: studentIds },
                { headers: csrf(), responseType: "blob" }
            )
            .then((response) => {
                const disposition = response.headers["content-disposition"] || "";
                const match = /filename\*?=(?:UTF-8'')?"?([^";]+)"?/i.exec(disposition);
                const name = match ? decodeURIComponent(match[1]) : "Attendance_Report.xlsx";
                const url = URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement("a");
                link.href = url;
                link.download = name;
                document.body.appendChild(link);
                link.click();
                link.remove();
                setTimeout(() => URL.revokeObjectURL(url), 1000);
            })
            .catch(() => showError("The Excel file could not be made. Please try again."))
            .finally(() => busy(button, false));
    }
    els.exportBtn.addEventListener("click", () => exportExcel(els.exportBtn, []));

    /* ── Page dialogs (redesigned, shared with the old version) ── */
    // Their pickers are the old script's Tom Selects: give their lists this report's look.
    ["#sms_template_id", "#email_template_id", "#letter_set_id"].forEach((sel) => {
        const el = document.querySelector(sel);
        if (el && el.tomselect) el.tomselect.dropdown.classList.add("atr-ts-dropdown");
    });
    // "To 3 selected students": counted once every "shown" listener (old ones too) has filled student_ids.
    [
        ["sendBulkSmsModal", "To"],
        ["sendBulkMailModal", "To"],
        ["generateBulkLetterModal", "For"],
    ].forEach(([id, word]) => {
        const el = document.getElementById(id);
        const sub = el && el.querySelector("[data-atr-modal-to]");
        if (!sub) return;
        el.addEventListener("shown.tw.modal", () =>
            setTimeout(() => {
                const count = (el.querySelector('[name="student_ids"]').value || "").split(",").filter(Boolean).length;
                sub.textContent = count ? `${word} ${fmt(count)} selected student${count === 1 ? "" : "s"}` : `${word} the selected students`;
            }, 0)
        );
    });

    /*
     * New and old versions search together (user, 24 Nov 2026, as on Intake Performance): the
     * filters follow each other between the tabs, and Search in either tab also runs the other
     * version in its hidden tab. The old script is driven only through its Tom Selects, its
     * percentage select and its Search button.
     */
    const OLD = {
        intake_semester: "#intake_semester",
        attendance_semester: "#attendance_semester",
        course: "#course",
        group: "#group",
        group_student_status: "#group_student_status",
    };
    const oldPercent = document.querySelector("#attendance_percentage");
    const oldSearch = document.querySelector("#studentGroupSearchBtn");
    const valuesOf = (ts) => [].concat(ts.getValue() || []).map(String).filter(Boolean);
    const same = (a, b) => a.length === b.length && a.every((v) => b.includes(v));
    const copyPicker = (from, to) => {
        const values = valuesOf(from);
        if (same(values, valuesOf(to))) return; // also stops the two echoing each other
        values.forEach((v) => {
            if (!to.options[v]) to.addOption({ value: v, text: from.options[v] ? from.options[v].text : v });
        });
        if (values.length && to.isDisabled) to.enable();
        to.setValue(values);
    };
    const oldPicker = (key) => {
        const el = document.querySelector(OLD[key]);
        return el && el.tomselect;
    };
    const copyAll = (toOld) => {
        FILTERS.forEach((key) => {
            const old = oldPicker(key);
            if (old) toOld ? copyPicker(pickers[key], old) : copyPicker(old, pickers[key]);
        });
        if (!oldPercent) return;
        if (toOld) oldPercent.value = els.percent.value;
        else {
            els.percent.value = oldPercent.value;
            markPercent();
        }
    };

    if (oldSearch) {
        FILTERS.forEach((key) => {
            const old = oldPicker(key);
            if (!old) return;
            pickers[key].input.addEventListener("change", () => copyPicker(pickers[key], old));
            old.input.addEventListener("change", () => copyPicker(old, pickers[key]));
        });
        els.percent.addEventListener("change", () => oldPercent && (oldPercent.value = els.percent.value));
        if (oldPercent) {
            oldPercent.addEventListener("change", () => {
                els.percent.value = oldPercent.value;
                markPercent();
            });
        }

        // A search started by the other tab must not bounce back to it.
        let mirroring = false;
        const hasSemester = (values) => values.intake_semester.length > 0 || values.attendance_semester.length > 0;

        els.form.addEventListener("submit", () => {
            if (mirroring || !hasSemester(filterValues())) return;
            copyAll(true);
            mirroring = true;
            try {
                oldSearch.click();
            } finally {
                mirroring = false;
            }
        });
        oldSearch.addEventListener("click", () => {
            if (mirroring) return;
            const old = (key) => (oldPicker(key) ? valuesOf(oldPicker(key)) : []);
            if (!old("intake_semester").length && !old("attendance_semester").length) return;
            copyAll(false);
            mirroring = true;
            try {
                els.form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
            } finally {
                mirroring = false;
            }
        });
    }

    paintIcons();
})();
