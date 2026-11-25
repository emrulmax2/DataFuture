import xlsx from "xlsx";
import TomSelect from "tom-select";
import { createIcons, icons } from "lucide";

/*
 * Reports › Term Performance › Retention, "New version" (user's term-retention-report design,
 * 25 Nov 2026). Generate asks once (RetentionByIntakeController::generate); Download PDF and
 * Export Excel show after it. Counts open their students (RetentionByIntakeController::students):
 * the dialog searches, pages and exports them in the browser. As the other new reports, the old
 * version follows: the same terms, and Generate runs both.
 */
(function () {
    const root = document.querySelector("[data-trt]");
    if (!root) return;

    const $ = (sel, el = root) => el.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const n = (v) => Number(v || 0).toLocaleString("en-GB");
    const pct = (r) => (r === null || r === undefined ? "–" : Number(r).toFixed(1) + "%");
    const ORD = (i) => i + (i % 100 >= 11 && i % 100 <= 13 ? "th" : ["th", "st", "nd", "rd"][i % 10] || "th");
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });
    const KEY = { total: "Total students", registered: "Registered", stayed: "Stayed", dropped: "Dropped out" };
    const OUTCOME = { dropped: ["Dropped out", "var(--trt-orange)"], stayed: ["Stayed", "var(--trt-teal)"], none: ["Not registered", "#c3c9d1"] };

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
    const form = $("[data-trt-form]");
    const select = $("[data-trt-terms]");
    const error = $("[data-trt-error]");
    const button = $("[data-trt-generate]");
    const pdf = $("[data-trt-pdf]");
    const excel = $("[data-trt-excel]");
    const result = $("[data-trt-result]");
    const picker = new TomSelect(select, {
        plugins: { dropdown_input: {}, remove_button: { title: "Remove this term" } },
        placeholder: "Search here...",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown tar-ts-dropdown",
        maxOptions: null,
        create: false,
    });
    const chosen = () => [].concat(picker.getValue() || []).map(String).filter(Boolean);

    let data = null;
    let seq = 0;
    const busy = (on) => {
        button.classList.toggle("is-busy", on);
        button.disabled = on;
    };
    const downloads = () => {
        const ready = !!data && data.terms.length > 0;
        pdf.hidden = excel.hidden = !ready;
        if (ready) {
            const terms = data.terms.map((t) => t.id).join("_");
            pdf.setAttribute("href", route(root.dataset.pdf, { terms }));
            excel.setAttribute("href", route(root.dataset.excel, { terms }));
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
        const terms = chosen();
        error.textContent = "";
        if (!terms.length) {
            error.textContent = "Please select at least one attendance term.";
            return;
        }
        const mine = ++seq;
        busy(true);
        result.innerHTML = "";
        data = null;
        downloads();
        axios
            .get(route(root.dataset.generate, { terms: terms.join("_") }))
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
    // A count that opens its students; zero stays text.
    const link = (v, term, intake, key) => (v > 0 ? `<button type="button" class="trt-num" data-trt-list="${key}" data-term="${term}" data-intake="${intake}">${n(v)}</button>` : n(v));

    // "Retention by term of study", drawn at the box's width so the text keeps its size
    // (as TermRetentionChart for the PDF: axis from 80% unless lower; values with at most two lines).
    function chart(W) {
        const S = data.series.map((s) => ({ ...s, pts: Object.entries(s.points).map(([k, v]) => [Number(k), Number(v)]).sort((a, b) => a[0] - b[0]) }));
        const all = S.flatMap((s) => s.pts);
        const N = Math.max(2, ...all.map((p) => p[0]));
        const lo = Math.max(0, Math.min(80, Math.floor((Math.min(...all.map((p) => p[1])) - 1) / 5) * 5));
        const hi = 100;
        const step = hi - lo > 30 ? 10 : 5;
        const H = 250;
        const L = 46;
        const R = 16;
        const T = 22;
        const B = 44;
        const PADX = W < 560 ? 22 : 44;
        const x = (i) => L + PADX + ((i - 1) * (W - L - R - 2 * PADX)) / (N - 1);
        const y = (v) => T + ((hi - v) * (H - T - B)) / (hi - lo);
        const labels = S.length <= 2;
        let g = `<svg width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" role="img" aria-label="Retention rate by term of study, one line per term. The figures are in the table below.">`;
        for (let v = lo; v <= hi; v += step) g += `<line x1="${L}" x2="${W - R}" y1="${y(v)}" y2="${y(v)}" stroke="#e3e7ea"/><text x="${L - 8}" y="${y(v) + 4}" text-anchor="end" font-size="12" fill="#55616b">${v}%</text>`;
        for (let i = 1; i <= N; i++) {
            const sub = i === 1 ? "newest intake" : i === N ? "oldest intake" : "";
            g += `<text x="${x(i)}" y="${H - 24}" text-anchor="middle" font-size="12.5" font-weight="600" fill="#16242b">${ORD(i)} term</text>`;
            if (sub) g += `<text x="${x(i)}" y="${H - 8}" text-anchor="middle" font-size="11.5" fill="#55616b">${sub}</text>`;
        }
        S.forEach((s, si) => {
            let d = "";
            let prev = null;
            s.pts.forEach(([i, v]) => {
                d += `${prev !== null && prev === i - 1 ? "L" : "M"}${x(i).toFixed(1)} ${y(v).toFixed(1)}`;
                prev = i;
            });
            g += `<path d="${d}" fill="none" stroke="${s.colour}" stroke-width="3" stroke-linejoin="round" stroke-linecap="round"/>`;
            s.pts.forEach(([i, v]) => {
                g += `<circle cx="${x(i)}" cy="${y(v)}" r="4.5" fill="#fff" stroke="${s.colour}" stroke-width="2.5"><title>${esc(s.label)} · ${ORD(i)} term: ${v.toFixed(1)}%</title></circle>`;
                if (labels) {
                    const o = S[1 - si] && S[1 - si].pts.find((p) => p[0] === i);
                    const above = !o || v >= o[1];
                    g += `<text x="${x(i)}" y="${y(v) + (above ? -10 : 19)}" text-anchor="middle" font-size="12" font-weight="700" fill="${s.colour}">${v.toFixed(1)}</text>`;
                }
            });
        });
        return g + "</svg>";
    }
    const drawChart = () => {
        const box = $("[data-trt-chart]");
        if (box && data && data.series.length) box.innerHTML = chart(Math.max(320, Math.floor(box.clientWidth - 20)));
    };
    let resizeTimer = null;
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(drawChart, 120);
    });

    function render() {
        const T = data.terms;
        let h = '<div><h1>Retention</h1><div class="trt-lede">Registered students who stayed through the term, by intake</div></div>';
        h += '<div class="trt-hero">';
        T.forEach((t) => {
            h += `<div class="trt-kpi"><div class="k"><i style="background:${t.colour}"></i>${esc(t.label)} term</div><div class="v">${pct(t.rate)}</div>`;
            h += `<div class="trt-split" role="img" aria-label="${n(t.stayed)} stayed, ${n(t.dropped)} dropped out">`;
            if (t.stayed) h += `<i style="flex:${t.stayed} 1 0;background:${t.colour}"></i>`;
            if (t.dropped) h += `<i style="flex:${t.dropped} 1 0;background:var(--trt-orange)"></i>`;
            h += `</div><div class="s"><b>${n(t.stayed)}</b> of ${n(t.registered)} stayed · <b class="${t.dropped ? "low" : ""}">${n(t.dropped)}</b> dropped out</div></div>`;
        });
        h += "</div>";

        if (data.series.length) {
            h += `<section class="trt-sec"><div class="trt-head"><h2>Retention by term of study</h2><span class="trt-hint">${data.series.map((s) => `<span style="color:${s.colour};font-weight:700">● ${esc(s.label)}</span>`).join(" &nbsp; ")}</span></div><div class="trt-chart" data-trt-chart></div>`;
            if (data.takeaway) h += `<div class="trt-takeaway">${data.takeaway.lead ? `<b>${esc(data.takeaway.lead)}</b> ` : ""}${esc(data.takeaway.text)}</div>`;
            h += "</section>";
        }

        const chev = '<svg class="chev" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';
        h += '<section class="trt-sec"><div class="trt-head"><h2>By term and intake</h2><span class="trt-hint">Select a count to list those students</span></div><div class="trt-table">';
        h += '<div class="trt-cols"><span>Term and intake</span><span>Term of study</span><span>Retention rate</span><span></span><span>Registered</span><span>Stayed</span><span>Dropped out</span><span>Total students</span></div>';
        T.forEach((t) => {
            h += `<div class="trt-row term"><button type="button" class="name" data-trt-toggle="${t.id}" aria-expanded="true">${chev}${esc(t.label)} term</button><span class="age"></span>`;
            h += `<span class="trt-track"><i style="width:${t.rate || 0}%;background:var(--trt-ink)"></i></span><span class="pct">${pct(t.rate)}</span>`;
            h += `<span class="n">${link(t.registered, t.id, "all", "registered")}</span><span class="n">${link(t.stayed, t.id, "all", "stayed")}</span><span class="n drop">${link(t.dropped, t.id, "all", "dropped")}</span><span class="n">${link(t.total, t.id, "all", "total")}</span></div>`;
            if (!t.intakes.length) {
                h += `<div class="trt-empty" data-group="${t.id}">No students are recorded for this term.</div>`;
                return;
            }
            t.intakes.forEach((r) => {
                const low = r.rate !== null && t.rate !== null && r.rate < t.rate;
                h += `<div class="trt-row" data-group="${t.id}"><span class="name">${esc(r.label)} intake</span><span class="age">${r.study ? ORD(r.study) + " term" : "–"}</span>`;
                h += `<span class="trt-track"><i style="width:${r.rate || 0}%;background:var(--trt-${low ? "orange" : "teal"})"></i></span><span class="pct${low ? " low" : ""}">${pct(r.rate)}</span>`;
                h += `<span class="n">${link(r.registered, t.id, r.id, "registered")}</span><span class="n">${link(r.stayed, t.id, r.id, "stayed")}</span>`;
                h += `<span class="n drop${r.dropped ? " low" : ""}">${link(r.dropped, t.id, r.id, "dropped")}</span><span class="n">${link(r.total, t.id, r.id, "total")}</span></div>`;
            });
        });
        h += "</div></section>";

        h += '<ul class="trt-notes"><li>Retention rate = (Registered − Dropped out) ÷ Registered. Orange = below that term\'s overall rate.</li><li>Term of study counts from the intake\'s first term: an intake that joined this term is in its 1st term.</li>';
        h += "<li>Total students = students on an intake's courses who are assigned to classes this term. Registered = those with an awarding body registration.</li>";
        h += "<li>Dropped out = registered students who have left (Discarded, Left, Suspended or Terminated, SLC and non-progression included) with their leaving recorded against this term; each counts once, in that term. Students who came back are not counted; suspended students who have not returned are. Leaving with the HNC is not a dropout.</li>";
        const gone = T.filter((t) => t.left_before > 0);
        if (gone.length) h += `<li>Not counted in a term: students who had left in an earlier term or within 28 days of their course start (${gone.map((t) => `${esc(t.label)} term ${n(t.left_before)}`).join(", ")}).</li>`;
        h += "</ul>";

        result.innerHTML = `<div class="trt-report">${h}</div>`;
        drawChart();
    }

    result.addEventListener("click", (e) => {
        const toggle = e.target.closest("[data-trt-toggle]");
        if (!toggle) return;
        const open = toggle.getAttribute("aria-expanded") !== "true";
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        result.querySelectorAll(`[data-group="${toggle.dataset.trtToggle}"]`).forEach((row) => (row.hidden = !open));
    });

    /* ── Students behind a count: every row at once; search, pages of 15 and Excel in the browser ── */
    const PER = 15;
    const modalEl = document.getElementById("trtStudentsModal");
    const modal = modalEl && window.tailwind ? tailwind.Modal.getOrCreateInstance(modalEl) : null;
    const mq = (sel) => modalEl.querySelector(sel);
    const L = { rows: [], shown: [], page: 0, title: "", kicker: "", seq: 0 };

    function drawList() {
        const pages = Math.max(1, Math.ceil(L.shown.length / PER));
        L.page = Math.max(0, Math.min(pages - 1, L.page));
        const part = L.shown.slice(L.page * PER, (L.page + 1) * PER);
        // The design's four columns; the course goes under the intake.
        let h = '<table class="trt-mt"><thead><tr><th scope="col">Student ID</th><th scope="col">Name</th><th scope="col">Intake</th><th scope="col">Status</th></tr></thead><tbody>';
        part.forEach((r) => {
            const [label, colour] = OUTCOME[r.outcome] || OUTCOME.none;
            const sub = r.outcome === "dropped" && r.left ? `${r.left_status ? esc(r.left_status) + " · " : ""}${esc(r.left)}` : esc(r.status);
            h += `<tr><td>${esc(r.registration_no)}</td><td><a href="${esc(r.url)}" target="_blank" rel="noopener">${esc(r.name)}</a></td><td class="intake">${esc(r.intake)}${r.course ? `<small>${esc(r.course)}</small>` : ""}</td>`;
            h += `<td><span class="trt-pill"><i style="background:${colour}"></i>${label}</span>${sub ? `<small>${sub}</small>` : ""}</td></tr>`;
        });
        if (!part.length) h += `<tr><td colspan="4" class="none">${L.rows.length ? "No students match that search." : "No students to show."}</td></tr>`;
        mq("[data-trt-m-body]").innerHTML = h + "</tbody></table>";
        mq("[data-trt-m-count]").textContent = L.shown.length
            ? `Showing ${n(L.page * PER + 1)} to ${n(L.page * PER + part.length)} of ${n(L.shown.length)}${L.shown.length < L.rows.length ? ` (filtered from ${n(L.rows.length)})` : ""}`
            : `0 of ${n(L.rows.length)}`;
        mq("[data-trt-m-page-label]").textContent = `Page ${n(L.page + 1)} of ${n(pages)}`;
        mq('[data-trt-m-page="-1"]').disabled = L.page === 0;
        mq('[data-trt-m-page="1"]').disabled = L.page >= pages - 1;
        mq("[data-trt-m-excel]").disabled = !L.shown.length;
    }

    function filter() {
        const q = mq("[data-trt-m-q]").value.trim().toLowerCase();
        L.shown = q ? L.rows.filter((r) => `${r.registration_no} ${r.name}`.toLowerCase().includes(q)) : L.rows;
        L.page = 0;
        drawList();
    }

    if (modal) {
        result.addEventListener("click", (e) => {
            const b = e.target.closest("[data-trt-list]");
            if (!b || !data) return;
            const key = b.dataset.trtList;
            const term = data.terms.find((t) => String(t.id) === b.dataset.term);
            const intake = b.dataset.intake === "all" ? null : term && term.intakes.find((r) => String(r.id) === b.dataset.intake);
            L.kicker = `${intake ? esc(intake.label) + " intake" : "All intakes"} · ${term ? esc(term.label) : ""} term`;
            L.title = `${KEY[key]} (${b.textContent.trim()})`;
            L.rows = L.shown = [];
            L.page = 0;
            mq("[data-trt-m-kicker]").innerHTML = L.kicker;
            mq("[data-trt-m-title]").textContent = L.title;
            mq("[data-trt-m-q]").value = "";
            mq("[data-trt-m-body]").innerHTML = '<div class="trt-mnote">Loading students…</div>';
            mq("[data-trt-m-count]").textContent = "";
            mq("[data-trt-m-page-label]").textContent = "";
            mq("[data-trt-m-excel]").disabled = true;
            modalEl.querySelectorAll("[data-trt-m-page]").forEach((p) => (p.disabled = true));
            modal.show();
            const mine = ++L.seq;
            axios
                .get(route(root.dataset.students, { term: b.dataset.term }), { params: { intake: b.dataset.intake, key } })
                .then(({ data: d }) => {
                    if (mine !== L.seq) return;
                    L.rows = L.shown = d.rows;
                    L.title = `${KEY[key]} (${n(d.rows.length)})`;
                    mq("[data-trt-m-title]").textContent = L.title;
                    filter();
                })
                .catch(() => {
                    if (mine !== L.seq) return;
                    mq("[data-trt-m-body]").innerHTML = '<div class="trt-mnote trt-mnote--error">The student list could not be loaded. Please try again.</div>';
                });
        });
        mq("[data-trt-m-q]").addEventListener("input", filter);
        modalEl.addEventListener("click", (e) => {
            const page = e.target.closest("[data-trt-m-page]");
            if (page && !page.disabled) {
                L.page += Number(page.dataset.trtMPage);
                drawList();
            }
            if (e.target.closest("[data-trt-m-excel]") && L.shown.length) {
                const rows = [["Student ID", "Name", "Intake", "Course", "Retention", "Current status", "Leaving status", "Status changed"]].concat(
                    L.shown.map((r) => [r.registration_no, r.name, r.intake, r.course, (OUTCOME[r.outcome] || OUTCOME.none)[0], r.status, r.left_status || "", r.left || ""])
                );
                const book = xlsx.utils.book_new();
                xlsx.utils.book_append_sheet(book, xlsx.utils.aoa_to_sheet(rows), "Students");
                const name = `${L.title.replace(/\s*\(.*\)$/, "")} ${mq("[data-trt-m-kicker]").textContent}`.replace(/[^A-Za-z0-9]+/g, "_").replace(/^_|_$/g, "");
                xlsx.writeFile(book, `${name}.xlsx`);
            }
        });
        modalEl.addEventListener("hidden.tw.modal", () => {
            L.seq++;
            L.rows = L.shown = [];
            mq("[data-trt-m-body]").innerHTML = "";
        });
    }

    /* ── New and old versions together (as the intake reports) ── */
    const oldSelect = document.querySelector("#retention_term_id");
    const oldForm = document.querySelector("#termRetentionReportForm");
    if (oldSelect && oldForm) {
        const valuesOf = (s) => (s.tomselect ? [].concat(s.tomselect.getValue() || []) : Array.from(s.selectedOptions).map((o) => o.value)).map(String).filter(Boolean);
        const same = (a, b) => a.length === b.length && a.every((v) => b.includes(v));
        const copy = (from, to) => {
            const values = valuesOf(from);
            if (same(values, valuesOf(to)) || !to.tomselect) return; // also stops the two echoing each other
            to.tomselect.setValue(values);
        };
        let mirroring = false;
        const fire = (target) => {
            mirroring = true;
            try {
                target.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
            } finally {
                mirroring = false;
            }
        };
        select.addEventListener("change", () => copy(select, oldSelect));
        oldSelect.addEventListener("change", () => copy(oldSelect, select));
        form.addEventListener("submit", () => {
            if (mirroring || !valuesOf(select).length) return;
            copy(select, oldSelect);
            fire(oldForm);
        });
        oldForm.addEventListener("submit", () => {
            if (mirroring || !valuesOf(oldSelect).length) return;
            copy(oldSelect, select);
            fire(form);
        });
    }

    paintIcons();
})();
