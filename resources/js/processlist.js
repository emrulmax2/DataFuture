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

const processPhaseClass = (value) => {
    const phase = String(value || "")
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/^-|-$/g, "");

    return phase ? `is-${phase}` : "";
};

const processInitials = (value) => {
    const words = String(value || "")
        .trim()
        .split(/\s+/)
        .map((word) => word.replace(/[^A-Za-z0-9]/g, ""))
        .filter(Boolean);

    if (words.length === 0) {
        return "PR";
    }

    if (words.length === 1) {
        return words[0].slice(0, 2).toUpperCase();
    }

    return `${words[0].charAt(0)}${words[1].charAt(0)}`.toUpperCase();
};

const hasProcessImage = (imageUrl) => {
    return imageUrl && !String(imageUrl).includes("placeholders/200x200");
};

const processAvatarTone = (row) => {
    const sequence = Number(row.sl || row.id || 1);
    const tone = ((sequence - 1) % 4) + 1;

    return `is-tone-${tone}`;
};

var table = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        if (window.processListTableInstance) {
            window.processListTableInstance.destroy();
        }

        let tableContent = new Tabulator("#processlistTableId", {
            ajaxURL: route("processlist.list"),
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
                    width: 86,
                    minWidth: 70,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 260,
                    widthGrow: 1.6,
                    formatter(cell) {
                        const data = cell.getData();
                        const rawName = data.name || "";
                        const name = escapeHtml(rawName);
                        const imageUrl = data.image_url || "";
                        const avatar = hasProcessImage(imageUrl)
                            ? `<span class="ss-process-cell__avatar"><img alt="${name}" src="${escapeHtml(imageUrl)}"></span>`
                            : `<span class="ss-process-cell__avatar ss-process-cell__avatar--initials ${processAvatarTone(data)}" aria-hidden="true">${escapeHtml(processInitials(rawName))}</span>`;

                        return `<span class="ss-process-cell">
                            ${avatar}
                            <strong>${name}</strong>
                        </span>`;
                    },
                },
                {
                    title: "Phase",
                    field: "phase",
                    headerHozAlign: "left",
                    minWidth: 150,
                    formatter(cell) {
                        const phase = cell.getValue();
                        return `<span class="ss-phase-pill ss-process-phase ${processPhaseClass(phase)}">${escapeHtml(phase)}</span>`;
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 124,
                    minWidth: 124,
                    download: false,
                    formatter(cell) {
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit process"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete process"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore process"><i data-lucide="rotate-cw"></i></button>';
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

        window.processListTableInstance = tableContent;

        if (window.processListTableResizeHandler) {
            window.removeEventListener("resize", window.processListTableResizeHandler);
        }

        window.processListTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.processListTableResizeHandler);

        $("#tabulator-export-csv").off("click.processlist").on("click.processlist", function () {
            tableContent.download("csv", "process-list.csv");
        });

        $("#tabulator-export-json").off("click.processlist").on("click.processlist", function () {
            tableContent.download("json", "process-list.json");
        });

        $("#tabulator-export-xlsx").off("click.processlist").on("click.processlist", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "process-list.xlsx", {
                sheetName: "Process List",
            });
        });

        $("#tabulator-export-html").off("click.processlist").on("click.processlist", function () {
            tableContent.download("html", "process-list.html", {
                style: true,
            });
        });

        $("#tabulator-print").off("click.processlist").on("click.processlist", function () {
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
    if ($("#processlistTableId").length) {
        table.init();

        function filterHTMLForm() {
            table.init();
        }

        $("#tabulatorFilterForm")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        $("#tabulator-html-filter-go").on("click", function () {
            filterHTMLForm();
        });

        $("#tabulator-html-filter-reset").on("click", function () {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLForm();
        });

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addProcessModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editProcessModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = "Are you sure?";

        const showSuccess = (title, message) => {
            $("#successModal .successModalTitle").html(title);
            $("#successModal .successModalDesc").html(message);
            succModal.show();
        };

        const updateUploadName = ($form, name = "No file selected") => {
            $form.find("[data-ss-upload-name]").text(name);
        };

        const setAutoFeedVisibility = ($form, animate = true) => {
            const isLive = $form.find('[name="phase"]').val() === "Live";
            const $wrap = $form.find(".autoFeedWrap");
            const resetAutoFeed = () => {
                $form.find('input[name="auto_feed"][value="No"]').prop("checked", true);
            };

            if (isLive) {
                if (animate) {
                    $wrap.fadeIn("fast");
                } else {
                    $wrap.show();
                }
                return;
            }

            if (animate) {
                $wrap.fadeOut("fast", resetAutoFeed);
            } else {
                $wrap.hide();
                resetAutoFeed();
            }
        };

        const resetFormState = ($form) => {
            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");
            $form.find('input[type="text"], input[type="file"]').val("");
            $form.find("select").val("");
            $form.find('input[name="id"]').val("0");
            const placeholder = $form.find("img[data-placeholder]").attr("data-placeholder");
            if (placeholder) {
                $form.find("img[data-placeholder]").attr("src", placeholder);
            }
            updateUploadName($form);
            setAutoFeedVisibility($form, false);
        };

        const setButtonLoading = (selector, isLoading) => {
            const button = document.querySelector(selector);
            const spinner = document.querySelector(`${selector} svg`);

            if (!button) {
                return;
            }

            if (isLoading) {
                button.setAttribute("disabled", "disabled");
                if (spinner) {
                    spinner.style.cssText = "display: inline-block;";
                }
                return;
            }

            button.removeAttribute("disabled");
            if (spinner) {
                spinner.style.cssText = "display: none;";
            }
        };

        const renderValidationErrors = ($form, error) => {
            if (!error.response || error.response.status !== 422) {
                console.log("error");
                return;
            }

            if (!error.response.data.errors) {
                if (error.response.data.message) {
                    if ($form.attr("id") === "editProcessForm") {
                        editModal.hide();
                    }
                    showSuccess("No Data Change!", error.response.data.message);
                }
                return;
            }

            for (const [key, val] of Object.entries(error.response.data.errors)) {
                $form.find(`.${key}`).addClass("border-danger");
                $form.find(`.error-${key}`).html(val);
            }
        };

        const showConfirm = (id, action, title, message) => {
            $("#confirmModal .confModTitle").html(title);
            $("#confirmModal .confModDesc").html(message);
            $("#confirmModal .agreeWith").attr("data-id", id);
            $("#confirmModal .agreeWith").attr("data-action", action);
            confModal.show();
        };

        resetFormState($("#addProcessForm"));
        resetFormState($("#editProcessForm"));

        const addModalEl = document.getElementById("addProcessModal");
        addModalEl.addEventListener("show.tw.modal", function () {
            resetFormState($("#addProcessForm"));
        });

        addModalEl.addEventListener("hide.tw.modal", function () {
            resetFormState($("#addProcessForm"));
        });

        const editModalEl = document.getElementById("editProcessModal");
        editModalEl.addEventListener("hide.tw.modal", function () {
            resetFormState($("#editProcessForm"));
        });

        const confirmModalEl = document.getElementById("confirmModal");
        confirmModalEl.addEventListener("hidden.tw.modal", function () {
            $("#confirmModal .agreeWith").attr("data-id", "0");
            $("#confirmModal .agreeWith").attr("data-action", "none");
            $("#confirmModal button").removeAttr("disabled");
        });

        $("#addProcessForm").on("change", "#processImageAdd", function () {
            showPreview("processImageAdd", "processImageAddShow");
            updateUploadName($("#addProcessForm"), this.files?.[0]?.name || "No file selected");
        });

        $("#editProcessForm").on("change", "#processImageEdit", function () {
            showPreview("processImageEdit", "processImageEditShow");
            updateUploadName($("#editProcessForm"), this.files?.[0]?.name || "No file selected");
        });

        $('#addProcessForm [name="phase"]').on("change", function () {
            setAutoFeedVisibility($("#addProcessForm"));
        });

        $('#editProcessForm [name="phase"]').on("change", function () {
            setAutoFeedVisibility($("#editProcessForm"));
        });

        $("#addProcessForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("addProcessForm");

            setButtonLoading("#save", true);

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route("processlist.store"),
                data: form_data,
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                setButtonLoading("#save", false);

                if (response.status == 200) {
                    addModal.hide();
                    showSuccess("Success!", "Process list item successfully inserted.");
                }
                table.init();
            }).catch(error => {
                setButtonLoading("#save", false);
                renderValidationErrors($("#addProcessForm"), error);
            });
        });

        $("#processlistTableId").on("click", ".edit_btn", function () {
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            resetFormState($("#editProcessForm"));

            axios({
                method: "get",
                url: route("processlist.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    let placeholder = $("#editProcessModal .processImageEditShow").attr("data-placeholder");
                    $('#editProcessForm input[name="name"]').val(dataset.name ? dataset.name : "");
                    $('#editProcessForm select[name="phase"]').val(dataset.phase ? dataset.phase : "");
                    $("#editProcessModal .processImageEditShow").attr("src", dataset.image_url ? dataset.image_url : placeholder);
                    $('#editProcessForm input[name="id"]').val(editId);

                    if (dataset.phase == "Live" && dataset.auto_feed == "Yes") {
                        $('#editProcessForm input[name="auto_feed"][value="Yes"]').prop("checked", true);
                    } else {
                        $('#editProcessForm input[name="auto_feed"][value="No"]').prop("checked", true);
                    }

                    setAutoFeedVisibility($("#editProcessForm"), false);
                    editModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editProcessForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editProcessForm input[name="id"]').val();
            const form = document.getElementById("editProcessForm");

            setButtonLoading("#update", true);

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("processlist.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    setButtonLoading("#update", false);
                    editModal.hide();
                    showSuccess("Success!", "Process list item successfully updated.");
                }
                table.init();
            }).catch((error) => {
                setButtonLoading("#update", false);
                renderValidationErrors($("#editProcessForm"), error);
            });
        });

        $("#confirmModal .agreeWith").on("click", function () {
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr("data-id");
            let action = $agreeBTN.attr("data-action");

            $("#confirmModal button").attr("disabled", "disabled");
            if (action === "DELETE") {
                axios({
                    method: "delete",
                    url: route("processlist.destory", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Done!", "Process list item successfully deleted.");
                    }
                    table.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            } else if (action === "RESTORE") {
                axios({
                    method: "post",
                    url: route("processlist.restore", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Success!", "Process list item successfully restored.");
                    }
                    table.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            }
        });

        $("#processlistTableId").on("click", ".delete_btn", function () {
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr("data-id");

            showConfirm(
                rowID,
                "DELETE",
                confModalDelTitle,
                "Want to delete this process? Please click on agree to continue."
            );
        });

        $("#processlistTableId").on("click", ".restore_btn", function () {
            let $statusBTN = $(this);
            let dataID = $statusBTN.attr("data-id");

            showConfirm(
                dataID,
                "RESTORE",
                confModalDelTitle,
                "Want to restore this process from the trash? Please click on agree to continue."
            );
        });

        function showPreview(inputId, targetImageId) {
            var src = document.getElementById(inputId);
            var target = document.getElementById(targetImageId);

            if (!src.files || !src.files[0]) {
                return;
            }

            var fr = new FileReader();
            fr.onload = function () {
                target.src = fr.result;
            };
            fr.readAsDataURL(src.files[0]);
        }
    }
})();
