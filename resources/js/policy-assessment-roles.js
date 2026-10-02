import xlsx from "xlsx";
import { createElement, createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

/* Policy Assessments › Roles (HR). */
(function () {
    const tableNode = document.querySelector("#policyRoleTable");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const card = document.querySelector("#policyRolesCard");
    const emptyState = document.querySelector("#rolesEmptyState");
    const tableArea = document.querySelector("#rolesTableArea");
    let tableContent = null;
    let hasRoles = card?.getAttribute("data-has-roles") === "1";

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });
    };

    /*
     * Inline SVG for markup built in JS. Tabulator can re-run formatters on a
     * redraw (e.g. a window resize) without firing renderComplete, so table
     * cells must not rely on createIcons() swapping <i data-lucide> later.
     */
    const iconSvg = (name, extraClass = "") => {
        const key = String(name).split("-").map((part) => part.charAt(0).toUpperCase() + part.slice(1)).join("");
        const node = icons[key];

        if (!node) {
            return "";
        }

        const svg = createElement(node);

        svg.setAttribute("class", `lucide lucide-${name}${extraClass ? " " + extraClass : ""}`);
        svg.setAttribute("stroke-width", "1.7");
        svg.setAttribute("aria-hidden", "true");

        return svg.outerHTML;
    };

    const escapeHtml = (value) => {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const plural = (count, one, many) => (Number(count) === 1 ? one : many);

    /* ------------------------------------------------------------------
     * The policy tick list (one per modal). The markup is rendered with the
     * page; this only filters it, keeps the counts and does the bulk ticks.
     * ------------------------------------------------------------------ */
    const createPicker = (form) => {
        const root = form.querySelector("[data-role-picker]");
        const list = root?.querySelector("[data-picker-list]");

        if (!root || !list) {
            /* No policies in the bank yet: nothing to drive. */
            return {
                reset() {},
                setTicked() {},
                revealError() {},
            };
        }

        const search = root.querySelector("[data-picker-search]");
        const totalNode = root.querySelector("[data-picker-total]");
        const totalNote = root.querySelector("[data-picker-total-note]");
        const allButton = root.querySelector("[data-picker-all]");
        const noneButton = root.querySelector("[data-picker-none]");
        const onlyButton = root.querySelector("[data-picker-only]");
        const noMatch = root.querySelector("[data-picker-nomatch]");
        const errorNode = root.querySelector(".error-policy_ids");
        const groups = Array.from(root.querySelectorAll("[data-picker-group]")).map((node) => ({
            node,
            toggle: node.querySelector("[data-picker-group-toggle]"),
            count: node.querySelector("[data-picker-group-count]"),
            fold: node.querySelector("[data-picker-fold]"),
            items: Array.from(node.querySelectorAll("[data-picker-item]")).map((item) => ({
                node: item,
                input: item.querySelector("[data-picker-policy]"),
                text: item.getAttribute("data-search") || "",
            })),
        }));
        let onlyTicked = false;

        const terms = () => (search.value || "").trim().toLowerCase().split(/\s+/).filter(Boolean);
        const filtering = () => terms().length > 0 || onlyTicked;
        const shown = (group) => group.items.filter((item) => !item.node.hidden);
        const everyItem = () => groups.reduce((all, group) => all.concat(group.items), []);
        const everyShown = () => groups.reduce((all, group) => all.concat(shown(group)), []);

        /* Counts, tri-state boxes, the running total and the bulk buttons. */
        const refresh = () => {
            let ticked = 0;
            let unassignable = 0;

            groups.forEach((group) => {
                const groupTicked = group.items.filter((item) => item.input.checked).length;
                const targets = shown(group);
                const targetsTicked = targets.filter((item) => item.input.checked).length;

                ticked += groupTicked;
                unassignable += group.items.filter((item) => item.input.checked && item.input.getAttribute("data-active") !== "1").length;

                group.items.forEach((item) => {
                    item.node.classList.toggle("is-ticked", item.input.checked);
                });
                group.count.textContent = `${groupTicked} of ${group.items.length}`;
                group.node.classList.toggle("has-ticks", groupTicked > 0);
                group.node.classList.toggle("is-complete", groupTicked === group.items.length);

                /* The box follows what the click would act on: the policies in view. */
                group.toggle.checked = targets.length > 0 && targetsTicked === targets.length;
                group.toggle.indeterminate = targetsTicked > 0 && targetsTicked < targets.length;
                group.toggle.disabled = targets.length === 0;
            });

            totalNode.textContent = ticked === 0 ? "No policies ticked" : `${ticked} ${plural(ticked, "policy", "policies")} ticked`;
            root.classList.toggle("has-ticks", ticked > 0);

            if (totalNote) {
                const noteText = totalNote.querySelector("span");

                totalNote.hidden = unassignable === 0;
                totalNote.title = unassignable === 0 ? "" : "Ticked, but not assigned until switched on in the Question Bank.";

                if (noteText) {
                    noteText.textContent = unassignable === 0 ? "" : `${unassignable} not active`;
                }
            }

            const targets = everyShown();
            const targetsTicked = targets.filter((item) => item.input.checked).length;
            const searching = terms().length > 0;
            const narrowed = searching || onlyTicked;

            /* The count matters for a search ("Tick shown (5)"); under "ticked only" every row is ticked already. */
            allButton.textContent = searching ? `Tick shown (${targets.length})` : narrowed ? "Tick shown" : "Tick all";
            noneButton.textContent = narrowed ? "Clear shown" : "Clear all";
            allButton.disabled = targets.length === 0 || targetsTicked === targets.length;
            noneButton.disabled = targetsTicked === 0;

            if (ticked > 0 && errorNode && errorNode.textContent !== "") {
                errorNode.textContent = "";
                list.classList.remove("border-danger");
            }
        };

        /* Search / "ticked only": hide what does not match. Not re-run on a tick,
           so a policy does not vanish from under the pointer when it is unticked. */
        const applyFilter = () => {
            const words = terms();
            let visible = 0;

            groups.forEach((group) => {
                let groupVisible = 0;

                group.items.forEach((item) => {
                    const matches = words.every((word) => item.text.includes(word)) && (!onlyTicked || item.input.checked);

                    item.node.hidden = !matches;
                    groupVisible += matches ? 1 : 0;
                });

                group.node.hidden = groupVisible === 0;
                visible += groupVisible;
            });

            root.classList.toggle("is-filtering", filtering());

            if (noMatch) {
                noMatch.hidden = visible > 0;
                noMatch.textContent = visible > 0
                    ? ""
                    : words.length > 0
                        ? `No ${onlyTicked ? "ticked " : ""}policies match "${search.value.trim()}".`
                        : "No policies are ticked yet.";
            }

            refresh();
        };

        const setFold = (group, open) => {
            group.node.classList.toggle("is-folded", !open);
            group.fold.setAttribute("aria-expanded", open ? "true" : "false");
        };

        const setOnlyTicked = (value) => {
            onlyTicked = value;
            onlyButton.setAttribute("aria-pressed", value ? "true" : "false");
            onlyButton.classList.toggle("is-on", value);
        };

        search.addEventListener("input", applyFilter);

        search.addEventListener("keydown", (event) => {
            if (event.key === "Enter") {
                /* Enter in the search box must not save the role. */
                event.preventDefault();
            } else if (event.key === "Escape" && search.value !== "") {
                event.preventDefault();
                event.stopPropagation();
                search.value = "";
                applyFilter();
            } else if (event.key === "ArrowDown") {
                const first = list.querySelector('section:not([hidden]) input[type="checkbox"]:not(:disabled)');

                if (first) {
                    event.preventDefault();
                    first.focus();
                }
            }
        });

        /* Arrow keys walk the boxes in view, so the list is quick without a mouse. */
        list.addEventListener("keydown", (event) => {
            if (event.key !== "ArrowDown" && event.key !== "ArrowUp") {
                return;
            }

            if (!(event.target instanceof HTMLInputElement) || event.target.type !== "checkbox") {
                return;
            }

            const boxes = Array.from(list.querySelectorAll('input[type="checkbox"]:not(:disabled)')).filter((box) => box.offsetParent !== null || box.getClientRects().length > 0);
            const index = boxes.indexOf(event.target);
            const next = boxes[index + (event.key === "ArrowDown" ? 1 : -1)];

            event.preventDefault();

            if (next) {
                next.focus();
            } else if (event.key === "ArrowUp") {
                search.focus();
            }
        });

        list.addEventListener("change", (event) => {
            const target = event.target;

            if (!(target instanceof HTMLInputElement)) {
                return;
            }

            if (target.hasAttribute("data-picker-group-toggle")) {
                const group = groups.find((entry) => entry.toggle === target);
                const want = target.checked;

                shown(group).forEach((item) => {
                    item.input.checked = want;
                });
            }

            refresh();
        });

        groups.forEach((group) => {
            group.fold.addEventListener("click", () => {
                setFold(group, group.node.classList.contains("is-folded"));
            });
        });

        allButton.addEventListener("click", () => {
            everyShown().forEach((item) => {
                item.input.checked = true;
            });
            refresh();
        });

        noneButton.addEventListener("click", () => {
            everyShown().forEach((item) => {
                item.input.checked = false;
            });
            /* Clearing while showing ticked only would leave an empty list behind. */
            if (onlyTicked) {
                setOnlyTicked(false);
                applyFilter();
                return;
            }
            refresh();
        });

        onlyButton.addEventListener("click", () => {
            setOnlyTicked(!onlyTicked);
            applyFilter();
        });

        const reset = () => {
            everyItem().forEach((item) => {
                item.input.checked = false;
            });
            search.value = "";
            setOnlyTicked(false);
            groups.forEach((group) => setFold(group, true));
            list.scrollTop = 0;
            applyFilter();
        };

        const setTicked = (ids) => {
            const wanted = new Set((Array.isArray(ids) ? ids : []).map((id) => String(id)));

            everyItem().forEach((item) => {
                item.input.checked = wanted.has(String(item.input.value));
            });
            applyFilter();
        };

        applyFilter();

        return {
            reset,
            setTicked,
            revealError() {
                list.classList.add("border-danger");
            },
        };
    };

    const pickers = {
        addForm: createPicker(document.querySelector("#addForm")),
        editForm: createPicker(document.querySelector("#editForm")),
    };

    const pickerOf = ($form) => pickers[$form.attr("id")];

    /* ------------------------------------------------------------------ */

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").text("");
        $form.find(".border-danger").removeClass("border-danger");
    };

    /*
     * Laravel keys array errors by position ("policy_ids.3"): every one of them
     * belongs above the tick list, said once.
     */
    const showErrors = ($form, errors) => {
        const grouped = {};

        clearErrors($form);

        Object.entries(errors || {}).forEach(([key, value]) => {
            const field = String(key).split(".")[0];
            const messages = Array.isArray(value) ? value : [value];

            if (!/^[A-Za-z0-9_-]+$/.test(field)) {
                return;
            }

            grouped[field] = grouped[field] || [];
            messages.forEach((message) => {
                if (!grouped[field].includes(message)) {
                    grouped[field].push(message);
                }
            });
        });

        Object.entries(grouped).forEach(([field, messages]) => {
            $form.find(`.${field}`).addClass("border-danger");
            $form.find(`.error-${field}`).text(messages.join(" "));
        });

        /* The form scrolls: bring the first problem into view. */
        const first = $form.find(".acc__input-error").filter(function () {
            return $(this).text() !== "";
        }).get(0);

        if (first) {
            const anchor = first.closest(".ss-modal-field, .pa-role-picker") || first;

            anchor.scrollIntoView({ block: "nearest" });
        }
    };

    const setBusy = (selector, busy) => {
        const button = document.querySelector(selector);

        if (!button) {
            return;
        }

        button.disabled = busy;
        const spinner = button.querySelector(".ss-spinner");

        if (spinner) {
            spinner.style.cssText = busy ? "display: inline-block;" : "display: none;";
        }
    };

    const showSuccess = (title, description) => {
        $("#successModal .successModalTitle").text(title);
        $("#successModal .successModalDesc").text(description);
        successModal.show();
    };

    const showWarning = (title, description) => {
        $("#warningModal .successModalTitle").text(title);
        $("#warningModal .successModalDesc").text(description);
        warningModal.show();
    };

    const errorMessage = (error, fallback) => {
        return error?.response?.data?.message || fallback;
    };

    const showConfirm = (title, description, action, recordID) => {
        $("#confirmModal .confModTitle").text(title);
        $("#confirmModal .confModDesc").text(description);
        $("#confirmModal .agreeWith").attr("data-id", recordID);
        $("#confirmModal .agreeWith").attr("data-action", action);
        confirmModal.show();
    };

    /* The Active card's wording follows its checkbox. */
    const syncToggleCopy = ($form) => {
        $form.find(".pa-active-toggle").each(function () {
            const checked = $(this).find("input").is(":checked");

            $(this).find("[data-on]").each(function () {
                $(this).text(checked ? $(this).attr("data-on") : $(this).attr("data-off"));
            });
        });
    };

    const resetForm = ($form, activeByDefault) => {
        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[type="text"], input[type="number"], textarea').val("");
        $form.find('input[name="id"]').val("0");
        $form.find('input[name="is_active"]').prop("checked", activeByDefault);
        syncToggleCopy($form);
        pickerOf($form)?.reset();
        $form.find(".ss-settings-modal__body").scrollTop(0);
    };

    const statusLabel = (data) => {
        if (data.deleted_at != null) {
            return "Archived";
        }

        return data.is_active == 1 ? "Active" : "Inactive";
    };

    const nameFormatter = (cell) => {
        const data = cell.getData();
        const description = data.description ? `<small title="${escapeHtml(data.description)}">${escapeHtml(data.description)}</small>` : "";

        return `<span class="pa-name-cell"><span class="pa-name-cell__icon">${iconSvg("briefcase")}</span><span class="pa-name-cell__body"><strong>${escapeHtml(data.name)}</strong>${description}</span></span>`;
    };

    /* "12 policies" over "Corporate 5 · Academic 4 · Student 3". */
    const policiesFormatter = (cell) => {
        const data = cell.getData();
        const total = Number(data.policies_count || 0);
        const inactive = Number(data.inactive_policies_count || 0);
        const breakdown = Array.isArray(data.breakdown) ? data.breakdown : [];

        if (total === 0) {
            return `<span class="pa-stack pa-role-policies"><strong>No policies</strong><small class="pa-muted-warn">${iconSvg("alert-triangle")}Nothing to assign yet</small></span>`;
        }

        const parts = breakdown.map((part) => {
            return `<span class="pa-role-breakdown__part" title="${escapeHtml(part.name)}${part.archived == 1 ? " (archived category)" : ""}">${escapeHtml(part.label)} <b>${escapeHtml(part.count)}</b></span>`;
        });
        const note = inactive > 0
            ? `<small class="pa-muted-warn" title="Switched off or in an archived category: not assigned until switched on.">${iconSvg("alert-triangle")}${inactive} not active</small>`
            : "";

        return `<span class="pa-stack pa-role-policies"><strong>${total} ${plural(total, "policy", "policies")}</strong><small class="pa-role-breakdown">${parts.join('<span class="pa-meta__dot" aria-hidden="true">·</span>')}</small>${note}</span>`;
    };

    const assignedFormatter = (cell) => {
        const data = cell.getData();
        const assigned = Number(data.assigned || 0);
        const staff = Number(data.assigned_staff || 0);

        if (assigned === 0) {
            return `<span class="pa-bank-num pa-bank-num--muted">Not used yet</span>`;
        }

        return `<span class="pa-stack"><strong>${assigned} ${plural(assigned, "assessment", "assessments")}</strong><small>${staff} ${plural(staff, "member of staff", "staff")}</small></span>`;
    };

    const statusFormatter = (cell) => {
        const data = cell.getData();
        const label = statusLabel(data);
        const css = label === "Active" ? "is-active" : label === "Archived" ? "pa-pill--archived" : "is-inactive";

        return `<span class="ss-status-pill ${css}"><span></span>${label}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();
        const id = escapeHtml(data.id);

        if (data.deleted_at != null) {
            return `<button data-id="${id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore role" title="Restore">${iconSvg("rotate-cw")}</button>`;
        }

        return [
            `<button data-id="${id}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit role" title="Edit">${iconSvg("pencil")}</button>`,
            `<button data-id="${id}" data-assigned="${escapeHtml(data.assigned)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive role" title="Archive">${iconSvg("trash-2")}</button>`,
        ].join("");
    };

    /* First run: a call to action instead of an empty table. */
    const showTableArea = () => {
        hasRoles = true;
        card?.setAttribute("data-has-roles", "1");

        if (emptyState) {
            emptyState.hidden = true;
        }

        if (tableArea) {
            tableArea.hidden = false;
        }
    };

    const buildTable = () => {
        if (!hasRoles) {
            return;
        }

        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "3";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#policyRoleTable", {
            ajaxURL: route("policy.assessment.role.list"),
            ajaxParams: { querystr, status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No roles found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 72,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 2,
                    formatter: nameFormatter,
                },
                {
                    title: "Description",
                    field: "description",
                    visible: false,
                    download: true,
                },
                {
                    title: "Policies",
                    field: "policies_count",
                    headerHozAlign: "left",
                    minWidth: 240,
                    widthGrow: 1.8,
                    formatter: policiesFormatter,
                    accessorDownload: (value, data) => data.policies_summary || "",
                },
                {
                    title: "Assigned",
                    field: "assigned",
                    headerHozAlign: "left",
                    minWidth: 140,
                    widthGrow: 0.9,
                    formatter: assignedFormatter,
                },
                {
                    title: "Sort",
                    field: "sort_order",
                    headerHozAlign: "left",
                    width: 82,
                },
                {
                    title: "Status",
                    field: "is_active",
                    headerHozAlign: "left",
                    minWidth: 120,
                    widthGrow: 0.6,
                    formatter: statusFormatter,
                    accessorDownload: (value, data) => statusLabel(data),
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 106,
                    minWidth: 106,
                    download: false,
                    formatter: actionFormatter,
                },
            ],
            renderComplete() {
                refreshIcons();
            },
        });
    };

    buildTable();
    refreshIcons();

    $("#tabulatorFilterForm").on("keypress.pa-roles", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#status").on("change.pa-roles", buildTable);
    $("#tabulator-html-filter-go").on("click.pa-roles", buildTable);

    $("#tabulator-html-filter-reset").on("click.pa-roles", function () {
        $("#query").val("");
        $("#status").val("3");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.pa-roles", function () {
        tableContent?.download("csv", "policy-roles.csv");
    });

    $("#tabulator-export-xlsx").on("click.pa-roles", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "policy-roles.xlsx", {
            sheetName: "Policy Roles",
        });
    });

    $("#tabulator-print").on("click.pa-roles", function () {
        tableContent?.print();
    });

    $("#addForm, #editForm").on("change.pa-roles", ".pa-active-toggle input", function () {
        syncToggleCopy($(this).closest("form"));
    });

    document.getElementById("addModal")?.addEventListener("show.tw.modal", function () {
        resetForm($("#addForm"), true);
    });

    document.getElementById("addModal")?.addEventListener("shown.tw.modal", function () {
        document.querySelector("#add_role_name")?.focus();
    });

    document.getElementById("addModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#addForm"), true);
    });

    document.getElementById("editModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#editForm"), false);
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    const handleFailure = ($form, error, button) => {
        setBusy(button, false);

        if (error.response?.status == 422) {
            const errors = error.response.data?.errors;

            if (errors && Object.keys(errors).length > 0) {
                showErrors($form, errors);

                if (errors.policy_ids || Object.keys(errors).some((key) => key.startsWith("policy_ids."))) {
                    pickerOf($form)?.revealError();
                }

                return true;
            }
        }

        return false;
    };

    $("#addForm").on("submit.pa-roles", function (event) {
        event.preventDefault();

        const $form = $("#addForm");

        clearErrors($form);
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("policy.assessment.role.store"),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#save", false);

            if (response.status == 200) {
                const policies = Number(response.data?.policies || 0);

                addModal.hide();
                showSuccess("Saved", `The role has been added with ${policies} ${plural(policies, "policy", "policies")}.`);
                showTableArea();
                buildTable();
            }
        }).catch((error) => {
            if (handleFailure($form, error, "#save")) {
                return;
            }

            showWarning("Not saved", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    $("#policyRoleTable").on("click.pa-roles", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editForm");

        resetForm($form, false);

        axios({
            method: "get",
            url: route("policy.assessment.role.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data || {};

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find('textarea[name="description"]').val(dataset.description || "");
                $form.find('input[name="sort_order"]').val(dataset.sort_order ?? "");
                $form.find('input[name="is_active"]').prop("checked", dataset.is_active == 1);
                $form.find('input[name="id"]').val(editId);
                syncToggleCopy($form);
                pickerOf($form)?.setTicked(dataset.policy_ids);
                editModal.show();
                refreshIcons();
            }
        }).catch((error) => {
            showWarning("Could not open", errorMessage(error, "This role could not be loaded."));
        });
    });

    $("#editForm").on("submit.pa-roles", function (event) {
        event.preventDefault();

        const $form = $("#editForm");
        const editId = $form.find('input[name="id"]').val();

        clearErrors($form);
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("policy.assessment.role.update", editId),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#update", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Saved", "The role has been updated. Assessments that are already assigned have not changed.");
                buildTable();
            }
        }).catch((error) => {
            if (handleFailure($form, error, "#update")) {
                return;
            }

            if (error.response?.status == 304) {
                editModal.hide();
                showSuccess("No changes", "The role was already up to date.");
                return;
            }

            showWarning("Not saved", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    $("#policyRoleTable").on("click.pa-roles", ".delete_btn", function () {
        const assigned = Number($(this).attr("data-assigned") || 0);
        const detail = assigned > 0
            ? `It can no longer be picked when assigning. The ${assigned} ${plural(assigned, "assessment", "assessments")} already assigned through it ${plural(assigned, "stays", "stay")} as ${plural(assigned, "it is", "they are")}. You can restore the role later from Archived.`
            : "It can no longer be picked when assigning. You can restore it later from Archived, with its policies.";

        showConfirm("Archive this role?", detail, "DELETE", $(this).attr("data-id"));
    });

    $("#policyRoleTable").on("click.pa-roles", ".restore_btn", function () {
        showConfirm("Restore this role?", "It will be available again when assigning, with the policies it had ticked.", "RESTORE", $(this).attr("data-id"));
    });

    $("#confirmModal .agreeWith").on("click.pa-roles", function () {
        const recordID = $(this).attr("data-id");
        const action = $(this).attr("data-action");

        if (action != "DELETE" && action != "RESTORE") {
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: action == "DELETE" ? "delete" : "post",
            url: action == "DELETE" ? route("policy.assessment.role.destroy", recordID) : route("policy.assessment.role.restore", recordID),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            $("#confirmModal button").removeAttr("disabled");

            if (response.status == 200) {
                confirmModal.hide();
                showSuccess("Done", action == "DELETE" ? "The role has been archived." : "The role has been restored.");
                buildTable();
            }
        }).catch((error) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showWarning("Not done", errorMessage(error, "Something went wrong. Please try again."));
        });
    });
})();
