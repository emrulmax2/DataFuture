import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");
var roomListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";
        let venue = $("#roomTableId").attr('data-venueid') != "" ? $("#roomTableId").attr('data-venueid') : "0";

        let tableContent = new Tabulator("#roomTableId", {
            ajaxURL: route("room.list"),
            ajaxParams: { querystr: querystr, status: status, venue: venue},
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
                    title: "Room Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Room Capacity",
                    field: "room_capacity",
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
                            btns +='<a href="'+route('room.show', cell.getData().id)+'" class="btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="eye-off" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#roomEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
                sheetName: "Rooms Details",
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
    if ($("#roomTableId").length) {
        // Init Table
        roomListTable.init();

        // Filter function
        function filterHTMLForm() {
            roomListTable.init();
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

        const roomAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#roomAddModal"));
        const roomEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#roomEditModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#roomConfirmModal"));

        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const roomAddModalEl = document.getElementById('roomAddModal')
        roomAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#roomAddModal .acc__input-error').html('');
            $('#roomAddModal input[type="text"]').val('');
            $('#roomAddModal select').val('');
        });
        
        const roomEditModalEl = document.getElementById('roomEditModal')
        roomEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#roomEditModal .acc__input-error').html('');
            $('#roomEditModal input[type="text"]').val('');
            $('#roomEditModal select').val('');
            $('#roomEditModal input[name="id"]').val('0');
        });

        const roomConfirmModal = document.getElementById('roomConfirmModal');
        roomConfirmModal.addEventListener('hidden.tw.modal', function(event){
            $('#roomConfirmModal .roomAgreeWith').attr('data-id', '0');
            $('#roomConfirmModal .roomAgreeWith').attr('data-action', 'none');
        });


        // Delete Room
        $('#roomTableId').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('roomConfirmModal').addEventListener('shown.tw.modal', function(event){
                $('#roomConfirmModal .roomConfModTitle').html(confModalDelTitle);
                $('#roomConfirmModal .roomConfModDesc').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#roomConfirmModal .roomAgreeWith').attr('data-id', rowID);
                $('#roomConfirmModal .roomAgreeWith').attr('data-action', 'DELETE');
            });
        });

        $('#roomTableId').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let venueID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('roomConfirmModal').addEventListener('shown.tw.modal', function(event){
                $('#roomConfirmModal .roomConfModTitle').html(confModalDelTitle);
                $('#roomConfirmModal .roomConfModDesc').html('Do you really want to restore these record?');
                $('#roomConfirmModal .roomAgreeWith').attr('data-id', venueID);
                $('#roomConfirmModal .roomAgreeWith').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#roomConfirmModal .roomAgreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#roomConfirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('room.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#roomConfirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Data Deleted!');
                        });
                    }
                    roomListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('room.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#roomConfirmModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Room Data Successfully Restored!');
                        });
                    }
                    roomListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $("#roomTableId").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("room.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#roomEditModal input[name="name"]').val(dataset.name ? dataset.name : '');
                    $('#roomEditModal input[name="room_capacity"]').val(dataset.room_capacity ? dataset.room_capacity : '');

                    $('#roomEditModal input[name="id"]').val(editId);
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        $('#roomEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('roomEditForm');

            $('#roomEditForm').find('input').removeClass('border-danger')
            $('#roomEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateRoom').setAttribute('disabled', 'disabled');
            document.querySelector('#updateRoom svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('room.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateRoom').removeAttribute('disabled');
                document.querySelector('#updateRoom svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    roomEditModal.hide();
                    roomListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Room data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateRoom').removeAttribute('disabled');
                document.querySelector('#updateRoom svg').style.cssText = 'display: none;';
                if(error.response){
                    if(error.response.status == 422){
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#roomEditForm .${key}`).addClass('border-danger')
                            $(`#roomEditForm  .error-${key}`).html(val)
                        }
                    }else{
                        console.log('error');
                    }
                }
            });
        });

        $('#roomAddForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('roomAddForm');

            $('#roomAddForm').find('input').removeClass('border-danger')
            $('#roomAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveRoom').setAttribute('disabled', 'disabled');
            document.querySelector('#saveRoom svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('room.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveRoom').removeAttribute('disabled');
                document.querySelector('#saveRoom svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    roomAddModal.hide();
                    roomListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Room data successfully inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveRoom').removeAttribute('disabled');
                document.querySelector('#saveRoom svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#roomAddForm .${key}`).addClass('border-danger')
                            $(`#roomAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });
    }
})()