import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#dfFieldTableId");

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

    const resetForm = ($form) => {
        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[type="text"]').val("");
        $form.find('input[name="id"]').val("0");
        $form.find("select").val("");
        $form.find("textarea").val("");
    };

    const nameFormatter = (cell) => {
        return `<span class="ss-df-field-name"><i data-lucide="braces"></i><strong>${escapeHtml(cell.getValue())}</strong></span>`;
    };

    const categoryFormatter = (cell) => {
        const category = cell.getValue();

        if (!category) {
            return '<span class="ss-df-field-empty">Not assigned</span>';
        }

        return `<span class="ss-df-field-category-pill"><i data-lucide="folder-tree"></i>${escapeHtml(category)}</span>`;
    };

    const typeFormatter = (cell) => {
        const type = String(cell.getValue() || "").toLowerCase();
        const label = type ? type.charAt(0).toUpperCase() + type.slice(1) : "Not set";

        return `<span class="ss-df-field-type-pill is-${escapeHtml(type || "none")}">${escapeHtml(label)}</span>`;
    };

    const descriptionFormatter = (cell) => {
        const description = cell.getValue();

        if (!description) {
            return '<span class="ss-df-field-empty">No description</span>';
        }

        return `<span class="ss-cell-wrap ss-df-field-description">${escapeHtml(description)}</span>`;
    };

    const statusFormatter = (cell) => {
        const isActive = cell.getData().deleted_at == null;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Archived"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore field"><i data-lucide="rotate-cw"></i></button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit field"><i data-lucide="pencil"></i></button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive field"><i data-lucide="trash-2"></i></button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const category = $("#category").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#dfFieldTableId", {
            ajaxURL: route("df.fields.list"),
            ajaxParams: { querystr, status, category },
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
                },
                {
                    title: "Category",
                    field: "category",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 154,
                    widthGrow: 1,
                    formatter: categoryFormatter,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 180,
                    widthGrow: 1.2,
                    formatter: nameFormatter,
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                    minWidth: 104,
                    widthGrow: 0.6,
                    formatter: typeFormatter,
                },
                {
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 1.6,
                    formatter: descriptionFormatter,
                },
                {
                    title: "Status",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 126,
                    widthGrow: 0.7,
                    formatter: statusFormatter,
                    download: false,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 116,
                    minWidth: 116,
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

    $("#tabulatorFilterForm").on("keypress.df-field", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.df-field", buildTable);

    $("#tabulator-html-filter-reset").on("click.df-field", function () {
        $("#query").val("");
        $("#category").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.df-field", function () {
        tableContent?.download("csv", "df-fields.csv");
    });

    $("#tabulator-export-xlsx").on("click.df-field", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "df-fields.xlsx", {
            sheetName: "DF Fields",
        });
    });

    $("#tabulator-print").on("click.df-field", function () {
        tableContent?.print();
    });

    $(".inputUppercase").on("input.df-field", function () {
        $(this).val($(this).val().toUpperCase());
    });

    document.getElementById("addSettingsModal")?.addEventListener("show.tw.modal", function () {
        resetForm($("#addSettingsForm"));
    });

    document.getElementById("addSettingsModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#addSettingsForm"));
    });

    document.getElementById("editSettingsModal")?.addEventListener("hide.tw.modal", function () {
        resetForm($("#editSettingsForm"));
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addSettingsForm").on("submit.df-field", function (event) {
        event.preventDefault();

        const form = document.getElementById("addSettingsForm");
        const $form = $("#addSettingsForm");

        clearErrors($form);
        setBusy("#saveSettings", true);

        axios({
            method: "post",
            url: route("df.fields.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#saveSettings", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Success!", "Datafuture field successfully created.");
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

    $("#dfFieldTableId").on("click.df-field", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editSettingsForm");

        resetForm($form);

        axios({
            method: "get",
            url: route("df.fields.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find('select[name="datafuture_field_category_id"]').val(dataset.datafuture_field_category_id || "");
                $form.find('select[name="type"]').val(dataset.type || "");
                $form.find('textarea[name="description"]').val(dataset.description || "");
                $form.find('input[name="id"]').val(editId);
                editModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editSettingsForm").on("submit.df-field", function (event) {
        event.preventDefault();

        const form = document.getElementById("editSettingsForm");
        const $form = $("#editSettingsForm");

        clearErrors($form);
        setBusy("#updateSettings", true);

        axios({
            method: "post",
            url: route("df.fields.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#updateSettings", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Datafuture field successfully updated.");
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

    $("#dfFieldTableId").on("click.df-field", ".delete_btn", function () {
        showConfirm(
            "Archive field?",
            "This Datafuture field will move to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#dfFieldTableId").on("click.df-field", ".restore_btn", function () {
        showConfirm(
            "Restore field?",
            "This Datafuture field will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.df-field", function () {
        const $agreeButton = $(this);
        const recordID = $agreeButton.attr("data-id");
        const action = $agreeButton.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("df.fields.destory", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Datafuture field successfully archived.");
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
                url: route("df.fields.restore"),
                data: { id: recordID },
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Datafuture field successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
