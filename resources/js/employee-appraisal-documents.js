import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

const renderLucideIcons = () => {
    createIcons({
        icons,
        "stroke-width": 1.5,
        nameAttr: "data-lucide",
    });
};

const pluralize = (count, singular) => singular + (count === 1 ? "" : "s");

const updateSectionSummary = (selector, totalRows, singular, emptyLabel) => {
    const summaryEl = document.querySelector(selector);
    if (!summaryEl) return;

    summaryEl.textContent = totalRows > 0
        ? `${totalRows} ${pluralize(totalRows, singular)} on file`
        : emptyLabel;
};

const updateTableFooterMeta = (table, totalRows, singular) => {
    if (!table || typeof table.getElement !== "function") return;

    const tableEl = table.getElement();
    const footerEl = tableEl.querySelector(".tabulator-footer .tabulator-footer-contents");
    if (!footerEl) return;

    let counterEl = footerEl.querySelector(".tabulator-page-counter");
    if (!counterEl) {
        counterEl = document.createElement("div");
        counterEl.className = "tabulator-page-counter";
        footerEl.prepend(counterEl);
    }

    const pageSize = Number(typeof table.getPageSize === "function" ? table.getPageSize() : 0) || 0;
    const page = Number(typeof table.getPage === "function" ? table.getPage() : 1) || 1;
    const visibleRows = Number(typeof table.getDataCount === "function" ? table.getDataCount("active") : 0) || 0;

    if (totalRows > 0) {
        const startRow = ((page - 1) * pageSize) + 1;
        const fallbackRows = Math.min(pageSize || totalRows, Math.max(totalRows - ((page - 1) * pageSize), 1));
        const effectiveRows = visibleRows > 0 ? visibleRows : fallbackRows;
        const endRow = Math.min(totalRows, startRow + Math.max(effectiveRows - 1, 0));
        counterEl.textContent = `Showing ${startRow}-${endRow} of ${totalRows} ${pluralize(totalRows, singular)}`;
    } else {
        counterEl.textContent = `Showing 0 of 0 ${pluralize(0, singular)}`;
    }
};

const buildCheckedPill = (checked) => {
    return checked == 1
        ? '<span class="ep-doc-pill ep-doc-pill--yes">Yes</span>'
        : '<span class="ep-doc-pill ep-doc-pill--no">No</span>';
};

const buildCreatedByCell = (name, date) => {
    return `
        <div class="ep-doc-usercell">
            <div class="ep-doc-usercell__name">${name}</div>
            <div class="ep-doc-usercell__meta">${date}</div>
        </div>
    `;
};

const buildDocumentActions = (data) => {
    const actions = [];

    if (data.document_id > 0 && data.url == 1) {
        actions.push(`
            <a data-id="${data.document_id}" href="javascript:void(0);" class="downloadDoc ep-doc-action-btn ep-doc-action-btn--download" title="Download">
                <i data-lucide="download" class="w-4 h-4"></i>
            </a>
        `);
    }

    if (data.deleted_at == null) {
        actions.push(`
            <button data-id="${data.id}" class="delete_btn ep-doc-action-btn ep-doc-action-btn--danger" title="Delete">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `);
    } else {
        actions.push(`
            <button data-id="${data.id}" class="restore_btn ep-doc-action-btn ep-doc-action-btn--restore" title="Restore">
                <i data-lucide="rotate-cw" class="w-4 h-4"></i>
            </button>
        `);
    }

    return `<div class="ep-doc-action-group">${actions.join("")}</div>`;
};

const renderFileChip = (inputId, targetId) => {
    const fileInput = document.getElementById(inputId);
    const target = document.getElementById(targetId);
    if (!target) return false;

    const file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;

    if (file) {
        target.innerHTML = ""
            + '<i data-lucide="file-text" class="w-4 h-4 ep-doc-file-chip__icon"></i>'
            + `<span class="ep-doc-file-chip__name">${file.name}</span>`
            + `<button type="button" class="ep-doc-file-chip__remove" data-input="${inputId}" title="Remove"><i data-lucide="x" class="w-3 h-3"></i></button>`;
        target.style.display = "inline-flex";
        renderLucideIcons();
    } else {
        target.innerHTML = "";
        target.style.display = "none";
    }

    return false;
};

const clearUploadErrors = () => {
    $("#addAppraisalDocForm .acc__input-error").html("");
    $("#addAppraisalDocForm .border-danger").removeClass("border-danger");
    $("#addAppraisalDocForm .ep-appraisal-upload-zone").removeClass("border-danger");
};

const applyUploadError = (key, message) => {
    $(`#addAppraisalDocForm .error-${key}`).html(message);

    if (key === "file") {
        $("#addAppraisalDocForm .ep-appraisal-upload-zone").addClass("border-danger");
    } else {
        $(`#addAppraisalDocForm [name="${key}"]`).addClass("border-danger");
    }
};

