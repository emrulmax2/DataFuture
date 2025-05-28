import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var disabilityListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-DISABILITY").val() != "" ? $("#query-DISABILITY").val() : "";
        let status = $("#status-DISABILITY").val() != "" ? $("#status-DISABILITY").val() : "";
        let tableContent = new Tabulator("#disabilityListTable", {
            ajaxURL: route("disabilities.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editDisabilityModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-DISABILITY").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-DISABILITY").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-DISABILITY").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Disabilities Details",
            });
        });

        $("#tabulator-export-html-DISABILITY").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-DISABILITY").on("click", function (event) {
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
    if ($("#disabilityListTable").length) {
        // Init Table
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'disabilityListTable'){
                disabilityListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormDisability() {
            disabilityListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-DISABILITY")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormDisability();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-DISABILITY").on("click", function (event) {
            filterHTMLFormDisability();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-DISABILITY").on("click", function (event) {
            $("#query-DISABILITY").val("");
            $("#status-DISABILITY").val("1");
            filterHTMLFormDisability();
        });

        const addDisabilityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addDisabilityModal"));
        const editDisabilityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editDisabilityModal"));
        const disabilitiesImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#disabilitiesImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addDisabilityModalEl = document.getElementById('addDisabilityModal')
        addDisabilityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addDisabilityModal .acc__input-error').html('');
            $('#addDisabilityModal .modal-body input:not([type="checkbox"])').val('');

            $('#addDisabilityModal input[name="is_hesa"]').prop('checked', false);
            $('#addDisabilityModal .hesa_code_area').fadeOut('fast', function(){
                $('#addDisabilityModal .hesa_code_area input').val('');
            });
            $('#addDisabilityModal input[name="is_df"]').prop('checked', false);
            $('#addDisabilityModal .df_code_area').fadeOut('fast', function(){
                $('#addDisabilityModal .df_code_area input').val('');
            })
            $('#addDisabilityModal input[name="active"]').prop('checked', true);
        });
        
        const editDisabilityModalEl = document.getElementById('editDisabilityModal')
        editDisabilityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editDisabilityModal .acc__input-error').html('');
            $('#editDisabilityModal .modal-body input:not([type="checkbox"])').val('');
            $('#editDisabilityModal input[name="id"]').val('0');

            $('#editDisabilityModal input[name="is_hesa"]').prop('checked', false);
            $('#editDisabilityModal .hesa_code_area').fadeOut('fast', function(){
                $('#editDisabilityModal .hesa_code_area input').val('');
            });
            $('#editDisabilityModal input[name="is_df"]').prop('checked', false);
            $('#editDisabilityModal .df_code_area').fadeOut('fast', function(){
                $('#editDisabilityModal .df_code_area input').val('');
            })
            $('#editDisabilityModal input[name="active"]').prop('checked', false);
        });
        
        $('#addDisabilityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addDisabilityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addDisabilityForm .hesa_code_area input').val('');
                })
            }else{
                $('#addDisabilityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addDisabilityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addDisabilityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addDisabilityForm .df_code_area').fadeIn('fast', function(){
                    $('#addDisabilityForm .df_code_area input').val('');
                })
            }else{
                $('#addDisabilityForm .df_code_area').fadeOut('fast', function(){
                    $('#addDisabilityForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editDisabilityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editDisabilityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editDisabilityForm .hesa_code_area input').val('');
                })
            }else{
                $('#editDisabilityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editDisabilityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editDisabilityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editDisabilityForm .df_code_area').fadeIn('fast', function(){
                    $('#editDisabilityForm .df_code_area input').val('');
                })
            }else{
                $('#editDisabilityForm .df_code_area').fadeOut('fast', function(){
                    $('#editDisabilityForm .df_code_area input').val('');
                })
            }
        })

        $('#addDisabilityForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addDisabilityForm');
        
            document.querySelector('#saveDisability').setAttribute('disabled', 'disabled');
            document.querySelector("#saveDisability svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('disabilities.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveDisability').removeAttribute('disabled');
                document.querySelector("#saveDisability svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addDisabilityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                disabilityListTable.init();
            }).catch(error => {
                document.querySelector('#saveDisability').removeAttribute('disabled');
                document.querySelector("#saveDisability svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addDisabilityForm .${key}`).addClass('border-danger');
                            $(`#addDisabilityForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#disabilityListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("disabilities.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editDisabilityModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editDisabilityModal input[name="is_hesa"]').prop('checked', true);
                            $('#editDisabilityModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editDisabilityModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editDisabilityModal input[name="is_hesa"]').prop('checked', false);
                            $('#editDisabilityModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editDisabilityModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editDisabilityModal input[name="is_df"]').prop('checked', true);
                            $('#editDisabilityModal .df_code_area').fadeIn('fast', function(){
                                $('#editDisabilityModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editDisabilityModal input[name="is_df"]').prop('checked', false);
                            $('#editDisabilityModal .df_code_area').fadeOut('fast', function(){
                                $('#editDisabilityModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editDisabilityModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editDisabilityModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editDisabilityModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editDisabilityForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editDisabilityForm input[name="id"]').val();
            const form = document.getElementById("editDisabilityForm");

            document.querySelector('#updateDisability').setAttribute('disabled', 'disabled');
            document.querySelector('#updateDisability svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("disabilities.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateDisability").removeAttribute("disabled");
                    document.querySelector("#updateDisability svg").style.cssText = "display: none;";
                    editDisabilityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                disabilityListTable.init();
            }).catch((error) => {
                document.querySelector("#updateDisability").removeAttribute("disabled");
                document.querySelector("#updateDisability svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editDisabilityForm .${key}`).addClass('border-danger')
                            $(`#editDisabilityForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editDisabilityModal.hide();

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
            if(action == 'DELETEDISABILITY'){
                axios({
                    method: 'delete',
                    url: route('disabilities.destory', recordID),
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
                    disabilityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREDISABILITY'){
                axios({
                    method: 'post',
                    url: route('disabilities.restore', recordID),
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
                    disabilityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATDISABILITY'){
                axios({
                    method: 'post',
                    url: route('disabilities.update.status', recordID),
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
                    disabilityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
        // Status Updater
        $('#disabilityListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATDISABILITY');
            });
        });

        // Delete Course
        $('#disabilityListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEDISABILITY');
            });
        });

        // Restore Course
        $('#disabilityListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREDISABILITY');
            });
        });

        $('#disabilitiesImportModal').on('click','#saveDisabilities',function(e) {
            e.preventDefault();
            $('#disabilitiesImportModal .dropzone').get(0).dropzone.processQueue();
            disabilitiesImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();