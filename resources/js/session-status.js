import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var sessionStatusListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-SESTS").val() != "" ? $("#query-SESTS").val() : "";
        let status = $("#status-SESTS").val() != "" ? $("#status-SESTS").val() : "";
        let tableContent = new Tabulator("#sessionStatusListTable", {
            ajaxURL: route("session.status.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editSessionStatusModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-SESTS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SESTS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SESTS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-SESTS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SESTS").on("click", function (event) {
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
    if ($("#sessionStatusListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'sessionStatusListTable'){
                sessionStatusListTable.init();
            }
        });
        

        // Filter function
        function filterTitleHTMLForm() {
            sessionStatusListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-SESTS")[0].addEventListener(
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
        $("#tabulator-html-filter-go-SESTS").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SESTS").on("click", function (event) {
            $("#query-SESTS").val("");
            $("#status-SESTS").val("1");
            filterTitleHTMLForm();
        });

        const addSessionStatusModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSessionStatusModal"));
        const editSessionStatusModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSessionStatusModal"));
        const sessionStatusImportModal = tailwind.Modal.getOrCreateInstance("#sessionStatusImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addSessionStatusModalEl = document.getElementById('addSessionStatusModal')
        addSessionStatusModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addSessionStatusModal .acc__input-error').html('');
            $('#addSessionStatusModal .modal-body input:not([type="checkbox"])').val('');

            $('#addSessionStatusModal input[name="is_hesa"]').prop('checked', false);
            $('#addSessionStatusModal .hesa_code_area').fadeOut('fast', function(){
                $('#addSessionStatusModal .hesa_code_area input').val('');
            });
            $('#addSessionStatusModal input[name="is_df"]').prop('checked', false);
            $('#addSessionStatusModal .df_code_area').fadeOut('fast', function(){
                $('#addSessionStatusModal .df_code_area input').val('');
            });
            $('#addSessionStatusModal input[name="active"]').prop('checked', true);
        });
        
        const editSessionStatusModalEl = document.getElementById('editSessionStatusModal')
        editSessionStatusModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editSessionStatusModal .acc__input-error').html('');
            $('#editSessionStatusModal .modal-body input:not([type="checkbox"])').val('');
            $('#editSessionStatusModal input[name="id"]').val('0');

            $('#editSessionStatusModal input[name="is_hesa"]').prop('checked', false);
            $('#editSessionStatusModal .hesa_code_area').fadeOut('fast', function(){
                $('#editSessionStatusModal .hesa_code_area input').val('');
            });
            $('#editSessionStatusModal input[name="is_df"]').prop('checked', false);
            $('#editSessionStatusModal .df_code_area').fadeOut('fast', function(){
                $('#editSessionStatusModal .df_code_area input').val('');
            })
            $('#editSessionStatusModal input[name="active"]').prop('checked', false);
        });
        
        $('#addSessionStatusForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSessionStatusForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addSessionStatusForm .hesa_code_area input').val('');
                })
            }else{
                $('#addSessionStatusForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addSessionStatusForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addSessionStatusForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSessionStatusForm .df_code_area').fadeIn('fast', function(){
                    $('#addSessionStatusForm .df_code_area input').val('');
                })
            }else{
                $('#addSessionStatusForm .df_code_area').fadeOut('fast', function(){
                    $('#addSessionStatusForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editSessionStatusForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSessionStatusForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editSessionStatusForm .hesa_code_area input').val('');
                })
            }else{
                $('#editSessionStatusForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editSessionStatusForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editSessionStatusForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSessionStatusForm .df_code_area').fadeIn('fast', function(){
                    $('#editSessionStatusForm .df_code_area input').val('');
                })
            }else{
                $('#editSessionStatusForm .df_code_area').fadeOut('fast', function(){
                    $('#editSessionStatusForm .df_code_area input').val('');
                })
            }
        })

        $('#addSessionStatusForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addSessionStatusForm');
        
            document.querySelector('#saveSessionStatus').setAttribute('disabled', 'disabled');
            document.querySelector("#saveSessionStatus svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('session.status.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveSessionStatus').removeAttribute('disabled');
                document.querySelector("#saveSessionStatus svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addSessionStatusModal.hide();

                    succModal.show();
                    sessionStatusListTable.init();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                
            }).catch(error => {
                sessionStatusListTable.init();
                document.querySelector('#saveSessionStatus').removeAttribute('disabled');
                document.querySelector("#saveSessionStatus svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addSessionStatusForm .${key}`).addClass('border-danger');
                            $(`#addSessionStatusForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#sessionStatusListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("session.status.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editSessionStatusModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editSessionStatusModal input[name="is_hesa"]').prop('checked', true);
                            $('#editSessionStatusModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editSessionStatusModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editSessionStatusModal input[name="is_hesa"]').prop('checked', false);
                            $('#editSessionStatusModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editSessionStatusModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editSessionStatusModal input[name="is_df"]').prop('checked', true);
                            $('#editSessionStatusModal .df_code_area').fadeIn('fast', function(){
                                $('#editSessionStatusModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editSessionStatusModal input[name="is_df"]').prop('checked', false);
                            $('#editSessionStatusModal .df_code_area').fadeOut('fast', function(){
                                $('#editSessionStatusModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editSessionStatusModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editSessionStatusModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editSessionStatusModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editSessionStatusForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editSessionStatusForm input[name="id"]').val();
            const form = document.getElementById("editSessionStatusForm");

            document.querySelector('#updateSessionStatus').setAttribute('disabled', 'disabled');
            document.querySelector('#updateSessionStatus svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("session.status.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateSessionStatus").removeAttribute("disabled");
                    document.querySelector("#updateSessionStatus svg").style.cssText = "display: none;";
                    editSessionStatusModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                sessionStatusListTable.init();
            }).catch((error) => {
                document.querySelector("#updateSessionStatus").removeAttribute("disabled");
                document.querySelector("#updateSessionStatus svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editSessionStatusForm .${key}`).addClass('border-danger')
                            $(`#editSessionStatusForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editSessionStatusModal.hide();

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
            if(action == 'DELETESESTS'){
                axios({
                    method: 'delete',
                    url: route('session.status.destory', recordID),
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
                    sessionStatusListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORESESTS'){
                axios({
                    method: 'post',
                    url: route('session.status.restore', recordID),
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
                    sessionStatusListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTATSESTS'){
                axios({
                    method: 'post',
                    url: route('session.status.update.status', recordID),
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
                    sessionStatusListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#sessionStatusListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATSESTS');
            });
        });

        // Delete Course
        $('#sessionStatusListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETESESTS');
            });
        });

        // Restore Course
        $('#sessionStatusListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORESESTS');
            });
        });

        $('#sessionStatusImportModal').on('click','#saveSessionStatus',function(e) {
            e.preventDefault();
            $('#sessionStatusImportModal .dropzone').get(0).dropzone.processQueue();
            sessionStatusImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();