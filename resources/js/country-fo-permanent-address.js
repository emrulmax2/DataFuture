import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var PermaddcountryListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-PERMADDCOUNTRY").val() != "" ? $("#query-PERMADDCOUNTRY").val() : "";
        let status = $("#status-PERMADDCOUNTRY").val() != "" ? $("#status-PERMADDCOUNTRY").val() : "";
        let tableContent = new Tabulator("#PermaddcountryListTable", {
            ajaxURL: route("permaddcountry.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editPermaddcountryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-PERMADDCOUNTRY").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-PERMADDCOUNTRY").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-PERMADDCOUNTRY").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Country to Permanent Address Details",
            });
        });

        $("#tabulator-export-html-PERMADDCOUNTRY").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-PERMADDCOUNTRY").on("click", function (event) {
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
    if ($("#PermaddcountryListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'PermaddcountryListTable'){
                PermaddcountryListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormPERMADDCOUNTRY() {
            PermaddcountryListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PERMADDCOUNTRY")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormPERMADDCOUNTRY();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-PERMADDCOUNTRY").on("click", function (event) {
            filterHTMLFormPERMADDCOUNTRY();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PERMADDCOUNTRY").on("click", function (event) {
            $("#query-PERMADDCOUNTRY").val("");
            $("#status-PERMADDCOUNTRY").val("1");
            filterHTMLFormPERMADDCOUNTRY();
        });

        const addPermaddcountryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPermaddcountryModal"));
        const editPermaddcountryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPermaddcountryModal"));
        const permaddcountryImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#permaddcountryImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addPermaddcountryModalEl = document.getElementById('addPermaddcountryModal')
        addPermaddcountryModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addPermaddcountryModal .acc__input-error').html('');
            $('#addPermaddcountryModal .modal-body input:not([type="checkbox"])').val('');

            $('#addPermaddcountryModal input[name="is_hesa"]').prop('checked', false);
            $('#addPermaddcountryModal .hesa_code_area').fadeOut('fast', function(){
                $('#addPermaddcountryModal .hesa_code_area input').val('');
            });
            $('#addPermaddcountryModal input[name="is_df"]').prop('checked', false);
            $('#addPermaddcountryModal .df_code_area').fadeOut('fast', function(){
                $('#addPermaddcountryModal .df_code_area input').val('');
            })
            $('#addPermaddcountryModal input[name="active"]').prop('checked', true);
        });
        
        const editPermaddcountryModalEl = document.getElementById('editPermaddcountryModal')
        editPermaddcountryModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editPermaddcountryModal .acc__input-error').html('');
            $('#editPermaddcountryModal .modal-body input:not([type="checkbox"])').val('');
            $('#editPermaddcountryModal input[name="id"]').val('0');

            $('#editPermaddcountryModal input[name="is_hesa"]').prop('checked', false);
            $('#editPermaddcountryModal .hesa_code_area').fadeOut('fast', function(){
                $('#editPermaddcountryModal .hesa_code_area input').val('');
            });
            $('#editPermaddcountryModal input[name="is_df"]').prop('checked', false);
            $('#editPermaddcountryModal .df_code_area').fadeOut('fast', function(){
                $('#editPermaddcountryModal .df_code_area input').val('');
            })
            $('#editPermaddcountryModal input[name="active"]').prop('checked', false);
        });
        
        $('#addPermaddcountryForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addPermaddcountryForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addPermaddcountryForm .hesa_code_area input').val('');
                })
            }else{
                $('#addPermaddcountryForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addPermaddcountryForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addPermaddcountryForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addPermaddcountryForm .df_code_area').fadeIn('fast', function(){
                    $('#addPermaddcountryForm .df_code_area input').val('');
                })
            }else{
                $('#addPermaddcountryForm .df_code_area').fadeOut('fast', function(){
                    $('#addPermaddcountryForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editPermaddcountryForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editPermaddcountryForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editPermaddcountryForm .hesa_code_area input').val('');
                })
            }else{
                $('#editPermaddcountryForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editPermaddcountryForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editPermaddcountryForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editPermaddcountryForm .df_code_area').fadeIn('fast', function(){
                    $('#editPermaddcountryForm .df_code_area input').val('');
                })
            }else{
                $('#editPermaddcountryForm .df_code_area').fadeOut('fast', function(){
                    $('#editPermaddcountryForm .df_code_area input').val('');
                })
            }
        })

        $('#addPermaddcountryForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addPermaddcountryForm');
        
            document.querySelector('#savePermaddcountry').setAttribute('disabled', 'disabled');
            document.querySelector("#savePermaddcountry svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('permaddcountry.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#savePermaddcountry').removeAttribute('disabled');
                document.querySelector("#savePermaddcountry svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addPermaddcountryModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                PermaddcountryListTable.init();
            }).catch(error => {
                document.querySelector('#savePermaddcountry').removeAttribute('disabled');
                document.querySelector("#savePermaddcountry svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addPermaddcountryForm .${key}`).addClass('border-danger');
                            $(`#addPermaddcountryForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#PermaddcountryListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("permaddcountry.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editPermaddcountryModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editPermaddcountryModal input[name="is_hesa"]').prop('checked', true);
                            $('#editPermaddcountryModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editPermaddcountryModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editPermaddcountryModal input[name="is_hesa"]').prop('checked', false);
                            $('#editPermaddcountryModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editPermaddcountryModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editPermaddcountryModal input[name="is_df"]').prop('checked', true);
                            $('#editPermaddcountryModal .df_code_area').fadeIn('fast', function(){
                                $('#editPermaddcountryModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editPermaddcountryModal input[name="is_df"]').prop('checked', false);
                            $('#editPermaddcountryModal .df_code_area').fadeOut('fast', function(){
                                $('#editPermaddcountryModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editPermaddcountryModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editPermaddcountryModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editPermaddcountryModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editPermaddcountryForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editPermaddcountryForm input[name="id"]').val();
            const form = document.getElementById("editPermaddcountryForm");

            document.querySelector('#updatePermaddcountry').setAttribute('disabled', 'disabled');
            document.querySelector('#updatePermaddcountry svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("permaddcountry.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updatePermaddcountry").removeAttribute("disabled");
                    document.querySelector("#updatePermaddcountry svg").style.cssText = "display: none;";
                    editPermaddcountryModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                PermaddcountryListTable.init();
            }).catch((error) => {
                document.querySelector("#updatePermaddcountry").removeAttribute("disabled");
                document.querySelector("#updatePermaddcountry svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editPermaddcountryForm .${key}`).addClass('border-danger')
                            $(`#editPermaddcountryForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editPermaddcountryModal.hide();

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
            if(action == 'DELETEPERMADDCOUNTRY'){
                axios({
                    method: 'delete',
                    url: route('permaddcountry.destory', recordID),
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
                    PermaddcountryListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREPERMADDCOUNTRY'){
                axios({
                    method: 'post',
                    url: route('permaddcountry.restore', recordID),
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
                    PermaddcountryListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATPERMADDCOUNTRY'){
                axios({
                    method: 'post',
                    url: route('permaddcountry.update.status', recordID),
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
                    PermaddcountryListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#PermaddcountryListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATPERMADDCOUNTRY');
            });
        });

        // Delete Course
        $('#PermaddcountryListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEPERMADDCOUNTRY');
            });
        });

        // Restore Course
        $('#PermaddcountryListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREPERMADDCOUNTRY');
            });
        });

        $('#permaddcountryImportModal').on('click','#savePermaddcountry',function(e) {
            e.preventDefault();
            $('#permaddcountryImportModal .dropzone').get(0).dropzone.processQueue();
            permaddcountryImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 3000);          
        });
    }
})();