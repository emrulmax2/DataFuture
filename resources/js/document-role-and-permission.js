import xlsx from "xlsx";
import { Check, createElement, createIcons, icons, Pencil, RotateCw, ShieldCheck, Trash2, X } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#docRolePermissionTable");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const addPermissionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPermissionModal"));
    const editPermissionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPermissionModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
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

    const showFieldErrors = ($form, errors) => {
        Object.entries(errors || {}).forEach(([key, value]) => {
            $form.find(`.${key}`).addClass("border-danger");
            $form.find(`.error-${key}`).text(Array.isArray(value) ? value[0] : value);
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
        $form.find('input[type="text"]').val("");
        $form.find('input[type="checkbox"]').prop("checked", false);
        $form.find('input[name="id"]').val("0");
    };

    const validatePermissions = ($form) => {
        const checkedLength = $form.find('input[type="checkbox"]:checked').length;

        if (checkedLength > 0) {
            return true;
        }

        $form.find("[data-permission-group]").addClass("border-danger");
        $form.find(".error-checkboxs").text("Please select at least one permission.");

        return false;
    };

    const roleFormatter = (cell) => {
        const data = cell.getData();

        return `<span class="ss-doc-role-name">${iconSvg(ShieldCheck)}<span><strong>${escapeHtml(data.display_name)}</strong><small>${escapeHtml(data.type || "Document role")}</small></span></span>`;
    };

    const typeFormatter = (cell) => {
        const value = cell.getValue();

        return `<span class="ss-doc-role-type-pill">${escapeHtml(value || "Not set")}</span>`;
    };

    const permissionFormatter = (field) => {
        return (cell) => {
            const enabled = cell.getData()[field] == 1;

            return `<span class="ss-doc-permission-pill ${enabled ? "is-yes" : "is-no"}" role="img" aria-label="${enabled ? "Allowed" : "Not allowed"}" title="${enabled ? "Allowed" : "Not allowed"}">${iconSvg(enabled ? Check : X)}</span>`;
        };
    };

    const statusFormatter = (cell) => {
        const data = cell.getData();
        const archived = data.deleted_at != null;

        return `<span class="ss-status-pill ${archived ? "is-inactive" : "is-active"}"><span></span>${archived ? "Archived" : "Active"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore document role">${iconSvg(RotateCw)}</button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit document role">${iconSvg(Pencil)}</button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive document role">${iconSvg(Trash2)}</button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#docRolePermissionTable", {
            ajaxURL: route("site.settings.doc.role.permission.list"),
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
                    width: 76,
                    headerSort: false,
                },
                {
                    title: "Display Name",
                    field: "display_name",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 1.45,
                    formatter: roleFormatter,
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                    minWidth: 140,
                    widthGrow: 0.78,
                    formatter: typeFormatter,
                },
                {
                    title: "Create",
                    field: "create",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 105,
                    widthGrow: 0.48,
                    formatter: permissionFormatter("create"),
                    download: false,
                },
                {
                    title: "Read",
                    field: "read",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 105,
                    widthGrow: 0.48,
                    formatter: permissionFormatter("read"),
                    download: false,
                },
                {
                    title: "Update",
                    field: "update",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 105,
                    widthGrow: 0.48,
                    formatter: permissionFormatter("update"),
                    download: false,
                },
                {
                    title: "Delete",
                    field: "delete",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 105,
                    widthGrow: 0.48,
                    formatter: permissionFormatter("delete"),
                    download: false,
                },
                {
                    title: "Status",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 122,
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

    $("#tabulatorFilterForm").on("keypress.doc-role", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.doc-role", buildTable);

    $("#tabulator-html-filter-reset").on("click.doc-role", function () {
        $("#query").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.doc-role", function () {
        tableContent?.download("csv", "document-role-permissions.csv");
    });

    $("#tabulator-export-xlsx").on("click.doc-role", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "document-role-permissions.xlsx", {
            sheetName: "Document Roles",
        });
    });

    $("#tabulator-print").on("click.doc-role", function () {
        tableContent?.print();
    });

    document.getElementById("addPermissionModal")?.addEventListener("show.tw.modal", function () {
        resetForm($("#addPermissionForm"));
    });

    document.getElementById("addPermissionModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#addPermissionForm"));
    });

    document.getElementById("editPermissionModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#editPermissionForm"));
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addPermissionForm").on("submit.doc-role", function (event) {
        event.preventDefault();

        const $form = $(this);
        clearErrors($form);

        if (!validatePermissions($form)) {
            return;
        }

        setBusy("#saveRole", true);

        axios({
            method: "post",
            url: route("site.settings.doc.role.permission.store"),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#saveRole", false);

            if (response.status == 200) {
                addPermissionModal.hide();
                showSuccess("Success!", "Document role and permissions successfully created.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#saveRole", false);

            if (error.response?.status == 422) {
                showFieldErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#docRolePermissionTable").on("click.doc-role", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editPermissionForm");

        resetForm($form);

        axios({
            method: "post",
            url: route("site.settings.doc.role.permission.edit"),
            data: { row_id: editId },
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data || {};

                $form.find('[name="id"]').val(dataset.id || editId);
                $form.find('[name="display_name"]').val(dataset.display_name || "");
                $form.find('[name="type"]').val(dataset.type || "");
                $form.find('[name="create"]').prop("checked", dataset.create == 1);
                $form.find('[name="read"]').prop("checked", dataset.read == 1);
                $form.find('[name="update"]').prop("checked", dataset.update == 1);
                $form.find('[name="delete"]').prop("checked", dataset.delete == 1);

                editPermissionModal.show();
                refreshIcons();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editPermissionForm").on("submit.doc-role", function (event) {
        event.preventDefault();

        const $form = $(this);
        clearErrors($form);

        if (!validatePermissions($form)) {
            return;
        }

        setBusy("#updateRole", true);

        axios({
            method: "post",
            url: route("site.settings.doc.role.permission.update"),
            data: new FormData(this),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#updateRole", false);

            if (response.status == 200) {
                editPermissionModal.hide();
                showSuccess("Success!", "Document role and permissions successfully updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#updateRole", false);

            if (error.response?.status == 422) {
                showFieldErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#docRolePermissionTable").on("click.doc-role", ".delete_btn", function () {
        showConfirm(
            "Archive document role?",
            "This role will move to archived records and can be restored later.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#docRolePermissionTable").on("click.doc-role", ".restore_btn", function () {
        showConfirm(
            "Restore document role?",
            "This role will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.doc-role", function () {
        const $agreeButton = $(this);
        const recordID = $agreeButton.attr("data-id");
        const action = $agreeButton.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("site.settings.doc.role.permission.destory", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Document role successfully archived.");
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
                url: route("site.settings.doc.role.permission.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Success!", "Document role successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
