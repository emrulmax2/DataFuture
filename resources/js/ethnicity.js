import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { data } from "jquery";
 
("use strict");
var ethnicityListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-ETHNIC").val() != "" ? $("#query-ETHNIC").val() : "";
        let status = $("#status-ETHNIC").val() != "" ? $("#status-ETHNIC").val() : "";
        let tableContent = new Tabulator("#ethnicityListTable", {
            ajaxURL: route("ethnic.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEthnicityModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-ETHNIC").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-ETHNIC").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-ETHNIC").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Ethnicities Details",
            });
        });

        $("#tabulator-export-html-ETHNIC").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-ETHNIC").on("click", function (event) {
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
    if ($("#ethnicityListTable").length) {
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'ethnicityListTable'){
                ethnicityListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormEthnic() {
            ethnicityListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-ETHNIC")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormEthnic();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-ETHNIC").on("click", function (event) {
            filterHTMLFormEthnic();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ETHNIC").on("click", function (event) {
            $("#query-ETHNIC").val("");
            $("#status-ETHNIC").val("1");
            filterHTMLFormEthnic();
        });

        const addEthnicityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEthnicityModal"));
        const editEthnicityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEthnicityModal"));
        const ethnicityImportModal = tailwind.Modal.getOrCreateInstance("#ethnicityImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addEthnicityModalEl = document.getElementById('addEthnicityModal')
        addEthnicityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addEthnicityModal .acc__input-error').html('');
            $('#addEthnicityModal .modal-body input:not([type="checkbox"])').val('');

            $('#addEthnicityModal input[name="is_hesa"]').prop('checked', false);
            $('#addEthnicityModal .hesa_code_area').fadeOut('fast', function(){
                $('#addEthnicityModal .hesa_code_area input').val('');
            });
            $('#addEthnicityModal input[name="is_df"]').prop('checked', false);
            $('#addEthnicityModal .df_code_area').fadeOut('fast', function(){
                $('#addEthnicityModal .df_code_area input').val('');
            });
            
            $('#addEthnicityModal input[name="active"]').prop('checked', true);
        });
        
        const editEthnicityModalEl = document.getElementById('editEthnicityModal')
        editEthnicityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editEthnicityModal .acc__input-error').html('');
            $('#editEthnicityModal .modal-body input:not([type="checkbox"])').val('');
            $('#editEthnicityModal input[name="id"]').val('0');

            $('#editEthnicityModal input[name="is_hesa"]').prop('checked', false);
            $('#editEthnicityModal .hesa_code_area').fadeOut('fast', function(){
                $('#editEthnicityModal .hesa_code_area input').val('');
            });
            $('#editEthnicityModal input[name="is_df"]').prop('checked', false);
            $('#editEthnicityModal .df_code_area').fadeOut('fast', function(){
                $('#editEthnicityModal .df_code_area input').val('');
            })
            
            $('#editEthnicityModal input[name="active"]').prop('checked', false);
        });
        
        $('#addEthnicityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addEthnicityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addEthnicityForm .hesa_code_area input').val('');
                })
            }else{
                $('#addEthnicityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addEthnicityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addEthnicityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addEthnicityForm .df_code_area').fadeIn('fast', function(){
                    $('#addEthnicityForm .df_code_area input').val('');
                })
            }else{
                $('#addEthnicityForm .df_code_area').fadeOut('fast', function(){
                    $('#addEthnicityForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editEthnicityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editEthnicityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editEthnicityForm .hesa_code_area input').val('');
                })
            }else{
                $('#editEthnicityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editEthnicityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editEthnicityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editEthnicityForm .df_code_area').fadeIn('fast', function(){
                    $('#editEthnicityForm .df_code_area input').val('');
                })
            }else{
                $('#editEthnicityForm .df_code_area').fadeOut('fast', function(){
                    $('#editEthnicityForm .df_code_area input').val('');
                })
            }
        })

        $('#addEthnicityForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addEthnicityForm');
        
            document.querySelector('#saveEthnicity').setAttribute('disabled', 'disabled');
            document.querySelector("#saveEthnicity svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('ethnic.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveEthnicity').removeAttribute('disabled');
                document.querySelector("#saveEthnicity svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addEthnicityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                ethnicityListTable.init();
            }).catch(error => {
                document.querySelector('#saveEthnicity').removeAttribute('disabled');
                document.querySelector("#saveEthnicity svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addEthnicityForm .${key}`).addClass('border-danger');
                            $(`#addEthnicityForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#ethnicityListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("ethnic.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editEthnicityModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editEthnicityModal input[name="is_hesa"]').prop('checked', true);
                            $('#editEthnicityModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editEthnicityModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editEthnicityModal input[name="is_hesa"]').prop('checked', false);
                            $('#editEthnicityModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editEthnicityModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editEthnicityModal input[name="is_df"]').prop('checked', true);
                            $('#editEthnicityModal .df_code_area').fadeIn('fast', function(){
                                $('#editEthnicityModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editEthnicityModal input[name="is_df"]').prop('checked', false);
                            $('#editEthnicityModal .df_code_area').fadeOut('fast', function(){
                                $('#editEthnicityModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editEthnicityModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editEthnicityModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editEthnicityModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editEthnicityForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editEthnicityForm input[name="id"]').val();
            const form = document.getElementById("editEthnicityForm");

            document.querySelector('#updateEthnicity').setAttribute('disabled', 'disabled');
            document.querySelector('#updateEthnicity svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("ethnic.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateEthnicity").removeAttribute("disabled");
                    document.querySelector("#updateEthnicity svg").style.cssText = "display: none;";
                    editEthnicityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                ethnicityListTable.init();
            }).catch((error) => {
                document.querySelector("#updateEthnicity").removeAttribute("disabled");
                document.querySelector("#updateEthnicity svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editEthnicityForm .${key}`).addClass('border-danger')
                            $(`#editEthnicityForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editEthnicityModal.hide();

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
            if(action == 'DELETEETHNIC'){
                axios({
                    method: 'delete',
                    url: route('ethnic.destory', recordID),
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
                    ethnicityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREETHNIC'){
                axios({
                    method: 'post',
                    url: route('ethnic.restore', recordID),
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
                    ethnicityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATETHNIC'){
                axios({
                    method: 'post',
                    url: route('ethnic.update.status', recordID),
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
                    ethnicityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#ethnicityListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATETHNIC');
            });
        });

        // Delete Course
        $('#ethnicityListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEETHNIC');
            });
        });

        // Restore Course
        $('#ethnicityListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREETHNIC');
            });
        });

        $('#ethnicityImportModal').on('click','#saveEthnicities',function(e) {
            e.preventDefault();
            $('#ethnicityImportModal .dropzone').get(0).dropzone.processQueue();
            ethnicityImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();