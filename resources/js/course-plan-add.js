/*
 * Add Class Plans — step 1 of the plan wizard.
 *
 * A port of the legacy `plan-add-search.js`, hitting the same three cascade
 * endpoints in the same order, and finishing on the same redirect to the plan
 * builder. Rebuilt against the redesigned markup.
 *
 * The legacy file addressed its TomSelect controls as `tomSelectList[0..3]`,
 * indexes into the order `.lccTom` elements happened to appear in the DOM.
 * Each control is named here instead, so moving a field cannot silently
 * repoint a `.clear()` at the wrong dropdown.
 */

import TomSelect from "tom-select";
import { csrfHeaders } from "./course-table-kit";

/* These endpoints number their rows from 1 in PHP, so the JSON is an object
 * (`{"1":{…}}`) rather than an array. */
function toRows(res) {
    if (Array.isArray(res)) return res;
    if (res && typeof res === "object") return Object.values(res);

    return [];
}

(function () {
    const form = document.getElementById("classPlanAddForm");
    if (!form) return;

    const tomOptions = {
        plugins: { dropdown_input: {} },
        placeholder: "Please Select",
        dropdownParent: "body",
        dropdownClass: "ts-dropdown cm-tom-dropdown",
        allowEmptyOption: true,
    };

    // Each step: the select it owns, the field wrapper it reveals, and the
    // endpoint that fills it. `param` is what the endpoint calls this step's id.
    const STEPS = [
        { key: "year", id: "academic-year", param: "academicYear" },
        {
            key: "term",
            id: "termDeclarationId",
            param: "term_declaration_id",
            route: "termdeclaration.list.by.academic.year",
            noun: "term",
        },
        {
            key: "course",
            id: "course_creation_id",
            param: "course_creation_id",
            route: "course.list.by.academic.term",
            noun: "course",
        },
        {
            key: "group",
            id: "group_id",
            route: "group.list.by.academic.term.course",
            noun: "group",
        },
    ];

    STEPS.forEach((step) => {
        step.select = document.getElementById(step.id);
        step.field = step.select.closest(".cm-field");
        step.tom = new TomSelect(step.select, tomOptions);
    });

    const emptyNote = form.querySelector("[data-cm-empty]");
    const emptyText = form.querySelector("[data-cm-empty-text]");
    const submitBtn = document.getElementById("submitModulesBtn");

    const valueOf = (step) => {
        const value = step.select.value;

        return value && Number(value) > 0 ? value : "";
    };

    function setSpinner(step, on) {
        const spinner = step.field.querySelector("[data-cm-field-spinner]");
        if (spinner) spinner.style.display = on ? "inline-block" : "none";
    }

    function setNote(message) {
        if (!emptyNote) return;

        emptyNote.hidden = !message;
        if (message && emptyText) emptyText.textContent = message;
    }

    /** Steps after `fromIndex` are cleared and hidden again. */
    function resetFrom(fromIndex) {
        STEPS.slice(fromIndex).forEach((step) => {
            step.tom.clear(true);
            step.tom.clearOptions();
            step.field.hidden = true;
        });
        setNote("");
        syncSubmit();
    }

    /** Save & Continue only lights up once the whole combination is chosen. */
    function syncSubmit() {
        const ready = STEPS.every((step) => valueOf(step) !== "");

        if (ready) submitBtn.removeAttribute("disabled");
        else submitBtn.setAttribute("disabled", "disabled");
    }

    function load(index, payload) {
        const step = STEPS[index];

        setSpinner(step, true);
        // Locked while loading so a second combination cannot be started
        // mid-request and land options against the wrong parent.
        STEPS.slice(0, index).forEach((prev) => prev.tom.disable());

        axios({
            method: "post",
            url: route(step.route),
            data: payload,
            headers: csrfHeaders(),
        })
            .then((response) => {
                const rows = toRows(response.data.res);

                rows.forEach((row) => step.tom.addOption({ value: row.id, text: row.name }));
                step.tom.refreshOptions(false);
                step.field.hidden = false;

                setNote(rows.length ? "" : `No ${step.noun} found for this combination.`);
            })
            .catch(() => {
                // 304 with an empty payload is how these endpoints report
                // "nothing here", and axios treats it as a rejection.
                step.field.hidden = false;
                setNote(`No ${step.noun} found for this combination.`);
            })
            .then(() => {
                setSpinner(step, false);
                STEPS.slice(0, index).forEach((prev) => prev.tom.enable());
                syncSubmit();
            });
    }

    $("#academic-year").on("change", function () {
        resetFrom(1);
        if (!valueOf(STEPS[0])) return;

        load(1, { academicYear: valueOf(STEPS[0]) });
    });

    $("#termDeclarationId").on("change", function () {
        resetFrom(2);
        if (!valueOf(STEPS[1])) return;

        load(2, {
            academicYear: valueOf(STEPS[0]),
            term_declaration_id: valueOf(STEPS[1]),
        });
    });

    $("#course_creation_id").on("change", function () {
        resetFrom(3);
        if (!valueOf(STEPS[2])) return;

        load(3, {
            academicYear: valueOf(STEPS[0]),
            term_declaration_id: valueOf(STEPS[1]),
            course_creation_id: valueOf(STEPS[2]),
        });
    });

    $("#group_id").on("change", syncSubmit);

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        const [year, term, creation, group] = STEPS.map(valueOf);
        if (!year || !term || !creation || !group) {
            syncSubmit();

            return;
        }

        window.location.href = route("class.plan.builder", {
            academic: year,
            term,
            creation,
            group,
        });
    });

    syncSubmit();
})();
