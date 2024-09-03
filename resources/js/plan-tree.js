import IMask from 'imask';
import xlsx from "xlsx";
import { createIcons, icons } from "lucide";
import Tabulator from "tabulator-tables";
import TomSelect from "tom-select";
import tippy, { roundArrow } from "tippy.js";

("use strict");
var classPlanTreeListTable = (function () {
    var _tableGen = function () {
        // Setup Tabulator
        let courses = $("#classPlanTreeListTable").attr('data-course') ;
        //let instance_term = $("#classPlanTreeListTable").attr('data-term');
        let group = $("#classPlanTreeListTable").attr('data-group');
        let year = $("#classPlanTreeListTable").attr('data-year');
        let attendanceSemester = $("#classPlanTreeListTable").attr('data-attendanceSemester');

        let tableContent = new Tabulator("#classPlanTreeListTable", {
            ajaxURL: route("plans.tree.list"),
            ajaxParams: { courses: courses, group: group, year: year, attendanceSemester: attendanceSemester}, //instance_term: instance_term,
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
                    title: "ID",
                    field: "id",
                    width: 110,
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
                    title: "Tutor",
                    field: "tutor",
                    headerHozAlign: "left",
                },
                {
                    title: "Personal Tutor",
                    field: "personalTutor",
                    headerHozAlign: "left",
                },
                {
                    title: "No of Student",
                    field: "on_of_student",
                    headerHozAlign: "left",
                },
                {
                    title: "Class Days",
                    field: "dates",
                    headerHozAlign: "left",
                    formatter(cell, formatterParams) {  
                        if(cell.getData().dates > 0){
                            return '<a target="_blank" href="'+route('plan.dates', cell.getData().id)+'" class="text-primary font-medium"><u>'+cell.getData().dates+'</u></a>';
                        }else{
                            return '<span>0</span>';
                        }
                    }
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
                    title: "Actions",
                    field: "id",
                    headerSort: false,
                    hozAlign: "center",
                    headerHozAlign: "left",
                    download: false,
                    formatter(cell, formatterParams) {                        
                        var btns = "";
                        if (cell.getData().deleted_at == null) {
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
                    $('#generateDaysBtn, #bulkCommunication').fadeIn();
                }else{
                    $('#generateDaysBtn, #bulkCommunication').fadeOut();
                }
            },
            renderComplete() {
                createIcons({
                    icons,
                    "stroke-width": 1.5,
                    nameAttr: "data-lucide",
                });
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

        // Export
        $("#tabulator-export-csv-CPL").on("click", function (event) {
            tableContent.download("csv", "data.csv");
        });

        $("#tabulator-export-json-CPL").on("click", function (event) {
            tableContent.download("json", "data.json");
        });

        $("#tabulator-export-xlsx-CPL").on("click", function (event) {
            window.XLSX = xlsx;
            tableContent.download("xlsx", "data.xlsx", {
                sheetName: "Plan Tree Details",
            });
        });

        $("#tabulator-export-html-CPL").on("click", function (event) {
            tableContent.download("html", "data.html", {
                style: true,
            });
        });

        // Print
        $("#tabulator-print-CPL").on("click", function (event) {
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
    let tomOptions = {
        plugins: {
            dropdown_input: {},
            remove_button: {
                title: "Remove this item",
            },
        },
        placeholder: 'Search Here...',
        persist: false,
        create: false,
        allowEmptyOption: true,
        onDelete: function (values) {
            return confirm( values.length > 1 ? "Are you sure you want to remove these " + values.length + " items?" : 'Are you sure you want to remove "' +values[0] +'"?' );
        },
    };
    let assigned_user_ids = new TomSelect(document.getElementById('assigned_user_ids'), tomOptions);

    const warningModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#warningModalCP"));
    const successModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#successModalCP"));
    const editPlanModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#editPlanModal"));
    const confirmModalCP = tailwind.Modal.getOrCreateInstance(document.querySelector("#confirmModalCP"));
    const assignManagerOrCoOrdinatorModal = tailwind.Modal.getOrCreateInstance(document.querySelector("#assignManagerOrCoOrdinatorModal"));

    const assignManagerOrCoOrdinatorModalEl = document.getElementById('assignManagerOrCoOrdinatorModal')
    assignManagerOrCoOrdinatorModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#assignManagerOrCoOrdinatorModalEl .acc__input-error').html('');
        $('#assignManagerOrCoOrdinatorModalEl input[type="hidden"]').val('');
        assigned_user_ids.clear(true);
    });
        
    let confModalDelTitle = 'Are you sure?';

    const editPlanModalEl = document.getElementById('editPlanModal')
    editPlanModalEl.addEventListener('hide.tw.modal', function(event) {
        $('#editPlanModal .acc__input-error').html('');
        $('#editPlanModal .modal-body select').val('');
        $('#editPlanModal .modal-body input:not([type="radio"])').val('');
        $('#editPlanModal input[name="id"]').val('0');
        $('#editPlanModal input[type="radio"]').prop('checked', false);
    });


    /* Get Term By AC Year */
    $('.classPlanTree').on('click', '.academicYear', function(e){
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        if($parent.hasClass('hasData')){
            $('> .theChild', $parent).slideToggle();
            $parent.toggleClass('opened');
        }else{
            $('svg', $link).fadeIn();
            var academicyear = $link.attr('data-yearid');
            axios({
                method: "post",
                url: route('plans.tree.get.semester'),
                data: {academicyear : academicyear},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('svg', $link).fadeOut();
                if (response.status == 200) {
                    $parent.addClass('hasData opened');
                    $parent.append(response.data.htm);

                    $('.classPlanTreeResultWrap').fadeOut('fast', function(){
                        $('.classPlanTreeResultWrap').html('');
                        $('.classPlanTreeResultNotice').fadeIn('fast', function(){
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        })
                    });

                    tailwind.svgLoader();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('svg', $link).fadeOut();
                    console.log('error');
                }
            });
        }
    });
    /* Get Term By AC Year */

    /* Get Course By Term */
    $('.classPlanTree').on('click', '.theTerm', function(e){
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        
        if($parent.hasClass('hasData')){
            $('> .theChild', $parent).slideToggle();
            $parent.toggleClass('opened');
        }else{
            $('svg', $link).fadeIn();
            var academicYearId = $link.attr('data-yearid');
            var attendanceSemester = $link.attr('data-attendanceSemester');
            axios({
                method: "post",
                url: route('plans.tree.get.courses'),
                data: {academicYearId : academicYearId, attendanceSemester : attendanceSemester},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                $('svg', $link).fadeOut();
                if (response.status == 200) {
                    $parent.addClass('hasData opened');
                    $parent.append(response.data.htm);

                    $('.classPlanTreeResultWrap').fadeOut('fast', function(){
                        $('.classPlanTreeResultWrap').html('');
                        $('.classPlanTreeResultNotice').fadeIn('fast', function(){
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        })
                    });

                    tailwind.svgLoader();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('svg', $link).fadeOut();
                    console.log('error');
                }
            });
        }
    });
    /* Get Course By Term */

    /* Get Group By Course */
    $('.classPlanTree').on('click', '.theCourse', function(e){
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        
        if($parent.hasClass('hasData')){
            $('> .theChild', $parent).slideToggle();
            $parent.toggleClass('opened');
        }else{
            $('svg', $link).fadeIn();
            var courseId = $link.attr('data-courseid');
            var attendanceSemester = $link.attr('data-attendanceSemester');
            var academicYearId = $link.attr('data-yearid');
            axios({
                method: "post",
                url: route('plans.tree.get.groups'),
                data: {courseId : courseId, attendanceSemester : attendanceSemester, academicYearId : academicYearId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('svg', $link).fadeOut();
                    $parent.addClass('hasData opened');
                    $parent.append(response.data.htm);

                    $('.classPlanTreeResultWrap').fadeOut('fast', function(){
                        $('.classPlanTreeResultWrap').html('');
                        $('.classPlanTreeResultNotice').fadeIn('fast', function(){
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });
                        })
                    });

                    tailwind.svgLoader();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });

                    $parent.find(".tooltip").each(function () {
                        let toolTIpOptions = {content: $(this).attr("title"), placement: 'right' };
                        $(this).removeAttr("title");
                
                        tippy(this, {
                            arrow: roundArrow,
                            animation: "shift-away",
                            ...toolTIpOptions,
                        });
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('svg', $link).fadeOut();
                    console.log('error');
                }
            });
        }
    });
    /* Get Group By Course */

    /* Get Module By Group */
    $('.classPlanTree').on('click', '.theGroup', function(e){
        e.preventDefault();
        var $link = $(this);
        var $parent = $link.parent('li');

        
        if(!$parent.hasClass('hasData')){
            $parent.siblings('li').removeClass('hasData opened');
            $('svg', $link).fadeIn();
            var courseId = $link.attr('data-courseid');
            //var termId = $link.attr('data-termid');
            var academicYearId = $link.attr('data-yearid');
            var attendancesemester = $link.attr('data-attendancesemester');
            var groupId = $link.attr('data-groupid');

            //termId : termId,
            axios({
                method: "post",
                url: route('plans.tree.get.module'),
                data: {courseId : courseId, attendancesemester: attendancesemester, academicYearId : academicYearId, groupId : groupId},
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    $('svg', $link).fadeOut();
                    $parent.addClass('hasData opened');
                    
                    $('.classPlanTreeResultNotice').fadeOut('fast', function(){
                        $('.classPlanTreeResultWrap').fadeIn('fast', function(){
                            $('.classPlanTreeResultWrap').html(response.data.htm);
                            
                            if($('#classPlanTreeListTable').length > 0){
                                classPlanTreeListTable.init();
                            }
                            createIcons({
                                icons,
                                "stroke-width": 1.5,
                                nameAttr: "data-lucide",
                            });

                            $('.classPlanTreeResultWrap').find(".tooltip").each(function () {
                                let toolTIpOptions = {content: $(this).attr("title"), placement: 'right'};
                                $(this).removeAttr("title");
                        
                                tippy(this, {
                                    arrow: roundArrow,
                                    animation: "shift-away",
                                    ...toolTIpOptions,
                                });
                            });
                        })
                    });

                    tailwind.svgLoader();
                    createIcons({
                        icons,
                        "stroke-width": 1.5,
                        nameAttr: "data-lucide",
                    });
                }
            }).catch(error => {
                if (error.response) {
                    $('svg', $link).fadeOut();
                    console.log('error');
                }
            });
        }
    });
    /* Get Modul By Group */


    /* Edit Plan */
    $('.classPlanTreeResultWrap').on('click', '#classPlanTreeListTable .edit_btn', function(e){
        var $btn = $(this);
        var planid = $btn.attr('data-id');
        
        axios({
            method: "get",
            url: route("plans.tree.edit", planid),
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
                $('#editPlanModal select[name="tutor_id"]').val(dataset.plan.tutor_id ? dataset.plan.tutor_id : '');
                $('#editPlanModal select[name="personal_tutor_id"]').val(dataset.plan.personal_tutor_id ? dataset.plan.personal_tutor_id : '');
                $('#editPlanModal select[name="class_type"]').val(dataset.plan.class_type ? dataset.plan.class_type : '');
                //$('#editPlanModal input[name="module_enrollment_key"]').val(dataset.plan.module_enrollment_key ? dataset.plan.module_enrollment_key : '');
                $('#editPlanModal input[name="start_time"]').val(dataset.plan.start_time ? dataset.plan.start_time : '');
                $('#editPlanModal input[name="end_time"]').val(dataset.plan.end_time ? dataset.plan.end_time : '');
                $('#editPlanModal input[name="submission_date"]').val(dataset.plan.submission_date ? dataset.plan.submission_date : '');
                $('#editPlanModal input[name="virtual_room"]').val(dataset.plan.virtual_room ? dataset.plan.virtual_room : '');
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

    $('#editPlanForm').on('submit', function(e){
        e.preventDefault();
        const form = document.getElementById('editPlanForm');
    
        document.querySelector('#updatePlans').setAttribute('disabled', 'disabled');
        document.querySelector("#updatePlans svg").style.cssText ="display: inline-block;";

        let form_data = new FormData(form);
        axios({
            method: "post",
            url: route('plans.tree.update'),
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
            classPlanTreeListTable.init();
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
    /* Edit Plan */


    /* Delete & Restore Plan */
    $('.classPlanTreeResultWrap').on('click', '#classPlanTreeListTable .delete_btn', function(e){
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

    $('.classPlanTreeResultWrap').on('click', '#classPlanTreeListTable .restore_btn', function(e){
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

    $('#confirmModalCP .agreeWithCP').on('click', function(e){
        e.preventDefault();
        let $agreeBTN = $(this);
        let recordID = $agreeBTN.attr('data-id');
        let action = $agreeBTN.attr('data-action');

        $('#confirmModalDP button').attr('disabled', 'disabled');
        if(action == 'DELETE'){
            axios({
                method: 'delete',
                url: route('plans.tree.destory', recordID),
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
                classPlanTreeListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        } else if(action == 'RESTORE'){
            axios({
                method: 'post',
                url: route('plans.tree.restore', recordID),
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
                classPlanTreeListTable.init();
            }).catch(error =>{
                console.log(error)
            });
        }
    });
    /* Delete & Restore Plan */

    /* Generate Days For Plan */
    $('.classPlanTreeResultWrap').on('click', '#generateDaysBtn', function(e){
        e.preventDefault();
        var $btn = $(this);
        var ids = [];
        
        $('#classPlanTreeListTable').find('.tabulator-row.tabulator-selected').each(function(){
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

                    classPlanTreeListTable.init();
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
                    console.log(error.response);
                    setTimeout(function(){
                        warningModalCP.hide();
                    }, 3000);

                    classPlanTreeListTable.init();
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
    /* Generate Days For Plan */


    /* Assign Manager & Co-Ordinator */
    $(document).on('click', '.assignManager', function(e){
        e.preventDefault();
        var $btn = $(this);
        
        
        var yearid = ((typeof $btn.attr('data-yearid') !== 'undefined' && $btn.attr('data-yearid') !== false) ? $btn.attr('data-yearid') : false);
        var termid = ((typeof $btn.attr('data-attendanceSemester') !== 'undefined' && $btn.attr('data-attendanceSemester') !== false) ? $btn.attr('data-attendanceSemester') : false);
        var courseid = ((typeof $btn.attr('data-courseid') !== 'undefined' && $btn.attr('data-courseid') !== false) ? $btn.attr('data-courseid') : false);
        var groupid = ((typeof $btn.attr('data-groupid') !== 'undefined' && $btn.attr('data-groupid') !== false) ? $btn.attr('data-groupid') : false);

        assignManagerOrCoOrdinatorModal.show();
        $('.assignRoleTitle').text('Manager');

        axios({
            method: "post",
            url: route('plans.get.assign.details'),
            data: {yearid : yearid, termid : termid, courseid : courseid, groupid : groupid, type: 'Manager'},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                let plans = response.data.plans;
                let participants = response.data.participants;
                let title = response.data.title;
                $('#assignManagerOrCoOrdinatorModal .theModTitle').html(title);

                if(plans.length > 0){
                    $('#assignManagerOrCoOrdinatorModal input[name="plan_ids"]').val(plans.join());
                };

                if(participants.length > 0){
                    $.each(participants, function(name, value) {
                        assigned_user_ids.addItem(value, true);
                    });
                }else{
                    assigned_user_ids.clear(true);
                }
                $('#assignManagerOrCoOrdinatorModal input[name="type"]').val('Manager');
            }
        }).catch(error => {
            if (error.response.status == 422 || error.response.status == 304) {
                console.log('error');
            }
        });
    });

    $(document).on('click', '.assignCoOrdinator', function(e){
        e.preventDefault();
        var $btn = $(this);
        
        
        var yearid = ((typeof $btn.attr('data-yearid') !== 'undefined' && $btn.attr('data-yearid') !== false) ? $btn.attr('data-yearid') : false);
        var termid = ((typeof $btn.attr('data-attendanceSemester') !== 'undefined' && $btn.attr('data-attendanceSemester') !== false) ? $btn.attr('data-attendanceSemester') : false);
        var courseid = ((typeof $btn.attr('data-courseid') !== 'undefined' && $btn.attr('data-courseid') !== false) ? $btn.attr('data-courseid') : false);
        var groupid = ((typeof $btn.attr('data-groupid') !== 'undefined' && $btn.attr('data-groupid') !== false) ? $btn.attr('data-groupid') : false);

        assignManagerOrCoOrdinatorModal.show();
        $('.assignRoleTitle').text('Audit User');

        axios({
            method: "post",
            url: route('plans.get.assign.details'),
            data: {yearid : yearid, termid : termid, courseid : courseid, groupid : groupid, type: 'Auditor'},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                let plans = response.data.plans;
                let participants = response.data.participants;
                let title = response.data.title;
                $('#assignManagerOrCoOrdinatorModal .theModTitle').html(title);

                if(plans.length > 0){
                    $('#assignManagerOrCoOrdinatorModal input[name="plan_ids"]').val(plans.join());
                };

                if(participants.length > 0){
                    $.each(participants, function(name, value) {
                        assigned_user_ids.addItem(value, true);
                    });
                }else{
                    assigned_user_ids.clear(true);
                }
                $('#assignManagerOrCoOrdinatorModal input[name="type"]').val('Auditor');
            }
        }).catch(error => {
            if (error.response.status == 422 || error.response.status == 304) {
                console.log('error');
            }
        });
    });

    $('#assignManagerOrCoOrdinatorForm').on('submit', function(e){
        e.preventDefault();
        var $form = $(this);
        const form = document.getElementById('assignManagerOrCoOrdinatorForm');
    
        document.querySelector('#updateParticipants').setAttribute('disabled', 'disabled');
        document.querySelector("#updateParticipants svg").style.cssText ="display: inline-block;";

        let plan_id = $('#assignManagerOrCoOrdinatorForm input[name="plan_ids"]').val();
        if(plan_id == ''){
            $('participantError', $form).remove();
            $('participantError', $form).prepend('<div class="alert alert-danger-soft show flex items-center mb-2" role="alert"><i data-lucide="alert-octagon" class="w-6 h-6 mr-2"></i> Plan Id Not found. Please add plan first.</div>');
            
            createIcons({
                icons,
                "stroke-width": 1.5,
                nameAttr: "data-lucide",
            });

            setTimeout(function(){
                $('participantError', $form).remove();
            }, 3000);
        }else{
            let form_data = new FormData(form);
            axios({
                method: "post",
                url: route('plans.assign.participants'),
                data: form_data,
                headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
            }).then(response => {
                if (response.status == 200) {
                    document.querySelector('#updateParticipants').removeAttribute('disabled');
                    document.querySelector("#updateParticipants svg").style.cssText = "display: none;";

                    assignManagerOrCoOrdinatorModal.hide();

                    successModalCP.show();
                    document.getElementById("successModalCP").addEventListener("shown.tw.modal", function (event) {
                        $("#successModalCP .successModalTitleCP").html("Congratulation!" );
                        $("#successModalCP .successModalDescCP").html('Participants are successfully assignd.');
                    });                
                    
                    setTimeout(function(){
                        successModalCP.hide();
                    }, 4000);
                }
            }).catch(error => {
                document.querySelector('#updateParticipants').removeAttribute('disabled');
                document.querySelector("#updateParticipants svg").style.cssText = "display: none;";
                if (error.response) {
                    if (error.response.status == 422) {
                        for (const [key, val] of Object.entries(error.response.data.errors)) {
                            $(`#assignManagerOrCoOrdinatorForm .${key}`).addClass('border-danger');
                            $(`#assignManagerOrCoOrdinatorForm  .error-${key}`).html(val);
                        }
                    } else {
                        console.log('error');
                    }
                }
            });
        }
    });
    /* Assign Manager & Co-Ordinator */

    /* Plan Visibility Set */
    $(document).on('click', '.visibilityBtn', function(e){
        e.preventDefault();
        var $btn = $(this);
        
        var visibility = ((typeof $btn.attr('data-visibility') !== 'undefined' && $btn.attr('data-visibility') !== false) ? $btn.attr('data-visibility') : 1);
        var yearid = ((typeof $btn.attr('data-yearid') !== 'undefined' && $btn.attr('data-yearid') !== false) ? $btn.attr('data-yearid') : false);
        //var termid = ((typeof $btn.attr('data-termid') !== 'undefined' && $btn.attr('data-termid') !== false) ? $btn.attr('data-termid') : false);
        var courseid = ((typeof $btn.attr('data-courseid') !== 'undefined' && $btn.attr('data-courseid') !== false) ? $btn.attr('data-courseid') : false);
        var groupid = ((typeof $btn.attr('data-groupid') !== 'undefined' && $btn.attr('data-groupid') !== false) ? $btn.attr('data-groupid') : false);
        var attendancesemester = ((typeof $btn.attr('data-attendancesemester') !== 'undefined' && $btn.attr('data-attendancesemester') !== false) ? $btn.attr('data-attendancesemester') : false);

        $btn.attr('disabled', 'disabled');

        axios({
            method: "post",
            url: route('plans.update.visibility'),
            data: {yearid : yearid, attendancesemester : attendancesemester, courseid : courseid, groupid : groupid, visibility : visibility},
            headers: {'X-CSRF-TOKEN' :  $('meta[name="csrf-token"]').attr('content')},
        }).then(response => {
            if (response.status == 200) {
                console.log(response.data);
                var suc = response.data.suc;
                var visibilities = response.data.visibility;
                $btn.removeAttr('disabled').removeClass('visibility_'+visibilities).addClass('visibility_'+(visibilities == 1 ? 0 : 1)).attr('data-visibility', visibilities);
                if(suc == 2){
                    warningModalCP.show();
                    document.getElementById("warningModalCP").addEventListener("shown.tw.modal", function (event) {
                        $("#warningModalCP .warningModalTitleCP").html("Oops!" );
                        $("#warningModalCP .warningModalDescCP").html('Plans not found under selectd criteria. Please add class plans first.');
                    }); 
                    
                    setTimeout(function(){
                        warningModalCP.hide();
                    }, 5000)
                }else{
                    successModalCP.show();
                    document.getElementById("successModalCP").addEventListener("shown.tw.modal", function (event) {
                        $("#successModalCP .successModalTitleCP").html("Congratulation!" );
                        $("#successModalCP .successModalDescCP").html('Plans visibility successfully updated.');
                    });                
                    
                    setTimeout(function(){
                        successModalCP.hide();
                    }, 5000);
                }
            }
        }).catch(error => {
            if (error.response) {
                console.log('error');
            }
        });
    });
    /* Plan Visibility Set */

    /* Bulk Communication Start */
    $('.classPlanTreeResultWrap').on('click', '#bulkCommunication', function(e){
        e.preventDefault();
        var $btn = $(this);
        var ids = [];
        
        $('#classPlanTreeListTable').find('.tabulator-row.tabulator-selected').each(function(){
            var $row = $(this);
            ids.push($row.find('.classPlanId').val());
        });

        if(ids.length > 0){
            var url_ids = ids.join('-');
            window.location.href = route('bulk.communication', url_ids);
        }else{
            warningModalCP.show();
            document.getElementById("warningModalCP").addEventListener("shown.tw.modal", function (event) {
                $("#warningModalCP .warningModalTitleCP").html("Error Found!");
                $("#warningModalCP .warningModalDescCP").html('Selected plans id not foudn. Please select some plan first or contact with the site administrator.');
            });
        }
    });
    /* Bulk Communication End */

})();