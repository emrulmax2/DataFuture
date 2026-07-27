import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
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

const normalizeType = (type) => {
    return Number(type) === 2 || String(type).toLowerCase() === "sms" ? "sms" : "email";
};

const typeLabel = (type) => {
    return normalizeType(type) === "sms" ? "SMS" : "Email";
};

const formatType = (cell) => {
    const type = normalizeType(cell.getValue());
    const icon = type === "sms" ? "smartphone" : "mail";

    return `<span class="ss-template-type-badge is-${type}"><i data-lucide="${icon}"></i>${typeLabel(type)}</span>`;
};

var CommunTemplateListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query-LS").val() != "" ? $("#query-LS").val() : "";
        let status = $("#status-LS").val() != "" ? $("#status-LS").val() : "";

        if (window.communicationTemplateTableInstance) {
            window.communicationTemplateTableInstance.destroy();
        }

        let tableContent = new Tabulator("#CommunTemplateListTable", {
            ajaxURL: route("communication.template.list"),
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
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                    minWidth: 110,
                    widthGrow: 0.5,
                    formatter(cell) {
                        return formatType(cell);
                    },
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 260,
                    widthGrow: 2.2,
                    formatter(cell) {
                        return `<span class="ss-template-name-cell">${escapeHtml(cell.getValue())}</span>`;
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
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit communication template"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete communication template"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore communication template"><i data-lucide="rotate-cw"></i></button>';
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

        window.communicationTemplateTableInstance = tableContent;

        if (window.communicationTemplateTableResizeHandler) {
            window.removeEventListener("resize", window.communicationTemplateTableResizeHandler);
        }

        window.communicationTemplateTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.communicationTemplateTableResizeHandler);

        $("#tabulator-export-csv-LS").off("click.communicationtemplate").on("click.communicationtemplate", function () {
            tableContent.download("csv", "communication-templates.csv");
        });

        $("#tabulator-export-xlsx-LS").off("click.communicationtemplate").on("click.communicationtemplate", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "communication-templates.xlsx", {
                sheetName: "Communication Templates",
            });
        });

        $("#tabulator-print-LS").off("click.communicationtemplate").on("click.communicationtemplate", function () {
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
    if (!$("#CommunTemplateListTable").length) {
        return;
    }

    CommunTemplateListTable.init();

    function filterHTMLForm() {
        CommunTemplateListTable.init();
    }

    $("#tabulatorFilterForm-LS")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLForm();
        }
    });

    $("#tabulator-html-filter-go-LS").on("click", function () {
        filterHTMLForm();
    });

    $("#tabulator-html-filter-reset-LS").on("click", function () {
        $("#query-LS").val("");
        $("#status-LS").val("1");
        filterHTMLForm();
    });

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addTemplateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTemplateModal"));
    const editTemplateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTemplateModal"));
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
        $form.find(".ss-template-type-options").removeClass("is-danger");
    };

    const showErrors = ($form, errors) => {
        for (const [key, val] of Object.entries(errors)) {
            const message = Array.isArray(val) ? val[0] : val;
            const field = key.split(".")[0];

            $form.find(`.${field}`).addClass("border-danger");
            $form.find(`.error-${field}`).html(message);

            if (field === "type") {
                $form.find(".ss-template-type-options").addClass("is-danger");
            }

            if (field === "email_content") {
                $form.find(".ss-editor").addClass("is-danger");
            }
        }
    };

    const updateSmsCounter = ($textarea) => {
        const chars = $textarea.val().length;
        const messages = Math.max(Math.ceil(chars / 160), 1);
        const remaining = chars > 0 ? messages * 160 - chars : 160;
        const $form = $textarea.closest("form");

        $form.find(".sms_countr").html(`${remaining} / ${messages}`);
    };

    const setTemplateMode = ($form, mode, options = {}) => {
        const clearContent = options.clearContent || false;
        const editor = $form.attr("id") === "editTemplateForm" ? editEditor : addEditor;
        const isSms = normalizeType(mode) === "sms";

        $form.find('input[name="type"][value="' + (isSms ? "2" : "1") + '"]').prop("checked", true);

        if (isSms) {
            $form.find(".emailContentWrap").hide();
            $form.find(".smsContentWrap").show();
        } else {
            $form.find(".smsContentWrap").hide();
            $form.find(".emailContentWrap").show();
        }

        if (clearContent) {
            if (editor) {
                editor.setData("");
            }

            $form.find(".smsContentWrap textarea").val("");
            updateSmsCounter($form.find(".smsContentWrap textarea"));
        }
    };

    const resetAddForm = () => {
        const $form = $("#addTemplateForm");
        clearErrors($form);
        $form.find('input[name="name"]').val("");
        setTemplateMode($form, "email", { clearContent: true });
        setBusy($("#saveTemplate"), false);
    };

    const resetEditForm = () => {
        const $form = $("#editTemplateForm");
        clearErrors($form);
        $form.find('input[name="name"]').val("");
        $form.find('input[name="id"]').val("0");
        setTemplateMode($form, "email", { clearContent: true });
        setBusy($("#editTemplates"), false);
    };

    updateSmsCounter($("#sms_content"));
    updateSmsCounter($("#edit_sms_content"));

    $(document).on("change", "#addTemplateForm .templateType, #editTemplateForm .templateType", function () {
        const $form = $(this).closest("form");
        setTemplateMode($form, $(this).val(), { clearContent: true });
        clearErrors($form);
    });

    $(document).on("keyup input", "#sms_content, #edit_sms_content", function () {
        updateSmsCounter($(this));
    });

    document.getElementById("addTemplateModal").addEventListener("show.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("addTemplateModal").addEventListener("hide.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("editTemplateModal").addEventListener("hide.tw.modal", function () {
        resetEditForm();
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addTemplateForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addTemplateForm");
        const form = document.getElementById("addTemplateForm");

        clearErrors($form);
        setBusy($("#saveTemplate"), true);

        let formData = new FormData(form);
        formData.append("email_content", addEditor ? addEditor.getData() : "");

        axios({
            method: "post",
            url: route("communication.template.store"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#saveTemplate"), false);

            if (response.status == 200) {
                resetAddForm();
                addTemplateModal.hide();
                showSuccess("Success!", "System communication template successfully inserted.");
            }

            CommunTemplateListTable.init();
        }).catch((error) => {
            setBusy($("#saveTemplate"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#CommunTemplateListTable").on("click", ".edit_btn", function () {
        let recordId = $(this).attr("data-id");
        resetEditForm();

        axios({
            method: "get",
            url: route("communication.template.edit", recordId),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const $form = $("#editTemplateForm");
                const isSms = Number(dataset.type) === 2;

                $form.find('input[name="name"]').val(dataset.name ? dataset.name : "");
                $form.find('input[name="id"]').val(recordId);
                setTemplateMode($form, isSms ? "sms" : "email");

                if (isSms) {
                    $form.find("#edit_sms_content").val(dataset.content ? dataset.content : "");
                    updateSmsCounter($form.find("#edit_sms_content"));
                } else if (editEditor) {
                    editEditor.setData(dataset.content ? dataset.content : "");
                }

                editTemplateModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editTemplateForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editTemplateForm");
        const form = document.getElementById("editTemplateForm");

        clearErrors($form);
        setBusy($("#editTemplates"), true);

        let formData = new FormData(form);
        formData.append("email_content", editEditor ? editEditor.getData() : "");

        axios({
            method: "post",
            url: route("communication.template.update"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#editTemplates"), false);

            if (response.status == 200) {
                editTemplateModal.hide();
                showSuccess("Success!", "System communication template successfully updated.");
            }

            CommunTemplateListTable.init();
        }).catch((error) => {
            setBusy($("#editTemplates"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else if (error.response.status == 304) {
                    editTemplateModal.hide();
                    showSuccess("No Data Change!", error.response.statusText);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#CommunTemplateListTable").on("click", ".delete_btn", function () {
        let rowID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to delete this communication template?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-action", "DELETE");
        confirmModal.show();
    });

    $("#CommunTemplateListTable").on("click", ".restore_btn", function () {
        let dataID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to restore this communication template?");
        $("#confirmModal .agreeWith").attr("data-id", dataID);
        $("#confirmModal .agreeWith").attr("data-action", "RESTORE");
        confirmModal.show();
    });

    $("#confirmModal .agreeWith").on("click", function () {
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr("data-id");
        let action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        const done = (title, message) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showSuccess(title, message);
            CommunTemplateListTable.init();
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("communication.template.destory", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "System communication template successfully deleted.");
                }
            }).catch(failed);
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("communication.template.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "System communication template successfully restored.");
                }
            }).catch(failed);
        }
    });
})();
