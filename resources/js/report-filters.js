/*
 * The student search form shared by the new Student Result Report and Result Grade Performance
 * Report (user's designs, 25 Nov 2026): seven filters with tag multi-selects, Reset and Search, the
 * New / Old version switch, and the link to the Old tab's form (Search here fills and runs it; its
 * Search fills this form and runs it here). Styles: .srp-* in student-result-report.css.
 */
export const esc = (s) => String(s == null ? "" : s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
export const n = (v) => Number(v).toLocaleString("en-GB");
export const pc = (a, b) => (b ? ((100 * a) / b).toFixed(1) + "%" : "–");
export const plural = (k, word) => `${n(k)} ${word}${k === 1 ? "" : "s"}`;
/* Statuses shown in the warning colour: the student is no longer studying. */
export const OFF = /left|suspend|discard|terminat|withdraw|incomplete|ghost|obsolete|expired|refused/i;

const CHEVD = '<svg class="srp-chevd" width="12" height="12" viewBox="0 0 12 12" aria-hidden="true"><path d="M2 4l4 4 4-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
const ORDER = ["intake", "attend", "course", "group", "evening", "type", "status"];

/**
 * @param {object} o
 * @param {HTMLElement} o.root   the page's .srp element (holds the version switch and panes)
 * @param {HTMLElement} o.form   the New pane's form panel
 * @param {object} o.options     lists from the server: intakes, terms, courses, statuses ([id, name]), groups ([name, courseIds, termIds]), types
 * @param {(params: string) => void} o.onSearch  runs a search with the form's query string ("" = nothing chosen)
 * @param {() => void} o.onReset
 * @param {string[]} [o.fields]  the fields in order (default: all seven); "cert" is the Certification claimed? switch
 * @param {string[]} [o.required]  multi-selects that must have a choice (asterisk, inline error, no search without them)
 */
export function filterForm({ root, form, options: OPT, onSearch, onReset, fields = ORDER, required = [] }) {
    const ALL = {
        intake: { label: "Intake semester", opts: OPT.intakes || [], sel: [] },
        attend: { label: "Attendance semester", opts: OPT.terms || [], sel: [] },
        course: { label: "Course", opts: OPT.courses || [], sel: [] },
        group: { label: "Master group", opts: (OPT.groups || []).map((g) => [g[0], g[0]]), sel: [] },
        type: { label: "Student type", opts: (OPT.types || []).map((t) => [t, t]), sel: [] },
        status: { label: "Student status", opts: OPT.statuses || [], sel: [] },
    };
    // Only the page's multi-selects (Progress Monitoring has no Student type, as its old form).
    const F = {};
    fields.forEach((id) => ALL[id] && (F[id] = ALL[id]));
    /* Any / Yes / No switches: [label in the criteria line, request parameter, the Old form's radios]. */
    const SEGS = { cert: { label: "Certification claimed?", crit: "Certification claimed", param: "certificate", old: 'input[name="group[certificate_claimed]"]', value: "" } };
    const segOn = (id) => fields.includes(id) && SEGS[id];
    const GROUP_LINKS = {};
    (OPT.groups || []).forEach((g) => (GROUP_LINKS[g[0]] = { courses: g[1].map(String), terms: g[2].map(String) }));
    let OPEN = null;

    const labelOf = (id, value) => {
        const o = F[id].opts.find((x) => String(x[0]) === String(value));
        return o ? o[1] : String(value);
    };
    /* Master groups narrow to the chosen courses and attendance semesters (as the design narrows by course). */
    const optsOf = (id) => {
        if (id !== "group") return F[id].opts;
        const cs = F.course ? F.course.sel.map(String) : [];
        const ts = F.attend ? F.attend.sel.map(String) : [];
        if (!cs.length && !ts.length) return F.group.opts;
        return F.group.opts.filter((o) => {
            const l = GROUP_LINKS[o[0]];
            return l && (!cs.length || l.courses.some((c) => cs.includes(c))) && (!ts.length || l.terms.some((t) => ts.includes(t)));
        });
    };

    const segHtml = (id) =>
        [["", "Any"], ["Yes", "Yes"], ["No", "No"]]
            .map((v) => `<button type="button" data-seg-value="${v[0]}" aria-pressed="${SEGS[id].value === v[0]}">${v[1]}</button>`)
            .join("");
    function build() {
        let h = "";
        fields.forEach((id) => {
            if (segOn(id)) {
                h += `<div class="srp-field"><span id="srp-l-${id}">${SEGS[id].label}</span><div class="srp-seg srp-seg--field" role="group" aria-labelledby="srp-l-${id}" data-seg="${id}">${segHtml(id)}</div></div>`;
                return;
            }
            if (id === "evening") {
                h += '<label class="srp-field"><span>Evening / Weekend</span><select class="srp-ctl" data-srp-evening><option value="">Please select</option><option>Yes</option><option>No</option></select></label>';
                return;
            }
            const f = F[id];
            const must = required.includes(id);
            h += `<div class="srp-field"><span id="srp-l-${id}">${f.label}${must ? '<span class="srp-req" aria-hidden="true">*</span>' : ""}</span><div class="srp-ms" data-ms="${id}"${must ? ' data-required' : ""}><div class="srp-msbox"><button type="button" class="srp-mstoggle" aria-haspopup="true" aria-expanded="false" aria-labelledby="srp-l-${id} srp-s-${id}"></button><span class="srp-tags" style="display:contents"></span>${CHEVD}</div><div class="srp-mspanel" hidden><input type="search" placeholder="Search" aria-label="Search ${f.label.toLowerCase()}"><div class="srp-msacts"><button type="button" data-all="1">Select all</button><button type="button" data-all="0">Clear</button></div><div class="srp-mslist" role="group" aria-labelledby="srp-l-${id}"></div></div></div>${must ? `<span class="srp-ferr" data-ferr="${id}" role="alert" hidden>Select at least one ${f.label.toLowerCase()}.</span>` : ""}</div>`;
        });
        h += '<div class="srp-acts"><button type="button" class="srp-btn" data-srp-reset>Reset</button><button type="button" class="srp-btn srp-btn--primary" data-srp-search><span class="srp-spin" aria-hidden="true"></span>Search</button></div>';
        form.innerHTML = h;
        Object.keys(F).forEach(draw);
    }
    const msEl = (id) => form.querySelector(`[data-ms="${id}"]`);
    const evening = () => form.querySelector("[data-srp-evening]");

    function draw(id) {
        const f = F[id];
        const el = msEl(id);
        const opts = optsOf(id);
        const allowed = opts.map((o) => String(o[0]));
        f.sel = f.sel.filter((v) => allowed.includes(String(v)));
        let t = f.sel
            .slice(0, 3)
            .map((v) => `<span class="srp-tag"><span>${esc(labelOf(id, v))}</span><button type="button" data-rm="${esc(v)}" aria-label="Remove ${esc(labelOf(id, v))}">×</button></span>`)
            .join("");
        if (f.sel.length > 3) t += `<span class="srp-tag is-more"><span>+${f.sel.length - 3} more</span></span>`;
        el.querySelector(".srp-tags").innerHTML =
            (t || '<span class="srp-ph">Any</span>') + `<span id="srp-s-${id}" hidden>${f.sel.length ? f.sel.length + " selected" : "any"}</span>`;
        const q = el.querySelector("input[type=search]").value.trim().toLowerCase();
        let any = false;
        el.querySelector(".srp-mslist").innerHTML =
            opts
                .map((o) => {
                    const hit = String(o[1]).toLowerCase().includes(q);
                    if (hit) any = true;
                    const on = f.sel.some((v) => String(v) === String(o[0]));
                    return `<label class="srp-opt"${hit ? "" : " hidden"}><input type="checkbox" value="${esc(o[0])}"${on ? " checked" : ""}><span>${esc(o[1])}</span></label>`;
                })
                .join("") + (any ? "" : '<div class="srp-none srp-muted">No match.</div>');
    }
    function open(id) {
        if (OPEN && OPEN !== id) close();
        OPEN = id;
        const el = msEl(id);
        el.classList.add("is-open");
        el.querySelector(".srp-mspanel").hidden = false;
        el.querySelector(".srp-mstoggle").setAttribute("aria-expanded", "true");
        el.querySelector("input[type=search]").focus();
    }
    function close(focus) {
        if (!OPEN) return;
        const id = OPEN;
        const el = msEl(id);
        OPEN = null;
        el.classList.remove("is-open");
        el.querySelector(".srp-mspanel").hidden = true;
        el.querySelector(".srp-mstoggle").setAttribute("aria-expanded", "false");
        el.querySelector("input[type=search]").value = "";
        draw(id);
        if (focus) el.querySelector(".srp-mstoggle").focus();
    }
    function set(id, sel) {
        F[id].sel = sel;
        draw(id);
        if ((id === "course" || id === "attend") && F.group) draw("group");
    }

    const PARAM = { intake: "intake[]", attend: "attendance[]", course: "course[]", group: "group[]", type: "type[]", status: "status[]" };
    function query() {
        const p = new URLSearchParams();
        fields.forEach((id) => {
            if (F[id]) F[id].sel.forEach((v) => p.append(PARAM[id], v));
            else if (id === "evening" && evening() && evening().value) p.append("evening", evening().value);
            else if (segOn(id) && SEGS[id].value) p.append(SEGS[id].param, SEGS[id].value);
        });
        return p.toString();
    }
    function criteria() {
        const parts = [];
        fields.forEach((k) => {
            if (k === "evening") {
                if (evening() && evening().value) parts.push(`Evening / Weekend: ${evening().value}`);
                return;
            }
            if (segOn(k)) {
                if (SEGS[k].value) parts.push(`${SEGS[k].crit}: ${SEGS[k].value}`);
                return;
            }
            if (!F[k]) return;
            const sel = F[k].sel;
            if (sel.length) parts.push(`${F[k].label}: ${sel.length > 2 ? sel.length + " selected" : sel.map((v) => labelOf(k, v)).join(", ")}`);
        });
        return parts.join(" · ");
    }
    function busy(on) {
        const btn = form.querySelector("[data-srp-search]");
        btn.disabled = on;
        btn.classList.toggle("is-busy", on);
    }
    /* Required multi-selects: flag the empty ones; true when all have a choice. */
    function valid(mark = true) {
        let ok = true;
        required.forEach((id) => {
            const empty = !!F[id] && !F[id].sel.length;
            if (empty) ok = false;
            if (!mark) return;
            msEl(id).classList.toggle("is-err", empty);
            form.querySelector(`[data-ferr="${id}"]`).hidden = !empty;
        });
        if (!ok && mark) {
            const first = required.find((id) => F[id] && !F[id].sel.length);
            msEl(first).querySelector(".srp-mstoggle").focus();
        }
        return ok;
    }
    function search() {
        close();
        if (!valid()) return;
        onSearch(query());
    }

    /* ---------- events ---------- */
    document.addEventListener("click", (e) => {
        const t = e.target;
        // A click on something a redraw just replaced is not a click outside.
        if (!t.isConnected) return;
        const ms = t.closest(".srp-ms");
        if (OPEN && (!ms || ms.dataset.ms !== OPEN)) close();
        if (!form.contains(t)) return;
        let b;
        if (ms) {
            const id = ms.dataset.ms;
            if ((b = t.closest("[data-rm]"))) {
                const v = b.dataset.rm;
                set(id, F[id].sel.filter((x) => String(x) !== v));
                ms.querySelector(".srp-mstoggle").focus();
            } else if ((b = t.closest("[data-all]"))) {
                const q = ms.querySelector("input[type=search]").value.trim().toLowerCase();
                const shown = optsOf(id).filter((o) => String(o[1]).toLowerCase().includes(q)).map((o) => o[0]);
                const has = (v) => F[id].sel.some((x) => String(x) === String(v));
                set(id, b.dataset.all === "1" ? F[id].sel.concat(shown.filter((v) => !has(v))) : F[id].sel.filter((x) => !shown.some((v) => String(v) === String(x))));
            } else if (t.closest(".srp-msbox")) {
                if (OPEN === id) close(true);
                else open(id);
            }
            return;
        }
        if ((b = t.closest("[data-seg-value]"))) {
            const seg = b.closest("[data-seg]");
            SEGS[seg.dataset.seg].value = b.dataset.segValue;
            seg.querySelectorAll("[data-seg-value]").forEach((x) => x.setAttribute("aria-pressed", String(x === b)));
        } else if (t.closest("[data-srp-search]")) search();
        else if (t.closest("[data-srp-reset]")) {
            Object.keys(F).forEach((k) => (F[k].sel = []));
            if (evening()) evening().value = "";
            Object.keys(SEGS).forEach((k) => (SEGS[k].value = ""));
            form.querySelectorAll("[data-seg-value]").forEach((x) => x.setAttribute("aria-pressed", String(x.dataset.segValue === "")));
            Object.keys(F).forEach(draw);
            required.forEach((id) => {
                if (!F[id]) return;
                msEl(id).classList.remove("is-err");
                form.querySelector(`[data-ferr="${id}"]`).hidden = true;
            });
            onReset();
        }
    });
    form.addEventListener("change", (e) => {
        const t = e.target;
        const ms = t.closest(".srp-ms");
        if (!ms || t.type !== "checkbox") return;
        const id = ms.dataset.ms;
        const v = t.value;
        const value = (F[id].opts.find((o) => String(o[0]) === v) || [v])[0];
        set(id, t.checked ? F[id].sel.concat([value]) : F[id].sel.filter((x) => String(x) !== v));
        const c = [...ms.querySelectorAll(".srp-opt input")].find((x) => x.value === v);
        if (c) c.focus();
        if (required.includes(id) && F[id].sel.length) {
            ms.classList.remove("is-err");
            form.querySelector(`[data-ferr="${id}"]`).hidden = true;
        }
    });
    form.addEventListener("input", (e) => {
        const t = e.target;
        const ms = t.closest(".srp-ms");
        if (!ms || t.type !== "search") return;
        const q = t.value.trim().toLowerCase();
        let any = false;
        ms.querySelectorAll(".srp-opt").forEach((o) => {
            const hit = o.textContent.toLowerCase().includes(q);
            o.hidden = !hit;
            if (hit) any = true;
        });
        const none = ms.querySelector(".srp-none");
        if (none) none.remove();
        if (!any) ms.querySelector(".srp-mslist").insertAdjacentHTML("beforeend", '<div class="srp-none srp-muted">No match.</div>');
    });
    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && OPEN) close(true);
    });

    /* ---------- New / Old version ---------- */
    root.querySelectorAll("[data-srp-version]").forEach((button) =>
        button.addEventListener("click", () => {
            close();
            root.querySelectorAll("[data-srp-version]").forEach((b) => b.setAttribute("aria-pressed", String(b === button)));
            root.querySelectorAll("[data-srp-pane]").forEach((pane) => (pane.hidden = pane.dataset.srpPane !== button.dataset.srpVersion));
        })
    );

    /*
     * The Old tab's form follows this one: Search here fills its Tom Selects (silently, so its own
     * option lookups do not run) and runs its search; its Search fills this form and runs it here.
     */
    const OLD = { intake: "#intake_semester", attend: "#attendance_semester", course: "#course", group: "#group", type: "#student_type", status: "#group_student_status" };
    const oldForm = document.querySelector("#studentGroupSearchForm");
    const oldTs = (sel) => {
        const el = document.querySelector(sel);
        return el && el.tomselect;
    };
    let mirroring = false;
    function toOld() {
        Object.keys(SEGS).forEach((k) => {
            if (!segOn(k)) return;
            document.querySelectorAll(SEGS[k].old).forEach((r) => (r.checked = r.value === SEGS[k].value));
        });
        Object.keys(OLD).forEach((key) => {
            if (!F[key]) return;
            const ts = oldTs(OLD[key]);
            if (!ts) return;
            const values = F[key].sel.map(String);
            values.forEach((v) => {
                if (!ts.options[v]) ts.addOption({ value: v, text: labelOf(key, v) });
            });
            if (values.length && ts.isDisabled) ts.enable();
            ts.setValue(values, true);
        });
        const ev = oldTs("#evening_weekend");
        if (ev && evening()) ev.setValue({ Yes: "1", No: "0" }[evening().value] || "", true);
    }
    function fromOld() {
        Object.keys(SEGS).forEach((k) => {
            if (!segOn(k)) return;
            const on = document.querySelector(`${SEGS[k].old}:checked`);
            SEGS[k].value = on ? on.value : "";
            form.querySelectorAll(`[data-seg="${k}"] [data-seg-value]`).forEach((x) => x.setAttribute("aria-pressed", String(x.dataset.segValue === SEGS[k].value)));
        });
        Object.keys(OLD).forEach((key) => {
            if (!F[key]) return;
            const ts = oldTs(OLD[key]);
            if (!ts) return;
            const values = [].concat(ts.getValue() || []).map(String).filter(Boolean);
            values.forEach((v) => {
                if (!F[key].opts.some((o) => String(o[0]) === v)) F[key].opts.push([v, ts.options[v] ? ts.options[v].text : v]);
            });
            F[key].sel = values.map((v) => F[key].opts.find((o) => String(o[0]) === v)[0]);
        });
        const ev = oldTs("#evening_weekend");
        if (ev && evening()) evening().value = { 1: "Yes", 0: "No" }[ev.getValue()] || "";
        Object.keys(F).forEach(draw);
    }
    if (oldForm) {
        form.addEventListener("click", (e) => {
            if (mirroring || !e.target.closest("[data-srp-search]") || !query() || !valid(false)) return;
            toOld();
            mirroring = true;
            try {
                oldForm.requestSubmit ? oldForm.requestSubmit() : oldForm.dispatchEvent(new Event("submit", { bubbles: true, cancelable: true }));
            } finally {
                mirroring = false;
            }
        });
        oldForm.addEventListener(
            "submit",
            () => {
                if (mirroring) return;
                fromOld();
                mirroring = true;
                try {
                    search();
                } finally {
                    mirroring = false;
                }
            },
            true
        );
    }

    build();
    return { query, criteria, busy, close };
}
