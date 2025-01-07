import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var EqvOrLwQfListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-EQVORLWQF").val() != "" ? $("#query-EQVORLWQF").val() : "";
        let status = $("#status-EQVORLWQF").val() != "" ? $("#status-EQVORLWQF").val() : "";
        let tableContent = new Tabulator("#EqvOrLwQfListTable", {
            ajaxURL: route("eqvorlwqf.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editEqvOrLwQfModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-EQVORLWQF").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-EQVORLWQF").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-EQVORLWQF").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Reason for Engagement Ending Details",
            });
        });

        $("#tabulator-export-html-EQVORLWQF").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-EQVORLWQF").on("click", function (event) {
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
    if ($("#EqvOrLwQfListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'EqvOrLwQfListTable'){
                EqvOrLwQfListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormEQVORLWQF() {
            EqvOrLwQfListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-EQVORLWQF")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormEQVORLWQF();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-EQVORLWQF").on("click", function (event) {
            filterHTMLFormEQVORLWQF();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-EQVORLWQF").on("click", function (event) {
            $("#query-EQVORLWQF").val("");
            $("#status-EQVORLWQF").val("1");
            filterHTMLFormEQVORLWQF();
        });

        const addEqvOrLwQfModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addEqvOrLwQfModal"));
        const editEqvOrLwQfModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editEqvOrLwQfModal"));
        const eqvOrLwQfImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#eqvOrLwQfImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addEqvOrLwQfModalEl = document.getElementById('addEqvOrLwQfModal')
        addEqvOrLwQfModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addEqvOrLwQfModal .acc__input-error').html('');
            $('#addEqvOrLwQfModal .modal-body input:not([type="checkbox"])').val('');

            $('#addEqvOrLwQfModal input[name="is_hesa"]').prop('checked', false);
            $('#addEqvOrLwQfModal .hesa_code_area').fadeOut('fast', function(){
                $('#addEqvOrLwQfModal .hesa_code_area input').val('');
            });
            $('#addEqvOrLwQfModal input[name="is_df"]').prop('checked', false);
            $('#addEqvOrLwQfModal .df_code_area').fadeOut('fast', function(){
                $('#addEqvOrLwQfModal .df_code_area input').val('');
            })
            $('#addEqvOrLwQfModal input[name="active"]').prop('checked', true);
        });
        
        const editEqvOrLwQfModalEl = document.getElementById('editEqvOrLwQfModal')
        editEqvOrLwQfModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editEqvOrLwQfModal .acc__input-error').html('');
            $('#editEqvOrLwQfModal .modal-body input:not([type="checkbox"])').val('');
            $('#editEqvOrLwQfModal input[name="id"]').val('0');

            $('#editEqvOrLwQfModal input[name="is_hesa"]').prop('checked', false);
            $('#editEqvOrLwQfModal .hesa_code_area').fadeOut('fast', function(){
                $('#editEqvOrLwQfModal .hesa_code_area input').val('');
            });
            $('#editEqvOrLwQfModal input[name="is_df"]').prop('checked', false);
            $('#editEqvOrLwQfModal .df_code_area').fadeOut('fast', function(){
                $('#editEqvOrLwQfModal .df_code_area input').val('');
            })
            $('#editEqvOrLwQfModal input[name="active"]').prop('checked', false);
        });
        
        $('#addEqvOrLwQfForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addEqvOrLwQfForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addEqvOrLwQfForm .hesa_code_area input').val('');
                })
            }else{
                $('#addEqvOrLwQfForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addEqvOrLwQfForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addEqvOrLwQfForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addEqvOrLwQfForm .df_code_area').fadeIn('fast', function(){
                    $('#addEqvOrLwQfForm .df_code_area input').val('');
                })
            }else{
                $('#addEqvOrLwQfForm .df_code_area').fadeOut('fast', function(){
                    $('#addEqvOrLwQfForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editEqvOrLwQfForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editEqvOrLwQfForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editEqvOrLwQfForm .hesa_code_area input').val('');
                })
            }else{
                $('#editEqvOrLwQfForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editEqvOrLwQfForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editEqvOrLwQfForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editEqvOrLwQfForm .df_code_area').fadeIn('fast', function(){
                    $('#editEqvOrLwQfForm .df_code_area input').val('');
                })
            }else{
                $('#editEqvOrLwQfForm .df_code_area').fadeOut('fast', function(){
                    $('#editEqvOrLwQfForm .df_code_area input').val('');
                })
            }
        })

        $('#addEqvOrLwQfForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addEqvOrLwQfForm');
        
            document.querySelector('#saveEqvOrLwQf').setAttribute('disabled', 'disabled');
            document.querySelector("#saveEqvOrLwQf svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('eqvorlwqf.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveEqvOrLwQf').removeAttribute('disabled');
                document.querySelector("#saveEqvOrLwQf svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addEqvOrLwQfModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                EqvOrLwQfListTable.init();
            }).catch(error => {
                document.querySelector('#saveEqvOrLwQf').removeAttribute('disabled');
                document.querySelector("#saveEqvOrLwQf svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addEqvOrLwQfForm .${key}`).addClass('border-danger');
                            $(`#addEqvOrLwQfForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#EqvOrLwQfListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("eqvorlwqf.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editEqvOrLwQfModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editEqvOrLwQfModal input[name="is_hesa"]').prop('checked', true);
                            $('#editEqvOrLwQfModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editEqvOrLwQfModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editEqvOrLwQfModal input[name="is_hesa"]').prop('checked', false);
                            $('#editEqvOrLwQfModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editEqvOrLwQfModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editEqvOrLwQfModal input[name="is_df"]').prop('checked', true);
                            $('#editEqvOrLwQfModal .df_code_area').fadeIn('fast', function(){
                                $('#editEqvOrLwQfModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editEqvOrLwQfModal input[name="is_df"]').prop('checked', false);
                            $('#editEqvOrLwQfModal .df_code_area').fadeOut('fast', function(){
                                $('#editEqvOrLwQfModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editEqvOrLwQfModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editEqvOrLwQfModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editEqvOrLwQfModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editEqvOrLwQfForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editEqvOrLwQfForm input[name="id"]').val();
            const form = document.getElementById("editEqvOrLwQfForm");

            document.querySelector('#updateEqvOrLwQf').setAttribute('disabled', 'disabled');
            document.querySelector('#updateEqvOrLwQf svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("eqvorlwqf.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateEqvOrLwQf").removeAttribute("disabled");
                    document.querySelector("#updateEqvOrLwQf svg").style.cssText = "display: none;";
                    editEqvOrLwQfModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                EqvOrLwQfListTable.init();
            }).catch((error) => {
                document.querySelector("#updateEqvOrLwQf").removeAttribute("disabled");
                document.querySelector("#updateEqvOrLwQf svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editEqvOrLwQfForm .${key}`).addClass('border-danger')
                            $(`#editEqvOrLwQfForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editEqvOrLwQfModal.hide();

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
            if(action == 'DELETEEQVORLWQF'){
                axios({
                    method: 'delete',
                    url: route('eqvorlwqf.destory', recordID),
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
                    EqvOrLwQfListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREEQVORLWQF'){
                axios({
                    method: 'post',
                    url: route('eqvorlwqf.restore', recordID),
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
                    EqvOrLwQfListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATEQVORLWQF'){
                axios({
                    method: 'post',
                    url: route('eqvorlwqf.update.status', recordID),
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
                    EqvOrLwQfListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#EqvOrLwQfListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATEQVORLWQF');
            });
        });

        // Delete Course
        $('#EqvOrLwQfListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEEQVORLWQF');
            });
        });

        // Restore Course
        $('#EqvOrLwQfListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREEQVORLWQF');
            });
        });

        $('#eqvOrLwQfImportModal').on('click','#saveEqvOrLwQf',function(e) {
            e.preventDefault();
            $('#eqvOrLwQfImportModal .dropzone').get(0).dropzone.processQueue();
            eqvOrLwQfImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();