import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
 
("use strict");
var userListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let tableContent = new Tabulator("#userListTable", {
            ajaxURL: route("users.list"),
            ajaxParams: { querystr: querystr, status: status },
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
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div class="flex lg:justify-start items-center">';
                            html += '<div class="intro-x w-10 h-10 image-fit mr-3">';
                                html += '<img alt="'+cell.getData().name+'" class="rounded-full" src="'+cell.getData().photo_url+'">';
                            html += '</div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().name+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Email",
                    field: "email",
                    headerHozAlign: "left",
                },
                {
                    title: "Role",
                    field: "roles",
                    headerSort: false,
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        return '<div style="white-space: normal;">'+cell.getData().roles+'</div>';
                    }
                },
                {
                    title: "Gender",
                    field: "gender",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "right",
                    headerHozAlign: "right",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editUsersModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            if(cell.getData().id > 1){
                                btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            }
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
                sheetName: "Users Details",
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
    if ($("#userListTable").length) {
        // Init Table
        userListTable.init();

        // Filter function
        function filterHTMLForm() {
            userListTable.init();
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
            $("#status").val("1");
            filterHTMLForm();
        });

        let tomOptions = {
            plugins: {
                dropdown_input: {},
                remove_button: {
                    title: "Remove this item",
                },
            },
            placeholder: 'Search Here...',
            persist: false,
            create: true,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };

        var role_id = new TomSelect('#role_id', tomOptions);
        var edit_role_id = new TomSelect('#edit_role_id', tomOptions);

        const addUserModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addUserModal"));
        const editUsersModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editUsersModal"));
        const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addUserModalEl = document.getElementById('addUserModal')
        addUserModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addUserModal .acc__input-error').html('');
            $('#addUserModal .modal-body input:not([type="checkbox"])').val('');
            $('#addUserModal .modal-body select').val('');
            var placeholder = $('#addUserModal .userImageAdd').attr('data-placeholder');
            $('#addUserModal .userImageAdd').attr('src', placeholder);
            $('#addUserModal input[type="checkbox"]').prop('checked', false);
            role_id.clear(true);
        });
        
        const editUsersModalEl = document.getElementById('editUsersModal')
        editUsersModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editUsersModal .acc__input-error').html('');
            $('#editUsersModal .modal-body input:not([type="checkbox"])').val('');
            $('#editUsersModal .modal-body select').val('');
            var placeholder = $('#addUserModal .userImageEdit').attr('data-placeholder');
            $('#editUsersModal .userImageEdit').attr('src', placeholder);
            $('#editUsersModal input[type="checkbox"]').prop('checked', false);
            $('#editUsersModal input[name="id"]').val('0');
            edit_role_id.clear(true);
        });

        $('#addUserForm').on('change', '#userPhotoAdd', function(){
            showPreview('userPhotoAdd', 'userImageAdd')
        })
        $('#editUsersForm').on('change', '#userPhotoEdit', function(){
            showPreview('userPhotoEdit', 'userImageEdit')
        })

        $('#addUserForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addUserForm');
        
            document.querySelector('#saveUser').setAttribute('disabled', 'disabled');
            document.querySelector("#saveUser svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            form_data.append('file', $('#addUserForm input[name="photo"]')[0].files[0]); 
            axios({
                method: "post",
                url: route('users.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveUser').removeAttribute('disabled');
                document.querySelector("#saveUser svg").style.cssText = "display: none;";
                //console.log(response.data.message);
                //return false;

                if (response.status == 200) {
                    addUserModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('New User Successfully created.');
                    });     
                }
                userListTable.init();
            }).catch(error => {
                document.querySelector('#saveUser').removeAttribute('disabled');
                document.querySelector("#saveUser svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addUserForm .${key}`).addClass('border-danger');
                            $(`#addUserForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#userListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("users.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    let roles = dataset.roleIds;
                    console.log(dataset.photo_url);
                    var placeholders = $('#editUsersModal .userImageEdit').attr('data-placeholder')
                    $('#editUsersModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editUsersModal input[name="email"]').val(dataset.email ? dataset.email : '');
                    $('#editUsersModal select[name="gender"]').val(dataset.gender ? dataset.gender : '');
                    $('#editUsersModal .userImageEdit').attr('src', dataset.photo_url ? dataset.photo_url : placeholders);
                    if(dataset.active == 1){
                        $('#editUsersModal input[name="active"]').prop('checked', true);
                    }else{
                        $('#editUsersModal input[name="active"]').prop('checked', false);
                    }
                    $('#editUsersModal input[name="id"]').val(editId);

                    if(roles.length > 0){
                        for (var roleID of roles) {
                            edit_role_id.addItem(roleID, true);
                        }
                    }else{
                        edit_role_id.clear(true); 
                    }
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        // Update Course Data
        $("#editUsersForm").on("submit", function (e) {
            e.preventDefault();
            let editId = $('#editUsersForm input[name="id"]').val();
            const form = document.getElementById("editUsersForm");

            document.querySelector('#updateUser').setAttribute('disabled', 'disabled');
            document.querySelector('#updateUser svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);
            form_data.append('file', $('#editUsersForm input[name="photo"]')[0].files[0]); 

            axios({
                method: "post",
                url: route("users.update", editId),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateUser").removeAttribute("disabled");
                    document.querySelector("#updateUser svg").style.cssText = "display: none;";
                    editUsersModal.hide();

                    successModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('User data successfully updated.');
                    });
                }
                userListTable.init();
            }).catch((error) => {
                document.querySelector("#updateUser").removeAttribute("disabled");
                document.querySelector("#updateUser svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editUsersForm .${key}`).addClass('border-danger')
                            $(`#editUsersForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editUsersModal.hide();

                        successModal.show();
                        document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                            $("#successModal .successModal").html("Oops!");
                            $("#successModal .successModal").html('No data change found!');
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
                    url: route('users.destory', recordID),
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
                    userListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('users.restore', recordID),
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
                    userListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#userListTable').on('click', '.delete_btn', function(){
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
        $('#userListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORE');
            });
        });
    }

    function showPreview(inputId, targetImageId) {
        var src = document.getElementById(inputId);
        var target = document.getElementById(targetImageId);
        var title = document.getElementById('selected_image_title');
        var fr = new FileReader();
        fr.onload = function () {
            target.src = fr.result;
        }
        fr.readAsDataURL(src.files[0]);
    };

})();
