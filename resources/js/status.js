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

var settingsListTable = (function () {
    var _tableGen = function () {
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        if (window.statusesTableInstance) {
            window.statusesTableInstance.destroy();
        }

        let tableContent = new Tabulator("#settingsListTable", {
            ajaxURL: route("statuses.list"),
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
                    width: 76,
                    minWidth: 64,
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    minWidth: 150,
                    widthGrow: 1.3,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Phase",
                    field: "type",
                    headerHozAlign: "left",
                    minWidth: 108,
                    formatter(cell) {
                        return `<span class="ss-phase-pill">${escapeHtml(cell.getValue())}</span>`;
                    },
                },
                {
                    title: "Process",
                    field: "process",
                    headerHozAlign: "left",
                    headerSort: false,
                    minWidth: 145,
                    widthGrow: 1.1,
                    formatter(cell) {
                        return escapeHtml(cell.getValue());
                    },
                },
                {
                    title: "Letter/Email",
                    field: "template",
                    headerSort: false,
                    hozAlign: "left",
                    headerHozAlign: "left",
                    minWidth: 180,
                    widthGrow: 1.45,
                    variableHeight: true,
                    formatter(cell) {
                        const data = cell.getData();

                        if (data.letter_set_id > 0) {
                            return '<span class="ss-template-cell"><strong>Letter</strong>' + escapeHtml(data.letter_name) + (data.signatory_name != "" ? '<small>Signatory: ' + escapeHtml(data.signatory_name) + "</small>" : "") + "</span>";
                        }

                        if (data.email_template_id > 0) {
                            return '<span class="ss-template-cell"><strong>Email</strong>' + escapeHtml(data.email_name) + "</span>";
                        }

                        return '<span class="ss-cell-wrap">&mdash;</span>';
                    },
                },
                {
                    title: "Eligible for Award",
                    field: "eligible_for_award",
                    headerHozAlign: "left",
                    minWidth: 132,
                    formatter(cell) {
                        const isEligible = cell.getValue() == 1;
                        return `<span class="ss-status-pill ${isEligible ? "is-active" : "is-inactive"}"><span></span>${isEligible ? "Yes" : "No"}</span>`;
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
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit status"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete status"><i data-lucide="trash-2"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore status"><i data-lucide="rotate-cw"></i></button>';
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

        window.statusesTableInstance = tableContent;

        if (window.statusesTableResizeHandler) {
            window.removeEventListener("resize", window.statusesTableResizeHandler);
        }

        window.statusesTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.statusesTableResizeHandler);

        $("#tabulator-export-csv").off("click.statuses").on("click.statuses", function () {
            tableContent.download("csv", "status-details.csv");
        });

        $("#tabulator-export-xlsx").off("click.statuses").on("click.statuses", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "status-details.xlsx", {
                sheetName: "Status Details",
            });
        });

        $("#tabulator-print").off("click.statuses").on("click.statuses", function () {
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
    // Tabulator
    if ($("#settingsListTable").length) {
        // Init Table
        settingsListTable.init();

        // Filter function
        function filterHTMLForm() {
            settingsListTable.init();
        }

        // On submit filter form
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

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLForm();
        });

        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            maxOptions: null,
            dropdownParent: 'body',
            dropdownClass: 'ts-dropdown ss-settings-tom-dropdown',
            create: false,
            allowEmptyOption: true,
            copyClassesToDropdown: false,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };
    
        let letter_set_id = new TomSelect('#letter_set_id', tomOptions);
        let email_template_id = new TomSelect('#email_template_id', tomOptions);
        let edit_letter_set_id = new TomSelect('#edit_letter_set_id', tomOptions);
        let edit_email_template_id = new TomSelect('#edit_email_template_id', tomOptions);
        let signatory_id = new TomSelect('#signatory_id', tomOptions);
        let edit_signatory_id = new TomSelect('#edit_signatory_id', tomOptions);

        let process_list_id = new TomSelect('#process_list_id', tomOptions);
        let edit_process_list_id = new TomSelect('#edit_process_list_id', tomOptions);

        const addSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSettingsModal"));
        const editSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSettingsModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const setBusy = ($button, isBusy) => {
            $button.prop("disabled", isBusy);
            $button.find(".ss-spinner").css("display", isBusy ? "inline-block" : "none");
        };

        const showSuccess = (title, message) => {
            $("#successModal .successModalTitle").html(title);
            $("#successModal .successModalDesc").html(message);
            succModal.show();
        };

        const showErrors = ($form, errors) => {
            for (const [key, val] of Object.entries(errors)) {
                $form.find(`.${key}`).addClass("border-danger");
                $form.find(`.error-${key}`).html(Array.isArray(val) ? val[0] : val);
            }
        };

        const resetProcessSelect = (select, placeholder = "Select a phase first") => {
            select.clear(true);
            select.clearOptions();
            select.addOption({
                value: "",
                text: placeholder,
            });
            select.refreshOptions(false);
            select.disable();
        };

        const populateProcessSelect = (select, rows, selectedValue = "") => {
            select.clear(true);
            select.clearOptions();

            const options = Array.isArray(rows) && rows.length > 0
                ? rows
                : [{ id: "", name: "No process found" }];

            $.each(options, function(index, row) {
                select.addOption({
                    value: String(row.id ?? ""),
                    text: row.name ?? "Please Select",
                });
            });

            select.enable();
            if (selectedValue) {
                select.addItem(String(selectedValue), true);
            }
            select.refreshOptions(false);
        };

        const loadProcessOptions = (phaseType, select, selectedValue = "") => {
            resetProcessSelect(select);

            if (!phaseType) {
                return Promise.resolve();
            }

            return axios({
                method: "post",
                url: route('statuses.get.process'),
                data: {theType : phaseType},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                populateProcessSelect(select, response.data.res, selectedValue);
            }).catch(error => {
                populateProcessSelect(select, [{ id: "", name: "No process found" }]);
                if (error.response && error.response.status != 304) {
                    console.log('error');
                }
            });
        };

        const updateAwardToggleText = ($field) => {
            const isChecked = $field.find('input[name="eligible_for_award"]').is(":checked");
            $field.find(".ss-status-toggle__copy strong").text(isChecked ? "Yes" : "No");
            $field.find(".ss-status-toggle__copy small").text(
                isChecked ? "Eligible for award workflows" : "Not eligible for award workflows"
            );
        };

        const resetAddForm = () => {
            $('#addSettingsModal .acc__input-error').html('');
            $('#addSettingsModal .border-danger').removeClass('border-danger');
            $('#addSettingsModal .modal-body input:not([type="checkbox"])').val('');
            $('#addSettingsModal .modal-body select').val('');
            $('#addSettingsModal .modal-body input[name="eligible_for_award"]').prop('checked', false);
            updateAwardToggleText($('#addSettingsModal .ss-status-toggle'));

            letter_set_id.clear(true);
            $('#addSettingsModal .signatoryWrap').fadeOut('fast', function(){
                signatory_id.clear(true);
            });
            email_template_id.clear(true);

            resetProcessSelect(process_list_id);
            setBusy($("#saveSettings"), false);
        };

        const resetEditForm = () => {
            $('#editSettingsModal .acc__input-error').html('');
            $('#editSettingsModal .border-danger').removeClass('border-danger');
            $('#editSettingsModal .modal-body input:not([type="checkbox"])').val('');
            $('#editSettingsModal .modal-body select').val('');
            $('#editSettingsModal input[name="id"]').val('0');
            $('#editSettingsModal .modal-body input[name="eligible_for_award"]').prop('checked', false);
            updateAwardToggleText($('#editSettingsModal .ss-status-toggle'));

            edit_letter_set_id.clear(true);
            $('#editSettingsModal .signatoryWrap').fadeOut('fast', function(){
                edit_signatory_id.clear(true);
            });
            edit_email_template_id.clear(true);

            resetProcessSelect(edit_process_list_id);
            setBusy($("#updateSettings"), false);
        };

        resetAddForm();
        resetEditForm();

        $(".ss-status-toggle input[name='eligible_for_award']").on("change", function () {
            updateAwardToggleText($(this).closest(".ss-status-toggle"));
        });

        const addSettingsModalEl = document.getElementById('addSettingsModal')
        addSettingsModalEl.addEventListener('show.tw.modal', function() {
            resetAddForm();
        });

        addSettingsModalEl.addEventListener('hide.tw.modal', function() {
            resetAddForm();
        });
        
        const editSettingsModalEl = document.getElementById('editSettingsModal')
        editSettingsModalEl.addEventListener('hide.tw.modal', function() {
            resetEditForm();
        });

        document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function() {
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
            $('#confirmModal button').removeAttr('disabled');
        });

        $('#addSettingsForm select[name="type"]').on('change', function(){
            loadProcessOptions($(this).val(), process_list_id);
        })

        $('#letter_set_id').on('change', function(e){
            email_template_id.clear(true);
            $('#addSettingsModal .signatoryWrap').fadeIn('fast', function(){
                signatory_id.clear(true);
            });
        })

        $('#email_template_id').on('change', function(e){
            letter_set_id.clear(true);
            $('#addSettingsModal .signatoryWrap').fadeOut('fast', function(){
                signatory_id.clear(true);
            });
        })

        $('#addSettingsForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addSettingsForm');

            setBusy($("#saveSettings"), true);

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('statuses.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                setBusy($("#saveSettings"), false);
                
                if (response.status == 200) {
                    addSettingsModal.hide();
                    showSuccess("Success!", "Status successfully inserted.");
                }
                settingsListTable.init();
            }).catch(error => {
                setBusy($("#saveSettings"), false);
                if (error.response) {
                    if (error.response.status == 422) {
                        showErrors($("#addSettingsForm"), error.response.data.errors);
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#settingsListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            resetEditForm();

            axios({
                method: "get",
                url: route("statuses.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editSettingsModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        $('#editSettingsModal select[name="type"]').val(dataset.type ? dataset.type : '');
                        
                        $('#editSettingsModal input[name="id"]').val(editId);
                        if(dataset.letter_set_id > 0){
                            edit_letter_set_id.addItem(dataset.letter_set_id, true);
                            edit_email_template_id.clear(true);

                            $('#editSettingsModal .signatoryWrap').fadeIn('fast', function(){
                                if(dataset.signatory_id > 0){
                                    edit_signatory_id.addItem(dataset.signatory_id, true);
                                }else{
                                    edit_signatory_id.clear(true);
                                }
                            });
                        }else{
                            edit_letter_set_id.clear(true);
                            $('#editSettingsModal .signatoryWrap').fadeOut('fast', function(){
                                edit_signatory_id.clear(true);
                            });
                        }
                        if(dataset.email_template_id > 0){
                            edit_email_template_id.addItem(dataset.email_template_id, true);
                            edit_letter_set_id.clear(true);
                            $('#editSettingsModal .signatoryWrap').fadeOut('fast', function(){
                                edit_signatory_id.clear(true);
                            });
                        }else{
                            edit_email_template_id.clear(true);
                        }

                        populateProcessSelect(edit_process_list_id, dataset.processes, dataset.process_list_id);

                        if(dataset.eligible_for_award == 1){
                            $('#editSettingsModal input[name="eligible_for_award"]').prop('checked', true);
                        }else{
                            $('#editSettingsModal input[name="eligible_for_award"]').prop('checked', false);
                        }
                        updateAwardToggleText($('#editSettingsModal .ss-status-toggle'));
                        editSettingsModal.show();
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        $('#edit_letter_set_id').on('change', function(e){
            edit_email_template_id.clear(true);
            $('#editSettingsModal .signatoryWrap').fadeIn('fast', function(){
                edit_signatory_id.clear(true);
            });
        })

        $('#edit_email_template_id').on('change', function(e){
            edit_letter_set_id.clear(true);
            $('#editSettingsModal .signatoryWrap').fadeOut('fast', function(){
                edit_signatory_id.clear(true);
            });
        })

        $('#editSettingsForm select[name="type"]').on('change', function(){
            loadProcessOptions($(this).val(), edit_process_list_id);
        });

        // Update Course Data
        $("#editSettingsForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editSettingsForm");

            setBusy($("#updateSettings"), true);

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("statuses.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                setBusy($("#updateSettings"), false);
                if (response.status == 200) {
                    editSettingsModal.hide();
                    showSuccess("Success!", "Status successfully updated.");
                }
                settingsListTable.init();
            }).catch((error) => {
                setBusy($("#updateSettings"), false);
                if (error.response) {
                    if (error.response.status == 422) {
                        showErrors($("#editSettingsForm"), error.response.data.errors);
                    }else if (error.response.status == 304) {
                        editSettingsModal.hide();
                        showSuccess("No Data Change!", error.response.statusText);
                    } else {
                        console.log("error");
                    }
                }
            });
        });

        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('statuses.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();
                        showSuccess("Done!", "Status successfully deleted.");
                    }
                    settingsListTable.init();
                }).catch(error =>{
                    $('#confirmModal button').removeAttr('disabled');
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('statuses.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();
                        showSuccess("Success!", "Status successfully restored.");
                    }
                    settingsListTable.init();
                }).catch(error =>{
                    $('#confirmModal button').removeAttr('disabled');
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#settingsListTable').on('click', '.delete_btn', function(){
            let rowID = $(this).attr('data-id');

            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Want to delete this status? Please click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            confirmModal.show();
        });

        // Restore Course
        $('#settingsListTable').on('click', '.restore_btn', function(){
            let statusID = $(this).attr('data-id');

            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Want to restore this status from the trash? Please click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', statusID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            confirmModal.show();
        });
    }
})();
