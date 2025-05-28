import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var employeeTrainingListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let employee = $('#employeeTrainingListTable').attr('data-employee');
        let status = $("#status-ET").val() != "" ? $("#status-ET").val() : "";
        let tableContent = new Tabulator("#employeeTrainingListTable", {
            ajaxURL: route("employee.training.list"),
            ajaxParams: { employee : employee, status: status },
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Provider",
                    field: "provider",
                    headerHozAlign: "left",
                },
                {
                    title: "Location",
                    field: "location",
                    headerHozAlign: "left",
                },
                {
                    title: "Start - End",
                    field: "start_date",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return cell.getData().start_date+' - '+cell.getData().end_date;
                    }
                },
                {
                    title: "Cost",
                    field: "cost",
                    headerHozAlign: "left",
                },
                {
                    title: "Expire Date",
                    field: "expire_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "220",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if(cell.getData().employee_document_id > 0){
                            btns +='<a data-id="'+cell.getData().employee_document_id+'" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        }
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editTraininglModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-ET").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx-ET").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        // Print
        $("#tabulator-print-ET").on("click", function (event) {
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
    if ($("#employeeTrainingListTable").length) {
        // Init Table
        employeeTrainingListTable.init();

        // Filter function
        function filterHTMLFormET() {
            employeeTrainingListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go-ET").on("click", function (event) {
            filterHTMLFormET();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-ET").on("click", function (event) {
            $("#status-ET").val("1");
            filterHTMLFormET();
        });

    }

    const addTraininglModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTraininglModal"));
    const editTraininglModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTraininglModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const addTraininglModalEl = document.getElementById('addTraininglModal')
    addTraininglModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addTraininglModal .acc__input-error').html('');
        $('#addTraininglModal .modal-body input:not([type="checkbox"])').val('');
    });
    const editTraininglModalEl = document.getElementById('editTraininglModal')
    addTraininglModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editTraininglModal .acc__input-error').html('');
        $('#editTraininglModal .modal-body input:not([type="checkbox"])').val('');
        $('#editTraininglModal .modal-footer [name="id"]').val('0');
    });

    $('#addTraininglModal').on('change', '#addTraiDocument', function(){
        showFileName('addTraiDocument', 'addTraiDocumentName');
    });

    $('#editTraininglModal').on('change', '#editTraiDocument', function(){
        showFileName('editTraiDocument', 'editTraiDocumentName');
    });

    function showFileName(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = fileInput.files[0].name;
        namePreview.innerText = fileName;
        return false;
    };

    $('#addTraininglForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addTraininglForm');
    
        document.querySelector('#saveTraining').setAttribute('disabled', 'disabled');
        document.querySelector("#saveTraining svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addTraininglForm input[name="document"]')[0].files[0]); 
        axios({
            method: "post",
            url: route('employee.training.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveTraining').removeAttribute('disabled');
            document.querySelector("#saveTraining svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addTraininglModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee training successfully inserted.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000)
            }
            employeeTrainingListTable.init();
        }).catch(error => {
            document.querySelector('#saveTraining').removeAttribute('disabled');
            document.querySelector("#saveTraining svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addTraininglForm .${key}`).addClass('border-danger');
                        $(`#addTraininglForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    
    $('#employeeTrainingListTable').on('click', '.edit_btn', function(e){
        e.preventDefault();
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "POST",
            url: route("employee.training.edit"),
            data: {editId : editId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),},
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;
                $('#editTraininglModal input[name="name"]').val(dataset.name ? dataset.name : '');
                $('#editTraininglModal input[name="provider"]').val(dataset.name ? dataset.provider : '');
                $('#editTraininglModal input[name="location"]').val(dataset.name ? dataset.location : '');
                $('#editTraininglModal input[name="training_date"]').val(dataset.start_date && dataset.end_date ? dataset.start_date+' - '+dataset.end_date : '');
                $('#editTraininglModal input[name="cost"]').val(dataset.cost ? dataset.cost : '');
                $('#editTraininglModal input[name="expire_date"]').val(dataset.expire_date ? dataset.expire_date : '');

                
                $('#editTraininglModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editTraininglForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editTraininglForm');
    
        document.querySelector('#updateTraining').setAttribute('disabled', 'disabled');
        document.querySelector("#updateTraining svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#editTraininglForm input[name="document"]')[0].files[0]);
        axios({
            method: "post",
            url: route('employee.training.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateTraining').removeAttribute('disabled');
            document.querySelector("#updateTraining svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editTraininglModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee training successfully updated.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000)
            }
            employeeTrainingListTable.init();
        }).catch(error => {
            document.querySelector('#updateTraining').removeAttribute('disabled');
            document.querySelector("#updateTraining svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editTraininglForm .${key}`).addClass('border-danger');
                        $(`#editTraininglForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Course
    $('#employeeTrainingListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEETR');
        });
    });

    // Restore Course
    $('#employeeTrainingListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREETR');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEETR'){
            axios({
                method: 'delete',
                url: route('employee.training.destory'),
                data: {recordID : recordID},
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
                employeeTrainingListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTOREETR'){
            axios({
                method: 'post',
                url: route('employee.training.restore'),
                data: {recordID : recordID},
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
                employeeTrainingListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } 
    });

    $('#employeeTrainingListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('employee.documents.download.url'), 
            data: {row_id : row_id},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200){
                let res = response.data.res;
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});

                if(res != ''){
                    window.open(res, '_blank');
                }
            } 
        }).catch(error => {
            if(error.response){
                $theLink.css({'opacity' : '1', 'cursor' : 'pointer'});
                console.log('error');
            }
        });
    });

})();