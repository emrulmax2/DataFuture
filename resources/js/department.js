import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#departmentTableId");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addDepartmentModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editDepartmentModal"));
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

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").html("");
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

    const resetAddForm = () => {
        const $form = $("#addDepartmentForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[name="name"]').val("");
        $form.find('input[name="available_for_all"][value="1"]').prop("checked", true);
    };

    const resetEditForm = () => {
        const $form = $("#editDepartmentForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[name="name"]').val("");
        $form.find('input[name="id"]').val("0");
        $form.find('input[name="available_for_all"]').prop("checked", false);
    };

    const nameFormatter = (cell) => {
        return `<span class="ss-department-name"><i data-lucide="building-2"></i><strong>${escapeHtml(cell.getValue())}</strong></span>`;
    };

    const availabilityFormatter = (cell) => {
        const isAvailable = cell.getValue() == 1;

        return `<span class="ss-status-pill ${isAvailable ? "is-active" : "is-inactive"}"><span></span>${isAvailable ? "Yes" : "No"}</span>`;
    };

    const statusFormatter = (cell) => {
        const isActive = cell.getData().deleted_at == null;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Archived"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore department"><i data-lucide="rotate-cw"></i></button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit department"><i data-lucide="pencil"></i></button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive department"><i data-lucide="trash-2"></i></button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#departmentTableId", {
            ajaxURL: route("department.list"),
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
                    width: 92,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 260,
                    widthGrow: 2,
                    formatter: nameFormatter,
                },
                {
                    title: "Available For All",
                    field: "available_for_all",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 170,
                    formatter: availabilityFormatter,
                },
                {
                    title: "Status",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 140,
                    formatter: statusFormatter,
                    download: false,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 120,
                    minWidth: 120,
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

    $("#tabulatorFilterForm").on("keypress.departments", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.departments", buildTable);

    $("#tabulator-html-filter-reset").on("click.departments", function () {
        $("#query").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.departments", function () {
        tableContent?.download("csv", "departments.csv");
    });

    $("#tabulator-export-xlsx").on("click.departments", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "departments.xlsx", {
            sheetName: "Departments",
        });
    });

    $("#tabulator-print").on("click.departments", function () {
        tableContent?.print();
    });

    document.getElementById("addDepartmentModal")?.addEventListener("show.tw.modal", resetAddForm);
    document.getElementById("addDepartmentModal")?.addEventListener("hide.tw.modal", resetAddForm);
    document.getElementById("editDepartmentModal")?.addEventListener("hide.tw.modal", resetEditForm);

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addDepartmentForm").on("submit.departments", function (event) {
        event.preventDefault();

        const form = document.getElementById("addDepartmentForm");
        const $form = $("#addDepartmentForm");

        clearErrors($form);
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("department.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#save", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Success!", "Department successfully created.");
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

    $("#departmentTableId").on("click.departments", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editDepartmentForm");

        resetEditForm();

        axios({
            method: "get",
            url: route("department.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const availableValue = dataset.available_for_all == 1 ? "1" : "0";

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find(`input[name="available_for_all"][value="${availableValue}"]`).prop("checked", true);
                $form.find('input[name="id"]').val(editId);
                editModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editDepartmentForm").on("submit.departments", function (event) {
        event.preventDefault();

        const form = document.getElementById("editDepartmentForm");
        const $form = $("#editDepartmentForm");

        clearErrors($form);
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("department.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#update", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Department successfully updated.");
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

    $("#departmentTableId").on("click.departments", ".delete_btn", function () {
        showConfirm(
            "Archive department?",
            "This department will move to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#departmentTableId").on("click.departments", ".restore_btn", function () {
        showConfirm(
            "Restore department?",
            "This department will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.departments", function () {
        const $agreeBTN = $(this);
        const recordID = $agreeBTN.attr("data-id");
        const action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("department.destory", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Department successfully archived.");
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
                url: route("department.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Department successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
