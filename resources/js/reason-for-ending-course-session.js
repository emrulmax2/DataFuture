import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var RsForEndCrsSListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-RSFENDCRSS").val() != "" ? $("#query-RSFENDCRSS").val() : "";
        let status = $("#status-RSFENDCRSS").val() != "" ? $("#status-RSFENDCRSS").val() : "";
        let tableContent = new Tabulator("#RsForEndCrsSListTable", {
            ajaxURL: route("rsendcrss.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editRsForEndCrsSModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-RSFENDCRSS").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-RSFENDCRSS").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-RSFENDCRSS").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Reason for ending course session",
            });
        });

        $("#tabulator-export-html-RSFENDCRSS").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-RSFENDCRSS").on("click", function (event) {
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
    if ($("#RsForEndCrsSListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'RsForEndCrsSListTable'){
                RsForEndCrsSListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormRSFENDCRSS() {
            RsForEndCrsSListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-RSFENDCRSS")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormRSFENDCRSS();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-RSFENDCRSS").on("click", function (event) {
            filterHTMLFormRSFENDCRSS();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-RSFENDCRSS").on("click", function (event) {
            $("#query-RSFENDCRSS").val("");
            $("#status-RSFENDCRSS").val("1");
            filterHTMLFormRSFENDCRSS();
        });

        const addRsForEndCrsSModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRsForEndCrsSModal"));
        const editRsForEndCrsSModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editRsForEndCrsSModal"));
        const rsForEndCrsSImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#rsForEndCrsSImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addRsForEndCrsSModalEl = document.getElementById('addRsForEndCrsSModal')
        addRsForEndCrsSModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addRsForEndCrsSModal .acc__input-error').html('');
            $('#addRsForEndCrsSModal .modal-body input:not([type="checkbox"])').val('');

            $('#addRsForEndCrsSModal input[name="is_hesa"]').prop('checked', false);
            $('#addRsForEndCrsSModal .hesa_code_area').fadeOut('fast', function(){
                $('#addRsForEndCrsSModal .hesa_code_area input').val('');
            });
            $('#addRsForEndCrsSModal input[name="is_df"]').prop('checked', false);
            $('#addRsForEndCrsSModal .df_code_area').fadeOut('fast', function(){
                $('#addRsForEndCrsSModal .df_code_area input').val('');
            })
            $('#addRsForEndCrsSModal input[name="active"]').prop('checked', true);
        });
        
        const editRsForEndCrsSModalEl = document.getElementById('editRsForEndCrsSModal')
        editRsForEndCrsSModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editRsForEndCrsSModal .acc__input-error').html('');
            $('#editRsForEndCrsSModal .modal-body input:not([type="checkbox"])').val('');
            $('#editRsForEndCrsSModal input[name="id"]').val('0');

            $('#editRsForEndCrsSModal input[name="is_hesa"]').prop('checked', false);
            $('#editRsForEndCrsSModal .hesa_code_area').fadeOut('fast', function(){
                $('#editRsForEndCrsSModal .hesa_code_area input').val('');
            });
            $('#editRsForEndCrsSModal input[name="is_df"]').prop('checked', false);
            $('#editRsForEndCrsSModal .df_code_area').fadeOut('fast', function(){
                $('#editRsForEndCrsSModal .df_code_area input').val('');
            })
            $('#editRsForEndCrsSModal input[name="active"]').prop('checked', false);
        });
        
        $('#addRsForEndCrsSForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addRsForEndCrsSForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addRsForEndCrsSForm .hesa_code_area input').val('');
                })
            }else{
                $('#addRsForEndCrsSForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addRsForEndCrsSForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addRsForEndCrsSForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addRsForEndCrsSForm .df_code_area').fadeIn('fast', function(){
                    $('#addRsForEndCrsSForm .df_code_area input').val('');
                })
            }else{
                $('#addRsForEndCrsSForm .df_code_area').fadeOut('fast', function(){
                    $('#addRsForEndCrsSForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editRsForEndCrsSForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editRsForEndCrsSForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editRsForEndCrsSForm .hesa_code_area input').val('');
                })
            }else{
                $('#editRsForEndCrsSForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editRsForEndCrsSForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editRsForEndCrsSForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editRsForEndCrsSForm .df_code_area').fadeIn('fast', function(){
                    $('#editRsForEndCrsSForm .df_code_area input').val('');
                })
            }else{
                $('#editRsForEndCrsSForm .df_code_area').fadeOut('fast', function(){
                    $('#editRsForEndCrsSForm .df_code_area input').val('');
                })
            }
        })

        $('#addRsForEndCrsSForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addRsForEndCrsSForm');
        
            document.querySelector('#saveRsForEndCrsS').setAttribute('disabled', 'disabled');
            document.querySelector("#saveRsForEndCrsS svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('rsendcrss.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveRsForEndCrsS').removeAttribute('disabled');
                document.querySelector("#saveRsForEndCrsS svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addRsForEndCrsSModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Item Successfully inserted.');
                    });     
                }
                RsForEndCrsSListTable.init();
            }).catch(error => {
                document.querySelector('#saveRsForEndCrsS').removeAttribute('disabled');
                document.querySelector("#saveRsForEndCrsS svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addRsForEndCrsSForm .${key}`).addClass('border-danger');
                            $(`#addRsForEndCrsSForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#RsForEndCrsSListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("rsendcrss.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editRsForEndCrsSModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editRsForEndCrsSModal input[name="is_hesa"]').prop('checked', true);
                            $('#editRsForEndCrsSModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editRsForEndCrsSModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editRsForEndCrsSModal input[name="is_hesa"]').prop('checked', false);
                            $('#editRsForEndCrsSModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editRsForEndCrsSModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editRsForEndCrsSModal input[name="is_df"]').prop('checked', true);
                            $('#editRsForEndCrsSModal .df_code_area').fadeIn('fast', function(){
                                $('#editRsForEndCrsSModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editRsForEndCrsSModal input[name="is_df"]').prop('checked', false);
                            $('#editRsForEndCrsSModal .df_code_area').fadeOut('fast', function(){
                                $('#editRsForEndCrsSModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editRsForEndCrsSModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editRsForEndCrsSModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editRsForEndCrsSModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editRsForEndCrsSForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editRsForEndCrsSForm input[name="id"]').val();
            const form = document.getElementById("editRsForEndCrsSForm");

            document.querySelector('#updateRsForEndCrsS').setAttribute('disabled', 'disabled');
            document.querySelector('#updateRsForEndCrsS svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("rsendcrss.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateRsForEndCrsS").removeAttribute("disabled");
                    document.querySelector("#updateRsForEndCrsS svg").style.cssText = "display: none;";
                    editRsForEndCrsSModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('data successfully updated.');
                    });
                }
                RsForEndCrsSListTable.init();
            }).catch((error) => {
                document.querySelector("#updateRsForEndCrsS").removeAttribute("disabled");
                document.querySelector("#updateRsForEndCrsS svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editRsForEndCrsSForm .${key}`).addClass('border-danger')
                            $(`#editRsForEndCrsSForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editRsForEndCrsSModal.hide();

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
            if(action == 'DELETERSFENDCRSS'){
                axios({
                    method: 'delete',
                    url: route('rsendcrss.destory', recordID),
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
                    RsForEndCrsSListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORERSFENDCRSS'){
                axios({
                    method: 'post',
                    url: route('rsendcrss.restore', recordID),
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
                    RsForEndCrsSListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATRSFENDCRSS'){
                axios({
                    method: 'post',
                    url: route('rsendcrss.update.status', recordID),
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
                    RsForEndCrsSListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#RsForEndCrsSListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATRSFENDCRSS');
            });
        });

        // Delete Course
        $('#RsForEndCrsSListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETERSFENDCRSS');
            });
        });

        // Restore Course
        $('#RsForEndCrsSListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORERSFENDCRSS');
            });
        });

        $('#rsForEndCrsSImportModal').on('click','#saveRsForEndCrsS',function(e) {
            e.preventDefault();
            $('#rsForEndCrsSImportModal .dropzone').get(0).dropzone.processQueue();
            rsForEndCrsSImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();