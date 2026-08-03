import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
};

const refreshIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.8,
        nameAttr: "data-lucide",
    });
};

const getModal = (selector) => {
    const element = document.querySelector(selector);
    return element ? tailwind.Modal.getOrCreateInstance(element) : null;
};

const setButtonBusy = ($button, busy) => {
    const $loader = $button.find("svg").last();

    $button.prop("disabled", busy);
    $loader.css("display", busy ? "inline-block" : "none");
};

const setModalCopy = (selector, titleClass, descClass, title, description) => {
    $(`${selector} ${titleClass}`).html(title);
    $(`${selector} ${descClass}`).html(description);
    refreshIcons();
};

const clearFormErrors = ($form) => {
    $form.find(".acc__input-error").html("");
    $form.find("input").removeClass("is-invalid border-danger");
};

const applyValidationErrors = ($form, errors = {}) => {
    Object.entries(errors).forEach(([key, value]) => {
        const message = Array.isArray(value) ? value[0] : value;

        $form.find(`[name="${key}"]`).addClass("is-invalid");
        $form.find(`.error-${key}`).html(escapeHtml(message));
    });
};

var agentBankListTable = (function () {
    let tableContent;

    const $table = $("#agentBankListTable");
    const listUrl = route("agent-user.bank.list");

    const getParams = () => ({
        agent_id: $table.attr("data-agent") || "0",
        querystr: ($("#query").val() || "").trim(),
        status: $("#status").val() || "1",
        size: true,
    });

    const renderBeneficiary = (row) => {
        const beneficiary = row.beneficiary || "Not set";

        return `
            <div class="agm-bank-beneficiary">
                <span class="agm-bank-icon">
                    <i data-lucide="landmark"></i>
                </span>
                <strong>${escapeHtml(beneficiary)}</strong>
            </div>
        `;
    };

    const renderStatus = (row) => {
        const id = escapeHtml(row.id);

        if (row.deleted_at != null) {
            return `
                <span class="agm-bank-status is-archived">
                    <i data-lucide="archive"></i>
                    Archived
                </span>
            `;
        }

        const isActive = Number(row.active) === 1;

        return `
            <button type="button" data-id="${id}" class="status_updater agm-bank-status ${isActive ? "is-active" : "is-inactive"}" title="Change bank status" aria-label="Change bank status">
                <b></b>
                ${isActive ? "Active" : "Inactive"}
            </button>
        `;
    };

    const renderActions = (row) => {
        const id = escapeHtml(row.id);

        if (row.deleted_at != null) {
            return `
                <span class="agm-bank-actions">
                    <button data-id="${id}" type="button" class="restore_btn agm-agent-action agm-agent-action--view" title="Restore bank" aria-label="Restore bank">
                        <i data-lucide="rotate-cw"></i>
                    </button>
                </span>
            `;
        }

        return `
            <span class="agm-bank-actions">
                <button data-id="${id}" data-tw-toggle="modal" data-tw-target="#editBankDetailsModal" type="button" class="edit_btn agm-agent-action agm-agent-action--edit" title="Edit bank" aria-label="Edit bank">
                    <i data-lucide="pencil"></i>
                </button>
                <button data-id="${id}" type="button" class="delete_btn agm-agent-action agm-agent-action--delete" title="Delete bank" aria-label="Delete bank">
                    <i data-lucide="trash-2"></i>
                </button>
            </span>
        `;
    };

    const _tableGen = function () {
        tableContent = new Tabulator("#agentBankListTable", {
            ajaxURL: listUrl,
            ajaxParams: getParams(),
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No bank details found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 110,
                    minWidth: 95,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="agm-agent-id">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Beneficiary Name",
                    field: "beneficiary",
                    minWidth: 280,
                    widthGrow: 2.2,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return renderBeneficiary(cell.getData());
                    },
                },
                {
                    title: "Sort Code",
                    field: "sort_code",
                    minWidth: 180,
                    widthGrow: 1,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="agm-bank-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "A/C No",
                    field: "ac_no",
                    minWidth: 210,
                    widthGrow: 1.1,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="agm-bank-muted">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Status",
                    field: "active",
                    minWidth: 150,
                    widthGrow: 0.8,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return renderStatus(cell.getData());
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 132,
                    minWidth: 132,
                    download: false,
                    formatter(cell) {
                        return renderActions(cell.getData());
                    },
                },
            ],
            ajaxResponse(url, params, response) {
                return response.data || [];
            },
            renderComplete() {
                refreshIcons();
            },
        });

        window.addEventListener("resize", () => {
            tableContent.redraw();
            refreshIcons();
        });
    };

    const reload = () => {
        if (!tableContent) {
            _tableGen();
            return;
        }

        tableContent.setData(listUrl, getParams());
    };

    return {
        init: function () {
            if (tableContent) {
                reload();
            } else {
                _tableGen();
            }
        },
        reload,
        download(format, filename, options = {}) {
            if (tableContent) {
                tableContent.download(format, filename, options);
            }
        },
        print() {
            if (tableContent) {
                tableContent.print();
            }
        },
    };
})();

