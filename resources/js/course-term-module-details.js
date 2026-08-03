/*
 * Term Module Creations — step 2 (set each module's assessments).
 *
 * A port of the wizard in the legacy `term-module-creation.js`. Same endpoint
 * and payload: `module_creation_id` plus the ticked `cmb_assessment[]`, posted
 * to `assessment.store`, one module at a time.
 *
 * Two things the legacy version got wrong are fixed here:
 *
 * 1. It advanced to the next module *synchronously*, immediately after issuing
 *    the axios call — so `nextWizardStep` was still true whatever the server
 *    said, and a failed save still moved you on. The step only advances once
 *    the request resolves now.
 * 2. Its last-step success handler read `response.data.red`, but `response` was
 *    out of scope there — a ReferenceError every time the final module was
 *    saved. The redirect target is taken from the resolved response instead.
 */

import { csrfHeaders, setBusy, showSuccess } from "./course-table-kit";

(function () {
    const wizard = document.querySelector("[data-cm-wizard]");
    if (!wizard) return;

    const steps = [...wizard.querySelectorAll(".cm-step")];
    if (!steps.length) return;

    function show(index) {
        steps.forEach((step, i) => {
            step.hidden = i !== index;
        });

        // The step that just appeared may be below the fold on a long form.
        const top = wizard.getBoundingClientRect().top + window.pageYOffset - 96;
        window.scrollTo({ top, behavior: "smooth" });
    }

    function indexOfStep(el) {
        return steps.indexOf(el.closest(".cm-step"));
    }

    wizard.addEventListener("click", (event) => {
        const prev = event.target.closest("[data-cm-step-prev]");
        if (prev) {
            const i = indexOfStep(prev);
            if (i > 0) show(i - 1);
            return;
        }

        const next = event.target.closest("[data-cm-step-next]");
        if (next) saveStep(next);
    });

    function saveStep(button) {
        const step = button.closest(".cm-step");
        const form = step.querySelector("form");
        const index = steps.indexOf(step);
        const isLast = step.hasAttribute("data-cm-step-last");
        const buttonId = `#${button.id}`;

        const ticked = form.querySelectorAll("input.cmb_assessment:checked").length;

        // Nothing ticked means nothing to insert — the endpoint would answer
        // with a "something went wrong" message for an empty selection even
        // though skipping a module is allowed, so the call is skipped instead.
        if (!ticked) {
            finish(isLast, null);
            return;
        }

        setBusy(buttonId, true);

        axios({
            method: "post",
            url: route("assessment.store"),
            data: new FormData(form),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy(buttonId, false);
                // Advance only now that the save has actually come back.
                finish(isLast, response.data && response.data.red ? response.data.red : null);
            })
            .catch((error) => {
                setBusy(buttonId, false);

                const message =
                    error.response && error.response.data && error.response.data.Message
                        ? error.response.data.Message
                        : "The assesments could not be saved. Please try again.";

                showSuccess("Could not save", message, "warn");
            });

        function finish(last, redirect) {
            if (!last) {
                show(index + 1);
                return;
            }

            showSuccess(
                "Module set complete",
                "The assesments have been saved. Returning you to the term module list.",
            );

            window.setTimeout(() => {
                window.location.href = redirect || route("term.module.creation");
            }, 1400);
        }
    }
})();
