import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var companySupervisorTable = (function () {
    var _tableGen = function (company_id = 0) {
        // Setup Tabulator
        let tableID = 'companySupervisorTable_'+company_id;

        let tableContent = new Tabulator("#"+tableID, {
            ajaxURL: route("companies.supervisor.list"),
            ajaxParams: { company_id: company_id },
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
                },
                {
                    title: "Email",
                    field: "email",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Other",
                    field: "other_info",
                    headerHozAlign: "left",
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCompanySupervisorModal" type="button" class="editSupervisor btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="deleteSupervisor btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restoreSupervisor btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
    };
    return {
        init: function (company_id) {
            _tableGen(company_id);
        },
    };
})();

var hideCollapsibleIcon = function(cell, formatterParams, onRendered){ 
    return '<span class="chellIconWrapper inline-flex">+</span>';
};

var wpCompanyListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-WPCOM").val() != "" ? $("#query-WPCOM").val() : "";
        let status = $("#status-WPCOM").val() != "" ? $("#status-WPCOM").val() : "";
        let tableContent = new Tabulator("#wpCompanyListTable", {
            ajaxURL: route("companies.list"),
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
            height:"auto",
            columns: [
                {
                    formatter: hideCollapsibleIcon, 
                    align: "left", 
                    title: "&nbsp;", 
                    width: "70",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, row, formatterParams){
                        const company_id = row.getData().id;
                        let holderWrapEl = document.getElementById('subTableWrap_'+company_id);
                        let supervisorTableId = 'companySupervisorTable_'+company_id;

                        if(row.getElement().classList.contains('active')){
                            row.getElement().classList.remove('active');
                            row.getElement().querySelector('.chellIconWrapper').innerHTML = '+';
                            holderWrapEl.style.display = 'none';
                        }else{
                            row.getElement().classList.add('active');
                            row.getElement().querySelector('.chellIconWrapper').innerHTML = '-';
                            holderWrapEl.style.display = 'block';
                            holderWrapEl.style.width = '100%';

                            if($('#'+supervisorTableId).length > 0){
                                companySupervisorTable.init(company_id)
                            }
                        }     
                    }
                },
                {
                    title: "Name",
                    field: "name",
                    headerHozAlign: "left",
                },
                {
                    title: "Email",
                    field: "email",
                    headerHozAlign: "left",
                },
                {
                    title: "Phone",
                    field: "phone",
                    headerHozAlign: "left",
                },
                {
                    title: "Address",
                    field: "address",
                    headerHozAlign: "left",
                },
                {
                    title: "Fax",
                    field: "fax",
                    headerHozAlign: "left",
                },
                {
                    title: "Status",
                    field: "active",
                    headerHozAlign: "left",
                    width: "100",
                    formatter(cell, formatterParams){
                        return '<div class="form-check form-switch"><input data-id="'+cell.getData().id+'" '+(cell.getData().active == 1 ? 'Checked' : '')+' value="'+cell.getData().active+'" type="checkbox" class="status_updater form-check-input"> </div>';
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "190",
                    download:false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#addCompanySupervisorModal" type="button" class="add_sup_btn btn-rounded btn btn-linkedin text-white p-0 w-9 h-9 ml-1"><i data-lucide="user-plus" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editWPCompanyModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
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
            rowFormatter: function(row, e) {
                const company_id = row.getData().id;
                const has_supervisor = row.getData().has_supervisor;

                var holderEl = document.createElement("div");
                holderEl.setAttribute('class', "pt-3 px-5 pb-5 overflow-x-auto scrollbar-hidden subTableWrap_"+company_id);
                holderEl.setAttribute('id', "subTableWrap_"+company_id);
                holderEl.style.display = "none";
                holderEl.style.boxSizing = "border-box";
                //holderEl.style.borderTop = "1px solid #e5e7eb";


                if(has_supervisor > 0){
                    var tableEl = document.createElement("div");
                    tableEl.setAttribute('class', "table-report table-report--tabulator subTable"+company_id);
                    tableEl.setAttribute('id', "companySupervisorTable_"+company_id);
                    tableEl.setAttribute('data-companyid', company_id);

                    holderEl.appendChild(tableEl);
                }else{
                    holderEl.innerHTML = '<div class="alert alert-pending-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-triangle" class="w-6 h-6 mr-2"></i> <strong>Oops!</strong> &nbsp;No supervisor found under this company.</div>';
                }

                row.getElement().appendChild(holderEl);
            }
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
        $("#tabulator-export-csv-WPCOM").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-WPCOM").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-WPCOM").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Title Details",
            });
        });

        $("#tabulator-export-html-WPCOM").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-WPCOM").on("click", function (event) {
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
    if ($("#wpCompanyListTable").length) {
        $('.optionBoxTitle').on('click', function(e){
            e.preventDefault();
            var $title = $(this);
            var $box = $title.parents('.optionBox');
            var $boxBody = $title.parent('.optionBoxHeader').siblings('.optionBoxBody');
            var table = $boxBody.attr('data-tableid');
    
            if($box.hasClass('active') && table == 'wpCompanyListTable'){
                wpCompanyListTable.init();
            }
        });
        

        // Filter function
        function filterTitleHTMLForm() {
            wpCompanyListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-WPCOM")[0].addEventListener(
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
        $("#tabulator-html-filter-go-WPCOM").on("click", function (event) {
            filterTitleHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-WPCOM").on("click", function (event) {
            $("#query-WPCOM").val("");
            $("#status-WPCOM").val("1");
            filterTitleHTMLForm();
        });

        const addWPCompanyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addWPCompanyModal"));
        const editWPCompanyModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editWPCompanyModal"));
        const addCompanySupervisorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addCompanySupervisorModal"));
        const editCompanySupervisorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editCompanySupervisorModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
        let confModalDelTitle = 'Are you sure?';

        const addWPCompanyModalEl = document.getElementById('addWPCompanyModal')
        addWPCompanyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addWPCompanyModal .acc__input-error').html('');
            $('#addWPCompanyModal .modal-body input:not([type="checkbox"])').val('');
            $('#addWPCompanyModal .modal-body textarea').val('');

            $('#addWPCompanyModal input[name="active"]').prop('checked', true);
        });
        
        const editWPCompanyModalEl = document.getElementById('editWPCompanyModal')
        editWPCompanyModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editWPCompanyModal .acc__input-error').html('');
            $('#editWPCompanyModal .modal-body input:not([type="checkbox"])').val('');
            $('#editWPCompanyModal input[name="id"]').val('0');
            $('#editWPCompanyModal .modal-body textarea').val('');
            
            $('#editWPCompanyModal input[name="active"]').prop('checked', false);
        });
        
        const addCompanySupervisorModalEl = document.getElementById('addCompanySupervisorModal')
        addCompanySupervisorModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#addCompanySupervisorModal .acc__input-error').html('');
            $('#addCompanySupervisorModal .modal-body input:not([type="checkbox"])').val('');
            $('#addCompanySupervisorModal .modal-body textarea').val('');
        });
        
        const editCompanySupervisorModalEl = document.getElementById('editCompanySupervisorModal')
        editCompanySupervisorModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editCompanySupervisorModal .acc__input-error').html('');
            $('#editCompanySupervisorModal .modal-body input:not([type="checkbox"])').val('');
            $('#editCompanySupervisorModal .modal-body textarea').val('');
            $('#editCompanySupervisorModal .modal-footer [name="id"]').val('0');
        });

        $('#addWPCompanyForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addWPCompanyForm');
        
            document.querySelector('#saveCompany').setAttribute('disabled', 'disabled');
            document.querySelector("#saveCompany svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('companies.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveCompany').removeAttribute('disabled');
                document.querySelector("#saveCompany svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addWPCompanyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Company details Successfully inserted.');
                    });     
                }
                wpCompanyListTable.init();
            }).catch(error => {
                document.querySelector('#saveCompany').removeAttribute('disabled');
                document.querySelector("#saveCompany svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addWPCompanyForm .${key}`).addClass('border-danger');
                            $(`#addWPCompanyForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $("#wpCompanyListTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("companies.edit", editId),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editWPCompanyModal [name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editWPCompanyModal [name="email"]').val(dataset.email ? dataset.email : '');
                    $('#editWPCompanyModal [name="phone"]').val(dataset.phone ? dataset.phone : '');
                    $('#editWPCompanyModal [name="fax"]').val(dataset.fax ? dataset.fax : '');
                    $('#editWPCompanyModal [name="website"]').val(dataset.website ? dataset.website : '');
                    $('#editWPCompanyModal [name="address"]').val(dataset.address ? dataset.address : '');
                    $('#editWPCompanyModal [name="other_info"]').val(dataset.other_info ? dataset.other_info : '');
                    
                    $('#editWPCompanyModal input[name="id"]').val(editId);

                    if(dataset.active == 1){
                        $('#editWPCompanyModal input[name="active"]').prop('checked', true);
                    }else{
                        $('#editWPCompanyModal input[name="active"]').prop('checked', false);
                    }
                }
            }).catch((error) => {
                console.log(error);
            });
        });

        // Update Course Data
        $("#editWPCompanyForm").on("submit", function (e) {
            e.preventDefault();
            const form = document.getElementById("editWPCompanyForm");

            document.querySelector('#updateCompany').setAttribute('disabled', 'disabled');
            document.querySelector('#updateCompany svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route("companies.update"),
                data: form_data,
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    document.querySelector("#updateCompany").removeAttribute("disabled");
                    document.querySelector("#updateCompany svg").style.cssText = "display: none;";
                    editWPCompanyModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html("Congratulations!");
                        $("#successModal .successModalDesc").html('Company details successfully updated.');
                    });
                }
                wpCompanyListTable.init();
            }).catch((error) => {
                document.querySelector("#updateCompany").removeAttribute("disabled");
                document.querySelector("#updateCompany svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editWPCompanyForm .${key}`).addClass('border-danger')
                            $(`#editWPCompanyForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editWPCompanyModal.hide();

                        let message = error.response.statusText;
                        succModal.show();
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

        /* Supervisor CRUD Start */
        $('#wpCompanyListTable').on('click', '.add_sup_btn', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var company_id = $theBtn.attr('data-id');

            $('#addCompanySupervisorModal [name="company_id"]').val(company_id);
        });

        $('#addCompanySupervisorForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('addCompanySupervisorForm');
        
            document.querySelector('#addSupervisor').setAttribute('disabled', 'disabled');
            document.querySelector("#addSupervisor svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('companies.supervisor.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#addSupervisor').removeAttribute('disabled');
                document.querySelector("#addSupervisor svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    addCompanySupervisorModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Company Supervisor details Successfully inserted.');
                    });     
                }
                wpCompanyListTable.init();
            }).catch(error => {
                document.querySelector('#addSupervisor').removeAttribute('disabled');
                document.querySelector("#addSupervisor svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#addCompanySupervisorForm .${key}`).addClass('border-danger');
                            $(`#addCompanySupervisorForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $(document).on('click', '.editSupervisor', function(e){
            e.preventDefault();
            let $editBtn = $(this);
            let row_id = $editBtn.attr("data-id");

            axios({
                method: "post",
                url: route("companies.supervisor.edit"),
                data: {row_id : row_id},
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    $('#editCompanySupervisorModal [name="name"]').val(dataset.name ? dataset.name : '');
                    $('#editCompanySupervisorModal [name="email"]').val(dataset.email ? dataset.email : '');
                    $('#editCompanySupervisorModal [name="phone"]').val(dataset.phone ? dataset.phone : '');
                    $('#editCompanySupervisorModal [name="other_info"]').val(dataset.other_info ? dataset.other_info : '');
                    
                    $('#editCompanySupervisorModal input[name="id"]').val(row_id);
                }
            }).catch((error) => {
                console.log(error);
            });
        })

        $('#editCompanySupervisorForm').on('submit', function(e){
            e.preventDefault();
            let $form = $(this);
            const form = document.getElementById('editCompanySupervisorForm');
        
            document.querySelector('#editSupervisor').setAttribute('disabled', 'disabled');
            document.querySelector("#editSupervisor svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            let company_id = $('[name="id"]', $form).val();
            axios({
                method: "post",
                url: route('companies.supervisor.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#editSupervisor').removeAttribute('disabled');
                document.querySelector("#editSupervisor svg").style.cssText = "display: none;";
                
                if (response.status == 200) {
                    editCompanySupervisorModal.hide();

                    succModal.show();
                    document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                        $("#successModal .successModalTitle").html( "Congratulations!" );
                        $("#successModal .successModalDesc").html('Company Supervisor details Successfully updated.');
                    });     
                }
                companySupervisorTable.init(company_id)
            }).catch(error => {
                document.querySelector('#editSupervisor').removeAttribute('disabled');
                document.querySelector("#editSupervisor svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editCompanySupervisorForm .${key}`).addClass('border-danger')
                            $(`#editCompanySupervisorForm  .error-${key}`).html(val)
                        }
                    }else if (error.response.status == 304) {
                        editCompanySupervisorModal.hide();

                        succModal.show();
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

        $(document).on('click', '.deleteSupervisor', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETESUP');
            });
        });
        /* Supervisor CRUD END */

        // Confirm Modal Action
        $('#confirmModal .agreeWith').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModal button').attr('disabled', 'disabled');
            if(action == 'DELETECOM'){
                axios({
                    method: 'delete',
                    url: route('companies.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        });
                    }
                    wpCompanyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORECOM'){
                axios({
                    method: 'post',
                    url: route('companies.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        });
                    }
                    wpCompanyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'CHANGESTATCOM'){
                axios({
                    method: 'post',
                    url: route('companies.update.status', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record status successfully updated!');
                        });
                    }
                    wpCompanyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'DELETESUP'){
                axios({
                    method: 'delete',
                    url: route('companies.supervisor.destroy', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModal button').removeAttr('disabled');
                        confirmModal.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('WOW!');
                            $('#successModal .successModalDesc').html('Record status successfully deleted!');
                        });
                    }
                    wpCompanyListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        // Delete Course
        $('#wpCompanyListTable').on('click', '.status_updater', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to change status of this record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'CHANGESTATCOM');
            });
        });

        // Delete Course
        $('#wpCompanyListTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
                $('#confirmModal .agreeWith').attr('data-id', rowID);
                $('#confirmModal .agreeWith').attr('data-action', 'DELETECOM');
            });
        });

        // Restore Course
        $('#wpCompanyListTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModal.show();
            document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
                $('#confirmModal .confModTitle').html(confModalDelTitle);
                $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModal .agreeWith').attr('data-id', courseID);
                $('#confirmModal .agreeWith').attr('data-action', 'RESTORECOM');
            });
        });



    }
})();