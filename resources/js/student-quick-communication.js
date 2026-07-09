/**
 * Global quick Send-Email / Send-SMS popups for student profile pages.
 *
 * Loaded on every student-top-menu page (via the layout), but only activates
 * when the shared partial `pages.students.live.partials.quick-communication-modals`
 * is present on the page (detected via #quickCommModals). This keeps it a no-op on
 * the Communication page, which keeps its own #sendEmailModal / #smsSMSModal and JS.
 *
 * The header email/phone (see show-info.blade.php) open these modals purely through
 * Tailwind `data-tw-toggle="modal"` attributes — this module only handles template
 * loading, the CKEditor body, the SMS counter and the two form submissions.
 */
import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import { createIcons, icons } from "lucide";
import TomSelect from "tom-select";

("use strict");

(function () {
    // Only run where the shared quick-comm popups are actually rendered.
    if (!document.getElementById("quickCommModals")) {
        return;
    }

    const csrf = () => $('meta[name="csrf-token"]').attr("content");

    const qcSuccessModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#qcSuccessModal"));
    const qcWarningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#qcWarningModal"));
    const sendEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendEmailModal"));
    const smsSMSModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#smsSMSModal"));

    const showSuccess = function (title, desc) {
        $("#qcSuccessModal .qcSuccessTitle").html(title);
        $("#qcSuccessModal .qcSuccessDesc").html(desc);
        qcSuccessModal.show();
        setTimeout(function () {
            qcSuccessModal.hide();
        }, 2000);
    };

    const showWarning = function (title, desc) {
        $("#qcWarningModal .qcWarningTitle").html(title);
        $("#qcWarningModal .qcWarningDesc").html(desc);
        qcWarningModal.show();
    };

    $("#qcSuccessModal .qcSuccessCloser").on("click", function (e) {
        e.preventDefault();
        qcSuccessModal.hide();
    });
    $("#qcWarningModal .qcWarningCloser").on("click", function (e) {
        e.preventDefault();
        qcWarningModal.hide();
    });

    /* ------------------------- Template selects (TomSelect) ------------------------- */
    const tomOpts = {
        plugins: { dropdown_input: {} },
        placeholder: "Search Here...",
        persist: false,
        create: false,
        allowEmptyOption: true,
        maxOptions: null,
        onDelete: function (values) {
            return confirm(values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' + values[0] + '"?');
        },
    };
    const emailTemplateSelect = document.getElementById("email_template_id")
        ? new TomSelect("#email_template_id", tomOpts)
        : null;
    const smsTemplateSelect = document.getElementById("sms_template_id")
        ? new TomSelect("#sms_template_id", tomOpts)
        : null;

    /* =============================== SEND EMAIL =============================== */
    let mailEditor;
    if ($("#mailEditor").length > 0) {
        const el = document.getElementById("mailEditor");
        ClassicEditor.create(el).then((editor) => {
            mailEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    const sendEmailModalEl = document.getElementById("sendEmailModal");
    if (sendEmailModalEl) {
        sendEmailModalEl.addEventListener("hide.tw.modal", function () {
            $("#sendEmailModal .acc__input-error").html("");
            $("#sendEmailModal .border-danger").removeClass("border-danger");
            $("#sendEmailModal .modal-body input#sendMailsDocument").val("");
            $("#sendEmailModal .modal-body input, #sendEmailModal .modal-body select").val("");
            $("#sendEmailForm .sendMailsDocumentNames").html("").fadeOut();
            if (mailEditor) mailEditor.setData("");
            if (emailTemplateSelect) emailTemplateSelect.clear(true);
        });
    }

    // Attachment file names preview
    $("#sendEmailForm #sendMailsDocument").on("change", function () {
        const inputs = document.getElementById("sendMailsDocument");
        let html = "";
        for (let i = 0; i < inputs.files.length; ++i) {
            html += '<div class="mb-1 text-primary font-medium flex justify-start items-center"><i data-lucide="disc" class="w-3 h3 mr-2"></i>' + inputs.files.item(i).name + "</div>";
        }
        $("#sendEmailForm .sendMailsDocumentNames").fadeIn().html(html);
        createIcons({ icons, "stroke-width": 1.5, nameAttr: "data-lucide" });
    });

    // Load email template into subject + editor
    $("#sendEmailForm [name='email_template_id']").on("change", function () {
        const emailTemplateID = $(this).val();
        if (emailTemplateID != "") {
            axios({
                method: "post",
                url: route("student.get.mail.template"),
                data: { emailTemplateID: emailTemplateID },
                headers: { "X-CSRF-TOKEN": csrf() },
            }).then((response) => {
                if (response.status == 200) {
                    if (mailEditor) mailEditor.setData(response.data.row.description || "");
                    $("#sendEmailForm [name='subject']").val(response.data.row.email_title || "");
                }
            }).catch(() => {});
        } else if (mailEditor) {
            mailEditor.setData("");
        }
    });

    $("#sendEmailForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("sendEmailForm");

        document.querySelector("#sendEmailBtn").setAttribute("disabled", "disabled");
        document.querySelector("#sendEmailBtn svg").style.cssText = "display: inline-block;";

        const form_data = new FormData(form);
        const fileInput = $("#sendEmailForm input#sendMailsDocument")[0];
        form_data.append("file", fileInput.files[0]);
        form_data.append("body", mailEditor ? mailEditor.getData() : "");

        axios({
            method: "post",
            url: route("student.send.mail"),
            data: form_data,
            headers: { "X-CSRF-TOKEN": csrf() },
        }).then((response) => {
            document.querySelector("#sendEmailBtn").removeAttribute("disabled");
            document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";
            if (response.status == 200) {
                sendEmailModal.hide();
                showSuccess("Congratulation!", "Mail successfully sent to student.");
            }
        }).catch((error) => {
            document.querySelector("#sendEmailBtn").removeAttribute("disabled");
            document.querySelector("#sendEmailBtn svg").style.cssText = "display: none;";
            if (error.response && error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#sendEmailForm .${key}`).addClass("border-danger");
                    $(`#sendEmailForm  .error-${key}`).html(val);
                }
            } else {
                showWarning("Oops!", "Something went wrong while sending the email. Please try again.");
            }
        });
    });

    /* =============================== SEND SMS =============================== */
    const smsSMSModalEl = document.getElementById("smsSMSModal");
    if (smsSMSModalEl) {
        smsSMSModalEl.addEventListener("hide.tw.modal", function () {
            $("#smsSMSModal .acc__input-error").html("");
            $("#smsSMSModal .border-danger").removeClass("border-danger");
            $("#smsSMSModal .modal-body input, #smsSMSModal .modal-body textarea").val("");
            $("#smsSMSModal .sms_countr").html("160 / 1");
            $("#smsSMSModal .modal-content .smsWarning").remove();
            if (smsTemplateSelect) smsTemplateSelect.clear(true);
        });
    }

    // SMS character counter
    $("#smsTextArea").on("keyup", function () {
        const maxlength = $(this).attr("maxlength") > 0 && $(this).attr("maxlength") != "" ? $(this).attr("maxlength") : 0;
        const chars = this.value.length;
        const messages = Math.ceil(chars / 160);
        const remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if (chars > 0) {
            if (chars >= maxlength && maxlength > 0) {
                $("#smsSMSModal .modal-content .smsWarning").remove();
                $("#smsSMSModal .modal-content").prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            } else {
                $("#smsSMSModal .modal-content .smsWarning").remove();
            }
            $("#smsSMSModal .sms_countr").html(remaining + " / " + messages);
        } else {
            $("#smsSMSModal .sms_countr").html("160 / 1");
            $("#smsSMSModal .modal-content .smsWarning").remove();
        }
    });

    // Load SMS template into text area
    $("#smsSMSForm #sms_template_id").on("change", function () {
        const smsTemplateId = $(this).val();
        if (smsTemplateId != "") {
            axios({
                method: "post",
                url: route("student.get.sms.template"),
                data: { smsTemplateId: smsTemplateId },
                headers: { "X-CSRF-TOKEN": csrf() },
            }).then((response) => {
                if (response.status == 200) {
                    $("#smsSMSForm #smsTextArea").val(response.data.row.description ? response.data.row.description : "").trigger("keyup");
                }
            }).catch(() => {});
        } else {
            $("#smsSMSForm #smsTextArea").val("");
            $("#smsSMSModal .sms_countr").html("160 / 1");
        }
    });

    $("#smsSMSForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("smsSMSForm");

        document.querySelector("#sendSMSBtn").setAttribute("disabled", "disabled");
        document.querySelector("#sendSMSBtn svg").style.cssText = "display: inline-block;";

        const form_data = new FormData(form);
        axios({
            method: "post",
            url: route("student.send.sms"),
            data: form_data,
            headers: { "X-CSRF-TOKEN": csrf() },
        }).then((response) => {
            document.querySelector("#sendSMSBtn").removeAttribute("disabled");
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";
            if (response.status == 200) {
                smsSMSModal.hide();
                showSuccess("Congratulation!", response.data.message);
            }
        }).catch((error) => {
            document.querySelector("#sendSMSBtn").removeAttribute("disabled");
            document.querySelector("#sendSMSBtn svg").style.cssText = "display: none;";
            if (error.response && error.response.status == 422) {
                for (const [key, val] of Object.entries(error.response.data.errors)) {
                    $(`#smsSMSForm .${key}`).addClass("border-danger");
                    $(`#smsSMSForm  .error-${key}`).html(val);
                }
            } else {
                showWarning("Oops!", "Something went wrong while sending the SMS. Please try again.");
            }
        });
    });
})();
