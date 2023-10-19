import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var moduleAssesmentListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-01").val() != "" ? $("#query-01").val() : "";
        let status = $("#status-01").val() != "" ? $("#status-01").val() : "";
        let module = $("#moduleAssesmentDataTable").attr('data-courseid') != "" ? $("#moduleAssesmentDataTable").attr('data-courseid') : "0";

        let tableContent = new Tabulator("#moduleAssesmentDataTable", {
            ajaxURL: route("course.module.assesment.list"),
            ajaxParams: { querystr: querystr, status: status, module: module},
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
                    width: "180",
                },
                {
                    title: "Assesment Code",
                    field: "code",
                    headerHozAlign: "left",
                },
                {
                    title: "Assesment Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#moduleAssesmentEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Course Module Assessment",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
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
    if ($("#moduleAssesmentDataTable").length) {
        // Init Table
        moduleAssesmentListTable.init();

        // Filter function
        function filterHTMLForm() {
            moduleAssesmentListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-01")[0].addEventListener(
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
        $("#tabulator-html-filter-go-01").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-01").on("click", function (event) {
            $("#query-01").val("");
            $("#status-01").val("1");
            filterHTMLForm();
        });


        const moduleAssesmentAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleAssesmentAddModal"));
        const moduleAssesmentEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleAssesmentEditModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModalCMA = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCMA"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const moduleAssesmentAddModalEl = document.getElementById('moduleAssesmentAddModal')
        moduleAssesmentAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#moduleAssesmentAddModal .acc__input-error').html('');
            $('#moduleAssesmentAddModal input[type="text"]').val('');
        });
        
        const moduleAssesmentEditModalEl = document.getElementById('moduleAssesmentEditModal')
        moduleAssesmentEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#moduleAssesmentEditModal .acc__input-error').html('');
            $('#moduleAssesmentEditModal input[type="text"]').val('');
            $('#moduleAssesmentEditModal input[name="id"]').val('0');
        });

        const confirmModalCMAEL = document.getElementById('confirmModalCMA');
        confirmModalCMAEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalCMA .agreeWithCMA').attr('data-id', '0');
            $('#confirmModalCMA .agreeWithCMA').attr('data-action', 'none');
        });


        // Delete Course
        $('#moduleAssesmentDataTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModalCMA.show();
            document.getElementById('confirmModalCMA').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCMA .confModTitleCMA').html(confModalDelTitle);
                $('#confirmModalCMA .confModDescCMA').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalCMA .agreeWithCMA').attr('data-id', rowID);
                $('#confirmModalCMA .agreeWithCMA').attr('data-action', 'DELETE');
            });
        });

        $('#moduleAssesmentDataTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalCMA.show();
            document.getElementById('confirmModalCMA').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCMA .confModTitleCMA').html(confModalDelTitle);
                $('#confirmModalCMA .confModDescCMA').html('Do you really want to restore these record?');
                $('#confirmModalCMA .agreeWithCMA').attr('data-id', courseID);
                $('#confirmModalCMA .agreeWithCMA').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalCMA .agreeWithCMA').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalCMA button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.module.assesment.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCMA button').removeAttr('disabled');
                        confirmModalCMA.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Course module assesment data successfully deleted.');
                        });
                    }
                    moduleAssesmentListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.module.assesment.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCMA button').removeAttr('disabled');
                        confirmModalCMA.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Module Assesment Data Successfully Restored!');
                        });
                    }
                    moduleAssesmentListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })


        $("#moduleAssesmentDataTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.module.assesment.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#moduleAssesmentEditModal input[name="assesment_code"]').val(dataset.assesment_code ? dataset.assesment_code : '');
                    $('#moduleAssesmentEditModal input[name="assesment_name"]').val(dataset.assesment_name ? dataset.assesment_name : '');
                    

                    $('#moduleAssesmentEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });


        $('#moduleAssesmentEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('moduleAssesmentEditForm');

            $('#moduleAssesmentEditForm').find('input').removeClass('border-danger')
            $('#moduleAssesmentEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateModuleAssesment').setAttribute('disabled', 'disabled');
            document.querySelector('#updateModuleAssesment svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('course.module.assesment.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateModuleAssesment').removeAttribute('disabled');
                document.querySelector('#updateModuleAssesment svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    moduleAssesmentEditModal.hide();
                    moduleAssesmentListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Module Assesment data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateModuleAssesment').removeAttribute('disabled');
                document.querySelector('#updateModuleAssesment svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#moduleAssesmentEditForm .${key}`).addClass('border-danger')
                            $(`#moduleAssesmentEditForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });


        $('#moduleAssesmentAddForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('moduleAssesmentAddForm');

            $('#moduleAssesmentAddForm').find('input').removeClass('border-danger')
            $('#moduleAssesmentAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveModuleAssesment').setAttribute('disabled', 'disabled');
            document.querySelector('#saveModuleAssesment svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('course.module.assesment.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveModuleAssesment').removeAttribute('disabled');
                document.querySelector('#saveModuleAssesment svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    moduleAssesmentAddModal.hide();
                    moduleAssesmentListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Module Assesment data successfully inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveModuleAssesment').removeAttribute('disabled');
                document.querySelector('#saveModuleAssesment svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#moduleAssesmentAddForm .${key}`).addClass('border-danger')
                            $(`#moduleAssesmentAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });
    }
})()