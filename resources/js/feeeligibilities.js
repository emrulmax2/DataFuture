import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var feeEligibilitiesListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-FEEELIGIBILITY").val() != "" ? $("#query-FEEELIGIBILITY").val() : "";
        let status = $("#status-FEEELIGIBILITY").val() != "" ? $("#status-FEEELIGIBILITY").val() : "";
        let tableContent = new Tabulator("#feeEligibilitiesListTable", {
            ajaxURL: route("feeeligibilities.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editFeeEligibilityModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="edit-3" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-FEEELIGIBILITY").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-FEEELIGIBILITY").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-FEEELIGIBILITY").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Fee Eligibilities Details",
            });
        });

        $("#tabulator-export-html-FEEELIGIBILITY").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-FEEELIGIBILITY").on("click", function (event) {
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
    if ($("#feeEligibilitiesListTable").length) {
        // Init Table
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'feeEligibilitiesListTable'){
                feeEligibilitiesListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormFEEELIGIBILITY() {
            feeEligibilitiesListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-FEEELIGIBILITY")[0].addEventListener(
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
        $("#tabulator-html-filter-go-FEEELIGIBILITY").on("click", function (event) {
            filterHTMLFormFEEELIGIBILITY();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-FEEELIGIBILITY").on("click", function (event) {
            $("#query-FEEELIGIBILITY").val("");
            $("#status-FEEELIGIBILITY").val("1");
            filterHTMLFormFEEELIGIBILITY();
        });

        const addFeeEligibilityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addFeeEligibilityModal"));
        const editFeeEligibilityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFeeEligibilityModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addFeeEligibilityModalEl = document.getElementById('addFeeEligibilityModal')
        addFeeEligibilityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addFeeEligibilityModal .acc__input-error').html('');
            $('#addFeeEligibilityModal .modal-body input:not([type="checkbox"])').val('');

            $('#addFeeEligibilityModal input[name="is_hesa"]').prop('checked', false);
            $('#addFeeEligibilityModal .hesa_code_area').fadeOut('fast', function(){
                $('#addFeeEligibilityModal .hesa_code_area input').val('');
            });
            $('#addFeeEligibilityModal input[name="is_df"]').prop('checked', false);
            $('#addFeeEligibilityModal .df_code_area').fadeOut('fast', function(){
                $('#addFeeEligibilityModal .df_code_area input').val('');
            })
            $('#addFeeEligibilityModal input[name="active"]').prop('checked', true);
        });
        
        const editFeeEligibilityModalEl = document.getElementById('editFeeEligibilityModal')
        editFeeEligibilityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editFeeEligibilityModal .acc__input-error').html('');
            $('#editFeeEligibilityModal .modal-body input:not([type="checkbox"])').val('');
            $('#editFeeEligibilityModal input[name="id"]').val('0');

            $('#editFeeEligibilityModal input[name="is_hesa"]').prop('checked', false);
            $('#editFeeEligibilityModal .hesa_code_area').fadeOut('fast', function(){
                $('#editFeeEligibilityModal .hesa_code_area input').val('');
            });
            $('#editFeeEligibilityModal input[name="is_df"]').prop('checked', false);
            $('#editFeeEligibilityModal .df_code_area').fadeOut('fast', function(){
                $('#editFeeEligibilityModal .df_code_area input').val('');
            })
            $('#editFeeEligibilityModal input[name="active"]').prop('checked', false);
        });
        
        $('#addFeeEligibilityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addFeeEligibilityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addFeeEligibilityForm .hesa_code_area input').val('');
                })
            }else{
                $('#addFeeEligibilityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addFeeEligibilityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addFeeEligibilityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addFeeEligibilityForm .df_code_area').fadeIn('fast', function(){
                    $('#addFeeEligibilityForm .df_code_area input').val('');
                })
            }else{
                $('#addFeeEligibilityForm .df_code_area').fadeOut('fast', function(){
                    $('#addFeeEligibilityForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editFeeEligibilityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editFeeEligibilityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editFeeEligibilityForm .hesa_code_area input').val('');
                })
            }else{
                $('#editFeeEligibilityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editFeeEligibilityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editFeeEligibilityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editFeeEligibilityForm .df_code_area').fadeIn('fast', function(){
                    $('#editFeeEligibilityForm .df_code_area input').val('');
                })
            }else{
                $('#editFeeEligibilityForm .df_code_area').fadeOut('fast', function(){
                    $('#editFeeEligibilityForm .df_code_area input').val('');
                })
            }
        })

        $('#addFeeEligibilityForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addFeeEligibilityForm');
        
            document.querySelector('#saveFeeEligibility').setAttribute('disabled', 'disabled');
            document.querySelector("#saveFeeEligibility svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            
            axios({
                method: "post",
                url: route('feeeligibilities.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveFeeEligibility').removeAttribute('disabled');
                document.querySelector("#saveFeeEligibility svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addFeeEligibilityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                feeEligibilitiesListTable.init();
            }).catch(error => {
                document.querySelector('#saveFeeEligibility').removeAttribute('disabled');
                document.querySelector("#saveFeeEligibility svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addFeeEligibilityForm .${key}`).addClass('border-danger');
                            $(`#addFeeEligibilityForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#feeEligibilitiesListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("feeeligibilities.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editFeeEligibilityModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editFeeEligibilityModal input[name="is_hesa"]').prop('checked', true);
                            $('#editFeeEligibilityModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editFeeEligibilityModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editFeeEligibilityModal input[name="is_hesa"]').prop('checked', false);
                            $('#editFeeEligibilityModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editFeeEligibilityModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editFeeEligibilityModal input[name="is_df"]').prop('checked', true);
                            $('#editFeeEligibilityModal .df_code_area').fadeIn('fast', function(){
                                $('#editFeeEligibilityModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editFeeEligibilityModal input[name="is_df"]').prop('checked', false);
                            $('#editFeeEligibilityModal .df_code_area').fadeOut('fast', function(){
                                $('#editFeeEligibilityModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editFeeEligibilityModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editFeeEligibilityModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editFeeEligibilityModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editFeeEligibilityForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editFeeEligibilityForm input[name="id"]').val();
            const form = document.getElementById("editFeeEligibilityForm");

            document.querySelector('#updateFeeEligibility').setAttribute('disabled', 'disabled');
            document.querySelector('#updateFeeEligibility svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("feeeligibilities.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateFeeEligibility").removeAttribute("disabled");
                    document.querySelector("#updateFeeEligibility svg").style.cssText = "display: none;";
                    editFeeEligibilityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                feeEligibilitiesListTable.init();
            }).catch((error) => {
                document.querySelector("#updateFeeEligibility").removeAttribute("disabled");
                document.querySelector("#updateFeeEligibility svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editFeeEligibilityForm .${key}`).addClass('border-danger')
                            $(`#editFeeEligibilityForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editFeeEligibilityModal.hide();

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
            if(action == 'DELETEFEEELIGIBILITY'){
                axios({
                    method: 'delete',
                    url: route('feeeligibilities.destory', recordID),
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
                    feeEligibilitiesListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREFEEELIGIBILITY'){
                axios({
                    method: 'post',
                    url: route('feeeligibilities.restore', recordID),
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
                    feeEligibilitiesListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATFEEELIGIBILITY'){
                axios({
                    method: 'post',
                    url: route('feeeligibilities.update.status', recordID),
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
                    feeEligibilitiesListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Status Updater
        $('#feeEligibilitiesListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATFEEELIGIBILITY');
            });
        });

        // Delete Course
        $('#feeEligibilitiesListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEFEEELIGIBILITY');
            });
        });

        // Restore Course
        $('#feeEligibilitiesListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREFEEELIGIBILITY');
            });
        });
    }
})();