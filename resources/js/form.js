import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
//import IMask from 'imask';
 
("use strict");
var formDataListDatatable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query").val() != "" ? $("#query").val() : "";
        let listTable = new Tabulator("#formDataListTable", {
            ajaxURL: route("formdatatypes.list"),
            ajaxParams: { querystr: querystr },
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
                    title: "Text",
                    field: "text",
                    headerHozAlign: "left",
                },
                {
                    title: "Number",
                    field: "number",
                    headerHozAlign: "left",
                },
                {
                    title: "Select",
                    field: "select",
                    headerHozAlign: "left",
                },
                {
                    title: "Checkbox",
                    field: "checkbox",
                    headerHozAlign: "left",
                },
                {
                    title: "Switch",
                    field: "switch",
                    headerHozAlign: "left",
                },
                {
                    title: "Radio Button",
                    field: "radio_button",
                    headerHozAlign: "left",
                },
                {
                    title: "Contact No",
                    field: "phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Email",
                    field: "email",
                    headerHozAlign: "left",
                },
                {
                    title: "Date",
                    field: "date",
                    headerHozAlign: "left",
                },
                {
                    title: "Date Range",
                    field: "date_range",
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
                            //btns +=
                                //'<a href="' +
                                //route("formdatatypes.show", cell.getData().id) +
                                //cell.getData().id +
                                //'"  class="btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="EyeOff" class="w-4 h-4"></i></a>';
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '" data-tw-toggle="modal" data-tw-target="#editFormDataModal"  type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns +=
                                '<button data-id="' +
                                cell.getData().id +
                                '"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
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
            listTable.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        });

        // Export
        $("#tabulator-export-csv").on("click", function (event) {
            listTable.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            listTable.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            listTable.download("xlsx", "data.xlsx", {
                sheetName: "Form List Details",
            });
        });

        $("#tabulator-export-html").on("click", function (event) {
            listTable.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print").on("click", function (event) {
            listTable.print();
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
    if ($("#formDataListTable").length) {
        // Init Table 
        formDataListDatatable.init();

        // Filter function
        function filterHTMLForm() {
            formDataListDatatable.init();
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
    

        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        
        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';

        $('#datatypeForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('datatypeForm');
        
            document.querySelector('#saveDataTypes').setAttribute('disabled', 'disabled');
        
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('formdatatypes.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveDataTypes').removeAttribute('disabled');
                
                if (response.status == 200) {
                    document.querySelector('#datatypeForm input[name="textInput"]').value = ''; 
                    document.querySelector('#datatypeForm input[name="numberInput"]').value = '';
                    document.querySelector('#datatypeForm select[name="selectOption"]').value = '';
                    document.querySelector('#datatypeForm input[name="checkboxInput"]').value= '';
                    document.querySelector('#datatypeForm input[name="switchInput"]').value= '';
                    document.querySelector('#datatypeForm input[name="horizontal_radio_button"]').value = ''; 
                    document.querySelector('#datatypeForm input[name="phone"]').value = '';
                    document.querySelector('#datatypeForm input[name="email"]').value= '';
                    document.querySelector('#datatypeForm input[name="dateformat"]').value= '';
                    document.querySelector('#datatypeForm input[name="daterange"]').value= '';
        
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Form Data Types Successfully Inserted!');
                    });
                }
            }).catch(error => {
                document.querySelector('#saveDataTypes').removeAttribute('disabled');
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#datatypeForm .${key}`).addClass('border-danger')
                            $(`#datatypeForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#formDataListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("formdatatypes.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        let dataset = response.data;
                        $('#editFormDataModal input[name="textInput"]').val(dataset.text_input ? dataset.text_input : '');
                        $('#editFormDataModal input[name="numberInput"]').val(dataset.number_input ? dataset.number_input : '');
                        $('#editFormDataModal select[name="selectOption"]').val(dataset.select_option ? dataset.select_option : '');

                        if(dataset.checkbox == "on"){
                            document.querySelector('#editFormDataModal #editFormdataTypeCheckbox').checked = true;
                        }else{
                            document.querySelector('#editFormDataModal #editFormdataTypeCheckbox').checked = false;
                        }

                        if(dataset.switch == "on"){
                            document.querySelector('#editFormDataModal #editFormdataTypeSwitch').checked = true;
                        }else{
                            document.querySelector('#editFormDataModal #editFormdataTypeSwitch').checked = false;
                        }

                        if(dataset.radio_button == "first"){
                            document.querySelector('#editFormDataModal #condition-new').checked = true;
                        }else if(dataset.radio_button == "second"){
                            document.querySelector('#editFormDataModal #condition-second').checked = true;
                        }

                        $('#editFormDataModal input[name="phone"]').val(dataset.phone ? dataset.phone : '');
                        $('#editFormDataModal input[name="email"]').val(dataset.email ? dataset.email : '');
                        
                        $('#editFormDataModal input[name="dateformat"]').val(dataset.date_format ? dataset.date_format : '');
                        //var dateStr = dataset.date_range;
                        //var dateRangeArr = dateStr.split(" - ");
                        //var startDate = dateRangeArr[0]; 
                        //var endDate = dateRangeArr[1]; 
                                        
                        $('#editFormDataModal input[name="daterange"]').val(dataset.date_range ? dataset.date_range : '');

                        $('#editFormDataModal input[name="id"]').val(editId);
                    }
                })
                .catch((error) => {
                    console.log(error);
                });
        });

        // Update Form Data Types
        $("#editFormDataType").on("submit", function (e) {
            const editModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editFormDataModal"));
            const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));

            e.preventDefault();
            const form = document.getElementById("editFormDataType");

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("formdatatypes.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            })
                .then((response) => {
                    if (response.status == 200) {
                        editModal.hide();

                        let message = response.data.message;
                        succModal.show();
                        document.getElementById("successModal")
                            .addEventListener("shown.tw.modal", function (event) {
                                $("#successModal .successModalTitle").html(
                                    "Congratulations!"
                                );
                                $("#successModal .successModalDesc").html(message);
                            });
                    }
                    formDataListDatatable.init();
                })
                .catch((error) => {
                    document
                        .querySelector("#updateFormData")
                        .removeAttribute("disabled");
                    document.querySelector("#updateFormData svg").style.cssText =
                        "display: none;";
                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(`#editFormDataType .${key}`).addClass(
                                    "border-danger"
                                );
                                $(`#editFormDataType  .error-${key}`).html(val);
                            }
                        }else if (error.response.status == 304) {
                            editModal.hide();

                            let message = error.response.statusText;
                            succModal.show();
                            document.getElementById("successModal")
                                .addEventListener("shown.tw.modal", function (event) {
                                    $("#successModal .successModalTitle").html(
                                        "No Data Change!"
                                    );
                                    $("#successModal .successModalDesc").html(message);
                                });
                        } else {
                            console.log("error");
                        }
                    }
                });
        });

        // Confirm Modal Action
        $('#confirmFormdataDelModal .agreeWith').on('click', function(){
            const confModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmFormdataDelModal"));
            document.getElementById('confirmFormdataDelModal').addEventListener('hidden.tw.modal', function(event){
                $('#confirmFormdataDelModal .agreeWith').attr('data-id', '0');
                $('#confirmFormdataDelModal .agreeWith').attr('data-action', 'none');
            });
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmFormdataDelModal button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('formdatatypes.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmFormdataDelModal button').removeAttr('disabled');
                        confModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Congratulations!');
                            $('#successModal .successModalDesc').html('Company Item Success Fully Deleted!');
                        });
                    }
                    formDataListDatatable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Company
        $('#formDataListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confModal.show();
            document.getElementById('confirmFormdataDelModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmFormdataDelModal .confModTitle').html(confModalDelTitle);
                $('#confirmFormdataDelModal .confModDesc').html(confModalDelDescription);
                $('#confirmFormdataDelModal .agreeWith').attr('data-id', rowID);
                $('#confirmFormdataDelModal .agreeWith').attr('data-action', 'DELETE');
            });
        });
    }
})()