import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var sexoListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-SEXO").val() != "" ? $("#query-SEXO").val() : "";
        let status = $("#status-SEXO").val() != "" ? $("#status-SEXO").val() : "";
        let tableContent = new Tabulator("#sexoListTable", {
            ajaxURL: route("sex.orientation.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editSexoModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-SEXO").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SEXO").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SEXO").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Orientation Details",
            });
        });

        $("#tabulator-export-html-SEXO").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SEXO").on("click", function (event) {
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
    if ($("#sexoListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'sexoListTable'){
                sexoListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormSEXO() {
            sexoListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-SEXO")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormSEXO();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-SEXO").on("click", function (event) {
            filterHTMLFormSEXO();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SEXO").on("click", function (event) {
            $("#query-SEXO").val("");
            $("#status-SEXO").val("1");
            filterHTMLFormSEXO();
        });

        const addSexoModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSexoModal"));
        const editSexoModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSexoModal"));
        const sexorientationImportModal = tailwind.Modal.getOrCreateInstance("#sexorientationImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addSexoModalEl = document.getElementById('addSexoModal')
        addSexoModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addSexoModal .acc__input-error').html('');
            $('#addSexoModal .modal-body input:not([type="checkbox"])').val('');

            $('#addSexoModal input[name="is_hesa"]').prop('checked', false);
            $('#addSexoModal .hesa_code_area').fadeOut('fast', function(){
                $('#addSexoModal .hesa_code_area input').val('');
            });
            $('#addSexoModal input[name="is_df"]').prop('checked', false);
            $('#addSexoModal .df_code_area').fadeOut('fast', function(){
                $('#addSexoModal .df_code_area input').val('');
            })
        });
        
        const editSexoModalEl = document.getElementById('editSexoModal')
        editSexoModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editSexoModal .acc__input-error').html('');
            $('#editSexoModal .modal-body input:not([type="checkbox"])').val('');
            $('#editSexoModal input[name="id"]').val('0');

            $('#editSexoModal input[name="is_hesa"]').prop('checked', false);
            $('#editSexoModal .hesa_code_area').fadeOut('fast', function(){
                $('#editSexoModal .hesa_code_area input').val('');
            });
            $('#editSexoModal input[name="is_df"]').prop('checked', false);
            $('#editSexoModal .df_code_area').fadeOut('fast', function(){
                $('#editSexoModal .df_code_area input').val('');
            })
        });
        
        $('#addSexoForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSexoForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addSexoForm .hesa_code_area input').val('');
                })
            }else{
                $('#addSexoForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addSexoForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addSexoForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSexoForm .df_code_area').fadeIn('fast', function(){
                    $('#addSexoForm .df_code_area input').val('');
                })
            }else{
                $('#addSexoForm .df_code_area').fadeOut('fast', function(){
                    $('#addSexoForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editSexoForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSexoForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editSexoForm .hesa_code_area input').val('');
                })
            }else{
                $('#editSexoForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editSexoForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editSexoForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSexoForm .df_code_area').fadeIn('fast', function(){
                    $('#editSexoForm .df_code_area input').val('');
                })
            }else{
                $('#editSexoForm .df_code_area').fadeOut('fast', function(){
                    $('#editSexoForm .df_code_area input').val('');
                })
            }
        })

        $('#addSexoForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addSexoForm');
        
            document.querySelector('#saveSexo').setAttribute('disabled', 'disabled');
            document.querySelector("#saveSexo svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('sex.orientation.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveSexo').removeAttribute('disabled');
                document.querySelector("#saveSexo svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addSexoModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                sexoListTable.init();
            }).catch(error => {
                document.querySelector('#saveSexo').removeAttribute('disabled');
                document.querySelector("#saveSexo svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addSexoForm .${key}`).addClass('border-danger');
                            $(`#addSexoForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#sexoListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("sex.orientation.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editSexoModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editSexoModal input[name="is_hesa"]').prop('checked', true);
                            $('#editSexoModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editSexoModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editSexoModal input[name="is_hesa"]').prop('checked', false);
                            $('#editSexoModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editSexoModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editSexoModal input[name="is_df"]').prop('checked', true);
                            $('#editSexoModal .df_code_area').fadeIn('fast', function(){
                                $('#editSexoModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editSexoModal input[name="is_df"]').prop('checked', false);
                            $('#editSexoModal .df_code_area').fadeOut('fast', function(){
                                $('#editSexoModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editSexoModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editSexoModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editSexoModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editSexoForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editSexoForm input[name="id"]').val();
            const form = document.getElementById("editSexoForm");

            document.querySelector('#updateSexo').setAttribute('disabled', 'disabled');
            document.querySelector('#updateSexo svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("sex.orientation.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateSexo").removeAttribute("disabled");
                    document.querySelector("#updateSexo svg").style.cssText = "display: none;";
                    editSexoModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                sexoListTable.init();
            }).catch((error) => {
                document.querySelector("#updateSexo").removeAttribute("disabled");
                document.querySelector("#updateSexo svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editSexoForm .${key}`).addClass('border-danger')
                            $(`#editSexoForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editSexoModal.hide();

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
            if(action == 'DELETESEXO'){
                axios({
                    method: 'delete',
                    url: route('sex.orientation.destory', recordID),
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
                    sexoListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORESEXO'){
                axios({
                    method: 'post',
                    url: route('sex.orientation.restore', recordID),
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
                    sexoListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTATSEXO'){
                axios({
                    method: 'post',
                    url: route('sex.orientation.update.status', recordID),
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
                    sexoListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#sexoListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATSEXO');
            });
        });

        // Delete Course
        $('#sexoListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETESEXO');
            });
        });

        // Restore Course
        $('#sexoListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORESEXO');
            });
        });

        $('#sexorientationImportModal').on('click','#saveImportSexorientation',function(e) {
            e.preventDefault();
            $('#sexorientationImportModal .dropzone').get(0).dropzone.processQueue();
            sexorientationImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();