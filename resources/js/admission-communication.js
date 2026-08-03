import ClassicEditor from "@ckeditor/ckeditor5-build-decoupled-document";
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");

function commEscape(value) {
    return String(value == null ? "" : value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function commInitials(name) {
    const initials = String(name || "")
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");

    return initials || "?";
}

function commAvatarColor(seed) {
    const palette = ["#0d7a76", "#6d4bb0", "#2f7d4f", "#c0562a", "#2f8f7d", "#3a6ea5"];
    const text = String(seed || "");
    let hash = 0;

    for (let i = 0; i < text.length; i += 1) {
        hash = (hash + text.charCodeAt(i) * (i + 1)) % palette.length;
    }

    return palette[hash];
}

function commFormatFileSize(bytes) {
    const size = Number(bytes) || 0;
    if (size < 1024) return size + " B";
    if (size < 1024 * 1024) return (size / 1024).toFixed(size < 10240 ? 1 : 0) + " KB";
    return (size / (1024 * 1024)).toFixed(1) + " MB";
}

function commIssuedByHtml(name, date, seed) {
    const safeName = commEscape(name || "--");
    const safeDate = commEscape(date || "");

    return '<div class="adm-comm-by">' +
        '<span class="adm-comm-by__avatar" style="background:' + commAvatarColor(seed || name) + ';">' + commInitials(name) + '</span>' +
        '<span class="adm-comm-by__text">' +
            '<span class="adm-comm-by__name">' + safeName + '</span>' +
            '<span class="adm-comm-by__date">' + safeDate + '</span>' +
        '</span>' +
    '</div>';
}

function commActionButton(action) {
    const tag = action.tag || "button";
    const attrs = [
        'data-id="' + commEscape(action.id) + '"',
        'class="' + commEscape(action.className) + ' adm-row-action adm-row-action--' + commEscape(action.type) + '"',
        'title="' + commEscape(action.title) + '"',
        'aria-label="' + commEscape(action.title) + '"',
    ];

    if (tag === "button") attrs.push('type="button"');
    if (tag === "a") attrs.push('href="javascript:void(0);"');
    if (action.extra) attrs.push(action.extra);

    return '<' + tag + ' ' + attrs.join(" ") + '>' +
        '<i data-lucide="' + commEscape(action.icon) + '"></i>' +
    '</' + tag + '>';
}

function commActions(actions) {
    return '<div class="adm-row-actions">' +
        actions.filter(Boolean).map(commActionButton).join("") +
    '</div>';
}

function commRenderLucide() {
    createIcons({
        icons,
        "stroke-width": 1.8,
        nameAttr: "data-lucide",
    });
}

function commPrepareButtonLoaders(scope = document) {
    scope.querySelectorAll('.modal-footer button svg[viewBox="-2 -2 42 42"]').forEach((svg) => {
        svg.classList.add("adm-btn-loader");
    });
}

function commDecorateModalButtons(scope = document) {
    commPrepareButtonLoaders(scope);

    scope.querySelectorAll(".modal-footer .btn, .modal-body.p-0 .px-5.pb-8.text-center .btn").forEach((button) => {
        if (button.querySelector(".adm-btn-icon")) return;

        const text = button.textContent.trim().toLowerCase();
        let icon = "check";

        if (text.includes("cancel")) icon = "x";
        else if (text.includes("delete")) icon = "trash-2";
        else if (text.includes("send")) icon = "send";
        else if (text.includes("agree") || text.includes("ok") || text.includes("save") || text.includes("update")) icon = "check";

        const iconEl = document.createElement("i");
        iconEl.setAttribute("data-lucide", icon);
        iconEl.className = "adm-btn-icon";
        button.prepend(iconEl);
    });

    commRenderLucide();
}

function commSetButtonBusy(selector, busy) {
    const button = document.querySelector(selector);
    if (!button) return;

    button.toggleAttribute("disabled", busy);
    button.classList.toggle("is-busy", busy);

    const loader = button.querySelector(".adm-btn-loader");
    if (loader) {
        loader.style.display = busy ? "inline-block" : "none";
    }
}

var applicantCommLetterListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#applicantCommLetterListTable").attr('data-applicant') != "" ? $("#applicantCommLetterListTable").attr('data-applicant') : "0";
        let queryStrCML = $("#query-CML").val() != "" ? $("#query-CML").val() : "";
        let statusCML = $("#status-CML").val() != "" ? $("#status-CML").val() : "1";

        let tableContent = new Tabulator("#applicantCommLetterListTable", {
            ajaxURL: route("admission.communication.letter.list"),
            ajaxParams: { applicantId: applicantId, queryStrCML : queryStrCML, statusCML : statusCML},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: "120",
                },
                {
                    title: "Type",
                    field: "letter_type",
                    headerHozAlign: "left"
                },
                {
                    title: "Subject",
                    field: "letter_title",
                    headerHozAlign: "left"
                },
                {
                    title: "Signatory",
                    field: "signatory_name",
                    headerHozAlign: "left"
                },
                {
                    title: "Issued By",
                    field: "created_by",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return commIssuedByHtml(cell.getData().created_by, cell.getData().created_at, cell.getData().id);
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "130",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        return commActions([
                            cell.getData().docurl > 0 ? {
                                tag: "a",
                                type: "download",
                                className: "downloadDoc",
                                icon: "download",
                                id: cell.getData().docurl,
                                title: "Download",
                            } : null,
                            cell.getData().deleted_at == null ? {
                                type: "delete",
                                className: "delete_btn",
                                icon: "trash-2",
                                id: cell.getData().id,
                                title: "Delete",
                            } : {
                                type: "restore",
                                className: "restore_btn",
                                icon: "rotate-cw",
                                id: cell.getData().id,
                                title: "Restore",
                            },
                        ]);
                    },
                },
            ],
            renderComplete() {
                commRenderLucide();
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            commRenderLucide();
        });

        // Export
        $("#tabulator-export-csv-CML").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CML").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CML").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Admission Communication Details",
            });
        });

        $("#tabulator-export-html-CML").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CML").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();
var applicantCommEmailListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#applicantCommEmailListTable").attr('data-applicant') != "" ? $("#applicantCommEmailListTable").attr('data-applicant') : "0";
        let queryStrCME = $("#query-CME").val() != "" ? $("#query-CME").val() : "";
        let statusCME = $("#status-CME").val() != "" ? $("#status-CME").val() : "1";

        let tableContent = new Tabulator("#applicantCommEmailListTable", {
            ajaxURL: route("admission.communication.mail.list"),
            ajaxParams: { applicantId: applicantId, queryStrCME : queryStrCME, statusCME : statusCME},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: "120",
                },
                {
                    title: "Subject",
                    field: "subject",
                    headerHozAlign: "left"
                },
                {
                    title: "From",
                    field: "smtp",
                    headerHozAlign: "left"
                },
                {
                    title: "Issued By",
                    field: "created_by",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return commIssuedByHtml(cell.getData().created_by, cell.getData().created_at, cell.getData().id);
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "130",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        return commActions(
                            cell.getData().deleted_at == null ? [
                                {
                                    type: "view",
                                    className: "view_btn",
                                    icon: "eye",
                                    id: cell.getData().id,
                                    title: "View",
                                    extra: 'data-tw-toggle="modal" data-tw-target="#viewCommunicationModal"',
                                },
                                {
                                    type: "delete",
                                    className: "delete_btn",
                                    icon: "trash-2",
                                    id: cell.getData().id,
                                    title: "Delete",
                                },
                            ] : [
                                {
                                    type: "restore",
                                    className: "restore_btn",
                                    icon: "rotate-cw",
                                    id: cell.getData().id,
                                    title: "Restore",
                                },
                            ]
                        );
                    },
                },
            ],
            renderComplete() {
                commRenderLucide();
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            commRenderLucide();
        });

        // Export
        $("#tabulator-export-csv-CME").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CME").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CME").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Applicant Email Details",
            });
        });

        $("#tabulator-export-html-CME").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CME").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

var applicantCommSMSListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId = $("#applicantCommSMSListTable").attr('data-applicant') != "" ? $("#applicantCommSMSListTable").attr('data-applicant') : "0";
        let queryStrCMS = $("#query-CMS").val() != "" ? $("#query-CMS").val() : "";
        let statusCMS = $("#status-CMS").val() != "" ? $("#status-CMS").val() : "1";

        let tableContent = new Tabulator("#applicantCommSMSListTable", {
            ajaxURL: route("admission.communication.sms.list"),
            ajaxParams: { applicantId: applicantId, queryStrCMS : queryStrCMS, statusCMS : statusCMS},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: "120",
                },
                {
                    title: "Subject",
                    field: "subject",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().subject;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Template",
                    field: "template",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += cell.getData().template;
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Issued By",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: "180",
                    formatter(cell, formatterParams){
                        return commIssuedByHtml(cell.getData().created_by, cell.getData().created_at, cell.getData().id);
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "130",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        return commActions(
                            cell.getData().deleted_at == null ? [
                                {
                                    type: "view",
                                    className: "view_btn",
                                    icon: "eye",
                                    id: cell.getData().id,
                                    title: "View",
                                    extra: 'data-tw-toggle="modal" data-tw-target="#viewCommunicationModal"',
                                },
                                {
                                    type: "delete",
                                    className: "delete_btn",
                                    icon: "trash-2",
                                    id: cell.getData().id,
                                    title: "Delete",
                                },
                            ] : [
                                {
                                    type: "restore",
                                    className: "restore_btn",
                                    icon: "rotate-cw",
                                    id: cell.getData().id,
                                    title: "Restore",
                                },
                            ]
                        );
                    },
                },
            ],
            renderComplete() {
                commRenderLucide();
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
            }
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            commRenderLucide();
        });

        // Export
        $("#tabulator-export-csv-CMS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CMS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CMS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Applicant SMS Details",
            });
        });

        $("#tabulator-export-html-CMS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CMS").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if ($("#applicantCommSMSListTable").length) {
        // Init Table
        applicantCommSMSListTable.init();

        // Filter function
        function filterHTMLFormCMS() {
            applicantCommSMSListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-CMS").on("click", function (event) {
            filterHTMLFormCMS();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CMS").on("click", function (event) {
            $("#query-CMS").val("");
            $("#status-CMS").val("1");
            filterHTMLFormCMS();
        });

    }

    if ($("#applicantCommEmailListTable").length) {
        // Init Table
        applicantCommEmailListTable.init();

        // Filter function
        function filterHTMLFormCME() {
            applicantCommEmailListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-CME").on("click", function (event) {
            filterHTMLFormCME();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CME").on("click", function (event) {
            $("#query-CME").val("");
            $("#status-CME").val("1");
            filterHTMLFormCME();
        });

    }

    if ($("#applicantCommLetterListTable").length) {
        // Init Table
        applicantCommLetterListTable.init();

        // Filter function
        function filterHTMLFormCML() {
            applicantCommLetterListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-CML").on("click", function (event) {
            filterHTMLFormCML();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CML").on("click", function (event) {
            $("#query-CML").val("");
            $("#status-CML").val("1");
            filterHTMLFormCML();
        });

    }

    let letterEditor;
    if($("#letterEditor").length > 0){
        const el = document.getElementById('letterEditor');
        ClassicEditor.create(el).then((editor) => {
            letterEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let mailEditor;
    if($("#mailEditor").length > 0){
        const el = document.getElementById('mailEditor');
        ClassicEditor.create(el).then((editor) => {
            mailEditor = editor;
            $(el).closest(".editor").find(".document-editor__toolbar").append(editor.ui.view.toolbar.element);
        }).catch((error) => {
            console.error(error);
        });
    }

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        dropdownParent: 'body',
        dropdownClass: 'ts-dropdown lcc-tom-float',
        //persist: false,
        create: false,
        allowEmptyOption: false,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let letter_set_id = new TomSelect('#letter_set_id', tomOptions);
    let sms_template_id = new TomSelect('#sms_template_id', tomOptions);
    let email_template_id = new TomSelect('#email_template_id', tomOptions);


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addLetterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLetterModal"));
    const sendEmailModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#sendEmailModal"));
    const smsSMSModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#smsSMSModal"));
    const viewCommunicationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewCommunicationModal"));

    commDecorateModalButtons();

    const addLetterModalEl = document.getElementById('addLetterModal')
    addLetterModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addLetterModal .acc__input-error').html('');
        $('#addLetterModal .modal-body input').val('');
        $('#addLetterModal .modal-body select').val('');
        $('#addLetterModal .modal-footer input#is_send_email').prop('checked', true);
        $('#addLetterModal .letterEditorArea').fadeOut();
        letterEditor.setData('');
        letter_set_id.clear(true);
    });

    const sendEmailModalEl = document.getElementById('sendEmailModal')
    sendEmailModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#sendEmailModal .acc__input-error').html('');
        $('#sendEmailModal .modal-body input#sendMailsDocument').val('');
        $('#sendEmailModal .modal-body input, #sendEmailModal .modal-body select').val('');
        $('#sendEmailModal .sendMailsDocumentNames').html('').fadeOut();
        mailEditor.setData('');
        email_template_id.clear(true);
    });

    const smsSMSModalEl = document.getElementById('smsSMSModal')
    smsSMSModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#smsSMSModal .acc__input-error').html('');
        $('#smsSMSModal .modal-body input, #smsSMSModal .modal-body textarea').val('');
        sms_template_id.clear(true);
    });

    const confirmModalEl = document.getElementById('confirmModal')
    confirmModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#confirmModal .confModDesc").html('');
        $("#confirmModal .agreeWith").attr('data-recordid', '0');
        $("#confirmModal .agreeWith").attr('data-status', 'none');
        $('#confirmModal button').removeAttr('disabled');
    });

    const viewCommunicationModalEl = document.getElementById('viewCommunicationModal')
    viewCommunicationModalEl.addEventListener('hide.tw.modal', function(event) {
        $("#viewCommunicationModal .modal-body").html('');
        $('#viewCommunicationModal .modal-header h2').html('View Communication');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#sendEmailForm #sendMailsDocument').on('change', function(){
        var inputs = document.getElementById('sendMailsDocument');
        var files = Array.prototype.slice.call(inputs.files || []);

        if (!files.length) {
            $('#sendEmailForm .sendMailsDocumentNames').html('').fadeOut();
            return;
        }

        var html = files.map(function(file) {
            return '<div class="adm-mail-upload__file">' +
                '<span class="adm-mail-upload__file-icon"><i data-lucide="file"></i></span>' +
                '<span class="adm-mail-upload__file-name">' + commEscape(file.name) + '</span>' +
                '<span class="adm-mail-upload__file-size">' + commEscape(commFormatFileSize(file.size)) + '</span>' +
            '</div>';
        }).join('');

        $('#sendEmailForm .sendMailsDocumentNames').fadeIn().html(html);
        commRenderLucide();
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })
    
    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    });

    $('#sendEmailForm [name="email_template_id"]').on('change', function(){
        var emailTemplateID = $(this).val();
        if(emailTemplateID != ''){
            axios({
                method: "post",
                url: route('admission.communication.get.mail.template'),
                data: {emailTemplateID : emailTemplateID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    if(response.data.row.description){
                        mailEditor.setData(response.data.row.description);
                    }else{
                        mailEditor.setData('');
                    }
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            mailEditor.setData('');
        }
    })

    $('#sendEmailForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('sendEmailForm');
    
        commSetButtonBusy('#sendEmailBtn', true);

        let form_data = new FormData(form);
        form_data.append('file', $('#sendEmailForm input#sendMailsDocument')[0].files[0]); 
        form_data.append('body', mailEditor.getData()); 
        axios({
            method: "post",
            url: route('admission.communication.send.mail'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            commSetButtonBusy('#sendEmailBtn', false);
            //console.log(response.data.message);
            //return false;

            if (response.status == 200) {
                sendEmailModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Mail successfully sent to applicant.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            applicantCommEmailListTable.init();
        }).catch(error => {
            commSetButtonBusy('#sendEmailBtn', false);
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#sendEmailForm .${key}`).addClass('border-danger');
                        $(`#sendEmailForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#applicantCommEmailListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Mail from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETEMAIL');
        });
    });

    $('#applicantCommEmailListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this Mail from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTOREMAIL');
        });
    });

    $('#applicantCommEmailListTable').on('click', '.view_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        viewCommunicationModal.show();
        axios({
            method: 'post',
            url: route('admission.communication.mail.show'),
            data: {recordId : recordId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#viewCommunicationModal .modal-header h2').html(response.data.heading);
                $('#viewCommunicationModal .modal-body').html(response.data.html);

                commRenderLucide();
            }
        }).catch(error =>{
            console.log(error)
        });
    });

    $('#smsTextArea').on('keyup', function(){
        var maxlength = ($(this).attr('maxlength') > 0 && $(this).attr('maxlength') != '' ? $(this).attr('maxlength') : 0);
        var chars = this.value.length,
            messages = Math.ceil(chars / 160),
            remaining = messages * 160 - (chars % (messages * 160) || messages * 160);
        if(chars > 0){
            if(chars >= maxlength && maxlength > 0){
                $('#smsSMSModal .modal-content .smsWarning').remove();
                $('#smsSMSModal .modal-content').prepend('<div class="alert smsWarning alert-danger-soft show flex items-center mb-0" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i>Opps! Your maximum character limit exceeded. Please make the text short or contact with administrator.</div>').fadeIn();
            }else{
                $('#smsSMSModal .modal-content .smsWarning').remove();
            }
            $('#smsSMSModal .sms_countr').html(remaining +' / '+messages);
        }else{
            $('#smsSMSModal .sms_countr').html('160 / 1');
            $('#smsSMSModal .modal-content .smsWarning').remove();
        }
    });

    $('#smsSMSForm [name="sms_template_id"]').on('change', function(){
        var smsTemplateId = $(this).val();
        if(smsTemplateId != ''){
            axios({
                method: "post",
                url: route('admission.communication.get.sms.template'),
                data: {smsTemplateId : smsTemplateId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('#smsSMSForm #smsTextArea').val(response.data.row.description ? response.data.row.description : '').trigger('keyup');
            }).catch(error => {
                //console.log('error');
            })
        }else{
            $('#smsSMSForm #smsTextArea').val('');
            $('#smsSMSModal .sms_countr').html('160 / 1');
        }
    })

    $('#smsSMSForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('smsSMSForm');
    
        commSetButtonBusy('#sendSMSBtn', true);

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('admission.communication.send.sms'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            commSetButtonBusy('#sendSMSBtn', false);

            if (response.status == 200) {
                //console.log(response.data);
                smsSMSModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            applicantCommSMSListTable.init();
        }).catch(error => {
            commSetButtonBusy('#sendSMSBtn', false);
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#smsSMSForm .${key}`).addClass('border-danger');
                        $(`#smsSMSForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#applicantCommSMSListTable').on('click', '.view_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        viewCommunicationModal.show();
        axios({
            method: 'post',
            url: route('admission.communication.sms.show'),
            data: {recordId : recordId},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                $('#viewCommunicationModal .modal-header h2').html(response.data.heading);
                $('#viewCommunicationModal .modal-body').html(response.data.html);

                commRenderLucide();
            }
        }).catch(error =>{
            console.log(error)
        });
    });

    $('#applicantCommSMSListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this SMS from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETESMS');
        });
    });

    $('#applicantCommSMSListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this SMS from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTORESMS');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr('data-recordid');
        let action = $agreeBTN.attr('data-status');
        let applicant = $agreeBTN.attr('data-applicant');

        $('#confirmModal button').attr('disabled', 'disabled');

        if(action == 'DELETEMAIL'){
            axios({
                method: 'delete',
                url: route('admission.communication.mail.destroy'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommEmailListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Mail successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'DELETESMS'){
            axios({
                method: 'delete',
                url: route('admission.communication.sms.destroy'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommSMSListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication SMS successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'DELETELETTER'){
            axios({
                method: 'delete',
                url: route('admission.communication.letter.destroy'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommLetterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Letter successfully deleted.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTOREMAIL'){
            axios({
                method: 'post',
                url: route('admission.communication.mail.restore'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommEmailListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Mail successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORESMS'){
            axios({
                method: 'post',
                url: route('admission.communication.sms.restore'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommSMSListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication SMS successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORELETTER'){
            axios({
                method: 'post',
                url: route('admission.communication.letter.restore'),
                data: {applicant : applicant, recordid : recordid},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();
                    applicantCommLetterListTable.init();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Done!');
                        $('#successModal .successModalDesc').html('Applicant Communication Letter successfully resotred.');
                        $('#successModal .successCloser').attr('data-action', 'NONE');
                    });

                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    });


    /* Letter Area */
    $('#addLetterModal #letter_set_id').on('change', function(){
        var letterSetId = $(this).val();
        if(letterSetId > 0){
            axios({
                method: 'post',
                url: route('admission.communication.get.letter.set'),
                data: {letterSetId : letterSetId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#addLetterModal .letterEditorArea').fadeIn('fast', function(){
                        var description = response.data.res.description ? response.data.res.description : '';
                        letterEditor.setData(description)
                    })
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            $('#addLetterModal .letterEditorArea').fadeOut('fast', function(){
                letterEditor.setData('')
            })
        }
    });

    $('#addLetterForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addLetterForm');
    
        commSetButtonBusy('#sendLetterBtn', true);

        let form_data = new FormData(form);
        form_data.append('letter_body', letterEditor.getData()); 
        axios({
            method: "post",
            url: route('admission.communication.send.letter'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            commSetButtonBusy('#sendLetterBtn', false);

            if (response.status == 200) {
                addLetterModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Letter successfully generated and send.');
                    $("#successModal .successCloser").attr('data-action', 'DISMISS');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            applicantCommLetterListTable.init();
        }).catch(error => {
            commSetButtonBusy('#sendLetterBtn', false);
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addLetterForm .${key}`).addClass('border-danger');
                        $(`#addLetterForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#applicantCommLetterListTable').on('click', '.delete_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to delete this Letter from applicant list? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'DELETELETTER');
        });
    });

    $('#applicantCommLetterListTable').on('click', '.restore_btn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var recordId = $btn.attr('data-id');

        confirmModal.show();
        document.getElementById("confirmModal").addEventListener("shown.tw.modal", function (event) {
            $("#confirmModal .confModTitle").html("Are you sure?" );
            $("#confirmModal .confModDesc").html('Want to restore this LETTER from the trash? Please click on agree to continue.');
            $("#confirmModal .agreeWith").attr('data-recordid', recordId);
            $("#confirmModal .agreeWith").attr('data-status', 'RESTORELETTER');
        });
    });

    $('#applicantCommLetterListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('admission.document.download'),
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });

    $('#viewCommunicationModal').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('admission.document.download'), 
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });
    /* Letter Area */

})();
