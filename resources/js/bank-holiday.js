import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

import dayjs from "dayjs";
import Litepicker from "litepicker";

("use strict");
var hrBankHolidayList = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-BHY").val() != "" ? $("#query-BHY").val() : "";
        let status = $("#status-BHY").val() != "" ? $("#status-BHY").val() : "";
        let holidayYear = $("#hrBankHolidayList").attr('data-year');

        let tableContent = new Tabulator("#hrBankHolidayList", {
            ajaxURL: route("hr.bank.holiday.list"),
            ajaxParams: { holidayyear: holidayYear, querystr: querystr, status: status },
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
                    width: "80",
                },
                {
                    title: "Year",
                    field: "year",
                    headerHozAlign: "left",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Duration",
                    field: "duration",
                    headerHozAlign: "left",
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editBankHolidayModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-BHY").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-BHY").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-BHY").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-BHY").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-BHY").on("click", function (event) {
            tableContent.print();
        });
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if ($("#hrBankHolidayList").length) {
        hrBankHolidayList.init();

        // Filter function
        function filterTitleHTMLForm() {
            hrBankHolidayList.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-BHY")[0].addEventListener(
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
        $("#tabulator-html-filter-go-BHY").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-BHY").on("click", function (event) {
            $("#query-BHY").val("");
            $("#status-BHY").val("1");
            filterTitleHTMLForm();
        });

    }
    const bankHolidayImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#bankHolidayImportModal"));
    const editBankHolidayModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editBankHolidayModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    const editBankHolidayModalEl = document.getElementById('editBankHolidayModal')
    editBankHolidayModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editBankHolidayModal .acc__input-error').html('');
        $('#editBankHolidayModal .modal-body input').val('');
        $('#editBankHolidayModal .modal-body textarea').val('');
        $('#editBankHolidayModal input[name="id"]').val('0');
    });

    $('#bankHolidayImportModal .closeImportModal').on('click', function(e){
        e.preventDefault();
        bankHolidayImportModal.hide();
        window.location.reload();
    });

    let dateOption = {
        autoApply: true,
        singleMode: true,
        numberOfColumns: 1,
        numberOfMonths: 1,
        showWeekNumbers: true,
        format: "DD-MM-YYYY",
        dropdowns: {
            minYear: 1900,
            maxYear: 2050,
            months: true,
            years: true,
        },
    };

    const start_date = new Litepicker({
        element: document.getElementById('start_date'),
        ...dateOption,
        setup: (picker) => {
            picker.on('selected', (date1, date2) => {
                end_date.setOptions({
                    minDate: picker.getDate()
                });
            });
        }
    });

    const end_date = new Litepicker({
        element: document.getElementById('end_date'),
        ...dateOption
    });

    $("#hrBankHolidayList").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("hr.bank.holiday.edit"),
            data: {rowID : editId},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#editBankHolidayModal input[name="name"]').val(dataset.name ? dataset.name : '');
                $('#editBankHolidayModal input[name="duration"]').val(dataset.duration ? dataset.duration : 1);
                $('#editBankHolidayModal textarea[name="description"]').val(dataset.description ? dataset.description : '');

                var startDate = new Date(dataset.start_date_modified);
                start_date.setOptions({
                    startDate : dataset.start_date
                });

                if(dataset.end_date && dataset.end_date != ''){
                    end_date.setOptions({
                        startDate : dataset.end_date,
                        minDate : startDate
                    });
                }else{
                    end_date.clearSelection();
                    end_date.setOptions({
                        minDate : startDate
                    });
                }

                $('#editBankHolidayModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editBankHolidayForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editBankHolidayForm');

        $('#editBankHolidayForm').find('input').removeClass('border-danger')
        $('#editBankHolidayForm').find('.acc__input-error').html('')

        document.querySelector('#updateBH').setAttribute('disabled', 'disabled');
        document.querySelector('#updateBH svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route('hr.bank.holiday.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateBH').removeAttribute('disabled');
            document.querySelector('#updateBH svg').style.cssText = 'display: none;';
            
            if (response.status == 200) {
                editBankHolidayModal.hide();
                
                successModal.show();
                document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                    $('#successModal .successModalTitle').html('Congratulations!');
                    $('#successModal .successModalDesc').html('Bank Holiday successfully updated.');
                });

                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            hrBankHolidayList.init(); 
        }).catch(error => {
            document.querySelector('#updateBH').removeAttribute('disabled');
            document.querySelector('#updateBH svg').style.cssText = 'display: none;';
            if(error.response){
                if(error.response.status == 422){
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editBankHolidayForm .${key}`).addClass('border-danger')
                        $(`#editBankHolidayForm  .error-${key}`).html(val)
                    }
                }else{
                    console.log('error');
                }
            }
        });
    });

    // Delete Room
    $('#hrBankHolidayList').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    $('#hrBankHolidayList').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? If yes, the please click on agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('hr.bank.holiday.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                }
                hrBankHolidayList.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('0 0x.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                    });
                }
                hrBankHolidayList.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })


})();