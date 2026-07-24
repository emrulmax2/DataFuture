import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const smsPhaseKeys = ["admission", "live", "hr"];

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

const isYesValue = (value) => {
    return value === true || value == 1 || String(value).toLowerCase() === "yes";
};

const formatToggleAction = (value, classes, attributes, label) => {
    const isEnabled = isYesValue(value);
    const icon = isEnabled ? "check" : "x";
    const state = isEnabled ? "active" : "inactive";

    return `<button type="button" class="ss-doc-icon ss-sms-toggle ${classes} ${isEnabled ? "is-active" : "is-inactive"}" ${attributes} aria-label="${label} ${state}"><i data-lucide="${icon}"></i></button>`;
};

const formatToggleIndicator = (value, label) => {
    const isEnabled = isYesValue(value);
    const icon = isEnabled ? "check" : "x";
    const state = isEnabled ? "active" : "inactive";

    return `<span class="ss-doc-icon ${isEnabled ? "is-active" : "is-inactive"}" aria-label="${label} ${state}"><i data-lucide="${icon}"></i></span>`;
};

const formatStatus = (value) => {
    const isEnabled = isYesValue(value);

    return `<span class="ss-status-pill ${isEnabled ? "is-active" : "is-inactive"}"><span></span>${isEnabled ? "Active" : "Inactive"}</span>`;
};

var smsTemplateTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status_filter").val() != "" ? $("#status_filter").val() : "1";
        let phase = $("#phase_filter").val() != "" ? $("#phase_filter").val() : "";

        if (window.smsTemplateTableInstance) {
            window.smsTemplateTableInstance.destroy();
        }

        let tableContent = new Tabulator("#smsTemplateListTable", {
            ajaxURL: route("sms.template.list"),
            ajaxParams: { querystr: querystr, status: status, phase: phase },
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
                    title: "Template",
                    field: "sms_title",
                    headerHozAlign: "left",
                    minWidth: 230,
                    widthGrow: 1.35,
                    formatter(cell) {
                        const data = cell.getData();
                        return `<span class="ss-sms-template-cell"><strong>${escapeHtml(data.sms_title)}</strong><small>${escapeHtml(data.description)}</small></span>`;
                    },
                },
                {
                    title: "Admission",
                    field: "admission",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 92,
                    widthGrow: 0.55,
                    formatter(cell) {
                        if (cell.getData().deleted_at != null) {
                            return formatToggleIndicator(cell.getValue(), "Admission phase");
                        }

                        return formatToggleAction(
                            cell.getValue(),
                            "updatePhase",
                            `data-phase="admission" data-id="${cell.getData().id}"`,
                            "Admission phase"
                        );
                    },
                },
                {
                    title: "Live",
                    field: "live",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 84,
                    widthGrow: 0.5,
                    formatter(cell) {
                        if (cell.getData().deleted_at != null) {
                            return formatToggleIndicator(cell.getValue(), "Live student phase");
                        }

                        return formatToggleAction(
                            cell.getValue(),
                            "updatePhase",
                            `data-phase="live" data-id="${cell.getData().id}"`,
                            "Live student phase"
                        );
                    },
                },
                {
                    title: "HR",
                    field: "hr",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 76,
                    widthGrow: 0.48,
                    formatter(cell) {
                        if (cell.getData().deleted_at != null) {
                            return formatToggleIndicator(cell.getValue(), "HR phase");
                        }

                        return formatToggleAction(
                            cell.getValue(),
                            "updatePhase",
                            `data-phase="hr" data-id="${cell.getData().id}"`,
                            "HR phase"
                        );
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 108,
                    widthGrow: 0.7,
                    formatter(cell) {
                        if (cell.getData().deleted_at != null) {
                            return formatStatus(cell.getValue());
                        }

                        return `<button type="button" class="status_updater ss-status-action" data-id="${cell.getData().id}">${formatStatus(cell.getValue())}</button>`;
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
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit SMS template"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete SMS template"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore SMS template"><i data-lucide="rotate-cw"></i></button>';
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

        window.smsTemplateTableInstance = tableContent;

        if (window.smsTemplateTableResizeHandler) {
            window.removeEventListener("resize", window.smsTemplateTableResizeHandler);
        }

        window.smsTemplateTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.smsTemplateTableResizeHandler);

        $("#tabulator-export-csv").off("click.smsTemplate").on("click.smsTemplate", function () {
            tableContent.download("csv", "sms-template-details.csv");
        });

        $("#tabulator-export-xlsx").off("click.smsTemplate").on("click.smsTemplate", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "sms-template-details.xlsx", {
                sheetName: "SMS Template Details",
            });
        });

        $("#tabulator-print").off("click.smsTemplate").on("click.smsTemplate", function () {
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
    if (!$("#smsTemplateListTable").length) {
        return;
    }

    smsTemplateTable.init();

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));

    const setBusy = ($button, isBusy) => {
        $button.prop("disabled", isBusy);
        $button.find(".ss-spinner").css("display", isBusy ? "inline-block" : "none");
    };

    const showSuccess = (title, message) => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(message);
        successModal.show();
    };

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").html("");
        $form.find(".border-danger").removeClass("border-danger");
    };

    const showErrors = ($form, errors) => {
        for (const [key, val] of Object.entries(errors)) {
            $form.find(`.${key}`).addClass("border-danger");
            $form.find(`.error-${key}`).html(Array.isArray(val) ? val[0] : val);
        }
    };

    const updatePhaseToggleText = ($toggle) => {
        const enabled = $toggle.find("input").is(":checked");
        const note = $toggle.find(".ss-status-toggle__copy small").data("default-note");
        $toggle.find(".ss-status-toggle__copy small").text(enabled ? "Enabled" : note);
    };

    const updatePhaseToggleTexts = ($form) => {
        $form.find(".ss-sms-phase-toggle").each(function () {
            const $toggle = $(this);
            const $note = $toggle.find(".ss-status-toggle__copy small");
            if (!$note.data("default-note")) {
                $note.data("default-note", $note.text());
            }
            updatePhaseToggleText($toggle);
        });
    };

    const updateActiveToggleText = ($toggle) => {
        const enabled = $toggle.find("input").is(":checked");
        $toggle.find(".ss-status-toggle__copy strong").text(enabled ? "Active" : "Inactive");
        $toggle.find(".ss-status-toggle__copy small").text(enabled ? "Available for sending" : "Not available for sending");
    };

    const updateActiveToggleTexts = ($form) => {
        $form.find(".ss-sms-active-toggle").each(function () {
            updateActiveToggleText($(this));
        });
    };

    const updateSmsCounter = ($textarea) => {
        const chars = $textarea.val().length;
        const messages = Math.max(1, Math.ceil(chars / 160));
        const remaining = chars > 0 ? messages * 160 - chars : 160;
        $textarea.closest(".modal").find(".sms_countr").text(`${remaining} / ${messages}`);
    };

    const resetSmsForm = ($form) => {
        clearErrors($form);
        $form[0].reset();
        $form.find('input[name="id"]').val("0");
        $form.find(".phaseCheckboxs").prop("checked", false);
        $form.find('input[name="status"]').prop("checked", true);
        $form.find(".sms_countr").text("160 / 1");
        updatePhaseToggleTexts($form);
        updateActiveToggleTexts($form);
    };

    const populateEditForm = (dataset, recordId) => {
        const $form = $("#editSmsForm");

        $form.find('input[name="sms_title"]').val(dataset.sms_title ? dataset.sms_title : "");
        $form.find('textarea[name="description"]').val(dataset.description ? dataset.description : "");
        $form.find('input[name="id"]').val(recordId);

        smsPhaseKeys.forEach((key) => {
            $form.find(`input[name="phase[${key}]"]`).prop("checked", isYesValue(dataset[key]));
        });

        $form.find('input[name="status"]').prop("checked", isYesValue(dataset.status));
        updatePhaseToggleTexts($form);
        updateActiveToggleTexts($form);
        updateSmsCounter($("#editSmsTextArea"));
    };

    const filterHTMLForm = () => {
        smsTemplateTable.init();
    };

    $("#tabulatorFilterForm")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLForm();
        }
    });

    $("#tabulator-html-filter-go").on("click", function () {
        filterHTMLForm();
    });

    $("#tabulator-html-filter-reset").on("click", function () {
        $("#query").val("");
        $("#status_filter").val("1");
        $("#phase_filter").val("");
        filterHTMLForm();
    });

    resetSmsForm($("#addSmsForm"));
    resetSmsForm($("#editSmsForm"));

    $(".ss-sms-phase-toggle input").on("change", function () {
        updatePhaseToggleText($(this).closest(".ss-sms-phase-toggle"));
    });

    $(".ss-sms-active-toggle input").on("change", function () {
        updateActiveToggleText($(this).closest(".ss-sms-active-toggle"));
    });

    $("#addSmsTextArea, #editSmsTextArea").on("input keyup", function () {
        updateSmsCounter($(this));
    });

    document.getElementById("addModal").addEventListener("show.tw.modal", function () {
        resetSmsForm($("#addSmsForm"));
        setBusy($("#saveSmsSet"), false);
    });

    document.getElementById("addModal").addEventListener("hide.tw.modal", function () {
        resetSmsForm($("#addSmsForm"));
        setBusy($("#saveSmsSet"), false);
    });

    document.getElementById("editModal").addEventListener("hide.tw.modal", function () {
        resetSmsForm($("#editSmsForm"));
        setBusy($("#editSmsSet"), false);
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addSmsForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addSmsForm");

        clearErrors($form);
        setBusy($("#saveSmsSet"), true);

        axios({
            method: "post",
            url: route("sms.template.store"),
            data: new FormData(document.getElementById("addSmsForm")),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#saveSmsSet"), false);

            if (response.status == 200) {
                resetSmsForm($form);
                addModal.hide();
                showSuccess("Success!", "SMS template successfully inserted.");
            }

            smsTemplateTable.init();
        }).catch((error) => {
            setBusy($("#saveSmsSet"), false);

            if (error.response && error.response.status == 422) {
                showErrors($form, error.response.data.errors || { phase: error.response.data.message });
            } else if (error.response) {
                console.log("error");
            }
        });
    });

    $("#smsTemplateListTable").on("click", ".edit_btn", function () {
        let recordId = $(this).attr("data-id");
        resetSmsForm($("#editSmsForm"));

        axios({
            method: "get",
            url: route("sms.template.edit", recordId),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                populateEditForm(response.data, recordId);
                editModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editSmsForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editSmsForm");

        clearErrors($form);
        setBusy($("#editSmsSet"), true);

        axios({
            method: "post",
            url: route("sms.template.update"),
            data: new FormData(document.getElementById("editSmsForm")),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#editSmsSet"), false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "SMS template successfully updated.");
            }

            smsTemplateTable.init();
        }).catch((error) => {
            setBusy($("#editSmsSet"), false);

            if (error.response && error.response.status == 422) {
                showErrors($form, error.response.data.errors || { phase: error.response.data.message });
            } else if (error.response) {
                console.log("error");
            }
        });
    });

    const openConfirm = (title, description, action, id, phase = "") => {
        $("#confirmModal .confModTitle").html(title);
        $("#confirmModal .confModDesc").html(description);
        $("#confirmModal .agreeWith").attr("data-id", id);
        $("#confirmModal .agreeWith").attr("data-action", action);
        $("#confirmModal .agreeWith").attr("data-phase", phase);
        confirmModal.show();
    };

    $("#smsTemplateListTable").on("click", ".delete_btn", function () {
        openConfirm("Are you sure?", "Do you really want to delete this SMS template?", "DELETE", $(this).attr("data-id"));
    });

    $("#smsTemplateListTable").on("click", ".restore_btn", function () {
        openConfirm("Are you sure?", "Do you really want to restore this SMS template?", "RESTORE", $(this).attr("data-id"));
    });

    $("#smsTemplateListTable").on("click", ".status_updater", function () {
        openConfirm("Are you sure?", "Do you really want to change this SMS template status?", "CHANGESTAT", $(this).attr("data-id"));
    });

    $("#smsTemplateListTable").on("click", ".updatePhase", function () {
        openConfirm("Are you sure?", "Do you really want to change this SMS template phase?", "CHANGEPHS", $(this).attr("data-id"), $(this).attr("data-phase"));
    });

    $("#confirmModal .agreeWith").on("click", function () {
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr("data-id");
        let action = $agreeBTN.attr("data-action");
        let phase = $agreeBTN.attr("data-phase");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("sms.template.destory", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                $("#confirmModal button").removeAttr("disabled");
                if (response.status == 200) {
                    confirmModal.hide();
                    showSuccess("Done!", "SMS template successfully deleted.");
                }
                smsTemplateTable.init();
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("sms.template.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                $("#confirmModal button").removeAttr("disabled");
                if (response.status == 200) {
                    confirmModal.hide();
                    showSuccess("Success!", "SMS template successfully restored.");
                }
                smsTemplateTable.init();
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        } else if (action == "CHANGESTAT") {
            axios({
                method: "post",
                url: route("sms.template.update.status"),
                data: { row_id: recordID },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                $("#confirmModal button").removeAttr("disabled");
                if (response.status == 200) {
                    confirmModal.hide();
                    showSuccess("Success!", "SMS template status successfully updated.");
                }
                smsTemplateTable.init();
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        } else if (action == "CHANGEPHS") {
            axios({
                method: "post",
                url: route("sms.template.update.phase.status"),
                data: { row_id: recordID, phase: phase },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                $("#confirmModal button").removeAttr("disabled");
                if (response.status == 200) {
                    confirmModal.hide();
                    showSuccess("Success!", "SMS template phase successfully updated.");
                }
                smsTemplateTable.init();
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        } else {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
        }
    });
})();
