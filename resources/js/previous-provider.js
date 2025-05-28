import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var PreviousproviderListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-PREVIOUSPROVIDER").val() != "" ? $("#query-PREVIOUSPROVIDER").val() : "";
        let status = $("#status-PREVIOUSPROVIDER").val() != "" ? $("#status-PREVIOUSPROVIDER").val() : "";
        let tableContent = new Tabulator("#PreviousproviderListTable", {
            ajaxURL: route("previousprovider.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editPreviousproviderModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
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
        $("#tabulator-export-csv-PREVIOUSPROVIDER").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-PREVIOUSPROVIDER").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-PREVIOUSPROVIDER").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Previous Provider Details",
            });
        });

        $("#tabulator-export-html-PREVIOUSPROVIDER").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-PREVIOUSPROVIDER").on("click", function (event) {
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
    if ($("#PreviousproviderListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'PreviousproviderListTable'){
                PreviousproviderListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormPREVIOUSPROVIDER() {
            PreviousproviderListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PREVIOUSPROVIDER")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormPREVIOUSPROVIDER();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-PREVIOUSPROVIDER").on("click", function (event) {
            filterHTMLFormPREVIOUSPROVIDER();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PREVIOUSPROVIDER").on("click", function (event) {
            $("#query-PREVIOUSPROVIDER").val("");
            $("#status-PREVIOUSPROVIDER").val("1");
            filterHTMLFormPREVIOUSPROVIDER();
        });

        const addPreviousproviderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPreviousproviderModal"));
        const editPreviousproviderModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPreviousproviderModal"));
        const previousproviderImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#previousproviderImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addPreviousproviderModalEl = document.getElementById('addPreviousproviderModal')
        addPreviousproviderModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addPreviousproviderModal .acc__input-error').html('');
            $('#addPreviousproviderModal .modal-body input:not([type="checkbox"])').val('');

            $('#addPreviousproviderModal input[name="is_hesa"]').prop('checked', false);
            $('#addPreviousproviderModal .hesa_code_area').fadeOut('fast', function(){
                $('#addPreviousproviderModal .hesa_code_area input').val('');
            });
            $('#addPreviousproviderModal input[name="is_df"]').prop('checked', false);
            $('#addPreviousproviderModal .df_code_area').fadeOut('fast', function(){
                $('#addPreviousproviderModal .df_code_area input').val('');
            })
            $('#addPreviousproviderModal input[name="active"]').prop('checked', true);
        });
        
        const editPreviousproviderModalEl = document.getElementById('editPreviousproviderModal')
        editPreviousproviderModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editPreviousproviderModal .acc__input-error').html('');
            $('#editPreviousproviderModal .modal-body input:not([type="checkbox"])').val('');
            $('#editPreviousproviderModal input[name="id"]').val('0');

            $('#editPreviousproviderModal input[name="is_hesa"]').prop('checked', false);
            $('#editPreviousproviderModal .hesa_code_area').fadeOut('fast', function(){
                $('#editPreviousproviderModal .hesa_code_area input').val('');
            });
            $('#editPreviousproviderModal input[name="is_df"]').prop('checked', false);
            $('#editPreviousproviderModal .df_code_area').fadeOut('fast', function(){
                $('#editPreviousproviderModal .df_code_area input').val('');
            })
            $('#editPreviousproviderModal input[name="active"]').prop('checked', false);
        });
        
        $('#addPreviousproviderForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addPreviousproviderForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addPreviousproviderForm .hesa_code_area input').val('');
                })
            }else{
                $('#addPreviousproviderForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addPreviousproviderForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addPreviousproviderForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addPreviousproviderForm .df_code_area').fadeIn('fast', function(){
                    $('#addPreviousproviderForm .df_code_area input').val('');
                })
            }else{
                $('#addPreviousproviderForm .df_code_area').fadeOut('fast', function(){
                    $('#addPreviousproviderForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editPreviousproviderForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editPreviousproviderForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editPreviousproviderForm .hesa_code_area input').val('');
                })
            }else{
                $('#editPreviousproviderForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editPreviousproviderForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editPreviousproviderForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editPreviousproviderForm .df_code_area').fadeIn('fast', function(){
                    $('#editPreviousproviderForm .df_code_area input').val('');
                })
            }else{
                $('#editPreviousproviderForm .df_code_area').fadeOut('fast', function(){
                    $('#editPreviousproviderForm .df_code_area input').val('');
                })
            }
        })

        $('#addPreviousproviderForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addPreviousproviderForm');
        
            document.querySelector('#savePreviousprovider').setAttribute('disabled', 'disabled');
            document.querySelector("#savePreviousprovider svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('previousprovider.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#savePreviousprovider').removeAttribute('disabled');
                document.querySelector("#savePreviousprovider svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addPreviousproviderModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                PreviousproviderListTable.init();
            }).catch(error => {
                document.querySelector('#savePreviousprovider').removeAttribute('disabled');
                document.querySelector("#savePreviousprovider svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addPreviousproviderForm .${key}`).addClass('border-danger');
                            $(`#addPreviousproviderForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#PreviousproviderListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("previousprovider.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editPreviousproviderModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editPreviousproviderModal input[name="is_hesa"]').prop('checked', true);
                            $('#editPreviousproviderModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editPreviousproviderModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editPreviousproviderModal input[name="is_hesa"]').prop('checked', false);
                            $('#editPreviousproviderModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editPreviousproviderModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editPreviousproviderModal input[name="is_df"]').prop('checked', true);
                            $('#editPreviousproviderModal .df_code_area').fadeIn('fast', function(){
                                $('#editPreviousproviderModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editPreviousproviderModal input[name="is_df"]').prop('checked', false);
                            $('#editPreviousproviderModal .df_code_area').fadeOut('fast', function(){
                                $('#editPreviousproviderModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editPreviousproviderModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editPreviousproviderModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editPreviousproviderModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editPreviousproviderForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editPreviousproviderForm input[name="id"]').val();
            const form = document.getElementById("editPreviousproviderForm");

            document.querySelector('#updatePreviousprovider').setAttribute('disabled', 'disabled');
            document.querySelector('#updatePreviousprovider svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("previousprovider.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updatePreviousprovider").removeAttribute("disabled");
                    document.querySelector("#updatePreviousprovider svg").style.cssText = "display: none;";
                    editPreviousproviderModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                PreviousproviderListTable.init();
            }).catch((error) => {
                document.querySelector("#updatePreviousprovider").removeAttribute("disabled");
                document.querySelector("#updatePreviousprovider svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editPreviousproviderForm .${key}`).addClass('border-danger')
                            $(`#editPreviousproviderForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editPreviousproviderModal.hide();

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
            if(action == 'DELETEPREVIOUSPROVIDER'){
                axios({
                    method: 'delete',
                    url: route('previousprovider.destory', recordID),
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
                    PreviousproviderListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREPREVIOUSPROVIDER'){
                axios({
                    method: 'post',
                    url: route('previousprovider.restore', recordID),
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
                    PreviousproviderListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATPREVIOUSPROVIDER'){
                axios({
                    method: 'post',
                    url: route('previousprovider.update.status', recordID),
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
                    PreviousproviderListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#PreviousproviderListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATPREVIOUSPROVIDER');
            });
        });

        // Delete Course
        $('#PreviousproviderListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEPREVIOUSPROVIDER');
            });
        });

        // Restore Course
        $('#PreviousproviderListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREPREVIOUSPROVIDER');
            });
        });

        $('#previousproviderImportModal').on('click','#savePreviousprovider',function(e) {
            e.preventDefault();
            $('#previousproviderImportModal .dropzone').get(0).dropzone.processQueue();
            previousproviderImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);           
        });
    }
})();