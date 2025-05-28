import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";

("use strict");

var bankholidayListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let academicyear = $("#bankholidayTableId").attr('data-academicyearid') != "" ? $("#bankholidayTableId").attr('data-academicyearid') : "0";

        let tableContent = new Tabulator("#bankholidayTableId", {
            ajaxURL: route("bankholidays.list"),
            ajaxParams: { querystr: querystr, status: status, academicyear: academicyear},
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
                    title: "Title",
                    field: "title",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "type",
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#bankholidayEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Bank Holiday Details",
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

// Dropzone
Dropzone.autoDiscover = false;
$(".dropzone").each(function () {
    Dropzone.options.bankholidayImportForm = {
        autoProcessQueue: false
    };

    let options = {
        accept: (file, done) => {
            console.log("Uploaded");
            done();
        },
    };

    if ($(this).data("single")) {
        options.maxFiles = 1;
    }

    if ($(this).data("file-types")) {
        options.accept = (file, done) => {
            if ($(this).data("file-types").split("|").indexOf(file.type) === -1) {
                alert("Error! Files of this type are not accepted");
                done("Error! Files of this type are not accepted");
            } else {
                console.log("Uploaded");
                done();
            }
        };
    }

    var dz = new Dropzone(this, options);

    dz.on("maxfilesexceeded", (file) => {
        alert("No more files please!");
    });
    dz.on("complete", function(file) {
        dz.removeFile(file);
        bankholidayListTable.init();
    });        
});

(function () {
    if ($("#bankholidayTableId").length) {
        // Init Table
        bankholidayListTable.init();

        // Filter function
        function filterHTMLForm() {
            bankholidayListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm")[0].addEventListener(
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
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLForm();
        });

        const bankholidayAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#bankholidayAddModal"));
        const bankholidayEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#bankholidayEditModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#bankholidayConfirmModal"));
        const bankholidayImportModal = tailwind.Modal.getOrCreateInstance("#bankholidayImportModal");

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const bankholidayAddModalEl = document.getElementById('bankholidayAddModal')
        bankholidayAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#bankholidayAddModal .acc__input-error').html('');
            $('#bankholidayAddModal input[type="text"]').val('');
            $('#bankholidayAddModal select').val('');
        });
        
        const bankholidayEditModalEl = document.getElementById('bankholidayEditModal')
        bankholidayEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#bankholidayEditModal .acc__input-error').html('');
            $('#bankholidayEditModal input[type="text"]').val('');
            $('#bankholidayEditModal select').val('');
            $('#bankholidayEditModal input[name="id"]').val('0');
        });

        const bankholidayConfirmModal = document.getElementById('bankholidayConfirmModal');
        bankholidayConfirmModal.addEventListener('hidden.tw.modal', function(event){
            $('#bankholidayConfirmModal .roomAgreeWith').attr('data-id', '0');
            $('#bankholidayConfirmModal .roomAgreeWith').attr('data-action', 'none');
        });


        // Delete Room
        $('#bankholidayTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('bankholidayConfirmModal').addEventListener('shown.tw.modal', function(event){
                $('#bankholidayConfirmModal .bankholidayConfModTitle').html(confModalDelTitle);
                $('#bankholidayConfirmModal .bankholidayConfModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-id', rowID);
                $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-action', 'DELETE');
            });
        });

        $('#bankholidayTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let academicyearID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('bankholidayConfirmModal').addEventListener('shown.tw.modal', function(event){
                $('#bankholidayConfirmModal .bankholidayConfModTitle').html(confModalDelTitle);
                $('#bankholidayConfirmModal .bankholidayConfModDesc').html('Do you really want to restore these record?');
                $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-id', academicyearID);
                $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#bankholidayConfirmModal .bankholidayAgreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#bankholidayConfirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('bankholidays.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#bankholidayConfirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Bank Holiday data successfully deleted!');
                        });
                    }
                    bankholidayListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('bankholidays.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#bankholidayConfirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Bank Holiday Data Successfully Restored!');
                        });
                    }
                    bankholidayListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#bankholidayTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("bankholidays.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#bankholidayEditModal input[name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                    $('#bankholidayEditModal input[name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                    $('#bankholidayEditModal input[name="duration"]').val(dataset.duration ? dataset.duration : '');
                    $('#bankholidayEditModal input[name="title"]').val(dataset.title ? dataset.title : '');
                    $('#bankholidayEditModal select[name="type"]').val(dataset.type ? dataset.type : '');

                    $('#bankholidayEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#bankholidayEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('bankholidayEditForm');

            $('#bankholidayEditForm').find('input').removeClass('border-danger')
            $('#bankholidayEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateBankholiday').setAttribute('disabled', 'disabled');
            document.querySelector('#updateBankholiday svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('bankholidays.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateBankholiday').removeAttribute('disabled');
                document.querySelector('#updateBankholiday svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    bankholidayEditModal.hide();
                    bankholidayListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Room data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateBankholiday').removeAttribute('disabled');
                document.querySelector('#updateBankholiday svg').style.cssText = 'display: none;';
                if(error.response){
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#bankholidayEditForm .${key}`).addClass('border-danger')
                            $(`#bankholidayEditForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });
        });

        $('#bankholidayAddForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('bankholidayAddForm');

            $('#bankholidayAddForm').find('input').removeClass('border-danger')
            $('#bankholidayAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveBankholiday').setAttribute('disabled', 'disabled');
            document.querySelector('#saveBankholiday svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('bankholidays.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveBankholiday').removeAttribute('disabled');
                document.querySelector('#saveBankholiday svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    bankholidayAddModal.hide();
                    bankholidayListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Bank holiday data successfully inserted.');
                    });
                }               
            }).catch(error => {
                document.querySelector('#saveBankholiday').removeAttribute('disabled');
                document.querySelector('#saveBankholiday svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#bankholidayAddForm .${key}`).addClass('border-danger')
                            $(`#bankholidayAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#bankholidayImportModal').on('click','#saveImportholiday',function(e) {
            e.preventDefault();
            $('.dropzone').get(0).dropzone.processQueue();
            bankholidayImportModal.hide();

            succModal.show();   
            //setTimeout(function() { succModal.hide(); }, 3000);
            
        });
    }
})()