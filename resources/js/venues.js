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

        if (window.venuesTableInstance) {
            window.venuesTableInstance.destroy();
        }

        let tableContent = new Tabulator("#venuesTableId", {
            ajaxURL: route("venues.list"),
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
                    width: 72,
                    minWidth: 64,
                },
                {
                    title: "Venue Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 150,
                    widthGrow: 1.7,
                    formatter(cell) {
                        return '<a class="ss-table-link" href="' + route("venues.show", cell.getData().id) + '">' + escapeHtml(cell.getValue()) + "</a>";
                    },
                },
                {
                    title: "ID Number",
                    field: "idnumber",
                    headerHozAlign: "left",
                    minWidth: 86,
                    widthGrow: 0.7,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "UKPRN",
                    field: "ukprn",
                    headerHozAlign: "left",
                    minWidth: 96,
                    widthGrow: 0.75,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Postal Code",
                    field: "postcode",
                    headerHozAlign: "left",
                    minWidth: 98,
                    widthGrow: 0.75,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Full Address",
                    field: "address",
                    headerHozAlign: "left",
                    minWidth: 150,
                    widthGrow: 2,
                    variableHeight: true,
                    formatter(cell) {
                        return '<span class="ss-cell-wrap">' + escapeHtml(cell.getValue()) + "</span>";
                    },
                },
                {
                    title: "IP",
                    field: "ip",
                    headerHozAlign: "left",
                    minWidth: 132,
                    widthGrow: 1.4,
                    variableHeight: true,
                    formatter(cell) {
                        return '<span class="ss-cell-wrap ss-cell-wrap--mono">' + escapeHtml(cell.getValue()) + "</span>";
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
                            btns += '<a href="' + route("venues.show", cell.getData().id) + '" class="ss-row-action ss-row-action--view" aria-label="View venue"><i data-lucide="eye"></i></a>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit venue"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete venue"><i data-lucide="trash-2"></i></button>';
                        } else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore venue"><i data-lucide="rotate-cw"></i></button>';
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

        window.venuesTableInstance = tableContent;

        if (window.venuesTableResizeHandler) {
            window.removeEventListener("resize", window.venuesTableResizeHandler);
        }

        window.venuesTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.venuesTableResizeHandler);

        $("#tabulator-export-csv").off("click.venues").on("click.venues", function () {
            tableContent.download("csv", "venues.csv");
        });

        $("#tabulator-export-xlsx").off("click.venues").on("click.venues", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "venues.xlsx", {
                sheetName: "Venues",
            });
        });

        $("#tabulator-print").off("click.venues").on("click.venues", function () {
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
    if ($("#venuesTableId").length) {
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
        const addModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addVenueModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editVenueModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const confModalDelTitle = "Are you sure?";
        const isActiveValue = (value) => value === true || value === 1 || value === "1";

        const setBusy = ($button, isBusy) => {
            $button.prop("disabled", isBusy);
            $button.find(".ss-spinner").css("display", isBusy ? "inline-block" : "none");
        };

        const showSuccess = (title, message) => {
            $("#successModal .successModalTitle").html(title);
            $("#successModal .successModalDesc").html(message);
            succModal.show();
        };

        const updateActiveFieldText = ($field) => {
            const isChecked = $field.find('input[type="checkbox"][name="active"]').is(":checked");
            $field.find(".ss-status-toggle__copy strong").text(isChecked ? "Active" : "Inactive");
            $field.find(".ss-status-toggle__copy small").text(
                isChecked ? "Available for campus setup" : "Hidden from active campus setup"
            );
        };

        const setActiveField = ($form, value) => {
            const $field = $form.find(".ss-status-toggle");

            if (!$field.length) {
                return;
            }

            $field.find('input[type="checkbox"][name="active"]').prop("checked", isActiveValue(value));
            updateActiveFieldText($field);
        };

        const resetFormState = ($form) => {
            if ($form[0]) {
                $form[0].reset();
            }

            $form.find(".acc__input-error").html("");
            $form.find(".border-danger").removeClass("border-danger");
            $form.find('input[name="id"]').val("0");
            setActiveField($form, false);
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

        $(".ss-status-toggle input[type='checkbox'][name='active']").on("change", function () {
            updateActiveFieldText($(this).closest(".ss-status-toggle"));
        });

        document.getElementById("addVenueModal").addEventListener("show.tw.modal", function () {
            resetFormState($("#addForm"));
        });

        document.getElementById("addVenueModal").addEventListener("hide.tw.modal", function () {
            resetFormState($("#addForm"));
            setBusy($("#save"), false);
        });

        document.getElementById("editVenueModal").addEventListener("hide.tw.modal", function () {
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

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route("venues.store"),
                data: form_data,
                headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
            }).then(response => {
                setBusy($("#save"), false);

                if (response.status == 200) {
                    addModal.hide();
                    showSuccess("Success!", "Venue successfully inserted.");
                }

                table.init();
            }).catch(error => {
                setBusy($("#save"), false);
                if (error.response) {
                    if (error.response.status == 422) {
                        showErrors($("#addForm"), error.response.data.errors);
                    } else {
                        console.log("error");
                    }
                }
            });
        });

        $("#venuesTableId").on("click", ".edit_btn", function () {
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            resetFormState($("#editForm"));

            axios({
                method: "get",
                url: route("venues.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editForm input[name="name"]').val(dataset.name ? dataset.name : "");
                    $('#editForm input[name="idnumber"]').val(dataset.idnumber ? dataset.idnumber : "");
                    $('#editForm input[name="ukprn"]').val(dataset.ukprn ? dataset.ukprn : "");
                    $('#editForm input[name="postcode"]').val(dataset.postcode ? dataset.postcode : "");
                    $('#editForm textarea[name="address"]').val(dataset.address ? dataset.address : "");
                    $('#editForm textarea[name="ip_addresses"]').val(dataset.ip_addresses ? dataset.ip_addresses : "");
                    setActiveField($("#editForm"), dataset.active);
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

            setBusy($("#update"), true);

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route("venues.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                setBusy($("#update"), false);

                if (response.status == 200) {
                    editModal.hide();
                    showSuccess("Success!", "Venue successfully updated.");
                }

                table.init();
            }).catch((error) => {
                setBusy($("#update"), false);
                if (error.response) {
                    if (error.response.status == 422) {
                        showErrors($("#editForm"), error.response.data.errors);
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
                    url: route("venues.destory", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Done!", "Venue successfully deleted.");
                    }

                    table.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            } else if (action == "RESTORE") {
                axios({
                    method: "post",
                    url: route("venues.restore", recordID),
                    headers: { "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
                }).then(response => {
                    if (response.status == 200) {
                        $("#confirmModal button").removeAttr("disabled");
                        confModal.hide();
                        showSuccess("Success!", "Venue successfully restored.");
                    }

                    table.init();
                }).catch(error => {
                    $("#confirmModal button").removeAttr("disabled");
                    console.log(error);
                });
            }
        });

        $("#venuesTableId").on("click", ".delete_btn", function () {
            let rowID = $(this).attr("data-id");

            showConfirm(
                rowID,
                "DELETE",
                confModalDelTitle,
                "Want to delete this venue? Please click on agree to continue."
            );
        });

        $("#venuesTableId").on("click", ".restore_btn", function () {
            let venueID = $(this).attr("data-id");

            showConfirm(
                venueID,
                "RESTORE",
                confModalDelTitle,
                "Want to restore this venue from the trash? Please click on agree to continue."
            );
        });
    }
})();
