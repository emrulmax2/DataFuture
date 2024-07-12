import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
 
("use strict");
var settingsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#settingsListTable", {
            ajaxURL: route("statuses.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: "180",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                },{
                    title: "Letter/Email",
                    field: "id",
                    headerSort: false,
                    hozAlign: "left",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        if(cell.getData().letter_set_id > 0){
                            return '<div style="white-space: normal; word-break: break-all;">Letter: '+cell.getData().letter_name+(cell.getData().signatory_name != '' ? '<br/>Signatory: '+cell.getData().signatory_name : '')+'</div>';
                        }else if(cell.getData().email_template_id > 0){
                            return '<div style="white-space: normal; word-break: break-all;">Email: '+cell.getData().email_name+'</div>';
                        }else{
                            return '';
                        }
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editSettingsModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
            },
        });

        // Redraw table onresize
        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Status Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
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
            //persist: false,
            maxOptions: null,
            create: false,
            allowEmptyOption: true,
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

        const addSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSettingsModal"));
        const editSettingsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSettingsModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addSettingsModalEl = document.getElementById('addSettingsModal')
        addSettingsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addSettingsModal .acc__input-error').html('');
            $('#addSettingsModal .modal-body input').val('');
            $('#addSettingsModal .modal-body select').val('');

            letter_set_id.clear(true);
            $('#addSettingsModal .signatoryWrap').fadeOut('fast', function(){
                signatory_id.clear(true);
            });
            email_template_id.clear(true);
        });
        
        const editSettingsModalEl = document.getElementById('editSettingsModal')
        editSettingsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editSettingsModal .acc__input-error').html('');
            $('#editSettingsModal .modal-body input').val('');
            $('#editSettingsModal .modal-body select').val('');
            $('#editSettingsModal input[name="id"]').val('0');

            edit_letter_set_id.clear(true);
            $('#editSettingsModal .signatoryWrap').fadeOut('fast', function(){
                edit_signatory_id.clear(true);
            });
            edit_email_template_id.clear(true);
        });

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
        
            document.querySelector('#saveSettings').setAttribute('disabled', 'disabled');
            document.querySelector("#saveSettings svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('statuses.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveSettings').removeAttribute('disabled');
                document.querySelector("#saveSettings svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addSettingsModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                settingsListTable.init();
            }).catch(error => {
                document.querySelector('#saveSettings').removeAttribute('disabled');
                document.querySelector("#saveSettings svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addSettingsForm .${key}`).addClass('border-danger');
                            $(`#addSettingsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#settingsListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

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

        // Update Course Data
        $("#editSettingsForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editSettingsForm input[name="id"]').val();
            const form = document.getElementById("editSettingsForm");

            document.querySelector('#updateSettings').setAttribute('disabled', 'disabled');
            document.querySelector('#updateSettings svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("statuses.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateSettings").removeAttribute("disabled");
                    document.querySelector("#updateSettings svg").style.cssText = "display: none;";
                    editSettingsModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                settingsListTable.init();
            }).catch((error) => {
                document.querySelector("#updateSettings").removeAttribute("disabled");
                document.querySelector("#updateSettings svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editSettingsForm .${key}`).addClass('border-danger')
                            $(`#editSettingsForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editSettingsModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModal").html("Oops!");
                            $("#successModal .successModal").html('No data change found!');
                        });
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

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        });
                    }
                    settingsListTable.init();
                }).catch(error =>{
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

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        });
                    }
                    settingsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#settingsListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#settingsListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();