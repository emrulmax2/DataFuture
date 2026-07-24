import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Litepicker from "litepicker";

("use strict");

(function () {
    const tableNode = document.querySelector("#hrHolidayYearsListTable");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHolidayYearModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHolidayYearModal"));
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

    const updateActiveToggle = ($toggle) => {
        const isChecked = $toggle.find('input[name="active"]').is(":checked");

        $toggle.find(".ss-status-toggle__copy strong").text(isChecked ? "Active" : "Inactive");
        $toggle.find(".ss-status-toggle__copy small").text(
            isChecked ? "Available for holiday planning" : "Not available for holiday planning"
        );
    };

    const setActiveField = ($form, value) => {
        const $toggle = $form.find(".ss-holiday-active-toggle");
        const isActive = value === true || value === 1 || value === "1";

        $toggle.find('input[name="active"]').prop("checked", isActive);
        updateActiveToggle($toggle);
    };

    const resetForm = ($form, active = true) => {
        clearErrors($form);
        $form[0]?.reset();
        $form.find('input[type="text"], input[type="number"]').val("");
        $form.find('input[name="id"]').val("0");
        setActiveField($form, active);
    };

    const clearPicker = (picker) => {
        try {
            picker?.clearSelection();
        } catch (error) {
            // Litepicker can throw if it is asked to clear before initial paint.
        }
    };

    const setPickerDate = (picker, selector, value) => {
        const input = document.querySelector(selector);

        if (input) {
            input.value = value || "";
        }

        if (!value) {
            clearPicker(picker);
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

    const dateOption = {
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

    const addEndDate = new Litepicker({
        element: document.getElementById("add_end_date"),
        ...dateOption,
    });

    const addStartDate = new Litepicker({
        element: document.getElementById("add_start_date"),
        ...dateOption,
        setup: (picker) => {
            picker.on("selected", () => {
                addEndDate.clearSelection();
                addEndDate.setOptions({ minDate: picker.getDate() });
            });
        },
    });

    const editEndDate = new Litepicker({
        element: document.getElementById("edit_end_date"),
        ...dateOption,
    });

    const editStartDate = new Litepicker({
        element: document.getElementById("edit_start_date"),
        ...dateOption,
        setup: (picker) => {
            picker.on("selected", () => {
                editEndDate.clearSelection();
                editEndDate.setOptions({ minDate: picker.getDate() });
            });
        },
    });

    const resetAddForm = () => {
        resetForm($("#addHolidayYearForm"), true);
        clearPicker(addStartDate);
        clearPicker(addEndDate);
    };

    const resetEditForm = () => {
        resetForm($("#editHolidayYearForm"), false);
        clearPicker(editStartDate);
        clearPicker(editEndDate);
    };

    const statusFormatter = (cell) => {
        const data = cell.getData();
        const isActive = data.active == 1;

        return `<button type="button" data-id="${escapeHtml(data.id)}" class="status_updater ss-status-pill ss-status-action ${isActive ? "is-active" : "is-inactive"}" aria-label="Change holiday year status"><span></span>${isActive ? "Active" : "Inactive"}</button>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore holiday year"><i data-lucide="rotate-cw"></i></button>`;
        }

        return [
            `<a href="${route("hr.bank.holiday", data.id)}" class="ss-row-action ss-row-action--calendar" aria-label="Bank holidays"><i data-lucide="landmark"></i></a>`,
            `<a href="${route("holiday.year.leave.option", data.id)}" class="ss-row-action ss-row-action--list" aria-label="Leave options"><i data-lucide="list-ordered"></i></a>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit holiday year"><i data-lucide="pencil"></i></button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete holiday year"><i data-lucide="trash-2"></i></button>`,
        ].join("");
    };

    const buildTable = () => {
        const querystr = $("#query-HY").val() || "";
        const status = $("#status-HY").val() || "";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#hrHolidayYearsListTable", {
            ajaxURL: route("holiday.year.list"),
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
                    width: 96,
                },
                {
                    title: "Year",
                    field: "year",
                    headerHozAlign: "left",
                    minWidth: 160,
                    formatter(cell) {
                        return `<strong class="ss-holiday-year-title">${escapeHtml(cell.getValue())}</strong>`;
                    },
                },
                {
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                    minWidth: 150,
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                    minWidth: 150,
                },
                {
                    title: "Notice Period",
                    field: "notice_period",
                    headerHozAlign: "left",
                    minWidth: 150,
                    formatter(cell) {
                        const value = cell.getValue();
                        return `<span class="ss-holiday-notice">${escapeHtml(value)} ${Number(value) === 1 ? "day" : "days"}</span>`;
                    },
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    minWidth: 130,
                    formatter: statusFormatter,
                    download: false,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 190,
                    minWidth: 190,
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

    $("#tabulatorFilterForm-HY").on("keypress.holidayYears", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go-HY").on("click.holidayYears", buildTable);

    $("#tabulator-html-filter-reset-HY").on("click.holidayYears", function () {
        $("#query-HY").val("");
        $("#status-HY").val("1");
        buildTable();
    });

    $("#tabulator-export-csv-HY").on("click.holidayYears", function () {
        tableContent?.download("csv", "holiday-years.csv");
    });

    $("#tabulator-export-xlsx-HY").on("click.holidayYears", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "holiday-years.xlsx", {
            sheetName: "Holiday Years",
        });
    });

    $("#tabulator-print-HY").on("click.holidayYears", function () {
        tableContent?.print();
    });

    document.getElementById("addHolidayYearModal")?.addEventListener("show.tw.modal", resetAddForm);
    document.getElementById("addHolidayYearModal")?.addEventListener("hide.tw.modal", resetAddForm);
    document.getElementById("editHolidayYearModal")?.addEventListener("hide.tw.modal", resetEditForm);

    $(".ss-holiday-active-toggle input[name='active']").on("change.holidayYears", function () {
        updateActiveToggle($(this).closest(".ss-holiday-active-toggle"));
    });

    $("#addHolidayYearForm").on("submit.holidayYears", function (event) {
        event.preventDefault();

        const form = document.getElementById("addHolidayYearForm");
        const $form = $("#addHolidayYearForm");

        clearErrors($form);
        setBusy("#saveHY", true);

        axios({
            method: "post",
            url: route("holiday.year.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#saveHY", false);

            if (response.status == 200) {
                addModal.hide();
                showSuccess("Success!", "Holiday year successfully created.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#saveHY", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#hrHolidayYearsListTable").on("click.holidayYears", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editHolidayYearForm");

        resetEditForm();

        axios({
            method: "post",
            url: route("holiday.year.edit"),
            data: { rowID: editId },
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const startDate = dataset.start_date || "";
                const endDate = dataset.end_date || "";

                setPickerDate(editStartDate, "#edit_start_date", startDate);
                setPickerDate(editEndDate, "#edit_end_date", endDate);

                if (dataset.start_date_modified) {
                    editEndDate.setOptions({
                        minDate: new Date(dataset.start_date_modified),
                    });
                }

                $form.find('input[name="notice_period"]').val(dataset.notice_period || "");
                $form.find('input[name="id"]').val(editId);
                setActiveField($form, dataset.active);
                editModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editHolidayYearForm").on("submit.holidayYears", function (event) {
        event.preventDefault();

        const form = document.getElementById("editHolidayYearForm");
        const $form = $("#editHolidayYearForm");

        clearErrors($form);
        setBusy("#updateHY", true);

        axios({
            method: "post",
            url: route("holiday.year.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#updateHY", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Holiday year successfully updated.");
                buildTable();
            }
        }).catch((error) => {
            setBusy("#updateHY", false);

            if (error.response?.status == 422) {
                showErrors($form, error.response.data.errors);
                return;
            }

            console.log(error);
        });
    });

    $("#hrHolidayYearsListTable").on("click.holidayYears", ".delete_btn", function () {
        showConfirm(
            "Archive holiday year?",
            "This holiday year will be moved to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#hrHolidayYearsListTable").on("click.holidayYears", ".restore_btn", function () {
        showConfirm(
            "Restore holiday year?",
            "This holiday year will be available in live records again.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#hrHolidayYearsListTable").on("click.holidayYears", ".status_updater", function () {
        showConfirm(
            "Change status?",
            "This will update whether the holiday year is available for holiday planning.",
            "CHANGESTAT",
            $(this).attr("data-id")
        );
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#confirmModal .agreeWith").on("click.holidayYears", function () {
        const $agreeBTN = $(this);
        const recordID = $agreeBTN.attr("data-id");
        const action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("holiday.year.destory", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Holiday year successfully archived.");
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
                url: route("holiday.year.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Holiday year successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
            return;
        }

        if (action == "CHANGESTAT") {
            axios({
                method: "post",
                url: route("holiday.year.update.status"),
                data: { recordID },
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Holiday year status successfully updated.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
