/*
 * Group Leader dashboard.
 *
 * Two pages share this file:
 *
 *   #glGroups  "My groups"  - the term selector re-fetches the card grid
 *   #glGroup   one group    - view tabs, worklist tabs, the day picker and
 *                             the student drawer
 *
 * Both pages ship fully rendered, so nothing here builds markup: every
 * response is server-rendered HTML that replaces one region. The only client
 * state is which tab is showing.
 */

import Litepicker from "litepicker";

const csrf = () => ({ "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") });

/** Prefers the server's own wording — a 403 explains what is missing. */
const serverMessage = (error, fallback) => {
    const data = error && error.response ? error.response.data : null;

    return (data && data.message) || fallback;
};

const busy = (el, on) => {
    if (el) el.classList.toggle("is-loading", on);
};

/* ====================================================== My groups ======= */

(function () {
    const root = document.getElementById("glGroups");
    if (!root) return;

    const toggle = document.getElementById("glTermDropdown");
    const cards = root.querySelector("[data-gl-cards]");
    const stats = root.querySelector("[data-gl-stats]");
    const label = root.querySelector("[data-gl-term-label]");
    const current = root.querySelector("[data-gl-term-current]");

    if (!toggle) return;

    // The panel does not close itself when something inside it is clicked, so
    // the instance is kept to hide it on choose.
    const dropdown = tailwind.Dropdown.getOrCreateInstance(toggle);

    /* Delegated from `document`, not from `#glGroups`: tw-starter moves the
     * open menu to <body> (dropdown.js appendTo("body")) and puts it back on
     * close, so by the time a row is clicked it is no longer a descendant of
     * this page's root and a handler bound there would never fire. */
    $(document).on("click", "[data-gl-term-item]", function (event) {
        event.preventDefault();

        const item = this;
        const term = item.getAttribute("data-gl-term-item");
        const chosen = (item.getAttribute("data-gl-term-name") || "").trim();

        dropdown.hide();

        // Repaint the button and the tick straight away: the request only
        // decides what the cards say, not which term was picked.
        if (current) current.textContent = chosen || "Select term";
        if (label) label.textContent = chosen || "";

        // The rows live in the moved menu, so they are found through the item
        // rather than through the page root.
        const panel = item.closest(".gl-dropdown-panel") || document;
        panel.querySelectorAll("[data-gl-term-item]").forEach((el) => el.classList.toggle("is-active", el === item));

        busy(cards, true);
        axios({ method: "post", url: route("gl.dashboard.groups"), data: { term_id: term }, headers: csrf() })
            .then((response) => {
                busy(cards, false);
                cards.innerHTML = response.data.cards || "";
                stats.innerHTML = response.data.stats || "";

                // The chosen term belongs in the URL: a group opened from here
                // carries it, and a refresh must land on the same list.
                const url = new URL(window.location.href);
                url.searchParams.set("term", term);
                window.history.replaceState({}, "", url);
            })
            .catch((error) => {
                busy(cards, false);
                cards.innerHTML = `<div class="gl-card gl-empty">${serverMessage(error, "That term could not be loaded.")}</div>`;
            });
    });
})();

/* ==================================================== One group ========= */

