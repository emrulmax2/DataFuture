import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { data } from "jquery";
 
("use strict");
var apelcredListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-APEL").val() != "" ? $("#query-APEL").val() : "";
        let status = $("#status-APEL").val() != "" ? $("#status-APEL").val() : "";
        let tableContent = new Tabulator("#apelcredListTable", {
            ajaxURL: route("apelcred.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editApelcredModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-APEL").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-APEL").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-APEL").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Apel Credit Details",
            });
        });

        $("#tabulator-export-html-APEL").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-APEL").on("click", function (event) {
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
    if ($("#apelcredListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'apelcredListTable'){
                apelcredListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormAPEL() {
            apelcredListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-APEL")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormAPEL();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-APEL").on("click", function (event) {
            filterHTMLFormAPEL();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-APEL").on("click", function (event) {
            $("#query-APEL").val("");
            $("#status-APEL").val("1");
            filterHTMLFormAPEL();
        });

        const addApelcredModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addApelcredModal"));
        const editApelcredModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editApelcredModal"));
        const apelCreditImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#apelCreditImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addApelcredModalEl = document.getElementById('addApelcredModal')
        addApelcredModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addApelcredModal .acc__input-error').html('');
            $('#addApelcredModal .modal-body input:not([type="checkbox"])').val('');

            $('#addApelcredModal input[name="is_hesa"]').prop('checked', false);
            $('#addApelcredModal .hesa_code_area').fadeOut('fast', function(){
                $('#addApelcredModal .hesa_code_area input').val('');
            });
            $('#addApelcredModal input[name="is_df"]').prop('checked', false);
            $('#addApelcredModal .df_code_area').fadeOut('fast', function(){
                $('#addApelcredModal .df_code_area input').val('');
            })
            $('#addApelcredModal input[name="active"]').prop('checked', true);
        });
        
        const editApelcredModalEl = document.getElementById('editApelcredModal')
        editApelcredModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editApelcredModal .acc__input-error').html('');
            $('#editApelcredModal .modal-body input:not([type="checkbox"])').val('');
            $('#editApelcredModal input[name="id"]').val('0');

            $('#editApelcredModal input[name="is_hesa"]').prop('checked', false);
            $('#editApelcredModal .hesa_code_area').fadeOut('fast', function(){
                $('#editApelcredModal .hesa_code_area input').val('');
            });
            $('#editApelcredModal input[name="is_df"]').prop('checked', false);
            $('#editApelcredModal .df_code_area').fadeOut('fast', function(){
                $('#editApelcredModal .df_code_area input').val('');
            })
            $('#editApelcredModal input[name="active"]').prop('checked', false);
        });
        
        $('#addApelcredForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addApelcredForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addApelcredForm .hesa_code_area input').val('');
                })
            }else{
                $('#addApelcredForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addApelcredForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addApelcredForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addApelcredForm .df_code_area').fadeIn('fast', function(){
                    $('#addApelcredForm .df_code_area input').val('');
                })
            }else{
                $('#addApelcredForm .df_code_area').fadeOut('fast', function(){
                    $('#addApelcredForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editApelcredForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editApelcredForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editApelcredForm .hesa_code_area input').val('');
                })
            }else{
                $('#editApelcredForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editApelcredForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editApelcredForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editApelcredForm .df_code_area').fadeIn('fast', function(){
                    $('#editApelcredForm .df_code_area input').val('');
                })
            }else{
                $('#editApelcredForm .df_code_area').fadeOut('fast', function(){
                    $('#editApelcredForm .df_code_area input').val('');
                })
            }
        })

        $('#addApelcredForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addApelcredForm');
        
            document.querySelector('#saveApelcred').setAttribute('disabled', 'disabled');
            document.querySelector("#saveApelcred svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('apelcred.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveApelcred').removeAttribute('disabled');
                document.querySelector("#saveApelcred svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addApelcredModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                apelcredListTable.init();
            }).catch(error => {
                document.querySelector('#saveApelcred').removeAttribute('disabled');
                document.querySelector("#saveApelcred svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addApelcredForm .${key}`).addClass('border-danger');
                            $(`#addApelcredForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#apelcredListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("apelcred.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editApelcredModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editApelcredModal input[name="is_hesa"]').prop('checked', true);
                            $('#editApelcredModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editApelcredModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editApelcredModal input[name="is_hesa"]').prop('checked', false);
                            $('#editApelcredModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editApelcredModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editApelcredModal input[name="is_df"]').prop('checked', true);
                            $('#editApelcredModal .df_code_area').fadeIn('fast', function(){
                                $('#editApelcredModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editApelcredModal input[name="is_df"]').prop('checked', false);
                            $('#editApelcredModal .df_code_area').fadeOut('fast', function(){
                                $('#editApelcredModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editApelcredModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editApelcredModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editApelcredModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editApelcredForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editApelcredForm input[name="id"]').val();
            const form = document.getElementById("editApelcredForm");

            document.querySelector('#updateApelcred').setAttribute('disabled', 'disabled');
            document.querySelector('#updateApelcred svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("apelcred.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateApelcred").removeAttribute("disabled");
                    document.querySelector("#updateApelcred svg").style.cssText = "display: none;";
                    editApelcredModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                apelcredListTable.init();
            }).catch((error) => {
                document.querySelector("#updateApelcred").removeAttribute("disabled");
                document.querySelector("#updateApelcred svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editApelcredForm .${key}`).addClass('border-danger')
                            $(`#editApelcredForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editApelcredModal.hide();

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
            if(action == 'DELETEAPEL'){
                axios({
                    method: 'delete',
                    url: route('apelcred.destory', recordID),
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
                    apelcredListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREAPEL'){
                axios({
                    method: 'post',
                    url: route('apelcred.restore', recordID),
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
                    apelcredListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATAPEL'){
                axios({
                    method: 'post',
                    url: route('apelcred.update.status', recordID),
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
                    apelcredListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#apelcredListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATAPEL');
            });
        });

        // Delete Course
        $('#apelcredListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEAPEL');
            });
        });

        // Restore Course
        $('#apelcredListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREAPEL');
            });
        });

        $('#apelCreditImportModal').on('click','#saveApelCredit',function(e) {
            e.preventDefault();
            $('#apelCreditImportModal .dropzone').get(0).dropzone.processQueue();
            apelCreditImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();