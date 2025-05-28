import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var QaualGradeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-QUALGRADE").val() != "" ? $("#query-QUALGRADE").val() : "";
        let status = $("#status-QUALGRADE").val() != "" ? $("#status-QUALGRADE").val() : "";
        let tableContent = new Tabulator("#QaualGradeListTable", {
            ajaxURL: route("qualification.grade.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editQaualGradeModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-QUALGRADE").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-QUALGRADE").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-QUALGRADE").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Reason for Engagement Ending Details",
            });
        });

        $("#tabulator-export-html-QUALGRADE").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-QUALGRADE").on("click", function (event) {
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
    if ($("#QaualGradeListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'QaualGradeListTable'){
                QaualGradeListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormQUALGRADE() {
            QaualGradeListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-QUALGRADE")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormQUALGRADE();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-QUALGRADE").on("click", function (event) {
            filterHTMLFormQUALGRADE();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-QUALGRADE").on("click", function (event) {
            $("#query-QUALGRADE").val("");
            $("#status-QUALGRADE").val("1");
            filterHTMLFormQUALGRADE();
        });

        const addQaualGradeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQaualGradeModal"));
        const editQaualGradeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQaualGradeModal"));
        const qaualgradeidImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#qaualgradeidImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addQaualGradeModalEl = document.getElementById('addQaualGradeModal')
        addQaualGradeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addQaualGradeModal .acc__input-error').html('');
            $('#addQaualGradeModal .modal-body input:not([type="checkbox"])').val('');

            $('#addQaualGradeModal input[name="is_hesa"]').prop('checked', false);
            $('#addQaualGradeModal .hesa_code_area').fadeOut('fast', function(){
                $('#addQaualGradeModal .hesa_code_area input').val('');
            });
            $('#addQaualGradeModal input[name="is_df"]').prop('checked', false);
            $('#addQaualGradeModal .df_code_area').fadeOut('fast', function(){
                $('#addQaualGradeModal .df_code_area input').val('');
            })
            $('#addQaualGradeModal input[name="active"]').prop('checked', true);
        });
        
        const editQaualGradeModalEl = document.getElementById('editQaualGradeModal')
        editQaualGradeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editQaualGradeModal .acc__input-error').html('');
            $('#editQaualGradeModal .modal-body input:not([type="checkbox"])').val('');
            $('#editQaualGradeModal input[name="id"]').val('0');

            $('#editQaualGradeModal input[name="is_hesa"]').prop('checked', false);
            $('#editQaualGradeModal .hesa_code_area').fadeOut('fast', function(){
                $('#editQaualGradeModal .hesa_code_area input').val('');
            });
            $('#editQaualGradeModal input[name="is_df"]').prop('checked', false);
            $('#editQaualGradeModal .df_code_area').fadeOut('fast', function(){
                $('#editQaualGradeModal .df_code_area input').val('');
            })
            $('#editQaualGradeModal input[name="active"]').prop('checked', false);
        });
        
        $('#addQaualGradeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addQaualGradeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addQaualGradeForm .hesa_code_area input').val('');
                })
            }else{
                $('#addQaualGradeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addQaualGradeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addQaualGradeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addQaualGradeForm .df_code_area').fadeIn('fast', function(){
                    $('#addQaualGradeForm .df_code_area input').val('');
                })
            }else{
                $('#addQaualGradeForm .df_code_area').fadeOut('fast', function(){
                    $('#addQaualGradeForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editQaualGradeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editQaualGradeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editQaualGradeForm .hesa_code_area input').val('');
                })
            }else{
                $('#editQaualGradeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editQaualGradeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editQaualGradeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editQaualGradeForm .df_code_area').fadeIn('fast', function(){
                    $('#editQaualGradeForm .df_code_area input').val('');
                })
            }else{
                $('#editQaualGradeForm .df_code_area').fadeOut('fast', function(){
                    $('#editQaualGradeForm .df_code_area input').val('');
                })
            }
        })

        $('#addQaualGradeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addQaualGradeForm');
        
            document.querySelector('#saveQaualGrade').setAttribute('disabled', 'disabled');
            document.querySelector("#saveQaualGrade svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('qualification.grade.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveQaualGrade').removeAttribute('disabled');
                document.querySelector("#saveQaualGrade svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addQaualGradeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Qualification Grade Successfully inserted.');
                    });     
                }
                QaualGradeListTable.init();
            }).catch(error => {
                document.querySelector('#saveQaualGrade').removeAttribute('disabled');
                document.querySelector("#saveQaualGrade svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addQaualGradeForm .${key}`).addClass('border-danger');
                            $(`#addQaualGradeForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#QaualGradeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("qualification.grade.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editQaualGradeModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editQaualGradeModal input[name="is_hesa"]').prop('checked', true);
                            $('#editQaualGradeModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editQaualGradeModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editQaualGradeModal input[name="is_hesa"]').prop('checked', false);
                            $('#editQaualGradeModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editQaualGradeModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editQaualGradeModal input[name="is_df"]').prop('checked', true);
                            $('#editQaualGradeModal .df_code_area').fadeIn('fast', function(){
                                $('#editQaualGradeModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editQaualGradeModal input[name="is_df"]').prop('checked', false);
                            $('#editQaualGradeModal .df_code_area').fadeOut('fast', function(){
                                $('#editQaualGradeModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editQaualGradeModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editQaualGradeModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editQaualGradeModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editQaualGradeForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editQaualGradeForm input[name="id"]').val();
            const form = document.getElementById("editQaualGradeForm");

            document.querySelector('#updateQaualGrade').setAttribute('disabled', 'disabled');
            document.querySelector('#updateQaualGrade svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("qualification.grade.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateQaualGrade").removeAttribute("disabled");
                    document.querySelector("#updateQaualGrade svg").style.cssText = "display: none;";
                    editQaualGradeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Qualification Grade data successfully updated.');
                    });
                }
                QaualGradeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateQaualGrade").removeAttribute("disabled");
                document.querySelector("#updateQaualGrade svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editQaualGradeForm .${key}`).addClass('border-danger')
                            $(`#editQaualGradeForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editQaualGradeModal.hide();

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
            if(action == 'DELETEQUALGRADE'){
                axios({
                    method: 'delete',
                    url: route('qualification.grade.destory', recordID),
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
                    QaualGradeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREQUALGRADE'){
                axios({
                    method: 'post',
                    url: route('qualification.grade.restore', recordID),
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
                    QaualGradeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATQUALGRADE'){
                axios({
                    method: 'post',
                    url: route('qualification.grade.update.status', recordID),
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
                    QaualGradeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#QaualGradeListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATQUALGRADE');
            });
        });

        // Delete Course
        $('#QaualGradeListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEQUALGRADE');
            });
        });

        // Restore Course
        $('#QaualGradeListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREQUALGRADE');
            });
        });

        $('#qaualgradeidImportModal').on('click','#saveQaualGradeImport',function(e) {
            e.preventDefault();
            $('#qaualgradeidImportModal .dropzone').get(0).dropzone.processQueue();
            qaualgradeidImportModal.hide();

            succModal.show();   
            setTimeout(function() { 
                succModal.hide(); 
                QaualGradeListTable.init();
            }, 2000);        
        });
    }
})();