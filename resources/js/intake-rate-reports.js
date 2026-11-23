import TomSelect from "tom-select";

/*
 * New intake reports (Reports › Intake Performance): the New version / Old
 * version tabs of each accordion ([data-version-tabs]), the form of each new
 * report ([data-rate-form], see rate/form.blade.php: one or more intakes) and
 * the collapsible intake and course rows of the results. The old reports keep
 * their own scripts.
 *
 * The tabs are plain toggle buttons: the theme's tab script takes over any
 * [role=tab] / [role=tabpanel] on the page.
 */
(function () {
    document.querySelectorAll("[data-version-tabs]").forEach((group) => {
        const buttons = group.querySelectorAll(":scope > .crn-tabs [data-version]");
        const panes = group.querySelectorAll(":scope > [data-version-pane]");
        buttons.forEach((button) =>
            button.addEventListener("click", () => {
                const name = button.dataset.version;
                buttons.forEach((b) => {
                    const active = b === button;
                    b.classList.toggle("is-active", active);
                    b.setAttribute("aria-pressed", active ? "true" : "false");
                });
                panes.forEach((pane) => {
                    pane.hidden = pane.dataset.versionPane !== name;
                });
            })
        );
    });

    // The results are injected after load, so their clicks are handled on the document.
    document.addEventListener("click", (e) => {
        // An intake row opens and closes everything under it (rows and blocks
        // carrying data-crn-group). A class, not [hidden], so a course closed
        // inside it stays closed when the intake opens again.
        const group = e.target.closest("[data-crn-toggle]");
        if (group) {
            const open = group.getAttribute("aria-expanded") !== "true";
            group.setAttribute("aria-expanded", open ? "true" : "false");
            const scope = group.closest(".crn-result") || document;
            scope.querySelectorAll(`[data-crn-group="${group.dataset.crnToggle}"]`).forEach((el) => {
                el.classList.toggle("is-grp-hidden", !open);
            });
            return;
        }
        // Detail tables: a course row opens and closes its terms.
        const toggle = e.target.closest(".crn-att-toggle");
        if (!toggle) return;
        const open = toggle.getAttribute("aria-expanded") !== "true";
        toggle.setAttribute("aria-expanded", open ? "true" : "false");
        toggle.closest("table").querySelectorAll(`[data-terms-of="${toggle.dataset.terms}"]`).forEach((row) => {
            row.hidden = !open;
        });
    });

    document.querySelectorAll("[data-rate-form]").forEach((form) => {
        const select = form.querySelector("[data-rate-semester]");
        const error = form.querySelector("[data-rate-error]");
        const button = form.querySelector("[data-rate-generate]");
        const spinner = button.querySelector(".crn-spinner");
        const downloads = [
            [form.querySelector("[data-rate-pdf]"), form.dataset.pdf],
            [form.querySelector("[data-rate-excel]"), form.dataset.excel],
        ].filter(([link, name]) => link && name);
        const result = form.parentElement.querySelector("[data-rate-result]");

        const reset = () => {
            result.innerHTML = "";
            downloads.forEach(([link]) => {
                link.style.display = "none";
                link.setAttribute("href", "javascript:void(0);");
            });
        };
        const busy = (on) => {
            if (on) button.setAttribute("disabled", "disabled");
            else button.removeAttribute("disabled");
            spinner.style.display = on ? "inline-block" : "none";
        };

        // One or more intakes, as the old reports allowed.
        const picker = new TomSelect(select, {
            plugins: { dropdown_input: {}, remove_button: { title: "Remove this intake" } },
            placeholder: "Search here...",
            create: false,
        });
        select.addEventListener("change", () => {
            error.textContent = "";
            reset();
        });

        form.addEventListener("submit", (e) => {
            e.preventDefault();
            const ids = [].concat(picker.getValue() || []).filter(Boolean);
            error.textContent = "";
            if (!ids.length) {
                error.textContent = "Please select at least one intake semester.";
                reset();
                return;
            }

            busy(true);
            reset();
            axios({
                method: "post",
                url: route(form.dataset.generate),
                data: { semester_ids: ids },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            })
                .then((response) => {
                    result.innerHTML = response.data.htm;
                    downloads.forEach(([link, name]) => {
                        link.setAttribute("href", route(name, ids.join("_")));
                        link.style.display = "";
                    });
                })
                .catch((err) => {
                    const errors = (err.response && err.response.data && err.response.data.errors) || {};
                    const key = Object.keys(errors).find((k) => k.indexOf("semester_ids") === 0);
                    error.textContent = key ? errors[key][0] : "The report could not be generated. Please try again.";
                })
                .finally(() => busy(false));
        });
    });
})();
