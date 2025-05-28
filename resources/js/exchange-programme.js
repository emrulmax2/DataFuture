import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var ExchangeProgrammeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-EXCHNGPRGM").val() != "" ? $("#query-EXCHNGPRGM").val() : "";
        let status = $("#status-EXCHNGPRGM").val() != "" ? $("#status-EXCHNGPRGM").val() : "";
        let tableContent = new Tabulator("#ExchangeProgrammeListTable", {
            ajaxURL: route("exchange.programme.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editExchangeProgrammeModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-EXCHNGPRGM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EXCHNGPRGM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EXCHNGPRGM").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-EXCHNGPRGM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EXCHNGPRGM").on("click", function (event) {
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
    if ($("#ExchangeProgrammeListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'ExchangeProgrammeListTable'){
                ExchangeProgrammeListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormEXCHNGPRGM() {
            ExchangeProgrammeListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-EXCHNGPRGM")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormEXCHNGPRGM();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-EXCHNGPRGM").on("click", function (event) {
            filterHTMLFormEXCHNGPRGM();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EXCHNGPRGM").on("click", function (event) {
            $("#query-EXCHNGPRGM").val("");
            $("#status-EXCHNGPRGM").val("1");
            filterHTMLFormEXCHNGPRGM();
        });

        const addExchangeProgrammeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addExchangeProgrammeModal"));
        const editExchangeProgrammeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editExchangeProgrammeModal"));
        const exchangeProgrammeImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#exchangeProgrammeImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addExchangeProgrammeModalEl = document.getElementById('addExchangeProgrammeModal')
        addExchangeProgrammeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addExchangeProgrammeModal .acc__input-error').html('');
            $('#addExchangeProgrammeModal .modal-body input:not([type="checkbox"])').val('');

            $('#addExchangeProgrammeModal input[name="is_hesa"]').prop('checked', false);
            $('#addExchangeProgrammeModal .hesa_code_area').fadeOut('fast', function(){
                $('#addExchangeProgrammeModal .hesa_code_area input').val('');
            });
            $('#addExchangeProgrammeModal input[name="is_df"]').prop('checked', false);
            $('#addExchangeProgrammeModal .df_code_area').fadeOut('fast', function(){
                $('#addExchangeProgrammeModal .df_code_area input').val('');
            })
            $('#addExchangeProgrammeModal input[name="active"]').prop('checked', true);
        });
        
        const editExchangeProgrammeModalEl = document.getElementById('editExchangeProgrammeModal')
        editExchangeProgrammeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editExchangeProgrammeModal .acc__input-error').html('');
            $('#editExchangeProgrammeModal .modal-body input:not([type="checkbox"])').val('');
            $('#editExchangeProgrammeModal input[name="id"]').val('0');

            $('#editExchangeProgrammeModal input[name="is_hesa"]').prop('checked', false);
            $('#editExchangeProgrammeModal .hesa_code_area').fadeOut('fast', function(){
                $('#editExchangeProgrammeModal .hesa_code_area input').val('');
            });
            $('#editExchangeProgrammeModal input[name="is_df"]').prop('checked', false);
            $('#editExchangeProgrammeModal .df_code_area').fadeOut('fast', function(){
                $('#editExchangeProgrammeModal .df_code_area input').val('');
            })
            $('#editExchangeProgrammeModal input[name="active"]').prop('checked', false);
        });
        
        $('#addExchangeProgrammeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addExchangeProgrammeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addExchangeProgrammeForm .hesa_code_area input').val('');
                })
            }else{
                $('#addExchangeProgrammeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addExchangeProgrammeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addExchangeProgrammeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addExchangeProgrammeForm .df_code_area').fadeIn('fast', function(){
                    $('#addExchangeProgrammeForm .df_code_area input').val('');
                })
            }else{
                $('#addExchangeProgrammeForm .df_code_area').fadeOut('fast', function(){
                    $('#addExchangeProgrammeForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editExchangeProgrammeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editExchangeProgrammeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editExchangeProgrammeForm .hesa_code_area input').val('');
                })
            }else{
                $('#editExchangeProgrammeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editExchangeProgrammeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editExchangeProgrammeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editExchangeProgrammeForm .df_code_area').fadeIn('fast', function(){
                    $('#editExchangeProgrammeForm .df_code_area input').val('');
                })
            }else{
                $('#editExchangeProgrammeForm .df_code_area').fadeOut('fast', function(){
                    $('#editExchangeProgrammeForm .df_code_area input').val('');
                })
            }
        })

        $('#addExchangeProgrammeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addExchangeProgrammeForm');
        
            document.querySelector('#saveExchangeProgramme').setAttribute('disabled', 'disabled');
            document.querySelector("#saveExchangeProgramme svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('exchange.programme.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveExchangeProgramme').removeAttribute('disabled');
                document.querySelector("#saveExchangeProgramme svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addExchangeProgrammeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                ExchangeProgrammeListTable.init();
            }).catch(error => {
                document.querySelector('#saveExchangeProgramme').removeAttribute('disabled');
                document.querySelector("#saveExchangeProgramme svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addExchangeProgrammeForm .${key}`).addClass('border-danger');
                            $(`#addExchangeProgrammeForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#ExchangeProgrammeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("exchange.programme.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editExchangeProgrammeModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editExchangeProgrammeModal input[name="is_hesa"]').prop('checked', true);
                            $('#editExchangeProgrammeModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editExchangeProgrammeModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editExchangeProgrammeModal input[name="is_hesa"]').prop('checked', false);
                            $('#editExchangeProgrammeModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editExchangeProgrammeModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editExchangeProgrammeModal input[name="is_df"]').prop('checked', true);
                            $('#editExchangeProgrammeModal .df_code_area').fadeIn('fast', function(){
                                $('#editExchangeProgrammeModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editExchangeProgrammeModal input[name="is_df"]').prop('checked', false);
                            $('#editExchangeProgrammeModal .df_code_area').fadeOut('fast', function(){
                                $('#editExchangeProgrammeModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editExchangeProgrammeModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editExchangeProgrammeModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editExchangeProgrammeModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editExchangeProgrammeForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editExchangeProgrammeForm input[name="id"]').val();
            const form = document.getElementById("editExchangeProgrammeForm");

            document.querySelector('#updateExchangeProgramme').setAttribute('disabled', 'disabled');
            document.querySelector('#updateExchangeProgramme svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("exchange.programme.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateExchangeProgramme").removeAttribute("disabled");
                    document.querySelector("#updateExchangeProgramme svg").style.cssText = "display: none;";
                    editExchangeProgrammeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                ExchangeProgrammeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateExchangeProgramme").removeAttribute("disabled");
                document.querySelector("#updateExchangeProgramme svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editExchangeProgrammeForm .${key}`).addClass('border-danger')
                            $(`#editExchangeProgrammeForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editExchangeProgrammeModal.hide();

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
            if(action == 'DELETEEXCHNGPRGM'){
                axios({
                    method: 'delete',
                    url: route('exchange.programme.destory', recordID),
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
                    ExchangeProgrammeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREEXCHNGPRGM'){
                axios({
                    method: 'post',
                    url: route('exchange.programme.restore', recordID),
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
                    ExchangeProgrammeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATEXCHNGPRGM'){
                axios({
                    method: 'post',
                    url: route('exchange.programme.update.status', recordID),
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
                    ExchangeProgrammeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#ExchangeProgrammeListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATEXCHNGPRGM');
            });
        });

        // Delete Course
        $('#ExchangeProgrammeListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEEXCHNGPRGM');
            });
        });

        // Restore Course
        $('#ExchangeProgrammeListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREEXCHNGPRGM');
            });
        });

        $('#exchangeProgrammeImportModal').on('click','#saveExchangeProgramme',function(e) {
            e.preventDefault();
            $('#exchangeProgrammeImportModal .dropzone').get(0).dropzone.processQueue();
            exchangeProgrammeImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();