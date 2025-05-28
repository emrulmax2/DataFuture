import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var titleListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-TITLE").val() != "" ? $("#query-TITLE").val() : "";
        let status = $("#status-TITLE").val() != "" ? $("#status-TITLE").val() : "";
        let tableContent = new Tabulator("#titleListTable", {
            ajaxURL: route("titles.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editTitleModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-TITLE").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-TITLE").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-TITLE").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-TITLE").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-TITLE").on("click", function (event) {
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
    if ($("#titleListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'titleListTable'){
                titleListTable.init();
            }
        });
        

        // Filter function
        function filterTitleHTMLForm() {
            titleListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-TITLE")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-TITLE").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-TITLE").on("click", function (event) {
            $("#query-TITLE").val("");
            $("#status-TITLE").val("1");
            filterTitleHTMLForm();
        });

        const addTitleModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTitleModal"));
        const editTitleModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTitleModal"));
        const titleImportModal = tailwind.Modal.getOrCreateInstance("#titleImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addTitleModalEl = document.getElementById('addTitleModal')
        addTitleModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addTitleModal .acc__input-error').html('');
            $('#addTitleModal .modal-body input:not([type="checkbox"])').val('');

            $('#addTitleModal input[name="is_hesa"]').prop('checked', false);
            $('#addTitleModal .hesa_code_area').fadeOut('fast', function(){
                $('#addTitleModal .hesa_code_area input').val('');
            });
            $('#addTitleModal input[name="is_df"]').prop('checked', false);
            $('#addTitleModal .df_code_area').fadeOut('fast', function(){
                $('#addTitleModal .df_code_area input').val('');
            });
            $('#addTitleModal input[name="active"]').prop('checked', true);
        });
        
        const editTitleModalEl = document.getElementById('editTitleModal')
        editTitleModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editTitleModal .acc__input-error').html('');
            $('#editTitleModal .modal-body input:not([type="checkbox"])').val('');
            $('#editTitleModal input[name="id"]').val('0');

            $('#editTitleModal input[name="is_hesa"]').prop('checked', false);
            $('#editTitleModal .hesa_code_area').fadeOut('fast', function(){
                $('#editTitleModal .hesa_code_area input').val('');
            });
            $('#editTitleModal input[name="is_df"]').prop('checked', false);
            $('#editTitleModal .df_code_area').fadeOut('fast', function(){
                $('#editTitleModal .df_code_area input').val('');
            })
            $('#editTitleModal input[name="active"]').prop('checked', false);
        });
        
        $('#addTitleForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addTitleForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addTitleForm .hesa_code_area input').val('');
                })
            }else{
                $('#addTitleForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addTitleForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addTitleForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addTitleForm .df_code_area').fadeIn('fast', function(){
                    $('#addTitleForm .df_code_area input').val('');
                })
            }else{
                $('#addTitleForm .df_code_area').fadeOut('fast', function(){
                    $('#addTitleForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editTitleForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editTitleForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editTitleForm .hesa_code_area input').val('');
                })
            }else{
                $('#editTitleForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editTitleForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editTitleForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editTitleForm .df_code_area').fadeIn('fast', function(){
                    $('#editTitleForm .df_code_area input').val('');
                })
            }else{
                $('#editTitleForm .df_code_area').fadeOut('fast', function(){
                    $('#editTitleForm .df_code_area input').val('');
                })
            }
        })

        $('#addTitleForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addTitleForm');
        
            document.querySelector('#saveTitle').setAttribute('disabled', 'disabled');
            document.querySelector("#saveTitle svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('titles.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveTitle').removeAttribute('disabled');
                document.querySelector("#saveTitle svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addTitleModal.hide();

                    succModal.show();
                    titleListTable.init();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                
            }).catch(error => {
                titleListTable.init();
                document.querySelector('#saveTitle').removeAttribute('disabled');
                document.querySelector("#saveTitle svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addTitleForm .${key}`).addClass('border-danger');
                            $(`#addTitleForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#titleListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("titles.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editTitleModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editTitleModal input[name="is_hesa"]').prop('checked', true);
                            $('#editTitleModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editTitleModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editTitleModal input[name="is_hesa"]').prop('checked', false);
                            $('#editTitleModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editTitleModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editTitleModal input[name="is_df"]').prop('checked', true);
                            $('#editTitleModal .df_code_area').fadeIn('fast', function(){
                                $('#editTitleModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editTitleModal input[name="is_df"]').prop('checked', false);
                            $('#editTitleModal .df_code_area').fadeOut('fast', function(){
                                $('#editTitleModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editTitleModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editTitleModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editTitleModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editTitleForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editTitleForm input[name="id"]').val();
            const form = document.getElementById("editTitleForm");

            document.querySelector('#updateTitle').setAttribute('disabled', 'disabled');
            document.querySelector('#updateTitle svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("titles.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateTitle").removeAttribute("disabled");
                    document.querySelector("#updateTitle svg").style.cssText = "display: none;";
                    editTitleModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                titleListTable.init();
            }).catch((error) => {
                document.querySelector("#updateTitle").removeAttribute("disabled");
                document.querySelector("#updateTitle svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editTitleForm .${key}`).addClass('border-danger')
                            $(`#editTitleForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editTitleModal.hide();

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
            if(action == 'DELETETITLE'){
                axios({
                    method: 'delete',
                    url: route('titles.destory', recordID),
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
                    titleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORETITLE'){
                axios({
                    method: 'post',
                    url: route('titles.restore', recordID),
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
                    titleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTATTITLE'){
                axios({
                    method: 'post',
                    url: route('titles.update.status', recordID),
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
                    titleListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#titleListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATTITLE');
            });
        });

        // Delete Course
        $('#titleListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETETITLE');
            });
        });

        // Restore Course
        $('#titleListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORETITLE');
            });
        });

        $('#titleImportModal').on('click','#saveTitle',function(e) {
            e.preventDefault();
            $('#titleImportModal .dropzone').get(0).dropzone.processQueue();
            titleImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();