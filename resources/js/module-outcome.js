import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var ModuleOutcomeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-MODULEOCTM").val() != "" ? $("#query-MODULEOCTM").val() : "";
        let status = $("#status-MODULEOCTM").val() != "" ? $("#status-MODULEOCTM").val() : "";
        let tableContent = new Tabulator("#ModuleOutcomeListTable", {
            ajaxURL: route("module.outcome.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editModuleOutcomeModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-MODULEOCTM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-MODULEOCTM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-MODULEOCTM").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-MODULEOCTM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-MODULEOCTM").on("click", function (event) {
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
    if ($("#ModuleOutcomeListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'ModuleOutcomeListTable'){
                ModuleOutcomeListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormMODULEOCTM() {
            ModuleOutcomeListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-MODULEOCTM")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormMODULEOCTM();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-MODULEOCTM").on("click", function (event) {
            filterHTMLFormMODULEOCTM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-MODULEOCTM").on("click", function (event) {
            $("#query-MODULEOCTM").val("");
            $("#status-MODULEOCTM").val("1");
            filterHTMLFormMODULEOCTM();
        });

        const addModuleOutcomeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModuleOutcomeModal"));
        const editModuleOutcomeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModuleOutcomeModal"));
        const moduleOutcomeImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleOutcomeImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addModuleOutcomeModalEl = document.getElementById('addModuleOutcomeModal')
        addModuleOutcomeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addModuleOutcomeModal .acc__input-error').html('');
            $('#addModuleOutcomeModal .modal-body input:not([type="checkbox"])').val('');

            $('#addModuleOutcomeModal input[name="is_hesa"]').prop('checked', false);
            $('#addModuleOutcomeModal .hesa_code_area').fadeOut('fast', function(){
                $('#addModuleOutcomeModal .hesa_code_area input').val('');
            });
            $('#addModuleOutcomeModal input[name="is_df"]').prop('checked', false);
            $('#addModuleOutcomeModal .df_code_area').fadeOut('fast', function(){
                $('#addModuleOutcomeModal .df_code_area input').val('');
            })
            $('#addModuleOutcomeModal input[name="active"]').prop('checked', true);
        });
        
        const editModuleOutcomeModalEl = document.getElementById('editModuleOutcomeModal')
        editModuleOutcomeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModuleOutcomeModal .acc__input-error').html('');
            $('#editModuleOutcomeModal .modal-body input:not([type="checkbox"])').val('');
            $('#editModuleOutcomeModal input[name="id"]').val('0');

            $('#editModuleOutcomeModal input[name="is_hesa"]').prop('checked', false);
            $('#editModuleOutcomeModal .hesa_code_area').fadeOut('fast', function(){
                $('#editModuleOutcomeModal .hesa_code_area input').val('');
            });
            $('#editModuleOutcomeModal input[name="is_df"]').prop('checked', false);
            $('#editModuleOutcomeModal .df_code_area').fadeOut('fast', function(){
                $('#editModuleOutcomeModal .df_code_area input').val('');
            })
            $('#editModuleOutcomeModal input[name="active"]').prop('checked', false);
        });
        
        $('#addModuleOutcomeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addModuleOutcomeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addModuleOutcomeForm .hesa_code_area input').val('');
                })
            }else{
                $('#addModuleOutcomeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addModuleOutcomeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addModuleOutcomeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addModuleOutcomeForm .df_code_area').fadeIn('fast', function(){
                    $('#addModuleOutcomeForm .df_code_area input').val('');
                })
            }else{
                $('#addModuleOutcomeForm .df_code_area').fadeOut('fast', function(){
                    $('#addModuleOutcomeForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editModuleOutcomeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editModuleOutcomeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editModuleOutcomeForm .hesa_code_area input').val('');
                })
            }else{
                $('#editModuleOutcomeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editModuleOutcomeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editModuleOutcomeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editModuleOutcomeForm .df_code_area').fadeIn('fast', function(){
                    $('#editModuleOutcomeForm .df_code_area input').val('');
                })
            }else{
                $('#editModuleOutcomeForm .df_code_area').fadeOut('fast', function(){
                    $('#editModuleOutcomeForm .df_code_area input').val('');
                })
            }
        })

        $('#addModuleOutcomeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addModuleOutcomeForm');
        
            document.querySelector('#saveModuleOutcome').setAttribute('disabled', 'disabled');
            document.querySelector("#saveModuleOutcome svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('module.outcome.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveModuleOutcome').removeAttribute('disabled');
                document.querySelector("#saveModuleOutcome svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addModuleOutcomeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                ModuleOutcomeListTable.init();
            }).catch(error => {
                document.querySelector('#saveModuleOutcome').removeAttribute('disabled');
                document.querySelector("#saveModuleOutcome svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addModuleOutcomeForm .${key}`).addClass('border-danger');
                            $(`#addModuleOutcomeForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#ModuleOutcomeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("module.outcome.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editModuleOutcomeModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editModuleOutcomeModal input[name="is_hesa"]').prop('checked', true);
                            $('#editModuleOutcomeModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editModuleOutcomeModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editModuleOutcomeModal input[name="is_hesa"]').prop('checked', false);
                            $('#editModuleOutcomeModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editModuleOutcomeModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editModuleOutcomeModal input[name="is_df"]').prop('checked', true);
                            $('#editModuleOutcomeModal .df_code_area').fadeIn('fast', function(){
                                $('#editModuleOutcomeModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editModuleOutcomeModal input[name="is_df"]').prop('checked', false);
                            $('#editModuleOutcomeModal .df_code_area').fadeOut('fast', function(){
                                $('#editModuleOutcomeModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editModuleOutcomeModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editModuleOutcomeModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editModuleOutcomeModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editModuleOutcomeForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editModuleOutcomeForm input[name="id"]').val();
            const form = document.getElementById("editModuleOutcomeForm");

            document.querySelector('#updateModuleOutcome').setAttribute('disabled', 'disabled');
            document.querySelector('#updateModuleOutcome svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("module.outcome.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateModuleOutcome").removeAttribute("disabled");
                    document.querySelector("#updateModuleOutcome svg").style.cssText = "display: none;";
                    editModuleOutcomeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                ModuleOutcomeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateModuleOutcome").removeAttribute("disabled");
                document.querySelector("#updateModuleOutcome svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editModuleOutcomeForm .${key}`).addClass('border-danger')
                            $(`#editModuleOutcomeForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editModuleOutcomeModal.hide();

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
            if(action == 'DELETEMODULEOCTM'){
                axios({
                    method: 'delete',
                    url: route('module.outcome.destory', recordID),
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
                    ModuleOutcomeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREMODULEOCTM'){
                axios({
                    method: 'post',
                    url: route('module.outcome.restore', recordID),
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
                    ModuleOutcomeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATMODULEOCTM'){
                axios({
                    method: 'post',
                    url: route('module.outcome.update.status', recordID),
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
                    ModuleOutcomeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#ModuleOutcomeListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATMODULEOCTM');
            });
        });

        // Delete Course
        $('#ModuleOutcomeListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEMODULEOCTM');
            });
        });

        // Restore Course
        $('#ModuleOutcomeListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREMODULEOCTM');
            });
        });

        $('#moduleOutcomeImportModal').on('click','#saveModuleOutcome',function(e) {
            e.preventDefault();
            $('#moduleOutcomeImportModal .dropzone').get(0).dropzone.processQueue();
            moduleOutcomeImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();