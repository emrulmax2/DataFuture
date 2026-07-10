import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";
import TomSelect from "tom-select";
import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";

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

const buildActionButtons = (cellData, includeNote = false) => {
    const actions = [];

    if (cellData.url !== "") {
        const noteAttr = includeNote ? ` data-note="${cellData.hasNote}"` : "";
        actions.push(`
            <a${noteAttr} data-id="${cellData.id}" target="_blank" href="javascript:void(0);" class="downloadDoc ep-doc-action-btn ep-doc-action-btn--download" title="Download">
                <i data-lucide="download" class="w-4 h-4"></i>
            </a>
        `);
    }

    if (cellData.deleted_at == null) {
        actions.push(`
            <button data-id="${cellData.id}" class="delete_btn ep-doc-action-btn ep-doc-action-btn--danger" title="Delete">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `);
    } else {
        actions.push(`
            <button data-id="${cellData.id}" class="restore_btn ep-doc-action-btn ep-doc-action-btn--restore" title="Restore">
                <i data-lucide="rotate-cw" class="w-4 h-4"></i>
            </button>
        `);
    }

    return `<div class="ep-doc-action-group">${actions.join("")}</div>`;
};

var employeeDocumentListTable = (function () {
    let tableContent = null;
    let resizeBound = false;
    let currentTotalRows = 0;

    var _tableGen = function () {
        let employeeId = $("#employeeDocumentListTable").attr("data-employee") !== "" ? $("#employeeDocumentListTable").attr("data-employee") : "0";
        let queryStr = $("#query-ED").val() !== "" ? $("#query-ED").val() : "";
        let status = $("#status-ED").val() !== "" ? $("#status-ED").val() : "1";

        if (tableContent && typeof tableContent.destroy === "function") {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#employeeDocumentListTable", {
            ajaxURL: route("employee.documents.uploads.list"),
            ajaxParams: { employeeId: employeeId, queryStr: queryStr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching documents found",
            ajaxResponse(url, params, response) {
                currentTotalRows = Number(response.total_rows || 0);
                return response;
            },
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: 92,
                },
                {
                    title: "Name",
                    field: "display_file_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Checked",
                    field: "hard_copy_check",
                    headerHozAlign: "left",
                    width: 120,
                    formatter(cell) {
                        return buildCheckedPill(cell.getData().hard_copy_check);
                    }
                },
                {
                    title: "Uploaded By",
                    field: "created_by",
                    headerHozAlign: "left",
                    widthGrow: 1.35,
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
                        return buildActionButtons(cell.getData(), true);
                    },
                },
            ],
            renderComplete() {
                renderLucideIcons();
                updateTableFooterMeta(this, currentTotalRows, "document");
                updateSectionSummary("#employeeDocumentSummary", currentTotalRows, "document", "Upload, manage and archive employee documents.");
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

        $("#tabulator-export-csv-ED").off("click").on("click", function () {
            tableContent.download("csv", "employee-documents.csv");
        });

        $("#tabulator-export-json-ED").off("click").on("click", function () {
            tableContent.download("json", "employee-documents.json");
        });

        $("#tabulator-export-xlsx-ED").off("click").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "employee-documents.xlsx", {
                sheetName: "Employee Upload Details",
            });
        });

        $("#tabulator-export-html-ED").off("click").on("click", function () {
            tableContent.download("html", "employee-documents.html", {
                style: true,
            });
        });

        $("#tabulator-print-ED").off("click").on("click", function () {
            tableContent.print();
        });
    };

    return {
        init: function () {
            _tableGen();
        },
    };
})();

