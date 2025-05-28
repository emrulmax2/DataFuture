import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var NonRegFFlgListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-NONREGFFLG").val() != "" ? $("#query-NONREGFFLG").val() : "";
        let status = $("#status-NONREGFFLG").val() != "" ? $("#status-NONREGFFLG").val() : "";
        let tableContent = new Tabulator("#NonRegFFlgListTable", {
            ajaxURL: route("non.regulated.fee.flag.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editNonRegFFlgModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-NONREGFFLG").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-NONREGFFLG").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-NONREGFFLG").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-NONREGFFLG").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-NONREGFFLG").on("click", function (event) {
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
    if ($("#NonRegFFlgListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'NonRegFFlgListTable'){
                NonRegFFlgListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormNONREGFFLG() {
            NonRegFFlgListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-NONREGFFLG")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormNONREGFFLG();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-NONREGFFLG").on("click", function (event) {
            filterHTMLFormNONREGFFLG();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-NONREGFFLG").on("click", function (event) {
            $("#query-NONREGFFLG").val("");
            $("#status-NONREGFFLG").val("1");
            filterHTMLFormNONREGFFLG();
        });

        const addNonRegFFlgModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addNonRegFFlgModal"));
        const editNonRegFFlgModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editNonRegFFlgModal"));
        const nonRegFFlgImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#nonRegFFlgImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addNonRegFFlgModalEl = document.getElementById('addNonRegFFlgModal')
        addNonRegFFlgModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addNonRegFFlgModal .acc__input-error').html('');
            $('#addNonRegFFlgModal .modal-body input:not([type="checkbox"])').val('');

            $('#addNonRegFFlgModal input[name="is_hesa"]').prop('checked', false);
            $('#addNonRegFFlgModal .hesa_code_area').fadeOut('fast', function(){
                $('#addNonRegFFlgModal .hesa_code_area input').val('');
            });
            $('#addNonRegFFlgModal input[name="is_df"]').prop('checked', false);
            $('#addNonRegFFlgModal .df_code_area').fadeOut('fast', function(){
                $('#addNonRegFFlgModal .df_code_area input').val('');
            })
            $('#addNonRegFFlgModal input[name="active"]').prop('checked', true);
        });
        
        const editNonRegFFlgModalEl = document.getElementById('editNonRegFFlgModal')
        editNonRegFFlgModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editNonRegFFlgModal .acc__input-error').html('');
            $('#editNonRegFFlgModal .modal-body input:not([type="checkbox"])').val('');
            $('#editNonRegFFlgModal input[name="id"]').val('0');

            $('#editNonRegFFlgModal input[name="is_hesa"]').prop('checked', false);
            $('#editNonRegFFlgModal .hesa_code_area').fadeOut('fast', function(){
                $('#editNonRegFFlgModal .hesa_code_area input').val('');
            });
            $('#editNonRegFFlgModal input[name="is_df"]').prop('checked', false);
            $('#editNonRegFFlgModal .df_code_area').fadeOut('fast', function(){
                $('#editNonRegFFlgModal .df_code_area input').val('');
            })
            $('#editNonRegFFlgModal input[name="active"]').prop('checked', false);
        });
        
        $('#addNonRegFFlgForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addNonRegFFlgForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addNonRegFFlgForm .hesa_code_area input').val('');
                })
            }else{
                $('#addNonRegFFlgForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addNonRegFFlgForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addNonRegFFlgForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addNonRegFFlgForm .df_code_area').fadeIn('fast', function(){
                    $('#addNonRegFFlgForm .df_code_area input').val('');
                })
            }else{
                $('#addNonRegFFlgForm .df_code_area').fadeOut('fast', function(){
                    $('#addNonRegFFlgForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editNonRegFFlgForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editNonRegFFlgForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editNonRegFFlgForm .hesa_code_area input').val('');
                })
            }else{
                $('#editNonRegFFlgForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editNonRegFFlgForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editNonRegFFlgForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editNonRegFFlgForm .df_code_area').fadeIn('fast', function(){
                    $('#editNonRegFFlgForm .df_code_area input').val('');
                })
            }else{
                $('#editNonRegFFlgForm .df_code_area').fadeOut('fast', function(){
                    $('#editNonRegFFlgForm .df_code_area input').val('');
                })
            }
        })

        $('#addNonRegFFlgForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addNonRegFFlgForm');
        
            document.querySelector('#saveNonRegFFlg').setAttribute('disabled', 'disabled');
            document.querySelector("#saveNonRegFFlg svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('non.regulated.fee.flag.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveNonRegFFlg').removeAttribute('disabled');
                document.querySelector("#saveNonRegFFlg svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addNonRegFFlgModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                NonRegFFlgListTable.init();
            }).catch(error => {
                document.querySelector('#saveNonRegFFlg').removeAttribute('disabled');
                document.querySelector("#saveNonRegFFlg svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addNonRegFFlgForm .${key}`).addClass('border-danger');
                            $(`#addNonRegFFlgForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#NonRegFFlgListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("non.regulated.fee.flag.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editNonRegFFlgModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editNonRegFFlgModal input[name="is_hesa"]').prop('checked', true);
                            $('#editNonRegFFlgModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editNonRegFFlgModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editNonRegFFlgModal input[name="is_hesa"]').prop('checked', false);
                            $('#editNonRegFFlgModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editNonRegFFlgModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editNonRegFFlgModal input[name="is_df"]').prop('checked', true);
                            $('#editNonRegFFlgModal .df_code_area').fadeIn('fast', function(){
                                $('#editNonRegFFlgModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editNonRegFFlgModal input[name="is_df"]').prop('checked', false);
                            $('#editNonRegFFlgModal .df_code_area').fadeOut('fast', function(){
                                $('#editNonRegFFlgModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editNonRegFFlgModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editNonRegFFlgModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editNonRegFFlgModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editNonRegFFlgForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editNonRegFFlgForm input[name="id"]').val();
            const form = document.getElementById("editNonRegFFlgForm");

            document.querySelector('#updateNonRegFFlg').setAttribute('disabled', 'disabled');
            document.querySelector('#updateNonRegFFlg svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("non.regulated.fee.flag.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateNonRegFFlg").removeAttribute("disabled");
                    document.querySelector("#updateNonRegFFlg svg").style.cssText = "display: none;";
                    editNonRegFFlgModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                NonRegFFlgListTable.init();
            }).catch((error) => {
                document.querySelector("#updateNonRegFFlg").removeAttribute("disabled");
                document.querySelector("#updateNonRegFFlg svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editNonRegFFlgForm .${key}`).addClass('border-danger')
                            $(`#editNonRegFFlgForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editNonRegFFlgModal.hide();

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
            if(action == 'DELETENONREGFFLG'){
                axios({
                    method: 'delete',
                    url: route('non.regulated.fee.flag.destory', recordID),
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
                    NonRegFFlgListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORENONREGFFLG'){
                axios({
                    method: 'post',
                    url: route('non.regulated.fee.flag.restore', recordID),
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
                    NonRegFFlgListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATNONREGFFLG'){
                axios({
                    method: 'post',
                    url: route('non.regulated.fee.flag.update.status', recordID),
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
                    NonRegFFlgListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#NonRegFFlgListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATNONREGFFLG');
            });
        });

        // Delete Course
        $('#NonRegFFlgListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETENONREGFFLG');
            });
        });

        // Restore Course
        $('#NonRegFFlgListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORENONREGFFLG');
            });
        });

        $('#nonRegFFlgImportModal').on('click','#saveNonRegFFlg',function(e) {
            e.preventDefault();
            $('#nonRegFFlgImportModal .dropzone').get(0).dropzone.processQueue();
            nonRegFFlgImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();