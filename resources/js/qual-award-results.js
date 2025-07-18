import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var QaualAwardResultListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-QLAWRES").val() != "" ? $("#query-QLAWRES").val() : "";
        let status = $("#status-QLAWRES").val() != "" ? $("#status-QLAWRES").val() : "";
        let tableContent = new Tabulator("#QaualAwardResultListTable", {
            ajaxURL: route("qual.award.result.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editQaualAwardResultModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-QLAWRES").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-QLAWRES").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-QLAWRES").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-QLAWRES").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-QLAWRES").on("click", function (event) {
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
    if ($("#QaualAwardResultListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'QaualAwardResultListTable'){
                QaualAwardResultListTable.init();
            }
        });
        

        // Filter function
        function filterTitleHTMLForm() {
            QaualAwardResultListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-QLAWRES")[0].addEventListener(
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
        $("#tabulator-html-filter-go-QLAWRES").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-QLAWRES").on("click", function (event) {
            $("#query-QLAWRES").val("");
            $("#status-QLAWRES").val("1");
            filterTitleHTMLForm();
        });

        const addQaualAwardResultModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQaualAwardResultModal"));
        const editQaualAwardResultModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQaualAwardResultModal"));
        const qaualAwardResultImportModal = tailwind.Modal.getOrCreateInstance("#qaualAwardResultImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addQaualAwardResultModalEl = document.getElementById('addQaualAwardResultModal')
        addQaualAwardResultModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addQaualAwardResultModal .acc__input-error').html('');
            $('#addQaualAwardResultModal .modal-body input:not([type="checkbox"])').val('');

            $('#addQaualAwardResultModal input[name="is_hesa"]').prop('checked', false);
            $('#addQaualAwardResultModal .hesa_code_area').fadeOut('fast', function(){
                $('#addQaualAwardResultModal .hesa_code_area input').val('');
            });
            $('#addQaualAwardResultModal input[name="is_df"]').prop('checked', false);
            $('#addQaualAwardResultModal .df_code_area').fadeOut('fast', function(){
                $('#addQaualAwardResultModal .df_code_area input').val('');
            });
            $('#addQaualAwardResultModal input[name="active"]').prop('checked', true);
        });
        
        const editQaualAwardResultModalEl = document.getElementById('editQaualAwardResultModal')
        editQaualAwardResultModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editQaualAwardResultModal .acc__input-error').html('');
            $('#editQaualAwardResultModal .modal-body input:not([type="checkbox"])').val('');
            $('#editQaualAwardResultModal input[name="id"]').val('0');

            $('#editQaualAwardResultModal input[name="is_hesa"]').prop('checked', false);
            $('#editQaualAwardResultModal .hesa_code_area').fadeOut('fast', function(){
                $('#editQaualAwardResultModal .hesa_code_area input').val('');
            });
            $('#editQaualAwardResultModal input[name="is_df"]').prop('checked', false);
            $('#editQaualAwardResultModal .df_code_area').fadeOut('fast', function(){
                $('#editQaualAwardResultModal .df_code_area input').val('');
            })
            $('#editQaualAwardResultModal input[name="active"]').prop('checked', false);
        });
        
        $('#addQaualAwardResultForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addQaualAwardResultForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addQaualAwardResultForm .hesa_code_area input').val('');
                })
            }else{
                $('#addQaualAwardResultForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addQaualAwardResultForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addQaualAwardResultForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addQaualAwardResultForm .df_code_area').fadeIn('fast', function(){
                    $('#addQaualAwardResultForm .df_code_area input').val('');
                })
            }else{
                $('#addQaualAwardResultForm .df_code_area').fadeOut('fast', function(){
                    $('#addQaualAwardResultForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editQaualAwardResultForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editQaualAwardResultForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editQaualAwardResultForm .hesa_code_area input').val('');
                })
            }else{
                $('#editQaualAwardResultForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editQaualAwardResultForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editQaualAwardResultForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editQaualAwardResultForm .df_code_area').fadeIn('fast', function(){
                    $('#editQaualAwardResultForm .df_code_area input').val('');
                })
            }else{
                $('#editQaualAwardResultForm .df_code_area').fadeOut('fast', function(){
                    $('#editQaualAwardResultForm .df_code_area input').val('');
                })
            }
        })

        $('#addQaualAwardResultForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addQaualAwardResultForm');
        
            document.querySelector('#saveQAR').setAttribute('disabled', 'disabled');
            document.querySelector("#saveQAR svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('qual.award.result.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveQAR').removeAttribute('disabled');
                document.querySelector("#saveQAR svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addQaualAwardResultModal.hide();

                    succModal.show();
                    QaualAwardResultListTable.init();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Award Result Item Successfully inserted.');
                    });     
                }
                
            }).catch(error => {
                QaualAwardResultListTable.init();
                document.querySelector('#saveQAR').removeAttribute('disabled');
                document.querySelector("#saveQAR svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addQaualAwardResultForm .${key}`).addClass('border-danger');
                            $(`#addQaualAwardResultForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#QaualAwardResultListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("qual.award.result.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editQaualAwardResultModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editQaualAwardResultModal input[name="is_hesa"]').prop('checked', true);
                            $('#editQaualAwardResultModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editQaualAwardResultModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editQaualAwardResultModal input[name="is_hesa"]').prop('checked', false);
                            $('#editQaualAwardResultModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editQaualAwardResultModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editQaualAwardResultModal input[name="is_df"]').prop('checked', true);
                            $('#editQaualAwardResultModal .df_code_area').fadeIn('fast', function(){
                                $('#editQaualAwardResultModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editQaualAwardResultModal input[name="is_df"]').prop('checked', false);
                            $('#editQaualAwardResultModal .df_code_area').fadeOut('fast', function(){
                                $('#editQaualAwardResultModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editQaualAwardResultModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editQaualAwardResultModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editQaualAwardResultModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editQaualAwardResultForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editQaualAwardResultForm input[name="id"]').val();
            const form = document.getElementById("editQaualAwardResultForm");

            document.querySelector('#updateQAR').setAttribute('disabled', 'disabled');
            document.querySelector('#updateQAR svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("qual.award.result.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateQAR").removeAttribute("disabled");
                    document.querySelector("#updateQAR svg").style.cssText = "display: none;";
                    editQaualAwardResultModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Award Result data successfully updated.');
                    });
                }
                QaualAwardResultListTable.init();
            }).catch((error) => {
                document.querySelector("#updateQAR").removeAttribute("disabled");
                document.querySelector("#updateQAR svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editQaualAwardResultForm .${key}`).addClass('border-danger')
                            $(`#editQaualAwardResultForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editQaualAwardResultModal.hide();

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
            if(action == 'DELETEQLAWRES'){
                axios({
                    method: 'delete',
                    url: route('qual.award.result.destory', recordID),
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
                    QaualAwardResultListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREQLAWRES'){
                axios({
                    method: 'post',
                    url: route('qual.award.result.restore', recordID),
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
                    QaualAwardResultListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTATQLAWRES'){
                axios({
                    method: 'post',
                    url: route('qual.award.result.update.status', recordID),
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
                    QaualAwardResultListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#QaualAwardResultListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATQLAWRES');
            });
        });

        // Delete Course
        $('#QaualAwardResultListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEQLAWRES');
            });
        });

        // Restore Course
        $('#QaualAwardResultListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREQLAWRES');
            });
        });

        $('#qaualAwardResultImportModal').on('click','#saveQAR',function(e) {
            e.preventDefault();
            $('#qaualAwardResultImportModal .dropzone').get(0).dropzone.processQueue();
            qaualAwardResultImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();