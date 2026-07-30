/*
 * Bulk Communication — SMS, email and letters for the students under a set of
 * class plans.
 *
 * A port of the legacy `bulk-communication.js`. The endpoints, the field names
 * and the payload shapes are unchanged: `student_ids` travels as a comma
 * string, the two editors are appended as `body` / `letter_body`, and the
 * attachments ride along as `documents[]`. Only the markup around them is new.
 */

import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import Litepicker from "litepicker";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import {
    PAGINATION_LANGS,
    clearErrors,
    createRangePainter,
    csrfHeaders,
    paintCount,
    paintErrors,
    refreshIcons,
    setBusy,
    showSuccess,
    wireResize,
} from "./course-table-kit";

const TABLE_ID = "#communicationStudentListTable";

(function () {
    const host = document.querySelector(TABLE_ID);
    if (!host) return;

    const plans = host.getAttribute("data-plans") || "";

    let table = null;
    let lastTotal = null;
    let settled = false;

    const getTable = () => table;
    const paintRange = createRangePainter(TABLE_ID, getTable, () => lastTotal);
    const modal = (id) => tailwind.Modal.getOrCreateInstance(document.querySelector(`#${id}`));

    /* ------------------------------------------------------------------ *
     * Student list
     * ------------------------------------------------------------------ */

    const actions = document.querySelector("#communicationBtnsArea");
    const selectedLabel = document.querySelector("[data-cm-selected]");

    /** Ids of the ticked rows — every send acts on exactly these. */
    const selectedStudentIds = () => (table ? table.getSelectedData().map((r) => r.id) : []);

    function paintSelection() {
        const n = selectedStudentIds().length;

        if (actions) actions.hidden = n === 0;
        if (selectedLabel) selectedLabel.textContent = `${n} selected`;
    }

    table = new Tabulator(TABLE_ID, {
        ajaxURL: route("bulk.communication.student.list"),
        ajaxParams: { plans },
        ajaxFiltering: true,
        ajaxSorting: true,
        printAsHtml: true,
        printStyled: true,
        pagination: "remote",
        paginationSize: 100,
        // `true` is Tabulator's "all rows"; `list()` reads size === 'true'.
        paginationSizeSelector: [true, 50, 100, 200, 300, 500],
        layout: "fitColumns",
        responsiveLayout: "collapse",
        placeholder: "No assigned students were found under the selected class plans",
        langs: PAGINATION_LANGS,
        selectable: true,
        // A row with no student behind it can never be messaged.
        selectableCheck: (row) => row.getData().id > 0,
        ajaxResponse(url, params, response) {
            lastTotal = typeof response.total === "number" ? response.total : null;
            paintCount(lastTotal);

            return response;
        },
        columns: [
            {
                formatter: "rowSelection",
                titleFormatter: "rowSelection",
                hozAlign: "left",
                headerHozAlign: "left",
                width: 56,
                headerSort: false,
                download: false,
                cellClick(e, cell) {
                    cell.getRow().toggleSelect();
                },
            },
            {
                title: "Student ID",
                field: "registration_no",
                headerHozAlign: "left",
                width: 148,
                cssClass: "cm-cell--primary",
            },
            {
                // Fixed rather than growing: a first name is a dozen characters
                // and any width past that is space the course and the status
                // badge can use.
                title: "First Name",
                field: "first_name",
                headerHozAlign: "left",
                width: 140,
            },
            {
                title: "Last Name",
                field: "last_name",
                headerHozAlign: "left",
                width: 140,
            },
            {
                title: "Intake Semester",
                field: "semester",
                headerHozAlign: "left",
                headerSort: false,
                width: 132,
            },
            {
                // The only column left growing, so the row always fills the
                // card exactly — every other width here is fixed. Course names
                // run to 60-odd characters, so it wraps rather than pushing the
                // table past its card when the window is narrow.
                title: "Course",
                field: "course",
                headerHozAlign: "left",
                widthGrow: 1,
                minWidth: 200,
                variableHeight: true,
                cssClass: "cm-cell--wrap",
            },
            {
                // Wide enough for the longest badge ("SLC Course Completed")
                // and no wider — growing it just left a gap at the end of
                // every row.
                title: "Status",
                field: "status_id",
                headerHozAlign: "left",
                headerSort: false,
                width: 190,
                formatter(cell) {
                    const v = String(cell.getValue() || "");
                    if (!v) return "";

                    const bad = /suspend|withdraw|discard|defer|terminat|fail/i.test(v);

                    return `<span class="cm-statusbadge ${bad ? "is-bad" : ""}">${v}</span>`;
                },
            },
        ],
        renderComplete() {
            refreshIcons();
            paintRange();

            // fitColumns leaves a sub-pixel overflow that shows as a horizontal
            // scrollbar; every table in this shell trims the last column by 1px.
            const cols = this.getColumns();
            if (cols.length > 0) {
                const last = cols[cols.length - 1];
                last.setWidth(last.getWidth() - 1);
            }

            // fitColumns sizes against the container as it was when the table
            // was built — before the web fonts land and the sidebar settles,
            // so the columns can end up wider than the card they sit in. One
            // re-measure after the first paint fixes it.
            if (!settled) {
                settled = true;
                window.requestAnimationFrame(() => table.redraw(true));
            }
        },
        rowSelectionChanged: paintSelection,
        // `checked` is 0 for a student who has been marked absent on one of
        // these plans; everyone else starts ticked, as before.
        rowFormatter(row) {
            if (row.getData().checked == 1) row.select();
        },
    });

    wireResize(getTable);

    $("[data-cm-select-all]").on("click", () => table.selectRow(table.getRows("active")));
    $("[data-cm-select-none]").on("click", () => table.deselectRow());

    /* ------------------------------------------------------------------ *
     * Shared modal plumbing
     * ------------------------------------------------------------------ */

    const TOM_OPTIONS = {
        placeholder: "Search…",
        // The modal clips its own overflow, so the list hangs off <body>.
        dropdownParent: "body",
        dropdownClass: "ts-dropdown cm-tom-dropdown",
        allowEmptyOption: true,
        create: false,
        maxOptions: 500,
    };

    const smsTemplate = new TomSelect("#sms_template_id", TOM_OPTIONS);
    const emailTemplate = new TomSelect("#email_template_id", TOM_OPTIONS);
    const letterSet = new TomSelect("#letter_set_id", TOM_OPTIONS);

    document.querySelectorAll("#generateBulkLetterModal .datepicker").forEach((input) => {
        new Litepicker({
            element: input,
            autoApply: true,
            singleMode: true,
            numberOfColumns: 1,
            numberOfMonths: 1,
            format: "DD-MM-YYYY",
            dropdowns: { minYear: 1900, maxYear: 2050, months: true, years: true },
        });
    });

    /** Boots one CKEditor and parks its toolbar in the shell above it. */
    function buildEditor(id) {
        const el = document.getElementById(id);
        if (!el) return Promise.resolve(null);

        return ClassicEditor.create(el)
            .then((editor) => {
                $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);

                return editor;
            })
            .catch(() => null);
    }

    let mailEditor = null;
    let letterEditor = null;
    buildEditor("mailEditor").then((e) => (mailEditor = e));
    buildEditor("letterEditor").then((e) => (letterEditor = e));

    /**
     * Opens one of the three dialogs against the current selection. Nothing is
     * sendable without students, and the endpoints read them as a comma string.
     */
    function openSend(id) {
        const ids = selectedStudentIds();

        if (!ids.length) {
            showSuccess("Nothing selected", "Tick at least one student in the list first.", "warn");

            return;
        }

        $(`#${id} input[name="student_ids"]`).val(ids.join(","));
        modal(id).show();
    }

    /* ------------------------------------------------------------------ *
     * SMS
     * ------------------------------------------------------------------ */

    /* An SMS is billed in 160-character parts, so the box reports how many are
     * left in the current part and how many parts the text has grown to. */
    function paintSmsCount() {
        const chars = $("#smsTextArea").val().length;

        if (chars === 0) {
            $("#sendBulkSmsModal .sms_countr").text("160 / 1").removeClass("is-over");

            return;
        }

        const messages = Math.ceil(chars / 160);
        const remaining = messages * 160 - chars;

        $("#sendBulkSmsModal .sms_countr")
            .text(`${remaining} / ${messages}`)
            .toggleClass("is-over", messages > 1);
    }

    $("#smsTextArea").on("input", paintSmsCount);

    $(".sendBulkSmsBtn").on("click", () => openSend("sendBulkSmsModal"));

    document.querySelector("#sendBulkSmsModal").addEventListener("hide.tw.modal", function () {
        clearErrors("#sendBulkSmsForm");
        $("#sms_subject, #smsTextArea").val("");
        $('#sendBulkSmsForm input[name="student_ids"]').val("");
        smsTemplate.clear(true);
        paintSmsCount();
    });

    $("#sendBulkSmsForm #sms_template_id").on("change", function () {
        const id = $(this).val();

        if (!id) {
            $("#smsTextArea").val("");
            paintSmsCount();

            return;
        }

        axios({
            method: "post",
            url: route("bulk.communication.get.sms.template"),
            data: { smsTemplateId: id },
            headers: csrfHeaders(),
        }).then((response) => {
            $("#smsTextArea").val(response.data.row.description || "");
            paintSmsCount();
        });
    });

    $("#sendBulkSmsForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#sendBulkSmsForm");
        setBusy("#sendSMSBtn", true);

        axios({
            method: "post",
            url: route("bulk.communication.send.sms"),
            data: new FormData(this),
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#sendSMSBtn", false);
                modal("sendBulkSmsModal").hide();
                showSuccess("Congratulations!", response.data.message || "The messages were sent.");
            })
            .catch((error) => reportSendError(error, "#sendBulkSmsForm", "#sendSMSBtn"));
    });

    /* ------------------------------------------------------------------ *
     * Email
     * ------------------------------------------------------------------ */

    $(".sendBulkMailBtn").on("click", () => openSend("sendBulkMailModal"));

    document.querySelector("#sendBulkMailModal").addEventListener("hide.tw.modal", function () {
        clearErrors("#sendBulkMailForm");
        $("#subject, #sendMailsDocument").val("");
        $("#mail_comon_smtp_id").val("");
        $('#sendBulkMailForm input[name="student_ids"]').val("");
        $("#sendMailsDocumentNames").html("").prop("hidden", true);
        emailTemplate.clear(true);
        if (mailEditor) mailEditor.setData("");
    });

    $("#sendMailsDocument").on("change", function () {
        const files = Array.from(this.files || []);

        $("#sendMailsDocumentNames")
            .html(
                files
                    .map(
                        (f) =>
                            '<span class="cm-upload__file">' +
                            '<span class="cm-upload__file-icon"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><path d="M14 2v6h6"></path></svg></span>' +
                            `<span class="cm-upload__file-name">${f.name}</span>` +
                            `<span class="cm-upload__file-size">${Math.max(1, Math.round(f.size / 1024))} KB</span>` +
                            "</span>"
                    )
                    .join("")
            )
            .prop("hidden", files.length === 0);
    });

    $('#sendBulkMailForm [name="email_template_id"]').on("change", function () {
        const id = $(this).val();

        if (!id) {
            if (mailEditor) mailEditor.setData("");

            return;
        }

        axios({
            method: "post",
            url: route("bulk.communication.get.mail.template"),
            data: { emailTemplateID: id },
            headers: csrfHeaders(),
        }).then((response) => {
            if (mailEditor) mailEditor.setData(response.data.row.description || "");
        });
    });

    $("#sendBulkMailForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#sendBulkMailForm");
        setBusy("#sendEmailBtn", true);

        const data = new FormData(this);
        data.append("body", mailEditor ? mailEditor.getData() : "");

        axios({
            method: "post",
            url: route("bulk.communication.send.email"),
            data,
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#sendEmailBtn", false);
                modal("sendBulkMailModal").hide();
                showSuccess("Congratulations!", response.data.message || "The emails were sent.");
            })
            .catch((error) => reportSendError(error, "#sendBulkMailForm", "#sendEmailBtn"));
    });

    /* ------------------------------------------------------------------ *
     * Letter
     * ------------------------------------------------------------------ */

    const today = () => {
        const d = new Date();

        return [d.getDate(), d.getMonth() + 1, d.getFullYear()]
            .map((n, i) => (i < 2 ? String(n).padStart(2, "0") : n))
            .join("-");
    };

    $(".generateBulkLetterBtn").on("click", () => openSend("generateBulkLetterModal"));

    document.querySelector("#generateBulkLetterModal").addEventListener("hide.tw.modal", function () {
        clearErrors("#generateBulkLetterForm");
        $("#signatory_id, #comon_smtp_id").val("");
        $('#generateBulkLetterForm input[name="student_ids"]').val("");
        $("#send_in_email").prop("checked", false);
        $("#generateBulkLetterModal .commonSmtpWrap, #generateBulkLetterModal .letterEditorArea").prop("hidden", true);
        letterSet.clear(true);
        if (letterEditor) letterEditor.setData("");
        $("#issued_date").val(today());
    });

    // The SMTP is only required when the letter is also emailed.
    $("#send_in_email").on("change", function () {
        const on = $(this).prop("checked");

        $("#generateBulkLetterModal .commonSmtpWrap").prop("hidden", !on);
        if (!on) $("#comon_smtp_id").val("");
    });

    $("#generateBulkLetterModal #letter_set_id").on("change", function () {
        const id = $(this).val();

        if (!id) {
            $("#generateBulkLetterModal .letterEditorArea").prop("hidden", true);
            if (letterEditor) letterEditor.setData("");

            return;
        }

        axios({
            method: "post",
            url: route("bulk.communication.get.letter.set"),
            data: { letterSetId: id },
            headers: csrfHeaders(),
        }).then((response) => {
            $("#generateBulkLetterModal .letterEditorArea").prop("hidden", false);
            if (letterEditor) letterEditor.setData((response.data.res && response.data.res.description) || "");
        });
    });

    // Clicking a tag copies it, so it can be pasted into the letter body.
    $("#generateBulkLetterModal").on("click", ".letterTags .dropdown-item", function () {
        const tag = $(this).text().trim();

        if (navigator.clipboard) navigator.clipboard.writeText(tag);
    });

    $("#generateBulkLetterForm").on("submit", function (event) {
        event.preventDefault();
        clearErrors("#generateBulkLetterForm");
        setBusy("#sendLetterBtn", true);

        const printPdf = $('input[name="print_pdf"]', this).val();
        const data = new FormData(this);
        data.append("letter_body", letterEditor ? letterEditor.getData() : "");

        axios({
            method: "post",
            url: route("bulk.communication.send.letter"),
            data,
            headers: csrfHeaders(),
        })
            .then((response) => {
                setBusy("#sendLetterBtn", false);
                modal("generateBulkLetterModal").hide();

                const pdf = response.data.pdf_url || "";
                if (printPdf == 1 && pdf !== "") window.open(pdf, "_blank");

                showSuccess("Congratulations!", "The letter was generated and sent to the selected students.");
            })
            .catch((error) => reportSendError(error, "#generateBulkLetterForm", "#sendLetterBtn"));
    });

    /**
     * All three endpoints answer 422 with field errors and 412 with a single
     * message — a missing mobile number, an SMS gateway that is switched off —
     * which belongs in the notice dialog rather than against a field.
     */
    function reportSendError(error, formSelector, buttonSelector) {
        setBusy(buttonSelector, false);
        if (!error.response) return;

        if (error.response.status === 422) {
            paintErrors(formSelector, error.response.data.errors || {});
        } else if (error.response.status === 412) {
            showSuccess("It could not be sent", error.response.data.message || "Please try again.", "warn");
        } else {
            showSuccess("Something went wrong", "The message could not be sent. Please try again.", "warn");
        }
    }

    paintSelection();
})();
