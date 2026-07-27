import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const letterPhaseKeys = ["admission", "live", "hr", "document_request"];

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

var letterSettingsListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query-LS").val() != "" ? $("#query-LS").val() : "";
        let status = $("#status-LS").val() != "" ? $("#status-LS").val() : "";
        let phase = $("#phase-LS").val() != "" ? $("#phase-LS").val() : "";

        if (window.letterSettingsTableInstance) {
            window.letterSettingsTableInstance.destroy();
        }

        let tableContent = new Tabulator("#letterSettingsListTable", {
            ajaxURL: route("letter.set.list"),
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
                    title: "Letter Type",
                    field: "letter_type",
                    headerHozAlign: "left",
                    minWidth: 170,
                    widthGrow: 1.15,
                    formatter(cell) {
                        const value = escapeHtml(cell.getValue());
                        return '<a href="' + route("letter.set.edit", cell.getData().id) + '" target="_blank" class="ss-letter-link">' + value + "</a>";
                    },
                },
                {
                    title: "Letter Title",
                    field: "letter_title",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 1.7,
                    formatter(cell) {
                        const value = escapeHtml(cell.getValue());
                        return '<a href="' + route("letter.set.edit", cell.getData().id) + '" target="_blank" class="ss-letter-title-cell">' + value + "</a>";
                    },
                },
                {
                    title: "Document Requests",
                    field: "document_request",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 118,
                    widthGrow: 0.58,
                    formatter(cell) {
                        return formatSwitch(cell, {
                            className: "updatePhase",
                            phase: "document_request",
                            label: "Toggle document request phase",
                        });
                    },
                },
                {
                    title: "Admission",
                    field: "admission",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 92,
                    widthGrow: 0.5,
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
                    minWidth: 82,
                    widthGrow: 0.45,
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
                    minWidth: 76,
                    widthGrow: 0.4,
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
                    minWidth: 86,
                    widthGrow: 0.45,
                    formatter(cell) {
                        return formatSwitch(cell, {
                            className: "status_updater",
                            label: "Toggle letter set status",
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
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit letter set"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete letter set"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore letter set"><i data-lucide="rotate-cw"></i></button>';
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

        window.letterSettingsTableInstance = tableContent;

        if (window.letterSettingsTableResizeHandler) {
            window.removeEventListener("resize", window.letterSettingsTableResizeHandler);
        }

        window.letterSettingsTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.letterSettingsTableResizeHandler);

        $("#tabulator-export-csv-LS").off("click.letterset").on("click.letterset", function () {
            tableContent.download("csv", "letter-sets.csv");
        });

        $("#tabulator-export-xlsx-LS").off("click.letterset").on("click.letterset", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "letter-sets.xlsx", {
                sheetName: "Letter Set Details",
            });
        });

        $("#tabulator-print-LS").off("click.letterset").on("click.letterset", function () {
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
    if (!$("#letterSettingsListTable").length) {
        return;
    }

    letterSettingsListTable.init();

    function filterHTMLForm() {
        letterSettingsListTable.init();
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
        $("#phase-LS").val("");
        filterHTMLForm();
    });

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLetterModal"));
    const editLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editLetterModal"));
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
        $form.find(".ss-document-choices").removeClass("border-danger");
        $form.find(".ss-editor").removeClass("is-danger");
    };

    const showErrors = ($form, errors) => {
        for (const [key, val] of Object.entries(errors)) {
            const message = Array.isArray(val) ? val[0] : val;
            const field = key.split(".")[0];

            $form.find(`.${field}`).addClass("border-danger");
            $form.find(`.error-${field}`).html(message);

            if (field === "phase") {
                $form.find(".ss-document-choices").addClass("border-danger");
            }

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
        $toggle.find(".ss-status-toggle__copy small").text(enabled ? "Letter set is active" : "Letter set is inactive");
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
        const $form = $("#addLetterForm");
        clearErrors($form);
        $form.find('input[name="letter_type"]').val("");
        $form.find('input[name="letter_title"]').val("");
        $form.find(".phaseCheckboxs").prop("checked", false);
        $form.find("#status").prop("checked", true);
        updateToggleTexts($form);
        setBusy($("#saveLetterSet"), false);

        if (addEditor) {
            addEditor.setData("");
        }
    };

    const resetEditForm = () => {
        const $form = $("#editLetterForm");
        clearErrors($form);
        $form.find('input[name="letter_type"]').val("");
        $form.find('input[name="letter_title"]').val("");
        $form.find('input[name="id"]').val("0");
        $form.find(".phaseCheckboxs").prop("checked", false);
        $form.find("#edit_status").prop("checked", false);
        updateToggleTexts($form);
        setBusy($("#editLetterSet"), false);

        if (editEditor) {
            editEditor.setData("");
        }
    };

    updateToggleTexts($("#addLetterForm"));
    updateToggleTexts($("#editLetterForm"));

    $(document).on("change", "#addLetterForm .ss-doc-toggle input, #editLetterForm .ss-doc-toggle input", function () {
        updatePhaseToggleText($(this).closest(".ss-doc-toggle"));
    });

    $(document).on("change", "#addLetterForm .ss-status-toggle--inline input, #editLetterForm .ss-status-toggle--inline input", function () {
        updateStatusToggleText($(this).closest(".ss-status-toggle--inline"));
    });

    document.getElementById("addLetterModal").addEventListener("show.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("addLetterModal").addEventListener("hide.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("editLetterModal").addEventListener("hide.tw.modal", function () {
        resetEditForm();
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addLetterForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addLetterForm");
        const form = document.getElementById("addLetterForm");

        clearErrors($form);
        setBusy($("#saveLetterSet"), true);

        let formData = new FormData(form);
        formData.append("description", addEditor ? addEditor.getData() : "");

        axios({
            method: "post",
            url: route("letter.set.store"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#saveLetterSet"), false);

            if (response.status == 200) {
                resetAddForm();
                addLetterModal.hide();
                showSuccess("Success!", "Letter set successfully inserted.");
            }

            letterSettingsListTable.init();
        }).catch((error) => {
            setBusy($("#saveLetterSet"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#letterSettingsListTable").on("click", ".edit_btn", function () {
        let editId = $(this).attr("data-id");
        resetEditForm();

        axios({
            method: "get",
            url: route("letter.set.get.row", editId),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const $form = $("#editLetterForm");

                $form.find('input[name="letter_type"]').val(dataset.letter_type ? dataset.letter_type : "");
                $form.find('input[name="letter_title"]').val(dataset.letter_title ? dataset.letter_title : "");
                $form.find('input[name="id"]').val(editId);

                letterPhaseKeys.forEach((key) => {
                    $form.find(`#edit_phase_${key}`).prop("checked", isOnValue(dataset[key]));
                });

                $form.find("#edit_status").prop("checked", isOnValue(dataset.status));

                if (editEditor) {
                    editEditor.setData(dataset.description ? dataset.description : "");
                }

                updateToggleTexts($form);
                editLetterModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editLetterForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editLetterForm");
        const form = document.getElementById("editLetterForm");

        clearErrors($form);
        setBusy($("#editLetterSet"), true);

        let formData = new FormData(form);
        formData.append("description", editEditor ? editEditor.getData() : "");

        axios({
            method: "post",
            url: route("letter.set.update"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#editLetterSet"), false);

            if (response.status == 200) {
                editLetterModal.hide();
                showSuccess("Success!", "Letter set successfully updated.");
            }

            letterSettingsListTable.init();
        }).catch((error) => {
            setBusy($("#editLetterSet"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else if (error.response.status == 304) {
                    editLetterModal.hide();
                    showSuccess("No Data Change!", error.response.statusText);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#letterSettingsListTable").on("click", ".delete_btn", function () {
        let rowID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to delete this letter set?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal .agreeWith").attr("data-action", "DELETE");
        confirmModal.show();
    });

    $("#letterSettingsListTable").on("click", ".restore_btn", function () {
        let dataID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to restore this letter set?");
        $("#confirmModal .agreeWith").attr("data-id", dataID);
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal .agreeWith").attr("data-action", "RESTORE");
        confirmModal.show();
    });

    $("#letterSettingsListTable").on("click", ".status_updater", function () {
        let rowID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to change the status of this letter set?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-phase", "");
        $("#confirmModal .agreeWith").attr("data-action", "CHANGESTAT");
        confirmModal.show();
    });

    $("#letterSettingsListTable").on("click", ".updatePhase", function () {
        let rowID = $(this).attr("data-id");
        let phase = $(this).attr("data-phase");

        $("#confirmModal .confModTitle").html(confModalTitle);
        $("#confirmModal .confModDesc").html("Do you really want to change the phase status of this letter set?");
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
            letterSettingsListTable.init();
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("letter.set.destory", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "Letter set successfully deleted.");
                }
            }).catch(failed);
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("letter.set.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Letter set successfully restored.");
                }
            }).catch(failed);
        } else if (action == "CHANGESTAT") {
            axios({
                method: "post",
                url: route("letter.set.update.status"),
                data: { row_id: recordID },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Letter set status successfully updated.");
                }
            }).catch(failed);
        } else if (action == "CHANGEPHS") {
            axios({
                method: "post",
                url: route("letter.set.update.phase.status"),
                data: { row_id: recordID, phase: phase },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Letter set phase status successfully updated.");
                }
            }).catch(failed);
        }
    });
})();
