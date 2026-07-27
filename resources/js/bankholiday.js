import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import Dropzone from "dropzone";
import IMask from "imask";

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
            paginationSizeSelector: [10, 25, 50, 100],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            columns: [
                {
                    title: "#ID",
                    field: "id",
                    width: 90,
                },
                {
                    title: "Start Date",
                    field: "start_date",
                    headerHozAlign: "left",
                    minWidth: 130,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "End Date",
                    field: "end_date",
                    headerHozAlign: "left",
                    minWidth: 130,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "Duration",
                    field: "duration",
                    headerHozAlign: "left",
                    minWidth: 120,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "Title",
                    field: "title",
                    headerHozAlign: "left",
                    minWidth: 220,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                    minWidth: 150,
                    formatter(cell) {
                        return cell.getValue() || "&mdash;";
                    },
                },
                {
                    title: "Actions",
                    field: "actions",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: 120,
                    minWidth: 120,
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="edit_btn ss-row-action ss-row-action--edit" aria-label="Edit bank holiday"><i data-lucide="pencil"></i></button>';
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="delete_btn ss-row-action ss-row-action--delete" aria-label="Delete bank holiday"><i data-lucide="trash-2"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '" type="button" class="restore_btn ss-row-action ss-row-action--restore" aria-label="Restore bank holiday"><i data-lucide="rotate-cw"></i></button>';
                        }
                        
                        return btns;
                    },
                },
            ],
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.7,
                    nameAttr: "data-lucide",
                });
            },
        });

        if (window.bankHolidayTableResizeHandler) {
            window.removeEventListener("resize", window.bankHolidayTableResizeHandler);
        }

        window.bankHolidayTableResizeHandler = () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.7,
                nameAttr: "data-lucide",
            });
        };

        window.addEventListener("resize", window.bankHolidayTableResizeHandler);

        // Export
        $("#tabulator-export-csv").off("click.bankholiday").on("click.bankholiday", function () {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").off("click.bankholiday").on("click.bankholiday", function () {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").off("click.bankholiday").on("click.bankholiday", function () {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Bank Holiday Details",
            });
        });

        $("#tabulator-export-html").off("click.bankholiday").on("click.bankholiday", function () {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").off("click.bankholiday").on("click.bankholiday", function () {
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
        autoProcessQueue: false,
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

        $(".datepicker").each(function () {
            if (this.dataset.maskReady) {
                return;
            }

            IMask(this, {
                mask: "00-00-0000",
            });
            this.dataset.maskReady = "1";
        });

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
        const bankholidayImportModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#bankholidayImportModal"));

        let confModalDelTitle = 'Are you sure?';

        const showSuccess = (title, message) => {
            $('#successModal .successModalTitle').html(title);
            $('#successModal .successModalDesc').html(message);
            succModal.show();
        };

        const showConfirm = (id, action, title, message) => {
            $('#bankholidayConfirmModal .bankholidayConfModTitle').html(title);
            $('#bankholidayConfirmModal .bankholidayConfModDesc').html(message);
            $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-id', id);
            $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-action', action);
            confModal.show();
        };

        const formatDateForInput = (value) => {
            if (!value) {
                return '';
            }

            const parts = String(value).split('-');
            if (parts.length === 3 && parts[0].length === 4) {
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }

            return value;
        };

        const bankholidayAddModalEl = document.getElementById('bankholidayAddModal')
        bankholidayAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#bankholidayAddModal .acc__input-error').html('');
            $('#bankholidayAddModal .border-danger').removeClass('border-danger');
            $('#bankholidayAddModal input[type="text"], #bankholidayAddModal input[type="number"]').val('');
            $('#bankholidayAddModal select').val('');
        });
        
        const bankholidayEditModalEl = document.getElementById('bankholidayEditModal')
        bankholidayEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#bankholidayEditModal .acc__input-error').html('');
            $('#bankholidayEditModal .border-danger').removeClass('border-danger');
            $('#bankholidayEditModal input[type="text"], #bankholidayEditModal input[type="number"]').val('');
            $('#bankholidayEditModal select').val('');
            $('#bankholidayEditModal input[name="id"]').val('0');
        });

        const bankholidayConfirmModal = document.getElementById('bankholidayConfirmModal');
        bankholidayConfirmModal.addEventListener('hidden.tw.modal', function(event){
            $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-id', '0');
            $('#bankholidayConfirmModal .bankholidayAgreeWith').attr('data-action', 'none');
            $('#bankholidayConfirmModal button').removeAttr('disabled');
        });


        // Delete Room
        $('#bankholidayTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            showConfirm(rowID, 'DELETE', confModalDelTitle, 'Do you really want to delete this bank holiday? Please click agree to continue.');
        });

        $('#bankholidayTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let academicyearID = $statusBTN.attr('data-id');

            showConfirm(academicyearID, 'RESTORE', confModalDelTitle, 'Do you really want to restore this bank holiday? Please click agree to continue.');
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

                        showSuccess('Done!', 'Bank Holiday data successfully deleted!');
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

                        showSuccess('Success!', 'Bank holiday data successfully restored!');
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
                    $('#bankholidayEditModal input[name="start_date"]').val(formatDateForInput(dataset.start_date));
                    $('#bankholidayEditModal input[name="end_date"]').val(formatDateForInput(dataset.end_date));
                    $('#bankholidayEditModal input[name="duration"]').val(dataset.duration ? dataset.duration : '');
                    $('#bankholidayEditModal input[name="title"]').val(dataset.title ? dataset.title : '');
                    $('#bankholidayEditModal select[name="type"]').val(dataset.type ? dataset.type : '');

                    $('#bankholidayEditModal input[name="id"]').val(editId);
                    bankholidayEditModal.show();
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#bankholidayEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('bankholidayEditForm');

            $('#bankholidayEditForm').find('input, select').removeClass('border-danger')
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
                    
                    showSuccess('Success!', 'Bank holiday data successfully updated.');
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

            $('#bankholidayAddForm').find('input, select').removeClass('border-danger')
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
                    
                    showSuccess('Success!', 'Bank holiday data successfully inserted.');
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

            showSuccess('Success!', 'Holidays data successfully uploaded.');
            //setTimeout(function() { succModal.hide(); }, 3000);
            
        });
    }
})();
