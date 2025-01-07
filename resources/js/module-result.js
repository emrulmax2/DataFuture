import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var ModuleResultListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-MODULERSLT").val() != "" ? $("#query-MODULERSLT").val() : "";
        let status = $("#status-MODULERSLT").val() != "" ? $("#status-MODULERSLT").val() : "";
        let tableContent = new Tabulator("#ModuleResultListTable", {
            ajaxURL: route("module.result.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editModuleResultModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-MODULERSLT").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-MODULERSLT").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-MODULERSLT").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-MODULERSLT").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-MODULERSLT").on("click", function (event) {
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
    if ($("#ModuleResultListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'ModuleResultListTable'){
                ModuleResultListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormMODULERSLT() {
            ModuleResultListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-MODULERSLT")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormMODULERSLT();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-MODULERSLT").on("click", function (event) {
            filterHTMLFormMODULERSLT();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-MODULERSLT").on("click", function (event) {
            $("#query-MODULERSLT").val("");
            $("#status-MODULERSLT").val("1");
            filterHTMLFormMODULERSLT();
        });

        const addModuleResultModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModuleResultModal"));
        const editModuleResultModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModuleResultModal"));
        const moduleResultImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleResultImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addModuleResultModalEl = document.getElementById('addModuleResultModal')
        addModuleResultModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addModuleResultModal .acc__input-error').html('');
            $('#addModuleResultModal .modal-body input:not([type="checkbox"])').val('');

            $('#addModuleResultModal input[name="is_hesa"]').prop('checked', false);
            $('#addModuleResultModal .hesa_code_area').fadeOut('fast', function(){
                $('#addModuleResultModal .hesa_code_area input').val('');
            });
            $('#addModuleResultModal input[name="is_df"]').prop('checked', false);
            $('#addModuleResultModal .df_code_area').fadeOut('fast', function(){
                $('#addModuleResultModal .df_code_area input').val('');
            })
            $('#addModuleResultModal input[name="active"]').prop('checked', true);
        });
        
        const editModuleResultModalEl = document.getElementById('editModuleResultModal')
        editModuleResultModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModuleResultModal .acc__input-error').html('');
            $('#editModuleResultModal .modal-body input:not([type="checkbox"])').val('');
            $('#editModuleResultModal input[name="id"]').val('0');

            $('#editModuleResultModal input[name="is_hesa"]').prop('checked', false);
            $('#editModuleResultModal .hesa_code_area').fadeOut('fast', function(){
                $('#editModuleResultModal .hesa_code_area input').val('');
            });
            $('#editModuleResultModal input[name="is_df"]').prop('checked', false);
            $('#editModuleResultModal .df_code_area').fadeOut('fast', function(){
                $('#editModuleResultModal .df_code_area input').val('');
            })
            $('#editModuleResultModal input[name="active"]').prop('checked', false);
        });
        
        $('#addModuleResultForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addModuleResultForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addModuleResultForm .hesa_code_area input').val('');
                })
            }else{
                $('#addModuleResultForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addModuleResultForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addModuleResultForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addModuleResultForm .df_code_area').fadeIn('fast', function(){
                    $('#addModuleResultForm .df_code_area input').val('');
                })
            }else{
                $('#addModuleResultForm .df_code_area').fadeOut('fast', function(){
                    $('#addModuleResultForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editModuleResultForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editModuleResultForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editModuleResultForm .hesa_code_area input').val('');
                })
            }else{
                $('#editModuleResultForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editModuleResultForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editModuleResultForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editModuleResultForm .df_code_area').fadeIn('fast', function(){
                    $('#editModuleResultForm .df_code_area input').val('');
                })
            }else{
                $('#editModuleResultForm .df_code_area').fadeOut('fast', function(){
                    $('#editModuleResultForm .df_code_area input').val('');
                })
            }
        })

        $('#addModuleResultForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addModuleResultForm');
        
            document.querySelector('#saveModuleResult').setAttribute('disabled', 'disabled');
            document.querySelector("#saveModuleResult svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('module.result.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveModuleResult').removeAttribute('disabled');
                document.querySelector("#saveModuleResult svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addModuleResultModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                ModuleResultListTable.init();
            }).catch(error => {
                document.querySelector('#saveModuleResult').removeAttribute('disabled');
                document.querySelector("#saveModuleResult svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addModuleResultForm .${key}`).addClass('border-danger');
                            $(`#addModuleResultForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#ModuleResultListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("module.result.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editModuleResultModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editModuleResultModal input[name="is_hesa"]').prop('checked', true);
                            $('#editModuleResultModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editModuleResultModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editModuleResultModal input[name="is_hesa"]').prop('checked', false);
                            $('#editModuleResultModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editModuleResultModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editModuleResultModal input[name="is_df"]').prop('checked', true);
                            $('#editModuleResultModal .df_code_area').fadeIn('fast', function(){
                                $('#editModuleResultModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editModuleResultModal input[name="is_df"]').prop('checked', false);
                            $('#editModuleResultModal .df_code_area').fadeOut('fast', function(){
                                $('#editModuleResultModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editModuleResultModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editModuleResultModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editModuleResultModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editModuleResultForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editModuleResultForm input[name="id"]').val();
            const form = document.getElementById("editModuleResultForm");

            document.querySelector('#updateModuleResult').setAttribute('disabled', 'disabled');
            document.querySelector('#updateModuleResult svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("module.result.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateModuleResult").removeAttribute("disabled");
                    document.querySelector("#updateModuleResult svg").style.cssText = "display: none;";
                    editModuleResultModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                ModuleResultListTable.init();
            }).catch((error) => {
                document.querySelector("#updateModuleResult").removeAttribute("disabled");
                document.querySelector("#updateModuleResult svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editModuleResultForm .${key}`).addClass('border-danger')
                            $(`#editModuleResultForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editModuleResultModal.hide();

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
            if(action == 'DELETEMODULERSLT'){
                axios({
                    method: 'delete',
                    url: route('module.result.destory', recordID),
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
                    ModuleResultListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREMODULERSLT'){
                axios({
                    method: 'post',
                    url: route('module.result.restore', recordID),
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
                    ModuleResultListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATMODULERSLT'){
                axios({
                    method: 'post',
                    url: route('module.result.update.status', recordID),
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
                    ModuleResultListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#ModuleResultListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATMODULERSLT');
            });
        });

        // Delete Course
        $('#ModuleResultListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEMODULERSLT');
            });
        });

        // Restore Course
        $('#ModuleResultListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREMODULERSLT');
            });
        });

        $('#moduleResultImportModal').on('click','#saveModuleResult',function(e) {
            e.preventDefault();
            $('#moduleResultImportModal .dropzone').get(0).dropzone.processQueue();
            moduleResultImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();