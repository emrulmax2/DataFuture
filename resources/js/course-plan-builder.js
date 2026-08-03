/*
 * Class Plan Builder.
 *
 * The sheet is a set of cards, each pinned to one day / venue / room. Saving
 * walks every card and rebuilds the `routineData` structure
 * `PlanController::store()` expects:
 *
 *   routineData[day]["<venue>_<room>"][n] = { module, tutor, time, … }
 *
 * The legacy version derived day and room from where a card sat in the DOM —
 * it walked `tr.routineRow[data-day]`, then `td.routineDay[data-venuRoom]`.
 * Every card already carries its own `data-day` / `data-venue` / `data-room`,
 * so the walk is done from the cards themselves here. That keeps the payload
 * identical while letting the layout change from a 7 x 42 table to day tabs.
 *
 * Values are read from the card's real form controls rather than from `data-id`
 * attributes that JS had to keep in sync, so there is one source of truth per
 * field.
 */

import IMask from "imask";
import Litepicker from "litepicker";
import TomSelect from "tom-select";
import {
    csrfHeaders,
    hideConfirm,
    openConfirm,
    setBusy,
    showSuccess,
    wireConfirmReset,
} from "./course-table-kit";

const FORM_ID = "#classPlanBuilderForm";

(function () {
    const form = document.querySelector(FORM_ID);
    if (!form) return;

    const scope = (id) => form.querySelector(`#${id}`).value;

    /* ------------------------------------------------------------------ *
     * Card helpers
     * ------------------------------------------------------------------ */

    const cardsIn = (root) => Array.from(root.querySelectorAll("[data-cm-card]"));

    function fieldValue(card, name) {
        const el = card.querySelector(`[data-cm-field="${name}"]`);

        return el ? el.value : "";
    }

    /**
     * The label behind a select's current value.
     *
     * Not `options[selectedIndex]`: TomSelect rewrites the original select and
     * *prepends* the chosen option, so the selection always sits at index 0 and
     * any "is something picked" test based on the index reads as empty.
     */
    function selectedLabel(select) {
        if (!select || !select.value) return "";

        const ts = select.tomselect;
        if (ts && ts.options && ts.options[select.value]) {
            return ts.options[select.value][ts.settings.labelField] || "";
        }

        for (const option of select.options) {
            if (option.value === select.value) return option.text;
        }

        return "";
    }

    /** Head line: the module name on its own. */
    function paintSummary(card) {
        const label = card.querySelector("[data-cm-card-summary]");
        if (!label) return;

        label.textContent = selectedLabel(card.querySelector('[data-cm-field="module"]')) || "No module";

        // Theory is led by the tutor, everything else by the personal tutor.
        // Only the display changes — both selects stay in the DOM, so a value
        // already saved against the hidden one is still sent on the next save.
        card.setAttribute("data-cm-tutor", fieldValue(card, "class_type") === "Theory" ? "tutor" : "ptutor");
    }

    /** Cards stacked in one room cycle through the palette by position. */
    function paintTints() {
        form.querySelectorAll("[data-cm-cards]").forEach((holder) => {
            cardsIn(holder).forEach((card, i) => {
                card.setAttribute("data-cm-tint", i % 6);
            });
        });
    }

    /** Room, venue and day counters, and the total in the page header. */
    function paintCounts() {
        form.querySelectorAll("[data-cm-room-count]").forEach((badge) => {
            const panel = badge.closest("[data-cm-room]");
            const n = cardsIn(panel).length;

            badge.textContent = n;
            badge.classList.toggle("is-filled", n > 0);
            panel.classList.toggle("has-cards", n > 0);

            const empty = panel.querySelector("[data-cm-room-empty]");
            if (empty) empty.hidden = n > 0;
        });

        form.querySelectorAll("[data-cm-daypane]").forEach((pane) => {
            const day = pane.getAttribute("data-cm-daypane");
            const badge = form.querySelector(`[data-cm-day-count="${day}"]`);
            const n = cardsIn(pane).length;

            if (badge) {
                badge.textContent = n;
                badge.hidden = n === 0;
            }
        });

        form.querySelectorAll("[data-cm-venue-count]").forEach((badge) => {
            const venue = badge.getAttribute("data-cm-venue-count");
            badge.textContent = cardsIn(form).filter((c) => c.getAttribute("data-venue") === venue).length;
        });

        const total = form.querySelector("[data-cm-total]");
        if (total) total.textContent = cardsIn(form).length;

        paintTints();
    }

    /* IMask instances are per-input; applied to whatever a card brings with it. */
    function maskCard(card) {
        card.querySelectorAll(".cm-classtime").forEach((input) => {
            if (input.dataset.cmMasked) return;
            input.dataset.cmMasked = "1";

            IMask(input, {
                overwrite: true,
                autofix: true,
                mask: "HH:MM - HH2:MM2",
                blocks: {
                    HH: { mask: IMask.MaskedRange, placeholderChar: "HH", from: 0, to: 23, maxLength: 2 },
                    MM: { mask: IMask.MaskedRange, placeholderChar: "MM", from: 0, to: 59, maxLength: 2 },
                    HH2: { mask: IMask.MaskedRange, placeholderChar: "HH", from: 0, to: 23, maxLength: 2 },
                    MM2: { mask: IMask.MaskedRange, placeholderChar: "MM", from: 0, to: 59, maxLength: 2 },
                },
            });
        });

        // Litepicker, not a mask: `resources/js/datepicker.js` only binds the
        // `.datepicker` fields present at load, and cards arrive later.
        card.querySelectorAll(".cm-classdate").forEach((input) => {
            if (input.dataset.cmPicker) return;
            input.dataset.cmPicker = "1";

            new Litepicker({
                element: input,
                autoApply: true,
                singleMode: true,
                numberOfColumns: 1,
                numberOfMonths: 1,
                format: "DD-MM-YYYY",
                dropdowns: { minYear: 1900, maxYear: 2050, months: true, years: true },
            });
        });
    }

    /* Searchable selects. The dropdown is re-parented to <body> because a room
     * panel clips its overflow, which would cut the list off. */
    const TOM_OPTIONS = {
        // No `dropdown_input` here. That plugin puts its search box in as the
        // dropdown's first child, above the option list, and on this screen the
        // panel was rendering that box with nothing under it. Without it the
        // control itself is the search field and the options render directly,
        // which is one fewer moving part on the path that was failing.
        placeholder: "Select",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown cm-tom-dropdown",
        allowEmptyOption: true,
        // 157 tutors; the default cap of 50 would hide most of them until the
        // user typed, which reads as a broken list.
        maxOptions: 500,
    };

    function enhanceSelects(card) {
        card.querySelectorAll('[data-cm-field="module"], [data-cm-field="tutor"], [data-cm-field="personal_tutor"]').forEach(
            (select) => {
                // `select.tomselect` is set by the constructor, so this is safe
                // to call again on a card that has already been through it.
                if (select.tomselect) return;

                new TomSelect(select, TOM_OPTIONS);
            }
        );
    }

    function adoptCard(card) {
        maskCard(card);
        enhanceSelects(card);
        paintSummary(card);
    }

    cardsIn(form).forEach(adoptCard);
    paintCounts();

    /* ------------------------------------------------------------------ *
     * Day tabs
     * ------------------------------------------------------------------ */

    $(form).on("click", "[data-cm-daytab]", function () {
        const day = this.getAttribute("data-cm-daytab");

        form.querySelectorAll("[data-cm-daytab]").forEach((tab) => {
            const on = tab === this;
            tab.classList.toggle("is-active", on);
            tab.setAttribute("aria-selected", on ? "true" : "false");
        });
        form.querySelectorAll("[data-cm-daypane]").forEach((pane) => {
            pane.hidden = pane.getAttribute("data-cm-daypane") !== day;
        });

        applyRoomSearch();
    });

    /* ------------------------------------------------------------------ *
     * Room panels — collapse, search
     * ------------------------------------------------------------------ */

    $(form).on("click", "[data-cm-room-toggle]", function () {
        this.closest("[data-cm-room]").classList.toggle("is-closed");
    });

    const toggleAllLabel = form.querySelector("[data-cm-toggle-all-label]");

    $(form).on("click", "[data-cm-toggle-all]", function () {
        const pane = form.querySelector("[data-cm-daypane]:not([hidden])");
        if (!pane) return;

        const panels = Array.from(pane.querySelectorAll("[data-cm-room]"));
        const allOpen = panels.every((p) => !p.classList.contains("is-closed"));

        panels.forEach((p) => p.classList.toggle("is-closed", allOpen));
        if (toggleAllLabel) toggleAllLabel.textContent = allOpen ? "Expand all" : "Collapse all";
    });

    /** Room search and the venue chips both decide which panels are on screen. */
    function applyRoomSearch() {
        const query = (form.querySelector("[data-cm-roomsearch]").value || "").trim().toLowerCase();
        const offVenues = new Set(
            Array.from(form.querySelectorAll("[data-cm-venue]"))
                .filter((input) => !input.checked)
                .map((input) => input.getAttribute("data-cm-venue"))
        );

        form.querySelectorAll("[data-cm-daypane]").forEach((pane) => {
            let shown = 0;

            pane.querySelectorAll("[data-cm-room]").forEach((panel) => {
                const hiddenByVenue = offVenues.has(panel.getAttribute("data-cm-room-venue"));
                const hiddenBySearch = query !== "" && !panel.getAttribute("data-cm-room-name").includes(query);

                panel.hidden = hiddenByVenue || hiddenBySearch;
                if (!panel.hidden) shown += 1;
            });

            const none = pane.querySelector("[data-cm-noroom]");
            if (none) none.hidden = shown > 0;
        });
    }

    $(form).on("input search", "[data-cm-roomsearch]", applyRoomSearch);

    /* ------------------------------------------------------------------ *
     * Venue chips
     * ------------------------------------------------------------------ */

    $(form).on("change", "[data-cm-venue]", function () {
        const venue = this.getAttribute("data-cm-venue");

        // A venue holding planned classes cannot be switched off — its rooms
        // would leave the sheet while their cards were still going to be saved.
        if (!this.checked) {
            const planned = cardsIn(form).filter((c) => c.getAttribute("data-venue") === venue).length;

            if (planned > 0) {
                this.checked = true;
                showSuccess(
                    "Venue still in use",
                    `This venue has ${planned} planned ${planned === 1 ? "class" : "classes"}. Remove them before hiding it.`,
                    "warn"
                );

                return;
            }
        }

        this.closest(".cm-venuechip").classList.toggle("is-on", this.checked);
        applyRoomSearch();
    });

    /* ------------------------------------------------------------------ *
     * Card field changes
     * ------------------------------------------------------------------ */

    $(form).on("change input", "[data-cm-field]", function () {
        const card = this.closest("[data-cm-card]");
        if (card) paintSummary(card);
    });

    /** Empties a select whether or not TomSelect is driving it. */
    function clearSelect(select) {
        if (!select) return;

        if (select.tomselect) {
            // Silent: this is a side effect of another change, and a second
            // change event here would re-enter the handler below.
            select.tomselect.clear(true);
        }

        select.value = "";
    }

    // Switching the class type retires one of the tutor fields, so the value on
    // it no longer applies and is cleared. Bound to the change event only —
    // doing this during the initial paint would wipe the saved tutor on every
    // card the moment the page loaded.
    $(form).on("change", '[data-cm-field="class_type"]', function () {
        const card = this.closest("[data-cm-card]");
        if (!card) return;

        const retired = this.value === "Theory" ? "personal_tutor" : "tutor";
        clearSelect(card.querySelector(`[data-cm-field="${retired}"]`));
    });

    $(form).on("click", "[data-cm-card-toggle]", function () {
        this.closest("[data-cm-card]").classList.toggle("is-closed");
    });

    /* ------------------------------------------------------------------ *
     * Add / copy / duplicate / paste
     * ------------------------------------------------------------------ */

    function placeCard(panel, card) {
        panel.querySelector("[data-cm-cards]").appendChild(card);
        adoptCard(card);
        paintCounts();
    }

    $(form).on("click", "[data-cm-add]", function () {
        const btn = this;
        const panel = btn.closest("[data-cm-room]");

        btn.setAttribute("disabled", "disabled");
        axios({
            method: "post",
            url: route("class.plan.get.box"),
            data: {
                term_declaration_id: scope("term_declaration_id"),
                academic_year_id: scope("academic_year_id"),
                course_creation_id: scope("course_creation_id"),
                instance_term_id: scope("instance_term_id"),
                course_id: scope("course_id"),
                group_id: scope("group_id"),
                day: btn.getAttribute("data-day"),
                venue: btn.getAttribute("data-venue"),
                room: btn.getAttribute("data-room"),
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                btn.removeAttribute("disabled");

                const holder = document.createElement("div");
                holder.innerHTML = response.data.htmls;

                const card = holder.querySelector("[data-cm-card]");
                if (card) placeCard(panel, card);
            })
            .catch(() => {
                btn.removeAttribute("disabled");
                showSuccess("Something went wrong", "The class could not be added. Please try again.", "warn");
            });
    });

    /* The clipboard holds a plain snapshot of a card's values, never the node,
     * so pasting into another room cannot move the original. */
    let clipboard = null;
    const clipEl = form.querySelector("[data-cm-clip]");
    const clipLabel = form.querySelector("[data-cm-clip-label]");

    const CLIP_FIELDS = ["module", "class_type", "tutor", "personal_tutor", "time", "submission", "virtual_room", "note"];

    function paintClip() {
        if (clipEl) clipEl.hidden = !clipboard;
        if (clipboard && clipLabel) clipLabel.textContent = clipboard.label;

        form.querySelectorAll("[data-cm-paste]").forEach((btn) => {
            btn.hidden = !clipboard;
        });
    }

    function snapshot(card) {
        const values = {};
        CLIP_FIELDS.forEach((name) => {
            values[name] = fieldValue(card, name);
        });

        const summary = card.querySelector("[data-cm-card-summary]");

        return { values, label: summary ? summary.textContent : "Class" };
    }

    /** A fresh card at `panel`, filled from `values`. Always a new record. */
    function pasteInto(panel, values) {
        const addBtn = panel.querySelector("[data-cm-add]");
        if (!addBtn) return;

        axios({
            method: "post",
            url: route("class.plan.get.box"),
            data: {
                term_declaration_id: scope("term_declaration_id"),
                academic_year_id: scope("academic_year_id"),
                course_creation_id: scope("course_creation_id"),
                instance_term_id: scope("instance_term_id"),
                course_id: scope("course_id"),
                group_id: scope("group_id"),
                day: addBtn.getAttribute("data-day"),
                venue: addBtn.getAttribute("data-venue"),
                room: addBtn.getAttribute("data-room"),
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                const holder = document.createElement("div");
                holder.innerHTML = response.data.htmls;

                const card = holder.querySelector("[data-cm-card]");
                if (!card) return;

                CLIP_FIELDS.forEach((name) => {
                    const el = card.querySelector(`[data-cm-field="${name}"]`);
                    if (el) el.value = values[name];
                });

                placeCard(panel, card);
            })
            .catch(() => {
                showSuccess("Something went wrong", "The class could not be pasted. Please try again.", "warn");
            });
    }

    $(form).on("click", "[data-cm-card-copy]", function () {
        clipboard = snapshot(this.closest("[data-cm-card]"));
        paintClip();
    });

    $(form).on("click", "[data-cm-clip-clear]", function () {
        clipboard = null;
        paintClip();
    });

    $(form).on("click", "[data-cm-card-duplicate]", function () {
        const card = this.closest("[data-cm-card]");

        pasteInto(card.closest("[data-cm-room]"), snapshot(card).values);
    });

    $(form).on("click", "[data-cm-paste]", function () {
        if (!clipboard) return;

        pasteInto(this.closest("[data-cm-room]"), clipboard.values);
    });

    /* ------------------------------------------------------------------ *
     * Remove
     * ------------------------------------------------------------------ */

    let pendingCard = null;

    $(form).on("click", "[data-cm-card-remove]", function () {
        const card = this.closest("[data-cm-card]");
        const existing = fieldValue(card, "existing_id");

        pendingCard = card;

        // A card that has never been saved is only DOM; one with an id has to
        // be deleted on the server too.
        openConfirm({
            id: Number(existing) > 0 ? existing : 0,
            action: Number(existing) > 0 ? "DELETE" : "DROP",
            title: "Remove this class?",
            message:
                Number(existing) > 0
                    ? "This class is already saved, so it will be moved to the trash."
                    : "This class has not been saved yet and will just be taken off the sheet.",
            confirmLabel: "Remove",
        });
    });

    function dropPending() {
        if (pendingCard) {
            pendingCard.remove();
            pendingCard = null;
        }
        paintCounts();
    }

    wireConfirmReset();
    document.querySelector("#confirmModal").addEventListener("hidden.tw.modal", function () {
        pendingCard = null;
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const action = $(this).attr("data-action");
        const id = $(this).attr("data-id");

        if (action === "DROP") {
            dropPending();
            hideConfirm();
            showSuccess("Removed", "The class was taken off the sheet.");

            return;
        }

        if (action !== "DELETE") return;

        axios({
            method: "delete",
            url: route("class.plan.delete", id),
            headers: csrfHeaders(),
        })
            .then(() => {
                dropPending();
                hideConfirm();
                showSuccess("Removed", "The class was moved to the trash.");
            })
            .catch(() => {
                hideConfirm();
                showSuccess("Something went wrong", "The class could not be removed.", "warn");
            });
    });

    /* ------------------------------------------------------------------ *
     * Save
     * ------------------------------------------------------------------ */

    /**
     * Rebuilds `routineData[day]["<venue>_<room>"][n]` from the cards. Days and
     * rooms with nothing on them contribute nothing, which `store()` handles —
     * it only iterates what it is given.
     */
    function collect() {
        const routineData = {};

        cardsIn(form).forEach((card) => {
            const day = card.getAttribute("data-day");
            const venueRoom = `${card.getAttribute("data-venue")}_${card.getAttribute("data-room")}`;

            if (!routineData[day]) routineData[day] = {};
            if (!routineData[day][venueRoom]) routineData[day][venueRoom] = {};

            const box = {};
            card.querySelectorAll("[data-cm-field]").forEach((el) => {
                box[el.getAttribute("data-cm-field")] = el.value;
            });

            // 1-based, matching what the legacy serialiser produced.
            routineData[day][venueRoom][Object.keys(routineData[day][venueRoom]).length + 1] = box;
        });

        return routineData;
    }

    const DAY_NAMES = { 1: "Mon", 2: "Tue", 3: "Wed", 4: "Thu", 5: "Fri", 6: "Sat", 7: "Sun" };

    /** Where a card sits, for a message the user can act on. */
    function cardWhere(card) {
        const day = DAY_NAMES[card.getAttribute("data-day")] || "";
        const room = card.closest("[data-cm-room]");
        const name = room ? room.querySelector(".cm-roompanel__room") : null;

        return `${day} · ${name ? name.textContent.trim() : "room"}`;
    }

    /**
     * A class with no module cannot be saved — `plans.module_creation_id` is
     * NOT NULL and the sheet would be meaningless. The server refuses it too;
     * this stops the round trip and points at the offending cards.
     */
    function cardsMissingModule() {
        return cardsIn(form).filter((card) => !fieldValue(card, "module"));
    }

    function flagCards(cards) {
        cardsIn(form).forEach((card) => card.classList.remove("is-invalid"));
        cards.forEach((card) => card.classList.add("is-invalid"));
    }

    // The flag clears as soon as the module is chosen.
    $(form).on("change", '[data-cm-field="module"]', function () {
        const card = this.closest("[data-cm-card]");
        if (card && this.value) card.classList.remove("is-invalid");
    });

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (cardsIn(form).length === 0) {
            showSuccess("Nothing to save", "Add at least one class before saving.", "warn");

            return;
        }

        const incomplete = cardsMissingModule();
        if (incomplete.length > 0) {
            flagCards(incomplete);

            // Open the day the first offender is on, so closing the dialog
            // leaves the user looking straight at it.
            const firstDay = incomplete[0].getAttribute("data-day");
            const tab = form.querySelector(`[data-cm-daytab="${firstDay}"]`);
            if (tab) tab.click();

            const list = incomplete.map(cardWhere);
            showSuccess(
                incomplete.length === 1 ? "One class has no module" : `${incomplete.length} classes have no module`,
                "Nothing was saved. Choose a module for:<br><br>" +
                    list.slice(0, 8).join("<br>") +
                    (list.length > 8 ? `<br>and ${list.length - 8} more` : ""),
                "warn"
            );

            incomplete[0].scrollIntoView({ block: "center" });

            return;
        }

        setBusy("#saveUpdatePlans", true);
        form.querySelectorAll("[data-cm-add]").forEach((b) => b.setAttribute("disabled", "disabled"));

        axios({
            method: "post",
            url: route("class.plan.store"),
            data: {
                routineData: collect(),
                term_declaration_id: scope("term_declaration_id"),
                academic_year_id: scope("academic_year_id"),
                course_creation_id: scope("course_creation_id"),
                instance_term_id: scope("instance_term_id"),
                course_id: scope("course_id"),
                group_id: scope("group_id"),
            },
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#saveUpdatePlans", false);
                form.querySelectorAll("[data-cm-add]").forEach((b) => b.removeAttribute("disabled"));

                showSuccess("Congratulations!", response.data.msg || "The class plans were saved.");
                window.setTimeout(function () {
                    window.location.href = response.data.red || route("class.plan");
                }, 2200);
            })
            .catch((error) => {
                setBusy("#saveUpdatePlans", false);
                form.querySelectorAll("[data-cm-add]").forEach((b) => b.removeAttribute("disabled"));

                // The server rejects the whole sheet rather than writing part of
                // it, and names the classes at fault — show them rather than a
                // generic failure the user cannot act on.
                const payload = error.response ? error.response.data : null;
                const problems = payload && Array.isArray(payload.errors) ? payload.errors : [];
                const detail = problems.length > 1 ? `<br><br>${problems.join("<br>")}` : "";

                showSuccess(
                    "Nothing was saved",
                    ((payload && payload.message) || "The class plans could not be saved. Please try again.") + detail,
                    "warn"
                );
            });
    });

    paintClip();
    applyRoomSearch();
})();
