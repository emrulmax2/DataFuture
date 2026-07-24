import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const escapeHtml = (value) => {
    if (value === null || value === undefined || value === "") {
        return "&mdash;";
    }

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

const isTrue = (value) => value === true || Number(value) === 1 || String(value).toLowerCase() === "yes";

const hasLogo = (url) => url && !String(url).includes("placeholders/200x200");

const formatYesNo = (value) => {
    const enabled = isTrue(value);
    const label = enabled ? "Yes" : "No";

    return `<span class="ss-elearning-icon-state ${enabled ? "is-active" : "is-inactive"}" title="${label}" aria-label="${label}">
        <i data-lucide="${enabled ? "check" : "x"}"></i>
    </span>`;
};

const formatReminder = (value) => {
    if (value === null || value === undefined || value === "") {
        return '<span class="ss-cell-muted">&mdash;</span>';
    }

    const days = Number(value);

    if (!days) {
        return '<span class="ss-elearning-reminder"><i data-lucide="bell-off"></i>No reminder</span>';
    }

    return `<span class="ss-elearning-reminder"><i data-lucide="bell"></i>${days} ${days === 1 ? "Day" : "Days"}</span>`;
};

const formatStatusButton = (data) => {
    const active = isTrue(data.active);
    const label = active ? "Active" : "Inactive";

    if (data.deleted_at != null) {
        return `<span class="ss-elearning-icon-state ${active ? "is-active" : "is-inactive"}" title="${label}" aria-label="${label}">
            <i data-lucide="${active ? "check" : "x"}"></i>
        </span>`;
    }

    return `<button type="button" data-id="${data.id}" class="status_updater ss-elearning-icon-state ss-elearning-status-button ${active ? "is-active" : "is-inactive"}" title="${label}" aria-label="Change activity status">
        <i data-lucide="${active ? "check" : "x"}"></i>
    </button>`;
};

const formatActivityCell = (cell) => {
    const data = cell.getData();
    const name = escapeHtml(data.name);
    const shortCode = data.short_code ? `<small><i data-lucide="hash"></i>${escapeHtml(data.short_code)}</small>` : "";
    const logo = hasLogo(data.logo_url)
        ? `<span class="ss-elearning-activity-logo"><img alt="${name}" src="${escapeHtml(data.logo_url)}"></span>`
        : `<span class="ss-elearning-activity-logo ss-elearning-activity-logo--empty"><i data-lucide="image"></i></span>`;

    return `<span class="ss-elearning-activity-cell">
        ${logo}
        <span class="ss-elearning-activity-copy">
            <strong>${name}</strong>
            ${shortCode}
        </span>
    </span>`;
};

var ELearningActivityList = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : 1;

        if (window.eLearningActivityTableInstance) {
            window.eLearningActivityTableInstance.destroy();
        }

        let tableContent = new Tabulator("#ELearningActivityList", {
            ajaxURL: route("elearning.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 68,
                    minWidth: 62,
                },
                {
                    title: "Activity",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 240,
                    widthGrow: 1.35,
                    formatter: formatActivityCell,
                },
                {
                    title: "Category",
                    field: "category",
                    headerHozAlign: "left",
                    minWidth: 150,
                    widthGrow: 0.75,
                    formatter(cell) {
                        return `<span class="ss-phase-pill ss-elearning-category">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Weekly",
                    field: "has_week",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 84,
                    minWidth: 76,
                    formatter(cell) {
                        return formatYesNo(cell.getValue());
                    },
                },
                {
                    title: "Mandatory",
                    field: "is_mandatory",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 104,
                    minWidth: 92,
                    formatter(cell) {
                        return formatYesNo(cell.getValue());
                    },
                },
                {
                    title: "Reminder",
                    field: "days_reminder",
                    headerHozAlign: "left",
                    width: 128,
                    minWidth: 118,
                    formatter(cell) {
                        return formatReminder(cell.getValue());
                    },
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 86,
                    minWidth: 78,
                    download: false,
                    formatter(cell) {
                        return formatStatusButton(cell.getData());
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 104,
                    minWidth: 104,
                    download: false,
                    formatter(cell) {
                        let btns = "";
                        const data = cell.getData();

                        if (data.deleted_at == null) {
                            btns += `<button data-id="${data.id}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit e-learning activity"><i data-lucide="pencil"></i></button>`;
                            btns += `<button data-id="${data.id}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete e-learning activity"><i data-lucide="trash-2"></i></button>`;
                        } else {
                            btns += `<button data-id="${data.id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore e-learning activity"><i data-lucide="rotate-cw"></i></button>`;
                        }

                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.7,
                    nameAttr: "data-lucide",
                });
            },
        });

        window.eLearningActivityTableInstance = tableContent;

        if (window.eLearningActivityTableResizeHandler) {
            window.removeEventListener("resize", window.eLearningActivityTableResizeHandler);
        }

        window.eLearningActivityTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.eLearningActivityTableResizeHandler);

        $("#tabulator-export-csv").off("click.elearning").on("click.elearning", function () {
            tableContent.download("csv", "e-learning-activities.csv");
        });

        $("#tabulator-export-xlsx").off("click.elearning").on("click.elearning", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "e-learning-activities.xlsx", {
                sheetName: "E-Learning Activities",
            });
        });

        $("#tabulator-print").off("click.elearning").on("click.elearning", function () {
            tableContent.print();
        });
    };

    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function () {
    if (!$("#ELearningActivityList").length) {
        return;
    }

    ELearningActivityList.init();

    const addELearningActivityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addELearningActivityModal"));
    const editELearningActivityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editELearningActivityModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const confModalDelTitle = "Are you sure?";

    const showSuccess = (title, message) => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(message);
        successModal.show();
    };

    const setButtonLoading = ($button, isLoading) => {
        $button.prop("disabled", isLoading);
        $button.find(".ss-spinner").css("display", isLoading ? "inline-block" : "none");
    };

    const updateUploadName = ($form, name = "No file selected") => {
        $form.find("[data-ss-upload-name]").text(name);
    };

    const updateActiveToggleCopy = ($form) => {
        $form.find(".ss-elearning-active-toggle").each(function () {
            const checked = $(this).find('input[type="checkbox"]').prop("checked");
            $(this).find(".ss-status-toggle__copy strong").text(checked ? "Active" : "Inactive");
            $(this).find(".ss-status-toggle__copy small").text(checked ? "Available for planning" : "Hidden from planning");
        });
    };

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").html("");
        $form.find(".border-danger").removeClass("border-danger");
        $form.find(".ss-elearning-logo-picker").removeClass("is-danger");
    };

    const resetFormState = ($form, options = {}) => {
        clearErrors($form);
        $form.find('input[type="text"], input[type="number"], input[type="file"]').val("");
        $form.find("select").val("");
        $form.find('input[name="id"]').val("0");
        $form.find('input[name="has_week"], input[name="is_mandatory"]').prop("checked", false);
        $form.find('input[name="active"]').prop("checked", options.active !== false);
        const placeholder = $form.find("img[data-placeholder]").attr("data-placeholder");
        if (placeholder) {
            $form.find("img[data-placeholder]").attr("src", placeholder);
        }
        updateUploadName($form);
        updateActiveToggleCopy($form);
        setButtonLoading($form.find('button[type="submit"]'), false);
    };

    const showPreview = (input, $form) => {
        const file = input.files?.[0];
        const target = $form.find("img[data-placeholder]")[0];

        if (!file || !target) {
            updateUploadName($form);
            return;
        }

        const reader = new FileReader();
        reader.onload = function () {
            target.src = reader.result;
        };
        reader.readAsDataURL(file);
        updateUploadName($form, file.name);
    };

    const renderValidationErrors = ($form, error) => {
        if (!error.response || error.response.status !== 422 || !error.response.data.errors) {
            console.log("error");
            return;
        }

        for (const [key, val] of Object.entries(error.response.data.errors)) {
            const message = Array.isArray(val) ? val[0] : val;
            const $field = $form.find(`.${key}`);

            $field.addClass("border-danger");
            $form.find(`.error-${key}`).html(message);

            if (key === "logo") {
                $form.find(".ss-elearning-logo-picker").addClass("is-danger");
            }
        }
    };

    const showConfirm = (id, action, title, message) => {
        $("#confirmModal .confModTitle").html(title);
        $("#confirmModal .confModDesc").html(message);
        $("#confirmModal .agreeWith").attr("data-id", id);
        $("#confirmModal .agreeWith").attr("data-action", action);
        confirmModal.show();
    };

    function filterTitleHTMLForm() {
        ELearningActivityList.init();
    }

    $("#tabulatorFilterForm")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterTitleHTMLForm();
        }
    });

    $("#tabulator-html-filter-go").on("click", function () {
        filterTitleHTMLForm();
    });

    $("#tabulator-html-filter-reset").on("click", function () {
        $("#query").val("");
        $("#status").val("1");
        filterTitleHTMLForm();
    });

    document.getElementById("addELearningActivityModal").addEventListener("show.tw.modal", function () {
        resetFormState($("#addELearningActivityForm"), { active: true });
    });

    document.getElementById("addELearningActivityModal").addEventListener("hide.tw.modal", function () {
        resetFormState($("#addELearningActivityForm"), { active: true });
    });

    document.getElementById("editELearningActivityModal").addEventListener("hide.tw.modal", function () {
        resetFormState($("#editELearningActivityForm"), { active: false });
        $("#updateSettings").prop("disabled", true);
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addELearningActivityForm, #editELearningActivityForm").on("input change", ".name, .short_code, .category, .days_reminder", function () {
        const $form = $(this).closest("form");
        const names = ["name", "short_code", "category", "days_reminder"];
        names.forEach((name) => {
            if ($(this).hasClass(name)) {
                $(this).removeClass("border-danger");
                $form.find(`.error-${name}`).html("");
            }
        });
    });

    $("#addELearningActivityForm, #editELearningActivityForm").on("change", ".logo", function () {
        const $form = $(this).closest("form");
        showPreview(this, $form);
        $(this).removeClass("border-danger");
        $form.find(".ss-elearning-logo-picker").removeClass("is-danger");
        $form.find(".error-logo").html("");
    });

    $("#addELearningActivityForm, #editELearningActivityForm").on("change", ".ss-elearning-active-toggle input", function () {
        updateActiveToggleCopy($(this).closest("form"));
    });

    $("#addELearningActivityForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addELearningActivityForm");
        const form = document.getElementById("addELearningActivityForm");

        clearErrors($form);
        setButtonLoading($("#saveSettings"), true);

        axios({
            method: "post",
            url: route("elearning.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setButtonLoading($("#saveSettings"), false);

            if (response.status == 200) {
                addELearningActivityModal.hide();
                showSuccess("Success!", "E-Learning activity settings successfully added.");
            }

            ELearningActivityList.init();
        }).catch((error) => {
            setButtonLoading($("#saveSettings"), false);
            renderValidationErrors($form, error);
        });
    });

    $("#ELearningActivityList").on("click", ".edit_btn", function () {
        const editId = $(this).attr("data-id");
        const $form = $("#editELearningActivityForm");

        resetFormState($form, { active: false });
        $("#updateSettings").prop("disabled", true);

        axios({
            method: "post",
            url: route("elearning.edit"),
            data: { editid: editId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;

                $form.find('[name="name"]').val(dataset.name ? dataset.name : "");
                $form.find('[name="short_code"]').val(dataset.short_code ? dataset.short_code : "");
                $form.find('[name="category"]').val(dataset.category ? dataset.category : "");
                $form.find('[name="days_reminder"]').val(dataset.days_reminder ? dataset.days_reminder : "");
                $form.find('[name="id"]').val(editId);
                $form.find('input[name="has_week"]').prop("checked", Number(dataset.has_week) === 1);
                $form.find('input[name="is_mandatory"]').prop("checked", Number(dataset.is_mandatory) === 1);
                $form.find('input[name="active"]').prop("checked", Number(dataset.active) === 1);
                $form.find("#userImageEdit").attr("src", dataset.logoUrl).attr("alt", dataset.category || "Activity logo");
                updateActiveToggleCopy($form);
                $("#updateSettings").prop("disabled", false);
                editELearningActivityModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editELearningActivityForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editELearningActivityForm");
        const form = document.getElementById("editELearningActivityForm");

        clearErrors($form);
        setButtonLoading($("#updateSettings"), true);

        axios({
            method: "post",
            url: route("elearning.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setButtonLoading($("#updateSettings"), false);

            if (response.status == 200) {
                editELearningActivityModal.hide();
                showSuccess("Success!", "E-Learning activity settings successfully updated.");
            }

            ELearningActivityList.init();
        }).catch((error) => {
            setButtonLoading($("#updateSettings"), false);
            renderValidationErrors($form, error);
        });
    });

    $("#confirmModal .agreeWith").on("click", function () {
        const $agreeBTN = $(this);
        const recordID = $agreeBTN.attr("data-id");
        const action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        const done = (title, message) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showSuccess(title, message);
            ELearningActivityList.init();
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("elearning.destory", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "E-Learning activity successfully deleted.");
                }
            }).catch(failed);
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("elearning.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "E-Learning activity successfully restored.");
                }
            }).catch(failed);
        } else if (action == "CHANGESTAT") {
            axios({
                method: "post",
                url: route("elearning.update.status", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "E-Learning activity status successfully updated.");
                }
            }).catch(failed);
        } else {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
        }
    });

    $("#ELearningActivityList").on("click", ".status_updater", function () {
        const rowID = $(this).attr("data-id");

        showConfirm(
            rowID,
            "CHANGESTAT",
            confModalDelTitle,
            "Do you really want to change this activity status?"
        );
    });

    $("#ELearningActivityList").on("click", ".delete_btn", function () {
        const rowID = $(this).attr("data-id");

        showConfirm(
            rowID,
            "DELETE",
            confModalDelTitle,
            "Do you really want to delete this e-learning activity?"
        );
    });

    $("#ELearningActivityList").on("click", ".restore_btn", function () {
        const rowID = $(this).attr("data-id");

        showConfirm(
            rowID,
            "RESTORE",
            confModalDelTitle,
            "Do you really want to restore this e-learning activity?"
        );
    });

    resetFormState($("#addELearningActivityForm"), { active: true });
    resetFormState($("#editELearningActivityForm"), { active: false });
    $("#updateSettings").prop("disabled", true);
})();
