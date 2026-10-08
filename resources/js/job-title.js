import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#jobTitleTableId");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addJobTitleModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editJobTitleModal"));
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
        const $form = $("#addJobTitleForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[name="name"]').val("");
        $form.find('select[name="department_id"]').val("");
    };

    const resetEditForm = () => {
        const $form = $("#editJobTitleForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[name="name"]').val("");
        $form.find('select[name="department_id"]').val("");
        $form.find('input[name="id"]').val("0");
        $("#editJobTitleUsage").attr("hidden", true).text("");
    };

    const nameFormatter = (cell) => {
        return `<span class="ss-department-name"><i data-lucide="briefcase"></i><strong>${escapeHtml(cell.getValue())}</strong></span>`;
    };

    /* Unassigned is stated rather than left blank — an empty cell reads as a
       value that failed to load. */
    const departmentFormatter = (cell) => {
        const name = cell.getValue();

        if (!name) {
            return '<span class="ss-cell-muted">Not assigned</span>';
        }

        return `<span class="ss-department-name"><i data-lucide="building-2"></i>${escapeHtml(name)}</span>`;
    };

    /* How many people hold the title. Shown because it decides whether it can
       be archived, and because renaming one renames it for all of them. */
    const usageFormatter = (cell) => {
        const count = Number(cell.getValue() || 0);

        if (!count) {
            return '<span class="ss-status-pill is-inactive"><span></span>Not in use</span>';
        }

        return `<span class="ss-status-pill is-active"><span></span>${count} ${count === 1 ? "employee" : "employees"}</span>`;
    };

    const statusFormatter = (cell) => {
        const isActive = cell.getData().deleted_at == null;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Archived"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore job title"><i data-lucide="rotate-cw"></i></button>`;
        }

        /* The In Use column counts active staff only, but archiving and
           renaming reach every employment on the title, so these two work
           from the full holder count and say how many of them are inactive. */
        const holders = Number(data.holders || 0);
        const inactive = Math.max(holders - Number(data.in_use || 0), 0);
        const inactiveNote = inactive > 0 ? ` (${inactive} inactive)` : "";

        const archive = holders > 0
            ? `<button type="button" class="ss-row-action ss-row-action--delete is-disabled" disabled aria-label="In use, cannot archive" title="Held by ${escapeHtml(holders)} staff${inactiveNote} — move them to another title first"><i data-lucide="trash-2"></i></button>`
            : `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive job title"><i data-lucide="trash-2"></i></button>`;

        return [
            `<button data-id="${escapeHtml(data.id)}" data-use="${escapeHtml(holders)}" data-inactive="${escapeHtml(inactive)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit job title"><i data-lucide="pencil"></i></button>`,
            archive,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";
        const department_id = $("#department_id").val() || "";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#jobTitleTableId", {
            ajaxURL: route("job.title.list"),
            ajaxParams: { querystr, status, department_id },
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
                    title: "Department",
                    field: "department",
                    headerHozAlign: "left",
                    minWidth: 200,
                    widthGrow: 1,
                    formatter: departmentFormatter,
                },
                {
                    title: "In Use",
                    field: "in_use",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 160,
                    formatter: usageFormatter,
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

    $("#tabulatorFilterForm").on("keypress.jobtitles", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.jobtitles", buildTable);

    $("#tabulator-html-filter-reset").on("click.jobtitles", function () {
        $("#query").val("");
        $("#status").val("1");
        $("#department_id").val("");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.jobtitles", function () {
        tableContent?.download("csv", "job-titles.csv");
    });

    $("#tabulator-export-xlsx").on("click.jobtitles", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "job-titles.xlsx", {
            sheetName: "Job Titles",
        });
    });

    $("#tabulator-print").on("click.jobtitles", function () {
        tableContent?.print();
    });

    document.getElementById("addJobTitleModal")?.addEventListener("show.tw.modal", resetAddForm);
    document.getElementById("addJobTitleModal")?.addEventListener("hide.tw.modal", resetAddForm);
    document.getElementById("editJobTitleModal")?.addEventListener("hide.tw.modal", resetEditForm);

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addJobTitleForm").on("submit.jobtitles", function (event) {
        event.preventDefault();

        const form = document.getElementById("addJobTitleForm");
        const $form = $("#addJobTitleForm");

        clearErrors($form);
        setBusy("#save", true);

        axios({
            method: "post",
            url: route("job.title.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#save", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Success!", "Job title successfully created.");
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

    $("#jobTitleTableId").on("click.jobtitles", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const inUse = Number($(this).attr("data-use") || 0);
        const inactive = Number($(this).attr("data-inactive") || 0);
        const $form = $("#editJobTitleForm");

        resetEditForm();

        axios({
            method: "get",
            url: route("job.title.edit", editId),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find('select[name="department_id"]').val(dataset.department_id || "");
                $form.find('input[name="id"]').val(editId);

                if (inUse > 0) {
                    $("#editJobTitleUsage")
                        .removeAttr("hidden")
                        .text(
                            `Held by ${inUse} ${inUse === 1 ? "employee" : "employees"}${inactive > 0 ? ` (${inactive} inactive)` : ""} — renaming it changes their recorded job title too.`
                        );
                }

                editModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editJobTitleForm").on("submit.jobtitles", function (event) {
        event.preventDefault();

        const form = document.getElementById("editJobTitleForm");
        const $form = $("#editJobTitleForm");

        clearErrors($form);
        setBusy("#update", true);

        axios({
            method: "post",
            url: route("job.title.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#update", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Job title successfully updated.");
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

    $("#jobTitleTableId").on("click.jobtitles", ".delete_btn", function () {
        showConfirm(
            "Archive job title?",
            "This job title will move to archived records and will no longer be offered when appointing staff.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#jobTitleTableId").on("click.jobtitles", ".restore_btn", function () {
        showConfirm(
            "Restore job title?",
            "This job title will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.jobtitles", function () {
        const $agreeBTN = $(this);
        const recordID = $agreeBTN.attr("data-id");
        const action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("job.title.destory", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Job title successfully archived.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                confirmModal.hide();

                /* The server refuses to archive a title somebody still holds,
                   and says how many. Shown rather than swallowed. */
                if (error.response?.status == 422) {
                    showSuccess("Cannot archive", error.response.data.message);
                    return;
                }

                console.log(error);
            });
            return;
        }

        if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("job.title.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Job title successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
