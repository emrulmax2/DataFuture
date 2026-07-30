import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";

("use strict");

const escapeHtml = (value) => {
    const div = document.createElement("div");
    div.textContent = value == null ? "" : String(value);
    return div.innerHTML;
};

const avatarPalette = ["#6d4bb0", "#0d7a76", "#2f6ea5", "#1e6b4e", "#9f2945", "#c65a2a"];

const initials = (value) => {
    const parts = String(value || "Unknown")
        .replace(/^Mr\s+|^Mrs\s+|^Miss\s+|^Ms\s+/i, "")
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (parts.length === 0) return "UN";

    return `${parts[0][0] || ""}${(parts[1] || parts[0])[0] || ""}`.toUpperCase();
};

const avatarColor = (value) => {
    const source = String(value || "agent document");
    let hash = 0;

    for (let index = 0; index < source.length; index += 1) {
        hash = source.charCodeAt(index) + ((hash << 5) - hash);
    }

    return avatarPalette[Math.abs(hash) % avatarPalette.length];
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

const getDropdown = (selector) => {
    const element = document.querySelector(selector);
    return element ? tailwind.Dropdown.getOrCreateInstance(element) : null;
};

const setButtonBusy = ($button, busy) => {
    const $loader = $button.find("svg").not("[data-lucide]").last();

    $button.prop("disabled", busy);
    $loader.css("display", busy ? "inline-block" : "none");
};

const setModalCopy = (selector, titleClass, descClass, title, description) => {
    $(`${selector} ${titleClass}`).html(title);
    $(`${selector} ${descClass}`).html(description);
    refreshIcons();
};

const showInlineUploadError = (message) => {
    const $modalContent = $("#uploadEmployeeDocumentModal .modal-content");

    $modalContent.find(".uploadError").remove();
    $modalContent.prepend(`
        <div class="agm-document-upload-alert uploadError" role="alert">
            <i data-lucide="alert-octagon"></i>
            <span>${escapeHtml(message)}</span>
        </div>
    `);
    refreshIcons();

    window.setTimeout(() => {
        $modalContent.find(".uploadError").remove();
    }, 2400);
};

var employeeDocumentListTable = (function () {
    let tableContent;

    const $table = $("#employeeDocumentListTable");
    const listUrl = route("agent-user.documents.uploads.list");

    const getParams = () => ({
        employeeId: $table.attr("data-employee") || "0",
        queryStr: ($("#query-ED").val() || "").trim(),
        status: $("#status-ED").val() || "1",
        size: true,
    });

    const renderFileCell = (row) => {
        const documentName = row.display_file_name || "Unknown";
        const fileName = row.url || "";
        const iconTone = Number(row.hard_copy_check) === 1 ? "is-danger" : "is-blue";

        return `
            <div class="agm-doc-file-cell">
                <span class="agm-doc-file-icon ${iconTone}">
                    <i data-lucide="file-text"></i>
                </span>
                <div class="agm-doc-file-copy">
                    <strong>${escapeHtml(documentName)}</strong>
                    ${fileName ? `<small>${escapeHtml(fileName)}</small>` : ""}
                </div>
            </div>
        `;
    };

    const renderCheckedCell = (value) => {
        if (Number(value) === 1) {
            return `
                <span class="agm-doc-check is-checked">
                    <i data-lucide="check"></i>
                    Checked
                </span>
            `;
        }

        return `<span class="agm-doc-check is-pending">Pending</span>`;
    };

    const renderUploadedBy = (row) => {
        const uploadedBy = row.created_by || "Unknown";
        const uploadedAt = row.created_at || "";

        return `
            <div class="agm-doc-uploader">
                <span class="agm-doc-uploader__avatar" style="background:${avatarColor(uploadedBy)}">${escapeHtml(initials(uploadedBy))}</span>
                <div class="agm-doc-uploader__copy">
                    <strong>${escapeHtml(uploadedBy)}</strong>
                    ${uploadedAt ? `<small>${escapeHtml(uploadedAt)}</small>` : ""}
                </div>
            </div>
        `;
    };

    const renderActions = (row) => {
        const id = escapeHtml(row.id);
        const canDownload = row.url != null && String(row.url) !== "";

        if (row.deleted_at != null) {
            return `
                <span class="agm-doc-actions">
                    <button data-id="${id}" type="button" class="restore_btn agm-agent-action agm-agent-action--view" title="Restore document" aria-label="Restore document">
                        <i data-lucide="rotate-cw"></i>
                    </button>
                </span>
            `;
        }

        return `
            <span class="agm-doc-actions">
                ${
                    canDownload
                        ? `<a data-id="${id}" href="javascript:void(0);" class="downloadDoc agm-agent-action agm-agent-action--download" title="Download document" aria-label="Download document">
                                <i data-lucide="download"></i>
                            </a>`
                        : ""
                }
                <button data-id="${id}" type="button" class="delete_btn agm-agent-action agm-agent-action--delete" title="Delete document" aria-label="Delete document">
                    <i data-lucide="trash-2"></i>
                </button>
            </span>
        `;
    };

    const _tableGen = function () {
        tableContent = new Tabulator("#employeeDocumentListTable", {
            ajaxURL: listUrl,
            ajaxParams: getParams(),
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No documents found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 100,
                    minWidth: 90,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<span class="agm-agent-id">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Name",
                    field: "display_file_name",
                    minWidth: 340,
                    widthGrow: 2.3,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return renderFileCell(cell.getData());
                    },
                },
                {
                    title: "Checked",
                    field: "hard_copy_check",
                    minWidth: 170,
                    widthGrow: 0.8,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return renderCheckedCell(cell.getValue());
                    },
                },
                {
                    title: "Uploaded By",
                    field: "created_by",
                    minWidth: 260,
                    widthGrow: 1.4,
                    headerHozAlign: "left",
                    formatter(cell) {
                        return renderUploadedBy(cell.getData());
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 124,
                    minWidth: 124,
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
    if (!$("#employeeDocumentListTable").length) return;

    employeeDocumentListTable.init();

    const successModal = getModal("#successModal");
    const confirmModal = getModal("#confirmModal");
    const warningModal = getModal("#warningModal");
    const uploadsDropdown = getDropdown("#uploadsDropdown");
    const uploadEmployeeDocumentModal = getModal("#uploadEmployeeDocumentModal");

    let activeDocumentLabel = "Selected document type";
    let dropzoneHasError = false;
    let uploadDropzone = null;

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
            .attr("data-status", action);
        refreshIcons();
        confirmModal?.show();
    };

    $("#tabulator-html-filter-go-ED").on("click", function () {
        employeeDocumentListTable.reload();
    });

    $("#tabulator-html-filter-reset-ED").on("click", function () {
        $("#query-ED").val("");
        $("#status-ED").val("1");
        employeeDocumentListTable.reload();
    });

    $("#tabulator-export-csv-ED").on("click", function () {
        employeeDocumentListTable.download("csv", "agent-documents.csv");
    });

    $("#tabulator-export-xlsx-ED").on("click", function () {
        window.XLSX = xlsx;
        employeeDocumentListTable.download("xlsx", "agent-documents.xlsx", {
            sheetName: "Agent Documents",
        });
    });

    $("#tabulator-print-ED").on("click", function () {
        employeeDocumentListTable.print();
    });

    document.getElementById("uploadEmployeeDocumentModal")?.addEventListener("hide.tw.modal", function () {
        const $modal = $("#uploadEmployeeDocumentModal");

        activeDocumentLabel = "Selected document type";
        $modal.find("input[name='display_file_name']").val("");
        $modal.find("input[name='document_setting_id']").val("0");
        $modal.find("input[name='hard_copy_check']").val("0");
        $modal.find("input[name='hard_copy_check_status'][value='0']").prop("checked", true);
        $modal.find("input[name='doc_name']").val("");
        $("#documentNameDisplay").text(activeDocumentLabel);
        setButtonBusy($("#uploadEmpDocBtn"), false);
        $(".uploadError").remove();

        if (uploadDropzone) {
            uploadDropzone.removeAllFiles(true);
        }
    });

    document.getElementById("confirmModal")?.addEventListener("hide.tw.modal", function () {
        $("#confirmModal .confModDesc").html("");
        $("#confirmModal .agreeWith").attr("data-recordid", "0").attr("data-status", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#closeUploadsDropdown").on("click", function (event) {
        event.preventDefault();
        uploadsDropdown?.hide();
    });

    $("#confirmModal .disAgreeWith").on("click", function (event) {
        event.preventDefault();
        confirmModal?.hide();
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

    $("#uploadEmployeeDocumentModal [name='doc_name']").on("input", function () {
        const displayName = ($(this).val() || "").trim();
        const composedName = displayName ? `${activeDocumentLabel} - ${displayName}` : activeDocumentLabel;

        $("#uploadEmployeeDocumentModal [name='display_file_name']").val(displayName);
        $("#documentNameDisplay").text(composedName);
    });

    if ($("#uploadDocumentForm").length > 0) {
        Dropzone.autoDiscover = false;
        Dropzone.options.uploadDocumentForm = {
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 20,
            parallelUploads: 10,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
        };

        uploadDropzone = new Dropzone("#uploadDocumentForm", {
            accept: (file, done) => done(),
        });

        uploadDropzone.on("maxfilesexceeded", (file) => {
            showInlineUploadError("You cannot upload more than 10 files at a time.");
            uploadDropzone.removeFile(file);
        });

        uploadDropzone.on("error", function () {
            dropzoneHasError = true;
        });

        uploadDropzone.on("success", function (file) {
            return file.previewElement.classList.add("dz-success");
        });

        uploadDropzone.on("queuecomplete", function () {
            setButtonBusy($("#uploadEmpDocBtn"), false);
            uploadEmployeeDocumentModal?.hide();

            if (!dropzoneHasError) {
                showSuccess("Congratulations!", "Agent document successfully uploaded.", "RELOAD", false);
                window.setTimeout(() => window.location.reload(), 1800);
            } else {
                showWarning("Error Found!", "Something went wrong. Please try later or contact administrator.");
            }
        });

        $("#uploadEmpDocBtn").on("click", function (event) {
            event.preventDefault();
            const $button = $(this);

            if (uploadDropzone.getQueuedFiles().length === 0) {
                showInlineUploadError("Please choose at least one file to upload.");
                return;
            }

            if ($("#uploadEmployeeDocumentModal [name='hard_copy_check_status']:checked").length <= 0) {
                showInlineUploadError("Please select the hard copy check status.");
                return;
            }

            dropzoneHasError = false;
            setButtonBusy($button, true);

            const hardCopyChecked = $("#uploadEmployeeDocumentModal [name='hard_copy_check_status']:checked").val();
            $("#uploadEmployeeDocumentModal input[name='hard_copy_check']").val(hardCopyChecked);
            uploadDropzone.processQueue();
        });
    }

    $("#employeeDocumentUploaders").on("click", function (event) {
        event.preventDefault();

        const $selectedDocument = $(".employee_doc_ids:checked");

        if ($selectedDocument.length <= 0) {
            showWarning("Oops!", "Please select a document type from the list first.");
            return;
        }

        activeDocumentLabel = ($selectedDocument.attr("data-label") || "Selected document type").trim();

        $("#uploadEmployeeDocumentModal input[name='document_setting_id']").val($selectedDocument.val());
        $("#uploadEmployeeDocumentModal input[name='display_file_name']").val("");
        $("#uploadEmployeeDocumentModal input[name='doc_name']").val("");
        $("#documentNameDisplay").text(activeDocumentLabel);

        uploadEmployeeDocumentModal?.show();
        uploadsDropdown?.hide();
        $(".employee_doc_ids").prop("checked", false);
    });

    $("#employeeDocumentListTable").on("click", ".delete_btn", function (event) {
        event.preventDefault();
        showConfirm($(this).attr("data-id"), "DELETEDOC", "Do you really want to delete this document? This process cannot be undone.");
    });

    $("#employeeDocumentListTable").on("click", ".restore_btn", function (event) {
        event.preventDefault();
        showConfirm($(this).attr("data-id"), "RESTOREDOC", "Do you really want to restore this document?");
    });

    $("#confirmModal .agreeWith").on("click", function (event) {
        event.preventDefault();

        const $agreeButton = $(this);
        const recordId = $agreeButton.attr("data-recordid");
        const action = $agreeButton.attr("data-status");
        const employee = $agreeButton.attr("data-employee");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action === "DELETEDOC") {
            axios({
                method: "delete",
                url: route("agent-user.documents.destory.uploads"),
                data: { employee, recordid: recordId },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            })
                .then((response) => {
                    $("#confirmModal button").removeAttr("disabled");

                    if (response.status === 200) {
                        confirmModal?.hide();
                        employeeDocumentListTable.reload();
                        showSuccess("Done!", "Agent document successfully deleted.");
                    }
                })
                .catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
        } else if (action === "RESTOREDOC") {
            axios({
                method: "post",
                url: route("agent-user.documents.restore.uploads"),
                data: { employee, recordid: recordId },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            })
                .then((response) => {
                    $("#confirmModal button").removeAttr("disabled");

                    if (response.status === 200) {
                        confirmModal?.hide();
                        employeeDocumentListTable.reload();
                        showSuccess("Done!", "Agent document successfully restored.");
                    }
                })
                .catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
        } else {
            confirmModal?.hide();
        }
    });

    $("#employeeDocumentListTable").on("click", ".downloadDoc", function (event) {
        event.preventDefault();

        const $link = $(this);
        const rowId = $link.attr("data-id");

        $link.css({ opacity: ".6", cursor: "not-allowed" });

        axios({
            method: "post",
            url: route("agent-user.documents.download.url"),
            data: { row_id: rowId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        })
            .then((response) => {
                $link.css({ opacity: "1", cursor: "pointer" });

                if (response.status === 200 && response.data.res) {
                    window.open(response.data.res, "_blank");
                }
            })
            .catch((error) => {
                $link.css({ opacity: "1", cursor: "pointer" });
                console.log(error);
            });
    });
})();
