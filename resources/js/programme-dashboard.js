/*
 * Programme Dashboard — module behaviour.
 *
 * Loaded only by `layout/programme-top-menu`, alongside
 * `resources/css/programme-dashboard.css`. Deliberately standalone and
 * dependency-free (no jQuery, no TomSelect, no Litepicker): the chrome, the
 * filters, the accordions and every dialog in this module are owned here, so
 * nothing this file does can leak into the rest of the app.
 *
 * Two kinds of filter live side by side:
 *   • status / course / module / group / date go back to the server, because
 *     they change which plans are in scope;
 *   • term pills, quick-view chips and free-text search run over the rendered
 *     rows in the browser, because every row already carries the data-*
 *     attributes they need.
 */

import Chart from "chart.js/auto";

const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ?? "";

/* ------------------------------------------------------------------ *
 * Small helpers
 * ------------------------------------------------------------------ */

const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));

/** Register a node/close pair so one document click can dismiss everything. */
const DISMISSABLE = [];
function registerDismissable(root, close) {
    DISMISSABLE.push({ root, close });
}
function closeAllDismissable(except = null) {
    DISMISSABLE.forEach(({ root, close }) => {
        if (root !== except && root.isConnected) close();
    });
}

document.addEventListener("click", (event) => {
    DISMISSABLE.forEach(({ root, close }) => {
        if (root.isConnected && !root.contains(event.target)) close();
    });
});

document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeAllDismissable();
});

let toastTimer = null;
function toast(message, isError = false) {
    const el = $("[data-pgd-toast]");
    if (!el) return;

    el.classList.toggle("is-error", !!isError);
    el.querySelector("em").textContent = message;
    el.hidden = false;

    if (toastTimer) clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { el.hidden = true; }, 2800);
}

function post(url, formOrData) {
    const body = formOrData instanceof FormData ? formOrData : (() => {
        const fd = new FormData();
        Object.entries(formOrData || {}).forEach(([k, v]) => fd.append(k, v));
        return fd;
    })();

    return fetch(url, {
        method: "POST",
        body,
        headers: { "X-CSRF-TOKEN": CSRF, "X-Requested-With": "XMLHttpRequest", Accept: "application/json" },
    });
}

function spin(button, on) {
    if (!button) return;
    button.disabled = !!on;
    const svg = button.querySelector(".pgd-btn__spin");
    if (svg) svg.style.display = on ? "inline-block" : "none";
}

/* ------------------------------------------------------------------ *
 * Header chrome — nav dropdown, profile menu
 * ------------------------------------------------------------------ */

function initChrome() {
    $$("[data-pgd-nav-group]").forEach((group) => {
        const trigger = $("[data-pgd-nav-toggle]", group);
        if (!trigger) return;

        const close = () => {
            group.classList.remove("is-open");
            trigger.setAttribute("aria-expanded", "false");
        };

        trigger.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            const wasOpen = group.classList.contains("is-open");
            closeAllDismissable();
            group.classList.toggle("is-open", !wasOpen);
            trigger.setAttribute("aria-expanded", wasOpen ? "false" : "true");
        });

        registerDismissable(group, close);
    });

    const profile = $("[data-pgd-profile]");
    if (profile) {
        const trigger = $("[data-pgd-profile-toggle]", profile);
        const close = () => profile.classList.remove("is-open");

        trigger?.addEventListener("click", (event) => {
            event.preventDefault();
            event.stopPropagation();
            const wasOpen = profile.classList.contains("is-open");
            closeAllDismissable();
            profile.classList.toggle("is-open", !wasOpen);
        });

        registerDismissable(profile, close);
    }
}

/* Term switcher on the tutor screens: a picker rather than a pill per term. */
function initTermPicker() {
    const picker = $("[data-pgd-termpicker]");
    if (!picker) return;

    const trigger = $("[data-pgd-termpicker-toggle]", picker);
    const search = $("[data-pgd-termpicker-search]", picker);
    const close = () => {
        picker.classList.remove("is-open");
        trigger?.setAttribute("aria-expanded", "false");
    };

    trigger?.addEventListener("click", (event) => {
        event.preventDefault();
        event.stopPropagation();
        const wasOpen = picker.classList.contains("is-open");
        closeAllDismissable();
        picker.classList.toggle("is-open", !wasOpen);
        trigger.setAttribute("aria-expanded", wasOpen ? "false" : "true");
        if (!wasOpen && search) {
            search.value = "";
            $$(".pgd-termpicker__opt", picker).forEach((opt) => { opt.hidden = false; });
            search.focus();
        }
    });

    search?.addEventListener("input", () => {
        const needle = search.value.trim().toLowerCase();
        $$(".pgd-termpicker__opt", picker).forEach((opt) => {
            opt.hidden = needle !== "" && !opt.textContent.toLowerCase().includes(needle);
        });
    });

    registerDismissable(picker, close);
}

function initClock() {
    const el = $("#theClock");
    if (!el) return;

    const pad = (n) => (n < 10 ? "0" : "") + n;
    const tick = () => {
        const now = new Date();
        el.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
    };

    tick();
    setInterval(tick, 1000);
}

/* ------------------------------------------------------------------ *
 * Dashboard
 * ------------------------------------------------------------------ */

