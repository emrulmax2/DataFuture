import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

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

const isRequiredValue = (value) => String(value).toLowerCase() === "yes";

var consentPolicyListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";

        if (window.consentPolicyTableInstance) {
            window.consentPolicyTableInstance.destroy();
        }

        let tableContent = new Tabulator("#consentPolicyListTable", {
            ajaxURL: route("consent.list"),
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
                    minWidth: 150,
                    widthGrow: 1.1,
                    variableHeight: true,
                    formatter(cell) {
                        return `<span class="ss-cell-wrap">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                    minWidth: 200,
                    widthGrow: 1.5,
                    variableHeight: true,
                    formatter(cell) {
                        const value = cell.getValue();

                        if (!value) {
                            return '<span class="ss-cell-muted">&mdash;</span>';
                        }

                        return (
                            '<span class="ss-cell-wrap ss-desc-cell" tabindex="0"' +
                            ` data-ss-tooltip-title="${escapeHtml(cell.getData().name)}"` +
                            ` data-ss-tooltip="${escapeHtml(value)}">${escapeHtml(value)}</span>`
                        );
                    },
                },
                {
                    title: "Department",
                    field: "department",
                    headerHozAlign: "left",
                    minWidth: 140,
                    widthGrow: 1.3,
                    variableHeight: true,
                    formatter(cell) {
                        const value = cell.getValue();
                        return value
                            ? `<span class="ss-cell-wrap">${escapeHtml(value)}</span>`
                            : '<span class="ss-cell-muted">&mdash;</span>';
                    },
                },
                {
                    title: "Required",
                    field: "is_required",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 104,
                    widthGrow: 0.5,
                    formatter(cell) {
                        const required = isRequiredValue(cell.getValue());
                        return `<span class="ss-status-pill ${required ? "is-active" : "is-inactive"}"><span></span>${required ? "Yes" : "No"}</span>`;
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
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit consent policy"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete consent policy"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore consent policy"><i data-lucide="rotate-cw"></i></button>';
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

        window.consentPolicyTableInstance = tableContent;

        if (window.consentPolicyTableResizeHandler) {
            window.removeEventListener("resize", window.consentPolicyTableResizeHandler);
        }

        window.consentPolicyTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.consentPolicyTableResizeHandler);

        $("#tabulator-export-csv").off("click.consentpolicy").on("click.consentpolicy", function () {
            tableContent.download("csv", "consent-policies.csv");
        });

        $("#tabulator-export-xlsx").off("click.consentpolicy").on("click.consentpolicy", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "consent-policies.xlsx", {
                sheetName: "Consent Policy Details",
            });
        });

        $("#tabulator-print").off("click.consentpolicy").on("click.consentpolicy", function () {
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
    if (!$("#consentPolicyListTable").length) {
        return;
    }

    consentPolicyListTable.init();

    function filterHTMLForm() {
        consentPolicyListTable.init();
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

    const tomOptions = {
        maxOptions: null,
        dropdownParent: "body",
        dropdownClass: "ts-dropdown ss-settings-tom-dropdown",
        create: false,
        allowEmptyOption: true,
        copyClassesToDropdown: false,
    };

    const departmentSelect = new TomSelect("#department_id", tomOptions);
    const editDepartmentSelect = new TomSelect("#edit_department_id", tomOptions);

    const addConsentPolicyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addConsentPolicyModal"));
    const editConsentPolicyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editConsentPolicyModal"));
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const confModalDelTitle = "Are you sure?";

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

    // The toggle is presentational; the posted value lives in the hidden input so
    // "No" is always submitted rather than the field dropping out when unchecked.
    const syncRequiredToggle = ($form) => {
        const $toggle = $form.find("[data-required-toggle]");
        const required = $toggle.is(":checked");

        $form.find('input[type="hidden"][name="is_required"]').val(required ? "Yes" : "No");
        $toggle.closest(".ss-status-toggle").find(".ss-status-toggle__copy small").text(required ? "Consent is mandatory" : "Optional consent");
    };

    const resetAddForm = () => {
        const $form = $("#addConsentPolicyForm");
        clearErrors($form);
        $form.find('input[name="name"]').val("");
        $form.find('textarea[name="description"]').val("");
        departmentSelect.clear(true);
        $form.find("[data-required-toggle]").prop("checked", false);
        syncRequiredToggle($form);
        setBusy($("#saveCP"), false);
    };

    const resetEditForm = () => {
        const $form = $("#editConsentPolicyForm");
        clearErrors($form);
        $form.find('input[name="name"]').val("");
        $form.find('textarea[name="description"]').val("");
        $form.find('input[name="id"]').val("0");
        editDepartmentSelect.clear(true);
        $form.find("[data-required-toggle]").prop("checked", false);
        syncRequiredToggle($form);
        setBusy($("#updateCP"), false);
    };

    resetAddForm();
    resetEditForm();

    $(document).on("change", "[data-required-toggle]", function () {
        syncRequiredToggle($(this).closest("form"));
    });

    document.getElementById("addConsentPolicyModal").addEventListener("show.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("addConsentPolicyModal").addEventListener("hide.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("editConsentPolicyModal").addEventListener("hide.tw.modal", function () {
        resetEditForm();
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#addConsentPolicyForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addConsentPolicyForm");
        const form = document.getElementById("addConsentPolicyForm");

        clearErrors($form);
        setBusy($("#saveCP"), true);

        axios({
            method: "post",
            url: route("consent.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#saveCP"), false);

            if (response.status == 200) {
                resetAddForm();
                addConsentPolicyModal.hide();
                showSuccess("Success!", "Consent policy successfully inserted.");
            }

            consentPolicyListTable.init();
        }).catch((error) => {
            setBusy($("#saveCP"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#consentPolicyListTable").on("click", ".edit_btn", function () {
        let editId = $(this).attr("data-id");
        resetEditForm();

        axios({
            method: "get",
            url: route("consent.edit", editId),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const $form = $("#editConsentPolicyForm");

                $form.find('input[name="name"]').val(dataset.name ? dataset.name : "");
                $form.find('textarea[name="description"]').val(dataset.description ? dataset.description : "");
                $form.find('input[name="id"]').val(editId);

                editDepartmentSelect.setValue(dataset.department_id ? String(dataset.department_id) : "", true);

                $form.find("[data-required-toggle]").prop("checked", isRequiredValue(dataset.is_required));
                syncRequiredToggle($form);

                editConsentPolicyModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editConsentPolicyForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editConsentPolicyForm");
        const form = document.getElementById("editConsentPolicyForm");

        clearErrors($form);
        setBusy($("#updateCP"), true);

        axios({
            method: "post",
            url: route("consent.update"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#updateCP"), false);

            if (response.status == 200) {
                editConsentPolicyModal.hide();
                showSuccess("Success!", "Consent policy successfully updated.");
            }

            consentPolicyListTable.init();
        }).catch((error) => {
            setBusy($("#updateCP"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else if (error.response.status == 304) {
                    editConsentPolicyModal.hide();
                    showSuccess("No Data Change!", error.response.statusText);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#consentPolicyListTable").on("click", ".delete_btn", function () {
        let rowID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalDelTitle);
        $("#confirmModal .confModDesc").html("Do you really want to delete this consent policy?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-action", "DELETE");
        confirmModal.show();
    });

    $("#consentPolicyListTable").on("click", ".restore_btn", function () {
        let dataID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalDelTitle);
        $("#confirmModal .confModDesc").html("Do you really want to restore this consent policy?");
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
            consentPolicyListTable.init();
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("consent.destory", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "Consent policy successfully deleted.");
                }
            }).catch(failed);
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("consent.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Consent policy successfully restored.");
                }
            }).catch(failed);
        }
    });
})();
