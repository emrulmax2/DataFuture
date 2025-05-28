import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var kinsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-KINS").val() != "" ? $("#query-KINS").val() : "";
        let status = $("#status-KINS").val() != "" ? $("#status-KINS").val() : "";
        let tableContent = new Tabulator("#kinsListTable", {
            ajaxURL: route("kin.relations.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editKinsModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-KINS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-KINS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-KINS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Kins Relation Details",
            });
        });

        $("#tabulator-export-html-KINS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-KINS").on("click", function (event) {
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
    if ($("#kinsListTable").length) {
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'kinsListTable'){
                kinsListTable.init();
            }
        });


        // Filter function
        function filterHTMLFormKINS() {
            kinsListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-KINS")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormKINS();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-KINS").on("click", function (event) {
            filterHTMLFormKINS();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-KINS").on("click", function (event) {
            $("#query-KINS").val("");
            $("#status-KINS").val("1");
            filterHTMLFormKINS();
        });

        const addKinsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addKinsModal"));
        const editKinsModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editKinsModal"));
        const kinsrelationImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#kinsrelationImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addKinsModalEl = document.getElementById('addKinsModal')
        addKinsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addKinsModal .acc__input-error').html('');
            $('#addKinsModal .modal-body input:not([type="checkbox"])').val('');

            $('#addKinsModal input[name="is_hesa"]').prop('checked', false);
            $('#addKinsModal .hesa_code_area').fadeOut('fast', function(){
                $('#addKinsModal .hesa_code_area input').val('');
            });
            $('#addKinsModal input[name="is_df"]').prop('checked', false);
            $('#addKinsModal .df_code_area').fadeOut('fast', function(){
                $('#addKinsModal .df_code_area input').val('');
            })
            $('#addKinsModal input[name="active"]').prop('checked', true);
        });
        
        const editKinsModalEl = document.getElementById('editKinsModal')
        editKinsModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editKinsModal .acc__input-error').html('');
            $('#editKinsModal .modal-body input:not([type="checkbox"])').val('');
            $('#editKinsModal input[name="id"]').val('0');

            $('#editKinsModal input[name="is_hesa"]').prop('checked', false);
            $('#editKinsModal .hesa_code_area').fadeOut('fast', function(){
                $('#editKinsModal .hesa_code_area input').val('');
            });
            $('#editKinsModal input[name="is_df"]').prop('checked', false);
            $('#editKinsModal .df_code_area').fadeOut('fast', function(){
                $('#editKinsModal .df_code_area input').val('');
            })
            $('#addKinsModal input[name="active"]').prop('checked', false);
        });
        
        $('#addKinsForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addKinsForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addKinsForm .hesa_code_area input').val('');
                })
            }else{
                $('#addKinsForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addKinsForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addKinsForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addKinsForm .df_code_area').fadeIn('fast', function(){
                    $('#addKinsForm .df_code_area input').val('');
                })
            }else{
                $('#addKinsForm .df_code_area').fadeOut('fast', function(){
                    $('#addKinsForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editKinsForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editKinsForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editKinsForm .hesa_code_area input').val('');
                })
            }else{
                $('#editKinsForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editKinsForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editKinsForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editKinsForm .df_code_area').fadeIn('fast', function(){
                    $('#editKinsForm .df_code_area input').val('');
                })
            }else{
                $('#editKinsForm .df_code_area').fadeOut('fast', function(){
                    $('#editKinsForm .df_code_area input').val('');
                })
            }
        })

        $('#addKinsForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addKinsForm');
        
            document.querySelector('#saveKins').setAttribute('disabled', 'disabled');
            document.querySelector("#saveKins svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('kin.relations.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveKins').removeAttribute('disabled');
                document.querySelector("#saveKins svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addKinsModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                kinsListTable.init();
            }).catch(error => {
                document.querySelector('#saveKins').removeAttribute('disabled');
                document.querySelector("#saveKins svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addKinsForm .${key}`).addClass('border-danger');
                            $(`#addKinsForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#kinsListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("kin.relations.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editKinsModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editKinsModal input[name="is_hesa"]').prop('checked', true);
                            $('#editKinsModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editKinsModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editKinsModal input[name="is_hesa"]').prop('checked', false);
                            $('#editKinsModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editKinsModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editKinsModal input[name="is_df"]').prop('checked', true);
                            $('#editKinsModal .df_code_area').fadeIn('fast', function(){
                                $('#editKinsModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editKinsModal input[name="is_df"]').prop('checked', false);
                            $('#editKinsModal .df_code_area').fadeOut('fast', function(){
                                $('#editKinsModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editKinsModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editKinsModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editKinsModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editKinsForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editKinsForm input[name="id"]').val();
            const form = document.getElementById("editKinsForm");

            document.querySelector('#updateKins').setAttribute('disabled', 'disabled');
            document.querySelector('#updateKins svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("kin.relations.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateKins").removeAttribute("disabled");
                    document.querySelector("#updateKins svg").style.cssText = "display: none;";
                    editKinsModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                kinsListTable.init();
            }).catch((error) => {
                document.querySelector("#updateKins").removeAttribute("disabled");
                document.querySelector("#updateKins svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editKinsForm .${key}`).addClass('border-danger')
                            $(`#editKinsForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editKinsModal.hide();

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
            if(action == 'DELETEKINS'){
                axios({
                    method: 'delete',
                    url: route('kin.relations.destory', recordID),
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
                    kinsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREKINS'){
                axios({
                    method: 'post',
                    url: route('kin.relations.restore', recordID),
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
                    kinsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATKINS'){
                axios({
                    method: 'post',
                    url: route('kin.relations.update.status', recordID),
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
                    kinsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#kinsListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATKINS');
            });
        });

        // Delete Course
        $('#kinsListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEKINS');
            });
        });

        // Restore Course
        $('#kinsListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREKINS');
            });
        });

        $('#kinsrelationImportModal').on('click','#saveKinsrelation',function(e) {
            e.preventDefault();
            $('#kinsrelationImportModal .dropzone').get(0).dropzone.processQueue();
            kinsrelationImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();