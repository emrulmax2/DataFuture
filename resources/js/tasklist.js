import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var taskListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let processlist = $("#processlists-01").val() != "" ? $("#processlists-01").val() : "";

        let tableContent = new Tabulator("#taskTableId", {
            ajaxURL: route("tasklist.list"),
            ajaxParams: { querystr: querystr, status: status, processlist: processlist},
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
                    headerSort: false,
                    field: "id",
                    width: "70",
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div>';
                                html += '<span class="font-medium">'+cell.getData().processlist+'</span><br/>';
                                html += '<span>'+cell.getData().name+'</span>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Interview",
                    field: "interview",
                    headerHozAlign: "left",
                },
                {
                    title: "Upload",
                    field: "upload",
                    headerHozAlign: "left",
                },
                {
                    title: "Ex. Link",
                    field: "external_link",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                },
                {
                    title: "Email",
                    field: "org_email",
                    headerHozAlign: "left",
                },
                {
                    title: "ID Card",
                    field: "id_card",
                    headerHozAlign: "left",
                },
                {
                    title: "Assigned User",
                    field: "user",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        return '<div style="white-space: normal;">'+cell.getData().user+'</div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "120",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            if(cell.getData().external_link_ref != '' && cell.getData().external_link_ref != null){
                                btns += '<a target="_blank" href="'+cell.getData().external_link_ref+'" class="btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="link" class="w-4 h-4"></i></a>';
                            }
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editTaskModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Tasks List",
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
    if ($("#taskTableId").length) {
        // Init Table
        taskListTable.init();

        // Filter function
        function filterHTMLForm() {
            taskListTable.init();
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
            $("#status").val("");
            filterHTMLForm();
        });

        let tomOptions = {
            plugins: {
                dropdown_input: {},
                remove_button: {
                    title: "Remove this item",
                }
            },
            placeholder: 'Search Here...',
            persist: false,
            create: true,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };

        var assignedUserAdd = new TomSelect('#assigned_users', tomOptions);
        var assignedUserEdit = new TomSelect('#edit_assigned_users', tomOptions);

        const addModal  = tailwind.Modal.getOrCreateInstance(document.querySelector("#addTaskModal"));
        const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editTaskModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        let confModalDelTitle = 'Are you sure?';
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
            $('#confirmModal .agreeWith').attr('data-id', '0');
            $('#confirmModal .agreeWith').attr('data-action', 'none');
        });

        const taskUserModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#taskUserModal"));
        document.getElementById('taskUserModal').addEventListener('hidden.tw.modal', function(event){
            $('#taskUserModal .taskUserModalContent').fadeOut('fast', function(){
                $('table tbody', this).html('');
            });
            $('#taskUserModal .taskUserModalLoader').fadeIn();
        });

        const addModalEl = document.getElementById('addTaskModal')
        addModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addTaskModal .acc__input-error').html('');
            $('#addTaskModal input:not([type="radio"]):not([type="checkbox"])').val('');
            $('#addTaskModal select').val('');
            $('#addTaskModal input[type="checkbox"]').prop('checked', false);
            $('#addTaskModal input[type="radio"][value="No"]').prop('checked', true);
            $('#addTaskModal .extarnalUrlWrap').fadeOut('fast', function(){
                $('#addTaskModal input[name="external_link_ref"]').val('')
            });
            $('#addTaskModal .taskStatusesWrap').fadeOut('fast', function(){
                $('#addTaskModal .taskStatusesWrap input[type="checkbox"]').prop('checked', false)
            });
            assignedUserAdd.clear(true);
        });
        
        const editModalEl = document.getElementById('editTaskModal')
        editModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editTaskModal .acc__input-error').html('');
            $('#addTaskModal input:not([type="radio"]):not([type="checkbox"])').val('');
            $('#addTaskModal select').val('');
            $('#addTaskModal input[type="checkbox"]').prop('checked', false);
            $('#editTaskModal input[name="id"]').val('0');
            $('#editTaskModal .extarnalUrlWrap').fadeOut('fast', function(){
                $('#editTaskModal input[name="external_link_ref"]').val('')
            })
            $('#editTaskModal .taskStatusesWrap').fadeOut('fast', function(){
                $('#editTaskModal .taskStatusesWrap input[type="checkbox"]').prop('checked', false)
            });
            assignedUserEdit.clear(true);
        });

        $('#addTaskForm input[name="external_link"]').on('change', function(){
            if($(this).prop('checked')){
                $('#addTaskForm .extarnalUrlWrap').fadeIn('fast', function(){
                    $('#addTaskForm input[name="external_link_ref"]').val('')
                })
            }else{
                $('#addTaskForm .extarnalUrlWrap').fadeOut('fast', function(){
                    $('#addTaskForm input[name="external_link_ref"]').val('')
                })
            }
        })

        $('#editTaskForm input[name="external_link"]').on('change', function(){
            if($(this).prop('checked')){
                $('#editTaskForm .extarnalUrlWrap').fadeIn('fast', function(){
                    $('#editTaskForm input[name="external_link_ref"]').val('')
                })
            }else{
                $('#editTaskForm .extarnalUrlWrap').fadeOut('fast', function(){
                    $('#editTaskForm input[name="external_link_ref"]').val('')
                })
            }
        })

        $('#addTaskForm input[name="status"]').on('change', function(){
            if($('#addTaskForm input[name="status"]:checked').val() == 'Yes'){
                $('#addTaskForm .taskStatusesWrap').fadeIn('fast', function(){
                    $('#addTaskForm .taskStatusesWrap input[type="checkbox"]').prop('checked', false);
                })
            }else{
                $('#addTaskForm .taskStatusesWrap').fadeOut('fast', function(){
                    $('#addTaskForm .taskStatusesWrap input[type="checkbox"]').prop('checked', false);
                })
            }
        })

        $('#editTaskForm input[name="status"]').on('change', function(){
            if($('#editTaskForm input[name="status"]:checked').val() == 'Yes'){
                $('#editTaskForm .taskStatusesWrap').fadeIn('fast', function(){
                    $('#editTaskForm .taskStatusesWrap input[type="checkbox"]').prop('checked', false);
                })
            }else{
                $('#editTaskForm .taskStatusesWrap').fadeOut('fast', function(){
                    $('#editTaskForm .taskStatusesWrap input[type="checkbox"]').prop('checked', false);
                })
            }
        })

        $('#addTaskForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addTaskForm');
        
            document.querySelector('#save').setAttribute('disabled', 'disabled');
            document.querySelector("#save svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('tasklist.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Success!");
                        $("#successModal .successModalDesc").html('Task list item successfully inserted');
                    });                
                        
                }
                taskListTable.init();
            }).catch(error => {
                document.querySelector('#save').removeAttribute('disabled');
                document.querySelector("#save svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addTaskForm .${key}`).addClass('border-danger')
                            $(`#addTaskForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#taskTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("tasklist.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editTaskModal select[name="process_list_id"]').val(dataset.process_list_id ? dataset.process_list_id : '');
                        $('#editTaskModal input[name="name"]').val(dataset.name ? dataset.name : '');
                        $('#editTaskModal input[name="short_description"]').val(dataset.short_description ? dataset.short_description : '');
                        if(dataset.interview == 'Yes'){
                            $('#editTaskModal input[name="interview"][value="Yes"]').prop('checked', true);
                        }else{
                            $('#editTaskModal input[name="interview"][value="No"]').prop('checked', true);
                        }
                        if(dataset.upload == 'Yes'){
                            $('#editTaskModal input[name="upload"][value="Yes"]').prop('checked', true);
                        }else{
                            $('#editTaskModal input[name="upload"][value="No"]').prop('checked', true);
                        }
                        if(dataset.org_email == 'Yes'){
                            $('#editTaskModal input[name="org_email"][value="Yes"]').prop('checked', true);
                        }else{
                            $('#editTaskModal input[name="org_email"][value="No"]').prop('checked', true);
                        }
                        if(dataset.id_card == 'Yes'){
                            $('#editTaskModal input[name="id_card"][value="Yes"]').prop('checked', true);
                        }else{
                            $('#editTaskModal input[name="id_card"][value="No"]').prop('checked', true);
                        }
                        
                        if(dataset.external_link == 1){
                            $('#editTaskModal input[name="external_link"]').prop('checked', true);
                            $('#editTaskModal .extarnalUrlWrap').fadeIn('fast', function(){
                                $('#editTaskModal input[name="external_link_ref"]').val(dataset.external_link_ref)
                            })
                        }else{
                            $('#editTaskModal input[name="external_link"]').prop('checked', false);
                            $('#editTaskModal .extarnalUrlWrap').fadeOut('fast', function(){
                                $('#editTaskModal input[name="external_link_ref"]').val('')
                            })
                        }

                        if(dataset.users.length > 0){
                            $.each(dataset.users, function(name, value) {
                                assignedUserEdit.addItem(value.user_id, true);
                            });
                        }else{
                            assignedUserEdit.clear(true);
                        }
                                
                        if(dataset.status == 'Yes'){
                            $('#editTaskModal input[name="status"][value="Yes"]').prop('checked', true);
                            $('#editTaskModal .taskStatusesWrap').fadeIn('fast', function(){
                                $('#editTaskModal .taskStatusesWrap input[type="checkbox"]').prop('checked', false);
                                if(dataset.statuses.length > 0){
                                    $.each(dataset.statuses, function(name, value) {
                                        $('#editTaskModal .taskStatusesWrap input[type="checkbox"][value="'+value.task_status_id+'"]').prop('checked', true);
                                    });
                                }else{
                                    $('#editTaskModal .taskStatusesWrap input[type="checkbox"]').prop('checked', false);
                                }
                            })
                        }else{
                            $('#editTaskModal input[name="status"][value="No"]').prop('checked', true);
                            $('#editTaskModal .taskStatusesWrap').fadeOut('fast', function(){
                                $('#editTaskModal .taskStatusesWrap input[type="checkbox"]').prop('checked', false);
                            })
                        }

                        $('#editTaskModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Course Data
        $("#editTaskForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editTaskModal input[name="id"]').val();

            const form = document.getElementById("editTaskForm");

            document.querySelector('#update').setAttribute('disabled', 'disabled');
            document.querySelector('#update svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("tasklist.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        document.querySelector("#update").removeAttribute("disabled");
                        document.querySelector("#update svg").style.cssText = "display: none;";
                        editModal.hide();

                        succModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModalTitle").html("Success!" );
                            $("#successModal .successModalDesc").html('Task list item data successfully updated.');
                        });
                    }
                    taskListTable.init();
                })
                .catch((error) => {
                    document.querySelector("#update").removeAttribute("disabled");
                    document.querySelector("#update svg").style.cssText = "display: none;";
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(error.response.data.errors)) {
                                $(`#editTaskForm .${key}`).addClass('border-danger')
                                $(`#editTaskForm  .error-${key}`).html(val)
                            }
                        }else if (error.response.status == 304) {
                            editModal.hide();

                            let message = error.response.statusText;
                            succModal.show();
                            document.getElementById("successModal")
                                .addEventListener("shown.tw.modal", function (event) {
                                    $("#successModal .successModalTitle").html( "No Data Change!" );
                                    $("#successModal .successModalDesc").html('No data change found.');
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
                    url: route('tasklist.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Data Deleted!');
                        });
                    }
                    taskListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('tasklist.restore', recordID),
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
                    taskListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

         // Delete Course
         $('#taskTableId').on('click', '.delete_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record?');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#taskTableId').on('click', '.restore_btn', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
            document.getElementById('confirmModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmModal .agreeWith').attr('data-id', '0');
                $('#confirmModal .agreeWith').attr('data-action', 'none');
            });
            let $statusBTN = $(this);
            let dataID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record?');
                $('#confirmModal .agreeWith').attr('data-id', dataID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });

        $('#taskTableId').on('click', '.taskUserLoader', function(){
            var task_id = $(this).attr('data-taskid');
            taskUserModal.show();

            axios({
                method: 'post',
                url: route('tasklist.users'),
                data: {task_id : task_id},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#taskUserModal .taskUserModalLoader').fadeOut('fast');
                    $('#taskUserModal .taskUserModalContent').fadeIn('fast', function(){
                        $('table tbody', this).html(response.data.res);
                    });

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error =>{
                console.log(error)
            });
        });
    }
})();