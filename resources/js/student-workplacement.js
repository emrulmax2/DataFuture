import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var studentWorkPlacementTable = (function () {
    var _tableGen = function () {
        let student_id = $('#studentWorkPlacementTable').attr('data-student');
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#studentWorkPlacementTable", {
            ajaxURL: route("student.work.placement.hour.list"),
            ajaxParams: { status: status, student_id : student_id},
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
                    width: "70",
                    minWidth: 30,
                },
                {
                    title: "Company",
                    field: "company",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                },
                {
                    title: "Supervisor",
                    field: "supervisor",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                },
                {
                    title: "Start",
                    field: "start_date",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "End",
                    field: "end_date",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Hours",
                    field: "hours",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Contract Type",
                    field: "contract_type",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Created",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    minWidth: 180,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editHourModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
                sheetName: "Semester Details",
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

(function(){
    if ($("#studentWorkPlacementTable").length) {
        // Init Table
        studentWorkPlacementTable.init();

        // Filter function
        function filterHTMLForm() {
            studentWorkPlacementTable.init();
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
            $("#status").val("1");
            filterHTMLForm();
        });
    }


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addHourModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHourModal"));
    const editHourModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHourModal"));

    const addHourModalEl = document.getElementById('addHourModal')
    addHourModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addHourModal .acc__input-error').html('');
        $('#addHourModal .modal-body select').val('');
        $('#addHourModal .modal-body input').val('');
        $('#addHourModal .modal-body select[name="company_supervisor_id"]').html('<option value="">Please Select</option>');
    });

    const editHourModalEl = document.getElementById('editHourModal')
    editHourModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editHourModal .acc__input-error').html('');
        $('#editHourModal .modal-body select').val('');
        $('#editHourModal .modal-body input').val('');
        $('#editHourModal .modal-body select[name="company_supervisor_id"]').html('<option value="">Please Select</option>');

        $('#editHourModal .modal-footer [name="id"]').val('0');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    })

    $('[name="company_id"]').on('change', function(e){
        e.preventDefault();
        let $theSelect = $(this);
        let $supervisorWrap = $theSelect.parent('div').siblings('.supervisorWrap');

        let theCompany = $theSelect.val();
        if(theCompany != '' && theCompany > 0){
            $('[name="company_supervisor_id"]', $supervisorWrap).val('').html('<option value="">Please Select</option>');
            axios({
                method: "post",
                url: route('student.get.company.supervisor'),
                data: {theCompany : theCompany},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('[name="company_supervisor_id"]', $supervisorWrap).val('').html(response.data.res);
            }).catch(error => {
                if (error.response.status == 422) {
                    console.log('error');
                }
            });
        }else{
            $('[name="company_supervisor_id"]', $supervisorWrap).val('').html('<option value="">Please Select</option>');
        }
    });

    $('#addHourForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addHourForm');
    
        document.querySelector('#saveWP').setAttribute('disabled', 'disabled');
        document.querySelector("#saveWP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.work.placement.hour'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveWP').removeAttribute('disabled');
            document.querySelector("#saveWP svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addHourModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student work placement hours successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#saveWP').removeAttribute('disabled');
            document.querySelector("#saveWP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addHourForm .${key}`).addClass('border-danger');
                        $(`#addHourForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    
    $("#studentWorkPlacementTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let row_id = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("student.edit.work.placement.hour", row_id),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;

                $('#editHourModal [name="company_id"]').val(dataset.company_id ? dataset.company_id : '');
                $('#editHourModal [name="company_supervisor_id"]').html(dataset.supervisor_html ? dataset.supervisor_html : '<option value="">Please Select</option>');
                $('#editHourModal [name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                $('#editHourModal [name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                $('#editHourModal [name="hours"]').val(dataset.hours ? dataset.hours : '');
                $('#editHourModal [name="contract_type"]').val(dataset.contract_type ? dataset.contract_type : '');

                $('#editHourModal [name="id"]').val(row_id ? row_id : '');
                
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editHourForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editHourForm');
    
        document.querySelector('#updateWP').setAttribute('disabled', 'disabled');
        document.querySelector("#updateWP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.work.placement.hour'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateWP').removeAttribute('disabled');
            document.querySelector("#updateWP svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editHourModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student work placement hours successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#updateWP').removeAttribute('disabled');
            document.querySelector("#updateWP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editHourForm .${key}`).addClass('border-danger');
                        $(`#editHourForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#studentWorkPlacementTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEWPH');
        });
    });

    // Restore Course
    $('#studentWorkPlacementTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREWPH');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEWPH'){
            axios({
                method: 'delete',
                url: route('student.destroy.work.placement.hour', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREWPH'){
            axios({
                method: 'post',
                url: route('student.restore.work.placement.hour'),
                data: {row_id : recordID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        $("#successModal .successCloser").attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    })

})();