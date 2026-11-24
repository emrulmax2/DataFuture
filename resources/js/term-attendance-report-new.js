import TomSelect from "tom-select";
import { createIcons, icons } from "lucide";

/*
 * Reports › Term Performance › Attendance Rates, "New version" (user's attendance-term-report
 * design, 24 Nov 2026).
 *
 * Generate asks once for the whole term (TermAttendanceRateController::generate: term → course →
 * group → module, each with its term rate and weekly rates); the breadcrumb and rows then drill
 * down here. Download PDF hands the level on screen and the lines switched on to the PDF.
 * As on Intake Performance, the old version follows: the same term, and Generate runs both.
 */
(function () {
    const root = document.querySelector("[data-tar]");
    if (!root) return;

    const $ = (sel, el = root) => el.querySelector(sel);
    const esc = (s) => String(s ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" })[c]);
    const pc = (v) => (v == null ? "–" : Number(v).toFixed(1) + "%");
    const MONTHS = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const wk = (d) => {
        const p = d.split("-");
        return +p[2] + " " + MONTHS[+p[1] - 1];
    };
    const has = (a) => Array.isArray(a) && a.some((v) => v != null);
    const short = (s, n) => (s.length > n ? s.slice(0, n - 1) + "…" : s);
    const paintIcons = () => createIcons({ icons, "stroke-width": 1.75, nameAttr: "data-lucide" });
    const LEVELS = [
        { one: "course", many: "Courses" },
        { one: "group", many: "Groups" },
        { one: "module", many: "Modules" },
    ];
    const COLOURS = ["#1668a8", "#c8601a", "#7a4fb5", "#2f9e8f", "#b3261e", "#8a6d1b", "#c2478a", "#4a5560"];
    const MAX_DEFAULT = 7; // more lines than this: only "Overall" until switched on (as the PDF)

    /* ── Version tabs (as Intake Performance; toggle buttons, not role=tab) ── */
    document.querySelectorAll("[data-version-tabs]").forEach((group) => {
        const buttons = group.querySelectorAll(":scope > .crn-tabs [data-version]");
        const panes = group.querySelectorAll(":scope > [data-version-pane]");
        buttons.forEach((button) =>
            button.addEventListener("click", () => {
                buttons.forEach((b) => {
                    b.classList.toggle("is-active", b === button);
                    b.setAttribute("aria-pressed", b === button ? "true" : "false");
                });
                panes.forEach((pane) => (pane.hidden = pane.dataset.versionPane !== button.dataset.version));
                // The old version's Chart.js chart measures itself when shown.
                if (button.dataset.version === "old") window.dispatchEvent(new Event("resize"));
            })
        );
    });

    /* ── Form ── */
    const form = $("[data-tar-form]");
    const select = $("[data-tar-term]");
    const error = $("[data-tar-error]");
    const button = $("[data-tar-generate]");
    const result = $("[data-tar-result]");
    const picker = new TomSelect(select, {
        plugins: { dropdown_input: {} },
        placeholder: "Search here...",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown tar-ts-dropdown",
        maxOptions: null,
        create: false,
    });

    let state = null; // { data, path: [nodes], on: { pathKey: { seriesKey: bool } } }
    let seq = 0;

    const busy = (on) => {
        button.classList.toggle("is-busy", on);
        button.disabled = on;
    };

    select.addEventListener("change", () => {
        error.textContent = "";
        result.innerHTML = "";
        state = null;
        updatePdfLink(); // hidden until a report is generated
    });

    form.addEventListener("submit", (e) => {
        e.preventDefault();
        const term = picker.getValue();
        error.textContent = "";
        if (!term) {
            error.textContent = "Please select an attendance semester.";
            return;
        }
        const mine = ++seq;
        busy(true);
        result.innerHTML = "";
        state = null;
        updatePdfLink();
        axios
            .get(route(root.dataset.generate, { term }))
            .then(({ data }) => {
                if (mine !== seq) return;
                state = { data, path: [data.tree], on: {} };
                render();
            })
            .catch((err) => {
                if (mine !== seq) return;
                error.textContent = (err.response && err.response.data && err.response.data.message) || "The report could not be generated. Please try again.";
            })
            .finally(() => mine === seq && busy(false));
    });

    /* ── The report ── */
    const pathKey = () => state.path.map((n) => n.id).join("/");

    function series(node) {
        const S = [];
        if (has(node.weekly)) S.push({ key: "_all", name: "Overall", data: node.weekly, colour: "#16242b", main: true });
        node.children.forEach((k, i) => {
            if (has(k.weekly)) S.push({ key: k.id, name: k.name, data: k.weekly, colour: COLOURS[i % COLOURS.length] });
        });
        return S;
    }

    function cell(v, avg) {
        if (v == null) return '<td class="na">–</td>';
        const base = avg == null ? 50 : avg;
        const d = v - base;
        const t = Math.min(1, Math.abs(d) / 30);
        const to = d < 0 ? [200, 96, 26] : [14, 90, 84];
        const k = 0.08 + t * 0.82;
        const c = to.map((x) => Math.round(255 + (x - 255) * k));
        const lum = (0.299 * c[0] + 0.587 * c[1] + 0.114 * c[2]) / 255;
        return `<td style="background:rgb(${c.join(",")});color:${lum < 0.6 ? "#fff" : "#16242b"}" title="${pc(v)}">${v.toFixed(1)}</td>`;
    }

    function render() {
        const { data, path } = state;
        const node = path[path.length - 1];
        const depth = path.length - 1;
        const lv = LEVELS[Math.min(depth, 2)];
        const kids = node.children || [];
        const rated = kids.filter((k) => k.rate != null).sort((a, b) => a.rate - b.rate);
        const termLabel = `${data.term.name} term`;

        // The one number, lowest and highest. Below the term, a link back up (no breadcrumb, user).
        const up = depth > 0 ? (depth === 1 ? termLabel : path[depth - 1].name) : "";
        let h = `<div class="tar-hero"><div>${depth > 0 ? `<button type="button" class="tar-back" data-tar-crumb="${depth - 1}"><i data-lucide="arrow-left"></i> ${esc(up)}</button>` : ""}<h1>${esc(depth === 0 ? termLabel : node.name)}</h1><div class="tar-big">${pc(node.rate)}</div><div class="tar-sub">${node.rate == null ? "No attendance this term" : "Attendance this term"}</div></div>`;
        h +=
            rated.length > 1
                ? `<ul class="tar-facts"><li><span>Lowest ${lv.one}</span><b>${esc(rated[0].name)}</b><strong class="${node.rate != null && rated[0].rate < node.rate ? "low" : ""}">${pc(rated[0].rate)}</strong></li><li><span>Highest ${lv.one}</span><b>${esc(rated[rated.length - 1].name)}</b><strong>${pc(rated[rated.length - 1].rate)}</strong></li></ul>`
                : "<div></div>";
        h += "</div>";

        // Week by week
        const S = series(node);
        const key = pathKey();
        h += `<section class="tar-chart-wrap"><div class="tar-head"><h2>Week by week</h2>${S.length > 1 ? '<span class="tar-legend">Select a name to show or hide its line</span>' : ""}</div>`;
        if (!S.length) {
            h += `<div class="tar-empty">No weekly figures for this ${depth === 0 ? "term" : LEVELS[depth - 1].one}.</div>`;
        } else {
            if (!state.on[key]) {
                state.on[key] = {};
                S.forEach((x) => (state.on[key][x.key] = !!x.main || S.length <= MAX_DEFAULT));
            }
            if (S.length > 1) {
                h += '<div class="tar-chips">' + S.map((x) => `<button type="button" class="tar-chip" data-series="${esc(x.key)}" aria-pressed="${!!state.on[key][x.key]}" style="--c:${x.colour}"><i></i>${esc(x.name)}</button>`).join("") + "</div>";
            }
            h += '<div class="tar-chart" data-tar-chart></div>';
            if (kids.length && S.length === 1) h += `<div class="tar-legend">The ${lv.many.toLowerCase()} have no weekly figures, so only the overall line is shown.</div>`;
        }
        h += "</section>";

        // The list
        if (depth < 3 && kids.length) {
            h += `<section class="tar-list"><div class="tar-head"><h2>${lv.many}</h2>`;
            if (rated.length && node.rate != null) h += `<div class="tar-legend"><i style="background:var(--tar-teal)"></i>At or above ${pc(node.rate)}<i style="background:var(--tar-orange)"></i>Below</div>`;
            h += '</div><div class="tar-rows">';
            rated.concat(kids.filter((k) => k.rate == null)).forEach((k) => {
                const below = k.rate != null && node.rate != null && k.rate < node.rate;
                const inner = `<span class="name">${esc(k.name)}</span><span class="tar-track">${k.rate != null ? `<i style="width:${Math.min(100, k.rate)}%;background:var(--tar-${below ? "orange" : "teal"})"></i>` : ""}</span><span class="rate${below ? " low" : ""}">${pc(k.rate)}</span>`;
                h +=
                    depth < 2
                        ? `<button type="button" class="tar-row" data-tar-go="${kids.indexOf(k)}" aria-label="Open ${esc(k.name)}">${inner}<span class="go" aria-hidden="true">›</span></button>`
                        : `<div class="tar-row">${inner}<span></span></div>`;
            });
            h += "</div></section>";
        }

        // Weekly figures as a heat grid
        const grid = (has(node.weekly) ? [{ name: depth === 0 ? "All courses" : "Overall", data: node.weekly, main: true }] : []).concat(
            kids
                .filter((k) => has(k.weekly))
                .sort((a, b) => (a.rate == null) - (b.rate == null) || a.rate - b.rate)
                .map((k) => ({ name: k.name, data: k.weekly, node: k }))
        );
        if (grid.length) {
            const shown = data.weeks.map((w, i) => i).filter((i) => grid.some((r) => r.data[i] != null));
            h += `<details open><summary>Weekly figures</summary><div class="tar-head" style="margin-bottom:8px"><span class="tar-legend">Each cell is one week's attendance (%). Colour shows how far it is from the term average${node.rate != null ? " of " + pc(node.rate) : ""}.</span><span class="tar-scale"><span>Below</span><i></i><span>Above</span></span></div>`;
            h += `<div class="tar-scroll"><table class="tar-heat"><thead><tr><th scope="col">${depth < 3 ? lv.many.replace(/s$/, "") : ""}</th>${shown.map((i) => `<th scope="col">${wk(data.weeks[i])}</th>`).join("")}</tr></thead><tbody>`;
            grid.forEach((r) => {
                const label = r.node && depth < 2 ? `<button type="button" data-tar-go="${kids.indexOf(r.node)}">${esc(r.name)}</button>` : esc(r.name);
                h += `<tr${r.main ? ' class="main"' : ""}><th scope="row">${label}</th>${shown.map((i) => cell(r.data[i], node.rate)).join("")}</tr>`;
            });
            h += "</tbody></table></div></details>";
        }

        result.innerHTML = `<div class="tar-panel" data-tar-panel>${h}</div>`;
        paintIcons();
        chart();
        updatePdfLink();
    }

    function chart() {
        const box = $("[data-tar-chart]", result);
        if (!box) return;
        const { data, path } = state;
        const node = path[path.length - 1];
        const on = state.on[pathKey()];
        const S = series(node).filter((x) => on[x.key]);
        const n = data.weeks.length;
        // Drawn at the box's own width (1 unit = 1px), so text keeps its real size (user: labels
        // were too big when a 1000-wide drawing was stretched to the panel).
        const W = Math.max(560, Math.round(box.clientWidth || 1000));
        const H = 300, L = 40, R = 176, T = 12, B = 28;
        const x = (i) => L + (n > 1 ? (i * (W - L - R)) / (n - 1) : (W - L - R) / 2);
        const y = (v) => T + ((100 - v) * (H - T - B)) / 100;

        let g = `<svg width="${W}" height="${H}" viewBox="0 0 ${W} ${H}" role="img" aria-label="Weekly attendance, one line per row. The figures are listed under Weekly figures.">`;
        [0, 25, 50, 75, 100].forEach((v) => {
            g += `<line x1="${L}" x2="${W - R}" y1="${y(v)}" y2="${y(v)}" stroke="#e3e7ea"/><text x="${L - 8}" y="${y(v) + 4}" text-anchor="end" font-size="11" fill="#55616b">${v}%</text>`;
        });
        data.weeks.forEach((w, i) => (g += `<text x="${x(i)}" y="${H - 8}" text-anchor="middle" font-size="11" fill="#55616b">${wk(w)}</text>`));
        if (node.rate != null) g += `<line x1="${L}" x2="${W - R}" y1="${y(node.rate)}" y2="${y(node.rate)}" stroke="#9aa3ab" stroke-dasharray="4 4"/>`;
        const ends = [];
        // Overall drawn last, on top.
        S.filter((s) => !s.main).concat(S.filter((s) => s.main)).forEach((s) => {
            let d = "";
            let pen = false;
            let last = -1;
            s.data.forEach((v, i) => {
                if (v == null) {
                    pen = false;
                    return;
                }
                d += (pen ? "L" : "M") + x(i).toFixed(1) + " " + y(v).toFixed(1);
                pen = true;
                last = i;
            });
            g += `<path d="${d}" fill="none" stroke="${s.colour}" stroke-width="${s.main ? 2.5 : 1.75}" stroke-linejoin="round" stroke-linecap="round"/>`;
            s.data.forEach((v, i) => {
                if (v != null) g += `<circle cx="${x(i)}" cy="${y(v)}" r="${s.main ? 3.25 : 2.5}" fill="${s.colour}"/>`;
            });
            if (last >= 0) ends.push({ s, i: last, y: y(s.data[last]) });
        });
        if (ends.length <= 8) {
            placeEnds(ends, 14, T + 4, H - B - 2);
            ends.forEach((e) => {
                g += `<text x="${x(e.i) + 9}" y="${(e.y + 4).toFixed(1)}" font-size="11" font-weight="${e.s.main ? 700 : 500}" fill="${e.s.colour}">${esc(short(e.s.name, 16))} ${pc(e.s.data[e.i])}</text>`;
            });
        }
        g += `<line data-guide y1="${T}" y2="${H - B}" stroke="#9aa3ab" visibility="hidden"/></svg><div class="tar-tip" data-tip hidden></div>`;
        box.innerHTML = g;

        const svg = box.querySelector("svg");
        const tip = box.querySelector("[data-tip]");
        const guide = box.querySelector("[data-guide]");
        svg.addEventListener("mousemove", (ev) => {
            const r = svg.getBoundingClientRect();
            const i = Math.max(0, Math.min(n - 1, Math.round((((ev.clientX - r.left) / r.width) * W - L) / ((W - L - R) / Math.max(1, n - 1)))));
            const rows = S.filter((s) => s.data[i] != null).sort((a, b) => b.data[i] - a.data[i]);
            if (!rows.length) {
                tip.hidden = true;
                guide.setAttribute("visibility", "hidden");
                return;
            }
            guide.setAttribute("x1", x(i));
            guide.setAttribute("x2", x(i));
            guide.setAttribute("visibility", "visible");
            tip.innerHTML = `<b>Week of ${wk(data.weeks[i])}</b>` + rows.map((s) => `<div><span title="${esc(s.name)}"><i style="background:${s.colour}"></i>${esc(s.name)}</span><span>${pc(s.data[i])}</span></div>`).join("");
            tip.hidden = false;
            const tw = tip.offsetWidth;
            let left = (x(i) / W) * r.width + 16;
            if (left + tw > r.width) left = (x(i) / W) * r.width - tw - 16;
            tip.style.left = Math.max(0, left) + "px";
        });
        svg.addEventListener("mouseleave", () => {
            tip.hidden = true;
            guide.setAttribute("visibility", "hidden");
        });
    }

    // End labels: at least `gap` apart and inside [top, bottom]; pushed down, then back up from the bottom.
    function placeEnds(ends, gap, top, bottom) {
        ends.sort((a, b) => a.y - b.y);
        ends.forEach((e, k) => (e.y = Math.max(e.y, top, k ? ends[k - 1].y + gap : top)));
        for (let k = ends.length - 1; k >= 0; k--) {
            const limit = k === ends.length - 1 ? bottom : ends[k + 1].y - gap;
            if (ends[k].y > limit) ends[k].y = limit;
        }
    }

    // Redrawn at the new width when the window changes (labels keep their size).
    let resizeTimer = null;
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => state && chart(), 150);
    });

    // The PDF is of the level on screen, with the lines switched on.
    function updatePdfLink() {
        const link = $("[data-tar-pdf]");
        if (!link) return;
        link.hidden = !state;
        if (!state) return;
        const on = state.on[pathKey()] || {};
        const params = { term: state.data.term.id };
        const path = state.path.slice(1).map((n) => n.id).join("/");
        if (path) params.path = path;
        const show = Object.keys(on).filter((k) => on[k]);
        if (show.length) params.show = show.join(",");
        link.setAttribute("href", route(root.dataset.pdf, params));
    }

    result.addEventListener("click", (e) => {
        if (!state) return;
        const node = state.path[state.path.length - 1];
        const go = e.target.closest("[data-tar-go]");
        if (go) {
            const next = node.children[Number(go.dataset.tarGo)];
            if (next && state.path.length < 3) {
                state.path.push(next);
                render();
                result.scrollIntoView({ block: "start", behavior: "smooth" });
            }
            return;
        }
        const crumb = e.target.closest("[data-tar-crumb]");
        if (crumb) {
            state.path = state.path.slice(0, Number(crumb.dataset.tarCrumb) + 1);
            render();
            return;
        }
        const chip = e.target.closest("[data-series]");
        if (chip) {
            const on = state.on[pathKey()];
            const k = chip.dataset.series;
            on[k] = !on[k];
            chip.setAttribute("aria-pressed", on[k] ? "true" : "false");
            chart();
            updatePdfLink();
        }
    });

    /* ── New and old versions together (as Intake Performance) ── */
    const oldSelect = document.querySelector("#term_declaration_id");
    const oldForm = document.querySelector("#termAttendanceRateSearchForm");
    if (oldSelect && oldForm) {
        let mirroring = false;
        const copy = (from, to) => {
            const v = from.getValue() || "";
            if ((to.getValue() || "") === v) return; // also stops the two echoing each other
            if (v && !to.options[v] && from.options[v]) to.addOption({ value: v, text: from.options[v].text });
            to.setValue(v);
        };
        const oldPicker = () => oldSelect.tomselect;
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