(function () {
    const root = document.getElementById("glGroup");
    if (!root) return;

    const groupId = root.getAttribute("data-gl-group");
    const termId = root.getAttribute("data-gl-term");
    const scope = () => ({ group_id: groupId, term_id: termId });

    const rows = root.querySelector("[data-gl-rows]");
    const dayHost = root.querySelector("[data-gl-day]");
    const drawer = root.querySelector("[data-gl-drawer]");
    const drawerPanel = root.querySelector("[data-gl-drawer-panel]");

    let tab = "risk";

    /* ---- view tabs ------------------------------------------------- */

    $(root).on("click", "[data-gl-view]", function () {
        const view = this.getAttribute("data-gl-view");

        root.querySelectorAll("[data-gl-view]").forEach((el) => el.classList.toggle("is-active", el === this));
        root.querySelectorAll("[data-gl-panel]").forEach((el) => {
            el.hidden = el.getAttribute("data-gl-panel") !== view;
        });
    });

    /* ---- worklist -------------------------------------------------- */

    $(root).on("click", "[data-gl-tab]", function () {
        const next = this.getAttribute("data-gl-tab");
        if (next === tab) return;

        tab = next;
        root.querySelectorAll("[data-gl-tab]").forEach((el) => el.classList.toggle("is-active", el === this));

        busy(rows, true);
        axios({
            method: "post",
            url: route("gl.dashboard.students"),
            data: { ...scope(), tab },
            headers: csrf(),
        })
            .then((response) => {
                busy(rows, false);
                rows.innerHTML = response.data.htm || "";
            })
            .catch((error) => {
                busy(rows, false);
                rows.innerHTML = `<div class="gl-empty">${serverMessage(error, "That list could not be loaded.")}</div>`;
            });
    });

    /* ---- day view --------------------------------------------------- */

    const dateInput = document.getElementById("glDayDate");
    if (dateInput) {
        new Litepicker({
            element: dateInput,
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            format: "DD-MM-YYYY",
            dropdowns: { minYear: 2015, maxYear: 2050, months: true, years: true },
            setup: (picker) => {
                picker.on("selected", (date) => {
                    busy(dayHost, true);
                    axios({
                        method: "post",
                        url: route("gl.dashboard.today"),
                        data: { ...scope(), date: date.format("DD-MM-YYYY") },
                        headers: csrf(),
                    })
                        .then((response) => {
                            busy(dayHost, false);
                            dayHost.innerHTML = response.data.htm || "";
                        })
                        .catch((error) => {
                            busy(dayHost, false);
                            dayHost.innerHTML = `<div class="gl-card gl-empty">${serverMessage(error, "That day could not be loaded.")}</div>`;
                        });
                });
            },
        });
    }

    // Time slots collapse in place; the markup for both states is already there.
    $(root).on("click", "[data-gl-slot]", function () {
        const slot = this.closest(".gl-slot");
        if (!slot) return;

        const closed = slot.classList.toggle("is-closed");
        const mark = this.querySelector("[data-gl-slot-mark]");
        if (mark) mark.textContent = closed ? "▸" : "▾";
    });

    /* ---- student drawer --------------------------------------------- */

    let followUpPicker = null;

    function openDrawer(html) {
        drawerPanel.innerHTML = html;
        drawer.classList.add("is-open");
        document.body.style.overflow = "hidden";

        // The picker is bound to markup that was just replaced, so it has to be
        // rebuilt rather than reused.
        const followUp = drawerPanel.querySelector("[data-gl-followup]");
        if (followUp) {
            followUpPicker = new Litepicker({
                element: followUp,
                autoApply: true,
                singleMode: true,
                numberOfColumns: 1,
                numberOfMonths: 1,
                format: "DD-MM-YYYY",
                dropdowns: { minYear: 2015, maxYear: 2050, months: true, years: true },
            });
        }
    }

    function closeDrawer() {
        drawer.classList.remove("is-open");
        document.body.style.overflow = "";
        if (followUpPicker) {
            followUpPicker.destroy();
            followUpPicker = null;
        }
    }

    $(root).on("click", "[data-gl-student]", function () {
        axios({
            method: "post",
            url: route("gl.dashboard.student"),
            data: { ...scope(), student_id: this.getAttribute("data-gl-student") },
            headers: csrf(),
        })
            .then((response) => openDrawer(response.data.htm || ""))
            .catch((error) => {
                openDrawer(`<div class="gl-drawer__body"><div class="gl-empty">${serverMessage(error, "That student could not be loaded.")}</div></div>`);
            });
    });

    $(root).on("click", "[data-gl-drawer-close]", closeDrawer);

    // Clicking the scrim closes; clicking inside the panel must not.
    drawer.addEventListener("click", (event) => {
        if (event.target === drawer) closeDrawer();
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && drawer.classList.contains("is-open")) closeDrawer();
    });

    /* ---- logging contact -------------------------------------------- */

    $(root).on("submit", "#glContactForm", function (event) {
        event.preventDefault();

        const form = this;
        const save = form.querySelector("#glContactSave");
        const payload = {
            ...scope(),
            tab,
            student_id: $('input[name="student_id"]', form).val(),
            method: $('select[name="method"]', form).val(),
            reason: $('select[name="reason"]', form).val(),
            note: $('textarea[name="note"]', form).val(),
            follow_up_date: $('input[name="follow_up_date"]', form).val(),
        };

        form.querySelectorAll("[data-gl-error]").forEach((el) => (el.textContent = ""));
        save.setAttribute("disabled", "disabled");

        axios({ method: "post", url: route("gl.dashboard.contact"), data: payload, headers: csrf() })
            .then((response) => {
                save.removeAttribute("disabled");

                // Logging contact can move the student out of the list behind
                // the drawer, so both are repainted from the same response.
                openDrawer(response.data.drawer || "");
                if (rows) rows.innerHTML = response.data.rows || "";

                Object.entries(response.data.counts || {}).forEach(([key, count]) => {
                    const el = root.querySelector(`[data-gl-count="${key}"]`);
                    if (el) el.textContent = count;
                });
            })
            .catch((error) => {
                save.removeAttribute("disabled");

                const data = error.response ? error.response.data : null;
                const errors = (data && data.errors) || {};

                if (Object.keys(errors).length) {
                    Object.entries(errors).forEach(([field, message]) => {
                        const el = form.querySelector(`[data-gl-error="${field}"]`);
                        if (el) el.textContent = Array.isArray(message) ? message[0] : message;
                    });

                    return;
                }

                const fallback = form.querySelector('[data-gl-error="reason"]');
                if (fallback) fallback.textContent = serverMessage(error, "That could not be saved.");
            });
    });
})();
