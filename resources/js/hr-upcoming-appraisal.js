import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
 
("use strict");
var upcomingAppraisalListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#upcomingAppraisalListTable", {
            ajaxURL: route("hr.portal.upcoming.appraisal.list"),
            ajaxParams: { status: status },
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<a href="'+cell.getData().url+'" class="block">';
                                html += '<div class="w-10 h-10 intro-x image-fit mr-5 inline-block">';
                                    html += '<img alt="'+cell.getData().name+'" class="rounded-full shadow" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div class="inline-block relative">';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().designation != '' ? cell.getData().designation : 'Unknown')+'</div>';
                                html += '</div>';
                            html += '</a>';
                        return html;
                    }
                },
                {
                    title: "Due On",
                    field: "due_on",
                    headerHozAlign: "left",
                },
                {
                    title: "Completed On",
                    field: "completed_on",
                    headerHozAlign: "left",
                },
                {
                    title: "Next Due",
                    field: "next_due_on",
                    headerHozAlign: "left",
                },
                {
                    title: "Appraised By",
                    field: "appraisedby",
                    headerHozAlign: "left",
                },
                {
                    title: "Reviewed By",
                    field: "reviewedby",
                    headerHozAlign: "left",
                },
                {
                    title: "Total Score",
                    field: "total_score",
                    headerHozAlign: "left",
                },
                {
                    title: "Promotion Consideration",
                    field: "promotion_consideration",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return (cell.getData().promotion_consideration == 1 ? '<span class="btn inline-flex btn-success w-auto px-1 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-1 text-white py-0 rounded-0">No</span>');
                    }
                },
                {
                    title: "Promotion",
                    field: "promotion_consideration",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return (cell.getData().promotion_consideration == 1 ? '<span class="btn inline-flex btn-success w-auto px-1 text-white py-0 rounded-0">Yes</span>' : '<span class="btn inline-flex btn-danger w-auto px-1 text-white py-0 rounded-0">No</span>');
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        return (cell.getData().promotion_consideration == 2 ? '<span class="btn inline-flex btn-danger w-auto px-1 text-white py-0 rounded-0">Overdue</span>' : '<span class="btn inline-flex btn-warning w-auto px-1 text-white py-0 rounded-0">Due</span>');
                    }
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
                        if (cell.getData().deleted_at == null) {
                            
                            if(cell.getData().notes != null){
                                btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#viewAppraisalNoteModal" type="button" class="view_note btn-rounded btn btn-twitter text-white p-0 w-9 h-9 ml-1"><i data-lucide="sticky-note" class="w-4 h-4"></i></button>';
                            }

                            btns +='<a href="'+route('employee.appraisal.documents', [cell.getData().employee_id, cell.getData().id])+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="folder-up" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editAppraisalModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Title Details",
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
    if ($("#upcomingAppraisalListTable").length) {
        upcomingAppraisalListTable.init();
        
        // Filter function
        function filterTitleHTMLForm() {
            upcomingAppraisalListTable.init();
        }

        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }

    let tomOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    var edit_appraised_by = new TomSelect('#edit_appraised_by', tomOptions);
    var edit_reviewed_by = new TomSelect('#edit_reviewed_by', tomOptions);


    const editAppraisalModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editAppraisalModal"));
    const viewAppraisalNoteModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewAppraisalNoteModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    let confModalDelTitle = 'Are you sure?';

    const editAppraisalModalEl = document.getElementById('editAppraisalModal')
    editAppraisalModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editAppraisalModal .acc__input-error').html('');
        $('#editAppraisalModal .modal-body input:not([type="checkbox"])').val('');
        $('#editAppraisalModal .modal-body textarea').val('');

        $('#editAppraisalModal input[name="promotion_consideration"]').prop('checked', false);
        edit_appraised_by.addItem('', true);
        edit_reviewed_by.addItem('', true);
    });

    const viewAppraisalNoteModalEl = document.getElementById('viewAppraisalNoteModal')
    viewAppraisalNoteModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#viewAppraisalNoteModal .modal-body').html('');
    });

    $('#upcomingAppraisalListTable').on('click', '.edit_btn', function(e){
        e.preventDefault();
        let $editBtn = $(this);
        let editId = $editBtn.attr("data-id");

        axios({
            method: "POST",
            url: route("employee.appraisal.edit"),
            data: {editId : editId},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),},
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;
                $('#editAppraisalModal input[name="due_on"]').val(dataset.due_on ? dataset.due_on : '');
                $('#editAppraisalModal input[name="completed_on"]').val(dataset.completed_on ? dataset.completed_on : '');
                $('#editAppraisalModal input[name="next_due_on"]').val(dataset.next_due_on ? dataset.next_due_on : '');
                $('#editAppraisalModal input[name="total_score"]').val(dataset.total_score ? dataset.total_score : '');
                $('#editAppraisalModal textarea').val(dataset.notes ? dataset.notes : '');

                if(dataset.appraised_by > 0){
                    edit_appraised_by.addItem(dataset.appraised_by, true);
                }

                if(dataset.reviewed_by > 0){
                    edit_reviewed_by.addItem(dataset.reviewed_by, true);
                }
                
                if(dataset.promotion_consideration == 1){
                    $('#editAppraisalModal input[name="promotion_consideration"]').prop('checked', true);
                }else{
                    $('#editAppraisalModal input[name="promotion_consideration"]').prop('checked', false);
                }

                $('#editAppraisalModal input[name="id"]').val(editId);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editAppraisalForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editAppraisalForm');
    
        document.querySelector('#updateAppraisal').setAttribute('disabled', 'disabled');
        document.querySelector("#updateAppraisal svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('employee.appraisal.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateAppraisal').removeAttribute('disabled');
            document.querySelector("#updateAppraisal svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editAppraisalModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Employee appraisal successfully updated.');
                });  
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000)
            }
            upcomingAppraisalListTable.init();
        }).catch(error => {
            document.querySelector('#updateAppraisal').removeAttribute('disabled');
            document.querySelector("#updateAppraisal svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editAppraisalForm .${key}`).addClass('border-danger');
                        $(`#editAppraisalForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });


    $('#upcomingAppraisalListTable').on('click', '.view_note', function(e){
        e.preventDefault();
        let $viewNoteBtn = $(this);
        let rowID = $viewNoteBtn.attr("data-id");

        axios({
            method: "POST",
            url: route("employee.appraisal.get.note"),
            data: {rowID : rowID},
            headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),},
        }).then((response) => {
            if (response.status == 200) {
                let note = response.data.notes;
                $('#viewAppraisalNoteModal .modal-body').html(note);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    // Delete Course
    $('#upcomingAppraisalListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#upcomingAppraisalListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html(confModalDelTitle);
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('employee.appraisal.destory'),
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
                upcomingAppraisalListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('employee.appraisal.restore'),
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
                upcomingAppraisalListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } 
    });
})()