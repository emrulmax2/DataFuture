import xlsx from "xlsx";
import { createElement, createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

/* Policy Assessments › Categories (HR). */
(function () {
    const tableNode = document.querySelector("#policyCategoryTable");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let tableContent = null;

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

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").text("");
        $form.find(".border-danger").removeClass("border-danger");
    };

    const showErrors = ($form, errors) => {
        clearErrors($form);

        Object.entries(errors || {}).forEach(([key, value]) => {
            const message = Array.isArray(value) ? value.join(" ") : value;

            $form.find(`.${key}`).addClass("border-danger");
            $form.find(`.error-${key}`).text(message);
        });
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
    };

    const statusLabel = (data) => {
        if (data.deleted_at != null) {
            return "Archived";
        }

        return data.is_active == 1 ? "Active" : "Inactive";
    };

    const nameFormatter = (cell) => {
        const data = cell.getData();
        const description = data.description ? `<small>${escapeHtml(data.description)}</small>` : "";

        return `<span class="pa-name-cell"><span class="pa-name-cell__icon">${iconSvg("folder-tree")}</span><span class="pa-name-cell__body"><strong>${escapeHtml(data.name)}</strong>${description}</span></span>`;
    };

    const policiesFormatter = (cell) => {
        const data = cell.getData();
        const total = Number(data.policies_count || 0);
        const active = Number(data.active_policies_count || 0);

        return `<span class="pa-stack"><strong>${total} ${total === 1 ? "policy" : "policies"}</strong><small>${active} active</small></span>`;
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
            return `<button data-id="${id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore category" title="Restore">${iconSvg("rotate-cw")}</button>`;
        }

        return [
            `<button data-id="${id}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit category" title="Edit">${iconSvg("pencil")}</button>`,
            `<button data-id="${id}" data-policies="${escapeHtml(data.policies_count)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive category" title="Archive">${iconSvg("trash-2")}</button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#policyCategoryTable", {
            ajaxURL: route("policy.assessment.category.list"),
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
            placeholder: "No categories found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 82,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 280,
                    widthGrow: 2.4,
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
                    minWidth: 130,
                    widthGrow: 0.8,
                    formatter: policiesFormatter,
                },
                {
                    title: "Sort",
                    field: "sort_order",
                    headerHozAlign: "left",
                    width: 96,
                },
                {
                    title: "Status",
                    field: "is_active",
                    headerHozAlign: "left",
                    minWidth: 130,
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
                    width: 112,
                    minWidth: 112,
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

    $("#tabulatorFilterForm").on("keypress.pa-categories", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#status").on("change.pa-categories", buildTable);
    $("#tabulator-html-filter-go").on("click.pa-categories", buildTable);

    $("#tabulator-html-filter-reset").on("click.pa-categories", function () {
        $("#query").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.pa-categories", function () {
        tableContent?.download("csv", "policy-categories.csv");
    });

    $("#tabulator-export-xlsx").on("click.pa-categories", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "policy-categories.xlsx", {
            sheetName: "Policy Categories",
        });
    });

    $("#tabulator-print").on("click.pa-categories", function () {
        tableContent?.print();
    });

    $("#addForm, #editForm").on("change.pa-categories", ".pa-active-toggle input", function () {
        syncToggleCopy($(this).closest("form"));
    });

    document.getElementById("addModal")?.addEventListener("show.tw.modal", function () {
        resetForm($("#addForm"), true);
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

    $("#addForm").on("submit.pa-categories", function (event) {
        event.preventDefault();

        const $form = $("#addForm");

        clearErrors($form);
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("policy.assessment.category.store"),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#save", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Saved", "The category has been added.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#save", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            showWarning("Not saved", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    $("#policyCategoryTable").on("click.pa-categories", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editForm");

        resetForm($form, false);

        axios({
            method: "get",
            url: route("policy.assessment.category.edit", editId),
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
                editModal.show();
                refreshIcons();
            }
        }).catch((error) => {
            showWarning("Could not open", errorMessage(error, "This category could not be loaded."));
        });
    });

    $("#editForm").on("submit.pa-categories", function (event) {
        event.preventDefault();

        const $form = $("#editForm");
        const editId = $form.find('input[name="id"]').val();

        clearErrors($form);
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("policy.assessment.category.update", editId),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#update", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Saved", "The category has been updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#update", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            if (error.response?.status == 304) {
                editModal.hide();
                showSuccess("No changes", "The category was already up to date.");
                return;
            }

            showWarning("Not saved", errorMessage(error, "Something went wrong. Please try again."));
        });
    });

    $("#policyCategoryTable").on("click.pa-categories", ".delete_btn", function () {
        const policies = Number($(this).attr("data-policies") || 0);
        const detail = policies > 0
            ? `Its ${policies} ${policies === 1 ? "policy stays" : "policies stay"} in the bank, but staff cannot start those tests until you restore the category.`
            : "You can restore it later from Archived.";

        showConfirm("Archive this category?", detail, "DELETE", $(this).attr("data-id"));
    });

    $("#policyCategoryTable").on("click.pa-categories", ".restore_btn", function () {
        showConfirm("Restore this category?", "It will be available again, with its policies.", "RESTORE", $(this).attr("data-id"));
    });

    $("#confirmModal .agreeWith").on("click.pa-categories", function () {
        const recordID = $(this).attr("data-id");
        const action = $(this).attr("data-action");

        if (action != "DELETE" && action != "RESTORE") {
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: action == "DELETE" ? "delete" : "post",
            url: action == "DELETE" ? route("policy.assessment.category.destroy", recordID) : route("policy.assessment.category.restore", recordID),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            $("#confirmModal button").removeAttr("disabled");

            if (response.status == 200) {
                confirmModal.hide();
                showSuccess("Done", action == "DELETE" ? "The category has been archived." : "The category has been restored.");
                buildTable();
            }
        }).catch((error) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showWarning("Not done", errorMessage(error, "Something went wrong. Please try again."));
        });
    });
})();
