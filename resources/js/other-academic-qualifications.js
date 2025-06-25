import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var otherAcademicQualificationsListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-OTACQF").val() != "" ? $("#query-OTACQF").val() : "";
        let status = $("#status-OTACQF").val() != "" ? $("#status-OTACQF").val() : "";
        let tableContent = new Tabulator("#otherAcademicQualificationsListTable", {
            ajaxURL: route("other.academic.qualification.list"),
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editOtherAcademicQualificationModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-OTACQF").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-OTACQF").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-OTACQF").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-OTACQF").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-OTACQF").on("click", function (event) {
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
    if ($("#otherAcademicQualificationsListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'otherAcademicQualificationsListTable'){
                otherAcademicQualificationsListTable.init();
            }
        });
        

        // Filter function
        function filterOTACQFHTMLForm() {
            otherAcademicQualificationsListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-OTACQF")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterOTACQFHTMLForm();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-OTACQF").on("click", function (event) {
            filterOTACQFHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-OTACQF").on("click", function (event) {
            $("#query-OTACQF").val("");
            $("#status-OTACQF").val("1");
            filterOTACQFHTMLForm();
        });

        const addOtherAcademicQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addOtherAcademicQualificationModal"));
        const editOtherAcademicQualificationModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editOtherAcademicQualificationModal"));
        const otherAcademicQualificationModalImportModal = tailwind.Modal.getOrCreateInstance("#otherAcademicQualificationModalImportModal");
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addOtherAcademicQualificationModalEl = document.getElementById('addOtherAcademicQualificationModal')
        addOtherAcademicQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addOtherAcademicQualificationModal .acc__input-error').html('');
            $('#addOtherAcademicQualificationModal .modal-body input:not([type="checkbox"])').val('');

            $('#addOtherAcademicQualificationModal input[name="is_hesa"]').prop('checked', false);
            $('#addOtherAcademicQualificationModal .hesa_code_area').fadeOut('fast', function(){
                $('#addOtherAcademicQualificationModal .hesa_code_area input').val('');
            });
            $('#addOtherAcademicQualificationModal input[name="is_df"]').prop('checked', false);
            $('#addOtherAcademicQualificationModal .df_code_area').fadeOut('fast', function(){
                $('#addOtherAcademicQualificationModal .df_code_area input').val('');
            });
            $('#addOtherAcademicQualificationModal input[name="active"]').prop('checked', true);
        });
        
        const editOtherAcademicQualificationModalEl = document.getElementById('editOtherAcademicQualificationModal')
        editOtherAcademicQualificationModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editOtherAcademicQualificationModal .acc__input-error').html('');
            $('#editOtherAcademicQualificationModal .modal-body input:not([type="checkbox"])').val('');
            $('#editOtherAcademicQualificationModal input[name="id"]').val('0');

            $('#editOtherAcademicQualificationModal input[name="is_hesa"]').prop('checked', false);
            $('#editOtherAcademicQualificationModal .hesa_code_area').fadeOut('fast', function(){
                $('#editOtherAcademicQualificationModal .hesa_code_area input').val('');
            });
            $('#editOtherAcademicQualificationModal input[name="is_df"]').prop('checked', false);
            $('#editOtherAcademicQualificationModal .df_code_area').fadeOut('fast', function(){
                $('#editOtherAcademicQualificationModal .df_code_area input').val('');
            })
            $('#editOtherAcademicQualificationModal input[name="active"]').prop('checked', false);
        });
        
        $('#addOtherAcademicQualificationForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addOtherAcademicQualificationForm .hesa_code_area').fadeIn('fast', function(){
                    $('#addOtherAcademicQualificationForm .hesa_code_area input').val('');
                })
            }else{
                $('#addOtherAcademicQualificationForm .hesa_code_area').fadeOut('fast', function(){
                    $('#addOtherAcademicQualificationForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#addOtherAcademicQualificationForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addOtherAcademicQualificationForm .df_code_area').fadeIn('fast', function(){
                    $('#addOtherAcademicQualificationForm .df_code_area input').val('');
                })
            }else{
                $('#addOtherAcademicQualificationForm .df_code_area').fadeOut('fast', function(){
                    $('#addOtherAcademicQualificationForm .df_code_area input').val('');
                })
            }
        })
        
        $('#editOtherAcademicQualificationForm input[name="is_hesa"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editOtherAcademicQualificationForm .hesa_code_area').fadeIn('fast', function(){
                    $('#editOtherAcademicQualificationForm .hesa_code_area input').val('');
                })
            }else{
                $('#editOtherAcademicQualificationForm .hesa_code_area').fadeOut('fast', function(){
                    $('#editOtherAcademicQualificationForm .hesa_code_area input').val('');
                })
            }
        })
        
        $('#editOtherAcademicQualificationForm input[name="is_df"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editOtherAcademicQualificationForm .df_code_area').fadeIn('fast', function(){
                    $('#editOtherAcademicQualificationForm .df_code_area input').val('');
                })
            }else{
                $('#editOtherAcademicQualificationForm .df_code_area').fadeOut('fast', function(){
                    $('#editOtherAcademicQualificationForm .df_code_area input').val('');
                })
            }
        })

        $('#addOtherAcademicQualificationForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addOtherAcademicQualificationForm');
        
            document.querySelector('#saveaddOtherAcademicQualification').setAttribute('disabled', 'disabled');
            document.querySelector("#saveaddOtherAcademicQualification svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('other.academic.qualification.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                console.log(response)
                document.querySelector('#saveaddOtherAcademicQualification').removeAttribute('disabled');
                document.querySelector("#saveaddOtherAcademicQualification svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addOtherAcademicQualificationModal.hide();

                    succModal.show();
                    otherAcademicQualificationsListTable.init();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html( "Congratulations!" );
                            $("#successModal .successModalDesc").html('Title Item Successfully inserted.');
                    });     
                }
                
            }).catch(error => {
                otherAcademicQualificationsListTable.init();
                document.querySelector('#saveaddOtherAcademicQualification').removeAttribute('disabled');
                document.querySelector("#saveaddOtherAcademicQualification svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addOtherAcademicQualificationForm .${key}`).addClass('border-danger');
                            $(`#addOtherAcademicQualificationForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#otherAcademicQualificationsListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("other.academic.qualification.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editOtherAcademicQualificationModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        if(dataset.is_hesa == 1){
                            $('#editOtherAcademicQualificationModal input[name="is_hesa"]').prop('checked', true);
                            $('#editOtherAcademicQualificationModal .hesa_code_area').fadeIn('fast', function(){
                                $('#editOtherAcademicQualificationModal input[name="hesa_code"]').val(dataset.hesa_code);
                            })
                        }else{
                            $('#editOtherAcademicQualificationModal input[name="is_hesa"]').prop('checked', false);
                            $('#editOtherAcademicQualificationModal .hesa_code_area').fadeOut('fast', function(){
                                $('#editOtherAcademicQualificationModal input[name="hesa_code"]').val('');
                            })
                        }

                        if(dataset.is_df == 1){
                            $('#editOtherAcademicQualificationModal input[name="is_df"]').prop('checked', true);
                            $('#editOtherAcademicQualificationModal .df_code_area').fadeIn('fast', function(){
                                $('#editOtherAcademicQualificationModal input[name="df_code"]').val(dataset.df_code);
                            })
                        }else{
                            $('#editOtherAcademicQualificationModal input[name="is_df"]').prop('checked', false);
                            $('#editOtherAcademicQualificationModal .df_code_area').fadeOut('fast', function(){
                                $('#editOtherAcademicQualificationModal input[name="df_code"]').val('');
                            })
                        }
                        $('#editOtherAcademicQualificationModal input[name="id"]').val(editId);

                        if(dataset.active == 1){
                            $('#editOtherAcademicQualificationModal input[name="active"]').prop('checked', true);
                        }else{
                            $('#editOtherAcademicQualificationModal input[name="active"]').prop('checked', false);
                        }
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editOtherAcademicQualificationForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editOtherAcademicQualificationForm input[name="id"]').val();
            const form = document.getElementById("editOtherAcademicQualificationForm");

            document.querySelector('#updateOtherAcademicQualification').setAttribute('disabled', 'disabled');
            document.querySelector('#updateOtherAcademicQualification svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("other.academic.qualification.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateOtherAcademicQualification").removeAttribute("disabled");
                    document.querySelector("#updateOtherAcademicQualification svg").style.cssText = "display: none;";
                    editOtherAcademicQualificationModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Titles data successfully updated.');
                    });
                }
                otherAcademicQualificationsListTable.init();
            }).catch((error) => {
                document.querySelector("#updateOtherAcademicQualification").removeAttribute("disabled");
                document.querySelector("#updateOtherAcademicQualification svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editOtherAcademicQualificationForm .${key}`).addClass('border-danger')
                            $(`#editOtherAcademicQualificationForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editOtherAcademicQualificationModal.hide();

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
            if(action == 'DELETEOTACQF'){
                axios({
                    method: 'delete',
                    url: route('other.academic.qualification.destory', recordID),
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
                    otherAcademicQualificationsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTOREOTACQF'){
                axios({
                    method: 'post',
                    url: route('other.academic.qualification.restore', recordID),
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
                    otherAcademicQualificationsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTATOTACQF'){
                axios({
                    method: 'post',
                    url: route('other.academic.qualification.update.status', recordID),
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
                    otherAcademicQualificationsListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#otherAcademicQualificationsListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATOTACQF');
            });
        });

        // Delete Course
        $('#otherAcademicQualificationsListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.dd');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETEOTACQF');
            });
        });

        // Restore Course
        $('#otherAcademicQualificationsListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTOREOTACQF');
            });
        });

        $('#otherAcademicQualificationModalImportModal').on('click','#saveaddOtherAcademicQualification',function(e) {
            e.preventDefault();
            $('#otherAcademicQualificationModalImportModal .dropzone').get(0).dropzone.processQueue();
            otherAcademicQualificationModalImportModal.hide();

            succModal.show();   
            setTimeout(function() { succModal.hide(); }, 2000);          
        });
    }
})();