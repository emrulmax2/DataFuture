import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import studentWorkPlacementDocumentsTable from "./student-workplacement-documents";


("use strict");
var studentWorkPlacementTable = (function () {
    var _tableGen = function () {
        let student_id = $('#studentWorkPlacementTable').attr('data-student');
        let status = $("#status").val() != "" ? $("#status").val() : "";

        let tableContent = new Tabulator("#studentWorkPlacementTable", {
            ajaxURL: route("student.work.placement.hour.list"),
            ajaxParams: { status: status, student_id : student_id},
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
                    width: "70",
                    minWidth: 30,
                },
                {
                    title: "Company",
                    field: "company",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                },
                {
                    title: "Supervisor",
                    field: "supervisor",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                },
                {
                    title: "Start",
                    field: "start_date",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "End",
                    field: "end_date",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Hours",
                    field: "hours",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Contract Type",
                    field: "contract_type",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Created",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
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
                    minWidth: 180,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editHourModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Semester Details",
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

var studentWorkPlacementNwTable = (function () {
    var _tableGen = function () {
        let student_id = $('#studentWorkPlacementNwTable').attr('data-student');
        let status = $("#wp_status").val() != "" ? $("#wp_status").val() : "";
        let module_id = $("#wp_modules").val() != "" ? $("#wp_modules").val() : "";
        let level_hours_id = $("#src_level_hours_id").val() != "" ? $("#src_level_hours_id").val() : "";
        let learning_hours_id = $("#src_learning_hours_id").val() != "" ? $("#src_learning_hours_id").val() : "";

        let tableContent = new Tabulator("#studentWorkPlacementNwTable", {
            ajaxURL: route("student.workplacement.hour.list"),
            ajaxParams: { status: status, student_id : student_id, module_id : module_id, level_hours_id: level_hours_id, learning_hours_id : learning_hours_id},
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
                    width: "70",
                    minWidth: 30,
                },
                {
                    title: "Company",
                    field: "company",
                    headerSort: false,
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().company+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().supervisor+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Durations",
                    field: "start_date",
                    headerHozAlign: "left",
                    minWidth: 180,
                    headerSort: false,
                    formatter(cell, formatterParams){
                        let endDate = cell.getData().end_date != '' ? cell.getData().end_date : 'Present';
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().start_date+' - '+ endDate + '</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().hours+' Hours</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Learning Type",
                    field: "level_hours",
                    headerHozAlign: "left",
                    minWidth: 180,
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().level_hours+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().learning_hours+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Module",
                    field: "module_name",
                    headerHozAlign: "left",
                    minWidth: 180,
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        
                        if(cell.getData().module_name != ''){
                            html += '<div>';
                                html += '<div class="font-medium whitespace-normal">'+cell.getData().module_name+'</div>';
                                html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().hours+' Hours</div>';
                            html += '</div>';
                        }

                        return html;
                    }
                },
                {
                    title: "Settings",
                    field: "workplacement_setting",
                    headerHozAlign: "left",
                    minWidth: 180,
                    headerSort: false,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().workplacement_setting+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().workplacement_setting_type+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Contract Type",
                    field: "contract_type",
                    headerHozAlign: "left",
                    minWidth: 180,
                },
                {
                    title: "Created",
                    field: "created_by",
                    headerHozAlign: "left",
                    width: "180",
                    minWidth: 180,
                    formatter(cell, formatterParams){
                        var html = '';
                        html += '<div>';
                            html += '<div class="font-medium whitespace-nowrap">'+cell.getData().created_by+'</div>';
                            html += '<div class="text-slate-500 text-xs whitespace-nowrap">'+cell.getData().created_at+'</div>';
                        html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Status",
                    field: "status",
                    headerHozAlign: "left",
                    minWidth: 180,
                    formatter(cell, formatterParams) {
                        const status = cell.getData().status;
                        let colorClass = '';
                        switch(status.toLowerCase()) {
                            case 'pending':
                                colorClass = 'bg-yellow-100 text-yellow-800';
                                break;
                            case 'rejected':
                                colorClass = 'bg-red-100 text-red-800';
                                break;
                            case 'confirmed':
                                colorClass = 'bg-green-100 text-green-800';
                                break;
                            default:
                                colorClass = 'bg-gray-100 text-gray-800';
                        }
                        
                        return `<div class="font-medium whitespace-nowrap">
                            <span class="px-2 py-1 rounded-md text-xs font-semibold ${colorClass}">
                                ${status}
                            </span>
                        </div>`;
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "250",
                    download: false,
                    minWidth: 250,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#workplacementDocumentsModal" type="button" class="document_btn btn-rounded btn btn-linkedin text-white p-0 h-9 px-2 ml-1"><i data-lucide="file-text" class="w-4 h-4 mr-1"></i>Documents</a>';
                            if(cell.getData().can_edit == 1){
                                btns += '<button data-id="' +cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editWpHourModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            }
                            if(cell.getData().can_delete == 1){
                                btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                            }
                        }  else if (cell.getData().deleted_at != null) {
                            btns +='<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                },
            ],
            ajaxResponse: function (url, params, response) {

                $('.completedHours').html(response.completed_hours);
                $('.pendingHours').html(response.pending_hours);
                $('.rejectedHours').html(response.rejected_hours);

                return response;
            },
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
        $("#tabulator-export-csv").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Semester Details",
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
    let tomSelectOptions = {
        plugins: {
            dropdown_input: {}
        },
        placeholder: 'Search Here...',
        persist: true,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    let wp_modules = new TomSelect('#wp_modules', tomSelectOptions);
    let src_level_hours_id = new TomSelect('#src_level_hours_id', tomSelectOptions);
    let src_learning_hours_id = new TomSelect('#src_learning_hours_id', tomSelectOptions);

    if ($("#studentWorkPlacementTable").length) {
        // Init Table
        studentWorkPlacementTable.init();

        // Filter function
        function filterHTMLForm() {
            studentWorkPlacementTable.init();
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
            $("#status").val("1");
            filterHTMLForm();
        });
    }

    if ($("#studentWorkPlacementNwTable").length) {
        // Init Table
        studentWorkPlacementNwTable.init();

        // Filter function
        function filterHTMLForm() {
            studentWorkPlacementNwTable.init();
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
        $("#wp_tabulator-html-filter-go").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#wp_tabulator-html-filter-reset").on("click", function (event) {
            $("#wp_status").val("All");
            wp_modules.addItem('0');
            src_level_hours_id.addItem('0');
            src_learning_hours_id.clear(true);
            src_learning_hours_id.clearOptions();
            src_learning_hours_id.addOption({ value: '0', text: 'All' });
            src_learning_hours_id.addItem('0');
            filterHTMLForm();
        });

        $(document).on('change', '#src_level_hours_id', function(e){
            e.preventDefault();
            let theLevelHours = $(this).val();
            src_learning_hours_id.clear(true);
            src_learning_hours_id.clearOptions();
            src_learning_hours_id.disable(); 
            
            if(theLevelHours != '' && theLevelHours > 0){
                axios({
                    method: "post",
                    url: route('student.get.wp.learning.hours'),
                    data: {theLevelHours: theLevelHours},
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    src_learning_hours_id.addOption({ value: '0', text: 'All' });
                    src_learning_hours_id.enable();
                    if(response.status == 200){  
                        $.each(response.data.learning_hours, function(index, row) {
                            src_learning_hours_id.addOption({
                                value: row.id,
                                text: row.name
                            });
                        });
                        src_learning_hours_id.refreshOptions();
                        src_learning_hours_id.addItem('0');
                    }
                }).catch(error => {
                    src_learning_hours_id.addOption({ value: '0', text: 'All' });
                    src_learning_hours_id.enable();
                    src_learning_hours_id.addItem('0');
                    if (error.response) {
                        if (error.response.status == 304) {
                            console.log('content not found');
                        } else {
                            console.log('error');
                        }
                    }
                });
            }else{
                src_learning_hours_id.addOption({ value: '0', text: 'All' });
                src_learning_hours_id.enable();
                src_learning_hours_id.addItem('0');
            }
        });
    }

    $("#studentWorkPlacementNwTable").on('click', '.document_btn', function(e){
        e.preventDefault();
        let rowId = $(this).attr('data-id');
        $("#studentWorkPlacementDocumentsTable").attr('data-row', rowId);
        $("#uploadDocumentModal input[name='student_workplacement_id']").val(rowId);

        studentWorkPlacementDocumentsTable.init();

    });


    
    let wp_level_hours_add_select = new TomSelect('#addWpHourModal #level_hours_id', tomSelectOptions);
    let wp_learning_hours_add_select = new TomSelect('#addWpHourModal #learning_hours_id', tomSelectOptions);
    let wp_workplacement_setting_add_select = new TomSelect('#addWpHourModal #workplacement_setting_id', tomSelectOptions);
    let wp_workplacement_setting_type_add_select = new TomSelect('#addWpHourModal #workplacement_setting_type_id', tomSelectOptions);
    let wp_assign_module_list_add_select = new TomSelect('#addWpHourModal #assign_module_list_id', tomSelectOptions);
    let wp_company_add_select = new TomSelect('#addWpHourModal #company_id', tomSelectOptions);
    let wp_company_supervisor_add_select = new TomSelect('#addWpHourModal #company_supervisor_id', tomSelectOptions);
    let wp_contract_type_add_select = new TomSelect('#addWpHourModal #contract_type', tomSelectOptions);

    let wp_level_hours_edit_select = new TomSelect('#editWpHourModal #level_hours_id', tomSelectOptions);
    let wp_learning_hours_edit_select = new TomSelect('#editWpHourModal #learning_hours_id', tomSelectOptions);
    let wp_workplacement_setting_edit_select = new TomSelect('#editWpHourModal #workplacement_setting_id', tomSelectOptions);
    let wp_workplacement_setting_type_edit_select = new TomSelect('#editWpHourModal #workplacement_setting_type_id', tomSelectOptions);
    let wp_assign_module_list_edit_select = new TomSelect('#editWpHourModal #assign_module_list_id', tomSelectOptions);
    let wp_company_edit_select = new TomSelect('#editWpHourModal #company_id', tomSelectOptions);
    let wp_company_supervisor_edit_select = new TomSelect('#editWpHourModal #company_supervisor_id', tomSelectOptions);
    let wp_contract_type_edit_select = new TomSelect('#editWpHourModal #contract_type', tomSelectOptions);
    let wp_status_edit_select = new TomSelect('#editWpHourModal #status', tomSelectOptions);


    const successModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
    const confirmModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModal"));
    const warningModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModal"));

    const addHourModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addHourModal"));
    const addWpHourModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#addWpHourModal"));
    const editWpHourModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editWpHourModal"));
    const editHourModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editHourModal"));

    const addHourModalEl = document.getElementById('addHourModal')
    addHourModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#addHourModal .acc__input-error').html('');
        $('#addHourModal .modal-body select').val('');
        $('#addHourModal .modal-body input').val('');
        $('#addHourModal .modal-body select[name="company_supervisor_id"]').html('<option value="">Please Select</option>');
    });

    document.querySelector("#addWpHourModal").addEventListener('hide.tw.modal', function(event) {
        $('#addWpHourModal .acc__input-error').html('');
        $('#addWpHourModal .modal-body select').val('');
        $('#addWpHourModal .modal-body input').val('');
        $('#addWpHourModal .modal-body select[name="company_supervisor_id"]').html('<option value="">Please Select</option>');


        $('#add_step2').hide();
        $('#add_step1').show();
        $('.add-form-wizard-step-item').removeClass('active');
        $('.add-form-wizard-step-item').eq(0).addClass('active');


    });

    const editHourModalEl = document.getElementById('editHourModal')
    editHourModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editHourModal .acc__input-error').html('');
        $('#editHourModal .modal-body select').val('');
        $('#editHourModal .modal-body input').val('');
        $('#editHourModal .modal-body select[name="company_supervisor_id"]').html('<option value="">Please Select</option>');

        $('#editHourModal .modal-footer [name="id"]').val('0');
    });

    document.querySelector("#editWpHourModal").addEventListener('hide.tw.modal', function(event) {
        $('#editHourModal .acc__input-error').html('');
        $('#editHourModal .modal-body select').val('');
        $('#editHourModal .modal-body input').val('');
        $('#editHourModal .modal-body select[name="company_supervisor_id"]').html('<option value="">Please Select</option>');

        $('#editHourModal .modal-footer [name="id"]').val('0');

        $('#edit_step2').hide();
        $('#edit_step1').show();
        $('.edit-form-wizard-step-item').removeClass('active');
        $('.edit-form-wizard-step-item').eq(0).addClass('active');


        wp_level_hours_edit_select.disable();

        wp_learning_hours_edit_select.clear(true);
        wp_learning_hours_edit_select.disable();

        wp_assign_module_list_edit_select.clear(true);
        wp_assign_module_list_edit_select.disable();

        wp_contract_type_edit_select.clear(true);
        wp_contract_type_edit_select.disable();
    });

    $('#successModal .successCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            successModal.hide();
            window.location.reload();
        }else{
            successModal.hide();
        }
    })

    $('#warningModal .warningCloser').on('click', function(e){
        e.preventDefault();
        if($(this).attr('data-action') == 'RELOAD'){
            warningModal.hide();
            window.location.reload();
        }else{
            warningModal.hide();
        }
    })

    $('[name="company_id"]').on('change', function(e){
        e.preventDefault();
        let $theSelect = $(this);
        let $supervisorWrap = $theSelect.parent('div').siblings('.supervisorWrap');

        let theCompany = $theSelect.val();
        if(theCompany != '' && theCompany > 0){
            $('[name="company_supervisor_id"]', $supervisorWrap).val('').html('<option value="">Please Select</option>');
            axios({
                method: "post",
                url: route('student.get.company.supervisor'),
                data: {theCompany : theCompany},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('[name="company_supervisor_id"]', $supervisorWrap).val('').html(response.data.res);
            }).catch(error => {
                if (error.response.status == 422) {
                    console.log('error');
                }
            });
        }else{
            $('[name="company_supervisor_id"]', $supervisorWrap).val('').html('<option value="">Please Select</option>');
        }
    });

    
    $(document).on('change', '#addWpHourForm [name="level_hours_id"]', function(e){
        e.preventDefault();
        let theLevelHours = $(this).val();
        wp_learning_hours_add_select.clear(true);
        wp_learning_hours_add_select.disable(); 
        
        if(theLevelHours != '' && theLevelHours > 0){
            axios({
                method: "post",
                url: route('student.get.wp.learning.hours'),
                data: {theLevelHours: theLevelHours},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                wp_learning_hours_add_select.enable();
                if(response.status == 200){  
                    $.each(response.data.learning_hours, function(index, row) {
                        wp_learning_hours_add_select.addOption({
                            value: row.id,
                            text: row.name
                        });
                    });
                    wp_learning_hours_add_select.refreshOptions();
                }
            }).catch(error => {
                wp_learning_hours_add_select.enable();
                if (error.response) {
                    if (error.response.status == 304) {
                        console.log('content not found');
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $(document).on('change', '#addWpHourForm [name="learning_hours_id"]', function(){
        let $theLearningHour = $(this);
        let theLearningHour = $theLearningHour.val();

        if(theLearningHour > 0){
            axios({
                method: "post",
                url: route('student.get.wp.learning.hour'),
                data: {theLearningHour: theLearningHour},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if(response.status == 200){  
                    let learningHour = response.data.learning_hour;
                    if(learningHour.module_required == 1){
                        $('#addWpHourForm [name="module_required"]').val(1);
                        $('#addWpHourForm .modReq').removeClass('hidden');
                    }else{
                        $('#addWpHourForm [name="module_required"]').val(0);
                        $('#addWpHourForm .modReq').addClass('hidden');
                    }
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#addWpHourForm [name="module_required"]').val(0);
            $('#addWpHourForm .modReq').addClass('hidden');
        }
    })

    $(document).on('change', '#editWpHourForm [name="level_hours_id"]', function(e){
        e.preventDefault();
        let theLevelHours = $(this).val();
        wp_learning_hours_edit_select.clear(true);
        wp_learning_hours_edit_select.disable(); 
        
        if(theLevelHours != '' && theLevelHours > 0){
            axios({
                method: "post",
                url: route('student.get.wp.learning.hours'),
                data: {theLevelHours: theLevelHours},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                wp_learning_hours_edit_select.enable();
                wp_learning_hours_edit_select.clearOptions();
                if(response.status == 200){  
                    $.each(response.data.learning_hours, function(index, row) {
                        wp_learning_hours_edit_select.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                }
            }).catch(error => {
                wp_learning_hours_edit_select.enable();
                if (error.response) {
                    if (error.response.status == 304) {
                        console.log('content not found');
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $(document).on('change', '#editWpHourForm [name="learning_hours_id"]', function(){
        let $theLearningHour = $(this);
        let theLearningHour = $theLearningHour.val();

        if(theLearningHour > 0){
            axios({
                method: "post",
                url: route('student.get.wp.learning.hour'),
                data: {theLearningHour: theLearningHour},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if(response.status == 200){  
                    let learningHour = response.data.learning_hour;
                    if(learningHour.module_required == 1){
                        $('#editWpHourForm [name="module_required"]').val(1);
                        $('#editWpHourForm .modReq').removeClass('hidden');
                    }else{
                        $('#editWpHourForm [name="module_required"]').val(0);
                        $('#editWpHourForm .modReq').addClass('hidden');
                    }
                }
            }).catch(error => {
                if (error.response) {
                    console.log('error');
                }
            });
        }else{
            $('#editWpHourForm [name="module_required"]').val(0);
            $('#editWpHourForm .modReq').addClass('hidden');
        }
    })

    $(document).on('change', '#addWpHourForm [name="workplacement_setting_id"]', function(e) {
        e.preventDefault();
        let theWpSetting = $(this).val();
        wp_workplacement_setting_type_add_select.clear(true);
        wp_workplacement_setting_type_add_select.clearOptions(); 
        wp_workplacement_setting_type_add_select.disable(); 
        
        if(theWpSetting != '' && theWpSetting > 0){
            axios({
                method: "post",
                url: route('student.get.wp.setting.type'),
                data: {theWpSetting: theWpSetting},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                wp_workplacement_setting_type_add_select.enable();
                if(response.status == 200){  
                    $.each(response.data.wp_setting_types, function(index, row) {
                        wp_workplacement_setting_type_add_select.addOption({
                            value: row.id,
                            text: row.type,
                        });
                    });
                }
            }).catch(error => {
                wp_workplacement_setting_type_add_select.enable();
                if (error.response) {
                    if (error.response.status == 304) {
                        console.log('content not found');
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });


    $(document).on('change', '#editWpHourForm [name="workplacement_setting_id"]', function(e) {
        e.preventDefault();
        let theWpSetting = $(this).val();
        
        if(theWpSetting != '' && theWpSetting > 0){
            axios({
                method: "post",
                url: route('student.get.wp.setting.type'),
                data: {theWpSetting: theWpSetting},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                wp_workplacement_setting_type_edit_select.enable();
                wp_workplacement_setting_type_edit_select.clearOptions(); 
                if(response.status == 200){  
                    $.each(response.data.wp_setting_types, function(index, row) {
                        wp_workplacement_setting_type_edit_select.addOption({
                            value: row.id,
                            text: row.type,
                        });
                    });
                }
            }).catch(error => {
                wp_workplacement_setting_type_edit_select.enable();
                if (error.response) {
                    if (error.response.status == 304) {
                        console.log('content not found');
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });


    $(document).on('change', '#addWpHourForm [name="company_id"]', function(e){
        e.preventDefault();
        let theCompany = $(this).val();
        wp_company_supervisor_add_select.clear(true);
        wp_company_supervisor_add_select.clearOptions(); 
        wp_company_supervisor_add_select.disable(); 
        let $companySupervisorSelect = $(this).closest('form').find('#company_supervisor_id');
        
        if(theCompany != '' && theCompany > 0){
            $companySupervisorSelect.html('<option value="">Loading...</option>');
            
            axios({
                method: "post",
                url: route('student.get.companysupervisor'),
                data: {theCompany: theCompany},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                wp_company_supervisor_add_select.enable();
                if(response.status == 200){  
                    $.each(response.data.supervisors, function(index, row) {
                        wp_company_supervisor_add_select.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                    wp_company_supervisor_add_select.refreshOptions();
                }
            }).catch(error => {
                wp_company_supervisor_add_select.enable();
                if (error.response) {
                    if (error.response.status == 304) {
                        console.log('content not found');
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });
       $(document).on('change', '#editWpHourForm [name="company_id"]', function(e){
        e.preventDefault();
        let theCompany = $(this).val();
        wp_company_supervisor_edit_select.clear(true);
        wp_company_supervisor_edit_select.disable(); 
        
        if(theCompany != '' && theCompany > 0){
            
            axios({
                method: "post",
                url: route('student.get.companysupervisor'),
                data: {theCompany: theCompany},
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                wp_company_supervisor_edit_select.enable();
				wp_company_supervisor_edit_select.clearOptions();
                if(response.status == 200){  
                    $.each(response.data.supervisors, function(index, row) {
                        wp_company_supervisor_edit_select.addOption({
                            value: row.id,
                            text: row.name,
                        });
                    });
                }
            }).catch(error => {
                wp_company_supervisor_edit_select.enable();
                if (error.response) {
                    if (error.response.status == 304) {
                        console.log('content not found');
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });

    $('#addHourForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('addHourForm');
    
        document.querySelector('#saveWP').setAttribute('disabled', 'disabled');
        document.querySelector("#saveWP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.store.work.placement.hour'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#saveWP').removeAttribute('disabled');
            document.querySelector("#saveWP svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addHourModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student work placement hours successfully inserted.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
                studentWorkPlacementTable.init();
            }
        }).catch(error => {
            document.querySelector('#saveWP').removeAttribute('disabled');
            document.querySelector("#saveWP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addHourForm .${key}`).addClass('border-danger');
                        $(`#addHourForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    
    $("#studentWorkPlacementTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let row_id = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("student.edit.work.placement.hour", row_id),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;

                $('#editHourModal [name="company_id"]').val(dataset.company_id ? dataset.company_id : '');
                $('#editHourModal [name="company_supervisor_id"]').html(dataset.supervisor_html ? dataset.supervisor_html : '<option value="">Please Select</option>');
                $('#editHourModal [name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                $('#editHourModal [name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                $('#editHourModal [name="hours"]').val(dataset.hours ? dataset.hours : '');
                $('#editHourModal [name="contract_type"]').val(dataset.contract_type ? dataset.contract_type : '');

                $('#editHourModal [name="id"]').val(row_id ? row_id : '');
                
            }
        }).catch((error) => {
            console.log(error);
        });
    });
    $("#studentWorkPlacementNwTable").on("click", ".edit_btn", function () {      
        let $editBtn = $(this);
        let row_id = $editBtn.attr("data-id");

        axios({
            method: "get",
            url: route("student.edit.workplacement.hour", row_id),
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        }).then((response) => {
            if (response.status == 200) {
                let dataset = response.data.res;

                $('#editWpHourForm [name="start_date"]').val(dataset.start_date ? dataset.start_date : '');
                $('#editWpHourForm [name="end_date"]').val(dataset.end_date ? dataset.end_date : '');
                $('#editWpHourForm [name="hours"]').val(dataset.hours ? dataset.hours : '');

                // level hours list
                wp_level_hours_edit_select.enable();
                wp_level_hours_edit_select.clearOptions();
                $.each(response.data.level_hours, function(index, row) {
                    wp_level_hours_edit_select.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                (response.data.level_hours_id ? wp_level_hours_edit_select.addItem(response.data.level_hours_id) : wp_level_hours_edit_select.clear(true));

                // learning hours list
                wp_learning_hours_edit_select.enable();
                wp_learning_hours_edit_select.clearOptions();
                $.each(response.data.learning_hours, function(index, row) {
                    wp_learning_hours_edit_select.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                (response.data.learning_hours_id ? wp_learning_hours_edit_select.addItem(response.data.learning_hours_id) : wp_learning_hours_edit_select.clear(true));

                //  Work Placement Setting list
                wp_workplacement_setting_edit_select.enable();
                wp_workplacement_setting_edit_select.clearOptions();
                $.each(response.data.workplacement_settings, function(index, row) {
                    wp_workplacement_setting_edit_select.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                (response.data.workplacement_setting_id ? wp_workplacement_setting_edit_select.addItem(response.data.workplacement_setting_id) : wp_workplacement_setting_edit_select.clear(true));


                //  Work Placement Setting Type list
                wp_workplacement_setting_type_edit_select.enable();
                wp_workplacement_setting_type_edit_select.clearOptions();
                $.each(response.data.workplacement_setting_types, function(index, row) {
                    wp_workplacement_setting_type_edit_select.addOption({
                        value: row.id,
                        text: row.type,
                    });
                });
                (response.data.workplacement_setting_type_id ? wp_workplacement_setting_type_edit_select.addItem(response.data.workplacement_setting_type_id) : wp_workplacement_setting_type_edit_select.clear(true));

                //  Company list
                wp_company_edit_select.enable();
                wp_company_edit_select.clearOptions();
                $.each(response.data.companies, function(index, row) {
                    wp_company_edit_select.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                (response.data.company_id ? wp_company_edit_select.addItem(response.data.company_id) : wp_company_edit_select.clear(true));

                //   Company Supervisor list
                wp_company_supervisor_edit_select.enable();
                wp_company_supervisor_edit_select.clearOptions();
                $.each(response.data.supervisors, function(index, row) {
                    wp_company_supervisor_edit_select.addOption({
                        value: row.id,
                        text: row.name,
                    });
                });
                (response.data.supervisor_id ? wp_company_supervisor_edit_select.addItem(response.data.supervisor_id) : wp_company_supervisor_edit_select.clear(true));

                // assign module list
                wp_assign_module_list_edit_select.enable();
                wp_assign_module_list_edit_select.clearOptions();
                $.each(response.data.assign_module_lists, function(index, row) {
                    wp_assign_module_list_edit_select.addOption({
                        value: row.id,
                        text: row.module_name,
                    });
                });
                (response.data.asign_module_list_id ? wp_assign_module_list_edit_select.addItem(response.data.asign_module_list_id) : wp_assign_module_list_edit_select.clear(true));

                // contract type
                wp_contract_type_edit_select.enable();
                wp_contract_type_edit_select.clearOptions();
                $.each([{"id": "Permanent","value":"Permanent"},{"id": "Temporary","value":"Temporary"},{"id":"Contract Base","value":"Contract Base"},{"id":"Part-time","value":"Part-time"}], function(index, row) {
                    wp_contract_type_edit_select.addOption({
                        value: row.id,
                        text: row.value,
                    });
                });
                (dataset.contract_type ? wp_contract_type_edit_select.addItem(dataset.contract_type) : wp_contract_type_edit_select.clear(true));

                // status
                wp_status_edit_select.enable();
                wp_status_edit_select.clearOptions();
                $.each([{"id": "Pending","value":"Pending"},{"id": "Rejected","value":"Rejected"},{"id":"Confirmed","value":"Confirmed"}], function(index, row) {
                    wp_status_edit_select.addOption({
                        value: row.id,
                        text: row.value,
                    });
                });
                (dataset.status ? wp_status_edit_select.addItem(dataset.status) : wp_status_edit_select.clear(true));

                $('#editWpHourForm [name="id"]').val(row_id ? row_id : '');

                if(response.data.module_required == 1){
                    $('#editWpHourForm [name="module_required"]').val(1);
                    $('#editWpHourForm .modReq').removeClass('hidden');
                }else{
                    $('#editWpHourForm [name="module_required"]').val(0);
                    $('#editWpHourForm .modReq').addClass('hidden');
                }
                
            }
        }).catch((error) => {
            console.log(error);
        });
    });

    $('#editHourForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editHourForm');
    
        document.querySelector('#updateWP').setAttribute('disabled', 'disabled');
        document.querySelector("#updateWP svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.work.placement.hour'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#updateWP').removeAttribute('disabled');
            document.querySelector("#updateWP svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editHourModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html('Student work placement hours successfully updated.');
                    $("#successModal .successCloser").attr('data-action', 'RELOAD');
                });  
                
               studentWorkPlacementTable.init();
            }
        }).catch(error => {
            document.querySelector('#updateWP').removeAttribute('disabled');
            document.querySelector("#updateWP svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editHourForm .${key}`).addClass('border-danger');
                        $(`#editHourForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#editWpHourModal').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editWpHourForm');
    
        document.querySelector('#wpHourUpdateBtn').setAttribute('disabled', 'disabled');
        document.querySelector("#wpHourUpdateBtn svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('student.update.workplacement.hour'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#wpHourUpdateBtn').removeAttribute('disabled');
            document.querySelector("#wpHourUpdateBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                editWpHourModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                });  
                
                studentWorkPlacementNwTable.init();
            }
        }).catch(error => {
            document.querySelector('#wpHourUpdateBtn').removeAttribute('disabled');
            document.querySelector("#wpHourUpdateBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editHourForm .${key}`).addClass('border-danger');
                        $(`#editHourForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $('#studentWorkPlacementTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEWPH');
        });
    });
    $('#studentWorkPlacementNwTable').on('click', '.delete_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to delete these record? If yes then please click on the agree btn.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'DELETEWPH');
        });
    });

    // Restore Course
    $('#studentWorkPlacementTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREWPH');
        });
    });

    $('#studentWorkPlacementNwTable').on('click', '.restore_btn', function(){
        let $statusBTN = $(this);
        let row_id = $statusBTN.attr('data-id');

        confirmModal.show();
        document.getElementById('confirmModal').addEventListener('shown.tw.modal', function(event){
            $('#confirmModal .confModTitle').html('Are you sure?');
            $('#confirmModal .confModDesc').html('Do you really want to restore these record? Click on agree to continue.');
            $('#confirmModal .agreeWith').attr('data-id', row_id);
            $('#confirmModal .agreeWith').attr('data-action', 'RESTOREWPH');
        });
    });

    $('#confirmModal .agreeWith').on('click', function(){
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModal button').attr('disabled', 'disabled');
        if(action == 'DELETEWPH'){
            axios({
                method: 'delete',
                url: route('student.destroy.work.placement.hour', recordID),
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record successfully deleted from DB row.');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTOREWPH'){
            axios({
                method: 'post',
                url: route('student.restore.work.placement.hour'),
                data: {row_id : recordID},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('#confirmModal button').removeAttr('disabled');
                    confirmModal.hide();

                    successModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('WOW!');
                        $('#successModal .successModalDesc').html('Record Successfully Restored!');
                        $('#successModal .successCloser').attr('data-action', 'RELOAD');
                    });

                    setTimeout(function(){
                        successModal.hide();
                        window.location.reload();
                    }, 2000);
                }
            }).catch(error =>{
                console.log(error)
            });
        }else{
            confirmModal.hide();
        }
    })

    $(document).on('click', '.add-step1-wizard-next-btn', function() {
        
        var nextStep = $(this).data('next');
        console.log(nextStep)
        var currentStep = $(this).closest('.add-step1-wizard-step');

        let stepOneIsValid = true;
        let errors = 0;
      

         if(!wp_level_hours_add_select.getValue()){
             $("#addWpHourModal .error-level_hours_id").text("Level Hours field is required!");
             //stepOneIsValid = false;
             errors += 1;
        }else{
             $("#addWpHourModal .error-level_hours_id").text("");
             //stepOneIsValid = true;
        }

         if(!wp_learning_hours_add_select.getValue()){
             $("#addWpHourModal .error-learning_hours_id").text("Learning Hours field is required!");
             //stepOneIsValid = false;
             errors += 1;
        }else{
             $("#addWpHourModal .error-learning_hours_id").text("");
             //stepOneIsValid = true;
        }

         if(!wp_workplacement_setting_add_select.getValue()){
             $("#addWpHourModal .error-workplacement_setting_id").text("Workplacement Setting field is required!");
             //stepOneIsValid = false;
             errors += 1;
        }else{
             $("#addWpHourModal .error-workplacement_setting_id").text("");
             //stepOneIsValid = true;
        }

         if(!wp_workplacement_setting_type_add_select.getValue()){
             $("#addWpHourModal .error-workplacement_setting_type_id").text("Workplacement Setting Type field is required!");
             //stepOneIsValid = false;
             errors += 1;
        }else{
             $("#addWpHourModal .error-workplacement_setting_type_id").text("");
             //stepOneIsValid = true;
        }

         if(!wp_company_add_select.getValue()){
             $("#addWpHourModal .error-company_id").text("Company field is required!");
             //stepOneIsValid = false;
             errors += 1;
        }else{
             $("#addWpHourModal .error-company_id").text("");
             //stepOneIsValid = true;
        }
         if(!wp_company_supervisor_add_select.getValue()){
             $("#addWpHourModal .error-company_supervisor_id").text("Company Supervisor field is required!");
             //stepOneIsValid = false;
             errors += 1;
        }else{
             $("#addWpHourModal .error-company_supervisor_id").text("");
             //stepOneIsValid = true;
        }
        let moduleRequired = $('#addWpHourModal [name="module_required"]').val();
        if(moduleRequired == 1 && !wp_assign_module_list_add_select.getValue()){
             $("#addWpHourModal .error-assign_module_list_id").text("Assign Module List field is required!");
             //stepOneIsValid = false;
             errors += 1;
        }else{
             $("#addWpHourModal .error-assign_module_list_id").text("");
             //stepOneIsValid = true;
        }
        if(errors > 0){
            stepOneIsValid = false;
        }

        // Validate current step before proceeding
        if(stepOneIsValid) {
            currentStep.hide();
            $('#' + nextStep).show();
            updateWizardProgress(nextStep);
            //console.log(nextStep);
        }
    });
    
    $(document).on('click', '.add-step1-wizard-prev-btn', function() {
        var prevStep = $(this).data('prev');
        var currentStep = $(this).closest('.add-step2-wizard-step');

        currentStep.hide();
        $('#' + prevStep).show();
        updateWizardProgress(prevStep);
    });
    
    function updateWizardProgress(activeStep) {
        $('.add-form-wizard-step-item').removeClass('active');
        if(activeStep === 'add_step2') {
            $('.add-form-wizard-step-item').eq(1).addClass('active');
            console.log('1')
            console.log($('.form-wizard-step-item'))
        } else {
            $('.add-form-wizard-step-item').eq(0).addClass('active');
            console.log('0')
        }
    }
    
 
    
    $(document).on('submit', '#addWpHourForm', function(e) {
        e.preventDefault();
        
        let secondStepValid = true;
        let hours = $("#addWpHourModal #hours").val();
        
        if(!hours) {
            $("#addWpHourModal .error-hours").text("Hours field is required!");
            secondStepValid = false;
        }else{
            $("#addWpHourModal .error-hours").text("");
            secondStepValid = true;
        }

        if(!wp_contract_type_add_select.getValue()){
             $("#addWpHourModal .error-contract_type").text("Contract Type field is required!");
             secondStepValid = false;
        }else{
             $("#addWpHourModal .error-contract_type").text("");
             secondStepValid = true;
        }

        let start_date = $("#addWpHourModal #start_date").val();
        
        if(!start_date) {
            $("#addWpHourModal .error-start_date").text("Start Date field is required!");
            secondStepValid = false;
        }else{
            $("#addWpHourModal .error-start_date").text("");
            secondStepValid = true;
        }
        
        if (!secondStepValid) {
            return false;
        }
        
        $('#wpHourInsertBtn svg').show();
        $('#wpHourInsertBtn').prop('disabled', true);

        const form = document.getElementById('addWpHourForm');
        const form_data = new FormData(form);
        
        axios({
            method: "post",
            url: route('student.store.workplacement.hour'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#wpHourInsertBtn').removeAttribute('disabled');
            document.querySelector("#wpHourInsertBtn svg").style.cssText = "display: none;";

            if (response.status == 200) {
                addWpHourModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                }); 
                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 1500);
                studentWorkPlacementNwTable.init();
            }
        }).catch(error => {
            document.querySelector('#wpHourInsertBtn').removeAttribute('disabled');
            document.querySelector("#wpHourInsertBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#addWpHourForm .${key}`).addClass('border-danger');
                        $(`#addWpHourForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });

    $(document).on('click', '.edit-step1-wizard-next-btn', function() {
        let nextStep = $(this).data('next');
        let currentStep = $(this).closest('.edit-step1-wizard-step');

        let editStepOneIsValid = true;
        let errors = 0;
      
         if(!wp_level_hours_edit_select.getValue()){
             $("#editWpHourModal .error-level_hours_id").text("Level Hours field is required!");
             //editStepOneIsValid = false;
             errors += 1;
        }else{
             $("#editWpHourModal .error-level_hours_id").text("");
             //editStepOneIsValid = true;
        }

         if(!wp_learning_hours_edit_select.getValue()){
             $("#editWpHourModal .error-learning_hours_id").text("Learning Hours field is required!");
             //editStepOneIsValid = false;
             errors += 1;
        }else{
             $("#editWpHourModal .error-learning_hours_id").text("");
             //editStepOneIsValid = true;
        }

         if(!wp_workplacement_setting_edit_select.getValue()){
             $("#editWpHourModal .error-workplacement_setting_id").text("Workplacement Setting field is required!");
             //editStepOneIsValid = false;
             errors += 1;
        }else{
             $("#editWpHourModal .error-workplacement_setting_id").text("");
             //editStepOneIsValid = true;
        }

         if(!wp_workplacement_setting_type_edit_select.getValue()){
             $("#editWpHourModal .error-workplacement_setting_type_id").text("Workplacement Setting Type field is required!");
             //editStepOneIsValid = false;
             errors += 1;
        }else{
             $("#editWpHourModal .error-workplacement_setting_type_id").text("");
             //editStepOneIsValid = true;
        }

         if(!wp_company_edit_select.getValue()){
             $("#editWpHourModal .error-company_id").text("Company field is required!");
             //editStepOneIsValid = false;
             errors += 1;
        }else{
             $("#editWpHourModal .error-company_id").text("");
             //editStepOneIsValid = true;
        }
         if(!wp_company_supervisor_edit_select.getValue()){
             $("#editWpHourModal .error-company_supervisor_id").text("Company Supervisor field is required!");
             //editStepOneIsValid = false;
             errors += 1;
        }else{
             $("#editWpHourModal .error-company_supervisor_id").text("");
             //editStepOneIsValid = true;
        }
        let moduleRequired = $('#editWpHourModal [name="module_required"]').val();
        if(moduleRequired == 1 && !wp_assign_module_list_edit_select.getValue()){
             $("#editWpHourModal .error-assign_module_list_id").text("Assign Module List field is required!");
             //editStepOneIsValid = false;
             errors += 1;
        }else{
             $("#editWpHourModal .error-assign_module_list_id").text("");
             //editStepOneIsValid = true;
        }

        if(errors > 0){
            editStepOneIsValid = false;
        }

        if(editStepOneIsValid) {
            currentStep.hide();
            $('#' + nextStep).show();
            updateWizardProgress(nextStep);
        }
    });
    
    $(document).on('click', '.edit-wizard-prev-btn', function() {
        let prevStep = $(this).data('prev');
        let currentStep = $(this).closest('.edit-step2-wizard-step');
        
        currentStep.hide();
        $('#' + prevStep).show();
        updateWizardProgress(prevStep);
    });
    
    function updateWizardProgress(activeStep) {
        $('.edit-form-wizard-step-item').removeClass('active');
        if(activeStep === 'edit_step2') {
            $('.edit-form-wizard-step-item').eq(1).addClass('active');
        } else {
            $('.edit-form-wizard-step-item').eq(0).addClass('active');
        }
    }
    
    $(document).on('submit', '#editWpHourForm', function(e) {
        e.preventDefault();
        
        let editSecondStepValid = true;

        let hours = $("#editWpHourModal #hours").val();
        if(!hours) {
            $("#editWpHourModal .error-hours").text("Hours field is required!");
            editSecondStepValid = false;
        }else{
            $("#editWpHourModal .error-hours").text("");
            editSecondStepValid = true;
        }

        if(!wp_contract_type_edit_select.getValue()){
             $("#editWpHourModal .error-contract_type").text("Contract Type field is required!");
             editSecondStepValid = false;
        }else{
             $("#editWpHourModal .error-contract_type").text("");
             editSecondStepValid = true;
        }

        let start_date = $("#editWpHourModal #start_date").val();
        if(!start_date) {
            $("#editWpHourModal .error-start_date").text("Start Date field is required!");
            editSecondStepValid = false;
        }else{
            $("#editWpHourModal .error-start_date").text("");
            editSecondStepValid = true;
        }
        
        if (!editSecondStepValid) {
            return false;
        }
        
        $('#wpHourUpdateBtn svg').show();
        $('#wpHourUpdateBtn').prop('disabled', true);

        const form = document.getElementById('editWpHourForm');
        const form_data = new FormData(form);
        
        axios({
            method: "post",
            url: route('student.update.workplacement.hour'),
            data: form_data,
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            document.querySelector('#wpHourUpdateBtn').removeAttribute('disabled');
            document.querySelector("#wpHourUpdateBtn svg").style.cssText = "display: none;";

            $(".hoursCompleted").html(response.data.total_completed_hours + ' Hours');

            $('#edit_step2').hide();
            $('#edit_step1').show();
            $('.edit-form-wizard-step-item').removeClass('active');
            $('.edit-form-wizard-step-item').eq(0).addClass('active');

            if (response.status == 200) {
                editWpHourModal.hide();

                successModal.show(); 
                document.getElementById("successModal").addEventListener("shown.tw.modal", function (event) {
                    $("#successModal .successModalTitle").html("Congratulation!" );
                    $("#successModal .successModalDesc").html(response.data.message);
                });  
                
                studentWorkPlacementNwTable.init();

                setTimeout(function(){
                    successModal.hide();
                    window.location.reload();
                }, 1500);

            }
        }).catch(error => {
            document.querySelector('#wpHourUpdateBtn').removeAttribute('disabled');
            document.querySelector("#wpHourUpdateBtn svg").style.cssText = "display: none;";
            if (error.response) {
                if (error.response.status == 422) {
                    for (const [key, val] of Object.entries(error.response.data.errors)) {
                        $(`#editWpHourForm .${key}`).addClass('border-danger');
                        $(`#editWpHourForm  .error-${key}`).html(val);
                    }
                } else {
                    console.log('error');
                }
            }
        });
    });
    
    // Clear validation errors when user starts typing/selecting
    $('input, select').on('input change', function() {
        const fieldName = $(this).attr('name');
        if (fieldName) {
            $(`.error-${fieldName}`).text('');
        }
    });


    $('.theTogglers').on('click', function(e){
        e.preventDefault();
        $('.collapsibles').slideToggle();
    })


})();