var employeeAppraisalDocListTable = (function () {
    let tableContent = null;
    let resizeBound = false;
    let currentTotalRows = 0;

    const _tableGen = function () {
        const employeeId = $("#employeeAppraisalDocListTable").attr("data-employee") !== "" ? $("#employeeAppraisalDocListTable").attr("data-employee") : "0";
        const appraisalId = $("#employeeAppraisalDocListTable").attr("data-appraisal") !== "" ? $("#employeeAppraisalDocListTable").attr("data-appraisal") : "0";
        const queryStr = $("#query-APD").val() !== "" ? $("#query-APD").val() : "";
        const status = $("#status-APD").val() !== "" ? $("#status-APD").val() : "1";

        if (tableContent && typeof tableContent.destroy === "function") {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#employeeAppraisalDocListTable", {
            ajaxURL: route("employee.appraisal.documents.list"),
            ajaxParams: { employee_id: employeeId, appraisal_id: appraisalId, queryStr: queryStr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching appraisal documents found",
            ajaxResponse(url, params, response) {
                currentTotalRows = Number(response.total_rows || 0);
                return response;
            },
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: 74,
                },
                {
                    title: "Name",
                    field: "display_file_name",
                    headerHozAlign: "left",
                    widthGrow: 1.6,
                },
                {
                    title: "Checked",
                    field: "hard_copy_check",
                    headerHozAlign: "left",
                    width: 128,
                    formatter(cell) {
                        return buildCheckedPill(cell.getData().hard_copy_check);
                    }
                },
                {
                    title: "Uploaded By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: 220,
                    formatter(cell) {
                        return buildCreatedByCell(cell.getData().created_by, cell.getData().created_at);
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 120,
                    download: false,
                    formatter(cell) {
                        return buildDocumentActions(cell.getData());
                    },
                },
            ],
            renderComplete() {
                renderLucideIcons();
                updateTableFooterMeta(this, currentTotalRows, "document");
                updateSectionSummary("#employeeAppraisalDocumentSummary", currentTotalRows, "document", "Upload, manage and archive appraisal documents.");
            }
        });

        if (!resizeBound) {
            window.addEventListener("resize", () => {
                if (tableContent) {
                    tableContent.redraw();
                    renderLucideIcons();
                }
            });
            resizeBound = true;
        }

        $("#tabulator-export-csv-APD").off("click").on("click", function () {
            tableContent.download("csv", "employee-appraisal-documents.csv");
        });

        $("#tabulator-export-xlsx-APD").off("click").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "employee-appraisal-documents.xlsx", {
                sheetName: "Employee Appraisal Documents",
            });
        });

        $("#tabulator-print-APD").off("click").on("click", function () {
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
    renderLucideIcons();

    if ($("#employeeAppraisalDocListTable").length) {
        employeeAppraisalDocListTable.init();

        $("#tabulator-html-filter-go-APD").on("click", function () {
            employeeAppraisalDocListTable.init();
        });

        $("#tabulator-html-filter-reset-APD").on("click", function () {
            $("#query-APD").val("");
            $("#status-APD").val("1");
            employeeAppraisalDocListTable.init();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addAppraisalDocModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAppraisalDocModal"));

    const showSuccessModal = (title, description, action = "DISMISS") => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(description);
        $("#successModal .successCloser").attr("data-action", action);
        successModal.show();
    };

    const showWarningModal = (title, description, action = "DISMISS") => {
        $("#warningModal .warningModalTitle").html(title);
        $("#warningModal .warningModalDesc").html(description);
        $("#warningModal .warningCloser").attr("data-action", action);
        warningModal.show();
    };

    const showConfirmModal = (recordId, status, description) => {
        $("#confirmModal .confModTitle").html("Are you sure?");
        $("#confirmModal .confModDesc").html(description);
        $("#confirmModal .agreeWith").attr("data-recordid", recordId);
        $("#confirmModal .agreeWith").attr("data-status", status);
        confirmModal.show();
    };

    const resetUploadForm = () => {
        const form = document.getElementById("addAppraisalDocForm");
        if (form) {
            form.reset();
        }

        clearUploadErrors();
        $("#addAppraisalDocForm input[name='hard_copy_check']").val("0");
        $("#addAppraisalDocForm .ep-appraisal-upload-zone").removeClass("border-danger");
        $("#appraisalDocumentFileName").html("").hide();
        document.querySelector("#uploadAppraisalDocBtn").removeAttribute("disabled");
        document.querySelector("#uploadAppraisalDocBtn svg").style.cssText = "display: none;";
    };

    const addAppraisalDocModalEl = document.getElementById("addAppraisalDocModal");
    addAppraisalDocModalEl.addEventListener("hide.tw.modal", function () {
        resetUploadForm();
    });

    const confirmModalEl = document.getElementById("confirmModal");
    confirmModalEl.addEventListener("hide.tw.modal", function () {
        $("#confirmModal .confModDesc").html("");
        $("#confirmModal .agreeWith").attr("data-recordid", "0");
        $("#confirmModal .agreeWith").attr("data-status", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#confirmModal .disAgreeWith").on("click", function (e) {
        e.preventDefault();
        confirmModal.hide();
    });

    $("#successModal .successCloser").on("click", function (e) {
        e.preventDefault();
        successModal.hide();
    });

    $("#warningModal .warningCloser").on("click", function (e) {
        e.preventDefault();
        warningModal.hide();
    });

    $("#addAppraisalDocForm").on("change", "#appraisalDocumentFile", function () {
        $("#addAppraisalDocForm .ep-appraisal-upload-zone").removeClass("border-danger");
        $("#addAppraisalDocForm .error-file").html("");
        renderFileChip("appraisalDocumentFile", "appraisalDocumentFileName");
    });

    $(document).on("click", ".ep-doc-file-chip__remove", function (e) {
        e.preventDefault();
        const inputId = $(this).attr("data-input");
        const fileInput = document.getElementById(inputId);
        if (fileInput) {
            fileInput.value = "";
        }
        $(this).closest(".ep-doc-file-chip").html("").hide();
    });

    $("#addAppraisalDocForm").on("submit", function (e) {
        e.preventDefault();

        clearUploadErrors();

        const form = document.getElementById("addAppraisalDocForm");
        const fileInput = document.getElementById("appraisalDocumentFile");
        const selectedFile = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        const displayName = ($("#addAppraisalDocForm input[name='display_file_name']").val() || "").trim();
        const hardCopyCheck = $('#addAppraisalDocForm input[name="hard_copy_check_status"]:checked').val();
        let hasError = false;

        if (!displayName) {
            applyUploadError("display_file_name", "Document name is required.");
            hasError = true;
        }

        if (!selectedFile) {
            applyUploadError("file", "Please choose a document to upload.");
            hasError = true;
        } else if (selectedFile.size > (5 * 1024 * 1024)) {
            applyUploadError("file", "The document must be 5MB or smaller.");
            hasError = true;
        }

        if (typeof hardCopyCheck === "undefined") {
            applyUploadError("hard_copy_check", "Please select the hard copy check status.");
            hasError = true;
        }

        if (hasError) {
            return;
        }

        document.querySelector("#uploadAppraisalDocBtn").setAttribute("disabled", "disabled");
        document.querySelector("#uploadAppraisalDocBtn svg").style.cssText = "display: inline-block;";

        $("#addAppraisalDocForm input[name='hard_copy_check']").val(hardCopyCheck);

        const formData = new FormData(form);

        axios({
            method: "post",
            url: route("employee.appraisal.upload.documents"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            document.querySelector("#uploadAppraisalDocBtn").removeAttribute("disabled");
            document.querySelector("#uploadAppraisalDocBtn svg").style.cssText = "display: none;";

            if (response.status === 200) {
                addAppraisalDocModal.hide();
                employeeAppraisalDocListTable.init();
                showSuccessModal("Congratulations!", "Appraisal document successfully uploaded.");
            }
        }).catch(error => {
            document.querySelector("#uploadAppraisalDocBtn").removeAttribute("disabled");
            document.querySelector("#uploadAppraisalDocBtn svg").style.cssText = "display: none;";

            if (error.response && error.response.status === 422 && error.response.data.errors) {
                Object.entries(error.response.data.errors).forEach(([key, value]) => {
                    applyUploadError(key, Array.isArray(value) ? value[0] : value);
                });
            } else {
                showWarningModal("Error Found!", "Something went wrong. Please try again.");
            }
        });
    });

    $("#employeeAppraisalDocListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        showConfirmModal($(this).attr("data-id"), "DELETEDOC", "Want to delete this document from the appraisal list? Please click agree to continue.");
    });

    $("#employeeAppraisalDocListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        showConfirmModal($(this).attr("data-id"), "RESTOREDOC", "Want to restore this document from the archive? Please click agree to continue.");
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();

        const $agreeBtn = $(this);
        const recordId = $agreeBtn.attr("data-recordid");
        const action = $agreeBtn.attr("data-status");

        if (action !== "DELETEDOC" && action !== "RESTOREDOC") {
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        if (action === "DELETEDOC") {
            axios({
                method: "delete",
                url: route("employee.appraisal.document.destory"),
                data: { recordid: recordId },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeAppraisalDocListTable.init();
                    showSuccessModal("Done!", "Appraisal document successfully deleted.");
                }
            }).catch(error => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }

        if (action === "RESTOREDOC") {
            axios({
                method: "post",
                url: route("employee.appraisal.document.restore"),
                data: { recordid: recordId },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeAppraisalDocListTable.init();
                    showSuccessModal("Done!", "Appraisal document successfully restored.");
                }
            }).catch(error => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });

    $("#employeeAppraisalDocListTable").on("click", ".downloadDoc", function (e) {
        e.preventDefault();
        const $theLink = $(this);
        const rowId = $theLink.attr("data-id");

        $theLink.css({ opacity: ".6", cursor: "not-allowed" });

        axios({
            method: "post",
            url: route("employee.documents.download.url"),
            data: { row_id: rowId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            if (response.status === 200) {
                const res = response.data.res;
                $theLink.css({ opacity: "1", cursor: "pointer" });

                if (res !== "") {
                    window.open(res, "_blank");
                }
            }
        }).catch(error => {
            if (error.response) {
                $theLink.css({ opacity: "1", cursor: "pointer" });
                console.log(error);
            }
        });
    });
})();
