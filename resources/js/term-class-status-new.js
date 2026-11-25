import TomSelect from "tom-select";
import { createIcons, icons } from "lucide";

/*
 * Reports › Term Performance › Class Status, "New version" (user's class-status-report design,
 * 25 Nov 2026). Generate asks once (ClassStatusReportController::generate); courses open and
 * close their groups here. Download PDF and Export Excel show once a report is generated.
 * As the other new reports, the old version follows: the same term, and Generate runs both.
 */
(function () {
    const root = document.querySelector("[data-tcs]");
    if (!root) return;

    const $ = (sel, el = root) => el.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const n = (v) => Number(v || 0).toLocaleString("en-GB");
    const pc = (a, b) => (b > 0 ? ((100 * a) / b).toFixed(1) + "%" : "–");
    const rate = (r) => (r.scheduled > 0 ? r.held / r.scheduled : null);
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });
    // Columns that only show when the term has any (the note says which are left out).
    const EXTRA = [
        { key: "cancelled", label: "Cancelled", none: "cancelled" },
        { key: "unknown", label: "Unknown", none: "unknown-status" },
        { key: "future", label: "Still to come", none: "future-scheduled" },
    ];

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
                // The old version's Tabulator measures itself when shown.
                if (button.dataset.version === "old") window.dispatchEvent(new Event("resize"));
            })
        );
    });

    /* ── Form ── */
    const form = $("[data-tcs-form]");
    const select = $("[data-tcs-term]");
    const error = $("[data-tcs-error]");
    const button = $("[data-tcs-generate]");
    const pdf = $("[data-tcs-pdf]");
    const excel = $("[data-tcs-excel]");
    const result = $("[data-tcs-result]");
    const picker = new TomSelect(select, {
        plugins: { dropdown_input: {} },
        placeholder: "Search here...",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown tar-ts-dropdown",
        maxOptions: null,
        create: false,
    });

    let data = null;
    let open = {};
    let seq = 0;

    const busy = (on) => {
        button.classList.toggle("is-busy", on);
        button.disabled = on;
    };
    const downloads = () => {
        pdf.hidden = excel.hidden = !data;
        if (data) {
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
                // The first course (lowest held %) opens with its groups, as in the design.
                open = d.courses.length ? { [d.courses[0].id]: true } : {};
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
    // Empty parts are left out (no slivers); in the tall bar each part is at least as wide as its label.
    function stack(r, tall) {
        const own = Math.max(0, r.held - r.proxy);
        const label = `${n(own)} held by the scheduled tutor, ${n(r.proxy)} held by proxy, ${n(r.not_held)} not held`;
        const parts = [
            [own, "var(--tar-teal)", "Held by scheduled tutor " + n(own)],
            [r.proxy, "var(--tcs-proxy)", "By proxy " + n(r.proxy)],
            [r.not_held, "var(--tar-orange)", n(r.not_held)],
        ].filter(([v]) => v > 0);
        return (
            `<span class="tcs-stack${tall ? " tall" : ""}" role="img" aria-label="${label}">` +
            parts.map(([v, colour, text]) => `<i style="flex:${v} 1 0;background:${colour}">${tall ? text : ""}</i>`).join("") +
            "</span>"
        );
    }

    function cells(r, low, extra) {
        return (
            stack(r) +
            `<span class="pct${low ? " low" : ""}">${pc(r.held, r.scheduled)}</span>` +
            `<span class="n">${n(r.scheduled)}</span><span class="n">${n(r.held)}</span>` +
            `<span class="n miss${r.not_held ? " low" : " zero"}">${n(r.not_held)}</span><span class="n">${n(r.proxy)}</span>` +
            extra.map((c) => `<span class="n${r[c.key] ? "" : " zero"}">${n(r[c.key])}</span>`).join("")
        );
    }

    function render() {
        const T = data.totals;
        if (!T.scheduled && !T.future) {
            result.innerHTML = `<div class="tcs-report"><div class="tcs-empty">No classes are scheduled for ${esc(data.term.name)}.</div></div>`;
            return;
        }
        const avg = rate(T);
        const extra = EXTRA.filter((c) => T[c.key] > 0);
        const absent = EXTRA.filter((c) => !T[c.key]).map((c) => c.none);
        const own = Math.max(0, T.held - T.proxy);
        const cols = ["minmax(0,1fr)", "minmax(120px,26%)", "70px", "84px", "64px", "72px", "72px"].concat(extra.map(() => "84px")).join(" ");

        let h = `<div class="tcs-hero"><div><h1>${esc(data.term.name)} term</h1><div class="tcs-big">${pc(T.held, T.scheduled)}</div><div class="tcs-sub">of scheduled classes were held</div></div><div class="tcs-stats">`;
        h += `<div class="tcs-stat"><div class="k">Scheduled</div><div class="v">${n(T.scheduled)}</div><div class="s">Classes due so far${T.future ? " · " + n(T.future) + " still to come" : ""}</div></div>`;
        h += `<div class="tcs-stat"><div class="k"><i style="background:var(--tar-teal)"></i>Held</div><div class="v">${n(T.held)}</div><div class="s">${n(own)} by the scheduled tutor</div></div>`;
        h += `<div class="tcs-stat"><div class="k"><i style="background:var(--tcs-proxy)"></i>Held by proxy</div><div class="v">${n(T.proxy)}</div><div class="s">${pc(T.proxy, T.held)} of classes held</div></div>`;
        h += `<div class="tcs-stat"><div class="k"><i style="background:var(--tar-orange)"></i>Not held</div><div class="v${T.not_held ? " low" : ""}">${n(T.not_held)}</div><div class="s">${pc(T.not_held, T.scheduled)} of scheduled classes</div></div></div></div>`;

        h += `<section class="tcs-sec"><h2>What happened to the ${n(T.scheduled)} scheduled classes</h2>${stack(T, true)}`;
        if (absent.length) {
            const list = absent.length === 1 ? absent[0] : absent.slice(0, -1).join(", ") + " or " + absent[absent.length - 1];
            h += `<div class="tcs-note">No ${list} classes are recorded for this term, so ${absent.length === 1 ? "that column is" : "those columns are"} left out.</div>`;
        }
        h += "</section>";

        h += `<section class="tcs-sec"><div class="tcs-head"><h2>By course and group</h2><div class="tcs-legend"><i style="background:var(--tar-teal)"></i>Scheduled tutor<i style="background:var(--tcs-proxy)"></i>Proxy<i style="background:var(--tar-orange)"></i>Not held</div></div>`;
        h += `<div style="--tcs-cols:${cols}"><div class="tcs-cols"><span>Course</span><span>Classes</span><span>Held %</span><span>Scheduled</span><span>Held</span><span>Not held</span><span>By proxy</span>${extra.map((c) => `<span>${c.label}</span>`).join("")}</div><div class="tcs-grid">`;
        const chev = '<svg class="tcs-chev" aria-hidden="true" width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';
        data.courses.forEach((c) => {
            const isOpen = !!open[c.id];
            const name = c.groups.length
                ? `<button type="button" class="name" data-tcs-toggle="${c.id}" aria-expanded="${isOpen}">${chev}<span>${esc(c.name)}</span></button>`
                : `<span class="name"><span style="width:20px;flex-shrink:0"></span><span>${esc(c.name)}</span></span>`;
            h += `<div class="tcs-row course">${name}${cells(c, rate(c) != null && rate(c) < avg, extra)}</div>`;
            c.groups.forEach((g) => {
                h += `<div class="tcs-row group"${isOpen ? "" : " hidden"}><span class="name">${esc(g.name)}</span>${cells(g, rate(g) != null && rate(g) < avg, extra)}</div>`;
            });
        });
        h += `<div class="tcs-row total"><span class="name" style="padding-left:28px">All courses shown</span>${cells(T, false, extra)}</div></div></div>`;
        h += `<div class="tcs-note">Held % = classes held ÷ classes scheduled (due so far). Lowest first; figures in orange are below the term average of ${pc(T.held, T.scheduled)}. Select a course with an arrow to show or hide its groups.</div></section>`;

        result.innerHTML = `<div class="tcs-report">${h}</div>`;
    }

    result.addEventListener("click", (e) => {
        const t = e.target.closest("[data-tcs-toggle]");
        if (!t || !data) return;
        const id = t.dataset.tcsToggle;
        open[id] = !open[id];
        render();
        const again = result.querySelector(`[data-tcs-toggle="${id}"]`);
        if (again) again.focus();
    });

    /* ── New and old versions together ── */
    const oldSelect = document.querySelector("#attendance_semester");
    const oldButton = document.querySelector("#classStatusFormBtn");
    if (oldSelect && oldButton) {
        let mirroring = false;
        const oldPicker = () => oldSelect.tomselect;
        const oldValue = () => {
            const v = oldPicker() ? oldPicker().getValue() : "";
            return String(Array.isArray(v) ? v[0] || "" : v || "");
        };
        const toOld = () => {
            const old = oldPicker();
            const v = picker.getValue() || "";
            if (!old || oldValue() === v) return; // also stops the two echoing each other
            if (v && !old.options[v] && picker.options[v]) old.addOption({ value: v, text: picker.options[v].text });
            old.setValue(v ? [v] : []);
        };
        const toNew = () => {
            const v = oldValue();
            if ((picker.getValue() || "") === v) return;
            picker.setValue(v);
        };
        select.addEventListener("change", toOld);
        oldSelect.addEventListener("change", toNew);

        form.addEventListener("submit", () => {
            if (mirroring || !picker.getValue()) return;
            toOld();
            mirroring = true;
            try {
                oldButton.click();
            } finally {
                mirroring = false;
            }
        });
        oldButton.addEventListener("click", () => {
            if (mirroring || !oldValue()) return;
            toNew();
            mirroring = true;
            try {
                form.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
            } finally {
                mirroring = false;
            }
        });
    }

    paintIcons();
})();
