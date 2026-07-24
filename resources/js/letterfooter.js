import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";

("use strict");

const escapeHtml = (value) => {
    if (value === null || value === undefined || value === "") {
        return "&mdash;";
    }

    return String(value)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
};

const isYes = (value) => String(value || "").toLowerCase() === "yes" || value === true || Number(value) === 1;

const formatAudience = (cell) => {
    const enabled = isYes(cell.getValue());

    return `<span class="ss-letter-audience-pill ${enabled ? "is-yes" : "is-no"}"><i data-lucide="${enabled ? "check" : "x"}"></i>${enabled ? "Yes" : "No"}</span>`;
};

const formatName = (cell) => {
    const data = cell.getData();
    const fileName = data.current_file_name ? `<small>${escapeHtml(data.current_file_name)}</small>` : "";

    return `<span class="ss-letter-asset-name"><strong>${escapeHtml(data.name)}</strong>${fileName}</span>`;
};

const formatFile = (cell) => {
    const data = cell.getData();

    if (!data.url) {
        return '<span class="ss-letter-file-empty"><i data-lucide="image-off"></i>No file</span>';
    }

    const url = escapeHtml(data.url);

    return `<a class="ss-letter-file-preview" href="${url}" target="_blank" rel="noopener" download>
        <span class="ss-letter-file-thumb"><img src="${url}" alt="${escapeHtml(data.name)}"></span>
        <span><strong>Preview</strong><small>Download artwork</small></span>
    </a>`;
};

