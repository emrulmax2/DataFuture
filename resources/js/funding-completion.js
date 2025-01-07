import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var FundingCompletionListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-FUNDINGCOM").val() != "" ? $("#query-FUNDINGCOM").val() : "";
        let status = $("#status-FUNDINGCOM").val() != "" ? $("#status-FUNDINGCOM").val() : "";
        let tableContent = new Tabulator("#FundingCompletionListTable", {
            ajaxURL: route("funding.completion.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editFundingCompletionModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-FUNDINGCOM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-FUNDINGCOM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-FUNDINGCOM").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Completion Details",
            });
        });

        $("#tabulator-export-html-FUNDINGCOM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-FUNDINGCOM").on("click", function (event) {
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
    if ($("#FundingCompletionListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'FundingCompletionListTable'){
                FundingCompletionListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormFUNDINGCOM() {
            FundingCompletionListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-FUNDINGCOM")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormFUNDINGCOM();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-FUNDINGCOM").on("click", function (event) {
            filterHTMLFormFUNDINGCOM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-FUNDINGCOM").on("click", function (event) {
            $("#query-FUNDINGCOM").val("");
            $("#status-FUNDINGCOM").val("1");
            filterHTMLFormFUNDINGCOM();
        });

        const addFundingCompletionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addFundingCompletionModal"));
        const editFundingCompletionModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFundingCompletionModal"));
        const fundingCompletionImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#fundingCompletionImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addFundingCompletionModalEl = document.getElementById('addFundingCompletionModal')
        addFundingCompletionModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addFundingCompletionModal .acc__input-error').html('');
            $('#addFundingCompletionModal .modal-body input:not([type="checkbox"])').val('');

            $('#addFundingCompletionModal input[name="is_hesa"]').prop('checked', false);
            $('#addFundingCompletionModal .hesa_code_area').fadeOut('fast', function(){
                $('#addFundingCompletionModal .hesa_code_area input').val('');
            });
            $('#addFundingCompletionModal input[name="is_df"]').prop('checked', false);
            $('#addFundingCompletionModal .df_code_area').fadeOut('fast', function(){
                $('#addFundingCompletionModal .df_code_area input').val('');
            })
            $('#addFundingCompletionModal input[name="active"]').prop('checked', true);
        });
        
        const editFundingCompletionModalEl = document.getElementById('editFundingCompletionModal')
        editFundingCompletionModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editFundingCompletionModal .acc__input-error').html('');
            $('#editFundingCompletionModal .modal-body input:not([type="checkbox"])').val('');
            $('#editFundingCompletionModal input[name="id"]').val('0');

            $('#editFundingCompletionModal input[name="is_hesa"]').prop('checked', false);
            $('#editFundingCompletionModal .hesa_code_area').fadeOut('fast', function(){
                $('#editFundingCompletionModal .hesa_code_area input').val('');
            });
            $('#editFundingCompletionModal input[name="is_df"]').prop('checked', false);
            $('#editFundingCompletionModal .df_code_area').fadeOut('fast', function(){
                $('#editFundingCompletionModal .df_code_area input').val('');
            })
            $('#editFundingCompletionModal input[name="active"]').prop('checked', false);
        });
        
        $('#addFundingCompletionForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addFundingCompletionForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addFundingCompletionForm .hesa_code_area input').val('');
                })
            }else{
                $('#addFundingCompletionForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addFundingCompletionForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addFundingCompletionForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addFundingCompletionForm .df_code_area').fadeIn('fast', function(){
                    $('#addFundingCompletionForm .df_code_area input').val('');
                })
            }else{
                $('#addFundingCompletionForm .df_code_area').fadeOut('fast', function(){
                    $('#addFundingCompletionForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editFundingCompletionForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editFundingCompletionForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editFundingCompletionForm .hesa_code_area input').val('');
                })
            }else{
                $('#editFundingCompletionForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editFundingCompletionForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editFundingCompletionForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editFundingCompletionForm .df_code_area').fadeIn('fast', function(){
                    $('#editFundingCompletionForm .df_code_area input').val('');
                })
            }else{
                $('#editFundingCompletionForm .df_code_area').fadeOut('fast', function(){
                    $('#editFundingCompletionForm .df_code_area input').val('');
                })
            }
        })

        $('#addFundingCompletionForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addFundingCompletionForm');
        
            document.querySelector('#saveFundingCompletion').setAttribute('disabled', 'disabled');
            document.querySelector("#saveFundingCompletion svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('funding.completion.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveFundingCompletion').removeAttribute('disabled');
                document.querySelector("#saveFundingCompletion svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addFundingCompletionModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                FundingCompletionListTable.init();
            }).catch(error => {
                document.querySelector('#saveFundingCompletion').removeAttribute('disabled');
                document.querySelector("#saveFundingCompletion svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addFundingCompletionForm .${key}`).addClass('border-danger');
                            $(`#addFundingCompletionForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#FundingCompletionListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("funding.completion.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editFundingCompletionModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editFundingCompletionModal input[name="is_hesa"]').prop('checked', true);
                            $('#editFundingCompletionModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editFundingCompletionModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editFundingCompletionModal input[name="is_hesa"]').prop('checked', false);
                            $('#editFundingCompletionModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editFundingCompletionModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editFundingCompletionModal input[name="is_df"]').prop('checked', true);
                            $('#editFundingCompletionModal .df_code_area').fadeIn('fast', function(){
                                $('#editFundingCompletionModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editFundingCompletionModal input[name="is_df"]').prop('checked', false);
                            $('#editFundingCompletionModal .df_code_area').fadeOut('fast', function(){
                                $('#editFundingCompletionModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editFundingCompletionModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editFundingCompletionModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editFundingCompletionModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editFundingCompletionForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editFundingCompletionForm input[name="id"]').val();
            const form = document.getElementById("editFundingCompletionForm");

            document.querySelector('#updateFundingCompletion').setAttribute('disabled', 'disabled');
            document.querySelector('#updateFundingCompletion svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("funding.completion.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateFundingCompletion").removeAttribute("disabled");
                    document.querySelector("#updateFundingCompletion svg").style.cssText = "display: none;";
                    editFundingCompletionModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                FundingCompletionListTable.init();
            }).catch((error) => {
                document.querySelector("#updateFundingCompletion").removeAttribute("disabled");
                document.querySelector("#updateFundingCompletion svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editFundingCompletionForm .${key}`).addClass('border-danger')
                            $(`#editFundingCompletionForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editFundingCompletionModal.hide();

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
            if(action == 'DELETEFUNDINGCOM'){
                axios({
                    method: 'delete',
                    url: route('funding.completion.destory', recordID),
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
                    FundingCompletionListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREFUNDINGCOM'){
                axios({
                    method: 'post',
                    url: route('funding.completion.restore', recordID),
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
                    FundingCompletionListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATFUNDINGCOM'){
                axios({
                    method: 'post',
                    url: route('funding.completion.update.status', recordID),
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
                    FundingCompletionListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#FundingCompletionListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATFUNDINGCOM');
            });
        });

        // Delete Course
        $('#FundingCompletionListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEFUNDINGCOM');
            });
        });

        // Restore Course
        $('#FundingCompletionListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREFUNDINGCOM');
            });
        });

        $('#fundingCompletionImportModal').on('click','#saveFundingCompletion',function(e) {
            e.preventDefault();
            $('#fundingCompletionImportModal .dropzone').get(0).dropzone.processQueue();
            fundingCompletionImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();