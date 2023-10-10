import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var hgenListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-HGEN").val() != "" ? $("#query-HGEN").val() : "";
        let status = $("#status-HGEN").val() != "" ? $("#status-HGEN").val() : "";
        let tableContent = new Tabulator("#hgenListTable", {
            ajaxURL: route("gender.list"),
            ajaxParams: { querystr: querystr, status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [5, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                },
                {
                    title: "Title",
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
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editHgenModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="trash" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv-HGEN").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-HGEN").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-HGEN").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Gender Details",
            });
        });

        $("#tabulator-export-html-HGEN").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-HGEN").on("click", function (event) {
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
    if ($("#hgenListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'hgenListTable'){
                hgenListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormHGEN() {
            hgenListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-HGEN")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormHGEN();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-HGEN").on("click", function (event) {
            filterHTMLFormHGEN();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-HGEN").on("click", function (event) {
            $("#query-HGEN").val("");
            $("#status-HGEN").val("1");
            filterHTMLFormHGEN();
        });

        const addHgenModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHgenModal"));
        const editHgenModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHgenModal"));
        const genderImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#genderImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addHgenModalEl = document.getElementById('addHgenModal')
        addHgenModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addHgenModal .acc__input-error').html('');
            $('#addHgenModal .modal-body input:not([type="checkbox"])').val('');

            $('#addHgenModal input[name="is_hesa"]').prop('checked', false);
            $('#addHgenModal .hesa_code_area').fadeOut('fast', function(){
                $('#addHgenModal .hesa_code_area input').val('');
            });
            $('#addHgenModal input[name="is_df"]').prop('checked', false);
            $('#addHgenModal .df_code_area').fadeOut('fast', function(){
                $('#addHgenModal .df_code_area input').val('');
            })
            $('#addHgenModal input[name="active"]').prop('checked', true);
        });
        
        const editHgenModalEl = document.getElementById('editHgenModal')
        editHgenModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editHgenModal .acc__input-error').html('');
            $('#editHgenModal .modal-body input:not([type="checkbox"])').val('');
            $('#editHgenModal input[name="id"]').val('0');

            $('#editHgenModal input[name="is_hesa"]').prop('checked', false);
            $('#editHgenModal .hesa_code_area').fadeOut('fast', function(){
                $('#editHgenModal .hesa_code_area input').val('');
            });
            $('#editHgenModal input[name="is_df"]').prop('checked', false);
            $('#editHgenModal .df_code_area').fadeOut('fast', function(){
                $('#editHgenModal .df_code_area input').val('');
            })
            $('#editHgenModal input[name="active"]').prop('checked', false);
        });
        
        $('#addHgenForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHgenForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addHgenForm .hesa_code_area input').val('');
                })
            }else{
                $('#addHgenForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addHgenForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addHgenForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHgenForm .df_code_area').fadeIn('fast', function(){
                    $('#addHgenForm .df_code_area input').val('');
                })
            }else{
                $('#addHgenForm .df_code_area').fadeOut('fast', function(){
                    $('#addHgenForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editHgenForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHgenForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editHgenForm .hesa_code_area input').val('');
                })
            }else{
                $('#editHgenForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editHgenForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editHgenForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHgenForm .df_code_area').fadeIn('fast', function(){
                    $('#editHgenForm .df_code_area input').val('');
                })
            }else{
                $('#editHgenForm .df_code_area').fadeOut('fast', function(){
                    $('#editHgenForm .df_code_area input').val('');
                })
            }
        })

        $('#addHgenForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addHgenForm');
        
            document.querySelector('#saveHgen').setAttribute('disabled', 'disabled');
            document.querySelector("#saveHgen svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('gender.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveHgen').removeAttribute('disabled');
                document.querySelector("#saveHgen svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addHgenModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                hgenListTable.init();
            }).catch(error => {
                document.querySelector('#saveHgen').removeAttribute('disabled');
                document.querySelector("#saveHgen svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addHgenForm .${key}`).addClass('border-danger');
                            $(`#addHgenForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#hgenListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("gender.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editHgenModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editHgenModal input[name="is_hesa"]').prop('checked', true);
                            $('#editHgenModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editHgenModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editHgenModal input[name="is_hesa"]').prop('checked', false);
                            $('#editHgenModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editHgenModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editHgenModal input[name="is_df"]').prop('checked', true);
                            $('#editHgenModal .df_code_area').fadeIn('fast', function(){
                                $('#editHgenModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editHgenModal input[name="is_df"]').prop('checked', false);
                            $('#editHgenModal .df_code_area').fadeOut('fast', function(){
                                $('#editHgenModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editHgenModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editHgenModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editHgenModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editHgenForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editHgenForm input[name="id"]').val();
            const form = document.getElementById("editHgenForm");

            document.querySelector('#updateHgen').setAttribute('disabled', 'disabled');
            document.querySelector('#updateHgen svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("gender.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateHgen").removeAttribute("disabled");
                    document.querySelector("#updateHgen svg").style.cssText = "display: none;";
                    editHgenModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                hgenListTable.init();
            }).catch((error) => {
                document.querySelector("#updateHgen").removeAttribute("disabled");
                document.querySelector("#updateHgen svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editHgenForm .${key}`).addClass('border-danger')
                            $(`#editHgenForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editHgenModal.hide();

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
            if(action == 'DELETEHGEN'){
                axios({
                    method: 'delete',
                    url: route('gender.destory', recordID),
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
                    hgenListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREHGEN'){
                axios({
                    method: 'post',
                    url: route('gender.restore', recordID),
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
                    hgenListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATHGEN'){
                axios({
                    method: 'post',
                    url: route('gender.update.status', recordID),
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
                    hgenListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#hgenListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATHGEN');
            });
        });

        // Delete Course
        $('#hgenListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEHGEN');
            });
        });

        // Restore Course
        $('#hgenListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREHGEN');
            });
        });

        $('#genderImportModal').on('click','#saveGender',function(e) {
            e.preventDefault();
            $('#genderImportModal .dropzone').get(0).dropzone.processQueue();
            genderImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 3000);          
        });
    }
})();