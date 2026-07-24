import Dropzone from "dropzone";
import xlsx from "xlsx";
import { CalendarDays, Check, createElement, createIcons, ExternalLink, icons, Minus, Pencil, Plus, RotateCw, Trash2, X } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

(function () {
    const tableNode = document.querySelector("#internalLinkTableId");

    if (!tableNode) {
        return;
    }

    const csrfToken = $('meta[name="csrf-token"]').attr("content");
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadEmployeeDocumentModal"));
    const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#uploadEmployeeDocumentModalEdit"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let tableContent = null;

    Dropzone.autoDiscover = false;

    const refreshIcons = () => {
        createIcons({
            icons,
            "stroke-width": 1.7,
            nameAttr: "data-lucide",
        });
    };

    const escapeHtml = (value) => {
        return String(value ?? "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const iconSvg = (Icon) => {
        const icon = createElement(Icon);

        icon.setAttribute("stroke-width", "1.8");
        icon.setAttribute("aria-hidden", "true");

        return icon.outerHTML;
    };

    const getInitials = (name) => {
        const words = String(name || "Link").trim().split(/\s+/).filter(Boolean);
        const first = words[0]?.charAt(0) || "L";
        const second = words.length > 1 ? words[words.length - 1].charAt(0) : words[0]?.charAt(1) || "K";

        return `${first}${second}`.toUpperCase();
    };

    const setBusy = (selector, busy) => {
        const button = document.querySelector(selector);

        if (!button) {
            return;
        }

        button.disabled = busy;
        const spinner = button.querySelector(".ss-spinner");

        if (spinner) {
            spinner.style.cssText = busy ? "display: inline-block;" : "display: none;";
        }
    };

    const showSuccess = (title, description) => {
        $("#successModal .successModalTitle").text(title);
        $("#successModal .successModalDesc").text(description);
        successModal.show();
    };

    const showWarning = (title, description) => {
        $("#warningModal .warningModalTitle").text(title);
        $("#warningModal .warningModalDesc").text(description);
        warningModal.show();
    };

    const showConfirm = (title, description, action, recordID) => {
        $("#confirmModal .confModTitle").text(title);
        $("#confirmModal .confModDesc").text(description);
        $("#confirmModal .agreeWith").attr("data-id", recordID);
        $("#confirmModal .agreeWith").attr("data-action", action);
        confirmModal.show();
    };

    const clearErrors = ($modal) => {
        $modal.find(".acc__input-error").text("");
        $modal.find(".border-danger").removeClass("border-danger");
        $modal.find(".ss-upload-alert").remove();
        $modal.find(".ss-internal-link-dropzone").removeClass("is-danger");
    };

    const addFieldError = ($modal, name, message) => {
        $modal.find(`[name="${name}"]`).addClass("border-danger");
        $modal.find(`.error-${name}`).text(message);
    };

    const showUploadAlert = ($modal, message) => {
        $modal.find(".ss-upload-alert").remove();
        $modal.find(".ss-settings-modal__body").prepend(
            `<div class="ss-upload-alert"><i data-lucide="alert-octagon"></i><span>${escapeHtml(message)}</span></div>`
        );
        refreshIcons();
    };

    const resetDropzone = (dropzone) => {
        if (dropzone) {
            dropzone.removeAllFiles(true);
        }
    };

    const syncFormFields = ($modal, formSelector) => {
        const $form = $(formSelector);

        $form.find('[name="name"]').val($modal.find('[name="name_status"]').val() || "");
        $form.find('[name="link"]').val($modal.find('[name="link_status"]').val() || "");
        $form.find('[name="parent_id"]').val($modal.find('[name="parent_category"]').val() || "");
        $form.find('[name="available_staff"]').val($modal.find('[name="available_staff_status"]').is(":checked") ? 1 : "");
        $form.find('[name="available_student"]').val($modal.find('[name="available_student_status"]').is(":checked") ? 1 : "");
        $form.find('[name="description"]').val($modal.find('[name="description_status"]').val() || "");
        $form.find('[name="start_date"]').val($modal.find('[name="start_date_status"]').val() || "");
        $form.find('[name="end_date"]').val($modal.find('[name="end_date_status"]').val() || "");
        $form.find('[name="active"]').val($modal.find('[name="active_status"]').is(":checked") ? 1 : "");
    };

    const validateModal = ($modal, dropzone, requireImage) => {
        clearErrors($modal);

        let valid = true;

        if (!($modal.find('[name="name_status"]').val() || "").trim()) {
            addFieldError($modal, "name_status", "Name is required.");
            valid = false;
        }

        if (requireImage && dropzone.getQueuedFiles().length === 0 && dropzone.files.length === 0) {
            $modal.find(".ss-internal-link-dropzone").addClass("is-danger");
            showUploadAlert($modal, "Please upload an image for this site link.");
            valid = false;
        }

        return valid;
    };

    const resetAddForm = (dropzone) => {
        const $modal = $("#uploadEmployeeDocumentModal");

        clearErrors($modal);
        $modal.find('input[type="text"], input[type="url"], textarea').val("");
        $modal.find("select").val("");
        $modal.find('[name="available_staff_status"], [name="available_student_status"]').prop("checked", false);
        $modal.find('[name="active_status"]').prop("checked", true);
        $("#uploadDocumentForm").find('input[type="hidden"]').val("");
        resetDropzone(dropzone);
    };

    const resetEditForm = (dropzone) => {
        const $modal = $("#uploadEmployeeDocumentModalEdit");

        clearErrors($modal);
        $modal.find('input[type="text"], input[type="url"], textarea').val("");
        $modal.find("select").val("");
        $modal.find('input[type="checkbox"]').prop("checked", false);
        $modal.find("[data-current-image]").prop("hidden", true);
        $modal.find("[data-current-image-preview]").html("");
        $modal.find("[data-current-image-name]").text("No image uploaded");
        $("#uploadDocumentFormEdit").find('input[type="hidden"]').val("");
        resetDropzone(dropzone);
    };

    const avatarHtml = (data) => {
        const initials = getInitials(data.name);
        const image = data.image ? escapeHtml(data.image) : "";
        const name = escapeHtml(data.name || "Internal link");

        return `<span class="ss-internal-link-avatar ${image ? "" : "is-fallback"}"><span>${initials}</span>${image ? `<img src="${image}" alt="${name}" onerror="this.remove();this.parentElement.classList.add('is-fallback');">` : ""}</span>`;
    };

    const nameFormatter = (cell) => {
        const data = cell.getData();
        const description = data.description ? escapeHtml(data.description) : (data.parent_id ? "Child shortcut" : "Parent shortcut");

        return `<span class="ss-internal-link-name">${avatarHtml(data)}<span><strong>${escapeHtml(data.name)}</strong><small>${description}</small></span></span>`;
    };

    const availabilityFormatter = (cell) => {
        const data = cell.getData();
        const chips = [];

        if (data.available_staff == 1) {
            chips.push(`<span class="ss-internal-audience-pill is-staff">${iconSvg(Check)}Staff</span>`);
        }

        if (data.available_student == 1) {
            chips.push(`<span class="ss-internal-audience-pill is-student">${iconSvg(Check)}Student</span>`);
        }

        if (!chips.length) {
            chips.push(`<span class="ss-internal-audience-pill is-empty">${iconSvg(X)}No audience</span>`);
        }

        return `<span class="ss-internal-audience-list">${chips.join("")}</span>`;
    };

    const statusFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return '<span class="ss-status-pill is-inactive"><span></span>Archived</span>';
        }

        const isActive = data.active == 1;

        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Inactive"}</span>`;
    };

    const linkFormatter = (cell) => {
        const link = cell.getValue();

        if (!link) {
            return '<span class="ss-internal-link-empty">No link</span>';
        }

        return `<a class="ss-internal-link-url" href="${escapeHtml(link)}" target="_blank" rel="noopener">${iconSvg(ExternalLink)}<span>${escapeHtml(link)}</span></a>`;
    };

    const dateFormatter = (cell) => {
        const data = cell.getData();
        const start = data.start_date || "";
        const end = data.end_date || "";

        if (!start && !end) {
            return '<span class="ss-internal-link-empty">No date range</span>';
        }

        return `<span class="ss-internal-date-range">${iconSvg(CalendarDays)}${escapeHtml(start || "Any time")} - ${escapeHtml(end || "No end")}</span>`;
    };

    const actionFormatter = (cell) => {
        const data = cell.getData();

        if (data.deleted_at != null) {
            return `<button data-id="${escapeHtml(data.id)}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore internal link">${iconSvg(RotateCw)}</button>`;
        }

        return [
            `<button data-id="${escapeHtml(data.id)}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit internal link">${iconSvg(Pencil)}</button>`,
            `<button data-id="${escapeHtml(data.id)}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Archive internal link">${iconSvg(Trash2)}</button>`,
        ].join("");
    };

    const buildTreeIcon = (Icon) => {
        const icon = createElement(Icon);

        icon.setAttribute("stroke-width", "2");
        icon.classList.add("ss-internal-tree-icon");

        return icon;
    };

    const buildTable = () => {
        const querystr = $("#query").val() || "";
        const status = $("#status").val() || "1";

        if (tableContent) {
            tableContent.destroy();
        }

        tableContent = new Tabulator("#internalLinkTableId", {
            ajaxURL: route("internal-link.list"),
            ajaxParams: { querystr, status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: false,
            placeholder: "No matching records found",
            dataTree: true,
            dataTreeStartExpanded: true,
            dataTreeElementColumn: "name",
            dataTreeCollapseElement: buildTreeIcon(Minus),
            dataTreeExpandElement: buildTreeIcon(Plus),
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 78,
                    headerSort: false,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 250,
                    widthGrow: 1.5,
                    formatter: nameFormatter,
                },
                {
                    title: "Link",
                    field: "link",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 180,
                    widthGrow: 1.2,
                    formatter: linkFormatter,
                },
                {
                    title: "Available To",
                    field: "available_staff",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 162,
                    widthGrow: 0.8,
                    formatter: availabilityFormatter,
                    download: false,
                },
                {
                    title: "Dates",
                    field: "start_date",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 168,
                    widthGrow: 0.9,
                    formatter: dateFormatter,
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    hozAlign: "left",
                    minWidth: 118,
                    widthGrow: 0.55,
                    formatter: statusFormatter,
                    download: false,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 112,
                    minWidth: 112,
                    download: false,
                    formatter: actionFormatter,
                },
            ],
            renderComplete() {
                refreshIcons();
            },
        });
    };

    const createDropzone = (formSelector, modalSelector, buttonSelector, onSuccess) => {
        const form = document.querySelector(formSelector);
        const $modal = $(modalSelector);
        let uploadFailed = false;

        if (!form) {
            return null;
        }

        if (form.dropzone) {
            form.dropzone.destroy();
        }

        const dropzone = new Dropzone(form, {
            autoProcessQueue: false,
            maxFiles: 1,
            maxFilesize: 20,
            parallelUploads: 1,
            acceptedFiles: ".jpeg,.jpg,.png,.gif,.svg",
            addRemoveLinks: true,
            thumbnailWidth: 120,
            thumbnailHeight: 120,
            headers: { "X-CSRF-TOKEN": csrfToken },
        });

        dropzone.on("maxfilesexceeded", (file) => {
            dropzone.removeAllFiles(true);
            dropzone.addFile(file);
        });

        dropzone.on("addedfile", () => {
            $modal.find(".ss-internal-link-dropzone").removeClass("is-danger");
            $modal.find(".ss-upload-alert").remove();
        });

        dropzone.on("processing", () => {
            uploadFailed = false;
            setBusy(buttonSelector, true);
        });

        dropzone.on("error", (file, response) => {
            uploadFailed = true;
            const message = typeof response === "string" ? response : response?.message || "Something went wrong. Please try again.";

            showUploadAlert($modal, message);
        });

        dropzone.on("success", (file) => {
            file.previewElement?.classList.add("dz-success");
        });

        dropzone.on("complete", (file) => {
            dropzone.removeFile(file);
        });

        dropzone.on("queuecomplete", () => {
            setBusy(buttonSelector, false);

            if (uploadFailed) {
                showWarning("Error Found!", "Something went wrong. Please try later or contact administrator.");
                return;
            }

            onSuccess();
        });

        return dropzone;
    };

    const addDropzone = createDropzone("#uploadDocumentForm", "#uploadEmployeeDocumentModal", "#uploadEmpDocBtn", () => {
        addModal.hide();
        showSuccess("Success!", "Internal link successfully created.");
        resetAddForm(addDropzone);
        buildTable();
    });

    const editDropzone = createDropzone("#uploadDocumentFormEdit", "#uploadEmployeeDocumentModalEdit", "#uploadEmpDocBtnEdit", () => {
        editModal.hide();
        showSuccess("Success!", "Internal link successfully updated.");
        resetEditForm(editDropzone);
        buildTable();
    });

    buildTable();
    refreshIcons();

    $("#tabulatorFilterForm").on("keypress.internal-link", function (event) {
        const keycode = event.keyCode ? event.keyCode : event.which;

        if (keycode == "13") {
            event.preventDefault();
            buildTable();
        }
    });

    $("#tabulator-html-filter-go").on("click.internal-link", buildTable);

    $("#tabulator-html-filter-reset").on("click.internal-link", function () {
        $("#query").val("");
        $("#status").val("1");
        buildTable();
    });

    $("#tabulator-export-csv").on("click.internal-link", function () {
        tableContent?.download("csv", "internal-links.csv");
    });

    $("#tabulator-export-xlsx").on("click.internal-link", function () {
        window.XLSX = xlsx;
        tableContent?.download("xlsx", "internal-links.xlsx", {
            sheetName: "Internal Links",
        });
    });

    $("#tabulator-print").on("click.internal-link", function () {
        tableContent?.print();
    });

    $("#warningModal .warningCloser").on("click.internal-link", function () {
        warningModal.hide();
    });

    document.getElementById("uploadEmployeeDocumentModal")?.addEventListener("show.tw.modal", function () {
        resetAddForm(addDropzone);
    });

    document.getElementById("uploadEmployeeDocumentModal")?.addEventListener("hide.tw.modal", function () {
        resetAddForm(addDropzone);
    });

    document.getElementById("uploadEmployeeDocumentModalEdit")?.addEventListener("hide.tw.modal", function () {
        resetEditForm(editDropzone);
    });

    document.getElementById("confirmModal")?.addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $("#uploadEmpDocBtn").on("click.internal-link", function (event) {
        event.preventDefault();

        const $modal = $("#uploadEmployeeDocumentModal");

        if (!validateModal($modal, addDropzone, true)) {
            return;
        }

        syncFormFields($modal, "#uploadDocumentForm");
        addDropzone.processQueue();
    });

    $("#internalLinkTableId").on("click.internal-link", ".edit_btn", function () {
        const internalLink = $(this).attr("data-id");
        const $modal = $("#uploadEmployeeDocumentModalEdit");
        const $form = $("#uploadDocumentFormEdit");

        resetEditForm(editDropzone);

        axios({
            method: "get",
            url: route("internal-link.edit", internalLink),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;

                $form.find('[name="id"]').val(dataset.id || internalLink);
                $modal.find('[name="name_status"]').val(dataset.name || "");
                $modal.find('[name="link_status"]').val(dataset.link || "");
                $modal.find('[name="parent_category"]').val(dataset.parent_id || "");
                $modal.find('[name="description_status"]').val(dataset.description || "");
                $modal.find('[name="start_date_status"]').val(dataset.start_date || "");
                $modal.find('[name="end_date_status"]').val(dataset.end_date || "");
                $modal.find('[name="available_staff_status"]').prop("checked", dataset.available_staff == 1);
                $modal.find('[name="available_student_status"]').prop("checked", dataset.available_student == 1);
                $modal.find('[name="active_status"]').prop("checked", dataset.active == 1);

                if (dataset.image) {
                    $modal.find("[data-current-image]").prop("hidden", false);
                    $modal.find("[data-current-image-preview]").html(avatarHtml(dataset));
                    $modal.find("[data-current-image-name]").text(dataset.name || "Internal link image");
                }

                syncFormFields($modal, "#uploadDocumentFormEdit");
                editModal.show();
                refreshIcons();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#uploadEmpDocBtnEdit").on("click.internal-link", function (event) {
        event.preventDefault();

        const $modal = $("#uploadEmployeeDocumentModalEdit");

        if (!validateModal($modal, editDropzone, false)) {
            return;
        }

        syncFormFields($modal, "#uploadDocumentFormEdit");

        if (editDropzone.getQueuedFiles().length > 0) {
            editDropzone.processQueue();
            return;
        }

        setBusy("#uploadEmpDocBtnEdit", true);

        axios({
            method: "post",
            url: route("internal-link.update"),
            data: new FormData(document.getElementById("uploadDocumentFormEdit")),
            headers: { "X-CSRF-TOKEN": csrfToken },
        }).then((response) => {
            setBusy("#uploadEmpDocBtnEdit", false);

            if (response.status == 200) {
                editModal.hide();
                showSuccess("Success!", "Internal link successfully updated.");
                resetEditForm(editDropzone);
                buildTable();
            }
        }).catch((error) => {
            setBusy("#uploadEmpDocBtnEdit", false);

            if (error.response?.status == 422) {
                showUploadAlert($modal, error.response.data.message || "Please check the form and try again.");
                return;
            }

            showWarning("Error Found!", "Something went wrong. Please try later or contact administrator.");
            console.log(error);
        });
    });

    $("#internalLinkTableId").on("click.internal-link", ".delete_btn", function () {
        showConfirm(
            "Archive internal link?",
            "This internal link will move to archived records.",
            "DELETE",
            $(this).attr("data-id")
        );
    });

    $("#internalLinkTableId").on("click.internal-link", ".restore_btn", function () {
        showConfirm(
            "Restore internal link?",
            "This internal link will be returned to active records.",
            "RESTORE",
            $(this).attr("data-id")
        );
    });

    $("#confirmModal .agreeWith").on("click.internal-link", function () {
        const $agreeButton = $(this);
        const recordID = $agreeButton.attr("data-id");
        const action = $agreeButton.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("internal-link.destroy", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Done!", "Internal link successfully archived.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
            return;
        }

        if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("internal-link.restore", recordID),
                headers: { "X-CSRF-TOKEN": csrfToken },
            }).then((response) => {
                if (response.status == 200) {
                    $("#confirmModal button").removeAttr("disabled");
                    confirmModal.hide();
                    showSuccess("Success!", "Internal link successfully restored.");
                    buildTable();
                }
            }).catch((error) => {
                $("#confirmModal button").removeAttr("disabled");
                console.log(error);
            });
        }
    });
})();
