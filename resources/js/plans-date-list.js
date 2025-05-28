import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var classPlanDateListsTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let planid = $('#classPlanDateListsTable').attr('data-planid');
        let dates = $("#dates-PD").val() != "" ? $("#dates-PD").val() : "";
        let statusu = $("#status-PD").val() != "" ? $("#status-PD").val() : "";
        
        let tableContent = new Tabulator("#classPlanDateListsTable", {
            ajaxURL: route("plan.dates.list"),
            ajaxParams: { planid: planid, dates: dates, status: statusu },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 20,
            paginationSizeSelector: [true, 10, 20, 30, 40],
            layout: "fitColumns",
            responsiveLayout: "collapse",
            placeholder: "No matching records found",
            selectable:true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "60",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: "#ID",
                    field: "id",
                    width: "120",
                },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                },
                {
                    title: "Type",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Room",
                    field: "room",
                    headerHozAlign: "left",
                },
                {
                    title: "Time",
                    field: "time",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
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
                            btns += '<button data-id="' + cell.getData().id + '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' + cell.getData().id + '"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        btns += '<input type="hidden" name="ids" class="ids" value="'+cell.getData().id+'"/>';
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
            rowSelectionChanged:function(data, rows){
                var ids = [];
                const bulkActionsDropdown1 = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#bulkActionsDropdown"));
                if(rows.length > 0){
                    $('.bulkActions').removeClass('hidden');
                    bulkActionsDropdown1.hide();
                }else{
                    $('.bulkActions').addClass('hidden');
                    bulkActionsDropdown1.hide();
                }
            },
            selectableCheck:function(row){
                return row.getData().id > 0; //allow selection of rows where the age is greater than 18
            }
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Plan Date List Details",
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
    if ($("#classPlanDateListsTable").length) {
        // Init Table
        classPlanDateListsTable.init();

        // Filter function
        function filterHTMLForm() {
            classPlanDateListsTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PD")[0].addEventListener(
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
        $("#tabulator-html-filter-go-PD").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PD").on("click", function (event) {
            $("#dates-PD").val("");
            $("#status-PD").val("1");
            filterHTMLForm();
        });

        const successModalDP = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalDP"));
        const addPlansDateModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addPlansDateModal"));
        const confirmModalDP = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalDP"));
            
        let confModalDelTitle = 'Are you sure?';

        const addPlansDateModalEl = document.getElementById('addPlansDateModal')
        addPlansDateModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addPlansDateModal .acc__input-error').html('');
            $('#addPlansDateModal .modal-body select').val('');
            $('#addPlansDateModal .modal-body input').val('');
        });

        document.getElementById('confirmModalDP').addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalDP .agreeWithDP').attr('data-id', '0');
            $('#confirmModalDP .agreeWithDP').attr('data-action', 'none');
        });

        $('#addPlansDateForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addPlansDateForm');
        
            document.querySelector('#saveDate').setAttribute('disabled', 'disabled');
            document.querySelector("#saveDate svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('plan.dates.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#saveDate').removeAttribute('disabled');
                    document.querySelector("#saveDate svg").style.cssText = "display: none;";

                    addPlansDateModal.hide();

                    successModalDP.show();
                    document.getElementById("successModalDP").addEventListener("shown.tw.modal", function (event) {
                        $("#successModalDP .successModalTitleDP").html("Congratulation!" );
                        $("#successModalDP .successModalDescDP").html('Class Plan date successfully inserted.');
                    });                
                        
                }
                classPlanDateListsTable.init();
            }).catch(error => {
                document.querySelector('#saveDate').removeAttribute('disabled');
                document.querySelector("#saveDate svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addPlansDateForm .${key}`).addClass('border-danger')
                            $(`#addPlansDateForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        // Delete Course
        $('#classPlanDateListsTable').on('click', '.delete_btn', function(e){
            e.preventDefault();
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModalDP.show();
            document.getElementById('confirmModalDP').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalDP .confModTitleDP').html(confModalDelTitle);
                $('#confirmModalDP .confModDescDP').html('Do you really want to delete these record? Click on agree to continue.');
                $('#confirmModalDP .agreeWithDP').attr('data-id', rowID);
                $('#confirmModalDP .agreeWithDP').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#classPlanDateListsTable').on('click', '.restore_btn', function(e){
            e.preventDefault();
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalDP.show();
            document.getElementById('confirmModalDP').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalDP .confModTitleDP').html(confModalDelTitle);
                $('#confirmModalDP .confModDescDP').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModalDP .agreeWithDP').attr('data-id', courseID);
                $('#confirmModalDP .agreeWithDP').attr('data-action', 'RESTORE');
            });
        });

        $('.bulkActionBtn').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var action = $theBtn.attr('data-action');
            var ids = [];

            $('#classPlanDateListsTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.ids').val());
            });

            if(ids.length > 0){
                confirmModalDP.show();
                document.getElementById('confirmModalDP').addEventListener('shown.tw.modal', function(event){
                    $('#confirmModalDP .confModTitleDP').html(confModalDelTitle);
                    $('#confirmModalDP .confModDescDP').html('Do you really want to proceed with the selected action?');
                    $('#confirmModalDP .agreeWithDP').attr('data-id', ids.join(','));
                    $('#confirmModalDP .agreeWithDP').attr('data-action', action);
                });
            }else{
                table.init();
            }
        })

        // Confirm Modal Action
        $('#confirmModalDP .agreeWithDP').on('click', function(e){
            e.preventDefault();
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalDP button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('plan.dates.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalDP button').removeAttr('disabled');
                        confirmModalDP.hide();

                        successModalDP.show();
                        document.getElementById("successModalDP").addEventListener("shown.tw.modal", function (event) {
                            $("#successModalDP .successModalTitleDP").html("Congratulation!" );
                            $("#successModalDP .successModalDescDP").html('Class Plan date successfully deleted form the list.');
                        }); 
                    }
                    classPlanDateListsTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('plan.dates.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalDP button').removeAttr('disabled');
                        confirmModalDP.hide();

                        successModalDP.show();
                        document.getElementById("successModalDP").addEventListener("shown.tw.modal", function (event) {
                            $("#successModalDP .successModalTitleDP").html("Congratulation!" );
                            $("#successModalDP .successModalDescDP").html('Class Plan date successfully restored to the list.');
                        }); 
                    }
                    classPlanDateListsTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'DELETEALL' || action == 'RESTOREALL'){
                axios({
                    method: 'post',
                    url: route('plan.dates.bulk.action'),
                    data: {ids : recordID, 'action' : action},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalDP button').removeAttr('disabled');
                        confirmModalDP.hide();

                        successModalDP.show();
                        document.getElementById('successModalDP').addEventListener('shown.tw.modal', function(event){
                            $('#successModalDP .successModalTitleDP').html('Success!');
                            $('#successModalDP .successModalDescDP').html('Bulk action successfully completed.');
                        });

                        setTimeout(function(){
                            succModal.hide();
                        }, 2000)
                    }
                    classPlanDateListsTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
    }
})();