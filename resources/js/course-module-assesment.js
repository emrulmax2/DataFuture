import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var moduleAssesmentListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let querystr = $("#query-01").val() != "" ? $("#query-01").val() : "";
        let status = $("#status-01").val() != "" ? $("#status-01").val() : "";
        let module = $("#moduleAssesmentDataTable").attr('data-courseid') != "" ? $("#moduleAssesmentDataTable").attr('data-courseid') : "0";

        let tableContent = new Tabulator("#moduleAssesmentDataTable", {
            ajaxURL: route("course.module.assesment.list"),
            ajaxParams: { querystr: querystr, status: status, module: module},
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
                },
                {
                    title: "Code",
                    field: "code",
                    headerHozAlign: "left",
                },
                {
                    title: "View In Plans",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().view_in_plan == 1) {
                            btns = `<div class=" form-switch">
                                        <input  id="view_in_plan" class="form-check-input view-plans" data-course_module_id="${cell.getData().course_module_id}" data-assessment_type_id="${cell.getData().assessment_type_id}" data-is_result_segment="${cell.getData().is_result_segment}" data-id="${cell.getData().id}" name="view_in_plan" value="1" checked type="checkbox">
                                        <label class="form-check-label" for="view_in_plan">&nbsp;</label>
                                    </div>`;
                        }else {
                            btns = `<div class=" form-switch">
                                        <input  id="view_in_plan" class="form-check-input view-plans" data-course_module_id="${cell.getData().course_module_id}" data-assessment_type_id="${cell.getData().assessment_type_id}" data-is_result_segment="${cell.getData().is_result_segment}" data-id="${cell.getData().id}" name="view_in_plan" value="1" type="checkbox">
                                        <label class="form-check-label" for="view_in_plan">&nbsp;</label>
                                    </div>`;
                        }
                        
                        return btns;
                    },
                },
                {
                    title: "Have Result Segment?",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "center",
                    width: "180",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().is_result_segment == 1) {
                            btns = "Yes";
                        }else {
                            btns = "No";
                        }
                        
                        return btns;
                    },
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
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#moduleAssesmentEditModal" type="button" class="edit_btn btn-rounded btn btn-success text-white p-0 w-9 h-9 ml-1"><i data-lucide="Pencil" class="w-4 h-4"></i></a>';
                            btns += '<button data-id="' +cell.getData().id +'"  class="delete_btn btn btn-danger text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="Trash2" class="w-4 h-4"></i></button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="' +cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }
                        
                        return btns;
                    },
                },
            ],
            renderComplete() {
                const succReModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
                $("#moduleAssesmentDataTable .view-plans").on('click', function(){
                    let view_in_plan = 0   
                    let tthis = $(this);
                    let id = tthis.data("id")
                    let course_module_id  = tthis.data("course_module_id")
                    let assessment_type_id  = tthis.data("assessment_type_id")
                    let is_result_segment  = tthis.data("is_result_segment")
                    if(tthis.prop("checked")) {
                        view_in_plan = 1;
                    }
                    axios({
                        method: "post",
                        url: route('course.module.assesment.update'),
                        data: { id: id, view_in_plan: view_in_plan ,course_module_id:course_module_id, assessment_type_id:assessment_type_id, is_result_segment:is_result_segment },
                        headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                    }).then(response => {
                        
                        if (response.status == 200) {
                           
                            //succReModal.show();
                            // document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            //     $('#successModal .successModalTitle').html('Congratulations!');
                            //     $('#successModal .successModalDesc').html('Course Module Assesment data successfully updated.');
                            // });
                            moduleAssesmentListTable.init();
                        }
                        
                    }).catch(error => {
                        if (error.response) {
                            if (error.response.status == 422) {
                                for (const [key, val] of Object.entries(error.response.data.errors)) {
                                    $(`#moduleAssesmentEditForm .${key}`).addClass('border-danger')
                                    $(`#moduleAssesmentEditForm  .error-${key}`).html(val)
                                }
                            } else {
                                console.log('error');
                            }
                        }
                    });    
                });
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
                sheetName: "Course Module Assessment",
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
    if ($("#moduleAssesmentDataTable").length) {
        // Init Table
        moduleAssesmentListTable.init();

        // Filter function
        function filterHTMLForm() {
            moduleAssesmentListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-01")[0].addEventListener(
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
        $("#tabulator-html-filter-go-01").on("click", function (event) {
            filterHTMLForm();
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-01").on("click", function (event) {
            $("#query-01").val("");
            $("#status-01").val("1");
            filterHTMLForm();
        });


        const moduleAssesmentAddModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleAssesmentAddModal"));
        const moduleAssesmentEditModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#moduleAssesmentEditModal"));
        const succModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModal"));
        const confirmModalCMA = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCMA"));
        
        let confModalDelTitle = 'Are you sure?';
        let confModalDelDescription = 'Do you really want to delete these records? <br>This process cannot be undone.';
        let confModalRestDescription = 'Do you really want to re-store these records? Click agree to continue.';

        const moduleAssesmentAddModalEl = document.getElementById('moduleAssesmentAddModal')
        moduleAssesmentAddModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#moduleAssesmentAddModal .acc__input-error').html('');
            $('#moduleAssesmentAddModal input[type="text"]').val('');
        });
        
        const moduleAssesmentEditModalEl = document.getElementById('moduleAssesmentEditModal')
        moduleAssesmentEditModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#moduleAssesmentEditModal .acc__input-error').html('');
            $('#moduleAssesmentEditModal input[type="text"]').val('');
            $('#moduleAssesmentEditModal input[name="id"]').val('0');
        });

        const confirmModalCMAEL = document.getElementById('confirmModalCMA');
        confirmModalCMAEL.addEventListener('hidden.tw.modal', function(event){
            $('#confirmModalCMA .agreeWithCMA').attr('data-id', '0');
            $('#confirmModalCMA .agreeWithCMA').attr('data-action', 'none');
        });


        // Delete Course
        $('#moduleAssesmentDataTable').on('click', '.delete_btn', function(){
            let $statusBTN = $(this);
            let rowID = $statusBTN.attr('data-id');

            confirmModalCMA.show();
            document.getElementById('confirmModalCMA').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCMA .confModTitleCMA').html(confModalDelTitle);
                $('#confirmModalCMA .confModDescCMA').html('Do you really want to delete these record? If yes, the please click on agree btn.');
                $('#confirmModalCMA .agreeWithCMA').attr('data-id', rowID);
                $('#confirmModalCMA .agreeWithCMA').attr('data-action', 'DELETE');
            });
        });

        $('#moduleAssesmentDataTable').on('click', '.restore_btn', function(){
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalCMA.show();
            document.getElementById('confirmModalCMA').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCMA .confModTitleCMA').html(confModalDelTitle);
                $('#confirmModalCMA .confModDescCMA').html('Do you really want to restore these record?');
                $('#confirmModalCMA .agreeWithCMA').attr('data-id', courseID);
                $('#confirmModalCMA .agreeWithCMA').attr('data-action', 'RESTORE');
            });
        });
        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Search Here...',
            persist: false,
            create: true,
            allowEmptyOption: true,
            onDelete: function (values) {
                return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
            },
        };
        var tomSelectArray = [];
        $('.assementlccTom').each(function(){
            if ($(this).attr("multiple") !== undefined) {
                tomOptions = {
                    ...tomOptions,
                    plugins: {
                        ...tomOptions.plugins,
                        remove_button: {
                            title: "Remove this item",
                        },
                    }
                };
            }
            tomSelectArray.push(new TomSelect(this, tomOptions));
        });
        // Confirm Modal Action
        $('#confirmModalCMA .agreeWithCMA').on('click', function(){
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalCMA button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('course.module.assesment.destory', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCMA button').removeAttr('disabled');
                        confirmModalCMA.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Done!');
                            $('#successModal .successModalDesc').html('Course module assesment data successfully deleted.');
                        });
                    }
                    moduleAssesmentListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('course.module.assesment.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCMA button').removeAttr('disabled');
                        confirmModalCMA.hide();

                        succModal.show();
                        document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                            $('#successModal .successModalTitle').html('Success!');
                            $('#successModal .successModalDesc').html('Course Module Assesment Data Successfully Restored!');
                        });
                    }
                    moduleAssesmentListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        })


        $("#moduleAssesmentDataTable").on("click", ".edit_btn", function () {      
            let $editBtn = $(this);
            let editId = $editBtn.attr("data-id");

            axios({
                method: "get",
                url: route("course.module.assesment.edit", editId),
                headers: {"X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")},
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    if(dataset.grades.length>0)
                        $.each(dataset.grades,function(index,value){
                            for(let i=0;i<(dataset.grades.length+20);i++)
                                if(index==i)
                                    $('#moduleAssesmentEditModal input[name="grade[]"]').eq(index).prop('checked',true)
                                else
                                    $('#moduleAssesmentEditModal input[name="grade[]"]').eq(i).prop('checked',false)
                        });
                    else
                        for(let i=0;i<(dataset.grades.length+33);i++)
                            $('#moduleAssesmentEditModal input[name="grade[]"]').eq(i).prop('checked',false)
                    if(dataset.is_result_segment==1)
                        $('#moduleAssesmentEditModal input[name="is_result_segment"]').prop('checked',true)
                    else
                        $('#moduleAssesmentEditModal input[name="is_result_segment"]').prop('checked', false);
                        tomSelectArray[1].setValue(dataset.assessment_type_id);
                        $('#moduleAssesmentEditModal input[name="id"]').val(editId);
                        
                        $('#moduleAssesmentEditModal input[name="view_in_plan"]').val(dataset.view_in_plan);
                }
            }).catch((error) => {
                console.log(error);
            });
        });


        $('#moduleAssesmentEditForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('moduleAssesmentEditForm');

            $('#moduleAssesmentEditForm').find('input').removeClass('border-danger')
            $('#moduleAssesmentEditForm').find('.acc__input-error').html('')

            document.querySelector('#updateModuleAssesment').setAttribute('disabled', 'disabled');
            document.querySelector('#updateModuleAssesment svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('course.module.assesment.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#updateModuleAssesment').removeAttribute('disabled');
                document.querySelector('#updateModuleAssesment svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    moduleAssesmentEditModal.hide();
                    moduleAssesmentListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Module Assesment data successfully updated.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#updateModuleAssesment').removeAttribute('disabled');
                document.querySelector('#updateModuleAssesment svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#moduleAssesmentEditForm .${key}`).addClass('border-danger')
                            $(`#moduleAssesmentEditForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });


        $('#moduleAssesmentAddForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('moduleAssesmentAddForm');

            $('#moduleAssesmentAddForm').find('input').removeClass('border-danger')
            $('#moduleAssesmentAddForm').find('.acc__input-error').html('')

            document.querySelector('#saveModuleAssesment').setAttribute('disabled', 'disabled');
            document.querySelector('#saveModuleAssesment svg').style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);

            axios({
                method: "post",
                url: route('course.module.assesment.store'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('#saveModuleAssesment').removeAttribute('disabled');
                document.querySelector('#saveModuleAssesment svg').style.cssText = 'display: none;';
                
                if (response.status == 200) {
                    moduleAssesmentAddModal.hide();
                    moduleAssesmentListTable.init();
                    
                    succModal.show();
                    document.getElementById('successModal').addEventListener('shown.tw.modal', function(event){
                        $('#successModal .successModalTitle').html('Congratulations!');
                        $('#successModal .successModalDesc').html('Course Module Assesment data successfully inserted.');
                    });
                }
                
            }).catch(error => {
                document.querySelector('#saveModuleAssesment').removeAttribute('disabled');
                document.querySelector('#saveModuleAssesment svg').style.cssText = 'display: none;';
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#moduleAssesmentAddForm .${key}`).addClass('border-danger')
                            $(`#moduleAssesmentAddForm  .error-${key}`).html(val)
                        }
                    } else {
                        console.log('error');
                    }
                }
            });

        });
    }
})()