function initDashboard() {
    const slotsWrap = $("[data-pgd-slots]");
    if (!slotsWrap) return;

    const configEl = $("[data-pgd-config]");
    const config = configEl ? JSON.parse(configEl.textContent) : { routes: {}, candidates: [], busyByTime: {}, absentUserIds: [] };

    const body = $("[data-pgd-slots-body]", slotsWrap);

    /* ---------------- client-side filter state ---------------- */

    const state = {
        terms: new Set($$("[data-pgd-term]").map((b) => b.dataset.pgdTerm)),
        quick: null,
        search: "",
    };

    const QUICK_LABELS = {
        needs: "Needs action",
        ongoing: "Ongoing now",
        nofeed: "Attendance not fed",
        online: "Online",
        campus: "On campus",
        unknown: "Not started",
    };

    const rowMatchesQuick = (row) => {
        const kind = row.dataset.kind;
        const needs = row.dataset.needs === "1";
        const online = row.dataset.online === "1";

        switch (state.quick) {
            case "needs": return needs || kind === "notstarted";
            case "ongoing": return kind === "ongoing";
            case "nofeed": return needs;
            case "online": return online;
            case "campus": return !online;
            case "unknown": return kind === "notstarted";
            default: return true;
        }
    };

    function applyFilters() {
        let visible = 0;
        let total = 0;

        // A row whose term has no pill on the bar is never filtered out by the
        // term control — there would be no way to switch it back on.
        const pilled = new Set($$("[data-pgd-term]").map((el) => el.dataset.pgdTerm));
        const termAllows = (row) => !pilled.has(row.dataset.term) || state.terms.has(row.dataset.term);

        $$("[data-pgd-slot]", body).forEach((slot) => {
            let shown = 0;
            let finished = 0;
            let alerts = 0;

            $$("[data-pgd-row]", slot).forEach((row) => {
                total += 1;

                const ok = termAllows(row)
                    && rowMatchesQuick(row)
                    && (state.search === "" || (row.dataset.search || "").includes(state.search));

                row.hidden = !ok;
                if (!ok) return;

                shown += 1;
                visible += 1;
                if (row.dataset.kind === "completed") finished += 1;
                if (row.dataset.kind === "notstarted") alerts += 1;
            });

            slot.hidden = shown === 0;

            const countLabel = slot.querySelector(".pgd-slot__meta span:first-child");
            if (countLabel) countLabel.textContent = `${shown} ${shown === 1 ? "class" : "classes"}`;

            const finishedLabel = slot.querySelector(".pgd-slot__count");
            if (finishedLabel) finishedLabel.textContent = `${finished}/${shown} finished`;

            const alertLabel = slot.querySelector(".pgd-slot__alert");
            if (alertLabel) {
                alertLabel.hidden = alerts === 0;
                alertLabel.textContent = `${alerts} not started`;
            }
        });

        // Staff cards drop anyone whose terms are all switched off.
        $$("[data-pgd-person]").forEach((person) => {
            const termIds = (person.dataset.terms || "").split(",").filter(Boolean);
            person.hidden = termIds.length > 0
                && termIds.some((id) => pilled.has(id))
                && !termIds.some((id) => state.terms.has(id));

            $$(".pgd-person__term", person).forEach((chip) => {
                chip.hidden = !state.terms.has(chip.dataset.term);
            });
        });

        const anyFilter = state.quick !== null || state.search !== "" || state.terms.size !== $$("[data-pgd-term]").length;

        const bar = $("[data-pgd-filterbar]");
        if (bar) {
            bar.hidden = !anyFilter;
            $("[data-pgd-filterlabel]", bar).textContent = state.quick ? QUICK_LABELS[state.quick] : (state.search !== "" ? "Search" : "Filtered by term");
            $("[data-pgd-visible]", bar).textContent = visible;
            $("[data-pgd-total]", bar).textContent = total;
        }

        const tail = $("[data-pgd-rowsearch-tail]");
        if (tail) {
            tail.hidden = state.search === "";
            $("[data-pgd-matchlabel]", tail).textContent = `${visible} ${visible === 1 ? "match" : "matches"}`;
        }

        const clientEmpty = $("[data-pgd-empty-client]", body);
        const hasServerRows = $$("[data-pgd-slot]", body).length > 0;
        if (clientEmpty) clientEmpty.hidden = !(hasServerRows && visible === 0);
    }

    /* ---------------- term pills ---------------- */

    const termStrip = $("[data-pgd-terms]");

    function bindTermPill(chip) {
        chip.addEventListener("click", () => {
            const id = chip.dataset.pgdTerm;

            // The last enabled term cannot be switched off — an empty board
            // reads as a broken page rather than a filter.
            if (state.terms.has(id) && state.terms.size === 1) return;

            if (state.terms.has(id)) {
                state.terms.delete(id);
                chip.classList.remove("is-on");
            } else {
                state.terms.add(id);
                chip.classList.add("is-on");
            }

            applyFilters();
        });
    }

    /**
     * Browsing to another date can change which terms have classes, so the
     * pills are re-rendered from the payload rather than just re-labelled —
     * a missing pill would silently filter every row of that term away.
     * Terms the user had switched off stay off if they are still on the bar.
     */
    function paintTermPills(chips) {
        if (!termStrip) return;

        const turnedOff = new Set(
            $$("[data-pgd-term]", termStrip)
                .filter((el) => !el.classList.contains("is-on"))
                .map((el) => el.dataset.pgdTerm),
        );

        termStrip.innerHTML = chips.map((chip) => `
            <button type="button" class="pgd-term ${turnedOff.has(String(chip.id)) ? "" : "is-on"}" data-pgd-term="${chip.id}" style="--pgd-term-dot: ${escapeAttr(chip.dot)};">
                <span class="pgd-term__dot"></span>
                <span class="pgd-term__name">${escapeHtml(chip.name)}</span>
                <span class="pgd-term__rate">${escapeHtml(chip.rate)}%</span>
                <span class="pgd-term__count">· ${escapeHtml(chip.today)} today</span>
            </button>
        `).join("");

        state.terms = new Set(
            $$("[data-pgd-term]", termStrip)
                .filter((el) => el.classList.contains("is-on"))
                .map((el) => el.dataset.pgdTerm),
        );

        // Everything hidden would look like a broken page; fall back to all on.
        if (state.terms.size === 0) {
            $$("[data-pgd-term]", termStrip).forEach((el) => {
                el.classList.add("is-on");
                state.terms.add(el.dataset.pgdTerm);
            });
        }

        $$("[data-pgd-term]", termStrip).forEach(bindTermPill);
    }

    /* ---------------- server-side filters ---------------- */

    function currentServerFilters() {
        return {
            planClassStatus: $("#planClassStatus")?.value ?? "All",
            planCourseId: $("#planCourseId")?.value ?? 0,
            theClassDate: $("#theClassDate")?.value ?? "",
            planModuleCreationId: $("#planModuleCreationId")?.value ?? 0,
            planGroupId: $("#planGroupId")?.value ?? 0,
        };
    }

    function reload() {
        slotsWrap.classList.add("is-loading");

        post(config.routes.classInfo, currentServerFilters())
            .then((response) => {
                if (!response.ok) throw new Error("Request failed");
                return response.json();
            })
            .then((payload) => {
                const res = payload.res;

                body.innerHTML = res.slots;

                const summary = $("[data-pgd-summary]");
                if (summary) summary.textContent = res.summary;

                Object.entries(res.stats || {}).forEach(([key, value]) => {
                    $$(`[data-pgd-stat="${key}"]`).forEach((el) => { el.textContent = value; });
                });
                syncQuickDisabled();

                paintTermPills(res.termChips || []);

                // Attendance follows the date / course / module / group filters
                // (not the class-status ones — see the partial's note).
                const attendance = $("[data-pgd-attendance]");
                if (attendance && typeof res.attendance === "string") {
                    attendance.innerHTML = res.attendance;
                    initDonut();
                }

                const tutorHolder = $(".tutorWrap .theHolder");
                if (tutorHolder) tutorHolder.innerHTML = res.tutors.html;
                $$(".tutorCount").forEach((el) => { el.textContent = res.tutors.count; });

                const ptutorHolder = $(".personalTutorWrap .theHolder");
                if (ptutorHolder) ptutorHolder.innerHTML = res.ptutors.html;
                $$(".personalTutorCount").forEach((el) => { el.textContent = res.ptutors.count; });

                applyFilters();
            })
            .catch(() => toast("Could not load classes for those filters.", true))
            .finally(() => slotsWrap.classList.remove("is-loading"));
    }

    $("#planClassStatus")?.addEventListener("change", reload);
    $("#planCourseId")?.addEventListener("change", reload);

    /* ---------------- module / group popovers ---------------- */

    $$('[data-pgd-pop="modules"], [data-pgd-pop="groups"]').forEach((pop) => {
        const trigger = $("[data-pgd-pop-toggle]", pop);
        const label = $("[data-pgd-pop-label]", pop);
        const search = $("[data-pgd-pop-search]", pop);
        const hidden = $("input[type=hidden]", pop);
        const allLabel = pop.dataset.pgdPop === "modules" ? "All modules" : "All groups";

        const close = () => pop.classList.remove("is-open");

        trigger.addEventListener("click", (event) => {
            event.stopPropagation();
            const wasOpen = pop.classList.contains("is-open");
            closeAllDismissable();
            pop.classList.toggle("is-open", !wasOpen);
            if (!wasOpen) {
                search.value = "";
                $$("[data-value]", pop).forEach((opt) => { opt.hidden = false; });
                search.focus();
            }
        });

        search.addEventListener("input", () => {
            const needle = search.value.trim().toLowerCase();
            $$("[data-value]", pop).forEach((opt) => {
                opt.hidden = needle !== "" && !opt.textContent.toLowerCase().includes(needle);
            });
        });

        pop.addEventListener("click", (event) => {
            const opt = event.target.closest("[data-value]");
            if (!opt) return;

            $$("[data-value]", pop).forEach((o) => o.classList.toggle("is-on", o === opt));
            hidden.value = opt.dataset.value;
            label.textContent = opt.dataset.value === "0" ? allLabel : opt.querySelector("span").textContent;
            pop.classList.toggle("is-set", opt.dataset.value !== "0");
            close();
            reload();
        });

        registerDismissable(pop, close);
    });

    /* ---------------- calendar ---------------- */

    (function initCalendar() {
        const pop = $('[data-pgd-pop="calendar"]');
        if (!pop) return;

        const trigger = $("[data-pgd-pop-toggle]", pop);
        const label = $("[data-pgd-cal-label]", pop);
        const hidden = $("#theClassDate");
        const grid = $("[data-pgd-cal-grid]", pop);
        const title = $("[data-pgd-cal-title]", pop);

        const MONTHS = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        const pad = (n) => (n < 10 ? "0" : "") + n;

        const selected = new Date(`${hidden.dataset.iso}T00:00:00`);
        let cursor = new Date(selected.getFullYear(), selected.getMonth(), 1);

        function paint() {
            title.textContent = `${MONTHS[cursor.getMonth()]} ${cursor.getFullYear()}`;
            grid.innerHTML = "";

            // Monday-first, matching the design's MON…SUN header row.
            const firstDow = (new Date(cursor.getFullYear(), cursor.getMonth(), 1).getDay() + 6) % 7;
            const days = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0).getDate();

            for (let blank = 0; blank < firstDow; blank += 1) {
                const filler = document.createElement("button");
                filler.type = "button";
                filler.className = "is-blank";
                filler.tabIndex = -1;
                grid.appendChild(filler);
            }

            for (let day = 1; day <= days; day += 1) {
                const button = document.createElement("button");
                button.type = "button";
                button.textContent = String(day);
                button.dataset.day = String(day);

                const isSelected = day === selected.getDate()
                    && cursor.getMonth() === selected.getMonth()
                    && cursor.getFullYear() === selected.getFullYear();
                if (isSelected) button.classList.add("is-on");

                grid.appendChild(button);
            }
        }

        const close = () => pop.classList.remove("is-open");

        trigger.addEventListener("click", (event) => {
            event.stopPropagation();
            const wasOpen = pop.classList.contains("is-open");
            closeAllDismissable();
            pop.classList.toggle("is-open", !wasOpen);
            if (!wasOpen) {
                cursor = new Date(selected.getFullYear(), selected.getMonth(), 1);
                paint();
            }
        });

        $("[data-pgd-cal-prev]", pop).addEventListener("click", (event) => {
            event.stopPropagation();
            cursor = new Date(cursor.getFullYear(), cursor.getMonth() - 1, 1);
            paint();
        });

        $("[data-pgd-cal-next]", pop).addEventListener("click", (event) => {
            event.stopPropagation();
            cursor = new Date(cursor.getFullYear(), cursor.getMonth() + 1, 1);
            paint();
        });

        grid.addEventListener("click", (event) => {
            const button = event.target.closest("[data-day]");
            if (!button) return;

            selected.setFullYear(cursor.getFullYear(), cursor.getMonth(), Number(button.dataset.day));

            const iso = `${selected.getFullYear()}-${pad(selected.getMonth() + 1)}-${pad(selected.getDate())}`;
            const uk = `${pad(selected.getDate())}-${pad(selected.getMonth() + 1)}-${selected.getFullYear()}`;

            hidden.value = uk;
            hidden.dataset.iso = iso;
            label.textContent = uk;
            close();
            reload();
        });

        registerDismissable(pop, close);
        paint();
    })();

    /* ---------------- term pills, quick chips, search ---------------- */

    $$("[data-pgd-term]").forEach(bindTermPill);

    /**
     * A quick view with nothing behind it is a dead end, so it is disabled
     * rather than left clickable. Re-run whenever the counts change; if the
     * active view is the one that emptied, drop back to showing everything.
     */
    function syncQuickDisabled() {
        let clearedActive = false;

        $$("[data-pgd-quick]").forEach((el) => {
            const counter = $("[data-pgd-stat]", el);
            if (!counter) return;

            const empty = Number(counter.textContent.trim()) === 0;
            el.disabled = empty;

            if (empty && el.dataset.pgdQuick === state.quick) clearedActive = true;
        });

        if (clearedActive) {
            state.quick = null;
            $$("[data-pgd-quick]").forEach((el) => el.classList.remove("is-on"));
        }
    }

    function setQuick(next) {
        state.quick = state.quick === next ? null : next;

        $$("[data-pgd-quick]").forEach((el) => {
            el.classList.toggle("is-on", el.dataset.pgdQuick === state.quick);
        });

        applyFilters();
    }

    $$("[data-pgd-quick]").forEach((el) => {
        el.addEventListener("click", () => {
            if (el.disabled) return;
            setQuick(el.dataset.pgdQuick);
        });
    });

    const rowSearch = $("[data-pgd-rowsearch]");
    rowSearch?.addEventListener("input", () => {
        state.search = rowSearch.value.trim().toLowerCase();
        applyFilters();
    });

    $("[data-pgd-rowsearch-clear]")?.addEventListener("click", () => {
        rowSearch.value = "";
        state.search = "";
        applyFilters();
    });

    document.addEventListener("click", (event) => {
        const reset = event.target.closest("[data-pgd-reset]");
        if (!reset) return;

        state.quick = null;
        state.search = "";
        state.terms = new Set($$("[data-pgd-term]").map((b) => b.dataset.pgdTerm));

        $$("[data-pgd-term]").forEach((b) => b.classList.add("is-on"));
        $$("[data-pgd-quick]").forEach((b) => b.classList.remove("is-on"));
        if (rowSearch) rowSearch.value = "";

        const status = $("#planClassStatus");
        const course = $("#planCourseId");
        if (status) status.value = "All";
        if (course) course.value = "0";

        $$('[data-pgd-pop="modules"], [data-pgd-pop="groups"]').forEach((pop) => {
            const hidden = $("input[type=hidden]", pop);
            if (hidden.value === "0") return;
            hidden.value = "0";
            pop.classList.remove("is-set");
            $("[data-pgd-pop-label]", pop).textContent = pop.dataset.pgdPop === "modules" ? "All modules" : "All groups";
            $$("[data-value]", pop).forEach((o) => o.classList.toggle("is-on", o.dataset.value === "0"));
        });

        reload();
    });

    $$("[data-pgd-scroll]").forEach((el) => {
        el.addEventListener("click", () => {
            const target = $(el.dataset.pgdScroll);
            if (target) window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - 130, behavior: "smooth" });
        });
    });

    /* ---------------- slot accordions + row menus ---------------- */

    body.addEventListener("click", (event) => {
        const toggle = event.target.closest("[data-pgd-slot-toggle]");
        if (toggle) {
            toggle.closest("[data-pgd-slot]").classList.toggle("is-closed");
            return;
        }

        const kebab = event.target.closest("[data-pgd-menu-toggle]");
        if (kebab) {
            event.stopPropagation();
            const menu = kebab.closest("[data-pgd-menu]");
            const wasOpen = menu.classList.contains("is-open");
            $$("[data-pgd-menu]", body).forEach((m) => m.classList.remove("is-open"));
            menu.classList.toggle("is-open", !wasOpen);
            return;
        }

        if (!event.target.closest(".pgd-menu")) {
            $$("[data-pgd-menu]", body).forEach((m) => m.classList.remove("is-open"));
        }
    });

    document.addEventListener("click", (event) => {
        if (!event.target.closest("[data-pgd-menu]")) {
            $$("[data-pgd-menu]", body).forEach((m) => m.classList.remove("is-open"));
        }
    });

    /* ---------------- modals ---------------- */

    function openModal(name) {
        const modal = $(`[data-pgd-modal="${name}"]`);
        if (modal) modal.hidden = false;
    }

    function closeModal(modal) {
        if (modal) modal.hidden = true;
    }

    document.addEventListener("click", (event) => {
        const opener = event.target.closest("[data-pgd-open]");
        if (opener) {
            openModal(opener.dataset.pgdOpen);
            return;
        }

        const closer = event.target.closest("[data-pgd-modal-close]");
        if (closer) closeModal(closer.closest("[data-pgd-modal]"));
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape") $$("[data-pgd-modal]").forEach((m) => { m.hidden = true; });
    });

    /** Copies the clicked row's identity into whichever dialog is opening. */
    function fillModal(modal, data) {
        $$("[data-pgd-active-module]", modal).forEach((el) => { el.textContent = data.module || ""; });
        $$("[data-pgd-active-group]", modal).forEach((el) => { el.textContent = data.group || ""; });
        $$("[data-pgd-active-meta]", modal).forEach((el) => { el.textContent = data.meta || ""; });
    }

    /* ---------------- cancel class ---------------- */

    const cancelModal = $('[data-pgd-modal="cancel"]');
    const cancelForm = $("#cancelClassForm");

    body.addEventListener("click", (event) => {
        const trigger = event.target.closest(".cancelClass");
        if (!trigger) return;

        cancelForm.querySelector('[name="plan_id"]').value = trigger.dataset.planid;
        cancelForm.querySelector('[name="plans_date_list_id"]').value = trigger.dataset.plandateid;
        cancelForm.querySelector("#canceled_reason").value = "";
        $(".error-canceled_reason", cancelForm).textContent = "";
        fillModal(cancelModal, trigger.dataset);
        openModal("cancel");
    });

    cancelForm?.addEventListener("submit", (event) => {
        event.preventDefault();
        const button = $("#saveCancelBtn");
        $(".error-canceled_reason", cancelForm).textContent = "";
        spin(button, true);

        post(config.routes.cancel, new FormData(cancelForm))
            .then(async (response) => {
                if (response.status === 422) {
                    const payload = await response.json();
                    Object.entries(payload.errors || {}).forEach(([key, value]) => {
                        const slot = $(`.error-${key}`, cancelForm);
                        if (slot) slot.textContent = Array.isArray(value) ? value[0] : value;
                    });
                    return;
                }
                if (!response.ok) throw new Error("Request failed");

                closeModal(cancelModal);
                toast("Class cancelled · notifications queued");
                reload();
            })
            .catch(() => toast("Could not cancel that class.", true))
            .finally(() => spin(button, false));
    });

    /* ---------------- end class ---------------- */

    const endModal = $('[data-pgd-modal="end"]');
    const endForm = $("#endClassModalForm");

    body.addEventListener("click", (event) => {
        const trigger = event.target.closest(".endClassBtn");
        if (!trigger) return;

        endForm.querySelector(".plan_date_list_id").value = trigger.dataset.plandateid;
        endForm.querySelector(".attendance_information_id").value = trigger.dataset.attendanceinfo;
        fillModal(endModal, trigger.dataset);
        openModal("end");
    });

    endForm?.addEventListener("submit", (event) => {
        event.preventDefault();
        const button = $("#endClassBtn");
        spin(button, true);

        post(config.routes.end, new FormData(endForm))
            .then((response) => {
                if (!response.ok) throw new Error("Request failed");
                closeModal(endModal);
                toast("Class ended");
                reload();
            })
            .catch(() => toast("Could not end that class.", true))
            .finally(() => spin(button, false));
    });

    /* ---------------- swap tutor ---------------- */

    const swapModal = $('[data-pgd-modal="swap"]');
    const swapForm = $("#proxyClassForm");
    const swapList = $("[data-pgd-swap-list]");
    const swapSearch = $("[data-pgd-swap-search]");
    const swapConfirm = $("#saveReAsignBtn");
    let swapContext = null;

    function paintSwapCandidates() {
        if (!swapContext) return;

        const needle = (swapSearch.value || "").trim().toLowerCase();
        const busy = config.busyByTime[swapContext.time] || {};
        const absent = new Set((config.absentUserIds || []).map(String));
        const picked = swapForm.querySelector("#proxy_tutor_id").value;

        const rows = config.candidates
            .filter((c) => String(c.id) !== String(swapContext.orgTutorId))
            .filter((c) => needle === "" || c.name.toLowerCase().includes(needle))
            .map((c) => {
                const isAbsent = absent.has(String(c.id));
                const clash = !isAbsent && Object.prototype.hasOwnProperty.call(busy, String(c.id));

                return {
                    ...c,
                    rank: isAbsent ? 2 : (clash ? 1 : 0),
                    status: isAbsent ? "Absent" : (clash ? "Clash" : "Available"),
                    statusClass: isAbsent ? "absent" : (clash ? "clash" : "free"),
                    meta: isAbsent
                        ? "Not clocked in today"
                        : (clash ? `Teaching ${busy[String(c.id)]} at this time` : (c.mobile || c.role || "No class at this time")),
                };
            })
            .sort((a, b) => a.rank - b.rank || a.name.localeCompare(b.name));

        if (!rows.length) {
            swapList.innerHTML = '<div class="pgd-swap__empty">No staff match that search.</div>';
            return;
        }

        swapList.innerHTML = rows.map((c) => `
            <button type="button" class="pgd-swap__cand ${String(c.id) === String(picked) ? "is-on" : ""}" data-candidate="${c.id}" data-name="${escapeAttr(c.name)}">
                <span class="pgd-avatar" style="background: ${escapeAttr(c.color || "#0E5A61")};">${c.photo ? `<img src="${escapeAttr(c.photo)}" alt="">` : escapeHtml(c.initials)}</span>
                <span class="pgd-swap__cand-copy">
                    <span class="pgd-swap__cand-name">${escapeHtml(c.name)}</span>
                    <span class="pgd-swap__cand-meta">${escapeHtml(c.meta)}</span>
                </span>
                <span class="pgd-swap__cand-pill pgd-swap__cand-pill--${c.statusClass}">${c.status}</span>
            </button>
        `).join("");
    }

    body.addEventListener("click", (event) => {
        const trigger = event.target.closest(".proxyClass");
        if (!trigger) return;

        swapContext = {
            orgTutorId: trigger.dataset.tutorid,
            time: (trigger.dataset.meta || "").split(" · ")[0],
        };

        swapForm.querySelector('[name="plan_id"]').value = trigger.dataset.planid;
        swapForm.querySelector('[name="plans_date_list_id"]').value = trigger.dataset.plandateid;
        swapForm.querySelector('[name="org_tutor_id"]').value = trigger.dataset.tutorid;
        swapForm.querySelector("#proxy_tutor_id").value = "";
        swapForm.querySelector("#proxy_reason").value = "";
        $(".error-proxy_reason", swapForm).textContent = "";
        $(".error-proxy_tutor_id", swapForm).textContent = "";

        $("[data-pgd-swap-name]", swapModal).textContent = trigger.dataset.tutorname || "";
        $("[data-pgd-swap-initials]", swapModal).textContent = trigger.dataset.tutorinitials || "";
        $("[data-pgd-swap-status]", swapModal).textContent = trigger.dataset.tutorstatus || "";
        $("[data-pgd-swap-footer]", swapModal).textContent = "Pick a staff member to cover this class.";
        swapConfirm.disabled = true;
        swapSearch.value = "";

        fillModal(swapModal, trigger.dataset);
        paintSwapCandidates();
        openModal("swap");
    });

    swapSearch?.addEventListener("input", paintSwapCandidates);

    swapList?.addEventListener("click", (event) => {
        const card = event.target.closest("[data-candidate]");
        if (!card) return;

        swapForm.querySelector("#proxy_tutor_id").value = card.dataset.candidate;
        $$("[data-candidate]", swapList).forEach((c) => c.classList.toggle("is-on", c === card));
        swapConfirm.disabled = false;

        const from = $("[data-pgd-swap-name]", swapModal).textContent;
        $("[data-pgd-swap-footer]", swapModal).textContent =
            `Both names stay on the class record — ${from} struck through, ${card.dataset.name} shown as covering.`;
    });

    swapForm?.addEventListener("submit", (event) => {
        event.preventDefault();

        const orgTutorId = swapForm.querySelector('[name="org_tutor_id"]').value;
        const proxyTutorId = swapForm.querySelector("#proxy_tutor_id").value;

        if (orgTutorId === proxyTutorId) {
            $(".error-proxy_tutor_id", swapForm).textContent = "You cannot assign the same tutor as a proxy.";
            return;
        }

        $(".error-proxy_reason", swapForm).textContent = "";
        $(".error-proxy_tutor_id", swapForm).textContent = "";
        spin(swapConfirm, true);

        post(config.routes.reassign, new FormData(swapForm))
            .then(async (response) => {
                if (response.status === 422) {
                    const payload = await response.json();
                    Object.entries(payload.errors || {}).forEach(([key, value]) => {
                        const slot = $(`.error-${key}`, swapForm);
                        if (slot) slot.textContent = Array.isArray(value) ? value[0] : value;
                    });
                    return;
                }
                if (!response.ok) throw new Error("Request failed");

                closeModal(swapModal);
                toast("Class swapped · both tutors kept on the record");
                reload();
            })
            .catch(() => toast("Could not re-assign that class.", true))
            .finally(() => spin(swapConfirm, false));
    });

    /* ---------------- follow-up queue ---------------- */

    document.addEventListener("click", (event) => {
        const one = event.target.closest("[data-pgd-remind]");
        if (one) {
            one.disabled = true;
            post(config.routes.remind, { tutor_id: one.dataset.pgdRemind })
                .then((response) => response.json())
                .then((payload) => {
                    one.textContent = "Reminded";
                    toast(payload.message || "Reminder sent.");
                })
                .catch(() => {
                    one.disabled = false;
                    toast("Could not send that reminder.", true);
                });
            return;
        }

        const all = event.target.closest("[data-pgd-remind-all]");
        if (all) {
            all.disabled = true;
            post(config.routes.remind, {})
                .then((response) => response.json())
                .then((payload) => {
                    $$("[data-pgd-remind]").forEach((b) => { b.disabled = true; b.textContent = "Reminded"; });
                    toast(payload.message || "Reminders sent.");
                    closeModal($('[data-pgd-modal="followup"]'));
                })
                .catch(() => toast("Could not send those reminders.", true))
                .finally(() => { all.disabled = false; });
        }
    });

    /* ---------------- absence panel (infinite scroll) ---------------- */

    (function initAbsence() {
        const list = $("[data-pgd-absence-list]");
        if (!list) return;

        const search = $("[data-pgd-absence-search]");
        const toggle = $("[data-pgd-absence-toggle]");
        const spinner = $("[data-pgd-absence-spinner]", list);
        const countChip = $(".pgd-absence__count");
        const pageSize = config.absencePageSize || 12;

        let loading = false;
        let query = "";
        let searchTimer = null;

        const hasMore = () => list.dataset.hasMore === "1";

        // The footer control is a link to the full live-attendance screen, so
        // it only needs its count kept in step with what the panel is showing.
        function syncToggle() {
            if (!toggle) return;

            const total = Number(list.dataset.total || 0);
            toggle.hidden = total === 0;
            toggle.textContent = `View all ${total}`;
        }

        function emptyNotice() {
            return `
                <div class="pgd-note pgd-note--ok" data-pgd-absence-empty>
                    <span class="pgd-note__icon">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><path d="m8.5 12.5 2.5 2.5 4.5-5"></path></svg>
                    </span>
                    <span>
                        <strong>${query === "" ? "Nobody absent" : "No match"}</strong>
                        ${query === "" ? "Everyone expected on site today has clocked in." : `No absent staff member matches “${escapeHtml(query)}”.`}
                    </span>
                </div>
            `;
        }

        function load({ page, replace = false }) {
            if (loading) return Promise.resolve();
            loading = true;
            if (spinner) spinner.hidden = false;

            const params = new URLSearchParams({
                page: String(page),
                per_page: String(pageSize),
                q: query,
                date: config.date || "",
            });

            return fetch(`${config.routes.absentRows}?${params.toString()}`, {
                headers: { Accept: "application/json", "X-Requested-With": "XMLHttpRequest" },
            })
                .then((response) => {
                    if (!response.ok) throw new Error("Request failed");
                    return response.json();
                })
                .then((payload) => {
                    if (replace) {
                        $$("[data-pgd-absence-row]", list).forEach((row) => row.remove());
                        $("[data-pgd-absence-empty]", list)?.remove();
                    }

                    if (spinner) spinner.insertAdjacentHTML("beforebegin", payload.html);

                    list.dataset.page = String(payload.page);
                    list.dataset.total = String(payload.total);
                    list.dataset.loaded = String(payload.loaded);
                    list.dataset.hasMore = payload.hasMore ? "1" : "0";

                    if (payload.total === 0 && !$("[data-pgd-absence-empty]", list)) {
                        spinner?.insertAdjacentHTML("beforebegin", emptyNotice());
                    }

                    if (countChip && query === "") countChip.textContent = payload.total;
                    syncToggle();
                })
                .catch(() => toast("Could not load more absent staff.", true))
                .finally(() => {
                    loading = false;
                    if (spinner) spinner.hidden = true;
                });
        }

        list.addEventListener("scroll", () => {
            if (loading || !hasMore()) return;
            if (list.scrollTop + list.clientHeight < list.scrollHeight - 48) return;
            load({ page: Number(list.dataset.page || 1) + 1 });
        });

        search?.addEventListener("input", () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                query = search.value.trim();
                list.scrollTop = 0;
                load({ page: 1, replace: true });
            }, 250);
        });

        syncToggle();
    })();

    /* ---------------- attendance donut ---------------- */

    /**
     * Attendance doughnut — one segment per term, sized by that term's share of
     * the students present, with the selected term pulled slightly proud.
     *
     * The term rows below the chart are the data source: they already carry
     * name / rate / expected / present / colour, so the chart and the list can
     * never drift apart. Re-run after the card re-renders; the previous Chart
     * instance is destroyed first or it keeps its old canvas alive.
     */
    let donutChart = null;

    function initDonut() {
        const canvas = $("[data-pgd-donut]");
        const rows = $$("[data-pgd-termrow]");

        if (donutChart) {
            donutChart.destroy();
            donutChart = null;
        }

        if (!canvas || !rows.length) return;

        const terms = rows.map((row) => ({
            id: row.dataset.pgdTermrow,
            name: row.dataset.name,
            rate: row.dataset.rate,
            present: row.dataset.present,
            expected: row.dataset.expected,
            color: row.dataset.color,
            // Segments are sized by each term's rate, not by its share of the
            // students present: term sizes differ by two orders of magnitude
            // here, and a composition ring collapses into a solid circle. The
            // tooltip still reports the true present/expected counts.
            value: Number(row.dataset.rateValue) || 0,
        }));

        const select = (termId) => {
            const row = rows.find((r) => r.dataset.pgdTermrow === termId);
            if (!row) return;

            rows.forEach((r) => r.classList.toggle("is-on", r === row));

            $("[data-pgd-term-rate]").textContent = row.dataset.rate;
            $("[data-pgd-term-name]").textContent = row.dataset.name;
            $("[data-pgd-term-dates]").textContent = row.dataset.dates;
            $("[data-pgd-term-expected]").textContent = row.dataset.expected;
            $("[data-pgd-term-present]").textContent = row.dataset.present;

            if (donutChart) {
                donutChart.data.datasets[0].offset = terms.map((t) => (t.id === termId ? 4 : 0));
                donutChart.update();
            }
        };

        donutChart = new Chart(canvas.getContext("2d"), {
            type: "doughnut",
            data: {
                labels: terms.map((t) => t.name),
                datasets: [{
                    data: terms.map((t) => t.value),
                    backgroundColor: terms.map((t) => t.color),
                    borderColor: "#FFFFFF",
                    borderWidth: 2,
                    hoverBorderColor: "#FFFFFF",
                    offset: terms.map((t, i) => (i === 0 ? 4 : 0)),
                }],
            },
            options: {
                cutout: "72%",
                // Sized by the 122px .pgd-donut wrapper rather than canvas
                // attributes, so it stays crisp on retina.
                responsive: true,
                maintainAspectRatio: false,
                animation: { duration: 320 },
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false },
                },
                onHover: (event, elements) => {
                    const tip = $("[data-pgd-tip]");
                    if (!tip) return;

                    if (!elements.length) {
                        tip.hidden = true;
                        return;
                    }

                    const t = terms[elements[0].index];
                    $("[data-pgd-tip-name]", tip).textContent = t.name;
                    $("[data-pgd-tip-dot]", tip).style.background = t.color;
                    $("[data-pgd-tip-value]", tip).textContent = `${t.present} of ${t.expected} present · ${t.rate}`;
                    tip.hidden = false;
                },
                onClick: (event, elements) => {
                    if (elements.length) select(terms[elements[0].index].id);
                },
            },
        });

        canvas.addEventListener("mouseleave", () => {
            const tip = $("[data-pgd-tip]");
            if (tip) tip.hidden = true;
        });

        rows.forEach((row) => row.addEventListener("click", () => select(row.dataset.pgdTermrow)));
        select(terms[0].id);
    }

    initDonut();
    syncQuickDisabled();
    applyFilters();
}