(function () {
    if (!$("#agentBankListTable").length) return;

    agentBankListTable.init();

    const addBankDetailsModal = getModal("#addBankDetailsModal");
    const editBankDetailsModal = getModal("#editBankDetailsModal");
    const successModal = getModal("#successModal");
    const warningModal = getModal("#warningModal");
    const confirmModal = getModal("#confirmModal");

    const showSuccess = (title, description, action = "NONE", autoHide = true) => {
        setModalCopy("#successModal", ".successModalTitle", ".successModalDesc", title, description);
        $("#successModal .successCloser").attr("data-action", action);
        successModal?.show();

        if (autoHide) {
            window.setTimeout(() => successModal?.hide(), 2000);
        }
    };

    const showWarning = (title, description, autoHide = true) => {
        setModalCopy("#warningModal", ".warningModalTitle", ".warningModalDesc", title, description);
        $("#warningModal .warningCloser").attr("data-action", "DISMISS");
        warningModal?.show();

        if (autoHide) {
            window.setTimeout(() => warningModal?.hide(), 2400);
        }
    };

    const showConfirm = (recordId, action, description) => {
        $("#confirmModal .confModTitle").html("Are you sure?");
        $("#confirmModal .confModDesc").html(description);
        $("#confirmModal .agreeWith")
            .attr("data-recordid", recordId)
            .attr("data-status", action)
            .attr("data-id", recordId)
            .attr("data-action", action);
        refreshIcons();
        confirmModal?.show();
    };

    const resetBankForm = ($form) => {
        clearFormErrors($form);
        $form[0]?.reset();
    };

    $("#tabulatorFilterForm").on("submit", function (event) {
        event.preventDefault();
        agentBankListTable.reload();
    });

    $("#query").on("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault();
            agentBankListTable.reload();
        }
    });

    $("#tabulator-html-filter-go").on("click", function () {
        agentBankListTable.reload();
    });

    $("#tabulator-html-filter-reset").on("click", function () {
        $("#query").val("");
        $("#status").val("1");
        agentBankListTable.reload();
    });

    $("#tabulator-export-csv").on("click", function () {
        agentBankListTable.download("csv", "agent-bank-details.csv");
    });

    $("#tabulator-export-xlsx").on("click", function () {
        window.XLSX = xlsx;
        agentBankListTable.download("xlsx", "agent-bank-details.xlsx", {
            sheetName: "Agent Bank Details",
        });
    });

    $("#tabulator-print").on("click", function () {
        agentBankListTable.print();
    });

    document.getElementById("addBankDetailsModal")?.addEventListener("hide.tw.modal", function () {
        resetBankForm($("#addBankDetailsForm"));
        $("#addBankDetailsModal input[name='active']").prop("checked", true);
        setButtonBusy($("#saveABNK"), false);
    });

    document.getElementById("editBankDetailsModal")?.addEventListener("hide.tw.modal", function () {
        resetBankForm($("#editBankDetailsForm"));
        $("#editBankDetailsModal input[name='active']").prop("checked", false);
        $("#editBankDetailsModal [name='id']").val("0");
        setButtonBusy($("#updateABNK"), false);
    });

    document.getElementById("confirmModal")?.addEventListener("hide.tw.modal", function () {
        $("#confirmModal .confModDesc").html("");
        $("#confirmModal .agreeWith")
            .attr("data-recordid", "0")
            .attr("data-status", "none")
            .attr("data-id", "0")
            .attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#successModal .successCloser").on("click", function (event) {
        event.preventDefault();

        if ($(this).attr("data-action") === "RELOAD") {
            successModal?.hide();
            window.location.reload();
            return;
        }

        successModal?.hide();
    });

    $("#warningModal .warningCloser").on("click", function (event) {
        event.preventDefault();
        warningModal?.hide();
    });

    $("#confirmModal .disAgreeWith").on("click", function (event) {
        event.preventDefault();
        confirmModal?.hide();
    });

    $("#addBankDetailsForm").on("submit", function (event) {
        event.preventDefault();

        const form = document.getElementById("addBankDetailsForm");
        const $form = $(form);
        const $button = $("#saveABNK");

        clearFormErrors($form);
        setButtonBusy($button, true);

        axios({
            method: "post",
            url: route("agent-user.store.bank"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                if (response.status === 200) {
                    addBankDetailsModal?.hide();
                    showSuccess("Success!", "Agent bank details successfully inserted.");
                    agentBankListTable.reload();
                }
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    applyValidationErrors($form, error.response.data.errors);
                } else {
                    showWarning("Error Found!", "Something went wrong. Please try again or contact administrator.");
                }
            })
            .finally(() => {
                setButtonBusy($button, false);
            });
    });

    $("#agentBankListTable").on("click", ".edit_btn", function () {
        const editId = $(this).attr("data-id");

        clearFormErrors($("#editBankDetailsForm"));

        axios({
            method: "post",
            url: route("agent-user.edit.bank"),
            data: { editId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                if (response.status === 200) {
                    const dataset = response.data.res || {};

                    $("#editBankDetailsModal [name='beneficiary']").val(dataset.beneficiary || "");
                    $("#editBankDetailsModal [name='sort_code']").val(dataset.sort_code || "");
                    $("#editBankDetailsModal [name='ac_no']").val(dataset.ac_no || "");
                    $("#editBankDetailsModal input[name='active']").prop("checked", Number(dataset.active) === 1);
                    $("#editBankDetailsModal input[name='id']").val(editId);
                }
            })
            .catch(() => {
                editBankDetailsModal?.hide();
                showWarning("Error Found!", "Unable to load this bank detail. Please try again.");
            });
    });

    $("#editBankDetailsForm").on("submit", function (event) {
        event.preventDefault();

        const form = document.getElementById("editBankDetailsForm");
        const $form = $(form);
        const $button = $("#updateABNK");

        clearFormErrors($form);
        setButtonBusy($button, true);

        axios({
            method: "post",
            url: route("agent-user.update.bank"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                if (response.status === 200) {
                    editBankDetailsModal?.hide();
                    showSuccess("Success!", "Agent bank details successfully updated.");
                    agentBankListTable.reload();
                }
            })
            .catch((error) => {
                if (error.response?.status === 422) {
                    applyValidationErrors($form, error.response.data.errors);
                } else {
                    showWarning("Error Found!", "Something went wrong. Please try again or contact administrator.");
                }
            })
            .finally(() => {
                setButtonBusy($button, false);
            });
    });

    $("#agentBankListTable").on("click", ".status_updater", function (event) {
        event.preventDefault();
        showConfirm($(this).attr("data-id"), "CHANGESTATBNK", "Do you really want to change the status of this bank detail?");
    });

    $("#agentBankListTable").on("click", ".delete_btn", function (event) {
        event.preventDefault();
        showConfirm($(this).attr("data-id"), "DELETEBNK", "Do you really want to delete this bank detail? This process cannot be undone.");
    });

    $("#agentBankListTable").on("click", ".restore_btn", function (event) {
        event.preventDefault();
        showConfirm($(this).attr("data-id"), "RESTOREBNK", "Do you really want to restore this bank detail?");
    });

    $("#confirmModal .agreeWith").on("click", function (event) {
        event.preventDefault();

        const $agreeButton = $(this);
        const recordId = $agreeButton.attr("data-recordid") || $agreeButton.attr("data-id");
        const action = $agreeButton.attr("data-status") || $agreeButton.attr("data-action");

        const actionMap = {
            DELETEBNK: {
                method: "delete",
                url: route("agent-user.destroy.bank", recordId),
                message: "Agent bank details successfully deleted.",
            },
            RESTOREBNK: {
                method: "post",
                url: route("agent-user.restore.bank", recordId),
                message: "Agent bank details successfully restored.",
            },
            CHANGESTATBNK: {
                method: "post",
                url: route("agent-user.changestatus.bank", recordId),
                message: "Agent bank details status successfully updated.",
            },
        };

        const request = actionMap[action];

        if (!recordId || !request) {
            confirmModal?.hide();
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        axios({
            method: request.method,
            url: request.url,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                if (response.status === 200) {
                    confirmModal?.hide();
                    showSuccess(action === "DELETEBNK" ? "Done!" : "Success!", request.message);
                    agentBankListTable.reload();
                }
            })
            .catch(() => {
                showWarning("Error Found!", "Something went wrong. Please try again or contact administrator.");
            })
            .finally(() => {
                $("#confirmModal button").removeAttr("disabled");
            });
    });
})();
