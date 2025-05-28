import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var HesaQualSubListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-HesaQualSub").val() != "" ? $("#query-HesaQualSub").val() : "";
        let status = $("#status-HesaQualSub").val() != "" ? $("#status-HesaQualSub").val() : "";
        let tableContent = new Tabulator("#HesaQualSubListTable", {
            ajaxURL: route("hesaQualificationSubject.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editHesaQualSubModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-HesaQualSub").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-HesaQualSub").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-HesaQualSub").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Highest Qualification on Entry Details",
            });
        });

        $("#tabulator-export-html-HesaQualSub").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-HesaQualSub").on("click", function (event) {
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
    if ($("#HesaQualSubListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'HesaQualSubListTable'){
                HesaQualSubListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormHesaQualSub() {
            HesaQualSubListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-HesaQualSub")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormHesaQualSub();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-HesaQualSub").on("click", function (event) {
            filterHTMLFormHesaQualSub();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-HesaQualSub").on("click", function (event) {
            $("#query-HesaQualSub").val("");
            $("#status-HesaQualSub").val("1");
            filterHTMLFormHesaQualSub();
        });

        const addHesaQualSubModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHesaQualSubModal"));
        const editHesaQualSubModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHesaQualSubModal"));
        const HesaQualSubImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#HesaQualSubImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addHesaQualSubModalEl = document.getElementById('addHesaQualSubModal')
        addHesaQualSubModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addHesaQualSubModal .acc__input-error').html('');
            $('#addHesaQualSubModal .modal-body input:not([type="checkbox"])').val('');

            $('#addHesaQualSubModal input[name="is_hesa"]').prop('checked', false);
            $('#addHesaQualSubModal .hesa_code_area').fadeOut('fast', function(){
                $('#addHesaQualSubModal .hesa_code_area input').val('');
            });
            $('#addHesaQualSubModal input[name="is_df"]').prop('checked', false);
            $('#addHesaQualSubModal .df_code_area').fadeOut('fast', function(){
                $('#addHesaQualSubModal .df_code_area input').val('');
            })
            $('#addHesaQualSubModal input[name="active"]').prop('checked', true);
        });
        
        const editHesaQualSubModalEl = document.getElementById('editHesaQualSubModal')
        editHesaQualSubModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editHesaQualSubModal .acc__input-error').html('');
            $('#editHesaQualSubModal .modal-body input:not([type="checkbox"])').val('');
            $('#editHesaQualSubModal input[name="id"]').val('0');

            $('#editHesaQualSubModal input[name="is_hesa"]').prop('checked', false);
            $('#editHesaQualSubModal .hesa_code_area').fadeOut('fast', function(){
                $('#editHesaQualSubModal .hesa_code_area input').val('');
            });
            $('#editHesaQualSubModal input[name="is_df"]').prop('checked', false);
            $('#editHesaQualSubModal .df_code_area').fadeOut('fast', function(){
                $('#editHesaQualSubModal .df_code_area input').val('');
            })
            $('#editHesaQualSubModal input[name="active"]').prop('checked', false);
        });
        
        $('#addHesaQualSubForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHesaQualSubForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addHesaQualSubForm .hesa_code_area input').val('');
                })
            }else{
                $('#addHesaQualSubForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addHesaQualSubForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addHesaQualSubForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addHesaQualSubForm .df_code_area').fadeIn('fast', function(){
                    $('#addHesaQualSubForm .df_code_area input').val('');
                })
            }else{
                $('#addHesaQualSubForm .df_code_area').fadeOut('fast', function(){
                    $('#addHesaQualSubForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editHesaQualSubForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHesaQualSubForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editHesaQualSubForm .hesa_code_area input').val('');
                })
            }else{
                $('#editHesaQualSubForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editHesaQualSubForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editHesaQualSubForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editHesaQualSubForm .df_code_area').fadeIn('fast', function(){
                    $('#editHesaQualSubForm .df_code_area input').val('');
                })
            }else{
                $('#editHesaQualSubForm .df_code_area').fadeOut('fast', function(){
                    $('#editHesaQualSubForm .df_code_area input').val('');
                })
            }
        })

        $('#addHesaQualSubForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addHesaQualSubForm');
        
            document.querySelector('#saveHesaQualSub').setAttribute('disabled', 'disabled');
            document.querySelector("#saveHesaQualSub svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('hesaQualificationSubject.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveHesaQualSub').removeAttribute('disabled');
                document.querySelector("#saveHesaQualSub svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addHesaQualSubModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                HesaQualSubListTable.init();
            }).catch(error => {
                document.querySelector('#saveHesaQualSub').removeAttribute('disabled');
                document.querySelector("#saveHesaQualSub svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addHesaQualSubForm .${key}`).addClass('border-danger');
                            $(`#addHesaQualSubForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#HesaQualSubListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("hesaQualificationSubject.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editHesaQualSubModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editHesaQualSubModal input[name="is_hesa"]').prop('checked', true);
                            $('#editHesaQualSubModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editHesaQualSubModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editHesaQualSubModal input[name="is_hesa"]').prop('checked', false);
                            $('#editHesaQualSubModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editHesaQualSubModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editHesaQualSubModal input[name="is_df"]').prop('checked', true);
                            $('#editHesaQualSubModal .df_code_area').fadeIn('fast', function(){
                                $('#editHesaQualSubModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editHesaQualSubModal input[name="is_df"]').prop('checked', false);
                            $('#editHesaQualSubModal .df_code_area').fadeOut('fast', function(){
                                $('#editHesaQualSubModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editHesaQualSubModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editHesaQualSubModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editHesaQualSubModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editHesaQualSubForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editHesaQualSubForm input[name="id"]').val();
            const form = document.getElementById("editHesaQualSubForm");

            document.querySelector('#updateHesaQualSub').setAttribute('disabled', 'disabled');
            document.querySelector('#updateHesaQualSub svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("hesaQualificationSubject.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateHesaQualSub").removeAttribute("disabled");
                    document.querySelector("#updateHesaQualSub svg").style.cssText = "display: none;";
                    editHesaQualSubModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                HesaQualSubListTable.init();
            }).catch((error) => {
                document.querySelector("#updateHesaQualSub").removeAttribute("disabled");
                document.querySelector("#updateHesaQualSub svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editHesaQualSubForm .${key}`).addClass('border-danger')
                            $(`#editHesaQualSubForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editHesaQualSubModal.hide();

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
            if(action == 'DELETEHesaQualSub'){
                axios({
                    method: 'delete',
                    url: route('hesaQualificationSubject.destory', recordID),
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
                    HesaQualSubListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREHesaQualSub'){
                axios({
                    method: 'post',
                    url: route('hesaQualificationSubject.restore', recordID),
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
                    HesaQualSubListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATHesaQualSub'){
                axios({
                    method: 'post',
                    url: route('hesaQualificationSubject.update.status', recordID),
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
                    HesaQualSubListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#HesaQualSubListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATHesaQualSub');
            });
        });

        // Delete Course
        $('#HesaQualSubListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEHesaQualSub');
            });
        });

        // Restore Course
        $('#HesaQualSubListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREHesaQualSub');
            });
        });

        $('#HesaQualSubImportModal').on('click','#saveHesaQualSub',function(e) {
            e.preventDefault();
            $('#HesaQualSubImportModal .dropzone').get(0).dropzone.processQueue();
            HesaQualSubImportModal.hide();
            HesaQualSubListTable.init();
            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();