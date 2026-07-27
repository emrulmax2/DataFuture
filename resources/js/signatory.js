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

var signatoryListTable = (function () {
    var _tableGen = function () {
        let queryStr = $("#query-SG").val() != "" ? $("#query-SG").val() : "";
        let status = $("#status-SG").val() != "" ? $("#status-SG").val() : "1";

        if (window.signatoryTableInstance) {
            window.signatoryTableInstance.destroy();
        }

        let tableContent = new Tabulator("#signatoryListTable", {
            ajaxURL: route("signatory.list"),
            ajaxParams: { queryStr: queryStr, status: status },
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
                    headerHozAlign: "left",
                    width: 68,
                    minWidth: 62,
                },
                {
                    title: "Signature",
                    field: "url",
                    headerHozAlign: "left",
                    minWidth: 140,
                    widthGrow: 0.8,
                    formatter(cell) {
                        const data = cell.getData();

                        if (data.url) {
                            return '<span class="ss-signature-thumb"><img alt="' + escapeHtml(data.signatory_name) + '" src="' + data.url + '"></span>';
                        }

                        return '<span class="ss-signature-empty"><i data-lucide="image-off"></i>No signature</span>';
                    },
                },
                {
                    title: "Name",
                    field: "signatory_name",
                    headerHozAlign: "left",
                    minWidth: 190,
                    widthGrow: 1.35,
                    formatter(cell) {
                        return '<span class="ss-signatory-name">' + escapeHtml(cell.getValue()) + "</span>";
                    },
                },
                {
                    title: "Designation",
                    field: "signatory_post",
                    headerHozAlign: "left",
                    minWidth: 220,
                    widthGrow: 1.5,
                    formatter(cell) {
                        return '<span class="ss-signatory-post">' + escapeHtml(cell.getValue()) + "</span>";
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 142,
                    minWidth: 142,
                    download: false,
                    formatter(cell) {
                        var btns = "";
                        const data = cell.getData();

                        if (data.url) {
                            btns += '<a target="_blank" href="' + data.url + '" download class="ss-row-action ss-row-action--view" aria-label="Download signature"><i data-lucide="download"></i></a>';
                        }

                        if (data.deleted_at == null) {
                            btns += '<button data-id="' + data.id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit signatory"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + data.id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete signatory"><i data-lucide="trash-2"></i></button>';
                        } else if (data.deleted_at != null) {
                            btns += '<button data-id="' + data.id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore signatory"><i data-lucide="rotate-cw"></i></button>';
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

        window.signatoryTableInstance = tableContent;

        if (window.signatoryTableResizeHandler) {
            window.removeEventListener("resize", window.signatoryTableResizeHandler);
        }

        window.signatoryTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.signatoryTableResizeHandler);

        $("#tabulator-export-csv-SG").off("click.signatory").on("click.signatory", function () {
            tableContent.download("csv", "signatories.csv");
        });

        $("#tabulator-export-xlsx-SG").off("click.signatory").on("click.signatory", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "signatories.xlsx", {
                sheetName: "Signatory Details",
            });
        });

        $("#tabulator-print-SG").off("click.signatory").on("click.signatory", function () {
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
    if (!$("#signatoryListTable").length) {
        return;
    }

    signatoryListTable.init();

    function filterHTMLForm() {
        signatoryListTable.init();
    }

    $("#tabulatorFilterForm-SG")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLForm();
        }
    });

    $("#tabulator-html-filter-go-SG").on("click", function () {
        filterHTMLForm();
    });

    $("#tabulator-html-filter-reset-SG").on("click", function () {
        $("#query-SG").val("");
        $("#status-SG").val("1");
        filterHTMLForm();
    });

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const addSignatoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSignatoryModal"));
    const editSignatoryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSignatoryModal"));
    const confirmTitle = "Are you sure?";

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
        $form.find(".ss-signatory-upload").removeClass("is-danger");
    };

    const showErrors = ($form, errors) => {
        for (const [key, val] of Object.entries(errors)) {
            const message = Array.isArray(val) ? val[0] : val;
            $form.find(`.${key}`).addClass("border-danger");
            $form.find(`.error-${key}`).html(message);

            if (key === "signatory") {
                $form.find(".ss-signatory-upload").addClass("is-danger");
            }
        }
    };

    const updateFileName = (input) => {
        const fileName = input.files?.[0]?.name || "No file selected";
        $(input).closest(".ss-signature-upload").find(".signatoryDocumentName").text(fileName);
    };

    const resetAddForm = () => {
        const $form = $("#addSignatoryForm");
        clearErrors($form);
        $form.find('input[name="signatory_name"]').val("");
        $form.find('input[name="signatory_post"]').val("");
        $form.find('input[name="signatory"]').val("");
        $form.find(".signatoryDocumentName").text("No file selected");
        setBusy($("#saveSignatorySet"), false);
    };

    const resetEditForm = () => {
        const $form = $("#editSignatoryForm");
        clearErrors($form);
        $form.find('input[name="signatory_name"]').val("");
        $form.find('input[name="signatory_post"]').val("");
        $form.find('input[name="id"]').val("0");
        $form.find('input[name="signatory"]').val("");
        $form.find(".signatoryDocumentName").text("No file selected");
        $form.find(".downloadExistAttachment").hide().attr("href", "#");
        setBusy($("#updateSignatorySet"), false);
    };

    document.getElementById("addSignatoryModal").addEventListener("show.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("addSignatoryModal").addEventListener("hide.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("editSignatoryModal").addEventListener("hide.tw.modal", function () {
        resetEditForm();
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .confModDesc").html("");
        $("#confirmModal .agreeWith").attr("data-recordid", "0");
        $("#confirmModal .agreeWith").attr("data-status", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addSignatoryForm, #editSignatoryForm").on("change", 'input[name="signatory"]', function () {
        updateFileName(this);
        $(this).closest("form").find(".ss-signatory-upload").removeClass("is-danger border-danger");
        $(this).closest("form").find(".error-signatory").html("");
    });

    $("#addSignatoryForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addSignatoryForm");
        const form = document.getElementById("addSignatoryForm");

        clearErrors($form);
        setBusy($("#saveSignatorySet"), true);

        axios({
            method: "post",
            url: route("signatory.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#saveSignatorySet"), false);

            if (response.status == 200) {
                resetAddForm();
                addSignatoryModal.hide();
                showSuccess("Success!", "Staff signatory successfully inserted.");
            }

            signatoryListTable.init();
        }).catch((error) => {
            setBusy($("#saveSignatorySet"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#signatoryListTable").on("click", ".edit_btn", function () {
        let signatoryID = $(this).attr("data-id");
        resetEditForm();

        axios({
            method: "post",
            url: route("signatory.edit"),
            data: { signatoryID: signatoryID },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            const dataset = response.data.message;

            $("#editSignatoryForm input[name='signatory_name']").val(dataset.signatory_name ? dataset.signatory_name : "");
            $("#editSignatoryForm input[name='signatory_post']").val(dataset.signatory_post ? dataset.signatory_post : "");
            $("#editSignatoryForm input[name='id']").val(signatoryID);

            if (dataset.signature) {
                $("#editSignatoryForm .downloadExistAttachment").show().attr("href", dataset.signature);
            }

            editSignatoryModal.show();
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editSignatoryForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editSignatoryForm");
        const form = document.getElementById("editSignatoryForm");

        clearErrors($form);
        setBusy($("#updateSignatorySet"), true);

        axios({
            method: "post",
            url: route("signatory.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#updateSignatorySet"), false);

            if (response.status == 200) {
                editSignatoryModal.hide();
                showSuccess("Success!", "Signatory successfully updated.");
            }

            signatoryListTable.init();
        }).catch((error) => {
            setBusy($("#updateSignatorySet"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#signatoryListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        let recordId = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confirmTitle);
        $("#confirmModal .confModDesc").html("Do you really want to delete this signatory?");
        $("#confirmModal .agreeWith").attr("data-recordid", recordId);
        $("#confirmModal .agreeWith").attr("data-status", "DELETE");
        confirmModal.show();
    });

    $("#signatoryListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        let recordId = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confirmTitle);
        $("#confirmModal .confModDesc").html("Do you really want to restore this signatory?");
        $("#confirmModal .agreeWith").attr("data-recordid", recordId);
        $("#confirmModal .agreeWith").attr("data-status", "RESTORE");
        confirmModal.show();
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();
        let recordId = $(this).attr("data-recordid");
        let action = $(this).attr("data-status");

        $("#confirmModal button").attr("disabled", "disabled");

        const done = (title, message) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showSuccess(title, message);
            signatoryListTable.init();
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("signatory.destory", recordId),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "Signatory successfully deleted.");
                }
            }).catch(failed);
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("signatory.restore", recordId),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Signatory successfully restored.");
                }
            }).catch(failed);
        } else {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
        }
    });
})();
