import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var courseMNListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-03").val() != "" ? $("#query-03").val() : "";
        let status = $("#status-03").val() != "" ? $("#status-03").val() : "";
        let course = $("#courseMonitorTableId").attr('data-courseid') != "" ? $("#courseMonitorTableId").attr('data-courseid') : "0";

        let tableContent = new Tabulator("#courseMonitorTableId", {
            ajaxURL: route("course.monitor.list"),
            ajaxParams: { querystr: querystr, status: status, course: course},
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Email",
                    field: "email",
                    headerSort: false,
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#courseMonitorEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Source of Tuition Fees",
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
    if ($("#courseMonitorTableId").length) {
        // Init Table
        courseMNListTable.init();

        // Filter function
        function filterHTMLForm() {
            courseMNListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm03")[0].addEventListener(
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
        $("#tabulator-html-filter-go-03").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-03").on("click", function (event) {
            $("#query-03").val("");
            $("#status-03").val("1");
            filterHTMLForm();
        });

        

        const courseMonitorAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#courseMonitorAddModal"));
        const courseMonitorEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#courseMonitorEditModal"));
        const succModalMN = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModalMN = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalMN"));

        let confModalDelTitleMN = 'Are you sure?';
        let confModalDelDescriptionMN = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescriptionMN = 'Do you really want to re-store these records? Click agree to continue.';

        const courseMonitorAddModalEl = document.getElementById('courseMonitorAddModal')
        courseMonitorAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#courseMonitorAddModal .acc__input-error').html('');
            $('#courseMonitorAddModal input[type="text"]').val('');
            $('#courseMonitorAddModal select').val('');

            datafuture_field_id.clear(true);
        });
        
        const courseMonitorEditModalEl = document.getElementById('courseMonitorEditModal')
        courseMonitorEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#courseMonitorEditModal .acc__input-error').html('');
            $('#courseMonitorEditModal input[type="text"]').val('');
            $('#courseMonitorEditModal select').val('');
            $('#courseMonitorEditModal input[name="id"]').val('0');

            edit_datafuture_field_id.clear(true);
        });

        const confModalMNEL = document.getElementById('confirmModalMN');
        confModalMNEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalMN .agreeWithMN').attr('data-id', '0');
            $('#confirmModalMN .agreeWithMN').attr('data-action', 'none');
        });

        // Delete Course
        $('#courseMonitorTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModalMN.show();
            document.getElementById('confirmModalMN').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalMN .confModTitleMN').html(confModalDelTitleMN);
                $('#confirmModalMN .confModDescMN').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalMN .agreeWithMN').attr('data-id', rowID);
                $('#confirmModalMN .agreeWithMN').attr('data-action', 'DELETE');
            });
        });

        $('#courseMonitorTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModalMN.show();
            document.getElementById('confirmModalMN').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalMN .confModTitleMN').html(confModalDelTitleMN);
                $('#confirmModalMN .confModDescMN').html('Do you really want to restore these record?');
                $('#confirmModalMN .agreeWithMN').attr('data-id', courseID);
                $('#confirmModalMN .agreeWithMN').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalMN .agreeWithMN').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalMN button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.monitor.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalMN button').removeAttr('disabled');
                        confModalMN.hide();

                        succModalMN.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Course base datafuture data successfully deleted.');
                        });
                    }
                    courseMNListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.monitor.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalMN button').removeAttr('disabled');
                        confModalMN.hide();

                        succModalMN.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Base Datafuture Data Successfully Restored!');
                        });
                    }
                    courseMNListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#courseMonitorTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.monitor.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#courseMonitorEditModal input[name="email"]').val(dataset.email ? dataset.email : '');
                    $('#courseMonitorEditModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#courseMonitorEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#courseMonitorEditForm').on('submit', function(e){
            e.preventDefault();
            const formMN = document.getElementById('courseMonitorEditForm');

            $('#courseMonitorEditForm').find('input').removeClass('border-danger')
            $('#courseMonitorEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateBaseMN').setAttribute('disabled', 'disabled');
            document.querySelector('#updateBaseMN svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formMN);

            axios({
                method: "post",
                url: route('course.monitor.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateBaseMN').removeAttribute('disabled');
                document.querySelector('#updateBaseMN svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    courseMonitorEditModal.hide();
                    courseMNListTable.init();
                    
                    succModalMN.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Base Datafuture Field Data Successfully Updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateBaseMN').removeAttribute('disabled');
                document.querySelector('#updateBaseMN svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#courseMonitorEditForm .${key}`).addClass('border-danger')
                            $(`#courseMonitorEditForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });


        $('#courseMonitorAddForm').on('submit', function(e){
            e.preventDefault();
            const formMN = document.getElementById('courseMonitorAddForm');

            $('#courseMonitorAddForm').find('input').removeClass('border-danger')
            $('#courseMonitorAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveBaseMN').setAttribute('disabled', 'disabled');
            document.querySelector('#saveBaseMN svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(formMN);

            axios({
                method: "post",
                url: route('course.monitor.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveBaseMN').removeAttribute('disabled');
                document.querySelector('#saveBaseMN svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    courseMonitorAddModal.hide();
                    courseMNListTable.init();
                    
                    succModalMN.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Base Databuture Field Data Successfully Inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveBaseMN').removeAttribute('disabled');
                document.querySelector('#saveBaseMN svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#courseMonitorAddForm .${key}`).addClass('border-danger')
                            $(`#courseMonitorAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });

    }
})()