var letterFooterListTable = (function () {
    var _tableGen = function () {
        let queryStr = $("#query-FOOTER").val() != "" ? $("#query-FOOTER").val() : "";
        let status = $("#status-FOOTER").val() != "" ? $("#status-FOOTER").val() : "1";

        if (window.letterFooterTableInstance) {
            window.letterFooterTableInstance.destroy();
        }

        let tableContent = new Tabulator("#letterFooterListTable", {
            ajaxURL: route("letterfooter.list"),
            ajaxParams: { queryStr: queryStr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    headerHozAlign: "left",
                    width: 68,
                    minWidth: 62,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 230,
                    widthGrow: 1.5,
                    formatter: formatName,
                },
                {
                    title: "Letter",
                    field: "for_letter",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 100,
                    widthGrow: 0.55,
                    formatter: formatAudience,
                },
                {
                    title: "Email",
                    field: "for_email",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 100,
                    widthGrow: 0.55,
                    formatter: formatAudience,
                },
                {
                    title: "Staff",
                    field: "for_staff",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 100,
                    widthGrow: 0.55,
                    formatter: formatAudience,
                },
                {
                    title: "File",
                    field: "url",
                    headerHozAlign: "left",
                    minWidth: 230,
                    widthGrow: 1.25,
                    formatter: formatFile,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 104,
                    minWidth: 104,
                    download: false,
                    formatter(cell) {
                        const data = cell.getData();
                        let btns = "";

                        if (data.url) {
                            btns += `<a target="_blank" rel="noopener" href="${escapeHtml(data.url)}" download class="ss-row-action ss-row-action--view" aria-label="Download footer"><i data-lucide="download"></i></a>`;
                        }

                        if (data.deleted_at == null) {
                            btns += `<button data-id="${data.id}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete footer"><i data-lucide="trash-2"></i></button>`;
                        } else {
                            btns += `<button data-id="${data.id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore footer"><i data-lucide="rotate-cw"></i></button>`;
                        }

                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.7,
                    nameAttr: "data-lucide",
                });
            },
        });

        window.letterFooterTableInstance = tableContent;

        if (window.letterFooterTableResizeHandler) {
            window.removeEventListener("resize", window.letterFooterTableResizeHandler);
        }

        window.letterFooterTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.letterFooterTableResizeHandler);

        $("#tabulator-export-csv-FOOTER").off("click.letterfooter").on("click.letterfooter", function () {
            tableContent.download("csv", "letter-footers.csv");
        });

        $("#tabulator-export-xlsx-FOOTER").off("click.letterfooter").on("click.letterfooter", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "letter-footers.xlsx", {
                sheetName: "Letter Footer Templates",
            });
        });

        $("#tabulator-print-FOOTER").off("click.letterfooter").on("click.letterfooter", function () {
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
    if (!$("#letterFooterListTable").length) {
        return;
    }

    letterFooterListTable.init();

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const uploadLetterFooterModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadLetterFooterModal"));
    let dzHasError = false;
    let footerDropzone = null;

    const setBusy = ($button, isBusy) => {
        $button.prop("disabled", isBusy);
        $button.find(".ss-spinner").css("display", isBusy ? "inline-block" : "none");
    };

    const showSuccess = (title, message) => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(message);
        successModal.show();
    };

    const showWarning = (title, message) => {
        $("#warningModal .warningModalTitle").html(title);
        $("#warningModal .warningModalDesc").html(message);
        warningModal.show();
    };

    const syncAudienceFields = ($modal) => {
        $modal.find(".letter_for_options").each(function () {
            const inputName = $(this).val();
            $modal.find(`input[name="${inputName}"]`).val($(this).prop("checked") ? "Yes" : "No");
        });
    };

    const clearUploadErrors = ($modal) => {
        $modal.find(".ss-upload-alert").remove();
        $modal.find(".acc__input-error").html("");
        $modal.find(".border-danger").removeClass("border-danger");
        $modal.find(".ss-letter-audience-field").removeClass("is-danger");
        $modal.find(".ss-letter-upload-dropzone").removeClass("is-danger");
    };

    const showInlineError = ($modal, message) => {
        $modal.find(".ss-upload-alert").remove();
        $modal.find(".ss-settings-modal__body").prepend(
            `<div class="ss-upload-alert"><i data-lucide="alert-octagon"></i><span>${escapeHtml(message)}</span></div>`
        );
        createIcons({
            icons,
            "stroke-width": 1.7,
            nameAttr: "data-lucide",
        });
    };

    const resetUploadModal = () => {
        const $modal = $("#uploadLetterFooterModal");

        clearUploadErrors($modal);
        $modal.find('input[name="footer_display_name"]').val("");
        $modal.find('input[name="name"]').val("");
        $modal.find('input[name="for_letter"]').val("No");
        $modal.find('input[name="for_email"]').val("No");
        $modal.find('input[name="for_staff"]').val("No");
        $modal.find("input.letter_for_options").prop("checked", false);
        setBusy($("#uploadFooterBtn"), false);
        dzHasError = false;

        if (footerDropzone) {
            footerDropzone.removeAllFiles(true);
        }
    };

    const validateUpload = () => {
        const $modal = $("#uploadLetterFooterModal");
        let valid = true;

        clearUploadErrors($modal);

        if (!$modal.find('input[name="footer_display_name"]').val().trim()) {
            $modal.find('input[name="footer_display_name"]').addClass("border-danger");
            $modal.find(".error-name").html("The name field is required.");
            valid = false;
        }

        if ($modal.find(".letter_for_options:checked").length === 0) {
            $modal.find(".ss-letter-audience-field").addClass("is-danger");
            $modal.find(".error-footer_dispaly_for").html("Please select at least one availability option.");
            valid = false;
        }

        if (!footerDropzone || footerDropzone.getAcceptedFiles().length === 0) {
            $modal.find(".ss-letter-upload-dropzone").addClass("is-danger");
            $modal.find(".error-file").html("Please upload a footer image.");
            valid = false;
        }

        if (!valid) {
            showInlineError($modal, "Please fill out all required fields.");
        }

        return valid;
    };

    function filterHTMLFormUP() {
        letterFooterListTable.init();
    }

    $("#tabulatorFilterForm-FOOTER")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLFormUP();
        }
    });

    $("#tabulator-html-filter-go-FOOTER").on("click", function () {
        filterHTMLFormUP();
    });

    $("#tabulator-html-filter-reset-FOOTER").on("click", function () {
        $("#query-FOOTER").val("");
        $("#status-FOOTER").val("1");
        filterHTMLFormUP();
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .confModDesc").html("");
        $("#confirmModal .agreeWith").attr("data-recordid", "0");
        $("#confirmModal .agreeWith").attr("data-status", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#confirmModal .disAgreeWith").on("click", function (e) {
        e.preventDefault();
        confirmModal.hide();
    });

    if ($("#uploadLetterFootForm").length > 0) {
        Dropzone.autoDiscover = false;

        const formEl = document.querySelector("#uploadLetterFootForm");
        if (formEl.dropzone) {
            formEl.dropzone.destroy();
        }

        footerDropzone = new Dropzone("#uploadLetterFootForm", {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 20,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            addRemoveLinks: true,
            thumbnailWidth: 140,
            thumbnailHeight: 90,
        });

        footerDropzone.on("addedfile", () => {
            clearUploadErrors($("#uploadLetterFooterModal"));
        });

        footerDropzone.on("maxfilesexceeded", (file) => {
            showInlineError($("#uploadLetterFooterModal"), "Only one footer image can be uploaded at a time.");
            footerDropzone.removeFile(file);
        });

        footerDropzone.on("error", () => {
            dzHasError = true;
        });

        footerDropzone.on("success", (file) => {
            file.previewElement.classList.add("dz-success");
        });

        footerDropzone.on("queuecomplete", function () {
            setBusy($("#uploadFooterBtn"), false);

            if (dzHasError) {
                showWarning("Upload Failed", "Something went wrong while uploading this footer. Please try again.");
                return;
            }

            uploadLetterFooterModal.hide();
            letterFooterListTable.init();
            showSuccess("Success!", "Letter footer successfully uploaded.");
        });
    }

    document.getElementById("uploadLetterFooterModal").addEventListener("show.tw.modal", function () {
        resetUploadModal();
    });

    document.getElementById("uploadLetterFooterModal").addEventListener("hide.tw.modal", function () {
        resetUploadModal();
    });

    $("#uploadLetterFooterModal [name='footer_display_name']").on("input paste change", function () {
        $("#uploadLetterFooterModal [name='name']").val($(this).val());
        $(this).removeClass("border-danger");
        $("#uploadLetterFooterModal .error-name").html("");
    });

    $("#uploadLetterFooterModal .letter_for_options").on("change", function () {
        syncAudienceFields($("#uploadLetterFooterModal"));
        $("#uploadLetterFooterModal .ss-letter-audience-field").removeClass("is-danger");
        $("#uploadLetterFooterModal .error-footer_dispaly_for").html("");
    });

    $("#uploadFooterBtn").on("click", function (e) {
        e.preventDefault();
        syncAudienceFields($("#uploadLetterFooterModal"));
        dzHasError = false;

        if (!validateUpload()) {
            return;
        }

        setBusy($("#uploadFooterBtn"), true);
        footerDropzone.processQueue();
    });

    $("#letterFooterListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        const uploadId = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html("Are you sure?");
        $("#confirmModal .confModDesc").html("Do you really want to delete this letter footer?");
        $("#confirmModal .agreeWith").attr("data-recordid", uploadId);
        $("#confirmModal .agreeWith").attr("data-status", "DELETE");
        confirmModal.show();
    });

    $("#letterFooterListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        const uploadId = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html("Are you sure?");
        $("#confirmModal .confModDesc").html("Do you really want to restore this letter footer?");
        $("#confirmModal .agreeWith").attr("data-recordid", uploadId);
        $("#confirmModal .agreeWith").attr("data-status", "RESTORE");
        confirmModal.show();
    });

    $("#confirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();
        const $agreeBTN = $(this);
        const recordid = $agreeBTN.attr("data-recordid");
        const action = $agreeBTN.attr("data-status");

        $("#confirmModal button").attr("disabled", "disabled");

        const done = (title, message) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            letterFooterListTable.init();
            showSuccess(title, message);
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action === "DELETE") {
            axios({
                method: "delete",
                url: route("letterheaderfooter.destory.uploads"),
                data: { recordid: recordid },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "Letter footer successfully deleted.");
                }
            }).catch(failed);
        } else if (action === "RESTORE") {
            axios({
                method: "post",
                url: route("letterheaderfooter.resotore.uploads"),
                data: { recordid: recordid },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Letter footer successfully restored.");
                }
            }).catch(failed);
        } else {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
        }
    });
})();
