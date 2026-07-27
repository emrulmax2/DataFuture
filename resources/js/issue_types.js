import xlsx from "xlsx";
import { BadgeAlert, createElement, createIcons, icons, Mail, Pencil, RotateCw, Trash2, Users } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#issueTypesTableId");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let tableContent = null;

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.7,
            nameAttr: "data-lucide",
        });
    };

    const escapeHtml = (value) => {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const iconSvg = (Icon) => {
        const icon = createElement(Icon);

        icon.setAttribute("stroke-width", "1.9");
        icon.setAttribute("aria-hidden", "true");

        return icon.outerHTML;
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

    const showConfirm = (title, description, action, recordID) => {
        $("#confirmModal .confModTitle").text(title);
        $("#confirmModal .confModDesc").text(description);
        $("#confirmModal .agreeWith").attr("data-id", recordID);
        $("#confirmModal .agreeWith").attr("data-action", action);
        confirmModal.show();
    };

    const resetForm = ($form) => {
        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[type="text"], input[type="email"]').val("");
        $form.find('input[type="radio"]').prop("checked", false);
        $form.find('input[name="id"]').val("0");
    };

    const nameFormatter = (cell) => {
        const data = cell.getData();

        return `<span class="ss-issue-type-name">${iconSvg(BadgeAlert)}<span><strong>${escapeHtml(data.name)}</strong><small>Report IT category</small></span></span>`;
    };

    const availabilityFormatter = (cell) => {
        const value = cell.getValue() || "Not set";
        const normalized = ["Employee", "Student", "Both"].includes(value) ? String(value).toLowerCase() : "empty";
        const label = value == "Both" ? "Employee + Student" : value;

        return `<span class="ss-issue-availability-pill is-${normalized}">${iconSvg(Users)}${escapeHtml(label)}</span>`;
    };

    const emailFormatter = (cell) => {
        const email = cell.getValue();

        if (!email) {
            return '<span class="ss-issue-empty">No reporting email</span>';
        }

        return `<a class="ss-issue-email" href="mailto:${escapeHtml(email)}">${iconSvg(Mail)}<span>${escapeHtml(email)}</span></a>`;
    };

    const statusFormatter = (cell) => {
        const isActive = cell.getData().deleted_at == null;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Archived"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore issue type">${iconSvg(RotateCw)}</button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit issue type">${iconSvg(Pencil)}</button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive issue type">${iconSvg(Trash2)}</button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#issueTypesTableId", {
            ajaxURL: route("issue.types.list"),
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
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 82,
                    headerSort: false,
                },
                {
                    title: "Issue Type",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 240,
                    widthGrow: 1.35,
                    formatter: nameFormatter,
                },
                {
                    title: "Available To",
                    field: "availability",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 178,
                    widthGrow: 0.8,
                    formatter: availabilityFormatter,
                },
                {
                    title: "Reporting Email",
                    field: "reporting_email",
                    headerHozAlign: "left",
                    minWidth: 240,
                    widthGrow: 1.25,
                    formatter: emailFormatter,
                },
                {
                    title: "Status",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 126,
                    widthGrow: 0.55,
                    formatter: statusFormatter,
                    download: false,
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

    $("#tabulatorFilterForm").on("keypress.issue-types", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.issue-types", buildTable);

    $("#tabulator-html-filter-reset").on("click.issue-types", function () {
        $("#query").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.issue-types", function () {
        tableContent?.download("csv", "issue-types.csv");
    });

    $("#tabulator-export-xlsx").on("click.issue-types", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "issue-types.xlsx", {
            sheetName: "Issue Types",
        });
    });

    $("#tabulator-print").on("click.issue-types", function () {
        tableContent?.print();
    });

    document.getElementById("addModal")?.addEventListener("show.tw.modal", function () {
        resetForm($("#addForm"));
    });

    document.getElementById("addModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#addForm"));
    });

    document.getElementById("editModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#editForm"));
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addForm").on("submit.issue-types", function (event) {
        event.preventDefault();

        const form = document.getElementById("addForm");
        const $form = $("#addForm");

        clearErrors($form);
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("issue.types.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#save", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Success!", "Issue type successfully created.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#save", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#issueTypesTableId").on("click.issue-types", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editForm");

        resetForm($form);

        axios({
            method: "get",
            url: route("issue.types.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data || {};

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find('input[name="reporting_email"]').val(dataset.reporting_email || "");
                $form.find(`input[name="availability"][value="${dataset.availability || ""}"]`).prop("checked", true);
                $form.find('input[name="id"]').val(editId);
                editModal.show();
                refreshIcons();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editForm").on("submit.issue-types", function (event) {
        event.preventDefault();

        const form = document.getElementById("editForm");
        const $form = $("#editForm");
        const editId = $form.find('input[name="id"]').val();

        clearErrors($form);
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("issue.types.update", editId),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#update", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Issue type successfully updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#update", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#issueTypesTableId").on("click.issue-types", ".delete_btn", function () {
        showConfirm(
            "Archive issue type?",
            "This issue type will move to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#issueTypesTableId").on("click.issue-types", ".restore_btn", function () {
        showConfirm(
            "Restore issue type?",
            "This issue type will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.issue-types", function () {
        const $agreeButton = $(this);
        const recordID = $agreeButton.attr("data-id");
        const action = $agreeButton.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("issue.types.destroy", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Issue type successfully archived.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });

            return;
        }

        if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("issue.types.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Issue type successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
