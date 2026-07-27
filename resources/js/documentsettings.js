import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const documentScopeKeys = [
    "application",
    "admission",
    "registration",
    "live",
    "student_profile",
    "staff",
    "agent",
];

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

const formatBool = (value) => {
    const isEnabled = isYesValue(value);
    return `<span class="ss-doc-icon ${isEnabled ? "is-active" : "is-inactive"}" aria-label="${isEnabled ? "Yes" : "No"}"><i data-lucide="${isEnabled ? "check" : "x"}"></i></span>`;
};

var table = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";

        if (window.documentSettingsTableInstance) {
            window.documentSettingsTableInstance.destroy();
        }

        let tableContent = new Tabulator("#documentsettingsTableId", {
            ajaxURL: route("documentsettings.list"),
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 160,
                    widthGrow: 1.45,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                    minWidth: 106,
                    widthGrow: 0.7,
                    formatter(cell) {
                        return `<span class="ss-doc-type-text">${escapeHtml(String(cell.getValue() || "optional").toLowerCase())}</span>`;
                    },
                },
                {
                    title: "Application",
                    field: "application",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 76,
                    widthGrow: 0.52,
                    formatter(cell) {
                        return formatBool(cell.getValue());
                    },
                },
                {
                    title: "Admission",
                    field: "admission",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 76,
                    widthGrow: 0.52,
                    formatter(cell) {
                        return formatBool(cell.getValue());
                    },
                },
                {
                    title: "Registration",
                    field: "registration",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 76,
                    widthGrow: 0.52,
                    formatter(cell) {
                        return formatBool(cell.getValue());
                    },
                },
                {
                    title: "Live",
                    field: "live",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 74,
                    widthGrow: 0.48,
                    formatter(cell) {
                        return formatBool(cell.getValue());
                    },
                },
                {
                    title: "Student Profile",
                    field: "student_profile",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 76,
                    widthGrow: 0.52,
                    formatter(cell) {
                        return formatBool(cell.getValue());
                    },
                },
                {
                    title: "Staff",
                    field: "staff",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 74,
                    widthGrow: 0.48,
                    formatter(cell) {
                        return formatBool(cell.getValue());
                    },
                },
                {
                    title: "Agent",
                    field: "agent",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 74,
                    widthGrow: 0.48,
                    formatter(cell) {
                        return formatBool(cell.getValue());
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
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit document setting"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete document setting"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore document setting"><i data-lucide="rotate-cw"></i></button>';
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

        window.documentSettingsTableInstance = tableContent;

        if (window.documentSettingsTableResizeHandler) {
            window.removeEventListener("resize", window.documentSettingsTableResizeHandler);
        }

        window.documentSettingsTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.documentSettingsTableResizeHandler);

        $("#tabulator-export-csv").off("click.documentsettings").on("click.documentsettings", function () {
            tableContent.download("csv", "document-settings-details.csv");
        });

        $("#tabulator-export-xlsx").off("click.documentsettings").on("click.documentsettings", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "document-settings-details.xlsx", {
                sheetName: "Document Settings Details",
            });
        });

        $("#tabulator-print").off("click.documentsettings").on("click.documentsettings", function () {
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
    if ($("#documentsettingsTableId").length) {
        table.init();

        function filterHTMLForm() {
            table.init();
        }

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
            $("#status").val("1");
            filterHTMLForm();
        });

        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = "Are you sure?";

        const setBusy = ($button, isBusy) => {
            $button.prop("disabled", isBusy);
            $button.find(".ss-spinner").css("display", isBusy ? "inline-block" : "none");
        };

        const showSuccess = (title, message) => {
            $("#successModal .successModalTitle").html(title);
            $("#successModal .successModalDesc").html(message);
            succModal.show();
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

        const showFormIssue = ($form, message) => {
            $form.find(".error-choose").html(message || "Please choose at least one document workflow.");
        };

        const updateDocumentToggleText = ($toggle) => {
            const enabled = $toggle.find("input").is(":checked");
            $toggle.find(".ss-status-toggle__copy small").text(enabled ? "Enabled" : "Not enabled");
        };

        const updateDocumentToggleTexts = ($form) => {
            $form.find(".ss-doc-toggle").each(function () {
                updateDocumentToggleText($(this));
            });
        };

        const resetAddForm = () => {
            const $form = $("#addForm");
            clearErrors($form);
            $form[0].reset();
            $form.find('input[name="type"][value="optional"]').prop("checked", true);
            $form.find('input[type="checkbox"]').prop("checked", false);
            updateDocumentToggleTexts($form);
            setBusy($("#save"), false);
        };

        const resetEditForm = () => {
            const $form = $("#editForm");
            clearErrors($form);
            $form[0].reset();
            $form.find('input[name="id"]').val("0");
            $form.find('input[type="checkbox"]').prop("checked", false);
            updateDocumentToggleTexts($form);
            setBusy($("#update"), false);
        };

        const populateEditForm = (dataset, editId) => {
            const $form = $("#editForm");

            $form.find('input[name="name"]').val(dataset.name ? dataset.name : "");
            $form.find(`input[name="type"][value="${dataset.type || "optional"}"]`).prop("checked", true);

            documentScopeKeys.forEach((key) => {
                $form.find(`input[name="${key}"]`).prop("checked", isYesValue(dataset[key]));
            });

            $form.find('input[name="id"]').val(editId);
            updateDocumentToggleTexts($form);
        };

        resetAddForm();
        resetEditForm();

        $(".ss-doc-toggle input").on("change", function () {
            updateDocumentToggleText($(this).closest(".ss-doc-toggle"));
        });

        document.getElementById("addModal").addEventListener("show.tw.modal", function () {
            resetAddForm();
        });

        document.getElementById("addModal").addEventListener("hide.tw.modal", function () {
            resetAddForm();
        });

        document.getElementById("editModal").addEventListener("hide.tw.modal", function () {
            resetEditForm();
        });

        document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
            $("#confirmModal .agreeWith").attr("data-id", "0");
            $("#confirmModal .agreeWith").attr("data-action", "none");
            $("#confirmModal button").removeAttr("disabled");
        });

        $("#addForm").on("submit", function (e) {
            e.preventDefault();
            const $form = $("#addForm");
            const form = document.getElementById("addForm");

            clearErrors($form);
            setBusy($("#save"), true);

            axios({
                method: "post",
                url: route("documentsettings.store"),
                data: new FormData(form),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                setBusy($("#save"), false);

                if (response.status == 200) {
                    resetAddForm();
                    addModal.hide();
                    showSuccess("Success!", "Document setting successfully inserted.");
                }

                table.init();
            }).catch((error) => {
                setBusy($("#save"), false);

                if (error.response) {
                    if (error.response.status == 422) {
                        if (error.response.data.errors) {
                            showErrors($form, error.response.data.errors);
                        } else {
                            showFormIssue($form, error.response.data.message);
                        }
                    } else {
                        console.log("error");
                    }
                }
            });
        });

        $("#documentsettingsTableId").on("click", ".edit_btn", function () {
            let editId = $(this).attr("data-id");
            resetEditForm();

            axios({
                method: "get",
                url: route("documentsettings.edit", editId),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    populateEditForm(response.data, editId);
                    editModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editForm").on("submit", function (e) {
            e.preventDefault();
            const $form = $("#editForm");
            const form = document.getElementById("editForm");

            clearErrors($form);
            setBusy($("#update"), true);

            axios({
                method: "post",
                url: route("documentsettings.update"),
                data: new FormData(form),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                setBusy($("#update"), false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess("Success!", "Document setting successfully updated.");
                }

                table.init();
            }).catch((error) => {
                setBusy($("#update"), false);

                if (error.response) {
                    if (error.response.status == 422) {
                        if (error.response.data.errors) {
                            showErrors($form, error.response.data.errors);
                        } else {
                            showFormIssue($form, error.response.data.message);
                        }
                    } else if (error.response.status == 304) {
                        editModal.hide();
                        showSuccess("No Data Change!", error.response.statusText);
                    } else {
                        console.log("error");
                    }
                }
            });
        });

        $("#confirmModal .agreeWith").on("click", function () {
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr("data-id");
            let action = $agreeBTN.attr("data-action");

            $("#confirmModal button").attr("disabled", "disabled");

            if (action == "DELETE") {
                axios({
                    method: "delete",
                    url: route("documentsettings.destory", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then((response) => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Done!", "Document setting successfully deleted.");
                    }
                    table.init();
                }).catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            } else if (action == "RESTORE") {
                axios({
                    method: "post",
                    url: route("documentsettings.restore", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then((response) => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Success!", "Document setting successfully restored.");
                    }
                    table.init();
                }).catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            }
        });

        $("#documentsettingsTableId").on("click", ".delete_btn", function () {
            let rowID = $(this).attr("data-id");

            $("#confirmModal .confModTitle").html(confModalDelTitle);
            $("#confirmModal .confModDesc").html("Do you really want to delete this document setting?");
            $("#confirmModal .agreeWith").attr("data-id", rowID);
            $("#confirmModal .agreeWith").attr("data-action", "DELETE");
            confModal.show();
        });

        $("#documentsettingsTableId").on("click", ".restore_btn", function () {
            let dataID = $(this).attr("data-id");

            $("#confirmModal .confModTitle").html(confModalDelTitle);
            $("#confirmModal .confModDesc").html("Do you really want to restore this document setting?");
            $("#confirmModal .agreeWith").attr("data-id", dataID);
            $("#confirmModal .agreeWith").attr("data-action", "RESTORE");
            confModal.show();
        });
    }
})();
