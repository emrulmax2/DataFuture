import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var DisableAllowanceListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-DISABLEALLW").val() != "" ? $("#query-DISABLEALLW").val() : "";
        let status = $("#status-DISABLEALLW").val() != "" ? $("#status-DISABLEALLW").val() : "";
        let tableContent = new Tabulator("#DisableAllowanceListTable", {
            ajaxURL: route("disable.allowance.list"),
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
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Hesa Code",
                    field: "hesa_code",
                    headerHozAlign: "left",
                },
                {
                    title: "DF Code",
                    field: "df_code",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editDisableAllowanceModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-DISABLEALLW").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-DISABLEALLW").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-DISABLEALLW").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-DISABLEALLW").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-DISABLEALLW").on("click", function (event) {
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
    if ($("#DisableAllowanceListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'DisableAllowanceListTable'){
                DisableAllowanceListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormDISABLEALLW() {
            DisableAllowanceListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-DISABLEALLW")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormDISABLEALLW();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-DISABLEALLW").on("click", function (event) {
            filterHTMLFormDISABLEALLW();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-DISABLEALLW").on("click", function (event) {
            $("#query-DISABLEALLW").val("");
            $("#status-DISABLEALLW").val("1");
            filterHTMLFormDISABLEALLW();
        });

        const addDisableAllowanceModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addDisableAllowanceModal"));
        const editDisableAllowanceModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editDisableAllowanceModal"));
        const disableAllowanceImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#disableAllowanceImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addDisableAllowanceModalEl = document.getElementById('addDisableAllowanceModal')
        addDisableAllowanceModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addDisableAllowanceModal .acc__input-error').html('');
            $('#addDisableAllowanceModal .modal-body input:not([type="checkbox"])').val('');

            $('#addDisableAllowanceModal input[name="is_hesa"]').prop('checked', false);
            $('#addDisableAllowanceModal .hesa_code_area').fadeOut('fast', function(){
                $('#addDisableAllowanceModal .hesa_code_area input').val('');
            });
            $('#addDisableAllowanceModal input[name="is_df"]').prop('checked', false);
            $('#addDisableAllowanceModal .df_code_area').fadeOut('fast', function(){
                $('#addDisableAllowanceModal .df_code_area input').val('');
            })
            $('#addDisableAllowanceModal input[name="active"]').prop('checked', true);
        });
        
        const editDisableAllowanceModalEl = document.getElementById('editDisableAllowanceModal')
        editDisableAllowanceModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editDisableAllowanceModal .acc__input-error').html('');
            $('#editDisableAllowanceModal .modal-body input:not([type="checkbox"])').val('');
            $('#editDisableAllowanceModal input[name="id"]').val('0');

            $('#editDisableAllowanceModal input[name="is_hesa"]').prop('checked', false);
            $('#editDisableAllowanceModal .hesa_code_area').fadeOut('fast', function(){
                $('#editDisableAllowanceModal .hesa_code_area input').val('');
            });
            $('#editDisableAllowanceModal input[name="is_df"]').prop('checked', false);
            $('#editDisableAllowanceModal .df_code_area').fadeOut('fast', function(){
                $('#editDisableAllowanceModal .df_code_area input').val('');
            })
            $('#editDisableAllowanceModal input[name="active"]').prop('checked', false);
        });
        
        $('#addDisableAllowanceForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addDisableAllowanceForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addDisableAllowanceForm .hesa_code_area input').val('');
                })
            }else{
                $('#addDisableAllowanceForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addDisableAllowanceForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addDisableAllowanceForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addDisableAllowanceForm .df_code_area').fadeIn('fast', function(){
                    $('#addDisableAllowanceForm .df_code_area input').val('');
                })
            }else{
                $('#addDisableAllowanceForm .df_code_area').fadeOut('fast', function(){
                    $('#addDisableAllowanceForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editDisableAllowanceForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editDisableAllowanceForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editDisableAllowanceForm .hesa_code_area input').val('');
                })
            }else{
                $('#editDisableAllowanceForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editDisableAllowanceForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editDisableAllowanceForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editDisableAllowanceForm .df_code_area').fadeIn('fast', function(){
                    $('#editDisableAllowanceForm .df_code_area input').val('');
                })
            }else{
                $('#editDisableAllowanceForm .df_code_area').fadeOut('fast', function(){
                    $('#editDisableAllowanceForm .df_code_area input').val('');
                })
            }
        })

        $('#addDisableAllowanceForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addDisableAllowanceForm');
        
            document.querySelector('#saveDisableAllowance').setAttribute('disabled', 'disabled');
            document.querySelector("#saveDisableAllowance svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('disable.allowance.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveDisableAllowance').removeAttribute('disabled');
                document.querySelector("#saveDisableAllowance svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addDisableAllowanceModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                DisableAllowanceListTable.init();
            }).catch(error => {
                document.querySelector('#saveDisableAllowance').removeAttribute('disabled');
                document.querySelector("#saveDisableAllowance svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addDisableAllowanceForm .${key}`).addClass('border-danger');
                            $(`#addDisableAllowanceForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#DisableAllowanceListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("disable.allowance.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editDisableAllowanceModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editDisableAllowanceModal input[name="is_hesa"]').prop('checked', true);
                            $('#editDisableAllowanceModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editDisableAllowanceModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editDisableAllowanceModal input[name="is_hesa"]').prop('checked', false);
                            $('#editDisableAllowanceModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editDisableAllowanceModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editDisableAllowanceModal input[name="is_df"]').prop('checked', true);
                            $('#editDisableAllowanceModal .df_code_area').fadeIn('fast', function(){
                                $('#editDisableAllowanceModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editDisableAllowanceModal input[name="is_df"]').prop('checked', false);
                            $('#editDisableAllowanceModal .df_code_area').fadeOut('fast', function(){
                                $('#editDisableAllowanceModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editDisableAllowanceModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editDisableAllowanceModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editDisableAllowanceModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editDisableAllowanceForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editDisableAllowanceForm input[name="id"]').val();
            const form = document.getElementById("editDisableAllowanceForm");

            document.querySelector('#updateDisableAllowance').setAttribute('disabled', 'disabled');
            document.querySelector('#updateDisableAllowance svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("disable.allowance.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateDisableAllowance").removeAttribute("disabled");
                    document.querySelector("#updateDisableAllowance svg").style.cssText = "display: none;";
                    editDisableAllowanceModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                DisableAllowanceListTable.init();
            }).catch((error) => {
                document.querySelector("#updateDisableAllowance").removeAttribute("disabled");
                document.querySelector("#updateDisableAllowance svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editDisableAllowanceForm .${key}`).addClass('border-danger')
                            $(`#editDisableAllowanceForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editDisableAllowanceModal.hide();

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
            if(action == 'DELETEDISABLEALLW'){
                axios({
                    method: 'delete',
                    url: route('disable.allowance.destory', recordID),
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
                    DisableAllowanceListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREDISABLEALLW'){
                axios({
                    method: 'post',
                    url: route('disable.allowance.restore', recordID),
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
                    DisableAllowanceListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATDISABLEALLW'){
                axios({
                    method: 'post',
                    url: route('disable.allowance.update.status', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record status successfully updated!');
                        });
                    }
                    DisableAllowanceListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#DisableAllowanceListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATDISABLEALLW');
            });
        });

        // Delete Course
        $('#DisableAllowanceListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEDISABLEALLW');
            });
        });

        // Restore Course
        $('#DisableAllowanceListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREDISABLEALLW');
            });
        });

        $('#disableAllowanceImportModal').on('click','#saveDisableAllowance',function(e) {
            e.preventDefault();
            $('#disableAllowanceImportModal .dropzone').get(0).dropzone.processQueue();
            disableAllowanceImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();