import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var LocationOfStudyListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-LOCATNOFSTDY").val() != "" ? $("#query-LOCATNOFSTDY").val() : "";
        let status = $("#status-LOCATNOFSTDY").val() != "" ? $("#status-LOCATNOFSTDY").val() : "";
        let tableContent = new Tabulator("#LocationOfStudyListTable", {
            ajaxURL: route("location.of.study.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editLocationOfStudyModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-LOCATNOFSTDY").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-LOCATNOFSTDY").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-LOCATNOFSTDY").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-LOCATNOFSTDY").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-LOCATNOFSTDY").on("click", function (event) {
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
    if ($("#LocationOfStudyListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'LocationOfStudyListTable'){
                LocationOfStudyListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormLOCATNOFSTDY() {
            LocationOfStudyListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-LOCATNOFSTDY")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormLOCATNOFSTDY();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-LOCATNOFSTDY").on("click", function (event) {
            filterHTMLFormLOCATNOFSTDY();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-LOCATNOFSTDY").on("click", function (event) {
            $("#query-LOCATNOFSTDY").val("");
            $("#status-LOCATNOFSTDY").val("1");
            filterHTMLFormLOCATNOFSTDY();
        });

        const addLocationOfStudyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addLocationOfStudyModal"));
        const editLocationOfStudyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editLocationOfStudyModal"));
        const locationOfStudyImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#locationOfStudyImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addLocationOfStudyModalEl = document.getElementById('addLocationOfStudyModal')
        addLocationOfStudyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addLocationOfStudyModal .acc__input-error').html('');
            $('#addLocationOfStudyModal .modal-body input:not([type="checkbox"])').val('');

            $('#addLocationOfStudyModal input[name="is_hesa"]').prop('checked', false);
            $('#addLocationOfStudyModal .hesa_code_area').fadeOut('fast', function(){
                $('#addLocationOfStudyModal .hesa_code_area input').val('');
            });
            $('#addLocationOfStudyModal input[name="is_df"]').prop('checked', false);
            $('#addLocationOfStudyModal .df_code_area').fadeOut('fast', function(){
                $('#addLocationOfStudyModal .df_code_area input').val('');
            })
            $('#addLocationOfStudyModal input[name="active"]').prop('checked', true);
        });
        
        const editLocationOfStudyModalEl = document.getElementById('editLocationOfStudyModal')
        editLocationOfStudyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editLocationOfStudyModal .acc__input-error').html('');
            $('#editLocationOfStudyModal .modal-body input:not([type="checkbox"])').val('');
            $('#editLocationOfStudyModal input[name="id"]').val('0');

            $('#editLocationOfStudyModal input[name="is_hesa"]').prop('checked', false);
            $('#editLocationOfStudyModal .hesa_code_area').fadeOut('fast', function(){
                $('#editLocationOfStudyModal .hesa_code_area input').val('');
            });
            $('#editLocationOfStudyModal input[name="is_df"]').prop('checked', false);
            $('#editLocationOfStudyModal .df_code_area').fadeOut('fast', function(){
                $('#editLocationOfStudyModal .df_code_area input').val('');
            })
            $('#editLocationOfStudyModal input[name="active"]').prop('checked', false);
        });
        
        $('#addLocationOfStudyForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addLocationOfStudyForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addLocationOfStudyForm .hesa_code_area input').val('');
                })
            }else{
                $('#addLocationOfStudyForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addLocationOfStudyForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addLocationOfStudyForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addLocationOfStudyForm .df_code_area').fadeIn('fast', function(){
                    $('#addLocationOfStudyForm .df_code_area input').val('');
                })
            }else{
                $('#addLocationOfStudyForm .df_code_area').fadeOut('fast', function(){
                    $('#addLocationOfStudyForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editLocationOfStudyForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editLocationOfStudyForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editLocationOfStudyForm .hesa_code_area input').val('');
                })
            }else{
                $('#editLocationOfStudyForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editLocationOfStudyForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editLocationOfStudyForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editLocationOfStudyForm .df_code_area').fadeIn('fast', function(){
                    $('#editLocationOfStudyForm .df_code_area input').val('');
                })
            }else{
                $('#editLocationOfStudyForm .df_code_area').fadeOut('fast', function(){
                    $('#editLocationOfStudyForm .df_code_area input').val('');
                })
            }
        })

        $('#addLocationOfStudyForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addLocationOfStudyForm');
        
            document.querySelector('#saveLocationOfStudy').setAttribute('disabled', 'disabled');
            document.querySelector("#saveLocationOfStudy svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('location.of.study.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveLocationOfStudy').removeAttribute('disabled');
                document.querySelector("#saveLocationOfStudy svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addLocationOfStudyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                LocationOfStudyListTable.init();
            }).catch(error => {
                document.querySelector('#saveLocationOfStudy').removeAttribute('disabled');
                document.querySelector("#saveLocationOfStudy svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addLocationOfStudyForm .${key}`).addClass('border-danger');
                            $(`#addLocationOfStudyForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#LocationOfStudyListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("location.of.study.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editLocationOfStudyModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editLocationOfStudyModal input[name="is_hesa"]').prop('checked', true);
                            $('#editLocationOfStudyModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editLocationOfStudyModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editLocationOfStudyModal input[name="is_hesa"]').prop('checked', false);
                            $('#editLocationOfStudyModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editLocationOfStudyModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editLocationOfStudyModal input[name="is_df"]').prop('checked', true);
                            $('#editLocationOfStudyModal .df_code_area').fadeIn('fast', function(){
                                $('#editLocationOfStudyModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editLocationOfStudyModal input[name="is_df"]').prop('checked', false);
                            $('#editLocationOfStudyModal .df_code_area').fadeOut('fast', function(){
                                $('#editLocationOfStudyModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editLocationOfStudyModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editLocationOfStudyModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editLocationOfStudyModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editLocationOfStudyForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editLocationOfStudyForm input[name="id"]').val();
            const form = document.getElementById("editLocationOfStudyForm");

            document.querySelector('#updateLocationOfStudy').setAttribute('disabled', 'disabled');
            document.querySelector('#updateLocationOfStudy svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("location.of.study.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateLocationOfStudy").removeAttribute("disabled");
                    document.querySelector("#updateLocationOfStudy svg").style.cssText = "display: none;";
                    editLocationOfStudyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                LocationOfStudyListTable.init();
            }).catch((error) => {
                document.querySelector("#updateLocationOfStudy").removeAttribute("disabled");
                document.querySelector("#updateLocationOfStudy svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editLocationOfStudyForm .${key}`).addClass('border-danger')
                            $(`#editLocationOfStudyForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editLocationOfStudyModal.hide();

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
            if(action == 'DELETELOCATNOFSTDY'){
                axios({
                    method: 'delete',
                    url: route('location.of.study.destory', recordID),
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
                    LocationOfStudyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORELOCATNOFSTDY'){
                axios({
                    method: 'post',
                    url: route('location.of.study.restore', recordID),
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
                    LocationOfStudyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATLOCATNOFSTDY'){
                axios({
                    method: 'post',
                    url: route('location.of.study.update.status', recordID),
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
                    LocationOfStudyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#LocationOfStudyListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATLOCATNOFSTDY');
            });
        });

        // Delete Course
        $('#LocationOfStudyListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETELOCATNOFSTDY');
            });
        });

        // Restore Course
        $('#LocationOfStudyListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORELOCATNOFSTDY');
            });
        });

        $('#locationOfStudyImportModal').on('click','#saveLocationOfStudy',function(e) {
            e.preventDefault();
            $('#locationOfStudyImportModal .dropzone').get(0).dropzone.processQueue();
            locationOfStudyImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();