var employeeCommunicationDocumentListTable = (function () {
    let tableContent = null;
    let resizeBound = false;
    let currentTotalRows = 0;

    var _tableGen = function () {
        let employeeId = $("#employeeCommunicationDocumentListTable").attr("data-employee") !== "" ? $("#employeeCommunicationDocumentListTable").attr("data-employee") : "0";
        let queryStr = $("#query-EDC").val() !== "" ? $("#query-EDC").val() : "";
        let status = $("#status-EDC").val() !== "" ? $("#status-EDC").val() : "1";

        if (tableContent && typeof tableContent.destroy === "function") {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#employeeCommunicationDocumentListTable", {
            ajaxURL: route("employee.documents.communication.list"),
            ajaxParams: { employeeId: employeeId, queryStr: queryStr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching communications found",
            ajaxResponse(url, params, response) {
                currentTotalRows = Number(response.total_rows || 0);
                return response;
            },
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: 92,
                },
                {
                    title: "Name",
                    field: "display_file_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Checked",
                    field: "hard_copy_check",
                    headerHozAlign: "left",
                    width: 120,
                    formatter(cell) {
                        return buildCheckedPill(cell.getData().hard_copy_check);
                    }
                },
                {
                    title: "Uploaded By",
                    field: "created_by",
                    headerHozAlign: "left",
                    widthGrow: 1.35,
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
                        return buildActionButtons(cell.getData(), false);
                    },
                },
            ],
            renderComplete() {
                renderLucideIcons();
                updateTableFooterMeta(this, currentTotalRows, "communication");
                updateSectionSummary("#employeeCommunicationSummary", currentTotalRows, "communication", "Track HR emails, attachments and archived communication records.");
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

        $("#tabulator-export-csv-EDC").off("click").on("click", function () {
            tableContent.download("csv", "employee-communications.csv");
        });

        $("#tabulator-export-json-EDC").off("click").on("click", function () {
            tableContent.download("json", "employee-communications.json");
        });

        $("#tabulator-export-xlsx-EDC").off("click").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "employee-communications.xlsx", {
                sheetName: "Employee Communication Details",
            });
        });

        $("#tabulator-export-html-EDC").off("click").on("click", function () {
            tableContent.download("html", "employee-communications.html", {
                style: true,
            });
        });

        $("#tabulator-print-EDC").off("click").on("click", function () {
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

    if ($("#employeeDocumentListTable").length) {
        employeeDocumentListTable.init();

        function filterHTMLFormUP() {
            employeeDocumentListTable.init();
        }

        $("#tabulator-html-filter-go-ED").on("click", function () {
            filterHTMLFormUP();
        });

        $("#tabulator-html-filter-reset-ED").on("click", function () {
            $("#query-ED").val("");
            $("#status-ED").val("1");
            filterHTMLFormUP();
        });
    }

    if ($("#employeeCommunicationDocumentListTable").length) {
        employeeCommunicationDocumentListTable.init();

        function filterHTMLFormEDC() {
            employeeCommunicationDocumentListTable.init();
        }

        $("#tabulator-html-filter-go-EDC").on("click", function () {
            filterHTMLFormEDC();
        });

        $("#tabulator-html-filter-reset-EDC").on("click", function () {
            $("#query-EDC").val("");
            $("#status-EDC").val("1");
            filterHTMLFormEDC();
        });
    }

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: "Search Here...",
        create: false,
        allowEmptyOption: true,
        dropdownParent: "body",
        dropdownClass: "employee-profile-tom-dropdown ep-doc-tom-dropdown",
        copyClassesToDropdown: false,
        onDelete: function (values) {
            return confirm(values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' + values[0] + '"?');
        },
    };

    let emailTemplateId = $("#email_template_id").length ? new TomSelect("#email_template_id", tomOptions) : null;

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const uploadsDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#uploadsDropdown"));
    const uploadEmployeeDocumentModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadEmployeeDocumentModal"));
    const addCommunicationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCommunicationModal"));
    const uploadDocTypeDisplay = $("#documentNameDisplay");
    const uploadDocNameInput = $('#uploadEmployeeDocumentModal [name="doc_name"]');
    const uploadDisplayNameInput = $('#uploadEmployeeDocumentModal [name="display_file_name"]');

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

    let emailBody;
    if ($("#email_body").length > 0) {
        const el = document.getElementById("email_body");
        ClassicEditor.create(el).then((editor) => {
            emailBody = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    $("#addCommunicationForm").on("change", "#editComDocument", function () {
        showFileName("editComDocument", "editComDocumentName");
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        namePreview.innerText = file ? file.name : "No file selected";
        return false;
    }

    const addCommunicationModalEl = document.getElementById("addCommunicationModal");
    addCommunicationModalEl.addEventListener("hide.tw.modal", function () {
        $("#addCommunicationModal .acc__input-error").html("");
        $("#addCommunicationModal .is-danger").removeClass("is-danger");
        $('#addCommunicationModal input[name="document_name"]').val("");
        $('#addCommunicationModal input[name="hard_copy_check_status"][value="0"]').prop("checked", true);
        $('#addCommunicationModal input[name="document"]').val("");
        $("#addCommunicationModal #editComDocumentName").html("No file selected");
        if (emailBody) {
            emailBody.setData($("#addCommunicationModal .sendEmailContent").attr("data-content"));
        }
        if (emailTemplateId) {
            emailTemplateId.clear(true);
        }
    });

    const uploadEmployeeDocumentModalEl = document.getElementById("uploadEmployeeDocumentModal");
    uploadEmployeeDocumentModalEl.addEventListener("hide.tw.modal", function () {
        uploadDisplayNameInput.val("");
        $('#uploadEmployeeDocumentModal input[name="document_setting_id"]').val("0");
        $('#uploadEmployeeDocumentModal input[name="hard_copy_check"]').val("0");
        uploadDocNameInput.val("");
        uploadDocTypeDisplay.text("Selected document type");
        $('#uploadEmployeeDocumentModal input[name="hard_copy_check_status"][value="0"]').prop("checked", true);
        document.querySelector("#uploadEmpDocBtn").removeAttribute("disabled");
        document.querySelector("#uploadEmpDocBtn svg").style.cssText = "display: none;";

        const dropzoneInstance = Dropzone.instances.find((instance) => instance.element && instance.element.id === "uploadDocumentForm");
        if (dropzoneInstance) {
            dropzoneInstance.removeAllFiles(true);
        }
    });

    const confirmModalEl = document.getElementById("confirmModal");
    confirmModalEl.addEventListener("hide.tw.modal", function () {
        $("#confirmModal .confModDesc").html("");
        $("#confirmModal .agreeWith").attr("data-recordid", "0");
        $("#confirmModal .agreeWith").attr("data-status", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#closeUploadsDropdown").on("click", function (e) {
        e.preventDefault();
        uploadsDropdown.hide();
    });

    $("#confirmModal .disAgreeWith").on("click", function (e) {
        e.preventDefault();
        confirmModal.hide();
    });

    $("#successModal .successCloser").on("click", function (e) {
        e.preventDefault();
        if ($(this).attr("data-action") === "RELOAD") {
            successModal.hide();
            window.location.reload();
        } else {
            successModal.hide();
        }
    });

    $("#warningModal .warningCloser").on("click", function (e) {
        e.preventDefault();
        if ($(this).attr("data-action") === "RELOAD") {
            warningModal.hide();
            window.location.reload();
        } else {
            warningModal.hide();
        }
    });

    uploadDocNameInput.on("input", function () {
        uploadDisplayNameInput.val($(this).val());
    });

    if ($("#uploadDocumentForm").length > 0) {
        let dzError = false;
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

        let options = {
            accept: (file, done) => {
                done();
            },
        };

        var drzn1 = new Dropzone("#uploadDocumentForm", options);

        drzn1.on("maxfilesexceeded", (file) => {
            $("#uploadEmployeeDocumentModal .modal-content .uploadError").remove();
            $("#uploadEmployeeDocumentModal .modal-content").prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>');
            drzn1.removeFile(file);
            renderLucideIcons();
            setTimeout(function () {
                $("#uploadEmployeeDocumentModal .modal-content .uploadError").remove();
            }, 2000);
        });

        drzn1.on("error", function () {
            dzError = true;
        });

        drzn1.on("success", function (file) {
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("queuecomplete", function () {
            $("#uploadEmpDocBtn").removeAttr("disabled");
            document.querySelector("#uploadEmpDocBtn svg").style.cssText = "display: none;";

            uploadEmployeeDocumentModal.hide();
            if (!dzError) {
                showSuccessModal("Congratulations!", "Employee document successfully uploaded.", "RELOAD");

                setTimeout(function () {
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            } else {
                showWarningModal("Error Found!", "Something went wrong. Please try later or contact administrator.");
                setTimeout(function () {
                    warningModal.hide();
                }, 2000);
            }

            dzError = false;
        });

        $("#uploadEmpDocBtn").on("click", function (e) {
            e.preventDefault();
            document.querySelector("#uploadEmpDocBtn").setAttribute("disabled", "disabled");
            document.querySelector("#uploadEmpDocBtn svg").style.cssText = "display: inline-block;";

            if ($('#uploadEmployeeDocumentModal [name="hard_copy_check_status"]:checked').length > 0) {
                let hardCopyChecked = $('#uploadEmployeeDocumentModal [name="hard_copy_check_status"]:checked').val();
                $('#uploadEmployeeDocumentModal input[name="hard_copy_check"]').val(hardCopyChecked);
                drzn1.processQueue();
            } else {
                $("#uploadEmployeeDocumentModal .modal-content .uploadError").remove();
                $("#uploadEmployeeDocumentModal .modal-content").prepend('<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status.</div>');
                renderLucideIcons();

                setTimeout(function () {
                    $("#uploadEmployeeDocumentModal .modal-content .uploadError").remove();
                    document.querySelector("#uploadEmpDocBtn").removeAttribute("disabled");
                    document.querySelector("#uploadEmpDocBtn svg").style.cssText = "display: none;";
                }, 2000);
            }
        });
    }

    $("#employeeDocumentUploaders").on("click", function (e) {
        e.preventDefault();

        if ($(".employee_doc_ids:checked").length > 0) {
            uploadEmployeeDocumentModal.show();
            let documentSettingId = $(".employee_doc_ids:checked").val();
            $('#uploadEmployeeDocumentModal input[name="document_setting_id"]').val(documentSettingId);

            let selectedDocumentID = $(".employee_doc_ids:checked");
            let documentLabelText = selectedDocumentID.attr("data-label").trim();

            uploadDocTypeDisplay.text(documentLabelText);
            uploadDocNameInput.val(documentLabelText);
            uploadDisplayNameInput.val(documentLabelText);

            uploadsDropdown.hide();
            $(".employee_doc_ids").prop("checked", false);
        } else {
            showWarningModal("Oops!", "Please select a document type from the list first.");
            setTimeout(function () {
                warningModal.hide();
            }, 2000);
        }
    });

    $("#employeeDocumentListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        let uploadId = $(this).attr("data-id");
        showConfirmModal(uploadId, "DELETEDOC", "Want to delete this document from the employee list? Please click agree to continue.");
    });

    $("#employeeDocumentListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        let uploadId = $(this).attr("data-id");
        showConfirmModal(uploadId, "RESTOREDOC", "Want to restore this document from the archive? Please click agree to continue.");
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr("data-recordid");
        let action = $agreeBTN.attr("data-status");
        let employee = $agreeBTN.attr("data-employee");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action === "DELETEDOC") {
            axios({
                method: "delete",
                url: route("employee.documents.destory.uploads"),
                data: { employee: employee, recordid: recordid },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeDocumentListTable.init();
                    employeeCommunicationDocumentListTable.init();

                    showSuccessModal("Done!", "Employee uploaded document successfully deleted.", "NONE");

                    setTimeout(function () {
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                console.log(error);
            });
        } else if (action === "RESTOREDOC") {
            axios({
                method: "post",
                url: route("employee.documents.restore.uploads"),
                data: { employee: employee, recordid: recordid },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeDocumentListTable.init();
                    employeeCommunicationDocumentListTable.init();

                    showSuccessModal("Done!", "Employee document successfully restored.", "NONE");

                    setTimeout(function () {
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                console.log(error);
            });
        } else {
            confirmModal.hide();
        }
    });

    $("#employeeDocumentListTable").on("click", ".downloadDoc", function (e) {
        e.preventDefault();
        let $theLink = $(this);
        let row_id = $theLink.attr("data-id");
        let has_note = $theLink.attr("data-note") ? $theLink.attr("data-note") : 0;

        $theLink.css({ "opacity": ".6", "cursor": "not-allowed" });

        axios({
            method: "post",
            url: route("employee.documents.download.url"),
            data: { row_id: row_id, has_note: has_note },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            if (response.status === 200) {
                let res = response.data.res;
                $theLink.css({ "opacity": "1", "cursor": "pointer" });

                if (res !== "") {
                    window.open(res, "_blank");
                }
            }
        }).catch(error => {
            if (error.response) {
                $theLink.css({ "opacity": "1", "cursor": "pointer" });
                console.log("error");
            }
        });
    });

    $("#employeeCommunicationDocumentListTable").on("click", ".downloadDoc", function (e) {
        e.preventDefault();
        let $theLink = $(this);
        let row_id = $theLink.attr("data-id");

        $theLink.css({ "opacity": ".6", "cursor": "not-allowed" });

        axios({
            method: "post",
            url: route("employee.documents.download.url"),
            data: { row_id: row_id },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            if (response.status === 200) {
                let res = response.data.res;
                $theLink.css({ "opacity": "1", "cursor": "pointer" });

                if (res !== "") {
                    window.open(res, "_blank");
                }
            }
        }).catch(error => {
            if (error.response) {
                $theLink.css({ "opacity": "1", "cursor": "pointer" });
                console.log("error");
            }
        });
    });

    $("#addCommunicationForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("addCommunicationForm");

        $("#addCommunicationForm .acc__input-error").html("");
        $("#addCommunicationForm .is-danger").removeClass("is-danger");

        document.querySelector("#sendEmail").setAttribute("disabled", "disabled");
        document.querySelector("#sendEmail svg").style.cssText = "display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("email_body", emailBody ? emailBody.getData() : "");

        axios({
            method: "post",
            url: route("employee.documents.sent.mail"),
            data: form_data,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            document.querySelector("#sendEmail").removeAttribute("disabled");
            document.querySelector("#sendEmail svg").style.cssText = "display: none;";

            if (response.status === 200) {
                addCommunicationModal.hide();
                let suc = response.data.suc;

                if (Number(suc) === 1) {
                    showSuccessModal("Congratulations!", "Employee communication email successfully sent.", "NONE");
                    setTimeout(function () {
                        successModal.hide();
                    }, 2000);
                } else {
                    showWarningModal("Error Found!", "Something went wrong. Please try later or contact administrator.");
                    setTimeout(function () {
                        warningModal.hide();
                    }, 2000);
                }
            }
            employeeCommunicationDocumentListTable.init();
        }).catch(error => {
            document.querySelector("#sendEmail").removeAttribute("disabled");
            document.querySelector("#sendEmail svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status === 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addCommunicationForm .${key}`).addClass("is-danger");
                        $(`#addCommunicationForm .error-${key}`).html(val);
                    }
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#email_template_id").on("change", function () {
        let the_template_id = $(this).val();

        if (the_template_id > 0) {
            axios({
                method: "post",
                url: route("employee.documents.get.template"),
                data: { the_template_id: the_template_id },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200 && emailBody) {
                    let row = response.data.row;
                    emailBody.setData(row.description);
                }
            }).catch(error => {
                if (error.response) {
                    console.log("error");
                }
            });
        } else if (emailBody) {
            emailBody.setData($("#addCommunicationModal .sendEmailContent").attr("data-content"));
        }
    });
})();
