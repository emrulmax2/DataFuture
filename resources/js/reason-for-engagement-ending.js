import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var RsnengendListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-RSNENGEND").val() != "" ? $("#query-RSNENGEND").val() : "";
        let status = $("#status-RSNENGEND").val() != "" ? $("#status-RSNENGEND").val() : "";
        let tableContent = new Tabulator("#RsnengendListTable", {
            ajaxURL: route("rsnengend.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
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
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editRsnengendModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-RSNENGEND").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-RSNENGEND").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-RSNENGEND").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Course Details",
            });
        });

        $("#tabulator-export-html-RSNENGEND").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-RSNENGEND").on("click", function (event) {
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
    if ($("#RsnengendListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'RsnengendListTable'){
                RsnengendListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormRSNENGEND() {
            RsnengendListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-RSNENGEND")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormRSNENGEND();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-RSNENGEND").on("click", function (event) {
            filterHTMLFormRSNENGEND();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-RSNENGEND").on("click", function (event) {
            $("#query-RSNENGEND").val("");
            $("#status-RSNENGEND").val("1");
            filterHTMLFormRSNENGEND();
        });

        const addRsnengendModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRsnengendModal"));
        const editRsnengendModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editRsnengendModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addRsnengendModalEl = document.getElementById('addRsnengendModal')
        addRsnengendModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addRsnengendModal .acc__input-error').html('');
            $('#addRsnengendModal .modal-body input:not([type="checkbox"])').val('');

            $('#addRsnengendModal input[name="is_hesa"]').prop('checked', false);
            $('#addRsnengendModal .hesa_code_area').fadeOut('fast', function(){
                $('#addRsnengendModal .hesa_code_area input').val('');
            });
            $('#addRsnengendModal input[name="is_df"]').prop('checked', false);
            $('#addRsnengendModal .df_code_area').fadeOut('fast', function(){
                $('#addRsnengendModal .df_code_area input').val('');
            })
            $('#addRsnengendModal input[name="active"]').prop('checked', true);
        });
        
        const editRsnengendModalEl = document.getElementById('editRsnengendModal')
        editRsnengendModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editRsnengendModal .acc__input-error').html('');
            $('#editRsnengendModal .modal-body input:not([type="checkbox"])').val('');
            $('#editRsnengendModal input[name="id"]').val('0');

            $('#editRsnengendModal input[name="is_hesa"]').prop('checked', false);
            $('#editRsnengendModal .hesa_code_area').fadeOut('fast', function(){
                $('#editRsnengendModal .hesa_code_area input').val('');
            });
            $('#editRsnengendModal input[name="is_df"]').prop('checked', false);
            $('#editRsnengendModal .df_code_area').fadeOut('fast', function(){
                $('#editRsnengendModal .df_code_area input').val('');
            })
            $('#editRsnengendModal input[name="active"]').prop('checked', false);
        });
        
        $('#addRsnengendForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addRsnengendForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addRsnengendForm .hesa_code_area input').val('');
                })
            }else{
                $('#addRsnengendForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addRsnengendForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addRsnengendForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addRsnengendForm .df_code_area').fadeIn('fast', function(){
                    $('#addRsnengendForm .df_code_area input').val('');
                })
            }else{
                $('#addRsnengendForm .df_code_area').fadeOut('fast', function(){
                    $('#addRsnengendForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editRsnengendForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editRsnengendForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editRsnengendForm .hesa_code_area input').val('');
                })
            }else{
                $('#editRsnengendForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editRsnengendForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editRsnengendForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editRsnengendForm .df_code_area').fadeIn('fast', function(){
                    $('#editRsnengendForm .df_code_area input').val('');
                })
            }else{
                $('#editRsnengendForm .df_code_area').fadeOut('fast', function(){
                    $('#editRsnengendForm .df_code_area input').val('');
                })
            }
        })

        $('#addRsnengendForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addRsnengendForm');
        
            document.querySelector('#saveRsnengend').setAttribute('disabled', 'disabled');
            document.querySelector("#saveRsnengend svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('rsnengend.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveRsnengend').removeAttribute('disabled');
                document.querySelector("#saveRsnengend svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addRsnengendModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                RsnengendListTable.init();
            }).catch(error => {
                document.querySelector('#saveRsnengend').removeAttribute('disabled');
                document.querySelector("#saveRsnengend svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addRsnengendForm .${key}`).addClass('border-danger');
                            $(`#addRsnengendForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#RsnengendListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("rsnengend.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editRsnengendModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editRsnengendModal input[name="is_hesa"]').prop('checked', true);
                            $('#editRsnengendModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editRsnengendModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editRsnengendModal input[name="is_hesa"]').prop('checked', false);
                            $('#editRsnengendModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editRsnengendModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editRsnengendModal input[name="is_df"]').prop('checked', true);
                            $('#editRsnengendModal .df_code_area').fadeIn('fast', function(){
                                $('#editRsnengendModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editRsnengendModal input[name="is_df"]').prop('checked', false);
                            $('#editRsnengendModal .df_code_area').fadeOut('fast', function(){
                                $('#editRsnengendModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editRsnengendModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editRsnengendModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editRsnengendModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editRsnengendForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editRsnengendForm input[name="id"]').val();
            const form = document.getElementById("editRsnengendForm");

            document.querySelector('#updateRsnengend').setAttribute('disabled', 'disabled');
            document.querySelector('#updateRsnengend svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("rsnengend.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateRsnengend").removeAttribute("disabled");
                    document.querySelector("#updateRsnengend svg").style.cssText = "display: none;";
                    editRsnengendModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                RsnengendListTable.init();
            }).catch((error) => {
                document.querySelector("#updateRsnengend").removeAttribute("disabled");
                document.querySelector("#updateRsnengend svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editRsnengendForm .${key}`).addClass('border-danger')
                            $(`#editRsnengendForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editRsnengendModal.hide();

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
            if(action == 'DELETERSNENGEND'){
                axios({
                    method: 'delete',
                    url: route('rsnengend.destory', recordID),
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
                    RsnengendListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORERSNENGEND'){
                axios({
                    method: 'post',
                    url: route('rsnengend.restore', recordID),
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
                    RsnengendListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATRSNENGEND'){
                axios({
                    method: 'post',
                    url: route('rsnengend.update.status', recordID),
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
                    RsnengendListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#RsnengendListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATRSNENGEND');
            });
        });

        // Delete Course
        $('#RsnengendListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETERSNENGEND');
            });
        });

        // Restore Course
        $('#RsnengendListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORERSNENGEND');
            });
        });
    }
})();