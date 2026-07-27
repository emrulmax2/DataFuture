import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const emailPhaseKeys = ["admission", "live", "hr"];

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

const isOnValue = (value) => {
    return value === true || value == 1;
};

const formatSwitch = (cell, options) => {
    const isOn = isOnValue(cell.getValue());
    const rowId = cell.getData().id;
    const phaseAttr = options.phase ? ` data-phase="${options.phase}"` : "";

    return (
        '<button type="button" role="switch" aria-checked="' + (isOn ? "true" : "false") + '"' +
        ' class="ss-table-switch ' + options.className + (isOn ? " is-active" : " is-inactive") + '"' +
        ' data-id="' + rowId + '"' + phaseAttr +
        ' aria-label="' + options.label + '">' +
        '<i data-lucide="' + (isOn ? "check" : "x") + '"></i>' +
        "</button>"
    );
};

var emailTemplateListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query-EMAIL").val() != "" ? $("#query-EMAIL").val() : "";
        let status = $("#status-EMAIL").val() != "" ? $("#status-EMAIL").val() : "";
        let phase = $("#phase-EMAIL").val() != "" ? $("#phase-EMAIL").val() : "";

        if (window.emailTemplateTableInstance) {
            window.emailTemplateTableInstance.destroy();
        }

        let tableContent = new Tabulator("#emailTemplateListTable", {
            ajaxURL: route("email.template.list"),
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
                    title: "Template Title",
                    field: "email_title",
                    headerHozAlign: "left",
                    minWidth: 200,
                    widthGrow: 2,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Admission",
                    field: "admission",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 96,
                    widthGrow: 0.55,
                    formatter(cell) {
                        return formatSwitch(cell, {
                            className: "updatePhase",
                            phase: "admission",
                            label: "Toggle admission phase",
                        });
                    },
                },
                {
                    title: "Live",
                    field: "live",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 88,
                    widthGrow: 0.5,
                    formatter(cell) {
                        return formatSwitch(cell, {
                            className: "updatePhase",
                            phase: "live",
                            label: "Toggle live student phase",
                        });
                    },
                },
                {
                    title: "HR",
                    field: "hr",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 84,
                    widthGrow: 0.48,
                    formatter(cell) {
                        return formatSwitch(cell, {
                            className: "updatePhase",
                            phase: "hr",
                            label: "Toggle human resource phase",
                        });
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 92,
                    widthGrow: 0.5,
                    formatter(cell) {
                        return formatSwitch(cell, {
                            className: "status_updater",
                            label: "Toggle template status",
                        });
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
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit email template"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete email template"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore email template"><i data-lucide="rotate-cw"></i></button>';
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

        window.emailTemplateTableInstance = tableContent;

        if (window.emailTemplateTableResizeHandler) {
            window.removeEventListener("resize", window.emailTemplateTableResizeHandler);
        }

        window.emailTemplateTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.emailTemplateTableResizeHandler);

        $("#tabulator-export-csv-EMAIL").off("click.emailtemplate").on("click.emailtemplate", function () {
            tableContent.download("csv", "email-templates.csv");
        });

        $("#tabulator-export-xlsx-EMAIL").off("click.emailtemplate").on("click.emailtemplate", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "email-templates.xlsx", {
                sheetName: "Email Template Details",
            });
        });

        $("#tabulator-print-EMAIL").off("click.emailtemplate").on("click.emailtemplate", function () {
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
    if (!$("#emailTemplateListTable").length) {
        return;
    }

    emailTemplateListTable.init();

    function filterHTMLForm() {
        emailTemplateListTable.init();
    }

    $("#tabulatorFilterForm-EMAIL")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLForm();
        }
    });

    $("#tabulator-html-filter-go-EMAIL").on("click", function () {
        filterHTMLForm();
    });

    $("#tabulator-html-filter-reset-EMAIL").on("click", function () {
        $("#query-EMAIL").val("");
        $("#status-EMAIL").val("1");
        $("#phase-EMAIL").val("");
        filterHTMLForm();
    });

    const addEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmailModal"));
    const editEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmailModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const confModalTitle = "Are you sure?";

    let addEditor;
    let editEditor;

    if ($("#addEditor").length > 0) {
        const el = document.getElementById("addEditor");
        ClassicEditor.create(el).then((editor) => {
            addEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    if ($("#editEditor").length > 0) {
        const el = document.getElementById("editEditor");
        ClassicEditor.create(el).then((editor) => {
            editEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

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
        $form.find(".ss-editor").removeClass("is-danger");
    };

    const showErrors = ($form, errors) => {
        for (const [key, val] of Object.entries(errors)) {
            const message = Array.isArray(val) ? val[0] : val;
            const field = key.split(".")[0];

            $form.find(`.${field}`).addClass("border-danger");
            $form.find(`.error-${field}`).html(message);

            if (field === "description") {
                $form.find(".ss-editor").addClass("is-danger");
            }
        }
    };

    const updatePhaseToggleText = ($toggle) => {
        const enabled = $toggle.find("input").is(":checked");
        $toggle.find(".ss-status-toggle__copy small").text(enabled ? "Enabled" : "Not enabled");
    };

    const updateStatusToggleText = ($toggle) => {
        const enabled = $toggle.find("input").is(":checked");
        $toggle.find(".ss-status-toggle__copy small").text(enabled ? "Template is available" : "Template is hidden");
    };

    const updateToggleTexts = ($form) => {
        $form.find(".ss-doc-toggle").each(function () {
            updatePhaseToggleText($(this));
        });
        $form.find(".ss-status-toggle--inline").each(function () {
            updateStatusToggleText($(this));
        });
    };

    const resetAddForm = () => {
        const $form = $("#addEmailForm");
        clearErrors($form);
        $form.find('input[name="email_title"]').val("");
        $form.find(".phaseCheckboxs").prop("checked", false);
        $form.find("#status").prop("checked", true);
        updateToggleTexts($form);
        setBusy($("#saveEmailSet"), false);

        if (addEditor) {
            addEditor.setData("");
        }
    };

    const resetEditForm = () => {
        const $form = $("#editEmailForm");
        clearErrors($form);
        $form.find('input[name="email_title"]').val("");
        $form.find('input[name="id"]').val("0");
        $form.find(".phaseCheckboxs").prop("checked", false);
        $form.find("#edit_status").prop("checked", false);
        updateToggleTexts($form);
        setBusy($("#editEmailSet"), false);

        if (editEditor) {
            editEditor.setData("");
        }
    };

    updateToggleTexts($("#addEmailForm"));
    updateToggleTexts($("#editEmailForm"));

    $(document).on("change", "#addEmailForm .ss-doc-toggle input, #editEmailForm .ss-doc-toggle input", function () {
        updatePhaseToggleText($(this).closest(".ss-doc-toggle"));
    });

    $(document).on("change", "#addEmailForm .ss-status-toggle--inline input, #editEmailForm .ss-status-toggle--inline input", function () {
        updateStatusToggleText($(this).closest(".ss-status-toggle--inline"));
    });

    document.getElementById("addEmailModal").addEventListener("show.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("addEmailModal").addEventListener("hide.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("editEmailModal").addEventListener("hide.tw.modal", function () {
        resetEditForm();
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addEmailForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addEmailForm");
        const form = document.getElementById("addEmailForm");

        clearErrors($form);
        setBusy($("#saveEmailSet"), true);

        let formData = new FormData(form);
        formData.append("description", addEditor ? addEditor.getData() : "");

        axios({
            method: "post",
            url: route("email.template.store"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#saveEmailSet"), false);

            if (response.status == 200) {
                resetAddForm();
                addEmailModal.hide();
                showSuccess("Success!", "Email template successfully inserted.");
            }

            emailTemplateListTable.init();
        }).catch((error) => {
            setBusy($("#saveEmailSet"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#emailTemplateListTable").on("click", ".edit_btn", function () {
        let editId = $(this).attr("data-id");
        resetEditForm();

        axios({
            method: "get",
            url: route("email.template.edit", editId),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const $form = $("#editEmailForm");

                $form.find('input[name="email_title"]').val(dataset.email_title ? dataset.email_title : "");
                $form.find('input[name="id"]').val(editId);

                emailPhaseKeys.forEach((key) => {
                    $form.find(`#edit_phase_${key}`).prop("checked", isOnValue(dataset[key]));
                });

                $form.find("#edit_status").prop("checked", isOnValue(dataset.status));

                if (editEditor) {
                    editEditor.setData(dataset.description ? dataset.description : "");
                }

                updateToggleTexts($form);
                editEmailModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editEmailForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editEmailForm");
        const form = document.getElementById("editEmailForm");

        clearErrors($form);
        setBusy($("#editEmailSet"), true);

        let formData = new FormData(form);
        formData.append("description", editEditor ? editEditor.getData() : "");

        axios({
            method: "post",
            url: route("email.template.update"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#editEmailSet"), false);

            if (response.status == 200) {
                editEmailModal.hide();
                showSuccess("Success!", "Email template successfully updated.");
            }

            emailTemplateListTable.init();
        }).catch((error) => {
            setBusy($("#editEmailSet"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else if (error.response.status == 304) {
                    editEmailModal.hide();
                    showSuccess("No Data Change!", error.response.statusText);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#emailTemplateListTable").on("click", ".delete_btn", function () {
        let rowID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to delete this email template?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal .agreeWith").attr("data-action", "DELETE");
        confirmModal.show();
    });

    $("#emailTemplateListTable").on("click", ".restore_btn", function () {
        let dataID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to restore this email template?");
        $("#confirmModal .agreeWith").attr("data-id", dataID);
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal .agreeWith").attr("data-action", "RESTORE");
        confirmModal.show();
    });

    $("#emailTemplateListTable").on("click", ".status_updater", function () {
        let rowID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to change the status of this email template?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal .agreeWith").attr("data-action", "CHANGESTAT");
        confirmModal.show();
    });

    $("#emailTemplateListTable").on("click", ".updatePhase", function () {
        let rowID = $(this).attr("data-id");
        let phase = $(this).attr("data-phase");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to change the phase status of this email template?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-phase", phase);
        $("#confirmModal .agreeWith").attr("data-action", "CHANGEPHS");
        confirmModal.show();
    });

    $("#confirmModal .agreeWith").on("click", function () {
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr("data-id");
        let action = $agreeBTN.attr("data-action");
        let phase = $agreeBTN.attr("data-phase");

        $("#confirmModal button").attr("disabled", "disabled");

        const done = (title, message) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showSuccess(title, message);
            emailTemplateListTable.init();
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("email.template.destory", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "Email template successfully deleted.");
                }
            }).catch(failed);
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("email.template.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Email template successfully restored.");
                }
            }).catch(failed);
        } else if (action == "CHANGESTAT") {
            axios({
                method: "post",
                url: route("email.template.update.status"),
                data: { row_id: recordID },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Email template status successfully updated.");
                }
            }).catch(failed);
        } else if (action == "CHANGEPHS") {
            axios({
                method: "post",
                url: route("email.template.update.phase.status"),
                data: { row_id: recordID, phase: phase },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Email template phase status successfully updated.");
                }
            }).catch(failed);
        }
    });
})();
