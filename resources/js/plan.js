import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";

("use strict");
var classPlanListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let courses = $("#courses-CPL").val() != "" ? $("#courses-CPL").val() : "";
        let instance_term = $("#instance_term-CPL").val() != "" ? $("#instance_term-CPL").val() : "";
        let room = $("#room-CPL").val() != "" ? $("#room-CPL").val() : "";
        let group = $("#group-CPL").val() != "" ? $("#group-CPL").val() : "";
        let tutor = $("#tutor-CPL").val() != "" ? $("#tutor-CPL").val() : "";
        let ptutor = $("#ptutor-CPL").val() != "" ? $("#ptutor-CPL").val() : "";
        let days = $("#days-CPL").val() != "" ? $("#days-CPL").val() : "";
        let status = $("#status-CPL").val() != "" ? $("#status-CPL").val() : "";
        let dates = $("#date-CPL").val() != "" ? $("#date-CPL").val() : "";

        let tableContent = new Tabulator("#classPlansListTable", {
            ajaxURL: route("class.plan.list"),
            ajaxParams: { courses: courses, term_declarations: instance_term, room: room, group: group, tutor: tutor, ptutor: ptutor, days: days, status: status, dates : dates},
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
            selectable:true,
            columns: [
                {
                    formatter: "rowSelection", 
                    titleFormatter: "rowSelection", 
                    hozAlign: "left", 
                    headerHozAlign: "left",
                    width: "60",
                    headerSort: false, 
                    download: false,
                    cellClick:function(e, cell){
                        cell.getRow().toggleSelect();
                    }
                },
                {
                    title: "#ID",
                    field: "id",
                    width: "100",
                },
                {
                    title: "Course",
                    field: "course",
                    headerHozAlign: "left",
                },
                {
                    title: "Module",
                    field: "module",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) { 
                        var html = '<div class="break-all whitespace-normal">';
                            html += '<a class="font-medium text-primary whitespace-normal break-all" href="'+route('tutor-dashboard.plan.module.show', cell.getData().id)+'">';
                                html += cell.getData().module;
                                html += (cell.getData().class_type != '' ? '<br/>'+cell.getData().class_type : '');
                            html += '</a>';
                        html += '</div>';
                        return html;
                    }
                },
                {
                    title: "Group",
                    field: "group",
                    headerHozAlign: "left",
                },
                {
                    title: "Room",
                    field: "room",
                    headerHozAlign: "left",
                },
                {
                    title: "Day - Time",
                    field: "day",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        var html = '<div>';
                                html += '<span>'+cell.getData().day+'</span><br/>';
                                html += '<span>'+cell.getData().time+'</span>';
                            html += '</div>';

                        return html;
                    }
                },
                {
                    title: "Submission",
                    field: "submission_date",
                    headerHozAlign: "left",
                },
                {
                    title: "Class Type",
                    field: "class_type",
                    headerHozAlign: "left",
                },
                {
                    title: "Tutor / P. Tutor",
                    field: "tutor",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {
                        if(cell.getData().class_type == 'Tutorial' || cell.getData().class_type == 'Seminar'){
                            return cell.getData().personalTutor;
                        }else{
                            return cell.getData().tutor;
                        }
                    }
                },
                {
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "left",
                    download: false,
                    width: "140",
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
                            if(cell.getData().dates == 1){
                                btns += '<a href="'+route('plan.dates', cell.getData().id)+'" class="view_days btn-round btn btn-success text-xs text-white px-2 py-1 ml-1"><i data-lucide="eye-off" class="w-4 h-4 mr-1"></i> View Days</a>';
                            }
                            btns += '<button data-id="'+cell.getData().id +'" data-tw-toggle="modal" data-tw-target="#editPlanModal" type="button" class="edit_btn btn-round btn btn-primary text-xs text-white px-2 py-1 ml-1"><i data-lucide="Pencil" class="w-4 h-4 mr-1"></i> Edit Plan</a>';
                            btns +='<button data-id="'+cell.getData().id +'"  class="delete_btn btn btn-danger text-xs text-white btn-round px-2 py-1 ml-1"><i data-lucide="Trash2" class="w-4 h-4 mr-1"></i> Delete</button>';
                        }  else if (cell.getData().deleted_at != null) {
                            btns += '<button data-id="'+cell.getData().id +'"  class="restore_btn btn btn-linkedin text-white btn-rounded ml-1 p-0 w-9 h-9"><i data-lucide="rotate-cw" class="w-4 h-4"></i></button>';
                        }

                        btns += '<input type="hidden" class="classPlanId" name="classPlanIds[]" value="'+cell.getData().id+'"/>';
                        
                        return '<div style="white-space: normal; text-align: left;">'+btns+'</div>';
                    },
                },
            ],
            rowSelectionChanged:function(data, rows){
                var ids = [];
                if(rows.length > 0){
                    $('#generateDaysBtn').fadeIn();
                }else{
                    $('#generateDaysBtn').fadeOut();
                }
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
            selectableCheck:function(row){
                return row.getData().id > 0; //allow selection of rows where the age is greater than 18
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
        init: function () {
            _tableGen();
        },
    };
})();

