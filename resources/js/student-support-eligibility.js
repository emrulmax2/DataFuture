import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var StudentSupportEligibilityListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-STDNTSFTELBLTY").val() != "" ? $("#query-STDNTSFTELBLTY").val() : "";
        let status = $("#status-STDNTSFTELBLTY").val() != "" ? $("#status-STDNTSFTELBLTY").val() : "";
        let tableContent = new Tabulator("#StudentSupportEligibilityListTable", {
            ajaxURL: route("student.support.eligibility.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editStudentSupportEligibilityModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-STDNTSFTELBLTY").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-STDNTSFTELBLTY").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-STDNTSFTELBLTY").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Funding Length Details",
            });
        });

        $("#tabulator-export-html-STDNTSFTELBLTY").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-STDNTSFTELBLTY").on("click", function (event) {
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
    if ($("#StudentSupportEligibilityListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'StudentSupportEligibilityListTable'){
                StudentSupportEligibilityListTable.init();
            }
        });

        // Filter function
        function filterHTMLFormSTDNTSFTELBLTY() {
            StudentSupportEligibilityListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-STDNTSFTELBLTY")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormSTDNTSFTELBLTY();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-STDNTSFTELBLTY").on("click", function (event) {
            filterHTMLFormSTDNTSFTELBLTY();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-STDNTSFTELBLTY").on("click", function (event) {
            $("#query-STDNTSFTELBLTY").val("");
            $("#status-STDNTSFTELBLTY").val("1");
            filterHTMLFormSTDNTSFTELBLTY();
        });

        const addStudentSupportEligibilityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addStudentSupportEligibilityModal"));
        const editStudentSupportEligibilityModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editStudentSupportEligibilityModal"));
        const studentSupportEligibilityImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#studentSupportEligibilityImportModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addStudentSupportEligibilityModalEl = document.getElementById('addStudentSupportEligibilityModal')
        addStudentSupportEligibilityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addStudentSupportEligibilityModal .acc__input-error').html('');
            $('#addStudentSupportEligibilityModal .modal-body input:not([type="checkbox"])').val('');

            $('#addStudentSupportEligibilityModal input[name="is_hesa"]').prop('checked', false);
            $('#addStudentSupportEligibilityModal .hesa_code_area').fadeOut('fast', function(){
                $('#addStudentSupportEligibilityModal .hesa_code_area input').val('');
            });
            $('#addStudentSupportEligibilityModal input[name="is_df"]').prop('checked', false);
            $('#addStudentSupportEligibilityModal .df_code_area').fadeOut('fast', function(){
                $('#addStudentSupportEligibilityModal .df_code_area input').val('');
            })
            $('#addStudentSupportEligibilityModal input[name="active"]').prop('checked', true);
        });
        
        const editStudentSupportEligibilityModalEl = document.getElementById('editStudentSupportEligibilityModal')
        editStudentSupportEligibilityModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editStudentSupportEligibilityModal .acc__input-error').html('');
            $('#editStudentSupportEligibilityModal .modal-body input:not([type="checkbox"])').val('');
            $('#editStudentSupportEligibilityModal input[name="id"]').val('0');

            $('#editStudentSupportEligibilityModal input[name="is_hesa"]').prop('checked', false);
            $('#editStudentSupportEligibilityModal .hesa_code_area').fadeOut('fast', function(){
                $('#editStudentSupportEligibilityModal .hesa_code_area input').val('');
            });
            $('#editStudentSupportEligibilityModal input[name="is_df"]').prop('checked', false);
            $('#editStudentSupportEligibilityModal .df_code_area').fadeOut('fast', function(){
                $('#editStudentSupportEligibilityModal .df_code_area input').val('');
            })
            $('#editStudentSupportEligibilityModal input[name="active"]').prop('checked', false);
        });
        
        $('#addStudentSupportEligibilityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addStudentSupportEligibilityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addStudentSupportEligibilityForm .hesa_code_area input').val('');
                })
            }else{
                $('#addStudentSupportEligibilityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addStudentSupportEligibilityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addStudentSupportEligibilityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addStudentSupportEligibilityForm .df_code_area').fadeIn('fast', function(){
                    $('#addStudentSupportEligibilityForm .df_code_area input').val('');
                })
            }else{
                $('#addStudentSupportEligibilityForm .df_code_area').fadeOut('fast', function(){
                    $('#addStudentSupportEligibilityForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editStudentSupportEligibilityForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editStudentSupportEligibilityForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editStudentSupportEligibilityForm .hesa_code_area input').val('');
                })
            }else{
                $('#editStudentSupportEligibilityForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editStudentSupportEligibilityForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editStudentSupportEligibilityForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editStudentSupportEligibilityForm .df_code_area').fadeIn('fast', function(){
                    $('#editStudentSupportEligibilityForm .df_code_area input').val('');
                })
            }else{
                $('#editStudentSupportEligibilityForm .df_code_area').fadeOut('fast', function(){
                    $('#editStudentSupportEligibilityForm .df_code_area input').val('');
                })
            }
        })

        $('#addStudentSupportEligibilityForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addStudentSupportEligibilityForm');
        
            document.querySelector('#saveStudentSupportEligibility').setAttribute('disabled', 'disabled');
            document.querySelector("#saveStudentSupportEligibility svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('student.support.eligibility.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveStudentSupportEligibility').removeAttribute('disabled');
                document.querySelector("#saveStudentSupportEligibility svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addStudentSupportEligibilityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                StudentSupportEligibilityListTable.init();
            }).catch(error => {
                document.querySelector('#saveStudentSupportEligibility').removeAttribute('disabled');
                document.querySelector("#saveStudentSupportEligibility svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addStudentSupportEligibilityForm .${key}`).addClass('border-danger');
                            $(`#addStudentSupportEligibilityForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#StudentSupportEligibilityListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("student.support.eligibility.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editStudentSupportEligibilityModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editStudentSupportEligibilityModal input[name="is_hesa"]').prop('checked', true);
                            $('#editStudentSupportEligibilityModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editStudentSupportEligibilityModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editStudentSupportEligibilityModal input[name="is_hesa"]').prop('checked', false);
                            $('#editStudentSupportEligibilityModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editStudentSupportEligibilityModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editStudentSupportEligibilityModal input[name="is_df"]').prop('checked', true);
                            $('#editStudentSupportEligibilityModal .df_code_area').fadeIn('fast', function(){
                                $('#editStudentSupportEligibilityModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editStudentSupportEligibilityModal input[name="is_df"]').prop('checked', false);
                            $('#editStudentSupportEligibilityModal .df_code_area').fadeOut('fast', function(){
                                $('#editStudentSupportEligibilityModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editStudentSupportEligibilityModal input[name="id"]').val(editId);
                        if(dataset.active == 1){
                            $('#editStudentSupportEligibilityModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editStudentSupportEligibilityModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editStudentSupportEligibilityForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editStudentSupportEligibilityForm input[name="id"]').val();
            const form = document.getElementById("editStudentSupportEligibilityForm");

            document.querySelector('#updateStudentSupportEligibility').setAttribute('disabled', 'disabled');
            document.querySelector('#updateStudentSupportEligibility svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("student.support.eligibility.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateStudentSupportEligibility").removeAttribute("disabled");
                    document.querySelector("#updateStudentSupportEligibility svg").style.cssText = "display: none;";
                    editStudentSupportEligibilityModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                StudentSupportEligibilityListTable.init();
            }).catch((error) => {
                document.querySelector("#updateStudentSupportEligibility").removeAttribute("disabled");
                document.querySelector("#updateStudentSupportEligibility svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editStudentSupportEligibilityForm .${key}`).addClass('border-danger')
                            $(`#editStudentSupportEligibilityForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editStudentSupportEligibilityModal.hide();

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
            if(action == 'DELETESTDNTSFTELBLTY'){
                axios({
                    method: 'delete',
                    url: route('student.support.eligibility.destory', recordID),
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
                    StudentSupportEligibilityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORESTDNTSFTELBLTY'){
                axios({
                    method: 'post',
                    url: route('student.support.eligibility.restore', recordID),
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
                    StudentSupportEligibilityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'CHANGESTATSTDNTSFTELBLTY'){
                axios({
                    method: 'post',
                    url: route('student.support.eligibility.update.status', recordID),
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
                    StudentSupportEligibilityListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $('#StudentSupportEligibilityListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATSTDNTSFTELBLTY');
            });
        });

        // Delete Course
        $('#StudentSupportEligibilityListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETESTDNTSFTELBLTY');
            });
        });

        // Restore Course
        $('#StudentSupportEligibilityListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORESTDNTSFTELBLTY');
            });
        });

        $('#studentSupportEligibilityImportModal').on('click','#saveStudentSupportEligibility',function(e) {
            e.preventDefault();
            $('#studentSupportEligibilityImportModal .dropzone').get(0).dropzone.processQueue();
            studentSupportEligibilityImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);        
        });
    }
})();