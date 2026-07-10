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

const buildReminderCell = (data) => {
    const label = (data.reminder == 1 && data.reminder_date) ? data.reminder_date : "&mdash;";
    return `<span class="ep-doc-reminder"><i data-lucide="clock" class="w-4 h-4"></i>${label}</span>`;
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
            <button data-id="${data.id}" data-tw-toggle="modal" data-tw-target="#viewEmpNoteModal" class="view_btn ep-doc-action-btn ep-doc-action-btn--view" title="View note">
                <i data-lucide="eye" class="w-4 h-4"></i>
            </button>
        `);
        actions.push(`
            <button data-id="${data.id}" data-tw-toggle="modal" data-tw-target="#editEmpNoteModal" type="button" class="edit_btn ep-doc-action-btn ep-doc-action-btn--edit" title="Edit note">
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

var employeeNotesListTable = (function () {
    let tableContent = null;
    let resizeBound = false;
    let currentTotalRows = 0;

    var _tableGen = function () {
        let employeeId = $("#employeeNotesListTable").attr("data-employee") !== "" ? $("#employeeNotesListTable").attr("data-employee") : "0";
        let queryStr = $("#query-EN").val() !== "" ? $("#query-EN").val() : "";
        let status = $("#status-EN").val() !== "" ? $("#status-EN").val() : "1";

        if (tableContent && typeof tableContent.destroy === "function") {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#employeeNotesListTable", {
            ajaxURL: route("employee.note.list"),
            ajaxParams: { employeeId: employeeId, queryStr: queryStr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
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
                    title: "Opening Date",
                    field: "opening_date",
                    headerHozAlign: "left",
                    width: 150,
                },
                {
                    title: "Note",
                    field: "note",
                    headerHozAlign: "left",
                    formatter(cell) {
                        return `<div class="ep-doc-note-preview">${cell.getData().note}</div>`;
                    }
                },
                {
                    title: "Reminder",
                    field: "reminder",
                    headerHozAlign: "left",
                    width: 180,
                    formatter(cell) {
                        return buildReminderCell(cell.getData());
                    }
                },
                {
                    title: "Created By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: 160,
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
                    width: 168,
                    download: false,
                    formatter(cell) {
                        return buildNoteActions(cell.getData());
                    },
                },
            ],
            renderComplete() {
                renderLucideIcons();
                updateTableFooterMeta(this, currentTotalRows, "note");
                updateSectionSummary("#employeeNotesSummary", currentTotalRows, "note", "Record, manage and archive notes for this employee.");
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

        $("#tabulator-export-csv-EN").off("click").on("click", function () {
            tableContent.download("csv", "employee-notes.csv");
        });

        $("#tabulator-export-json-EN").off("click").on("click", function () {
            tableContent.download("json", "employee-notes.json");
        });

        $("#tabulator-export-xlsx-EN").off("click").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "employee-notes.xlsx", {
                sheetName: "Employee Note Details",
            });
        });

        $("#tabulator-export-html-EN").off("click").on("click", function () {
            tableContent.download("html", "employee-notes.html", {
                style: true,
            });
        });

        $("#tabulator-print-EN").off("click").on("click", function () {
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

    if ($("#employeeNotesListTable").length) {
        employeeNotesListTable.init();

        function filterHTMLFormEN() {
            employeeNotesListTable.init();
        }

        $("#tabulator-html-filter-go-EN").on("click", function () {
            filterHTMLFormEN();
        });

        $("#tabulator-html-filter-reset-EN").on("click", function () {
            $("#query-EN").val("");
            $("#status-EN").val("1");
            filterHTMLFormEN();
        });
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addEmpNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEmpNoteModal"));
    const viewEmpNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewEmpNoteModal"));
    const editEmpNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEmpNoteModal"));

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

    let addEmpNoteEditor;
    if ($("#addEmpNoteEditor").length > 0) {
        const el = document.getElementById("addEmpNoteEditor");
        ClassicEditor.create(el).then((editor) => {
            addEmpNoteEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let editEmpNoteEditor;
    if ($("#editEmpNoteEditor").length > 0) {
        const el = document.getElementById("editEmpNoteEditor");
        ClassicEditor.create(el).then((editor) => {
            editEmpNoteEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    $("#reminder").on("change", function () {
        if ($(this).prop("checked")) {
            $("#addEmpNoteModal .reminderDateWrap").fadeIn("fast", function () {
                $("input", this).val("");
            });
        } else {
            $("#addEmpNoteModal .reminderDateWrap").fadeOut("fast", function () {
                $("input", this).val("");
            });
        }
    });

    $("#edit_reminder").on("change", function () {
        if ($(this).prop("checked")) {
            $("#editEmpNoteModal .reminderDateWrap").fadeIn("fast", function () {
                $("input", this).val("");
            });
        } else {
            $("#editEmpNoteModal .reminderDateWrap").fadeOut("fast", function () {
                $("input", this).val("");
            });
        }
    });

    const addNoteModalEl = document.getElementById("addEmpNoteModal");
    addNoteModalEl.addEventListener("hide.tw.modal", function () {
        clearFormErrors("#addEmpNoteForm");
        $('#addEmpNoteModal input[name="document"]').val("");
        $("#addEmpNoteModal #addEmpNoteDocumentName").html("").hide();
        if (addEmpNoteEditor) {
            addEmpNoteEditor.setData("");
        }
        $("#reminder").prop("checked", false);
        $("#addEmpNoteModal .reminderDateWrap").fadeOut("fast", function () {
            $("input", this).val("");
        });
    });

    const editNoteModalEl = document.getElementById("editEmpNoteModal");
    editNoteModalEl.addEventListener("hide.tw.modal", function () {
        clearFormErrors("#editEmpNoteForm");
        $('#editEmpNoteModal input[name="opening_date"]').val("");
        $('#editEmpNoteModal input[name="document"]').val("");
        $("#editEmpNoteModal #editEmpNoteDocumentName").html("").hide();
        $('#editEmpNoteModal input[name="id"]').val("0");
        $("#editEmpNoteModal .downloadExistAttachment").attr("href", "#").fadeOut();
        if (editEmpNoteEditor) {
            editEmpNoteEditor.setData("");
        }
        $("#edit_reminder").prop("checked", false);
        $("#editEmpNoteModal .reminderDateWrap").hide().find("input").val("");
    });

    const viewNoteModalEl = document.getElementById("viewEmpNoteModal");
    viewNoteModalEl.addEventListener("hide.tw.modal", function () {
        $("#viewEmpNoteModal .modal-body").html("");
        $("#viewEmpNoteModal .modal-footer .footerBtns").html("");
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

    $("#addEmpNoteForm").on("change", "#addEmpNoteDocument", function () {
        renderFileChip("addEmpNoteDocument", "addEmpNoteDocumentName");
    });

    $("#editEmpNoteForm").on("change", "#editEmpNoteDocument", function () {
        renderFileChip("editEmpNoteDocument", "editEmpNoteDocumentName");
    });

    function renderFileChip(inputId, targetId) {
        let fileInput = document.getElementById(inputId);
        let target = document.getElementById(targetId);
        if (!target) return false;
        let file = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
        if (file) {
            target.innerHTML = ''
                + '<i data-lucide="file-text" class="w-4 h-4 ep-doc-file-chip__icon"></i>'
                + '<span class="ep-doc-file-chip__name">' + file.name + '</span>'
                + '<button type="button" class="ep-doc-file-chip__remove" data-input="' + inputId + '" title="Remove"><i data-lucide="x" class="w-3 h-3"></i></button>';
            target.style.display = "inline-flex";
            renderLucideIcons();
        } else {
            target.innerHTML = "";
            target.style.display = "none";
        }
        return false;
    }

    $(document).on("click", ".ep-doc-file-chip__remove", function (e) {
        e.preventDefault();
        let inputId = $(this).attr("data-input");
        let fileInput = document.getElementById(inputId);
        if (fileInput) {
            fileInput.value = "";
        }
        let chip = $(this).closest(".ep-doc-file-chip");
        chip.html("").hide();
    });

    $("#employeeNotesListTable").on("click", ".view_btn", function () {
        var noteId = $(this).attr("data-id");
        axios({
            method: "post",
            url: route("employee.show.note"),
            data: { noteId: noteId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            $("#viewEmpNoteModal .modal-body").html(response.data.message);
            if (response.data.btns !== "") {
                $("#viewEmpNoteModal .modal-footer .footerBtns").html(response.data.btns);
            }
            renderLucideIcons();
        }).catch(error => {
            console.log("error");
        });
    });

    $("#addEmpNoteForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("addEmpNoteForm");

        clearFormErrors("#addEmpNoteForm");

        document.querySelector("#saveEmpNote").setAttribute("disabled", "disabled");
        document.querySelector("#saveEmpNote svg").style.cssText = "display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("file", $('#addEmpNoteForm input[name="document"]')[0].files[0]);
        form_data.append("content", addEmpNoteEditor ? addEmpNoteEditor.getData() : "");
        axios({
            method: "post",
            url: route("employee.store.note"),
            data: form_data,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            document.querySelector("#saveEmpNote").removeAttribute("disabled");
            document.querySelector("#saveEmpNote svg").style.cssText = "display: none;";

            if (response.status === 200) {
                addEmpNoteModal.hide();
                showSuccessModal("Congratulations!", "Employee note successfully stored.", "NONE");
                setTimeout(function () {
                    successModal.hide();
                }, 2000);
            }
            employeeNotesListTable.init();
        }).catch(error => {
            document.querySelector("#saveEmpNote").removeAttribute("disabled");
            document.querySelector("#saveEmpNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status === 422) {
                    applyFormErrors("#addEmpNoteForm", error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#employeeNotesListTable").on("click", ".edit_btn", function () {
        var noteId = $(this).attr("data-id");
        axios({
            method: "post",
            url: route("employee.get.note"),
            data: { noteId: noteId },
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            let dataset = response.data.res;
            if (editEmpNoteEditor) {
                editEmpNoteEditor.setData(dataset.note ? dataset.note : "");
            }
            $('#editEmpNoteModal [name="opening_date"]').val(dataset.opening_date ? dataset.opening_date : "");
            $('#editEmpNoteModal input[name="id"]').val(noteId);
            if (dataset.docURL !== "") {
                $("#editEmpNoteModal .downloadExistAttachment").attr("href", dataset.docURL).css("display", "inline-flex");
            } else {
                $("#editEmpNoteModal .downloadExistAttachment").attr("href", "#").hide();
            }
            if (dataset.reminder == 1) {
                $("#edit_reminder").prop("checked", true);
                $("#editEmpNoteModal .reminderDateWrap").fadeIn("fast", function () {
                    $('input[name="reminder_date"]', this).val(dataset.reminder_date ? dataset.reminder_date : "");
                });
            } else {
                $("#edit_reminder").prop("checked", false);
                $("#editEmpNoteModal .reminderDateWrap").fadeOut("fast", function () {
                    $('input[name="reminder_date"]', this).val("");
                });
            }
        }).catch(error => {
            console.log("error");
        });
    });

    $("#editEmpNoteForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("editEmpNoteForm");

        clearFormErrors("#editEmpNoteForm");

        document.querySelector("#updateEmpNote").setAttribute("disabled", "disabled");
        document.querySelector("#updateEmpNote svg").style.cssText = "display: inline-block;";

        let form_data = new FormData(form);
        form_data.append("file", $('#editEmpNoteForm input[name="document"]')[0].files[0]);
        form_data.append("content", editEmpNoteEditor ? editEmpNoteEditor.getData() : "");
        axios({
            method: "post",
            url: route("employee.update.note"),
            data: form_data,
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then(response => {
            document.querySelector("#updateEmpNote").removeAttribute("disabled");
            document.querySelector("#updateEmpNote svg").style.cssText = "display: none;";

            if (response.status === 200) {
                editEmpNoteModal.hide();
                showSuccessModal("Congratulations!", "Employee note successfully updated.", "NONE");
                setTimeout(function () {
                    successModal.hide();
                }, 2000);
            }
            employeeNotesListTable.init();
        }).catch(error => {
            document.querySelector("#updateEmpNote").removeAttribute("disabled");
            document.querySelector("#updateEmpNote svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status === 422) {
                    applyFormErrors("#editEmpNoteForm", error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#employeeNotesListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        var noteId = $(this).attr("data-id");
        showConfirmModal(noteId, "DELETENOT", "Want to delete this note from the employee list? Please click agree to continue.");
    });

    $("#employeeNotesListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        var noteId = $(this).attr("data-id");
        showConfirmModal(noteId, "RESTORENOT", "Want to restore this note from the archive? Please click agree to continue.");
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr("data-recordid");
        let action = $agreeBTN.attr("data-status");
        let employee = $agreeBTN.attr("data-employee");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action === "DELETENOT") {
            axios({
                method: "delete",
                url: route("employee.destory.note"),
                data: { employee: employee, recordid: recordid },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeNotesListTable.init();

                    showSuccessModal("Done!", "Employee note successfully deleted.", "NONE");
                    setTimeout(function () {
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error => {
                console.log(error);
            });
        } else if (action === "RESTORENOT") {
            axios({
                method: "post",
                url: route("employee.restore.note"),
                data: { employee: employee, recordid: recordid },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status === 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    employeeNotesListTable.init();

                    showSuccessModal("Done!", "Employee note successfully restored.", "NONE");
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

    $("#employeeNotesListTable").on("click", ".downloadDoc", function (e) {
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr("data-id");

        $theLink.css({ "opacity": ".6", "cursor": "not-allowed" });

        axios({
            method: "post",
            url: route("employee.note.download.url"),
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
})();
