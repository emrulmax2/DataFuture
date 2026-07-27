import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from "imask";

("use strict");
var table = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#academicyearsTableId", {
            ajaxURL: route("academicyears.list"),
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
                    width: 90,
                },
                {
                    title: "Academic Year",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Hesa Code",
                    field: "hesa_code",
                    headerHozAlign: "left",
                    minWidth: 130,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "DF Code",
                    field: "df_code",
                    headerHozAlign: "left",
                    minWidth: 120,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "From Date",
                    field: "from_date",
                    headerHozAlign: "left",
                    minWidth: 130,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "To Date",
                    field: "to_date",
                    headerHozAlign: "left",
                    minWidth: 130,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "Hesa Report Target Date",
                    field: "target_date_hesa_report",
                    headerHozAlign: "left",
                    minWidth: 190,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 170,
                    minWidth: 170,
                    download: false,
                    formatter(cell) {
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<a href="' + route("academicyears.show", cell.getData().id) + '" class="ss-row-action ss-row-action--view" aria-label="View academic year"><i data-lucide="eye"></i></a>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit academic year"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete academic year"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore academic year"><i data-lucide="rotate-cw"></i></button>';
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
            tableContent.download("csv", "academic-years-details.csv");
        });

        $("#tabulator-export-json").on("click", function () {
            tableContent.download("json", "academic-years-details.json");
        });

        $("#tabulator-export-xlsx").on("click", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "academic-years-details.xlsx", {
                sheetName: "Academic Years Details",
            });
        });

        $("#tabulator-export-html").on("click", function () {
            tableContent.download("html", "academic-years-details.html", {
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
    if ($("#academicyearsTableId").length) {
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

        $(".datepicker").each(function () {
            if (this.dataset.maskReady) {
                return;
            }

            IMask(this, {
                mask: "00-00-0000",
            });
            this.dataset.maskReady = "1";
        });

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = "Are you sure?";
        const isEnabledValue = (value) => value === true || value === 1 || value === "1";

        const showSuccess = (title, message) => {
            $("#successModal .successModalTitle").html(title);
            $("#successModal .successModalDesc").html(message);
            succModal.show();
        };

        const setCodeField = ($form, key, enabled, value = "") => {
            const isEnabled = isEnabledValue(enabled);
            const areaClass = key === "hesa" ? ".hesa_code_area" : ".df_code_area";
            const checkboxName = key === "hesa" ? "is_hesa" : "is_df";
            const inputName = key === "hesa" ? "hesa_code" : "df_code";
            const $area = $form.find(areaClass);
            const $checkbox = $form.find(`input[name="${checkboxName}"]`);
            const $input = $form.find(`input[name="${inputName}"]`);

            $checkbox.prop("checked", isEnabled);
            $checkbox.attr("aria-checked", isEnabled ? "true" : "false");
            $area.attr("data-enabled", isEnabled ? "true" : "false");
            $input.prop("disabled", !isEnabled);
            $input.val(isEnabled ? value : "");
        };

        const resetFormState = ($form) => {
            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");
            $form.find('input[type="text"], input[type="hidden"]').val("");
            $form.find('input[name="id"]').val("0");
            setCodeField($form, "hesa", false);
            setCodeField($form, "df", false);
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

        const confirmModalEl = document.getElementById("confirmModal");
        confirmModalEl.addEventListener("hidden.tw.modal", function () {
            $("#confirmModal .agreeWith").attr("data-id", "0");
            $("#confirmModal .agreeWith").attr("data-action", "none");
            $("#confirmModal button").removeAttr("disabled");
        });

        $('#addForm input[name="is_hesa"]').on("change", function () {
            setCodeField($("#addForm"), "hesa", $(this).prop("checked"));
        });

        $('#addForm input[name="is_df"]').on("change", function () {
            setCodeField($("#addForm"), "df", $(this).prop("checked"));
        });

        $('#editForm input[name="is_hesa"]').on("change", function () {
            setCodeField($("#editForm"), "hesa", $(this).prop("checked"));
        });

        $('#editForm input[name="is_df"]').on("change", function () {
            setCodeField($("#editForm"), "df", $(this).prop("checked"));
        });

        $("#addForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("addForm");

            document.querySelector("#save").setAttribute("disabled", "disabled");
            document.querySelector("#save svg").style.cssText = "display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route("academicyears.store"),
                data: form_data,
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                document.querySelector("#save").removeAttribute("disabled");
                document.querySelector("#save svg").style.cssText = "display: none;";

                if (response.status == 200) {
                    addModal.hide();
                    showSuccess("Success!", "Academic year successfully inserted.");
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

        $("#academicyearsTableId").on("click", ".edit_btn", function () {
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            resetFormState($("#editForm"));

            axios({
                method: "get",
                url: route("academicyears.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editForm input[name="name"]').val(dataset.name ? dataset.name : "");
                    setCodeField($("#editForm"), "hesa", dataset.is_hesa, dataset.hesa_code ? dataset.hesa_code : "");
                    setCodeField($("#editForm"), "df", dataset.is_df, dataset.df_code ? dataset.df_code : "");
                    $('#editForm input[name="from_date"]').val(dataset.from_date ? dataset.from_date : "");
                    $('#editForm input[name="to_date"]').val(dataset.to_date ? dataset.to_date : "");
                    $('#editForm input[name="target_date_hesa_report"]').val(dataset.target_date_hesa_report ? dataset.target_date_hesa_report : "");
                    $('#editForm input[name="id"]').val(editId);
                    editModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $("#editForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editForm input[name="id"]').val();
            const form = document.getElementById("editForm");

            document.querySelector("#update").setAttribute("disabled", "disabled");
            document.querySelector("#update svg").style.cssText = "display: inline-block;";

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("academicyears.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#update").removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    editModal.hide();
                    showSuccess("Success!", "Academic year successfully updated.");
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
                        showSuccess("No Data Change!", error.response.statusText);
                    } else {
                        console.log("error");
                    }
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
                    url: route("academicyears.destory", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Done!", "Academic year successfully deleted!");
                    }
                    table.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            } else if (action == "RESTORE") {
                axios({
                    method: "post",
                    url: route("academicyears.restore", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Success!", "Academic year successfully restored!");
                    }
                    table.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            }
        });

        $("#academicyearsTableId").on("click", ".delete_btn", function () {
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr("data-id");

            showConfirm(
                rowID,
                "DELETE",
                confModalDelTitle,
                "Want to delete this academic year? Please click on agree to continue."
            );
        });

        $("#academicyearsTableId").on("click", ".restore_btn", function () {
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr("data-id");

            showConfirm(
                courseID,
                "RESTORE",
                confModalDelTitle,
                "Want to restore this academic year from the trash? Please click on agree to continue."
            );
        });
    }
})();
