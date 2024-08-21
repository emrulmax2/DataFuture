import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
 
("use strict");
var table = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let term = $("#term").val() != "" ? $("#term").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "1";

        let tableContent = new Tabulator("#groupsTableId", {
            ajaxURL: route("groups.list"),
            ajaxParams: { querystr: querystr, status: status, term : term},
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [true, 5, 10, 20, 30, 50, 100, 200, 500, 1000],
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
                    width: "180",
                },
                {
                    title: "Term",
                    field: "term",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Course",
                    field: "course",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Group Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Evening & Weekend",
                    field: "evening_and_weekend",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        return (cell.getData().active == 1 ? '<span class="font-medium text-success">Active</span>' : '<span class="font-medium text-danger">Inactive</span>')
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
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns +='<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +='<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
            },
            rowSelectionChanged:function(data, rows){
                var ids = [];
                const groupActionDropdown1 = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#groupActionDropdown"));
                if(rows.length > 0){
                    $('.groupActions').removeClass('hidden');
                    groupActionDropdown1.hide();
                }else{
                    $('.groupActions').addClass('hidden');
                    groupActionDropdown1.hide();
                }
            },
            selectableCheck:function(row){
                return row.getData().id > 0; //allow selection of rows where the age is greater than 18
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
                sheetName: "Groups Details",
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
    // Tabulator
    if ($("#groupsTableId").length) {
        // Init Table
        table.init();

        // Filter function
        function filterHTMLForm() {
            table.init();
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
            $("#term").val("");
            $("#status").val("1");
            filterHTMLForm();
        });
        
        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            //persist: true,
            create: false,
            allowEmptyOption: false,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };
    
        let course_id = new TomSelect('#course_id', tomOptions);
        let edit_course_id = new TomSelect('#edit_course_id', tomOptions);

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));

        const groupActionDropdown = tailwind.Dropdown.getOrCreateInstance(document.querySelector("#groupActionDropdown"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';


        const addModalEl = document.getElementById('addModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addModal .acc__input-error').html('');
            $('#addModal input[name="name"]').val('');
            $('#addModal select').val('');
            $('#addModal input[name="evening_and_weekend"]').prop('checked', false);
            $('#addModal input[name="active"]').prop('checked', true);
            course_id.clear(true);
        });
        
        const editModalEl = document.getElementById('editModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editModal .acc__input-error').html('');
            $('#editModal input[name="name"]').val('');
            $('#editModal select').val('');
            $('#editModal input[name="evening_and_weekend"]').prop('checked', false);
            $('#addModal input[name="active"]').prop('checked', false);
            $('#editModal input[name="id"]').val('0');
            edit_course_id.clear(true);
        });

        document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
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
                url: route('groups.store'),
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
                        $("#successModal .successModalTitle").html( "Success!" );
                        $("#successModal .successModalDesc").html('Data Inserted');
                    });                
                        
                }
                table.init();
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

        $("#groupsTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("groups.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editModal select[name="term_declaration_id"]').val(dataset.term_declaration_id ? dataset.term_declaration_id : '');
                    $('#editModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    if(dataset.course_id > 0){
                        edit_course_id.addItem(dataset.course_id, true);
                    }else{
                        edit_course_id.clear(true);
                    }

                    if(dataset.evening_and_weekend == 1){
                        $('#editModal input[name="evening_and_weekend"]').prop('checked', true);
                    }else{
                        $('#editModal input[name="evening_and_weekend"]').prop('checked', false);
                    }

                    if(dataset.active == 1){
                        $('#editModal input[name="active"]').prop('checked', true);
                    }else{
                        $('#editModal input[name="active"]').prop('checked', false);
                    }

                    $('#editModal input[name="id"]').val(editId);
                }
            })
            .catch((error) => {
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
                url: route("groups.update", editId),
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
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Data Updated');
                    });
                }
                table.init();
            })
            .catch((error) => {
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
                            $("#successModal .successModalTitle").html("No Data Change!");
                            $("#successModal .successModalDesc").html(message);
                        });
                    } else {
                        console.log("error");
                    }
                }
            });
        });

        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(e){
            e.preventDefault();

            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('groups.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Groups data successfully deleted!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('groups.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Groups data Successfully Restored!');
                        });
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            }else if(action == 'ACTIVEALL' || action == 'INACTIVEALL' || action == 'DELETEALL' || action == 'RESTOREALL'){
                axios({
                    method: 'post',
                    url: route('groups.bulk.action'),
                    data: {ids : recordID, 'action' : action},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Bulk action successfull.');
                        });

                        setTimeout(function(){
                            succModal.hide();
                        }, 2000)
                    }
                    table.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#groupsTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record?  If yes, the please click on agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#groupsTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore this record?');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });

        $('.groupActionBTN').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var action = $theBtn.attr('data-action');
            var ids = [];

            $('#groupsTableId').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.ids').val());
            });

            if(ids.length > 0){
                confModal.show();
                document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                    $('#confirmModal .confModTitle').html(confModalDelTitle);
                    $('#confirmModal .confModDesc').html('Do you really want to proceed with the selected action?');
                    $('#confirmModal .agreeWith').attr('data-id', ids.join(','));
                    $('#confirmModal .agreeWith').attr('data-action', action);
                });
            }else{
                table.init();
            }
        })
    }
})();