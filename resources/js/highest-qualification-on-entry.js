import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var HighestqoeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-HIGHESTQOE").val() != "" ? $("#query-HIGHESTQOE").val() : "";
        let status = $("#status-HIGHESTQOE").val() != "" ? $("#status-HIGHESTQOE").val() : "";
        let tableContent = new Tabulator("#HighestqoeListTable", {
            ajaxURL: route("highestqoe.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editHighestqoeModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-HIGHESTQOE").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-HIGHESTQOE").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-HIGHESTQOE").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Highest Qualification on Entry Details",
            });
        });

        $("#tabulator-export-html-HIGHESTQOE").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-HIGHESTQOE").on("click", function (event) {
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
    if ($("#HighestqoeListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'HighestqoeListTable'){
                HighestqoeListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormHIGHESTQOE() {
            HighestqoeListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-HIGHESTQOE")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormHIGHESTQOE();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-HIGHESTQOE").on("click", function (event) {
            filterHTMLFormHIGHESTQOE();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-HIGHESTQOE").on("click", function (event) {
            $("#query-HIGHESTQOE").val("");
            $("#status-HIGHESTQOE").val("1");
            filterHTMLFormHIGHESTQOE();
        });

        const addHighestqoeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHighestqoeModal"));
        const editHighestqoeModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHighestqoeModal"));
        const highestqoeImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#highestqoeImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addHighestqoeModalEl = document.getElementById('addHighestqoeModal')
        addHighestqoeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addHighestqoeModal .acc__input-error').html('');
            $('#addHighestqoeModal .modal-body input:not([type="checkbox"])').val('');

            $('#addHighestqoeModal input[name="is_hesa"]').prop('checked', false);
            $('#addHighestqoeModal .hesa_code_area').fadeOut('fast', function(){
                $('#addHighestqoeModal .hesa_code_area input').val('');
            });
            $('#addHighestqoeModal input[name="is_df"]').prop('checked', false);
            $('#addHighestqoeModal .df_code_area').fadeOut('fast', function(){
                $('#addHighestqoeModal .df_code_area input').val('');
            })
            $('#addHighestqoeModal input[name="active"]').prop('checked', true);
        });
        
        const editHighestqoeModalEl = document.getElementById('editHighestqoeModal')
        editHighestqoeModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editHighestqoeModal .acc__input-error').html('');
            $('#editHighestqoeModal .modal-body input:not([type="checkbox"])').val('');
            $('#editHighestqoeModal input[name="id"]').val('0');

            $('#editHighestqoeModal input[name="is_hesa"]').prop('checked', false);
            $('#editHighestqoeModal .hesa_code_area').fadeOut('fast', function(){
                $('#editHighestqoeModal .hesa_code_area input').val('');
            });
            $('#editHighestqoeModal input[name="is_df"]').prop('checked', false);
            $('#editHighestqoeModal .df_code_area').fadeOut('fast', function(){
                $('#editHighestqoeModal .df_code_area input').val('');
            })
            $('#editHighestqoeModal input[name="active"]').prop('checked', false);
        });
        
        $('#addHighestqoeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHighestqoeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addHighestqoeForm .hesa_code_area input').val('');
                })
            }else{
                $('#addHighestqoeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addHighestqoeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addHighestqoeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHighestqoeForm .df_code_area').fadeIn('fast', function(){
                    $('#addHighestqoeForm .df_code_area input').val('');
                })
            }else{
                $('#addHighestqoeForm .df_code_area').fadeOut('fast', function(){
                    $('#addHighestqoeForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editHighestqoeForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHighestqoeForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editHighestqoeForm .hesa_code_area input').val('');
                })
            }else{
                $('#editHighestqoeForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editHighestqoeForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editHighestqoeForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHighestqoeForm .df_code_area').fadeIn('fast', function(){
                    $('#editHighestqoeForm .df_code_area input').val('');
                })
            }else{
                $('#editHighestqoeForm .df_code_area').fadeOut('fast', function(){
                    $('#editHighestqoeForm .df_code_area input').val('');
                })
            }
        })

        $('#addHighestqoeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addHighestqoeForm');
        
            document.querySelector('#saveHighestqoe').setAttribute('disabled', 'disabled');
            document.querySelector("#saveHighestqoe svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('highestqoe.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveHighestqoe').removeAttribute('disabled');
                document.querySelector("#saveHighestqoe svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addHighestqoeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                HighestqoeListTable.init();
            }).catch(error => {
                document.querySelector('#saveHighestqoe').removeAttribute('disabled');
                document.querySelector("#saveHighestqoe svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addHighestqoeForm .${key}`).addClass('border-danger');
                            $(`#addHighestqoeForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#HighestqoeListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("highestqoe.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editHighestqoeModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editHighestqoeModal input[name="is_hesa"]').prop('checked', true);
                            $('#editHighestqoeModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editHighestqoeModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editHighestqoeModal input[name="is_hesa"]').prop('checked', false);
                            $('#editHighestqoeModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editHighestqoeModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editHighestqoeModal input[name="is_df"]').prop('checked', true);
                            $('#editHighestqoeModal .df_code_area').fadeIn('fast', function(){
                                $('#editHighestqoeModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editHighestqoeModal input[name="is_df"]').prop('checked', false);
                            $('#editHighestqoeModal .df_code_area').fadeOut('fast', function(){
                                $('#editHighestqoeModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editHighestqoeModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editHighestqoeModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editHighestqoeModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editHighestqoeForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editHighestqoeForm input[name="id"]').val();
            const form = document.getElementById("editHighestqoeForm");

            document.querySelector('#updateHighestqoe').setAttribute('disabled', 'disabled');
            document.querySelector('#updateHighestqoe svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("highestqoe.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateHighestqoe").removeAttribute("disabled");
                    document.querySelector("#updateHighestqoe svg").style.cssText = "display: none;";
                    editHighestqoeModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                HighestqoeListTable.init();
            }).catch((error) => {
                document.querySelector("#updateHighestqoe").removeAttribute("disabled");
                document.querySelector("#updateHighestqoe svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editHighestqoeForm .${key}`).addClass('border-danger')
                            $(`#editHighestqoeForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editHighestqoeModal.hide();

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
            if(action == 'DELETEHIGHESTQOE'){
                axios({
                    method: 'delete',
                    url: route('highestqoe.destory', recordID),
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
                    HighestqoeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREHIGHESTQOE'){
                axios({
                    method: 'post',
                    url: route('highestqoe.restore', recordID),
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
                    HighestqoeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATHIGHESTQOE'){
                axios({
                    method: 'post',
                    url: route('highestqoe.update.status', recordID),
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
                    HighestqoeListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#HighestqoeListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATHIGHESTQOE');
            });
        });

        // Delete Course
        $('#HighestqoeListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEHIGHESTQOE');
            });
        });

        // Restore Course
        $('#HighestqoeListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREHIGHESTQOE');
            });
        });

        $('#highestqoeImportModal').on('click','#saveHighestqoe',function(e) {
            e.preventDefault();
            $('#highestqoeImportModal .dropzone').get(0).dropzone.processQueue();
            highestqoeImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();