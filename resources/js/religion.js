import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var relgnListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-RELGN").val() != "" ? $("#query-RELGN").val() : "";
        let status = $("#status-RELGN").val() != "" ? $("#status-RELGN").val() : "";
        let tableContent = new Tabulator("#relgnListTable", {
            ajaxURL: route("religion.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editRelgnModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-RELGN").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-RELGN").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-RELGN").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Religion Details",
            });
        });

        $("#tabulator-export-html-RELGN").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-RELGN").on("click", function (event) {
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
    if ($("#relgnListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'relgnListTable'){
                relgnListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormRELGN() {
            relgnListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-RELGN")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormRELGN();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-RELGN").on("click", function (event) {
            filterHTMLFormRELGN();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-RELGN").on("click", function (event) {
            $("#query-RELGN").val("");
            $("#status-RELGN").val("1");
            filterHTMLFormRELGN();
        });

        const addRelgnModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRelgnModal"));
        const editRelgnModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editRelgnModal"));
        const religionImportModal = tailwind.Modal.getOrCreateInstance("#religionImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addRelgnModalEl = document.getElementById('addRelgnModal')
        addRelgnModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addRelgnModal .acc__input-error').html('');
            $('#addRelgnModal .modal-body input:not([type="checkbox"])').val('');

            $('#addRelgnModal input[name="is_hesa"]').prop('checked', false);
            $('#addRelgnModal .hesa_code_area').fadeOut('fast', function(){
                $('#addRelgnModal .hesa_code_area input').val('');
            });
            $('#addRelgnModal input[name="is_df"]').prop('checked', false);
            $('#addRelgnModal .df_code_area').fadeOut('fast', function(){
                $('#addRelgnModal .df_code_area input').val('');
            })
            $('#addRelgnModal input[name="active"]').prop('checked', true);
        });
        
        const editRelgnModalEl = document.getElementById('editRelgnModal')
        editRelgnModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editRelgnModal .acc__input-error').html('');
            $('#editRelgnModal .modal-body input:not([type="checkbox"])').val('');
            $('#editRelgnModal input[name="id"]').val('0');

            $('#editRelgnModal input[name="is_hesa"]').prop('checked', false);
            $('#editRelgnModal .hesa_code_area').fadeOut('fast', function(){
                $('#editRelgnModal .hesa_code_area input').val('');
            });
            $('#editRelgnModal input[name="is_df"]').prop('checked', false);
            $('#editRelgnModal .df_code_area').fadeOut('fast', function(){
                $('#editRelgnModal .df_code_area input').val('');
            })
            $('#editRelgnModal input[name="active"]').prop('checked', false);
        });
        
        $('#addRelgnForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addRelgnForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addRelgnForm .hesa_code_area input').val('');
                })
            }else{
                $('#addRelgnForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addRelgnForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addRelgnForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addRelgnForm .df_code_area').fadeIn('fast', function(){
                    $('#addRelgnForm .df_code_area input').val('');
                })
            }else{
                $('#addRelgnForm .df_code_area').fadeOut('fast', function(){
                    $('#addRelgnForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editRelgnForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editRelgnForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editRelgnForm .hesa_code_area input').val('');
                })
            }else{
                $('#editRelgnForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editRelgnForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editRelgnForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editRelgnForm .df_code_area').fadeIn('fast', function(){
                    $('#editRelgnForm .df_code_area input').val('');
                })
            }else{
                $('#editRelgnForm .df_code_area').fadeOut('fast', function(){
                    $('#editRelgnForm .df_code_area input').val('');
                })
            }
        })

        $('#addRelgnForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addRelgnForm');
        
            document.querySelector('#saveRelgn').setAttribute('disabled', 'disabled');
            document.querySelector("#saveRelgn svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('religion.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveRelgn').removeAttribute('disabled');
                document.querySelector("#saveRelgn svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addRelgnModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                relgnListTable.init();
            }).catch(error => {
                document.querySelector('#saveRelgn').removeAttribute('disabled');
                document.querySelector("#saveRelgn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addRelgnForm .${key}`).addClass('border-danger');
                            $(`#addRelgnForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#relgnListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("religion.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editRelgnModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editRelgnModal input[name="is_hesa"]').prop('checked', true);
                            $('#editRelgnModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editRelgnModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editRelgnModal input[name="is_hesa"]').prop('checked', false);
                            $('#editRelgnModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editRelgnModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editRelgnModal input[name="is_df"]').prop('checked', true);
                            $('#editRelgnModal .df_code_area').fadeIn('fast', function(){
                                $('#editRelgnModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editRelgnModal input[name="is_df"]').prop('checked', false);
                            $('#editRelgnModal .df_code_area').fadeOut('fast', function(){
                                $('#editRelgnModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editRelgnModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editRelgnModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editRelgnModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editRelgnForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editRelgnForm input[name="id"]').val();
            const form = document.getElementById("editRelgnForm");

            document.querySelector('#updateRelgn').setAttribute('disabled', 'disabled');
            document.querySelector('#updateRelgn svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("religion.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateRelgn").removeAttribute("disabled");
                    document.querySelector("#updateRelgn svg").style.cssText = "display: none;";
                    editRelgnModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                relgnListTable.init();
            }).catch((error) => {
                document.querySelector("#updateRelgn").removeAttribute("disabled");
                document.querySelector("#updateRelgn svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editRelgnForm .${key}`).addClass('border-danger')
                            $(`#editRelgnForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editRelgnModal.hide();

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
            if(action == 'DELETERELGN'){
                axios({
                    method: 'delete',
                    url: route('religion.destory', recordID),
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
                    relgnListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORERELGN'){
                axios({
                    method: 'post',
                    url: route('religion.restore', recordID),
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
                    relgnListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATRELGN'){
                axios({
                    method: 'post',
                    url: route('religion.update.status', recordID),
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
                    relgnListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#relgnListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATRELGN');
            });
        });


        // Delete Course
        $('#relgnListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETERELGN');
            });
        });

        // Restore Course
        $('#relgnListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORERELGN');
            });
        });

        $('#religionImportModal').on('click','#saveReligion',function(e) {
            e.preventDefault();
            $('#religionImportModal .dropzone').get(0).dropzone.processQueue();
            religionImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 3000);           
        });
    }
})();