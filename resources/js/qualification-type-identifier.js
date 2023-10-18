import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var QaualtypeidListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-QUALTYPEID").val() != "" ? $("#query-QUALTYPEID").val() : "";
        let status = $("#status-QUALTYPEID").val() != "" ? $("#status-QUALTYPEID").val() : "";
        let tableContent = new Tabulator("#QaualtypeidListTable", {
            ajaxURL: route("qaualtypeid.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editQaualtypeidModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-QUALTYPEID").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-QUALTYPEID").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-QUALTYPEID").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Qualification Type Identifier Details",
            });
        });

        $("#tabulator-export-html-QUALTYPEID").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-QUALTYPEID").on("click", function (event) {
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
    if ($("#QaualtypeidListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'QaualtypeidListTable'){
                QaualtypeidListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormQUALTYPEID() {
            QaualtypeidListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-QUALTYPEID")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormQUALTYPEID();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-QUALTYPEID").on("click", function (event) {
            filterHTMLFormQUALTYPEID();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-QUALTYPEID").on("click", function (event) {
            $("#query-QUALTYPEID").val("");
            $("#status-QUALTYPEID").val("1");
            filterHTMLFormQUALTYPEID();
        });

        const addQaualtypeidModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addQaualtypeidModal"));
        const editQaualtypeidModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editQaualtypeidModal"));
        const qaualtypeidImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#qaualtypeidImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addQaualtypeidModalEl = document.getElementById('addQaualtypeidModal')
        addQaualtypeidModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addQaualtypeidModal .acc__input-error').html('');
            $('#addQaualtypeidModal .modal-body input:not([type="checkbox"])').val('');

            $('#addQaualtypeidModal input[name="is_hesa"]').prop('checked', false);
            $('#addQaualtypeidModal .hesa_code_area').fadeOut('fast', function(){
                $('#addQaualtypeidModal .hesa_code_area input').val('');
            });
            $('#addQaualtypeidModal input[name="is_df"]').prop('checked', false);
            $('#addQaualtypeidModal .df_code_area').fadeOut('fast', function(){
                $('#addQaualtypeidModal .df_code_area input').val('');
            })
            $('#addQaualtypeidModal input[name="active"]').prop('checked', true);
        });
        
        const editQaualtypeidModalEl = document.getElementById('editQaualtypeidModal')
        editQaualtypeidModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editQaualtypeidModal .acc__input-error').html('');
            $('#editQaualtypeidModal .modal-body input:not([type="checkbox"])').val('');
            $('#editQaualtypeidModal input[name="id"]').val('0');

            $('#editQaualtypeidModal input[name="is_hesa"]').prop('checked', false);
            $('#editQaualtypeidModal .hesa_code_area').fadeOut('fast', function(){
                $('#editQaualtypeidModal .hesa_code_area input').val('');
            });
            $('#editQaualtypeidModal input[name="is_df"]').prop('checked', false);
            $('#editQaualtypeidModal .df_code_area').fadeOut('fast', function(){
                $('#editQaualtypeidModal .df_code_area input').val('');
            })
            $('#editQaualtypeidModal input[name="active"]').prop('checked', false);
        });
        
        $('#addQaualtypeidForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addQaualtypeidForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addQaualtypeidForm .hesa_code_area input').val('');
                })
            }else{
                $('#addQaualtypeidForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addQaualtypeidForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addQaualtypeidForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addQaualtypeidForm .df_code_area').fadeIn('fast', function(){
                    $('#addQaualtypeidForm .df_code_area input').val('');
                })
            }else{
                $('#addQaualtypeidForm .df_code_area').fadeOut('fast', function(){
                    $('#addQaualtypeidForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editQaualtypeidForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editQaualtypeidForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editQaualtypeidForm .hesa_code_area input').val('');
                })
            }else{
                $('#editQaualtypeidForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editQaualtypeidForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editQaualtypeidForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editQaualtypeidForm .df_code_area').fadeIn('fast', function(){
                    $('#editQaualtypeidForm .df_code_area input').val('');
                })
            }else{
                $('#editQaualtypeidForm .df_code_area').fadeOut('fast', function(){
                    $('#editQaualtypeidForm .df_code_area input').val('');
                })
            }
        })

        $('#addQaualtypeidForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addQaualtypeidForm');
        
            document.querySelector('#saveQaualtypeid').setAttribute('disabled', 'disabled');
            document.querySelector("#saveQaualtypeid svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('qaualtypeid.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveQaualtypeid').removeAttribute('disabled');
                document.querySelector("#saveQaualtypeid svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addQaualtypeidModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                QaualtypeidListTable.init();
            }).catch(error => {
                document.querySelector('#saveQaualtypeid').removeAttribute('disabled');
                document.querySelector("#saveQaualtypeid svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addQaualtypeidForm .${key}`).addClass('border-danger');
                            $(`#addQaualtypeidForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#QaualtypeidListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("qaualtypeid.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editQaualtypeidModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editQaualtypeidModal input[name="is_hesa"]').prop('checked', true);
                            $('#editQaualtypeidModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editQaualtypeidModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editQaualtypeidModal input[name="is_hesa"]').prop('checked', false);
                            $('#editQaualtypeidModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editQaualtypeidModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editQaualtypeidModal input[name="is_df"]').prop('checked', true);
                            $('#editQaualtypeidModal .df_code_area').fadeIn('fast', function(){
                                $('#editQaualtypeidModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editQaualtypeidModal input[name="is_df"]').prop('checked', false);
                            $('#editQaualtypeidModal .df_code_area').fadeOut('fast', function(){
                                $('#editQaualtypeidModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editQaualtypeidModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editQaualtypeidModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editQaualtypeidModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editQaualtypeidForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editQaualtypeidForm input[name="id"]').val();
            const form = document.getElementById("editQaualtypeidForm");

            document.querySelector('#updateQaualtypeid').setAttribute('disabled', 'disabled');
            document.querySelector('#updateQaualtypeid svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("qaualtypeid.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateQaualtypeid").removeAttribute("disabled");
                    document.querySelector("#updateQaualtypeid svg").style.cssText = "display: none;";
                    editQaualtypeidModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                QaualtypeidListTable.init();
            }).catch((error) => {
                document.querySelector("#updateQaualtypeid").removeAttribute("disabled");
                document.querySelector("#updateQaualtypeid svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editQaualtypeidForm .${key}`).addClass('border-danger')
                            $(`#editQaualtypeidForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editQaualtypeidModal.hide();

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
            if(action == 'DELETEQUALTYPEID'){
                axios({
                    method: 'delete',
                    url: route('qaualtypeid.destory', recordID),
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
                    QaualtypeidListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREQUALTYPEID'){
                axios({
                    method: 'post',
                    url: route('qaualtypeid.restore', recordID),
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
                    QaualtypeidListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATQUALTYPEID'){
                axios({
                    method: 'post',
                    url: route('qaualtypeid.update.status', recordID),
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
                    QaualtypeidListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#QaualtypeidListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATQUALTYPEID');
            });
        });

        // Delete Course
        $('#QaualtypeidListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEQUALTYPEID');
            });
        });

        // Restore Course
        $('#QaualtypeidListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREQUALTYPEID');
            });
        });

        $('#qaualtypeidImportModal').on('click','#saveQaualtypeid',function(e) {
            e.preventDefault();
            $('#qaualtypeidImportModal .dropzone').get(0).dropzone.processQueue();
            qaualtypeidImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 3000);           
        });
    }
})();