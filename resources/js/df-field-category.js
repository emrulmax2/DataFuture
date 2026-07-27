import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#dfFieldCategoryTableId");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSettingsModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSettingsModal"));
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
        const $form = $("#addSettingsForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[name="name"]').val("");
    };

    const resetEditForm = () => {
        const $form = $("#editSettingsForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[name="name"]').val("");
        $form.find('input[name="id"]').val("0");
    };

    const nameFormatter = (cell) => {
        return `<span class="ss-df-category-name"><i data-lucide="folder-tree"></i><span><strong>${escapeHtml(cell.getValue())}</strong><small>Datafuture field group</small></span></span>`;
    };

    const statusFormatter = (cell) => {
        const isActive = cell.getData().deleted_at == null;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Archived"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore category"><i data-lucide="rotate-cw"></i></button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit category"><i data-lucide="pencil"></i></button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive category"><i data-lucide="trash-2"></i></button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#dfFieldCategoryTableId", {
            ajaxURL: route("df.field.categories.list"),
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
                    minWidth: 280,
                    widthGrow: 2,
                    formatter: nameFormatter,
                },
                {
                    title: "Status",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 150,
                    formatter: statusFormatter,
                    download: false,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 130,
                    minWidth: 130,
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

    $("#tabulatorFilterForm").on("keypress.df-field-category", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.df-field-category", buildTable);

    $("#tabulator-html-filter-reset").on("click.df-field-category", function () {
        $("#query").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.df-field-category", function () {
        tableContent?.download("csv", "df-field-categories.csv");
    });

    $("#tabulator-export-xlsx").on("click.df-field-category", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "df-field-categories.xlsx", {
            sheetName: "DF Field Categories",
        });
    });

    $("#tabulator-print").on("click.df-field-category", function () {
        tableContent?.print();
    });

    document.getElementById("addSettingsModal")?.addEventListener("show.tw.modal", resetAddForm);
    document.getElementById("addSettingsModal")?.addEventListener("hide.tw.modal", resetAddForm);
    document.getElementById("editSettingsModal")?.addEventListener("hide.tw.modal", resetEditForm);

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addSettingsForm").on("submit.df-field-category", function (event) {
        event.preventDefault();

        const form = document.getElementById("addSettingsForm");
        const $form = $("#addSettingsForm");

        clearErrors($form);
        setBusy("#saveSettings", true);

        axios({
            method: "post",
            url: route("df.field.categories.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#saveSettings", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Success!", "Datafuture field category successfully created.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#saveSettings", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#dfFieldCategoryTableId").on("click.df-field-category", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editSettingsForm");

        resetEditForm();

        axios({
            method: "get",
            url: route("df.field.categories.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find('input[name="id"]').val(editId);
                editModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editSettingsForm").on("submit.df-field-category", function (event) {
        event.preventDefault();

        const form = document.getElementById("editSettingsForm");
        const $form = $("#editSettingsForm");

        clearErrors($form);
        setBusy("#updateSettings", true);

        axios({
            method: "post",
            url: route("df.field.categories.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#updateSettings", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Datafuture field category successfully updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#updateSettings", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#dfFieldCategoryTableId").on("click.df-field-category", ".delete_btn", function () {
        showConfirm(
            "Archive category?",
            "This Datafuture field category will move to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#dfFieldCategoryTableId").on("click.df-field-category", ".restore_btn", function () {
        showConfirm(
            "Restore category?",
            "This Datafuture field category will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.df-field-category", function () {
        const $agreeButton = $(this);
        const recordID = $agreeButton.attr("data-id");
        const action = $agreeButton.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("df.field.categories.destory", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Datafuture field category successfully archived.");
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
                url: route("df.field.categories.restore"),
                data: { id: recordID },
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Datafuture field category successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
