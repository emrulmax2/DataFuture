import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

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

const isYes = (value) => {
    return value === true || value === 1 || value === "1" || String(value || "").toLowerCase() === "yes";
};

const yesNoPill = (value) => {
    const active = isYes(value);
    const className = active ? "is-active" : "is-inactive";
    const icon = active ? "check" : "x";

    return `<span class="ss-task-bool-pill ${className}" aria-label="${active ? "Yes" : "No"}"><i data-lucide="${icon}"></i></span>`;
};

const taskNameCell = (cell) => {
    const data = cell.getData();

    return `<span class="ss-task-name-cell">
        <strong>${escapeHtml(data.name)}</strong>
        <small>${escapeHtml(data.processlist)}</small>
    </span>`;
};

const initialsFromName = (value) => {
    const words = String(value || "")
        .trim()
        .split(/\s+/)
        .map((word) => word.replace(/[^A-Za-z0-9]/g, ""))
        .filter(Boolean);

    if (words.length === 0) {
        return "NA";
    }

    if (words.length === 1) {
        return words[0].slice(0, 2).toUpperCase();
    }

    return `${words[0].charAt(0)}${words[1].charAt(0)}`.toUpperCase();
};

const assignedAvatarTone = (index) => {
    return `is-tone-${(index % 4) + 1}`;
};

const normalizeAssignedUsersHtml = (content) => {
    const wrapper = document.createElement("div");
    wrapper.innerHTML = content;

    wrapper.querySelectorAll(".taskUserLoader > div").forEach((avatar, index) => {
        const image = avatar.querySelector("img");
        const imageUrl = image ? image.getAttribute("src") || "" : "";

        if (!image || imageUrl.includes("placeholders/200x200")) {
            const name = image ? image.getAttribute("alt") || "" : "";
            avatar.innerHTML = `<span class="ss-task-assigned-initial ${assignedAvatarTone(index)}">${escapeHtml(initialsFromName(name))}</span>`;
        }
    });

    return wrapper.innerHTML;
};

const assignedUsersCell = (cell) => {
    const content = cell.getValue();

    return content
        ? `<span class="ss-task-assigned-cell">${normalizeAssignedUsersHtml(content)}</span>`
        : '<span class="ss-cell-muted">&mdash;</span>';
};

var taskListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let processlist = $("#processlists-01").val() != "" ? $("#processlists-01").val() : "";

        if (window.taskListTableInstance) {
            window.taskListTableInstance.destroy();
        }

        let tableContent = new Tabulator("#taskTableId", {
            ajaxURL: route("tasklist.list"),
            ajaxParams: { querystr: querystr, status: status, processlist: processlist },
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
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 58,
                    minWidth: 54,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 190,
                    widthGrow: 1.25,
                    variableHeight: true,
                    formatter: taskNameCell,
                },
                {
                    title: "Interview",
                    field: "interview",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 84,
                    minWidth: 76,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Upload",
                    field: "upload",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 72,
                    minWidth: 66,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Ex. Link",
                    field: "external_link",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 78,
                    minWidth: 72,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 74,
                    minWidth: 68,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Email",
                    field: "org_email",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 68,
                    minWidth: 64,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "ID Card",
                    field: "id_card",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 74,
                    minWidth: 68,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Excuse",
                    field: "attendance_excuses",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 74,
                    minWidth: 68,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Pearson",
                    field: "pearson_reg",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 78,
                    minWidth: 72,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Address",
                    field: "address_request",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 78,
                    minWidth: 72,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Hesa Status",
                    field: "hesa_status",
                    headerHozAlign: "center",
                    hozAlign: "center",
                    width: 98,
                    minWidth: 92,
                    formatter(cell) {
                        return yesNoPill(cell.getValue());
                    },
                },
                {
                    title: "Assigned",
                    field: "user",
                    headerHozAlign: "left",
                    headerSort: false,
                    width: 112,
                    minWidth: 104,
                    formatter: assignedUsersCell,
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 106,
                    minWidth: 106,
                    download: false,
                    formatter(cell) {
                        const data = cell.getData();
                        var btns = "";

                        if (data.deleted_at == null) {
                            if (data.external_link_ref != "" && data.external_link_ref != null) {
                                btns += `<a target="_blank" rel="noopener" href="${escapeHtml(data.external_link_ref)}" class="ss-row-action ss-row-action--view" aria-label="Open task link"><i data-lucide="link"></i></a>`;
                            }
                            btns += `<button data-id="${data.id}" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit task"><i data-lucide="pencil"></i></button>`;
                            btns += `<button data-id="${data.id}" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete task"><i data-lucide="trash-2"></i></button>`;
                        } else {
                            btns += `<button data-id="${data.id}" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore task"><i data-lucide="rotate-cw"></i></button>`;
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

        window.taskListTableInstance = tableContent;

        if (window.taskListTableResizeHandler) {
            window.removeEventListener("resize", window.taskListTableResizeHandler);
        }

        window.taskListTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.taskListTableResizeHandler);

        $("#tabulator-export-csv").off("click.tasklist").on("click.tasklist", function () {
            tableContent.download("csv", "task-list.csv");
        });

        $("#tabulator-export-json").off("click.tasklist").on("click.tasklist", function () {
            tableContent.download("json", "task-list.json");
        });

        $("#tabulator-export-xlsx").off("click.tasklist").on("click.tasklist", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "task-list.xlsx", {
                sheetName: "Tasks List",
            });
        });

        $("#tabulator-export-html").off("click.tasklist").on("click.tasklist", function () {
            tableContent.download("html", "task-list.html", {
                style: true,
            });
        });

        $("#tabulator-print").off("click.tasklist").on("click.tasklist", function () {
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
    if ($("#taskTableId").length) {
        taskListTable.init();

        function filterHTMLForm() {
            taskListTable.init();
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
            $("#processlists-01").val("");
            $("#status").val("1");
            filterHTMLForm();
        });

        let tomOptions = {
            dropdownParent: "body",
            dropdownClass: "ts-dropdown ss-settings-tom-dropdown",
            plugins: {
                dropdown_input: {},
                remove_button: {
                    title: "Remove this item",
                },
            },
            placeholder: "Search Here...",
            create: false,
            maxOptions: null,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm(
                    values.length > 1
                        ? "Are you sure you want to remove these " + values.length + " items?"
                        : 'Are you sure you want to remove "' + values[0] + '"?'
                );
            },
        };

        var assignedUserAdd = new TomSelect("#assigned_users", tomOptions);
        var assignedUserEdit = new TomSelect("#edit_assigned_users", tomOptions);

        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTaskModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTaskModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const taskUserModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#taskUserModal"));
        let confModalDelTitle = "Are you sure?";

        const showSuccess = (title, message) => {
            $("#successModal .successModalTitle").html(title);
            $("#successModal .successModalDesc").html(message);
            succModal.show();
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

        const clearValidation = ($form) => {
            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");
            $form.find(".ts-wrapper").removeClass("border-danger");
        };

        const updateUploadName = ($form, name = "No file selected") => {
            $form.find("[data-ss-upload-name]").text(name);
        };

        const setExternalVisibility = ($form, animate = true, resetValue = false) => {
            const isEnabled = $form.find('input[name="external_link"]').prop("checked");
            const $wrap = $form.find(".extarnalUrlWrap");
            const clearValue = () => {
                if (resetValue) {
                    $form.find('input[name="external_link_ref"]').val("");
                }
            };

            if (isEnabled) {
                if (animate) {
                    $wrap.stop(true, true).css("display", "flex").hide().fadeIn("fast");
                } else {
                    $wrap.css("display", "flex");
                }
                return;
            }

            if (animate) {
                $wrap.fadeOut("fast", clearValue);
            } else {
                $wrap.hide();
                clearValue();
            }
        };

        const setTaskStatusesVisibility = ($form, animate = true, resetValue = false) => {
            const hasTaskStatus = $form.find('input[name="status"]:checked').val() === "Yes";
            const $wrap = $form.find(".taskStatusesWrap");
            const clearValues = () => {
                if (resetValue) {
                    $form.find('.taskStatusesWrap input[type="checkbox"]').prop("checked", false);
                }
            };

            if (hasTaskStatus) {
                if (animate) {
                    $wrap.fadeIn("fast");
                } else {
                    $wrap.show();
                }
                return;
            }

            if (animate) {
                $wrap.fadeOut("fast", clearValues);
            } else {
                $wrap.hide();
                clearValues();
            }
        };

        const setRadioValue = ($form, name, value) => {
            const normalized = isYes(value) ? "Yes" : "No";
            $form.find(`input[name="${name}"][value="${normalized}"]`).prop("checked", true);
        };

        const resetFormState = ($form, tomSelectInstance) => {
            clearValidation($form);
            $form.find('input[type="text"], input[type="url"], input[type="file"]').val("");
            $form.find("select").val("");
            $form.find('input[name="id"]').val("0");
            $form.find('input[type="checkbox"]').prop("checked", false);
            $form.find('input[type="radio"][value="No"]').prop("checked", true);
            if (tomSelectInstance) {
                tomSelectInstance.clear(true);
            }

            const placeholder = $form.find("img[data-placeholder]").attr("data-placeholder");
            if (placeholder) {
                $form.find("img[data-placeholder]").attr("src", placeholder);
            }

            updateUploadName($form);
            setExternalVisibility($form, false, true);
            setTaskStatusesVisibility($form, false, true);
        };

        const renderValidationErrors = ($form, error) => {
            if (!error.response || error.response.status !== 422) {
                console.log("error");
                return;
            }

            if (!error.response.data.errors) {
                if (error.response.data.message) {
                    if ($form.attr("id") === "editTaskForm") {
                        editModal.hide();
                    }
                    showSuccess("No Data Change!", error.response.data.message);
                }
                return;
            }

            for (const [key, val] of Object.entries(error.response.data.errors)) {
                $form.find(`.${key}`).addClass("border-danger");
                $form.find(`.error-${key}`).html(val);

                if (key === "assigned_users") {
                    $form.find(".ts-wrapper").addClass("border-danger");
                }

                if (key === "task_statuses") {
                    $form.find('[name="task_statuses[]"]').closest(".ss-status-toggle").addClass("border-danger");
                }
            }
        };

        const showConfirm = (id, action, title, message) => {
            $("#confirmModal .confModTitle").html(title);
            $("#confirmModal .confModDesc").html(message);
            $("#confirmModal .agreeWith").attr("data-id", id);
            $("#confirmModal .agreeWith").attr("data-action", action);
            confModal.show();
        };

        resetFormState($("#addTaskForm"), assignedUserAdd);
        resetFormState($("#editTaskForm"), assignedUserEdit);

        const addModalEl = document.getElementById("addTaskModal");
        addModalEl.addEventListener("show.tw.modal", function () {
            resetFormState($("#addTaskForm"), assignedUserAdd);
        });

        addModalEl.addEventListener("hide.tw.modal", function () {
            resetFormState($("#addTaskForm"), assignedUserAdd);
        });

        const editModalEl = document.getElementById("editTaskModal");
        editModalEl.addEventListener("hide.tw.modal", function () {
            resetFormState($("#editTaskForm"), assignedUserEdit);
        });

        const confirmModalEl = document.getElementById("confirmModal");
        confirmModalEl.addEventListener("hidden.tw.modal", function () {
            $("#confirmModal .agreeWith").attr("data-id", "0");
            $("#confirmModal .agreeWith").attr("data-action", "none");
            $("#confirmModal button").removeAttr("disabled");
        });

        const taskUserModalEl = document.getElementById("taskUserModal");
        taskUserModalEl.addEventListener("hidden.tw.modal", function () {
            $("#taskUserModal .taskUserModalContent").hide();
            $("#taskUserModal table tbody").html("");
            $("#taskUserModal .taskUserModalLoader").show();
        });

        $("#addTaskForm").on("change", "#processImageAdd", function () {
            showPreview("processImageAdd", "processImageAddShow");
            updateUploadName($("#addTaskForm"), this.files?.[0]?.name || "No file selected");
        });

        $("#editTaskForm").on("change", "#processImageEdit", function () {
            showPreview("processImageEdit", "processImageEditShow");
            updateUploadName($("#editTaskForm"), this.files?.[0]?.name || "No file selected");
        });

        $('#addTaskForm input[name="external_link"]').on("change", function () {
            setExternalVisibility($("#addTaskForm"), true, true);
        });

        $('#editTaskForm input[name="external_link"]').on("change", function () {
            setExternalVisibility($("#editTaskForm"), true, true);
        });

        $('#addTaskForm input[name="status"]').on("change", function () {
            setTaskStatusesVisibility($("#addTaskForm"), true, true);
        });

        $('#editTaskForm input[name="status"]').on("change", function () {
            setTaskStatusesVisibility($("#editTaskForm"), true, true);
        });

        $("#addTaskForm, #editTaskForm").on("input change", "input, select", function () {
            const $form = $(this).closest("form");
            const name = $(this).attr("name");

            if (name) {
                const normalizedName = name.replace("[]", "");
                $form.find(`.${normalizedName}`).removeClass("border-danger");
                $form.find(`.error-${normalizedName}`).html("");
            }

            if (name === "assigned_users[]") {
                $form.find(".ts-wrapper").removeClass("border-danger");
                $form.find(".error-assigned_users").html("");
            }

            if (name === "task_statuses[]") {
                $(this).closest(".ss-status-toggle").removeClass("border-danger");
                $form.find(".error-task_statuses").html("");
            }
        });

        $("#addTaskForm").on("submit", function (e) {
            e.preventDefault();
            const $form = $("#addTaskForm");
            const form = document.getElementById("addTaskForm");

            clearValidation($form);
            setButtonLoading("#save", true);

            let formData = new FormData(form);

            axios({
                method: "post",
                url: route("tasklist.store"),
                data: formData,
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                setButtonLoading("#save", false);

                if (response.status == 200) {
                    addModal.hide();
                    showSuccess("Success!", "Task list item successfully inserted.");
                }
                taskListTable.init();
            }).catch(error => {
                setButtonLoading("#save", false);
                renderValidationErrors($form, error);
            });
        });

        $("#taskTableId").on("click", ".edit_btn", function () {
            let editId = $(this).attr("data-id");
            const $form = $("#editTaskForm");

            resetFormState($form, assignedUserEdit);

            axios({
                method: "get",
                url: route("tasklist.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    let placeholder = $("#editTaskModal .processImageEditShow").attr("data-placeholder");

                    $form.find('select[name="process_list_id"]').val(dataset.process_list_id ? dataset.process_list_id : "");
                    $form.find('input[name="name"]').val(dataset.name ? dataset.name : "");
                    $form.find('input[name="short_description"]').val(dataset.short_description ? dataset.short_description : "");
                    $("#editTaskModal .processImageEditShow").attr("src", dataset.image_url ? dataset.image_url : placeholder);
                    $form.find('input[name="id"]').val(editId);

                    [
                        "interview",
                        "upload",
                        "org_email",
                        "id_card",
                        "attendance_excuses",
                        "pearson_reg",
                        "address_request",
                        "hesa_status",
                        "status",
                    ].forEach((fieldName) => {
                        setRadioValue($form, fieldName, dataset[fieldName]);
                    });

                    $form.find('input[name="external_link"]').prop("checked", isYes(dataset.external_link));
                    $form.find('input[name="external_link_ref"]').val(dataset.external_link_ref ? dataset.external_link_ref : "");
                    setExternalVisibility($form, false, false);

                    if (dataset.users && dataset.users.length > 0) {
                        $.each(dataset.users, function (name, value) {
                            assignedUserEdit.addItem(value.user_id, true);
                        });
                    }

                    $form.find('.taskStatusesWrap input[type="checkbox"]').prop("checked", false);
                    if (isYes(dataset.status) && dataset.statuses && dataset.statuses.length > 0) {
                        $.each(dataset.statuses, function (name, value) {
                            $form.find(`.taskStatusesWrap input[type="checkbox"][value="${value.task_status_id}"]`).prop("checked", true);
                        });
                    }
                    setTaskStatusesVisibility($form, false, false);

                    editModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editTaskForm").on("submit", function (e) {
            e.preventDefault();
            const $form = $("#editTaskForm");
            const form = document.getElementById("editTaskForm");

            clearValidation($form);
            setButtonLoading("#update", true);

            let formData = new FormData(form);

            axios({
                method: "post",
                url: route("tasklist.update"),
                data: formData,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                setButtonLoading("#update", false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess("Success!", "Task list item successfully updated.");
                }
                taskListTable.init();
            }).catch((error) => {
                setButtonLoading("#update", false);
                renderValidationErrors($form, error);
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
                    url: route("tasklist.destory", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Done!", "Task list item successfully deleted.");
                    }
                    taskListTable.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            } else if (action === "RESTORE") {
                axios({
                    method: "post",
                    url: route("tasklist.restore", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Success!", "Task list item successfully restored.");
                    }
                    taskListTable.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            }
        });

        $("#taskTableId").on("click", ".delete_btn", function () {
            let rowID = $(this).attr("data-id");

            showConfirm(
                rowID,
                "DELETE",
                confModalDelTitle,
                "Want to delete this task? Please click on agree to continue."
            );
        });

        $("#taskTableId").on("click", ".restore_btn", function () {
            let dataID = $(this).attr("data-id");

            showConfirm(
                dataID,
                "RESTORE",
                confModalDelTitle,
                "Want to restore this task from the trash? Please click on agree to continue."
            );
        });

        $("#taskTableId").on("click", ".taskUserLoader", function () {
            var task_id = $(this).attr("data-taskid");
            taskUserModal.show();

            axios({
                method: "post",
                url: route("tasklist.users"),
                data: { task_id: task_id },
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                if (response.status == 200) {
                    $("#taskUserModal .taskUserModalLoader").fadeOut("fast");
                    $("#taskUserModal .taskUserModalContent").fadeIn("fast", function () {
                        $("table tbody", this).html(response.data.res);
                    });

                    createIcons({
                        icons,
                        "stroke-width": 1.7,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                console.log(error);
            });
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
