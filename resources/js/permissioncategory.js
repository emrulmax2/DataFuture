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

var table = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let department = $("#department").val() != "" ? $("#department").val() : "";

        // Rebuilt on every filter; the old instance and its listeners have to
        // go first or each search stacks another table and resize handler.
        if (window.permissionCategoryTableInstance) {
            window.permissionCategoryTableInstance.destroy();
        }

        let tableContent = new Tabulator("#permissioncategoryTableId", {
            ajaxURL: route("permissioncategory.list"),
            ajaxParams: { querystr: querystr, status: status, department: department },
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
                    width: 72,
                    minWidth: 64,
                },
                {
                    title: "Department",
                    field: "department",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 150,
                    widthGrow: 1.2,
                    // Categories saved before departments existed have none yet,
                    // which escapeHtml renders as a dash.
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Sub Department",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 150,
                    widthGrow: 1.6,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: 104,
                    minWidth: 104,
                    download: false,
                    formatter(cell) {
                        var btns = "";

                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit permission category"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete permission category"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore permission category"><i data-lucide="rotate-cw"></i></button>';
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

        window.permissionCategoryTableInstance = tableContent;

        if (window.permissionCategoryTableResizeHandler) {
            window.removeEventListener("resize", window.permissionCategoryTableResizeHandler);
        }

        window.permissionCategoryTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.permissionCategoryTableResizeHandler);

        $("#tabulator-export-csv").off("click.permcat").on("click.permcat", function () {
            tableContent.download("csv", "permission-categories.csv");
        });

        $("#tabulator-export-xlsx").off("click.permcat").on("click.permcat", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "permission-categories.xlsx", {
                sheetName: "Permission Categories",
            });
        });

        $("#tabulator-print").off("click.permcat").on("click.permcat", function () {
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
    if ($("#permissioncategoryTableId").length) {
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
            $("#department").val("");
            $("#status").val("1");
            filterHTMLForm();
        });

        // A dropdown is a finished choice, unlike a half-typed query, so the
        // list follows it straight away rather than waiting for Go.
        $("#department").on("change", function () {
            filterHTMLForm();
        });

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPermissionModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPermissionModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
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

        // form.reset() clears the department select as well as the input, which
        // the old per-input reset did not.
        const resetFormState = ($form) => {
            if ($form[0]) {
                $form[0].reset();
            }

            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");
            $form.find('input[name="id"]').val("0");
        };

        const showErrors = ($form, errors) => {
            for (const [key, val] of Object.entries(errors)) {
                $form.find(`.${key}`).addClass("border-danger");
                $form.find(`.error-${key}`).html(Array.isArray(val) ? val[0] : val);
            }
        };

        const showConfirm = (id, action, title, message) => {
            $("#confirmModal .confModTitle").html(title);
            $("#confirmModal .confModDesc").html(message);
            $("#confirmModal .agreeWith").attr("data-id", id);
            $("#confirmModal .agreeWith").attr("data-action", action);
            confModal.show();
        };

        resetFormState($("#addForm"));
        resetFormState($("#editForm"));

        document.getElementById("addPermissionModal").addEventListener("show.tw.modal", function () {
            resetFormState($("#addForm"));
        });

        document.getElementById("addPermissionModal").addEventListener("hide.tw.modal", function () {
            resetFormState($("#addForm"));
            setBusy($("#save"), false);
        });

        document.getElementById("editPermissionModal").addEventListener("hide.tw.modal", function () {
            resetFormState($("#editForm"));
            setBusy($("#update"), false);
        });

        document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
            $("#confirmModal .agreeWith").attr("data-id", "0");
            $("#confirmModal .agreeWith").attr("data-action", "none");
            $("#confirmModal button").removeAttr("disabled");
        });

        $("#addForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("addForm");

            setBusy($("#save"), true);

            axios({
                method: "post",
                url: route("permissioncategory.store"),
                data: new FormData(form),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                setBusy($("#save"), false);

                if (response.status == 200) {
                    addModal.hide();
                    showSuccess("Success!", "Permission category successfully inserted.");
                }

                table.init();
            }).catch((error) => {
                setBusy($("#save"), false);
                if (error.response && error.response.status == 422) {
                    showErrors($("#addForm"), error.response.data.errors || {});
                } else {
                    console.log(error);
                }
            });
        });

        $("#permissioncategoryTableId").on("click", ".edit_btn", function () {
            let editId = $(this).attr("data-id");

            resetFormState($("#editForm"));

            axios({
                method: "get",
                url: route("permissioncategory.edit", editId),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editForm select[name="department_id"]').val(dataset.department_id ? dataset.department_id : "");
                    $('#editForm input[name="name"]').val(dataset.name ? dataset.name : "");
                    $('#editForm input[name="id"]').val(editId);
                    editModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editForm");

            setBusy($("#update"), true);

            axios({
                method: "post",
                url: route("permissioncategory.update"),
                data: new FormData(form),
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then((response) => {
                setBusy($("#update"), false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess("Success!", "Permission category successfully updated.");
                }

                table.init();
            }).catch((error) => {
                setBusy($("#update"), false);

                if (!error.response) {
                    console.log(error);
                    return;
                }

                // The controller answers 422 for two different things: a field
                // that failed validation (carries `errors`) and an update that
                // changed nothing (carries only a message). Treating both as
                // field errors throws on the second.
                if (error.response.status == 422 && error.response.data.errors) {
                    showErrors($("#editForm"), error.response.data.errors);
                } else if (error.response.status == 422 || error.response.status == 304) {
                    editModal.hide();
                    showSuccess("No Data Change!", error.response.data.message || "Nothing was modified.");
                } else {
                    console.log(error);
                }
            });
        });

        $("#confirmModal .agreeWith").on("click", function () {
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr("data-id");
            let action = $agreeBTN.attr("data-action");

            $("#confirmModal button").attr("disabled", "disabled");

            if (action == "DELETE") {
                axios({
                    method: "delete",
                    url: route("permissioncategory.destory", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then((response) => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Done!", "Permission category successfully deleted.");
                    }

                    table.init();
                }).catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            } else if (action == "RESTORE") {
                axios({
                    method: "post",
                    url: route("permissioncategory.restore", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then((response) => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Success!", "Permission category successfully restored.");
                    }

                    table.init();
                }).catch((error) => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            }
        });

        $("#permissioncategoryTableId").on("click", ".delete_btn", function () {
            showConfirm(
                $(this).attr("data-id"),
                "DELETE",
                confModalDelTitle,
                "Want to delete this permission category? Please click on agree to continue."
            );
        });

        $("#permissioncategoryTableId").on("click", ".restore_btn", function () {
            showConfirm(
                $(this).attr("data-id"),
                "RESTORE",
                confModalDelTitle,
                "Want to restore this permission category from the trash? Please click on agree to continue."
            );
        });
    }
})();
