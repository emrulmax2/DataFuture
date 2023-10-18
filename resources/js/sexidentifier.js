import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { data } from "jquery";
 
("use strict");
var studentidentifierListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-SID").val() != "" ? $("#query-SID").val() : "";
        let status = $("#status-SID").val() != "" ? $("#status-SID").val() : "";
        let tableContent = new Tabulator("#studentidentifierListTable", {
            ajaxURL: route("sexidentifier.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editStudentidentifierModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-SID").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SID").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SID").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "SID Details",
            });
        });

        $("#tabulator-export-html-SID").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SID").on("click", function (event) {
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
    if ($("#studentidentifierListTable").length) {
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'studentidentifierListTable'){
                studentidentifierListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormSID() {
            studentidentifierListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-SID")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormSID();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-SID").on("click", function (event) {
            filterHTMLFormSID();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SID").on("click", function (event) {
            $("#query-SID").val("");
            $("#status-SID").val("1");
            filterHTMLFormSID();
        });

        const addStudentidentifierModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addStudentidentifierModal"));
        const editStudentidentifierModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentidentifierModal"));
        const studentidentifierImportModal = tailwind.Modal.getOrCreateInstance("#studentidentifierImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addStudentidentifierModalEl = document.getElementById('addStudentidentifierModal')
        addStudentidentifierModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addStudentidentifierModal .acc__input-error').html('');
            $('#addStudentidentifierModal .modal-body input:not([type="checkbox"])').val('');

            $('#addStudentidentifierModal input[name="is_hesa"]').prop('checked', false);
            $('#addStudentidentifierModal .hesa_code_area').fadeOut('fast', function(){
                $('#addStudentidentifierModal .hesa_code_area input').val('');
            });
            $('#addStudentidentifierModal input[name="is_df"]').prop('checked', false);
            $('#addStudentidentifierModal .df_code_area').fadeOut('fast', function(){
                $('#addStudentidentifierModal .df_code_area input').val('');
            });
            
            $('#addStudentidentifierModal input[name="active"]').prop('checked', true);
        });
        
        const editStudentidentifierModalEl = document.getElementById('editStudentidentifierModal')
        editStudentidentifierModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editStudentidentifierModal .acc__input-error').html('');
            $('#editStudentidentifierModal .modal-body input:not([type="checkbox"])').val('');
            $('#editStudentidentifierModal input[name="id"]').val('0');

            $('#editStudentidentifierModal input[name="is_hesa"]').prop('checked', false);
            $('#editStudentidentifierModal .hesa_code_area').fadeOut('fast', function(){
                $('#editStudentidentifierModal .hesa_code_area input').val('');
            });
            $('#editStudentidentifierModal input[name="is_df"]').prop('checked', false);
            $('#editStudentidentifierModal .df_code_area').fadeOut('fast', function(){
                $('#editStudentidentifierModal .df_code_area input').val('');
            })
            
            $('#editStudentidentifierModal input[name="active"]').prop('checked', false);
        });
        
        $('#addStudentidentifierForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addStudentidentifierForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addStudentidentifierForm .hesa_code_area input').val('');
                })
            }else{
                $('#addStudentidentifierForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addStudentidentifierForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addStudentidentifierForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addStudentidentifierForm .df_code_area').fadeIn('fast', function(){
                    $('#addStudentidentifierForm .df_code_area input').val('');
                })
            }else{
                $('#addStudentidentifierForm .df_code_area').fadeOut('fast', function(){
                    $('#addStudentidentifierForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editStudentidentifierForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editStudentidentifierForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editStudentidentifierForm .hesa_code_area input').val('');
                })
            }else{
                $('#editStudentidentifierForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editStudentidentifierForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editStudentidentifierForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editStudentidentifierForm .df_code_area').fadeIn('fast', function(){
                    $('#editStudentidentifierForm .df_code_area input').val('');
                })
            }else{
                $('#editStudentidentifierForm .df_code_area').fadeOut('fast', function(){
                    $('#editStudentidentifierForm .df_code_area input').val('');
                })
            }
        })

        $('#addStudentidentifierForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addStudentidentifierForm');
        
            document.querySelector('#saveStudentidentifier').setAttribute('disabled', 'disabled');
            document.querySelector("#saveStudentidentifier svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('sexidentifier.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveStudentidentifier').removeAttribute('disabled');
                document.querySelector("#saveStudentidentifier svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addStudentidentifierModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Item Successfully inserted.');
                    });     
                }
                studentidentifierListTable.init();
            }).catch(error => {
                document.querySelector('#saveStudentidentifier').removeAttribute('disabled');
                document.querySelector("#saveStudentidentifier svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addStudentidentifierForm .${key}`).addClass('border-danger');
                            $(`#addStudentidentifierForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#studentidentifierListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("sexidentifier.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editStudentidentifierModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editStudentidentifierModal input[name="is_hesa"]').prop('checked', true);
                            $('#editStudentidentifierModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editStudentidentifierModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editStudentidentifierModal input[name="is_hesa"]').prop('checked', false);
                            $('#editStudentidentifierModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editStudentidentifierModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editStudentidentifierModal input[name="is_df"]').prop('checked', true);
                            $('#editStudentidentifierModal .df_code_area').fadeIn('fast', function(){
                                $('#editStudentidentifierModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editStudentidentifierModal input[name="is_df"]').prop('checked', false);
                            $('#editStudentidentifierModal .df_code_area').fadeOut('fast', function(){
                                $('#editStudentidentifierModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editStudentidentifierModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editStudentidentifierModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editStudentidentifierModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editStudentidentifierForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editStudentidentifierForm input[name="id"]').val();
            const form = document.getElementById("editStudentidentifierForm");

            document.querySelector('#updateStudentidentifier').setAttribute('disabled', 'disabled');
            document.querySelector('#updateStudentidentifier svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("sexidentifier.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateStudentidentifier").removeAttribute("disabled");
                    document.querySelector("#updateStudentidentifier svg").style.cssText = "display: none;";
                    editStudentidentifierModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Data successfully updated.');
                    });
                }
                studentidentifierListTable.init();
            }).catch((error) => {
                document.querySelector("#updateStudentidentifier").removeAttribute("disabled");
                document.querySelector("#updateStudentidentifier svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editStudentidentifierForm .${key}`).addClass('border-danger')
                            $(`#editStudentidentifierForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editStudentidentifierModal.hide();

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
            if(action == 'DELETEStudentidentifier'){
                axios({
                    method: 'delete',
                    url: route('sexidentifier.destory', recordID),
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
                    studentidentifierListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREStudentidentifier'){
                axios({
                    method: 'post',
                    url: route('sexidentifier.restore', recordID),
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
                    studentidentifierListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATStudentidentifier'){
                axios({
                    method: 'post',
                    url: route('sexidentifier.update.status', recordID),
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
                    studentidentifierListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#studentidentifierListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATStudentidentifier');
            });
        });

        // Delete Course
        $('#studentidentifierListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEStudentidentifier');
            });
        });

        // Restore Course
        $('#studentidentifierListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREStudentidentifier');
            });
        });

        $('#studentidentifierImportModal').on('click','#saveImportStudentIdentifier',function(e) {
            e.preventDefault();
            $('#studentidentifierImportModal .dropzone').get(0).dropzone.processQueue();
            studentidentifierImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 3000);          
        });
    }
})();