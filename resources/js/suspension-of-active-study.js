import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var SuspensionOfActiveStudyListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-SPSNOACTVSTD").val() != "" ? $("#query-SPSNOACTVSTD").val() : "";
        let status = $("#status-SPSNOACTVSTD").val() != "" ? $("#status-SPSNOACTVSTD").val() : "";
        let tableContent = new Tabulator("#SuspensionOfActiveStudyListTable", {
            ajaxURL: route("suspension.of.active.study.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editSuspensionOfActiveStudyModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-SPSNOACTVSTD").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-SPSNOACTVSTD").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-SPSNOACTVSTD").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-SPSNOACTVSTD").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-SPSNOACTVSTD").on("click", function (event) {
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
    if ($("#SuspensionOfActiveStudyListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'SuspensionOfActiveStudyListTable'){
                SuspensionOfActiveStudyListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormSPSNOACTVSTD() {
            SuspensionOfActiveStudyListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-SPSNOACTVSTD")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormSPSNOACTVSTD();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-SPSNOACTVSTD").on("click", function (event) {
            filterHTMLFormSPSNOACTVSTD();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-SPSNOACTVSTD").on("click", function (event) {
            $("#query-SPSNOACTVSTD").val("");
            $("#status-SPSNOACTVSTD").val("1");
            filterHTMLFormSPSNOACTVSTD();
        });

        const addSuspensionOfActiveStudyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addSuspensionOfActiveStudyModal"));
        const editSuspensionOfActiveStudyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editSuspensionOfActiveStudyModal"));
        const suspensionOfActiveStudyImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#suspensionOfActiveStudyImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addSuspensionOfActiveStudyModalEl = document.getElementById('addSuspensionOfActiveStudyModal')
        addSuspensionOfActiveStudyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addSuspensionOfActiveStudyModal .acc__input-error').html('');
            $('#addSuspensionOfActiveStudyModal .modal-body input:not([type="checkbox"])').val('');

            $('#addSuspensionOfActiveStudyModal input[name="is_hesa"]').prop('checked', false);
            $('#addSuspensionOfActiveStudyModal .hesa_code_area').fadeOut('fast', function(){
                $('#addSuspensionOfActiveStudyModal .hesa_code_area input').val('');
            });
            $('#addSuspensionOfActiveStudyModal input[name="is_df"]').prop('checked', false);
            $('#addSuspensionOfActiveStudyModal .df_code_area').fadeOut('fast', function(){
                $('#addSuspensionOfActiveStudyModal .df_code_area input').val('');
            })
            $('#addSuspensionOfActiveStudyModal input[name="active"]').prop('checked', true);
        });
        
        const editSuspensionOfActiveStudyModalEl = document.getElementById('editSuspensionOfActiveStudyModal')
        editSuspensionOfActiveStudyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editSuspensionOfActiveStudyModal .acc__input-error').html('');
            $('#editSuspensionOfActiveStudyModal .modal-body input:not([type="checkbox"])').val('');
            $('#editSuspensionOfActiveStudyModal input[name="id"]').val('0');

            $('#editSuspensionOfActiveStudyModal input[name="is_hesa"]').prop('checked', false);
            $('#editSuspensionOfActiveStudyModal .hesa_code_area').fadeOut('fast', function(){
                $('#editSuspensionOfActiveStudyModal .hesa_code_area input').val('');
            });
            $('#editSuspensionOfActiveStudyModal input[name="is_df"]').prop('checked', false);
            $('#editSuspensionOfActiveStudyModal .df_code_area').fadeOut('fast', function(){
                $('#editSuspensionOfActiveStudyModal .df_code_area input').val('');
            })
            $('#editSuspensionOfActiveStudyModal input[name="active"]').prop('checked', false);
        });
        
        $('#addSuspensionOfActiveStudyForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSuspensionOfActiveStudyForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addSuspensionOfActiveStudyForm .hesa_code_area input').val('');
                })
            }else{
                $('#addSuspensionOfActiveStudyForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addSuspensionOfActiveStudyForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addSuspensionOfActiveStudyForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addSuspensionOfActiveStudyForm .df_code_area').fadeIn('fast', function(){
                    $('#addSuspensionOfActiveStudyForm .df_code_area input').val('');
                })
            }else{
                $('#addSuspensionOfActiveStudyForm .df_code_area').fadeOut('fast', function(){
                    $('#addSuspensionOfActiveStudyForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editSuspensionOfActiveStudyForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSuspensionOfActiveStudyForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editSuspensionOfActiveStudyForm .hesa_code_area input').val('');
                })
            }else{
                $('#editSuspensionOfActiveStudyForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editSuspensionOfActiveStudyForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editSuspensionOfActiveStudyForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editSuspensionOfActiveStudyForm .df_code_area').fadeIn('fast', function(){
                    $('#editSuspensionOfActiveStudyForm .df_code_area input').val('');
                })
            }else{
                $('#editSuspensionOfActiveStudyForm .df_code_area').fadeOut('fast', function(){
                    $('#editSuspensionOfActiveStudyForm .df_code_area input').val('');
                })
            }
        })

        $('#addSuspensionOfActiveStudyForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addSuspensionOfActiveStudyForm');
        
            document.querySelector('#saveSuspensionOfActiveStudy').setAttribute('disabled', 'disabled');
            document.querySelector("#saveSuspensionOfActiveStudy svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('suspension.of.active.study.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveSuspensionOfActiveStudy').removeAttribute('disabled');
                document.querySelector("#saveSuspensionOfActiveStudy svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addSuspensionOfActiveStudyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                SuspensionOfActiveStudyListTable.init();
            }).catch(error => {
                document.querySelector('#saveSuspensionOfActiveStudy').removeAttribute('disabled');
                document.querySelector("#saveSuspensionOfActiveStudy svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addSuspensionOfActiveStudyForm .${key}`).addClass('border-danger');
                            $(`#addSuspensionOfActiveStudyForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#SuspensionOfActiveStudyListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("suspension.of.active.study.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editSuspensionOfActiveStudyModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editSuspensionOfActiveStudyModal input[name="is_hesa"]').prop('checked', true);
                            $('#editSuspensionOfActiveStudyModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editSuspensionOfActiveStudyModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editSuspensionOfActiveStudyModal input[name="is_hesa"]').prop('checked', false);
                            $('#editSuspensionOfActiveStudyModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editSuspensionOfActiveStudyModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editSuspensionOfActiveStudyModal input[name="is_df"]').prop('checked', true);
                            $('#editSuspensionOfActiveStudyModal .df_code_area').fadeIn('fast', function(){
                                $('#editSuspensionOfActiveStudyModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editSuspensionOfActiveStudyModal input[name="is_df"]').prop('checked', false);
                            $('#editSuspensionOfActiveStudyModal .df_code_area').fadeOut('fast', function(){
                                $('#editSuspensionOfActiveStudyModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editSuspensionOfActiveStudyModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editSuspensionOfActiveStudyModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editSuspensionOfActiveStudyModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editSuspensionOfActiveStudyForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editSuspensionOfActiveStudyForm input[name="id"]').val();
            const form = document.getElementById("editSuspensionOfActiveStudyForm");

            document.querySelector('#updateSuspensionOfActiveStudy').setAttribute('disabled', 'disabled');
            document.querySelector('#updateSuspensionOfActiveStudy svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("suspension.of.active.study.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateSuspensionOfActiveStudy").removeAttribute("disabled");
                    document.querySelector("#updateSuspensionOfActiveStudy svg").style.cssText = "display: none;";
                    editSuspensionOfActiveStudyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                SuspensionOfActiveStudyListTable.init();
            }).catch((error) => {
                document.querySelector("#updateSuspensionOfActiveStudy").removeAttribute("disabled");
                document.querySelector("#updateSuspensionOfActiveStudy svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editSuspensionOfActiveStudyForm .${key}`).addClass('border-danger')
                            $(`#editSuspensionOfActiveStudyForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editSuspensionOfActiveStudyModal.hide();

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
            if(action == 'DELETESPSNOACTVSTD'){
                axios({
                    method: 'delete',
                    url: route('suspension.of.active.study.destory', recordID),
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
                    SuspensionOfActiveStudyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORESPSNOACTVSTD'){
                axios({
                    method: 'post',
                    url: route('suspension.of.active.study.restore', recordID),
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
                    SuspensionOfActiveStudyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATSPSNOACTVSTD'){
                axios({
                    method: 'post',
                    url: route('suspension.of.active.study.update.status', recordID),
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
                    SuspensionOfActiveStudyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#SuspensionOfActiveStudyListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATSPSNOACTVSTD');
            });
        });

        // Delete Course
        $('#SuspensionOfActiveStudyListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETESPSNOACTVSTD');
            });
        });

        // Restore Course
        $('#SuspensionOfActiveStudyListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORESPSNOACTVSTD');
            });
        });

        $('#suspensionOfActiveStudyImportModal').on('click','#saveSuspensionOfActiveStudy',function(e) {
            e.preventDefault();
            $('#suspensionOfActiveStudyImportModal .dropzone').get(0).dropzone.processQueue();
            suspensionOfActiveStudyImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();