/* ------------------------------------------------------------------ *
 * Escaping helpers for the one place that builds markup from data
 * ------------------------------------------------------------------ */

function escapeHtml(value) {
    const wrapper = document.createElement("div");
    wrapper.textContent = value === null || value === undefined ? "" : String(value);
    return wrapper.innerHTML;
}

function escapeAttr(value) {
    return escapeHtml(value).replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

/* ------------------------------------------------------------------ *
 * Tutor / personal-tutor screens
 * ------------------------------------------------------------------ */

function initTutorScreens() {
    const courseFilter = $("#personalTutorCourseFilter");
    courseFilter?.addEventListener("change", () => { window.location.href = courseFilter.value; });

    initTypeAccordions();
}

/*
 * Class-type sections on the personal-tutor detail table.
 *
 * Open on load rather than closed: the sections group rows that were all
 * visible before, so starting collapsed would hide data the page used to
 * show. `hidden` rather than a display rule, so nothing has to know what the
 * body's display value is.
 */
function initTypeAccordions() {
    document.querySelectorAll("[data-pgd-acc]").forEach((head) => {
        const body = head.nextElementSibling;
        if (!body) return;

        head.addEventListener("click", () => {
            const open = head.getAttribute("aria-expanded") === "true";
            head.setAttribute("aria-expanded", open ? "false" : "true");
            body.hidden = open;
        });
    });
}

/* ------------------------------------------------------------------ *
 * Boot
 * ------------------------------------------------------------------ */

function boot() {
    if (!document.body.classList.contains("pgd-body")) return;

    initChrome();
    initTermPicker();
    initClock();
    initDashboard();
    initTutorScreens();
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", boot);
} else {
    boot();
}
