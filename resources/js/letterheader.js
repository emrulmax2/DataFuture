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

var letterHeaderListTable = (function () {
    var _tableGen = function () {
        let queryStr = $("#query-HEADER").val() != "" ? $("#query-HEADER").val() : "";
        let status = $("#status-HEADER").val() != "" ? $("#status-HEADER").val() : "1";

        if (window.letterHeaderTableInstance) {
            window.letterHeaderTableInstance.destroy();
        }

        let tableContent = new Tabulator("#letterHeaderListTable", {
            ajaxURL: route("letterheader.list"),
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
                            btns += `<a target="_blank" rel="noopener" href="${escapeHtml(data.url)}" download class="ss-row-action ss-row-action--view" aria-label="Download header"><i data-lucide="download"></i></a>`;
                        }

                        if (data.deleted_at == null) {
                            btns += `<button data-id="${data.id}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete header"><i data-lucide="trash-2"></i></button>`;
                        } else {
                            btns += `<button data-id="${data.id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore header"><i data-lucide="rotate-cw"></i></button>`;
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

        window.letterHeaderTableInstance = tableContent;

        if (window.letterHeaderTableResizeHandler) {
            window.removeEventListener("resize", window.letterHeaderTableResizeHandler);
        }

        window.letterHeaderTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.letterHeaderTableResizeHandler);

        $("#tabulator-export-csv-HEADER").off("click.letterheader").on("click.letterheader", function () {
            tableContent.download("csv", "letter-headers.csv");
        });

        $("#tabulator-export-xlsx-HEADER").off("click.letterheader").on("click.letterheader", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "letter-headers.xlsx", {
                sheetName: "Letter Header Templates",
            });
        });

        $("#tabulator-print-HEADER").off("click.letterheader").on("click.letterheader", function () {
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
    if (!$("#letterHeaderListTable").length) {
        return;
    }

    letterHeaderListTable.init();

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#letterheadConfirmModal"));
    const uploadLetterHeaderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadLetterHeaderModal"));
    let dzHasError = false;
    let headerDropzone = null;

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
        const $modal = $("#uploadLetterHeaderModal");

        clearUploadErrors($modal);
        $modal.find('input[name="display_name"]').val("");
        $modal.find('input[name="name"]').val("");
        $modal.find('input[name="for_letter"]').val("No");
        $modal.find('input[name="for_email"]').val("No");
        $modal.find('input[name="for_staff"]').val("No");
        $modal.find("input.letter_for_options").prop("checked", false);
        setBusy($("#uploadHeaderBtn"), false);
        dzHasError = false;

        if (headerDropzone) {
            headerDropzone.removeAllFiles(true);
        }
    };

    const validateUpload = () => {
        const $modal = $("#uploadLetterHeaderModal");
        let valid = true;

        clearUploadErrors($modal);

        if (!$modal.find('input[name="display_name"]').val().trim()) {
            $modal.find('input[name="display_name"]').addClass("border-danger");
            $modal.find(".error-name").html("The name field is required.");
            valid = false;
        }

        if ($modal.find(".letter_for_options:checked").length === 0) {
            $modal.find(".ss-letter-audience-field").addClass("is-danger");
            $modal.find(".error-for").html("Please select at least one availability option.");
            valid = false;
        }

        if (!headerDropzone || headerDropzone.getAcceptedFiles().length === 0) {
            $modal.find(".ss-letter-upload-dropzone").addClass("is-danger");
            $modal.find(".error-file").html("Please upload a header image.");
            valid = false;
        }

        if (!valid) {
            showInlineError($modal, "Please fill out all required fields.");
        }

        return valid;
    };

    function filterHTMLFormUP() {
        letterHeaderListTable.init();
    }

    $("#tabulatorFilterForm-HEADER")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLFormUP();
        }
    });

    $("#tabulator-html-filter-go-HEADER").on("click", function () {
        filterHTMLFormUP();
    });

    $("#tabulator-html-filter-reset-HEADER").on("click", function () {
        $("#query-HEADER").val("");
        $("#status-HEADER").val("1");
        filterHTMLFormUP();
    });

    document.getElementById("letterheadConfirmModal").addEventListener("hidden.tw.modal", function () {
        $("#letterheadConfirmModal .confModDesc").html("");
        $("#letterheadConfirmModal .agreeWith").attr("data-recordid", "0");
        $("#letterheadConfirmModal .agreeWith").attr("data-status", "none");
        $("#letterheadConfirmModal button").removeAttr("disabled");
    });

    $("#letterheadConfirmModal .disAgreeWith").on("click", function (e) {
        e.preventDefault();
        confirmModal.hide();
    });

    if ($("#uploadLetterHeadForm").length > 0) {
        Dropzone.autoDiscover = false;

        const formEl = document.querySelector("#uploadLetterHeadForm");
        if (formEl.dropzone) {
            formEl.dropzone.destroy();
        }

        headerDropzone = new Dropzone("#uploadLetterHeadForm", {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 20,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            addRemoveLinks: true,
            thumbnailWidth: 140,
            thumbnailHeight: 90,
        });

        headerDropzone.on("addedfile", () => {
            clearUploadErrors($("#uploadLetterHeaderModal"));
        });

        headerDropzone.on("maxfilesexceeded", (file) => {
            showInlineError($("#uploadLetterHeaderModal"), "Only one header image can be uploaded at a time.");
            headerDropzone.removeFile(file);
        });

        headerDropzone.on("error", () => {
            dzHasError = true;
        });

        headerDropzone.on("success", (file) => {
            file.previewElement.classList.add("dz-success");
        });

        headerDropzone.on("queuecomplete", function () {
            setBusy($("#uploadHeaderBtn"), false);

            if (dzHasError) {
                showWarning("Upload Failed", "Something went wrong while uploading this header. Please try again.");
                return;
            }

            uploadLetterHeaderModal.hide();
            letterHeaderListTable.init();
            showSuccess("Success!", "Letter header successfully uploaded.");
        });
    }

    document.getElementById("uploadLetterHeaderModal").addEventListener("show.tw.modal", function () {
        resetUploadModal();
    });

    document.getElementById("uploadLetterHeaderModal").addEventListener("hide.tw.modal", function () {
        resetUploadModal();
    });

    $("#uploadLetterHeaderModal [name='display_name']").on("input paste change", function () {
        $("#uploadLetterHeaderModal [name='name']").val($(this).val());
        $(this).removeClass("border-danger");
        $("#uploadLetterHeaderModal .error-name").html("");
    });

    $("#uploadLetterHeaderModal .letter_for_options").on("change", function () {
        syncAudienceFields($("#uploadLetterHeaderModal"));
        $("#uploadLetterHeaderModal .ss-letter-audience-field").removeClass("is-danger");
        $("#uploadLetterHeaderModal .error-for").html("");
    });

    $("#uploadHeaderBtn").on("click", function (e) {
        e.preventDefault();
        syncAudienceFields($("#uploadLetterHeaderModal"));
        dzHasError = false;

        if (!validateUpload()) {
            return;
        }

        setBusy($("#uploadHeaderBtn"), true);
        headerDropzone.processQueue();
    });

    $("#letterHeaderListTable").on("click", ".delete_btn", function (e) {
        e.preventDefault();
        const uploadId = $(this).attr("data-id");

        $("#letterheadConfirmModal .confModTitle").html("Are you sure?");
        $("#letterheadConfirmModal .confModDesc").html("Do you really want to delete this letter header?");
        $("#letterheadConfirmModal .agreeWith").attr("data-recordid", uploadId);
        $("#letterheadConfirmModal .agreeWith").attr("data-status", "DELETE");
        confirmModal.show();
    });

    $("#letterHeaderListTable").on("click", ".restore_btn", function (e) {
        e.preventDefault();
        const uploadId = $(this).attr("data-id");

        $("#letterheadConfirmModal .confModTitle").html("Are you sure?");
        $("#letterheadConfirmModal .confModDesc").html("Do you really want to restore this letter header?");
        $("#letterheadConfirmModal .agreeWith").attr("data-recordid", uploadId);
        $("#letterheadConfirmModal .agreeWith").attr("data-status", "RESTORE");
        confirmModal.show();
    });

    $("#letterheadConfirmModal .agreeWith").on("click", function (e) {
        e.preventDefault();
        const $agreeBTN = $(this);
        const recordid = $agreeBTN.attr("data-recordid");
        const action = $agreeBTN.attr("data-status");

        $("#letterheadConfirmModal button").attr("disabled", "disabled");

        const done = (title, message) => {
            $("#letterheadConfirmModal button").removeAttr("disabled");
            confirmModal.hide();
            letterHeaderListTable.init();
            showSuccess(title, message);
        };

        const failed = (error) => {
            $("#letterheadConfirmModal button").removeAttr("disabled");
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
                    done("Done!", "Letter header successfully deleted.");
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
                    done("Success!", "Letter header successfully restored.");
                }
            }).catch(failed);
        } else {
            $("#letterheadConfirmModal button").removeAttr("disabled");
            confirmModal.hide();
        }
    });
})();
