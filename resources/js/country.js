import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import { data } from "jquery";
 
("use strict");
var countryListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-CNTR").val() != "" ? $("#query-CNTR").val() : "";
        let status = $("#status-CNTR").val() != "" ? $("#status-CNTR").val() : "";
        let tableContent = new Tabulator("#countryListTable", {
            ajaxURL: route("countries.list"),
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
                    title: "ISO Code",
                    field: "iso_code",
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCountryModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-CNTR").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CNTR").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CNTR").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Course Details"
            });
        });

        $("#tabulator-export-html-CNTR").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CNTR").on("click", function (event) {
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
    if ($("#countryListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'countryListTable'){
                countryListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormCNTR() {
            countryListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-CNTR")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormCNTR();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-CNTR").on("click", function (event) {
            filterHTMLFormCNTR();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CNTR").on("click", function (event) {
            $("#query-CNTR").val("");
            $("#status-CNTR").val("1");
            filterHTMLFormCNTR();
        });

        const addCountryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCountryModal"));
        const editCountryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCountryModal"));
        const countryImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#countryImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addCountryModalEl = document.getElementById('addCountryModal')
        addCountryModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addCountryModal .acc__input-error').html('');
            $('#addCountryModal .modal-body input:not([type="checkbox"])').val('');

            $('#addCountryModal input[name="is_hesa"]').prop('checked', false);
            $('#addCountryModal .hesa_code_area').fadeOut('fast', function(){
                $('#addCountryModal .hesa_code_area input').val('');
            });
            $('#addCountryModal input[name="is_df"]').prop('checked', false);
            $('#addCountryModal .df_code_area').fadeOut('fast', function(){
                $('#addCountryModal .df_code_area input').val('');
            })
            $('#addCountryModal input[name="active"]').prop('checked', true);
        });
        
        const editCountryModalEl = document.getElementById('editCountryModal')
        editCountryModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editCountryModal .acc__input-error').html('');
            $('#editCountryModal .modal-body input:not([type="checkbox"])').val('');
            $('#editCountryModal input[name="id"]').val('0');

            $('#editCountryModal input[name="is_hesa"]').prop('checked', false);
            $('#editCountryModal .hesa_code_area').fadeOut('fast', function(){
                $('#editCountryModal .hesa_code_area input').val('');
            });
            $('#editCountryModal input[name="is_df"]').prop('checked', false);
            $('#editCountryModal .df_code_area').fadeOut('fast', function(){
                $('#editCountryModal .df_code_area input').val('');
            })
            $('#editCountryModal input[name="active"]').prop('checked', false);
        });
        
        $('#addCountryForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addCountryForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addCountryForm .hesa_code_area input').val('');
                })
            }else{
                $('#addCountryForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addCountryForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addCountryForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addCountryForm .df_code_area').fadeIn('fast', function(){
                    $('#addCountryForm .df_code_area input').val('');
                })
            }else{
                $('#addCountryForm .df_code_area').fadeOut('fast', function(){
                    $('#addCountryForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editCountryForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editCountryForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editCountryForm .hesa_code_area input').val('');
                })
            }else{
                $('#editCountryForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editCountryForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editCountryForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editCountryForm .df_code_area').fadeIn('fast', function(){
                    $('#editCountryForm .df_code_area input').val('');
                })
            }else{
                $('#editCountryForm .df_code_area').fadeOut('fast', function(){
                    $('#editCountryForm .df_code_area input').val('');
                })
            }
        })

        $('#addCountryForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addCountryForm');
        
            document.querySelector('#saveCountry').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCountry svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('countries.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCountry').removeAttribute('disabled');
                document.querySelector("#saveCountry svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addCountryModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                countryListTable.init();
            }).catch(error => {
                document.querySelector('#saveCountry').removeAttribute('disabled');
                document.querySelector("#saveCountry svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addCountryForm .${key}`).addClass('border-danger');
                            $(`#addCountryForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#countryListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("countries.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editCountryModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        $('#editCountryModal input[name="iso_code"]').val(dataset.iso_code ? dataset.iso_code : '');
                        if(dataset.is_hesa == 1){
                            $('#editCountryModal input[name="is_hesa"]').prop('checked', true);
                            $('#editCountryModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editCountryModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editCountryModal input[name="is_hesa"]').prop('checked', false);
                            $('#editCountryModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editCountryModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editCountryModal input[name="is_df"]').prop('checked', true);
                            $('#editCountryModal .df_code_area').fadeIn('fast', function(){
                                $('#editCountryModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editCountryModal input[name="is_df"]').prop('checked', false);
                            $('#editCountryModal .df_code_area').fadeOut('fast', function(){
                                $('#editCountryModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editCountryModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editCountryModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editCountryModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editCountryForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editCountryForm input[name="id"]').val();
            const form = document.getElementById("editCountryForm");

            document.querySelector('#updateCountry').setAttribute('disabled', 'disabled');
            document.querySelector('#updateCountry svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("countries.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateCountry").removeAttribute("disabled");
                    document.querySelector("#updateCountry svg").style.cssText = "display: none;";
                    editCountryModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                countryListTable.init();
            }).catch((error) => {
                document.querySelector("#updateCountry").removeAttribute("disabled");
                document.querySelector("#updateCountry svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editCountryForm .${key}`).addClass('border-danger')
                            $(`#editCountryForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editCountryModal.hide();

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
            if(action == 'DELETECNTR'){
                axios({
                    method: 'delete',
                    url: route('countries.destory', recordID),
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
                    countryListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORECNTR'){
                axios({
                    method: 'post',
                    url: route('countries.restore', recordID),
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
                    countryListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATCNTR'){
                axios({
                    method: 'post',
                    url: route('countries.update.status', recordID),
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
                    countryListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#countryListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATCNTR');
            });
        });

        // Delete Course
        $('#countryListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETECNTR');
            });
        });

        // Restore Course
        $('#countryListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORECNTR');
            });
        });

        $('#countryImportModal').on('click','#saveCountry',function(e) {
            e.preventDefault();
            $('#countryImportModal .dropzone').get(0).dropzone.processQueue();
            countryImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 3000);          
        });
    }
})();