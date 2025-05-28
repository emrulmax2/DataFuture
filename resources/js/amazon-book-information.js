import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";

("use strict");

var amazonBookInfoListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#amazonBookInfoListTable", {
            ajaxURL: route("library.books.list"),
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
                    width: "80",
                },
                {
                    title: "Book",
                    field: "title",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="flex justify-start items-center">';
                                html += '<div class="intro-x mr-3" style="flex: 0 0 48px;">';
                                    html += '<img alt="'+cell.getData().title+'" class="w-auto h-12 shadow intro-x mr-5" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div>';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().title+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().author != '' ? cell.getData().author : '')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Publisher",
                    field: "publisher",
                    headerHozAlign: "left",
                },
                {
                    title: "ISBN13",
                    field: "isbn13",
                    headerHozAlign: "left",
                },
                {
                    title: "ISBN10",
                    field: "isbn10",
                    headerHozAlign: "left",
                },
                {
                    title: "Edition",
                    field: "edition",
                    headerHozAlign: "left",
                },
                {
                    title: "Published",
                    field: "publication_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Price",
                    field: "price",
                    headerHozAlign: "left",
                },
                {
                    title: "Qty",
                    field: "quantity",
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "70",
                    formatter(cell, formatterParams) { 
                        return '<a data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#viewQtyModal" href="javascript:void(0);" class="viewQtyBtn text-primary font-medium underline">'+cell.getData().quantity+'</a>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editABIModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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

        

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Roles Details",
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

var libraryBookLocationList = (function () {
    var _tableGen = function (amazonBookId) {
        // Setup Tabulator

        let tableContent = new Tabulator("#libraryBookLocationList", {
            ajaxURL: route("library.books.location.list"),
            ajaxParams: { amazonBookId: amazonBookId },
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
                    width: "80",
                },
                {
                    title: "Book",
                    field: "title",
                    headerHozAlign: "left",
                    headerSort: false,
                    formatter(cell, formatterParams) { 
                        var html = '<div class="flex justify-start items-center">';
                                html += '<div class="intro-x mr-3" style="flex: 0 0 48px;">';
                                    html += '<img alt="'+cell.getData().title+'" class="w-auto h-12 shadow intro-x mr-5" src="'+cell.getData().photo_url+'">';
                                html += '</div>';
                                html += '<div>';
                                    html += '<div class="font-medium whitespace-nowrap">'+cell.getData().title+'</div>';
                                    html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+(cell.getData().author != '' ? cell.getData().author : '')+'</div>';
                                html += '</div>';
                            html += '</div>';
                        return html;
                    }
                },
                {
                    title: "ISBN13",
                    field: "isbn13",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "ISBN10",
                    field: "isbn10",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Venue",
                    field: "venue",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Location",
                    field: "location",
                    headerSort: false,
                    headerHozAlign: "left",
                },
                {
                    title: "Barcode",
                    field: "book_barcode",
                    headerHozAlign: "left",
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "120",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-abiid="'+cell.getData().abi_id+'" data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-abiid="'+cell.getData().abi_id+'" data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
    };
    return {
        init: function (amazonBookId) {
            _tableGen(amazonBookId);
        },
    };
})();


(function () {
    if ($("#amazonBookInfoListTable").length) {
        amazonBookInfoListTable.init();

        function filterHTMLForm() {
            amazonBookInfoListTable.init();
        }

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

        $("#tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        $("#tabulator-html-filter-reset").on("click", function (event) {
            $("#query").val("");
            $("#status").val("1");
            filterHTMLForm();
        });
    }

    
    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const editABIModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editABIModal"));
    const viewQtyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#viewQtyModal"));

    const editABIModalEl = document.getElementById('editABIModal')
    editABIModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editABIModal .acc__input-error').html('');
        $('#editABIModal .modal-body input').val('');
        $('#editABIModal input[name="id"]').val('0');
    });

    const viewQtyModalEl = document.getElementById('viewQtyModal')
    viewQtyModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#libraryBookLocationList').html('').removeClass('tabulator').removeAttr('tabulator-layout').removeAttr('role');
    });

    $("#amazonBookInfoListTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let row_id = $editBtn.attr("data-id");

        axios({
            method: "POST",
            url: route("library.books.edit"),
            data: {row_id : row_id},
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.row;
                $('#editABIModal [name="author"]').val(dataset.author ? dataset.author : '');
                $('#editABIModal [name="title"]').val(dataset.title ? dataset.title : '');
                $('#editABIModal [name="publisher"]').val(dataset.publisher ? dataset.publisher : '');
                $('#editABIModal [name="isbn13"]').val(dataset.isbn13 ? dataset.isbn13 : '');
                $('#editABIModal [name="isbn10"]').val(dataset.isbn10 ? dataset.isbn10 : '');
                $('#editABIModal [name="edition"]').val(dataset.edition ? dataset.edition : '');
                $('#editABIModal [name="publication_date"]').val(dataset.publication_date ? dataset.publication_date : '');
                $('#editABIModal [name="number_of_pages"]').val(dataset.number_of_pages ? dataset.number_of_pages : '');
                $('#editABIModal [name="price"]').val(dataset.price ? dataset.price : '');

                $('#editABIModal [name="id"]').val(row_id);
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $("#editABIForm").on("submit", function (e) {
        e.preventDefault();
        const form = document.getElementById("editABIForm");

        document.querySelector('#updateABIBtn').setAttribute('disabled', 'disabled');
        document.querySelector('#updateABIBtn svg').style.cssText = 'display: inline-block;';

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route("library.books.update"),
            data: form_data,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                document.querySelector("#updateABIBtn").removeAttribute("disabled");
                document.querySelector("#updateABIBtn svg").style.cssText = "display: none;";
                editABIModal.hide();

                successModal.show();
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulations!");
                    $("#successModal .successModalDesc").html('Book information successfully updated.');
                });

                setTimeout(() => {
                    successModal.hide();
                }, 2000);
            }
            amazonBookInfoListTable.init();
        }).catch((error) => {
            document.querySelector("#updateABIBtn").removeAttribute("disabled");
            document.querySelector("#updateABIBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editABIForm .${key}`).addClass('border-danger')
                        $(`#editABIForm  .error-${key}`).html(val)
                    }
                } else {
                    console.log("error");
                }
            }
        });
    });

    $('#amazonBookInfoListTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETE');
        });
    });

    $('#amazonBookInfoListTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
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
                url: route('library.books.destory', recordID),
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

                    setTimeout(() => {
                        successModal.hide();
                    }, 2000);
                }
                amazonBookInfoListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('library.books.restore', recordID),
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

                    setTimeout(() => {
                        successModal.hide();
                    }, 2000);
                }
                amazonBookInfoListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }else if(action == 'DELETELB'){
            axios({
                method: 'delete',
                url: route('library.books.location.destory', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    var abi_id = response.data.abi;
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                    });
                    libraryBookLocationList.init(abi_id);

                    setTimeout(() => {
                        successModal.hide();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }
    });

    $('#libraryBookLocationList').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETELB');
        });
    });

    $('#amazonBookInfoListTable').on('click', '.viewQtyBtn', function(e){
        e.preventDefault();
        var $theBtn = $(this);
        var abi_id = $theBtn.attr('data-id');

        libraryBookLocationList.init(abi_id);
    })
})();