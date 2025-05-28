import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import { min } from "lodash";

("use strict");
var studentProofOfIdCheckTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-PIC").val() != "" ? $("#query-PIC").val() : "";
        let status = $("#status-PIC").val() != "" ? $("#status-PIC").val() : "";
        let student = $('#studentProofOfIdCheckTable').attr('data-student');

        let tableContent = new Tabulator("#studentProofOfIdCheckTable", {
            ajaxURL: route("student.proof.id.check.list"),
            ajaxParams: { student: student, querystr: querystr, status: status },
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
                    minWidth: 80,
                },
                {
                    title: "Proof Type",
                    field: "proof_type",
                    headerHozAlign: "left",
                    minWidth: 100,
                },
                {
                    title: "Proof ID",
                    field: "proof_id",
                    headerHozAlign: "left",
                    minWidth: 120,
                },
                {
                    title: "Proof Expire Date",
                    field: "proof_expiredate",
                    headerHozAlign: "left",
                    minWidth: 180,
                    formatter(cell, formatterParams) {
                        return `<span class="whitespace-nowrap">${cell.getData().proof_expiredate}</span>`
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    download: false,
                    minWidth: 120,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editProoOfIdCheckModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
        $("#tabulator-export-csv-PIC").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-PIC").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-PIC").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Student Proof Id Check Details",
            });
        });

        $("#tabulator-export-html-PIC").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-PIC").on("click", function (event) {
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
    if ($("#studentProofOfIdCheckTable").length) {
        // Init Table
        studentProofOfIdCheckTable.init();

        // Filter function
        function filterHTMLFormPCI() {
            studentProofOfIdCheckTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-PIC")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLFormPCI();
                }
            }
        );

        // On click go button
        $("#tabulator-html-filter-go-PIC").on("click", function (event) {
            filterHTMLFormPCI();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-PIC").on("click", function (event) {
            $("#query-PIC").val("");
            $("#status-PIC").val("1");
            filterHTMLFormPCI();
        });
    }


    const addProoOfIdCheckModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addProoOfIdCheckModal"));
    const editProoOfIdCheckModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editProoOfIdCheckModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

    document.getElementById('addProoOfIdCheckModal').addEventListener('hide.tw.modal', function(event) {
        $('#addProoOfIdCheckModal .acc__input-error').html('');
        $('#addProoOfIdCheckModal .modal-body input').val('');
        $('#addProoOfIdCheckModal .modal-body select').val('');
    });
    document.getElementById('editProoOfIdCheckModal').addEventListener('hide.tw.modal', function(event) {
        $('#editProoOfIdCheckModal .acc__input-error').html('');
        $('#editProoOfIdCheckModal .modal-body input').val('');
        $('#editProoOfIdCheckModal .modal-body select').val('');
        $('#editProoOfIdCheckModal input[name="id"]').val('0');
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    $('#addProoOfIdCheckForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addProoOfIdCheckForm');
    
        document.querySelector('#addPIC').setAttribute('disabled', 'disabled');
        document.querySelector("#addPIC svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.proof.id.check.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                document.querySelector('#addPIC').removeAttribute('disabled');
                document.querySelector("#addPIC svg").style.cssText = "display: none;";

                addProoOfIdCheckModal.hide();
                studentProofOfIdCheckTable.init();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student Proof of ID check successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });      
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
        }).catch(error => {
            document.querySelector('#addPIC').removeAttribute('disabled');
            document.querySelector("#addPIC svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addProoOfIdCheckForm .${key}`).addClass('border-danger');
                        $(`#addProoOfIdCheckForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#studentProofOfIdCheckTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("student.proof.id.check.edit", editId),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data;
                $('#editProoOfIdCheckModal select[name="proof_type"]').val(dataset.proof_type ? dataset.proof_type : '');
                $('#editProoOfIdCheckModal input[name="proof_id"]').val(dataset.proof_id ? dataset.proof_id : '');
                $('#editProoOfIdCheckModal input[name="proof_expiredate"]').val(dataset.proof_expiredate ? dataset.proof_expiredate : '');
                
                $('#editProoOfIdCheckModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editProoOfIdCheckForm").on("submit", function (e) {
        e.preventDefault();
        let editId = $('#editProoOfIdCheckForm input[name="id"]').val();
        const form = document.getElementById("editProoOfIdCheckForm");

        document.querySelector('#editPIC').setAttribute('disabled', 'disabled');
        document.querySelector('#editPIC svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);

        axios({
            method: "post",
            url: route("student.proof.id.check.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#editPIC").removeAttribute("disabled");
                document.querySelector("#editPIC svg").style.cssText = "display: none;";

                editProoOfIdCheckModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student Proof of ID check successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                });
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            studentProofOfIdCheckTable.init();
        }).catch((error) => {
            document.querySelector("#editPIC").removeAttribute("disabled");
            document.querySelector("#editPIC svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editProoOfIdCheckForm .${key}`).addClass('border-danger')
                        $(`#editProoOfIdCheckForm  .error-${key}`).html(val)
                    }
                }else if (error.response.status == 304) {
                    editProoOfIdCheckModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Oops!" );
                        $("#successModal .successModalDesc").html('No change found!');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                
                    setTimeout(function(){
                        successModal.hide();
                    }, 2000);
                } else {
                    console.log("error");
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
        if(action == 'DELETEPIC'){
            axios({
                method: 'delete',
                url: route('student.proof.id.check.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student Proof of ID check successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                studentProofOfIdCheckTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREPIC'){
            axios({
                method: 'post',
                url: route('student.proof.id.check.restore', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $("#successModal .successModalTitle").html("Congratulation!" );
                        $("#successModal .successModalDesc").html('Student Proof of ID check successfully updated.');
                        $("#successModal .successCloser").attr('data-action', 'NONE');
                    });
                }
                studentProofOfIdCheckTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

    // Delete Course
    $('#studentProofOfIdCheckTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are your Sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEPIC');
        });
    });

    // Restore Course
    $('#studentProofOfIdCheckTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are your Sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREPIC');
        });
    });

})();