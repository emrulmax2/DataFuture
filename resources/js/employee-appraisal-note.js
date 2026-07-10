import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
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

const clearFormErrors = (formSelector) => {
    $(`${formSelector} .acc__input-error`).html("");
    $(`${formSelector} .border-danger`).removeClass("border-danger");
    $(`${formSelector} .document-editor`).removeClass("is-danger");
};

const applyFormErrors = (formSelector, errors) => {
    Object.entries(errors).forEach(([key, val]) => {
        const message = Array.isArray(val) ? val[0] : val;
        $(`${formSelector} .error-${key}`).html(message);

        if (key === "content") {
            $(`${formSelector} .document-editor`).addClass("is-danger");
        } else {
            $(`${formSelector} [name="${key}"]`).addClass("border-danger");
        }
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

const buildCreatedByCell = (name, date) => {
    return `
        <div class="ep-doc-usercell">
            <div class="ep-doc-usercell__name">${name}</div>
            <div class="ep-doc-usercell__meta">${date}</div>
        </div>
    `;
};

const buildNoteActions = (data) => {
    const actions = [];

    if (data.employee_document_id > 0) {
        actions.push(`
            <a data-id="${data.employee_document_id}" href="javascript:void(0);" class="downloadDoc ep-doc-action-btn ep-doc-action-btn--download" title="Download attachment">
                <i data-lucide="download" class="w-4 h-4"></i>
            </a>
        `);
    }

    if (data.deleted_at == null) {
        actions.push(`
            <button data-id="${data.id}" class="view_btn ep-doc-action-btn ep-doc-action-btn--view" title="View note">
                <i data-lucide="message-square" class="w-4 h-4"></i>
            </button>
        `);
        actions.push(`
            <button data-id="${data.id}" class="edit_btn ep-doc-action-btn ep-doc-action-btn--edit" title="Edit note">
                <i data-lucide="pencil" class="w-4 h-4"></i>
            </button>
        `);
        actions.push(`
            <button data-id="${data.id}" class="delete_btn ep-doc-action-btn ep-doc-action-btn--danger" title="Delete note">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
            </button>
        `);
    } else {
        actions.push(`
            <button data-id="${data.id}" class="restore_btn ep-doc-action-btn ep-doc-action-btn--restore" title="Restore note">
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

const editorConfig = {
    toolbar: {
        items: ["heading", "|", "bold", "italic", "underline", "strikethrough", "|", "bulletedList", "numberedList", "|", "link"],
        shouldNotGroupWhenFull: true,
    },
};

var employeeAppraisalNoteListTable = (function () {
    let tableContent = null;
    let resizeBound = false;
    let currentTotalRows = 0;

    const _tableGen = function () {
        const employeeId = $("#employeeAppraisalNoteListTable").attr("data-employee") !== "" ? $("#employeeAppraisalNoteListTable").attr("data-employee") : "0";
        const appraisalId = $("#employeeAppraisalNoteListTable").attr("data-appraisal") !== "" ? $("#employeeAppraisalNoteListTable").attr("data-appraisal") : "0";
        const queryStr = $("#query-APN").val() !== "" ? $("#query-APN").val() : "";
        const status = $("#status-APN").val() !== "" ? $("#status-APN").val() : "1";

        if (tableContent && typeof tableContent.destroy === "function") {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#employeeAppraisalNoteListTable", {
            ajaxURL: route("employee.note.list"),
            ajaxParams: { employeeId: employeeId, queryStr: queryStr, status: status, appraisalId: appraisalId },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching appraisal notes found",
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
                    title: "Opening Date",
                    field: "opening_date",
                    headerHozAlign: "left",
                    width: 160,
                },
                {
                    title: "Note",
                    field: "note",
                    headerHozAlign: "left",
                    widthGrow: 1.5,
                    formatter(cell) {
                        return `<div class="ep-doc-note-preview">${cell.getData().note}</div>`;
                    }
                },
                {
                    title: "Created By",
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
                    width: 156,
                    download: false,
                    formatter(cell) {
                        return buildNoteActions(cell.getData());
                    },
                },
            ],
            renderComplete() {
                renderLucideIcons();
                updateTableFooterMeta(this, currentTotalRows, "note");
                updateSectionSummary("#employeeAppraisalNoteSummary", currentTotalRows, "note", "Record and manage notes linked to this appraisal.");
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

        $("#tabulator-export-csv-APN").off("click").on("click", function () {
            tableContent.download("csv", "employee-appraisal-notes.csv");
        });

        $("#tabulator-export-xlsx-APN").off("click").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "employee-appraisal-notes.xlsx", {
                sheetName: "Employee Appraisal Notes",
            });
        });

        $("#tabulator-print-APN").off("click").on("click", function () {
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

    if ($("#employeeAppraisalNoteListTable").length) {
        employeeAppraisalNoteListTable.init();

        $("#tabulator-html-filter-go-APN").on("click", function () {
            employeeAppraisalNoteListTable.init();
        });

        $("#tabulator-html-filter-reset-APN").on("click", function () {
            $("#query-APN").val("");
            $("#status-APN").val("1");
            employeeAppraisalNoteListTable.init();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addAppraisalNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addAppraisalNoteModal"));
    const viewAppraisalNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewAppraisalNoteModal"));
    const editAppraisalNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAppraisalNoteModal"));

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

    let addAppraisalNoteEditor;
    if ($("#addAppraisalNoteEditor").length > 0) {
        const el = document.getElementById("addAppraisalNoteEditor");
        ClassicEditor.create(el, editorConfig).then((editor) => {
            addAppraisalNoteEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let editAppraisalNoteEditor;
    if ($("#editAppraisalNoteEditor").length > 0) {
        const el = document.getElementById("editAppraisalNoteEditor");
        ClassicEditor.create(el, editorConfig).then((editor) => {
            editAppraisalNoteEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    const addNoteModalEl = document.getElementById("addAppraisalNoteModal");
    addNoteModalEl.addEventListener("hide.tw.modal", function () {
        const form = document.getElementById("addAppraisalNoteForm");
        if (form) {
            form.reset();
        }
        clearFormErrors("#addAppraisalNoteForm");
        $('#addAppraisalNoteModal input[name="document"]').val("");
        $("#addAppraisalNoteDocumentName").html("").hide();
        if (addAppraisalNoteEditor) {
            addAppraisalNoteEditor.setData("");
        }
        document.querySelector("#saveAppraisalNote").removeAttribute("disabled");
        document.querySelector("#saveAppraisalNote svg").style.cssText = "display: none;";
    });

    const editNoteModalEl = document.getElementById("editAppraisalNoteModal");
    editNoteModalEl.addEventListener("hide.tw.modal", function () {
        clearFormErrors("#editAppraisalNoteForm");
        $('#editAppraisalNoteModal input[name="opening_date"]').val("");
        $('#editAppraisalNoteModal input[name="document"]').val("");
        $('#editAppraisalNoteModal input[name="id"]').val("0");
        $("#editAppraisalNoteDocumentName").html("").hide();
        $("#editAppraisalNoteModal .downloadExistAttachment").attr("href", "#").hide();
        if (editAppraisalNoteEditor) {
            editAppraisalNoteEditor.setData("");
        }
        document.querySelector("#updateAppraisalNote").removeAttribute("disabled");
        document.querySelector("#updateAppraisalNote svg").style.cssText = "display: none;";
    });

    const viewNoteModalEl = document.getElementById("viewAppraisalNoteModal");
    viewNoteModalEl.addEventListener("hide.tw.modal", function () {
        $("#viewAppraisalNoteContent").html("");
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

    $("#addAppraisalNoteForm").on("change", "#addAppraisalNoteDocument", function () {
        renderFileChip("addAppraisalNoteDocument", "addAppraisalNoteDocumentName");
    });

    $("#editAppraisalNoteForm").on("change", "#editAppraisalNoteDocument", function () {
        renderFileChip("editAppraisalNoteDocument", "editAppraisalNoteDocumentName");
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

    const buildViewContent = (dataset) => {
        const docLink = dataset.docURL
            ? `
                <a href="${dataset.docURL}" target="_blank" class="ep-doc-btn ep-doc-btn--soft">
                    <i data-lucide="download" class="w-4 h-4"></i>
                    Download Attachment
                </a>
            `
            : "";

        return `
            <div class="ep-appraisal-note-view__meta">
                <div class="ep-appraisal-note-view__meta-item">
                    <span>Opening Date</span>
                    <strong>${dataset.opening_date || "N/A"}</strong>
                </div>
            </div>
            <div class="ep-doc-note-view">${dataset.note || "<p>No note content found.</p>"}</div>
            ${docLink ? `<div class="ep-appraisal-note-view__attachment">${docLink}</div>` : ""}
        `;
    };

    $("#employeeAppraisalNoteListTable").on("click", ".view_btn", function () {
        const noteId = $(this).attr("data-id");

        axios({
            method: "post",
            url: route("employee.get.note"),
            data: { noteId: noteId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            const dataset = response.data.res;
            $("#viewAppraisalNoteContent").html(buildViewContent(dataset));
            renderLucideIcons();
            viewAppraisalNoteModal.show();
        }).catch(error => {
            console.log(error);
        });
    });

    $("#addAppraisalNoteForm").on("submit", function (e) {
        e.preventDefault();

        const form = document.getElementById("addAppraisalNoteForm");
        clearFormErrors("#addAppraisalNoteForm");

        document.querySelector("#saveAppraisalNote").setAttribute("disabled", "disabled");
        document.querySelector("#saveAppraisalNote svg").style.cssText = "display: inline-block;";

        const formData = new FormData(form);
        formData.append("content", addAppraisalNoteEditor ? addAppraisalNoteEditor.getData() : "");

        axios({
            method: "post",
            url: route("employee.store.note"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            document.querySelector("#saveAppraisalNote").removeAttribute("disabled");
            document.querySelector("#saveAppraisalNote svg").style.cssText = "display: none;";

            if (response.status === 200) {
                addAppraisalNoteModal.hide();
                employeeAppraisalNoteListTable.init();
                showSuccessModal("Congratulations!", "Appraisal note successfully stored.");
            }
        }).catch(error => {
            document.querySelector("#saveAppraisalNote").removeAttribute("disabled");
            document.querySelector("#saveAppraisalNote svg").style.cssText = "display: none;";

            if (error.response && error.response.status === 422) {
                applyFormErrors("#addAppraisalNoteForm", error.response.data.errors);
            } else {
                showWarningModal("Error Found!", "Something went wrong. Please try again.");
            }
        });
    });

    $("#employeeAppraisalNoteListTable").on("click", ".edit_btn", function () {
        const noteId = $(this).attr("data-id");

        axios({
            method: "post",
            url: route("employee.get.note"),
            data: { noteId: noteId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            const dataset = response.data.res;

            if (editAppraisalNoteEditor) {
                editAppraisalNoteEditor.setData(dataset.note ? dataset.note : "");
            }

            $('#editAppraisalNoteModal input[name="opening_date"]').val(dataset.opening_date ? dataset.opening_date : "");
            $('#editAppraisalNoteModal input[name="id"]').val(noteId);

            if (dataset.docURL !== "") {
                $("#editAppraisalNoteModal .downloadExistAttachment").attr("href", dataset.docURL).css("display", "inline-flex");
            } else {
                $("#editAppraisalNoteModal .downloadExistAttachment").attr("href", "#").hide();
            }

            editAppraisalNoteModal.show();
        }).catch(error => {
            console.log(error);
        });
    });

    $("#editAppraisalNoteForm").on("submit", function (e) {
        e.preventDefault();

        const form = document.getElementById("editAppraisalNoteForm");
        clearFormErrors("#editAppraisalNoteForm");

        document.querySelector("#updateAppraisalNote").setAttribute("disabled", "disabled");
        document.querySelector("#updateAppraisalNote svg").style.cssText = "display: inline-block;";

        const formData = new FormData(form);
        formData.append("content", editAppraisalNoteEditor ? editAppraisalNoteEditor.getData() : "");

        axios({
            method: "post",
            url: route("employee.update.note"),
            data: formData,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            document.querySelector("#updateAppraisalNote").removeAttribute("disabled");
            document.querySelector("#updateAppraisalNote svg").style.cssText = "display: none;";

            if (response.status === 200) {
                editAppraisalNoteModal.hide();
                employeeAppraisalNoteListTable.init();
                showSuccessModal("Congratulations!", "Appraisal note successfully updated.");
            }
        }).catch(error => {
            document.querySelector("#updateAppraisalNote").removeAttribute("disabled");
            document.querySelector("#updateAppraisalNote svg").style.cssText = "display: none;";

            if (error.response && error.response.status === 422) {
                applyFormErrors("#editAppraisalNoteForm", error.response.data.errors);
            } else {
                showWarningModal("Error Found!", "Something went wrong. Please try again.");
            }
        });
    });

    $("#employeeAppraisalNoteListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        showConfirmModal($(this).attr("data-id"), "DELETENOT", "Want to delete this note from the appraisal list? Please click agree to continue.");
    });

    $("#employeeAppraisalNoteListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        showConfirmModal($(this).attr("data-id"), "RESTORENOT", "Want to restore this note from the archive? Please click agree to continue.");
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();

        const $agreeBtn = $(this);
        const recordId = $agreeBtn.attr("data-recordid");
        const action = $agreeBtn.attr("data-status");
        const employee = $("#employeeAppraisalNoteListTable").attr("data-employee") || "0";

        if (action !== "DELETENOT" && action !== "RESTORENOT") {
            return;
        }

        $("#confirmModal button").attr("disabled", "disabled");

        if (action === "DELETENOT") {
            axios({
                method: "delete",
                url: route("employee.destory.note"),
                data: { employee: employee, recordid: recordId },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeAppraisalNoteListTable.init();
                    showSuccessModal("Done!", "Appraisal note successfully deleted.");
                }
            }).catch(error => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }

        if (action === "RESTORENOT") {
            axios({
                method: "post",
                url: route("employee.restore.note"),
                data: { employee: employee, recordid: recordId },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeAppraisalNoteListTable.init();
                    showSuccessModal("Done!", "Appraisal note successfully restored.");
                }
            }).catch(error => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });

    $("#employeeAppraisalNoteListTable").on("click", ".downloadDoc", function (e) {
        e.preventDefault();
        const $theLink = $(this);
        const rowId = $theLink.attr("data-id");

        $theLink.css({ opacity: ".6", cursor: "not-allowed" });

        axios({
            method: "post",
            url: route("employee.note.download.url"),
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
