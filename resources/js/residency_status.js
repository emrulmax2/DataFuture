import xlsx from "xlsx";
import { BadgeCheck, createElement, createIcons, icons, Pencil, RotateCw, Trash2 } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#residencyStatusTableId");

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
        $form.find('input[type="text"]').val("");
        $form.find('input[name="id"]').val("0");
    };

    const nameFormatter = (cell) => {
        return `<span class="ss-residency-status-name">${iconSvg(BadgeCheck)}<span><strong>${escapeHtml(cell.getValue())}</strong><small>Student residency option</small></span></span>`;
    };

    const statusFormatter = (cell) => {
        const isActive = cell.getData().deleted_at == null;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Archived"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore residency status">${iconSvg(RotateCw)}</button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit residency status">${iconSvg(Pencil)}</button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive residency status">${iconSvg(Trash2)}</button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#residencyStatusTableId", {
            ajaxURL: route("residency.status.list"),
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
                    title: "Residency Status",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 300,
                    widthGrow: 2,
                    formatter: nameFormatter,
                },
                {
                    title: "Status",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 140,
                    widthGrow: 0.65,
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

    $("#tabulatorFilterForm").on("keypress.residency-status", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.residency-status", buildTable);

    $("#tabulator-html-filter-reset").on("click.residency-status", function () {
        $("#query").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.residency-status", function () {
        tableContent?.download("csv", "residency-statuses.csv");
    });

    $("#tabulator-export-xlsx").on("click.residency-status", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "residency-statuses.xlsx", {
            sheetName: "Residency Status",
        });
    });

    $("#tabulator-print").on("click.residency-status", function () {
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

    $("#addForm").on("submit.residency-status", function (event) {
        event.preventDefault();

        const form = document.getElementById("addForm");
        const $form = $("#addForm");

        clearErrors($form);
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("residency.status.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#save", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Success!", "Residency status successfully created.");
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

    $("#residencyStatusTableId").on("click.residency-status", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editForm");

        resetForm($form);

        axios({
            method: "get",
            url: route("residency.status.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data || {};

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find('input[name="id"]').val(editId);
                editModal.show();
                refreshIcons();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editForm").on("submit.residency-status", function (event) {
        event.preventDefault();

        const form = document.getElementById("editForm");
        const $form = $("#editForm");
        const editId = $form.find('input[name="id"]').val();

        clearErrors($form);
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("residency.status.update", editId),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#update", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Residency status successfully updated.");
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
                showSuccess("No changes", "Residency status was already up to date.");
                return;
            }

            console.log(error);
        });
    });

    $("#residencyStatusTableId").on("click.residency-status", ".delete_btn", function () {
        showConfirm(
            "Archive residency status?",
            "This residency status will move to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#residencyStatusTableId").on("click.residency-status", ".restore_btn", function () {
        showConfirm(
            "Restore residency status?",
            "This residency status will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.residency-status", function () {
        const $agreeButton = $(this);
        const recordID = $agreeButton.attr("data-id");
        const action = $agreeButton.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("residency.status.destroy", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Residency status successfully archived.");
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
                url: route("residency.status.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Residency status successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
