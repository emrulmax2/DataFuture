import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var table = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#assessmentTypeTableId", {
            ajaxURL: route("assessment-type.list"),
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
                    width: 110,
                },
                {
                    title: "Assessment Type",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 240,
                },
                {
                    title: "Code",
                    field: "code",
                    headerHozAlign: "left",
                    minWidth: 140,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "Status",
                    field: "is_active",
                    headerHozAlign: "left",
                    minWidth: 140,
                    formatter(cell) {
                        const isActive = cell.getValue() == 1;
                        return `<span class="ss-status-pill ${isActive ? "is-active" : "is-inactive"}"><span></span>${isActive ? "Active" : "Inactive"}</span>`;
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 180,
                    minWidth: 180,
                    download: false,
                    formatter(cell) {
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit assessment type"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete assessment type"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore assessment type"><i data-lucide="rotate-cw"></i></button>';
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

        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        $("#tabulator-export-csv").on("click", function () {
            tableContent.download("csv", "assessment-type-details.csv");
        });

        $("#tabulator-export-json").on("click", function () {
            tableContent.download("json", "assessment-type-details.json");
        });

        $("#tabulator-export-xlsx").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "assessment-type-details.xlsx", {
                sheetName: "Assessment Type Details",
            });
        });

        $("#tabulator-export-html").on("click", function () {
            tableContent.download("html", "assessment-type-details.html", {
                style: true,
            });
        });

        $("#tabulator-print").on("click", function () {
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
    if ($("#assessmentTypeTableId").length) {
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
        let confModalDelTitle = "Are you sure?";
        const isActiveValue = (value) => value === true || value === 1 || value === "1";

        const updateActiveFieldText = ($field) => {
            const isChecked = $field.find('input[name="is_active"]').is(":checked");
            $field.find(".ss-status-toggle__copy strong").text(isChecked ? "Active" : "Inactive");
            $field.find(".ss-status-toggle__copy small").text(
                isChecked ? "Available for new assessment setup" : "Not available for new assessment setup"
            );
        };

        const setActiveField = ($form, value) => {
            const $field = $form.find(".ss-status-toggle");
            $field.find('input[name="is_active"]').prop("checked", isActiveValue(value));
            updateActiveFieldText($field);
        };

        const resetFormState = ($form) => {
            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");
            $form.find('input[type="text"], input[type="hidden"]').val("");
            $form.find('input[name="id"]').val("0");
            setActiveField($form, false);
        };

        resetFormState($("#addForm"));
        resetFormState($("#editForm"));

        $(".ss-status-toggle input[name='is_active']").on("change", function () {
            updateActiveFieldText($(this).closest(".ss-status-toggle"));
        });

        const addModalEl = document.getElementById("addModal");
        addModalEl.addEventListener("show.tw.modal", function () {
            resetFormState($("#addForm"));
        });

        addModalEl.addEventListener("hide.tw.modal", function () {
            resetFormState($("#addForm"));
        });

        const editModalEl = document.getElementById("editModal");
        editModalEl.addEventListener("hide.tw.modal", function () {
            resetFormState($("#editForm"));
        });

        $("#addForm").on("submit", function (e) {
            const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
            e.preventDefault();
            const form = document.getElementById("addForm");

            document.querySelector("#save").setAttribute("disabled", "disabled");
            document.querySelector("#save svg").style.cssText = "display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route("assessment-type.store"),
                data: form_data,
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                document.querySelector("#save").removeAttribute("disabled");
                document.querySelector("#save svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    addModal.hide();
                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function () {
                            $("#successModal .successModalTitle").html("Success!");
                            $("#successModal .successModalDesc").html("Assessment type successfully inserted");
                        });
                }
                table.init();
            }).catch(error => {
                document.querySelector("#save").removeAttribute("disabled");
                document.querySelector("#save svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass("border-danger");
                            $(`#addForm .error-${key}`).html(val);
                        }
                    } else {
                        console.log("error");
                    }
                }
            });
        });

        $("#assessmentTypeTableId").on("click", ".edit_btn", function () {
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");
            const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));

            resetFormState($("#editForm"));

            axios({
                method: "get",
                url: route("assessment-type.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editForm input[name="name"]').val(dataset.name ? dataset.name : "");
                    $('#editForm input[name="code"]').val(dataset.code ? dataset.code : "");
                    setActiveField($("#editForm"), dataset.is_active);
                    $('#editForm input[name="id"]').val(editId);
                    editModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editForm").on("submit", function (e) {
            let editId = $('#editForm input[name="id"]').val();
            const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
            const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

            e.preventDefault();
            const form = document.getElementById("editForm");

            document.querySelector("#update").setAttribute("disabled", "disabled");
            document.querySelector("#update svg").style.cssText = "display: inline-block;";

            let form_data = new FormData(form);
            form_data.append("_method", "PUT");

            axios({
                method: "post",
                url: route("assessment-type.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#update").removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    editModal.hide();

                    succModal.show();
                    document.getElementById("successModal")
                        .addEventListener("shown.tw.modal", function () {
                            $("#successModal .successModalTitle").html("Success!");
                            $("#successModal .successModalDesc").html("Assessment type successfully updated");
                        });
                }
                table.init();
            }).catch((error) => {
                document.querySelector("#update").removeAttribute("disabled");
                document.querySelector("#update svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editForm .${key}`).addClass("border-danger");
                            $(`#editForm .error-${key}`).html(val);
                        }
                    } else if (error.response.status == 304) {
                        editModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function () {
                            $("#successModal .successModalTitle").html("No Data Change!");
                            $("#successModal .successModalDesc").html(message);
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });

        $("#confirmModal .agreeWith").on("click", function () {
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
                $("#confirmModal .agreeWith").attr("data-id", "0");
                $("#confirmModal .agreeWith").attr("data-action", "none");
            });

            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr("data-id");
            let action = $agreeBTN.attr("data-action");

            $("#confirmModal button").attr("disabled", "disabled");
            if (action == "DELETE") {
                axios({
                    method: "delete",
                    url: route("assessment-type.destroy", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();

                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function () {
                            $("#successModal .successModalTitle").html("Done!");
                            $("#successModal .successModalDesc").html("Assessment type successfully deleted!");
                        });
                    }
                    table.init();
                }).catch(error => {
                    console.log(error);
                });
            } else if (action == "RESTORE") {
                axios({
                    method: "post",
                    url: route("assessment-type.restore", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();

                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function () {
                            $("#successModal .successModalTitle").html("Success!");
                            $("#successModal .successModalDesc").html("Assessment type successfully restored!");
                        });
                    }
                    table.init();
                }).catch(error => {
                    console.log(error);
                });
            }
        });

        $("#assessmentTypeTableId").on("click", ".delete_btn", function () {
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
                $("#confirmModal .agreeWith").attr("data-id", "0");
                $("#confirmModal .agreeWith").attr("data-action", "none");
            });
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr("data-id");

            confModal.show();
            document.getElementById("confirmModal").addEventListener("shown.tw.modal", function () {
                $("#confirmModal .confModTitle").html(confModalDelTitle);
                $("#confirmModal .confModDesc").html("Want to delete this assessment type? Please click on agree to continue.");
                $("#confirmModal .agreeWith").attr("data-id", rowID);
                $("#confirmModal .agreeWith").attr("data-action", "DELETE");
            });
        });

        $("#assessmentTypeTableId").on("click", ".restore_btn", function () {
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById("confirmModal").addEventListener("hidden.tw.modal", function () {
                $("#confirmModal .agreeWith").attr("data-id", "0");
                $("#confirmModal .agreeWith").attr("data-action", "none");
            });
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr("data-id");

            confModal.show();
            document.getElementById("confirmModal").addEventListener("shown.tw.modal", function () {
                $("#confirmModal .confModTitle").html(confModalDelTitle);
                $("#confirmModal .confModDesc").html("Want to restore this assessment type from the trash? Please click on agree to continue.");
                $("#confirmModal .agreeWith").attr("data-id", courseID);
                $("#confirmModal .agreeWith").attr("data-action", "RESTORE");
            });
        });
    }
})();
