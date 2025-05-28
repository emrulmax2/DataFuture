import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var semesterComissionRateTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let semester_id = $("#semester-SCR").val() != "" ? $("#semester-SCR").val() : "";
        let status = $("#status-SCR").val() != "" ? $("#status-SCR").val() : "";

        let tableContent = new Tabulator("#semesterComissionRateTable", {
            ajaxURL: route("semester.comission.rate.list"),
            ajaxParams: { semester_id: semester_id, status: status },
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
                    title: "Semester",
                    field: "semester_name",
                    headerHozAlign: "left",
                },
                {
                    title: "Rate",
                    field: "rate",
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
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#addComissionRateModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        
    };
    return {
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    let mrkTomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        //persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    let mrkTomOptionsMul = {
        ...mrkTomOptions,
        plugins: {
            ...mrkTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    var marketing_semester_id = new TomSelect('#marketing_semester_id', mrkTomOptions);
    var semester_SCR = new TomSelect('#semester-SCR', mrkTomOptions);
    var comr_semester_id = new TomSelect('#comr_semester_id', comr_semester_id);

    function semesterComissionRateTableInit() {
        semesterComissionRateTable.init();
    }

    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const semesterComissionRatModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#semesterComissionRatModal"));
    const addComissionRateModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addComissionRateModal"));
    let confModalDelTitle = 'Are you sure?';

    const semesterComissionRatModalEl = document.getElementById('semesterComissionRatModal')
    semesterComissionRatModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#semesterComissionRateTable').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
        semester_SCR.clear(true);
    });

    semesterComissionRatModalEl.addEventListener('shown.tw.modal', function(event) {
        semesterComissionRateTable.init();
    });

    $("#tabulator-html-filter-go-SCR").on("click", function (event) {
        semesterComissionRateTableInit();
    });

    $("#tabulator-html-filter-reset-SCR").on("click", function (event) {
        semester_SCR.clear(true);
        $("#status-SCR").val("1");
        semesterComissionRateTableInit();
    });

    const addComissionRateModalEl = document.getElementById('addComissionRateModal')
    addComissionRateModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addComissionRateModal .acc__input-error').html('');
        $('#addComissionRateModal [name="comission_rate"]').val('');
        $('#addComissionRateModal [name="id"]').val('0');

        comr_semester_id.clear(true);
    });

    // Save Comission Rate FOrm
    $('#addComissionRateForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addComissionRateForm');
    
        document.querySelector('#saveComRateBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveComRateBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('semester.comission.rate.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveComRateBtn').removeAttribute('disabled');
            document.querySelector("#saveComRateBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addComissionRateModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Semester comission rate successfully saved.');
                });     
            }
            semesterComissionRateTableInit();
        }).catch(error => {
            document.querySelector('#saveComRateBtn').removeAttribute('disabled');
            document.querySelector("#saveComRateBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addComissionRateForm .${key}`).addClass('border-danger');
                        $(`#addComissionRateForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Get Edit Data pupulated
    $("#semesterComissionRateTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "POST",
            url: route("semester.comission.rate.edit"),
            data: {row_id : editId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") },
        }).then((response) => {
            if (response.status == 200) {
                let row = response.data.row;
                $('#addComissionRateModal [name="comission_rate"]').val(row.rate ? row.rate : '');
                $('#addComissionRateModal [name="id"]').val(editId);
                
                if(row.semester_id > 0){
                    comr_semester_id.addItem(row.semester_id);
                }else{
                    comr_semester_id.clear(true);
                }
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    // Delete Course
    $('#semesterComissionRateTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETECRT');
        });
    });

    // Restore Course
    $('#semesterComissionRateTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORECRT');
        });
    });

    // Confirm Modal Action
    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETECRT'){
            axios({
                method: 'delete',
                url: route('semester.comission.rate.destory', recordID),
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
                semesterComissionRateTableInit();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORECRT'){
            axios({
                method: 'post',
                url: route('semester.comission.rate.restore', recordID),
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
                semesterComissionRateTableInit();
            }).catch(error =>{
                console.log(error)
            });
        }
    })


    //Marketing Report Form Submit
    $('#accountMarketingReportForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('accountMarketingReportForm');
    
        document.querySelector('#markRepBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#markRepBtn svg.theLoader").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('reports.account.marketing.generate'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#markRepBtn').removeAttribute('disabled');
            document.querySelector("#markRepBtn svg.theLoader").style.cssText = "display: none;";
            
            if (response.status == 200) {
                $('#accountsMarketingReportWrap').fadeIn('fast', function(){
                    $('#accountsMarketingReportWrap').html(response.data.html);
                })
            }
        }).catch(error => {
            document.querySelector('#markRepBtn').removeAttribute('disabled');
            document.querySelector("#markRepBtn svg.theLoader").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#accountMarketingReportForm .${key}`).addClass('border-danger');
                        $(`#accountMarketingReportForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
})()