import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

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

const formatSecret = (cell) => {
    const value = cell.getValue();

    if (value === null || value === undefined || value === "") {
        return '<span class="ss-cell-muted">&mdash;</span>';
    }

    return (
        '<button type="button" class="ss-secret-cell copy_secret" title="Click to copy"' +
        ` data-copy="${escapeHtml(value)}">` +
        `<span class="ss-secret-cell__value">${escapeHtml(value)}</span>` +
        '<i data-lucide="copy"></i>' +
        "</button>"
    );
};

const copyToClipboard = (text) => {
    if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
    }

    return new Promise((resolve, reject) => {
        const helper = document.createElement("textarea");
        helper.value = text;
        helper.setAttribute("readonly", "");
        helper.style.position = "fixed";
        helper.style.opacity = "0";
        document.body.appendChild(helper);
        helper.select();

        try {
            document.execCommand("copy") ? resolve() : reject(new Error("copy failed"));
        } catch (error) {
            reject(error);
        } finally {
            document.body.removeChild(helper);
        }
    });
};

// Probes each listed account against its provider and flags the ones that cannot
// connect. Results are cached server-side, so re-renders (resize, sort) reuse the
// previous verdict instead of re-dialling the mail server.
const smtpHealthCache = {};
let smtpHealthSignature = "";

const paintSmtpHealth = () => {
    $("#smtpSettingsListTable .ss-smtp-user").each(function () {
        const $cell = $(this);
        const result = smtpHealthCache[$cell.attr("data-row-id")];

        if (!result) {
            return;
        }

        $cell.toggleClass("is-smtp-down", result.ok === false);
        $cell.attr("title", result.ok === false ? `SMTP unreachable: ${result.message}` : "");
    });
};

const checkSmtpHealth = (ids) => {
    const unique = [...new Set((ids || []).filter(Boolean))];

    if (!unique.length) {
        return;
    }

    const signature = unique.join(",");

    if (signature === smtpHealthSignature) {
        paintSmtpHealth();
        return;
    }

    smtpHealthSignature = signature;

    axios({
        method: "post",
        url: route("common.smtp.health"),
        data: { ids: unique },
        headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
    }).then((response) => {
        Object.assign(smtpHealthCache, response.data.data || {});
        paintSmtpHealth();
    }).catch((error) => {
        smtpHealthSignature = "";
        console.log(error);
    });
};

var smtpSettingsListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";

        if (window.smtpSettingsTableInstance) {
            window.smtpSettingsTableInstance.destroy();
        }

        let tableContent = new Tabulator("#smtpSettingsListTable", {
            ajaxURL: route("common.smtp.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    width: 52,
                    minWidth: 48,
                },
                {
                    title: "SMTP Email",
                    field: "smtp_user",
                    headerHozAlign: "left",
                    minWidth: 140,
                    widthGrow: 0.7,
                    formatter(cell) {
                        return `<span class="ss-smtp-user" data-row-id="${cell.getData().id}">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "App Password",
                    field: "smtp_pass",
                    headerHozAlign: "left",
                    minWidth: 100,
                    widthGrow: 1.1,
                    formatter: formatSecret,
                },
                {
                    title: "Email Password",
                    field: "smtp_email_password",
                    headerHozAlign: "left",
                    minWidth: 100,
                    widthGrow: 1.1,
                    formatter: formatSecret,
                },
                {
                    title: "Host",
                    field: "smtp_host",
                    headerHozAlign: "left",
                    minWidth: 96,
                    widthGrow: 1.1,
                    formatter(cell) {
                        return `<span class="ss-cell-wrap ss-cell-wrap--mono">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Port",
                    field: "smtp_port",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 54,
                    widthGrow: 0.35,
                    formatter(cell) {
                        return `<span class="ss-cell-wrap ss-cell-wrap--mono">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Encryption",
                    field: "smtp_encryption",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 74,
                    widthGrow: 0.4,
                    formatter(cell) {
                        const value = cell.getValue();
                        return value ? `<span class="ss-phase-pill">${escapeHtml(value)}</span>` : '<span class="ss-cell-muted">&mdash;</span>';
                    },
                },
                {
                    title: "Auth",
                    field: "smtp_authentication",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 68,
                    widthGrow: 0.38,
                    formatter(cell) {
                        const isTrue = String(cell.getValue()).toLowerCase() === "true";
                        return `<span class="ss-status-pill ${isTrue ? "is-active" : "is-inactive"}"><span></span>${isTrue ? "True" : "False"}</span>`;
                    },
                },
                {
                    title: "Default",
                    field: "is_default",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    minWidth: 58,
                    widthGrow: 0.3,
                    formatter(cell) {
                        const isDefault = cell.getValue() == 1;
                        return `<span class="ss-doc-icon ${isDefault ? "is-active" : "is-inactive"}" aria-label="${isDefault ? "Default sender" : "Not default"}"><i data-lucide="${isDefault ? "check" : "x"}"></i></span>`;
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 90,
                    minWidth: 90,
                    download: false,
                    formatter(cell) {
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit SMTP account"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete SMTP account"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore SMTP account"><i data-lucide="rotate-cw"></i></button>';
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

                checkSmtpHealth(this.getData().map((row) => row.id));
            },
        });

        window.smtpSettingsTableInstance = tableContent;

        if (window.smtpSettingsTableResizeHandler) {
            window.removeEventListener("resize", window.smtpSettingsTableResizeHandler);
        }

        window.smtpSettingsTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.smtpSettingsTableResizeHandler);

        $("#tabulator-export-csv").off("click.commonsmtp").on("click.commonsmtp", function () {
            tableContent.download("csv", "common-smtp.csv");
        });

        $("#tabulator-export-xlsx").off("click.commonsmtp").on("click.commonsmtp", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "common-smtp.xlsx", {
                sheetName: "Common SMTP Details",
            });
        });

        $("#tabulator-print").off("click.commonsmtp").on("click.commonsmtp", function () {
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
    if (!$("#smtpSettingsListTable").length) {
        return;
    }

    smtpSettingsListTable.init();

    function filterHTMLForm() {
        smtpSettingsListTable.init();
    }

    $("#tabulatorFilterForm")[0].addEventListener("keypress", function (event) {
        let keycode = event.keyCode ? event.keyCode : event.which;
        if (keycode == "13") {
            event.preventDefault();
            filterHTMLForm();
        }
    });

    $("#tabulator-html-filter-go").on("click", function () {
        filterHTMLForm();
    });

    $("#tabulator-html-filter-reset").on("click", function () {
        $("#query").val("");
        $("#status").val("1");
        filterHTMLForm();
    });

    const addSmtpModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSmtpModal"));
    const editSmtpModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSmtpModal"));
    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const confModalDelTitle = "Are you sure?";

    const setBusy = ($button, isBusy) => {
        $button.prop("disabled", isBusy);
        $button.find(".ss-spinner").css("display", isBusy ? "inline-block" : "none");
    };

    const showSuccess = (title, message) => {
        $("#successModal .successModalTitle").html(title);
        $("#successModal .successModalDesc").html(message);
        succModal.show();
    };

    const clearErrors = ($form) => {
        $form.find(".acc__input-error").html("");
        $form.find(".border-danger").removeClass("border-danger");
    };

    const showErrors = ($form, errors) => {
        for (const [key, val] of Object.entries(errors)) {
            $form.find(`.${key}`).addClass("border-danger");
            $form.find(`.error-${key}`).html(Array.isArray(val) ? val[0] : val);
        }
    };

    const updateDefaultToggleText = ($toggle) => {
        const enabled = $toggle.find("input").is(":checked");
        $toggle.find(".ss-status-toggle__copy small").text(enabled ? "Default sender" : "Not default");
    };

    const resetAddForm = () => {
        const $form = $("#addSmtpForm");
        clearErrors($form);
        $form.find('input[type="text"], input[type="password"]').val("");
        $form.find('input[name="smtp_host"]').val("smtp.gmail.com");
        $form.find('input[name="smtp_port"]').val("587");
        $form.find('select[name="smtp_encryption"]').val("tls");
        $form.find('select[name="smtp_authentication"]').val("true");
        $form.find('input[type="checkbox"]').prop("checked", false);
        updateDefaultToggleText($form.find(".ss-status-toggle--inline"));
        setBusy($("#saveSMTP"), false);
    };

    const resetEditForm = () => {
        const $form = $("#editSmtpForm");
        clearErrors($form);
        $form.find('input[type="text"], input[type="password"]').val("");
        $form.find('input[name="id"]').val("0");
        $form.find("select").val("");
        $form.find('input[type="checkbox"]').prop("checked", false);
        updateDefaultToggleText($form.find(".ss-status-toggle--inline"));
        setBusy($("#updateSMTP"), false);
    };

    resetAddForm();
    resetEditForm();

    $(document).on("change", "#addSmtpForm .ss-status-toggle--inline input, #editSmtpForm .ss-status-toggle--inline input", function () {
        updateDefaultToggleText($(this).closest(".ss-status-toggle--inline"));
    });

    document.getElementById("addSmtpModal").addEventListener("show.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("addSmtpModal").addEventListener("hide.tw.modal", function () {
        resetAddForm();
    });

    document.getElementById("editSmtpModal").addEventListener("hide.tw.modal", function () {
        resetEditForm();
    });

    document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
        $("#confirmModal .agreeWith").attr("data-id", "0");
        $("#confirmModal .agreeWith").attr("data-action", "none");
        $("#confirmModal button").removeAttr("disabled");
    });

    $('#addSmtpForm select[name="smtp_encryption"], #editSmtpForm select[name="smtp_encryption"]').on("change", function () {
        const $form = $(this).closest("form");
        $form.find('input[name="smtp_port"]').val($(this).val() == "ssl" ? 465 : 587);
    });

    $("#addSmtpForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#addSmtpForm");
        const form = document.getElementById("addSmtpForm");

        clearErrors($form);
        setBusy($("#saveSMTP"), true);

        axios({
            method: "post",
            url: route("common.smtp.store"),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#saveSMTP"), false);

            if (response.status == 200) {
                resetAddForm();
                addSmtpModal.hide();
                showSuccess("Success!", "Common SMTP successfully inserted.");
            }

            smtpSettingsListTable.init();
        }).catch((error) => {
            setBusy($("#saveSMTP"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#smtpSettingsListTable").on("click", ".copy_secret", function (e) {
        e.stopPropagation();

        const $button = $(this);
        const value = $button.attr("data-copy");

        if (!value) {
            return;
        }

        copyToClipboard(value).then(() => {
            clearTimeout($button.data("copyTimer"));
            $button.addClass("is-copied").attr("title", "Copied");

            $button.data("copyTimer", setTimeout(() => {
                $button.removeClass("is-copied").attr("title", "Click to copy");
            }, 1400));
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#smtpSettingsListTable").on("click", ".edit_btn", function () {
        let editId = $(this).attr("data-id");
        resetEditForm();

        axios({
            method: "get",
            url: route("common.smtp.edit", editId),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                const dataset = response.data;
                const $form = $("#editSmtpForm");

                $form.find('input[name="smtp_user"]').val(dataset.smtp_user ? dataset.smtp_user : "");
                $form.find('input[name="smtp_pass"]').val(dataset.smtp_pass ? dataset.smtp_pass : "");
                $form.find('input[name="smtp_email_password"]').val(dataset.smtp_email_password ? dataset.smtp_email_password : "");
                $form.find('input[name="smtp_host"]').val(dataset.smtp_host ? dataset.smtp_host : "");
                $form.find('input[name="smtp_port"]').val(dataset.smtp_port ? dataset.smtp_port : "");
                $form.find('select[name="smtp_encryption"]').val(dataset.smtp_encryption ? dataset.smtp_encryption : "");
                $form.find('select[name="smtp_authentication"]').val(dataset.smtp_authentication ? dataset.smtp_authentication : "");
                $form.find('input[name="is_default"]').prop("checked", dataset.is_default == 1);
                $form.find('input[name="id"]').val(editId);

                updateDefaultToggleText($form.find(".ss-status-toggle--inline"));
                editSmtpModal.show();
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editSmtpForm").on("submit", function (e) {
        e.preventDefault();
        const $form = $("#editSmtpForm");
        const form = document.getElementById("editSmtpForm");
        const editId = $form.find('input[name="id"]').val();

        clearErrors($form);
        setBusy($("#updateSMTP"), true);

        axios({
            method: "post",
            url: route("common.smtp.update", editId),
            data: new FormData(form),
            headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            setBusy($("#updateSMTP"), false);

            if (response.status == 200) {
                editSmtpModal.hide();
                showSuccess("Success!", "Common SMTP successfully updated.");
            }

            smtpSettingsListTable.init();
        }).catch((error) => {
            setBusy($("#updateSMTP"), false);

            if (error.response) {
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($form, error.response.data.errors);
                } else if (error.response.status == 304) {
                    editSmtpModal.hide();
                    showSuccess("No Data Change!", error.response.statusText);
                } else {
                    console.log("error");
                }
            }
        });
    });

    $("#smtpSettingsListTable").on("click", ".delete_btn", function () {
        let rowID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalDelTitle);
        $("#confirmModal .confModDesc").html("Do you really want to delete this SMTP account?");
        $("#confirmModal .agreeWith").attr("data-id", rowID);
        $("#confirmModal .agreeWith").attr("data-action", "DELETE");
        confirmModal.show();
    });

    $("#smtpSettingsListTable").on("click", ".restore_btn", function () {
        let dataID = $(this).attr("data-id");

        $("#confirmModal .confModTitle").html(confModalDelTitle);
        $("#confirmModal .confModDesc").html("Do you really want to restore this SMTP account?");
        $("#confirmModal .agreeWith").attr("data-id", dataID);
        $("#confirmModal .agreeWith").attr("data-action", "RESTORE");
        confirmModal.show();
    });

    $("#confirmModal .agreeWith").on("click", function () {
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr("data-id");
        let action = $agreeBTN.attr("data-action");

        $("#confirmModal button").attr("disabled", "disabled");

        const done = (title, message) => {
            $("#confirmModal button").removeAttr("disabled");
            confirmModal.hide();
            showSuccess(title, message);
            smtpSettingsListTable.init();
        };

        const failed = (error) => {
            $("#confirmModal button").removeAttr("disabled");
            console.log(error);
        };

        if (action == "DELETE") {
            axios({
                method: "delete",
                url: route("common.smtp.destory", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Done!", "Common SMTP successfully deleted.");
                }
            }).catch(failed);
        } else if (action == "RESTORE") {
            axios({
                method: "post",
                url: route("common.smtp.restore", recordID),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    done("Success!", "Common SMTP successfully restored.");
                }
            }).catch(failed);
        }
    });
})();
