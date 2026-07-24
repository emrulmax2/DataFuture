import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";
import Litepicker from "litepicker";

("use strict");

(function () {
    const tableNode = document.querySelector("#hrBankHolidayList");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBankHolidayModal"));
    const importModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#bankHolidayImportModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let tableContent = null;
    let importDropzone = null;

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

    const formatDisplayDate = (value) => {
        if (!value) {
            return "&mdash;";
        }

        const parts = String(value).split(" ")[0].split("-");

        if (parts.length === 3 && parts[0].length === 4) {
            return `${escapeHtml(parts[2])}-${escapeHtml(parts[1])}-${escapeHtml(parts[0])}`;
        }

        return escapeHtml(value);
    };

    const formatDateForInput = (value) => {
        if (!value) {
            return "";
        }

        const parts = String(value).split(" ")[0].split("-");

        if (parts.length === 3 && parts[0].length === 4) {
            return `${parts[2]}-${parts[1]}-${parts[0]}`;
        }

        return value;
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

    const resetEditForm = () => {
        const $form = $("#editBankHolidayForm");

        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[type="text"], input[type="number"], textarea').val("");
        $form.find('input[name="id"]').val("0");

        try {
            startDatePicker?.clearSelection();
            endDatePicker?.clearSelection();
        } catch (error) {
            // Litepicker can throw if cleared before its first paint.
        }
    };

    const dateOptions = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const endDatePicker = new Litepicker({
        element: document.getElementById("end_date"),
        ...dateOptions,
    });

    const startDatePicker = new Litepicker({
        element: document.getElementById("start_date"),
        ...dateOptions,
        setup: (picker) => {
            picker.on("selected", () => {
                endDatePicker.clearSelection();
                endDatePicker.setOptions({
                    minDate: picker.getDate(),
                });
            });
        },
    });

    const setPickerDate = (picker, selector, value) => {
        const input = document.querySelector(selector);

        if (input) {
            input.value = value || "";
        }

        if (!value) {
            try {
                picker?.clearSelection();
            } catch (error) {
                // The input value is already cleared.
            }
            return;
        }

        try {
            picker?.setDate(value);
        } catch (error) {
            if (input) {
                input.value = value;
            }
        }
    };

    const nameFormatter = (cell) => {
        const data = cell.getData();
        const description = data.description ? `<small>${escapeHtml(data.description)}</small>` : "";

        return `<span class="ss-bank-holiday-name"><strong>${escapeHtml(data.name)}</strong>${description}</span>`;
    };

    const durationFormatter = (cell) => {
        const value = cell.getValue();

        if (!value) {
            return "&mdash;";
        }

        return `<span class="ss-holiday-notice">${escapeHtml(value)} ${Number(value) === 1 ? "day" : "days"}</span>`;
    };

    const statusFormatter = (cell) => {
        const data = cell.getData();
        const isActive = data.deleted_at == null;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Archived"}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore bank holiday"><i data-lucide="rotate-cw"></i></button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit bank holiday"><i data-lucide="pencil"></i></button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive bank holiday"><i data-lucide="trash-2"></i></button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query-BHY").val() || "";
        const status = $("#status-BHY").val() || "";
        const holidayYear = $("#hrBankHolidayList").attr("data-year") || "0";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#hrBankHolidayList", {
            ajaxURL: route("hr.bank.holiday.list"),
            ajaxParams: { holidayyear: holidayYear, querystr, status },
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
                    width: 72,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 210,
                    widthGrow: 2,
                    formatter: nameFormatter,
                },
                {
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                    minWidth: 120,
                    formatter: (cell) => formatDisplayDate(cell.getValue()),
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                    minWidth: 120,
                    formatter: (cell) => formatDisplayDate(cell.getValue()),
                },
                {
                    title: "Duration",
                    field: "duration",
                    headerHozAlign: "left",
                    minWidth: 100,
                    formatter: durationFormatter,
                },
                {
                    title: "Status",
                    field: "deleted_at",
                    headerHozAlign: "left",
                    minWidth: 118,
                    formatter: statusFormatter,
                    download: false,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 100,
                    minWidth: 100,
                    download: false,
                    formatter: actionFormatter,
                },
            ],
            renderComplete() {
                refreshIcons();
            },
        });
    };

    const initImportDropzone = () => {
        const formEl = document.getElementById("bankHolidayImportForm");

        if (!formEl) {
            return;
        }

        Dropzone.autoDiscover = false;

        if (formEl.dropzone) {
            formEl.dropzone.destroy();
        }

        importDropzone = new Dropzone(formEl, {
            url: formEl.getAttribute("action"),
            paramName: "file",
            autoProcessQueue: false,
            uploadMultiple: false,
            maxFiles: 1,
            acceptedFiles: ".csv,.xlsx,.xls",
            headers: { "X-CSRF-TOKEN": csrfToken },
        });

        importDropzone.on("success", function () {
            importModal.hide();
            showSuccess("Success!", "Bank holidays successfully imported.");
            buildTable();
            importDropzone.removeAllFiles(true);
        });

        importDropzone.on("error", function (file, response) {
            const message = typeof response === "string" ? response : "Please check the import file and try again.";

            $(file.previewElement).find("[data-dz-errormessage]").text(message);
        });

        importDropzone.on("maxfilesexceeded", function (file) {
            importDropzone.removeAllFiles(true);
            importDropzone.addFile(file);
        });
    };

    buildTable();
    initImportDropzone();
    refreshIcons();

    $("#tabulatorFilterForm-BHY").on("keypress.bankHoliday", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go-BHY").on("click.bankHoliday", buildTable);

    $("#tabulator-html-filter-reset-BHY").on("click.bankHoliday", function () {
        $("#query-BHY").val("");
        $("#status-BHY").val("1");
        buildTable();
    });

    $("#tabulator-export-csv-BHY").on("click.bankHoliday", function () {
        tableContent?.download("csv", "bank-holidays.csv");
    });

    $("#tabulator-export-xlsx-BHY").on("click.bankHoliday", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "bank-holidays.xlsx", {
            sheetName: "Bank Holidays",
        });
    });

    $("#tabulator-print-BHY").on("click.bankHoliday", function () {
        tableContent?.print();
    });

    document.getElementById("editBankHolidayModal")?.addEventListener("hide.tw.modal", resetEditForm);

    document.getElementById("bankHolidayImportModal")?.addEventListener("hide.tw.modal", function () {
        importDropzone?.removeAllFiles(true);
    });

    $("#bankHolidayImportModal .closeImportModal").on("click.bankHoliday", function () {
        importModal.hide();
    });

    $("#saveImportHoliday").on("click.bankHoliday", function () {
        if (!importDropzone || importDropzone.getQueuedFiles().length === 0) {
            showSuccess("Choose a file", "Please select a bank holiday import file before uploading.");
            return;
        }

        importDropzone.processQueue();
    });

    $("#hrBankHolidayList").on("click.bankHoliday", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editBankHolidayForm");

        resetEditForm();

        axios({
            method: "post",
            url: route("hr.bank.holiday.edit"),
            data: { rowID: editId },
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const startDate = formatDateForInput(dataset.start_date || "");
                const endDate = formatDateForInput(dataset.end_date || "");

                $form.find('input[name="name"]').val(dataset.name || "");
                $form.find('input[name="duration"]').val(dataset.duration || 1);
                $form.find('textarea[name="description"]').val(dataset.description || "");
                $form.find('input[name="id"]').val(editId);

                setPickerDate(startDatePicker, "#start_date", startDate);
                setPickerDate(endDatePicker, "#end_date", endDate);

                if (dataset.start_date_modified) {
                    endDatePicker.setOptions({
                        minDate: new Date(dataset.start_date_modified),
                    });
                }

                editModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editBankHolidayForm").on("submit.bankHoliday", function (event) {
        event.preventDefault();

        const form = document.getElementById("editBankHolidayForm");
        const $form = $("#editBankHolidayForm");

        clearErrors($form);
        setBusy("#updateBH", true);

        axios({
            method: "post",
            url: route("hr.bank.holiday.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#updateBH", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Bank holiday successfully updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#updateBH", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#hrBankHolidayList").on("click.bankHoliday", ".delete_btn", function () {
        showConfirm(
            "Archive bank holiday?",
            "This bank holiday will move to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#hrBankHolidayList").on("click.bankHoliday", ".restore_btn", function () {
        showConfirm(
            "Restore bank holiday?",
            "This bank holiday will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#confirmModal .agreeWith").on("click.bankHoliday", function () {
        const $agreeBTN = $(this);
        const recordID = $agreeBTN.attr("data-id");
        const action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("hr.bank.holiday.destory", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Bank holiday successfully archived.");
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
                url: route("hr.bank.holiday.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Bank holiday successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
