import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { each } from "jquery";
import Dropzone from "dropzone";

("use strict");

function uploadEscape(value) {
    return String(value == null ? "" : value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
}

function uploadInitials(name) {
    const initials = String(name || "")
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join("");

    return initials || "?";
}

function uploadAvatarColor(seed) {
    const palette = ["#0d7a76", "#6d4bb0", "#2f7d4f", "#c0562a", "#2f8f7d", "#3a6ea5"];
    const text = String(seed || "");
    let hash = 0;

    for (let i = 0; i < text.length; i += 1) {
        hash = (hash + text.charCodeAt(i) * (i + 1)) % palette.length;
    }

    return palette[hash];
}

function uploadRenderLucide() {
    createIcons({
        icons,
        "stroke-width": 1.8,
        nameAttr: "data-lucide",
    });
}

function uploadPersonHtml(name, date, seed) {
    const safeName = uploadEscape(name || "Unknown");
    const safeDate = uploadEscape(date || "");

    return '<div class="adm-upload-by">' +
        '<span class="adm-upload-by__avatar" style="background:' + uploadAvatarColor(seed || name) + ';">' + uploadInitials(name) + '</span>' +
        '<span class="adm-upload-by__text">' +
            '<span class="adm-upload-by__name">' + safeName + '</span>' +
            '<span class="adm-upload-by__date">' + safeDate + '</span>' +
        '</span>' +
    '</div>';
}

function uploadDocIcon(type) {
    const ext = String(type || "").toLowerCase();
    if (["jpg", "jpeg", "png", "gif", "webp"].includes(ext)) return "image";
    if (["xls", "xlsx", "csv"].includes(ext)) return "sheet";
    if (["doc", "docx"].includes(ext)) return "doc";
    if (["ppt", "pptx"].includes(ext)) return "slides";
    if (ext === "pdf") return "pdf";
    return "file";
}

function uploadDocumentHtml(data) {
    const type = uploadEscape(String(data.doc_type || "FILE").toUpperCase());
    const title = uploadEscape(data.display_file_name || "Unknown");
    const fileName = uploadEscape(data.current_file_name || "");
    const icon = uploadDocIcon(data.doc_type);

    return '<div class="adm-upload-doc adm-upload-doc--' + icon + '">' +
        '<span class="adm-upload-doc__icon"><i data-lucide="' + (icon === "image" ? "image" : "file") + '"></i></span>' +
        '<span class="adm-upload-doc__text">' +
            '<span class="adm-upload-doc__title">' + title + '</span>' +
            '<span class="adm-upload-doc__meta">' + type + (fileName ? " · " + fileName : "") + '</span>' +
        '</span>' +
    '</div>';
}

function uploadHardCopyHtml(value) {
    const checked = Number(value) === 1;
    return '<span class="adm-upload-yn adm-upload-yn--' + (checked ? "yes" : "no") + '">' +
        '<span class="adm-upload-yn__icon"><i data-lucide="' + (checked ? "check" : "x") + '"></i></span>' +
        '<span>' + (checked ? "Yes" : "No") + '</span>' +
    '</span>';
}

function uploadActionButton(action) {
    const tag = action.tag || "button";
    const attrs = [
        'data-id="' + uploadEscape(action.id) + '"',
        'class="' + uploadEscape(action.className) + ' adm-row-action adm-row-action--' + uploadEscape(action.type) + '"',
        'title="' + uploadEscape(action.title) + '"',
        'aria-label="' + uploadEscape(action.title) + '"',
    ];

    if (tag === "button") attrs.push('type="button"');
    if (tag === "a") attrs.push('href="javascript:void(0);"');

    return '<' + tag + ' ' + attrs.join(" ") + '>' +
        '<i data-lucide="' + uploadEscape(action.icon) + '"></i>' +
    '</' + tag + '>';
}

function uploadActions(actions) {
    return '<div class="adm-row-actions">' +
        actions.filter(Boolean).map(uploadActionButton).join("") +
    '</div>';
}

function uploadSetButtonBusy(selector, busy) {
    const button = document.querySelector(selector);
    if (!button) return;

    button.toggleAttribute("disabled", busy);
    const loader = button.querySelector(".adm-btn-loader");
    if (loader) {
        loader.style.display = busy ? "inline-block" : "none";
    }
}

function uploadDecorateModalButtons(scope = document) {
    scope.querySelectorAll(".modal-footer .btn, .modal-body.p-0 .px-5.pb-8.text-center .btn").forEach((button) => {
        if (button.querySelector(".adm-btn-icon")) return;

        const text = button.textContent.trim().toLowerCase();
        let icon = "check";

        if (text.includes("cancel") || text.includes("no,")) icon = "x";
        else if (text.includes("upload")) icon = "upload-cloud";
        else if (text.includes("agree")) icon = "check";

        const iconEl = document.createElement("i");
        iconEl.setAttribute("data-lucide", icon);
        iconEl.className = "adm-btn-icon";
        button.prepend(iconEl);
    });

    uploadRenderLucide();
}

var applicantUploadListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let applicantId =
            $("#applicantUploadListTable").attr("data-applicant") != ""
                ? $("#applicantUploadListTable").attr("data-applicant")
                : "0";
        let queryStr = $("#query-UP").val() != "" ? $("#query-UP").val() : "";
        let status = $("#status-UP").val() != "" ? $("#status-UP").val() : "1";

        let tableContent = new Tabulator("#applicantUploadListTable", {
            ajaxURL: route("admission.uploads.list"),
            ajaxParams: {
                applicantId: applicantId,
                queryStr: queryStr,
                status: status,
            },
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
                    title: "Name",
                    field: "display_file_name",
                    headerHozAlign: "left",
                    minWidth: 320,
                    formatter(cell) {
                        return uploadDocumentHtml(cell.getData());
                    },
                },
                {
                    title: "Checked",
                    field: "hard_copy_check",
                    headerHozAlign: "left",
                    width: 150,
                    formatter(cell) {
                        return uploadHardCopyHtml(cell.getData().hard_copy_check);
                    },
                },
                {
                    title: "Uploaded By",
                    field: "created_by",
                    headerHozAlign: "left",
                    minWidth: 220,
                    formatter(cell) {
                        return uploadPersonHtml(cell.getData().created_by, cell.getData().created_at, cell.getData().id);
                    },
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "130",
                    download: false,
                    formatter(cell, formatterParams) {
                        return uploadActions([
                            {
                                tag: "a",
                                type: "download",
                                className: "downloadDoc",
                                icon: "download",
                                id: cell.getData().id,
                                title: "Download",
                            },
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
                uploadRenderLucide();
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                }
            },
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            uploadRenderLucide();
        });

        // Export
        $("#tabulator-export-csv-UP").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-UP").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-UP").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Admission Uploads Details",
            });
        });

        $("#tabulator-export-html-UP").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-UP").on("click", function (event) {
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
    if ($("#applicantUploadListTable").length) {
        // Init Table
        applicantUploadListTable.init();

        // Filter function
        function filterHTMLFormUP() {
            applicantUploadListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go-UP").on("click", function (event) {
            filterHTMLFormUP();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-UP").on("click", function (event) {
            $("#query-UP").val("");
            $("#status-UP").val("1");
            filterHTMLFormUP();
        });
    }
    const successModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector("#successModal")
    );
    const confirmModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector("#confirmModal")
    );
    const warningModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector("#warningModal")
    );
    const uploadsDropdown = tailwind.Dropdown.getOrCreateInstance(
        document.querySelector("#uploadsDropdown")
    );
    const uploadDocumentModal = tailwind.Modal.getOrCreateInstance(
        document.querySelector("#uploadDocumentModal")
    );

    uploadDecorateModalButtons();

    const uploadDocumentModalEl = document.getElementById(
        "uploadDocumentModal"
    );
    uploadDocumentModalEl.addEventListener("hide.tw.modal", function (event) {
        $('#uploadDocumentModal input[name="document_setting_id"]').val("0");
        $('#uploadDocumentModal input[name="hard_copy_check"]').val("0");
        $('#uploadDocumentModal input[name="display_file_name"]').val("");
        $('#uploadDocumentModal input[name="display_name"]').val("");
        $(
            '#uploadDocumentModal input[name="hard_copy_check_status"][value="0"]'
        ).prop("checked", true);
        $("#documentNameDisplay").text("");
        uploadSetButtonBusy("#uploadDocBtn", false);
    });
    const confirmModalEl = document.getElementById("confirmModal");
    confirmModalEl.addEventListener("hide.tw.modal", function (event) {
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
        if ($(this).attr("data-action") == "RELOAD") {
            successModal.hide();
            window.location.reload();
        } else {
            successModal.hide();
        }
    });

    $("#warningModal .warningCloser").on("click", function (e) {
        e.preventDefault();
        if ($(this).attr("data-action") == "RELOAD") {
            warningModal.hide();
            window.location.reload();
        } else {
            warningModal.hide();
        }
    });

    /* Start Dropzone */
    if ($("#uploadDocumentForm").length > 0) {
        let dzError = false;
        Dropzone.autoDiscover = false;
        const uploadDocumentDropzoneOptions = {
            autoProcessQueue: false,
            maxFiles: 10,
            maxFilesize: 5,
            parallelUploads: 10,
            acceptedFiles:
                ".jpeg,.jpg,.png,.gif,.pdf,.xl,.xls,.xlsx,.doc,.docx,.ppt,.pptx,.txt",
            addRemoveLinks: true,
            thumbnailWidth: 100,
            thumbnailHeight: 100,
            accept: (file, done) => {
                console.log("Uploaded");
                done();
            },
            /*accept: function(file, done) {
                if(!file.name.match(/[`!@#$%^&*+\-=\[\]{};':"\\|,<>\/?~]/)){
                    alert("Invalid File Name");
                    done('Invalid file name');
                }else { 
                    done(); 
                }
            },*/
        };

        var drzn1 = new Dropzone("#uploadDocumentForm", uploadDocumentDropzoneOptions);

        drzn1.on("addedfile", function (file) {
            if (file.name.match(/[`!@#$%^&*+\=\[\]{};':"\\|,<>\/?~]/)) {
                $("#uploadDocumentModal .modal-content .uploadError").remove();
                $("#uploadDocumentModal .modal-content").prepend(
                    '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! One of your selected file name contain validation error & that file has been removed.</div>'
                );
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
                drzn1.removeFile(file);

                setTimeout(function () {
                    $(
                        "#uploadDocumentModal .modal-content .uploadError"
                    ).remove();
                }, 5000);
            }
        });

        drzn1.on("maxfilesexceeded", (file) => {
            $("#uploadDocumentModal .modal-content .uploadError").remove();
            $("#uploadDocumentModal .modal-content").prepend(
                '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Can not upload more than 10 files at a time.</div>'
            );
            drzn1.removeFile(file);
            setTimeout(function () {
                $("#uploadDocumentModal .modal-content .uploadError").remove();
            }, 2000);
        });

        drzn1.on("error", function (file, response) {
            dzError = true;
        });

        drzn1.on("success", function (file, response) {
            //console.log(response);
            return file.previewElement.classList.add("dz-success");
        });

        drzn1.on("complete", function (file) {
            //drzn1.removeFile(file);
        });

        drzn1.on("queuecomplete", function () {
            uploadSetButtonBusy("#uploadDocBtn", false);

            uploadDocumentModal.hide();
            if (!dzError) {
                successModal.show();
                document
                    .getElementById("successModal")
                    .addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html(
                            "Congratulation!"
                        );
                        $("#successModal .successModalDesc").html(
                            "Applicant document successfully uploaded."
                        );
                        $("#successModal .successCloser").attr(
                            "data-action",
                            "RELOAD"
                        );
                    });

                setTimeout(function () {
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            } else {
                warningModal.show();
                document
                    .getElementById("warningModal")
                    .addEventListener("shown.tw.modal", function (event) {
                        $("#warningModal .warningModalTitle").html(
                            "Error Found!"
                        );
                        $("#warningModal .warningModalDesc").html(
                            "Something went wrong. Please try later or contact administrator."
                        );
                        $("#warningModal .warningCloser").attr(
                            "data-action",
                            "DISMISS"
                        );
                    });
                setTimeout(function () {
                    warningModal.hide();
                    //window.location.reload();
                }, 2000);
            }
        });

        $('#uploadDocumentModal [name="display_name"]').on(
            "keyup",
            function () {
                $('#uploadDocumentModal [name="display_file_name"]').val(
                    $(this).val()
                );
            }
        );

        $("#uploadDocBtn").on("click", function (e) {
            e.preventDefault();
            uploadSetButtonBusy("#uploadDocBtn", true);

            if (drzn1.files.length > 0) {
                if (
                    $(
                        '#uploadDocumentModal [name="hard_copy_check_status"]:checked'
                    ).length > 0
                ) {
                    var hardCopyChecked = $(
                        '#uploadDocumentModal [name="hard_copy_check_status"]:checked'
                    ).val();
                    $('#uploadDocumentModal input[name="hard_copy_check"]').val(
                        hardCopyChecked
                    );
                    drzn1.processQueue();
                } else {
                    $(
                        "#uploadDocumentModal .modal-content .uploadError"
                    ).remove();
                    $("#uploadDocumentModal .modal-content").prepend(
                        '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select the hard copy check status.</div>'
                    );

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    setTimeout(function () {
                        $(
                            "#uploadDocumentModal .modal-content .uploadError"
                        ).remove();
                        uploadSetButtonBusy("#uploadDocBtn", false);
                    }, 2000);
                }
            } else {
                $("#uploadDocumentModal .modal-content .uploadError").remove();
                $("#uploadDocumentModal .modal-content").prepend(
                    '<div class="alert uploadError alert-danger-soft show flex items-start mb-0" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Oops! Please select at least one file.</div>'
                );

                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });

                setTimeout(function () {
                    $(
                        "#uploadDocumentModal .modal-content .uploadError"
                    ).remove();
                    uploadSetButtonBusy("#uploadDocBtn", false);
                }, 2000);
            }
        });
    }
    /* End Dropzone */

    $("#applicantDocumentUploaders").on("click", function (e) {
        e.preventDefault();

        if ($(".applicant_doc_ids:checked").length > 0) {
            uploadDocumentModal.show();
            var documentSettingId = $(".applicant_doc_ids:checked").val();
            $('#uploadDocumentModal input[name="document_setting_id"]').val(
                documentSettingId
            );

            var selectedDocumentID = $('.applicant_doc_ids:checked');
            var documentLabelText = selectedDocumentID.attr('data-label').trim();

            $('#documentNameDisplay').text(documentLabelText);

            $('.displayNameInput').off('input.admUploadName').on('input.admUploadName', function() {
                var displayName = $(this).val();
                var seperator = " ";
                if(displayName.length > 0){
                    seperator = " - ";
                }else{
                    seperator = " ";
                }
                $('#documentNameDisplay').text(documentLabelText + seperator + displayName);
            });



            uploadsDropdown.hide();
            $(".applicant_doc_ids").prop("checked", false);
        } else {
            warningModal.show();
            $("#warningModal .warningModalTitle").html("Oops!");
            $("#warningModal .warningModalDesc").html(
                "Please a document type from the list firs."
            );
            $("#warningModal .warningCloser").attr("data-action", "DISMISS");

            setTimeout(function () {
                warningModal.hide();
            }, 2000);
        }
    });

    $("#applicantUploadListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr("data-id");

        confirmModal.show();
        document
            .getElementById("confirmModal")
            .addEventListener("shown.tw.modal", function (event) {
                $("#confirmModal .confModTitle").html("Are you sure?");
                $("#confirmModal .confModDesc").html(
                    "Want to delete this document from applicant list? Please click on agree to continue."
                );
                $("#confirmModal .agreeWith").attr("data-recordid", uploadId);
                $("#confirmModal .agreeWith").attr("data-status", "DELETEDOC");
            });
    });

    $("#applicantUploadListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        var $btn = $(this);
        var uploadId = $btn.attr("data-id");

        confirmModal.show();
        document
            .getElementById("confirmModal")
            .addEventListener("shown.tw.modal", function (event) {
                $("#confirmModal .confModTitle").html("Are you sure?");
                $("#confirmModal .confModDesc").html(
                    "Want to restore this document from the trash? Please click on agree to continue."
                );
                $("#confirmModal .agreeWith").attr("data-recordid", uploadId);
                $("#confirmModal .agreeWith").attr("data-status", "RESTOREDOC");
            });
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordid = $agreeBTN.attr("data-recordid");
        let action = $agreeBTN.attr("data-status");
        let applicant = $agreeBTN.attr("data-applicant");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETEDOC") {
            axios({
                method: "delete",
                url: route("admission.destory.uploads"),
                data: { applicant: applicant, recordid: recordid },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confirmModal.hide();
                        applicantUploadListTable.init();

                        successModal.show();
                        document
                            .getElementById("successModal")
                            .addEventListener(
                                "shown.tw.modal",
                                function (event) {
                                    $("#successModal .successModalTitle").html(
                                        "Done!"
                                    );
                                    $("#successModal .successModalDesc").html(
                                        "Applicant uploaded document successfully deleted."
                                    );
                                    $("#successModal .successCloser").attr(
                                        "data-action",
                                        "NONE"
                                    );
                                }
                            );

                        setTimeout(function () {
                            successModal.hide();
                        }, 2000);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        } else if (action == "RESTOREDOC") {
            axios({
                method: "post",
                url: route("admission.resotore.uploads"),
                data: { applicant: applicant, recordid: recordid },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content"
                    ),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confirmModal.hide();
                        applicantUploadListTable.init();

                        successModal.show();
                        document
                            .getElementById("successModal")
                            .addEventListener(
                                "shown.tw.modal",
                                function (event) {
                                    $("#successModal .successModalTitle").html(
                                        "Done!"
                                    );
                                    $("#successModal .successModalDesc").html(
                                        "Applicant document successfully resotred."
                                    );
                                    $("#successModal .successCloser").attr(
                                        "data-action",
                                        "NONE"
                                    );
                                }
                            );

                        setTimeout(function () {
                            successModal.hide();
                        }, 2000);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        } else {
            confirmModal.hide();
        }
    });

    $("#applicantUploadListTable").on("click", ".downloadDoc", function (e) {
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr("data-id");

        $theLink.css({ opacity: ".6", cursor: "not-allowed" });

        axios({
            method: "post",
            url: route("admission.document.download"),
            data: { row_id: row_id },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        })
            .then((response) => {
                if (response.status == 200) {
                    let res = response.data.res;
                    $theLink.css({ opacity: "1", cursor: "pointer" });

                    if (res != "") {
                        window.open(res, "_blank");
                    }
                }
            })
            .catch((error) => {
                if (error.response) {
                    $theLink.css({ opacity: "1", cursor: "pointer" });
                    console.log("error");
                }
            });
    });
})();
