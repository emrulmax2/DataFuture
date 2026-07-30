/*
 * Term Module Creations — step 1 (choose course modules).
 *
 * A port of the picker in the legacy `term-module-creation.js`. The payload is
 * unchanged: a `moduleid[]` input per chosen module, plus `instanceTermId` and
 * `courseId`, posted to `term.module.creation.store`.
 *
 * The interaction follows the design rather than the legacy behaviour. The old
 * screen needed a **double click** to select and another double click on the
 * copied row to deselect, and it moved rows between the two panels by cloning
 * DOM nodes. Here a single click toggles, every module stays listed on the
 * right showing whether it is on, and the left panel is rendered from that
 * state — so the two panels cannot drift out of sync.
 */

import { csrfHeaders, setBusy, showSuccess } from "./course-table-kit";

(function () {
    const form = document.querySelector("#termModuleCreationFormStp1");
    if (!form) return;

    const optionList = form.querySelector("[data-cm-option-list]");
    const pickedList = form.querySelector("[data-cm-picked-list]");
    const pickedEmpty = form.querySelector("[data-cm-picked-empty]");
    const pickedCount = form.querySelector("[data-cm-picked-count]");
    const submitBtn = form.querySelector("#saveandcontinue");

    // Selection order is the insertion order of this map, so the left panel
    // lists modules in the order they were picked.
    const picked = new Map();

    function escapeHtml(value) {
        const wrapper = document.createElement("div");
        wrapper.textContent = value === null || value === undefined ? "" : String(value);

        return wrapper.innerHTML;
    }

    function paint() {
        const rows = [...picked.entries()];

        pickedList.innerHTML = rows
            .map(
                ([id, name]) => `
                <div class="cm-chosen" data-cm-picked="${escapeHtml(id)}">
                    <span class="cm-chosen__name">${escapeHtml(name)}</span>
                    <button type="button" class="cm-chosen__remove" data-cm-picked-remove title="Remove module">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6L6 18M6 6l12 12"></path></svg>
                    </button>
                    <input type="hidden" name="moduleid[]" value="${escapeHtml(id)}">
                </div>`,
            )
            .join("");

        pickedEmpty.hidden = rows.length > 0;
        pickedCount.textContent = `${rows.length} selected`;

        if (rows.length) submitBtn.removeAttribute("disabled");
        else submitBtn.setAttribute("disabled", "disabled");
    }

    function setSelected(button, on) {
        const id = button.getAttribute("data-modid");

        if (on) picked.set(id, button.getAttribute("data-modname"));
        else picked.delete(id);

        button.classList.toggle("is-on", on);
        button.setAttribute("aria-pressed", on ? "true" : "false");
        paint();
    }

    optionList.addEventListener("click", (event) => {
        const button = event.target.closest("[data-cm-option]");
        if (!button) return;

        setSelected(button, !button.classList.contains("is-on"));
    });

    // Removing from the left panel clears the matching option on the right,
    // which is what lets it be picked again.
    pickedList.addEventListener("click", (event) => {
        if (!event.target.closest("[data-cm-picked-remove]")) return;

        const id = event.target.closest("[data-cm-picked]").getAttribute("data-cm-picked");
        const option = optionList.querySelector(`[data-modid="${CSS.escape(id)}"]`);

        if (option) setSelected(option, false);
        else {
            picked.delete(id);
            paint();
        }
    });

    paint();

    /* ------------------------------------------------------------------ *
     * Submit — unchanged endpoint and payload
     * ------------------------------------------------------------------ */

    $(form).on("submit", function (e) {
        e.preventDefault();
        setBusy("#saveandcontinue", true);

        axios({
            method: "post",
            url: route("term.module.creation.store"),
            data: new FormData(form),
            headers: csrfHeaders(),
        })
            .then((response) => {
                if (response.status != 200) {
                    setBusy("#saveandcontinue", false);
                    return;
                }

                showSuccess(
                    "Modules selected",
                    response.data.message || "The module set has been created successfully.",
                );

                // The endpoint returns where to go next (step 2). Kept on a
                // short delay so the confirmation is readable, as before.
                if (response.data.red) {
                    window.setTimeout(() => {
                        window.location.href = response.data.red;
                    }, 1200);
                } else {
                    setBusy("#saveandcontinue", false);
                }
            })
            .catch((error) => {
                setBusy("#saveandcontinue", false);

                const message =
                    error.response && error.response.data && error.response.data.message
                        ? error.response.data.message
                        : "The module set could not be saved. Please try again.";

                showSuccess("Could not save", message, "warn");
            });
    });
})();
