import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var careLeaverListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-CARELEAVER").val() != "" ? $("#query-CARELEAVER").val() : "";
        let status = $("#status-CARELEAVER").val() != "" ? $("#status-CARELEAVER").val() : "";
        let tableContent = new Tabulator("#careLeaverListTable", {
            ajaxURL: route("care.leaver.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCareLeaverModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-CARELEAVER").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CARELEAVER").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CARELEAVER").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Disabilities Details",
            });
        });

        $("#tabulator-export-html-CARELEAVER").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CARELEAVER").on("click", function (event) {
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
    if ($("#careLeaverListTable").length) {
        // Init Table
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'careLeaverListTable'){
                careLeaverListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormDisability() {
            careLeaverListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-CARELEAVER")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormDisability();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-CARELEAVER").on("click", function (event) {
            filterHTMLFormDisability();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CARELEAVER").on("click", function (event) {
            $("#query-CARELEAVER").val("");
            $("#status-CARELEAVER").val("1");
            filterHTMLFormDisability();
        });

        const addCareLeaverModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCareLeaverModal"));
        const editCareLeaverModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCareLeaverModal"));
        const careLeaverImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#careLeaverImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addCareLeaverModalEl = document.getElementById('addCareLeaverModal')
        addCareLeaverModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addCareLeaverModal .acc__input-error').html('');
            $('#addCareLeaverModal .modal-body input:not([type="checkbox"])').val('');

            $('#addCareLeaverModal input[name="is_hesa"]').prop('checked', false);
            $('#addCareLeaverModal .hesa_code_area').fadeOut('fast', function(){
                $('#addCareLeaverModal .hesa_code_area input').val('');
            });
            $('#addCareLeaverModal input[name="is_df"]').prop('checked', false);
            $('#addCareLeaverModal .df_code_area').fadeOut('fast', function(){
                $('#addCareLeaverModal .df_code_area input').val('');
            })
            $('#addCareLeaverModal input[name="active"]').prop('checked', true);
        });
        
        const editCareLeaverModalEl = document.getElementById('editCareLeaverModal')
        editCareLeaverModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editCareLeaverModal .acc__input-error').html('');
            $('#editCareLeaverModal .modal-body input:not([type="checkbox"])').val('');
            $('#editCareLeaverModal input[name="id"]').val('0');

            $('#editCareLeaverModal input[name="is_hesa"]').prop('checked', false);
            $('#editCareLeaverModal .hesa_code_area').fadeOut('fast', function(){
                $('#editCareLeaverModal .hesa_code_area input').val('');
            });
            $('#editCareLeaverModal input[name="is_df"]').prop('checked', false);
            $('#editCareLeaverModal .df_code_area').fadeOut('fast', function(){
                $('#editCareLeaverModal .df_code_area input').val('');
            })
            $('#editCareLeaverModal input[name="active"]').prop('checked', false);
        });
        
        $('#addCareLeaverForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addCareLeaverForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addCareLeaverForm .hesa_code_area input').val('');
                })
            }else{
                $('#addCareLeaverForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addCareLeaverForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addCareLeaverForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addCareLeaverForm .df_code_area').fadeIn('fast', function(){
                    $('#addCareLeaverForm .df_code_area input').val('');
                })
            }else{
                $('#addCareLeaverForm .df_code_area').fadeOut('fast', function(){
                    $('#addCareLeaverForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editCareLeaverForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editCareLeaverForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editCareLeaverForm .hesa_code_area input').val('');
                })
            }else{
                $('#editCareLeaverForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editCareLeaverForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editCareLeaverForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editCareLeaverForm .df_code_area').fadeIn('fast', function(){
                    $('#editCareLeaverForm .df_code_area input').val('');
                })
            }else{
                $('#editCareLeaverForm .df_code_area').fadeOut('fast', function(){
                    $('#editCareLeaverForm .df_code_area input').val('');
                })
            }
        })

        $('#addCareLeaverForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addCareLeaverForm');
        
            document.querySelector('#saveCareLeaver').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCareLeaver svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('care.leaver.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCareLeaver').removeAttribute('disabled');
                document.querySelector("#saveCareLeaver svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addCareLeaverModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                careLeaverListTable.init();
            }).catch(error => {
                document.querySelector('#saveCareLeaver').removeAttribute('disabled');
                document.querySelector("#saveCareLeaver svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addCareLeaverForm .${key}`).addClass('border-danger');
                            $(`#addCareLeaverForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#careLeaverListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("care.leaver.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editCareLeaverModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editCareLeaverModal input[name="is_hesa"]').prop('checked', true);
                            $('#editCareLeaverModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editCareLeaverModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editCareLeaverModal input[name="is_hesa"]').prop('checked', false);
                            $('#editCareLeaverModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editCareLeaverModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editCareLeaverModal input[name="is_df"]').prop('checked', true);
                            $('#editCareLeaverModal .df_code_area').fadeIn('fast', function(){
                                $('#editCareLeaverModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editCareLeaverModal input[name="is_df"]').prop('checked', false);
                            $('#editCareLeaverModal .df_code_area').fadeOut('fast', function(){
                                $('#editCareLeaverModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editCareLeaverModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editCareLeaverModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editCareLeaverModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editCareLeaverForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editCareLeaverForm input[name="id"]').val();
            const form = document.getElementById("editCareLeaverForm");

            document.querySelector('#updateCareLeaver').setAttribute('disabled', 'disabled');
            document.querySelector('#updateCareLeaver svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("care.leaver.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateCareLeaver").removeAttribute("disabled");
                    document.querySelector("#updateCareLeaver svg").style.cssText = "display: none;";
                    editCareLeaverModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                careLeaverListTable.init();
            }).catch((error) => {
                document.querySelector("#updateCareLeaver").removeAttribute("disabled");
                document.querySelector("#updateCareLeaver svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editCareLeaverForm .${key}`).addClass('border-danger')
                            $(`#editCareLeaverForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editCareLeaverModal.hide();

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
            if(action == 'DELETEDISABILITY'){
                axios({
                    method: 'delete',
                    url: route('care.leaver.destory', recordID),
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
                    careLeaverListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREDISABILITY'){
                axios({
                    method: 'post',
                    url: route('care.leaver.restore', recordID),
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
                    careLeaverListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATDISABILITY'){
                axios({
                    method: 'post',
                    url: route('care.leaver.update.status', recordID),
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
                    careLeaverListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
        // Status Updater
        $('#careLeaverListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATDISABILITY');
            });
        });

        // Delete Course
        $('#careLeaverListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEDISABILITY');
            });
        });

        // Restore Course
        $('#careLeaverListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREDISABILITY');
            });
        });

        $('#careLeaverImportModal').on('click','#saveDisabilities',function(e) {
            e.preventDefault();
            $('#careLeaverImportModal .dropzone').get(0).dropzone.processQueue();
            careLeaverImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();