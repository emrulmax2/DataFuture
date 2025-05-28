import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select"; 

("use strict");
var requisitionDocListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        var requisition_id = $("#requisitionDocListTable").attr('data-requisition');
        let querystr = $("#query-RD").val() != "" ? $("#query-RD").val() : "";
        let status = $("#status-RD").val() != "" ? $("#status-RD").val() : "";

        let tableContent = new Tabulator("#requisitionDocListTable", {
            ajaxURL: route("budget.management.req.doc.list"),
            ajaxParams: { requisition_id: requisition_id, querystr: querystr, status: status  },
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
                    width: '120'
                },
                {
                    title: "Name",
                    field: "display_file_name",
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
                        btns +='<a data-id="'+cell.getData().id+'" href="javascript:void(0);" class="downloadDoc btn-rounded btn btn-linkedin text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="cloud-lightning" class="w-4 h-4"></i></a>';
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
    requisitionDocListTable.init();

    function filterRDHTMLForm() {
        requisitionDocListTable.init();
    }

    // On submit filter form
    $("#tabulatorFilterForm-RD")[0].addEventListener(
        "keypress",
        function (event) {
            let keycode = event.keyCode ? event.keyCode : event.which;
            if (keycode == "13") {
                event.preventDefault();
                filterRDHTMLForm();
            }
        }
    );

    // On click go button
    $("#tabulator-html-filter-go-RD").on("click", function (event) {
        filterRDHTMLForm();
    });

    // On reset filter form
    $("#tabulator-html-filter-reset-RD").on("click", function (event) {
        $("#query-RD").val("");
        $("#status-RD").val("1");
        filterRDHTMLForm();
    });

    const addRequisitionDocModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addRequisitionDocModal"));

    const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const successModalEl = document.getElementById('successModal')
    successModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#successModal .successCloser').attr('data-action', 'NONE');
    });

    const addRequisitionDocModalEl = document.getElementById('addRequisitionDocModal')
    addRequisitionDocModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addRequisitionDocModal .acc__input-error').html('');
        $('#addRequisitionDocModal .modal-body input:not([type="checkbox"])').val('');
        $('#addRequisitionDocModal .documentNoteName').html('');
    });
    
    $('#addRequisitionDocModal').on('change', '#addRequiDocument', function(){
        showFileNames('addRequiDocument', 'addRequiDocumentName');
    });

    $('#addRequisitionDocForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addRequisitionDocForm');
    
        document.querySelector('#saveDocBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#saveDocBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        form_data.append('file', $('#addRequisitionDocForm #addRequiDocument')[0].files[0]); 
        axios({
            method: "post",
            url: route('budget.management.req.doc.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveDocBtn').removeAttribute('disabled');
            document.querySelector("#saveDocBtn svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addRequisitionDocModal.hide();

                succModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Requisition documents Successfully uploaded.');
                });  
                
                setTimeout(() => {
                    succModal.hide();
                }, 2000);
            }
            requisitionDocListTable.init();
        }).catch(error => {
            document.querySelector('#saveDocBtn').removeAttribute('disabled');
            document.querySelector("#saveDocBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addRequisitionDocForm .${key}`).addClass('border-danger');
                        $(`#addRequisitionDocForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
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
        if(action == 'DELETERD'){
            axios({
                method: 'delete',
                url: route('budget.management.req.doc.destory', recordID),
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
                requisitionDocListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORERD'){
            axios({
                method: 'post',
                url: route('budget.management.req.doc.restore', recordID),
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
                requisitionDocListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'CHANGESTATRD'){
            axios({
                method: 'post',
                url: route('budget.management.req.doc.update.status', recordID),
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
                requisitionDocListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#requisitionDocListTable').on('click', '.status_updater', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATRD');
        });
    });

    // Delete Course
    $('#requisitionDocListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETERD');
        });
    });

    // Restore Course
    $('#requisitionDocListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORERD');
        });
    });


    function showFileNames(inputId, targetPreviewId) {
        let fileInput = document.getElementById(inputId);
        let namePreview = document.getElementById(targetPreviewId);
        let fileName = '';
        if(fileInput.files.length > 0){
            fileName += '<ul class="m-0">';
            $.each(fileInput.files, function(index, file){
                fileName += '<li class="mb-1 text-primary flex items-center"><i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>'+file.name+'</li>';
            });
            fileName += '</ul>';
        }
        
        $('#'+targetPreviewId).html(fileName);
        createIcons({
            icons,
            "stroke-width": 1.5,
            nameAttr: "data-lucide",
        });

        return false;
    };

    $('#requisitionDocListTable').on('click', '.downloadDoc', function(e){
        e.preventDefault();
        var $theLink = $(this);
        var row_id = $theLink.attr('data-id');

        $theLink.css({'opacity' : '.6', 'cursor' : 'not-allowed'});

        axios({
            method: "post",
            url: route('budget.management.req.doc.download'), 
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
})()