import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import IMask from 'imask';
import TomSelect from "tom-select";
import { set } from "lodash";
 
("use strict");
var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let reportItAllId = $('#reportItAllTableId').data('report_id');
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#reportItAllTableId", {
            ajaxURL: route("report.it.all.log.list"),
            ajaxParams: { querystr: querystr, status: status, report_it_all_id: reportItAllId },
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
                    title: "Description",
                    field: "description",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Modified At",
                    field: "created_at",
                    headerHozAlign: "left",
                },
                {
                    title: "Last Modified By",
                    field: "created_by",
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
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                            
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Academic Years Details",
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
    let accTomOptions = {
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
    let accTomOptionsMul = {
        ...accTomOptions,
        plugins: {
            ...accTomOptions.plugins,
            remove_button: {
                title: "Remove this item",
            },
        }
    };

    // Tabulator
    if ($("#reportItAllTableId").length) {

        let status = new TomSelect('#status', accTomOptions);

        // Init Table
        table.init();

        // Filter function
        function filterHTMLForm() {
            table.init();
        }

        //On submit filter form
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

        //On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLForm();
        });

        // $(".datepicker").each(function () {
        //     var maskOptions = {
        //         mask: '00-00-0000'
        //     };
        //     var mask = IMask(this, maskOptions);
        // });
        const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        const confirmSecondaryModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmSecondaryModal"));
        let confModalDelTitle = 'Are you sure?';
        let confPermanentModalDelTitle = 'Permanently Delete Alert';


        
        const editModalEl = document.getElementById('editModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModal .acc__input-error').html('');
            $('#editModal input[name="id"]').val('0');
            $('#editForm input[name="status"]').prop('checked', false);
            $('#editModal input').not('input[type=hidden]').val('');
            $('#editModal select').not('select[type=hidden]').val('');
            $('#editModal textarea').val('');
           
            $('#editModal input[name="location"]').val('');
            $('#editModal input[name="student_id"]').val($('#edit_student_id').val());
            $('#editModal input[name="employee_id"]').val($('#edit_employee_id').val());
            $('#editModal input[name="updated_by"]').val($('#edit_updated_by').val());
        });


        const addModalEl = document.getElementById('addModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addModal .acc__input-error').html('');
            $('#addModal input[name="id"]').val('0');
            $('#addModal input').not('input[type=hidden]').val('');
            $('#addModal select').not('select[type=hidden]').val('');
            $('#addModal textarea').val('');
        });

        const confirmModalEl = document.getElementById('confirmModal')
        confirmModalEl.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });


        $('#addForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addForm');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('report.it.all.log.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    document.querySelector('#save').removeAttribute('disabled');
                    document.querySelector("#save svg").style.cssText = "display: none;";
                    addModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Log Note inserted successfully.');
                    });         
                }
                table.init();
                location.reload();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addForm .${key}`).addClass('border-danger')
                            $(`#addForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#reportItAllTableId").on("click", ".edit_btn", function (e) {  
            $('#editForm .Employee_class').hide();
            $('#editForm .Student_class').hide();    
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");
            e.preventDefault();
            $('#editForm textarea').attr('disabled', 'disabled');
            $('.editLoading').show();
            axios({
                method: "get",
                url: route("report.it.all.log.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                 
                $('#editForm textarea').removeAttr('disabled');
                    $('.editLoading').hide();
                    let dataset = response.data;
                    //implement radio button selection
                    

                    $('#editForm textarea[name="description"]').val(dataset.description);

                    $('#editModal input[name="id"]').val(editId);
                    $('#editModal input[name="report_it_all_id"]').val(dataset.report_it_all_id);
                }
            })
            .catch((error) => {
                
                $('#editForm input').removeAttr('disabled');
                $('#editForm select').removeAttr('disabled');
                console.log(error);
            });
        });

        // Update Course Data
        $("#editForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editModal input[name="id"]').val();

            const form = document.getElementById("editForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("report.it.all.log.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#update").removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    editModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Log data successfully updated.');
                    });
                }
                table.init();
                setTimeout(function(){
                    succModal.hide();
                },1000);
            }).catch((error) => {
                document.querySelector("#update").removeAttribute("disabled");
                document.querySelector("#update svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editForm .${key}`).addClass('border-danger')
                            $(`#editForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("Oops!");
                            $("#successModal .successModalDesc").html(message);
                        });
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
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('report.it.all.log.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Data successfully deleted!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('report.it.all.log.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Data Successfully Restored!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
        

        $(document).on('click', '.click-close', function() {
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmSecondaryModal.show();
            document.getElementById('confirmSecondaryModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmSecondaryModal .confModTitle').html(confModalDelTitle);
                $('#confirmSecondaryModal .confModDesc').html('Do you really want close this issue?');
                $('#confirmSecondaryModal .agreeWith').attr('data-id', rowID);
                $('#confirmSecondaryModal .agreeWith').attr('data-action', 'CLOSE');
            });
        });


        $(document).on('click', '.click-open', function() {
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmSecondaryModal.show();
            document.getElementById('confirmSecondaryModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmSecondaryModal .confModTitle').html(confModalDelTitle);
                $('#confirmSecondaryModal .confModDesc').html('Do you really want to re-open this issue?');
                $('#confirmSecondaryModal .agreeWith').attr('data-id', rowID);
                $('#confirmSecondaryModal .agreeWith').attr('data-action', 'OPEN');
            });
        });

        $('#confirmSecondaryModal .agreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmSecondaryModal button').attr('disabled', 'disabled');
            if(action == 'CLOSE'){
            axios({
                    method: 'post',
                    url: route('report.it.all.close', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmSecondaryModal button').removeAttr('disabled');
                        confirmSecondaryModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Report IT entry closed/resolved successfully!');
                        });
                    }
                    location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'OPEN'){
                axios({
                    method: 'post',
                    url: route('report.it.all.reopen', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmSecondaryModal button').removeAttr('disabled');
                        confirmSecondaryModal.hide();
                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Report IT entry re-opened successfully!');
                        });
                    }
                    location.reload();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })
        // Delete Course
        $('#reportItAllTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        
        // delete Final Btn
        $('#reportItAllTableId').on('click', '.delete_final_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to remove these record from system? This action is final and no turning back from it.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        
        // Restore Course
        $('#reportItAllTableId').on('click', '.restore_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Want to restore this from the trash? Please click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }
})();