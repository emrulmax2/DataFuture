import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var FundingLengthListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-FUNDINGLEN").val() != "" ? $("#query-FUNDINGLEN").val() : "";
        let status = $("#status-FUNDINGLEN").val() != "" ? $("#status-FUNDINGLEN").val() : "";
        let tableContent = new Tabulator("#FundingLengthListTable", {
            ajaxURL: route("funding.length.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editFundingLengthModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-FUNDINGLEN").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-FUNDINGLEN").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-FUNDINGLEN").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-FUNDINGLEN").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-FUNDINGLEN").on("click", function (event) {
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
    if ($("#FundingLengthListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'FundingLengthListTable'){
                FundingLengthListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormFUNDINGLEN() {
            FundingLengthListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-FUNDINGLEN")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormFUNDINGLEN();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-FUNDINGLEN").on("click", function (event) {
            filterHTMLFormFUNDINGLEN();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-FUNDINGLEN").on("click", function (event) {
            $("#query-FUNDINGLEN").val("");
            $("#status-FUNDINGLEN").val("1");
            filterHTMLFormFUNDINGLEN();
        });

        const addFundingLengthModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addFundingLengthModal"));
        const editFundingLengthModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFundingLengthModal"));
        const fundingLengthImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#fundingLengthImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addFundingLengthModalEl = document.getElementById('addFundingLengthModal')
        addFundingLengthModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addFundingLengthModal .acc__input-error').html('');
            $('#addFundingLengthModal .modal-body input:not([type="checkbox"])').val('');

            $('#addFundingLengthModal input[name="is_hesa"]').prop('checked', false);
            $('#addFundingLengthModal .hesa_code_area').fadeOut('fast', function(){
                $('#addFundingLengthModal .hesa_code_area input').val('');
            });
            $('#addFundingLengthModal input[name="is_df"]').prop('checked', false);
            $('#addFundingLengthModal .df_code_area').fadeOut('fast', function(){
                $('#addFundingLengthModal .df_code_area input').val('');
            })
            $('#addFundingLengthModal input[name="active"]').prop('checked', true);
        });
        
        const editFundingLengthModalEl = document.getElementById('editFundingLengthModal')
        editFundingLengthModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editFundingLengthModal .acc__input-error').html('');
            $('#editFundingLengthModal .modal-body input:not([type="checkbox"])').val('');
            $('#editFundingLengthModal input[name="id"]').val('0');

            $('#editFundingLengthModal input[name="is_hesa"]').prop('checked', false);
            $('#editFundingLengthModal .hesa_code_area').fadeOut('fast', function(){
                $('#editFundingLengthModal .hesa_code_area input').val('');
            });
            $('#editFundingLengthModal input[name="is_df"]').prop('checked', false);
            $('#editFundingLengthModal .df_code_area').fadeOut('fast', function(){
                $('#editFundingLengthModal .df_code_area input').val('');
            })
            $('#editFundingLengthModal input[name="active"]').prop('checked', false);
        });
        
        $('#addFundingLengthForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addFundingLengthForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addFundingLengthForm .hesa_code_area input').val('');
                })
            }else{
                $('#addFundingLengthForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addFundingLengthForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addFundingLengthForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addFundingLengthForm .df_code_area').fadeIn('fast', function(){
                    $('#addFundingLengthForm .df_code_area input').val('');
                })
            }else{
                $('#addFundingLengthForm .df_code_area').fadeOut('fast', function(){
                    $('#addFundingLengthForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editFundingLengthForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editFundingLengthForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editFundingLengthForm .hesa_code_area input').val('');
                })
            }else{
                $('#editFundingLengthForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editFundingLengthForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editFundingLengthForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editFundingLengthForm .df_code_area').fadeIn('fast', function(){
                    $('#editFundingLengthForm .df_code_area input').val('');
                })
            }else{
                $('#editFundingLengthForm .df_code_area').fadeOut('fast', function(){
                    $('#editFundingLengthForm .df_code_area input').val('');
                })
            }
        })

        $('#addFundingLengthForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addFundingLengthForm');
        
            document.querySelector('#saveFundingLength').setAttribute('disabled', 'disabled');
            document.querySelector("#saveFundingLength svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('funding.length.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveFundingLength').removeAttribute('disabled');
                document.querySelector("#saveFundingLength svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addFundingLengthModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                FundingLengthListTable.init();
            }).catch(error => {
                document.querySelector('#saveFundingLength').removeAttribute('disabled');
                document.querySelector("#saveFundingLength svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addFundingLengthForm .${key}`).addClass('border-danger');
                            $(`#addFundingLengthForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#FundingLengthListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("funding.length.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editFundingLengthModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editFundingLengthModal input[name="is_hesa"]').prop('checked', true);
                            $('#editFundingLengthModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editFundingLengthModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editFundingLengthModal input[name="is_hesa"]').prop('checked', false);
                            $('#editFundingLengthModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editFundingLengthModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editFundingLengthModal input[name="is_df"]').prop('checked', true);
                            $('#editFundingLengthModal .df_code_area').fadeIn('fast', function(){
                                $('#editFundingLengthModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editFundingLengthModal input[name="is_df"]').prop('checked', false);
                            $('#editFundingLengthModal .df_code_area').fadeOut('fast', function(){
                                $('#editFundingLengthModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editFundingLengthModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editFundingLengthModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editFundingLengthModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editFundingLengthForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editFundingLengthForm input[name="id"]').val();
            const form = document.getElementById("editFundingLengthForm");

            document.querySelector('#updateFundingLength').setAttribute('disabled', 'disabled');
            document.querySelector('#updateFundingLength svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("funding.length.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateFundingLength").removeAttribute("disabled");
                    document.querySelector("#updateFundingLength svg").style.cssText = "display: none;";
                    editFundingLengthModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                FundingLengthListTable.init();
            }).catch((error) => {
                document.querySelector("#updateFundingLength").removeAttribute("disabled");
                document.querySelector("#updateFundingLength svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editFundingLengthForm .${key}`).addClass('border-danger')
                            $(`#editFundingLengthForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editFundingLengthModal.hide();

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
            if(action == 'DELETEFUNDINGLEN'){
                axios({
                    method: 'delete',
                    url: route('funding.length.destory', recordID),
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
                    FundingLengthListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREFUNDINGLEN'){
                axios({
                    method: 'post',
                    url: route('funding.length.restore', recordID),
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
                    FundingLengthListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATFUNDINGLEN'){
                axios({
                    method: 'post',
                    url: route('funding.length.update.status', recordID),
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
                    FundingLengthListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#FundingLengthListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATFUNDINGLEN');
            });
        });

        // Delete Course
        $('#FundingLengthListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEFUNDINGLEN');
            });
        });

        // Restore Course
        $('#FundingLengthListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREFUNDINGLEN');
            });
        });

        $('#fundingLengthImportModal').on('click','#saveFundingLength',function(e) {
            e.preventDefault();
            $('#fundingLengthImportModal .dropzone').get(0).dropzone.processQueue();
            fundingLengthImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();