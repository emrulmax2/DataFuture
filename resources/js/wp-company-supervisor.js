import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
 
("use strict");
var wpSupervisorListTable = (function () {
    var _tableGen = function (company_id) {
        let tableID = 'wpSupervisorListTable_'+company_id;
        

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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editCompanySupervisorModal" type="button" class="editSupervisor btn-rounded btn btn-success text-white p-0 w-[30px] h-[30px] ml-1"><i data-lucide="Pencil" class="w-3 h-3"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="deleteSupervisor btn btn-danger text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="Trash2" class="w-3 h-3"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restoreSupervisor btn btn-linkedin text-white btn-rounded ml-1 p-0 w-[30px] h-[30px]"><i data-lucide="rotate-cw" class="w-3 h-3"></i></button>';
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
                const actionColumn = this.getColumn("id");
                if (actionColumn) {
                    const currentWidth = actionColumn.getWidth();
                    actionColumn.setWidth(currentWidth - 1);
                }
            },
        });

        window.addEventListener("resize", () => {
            tableContent.redraw();
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
             const actionColumn = this.getColumn("id");
            if (actionColumn) {
                const currentWidth = actionColumn.getWidth();
                actionColumn.setWidth(currentWidth - 1);
            }
        });
    };
    
    return {
        init: function (company_id) {
            _tableGen(company_id);
        },

        reset: function() {
            $('[id^="wpSupervisorListTable_"]').removeData('table-initialized');
        }
    };
})();

(function () {

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


        $(document).on('click', '.add_sup_btn', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            var company_id = $theBtn.attr('data-id');
            console.log(company_id)

            $('#addCompanySupervisorForm [name="company_id"]').val(company_id);
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

                setTimeout(function () {
                    window.location.reload();
                }, 2000);

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

        $(document).on("click", ".editCompany_btn", function () {  
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


                    setTimeout(function () {
                        window.location.reload();
                    }, 2000);
                }

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

                setTimeout(function () {
                    window.location.reload();
                }, 2000);

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
                setTimeout(function () {
                    window.location.reload();
                }, 2000);
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
                     setTimeout(function () {
                    window.location.reload();
                }, 2000);
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
                     setTimeout(function () {
                    window.location.reload();
                }, 2000);
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
                     setTimeout(function () {
                    window.location.reload();
                }, 2000);
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
                     setTimeout(function () {
                    window.location.reload();
                }, 2000);
                }).catch(error =>{
                    console.log(error)
                });
            }
        })

        $(document).on('click', '.deleteCompanyBtn', function(){
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

 /**
  * Accordion and Table initialization
  */
     

    function initializeAccordionsAndTables() {
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = button.getAttribute('data-target');
                const collapse = document.querySelector(targetId);
                const companyId = targetId.split('-').pop();
                
                if(collapse && !collapse.classList.contains('collapse')) {
                    wpSupervisorListTable.init(companyId);
                }
            });
        });
        
        const firstAccordion = document.querySelector('.accordion-button:not(.collapsed)');
        if(firstAccordion) {
            const targetId = firstAccordion.getAttribute('data-target');
            const companyId = targetId.split('-').pop();
            wpSupervisorListTable.init(companyId);
        }
    }

    initializeAccordions();
    initializeAccordionsAndTables();

        // Search functionality
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('search');
        
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });
        }
    
        function performSearch() {
            const keyword = searchInput.value.trim();
            const url = searchForm.action;

            wpSupervisorListTable.reset();
            
            fetch(`${url}?search=${encodeURIComponent(keyword)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                document.querySelector('.companyListContainer').innerHTML = data.html;
                
                initializeAccordions();
                initializeAccordionsAndTables();
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    
        function initializeAccordions() {
            document.querySelectorAll('.accordion-collapse').forEach(collapse => {
                collapse.classList.add('collapse');
            });
    
            document.querySelectorAll('.accordion-button').forEach(button => {
                button.addEventListener('click', function() {
                    const targetId = button.getAttribute('data-target');
                    const targetContent = document.querySelector(targetId);
                    const plusIcon = button.querySelector('.accordion-icon-plus');
                    const minusIcon = button.querySelector('.accordion-icon-minus');
                    
                    const isExpanded = button.getAttribute('aria-expanded') === 'true';
    
                    if (isExpanded) {
                        plusIcon.classList.remove('hidden');
                        minusIcon.classList.add('hidden');
                    } else {
                        plusIcon.classList.add('hidden');
                        minusIcon.classList.remove('hidden');
                    }
                    
                    if (!isExpanded) {
                        targetContent.classList.remove('collapse');
                        targetContent.classList.add('show');
                        button.setAttribute('aria-expanded', 'true');
                    } else {
                        targetContent.classList.remove('show');
                        targetContent.classList.add('collapse');
                        button.setAttribute('aria-expanded', 'false');
                    }
                });
            });
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });
        }
})();