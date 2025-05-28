import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { data } from "jquery";
 
("use strict");
var termtimeaccommodationtypeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-TTACCOM").val() != "" ? $("#query-TTACCOM").val() : "";
        let status = $("#status-TTACCOM").val() != "" ? $("#status-TTACCOM").val() : "";
        let tableContent = new Tabulator("#termtimeaccommodationtypeListTable", {
            ajaxURL: route("termtimeaccommodationtype.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editTTACCOMModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                const columnLists = this.getColumns();
                if (columnLists.length > 0) {
                    const lastColumn = columnLists[columnLists.length - 1];
                    const currentWidth = lastColumn.getWidth();
                    lastColumn.setWidth(currentWidth - 1);
                } 
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
        $("#tabulator-export-csv-TTACCOM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-TTACCOM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-TTACCOM").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Term Time Accommodation Type Details",
            });
        });

        $("#tabulator-export-html-TTACCOM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-TTACCOM").on("click", function (event) {
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
    if ($("#termtimeaccommodationtypeListTable").length) {
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'termtimeaccommodationtypeListTable'){
                termtimeaccommodationtypeListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormTTACCOM() {
            termtimeaccommodationtypeListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-TTACCOM")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormTTACCOM();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-TTACCOM").on("click", function (event) {
            filterHTMLFormTTACCOM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-TTACCOM").on("click", function (event) {
            $("#query-TTACCOM").val("");
            $("#status-TTACCOM").val("1");
            filterHTMLFormTTACCOM();
        });

        const addTTACCOMModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTTACCOMModal"));
        const editTTACCOMModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTTACCOMModal"));
        const accommodationImportModal = tailwind.Modal.getOrCreateInstance("#accommodationImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addTTACCOMModalEl = document.getElementById('addTTACCOMModal')
        addTTACCOMModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addTTACCOMModal .acc__input-error').html('');
            $('#addTTACCOMModal .modal-body input:not([type="checkbox"])').val('');

            $('#addTTACCOMModal input[name="is_hesa"]').prop('checked', false);
            $('#addTTACCOMModal .hesa_code_area').fadeOut('fast', function(){
                $('#addTTACCOMModal .hesa_code_area input').val('');
            });
            $('#addTTACCOMModal input[name="is_df"]').prop('checked', false);
            $('#addTTACCOMModal .df_code_area').fadeOut('fast', function(){
                $('#addTTACCOMModal .df_code_area input').val('');
            });
            
            $('#addTTACCOMModal input[name="active"]').prop('checked', true);
        });
        
        const editTTACCOMModalEl = document.getElementById('editTTACCOMModal')
        editTTACCOMModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editTTACCOMModal .acc__input-error').html('');
            $('#editTTACCOMModal .modal-body input:not([type="checkbox"])').val('');
            $('#editTTACCOMModal input[name="id"]').val('0');

            $('#editTTACCOMModal input[name="is_hesa"]').prop('checked', false);
            $('#editTTACCOMModal .hesa_code_area').fadeOut('fast', function(){
                $('#editTTACCOMModal .hesa_code_area input').val('');
            });
            $('#editTTACCOMModal input[name="is_df"]').prop('checked', false);
            $('#editTTACCOMModal .df_code_area').fadeOut('fast', function(){
                $('#editTTACCOMModal .df_code_area input').val('');
            })
            
            $('#editTTACCOMModal input[name="active"]').prop('checked', false);
        });
        
        $('#addTTACCOMForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addTTACCOMForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addTTACCOMForm .hesa_code_area input').val('');
                })
            }else{
                $('#addTTACCOMForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addTTACCOMForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addTTACCOMForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addTTACCOMForm .df_code_area').fadeIn('fast', function(){
                    $('#addTTACCOMForm .df_code_area input').val('');
                })
            }else{
                $('#addTTACCOMForm .df_code_area').fadeOut('fast', function(){
                    $('#addTTACCOMForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editTTACCOMForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editTTACCOMForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editTTACCOMForm .hesa_code_area input').val('');
                })
            }else{
                $('#editTTACCOMForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editTTACCOMForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editTTACCOMForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editTTACCOMForm .df_code_area').fadeIn('fast', function(){
                    $('#editTTACCOMForm .df_code_area input').val('');
                })
            }else{
                $('#editTTACCOMForm .df_code_area').fadeOut('fast', function(){
                    $('#editTTACCOMForm .df_code_area input').val('');
                })
            }
        })

        $('#addTTACCOMForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addTTACCOMForm');
        
            document.querySelector('#saveTTACCOM').setAttribute('disabled', 'disabled');
            document.querySelector("#saveTTACCOM svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('termtimeaccommodationtype.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveTTACCOM').removeAttribute('disabled');
                document.querySelector("#saveTTACCOM svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addTTACCOMModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Item Successfully inserted.');
                    });     
                }
                termtimeaccommodationtypeListTable.init();
            }).catch(error => {
                document.querySelector('#saveTTACCOM').removeAttribute('disabled');
                document.querySelector("#saveTTACCOM svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addTTACCOMForm .${key}`).addClass('border-danger');
                            $(`#addTTACCOMForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#termtimeaccommodationtypeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("termtimeaccommodationtype.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editTTACCOMModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editTTACCOMModal input[name="is_hesa"]').prop('checked', true);
                            $('#editTTACCOMModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editTTACCOMModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editTTACCOMModal input[name="is_hesa"]').prop('checked', false);
                            $('#editTTACCOMModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editTTACCOMModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editTTACCOMModal input[name="is_df"]').prop('checked', true);
                            $('#editTTACCOMModal .df_code_area').fadeIn('fast', function(){
                                $('#editTTACCOMModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editTTACCOMModal input[name="is_df"]').prop('checked', false);
                            $('#editTTACCOMModal .df_code_area').fadeOut('fast', function(){
                                $('#editTTACCOMModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editTTACCOMModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editTTACCOMModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editTTACCOMModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editTTACCOMForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editTTACCOMForm input[name="id"]').val();
            const form = document.getElementById("editTTACCOMForm");

            document.querySelector('#updateTTACCOM').setAttribute('disabled', 'disabled');
            document.querySelector('#updateTTACCOM svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("termtimeaccommodationtype.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateTTACCOM").removeAttribute("disabled");
                    document.querySelector("#updateTTACCOM svg").style.cssText = "display: none;";
                    editTTACCOMModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Data successfully updated.');
                    });
                }
                termtimeaccommodationtypeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateTTACCOM").removeAttribute("disabled");
                document.querySelector("#updateTTACCOM svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editTTACCOMForm .${key}`).addClass('border-danger')
                            $(`#editTTACCOMForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editTTACCOMModal.hide();

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
            if(action == 'DELETETTACCOM'){
                axios({
                    method: 'delete',
                    url: route('termtimeaccommodationtype.destory', recordID),
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
                    termtimeaccommodationtypeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORETTACCOM'){
                axios({
                    method: 'post',
                    url: route('termtimeaccommodationtype.restore', recordID),
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
                    termtimeaccommodationtypeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATTTACCOM'){
                axios({
                    method: 'post',
                    url: route('termtimeaccommodationtype.update.status', recordID),
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
                    termtimeaccommodationtypeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#termtimeaccommodationtypeListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATTTACCOM');
            });
        });

        // Delete Course
        $('#termtimeaccommodationtypeListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETETTACCOM');
            });
        });

        // Restore Course
        $('#termtimeaccommodationtypeListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORETTACCOM');
            });
        });

        $('#accommodationImportModal').on('click','#saveTermtimeAccommodation',function(e) {
            e.preventDefault();
            $('#accommodationImportModal .dropzone').get(0).dropzone.processQueue();
            accommodationImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);       
        });
    }
})();