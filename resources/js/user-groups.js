import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var myGroupListTable = (function () {
    var tableContent = null;
    var _tableGen = function () {
        if (tableContent) {
            tableContent.destroy();
        }

        // Setup Tabulator
        let status = $("#status").val() != "" ? $("#status").val() : "";

        tableContent = new Tabulator("#myGroupListTable", {
            ajaxURL: route("user.account.group.list"),
            ajaxParams: { status: status },
            ajaxFiltering: true,
            ajaxSorting: true,
            printAsHtml: true,
            printStyled: true,
            pagination: "remote",
            paginationSize: 10,
            paginationSizeSelector: [10, 25, 50, true],
            paginationCounter: "rows",
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
                    title: "Type",
                    field: "type",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var label = cell.getData().type == 1 ? "Private" : "Public";
                        return '<span class="myhr-groups-type-pill">'+label+'</span>';
                    }
                },
                {
                    title: "No of Members",
                    field: "members",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    formatter(cell, formatterParams){
                        return '<a data-id="'+cell.getData().id +'" href="javascript:void(0);" class="viewGroupMembers myhr-groups-member-count">'+cell.getData().members+'</a>';
                    }
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
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editGroupModal" type="button" class="edit_btn myhr-groups-action-btn myhr-groups-action-btn--edit" title="Edit"><i data-lucide="pencil" class="w-4 h-4"></i></button>';
                            btns += '<button data-id="' +cell.getData().id +'" class="delete_btn myhr-groups-action-btn myhr-groups-action-btn--delete" title="Delete"><i data-lucide="trash-2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'" class="restore_btn myhr-groups-action-btn myhr-groups-action-btn--restore" title="Restore"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv").off("click.myGroupsExport").on("click.myGroupsExport", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-xlsx").off("click.myGroupsExport").on("click.myGroupsExport", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "My Groups",
            });
        });

        // Print
        $("#tabulator-print").off("click.myGroupsPrint").on("click.myGroupsPrint", function (event) {
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
    if ($("#myGroupListTable").length) {
        myGroupListTable.init();
        
        // Filter function
        function filterTitleHTMLForm() {
            myGroupListTable.init();
        }


        // On click go button
        $("#tabulator-html-filter-go").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#status").val("1");
            filterTitleHTMLForm();
        });
    }


    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));
    const addGroupModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addGroupModal"));
    const editGroupModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editGroupModal"));
    const groupMembersModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#groupMembersModal"));
    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    });

    let tomOptions = {
        plugins: {
            dropdown_input: {},
            remove_button: {
                title: "Remove this item",
            },
        },
        dropdownParent: 'body',
        placeholder: 'Search Here...',
        //persist: false,
        create: true,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };

    var employee_ids = new TomSelect('#employee_ids', tomOptions);
    var edit_employee_ids = new TomSelect('#edit_employee_ids', tomOptions);

    const addGroupModalEl = document.getElementById('addGroupModal')
    addGroupModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addGroupModal .acc__input-error').html('');
        $('#addGroupModal .modal-body input:not([type="radio"])').val('');
        $('#addGroupModal #group_type_1').prop('checked', true);

        employee_ids.clear(true);
    });

    const editGroupModalEl = document.getElementById('editGroupModal')
    editGroupModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editGroupModal .acc__input-error').html('');
        $('#editGroupModal .modal-body input:not([type="radio"])').val('');
        $('#editGroupModal #edit_group_type_1').prop('checked', true);
        $('#editGroupModal #edit_group_type_2').prop('checked', false);

        edit_employee_ids.clear(true);
    });

    $("#myGroupListTable").on("click", ".viewGroupMembers", function (e) {
        e.preventDefault();
        var rowId = $(this).attr("data-id");
        var $modalBody = $("#groupMembersModal .modal-body");

        $modalBody.html(
            '<div class="myhr-group-members-loading"><svg width="28" viewBox="-2 -2 42 42" xmlns="http://www.w3.org/2000/svg" stroke="#0f7b76"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="4"><circle stroke-opacity=".25" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"></animateTransform></path></g></g></svg></div>'
        );
        groupMembersModal.show();

        axios({
            method: "post",
            url: route("user.account.group.members"),
            data: { row_id: rowId },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            $modalBody.html(response.data.html || "");
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }).catch(() => {
            $modalBody.html('<div class="myhr-group-members__empty"><i data-lucide="alert-octagon"></i><p>Unable to load group members.</p></div>');
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });
    });

    $('#addGroupForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('addGroupForm');
    
        document.querySelector('#createGroup').setAttribute('disabled', 'disabled');
        document.querySelector("#createGroup svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('user.account.group.store'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#createGroup').removeAttribute('disabled');
            document.querySelector("#createGroup svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                addGroupModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Group successfully created.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            myGroupListTable.init();
        }).catch(error => {
            document.querySelector('#createGroup').removeAttribute('disabled');
            document.querySelector("#createGroup svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $form.find(`[name="${key}"], [name="${key}[]"]`).addClass('border-danger');
                        $form.find(`.error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $("#myGroupListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let row_id = $editBtn.attr("data-id");

        axios({
            method: "post",
            url: route("user.account.group.edit"),
            data: {'row_id' : row_id},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;
                let member_ids = dataset.member_ids;
                $('#editGroupModal input[name="name"]').val(dataset.name ? dataset.name : '');
                $('#editGroupModal input[name="id"]').val(row_id);

                if(member_ids.length > 0){
                    for (var employee_id of member_ids) {
                        edit_employee_ids.addItem(employee_id, true);
                    }
                }else{
                    edit_employee_ids.clear(true);
                }

                if(dataset.type == 1){
                    $('#editGroupModal #edit_group_type_1').prop('checked', true);
                }else{
                    $('#editGroupModal #edit_group_type_2').prop('checked', true);
                }
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editGroupForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('editGroupForm');
    
        document.querySelector('#updateGroup').setAttribute('disabled', 'disabled');
        document.querySelector("#updateGroup svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('user.account.group.update'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateGroup').removeAttribute('disabled');
            document.querySelector("#updateGroup svg").style.cssText = "display: none;";
            
            if (response.status == 200) {
                editGroupModal.hide();
                
                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html( "Congratulations!" );
                    $("#successModal .successModalDesc").html('Group successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'NONE');
                }); 
                
                setTimeout(function(){
                    successModal.hide();
                }, 2000);
            }
            myGroupListTable.init();
        }).catch(error => {
            document.querySelector('#updateGroup').removeAttribute('disabled');
            document.querySelector("#updateGroup svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $form.find(`[name="${key}"], [name="${key}[]"]`).addClass('border-danger');
                        $form.find(`.error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    // Delete Course
    $('#myGroupListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let rowID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', rowID);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    // Restore Course
    $('#myGroupListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let courseID = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', courseID);
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
                url: route('user.account.group.destory', recordID),
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
                myGroupListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('user.account.group.restore', recordID),
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
                myGroupListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    })

})();