(function(){
    if($('#classPlansListTable').length > 0){
        const warningModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModalCP"));
        const successModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalCP"));
        const editPlanModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPlanModal"));
        const confirmModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCP"));
            
        let confModalDelTitle = 'Are you sure?';

        const editPlanModalEl = document.getElementById('editPlanModal')
        editPlanModalEl.addEventListener('hide.tw.modal', function(event) {
            $('#editPlanModal .acc__input-error').html('');
            $('#editPlanModal .modal-body select').val('');
            $('#editPlanModal .modal-body textarea').val('');
            $('#editPlanModal .modal-body input:not([type="radio"])').val('');
            $('#editPlanModal input[name="id"]').val('0');
            $('#editPlanModal input[type="radio"]').prop('checked', false);

            //$('#editPlanModal .tutorWrap').fadeOut('fast');
            //$('#editPlanModal .PersonalTutorWrap').fadeOut('fast');

            tutorId.clear(true);
            personalTutorId.clear(true);

        });

        $('.theTimeField').each(function(){
            var timeMaskModal = IMask(this, {
                    overwrite: true,
                    autofix: true,
                    mask: 'HH:MM',
                    blocks: {
                        HH: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'HH',
                            from: 0,
                            to: 23,
                            maxLength: 2
                        },
                        MM: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'MM',
                            from: 0,
                            to: 59,
                            maxLength: 2
                        }
                    }
            });
        });

        let tomOptions = {
            plugins: {
                dropdown_input: {}
            },
            placeholder: 'Please Select',
            //persist: false,
            create: true,
            onDelete: function (values) {
                return confirm(
                    values.length > 1
                        ? "Are you sure you want to remove these " +
                                values.length +
                                " items?"
                        : 'Are you sure you want to remove "' +
                                values[0] +
                                '"?'
                );
            },
        };
        let topOptionsMultiple = {
            ...tomOptions,
            plugins: {
                ...tomOptions.plugins,
                remove_button: {
                    title: "Remove this item",
                },
            }
        };
        
        var coursesCPL = new TomSelect('#courses-CPL', tomOptions);
        var instanceTermCPL = new TomSelect('#instance_term-CPL', tomOptions);
        var groupCPL = new TomSelect('#group-CPL', tomOptions);
            groupCPL.disable();
        var tutorCPL = new TomSelect('#tutor-CPL', topOptionsMultiple);
        var ptutorCPL = new TomSelect('#ptutor-CPL', topOptionsMultiple);
        var roomCPL = new TomSelect('#room-CPL', topOptionsMultiple);
        var daysCPL = new TomSelect('#days-CPL', topOptionsMultiple);
        let tutorId = new TomSelect(document.getElementById('tutor_id'), tomOptions);
        let personalTutorId = new TomSelect(document.getElementById('personal_tutor_id'), tomOptions);

        // Init Table
        classPlanListTable.init();

        // Filter function
        function filterHTMLForm() {
            classPlanListTable.init();
        }

        // On submit filter form
        $("#tabulatorFilterForm-CPL")[0].addEventListener(
            "keypress",
            function (event) {
                let keycode = event.keyCode ? event.keyCode : event.which;
                if (keycode == "13") {
                    event.preventDefault();
                    filterHTMLForm();
                }
            }
        );

        // On Change Course & Term Filter field. 
        $('#courses-CPL, #instance_term-CPL').on('change', function(e){
            var course = $('#courses-CPL').val();
            var term = $('#instance_term-CPL').val();

            if(course > 0 && term > 0){
                $('.termCplLoader, .courseCplLoader').removeClass('hidden');
                axios({
                    method: "post",
                    url: route('class.plan.get.group.filter'),
                    data: {course : course, term : term},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    $('.termCplLoader, .courseCplLoader').addClass('hidden');
                    groupCPL.enable();
                    groupCPL.clearOptions();
                    if (response.status == 200) {
                        $.each(response.data.res, function(index, row) {
                            groupCPL.addOption({
                                value: row.id,
                                text: row.name,
                            });
                        });
                        groupCPL.refreshOptions()
                    }
                }).catch(error => {
                    $('.termCplLoader, .courseCplLoader').addClass('hidden');
                    groupCPL.enable();
                    console.log('error');
                });
            }else{
                $('.termCplLoader, .courseCplLoader').addClass('hidden');
                groupCPL.clear();
                groupCPL.clearOptions();
                groupCPL.disable();
            }
        });

        // On click go button
        $("#tabulator-html-filter-go-CPL").on("click", function (event) {
            var views = ($("#view-CPL").val() > 0 ? $("#view-CPL").val() : 1);

            if(views == 3){
                window.location.href = route('plans.tree');
            }else if(views == 2){
                $('#tabulator-print-CPL, #tabulator-export-CPL, #generateDaysBtn').fadeOut();
                let courses = $("#courses-CPL").val() != "" ? $("#courses-CPL").val() : "";
                let instance_term = $("#instance_term-CPL").val() != "" ? $("#instance_term-CPL").val() : "";
                let room = $("#room-CPL").val() != "" ? $("#room-CPL").val() : "";
                let group = $("#group-CPL").val() != "" ? $("#group-CPL").val() : "";
                let tutor = $("#tutor-CPL").val() != "" ? $("#tutor-CPL").val() : "";
                let ptutor = $("#ptutor-CPL").val() != "" ? $("#ptutor-CPL").val() : "";
                let days = $("#days-CPL").val() != "" ? $("#days-CPL").val() : "";
                let status = $("#status-CPL").val() != "" ? $("#status-CPL").val() : "";
                let dates = $("#date-CPL").val() != "" ? $("#date-CPL").val() : "";

                axios({
                    method: "post",
                    url: route('class.plan.grid'),
                    data: {courses : courses, term_declaration : instance_term, room : room, group : group, tutor : tutor, ptutor : ptutor, days : days, status : status, dates : dates},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#classPlansListTable').removeAttr('tabulator-layout').removeAttr('role').removeClass('tabulator').html(response.data.htm);
                        createIcons({
                            icons,
                            "stroke-width": 1.5,
                            nameAttr: "data-lucide",
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#tabulator-print-CPL, #tabulator-export-CPL').fadeIn();
                $('#generateDaysBtn').fadeOut();
                filterHTMLForm();
            }
        });

        // On reset filter form
        $("#tabulator-html-filter-reset-CPL").on("click", function (event) {
            coursesCPL.clear(true);
            instanceTermCPL.clear(true);
            tutorCPL.clear(true);
            ptutorCPL.clear(true);
            roomCPL.clear(true);
            groupCPL.clear(true);
            daysCPL.clear(true);
            $("#status-CPL").val('1');
            $("#date-CPL").val('');

            $('#tabulator-print-CPL, #tabulator-export-CPL').fadeIn();
            $('#generateDaysBtn').fadeOut();
            filterHTMLForm();
        });

        // Generate Days Code
        $('#generateDaysBtn').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);
            var ids = [];
            $('#classPlansListTable').find('.tabulator-row.tabulator-selected').each(function(){
                var $row = $(this);
                ids.push($row.find('.classPlanId').val());
            });
            if(ids.length > 0){
                $btn.attr('disabled', 'disabled');
                $('svg', $btn).fadeIn('fast');
                axios({
                    method: "post",
                    url: route('plan.dates.generate'),
                    data: {classPlansIds : ids},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $btn.removeAttr('disabled', 'disabled');
                        $('svg', $btn).fadeOut('fast');
                        successModalCP.show();
                        document.getElementById("successModalCP").addEventListener("shown.tw.modal", function (event) {
                            $("#successModalCP .successModalTitleCP").html(response.data.title);
                            $("#successModalCP .successModalDescCP").html(response.data.Message);
                        });

                        setTimeout(function(){
                            successModalCP.hide();
                        }, 3000);

                        filterHTMLForm();
                    }
                }).catch(error => {
                    $btn.removeAttr('disabled', 'disabled');
                    $('svg', $btn).fadeOut('fast');
                    if (error.response.status == 422 || error.response.status == 304) {
                        warningModalCP.show();
                        document.getElementById("warningModalCP").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModalCP .warningModalTitleCP").html(error.response.data.title);
                            $("#warningModalCP .warningModalDescCP").html(error.response.data.Message);
                        });

                        setTimeout(function(){
                            warningModalCP.hide();
                        }, 3000);

                        filterHTMLForm();
                    } else {
                        console.log('error');
                    }
                });
            }else{
                warningModalCP.show();
                document.getElementById("warningModalCP").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModalCP .warningModalTitleCP").html("Error Found!");
                    $("#warningModalCP .warningModalDescCP").html('Selected plans id not foudn. Please select some plan first or contact with the site administrator.');
                });
            }
        });

        $('#classPlansListTable').on('click', '.edit_btn', function(e){
            var $btn = $(this);
            var planid = $btn.attr('data-id');

            axios({
                method: "get",
                url: route("class.plan.edit", planid),
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
                },
            }).then((response) => {
                if (response.status == 200) {
                    let dataset = response.data;
                    
                    $('#editPlanModal .termName').html(dataset.plan.term ? dataset.plan.term : '');
                    $('#editPlanModal .courseName').html(dataset.plan.course ? dataset.plan.course : '');
                    $('#editPlanModal .groupName').html(dataset.plan.group ? dataset.plan.group : '');

                    $('#editPlanModal select[name="module_creation_id"]').html(dataset.plan.modules ? dataset.plan.modules : '');
                    $('#editPlanModal select[name="rooms_id"]').val(dataset.plan.rooms_id ? dataset.plan.rooms_id : '');

                    var classType = dataset.plan.class_type ? dataset.plan.class_type : '';
                    $('#editPlanModal select[name="class_type"]').val(classType);
                    if(classType == 'Tutorial' || classType == 'Seminar'){
                        $('#editPlanModal .tutorWrap').fadeOut('fast', function(){
                            //$('#editPlanModal [name="tutor_id"]').val('');
                            tutorId.clear(true)
                        });
                        // $('#editPlanModal .PersonalTutorWrap').fadeIn('fast', function(){
                        //     personalTutorId.addItem(dataset.plan.personal_tutor_id);
                        // });
                    }else{
                        $('#editPlanModal .tutorWrap').fadeIn('fast', function(){
                            //$('#editPlanModal [name="tutor_id"]').val(dataset.plan.tutor_id ? dataset.plan.tutor_id : '');
                            tutorId.addItem(dataset.plan.tutor_id);
                        });
                            
                        // $('#editPlanModal .PersonalTutorWrap').fadeOut('fast', function(){
                        //     personalTutorId.clear(true);
                        // });
                    }
                    //$('#editPlanModal select[name="tutor_id"]').val(dataset.plan.tutor_id ? dataset.plan.tutor_id : '');
                    //$('#editPlanModal select[name="personal_tutor_id"]').val(dataset.plan.personal_tutor_id ? dataset.plan.personal_tutor_id : '');
                    if(dataset.plan.personal_tutor_id > 0){
                        personalTutorId.addItem(dataset.plan.personal_tutor_id)
                    }else{
                        personalTutorId.clear(true);
                    }
                    //$('#editPlanModal input[name="module_enrollment_key"]').val(dataset.plan.module_enrollment_key ? dataset.plan.module_enrollment_key : '');
                    $('#editPlanModal input[name="start_time"]').val(dataset.plan.start_time ? dataset.plan.start_time : '');
                    $('#editPlanModal input[name="end_time"]').val(dataset.plan.end_time ? dataset.plan.end_time : '');
                    $('#editPlanModal input[name="submission_date"]').val(dataset.plan.submission_date ? dataset.plan.submission_date : '');
                    $('#editPlanModal textarea[name="virtual_room"]').val(dataset.plan.virtual_room ? dataset.plan.virtual_room : '');
                    $('#editPlanModal textarea[name="note"]').val(dataset.plan.note ? dataset.plan.note : '');

                    if(dataset.plan.sat == 1){
                        $('#editPlanModal #day_sat').prop('checked', true);
                    }else if(dataset.plan.sun == 1){
                        $('#editPlanModal #day_sun').prop('checked', true);
                    }else if(dataset.plan.mon == 1){
                        $('#editPlanModal #day_mon').prop('checked', true);
                    }else if(dataset.plan.tue == 1){
                        $('#editPlanModal #day_tue').prop('checked', true);
                    }else if(dataset.plan.wed == 1){
                        $('#editPlanModal #day_wed').prop('checked', true);
                    }else if(dataset.plan.thu == 1){
                        $('#editPlanModal #day_thu').prop('checked', true);
                    }else if(dataset.plan.fri == 1){
                        $('#editPlanModal #day_fri').prop('checked', true);
                    }

                    $('#editPlanModal input[name="id"]').val(planid);
                }
            }).catch((error) => {
                console.log(error);
            });
        });


        $('#editPlanModal [name="class_type"]').on('change', function(e){
            var $classType = $(this);
            var classType = $classType.val();
    
            if(classType == 'Tutorial' || classType == 'Seminar'){
                $('#editPlanModal .tutorWrap').fadeOut('fast', function(){
                    //$('#editPlanModal [name="tutor_id"]').val('');
                    tutorId.clear(true);
                });
            }else{
                $('#editPlanModal .tutorWrap').fadeIn('fast', function(){
                    //$('#editPlanModal [name="tutor_id"]').val('');
                    tutorId.clear(true)
                });
            }
        })


        $('#editPlanForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('editPlanForm');
        
            document.querySelector('#updatePlans').setAttribute('disabled', 'disabled');
            document.querySelector("#updatePlans svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('class.plan.update'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    console.log(response.data)
                    document.querySelector('#updatePlans').removeAttribute('disabled');
                    document.querySelector("#updatePlans svg").style.cssText = "display: none;";

                    editPlanModal.hide();

                    successModalCP.show();
                    document.getElementById("successModalCP").addEventListener("shown.tw.modal", function (event) {
                        $("#successModalCP .successModalTitleCP").html("Congratulation!" );
                        $("#successModalCP .successModalDescCP").html('Class Plan date successfully updated.');
                    });                
                        
                }
                classPlanListTable.init();
            }).catch(error => {
                document.querySelector('#updatePlans').removeAttribute('disabled');
                document.querySelector("#updatePlans svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#editPlanForm .${key}`).addClass('border-danger');
                            $(`#editPlanForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        // Delete Course
        $('#classPlansListTable').on('click', '.delete_btn', function(e){
            e.preventDefault();
            let $deleteBTN = $(this);
            let rowID = $deleteBTN.attr('data-id');

            confirmModalCP.show();
            document.getElementById('confirmModalCP').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCP .confModTitleCP').html(confModalDelTitle);
                $('#confirmModalCP .confModDescCP').html('Do you really want to delete these record? Click on agree to continue.');
                $('#confirmModalCP .agreeWithCP').attr('data-id', rowID);
                $('#confirmModalCP .agreeWithCP').attr('data-action', 'DELETE');
            });
        });

        // Restore Course
        $('#classPlansListTable').on('click', '.restore_btn', function(e){
            e.preventDefault();
            let $statusBTN = $(this);
            let courseID = $statusBTN.attr('data-id');

            confirmModalCP.show();
            document.getElementById('confirmModalCP').addEventListener('shown.tw.modal', function(event){
                $('#confirmModalCP .confModTitleCP').html(confModalDelTitle);
                $('#confirmModalCP .confModDescCP').html('Do you really want to restore these record? Click on agree to continue.');
                $('#confirmModalCP .agreeWithCP').attr('data-id', courseID);
                $('#confirmModalCP .agreeWithCP').attr('data-action', 'RESTORE');
            });
        });

        // Confirm Modal Action
        $('#confirmModalCP .agreeWithCP').on('click', function(e){
            e.preventDefault();
            let $agreeBTN = $(this);
            let recordID = $agreeBTN.attr('data-id');
            let action = $agreeBTN.attr('data-action');

            $('#confirmModalDP button').attr('disabled', 'disabled');
            if(action == 'DELETE'){
                axios({
                    method: 'delete',
                    url: route('class.plan.delete', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCP button').removeAttr('disabled');
                        confirmModalCP.hide();

                        successModalCP.show();
                        document.getElementById("successModalCP").addEventListener("shown.tw.modal", function (event) {
                            $("#successModalCP .successModalTitleCP").html("Congratulation!" );
                            $("#successModalCP .successModalDescCP").html('Class Plan successfully deleted form the list.');
                        }); 
                    }
                    classPlanListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            } else if(action == 'RESTORE'){
                axios({
                    method: 'post',
                    url: route('class.plan.restore', recordID),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#confirmModalCP button').removeAttr('disabled');
                        confirmModalCP.hide();

                        successModalCP.show();
                        document.getElementById("successModalCP").addEventListener("shown.tw.modal", function (event) {
                            $("#successModalCP .successModalTitleCP").html("Congratulation!" );
                            $("#successModalCP .successModalDescCP").html('Class Plan successfully restored to the list.');
                        }); 
                    }
                    classPlanListTable.init();
                }).catch(error =>{
                    console.log(error)
                });
            }
        });

        $('#exportPlansXLSX').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            const form = document.getElementById('tabulatorFilterForm-CPL');

            $theBtn.attr('disabled', 'disabled');
            $theBtn.find('svg.theLoader').fadeIn();

            let form_data = new FormData(form);
            axios({
                method: "POST",
                url: route('class.plan.export'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                responseType: 'blob',
            }).then(response => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                if (response.status == 200) {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    link.href = url;
                    link.setAttribute('download', 'Plan_List.xlsx'); 
                    document.body.appendChild(link);
                    link.click();
                }
            }).catch(error => {
                $theBtn.removeAttr('disabled');
                $theBtn.find('svg.theLoader').fadeOut();
                console.log('error');
            });
        })
    }

    if($('#classPlanAddForm').length > 0){
        $('#findModuleList').on('click', function(e){
            e.preventDefault();
            var $theBtn = $(this);
            $('svg', $theBtn).fadeIn('fast');
            $theBtn.attr('disabled', 'disabled');
    
            if($('#course').val() == '' || $('#instanceTermId').val() == ''){
                if($('#course').val() == ''){
                    $('.error-course').fadeIn('fast').html('This field is required.')
                }
                if($('#instanceTermId').val() == ''){
                    $('.error-instanceTermId').fadeIn('fast').html('This field is required.')
                }
                $('svg', $theBtn).fadeOut('fast');
                $theBtn.removeAttr('disabled');
            }else{
                $('#classPlanAddForm .acc__input-error').fadeOut('fast').html('');
                var courseID = $('#course').val();
                var instanceTermId = $('#instanceTermId').val();
    
                axios({
                    method: "post",
                    url: route('class.plan.get.modules.by.course.terms'),
                    data: {courseID : courseID, instanceTermId : instanceTermId},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('svg', $theBtn).fadeOut('fast').removeAttr('disabled');
                        if(response.data.thtml != ''){
                            $(".instanceTermDetails").fadeIn('fast').html(response.data.thtml);
                        }else{
                            $(".instanceTermDetails").fadeOut('fast').html('');
                        }
                        if(response.data.mhtml != ''){
                            $(".availableModules").fadeIn('fast').html(response.data.mhtml);
                            $('.theSubmitArea').fadeIn('fast');
                        }else{
                            $(".availableModules").fadeOut('fast').html('');
                            $('.theSubmitArea').fadeOut('fast');
                        }
                    }
                }).catch(error => {
                    $('svg', $theBtn).fadeOut('fast').removeAttr('disabled');
                    console.log('error');
                });
            }
        });
    
        $('#classPlanAddForm').on('change', 'input[name="module_creation_id"]', function(){
            var moduleLenth = $('#classPlanAddForm').find('input[name="module_creation_id"]:checked').length;
            if(moduleLenth > 0){
                $('#classPlanAddForm .moduleSelectionError').fadeOut('fast').html('');
            }
        })
    
        $('#classPlanAddForm').on('submit', function(e){
            e.preventDefault();
            var $form = $(this);
            const form = document.getElementById('classPlanAddForm');
    
            document.querySelector('#submitModulesBtn').setAttribute('disabled', 'disabled');
            document.querySelector('#submitModulesBtn svg').style.cssText = 'display: inline-block;';
    
            var moduleLenth = $form.find('input[name="module_creation_id"]:checked').length;
            if(moduleLenth > 0){
                var courseID = $('#course', $form).val();
                var instanceTermId = $('#instanceTermId', $form).val();
                var moduleCreation = $('input[name="module_creation_id"]:checked', $form).val();
    
                var url = route('class.plan.builder', {'course' : courseID, 'instanceterm' : instanceTermId, 'modulecreation' : moduleCreation});
                window.location.href = url;
            }else{
                $('.moduleSelectionError', $form).fadeIn('fast').html('You have to select a module before continue.');
                document.querySelector('#submitModulesBtn').removeAttribute('disabled');
                document.querySelector('#submitModulesBtn svg').style.cssText = 'display: none;';
            }
        });
    }
    

    if($('#classPlanBuilderForm').length > 0){
        const warningModalCPB = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModalCPB"));
        const successModalCPB = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalCPB"));
        const confirmModalCPB = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCPB"));
        const successModalCPB2 = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalCPB2"));

        const confirmModalCPBEl = document.getElementById('confirmModalCPB')
        confirmModalCPBEl.addEventListener('hide.tw.modal', function(event) {
            $('#confirmModalCPB .agreeWithCPB').attr('data-id', 0).attr('data-action', 0);
        });

        $('#classPlanBuilderForm').on('submit', function(e){
            e.preventDefault();
            const form = document.getElementById('classPlanBuilderForm');
            var term_declaration_id = $('#classPlanBuilderForm #term_declaration_id').val();
            var academic_year_id = $('#classPlanBuilderForm #academic_year_id').val();
            var course_creation_id = $('#classPlanBuilderForm #course_creation_id').val();
            var instance_term_id = $('#classPlanBuilderForm #instance_term_id').val();
            var course_id = $('#classPlanBuilderForm #course_id').val();
            var group_id = $('#classPlanBuilderForm #group_id').val();

        
            document.querySelector('.addPlanBox').setAttribute('disabled', 'disabled');
            document.querySelector('#saveUpdatePlans').setAttribute('disabled', 'disabled');
            document.querySelector("#saveUpdatePlans svg").style.cssText ="display: inline-block;";

            let form_data = new FormData(form);
            var routineData = {};
            var routineDayBoxCount = $('#classPlanBuilderForm .routineDayBox').length;

            if(routineDayBoxCount == 0){
                warningModalCPB.show();
                document.getElementById("warningModalCPB").addEventListener("shown.tw.modal", function (event) {
                    $("#warningModalCPB .warningModalTitleCPB").html("Error Found!");
                    $("#warningModalCPB .warningModalDescCPB").html('Plans not found. Please add at least one plan to continue.');
                });

                setTimeout(function(){
                    warningModalCPB.hide();
                }, 3000);

                document.querySelector('.addPlanBox').removeAttribute('disabled');
                document.querySelector('#saveUpdatePlans').removeAttribute('disabled');
                document.querySelector("#saveUpdatePlans svg").style.cssText = "display: none;";

                return false;
            }
            $('#classPlanBuilderForm tr.routineRow').each(function(){
                var $tr = $(this);
                var day = $tr.attr('data-day');

                routineData[day] = {};
                $('td.routineDay', $tr).each(function(){
                    var $td = $(this);
                    var venuRoom = $td.attr('data-venuRoom');

                    routineData[day][venuRoom] = {};
                    var sc = 1;
                    $('.routineDayBox', $td).each(function(){
                        var $routineDayBox = $(this);

                        routineData[day][venuRoom][sc] = {};
                        $('.rdbItem', $routineDayBox).each(function(){
                            var $rdbItem = $(this);
                            var dataValue = (($rdbItem.attr('data-id') != '' && $rdbItem.attr('data-id') != 'undefined' && $rdbItem.attr('data-id') != 0) ? $rdbItem.attr('data-id') : '');
                            var dataLabel = $rdbItem.attr('data-label').toLowerCase().replace(' ', '_');
                            routineData[day][venuRoom][sc][dataLabel] = dataValue;
                        })
                        if($('.existing_id', $routineDayBox).length > 0){
                            routineData[day][venuRoom][sc]['existing_id'] = $('.existing_id', $routineDayBox).val();
                        }
                        sc += 1;
                    });
                });
            });

            axios({
                method: "post",
                url: route('class.plan.store'),
                data: {
                    routineData : routineData, 

                    term_declaration_id : term_declaration_id, 
                    academic_year_id : academic_year_id, 
                    course_creation_id : course_creation_id,
                    instance_term_id : instance_term_id,
                    course_id : course_id,
                    group_id : group_id
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                document.querySelector('.addPlanBox').removeAttribute('disabled');
                document.querySelector('#saveUpdatePlans').removeAttribute('disabled');
                document.querySelector("#saveUpdatePlans svg").style.cssText = "display: none;";
                //colsole.log(response.data);
                if(response.status == 200){
                    successModalCPB.show();
                    document.getElementById("successModalCPB").addEventListener("shown.tw.modal", function (event) {
                        $("#successModalCPB .successModalTitleCPB").html("Congratulations!");
                        $("#successModalCPB .successModalDescCPB").html(response.data.msg+' Get back to the list.');
                        $("#successModalCPB a").attr('href', response.data.red);
                    });

                    setTimeout(function(){
                        successModalCPB.hide();
                        window.location.href = response.data.red;
                    }, 5000);
                }
            }).catch(error => {
                document.querySelector('.addPlanBox').removeAttribute('disabled');
                document.querySelector('#saveUpdatePlans').removeAttribute('disabled');
                document.querySelector("#saveUpdatePlans svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 304) {
                        warningModalCPB.show();
                        document.getElementById("warningModalCPB").addEventListener("shown.tw.modal", function (event) {
                            $("#warningModalCPB .warningModalTitleCPB").html("Error Found!");
                            $("#warningModalCPB .warningModalDescCPB").html('No class plans inserted or updated. Something went wrong. Please try later.');
                        });

                        setTimeout(function(){
                            warningModalCPB.hide();
                        }, 3000);
                    } else {
                        console.log('error');
                    }
                }
            });
        });

        $('#classPlanBuilderForm').on('click', '.addPlanBox', function(){
            var $btn = $(this);
            var $form = $('#classPlanBuilderForm');
            var $td = $(this).parent('.routineDay');
            var $box = $('.routineDayBoxes', $td);

            var term_declaration_id = $form.find('input[name="term_declaration_id"]').val();
            var academic_year_id = $form.find('input[name="academic_year_id"]').val();
            var course_creation_id = $form.find('input[name="course_creation_id"]').val();
            var instance_term_id = $form.find('input[name="instance_term_id"]').val();
            var course_id = $form.find('input[name="course_id"]').val();
            var group_id = $form.find('input[name="group_id"]').val();

            var day = $btn.attr('data-day');
            var venue = $btn.attr('data-venue');
            var room = $btn.attr('data-room');

            $btn.attr('disabled', 'disabled')
            axios({
                method: "post",
                url: route('class.plan.get.box'),
                data: {
                    term_declaration_id : term_declaration_id, 
                    academic_year_id : academic_year_id, 
                    course_creation_id : course_creation_id, 
                    instance_term_id : instance_term_id, 
                    course_id : course_id, 
                    group_id : group_id, 
                    day : day, 
                    venue : venue, 
                    room : room
                },
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $btn.removeAttr('disabled');
                    $box.append(response.data.htmls);

                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                    
                    $box.find('.dateMask').each(function(){
                        var dateMask = IMask(
                            this,
                            {
                                mask: Date,
                                min: new Date(1990, 0, 1),
                                max: new Date(2050, 0, 1),
                                pattern: 'd-`m-`Y',
                                format: function (date) {
                                    var day = date.getDate();
                                    var month = date.getMonth() + 1;
                                    var year = date.getFullYear();
                                
                                    if (day < 10) day = "0" + day;
                                    if (month < 10) month = "0" + month;
                                
                                    return [day, month, year].join('-');
                                },
                                // define str -> date convertion
                                parse: function (str) {
                                    var yearMonthDay = str.split('-');
                                    return new Date(yearMonthDay[2], yearMonthDay[1] - 1, yearMonthDay[0]);
                                },
                                lazy: false
                            });
                    });
            
                    $box.find('.timeMask').each(function(){
                        var timeMask = IMask(
                            this,
                            {
                                overwrite: true,
                                autofix: true,
                                mask: 'HH:MM - HH2:MM2',
                                blocks: {
                                    HH: {
                                        mask: IMask.MaskedRange,
                                        placeholderChar: 'HH',
                                        from: 0,
                                        to: 23,
                                        maxLength: 2
                                    },
                                    MM: {
                                        mask: IMask.MaskedRange,
                                        placeholderChar: 'MM',
                                        from: 0,
                                        to: 59,
                                        maxLength: 2
                                    },
                                    HH2: {
                                        mask: IMask.MaskedRange,
                                        placeholderChar: 'HH',
                                        from: 0,
                                        to: 23,
                                        maxLength: 2
                                    },
                                    MM2: {
                                        mask: IMask.MaskedRange,
                                        placeholderChar: 'MM',
                                        from: 0,
                                        to: 59,
                                        maxLength: 2
                                    }
                                }
                            });
                    });
                }
            }).catch(error => {
                $btn.removeAttr('disabled');
                if (error.response) {
                    console.log('error');
                }
            });

        });

        $('#classPlanBuilderForm').on('click', '.removePlanBTN', function(e){
            e.preventDefault();
            var $btn = $(this);
            var $routineDayBox = $btn.parent('.routineDayBox');
            var planID = ($routineDayBox.find('.existing_id').val() > 0 ? $routineDayBox.find('.existing_id').val() : 0);
            $routineDayBox.addClass('removeNow');

            confirmModalCPB.show();
            document.getElementById("confirmModalCPB").addEventListener("shown.tw.modal", function (event) {
                $("#confirmModalCPB .confModTitleCPB").html("Are you sure?");
                $("#confirmModalCPB .confModDescCPB").html('Want to remove this set of Class Plan? If yes then click on the Agree button.');
                if(planID > 0){
                    $('#confirmModalCPB .agreeWithCPB').attr('data-id', planID).attr('data-action', 'DELETE');
                }else{
                    $('#confirmModalCPB .agreeWithCPB').attr('data-id', 0).attr('data-action', 'NONE');
                }
            });
        });

        $('#confirmModalCPB .agreeWithCPB').on('click', function(e){
            e.preventDefault();
            var $btn = $(this);

            if($btn.attr('data-id') > 0 && $btn.attr('data-action') == 'DELETE'){
                var planid = $btn.attr('data-id');
                axios({
                    method: "delete",
                    url: route('class.plan.delete', planid),
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        $('#classPlanBuilderForm').find('.routineDayBox.removeNow').remove();
                        confirmModalCPB.hide();

                        successModalCPB2.show(); 
                        document.getElementById("successModalCPB2").addEventListener("shown.tw.modal", function (event) {
                            $("#successModalCPB2 .successModalTitleCPB2").html("Congratulations!");
                            $("#successModalCPB2 .successModalDescCPB2").html('Class Plan successfully moved to the trash.');
                        });
                    }
                }).catch(error => {
                    if (error.response) {
                        console.log('error');
                    }
                });
            }else{
                $('#classPlanBuilderForm').find('.routineDayBox.removeNow').remove();
                confirmModalCPB.hide();

                successModalCPB2.show(); 
                document.getElementById("successModalCPB2").addEventListener("shown.tw.modal", function (event) {
                    $("#successModalCPB2 .successModalTitleCPB2").html("Congratulations!");
                    $("#successModalCPB2 .successModalDescCPB2").html('Class Plan successfully removed from the sheet.');
                });
            }
            
        })


        $('#classPlanBuilderForm').on('click', '.DMToggle', function(e){
            e.preventDefault();
            $(this).siblings('.dropdownMenuBox').slideToggle('fast');
            $(this).parent('.dropdownMenus').toggleClass('active');
        })

        $('#classPlanBuilderForm').on("input", '.dropdownMenuSearch', function() {
            var $this = $(this);
            let filter = $(this).val();
            
            if (filter) {
                $this.siblings('.dropdownMenus').children('li').hide();
                $this.siblings('.dropdownMenus').children(`li:contains('${filter}')`).show();
            } else {
                $this.siblings('.dropdownMenus').children('li').show();
            }
        });
        $.expr[':'].contains = function(a, i, m) {
            return $(a).text().toUpperCase()
                .indexOf(m[3].toUpperCase()) >= 0;
        };

        /* Hide Dropdown On Click Outside */
        $(document).on('mouseup', function(e) {
            var container = $('.dropdownMenus.active');
            if (!container.is(e.target) && container.has(e.target).length === 0){
                $('.dropdownMenuSearch', container).val('');
                $('.dropdownMenuBox', container).slideUp();
                $('.dropdownMenus', container).children('li').show();
                container.removeClass('active');
            }
        });

        /* Select Dropdown Option */
        $('#classPlanBuilderForm').on('click', '.dropdownMenus.active ul li', function(e){
            e.preventDefault();
            var $this = $(this);
            var id = $this.attr('data-value');
            var title = $this.html();
            var $theDropdown = $this.closest('.dropdownMenus.active');
            var label = $theDropdown.attr('data-label');
            
            $theDropdown.attr('data-id', id);
            $theDropdown.children('.DMToggle').children('span').html(title);

            if(label == 'Module'){
                axios({
                    method: 'post',
                    url: route('class.plan.get.module.details'),
                    data: {id : id},
                    headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
                }).then(response => {
                    if (response.status == 200) {
                        var module = response.data.res;
                        if(module.moodle_enrollment_key){
                            $theDropdown.siblings('.enrollmentKey').attr('data-id', module.moodle_enrollment_key);
                            $theDropdown.siblings('.enrollmentKey').children('.btn-ekey').children('span').html(module.moodle_enrollment_key)
                        }else{
                            $theDropdown.siblings('.enrollmentKey').attr('data-id', '0');
                            $theDropdown.siblings('.enrollmentKey').children('.btn-ekey').children('span').html('Enrollment')
                        }
                        if(module.submission_date){
                            $theDropdown.siblings('.submissionDate').attr('data-id', module.submission_date);
                            $theDropdown.siblings('.submissionDate').children('.btn-submission').children('span').html(module.submission_date)
                        }else{
                            $theDropdown.siblings('.submissionDate').attr('data-id', '0');
                            $theDropdown.siblings('.submissionDate').children('.btn-submission').children('span').html('Submission')
                        }
                        if(module.class_type){
                            $theDropdown.siblings('.classType').attr('data-id', module.class_type);
                            $theDropdown.siblings('.classType').children('.btn-class-type').children('span').html(module.class_type)
                        }else{
                            $theDropdown.siblings('.classType').attr('data-id', '0');
                            $theDropdown.siblings('.classType').children('.btn-class-type').children('span').html('Class Type')
                        }
                    }
                }).catch(error => {
                    if (error.response) {
                        $theDropdown.siblings('.enrollmentKey').attr('data-id', '0');
                        $theDropdown.siblings('.enrollmentKey').children('.btn-ekey').children('span').html('Enrollment')
                        $theDropdown.siblings('.submissionDate').attr('data-id', '0');
                        $theDropdown.siblings('.submissionDate').children('.btn-submission').children('span').html('Submission')
                        $theDropdown.siblings('.classType').attr('data-id', '0');
                        $theDropdown.siblings('.classType').children('.btn-class-type').children('span').html('Class Type')
                        console.log('error');
                    }
                });
                $('.dropdownMenus.active .dropdownMenuSearch').val('');
                $('.dropdownMenus.active .dropdownMenuBox').slideUp();
                $('.dropdownMenus').removeClass('active');
            }else{
                $('.dropdownMenus.active .dropdownMenuSearch').val('');
                $('.dropdownMenus.active .dropdownMenuBox').slideUp();
                $('.dropdownMenus').removeClass('active');
            }
        });

        /* Clear Selected Value from Item */
        $('#classPlanBuilderForm').on('click', '.clearSelectionDropdown', function(e){
            e.preventDefault();
            
            var $this = $(this);
            var title = $this.parent('.dropdownMenus').attr('data-label');
            $this.parent('.dropdownMenus').attr('data-id', '0');
            $this.siblings('.DMToggle').children('span').text(title);
        });
        
        $('#classPlanBuilderForm').on('click', '.inputToggles', function(e){
            e.preventDefault();
            var $this = $(this);
            var $inputFields = $this.parent('.inputFields');
            var $input = $inputFields.find('.inputFieldsInput');
            var enrollmKey = ($inputFields.attr('data-id') != '' && $inputFields.attr('data-id') != '0' ? $inputFields.attr('data-id') : '');
            $('.inputWraps', $inputFields).fadeIn('fast', function(){
                $input.val(enrollmKey).trigger('focus');
            });
            $inputFields.addClass('active');
        });

        $('#classPlanBuilderForm').on('click', '.okInputValue', function(e){
            e.preventDefault();
            var $this = $(this);
            var $inputWrap = $this.parent('.inputWraps');
            var $inputFields = $inputWrap.parent('.inputFields');
            var $inputField = $this.siblings('.inputFieldsInput');
            var $btn = $inputFields.find('.inputToggles')
            var title = $inputFields.attr('data-label');

            var enrollmKey = $inputField.val();
            $inputFields.attr('data-id', enrollmKey);
            $btn.find('span').html((enrollmKey != '' ? enrollmKey : title));
            $('.inputWraps', $inputFields).fadeOut('fast', function(){
                $('.inputFieldsInput', this).val('');
            });
            $inputFields.removeClass('active');
        });

        /* Hide Inputwrap On Click Outside */
        $(document).on('mouseup', function(e) {
            var container = $('.inputFields.active');
            if (!container.is(e.target) && container.has(e.target).length === 0){
                $('.inputWraps', container).fadeOut('fast', function(){
                    $('.inputFieldsInput', this).val('');
                });
                container.removeClass('active');
            }
        });

        $(document.body).on('click', '.clearSelectionInput', function(e){
            e.preventDefault();
            var $this = $(this);
            var $inputFields = $this.parent('.inputFields');
            var label = $inputFields.attr('data-label');
            var $btn = $inputFields.find('.inputToggles')
            $inputFields.attr('data-id', 0);
            $btn.find('span').html(label);
            $inputFields.removeClass('active');
        });

        $(document.body).find('.dateMask').each(function(){
            var dateMask = IMask(
                this,
                {
                    mask: Date,
                    min: new Date(1990, 0, 1),
                    max: new Date(2050, 0, 1),
                    pattern: 'd-`m-`Y',
                    format: function (date) {
                        var day = date.getDate();
                        var month = date.getMonth() + 1;
                        var year = date.getFullYear();
                    
                        if (day < 10) day = "0" + day;
                        if (month < 10) month = "0" + month;
                    
                        return [day, month, year].join('-');
                    },
                    // define str -> date convertion
                    parse: function (str) {
                        var yearMonthDay = str.split('-');
                        return new Date(yearMonthDay[2], yearMonthDay[1] - 1, yearMonthDay[0]);
                    },
                    lazy: false
                });
        });

        $(document.body).find('.timeMask').each(function(){
            var timeMask = IMask(
                this,
                {
                    overwrite: true,
                    autofix: true,
                    mask: 'HH:MM - HH2:MM2',
                    blocks: {
                        HH: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'HH',
                            from: 0,
                            to: 23,
                            maxLength: 2
                        },
                        MM: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'MM',
                            from: 0,
                            to: 59,
                            maxLength: 2
                        },
                        HH2: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'HH',
                            from: 0,
                            to: 23,
                            maxLength: 2
                        },
                        MM2: {
                            mask: IMask.MaskedRange,
                            placeholderChar: 'MM',
                            from: 0,
                            to: 59,
                            maxLength: 2
                        }
                    }
                });
        });

        $('.cp_venue_id').on('change', function(){
            var $cp_venue_id = $(this);
            var cp_venue_id = $cp_venue_id.val();

            if($cp_venue_id.prop('checked')){
                $('.routineBuilderTable .cp_venue_col_'+cp_venue_id).css({'display' : 'table-cell'});
            }else{
                var routineCount = 0;
                $('.routineBuilderTable tbody .cp_venue_col_'+cp_venue_id).each(function(){
                    routineCount += $(this).find('.routineDayBox').length;
                });
                if(routineCount > 0){
                    $cp_venue_id.prop('checked', true);
                }else{
                    $('.routineBuilderTable .cp_venue_col_'+cp_venue_id).css({'display' : 'none'});
                }
            }
        })
    }

})()